<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('livreurs', function (Blueprint $table) {
            $table->id();
            $table->string('unique_id')->unique(); // <-- AJOUTE CETTE LIGNE
            $table->string('prenom');
            $table->string('nom');
            $table->string('telephone');
            $table->string('type_vehicule')->nullable();
            $table->boolean('peut_choisir_zones')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('livreurs');
    }
};
