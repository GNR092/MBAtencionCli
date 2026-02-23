<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use App\Models\Contract;
use App\Models\XmlFile;
use App\Models\Cuentas;
use App\Models\Impuesto;
use App\Models\IncrementoImporte;
use Carbon\Carbon;
use Carbon\CarbonPeriod;

class CuentasPorCobrar extends Controller
{

public function limpiar()
{
        session()->forget(['search', 'categoria']);
        return redirect()->route('cuentas-cobrar.index');
}

    /* -------------------------
       FILTROS REUTILIZABLES
    ------------------------- */
private function aplicarFiltros(&$query, Request $request)
{
        if ($request->filled('mes')) {
            $query->whereDate('cuentasporpagar.mesesdepago', $request->input('mes'));
        }

        if ($request->filled('id_cuentas_por_pagar')) {
            $query->where('cuentasporpagar.id_cuentas_por_pagar', $request->input('id_cuentas_por_pagar'));
        }

        if ($request->filled('name')) {
            $query->where('users.name', 'LIKE', '%'.$request->input('name').'%');
        }

        if ($request->filled('isr')) {
            $query->where('cuentasporpagar.isr', $request->input('isr'));
        }

        if ($request->filled('saldo_neto')) {
            $query->where('cuentasporpagar.saldo_neto', $request->input('saldo_neto'));
        }

        if ($request->filled('monto_pagado')) {
            $query->where('cuentasporpagar.monto_pagado', $request->input('monto_pagado'));
        }

        if ($request->filled('saldo_pendiente')) {
            $query->where('cuentasporpagar.saldo_pendiente', $request->input('saldo_pendiente'));
        }

        if ($request->filled('search') && $request->filled('categoria')) {
            $search = $request->input('search');
            $categoria = $request->input('categoria');

            switch ($categoria) {
                case 'id':
                    $query->where('cuentasporpagar.id_cuentas_por_pagar', 'LIKE', "%{$search}%");
                    break;
                case 'estado':
                    $query->where('cuentasporpagar.estado', 'LIKE', "%{$search}%");
                    break;
                case 'mes':
                    $query->where('cuentasporpagar.mesesdepago', 'LIKE', "%{$search}%");
                    break;
                case 'name':
                    $query->where('users.name', 'LIKE', "%{$search}%");
                    break;
            }
        }
}

    /* --------------------------------------------------
       1. GENERAR CUENTAS POR PAGO POR CONTRATO (si falta)
    -------------------------------------------------- */
public function calcularCuentasPorPagar($user_id)
{
        $contracts = Contract::where('user_id', $user_id)
            ->where(function ($q) {
                $q->whereNotNull('fecha_inicio')->orWhereNotNull('fecha_creacion');
            })
            ->get();

        foreach ($contracts as $contract) {
            $fechaStart = $contract->fecha_inicio ?? $contract->fecha_creacion ?? $contract->fecha;
            $fechaEnd   = $contract->fecha_terminacion ?? now()->format('Y-m-d');
            $inicio = Carbon::parse($fechaStart)->startOfMonth();
            $fin    = Carbon::parse($fechaEnd)->startOfMonth();

            $periodo = CarbonPeriod::create($inicio, '1 month', $fin);

            foreach ($periodo as $date) {
                $mes = $date->format('Y-m');

                $exists = DB::table('cuentasporpagar')
                    ->where('id_contract', $contract->id)
                    ->whereRaw("JSON_EXTRACT(mesesdepago, '$.mes') = ?", [$mes])
                    ->exists();

                if (!$exists) {
                    
                    $importeIncremento = DB::table('incrementos_importe')
                        ->where('id_contract', $contract->id)
                        ->whereDate('fecha_inicio', '<=', $mes . '-01')
                        ->where(function ($q) use ($mes) {
                            $q->whereNull('fecha_fin')
                              ->orWhereDate('fecha_fin', '>=', $mes . '-01');
                        })
                        ->value('importe_base');

                    $importe_base = $importeIncremento ?? $contract->importe_bruto_renta;

                    DB::table('cuentasporpagar')->insert([
                        'id_contract' => $contract->id,
                        'mesesdepago' => json_encode(['mes' => $mes]),
                        'estado' => 'pendiente',
                        'saldo_neto' => $importe_base, 
                        'xml_file_id' => null,
                        'mesespagados' => json_encode([]),
                        'monto_pagado' => 0,
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);
                }
            }
        }
}

    /* --------------------------------------------------
       2. ACTUALIZAR CUENTA CUANDO LLEGA UN XML
       - usa impuesto.importeBase como fuente real
       - reemplaza monto_pagado (no suma) para evitar duplicados
       - permite reprocesar el mismo XML para corregir montos
    -------------------------------------------------- */
public function actualizarConXML(XmlFile $xml)
{
        // Use fecha_inicio (CFDI date) for reliable Y-m parsing.
        // The 'mes' field stores a display name like "Enero", not a parseable date.
        if (!$xml->fecha_inicio) return;

        try {
            $mesXml = Carbon::parse($xml->fecha_inicio)->format('Y-m');
        } catch (\Exception $e) {
            return;
        }

        // Find the contract for this specific user + project combination.
        $userProyecto = \App\Models\UserProyecto::where('id_user', $xml->id_user)
            ->where('id_proyecto', $xml->id_proyecto)
            ->first();

        $contract = $userProyecto
            ? Contract::where('id_user_p', $userProyecto->id_user_p)->first()
            : null;

        if (!$contract) return;

        
        // Clave única: xml_file_id (un registro por factura/UUID)
        $cuenta = DB::table('cuentasporpagar')
            ->where('xml_file_id', $xml->id)
            ->first();

        DB::beginTransaction();
        try {
            if (!$cuenta) {
                
                $importeIncremento = DB::table('incrementos_importe')
                    ->where('id_contract', $contract->id)
                    ->whereDate('fecha_inicio', '<=', $mesXml . '-01')
                    ->where(function ($q) use ($mesXml) {
                        $q->whereNull('fecha_fin')
                          ->orWhereDate('fecha_fin', '>=', $mesXml . '-01');
                    })
                    ->value('importe_base');

                $importeBase = $importeIncremento ?? $contract->importe_bruto_renta;

                $id = DB::table('cuentasporpagar')->insertGetId([
                    'id_contract' => $contract->id,
                    'mesesdepago' => json_encode(['mes' => $mesXml]),
                    'xml_file_id' => $xml->id,
                    'estado' => 'pendiente',
                    'saldo_neto' => $importeBase,
                    'monto_pagado' => 0,
                    'mesespagados' => json_encode([]),
                    'created_at' => now(),
                    'updated_at' => now()
                ]);

                $cuenta = DB::table('cuentasporpagar')->where('id_cuentas_por_pagar', $id)->first();
            }

            
            $impuesto = Impuesto::where('xml_file_id', $xml->id)->first();
            if (!$impuesto) {
                
                DB::commit();
                return;
            }

            
            $importeBaseXml = floatval($impuesto->importeBase ?? 0);
            $isrXml = floatval($impuesto->isr ?? 0);

            
            $importeIncremento = DB::table('incrementos_importe')
                ->where('id_contract', $contract->id)
                ->whereDate('fecha_inicio', '<=', $mesXml . '-01')
                ->where(function ($q) use ($mesXml) {
                    $q->whereNull('fecha_fin')
                      ->orWhereDate('fecha_fin', '>=', $mesXml . '-01');
                })
                ->value('importe_base');

            
            
            
            
            $importeBaseMes = $importeBaseXml ?: ($importeIncremento ?? $contract->importe_bruto_renta);

            
            
            
            $montoPagadoTotal = floatval($cuenta->monto_pagado ?? 0);

            $totalPagar      = max(0, $importeBaseMes - $isrXml);
            $saldoPendiente  = max(0, $totalPagar - $montoPagadoTotal);

            if ($montoPagadoTotal <= 0) {
                $estado = 'pendiente';
            } elseif ($montoPagadoTotal >= $totalPagar) {
                $estado = 'pagado';
                $saldoPendiente = 0;
            } else {
                $estado = 'parcial';
            }

            
            $mesesPagados = json_decode($cuenta->mesespagados ?? '[]', true);
            if (!is_array($mesesPagados)) $mesesPagados = [];
            if (!in_array($mesXml, $mesesPagados)) {
                $mesesPagados[] = $mesXml;
            }

            
            DB::table('cuentasporpagar')
                ->where('id_cuentas_por_pagar', $cuenta->id_cuentas_por_pagar)
                ->update([
                    'xml_file_id'    => $xml->id,
                    'saldo_neto'     => $totalPagar,
                    'isr'            => $isrXml,
                    'saldo_pendiente'=> $saldoPendiente,
                    'estado'         => $estado,
                    'updated_at'     => now()
                ]);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            
            
        }
}

    /* --------------------------------------------------
       3. CALCULAR MONTOS GENERALES (NO SOBREESCRIBIR XML)
    -------------------------------------------------- */
public function calcularMontos()
{
        $cuentas = DB::table('cuentasporpagar')
            ->leftJoin('contract', 'cuentasporpagar.id_contract', '=', 'contract.id')
            ->leftJoin('xml_files', 'cuentasporpagar.xml_file_id', '=', 'xml_files.id')
            ->leftJoin('impuesto', 'xml_files.id', '=', 'impuesto.xml_file_id')
            ->select(
                'cuentasporpagar.*',
                'contract.importe_bruto_renta as importeBaseContrato',
                'contract.id as id_contract',
                'impuesto.isr'
            )
            ->get();

        foreach ($cuentas as $c) {
            
            if ($c->xml_file_id) continue;

            
            $meses = json_decode($c->mesesdepago, true);
            $mesCuenta = $meses['mes'] ?? null;
            if (!$mesCuenta) continue;

            
            $importeIncremento = DB::table('incrementos_importe')
                ->where('id_contract', $c->id_contract)
                ->whereDate('fecha_inicio', '<=', $mesCuenta . '-01')
                ->where(function ($q) use ($mesCuenta) {
                    $q->whereNull('fecha_fin')
                      ->orWhereDate('fecha_fin', '>=', $mesCuenta . '-01');
                })
                ->value('importe_base');

            $importeBase = $importeIncremento ?? floatval($c->importeBaseContrato);

            $isr = floatval($c->isr ?? 0);
            $montoPagado = floatval($c->monto_pagado ?? 0);

            $totalPagar = max(0, $importeBase - $isr);

            if ($montoPagado <= 0) {
                $estado = 'pendiente';
                $saldoPendiente = $totalPagar;
            } else {
                $estado = 'parcial';
                $saldoPendiente = max(0, $totalPagar - $montoPagado);
            }

            DB::table('cuentasporpagar')
                ->where('id_cuentas_por_pagar', $c->id_cuentas_por_pagar)
                ->update([
                    'estado' => $estado,
                    'saldo_pendiente' => $saldoPendiente,
                    'saldo_neto' => $totalPagar,
                    'updated_at' => now()
                ]);
        }
}

    /* --------------------------------------------------
       4. REUTILIZAR: recalcula una cuenta (monto_pagado -> estado/saldo)
    -------------------------------------------------- */
private function recalcularCuenta($idCuenta)
{
        $cuenta = DB::table('cuentasporpagar')->where('id_cuentas_por_pagar', $idCuenta)->first();
        if (!$cuenta) return;

        $saldoNeto = floatval($cuenta->saldo_neto ?? 0);
        $montoPagado = floatval($cuenta->monto_pagado ?? 0);

        $saldoPendiente = max(0, $saldoNeto - $montoPagado);

        if ($montoPagado <= 0) {
            $estado = 'pendiente';
        } elseif ($saldoPendiente > 0) {
            $estado = 'parcial';
        } else {
            $estado = 'pagado';
        }

        DB::table('cuentasporpagar')
            ->where('id_cuentas_por_pagar', $idCuenta)
            ->update([
                'saldo_pendiente' => $saldoPendiente,
                'estado' => $estado,
                'updated_at' => now()
            ]);
}

    /* --------------------------------------------------
       5. INDEX
    -------------------------------------------------- */
public function Index(Request $request)
{
        $user = $request->user();
        if (!$user) return redirect('/inicio-de-sesion');

        $this->calcularCuentasPorPagar($user->id);
        $this->calcularMontos();

        $query = Cuentas::with('contract')
            ->leftJoin('xml_files', 'cuentasporpagar.xml_file_id', '=', 'xml_files.id')
            ->leftJoin('contract', 'cuentasporpagar.id_contract', '=', 'contract.id')
            ->leftJoin('users', 'contract.user_id', '=', 'users.id')
            ->leftJoin('user_proyectos', 'contract.id_user_p', '=', 'user_proyectos.id_user_p')
            ->leftJoin('proyectos', 'user_proyectos.id_proyecto', '=', 'proyectos.id_proyecto')
            ->leftJoin('impuesto', 'xml_files.id', '=', 'impuesto.xml_file_id')
            ->select(
                'cuentasporpagar.*',
                'users.name as name',
                'contract.importe_bruto_renta as importeBase',
                DB::raw('COALESCE(proyectos.nombre_proyecto, "Sin proyecto") as proyecto'),
            )
            ->where('users.id', $user->id)
            ->where('cuentasporpagar.estado', '!=', 'pagado')
            ->where('cuentasporpagar.mes_pago', '<=', date('Y-m'));

        if ($request->filled('month')) {
            $query->where('cuentasporpagar.mes_pago', $request->month);
        }

        if ($request->filled('search') && $request->filled('categoria')) {
            $search = $request->input('search');
            $categoria = $request->input('categoria');

            switch ($categoria) {
                case 'mes':
                    $query->where('cuentasporpagar.mes_pago', 'LIKE', "%{$search}%");
                    break;
                case 'proyecto':
                    $query->where('proyectos.nombre_proyecto', 'LIKE', "%{$search}%");
                    break;
                case 'estado':
                    $query->where('cuentasporpagar.estado', 'LIKE', "%{$search}%");
                    break;
            }
        }

        $this->aplicarFiltros($query, $request);

        $cuentas = $query->paginate(6)->appends($request->query());

        $minYear = (int) DB::table('cuentasporpagar')
            ->join('contract', 'cuentasporpagar.id_contract', '=', 'contract.id')
            ->where('contract.user_id', $user->id)
            ->whereNotNull('cuentasporpagar.mes_pago')
            ->min(DB::raw('LEFT(cuentasporpagar.mes_pago, 4)'));
        $minYear = $minYear ?: now()->year;

        if ($request->expectsJson()) {
            $html = view('cuentasCobrar', compact('cuentas', 'minYear'))->render();
            return response()->json(['html' => $html]);
        }

        return view('cuentasCobrar', compact('cuentas', 'minYear'));
}

public function graficaAnualNoPagados(Request $request, $year)
{
    $user = $request->user();
    if (!$user) return response()->json([]);

    
    $cuentas = DB::table('cuentasporpagar')
        ->join('contract', 'cuentasporpagar.id_contract', '=', 'contract.id')
        ->where('contract.user_id', $user->id)
        ->select('cuentasporpagar.*')
        ->get();

    $resultado = [];

    for ($m = 1; $m <= 12; $m++) {

        $noPagados = 0;

        foreach ($cuentas as $c) {
            $mesJson = json_decode($c->mesesdepago);

            if (!$mesJson || !isset($mesJson->mes)) continue;

            
            [$y, $month] = explode('-', $mesJson->mes);

            if ((int)$y === (int)$year && (int)$month === $m) {
                $noPagados += floatval($c->saldo_pendiente);
            }
        }

        $resultado[] = [
            'mes' => $m,
            'no_pagados' => $noPagados,
        ];
    }

    return response()->json($resultado);
}


}
