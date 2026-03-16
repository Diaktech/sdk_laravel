<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SignatureEvenement extends Model
{
    use HasFactory;

    // Nom de la table si différent du pluriel automatique
    protected $table = 'signatures_evenement';

    // Champs autorisés pour l'insertion de masse
    protected $fillable = [
        'evenement_id',
        'type_signature', // 'client' ou 'collecteur'
        'chemin_signature',
        'signe_par',
        'ip_adresse'
    ];

    /**
     * Relation : La signature appartient à un événement.
     */
    public function evenement()
    {
        return $this->belongsTo(Evenement::class);
    }

    /**
     * Relation : La personne qui a validé/enregistré la signature.
     */
    public function auteur()
    {
        return $this->belongsTo(User::class, 'signe_par');
    }

    /**
     * Helper pour savoir si c'est une signature client.
     */
    public function isClient()
    {
        return $this->type_signature === 'client';
    }
}