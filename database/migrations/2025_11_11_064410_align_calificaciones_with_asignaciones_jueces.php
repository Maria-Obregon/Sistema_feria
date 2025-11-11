<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (! Schema::hasColumn('calificaciones', 'asignaciones_juez_id')) {
            Schema::table('calificaciones', function (Blueprint $table) {
                $table->unsignedBigInteger('asignaciones_juez_id')->nullable()->after('asignacion_juez_id');
            });
        }

        try {
            Schema::table('calificaciones', function (Blueprint $table) {
                $table->foreign('asignaciones_juez_id')
                    ->references('id')
                    ->on('asignaciones_jueces')
                    ->cascadeOnDelete();
            });
        } catch (\Throwable $e) {
            // ignore if FK already exists
        }

        $driver = DB::getDriverName();
        $indexName = 'uq_calif_asig_criterio';
        $needsIndex = true;
        try {
            if ($driver === 'mysql') {
                $exists = DB::selectOne(
                    "SELECT COUNT(1) AS c FROM information_schema.statistics WHERE table_schema = DATABASE() AND table_name = 'calificaciones' AND index_name = ?",
                    [$indexName]
                );
                $needsIndex = empty($exists) || ((int) ($exists->c ?? 0) === 0);
            } elseif ($driver === 'sqlite') {
                $list = DB::select("PRAGMA index_list('calificaciones')");
                foreach ($list as $idx) {
                    if (($idx->name ?? '') === $indexName) {
                        $needsIndex = false;
                        break;
                    }
                }
            }
        } catch (\Throwable $e) {
            // fall through to attempt create
        }

        if ($needsIndex) {
            try {
                Schema::table('calificaciones', function (Blueprint $table) use ($indexName) {
                    $table->unique(['asignaciones_juez_id', 'criterio_id'], $indexName);
                });
            } catch (\Throwable $e) {
                // ignore if exists
            }
        }

        $sqlViewSelect = 'SELECT id, proyecto_id, juez_id, etapa_id, tipo_eval, created_at, updated_at FROM asignaciones_jueces';
        try {
            if ($driver === 'mysql') {
                DB::statement("CREATE OR REPLACE VIEW asignacion_juez AS $sqlViewSelect");
            } elseif ($driver === 'sqlite') {
                DB::statement("CREATE VIEW IF NOT EXISTS asignacion_juez AS $sqlViewSelect");
            } else {
                DB::statement("CREATE OR REPLACE VIEW asignacion_juez AS $sqlViewSelect");
            }
        } catch (\Throwable $e) {
            // ignore view errors
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        try {
            DB::statement('DROP VIEW IF EXISTS asignacion_juez');
        } catch (\Throwable $e) { /* noop */
        }

        $indexName = 'uq_calif_asig_criterio';
        try {
            Schema::table('calificaciones', function (Blueprint $table) use ($indexName) {
                try {
                    $table->dropUnique($indexName);
                } catch (\Throwable $e) { /* noop */
                }
            });
        } catch (\Throwable $e) { /* noop */
        }

        try {
            Schema::table('calificaciones', function (Blueprint $table) {
                try {
                    $table->dropForeign(['asignaciones_juez_id']);
                } catch (\Throwable $e) { /* noop */
                }
            });
        } catch (\Throwable $e) { /* noop */
        }

        if (Schema::hasColumn('calificaciones', 'asignaciones_juez_id')) {
            Schema::table('calificaciones', function (Blueprint $table) {
                $table->dropColumn('asignaciones_juez_id');
            });
        }
    }
};
