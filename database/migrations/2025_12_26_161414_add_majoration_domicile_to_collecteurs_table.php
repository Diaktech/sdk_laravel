<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('collecteurs', function (Blueprint $blueprint) {
            // On ajoute le champ avec une valeur par défaut de 0.00
            $blueprint->decimal('majoration_domicile', 10, 2)->default(0.00)->after('tarif_kilo_vente_defaut');
        });
    }

    public function down(): void
    {
        Schema::table('collecteurs', function (Blueprint $blueprint) {
            $blueprint->dropColumn('majoration_domicile');
        });
    }
};