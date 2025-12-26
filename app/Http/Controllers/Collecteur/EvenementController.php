<?php

namespace App\Http\Controllers\Collecteur;

use App\Http\Controllers\Controller;
use App\Models\Evenement;
use App\Models\Depart;
use App\Models\Destinataire;
use App\Models\Article;
use App\Models\ItemEvenement;
use App\Models\Client;
use App\Models\Famille; 
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EvenementController extends Controller
{
    /**
     * Afficher la liste des événements du collecteur
     */
    public function index()
    {
        $collecteur = Auth::user()->userable; // Récupère le collecteur connecté
        
        $evenements = Evenement::where('collecteur_id', $collecteur->id)
            ->with(['depart', 'client', 'destinataire'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);
            
        return view('collecteur.evenements.index', compact('evenements'));
    }

    /**
     * Afficher le formulaire de création d'un événement
     */
    public function create(Request $request)
    {
        $collecteur = Auth::user()->userable;
        //$entite = Auth::user()->entite; // On récupère l'entité du collecteur

        $entite = $collecteur->entite;
        
        // $departs = Depart::where('entite_id', $collecteur->entite_id)    //Modifié car le collecteur peut prendre en charge sur des départ entité 0 (Ouvert à toutes les entités)
        //     ->where('statut', 'ouvert')
        //     ->get();

        $departs = Depart::whereIn('entite_id', [$collecteur->entite_id, 0])
        ->where('statut', 'ouvert')
        ->orderBy('date_depart', 'asc')
        ->get();
                
        $clients = Client::where('collecteur_principal_id', $collecteur->id)
            ->orderBy('nom')
            ->orderBy('prenom')
            ->get(['id', 'unique_id', 'prenom', 'nom']); // Seulement les champs nécessaires

        //Récupération des Familles avec leur articles
        $familles = Famille::with(['articles' => function($query) {
            $query->orderBy('libelle');
        }])->orderBy('nom')->get();

        $departId = session('depart_id') ?? $request->query('depart_id');
        $depart = Depart::find($departId);
                
        return view('collecteur.evenements.create', [
            'departs' => $departs,
            'clients' => $clients, 
            'familles' => $familles,
            'depart'   => $depart, // Pour le type de facturation
            'entite'   => $entite, // <--- pour window.tarifVolumeParDefaut
            'collecteur' => $collecteur,
        ]);
    }

    /**
     * Enregistrer un nouvel événement
     */
    public function store(Request $request)
    {
        $collecteur = Auth::user()->userable;

        // 1. Validation stricte
        $validated = $request->validate([
            'depart_id' => 'required|exists:departs,id',
            'client_id' => 'required|exists:clients,id',
            'destinataire_id' => 'nullable|exists:destinataires,id',
            'type_prise_charge' => 'required|in:depot,domicile',
            'items' => 'required|array|min:1',
            'items.*.article_id' => 'required|exists:articles,id',
            'items.*.quantite' => 'required|integer|min:1',
            'items.*.longueur' => 'nullable|numeric|min:0',
            'items.*.largeur' => 'nullable|numeric|min:0',
            'items.*.hauteur' => 'nullable|numeric|min:0',
            'items.*.poids' => 'nullable|numeric|min:0', // Pour le calcul au kilo
            'items.*.etat' => 'required|in:neuf,occasion,abime',
        ]);

        // 2. Récupérer le départ pour connaître les tarifs (prix_m3 ou prix_kilo)
        $depart = Depart::findOrFail($validated['depart_id']);

        // --- SÉCURITÉ SUPPLÉMENTAIRE ---
        // On vérifie que le départ appartient soit à l'entité du collecteur, soit est un départ ouvert (0)
        if ($depart->entite_id !== $collecteur->entite_id && $depart->entite_id !== 0) {
            return redirect()->back()
                ->withErrors(['depart_id' => 'Ce départ n’est pas autorisé pour votre entité.'])
                ->withInput();
        }

        // 3. Créer l'événement de base
        $evenement = Evenement::create([
            'depart_id' => $validated['depart_id'],
            'client_id' => $validated['client_id'],
            'collecteur_id' => $collecteur->id,
            'destinataire_id' => $validated['destinataire_id'] ?? null,
            'code_unique' => 'EXP' . date('Y') . str_pad(Evenement::count() + 1, 5, '0', STR_PAD_LEFT),
            'type_prise_charge' => $validated['type_prise_charge'],
            'statut' => 'en_attente',
        ]);

        // 4. Boucle sur les articles pour sécuriser les calculs
        foreach ($validated['items'] as $itemData) {
            $articleDB = \App\Models\Article::find($itemData['article_id']);
            
            // --- SÉCURITÉ : Mesures Fixes ---
            // Si l'article est à mesures fixes, on ignore ce qui vient du formulaire
            if ($articleDB->mesures_fixes) {
                $longueur = $articleDB->longueur;
                $largeur = $articleDB->largeur;
                $hauteur = $articleDB->hauteur;
                $volumeUnit = $articleDB->volume;
            } else {
                $longueur = $itemData['longueur'];
                $largeur = $itemData['largeur'];
                $hauteur = $itemData['hauteur'];
                $volumeUnit = ($longueur * $largeur * $hauteur) / 1000000;
            }

            // --- CALCUL DES PRIX ---
            $prixUnitaire = 0;
            
            // Si le départ est au volume
            if ($depart->type_facturation == 'volume') {
                $prixUnitaire = $volumeUnit * $depart->prix_m3;
            } 
            // Si le départ est au poids
            elseif ($depart->type_facturation == 'poids') {
                $poidsSaisi = $itemData['poids'] ?? $articleDB->poids_moyen ?? 0;
                $prixUnitaire = $poidsSaisi * $depart->prix_kilo;
            }

            // --- ENREGISTREMENT DE L'ITEM ---
            $evenement->items()->create([
                'article_id' => $articleDB->id,
                'quantite' => $itemData['quantite'],
                'longueur' => $longueur,
                'largeur' => $largeur,
                'hauteur' => $hauteur,
                'poids' => $itemData['poids'] ?? 0,
                'volume_unitaire' => $volumeUnit,
                'prix_unitaire' => $prixUnitaire,
                'prix_total' => $prixUnitaire * $itemData['quantite'],
                'etat' => $itemData['etat'],
            ]);
        }

        // 5. Finalisation
        $evenement->calculerTotaux(); // Méthode dans ton modèle Evenement
        
        // Mise à jour du remplissage du départ
        $depart->increment('volume_actuel', $evenement->volume_total);

        return redirect()->route('collecteur.evenements.show', $evenement)
            ->with('success', 'Prise en charge enregistrée et prix calculés !');
    }

    /**
     * Afficher un événement spécifique
     */
    public function show(Evenement $evenement)
    {
        // Vérifier que l'événement appartient au collecteur
        if ($evenement->collecteur_id !== Auth::user()->userable->id) {
            abort(403);
        }
        
        $evenement->load(['items.article', 'depart', 'client', 'destinataire.zone']);
        
        return view('collecteur.evenements.show', compact('evenement'));
    }
}