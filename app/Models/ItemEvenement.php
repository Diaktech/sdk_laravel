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
        'volume_calcule',
        'prix_par_m3',
        'prix_par_kilo',
        'etat',
        'notes_defaut',
        'photo_defaut_chemin',
        'notes'
    ];

    protected $casts = [
        'longueur' => 'decimal:2',
        'largeur' => 'decimal:2',
        'hauteur' => 'decimal:2',
        'poids' => 'decimal:2',
        'volume_calcule' => 'decimal:3',
        'prix_par_m3' => 'decimal:2',
        'prix_par_kilo' => 'decimal:2',
    ];

    // Relations
    public function evenement()
    {
        return $this->belongsTo(Evenement::class);
    }

    public function article()
    {
        return $this->belongsTo(Article::class);
    }

    // Méthode pour calculer le volume
    public function calculerVolume()
    {
        if ($this->longueur && $this->largeur && $this->hauteur) {
            // Conversion cm³ → m³
            return ($this->longueur * $this->largeur * $this->hauteur) / 1000000;
        }
        return 0;
    }
}