<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('villes', function (Blueprint $table) {
            $table->id();
            $table->string('nom'); // Nom de la ville
            $table->unsignedBigInteger('pays_id');
            $table->timestamps();

            $table->foreign('pays_id')->references('id')->on('pays')->onDelete('cascade');
        });

        // Pas d'insertion automatique, on les ajoutera via seeds plus tard
    }

    public function down(): void
    {
        Schema::dropIfExists('villes');
    }
};