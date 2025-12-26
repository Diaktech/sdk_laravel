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
        this.famillesArticles = []; // Stockera le catalogue envoyé par Blade
        this.tomSelectInstance = null; // Instance de la barre de recherche
        
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

        // Récupérer les données injectées par le Blade
        this.famillesArticles = window.DATA_CATALOGUE || [];
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
        
        // ==================== ÉVÉNEMENTS TYPE PRISE EN CHARGE ====================
        document.querySelectorAll('input[name="type_prise_charge"]').forEach(radio => {
            radio.addEventListener('change', () => this.mettreAJourResumeEtape1());
        });
        
        // ==================== ÉVÉNEMENTS ÉTAPE 2 (à venir) ====================
        // --- AJOUT SÉCURITÉ CHANGEMENT DE DÉPART ---
        const selectDepart = document.getElementById('depart_id');
        if (selectDepart) {
            selectDepart.addEventListener('change', () => {
                const option = selectDepart.options[selectDepart.selectedIndex];
                if (option && option.value !== "") {
                    // On récupère le type (volume ou poids)
                    const type = option.getAttribute('data-type-calcul').toLowerCase();
                    window.typeFacturation = type;
                    
                    console.log("🔄 Changement de mode détecté : " + type);

                    // Si on a déjà des articles dans le tableau, on force le recalcul
                    const rows = document.querySelectorAll('#liste-articles-body tr:not(#empty-list-message)');
                    rows.forEach(row => {
                        const input = row.querySelector('.input-qte');
                        if (input) input.dispatchEvent(new Event('input')); // Déclenche le recalcul de la ligne
                    });
                    
                    // On recalcule le bandeau noir
                    this.calculerTotauxGlobaux();
                }
            });
        }
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
        
        // A DECOMMENTER  ; COMMENTE
        //  POUR POUVOIR PASSER LES ETAPES SANS CONTROLE
        // if (!depart || !client || !destinataire) {
        //     let message = 'Veuillez sélectionner :\n';
        //     if (!depart) message += '• Un départ\n';
        //     if (!client) message += '• Un client\n';
        //     if (!destinataire) message += '• Un destinataire\n';
            
        //     alert(message);
        //     console.log('❌ Étape 1 invalide : champs manquants');
        //     return false;
        // }
        
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
    /**
     * CALCULER LA CAPACITÉ RESTANTE DU DÉPART SÉLECTIONNÉ
     */
    calculerCapaciteRestante() {
        const departSelect = document.getElementById('depart_id');
        const selectedOption = departSelect.options[departSelect.selectedIndex];
        
        const container = document.getElementById('statut-remplissage-container');
        const labelPercent = document.getElementById('capacite-percent');
        const labelDetails = document.getElementById('capacite-restante');
        const barre = document.getElementById('barre-progression');
        
        if (selectedOption && selectedOption.value) {
            const max = parseFloat(selectedOption.dataset.volumeMax) || 0;
            const actuel = parseFloat(selectedOption.dataset.volumeActuel) || 0;
            const pourcentage = max > 0 ? Math.round((actuel / max) * 100) : 0;

            // 1. Mise à jour des textes
            labelPercent.textContent = pourcentage + '%';
            labelDetails.textContent = `${actuel.toFixed(1)} / ${max.toFixed(0)} m³`;
            if(barre) barre.style.width = Math.min(pourcentage, 100) + '%';

            // 2. Changement de style visuel (Couleurs de fond et texte)
            // On réinitialise tout
            container.className = "mt-4 p-4 rounded-xl border-2 transition-all duration-300 ";
            labelPercent.className = "text-4xl font-black ";

            if (pourcentage >= 100) {
                container.classList.add('bg-red-50', 'border-red-600');
                labelPercent.classList.add('text-red-800');
            } else if (pourcentage >= 85) {
                container.classList.add('bg-orange-50', 'border-orange-500');
                labelPercent.classList.add('text-orange-600');
            } else if (pourcentage >= 50) {
                container.classList.add('bg-blue-50', 'border-blue-400');
                labelPercent.classList.add('text-blue-600');
            } else {
                container.classList.add('bg-green-50', 'border-green-500');
                labelPercent.classList.add('text-green-600');
            }
        } else {
            // Reset état vide
            labelPercent.textContent = '0%';
            labelPercent.className = "text-4xl font-black text-gray-300";
            container.className = "mt-4 p-4 rounded-xl border-2 border-dashed border-gray-200";
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
        console.log('📦 Initialisation de l\'Étape 2...');

        // On récupère les éléments
        const boutonsFamille = document.querySelectorAll('.btn-famille-tuile'); // Attention au nom de la classe
        const sectionSelection = document.getElementById('section-selection-article');
        const selectElt = document.getElementById('select-article-search');
        const btnAjouter = document.getElementById('btn-ajouter-article'); // On récupère le bouton ajouter

        console.log(`🔍 Debug : ${boutonsFamille.length} boutons de famille trouvés.`);

        // 1. Initialiser Tom Select s'il ne l'est pas déjà
        if (!this.tomSelectInstance && selectElt) {
            this.tomSelectInstance = new TomSelect('#select-article-search', {
                create: false,
                sortField: { field: "text", direction: "asc" },
                placeholder: "Chercher un article...",
                allowEmptyOption: true,
            });
        }

        // 2. Écouteur de clic sur les boutons de Famille
        boutonsFamille.forEach(btn => {
            btn.onclick = (e) => {
                e.preventDefault(); // Empêcher tout comportement par défaut
                const familleId = btn.getAttribute('data-famille-id');
                
                // Gestion visuelle
                boutonsFamille.forEach(b => b.classList.remove('active')); // Ma classe CSS s'appelle .active
                btn.classList.add('active');

                // Charger les articles
                this.chargerArticlesParFamille(familleId);

                // Afficher la suite
                if (sectionSelection) {
                    sectionSelection.classList.remove('hidden');
                    sectionSelection.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                }
            };

            // 3. ICI : ON CONNECTE LE BOUTON AJOUTER
            if (btnAjouter) {
                btnAjouter.onclick = (e) => {
                    e.preventDefault();
                    
                    // On récupère l'ID de l'article sélectionné dans Tom Select
                    const articleId = this.tomSelectInstance.getValue();
                    
                    console.log('Tentative d\'ajout de l\'article ID:', articleId);

                    if (articleId) {
                        // C'est ici qu'on appelle enfin ta méthode !
                        this.preparerAjoutArticle(articleId);
                        
                        // On réinitialise la recherche pour le suivant
                        this.tomSelectInstance.clear();
                    } else {
                        alert("Veuillez d'abord sélectionner un article dans la liste.");
                    }
                };
            }

        });
    }

    /**
     * CHARGE LES ARTICLES DANS LE SELECT SELON LA FAMILLE CHOISIE
     */
    chargerArticlesParFamille(familleId) {
        // On trouve la famille dans nos données de référence
        const famille = this.famillesArticles.find(f => f.id == familleId);
        
        if (famille && this.tomSelectInstance) {
            // On vide les anciennes options
            this.tomSelectInstance.clear();
            this.tomSelectInstance.clearOptions();

            // On prépare les nouvelles options (format attendu par Tom Select)
            const options = famille.articles.map(art => {
                return { value: art.id, text: art.libelle };
            });

            // On injecte et on rafraîchit
            this.tomSelectInstance.addOptions(options);
            this.tomSelectInstance.refreshOptions(false);
            
            console.log(`✅ ${options.length} articles chargés pour la famille : ${famille.nom}`);
        }
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
    
    /**
     * ETAPE 2 ARTICLE ET SELECTION
     */
    preparerAjoutArticle(articleId) {
        let articleTrouve = null;

        // On cherche l'article dans notre catalogue local
        this.famillesArticles.forEach(famille => {
            const art = famille.articles.find(a => a.id == articleId);
            if (art) articleTrouve = art;
        });

        if (articleTrouve) {
            console.log('✅ Article trouvé, ajout au tableau...');
            this.ajouterLigneTableau(articleTrouve);
        } else {
            console.error('❌ Article introuvable dans les données locales');
        }
    }

    ajouterLigneTableau(article) {
        const tbody = document.getElementById('liste-articles-body');
        const isFixe = article.mesures_fixes == 1;
        const classLocked = isFixe ? 'bg-gray-100 text-gray-400 cursor-not-allowed' : 'bg-white';
        const attrReadonly = isFixe ? 'readonly' : '';
        // DÉTECTION PROPRE DU MODE DOMICILE
        // On vérifie si le bouton radio coché a la valeur "domicile"
        const radioPriseCharge = document.querySelector('input[name="type_prise_charge"]:checked');
        const isDomicile = radioPriseCharge && radioPriseCharge.value === 'domicile';   

        // Récupération de la majoration personnalisée du collecteur
        const majorationCollecteur = parseFloat(document.getElementById('majoration_domicile')?.value) || 0;
        const fraisDomicile = isDomicile ? majorationCollecteur : 0;
        
        // 1. Déterminer le mode (Poids ou Volume)
        const selectDepart = document.getElementById('depart_id');
        const optionDepart = selectDepart.options[selectDepart.selectedIndex];
        const modeCalcul = optionDepart ? optionDepart.getAttribute('data-type-calcul') : 'volume';

        // 2. Logique des droits sur le prix
        const peutModifier = window.collecteurCanEditPrix === true;
        const classLockedPrix = peutModifier ? 'bg-blue-50' : 'bg-gray-100 text-gray-400 cursor-not-allowed';

        let tarifVenteBase = 0;
        

        if (modeCalcul === 'poids') {
            tarifVenteBase = parseFloat(document.getElementById('tarif_poids_defaut')?.value) || 0;
        } else {
            tarifVenteBase = parseFloat(document.getElementById('tarif_revient_vol_entite')?.value) || 0;
        }

        let prixAffiche = tarifVenteBase + fraisDomicile;
        let labelPrix = modeCalcul === 'poids' ? "Prix Kg" : "Prix m³";

        
        console.log('tarifVenteBase   >>>>    '+tarifVenteBase);      
        console.log('fraisDomicile   >>>>    '+fraisDomicile);    
        console.log('prixAffiche   >>>>    '+prixAffiche);    

        // 4. GÉNÉRATION DES COLONNES CONDITIONNELLES
        const colVolume = (modeCalcul === 'volume') ? `
        <td class="px-2 py-3">
            <div class="flex flex-col items-center justify-end h-full">
                <span class="text-[8px] font-bold text-gray-400 uppercase mb-1">Vol m³</span>
                <input type="text" class="input-vol w-16 p-1 border-none bg-transparent font-mono text-[10px] font-bold text-gray-500 text-center" 
                    value="${article.volume_article || '0.0000'}" readonly>
            </div>
        </td>` : '';

        const colPrix = `
        <td class="px-2 py-3">
            <div class="flex flex-col items-center">
                <span class="text-[8px] font-bold text-blue-500 uppercase mb-1">${labelPrix}</span>
                <input type="number" name="items[${article.id}][prix_unitaire]" 
                    class="input-prix-unit w-16 p-1 border-blue-200 border rounded-md text-center text-xs font-bold ${classLockedPrix}" 
                    value="${prixAffiche.toFixed(2)}" ${peutModifier ? '' : 'readonly'}>
            </div>
        </td>`;

        // 5. CRÉATION DE LA LIGNE
        const row = document.createElement('tr');
        row.className = "hover:bg-gray-50 transition-colors align-top border-b";
        row.setAttribute('data-fixe', isFixe ? '1' : '0');
        row.setAttribute('data-article-id', article.id);

        row.innerHTML = `
        <td class="px-3 py-4 min-w-[150px] sticky-column bg-white">
            <div class="text-sm font-black text-gray-900 leading-tight">${article.libelle}</div>
            <div class="text-[9px] text-gray-400 uppercase mt-1">${isFixe ? 'Standard' : 'Libre'}</div>
        </td>

        <td class="px-3 py-4">
            <div class="flex flex-col items-center justify-end h-full">
                <span class="text-[9px] font-bold text-gray-400 uppercase mb-1">Qté</span>
                <input type="number" name="items[${article.id}][qte]" class="input-qte w-12 p-1.5 border rounded-lg font-bold text-center text-sm" value="1" min="1">
            </div>
        </td>

        <td class="px-3 py-4">
            <div class="flex flex-col items-center justify-end h-full">
                <span class="text-[9px] font-bold text-gray-400 uppercase mb-1">Dimensions (cm)</span>
                <div class="flex gap-1">
                    <div class="flex flex-col items-center">
                        <label class="text-[7px] font-bold text-gray-300 uppercase">L</label>
                        <input type="number" name="items[${article.id}][longueur]" class="input-dim w-8 p-1 border rounded-md text-center text-[10px] font-bold ${classLocked}" value="${article.longueur || 0}" ${attrReadonly}>
                    </div>
                    <div class="flex flex-col items-center">
                        <label class="text-[7px] font-bold text-gray-300 uppercase">l</label>
                        <input type="number" name="items[${article.id}][largeur]" class="input-dim w-8 p-1 border rounded-md text-center text-[10px] font-bold ${classLocked}" value="${article.largeur || 0}" ${attrReadonly}>
                    </div>
                    <div class="flex flex-col items-center">
                        <label class="text-[7px] font-bold text-gray-300 uppercase">H</label>
                        <input type="number" name="items[${article.id}][hauteur]" class="input-dim w-8 p-1 border rounded-md text-center text-[10px] font-bold ${classLocked}" value="${article.hauteur || 0}" ${attrReadonly}>
                    </div>
                </div>
            </div>
        </td>

        <td class="px-3 py-4">
            <div class="flex flex-col items-center justify-end h-full">
                <span class="text-[9px] font-bold text-gray-400 uppercase mb-1">Poids (Kg)</span>
                <input type="number" name="items[${article.id}][poids]" class="input-poids w-14 p-1.5 border rounded-lg text-center text-sm" value="${article.poids || 0}">
            </div>
        </td>

        ${colVolume}

        <td class="px-3 py-4">
            <div class="flex flex-col items-center justify-end h-full">
                <span class="text-[9px] font-bold text-gray-400 uppercase mb-1">État & Valeur</span>
                <select name="items[${article.id}][etat]" class="input-etat w-24 p-1 text-[10px] border rounded mb-1">
                    <option value="neuf">Neuf</option>
                    <option value="bon" selected>Bon état</option>
                    <option value="abime">Abîmé / Rayé</option>
                    <option value="casse">Cassé / HS</option>
                </select>
                <input type="number" name="items[${article.id}][valeur_caf]" class="input-caf w-24 p-1 border rounded text-[10px]" placeholder="Valeur CAF (€)">
            </div>
        </td>

        <td class="px-3 py-4">
            <div class="flex flex-col items-center justify-end h-full">
                <span class="text-[9px] font-bold text-gray-400 uppercase mb-1">Notes défauts</span>
                <textarea name="items[${article.id}][notes_defaut]" class="w-24 p-1 text-[10px] border rounded leading-tight" rows="2" placeholder="Défauts..."></textarea>
            </div>
        </td>

        ${colPrix}

        <td class="px-3 py-4 text-right">
            <div class="flex flex-col items-end justify-end h-full">
                <span class="text-[9px] font-bold text-gray-400 uppercase mb-1">Total (€)</span>
                <div class="text-sm font-black text-gray-900 py-1.5"><span class="row-total-price">0.00</span></div>
            </div>
        </td>

        <td class="px-3 py-4 text-center">
            <button type="button" onclick="window.formulaireEvenement.supprimerLigne(this)" class="text-red-300 hover:text-red-500 pt-6">
                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1-1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd"></path></svg>
            </button>
        </td>
        `;

        tbody.appendChild(row);
        this.activerCalculsLigne(row);
    }

    supprimerLigne(bouton) {
        // 1. On identifie la ligne (tr) du bouton cliqué
        const ligne = bouton.closest('tr');
        
        // 2. On la supprime du DOM
        ligne.remove();
        
        // 3. On vérifie si le tableau est vide pour réafficher le message "Aucun article"
        const tbody = document.getElementById('liste-articles-body');
        if (tbody && tbody.querySelectorAll('tr:not(#empty-list-message)').length === 0) {
            const emptyMsg = document.getElementById('empty-list-message');
            if (emptyMsg) emptyMsg.classList.remove('hidden');
        }
        
        // 4. C'EST ICI : On recalcule les totaux globaux (bandeau noir)
        this.calculerTotauxGlobaux();
    }



    activerCalculsLigne(row) {
        // On sélectionne tous les types d'inputs qui doivent déclencher un recalcul
        const inputs = row.querySelectorAll('.input-dim, .input-qte, .input-poids, .input-prix-unit, .input-caf');
        const volInput = row.querySelector('.input-vol');
        const priceDisplay = row.querySelector('.row-total-price');
        const tarifRevientPoids = parseFloat(document.getElementById('tarif_revient_poids_entite')?.value) || 0;


        const recalculer = () => {
            // --- 1. RÉCUPÉRATION DU VOLUME UNITAIRE ---
            let volumeUnitaire = 0;
            const isFixe = row.getAttribute('data-fixe') === '1';

            if (isFixe) {
                // Pour les fixes, on récupère la valeur stockée (souvent dans un input hidden ou readonly)
                volumeUnitaire = parseFloat(volInput.value) || 0;
            } else {
                // Pour les libres, calcul en direct : (L * l * H) / 1 000 000
                const dims = row.querySelectorAll('.input-dim');
                const L = dims[0] ? (parseFloat(dims[0].value) || 0) : 0;
                const l = dims[1] ? (parseFloat(dims[1].value) || 0) : 0;
                const H = dims[2] ? (parseFloat(dims[2].value) || 0) : 0;
                volumeUnitaire = (L * l * H) / 1000000;
                
                if (volInput) volInput.value = volumeUnitaire.toFixed(4);
            }

            // --- 2. RÉCUPÉRATION DES QUANTITÉS ET PRIX ---
            const qte = parseFloat(row.querySelector('.input-qte').value) || 0;
            const poidsUnitaire = parseFloat(row.querySelector('.input-poids').value) || 0;
            const pUnitaireSaisi = parseFloat(row.querySelector('.input-prix-unit').value) || 0;

            // --- 3. DÉTERMINATION DU MODE DE FACTURATION ---
            const selectDepart = document.getElementById('depart_id');
            let modeCalcul = 'volume'; 
            if (selectDepart && selectDepart.selectedIndex > 0) {
                const option = selectDepart.options[selectDepart.selectedIndex];
                modeCalcul = option.getAttribute('data-type-calcul').toLowerCase();
            }

            // --- 4. CALCUL DU PRIX TOTAL DE LA LIGNE ---
            let totalLigne = 0;
            if (modeCalcul === 'volume') {
                // Prix = (Volume Unitaire * Quantité) * Prix au m3
                totalLigne = volumeUnitaire * pUnitaireSaisi;
            } else {
                // Prix = (Poids Unitaire * Quantité) * Prix au kg
                totalLigne = poidsUnitaire * (pUnitaireSaisi + tarifRevientPoids);
            }

            // Mise à jour visuelle du prix de la ligne
            if (priceDisplay) {
                priceDisplay.innerText = totalLigne.toFixed(2);
            }

            // --- 5. MISE À JOUR DES TOTAUX GLOBAUX ---
            // Cette fonction va additionner toutes les lignes et mettre à jour le bandeau noir
            this.calculerTotauxGlobaux();
        };

        // Ajout des écouteurs d'événements
        inputs.forEach(input => {
            input.addEventListener('input', recalculer);
        });

        // Également pour le changement d'état (même si ça n'influe pas sur le prix, 
        // c'est bien de garder la structure prête pour l'étape 3)
        const etatSelect = row.querySelector('.input-etat');
        if(etatSelect) etatSelect.addEventListener('change', recalculer);

        // Lancement immédiat pour l'affichage initial après ajout
        setTimeout(recalculer, 50);
}

    mettreAJourTotauxEtape2() {
        let volTotal = 0;
        let prixTotalClient = 0;
        
        const tarifPlancher = window.tarifVolumeParDefaut || 0; 

        const rows = document.querySelectorAll('#liste-articles-body tr:not(#empty-list-message)');
        
        rows.forEach(row => {
            const qte = parseFloat(row.querySelector('.input-qte').value) || 0;
            const volLigne = parseFloat(row.querySelector('.input-vol').value) || 0;
            const prixM3Saisi = parseFloat(row.querySelector('.input-prix-m3').value) || 0;

            const volumeCumuleLigne = volLigne * qte;
            
            volTotal += volumeCumuleLigne;
            prixTotalClient += (volumeCumuleLigne * prixM3Saisi);
        });

        // CALCUL DES PARTS (Selon ton exemple)
        // 1. Part Entité (TS) : Volume Total * Tarif Plancher
        const partEntite = volTotal * tarifPlancher;

        // 2. Part Collecteur : Ce que paie le client - Part Entité
        // (Cela revient bien à : Volume * (Prix Saisi - Tarif Plancher))
        const partCollecteur = prixTotalClient - partEntite;

        // Mise à jour visuelle
        document.getElementById('total-volume-display').innerText = volTotal.toFixed(3);
        document.getElementById('total-prix-display').innerText = prixTotalClient.toFixed(2);
        document.getElementById('total-commission-display').innerText = partCollecteur.toFixed(2);
        
        // On affiche le gain (part collecteur)
        const displayGain = document.getElementById('total-commission-display');
        if (displayGain) {
            displayGain.innerText = partCollecteur.toFixed(2);
            
            // Petit bonus : mettre en rouge si le collecteur vend à perte 
            // par rapport au prix plancher
            displayGain.classList.toggle('text-red-500', partCollecteur < 0);
        }

        // Sauvegarde pour l'étape 3
        this.recapitulatif = {
            volumeTotal: volTotal,
            prixTotalClient: prixTotalClient,
            partCollecteur: partCollecteur,
            partEntite: partEntite
        };
    }

    calculerVolumeTotal() {
        let total = 0;
        const rows = document.querySelectorAll('#liste-articles-body tr');

        rows.forEach(row => {
            const articleId = row.getAttribute('data-article-id');
            const isFixe = row.getAttribute('data-fixe') === '1';
            
            if (isFixe) {
                // Sécurité : on recherche la valeur officielle dans notre DATA_CATALOGUE
                // au lieu de lire la valeur de l'input
                total += this.recupererVolumeOfficiel(articleId);
            } else {
                total += parseFloat(row.querySelector('.input-vol').value) || 0;
            }
        });

        // Affichage du total (si tu as un élément dédié)
        const affichageTotal = document.getElementById('total-volume-cargaison');
        if (affichageTotal) affichageTotal.innerText = total.toFixed(3) + ' m³';
    }

    calculerTotauxGlobaux() {
        // 1. SÉCURITÉ & TITRE
        const selectDepart = document.getElementById('depart_id');
        const titleEl = document.getElementById('type-prise-en-charge-title');

        const modeCalcul = selectDepart.options[selectDepart.selectedIndex].getAttribute('data-type-calcul');

        // Tarifs de revient (Fixés par le gestionnaire, invisibles pour le collecteur)
        const tarifRevientPoids = parseFloat(document.getElementById('tarif_revient_poids_entite')?.value) || 0;
        const tarifRevientVol = parseFloat(document.getElementById('tarif_revient_vol_entite')?.value) || 0;
        
        let typeFacturationActuel = window.typeFacturation || 'volume';

        if (selectDepart && selectDepart.selectedIndex > 0) {
            const option = selectDepart.options[selectDepart.selectedIndex];
            const dataType = option.getAttribute('data-type-calcul');
            if (dataType) typeFacturationActuel = dataType.toLowerCase();
        }

        if (titleEl) {
            titleEl.innerText = typeFacturationActuel === 'volume' 
                ? "Prise en charge en VOLUME" 
                : "Prise en charge en POIDS";
        }

        // 2. TARIFS PLANCHERS (TS) POUR LA PART ENTITÉ
        //const tarifVolTS = window.tarifVolumeParDefaut || 250;
        //const tarifPoidsTS = window.tarifPoidsParDefaut || 3;

        let volTotalGlobal = 0;
        let poidsTotalGlobal = 0;
        let prixTotalClient = 0;
        let partEntiteTotal = 0;
        let qteTotale = 0;

        const rows = document.querySelectorAll('#liste-articles-body tr:not(#empty-list-message)');

        rows.forEach(row => {
            const qte = parseFloat(row.querySelector('.input-qte')?.value) || 0;
            const pUnitaireSaisi = parseFloat(row.querySelector('.input-prix-unit')?.value) || 0;
            const poidsUnitaire = parseFloat(row.querySelector('.input-poids')?.value) || 0;

            // Calcul du volume unitaire (L*l*H)
            const dims = row.querySelectorAll('.input-dim');
            let volUnitaire = 0;
            if (dims.length === 3) {
                volUnitaire = (parseFloat(dims[0].value) * parseFloat(dims[1].value) * parseFloat(dims[2].value)) / 1000000;
            } else {
                // Sécurité pour articles fixes si l'input vol existe
                volUnitaire = parseFloat(row.querySelector('.input-vol')?.value) || 0;
            }

            //const volCumuleLigne = volUnitaire * qte;
            //const poidsCumuleLigne = poidsUnitaire * qte;

            qteTotale += qte;
            volTotalGlobal += volUnitaire;
            poidsTotalGlobal += poidsUnitaire;

            let totalLigne = 0;

            if (typeFacturationActuel === 'volume') {
                totalLigne = volUnitaire * pUnitaireSaisi;
                prixTotalClient += totalLigne;
                partEntiteTotal += (volUnitaire * tarifRevientVol);
            } else {
                totalLigne = poidsUnitaire * (pUnitaireSaisi + tarifRevientPoids);
                prixTotalClient += totalLigne;
                partEntiteTotal += (poidsUnitaire * tarifRevientPoids);
            }

            // Mise à jour du prix total sur la ligne
            const priceDisplay = row.querySelector('.row-total-price');
            if (priceDisplay) priceDisplay.innerText = totalLigne.toFixed(2);
        });

        const gain = prixTotalClient - partEntiteTotal;

        // 3. MISE À JOUR DE L'AFFICHAGE DU BANDEAU
        const elVol = document.getElementById('total-volume-display');
        const elUnit = document.getElementById('unit-display');
        const elPrix = document.getElementById('total-prix-display');
        const elGain = document.getElementById('total-commission-display');
        const elQte = document.getElementById('total-qte-display');
        const elPoids = document.getElementById('total-poids-display');

        if (elVol) {
            elVol.innerText = (typeFacturationActuel === 'volume') ? volTotalGlobal.toFixed(4) : poidsTotalGlobal.toFixed(2);
        }
        if (elUnit) {
            elUnit.innerText = (typeFacturationActuel === 'volume') ? 'm³' : 'kg';
        }
        if (elPrix) elPrix.innerText = prixTotalClient.toFixed(2);
        if (elQte) elQte.innerText = qteTotale;
        if (elPoids) elPoids.innerText = poidsTotalGlobal.toFixed(2) + ' kg';
        
        if (elGain) {
            elGain.innerText = gain.toFixed(2);
            elGain.classList.toggle('text-red-500', gain < 0);
            elGain.classList.toggle('text-emerald-400', gain >= 0);
        }

        // 4. SAUVEGARDE POUR ÉTAPE 3
        this.recapitulatif = { 
            volTotal: volTotalGlobal, 
            poidsTotal: poidsTotalGlobal, 
            prixTotalClient, 
            gain, 
            typeFacturation: typeFacturationActuel,
            partEntite: partEntiteTotal,
            commentaire_general: document.getElementById('commentaire_general')?.value || ''
        };
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

        this.currentFilter = 'directs'; // directs, groupe, partages
        this.filterTexts = {
            'directs': 'Mes clients',
            'groupe': 'Clients du groupe', 
            'partages': 'Clients partagés'
        };

        this.bindFilterButtons(); 

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

    

    bindFilterButtons() {
        console.log('🎛️ Initialisation boutons filtre');
        
        // Texte descriptif pour chaque filtre
        this.filterDescriptions = {
            'directs': 'Recherche dans vos clients directs',
            'groupe': 'Recherche dans les clients de votre groupe', 
            'partages': 'Recherche dans les clients partagés avec vous'
        };
        
        // Bouton "Mes clients"
        const btnDirects = document.getElementById('btn-clients-directs');
        if (btnDirects) {
            btnDirects.addEventListener('click', () => {
                this.setFilter('directs');
            });
        }
        
        // Bouton "Clients du groupe"
        const btnGroupe = document.getElementById('btn-clients-groupe');
        if (btnGroupe) {
            btnGroupe.addEventListener('click', () => {
                this.setFilter('groupe');
            });
        }
        
        // Bouton "Clients partagés"
        const btnPartages = document.getElementById('btn-clients-partages');
        if (btnPartages) {
            btnPartages.addEventListener('click', () => {
                this.setFilter('partages');
            });
        }
    }

    setFilter(type) {
        console.log(`🎯 Changement filtre : ${type}`);
        
        // 1. Mettre à jour la variable
        this.currentFilter = type;
        
        // 2. Mettre à jour l'interface des boutons
        const buttons = ['btn-clients-directs', 'btn-clients-groupe', 'btn-clients-partages'];
        
        buttons.forEach(btnId => {
            const btn = document.getElementById(btnId);
            if (btn) {
                // Retirer toutes les classes actives
                btn.classList.remove('active', 'active-groupe', 'active-partages');
                btn.classList.remove('bg-purple-100', 'bg-blue-100', 'bg-green-100');
                btn.classList.remove('border-purple-500', 'border-blue-500', 'border-green-500');
                btn.classList.remove('text-purple-700', 'text-blue-700', 'text-green-700');
                
                // Classes par défaut pour boutons inactifs
                btn.classList.add('bg-gray-100', 'border-gray-300', 'text-gray-600');
                
                // Appliquer la classe active appropriée
                if (btnId === `btn-clients-${type}`) {
                    btn.classList.remove('bg-gray-100', 'border-gray-300', 'text-gray-600');
                    
                    switch(type) {
                        case 'directs':
                            btn.classList.add('active', 'bg-purple-100', 'border-purple-500', 'text-purple-700');
                            break;
                        case 'groupe':
                            btn.classList.add('active-groupe', 'bg-blue-100', 'border-blue-500', 'text-blue-700');
                            break;
                        case 'partages':
                            btn.classList.add('active-partages', 'bg-green-100', 'border-green-500', 'text-green-700');
                            break;
                    }
                }
            }
        });
        
        // 3. Mettre à jour le texte indicateur
        const filtreType = document.getElementById('filtre-type');
        if (filtreType) {
            filtreType.textContent = this.filterTexts[type];
            
            // Changer la couleur aussi
            filtreType.className = 'font-semibold ' + 
                (type === 'directs' ? 'text-purple-600' :
                type === 'groupe' ? 'text-blue-600' : 'text-green-600');
            
            // Mettre à jour la description
            const descriptionElement = filtreType.nextElementSibling;
            if (descriptionElement && descriptionElement.classList.contains('text-gray-400')) {
                descriptionElement.textContent = ` • ${this.filterDescriptions[type]}`;
            }
        }
        
        // 4. Si une recherche est en cours, relancer avec le nouveau filtre
        if (this.searchInput && this.searchInput.value.trim().length >= 2) {
            this.search();
        }
        
        // 5. Animation visuelle
        const activeBtn = document.getElementById(`btn-clients-${type}`);
        if (activeBtn) {
            activeBtn.style.transform = 'scale(0.98)';
            setTimeout(() => {
                activeBtn.style.transform = 'scale(1)';
            }, 150);
        }
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
            const url = `/collecteur/clients/search?q=${encodeURIComponent(query)}&type=${this.currentFilter}`;
            
            const response = await fetch(url, {
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
 * INITIALISATION UNIQUE DU SYSTÈME
 */
document.addEventListener('DOMContentLoaded', () => {
    console.log('🚀 Démarrage du système (Fichier externe)');

    // 1. On crée l'instance (le constructeur appellera init())
    const formulaire = new FormulaireTroisEtapes();
    
    // 2. On récupère les données de la fenêtre (passées par le Blade)
    formulaire.famillesArticles = window.DATA_CATALOGUE || [];
    
    // 3. Initialisation de la recherche client si la classe existe
    if (typeof ClientSearch !== 'undefined') {
        window.clientSearch = new ClientSearch();
    }

    // 4. On expose l'instance globalement pour les boutons (ex: afficherEtape)
    window.formulaireEvenement = formulaire;

    console.log('✅ Système prêt et catalogue injecté');
});


//A  SUPPRIMER MAIS VOIR SI ON PEUT METTRE TOUT LE JS ICI 

// /**
//  * INITIALISATION QUAND LE DOM EST CHARGÉ
//  */
// document.addEventListener('DOMContentLoaded', () => {
//     console.log('🚀 SDKTRANSIT - Formulaire prise en charge');
    
//     try {
//         // 1. Créer l'instance du formulaire principal
//         window.formulaireEvenement = new FormulaireTroisEtapes();
//         console.log('✅ Formulaire principal initialisé');
        
//         // 2. Créer l'instance de recherche client
//         window.clientSearch = new ClientSearch();
//         console.log('✅ Recherche client initialisée');
        
//         console.log('🎉 Toutes les fonctionnalités sont prêtes !');
        
//     } catch (error) {
//         console.error('❌ Erreur lors de l\'initialisation :', error);
//         alert('Une erreur est survenue lors du chargement du formulaire.');
//     }
// });

/**
 * EXPORT POUR UTILISATION DANS D'AUTRES FICHIERS (OPTIONNEL)
 */
// if (typeof module !== 'undefined' && module.exports) {
//     module.exports = FormulaireTroisEtapes;
// }