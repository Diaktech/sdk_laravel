<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Facture extends Model
{
    protected $fillable = [
        'evenement_id', 'client_id', 'numero_facture', 'devise','montant_remise', 
        'montant_total', 'montant_entite', 'montant_collecteur', 
        'statut_paiement', 'est_entierement_payee', 'chemin_pdf', 
        'generee_par', 'date_generation'
    ];

    protected $casts = [
        'date_generation' => 'datetime',
        'est_entierement_payee' => 'boolean',
    ];

    // Une facture appartient à un événement
    public function evenement()
    {
        return $this->belongsTo(Evenement::class);
    }

    // Une facture appartient à un client
    public function client()
    {
        return $this->belongsTo(Client::class);
    }
}
