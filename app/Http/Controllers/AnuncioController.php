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

        try {
            $path = null;
            if ($request->hasFile('adjunto')) {
                $path = $request->file('adjunto')->store('anuncios', 'public');
                if ($path === false) {
                    return redirect()->back()->with('error', 'No se pudo guardar el archivo. Verifique los permisos del servidor.')->withInput();
                }
            }

            Anuncio::create([
                'titulo' => $request->titulo,
                'descripcion' => $request->descripcion,
                'adjunto_ruta' => $path,
                'estado' => 'activo',
                'prioridad' => $request->prioridad,
            ]);
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error al crear el anuncio: ' . $e->getMessage())->withInput();
        }

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

        try {
            $data = [
                'titulo' => $request->titulo,
                'descripcion' => $request->descripcion,
                'prioridad' => $request->prioridad,
                'estado' => $request->estado,
            ];

            if ($request->hasFile('adjunto')) {
                if ($anuncio->adjunto_ruta) Storage::disk('public')->delete($anuncio->adjunto_ruta);
                $path = $request->file('adjunto')->store('anuncios', 'public');
                if ($path === false) {
                    return redirect()->back()->with('error', 'No se pudo guardar el archivo. Verifique los permisos del servidor.')->withInput();
                }
                $data['adjunto_ruta'] = $path;
            }

            $anuncio->update($data);
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error al actualizar el anuncio: ' . $e->getMessage())->withInput();
        }

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
