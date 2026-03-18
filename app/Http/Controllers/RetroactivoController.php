<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RetroactivoController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        if (!$user || $user->role !== 'administrador') {
            return redirect('/inicio-de-sesion');
        }
        return view('retroactivo');
    }
}
