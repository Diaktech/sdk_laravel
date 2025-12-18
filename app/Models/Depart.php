<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Depart extends Model
{
    use HasFactory;

    protected $fillable = [
        'entite_id',
        'date_depart',
        'volume_maximal',
        'poids_maximal',
        'type_calcul',
        'statut',
        'pays_destination',
        'volume_actuel',
        'poids_actuel',
        'lieu_depart',
        'lieu_arrivee',
        'nombre_pieds',
        'cree_par',
        'ferme_par',
        'date_fermeture',
    ];

    protected $casts = [
        'date_depart' => 'date',
        'date_fermeture' => 'datetime',
        'volume_maximal' => 'decimal:3',
        'poids_maximal' => 'decimal:2',
        'volume_actuel' => 'decimal:3',
        'poids_actuel' => 'decimal:2',
        'nombre_pieds' => 'integer',
    ];

    // Relation : un départ appartient à une entité
    public function entite()
    {
        return $this->belongsTo(Entite::class);
    }

    // Relation : créé par un gestionnaire
    public function createur()
    {
        return $this->belongsTo(Gestionnaire::class, 'cree_par');
    }

    // Relation : fermé par un gestionnaire
    public function fermeur()
    {
        return $this->belongsTo(Gestionnaire::class, 'ferme_par');
    }

    // Relation : un départ a plusieurs événements
    public function evenements()
    {
        return $this->hasMany(Evenement::class);
    }

    // Calcul du remplissage en pourcentage
    public function getPourcentageRemplissageAttribute()
    {
        if ($this->type_calcul === 'volume' && $this->volume_maximal > 0) {
            return ($this->volume_actuel / $this->volume_maximal) * 100;
        }
        
        if ($this->type_calcul === 'poids' && $this->poids_maximal > 0) {
            return ($this->poids_actuel / $this->poids_maximal) * 100;
        }
        
        return 0;
    }

    // Vérifie si le départ est plein
    public function estPlein(): bool
    {
        return $this->pourcentage_remplissage >= 100;
    }

    // Vérifie si on peut ajouter un événement
    public function peutAjouterEvenement($volume, $poids): bool
    {
        if ($this->estPlein()) {
            return false;
        }

        if ($this->type_calcul === 'volume') {
            $nouveauVolume = $this->volume_actuel + $volume;
            return $nouveauVolume <= $this->volume_maximal;
        }

        if ($this->type_calcul === 'poids') {
            $nouveauPoids = $this->poids_actuel + $poids;
            return $nouveauPoids <= $this->poids_maximal;
        }

        return true;
    }
}