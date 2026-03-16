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
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Models\Facture;
use App\Models\Paiement;
use App\Models\DetteClient;
use Carbon\Carbon;

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
        // 1. Identification du collecteur et de ses tarifs de revient
        $collecteur = Auth::user()->userable; // Accès au modèle Collecteur
        $tarifRevientVol = $collecteur->tarif_volume_revient; // Colonne 14

        // 1. Récupération des droits et tarifs officiels (au début de la fonction store)
        
        $tarifVenteDefaut = $collecteur->tarif_kilo_vente_defaut; //Colonne 18
        $tarifRevientPoids = $collecteur->tarif_kilo_revient; //Colonne 15

        // 2. Validation des données
        $validated = $request->validate([
            'depart_id'         => 'required|exists:departs,id',
            'client_id'         => 'required|exists:clients,id',
            'destinataire_id'   => 'nullable|exists:destinataires,id',
            'zone_id'           => 'required|exists:zones,id',
            'type_prise_charge' => 'required|in:depot,domicile',
            'articles'          => 'required|array|min:1',
            
            // Champs obligatoires pour chaque article
            'articles.*.article_id'          => 'required|exists:articles,id',
            'articles.*.quantite'            => 'required|numeric',
            'articles.*.prix_unitaire_saisi' => 'required|numeric',
            'articles.*.photo'               => 'required|image|max:10240', // Augmenté à 10Mo suite à nos échanges

            //'articles.*.is_lot'              => 'nullable|boolean', // On attend true/false ou 1/0
            'articles.*.is_lot' => 'nullable',
            
            // AJOUTE CES LIGNES POUR RÉCUPÉRER LES DONNÉES MANQUANTES :
            'articles.*.poids'        => 'nullable|numeric',
            'articles.*.longueur'     => 'nullable|numeric',
            'articles.*.largeur'      => 'nullable|numeric',
            'articles.*.hauteur'      => 'nullable|numeric',
            'articles.*.valeur_caf'   => 'nullable|numeric',
            'articles.*.etat'         => 'nullable|string',
            'articles.*.notes_defaut' => 'nullable|string',

            'montant_verse'        => 'required|numeric|min:0',
            'moyen_paiement'       => 'required|string',
            'signature_collecteur' => 'required|string',
            'signature_client'     => 'nullable|string',
            'type_calcul'          => 'required|string',
            'promo_id'              => 'nullable|numeric',
        ]);

        $majorationDomicile = ($validated['type_prise_charge'] === 'domicile') ? ($collecteur->majoration_domicile ?? 0.50) : 0;

        $depart = Depart::findOrFail($validated['depart_id']);

        try{
            return DB::transaction(function () use ($request, $validated, $collecteur, $depart, $tarifRevientVol, $tarifRevientPoids) {

                // Génération du code unique (Ex: 26021521301588)
                // format('ymdHis') donne 12 chiffres + rand(10, 99) donne 2 chiffres = 14 chiffres au total
                $codeUnique = now()->format('ymdHis') . rand(10, 99);
                
                // On vérifie si au moins un article est marqué comme "is_lot"
                $contientDesLots = collect($validated['articles'])->contains(function ($item) {
                    return filter_var($item['is_lot'] ?? false, FILTER_VALIDATE_BOOLEAN);
                });

                // A. Création de l'Événement (Header)
                $evenement = Evenement::create([
                    'depart_id'            => $validated['depart_id'],           // 2
                    'client_id'            => $validated['client_id'],           // 3
                    'collecteur_id'        => $collecteur->id,                   // 4
                    'destinataire_id'      => $validated['destinataire_id'] ?? null, // 5
                    'code_unique'          => $codeUnique,                       // 6
                    'type_prise_charge'    => $validated['type_prise_charge'],   // 7
                    'statut'               => 'en_attente',                      // 8
                    'priorite'             => 'normale',                         // 9
                    'zone_id'              => $validated['zone_id'],             // 10
                    'volume_total'         => 0.000,                             // 11
                    'poids_total'          => 0.00,                              // 12
                    'montant_total'        => 0.00,                              // 13
                    'contient_des_lots'    => $contientDesLots,                  // Enregistre 1 ou 0
                    'part_entite'          => 0.00,                              // 14
                    'commission_col'       => 0.00,                              // 15
                    'prix_kilo'            => $depart->prix_kilo,                // 16
                    'prix_m3'              => $depart->prix_m3,                  // 17
                    'facture_generee'      => 1,                                 // 18
                    'necessite_validation' => 0,                                 // 19
                    'valide_par_id'        => null,                              // 20
                    'date_validation'      => null,                              // 21
                    'note'                 => $request->commentaire_general,      // 22
                ]);

                $peutModifier = $collecteur->peut_modifier_tarif_vente; // Booléen Colonne 17

                $totalVolumeGlobal = 0; 
                $totalPoidsGlobal = 0;   
                $montant_total = 0;      
                $totalPartEntite = 0;    
                $commissionLigne = 0;   
                $commissionCollecteur=0; 
                $contientLot=0;

                // B. Boucle Articles (Logique conforme à ton JS)
                foreach ($validated['articles'] as $index => $itemData) {
                    $articleDB = Article::findOrFail($itemData['article_id']);

                    // Sécurité supplémentaire
                    if (!$request->hasFile("articles.$index.photo")) {
                        throw new \Exception("La photo pour l'article " . ($index + 1) . " est manquante.");
                    }
                    
                    // Photo
                    $photoPath = $request->file("articles.$index.photo")->store('evenements/items', 'public');

                    // Dimensions et Volume (Unitaire = Total de la ligne pour le calcul)
                    $longueur = $itemData['longueur'] ?? $articleDB->longueur ?? 0;
                    $largeur  = $itemData['largeur'] ?? $articleDB->largeur ?? 0;
                    $hauteur  = $itemData['hauteur'] ?? $articleDB->hauteur ?? 0;
                    
                    // 1. Calcul du volume pour UN seul objet
                    $volUnitaire = ($articleDB->mesures_fixes) ? $articleDB->volume : (($longueur * $largeur * $hauteur) / 1000000);


                    //POIDS
                    $poidsUnitaire = $itemData['poids'] ?? 0;
                    $pUnitaireSaisi = $itemData['prix_unitaire_saisi'];

                    // On convertit le "is_lot" reçu du JS en vrai booléen
                    $isLot = filter_var($itemData['is_lot'] ?? false, FILTER_VALIDATE_BOOLEAN);
                    $qte = (float) $itemData['quantite'];
                    $multiplicateur = $isLot ? $qte : 1;


                    $prixTotalLigneClient = 0;
                    $partEntiteLigne = 0;

                    if ($depart->type_calcul == 'volume') {
                        
                        // Logique Volume : Prix basé sur le m3
                        $prixTotalLigneClient = ($volUnitaire * $pUnitaireSaisi) * $multiplicateur; // !!!
    
                        $partEntiteLigne = ($volUnitaire * $tarifRevientVol) * $multiplicateur; // !!!
                    } else if($depart->type_calcul == 'poids'){
                        /**
                         * LOGIQUE POIDS (Mise à jour selon tes éléments) :
                         * 1. PrixUnitaireSaisi : Contient déjà (Base + Majoration domicile éventuelle) envoyé par le JS
                         * 2. Part Entité (TS) : Basée sur le tarif de revient manager (tarif_kilo_revient)
                         * 3. Commission : Ce qui reste après que l'entité a pris sa part
                         */

                        // ÉTAPE A : Déterminer le prix unitaire réel (Sécurité)
                        if ($peutModifier) {
                            // Le collecteur a le droit : on prend ce qu'il a saisi
                            $prixUnitFinal = (float) $pUnitaireSaisi;
                        } else {
                            // Le collecteur n'a PAS le droit : on force le calcul (Prix de base + Majoration)
                            // Même si le JS a envoyé autre chose, on l'écrase ici.
                            $prixUnitFinal = (float) ($tarifVenteDefaut + $majorationDomicile);

                            //Au cas ou le collecteur n'aurait pas le droit de modi
                            // fier et que la valeur serait différente, il faudrait journaliser cette anomalie
                        }
                        
                        // ÉTAPE B : Calculs des montants
                        // Le client paie : Poids * (Prix de base affiché + majoration domicile si applicable)
                        // Note : pUnitaireSaisi est la valeur finale de l'input dans l'interface, il faut ajouter 
                        $prixTotalLigneClient = ($poidsUnitaire * $prixUnitFinal) * $multiplicateur;

                        // L'entité (Manager) récupère toujours son tarif de revient par kilo
                        $partEntiteLigne = ($poidsUnitaire * $tarifRevientPoids) * $multiplicateur;
                    }else{
                        //Envoyer une erreur c'est ni un poids ni un volume. Problème sur le départ
                    }

                    // La commission du collecteur est le reliquat (inclut sa marge + la majoration domicile)
                    $commissionLigne = (float) $prixTotalLigneClient - $partEntiteLigne;

                    // Création de l'Item
                    $item = $evenement->items()->create([
                        //'evenement_id'        => $evenement->id,             // 2 //Doit se faire automatiquement
                        'article_id'          => $articleDB->id,             // 3
                        'quantite'            => $itemData['quantite'],      // 4
                        'is_lot'              => $isLot,
                        'longueur'            => $longueur,                  // 5
                        'largeur'             => $largeur,                   // 6
                        'hauteur'             => $hauteur,                   // 7
                        'poids'               => $poidsUnitaire,             // 8
                        'volume_unitaire'     => $volUnitaire,               // 9
                        'prix_total_client'   => $prixTotalLigneClient,      // 10
                        'part_entite_item'    => $partEntiteLigne,           // 11
                        'commission_col_item' => $commissionLigne,           // 12
                        'valeur_caf'          => $itemData['valeur_caf'] ?? 0.00, // 13
                        'etat'                => $itemData['etat'] ?? 'bon_etat', // 14
                        'notes_defaut'        => $itemData['notes_defaut'] ?? null, // 15
                        'photo_defaut_chemin' => $photoPath,                 // 16
                        'notes'               => null,                        // 17 (Champ additionnel libre)
                    ]);

                    DB::table('photo_evenements')->insert([
                        'evenement_id'      => $evenement->id,
                        'item_evenement_id' => $item->id, // On lie à l'article précis
                        'type_photo'        => 'defaut',  // Pour dire que c'est la photo de l'objet
                        'chemin_photo'      => $photoPath,
                        'prise_par'         => auth()->id(),
                        'created_at'        => now(),
                    ]);


                    //Cumul des sommes
                    $totalVolumeGlobal      += ($volUnitaire * $multiplicateur);
                    $totalPoidsGlobal       += ($poidsUnitaire * $multiplicateur);
                    $montant_total          += $prixTotalLigneClient;
                    $totalPartEntite        += $partEntiteLigne;
                    $commissionCollecteur   += $commissionLigne;
                
                } //Fin foreach

                $remiseMontant = 0;
                $promoId = $request->promo_id; // Récupéré du <input type="hidden">

                if ($promoId) {
                    // 1. On récupère la promo
                    $promo = \App\Models\ReductionPromotionnelle::find($promoId);
                    
                    // 2. On vérifie SI elle est toujours valide (sécurité serveur)
                    if ($promo && $promo->estValide($montant_total, $validated['type_prise_charge'], $validated['client_id'])) {
                        
                        // 3. On utilise la méthode de ton modèle pour calculer le montant exact
                        $remiseMontant = $promo->calculerRemise($montant_total);
                    } else {
                        // Si la promo n'est plus valide (ex: montant minimum plus atteint après modif),
                        // on remet l'ID à null pour ne pas l'enregistrer
                        $promoId = null;
                    }
                }            

                // 2. Détermination du montant net final calculé par le serveur
                $montantNetServeur = max(0, $montant_total - $remiseMontant);

                /**
                 * 3. COMPARAISON DE SÉCURITÉ
                 * On compare le total calculé ici ($montantNetServeur) avec celui envoyé par le JS ($request->total_js)
                 * On utilise abs(...) < 0.01 pour éviter les problèmes de précision des nombres flottants.
                 */
                $ecart = abs($montantNetServeur - (float)$request->total_js);

                if ($ecart > 0.01) {
                    \Log::warning("Écart de prix sur l'EXP {$code_unique}: JS({$request->total_js}) vs PHP({$montantNetServeur})");
                }

                // On synchronise l'objet pour la suite (Facture, Crédit)
                $evenement->update([
                    'volume_total'  => $totalVolumeGlobal,
                    'poids_total'   => $totalPoidsGlobal,
                    'montant_total' => $montantNetServeur,
                    'reduction_promotionnelle' => $remiseMontant,
                    'promo_id'                 => $promoId,
                    'part_entite'   => $totalPartEntite,   
                    'commission_col' => $commissionCollecteur,
                ]);

                // 5. VALIDATION OFFICIELLE DE LA PROMO
                if ($promoId && $remiseMontant > 0) {
                    // A. Incrémentation du compteur global
                    $promo->increment('nombre_utilisations_actuel');

                    // B. Enregistrement dans la table de liaison (Historique)
                    \App\Models\ReductionUtilisation::create([
                        'reduction_id' => $promoId,
                        'client_id'    => $evenement->client_id,
                        'evenement_id' => $evenement->id,
                    ]);
                }

                // D. Facture
                $montantTotal = $evenement->montant_total; // Somme des items
                $montantVerse = $validated['montant_verse'];
                $resteAPayer = $montantTotal - $montantVerse;

                // Détermination du statut de la facture
                $statutFacture = 'en_attente';
                if ($montantVerse > 0 && $resteAPayer > 0) $statutFacture = 'partiel';
                if ($resteAPayer <= 0) $statutFacture = 'paye';

                $deviseParDefaut = 'EUR';

                $facture = Facture::create([
                    'evenement_id' => $evenement->id,
                    'client_id' => $evenement->client_id,
                    'numero_facture' => 'FAC-' . date('Y') . '-' . Str::upper(Str::random(6)),
                    'devise'              => $deviseParDefaut,
                    'montant_remise'    => $remiseMontant,
                    'montant_total' => $montantNetServeur,
                    'montant_entite' => $evenement->part_entitee, // Part entreprise
                    'montant_collecteur' => $evenement->commission_col, // Part agent
                    'statut_paiement' => $statutFacture,
                    'est_entierement_payee' => ($resteAPayer <= 0),
                    'generee_par' => auth()->id(),
                    'date_generation' => now(),
                ]);

                // E. Paiement et Crédit
                if ($montantVerse > 0) {
                    Paiement::create([
                        'reference_interne' => 'REC-' . date('Y') . '-' . Str::upper(Str::random(6)),
                        'facture_id' => $facture->id,
                        'client_id' => $evenement->client_id,
                        'collecteur_id' => auth()->id(),
                        'montant' => $montantVerse,
                        'devise'              => $deviseParDefaut,
                        'moyen_paiement' => $validated['moyen_paiement'],
                        'type_paiement' => 'acompte',
                        'statut' => 'valide',
                        'date_enregistrement' => now(),
                    ]);
                }

                if ($resteAPayer > 0) {
                    $typeLibelle = ($montantVerse == 0) ? "Impayé total" : "Paiement partiel";
                    
                    DetteClient::create([
                        'client_id' => $evenement->client_id,
                        'evenement_id' => $evenement->id,
                        'facture_id' => $facture->id,
                        'montant_initial' => $resteAPayer,
                        'montant_restant' => $resteAPayer,
                        'statut' => 'actif',
                        'type' => 'reliquat',
                        'justification' => "{$typeLibelle} - Expédition {$evenement->code_unique}. Versé: {$montantVerse} / Total: {$montantTotal}.",
                        'cree_par' => auth()->id(),
                    ]);
                }

                //Signature
                $signatures = [
                    'client'     => $request->input('signature_client'),
                    'collecteur' => $request->input('signature_collecteur')
                ];

                foreach ($signatures as $type => $base64Data) {
                    if ($base64Data) {
                        $image = str_replace('data:image/png;base64,', '', $base64Data);
                        $image = str_replace(' ', '+', $image);
                        $fileName = "sig_{$type}_{$evenement->id}_" . time() . ".png";
                        $path = "signatures/{$fileName}";
                        \Storage::disk('public')->put($path, base64_decode($image));

                        DB::table('signature_evenements')->insert([
                            'evenement_id'     => $evenement->id,
                            'type_signature'   => $type,
                            'chemin_signature' => $path,
                            'signe_par'        => Auth::id(),
                            'ip_adresse'       => $request->ip(),
                            'created_at'       => now(),
                        ]);
                    }
                }

                // F. Logistique et Historique
                $depart->increment('volume_actuel', $evenement->volume_total);
                
                DB::table('historique_statuts_evenements')->insert([
                    'evenement_id' => $evenement->id,
                    'statut'       => 'en_attente',
                    'modifie_par'  => Auth::id(),
                    'date_changement' => now(),
                ]);

                return response()->json([
                    'success' => true,
                    'redirect_url' => route('collecteur.evenements.show', $evenement->id),
                    'message' => 'Collecte enregistrée avec succès'
                ]);
            });
        }catch (\Exception $e) {
            // En cas d'erreur, Laravel fait déjà le Rollback grâce à DB::transaction()
            // On log l'erreur pour pouvoir la corriger
            \Log::error("Erreur lors de l'enregistrement de la collecte : " . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Une erreur technique est survenue : ' . $e->getMessage()
            ], 500);
        }
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