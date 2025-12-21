<?php
// database/migrations/xxxx_add_client_id_required_to_destinataires_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('destinataires', function (Blueprint $table) {
            // client_id REQUIRED (pas nullable)
            $table->foreignId('client_id')
                    ->after('id')
                    ->constrained('clients')
                    ->onDelete('cascade');
                
            // INDEX pour améliorer les performances
            $table->index('client_id');
        });
    }

    public function down(): void
    {
        Schema::table('destinataires', function (Blueprint $table) {
            $table->dropForeign(['client_id']);
            $table->dropIndex(['client_id']);
            $table->dropColumn('client_id');
        });
    }
};