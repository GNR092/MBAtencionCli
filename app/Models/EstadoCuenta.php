<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EstadoCuenta extends Model{
     use HasFactory;
         // Nombre de la tabla
    protected $table = 'estados';

    //
    protected $fillable = [
        'user_id',
        'proyecto',
        'monto',
        'fecha',
        'saldo',
        'importacion',
        'saldo_neto',
        'factura',
    ];

        public function user()
    {
        return $this->belongsTo(User::class);
    }
}