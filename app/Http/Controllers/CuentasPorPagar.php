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
                DB::raw("COALESCE(proyectos.nombre_proyecto, 'Sin proyecto') as proyecto"),
            );

        $this->aplicarExclusionCancelados($query);

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
            ->leftJoin('users', 'contract.user_id', '=', 'users.id')
            ->leftJoin('regimen_fiscals', 'users.id_regimen', '=', 'regimen_fiscals.id_regimen')
            ->select(
                'cuentasporpagar.id_cuentas_por_pagar',
                'cuentasporpagar.id_contract',
                'cuentasporpagar.monto_pagado',
                'cuentasporpagar.estado',
                'contract.importe_bruto_renta as importeBaseContrato',
                'regimen_fiscals.nombre_regimen as regimenFiscal',
                'cuentasporpagar.mesesdepago'
            )
            ->selectSub(function ($subQuery) {
                $subQuery->from('impuesto as i')
                    ->select('i.importeBase')
                    ->whereColumn('i.xml_file_id', 'cuentasporpagar.xml_file_id')
                    ->orderByDesc('i.importeBase')
                    ->limit(1);
            }, 'importeBaseXML');

        $this->aplicarExclusionCancelados($cuentas, 'cuentasporpagar');

        $cuentas = $cuentas->get();

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
            $montoPagado = floatval($cuenta->monto_pagado ?? 0);
            $saldoPendiente = round(max(0, $saldoNeto - $montoPagado), 2);

            if (($cuenta->estado ?? null) === 'pagado') {
                $saldoPendiente = 0;
            }

            $mesesCubiertos = 1;
            if ($saldoNeto > 0) {
                $mesesCubiertos = max(1, (int) ceil($montoPagado / $saldoNeto));
            }
            $esExtra = $mesesCubiertos > 1;

            DB::table('cuentasporpagar')
                ->where('id_cuentas_por_pagar', $cuenta->id_cuentas_por_pagar)
                ->update([
                    'tasaCuota' => $tasaCuota,
                    'isr' => $isr,
                    'saldo_neto' => $saldoNeto,
                    'saldo_pendiente' => $saldoPendiente,
                    'meses_cubiertos' => $mesesCubiertos,
                    'es_extra' => $esExtra,
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
            ->leftJoin('users', 'contract.user_id', '=', 'users.id')
            ->leftJoin('regimen_fiscals', 'users.id_regimen', '=', 'regimen_fiscals.id_regimen')
            ->select(
                'cuentasporpagar.id_cuentas_por_pagar',
                'cuentasporpagar.id_contract',
                'cuentasporpagar.monto_pagado',
                'cuentasporpagar.estado',
                'cuentasporpagar.mesesdepago',
                'contract.importe_bruto_renta as importeBaseContrato',
                'regimen_fiscals.nombre_regimen as regimenFiscal',
            )
            ->selectSub(function ($subQuery) {
                $subQuery->from('impuesto as i')
                    ->select('i.importeBase')
                    ->whereColumn('i.xml_file_id', 'cuentasporpagar.xml_file_id')
                    ->orderByDesc('i.importeBase')
                    ->limit(1);
            }, 'importeBaseXML');

        $this->aplicarExclusionCancelados($cuentas, 'cuentasporpagar');

        $cuentas = $cuentas->get();

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

            if (($cuenta->estado ?? null) === 'pagado') {
                $saldoPendiente = 0;
            }

            $mesesCubiertos = 1;
            if ($saldoNeto > 0) {
                $mesesCubiertos = max(1, (int) ceil($montoPagado / $saldoNeto));
            }
            $esExtra = $mesesCubiertos > 1;

            DB::table('cuentasporpagar')
                ->where('id_cuentas_por_pagar', $cuenta->id_cuentas_por_pagar)
                ->update([
                    'tasaCuota' => $tasaCuota,
                    'isr' => $isr,
                    'saldo_neto' => $saldoNeto,
                    'saldo_pendiente' => $saldoPendiente,
                    'meses_cubiertos' => $mesesCubiertos,
                    'es_extra' => $esExtra,
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

        $mesActual = Carbon::now()->format('Y-m');

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
            // Si ya existe en cuentasporpagar o fue eliminado a retroactivos_eliminados, no crear de nuevo
            if (DB::table('cuentasporpagar')->where('uuid', $xml->uuid)->exists()) {
                continue;
            }

            if (! empty($xml->uuid) && DB::table('retroactivos_eliminados')->where('uuid', $xml->uuid)->exists()) {
                continue;
            }

            $fueEliminadoPorContratoMes = DB::table('retroactivos_eliminados')
                ->where('id_contract', $contract->id)
                ->where('mes_pago', $mesXml)
                ->exists();

            if ($fueEliminadoPorContratoMes) {
                continue;
            }

            $esRetroactivo = $mesXml !== $mesActual;

            DB::table('cuentasporpagar')->insert([
                'uuid' => $xml->uuid,
                'id_contract' => $contract->id,
                'id_user_depto' => $contract->id_user_depto,
                'origen' => 'xml',
                'xml_file_id' => $xml->id,
                'mes_pago' => $mesXml,
                'es_retroactivo' => $esRetroactivo,
                'mesesdepago' => json_encode(['mes' => $mesXml]),
                'estado' => 'pendiente',
                'saldo_neto' => $contract->importe_bruto_renta,
                'monto_pagado' => 0,
                'meses_cubiertos' => 1,
                'es_extra' => false,
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

        $mesActual = Carbon::now()->format('Y-m');

        foreach ($contracts as $contract) {
            $fechaStart = $contract->fecha_inicio ?? $contract->fecha_creacion ?? $contract->fecha;
            $fechaEndContrato = $contract->fecha_terminacion
                ? Carbon::parse($contract->fecha_terminacion)->startOfMonth()
                : Carbon::now()->addMonths(12)->startOfMonth();
            $fechaMaxGeneracion = Carbon::now()->startOfMonth();
            $fechaEnd = $fechaEndContrato->lessThan($fechaMaxGeneracion) ? $fechaEndContrato : $fechaMaxGeneracion;

            $inicio = Carbon::parse($fechaStart)->startOfMonth();
            $fin = $fechaEnd;

            if ($inicio->greaterThan($fin)) {
                continue;
            }

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

                $fueEliminado = DB::table('retroactivos_eliminados')
                    ->where('id_contract', $contract->id)
                    ->where('mes_pago', $mes)
                    ->exists();

                if ($fueEliminado) {
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

                $esRetroactivo = $mes !== $mesActual;

                DB::table('cuentasporpagar')->insert([
                    'id_contract' => $contract->id,
                    'id_user_depto' => $contract->id_user_depto,
                    'origen' => 'esperado',
                    'mes_pago' => $mes,
                    'es_retroactivo' => $esRetroactivo,
                    'mesesdepago' => json_encode(['mes' => $mes]),
                    'estado' => 'pendiente',
                    'saldo_neto' => $importeBase,
                    'xml_file_id' => null,
                    'mesespagados' => json_encode([]),
                    'monto_pagado' => 0,
                    'meses_cubiertos' => 1,
                    'es_extra' => false,
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

        $driver = DB::connection()->getDriverName();
        $deptoDetalleMapSql = $driver === 'pgsql'
            ? "(SELECT STRING_AGG(CONCAT_WS('|', ud.nombre, COALESCE(NULLIF(ud.predial, ''), 'N/A'), COALESCE(NULLIF(ud.tipo, ''), 'N/A')), ';') FROM user_depto ud WHERE ud.id_user_p = contract.id_user_p)"
            : "(SELECT GROUP_CONCAT(CONCAT_WS('|', ud.nombre, COALESCE(NULLIF(ud.predial, ''), 'N/A'), COALESCE(NULLIF(ud.tipo, ''), 'N/A')) SEPARATOR ';') FROM user_depto ud WHERE ud.id_user_p = contract.id_user_p)";
        $departamentosUsuarioSql = $driver === 'pgsql'
            ? "(SELECT ARRAY_TO_STRING(ARRAY_AGG(DISTINCT ud.nombre ORDER BY ud.nombre), ', ') FROM user_depto ud WHERE ud.id_user_p = contract.id_user_p)"
            : "(SELECT GROUP_CONCAT(DISTINCT ud.nombre ORDER BY ud.nombre SEPARATOR ', ') FROM user_depto ud WHERE ud.id_user_p = contract.id_user_p)";
        $predialesUsuarioSql = $driver === 'pgsql'
            ? "(SELECT ARRAY_TO_STRING(ARRAY_AGG(DISTINCT ud.predial ORDER BY ud.predial), ', ') FROM user_depto ud WHERE ud.id_user_p = contract.id_user_p AND ud.predial IS NOT NULL AND ud.predial != '' AND ud.predial != 'N/A')"
            : "(SELECT GROUP_CONCAT(DISTINCT ud.predial ORDER BY ud.predial SEPARATOR ', ') FROM user_depto ud WHERE ud.id_user_p = contract.id_user_p AND ud.predial IS NOT NULL AND ud.predial != '' AND ud.predial != 'N/A')";
        $tiposUsuarioSql = $driver === 'pgsql'
            ? "(SELECT ARRAY_TO_STRING(ARRAY_AGG(DISTINCT ud.tipo ORDER BY ud.tipo), ', ') FROM user_depto ud WHERE ud.id_user_p = contract.id_user_p AND ud.tipo IS NOT NULL AND ud.tipo != '')"
            : "(SELECT GROUP_CONCAT(DISTINCT ud.tipo ORDER BY ud.tipo SEPARATOR ', ') FROM user_depto ud WHERE ud.id_user_p = contract.id_user_p AND ud.tipo IS NOT NULL AND ud.tipo != '')";
        $mesSubidaSql = $driver === 'pgsql'
            ? "TO_CHAR(xml_files.created_at, 'YYYY-MM')"
            : "DATE_FORMAT(xml_files.created_at, '%Y-%m')";

        $query = Cuentas::with('contract')
            ->leftJoin('xml_files', 'cuentasporpagar.xml_file_id', '=', 'xml_files.id')
            ->leftJoin('contract', 'cuentasporpagar.id_contract', '=', 'contract.id')
            ->leftJoin('users', 'contract.user_id', '=', 'users.id')
            ->leftJoin('user_proyectos', 'contract.id_user_p', '=', 'user_proyectos.id_user_p')
            ->leftJoin('proyectos', 'user_proyectos.id_proyecto', '=', 'proyectos.id_proyecto')
            ->leftJoin('razones_sociales', 'proyectos.id_razon_social', '=', 'razones_sociales.id_razon_social')
            ->leftJoin('regimen_fiscals', 'users.id_regimen', '=', 'regimen_fiscals.id_regimen')
            ->select(
                'cuentasporpagar.*',
                'users.name as name',
                'contract.importe_bruto_renta as importeBase',
                'contract.tipo as contract_tipo',
                DB::raw("COALESCE(proyectos.nombre_proyecto, 'Sin proyecto') as proyecto"),
                DB::raw("COALESCE(razones_sociales.nombre_razon_social, 'Sin razon social') as razon_social"),
                'xml_files.departamento',
                DB::raw("{$deptoDetalleMapSql} as depto_detalle_map"),
                DB::raw("{$departamentosUsuarioSql} as departamentos_usuario"),
                DB::raw("{$predialesUsuarioSql} as prediales_usuario"),
                DB::raw("{$tiposUsuarioSql} as tipos_usuario"),
                DB::raw("COALESCE(NULLIF(user_proyectos.metodo_pago, ''), users.metodo_pago) as metodo_pago"),
                DB::raw("{$mesSubidaSql} as mes_subida"),
            );

        $this->aplicarExclusionCancelados($query);
        $selectedMonth = $request->month ?? now()->format('Y-m');
        $query->where('cuentasporpagar.mes_pago', $selectedMonth);
        $query->where('cuentasporpagar.es_retroactivo', false);
        $query->where(function ($q) {
            $q->whereNotNull('cuentasporpagar.uuid')
                ->orWhereNotExists(function ($sub) {
                    $sub->select(DB::raw(1))
                        ->from('cuentasporpagar as c2')
                        ->whereColumn('c2.id_contract', 'cuentasporpagar.id_contract')
                        ->whereColumn('c2.mes_pago', 'cuentasporpagar.mes_pago')
                        ->whereNotNull('c2.uuid');
                });
        });

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

    public function tablasControl(Request $request)
    {
        $user = Auth::user();
        if (! $user) {
            return redirect('/inicio-de-sesion');
        }

        $this->seedDesdeXmlFiles();
        $this->generarCuentasDesdeContratos();
        $this->recalcularSaldos();

        $selectedMonth = $request->month ?? now()->format('Y-m');
        if (! preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', (string) $selectedMonth)) {
            $selectedMonth = now()->format('Y-m');
        }
        $mesTabLabel = strtoupper($selectedMonth);
        try {
            $mesDate = Carbon::createFromFormat('Y-m', $selectedMonth);
            $meses = [
                1 => 'ENERO', 2 => 'FEBRERO', 3 => 'MARZO', 4 => 'ABRIL', 5 => 'MAYO', 6 => 'JUNIO',
                7 => 'JULIO', 8 => 'AGOSTO', 9 => 'SEPTIEMBRE', 10 => 'OCTUBRE', 11 => 'NOVIEMBRE', 12 => 'DICIEMBRE',
            ];
            $mesTabLabel = $meses[(int) $mesDate->format('n')].' '.$mesDate->format('Y');
        } catch (\Exception $e) {
        }

        $legacyTabs = [
            'bdd-febrero' => 'principal', 'bdd-no-pagado' => 'no-pagado', 'bdd-deber-ser' => 'deber-ser',
            'bdd-pagado' => 'pagado', 'bdd-extra' => 'extra',
        ];

        $requestedTab = $request->query('tab', 'principal');
        if (array_key_exists($requestedTab, $legacyTabs)) {
            $requestedTab = $legacyTabs[$requestedTab];
        }

        $tabs = [
            'principal' => $mesTabLabel, 'no-pagado' => 'NO PAGADO', 'deber-ser' => 'DEBER SER',
            'pagado' => 'PAGADO', 'extra' => 'EXTRA',
        ];
        $activeTab = $requestedTab;
        if (! array_key_exists($activeTab, $tabs)) {
            $activeTab = 'principal';
        }
        $pendingTabLabel = null;

        if (! in_array($activeTab, ['principal', 'no-pagado', 'deber-ser', 'pagado', 'extra'], true)) {
            $registros = collect();
            $totalNeto = 0;
            $totalPagado = 0;
            $totalPendiente = 0;
            $pendingTabLabel = $tabs[$activeTab];

            return view('tablas-control', compact('registros', 'selectedMonth', 'totalNeto', 'totalPagado', 'totalPendiente', 'tabs', 'activeTab', 'pendingTabLabel'));
        }

        $driver = DB::connection()->getDriverName();

        $incrementoVigenteSql = $driver === 'pgsql'
            ? "(SELECT ii.importe_base FROM incrementos_importe ii WHERE ii.id_contract = contract.id AND TO_CHAR(ii.fecha_inicio, 'YYYY-MM') <= cuentasporpagar.mes_pago AND (ii.fecha_fin IS NULL OR TO_CHAR(ii.fecha_fin, 'YYYY-MM') >= cuentasporpagar.mes_pago) ORDER BY ii.fecha_inicio DESC LIMIT 1)"
            : "(SELECT ii.importe_base FROM incrementos_importe ii WHERE ii.id_contract = contract.id AND DATE_FORMAT(ii.fecha_inicio, '%Y-%m') <= cuentasporpagar.mes_pago AND (ii.fecha_fin IS NULL OR DATE_FORMAT(ii.fecha_fin, '%Y-%m') >= cuentasporpagar.mes_pago) ORDER BY ii.fecha_inicio DESC LIMIT 1)";

        $registros = Cuentas::query()
            ->leftJoin('xml_files', 'cuentasporpagar.xml_file_id', '=', 'xml_files.id')
            ->leftJoin('contract', 'cuentasporpagar.id_contract', '=', 'contract.id')
            ->leftJoin('users', 'contract.user_id', '=', 'users.id')
            ->leftJoin('user_proyectos', 'contract.id_user_p', '=', 'user_proyectos.id_user_p')
            ->leftJoin('proyectos', 'user_proyectos.id_proyecto', '=', 'proyectos.id_proyecto')
            ->leftJoin('razones_sociales', 'proyectos.id_razon_social', '=', 'razones_sociales.id_razon_social')
            ->select(
                'cuentasporpagar.id_cuentas_por_pagar', 'cuentasporpagar.estado', 'cuentasporpagar.mes_pago',
                'cuentasporpagar.mesesdepago', 'cuentasporpagar.saldo_neto', 'cuentasporpagar.monto_pagado',
                'cuentasporpagar.meses_cubiertos', 'cuentasporpagar.es_extra', 'cuentasporpagar.saldo_pendiente',
                'cuentasporpagar.isr', 'users.name as inversionista',
                DB::raw("COALESCE(NULLIF(user_proyectos.metodo_pago, ''), users.metodo_pago) as metodo_pago"),
                'contract.id_user_p', 'contract.importe_bruto_renta as contrato_importe',
                DB::raw("{$incrementoVigenteSql} as incremento_vigente"),
                DB::raw("COALESCE(proyectos.nombre_proyecto, 'Sin proyecto') as proyecto"),
                DB::raw("COALESCE(razones_sociales.nombre_razon_social, 'Sin razón social') as razon_social"),
                DB::raw("COALESCE(razones_sociales.rfc, '') as rfc_oculto"),
                'xml_files.departamento as xml_departamentos'
            );

        $this->aplicarExclusionCancelados($registros);

        $registros = $registros
            ->where(function ($query) use ($activeTab, $selectedMonth) {
                if ($activeTab === 'principal') {
                    $query->where('cuentasporpagar.mes_pago', $selectedMonth)
                        ->where('cuentasporpagar.origen', 'xml')
                        ->whereNotNull('cuentasporpagar.xml_file_id')
                        ->whereNotNull('cuentasporpagar.uuid');

                    return;
                }

                if ($activeTab === 'deber-ser') {
                    $query->where('cuentasporpagar.mes_pago', $selectedMonth);

                    return;
                }

                if ($activeTab === 'no-pagado') {
                    $query->where('cuentasporpagar.mes_pago', '<', $selectedMonth)
                        ->where('cuentasporpagar.estado', '!=', 'pagado')
                        ->where('contract.estado', 'activo');

                    return;
                }

                if ($activeTab === 'pagado') {
                    $query->where(function ($pagado) use ($selectedMonth) {
                        $pagado->where(function ($mesActual) use ($selectedMonth) {
                            $mesActual->where('cuentasporpagar.mes_pago', $selectedMonth)
                                ->where('cuentasporpagar.estado', 'pagado');
                        })->orWhere(function ($retroPagado) use ($selectedMonth) {
                            $retroPagado->where('cuentasporpagar.mes_pago', '<', $selectedMonth)
                                ->where('cuentasporpagar.es_retroactivo', true)
                                ->where('cuentasporpagar.estado', 'pagado');
                        });
                    });

                    return;
                }

                if ($activeTab === 'extra') {
                    $query->where('cuentasporpagar.mes_pago', '<', $selectedMonth)
                        ->where('cuentasporpagar.es_retroactivo', true);

                    return;
                }

                $query->where('cuentasporpagar.mes_pago', $selectedMonth);
            })
            ->orderBy('users.name')
            ->orderBy('cuentasporpagar.id_cuentas_por_pagar')
            ->get();

        $deptosPorUserProyecto = DB::table('user_depto')
            ->select('id_user_p', 'nombre', 'tipo', 'predial', 'importe')
            ->get()
            ->groupBy('id_user_p');

        $normalizarNombre = fn (?string $nombre): string => strtolower(preg_replace('/\s+/', '', trim((string) $nombre)));

        $registros = $registros->map(function ($registro) use ($deptosPorUserProyecto, $normalizarNombre) {
            $conceptoIdx = null;
            $meses = json_decode((string) $registro->mesesdepago, true);
            if (is_array($meses) && array_key_exists('concepto_idx', $meses)) {
                $conceptoIdx = is_numeric($meses['concepto_idx']) ? (int) $meses['concepto_idx'] : null;
            }

            $departamentosXml = collect(preg_split('/[,;]+/', (string) ($registro->xml_departamentos ?? '')))
                ->map(fn ($item) => trim($item))
                ->filter()
                ->values();

            $departamento = $departamentosXml->isNotEmpty()
                ? ($conceptoIdx !== null ? ($departamentosXml->get($conceptoIdx) ?? $departamentosXml->first()) : $departamentosXml->first())
                : null;

            $deptoData = null;
            $deptosUsuario = $deptosPorUserProyecto->get($registro->id_user_p, collect());
            if ($deptosUsuario->isNotEmpty()) {
                $deptoData = $deptosUsuario->first(fn ($d) => $normalizarNombre($d->nombre) === $normalizarNombre($departamento));
                if (! $deptoData) {
                    $deptoData = $deptosUsuario->first(function ($d) {
                        $predial = trim((string) ($d->predial ?? ''));

                        return $predial !== '' && strtoupper($predial) !== 'N/A';
                    }) ?? $deptosUsuario->first();
                }
            }

            $tipoRaw = strtolower(trim((string) ($deptoData->tipo ?? '')));
            $tipoDepartamento = match ($tipoRaw) {
                'campus' => 'Campus',
                'condominio', 'condominios' => 'Condominios',
                default => 'N/A',
            };

            $registro->departamento = $departamento ?: ($deptoData->nombre ?? 'N/A');
            $registro->tipo_departamento = $tipoDepartamento;
            $predial = trim((string) ($deptoData->predial ?? ''));
            $registro->cuenta_predial = strtoupper($predial) === 'N/A' ? '' : $predial;
            $registro->importe_renta = $registro->incremento_vigente ?? ($deptoData->importe ?? null) ?? ($registro->contrato_importe ?? 0);

            return $registro;
        });

        $totalesBase = $registros->unique('id_cuentas_por_pagar');
        $totalNeto = $totalesBase->sum('saldo_neto');
        $totalPagado = $totalesBase->sum('monto_pagado');
        $totalPendiente = $totalesBase->sum('saldo_pendiente');

        return view('tablas-control', compact('registros', 'selectedMonth', 'totalNeto', 'totalPagado', 'totalPendiente', 'tabs', 'activeTab', 'pendingTabLabel'));
    }

    public function actualizarEstado(Request $request, $id)
    {
        $nuevoEstado = $request->input('estado');

        if (! in_array($nuevoEstado, ['pendiente', 'parcial', 'pagado', 'vencido'])) {
            return response()->json(['success' => false, 'message' => 'Estado inválido']);
        }

        $cuenta = DB::table('cuentasporpagar')->where('id_cuentas_por_pagar', $id)->first();
        if (! $cuenta) {
            return response()->json(['success' => false, 'message' => 'Cuenta no encontrada']);
        }

        if (! empty($cuenta->es_retroactivo) && empty($cuenta->xml_file_id)) {
            return response()->json([
                'success' => false,
                'message' => 'No puedes cambiar el estado de un retroactivo sin factura subida.',
            ]);
        }

        try {
            $mesCuenta = Carbon::createFromFormat('Y-m', (string) $cuenta->mes_pago)->startOfMonth();
            $mesActual = now()->startOfMonth();

            if ($mesCuenta->lt($mesActual) && $nuevoEstado !== (string) $cuenta->estado) {
                return response()->json([
                    'success' => false,
                    'message' => 'El periodo de esta cuenta ya cerro y no permite cambios de estado.',
                ]);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'No se pudo validar el periodo de la cuenta.',
            ]);
        }

        $updates = ['estado' => $nuevoEstado, 'updated_at' => now()];

        if ($nuevoEstado === 'pagado') {
            $updates['monto_pagado'] = $cuenta->saldo_neto;
            $updates['saldo_pendiente'] = 0;
            $updates['meses_cubiertos'] = $cuenta->meses_cubiertos ?? 1;
            $updates['es_extra'] = (($cuenta->meses_cubiertos ?? 1) > 1);
        } elseif ($nuevoEstado === 'pendiente') {
            $updates['monto_pagado'] = 0;
            $updates['saldo_pendiente'] = $cuenta->saldo_neto;
            $updates['meses_cubiertos'] = 1;
            $updates['es_extra'] = false;
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
        $mesesQuery = DB::table('cuentasporpagar')
            ->whereNotNull('mes_pago')
            ->where('mes_pago', 'like', $year.'-%')
            ->distinct();

        $this->aplicarExclusionCancelados($mesesQuery, 'cuentasporpagar');

        $meses = $mesesQuery
            ->pluck('mes_pago')
            ->toArray();

        return response()->json($meses);
    }

    private function aplicarExclusionCancelados($query, string $alias = 'cuentasporpagar'): void
    {
        $query->whereNotExists(function ($sub) use ($alias) {
            $sub->select(DB::raw(1))
                ->from('retroactivos_eliminados as re')
                ->where(function ($match) use ($alias) {
                    $match->where(function ($byContractMonth) use ($alias) {
                        $byContractMonth->whereColumn('re.id_contract', "{$alias}.id_contract")
                            ->whereColumn('re.mes_pago', "{$alias}.mes_pago");
                    })->orWhere(function ($byUuid) use ($alias) {
                        $byUuid->whereNotNull('re.uuid')
                            ->where('re.uuid', '!=', '')
                            ->whereRaw("LOWER(re.uuid) = LOWER({$alias}.uuid)");
                    });
                });
        });
    }
}
