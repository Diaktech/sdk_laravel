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
        'zone_id',
        'volume_total',
        'poids_total',
        'montant_total',
        'part_entite',
        'commission_col',
        'prix_kilo',
        'prix_m3',
        'facture_generee',
        'necessite_validation',
        'valide_par_id',
        'date_validation',
        'note',
    ];

    protected $casts = [
        'volume_total' => 'decimal:3',
        'poids_total' => 'decimal:2',
        'montant_total' => 'decimal:2',
        'part_entite' => 'decimal:2',
        'commission_col' => 'decimal:2',
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

    public function zone()
    {
        return $this->belongsTo(Zone::class);
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

    public function calculerTotaux()
    {
        // On recharge les items pour être sûr d'avoir les dernières valeurs en mémoire
        $this->load('items');

        $this->update([
            'montant_total'  => $this->items->sum('prix_total_client'),
            'part_entite'    => $this->items->sum('part_entite_item'),    // Somme des parts figées par ligne
            'commission_col' => $this->items->sum('commission_col_item'), // Somme des gains figés par ligne
            'poids_total'    => $this->items->sum('poids'),
            'volume_total'   => $this->items->sum('volume_unitaire'),
        ]);
    }


}