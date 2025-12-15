<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            🏢 Dashboard Super Gestionnaire
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <p>Bienvenue <strong>{{ Auth::user()->name }}</strong> ({{ Auth::user()->user_type }})</p>
                    
                    <div class="mt-6 grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="bg-blue-50 p-4 rounded-lg">
                            <h3 class="font-bold">Gestion des Comptes</h3>
                            <p>Créer/modifier Super Managers</p>
                        </div>
                        <div class="bg-green-50 p-4 rounded-lg">
                            <h3 class="font-bold">Configuration Globale</h3>
                            <p>Paramètres, tarifs TS, alertes</p>
                        </div>
                        <div class="bg-purple-50 p-4 rounded-lg">
                            <h3 class="font-bold">Audit & Logs</h3>
                            <p>Suivi de toutes les actions</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>