<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Groupe;
use App\Models\Gestionnaire;

class GroupesSeeder extends Seeder
{
    public function run()
    {
        // 1. Récupérer le manager créé dans InitialDataSeeder
        $manager = Gestionnaire::where('unique_id', 'MAM001')->first();
        
        // Sécurité au cas où InitialDataSeeder n'aurait pas été lancé
        if (!$manager) {
            $manager = Gestionnaire::firstOrCreate(
                ['unique_id' => 'MAM001'],
                [
                    'prenom' => 'Manager',
                    'nom' => 'Test',
                    'telephone' => '+221 77 000 00 00',
                    'peut_modifier_articles' => true,
                    'peut_modifier_parameters' => true,
                ]
            );
        }
        
        // 2. Définition des groupes
        $groupes = [
            [
                'nom' => 'Groupe Paris Nord',
                'code_unique' => 'GRP0001',
                'cree_par' => $manager->id,
                'description' => 'Collecteurs basés au Nord de Paris'
            ],
            [
                'nom' => 'Groupe Dakar Plateau',
                'code_unique' => 'GRP0002',
                'cree_par' => $manager->id,
                'description' => 'Collecteurs basés à Dakar Plateau'
            ],
            [
                'nom' => 'Groupe Partenaires VIP',
                'code_unique' => 'GRP0003',
                'cree_par' => $manager->id,
                'description' => 'Partenaires stratégiques à l’international'
            ],
        ];
        
        // 3. Insertion / Mise à jour
        foreach ($groupes as $groupe) {
            Groupe::updateOrCreate(
                ['code_unique' => $groupe['code_unique']],
                $groupe
            );
        }
        
        $this->command->info('✅ 3 groupes créés (GRP0001, GRP0002, GRP0003)');
    }
}