<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('livraisons', function (Blueprint $table) {
            $table->id();
            
            // Références
            $table->foreignId('evenement_id')->constrained('evenements')->onDelete('cascade');
            $table->foreignId('livreur_id')->constrained('livreurs')->onDelete('restrict');
            $table->foreignId('zone_id')->constrained('zones')->onDelete('restrict');
            
            // Statuts
            $table->enum('statut_livraison', ['complet', 'partiel', 'en_attente'])->default('en_attente');
            $table->enum('statut', [
                'en_attente', 
                'en_livraison', 
                'livre', 
                'annule', 
                'litige', 
                'absent',
                'partiellement_livre'
            ])->default('en_attente');
            
            // Dates
            $table->date('date_prevue');
            $table->timestamp('date_debut')->nullable();
            $table->timestamp('date_livraison')->nullable();
            
            // Notes
            $table->text('notes')->nullable();
            
            // Timestamps
            $table->timestamps();
            
            // Index
            $table->index('evenement_id');
            $table->index('livreur_id');
            $table->index('statut');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('livraisons');
    }
};