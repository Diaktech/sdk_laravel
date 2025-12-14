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
        $senegal = Pays::where('code_iso', 'SEN')->first();
        $france = Pays::where('code_iso', 'FRA')->first();

        // Fallback si pas trouvé (pour tests)
        if (!$senegal) {
            $senegal = Pays::create(['nom' => 'Sénégal', 'code_iso' => 'SEN']);
        }
        if (!$france) {
            $france = Pays::create(['nom' => 'France', 'code_iso' => 'FRA']);
        }

        $dakar = Ville::where('nom', 'Dakar')->where('pays_id', $senegal->id)->first();
        $paris = Ville::where('nom', 'Paris')->where('pays_id', $france->id)->first();

        if (!$dakar) {
            $dakar = Ville::create(['nom' => 'Dakar', 'pays_id' => $senegal->id]);
        }
        if (!$paris) {
            $paris = Ville::create(['nom' => 'Paris', 'pays_id' => $france->id]);
        }

        // ========== 2. CRÉATION DE LA ZONE DAKAR ==========
        $zoneDakar = Zone::firstOrCreate(
            ['code' => 99100],
            [
                'nom' => 'Plateau Dakar',
                'ville_id' => $dakar->id,
                'pays_id' => $senegal->id,
                'description' => 'Zone centrale de Dakar',
            ]
        );

        // ========== 3. CRÉATION DE L'ENTITÉ TS ==========
        $entiteTS = Entite::firstOrCreate(
            ['code' => 'TS'],
            [
                'nom' => 'Terranga Services',
                'tarif_ts_par_defaut' => 250.00,
                'tarif_kilo_par_defaut' => 3.00,
                'majoration_domicile' => 0.50,
                'email_contact' => 'contact@terrangaservices.com',
                'telephone_contact' => '+221 33 123 45 67',
            ]
        );

        // ========== 4. CRÉATION DU SUPER GESTIONNAIRE ==========
        $superGest = SuperGestionnaire::firstOrCreate(
            ['unique_id' => 'SM001'],
            [
                'prenom' => 'Admin',
                'nom' => 'System',
                'telephone' => '+221 77 123 45 67',
                'droits_access_speciaux' => json_encode(['all']),
            ]
        );

        User::firstOrCreate(
            ['email' => 'super@sdtransit.com'],
            [
                'name' => 'Admin System',
                'email' => 'super@sdtransit.com',
                'password' => Hash::make('password'),
                'user_type' => 'super_gestionnaire',
                'userable_id' => $superGest->id,
                'userable_type' => SuperGestionnaire::class,
                'is_active' => true,
            ]
        );

        // ========== 5. CRÉATION DU GESTIONNAIRE ==========
        $gest = Gestionnaire::firstOrCreate(
            ['unique_id' => 'MAM001'],
            [
                'prenom' => 'Manager',
                'nom' => 'Test',
                'telephone' => '+221 77 234 56 78',
                'peut_modifier_articles' => true,
                'peut_modifier_parameters' => true,
            ]
        );

        User::firstOrCreate(
            ['email' => 'manager@sdtransit.com'],
            [
                'name' => 'Manager Test',
                'email' => 'manager@sdtransit.com',
                'password' => Hash::make('password'),
                'user_type' => 'gestionnaire',
                'userable_id' => $gest->id,
                'userable_type' => Gestionnaire::class,
                'is_active' => true,
            ]
        );

        // ========== 6. CRÉATION DU COLLECTEUR ==========
        $collecteur = Collecteur::firstOrCreate(
            ['unique_id' => 'COL001'],
            [
                'prenom' => 'Pape',
                'nom' => 'Diop',
                'telephone' => '+221 77 345 67 89',
                'adresse_ligne1' => '123 Rue de la Collecte',
                'adresse_ligne2' => 'Appartement 4',
                'code_postal' => '12500',
                'ville_id' => $dakar->id,
                'pays_id' => $senegal->id,
                'entite_id' => $entiteTS->id,
                'est_bloque' => false,
                'niveau_blocage' => 0,
                'montant_total_genere' => 0,
                'montant_total_regularise' => 0,
                'montant_restant' => 0,
            ]
        );

        User::firstOrCreate(
            ['email' => 'collecteur@sdtransit.com'],
            [
                'name' => 'Pape Diop',
                'email' => 'collecteur@sdtransit.com',
                'password' => Hash::make('password'),
                'user_type' => 'collecteur',
                'userable_id' => $collecteur->id,
                'userable_type' => Collecteur::class,
                'is_active' => true,
            ]
        );

        // ========== 7. CRÉATION DU CLIENT ==========
        $client = Client::firstOrCreate(
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

        User::firstOrCreate(
            ['email' => 'client@sdtransit.com'],
            [
                'name' => 'Aminata Ndiaye',
                'email' => 'client@sdtransit.com',
                'password' => Hash::make('password'),
                'user_type' => 'client',
                'userable_id' => $client->id,
                'userable_type' => Client::class,
                'is_active' => true,
            ]
        );

        // ========== 8. CRÉATION DU LIVREUR ==========
        $livreur = Livreur::firstOrCreate(
            ['unique_id' => 'DLV001'],
            [
                'prenom' => 'Ibrahima',
                'nom' => 'Sarr',
                'telephone' => '+221 77 567 89 01',
                'type_vehicule' => 'Moto',
                'peut_choisir_zones' => true,
            ]
        );

        User::firstOrCreate(
            ['email' => 'livreur@sdtransit.com'],
            [
                'name' => 'Ibrahima Sarr',
                'email' => 'livreur@sdtransit.com',
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

        // ========== 10. MESSAGE DE CONFIRMATION ==========
        $this->command->info('✅ Données initiales créées avec succès !');
        $this->command->info('Super Admin: super@sdtransit.com / password');
        $this->command->info('Manager: manager@sdtransit.com / password');
        $this->command->info('Collecteur: collecteur@sdtransit.com / password');
        $this->command->info('Client: client@sdtransit.com / password');
        $this->command->info('Livreur: livreur@sdtransit.com / password');
    }
}