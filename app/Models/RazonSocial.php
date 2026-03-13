<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RazonSocial extends Model
{
    protected $table = 'razones_sociales';

    protected $primaryKey = 'id_razon_social';

    protected $fillable = [
        'nombre_razon_social',
        'rfc',
        'telefono',
        'email',
        'direccion',
    ];

    public function proyectos()
    {
        return $this->hasMany(Proyecto::class, 'id_razon_social', 'id_razon_social');
    }
}
