<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ItemEvenement extends Model
{
    use HasFactory;

    protected $fillable = [
        'evenement_id',
        'article_id',
        'quantite',
        'longueur',
        'largeur',
        'hauteur',
        'poids',
        'volume_unitaire',
        // Nouveaux champs financiers
        'prix_unitaire_client',
        'prix_total_client',
        'part_entite_item',
        'commission_col_item',
        'valeur_caf',
        'etat',
        'notes_defaut',
        'photo_defaut_chemin',
        'notes',
    ];

    protected $casts = [
        'longueur' => 'decimal:2',
        'largeur' => 'decimal:2',
        'hauteur' => 'decimal:2',
        'poids' => 'decimal:2',
        'volume_unitaire' => 'decimal:4', 
        'prix_unitaire_client' => 'decimal:2',
        'prix_total_client' => 'decimal:2',
        'part_entite_item' => 'decimal:2',
        'commission_col_item' => 'decimal:2',
        'valeur_caf' => 'decimal:2',
    ];

    // --- Relations ---

    public function evenement()
    {
        return $this->belongsTo(Evenement::class);
    }

    public function article()
    {
        return $this->belongsTo(Article::class);
    }

    // --- Logique Métier ---

    /**
     * Calcule le volume en m3 à partir des dimensions en cm
     */
    public function calculerVolume()
    {
        if ($this->longueur && $this->largeur && $this->hauteur) {
            // Conversion cm³ → m³ (1 m3 = 1 000 000 cm3)
            return ($this->longueur * $this->largeur * $this->hauteur) / 1000000;
        }
        return 0;
    }

    /**
     * Accessoire pour savoir si l'item est facturé au poids ou au volume
     * basé sur le type de l'événement parent.
     */
    public function getTypeFacturationAttribute()
    {
        return $this->evenement->type_evenement; // 'poids' ou 'volume'
    }

    /**
     * Permet d'écrire $item->unite_mesure pour obtenir "kg" ou "m³"
     */
    public function getUniteMesureAttribute()
    {
        // On remonte au départ lié à l'événement
        $type = $this->evenement->depart->type_facturation; 
        
        return $type === 'poids' ? 'kg' : 'm³';
    }
}