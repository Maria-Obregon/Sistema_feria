<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('rubrica_asignacion', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('modalidad_id')->nullable();
            $table->unsignedBigInteger('categoria_id')->nullable();
            $table->unsignedBigInteger('nivel_id')->nullable();
            $table->unsignedBigInteger('etapa_id');
            $table->enum('tipo_eval', ['escrito', 'exposicion']);
            $table->unsignedBigInteger('rubrica_id');
            $table->timestamps();

            // Índices para búsqueda
            $table->index(['modalidad_id']);
            $table->index(['categoria_id']);
            $table->index(['nivel_id']);
            $table->index(['etapa_id']);
            $table->index(['tipo_eval']);

            // Unicidad por combinación (evitar duplicados)
            $table->unique([
                'modalidad_id', 'categoria_id', 'nivel_id', 'etapa_id', 'tipo_eval',
            ], 'uq_rubrica_asignacion_clave');

            // FKs (best-effort; asumen nombres de tablas existentes)
            // No usar cascade para no romper datos existentes
            $table->foreign('rubrica_id')->references('id')->on('rubricas');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rubrica_asignacion');
    }
};
