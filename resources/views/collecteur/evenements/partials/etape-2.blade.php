
    
<div class="mb-10">
    <h3 class="text-2xl font-black text-gray-900 mb-6 uppercase tracking-tight">
        1. Sélectionnez une famille
    </h3>
    
    <div class="grille-familles-premium" id="grille-familles">
        @foreach($familles as $famille)
            <button type="button" 
                    class="btn-famille-tuile"
                    data-famille-id="{{ $famille->id }}">
                <span class="nom-famille">{{ $famille->nom }}</span>
                <span class="compteur-articles">{{ $famille->articles->count() }} articles</span>
            </button>
        @endforeach
    </div>
</div>
<input type="hidden" id="tarif_revient_poids_entite" value="{{ $collecteur->tarif_kilo_revient }}">
<input type="hidden" id="tarif_poids_defaut" value="{{ $collecteur->tarif_kilo_vente_defaut}}">
<input type="hidden" id="tarif_revient_vol_entite" value="{{ $collecteur->tarif_volume_revient }}"> 
<input type="hidden" id="majoration_domicile" value="{{ $collecteur->majoration_domicile ?? 0 }}">

<div id="section-selection-article" class="hidden mt-10 p-6 bg-gray-50 rounded-2xl border-2 border-dashed border-gray-200">
    <h3 class="text-xl font-bold text-gray-800 mb-4 tracking-tight">2. Rechercher l'article précis</h3>
    <div class="flex flex-col md:flex-row gap-4">
        <div class="flex-1">
            <select id="select-article-search" class="w-full"></select>
        </div>
        <button type="button" id="btn-ajouter-article" class="bg-blue-600 text-white px-8 py-3 rounded-xl font-bold hover:bg-blue-700 transition">
            AJOUTER À LA LISTE
        </button>
    </div>
</div>

<div class="mt-8">
    <h3 class="text-xl font-bold text-gray-800 mb-4 uppercase tracking-tight">Liste des articles</h3>
    
    <div class="overflow-x-auto shadow-sm border border-gray-200 rounded-xl bg-white custom-scrollbar">
        <table class="w-full text-left border-collapse min-w-[800px]"> <thead class="bg-gray-50">
                <tr>
                    <th class="px-3 py-4 text-xs font-black uppercase text-gray-500 sticky-column">Article</th>
                    <th class="px-3 py-4 text-xs font-black uppercase text-gray-500">Quantité</th>
                    <th class="px-3 py-4 text-xs font-black uppercase text-gray-500">Dimensions (cm)</th>
                    <th class="px-3 py-4 text-xs font-black uppercase text-gray-500">Poids (kg)</th>
                    <th class="px-3 py-4 text-xs font-black uppercase text-gray-500">Vol (m³)</th>
                    <th class="px-3 py-4 text-xs font-black uppercase text-gray-500">Prix / m³</th>
                    <th class="px-3 py-4 text-xs font-black uppercase text-gray-500 text-right">Total</th>
                    <th class="px-3 py-4 text-xs font-black uppercase text-gray-500 text-center">Action</th>
                </tr>
            </thead>
            <tbody id="liste-articles-body" class="divide-y divide-gray-100">
                <tr id="empty-list-message">
                    <td colspan="8" class="px-3 py-10 text-center text-gray-400 italic">
                        Aucun article ajouté pour le moment.
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>


<div class="summary-box shadow-2xl">
    <div class="text-center mb-4">
        <span id="type-prise-en-charge-title" class="px-4 py-1 bg-slate-800 rounded-full text-[10px] font-bold tracking-widest text-yellow-400 uppercase border border-slate-700">
            Mode de calcul : --
        </span>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
        <div class="flex flex-col justify-between space-y-4">
            <div>
                <span class="block text-xs font-bold text-slate-400 uppercase tracking-widest mb-1">Chargement Total</span>
                <div class="flex items-baseline">
                    <span id="total-volume-display" class="text-3xl font-black text-white">0.000</span>
                    <span class="text-slate-400 ml-1 font-bold" id="unit-display">m³</span>
                </div>
            </div>

            <div class="flex flex-col space-y-1 pt-2 border-t border-slate-800">
                <div class="flex items-center space-x-2">
                    <span class="text-[10px] font-bold text-slate-500 uppercase tracking-tighter">Poids Total :</span>
                    <span id="total-poids-display" class="text-sm font-bold text-slate-300">0.00 kg</span>
                </div>
                <div class="flex items-center space-x-2">
                    <span class="text-[10px] font-bold text-slate-500 uppercase tracking-tighter">Quantité :</span>
                    <span id="total-qte-display" class="text-sm font-bold text-slate-300">0</span>
                </div>
            </div>
        </div>

        <div class="text-left border-y md:border-y-0 md:border-x border-slate-800 py-4 md:px-8 md:py-0">
            <span class="block text-xs font-bold text-blue-400 uppercase tracking-widest mb-1">Total Facturé Client</span>
            <div class="flex items-baseline">
                <span id="total-prix-display" class="text-4xl font-black text-blue-400">0.00</span>
                <span class="text-blue-400/50 ml-1 font-bold">€</span>
            </div>
        </div>

        <div class="text-left md:text-right">
            <span class="block text-xs font-bold text-emerald-400 uppercase tracking-widest mb-1">Votre Gain (Marge)</span>
            <div class="flex items-baseline md:justify-end">
                <span id="total-commission-display" class="text-4xl font-black text-emerald-400">0.00</span>
                <span class="text-emerald-400/50 ml-1 font-bold">€</span>
            </div>
        </div>
    </div>
</div>

<div class="mt-4">
    <label class="block text-sm font-bold text-gray-700 mb-2">Commentaire général sur l'envoi</label>
    <textarea id="commentaire_general" name="commentaire_general" rows="2" class="w-full border rounded-lg p-2 text-sm" placeholder="Observations particulières..."></textarea>
</div>