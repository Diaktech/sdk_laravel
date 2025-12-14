<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SuperGestionnaire extends Model
{
    use HasFactory;

    protected $fillable = [
        'unique_id',
        'prenom',
        'nom',
        'telephone',
        'droits_access_speciaux',
    ];

    protected $casts = [
        'droits_access_speciaux' => 'array',
    ];

    // Relation avec User (polymorphique)
    public function user()
    {
        return $this->morphOne(User::class, 'userable');
    }
}