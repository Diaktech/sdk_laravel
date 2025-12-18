<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('item_evenements', function (Blueprint $table) {
            $table->id();
            
            // Références
            $table->foreignId('evenement_id')->constrained('evenements')->onDelete('cascade');
            $table->foreignId('article_id')->constrained('articles')->onDelete('restrict');
            
            // Quantité et mesures
            $table->integer('quantite')->default(1);
            $table->decimal('longueur', 8, 2)->nullable(); // cm
            $table->decimal('largeur', 8, 2)->nullable(); // cm
            $table->decimal('hauteur', 8, 2)->nullable(); // cm
            $table->decimal('poids', 10, 2)->nullable(); // kg
            
            // Calculs
            $table->decimal('volume_calcule', 10, 3)->default(0); // m³
            $table->decimal('prix_par_m3', 10, 2)->nullable();
            $table->decimal('prix_par_kilo', 10, 2)->nullable();
            
            // État et observations
            $table->enum('etat', ['bon_etat', 'defaut'])->default('bon_etat');
            $table->text('notes_defaut')->nullable();
            $table->string('photo_defaut_chemin')->nullable();
            $table->text('notes')->nullable();
            
            // Timestamps
            $table->timestamps();
            
            // Index
            $table->index('evenement_id');
            $table->index('article_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('item_evenements');
    }
};