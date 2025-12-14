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
        Schema::create('collecteurs', function (Blueprint $table) {
            $table->id();
            $table->string('unique_id')->unique(); // COL001, COL002...
            $table->string('prenom');
            $table->string('nom');
            $table->string('telephone');
            
            // ADRESSE OPTION A
            $table->string('adresse_ligne1');
            $table->string('adresse_ligne2')->nullable();
            $table->string('code_postal', 10)->nullable();
            $table->unsignedBigInteger('ville_id')->nullable();
            $table->unsignedBigInteger('pays_id')->nullable();
            
            $table->unsignedBigInteger('entite_id')->nullable();
            $table->boolean('est_bloque')->default(false);
            $table->integer('niveau_blocage')->default(0); // 0:aucun, 1:pas nouvelles prises, 2:régularisation seulement, 3:blocage total
            $table->decimal('montant_total_genere', 10, 2)->default(0);
            $table->decimal('montant_total_regularise', 10, 2)->default(0);
            $table->decimal('montant_restant', 10, 2)->default(0);
            $table->timestamps();

            // Clés étrangères
            $table->foreign('ville_id')->references('id')->on('villes')->onDelete('set null');
            $table->foreign('pays_id')->references('id')->on('pays')->onDelete('set null');
            $table->foreign('entite_id')->references('id')->on('entites')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('collecteurs');
    }
};
