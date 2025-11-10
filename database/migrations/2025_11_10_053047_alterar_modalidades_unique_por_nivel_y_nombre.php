<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('modalidades', function (Blueprint $table) {
            // Quitar unique viejo (ajusta el nombre del índice si difiere)
            try { $table->dropUnique('modalidades_nombre_unique'); } catch (\Throwable $e) {}

            // Asegurar índice compuesto
            $table->unique(['nivel_id', 'nombre'], 'modalidades_nivel_nombre_unique');
        });
    }

    public function down(): void
    {
        Schema::table('modalidades', function (Blueprint $table) {
            // Reponer unique simple si hiciera falta (no recomendado)
            $table->dropUnique('modalidades_nivel_nombre_unique');
            $table->unique('nombre', 'modalidades_nombre_unique');
        });
    }
};