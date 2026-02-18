<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class UserViewController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        
        if (!$user) {
            return redirect()->route('login.form')->withErrors('Usuario no encontrado o sesión caducada.');
        }

        
        
        
        $anuncios = \App\Models\Anuncio::where('estado', 'activo')
            ->orderByRaw("FIELD(prioridad, 'alta', 'media', 'baja')")
            ->orderBy('created_at', 'desc')
            ->get();

        
        $ticketsCount = 0;
        $equiposAsignados = 0;
        $entregasCount = 0;
        $notificaciones = 0;
        $ultimasNotificaciones = [];
        $ultimosTickets = [];
        $equipos = [];
        $usuarios = [];
        $misResguardos = [];

        
        $administradores = User::where('role', 'administrador')->get();

        return view('viewUser', compact(
            'user',
            'ticketsCount',
            'equiposAsignados',
            'entregasCount',
            'notificaciones',
            'ultimasNotificaciones',
            'ultimosTickets',
            'equipos',
            'usuarios',
            'misResguardos',
            'administradores',
            'anuncios' 
        ));
    }

    public function actualizarFoto(Request $request)
    {
        $request->validate([
            'foto' => 'required|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login.form')->withErrors('Sesión caducada.');
        }

        if ($request->hasFile('foto')) {
            if ($user->foto) { 
                Storage::disk('public')->delete($user->foto);
            }
            $path = $request->file('foto')->store('fotos_perfil', 'public');
            $user->foto = $path;
            $user->save();

            
            Session::put('user', $user);
        }

        return back()->with('success', 'Foto de perfil actualizada correctamente.');
    }
}
