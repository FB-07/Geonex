<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
            Schema::create('users', function (Blueprint $table) {

            $table->increments('gfuser_id'); 

            $table->string('gfuser_alias', 25);
            $table->string('gfuser_password', 64);    
            $table->string('gfuser_fullname', 255);   

            $table->enum('gfuser_sex', ['M','F']);    

            $table->char('gfuser_active', 1);     

            $table->string('gfuser_email', 200)->nullable();

            $table->unique('gfuser_alias');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};