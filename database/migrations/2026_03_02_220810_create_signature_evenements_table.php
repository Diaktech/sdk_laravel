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
        Schema::create('signature_evenements', function (Blueprint $table) {
        $table->id();
        $table->foreignId('evenement_id')->constrained('evenements')->onDelete('cascade');
        
        // Type : 'client' ou 'collecteur'
        $table->string('type_signature'); 
        
        // Chemin du fichier PNG
        $table->string('chemin_signature'); 
        
        // Identifiant de celui qui a validé la signature (souvent le collecteur connecté)
        $table->foreignId('signe_par')->constrained('users');

        // ip_adresse (Optionnel, mais bien pour la preuve juridique)
        $table->string('ip_adresse')->nullable();

        $table->timestamps(); // Gère automatiquement le 'quand' (created_at)
            });
        }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('signature_evenements');
    }
};
