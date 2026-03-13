<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class AuthUser
{
    public function handle($request, Closure $next, $role = null)
    {

        if (! Auth::check()) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['error' => 'Unauthenticated.'], 401);
            }

            return redirect('/inicio-de-sesion');
        }

        $user = Auth::user();

        if ($role && $user->role !== $role) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['error' => 'Unauthorized.'], 403);
            }

            return redirect('/inicio-de-sesion');
        }

        return $next($request);
    }
}
