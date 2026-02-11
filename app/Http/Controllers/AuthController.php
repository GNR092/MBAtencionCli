<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use App\Models\User;
use Illuminate\Support\Facades\Auth; // Importar la fachada Auth

class AuthController extends Controller
{
    public function showLoginForm()
    {
        return view('inicioDeSesion');
    }

    public function login(Request $request)
    {
        $request->validate([
            'password' => 'required|string',
            'email' => 'required|email',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Correo electrónico o contraseña incorrectos'
            ]);
        }

        Session::put('user', $user);
        // Iniciar sesión del usuario usando el sistema de autenticación de Laravel
        //Auth::login($user);

        return response()->json([
            'success' => true,
            'message' => 'Bienvenido '.$user->name,
            'role' => $user->role
        ]);
    }

    public function logout(Request $request) // Añadir Request para invalidate y regenerate
    {
        Auth::logout(); // Cerrar sesión del usuario

        $request->session()->invalidate(); // Invalidar la sesión actual
        $request->session()->regenerateToken(); // Regenerar el token CSRF

        return redirect('/inicio-de-sesion');
    }
}
