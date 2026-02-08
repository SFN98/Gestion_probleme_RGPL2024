-- Migration 002 : Ajout champ category pour FAQ et champs solution pour tickets
-- À exécuter sur la base rgpl2024

-- Ajouter le champ category à la table FAQ
ALTER TABLE `faq` 
ADD COLUMN IF NOT EXISTS `category` VARCHAR(100) NOT NULL DEFAULT '' AFTER `title`,
ADD COLUMN IF NOT EXISTS `question` TEXT NOT NULL DEFAULT '' AFTER `category`;

-- Ajouter les champs pour les types de solutions dans tickets
ALTER TABLE `tickets`
ADD COLUMN IF NOT EXISTS `solution_type` VARCHAR(20) NOT NULL DEFAULT 'text' COMMENT 'text|pdf|video' AFTER `response`,
ADD COLUMN IF NOT EXISTS `solution_content` TEXT NOT NULL DEFAULT '' COMMENT 'Contenu texte ou URL selon le type' AFTER `solution_type`,
ADD COLUMN IF NOT EXISTS `solution_file` VARCHAR(500) NOT NULL DEFAULT '' COMMENT 'URL du fichier PDF ou vidéo' AFTER `solution_content`;

-- Mettre à jour les données existantes : migrer title vers question si question est vide
UPDATE `faq` SET `question` = `title` WHERE `question` = '' OR `question` IS NULL;
