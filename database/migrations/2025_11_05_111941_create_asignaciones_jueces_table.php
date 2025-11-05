<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('asignaciones_jueces')) {
            Schema::create('asignaciones_jueces', function (Blueprint $table) {
                $table->id();

                $table->foreignId('proyecto_id')
                    ->constrained('proyectos')
                    ->cascadeOnDelete();

                $table->foreignId('juez_id')
                    ->constrained('jueces')
                    ->cascadeOnDelete();

                // Si tienes tabla etapas, luego ponemos FK en otra migración.
                $table->unsignedTinyInteger('etapa_id'); // 1=Institucional, 2=Circuital, 3=Regional (por ejemplo)

                $table->enum('tipo_eval', ['escrito','exposicion','integral'])->default('integral');

                $table->timestamps();

                // Evita duplicados (proyecto, juez, etapa)
                $table->unique(['proyecto_id','juez_id','etapa_id'], 'uniq_proy_juez_etapa');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('asignaciones_jueces');
    }
};
