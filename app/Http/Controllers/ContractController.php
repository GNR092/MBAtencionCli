<?php

namespace App\Http\Controllers;

use App\Models\Contract;
use App\Models\User;
use App\Models\UserDepto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

class ContractController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        if (! $user) {
            return redirect('/inicio-de-sesion');
        }

        $query = DB::table('contract')
            ->leftJoin('user_proyectos', 'contract.id_user_p', '=', 'user_proyectos.id_user_p')
            ->leftJoin('proyectos', 'user_proyectos.id_proyecto', '=', 'proyectos.id_proyecto')
            ->leftJoin('user_depto', 'contract.id_user_depto', '=', 'user_depto.id_user_depto')
            ->select('contract.*', 'proyectos.nombre_proyecto as proyecto', 'user_depto.nombre as departamento', 'user_depto.predial as predial')
            ->where(function ($q) use ($user) {
                $q->where('contract.user_id', $user->id)
                    ->orWhere('user_proyectos.id_user', $user->id);
            })
            ->orderBy('contract.created_at', 'asc');

        $search = session('search');
        $categoria = session('categoria');

        if ($search && $categoria) {
            switch ($categoria) {
                case 'id':
                    $query->where('id', $search);
                    break;
                case 'folio':
                    $query->where('folio', $search);
                    break;
                case 'fecha':
                    $query->whereDate('fecha', $search);
                    break;
            }
        }

        $contratos = $query->paginate(6);

        if ($request->expectsJson()) {
            $html = view('contratos', compact('contratos', 'search', 'categoria'))->render();

            return response()->json(['html' => $html]);
        }

        return view('contratos', compact('contratos', 'search', 'categoria'));
    }

    public function buscar(Request $request)
    {

        session([
            'search' => $request->input('search'),
            'categoria' => $request->input('categoria'),
        ]);

        return redirect()->route('contratos.index');
    }

    public function limpiar()
    {

        session()->forget(['search', 'categoria']);

        return redirect()->route('contratos.index');
    }

    public function search(Request $request)
    {

        session([
            'search' => $request->input('search'),
            'categoria' => $request->input('categoria'),
        ]);

        return redirect()->route('admin.contratos.index');
    }

    public function clean()
    {

        session()->forget(['search', 'categoria']);

        return redirect()->route('admin.contratos.index');
    }

    public function confirmPassword(Request $request)
    {
        $request->validate([
            'password' => 'required|string',
        ]);

        $admin = Auth::user();

        if (! $admin || $admin->role !== 'administrador') {
            return redirect('/inicio-de-sesion');
        }

        if (Hash::check($request->password, $admin->password)) {

            session(['validated_admin_contract' => true]);

            return redirect()->route('admin.contratos.create');
        }

        return back()->with('error', 'Contraseña incorrecta, intenta nuevamente.');
    }

    public function confirmPasswordEdit(Request $request)
    {
        $request->validate([
            'user_id' => 'required|integer',
            'password' => 'required|string',
        ]);

        $admin = Auth::user();
        if (! $admin || $admin->role !== 'administrador') {
            return redirect('/inicio-de-sesion');
        }

        if (! Hash::check($request->password, $admin->password)) {
            return back()->withErrors(['password' => 'Contraseña incorrecta']);
        }

        session(['validated_edit_contrato' => $request->user_id]);

        return redirect()->route('admin.contratos.editar', $request->user_id);
    }

    public function editar($id)
    {
        $admin = Auth::user();

        if (! $admin || $admin->role !== 'administrador') {
            return redirect('/inicio-de-sesion');
        }

        if (session('validated_edit_contrato') != $id) {
            return redirect()->route('admin.contratos.index')
                ->withErrors(['auth' => 'Debes confirmar tu contraseña antes de editar este contrato.']);
        }

        $contractToEdit = Contract::findOrFail($id);
        $users = User::all();
        $proyectos = \App\Models\Proyecto::orderBy('nombre_proyecto')->get();
        $currentProyectoId = optional(\App\Models\UserProyecto::find($contractToEdit->id_user_p))->id_proyecto;
        $contratoBloqueado = $this->isContratoBloqueado((int) $contractToEdit->id);
        $proyectoActual = $proyectos->firstWhere('id_proyecto', $currentProyectoId);

        $departamentosProyectoActual = collect();
        if ($contractToEdit->id_user_p) {
            $departamentosProyectoActual = DB::table('user_depto')
                ->where('id_user_p', $contractToEdit->id_user_p)
                ->orderBy('nombre')
                ->get(['id_user_depto', 'nombre', 'predial', 'tipo']);
        }

        return view('editContrato', compact('admin', 'contractToEdit', 'users', 'proyectos', 'currentProyectoId', 'contratoBloqueado', 'proyectoActual', 'departamentosProyectoActual'));
    }

    public function actualizar(Request $request, $id)
    {
        $contrato = Contract::findOrFail($id);
        $idUserPAnterior = (int) $contrato->id_user_p;

        if ($this->isContratoBloqueado((int) $contrato->id)) {
            return back()->with('error', 'Este contrato tiene movimientos de pago y no puede modificarse. Debes renovarlo con un nuevo PDF.');
        }

        $request->validate([
            'user_id' => 'required|exists:users,id',
            'proyect' => 'required|exists:proyectos,id_proyecto',
            'id_user_depto' => 'nullable|exists:user_depto,id_user_depto',
            'nuevo_depto_nombre' => 'nullable|string|max:255',
            'nuevo_depto_predial' => 'nullable|string|max:255',
            'importe_bruto_renta' => 'required',
            'archivo' => 'sometimes|file|mimes:pdf|max:2048', // 'sometimes' makes it optional
        ]);

        $userId = $request->input('user_id');
        $proyectoId = $request->input('proyect');

        // Find the user_proyectos pivot record
        $userProyecto = \App\Models\UserProyecto::firstOrCreate([
            'id_user' => $userId,
            'id_proyecto' => $proyectoId,
        ]);

        $requestedDeptoId = $request->input('id_user_depto');
        $nuevoDeptoNombre = trim((string) $request->input('nuevo_depto_nombre', ''));

        if ($requestedDeptoId || $nuevoDeptoNombre !== '') {
            $idUserDepto = $this->resolveOrCreateDeptoFromRequest($request, (int) $userProyecto->id_user_p, $contrato->id_user_depto, str_replace(['$', ','], '', $request->input('importe_bruto_renta')));
        } else {
            // Si solo cambian proyecto, mantenemos el departamento actual del contrato.
            $deptoAnterior = $contrato->id_user_depto ?: $this->resolveUserDeptoId($idUserPAnterior, null);
            $idUserDepto = $deptoAnterior ?: $this->resolveUserDeptoId((int) $userProyecto->id_user_p, null);
        }

        if (! $idUserDepto) {
            // Permitir cambiar solo el proyecto sin forzar alta/cambio de departamento.
            // Si el contrato no tiene departamento ligado y el proyecto tampoco tiene deptos,
            // se mantiene en null para no bloquear la corrección del proyecto.
            $idUserDepto = null;
        }

        // Handle file update
        $path = $contrato->contenido;
        if ($request->hasFile('archivo')) {
            $archivo = $request->file('archivo');

            // Eliminar archivo anterior
            if (Storage::exists($contrato->contenido)) {
                Storage::delete($contrato->contenido);
            }

            // Guardar nuevo en ruta organizada por usuario
            $path = $this->storeContractFile($archivo, $userId);
            $contrato->nombre = $archivo->getClientOriginalName();
            $contrato->tipo = $archivo->getMimeType();
        }

        // Update contract fields
        $contrato->user_id = $userId;
        $contrato->id_user_p = $userProyecto->id_user_p;
        $contrato->id_user_depto = $idUserDepto;
        $contrato->importe_bruto_renta = str_replace(['$', ','], '', $request->input('importe_bruto_renta'));
        $contrato->estado = $request->input('activo') ? 'activo' : ($request->input('inactivo') ? 'inactivo' : 'desconocido');
        $contrato->contenido = $path;

        $contrato->save();

        return redirect()->route('admin.contratos.index')->with('success', 'Contrato actualizado correctamente.');
    }

    public function renovar(Request $request, $id)
    {
        $admin = Auth::user();
        if (! $admin || $admin->role !== 'administrador') {
            return redirect('/inicio-de-sesion');
        }

        $contratoOriginal = Contract::findOrFail($id);

        if (! $this->isContratoBloqueado((int) $contratoOriginal->id)) {
            return back()->with('error', 'Este contrato no tiene movimientos. Puedes editarlo sin renovarlo.');
        }

        $request->validate([
            'user_id' => 'required|exists:users,id',
            'proyect' => 'required|exists:proyectos,id_proyecto',
            'id_user_depto' => 'nullable|exists:user_depto,id_user_depto',
            'nuevo_depto_nombre' => 'nullable|string|max:255',
            'nuevo_depto_predial' => 'nullable|string|max:255',
            'importe_bruto_renta' => 'required',
            'fecha_inicio' => 'required|date',
            'fecha_terminacion' => 'required|date|after_or_equal:fecha_inicio',
            'archivo' => 'required|file|mimes:pdf|max:2048',
        ]);

        $userId = (int) $request->input('user_id');
        $proyectoId = (int) $request->input('proyect');

        $userProyecto = \App\Models\UserProyecto::firstOrCreate([
            'id_user' => $userId,
            'id_proyecto' => $proyectoId,
        ]);

        $requestedDeptoId = $request->input('id_user_depto');
        $nuevoDeptoNombre = trim((string) $request->input('nuevo_depto_nombre', ''));

        if ($requestedDeptoId || $nuevoDeptoNombre !== '') {
            $idUserDepto = $this->resolveOrCreateDeptoFromRequest($request, (int) $userProyecto->id_user_p, $contratoOriginal->id_user_depto, str_replace(['$', ','], '', $request->input('importe_bruto_renta')));
        } else {
            // En renovación, si no se solicita cambio explícito de depto, conservar el actual.
            $deptoAnterior = $contratoOriginal->id_user_depto ?: $this->resolveUserDeptoId((int) $contratoOriginal->id_user_p, null);
            $idUserDepto = $deptoAnterior ?: $this->resolveUserDeptoId((int) $userProyecto->id_user_p, null);
        }

        if (! $idUserDepto) {
            return back()->with('error', 'Selecciona un departamento válido o captura uno nuevo para renovar este contrato.');
        }

        $archivo = $request->file('archivo');
        $path = $this->storeContractFile($archivo, $userId);

        DB::transaction(function () use ($request, $contratoOriginal, $userId, $userProyecto, $archivo, $path, $idUserDepto) {
            Contract::create([
                'user_id' => $userId,
                'nombre' => $archivo->getClientOriginalName(),
                'tipo' => $archivo->getMimeType(),
                'contenido' => $path,
                'id_user_p' => $userProyecto->id_user_p,
                'folio' => $this->generarFolio(),
                'fecha' => now(),
                'estado' => 'activo',
                'importe_bruto_renta' => str_replace(['$', ','], '', $request->input('importe_bruto_renta')),
                'fecha_inicio' => $request->input('fecha_inicio'),
                'fecha_terminacion' => $request->input('fecha_terminacion'),
                'id_user_depto' => $idUserDepto,
            ]);

            if ($contratoOriginal->estado !== 'inactivo') {
                $contratoOriginal->estado = 'inactivo';
                $contratoOriginal->save();
            }
        });

        return redirect()->route('admin.contratos.index')->with('success', 'Contrato renovado correctamente. Se creó un nuevo contrato con PDF actualizado y el anterior quedó inactivo.');
    }

    public function subir(Request $request)
    {
        $request->validate([
            'archivo' => 'required|file|mimes:pdf|max:2048',
            'user_id' => 'required|exists:users,id',
            'proyect' => 'required|exists:proyectos,id_proyecto',
            'id_user_depto' => 'nullable|exists:user_depto,id_user_depto',
            'nuevo_depto_nombre' => 'nullable|string|max:255',
            'nuevo_depto_predial' => 'nullable|string|max:255',
            'importe_bruto_renta' => 'required',
            'fecha_inicio' => 'required|date',
            'fecha_terminacion' => 'required|date',
        ]);

        $userId = $request->input('user_id');
        $proyectoId = $request->input('proyect');
        $archivo = $request->file('archivo');

        // Guardar en ruta organizada por usuario: contracts/{user_id}/{timestamp}_{nombre}
        $path = $this->storeContractFile($archivo, $userId);

        $userProyecto = \App\Models\UserProyecto::firstOrCreate([
            'id_user' => $userId,
            'id_proyecto' => $proyectoId,
        ]);

        $idUserDepto = $this->resolveOrCreateDeptoFromRequest(
            $request,
            (int) $userProyecto->id_user_p,
            null,
            str_replace(['$', ','], '', $request->input('importe_bruto_renta'))
        );
        if (! $idUserDepto) {
            Storage::delete($path);

            return back()->with('error', 'Selecciona un departamento válido o captura uno nuevo para este proyecto.');
        }

        Contract::create([
            'user_id' => $userId,
            'nombre' => $archivo->getClientOriginalName(),
            'tipo' => $archivo->getMimeType(),
            'contenido' => $path,
            'id_user_p' => $userProyecto->id_user_p,
            'folio' => $this->generarFolio(),
            'fecha' => now(),
            'estado' => $this->generarEstado($request),
            'importe_bruto_renta' => str_replace(['$', ','], '', $request->input('importe_bruto_renta')),
            'fecha_inicio' => $request->input('fecha_inicio'),
            'fecha_terminacion' => $request->input('fecha_terminacion'),
            'id_user_depto' => $idUserDepto,
        ]);

        return back()->with('success', '✅ Archivo enviado correctamente.');
    }

    public function crear()
    {
        $admin = Auth::user();

        if (! $admin || $admin->role !== 'administrador') {
            return redirect('/inicio-de-sesion');
        }

        if (! session('validated_admin_contract')) {
            return redirect()->route('admin.contratos.index')->with('error', '⚠️ Debes confirmar tu contraseña antes de crear un contrato.');
        }

        session()->forget('validated_admin_contract');

        $users = User::all();
        $proyectos = $admin->proyectos;
        $contract = null;

        return view('adContrato', compact('users', 'proyectos', 'admin', 'contract'));
    }

    public function delete(Request $request)
    {
        $admin = Auth::user();

        if (! $admin || $admin->role !== 'administrador') {
            return redirect('/inicio-de-sesion');
        }

        if (! Hash::check($request->input('password'), $admin->password)) {
            return back()->with('error', 'Contraseña incorrecta');
        }

        $contratoId = $request->input('id');

        $contrato = Contract::find($contratoId);

        if (! $contrato) {
            return back()->with('error', 'Contrato no encontrado.');
        }

        if ($this->isContratoBloqueado((int) $contrato->id)) {
            return back()->with('error', 'No puedes eliminar un contrato con movimientos de pago.');
        }

        $contrato->delete();

        return back()->with('success', 'Contrato eliminado correctamente.');
    }

    public function show()
    {
        $user = Auth::user();
        if (! $user) {
            return redirect('/inicio-de-sesion');
        }

        $users = User::all();

        $query = DB::table('contract')
            ->join('users', 'contract.user_id', '=', 'users.id')
            ->leftJoin('user_proyectos', 'contract.id_user_p', '=', 'user_proyectos.id_user_p')
            ->leftJoin('proyectos', 'user_proyectos.id_proyecto', '=', 'proyectos.id_proyecto')
            ->leftJoin('user_depto', 'contract.id_user_depto', '=', 'user_depto.id_user_depto')
            ->select('contract.*', 'users.name as user_name', DB::raw('COALESCE(proyectos.nombre_proyecto, "Sin proyecto") as proyecto'), DB::raw('COALESCE(user_depto.nombre, "Sin departamento") as departamento'), DB::raw('COALESCE(NULLIF(user_depto.predial, ""), "Sin predial") as predial'))
            ->orderBy('contract.created_at', 'asc');

        if (request()->filled('month')) {
            $year = substr(request('month'), 0, 4);
            $month = substr(request('month'), 5, 2);

            $query->whereYear('contract.created_at', $year)
                ->whereMonth('contract.created_at', $month);
        }

        $search = session('search');
        $categoria = session('categoria');

        if ($search && $categoria) {
            switch ($categoria) {
                case 'id':
                    $query->where('contract.id', $search);
                    break;
                case 'name':
                    $query->where('users.name', 'like', "%{$search}%");
                    break;
            }
        }

        $contratos = $query->paginate(6);

        session()->forget('validated_edit_contract');

        return view('subirContrato', compact('users', 'contratos', 'search', 'categoria'));
    }

    public function descargar($id)
    {
        $user = Auth::user();
        if (! $user) {
            return redirect('/inicio-de-sesion');
        }

        $contrato = Contract::findOrFail($id);

        // Authorization: only the owner or an admin can download
        if ($user->id !== $contrato->user_id && $user->role !== 'administrador') {
            abort(403, 'No tienes permiso para descargar este archivo.');
        }

        if (! Storage::exists($contrato->contenido)) {
            abort(404, 'Archivo no encontrado.');
        }

        $path = Storage::path($contrato->contenido);
        $name = $contrato->nombre;

        return response()->streamDownload(function () use ($path) {
            $stream = fopen($path, 'rb');
            fpassthru($stream);
            fclose($stream);
        }, $name, [
            'Content-Type' => 'application/pdf',
            'Content-Length' => filesize($path),
        ]);
    }

    public function getProjectsForUser(User $user)
    {
        $admin = Auth::user();

        if (! $admin || $admin->role !== 'administrador') {
            abort(403, 'Unauthorized action.');
        }

        return response()->json($user->proyectos);
    }

    public function getDepartmentsForUserProject(User $user, int $project)
    {
        $admin = Auth::user();

        if (! $admin || $admin->role !== 'administrador') {
            abort(403, 'Unauthorized action.');
        }

        $userProyecto = DB::table('user_proyectos')
            ->where('id_user', $user->id)
            ->where('id_proyecto', $project)
            ->first(['id_user_p']);

        if (! $userProyecto) {
            return response()->json([]);
        }

        $deptos = DB::table('user_depto')
            ->where('id_user_p', $userProyecto->id_user_p)
            ->orderBy('nombre')
            ->get(['id_user_depto', 'nombre', 'predial', 'tipo', 'importe']);

        return response()->json($deptos);
    }

    /**
     * Guarda el PDF en una ruta organizada por usuario:
     *   contracts/{user_id}/{YYYYMMDDHHMMSS}_{nombre_original}
     *
     * Esto permite:
     *   - Respaldar contratos por usuario:  contracts/{user_id}/
     *   - Descargar con Storage::download() sin cambios en descargar()
     *
     * @return string Ruta relativa almacenada en 'contenido'
     */
    private function storeContractFile(\Illuminate\Http\UploadedFile $file, int $userId): string
    {
        $safeName = now()->format('YmdHis').'_'.$file->getClientOriginalName();

        return Storage::putFileAs("contracts/{$userId}", $file, $safeName);
    }

    private function generarFolio()
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

    private function generarEstado(Request $request)
    {
        if ($request->input('activo')) {
            return 'activo';
        } elseif ($request->input('inactivo')) {
            return 'inactivo';
        }

        return 'desconocido';
    }

    private function isContratoBloqueado(int $contractId): bool
    {
        $hasMovimientosCxp = DB::table('cuentasporpagar')
            ->where('id_contract', $contractId)
            ->where(function ($q) {
                $q->where('estado', 'pagado')
                    ->orWhere('monto_pagado', '>', 0);
            })
            ->exists();

        if ($hasMovimientosCxp) {
            return true;
        }

        if (! Schema::hasTable('pagos_efectivo')) {
            return false;
        }

        return DB::table('pagos_efectivo')
            ->where(function ($q) use ($contractId) {
                $q->where('id_contract', $contractId)
                    ->orWhereIn('id_cuentas_por_pagar', function ($sub) use ($contractId) {
                        $sub->from('cuentasporpagar')
                            ->select('id_cuentas_por_pagar')
                            ->where('id_contract', $contractId);
                    });
            })
            ->exists();
    }

    private function resolveUserDeptoId(int $idUserP, ?int $preferredDeptoId): ?int
    {
        if ($preferredDeptoId) {
            $sameDepto = DB::table('user_depto')
                ->where('id_user_p', $idUserP)
                ->where('id_user_depto', $preferredDeptoId)
                ->value('id_user_depto');

            if ($sameDepto) {
                return (int) $sameDepto;
            }
        }

        $firstDepto = DB::table('user_depto')
            ->where('id_user_p', $idUserP)
            ->orderBy('id_user_depto')
            ->value('id_user_depto');

        return $firstDepto ? (int) $firstDepto : null;
    }

    private function resolveOrCreateDeptoFromRequest(Request $request, int $idUserP, ?int $preferredDeptoId, $importeBase): ?int
    {
        $requestedDeptoId = $request->input('id_user_depto');
        if ($requestedDeptoId) {
            $belongsToProject = DB::table('user_depto')
                ->where('id_user_p', $idUserP)
                ->where('id_user_depto', (int) $requestedDeptoId)
                ->exists();

            if ($belongsToProject) {
                return (int) $requestedDeptoId;
            }
        }

        $nuevoDeptoNombre = trim((string) $request->input('nuevo_depto_nombre', ''));
        if ($nuevoDeptoNombre !== '') {
            $nuevoPredial = trim((string) $request->input('nuevo_depto_predial', ''));
            $tipoBase = DB::table('user_depto')
                ->where('id_user_p', $idUserP)
                ->whereNotNull('tipo')
                ->value('tipo');

            $nuevo = UserDepto::create([
                'id_user_p' => $idUserP,
                'nombre' => $nuevoDeptoNombre,
                'tipo' => $tipoBase ?: 'Condominios',
                'importe' => is_numeric($importeBase) ? (float) $importeBase : 0,
                'predial' => $nuevoPredial,
            ]);

            return (int) $nuevo->id_user_depto;
        }

        return $this->resolveUserDeptoId($idUserP, $preferredDeptoId);
    }
}
