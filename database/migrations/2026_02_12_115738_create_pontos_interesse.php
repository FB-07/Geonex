<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pontos_interesse', function (Blueprint $table) {
            $table->id();
            $table->string('nome');
            $table->foreignId('gfuser_id')->constrained()->cascadeOnDelete();
            $table->foreignId('distritos_id')->constrained('distritos')->cascadeOnDelete();
            $table->foreignId('municipios_id')->constrained('municipio0s')->cascadeOnDelete();
            $table->foreignId('freguesia_id')->constrained('freguesias')->cascadeOnDelete();

            $table->foreignId('place_id')->constrained()->cascadeOnDelete();

        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pontos_interesse');
    }
};
