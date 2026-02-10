<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('codigos_postais', function (Blueprint $table) {
            $table->string('codigo', 16)->primary();

            $table->foreignId('freguesia_id')
                  ->constrained('freguesias')
                  ->cascadeOnDelete();

            $table->timestamps();

            $table->index('freguesia_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('codigos_postais');
    }
};
