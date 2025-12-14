<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Pays;    // <-- AJOUTE
use App\Models\Ville;   // <-- AJOUTE

class PaysVillesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Pays africains principaux
        $senegal = Pays::create(['nom' => 'Sénégal', 'code_iso' => 'SEN']);
        $cotedivoire = Pays::create(['nom' => 'Côte d\'Ivoire', 'code_iso' => 'CIV']);
        $mali = Pays::create(['nom' => 'Mali', 'code_iso' => 'MLI']);
        
        // Villes du Sénégal
        Ville::create(['nom' => 'Dakar', 'pays_id' => $senegal->id]);
        Ville::create(['nom' => 'Thiès', 'pays_id' => $senegal->id]);
        
        // Villes Côte d'Ivoire
        Ville::create(['nom' => 'Abidjan', 'pays_id' => $cotedivoire->id]);
        
        // Villes Mali
        Ville::create(['nom' => 'Bamako', 'pays_id' => $mali->id]);
    }
}
