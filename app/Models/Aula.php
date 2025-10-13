<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Aula extends Model
{
    use HasFactory;

    protected $table = 'aulas';

    protected $fillable = [
        'codigo',
        'feria_id',
    ];

    protected function casts(): array
    {
        return [
            'creado_en' => 'datetime',
            'actualizado_en' => 'datetime',
        ];
    }

    public function feria()
    {
        return $this->belongsTo(Feria::class);
    }

    public function distribuciones()
    {
        return $this->hasMany(DistribucionAula::class);
    }
}
