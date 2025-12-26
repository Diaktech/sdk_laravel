<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Groupe extends Model
{
    protected $table = 'groupes';
    
    protected $fillable = [
        'nom',
        'code_unique',
        'cree_par',
        'description',
    ];
    
    /**
     * Les collecteurs membres de ce groupe
     */
    public function collecteurs(): BelongsToMany
    {
        return $this->belongsToMany(Collecteur::class, 'groupe_collecteur')
                    ->withPivot('est_propriétaire')
                    ->withTimestamps();
    }
    
    /**
     * Les clients dans ce groupe
     */
    public function clients(): BelongsToMany
    {
        return $this->belongsToMany(Client::class, 'groupe_client')
                    ->withPivot(['partage_par', 'approuve_par', 'date_approbation'])
                    ->withTimestamps();
    }
    
    /**
     * Le gestionnaire qui a créé le groupe
     */
    public function createur()
    {
        return $this->belongsTo(Gestionnaire::class, 'cree_par');
    }
}