<?php

// database/migrations/xxxx_xx_xx_xxxxxx_alter_categorias_nivel_nullable.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('categorias', function (Blueprint $table) {
            $table->string('nivel', 120)->nullable()->change();
        });
    }
    public function down(): void {
        Schema::table('categorias', function (Blueprint $table) {
            $table->string('nivel', 120)->nullable(false)->change();
        });
    }
};
