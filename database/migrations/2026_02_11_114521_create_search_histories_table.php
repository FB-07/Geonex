<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('search_histories', function (Blueprint $table) {
            $table->id();

            // Compatível com $table->increments('gfuser_id')
            $table->unsignedInteger('gfuser_id');

            $table->string('place_id', 150);
            $table->string('query')->nullable();

            $table->timestamps();

            $table->foreign('gfuser_id')
                ->references('gfuser_id')
                ->on('users')
                ->cascadeOnDelete();

            $table->foreign('place_id')
                ->references('place_id')
                ->on('places')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('search_histories');
    }
};