# Audit Relations et Regles de Gestion

## Portee retenue

Les remarques projet les plus recentes recentrent le produit sur :

- le couturier comme acteur principal
- la prise de mesure assistee
- le carnet client prive au couturier
- la bibliotheque de modeles et patrons
- les formes de decoupe et materiaux

Le client n'est plus, a ce stade, un utilisateur actif de la plateforme. Il est traite comme une entite metier dans le carnet du couturier.

## Relations verifiees

### Noyau utilisateurs

- `users` 1,N `clients`
  - FK: `clients.prestataire_id -> users.id`
  - sens metier: un couturier gere plusieurs clients

- `users` 1,N `model_vetements`
  - FK: `model_vetements.prestataire_id -> users.id`
  - sens metier: un couturier cree ou possede plusieurs modeles

- `users` 1,N `fiche_mesures`
  - FK: `fiche_mesures.prestataire_id -> users.id`
  - sens metier: une fiche de mesures est produite par un couturier

- `users` 1,N `social_accounts`
  - FK: `social_accounts.user_id -> users.id`
  - sens metier: un compte plateforme peut etre relie a un ou plusieurs fournisseurs sociaux

### Carnet client et mesures

- `clients` 1,N `fiche_mesures`
  - FK: `fiche_mesures.client_id -> clients.id`
  - sens metier: un client peut avoir plusieurs sessions de mesures

- `fiche_mesures` 1,N `mesures`
  - FK: `mesures.fiche_mesure_id -> fiche_mesures.id`
  - sens metier: une fiche de mesures contient plusieurs lignes de mesure

- `type_mesures` 1,N `mesures`
  - FK: `mesures.type_mesure_id -> type_mesures.id`
  - sens metier: chaque valeur saisie ou calculee reference un type de mesure

### Modeles et patrons

- `type_vetements` 1,N `model_vetements`
  - FK: `model_vetements.type_vetement_id -> type_vetements.id`

- `model_vetements` 1,N `mesure_modeles`
  - FK: `mesure_modeles.model_vetement_id -> model_vetements.id`

- `type_mesures` 1,N `mesure_modeles`
  - FK: `mesure_modeles.type_mesure_id -> type_mesures.id`

- `model_vetements` 1,N `patrons`
  - FK: `patrons.model_vetement_id -> model_vetements.id`

- `patrons` 1,N `pieces_patron`
  - FK: `pieces_patron.patron_id -> patrons.id`

- `pieces_patron` 1,N `annotation_patrons`
  - FK: `annotation_patrons.piece_patron_id -> pieces_patron.id`

- `type_mesures` 0,N `annotation_patrons`
  - FK: `annotation_patrons.type_mesure_id -> type_mesures.id`

### Formes de decoupe et materiaux

- `formes_decoupe` 1,N `materiaux`
  - FK: `materiaux.forme_decoupe_id -> formes_decoupe.id`
  - sens metier: un materiau peut pointer vers une forme de reference

- `pieces_patron` 1,N `dispositions_piece_patron`
  - FK: `dispositions_piece_patron.piece_patron_id -> pieces_patron.id`

- `formes_decoupe` 1,N `dispositions_piece_patron`
  - FK: `dispositions_piece_patron.forme_decoupe_id -> formes_decoupe.id`

- `materiaux` 0,N `dispositions_piece_patron`
  - FK: `dispositions_piece_patron.materiau_id -> materiaux.id`

### Commandes

- `clients` 1,N `commande_vetements`
  - FK: `commande_vetements.client_id -> clients.id`

- `model_vetements` 1,N `commande_vetements`
  - FK: `commande_vetements.model_vetement_id -> model_vetements.id`

- `fiche_mesures` 0,N `commande_vetements`
  - FK: `commande_vetements.fiche_mesure_id -> fiche_mesures.id`

## Regles de gestion ressorties

### Authentification et comptes

- la connexion reste obligatoire avant acces aux ecrans metier
- un couturier possede un compte `users`
- la connexion sociale doit rattacher ou creer un utilisateur dans `users`
- un compte social doit pouvoir etre memorise dans `social_accounts`

### Carnet client

- un client appartient a un seul couturier dans la version actuelle
- un client peut exister sans commande
- un client peut avoir plusieurs fiches de mesures dans le temps
- l'historique des mesures doit etre conserve

### Prise de mesures

- une fiche de mesures appartient a un client et a un couturier
- une fiche de mesures peut etre `brouillon`, `valide` ou `archive`
- une mesure elementaire porte une source: `ia`, `manuelle`, `corrigee`
- une mesure IA peut exposer un score de confiance
- les mesures secondaires doivent pouvoir etre deduites a partir de regles de proportion

### Modeles et patrons

- un modele de vetement appartient a un type de vetement
- un patron appartient a un modele
- un patron est compose de plusieurs pieces
- une piece peut recevoir plusieurs annotations de cotation
- une piece peut recevoir plusieurs dispositions de formes de decoupe
- une disposition peut porter un materiau associe

### Bibliotheque metier

- les formes de decoupe peuvent etre globales ou internes
- les materiaux peuvent etre globaux ou internes
- les modeles/patrons peuvent alimenter une bibliotheque partagee a terme

## Incoherences detectees avant correction

- plusieurs modeles pointaient vers des noms de tables inexistants:
  - `types_vetements` au lieu de `type_vetements`
  - `types_mensurations` au lieu de `type_mesures`
  - `modeles_vetements` au lieu de `model_vetements`
  - `pieces_patrons` au lieu de `pieces_patron`
  - `fiches_mesures` au lieu de `fiche_mesures`
  - `mensurations_modeles` au lieu de `mesure_modeles`
  - `commandes_vetements` au lieu de `commande_vetements`

- plusieurs FK de modeles ne correspondaient pas aux migrations:
  - `modele_vetement_id` vs `model_vetement_id`
  - `type_mensuration_id` vs `type_mesure_id`
  - `createur_id` vs `prestataire_id`

- des modeles existaient sans table migree correspondante:
  - `FicheClient`
  - `Paiement`
  - `NoteCouturier`

- une table cle du metier etait documentee mais absente des migrations:
  - `regles_proportions`

## Decisions prises

- alignement des modeles Laravel sur les vraies tables creees par les migrations
- ajout des modeles manquants pour:
  - `clients`
  - `formes_decoupe`
  - `materiaux`
  - `dispositions_piece_patron`
- ajout de la migration `regles_proportions`
- conservation de `FicheClient`, `Paiement` et `NoteCouturier` comme elements legacy a requalifier avant usage

## Points a trancher ensuite

- faut-il supprimer completement `FicheClient` du code si le carnet client repose desormais sur `clients` ?
- faut-il migrer plus tard `paiements` et `notes_couturiers`, ou les sortir du perimetre V1 ?
- faut-il renommer certains fichiers PHP pour que les noms de classes suivent plus strictement les tables actuelles (`TypeMesure`, `MesureModele`, `Mesure`) ?
