<?php 

namespace App\Http\Controllers\Collecteur;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ReductionPromotionnelle;
use Illuminate\Support\Facades\Auth;

class PromotionController extends Controller
{
    public function verifier(Request $request)
    {
        try {
            // 1. Récupération sécurisée des inputs
            $total = (float) $request->input('total', 0);
            $clientId = $request->input('client_identifiant'); // Reçoit l'ID technique (ex: 1)
            $typeCalcul = $request->input('type_calcul', 'volume');
            
            // 2. Récupération de l'entité de l'utilisateur connecté
            $user = Auth::user();
            // Laravel va chercher automatiquement dans la table 'collecteurs' 
            // grâce à la relation polymorphique 'userable'
            $collecteur = $user->userable;

            // 3. Vérification de sécurité
            if (!$collecteur || !($collecteur instanceof \App\Models\Collecteur)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Accès refusé : vous devez être connecté en tant que collecteur.'
                ], 403);
            }

            // 4. Récupérer l'entité_id
            $entiteId = $collecteur->entite_id;

            // 3. Recherche de la meilleure promotion automatique
            $promo = ReductionPromotionnelle::where('entite_id', $entiteId)
                ->where('is_active', true)
                ->where('is_automatique', true)
                ->where(function($q) use ($clientId) {
                    $q->where('client_id', $clientId)
                    ->orWhereNull('client_id');
                })
                ->orderByRaw('client_id DESC') // Donne la priorité à la promo spécifique client
                ->get()
                ->first(function($item) use ($total, $typeCalcul, $clientId) {
                    // La méthode estValide doit exister dans ton modèle
                    return $item->estValide($total, $typeCalcul, $clientId);
                });

            // 4. Réponse JSON
            if ($promo) {
                $montantRemise = $promo->calculerRemise($total);
                return response()->json([
                    'success' => true,
                    'promo_id' => $promo->id,
                    'libelle' => $promo->libelle,
                    'montant_remise' => $montantRemise,
                    'nouveau_total' => max(0, $total - $montantRemise)
                ]);
            }

            return response()->json(['success' => false]);

        } catch (\Exception $e) {
            // En cas de bug, on renvoie un JSON propre au lieu d'une page d'erreur HTML
            return response()->json([
                'success' => false,
                'message' => 'Erreur serveur: ' . $e->getMessage()
            ], 500);
        }
    }
}