# Diagramme de Cas d'Utilisation
## Plateforme Intelligente de Couture

**Version** : 1.4
**Date** : Avril 2026
**Évolution v1.4** : reformatage des **scénarios alternatifs** en numérotation romaine continue traversant scénario principal et alternatives, avec mention "En [étape]" pour chaque scénario alternatif.

---

## 1. Acteurs du Système

### 1.1 Liste des acteurs

```
   ┌──────────┐    ┌──────────┐    ┌────────────────┐    ┌──────────┐
   │  Client  │    │ Couturier│    │ Administrateur │    │ Système  │
   └──────────┘    └──────────┘    └────────────────┘    └──────────┘
       (humain)       (humain)         (humain)         (acteur externe non humain)
```

> **Évolution V1** : la hiérarchie de généralisation entre acteurs (parent abstrait `Utilisateur` → `Couturier` / `Administrateur`) a été **supprimée**. L'acteur `Client` est désormais un acteur **concret et de plein droit** : il dispose de son propre compte, accède à la plateforme et déclenche directement des cas d'utilisation. Tous les acteurs humains accèdent directement à `UC-00 S'authentifier` (associations directes acteur → cas).

### 1.2 Description des acteurs

| Acteur | Nature | Description |
|--------|--------|-------------|
| **Client** | Humain | Personne finale qui souhaite faire confectionner un vêtement. Dispose d'un compte (créé par un couturier ou via auto-inscription) et peut consulter les couturiers, leurs catalogues, les contacter et les noter. |
| **Couturier** | Humain | Utilisateur principal — gère ses fiches clients, prend les mesures, crée des modèles et génère des patrons adaptés (application mobile Flutter). |
| **Administrateur** | Humain | Configure les référentiels, gère les comptes et modère la bibliothèque globale (interface web). |
| **Système** | Externe (non humain) | Microservice Python (traitement IA des images) + API Laravel (calculs de déduction et d'adaptation). |

### 1.3 Authentification — email OU téléphone (contexte Bénin)

Au Bénin, de nombreux adultes ne disposent pas d'adresse email mais ont systématiquement un numéro de téléphone. La plateforme **autorise indifféremment l'email ou le numéro de téléphone** comme identifiant de connexion. Au moins un des deux est obligatoire à l'inscription d'un client. Les couturiers et administrateurs continuent d'utiliser l'email comme identifiant principal.

---

## 2. Cadre Système

> Tous les cas d'utilisation sont placés à l'intérieur du cadre système nommé **"Plateforme Intelligente de Couture"**.
> Les acteurs (Client, Couturier, Administrateur, Système) sont positionnés à l'extérieur de ce cadre.
> Les labels dans les bulles UML ne contiennent **pas** le préfixe "UC-XX" — les identifiants servent uniquement à la documentation.

---

## 3. Convention de notation des scénarios

Chaque cas d'utilisation suit la convention suivante :

- Le **scénario principal** est numéroté `1, 2, 3…` (ce qui correspond aux étapes `i, ii, iii…` en romain).
- Les **scénarios alternatifs** sont nommés `A1, A2, A3…` et précisent à quelle étape du scénario principal ils se déclenchent (mention « **En i / En ii / En iii…** »).
- Les étapes des scénarios alternatifs **continuent la numérotation romaine** à partir de la dernière étape du scénario principal et **ne se réinitialisent pas** entre A1, A2, A3.
  *Exemple* : si le scénario principal se termine à `iii`, le premier alternatif (A1) commence à `iv` ; si A1 va jusqu'à `vii`, alors A2 commence à `viii`, et ainsi de suite.

---

## 4. Cas d'Utilisation

### 4.0 Cas commun à tous les acteurs humains

#### UC-00 — S'authentifier

**Acteurs** : Client, Couturier, Administrateur (associations directes — plus aucune généralisation entre acteurs)

**Description** : Se connecter à la plateforme avec son identifiant (email **OU** téléphone pour les clients ; email pour les couturiers et administrateurs) et son mot de passe.

**Préconditions** : L'utilisateur dispose d'un compte actif.

**Postconditions** : Une session authentifiée est ouverte ; un token Sanctum est délivré ; l'interface adaptée au rôle est affichée.

**Scénario principal** :
1. L'utilisateur saisit son identifiant (email ou téléphone) et son mot de passe
2. L'API Laravel vérifie les identifiants (token Sanctum)
3. La session est ouverte et l'interface adaptée au rôle est affichée

**Scénarios alternatifs** :

**A1 — Identifiants incorrects** (déclenché à l'étape 2)
En ii :
iv. L'API détecte que l'identifiant n'existe pas ou que le mot de passe ne correspond pas
v. Le système incrémente le compteur d'échecs de l'utilisateur
vi. Un message d'erreur générique est affiché (« Identifiant ou mot de passe incorrect ») — pas de précision sur la nature de l'erreur (sécurité)
vii. Le scénario reprend à l'étape 1

**A2 — Compte verrouillé après 5 tentatives** (déclenché à l'étape 2, si compteur d'échecs ≥ 5)
En ii :
viii. Le système verrouille temporairement le compte (15 minutes)
ix. Un email ou SMS est envoyé à l'utilisateur pour notifier le verrouillage
x. Un message indique la durée de verrouillage et propose la procédure de récupération
xi. Le scénario se termine en échec

**A3 — Compte désactivé par l'administrateur** (déclenché à l'étape 2)
En ii :
xii. L'API constate que le compte est marqué INACTIF (UC-16)
xiii. Un message indique que le compte est désactivé et invite à contacter le support
xiv. Le scénario se termine en échec

**A4 — Mot de passe oublié** (déclenché à l'étape 1)
En i :
xv. L'utilisateur clique sur « Mot de passe oublié »
xvi. Il saisit son identifiant (email ou téléphone)
xvii. Le système envoie un lien de réinitialisation (email) ou un code OTP (SMS)
xviii. L'utilisateur définit un nouveau mot de passe
xix. Le scénario reprend à l'étape 1 avec le nouveau mot de passe

**A5 — Première connexion (mot de passe temporaire)** (déclenché à l'étape 3, après UC-20)
En iii :
xx. Le système détecte que l'utilisateur se connecte avec un mot de passe temporaire
xxi. Il l'oblige à définir un nouveau mot de passe avant d'accéder à l'interface
xxii. Le scénario reprend à l'étape 3

**A6 — Pas de connexion réseau** (déclenché à l'étape 2)
En ii :
xxiii. La requête vers l'API échoue (timeout réseau)
xxiv. L'application Flutter affiche un message « Vérifiez votre connexion Internet »
xxv. Si un token valide est encore en cache local et non expiré, l'application propose un mode dégradé en lecture seule (consultation des fiches déjà téléchargées)
xxvi. Sinon, le scénario se termine en échec

---

### 4.1 Acteur : Couturier

#### UC-01 — Gérer son carnet clients

**Description** : Créer, consulter, modifier et archiver les fiches de ses clients dans son espace privé.

**Préconditions** : Le couturier est authentifié.

**Postconditions** : Le carnet client est mis à jour de manière persistante.

**Scénario principal** *(création d'une fiche)* :
1. Le couturier accède à son carnet (application mobile Flutter)
2. Il choisit « Nouvelle fiche client »
3. Il saisit les informations : nom, prénom, **téléphone (obligatoire)**, email (optionnel), date de naissance (optionnel), notes
4. Il valide
5. L'application valide le format des données et l'unicité du téléphone dans son carnet
6. La fiche est enregistrée dans son espace privé (sync API Laravel + cache local Isar)

**Scénarios alternatifs** :

**A1 — Téléphone invalide ou manquant** (déclenché à l'étape 5)
En v :
vii. Le système détecte que le numéro de téléphone est absent ou ne respecte pas le format E.164 (+229…)
viii. Un message d'erreur surligne le champ concerné
ix. Le scénario reprend à l'étape 3

**A2 — Téléphone déjà utilisé dans le carnet** (déclenché à l'étape 5)
En v :
x. Le système détecte qu'une fiche existe déjà avec ce numéro
xi. Une boîte de dialogue propose : « Ouvrir la fiche existante » ou « Modifier le numéro »
xii. Si « Ouvrir » : redirection vers la fiche existante (UC-06)
xiii. Si « Modifier » : reprise à l'étape 3

**A3 — Modification d'une fiche existante** (déclenché à l'étape 1)
En i :
xiv. Le couturier sélectionne une fiche existante dans la liste
xv. Il choisit « Modifier »
xvi. Il met à jour les champs souhaités
xvii. Reprise à partir de l'étape 4

**A4 — Recherche par nom ou téléphone** (déclenché à l'étape 1)
En i :
xviii. Le couturier saisit une chaîne dans la barre de recherche du carnet
xix. Le système filtre les fiches en temps réel (recherche locale Isar puis API si pas trouvée localement)
xx. Le couturier sélectionne une fiche pour consulter / modifier

**A5 — Archivage d'une fiche** (déclenché à l'étape 1)
En i :
xxi. Le couturier ouvre une fiche et choisit « Archiver »
xxii. Le système demande confirmation
xxiii. Si confirmé : la fiche passe au statut ARCHIVEE (non détruite — historique conservé pour traçabilité)
xxiv. La fiche disparaît de la liste active mais reste accessible via le filtre « Archivées »

**A6 — Création d'un compte d'accès pour le client** (déclenché après l'étape 6)
En vi :
xxv. Une fois la fiche enregistrée, le couturier choisit « Créer un compte d'accès »
xxvi. Le scénario continue dans UC-20

**A7 — Synchronisation différée hors ligne** (déclenché à l'étape 6)
En vi :
xxvii. La requête API échoue (pas de réseau)
xxviii. La fiche est enregistrée localement dans Isar avec un marqueur pending_sync
xxix. Une notification informe le couturier « Fiche enregistrée hors ligne — synchronisation automatique au retour du réseau »
xxx. Au retour du réseau, un service en arrière-plan pousse les fiches en attente vers l'API

---

#### UC-02 — Enregistrer les mesures d'un client

**Description** : Réaliser une prise de mesures complète via le guidage photo IA ou par saisie manuelle.

**Préconditions** : La fiche client existe ; le couturier est authentifié.

**Cas inclus** : `<<include>>` UC-03, UC-04, UC-05.

**Postconditions** : Une `FicheMesure` est créée et liée au client.

**Scénario principal** *(mode IA assisté)* :
1. Le couturier sélectionne un client et lance une prise de mesures
2. Il choisit le mode « Assisté par IA »
3. L'application déclenche le guidage photo pour les 4 angles (UC-03)
4. Les 4 photos sont soumises au pipeline IA (UC-04)
5. Le couturier visualise les estimations et les corrige si besoin
6. L'API déduit les mensurations secondaires (UC-05)
7. La fiche de mesures est enregistrée et liée au client

**Scénarios alternatifs** :

**A1 — Saisie manuelle directe sans photo** (déclenché à l'étape 2)
En ii :
viii. Le couturier choisit le mode « Saisie manuelle »
ix. L'application affiche le formulaire des mensurations principales
x. Le couturier saisit chaque mesure au mètre ruban
xi. Reprise à l'étape 6 (déduction des mensurations secondaires)

**A2 — Mode mixte (photo + ajustement manuel)** (déclenché à l'étape 5)
En v :
xii. Pour une mesure jugée incertaine par le couturier, il bascule en saisie manuelle locale
xiii. L'IA marque les mensurations corrigées avec un drapeau corrigee_manuellement (audit qualité)
xiv. Reprise à l'étape 6

**A3 — Échec total du pipeline IA** (déclenché à l'étape 4)
En iv :
xv. Le pipeline IA renvoie une erreur (qualité photo insuffisante, calibration impossible, pose non détectée)
xvi. L'application propose au couturier de : « Reprendre les photos » (retour à l'étape 3) ou « Basculer en saisie manuelle » (déroulé identique à A1)

**A4 — Renouvellement mineur d'une fiche existante** (déclenché à l'étape 1)
En i :
xvii. Le couturier ouvre une fiche de mesures existante
xviii. Il choisit « Mettre à jour quelques mesures »
xix. Il modifie 1 à 5 mesures spécifiques (ex : tour de taille après prise de poids)
xx. Une nouvelle version de la fiche est créée (versioning) avec les anciennes mesures conservées pour comparaison

**A5 — Annulation par le client** (déclenché à toute étape entre 1 et 6)
xxi. Le client retire son consentement à la prise de mesure
xxii. Le couturier annule la session
xxiii. Aucune donnée n'est persistée ; les photos en cache local sont supprimées

---

#### UC-03 — Guider la prise de photos

**Description** : Afficher sur l'application mobile Flutter les instructions de cadrage, de posture et l'élément de calibration requis pour chaque angle.

**Acteur principal** : Couturier (opérateur), avec le client comme sujet.

**Préconditions** : UC-02 en cours ; les permissions caméra sont accordées.

**Note** : Cas inclus par UC-02. Plugin Flutter `camera`.

**Scénario principal** :
1. L'application affiche le guide pour l'angle FACE (silhouette de référence + emplacement de la carte de calibration)
2. Le couturier positionne le client et la carte selon les indications
3. L'application détecte automatiquement la pose correcte et déclenche la capture
4. Idem pour PROFIL GAUCHE, PROFIL DROIT, DOS
5. Les 4 photos sont validées par le couturier (vignettes) avant transmission

**Scénarios alternatifs** :

**A1 — Permissions caméra refusées** (déclenché à l'étape 1)
En i :
vi. L'application détecte que l'autorisation caméra n'est pas accordée
vii. Une boîte de dialogue explique le besoin et propose d'ouvrir les paramètres
viii. Si l'utilisateur refuse : retour à UC-02 et bascule en saisie manuelle (UC-02 A1)

**A2 — Carte de calibration absente du cadre** (déclenché à l'étape 3)
En iii :
ix. L'application ne détecte pas la carte de référence (CR80 ou A4) dans le champ
x. Un message visuel guide le couturier « Placez la carte à hauteur de hanche dans le cadre »
xi. La capture est bloquée tant que la carte n'est pas détectée
xii. Reprise à l'étape 2

**A3 — Pose incorrecte du sujet** (déclenché à l'étape 3)
En iii :
xiii. L'application détecte une pose non conforme (bras non écartés, mauvais angle…)
xiv. Un overlay rouge indique la zone à corriger
xv. Reprise à l'étape 2

**A4 — Photo floue ou sous-exposée** (déclenché à l'étape 5)
En v :
xvi. L'analyse rapide locale détecte un flou ou une luminosité insuffisante
xvii. La photo est rejetée avec le motif « Flou » ou « Trop sombre »
xviii. Le couturier reprend la photo (retour à l'étape 1 pour l'angle concerné)

**A5 — Annulation en cours de séquence** (déclenché à toute étape entre 1 et 5)
xix. Le couturier interrompt la séquence
xx. Les photos déjà prises sont supprimées du cache local
xxi. Retour à UC-02

---

#### UC-04 — Traiter les images par IA

**Description** : Soumettre les photos au pipeline (calibration d'échelle + détection des points-clés via Google ML Kit Pose Detection on-device, puis estimation des mensurations principales).

**Acteur principal** : Système.

**Préconditions** : Les 4 photos validées sont disponibles ; la carte de référence est détectable.

**Note** : Cas inclus par UC-02. Traitement majoritairement on-device (Flutter), agrégation finale côté API Laravel.

**Scénario principal** :
1. L'application Flutter exécute la calibration d'échelle (détection de la carte → coefficient pixels/cm)
2. ML Kit Pose Detection extrait les 33 keypoints anatomiques pour chaque photo
3. L'application calcule les mensurations principales (épaule, poitrine, taille, hanches, longueurs) à partir des keypoints et du coefficient
4. Les mensurations principales sont envoyées à l'API Laravel
5. L'API renvoie les estimations validées avec un score de confiance par mesure

**Scénarios alternatifs** :

**A1 — Calibration impossible (carte non détectée)** (déclenché à l'étape 1)
En i :
vi. L'algorithme de détection de contours ne reconnaît pas la carte
vii. L'application renvoie un code d'erreur CALIBRATION_FAILED à UC-02
viii. UC-02 A3 prend le relais

**A2 — Détection de pose partielle** (déclenché à l'étape 2)
En ii :
ix. ML Kit ne détecte qu'une partie des 33 keypoints (occlusion, vêtements amples)
x. Si plus de 80 % des keypoints critiques sont détectés : reprise à l'étape 3 avec un drapeau confiance_reduite
xi. Sinon : code d'erreur POSE_INCOMPLETE → UC-02 A3

**A3 — Mensurations aberrantes** (déclenché à l'étape 5)
En v :
xii. L'API détecte une valeur hors plages physiologiques (ex : tour de taille = 30 cm)
xiii. La mesure est marquée aberrante mais incluse dans la réponse
xiv. L'application alerte visuellement le couturier à l'étape 5 d'UC-02

**A4 — Faible confiance globale** (déclenché à l'étape 5)
En v :
xv. Le score de confiance moyen est inférieur à 70 %
xvi. L'application recommande au couturier de vérifier au mètre les mensurations principales avant validation

---

#### UC-05 — Déduire les mensurations secondaires

**Description** : Calculer automatiquement les mensurations secondaires via règle de trois et règles de proportion versionnées (référentiel admin).

**Acteur principal** : Système (API Laravel).

**Préconditions** : Les mensurations principales sont disponibles ; un référentiel `RegleProportion` est actif.

**Note** : Cas inclus par UC-02.

**Scénario principal** :
1. L'API reçoit les mensurations principales validées
2. Elle charge les règles de proportion actives pour le `TypeVetement` et le `Sexe` du client
3. Pour chaque mensuration secondaire, elle applique la règle de trois (ratio mesure secondaire / mesure principale) à partir des mensurations modèle de référence
4. Toutes les mensurations (principales + déduites) sont enregistrées dans la `FicheMesure`
5. La fiche est renvoyée à l'application Flutter

**Scénarios alternatifs** :

**A1 — Aucune règle de proportion applicable** (déclenché à l'étape 2)
En ii :
vi. L'API ne trouve aucune règle active pour le TypeVetement + Sexe donnés
vii. Les mensurations secondaires sont laissées vides
viii. Un avertissement est renvoyé : « Référentiel incomplet — saisie manuelle requise »
ix. L'application Flutter ouvre la saisie manuelle des mensurations secondaires

**A2 — Règle de trois donne un résultat aberrant** (déclenché à l'étape 3)
En iii :
x. Une mesure déduite sort des bornes physiologiques de la règle
xi. La mesure est marquée aberrante et clampée aux bornes
xii. Un drapeau d'alerte est ajouté à la FicheMesure

**A3 — Plusieurs règles candidates** (déclenché à l'étape 2)
En ii :
xiii. Plusieurs règles actives correspondent (ex : règle générique + règle spécifique région)
xiv. L'API applique la règle la plus spécifique (priorité au scope le plus restreint)
xv. La règle utilisée est tracée dans la FicheMesure (audit)

---

#### UC-06 — Consulter le dossier d'un client

**Description** : Accéder à la fiche complète d'un client (informations, historique des mesures, commandes associées, photos).

**Préconditions** : Le couturier est authentifié et le client appartient à son carnet.

**Scénario principal** :
1. Le couturier sélectionne un client dans son carnet
2. L'application charge la fiche complète : informations personnelles, fiches de mesures (versions successives), commandes liées, notes
3. Le couturier consulte les sections par onglets (Profil / Mesures / Commandes / Notes)

**Scénarios alternatifs** :

**A1 — Client non trouvé / supprimé** (déclenché à l'étape 2)
En ii :
iv. La fiche n'existe plus côté serveur (suppression administrative)
v. L'application affiche « Ce client n'est plus accessible » et purge le cache local
vi. Retour au carnet

**A2 — Données partielles en mode hors ligne** (déclenché à l'étape 2)
En ii :
vii. Le couturier est hors ligne
viii. L'application affiche les données présentes dans le cache local Isar (peut être ancien)
ix. Un bandeau jaune indique « Données hors ligne — dernière sync : il y a 2 h »

**A3 — Comparaison de versions de mesures** (déclenché à l'étape 3)
En iii :
x. Le couturier sélectionne deux versions de fiche de mesures
xi. L'application affiche un comparatif côte à côte avec les variations en pourcentage
xii. Utile pour suivre l'évolution morphologique du client

---

#### UC-07 — Initialiser un modèle de vêtement

**Description** : Créer un nouveau modèle en deux étapes (mensurations modèle + patron) et, optionnellement, le soumettre à la bibliothèque.

**Cas inclus** : `<<include>>` UC-08, UC-09.

**Extensions** : `<<extend>>` UC-17 (soumission à la bibliothèque globale).

**Scénario principal** :
1. Le couturier choisit « Nouveau modèle » dans son catalogue
2. Il renseigne les métadonnées : nom, `TypeVetement`, sexe cible, description, photo de présentation
3. Il saisit les mensurations modèle (UC-08)
4. Il enregistre le patron (UC-09)
5. Il choisit la visibilité : `PRIVE` ou `SOUMETTRE_BIBLIOTHEQUE` (UC-17)
6. Le modèle est enregistré au statut `ACTIF` dans son catalogue privé

**Scénarios alternatifs** :

**A1 — Sauvegarde en brouillon** (déclenché à toute étape entre 2 et 5)
vii. Le couturier interrompt la création
viii. L'application sauvegarde l'état au statut BROUILLON
ix. Le modèle peut être repris ultérieurement

**A2 — Type de vêtement absent du référentiel** (déclenché à l'étape 2)
En ii :
x. Le TypeVetement souhaité n'existe pas
xi. Le couturier soumet une suggestion à l'admin (formulaire d'ajout)
xii. Le modèle reste en BROUILLON en attendant la validation

**A3 — Soumission à la bibliothèque globale** (déclenché à l'étape 5)
En v :
xiii. Le couturier coche « Soumettre à la bibliothèque »
xiv. Le modèle passe au statut EN_ATTENTE_MODERATION
xv. Une notification est envoyée à l'admin (UC-18)

**A4 — Duplication d'un modèle existant** (déclenché à l'étape 1)
En i :
xvi. Le couturier choisit « Dupliquer un modèle existant »
xvii. Il sélectionne un modèle source (sien ou bibliothèque)
xviii. Une copie modifiable est créée — reprise à l'étape 2

---

#### UC-08 — Saisir les mensurations modèle (Étape 1)

**Description** : Enregistrer les mensurations d'une personne de référence comme base pour la règle de trois.

**Note** : Inclus par UC-07.

**Scénario principal** :
1. L'application affiche les champs obligatoires selon le `TypeVetement` (haut, bas, robe entière…)
2. Le couturier saisit chaque mesure au cm
3. Il valide
4. Le système contrôle la cohérence (tour de poitrine > tour de taille pour un haut, etc.)
5. Les mensurations modèle sont enregistrées

**Scénarios alternatifs** :

**A1 — Mensuration manquante** (déclenché à l'étape 4)
En iv :
vi. Une mesure obligatoire est absente
vii. Le champ est surligné — message d'erreur
viii. Reprise à l'étape 2

**A2 — Incohérence détectée** (déclenché à l'étape 4)
En iv :
ix. Le système détecte une incohérence (ex : tour de taille > tour de hanches)
x. Une boîte de dialogue demande confirmation : « Êtes-vous sûr ? »
xi. Si confirmé : le drapeau coherence_overridee est posé pour audit
xii. Sinon : reprise à l'étape 2

**A3 — Import depuis une fiche client existante** (déclenché à l'étape 1)
En i :
xiii. Le couturier choisit « Importer depuis un client »
xiv. Il sélectionne un client de son carnet
xv. Les mensurations principales du client sont copiées comme mensurations modèle
xvi. Reprise à l'étape 3

---

#### UC-09 — Enregistrer le patron (Étape 2)

**Description** : Associer un patron au modèle (3 méthodes) et annoter les pièces.

**Note** : Inclus par UC-07.

**Extensions** :
- `<<extend>>` UC-09a : Upload d'un fichier patron
- `<<extend>>` UC-09b : Création directe dans l'éditeur intégré
- `<<extend>>` UC-09c : Génération par IA (V2)

**Scénario principal** :
1. Le couturier choisit la méthode (Upload / Éditeur / IA)
2. Il fournit le patron (selon la méthode — voir UC-09a, b, c)
3. L'application affiche les pièces détectées
4. Le couturier annote chaque pièce : nom, type (devant/dos/manche…), zone du corps liée
5. Il valide — le patron est enregistré au format SVG (format pivot)

**Scénarios alternatifs** :

**A1 — Pièces non détectées** (déclenché à l'étape 3)
En iii :
vi. L'application n'identifie aucune pièce dans le fichier
vii. Le couturier doit délimiter manuellement les pièces dans l'éditeur intégré
viii. Reprise à l'étape 4

**A2 — Annotation incomplète** (déclenché à l'étape 5)
En v :
ix. Au moins une pièce n'est pas annotée
x. Message d'erreur listant les pièces concernées
xi. Reprise à l'étape 4

**A3 — Reprise d'un patron existant** (déclenché à l'étape 1)
En i :
xii. Le couturier choisit « Réutiliser un patron »
xiii. Il sélectionne un patron de ses précédents modèles
xiv. Reprise à l'étape 3

---

##### UC-09a — Upload d'un fichier patron *(extension de UC-09)*

**Scénario principal** :
1. Le couturier choisit « Upload »
2. Il sélectionne un fichier (PDF, SVG, PNG haute définition)
3. L'application valide le format et la taille (max 20 Mo)
4. Le fichier est uploadé sur S3 et converti en SVG si nécessaire

**Scénarios alternatifs** :

**A1 — Format non pris en charge** (déclenché à l'étape 3)
En iii :
v. Le fichier est dans un format non géré (ex : .ai, .dwg)
vi. Message d'erreur listant les formats acceptés
vii. Reprise à l'étape 2

**A2 — Fichier trop volumineux** (déclenché à l'étape 3)
En iii :
viii. La taille dépasse 20 Mo
ix. L'application propose une compression automatique ou demande un fichier allégé

**A3 — Échec de conversion en SVG** (déclenché à l'étape 4)
En iv :
x. La conversion échoue (PDF complexe, image illisible)
xi. L'application propose de basculer sur l'éditeur intégré (UC-09b) en gardant le fichier original comme référence visuelle

---

##### UC-09b — Création directe dans l'éditeur *(extension de UC-09)*

**Scénario principal** :
1. Le couturier ouvre l'éditeur web intégré (Filament)
2. Il dessine les pièces (formes vectorielles, courbes de Bézier)
3. Il enregistre — le patron est généré au format SVG

**Scénarios alternatifs** :

**A1 — Sauvegarde automatique** (déclenchée toutes les 30 s pendant l'étape 2)
En ii :
iv. L'éditeur sauvegarde automatiquement le travail en cours pour prévenir toute perte

**A2 — Annulation des modifications** (déclenché à toute étape entre 1 et 3)
v. Le couturier choisit « Annuler les modifications »
vi. Confirmation demandée
vii. Retour à la dernière version sauvegardée

---

##### UC-09c — Génération du patron par IA *(extension de UC-09 — V2)*

**Note** : Reportée en V2. Génération assistée d'un patron paramétrique à partir d'une description textuelle ou d'une photo de référence.

---

#### UC-10 — Générer le patron adapté pour un client

**Description** : Calculer et afficher le patron adapté aux dimensions réelles d'un client.

**Cas inclus** : `<<include>>` UC-11.

**Préconditions** : Le client a une fiche de mesures à jour ; un modèle est sélectionné.

**Scénario principal** :
1. Le couturier ouvre une commande ou choisit « Générer un patron » depuis la fiche client
2. Il sélectionne le modèle souhaité
3. L'API calcule l'adaptation (UC-11)
4. L'application affiche le patron adapté avec les nouvelles cotations
5. Le couturier consulte / partage / exporte en PDF

**Scénarios alternatifs** :

**A1 — Mensurations client manquantes** (déclenché à l'étape 3)
En iii :
vi. Une mesure clé pour le TypeVetement du modèle est absente
vii. L'application invite le couturier à compléter via UC-02
viii. Reprise à l'étape 3 après complétion

**A2 — Modèle incompatible avec le sexe du client** (déclenché à l'étape 2)
En ii :
ix. Le modèle est typé FEMME mais le client est HOMME
x. Avertissement « Modèle non adapté au sexe du client »
xi. Le couturier peut continuer à ses risques ou changer de modèle

**A3 — Échec d'export PDF** (déclenché à l'étape 5)
En v :
xii. Le service PDF (Browsershot) renvoie une erreur
xiii. L'application propose un téléchargement direct du SVG comme repli

**A4 — Adaptation hors plages typiques** (déclenché à l'étape 4)
En iv :
xiv. L'écart client/modèle dépasse 25 % sur une mesure
xv. Un avertissement est affiché — le rendu peut nécessiter retouche manuelle

---

#### UC-11 — Calculer l'adaptation du patron

**Description** : Calculer les écarts client ↔ modèle et appliquer aux dimensions des pièces.

**Acteur principal** : Système.

**Note** : Inclus par UC-10. Exécuté par l'API Laravel.

**Scénario principal** :
1. L'API reçoit la `FicheMesure` du client + l'identifiant du modèle
2. Elle charge les mensurations modèle de référence
3. Pour chaque mesure clé, elle calcule le ratio client / modèle
4. Elle applique le ratio aux dimensions des pièces du patron (transformation affine)
5. Elle renvoie le SVG adapté avec les nouvelles cotations

**Scénarios alternatifs** :

**A1 — Mesure modèle manquante** (déclenché à l'étape 3)
En iii :
vi. Une mesure modèle nécessaire au calcul est absente
vii. L'API renvoie une erreur 422 avec la liste des mesures manquantes
viii. UC-10 A1 prend le relais

**A2 — Ratio aberrant** (déclenché à l'étape 3)
En iii :
ix. Le ratio sort de la plage [0.5 ; 1.5]
x. Un drapeau d'alerte est ajouté à la réponse
xi. Le calcul est néanmoins effectué

---

#### UC-12 — Consulter la bibliothèque globale

**Description** : Parcourir les modèles partagés et utiliser directement un modèle existant.

**Scénario principal** :
1. Le couturier accède à la bibliothèque globale
2. Il filtre par `TypeVetement`, sexe, popularité, note moyenne
3. Il consulte un modèle (photo, mensurations modèle, aperçu patron)
4. Il choisit « Importer dans mon catalogue » ou « Utiliser pour un client »

**Scénarios alternatifs** :

**A1 — Aucun résultat correspondant** (déclenché à l'étape 2)
En ii :
v. Aucun modèle ne correspond aux filtres
vi. Suggestion d'élargir les critères ou de créer un nouveau modèle (UC-07)

**A2 — Modèle retiré pendant la consultation** (déclenché à l'étape 4)
En iv :
vii. Le modèle a été retiré par l'admin (UC-18)
viii. Une notification informe le couturier
ix. Retour à la liste

**A3 — Import dans le catalogue** (déclenché à l'étape 4)
En iv :
x. Le couturier choisit « Importer »
xi. Une copie locale est créée dans son catalogue privé (statut IMPORTE)
xii. Il peut ensuite la modifier librement

---

#### UC-17 — Soumettre un modèle à la bibliothèque globale

**Description** : Proposer un modèle privé ACTIF à la bibliothèque globale (modération admin).

**Cas étend** : UC-07.

**Préconditions** : Le modèle est complet (mensurations modèle + patron annoté + photo).

**Scénario principal** :
1. Le couturier ouvre un modèle de son catalogue
2. Il choisit « Soumettre à la bibliothèque »
3. Il complète une description publique et choisit des tags
4. Il soumet
5. Le modèle passe au statut `EN_ATTENTE_MODERATION`
6. Une notification est envoyée aux admins

**Scénarios alternatifs** :

**A1 — Modèle incomplet** (déclenché à l'étape 4)
En iv :
vii. Le système détecte que des éléments obligatoires manquent (photo, annotations…)
viii. Liste des manques affichée
ix. Reprise à l'étape 1 après complétion

**A2 — Modèle déjà soumis** (déclenché à l'étape 4)
En iv :
x. Le modèle est déjà en EN_ATTENTE_MODERATION ou PUBLIE
xi. Message informatif — pas de nouvelle soumission

**A3 — Retrait d'une soumission en attente** (déclenché à l'étape 1)
En i :
xii. Le couturier ouvre une soumission en attente
xiii. Il choisit « Retirer la soumission »
xiv. Le statut revient à PRIVE

---

#### UC-20 — Créer un compte client *(nouveau V1)*

**Acteur principal** : Couturier

**Description** : À partir d'une fiche client existante, le couturier déclenche la création d'un compte d'accès. Le système génère un mot de passe initial (à changer à la première connexion) et envoie les identifiants au client par SMS et/ou email.

**Préconditions** : Le client a une fiche dans le carnet ; il dispose d'au moins un email OU un téléphone.

**Scénario principal** :
1. Le couturier ouvre la fiche d'un client sans compte
2. Il choisit « Créer un compte d'accès »
3. Le système valide l'unicité de l'email / téléphone à l'échelle de la plateforme
4. Le système génère un mot de passe temporaire
5. Les identifiants sont envoyés par SMS (téléphone) et/ou email au client
6. Un compte `Client` est créé et lié à la fiche

**Scénarios alternatifs** :

**A1 — Identifiant déjà utilisé sur la plateforme** (déclenché à l'étape 3)
En iii :
vii. Le téléphone ou l'email est déjà associé à un compte existant
viii. Le système propose : « Lier la fiche au compte existant » ou « Annuler »
ix. Si « Lier » : la fiche est rattachée au compte client existant (le client verra cette nouvelle fiche dans son espace)
x. Si « Annuler » : retour à l'étape 1

**A2 — Échec d'envoi du SMS / email** (déclenché à l'étape 5)
En v :
xi. Le SMS ou l'email n'a pas pu être envoyé (numéro invalide, quota dépassé)
xii. Le compte est tout de même créé
xiii. Le couturier reçoit une notification « Identifiants à transmettre manuellement » avec la possibilité de copier/afficher le mot de passe temporaire

**A3 — Aucun moyen de contact valide** (déclenché à l'étape 1)
En i :
xiv. La fiche n'a ni email ni téléphone valides
xv. L'option « Créer un compte » est grisée
xvi. Le couturier doit d'abord enrichir la fiche

---

### 4.2 Acteur : Administrateur

#### UC-13 — Gérer les types de vêtements

**Description** : CRUD sur le référentiel `TypeVetement` (haut, robe, pantalon, etc.).

**Scénario principal** :
1. L'admin accède au panel Filament
2. Il liste / filtre les types existants
3. Il crée, modifie ou archive un type
4. La modification est versionnée et historisée

**Scénarios alternatifs** :

**A1 — Type utilisé par des modèles existants** (déclenché à l'étape 3, opération de suppression)
En iii :
v. L'admin tente de supprimer un type encore référencé
vi. La suppression est bloquée — proposition d'archivage (statut INACTIF)

**A2 — Validation d'une suggestion couturier** (déclenché à l'étape 3)
En iii :
vii. L'admin reçoit une suggestion d'un couturier (UC-07 A2)
viii. Il valide ou rejette avec motif
ix. Une notification est renvoyée au couturier

---

#### UC-14 — Gérer les types de mensurations

**Description** : CRUD sur le référentiel `TypeMensuration` (tour de poitrine, longueur de manche, etc.).

**Scénario principal** *(similaire à UC-13)* :
1. Liste / filtre des types
2. Création / modification / archivage
3. Pour chaque type : nom, unité, plage min/max physiologique, optionnel/obligatoire selon `TypeVetement`

**Scénarios alternatifs** :

**A1 — Modification des bornes physiologiques** (déclenché à l'étape 2)
En ii :
iv. L'admin modifie les plages min/max
v. Le système avertit si des fiches existantes deviendraient hors plage
vi. Confirmation explicite requise

**A2 — Type lié à une règle de proportion active** (déclenché à l'étape 2, opération de suppression)
En ii :
vii. L'admin tente de supprimer un type lié à une règle de proportion active
viii. Suppression bloquée — proposition d'archivage uniquement

---

#### UC-15 — Gérer les règles de proportion

**Description** : CRUD sur le référentiel `RegleProportion` (versionnées). Une règle = un ratio entre deux `TypeMensuration` pour un `TypeVetement` et un sexe donnés.

**Scénario principal** :
1. L'admin liste les règles actives
2. Il crée une nouvelle règle : `TypeVetement` + `Sexe` + `TypeMensuration` source + `TypeMensuration` cible + ratio + bornes
3. Il valide → la règle entre en version `v(n+1)`
4. L'ancienne version est marquée `INACTIVE` mais conservée

**Scénarios alternatifs** :

**A1 — Conflit de scope entre règles** (déclenché à l'étape 3)
En iii :
v. Une règle existante couvre exactement le même scope
vi. L'admin doit choisir : remplacer (l'ancienne devient inactive) ou créer une règle plus spécifique

**A2 — Test à blanc** (déclenché à l'étape 1)
En i :
vii. L'admin choisit « Simuler » sur une fiche réelle
viii. Il visualise l'impact de la règle sans la persister
ix. Aide à la décision avant publication

**A3 — Désactivation d'une règle utilisée** (déclenché à l'étape 3)
En iii :
x. L'admin désactive une règle utilisée par des fiches récentes
xi. Avertissement : « N fiches reposent sur cette règle. Les nouveaux calculs échoueront. »
xii. Confirmation explicite requise

---

#### UC-16 — Gérer les comptes couturiers

**Description** : Créer, modifier, suspendre, désactiver les comptes des couturiers.

**Scénario principal** :
1. L'admin liste les couturiers
2. Il consulte un profil (informations, modèles publiés, signalements éventuels)
3. Il modifie le statut : `ACTIF` / `SUSPENDU` / `INACTIF`
4. Il valide — un email automatique est envoyé au couturier concerné

**Scénarios alternatifs** :

**A1 — Suspension temporaire avec motif** (déclenché à l'étape 3)
En iii :
v. L'admin choisit « Suspendre »
vi. Il saisit un motif et une durée
vii. Le compte est suspendu — connexion bloquée, modèles publiés masqués
viii. Réactivation automatique à la fin de la durée

**A2 — Suppression demandée par le couturier** (déclenché à l'étape 1)
En i :
ix. L'admin reçoit une demande de fermeture de compte
x. Il anonymise les données personnelles (RGPD-like) tout en conservant les modèles publiés à la bibliothèque
xi. Le compte passe au statut SUPPRIME

**A3 — Création manuelle d'un compte couturier** (déclenché à l'étape 1)
En i :
xii. L'admin saisit les informations d'un nouveau couturier
xiii. Identifiants envoyés par email
xiv. Le compte est EN_ATTENTE_VERIFICATION jusqu'à la première connexion

---

#### UC-18 — Modérer la bibliothèque globale

**Description** : Examiner les modèles soumis (UC-17) et décider de leur publication.

**Scénario principal** :
1. L'admin accède à la file de modération (modèles `EN_ATTENTE_MODERATION`)
2. Il consulte un modèle : photos, patron, métadonnées, profil du couturier soumissionnaire
3. Il valide ou rejette avec motif
4. Si validé : le modèle passe au statut `PUBLIE` dans la bibliothèque globale
5. Une notification est envoyée au couturier

**Scénarios alternatifs** :

**A1 — Rejet avec motif détaillé** (déclenché à l'étape 3)
En iii :
vi. L'admin choisit « Rejeter »
vii. Il sélectionne un motif standardisé (ex : qualité photo, patron incomplet, contenu inapproprié)
viii. Il ajoute un commentaire optionnel
ix. Le modèle revient au statut PRIVE avec l'historique du rejet

**A2 — Demande de corrections** (déclenché à l'étape 3)
En iii :
x. L'admin choisit « Demander des corrections »
xi. Le modèle passe au statut CORRECTIONS_DEMANDEES
xii. Le couturier voit la liste des corrections à apporter avant resoumission

**A3 — Retrait d'un modèle déjà publié** (déclenché à l'étape 1)
En i :
xiii. L'admin reçoit un signalement sur un modèle publié
xiv. Il consulte le modèle et les motifs du signalement
xv. Il décide : maintien ou retrait
xvi. Si retrait : statut RETIRE, notification au couturier, le modèle disparaît de la bibliothèque (conservé en archive)

**A4 — Modération en lot** (déclenché à l'étape 2)
En ii :
xvii. L'admin sélectionne plusieurs modèles
xviii. Action groupée : valider / rejeter avec motif unique
xix. Notifications envoyées en masse

---

### 4.3 Acteur : Client *(nouveau dans V1)*

#### UC-19 — S'inscrire (compte client)

**Acteur principal** : Client

**Description** : Création autonome d'un compte client depuis l'application (auto-inscription, en alternative à UC-20 piloté par le couturier).

**Scénario principal** :
1. Le visiteur ouvre l'application et choisit « Créer un compte »
2. Il saisit nom, prénom, téléphone (obligatoire), email (optionnel), date de naissance (optionnelle), mot de passe
3. Le système valide l'unicité de l'identifiant (téléphone et/ou email)
4. Le système envoie un code de vérification par SMS
5. Le client confirme le code → le compte est activé

**Scénarios alternatifs** :

**A1 — Téléphone déjà associé à un compte** (déclenché à l'étape 3)
En iii :
vi. Le système détecte un compte existant avec ce téléphone
vii. Il propose : « Se connecter » (UC-00) ou « Récupérer le mot de passe » (UC-00 A4)
viii. Inscription annulée

**A2 — Code SMS non reçu** (déclenché à l'étape 5)
En v :
ix. Le client signale ne pas avoir reçu le code
x. Bouton « Renvoyer le code » disponible après 60 secondes
xi. Après 3 renvois infructueux : proposition d'envoi par email (si email renseigné)

**A3 — Mot de passe trop faible** (déclenché à l'étape 3)
En iii :
xii. Le mot de passe ne respecte pas les règles (8+ caractères, mélange casse + chiffre)
xiii. Indicateur de force visuel + suggestions
xiv. Reprise à l'étape 2

**A4 — Format de téléphone invalide** (déclenché à l'étape 3)
En iii :
xv. Le numéro saisi n'est pas conforme au format E.164
xvi. Aide visuelle : « Format attendu : +229 XX XX XX XX »
xvii. Reprise à l'étape 2

**A5 — Code expiré** (déclenché à l'étape 5)
En v :
xviii. Le code saisi est expiré (validité 10 min)
xix. Le client peut redemander un code
xx. Reprise à l'étape 4

---

#### UC-21 — Consulter la liste des couturiers

**Description** : Le client parcourt l'annuaire des couturiers de la plateforme.

**Scénario principal** :
1. Le client ouvre l'onglet « Couturiers »
2. L'application charge la liste paginée (par défaut : tri par proximité géographique)
3. Le client filtre : ville, spécialité, note moyenne minimale, sexe servi
4. Il consulte une fiche couturier (photo, bio, ville, spécialités, note moyenne, nombre d'avis)

**Scénarios alternatifs** :

**A1 — Géolocalisation refusée** (déclenché à l'étape 2)
En ii :
v. Le client n'a pas accordé la permission GPS
vi. La liste est triée par défaut sur la ville déclarée dans son profil
vii. Sinon : tri par note moyenne globale

**A2 — Aucun couturier dans la zone** (déclenché à l'étape 3)
En iii :
viii. Aucun couturier ne correspond aux filtres
ix. Suggestion : élargir le rayon ou supprimer un filtre

**A3 — Couturier suspendu** (déclenché à l'étape 4)
En iv :
x. Le couturier consulté est passé en statut SUSPENDU
xi. La fiche affiche : « Profil temporairement indisponible »
xii. Retour à la liste

---

#### UC-22 — Consulter le catalogue d'un couturier

**Description** : Le client visualise les modèles publics qu'un couturier propose, avec photos et descriptions.

**Scénario principal** :
1. Le client ouvre la fiche d'un couturier
2. Il accède à l'onglet « Catalogue »
3. L'application affiche les modèles publics du couturier (ses propres + ceux de la bibliothèque qu'il met en avant)
4. Le client peut filtrer par `TypeVetement`, prix indicatif

**Scénarios alternatifs** :

**A1 — Catalogue vide** (déclenché à l'étape 3)
En iii :
v. Le couturier n'a pas encore publié de modèle
vi. Message « Ce couturier n'a pas encore publié de modèles »
vii. Bouton « Le contacter » disponible (UC-23)

**A2 — Modèle retiré pendant consultation** (déclenché à l'étape 4)
En iv :
viii. Le modèle consulté a été retiré
ix. Notification + retour au catalogue

**A3 — Mise en favori** (déclenché à l'étape 4)
En iv :
x. Le client ajoute un modèle à ses favoris
xi. Le modèle apparaît dans son onglet « Favoris »

---

#### UC-23 — Contacter un couturier

**Description** : Initier un échange avec un couturier (formulaire de contact, demande de devis, message court).

**Scénario principal** :
1. Le client clique sur « Contacter » depuis la fiche couturier ou un modèle
2. Il choisit le motif (devis, question, prise de rendez-vous)
3. Il rédige un message court (max 500 caractères) et joint éventuellement un modèle de référence
4. Il envoie
5. Le couturier reçoit la notification dans son espace + push FCM
6. Le client voit l'échange dans son onglet « Mes contacts »

**Scénarios alternatifs** :

**A1 — Couturier non joignable** (déclenché à l'étape 5)
En v :
vii. Le couturier est SUSPENDU ou a désactivé les contacts
viii. Message « Ce couturier n'accepte pas de nouveaux contacts pour le moment »

**A2 — Quota anti-spam atteint** (déclenché à l'étape 4)
En iv :
ix. Le client a déjà envoyé 5 messages dans les dernières 24 h sans réponse
x. Limitation appliquée — message « Patientez la réponse de vos contacts précédents »

**A3 — Pièce jointe invalide** (déclenché à l'étape 4)
En iv :
xi. La pièce jointe dépasse 5 Mo ou n'est pas au format autorisé
xii. Message d'erreur — reprise à l'étape 3

---

#### UC-24 — Noter un couturier

**Description** : Après une commande terminée, le client laisse une note (1 à 5 étoiles) et un commentaire optionnel.

**Préconditions** : Le client a au moins une commande au statut `TERMINE` auprès du couturier noté.

**Scénario principal** :
1. Le client reçoit une notification après livraison d'une commande
2. Il ouvre la commande et choisit « Noter »
3. Il attribue 1 à 5 étoiles
4. Il rédige un commentaire optionnel
5. Il valide → la note est publiée et la moyenne du couturier mise à jour

**Scénarios alternatifs** :

**A1 — Note déjà laissée** (déclenché à l'étape 2)
En ii :
vi. Le client a déjà noté cette commande
vii. L'application propose de modifier la note (avec historique conservé)

**A2 — Modération automatique du commentaire** (déclenché à l'étape 5)
En v :
viii. Un filtre détecte un contenu potentiellement inapproprié
ix. Le commentaire passe au statut EN_ATTENTE_MODERATION
x. La note est publiée mais le commentaire est masqué jusqu'à validation admin

**A3 — Signalement par le couturier** (déclenché à l'étape 5)
En v :
xi. Le couturier signale une note jugée injuste
xii. La note reste affichée mais marquée « Signalée »
xiii. Un admin l'examine (UC-18 A3 étendu)

**A4 — Note hors délai** (déclenché à l'étape 1)
En i :
xiv. La commande est terminée depuis plus de 60 jours
xv. La possibilité de noter est désactivée

---

### 4.4 Acteur : Système

Les UC suivants sont déclenchés par le Système :
- **UC-04** — Traiter les images par IA (calcul on-device + agrégation API)
- **UC-05** — Déduire les mensurations secondaires (API Laravel)
- **UC-11** — Calculer l'adaptation du patron (API Laravel)

---

## 5. Relations entre Cas d'Utilisation

### Relations `<<include>>`

| Cas d'utilisation | Inclut |
|-------------------|--------|
| UC-02 Enregistrer les mesures | UC-03, UC-04, UC-05 |
| UC-07 Initialiser un modèle | UC-08, UC-09 |
| UC-10 Générer le patron adapté | UC-11 |

### Relations `<<extend>>`

| Extension | Étend | Condition |
|-----------|-------|-----------|
| UC-09a Upload d'un fichier patron | UC-09 | Méthode "Upload" |
| UC-09b Création directe sur la plateforme | UC-09 | Méthode "Dessiner" |
| UC-09c Génération par IA | UC-09 | Méthode "Générer par IA" (V2) |
| UC-17 Soumettre à la bibliothèque | UC-07 | Le couturier choisit de partager son modèle |

### Suppression de la généralisation entre acteurs

> Dans la version 1.0, `Utilisateur` était modélisé comme un acteur abstrait parent dont héritaient `Couturier` et `Administrateur`. **Cette relation a été supprimée** pour clarifier le modèle et permettre l'introduction de l'acteur `Client` au même niveau. Tous les acteurs humains sont désormais reliés directement à UC-00.

---

## 6. Tableau récapitulatif des cas d'utilisation (25 UCs)

| ID | Libellé | Acteur principal | Priorité MVP |
|----|---------|-----------------|--------------|
| UC-00 | S'authentifier | Client / Couturier / Administrateur | Indispensable |
| UC-01 | Gérer son carnet clients | Couturier | Indispensable |
| UC-02 | Enregistrer les mesures d'un client | Couturier | Indispensable |
| UC-03 | Guider la prise de photos (Flutter) | Couturier | Indispensable |
| UC-04 | Traiter les images par IA | Système | Indispensable |
| UC-05 | Déduire les mensurations secondaires | Système | Indispensable |
| UC-06 | Consulter le dossier d'un client | Couturier | Indispensable |
| UC-07 | Initialiser un modèle de vêtement | Couturier | Indispensable |
| UC-08 | Saisir les mensurations modèle (Étape 1) | Couturier | Indispensable |
| UC-09 | Enregistrer le patron (Étape 2) | Couturier | Indispensable |
| UC-09a | Upload d'un fichier patron | Couturier | Indispensable |
| UC-09b | Création directe du patron | Couturier | Souhaitable |
| UC-09c | Génération du patron par IA | Couturier | Futur (V2) |
| UC-10 | Générer le patron adapté | Couturier | Indispensable |
| UC-11 | Calculer l'adaptation du patron | Système | Indispensable |
| UC-12 | Consulter la bibliothèque globale | Couturier | Indispensable |
| UC-13 | Gérer les types de vêtements | Administrateur | Indispensable |
| UC-14 | Gérer les types de mensurations | Administrateur | Indispensable |
| UC-15 | Gérer les règles de proportion | Administrateur | Indispensable |
| UC-16 | Gérer les comptes couturiers | Administrateur | Indispensable |
| UC-17 | Soumettre un modèle à la bibliothèque | Couturier | Indispensable |
| UC-18 | Modérer la bibliothèque globale | Administrateur | Indispensable |
| **UC-19** | **S'inscrire (compte client)** | **Client** | **Indispensable** |
| **UC-20** | **Créer un compte client** | **Couturier** | **Indispensable** |
| **UC-21** | **Consulter la liste des couturiers** | **Client** | **Indispensable** |
| **UC-22** | **Consulter le catalogue d'un couturier** | **Client** | **Indispensable** |
| **UC-23** | **Contacter un couturier** | **Client** | **Souhaitable** |
| **UC-24** | **Noter un couturier** | **Client** | **Souhaitable** |
