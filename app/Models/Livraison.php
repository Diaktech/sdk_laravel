<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Livraison extends Model
{
    use HasFactory;

    protected $fillable = [
        'evenement_id',
        'livreur_id',
        'zone_id',
        'statut_livraison',
        'statut',
        'date_prevue',
        'date_debut',
        'date_livraison',
        'notes'
    ];

    protected $casts = [
        'date_prevue' => 'date',
        'date_debut' => 'datetime',
        'date_livraison' => 'datetime',
    ];

    // Relations
    public function evenement()
    {
        return $this->belongsTo(Evenement::class);
    }

    public function livreur()
    {
        return $this->belongsTo(Livreur::class);
    }

    public function zone()
    {
        return $this->belongsTo(Zone::class);
    }

    public function preuves()
    {
        return $this->hasMany(PreuveLivraison::class);
    }

    // Méthodes utilitaires
    public function estTerminee()
    {
        return in_array($this->statut, ['livre', 'partiellement_livre']);
    }

    public function estEnCours()
    {
        return $this->statut === 'en_livraison';
    }
}