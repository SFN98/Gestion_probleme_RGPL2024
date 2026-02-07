# Analyse de l'état actuel du projet — Avant migration

**Projet :** Plateforme de gestion des problèmes (RGPL 2024)  
**Objectif métier :** Centraliser les problèmes liés au fonctionnement d’une application et mettre à disposition des solutions pour les utilisateurs.  
**Date d’analyse :** 7 février 2025  

---

## 1. Vue d’ensemble

Le projet est aujourd’hui une **application web statique** (HTML + JavaScript) avec une **page d’entrée PHP vide** (`index.php`). Il n’y a **pas de backend** : les données (tickets, FAQ) sont gérées **en mémoire JavaScript** et ne sont pas persistées entre sessions ou entre utilisateurs. L’authentification est simulée côté client (sessionStorage/localStorage).

---

## 2. Structure actuelle du projet

```
Gestion_probleme_RGPL2024/
├── index.php          # Vide (1 ligne)
├── index.html         # Page de soumission de problème (formulaire terrain)
├── login.html         # Page de connexion admin
├── dashboard.html     # Dashboard administrateur (liste tickets, FAQ, stats)
├── README.md          # Minimal (titre du projet)
├── js/
│   ├── dashboard.js   # Logique dashboard (tickets, FAQ, auth, filtres)
│   ├── login.js       # Logique connexion (credentials en dur)
│   └── submit.js      # Logique formulaire de soumission + affichage FAQ
└── css/               # ABSENT (fichiers supprimés selon git : dashboard.css, login.css, submit.css)
```

**Constats :**

- **Aucun dossier `css/`** : les trois HTML référencent `css/submit.css`, `css/login.css`, `css/dashboard.css` qui n’existent plus → les pages n’ont pas de styles chargés.
- **Pas de `submit.html`** : `login.html` pointe vers `submit.html` (lien “Retour à la page de soumission”) alors que le formulaire de soumission est dans **`index.html`** → lien cassé.
- **Pas de backend** : pas de `composer.json`, pas d’API PHP, pas de base de données.
- **Pas de dossiers “modulaires”** visibles à la racine pour la future architecture (ex. `src/`, `public/`, `api/`, etc.) — à créer ou à préciser lors de la migration.

---

## 3. Parcours utilisateur et fonctionnalités

| Parcours | Fichiers | Description |
|----------|----------|-------------|
| **Soumission problème (terrain)** | `index.html` + `js/submit.js` | Formulaire : nom, login, téléphone, type de problème, titre, description, images. Détection province depuis le login, affichage des tickets similaires (province) et de la FAQ. |
| **Connexion admin** | `login.html` + `js/login.js` | Identifiant / mot de passe, “Se souvenir de moi”. Credentials en dur (admin/admin123, technicien/tech2024, superviseur/super2024). Redirection vers `dashboard.html`. |
| **Dashboard admin** | `dashboard.html` + `js/dashboard.js` | KPIs (total, ouverts, résolus, en cours), tableau des tickets avec filtres (province, type, statut, priorité, recherche), détail ticket, réaffectation, marquer résolu, stats par province, gestion FAQ (CRUD). |

---

## 4. Données et persistance

- **Tickets** : tableau global `TICKETS_DATA` dans `dashboard.js` et `submit.js`.  
  - **Incohérence** : `dashboard.js` initialise avec un ticket exemple, `submit.js` avec un tableau vide. Comme il n’y a pas de backend, les deux pages ne partagent pas les mêmes données (contexte mémoire distinct par page).
- **FAQ** : tableaux `FAQ_DATA` dans les deux fichiers, dupliqués et non synchronisés.
- **Auth** : `sessionStorage` (isLoggedIn, username, userRole) et `localStorage` (rememberMe, savedUsername). Contournable (pas de vérification serveur).

Les commentaires dans le code indiquent des **TODO backend** (remplacer par `GET/POST /api/tickets`, `/api/faq`).

---

## 5. Logique métier réutilisée (à migrer)

- **Province depuis login** : `PROVINCE_RULES` (prefixes + letterMap) et `getProvinceFromLogin(login)` — présente dans `submit.js` et `dashboard.js` (duplication).
- **Priorité** : `getPriorityTag(type)` dans `submit.js` (P1/P2/P3/P4 selon le type de problème).
- **Modèle ticket** : `createTicketObj(...)` dans `submit.js` (id, titre, province, priorité, statut, assignee, dates, etc.).
- **Types de problème** : liste fixe dans le `<select>` de `index.html` (non accès questionnaires, application, personnel, zones de travail, outils de contrôle, synchronisation données/paramètres).

---

## 6. Points techniques à corriger / risques

| Problème | Sévérité | Détail |
|----------|----------|--------|
| CSS manquants | Élevée | Tous les HTML pointent vers `css/*.css` supprimés → mise en forme cassée. |
| Lien `submit.html` | Moyenne | `login.html` → `submit.html` alors que la soumission est dans `index.html` → 404 ou page inexistante. |
| Données non partagées | Élevée | Tickets/FAQ en mémoire par page ; soumission et dashboard ne voient pas les mêmes données. |
| Pas de persistance | Élevée | Rechargement ou nouvel utilisateur = perte des données. |
| Auth côté client uniquement | Moyenne | Accès au dashboard possible en ouvrant directement `dashboard.html` sans login. |
| Duplication de code | Moyenne | `PROVINCE_RULES`, `loadTickets`/`saveTickets`, `loadFAQ`/`saveFAQ`, `escapeHtml` en double (submit vs dashboard). |
| `index.php` vide | Faible | Aucun rôle actuel ; prévu pour la future entrée PHP. |
| README | Faible | Contenu minimal ; possible encodage BOM/UTF-16. |

---

## 7. Synthèse pour la migration vers une architecture modulaire PHP/JS

- **Front** : 3 écrans (soumission, login, dashboard) avec HTML/JS existants ; à intégrer dans une structure `public/` ou équivalent, et à brancher sur des appels API au lieu de variables globales.
- **Backend à créer** : API PHP (tickets, FAQ, auth), persistance (fichier ou BDD), partage des mêmes données pour soumission et dashboard.
- **À factoriser** : règles provinces/priorités, format ticket, utilitaires (`escapeHtml`, etc.) dans des modules partagés (PHP côté serveur, JS côté client si besoin).
- **À corriger avant ou pendant la migration** : recréer ou recâbler les CSS, corriger le lien “Retour à la page de soumission” (vers `index.html` ou future route), unifier la source de vérité des tickets et FAQ.

Cette analyse sert de base pour définir les étapes de restructuration (dossiers, modules PHP, routes, API et refonte JS) dans un prochain document de plan de migration.
