<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Collecteur extends Model
{
    use HasFactory;

    protected $fillable = [
        'unique_id',
        'prenom',
        'nom',
        'telephone',
        'adresse_ligne1',
        'adresse_ligne2',
        'code_postal',
        'ville_id',
        'pays_id',
        'entite_id',
        'est_bloque',
        'niveau_blocage',
        'montant_total_genere',
        'montant_total_regularise',
        'montant_restant',
    ];

    protected $casts = [
        'est_bloque' => 'boolean',
        'niveau_blocage' => 'integer',
        'montant_total_genere' => 'decimal:2',
        'montant_total_regularise' => 'decimal:2',
        'montant_restant' => 'decimal:2',
    ];

    public function user()
    {
        return $this->morphOne(User::class, 'userable');
    }

    public function entite()
    {
        return $this->belongsTo(Entite::class);
    }

    public function ville()
    {
        return $this->belongsTo(Ville::class);
    }

    public function pays()
    {
        return $this->belongsTo(Pays::class);
    }
}