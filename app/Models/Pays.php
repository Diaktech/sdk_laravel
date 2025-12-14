<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pays extends Model
{
    use HasFactory;

    protected $fillable = [
        'nom',
        'code_iso',
    ];

    public function villes()
    {
        return $this->hasMany(Ville::class);
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