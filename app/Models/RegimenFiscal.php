<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RegimenFiscal extends Model
{
    protected $table = 'regimen_fiscals';

    protected $primaryKey = 'id_regimen';

    public $incrementing = false;

    protected $keyType = 'int';

    protected $fillable = [
        'id_regimen',
        'nombre_regimen',
        'tasa_retencion',
    ];

    protected $casts = [
        'tasa_retencion' => 'float',
    ];
}
