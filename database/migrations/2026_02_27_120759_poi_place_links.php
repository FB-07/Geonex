<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::connection('mysql2')->create('poi_place_links', function (Blueprint $table) {
            $table->id();

            $table->unsignedInteger('gfuser_id');
            $table->foreign('gfuser_id')->references('gfuser_id')->on('users')->onDelete('cascade');
            
            $table->foreignId('ponto_interesse_id')->constrained('pontos_interesse')->cascadeOnDelete();

            $table->foreignId('place_id')->constrained('places')->cascadeOnDelete();

            $table->char('color', 7)->nullable();

            $table->timestamps();

            $table->unique(['gfuser_id', 'ponto_interesse_id', 'place_id'], 'unique_user_poi_place');
        });
    }

    public function down(): void
    {
        Schema::connection('mysql2')->dropIfExists('poi_place_links');
    }
};