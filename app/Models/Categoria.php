<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Categoria extends Model
{
    use HasFactory;

    protected $table = 'categorias';
    protected $fillable = ['nombre','nivel','activo']; // tienes 'activo' en la tabla
    protected $casts = ['activo' => 'boolean'];

    public function proyectos()
    {
        return $this->hasMany(Proyecto::class);
    }
    public function modalidades() {
    return $this->belongsToMany(\App\Models\Modalidad::class, 'categoria_modalidad');
}
}
