<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('articles', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('famille_id')->nullable();
            $table->string('reference_article')->unique();
            $table->string('libelle');
            $table->string('positions_tarifaires')->nullable();
            $table->string('origine')->nullable();
            $table->decimal('valeur_caf', 10, 2)->nullable();
            $table->decimal('vc_deduit', 10, 2)->nullable();
            $table->text('note')->nullable();
            $table->string('conditionnement')->nullable();
            $table->decimal('longueur', 8, 2)->nullable();
            $table->decimal('largeur', 8, 2)->nullable();
            $table->decimal('hauteur', 8, 2)->nullable();
            $table->decimal('volume_article', 10, 4)->nullable();
            $table->decimal('poids', 8, 3)->nullable();
            $table->boolean('mesures_fixes')->default(false);
            $table->boolean('est_pris_en_charge')->default(false);
            $table->unsignedBigInteger('cree_par')->nullable(); // super_gestionnaire_id
            $table->timestamps();

            $table->foreign('famille_id')->references('id')->on('familles')->onDelete('set null');
            $table->foreign('cree_par')->references('id')->on('super_gestionnaires')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('articles');
    }
};