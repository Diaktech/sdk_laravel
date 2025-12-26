<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Nouvelle Prise en Charge
            </h2>
            <a href="{{ route('collecteur.evenements.index') }}" 
                class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                ← Retour à la liste
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8">
            
            <!-- Indicateur d'étapes -->
            <div class="mb-10">
                <div class="flex justify-between items-center mb-2">
                    <div class="flex-1">
                        <div class="flex items-center">
                            <!-- Étape 1 -->
                            <div class="flex items-center">
                                <div class="etape-indicator etape-active" data-etape="1">
                                    1
                                </div>
                                <span class="ml-3 text-sm font-medium text-gray-700">Informations</span>
                            </div>
                            
                            <!-- Ligne -->
                            <div class="flex-1 border-t border-gray-300 mx-6"></div>
                            
                            <!-- Étape 2 -->
                            <div class="flex items-center">
                                <div class="etape-indicator" data-etape="2">
                                    2
                                </div>
                                <span class="ml-3 text-sm font-medium text-gray-500">Articles</span>
                            </div>
                            
                            <!-- Ligne -->
                            <div class="flex-1 border-t border-gray-300 mx-6"></div>
                            
                            <!-- Étape 3 -->
                            <div class="flex items-center">
                                <div class="etape-indicator" data-etape="3">
                                    3
                                </div>
                                <span class="ml-3 text-sm font-medium text-gray-500">Validation</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Formulaire principal -->
            <form action="{{ route('collecteur.evenements.store') }}" method="POST" id="evenementForm">
                @csrf
                
                <!-- Données temporaires (cachées) -->
                <input type="hidden" name="donnees_etape1" id="donnees-etape1">
                <input type="hidden" name="donnees_etape2" id="donnees-etape2">
                
                <!-- Étape 1 : Informations générales -->
                <div id="etape-1" class="etape-content etape-active">
                    @include('collecteur.evenements.partials.etape-1')
                </div>
                
                <!-- Étape 2 : Articles -->
                <div id="etape-2" class="etape-content hidden">
                    @include('collecteur.evenements.partials.etape-2')
                </div>
                
                <!-- Étape 3 : Récapitulatif -->
                <div id="etape-3" class="etape-content hidden">
                    @include('collecteur.evenements.partials.etape-3')
                </div>
                
                <!-- Navigation entre étapes -->
                <div class="mt-10 pt-6 border-t border-gray-200 flex justify-between">
                    <button type="button" id="btn-precedent" class="btn-precedent hidden bg-gray-500 hover:bg-gray-700 text-white font-bold py-3 px-8 rounded-lg transition duration-200">
                        ← Précédent
                    </button>
                    
                    <div class="ml-auto flex space-x-4">
                        <button type="button" id="btn-suivant" class="btn-suivant bg-blue-600 hover:bg-blue-800 text-white font-bold py-3 px-8 rounded-lg transition duration-200">
                            Suivant →
                        </button>
                        
                        <button type="submit" id="btn-valider" class="btn-valider hidden bg-green-600 hover:bg-green-800 text-white font-bold py-3 px-8 rounded-lg transition duration-200">
                            ✅ Valider la prise en charge
                        </button>
                    </div>
                </div>
            </form>
            
        </div>
    </div>

    <!-- Inclusion des fichiers CSS et JS externes -->
    @push('styles')
    <link href="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/css/tom-select.bootstrap5.min.css" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/collecteur/evenements-create.css') }}">
    <style>

        </style>
    @endpush

    
    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/js/tom-select.complete.min.js"></script>
        
        <script src="{{ asset('js/collecteur/evenements-create.js') }}"></script>

        <script>
            // On ne fait QUE passer les données, pas de logique ici
            window.DATA_CATALOGUE = @json($familles);
        </script>
        <script>
            // On récupère le tarif de base de l'entité (via le contrôleur ou Auth)
            window.tarifVolumeParDefaut = {{ $entite->tarif_volume_par_defaut ?? 250 }};
            window.tarifPoidsParDefaut = {{ $entite->tarif_kilo_par_defaut ?? 5 }}; // Exemple: 2€/kg
            window.typeFacturation = "{{ $depart->type_facturation ?? 'volume' }}";
            window.collecteurCanEditPrix = {{ $collecteur->peut_modifier_tarif_vente ? 'true' : 'false' }};
        </script>
    @endpush
</x-app-layout>