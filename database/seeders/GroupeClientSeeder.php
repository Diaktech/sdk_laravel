<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Groupe;
use App\Models\Client;
use App\Models\Collecteur;
use App\Models\Gestionnaire;
use App\Models\GroupeClient;

class GroupeClientSeeder extends Seeder
{
    public function run()
    {
        // 1. Récupération des acteurs (On utilise les IDs créés dans InitialDataSeeder)
        $manager = Gestionnaire::first(); // Récupère le manager MAN001
        $collecteur = Collecteur::where('unique_id', 'COL001')->first();
        
        // 2. Récupération des groupes (Assure-toi d'avoir lancé GroupesSeeder avant)
        $groupe1 = Groupe::where('code_unique', 'GRP0001')->first();
        $groupe2 = Groupe::where('code_unique', 'GRP0002')->first();
        
        // 3. Récupération des clients (On utilise ceux qu'on a vraiment créés)
        $client1 = Client::where('unique_id', 'CLT001')->first();
        $client2 = Client::where('unique_id', 'CLT002')->first();

        $this->command->info("🔗 Liaison des clients aux groupes...");

        // Liaison du Client 1 au Groupe 1 (Standard)
        if ($groupe1 && $client1) {
            GroupeClient::firstOrCreate(
                [
                    'groupe_id' => $groupe1->id,
                    'client_id' => $client1->id
                ],
                [
                    'partage_par' => null,
                    'approuve_par' => null,
                    'date_approbation' => null,
                ]
            );
            $this->command->info("   ✅ Client CLT001 lié au Groupe GRP0001");
        }

        // Liaison du Client 2 au Groupe 2 (Simulant un partage approuvé par le manager)
        if ($groupe2 && $client2 && $collecteur && $manager) {
            GroupeClient::firstOrCreate(
                [
                    'groupe_id' => $groupe2->id,
                    'client_id' => $client2->id
                ],
                [
                    'partage_par' => $collecteur->id,
                    'approuve_par' => $manager->id,
                    'date_approbation' => now(),
                ]
            );
            $this->command->info("   ✅ Client CLT002 lié au Groupe GRP0002 (Partage approuvé)");
        }
    }
}