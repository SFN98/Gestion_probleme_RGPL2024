-- Migration 002 : Ajout champ category pour FAQ et champs solution pour tickets
-- À exécuter sur la base rgpl2024
-- Note: Les colonnes sont ajoutées seulement si elles n'existent pas déjà (géré par le script PHP)

-- Ajouter le champ category à la table FAQ
ALTER TABLE `faq` 
ADD COLUMN `category` VARCHAR(100) NOT NULL DEFAULT '' AFTER `title`;

-- Ajouter le champ question à la table FAQ
ALTER TABLE `faq` 
ADD COLUMN `question` TEXT NOT NULL DEFAULT '' AFTER `category`;

-- Ajouter les champs pour les types de solutions dans tickets
ALTER TABLE `tickets`
ADD COLUMN `solution_type` VARCHAR(20) NOT NULL DEFAULT 'text' COMMENT 'text|pdf|video' AFTER `response`;

ALTER TABLE `tickets`
ADD COLUMN `solution_content` TEXT NOT NULL DEFAULT '' COMMENT 'Contenu texte ou URL selon le type' AFTER `solution_type`;

ALTER TABLE `tickets`
ADD COLUMN `solution_file` VARCHAR(500) NOT NULL DEFAULT '' COMMENT 'URL du fichier PDF ou vidéo' AFTER `solution_content`;

-- Mettre à jour les données existantes : migrer title vers question si question est vide
UPDATE `faq` SET `question` = `title` WHERE `question` = '' OR `question` IS NULL;
