<?php
/**
 * Script d'exécution de migrations — exécute une migration SQL spécifique
 * Usage: php scripts/run-migration.php 002
 * Prérequis : config/database.php configuré (DB_HOST, DB_NAME, DB_USER, DB_PASSWORD)
 */

if (php_sapi_name() !== 'cli') {
    die('Ce script doit être exécuté en ligne de commande (php scripts/run-migration.php <numéro>).' . PHP_EOL);
}

$migrationNumber = $argv[1] ?? null;
if (!$migrationNumber) {
    die('Usage: php scripts/run-migration.php <numéro_migration>' . PHP_EOL . 'Exemple: php scripts/run-migration.php 002' . PHP_EOL);
}

$basePath = dirname(__DIR__);
require $basePath . '/config/app.php';

if (!defined('DB_HOST') || !defined('DB_NAME')) {
    die('Configuration base de données manquante (config/database.php).' . PHP_EOL);
}

require $basePath . '/src/Utils/db.php';
$pdo = getDb();
if (!$pdo) {
    die('Impossible de se connecter à la base ' . DB_NAME . '.' . PHP_EOL);
}

$migrationFile = $basePath . '/migrations/' . str_pad($migrationNumber, 3, '0', STR_PAD_LEFT) . '_*.sql';
$files = glob($migrationFile);

if (empty($files)) {
    die('Fichier de migration introuvable : migrations/' . str_pad($migrationNumber, 3, '0', STR_PAD_LEFT) . '_*.sql' . PHP_EOL);
}

$migrationFile = $files[0];
echo "Exécution de la migration : " . basename($migrationFile) . "\n";

$sql = file_get_contents($migrationFile);
if ($sql === false) {
    die('Impossible de lire le fichier de migration.' . PHP_EOL);
}

// Supprimer les commentaires en début de ligne (-- ...)
$sql = preg_replace('/^\s*--[^\n]*\n/m', '', $sql);
// Découper par ";\n" ou ";\r\n" pour obtenir une requête par bloc
$statements = preg_split('/;\s*\r?\n/', trim($sql), -1, PREG_SPLIT_NO_EMPTY);

$done = 0;
$errors = 0;

foreach ($statements as $statement) {
    $statement = trim($statement);
    if ($statement === '') {
        continue;
    }
    if (substr($statement, -1) !== ';') {
        $statement .= ';';
    }
    
    // Vérifier si c'est un ALTER TABLE ADD COLUMN et si la colonne existe déjà
    if (preg_match('/ALTER\s+TABLE\s+[`]?(\w+)[`]?\s+ADD\s+COLUMN\s+[`]?(\w+)[`]?/i', $statement, $matches)) {
        $tableName = $matches[1];
        $columnName = $matches[2];
        
        // Vérifier si la colonne existe déjà
        try {
            $checkStmt = $pdo->query("SHOW COLUMNS FROM `{$tableName}` LIKE '{$columnName}'");
            if ($checkStmt->rowCount() > 0) {
                echo "  Colonne `{$tableName}`.`{$columnName}` existe déjà, ignorée.\n";
                continue;
            }
        } catch (PDOException $e) {
            // Si la table n'existe pas, on continue quand même (erreur sera levée par ALTER TABLE)
        }
    }
    
    try {
        $pdo->exec($statement);
        $done++;
        if (preg_match('/ALTER\s+TABLE\s+[`]?(\w+)[`]?\s+ADD\s+COLUMN\s+[`]?(\w+)[`]?/i', $statement, $m)) {
            echo "  Colonne ajoutée : {$m[1]}.{$m[2]}\n";
        } elseif (preg_match('/UPDATE\s+[`]?(\w+)[`]?/i', $statement, $m)) {
            echo "  Données mises à jour : {$m[1]}\n";
        }
    } catch (PDOException $e) {
        $errors++;
        // Ignorer l'erreur "Duplicate column name" car on vérifie déjà avant
        if (strpos($e->getMessage(), 'Duplicate column name') === false) {
            echo "  Erreur : " . $e->getMessage() . "\n";
            echo "  SQL : " . substr($statement, 0, 120) . "…\n";
        } else {
            echo "  Colonne déjà existante, ignorée.\n";
        }
    }
}

if ($errors > 0 && $done === 0) {
    echo "\nMigration échouée. ($errors erreur(s))\n";
    exit(1);
} else {
    echo "\nMigration terminée. ($done requête(s) exécutée(s), $errors erreur(s) ignorée(s))\n";
}
