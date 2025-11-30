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
        Schema::create('categoria_rubrica', function (Blueprint $table) {
            $table->id();
            $table->foreignId('categoria_id')->constrained('categorias')->onDelete('cascade');
            $table->foreignId('rubrica_id')->constrained('rubricas')->onDelete('cascade');

            // Corrección: etapas.id es tinyint unsigned
            $table->unsignedTinyInteger('etapa_id');
            $table->foreign('etapa_id')->references('id')->on('etapas')->onDelete('cascade');

            $table->enum('tipo_eval', ['escrito', 'exposicion']);
            $table->timestamps();

            $table->unique(['categoria_id', 'etapa_id', 'tipo_eval'], 'cat_rub_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('categoria_rubrica');
    }
};
