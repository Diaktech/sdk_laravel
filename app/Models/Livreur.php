<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Livreur extends Model
{
    use HasFactory;

    protected $fillable = [
        'unique_id',
        'prenom',
        'nom',
        'telephone',
        'type_vehicule',
        'peut_choisir_zones',
    ];

    protected $casts = [
        'peut_choisir_zones' => 'boolean',
    ];

    public function user()
    {
        return $this->morphOne(User::class, 'userable');
    }
}