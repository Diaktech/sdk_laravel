<?php
// ====================================================
// FICHIER: app/Http/Controllers/Collecteur/ClientController.php
// CONTROLEUR: Gestion des clients pour les collecteurs
// CRÉATION: [20/12/2025]
// ====================================================

namespace App\Http\Controllers\Collecteur;

use App\Http\Controllers\Controller; // Contrôleur de base Laravel
use App\Models\Client; // Modèle Client
use Illuminate\Http\Request; // Pour gérer les requêtes HTTP
use Illuminate\Support\Facades\Auth; // Pour l'authentification

class ClientController extends Controller
{
    /**
     * ============================================
     * MÉTHODE: search()
     * BUT: Rechercher des clients via AJAX
     * URL: GET /collecteur/clients/search?q=terme
     * ============================================
     * 
     * Recherche dans:
     * 1. ID unique (exact match)
     * 2. Téléphone (LIKE %term%)
     * 3. Email (LIKE %term%)
     * 4. Nom + Prénom (LIKE %term%)
     * 
     * @param Request $request Contient le paramètre 'q'
     * @return \Illuminate\Http\JsonResponse Clients trouvés (max 5)
     */
    public function search(Request $request)
    {
        // 1. Récupérer le collecteur connecté
        $collecteur = Auth::user()->userable;
        
        // 2. Nettoyer et récupérer le terme de recherche
        $query = trim($request->get('q', ''));

        // 3. NOUVEAU : Récupérer le type de filtre (directs, groupe, partages)
        $type = $request->get('type', 'directs'); // Valeur par défaut : clients directs
        
        // 4. Validation: minimum 2 caractères (sauf pour ID numérique)
        if (strlen($query) < 2 && !is_numeric($query)) {
            return response()->json([]); // Retour vide
        }

        // 5. CHOISIR LA REQUÊTE SELON LE TYPE
        switch($type) {
            case 'groupe':
                $clientsQuery = $this->getClientsGroupeQuery($collecteur);
                break;
            case 'partages':
                $clientsQuery = $this->getClientsPartagesQuery($collecteur);
                break;
            default: // 'directs' (ton code actuel)
                $clientsQuery = Client::where('collecteur_principal_id', $collecteur->id);
        }
        
        // 6. Recherche dans la base de données
        $clients = $clientsQuery->where(function($q) use ($query) {
            // 🔍 CRITÈRE 1: ID unique exact
            $q->where('unique_id', $query);
            
            // 🔍 CRITÈRE 2: Téléphone (recherche partielle)
            $q->orWhere('telephone', 'LIKE', "%{$query}%");
            
            // 🔍 CRITÈRE 3: Email (recherche partielle)
            //$q->orWhere('email', 'LIKE', "%{$query}%"); Pas de mail dans la table client
            
            // 🔍 CRITÈRE 3: Nom et/ou prénom
            if (strpos($query, ' ') !== false) {
                // Si espace dans la recherche: "nom prénom" ou "prénom nom"
                $q->orWhereRaw("CONCAT(nom, ' ', prenom) LIKE ?", ["%{$query}%"]);
                $q->orWhereRaw("CONCAT(prenom, ' ', nom) LIKE ?", ["%{$query}%"]);
            } else {
                // Sinon: chercher dans nom OU prénom
                $q->orWhere('nom', 'LIKE', "%{$query}%");
                $q->orWhere('prenom', 'LIKE', "%{$query}%");
            }
        })
            // 📊 TRI INTELLIGENT: priorité aux correspondances exactes
            ->orderByRaw("
                CASE 
                    WHEN unique_id = ? THEN 1        -- ID exact = priorité 1
                    WHEN telephone LIKE ? THEN 2     -- Téléphone = priorité 2
                    ELSE 3                           -- Nom/prénom = priorité 3
                END
            ", [$query, "%{$query}%"])
            // 📊 TRI ALPHABÉTIQUE pour les égalités de priorité
            ->orderBy('nom')
            ->orderBy('prenom')
            ->limit(5) // 🔒 Limiter à 5 résultats max (performance + UX)
            ->get(['id', 'unique_id', 'prenom', 'nom', 'telephone']); // 🎯 Seulement les champs nécessaires
        
        // 7. Retourner les résultats en JSON
        return response()->json($clients);
    }
    
    
    /**
     * ============================================
     * MÉTHODE: destinataires()
     * BUT: Récupérer les destinataires d'un client
     * URL: GET /collecteur/clients/{id}/destinataires
     * ============================================
     * 
     * @param int $clientId ID du client
     * @return \Illuminate\Http\JsonResponse Destinataires du client
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException Si client non trouvé
     */
    public function destinataires($clientId)
    {
        // 1. Récupérer le collecteur connecté
        $collecteur = Auth::user()->userable;
        
        // 2. Vérifier que le client existe ET appartient au collecteur
        // 🔐 Sécurité: empêche l'accès aux clients d'autres collecteurs
        $client = Client::where('id', $clientId)
            ->where('collecteur_principal_id', $collecteur->id)
            ->firstOrFail(); // ❌ Retourne 404 si non trouvé
        
        // 3. Récupérer les destinataires du client
        $destinataires = $client->destinataires()
            ->orderBy('nom')    // 📊 Trier par nom
            ->orderBy('prenom') // 📊 Puis par prénom
            ->get(['id', 'code_unique', 'prenom', 'nom', 'telephone', 'adresse', 'zone_id']);
        
        // 4. Retourner les destinataires en JSON
        return response()->json($destinataires);
    }

    /**
     * Requête pour les clients du groupe
     */
    private function getClientsGroupeQuery($collecteur)
    {
        // Clients qui sont dans les mêmes groupes que le collecteur
        return Client::whereHas('groupes', function($q) use ($collecteur) {
            $q->whereHas('collecteurs', function($q2) use ($collecteur) {
                $q2->where('collecteur_id', $collecteur->id);
            });
        });
    }

    /**
     * Requête pour les clients partagés
     */
    private function getClientsPartagesQuery($collecteur)
    {
        // Clients partagés AVEC ce collecteur (validés par manager)
        // Partagés par d'autres collecteurs, pas par lui-même
        return Client::whereHas('groupes', function($q) use ($collecteur) {
            $q->where('partage_par', '!=', $collecteur->id) // Pas partagé PAR lui
            ->whereNotNull('approuve_par'); // Validé par manager
        });
    }    
}