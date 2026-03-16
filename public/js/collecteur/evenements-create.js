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
        this.donneesEtape3 = {};              // Données de l'étape 3
        this.currentClientId = null;
        this.destinatairesCache = {}; // Cache pour éviter les appels API répétés
        this.famillesArticles = []; // Stockera le catalogue envoyé par Blade
        this.tomSelectInstance = null; // Instance de la barre de recherche
        this.fichiersPhotos = {};   //Permet de garder la photo en mémoire
        
        // ==================== INITIALISATION ====================
        this.init();
    }
    
    /**
     * INITIALISATION - Configure les événements et l'état initial
     */
    init() {
        console.log('🚀 Initialisation du formulaire de prise en charge');

        // 1. Définir l'état initial
        this.etapeCourante = 1;
        this.totalEtapes = 3; // Pour que mettreAJourBoutonsNavigation fonctionne bien

        // 2. Sécurité : Bloquer la touche Entrée
        this.bloquerEntree();
        
        // 3. Charger les données du catalogue (Blade -> JS)
        this.famillesArticles = window.DATA_CATALOGUE || [];

        // 4. Initialiser les outils techniques (TomSelect, Photo upload)
        // On fait ça AVANT le bindEvents pour que les éléments existent
        this.initialiserComposants();
        //this.initPhotoUpload();

        // 5. Configurer les écouteurs d'événements (Clicks, Change, etc.)
        this.bindEvents();
        
        // 6. Affichage final de l'étape 1
        // Cette fonction appelle maintenant l'esclave visuel sans validation
        this.afficherEtape(1);
        
        console.log('✅ Initialisation terminée');
    }

    //Bloquer la touche entrée
    bloquerEntree() {
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Enter' && e.target.tagName !== 'TEXTAREA') {
                e.preventDefault();
            }
        });
    }


    /**
     * CONFIGURATION DES ÉVÉNEMENTS
     */
    /*********************************************************** CONFIGURATION  & ETAPE 1  *****************************************************************************/

    bindEvents() {
        console.log('🔗 Configuration des événements');

        // ==================== NAVIGATION ENTRE ÉTAPES ====================
        
        // Boutons Suivant
        document.querySelectorAll('.btn-suivant').forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.preventDefault();
                this.etapeSuivante();
            });
        });

        // Boutons Précédent
        document.querySelectorAll('.btn-precedent').forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.preventDefault();
                this.etapePrecedente();
            });
        });

        // Indicateurs (cercles 1, 2, 3) : Navigation libre vers l'ARRIÈRE uniquement
        document.querySelectorAll('.etape-indicator').forEach(indicator => {
            indicator.addEventListener('click', (e) => {
                const etapeCible = parseInt(indicator.getAttribute('data-etape'));
                if (etapeCible < this.etapeCourante) {
                    this.afficherEtape(etapeCible);
                }
            });
        });

        /***********************************************************ETAPE 1 - ÉVÉNEMENTS ÉTAPE 1 (Fusionnés) *****************************************************************************/

        const departSelect = document.getElementById('depart_id');
        if (departSelect) {
            departSelect.addEventListener('change', () => {
                const option = departSelect.options[departSelect.selectedIndex];
                
                // 1. Logique de capacité et affichage
                this.calculerCapaciteRestante();
                this.gererAffichageTypePriseCharge();
                this.mettreAJourResumeEtape1();

                // 2. Sécurité et Recalcul des prix si des articles existent déjà
                if (option && option.value !== "") {
                    const type = (option.getAttribute('data-type-calcul') || 'volume').toLowerCase();
                    window.typeFacturation = type;
                    console.log("🔄 Mode de facturation : " + type);

                    // Déclencher le recalcul automatique des lignes existantes
                    const rows = document.querySelectorAll('#liste-articles-body tr:not(#empty-list-message)');
                    rows.forEach(row => {
                        const input = row.querySelector('.input-qte');
                        if (input) input.dispatchEvent(new Event('input'));
                    });
                    
                    this.calculerTotauxGlobaux();
                }
            });

            //Ajouter la suppression des articles si le changement de départ est différent du caclul - kilo -> volume ou volume -> kilo. Vider la liste des articles
        }

        // Type de prise en charge (Radio boutons Domicile/Dépôt)
        document.querySelectorAll('input[name="type_prise_charge"]').forEach(radio => {
            radio.addEventListener('change', () => this.mettreAJourResumeEtape1());
        });
    }
    
    /*********************************************************** FIN  -  CONFIGURATION  & ETAPE 1  *****************************************************************************/

    
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
        console.log(`🔀 Affichage visuel de l'étape : ${etape}`);

        // 1. Masquer toutes les étapes
        document.querySelectorAll('.etape-content').forEach(div => {
            div.classList.remove('etape-active');
            div.classList.add('hidden');
        });

        // 2. Réinitialiser les indicateurs (cercles)
        document.querySelectorAll('.etape-indicator').forEach(indicator => {
            indicator.classList.remove('etape-active');
        });

        // 3. Activer le bloc actuel
        const etapeElement = document.getElementById(`etape-${etape}`);
        if (etapeElement) {
            etapeElement.classList.add('etape-active');
            etapeElement.classList.remove('hidden');
        }

        // 4. Activer l'indicateur actuel
        const indicatorElement = document.querySelector(`.etape-indicator[data-etape="${etape}"]`);
        if (indicatorElement) {
            indicatorElement.classList.add('etape-active');
        }

        // 5. Mettre à jour l'état de la classe et les boutons
        this.etapeCourante = etape;
        this.mettreAJourBoutonsNavigation(etape);

        // 6. Déclencher les logiques spécifiques à l'arrivée sur une étape
        this.executerLogiqueEntreeEtape(etape);
    }
    
    /**
     * Logiques spécifiques au moment où l'on arrive sur une étape
     */
    executerLogiqueEntreeEtape(etape) {
        switch(etape) {
            case 1:
                this.mettreAJourResumeEtape1();
                break;
            case 2:
                this.initialiserEtape2();
                break;
            case 3:
                this.initialiserEtape3();
                break;
        }
    }

    /**
     * PASSER À L'ÉTAPE SUIVANTE
     */
    etapeSuivante() {
        console.log(`➡️ Tentative de passage à la suite (Actuelle: ${this.etapeCourante})`);

        // 1. On valide l'étape actuelle. Si validerEtape retourne false, on s'arrête.
        if (!this.validerEtape(this.etapeCourante)) {
            return; 
        }

        // 2. Si c'est valide, on sauvegarde les données avant de partir
        this.sauvegarderDonneesEtape(this.etapeCourante);

        // 3. On calcule la prochaine étape
        const prochaineEtape = this.etapeCourante + 1;

        // 4. On demande l'affichage
        this.afficherEtape(prochaineEtape);

        // 5. On remonte en haut de page pour le confort
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }

    /**
     * REVENIR À L'ÉTAPE PRÉCÉDENTE
     */
    etapePrecedente() {
        if (this.etapeCourante > 1) {
            console.log('⬅️ Retour en arrière autorisé');
            this.afficherEtape(this.etapeCourante - 1);
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }
    }

    /**
     * VALIDER UNE ÉTAPE
     */
    validerEtape(etape) {
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
        let estValide = true;
        let messagesErreur = [];

        // Récupération des éléments
        const departEl = document.getElementById('depart_id');
        const clientEl = document.getElementById('client_id');
        const destinataireEl = document.getElementById('destinataire_id');

        // 1. Définition des champs à contrôler
        // On commence par les deux champs toujours présents
        const champsAControler = [
            { el: departEl, nom: 'Un départ' },
            { el: clientEl, nom: 'Un client' }
        ];

        // On ajoute le destinataire seulement s'il n'est pas caché
        const destinataireVisible = destinataireEl && !destinataireEl.classList.contains('hidden');
        if (destinataireVisible) {
            champsAControler.push({ el: destinataireEl, nom: 'Un destinataire' });
        }

        // 2. Boucle de validation visuelle et textuelle
        champsAControler.forEach(champ => {
            if (!champ.el || !champ.el.value) {
                // Applique le contour rouge si le champ est vide
                champ.el.classList.add('border-red-500', 'ring-1', 'ring-red-500');
                messagesErreur.push(`• ${champ.nom}`);
                estValide = false;
            } else {
                // Retire le contour rouge si c'est corrigé
                champ.el.classList.remove('border-red-500', 'ring-1', 'ring-red-500');
            }
        });

        // 3. Affichage de l'alerte SweetAlert2 si erreur de saisie
        if (!estValide) {
            Swal.fire({
                title: 'Champs manquants',
                html: `<div class="text-left">Veuillez sélectionner :<br><br>${messagesErreur.join('<br>')}</div>`,
                icon: 'warning',
                confirmButtonColor: '#3085d6',
                confirmButtonText: 'Compris'
            }).then(() => {
                // Optionnel : On scrolle doucement vers le premier champ en erreur après fermeture
                const premierErreur = document.querySelector('.border-red-500');
                if (premierErreur) {
                    premierErreur.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }
            });
            return false;
        }

        // 4. Vérification spécifique du type de calcul (Poids)
        // On utilise departEl ici pour récupérer l'option sélectionnée
        const selectedOption = departEl.options[departEl.selectedIndex];
        const typeCalcul = selectedOption.getAttribute('data-type-calcul');
        
        if (typeCalcul === 'poids') {
            const typePriseCharge = document.querySelector('input[name="type_prise_charge"]:checked');
            if (!typePriseCharge) {
                Swal.fire({
                    title: 'Option requise',
                    text: 'Veuillez indiquer si la prise en charge est à domicile.',
                    icon: 'info',
                    confirmButtonColor: '#3085d6'
                });
                return false;
            }
        }
        
        console.log('✅ Étape 1 validée');
        return true;
    }
    

    /**
     * VALIDATION SPÉCIFIQUE ÉTAPE 2
     * @returns {boolean} - true si validation réussie
     */
    validerEtape2() {
        // 1. Vérification des articles et de leurs photos obligatoires
        if (!this.validerArticlesTableau()) {
            // La fonction validerArticlesTableau doit maintenant vérifier les photos (voir ci-dessous)
            return false;
        }

        // 2. Validation du commentaire général
        const commGeneral = document.getElementById('commentaire_general');
        if (!commGeneral || commGeneral.value.trim().length < 3) {
            commGeneral.style.setProperty('border', '2px solid #ef4444', 'important');
            commGeneral.style.backgroundColor = '#fef2f2';
            
            Swal.fire({
                icon: 'warning',
                title: 'Commentaire requis',
                text: 'Veuillez laisser un commentaire général sur l\'envoi (min. 3 caractères).',
                confirmButtonColor: '#3085d6'
            });
            commGeneral.focus();
            return false;
        }

        commGeneral.style.border = '';
        commGeneral.style.backgroundColor = '';
        return true;
    }
        
    /*********************************************************** SAUVEGARDE DES ETAPES  *****************************************************************************/

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
            case 3:
                this.sauvegarderDonneesEtape3();
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
            zone_id: document.getElementById('zone_id').value,
            type_calcul: selectedOption.getAttribute('data-type-calcul'),
            type_prise_charge: document.querySelector('input[name="type_prise_charge"]:checked')?.value || 'depot'
        };
        
        console.log('💾 Données étape 1 sauvegardées :', this.donneesEtape1);
    }
    
    /**
     * SAUVEGARDER LES DONNÉES DE L'ÉTAPE 2
     */
    sauvegarderDonneesEtape2() {
        const articlesRecupere = [];
        // On cible toutes les lignes du tableau sauf le message "liste vide"
        const rows = document.querySelectorAll('#liste-articles-body tr:not(#empty-list-message)');

        rows.forEach((row) => {
            // 1. Récupération des dimensions (L, l, H)
            const dims = row.querySelectorAll('.input-dim');
            
            // 2. Récupération de l'ID temporaire pour lier la photo
            const tempIdInput = row.querySelector('input[name*="[temp_id]"]');
            const tempId = tempIdInput ? tempIdInput.value : null;

            // 3. Construction de l'objet article
            articlesRecupere.push({
                article_id: row.getAttribute('data-article-id'),
                quantite: row.querySelector('.input-qte')?.value || 1,
                is_lot: row.querySelector('.input-is-lot')?.checked || false,
                prix_unitaire_saisi: row.querySelector('.input-prix-unit')?.value || 0,
                poids: row.querySelector('.input-poids')?.value || 0,
                
                // Dimensions : on respecte l'ordre L, l, H du HTML
                longueur: dims[0]?.value || 0,
                largeur: dims[1]?.value || 0,
                hauteur: dims[2]?.value || 0,
                
                // État, Valeur et Notes
                valeur_caf: row.querySelector('.input-caf')?.value || 0,
                etat: row.querySelector('.input-etat')?.value || 'bon',
                notes_defaut: row.querySelector('textarea[name*="[notes_defaut]"]')?.value || '',
                
                // Récupération de la photo stockée dans l'objet de classe
                photo: this.fichiersPhotos[tempId] || null
            });
        });

        // On stocke le tout dans la propriété de classe
        this.donneesEtape2 = {
            articles: articlesRecupere
        };
        
        console.log('💾 Données étape 2 sauvegardées :', this.donneesEtape2);
    }

    /**
     * SAUVEGARDER LES DONNÉES DE L'ÉTAPE 3
    */
    sauvegarderDonneesEtape3() {
        this.donneesEtape3 = {
            moyen_paiement: document.getElementById('moyen-paiement').value,
            montant_verse: parseFloat(document.getElementById('montant-verse').value) || 0,
            signature_client: document.getElementById('input-sig-client').value,
            signature_collecteur: document.getElementById('input-sig-collecteur').value,
            promo_id: document.getElementById('applied_promo_id').value,
            confirmation_dette: document.querySelector('input[name="confirmation_dette"]')?.checked || false,
            commentaire_general: document.getElementById('commentaire_general')?.value || ''
        };
        console.log('💾 Données étape 3 (Finales) sauvegardées :', this.donneesEtape3);
    }

    /***********************************************************FIN  -   SAUVEGARDE DES ETAPES  *****************************************************************************/


    /***********************************************************  VALIDER LE FORMULAIRE  *****************************************************************************/
    async validerEtEnvoyer() {
        this.sauvegarderDonneesEtape(this.etapeCourante); //sauvegarde les données de l'étape 3 pour les récupérer dans le tableau

        // 2. VÉRIFICATION DE LA DETTE
        const totalAPayer = this.totalFinalAvecRemise;
        const montantVerse = this.donneesEtape3.montant_verse;
        const confirmationDette = this.donneesEtape3.confirmation_dette;

        if (montantVerse < totalAPayer) {
            if (!confirmationDette) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Reconnaissance de dette',
                    text: 'Attention : Le montant versé est inférieur au total. vous devez cocher la case "Reconnaissance de dette" pour continuer',
                    confirmButtonColor: '#3085d6'
                });
                
                // On peut même scroller vers l'élément pour l'aider
                document.getElementById('dette-confirmation').scrollIntoView({ behavior: 'smooth' });
                return; // ON ARRÊTE L'ENVOI
            }
        }

        // On prépare le payload final
        const payload = {
            ...this.donneesEtape1,
            articles: this.donneesEtape2.articles,
            ...this.donneesEtape3,
            total_js: this.totalFinalAvecRemise
        };

        console.log("*******************************")
        console.log("Affichage de toutes les données")
        console.log(payload)
        console.log("*******************************")

        // 4. AFFICHER LE LOADER    A REMETTRE
        // const loader = document.getElementById('global-loader');
        // if(loader) loader.classList.remove('hidden');

       // 5. Envoi AJAX (Fetch)
        try {
            // On utilise FormData pour permettre l'envoi des photos
            const formData = new FormData();

            // 1. On remplit le FormData avec objet payload actuel
            // (Note: Si payload contient des fichiers, il faut les ajouter un par un)
            Object.keys(payload).forEach(key => {
                if (key === 'articles') {
                    // Pour les articles, on les ajoute un par un pour Laravel
                    payload.articles.forEach((article, index) => {
                        Object.keys(article).forEach(subKey => {

                        let value = article[subKey];

                        // 🚀 CORRECTION : Si c'est la photo et que c'est du Base64
                        if (subKey === 'photo' && typeof value === 'string' && value.startsWith('data:image')) {
                            alert('c en Base64 !!!!! ')
                            const blob = this.dataURLtoBlob(value);
                            
                            formData.append(`articles[${index}][photo]`, blob, `photo_${index}.png`);

                        }else{
                            formData.append(`articles[${index}][${subKey}]`, article[subKey]);
                        }                            

                            
                        });
                    });
                } else {
                    formData.append(key, payload[key]);
                }
            });

            const response = await fetch('/collecteur/evenements', { // Solution 1: URL courte
                method: 'POST',
                headers: {
                    // 'Content-Type' ne doit PAS être défini ici pour FormData
                    'Accept': 'application/json', // <--- INDISPENSABLE pour éviter le "<!DOCTYPE"
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: formData // On envoie le FormData
            });

            // Maintenant, même en cas d'erreur, Laravel répondra en JSON
            const result = await response.json();

            if (response.ok && result.success) {
                alert("Collecte enregistrée avec succès !");
                window.location.href = result.redirect_url;
            } else {
                // Tu verras ici précisément pourquoi Laravel n'est pas content
                console.error("Erreurs de validation :", result.errors);
                alert("Erreur : " + (result.message || "Problème lors de l'enregistrement"));
            }
        } catch (error) {
            console.error("Erreur technique :", error);
            alert("Une erreur technique est survenue.");
        }
    }    
    /*********************************************************** FIN  -   VALIDER LE FORMULAIRE  *****************************************************************************/


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

    initialiserEtape3(){
        this.preparerEtapeRecapitulatif();
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

    /***********************************************************CHARGEMENT DES CLIENTS ET DESTINATAIRES*****************************************************************************/
    
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
            const displayText = `${displayName} (${dest.code_unique}) - ZONE -> [ ${dest.zone_id} ]`;
            options += `<option value="${dest.id}" data-zone="${dest.zone_id}">📍 ${displayText}</option>`;
        });
        
        select.innerHTML = options;
        select.classList.remove('hidden');
        
        // Sélectionner le premier par défaut
        if (destinataires.length > 0) {
            // select.value = destinataires[0].id; // Optionnel : auto-sélection
            this.updateResumeDestinataire(`${destinataires[0].prenom} ${destinataires[0].nom}`);
        }
        
        // Ajouter l'écouteur d'événement pour le changement
        select.addEventListener('change', (e) => {
            // 1. Récupérer la zone-id depuis l'option sélectionnée
            const selectedOption = e.target.options[e.target.selectedIndex];
            const zoneId = selectedOption ? selectedOption.getAttribute('data-zone') : '';

            // 2. Mettre à jour le champ hidden
            const hiddenZone = document.getElementById('zone_id');
            if (hiddenZone) {
                hiddenZone.value = zoneId;
            }

            // 3. Tes fonctions de mise à jour habituelles
            this.mettreAJourResumeEtape1();
            //this.validerEtape(1);
            
            console.log("✅ Zone ID synchronisée dans le hidden :", zoneId);
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
    
    /*   
    /***********************************************************  FIN   -   CHARGEMENT DES CLIENTS ET DESTINATAIRES*****************************************************************************/



    /*********************************************************** ETAPE 2 ARTICLE ET SELECTION*****************************************************************************/
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
        const tempId = 'item_' + Date.now() + '_' + Math.floor(Math.random() * 1000); //Pour la photo
        const row = document.createElement('tr');
        row.className = "hover:bg-gray-50 transition-colors align-top border-b";
        row.setAttribute('data-fixe', isFixe ? '1' : '0');
        row.setAttribute('data-article-id', article.id);
        row.id = `row-${tempId}`;

        row.innerHTML = `
        <td class="px-3 py-4 min-w-[150px] sticky-column bg-white">
            <div class="text-sm font-black text-gray-900 leading-tight nom-article">${article.libelle}</div>
            <div class="text-[9px] text-gray-400 uppercase mt-1 mb-2">${isFixe ? 'Standard' : 'Libre'}</div>
            
            <div class="mt-2 flex items-center gap-2">
                <button type="button" 
                    onclick="window.formulaireEvenement.ouvrirAppareilPhoto('${tempId}', this, '${article.libelle.replace(/'/g, "\\'")}')"
                    class="btn-photo-item flex items-center gap-1 px-2 py-1 bg-blue-50 text-blue-600 rounded-md border border-blue-200 hover:bg-blue-100 transition-colors">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    </svg>
                    <span class="text-[10px] font-bold uppercase">Photo</span>
                </button>
                
                <span class="photo-status hidden text-green-500" id="status-${tempId}">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                    </svg>
                </span>
            </div>

            <input type="hidden" name="items[${article.id}][temp_photo_path]" class="input-temp-photo" id="input-${tempId}">
            <input type="hidden" name="items[${article.id}][temp_id]" value="${tempId}">
        </td>

        <td class="px-3 py-4">
            <div class="flex flex-col items-center justify-end h-full">
                <div class="mt-2 flex items-center gap-1 cursor-pointer group" title="Cocher si la quantité multiplie le prix">
                    <input type="checkbox" name="items[${article.id}][is_lot]" class="input-is-lot w-3.5 h-3.5 text-blue-600 border-gray-300 rounded focus:ring-blue-500 cursor-pointer">
                    <label class="text-[9px] font-black text-gray-400 uppercase group-hover:text-blue-600 cursor-pointer transition-colors">Lot</label>
                </div>
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
                    <option value="bon_etat" selected>Bon état</option>
                    <option value="defaut">Abîmé / Rayé</option>
                    <option value="HS">HS</option>
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

        // Demander confirmation avant toute action
        const confirmation = confirm("Voulez-vous vraiment supprimer cet article ?");
        
        // Si l'utilisateur clique sur "Annuler", on arrête tout
        if (!confirmation) {
            return;
        }

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
        // On sélectionne tous les types d'inputs qui doivent déclencher un recalcul + la checkbox Lot
        const inputs = row.querySelectorAll('.input-dim, .input-qte, .input-poids, .input-prix-unit, .input-caf, .input-is-lot');
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

            // --- 2. RÉCUPÉRATION DES QUANTITÉS ET PRIX ET OPTION LOT---
            const qte = parseFloat(row.querySelector('.input-qte').value) || 0;
            const poidsUnitaire = parseFloat(row.querySelector('.input-poids').value) || 0;
            const pUnitaireSaisi = parseFloat(row.querySelector('.input-prix-unit').value) || 0;
            const isLot = row.querySelector('.input-is-lot')?.checked || false;

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
                //Prix = (poidsUnitaire * pUnitaireSaisi)
                //Le pUnitaireSaisi contient le tarifKiloRevient et le tarifKiloVenteDefaut
                totalLigne = poidsUnitaire * pUnitaireSaisi;
            }

            // !!! APPLICATION DU MULTIPLICATEUR SI C'EST UN LOT
            if (isLot) {
                totalLigne = totalLigne * qte;
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
            const typeEvent = input.type === 'checkbox' ? 'change' : 'input';
            input.addEventListener(typeEvent, recalculer);
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
        const rows = document.querySelectorAll('#liste-articles-body tr:not(#empty-list-message) ');

        rows.forEach(row => {
            const articleId = row.getAttribute('data-article-id');
            const isFixe = row.getAttribute('data-fixe') === '1';

            const qte = parseFloat(row.querySelector('.input-qte')?.value) || 0;
            const isLot = row.querySelector('.input-is-lot')?.checked || false;

            let volumeUnitaireLigne = 0;
            
            if (isFixe) {
                // Sécurité : on recherche la valeur officielle dans notre DATA_CATALOGUE
                // au lieu de lire la valeur de l'input
                volumeUnitaireLigne = this.recupererVolumeOfficiel(articleId);
            } else {
                volumeUnitaireLigne = parseFloat(row.querySelector('.input-vol').value) || 0;
            }

            // !!! LOGIQUE DE CUMUL DU VOLUME
            // Si c'est un lot (ex: 10 cartons), on multiplie par la quantité.
            // Sinon (ex: 1 palette de 50 cartons), on ne compte que le volume de la palette.
            if (isLot) {
                total += (volumeUnitaireLigne * qte);
            } else {
                total += volumeUnitaireLigne;
            }
        });

        // Affichage du total (si tu as un élément dédié)
        const affichageTotal = document.getElementById('total-volume-cargaison'); //Existe pas
        if (affichageTotal) affichageTotal.innerText = total.toFixed(3) + ' m³';

        // On retourne le total au cas où une autre fonction en aurait besoin
        return total;
    }

    calculerTotauxGlobaux() {
        // 1. SÉCURITÉ & TITRE
        const selectDepart = document.getElementById('depart_id');
        const titleEl = document.getElementById('type-prise-en-charge-title');

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
            //RÉCUPÉRATION DE L'OPTION LOT
            const isLot = row.querySelector('.input-is-lot')?.checked || false;

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


            let totalLigne = 0;
            let partEntiteLigne = 0;

            // Multiplicateur : si c'est un lot, on multiplie par Qte, sinon par 1
            const multiplicateur = isLot ? qte : 1;

            if (typeFacturationActuel === 'volume') {
                totalLigne = (volUnitaire * pUnitaireSaisi) * multiplicateur;
                partEntiteLigne = (volUnitaire * tarifRevientVol) * multiplicateur;
                
                // Pour le bandeau noir, on cumule le volume réel transporté
                volTotalGlobal += (volUnitaire * multiplicateur);
                poidsTotalGlobal += (poidsUnitaire * multiplicateur);
            } else {
                // !!! CORRECTION : pUnitaireSaisi contient déjà la base + majoration, 
                // on ne rajoute pas tarifRevientPoids ici pour le client
                totalLigne = (poidsUnitaire * pUnitaireSaisi) * multiplicateur;
                partEntiteLigne = (poidsUnitaire * tarifRevientPoids) * multiplicateur;

                volTotalGlobal += (volUnitaire * multiplicateur);
                poidsTotalGlobal += (poidsUnitaire * multiplicateur);
            }   

            console.log("typeFacturationActuel  --->    "+typeFacturationActuel);
            console.log("pUnitaireSaisi  --->    "+pUnitaireSaisi);
            console.log("multiplicateur  --->    "+multiplicateur);
            
            console.log("partEntiteLigne  --->    "+partEntiteLigne);
            console.log("volUnitaire  --->    "+volUnitaire);
            console.log("typeFacturationActuel  --->    "+typeFacturationActuel);

            prixTotalClient += totalLigne;
            partEntiteTotal += partEntiteLigne;
            qteTotale += qte;

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
            elVol.innerText = volTotalGlobal.toFixed(4);
        }
        if (elUnit) {
            elUnit.innerText = 'm³'; // Le chargement reste en m³
        }
        if (elPrix) elPrix.innerText = prixTotalClient.toFixed(2);
        if (elQte) elQte.innerText = qteTotale;
        
        if (elPoids) {
            // Le poids total va dans sa propre ligne en bas
            elPoids.innerText = poidsTotalGlobal.toFixed(2) + ' kg';
        }
        
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

    /***********************************************************FIN   -    ETAPE 2 ARTICLE ET SELECTION*****************************************************************************/



    /*********************************************************** GESTION DES PHOTOS PAR ARTICLE *****************************************************************************/

    /**
     * OUVRE L'APPAREIL PHOTO POUR UN ARTICLE PRÉCIS
     * @param {string} tempId - L'identifiant temporaire de la ligne
     * @param {HTMLElement} btn - Le bouton cliqué
     */
    ouvrirAppareilPhoto(tempId, btn, libelle) { // <-- On récupère le libelle ici
        const cameraInput = document.getElementById('global-camera-input');
        
        cameraInput.onchange = (e) => {
            const file = e.target.files[0];
            if (file) {
                this.fichiersPhotos[tempId] = file;
                const reader = new FileReader();
                reader.onload = (event) => {
                    // On transmet le vrai nom à la galerie
                    this.ajouterMiniatureGalerie(tempId, event.target.result, libelle);
                    
                    // On met le bouton en vert
                    btn.classList.replace('bg-blue-50', 'bg-green-50');
                    btn.classList.replace('text-blue-600', 'text-green-600');
                    btn.innerHTML = `<svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path></svg> <span class="text-[10px] font-bold">OK</span>`;

                    const ligne = document.getElementById(`row-${tempId}`);
                    if (ligne) ligne.classList.remove('bg-red-50', 'border-red-200');   
                };
                reader.readAsDataURL(file);
            }
        };
        cameraInput.click();
    }

    /**
     * TRAITE ET AFFICHE LA PHOTO DANS LA GALERIE
     * Changement des informations dans le ini:
     *  upload_max_filesize = 2M -> passé à 20M
     * post_max_size = 8M        -> passé à 20M
     */
    traiterPhotoArticle(tempId, file, btn) {
        const reader = new FileReader();
        reader.onload = (e) => {
            const base64Image = e.target.result;

            // 1. Mettre à jour le bouton dans le tableau
            btn.classList.replace('bg-blue-50', 'bg-green-50');
            btn.classList.replace('text-blue-600', 'text-green-600');
            btn.innerHTML = `
                <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path></svg>
                <span class="text-[10px] font-bold">OK</span>`;
            
            // 2. Afficher le petit Check vert à côté du bouton
            document.getElementById(`status-${tempId}`).classList.remove('hidden');

            // 3. Ajouter la miniature dans la galerie (Card)
            this.ajouterMiniatureGalerie(tempId, base64Image);

            // 4. (Optionnel) Ici tu lanceras ton upload AJAX vers Laravel
            // this.uploadPhotoAjax(tempId, file);
        };
        reader.readAsDataURL(file);
    }

    /**
     * CRÉÉ LA MINIATURE DANS LA GRILLE
     */
    ajouterMiniatureGalerie(tempId, imageSrc, libelle = "Article") {
        const grid = document.getElementById('photos-grid-container');
        const noMsg = document.getElementById('no-photos-message');
        
        if (!grid) return;
        if (noMsg) noMsg.style.display = 'none';

        // Nettoyage si on reprend une photo pour le même article
        let thumb = document.getElementById(`thumb-${tempId}`);
        if (thumb) thumb.remove();

        // Création du badge
        thumb = document.createElement('div');
        thumb.id = `thumb-${tempId}`;
        
        // Design : Fond noir, bords arrondis, padding généreux
        thumb.className = "w-full flex items-center gap-3 p-3 bg-gray-900 rounded-2xl border border-gray-800 shadow-xl animate-fadeIn overflow-hidden";
        
        thumb.innerHTML = `
        <div class="relative rounded-xl overflow-hidden flex-shrink-0 border border-gray-700 bg-gray-800" 
            style="width: 400px; height: 250px;">
            <img src="${imageSrc}" class="absolute inset-0 w-full h-full object-cover">
        </div>

        <div class="flex flex-col min-w-0 flex-grow">
            <span class="text-[11px] font-black text-blue-400 uppercase truncate tracking-wider leading-tight">${libelle}</span>
            <span class="text-[9px] text-gray-500 truncate mt-1 uppercase font-bold tracking-tighter">Capture validée</span>
        </div>

            <button type="button" 
                onclick="window.formulaireEvenement.supprimerPhoto('${tempId}')"
                class="flex-shrink-0 p-2 text-gray-500 hover:text-red-400 transition-colors">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        `;
        
        grid.appendChild(thumb);
        this.mettreAJourCompteurPhotos();
    }

    supprimerPhoto(tempId) {
        // 1. Demander confirmation avant toute action
        const confirmation = confirm("Voulez-vous vraiment supprimer cette photo ?");
        
        // Si l'utilisateur clique sur "Annuler", on arrête tout
        if (!confirmation) {
            return;
        }

        // 2. Si validé, on procède à la suppression de la vignette
        const thumb = document.getElementById(`thumb-${tempId}`);
        if (thumb) {
            thumb.classList.add('animate-fadeOut'); // Optionnel: petit effet visuel
            setTimeout(() => thumb.remove(), 200);
        }

        // 3. Réinitialiser le bouton dans le tableau (retour au bleu)
        const btn = document.querySelector(`button[onclick*="${tempId}"]`);
        if (btn) {
            btn.className = "btn-photo-item flex items-center gap-1 px-2 py-1 bg-blue-50 text-blue-600 rounded-md border border-blue-200 hover:bg-blue-100 transition-colors";
            btn.innerHTML = `
                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"></path>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"></path>
                </svg>
                <span class="text-[10px] font-bold uppercase">Photo</span>`;
        }

        // 4. Cacher l'indicateur de statut (le check vert) et mettre à jour le compteur
        document.getElementById(`status-${tempId}`)?.classList.add('hidden');
        
        // Si vous avez un input caché pour stocker le fichier, pensez à le vider aussi
        const inputCache = document.getElementById(`input-${tempId}`);
        if (inputCache) inputCache.value = "";

        this.mettreAJourCompteurPhotos();
    }

    mettreAJourCompteurPhotos() {
        const grid = document.getElementById('photos-grid-container');
        if (!grid) return;
        
        const count = grid.querySelectorAll('div[id^="thumb-"]').length;
        document.getElementById('photo-counter').textContent = `(${count})`;
        
        const noMsg = document.getElementById('no-photos-message');
        if (noMsg) {
            noMsg.style.display = (count === 0) ? 'flex' : 'none';
        }
    }

    /***********************************************************FIN   -   GESTION DES PHOTOS PAR ARTICLE *****************************************************************************/

    /**
     * Vérifie que chaque article du tableau est correctement saisi et photographié
     * @returns {boolean}
     */

    validerArticlesTableau() {
        const lignes = document.querySelectorAll('#liste-articles-body tr:not(#empty-list-message)');
        let estValide = true;
        let articlesSansPhotos = [];
        let erreursCount = 0;

        if (lignes.length === 0) {
            Swal.fire('Panier vide', 'Veuillez ajouter au moins un article.', 'warning');
            return false;
        }

        lignes.forEach((ligne) => {
            // On récupère l'ID de la ligne
            const tempIdRaw = ligne.id.replace('row-', '');
            const tempId = tempIdRaw.trim();
            const libelle = ligne.querySelector('.nom-article')?.textContent?.trim() || "Article";
            
            

            // --- DEBUG PHOTO ---
            // On cherche le badge dans TOUTE la page
            const badgePhoto = document.getElementById(`thumb-${tempId}`);
            
            console.log(`Vérification : Article [${libelle}] | ID ligne: [${tempId}] | Badge trouvé:`, badgePhoto);

            if (!badgePhoto) {
                estValide = false;
                articlesSansPhotos.push(libelle);
                ligne.style.backgroundColor = '#fef2f2'; 
                ligne.style.borderLeft = '4px solid #ef4444';
            } else {
                ligne.style.backgroundColor = '';
                ligne.style.borderLeft = '';
            }

            // --- RESTE DE LA VALIDATION (Champs) ---
            const champs = {
                qte: ligne.querySelector('.input-qte'),
                longueur: ligne.querySelector('input[name*="[longueur]"]'),
                largeur: ligne.querySelector('input[name*="[largeur]"]'),
                hauteur: ligne.querySelector('input[name*="[hauteur]"]'),
                poids: ligne.querySelector('.input-poids'),
                valeur: ligne.querySelector('.input-caf'),
                prix: ligne.querySelector('.input-prix-unit')
            };

            Object.entries(champs).forEach(([nom, el]) => {
                if (!el) return;
                const valeur = parseFloat(el.value);
                if (isNaN(valeur) || valeur <= 0) {
                    el.style.setProperty('border-color', '#ef4444', 'important');
                    el.style.setProperty('border-width', '2px', 'important');
                    estValide = false;
                    erreursCount++;
                } else {
                    el.style.removeProperty('border-color');
                    el.style.removeProperty('border-width');
                }
            });

            // --- VÉRIFICATION DU COMMENTAIRE (Note défaut) ---
            const noteDefaut = ligne.querySelector('textarea[name*="[notes_defaut]"]');
            if (noteDefaut) {
                const texte = noteDefaut.value.trim();
                if (texte.length < 3) {
                    noteDefaut.style.setProperty('border-color', '#ef4444', 'important');
                    noteDefaut.style.setProperty('border-width', '2px', 'important');
                    noteDefaut.style.backgroundColor = '#fef2f2';
                    estValide = false;
                    erreursCount++;
                } else {
                    noteDefaut.style.removeProperty('border-color');
                    noteDefaut.style.removeProperty('border-width');
                    noteDefaut.style.backgroundColor = '';
                }
            }


        });

        

        // Affichage Alerte Photo (Prioritaire)
        if (articlesSansPhotos.length > 0) {
            Swal.fire({
                icon: 'error',
                title: 'Photos manquantes',
                html: `Prenez une photo pour : <br><b>${articlesSansPhotos.join(', ')}</b>`,
                confirmButtonColor: '#3B82F6'
            });
            return false;
        }

        if (!estValide) {
            Swal.fire({ icon: 'error', title: 'Saisie incomplète', text: `Il y a ${erreursCount} erreurs.` });
        }

        return estValide;
    }


    /***********************************************************ETAPE 3 - RECAPITULATIF POUR L ETAPE 3 - photo, tarif, promo, signature *****************************************************************************/

    async preparerEtapeRecapitulatif() {
        // 1. Mise à jour des totaux de base
        this.calculerTotauxGlobaux();
        const data = this.recapitulatif;
        

        // 2. Détermination du mode de calcul (via le select de départ)
        const selectDepart = document.getElementById('depart_id');
        let modeCalculActuel = data.typeFacturation; 

        if (selectDepart && selectDepart.selectedIndex > 0) {
            const option = selectDepart.options[selectDepart.selectedIndex];
            const attrCalcul = option.getAttribute('data-type-calcul');
            if (attrCalcul) modeCalculActuel = attrCalcul.toLowerCase();
        }

        // 3. Appel de la sous-fonction pour remplir les tableaux (C'est elle qui utilise volBody, poidsBody, etc.)
        this.remplirTableauxRecap(modeCalculActuel);

        // 4. Reset UI des Promotions
        const promoDiv = document.getElementById('promo-applied');
        const inputPromoId = document.getElementById('applied_promo_id');
        
        this.montantRemiseAppliquee = 0;
        this.totalFinalAvecRemise = data.prixTotalClient;
        
        if(promoDiv) promoDiv.style.display = 'none';
        if(inputPromoId) inputPromoId.value = '';

        // 5. Appel au serveur pour vérifier les promotions
        try {
            const response = await fetch('/collecteur/promotions/verifier', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({
                    total: data.prixTotalClient,
                    client_identifiant: document.getElementById('client_id')?.value,
                    type_calcul: modeCalculActuel
                })
            });

            if (!response.ok) throw new Error('Erreur serveur');
            
            const result = await response.json();

            if (result.success) {
                this.montantRemiseAppliquee = result.montant_remise;
                this.totalFinalAvecRemise = result.nouveau_total;
                
                
                const promoDiv = document.getElementById('promo-applied');
                if(promoDiv) promoDiv.style.display = 'flex';
                
                // 2. On met le libellé
                const libelleEl = document.getElementById('promo-libelle');
                if(libelleEl) libelleEl.innerText = result.libelle;

                // 3. LA FIX : On va chercher l'élément valeur directement ici
                const valeurEl = document.getElementById('promo-valeur');
                if (valeurEl) {
                    console.log("Mise à jour de la remise :", result.montant_remise);
                    valeurEl.innerText = `-${parseFloat(result.montant_remise).toFixed(2)}€`;
                }

                // 4. On stocke l'ID
                const inputPromoId = document.getElementById('applied_promo_id');
                if(inputPromoId) inputPromoId.value = result.promo_id;
            }
        } catch (error) {
            console.error("Erreur lors de la vérification promo:", error);
        }

        // 6. Mise à jour finale des textes financiers et signatures
        document.getElementById('recap-quantite-totale').innerText = document.getElementById('total-qte-display').innerText;
        document.getElementById('recap-sous-total').innerText = data.prixTotalClient.toFixed(2) + '€';
        
        this.mettreAJourAffichageFinance();
        this.initSignatureCanvas();
    }

    remplirTableauxRecap(typeFacturation) {
        const volBody = document.getElementById('recap-volume-body');
        const poidsBody = document.getElementById('recap-poids-body');
        const volContainer = document.getElementById('container-volume');
        const poidsContainer = document.getElementById('container-poids');

        // Reset des contenus
        if (volBody) volBody.innerHTML = '';
        if (poidsBody) poidsBody.innerHTML = '';

        // Parcours des lignes d'articles existantes
        const rows = document.querySelectorAll('#liste-articles-body tr:not(#empty-list-message)');
        
        rows.forEach(row => {
            const tempId = row.id.replace('row-', '');
            const libelle = row.querySelector('.nom-article')?.textContent || "Article";
            const qte = parseFloat(row.querySelector('.input-qte')?.value) || 0;
            const totalLigne = parseFloat(row.querySelector('.row-total-price')?.innerText) || 0;
            const note = row.querySelector('textarea[name*="[notes_defaut]"]')?.value || "";

            // --- AJOUT LOGIQUE LOT ---
            // Vérifie si la checkbox est cochée pour cette ligne
            const isLot = row.querySelector('.input-is-lot')?.checked || false;
            const badgeLot = isLot 
            ? `<span style="background:#3b82f6; color:white; font-size:9px; padding:1px 5px; border-radius:4px; margin-left:5px; font-weight:900; vertical-align:middle;">LOT</span>` 
            : '';

            // Récupération de la miniature
            const photoSrc = document.getElementById(`thumb-${tempId}`)?.querySelector('img')?.src;
            const photoHtml = photoSrc 
                ? `<img src="${photoSrc}" style="width:40px; height:40px; object-fit:cover; border-radius:5px;">`
                : `<span style="font-size:10px; color:#ccc;">N/A</span>`;

            const htmlRow = `
                <tr style="border-bottom: 1px solid #f8f9fa;">
                    <td style="padding: 10px 0;">
                        <div style="font-weight:bold;">${libelle} (x${qte}) ${badgeLot}</div>
                        <div style="font-size:10px; color:#666; font-style:italic;">${note}</div>
                    </td>
                    <td style="text-align:right; font-weight:bold;">${totalLigne.toFixed(2)}€</td>
                    <td style="text-align:right; padding-left:10px;">${photoHtml}</td>
                </tr>
            `;

            // Injection dans le bon tableau selon le mode de facturation
            if (typeFacturation === 'volume' && volBody) {
                volBody.insertAdjacentHTML('beforeend', htmlRow);
            } else if (typeFacturation === 'poids' && poidsBody) {
                poidsBody.insertAdjacentHTML('beforeend', htmlRow);
            }
        });

        // Gestion de la visibilité des blocs
        if(volContainer) volContainer.style.display = (typeFacturation === 'volume') ? 'block' : 'none';
        if(poidsContainer) poidsContainer.style.display = (typeFacturation === 'poids') ? 'block' : 'none';
    }

    calculerResteAPayer() {
        const inputVerse = document.getElementById('montant-verse');
        const elReste = document.getElementById('reste-a-payer');
        const blocDette = document.getElementById('dette-confirmation');
        
        // 1. On récupère la valeur brute sans la modifier immédiatement
        let verse = parseFloat(inputVerse.value) || 0;
        
        // 2. Si le montant dépasse, on prépare la correction
        if (verse > this.totalFinalAvecRemise) {
            // Alerte visuelle : le champ devient rouge
            inputVerse.style.backgroundColor = '#fff1f2'; 
            inputVerse.style.color = '#e53e3e';
            
            // On sature le calcul à 0 pour le reste, mais on laisse l'utilisateur effacer
            var resteCalculé = 0;
        } else {
            // Remise à la normale du style
            inputVerse.style.backgroundColor = '#f9fafb';
            inputVerse.style.color = '#000';
            var resteCalculé = this.totalFinalAvecRemise - verse;
        }

        // 3. Mise à jour de l'affichage du reste
        if (elReste) {
            elReste.innerText = resteCalculé.toFixed(2) + '€';
            
            if (resteCalculé > 0) {
                elReste.style.color = '#c53030'; // Rouge
                if (blocDette) blocDette.style.display = 'block';
            } else {
                elReste.style.color = '#28a745'; // Vert
                if (blocDette) blocDette.style.display = 'none';
            }
        }
    }
    
    mettreAJourAffichageFinance() {
        // On met à jour le gros montant bleu
        const finalDisplay = document.getElementById('recap-final-total');
        if (finalDisplay) {
            finalDisplay.innerText = this.totalFinalAvecRemise.toFixed(2) + '€';
        }
        // On recalcule le reste à payer (Versé - Total final)
        this.calculerResteAPayer();
        
        document.getElementById('montant-verse').addEventListener('blur', (e) => {
            let val = parseFloat(e.target.value) || 0;
            if (val > this.totalFinalAvecRemise) {
                // Là seulement on force la valeur max, car l'utilisateur a fini de taper
                e.target.value = this.totalFinalAvecRemise.toFixed(2);
                this.calculerResteAPayer(); // On relance une fois pour nettoyer les couleurs
            }
        });

    }

    /********SIGNATURES DES COLLECTEURS ET DES CLIENTS**********/

    initSignatureCanvas() {
        const setups = ['client', 'collecteur'];
        setups.forEach(type => {
            const canvas = document.getElementById(`signature-${type}`);
            if (!canvas) return;
            
            const ctx = canvas.getContext('2d');
            let drawing = false;

            // Ajuster la résolution du canvas
            canvas.width = canvas.offsetWidth;
            canvas.height = canvas.offsetHeight;

            const startDrawing = (e) => {
                drawing = true;
                ctx.beginPath();
                ctx.lineWidth = 2;
                ctx.lineCap = 'round';
                ctx.strokeStyle = '#000';
                const pos = getPos(e);
                ctx.moveTo(pos.x, pos.y);
            };

            const draw = (e) => {
                if (!drawing) return;
                e.preventDefault();
                const pos = getPos(e);
                ctx.lineTo(pos.x, pos.y);
                ctx.stroke();
            };

            const stopDrawing = () => {
                drawing = false;
                // Sauvegarder dans l'input hidden en Base64
                document.getElementById(`input-sig-${type}`).value = canvas.toDataURL();
            };

            const getPos = (e) => {
                const rect = canvas.getBoundingClientRect();
                const clientX = e.touches ? e.touches[0].clientX : e.clientX;
                const clientY = e.touches ? e.touches[0].clientY : e.clientY;
                return { x: clientX - rect.left, y: clientY - rect.top };
            };

            canvas.addEventListener('mousedown', startDrawing);
            canvas.addEventListener('mousemove', draw);
            window.addEventListener('mouseup', stopDrawing);
            
            canvas.addEventListener('touchstart', startDrawing);
            canvas.addEventListener('touchmove', draw);
            canvas.addEventListener('touchend', stopDrawing);
        });
    }

    clearSignature(type) {
        const canvas = document.getElementById(`signature-${type}`);
        const ctx = canvas.getContext('2d');
        ctx.clearRect(0, 0, canvas.width, canvas.height);
        document.getElementById(`input-sig-${type}`).value = '';
    }

    /***********************************************************FIN     -     ETAPE 3 - RECAPITULATIF POUR L ETAPE 3 - photo, tarif, promo, signature *****************************************************************************/

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
});
