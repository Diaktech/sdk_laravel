<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Destinataire;
use App\Models\Client;
use App\Models\Collecteur;
use App\Models\Zone;
use App\Models\Ville;
use App\Models\Pays;

class DestinatairesSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info("📇 Création des clients pour tous les scénarios de test...");
        
        // Récupération des données nécessaires
        $col1 = Collecteur::where('unique_id', 'COL001')->first();
        $col2 = Collecteur::where('unique_id', 'COL002')->first() ?? $col1; // Fallback si COL002 n'existe pas
        
        $zones = Zone::all();
        $ville = Ville::first();
        $pays = Pays::where('code_iso', 'FRA')->first() ?? Pays::first();

        if (!$col1 || !$ville || !$pays) {
            $this->command->error("❌ Erreur : Collecteur, Ville ou Pays manquant. Vérifiez InitialDataSeeder.");
            return;
        }

        // --- SCÉNARIOS DE TEST ---

        // SCÉNARIO A : Clients Directs de COL001
        $this->createClient('CLT101', 'Jean', 'Dupont', $col1, $ville, $pays);
        $this->createClient('CLT102', 'Marie', 'Martin', $col1, $ville, $pays);

        // SCÉNARIO B : Clients rattachés à COL002
        $this->createClient('CLT201', 'Paul', 'Durand', $col2, $ville, $pays);
        $this->createClient('CLT202', 'Sophie', 'Leroy', $col2, $ville, $pays);

        // SCÉNARIO C : Client pour partage
        $this->createClient('CLT301', 'Thomas', 'Moreau', $col2, $ville, $pays);

        // --- CRÉATION DES DESTINATAIRES ---
        $clients = Client::all();
        if ($zones->isEmpty()) {
            $this->command->warn("⚠️ Aucune zone trouvée, les destinataires ne seront pas créés.");
            return;
        }

        foreach ($clients as $client) {
            foreach (range(1, 2) as $i) {
                Destinataire::updateOrCreate(
                    ['code_unique' => 'DES-' . $client->unique_id . '-' . $i],
                    [
                        'client_id' => $client->id,
                        'prenom' => 'Desti ' . $i,
                        'nom' => $client->nom,
                        'telephone' => '+22177' . rand(1000000, 9999999),
                        'adresse' => 'Quartier Test ' . $i . ', Dakar',
                        'zone_id' => $zones->random()->id,
                        'cree_par_id' => $client->collecteur_principal_id,
                        'cree_par_type' => 'App\Models\Collecteur'
                    ]
                );
            }
        }

        $this->command->info("✅ Clients et Destinataires prêts.");
    }

    /**
     * Helper pour créer un client proprement
     */
    private function createClient($uid, $prenom, $nom, $collecteur, $ville, $pays) 
    {
        return Client::updateOrCreate(
            ['unique_id' => $uid],
            [
                'prenom' => $prenom,
                'nom' => $nom,
                'telephone' => '+336' . rand(10000000, 99999999),
                'adresse_ligne1' => rand(1, 100) . ' Rue de Test',
                'code_postal' => '75000',
                'ville_id' => $ville->id,
                'pays_id' => $pays->id,
                'collecteur_principal_id' => $collecteur->id,
            ]
        );
    }
}