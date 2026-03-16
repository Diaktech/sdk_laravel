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
        Schema::create('reduction_promotionnelles', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique(); // Le code à saisir (ex: SOLDES2026)
            $table->string('libelle'); // Nom affiché (ex: Remise d'hiver)
            
            // Type et Valeur
            $table->enum('type', ['pourcentage', 'fixe'])->default('pourcentage');
            $table->decimal('valeur', 10, 2); 
            $table->decimal('plafond_remise', 10, 2)->nullable(); // Max de remise pour les %
            
            // Conditions
            $table->decimal('montant_minimum_commande', 10, 2)->default(0);
            $table->integer('usage_max_total')->nullable(); // Ex: Limité aux 100 premiers
            $table->integer('usage_max_par_client')->default(1);
            $table->integer('nombre_utilisations_actuel')->default(0); // Compteur
            
            // Dates
            $table->timestamp('date_debut')->nullable();
            $table->timestamp('date_fin')->nullable();
            
            // Flags
            $table->boolean('cumulable')->default(false);
            $table->boolean('is_active')->default(true);
            $table->boolean('is_automatique')->default(false); // Si vrai, pas besoin de saisir de code
            $table->enum('type_calcul_autorise', ['volume', 'poids', 'tous'])->default('tous');

            // Liaisons
            $table->foreignId('entite_id')->constrained('entites')->onDelete('cascade');
            $table->foreignId('client_id')->nullable()->constrained('clients')->onDelete('set null');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reduction_promotionnelles');
    }
};
