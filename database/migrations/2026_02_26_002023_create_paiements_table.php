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
        Schema::create('paiements', function (Blueprint $table) {
            $table->id();
            
            // --- IDENTIFICATION ---
            // Numéro de reçu unique pour le client (ex: REC-2026-0001).
            $table->string('reference_interne')->unique();
            
            // --- RELATIONS ---
            $table->foreignId('facture_id')->constrained('factures')->onDelete('cascade');
            $table->foreignId('client_id')->constrained('clients');
            // L'agent qui a physiquement encaissé la somme (responsable de sa caisse).
            $table->foreignId('collecteur_id')->constrained('users'); 
            
            // --- DÉTAILS DU FLUX ---
            // Montant encaissé lors de cette opération précise.
            $table->decimal('montant', 12, 2);
            $table->string('devise', 3)->default('EUR');
            // Canal : especes, virement, mobile_money, etc.
            $table->enum('moyen_paiement', ['especes', 'carte', 'virement', 'mobile_money', 'cheque'])->default('especes');
            // Type : 'acompte' (lors de l'expédition) ou 'solde_dette' (paiement d'un reliquat plus tard).
            $table->enum('type_paiement', ['acompte', 'solde_dette'])->default('acompte');
            
            // --- TRACABILITÉ & NOTES ---
            // ID de transaction externe (ex: Référence de virement ou ID Orange Money).
            $table->string('reference_transaction')->nullable(); 
            // Permet d'annuler un paiement en cas d'erreur de saisie sans supprimer la ligne (audit).
            $table->enum('statut', ['valide', 'annule'])->default('valide');
            $table->text('notes')->nullable();
            
            $table->timestamp('date_enregistrement')->useCurrent();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('paiements');
    }
};
