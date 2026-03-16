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
        Schema::create('factures', function (Blueprint $table) {
            $table->id();
            // Liens avec les autres tables
            $table->foreignId('evenement_id')->constrained('evenements')->onDelete('cascade');
            $table->foreignId('client_id')->constrained('clients')->onDelete('cascade');
            $table->foreignId('generee_par')->constrained('users');

            // Informations facture
            $table->string('numero_facture')->unique();
            $table->string('devise', 3)->default('EUR');
            $table->decimal('montant_remise', 12, 2);
            // Montants financiers
            $table->decimal('montant_total', 12, 2);
            $table->decimal('montant_entite', 12, 2);
            $table->decimal('montant_collecteur', 12, 2);
            
            // États de paiement
            $table->enum('statut_paiement', ['en_attente', 'partiel', 'paye', 'rembourse'])->default('en_attente');
            $table->boolean('est_entierement_payee')->default(false);
            
            // Documents et dates
            $table->string('chemin_pdf')->nullable();
            $table->timestamp('date_generation')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('factures');
    }
};
