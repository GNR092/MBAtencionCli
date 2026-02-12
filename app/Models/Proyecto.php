<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Proyecto extends Model
{
    protected $table = 'proyectos';
    protected $primaryKey = 'id_proyecto';

    protected $fillable = [
        'nombre_proyecto',
    ];

    public function users()
    {
        return $this->belongsToMany(User::class, 'user_proyectos', 'id_proyecto', 'id_user')
            ->using(UserProyecto::class)
            ->withPivot(['id_user_p']);
    }
}