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
        Schema::create('criterios', function (Blueprint $table) {
             $table->id();
    $table->foreignId('rubrica_id')->constrained('rubricas')->cascadeOnDelete();
    $table->string('nombre', 150);
    $table->decimal('peso', 5,2)->default(1.00);
    $table->unsignedInteger('max_puntos')->default(100);
    $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('criterios');
    }
};
