<?php

// database/migrations/XXXX_XX_XX_XXXXXX_create_categoria_modalidad_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('categoria_modalidad', function (Blueprint $table) {
            $table->id();
            $table->foreignId('categoria_id')->constrained('categorias')->cascadeOnDelete();
            $table->foreignId('modalidad_id')->constrained('modalidades')->cascadeOnDelete();
            $table->unique(['categoria_id','modalidad_id']);
            $table->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('categoria_modalidad');
    }
};
