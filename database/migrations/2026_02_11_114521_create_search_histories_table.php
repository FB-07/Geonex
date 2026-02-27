<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration{
    public function up(): void
    {
        Schema::create('search_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('gfuser_id')->constrained()->cascadeOnDelete();
            $table->foreign('gfuser_id')->references('gfuser_id')->on('users')->onDelete('cascade');
            $table->string('place_id', 150);
            $table->foreign('place_id')->references('place_id')->on('places')->onDelete('cascade');
            $table->string('query')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('search_histories');
    }
};

