<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('affectations_zones', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('livreur_id');
            $table->unsignedBigInteger('zone_id');
            $table->unsignedBigInteger('affecte_par')->nullable(); // gestionnaire_id
            $table->date('date_affectation')->nullable();
            $table->timestamps();

            $table->foreign('livreur_id')->references('id')->on('livreurs')->onDelete('cascade');
            $table->foreign('zone_id')->references('id')->on('zones')->onDelete('cascade');
            $table->foreign('affecte_par')->references('id')->on('gestionnaires')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('affectations_zones');
    }
};