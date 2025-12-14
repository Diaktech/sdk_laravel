<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('zones', function (Blueprint $table) {
            $table->id();
            $table->integer('code')->unique(); // 99100, 99101, etc.
            $table->string('nom');
            $table->unsignedBigInteger('ville_id');
            $table->unsignedBigInteger('pays_id');
            $table->text('description')->nullable();
            $table->timestamps();

            $table->foreign('ville_id')->references('id')->on('villes')->onDelete('cascade');
            $table->foreign('pays_id')->references('id')->on('pays')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('zones');
    }
};