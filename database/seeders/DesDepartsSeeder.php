<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Depart;
use App\Models\Entite;
use App\Models\Gestionnaire;

class DesDepartsSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info("🔧 Génération des scénarios de départs (Calcul Poids & Volume)...");

        $entiteTS = Entite::where('code', 'TS')->first();
        // On cherche une deuxième entité pour les tests de restriction
        $entiteAutre = Entite::where('id', '!=', $entiteTS->id)->first() ?? $entiteTS;
        $manager = Gestionnaire::first();

        $departs = [
            // 1. DÉPART STANDARD - CALCUL AU POIDS
            [
                'lieu_depart'      => 'Paris, France',
                'lieu_arrivee'     => 'Dakar, Sénégal',
                'pays_destination' => 'Sénégal',
                'date_depart'      => '2026-01-15 10:00:00',
                'type_calcul'      => 'poids',
                'poids_maximal'    => 10000.00,
                'volume_maximal'   => 50.00,
                'poids_actuel'     => 1500.00,
                'volume_actuel'    => 5.00,
                'statut'           => 'ouvert',
                'entite_id'        => $entiteTS->id,
                'cree_par'         => $manager->id,
                'nombre_pieds'     => 0,
            ],

            // 2. DÉPART STANDARD - CALCUL AU VOLUME
            [
                'lieu_depart'      => 'Marseille, France',
                'lieu_arrivee'     => 'Abidjan, Côte d’Ivoire',
                'pays_destination' => 'Côte d’Ivoire',
                'date_depart'      => '2026-02-01 08:00:00',
                'type_calcul'      => 'volume',
                'poids_maximal'    => 15000.00,
                'volume_maximal'   => 80.00,
                'poids_actuel'     => 500.00,
                'volume_actuel'    => 2.50,
                'statut'           => 'ouvert',
                'entite_id'        => $entiteTS->id,
                'cree_par'         => $manager->id,
                'nombre_pieds'     => 0,
            ],

            // 3. DÉPART PLEIN (CAPACITÉ ATTEINTE)
            [
                'lieu_depart'      => 'Le Havre, France',
                'lieu_arrivee'     => 'Dakar, Sénégal',
                'pays_destination' => 'Sénégal',
                'date_depart'      => '2026-01-10 14:00:00',
                'type_calcul'      => 'poids',
                'poids_maximal'    => 5000.00,
                'volume_maximal'   => 20.00,
                'poids_actuel'     => 5000.00, // <--- PLEIN
                'volume_actuel'    => 18.00,
                'statut'           => 'chargement',
                'entite_id'        => $entiteTS->id,
                'cree_par'         => $manager->id,
                'nombre_pieds'     => 0,
            ],

            // 4. DÉPART BLOQUÉ / TRANSIT (NE DOIT PAS APPARAÎTRE EN SAISIE)
            [
                'lieu_depart'      => 'Bordeaux, France',
                'lieu_arrivee'     => 'Bamako, Mali',
                'pays_destination' => 'Mali',
                'date_depart'      => '2025-12-30 09:00:00',
                'type_calcul'      => 'poids',
                'poids_maximal'    => 8000.00,
                'volume_maximal'   => 40.00,
                'poids_actuel'     => 3000.00,
                'volume_actuel'    => 15.00,
                'statut'           => 'transit', // <--- STATUT NON ELIGIBLE
                'entite_id'        => $entiteTS->id,
                'cree_par'         => $manager->id,
                'nombre_pieds'     => 0,
            ],

            // 5. DÉPART RÉSERVÉ À UNE AUTRE ENTITÉ
            [
                'lieu_depart'      => 'Paris, France',
                'lieu_arrivee'     => 'Lomé, Togo',
                'pays_destination' => 'Togo',
                'date_depart'      => '2026-03-01 12:00:00',
                'type_calcul'      => 'poids',
                'poids_maximal'    => 10000.00,
                'volume_maximal'   => 50.00,
                'poids_actuel'     => 0,
                'volume_actuel'    => 0,
                'statut'           => 'ouvert',
                'entite_id'        => $entiteAutre->id, // <--- AUTRE ENTITE
                'cree_par'         => $manager->id,
                'nombre_pieds'     => 0,
            ],
        ];

        foreach ($departs as $departData) {
            // On utilise le lieu et la date comme clé unique pour éviter les doublons
            Depart::updateOrCreate(
                [
                    'lieu_depart'  => $departData['lieu_depart'],
                    'lieu_arrivee' => $departData['lieu_arrivee'],
                    'date_depart'  => $departData['date_depart']
                ],
                $departData
            );
        }

        $this->command->info("✅ Départs de test créés selon la structure SQL réelle.");
    }
}