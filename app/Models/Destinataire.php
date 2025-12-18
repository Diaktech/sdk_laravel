<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Destinataire extends Model
{
    use HasFactory;

    protected $fillable = [
        'code_unique',
        'prenom',
        'nom',
        'telephone',
        'adresse',
        'zone_id',
        'coordonnees_gps',
        'description_localisation',
        'cree_par_id',
        'cree_par_type'
    ];

    protected $casts = [
        'coordonnees_gps' => 'array',
    ];

    // Relation avec la zone
    public function zone()
    {
        return $this->belongsTo(Zone::class);
    }

    // Relation polymorphique avec le créateur
    public function createur()
    {
        return $this->morphTo('cree_par');
    }

    // Relation avec les événements
    public function evenements()
    {
        return $this->hasMany(Evenement::class);
    }
}