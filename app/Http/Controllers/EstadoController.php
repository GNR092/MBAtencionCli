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
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\User;

class EstadoController extends Controller
{

public function Index(Request $request)
{
    // Verificar sesión de usuario
    $user = Session::get('user');
    if (!$user) {
        return redirect('/inicio-de-sesion');
    }

    $yearActual = date('Y');

    // Query base
    $query = Cuentas::with('contract')
        ->leftJoin('xml_files', 'cuentasporpagar.xml_file_id', '=', 'xml_files.id')
        ->leftJoin('contract', 'cuentasporpagar.id_contract', '=', 'contract.id')
        ->leftJoin('users', 'contract.user_id', '=', 'users.id')
        ->leftJoin('impuesto', 'xml_files.id', '=', 'impuesto.xml_file_id')
        ->select(
            'cuentasporpagar.*',
            'users.name as name',
            'xml_files.proyectos as proyectos',
            'contract.importe_bruto_renta as importeBase',
        )
        ->where('users.id', $user->id)
        ->where('cuentasporpagar.estado', '=', 'pagado')


        ->where(function ($q) use ($yearActual) {
            // Extraer el valor "2024-10"
            $q->whereRaw("LEFT(JSON_UNQUOTE(JSON_EXTRACT(cuentasporpagar.mesesdepago, '$.mes')), 4) < ?", [
                $yearActual
            ]) // Años anteriores

            ->orWhereRaw("LEFT(JSON_UNQUOTE(JSON_EXTRACT(cuentasporpagar.mesesdepago, '$.mes')), 4) = ?", [
                $yearActual
            ]); // Meses del año actual
        });

                    // =============================
            //   FILTRO POR MES DEL LAYOUT
            // =============================
            if ($request->filled('month')) {

                $selectedMonth = $request->month; // Ej: 2025-01

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
                case 'id':
                    $query->where('cuentasporpagar.id_cuentas_por_pagar', 'LIKE', "%{$search}%");
                    break;
                case 'estado':
                    $query->where('cuentasporpagar.estado', 'LIKE', "%{$search}%");
                    break;
            }
        }


    // Paginación
    $cuentas = $query->paginate(6)->appends($request->query());

    if ($request->expectsJson()) {
        $html = view('estadosDeCuenta', [
            'cuentas' => $cuentas,
            'user' => $user,
            'usuario' => $user
        ])->render();
        return response()->json(['html' => $html]);
    }

    return view('estadosDeCuenta', [
    'cuentas' => $cuentas,
    'user' => $user,   // 👈 NECESARIO PARA EL BOTÓN
    'usuario' => $user
]);

}

public function limpiar(){
        session()->forget(['search', 'categoria']);
        return redirect()->route('estadosDeCuenta');
}

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
            }
        }
}

public function graficaAnualPagados($year){
        $user = Session::get('user');
    if (!$user) return response()->json([]);

    //  Traemos SOLO las cuentas cuyos contratos pertenecen al usuario en sesión
    $cuentas = DB::table('cuentasporpagar')
        ->join('contract', 'cuentasporpagar.id_contract', '=', 'contract.id')
        ->where('contract.user_id', $user->id)
        ->select('cuentasporpagar.*')
        ->get();

    $resultado = [];

    for ($m = 1; $m <= 12; $m++) {

        $Pagados = 0;

        foreach ($cuentas as $c) {
            $mesJson = json_decode($c->mesesdepago);

            if (!$mesJson || !isset($mesJson->mes)) continue;

            // extraemos año y mes
            [$y, $month] = explode('-', $mesJson->mes);

            if ((int)$y === (int)$year && (int)$month === $m) {
                $Pagados += floatval($c->monto_pagado);
            }
        }

        $resultado[] = [
            'mes' => $m,
            'pagados' => $Pagados,
        ];
    }

    return response()->json($resultado);
}


public function descargarPdf(Request $request)
{
    $id = $request->id_usuario;

    // Validar que exista el usuario
    $usuario = User::findOrFail($id);

    // Filtros
    $desde = $request->desde;
    $hasta = $request->hasta;

    // Query base
    $query = Cuentas::with('contract')
        ->leftJoin('xml_files', 'cuentasporpagar.xml_file_id', '=', 'xml_files.id')
        ->leftJoin('contract', 'cuentasporpagar.id_contract', '=', 'contract.id')
        ->select(
            'cuentasporpagar.*',
            'xml_files.proyectos as proyecto',
            'contract.importe_bruto_renta as importeBase'
        )
        ->where('contract.user_id', $id);

    // Aplicar filtro de fechas
    if ($desde) {
        $query->whereDate('cuentasporpagar.created_at', '>=', $desde);
    }

    if ($hasta) {
        $query->whereDate('cuentasporpagar.created_at', '<=', $hasta);
    }

    $cuentas = $query->orderBy('cuentasporpagar.created_at', 'asc')->get();

    // Generar PDF
    $pdf = Pdf::loadView('pdf.estadodecuenta', [
        'usuario' => $usuario,
        'cuentas' => $cuentas
    ]);

    return $pdf->download('EstadoDeCuenta-' . $usuario->name . '.pdf');
}




}