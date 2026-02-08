-- Migration : ajout colonnes remember_token pour "Se souvenir de moi"
-- Optionnel : à exécuter si vous souhaitez utiliser la fonctionnalité "Se souvenir de moi"

ALTER TABLE `users` 
ADD COLUMN `remember_token` VARCHAR(64) DEFAULT NULL,
ADD COLUMN `remember_expires_at` DATETIME DEFAULT NULL;
