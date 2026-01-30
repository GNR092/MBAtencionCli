<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\IncrementoImporte;
use App\Models\Contract;

class IncrementoImporteController extends Controller
{
    public function index(Request $request)
    {
        // Consulta base
        $query = IncrementoImporte::with('contract');

        // FILTRO POR MES
        if ($request->filled('month')) {
            $year  = substr($request->month, 0, 4);
            $month = substr($request->month, 5, 2);

            $query->whereYear('created_at', $year)
                ->whereMonth('created_at', $month);
        }

        // Obtener resultados paginados
        $incrementos = $query->paginate(10)->appends($request->query());

        return view('incrementos.index', compact('incrementos'));
    }


    public function create()
    {
        $contract = Contract::all();
        return view('incrementos.create', compact('contract'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'id_contract' => 'required|exists:contract,id',
            'importe_base' => 'required|numeric|min:0',
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'nullable|date|after_or_equal:fecha_inicio',
        ]);

        IncrementoImporte::create($request->all());

        return redirect()->route('incrementos.index')
                         ->with('success', 'Incremento registrado correctamente.');
    }

    public function destroy($id)
    {
        IncrementoImporte::findOrFail($id)->delete();
        return redirect()->route('incrementos.index')
                         ->with('success', 'Incremento eliminado.');
    }
}
