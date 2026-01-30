<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class UserController extends Controller
{
    public function dashboard() { return view('viewUser'); }
    public function facturacion() { return view('factura'); }
    public function cuentasCobrar() { return view('cuentasCobrar'); }
    public function estadosDeCuenta() { return view('estadosDeCuenta'); }
    public function contratos() { return view('contratos'); }
    public function notificaciones() { return view('notificaciones'); }
}

