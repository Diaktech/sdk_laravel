<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('item_evenements', function (Blueprint $table) {
            $table->id();
            
            // Références
            $table->foreignId('evenement_id')->constrained('evenements')->onDelete('cascade');
            $table->foreignId('article_id')->constrained('articles')->onDelete('restrict');
            
            // Quantité et mesures
            $table->integer('quantite')->default(1);
            $table->decimal('longueur', 8, 2)->nullable();
            $table->decimal('largeur', 8, 2)->nullable();
            $table->decimal('hauteur', 8, 2)->nullable();
            $table->decimal('poids', 10, 2)->nullable(); // Rappel : c'est le poids total de la ligne
            $table->decimal('volume_unitaire', 10, 4)->default(0); // Augmenté à 4 décimales pour la précision

            // --- SECTION FINANCES DÉTAILLÉE ---
            // 1. Ce que le client paie pour cet article (Total)
            $table->decimal('prix_total_client', 10, 2)->default(0); 

            // 2. Ce que l'entité (le transporteur) prend sur cet article
            // (poids * tarif_kilo_revient) OU (volume * tarif_volume_revient)
            $table->decimal('part_entite_item', 10, 2)->default(0);

            // 3. Ce que le collecteur gagne sur cet article (Marge)
            // (prix_total_client - part_entite_item)
            $table->decimal('commission_col_item', 10, 2)->default(0);
            // ----------------------------------

            // Valeur CAF
            $table->decimal('valeur_caf', 10, 2)->nullable()->default(0.00);
            
            // État et observations
            $table->enum('etat', ['bon_etat', 'defaut'])->default('bon_etat');
            $table->text('notes_defaut')->nullable();
            $table->string('photo_defaut_chemin')->nullable();
            $table->text('notes')->nullable();
            
            $table->timestamps();
            
            // Note : foreignId crée déjà les index, mais tu peux les laisser si tu préfères
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('item_evenements');
    }
};