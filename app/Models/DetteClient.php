<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DetteClient extends Model
{
    protected $table = 'dettes_clients';

    protected $fillable = [
        'client_id', 'evenement_id', 'facture_id', 'montant_initial', 
        'montant_restant', 'statut', 'type', 'justification', 'cree_par', 'date_echeance'
    ];

    /**
     * Relation : Une dette peut être payée en plusieurs fois (plusieurs remboursements)
     */
    public function remboursements()
    {
        return $this->hasMany(RemboursementDette::class, 'dette_client_id');
    }

    /**
     * Relations vers les parents
     */
    public function client() { return $this->belongsTo(Client::class); }
    public function evenement() { return $this->belongsTo(Evenement::class); }
    public function facture() { return $this->belongsTo(Facture::class); }
}
