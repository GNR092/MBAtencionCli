<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EstadoCuenta extends Model
{
    use HasFactory;

    protected $table = 'estados_de_cuenta';

    //
    protected $fillable = [
        'user_id',
        'proyecto',
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
