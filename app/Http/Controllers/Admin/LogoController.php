<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Logo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class LogoController extends Controller
{
    public function index()
    {
        $logos = Logo::orderBy('created_at', 'desc')->get();

        return view('logos', compact('logos'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'logo' => [
                'required',
                'image',
                'mimes:png,svg',
                'max:2048',
            ],
            'url_redireccion' => 'nullable|url',
        ], [

            'logo.mimes' => 'El logo debe ser un archivo de tipo: png, svg.',
            'logo.max' => 'El tamaño del logo no debe ser mayor a 2MB.',
        ]);

        $path = $request->file('logo')->store('logos_carrusel', 'public');

        \App\Models\Logo::create([
            'nombre' => $request->nombre,
            'imagen_ruta' => $path,
            'url_redireccion' => $request->url_redireccion,
            'activo' => true,
        ]);

        return back()->with('success', 'Logo cargado correctamente.');
    }

    public function toggle(Logo $logo)
    {
        $logo->activo = ! $logo->activo;
        $logo->save();

        return back();
    }

    public function destroy(Logo $logo)
    {
        Storage::disk('public')->delete($logo->imagen_ruta);
        $logo->delete();

        return back()->with('success', 'Logo eliminado.');
    }
}
