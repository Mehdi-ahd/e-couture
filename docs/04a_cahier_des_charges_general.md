# Cahier des Charges — Version Générale
## Plateforme Intelligente de Couture

**Version** : 1.2
**Date** : Avril 2026
**Statut** : En cours de révision

> **Évolution V1 (v1.2)** :
> - Introduction d'un **acteur Client** disposant d'un compte (auto-inscription ou création par un couturier).
> - Authentification possible par **email OU téléphone** (contexte Bénin où l'email n'est pas généralisé).
> - **Suppression de la généralisation entre acteurs** dans le diagramme de cas d'utilisation : `Client`, `Couturier` et `Administrateur` accèdent directement à `S'authentifier`.
> - Ajout d'un module client-facing : annuaire des couturiers, catalogues, contact, notation.
> - Renommage de `CarnetClient` en classe-association `FicheClient` (relation N:M Couturier↔Client).

---

## 1. Présentation du Projet

### 1.1 Contexte

Le domaine de la couture professionnelle repose encore largement sur des pratiques artisanales qui, bien qu'efficaces dans certains contextes, présentent des limites importantes en matière de rapidité, de précision et d'organisation. Les couturiers gèrent actuellement leurs clients sur papier ou via des solutions génériques, prennent leurs mesures manuellement et créent leurs patrons à la main sans outil numérique intégré.

Ce projet vise à concevoir une plateforme numérique professionnelle combinant la prise de mesures assistée par intelligence artificielle, la gestion structurée des clients, et l'accès à une bibliothèque commune de modèles de vêtements préenregistrés avec leurs patrons.

### 1.2 Problématique

La gestion manuelle et fragmentée du processus de couture présente plusieurs limites :
- Erreurs et imprécisions dans la prise de mesures
- Perte de temps dans la saisie et le recalcul des dimensions
- Difficulté à retrouver et centraliser les fiches clients
- Création de patrons encore trop manuelle et chronophage
- Absence d'un outil numérique adapté aux besoins métier spécifiques de la couture
- Impossibilité de capitaliser sur une base de modèles partagée et enrichie collectivement

### 1.3 Objectif du Système

Fournir une plateforme numérique et mobile destinée aux couturiers professionnels permettant de :
- Guider la prise de photos sous plusieurs angles pour estimer automatiquement les mensurations corporelles grâce à l'IA
- Déduire automatiquement les mensurations secondaires à partir des mesures principales
- Centraliser les fiches clients dans un carnet virtuel privé par couturier
- Accéder à une bibliothèque globale de modèles de vêtements préenregistrés
- Générer des patrons adaptés aux mensurations réelles de chaque client, avec des annotations visuelles

---

## 2. Périmètre Fonctionnel

### 2.1 Ce qui est INCLUS dans le périmètre

| Domaine | Fonctionnalités incluses |
|---------|--------------------------|
| Authentification | Inscription, connexion, espace privé sécurisé par couturier |
| Référentiels | Types de vêtements, types de mensurations, règles de proportion |
| Carnet clients | CRUD des fiches clients, historique des mesures par client |
| Prise de mesures | Guidage photo multi-angles (application mobile), estimation des mensurations par IA, déduction automatique des mesures secondaires, saisie manuelle |
| Bibliothèque globale | Base commune de modèles de vêtements préenregistrés, accessible à tous les couturiers |
| Initialisation de modèles | Création de nouveaux modèles en 2 étapes : mensurations modèle de référence + patron |
| Gestion des patrons | Upload de fichier, création directe sur la plateforme, génération par IA |
| Annotations patrons | Positionnement des cotations de mensurations sur les pièces du patron |
| Génération adaptée | Calcul automatique du patron aux dimensions du client, mise à jour des annotations |
| Administration | Gestion des comptes couturiers, des référentiels métier, des règles de proportion, de la bibliothèque globale |

### 2.2 Ce qui est HORS périmètre (V1)

- Usage multi-couturiers / gestion d'atelier avec équipes (prévu V2)
- Génération 3D du vêtement (prévu V3)
- Gestion des commandes clients avec suivi de paiement
- Gestion des stocks et matières
- Intégration avec des logiciels tiers (ERP, logiciels de patronage existants)

---

## 3. Acteurs et Profils Utilisateurs

> **Note V1** : trois acteurs humains de plein droit (`Client`, `Couturier`, `Administrateur`) — plus aucune relation de généralisation entre acteurs.

### 3.0 Client *(nouveau V1)*
- **Rôle** : Personne finale qui souhaite faire confectionner un vêtement.
- **Responsabilités** :
  - Créer son compte par auto-inscription (UC-19) ou voir son compte créé par un couturier (UC-20)
  - Consulter l'annuaire des couturiers et leurs catalogues
  - Contacter un couturier pour une demande
  - Noter un couturier après une commande terminée
- **Niveau d'accès** : Lecture publique sur les couturiers et leurs catalogues. Écriture sur ses propres données (profil, messages envoyés, notes émises).

### 3.1 Couturier
- **Rôle** : Utilisateur principal de la plateforme
- **Responsabilités** :
  - Gérer son carnet virtuel de clients
  - Réaliser les prises de mesures guidées par photo (IA) ou manuellement
  - Parcourir la bibliothèque globale de modèles et les utiliser directement
  - Créer de nouveaux modèles de vêtements et les contribuer à la bibliothèque
  - Générer des patrons adaptés aux mesures de chaque client
  - Consulter et exporter les patrons annotés
- **Niveau d'accès** : Complet sur son espace privé (clients, mesures, commandes). Accès en lecture à la bibliothèque globale. Aucun accès aux données privées des autres couturiers.

### 3.2 Administrateur
- **Rôle** : Gestionnaire technique et métier de la plateforme
- **Responsabilités** :
  - Gérer les types de vêtements disponibles
  - Gérer le référentiel des types de mensurations
  - Définir et maintenir les règles de proportion
  - Gérer et modérer la bibliothèque globale de modèles
  - Gérer les comptes couturiers
- **Niveau d'accès** : Complet sur les référentiels système et la bibliothèque globale. Aucun accès aux données privées des couturiers.

---

## 4. Exigences Fonctionnelles

### 4.1 Module Authentification

#### EF-01 : Authentification sécurisée
- Chaque utilisateur (Client, Couturier, Administrateur) dispose d'un compte unique
- L'identifiant de connexion est l'**email OU le téléphone** (au moins un des deux est obligatoire à la création) — adapté au contexte béninois où l'email n'est pas généralisé
- La connexion est protégée par un mot de passe hashé
- Après 5 tentatives échouées, le compte est temporairement verrouillé
- La session expire automatiquement après une période d'inactivité configurable
- L'unicité est garantie par champ renseigné (un email ne peut être utilisé qu'une seule fois ; idem pour un téléphone)

#### EF-02 : Espace privé du couturier
- Chaque couturier accède uniquement à ses propres données (clients, mesures, commandes)
- L'isolation des données privées entre couturiers est garantie au niveau du système
- L'administrateur ne peut pas consulter les données privées d'un couturier

---

### 4.2 Module Référentiels (Administration)

#### EF-03 : Gestion des types de vêtements
- L'administrateur peut créer, modifier et archiver des types de vêtements
- Chaque type a : code unique, nom, catégorie (HAUT / BAS / ROBE / ENSEMBLE / ACCESSOIRE), description
- Un type archivé reste associé aux modèles existants mais ne peut plus être sélectionné pour de nouveaux modèles

#### EF-04 : Gestion des types de mensurations
- L'administrateur gère le référentiel des mensurations disponibles
- Chaque mensuration a : code, nom, unité (cm), catégorie (PRINCIPALE ou SECONDAIRE), description de la méthode de prise de mesure
- Les mensurations PRINCIPALES sont estimables directement depuis les images par le module IA
- Les mensurations SECONDAIRES sont déduites automatiquement via les règles de proportion

#### EF-05 : Gestion des règles de proportion
- L'administrateur définit les formules de déduction des mensurations secondaires à partir des mesures principales validées
- Une règle désactivée cesse de s'appliquer sur les nouvelles fiches de mesures
- Le système empêche la création de dépendances circulaires entre règles

#### EF-06 : Gestion des comptes couturiers
- L'administrateur crée les comptes avec : nom, prénom, email (unique), téléphone (optionnel)
- Un couturier reçoit ses identifiants à la création de son compte
- Un compte désactivé perd l'accès à la plateforme sans suppression de ses données

---

### 4.3 Module Carnet Clients

#### EF-07 : Gestion des fiches clients
- Le couturier peut créer, modifier, consulter et archiver ses fiches clients
- Chaque fiche contient : nom, prénom, téléphone, email (optionnel), date de naissance (optionnel), notes libres
- La recherche est possible par nom ou numéro de téléphone
- L'archivage d'un client conserve l'intégralité de son historique de mesures

#### EF-08 : Historique des mesures par client
- Chaque client possède un historique chronologique de toutes ses fiches de mesures
- Chaque fiche est datée et indique la méthode utilisée (photo ou manuelle)
- La fiche la plus récente est mise en avant lors de la sélection pour une commande

---

### 4.4 Module Prise de Mesures

#### EF-09 : Guidage photo multi-angles (application mobile)
- Le système guide le couturier lors de la capture en affichant pour chaque angle : l'instruction de positionnement, un guide de posture pour le client, les consignes de distance et de cadrage
- Les angles requis sont : FACE, PROFIL GAUCHE, PROFIL DROIT, DOS
- Le système valide la qualité de l'image avant de passer à l'angle suivant

#### EF-10 : Estimation des mensurations par intelligence artificielle
- À l'issue du protocole de guidage photo, le module IA analyse les images capturées et estime les mensurations principales du client
- Le module IA est spécifiquement entraîné pour opérer sur des images produites par le protocole de guidage défini par la plateforme
- L'IA tient compte des paramètres de capture pour produire des estimations en centimètres réels
- Chaque estimation est accompagnée d'un indicateur de confiance visible par le couturier
- Le couturier peut valider ou corriger manuellement chaque valeur proposée

#### EF-11 : Déduction automatique des mensurations secondaires
- Après validation des mensurations principales, le système calcule automatiquement les mensurations secondaires en appliquant les règles de proportion définies dans le référentiel
- Les valeurs déduites sont clairement distinguées des valeurs estimées et manuelles
- Le couturier peut les corriger manuellement si nécessaire

#### EF-12 : Saisie manuelle directe
- Le couturier peut saisir toutes les mensurations directement sans prise de photo
- La saisie manuelle est disponible comme méthode principale ou en complément de la prise de photo

#### EF-13 : Validation et enregistrement de la fiche de mesures
- Une fiche de mesures peut être enregistrée en BROUILLON ou validée (VALIDEE)
- Seules les fiches VALIDEE peuvent être utilisées pour générer des patrons adaptés
- La source de chaque mensuration est systématiquement tracée (ESTIMEE / MANUELLE / DEDUITE)

---

### 4.5 Module Bibliothèque Globale de Modèles

#### EF-14 : Bibliothèque commune et partagée
- La bibliothèque de modèles de vêtements est **globale** : elle est partagée entre tous les couturiers de la plateforme
- Elle est alimentée et maintenue par l'administrateur, et peut être enrichie par les contributions des couturiers (selon validation)
- Un couturier peut accéder directement à un modèle existant dans la bibliothèque sans avoir à le recréer

#### EF-15 : Consultation et recherche de la bibliothèque
- Le couturier peut parcourir la bibliothèque et filtrer par type de vêtement, catégorie, nom ou mots-clés
- Chaque modèle affiche : nom, type de vêtement, aperçu du patron, mensurations de référence associées
- Le couturier peut prévisualiser un modèle avant de l'utiliser

#### EF-16 : Utilisation d'un modèle de la bibliothèque
- En sélectionnant un modèle de la bibliothèque, le couturier peut immédiatement lancer la génération d'un patron adapté pour un de ses clients
- Le modèle de la bibliothèque n'est jamais modifié par l'usage individuel d'un couturier

#### EF-17 : Contribution d'un nouveau modèle
- Un couturier peut créer un nouveau modèle de vêtement en deux étapes (mensurations de référence + patron) et le soumettre à la bibliothèque globale
- La soumission est validée par l'administrateur avant publication dans la bibliothèque
- Un modèle non encore publié reste visible et utilisable uniquement par son créateur

---

### 4.6 Module Initialisation des Modèles (2 Étapes)

#### EF-18 : Étape 1 — Mensurations modèle de référence
- Le couturier saisit les valeurs des mensurations d'une personne de référence ayant servi à créer le patron original
- Ces valeurs constituent la base de calcul permettant d'adapter le patron aux dimensions de n'importe quel client
- L'étape 1 est modifiable tant qu'aucune commande n'a été générée depuis ce modèle

#### EF-19 : Étape 2 — Enregistrement du patron (3 méthodes)
- **Méthode A — Upload** : le couturier importe un fichier patron existant (image JPG/PNG, PDF, SVG)
- **Méthode B — Création directe** : le couturier dessine les pièces du patron dans l'éditeur intégré de la plateforme
- **Méthode C — Génération par IA** : le couturier décrit le vêtement et ses contraintes, le système génère une proposition de patron
- Quelle que soit la méthode, le résultat final est un patron composé de pièces nommées et annotées

#### EF-20 : Annotations de mensurations sur les patrons
- Le couturier positionne des cotations de mensurations sur les pièces du patron
- Chaque annotation indique à quelle dimension du vêtement correspond une mensuration
- Les valeurs des annotations sont automatiquement mises à jour lors de la génération d'un patron adapté

---

### 4.7 Module Génération du Patron Adapté

#### EF-21 : Calcul d'adaptation du patron
- Le système calcule les écarts entre les mensurations réelles du client et les mensurations de référence du modèle
- Ces écarts sont appliqués aux dimensions de chaque pièce du patron pour produire un patron aux dimensions exactes du client
- Les valeurs des annotations sont recalculées et affichées en temps réel

#### EF-22 : Affichage et export du patron adapté
- Le patron adapté affiche toutes les pièces avec leurs dimensions recalculées et leurs annotations mises à jour
- La comparaison avec le patron modèle original est disponible en mode côte à côte
- L'export est disponible en format PDF ou SVG, prêt à l'impression

---

### 4.8 Module Espace Client *(nouveau V1)*

#### EF-23 : Inscription et création de compte client
- Auto-inscription depuis l'application (UC-19) : nom, prénom, téléphone obligatoire, email optionnel, date de naissance optionnelle, mot de passe
- Création par un couturier depuis sa fiche client (UC-20) : génération d'un mot de passe temporaire envoyé par SMS et/ou email, changement obligatoire à la première connexion
- Vérification du téléphone par code SMS lors de l'auto-inscription

#### EF-24 : Annuaire et catalogues des couturiers
- Le client peut consulter la liste des couturiers de la plateforme (UC-21)
- Filtres par ville, spécialité, note moyenne
- Chaque couturier dispose d'un catalogue public (UC-22) présentant les modèles qu'il propose, avec photos et descriptions

#### EF-25 : Contact et notation
- Le client peut envoyer un message de contact à un couturier (UC-23) : sujet, contenu — le couturier reçoit la notification
- Après une commande au statut TERMINE, le client peut laisser une note de 1 à 5 étoiles avec commentaire optionnel (UC-24)
- La note moyenne du couturier est affichée publiquement dans son profil et dans l'annuaire

---

## 5. Exigences Non Fonctionnelles

### 5.1 Performance
- Les pages et écrans doivent se charger en moins de 3 secondes sur une connexion mobile correcte (4G)
- Le traitement IA des images doit aboutir à des estimations en moins de 10 secondes
- Le calcul d'un patron adapté doit être effectué en moins de 5 secondes

### 5.2 Disponibilité
- La plateforme doit être accessible 7j/7, 24h/24 (objectif 99,5% de disponibilité)
- Les maintenances planifiées sont annoncées 48h à l'avance

### 5.3 Sécurité
- Authentification obligatoire pour tout accès à la plateforme
- Isolation stricte des données privées entre couturiers
- Toutes les données sont chiffrées en transit (HTTPS/TLS)
- Les images de clients sont stockées de façon sécurisée et privée, non accessibles à d'autres couturiers

### 5.4 Fiabilité des données
- Les mensurations validées et utilisées dans une commande ne peuvent plus être supprimées (archivage uniquement)
- Les patrons adaptés générés sont conservés indéfiniment
- Aucune donnée client n'est supprimée définitivement — archivage logique uniquement

### 5.5 Ergonomie et accessibilité
- Application mobile (iOS et Android) pour la prise de mesures, avec interface optimisée pour le terrain
- Interface web pour la gestion des clients, des modèles, des patrons et de l'administration
- Formulaires de saisie optimisés pour la rapidité
- Messages d'erreur explicites avec indications correctives claires
- Interface disponible en français (langue principale), extensible

### 5.6 Traçabilité
- Toute modification d'une fiche de mesures est historisée
- Les versions des patrons sont conservées avec numérotation incrémentale
- La source de chaque mensuration est systématiquement tracée (ESTIMEE / MANUELLE / DEDUITE)

---

## 6. Modèle de Données Principal

### Entités clés

| Entité | Rôle |
|--------|------|
| `TypeVetement` | Catégorie de vêtement (robe, pantalon, etc.) |
| `TypeMensuration` | Référentiel des mensurations gérées |
| `RegleProportion` | Formule de calcul des mensurations secondaires |
| `Utilisateur` | Classe mère abstraite des comptes (id, nom, prenom, email?, telephone?, motDePasseHash) — contrainte : email OR telephone |
| `Couturier` | Utilisateur principal de la plateforme (mobile Flutter) |
| `Administrateur` | Gestionnaire des référentiels, comptes et bibliothèque |
| `Client` | Utilisateur final disposant d'un compte (sous-classe d'`Utilisateur`) |
| `FicheClient` | Classe-association liant Couturier ↔ Client (notes du couturier, archivage) — remplace `CarnetClient` |
| `FicheMesure` | Enregistrement d'une prise de mesures complète |
| `LigneMensuration` | Valeur d'une mensuration dans une fiche |
| `ModelVetement` | Modèle de vêtement (bibliothèque globale ou privé en attente) |
| `MensurationModele` | Mensuration de référence associée au modèle |
| `Patron` | Patron du modèle (upload / création / IA) |
| `PiecePatron` | Pièce individuelle du patron |
| `AnnotationPatron` | Cotation de mensuration positionnée sur une pièce |
| `CommandeVetement` | Association client + couturier + modèle + fiche de mesures validée |
| `NoteCouturier` | Note de 1 à 5 laissée par un client sur un couturier après commande TERMINE |
| `MessageContact` | Message envoyé par un client à un couturier depuis son catalogue |

> Le **patron adapté n'est pas une entité persistée** : il est calculé et affiché à la volée à partir du `Patron` du modèle et des `LigneMensuration` du client.

---

## 7. Architecture Applicative Cible

### Découpage technique

| Composant | Technologie retenue | Rôle |
|-----------|---------------------|------|
| Application mobile | **Flutter** (Dart) | Prise de mesures guidée par photo (iOS et Android) — intégration native avec Google ML Kit et TensorFlow Lite |
| Interface web | Laravel + Blade / Vue | Gestion des clients, bibliothèque, patrons, administration |
| API Backend | **Laravel** (PHP 8+) | Logique métier, calculs d'adaptation, gestion des données, authentification (Sanctum) |
| Traitement asynchrone | Laravel Queues + Horizon | Traitement des images en arrière-plan sans bloquer l'API |
| Module IA mesures | **Microservice Python** (FastAPI + TensorFlow / PyTorch) | Estimation des mensurations depuis les images guidées — appelé par Laravel via HTTP interne |
| Module IA patron | Microservice Python (modèle génératif) | Génération de patrons à partir de descriptions (V2) |
| Base de données | **PostgreSQL** | Stockage relationnel des données |
| Stockage fichiers | Stockage objet S3-compatible (Laravel Storage) | Images de capture, fichiers patrons, exports PDF/SVG |

### Flux de données principal

```
Application Mobile Flutter
       │
       │ 1. Upload des photos guidées (4 angles)
       ▼
   API Laravel
       │
       │ 2. Job asynchrone mis en queue (Horizon)
       ▼
   Laravel Queue Worker
       │
       │ 3. Appel HTTP interne vers le microservice IA
       ▼
   Microservice Python / TensorFlow
   (calibration d'échelle + détection des mensurations)
       │
       │ 4. Résultats JSON retournés à Laravel
       ▼
   API Laravel
   (stockage + calcul règle de trois + déduction secondaires)
       │
       │ 5. Résultats disponibles (polling ou notification)
       ▼
   Application Mobile Flutter
   (affichage + validation + correction manuelle par le couturier)
       │
       │ + Modèle sélectionné dans la bibliothèque globale
       ▼
   Calcul d'adaptation (Laravel)
       │
       ▼
   Patron adapté annoté → Export PDF/SVG
```

---

## 8. Risques et Mesures d'Atténuation

| Risque | Impact | Probabilité | Mesure d'atténuation |
|--------|--------|-------------|----------------------|
| Imprécision des estimations IA de mesures | Élevé | Élevée | Guidage strict, validation manuelle obligatoire, indicateur de confiance visible |
| Règles de proportion métier mal définies | Élevé | Moyenne | Validation avec des couturiers professionnels avant déploiement |
| Complexité technique du module IA patron | Élevé | Élevée | MVP sans IA patron (upload + création manuelle), IA patron introduite en V2 |
| Faible enrichissement de la bibliothèque au démarrage | Moyen | Élevée | Pré-alimentation de la bibliothèque par l'administrateur avant lancement |
| Faible adoption par les couturiers | Moyen | Moyenne | Interface simple, terminologie métier native, bibliothèque pré-remplie |
| Surcharge fonctionnelle | Moyen | Élevée | MVP bien délimité, évolution par modules |

---

## 9. Indicateurs de Succès du Projet

### Indicateurs fonctionnels
- Toutes les fonctionnalités prioritaires MVP livrées et opérationnelles
- Guidage photo fonctionnel sur iOS et Android
- Bibliothèque globale alimentée avec au moins 50 modèles au lancement
- Génération de patron adapté cohérente pour un vêtement de référence

### Indicateurs de qualité
- Taux d'erreur des estimations IA inférieur à 2 cm sur les mensurations principales
- Cohérence des dimensions du patron adapté validée par des couturiers experts
- Temps de génération du patron adapté inférieur à 5 secondes

### Indicateurs d'usage
- Nombre de couturiers actifs sur la plateforme
- Nombre de clients enregistrés dans les carnets privés
- Taux d'utilisation de la bibliothèque globale (vs. création de modèles from scratch)
- Nombre de patrons adaptés générés

### Indicateurs de satisfaction
- Retour positif sur la simplicité d'utilisation
- Réduction du temps de prise de mesures par rapport à la méthode manuelle habituelle
- Adoption de la plateforme dans le processus de travail quotidien

---

## 10. Planning Indicatif (MVP)

| Phase | Contenu | Durée indicative |
|-------|---------|-----------------|
| Phase 1 | Authentification, carnet clients, prise de mesures manuelle, référentiels | 6 semaines |
| Phase 2 | Guidage photo, module IA mesures, déduction automatique | 8 semaines |
| Phase 3 | Bibliothèque globale, initialisation de modèles (upload + création), annotations | 8 semaines |
| Phase 4 | Génération du patron adapté, export PDF/SVG | 6 semaines |
| Phase 5 | Tests utilisateurs avec couturiers, corrections, stabilisation | 4 semaines |
| **Total MVP** | | **~32 semaines** |

> *Note : La génération de patron par IA (méthode C) et la visualisation 3D sont positionnées hors MVP, dans les phases V2 et V3.*
