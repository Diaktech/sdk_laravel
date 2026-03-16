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
        Schema::create('historique_statuts_evenements', function (Blueprint $table) {
            $table->id();
            // L'ID de l'événement
            $table->foreignId('evenement_id')->constrained('evenements')->onDelete('cascade');
            
            // Le statut (ex: 'en_attente', 'en_transit', 'livre')
            $table->string('statut');
            
            // L'ID de l'utilisateur (le collecteur ou le manager)
            $table->foreignId('modifie_par')->constrained('users');
            
            $table->timestamp('date_changement')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('historique_statuts_evenements');
    }
};
