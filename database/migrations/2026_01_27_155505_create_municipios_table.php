<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('municipios', function (Blueprint $table) {
            $table->id();
            $table->foreignId('distrito_id')->constrained('distritos')->cascadeOnDelete();
            $table->string('nome');
            $table->timestamps();

            $table->unique(['distrito_id', 'nome']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('municipios');
    }
};
