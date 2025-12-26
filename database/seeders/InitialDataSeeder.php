<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Entite;
use App\Models\Pays;
use App\Models\Ville;
use App\Models\Zone;
use App\Models\SuperGestionnaire;
use App\Models\Gestionnaire;
use App\Models\Collecteur;
use App\Models\Livreur;
use App\Models\Client;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class InitialDataSeeder extends Seeder
{
    public function run(): void
    {
        // ========== 1. RÉCUPÉRATION DES DONNÉES GÉOGRAPHIQUES ==========
        // On utilise updateOrCreate pour éviter les doublons si on relance le seeder
        $senegal = Pays::updateOrCreate(['code_iso' => 'SEN'], ['nom' => 'Sénégal']);
        $france = Pays::updateOrCreate(['code_iso' => 'FRA'], ['nom' => 'France']);

        $dakar = Ville::updateOrCreate(
            ['nom' => 'Dakar', 'pays_id' => $senegal->id]
        );
        $paris = Ville::updateOrCreate(
            ['nom' => 'Paris', 'pays_id' => $france->id]
        );

        // ========== 2. CRÉATION DE LA ZONE DAKAR ==========
        $zoneDakar = Zone::updateOrCreate(
            ['code' => '99100'],
            [
                'nom' => 'Plateau Dakar',
                'ville_id' => $dakar->id,
                'pays_id' => $senegal->id,
                'description' => 'Zone centrale de Dakar',
            ]
        );

        // ========== 3. CRÉATION DE L'ENTITÉ TS ==========
        $entiteTS = Entite::updateOrCreate(
            ['code' => 'TS'],
            [
                'nom' => 'Terranga Services',
                'majoration_domicile' => 0.50,
                'email_contact' => 'contact@terrangaservices.com',
                'telephone_contact' => '+221 33 123 45 67',
            ]
        );

        // ========== 4. CRÉATION DU SUPER GESTIONNAIRE ==========
        $superGest = SuperGestionnaire::updateOrCreate(
            ['unique_id' => 'SM001'],
            [
                'prenom' => 'Admin',
                'nom' => 'System',
                'telephone' => '+221 77 123 45 67',
                'droits_access_speciaux' => json_encode(['all']),
            ]
        );

        User::updateOrCreate(
            ['email' => 'super@sdtransit.com'],
            [
                'name' => 'Admin System',
                'password' => Hash::make('password'),
                'user_type' => 'super_gestionnaire',
                'userable_id' => $superGest->id,
                'userable_type' => SuperGestionnaire::class,
                'is_active' => true,
            ]
        );

        // ========== 5. CRÉATION DU GESTIONNAIRE ==========
        $gest = Gestionnaire::updateOrCreate(
            ['unique_id' => 'MAN001'],
            [
                'prenom' => 'Manager',
                'nom' => 'Test',
                'telephone' => '+221 77 234 56 78',
                'peut_modifier_articles' => true,
                'peut_modifier_parameters' => true,
            ]
        );

        User::updateOrCreate(
            ['email' => 'manager@sdtransit.com'],
            [
                'name' => 'Manager Test',
                'password' => Hash::make('password'),
                'user_type' => 'gestionnaire',
                'userable_id' => $gest->id,
                'userable_type' => Gestionnaire::class,
                'is_active' => true,
            ]
        );

        // ========== 6. CRÉATION DU COLLECTEUR (LYON) ==========
        $collecteur = Collecteur::updateOrCreate(
            ['unique_id' => 'COL001'],
            [
                'prenom' => 'Pape',
                'nom' => 'Diop',
                'telephone' => '+221 77 345 67 89',
                'adresse_ligne1' => '123 Rue de la Collecte',
                'ville_id' => $paris->id, // Il est physiquement en France
                'pays_id' => $france->id,
                'entite_id' => $entiteTS->id,
                
                // Nouveaux champs financiers migrés
                'tarif_volume_revient' => 280.00,
                'tarif_kilo_revient' => 3.50,
                'tarif_kilo_vente_defaut' => 5.50,
                'peut_modifier_tarif_vente' => true,
                
                'est_bloque' => false,
                'montant_total_genere' => 0,
                'montant_total_regularise' => 0,
                'montant_restant' => 0,
            ]
        );

        User::updateOrCreate(
            ['email' => 'collecteur@sdtransit.com'],
            [
                'name' => 'Pape Diop',
                'password' => Hash::make('password'),
                'user_type' => 'collecteur',
                'userable_id' => $collecteur->id,
                'userable_type' => Collecteur::class,
                'is_active' => true,
            ]
        );

        // ========== 7. CRÉATION DU CLIENT ==========
        $client = Client::updateOrCreate(
            ['unique_id' => 'CLT001'],
            [
                'prenom' => 'Aminata',
                'nom' => 'Ndiaye',
                'telephone' => '+221 77 456 78 90',
                'adresse_ligne1' => '456 Avenue des Clients',
                'code_postal' => '75000',
                'ville_id' => $paris->id,
                'pays_id' => $france->id,
                'collecteur_principal_id' => $collecteur->id,
                'total_du' => 0,
                'total_paye' => 0,
                'volume_total_envoye' => 0,
            ]
        );

        User::updateOrCreate(
            ['email' => 'client@sdtransit.com'],
            [
                'name' => 'Aminata Ndiaye',
                'password' => Hash::make('password'),
                'user_type' => 'client',
                'userable_id' => $client->id,
                'userable_type' => Client::class,
                'is_active' => true,
            ]
        );

        // ========== 8. CRÉATION DU LIVREUR ==========
        $livreur = Livreur::updateOrCreate(
            ['unique_id' => 'DLV001'],
            [
                'prenom' => 'Ibrahima',
                'nom' => 'Sarr',
                'telephone' => '+221 77 567 89 01',
                'type_vehicule' => 'Moto',
                'peut_choisir_zones' => true,
            ]
        );

        User::updateOrCreate(
            ['email' => 'livreur@sdtransit.com'],
            [
                'name' => 'Ibrahima Sarr',
                'password' => Hash::make('password'),
                'user_type' => 'livreur',
                'userable_id' => $livreur->id,
                'userable_type' => Livreur::class,
                'is_active' => true,
            ]
        );

        // ========== 9. ASSOCIATION LIVREUR - ZONE ==========
        if ($livreur && $zoneDakar) {
            $livreur->zones()->syncWithoutDetaching([$zoneDakar->id]);
        }
        
        $this->command->info('✅ InitialDataSeeder mis à jour et exécuté !');
    }
}