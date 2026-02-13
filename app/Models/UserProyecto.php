<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class UserProyecto extends Pivot
{
    protected $table = 'user_proyectos';
    protected $primaryKey = 'id_user_p';

    protected $fillable = [
        'id_user',
        'id_proyecto'
    ];

    public function deptos()
    {
        return $this->hasMany(UserDepto::class, 'id_user_p', 'id_user_p');
    }
}