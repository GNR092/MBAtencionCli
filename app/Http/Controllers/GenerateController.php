<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Hash;
use App\Models\RegimenFiscal;
use App\Models\Proyecto;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Models\UserProyecto;
use App\Models\UserDepto;

class GenerateController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        if (!$user) {
            return redirect('/inicio-de-sesion');
        }


        $regimenesFiscales = RegimenFiscal::all();
        $proyectos = Proyecto::all();

        return view('registroUsuarios', compact('regimenesFiscales', 'proyectos'));
    }

    public function datos(Request $request)
    {
        // Validaciones para evitar errores silenciosos
        $request->validate([
            'name' => 'required',
            'email' => 'required|email|unique:users,email',
            'phone' => 'required',
            'proyect' => 'required|array', // Debe haber al menos un proyecto
        ]);

        $user = Auth::user();
        if (!$user) { return redirect('/inicio-de-sesion'); }

        $phone = '52' . $request->input('phone');
        $passwordPlain = $this->generarContrasenia();

        try {
            DB::transaction(function () use ($request, $phone, $passwordPlain) {

                // --- CREAR USUARIO ---
                $newUser = User::create([
                    'name'              => mb_convert_encoding($request->name, 'UTF-8', 'UTF-8'),
                    'email'             => $request->email,
                    'password'          => Hash::make($passwordPlain),
                    'role'              => 'usuario',
                    'phone'             => $phone,
                    'id_regimen'        => $request->regimenFiscal,
                    'rfc'               => $request->rfc ?? null,
                    'curp'              => $request->curp ?? null,
                    'email_verified_at' => now(),
                    'metodo_pago'       => $request->metodo_pago ?? null,
                ]);

                $proyectosIds = $request->input('proyect', []);
                $detallesDeptos = $request->input('project_details', []);

                // --- RECORRER PROYECTOS ---
                foreach ($proyectosIds as $projectId) {

                    
                    $pivot = new UserProyecto();
                    $pivot->id_user = $newUser->id;
                    $pivot->id_proyecto = $projectId;
                    $pivot->created_at = now();
                    $pivot->updated_at = now();
                    $pivot->save();

                    $pivotId = $pivot->getKey();

                    // Si getKey retorna null
                    if (!$pivotId) {
                        $pivotId = $pivot->id_user_p ?? $pivot->id;
                    }

                    // Verificamos si existen detalles para este proyecto en específico
                    if (isset($detallesDeptos[$projectId]) && is_array($detallesDeptos[$projectId])) {

                        foreach ($detallesDeptos[$projectId] as $index => $deptoData) {

                            // Aseguramos que vengan los datos mínimos
                            if(empty($deptoData['nombre_depto'])) continue;

                            UserDepto::create([
                                'id_user_p' => $pivotId, // Usamos la ID recuperada de forma segura
                                'nombre'    => $deptoData['nombre_depto'],
                                'predial'   => $deptoData['cuenta_numero'] ?? 'N/A',
                                'importe'   => $deptoData['importe'] ?? 0,
                            ]);
                        }
                    }
                }
            });

            return back()->with('success', '✅ Usuario registrado correctamente.')
                         ->with('generated_password', $passwordPlain);

        } catch (\Throwable $e) {
            // Si falla, revertirá todo y mostrará esto:
            Log::error("Error Fatal Registro: " . $e->getMessage());
            return back()->with('error', 'Error técnico: ' . $e->getMessage());

            // Depuracion
            // dd($e->getMessage(), $e->getLine(), $e->getFile());
        }
    }

    private function generarContrasenia($length = 8)
    {
        return substr(str_shuffle('abcdefghijkmnopqrstuvwxyzABCDEFGHJKLMNPQRSTUVWXYZ23456789!@$%&*'), 0, $length);
    }
}
