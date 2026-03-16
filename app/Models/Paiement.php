<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Paiement extends Model
{
    use HasFactory;

    protected $fillable = [
        'reference_interne',
        'facture_id',
        'client_id',
        'collecteur_id',
        'montant',
        'devise',
        'moyen_paiement',
        'type_paiement',
        'reference_transaction',
        'statut',
        'notes',
        'date_enregistrement'
    ];

    /**
     * Une facture peut être liée à plusieurs paiements (acompte, solde, etc.)
     */
    public function facture()
    {
        return $this->belongsTo(Facture::class);
    }

    /**
     * Le client qui a effectué le versement
     */
    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    /**
     * L'utilisateur (collecteur) qui a encaissé la somme
     */
    public function collecteur()
    {
        return $this->belongsTo(User::class, 'collecteur_id');
    }
}