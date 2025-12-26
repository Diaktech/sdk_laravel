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
        'tarif_volume_revient',
        'tarif_kilo_revient',
        'tarif_kilo_vente_defaut',
        'majoration_domicile',
        'peut_modifier_tarif_vente',
        'montant_total_genere',
        'montant_total_regularise',
        'montant_restant',
    ];

    protected $casts = [
        'est_bloque' => 'boolean',
        'niveau_blocage' => 'integer',
        'tarif_volume_revient'      => 'decimal:2',
        'tarif_kilo_revient'        => 'decimal:2',
        'tarif_kilo_vente_defaut'   => 'decimal:2',
        'majoration_domicile' => 'float',
        'peut_modifier_tarif_vente' => 'boolean',
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

    public function groupes() {
        return $this->belongsToMany(Groupe::class, 'groupe_collecteur')
            ->withPivot('est_propriétaire');
    }

    public function clientsPartages() {
    return $this->hasManyThrough(
        Client::class,
        GroupeClient::class,
        'partage_par',  // collecteur qui a partagé
        'id',
        'id',
        'client_id'
        )->whereNotNull('approuve_par'); // Uniquement les partages approuvés
    }

}