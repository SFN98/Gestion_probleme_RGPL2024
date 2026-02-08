# Architecture du projet

## 1. Structure des dossiers et fichiers attendus

```
Gestion_probleme_RGPL2024/
├── index.php                      # Point d'entrée unique — routage vers les pages
│
├── config/                        # Configuration de l'application
│   └── app.php                    # Constantes, chemins, paramètres (env, base URL, etc.)
│
├── src/                           # Logique métier PHP (hors document root)
│   ├── Controllers/               # Contrôleurs (ou « Pages ») — traitent une route, préparent les données, incluent la vue
│   │   ├── HomeController.php     # Page d'accueil agent (5.0)
│   │   ├── SearchController.php   # Recherche par mots clés (5.1)
│   │   ├── SubmitController.php   # Soumission de problème (5.2)
│   │   ├── TicketCheckController.php  # Consultation du ticket par numéro (5.3)
│   │   ├── LoginController.php    # Connexion administrateur (5.4)
│   │   └── DashboardController.php    # Dashboard contrôleur (5.5)
│   ├── Services/                 # Services métier — accès aux données, règles métier
│   │   ├── TicketService.php      # CRUD tickets, prise, libération, marquer résolu
│   │   ├── FaqService.php         # CRUD FAQ
│   │   ├── PdfLibraryService.php  # CRUD bibliothèque PDF (upload, remplacement)
│   │   ├── AuthService.php        # Authentification contrôleurs (session, remember)
│   │   └── MediaStorageService.php # Enregistrement, suppression, chemins des médias (tickets + PDF)
│   └── Utils/                    # Utilitaires réutilisables
│       ├── ProvinceHelper.php     # Détection province depuis login, priorité P1–P4
│       └── helpers.php            # escapeHtml, redirections, etc.
│
├── templates/                    # Vues PHP — pas d’URL directe, incluses par les contrôleurs
│   ├── layouts/
│   │   └── layout.php             # Structure HTML commune (head, design system, footer)
│   ├── partials/                 # Composants réutilisables
│   │   ├── loader.php            # Composant Loader (overlay / inline)
│   │   ├── header.php            # En-tête commun (optionnel, selon layout)
│   │   └── footer.php            # Pied de page commun (optionnel)
│   └── pages/                    # Une vue par écran principal
│       ├── home.php              # 5.0 — Page d'accueil agent (3 options)
│       ├── search.php            # 5.1 — Recherche par mots clés (résultats tickets résolus, FAQ, PDF)
│       ├── submit.php             # 5.2 — Formulaire de soumission de problème
│       ├── ticket-check.php       # 5.3 — Saisie numéro + affichage statut / réponse
│       ├── login.php              # 5.4 — Formulaire de connexion contrôleur
│       └── dashboard.php          # 5.5 — Liste tickets, KPI, filtres, FAQ, bibliothèque PDF
│
├── public/                       # Racine web (document root) — tout est accessible par URL
│   ├── index.php                 # Redirection vers ../index.php ou point d'entrée si document root = public
│   ├── assets/                   # Images, logos (logo_rgpl2023.png, gabon_flag.png, etc.)
│   ├── css/                      # Design system et composants
│   │   ├── design-system.css
│   │   ├── base.css
│   │   └── components.css
│   ├── js/                       # Scripts front (recherche, soumission, consultation, login, dashboard)
│   │   ├── search.js
│   │   ├── submit.js
│   │   ├── ticket-check.js
│   │   ├── login.js
│   │   └── dashboard.js
│   └── uploads/                  # Médias uploadés (hors versioning — voir section 6)
│       ├── tickets/              # Pièces jointes des tickets (images, photos, vidéos)
│       │   └── {ticketId}/       # Un sous-dossier par ticket (ex. TABC123/)
│       │       └── ...           # Fichiers nommés de façon unique (uuid ou slug)
│       └── pdf-library/          # Documents PDF de la bibliothèque FAQ (max 20 Mo)
│           └── ...               # Un fichier par document (nom unique, ex. uuid.pdf)
│
├── storage/                      # [Optionnel] Médias hors document root — servi via PHP si accès restreint
│   ├── tickets/                  # Même structure que public/uploads/tickets/
│   └── pdf-library/              # Même structure que public/uploads/pdf-library/
│
├── data/                         # Données de l’application (hors document root)
│   ├── tickets.json              # Ou base de données — à définir
│   ├── faq.json
│   ├── pdf-library.json          # Métadonnées des PDF (titre, description, mots clés, fileUrl)
│   └── users.json                # Comptes contrôleurs (identifiants, mots de passe hashés)
│
├── docs/                         # Documentation
│   ├── ARCHITECTURE.md            # Ce fichier — structure et rôles
│   ├── DOCUMENTATION_SITE.md      # Spécification fonctionnelle (parcours, données, règles)
│   ├── ROADMAP.md                 # Phases de développement (plan de réalisation)
│   └── ANALYSE_ETAT_ACTUEL.md    # Analyse avant migration
│
├── README.md
└── (anciens *.html et js/ à la racine — dépréciés, remplacés par les modules PHP)
```

---

## 2. Rôle des dossiers

| Dossier | Rôle |
|--------|------|
| **config/** | Configuration centralisée (chemins, URL, paramètres). Aucune logique métier. |
| **src/Controllers/** | Reçoit la requête (route), appelle les services, passe les données à la vue, inclut le layout + la page. |
| **src/Services/** | Logique métier et accès aux données (tickets, FAQ, PDF, auth). Pas d’HTML. |
| **src/Utils/** | Fonctions réutilisables (province, priorité, escape, etc.). |
| **templates/** | Uniquement de l’affichage (HTML + PHP d’affichage). Inclus par les contrôleurs ; jamais appelés directement par URL. |
| **templates/layouts/** | Structure commune des pages (DOCTYPE, head avec CSS, body, zone principale, scripts). |
| **templates/partials/** | Blocs réutilisables (loader, header, footer) inclus dans les layouts ou les pages. |
| **templates/pages/** | Une vue par écran fonctionnel (contenu spécifique de la page). |
| **public/** | Tout ce qui doit être servi par le serveur web (CSS, JS, images, uploads). Si le document root est la racine du projet, les URLs pointent vers `public/` (ex. `/public/css/design-system.css`) ou via réécriture. |
| **public/uploads/** | Médias envoyés par les utilisateurs (tickets : images/vidéos ; bibliothèque : PDF). Structure par type et par ticket. Ne pas versionner (voir section 6). |
| **storage/** | [Optionnel] Copie des médias hors document root ; servi via un contrôleur PHP si l’on souhaite restreindre l’accès (ex. pièces jointes tickets réservées aux contrôleurs). |
| **data/** | Fichiers de persistance (JSON, SQLite, etc.) ou emplacement des données. Hors document root pour la sécurité. |
| **docs/** | Documentation technique et fonctionnelle. |

---

## 3. Rôle des fichiers principaux

| Fichier | Rôle |
|---------|------|
| **index.php** | Point d’entrée unique. Analyse l’URL (ou un paramètre), appelle le bon contrôleur (ou inclut la page), gère les erreurs 404. |
| **config/app.php** | Définit les constantes (BASE_PATH, PUBLIC_URL, chemins vers data/, uploads/), options (debug, taille max upload). |
| **src/Controllers/*.php** | Un contrôleur par « page » : vérification des droits (dashboard/login), appel des services, rendu de la vue correspondante. |
| **src/Services/TicketService.php** | Création/mise à jour des tickets, prise en charge, libération, marquer résolu (avec champ réponse/solution), liste (tri par date), filtres. |
| **src/Services/FaqService.php** | CRUD FAQ ; recherche pour la page « Recherche par mots clés ». |
| **src/Services/PdfLibraryService.php** | Ajout/modification/suppression des documents PDF (fichier + métadonnées), contrainte 20 Mo, remplacement simple sans historique. S’appuie sur MediaStorageService pour l’enregistrement des fichiers. |
| **src/Services/MediaStorageService.php** | Logique de stockage des médias : création des dossiers (tickets/{ticketId}, pdf-library), nommage unique des fichiers, enregistrement depuis la requête, suppression, génération des URLs ou chemins. Respect des limites (taille, types MIME). |
| **src/Services/AuthService.php** | Connexion contrôleur (vérification identifiant/mot de passe), session, « Se souvenir de moi », déconnexion, protection des routes dashboard. |
| **src/Utils/ProvinceHelper.php** | Règles métier : détection province depuis le login (préfixes, lettre), priorité P1–P4 selon le type de problème. |
| **templates/layouts/layout.php** | Structure HTML commune : head (meta, titre, liens vers design-system, base, components), body, zone pour le contenu, scripts JS. |
| **templates/partials/loader.php** | Affiche le composant Loader (overlay ou inline) ; variables : `$loader_id`, `$loader_variant`, `$loader_title`, `$loader_subtitle`, `$loader_progress`, `$loader_logo`. |
| **templates/pages/home.php** | Contenu de la page d’accueil agent : 3 boutons/liens (Rechercher une solution, Soumettre un problème, Consulter mon ticket). |
| **templates/pages/search.php** | Champ recherche, zone résultats (tickets résolus, FAQ, PDF téléchargeables). |
| **templates/pages/submit.php** | Formulaire de soumission (nom, login, téléphone, type, titre, description, pièces jointes), affichage province détectée. |
| **templates/pages/ticket-check.php** | Saisie numéro de ticket, affichage statut et champ Réponse/solution si résolu. |
| **templates/pages/login.php** | Formulaire connexion (identifiant, mot de passe, « Se souvenir de moi »), alerte, lien vers page d’accueil agent. |
| **templates/pages/dashboard.php** | Interface complète dashboard : KPI, liste tickets (tri date), actions (prendre, libérer, voir, réaffecter, marquer résolu), panneau FAQ et bibliothèque PDF, modales. |
| **public/css/*.css** | Design system (variables), base (reset, typo, layout), composants (boutons, cartes, formulaires, loader, etc.). |
| **public/js/*.js** | Comportement côté client : recherche, envoi formulaire soumission, consultation ticket, login, dashboard (filtres, modales, appels API si besoin). |

---

## 4. Stockage des médias (serveur privé)

### 4.1 Dossiers dédiés

| Emplacement | Rôle |
|-------------|------|
| **public/uploads/tickets/{ticketId}/** | Pièces jointes d’un ticket : captures d’écran, photos, vidéos. Un sous-dossier par ticket (ex. `TABC123`). Les chemins relatifs ou URLs sont enregistrés dans le champ `images` du ticket. |
| **public/uploads/pdf-library/** | Documents PDF de la bibliothèque FAQ. Un fichier par document (nom unique, ex. `{uuid}.pdf` ou `{slug}.pdf`). Le champ `fileUrl` dans les métadonnées (data/pdf-library.json) pointe vers ce fichier. |

**Optionnel — stockage hors document root :** si l’on souhaite restreindre l’accès (ex. pièces jointes des tickets visibles uniquement par les contrôleurs connectés), les fichiers peuvent être enregistrés dans **storage/tickets/** et **storage/pdf-library/**. Un contrôleur (ex. `MediaController::download`) sert alors le fichier après vérification des droits (lecture du fichier, envoi des en-têtes appropriés). En l’absence de cette contrainte, **public/uploads/** suffit.

### 4.2 Règles de stockage

| Type | Taille max par fichier | Types autorisés | Nommage |
|------|------------------------|-----------------|---------|
| **Ticket (pièces jointes)** | 5 Mo (à configurer dans config/app.php) | Images : jpeg, png, gif, webp. Vidéos : mp4, webm (à préciser). | Nom unique par fichier dans le dossier du ticket (ex. `{uuid}.{ext}` ou `{timestamp}-{nom_sanitifé}.{ext}`) pour éviter les collisions. |
| **Bibliothèque PDF** | 20 Mo | PDF uniquement. | Nom unique dans pdf-library/ (ex. `{uuid}.pdf`). En cas de mise à jour, remplacement du fichier existant (même nom ou suppression de l’ancien). |

- **Création des dossiers** : au premier enregistrement pour un ticket, créer `public/uploads/tickets/{ticketId}/` si nécessaire (mkdir avec droits adaptés). Idem pour `public/uploads/pdf-library/` au démarrage ou à la première utilisation.
- **Sécurité** : les dossiers d’upload ne doivent pas exécuter de code (PHP, script). Sur Apache : placer un `.htaccess` dans `public/uploads/` avec `php_flag engine off` (ou équivalent). Sur Nginx : pas de bloc `location` exécutant PHP sur `/uploads/`. Ne pas permettre l’upload de fichiers `.php`, `.phtml`, etc. (vérifier extension et type MIME côté serveur).

### 4.3 Logique métier (qui écrit, qui lit)

| Action | Responsable | Dossier cible | Enregistrement |
|--------|-------------|---------------|----------------|
| **Soumission d’un ticket avec pièces jointes** | `SubmitController` → `TicketService` + `MediaStorageService` | `public/uploads/tickets/{ticketId}/` | Après création du ticket (obtention de l’id), enregistrer chaque fichier via `MediaStorageService::storeTicketMedia($ticketId, $uploadedFile)`. Retourne le chemin relatif ou l’URL ; ajouter au tableau `images` du ticket et sauvegarder le ticket (data/tickets.json ou BDD). |
| **Ajout d’un PDF à la bibliothèque** | `DashboardController` (ou API) → `PdfLibraryService` + `MediaStorageService` | `public/uploads/pdf-library/` | `MediaStorageService::storePdfDocument($uploadedFile)` : vérifier taille ≤ 20 Mo, type PDF, enregistrer avec nom unique, retourner `fileUrl`. `PdfLibraryService` enregistre les métadonnées (titre, description, mots clés, fileUrl) dans data/pdf-library.json. |
| **Mise à jour d’un PDF existant** | `PdfLibraryService` + `MediaStorageService` | `public/uploads/pdf-library/` | Remplacer le fichier existant (même identifiant document) : supprimer l’ancien fichier, enregistrer le nouveau (même nom ou nouveau nom + mise à jour du fileUrl dans les métadonnées). Pas d’historique des versions. |
| **Suppression d’un document PDF** | `PdfLibraryService` + `MediaStorageService` | `public/uploads/pdf-library/` | Supprimer le fichier du disque via `MediaStorageService::deletePdfDocument($fileUrl)` et retirer l’entrée des métadonnées. |
| **Affichage / téléchargement** | Côté agent ou contrôleur | Lecture seule | **Si tout est dans public/uploads/** : lien direct vers l’URL du fichier (ex. `/uploads/tickets/TABC123/abc.jpg`). **Si stockage dans storage/** : requête vers une route dédiée (ex. `/media/ticket/{ticketId}/{filename}` ou `/media/pdf/{id}`) ; le contrôleur vérifie les droits, lit le fichier depuis storage/, envoie les en-têtes (Content-Type, Content-Disposition) et le contenu. |

### 4.4 Configuration (config/app.php)

- `UPLOAD_PATH_TICKETS` : chemin absolu vers `public/uploads/tickets/` (ou `storage/tickets/`).
- `UPLOAD_PATH_PDF_LIBRARY` : chemin absolu vers `public/uploads/pdf-library/` (ou `storage/pdf-library/`).
- `UPLOAD_MAX_SIZE_TICKET_MB` : 5.
- `UPLOAD_MAX_SIZE_PDF_MB` : 20.
- `UPLOAD_ALLOWED_MIMES_TICKET` : liste des types MIME autorisés pour les pièces jointes (images + vidéos).
- `UPLOAD_ALLOWED_MIMES_PDF` : `['application/pdf']`.
- `PUBLIC_UPLOAD_URL` : base des URLs publiques pour les médias (ex. `/uploads/` ou `https://domaine.example/uploads/` si besoin d’URLs absolues).

### 4.5 Fichiers à ignorer (versioning)

- Ajouter **public/uploads/** (et **storage/** si utilisé) au `.gitignore` pour ne pas versionner les médias. Conserver éventuellement la structure vide avec un fichier `.gitkeep` pour que les dossiers existent au déploiement.

### 4.6 Fichier trop volumineux (option A)

Si un fichier dépasse la limite (5 Mo pour tickets, 20 Mo pour PDF) : **refuser l’upload** et renvoyer un **message d’erreur** contenant le nom du fichier et la limite en Mo. Dans ce message, **inclure des liens** (ouverts en nouvel onglet) vers des sites de compression recommandés (vidéo et PDF). Aucune redirection automatique, aucune section dédiée dans l’app — l’info est uniquement dans le message d’erreur. Détail en **DOCUMENTATION_SITE.md**, section 7.5.

---

## 5. Composant Loader

### 5.1 Emplacement

- **Vue** : `templates/partials/loader.php`
- **Styles** : `public/css/components.css` (classes `.loader`, `.loader--overlay`, `.loader--inline`).

### 5.2 Utilisation dans une page PHP

Définir les variables optionnelles puis inclure le partial :

```php
<?php
$loader_id       = 'app-loader';
$loader_variant  = 'overlay';           // 'overlay' | 'inline'
$loader_title    = 'Plateforme de Gestion';
$loader_subtitle = 'Chargement des données...';
$loader_progress = true;
$loader_logo     = '/assets/logo_court_rgpl2023.png';  // ou '' pour le spinner seul
require __DIR__ . '/../templates/partials/loader.php';
?>
```

### 5.3 Variantes

| Variante   | Classe             | Usage |
|------------|--------------------|--------|
| Pleine page | `loader loader--overlay` | Écran de chargement initial (fixe, z-index 9999). |
| Inline    | `loader loader--inline`  | Bloc de chargement dans une carte ou une section. |

### 5.4 Masquage (côté JS)

Ajouter la classe `is-hidden` au conteneur :

```javascript
document.getElementById('app-loader').classList.add('is-hidden');
```

---

## 6. Flux de requête (résumé)

1. L’utilisateur demande une URL (ex. `/`, `/recherche`, `/soumettre`, `/ticket`, `/login`, `/dashboard`).
2. **index.php** reçoit la requête, charge la config et le routeur (ou un switch sur le chemin).
3. Le **contrôleur** correspondant est invoqué ; il utilise les **services** pour lire/écrire les données.
4. Le contrôleur inclut **templates/layouts/layout.php** en lui passant le contenu (ou le nom de la page) ; le layout inclut **templates/pages/xxx.php** et éventuellement **templates/partials/loader.php**.
5. La réponse HTML est envoyée ; les assets (CSS, JS, images) sont servis depuis **public/**.

Les anciens fichiers HTML à la racine et le dossier **js/** à la racine sont dépréciés : les écrans et scripts seront migrés vers la structure ci-dessus (templates/pages, public/js).

---

## 7. Optimisations

### 7.1 Fluidité et adaptation aux réseaux lents

La plateforme doit rester fluide et utilisable sur des connexions lentes. Principes à respecter :

- **Pages légères** : HTML minimal par écran, CSS/JS regroupés et chargés dans l’ordre (design-system, base, components). Éviter les scripts ou librairies lourdes inutiles.
- **Loader** : afficher le composant Loader pendant le chargement des données (liste tickets, résultats recherche, etc.) pour donner un retour visuel immédiat ; le masquer une fois la réponse reçue.
- **Cache navigateur** : envoyer des en-têtes HTTP adaptés sur les assets statiques (CSS, JS, images dans `public/`) : `Cache-Control` avec `max-age` raisonnable (ex. 1 an avec nom de fichier ou hash dans l’URL pour invalidation). Les pages HTML peuvent être en `no-cache` ou courte durée pour refléter les mises à jour.
- **Payloads limités** : ne pas charger en une fois des listes énormes (ex. tickets) ; prévoir une **pagination** ou un chargement par lots si le volume augmente. Pour le dashboard, limiter le nombre de tickets renvoyés par requête si besoin.
- **Timeouts et retry** : côté client (JS), définir un timeout sur les requêtes (ex. 30 s) et proposer un message clair en cas d’échec (« Réseau lent ou indisponible. Réessayer ? ») avec un bouton pour relancer la requête. Éviter les attentes infinies sans feedback.
- **Compression** : activer la compression gzip (ou équivalent) côté serveur pour les réponses HTML/CSS/JS afin de réduire le volume transféré sur liens lents.
- **Priorité au contenu visible** : charger en priorité ce qui est affiché en premier (above the fold) ; images ou blocs secondaires peuvent être chargés en différé (lazy load) si pertinent.

### 7.2 File / verrouillage pour la prise de ticket (éviter la double prise)

**Problème** : deux contrôleurs peuvent cliquer « Prendre le ticket » sur le même ticket au même moment ; sans garde-fou, les deux pourraient être considérés comme assignés.

**Principe** : traiter la **prise de ticket comme une opération atomique** côté serveur. Une seule requête « prendre le ticket X » doit aboutir ; les autres doivent recevoir une erreur explicite.

**Côté serveur (TicketService ou équivalent)** :

1. Lors de la requête « prendre le ticket `ticketId` » (avec l’identifiant du contrôleur connecté) :
   - **Verrouiller** la ressource (le ticket ou le fichier/table des tickets) le temps de la lecture et de la mise à jour.
     - **Avec fichiers JSON** : utiliser `flock()` (verrou exclusif) sur le fichier `data/tickets.json` (ou sur un fichier de lock dédié) pendant la lecture, la vérification du statut et l’écriture. Si le ticket est encore `open`, mettre à jour `status` et `assignee`, puis sauvegarder ; sinon, retourner une erreur « Ticket déjà pris ».
     - **Avec base de données** : exécuter une mise à jour conditionnelle du type `UPDATE tickets SET status = 'progress', assignee = ? WHERE id = ? AND status = 'open'` ; si le nombre de lignes modifiées est 0, le ticket était déjà pris ou résolu → retourner erreur.
2. **Réponse** :
   - **Succès** : ticket mis à jour (statut « En cours de traitement », assigné = contrôleur demandeur) ; retourner les données à jour.
   - **Échec** : retourner un code ou message explicite (ex. `ticket_already_taken` ou « Ce ticket a déjà été pris par [nom] ») pour que le client puisse afficher un message et rafraîchir la liste.

**Côté client (dashboard)** :

- Au clic sur « Prendre le ticket », **désactiver immédiatement** le bouton (ou afficher un état « En cours… ») pour limiter les double-clics.
- Envoyer la requête au serveur ; à la réponse :
  - **Succès** : mettre à jour l’affichage (statut, assigné) et la liste des tickets.
  - **Échec (déjà pris)** : afficher un message du type « Ce ticket a déjà été pris par [nom]. La liste va se rafraîchir. » et **rafraîchir la liste** des tickets (ou mettre à jour l’entrée concernée) pour refléter l’état réel.

Ainsi, la « file » des prises est implicite : la première requête valide qui atteint le serveur et trouve le ticket encore `open` l’obtient ; les autres reçoivent une erreur claire et peuvent voir le ticket déjà assigné après rafraîchissement.
