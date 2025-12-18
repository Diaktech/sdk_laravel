<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('departs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('entite_id');
            $table->date('date_depart');
            $table->decimal('volume_maximal', 10, 3); // en m³
            $table->decimal('poids_maximal', 10, 2)->nullable(); // en kg
            $table->enum('type_calcul', ['volume', 'poids'])->default('volume');
            $table->enum('statut', [
                'brouillon', 
                'ouvert', 
                'chargement', 
                'charge', 
                'transit', 
                'arrive', 
                'termine'
            ])->default('brouillon');
            $table->string('pays_destination');
            $table->decimal('volume_actuel', 10, 3)->default(0);
            $table->decimal('poids_actuel', 10, 2)->default(0);
            $table->string('lieu_depart');
            $table->string('lieu_arrivee');
            $table->integer('nombre_pieds')->nullable(); // Nombre de pieds conteneur
            $table->unsignedBigInteger('cree_par')->nullable(); // gestionnaire_id
            $table->unsignedBigInteger('ferme_par')->nullable(); // gestionnaire_id
            $table->timestamp('date_fermeture')->nullable();
            $table->timestamps();

            $table->foreign('entite_id')->references('id')->on('entites')->onDelete('cascade');
            $table->foreign('cree_par')->references('id')->on('gestionnaires')->onDelete('set null');
            $table->foreign('ferme_par')->references('id')->on('gestionnaires')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('departs');
    }
};