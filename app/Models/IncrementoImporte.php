<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IncrementoImporte extends Model
{
    //
    use HasFactory;
    protected $table='incrementos_importe';

    protected $primarykey = 'id';
        // Indica que la PK es autoincremental
    public $incrementing = true;

    // Tipo de clave primaria
    protected $keyType = 'int';

    protected $fillable= [
        'id_contract',
        'fecha_inicio',
        'fecha_fin',
        'importe_base',
    ];

    public function contract()
    {
        return $this->belongsTo(Contract::class,'id_contract');
    }
}
