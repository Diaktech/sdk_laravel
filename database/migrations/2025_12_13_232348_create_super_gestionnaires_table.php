<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('super_gestionnaires', function (Blueprint $table) {
            $table->id();
            $table->string('unique_id')->unique(); // SM001, SM002...
            $table->string('prenom');
            $table->string('nom');
            $table->string('telephone');
            $table->json('droits_access_speciaux')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('super_gestionnaires');
    }
};