<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Famille;

class DonneesReferenceSeeder extends Seeder
{
    public function run(): void
    {
        $familles = [
            'FRIPERIE',
            'MARCHANDISES DIVERS',
            'MATERIELS DE COIFFURE', 
            'MATERIELS ELECTROMENAGERS',
            'MATERIELS INFORMATIQUES',
            'PRODUIT ALIMENTAIRE',
            'QUINCAILLERIE',
            'SANITAIRES'
        ];

        foreach ($familles as $nom) {
            Famille::firstOrCreate(['nom' => $nom]);
        }

        $this->command->info('✅ 8 familles d\'articles créées.');
    }
}