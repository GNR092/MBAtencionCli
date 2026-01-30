<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Session;

class AuthUser
{
    public function handle($request, Closure $next, $role = null)
    {
        $user = Session::get('user');

        // No hay usuario logueado
        if (!$user) {
            return redirect('/inicio-de-sesion');
        }

        // El rol no coincide
        if ($role && $user->rol !== $role) {
            return redirect('/inicio-de-sesion');
        }

        return $next($request);
    }
}
