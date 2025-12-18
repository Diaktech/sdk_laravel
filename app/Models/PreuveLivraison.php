<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PreuveLivraison extends Model
{
    use HasFactory;

    protected $fillable = [
        'livraison_id',
        'type_preuve',
        'chemin_photo',
        'chemin_signature',
        'nom_destinataire',
        'notes'
    ];

    // Relations
    public function livraison()
    {
        return $this->belongsTo(Livraison::class);
    }

    // Méthode pour obtenir l'URL de la photo
    public function getPhotoUrlAttribute()
    {
        return $this->chemin_photo ? asset('storage/' . $this->chemin_photo) : null;
    }

    // Méthode pour obtenir l'URL de la signature
    public function getSignatureUrlAttribute()
    {
        return $this->chemin_signature ? asset('storage/' . $this->chemin_signature) : null;
    }
}