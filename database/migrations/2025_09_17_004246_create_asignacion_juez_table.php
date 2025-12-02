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
        Schema::create('asignacion_juez', function (Blueprint $table) {
            $table->id();

            $table->foreignId('proyecto_id')
                ->constrained('proyectos')
                ->cascadeOnDelete();

            $table->foreignId('juez_id')
                ->constrained('jueces')
                ->cascadeOnDelete();

            // igual que en asignacion_juez
            $table->unsignedTinyInteger('etapa_id'); // FK abajo
            $table->string('tipo_eval', 50)->nullable();
            $table->dateTime('asignado_en')->nullable();

            $table->timestamps();

            // FK a etapas con RESTRICT ON DELETE (igual que tu tabla singular)
            $table->foreign('etapa_id')
                ->references('id')->on('etapas')
                ->restrictOnDelete();

            // Evita duplicados
            $table->unique(['proyecto_id', 'juez_id', 'etapa_id', 'tipo_eval'], 'uniq_proy_juez_etapa_tipo');
        });
    }
};
