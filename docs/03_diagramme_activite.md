# Diagramme d'Activité
## Plateforme Intelligente de Couture

---

## Flux 1 — Prise de Mesures Guidée par Photo (Flutter → Laravel → Python)

```
[Début — Couturier authentifié sur l'app Flutter, fiche client sélectionnée]
   │
   ▼
┌─────────────────────────────────────────┐
│ COUTURIER sélectionne un client         │
│ dans son carnet virtuel (Flutter)       │
└─────────────────────────────────────────┘
   │
   ▼
┌─────────────────────────────────────────┐
│ COUTURIER lance une nouvelle            │
│ prise de mesures                        │
└─────────────────────────────────────────┘
   │
   ▼
◇ Méthode choisie ?
   │ Photo guidée (IA)           │ Saisie manuelle directe
   ▼                             ▼
═══════════════════════     ┌────────────────────────────┐
 PROTOCOLE PHOTO FLUTTER    │ COUTURIER saisit            │
═══════════════════════     │ les mensurations principales│
   │                        │ + secondaires directement   │
   ▼                        └──────────────┬─────────────┘
┌─────────────────────────────────────────┐             │
│ FLUTTER affiche le premier angle        │             │
│ de capture : FACE                       │             │
│ + guide de posture, cadrage,            │             │
│   rappel de l'élément de calibration    │             │
└─────────────────────────────────────────┘             │
   │                                                     │
   ▼                                                     │
┌─────────────────────────────────────────┐             │
│ COUTURIER positionne le client          │             │
│ selon les instructions affichées        │             │
└─────────────────────────────────────────┘             │
   │                                                     │
   ▼                                                     │
┌─────────────────────────────────────────┐             │
│ COUTURIER capture la photo              │             │
│ Flutter valide la qualité               │             │
│ (netteté, cadrage, présence calibration)│             │
└─────────────────────────────────────────┘             │
   │                                                     │
   ▼                                                     │
◇ Tous les angles capturés ?                            │
(FACE / PROFIL GAUCHE / PROFIL DROIT / DOS)             │
   │ Non                    │ Oui                        │
   ▼                        ▼                            │
┌──────────────────┐  ┌──────────────────────────────┐  │
│ FLUTTER passe à  │  │ FLUTTER soumet les 4 photos   │  │
│ l'angle suivant  │  │ à l'API Laravel               │  │
│ (retour au guide)│  │ → reçoit un traitementId      │  │
└──────────────────┘  │ FicheMesure.statutTraitement  │  │
   │ (boucle)         │ → EN_ATTENTE                  │  │
   └──────────────────└──────────────┬────────────────┘  │
                                     │                    │
                                     ▼                    │
═══════════════════════════════════════════════════════   │
          PIPELINE ASYNCHRONE (Laravel + Python)          │
═══════════════════════════════════════════════════════   │
                                     │                    │
                                     ▼                    │
                      ┌──────────────────────────────┐   │
                      │ LARAVEL (Horizon) crée un job │   │
                      │ asynchrone de traitement IA   │   │
                      │ FicheMesure.statutTraitement  │   │
                      │ → EN_COURS                    │   │
                      └──────────────┬───────────────┘   │
                                     │                    │
                                     ▼                    │
                      ┌──────────────────────────────┐   │
                      │ MICROSERVICE PYTHON reçoit    │   │
                      │ les images                    │   │
                      │                               │   │
                      │ 1. Détecte l'élément de       │   │
                      │    calibration dans chaque    │   │
                      │    image → coefficient px/cm  │   │
                      │                               │   │
                      │ 2. Détecte les points         │   │
                      │    anatomiques par angle      │   │
                      │                               │   │
                      │ 3. Calcule les mensurations   │   │
                      │    principales en cm réels    │   │
                      │                               │   │
                      │ 4. Attribue un score de       │   │
                      │    confiance à chaque valeur  │   │
                      └──────────────┬───────────────┘   │
                                     │                    │
                                     ▼                    │
◇ Traitement réussi ?                                    │
                    │ Oui                  │ Non          │
                    ▼                      ▼              │
      ┌──────────────────────┐  ┌──────────────────────┐ │
      │ Résultats JSON       │  │ FicheMesure.statut    │ │
      │ retournés à Laravel  │  │ Traitement → ECHEC    │ │
      │ FicheMesure.statut   │  │                       │ │
      │ Traitement → TERMINE │  │ COUTURIER est notifié │ │
      └──────────┬───────────┘  │ → peut reprendre en   │ │
                 │              │   saisie manuelle      │ │
                 │              └──────────┬────────────┘ │
                 │                         │              │
                 │              ───────────┘              │
                 │                                        │
                 ▼                                        │
═══════════════════════════════════════════════════════   │
          RETOUR SUR FLUTTER (polling)                    │
═══════════════════════════════════════════════════════   │
                 │                                        │
                 ▼                                        │
      ┌──────────────────────────────┐                   │
      │ FLUTTER interroge l'API      │                   │
      │ toutes les 2 secondes        │                   │
      │ GET /api/mesures/{id}/statut │                   │
      └──────────────┬───────────────┘                   │
                     │                                    │
                     ▼                                    │
◇ Statut TERMINE ?                                       │
      │ Non (encore EN_COURS)   │ Oui                    │
      ▼                          ▼                        │
┌─────────────────┐  ┌────────────────────────────┐      │
│ FLUTTER affiche │  │ FLUTTER affiche les         │      │
│ un indicateur   │  │ estimations avec leurs      │      │
│ de chargement   │  │ scores de confiance         │      │
│ (boucle)        │  └──────────────┬─────────────┘      │
└─────────────────┘                 │                    │
   │ (boucle)                       │◄───────────────────┘
   └──────────────────────          │ (rejoint ici la saisie manuelle)
                                    │
                                    ▼
                     ┌──────────────────────────────┐
                     │ COUTURIER vérifie et corrige  │
                     │ les valeurs si nécessaire     │
                     │ (valeurs ESTIMEE → MANUELLE   │
                     │  si modifiées)                │
                     └──────────────┬───────────────┘
                                    │
                                    ▼
═══════════════════════════════════════════════════════
           DÉDUCTION DES MENSURATIONS SECONDAIRES
           (API Laravel — règle de trois + proportion)
═══════════════════════════════════════════════════════
                                    │
                                    ▼
                     ┌──────────────────────────────┐
                     │ LARAVEL applique la règle de  │
                     │ trois sur la base des         │
                     │ mensurations de référence     │
                     │ du type de vêtement pivot     │
                     │ (source: DEDUITE_REGLE_DE_TROIS)│
                     └──────────────┬───────────────┘
                                    │
                                    ▼
                     ┌──────────────────────────────┐
                     │ LARAVEL affine avec les       │
                     │ règles de proportion actives  │
                     │ (source: DEDUITE_COMBINEE)    │
                     └──────────────┬───────────────┘
                                    │
                                    ▼
                     ┌──────────────────────────────┐
                     │ COUTURIER consulte et corrige │
                     │ les mensurations déduites     │
                     │ si nécessaire                 │
                     └──────────────┬───────────────┘
                                    │
                                    ▼
                     ┌──────────────────────────────┐
                     │ COUTURIER valide la fiche     │
                     │ FicheMesure → VALIDEE         │
                     └──────────────┬───────────────┘
                                    │
                                    ▼
               [Fin — Mesures enregistrées et associées au client]
```

---

## Flux 2 — Initialisation d'un Modèle de Vêtement et Soumission à la Bibliothèque

```
[Début — Couturier authentifié]
   │
   ▼
┌─────────────────────────────────────────┐
│ COUTURIER crée un nouveau modèle :      │
│ nom, type de vêtement, description      │
│ SYSTÈME : statut BROUILLON, portée PRIVE│
│ étapeActuelle = 1                       │
└─────────────────────────────────────────┘
   │
   ▼
═══════════════════════════════════════════
   ÉTAPE 1 : MENSURATIONS MODÈLE (RÉFÉRENCE)
═══════════════════════════════════════════
   │
   ▼
┌─────────────────────────────────────────┐
│ COUTURIER saisit le profil de la        │
│ personne de référence (taille,          │
│ morphologie générale)                   │
└─────────────────────────────────────────┘
   │
   ▼
┌─────────────────────────────────────────┐
│ Pour chaque mensuration requise         │
│ pour ce type de vêtement :              │
│ COUTURIER saisit la valeur de référence │
└─────────────────────────────────────────┘
   │
   ▼
┌─────────────────────────────────────────┐
│ COUTURIER valide l'étape 1              │
│ SYSTÈME enregistre les MensurationModele│
│ (base de calcul règle de trois)         │
│ étapeActuelle → 2                       │
└─────────────────────────────────────────┘
   │
   ▼
═══════════════════════════════════════════
              ÉTAPE 2 : PATRON
═══════════════════════════════════════════
   │
   ▼
◇ Méthode d'enregistrement du patron ?
   │ Upload           │ Création directe   │ Génération IA (V2)
   ▼                  ▼                    ▼
┌───────────┐  ┌───────────────┐  ┌──────────────────────┐
│ COUTURIER │  │ COUTURIER     │  │ COUTURIER décrit le  │
│ importe   │  │ dessine les   │  │ vêtement (Flutter)   │
│ un fichier│  │ pièces dans   │  │                      │
│ (image,   │  │ l'éditeur     │  │ LARAVEL appelle le   │
│ PDF, SVG) │  │ intégré       │  │ microservice IA      │
│           │  │               │  │ → pièces générées    │
└─────┬─────┘  └──────┬────────┘  └──────────┬───────────┘
      │               │                       │
      └───────────────┴───────────┬───────────┘
                                  ▼
              ┌─────────────────────────────────────────┐
              │ Pièces du patron disponibles             │
              │ dans l'éditeur de la plateforme          │
              └──────────────────────────────┬──────────┘
                                             │
                                             ▼
              ┌─────────────────────────────────────────┐
              │ COUTURIER ajoute les annotations        │
              │ de mensurations sur les pièces          │
              │ (cotations avec positions {x,y})        │
              └──────────────────────────────┬──────────┘
                                             │
                                             ▼
◇ Toutes les mensurations clés sont-elles annotées ?
              │ Non                           │ Oui
              ▼                               ▼
┌──────────────────────────┐   ┌──────────────────────────────┐
│ COUTURIER complète les   │   │ COUTURIER valide l'étape 2   │
│ annotations manquantes   │   │ Patron → VALIDE              │
│ (boucle)                 │   │ ModelVetement → ACTIF        │
└──────────────────────────┘   │ portée : PRIVE               │
              │                └──────────────┬───────────────┘
              └───────────────────────────    │
                                              │
                                              ▼
═══════════════════════════════════════════════════════
        SOUMISSION À LA BIBLIOTHÈQUE GLOBALE
                     (optionnelle)
═══════════════════════════════════════════════════════
                                              │
                                              ▼
◇ COUTURIER souhaite-t-il partager ce modèle ?
              │ Non                          │ Oui
              ▼                              ▼
┌──────────────────────────┐   ┌──────────────────────────────┐
│ Modèle reste ACTIF       │   │ COUTURIER soumet le modèle   │
│ portée PRIVE             │   │ à la bibliothèque globale    │
│                          │   │ ModelVetement → SOUMIS       │
│ [Fin — modèle utilisable │   │ (non modifiable jusqu'à      │
│  par le couturier seul]  │   │  décision de l'admin)        │
└──────────────────────────┘   └──────────────┬───────────────┘
                                              │
                                              ▼
                          [Fin — En attente de modération — UC-18]
```

---

## Flux 3 — Génération du Patron Adapté pour un Client

```
[Début — Couturier authentifié, client avec FicheMesure VALIDEE]
   │
   ▼
┌─────────────────────────────────────────┐
│ COUTURIER sélectionne le client         │
│ concerné                                │
└─────────────────────────────────────────┘
   │
   ▼
◇ Source du modèle de vêtement ?
   │ Bibliothèque globale            │ Mes modèles privés
   ▼                                 ▼
┌──────────────────────────┐  ┌──────────────────────────────┐
│ COUTURIER parcourt la    │  │ COUTURIER sélectionne un     │
│ bibliothèque globale     │  │ de ses modèles ACTIF/PRIVE   │
│ (portée GLOBAL)          │  └──────────────────────────────┘
│ il filtre et sélectionne │
│ un modèle PUBLIE         │
└──────────────┬───────────┘
               │
               └──────────────────┬──────────────────────────┘
                                  │
                                  ▼
┌─────────────────────────────────────────┐
│ COUTURIER sélectionne la fiche          │
│ de mesures du client à utiliser         │
│ (la plus récente proposée par défaut)   │
└─────────────────────────────────────────┘
   │
   ▼
┌─────────────────────────────────────────┐
│ COUTURIER crée la commande              │
│ CommandeVetement → EN_ATTENTE           │
└─────────────────────────────────────────┘
   │
   ▼
═══════════════════════════════════════════
         CALCUL D'ADAPTATION (LARAVEL)
═══════════════════════════════════════════
   │
   ▼
┌─────────────────────────────────────────┐
│ LARAVEL récupère les MensurationModele  │
│ de référence (Étape 1 du modèle)        │
└─────────────────────────────────────────┘
   │
   ▼
┌─────────────────────────────────────────┐
│ LARAVEL récupère les mensurations       │
│ du client (fiche sélectionnée)          │
└─────────────────────────────────────────┘
   │
   ▼
◇ Des mensurations nécessaires sont-elles manquantes ?
   │ Oui                              │ Non
   ▼                                  │
┌──────────────────────────────┐      │
│ LARAVEL signale les mesures  │      │
│ manquantes                   │      │
│ COUTURIER les complète       │      │
│ (saisie manuelle)            │      │
└──────────────┬───────────────┘      │
               └──────────────────────┘
                                      │
                                      ▼
┌─────────────────────────────────────────┐
│ LARAVEL calcule les écarts              │
│ écart_i = valeur_client_i               │
│         - valeur_modèle_i               │
│                                         │
│ Évalue la faisabilité :                 │
│ si écart > seuil critique → avertissement│
└─────────────────────────────────────────┘
   │
   ▼
┌─────────────────────────────────────────┐
│ LARAVEL applique les écarts aux         │
│ dimensions de chaque pièce du patron    │
│ dimension_adaptée = dimension_modèle    │
│                   + écart_i             │
└─────────────────────────────────────────┘
   │
   ▼
┌─────────────────────────────────────────┐
│ LARAVEL met à jour les valeurs          │
│ des annotations (valeurs client réelles)│
│ PatronAdapte → DISPONIBLE               │
│ CommandeVetement → PATRON_GENERE        │
└─────────────────────────────────────────┘
   │
   ▼
═══════════════════════════════════════════
         CONSULTATION ET EXPORT (Flutter)
═══════════════════════════════════════════
   │
   ▼
┌─────────────────────────────────────────┐
│ COUTURIER visualise le patron adapté    │
│ (toutes pièces + annotations mises à   │
│  jour avec dimensions réelles client)  │
└─────────────────────────────────────────┘
   │
   ▼
◇ Action souhaitée ?
   │ Exporter              │ Ajuster              │ Terminer
   ▼                       ▼                      ▼
┌──────────────────┐  ┌──────────────────┐  ┌──────────────────┐
│ Export PDF / SVG │  │ Ajustements      │  │ Commande →       │
│ (prêt impression)│  │ manuels de       │  │ EN_CONFECTION    │
│ PatronAdapte →   │  │ dimensions       │  │                  │
│ EXPORTE          │  │ (nouvelle version│  │ [Fin]            │
└──────────────────┘  │ du PatronAdapte) │  └──────────────────┘
   │                  └──────────────────┘
   ▼
[Fin — Patron disponible pour la confection]
```

---

## Flux 4 — Authentification et Accès à l'Espace Personnel

```
[Début]
   │
   ▼
◇ Interface utilisée ?
   │ Application mobile Flutter       │ Interface web (admin)
   ▼                                  ▼
┌──────────────────────────┐  ┌──────────────────────────┐
│ COUTURIER saisit email   │  │ ADMIN saisit email       │
│ et mot de passe          │  │ et mot de passe          │
│ (Flutter)                │  │ (navigateur web)         │
└──────────────┬───────────┘  └──────────────┬───────────┘
               └──────────────────┬──────────┘
                                  │
                                  ▼
                  ┌──────────────────────────────┐
                  │ API LARAVEL vérifie les       │
                  │ identifiants (Sanctum)        │
                  └──────────────┬───────────────┘
                                 │
                                 ▼
◇ Identifiants corrects ?
               │ Non                       │ Oui
               ▼                           ▼
┌──────────────────────────┐   ┌──────────────────────────┐
│ Message d'erreur affiché │   │ Token Sanctum généré     │
│                          │   └──────────────┬───────────┘
│ Après 5 tentatives :     │                  │
│ compte verrouillé        │                  ▼
│ temporairement           │   ◇ Quel rôle ?
└──────────────────────────┘      │ Couturier        │ Administrateur
               │                  ▼                  ▼
               ▼           ┌────────────────┐  ┌────────────────┐
   [Retour formulaire]     │ Espace privé   │  │ Panneau admin  │
                           │ du couturier   │  │ (référentiels, │
                           │ (Flutter) :    │  │ comptes,       │
                           │ carnet clients,│  │ bibliothèque,  │
                           │ bibliothèque,  │  │ modération)    │
                           │ commandes      │  └───────┬────────┘
                           └───────┬────────┘          │
                                   └──────────┬─────────┘
                                              ▼
                               [Fin — Session ouverte]
```

---

## Flux 5 — Modération de la Bibliothèque Globale (Administrateur)

```
[Début — Administrateur authentifié, au moins un modèle au statut SOUMIS]
   │
   ▼
┌─────────────────────────────────────────┐
│ ADMIN accède à la file de modération    │
│ (liste des ModelVetement statut SOUMIS) │
└─────────────────────────────────────────┘
   │
   ▼
┌─────────────────────────────────────────┐
│ ADMIN sélectionne un modèle à examiner  │
└─────────────────────────────────────────┘
   │
   ▼
┌─────────────────────────────────────────┐
│ ADMIN consulte le détail du modèle :    │
│ — nom, type de vêtement, description    │
│ — aperçu du patron (toutes les pièces)  │
│ — mensurations de référence (Étape 1)   │
│ — annotations positionnées (Étape 2)    │
└─────────────────────────────────────────┘
   │
   ▼
◇ Le modèle est-il conforme ?
   │ Oui                         │ Non
   ▼                             ▼
┌──────────────────────────┐  ┌──────────────────────────────┐
│ ADMIN valide la          │  │ ADMIN rédige un commentaire  │
│ publication              │  │ de rejet explicite           │
│                          │  │                              │
│ ModelVetement → PUBLIE   │  │ ModelVetement → REJETE       │
│ portée → GLOBAL          │  │ portée reste PRIVE           │
│                          │  │                              │
│ Modèle accessible à tous │  │ Le couturier créateur est    │
│ les couturiers dans la   │  │ notifié avec le commentaire  │
│ bibliothèque globale     │  │ → peut corriger et resoumettre│
└──────────────────────────┘  └──────────────────────────────┘
   │                             │
   ▼                             ▼
◇ D'autres modèles en attente ?
   │ Oui                         │ Non
   ▼                             ▼
[Retour à la file             [Fin — File de modération vide]
 de modération]
```
