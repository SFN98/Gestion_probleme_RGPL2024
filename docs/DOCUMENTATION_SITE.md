# Documentation du site — Plateforme de gestion des problèmes RGPL 2024

## 1. Objectif du site

La plateforme permet de :

- **Centraliser** les problèmes liés au fonctionnement d’une application (terrain / utilisateurs).
- **Mettre à disposition** des solutions pour les utilisateurs (FAQ, statuts de résolution).

Elle s’adresse d’une part aux **agents** (ou utilisateurs) qui soumettent les problèmes, et d’autre part aux **administrateurs** (ou contrôleurs) qui traitent les tickets et alimentent les réponses.

---

## 2. Utilisateurs et rôles

Deux rôles suffisent :

| Rôle | Appellation alternative | Description | Parcours principal |
|------|-------------------------|-------------|---------------------|
| **Agent** | User | Personne qui soumet un problème : agent de terrain, chef d’équipe, superviseur de terrain. | Formulaire de soumission de problème (page dédiée). |
| **Administrateur** | Contrôleur | Personne qui traite les tickets sur la plateforme : consultation, réaffectation, résolution, gestion de la FAQ. | Connexion → Dashboard. |

- Les **agents** n’ont pas besoin de se connecter pour soumettre un problème (sauf si une authentification légère est décidée plus tard).
- Les **administrateurs** (ou contrôleurs) se connectent pour accéder au dashboard et gérer les tickets et la FAQ.

---

## 3. Fonctionnement côté agent

Un agent rencontre un problème sur l’application. Il fait une ou plusieurs **captures** (écran, photos, vidéos) pour illustrer le cas.

Il se rend sur le site et a **trois options** :

| Option | Action | Bénéfice |
|--------|--------|----------|
| **1. Rechercher une solution** | Il saisit des **mots clés** liés à d’anciennes demandes déjà traitées. | Il peut trouver la **solution** à son problème actuel si un autre agent avait déjà eu le même cas et que les contrôleurs l’avaient corrigé (réponses / FAQ issues des tickets résolus). |
| **2. Soumettre un nouveau problème** | Il remplit le **formulaire de soumission** et décrit ce qu’il a constaté. Il envoie le formulaire (avec description et pièces jointes). | Il reçoit un **numéro de ticket** associé à sa demande. Ce ticket lui permet ensuite de suivre l’état de traitement (option 3). Côté plateforme, le ticket permet d’**identifier** le problème pour, à l’avenir, ne plus avoir à retraiter le même cas (réutilisation des solutions, regroupement, FAQ). |
| **3. Consulter son ticket** | Il saisit son **numéro de ticket**. | Il voit si son problème a déjà été **traité** par les contrôleurs après sa soumission (statut, réponse éventuelle). |

En résumé : l’agent peut **chercher une solution** avant de soumettre, **soumettre** et obtenir un ticket pour suivi, ou **consulter** l’état de son ticket déjà soumis.

---

## 4. Fonctionnement côté administrateur — Étapes de résolution d’un problème

Côté plateforme, les **contrôleurs** sont connectés. Ils voient les tickets soumis par les agents, les prennent en charge et les font passer à la résolution. Voici les étapes prévues.

1. **Connexion**  
   Le contrôleur se connecte (identifiant / mot de passe) et accède au dashboard.

2. **Liste des tickets**  
   Les tickets sont affichés **par date** (du **plus ancien au plus récent**). Le tableau indique ID, titre, province, priorité, assigné, statut. Les **KPI** (Total, Ouverts, En cours de traitement, Résolus) et les **filtres** (recherche, province, type, statut, priorité) permettent de cibler les demandes.

3. **Prise en charge d’un ticket (« prise »)**  
   Le contrôleur **sélectionne / prend** un ticket (bouton ou action « Prendre le ticket »). Dès qu’il le prend :
   - le statut du ticket passe à **« En cours de traitement »** ;
   - le ticket est associé au contrôleur connecté : l’**assigné** (ou « agent en charge ») est renseigné (nom ou identifiant du contrôleur).
   Ainsi, on sait qui traite quoi et les autres contrôleurs voient que le ticket est déjà pris.

4. **Prise de connaissance du problème**  
   Il ouvre les **détails complets** du ticket (bouton « Voir ») : description, pièces jointes (captures, photos, vidéos), coordonnées du soumetteur, province, type, priorité. Il s’appuie sur ces éléments pour comprendre le problème et le traiter (en interne ou avec l’équipe technique).

5. **Réaffectation (optionnelle)**  
   Si le traitement doit être confié à un autre contrôleur, il utilise **Réaffecter** : il saisit le nom du nouvel assigné. Le ticket reste en « En cours de traitement » et est associé à cette autre personne.

6. **Traitement du problème**  
   Le traitement lui-même se fait **hors plateforme** (diagnostic, correction, procédure, etc.). La plateforme sert à suivre qui traite quoi et à quel stade en est le ticket.

7. **Marquer comme résolu**  
   Une fois le problème réglé (ou une solution / contournement identifié), le contrôleur clique sur **Marquer résolu** et renseigne le champ **« Réponse / solution »** : texte explicatif et/ou lien vers un document PDF. Le statut du ticket devient **Résolu**. L’agent voit ce statut et la **réponse / solution** lorsqu’il consulte son ticket par numéro (écran « Consulter mon ticket »). Aucune notification (e‑mail, SMS) n’est envoyée à l’agent ; il consulte de lui‑même.

7bis. **Libérer le ticket (optionnel)**  
   Si le contrôleur qui a pris le ticket ne peut pas le traiter (absence, charge de travail), il peut **libérer le ticket** : statut repasse à **« Ouvert »**, l’assigné est vidé. Un autre contrôleur pourra alors le prendre.

8. **Alimenter la base de solutions (recommandé)**  
   - **FAQ** : ajouter ou modifier une entrée FAQ (titre/question, solution ou réponse, statut). Ces entrées sont indexées et consultables par les agents (recherche par mots clés).
   - **Bibliothèque FAQ / documents PDF** : ajouter des **étapes de résolution** sous forme de **documents PDF** téléchargeables. Ces PDF constituent une bibliothèque de procédures ou de guides que les **utilisateurs (agents)** peuvent **télécharger** depuis la plateforme (par exemple depuis la recherche par mots clés ou une page dédiée « Documentation » ou « Bibliothèque FAQ »).

**Résumé du flux côté administrateur :** Connexion → Liste des tickets (par date, plus ancien → plus récent) → **Prise du ticket** (statut « En cours de traitement », assigné = contrôleur) → Lecture du détail → Réaffectation éventuelle ou **Libérer le ticket** → Traitement hors plateforme → Marquer résolu (avec Réponse / solution) → Optionnel : FAQ + bibliothèque PDF (étapes de résolution téléchargeables par les agents).

---

## 5. Écrans et parcours

Les écrans décrits ci-dessous seront recréés en modules PHP. Côté agent : page d’accueil unique puis recherche, soumission, consultation du ticket. Côté administrateur : connexion et dashboard.

### 5.0 Page d’accueil (agent)

- **Objectif** : Point d’entrée unique pour l’agent, sans connexion, avec les trois options clairement présentées.
- **Contenu principal** :
  - **Trois entrées** bien visibles :
    1. **Rechercher une solution** — accès à la recherche par mots clés (tickets résolus, FAQ, PDF).
    2. **Soumettre un problème** — accès au formulaire de soumission.
    3. **Consulter mon ticket** — accès à la consultation par numéro de ticket.
  - Aucune authentification requise ; parcours simple et direct.
- **Comportement prévu** : l’agent qui arrive sur le site voit cette page en premier et choisit l’une des trois options.

### 5.1 Recherche par mots clés (agent)

- **Objectif** : Permettre à l’agent de chercher une solution avant de soumettre un problème, en s’appuyant **uniquement sur les problèmes déjà résolus** (tickets résolus), la FAQ et la bibliothèque PDF.
- **Contenu principal** :
  - Champ de **recherche par mots clés** (rattachés aux tickets résolus, à la FAQ et aux documents de la bibliothèque FAQ).
  - Affichage des **résultats** : **tickets résolus** correspondants (titre, description, solution affichée ou lien) ; entrées **FAQ** (solution, statut) ; **documents PDF** (étapes de résolution, procédures) avec lien de **téléchargement**.
- **Comportement prévu** : la recherche inclut **uniquement les tickets résolus** (plus la FAQ et la bibliothèque PDF). Titres, descriptions et mots clés sont indexés ; affichage des solutions et des PDF téléchargeables.

### 5.2 Soumission de problème (agent)

- **Objectif** : Permettre à un agent de déclarer un problème (ce qu’il a constaté) et d’obtenir un numéro de ticket pour le suivi.
- **Contenu principal** :
  - **En-tête** : titre type « Soumettre un problème ».
  - **Formulaire « Nouveau problème »** :
    - Nom du soumetteur * (texte)
    - Login * (texte) — la province est déduite automatiquement (règles métier ci-dessous).
    - Téléphone * (tel)
    - Type de problème * (liste fixe — voir section 6).
    - Titre du problème * (texte)
    - **Description détaillée** * (textarea) — ce qu’il a constaté.
    - **Pièces jointes** : captures d’écran, photos, vidéos (formats et taille max à définir, ex. images jpg/png, vidéos, max 5 Mo par fichier, multiple).
  - **Province détectée** : affichée en lecture seule à partir du login.
  - **Boutons** : Annuler, Soumettre le problème.
  - Liens ou rappels vers : recherche par mots clés, consultation du ticket.
  - Optionnel : bloc « Problèmes déjà déclarés dans votre province », bloc « FAQ – Solutions rapides ».
- **Comportement prévu** : à la soumission, création (ou regroupement) d’un ticket ; **affichage du numéro de ticket** à l’agent ; message de succès ; formulaire réinitialisé. Le ticket permet à l’agent de consulter l’état de traitement (écran 4.3) et à la plateforme d’identifier le problème pour la suite.

### 5.3 Consultation du ticket (agent)

- **Objectif** : Permettre à l’agent de voir si son problème a déjà été traité après sa soumission et de consulter la **réponse / solution** du contrôleur.
- **Contenu principal** :
  - Champ de saisie du **numéro de ticket** (reçu après soumission).
  - Bouton ou action « Vérifier » / « Consulter ».
  - **Résultat** : affichage du statut du ticket (Ouvert, En cours de traitement, Résolu) et, lorsque le ticket est résolu, du champ **« Réponse / solution »** (texte et/ou lien vers un PDF) renseigné par le contrôleur.
- **Comportement prévu** : recherche du ticket par **numéro de ticket seul** (aucune vérification supplémentaire type téléphone ou e‑mail). Le contenu n’est pas considéré comme confidentiel : un même problème peut concerner plusieurs agents. Affichage sans connexion.

### 5.4 Connexion (accès dashboard — administrateur)

- **Objectif** : Authentifier un administrateur (ou contrôleur) pour accès au dashboard.
- **Contenu principal** :
  - Fond animé (dégradé discret).
  - **En-tête** : logo, titre « Connexion Dashboard », sous-titre « Accès administrateur sécurisé ».
  - **Zone d’alerte** : message d’erreur ou de succès (identifiant/mot de passe incorrect, bienvenue, etc.).
  - **Formulaire** :
    - Identifiant (texte, autocomplete username)
    - Mot de passe (password, autocomplete current-password)
    - « Se souvenir de moi » (checkbox)
    - Lien « Mot de passe oublié » (comportement à définir côté backend).
  - **Bouton** : « Se connecter » (avec état chargement pendant la requête).
  - **Bloc démo** : identifiants de démonstration (ex. admin / admin123).
  - **Pied de page** : lien aide (email support), lien « Retour à la page de soumission » (vers la page de soumission de problème).
- **Comportement prévu** : après authentification réussie, redirection vers le dashboard ; session et option « Se souvenir de moi » gérées côté PHP.

### 5.5 Dashboard administrateur

- **Objectif** : Consulter et gérer les tickets (avec prise en charge), les statistiques, la FAQ et la **bibliothèque FAQ (PDF)**.
- **Contenu principal** :
  - **En-tête** : titre « Dashboard Administrateur », version app (ex. v2.3.1), bouton Déconnexion.
  - **KPI** : Total problèmes, Ouverts, En cours de traitement, Résolus (valeurs dynamiques).
  - **Liste des tickets** : affichée **par date (du plus ancien au plus récent)**. Colonnes : ID, Titre du problème, Province, Occurrences, Priorité, Assigné, Statut, Actions.
  - **Actions par ticket** :
    - **Prendre le ticket** : pour les tickets ouverts, le contrôleur connecté peut « prendre » le ticket → statut « En cours de traitement », assigné = ce contrôleur.
    - **Libérer le ticket** : pour un ticket qu’il a pris, le contrôleur peut le libérer → statut repasse à « Ouvert », assigné vidé (un autre contrôleur pourra le prendre).
    - Voir (détail), Réaffecter (nom du nouvel assigné), Marquer résolu (avec saisie de la Réponse / solution, si non résolu).
  - **Filtres et recherche** : champ recherche (ID, login, téléphone, description), filtres (province, type), boutons rapides (Tous, Ouverts, En cours, Résolus, Priorité 1), bouton « Réinitialiser données » (à sécuriser côté backend).
  - **Panneau latéral** :
    - Détails du ticket sélectionné (aperçu).
    - Statistiques par province.
    - **Gestion FAQ** : liste + Ajouter ; pour chaque entrée : modifier, supprimer.
    - **Gestion bibliothèque FAQ (PDF)** : liste des documents PDF (étapes de résolution) ; ajout (upload PDF, titre, description, mots clés), modifier, supprimer. Ces documents sont téléchargeables par les agents.
  - **Modales** :
    - Détails complets du ticket (tous les champs + images).
    - Réaffectation (saisie du nouvel assigné).
    - Marquer résolu (saisie du champ **Réponse / solution** : texte et/ou lien vers un PDF).
    - Ajout / édition FAQ (titre/question, solution/réponse, statut).
    - Ajout / édition document PDF (titre, fichier, description, mots clés).
- **Comportement prévu** : données chargées depuis l’API/backend ; prise de ticket et mises à jour reflétées en temps réel ou après rechargement selon implémentation.

---

## 6. Modèle de données

### 6.1 Ticket (problème déclaré)

| Champ | Type | Description |
|-------|------|-------------|
| id | string | Identifiant unique (ex. Txxxxxx ou format métier). |
| title / pbTitle | string | Titre du problème. |
| reporterName | string | Nom du soumetteur. |
| login | string | Login du soumetteur (servira à la détection de province). |
| phone | string | Téléphone. |
| type | string | Type de problème (liste fixe). |
| desc | string | Description détaillée. |
| images | array | Pièces jointes : captures d’écran, photos, vidéos (URLs ou stockage à définir en backend). |
| occurrences | int | Nombre de déclarations similaires regroupées. |
| province | string | Province (dérivée du login ou saisie). |
| priority | string | P1 | P2 | P3 | P4 (calculée à partir du type). |
| status | string | open \| progress \| resolved. « progress » = En cours de traitement (ticket pris par un contrôleur). Libération → open. |
| assignee | string | Contrôleur qui a pris le ticket ou à qui il a été réaffecté (vidé si le ticket est libéré). |
| response | string | **Réponse / solution** : texte et/ou lien vers un PDF, renseigné par le contrôleur lors du « Marquer résolu ». Affiché à l’agent sur l’écran « Consulter mon ticket » lorsque le ticket est résolu. |
| firstReportedAt | datetime | Date de première déclaration. |
| lastUpdatedAt | datetime | Dernière mise à jour. |

### 6.2 FAQ (solution / entrée d’aide)

| Champ | Type | Description |
|-------|------|-------------|
| id | string | Identifiant unique. |
| title | string | Titre / question. Peut être indexé pour la recherche par mots clés (option agent 1). |
| solution | string | Réponse ou solution. |
| status | string | resolved | workaround | known. |
| createdAt | datetime | Date de création. |
| updatedAt | datetime | Date de modification (optionnel). |

### 6.3 Bibliothèque FAQ (documents PDF)

Ensemble de **documents PDF** mis à disposition par les contrôleurs : étapes de résolution, procédures, guides. Les **agents** peuvent les **télécharger** depuis la plateforme (recherche par mots clés ou page dédiée).

- **Taille maximale** par fichier : **20 Mo**.
- **Mise à jour** : en cas de nouvelle version d’un guide, on **remplace simplement** le document (pas d’historique des versions).

| Champ | Type | Description |
|-------|------|-------------|
| id | string | Identifiant unique du document. |
| title | string | Titre du document (indexé pour la recherche). |
| description | string | Courte description (optionnel). |
| fileUrl | string | Chemin ou URL du fichier PDF. |
| keywords | array / string | Mots clés pour la recherche (optionnel). |
| createdAt | datetime | Date d’ajout. |
| updatedAt | datetime | Date de modification (optionnel). |

---

## 7. Règles métier

### 7.1 Détection de la province à partir du login

- **Prefixes** (priorité si le login commence par) :  
  - 2XA, 2AA, 3AAA, 4AABA → Estuaire Libreville  
  - 5CAABA → Moyen Ogooué  
- **Première lettre** (si aucun préfixe) : A → G1 — Libreville/Akanda/Owendo ; B→G2, C→G3, … ; J → G1 — reste Estuaire.  
- Sinon : « Inconnu ».

### 7.2 Priorité (P1 à P4) selon le type de problème

- **P1** : Non accès aux questionnaires, Non accès à l’application, Non accès à la synchronisation des données, Non accès à la synchronisation des paramètres.  
- **P2** : Non visibilité du personnel, Non accès aux zones de travail.  
- **P3** : Non accès aux outils de contrôle.  
- **P4** : Autres.

### 7.3 Types de problème (liste fixe)

- Non accès aux questionnaires  
- Non accès à l’application  
- Non visibilité du personnel  
- Non accès aux zones de travail  
- Non accès aux outils de contrôle  
- Non accès à la synchronisation des données  
- Non accès à la synchronisation des paramètres  

### 7.4 Regroupement de tickets similaires

Lors d’une soumission, si un ticket existant est considéré comme similaire (même titre, même province, titre de problème identique, début de description similaire), on incrémente le compteur d’occurrences au lieu de créer un nouveau ticket (à implémenter côté backend avec critères précis).

### 7.5 Fichiers dépassant la taille autorisée (option A)

Lorsqu’un fichier (pièce jointe ticket ou PDF bibliothèque) **dépasse la limite** autorisée (5 Mo par fichier pour les tickets, 20 Mo pour les PDF de la bibliothèque) :

- **Refuser l’upload** et afficher un **message d’erreur explicite** : indiquer le nom du fichier, la limite concernée (en Mo) et inviter l’utilisateur à compresser le fichier avant de le renvoyer.
- **Proposer des liens** vers des **sites de compression** reconnus (vidéo et PDF), ouverts dans un nouvel onglet. Exemples de libellés : « Compresser une vidéo : [lien outil 1], [lien outil 2] » ; « Compresser un PDF : [lien outil 1], [lien outil 2] ».
- **Aucune redirection automatique** : l’utilisateur reste sur la page ; il choisit d’ouvrir un lien s’il le souhaite, compresse son fichier ailleurs, puis revient sur la plateforme pour renvoyer le fichier.
- **Aucune section dédiée** dans l’application : l’information est donnée **uniquement dans le message d’erreur** affiché lorsque la taille est dépassée.

---

## 8. Design system

- **Couleurs** : Bleu (regal-navy), noir (carbon-black), vert (shamrock) en usage principal ; jaune (golden-pollen) en exception (alertes, certaines pages). Fonds et surfaces : white-smoke.  
- **Fichiers** : `public/css/design-system.css` (variables), `public/css/base.css` (reset, typo, layout), `public/css/components.css` (boutons, cartes, formulaires, badges, alertes, modales, loader, etc.).  
- **Responsive** : Mobile-first, breakpoints 640px et 1024px, conteneur fluide et grilles adaptatives.  
- **Composant Loader** : réutilisable en overlay (pleine page) ou inline ; voir `docs/ARCHITECTURE.md` pour l’usage du partial `templates/partials/loader.php`.

---

## 9. Prochaines étapes (migration)

- Remplacer les pages HTML par des **modules PHP** qui incluent le design system et le composant Loader.  
- Mettre en place un **backend** (API ou rendu serveur) pour tickets, FAQ et authentification.  
- **Persistance** : base de données ou fichiers pour tickets et FAQ.  
- **Sécurité** : authentification côté serveur pour les administrateurs, HTTPS, gestion des sessions (agents vs administrateurs).
- **Optimisations** : plateforme fluide et adaptée aux réseaux lents (pages légères, loader, cache, timeouts/retry, pagination si besoin) ; **prise de ticket atomique** avec verrouillage côté serveur pour éviter que deux contrôleurs prennent le même ticket (détail dans `docs/ARCHITECTURE.md`, section 7).

Cette documentation sert de référence fonctionnelle et technique pour la suite du développement.
