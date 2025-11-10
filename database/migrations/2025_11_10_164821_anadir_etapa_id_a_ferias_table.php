<?php
// database/migrations/2025_11_10_164821_anadir_etapa_id_a_ferias_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('ferias', function (Blueprint $table) {
            if (!Schema::hasColumn('ferias', 'etapa_id')) {
                // Debe coincidir con tinyIncrements de etapas.id
                $table->unsignedTinyInteger('etapa_id')->nullable()->after('id');
                $table->foreign('etapa_id')
                      ->references('id')->on('etapas')
                      ->nullOnDelete();
            }
        });

        // Backfill portable (SQLite/MySQL)
        // Asegura 3 etapas base
        foreach (['Institucional','Circuital','Regional'] as $nombre) {
            DB::table('etapas')->updateOrInsert(
                ['nombre' => $nombre],
                ['updated_at' => now(), 'created_at' => now()]
            );
        }
        $mapIds = DB::table('etapas')->pluck('id','nombre'); // ['Institucional'=>1, ...]

        $mapTipo = [
            'institucional' => 'Institucional',
            'circuital'     => 'Circuital',
            'regional'      => 'Regional',
        ];

        DB::table('ferias')->whereNull('etapa_id')->orderBy('id')
          ->chunkById(100, function ($rows) use ($mapTipo, $mapIds) {
            foreach ($rows as $f) {
                $key = strtolower(trim((string)($f->tipo_feria ?? '')));
                $nombre = $mapTipo[$key] ?? null;
                if ($nombre && isset($mapIds[$nombre])) {
                    DB::table('ferias')->where('id',$f->id)->update(['etapa_id' => $mapIds[$nombre]]);
                }
            }
        });
    }

    public function down(): void
    {
        Schema::table('ferias', function (Blueprint $table) {
            if (Schema::hasColumn('ferias', 'etapa_id')) {
                try {
                    $table->dropForeign(['etapa_id']);
                } catch (\Throwable $e) {}
                $table->dropColumn('etapa_id');
            }
        });
    }
};
