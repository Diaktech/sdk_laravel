<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PhotoEvenement extends Model
{
    // On précise le nom de la table car Laravel chercherait "photo_evenements" par défaut
    protected $table = 'photo_evenements';

    protected $fillable = [
        'evenement_id',
        'item_evenement_id',
        'type_photo',
        'chemin_photo',
        'prise_par'
    ];

    // Relation : Une photo appartient à un événement
    public function evenement()
    {
        return $this->belongsTo(Evenement::class);
    }

    // Relation : Une photo appartient à un item de collecte
    public function item()
    {
        return $this->belongsTo(ItemEvenement::class, 'item_evenement_id');
    }
}
