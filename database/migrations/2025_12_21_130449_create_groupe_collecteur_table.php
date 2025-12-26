<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('groupe_collecteur', function (Blueprint $table) {
            $table->id();
            $table->foreignId('groupe_id')->constrained('groupes')->cascadeOnDelete();
            $table->foreignId('collecteur_id')->constrained('collecteurs')->cascadeOnDelete();
            $table->boolean('est_propriétaire')->default(false);
            $table->timestamps();
            
            $table->unique(['groupe_id', 'collecteur_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('groupe_collecteur');
    }
};