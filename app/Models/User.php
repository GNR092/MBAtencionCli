<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    protected $primaryKey = 'id';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'email_verified_at',
        'phone',
        'id_regimen',
        'foto',
        'fecha_nacimiento',
        'metodo_pago',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function contract()
    {
        return $this->hasMany(Contract::class);
    }

    public function regimenFiscal()
    {
        return $this->belongsTo(RegimenFiscal::class, 'id_regimen', 'id_regimen');
    }

    public function proyectos()
    {
        return $this->belongsToMany(Proyecto::class, 'user_proyectos', 'id_user', 'id_proyecto')
            ->using(UserProyecto::class);
    }

    public function userProyectos()
    {
        return $this->hasMany(UserProyecto::class, 'id_user', 'id');
    }
}
