<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class RoleMiddleware
{
    public function handle($request, Closure $next, $role = null)
    {
        $user = Auth::user();

        if (!$user) {
            return redirect('/inicio-de-sesion');
        }

        if ($role && $user->rol !== $role) {
            abort(403, 'No autorizado');
        }

        return $next($request);
    }
}
