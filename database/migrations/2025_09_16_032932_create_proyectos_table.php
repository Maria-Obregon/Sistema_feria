<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('proyectos', function (Blueprint $table) {
            $table->id();
            $table->string('titulo', 250);
            $table->text('resumen'); // Máximo 250 palabras - validación en modelo
            $table->foreignId('area_id')->constrained('areas')->onDelete('restrict');
            $table->foreignId('categoria_id')->constrained('categorias')->onDelete('restrict');
            $table->foreignId('institucion_id')->constrained('instituciones')->onDelete('restrict');
            $table->enum('estado', ['inscrito', 'en_evaluacion', 'evaluado', 'promovido', 'no_promovido', 'descalificado']);
            $table->json('palabras_clave')->nullable();
            $table->string('archivo_proyecto')->nullable(); // PDF máximo 10MB
            $table->string('archivo_presentacion')->nullable(); // Imágenes máximo 5MB
            $table->foreignId('modalidad_id')->constrained('modalidades')->restrictOnDelete();
            $table->string('codigo', 30)->unique();
            $table->timestamps();
            
            $table->index(['area_id', 'categoria_id']);
            $table->index('estado');
        });
    }
    

    public function down(): void
    {
        Schema::dropIfExists('proyectos');
    }
};
