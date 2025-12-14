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
        Schema::create('clients', function (Blueprint $table) {
            $table->id();
            $table->string('unique_id')->unique(); // CLT001, CLT002...
            $table->string('prenom');
            $table->string('nom');
            $table->string('telephone');
            
            // ADRESSE OPTION A
            $table->string('adresse_ligne1');
            $table->string('adresse_ligne2')->nullable();
            $table->string('code_postal', 10)->nullable();
            $table->unsignedBigInteger('ville_id')->nullable();
            $table->unsignedBigInteger('pays_id')->nullable();
            
            $table->unsignedBigInteger('collecteur_principal_id')->nullable();
            $table->decimal('total_du', 10, 2)->default(0);
            $table->decimal('total_paye', 10, 2)->default(0);
            $table->decimal('volume_total_envoye', 10, 3)->default(0);
            $table->timestamps();

            // Clés étrangères
            $table->foreign('ville_id')->references('id')->on('villes')->onDelete('set null');
            $table->foreign('pays_id')->references('id')->on('pays')->onDelete('set null');
            $table->foreign('collecteur_principal_id')->references('id')->on('collecteurs')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clients');
    }
};
