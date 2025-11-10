<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('proyectos', function (Blueprint $table) {
            // Si ya existiera, no la volvemos a crear
            if (!Schema::hasColumn('proyectos', 'feria_id')) {
                // si ya tienes proyectos viejos y no quieres romperlos, déjala nullable
                $table->foreignId('feria_id')->nullable()->after('institucion_id')
                      ->constrained('ferias')->cascadeOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('proyectos', function (Blueprint $table) {
            if (Schema::hasColumn('proyectos', 'feria_id')) {
                // elimina la FK y la columna
                $table->dropConstrainedForeignId('feria_id');
                // si tu versión de Laravel no soporta dropConstrainedForeignId:
                // $table->dropForeign(['feria_id']);
                // $table->dropColumn('feria_id');
            }
        });
    }
};
