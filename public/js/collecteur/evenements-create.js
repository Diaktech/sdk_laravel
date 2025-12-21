// public/js/collecteur/evenements-create.js

/**
 * CLASSE PRINCIPALE - Gestion du formulaire en 3 étapes
 */
class FormulaireTroisEtapes {
    constructor() {
        // ==================== VARIABLES D'INSTANCE ====================
        this.etapeCourante = 1;               // Étape actuelle (1, 2 ou 3)
        this.totalEtapes = 3;                 // Nombre total d'étapes
        this.articles = [];                   // Tableau des articles ajoutés
        this.donneesEtape1 = {};              // Données de l'étape 1
        this.donneesEtape2 = {};              // Données de l'étape 2
        this.currentClientId = null;
        this.destinatairesCache = {}; // Cache pour éviter les appels API répétés
        
        // ==================== INITIALISATION ====================
        this.init();
    }
    
    /**
     * INITIALISATION - Configure les événements et l'état initial
     */
    init() {
        console.log('🔧 Initialisation du formulaire 3 étapes');
        
        // Configuration des événements
        this.bindEvents();
        
        // Affichage de l'étape initiale
        this.afficherEtape(1);
        
        // Initialisation des composants
        this.initialiserComposants();
    }
    
    /**
     * CONFIGURATION DES ÉVÉNEMENTS
     */
    bindEvents() {
        console.log('🔗 Configuration des événements');
        
        // ==================== NAVIGATION ENTRE ÉTAPES ====================
        const btnSuivant = document.getElementById('btn-suivant');
        const btnPrecedent = document.getElementById('btn-precedent');
        const btnValider = document.getElementById('btn-valider');
        
        
        if (btnSuivant) {
            btnSuivant.addEventListener('click', () => this.etapeSuivante());
        }
        
        if (btnPrecedent) {
            btnPrecedent.addEventListener('click', () => this.etapePrecedente());
        }
        
        if (btnValider) {
            btnValider.addEventListener('click', (e) => this.validerFormulaire(e));
        }
        
        // ==================== INDICATEURS D'ÉTAPES CLIQUABLES ====================
        document.querySelectorAll('.etape-indicator').forEach(indicator => {
            indicator.addEventListener('click', (e) => {
                const etape = parseInt(e.target.getAttribute('data-etape'));
                if (etape < this.etapeCourante) {
                    this.afficherEtape(etape);
                }
            });
        });
        
        // ==================== ÉVÉNEMENTS ÉTAPE 1 ====================
        const departSelect = document.getElementById('depart_id');
        const clientSelect = document.getElementById('client_id');
        const destinataireSelect = document.getElementById('destinataire_id');
        
        if (departSelect) {
            // Quand le départ change : calculer capacité + gérer affichage conditionnel
            departSelect.addEventListener('change', () => {
                this.calculerCapaciteRestante();
                this.gererAffichageTypePriseCharge();
                this.mettreAJourResumeEtape1();
            });
        }
        
        // ==================== CLIENT : COMMENTÉ TEMPORAIREMENT ====================
        // L'ancien select n'existe plus, remplacé par input hidden
        // La gestion se fera via la nouvelle classe ClientSearch
        // const clientSelect = document.getElementById('client_id');
        // if (clientSelect) {
        //     clientSelect.addEventListener('change', async (e) => {
        //         await this.gestionChangementClient(e.target.value);
        //         this.mettreAJourResumeEtape1();
        //     });
        // }

        // ==================== DESTINATAIRE : À ADAPTER PLUS TARD ====================
        // Le select destinataire sera créé dynamiquement
        // On gérera l'événement plus tard
        // const destinataireSelect = document.getElementById('destinataire_id');
        // if (destinataireSelect) {
        //     destinataireSelect.addEventListener('change', () => this.mettreAJourResumeEtape1());
        // }
        
        // ==================== ÉVÉNEMENTS TYPE PRISE EN CHARGE ====================
        document.querySelectorAll('input[name="type_prise_charge"]').forEach(radio => {
            radio.addEventListener('change', () => this.mettreAJourResumeEtape1());
        });
        
        // ==================== ÉVÉNEMENTS ÉTAPE 2 (à venir) ====================
        // Sera ajouté quand on créera l'étape 2
    }
    
    /**
     * INITIALISATION DES COMPOSANTS
     */
    initialiserComposants() {
        console.log('⚙️ Initialisation des composants');
        
        // Initialiser l'affichage de la capacité
        this.calculerCapaciteRestante();
        
        // Initialiser l'affichage conditionnel type prise en charge
        this.gererAffichageTypePriseCharge();
        
        // Initialiser le résumé de l'étape 1
        this.mettreAJourResumeEtape1();
    }
    
    /**
     * AFFICHER UNE ÉTAPE SPÉCIFIQUE
     * @param {number} etape - Numéro de l'étape à afficher (1, 2 ou 3)
     */
    afficherEtape(etape) {
        console.log(`🔄 Tentative de passage à l'étape ${etape}`);
        
        // ==================== VALIDATION DE L'ÉTAPE COURANTE ====================
        if (!this.validerEtape(this.etapeCourante)) {
            console.log(`❌ Étape ${this.etapeCourante} non validée`);
            return;
        }
        
        // ==================== SAUVEGARDE DES DONNÉES ====================
        this.sauvegarderDonneesEtape(this.etapeCourante);
        
        // ==================== MASQUER TOUTES LES ÉTAPES ====================
        document.querySelectorAll('.etape-content').forEach(div => {
            div.classList.remove('etape-active');
            div.classList.add('hidden');
        });
        
        // ==================== MISE À JOUR DES INDICATEURS VISUELS ====================
        document.querySelectorAll('.etape-indicator').forEach(indicator => {
            indicator.classList.remove('etape-active');
        });
        
        // ==================== AFFICHER LA NOUVELLE ÉTAPE ====================
        const etapeElement = document.getElementById(`etape-${etape}`);
        if (etapeElement) {
            etapeElement.classList.add('etape-active');
            etapeElement.classList.remove('hidden');
        }
        
        const indicatorElement = document.querySelector(`.etape-indicator[data-etape="${etape}"]`);
        if (indicatorElement) {
            indicatorElement.classList.add('etape-active');
        }
        
        // ==================== MISE À JOUR DES BOUTONS DE NAVIGATION ====================
        this.mettreAJourBoutonsNavigation(etape);
        
        // ==================== MISE À JOUR DE L'ÉTAPE COURANTE ====================
        this.etapeCourante = etape;
        console.log(`✅ Étape ${etape} affichée`);
        
        // ==================== ACTIONS SPÉCIFIQUES PAR ÉTAPE ====================
        switch(etape) {
            case 3:
                this.mettreAJourRecapitulatif();
                break;
            case 2:
                this.initialiserEtape2();
                break;
        }
    }
    
    /**
     * VALIDER UNE ÉTAPE AVANT DE PASSER À LA SUIVANTE
     * @param {number} etape - Numéro de l'étape à valider
     * @returns {boolean} - true si validation réussie, false sinon
     */
    validerEtape(etape) {
        console.log(`📋 Validation de l'étape ${etape}`);
        
        switch(etape) {
            case 1:
                return this.validerEtape1();
                
            case 2:
                return this.validerEtape2();
                
            default:
                return true;
        }
    }
    
    /**
     * VALIDATION SPÉCIFIQUE ÉTAPE 1
     * @returns {boolean} - true si validation réussie
     */
    validerEtape1() {
        console.log('📋 Validation étape 1 - Informations générales');
        
        // ==================== VÉRIFICATION DES CHAMPS OBLIGATOIRES ====================
        const depart = document.getElementById('depart_id').value;
        const client = document.getElementById('client_id').value;
        const destinataireSelect = document.getElementById('destinataire_id');
            const destinataire = destinataireSelect && !destinataireSelect.classList.contains('hidden') 
        ? destinataireSelect.value 
        : '';
        
        if (!depart || !client || !destinataire) {
            let message = 'Veuillez sélectionner :\n';
            if (!depart) message += '• Un départ\n';
            if (!client) message += '• Un client\n';
            if (!destinataire) message += '• Un destinataire\n';
            
            alert(message);
            console.log('❌ Étape 1 invalide : champs manquants');
            return false;
        }
        
        // ==================== VÉRIFICATION SPÉCIFIQUE SI CALCUL PAR POIDS ====================
        const departSelect = document.getElementById('depart_id');
        const selectedOption = departSelect.options[departSelect.selectedIndex];
        const typeCalcul = selectedOption.getAttribute('data-type-calcul');
        
        if (typeCalcul === 'poids') {
            const typePriseCharge = document.querySelector('input[name="type_prise_charge"]:checked');
            if (!typePriseCharge) {
                alert('Veuillez indiquer si la prise en charge est à domicile.');
                console.log('❌ Étape 1 invalide : type prise en charge manquant');
                return false;
            }
        }
        
        console.log('✅ Étape 1 validée');
        return true;
    }
    
    /**
     * VALIDATION SPÉCIFIQUE ÉTAPE 2 (À IMPLÉMENTER)
     * @returns {boolean} - true si validation réussie
     */
    validerEtape2() {
        console.log('📋 Validation étape 2 - Articles');
        
        // À IMPLÉMENTER : vérifier qu'au moins un article est ajouté
        // Pour l'instant, retourner true pour permettre la navigation
        return true;
    }
    
    /**
     * SAUVEGARDER LES DONNÉES DE L'ÉTAPE COURANTE
     * @param {number} etape - Numéro de l'étape à sauvegarder
     */
    sauvegarderDonneesEtape(etape) {
        console.log(`💾 Sauvegarde des données étape ${etape}`);
        
        switch(etape) {
            case 1:
                this.sauvegarderDonneesEtape1();
                break;
            case 2:
                this.sauvegarderDonneesEtape2();
                break;
        }
    }
    
    /**
     * SAUVEGARDER LES DONNÉES DE L'ÉTAPE 1
     */
    sauvegarderDonneesEtape1() {
        const departSelect = document.getElementById('depart_id');
        const selectedOption = departSelect.options[departSelect.selectedIndex];
        
        this.donneesEtape1 = {
            depart_id: departSelect.value,
            client_id: document.getElementById('client_id').value,
            destinataire_id: document.getElementById('destinataire_id').value,
            type_calcul: selectedOption.getAttribute('data-type-calcul'),
            type_prise_charge: document.querySelector('input[name="type_prise_charge"]:checked')?.value || 'depot'
        };
        
        console.log('💾 Données étape 1 sauvegardées :', this.donneesEtape1);
    }
    
    /**
     * SAUVEGARDER LES DONNÉES DE L'ÉTAPE 2 (À IMPLÉMENTER)
     */
    sauvegarderDonneesEtape2() {
        // À IMPLÉMENTER quand on créera l'étape 2
        this.donneesEtape2 = {
            articles: this.articles
        };
        
        console.log('💾 Données étape 2 sauvegardées :', this.donneesEtape2);
    }
    
    /**
     * MISE À JOUR DES BOUTONS DE NAVIGATION
     * @param {number} etape - Numéro de l'étape actuelle
     */
    mettreAJourBoutonsNavigation(etape) {
        console.log(`🔘 Mise à jour boutons navigation pour étape ${etape}`);
        
        const btnPrecedent = document.getElementById('btn-precedent');
        const btnSuivant = document.getElementById('btn-suivant');
        const btnValider = document.getElementById('btn-valider');
        
        // ==================== BOUTON PRÉCÉDENT ====================
        if (btnPrecedent) {
            btnPrecedent.classList.toggle('hidden', etape === 1);
        }
        
        // ==================== BOUTON SUIVANT ====================
        if (btnSuivant) {
            btnSuivant.classList.toggle('hidden', etape === this.totalEtapes);
        }
        
        // ==================== BOUTON VALIDER ====================
        if (btnValider) {
            btnValider.classList.toggle('hidden', etape !== this.totalEtapes);
        }
    }
    
    /**
     * PASSER À L'ÉTAPE SUIVANTE
     */
    etapeSuivante() {
        console.log('➡️ Passage à l\'étape suivante');
        this.afficherEtape(this.etapeCourante + 1);
    }
    
    /**
     * REVENIR À L'ÉTAPE PRÉCÉDENTE
     */
    etapePrecedente() {
        console.log('⬅️ Retour à l\'étape précédente');
        this.afficherEtape(this.etapeCourante - 1);
    }
    
    /**
     * CALCULER LA CAPACITÉ RESTANTE DU DÉPART SÉLECTIONNÉ
     */
    calculerCapaciteRestante() {
        const departSelect = document.getElementById('depart_id');
        const selectedOption = departSelect.options[departSelect.selectedIndex];
        
        if (selectedOption && selectedOption.value) {
            const volumeMax = parseFloat(selectedOption.dataset.volumeMax) || 0;
            const volumeActuel = parseFloat(selectedOption.dataset.volumeActuel) || 0;
            const restant = volumeMax - volumeActuel;
            
            const pourcentage = volumeMax > 0 ? (volumeActuel / volumeMax) * 100 : 0;
            
            // ==================== MISE À JOUR DE L'AFFICHAGE ====================
            const capaciteRestanteEl = document.getElementById('capacite-restante');
            const barreProgressionEl = document.getElementById('barre-progression');
            
            if (capaciteRestanteEl) {
                capaciteRestanteEl.textContent = `${restant.toFixed(3)} m³`;
            }
            
            if (barreProgressionEl) {
                barreProgressionEl.style.width = `${pourcentage}%`;
                
                // ==================== COULEUR DE LA BARRE SELON LE REMPLISSAGE ====================
                if (pourcentage >= 90) {
                    barreProgressionEl.className = 'bg-red-600 h-2.5 rounded-full';
                } else if (pourcentage >= 50) {
                    barreProgressionEl.className = 'bg-yellow-500 h-2.5 rounded-full';
                } else {
                    barreProgressionEl.className = 'bg-green-600 h-2.5 rounded-full';
                }
            }
            
            console.log(`📊 Capacité restante : ${restant.toFixed(3)} m³ (${pourcentage.toFixed(1)}% rempli)`);
        } else {
            // ==================== RÉINITIALISER SI AUCUN DÉPART SÉLECTIONNÉ ====================
            const capaciteRestanteEl = document.getElementById('capacite-restante');
            const barreProgressionEl = document.getElementById('barre-progression');
            
            if (capaciteRestanteEl) capaciteRestanteEl.textContent = '-- m³';
            if (barreProgressionEl) barreProgressionEl.style.width = '0%';
        }
    }
    
    /**
     * GÉRER L'AFFICHAGE CONDITIONNEL DU TYPE DE PRISE EN CHARGE
     */
    gererAffichageTypePriseCharge() {
        const departSelect = document.getElementById('depart_id');
        const selectedOption = departSelect.options[departSelect.selectedIndex];
        
        const sectionPoids = document.getElementById('section-type-prise-charge');
        const sectionVolume = document.getElementById('section-info-volume');
        
        if (!selectedOption || !selectedOption.value) {
            // ==================== AUCUN DÉPART SÉLECTIONNÉ ====================
            if (sectionPoids) sectionPoids.classList.add('hidden');
            if (sectionVolume) sectionVolume.classList.add('hidden');
            console.log('❓ Aucun départ sélectionné - Masquer les sections');
            return;
        }
        
        const typeCalcul = selectedOption.getAttribute('data-type-calcul') || 'volume';
        
        if (typeCalcul === 'poids') {
            // ==================== CALCUL PAR POIDS : AFFICHER LE CHOIX ====================
            if (sectionPoids) sectionPoids.classList.remove('hidden');
            if (sectionVolume) sectionVolume.classList.add('hidden');
            console.log('⚖️ Calcul par poids - Afficher choix domicile/dépôt');
        } else {
            // ==================== CALCUL PAR VOLUME : AFFICHER L'INFO ====================
            if (sectionPoids) sectionPoids.classList.add('hidden');
            if (sectionVolume) sectionVolume.classList.remove('hidden');
            console.log('📦 Calcul par volume - Afficher info seulement');
        }
    }
    
    /**
     * METTRE À JOUR LE RÉSUMÉ DE L'ÉTAPE 1
     */
    mettreAJourResumeEtape1() {
        console.log('📝 Mise à jour résumé étape 1');
        
        // ==================== DÉPART ====================
        const departSelect = document.getElementById('depart_id');
        const departOption = departSelect.options[departSelect.selectedIndex];
        const resumeDepart = document.getElementById('resume-depart');
        
        if (resumeDepart) {
            resumeDepart.textContent = departOption.value 
                ? departOption.textContent.split('(')[0].trim() 
                : '--';
        }
        
        // ==================== CLIENT ====================
        const clientId = document.getElementById('client_id').value;
        const resumeClient = document.getElementById('resume-client');
        const selectedClientName = document.getElementById('selected_client_name');

        if (resumeClient) {
            if (clientId && selectedClientName && selectedClientName.textContent !== '--') {
                resumeClient.textContent = selectedClientName.textContent;
            } else {
                resumeClient.textContent = '--';
            }
        }
        
        // ==================== DESTINATAIRE ====================
        const destinataireSelect = document.getElementById('destinataire_id');
        const destinataireOption = destinataireSelect.options[destinataireSelect.selectedIndex];
        const resumeDestinataire = document.getElementById('resume-destinataire');
        
        if (resumeDestinataire) {
            resumeDestinataire.textContent = destinataireOption.value 
                ? destinataireOption.textContent.split('(')[0].trim() 
                : '--';
        }
        
        // ==================== TYPE DE PRISE EN CHARGE ====================
        const resumeType = document.getElementById('resume-type');
        if (resumeType) {
            const departOption = departSelect.options[departSelect.selectedIndex];
            const typeCalcul = departOption.getAttribute('data-type-calcul');
            
            if (typeCalcul === 'poids') {
                const typePriseCharge = document.querySelector('input[name="type_prise_charge"]:checked');
                if (typePriseCharge) {
                    resumeType.textContent = typePriseCharge.value === 'domicile' 
                        ? 'Domicile (+0.50€)' 
                        : 'Dépôt';
                    resumeType.className = typePriseCharge.value === 'domicile'
                        ? 'font-semibold text-lg mt-1 text-orange-600'
                        : 'font-semibold text-lg mt-1 text-green-600';
                } else {
                    resumeType.textContent = '--';
                }
            } else {
                resumeType.textContent = 'Volume';
                resumeType.className = 'font-semibold text-lg mt-1 text-blue-600';
            }
        }
    }
    
    /**
     * INITIALISER L'ÉTAPE 2 (À IMPLÉMENTER)
     */
    initialiserEtape2() {
        console.log('🛍️ Initialisation étape 2 - Sélection articles');
        // À IMPLÉMENTER : charger les familles et articles
    }
    
    /**
     * METTRE À JOUR LE RÉCAPITULATIF (ÉTAPE 3)
     */
    mettreAJourRecapitulatif() {
        console.log('📊 Mise à jour récapitulatif étape 3');
        
        // À IMPLÉMENTER : calculer les totaux et afficher
        // Pour l'instant, log seulement
        console.log('Données pour récapitulatif :', {
            etape1: this.donneesEtape1,
            etape2: this.donneesEtape2,
            articles: this.articles
        });
    }
    
    /**
     * VALIDER LE FORMULAIRE COMPLET (SOUMISSION)
     * @param {Event} e - Événement de soumission
     */
    validerFormulaire(e) {
        console.log('✅ Validation finale du formulaire');
        
        // ==================== VALIDATION FINALE ====================
        if (!this.validerEtape(1) || !this.validerEtape(2)) {
            e.preventDefault();
            alert('Veuillez corriger les erreurs avant de soumettre.');
            console.log('❌ Formulaire non soumis : validation échouée');
            return;
        }
        
        // ==================== PRÉPARATION DES DONNÉES POUR ENVOI ====================
        this.preparerDonneesSoumission();
        
        console.log('✅ Formulaire prêt pour soumission');
        // Le formulaire se soumet normalement
    }
    
    /**
     * PRÉPARER LES DONNÉES POUR LA SOUMISSION (À IMPLÉMENTER)
     */
    preparerDonneesSoumission() {
        console.log('📦 Préparation des données pour soumission');
        
        // À IMPLÉMENTER : rassembler toutes les données dans les champs cachés
        // ou dans le format attendu par le contrôleur
        
        // Exemple :
        // document.getElementById('donnees-etape1').value = JSON.stringify(this.donneesEtape1);
        // document.getElementById('donnees-etape2').value = JSON.stringify(this.donneesEtape2);
    }
    
    /**
     * AJOUTER UN ARTICLE (À IMPLÉMENTER)
     * @param {Object} articleData - Données de l'article
     */
    ajouterArticle(articleData) {
        console.log('➕ Ajout d\'un article :', articleData);
        this.articles.push(articleData);
        this.mettreAJourListeArticles();
    }
    
    /**
     * METTRE À JOUR LA LISTE DES ARTICLES (À IMPLÉMENTER)
     */
    mettreAJourListeArticles() {
        console.log('🔄 Mise à jour liste articles');
        // À IMPLÉMENTER : afficher la liste des articles ajoutés
    }

    /**
     * GÉRER LE CHANGEMENT DE CLIENT (CHARGEMENT DES DESTINATAIRES)
     * @param {string} clientId - ID du client sélectionné
     */
    async gestionChangementClient(clientId) {
        console.log(`👤 Client sélectionné : ${clientId}`);
        this.currentClientId = clientId;
        
        // Références aux éléments HTML
        const container = document.getElementById('destinataire-container');
        const initialMsg = document.getElementById('destinataire-initial');
        const select = document.getElementById('destinataire_id');
        const loading = document.getElementById('destinataire-loading');
        const noDest = document.getElementById('no-destinataires');
        
        // 1. Réinitialiser l'affichage
        this.resetAffichageDestinataires();
        
        if (!clientId) {
            // Aucun client sélectionné
            if (container) container.classList.add('hidden');
            if (initialMsg) initialMsg.classList.remove('hidden');
            this.updateResumeDestinataire('--');
            return;
        }
        
        // 2. Client sélectionné : afficher le container
        if (container) container.classList.remove('hidden');
        if (initialMsg) initialMsg.classList.add('hidden');
        
        // 3. Vérifier le cache
        if (this.destinatairesCache[clientId]) {
            console.log('📦 Destinataires récupérés du cache');
            this.afficherDestinataires(this.destinatairesCache[clientId]);
            return;
        }
        
        // 4. Charger depuis l'API
        await this.chargerDestinatairesAPI(clientId);
    }

    /**
    * RÉINITIALISER L'AFFICHAGE DES DESTINATAIRES
    */
    resetAffichageDestinataires() {
        const select = document.getElementById('destinataire_id');
        const loading = document.getElementById('destinataire-loading');
        const noDest = document.getElementById('no-destinataires');
        
        if (select) {
            select.innerHTML = '<option value="">-- Choisir un destinataire --</option>';
            select.classList.add('hidden');
            select.value = '';
        }
        
        if (loading) loading.classList.add('hidden');
        if (noDest) noDest.classList.add('hidden');
    }


    /**
     * CHARGER LES DESTINATAIRES DEPUIS L'API
     * @param {string} clientId - ID du client
     */
    async chargerDestinatairesAPI(clientId) {
        console.log(`🔄 Chargement des destinataires pour client ${clientId}`);
        
        const select = document.getElementById('destinataire_id');
        const loading = document.getElementById('destinataire-loading');
        const noDest = document.getElementById('no-destinataires');
        
        // Afficher le loading
        if (loading) loading.classList.remove('hidden');
        if (select) select.classList.add('hidden');
        if (noDest) noDest.classList.add('hidden');
        
        try {
            const response = await fetch(`/collecteur/clients/${clientId}/destinataires`);
            
            if (!response.ok) {
                throw new Error(`Erreur HTTP ${response.status}`);
            }
            
            const destinataires = await response.json();
            
            // Mettre en cache
            this.destinatairesCache[clientId] = destinataires;
            
            // Afficher les résultats
            if (loading) loading.classList.add('hidden');
            this.afficherDestinataires(destinataires);
            
            console.log(`✅ ${destinataires.length} destinataire(s) chargé(s)`);
            
        } catch (error) {
            console.error('❌ Erreur chargement destinataires:', error);
            
            if (loading) loading.classList.add('hidden');
            this.afficherErreurDestinataires();
        }
    }

    /**
     * AFFICHER LES DESTINATAIRES DANS LE SELECT
     * @param {Array} destinataires - Liste des destinataires
     */
    afficherDestinataires(destinataires) {
        const select = document.getElementById('destinataire_id');
        const noDest = document.getElementById('no-destinataires');
        
        if (!select) return;
        
        if (destinataires.length === 0) {
            // Aucun destinataire
            select.classList.add('hidden');
            if (noDest) {
                noDest.classList.remove('hidden');
            }
            this.updateResumeDestinataire('Aucun destinataire');
            return;
        }
        
        // Peupler le select
        let options = '<option value="">-- Choisir un destinataire --</option>';
        destinataires.forEach(dest => {
            const displayName = `${dest.prenom} ${dest.nom}`.trim();
            const displayText = `${displayName} (${dest.code_unique})`;
            options += `<option value="${dest.id}">📍 ${displayText}</option>`;
        });
        
        select.innerHTML = options;
        select.classList.remove('hidden');
        
        // Sélectionner le premier par défaut
        if (destinataires.length > 0) {
            // select.value = destinataires[0].id; // Optionnel : auto-sélection
            this.updateResumeDestinataire(`${destinataires[0].prenom} ${destinataires[0].nom}`);
        }
        
        // Ajouter l'écouteur d'événement pour le changement
        select.addEventListener('change', () => {
            this.mettreAJourResumeEtape1();
            this.validerEtape(1);
        });
    }

    /**
     * AFFICHER UNE ERREUR DE CHARGEMENT
     */
    afficherErreurDestinataires() {
        const noDest = document.getElementById('no-destinataires');
        if (noDest) {
            noDest.innerHTML = `
                <p class="text-red-800">
                    <span class="font-semibold">❌ Erreur :</span> 
                    Impossible de charger les destinataires.
                </p>
                <button type="button" onclick="window.formulaireEvenement.chargerDestinatairesAPI('${this.currentClientId}')" 
                        class="mt-2 text-blue-600 hover:text-blue-800 text-sm flex items-center">
                    Réessayer
                </button>
            `;
            noDest.classList.remove('hidden');
        }
        this.updateResumeDestinataire('Erreur chargement');
    }

    /**
     * METTRE À JOUR LE RÉSUMÉ DU DESTINATAIRE
     * @param {string} text - Texte à afficher
     */
    updateResumeDestinataire(text) {
        const resumeElement = document.getElementById('resume-destinataire');
        if (resumeElement) {
            resumeElement.textContent = text;
        }
    }
    

}


// ====================================================
// CLASSE POUR LA RECHERCHE DE CLIENTS (AJAX)
// ====================================================

class ClientSearch {
    constructor() {
        this.searchInput = document.getElementById('client_search');
        this.clientIdInput = document.getElementById('client_id');
        this.resultsContainer = document.getElementById('client_results');
        this.selectedContainer = document.getElementById('client_selected');
        this.selectedName = document.getElementById('selected_client_name');
        this.selectedInfo = document.getElementById('selected_client_info');
        this.clearBtn = document.getElementById('clear_client_btn');
        this.messageContainer = document.getElementById('client_message');
        
        if (this.searchInput) {
            this.init();
        }
    }
    
    init() {
        console.log('🔍 Initialisation recherche client');
        
        // Recherche avec debounce
        this.searchInput.addEventListener('input', this.debounce(this.search.bind(this), 300));
        
        // Bouton "Changer"
        if (this.clearBtn) {
            this.clearBtn.addEventListener('click', this.clearSelection.bind(this));
        }
        
        // Cacher résultats quand on clique ailleurs
        document.addEventListener('click', (e) => {
            if (!this.searchInput.contains(e.target) && 
                !this.resultsContainer.contains(e.target)) {
                this.resultsContainer.classList.add('hidden');
            }
        });
    }
    
    debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    }
    
    async search() {
        const query = this.searchInput.value.trim();
        
        // Réinitialiser
        this.hideMessage();
        this.resultsContainer.classList.add('hidden');
        this.resultsContainer.innerHTML = '';
        
        if (query.length < 2) {
            return;
        }
        
        // Afficher message "Recherche en cours"
        this.showMessage('Recherche en cours...', 'info');
        
        try {
            const response = await fetch(`/collecteur/clients/search?q=${encodeURIComponent(query)}`, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Cache-Control': 'no-cache'
                }
            });
            
            if (!response.ok) {
                throw new Error(`HTTP ${response.status}`);
            }
            
            const clients = await response.json();
            
            this.hideMessage();
            
            if (clients.length === 0) {
                this.showMessage('Aucun client trouvé', 'warning');
                return;
            }
            
            // Afficher les résultats
            this.displayResults(clients);
            
        } catch (error) {
            console.error('❌ Erreur recherche:', error);
            this.showMessage('Erreur de recherche', 'error');
        }
    }
    
    displayResults(clients) {
        this.resultsContainer.innerHTML = '';
        
        clients.forEach(client => {
            const item = document.createElement('div');
            item.className = 'p-3 border-b border-gray-100 hover:bg-gray-50 cursor-pointer';
            item.innerHTML = `
                <div class="font-medium">👤 ${client.prenom} ${client.nom}</div>
                <div class="text-sm text-gray-600">
                    ID: ${client.unique_id} | Tel: ${client.telephone}
                </div>
            `;
            
            item.addEventListener('click', () => {
                this.selectClient(client);
            });
            
            this.resultsContainer.appendChild(item);
        });
        
        this.resultsContainer.classList.remove('hidden');
    }
    
    selectClient(client) {
        console.log('✅ Client sélectionné:', client);
        
        // Mettre à jour les champs cachés
        this.clientIdInput.value = client.id;
        
        // Mettre à jour l'affichage
        this.selectedName.textContent = `${client.prenom} ${client.nom}`;
        this.selectedInfo.textContent = `ID: ${client.unique_id} | Tel: ${client.telephone}`;
        this.selectedContainer.classList.remove('hidden');
        
        // Cacher la recherche et les résultats
        this.searchInput.value = '';
        this.resultsContainer.classList.add('hidden');
        this.hideMessage();
        
        // Charger les destinataires de ce client
        if (window.formulaireEvenement) {
            window.formulaireEvenement.gestionChangementClient(client.id);
        }
        
        // Mettre à jour le résumé
        if (window.formulaireEvenement) {
            window.formulaireEvenement.mettreAJourResumeEtape1();
        }
    }
    
    clearSelection() {
        this.clientIdInput.value = '';
        this.selectedContainer.classList.add('hidden');
        this.searchInput.focus();
        
        // Réinitialiser aussi les destinataires
        const destinataireContainer = document.getElementById('destinataire-container');
        const destinataireInitial = document.getElementById('destinataire-initial');
        const destinataireSelect = document.getElementById('destinataire_id');
        
        if (destinataireContainer) destinataireContainer.classList.add('hidden');
        if (destinataireInitial) destinataireInitial.classList.remove('hidden');
        if (destinataireSelect) {
            destinataireSelect.innerHTML = '<option value="">-- Choisir un destinataire --</option>';
            destinataireSelect.classList.add('hidden');
        }
        
        // Mettre à jour le résumé
        if (window.formulaireEvenement) {
            window.formulaireEvenement.mettreAJourResumeEtape1();
        }
    }
    
    showMessage(text, type = 'info') {
        this.messageContainer.innerHTML = text;
        this.messageContainer.className = `p-3 text-sm rounded-lg ${
            type === 'error' ? 'bg-red-100 text-red-800' :
            type === 'warning' ? 'bg-yellow-100 text-yellow-800' :
            'bg-blue-100 text-blue-800'
        }`;
        this.messageContainer.classList.remove('hidden');
    }
    
    hideMessage() {
        this.messageContainer.classList.add('hidden');
    }
}




/**
 * INITIALISATION QUAND LE DOM EST CHARGÉ
 */
document.addEventListener('DOMContentLoaded', () => {
    console.log('🚀 SDKTRANSIT - Formulaire prise en charge');
    
    try {
        // 1. Créer l'instance du formulaire principal
        window.formulaireEvenement = new FormulaireTroisEtapes();
        console.log('✅ Formulaire principal initialisé');
        
        // 2. Créer l'instance de recherche client
        window.clientSearch = new ClientSearch();
        console.log('✅ Recherche client initialisée');
        
        console.log('🎉 Toutes les fonctionnalités sont prêtes !');
        
    } catch (error) {
        console.error('❌ Erreur lors de l\'initialisation :', error);
        alert('Une erreur est survenue lors du chargement du formulaire.');
    }
});

/**
 * EXPORT POUR UTILISATION DANS D'AUTRES FICHIERS (OPTIONNEL)
 */
if (typeof module !== 'undefined' && module.exports) {
    module.exports = FormulaireTroisEtapes;
}