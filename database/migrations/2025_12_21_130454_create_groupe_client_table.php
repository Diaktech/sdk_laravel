<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('groupe_client', function (Blueprint $table) {
            $table->id();
            $table->foreignId('groupe_id')->constrained('groupes')->cascadeOnDelete();
            $table->foreignId('client_id')->constrained('clients')->cascadeOnDelete();
            $table->foreignId('partage_par')->nullable()->constrained('collecteurs')->nullOnDelete();
            $table->foreignId('approuve_par')->nullable()->constrained('gestionnaires')->nullOnDelete();
            $table->timestamp('date_approbation')->nullable();
            $table->timestamps();
            
            $table->unique(['groupe_id', 'client_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('groupe_client');
    }
};