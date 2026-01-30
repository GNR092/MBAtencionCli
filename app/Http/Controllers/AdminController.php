<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function dashboard() { return view('viewAdministrador'); }
    public function subirArchivo() { return view('subirContrato'); }
    
}
