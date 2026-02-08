# Migrations base de données

## Première installation

1. Configurer `config/database.php` (DB_HOST, DB_NAME, DB_USER, DB_PASSWORD).
2. Exécuter en ligne de commande :
   ```bash
   php scripts/init-db.php
   ```
   Cela crée la base, les tables (tickets, faq, pdf_library, users) et insère le compte admin de test.

## Migrations supplémentaires

Après la première installation, pour appliquer des migrations supplémentaires (ajout de colonnes, modifications de schéma) :

```bash
php scripts/run-migration.php <numéro>
```

Exemple pour la migration 002 :
```bash
php scripts/run-migration.php 002
```

Le script vérifie automatiquement si les colonnes existent déjà avant de les ajouter, évitant les erreurs de duplication.

## Fichiers de migration

- **001_tables_only.sql** : Tables initiales (utilisé par init-db.php)
- **001_initial.sql** : schéma complet (CREATE DATABASE, CREATE TABLE, INSERT utilisateur de test). Peut être exécuté manuellement avec le client MySQL :
  ```bash
  mysql -u root -p < migrations/001_initial.sql
  ```
- **002_add_faq_category_and_solution_types.sql** : Ajoute les champs `category` et `question` à la table `faq`, et les champs `solution_type`, `solution_content`, `solution_file` à la table `tickets`.

## Comportement de l'application

- Si `getDb()` retourne une connexion (config/database.php présent et base accessible), les services **TicketService**, **FaqService** et **PdfLibraryService** utilisent MySQL.
- Sinon, ils utilisent les fichiers JSON dans `data/` (tickets.json, faq.json, pdf-library.json).
