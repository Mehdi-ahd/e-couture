# Schéma de la Base de Données
## Plateforme Intelligente de Couture sur Mesure

**Version** : 2.1 (alignée sur `export_couture.xlsx` — Class Diagram1, **adaptée MySQL 8**)
**Date** : Avril 2026
**SGBD** : **MySQL 8.0+** (collation `utf8mb4_unicode_ci`)
**Source de vérité** : feuille **`Class Diagram1`** de `export_couture.xlsx`

> ⚠️ **Cette version remplace la v1.0** et reflète le diagramme de classes révisé exporté depuis l'outil de modélisation. Différences clés vs v1.0 documentées en §13.

---

## 1. Conventions générales

### 1.1 Identifiants — règle universelle

Chaque table métier respecte la double-clé suivante :

| Colonne | Type | Rôle | Exposé API ? |
|---|---|---|---|
| `id` | `BIGINT UNSIGNED AUTO_INCREMENT` PK | Clé primaire interne, performante pour les jointures et indexations | ❌ Jamais |
| `external_id` | `CHAR(36) NOT NULL UNIQUE` (UUID v4 généré côté app via trait `HasExternalId`) | Identifiant public, opaque, non séquentiel | ✅ Toujours (URLs, JSON, Filament) |

> **Pourquoi cette double-clé ?**
> - `id` BIGINT garde les jointures rapides et les FK compactes (8 octets vs 16).
> - `external_id` UUID empêche l'énumération des ressources (`/api/commandes/47` → `/api/commandes/3f9a-…`), pratique pour la sécurité et pour générer un ID côté mobile **avant synchro** (offline-first du UC-12).
> - Les FK utilisent **toujours `id`** (jamais `external_id`).
> - Le diagramme UML modélise les `id` en `string` ou `UUID` — notre implémentation respecte cette intention via `external_id` UUID, tout en gardant l'efficacité d'un `id` BIGINT interne (transparent pour le code métier qui n'utilise que `external_id`).

### 1.2 Conventions de nommage

| Élément | Convention | Exemple |
|---|---|---|
| Table | `snake_case`, **pluriel français** | `fiches_clients` |
| Colonne | `snake_case` | `date_naissance` |
| FK | `<table_singulier>_id` | `couturier_id` |
| Index | `idx_<table>_<colonnes>` | `idx_users_email` |
| Unique | `uniq_<table>_<colonnes>` | `uniq_fiches_clients_couple` |
| Enum MySQL | `<table>_<colonne>_enum` | `commandes_vetements_statut_enum` |

### 1.3 Colonnes systématiques

```sql
id           BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
external_id  CHAR(36) NOT NULL UNIQUE,                       -- UUID v4 généré par Laravel
created_at   TIMESTAMP NULL DEFAULT NULL,                    -- géré par Eloquent
updated_at   TIMESTAMP NULL DEFAULT NULL                     -- géré par Eloquent
```

`deleted_at TIMESTAMP NULL DEFAULT NULL` est ajouté uniquement aux tables soumises à archivage : `users`, `modeles_vetements`, `commandes_vetements`, `fiches_clients`.

### 1.4 Configuration MySQL requise

```sql
-- Création de la base
CREATE DATABASE e_couture
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;
```

**Pas d'extensions à installer** — MySQL 8 supporte nativement :
- `JSON` (depuis 5.7) → JSON-équivalent
- `CHECK` constraints (depuis 8.0.16) → règles métier
- Collation `utf8mb4_unicode_ci` → comparaisons insensibles à la casse (équivalent CITEXT)
- UUID via fonction `UUID()` ou côté Laravel via `Str::uuid()` (recommandé)

**Configuration `config/database.php`** :
```php
'mysql' => [
    'driver' => 'mysql',
    'charset' => 'utf8mb4',
    'collation' => 'utf8mb4_unicode_ci',
    'engine' => 'InnoDB',
    'strict' => true,
],
```

---

## 2. Vue d'ensemble (5 zones + technique)

```
┌─ Zone 1 — Référentiels ─────────────┐  ┌─ Zone 2 — Utilisateurs ────────────┐
│ types_mensurations                  │  │ users (STI: COUTURIER/ADMIN/CLIENT)│
│ types_vetements (mensuration_pivot) │  │  └─ KYC intégré                    │
│ regles_proportions (source/cible)   │  │ + tables Spatie Permission         │
└─────────────────────────────────────┘  │ + sessions / tokens / media KYC    │
                                         └────────────────────────────────────┘
┌─ Zone 3 — Fiches & Mesures ─────────┐  ┌─ Zone 4 — Modèles & Patrons ───────┐
│ fiches_clients (Couturier×Client)   │  │ modeles_vetements                  │
│ fiches_mesures (rattachée Client)   │  │ mensurations_modeles               │
│ lignes_mensurations                 │  │ patrons → pieces_patrons           │
│                                     │  │            └─ annotations_patrons  │
└─────────────────────────────────────┘  └────────────────────────────────────┘
┌─ Zone 5 — Commandes & Notation ─────┐  ┌─ Zone Technique ───────────────────┐
│ commandes_vetements                 │  │ media (Spatie — KYC + patrons)     │
│ paiements (1:N par commande)        │  │ activity_log, jobs, sessions       │
│ notes_couturiers (4 sous-notes)     │  │ + Sanctum, Telescope, Horizon      │
└─────────────────────────────────────┘  └────────────────────────────────────┘
```

---

## 3. Zone 1 — Référentiels

### 3.1 `types_mensurations`
*Source diagramme : classe `TypeMensuration` (id 294)*

| Colonne | Type | Contraintes | Description |
|---|---|---|---|
| `id` | BIGSERIAL | PK | |
| `external_id` | UUID | UNIQUE NOT NULL | |
| `code` | VARCHAR(50) | UNIQUE NOT NULL | Code technique (`TOUR_POITRINE`) |
| `nom` | VARCHAR(120) | NOT NULL | |
| `unite` | VARCHAR(10) | NOT NULL DEFAULT 'cm' | `cm`, `mm`, `inch` |
| `categorie` | ENUM | NOT NULL | `PRINCIPALE`, `SECONDAIRE` |
| `description` | TEXT | NULL | |
| `est_actif` | BOOLEAN | NOT NULL DEFAULT TRUE | |
| `created_at` / `updated_at` | TIMESTAMP | | |

**Index** : `idx_types_mensurations_actif (est_actif)`.

---

### 3.2 `types_vetements`
*Source diagramme : classe `TypeVetement` (id 303) + association `pivot` (id 362) avec `TypeMensuration`*

| Colonne | Type | Contraintes | Description |
|---|---|---|---|
| `id` | BIGSERIAL | PK | |
| `external_id` | UUID | UNIQUE NOT NULL | |
| `code` | VARCHAR(50) | UNIQUE NOT NULL | `ROBE_LONGUE`, `CHEMISE_HOMME` |
| `nom` | VARCHAR(120) | NOT NULL | |
| `categorie` | ENUM | NOT NULL | `HAUT`, `BAS`, `ROBE`, `ENSEMBLE`, `ACCESSOIRE` |
| `mensuration_pivot_id` | BIGINT | FK → `types_mensurations(id)` NOT NULL | Implémente association `pivot` |
| `description` | TEXT | NULL | |
| `est_actif` | BOOLEAN | NOT NULL DEFAULT TRUE | |
| `created_at` / `updated_at` | TIMESTAMP | | |

**Index** : `idx_types_vetements_categorie (categorie)`.

---

### 3.3 `regles_proportions`
*Source diagramme : classe `RegleProportion` (id 284) + 2 associations vers `TypeMensuration` (`source` id 352 et la 2e id 354 — implicite "cible")*

| Colonne | Type | Contraintes |
|---|---|---|
| `id` | BIGSERIAL | PK |
| `external_id` | UUID | UNIQUE NOT NULL |
| `nom` | VARCHAR(150) | NOT NULL |
| `mensuration_source_id` | BIGINT | FK → `types_mensurations(id)` NOT NULL — implémente association `source` |
| `mensuration_cible_id` | BIGINT | FK → `types_mensurations(id)` NOT NULL — implémente la 2e association (cible) |
| `coefficient` | DECIMAL(8,4) | NOT NULL |
| `offset` | DECIMAL(8,2) | NOT NULL DEFAULT 0 |
| `source_metier` | VARCHAR(100) | NULL — origine du barème |
| `version` | SMALLINT | NOT NULL DEFAULT 1 |
| `est_active` | BOOLEAN | NOT NULL DEFAULT TRUE |
| `created_at` / `updated_at` | TIMESTAMP | |

**Contraintes** :
- `CHECK (mensuration_source_id <> mensuration_cible_id)` — pas de boucle directe.
- `UNIQUE (mensuration_source_id, mensuration_cible_id, version)`.

---

## 4. Zone 2 — Utilisateurs (héritage STI + KYC intégré)

> **Stratégie d'héritage** : Single Table Inheritance avec colonne discriminante `type`. Toutes les sous-classes (`Couturier`, `Administrateur`, `Client`) partagent une seule table `users` — colonnes spécifiques nullable selon le `type`. Les rôles métier sont gérés par **Spatie Permission**.
>
> **Avantage clé** : Sanctum, Filament Shield, Breeze et Socialite fonctionnent nativement sur une table `users` unique.

### 4.1 `users`
*Source diagramme : classe abstraite `Utilisateur` (id 313) + spécialisations `Couturier` (231), `Administrateur` (199), `Client` (212). Le KYC (typePiece, fichierPieceRecto/Verso, selfie, statut, motifRejet, dateSoumission, dateValidation) est porté par la classe abstraite Utilisateur — donc présent sur la table unique.*

| Colonne | Type | Contraintes | Description |
|---|---|---|---|
| `id` | BIGSERIAL | PK | |
| `external_id` | UUID | UNIQUE NOT NULL | |
| `type` | ENUM | NOT NULL | `COUTURIER`, `ADMINISTRATEUR`, `CLIENT` (discriminant STI) |
| `nom` | VARCHAR(80) | NOT NULL | |
| `prenom` | VARCHAR(80) | NOT NULL | |
| `email` | VARCHAR(190) (utf8mb4_unicode_ci) | UNIQUE NULL | Insensible à la casse — voir contrainte ci-dessous |
| `email_verified_at` | TIMESTAMP | NULL | Pour Laravel Breeze |
| `telephone` | VARCHAR(20) | UNIQUE NULL | Format E.164 (`+229XXXXXXXX`) — voir contrainte ci-dessous |
| `telephone_verified_at` | TIMESTAMP | NULL | OTP SMS (Vonage) |
| `password` | VARCHAR(255) | NOT NULL | Hash bcrypt/argon2 |
| `must_change_password` | BOOLEAN | NOT NULL DEFAULT FALSE | UC-20 (RG-13) |
| `est_actif` | BOOLEAN | NOT NULL DEFAULT TRUE | |
| `last_login_at` | TIMESTAMP | NULL | |
| `remember_token` | VARCHAR(100) | NULL | Laravel |
| **— KYC (porté par Utilisateur abstract dans le diagramme) —** | | | |
| `kyc_type_piece` | ENUM | NULL | `CNI`, `PASSEPORT`, `PERMIS_CONDUIRE` |
| `kyc_statut` | ENUM | NOT NULL DEFAULT 'NON_SOUMIS' | `NON_SOUMIS`, `EN_ATTENTE`, `VALIDE`, `REJETE` |
| `kyc_motif_rejet` | TEXT | NULL | Renseigné si `kyc_statut = REJETE` |
| `kyc_date_soumission` | TIMESTAMP | NULL | |
| `kyc_date_validation` | TIMESTAMP | NULL | |
| **— Spécifique COUTURIER (NOT NULL si type='COUTURIER') —** | | | |
| `specialite` | VARCHAR(150) | NULL | |
| `adresse_atelier` | TEXT | NULL | |
| `bio` | TEXT | NULL | (optionnel même pour COUTURIER) |
| `note_moyenne_globale` | DECIMAL(3,2) | NULL | Cache des 4 sous-notes (calculée) |
| **— Spécifique CLIENT —** | | | |
| `date_naissance` | DATE | NULL | |
| `notes_personnelles` | TEXT | NULL | Notes propres au client (renommé pour éviter confusion avec FicheClient) |
| **— Communs —** | | | |
| `created_at` / `updated_at` | TIMESTAMP | | |
| `deleted_at` | TIMESTAMP | NULL | Soft delete |

**Fichiers KYC** : `kyc_recto`, `kyc_verso`, `kyc_selfie` ne sont **pas** des colonnes — ils sont stockés dans la table `media` (Spatie MediaLibrary) avec collection_name correspondant. Voir §8.3.

**Contraintes CHECK** :
```sql
-- Au moins un canal de contact (email pour ADMIN, telephone pour COUTURIER/CLIENT)
CHECK (email IS NOT NULL OR telephone IS NOT NULL)

-- Couturier : telephone, specialite, adresse_atelier OBLIGATOIRES
CHECK (
  type <> 'COUTURIER'
  OR (telephone IS NOT NULL
      AND specialite IS NOT NULL
      AND adresse_atelier IS NOT NULL)
)

-- Client : telephone OBLIGATOIRE
CHECK (type <> 'CLIENT' OR telephone IS NOT NULL)

-- Cohérence STI : pas d'attributs Couturier sur un Client
CHECK (
  type <> 'CLIENT'
  OR (specialite IS NULL AND adresse_atelier IS NULL AND bio IS NULL)
)

-- Cohérence KYC
CHECK (kyc_statut <> 'REJETE' OR kyc_motif_rejet IS NOT NULL)
CHECK (kyc_statut = 'NON_SOUMIS' OR kyc_date_soumission IS NOT NULL)
```

**Index** :
- `idx_users_type (type)` — filtrage rapide par sous-classe.
- `idx_users_kyc_statut (kyc_statut)` — file d'attente admin.
- `idx_users_specialite (specialite)` — recherche catalogue (UC-22).
- `idx_users_note (note_moyenne_globale)`.

---

### 4.2 Tables Spatie Permission *(générées automatiquement)*

`roles`, `permissions`, `model_has_roles`, `model_has_permissions`, `role_has_permissions`.

> Rôles seedés : `super_admin`, `administrateur`, `couturier`, `client`.

### 4.3 Tables d'authentification *(générées par Breeze + Sanctum)*

| Table | Rôle |
|---|---|
| `password_reset_tokens` | Réinitialisation mot de passe |
| `personal_access_tokens` | Tokens API mobile (Sanctum) |
| `sessions` | Sessions web Filament |

> 🔁 **Plus de table `verifications_identite`** : le KYC est intégré à `users`. Les codes OTP éphémères (vérification téléphone/email à l'inscription) restent gérés par cache Redis (TTL 5 min) — pas de persistance SQL.

---

## 5. Zone 3 — Fiches clients & Mesures

### 5.1 `fiches_clients`
*Source diagramme : classe-association `FicheClient` (id 208) liée à l'association `commander` (id 371) entre `Couturier` (231) et `Client` (212). Une FicheClient agrège 1..* `CommandeVetement` (asso `liée` id 367).*

| Colonne | Type | Contraintes |
|---|---|---|
| `id` | BIGSERIAL | PK |
| `external_id` | UUID | UNIQUE NOT NULL |
| `couturier_id` | BIGINT | FK → `users(id)` NOT NULL |
| `client_id` | BIGINT | FK → `users(id)` NOT NULL |
| `date_creation` | TIMESTAMP | NOT NULL DEFAULT NOW() |
| `est_actif` | BOOLEAN | NOT NULL DEFAULT TRUE |
| `created_at` / `updated_at` | TIMESTAMP | |
| `deleted_at` | TIMESTAMP | NULL |

**Contraintes** :
- `UNIQUE (couturier_id, client_id)` (uniq_fiches_clients_couple).
- `CHECK (couturier_id <> client_id)`.

**Index** : `idx_fiches_couturier (couturier_id, est_actif)`.

> **Note v2** : la classe `FicheClient` ne contient plus que `dateCreation` + `estActif`. Les **notes du couturier sur le client** ont disparu de cette classe — si elles doivent être conservées, elles vont sur `users.notes_personnelles` (notes du client lui-même selon le diagramme).

---

### 5.2 `fiches_mesures`
*Source diagramme : classe `FicheMesure` (id 237) — **directement rattachée à `Client`** via association `a` (id 333) : Client 1 ↔ FicheMesure 0..*.*
> 🔁 **Changement v2** : la fiche-mesure n'est plus rattachée à `FicheClient` mais directement au `Client`. Cohérent : la mesure du corps appartient au client, pas à un suivi spécifique par un couturier.

| Colonne | Type | Contraintes |
|---|---|---|
| `id` | BIGSERIAL | PK |
| `external_id` | UUID | UNIQUE NOT NULL |
| `client_id` | BIGINT | FK → `users(id)` ON DELETE CASCADE NOT NULL |
| `date_prise` | DATE | NOT NULL |
| `methode` | ENUM | NOT NULL — `PHOTO`, `MANUELLE` |
| `statut_traitement` | ENUM | NOT NULL DEFAULT 'EN_ATTENTE' — `EN_ATTENTE`, `EN_COURS`, `TERMINE`, `ECHEC` |
| `traitement_id` | UUID | NULL — UUID idempotence du job ML Kit |
| `version_regles` | SMALLINT | NOT NULL — version de `regles_proportions` utilisée |
| `notes` | TEXT | NULL |
| `statut` | ENUM | NOT NULL DEFAULT 'BROUILLON' — `BROUILLON`, `VALIDEE` |
| `validee_at` | TIMESTAMP | NULL |
| `created_at` / `updated_at` | TIMESTAMP | |

**Contraintes** :
- `CHECK (statut <> 'VALIDEE' OR methode = 'MANUELLE' OR statut_traitement = 'TERMINE')` — RG-11.

**Index** :
- `idx_fiches_mesures_client_statut (client_id, statut)`.
- `idx_fiches_mesures_traitement (statut_traitement)` — file d'attente jobs.

---

### 5.3 `lignes_mensurations`
*Source diagramme : classe `LigneMensuration` (id 248) + association `concerne` (id 350) avec `TypeMensuration`.*

| Colonne | Type | Contraintes |
|---|---|---|
| `id` | BIGSERIAL | PK |
| `external_id` | UUID | UNIQUE NOT NULL |
| `fiche_mesure_id` | BIGINT | FK → `fiches_mesures(id)` ON DELETE CASCADE NOT NULL |
| `type_mensuration_id` | BIGINT | FK → `types_mensurations(id)` NOT NULL |
| `valeur` | DECIMAL(7,2) | NOT NULL |
| `source` | ENUM | NOT NULL — `ESTIMEE`, `MANUELLE`, `DEDUITE_PROPORTION`, `DEDUITE_REGLE_DE_TROIS`, `DEDUITE_COMBINEE` |
| `confiance` | DECIMAL(3,2) | NULL — score IA 0–1 |
| `commentaire` | TEXT | NULL |
| `created_at` / `updated_at` | TIMESTAMP | |

**Contraintes** :
- `UNIQUE (fiche_mesure_id, type_mensuration_id)`.
- `CHECK (valeur > 0)`.
- `CHECK (confiance IS NULL OR (confiance >= 0 AND confiance <= 1))`.

---

## 6. Zone 4 — Modèles & Patrons

### 6.1 `modeles_vetements`
*Source diagramme : classe `ModelVetement` (id 260) — **simplifiée v2** (plus d'`etapeActuelle`/`commentaireRejet`).*
*Associations : `crée` (Couturier 0..1 → Modèle 0..*) ; `classifie` (TypeVetement 1 → Modèle 0..*) ; `inclut` (Modèle 1 → Patron 0..1).*

| Colonne | Type | Contraintes |
|---|---|---|
| `id` | BIGSERIAL | PK |
| `external_id` | UUID | UNIQUE NOT NULL |
| `type_vetement_id` | BIGINT | FK → `types_vetements(id)` NOT NULL — asso `classifie` |
| `createur_id` | BIGINT | FK → `users(id)` NULL — asso `crée` (0..1 dans le diagramme) |
| `nom` | VARCHAR(150) | NOT NULL |
| `description` | TEXT | NULL |
| `portee` | ENUM | NOT NULL — `PRIVE`, `GLOBAL` |
| `statut` | ENUM | NOT NULL DEFAULT 'BROUILLON' — `BROUILLON`, `ACTIF`, `SOUMIS`, `REJETE`, `ARCHIVE` |
| `created_at` / `updated_at` | TIMESTAMP | |
| `deleted_at` | TIMESTAMP | NULL |

**Contraintes** :
- `CHECK (portee <> 'GLOBAL' OR statut IN ('ACTIF','SOUMIS','REJETE','ARCHIVE'))` — un modèle GLOBAL n'est jamais en BROUILLON.

**Index** :
- `idx_modeles_portee_statut (portee, statut)`.
- `idx_modeles_createur (createur_id)`.

> **Note v2** : `etapeActuelle` (suivi du wizard 1=mensurations, 2=patron) et `commentaireRejet` ne sont plus dans le diagramme. Si besoin métier → réintroduire ; sinon le `statut = SOUMIS/REJETE` suffit, le commentaire de rejet pouvant être tracé via `activity_log` (Spatie).

---

### 6.2 `mensurations_modeles`
*Source diagramme : classe `MensurationModele` (id 255) + association `définit` (Modèle 1 → MensurationModèle 1..*) + association `concerne` (TypeMensuration 1 → MensurationModèle 0..*).*

| Colonne | Type | Contraintes |
|---|---|---|
| `id` | BIGSERIAL | PK |
| `external_id` | UUID | UNIQUE NOT NULL |
| `modele_vetement_id` | BIGINT | FK → `modeles_vetements(id)` ON DELETE CASCADE NOT NULL — asso `définit` |
| `type_mensuration_id` | BIGINT | FK → `types_mensurations(id)` NOT NULL — asso `concerne` |
| `valeur` | DECIMAL(7,2) | NOT NULL |
| `notes` | TEXT | NULL |
| `created_at` / `updated_at` | TIMESTAMP | |

**Unique** : `(modele_vetement_id, type_mensuration_id)`.

---

### 6.3 `patrons`
*Source diagramme : classe `Patron` (id 269) + association `inclut` (Modèle 1 → Patron 0..1).*
*Champs v2 : `methode`, `version` (int), `fichierUrl`, `donneesDessin`, `statut`, `createdAt`.*

| Colonne | Type | Contraintes |
|---|---|---|
| `id` | BIGSERIAL | PK |
| `external_id` | UUID | UNIQUE NOT NULL |
| `modele_vetement_id` | BIGINT | FK → `modeles_vetements(id)` ON DELETE CASCADE UNIQUE NOT NULL |
| `methode` | ENUM | NOT NULL — `UPLOAD`, `CREATION`, `IA` |
| `version` | SMALLINT | NOT NULL DEFAULT 1 |
| `fichier_url` | VARCHAR(500) | NULL — chemin S3 (méthode UPLOAD) |
| `donnees_dessin` | JSON | NULL — données vectorielles (méthode CREATION/IA) |
| `statut` | ENUM | NOT NULL DEFAULT 'BROUILLON' — `BROUILLON`, `VALIDE` |
| `created_at` / `updated_at` | TIMESTAMP | |

**Contraintes** :
- `UNIQUE (modele_vetement_id)` — un seul patron par modèle.
- `CHECK (fichier_url IS NOT NULL OR donnees_dessin IS NOT NULL)` — au moins une source.

> Le diagramme typait `donneesDessin` en `string` — on choisit JSON pour stocker proprement les coordonnées vectorielles (SVG path, courbes Bézier) et permettre des requêtes sur les données dessin.

---

### 6.4 `pieces_patrons`
*Source diagramme : classe `PiecePatron` (id 278) + association `comporte` (Patron 1 → PiecePatron 1..*).*

| Colonne | Type | Contraintes |
|---|---|---|
| `id` | BIGSERIAL | PK |
| `external_id` | UUID | UNIQUE NOT NULL |
| `patron_id` | BIGINT | FK → `patrons(id)` ON DELETE CASCADE NOT NULL |
| `nom` | VARCHAR(100) | NOT NULL — `Devant`, `Dos`, `Manche`… |
| `ordre` | SMALLINT | NOT NULL DEFAULT 1 |
| `donnees_geometriques` | JSON | NOT NULL — coordonnées des contours |
| `created_at` / `updated_at` | TIMESTAMP | |

**Index** : `idx_pieces_patron_ordre (patron_id, ordre)`.

---

### 6.5 `annotations_patrons`
*Source diagramme : classe `AnnotationPatron` (id 201) + asso `porte` (PiecePatron 1 → Annotation 0..*) + asso `positionne` (TypeMensuration 1 → Annotation 0..*).*
*Champs v2 : `id`, `label`, `positionDepart`, `positionFin`, `orientation` (tous string).*

| Colonne | Type | Contraintes |
|---|---|---|
| `id` | BIGSERIAL | PK |
| `external_id` | UUID | UNIQUE NOT NULL |
| `piece_patron_id` | BIGINT | FK → `pieces_patrons(id)` ON DELETE CASCADE NOT NULL |
| `type_mensuration_id` | BIGINT | FK → `types_mensurations(id)` NOT NULL |
| `label` | VARCHAR(100) | NOT NULL — libellé affiché sur la pièce |
| `position_depart` | VARCHAR(50) | NOT NULL — coordonnées `"x,y"` du point de départ |
| `position_fin` | VARCHAR(50) | NOT NULL — coordonnées `"x,y"` du point de fin |
| `orientation` | VARCHAR(20) | NOT NULL — `HORIZONTAL`, `VERTICAL`, `DIAGONAL` |
| `created_at` / `updated_at` | TIMESTAMP | |

> Le diagramme type ces champs en `string` simple — on garde VARCHAR pour respecter, mais on pourrait aussi décomposer `position_depart`/`position_fin` en couples NUMERIC `(x, y)` si on veut indexer.

---

## 7. Zone 5 — Commandes, Paiements & Notation

### 7.1 `commandes_vetements`
*Source diagramme : classe `CommandeVetement` (id 223). Associations : `porte sur` (Modèle 1 → Commande 0..*) ; `utilise` (FicheMesure 1 → Commande 0..*) ; `liée` (Commande 1..* → FicheClient 1).*

> 🔁 **Changement v2** : `montant` est **supprimé** de la commande (porté désormais par `Paiement`).

| Colonne | Type | Contraintes |
|---|---|---|
| `id` | BIGSERIAL | PK |
| `external_id` | UUID | UNIQUE NOT NULL |
| `numero` | VARCHAR(20) | UNIQUE NOT NULL — séquence lisible (`CMD-2026-00042`) |
| `fiche_client_id` | BIGINT | FK → `fiches_clients(id)` NOT NULL — asso `liée` |
| `modele_vetement_id` | BIGINT | FK → `modeles_vetements(id)` NOT NULL — asso `porte sur` |
| `fiche_mesure_id` | BIGINT | FK → `fiches_mesures(id)` NOT NULL — asso `utilise` |
| `statut` | ENUM | NOT NULL DEFAULT 'EN_ATTENTE' — `EN_ATTENTE`, `EN_CONFECTION`, `TERMINE`, `ANNULE` |
| `notes` | TEXT | NULL — instructions spéciales |
| `date_commande` | TIMESTAMP | NOT NULL DEFAULT NOW() |
| `date_livraison_souhaitee` | DATE | NULL |
| `date_terminee_at` | TIMESTAMP | NULL |
| `created_at` / `updated_at` | TIMESTAMP | |
| `deleted_at` | TIMESTAMP | NULL |

> **Note** : Les références `couturier_id` et `client_id` ne sont plus directes — elles sont déduites via `fiche_client_id → (couturier_id, client_id)`. Une vue ou un accesseur Eloquent `commande.couturier` / `commande.client` les exposera. Cela respecte fidèlement le diagramme v2.

**Contraintes** :
- `UNIQUE (numero)`.

**Index** :
- `idx_commandes_fiche_client (fiche_client_id, statut)`.
- `idx_commandes_modele (modele_vetement_id)`.
- `idx_commandes_date (date_commande DESC)`.

> **RG-03** (modèle utilisable) et **RG-04** (FicheMesure VALIDEE) restent vérifiées au niveau **applicatif** (Form Request + state machine `spatie/laravel-model-states`).

---

### 7.2 `paiements`
*Source diagramme : classe `Paiement` (id 188) + association (id 376) Commande 1 ↔ Paiement 0..*.*
*Champs v2 : `mode` (Enum), `montant`, `devise`, `statut`, `dateInitiation`, `dateConfirmation`, `referenceExterne`, `metadonneesAgregateur` (JSON).*

| Colonne | Type | Contraintes |
|---|---|---|
| `id` | BIGSERIAL | PK |
| `external_id` | UUID | UNIQUE NOT NULL |
| `commande_id` | BIGINT | FK → `commandes_vetements(id)` NOT NULL |
| `mode` | ENUM | NOT NULL — `KKIAPAY_MOMO`, `KKIAPAY_CARTE`, `FEDAPAY_MOMO`, `FEDAPAY_CARTE`, `ESPECES` |
| `montant` | DECIMAL(12,2) | NOT NULL |
| `devise` | CHAR(3) | NOT NULL DEFAULT 'XOF' |
| `statut` | ENUM | NOT NULL DEFAULT 'INITIE' — `INITIE`, `EN_ATTENTE`, `REUSSI`, `ECHEC`, `REMBOURSE` |
| `date_initiation` | TIMESTAMP | NOT NULL DEFAULT NOW() |
| `date_confirmation` | TIMESTAMP | NULL |
| `reference_externe` | VARCHAR(100) | UNIQUE NULL — id transaction renvoyé par l'agrégateur |
| `metadonnees_agregateur` | JSON | NULL — payload brut du webhook (debug, audit) |
| `created_at` / `updated_at` | TIMESTAMP | |

**Contraintes** :
- `CHECK (montant > 0)`.
- `CHECK (statut <> 'REUSSI' OR date_confirmation IS NOT NULL)`.
- `CHECK (statut <> 'REUSSI' OR reference_externe IS NOT NULL)` — un paiement réussi doit avoir l'id agrégateur.

**Index** :
- `idx_paiements_commande_statut (commande_id, statut)`.
- `idx_paiements_reference (reference_externe)`.

---

### 7.3 `notes_couturiers`
*Source diagramme : classe `NoteCouturier` (id 178) — **4 sous-notes v2** (noteService, noteConception, noteLivraison, noteDelai) + commentaire global.*
*Associations : (id 378) Couturier 1 ↔ Note 0..* ; (id 380) Note 0..* ↔ Client 1.*

| Colonne | Type | Contraintes |
|---|---|---|
| `id` | BIGSERIAL | PK |
| `external_id` | UUID | UNIQUE NOT NULL |
| `client_id` | BIGINT | FK → `users(id)` NOT NULL — auteur |
| `couturier_id` | BIGINT | FK → `users(id)` NOT NULL — cible |
| `commande_id` | BIGINT | FK → `commandes_vetements(id)` NULL — RG-12 (note rattachable à une commande terminée) |
| `note_service` | SMALLINT | NOT NULL — qualité du service global (1–5) |
| `note_conception` | SMALLINT | NOT NULL — qualité du modèle/design (1–5) |
| `note_livraison` | SMALLINT | NOT NULL — qualité de la livraison/finition (1–5) |
| `note_delai` | SMALLINT | NOT NULL — respect des délais (1–5) |
| `commentaire` | TEXT | NULL |
| `date_notation` | TIMESTAMP | NOT NULL DEFAULT NOW() |
| `est_visible` | BOOLEAN | NOT NULL DEFAULT TRUE |
| `created_at` / `updated_at` | TIMESTAMP | |

**Contraintes** :
- `CHECK (note_service BETWEEN 1 AND 5)`.
- `CHECK (note_conception BETWEEN 1 AND 5)`.
- `CHECK (note_livraison BETWEEN 1 AND 5)`.
- `CHECK (note_delai BETWEEN 1 AND 5)`.
- `CHECK (client_id <> couturier_id)`.
- `UNIQUE (client_id, commande_id)` — une seule note par client/commande (NULL accepté plusieurs fois en PG sauf si on l'impose autrement).

**Index** :
- `idx_notes_couturier_visible (couturier_id, est_visible)` — agrégation moyenne.

> **Calcul de la note globale** (RC-05 v2) : `note_moyenne_globale = (note_service + note_conception + note_livraison + note_delai) / 4`, agrégée par couturier. Mise en cache sur `users.note_moyenne_globale` via observer Eloquent à chaque création/modération.

---

## 8. Zone Technique — Tables auto-générées

### 8.1 Par packages installés

| Table | Package | Rôle |
|---|---|---|
| `migrations` | Laravel | Suivi des migrations |
| `cache`, `cache_locks` | Laravel | Cache base |
| `jobs`, `job_batches`, `failed_jobs` | Laravel + Horizon | Queue Redis |
| `personal_access_tokens` | Sanctum | Tokens API mobile |
| `sessions` | Laravel | Sessions Filament |
| `password_reset_tokens` | Breeze | Reset password |
| `roles`, `permissions`, `model_has_roles`, `model_has_permissions`, `role_has_permissions` | Spatie Permission | RBAC |
| `activity_log` | Spatie Activitylog | Audit trail (qui a fait quoi) |
| `media` | Spatie MediaLibrary | KYC + photos modèles + fichiers patrons |
| `tags`, `taggables` | Spatie Tags | Tags modèles |
| `telescope_entries`, `telescope_entries_tags`, `telescope_monitoring` | Telescope (dev) | Debug |

### 8.2 `activity_log` — colonnes principales (audit RGPD)

| Colonne | Type | Description |
|---|---|---|
| `id`, `log_name`, `description` | | |
| `subject_type`, `subject_id` | morph | Entité concernée |
| `causer_type`, `causer_id` | morph | Auteur de l'action (user) |
| `properties` | JSON | Diff before/after |
| `created_at`, `updated_at` | | |

> **Usage** : tracer les opérations sensibles : validation/rejet KYC, validation FicheMesure, modération NoteCouturier, soumission/rejet ModelVetement, paiement, etc.

### 8.3 `media` (Spatie) — collections utilisées

| Collection | Modèle | Usage |
|---|---|---|
| `kyc_recto` | `User` | Photo recto pièce d'identité |
| `kyc_verso` | `User` | Photo verso pièce d'identité |
| `kyc_selfie` | `User` | Selfie de vérification |
| `avatar` | `User` | Photo de profil |
| `modele_photos` | `ModeleVetement` | Galerie photos du modèle |
| `patron_fichier` | `Patron` | Fichier source (PDF, SVG, IMG) |

> Le polymorphisme de `media.model_type` + `model_id` permet d'attacher 0..n fichiers à n'importe quelle entité, sans coupler la table. C'est plus propre que des colonnes `*_url` sur `users`.

---

## 9. Diagramme relationnel résumé

```
                          ┌──────────────────┐
                          │types_mensurations│◄─────────┐
                          └────┬─────────────┘          │
                          pivot│                  source/cible
                          ┌────▼────────┐  ┌────────────┴───────┐
                          │types_vetements│  │regles_proportions │
                          └────┬─────────┘  └────────────────────┘
                       classifie│
                          ┌─────▼────────────────────────────┐
                          │     modeles_vetements             │
                          │  (createur_id → users)            │
                          └─┬──────────────────┬──────────────┘
                            │ 1:N      inclut  │ 1:0..1
                  ┌─────────▼──────────┐  ┌────▼───────┐
                  │mensurations_modeles│  │  patrons   │
                  └────────────────────┘  └────┬───────┘
                                       comporte│ 1:N
                                       ┌───────▼──────────┐
                                       │ pieces_patrons   │
                                       └────┬─────────────┘
                                       porte│ 1:N
                                       ┌────▼──────────────────┐
                                       │annotations_patrons    │
                                       │  → types_mensurations │
                                       └───────────────────────┘

   ┌──────────────┐ commander ┌──────────────────┐ liée  ┌─────────────────────┐
   │   users      │◄──────────┤ fiches_clients   │◄──────┤ commandes_vetements │
   │ (STI: COUT/  │ N:M       │(couturier+client)│ 1:N   │ (modele+fichemesure)│
   │  ADMIN/CLI)  │           └──────────────────┘       └─────┬───────────────┘
   │ + KYC champs │                                            │ 1:N
   └──┬───────────┘                                       ┌────▼────────┐
   a  │ 1:N (Client → FicheMesure)                        │  paiements  │
      ▼                                                   └─────────────┘
   ┌────────────────┐ contient ┌────────────────────┐
   │ fiches_mesures ├─────────►│ lignes_mensurations│
   └────────────────┘  1:N     │ → types_mensurations│
                               └─────────────────────┘

   ┌──────────────┐
   │   users      │◄─── notes_couturiers ──── (4 sous-notes : service,
   └──────────────┘                            conception, livraison, délai)
```

---

## 10. Checklist des migrations Laravel à créer

Ordre recommandé (respect des dépendances FK) :

```
01_extend_users_table.php                     (ajoute type, kyc_*, telephone, specialite, etc.)
02_create_types_mensurations_table.php
03_create_types_vetements_table.php           (FK → types_mensurations pour pivot)
04_create_regles_proportions_table.php        (2 FK → types_mensurations)
05_create_fiches_clients_table.php
06_create_fiches_mesures_table.php            (FK → users CLIENT)
07_create_lignes_mensurations_table.php
08_create_modeles_vetements_table.php
09_create_mensurations_modeles_table.php
10_create_patrons_table.php
11_create_pieces_patrons_table.php
12_create_annotations_patrons_table.php
13_create_commandes_vetements_table.php
14_create_paiements_table.php
15_create_notes_couturiers_table.php
```

> Les migrations Spatie/Sanctum/Telescope/MediaLibrary sont déjà publiées (cf. terminal d'installation).

---

## 11. Convention Eloquent côté code

Pour respecter la convention `id` + `external_id` sur tous les Models, créer un trait :

```php
// app/Models/Concerns/HasExternalId.php
trait HasExternalId
{
    protected static function bootHasExternalId(): void
    {
        static::creating(function ($model) {
            if (empty($model->external_id)) {
                $model->external_id = (string) Str::uuid();
            }
        });
    }

    public function getRouteKeyName(): string
    {
        return 'external_id'; // route-model-binding via UUID
    }
}
```

Tous les Models métier `use HasExternalId;` → URLs et JSON exposent automatiquement l'UUID, jamais l'`id` interne.

### 11.1 STI sur `User` — exemple Laravel

```php
// app/Models/User.php  (parent)
class User extends Authenticatable {
    protected $table = 'users';
    public function newFromBuilder($attributes = [], $connection = null) {
        $model = match($attributes->type) {
            'COUTURIER'      => new Couturier(),
            'ADMINISTRATEUR' => new Administrateur(),
            'CLIENT'         => new Client(),
            default          => new self(),
        };
        $model->exists = true;
        $model->setRawAttributes((array) $attributes, true);
        $model->setConnection($connection ?: $this->getConnectionName());
        return $model;
    }
}

// app/Models/Couturier.php
class Couturier extends User {
    protected $attributes = ['type' => 'COUTURIER'];
    protected static function booted() {
        static::addGlobalScope('couturier', fn($q) => $q->where('type','COUTURIER'));
    }
}
// Idem pour Client, Administrateur
```

---

## 12. Volumétrie estimée (sprint MVP — 6 semaines)

| Table | Lignes attendues fin MVP |
|---|---|
| `users` (Couturiers) | ~50 |
| `users` (Clients) | ~500 |
| `fiches_clients` | ~800 |
| `fiches_mesures` | ~1 500 |
| `lignes_mensurations` | ~25 000 (≈17 mensurations/fiche) |
| `modeles_vetements` | ~200 (dont 30 GLOBAL) |
| `commandes_vetements` | ~1 000 |
| `paiements` | ~1 200 (1.2 paiements/commande en moyenne) |
| `notes_couturiers` | ~400 |
| `media` | ~5 000 (KYC + modèles + patrons) |
| `activity_log` | ~50 000 |

> Volumes parfaitement à l'aise pour PostgreSQL — pas de besoin de partitionnement à ce stade. Index ciblés suffisants.

---

## 13. Différences vs v1.0 (changement diagramme)

| Élément | v1.0 (ancien md) | v2.0 (nouveau xlsx) |
|---|---|---|
| **KYC** | Table séparée `verifications_identite` | **Fusionné dans `users`** (champs `kyc_*`) |
| **NoteCouturier** | 1 note unique (1–5) | **4 sous-notes** (service, conception, livraison, délai) |
| **Paiement** | `paiements` simple (agregateur, transaction_id) | Enrichi : `mode` ENUM, `metadonnees_agregateur` JSON, `reference_externe` |
| **FicheMesure** | Rattachée à `FicheClient` | **Rattachée directement à `Client`** |
| **FicheClient** | notes, archivee, dernierContact | Simplifiée : juste `dateCreation` + `estActif` |
| **Client.notes** | Sur `FicheClient` (notes du couturier) | Sur `users.notes_personnelles` (notes du client) |
| **Couturier** | telephone, specialite, adresse nullable + ville | telephone, specialite, adresse **NOT NULL** ; **ville supprimée** |
| **MessageContact** | Présent | **Supprimée** du diagramme |
| **CommandeVetement.montant** | Présent | **Supprimé** (porté par Paiement) |
| **CommandeVetement** FK | `couturier_id`, `client_id` directs | Via `fiche_client_id` (suit le diagramme) |
| **ModelVetement** | etapeActuelle, commentaireRejet | **Supprimés** |
| **AnnotationPatron** | point_x, point_y NUMERIC + direction ENUM | label + position_depart/fin (string) + orientation |
| **Patron** | format, fichier_path | methode, **version** (int), fichier_url, **donnees_dessin** JSON, statut |
| **TypeVetement.mensuration_pivot** | Champ direct | Implémenté via association → reste FK directe (commodité) |

---

**Fin du document — schéma BDD v2.0 fidèle à `Class Diagram1` du fichier `export_couture.xlsx`.**
