<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RemboursementDette extends Model
{
    protected $table = 'remboursements_dettes';

    protected $fillable = [
        'dette_client_id', 'paiement_id', 'montant_verse', 
        'chemin_recu_pdf', 'encaisse_par', 'date_remboursement'
    ];

    protected $casts = [
        'date_remboursement' => 'datetime',
    ];

    /**
     * Relation : Retourne la dette d'origine
     */
    public function dette()
    {
        return $this->belongsTo(DetteClient::class, 'dette_client_id');
    }

    /**
     * Relation : Retourne les détails du paiement associé
     */
    public function paiement()
    {
        return $this->belongsTo(Paiement::class);
    }
}
