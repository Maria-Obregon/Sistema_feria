<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Regional extends Model
{
    use HasFactory;

    protected $table = 'regionales';

    protected $fillable = [
        'nombre',
        'codigo',
        'activo',
    ];

    protected function casts(): array
    {
        return [
            'activo' => 'boolean',
        ];
    }

    public function circuitos()
    {
        return $this->hasMany(Circuito::class);
    }

    public function usuarios()
    {
        return $this->hasMany(Usuario::class);
    }

    public function instituciones()
    {
        return $this->hasManyThrough(Institucion::class, Circuito::class);
    }
}
