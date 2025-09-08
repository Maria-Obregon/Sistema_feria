<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class Usuario extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles;

    protected $table = 'usuarios';
    protected $guard_name = 'sanctum';

    protected $fillable = [
        'nombre',
        'apellidos',
        'email',
        'password',
        'rol_id',
        'activo',
        'regional_id',
        'circuito_id',
        'institucion_id',
        'telefono',
        'identificacion',
        'tipo_identificacion',
        'two_factor_secret',
        'two_factor_recovery_codes',
        'two_factor_confirmed_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_secret',
        'two_factor_recovery_codes',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'activo' => 'boolean',
        ];
    }

    public function rol()
    {
        return $this->belongsTo(Rol::class);
    }

    // Relaciones multi-tenant
    public function regional()
    {
        return $this->belongsTo(Regional::class);
    }

    public function circuito()
    {
        return $this->belongsTo(Circuito::class);
    }

    public function institucion()
    {
        return $this->belongsTo(Institucion::class);
    }

    // Scope para multi-tenancy
    public function scopeForRegional($query, $regionalId)
    {
        return $query->where('regional_id', $regionalId);
    }
}
