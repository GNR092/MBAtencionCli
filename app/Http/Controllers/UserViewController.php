<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use App\Models\User;

class UserViewController extends Controller
{
    public function index()
    {
        // Get user ID from session, then fetch user from DB to ensure freshest data
        $sessionUser = Session::get('user');
        $userId = $sessionUser ? $sessionUser->id : null;
        $user = $userId ? User::find($userId) : null;

        // Redirect to login if user not found (or handle error appropriately)
        if (!$user) {
            return redirect()->route('login.form')->withErrors('Usuario no encontrado o sesión caducada.');
        }

        // Dummy data for now
        $ticketsCount = 0;
        $equiposAsignados = 0;
        $entregasCount = 0;
        $notificaciones = 0;
        $ultimasNotificaciones = [];
        $ultimosTickets = [];
        $equipos = [];
        $usuarios = [];
        $misResguardos = [];

        // Fetch administrators for the chat directory
        $administradores = User::where('rol', 'administrador')->get();

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
            'administradores'
        ));
    }

    public function actualizarFoto(Request $request)
    {
        $request->validate([
            'foto' => 'required|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        $user = User::find(Session::get('user')->id);

        if ($request->hasFile('foto')) {
            if ($user->foto) { // Delete old photo if exists
                Storage::disk('public')->delete($user->foto);
            }
            $path = $request->file('foto')->store('fotos_perfil', 'public');
            $user->foto = $path;
            $user->save();

            // Update session user object
            Session::put('user', $user);
        }

        return back()->with('success', 'Foto de perfil actualizada correctamente.');
    }
}
