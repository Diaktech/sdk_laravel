<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('dettes_clients', function (Blueprint $table) {
            $table->id();
            
            // --- RELATIONS ---
            // Le client qui doit l'argent
            $table->foreignId('client_id')->constrained()->onDelete('cascade');
            // L'expédition (header) concernée
            $table->foreignId('evenement_id')->constrained('evenements')->onDelete('cascade');
            // La facture liée à cet impayé
            $table->foreignId('facture_id')->constrained('factures')->onDelete('cascade');
            
            // --- MONTANTS ---
            // Montant total impayé au moment de la création
            $table->decimal('montant_initial', 12, 2);
            // Solde restant (déduit à chaque remboursement)
            $table->decimal('montant_restant', 12, 2); 
            
            // --- ÉTATS & TYPES ---
            // actif = impayé, solde = payé, annule = geste commercial
            $table->enum('statut', ['actif', 'solde', 'annule'])->default('actif');
            // reliquat = défaut au départ, douane = frais douaniers, frais_supp = imprévus
            $table->enum('type', ['reliquat', 'douane', 'frais_supp'])->default('reliquat');
            
            // --- INFOS ---
            // Explication générée par le système (ex: Impayé total)
            $table->text('justification')->nullable();
            // L'utilisateur qui a validé la transaction
            $table->foreignId('cree_par')->constrained('users');
            // Date limite de paiement (facultatif)
            $table->date('date_echeance')->nullable();
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dette_clients');
    }
};
