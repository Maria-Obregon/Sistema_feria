<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DistribucionAula extends Model
{
    use HasFactory;

    protected $table = 'distribucion_aula';

    protected $fillable = [
        'aula_id',
        'proyecto_id',
    ];

    protected function casts(): array
    {
        return [
            'creado_en' => 'datetime',
            'actualizado_en' => 'datetime',
        ];
    }

    public function aula()
    {
        return $this->belongsTo(Aula::class);
    }

    public function proyecto()
    {
        return $this->belongsTo(Proyecto::class);
    }
}
