<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Hash;

class GenerateController extends Controller
{
    public function index()
    {
        $user = Session::get('user');

        if (!$user) {
            return redirect('/inicio-de-sesion');
        }

        
        return view('registroUsuarios');
    }

public function datos(Request $request)
{
    $user = Session::get('user');
    if (!$user) {
        return redirect('/inicio-de-sesion');
    }

    $phone = '52' . $request->input('phone');

    $request->validate([
        'name'     => 'required|string|max:255',
        'email'    => 'required|email|unique:users,email',
        'phone'    => 'nullable|string|max:12',
    ]);

    // 🔹 Generar contraseña
    $passwordPlain = $this->generarContrasenia();

    DB::table('users')->insert([
        'name'              => $request->name,
        'email'             => $request->email,
        'password'          => Hash::make($passwordPlain), // guardamos encriptada
        'rol'               => 'usuario',
        'phone'             => $phone,
        'proyect'           => json_encode($request->proyect), // como es multiple
        'regimenFiscal'     => $request->regimenFiscal,
        'created_at'        => now(),
        'updated_at'        => now(),
        'email_verified_at' => now(),
    ]);

    // 🔹 Guardamos la contraseña en sesión para mostrarla en la vista
    return back()->with([
        'success' => '✅ Usuario registrado correctamente.',
        'generated_password' => $passwordPlain
    ]);
}

    private function generarContrasenia($length = 8)
    {
        return substr(str_shuffle('abcdefghijkmnopqrstuvwxyzABCDEFGHJKLMNPQRSTUVWXYZ23456789!@$%&*'), 0, $length);
    }
}
