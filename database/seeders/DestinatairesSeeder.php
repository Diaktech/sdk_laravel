<?php
// database/seeders/DestinatairesSeeder.php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
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
        echo "📇 Création des clients et destinataires de test...\n";
        
        // Récupérer ou créer le collecteur
        $collecteur = Collecteur::where('unique_id', 'COL001')->first();
        if (!$collecteur) {
            echo "❌ Collecteur COL001 non trouvé\n";
            return;
        }
        
        // Récupérer des zones
        $zones = Zone::take(3)->get();
        if ($zones->isEmpty()) {
            echo "❌ Aucune zone disponible\n";
            return;
        }
        
        // Récupérer une ville et un pays
        $ville = Ville::first();
        $pays = Pays::first();
        
        // ==================== CRÉATION DES CLIENTS ====================
        
        echo "\n👥 Création des clients...\n";
        
        // Client 1 (déjà existant)
        $client1 = Client::where('unique_id', 'CLT001')->first();
        echo "✅ Client 1 : {$client1->unique_id} - {$client1->prenom} {$client1->nom}\n";
        
        // Client 2 - CRÉATION AVEC BONNE STRUCTURE
        $client2 = Client::firstOrCreate(
            ['unique_id' => 'CLT002'],
            [
                'prenom' => 'Marie',
                'nom' => 'Fall',
                'telephone' => '+221772345678',
                'adresse_ligne1' => 'Rue des Almadies 12', // CHANGÉ
                'adresse_ligne2' => null,
                'code_postal' => '75001',
                'ville_id' => $ville->id ?? 5, // CHANGÉ
                'pays_id' => $pays->id ?? 4,   // CHANGÉ
                'collecteur_principal_id' => $collecteur->id,
                'total_du' => 150.00,
                'total_paye' => 100.00,
                'volume_total_envoye' => 2.500,
            ]
        );
        echo "✅ Client 2 : {$client2->unique_id} - {$client2->prenom} {$client2->nom}\n";
        
        // Client 3
        $client3 = Client::firstOrCreate(
            ['unique_id' => 'CLT003'],
            [
                'prenom' => 'Ibrahima',
                'nom' => 'Ndiaye',
                'telephone' => '+221763456789',
                'adresse_ligne1' => 'Avenue Pikine 45', // CHANGÉ
                'adresse_ligne2' => 'Résidence Salam',
                'code_postal' => '75002',
                'ville_id' => $ville->id ?? 5, // CHANGÉ
                'pays_id' => $pays->id ?? 4,   // CHANGÉ
                'collecteur_principal_id' => $collecteur->id,
                'total_du' => 0,
                'total_paye' => 500.00,
                'volume_total_envoye' => 8.200,
            ]
        );
        echo "✅ Client 3 : {$client3->unique_id} - {$client3->prenom} {$client3->nom}\n";
        
        // ==================== CRÉATION DES DESTINATAIRES ====================
        
        echo "\n📬 Création des destinataires...\n";
        
        // Destinataires pour Client 1 (3 destinataires)
        $dest1_1 = Destinataire::create([
            'code_unique' => 'DES' . str_pad(Destinataire::count() + 1, 4, '0', STR_PAD_LEFT),
            'client_id' => $client1->id, // REQUIRED
            'prenom' => 'Moussa',
            'nom' => 'Diop',
            'telephone' => '+221781234567',
            'adresse' => 'Rue 10, Point E, Dakar',
            'zone_id' => $zones[0]->id,
            'coordonnees_gps' => json_encode(['lat' => 14.7167, 'lng' => -17.4677]),
            'description_localisation' => 'Maison bleue, portail vert',
            'cree_par_id' => $collecteur->id,
            'cree_par_type' => 'App\Models\Collecteur'
        ]);
        echo "✅ Destinataire 1.1 pour {$client1->unique_id} : {$dest1_1->prenom} {$dest1_1->nom}\n";
        
        $dest1_2 = Destinataire::create([
            'code_unique' => 'DES' . str_pad(Destinataire::count() + 1, 4, '0', STR_PAD_LEFT),
            'client_id' => $client1->id, // REQUIRED
            'prenom' => 'Aminata',
            'nom' => 'Sow',
            'telephone' => '+221772345678',
            'adresse' => 'Cité Keur Gorgui, Dakar',
            'zone_id' => $zones[1]->id ?? $zones[0]->id,
            'coordonnees_gps' => json_encode(['lat' => 14.7500, 'lng' => -17.4333]),
            'description_localisation' => 'Appartement 3ème étage',
            'cree_par_id' => $collecteur->id,
            'cree_par_type' => 'App\Models\Collecteur'
        ]);
        echo "✅ Destinataire 1.2 pour {$client1->unique_id} : {$dest1_2->prenom} {$dest1_2->nom}\n";
        
        // Destinataires pour Client 2 (2 destinataires)
        $dest2_1 = Destinataire::create([
            'code_unique' => 'DES' . str_pad(Destinataire::count() + 1, 4, '0', STR_PAD_LEFT),
            'client_id' => $client2->id, // REQUIRED
            'prenom' => 'Fatou',
            'nom' => 'Bâ',
            'telephone' => '+221773456789',
            'adresse' => 'Mermoz, Dakar',
            'zone_id' => $zones[0]->id,
            'coordonnees_gps' => json_encode(['lat' => 14.7000, 'lng' => -17.4500]),
            'description_localisation' => 'Bureau au rez-de-chaussée',
            'cree_par_id' => $collecteur->id,
            'cree_par_type' => 'App\Models\Collecteur'
        ]);
        echo "✅ Destinataire 2.1 pour {$client2->unique_id} : {$dest2_1->prenom} {$dest2_1->nom}\n";
        
        // Destinataires pour Client 3 (1 destinataire)
        $dest3_1 = Destinataire::create([
            'code_unique' => 'DES' . str_pad(Destinataire::count() + 1, 4, '0', STR_PAD_LEFT),
            'client_id' => $client3->id, // REQUIRED
            'prenom' => 'Omar',
            'nom' => 'Sy',
            'telephone' => '+221764567890',
            'adresse' => 'Guédiawaye, Dakar',
            'zone_id' => $zones[2]->id ?? $zones[0]->id,
            'coordonnees_gps' => json_encode(['lat' => 14.7667, 'lng' => -17.4000]),
            'description_localisation' => 'Magasin côté rue',
            'cree_par_id' => $collecteur->id,
            'cree_par_type' => 'App\Models\Collecteur'
        ]);
        echo "✅ Destinataire 3.1 pour {$client3->unique_id} : {$dest3_1->prenom} {$dest3_1->nom}\n";
        
        echo "\n🎉 Récapitulatif :\n";
        echo "• 3 clients existants (CLT001, CLT002, CLT003)\n";
        echo "• 4 destinataires créés (liés chacun à un client)\n";
        echo "• Tous liés au collecteur {$collecteur->unique_id}\n";
    }
}