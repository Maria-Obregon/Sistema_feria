<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('instituciones', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 200);
            $table->string('codigo_presupuestario', 20)->unique();
            $table->foreignId('circuito_id')->constrained('circuitos')->onDelete('restrict');
            $table->enum('tipo', ['publica', 'privada', 'subvencionada']);
            $table->string('telefono', 20)->nullable();
            $table->string('email', 100)->nullable();
            $table->text('direccion')->nullable();
            $table->boolean('activo')->default(true);
            $table->integer('limite_proyectos')->default(50);
            $table->integer('limite_estudiantes')->default(200);
            $table->timestamps();
            
            $table->index(['circuito_id', 'activo']);
            $table->index('codigo_presupuestario');
            $table->index('tipo');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('instituciones');
    }
};
