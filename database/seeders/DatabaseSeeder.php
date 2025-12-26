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
        $this->call(DonneesReferenceSeeder::class);

        $this->call(PaysVillesSeeder::class);

        // 1. Les bases (Pays, Villes, Zones, Entités, Users de test)
        $this->call(InitialDataSeeder::class);
        
        // 2. Les départs (doivent arriver APRES l'entité et le gestionnaire)
        $this->call(DesDepartsSeeder::class);

        // 3. Créer les autres clients et tous les destinataires
        $this->call(DestinatairesSeeder::class);

        $this->call(GroupeClientSeeder::class);

        $this->call(GroupeCollecteurSeeder::class);

        $this->call(GroupesSeeder::class);

        
        $this->command->info('✅ Tous les seeders ont été exécutés avec succès !');
        $this->command->info('📊 Résumé :');
        $this->command->info('   • Utilisateurs et rôles créés');
        $this->command->info('   • Données géographiques créées');
        $this->command->info('   • Articles et départs créés');
        $this->command->info('   • Groupes et associations créés');
    }
}