<?php

namespace Canva\HBD\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class HbdAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::check()) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['error' => 'Unauthenticated.'], 401);
            }
            return redirect('/login');
        }

        $user = Auth::user();
        $adminRole = config('hbd.admin_role', 'administrador');

        if ($user->role !== $adminRole) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['error' => 'Unauthorized.'], 403);
            }
            return redirect('/');
        }

        return $next($request);
    }
}
