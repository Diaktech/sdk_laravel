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
    public function create()
    {
        $collecteur = Auth::user()->userable;
        
        $departs = Depart::where('entite_id', $collecteur->entite_id)
            ->where('statut', 'ouvert')
            ->get();
                
        $clients = Client::where('collecteur_principal_id', $collecteur->id)
            ->orderBy('nom')
            ->orderBy('prenom')
            ->get(['id', 'unique_id', 'prenom', 'nom']); // Seulement les champs nécessaires
                
        return view('collecteur.evenements.create', [
            'departs' => $departs,
            'clients' => $clients, // Léger
            // Pas de $clientsData !
        ]);
    }

    /**
     * Enregistrer un nouvel événement
     */
    public function store(Request $request)
    {
        $collecteur = Auth::user()->userable;
        
        // Validation
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
            'items.*.poids' => 'nullable|numeric|min:0',
            'items.*.etat' => 'required|in:bon_etat,defaut',
            'items.*.notes_defaut' => 'nullable|string',
        ]);
        
        // Créer l'événement
        $evenement = Evenement::create([
            'depart_id' => $validated['depart_id'],
            'client_id' => $validated['client_id'],
            'collecteur_id' => $collecteur->id,
            'destinataire_id' => $validated['destinataire_id'] ?? null,
            'code_unique' => 'EXP' . date('Y') . str_pad(Evenement::count() + 1, 5, '0', STR_PAD_LEFT),
            'type_prise_charge' => $validated['type_prise_charge'],
            'statut' => 'en_attente',
            'priorite' => 'normale',
        ]);
        
        // Ajouter les items
        foreach ($validated['items'] as $itemData) {
            $item = new ItemEvenement($itemData);
            
            // Calculer le volume si dimensions fournies
            if ($itemData['longueur'] && $itemData['largeur'] && $itemData['hauteur']) {
                $item->volume_calcule = ($itemData['longueur'] * $itemData['largeur'] * $itemData['hauteur']) / 1000000; // cm³ → m³
            }
            
            $evenement->items()->save($item);
        }
        
        // Recalculer les totaux de l'événement
        $evenement->calculerTotaux();
        
        // Mettre à jour le volume actuel du départ
        $depart = Depart::find($validated['depart_id']);
        $depart->volume_actuel += $evenement->volume_total;
        $depart->save();
        
        return redirect()->route('collecteur.evenements.show', $evenement)
            ->with('success', 'Prise en charge créée avec succès !');
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