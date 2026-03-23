<?php

namespace App\Http\Controllers;

use App\Models\Cuentas;
use App\Models\RetroactivoEliminado;
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

    public function eliminar(Request $request, $id)
    {
        $user = Auth::user();
        if (! $user || $user->role !== 'administrador') {
            return redirect('/inicio-de-sesion');
        }

        $cuenta = Cuentas::where('id_cuentas_por_pagar', $id)->first();

        if (! $cuenta) {
            return redirect()->back()->withErrors(['message' => 'Cuenta no encontrada.']);
        }

        try {
            DB::beginTransaction();

            RetroactivoEliminado::create([
                'cuenta_original_id' => $cuenta->id_cuentas_por_pagar,
                'id_contract' => $cuenta->id_contract,
                'xml_file_id' => $cuenta->xml_file_id,
                'uuid' => $cuenta->uuid,
                'mes_pago' => $cuenta->mes_pago,
                'es_retroactivo' => $cuenta->es_retroactivo,
                'estado' => $cuenta->estado,
                'saldo_neto' => $cuenta->saldo_neto,
                'monto_pagado' => $cuenta->monto_pagado,
                'saldo_pendiente' => $cuenta->saldo_pendiente,
                'isr' => $cuenta->isr,
                'tasaCuota' => $cuenta->tasaCuota,
                'mesesdepago' => $cuenta->mesesdepago,
                'mesespagados' => $cuenta->mesespagados,
                'mesespendientes' => $cuenta->mesespendientes,
                'eliminado_por' => $user->name,
                'motivo' => $request->input('motivo'),
            ]);

            $cuenta->delete();

            DB::commit();

            return redirect()->back()->with('success', 'Retroactivo eliminado correctamente.');
        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->back()->withErrors(['message' => 'Error al eliminar el retroactivo.']);
        }
    }

    public function eliminados(Request $request)
    {
        $user = Auth::user();
        if (! $user || $user->role !== 'administrador') {
            return redirect('/inicio-de-sesion');
        }

        $query = RetroactivoEliminado::with('contract')
            ->leftJoin('contract', 'retroactivos_eliminados.id_contract', '=', 'contract.id')
            ->leftJoin('users', 'contract.user_id', '=', 'users.id')
            ->leftJoin('xml_files', 'retroactivos_eliminados.xml_file_id', '=', 'xml_files.id')
            ->leftJoin('user_proyectos', 'contract.id_user_p', '=', 'user_proyectos.id_user_p')
            ->leftJoin('proyectos', 'user_proyectos.id_proyecto', '=', 'proyectos.id_proyecto')
            ->select(
                'retroactivos_eliminados.*',
                'users.name as name',
                'contract.importe_bruto_renta as importeBase',
                DB::raw('COALESCE(proyectos.nombre_proyecto, "Sin proyecto") as proyecto'),
                DB::raw('DATE_FORMAT(xml_files.created_at, "%Y-%m") as mes_subida'),
            );

        $query->when($request->filled(['search', 'categoria']), function ($q) use ($request) {
            $columnas = [
                'mes' => 'retroactivos_eliminados.mes_pago',
                'name' => 'users.name',
                'estado' => 'retroactivos_eliminados.estado',
                'eliminado_por' => 'retroactivos_eliminados.eliminado_por',
            ];
            if (array_key_exists($request->categoria, $columnas)) {
                $q->where($columnas[$request->categoria], 'LIKE', '%'.$request->search.'%');
            }
        });

        $totalEliminado = (clone $query)->sum('retroactivos_eliminados.saldo_neto');

        $eliminadosRaw = $query->orderBy('retroactivos_eliminados.updated_at', 'desc')->get();

        $grupos = $eliminadosRaw->groupBy('name')->map(function ($grupo, $nombre) {
            return [
                'nombre' => $nombre,
                'eliminados' => $grupo,
                'total_neto' => $grupo->sum('saldo_neto'),
                'count' => $grupo->count(),
            ];
        })->values();

        return view('retroactivoEliminados', compact('grupos', 'totalEliminado', 'eliminadosRaw'));
    }

    public function restaurar($id)
    {
        $user = Auth::user();
        if (! $user || $user->role !== 'administrador') {
            return redirect('/inicio-de-sesion');
        }

        $eliminado = RetroactivoEliminado::find($id);

        if (! $eliminado) {
            return redirect()->back()->withErrors(['message' => 'Registro no encontrado.']);
        }

        try {
            DB::beginTransaction();

            Cuentas::create([
                'id_contract' => $eliminado->id_contract,
                'xml_file_id' => $eliminado->xml_file_id,
                'uuid' => $eliminado->uuid,
                'mes_pago' => $eliminado->mes_pago,
                'es_retroactivo' => $eliminado->es_retroactivo,
                'estado' => $eliminado->estado,
                'saldo_neto' => $eliminado->saldo_neto,
                'monto_pagado' => $eliminado->monto_pagado,
                'saldo_pendiente' => $eliminado->saldo_pendiente,
                'isr' => $eliminado->isr,
                'tasaCuota' => $eliminado->tasaCuota,
                'mesesdepago' => $eliminado->mesesdepago,
                'mesespagados' => $eliminado->mesespagados,
                'mesespendientes' => $eliminado->mesespendientes,
            ]);

            $eliminado->delete();

            DB::commit();

            return redirect()->route('retroactivo.eliminados')->with('success', 'Retroactivo restaurado correctamente.');
        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->back()->withErrors(['message' => 'Error al restaurar el retroactivo.']);
        }
    }

    public function destroyPermanente($id)
    {
        $user = Auth::user();
        if (! $user || $user->role !== 'administrador') {
            return redirect('/inicio-de-sesion');
        }

        $eliminado = RetroactivoEliminado::find($id);

        if (! $eliminado) {
            return redirect()->back()->withErrors(['message' => 'Registro no encontrado.']);
        }

        try {
            $eliminado->delete();

            return redirect()->route('retroactivo.eliminados')->with('success', 'Eliminación permanente realizada.');
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['message' => 'Error al eliminar permanentemente.']);
        }
    }
}
