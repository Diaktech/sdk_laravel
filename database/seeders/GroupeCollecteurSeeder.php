<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Groupe;
use App\Models\Collecteur;
use App\Models\GroupeCollecteur;

class GroupeCollecteurSeeder extends Seeder
{
    public function run()
    {
        // 1. Récupération sécurisée des groupes
        $g1 = Groupe::where('code_unique', 'GRP0001')->first();
        $g2 = Groupe::where('code_unique', 'GRP0002')->first();
        $g3 = Groupe::where('code_unique', 'GRP0003')->first();
        
        // 2. Récupération sécurisée des collecteurs
        // On s'assure d'avoir au moins COL001 (créé dans InitialDataSeeder)
        $c1 = Collecteur::where('unique_id', 'COL001')->first();
        $c2 = Collecteur::where('unique_id', 'COL002')->first();
        $c3 = Collecteur::where('unique_id', 'COL003')->first();
        $c4 = Collecteur::where('unique_id', 'COL004')->first();
        
        $this->command->info('🔗 Création des liens Groupes <-> Collecteurs...');

        // 3. Préparation des associations (uniquement si les deux existent)
        $data = [];

        // Groupe 1
        if ($g1 && $c1) $data[] = ['groupe_id' => $g1->id, 'collecteur_id' => $c1->id, 'est_proprietaire' => false];
        if ($g1 && $c2) $data[] = ['groupe_id' => $g1->id, 'collecteur_id' => $c2->id, 'est_proprietaire' => true];

        // Groupe 2
        if ($g2 && $c1) $data[] = ['groupe_id' => $g2->id, 'collecteur_id' => $c1->id, 'est_proprietaire' => true];
        if ($g2 && $c3) $data[] = ['groupe_id' => $g2->id, 'collecteur_id' => $c3->id, 'est_proprietaire' => false];

        // Groupe 3
        if ($g3 && $c2) $data[] = ['groupe_id' => $g3->id, 'collecteur_id' => $c2->id, 'est_proprietaire' => true];
        if ($g3 && $c4) $data[] = ['groupe_id' => $g3->id, 'collecteur_id' => $c4->id, 'est_proprietaire' => false];

        // 4. Insertion
        foreach ($data as $item) {
            GroupeCollecteur::firstOrCreate(
                [
                    'groupe_id' => $item['groupe_id'],
                    'collecteur_id' => $item['collecteur_id']
                ],
                [
                    'est_proprietaire' => $item['est_proprietaire'] // Attention à l'orthographe 'proprietaire' sans accent dans la DB
                ]
            );
        }

        $this->command->info('✅ Associations groupe-collecteur terminées !');
    }
}