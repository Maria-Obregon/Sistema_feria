<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Etapa extends Model
{
    protected $fillable = ['nombre'];

    // helpers
    public const INSTITUCIONAL = 'Institucional';

    public const CIRCUITAL = 'Circuital';

    public const REGIONAL = 'Regional';

    public function ferias()
    {
        return $this->hasMany(Feria::class);
    }

    public function modalidades()
    {
        return $this->belongsToMany(Modalidad::class, 'modalidad_etapa');
    }

    public function proyectos()
    {
        return $this->hasMany(Proyecto::class);
    } // si usás etapa_id en proyectos

    public static function idPorNombre(string $nombre): ?int
    {
        return static::query()->whereRaw('LOWER(nombre)=LOWER(?)', [$nombre])->value('id');
    }

    public static function idsCircuitalYRegional(): array
    {
        $c = static::idPorNombre('CIRCUITAL');
        $r = static::idPorNombre('REGIONAL');

        return array_values(array_filter([$c, $r], fn ($v) => ! is_null($v)));
    }
}
