<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('asignacion_juez', function (Blueprint $table) {
            $table->boolean('fue_finalizada')->default(false)->after('finalizada_at');
        });

        // Backfill existing finalized assignments
        DB::table('asignacion_juez')
            ->whereNotNull('finalizada_at')
            ->update(['fue_finalizada' => true]);
    }

    public function down()
    {
        Schema::table('asignacion_juez', function (Blueprint $table) {
            $table->dropColumn('fue_finalizada');
        });
    }
};
