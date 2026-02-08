# Migrations base de données

## Première installation

1. Configurer `config/database.php` (DB_HOST, DB_NAME, DB_USER, DB_PASSWORD).
2. Exécuter en ligne de commande :
   ```bash
   php scripts/init-db.php
   ```
   Cela crée la base, les tables (tickets, faq, pdf_library, users) et insère le compte admin de test.

## Fichiers

- **001_initial.sql** : schéma complet (CREATE DATABASE, CREATE TABLE, INSERT utilisateur de test). Peut être exécuté manuellement avec le client MySQL :
  ```bash
  mysql -u root -p < migrations/001_initial.sql
  ```

## Comportement de l’application

- Si `getDb()` retourne une connexion (config/database.php présent et base accessible), les services **TicketService**, **FaqService** et **PdfLibraryService** utilisent MySQL.
- Sinon, ils utilisent les fichiers JSON dans `data/` (tickets.json, faq.json, pdf-library.json).
