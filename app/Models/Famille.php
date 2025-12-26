<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes; 
class Famille extends Model
{
    use HasFactory, SoftDeletes; // <-- MODIFIE

    protected $fillable = [
        'nom',
        'description',
    ];

    // Les articles liés (sans les soft deleted)
    public function articles()
    {
        return $this->hasMany(Article::class);
    }

    // Méthode pour compter les articles actifs
    public function countArticlesActifs()
    {
        return $this->articles()->count();
    }

    // Scope pour familles actives
    public function scopeActive($query)
    {
        return $query->whereNull('deleted_at');
    }

    // Vérifie si la famille peut être supprimée (pas d'articles liés)
    public function canBeDeleted(): bool
    {
        return $this->articles()->count() === 0;
    }
}