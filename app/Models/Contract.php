<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Contract extends Model
{
    use HasFactory;

    
     protected $table = 'contract';
    
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
        'id_user_p',
    ];

    
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function userProyecto()
    {
        return $this->belongsTo(UserProyecto::class, 'id_user_p', 'id_user_p');
    }

    
}
