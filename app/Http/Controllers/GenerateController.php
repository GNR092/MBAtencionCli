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
        $user = Auth::user();
        if (!$user) {
            return redirect('/inicio-de-sesion');
        }

        $phone = '52' . $request->input('phone');
        $passwordPlain = '123456';

        try {
            DB::transaction(function () use ($request, $phone, $passwordPlain) {
                
                $newUser = User::create([
                    'name'              => mb_convert_encoding($request->name, 'UTF-8', 'UTF-8'),
                    'email'             => $request->email,
                    'password'          => Hash::make($passwordPlain),
                    'role'              => 'usuario',
                    'phone'             => $phone,
                    'id_regimen'        => $request->regimenFiscal,
                    'email_verified_at' => now(),
                ]);

                $proyectosIds = $request->input('proyect', []);
                $detallesDeptos = $request->input('project_details', []);

                foreach ($proyectosIds as $index => $projectId) {

                    
                    $pivot = UserProyecto::create([
                        'id_user'     => $newUser->id,
                        'id_proyecto' => $projectId
                    ]);

                    
                    $data = $detallesDeptos[$projectId] ?? null;

                    if ($data) {
                        
                        UserDepto::create([
                            'id_user_p' => $pivot->id_user_p, 
                            'nombre'    => $data['nombre_depto'] ?? 'N/A',
                            'predial'   => $data['cuenta_numero'] ?? 'N/A', 
                            'importe'   => $data['importe'] ?? 0,
                        ]);
                    }
                }
            });

            return back()->with('success', '✅ Usuario y proyectos registrados.');
        } catch (\Throwable $e) {
            Log::error("Error en registro: " . $e->getMessage());
            return back()->with('error', 'Error: ' . $e->getMessage());
        }
    }

    private function generarContrasenia($length = 8)
    {
        return substr(str_shuffle('abcdefghijkmnopqrstuvwxyzABCDEFGHJKLMNPQRSTUVWXYZ23456789!@$%&*'), 0, $length);
    }
}