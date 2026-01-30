<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Contract extends Model
{
    use HasFactory;

    // Nombre de la tabla (opcional si sigue convención plural "contracts")
     protected $table = 'contract';
    // Campos que se pueden asignar masivamente
    protected $fillable = [
        'user_id',
        'estado',
        'fecha_inicio',
        'fecha_fin',
        'nombre',
        'tipo', 
        'contenido',
        'folio', 
        'fecha',
        'importe_bruto_renta',
        'fecha_terminacion',
        'proyecto',
    ];

    // Relación: un contrato pertenece a un usuario
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    
}
