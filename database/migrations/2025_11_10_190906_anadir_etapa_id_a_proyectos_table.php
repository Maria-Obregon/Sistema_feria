<?php
// database/migrations/xxxx_add_etapa_id_to_proyectos.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('proyectos', function (Blueprint $table) {
            if (!Schema::hasColumn('proyectos','etapa_id')) {
                $table->unsignedTinyInteger('etapa_id')->nullable()->after('feria_id');
                $table->foreign('etapa_id')
                      ->references('id')->on('etapas')
                      ->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('proyectos', function (Blueprint $table) {
            if (Schema::hasColumn('proyectos','etapa_id')) {
                try {
                    $table->dropForeign(['etapa_id']);
                } catch (\Throwable $e) {}
                $table->dropColumn('etapa_id');
            }
        });
    }
};
