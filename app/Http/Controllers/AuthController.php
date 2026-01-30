<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use App\Models\User;

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

        // Guardar en sesión
        Session::put('user', $user);

        return response()->json([
            'success' => true,
            'message' => 'Bienvenido '.$user->name,
            'rol' => $user->rol
        ]);
    }

    public function logout()
    {
        Session::flush();
        return redirect('/inicio-de-sesion');
    }
}
