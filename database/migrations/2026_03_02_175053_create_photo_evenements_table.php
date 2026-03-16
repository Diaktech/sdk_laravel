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
        Schema::create('photo_evenements', function (Blueprint $table) {
            $table->id();
                
                // Lien vers l'événement global
                $table->foreignId('evenement_id')->constrained('evenements')->onDelete('cascade');
                
                // Lien vers l'item précis (nullable au cas où tu prendrais une photo globale plus tard)
                $table->foreignId('item_evenement_id')->nullable()->constrained('item_evenements')->onDelete('cascade');
                
                $table->string('type_photo')->default('defaut'); // 'generale' ou 'defaut'
                $table->string('chemin_photo'); // Le chemin du fichier
                
                // Qui a pris la photo
                $table->foreignId('prise_par')->constrained('users'); 
                
                $table->timestamps();
            });
        }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('photo_evenements');
    }
};
