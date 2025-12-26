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
        Schema::create('collecteurs', function (Blueprint $table) {
            $table->id();
            $table->string('unique_id')->unique(); // COL001, COL002...
            $table->string('prenom');
            $table->string('nom');
            $table->string('telephone');
            
            
            $table->string('adresse_ligne1');
            $table->string('adresse_ligne2')->nullable();
            $table->string('code_postal', 10)->nullable();
            $table->unsignedBigInteger('ville_id')->nullable();
            $table->unsignedBigInteger('pays_id')->nullable();
            
            $table->unsignedBigInteger('entite_id')->nullable();
            $table->boolean('est_bloque')->default(false);
            $table->integer('niveau_blocage')->default(0); // 0:aucun, 1:pas nouvelles prises, 2:régularisation seulement, 3:blocage total

            // 1. CE QUE LE COLLECTEUR DOIT RENDRE (Le coût pour lui)
            $table->decimal('tarif_volume_revient', 10, 2)->default(250.00); 
            $table->decimal('tarif_kilo_revient', 10, 2)->default(3.00);

            // 2. CE QUE LE CLIENT PAIE (La part fixée par le gestionnaire)
            $table->decimal('tarif_kilo_vente_defaut', 10, 2)->default(5.00); 

            //Majoration en fonction de la zone de récupération des colis, paramétré par le gestionnaire 
            $blueprint->decimal('majoration_domicile', 10, 2)->default(0.00);
            
            // 3. DROITS DU COLLECTEUR
            // 1 = Il saisit ce qu'il veut | 0 = On impose le 'tarif_kilo_vente_defaut'
            $table->boolean('peut_modifier_tarif_vente')->default(false);

            $table->decimal('montant_total_genere', 10, 2)->default(0);
            $table->decimal('montant_total_regularise', 10, 2)->default(0);
            $table->decimal('montant_restant', 10, 2)->default(0);
            $table->timestamps();

            // Clés étrangères
            $table->foreign('ville_id')->references('id')->on('villes')->onDelete('set null');
            $table->foreign('pays_id')->references('id')->on('pays')->onDelete('set null');
            $table->foreign('entite_id')->references('id')->on('entites')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('collecteurs');
    }
};
