<?php

namespace App\Http\Controllers;

use App\Models\RazonSocial;
use Illuminate\Http\Request;

class RazonSocialController extends Controller
{
    public function index()
    {
        $razonesSociales = RazonSocial::with('proyectos')->orderBy('created_at', 'desc')->get();

        return view('admin.razones-sociales.index', compact('razonesSociales'));
    }

    public function create()
    {
        return view('admin.razones-sociales.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre_razon_social' => 'required|string|max:255',
            'rfc' => 'required|string|max:13|unique:razones_sociales,rfc',
            'telefono' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'direccion' => 'nullable|string',
        ]);

        RazonSocial::create($request->all());

        return redirect()->route('razones-sociales.index')->with('success', 'Razón social creada exitosamente.');
    }

    public function edit($id)
    {
        $razonSocial = RazonSocial::findOrFail($id);

        return view('admin.razones-sociales.edit', compact('razonSocial'));
    }

    public function update(Request $request, $id)
    {
        $razonSocial = RazonSocial::findOrFail($id);

        $request->validate([
            'nombre_razon_social' => 'required|string|max:255',
            'rfc' => 'required|string|max:13|unique:razones_sociales,rfc,'.$id.',id_razon_social',
            'telefono' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'direccion' => 'nullable|string',
        ]);

        $razonSocial->update($request->all());

        return redirect()->route('razones-sociales.index')->with('success', 'Razón social actualizada.');
    }

    public function destroy($id)
    {
        $razonSocial = RazonSocial::findOrFail($id);
        $razonSocial->delete();

        return redirect()->route('razones-sociales.index')->with('success', 'Razón social eliminada.');
    }
}
