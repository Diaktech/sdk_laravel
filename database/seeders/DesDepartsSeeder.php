<?php
// database/seeders/DesDepartsSeeder.php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Depart;
use App\Models\Entite;
use App\Models\Gestionnaire;

class DesDepartsSeeder extends Seeder
{
    /**
     * Exécuter le seeder.
     */
    public function run(): void
    {
        // Récupérer l'entité TS (doit exister)
        $entiteTS = Entite::firstOrCreate([
            'nom' => 'Terranga Services',
            'code' => 'TS',
            'tarif_ts_par_defaut' => 250.00,
            'tarif_kilo_par_defaut' => 3.00,
            'majoration_domicile' => 0.50,
        ]);

        // Récupérer un gestionnaire pour créer les départs
        $gestionnaire = Gestionnaire::first();
        
        if (!$gestionnaire) {
            $gestionnaire = Gestionnaire::create([
                'unique_id' => 'MAN001',
                'prenom' => 'Manager',
                'nom' => 'Test',
                'telephone' => '+33123456789',
                'peut_modifier_articles' => true,
                'peut_modifier_parameters' => true,
            ]);
        }

        echo "🔧 Création des départs de test...\n";

        // ==================== DÉPARTS EN "VOLUME" (calcul par m³) ====================
        
        // Départ 1 : Volume - Statut "ouvert" (disponible)
        $depart1 = Depart::create([
            'entite_id' => $entiteTS->id,
            'cree_par' => $gestionnaire->id,
            'date_depart' => now()->addDays(15),
            'lieu_depart' => 'Paris, France',
            'lieu_arrivee' => 'Dakar, Sénégal',
            'pays_destination' => 'Sénégal',
            'volume_maximal' => 50.000,      // 50 m³
            'poids_maximal' => null,         // Pas de limite poids
            'type_calcul' => 'volume',       // Calcul par volume
            'statut' => 'ouvert',           // Disponible pour nouvelles prises
            'nombre_pieds' => 20,           // 20 pieds
            'volume_actuel' => 12.500,      // Déjà 12.5 m³ utilisés
            'poids_actuel' => 0,
        ]);
        echo "✅ Départ 1 créé : {$depart1->lieu_depart} → {$depart1->lieu_arrivee} (Volume)\n";

        // Départ 2 : Volume - Presque plein
        $depart2 = Depart::create([
            'entite_id' => $entiteTS->id,
            'cree_par' => $gestionnaire->id,
            'date_depart' => now()->addDays(20),
            'lieu_depart' => 'Lyon, France',
            'lieu_arrivee' => 'Abidjan, Côte d\'Ivoire',
            'pays_destination' => 'Côte d\'Ivoire',
            'volume_maximal' => 40.000,      // 40 m³
            'poids_maximal' => null,
            'type_calcul' => 'volume',
            'statut' => 'ouvert',
            'nombre_pieds' => 20,
            'volume_actuel' => 35.200,      // 88% rempli
            'poids_actuel' => 0,
        ]);
        echo "✅ Départ 2 créé : {$depart2->lieu_depart} → {$depart2->lieu_arrivee} (Volume - 88%)\n";

        // Départ 3 : Volume - Nouveau départ
        $depart3 = Depart::create([
            'entite_id' => $entiteTS->id,
            'cree_par' => $gestionnaire->id,
            'date_depart' => now()->addDays(30),
            'lieu_depart' => 'Marseille, France',
            'lieu_arrivee' => 'Lomé, Togo',
            'pays_destination' => 'Togo',
            'volume_maximal' => 60.000,      // 60 m³
            'poids_maximal' => null,
            'type_calcul' => 'volume',
            'statut' => 'ouvert',
            'nombre_pieds' => 40,           // 40 pieds
            'volume_actuel' => 5.000,       // Seulement 5 m³ utilisés
            'poids_actuel' => 0,
        ]);
        echo "✅ Départ 3 créé : {$depart3->lieu_depart} → {$depart3->lieu_arrivee} (Volume - 8%)\n";

        // ==================== DÉPARTS EN "POIDS" (calcul par kg) ====================
        
        // Départ 4 : Poids - Statut "ouvert"
        $depart4 = Depart::create([
            'entite_id' => $entiteTS->id,
            'cree_par' => $gestionnaire->id,
            'date_depart' => now()->addDays(10),
            'lieu_depart' => 'Bordeaux, France',
            'lieu_arrivee' => 'Ouagadougou, Burkina Faso',
            'pays_destination' => 'Burkina Faso',
            'volume_maximal' => 30.000,      // 30 m³
            'poids_maximal' => 10000.00,    // 10 tonnes max
            'type_calcul' => 'poids',       // Calcul par poids
            'statut' => 'ouvert',
            'nombre_pieds' => 20,
            'volume_actuel' => 8.500,
            'poids_actuel' => 3200.00,      // 3.2 tonnes utilisées
        ]);
        echo "✅ Départ 4 créé : {$depart4->lieu_depart} → {$depart4->lieu_arrivee} (Poids)\n";

        // Départ 5 : Poids - Presque plein en poids
        $depart5 = Depart::create([
            'entite_id' => $entiteTS->id,
            'cree_par' => $gestionnaire->id,
            'date_depart' => now()->addDays(25),
            'lieu_depart' => 'Toulouse, France',
            'lieu_arrivee' => 'Cotonou, Bénin',
            'pays_destination' => 'Bénin',
            'volume_maximal' => 25.000,
            'poids_maximal' => 8000.00,     // 8 tonnes max
            'type_calcul' => 'poids',
            'statut' => 'ouvert',
            'nombre_pieds' => 20,
            'volume_actuel' => 6.200,
            'poids_actuel' => 7200.00,      // 90% du poids max
        ]);
        echo "✅ Départ 5 créé : {$depart5->lieu_depart} → {$depart5->lieu_arrivee} (Poids - 90%)\n";

        // Départ 6 : Poids - Bientôt départ
        $depart6 = Depart::create([
            'entite_id' => $entiteTS->id,
            'cree_par' => $gestionnaire->id,
            'date_depart' => now()->addDays(5),
            'lieu_depart' => 'Lille, France',
            'lieu_arrivee' => 'Conakry, Guinée',
            'pays_destination' => 'Guinée',
            'volume_maximal' => 35.000,
            'poids_maximal' => 12000.00,    // 12 tonnes
            'type_calcul' => 'poids',
            'statut' => 'ouvert',
            'nombre_pieds' => 40,
            'volume_actuel' => 15.800,
            'poids_actuel' => 4500.00,      // 37.5% du poids max
        ]);
        echo "✅ Départ 6 créé : {$depart6->lieu_depart} → {$depart6->lieu_arrivee} (Poids - 38%)\n";

        // ==================== DÉPARTS AUTRES STATUTS ====================
        
        // Départ 7 : Volume - Statut "chargement" (non disponible)
        Depart::create([
            'entite_id' => $entiteTS->id,
            'cree_par' => $gestionnaire->id,
            'date_depart' => now()->addDays(3),
            'lieu_depart' => 'Nantes, France',
            'lieu_arrivee' => 'Bamako, Mali',
            'pays_destination' => 'Mali',
            'volume_maximal' => 45.000,
            'poids_maximal' => null,
            'type_calcul' => 'volume',
            'statut' => 'chargement',       // En cours de chargement
            'nombre_pieds' => 20,
            'volume_actuel' => 42.500,      // 94% rempli
            'poids_actuel' => 0,
        ]);
        echo "✅ Départ 7 créé : Nantes → Bamako (Volume - chargement)\n";

        // Départ 8 : Poids - Statut "brouillon" (non disponible)
        Depart::create([
            'entite_id' => $entiteTS->id,
            'cree_par' => $gestionnaire->id,
            'date_depart' => now()->addDays(60),
            'lieu_depart' => 'Strasbourg, France',
            'lieu_arrivee' => 'Yaoundé, Cameroun',
            'pays_destination' => 'Cameroun',
            'volume_maximal' => 50.000,
            'poids_maximal' => 15000.00,
            'type_calcul' => 'poids',
            'statut' => 'brouillon',        // En préparation
            'nombre_pieds' => 40,
            'volume_actuel' => 0,
            'poids_actuel' => 0,
        ]);
        echo "✅ Départ 8 créé : Strasbourg → Yaoundé (Poids - brouillon)\n";

        echo "\n🎉 {$entiteTS->nom} : 8 départs créés avec succès !\n";
        echo "📊 Répartition :\n";
        echo "  • Volume (ouvert) : 3 départs\n";
        echo "  • Poids (ouvert) : 3 départs\n";
        echo "  • Autres statuts : 2 départs\n";
        echo "  • Capacités : 8% à 90% de remplissage\n";
    }
}