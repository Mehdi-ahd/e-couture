# Cahier des Charges — VERSION ARCHIVÉE (v1.0)
## Plateforme Intelligente de Couture

**Version** : 1.0 (Draft) — **ARCHIVÉE**
**Date** : Avril 2026
**Statut** : Document historique conservé pour référence

> ⚠️ **DOCUMENT ARCHIVÉ — NE PAS UTILISER COMME RÉFÉRENCE COURANTE**
>
> Ce document est conservé à titre historique. Il décrit la version v1.0 du cahier des charges, **avant** l'introduction des comptes clients, de l'authentification par téléphone, et de la suppression de la hiérarchie d'acteurs.
>
> **Pour la version courante (v1.2)**, consulter :
> - `04a_cahier_des_charges_general.md` — version générale
> - `04b_cahier_des_charges_detaille.md` — spécifications techniques détaillées
> - `01_cas_utilisation.md` — diagramme de cas d'utilisation à jour (25 UCs)
> - `02_diagramme_classes.md` — diagramme de classes à jour

---

## 1. Présentation du Projet

### 1.1 Contexte

Le domaine de la couture professionnelle repose encore largement sur des pratiques artisanales qui, bien qu'efficaces dans certains contextes, présentent des limites importantes en matière de rapidité, de précision et d'organisation. Les couturiers gèrent actuellement leurs clients sur papier ou via des solutions génériques (notes, tableurs), prennent leurs mesures manuellement et créent leurs patrons à la main sans outil numérique intégré.

Ce projet vise à concevoir une plateforme numérique professionnelle qui répond concrètement à ces besoins en combinant la prise de mesures assistée par image, la gestion structurée des clients et la préparation numérique des patrons de vêtements.

### 1.2 Problématique

La gestion manuelle et fragmentée du processus de couture présente plusieurs limites :
- Erreurs et imprécisions dans la prise de mesures
- Perte de temps dans la saisie et le recalcul des dimensions
- Difficulté à retrouver et centraliser les fiches clients
- Création de patrons encore trop manuelle et chronophage
- Absence d'un outil numérique adapté aux besoins métier spécifiques de la couture

### 1.3 Objectif du Système

Fournir une plateforme numérique et mobile destinée aux couturiers professionnels permettant de :
- Prendre des photos guidées pour estimer les mensurations corporelles
- Déduire automatiquement les mensurations secondaires à partir des mesures principales
- Centraliser les fiches clients dans un carnet virtuel privé
- Créer et gérer des modèles de vêtements avec leurs patrons
- Générer des patrons adaptés aux mensurations réelles de chaque client, avec des annotations visuelles

---

## 2. Périmètre Fonctionnel

### 2.1 Ce qui est INCLUS dans le périmètre

| Domaine | Fonctionnalités incluses |
|---------|--------------------------|
| Authentification | Inscription, connexion, espace privé sécurisé par couturier |
| Référentiels | Types de vêtements, types de mensurations, règles de proportion |
| Carnet clients | CRUD des fiches clients, historique des mesures par client |
| Prise de mesures | Guidage photo multi-angles (application mobile), estimation des mensurations, déduction automatique des mesures secondaires, saisie manuelle |
| Modèles de vêtements | Initialisation en 2 étapes : mensurations modèle de référence + patron |
| Gestion des patrons | Upload de fichier, création directe sur la plateforme, génération par IA |
| Annotations patrons | Positionnement des cotations de mensurations sur les pièces du patron |
| Génération adaptée | Calcul automatique du patron aux dimensions du client, mise à jour des annotations |
| Administration | Gestion des comptes couturiers, des référentiels métier, des règles de proportion |

### 2.2 Ce qui est HORS périmètre (V1)

- Espace client (les clients n'ont pas de compte sur la plateforme)
- Usage multi-couturiers / gestion d'atelier avec équipes (prévu V2)
- Génération 3D du vêtement (prévu V3)
- Gestion des commandes clients avec suivi de paiement
- Gestion des stocks et matières
- Application web complète (V1 : application mobile pour la prise de mesures, interface web pour les fonctions de gestion et d'administration)
- Intégration avec des logiciels tiers (ERP, logiciels de patronage existants)

---

## 3. Acteurs et Profils Utilisateurs

### 3.1 Couturier
- **Rôle** : Utilisateur principal de la plateforme
- **Responsabilités** :
  - Gérer son carnet virtuel de clients
  - Réaliser les prises de mesures (guidées par photo ou manuelles)
  - Créer et gérer ses modèles de vêtements et leurs patrons
  - Générer des patrons adaptés aux mesures de chaque client
  - Consulter et exporter les patrons annotés
- **Niveau d'accès** : Complet sur son espace privé (clients, modèles, commandes). Aucun accès aux données des autres couturiers.

### 3.2 Administrateur
- **Rôle** : Gestionnaire technique et métier de la plateforme
- **Responsabilités** :
  - Gérer les types de vêtements disponibles
  - Gérer le référentiel des types de mensurations
  - Définir et maintenir les règles de proportion (formules de déduction)
  - Gérer les comptes couturiers (création, activation, désactivation)
- **Niveau d'accès** : Complet sur les référentiels système. Aucun accès aux données privées des couturiers (clients, mesures, patrons).

---

## 4. Exigences Fonctionnelles

### 4.1 Module Authentification

#### EF-01 : Authentification sécurisée
- Chaque couturier dispose d'un compte unique identifié par son email
- La connexion est protégée par un mot de passe hashé
- Après 5 tentatives échouées, le compte est temporairement verrouillé
- La session expire automatiquement après une période d'inactivité configurable

#### EF-02 : Espace privé du couturier
- Chaque couturier accède uniquement à ses propres données (clients, mesures, modèles)
- L'isolation des données entre couturiers est garantie au niveau du système
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
- Les mensurations PRINCIPALES sont estimables par image
- Les mensurations SECONDAIRES sont calculées automatiquement par règle de proportion

#### EF-05 : Gestion des règles de proportion
- L'administrateur définit les formules de déduction des mensurations secondaires
- Chaque règle spécifie : mensuration source (principale), mensuration cible (secondaire), coefficient, offset
- Formule appliquée : `valeur_cible = (valeur_source × coefficient) + offset`
- Exemple : Carrure dos = Tour épaules × 0.46 + 0
- Une règle désactivée cesse de s'appliquer sur les nouvelles fiches de mesures
- Le système empêche la création de dépendances circulaires entre règles

#### EF-06 : Gestion des comptes couturiers
- L'administrateur crée les comptes avec : nom, prénom, email (unique), téléphone (optionnel)
- Un couturier reçoit ses identifiants par email à la création de son compte
- Un compte désactivé perd immédiatement l'accès à la plateforme, sans suppression des données

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

#### EF-10 : Estimation des mensurations depuis les images
- Le système analyse les images capturées et propose des estimations pour les mensurations PRINCIPALES
- Chaque estimation est accompagnée d'un indicateur de confiance (score de 0 à 1)
- Le couturier peut valider ou corriger manuellement chaque valeur proposée
- Les valeurs estimées sont marquées avec la source ESTIMEE

#### EF-11 : Déduction automatique des mensurations secondaires
- Après validation des mensurations principales, le système applique automatiquement les règles de proportion actives
- Les valeurs calculées sont marquées DEDUITE
- Le couturier peut les corriger manuellement (la source passe alors à MANUELLE)
- La déduction n'écrase jamais une valeur déjà saisie manuellement

#### EF-12 : Saisie manuelle directe
- Le couturier peut saisir toutes les mensurations directement sans prise de photo
- La saisie manuelle est disponible comme méthode principale ou en complément de la prise de photo
- Toutes les valeurs saisies manuellement sont marquées MANUELLE

#### EF-13 : Validation et enregistrement de la fiche de mesures
- Une fiche de mesures peut être enregistrée en BROUILLON (incomplète) ou validée (VALIDEE)
- Seules les fiches VALIDEE peuvent être utilisées pour générer des patrons adaptés
- Une fiche validée reste modifiable jusqu'à ce qu'elle soit utilisée dans une commande

---

### 4.5 Module Modèles de Vêtements

#### EF-14 : Création d'un modèle en deux étapes
- La création d'un modèle de vêtement est structurée en deux étapes séquentielles obligatoires
- Un modèle est inutilisable (statut BROUILLON) tant que les deux étapes ne sont pas complètes
- Le couturier peut interrompre et reprendre la création à tout moment

#### EF-15 : Étape 1 — Mensurations modèle de référence
- Le couturier saisit le profil d'une personne de référence (personne ayant servi à créer le patron original)
- Il renseigne les valeurs des mensurations pertinentes pour ce type de vêtement
- Ces valeurs serviront de base de calcul pour l'adaptation aux mensurations de chaque client
- L'étape 1 est modifiable tant qu'aucune commande n'a été générée

#### EF-16 : Étape 2 — Enregistrement du patron (3 méthodes)
- **Méthode A — Upload** : le couturier importe un fichier existant (image JPG/PNG, PDF, SVG). Le système affiche le fichier et permet de positionner les annotations.
- **Méthode B — Création directe** : le couturier dessine les pièces du patron dans l'éditeur intégré de la plateforme (lignes, courbes, points). Chaque pièce est nommée et peut être réordonnée.
- **Méthode C — Génération par IA** : le couturier décrit le vêtement et ses contraintes. Le système génère une proposition de patron que le couturier peut modifier.
- Quelle que soit la méthode, le résultat final est un patron composé de pièces modifiables

#### EF-17 : Annotations de mensurations sur les patrons
- Le couturier positionne des annotations de mensurations sur les pièces du patron
- Chaque annotation est une cotation (point de départ + point de fin) reliant deux zones de la pièce
- L'annotation est associée à un type de mensuration et affiche la valeur correspondante
- Lors de la génération d'un patron adapté, les valeurs des annotations sont automatiquement mises à jour

---

### 4.6 Module Génération du Patron Adapté

#### EF-18 : Calcul d'adaptation du patron
- Le système calcule les écarts entre les mensurations du client et les mensurations modèle de référence : `écart_i = valeur_client_i - valeur_modèle_i`
- Ces écarts sont appliqués aux dimensions des pièces du patron : `dimension_adaptée = dimension_modèle + écart_i`
- Les valeurs des annotations sont recalculées et affichées avec les dimensions réelles du client

#### EF-19 : Affichage du patron adapté
- Le patron adapté affiche toutes les pièces avec leurs dimensions recalculées
- Les annotations indiquent clairement quelle mensuration correspond à quelle zone de la pièce
- La différence avec le patron modèle original est visualisable (mode comparaison)

#### EF-20 : Export du patron adapté
- Le couturier peut exporter le patron adapté au format PDF ou SVG
- L'export inclut : toutes les pièces, les annotations avec les valeurs du client, les informations du client et du modèle
- L'export est disponible en format impression (avec repères de couture et marges de couture si définies)

---

### 4.7 Module Bibliothèque des Modèles

#### EF-21 : Consultation et recherche des modèles
- Le couturier accède à sa bibliothèque de tous ses modèles de vêtements
- Il peut filtrer par type de vêtement, statut (BROUILLON / ACTIF / ARCHIVE) ou par recherche textuelle
- Il peut consulter les détails d'un modèle, voir ses pièces de patron et ses mensurations de référence

#### EF-22 : Réutilisation d'un modèle
- Un modèle ACTIF peut être réutilisé pour générer un patron adapté pour n'importe quel client
- Le modèle original n'est jamais modifié lors de la génération d'un patron adapté
- Chaque patron adapté est sauvegardé indépendamment et peut être exporté à tout moment

---

## 5. Exigences Non Fonctionnelles

### 5.1 Performance
- Les pages et écrans doivent se charger en moins de 3 secondes sur une connexion mobile correcte (4G)
- Le traitement des images pour l'estimation des mensurations doit être effectué en moins de 10 secondes
- Le calcul d'un patron adapté doit être effectué en moins de 5 secondes

### 5.2 Disponibilité
- La plateforme doit être accessible 7j/7, 24h/24 (objectif 99,5% de disponibilité)
- Les maintenances planifiées sont annoncées 48h à l'avance

### 5.3 Sécurité
- Authentification obligatoire pour tout accès à la plateforme
- Isolation stricte des données entre couturiers (un couturier ne peut jamais accéder aux données d'un autre)
- Toutes les données sont chiffrées en transit (HTTPS/TLS)
- Les mots de passe sont stockés sous forme de hash non réversible (bcrypt ou argon2)
- Les images de clients (photos pour la prise de mesures) sont stockées de façon sécurisée et privée

### 5.4 Fiabilité des données
- Les mensurations validées et utilisées dans une commande ne peuvent plus être supprimées (archivage uniquement)
- Les patrons adaptés générés sont conservés indéfiniment
- Aucune donnée client n'est supprimée définitivement — archivage logique uniquement

### 5.5 Ergonomie et accessibilité
- Application mobile (iOS et Android) pour la prise de mesures avec une interface optimisée pour le terrain
- Interface web pour la gestion des modèles, des patrons et de l'administration
- Formulaires de saisie optimisés pour la rapidité (autofocus, navigation clavier)
- Messages d'erreur explicites avec indications correctives claires
- Interface disponible en français (langue principale), extensible

### 5.6 Traçabilité
- Toute modification d'une fiche de mesures est historisée (date, utilisateur, valeur précédente)
- Les versions des patrons sont conservées (numérotation incrémentale)
- La source de chaque mensuration est systématiquement tracée (ESTIMEE / MANUELLE / DEDUITE)

---

## 6. Modèle de Données Principal

### Entités clés

| Entité | Rôle |
|--------|------|
| `TypeVetement` | Catégorie de vêtement (robe, pantalon, etc.) |
| `TypeMensuration` | Référentiel des mensurations gérées |
| `RegleProportion` | Formule de calcul des mensurations secondaires |
| `Utilisateur` | Classe mère abstraite des comptes |
| `Couturier` | Utilisateur principal de la plateforme |
| `Administrateur` | Gestionnaire des référentiels et comptes |
| `Client` | Fiche client privée d'un couturier |
| `FicheMesure` | Enregistrement d'une prise de mesures complète |
| `LigneMensuration` | Valeur d'une mensuration dans une fiche |
| `ModelVetement` | Modèle de vêtement réutilisable (2 étapes) |
| `MensurationModele` | Mensuration de référence du modèle |
| `Patron` | Patron associé à un modèle (upload / création / IA) |
| `PiecePatron` | Pièce individuelle du patron |
| `AnnotationPatron` | Cotation de mensuration positionnée sur une pièce |
| `CommandeVetement` | Association client + couturier + modèle |
| `PatronAdapte` | Patron recalculé aux mesures réelles du client |

---

## 7. Architecture Applicative Cible

### Découpage technique

| Composant | Technologie envisagée | Rôle |
|-----------|----------------------|------|
| Application mobile | React Native / Expo | Prise de mesures guidée par photo (iOS et Android) |
| Interface web | Application web React | Gestion des clients, modèles, patrons, administration |
| API Backend | Node.js / Express | Logique métier, calculs d'adaptation, gestion des données |
| Base de données | PostgreSQL | Stockage relationnel des données |
| Stockage fichiers | Stockage objet (S3-compatible) | Images, fichiers patrons, exports PDF |
| Module IA mesures | Service de vision par ordinateur | Estimation des mensurations depuis les images |
| Module IA patron | Modèle génératif | Génération de patrons à partir de descriptions |

### Flux de données principal

```
Application Mobile
       │
       │ Photos guidées
       ▼
   API Backend ──────► Module IA Mesures ──► Estimations
       │
       │ Stockage + Calcul de déduction
       ▼
  Base de données (Mensurations validées)
       │
       │ Génération patron adapté
       ▼
   API Backend ──────► Calcul d'adaptation ──► PatronAdapte
       │
       │ Export
       ▼
  Interface Web (visualisation + export PDF/SVG)
```

---

## 8. Risques et Mesures d'Atténuation

| Risque | Impact | Probabilité | Mesure d'atténuation |
|--------|--------|-------------|----------------------|
| Imprécision des estimations de mesures par image | Élevé | Élevée | Guidage strict, validation manuelle obligatoire, indicateur de confiance visible |
| Règles de proportion métier mal définies | Élevé | Moyenne | Validation des règles avec des couturiers professionnels avant déploiement |
| Complexité technique du module IA patron | Élevé | Élevée | MVP sans IA (upload + création manuelle), IA introduite en V2 |
| Faible adoption par les couturiers | Moyen | Moyenne | Interface simple, terminologie métier native, formation courte |
| Surcharge fonctionnelle | Moyen | Élevée | MVP bien délimité, évolution par modules |

---

## 9. Indicateurs de Succès du Projet

### Indicateurs fonctionnels
- Toutes les fonctionnalités prioritaires MVP livrées et opérationnelles
- Guidage photo fonctionnel sur iOS et Android
- Génération de patron adapté aboutissant à un résultat cohérent pour un vêtement de référence

### Indicateurs de qualité
- Taux d'erreur des estimations de mesure inférieur à 2 cm sur les mensurations principales
- Cohérence des dimensions du patron adapté validée par des couturiers experts
- Temps de génération du patron adapté inférieur à 5 secondes

### Indicateurs d'usage
- Nombre de couturiers actifs sur la plateforme
- Nombre de clients enregistrés dans les carnets
- Nombre de patrons adaptés générés
- Taux de réutilisation des modèles de vêtements

### Indicateurs de satisfaction
- Retour positif sur la simplicité d'utilisation (questionnaire post-session)
- Réduction du temps de prise de mesures par rapport à la méthode manuelle habituelle
- Adoption de la plateforme dans le processus de travail quotidien

---

## 10. Planning Indicatif (MVP)

| Phase | Contenu | Durée indicative |
|-------|---------|-----------------|
| Phase 1 | Authentification, carnet clients, prise de mesures manuelle, référentiels | 6 semaines |
| Phase 2 | Guidage photo, estimation des mensurations par image, déduction automatique | 8 semaines |
| Phase 3 | Initialisation des modèles, gestion des patrons (upload + création), annotations | 8 semaines |
| Phase 4 | Génération du patron adapté, export PDF/SVG | 6 semaines |
| Phase 5 | Tests utilisateurs avec couturiers, corrections, stabilisation | 4 semaines |
| **Total MVP** | | **~32 semaines** |

> *Note : La génération de patron par IA (UC-09c) et la visualisation 3D sont positionnées hors MVP, dans les phases de version V2 et V3.*
