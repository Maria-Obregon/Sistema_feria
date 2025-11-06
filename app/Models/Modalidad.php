<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Modalidad extends Model
{
    protected $table = 'modalidades';    
    protected $fillable = ['nombre','activo'];
    protected $casts = ['activo' => 'boolean'];
}
