<?php
/**
 * Initialisation de la base de données — crée la base et les tables
 * À exécuter en CLI : php scripts/init-db.php
 * Prérequis : config/database.php configuré (DB_HOST, DB_NAME, DB_USER, DB_PASSWORD)
 */

if (php_sapi_name() !== 'cli') {
    die('Ce script doit être exécuté en ligne de commande (php scripts/init-db.php).' . PHP_EOL);
}

$basePath = dirname(__DIR__);
require $basePath . '/config/app.php';

if (!defined('DB_HOST') || !defined('DB_NAME')) {
    die('Configuration base de données manquante (config/database.php).' . PHP_EOL);
}

$dsnNoDb = sprintf(
    'mysql:host=%s;port=%s;charset=%s',
    DB_HOST,
    defined('DB_PORT') ? DB_PORT : '3306',
    defined('DB_CHARSET') ? DB_CHARSET : 'utf8mb4'
);
$user = defined('DB_USER') ? DB_USER : 'root';
$pass = defined('DB_PASSWORD') ? DB_PASSWORD : '';

echo "Connexion à MySQL…\n";

try {
    $pdoNoDb = new PDO($dsnNoDb, $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ]);
} catch (PDOException $e) {
    die('Erreur de connexion : ' . $e->getMessage() . PHP_EOL);
}

echo "Création de la base " . DB_NAME . " si nécessaire…\n";
$pdoNoDb->exec(sprintf(
    "CREATE DATABASE IF NOT EXISTS `%s` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci",
    str_replace('`', '``', DB_NAME)
));

require $basePath . '/src/Utils/db.php';
$pdo = getDb();
if (!$pdo) {
    die('Impossible de se connecter à la base ' . DB_NAME . '.' . PHP_EOL);
}

$migrationFile = $basePath . '/migrations/001_tables_only.sql';
if (!is_file($migrationFile)) {
    die('Fichier de migration introuvable : migrations/001_tables_only.sql' . PHP_EOL);
}

$sql = file_get_contents($migrationFile);
// Supprimer les commentaires en début de ligne (-- ...)
$sql = preg_replace('/^\s*--[^\n]*\n/m', '', $sql);
// Découper par ";\n" ou ";\r\n" pour obtenir une requête par bloc
$statements = preg_split('/;\s*\r?\n/', trim($sql), -1, PREG_SPLIT_NO_EMPTY);

$done = 0;
foreach ($statements as $statement) {
    $statement = trim($statement);
    if ($statement === '') {
        continue;
    }
    if (substr($statement, -1) !== ';') {
        $statement .= ';';
    }
    try {
        $pdo->exec($statement);
        $done++;
        if (preg_match('/CREATE\s+TABLE\s+(?:IF\s+NOT\s+EXISTS\s+)?[`]?(\w+)[`]?/i', $statement, $m)) {
            echo "  Table créée : {$m[1]}\n";
        } elseif (preg_match('/INSERT\s+INTO/i', $statement)) {
            echo "  Données initiales insérées (users)\n";
        }
    } catch (PDOException $e) {
        echo "  Erreur : " . $e->getMessage() . "\n";
        echo "  SQL : " . substr($statement, 0, 120) . "…\n";
    }
}

echo "Terminé. ($done requête(s) exécutée(s))\n";
