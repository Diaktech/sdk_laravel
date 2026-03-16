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
    Schema::create('reduction_utilisations', function (Blueprint $table) {
            $table->id();
            
            // Lien vers la promo
            $table->foreignId('reduction_id')
                ->constrained('reduction_promotionnelles')
                ->onDelete('cascade');

            // Lien vers le client qui a utilisé la promo
            $table->foreignId('client_id')
                ->constrained('clients')
                ->onDelete('cascade');

            // Lien vers l'événement (la collecte) concerné
            $table->foreignId('evenement_id')
                ->constrained('evenements')
                ->onDelete('cascade');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reduction_utilisations');
    }
};
