<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class UserProyecto extends Pivot
{
    public $incrementing = true;

    protected $table = 'user_proyectos';

    protected $primaryKey = 'id_user_p';

    protected $fillable = [
        'id_user',
        'id_proyecto',
    ];

    public function deptos()
    {
        return $this->hasMany(UserDepto::class, 'id_user_p', 'id_user_p');
    }

    /**
     * ESTE MÉTODO SOLUCIONA TU ERROR
     */
    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($userProyecto) {

            // borra automáticamente los deptos relacionados
            $userProyecto->deptos()->delete();

        });
    }

    public function proyecto()
    {
        return $this->belongsTo(Proyecto::class, 'id_proyecto', 'id_proyecto');
    }
}
