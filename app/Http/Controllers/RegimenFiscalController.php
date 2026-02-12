<?php

namespace App\Http\Controllers;

use App\Models\RegimenFiscal;
use Illuminate\Http\Request;

class RegimenFiscalController extends Controller
{
    public function index()
    {
        $regimenes = RegimenFiscal::all();
        
        return view('admin.regimen_fiscal.index', compact('regimenes'));
    }

    public function create()
    {
        return view('admin.regimen_fiscal.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'id_regimen' => 'required|integer|unique:regimen_fiscals,id_regimen',
            'nombre_regimen' => 'required|string|max:255',
            'tasa_retencion' => 'required|numeric|min:0',
        ]);

        RegimenFiscal::create($request->all());

        return redirect()->route('regimen-fiscal.index')->with('success', 'Régimen creado.');
    }

    public function edit($id)
    {
        $regimen = RegimenFiscal::findOrFail($id);
        return view('admin.regimen_fiscal.edit', compact('regimen'));
    }

    public function update(Request $request, $id)
    {
        $regimen = RegimenFiscal::findOrFail($id);

        $request->validate([
            'nombre_regimen' => 'required|string|max:255',
            'tasa_retencion' => 'required|numeric|min:0',
        ]);

        $regimen->update($request->only(['nombre_regimen', 'tasa_retencion']));

        return redirect()->route('regimen-fiscal.index')->with('success', 'Régimen actualizado.');
    }

    public function destroy($id)
    {
        $regimen = RegimenFiscal::findOrFail($id);
        $regimen->delete();
        return redirect()->route('regimen-fiscal.index')->with('success', 'Régimen eliminado.');
    }
}
