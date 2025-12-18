<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // ========== TOUJOURS exécuté ==========
        $this->call(DonneesReferenceSeeder::class);

        // ========== SEULEMENT en local/dev ==========
        if (app()->environment('local', 'development')) {
            $this->call(PaysVillesSeeder::class);
            $this->call(InitialDataSeeder::class);
        }
    }
}