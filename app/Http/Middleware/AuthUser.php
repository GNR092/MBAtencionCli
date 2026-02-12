<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Auth; 

class AuthUser
{
    public function handle($request, Closure $next, $role = null)
    {
        
        if (!Auth::check()) {
            return redirect('/inicio-de-sesion');
        }

        $user = Auth::user();

        
        if ($role && $user->role !== $role) {
            return redirect('/inicio-de-sesion');
        }

        return $next($request);
    }
}
