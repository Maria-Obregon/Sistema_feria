<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('circuitos', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 100);
            $table->string('codigo', 10)->unique();
            $table->foreignId('regional_id')->constrained('regionales')->onDelete('restrict');
            $table->boolean('activo')->default(true);
            $table->timestamps();
            
            $table->index(['regional_id', 'activo']);
            $table->index('codigo');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('circuitos');
    }
};
