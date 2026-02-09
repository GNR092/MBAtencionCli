<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RegimenFiscal extends Model
{
    protected $table = 'regimen_fiscals';
    protected $primaryKey = 'id_regimen';

    protected $fillable = [
        'nombre_regimen',
        'iva_regimen',
    ];

    protected $casts = [
        'iva_regimen' => 'float',
    ];
}