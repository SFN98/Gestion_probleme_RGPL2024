# Roadmap du projet — Plateforme de gestion des problèmes RGPL 2024

Ce document décrit les phases de développement prévues. Chaque phase peut être découpée en tâches plus fines (issues, sprints) selon votre organisation.

---

## Phase 0 — Déjà en place

| Élément | Statut |
|--------|--------|
| [x] Documentation fonctionnelle (DOCUMENTATION_SITE.md) | Fait |
| [x] Architecture et structure cible (ARCHITECTURE.md) | Fait |
| [x] Design system (public/css) | Fait |
| [x] Composant Loader (templates/partials/loader.php) | Fait |
| [x] Roadmap (ce fichier) | Fait |
| [x] README à jour | Fait |

**À faire avant de coder :**  
- [x] Créer les dossiers et fichiers vides de la structure cible (config/, src/Controllers, src/Services, src/Utils, templates/layouts, templates/pages, data/, public/uploads/tickets, public/uploads/pdf-library, .gitignore).

---

## Phase 1 — Fondations

Objectif : point d’entrée, configuration, layout commun, données et utilitaires. Aucun écran métier complet encore.

| Tâche | Détail | Statut |
|-------|--------|--------|
| [x] config/app.php | Constantes (BASE_PATH, PUBLIC_URL, chemins data/ et uploads/, tailles max upload, options). | Fait |
| [x] index.php | Routage simple (GET : /, /recherche, /soumettre, /ticket, /login, /dashboard). Inclure config, dispatcher vers le bon contrôleur ou une page 404. | Fait |
| [x] templates/layouts/layout.php | Structure HTML : DOCTYPE, head (meta, titre variable, liens CSS design-system, base, components), body avec zone pour le contenu, scripts JS optionnels. Inclure le loader si besoin. | Fait |
| [x] data/ | Fichiers JSON initiaux (tickets.json [], faq.json [], pdf-library.json [], users.json avec au moins un compte contrôleur de test). Dossiers créés, droits en écriture. | Fait |
| [x] src/Utils/ProvinceHelper.php | Règles province (préfixes, letterMap) et priorité P1–P4. Fonctions réutilisables. | Fait |
| [x] src/Utils/helpers.php | escapeHtml, redirection, éventuellement fonction de chargement JSON avec lock. | Fait |

**Livrable :**  
- [x] Une URL (ex. `/`) affiche une page blanche ou un message de test via le layout ;
- [x] Les routes existent et renvoient vers des contrôleurs vides ou des vues minimales.

---

## Phase 2 — Côté agent (pages et services de base)

Objectif : les quatre écrans agent (accueil, recherche, soumission, consultation ticket) sont accessibles et fonctionnels avec des données en JSON. Pas encore de pièces jointes (Phase 4).

| Tâche | Détail | Statut |
|-------|--------|--------|
| [ ] HomeController + templates/pages/home.php | Page d’accueil avec les 3 liens (Rechercher une solution, Soumettre un problème, Consulter mon ticket). | |
| [ ] TicketService (sans médias) | Lecture/écriture tickets.json (CRUD basique, liste triée par date). Génération ID ticket. Pas de prise/libération pour l’instant. | |
| [ ] FaqService | Lecture/écriture faq.json. Recherche par mots clés (titre, solution) sur FAQ. | |
| [ ] PdfLibraryService (métadonnées seules) | Lecture/écriture pdf-library.json. Pas d’upload PDF encore (Phase 4). | |
| [ ] SearchController + search.php | Champ recherche, appel FaqService + TicketService (tickets résolus) + PdfLibraryService. Affichage résultats (tickets résolus, FAQ, PDF avec liens). Page recherche par mots clés. | |
| [ ] SubmitController + submit.php | Formulaire soumission (nom, login, téléphone, type, titre, description). Détection province (ProvinceHelper). Création ticket via TicketService, affichage numéro de ticket, message succès. Lien vers consultation ticket. | |
| [ ] TicketCheckController + ticket-check.php | Saisie numéro de ticket, recherche par ID, affichage statut et champ réponse/solution si résolu. | |
| [ ] public/js | Scripts minimalistes pour recherche (envoi formulaire ou fetch), soumission (formulaire), consultation ticket (affichage résultat). Loader affiché pendant les requêtes si besoin. | |

**Livrable :**  
- [ ] Un agent peut ouvrir l’accueil  
- [ ] Aller sur recherche/soumission/consultation  
- [ ] Soumettre un ticket (sans fichier), voir son numéro, et consulter le ticket par numéro  
- [ ] Recherche renvoie tickets résolus + FAQ (+ PDF quand métadonnées présentes)

---

## Phase 3 — Côté contrôleur (auth + dashboard)

Objectif : connexion, dashboard, prise/libération de ticket avec verrouillage, réaffectation, marquer résolu avec réponse/solution.

| Tâche | Détail | Statut |
|-------|--------|--------|
| [ ] AuthService | Connexion (vérification users.json), session, « Se souvenir de moi », déconnexion. Protection des routes /dashboard (redirection vers /login si non connecté). | |
| [ ] LoginController + login.php | Formulaire connexion, appel AuthService, redirection dashboard si OK, sinon message d’erreur. Lien vers page d’accueil agent. | |
| [ ] TicketService (prise, libération, résolution) | Prise de ticket : verrou (flock sur tickets.json ou UPDATE conditionnel si BDD), vérification statut = open, mise à jour status + assignee. Retourner succès ou erreur « ticket déjà pris ». Libération : idem, status = open, assignee vide. Marquer résolu : status = resolved, champ response renseigné. Réaffectation : assignee + status = progress. | |
| [ ] DashboardController + dashboard.php | Liste tickets (tri par date), KPI, filtres (recherche, province, type, statut). Actions : Prendre (avec gestion erreur « déjà pris »), Libérer, Voir détail, Réaffecter, Marquer résolu (avec champ Réponse/solution). Panneau FAQ (liste, ajout, modification, suppression via FaqService). Panneau bibliothèque PDF : liste des entrées (métadonnées) ; upload PDF en Phase 4. Modales : détail ticket, réaffectation, marquer résolu, FAQ, PDF. | |
| [ ] public/js/dashboard.js | Appels pour liste tickets, prise, libération, réaffectation, marquer résolu, CRUD FAQ. Désactivation bouton « Prendre » au clic, message si ticket déjà pris, rafraîchissement liste. Loader pendant les requêtes. | |

**Livrable :**  
- [ ] Un contrôleur peut se connecter  
- [ ] Voir la liste des tickets  
- [ ] Prendre un ticket (un seul gagnant en cas de concurrence)  
- [ ] Le libérer  
- [ ] Le réaffecter  
- [ ] Le marquer résolu avec une réponse  
- [ ] Gestion FAQ opérationnelle  
- [ ] Bibliothèque PDF affichée (sans upload encore)

---

## Phase 4 — Médias et robustesse

Objectif : pièces jointes des tickets, bibliothèque PDF (upload), message « fichier trop volumineux » avec liens compression, optimisations.

| Tâche | Détail | Statut |
|-------|--------|--------|
| [ ] MediaStorageService | Création dossiers tickets/{ticketId}, pdf-library. Enregistrement fichiers (nom unique, vérification taille et type MIME). Suppression fichier. Génération URL/chemin. Limites : 5 Mo (ticket), 20 Mo (PDF). En cas de dépassement : refuser et retourner erreur avec message + liens vers outils de compression (voir DOCUMENTATION_SITE.md 7.5). | |
| [ ] SubmitController + formulaire | Gestion upload multiple (images/vidéos). Après création ticket, enregistrer les fichiers via MediaStorageService, stocker les chemins dans ticket.images. | |
| [ ] Dashboard : détail ticket | Affichage des pièces jointes (images cliquables, liens vidéo/PDF). | |
| [ ] PdfLibraryService + upload | Upload PDF dans public/uploads/pdf-library/, enregistrement métadonnées (titre, description, mots clés, fileUrl). Mise à jour = remplacement fichier. Suppression = suppression fichier + entrée. | |
| [ ] Dashboard : gestion bibliothèque PDF | Formulaire ajout/édition PDF (fichier, titre, description, mots clés). Liste avec lien téléchargement. | |
| [ ] Optimisations (ARCHITECTURE 7.1) | Cache HTTP sur assets. Timeout + retry côté JS. Pagination liste tickets si besoin. Compression gzip côté serveur si possible. Loader systématique pendant chargement données. | |

**Livrable :**  
- [ ] Soumission de ticket avec pièces jointes (images/vidéos), affichage dans le détail  
- [ ] Bibliothèque PDF complète (upload, remplacement, suppression)  
- [ ] Message clair si fichier > limite avec liens compression  
- [ ] Comportement correct sur réseau lent (loader, timeouts)

---

## Phase 5 — Finition et déploiement

Objectif : sécurisation, nettoyage, déploiement sur serveur privé.

| Tâche | Détail | Statut |
|-------|--------|--------|
| [ ] Sécurité | Vérification extension + MIME pour tous les uploads. .htaccess ou config Nginx pour désactiver exécution PHP dans uploads/. HTTPS en production. Mots de passe contrôleurs hashés (password_hash). | |
| [ ] .gitignore | public/uploads/, storage/, data/*.json si sensibles (ou données de test versionnées, données réelles exclues). | |
| [ ] Réinitialiser données | Bouton dashboard « Réinitialiser données » : confirmation, puis vidage ou reset des JSON (à définir selon besoin). | |
| [ ] Tests manuels | Parcours complet agent (recherche, soumission avec fichiers, consultation ticket). Parcours complet contrôleur (connexion, prise, libération, résolution, FAQ, PDF). Test concurrence : deux onglets, prise même ticket → un seul succès. | |
| [ ] Déploiement | Copie projet sur serveur, racine web sur dossier du projet ou public/. Droits écriture data/ et public/uploads/. Config PHP (upload_max_filesize, post_max_size) pour 5 Mo et 20 Mo. | |

**Livrable :**  
- [ ] Application déployée  
- [ ] Sécurisée  
- [ ] Testée  
- [ ] Utilisable en conditions réelles

---

## Récapitulatif des phases

| Phase | Contenu principal | Avancement |
|-------|-------------------|:----------:|
| [x] 0 | Déjà fait (docs, design system, loader). Création structure dossiers/fichiers. | Fait |
| [x] 1 | config, index.php router, layout, data, Utils (ProvinceHelper, helpers). | Fait |
| [ ] 2 | Accueil, recherche, soumission (sans fichiers), consultation ticket. TicketService, FaqService, PdfLibraryService (métadonnées). | |
| [ ] 3 | Auth, login, dashboard. Prise/libération ticket avec verrou. Réaffectation, marquer résolu (réponse/solution). CRUD FAQ. | |
| [ ] 4 | Upload tickets (images/vidéos), upload PDF bibliothèque. Message fichier trop lourd + liens. Optimisations (cache, timeout, loader). | |
| [ ] 5 | Sécurité, .gitignore, tests, déploiement. | |

Les phases 1 à 3 permettent une première version utilisable (sans médias) ; la phase 4 ajoute les médias et la robustesse ; la phase 5 finalise pour la mise en production.
