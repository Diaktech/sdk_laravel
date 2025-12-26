<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GroupeCollecteur extends Model
{
    protected $table = 'groupe_collecteur';
    
    protected $fillable = [
        'groupe_id',
        'collecteur_id',
        'est_propriétaire',
    ];
    
    public $timestamps = true;
    
    /**
     * Le groupe
     */
    public function groupe()
    {
        return $this->belongsTo(Groupe::class);
    }
    
    /**
     * Le collecteur
     */
    public function collecteur()
    {
        return $this->belongsTo(Collecteur::class);
    }
}