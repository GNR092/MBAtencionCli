<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Anuncio extends Model
{
    use HasFactory;

    // Forzamos el nombre de la tabla por si Laravel intenta buscar "anuncios" en plural de forma distinta
    protected $table = 'anuncios';

    protected $fillable = [
        'titulo',
        'descripcion',
        'adjunto_ruta',
        'estado',
        'prioridad',
    ];
}
