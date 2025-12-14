<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('entites', function (Blueprint $table) {
            $table->id();
            $table->string('nom'); // Terranga Services, etc.
            $table->string('code'); // TS, etc.
            $table->decimal('tarif_ts_par_defaut', 10, 2)->default(250.00);
            $table->decimal('tarif_kilo_par_defaut', 10, 2)->default(3.00);
            $table->decimal('majoration_domicile', 10, 2)->default(0.50);
            $table->string('email_contact')->nullable();
            $table->string('telephone_contact')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('entites');
    }
};