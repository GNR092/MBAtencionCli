<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class XmlFilesExport implements FromView
{
    protected $query;
    protected $totalBase;
    protected $totalISR;

    public function __construct($query, $totalBase, $totalISR)
    {
        $this->query = $query;
        $this->totalBase = $totalBase;
        $this->totalISR = $totalISR;
    }

    public function view(): View
    {
        // Ejecutamos la consulta
        $xmlFiles = $this->query->get();

        return view('exports.xml_files', [
            'xmlFiles' => $xmlFiles,
            'totalBase' => $this->totalBase,
            'totalISR' => $this->totalISR
        ]);
    }
}
