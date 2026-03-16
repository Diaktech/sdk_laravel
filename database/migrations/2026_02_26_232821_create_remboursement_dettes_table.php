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
        Schema::create('remboursements_dettes', function (Blueprint $table) {
            $table->id();
            
            // La dette spécifique que l'on rembourse
            $table->foreignId('dette_client_id')->constrained('dettes_clients')->onDelete('cascade');
            // Le paiement global enregistré pour cette rentrée d'argent
            $table->foreignId('paiement_id')->constrained('paiements')->onDelete('cascade');
            
            // Somme versée lors de cette opération précise
            $table->decimal('montant_verse', 12, 2);
            // Lien vers le PDF de la quittance générée
            $table->string('chemin_recu_pdf')->nullable();
            
            // Le collecteur/gestionnaire qui a encaissé l'argent
            $table->foreignId('encaisse_par')->constrained('users');
            // Date effective du remboursement
            $table->timestamp('date_remboursement')->useCurrent();
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('remboursement_dettes');
    }
};
