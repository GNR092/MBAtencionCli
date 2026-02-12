<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model; 

class UserProyecto extends Model
{
    protected $table = 'user_proyectos';
    protected $primaryKey = 'id_user_p';

    
    public $incrementing = true;

    protected $fillable = [
        'id_user',
        'id_proyecto'
    ];

    public function deptos()
    {
        return $this->hasMany(UserDepto::class, 'id_user_p', 'id_user_p');
    }
}