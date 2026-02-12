<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Auth; // Importar la fachada Auth

class AuthUser
{
    public function handle($request, Closure $next, $role = null)
    {
        // Verificar si hay un usuario autenticado
        if (!Auth::check()) {
            return redirect('/inicio-de-sesion');
        }

        $user = Auth::user();

        // El role no coincide
        if ($role && $user->role !== $role) {
            return redirect('/inicio-de-sesion');
        }

        return $next($request);
    }
}
