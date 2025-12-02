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
            $table->decimal('puntaje', 8, 2)->nullable()->after('tipo_eval');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('asignacion_juez', function (Blueprint $table) {
            $table->dropColumn('puntaje');
        });
    }
};
