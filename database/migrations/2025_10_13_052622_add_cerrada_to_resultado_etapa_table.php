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
        Schema::table('resultado_etapa', function (Blueprint $table) {
            $table->boolean('cerrada')->default(false)->after('nota_final');
            $table->unique(['proyecto_id', 'etapa_id'], 'resultado_etapa_proyecto_etapa_unique');
        });
    }

    public function down(): void
    {
        Schema::table('resultado_etapa', function (Blueprint $table) {
            $table->dropUnique('resultado_etapa_proyecto_etapa_unique');
            $table->dropColumn('cerrada');
        });
    }
};
