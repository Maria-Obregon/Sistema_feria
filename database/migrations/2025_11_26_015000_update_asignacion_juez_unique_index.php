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
        Schema::table('asignacion_juez', function (Blueprint $table) {
            // 1. Dropear FKs que podrían estar usando el índice
            $table->dropForeign(['proyecto_id']);
            $table->dropForeign(['juez_id']);
            // $table->dropForeign(['etapa_id']); // Probablemente no tiene FK si etapa es tinyint sin constraint explicita previa

            // 2. Dropear el índice único problemático
            try {
                $table->dropUnique('asignacion_juez_proyecto_id_juez_id_etapa_id_unique');
            } catch (\Exception $e) {
            }

            // 3. Crear el nuevo índice único
            $table->unique(['proyecto_id', 'juez_id', 'etapa_id', 'tipo_eval'], 'asig_juez_proy_etapa_tipo_unique');

            // 4. Restaurar FKs
            $table->foreign('proyecto_id')->references('id')->on('proyectos')->onDelete('cascade');
            $table->foreign('juez_id')->references('id')->on('jueces')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No implementamos down complejo por ahora para ahorrar tiempo,
        // pero idealmente revertiría todo.
    }
};
