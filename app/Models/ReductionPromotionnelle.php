<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class ReductionPromotionnelle extends Model
{
    protected $fillable = [
        'code', 'libelle', 'type', 'valeur', 'plafond_remise',
        'montant_minimum_commande', 'usage_max_total', 'usage_max_par_client',
        'nombre_utilisations_actuel', 'date_debut', 'date_fin',
        'cumulable', 'is_active', 'type_calcul_autorise', 'entite_id', 'client_id'
    ];

    protected $casts = [
        'date_debut' => 'datetime',
        'date_fin' => 'datetime',
        'is_active' => 'boolean',
        'is_automatique' => 'boolean',
        'cumulable' => 'boolean',
    ];

    /**
     * Vérifie si la promotion peut être appliquée
     */
    public function estValide($montantCommande, $typeCalcul, $clientId = null)
    {
        $now = Carbon::now();

        // 1. Basique : Active ?
        if (!$this->is_active) return false;

        // 2. Dates
        if ($this->date_debut && $now->lt($this->date_debut)) return false;
        if ($this->date_fin && $now->gt($this->date_fin)) return false;

        // 3. Montant minimum
        if ($montantCommande < $this->montant_minimum_commande) return false;

        // 4. Type de calcul (Volume / Poids)
        if ($this->type_calcul_autorise !== 'tous' && $this->type_calcul_autorise !== $typeCalcul) return false;

        // 5. Quotas globaux
        if ($this->usage_max_total && $this->nombre_utilisations_actuel >= $this->usage_max_total) return false;

        // 6. Restriction client spécifique
        if ($this->client_id && $this->client_id !== $clientId) return false;

        return true;
    }

    /**
     * Calcule le montant de la réduction
     */
    public function calculerRemise($total)
    {
        if ($this->type === 'fixe') {
            return min($this->valeur, $total);
        }

        $remise = ($total * $this->valeur) / 100;
        
        // Appliquer le plafond si défini
        if ($this->plafond_remise) {
            $remise = min($remise, $this->plafond_remise);
        }

        return $remise;
    }

    /**
     * Recherche une promotion automatique éligible
     */
    public static function getAutoPromo($entiteId, $clientId, $montant, $typeCalcul)
    {
        return self::where('entite_id', $entiteId)
            ->where('is_active', true)
            ->where('is_automatique', true)
            ->where(function($q) use ($clientId) {
                $q->where('client_id', $clientId) // Promo spécifique au client
                ->orWhereNull('client_id');    // Ou promo automatique globale
            })
            ->get()
            ->filter(function($promo) use ($montant, $typeCalcul, $clientId) {
                return $promo->estValide($montant, $typeCalcul, $clientId);
            })
            ->first(); // On prend la première (ou tu peux trier par la plus avantageuse)
    }


}