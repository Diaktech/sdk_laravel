<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Gestionnaire extends Model
{
    use HasFactory;

    protected $fillable = [
        'unique_id',
        'prenom',
        'nom',
        'telephone',
        'peut_modifier_articles',
        'peut_modifier_parameters',
    ];

    protected $casts = [
        'peut_modifier_articles' => 'boolean',
        'peut_modifier_parameters' => 'boolean',
    ];

    public function user()
    {
        return $this->morphOne(User::class, 'userable');
    }
}