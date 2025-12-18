<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Evenement extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'depart_id',
        'client_id',
        'collecteur_id',
        'destinataire_id',
        'code_unique',
        'type_prise_charge',
        'statut',
        'priorite',
        'volume_total',
        'poids_total',
        'montant_total',
        'montant_ts',
        'montant_collecteur',
        'prix_kilo',
        'prix_m3',
        'facture_generee',
        'necessite_validation',
        'valide_par_id',
        'date_validation'
    ];

    protected $casts = [
        'volume_total' => 'decimal:3',
        'poids_total' => 'decimal:2',
        'montant_total' => 'decimal:2',
        'montant_ts' => 'decimal:2',
        'montant_collecteur' => 'decimal:2',
        'prix_kilo' => 'decimal:2',
        'prix_m3' => 'decimal:2',
        'facture_generee' => 'boolean',
        'necessite_validation' => 'boolean',
        'date_validation' => 'datetime',
    ];

    // Relations
    public function depart()
    {
        return $this->belongsTo(Depart::class);
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function collecteur()
    {
        return $this->belongsTo(Collecteur::class);
    }

    public function destinataire()
    {
        return $this->belongsTo(Destinataire::class);
    }

    public function validateur()
    {
        return $this->belongsTo(Gestionnaire::class, 'valide_par_id');
    }

    public function items()
    {
        return $this->hasMany(ItemEvenement::class);
    }

    public function livraison()
    {
        return $this->hasOne(Livraison::class);
    }

    // Scope pour les événements en attente
    public function scopeEnAttente($query)
    {
        return $query->where('statut', 'en_attente');
    }

    // Scope pour les événements validés
    public function scopeValides($query)
    {
        return $query->where('statut', 'valide');
    }
}