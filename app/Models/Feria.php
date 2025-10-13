<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Feria extends Model
{
    use HasFactory;

    protected $table = 'ferias';

    protected $fillable = [
        'anio',
        'institucion_id',
        'fecha',
        'hora_inicio',
        'sede',
        'proyectos_por_aula',
        'tipo_feria',
        'correo_notif',
        'telefono_fax',
        'lugar_realizacion',
        'circuito_id',
    ];

    protected function casts(): array
    {
        return [
            'fecha' => 'date',
            'anio' => 'integer',
            'proyectos_por_aula' => 'integer',
            'creado_en' => 'datetime',
            'actualizado_en' => 'datetime',
        ];
    }

    public function circuito()
    {
        return $this->belongsTo(Circuito::class);
    }

    public function institucion()
    {
        return $this->belongsTo(Institucion::class);
    }

    public function aulas()
    {
        return $this->hasMany(Aula::class);
    }
}
