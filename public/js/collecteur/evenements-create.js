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
        
        if (clientSelect) {
            clientSelect.addEventListener('change', () => this.mettreAJourResumeEtape1());
        }
        
        if (destinataireSelect) {
            destinataireSelect.addEventListener('change', () => this.mettreAJourResumeEtape1());
        }
        
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
        const destinataire = document.getElementById('destinataire_id').value;
        
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
        const clientSelect = document.getElementById('client_id');
        const clientOption = clientSelect.options[clientSelect.selectedIndex];
        const resumeClient = document.getElementById('resume-client');
        
        if (resumeClient) {
            resumeClient.textContent = clientOption.value 
                ? clientOption.textContent.split('(')[0].trim() 
                : '--';
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
}

/**
 * INITIALISATION QUAND LE DOM EST CHARGÉ
 */
document.addEventListener('DOMContentLoaded', () => {
    console.log('🚀 SDKTRANSIT - Formulaire prise en charge');
    
    try {
        // Créer l'instance du formulaire
        window.formulaireEvenement = new FormulaireTroisEtapes();
        console.log('✅ Formulaire initialisé avec succès');
    } catch (error) {
        console.error('❌ Erreur lors de l\'initialisation du formulaire :', error);
        alert('Une erreur est survenue lors du chargement du formulaire.');
    }
});

/**
 * EXPORT POUR UTILISATION DANS D'AUTRES FICHIERS (OPTIONNEL)
 */
if (typeof module !== 'undefined' && module.exports) {
    module.exports = FormulaireTroisEtapes;
}