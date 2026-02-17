<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::connection('mysql2')->table('pontos_interesse', function (Blueprint $table) {
            $table->unique(['gfuser_id', 'place_id'], 'unique_user_place');
        });
    }

    public function down(): void
    {
        Schema::connection('mysql2')->table('pontos_interesse', function (Blueprint $table) {
            $table->dropUnique('unique_user_nome_local');
        });
    }
};
