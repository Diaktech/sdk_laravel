<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReductionUtilisation extends Model
{
    protected $fillable = [
        'reduction_id',
        'client_id',
        'evenement_id'
    ];

    // Relations
    public function reduction() {
        return $this->belongsTo(ReductionPromotionnelle::class, 'reduction_id');
    }

    public function client() {
        return $this->belongsTo(Client::class);
    }

    public function evenement() {
        return $this->belongsTo(Evenement::class);
    }
}