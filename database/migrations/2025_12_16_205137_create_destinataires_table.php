<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('destinataires', function (Blueprint $table) {
            $table->id();

            $table->foreignId('client_id')->constrained('clients')->onDelete('cascade');
            
            // Identifiant unique
            $table->string('code_unique')->unique(); // DES0001
            
            // Informations personnelles
            $table->string('prenom');
            $table->string('nom');
            $table->string('telephone');
            $table->text('adresse');
            
            // Localisation
            $table->foreignId('zone_id')->constrained('zones')->onDelete('restrict');
            $table->json('coordonnees_gps')->nullable(); // {lat: xx, lng: yy}
            $table->text('description_localisation')->nullable();
            
            // Créateur (polymorphique)
            $table->unsignedBigInteger('cree_par_id')->nullable();
            $table->string('cree_par_type')->nullable(); // 'App\Models\Client' ou 'App\Models\Collecteur'
            
            // Timestamps
            $table->timestamps();
            
            // Index
            $table->index(['cree_par_id', 'cree_par_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('destinataires');
    }
};