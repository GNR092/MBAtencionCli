<?php

namespace App\Http\Controllers;

use App\Models\Cuentas;
use App\Models\PagoEfectivo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class EfectivoController extends Controller
{
    public function index(Request $request)
    {
        $query = PagoEfectivo::with(['user', 'contract'])
            ->join('users', 'pagos_efectivo.id_user', '=', 'users.id')
            ->join('contract', 'pagos_efectivo.id_contract', '=', 'contract.id')
            ->select('pagos_efectivo.*', 'users.name as nombre_usuario');

        if ($request->filled('mes')) {
            $query->where('pagos_efectivo.mes_pago', $request->mes);
        }

        if ($request->filled('id_user')) {
            $query->where('pagos_efectivo.id_user', $request->id_user);
        }

        $pagos = $query->orderByDesc('pagos_efectivo.fecha_pago')->get();
        $total = $pagos->sum('monto');

        // Cuentas pendientes/parciales para el formulario
        $cuentasPendientes = DB::table('cuentasporpagar')
            ->join('contract', 'cuentasporpagar.id_contract', '=', 'contract.id')
            ->join('users', 'contract.user_id', '=', 'users.id')
            ->whereIn('cuentasporpagar.estado', ['pendiente', 'parcial'])
            ->select(
                'cuentasporpagar.id_cuentas_por_pagar',
                'cuentasporpagar.id_contract',
                'cuentasporpagar.mes_pago',
                'cuentasporpagar.saldo_pendiente',
                'cuentasporpagar.saldo_neto',
                'users.name as nombre_usuario',
                'users.id as id_user'
            )
            ->orderBy('users.name')
            ->orderBy('cuentasporpagar.mes_pago')
            ->get();

        $usuarios = DB::table('users')
            ->whereIn('id', PagoEfectivo::pluck('id_user')->unique())
            ->select('id', 'name')
            ->orderBy('name')
            ->get();

        return view('efectivo', compact('pagos', 'total', 'cuentasPendientes', 'usuarios'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'id_cuentas_por_pagar' => 'required|integer|exists:cuentasporpagar,id_cuentas_por_pagar',
            'monto'                => 'required|numeric|min:0.01',
            'fecha_pago'           => 'required|date',
            'concepto'             => 'nullable|string|max:500',
        ]);

        $cuenta = Cuentas::where('id_cuentas_por_pagar', $request->id_cuentas_por_pagar)->firstOrFail();

        // Obtener id_user desde el contrato
        $contrato = DB::table('contract')
            ->join('users', 'contract.user_id', '=', 'users.id')
            ->where('contract.id', $cuenta->id_contract)
            ->select('contract.id as contract_id', 'users.id as user_id')
            ->first();

        PagoEfectivo::create([
            'id_contract'          => $cuenta->id_contract,
            'id_cuentas_por_pagar' => $cuenta->id_cuentas_por_pagar,
            'id_user'              => $contrato->user_id,
            'monto'                => $request->monto,
            'fecha_pago'           => $request->fecha_pago,
            'mes_pago'             => $cuenta->mes_pago,
            'concepto'             => $request->concepto,
        ]);

        // Actualizar cuentasporpagar
        $nuevoPagado   = floatval($cuenta->monto_pagado) + floatval($request->monto);
        $saldoNeto     = floatval($cuenta->saldo_neto);
        $nuevoSaldo    = round(max(0, $saldoNeto - $nuevoPagado), 2);

        if ($nuevoPagado >= $saldoNeto) {
            $estado     = 'pagado';
            $nuevoSaldo = 0;
        } elseif ($nuevoPagado > 0) {
            $estado = 'parcial';
        } else {
            $estado = 'pendiente';
        }

        DB::table('cuentasporpagar')
            ->where('id_cuentas_por_pagar', $cuenta->id_cuentas_por_pagar)
            ->update([
                'monto_pagado'    => round($nuevoPagado, 2),
                'saldo_pendiente' => $nuevoSaldo,
                'estado'          => $estado,
                'updated_at'      => now(),
            ]);

        return redirect()->back()->with('success', 'Pago en efectivo registrado correctamente.');
    }

    public function userIndex()
    {
        $pagos = PagoEfectivo::where('id_user', Auth::id())
            ->orderByDesc('fecha_pago')
            ->paginate(10);

        $total = PagoEfectivo::where('id_user', Auth::id())->sum('monto');

        return view('User.efectivoUser', compact('pagos', 'total'));
    }
}
