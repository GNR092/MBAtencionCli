<?php

namespace App\Http\Controllers;

use App\Models\Cuentas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class RetroactivoController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        if (! $user || $user->role !== 'administrador') {
            return redirect('/inicio-de-sesion');
        }

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

        $query->where('cuentasporpagar.es_retroactivo', true);

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

        return view('retroactivo', compact('grupos', 'totalPendiente', 'totalPagado', 'cuentasRaw'));
    }
}
