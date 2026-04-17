<?php

namespace App\Http\Controllers;

use App\Models\Contract;
use App\Models\Proyecto;
use App\Models\RegimenFiscal;
use App\Models\User;
use App\Models\UserDepto;
use App\Models\UserProyecto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class GenerateController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        if (! $user) {
            return redirect('/login');
        }

        $regimenesFiscales = RegimenFiscal::all();
        $proyectos = Proyecto::with('razonSocial')->get();

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
            'project_payment_methods' => 'required|array',
            'project_payment_methods.*' => 'nullable|in:efectivo,transferencia',
            'project_details' => 'required|array',
            'project_details.*' => 'required|array|min:1',
            'project_details.*.*.nombre_depto' => 'required|string|max:255',
            'project_details.*.*.importe' => 'required|numeric|min:0.01',
            'project_details.*.*.tipo' => 'required|in:Campus,Condominios',
            'project_details.*.*.cuenta_numero' => 'nullable|string|max:255',
            'project_details.*.*.fecha_inicio_contrato' => 'required|date',
            'project_details.*.*.fecha_terminacion_contrato' => 'required|date',
            'project_details.*.*.contract_file' => 'required|file|mimes:pdf|max:2048',
        ]);

        $user = Auth::user();
        if (! $user) {
            return redirect('/login');
        }

        $phone = '52'.$request->input('phone');
        $passwordPlain = $this->generarContrasenia();
        $proyectosIds = $request->input('proyect', []);
        $projectPaymentMethods = $request->input('project_payment_methods', []);

        foreach ($proyectosIds as $projectId) {
            if (empty($projectPaymentMethods[$projectId])) {
                return back()
                    ->withErrors([
                        "project_payment_methods.$projectId" => 'Selecciona el metodo de pago para cada proyecto.',
                    ])
                    ->withInput();
            }
        }

        $metodoPagoGeneral = collect($projectPaymentMethods)
            ->first(fn ($metodo) => in_array($metodo, ['efectivo', 'transferencia'], true));

        try {
            DB::transaction(function () use ($request, $phone, $passwordPlain, $proyectosIds, $projectPaymentMethods, $metodoPagoGeneral) {

                // --- CREAR USUARIO ---
                $newUser = User::create([
                    'name' => mb_convert_encoding($request->name, 'UTF-8', 'UTF-8'),
                    'email' => $request->email,
                    'password' => Hash::make($passwordPlain),
                    'role' => 'usuario',
                    'phone' => $phone,
                    'id_regimen' => $request->regimenFiscal,
                    'rfc' => $request->rfc ?? null,
                    'curp' => $request->curp ?? null,
                    'email_verified_at' => now(),
                    'metodo_pago' => $metodoPagoGeneral,
                    'fecha_nacimiento' => $request->fecha_nacimiento ?: null,
                ]);

                $detallesDeptos = $request->input('project_details', []);

                // --- RECORRER PROYECTOS ---
                foreach ($proyectosIds as $projectId) {

                    $pivot = new UserProyecto;
                    $pivot->id_user = $newUser->id;
                    $pivot->id_proyecto = $projectId;
                    $pivot->metodo_pago = $projectPaymentMethods[$projectId] ?? null;
                    $pivot->created_at = now();
                    $pivot->updated_at = now();
                    $pivot->save();

                    $pivotId = $pivot->getKey();

                    // Si getKey retorna null
                    if (! $pivotId) {
                        $pivotId = $pivot->id_user_p ?? $pivot->id;
                    }

                    // Verificamos si existen detalles para este proyecto en específico
                    if (isset($detallesDeptos[$projectId]) && is_array($detallesDeptos[$projectId])) {

                        foreach ($detallesDeptos[$projectId] as $index => $deptoData) {

                            // Aseguramos que vengan los datos mínimos
                            if (empty($deptoData['nombre_depto'])) {
                                continue;
                            }

                            $depto = UserDepto::create([
                                'id_user_p' => $pivotId, // Usamos la ID recuperada de forma segura
                                'nombre' => $deptoData['nombre_depto'],
                                'tipo' => $deptoData['tipo'] ?? null,
                                'predial' => $deptoData['cuenta_numero'] ?? 'N/A',
                                'importe' => $deptoData['importe'] ?? 0,
                            ]);

                            $contractFile = $request->file("project_details.$projectId.$index.contract_file");
                            if (! $contractFile) {
                                throw new \RuntimeException('Falta contrato PDF para un departamento.');
                            }

                            if ($deptoData['fecha_terminacion_contrato'] < $deptoData['fecha_inicio_contrato']) {
                                throw new \RuntimeException('La fecha de terminación del contrato no puede ser menor a la fecha de inicio.');
                            }

                            $contractPath = $this->storeContractFile($contractFile, (int) $newUser->id);

                            Contract::create([
                                'user_id' => $newUser->id,
                                'id_user_p' => $pivotId,
                                'id_user_depto' => $depto->id_user_depto,
                                'folio' => $this->generarFolio(),
                                'fecha' => now()->toDateString(),
                                'estado' => 'activo',
                                'nombre' => $contractFile->getClientOriginalName(),
                                'tipo' => $contractFile->getMimeType(),
                                'contenido' => $contractPath,
                                'importe_bruto_renta' => $deptoData['importe'],
                                'fecha_inicio' => $deptoData['fecha_inicio_contrato'],
                                'fecha_terminacion' => $deptoData['fecha_terminacion_contrato'],
                            ]);
                        }
                    }
                }
            });

            return back()->with('success', '✅ Usuario registrado correctamente.')
                ->with('generated_password', $passwordPlain);

        } catch (\Throwable $e) {
            // Si falla, revertirá todo y mostrará esto:
            Log::error('Error Fatal Registro: '.$e->getMessage());

            return back()->with('error', 'Error técnico: '.$e->getMessage());

            // Depuracion
            // dd($e->getMessage(), $e->getLine(), $e->getFile());
        }
    }

    private function generarContrasenia($length = 8)
    {
        return substr(str_shuffle('abcdefghijkmnopqrstuvwxyzABCDEFGHJKLMNPQRSTUVWXYZ23456789!@$%&*'), 0, $length);
    }

    private function storeContractFile(\Illuminate\Http\UploadedFile $file, int $userId): string
    {
        $safeName = now()->format('YmdHis').'_'.preg_replace('/\s+/', '_', $file->getClientOriginalName());

        return Storage::putFileAs("contracts/{$userId}", $file, $safeName);
    }

    private function generarFolio(): string
    {
        $fecha = date('Ymd');
        $ultimoFolio = DB::table('contract')
            ->where('folio', 'like', "CTR-{$fecha}-%")
            ->orderBy('folio', 'desc')
            ->first();

        if ($ultimoFolio) {
            $numeroActual = (int) substr($ultimoFolio->folio, -4);
            $nuevoNumero = str_pad($numeroActual + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $nuevoNumero = '0001';
        }

        return "CTR-{$fecha}-{$nuevoNumero}";
    }
}
