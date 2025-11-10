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

    // MUY importante para Spatie + Sanctum
    protected $guard_name = 'sanctum';

    protected $fillable = [
        'nombre',
        'apellidos',
        'email',
        'password', 
        'activo',
        'regional_id',
        'circuito_id',
        'institucion_id',
        'telefono',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'activo' => 'boolean',
        ];
    }
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

    public function juez()
    {
        return $this->hasOne(Juez::class, 'usuario_id');
    }

    public function scopeForRegional($query, $regionalId)
    {
        return $query->where('regional_id', $regionalId);
    }
}
