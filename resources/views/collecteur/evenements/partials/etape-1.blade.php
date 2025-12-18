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
                            <option value="{{ $depart->id }}" 
                                    data-volume-max="{{ $depart->volume_maximal }}"
                                    data-volume-actuel="{{ $depart->volume_actuel }}"
                                    data-type-calcul="{{ $depart->type_calcul }}">
                                🚚 {{ $depart->lieu_depart }} → {{ $depart->lieu_arrivee }}
                                <span class="text-gray-600 text-sm">
                                    ({{ $depart->type_prise_charge === 'domicile' ? 'Domicile' : 'Dépôt' }})
                                </span>
                                <span class="block text-sm text-gray-500 mt-1">
                                    📦 {{ $depart->volume_actuel }}/{{ $depart->volume_maximal }} m³
                                    | ⚖️ Calcul par {{ $depart->type_calcul }}
                                </span>
                            </option>
                        @endforeach
                    </select>
                    @error('depart_id')
                        <p class="text-red-500 text-sm mt-2">{{ $message }}</p>
                    @enderror
                </div>
                
                <!-- Capacité restante -->
                <div class="mt-4 pt-4 border-t border-blue-100">
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-600">Capacité restante :</span>
                        <span id="capacite-restante" class="font-semibold">-- m³</span>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-2.5 mt-2">
                        <div id="barre-progression" class="bg-green-600 h-2.5 rounded-full" style="width: 0%"></div>
                    </div>
                </div>
            </div>
            
            <!-- Sélection du client -->
            <div class="bg-purple-50 border border-purple-100 rounded-lg p-6">
                <div class="flex items-center mb-4">
                    <div class="bg-purple-100 text-purple-800 rounded-lg p-2 mr-3">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                        </svg>
                    </div>
                    <h4 class="text-lg font-semibold text-gray-800">Client (Expéditeur)</h4>
                </div>
                
                <div class="mb-4">
                    <label for="client_id" class="block text-sm font-medium text-gray-700 mb-2">
                        Sélectionnez un client *
                    </label>
                    <select name="client_id" id="client_id" required
                            class="w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500 py-3 px-4">
                        <option value="">-- Choisir un client --</option>
                        @foreach($clients as $client)
                            <option value="{{ $client->id }}">
                                👤 {{ $client->prenom }} {{ $client->nom }}
                                <span class="text-gray-600">({{ $client->unique_id }})</span>
                            </option>
                        @endforeach
                    </select>
                </div>
                
                <!-- Bouton pour ajouter un nouveau client (optionnel) -->
                <button type="button" 
                        class="text-purple-600 hover:text-purple-800 text-sm flex items-center">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    Ajouter un nouveau client
                </button>
            </div>
            
        </div>
        
        <!-- Colonne droite : Destinataire et Type de prise en charge -->
        <div class="space-y-8">
            
            <!-- Sélection du destinataire -->
            <div class="bg-green-50 border border-green-100 rounded-lg p-6">
                <div class="flex items-center mb-4">
                    <div class="bg-green-100 text-green-800 rounded-lg p-2 mr-3">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                        </svg>
                    </div>
                    <h4 class="text-lg font-semibold text-gray-800">Destinataire (Optionnel)</h4>
                </div>
                
                <div class="mb-4">
                    <label for="destinataire_id" class="block text-sm font-medium text-gray-700 mb-2">
                        Sélectionnez un destinataire
                    </label>
                    <select name="destinataire_id" id="destinataire_id"
                            class="w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500 py-3 px-4">
                        <option value="">-- Choisir un destinataire --</option>
                        @foreach($destinataires as $destinataire)
                            <option value="{{ $destinataire->id }}">
                                📍 {{ $destinataire->prenom }} {{ $destinataire->nom }}
                                <span class="text-gray-600">({{ $destinataire->code_unique }})</span>
                            </option>
                        @endforeach
                    </select>
                </div>
                
                <!-- Bouton pour ajouter un nouveau destinataire -->
                <button type="button" 
                        class="text-green-600 hover:text-green-800 text-sm flex items-center">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    Ajouter un nouveau destinataire
                </button>
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
    
    <!-- Résumé de l'étape 1 -->
    <div class="mt-10 pt-6 border-t border-gray-200 bg-gray-50 rounded-lg p-6">
        <h4 class="font-semibold text-gray-800 mb-3">Résumé de votre sélection :</h4>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="text-center p-4 bg-white rounded-lg border">
                <div class="text-sm text-gray-600">Départ sélectionné</div>
                <div id="resume-depart" class="font-semibold text-lg mt-1 text-gray-800">--</div>
            </div>
            <div class="text-center p-4 bg-white rounded-lg border">
                <div class="text-sm text-gray-600">Client</div>
                <div id="resume-client" class="font-semibold text-lg mt-1 text-gray-800">--</div>
            </div>
            <div class="text-center p-4 bg-white rounded-lg border">
                <div class="text-sm text-gray-600">Type</div>
                <div id="resume-type" class="font-semibold text-lg mt-1 text-green-600">Dépôt</div>
            </div>
        </div>
    </div>




    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="text-center p-4 bg-white rounded-lg border">
            <div class="text-sm text-gray-600">Départ</div>
            <div id="resume-depart" class="font-semibold text-lg mt-1 text-gray-800">--</div>
        </div>
        <div class="text-center p-4 bg-white rounded-lg border">
            <div class="text-sm text-gray-600">Client</div>
            <div id="resume-client" class="font-semibold text-lg mt-1 text-gray-800">--</div>
        </div>
        <div class="text-center p-4 bg-white rounded-lg border">
            <div class="text-sm text-gray-600">Destinataire</div>
            <div id="resume-destinataire" class="font-semibold text-lg mt-1 text-gray-800">--</div>
        </div>
        <div class="text-center p-4 bg-white rounded-lg border">
            <div class="text-sm text-gray-600">Type</div>
            <div id="resume-type" class="font-semibold text-lg mt-1 text-green-600">--</div>
        </div>
    </div>



</div>