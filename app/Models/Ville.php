<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ville extends Model
{
    use HasFactory;

    protected $fillable = [
        'nom',
        'pays_id',
    ];

    public function pays()
    {
        return $this->belongsTo(Pays::class);
    }

    public function zones()
    {
        return $this->hasMany(Zone::class);
    }

    public function clients()
    {
        return $this->hasMany(Client::class);
    }

    public function collecteurs()
    {
        return $this->hasMany(Collecteur::class);
    }
}