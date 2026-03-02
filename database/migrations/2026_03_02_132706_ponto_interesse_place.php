<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('ponto_interesse_place', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ponto_interesse_id')->constrained('pontos_interesse')->onDelete('cascade');
            $table->string('place_id', 150);
            $table->foreign('place_id')->references('place_id')->on('places')->onDelete('cascade');
            $table->timestamps();

            $table->unique(['ponto_interesse_id', 'place_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ponto_interesse_place');
    }
};