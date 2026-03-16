<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // On peut mélanger les appels individuels et les tableaux
        $this->call(DonneesReferenceSeeder::class);
        $this->call(PaysVillesSeeder::class);

        // Regrouper par thématique aide à comprendre l'ordre logique
        $this->call([
            InitialDataSeeder::class,      // Bases (Entités, Users)
            DesDepartsSeeder::class,       // Départs
            DestinatairesSeeder::class,    // Clients et Destinataires
            GroupeClientSeeder::class,
            GroupeCollecteurSeeder::class,
            GroupesSeeder::class,
            ReductionPromotionnelleSeeder::class, // Les promos à la fin car elles dépendent de tout le reste
        ]);

        $this->command->info('✅ Tous les seeders ont été exécutés avec succès !');
    }
}