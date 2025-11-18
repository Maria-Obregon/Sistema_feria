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
        Schema::table('rubricas', function (Blueprint $table) {
            if (! Schema::hasColumn('rubricas', 'modo')) {
                $table->string('modo', 30)->default('por_criterio')->after('tipo_eval');
            }
            if (! Schema::hasColumn('rubricas', 'max_total')) {
                $table->smallInteger('max_total')->nullable()->after('modo');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('rubricas', function (Blueprint $table) {
            if (Schema::hasColumn('rubricas', 'max_total')) {
                $table->dropColumn('max_total');
            }
            if (Schema::hasColumn('rubricas', 'modo')) {
                $table->dropColumn('modo');
            }
        });
    }
};
