<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('preuve_livraisons', function (Blueprint $table) {
            $table->id();
            
            // Référence
            $table->foreignId('livraison_id')->constrained('livraisons')->onDelete('cascade');
            
            // Type de preuve
            $table->enum('type_preuve', ['livraison', 'defaut', 'absence'])->default('livraison');
            
            // Fichiers
            $table->string('chemin_photo')->nullable();
            $table->string('chemin_signature')->nullable();
            
            // Informations
            $table->string('nom_destinataire')->nullable(); // Personne qui a signé
            $table->text('notes')->nullable();
            
            // Timestamps
            $table->timestamps();
            
            // Index
            $table->index('livraison_id');
            $table->index('type_preuve');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('preuve_livraisons');
    }
};