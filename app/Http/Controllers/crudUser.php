<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class crudUser extends Controller{
    public function index(Request $request){
            $currentUser = Session::get('user'); // usuario logueado

            $query = DB::table('users')
                ->where('rol','usuario');

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
                        $query->whereRaw('LOWER(proyect) LIKE ?', ['%' . strtolower($search) . '%']);
                        break;
                }
            }

            // FILTRO POR MES
            if ($request->filled('month')) {
                $year  = substr($request->month, 0, 4);
                $month = substr($request->month, 5, 2);

                $query->whereYear('users.created_at', $year)
                    ->whereMonth('users.created_at', $month);
            }

            $users = $query->paginate(6);
            $roles = ['admin', 'jefe', 'usuario'];
            $areas = [];

            return view('admiUsers', compact('users','search', 'categoria', 'roles', 'areas'));
    }

    public function limpiar()
    {
        return redirect()->route('admiUsers');
    }

    public function confirmPassword(Request $request)
    {
        $request->validate([
            'user_id' => 'required|integer',
            'password' => 'required|string',
        ]);

        // Verifica que sea admin desde sesión
        $admin = Session::get('user');
        if (!$admin || $admin->rol !== 'administrador') {
            return redirect('/inicio-de-sesion');
        }

        // Verifica la contraseña del admin
        if (!Hash::check($request->password, $admin->password)) {
            return back()->withErrors(['password' => 'Contraseña incorrecta']);
        }

        // Guardamos en sesión que ya validó
        session(['validated_edit_user' => $request->user_id]);

        // Redirigimos al formulario de edición
        return redirect()->route('users.edit', $request->user_id);
    }

    public function showEditForm($id)
    {
        $admin = Session::get('user');

        // Verificar que sea admin
        if (!$admin || $admin->rol !== 'administrador') {
            return redirect('/inicio-de-sesion');
        }

        // Verificar que el admin validó antes de abrir este usuario
        if (session('validated_edit_user') != $id) {
            return redirect()->route('admiUsers')
                            ->withErrors(['auth' => 'Debes confirmar tu contraseña antes de editar este usuario.']);
        }

        // Usuario que se quiere editar
        $userToEdit = User::findOrFail($id);

        return view('editUser', compact('admin', 'userToEdit'));
    }

    public function eliminar(Request $request)
    {
        $admin = Session::get('user');

        // Verificar que sea admin
        if (!$admin || $admin->rol !== 'administrador') {
            return redirect('/inicio-de-sesion');
        }

        // Validar contraseña ingresada
        if (!Hash::check($request->input('password'), $admin->password)) {
            return back()->with('error', 'Contraseña incorrecta');
        }

        $id = $request->input('user_id');

        // Evitar autodestrucción del admin
        if ($id == $admin->id) {
            return back()->with('error', ' No puedes eliminar tu propia cuenta de administrador.');
        }

        // Eliminar usuario
        User::destroy($id);

        return back()->with('success', 'Usuario eliminado correctamente.');
    }

    public function editar(Request $request)
{
    $admin = Session::get('user');

    // Verificar que sea admin
    if (!$admin || $admin->rol !== 'administrador') {
        return redirect('/inicio-de-sesion');
    }

    $id = $request->input('id');
    $name = $request->input('name');
    $email = $request->input('email');
    $phone = '52' . $request->input('phone'); // anteponer 52
    $proyect = $request->input('proyect');
    $regimenFiscal = $request->input('regimenFiscal');
    $password = $request->input('password');

    // Datos base que siempre se actualizan
    $data = [
        'name' => $name,
        'email' => $email,
        'phone' => $phone,
        'proyect' => json_encode($proyect),
        'regimenFiscal' => $regimenFiscal,
    ];

    // Solo actualizar contraseña si no viene vacía
    if (!empty($password)) {
        $data['password'] = Hash::make($password);
    }

    DB::table('users')->where('id', $id)->update($data);

    // limpiar validación después de editar
    session()->forget('validated_edit_user');

    return redirect()->route('admiUsers')
        ->with('success', 'Usuario editado correctamente.');
}

}
