<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'user_type',
        'userable_id',
        'userable_type',
        'is_active',
        'last_login_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'is_active' => 'boolean',
        'last_login_at' => 'datetime',
    ];

    // Relation polymorphique : un User peut être lié à différents modèles
    public function userable()
    {
        return $this->morphTo();
    }

    // Méthodes helpers pour vérifier le type
    public function isSuperGestionnaire(): bool
    {
        return $this->user_type === 'super_gestionnaire';
    }

    public function isGestionnaire(): bool
    {
        return $this->user_type === 'gestionnaire';
    }

    public function isCollecteur(): bool
    {
        return $this->user_type === 'collecteur';
    }

    public function isLivreur(): bool
    {
        return $this->user_type === 'livreur';
    }

    public function isClient(): bool
    {
        return $this->user_type === 'client';
    }
}