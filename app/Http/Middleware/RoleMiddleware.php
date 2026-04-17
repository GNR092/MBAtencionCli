<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class RoleMiddleware
{
    public function handle($request, Closure $next, $role = null)
    {
        $user = Auth::user();

        if (! $user) {
            return redirect('/login');
        }

        if ($role && $user->role !== $role) {
            abort(403, 'No autorizado');
        }

        return $next($request);
    }
}
