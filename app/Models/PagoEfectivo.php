<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PagoEfectivo extends Model
{
    use HasFactory;

    protected $table = 'pagos_efectivo';

    protected $fillable = [
        'id_contract',
        'id_cuentas_por_pagar',
        'id_user',
        'monto',
        'fecha_pago',
        'mes_pago',
        'concepto',
    ];

    public function contract()
    {
        return $this->belongsTo(Contract::class);
    }

    public function cuenta()
    {
        return $this->belongsTo(Cuentas::class, 'id_cuentas_por_pagar', 'id_cuentas_por_pagar');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
