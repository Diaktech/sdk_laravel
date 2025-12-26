<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Article extends Model
{
    use HasFactory;

    protected $fillable = [
        'famille_id',
        'reference_article',
        'libelle',
        'positions_tarifaires',
        'origine',
        'valeur_caf',
        'vc_deduit',
        'note',
        'conditionnement',
        'longueur',
        'largeur',
        'hauteur',
        'volume_article',
        'poids',
        'mesures_fixes',
        'est_pris_en_charge',
        'cree_par',
    ];

    protected $casts = [
        'mesures_fixes' => 'boolean',
        'est_pris_en_charge' => 'boolean',
        'valeur_caf' => 'decimal:2',
        'vc_deduit' => 'decimal:2',
        'longueur' => 'decimal:2',
        'largeur' => 'decimal:2',
        'hauteur' => 'decimal:2',
        'volume_article' => 'decimal:4',
        'poids' => 'decimal:3',
    ];

    /**
     * Logique automatique à l'enregistrement
     */
    protected static function booted()
    {
        static::saving(function ($article) {
            // Si l'article a des mesures, on calcule le volume avant de sauvegarder
            if ($article->longueur && $article->largeur && $article->hauteur) {
                $article->volume_article = ($article->longueur * $article->largeur * $article->hauteur) / 1000000;
            } else {
                $article->volume_article = 0;
            }
        });
    }

    // Relation : un article appartient à une famille
    public function famille()
    {
        return $this->belongsTo(Famille::class);
    }

    // Relation : créé par un super gestionnaire
    public function createur()
    {
        return $this->belongsTo(SuperGestionnaire::class, 'cree_par');
    }

    // Méthode pour calculer le volume (si mesures disponibles)
    public function getVolumeAttribute()
    {
        return $this->volume_article ?? 0;
    }

    // Vérifie si l'article a des mesures
    public function hasMesures(): bool
    {
        return !is_null($this->longueur) && !is_null($this->largeur) && !is_null($this->hauteur);
    }
}