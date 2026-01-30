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
            'name' => 'required|string',
            'password' => 'required|string',
            'email' => 'required|email',
        ]);

        $user = User::where('name', $request->name)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Usuario o contraseña incorrectos'
            ]);
        }

        if ($user->email !== $request->email) {
            return response()->json([
                'success' => false,
                'message' => 'Correo electrónico incorrecto'
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
