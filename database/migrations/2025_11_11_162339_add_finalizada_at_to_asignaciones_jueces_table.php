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
        if (! Schema::hasColumn('asignacion_juez', 'finalizada_at')) {
            Schema::table('asignacion_juez', function (Blueprint $table) {
                $table->timestamp('finalizada_at')->nullable()->after('tipo_eval');
                $table->index('finalizada_at');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('asignaciones_jueces', 'finalizada_at')) {
            Schema::table('asignaciones_jueces', function (Blueprint $table) {
                $table->dropIndex(['finalizada_at']);
                $table->dropColumn('finalizada_at');
            });
        }
    }
};
