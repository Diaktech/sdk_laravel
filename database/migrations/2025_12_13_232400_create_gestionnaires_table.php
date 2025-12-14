<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('gestionnaires', function (Blueprint $table) {
            $table->id();
            $table->string('unique_id')->unique(); // MAM001, MAM002...
            $table->string('prenom');
            $table->string('nom');
            $table->string('telephone');
            $table->boolean('peut_modifier_articles')->default(false);
            $table->boolean('peut_modifier_parameters')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gestionnaires');
    }
};