<?php

namespace App\Http\Controllers;

use App\Models\Proyecto;
use Illuminate\Http\Request;

class ProyectoController extends Controller
{
    public function index()
    {
        $proyectos = Proyecto::all();
        return view('admin.proyectos.index', compact('proyectos'));
    }

    public function create()
    {
        return view('admin.proyectos.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre_proyecto' => 'required|string|max:255',
        ]);

        Proyecto::create($request->all());

        return redirect()->route('proyectos.index')->with('success', 'Proyecto creado exitosamente.');
    }

    public function edit($id)
    {
        $proyecto = Proyecto::findOrFail($id);
        return view('admin.proyectos.edit', compact('proyecto'));
    }

    public function update(Request $request, $id)
    {
        $proyecto = Proyecto::findOrFail($id);

        $request->validate([
            'nombre_proyecto' => 'required|string|max:255',
        ]);

        $proyecto->update($request->only(['nombre_proyecto']));

        return redirect()->route('proyectos.index')->with('success', 'Proyecto actualizado.');
    }

    public function destroy($id)
    {
        $proyecto = Proyecto::findOrFail($id);
        $proyecto->delete();
        return redirect()->route('proyectos.index')->with('success', 'Proyecto eliminado.');
    }
}