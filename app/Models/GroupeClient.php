<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GroupeClient extends Model
{
    protected $table = 'groupe_client';
    
    protected $fillable = [
        'groupe_id',
        'client_id',
        'partage_par',
        'approuve_par',
        'date_approbation',
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
     * Le client
     */
    public function client()
    {
        return $this->belongsTo(Client::class);
    }
    
    /**
     * Le collecteur qui a partagé
     */
    public function partageur()
    {
        return $this->belongsTo(Collecteur::class, 'partage_par');
    }
    
    /**
     * Le gestionnaire qui a approuvé
     */
    public function approbateur()
    {
        return $this->belongsTo(Gestionnaire::class, 'approuve_par');
    }
}