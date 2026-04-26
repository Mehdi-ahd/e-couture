# Diagramme de Classes
## Plateforme Intelligente de Couture

**Version** : 1.2
**Date** : Avril 2026

---

## 1. Vue d'ensemble des entités

Le modèle se décompose en cinq grandes zones :
1. **Référentiels** : types de vêtements, types de mensurations, règles de proportion
2. **Acteurs / Utilisateurs** : hiérarchie par rôle (`Couturier`, `Administrateur`, `Client`) — tous spécialisations de la classe abstraite `Utilisateur`
3. **Fiches clients d'un couturier** : la relation N:M entre `Couturier` et `Client` est portée par la **classe-association `FicheClient`** (anciennement `CarnetClient`) qui contient les informations privées du carnet (notes du couturier, archivage, etc.). Chaque fiche peut produire un historique de mesures.
4. **Bibliothèque & Modèles de vêtements** : modèles à portée globale (partagée) ou privée, mensurations de référence, patrons annotés
5. **Commandes** : `CommandeVetement` lie un client, un couturier et un modèle ; le patron adapté est généré dynamiquement à l'initialisation (non persisté)

> **Évolution V1** : `Client` est maintenant une **classe d'utilisateur de plein droit** (sous-classe d'`Utilisateur`) car le client dispose désormais d'un compte. Les données privées que le couturier conserve sur ses clients (notes, archivage du carnet) sont déplacées dans la classe-association `FicheClient`.

---

## 2. Classes et Attributs

---

### Zone 1 — Référentiels

#### Classe : `TypeVetement`
*(inchangé)* — Catégorie de vêtement définissant la mensuration pivot pour la règle de trois.

| Attribut | Type |
|----------|------|
| id, code, nom, categorie (HAUT/BAS/ROBE/ENSEMBLE/ACCESSOIRE), mensurationPivot_id, description, estActif, createdAt, updatedAt | — |

**Relations :** classifie 0..* `ModelVetement` ; référence 1 `TypeMensuration` (pivot).

#### Classe : `TypeMensuration`
*(inchangé)*

| Attribut | Type |
|----------|------|
| id, code, nom, unite, categorie (PRINCIPALE/SECONDAIRE), description, estActif | — |

#### Classe : `RegleProportion`
*(inchangé)* — Formule appliquée après la règle de trois pour affiner les déductions :
```
mensurationCible = (mensurationSource × coefficient) + offset
```

| Attribut | Type |
|----------|------|
| id, nom, mensurationSource_id, mensurationCible_id, coefficient, offset, sourceMetier, version, estActive, createdAt | — |

---

### Zone 2 — Acteurs / Utilisateurs

> Les rôles sont modélisés par une hiérarchie de classes : `Utilisateur` est la classe mère **abstraite**. Trois sous-classes concrètes la spécialisent : `Couturier`, `Administrateur`, et désormais **`Client`**.

#### Classe : `Utilisateur` *(abstraite)*

| Attribut | Type | Description |
|----------|------|-------------|
| id | UUID | Identifiant unique |
| nom | String | Nom de famille |
| prenom | String | Prénom |
| email | String (nullable) | Adresse email — **unique si renseignée** |
| telephone | String (nullable) | Numéro de téléphone — **unique si renseigné** |
| motDePasseHash | String | Mot de passe hashé |
| estActif | Boolean | Compte actif ou désactivé |
| createdAt | DateTime | Date de création |
| lastLogin | DateTime (nullable) | Dernière connexion |

> **Contrainte d'identification** : `email OR telephone` doit être renseigné (au moins l'un des deux). Cette contrainte permet de couvrir le contexte béninois où de nombreux adultes n'ont pas d'email mais disposent toujours d'un téléphone.

**Généralisation** : spécialisée par `Couturier`, `Administrateur` et `Client`.

---

#### Classe : `Couturier` *(spécialisation)*
Utilisateur principal, accès via mobile Flutter.

| Attribut | Type |
|----------|------|
| specialite (nullable), adresseAtelier (nullable), bio (nullable), ville (nullable) | — |

**Relations :**
- A 0..* `FicheClient` (chaque fiche associe un Couturier à un Client)
- Crée 0..* `ModelVetement` (PRIVE ou SOUMIS)
- Émet 0..* `CommandeVetement`
- Reçoit 0..* notes (UC-24) — moyenne stockée comme indicateur agrégé

---

#### Classe : `Administrateur` *(spécialisation)*
*(pas d'attributs supplémentaires)*

---

#### Classe : `Client` *(spécialisation — nouveau V1)*
Utilisateur final disposant d'un compte. Peut s'inscrire seul (UC-19) ou voir son compte créé par un couturier (UC-20).

| Attribut | Type | Description |
|----------|------|-------------|
| dateNaissance | Date (nullable) | Date de naissance |
| ville | String (nullable) | Ville de résidence |

> Les attributs hérités d'`Utilisateur` (nom, prénom, email, telephone, etc.) sont suffisants. Les **notes propres au couturier** (préférences, mémo) ne sont pas stockées sur `Client` mais dans `FicheClient` (voir Zone 3) : un même client peut être suivi par plusieurs couturiers, chacun avec ses propres notes.

**Relations :**
- Référencé par 0..* `FicheClient`
- Concerné par 0..* `CommandeVetement`
- Émet 0..* notes/commentaires sur des couturiers (UC-24)
- Émet 0..* messages de contact à des couturiers (UC-23)

---

### Zone 3 — Fiches clients d'un couturier

#### Classe-association : `FicheClient`
Représente la relation N:M entre `Couturier` et `Client` ainsi que les informations propres au suivi d'un client par un couturier donné. Remplace la classe `CarnetClient` de la version 1.0 : il n'y a plus de "carnet" objet — un couturier voit ses fiches comme l'ensemble des `FicheClient` qu'il possède.

| Attribut | Type | Description |
|----------|------|-------------|
| id | UUID | Identifiant unique |
| notes | Text (nullable) | Notes privées du couturier sur ce client |
| estArchive | Boolean | Fiche archivée par le couturier (sans suppression) |
| dateAjout | DateTime | Date d'ajout du client par ce couturier |
| dernierContact | DateTime (nullable) | Dernière interaction enregistrée |

**Multiplicité** : `Couturier 1 ──── 0..* FicheClient ────── 1 Client`
> Un même `Client` peut figurer dans plusieurs `FicheClient` (un par couturier qui le suit). Un `Couturier` voit autant de `FicheClient` qu'il a de clients suivis.

**Relations :**
- Lie 1 `Couturier` à 1 `Client`
- Possède 0..* `FicheMesure`

---

#### Classe : `FicheMesure`
Enregistrement d'une prise de mesures complète, intégrant le suivi du traitement IA asynchrone.

| Attribut | Type |
|----------|------|
| id, date, methode (PHOTO/MANUELLE), statutTraitement (EN_ATTENTE/EN_COURS/TERMINE/ECHEC), traitementId (nullable), versionRegles (nullable), notes (nullable), statut (BROUILLON/VALIDEE), createdAt | — |

**Relations :** appartient à 1 `FicheClient` (composition) ; contient 0..* `LigneMensuration`.

---

#### Classe : `LigneMensuration`
*(inchangé)*

| Attribut | Type |
|----------|------|
| id, valeur, source (ESTIMEE/MANUELLE/DEDUITE_PROPORTION/DEDUITE_REGLE_DE_TROIS/DEDUITE_COMBINEE), confiance (nullable), commentaire (nullable) | — |

---

### Zone 4 — Bibliothèque et Modèles de Vêtements

#### Classe : `ModelVetement`
*(inchangé)*

| Attribut | Type |
|----------|------|
| id, nom, description (nullable), portee (PRIVE/GLOBAL), statut (BROUILLON/ACTIF/SOUMIS/REJETE/ARCHIVE), etapeActuelle, commentaireRejet (nullable), createdAt, updatedAt | — |

#### Classe : `MensurationModele`
*(inchangé)* — Mensuration de référence d'un modèle (Étape 1).

#### Classe : `Patron`
*(inchangé)* — Patron du modèle selon trois méthodes (UPLOAD/CREATION/IA).

#### Classe : `PiecePatron`
*(inchangé)*

#### Classe : `AnnotationPatron`
*(inchangé)*

---

### Zone 5 — Commandes

#### Classe : `CommandeVetement`
Association entre un couturier, un client (utilisateur réel disposant d'un compte) et un modèle. Représente une commande de confection sur mesure. À l'initialisation, la plateforme génère dynamiquement l'affichage du patron adapté en superposant les `LigneMensuration` du client sur les `AnnotationPatron` du `Patron` du modèle. **Aucune entité persistée n'est créée pour le patron adapté.**

| Attribut | Type | Description |
|----------|------|-------------|
| id | UUID | Identifiant unique |
| statut | Enum | EN_ATTENTE, EN_CONFECTION, TERMINE, ANNULE |
| notes | Text (nullable) | Instructions spéciales du couturier |
| dateCommande | DateTime | Date de création |
| dateLivraison | Date (nullable) | Date de livraison souhaitée |
| montant | Decimal (nullable) | Montant convenu (optionnel) |
| updatedAt | DateTime | Dernière modification |

**Relations :**
- Émise par 1 `Couturier`
- Concerne 1 `Client`
- Porte sur 1 `ModelVetement` (portée GLOBAL ou PRIVE)
- Utilise 1 `FicheMesure` au statut VALIDEE
- Peut donner lieu à 0..1 note (`NoteCouturier`, voir ci-dessous)

---

#### Classe : `NoteCouturier` *(nouveau V1 — UC-24)*
Note laissée par un client sur un couturier après une commande terminée.

| Attribut | Type | Description |
|----------|------|-------------|
| id | UUID | Identifiant unique |
| valeur | Integer | Note de 1 à 5 |
| commentaire | Text (nullable) | Commentaire libre |
| dateNotation | DateTime | Date de la note |
| estVisible | Boolean | Visible publiquement (modérable) |

**Relations :** liée à 1 `Client` (auteur), 1 `Couturier` (cible) et 0..1 `CommandeVetement`.

---

#### Classe : `MessageContact` *(nouveau V1 — UC-23)*
Message envoyé par un client à un couturier depuis le catalogue.

| Attribut | Type | Description |
|----------|------|-------------|
| id | UUID | Identifiant unique |
| sujet | String | Sujet du message |
| contenu | Text | Message |
| dateEnvoi | DateTime | Date d'envoi |
| statut | Enum | NON_LU, LU, REPONDU |

**Relations :** émis par 1 `Client`, reçu par 1 `Couturier`.

---

## 3. Hiérarchie des Utilisateurs (Généralisation)

```
              ┌───────────────────────────────────────────┐
              │           Utilisateur (abstrait)          │
              │  id, nom, prenom, email?, telephone?,     │
              │  motDePasseHash, estActif, createdAt,     │
              │  lastLogin                                │
              │  ◊ contrainte: email OR telephone         │
              └─────────────┬─────────────────────────────┘
              ┌─────────────┼────────────────────┐
              ▼             ▼                    ▼
        ┌──────────┐ ┌───────────────┐    ┌─────────────────┐
        │Couturier │ │Administrateur │    │     Client      │
        │specialite│ └───────────────┘    │ dateNaissance?  │
        │ville     │                      │ ville?          │
        │bio       │                      └─────────────────┘
        └──────────┘
```

> **Important** : il s'agit d'une généralisation **entre classes** (diagramme de classes), à ne pas confondre avec la généralisation entre **acteurs** (diagramme de cas d'utilisation), qui a été **supprimée** dans la V1 (voir `01_cas_utilisation.md`).

---

## 4. Résumé des Relations

```
TypeVetement ──(pivot)── TypeMensuration ───── RegleProportion
     │                        │   │                (source → cible)
     │ classifie              │   ├── LigneMensuration ─── FicheMesure ──┐
     *                        │   │                                       │
  ModelVetement ──────────────┘   ├── MensurationModele                  │ rattachée à
     │  │ (PRIVE/GLOBAL)          └── AnnotationPatron ── PiecePatron    │
     │  └── Patron ─── PiecePatron                                       │
     │                                                              FicheClient
     │                                                              ╱        ╲
     │                                                       Couturier      Client
     │                                                              ╲        ╱
     └── CommandeVetement (Couturier × Client × Modèle × FicheMesure)
                  │
                  ├── NoteCouturier (Client → Couturier)
                  └── MessageContact (Client → Couturier)
```

---

## 5. Contraintes et Règles de Gestion

### Contraintes d'intégrité

| Règle | Description |
|-------|-------------|
| RG-01 | Une `FicheClient` est unique pour un couple (Couturier, Client) |
| RG-02 | Un compte `Utilisateur` doit avoir au moins un email **ou** un téléphone (unicité globale par champ renseigné) |
| RG-03 | Un `ModelVetement` n'est utilisable dans une commande que si statut = ACTIF (PRIVE pour le créateur) ou portée = GLOBAL |
| RG-04 | Une `CommandeVetement` requiert une `FicheMesure` au statut VALIDEE |
| RG-05 | L'affichage du patron adapté requiert que le `ModelVetement` ait ses deux étapes complètes |
| RG-06 | Le patron adapté est calculé à la volée — non persisté en base |
| RG-07 | La désactivation d'un `TypeMensuration` n'affecte pas les données déjà enregistrées |
| RG-08 | Une `RegleProportion` ne peut pas créer de dépendance circulaire |
| RG-09 | Un `ModelVetement` GLOBAL ne peut pas être modifié directement par un couturier ; toute modification crée une copie privée |
| RG-10 | Un `ModelVetement` SOUMIS ne peut plus être modifié par le couturier jusqu'à décision admin |
| RG-11 | Une `FicheMesure` PHOTO ne peut passer à VALIDEE tant que `statutTraitement` ≠ TERMINE |
| RG-12 | Une `NoteCouturier` ne peut être créée que si le client a au moins une `CommandeVetement` au statut TERMINE auprès du couturier ciblé |
| RG-13 | Un compte `Client` créé par un couturier (UC-20) doit forcer le changement de mot de passe à la première connexion |

### Règles métier de calcul *(inchangé)*

| Règle | Formule |
|-------|---------|
| RC-01 | Règle de trois : `valeur_cible = (valeur_ref_cible × valeur_client_source) / valeur_ref_source` |
| RC-02 | Raffinement : `valeur_finale = (valeur_brute × coefficient) + offset` |
| RC-03 | Écart d'adaptation : `écart_i = valeur_client_i - valeur_modèle_i` |
| RC-04 | Dimension pièce adaptée : `dimension_adaptée = dimension_modèle + écart_i` |
| RC-05 | Note moyenne couturier : moyenne des `NoteCouturier.valeur` où `estVisible = true` |
