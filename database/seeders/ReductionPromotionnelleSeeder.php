<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ReductionPromotionnelle;
use App\Models\Client;
use App\Models\Entite;
use Carbon\Carbon;

class ReductionPromotionnelleSeeder extends Seeder
{
    public function run(): void
    {
        // Récupération de l'entité parente
        $entiteId = Entite::first()->id ?? 1;

        // Récupération d'un client spécifique pour les tests ciblés
        $client001 = Client::where('unique_id', 'CLT001')->first();

        // 1. Remise automatique de Bienvenue (Fixe)
        ReductionPromotionnelle::create([
            'code'                      => 'BIENVENUE5',
            'libelle'                   => 'Offre de Bienvenue',
            'type'                      => 'fixe',
            'valeur'                    => 5.00,
            'montant_minimum_commande'  => 50.00,
            'usage_max_total'           => 1000,
            'usage_max_par_client'      => 1,
            'is_automatique'            => 1,
            'is_active'                 => 1,
            'cumulable'                 => 0,
            'type_calcul_autorise'      => 'tous',
            'entite_id'                 => $entiteId,
        ]);

        // 2. Geste commercial Fidélité (Pourcentage avec Plafond)
        if ($client001) {
            ReductionPromotionnelle::create([
                'code'                      => 'FIDELITE15',
                'libelle'                   => 'Remise Fidélité Client',
                'type'                      => 'pourcentage',
                'valeur'                    => 15.00,
                'plafond_remise'            => 50.00,
                'usage_max_par_client'      => 3,
                'is_automatique'            => 1,
                'is_active'                 => 1,
                'client_id'                 => $client001->id,
                'entite_id'                 => $entiteId,
                'type_calcul_autorise'      => 'tous',
            ]);
        }

        // 3. Promo spéciale "Volume" (Uniquement si type_calcul = volume)
        ReductionPromotionnelle::create([
            'code'                      => 'SPECIAL_VOL',
            'libelle'                   => 'Bonus Envoi Volume',
            'type'                      => 'pourcentage',
            'valeur'                    => 10.00,
            'montant_minimum_commande'  => 300.00,
            'is_automatique'            => 1,
            'is_active'                 => 1,
            'type_calcul_autorise'      => 'volume',
            'entite_id'                 => $entiteId,
        ]);

        // 4. Code Manuel pour les Soldes (A taper dans l'input)
        ReductionPromotionnelle::create([
            'code'                      => 'SOLDES2026',
            'libelle'                   => 'Soldes 2026',
            'type'                      => 'pourcentage',
            'valeur'                    => 20.00,
            'date_debut'                => Carbon::now()->subDays(1),
            'date_fin'                  => Carbon::now()->addDays(15),
            'is_automatique'            => 0, // Manuel
            'is_active'                 => 1,
            'entite_id'                 => $entiteId,
            'type_calcul_autorise'      => 'tous',
        ]);

        // 5. Test : Promo déjà expirée
        ReductionPromotionnelle::create([
            'code'                      => 'OLD2025',
            'libelle'                   => 'Ancienne Promo',
            'type'                      => 'fixe',
            'valeur'                    => 100.00,
            'date_debut'                => Carbon::now()->subDays(60),
            'date_fin'                  => Carbon::now()->subDays(30),
            'is_automatique'            => 1,
            'is_active'                 => 1,
            'entite_id'                 => $entiteId,
            'type_calcul_autorise'      => 'tous',
        ]);

        // 6. Test : Quota total atteint
        ReductionPromotionnelle::create([
            'code'                      => 'FULL',
            'libelle'                   => 'Promo Victime de son succès',
            'type'                      => 'fixe',
            'valeur'                    => 10.00,
            'usage_max_total'           => 10,
            'nombre_utilisations_actuel' => 10,
            'is_automatique'            => 1,
            'is_active'                 => 1,
            'entite_id'                 => $entiteId,
            'type_calcul_autorise'      => 'tous',
        ]);
    }
}