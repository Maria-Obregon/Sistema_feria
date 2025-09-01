<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Notifications\Notifiable;

class Usuario extends Authenticatable
{
    use HasApiTokens, Notifiable;

    protected $table = 'usuario';
    protected $fillable = ['email','password','rol_id','activo'];
    protected $hidden = ['password','remember_token'];
    protected $casts = ['password'=>'hashed','activo'=>'boolean'];

    // Mapea created_at a tu columna del diagrama (opcional)
    const CREATED_AT = 'creado_en';
    const UPDATED_AT = null;

    public function rol(){ return $this->belongsTo(Rol::class, 'rol_id'); }
}
