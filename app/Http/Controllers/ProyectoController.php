<?php

namespace App\Http\Controllers;

use App\Models\Proyecto;
use App\Models\RazonSocial;
use Illuminate\Http\Request;

class ProyectoController extends Controller
{
    public function index()
    {
        $proyectos = Proyecto::with('razonSocial')->get();

        return view('admin.proyectos.index', compact('proyectos'));
    }

    public function create()
    {
        $razonesSociales = RazonSocial::all();

        return view('admin.proyectos.create', compact('razonesSociales'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre_proyecto' => 'required|string|max:255',
            'id_razon_social' => 'nullable|exists:razones_sociales,id_razon_social',
        ]);

        Proyecto::create($request->all());

        return redirect()->route('proyectos.index')->with('success', 'Proyecto creado exitosamente.');
    }

    public function edit($id)
    {
        $proyecto = Proyecto::findOrFail($id);
        $razonesSociales = RazonSocial::all();

        return view('admin.proyectos.edit', compact('proyecto', 'razonesSociales'));
    }

    public function update(Request $request, $id)
    {
        $proyecto = Proyecto::findOrFail($id);

        $request->validate([
            'nombre_proyecto' => 'required|string|max:255',
            'id_razon_social' => 'nullable|exists:razones_sociales,id_razon_social',
        ]);

        $proyecto->update($request->all());

        return redirect()->route('proyectos.index')->with('success', 'Proyecto actualizado.');
    }

    public function destroy($id)
    {
        $proyecto = Proyecto::findOrFail($id);
        $proyecto->delete();

        return redirect()->route('proyectos.index')->with('success', 'Proyecto eliminado.');
    }
}
