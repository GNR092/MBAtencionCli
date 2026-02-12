<?php

namespace App\Http\Controllers;

use App\Models\Anuncio;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AnuncioController extends Controller
{
    public function index()
    {
        $anuncios = Anuncio::orderBy('prioridad', 'desc')->get();
        
        return view('anuncios', compact('anuncios'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'titulo' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'adjunto' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'prioridad' => 'required|in:baja,media,alta',
        ]);

        $path = $request->hasFile('adjunto') ? $request->file('adjunto')->store('anuncios', 'public') : null;

        Anuncio::create([
            'titulo' => $request->titulo,
            'descripcion' => $request->descripcion,
            'adjunto_ruta' => $path,
            'estado' => 'activo',
            'prioridad' => $request->prioridad,
        ]);

        return redirect()->route('admin.anuncios.index')->with('success', 'Anuncio creado');
    }

    public function toggleStatus($id)
    {
        $anuncio = Anuncio::findOrFail($id);
        $anuncio->estado = ($anuncio->estado == 'activo') ? 'inactivo' : 'activo';
        $anuncio->save();
        return redirect()->back();
    }

    public function update(Request $request, $id)
    {
        $anuncio = Anuncio::findOrFail($id);

        $request->validate([
            'titulo' => 'required|string|max:255',
            'prioridad' => 'required|in:baja,media,alta',
            'estado' => 'required|in:activo,inactivo',
        ]);

        if ($request->hasFile('adjunto')) {
            if ($anuncio->adjunto_ruta) Storage::disk('public')->delete($anuncio->adjunto_ruta);
            $anuncio->adjunto_ruta = $request->file('adjunto')->store('anuncios', 'public');
        }

        $anuncio->update([
            'titulo' => $request->titulo,
            'descripcion' => $request->descripcion,
            'prioridad' => $request->prioridad,
            'estado' => $request->estado,
        ]);

        return redirect()->route('admin.anuncios.index')->with('success', 'Anuncio actualizado');
    }

    public function destroy($id)
    {
        $anuncio = Anuncio::findOrFail($id);
        if ($anuncio->adjunto_ruta) Storage::disk('public')->delete($anuncio->adjunto_ruta);
        $anuncio->delete();
        return redirect()->route('admin.anuncios.index')->with('success', 'Anuncio eliminado');
    }
}
