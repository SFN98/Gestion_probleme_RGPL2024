-- Migration initiale — Plateforme RGPL 2024
-- Base MySQL/MariaDB : tickets, faq, pdf_library, users

CREATE DATABASE IF NOT EXISTS `rgpl2024` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `rgpl2024`;

-- Tickets (problèmes déclarés)
CREATE TABLE IF NOT EXISTS `tickets` (
  `id` VARCHAR(32) NOT NULL PRIMARY KEY,
  `title` VARCHAR(500) NOT NULL DEFAULT '',
  `reporter_name` VARCHAR(255) NOT NULL DEFAULT '',
  `login` VARCHAR(255) NOT NULL DEFAULT '',
  `phone` VARCHAR(50) NOT NULL DEFAULT '',
  `type` VARCHAR(255) NOT NULL DEFAULT '',
  `desc` TEXT NOT NULL COMMENT 'Description détaillée du problème',
  `images` JSON DEFAULT NULL COMMENT 'Pièces jointes (URLs ou chemins)',
  `occurrences` INT UNSIGNED NOT NULL DEFAULT 1,
  `province` VARCHAR(100) NOT NULL DEFAULT '',
  `priority` VARCHAR(10) NOT NULL DEFAULT 'P4',
  `status` VARCHAR(20) NOT NULL DEFAULT 'open',
  `assignee` VARCHAR(255) NOT NULL DEFAULT '',
  `response` TEXT NOT NULL DEFAULT '',
  `first_reported_at` DATETIME NOT NULL,
  `last_updated_at` DATETIME NOT NULL,
  KEY `idx_status` (`status`),
  KEY `idx_first_reported_at` (`first_reported_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- FAQ (solutions / entrées d'aide)
CREATE TABLE IF NOT EXISTS `faq` (
  `id` VARCHAR(32) NOT NULL PRIMARY KEY,
  `title` VARCHAR(500) NOT NULL DEFAULT '',
  `solution` TEXT NOT NULL,
  `status` VARCHAR(20) NOT NULL DEFAULT 'resolved',
  `created_at` DATETIME NOT NULL,
  `updated_at` DATETIME NOT NULL,
  FULLTEXT KEY `ft_faq_search` (`title`, `solution`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Bibliothèque PDF (métadonnées)
CREATE TABLE IF NOT EXISTS `pdf_library` (
  `id` VARCHAR(32) NOT NULL PRIMARY KEY,
  `title` VARCHAR(500) NOT NULL DEFAULT '',
  `description` TEXT,
  `file_url` VARCHAR(500) NOT NULL DEFAULT '',
  `keywords` JSON DEFAULT NULL,
  `created_at` DATETIME NOT NULL,
  `updated_at` DATETIME NOT NULL,
  FULLTEXT KEY `ft_pdf_search` (`title`, `description`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Utilisateurs (contrôleurs / administrateurs)
CREATE TABLE IF NOT EXISTS `users` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `username` VARCHAR(100) NOT NULL,
  `password_hash` VARCHAR(255) NOT NULL,
  `display_name` VARCHAR(255) NOT NULL DEFAULT '',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY `uk_username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Compte contrôleur de test (mot de passe : password)
INSERT INTO `users` (`username`, `password_hash`, `display_name`, `created_at`) VALUES
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Administrateur', NOW())
ON DUPLICATE KEY UPDATE `username` = `username`;
