<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Proyecto extends Model
{
    protected $table = 'proyectos'; // Nombre de la tabla
    protected $primaryKey = 'id_proyecto'; // Clave primaria
    public $incrementing = false; // La clave primaria NO es autoincremental
    protected $keyType = 'string'; // El tipo de la clave primaria es string

    protected $fillable = [
        'id_proyecto',
        'nombre_proyecto',
    ];
}