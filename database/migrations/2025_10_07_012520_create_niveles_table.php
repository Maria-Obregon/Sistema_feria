<?php

// database/migrations/2025_10_07_000000_create_niveles_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('niveles', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 120)->unique();
            $table->boolean('activo')->default(true);
            $table->timestamps();
            $table->index('activo');
        });
    }
    public function down(): void {
        Schema::dropIfExists('niveles');
    }
};
