<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Entite extends Model
{
    use HasFactory;

    protected $fillable = [
        'nom',
        'code',
        'majoration_domicile',
        'email_contact',
        'telephone_contact',
    ];

    protected $casts = [
        'majoration_domicile' => 'decimal:2',
    ];

    public function collecteurs()
    {
        return $this->hasMany(Collecteur::class);
    }
}