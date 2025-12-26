<div class="bg-white rounded-xl shadow-lg p-8">
    <h3 class="text-2xl font-bold text-gray-800 mb-8 pb-4 border-b border-gray-200">
        📋 Informations générales
    </h3>
    
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        
        <!-- Colonne gauche : Départ et Client -->
        <div class="space-y-8">
            
            <!-- Sélection du départ -->
            <div class="bg-blue-50 border border-blue-100 rounded-lg p-6">
                <div class="flex items-center mb-4">
                    <div class="bg-blue-100 text-blue-800 rounded-lg p-2 mr-3">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <h4 class="text-lg font-semibold text-gray-800">Départ (Conteneur)</h4>
                </div>
                
                <div>
                    <label for="depart_id" class="block text-sm font-medium text-gray-700 mb-2">
                        Sélectionnez un départ disponible *
                    </label>
                        <select name="depart_id" id="depart_id" required
                                class="w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500 py-3 px-4 text-lg">
                            <option value="">-- Choisir un départ --</option>
                            @foreach($departs as $depart)
                                @php
                                    $dateDepart = \Carbon\Carbon::parse($depart->date_depart);
                                    // diffInDays avec 'false' permet d'avoir le signe négatif si la date est passée
                                    // On utilise (int) pour supprimer les décimales
                                    $joursRestants = (int) \Carbon\Carbon::now()->startOfDay()->diffInDays($dateDepart->startOfDay(), false);
                                    
                                    if($joursRestants < 0) {
                                        $delaiText = "Fermé";
                                    } elseif($joursRestants == 0) {
                                        $delaiText = "Aujourd'hui";
                                    } else {
                                        $delaiText = $joursRestants . " jrs";
                                    }
                                @endphp
                                
                                <option value="{{ $depart->id }}" 
                                        data-volume-max="{{ $depart->volume_maximal }}"
                                        data-volume-actuel="{{ $depart->volume_actuel }}"
                                        data-type-calcul="{{ $depart->type_calcul }}">
                                    
                                    🚚 [{{ $dateDepart->format('d/m') }} | {{ $delaiText }}] 
                                    {{ $depart->lieu_depart }} ➔ {{ $depart->lieu_arrivee }} 
                                    ({{ $depart->type_calcul == 'poids' ? 'Kg' : 'm³' }}) 
                                    [{{ number_format($depart->volume_actuel, 1) }}/{{ (int)$depart->volume_maximal }}]
                                    
                                </option>
                            @endforeach
                        </select>
                    @error('depart_id')
                        <p class="text-red-500 text-sm mt-2">{{ $message }}</p>
                    @enderror
                </div>
                
                <!-- Capacité restante -->
                <div id="statut-remplissage-container" class="mt-4 p-4 rounded-xl border-2 border-dashed border-gray-200 transition-all duration-300">
                    <div class="flex flex-col items-center justify-center space-y-1">
                        <span class="text-xs font-bold uppercase tracking-wider text-gray-500">Taux d'occupation</span>
                        
                        <div id="capacite-percent" class="text-4xl font-black text-gray-400">0%</div>
                        
                        <div id="capacite-restante" class="text-sm font-medium text-gray-600">-- / -- m³</div>
                    </div>
                </div>

                <div class="w-full bg-gray-200 rounded-full h-1.5 mt-3">
                    <div id="barre-progression" class="bg-gray-400 h-1.5 rounded-full transition-all duration-500" style="width: 0%"></div>
                </div>
            </div>
            
            <!-- =========================================== -->
            <!-- NOUVELLE SECTION : RECHERCHE DE CLIENT     -->
            <!-- =========================================== -->
            <div class="bg-purple-50 border border-purple-100 rounded-lg p-6">
                <div class="flex items-center mb-4">
                    <div class="bg-purple-100 text-purple-800 rounded-lg p-2 mr-3">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                        </svg>
                    </div>
                    <h4 class="text-lg font-semibold text-gray-800">Client (Expéditeur)</h4>
                </div>
                
                <!-- ==================== CHAMP DE RECHERCHE ==================== -->
                <div class="mb-6">
                    <label for="client_search" class="block text-sm font-medium text-gray-700 mb-2">
                        Rechercher un client *
                    </label>

                    <!-- ==================== BOUTONS DE FILTRE ==================== -->
                    <div class="mb-4">
                        <div class="flex flex-wrap gap-2 mb-2">
                            <!-- Bouton par défaut (clients directs) -->
                            <button type="button" 
                                    id="btn-clients-directs"
                                    class="filter-btn active px-4 py-2 bg-purple-100 border-2 border-purple-500 text-purple-700 rounded-lg font-medium text-sm hover:bg-purple-200 transition-all duration-200 shadow-sm">
                                <span class="flex items-center">
                                    <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"/>
                                    </svg>
                                    Mes clients
                                </span>
                            </button>
                            
                            <!-- Bouton clients de groupe -->
                            <button type="button" 
                                    id="btn-clients-groupe"
                                    class="filter-btn px-4 py-2 bg-gray-100 border-2 border-gray-300 text-gray-600 rounded-lg font-medium text-sm hover:bg-gray-200 transition-all duration-200 shadow-sm">
                                <span class="flex items-center">
                                    <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M13 6a3 3 0 11-6 0 3 3 0 016 0zm-8 9a3 3 0 100-6 3 3 0 000 6zm10 0a3 3 0 100-6 3 3 0 000 6z" clip-rule="evenodd"/>
                                    </svg>
                                    Clients du groupe
                                </span>
                            </button>
                            
                            <!-- Bouton clients partagés -->
                            <button type="button" 
                                    id="btn-clients-partages"
                                    class="filter-btn px-4 py-2 bg-gray-100 border-2 border-gray-300 text-gray-600 rounded-lg font-medium text-sm hover:bg-gray-200 transition-all duration-200 shadow-sm">
                                <span class="flex items-center">
                                    <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M12.586 4.586a2 2 0 112.828 2.828l-3 3a2 2 0 01-2.828 0 1 1 0 00-1.414 1.414 4 4 0 005.656 0l3-3a4 4 0 00-5.656-5.656l-1.5 1.5a1 1 0 101.414 1.414l1.5-1.5zm-5 5a2 2 0 012.828 0 1 1 0 101.414-1.414 4 4 0 00-5.656 0l-3 3a4 4 0 105.656 5.656l1.5-1.5a1 1 0 10-1.414-1.414l-1.5 1.5a2 2 0 11-2.828-2.828l3-3z" clip-rule="evenodd"/>
                                    </svg>
                                    Clients partagés
                                </span>
                            </button>
                        </div>
                        
                        <!-- Indicateur du filtre actif -->
                        <div class="text-xs text-gray-500 mt-1 px-1">
                            <span class="font-semibold" id="filtre-type">Mes clients</span>
                            <span class="text-gray-400"> • Recherche dans vos clients directs</span>
                        </div>
                    </div>
                    <!-- ==================== FIN BOUTONS FILTRE ==================== -->

                    <!-- Champ caché pour l'ID du client (pour le formulaire) -->
                    <input type="hidden" id="client_id" name="client_id" value="">
                    
                    <!-- Champ de recherche visible -->
                    <div class="relative">
                        <input type="text" 
                            id="client_search"
                            class="w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500 py-3 px-4 pr-10"
                            placeholder="ID, téléphone, ou nom du client..."
                            autocomplete="off"
                            spellcheck="false">
                        
                        <!-- Icône de recherche -->
                        <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                            <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>
                        </div>
                    </div>
                    
                    <!-- Indicateur de saisie minimum -->
                    <p class="mt-1 text-xs text-gray-500">
                        Minimum 2 caractères. Tapez l'ID, téléphone, ou nom.
                    </p>
                </div>
                
                <!-- ==================== RÉSULTATS DE RECHERCHE ==================== -->
                <div id="client_results" class="hidden mb-4 border border-gray-200 rounded-lg bg-white shadow-sm max-h-60 overflow-y-auto">
                    <!-- Les résultats seront injectés ici par JavaScript -->
                </div>
                
                <!-- ==================== CLIENT SÉLECTIONNÉ (AFFICHAGE) ==================== -->
                <div id="client_selected" class="hidden mb-4 p-4 bg-purple-100 border border-purple-200 rounded-lg">
                    <div class="flex justify-between items-center">
                        <div>
                            <div class="font-semibold text-purple-900" id="selected_client_name">--</div>
                            <div class="text-sm text-purple-700" id="selected_client_info">--</div>
                        </div>
                        <button type="button" 
                                id="clear_client_btn"
                                class="text-purple-600 hover:text-purple-800 text-sm font-medium">
                            Changer
                        </button>
                    </div>
                </div>
                
                <!-- ==================== MESSAGE D'ÉTAT ==================== -->
                <div id="client_message" class="hidden mb-4 p-3 text-sm rounded-lg">
                    <!-- Messages: "Recherche en cours...", "Aucun client trouvé", etc. -->
                </div>
                
                <!-- ==================== BOUTONS D'ACTION ==================== -->
                <div class="flex justify-between items-center pt-4 border-t border-purple-100">
                    <!-- Bouton pour ajouter un nouveau client -->
                    <button type="button" 
                            class="text-purple-600 hover:text-purple-800 text-sm flex items-center">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                        </svg>
                        Ajouter un nouveau client
                    </button>
                    
                    <!-- Lien vers la liste complète des clients -->
                    <!-- REMPLACER CETTE LIGNE : -->
                    
                    <a href="#" 
                    class="text-gray-600 hover:text-gray-800 text-sm flex items-center">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                        </svg>
                        Voir tous mes clients
                    </a>
                </div>
            </div>
            <!-- =========================================== -->
            <!-- FIN NOUVELLE SECTION                       -->
            <!-- =========================================== -->
            

            <!-- Après la recherche client -->
            <div class="flex flex-wrap gap-2 mb-4">
                <!-- Bouton par défaut (clients directs) -->
                <button type="button" 
                        id="btn-clients-directs"
                        class="px-4 py-2 bg-purple-600 text-white rounded-lg font-medium text-sm hover:bg-purple-700 transition">
                    Mes clients directs
                </button>
                
                <!-- Bouton clients de groupe -->
                <button type="button" 
                        id="btn-clients-groupe"
                        class="px-4 py-2 bg-blue-600 text-white rounded-lg font-medium text-sm hover:bg-blue-700 transition">
                    Afficher les clients de mon groupe
                </button>
                
                <!-- Bouton clients partagés -->
                <button type="button" 
                        id="btn-clients-partages"
                        class="px-4 py-2 bg-green-600 text-white rounded-lg font-medium text-sm hover:bg-green-700 transition">
                    Afficher les clients partagés
                </button>
            </div>

            <!-- Indicateur actif -->
            <div id="filtre-actif" class="mb-4 text-sm text-gray-600">
                Affichage : <span class="font-semibold" id="filtre-type">Mes clients directs</span>
            </div>
        </div>
        
        <!-- Colonne droite : Destinataire et Type de prise en charge -->
        <div class="space-y-8">
            
            <!-- Sélection du destinataire (sera peuplée dynamiquement) -->
        <div class="bg-green-50 border border-green-100 rounded-lg p-6">
            <div class="flex items-center mb-4">
                <div class="bg-green-100 text-green-800 rounded-lg p-2 mr-3">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                    </svg>
                </div>
                <h4 class="text-lg font-semibold text-gray-800">Destinataire</h4>
            </div>
            
            <!-- Ce div s'affichera quand un client sera sélectionné -->
            <div id="destinataire-container" class="hidden">
                <div class="mb-4">
                    <label for="destinataire_id" class="block text-sm font-medium text-gray-700 mb-2">
                        Sélectionnez un destinataire *
                    </label>
                    
                    <!-- Indicateur de chargement -->
                    <div id="destinataire-loading" class="hidden text-center py-4">
                        <div class="inline-block animate-spin rounded-full h-6 w-6 border-b-2 border-blue-600"></div>
                        <span class="ml-2 text-gray-600">Chargement des destinataires...</span>
                    </div>
                    
                    <!-- Select des destinataires (sera peuplé dynamiquement) -->
                    <select name="destinataire_id" id="destinataire_id"
                            class="w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500 py-3 px-4 hidden">
                        <option value="">-- Choisir un destinataire --</option>
                    </select>
                    
                    <!-- Message si aucun destinataire -->
                    <div id="no-destinataires" class="hidden p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
                        <p class="text-yellow-800">
                            <span class="font-semibold">⚠️ Attention :</span> 
                            Ce client n'a aucun destinataire enregistré.
                        </p>
                        <button type="button" class="mt-2 text-blue-600 hover:text-blue-800 text-sm flex items-center">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                            </svg>
                            Ajouter un destinataire
                        </button>
                    </div>
                </div>
            </div>
            
            <!-- Message initial (avant sélection client) -->
            <div id="destinataire-initial" class="text-center py-8">
            
                <p class="text-gray-600">
                    Sélectionnez d'abord un client pour afficher ses destinataires.
                </p>
            </div>
        </div>
            

            <!-- Section conditionnelle : Affiche seulement si type_calcul = "poids" -->
            <div id="section-type-prise-charge" class="bg-yellow-50 border border-yellow-100 rounded-lg p-6 hidden">
                <div class="flex items-center mb-4">
                    <div class="bg-yellow-100 text-yellow-800 rounded-lg p-2 mr-3">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                        </svg>
                    </div>
                    <h4 class="text-lg font-semibold text-gray-800">Prise en charge à domicile ?</h4>
                </div>
                
                <div class="space-y-4">
                    <!-- Option NON (dépôt) -->
                    <div class="flex items-center p-4 border border-gray-200 rounded-lg hover:bg-white cursor-pointer transition duration-200"
                        onclick="document.getElementById('type-depot').click()">
                        <input type="radio" id="type-depot" name="type_prise_charge" value="depot"
                            class="h-5 w-5 text-blue-600 focus:ring-blue-500">
                        <label for="type-depot" class="ml-3 flex-1 cursor-pointer">
                            <span class="font-medium text-gray-900">NON - Prise en charge au dépôt</span>
                            <p class="text-gray-600 text-sm mt-1">
                                Le client apporte les articles au dépôt.
                            </p>
                        </label>
                        <div class="text-2xl">🏢</div>
                    </div>
                    
                    <!-- Option OUI (domicile) -->
                    <div class="flex items-center p-4 border border-gray-200 rounded-lg hover:bg-white cursor-pointer transition duration-200"
                        onclick="document.getElementById('type-domicile').click()">
                        <input type="radio" id="type-domicile" name="type_prise_charge" value="domicile"
                            class="h-5 w-5 text-blue-600 focus:ring-blue-500">
                        <label for="type-domicile" class="ml-3 flex-1 cursor-pointer">
                            <span class="font-medium text-gray-900">OUI - Récupération à domicile</span>
                            <p class="text-gray-600 text-sm mt-1">
                                Je récupère les articles chez le client (+0.50€/kg).
                            </p>
                        </label>
                        <div class="text-2xl">🏠</div>
                    </div>
                </div>
                
                <div class="mt-6 p-4 bg-blue-50 rounded-lg border border-blue-100">
                    <p class="text-sm text-blue-800">
                        💡 <span class="font-semibold">Important :</span> 
                        La prise en charge à domicile ajoute 0.50€ par kilo.
                        Tarif final : <span class="font-bold">3.50 €/kg</span> (au lieu de 3.00 €/kg).
                    </p>
                </div>
            </div>

            <!-- Section info pour type_calcul = "volume" -->
            <div id="section-info-volume" class="bg-green-50 border border-green-100 rounded-lg p-6 hidden">
                <div class="flex items-center mb-4">
                    <div class="bg-green-100 text-green-800 rounded-lg p-2 mr-3">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                        </svg>
                    </div>
                    <h4 class="text-lg font-semibold text-gray-800">Calcul par volume</h4>
                </div>
                
                <div class="p-4 bg-white rounded-lg border border-gray-200">
                    <p class="text-gray-700">
                        Ce départ utilise le <span class="font-bold">calcul par volume</span>.
                    </p>
                    <p class="text-sm text-gray-600 mt-2">
                        Prix = Volume × prix/m³<br>
                        Part TS = Volume × 250€ (fixe)<br>
                        Part collecteur = Différence
                    </p>
                </div>
                
                <!-- Champ caché pour type_prise_charge (valeur par défaut pour volume) -->
                <input type="hidden" name="type_prise_charge" value="depot">
            </div>

        </div> <!-- Fermeture Colonne droite -->
    </div>
    
    <!-- ==================== RÉSUMÉ FINAL ==================== -->
    <div class="mt-10 pt-6 border-t border-gray-200">
        <h4 class="font-semibold text-gray-800 mb-6 text-lg">Résumé de votre sélection</h4>
        
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <!-- Départ -->
            <div class="text-center p-4 bg-white rounded-lg border border-gray-200 shadow-sm">
                <div class="text-sm text-gray-600 mb-1">Départ</div>
                <div id="resume-depart" class="font-semibold text-gray-800">--</div>
            </div>
            
            <!-- Client -->
            <div class="text-center p-4 bg-white rounded-lg border border-gray-200 shadow-sm">
                <div class="text-sm text-gray-600 mb-1">Client</div>
                <div id="resume-client" class="font-semibold text-gray-800">--</div>
            </div>
            
            <!-- Destinataire -->
            <div class="text-center p-4 bg-white rounded-lg border border-gray-200 shadow-sm">
                <div class="text-sm text-gray-600 mb-1">Destinataire</div>
                <div id="resume-destinataire" class="font-semibold text-gray-800">--</div>
            </div>
            
            <!-- Type -->
            <div class="text-center p-4 bg-white rounded-lg border border-gray-200 shadow-sm">
                <div class="text-sm text-gray-600 mb-1">Type</div>
                <div id="resume-type" class="font-semibold text-gray-800">--</div>
            </div>
        </div>
    </div>
    <!-- ==================== FIN RÉSUMÉ ==================== -->
    
</div> <!-- Fermeture du div principal bg-white -->

