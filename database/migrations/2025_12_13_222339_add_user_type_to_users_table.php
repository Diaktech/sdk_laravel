<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('user_type')->default('client'); // super_manager, manager, collecteur, livreur, client
            $table->unsignedBigInteger('userable_id')->nullable(); // Pour polymorphic
            $table->string('userable_type')->nullable(); // Pour polymorphic
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_login_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['user_type', 'userable_id', 'userable_type', 'is_active', 'last_login_at']);
        });
    }
};