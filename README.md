# Plateforme de gestion des problemes RGPL 2024

Application web permettant de **centraliser les problemes** lies au fonctionnement d'une application terrain et de **mettre a disposition des solutions** pour les agents (recherche, tickets, FAQ, bibliotheque PDF).

---

## Roles

- **Agents** : soumettent des problemes, recherchent des solutions (mots cles), consultent l'etat de leur ticket par numero. Pas de connexion requise.
- **Controleurs (administrateurs)** : se connectent, prennent en charge les tickets, les traitent, marquent resolus (avec reponse/solution), gerent la FAQ et la bibliotheque PDF.

---

## Structure du projet

```
|-- index.php              # Point d'entree (routage)
|-- config/                # Configuration
|-- src/                   # Logique PHP (Controllers, Services, Utils)
|-- templates/             # Vues (layouts, partials, pages)
|-- public/                # Racine web (CSS, JS, assets, uploads)
|-- data/                  # Donnees (tickets, FAQ, PDF, users)
|-- docs/                  # Documentation
|-- README.md
```

- **Documentation detaillee** : [docs/ARCHITECTURE.md](docs/ARCHITECTURE.md) (structure, roles des dossiers/fichiers, stockage des medias, loader).
- **Specification fonctionnelle** : [docs/DOCUMENTATION_SITE.md](docs/DOCUMENTATION_SITE.md) (parcours agent/controleur, ecrans, modele de donnees, regles metier).
- **Roadmap** : [docs/ROADMAP.md](docs/ROADMAP.md) (phases de developpement : fondations, cote agent, cote controleur, medias, finition).

---

## Prerequis

- PHP 7.4+ (ou 8.x)
- Serveur web (Apache, Nginx) avec support PHP
- Optionnel : extension PHP pour JSON, sessions, uploads

---

## Installation / demarrage

1. Cloner ou copier le projet sur le serveur (ou en local).
2. Verifier que **public/uploads/tickets/** et **public/uploads/pdf-library/** existent et sont inscriptibles.

### Racine web = public/ (recommandé ? type Laravel/Symfony)

**Recommandé** pour serveur interne (ex. `gestion.dgs.capi`) : configurer Apache pour que la **racine web (DocumentRoot)** pointe sur le dossier **public/** du projet.

- **Apache (vhost)** : `DocumentRoot /chemin/vers/Gestion_probleme_RGPL2024/public`
- **URL** : `http://gestion.dgs.capi/` (sans `/public`, sans sous-dossier)
- **Assets** : servis directement en `/css/`, `/js/`, `/assets/` (pas de 404 sur les CSS)
- Aucune modification dans `config/app.php` : `public/index.php` definit `WEB_ROOT_IS_PUBLIC` et les URLs sont gérées automatiquement.
- La réécriture est dans **public/.htaccess** (fichier ou dossier existant ? servi en statique, sinon ? index.php).

### Racine web = dossier projet (sous-dossier, ex. WAMP sans vhost)

Si la racine web est le **dossier du projet** (ex. `www\Gestion_probleme_RGPL2024\`) :

- **URL** : `http://localhost/Gestion_probleme_RGPL2024/` ou `http://gestion.dgs.capi/Gestion_probleme_RGPL2024/`
- **config/app.php** : garder `APP_BASE_URL = '/Gestion_probleme_RGPL2024'` (ou le nom du dossier).
- **.htaccess** à la racine du projet : `RewriteBase /Gestion_probleme_RGPL2024/` ; les requêtes vers des fichiers réels (-f, -d) ne sont pas réécrites.
- Activer **mod_rewrite** (WAMP : Apache > Charger les modules > rewrite_module).

### Base de donnees MySQL (WAMP / MariaDB)

L'application utilise **MySQL** si `config/database.php` est configuré, sinon elle continue d'utiliser les fichiers JSON dans `data/` (tickets, FAQ, pdf-library).

**Mise en place :**

1. Copier `config/database.php.example` en `config/database.php` et renseigner `DB_HOST`, `DB_NAME`, `DB_USER`, `DB_PASSWORD`.
2. Lancer la création de la base et des tables (en CLI) :
   ```bash
   php scripts/init-db.php
   ```
   Le script crée la base `rgpl2024`, les tables `tickets`, `faq`, `pdf_library`, `users`, et insère le compte contrôleur de test (admin / password).
3. Connexion PDO : `getDb()` dans `src/Utils/db.php` retourne l?instance PDO ou `null` si la BDD n?est pas configurée.

**Schéma SQL :** `migrations/001_initial.sql` (peut être exécuté manuellement avec le client MySQL si besoin).

---

## Design system

- **Couleurs** : bleu (regal-navy), noir (carbon-black), vert (shamrock) ; jaune (golden-pollen) en exception.
- **Fichiers** : `public/css/design-system.css`, `public/css/base.css`, `public/css/components.css`.
- **Composant Loader** : `templates/partials/loader.php` (overlay ou inline). Voir docs/ARCHITECTURE.md section 5.

---

## Licence et contact

Projet interne - RGPL 2024. Pour toute question, se referer a la documentation dans **docs/**.
