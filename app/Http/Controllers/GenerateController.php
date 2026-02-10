<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Hash;
use App\Models\RegimenFiscal; // Agregado
use App\Models\Proyecto; // Agregado
use Illuminate\Support\Facades\Auth; // Importar la fachada Auth

class GenerateController extends Controller
{
    public function index()
    {
        $user = Auth::user(); // Usar Auth::user()

        if (!$user) {
            return redirect('/inicio-de-sesion');
        }


        $regimenesFiscales = RegimenFiscal::all(); // Obtener todos los regímenes fiscales
        $proyectos = Proyecto::all(); // Obtener todos los proyectos

        return view('registroUsuarios', compact('regimenesFiscales', 'proyectos'));
    }

    // reparar est edato
    public function datos(Request $request)
    {
        $user = Auth::user(); // Usar Auth::user()
        if (!$user) {
            return redirect('/inicio-de-sesion');
        }

        // $request->validate([
        //     'name'     => 'required|string|max:255',
        //     'email'    => 'required|email|unique:users,email',
        //     'phone'    => 'required|string|max:10|min:10', // Changed to required and 10 digits
        //     'project_details' => 'required|array',
        //     'project_details.*.nombre_depto' => 'required|string|max:255',
        //     'project_details.*.cuenta_predial' => 'nullable|boolean',
        //     'project_details.*.cuenta_numero' => 'nullable|string|max:255',
        //     'project_details.*.importe' => 'required|numeric|min:0',
        //     'regimenFiscal'     => 'required|string|max:255', // Add validation for regimenFiscal
        // ]);

        $phone = '52' . $request->input('phone');

        // 🔹 Generar contraseña
        $passwordPlain = $this->generarContrasenia();

        $proyectData = [];
        foreach ($request->input('project_details') as $projectId => $details) {
            $proyectData[] = [
                'id_proyecto' => (string) $projectId,
                'nombre_depto' => $details['nombre_depto'],
                'cuenta_predial' => isset($details['cuenta_predial']), // Checkbox sends value only if checked
                'cuenta_numero' => $details['cuenta_numero'] ?? null,
                'importe' => (float) $details['importe'],
            ];
        }

        DB::table('users')->insert([
            'name'              => $request->name,
            'email'             => $request->email,
            'password'          => Hash::make($passwordPlain), // guardamos encriptada
            'role'              => 'usuario', // Cambiado de 'rol' a 'role'
            'phone'             => $phone,
            'proyect'           => json_encode($proyectData), // Guardar como JSON de objetos
            'id_regimen'        => $request->regimenFiscal, // Cambiado de 'regimenFiscal' a 'id_regimen'
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