<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Zone extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',  // <-- AJOUT
        'nom',
        'ville_id',
        'pays_id',
        'description',
    ];

    public function ville()
    {
        return $this->belongsTo(Ville::class);
    }

    public function pays()
    {
        return $this->belongsTo(Pays::class);
    }

    public function livreurs()
    {
        return $this->belongsToMany(Livreur::class, 'affectations_zones')
                    ->withTimestamps();
    }
}