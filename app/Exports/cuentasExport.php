<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class cuentasExport implements FromView
{
    protected $query;
    protected $totalPendiente;
    protected $totalPagado;

    public function __construct($query, $totalPendiente, $totalPagado)
    {
        $this->query = $query;
        $this->totalPendiente = $totalPendiente;
        $this->totalPagado = $totalPagado;
    }

    public function view(): View
    {
        // Ejecutamos la consulta
        $cuentas = $this->query->get();

        return view('exports.cuentas', [
            'cuentas' => $cuentas,
            'totalPendiente' => $this->totalPendiente,
            'totalPagado' => $this->totalPagado,
        ]);
    }
}