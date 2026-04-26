# Cahier des Charges — Version Détaillée (Spécifications Techniques)
## Plateforme Intelligente de Couture

**Version** : 1.2
**Date** : Avril 2026
**Statut** : En cours de révision
**Complément de** : `04a_cahier_des_charges_general.md`

> **Évolution V1 (v1.2)** : intégration des comptes clients, authentification par email OU téléphone, suppression de la hiérarchie d'acteurs, ajout du module client-facing (annuaire, catalogues, contact, notation). Voir le préambule de `04a` pour la synthèse.

> Ce document détaille les spécifications techniques qui complètent la version générale du cahier des charges. Il couvre en particulier le fonctionnement précis du module de prise de mesures par intelligence artificielle et le mécanisme de calcul des mensurations secondaires par règle de trois. Les sections non mentionnées ici sont identiques à la version générale.

---

## 1. Corrections et Précisions Apportées au Cahier Général

### 1.1 Bibliothèque de modèles — Nature globale et partagée

La bibliothèque de modèles de vêtements n'est **pas** une bibliothèque privée par couturier. Il s'agit d'une **base commune globale**, alimentée et maintenue à l'échelle de la plateforme entière.

**Implications concrètes :**
- Un modèle de vêtement validé et publié dans la bibliothèque est accessible à l'ensemble des couturiers inscrits sur la plateforme
- Si un modèle correspondant au besoin du couturier existe déjà dans la bibliothèque, il peut l'utiliser directement sans avoir à le recréer
- L'objectif à long terme est de constituer une **large bibliothèque de référence** couvrant les vêtements les plus courants (robes, pantalons, vestes, boubous, abayas, tailleurs, etc.), chacun avec ses mensurations modèle de référence et son patron annoté
- L'administrateur est responsable de la qualité et de la cohérence des modèles publiés dans la bibliothèque
- Un couturier peut contribuer un nouveau modèle, qui reste en statut privé jusqu'à validation et publication par l'administrateur

### 1.2 Intelligence artificielle — Intégration centrale, pas optionnelle

Le module d'intelligence artificielle pour la prise de mesures est une **composante centrale** du système, pas une fonctionnalité périphérique. Il est entraîné et opérationnel dès la fin du protocole de guidage photo. Ce document précise son fonctionnement détaillé.

---

## 2. Spécifications Détaillées : Module IA de Prise de Mesures

### 2.1 Vue d'ensemble du pipeline

Le module IA de prise de mesures opère en trois étapes successives après la capture des photos guidées :

```
[Photos guidées capturées — 4 angles standardisés]
         │
         ▼
  ÉTAPE 1 : Calibration d'échelle
  (conversion pixels → centimètres réels)
         │
         ▼
  ÉTAPE 2 : Détection et mesure des points anatomiques
  (estimation des mensurations principales dans l'image)
         │
         ▼
  ÉTAPE 3 : Calcul des mensurations dérivées par règle de trois
  (à partir des données de référence et des proportions détectées)
         │
         ▼
  [Mensurations principales et secondaires proposées au couturier]
```

---

### 2.2 Étape 1 — Calibration d'échelle (pixels → centimètres)

#### Problématique

Une image capturée par un appareil photo exprime les dimensions en pixels. Or la plateforme a besoin de mensurations réelles en centimètres. Le passage de l'un à l'autre nécessite de connaître le rapport d'échelle entre l'image et la réalité physique, qui varie en fonction :
- de la distance entre l'appareil et le sujet
- des paramètres optiques de l'appareil (focale, résolution)
- de la posture du sujet au moment de la capture

#### Mécanisme de calibration

Le protocole de guidage photo impose un élément de référence physique dans chaque image, dont la dimension réelle est connue. Cet élément sert de repère d'échelle.

**Options d'élément de référence (à valider avec les couturiers) :**
- Une règle graduée tenue par le sujet
- Une feuille A4 positionnée à un endroit défini
- Une distance de capture standardisée avec un marqueur au sol
- Un QR code de taille connue affiché sur un carton

**Calcul du coefficient d'échelle :**
```
coefficient_pixels_par_cm = taille_reference_en_pixels / taille_reference_reelle_en_cm

valeur_reelle_en_cm = valeur_mesuree_en_pixels / coefficient_pixels_par_cm
```

**Exemple :**
Si une feuille A4 (21 cm de large) mesure 420 pixels de large dans l'image :
```
coefficient = 420 pixels / 21 cm = 20 pixels/cm
```
Une distance de 200 pixels dans la même image correspond donc à : 200 / 20 = **10 cm réels**.

#### Gestion des variations d'angle

Les captures sous différents angles (profil, dos) introduisent des distorsions de perspective qui peuvent fausser les mesures. Le système applique des corrections de perspective par angle de capture :
- Vue de face : mesures de largeur (tour de poitrine, tour de taille, tour de hanches, largeur épaules)
- Vue de profil : mesures de profondeur (épaisseur du buste, profil du ventre)
- Vue de dos : mesures de la largeur dorsale, carrure dos

Chaque angle est traité avec son propre coefficient d'échelle calculé à partir de l'élément de référence visible dans cette image.

---

### 2.3 Étape 2 — Détection des mensurations principales par IA

#### Entraînement du modèle

Le modèle IA est entraîné **spécifiquement** sur des images produites selon le protocole de guidage défini par la plateforme. Cet entraînement ciblé lui permet de :
- Identifier les points anatomiques pertinents sur un corps humain habillé
- Estimer les contours corporels sous des vêtements légers
- Fonctionner de manière fiable dans les conditions de capture imposées par le protocole (distance, posture, angle)

#### Mensurations principales estimées

| Mensuration | Angle(s) utilisé(s) | Méthode de détection |
|-------------|--------------------|-----------------------|
| Tour de poitrine | Face | Largeur maximale au niveau des pectoraux |
| Tour de taille | Face | Largeur minimale au niveau de la taille |
| Tour de hanches | Face | Largeur maximale au niveau des hanches |
| Largeur des épaules | Face | Distance entre les deux extrémités des épaules |
| Longueur du buste | Face + Profil | Distance épaule → taille |
| Longueur totale (épaule → genou ou cheville) | Face | Mesure verticale depuis l'épaule |
| Hauteur totale | Face | Taille globale de la silhouette |
| Carrure dos | Dos | Largeur dorsale entre les omoplates |

#### Format de sortie du modèle IA

Pour chaque mensuration, le modèle retourne :
```json
{
  "mensuration": "tour_poitrine",
  "valeur_pixels": 384,
  "valeur_cm": 96.0,
  "confiance": 0.87,
  "source": "ESTIMEE"
}
```

Le score de confiance indique la fiabilité de l'estimation. En dessous d'un seuil configurable (ex : 0.6), le système signale la valeur comme incertaine et invite le couturier à la vérifier manuellement.

---

### 2.4 Étape 3 — Calcul des mensurations dérivées par règle de trois

#### Principe général

Une fois les mensurations principales estimées et validées par le couturier, le système calcule les mensurations secondaires. Ces mensurations ne peuvent pas être directement lues sur une image mais se déduisent à partir des mensurations connues et des **données de référence** du modèle de vêtement sélectionné.

#### Règle de trois appliquée aux proportions

La règle de trois repose sur le principe suivant : **si on connaît les proportions exactes d'une personne de référence dont on a toutes les mensurations, on peut estimer n'importe quelle mensuration d'un client à partir de la mensuration principale connue des deux personnes**.

**Formule générale :**
```
mensuration_cible_client = (mensuration_cible_reference × mensuration_principale_client) / mensuration_principale_reference
```

**Exemple concret :**

La personne de référence du modèle a :
- Tour de poitrine : 90 cm (mensuration principale de référence)
- Tour sous-poitrine : 75 cm (mensuration dérivée de référence)

Le client a :
- Tour de poitrine estimé : 100 cm (mensuration principale du client)

Calcul du tour sous-poitrine du client :
```
tour_sous_poitrine_client = (75 × 100) / 90 = 83.33 cm
```

#### Application des règles de proportion comme raffinement

La règle de trois fournit une estimation de base. Elle est ensuite affinée par les **règles de proportion** définies dans le référentiel par l'administrateur, qui intègrent des coefficients de correction issus de l'expérience métier couture.

**Combinaison des deux mécanismes :**
```
valeur_brute_par_regle_de_trois = (valeur_reference_cible × valeur_principale_client) / valeur_principale_reference

valeur_finale = (valeur_brute × coefficient_metier) + offset_metier
```

Le coefficient métier et l'offset sont définis dans la règle de proportion correspondante. Ils peuvent être différents pour chaque type de vêtement.

#### Choix de la mensuration principale pivot

Selon le type de vêtement, la mensuration principale utilisée comme pivot pour les calculs de règle de trois peut varier :

| Type de vêtement | Mensuration pivot principale |
|------------------|------------------------------|
| Robe / Haut / Veste | Tour de poitrine |
| Pantalon / Jupe | Tour de hanches |
| Ensemble complet | Tour de poitrine + Tour de hanches (calcul séparé pour le haut et le bas) |

Cette configuration est stockée dans la définition du type de vêtement dans le référentiel.

#### Mensurations secondaires calculées (exemples)

| Mensuration secondaire | Mensuration pivot | Exemple de règle de trois |
|------------------------|-------------------|-----------------------------|
| Tour sous-poitrine | Tour de poitrine | (ref_sous_poitrine × poitrine_client) / poitrine_ref |
| Tour de bras | Tour de poitrine | (ref_tour_bras × poitrine_client) / poitrine_ref |
| Longueur de manche | Hauteur totale | (ref_longueur_manche × hauteur_client) / hauteur_ref |
| Hauteur de bassin | Tour de hanches | (ref_hauteur_bassin × hanches_client) / hanches_ref |
| Entrejambe | Hauteur totale + Tour de hanches | Calcul combiné |

---

### 2.5 Traçabilité des sources de calcul

Chaque valeur de mensuration dans la fiche d'un client est accompagnée de son historique de calcul complet, consultable par le couturier :

| Source | Signification |
|--------|---------------|
| `ESTIMEE` | Valeur produite directement par le modèle IA depuis les images |
| `MANUELLE` | Valeur saisie ou corrigée manuellement par le couturier |
| `DEDUITE_PROPORTION` | Valeur calculée via les règles de proportion (coefficient + offset) |
| `DEDUITE_REGLE_DE_TROIS` | Valeur calculée par règle de trois à partir des données de référence |
| `DEDUITE_COMBINEE` | Valeur calculée par règle de trois puis affinée par règle de proportion |

Cette traçabilité permet au couturier de comprendre l'origine de chaque chiffre et d'identifier rapidement les valeurs à vérifier en priorité.

---

## 3. Spécifications Détaillées : Gestion de la Bibliothèque Globale

### 3.1 Cycle de vie d'un modèle dans la bibliothèque

```
CREATION (couturier) ──► SOUMIS (en attente de validation) ──► PUBLIE (bibliothèque globale)
                                  │
                                  ▼
                              REJETE (retour au couturier avec commentaire)
```

Un modèle publié dans la bibliothèque dispose de :
- Son nom, type de vêtement, description et aperçu du patron
- Ses mensurations modèle de référence complètes (issues de l'Étape 1)
- Son patron avec toutes les annotations positionnées (issu de l'Étape 2)
- La mensuration principale pivot définie pour son type de vêtement

### 3.2 Versionnement des modèles de la bibliothèque

- Un modèle publié peut être mis à jour par l'administrateur (nouvelle version incrémentale)
- Les patrons adaptés déjà générés par les couturiers à partir d'une version antérieure sont conservés et ne sont pas affectés par la mise à jour
- Le couturier est informé qu'une nouvelle version d'un modèle qu'il utilise est disponible

---

## 4. Spécifications Détaillées : Calcul d'Adaptation du Patron

### 4.1 Mécanisme complet d'adaptation

Lorsqu'un couturier génère un patron adapté pour un client à partir d'un modèle de la bibliothèque, le système procède comme suit :

**Pour chaque mensuration annotée sur le patron :**

```
1. Récupérer la valeur de référence du modèle : valeur_ref
2. Récupérer la valeur réelle du client : valeur_client
3. Calculer l'écart : écart = valeur_client - valeur_ref
4. Appliquer l'écart à la dimension de la pièce concernée :
   dimension_adaptée = dimension_modèle + écart
5. Mettre à jour la valeur affichée dans l'annotation : valeur_client
```

**Cas des annotations bidimensionnelles (ex : tour = périmètre) :**

Pour une mesure circulaire (tour de poitrine), l'adaptation s'applique de façon proportionnelle sur la largeur de la pièce (la moitié ou le quart du tour selon le découpage du patron) :
```
écart_unitaire = écart_total / nombre_de_pieces_concernées
dimension_pièce_adaptée = dimension_pièce_modèle + écart_unitaire
```

### 4.2 Indicateur de faisabilité

Avant de générer le patron adapté, le système évalue si les écarts calculés sont dans des marges techniquement réalisables pour ce type de vêtement. Si un écart dépasse un seuil critique (ex : +/- 20 cm sur une mesure clé), le système affiche un avertissement au couturier lui signalant que l'adaptation peut nécessiter des ajustements manuels supplémentaires.

---

## 5. Architecture Applicative Cible (Détaillée)

### 5.1 Choix technologiques et justification

| Composant | Technologie retenue | Justification |
|-----------|---------------------|---------------|
| Application mobile | **Flutter** (Dart) | Intégration native avec Google ML Kit (Pose Detection, Body Segmentation) et TensorFlow Lite pour l'inférence on-device. Contrôle bas niveau du flux caméra (frame par frame) via le plugin `camera`. Rendu identique iOS/Android pour les instructions visuelles de guidage. |
| API Backend | **Laravel** (PHP 8+) | Maîtrise du framework par l'équipe. Système de queues asynchrones (Horizon) natif pour le traitement des images en arrière-plan. Laravel Sanctum pour l'authentification mobile. Laravel Storage compatible S3 pour les fichiers patrons et images. |
| Traitement asynchrone | **Laravel Queues + Horizon** | Les photos ne bloquent pas l'API. La requête mobile soumet les photos et reçoit un identifiant de traitement ; un job de queue appelle le microservice IA en arrière-plan ; l'app récupère les résultats via polling ou notification push. |
| Module IA mesures | **Microservice Python** (FastAPI + TensorFlow / PyTorch) | Les modèles de vision (TensorFlow, PyTorch) sont natifs en Python. Le microservice expose une API REST interne appelée par Laravel. Ce découplage permet de faire évoluer le modèle IA indépendamment du backend. |
| Base de données | **PostgreSQL** | Stockage relationnel robuste, compatible avec l'ORM Eloquent de Laravel. |
| Stockage fichiers | **Stockage S3-compatible** (via Laravel Storage) | Gestion des images de capture, fichiers patrons et exports PDF/SVG. |

### 5.2 Schéma d'architecture détaillé

```
┌─────────────────────────────────────────────────────────┐
│                  Application Mobile Flutter              │
│  - Guidage photo (plugin camera)                         │
│  - Google ML Kit (pré-traitement on-device optionnel)    │
│  - TensorFlow Lite (inférence légère on-device si dispo) │
└───────────────────────────┬─────────────────────────────┘
                            │ HTTPS (Laravel Sanctum)
                            ▼
┌─────────────────────────────────────────────────────────┐
│                    API Laravel (PHP 8+)                  │
│  - Authentification (Sanctum)                            │
│  - Validation des requêtes (Form Requests)               │
│  - Ressources API (API Resources)                        │
│  - Gestion fichiers (Laravel Storage → S3)               │
│  - Calculs métier (règle de trois, adaptation patron)    │
└──────────────┬──────────────────────┬───────────────────┘
               │                      │
               ▼                      ▼
┌──────────────────────┐   ┌──────────────────────────────┐
│  Laravel Horizon     │   │        PostgreSQL             │
│  (Queue Worker)      │   │  (données métier, mesures,   │
│                      │   │   patrons, bibliothèque)      │
│  Job: TraiterPhotos  │   └──────────────────────────────┘
└──────────┬───────────┘
           │ HTTP interne
           ▼
┌─────────────────────────────────────────────────────────┐
│           Microservice Python (FastAPI)                  │
│  - Calibration d'échelle (pixels → cm)                   │
│  - Détection points anatomiques (TensorFlow / PyTorch)   │
│  - Estimation des mensurations principales               │
│  - Score de confiance par mensuration                    │
│  - Retour JSON structuré vers Laravel                    │
└─────────────────────────────────────────────────────────┘
```

### 5.3 Communication mobile ↔ API pour le traitement IA

Le traitement d'image pouvant durer plusieurs secondes, le flux de communication est asynchrone :

```
1. Flutter  →  POST /api/mesures/soumettre  →  Laravel
              (upload des 4 photos + métadonnées)
              ← Réponse immédiate : { "traitement_id": "abc123" }

2. Laravel  →  Job mis en queue (Horizon)

3. Job      →  POST /interne/analyser  →  Microservice Python
              ← Résultats JSON (mensurations + scores de confiance)

4. Laravel     Stocke les résultats en base

5. Flutter  →  GET /api/mesures/abc123/resultats  →  Laravel
              ← { "statut": "TERMINE", "mensurations": [...] }
              (polling toutes les 2 secondes jusqu'à TERMINE)
```

---

## 6. Exigences Techniques Complémentaires

### 6.1 Module IA Mesures — Contraintes d'entraînement

- Le modèle doit être entraîné sur un dataset d'images respectant strictement le protocole de guidage photo défini par la plateforme (angles, distance, posture, élément de calibration)
- Le dataset d'entraînement doit couvrir une diversité représentative de morphologies, tailles, genres et types de vêtements portés lors de la capture
- Les performances du modèle doivent être évaluées sur un ensemble de validation avec mesures terrain de référence (prises au mètre-ruban)
- L'objectif de performance cible est un écart moyen inférieur à 2 cm sur les mensurations principales

### 6.2 Calibration — Robustesse

- Le système doit détecter automatiquement l'élément de référence dans l'image et refuser de procéder si la détection échoue ou si la qualité de la calibration est insuffisante
- Un message explicite guide le couturier pour reprendre la capture dans de meilleures conditions
- Le coefficient d'échelle calculé pour chaque image est enregistré dans les métadonnées de la fiche de mesures pour permettre une rétro-analyse en cas de doute

### 6.3 Règles de proportion — Gouvernance

- Toute règle de proportion doit être documentée avec sa source métier (reference couture, expérience terrain, norme)
- Les règles sont versionnées : une modification de règle crée une nouvelle version sans effacer l'ancienne
- Les fiches de mesures enregistrent la version des règles utilisées pour leurs calculs dérivés

---

## 7. Glossaire Technique

| Terme | Définition |
|-------|------------|
| **Mensuration principale** | Mensuration directement estimable depuis une image (ex : tour de poitrine) |
| **Mensuration secondaire** | Mensuration calculée à partir d'une mensuration principale via règle de proportion ou règle de trois |
| **Mensuration de référence** | Valeur d'une mensuration pour la personne modèle ayant servi à créer le patron original |
| **Mensuration client** | Valeur d'une mensuration pour un client spécifique du couturier |
| **Coefficient d'échelle** | Rapport pixels/cm calculé depuis l'élément de référence dans l'image |
| **Règle de trois** | Calcul de proportionnalité permettant de déduire une mensuration inconnue à partir de proportions connues sur une personne de référence |
| **Règle de proportion** | Formule métier (coefficient + offset) appliquée après la règle de trois pour affiner le calcul |
| **Patron adapté** | Patron dont les dimensions des pièces ont été recalculées pour correspondre aux mensurations réelles d'un client |
| **Annotation** | Cotation positionnée sur une pièce du patron indiquant à quelle mensuration correspond une dimension |
| **Bibliothèque globale** | Base de modèles de vêtements partagée entre tous les couturiers de la plateforme |
| **FicheClient** | Classe-association liant un couturier à un client (notes privées, archivage) — remplace `CarnetClient` |
| **Compte client** | Compte d'utilisateur final, créé par auto-inscription (UC-19) ou par un couturier (UC-20) |
| **Identifiant de connexion** | Email **OU** téléphone — au moins un des deux est obligatoire (contexte Bénin) |
| **Note couturier** | Note de 1 à 5 laissée par un client après une commande au statut TERMINE (UC-24) |
