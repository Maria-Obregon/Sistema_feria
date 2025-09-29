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
        Schema::create('juez_institucion', function (Blueprint $table) {
            $table->id();
    $table->foreignId('juez_id')->constrained('jueces')->cascadeOnDelete();
    $table->foreignId('institucion_id')->constrained('instituciones')->cascadeOnDelete();
    $table->timestamps();

    $table->unique(['juez_id','institucion_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('juez_institucion');
    }
};
