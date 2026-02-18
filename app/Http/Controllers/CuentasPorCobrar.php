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
        return redirect()->route('cuentasCobrar');
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
            ->whereNotNull('fecha_creacion')
            ->whereNotNull('fecha_terminacion')
            ->get();

        foreach ($contracts as $contract) {
            $inicio = Carbon::parse($contract->fecha_creacion)->startOfMonth();
            $fin = Carbon::parse($contract->fecha_terminacion)->startOfMonth();

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
        
        try {
            $mesXml = Carbon::parse($xml->mes)->format('Y-m');
        } catch (\Exception $e) {
            return; 
        }

        
        $contract = Contract::where('user_id', $xml->id_user)->first();
        if (!$contract) return;

        
        $cuenta = DB::table('cuentasporpagar')
            ->where('id_contract', $contract->id)
            ->whereRaw("JSON_EXTRACT(mesesdepago, '$.mes') = ?", [$mesXml])
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
                    'estado' => 'parcial',
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

            
            
            
            $montoPagadoXml = $importeBaseXml;
            $montoPagadoTotal = floatval($montoPagadoXml);

            
            $totalPagar = max(0, $importeBaseMes - $isrXml);

            $saldoPendiente = max(0, $totalPagar - $montoPagadoTotal);

            $estado = ($saldoPendiente == 0) ? 'pagado' : 'parcial';

            
            $mesesPagados = json_decode($cuenta->mesespagados ?? '[]', true);
            if (!is_array($mesesPagados)) $mesesPagados = [];
            if (!in_array($mesXml, $mesesPagados)) {
                $mesesPagados[] = $mesXml;
            }

            
            DB::table('cuentasporpagar')
                ->where('id_cuentas_por_pagar', $cuenta->id_cuentas_por_pagar)
                ->update([
                    'xml_file_id' => $xml->id,
                    'saldo_neto' => $totalPagar,
                    'isr' => $isrXml,
                    'monto_pagado' => $montoPagadoTotal,
                    'saldo_pendiente' => $saldoPendiente,
                    'estado' => $estado,
                    'mesespagados' => json_encode(array_values($mesesPagados)),
                    'updated_at' => now()
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
        $user = Auth::user();
        if (!$user) return redirect('/inicio-de-sesion');

        
        $this->calcularCuentasPorPagar($user->id);

        
        $xmls = XmlFile::where('id_user', $user->id)->get();
        foreach ($xmls as $xml) {
            $this->actualizarConXML($xml);
        }

        
        $this->calcularMontos();

        $query = Cuentas::with('contract')
            ->leftJoin('xml_files', 'cuentasporpagar.xml_file_id', '=', 'xml_files.id')
            ->leftJoin('contract', 'cuentasporpagar.id_contract', '=', 'contract.id')
            ->leftJoin('users', 'contract.user_id', '=', 'users.id')
            ->leftJoin('impuesto', 'xml_files.id', '=', 'impuesto.xml_file_id')
            ->select(
                'cuentasporpagar.*',
                'users.name as name',
                'contract.importe_bruto_renta as importeBase',
                'contract.proyecto as proyecto',
            )
            ->where('users.id', $user->id)
            ->where('cuentasporpagar.estado', '!=', 'pagado')
            ->whereRaw("JSON_UNQUOTE(JSON_EXTRACT(cuentasporpagar.mesesdepago, '$.mes')) <= ?", [date('Y-m')]);

            
            
            
            if ($request->filled('month')) {

                $selectedMonth = $request->month; 

                $query->where(function ($q) use ($selectedMonth) {
                    $q->where('cuentasporpagar.mesesdepago->mes', $selectedMonth);
                });
            }


        if ($request->filled('search') && $request->filled('categoria')) {
            $search = $request->input('search');
            $categoria = $request->input('categoria');

            switch ($categoria) {
                case 'mes':
                    $query->where('cuentasporpagar.mesesdepago', 'LIKE', "%{$search}%");
                    break;
                case 'proyecto':
                    $query->where('contract.proyecto', 'LIKE', "%{$search}%");
                    break;
                case 'estado':
                    $query->where('cuentasporpagar.estado', 'LIKE', "%{$search}%");
                    break;
            }
        }

        $this->aplicarFiltros($query, $request);

        $cuentas = $query->paginate(6)->appends($request->query());

        if ($request->expectsJson()) {
            $html = view('cuentasCobrar', compact('cuentas'))->render();
            return response()->json(['html' => $html]);
        }

        return view('cuentasCobrar', compact('cuentas'));
}

public function graficaAnualNoPagados($year)
{
    $user = Auth::user();
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
