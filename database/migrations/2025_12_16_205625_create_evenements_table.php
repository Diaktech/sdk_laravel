<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('evenements', function (Blueprint $table) {
            $table->id();
            
            // Références principales
            $table->foreignId('depart_id')->constrained('departs')->onDelete('restrict');
            $table->foreignId('client_id')->constrained('clients')->onDelete('restrict');
            $table->foreignId('collecteur_id')->constrained('collecteurs')->onDelete('restrict');
            $table->foreignId('destinataire_id')->nullable()->constrained('destinataires')->onDelete('set null');
            
            // Métadonnées
            $table->string('code_unique')->unique(); // EXP2025001
            $table->enum('type_prise_charge', ['depot', 'domicile'])->default('depot');
            $table->enum('statut', ['en_attente', 'valide', 'attente_correction', 'annule', 'termine'])->default('en_attente');
            $table->enum('priorite', ['basse', 'normale', 'haute', 'urgente'])->default('normale');
            
            // Calculs et totaux
            $table->decimal('volume_total', 10, 3)->default(0); // m³
            $table->decimal('poids_total', 10, 2)->default(0); // kg
            $table->decimal('montant_total', 12, 2)->default(0);
            $table->decimal('montant_ts', 12, 2)->default(0);
            $table->decimal('montant_collecteur', 12, 2)->default(0);
            $table->decimal('prix_kilo', 8, 2)->nullable(); // 3.00 ou 3.50
            $table->decimal('prix_m3', 10, 2)->nullable(); // montant_total / volume_total
            
            // Validation et gestion
            $table->boolean('facture_generee')->default(false);
            $table->boolean('necessite_validation')->default(false);
            $table->foreignId('valide_par_id')->nullable()->constrained('gestionnaires')->onDelete('set null');
            $table->timestamp('date_validation')->nullable();
            
            // Timestamps
            $table->timestamps();
            $table->softDeletes(); // Pour archivage
            
            // Index
            $table->index('code_unique');
            $table->index('statut');
            $table->index('depart_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('evenements');
    }
};