<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('modalidades', function (Blueprint $table) {
            if (!Schema::hasColumn('modalidades', 'nivel_id')) {
                $table->foreignId('nivel_id')
                    ->nullable()
                    ->after('id')
                    ->constrained('niveles')
                    ->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('modalidades', function (Blueprint $table) {
            if (Schema::hasColumn('modalidades', 'nivel_id')) {
                // Si tu versión no soporta dropConstrainedForeignId, usa dropForeign+dropColumn
                try {
                    $table->dropConstrainedForeignId('nivel_id');
                } catch (\Throwable $e) {
                    $table->dropForeign(['nivel_id']);
                    $table->dropColumn('nivel_id');
                }
            }
        });
    }
};
