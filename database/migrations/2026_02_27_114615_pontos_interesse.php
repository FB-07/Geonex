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
            $table->foreignId('distrito_id')->nullable()->constrained('distritos')->cascadeOnDelete();
            $table->foreignId('municipio_id')->nullable()->constrained('municipios')->cascadeOnDelete();
            $table->foreignId('freguesia_id')->nullable()->constrained('freguesias')->cascadeOnDelete();
            $table->string('place_id', 150);
            $table->timestamps();

            $table->foreign('gfuser_id')->references('gfuser_id')->on('users')->onDelete('cascade');
            $table->foreign('place_id')->references('place_id')->on('places')->onDelete('cascade');

            $table->unique(['gfuser_id', 'place_id'], 'unique_user_place');
        });
    }

    public function down(): void
    {
        Schema::connection('mysql2')->dropIfExists('pontos_interesse');
    }
};
