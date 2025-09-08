<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Circuito extends Model
{
    use HasFactory;

    protected $table = 'circuitos';

    protected $fillable = [
        'nombre',
        'codigo',
        'regional_id',
        'activo',
    ];

    protected function casts(): array
    {
        return [
            'activo' => 'boolean',
        ];
    }

    public function regional()
    {
        return $this->belongsTo(Regional::class);
    }

    public function instituciones()
    {
        return $this->hasMany(Institucion::class);
    }

    public function usuarios()
    {
        return $this->hasMany(Usuario::class);
    }
}
