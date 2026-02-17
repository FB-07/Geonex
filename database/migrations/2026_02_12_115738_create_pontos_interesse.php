<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::connection('mysql2')->create('pontos_interesse', function (Blueprint $table) {
            $table->id();
            $table->string('nome', 255);
            $table->unsignedInteger('gfuser_id');
            $table->foreignId('distrito_id')->constrained('distritos')->cascadeOnDelete();
            $table->foreignId('municipio_id')->constrained('municipios')->cascadeOnDelete();
            $table->foreignId('freguesia_id')->constrained('freguesias')->cascadeOnDelete();
            $table->string('place_id', 150);
            $table->timestamps();
            $table->foreign('gfuser_id')->references('gfuser_id')->on('users')->onDelete('cascade');
            $table->foreign('place_id')->references('place_id')->on('places')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pontos_interesse');
    }
};
