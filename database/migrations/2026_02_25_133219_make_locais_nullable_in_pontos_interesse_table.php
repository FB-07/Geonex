<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pontos_interesse', function (Blueprint $table) {
            $table->unsignedBigInteger('distrito_id')->nullable()->change();
            $table->unsignedBigInteger('municipio_id')->nullable()->change();
            $table->unsignedBigInteger('freguesia_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('pontos_interesse', function (Blueprint $table) {
            $table->unsignedBigInteger('distrito_id')->nullable(false)->change();
            $table->unsignedBigInteger('municipio_id')->nullable(false)->change();
            $table->unsignedBigInteger('freguesia_id')->nullable(false)->change();
        });
    }
};
