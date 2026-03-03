<?php

namespace App\Http\Controllers;

use App\Exports\cuentasExport;
use App\Models\Contract;
use App\Models\Cuentas;
use App\Models\XmlFile;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class CuentasPorPagar extends Controller
{
    public function export(Request $request)
    {
        $query = Cuentas::with('contract')
            ->leftJoin('xml_files', 'cuentasporpagar.xml_file_id', '=', 'xml_files.id')
            ->leftJoin('contract', 'cuentasporpagar.id_contract', '=', 'contract.id')
            ->leftJoin('users', 'contract.user_id', '=', 'users.id')
            ->leftJoin('impuesto', 'xml_files.id', '=', 'impuesto.xml_file_id')
            ->leftJoin('user_proyectos', 'contract.id_user_p', '=', 'user_proyectos.id_user_p')
            ->leftJoin('proyectos', 'user_proyectos.id_proyecto', '=', 'proyectos.id_proyecto')
            ->select(
                'cuentasporpagar.*',
                'users.name as name',
                'contract.importe_bruto_renta as importeBase',
                DB::raw('COALESCE(proyectos.nombre_proyecto, "Sin proyecto") as proyecto'),
            );

        $this->aplicarFiltros($query, $request);

        if ($request->filled('desde')) {
            $query->where('cuentasporpagar.mes_pago', '>=', substr($request->desde, 0, 7));
        }

        if ($request->filled('hasta')) {
            $query->where('cuentasporpagar.mes_pago', '<=', substr($request->hasta, 0, 7));
        }

        if ($request->filled('estado')) {
            $query->where('cuentasporpagar.estado', $request->estado);
        }

        $totalPendiente = (clone $query)->sum('cuentasporpagar.saldo_pendiente');
        $totalPagado = (clone $query)->sum('cuentasporpagar.monto_pagado');

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
            )->get();

        foreach ($cuentas as $cuenta) {
            $mesData = null;
            if (! empty($cuenta->mesesdepago)) {
                $decoded = json_decode($cuenta->mesesdepago, true);
                if (is_array($decoded) && isset($decoded['mes'])) {
                    $mesData = $decoded['mes'];
                } elseif (is_string($decoded)) {
                    $mesData = $decoded;
                }
            }

            if (! $mesData) {
                continue;
            }

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
            $tasaCuota = $regimen === 'resico' ? 0.0125 : ($regimen === 'arrendamiento' ? 0.10 : 0.00);

            $isr = round($importeBase * $tasaCuota, 2);

            $saldoNeto = round($importeBase - $isr, 2);
            $saldoPendiente = round($saldoNeto - ($cuenta->monto_pagado ?? 0), 2);

            if ($cuenta->estado === 'pagado') {

                continue;
            }

            if ($cuenta->monto_pagado == 0) {
                $estado = 'pendiente';
            } elseif ($cuenta->monto_pagado > 0 && $cuenta->monto_pagado < $saldoNeto) {
                $estado = 'parcial';
            } elseif ($cuenta->monto_pagado >= $saldoNeto) {
                $estado = 'pagado';
                $saldoPendiente = 0;
            }

            DB::table('cuentasporpagar')
                ->where('id_cuentas_por_pagar', $cuenta->id_cuentas_por_pagar)
                ->update([
                    'tasaCuota' => $tasaCuota,
                    'isr' => $isr,
                    'saldo_neto' => $saldoNeto,
                    'saldo_pendiente' => $saldoPendiente,
                    'estado' => $estado,
                    'updated_at' => now(),
                ]);
        }

        return response()->json(['message' => 'Saldos e ISR actualizados correctamente']);
    }

    private function recalcularSaldos(): void
    {
        $cuentas = DB::table('cuentasporpagar')
            ->leftJoin('contract', 'cuentasporpagar.id_contract', '=', 'contract.id')
            ->leftJoin('xml_files', 'cuentasporpagar.xml_file_id', '=', 'xml_files.id')
            ->leftJoin('impuesto', 'xml_files.id', '=', 'impuesto.xml_file_id')
            ->leftJoin('users', 'contract.user_id', '=', 'users.id')
            ->leftJoin('regimen_fiscals', 'users.id_regimen', '=', 'regimen_fiscals.id_regimen')
            ->where('cuentasporpagar.estado', '!=', 'pagado')
            ->select(
                'cuentasporpagar.id_cuentas_por_pagar',
                'cuentasporpagar.id_contract',
                'cuentasporpagar.monto_pagado',
                'cuentasporpagar.mesesdepago',
                'contract.importe_bruto_renta as importeBaseContrato',
                'impuesto.importeBase as importeBaseXML',
                'regimen_fiscals.nombre_regimen as regimenFiscal',
            )->get();

        foreach ($cuentas as $cuenta) {
            $decoded = json_decode($cuenta->mesesdepago, true);
            $mesData = $decoded['mes'] ?? (is_string($decoded) ? $decoded : null);
            if (! $mesData) {
                continue;
            }

            try {
                $mesDate = Carbon::createFromFormat('Y-m', $mesData)->startOfMonth();
            } catch (\Exception $e) {
                continue;
            }

            $incremento = DB::table('incrementos_importe')
                ->where('id_contract', $cuenta->id_contract)
                ->whereDate('fecha_inicio', '<=', $mesDate->copy()->endOfMonth())
                ->where(fn ($q) => $q->whereNull('fecha_fin')->orWhereDate('fecha_fin', '>=', $mesDate->copy()->startOfMonth()))
                ->orderByDesc('fecha_inicio')
                ->value('importe_base');

            $importeBase = floatval($cuenta->importeBaseXML ?? $incremento ?? $cuenta->importeBaseContrato);
            $regimen = strtolower($cuenta->regimenFiscal ?? '');
            $tasaCuota = $regimen === 'resico' ? 0.0125 : ($regimen === 'arrendamiento' ? 0.10 : 0.00);
            $isr = round($importeBase * $tasaCuota, 2);
            $saldoNeto = round($importeBase - $isr, 2);
            $montoPagado = floatval($cuenta->monto_pagado ?? 0);
            $saldoPendiente = round(max(0, $saldoNeto - $montoPagado), 2);

            $estado = $montoPagado <= 0 ? 'pendiente'
                : ($montoPagado >= $saldoNeto ? 'pagado' : 'parcial');

            if ($estado === 'pagado') {
                $saldoPendiente = 0;
            }

            DB::table('cuentasporpagar')
                ->where('id_cuentas_por_pagar', $cuenta->id_cuentas_por_pagar)
                ->update([
                    'tasaCuota' => $tasaCuota,
                    'isr' => $isr,
                    'saldo_neto' => $saldoNeto,
                    'saldo_pendiente' => $saldoPendiente,
                    'estado' => $estado,
                    'updated_at' => now(),
                ]);
        }
    }

    /*Cada mes debe quedar registrado con estado "pendiente"
inicialmente, aunque no haya XML / factura cargada aún.*/
    private function seedDesdeXmlFiles(): void
    {
        $xmls = XmlFile::whereNotNull('fecha_inicio')
            ->whereNotNull('id_proyecto')
            ->whereNotNull('id_user')
            ->get();

        foreach ($xmls as $xml) {
            // Sin folio fiscal no hay clave única confiable
            if (empty($xml->uuid)) {
                continue;
            }

            try {
                $mesXml = Carbon::parse($xml->fecha_inicio)->format('Y-m');
            } catch (\Exception $e) {
                continue;
            }

            $userProyecto = DB::table('user_proyectos')
                ->where('id_user', $xml->id_user)
                ->where('id_proyecto', $xml->id_proyecto)
                ->first();

            if (! $userProyecto) {
                continue;
            }

            $contract = Contract::where('id_user_p', $userProyecto->id_user_p)->first();
            if (! $contract) {
                continue;
            }

            // El folio fiscal (UUID del CFDI) es globalmente único: un registro por factura
            if (DB::table('cuentasporpagar')->where('uuid', $xml->uuid)->exists()) {
                continue;
            }

            DB::table('cuentasporpagar')->insert([
                'uuid' => $xml->uuid,
                'id_contract' => $contract->id,
                'xml_file_id' => $xml->id,
                'mes_pago' => $mesXml,
                'mesesdepago' => json_encode(['mes' => $mesXml]),
                'estado' => 'pendiente',
                'saldo_neto' => $contract->importe_bruto_renta,
                'monto_pagado' => 0,
                'mesespagados' => json_encode([]),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    private function generarCuentasDesdeContratos(): void
    {
        $contracts = Contract::whereNotNull('fecha_inicio')
            ->get();

        foreach ($contracts as $contract) {
            $fechaStart = $contract->fecha_inicio ?? $contract->fecha_creacion ?? $contract->fecha;
            $fechaEnd = $contract->fecha_terminacion
                ? Carbon::parse($contract->fecha_terminacion)->startOfMonth()
                : Carbon::now()->addMonths(12)->startOfMonth();

            $inicio = Carbon::parse($fechaStart)->startOfMonth();
            $fin = $fechaEnd;

            $periodo = CarbonPeriod::create($inicio, '1 month', $fin);

            foreach ($periodo as $date) {
                $mes = $date->format('Y-m');

                $exists = DB::table('cuentasporpagar')
                    ->where('id_contract', $contract->id)
                    ->where('mes_pago', $mes)
                    ->exists();

                if ($exists) {
                    continue;
                }

                $importeIncremento = DB::table('incrementos_importe')
                    ->where('id_contract', $contract->id)
                    ->whereDate('fecha_inicio', '<=', $mes.'-01')
                    ->where(function ($q) use ($mes) {
                        $q->whereNull('fecha_fin')
                            ->orWhereDate('fecha_fin', '>=', $mes.'-01');
                    })
                    ->value('importe_base');

                $importeBase = $importeIncremento ?? $contract->importe_bruto_renta;

                DB::table('cuentasporpagar')->insert([
                    'id_contract' => $contract->id,
                    'mes_pago' => $mes,
                    'mesesdepago' => json_encode(['mes' => $mes]),
                    'estado' => 'pendiente',
                    'saldo_neto' => $importeBase,
                    'xml_file_id' => null,
                    'mesespagados' => json_encode([]),
                    'monto_pagado' => 0,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    public function index(Request $request)
    {
        $user = Auth::user();
        if (! $user) {
            return redirect('/inicio-de-sesion');
        }

        $this->seedDesdeXmlFiles();
        $this->generarCuentasDesdeContratos();
        $this->recalcularSaldos();

        $query = Cuentas::with('contract')
            ->leftJoin('xml_files', 'cuentasporpagar.xml_file_id', '=', 'xml_files.id')
            ->leftJoin('contract', 'cuentasporpagar.id_contract', '=', 'contract.id')
            ->leftJoin('users', 'contract.user_id', '=', 'users.id')
            ->leftJoin('impuesto', 'xml_files.id', '=', 'impuesto.xml_file_id')
            ->leftJoin('user_proyectos', 'contract.id_user_p', '=', 'user_proyectos.id_user_p')
            ->leftJoin('proyectos', 'user_proyectos.id_proyecto', '=', 'proyectos.id_proyecto')
            ->select(
                'cuentasporpagar.*',
                'users.name as name',
                'contract.importe_bruto_renta as importeBase',
                DB::raw('COALESCE(proyectos.nombre_proyecto, "Sin proyecto") as proyecto'),
                DB::raw('DATE_FORMAT(xml_files.created_at, "%Y-%m") as mes_subida'),
            );
        $selectedMonth = $request->month ?? now()->format('Y-m');
        $query->where('cuentasporpagar.mes_pago', $selectedMonth);

        $query->when($request->filled(['search', 'categoria']), function ($q) use ($request) {
            $columnas = [
                'mes' => 'cuentasporpagar.mes_pago',
                'name' => 'users.name',
                'estado' => 'cuentasporpagar.estado',
            ];
            if (array_key_exists($request->categoria, $columnas)) {
                $q->where($columnas[$request->categoria], 'LIKE', '%'.$request->search.'%');
            }
        });

        $totalPendiente = (clone $query)->sum('cuentasporpagar.saldo_pendiente');
        $totalPagado = (clone $query)->sum('cuentasporpagar.monto_pagado');

        $cuentasRaw = $query->orderBy('users.name')->orderBy('cuentasporpagar.id_cuentas_por_pagar')->get();

        $grupos = $cuentasRaw->groupBy('name')->map(function ($grupo, $nombre) {
            return [
                'nombre' => $nombre,
                'cuentas' => $grupo,
                'total_neto' => $grupo->sum('saldo_neto'),
                'total_pagado' => $grupo->sum('monto_pagado'),
                'total_pendiente' => $grupo->sum('saldo_pendiente'),
                'count' => $grupo->count(),
            ];
        })->values();

        $proyectos = \App\Models\Proyecto::orderBy('nombre_proyecto')->select('id_proyecto', 'nombre_proyecto')->get();

        $minYear = (int) DB::table('cuentasporpagar')
            ->whereNotNull('mes_pago')
            ->min(DB::raw('LEFT(mes_pago, 4)'));
        $minYear = $minYear ?: now()->year;

        return view('viewAdministrador', compact('grupos', 'totalPendiente', 'totalPagado', 'proyectos', 'minYear', 'selectedMonth', 'cuentasRaw'))
            ->with('selectedMonth', $selectedMonth);
    }

    public function actualizarEstado(Request $request, $id)
    {
        $nuevoEstado = $request->input('estado');

        if (! in_array($nuevoEstado, ['pendiente', 'parcial', 'pagado', 'vencido'])) {
            return response()->json(['success' => false, 'message' => 'Estado inválido']);
        }

        $cuenta = DB::table('cuentasporpagar')->where('id_cuentas_por_pagar', $id)->first();

        $updates = ['estado' => $nuevoEstado, 'updated_at' => now()];

        if ($nuevoEstado === 'pagado') {
            $updates['monto_pagado'] = $cuenta->saldo_neto;
            $updates['saldo_pendiente'] = 0;
        } elseif ($nuevoEstado === 'pendiente') {
            $updates['monto_pagado'] = 0;
            $updates['saldo_pendiente'] = $cuenta->saldo_neto;
        }

        DB::table('cuentasporpagar')->where('id_cuentas_por_pagar', $id)->update($updates);

        $mesPago = $cuenta->mes_pago;
        $totalPendiente = DB::table('cuentasporpagar')
            ->where('mes_pago', $mesPago)
            ->sum('saldo_pendiente');
        $totalPagado = DB::table('cuentasporpagar')
            ->where('mes_pago', $mesPago)
            ->sum('monto_pagado');

        $updated = DB::table('cuentasporpagar')->where('id_cuentas_por_pagar', $id)->first();

        return response()->json([
            'success' => true,
            'totalPendiente' => number_format($totalPendiente, 2),
            'totalPagado' => number_format($totalPagado, 2),
            'montoPagado' => number_format($updated->monto_pagado, 2),
            'saldoPendiente' => number_format($updated->saldo_pendiente, 2),
        ]);
    }

    public function limpiar()
    {
        session()->forget(['search', 'categoria']);

        return redirect()->route('cuentas-pagar.index');
    }

    /* ================= FUNCIÓN PARA REUTILIZAR FILTROS ================= */
    private function aplicarFiltros(&$query, Request $request)
    {
        if ($request->filled('mes')) {
            $query->where('cuentasporpagar.mes_pago', $request->input('mes'));
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
                    $query->where('cuentasporpagar.mes_pago', 'LIKE', "%{$search}%");
                    break;
            }
        }
    }

    public function graficaAnual($year)
    {
        $cuentas = Cuentas::whereNotNull('mes_pago')
            ->where('mes_pago', 'like', $year.'-%')
            ->get(['mes_pago', 'estado', 'saldo_neto']);

        $resultado = [];
        for ($m = 1; $m <= 12; $m++) {
            $mes = sprintf('%d-%02d', $year, $m);
            $pagados = $cuentas->where('mes_pago', $mes)->where('estado', 'pagado')->sum('saldo_neto');
            $noPagados = $cuentas->where('mes_pago', $mes)->where('estado', '!=', 'pagado')->sum('saldo_neto');
            $resultado[] = ['mes' => $m, 'pagados' => $pagados, 'no_pagados' => $noPagados];
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

    public function graficaAnualProyecto($year, $id_proyecto)
    {
        $cuentas = DB::table('cuentasporpagar')
            ->leftJoin('contract', 'cuentasporpagar.id_contract', '=', 'contract.id')
            ->leftJoin('user_proyectos', 'contract.id_user_p', '=', 'user_proyectos.id_user_p')
            ->leftJoin('proyectos', 'user_proyectos.id_proyecto', '=', 'proyectos.id_proyecto')
            ->select('cuentasporpagar.estado', 'cuentasporpagar.saldo_neto', 'cuentasporpagar.mes_pago')
            ->where('proyectos.id_proyecto', $id_proyecto)
            ->whereNotNull('cuentasporpagar.mes_pago')
            ->where('cuentasporpagar.mes_pago', 'like', $year.'-%')
            ->get();

        $resultado = [];
        for ($m = 1; $m <= 12; $m++) {
            $mes = sprintf('%d-%02d', $year, $m);
            $pagados = $cuentas->where('mes_pago', $mes)->where('estado', 'pagado')->sum('saldo_neto');
            $noPagados = $cuentas->where('mes_pago', $mes)->where('estado', '!=', 'pagado')->sum('saldo_neto');
            $resultado[] = ['mes' => $m, 'pagados' => $pagados, 'no_pagados' => $noPagados];
        }

        return response()->json($resultado);
    }

    public function mesesConFacturas($year)
    {
        $meses = DB::table('cuentasporpagar')
            ->whereNotNull('mes_pago')
            ->where('mes_pago', 'like', $year.'-%')
            ->distinct()
            ->pluck('mes_pago')
            ->toArray();

        return response()->json($meses);
    }
}
