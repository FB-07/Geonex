<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('pontos_interesse', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('gfuser_id');
            $table->string('nome', 255);
            $table->char('color', 7)->nullable();
            $table->foreignId('distrito_id')->nullable()->constrained('distritos')->nullOnDelete();
            $table->foreignId('municipio_id')->nullable()->constrained('municipios')->nullOnDelete();
            $table->foreignId('freguesia_id')->nullable()->constrained('freguesias')->nullOnDelete();
            $table->timestamps();

            $table->foreign('gfuser_id')
                  ->references('gfuser_id')
                  ->on('users')
                  ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pontos_interesse');
    }
};