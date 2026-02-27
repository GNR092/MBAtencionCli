<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\RegimenFiscal;
use App\Models\Proyecto;
use \App\Models\UserDepto;


class crudUser extends Controller
{
    public function index(Request $request)
    {
        $proyectos = Proyecto::all();
        $regimenesFiscales = RegimenFiscal::all();
        $currentUser = Auth::user();

        $query = User::where('role', 'usuario')
            ->with([
                'userProyectos.deptos',
                'userProyectos.proyecto'
            ]);

        $search = $request->input('search');
        $categoria = $request->input('categoria');

        if ($search && $categoria) {
            switch ($categoria) {
                case 'nombre':
                    $query->where('name', 'LIKE', '%' . $search . '%');
                    break;

                case 'email':
                    $query->where('email', 'LIKE', '%' . $search . '%');
                    break;

                case 'proyecto':
                    $query->whereHas('userProyectos.proyecto', function ($q) use ($search) {
                        $q->where('nombre_proyecto', 'like', '%' . $search . '%');
                    });
                    break;
            }
        }

        if ($request->filled('month')) {
            $year  = substr($request->month, 0, 4);
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
        if (!$admin || $admin->role !== 'administrador') {
            return redirect('/inicio-de-sesion');
        }


        if (!Hash::check($request->password, $admin->password)) {
            return back()->withErrors(['password' => 'Contraseña incorrecta']);
        }


        session(['validated_edit_user' => $request->user_id]);


        return redirect()->route('usuarios.editar', $request->user_id);
    }

    public function showEditForm($id)
    {
        $admin = Auth::user();


        if (!$admin || $admin->role !== 'administrador') {
            return redirect('/inicio-de-sesion');
        }


        if (session('validated_edit_user') != $id) {
            return redirect()->route('usuarios.index')
                ->withErrors(['auth' => 'Debes confirmar tu contraseña antes de editar este usuario.']);
        }


        $userToEdit = User::findOrFail($id);
        $regimenesFiscales = RegimenFiscal::all();

        return view('editUser', compact('admin', 'userToEdit', 'regimenesFiscales'));
    }

    public function eliminar(Request $request)
    {
        $admin = Auth::user();

        if (!$admin || $admin->role !== 'administrador') {
            return redirect('/inicio-de-sesion');
        }

        if (!Hash::check($request->input('password'), $admin->password)) {
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
        if (!$admin || $admin->role !== 'administrador') {
            return redirect('/inicio-de-sesion');
        }

        $id = $request->input('id');

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $id,
            'phone' => 'required|string|max:20',
            'regimenFiscal' => 'required|integer',
            'password' => 'nullable|string|min:8|confirmed',
        ]);

        DB::transaction(function () use ($request, $id) {
            $user = User::findOrFail($id);

            // 1. Actualizar datos básicos del usuario
            $user->name = mb_convert_encoding($request->input('name'), 'UTF-8', 'UTF-8');
            $user->email = $request->input('email');
            $user->phone = '52' . $request->input('phone');
            $user->id_regimen = $request->input('regimenFiscal');

            if ($request->filled('password')) {
                $user->password = $request->input('password');
            }

            $user->save();

            // 2. Obtener proyectos actuales y nuevos
            $projectIds = $request->input('proyect', []);
            $currentProyectos = $user->userProyectos()->get()->keyBy('id_proyecto');
            $currentProjectIds = $currentProyectos->keys()->map(fn($k) => (int) $k)->toArray();
            $newProjectIds = array_map('intval', $projectIds);

            // 3. Eliminar proyectos que ya no están seleccionados (esto también elimina sus deptos por el boot del modelo)
            $projectsToRemove = array_diff($currentProjectIds, $newProjectIds);
            if (!empty($projectsToRemove)) {
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
                            UserDepto::create([
                                'id_user_p' => $userProyectoId,
                                'nombre' => $deptData['nombre_depto'],
                                'importe' => $deptData['importe'],
                                'predial' => isset($deptData['cuenta_predial']) ? ($deptData['cuenta_numero'] ?? '') : '',
                            ]);
                        }
                    }
                }
            }
        });

        session()->forget('validated_edit_user');

        return redirect(route('usuarios.index') . '#user-' . $id)
            ->with('success', 'Usuario editado correctamente.')
            ->with('highlight_user', $id);
    }

    public function store(Request $request)
    {
        $admin = Auth::user();
        if (!$admin || $admin->role !== 'administrador') {
            return redirect('/inicio-de-sesion');
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'phone' => 'required|string|max:20',
            'proyect' => 'sometimes|array',
            'regimenFiscal' => 'required|integer',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = User::create([
            'name' => mb_convert_encoding($request->name, 'UTF-8', 'UTF-8'),
            'email' => $request->email,
            'phone' => '52' . $request->phone,
            'id_regimen' => $request->regimenFiscal,
            'password' => Hash::make($request->password),
            'role' => 'usuario',
        ]);

        if ($request->has('proyect')) {
            $user->proyectos()->attach($request->proyect);
        }

        return redirect()->route('usuarios.index')->with('success', 'Usuario creado correctamente.');
    }
}
