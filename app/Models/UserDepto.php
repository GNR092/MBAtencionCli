<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserDepto extends Model
{
    use HasFactory;

    protected $table = 'user_depto';

    protected $primaryKey = 'id_user_depto';

    protected $fillable = [
        'id_user_p',
        'nombre',
        'tipo',
        'predial',
        'importe',
    ];

    public function userProyecto()
    {
        return $this->belongsTo(UserProyecto::class, 'id_user_p', 'id_user_p');
    }
}
