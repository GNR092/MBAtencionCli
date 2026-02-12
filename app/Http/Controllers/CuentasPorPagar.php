<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use App\Models\Contract;
use App\Models\XmlFile;
use App\Models\Cuentas;
use App\Models\Impuesto;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\cuentasExport;
use App\Models\IncrementoImporte;


class CuentasPorPagar extends Controller
{

public function export(Request $request)
{
        $query = Cuentas::with('contract')
            ->leftJoin('xml_files', 'cuentasporpagar.xml_file_id', '=', 'xml_files.id')
            ->leftJoin('contract', 'cuentasporpagar.id_contract', '=', 'contract.id')
            ->leftJoin('users', 'contract.user_id', '=', 'users.id')
            ->leftJoin('impuesto', 'xml_files.id', '=', 'impuesto.xml_file_id')
            ->select(
                'cuentasporpagar.*',
                'users.name as name',
                'contract.importe_bruto_renta as importeBase',
                'contract.proyecto as proyecto'
            );

        
        $this->aplicarFiltros($query, $request);

        
         

         

            if ($request->filled('desde')) {
                $desdeMes = substr($request->desde, 0, 7); 
                $query->where('cuentasporpagar.mesesdepago->mes', '>=', $desdeMes);
            }

            if ($request->filled('hasta')) {
                $hastaMes = substr($request->hasta, 0, 7);
                $query->where('cuentasporpagar.mesesdepago->mes', '<=', $hastaMes);
            }


            if ($request->filled('estado')) {
                $query->where('cuentasporpagar.estado', $request->estado);
            }

            $totalPendiente = (clone $query)->sum('cuentasporpagar.saldo_pendiente');
            $totalPagado =(clone $query)->sum('cuentasporpagar.monto_pagado');

        return Excel::download(
            new cuentasExport($query, $totalPendiente, $totalPagado),
            'cuentas.xlsx'
        );
}

public function calculodesaldos()
{
    $cuentas = DB::table('cuentasporpagar')
        ->leftJoin('contract', 'cuentasporpagar.id_contract', '=', 'contract.id')
        ->leftJoin('xml_files', 'cuentasporpagar.xml_file_id', '=', 'xml_files.id')
                    ->leftJoin('impuesto', 'xml_files.id', '=', 'impuesto.xml_file_id') 
                    ->leftJoin('users', 'contract.user_id', '=', 'users.id')
                    ->leftJoin('regimen_fiscals', 'users.id_regimen', '=', 'regimen_fiscals.id_regimen') 
                    ->select(
                        'cuentasporpagar.id_cuentas_por_pagar',
                        'cuentasporpagar.id_contract',
                        'cuentasporpagar.monto_pagado',
                        'cuentasporpagar.estado',
                        'contract.importe_bruto_renta as importeBaseContrato',
                        'impuesto.importeBase as importeBaseXML',       
                        'regimen_fiscals.nombre_regimen as regimenFiscal', 
                        'cuentasporpagar.mesesdepago'
                    )        ->get();

    foreach ($cuentas as $cuenta) {

        
        
        
        $mesData = null;
        if (!empty($cuenta->mesesdepago)) {
            $decoded = json_decode($cuenta->mesesdepago, true);
            if (is_array($decoded) && isset($decoded['mes'])) {
                $mesData = $decoded['mes'];
            } elseif (is_string($decoded)) {
                $mesData = $decoded;
            }
        }

        if (!$mesData) continue;

        try {
            $mesDate = Carbon::createFromFormat('Y-m', $mesData)->startOfMonth();
        } catch (\Exception $e) {
            continue;
        }

        
        
        
        $incremento = DB::table('incrementos_importe')
            ->where('id_contract', $cuenta->id_contract)
            ->where(function ($q) use ($mesDate) {
                $q->whereDate('fecha_inicio', '<=', $mesDate->copy()->endOfMonth());
            })
            ->where(function ($q) use ($mesDate) {
                $q->whereNull('fecha_fin')
                  ->orWhereDate('fecha_fin', '>=', $mesDate->copy()->startOfMonth());
            })
            ->orderByDesc('fecha_inicio')
            ->first();

        
        
        
        $importeBase = $cuenta->importeBaseXML              
            ?? ($incremento->importe_base ?? null)         
            ?? $cuenta->importeBaseContrato;               

        $importeBase = floatval($importeBase);

        
        
        
        $regimen = strtolower($cuenta->regimenFiscal ?? '');
        $tasaCuota = $regimen === 'resico' ? 0.0125 :
                     ($regimen === 'arrendamiento' ? 0.10 : 0.00);

        $isr = round($importeBase * $tasaCuota, 2);

        
        
        
        $saldoNeto = round($importeBase - $isr, 2);
        $saldoPendiente = round($saldoNeto - ($cuenta->monto_pagado ?? 0), 2);

        
        
        
        if ($cuenta->estado === 'pagado') {
    
    continue;
}
 




if ($cuenta->monto_pagado == 0) {
    $estado = 'pendiente';
}


elseif ($cuenta->monto_pagado > 0 && $cuenta->monto_pagado < $saldoNeto) {
    $estado = 'parcial';
}



elseif ($cuenta->monto_pagado == $saldoNeto) {
    $estado = 'parcial'; 
    $saldoPendiente = 0;
}


        
        
        
        DB::table('cuentasporpagar')
            ->where('id_cuentas_por_pagar', $cuenta->id_cuentas_por_pagar)
            ->update([
                'tasaCuota'       => $tasaCuota,
                'isr'             => $isr,
                'saldo_neto'      => $saldoNeto,
                'saldo_pendiente' => $saldoPendiente,
                'estado'          => $estado,
                'updated_at'      => now(),
            ]);
    }

    return response()->json(['message' => 'Saldos e ISR actualizados correctamente']);
}



/*Cada mes debe quedar registrado con estado "pendiente" 
inicialmente, aunque no haya XML / factura cargada aún.*/
    public function index(Request $request)
    {
        $user = Session::get('user');
        if (!$user) return redirect('/inicio-de-sesion');

        $this->calculodesaldos();

        $query = Cuentas::with('contract')
            ->leftJoin('xml_files', 'cuentasporpagar.xml_file_id', '=', 'xml_files.id')
            ->leftJoin('contract', 'cuentasporpagar.id_contract', '=', 'contract.id')
            ->leftJoin('users', 'contract.user_id', '=', 'users.id')
            ->leftJoin('impuesto', 'xml_files.id', '=', 'impuesto.xml_file_id')
            ->select(
                'cuentasporpagar.*',
                'cuentasporpagar.monto_pagado',
                'cuentasporpagar.saldo_pendiente',
                'cuentasporpagar.estado',
                'users.name as name',
                'contract.importe_bruto_renta as importeBase',
                'contract.proyecto as proyecto',
            );


            
            
            
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
                case 'name':
                    $query->where('users.name', 'LIKE', "%{$search}%");
                    break;
                case 'estado':
                    $query->where('cuentasporpagar.estado', 'LIKE', "%{$search}%");
                    break;
            }
        }

        $totalPendiente = (clone $query)->sum('cuentasporpagar.saldo_pendiente');
        $totalPagado =(clone $query)->sum('cuentasporpagar.monto_pagado');
        $cuentas = $query->paginate(6)->appends($request->query());
        $proyectos = Contract::select('proyecto')->distinct()->pluck('proyecto');


        return view('viewAdministrador', compact('cuentas','totalPendiente','totalPagado','proyectos'))
        ->with('selectedMonth', $request->month ?? now()->format('Y-m'));
    }


    public function actualizarEstado(Request $request, $id)
    {
        $nuevoEstado = $request->input('estado');

        if (!in_array($nuevoEstado, ['parcial', 'pagado'])) {
            return response()->json(['success' => false, 'message' => 'Estado inválido']);
        }

        DB::table('cuentasporpagar')
            ->where('id_cuentas_por_pagar', $id)
            ->update([
                'estado' => $nuevoEstado,
                'updated_at' => now()
            ]);

        return response()->json(['success' => true]);
    }


public function limpiar()
{
    session()->forget(['search', 'categoria']);
    return redirect()->route('viewAdministrador');
}

    /* ================= FUNCIÓN PARA REUTILIZAR FILTROS ================= */
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
                case 'name':
                    $query->where('users.name', 'LIKE', "%{$search}%");
                    break;
                case 'estado':
                    $query->where('cuentasporpagar.estado', 'LIKE', "%{$search}%");
                    break;
                case 'mes':
                    $query->where('cuentasporpagar.mesesdepago', 'LIKE', "%{$search}%");
                    break;
            }
        }
}

public function graficaAnual($year)
{
    $cuentas = Cuentas::all();

    $resultado = [];

    for ($m = 1; $m <= 12; $m++) {

        $pagados = 0;
        $noPagados = 0;

        foreach ($cuentas as $c) {
            $mesJson = json_decode($c->mesesdepago);

            if (!$mesJson || !isset($mesJson->mes)) continue;

            
            [$y, $month] = explode('-', $mesJson->mes);

            if ((int)$y === (int)$year && (int)$month === $m) {
                if ($c->estado === "pagado") {
                    $pagados += $c->saldo_neto;
                } else {
                    $noPagados += $c->saldo_neto;
                }
            }
        }

        $resultado[] = [
            'mes' => $m,
            'pagados' => $pagados,
            'no_pagados' => $noPagados,
        ];
    }

    return response()->json($resultado);
}

/**
 * Normaliza texto para comparación flexible.
 */
private function normalizar($texto)
{
    $texto = strtolower(trim($texto));
    $texto = iconv('UTF-8', 'ASCII//TRANSLIT', $texto); 
    $texto = preg_replace('/[^a-z0-9]+/', ' ', $texto); 
    return trim(preg_replace('/\s+/', ' ', $texto)); 
}

public function graficaAnualProyecto($year, $proyecto)
{
    $cuentas = DB::table('cuentasporpagar')
        ->leftJoin('contract', 'cuentasporpagar.id_contract', '=', 'contract.id')
        ->select(
            'cuentasporpagar.estado',
            'cuentasporpagar.saldo_neto',
            'cuentasporpagar.mesesdepago',
            'contract.proyecto'
        )
        ->where('contract.proyecto', $proyecto)
        ->get();

    $resultado = [];

    for ($m = 1; $m <= 12; $m++) {

        $pagados = 0;
        $noPagados = 0;

        foreach ($cuentas as $c) {

            $mesJson = json_decode($c->mesesdepago);

            if (!$mesJson || !isset($mesJson->mes)) continue;

            [$y, $month] = explode('-', $mesJson->mes);

            if ((int)$y === (int)$year && (int)$month === $m) {
                if ($c->estado === "pagado") {
                    $pagados += $c->saldo_neto;
                } else {
                    $noPagados += $c->saldo_neto;
                }
            }
        }

        $resultado[] = [
            'mes' => $m,
            'pagados' => $pagados,
            'no_pagados' => $noPagados,
        ];
    }

    return response()->json($resultado);
}




}
