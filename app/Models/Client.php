<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Client extends Model
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
        'collecteur_principal_id',
        'total_du',
        'total_paye',
        'volume_total_envoye',
    ];

    protected $casts = [
        'total_du' => 'decimal:2',
        'total_paye' => 'decimal:2',
        'volume_total_envoye' => 'decimal:2',
    ];

    public function user()
    {
        return $this->morphOne(User::class, 'userable');
    }

    public function collecteurPrincipal()
    {
        return $this->belongsTo(Collecteur::class, 'collecteur_principal_id');
    }

    public function destinataires()
    {
        return $this->hasMany(Destinataire::class, 'client_id');
    }

    public function ville()
    {
        return $this->belongsTo(Ville::class);
    }

    public function pays()
    {
        return $this->belongsTo(Pays::class);
    }

    public function groupes() 
    {
        return $this->belongsToMany(Groupe::class, 'groupe_client')
            ->withPivot(['partage_par', 'approuve_par', 'date_approbation']);
    }

    // Collecteurs via les groupes
    public function collecteursGroupes()
    {
        return $this->hasManyThrough(
            Collecteur::class,
            GroupeClient::class,
            'client_id',     // Clé étrangère sur groupe_client
            'id',            // Clé primaire sur collecteurs
            'id',            // Clé primaire sur clients
            'groupe_id'      // Clé étrangère sur groupe_client
        )->whereHas('groupes'); // Seulement via les groupes
    }

}