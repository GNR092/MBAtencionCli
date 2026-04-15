<?php

namespace App\Http\Controllers;

use App\Models\Proyecto;
use App\Models\RegimenFiscal;
use App\Models\User;
use App\Models\UserDepto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class crudUser extends Controller
{
    public function index(Request $request)
    {
        $proyectos = Proyecto::with('razonSocial')->get();
        $regimenesFiscales = RegimenFiscal::all();
        $currentUser = Auth::user();

        $query = User::where('role', 'usuario')
            ->with([
                'userProyectos.deptos',
                'userProyectos.proyecto',
            ]);

        $search = $request->input('search');
        $categoria = $request->input('categoria');

        if ($search && $categoria) {
            switch ($categoria) {
                case 'nombre':
                    $query->where('name', 'LIKE', '%'.$search.'%');
                    break;

                case 'email':
                    $query->where('email', 'LIKE', '%'.$search.'%');
                    break;

                case 'proyecto':
                    $query->whereHas('userProyectos.proyecto', function ($q) use ($search) {
                        $q->where('nombre_proyecto', 'like', '%'.$search.'%');
                    });
                    break;
            }
        }

        if ($request->filled('month')) {
            $year = substr($request->month, 0, 4);
            $month = substr($request->month, 5, 2);

            $query->whereYear('created_at', $year)
                ->whereMonth('created_at', $month);
        }

        $users = $query->paginate(10);

        $roles = ['admin', 'jefe', 'usuario'];

        $areas = [];

        return view('admiUsers', compact(
            'users',
            'search',
            'categoria',
            'roles',
            'areas',
            'proyectos',
            'regimenesFiscales'
        ));

    }

    public function limpiar()
    {
        return redirect()->route('usuarios.index');
    }

    public function confirmPassword(Request $request)
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

        session(['validated_edit_user' => $request->user_id]);

        return redirect()->route('usuarios.editar', $request->user_id);
    }

    public function showEditForm($id)
    {
        $admin = Auth::user();

        if (! $admin || $admin->role !== 'administrador') {
            return redirect('/inicio-de-sesion');
        }

        if (session('validated_edit_user') != $id) {
            return redirect()->route('usuarios.index')
                ->withErrors(['auth' => 'Debes confirmar tu contraseña antes de editar este usuario.']);
        }

        $userToEdit = User::with(['userProyectos.deptos', 'userProyectos.proyecto'])->findOrFail($id);
        $regimenesFiscales = RegimenFiscal::all();
        $proyectos = Proyecto::with('razonSocial')->get();

        $selectedProjectIds = $userToEdit->userProyectos
            ->pluck('id_proyecto')
            ->map(fn ($id) => (string) $id)
            ->toArray();

        $existingProjectsData = $userToEdit->userProyectos
            ->mapWithKeys(function ($up) {
                return [$up->id_proyecto => $up->deptos->map(fn ($d) => [
                    'nombre' => $d->nombre,
                    'tipo' => $d->tipo,
                    'importe' => $d->importe,
                    'tiene_predial' => $d->predial !== '' && $d->predial !== 'N/A' && $d->predial !== null,
                    'cuenta_numero' => ($d->predial !== 'N/A' && $d->predial !== null) ? $d->predial : '',
                ])->toArray()];
            })->toArray();

        $existingProjectPaymentMethods = $userToEdit->userProyectos
            ->mapWithKeys(fn ($up) => [$up->id_proyecto => $up->metodo_pago])
            ->toArray();

        return view('editUser', compact('admin', 'userToEdit', 'regimenesFiscales', 'proyectos', 'selectedProjectIds', 'existingProjectsData', 'existingProjectPaymentMethods'));
    }

    public function eliminar(Request $request)
    {
        $admin = Auth::user();

        if (! $admin || $admin->role !== 'administrador') {
            return redirect('/inicio-de-sesion');
        }

        if (! Hash::check($request->input('password'), $admin->password)) {
            return back()->with('error', 'Contraseña incorrecta');
        }

        $id = $request->input('user_id');

        if ($id == $admin->id) {
            return back()->with('error', 'No puedes eliminar tu propia cuenta.');
        }

        DB::transaction(function () use ($id) {

            $pivots = \App\Models\UserProyecto::where('id_user', $id)->get();

            foreach ($pivots as $pivot) {

                \App\Models\UserDepto::where('id_user_p', $pivot->id_user_p)->delete();
            }

            \App\Models\UserProyecto::where('id_user', $id)->delete();

            User::destroy($id);
        });

        return back()->with('success', 'Usuario eliminado correctamente.');
    }

    public function editar(Request $request)
    {
        $admin = Auth::user();
        if (! $admin || $admin->role !== 'administrador') {
            return redirect('/inicio-de-sesion');
        }

        $id = $request->input('id');

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,'.$id,
            'phone' => 'required|string|max:20',
            'regimenFiscal' => 'required|integer',
            'password' => 'nullable|string|min:8|confirmed',
            'fecha_nacimiento' => 'nullable|date',
            'project_payment_methods' => 'nullable|array',
            'project_payment_methods.*' => 'nullable|in:efectivo,transferencia',
            'project_details' => 'nullable|array',
            'project_details.*' => 'nullable|array',
            'project_details.*.*.nombre_depto' => 'required_with:project_details|string|max:255',
            'project_details.*.*.importe' => 'required_with:project_details|numeric|min:0',
            'project_details.*.*.tipo' => 'required_with:project_details|in:Campus,Condominios',
            'project_details.*.*.cuenta_numero' => 'nullable|string|max:255',
        ]);

        $projectPaymentMethods = $request->input('project_payment_methods', []);
        $projectIds = $request->input('proyect', []);
        foreach ($projectIds as $projectId) {
            if (empty($projectPaymentMethods[$projectId])) {
                return back()
                    ->withErrors([
                        "project_payment_methods.$projectId" => 'Selecciona el metodo de pago para cada proyecto.',
                    ])
                    ->withInput();
            }
        }

        DB::transaction(function () use ($request, $id, $projectPaymentMethods) {
            $user = User::findOrFail($id);

            // 1. Actualizar datos básicos del usuario
            $user->name = mb_convert_encoding($request->input('name'), 'UTF-8', 'UTF-8');
            $user->email = $request->input('email');
            $user->phone = '52'.$request->input('phone');
            $user->id_regimen = $request->input('regimenFiscal');
            $user->fecha_nacimiento = $request->input('fecha_nacimiento') ?: null;
            $user->metodo_pago = collect($projectPaymentMethods)
                ->first(fn ($metodo) => in_array($metodo, ['efectivo', 'transferencia'], true)) ?: null;

            if ($request->filled('password')) {
                $user->password = $request->input('password');
            }

            $user->save();

            // 2. Obtener proyectos actuales y nuevos
            $projectIds = $request->input('proyect', []);
            $currentProyectos = $user->userProyectos()->get()->keyBy('id_proyecto');
            $currentProjectIds = $currentProyectos->keys()->map(fn ($k) => (int) $k)->toArray();
            $newProjectIds = array_map('intval', $projectIds);

            foreach ($currentProyectos as $proyectoActual) {
                $metodoProyecto = $projectPaymentMethods[$proyectoActual->id_proyecto] ?? null;
                $proyectoActual->metodo_pago = $metodoProyecto ?: null;
                $proyectoActual->save();
            }

            // 3. Eliminar proyectos que ya no están seleccionados (esto también elimina sus deptos por el boot del modelo)
            $projectsToRemove = array_diff($currentProjectIds, $newProjectIds);
            if (! empty($projectsToRemove)) {
                \App\Models\UserProyecto::where('id_user', $user->id)
                    ->whereIn('id_proyecto', $projectsToRemove)
                    ->get()
                    ->each(function ($pivot) {
                        $pivot->delete();
                    });
            }

            // 4. Agregar proyectos nuevos
            $projectsToAdd = array_diff($newProjectIds, $currentProjectIds);
            foreach ($projectsToAdd as $projectId) {
                \App\Models\UserProyecto::create([
                    'id_user' => $user->id,
                    'id_proyecto' => $projectId,
                    'metodo_pago' => $projectPaymentMethods[$projectId] ?? null,
                ]);
            }

            // 5. Refrescar la colección de proyectos del usuario
            $user->load('userProyectos');
            $userProyectos = $user->userProyectos->keyBy('id_proyecto');

            // 6. Limpiar departamentos de proyectos que se mantienen (se recrearán)
            if ($userProyectos->isNotEmpty()) {
                UserDepto::whereIn('id_user_p', $userProyectos->pluck('id_user_p'))->delete();
            }

            // 7. Procesar y guardar los nuevos detalles de departamentos
            if ($request->has('project_details')) {
                foreach ($request->project_details as $projectId => $departments) {
                    if (isset($userProyectos[$projectId])) {
                        $userProyectoId = $userProyectos[$projectId]->id_user_p;
                        foreach ($departments as $deptData) {
                            $predial = trim((string) ($deptData['cuenta_numero'] ?? ''));

                            UserDepto::create([
                                'id_user_p' => $userProyectoId,
                                'nombre' => $deptData['nombre_depto'],
                                'tipo' => $deptData['tipo'],
                                'importe' => $deptData['importe'],
                                'predial' => $predial,
                            ]);
                        }
                    }
                }
            }
        });

        session()->forget('validated_edit_user');

        return redirect(route('usuarios.index').'#user-'.$id)
            ->with('success', 'Usuario editado correctamente.')
            ->with('highlight_user', $id);
    }

    public function cumpleanios()
    {
        $hoy = now();
        $mesActual = $hoy->month;

        // Todos los inversionistas con fecha de nacimiento
        $todos = User::where('role', 'usuario')
            ->whereNotNull('fecha_nacimiento')
            ->orderByRaw('MONTH(fecha_nacimiento), DAY(fecha_nacimiento)')
            ->get()
            ->map(function ($user) use ($hoy) {
                $cumple = \Carbon\Carbon::parse($user->fecha_nacimiento)->setYear($hoy->year);
                if ($cumple->lt($hoy->startOfDay())) {
                    $cumple->addYear();
                }
                $user->dias_para_cumple = $hoy->startOfDay()->diffInDays($cumple->startOfDay());
                $user->edad = $hoy->year - \Carbon\Carbon::parse($user->fecha_nacimiento)->year;

                return $user;
            })
            ->sortBy('dias_para_cumple');

        $esteMes = $todos->filter(fn ($u) => \Carbon\Carbon::parse($u->fecha_nacimiento)->month === $mesActual);
        $proximoMes = $todos->filter(fn ($u) => \Carbon\Carbon::parse($u->fecha_nacimiento)->month === ($mesActual % 12) + 1);
        $restantes = $todos->reject(fn ($u) => in_array(\Carbon\Carbon::parse($u->fecha_nacimiento)->month, [$mesActual, ($mesActual % 12) + 1]));

        return view('cumpleanios', compact('esteMes', 'proximoMes', 'restantes', 'hoy'));
    }

    public function store(Request $request)
    {
        $admin = Auth::user();
        if (! $admin || $admin->role !== 'administrador') {
            return redirect('/inicio-de-sesion');
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'phone' => 'required|string|max:20',
            'proyect' => 'sometimes|array',
            'regimenFiscal' => 'required|integer',
            'password' => 'required|string|min:8|confirmed',
            'fecha_nacimiento' => 'nullable|date',
        ]);

        $user = User::create([
            'name' => mb_convert_encoding($request->name, 'UTF-8', 'UTF-8'),
            'email' => $request->email,
            'phone' => '52'.$request->phone,
            'id_regimen' => $request->regimenFiscal,
            'password' => Hash::make($request->password),
            'role' => 'usuario',
            'fecha_nacimiento' => $request->fecha_nacimiento ?: null,
        ]);

        if ($request->has('proyect')) {
            $user->proyectos()->attach($request->proyect);
        }

        return redirect()->route('usuarios.index')->with('success', 'Usuario creado correctamente.');
    }
}
