<?php
/**
 * Création d'un compte administrateur — CLI uniquement
 * Usage : php scripts/create-admin.php username password "Display Name"
 */

if (php_sapi_name() !== 'cli') {
    die('Ce script doit être exécuté en ligne de commande.' . PHP_EOL);
}

$basePath = dirname(__DIR__);
require $basePath . '/config/app.php';
require $basePath . '/src/Utils/db.php';

if ($argc < 3) {
    echo "Usage : php scripts/create-admin.php <username> <password> [display_name]\n";
    echo "Exemple : php scripts/create-admin.php admin password123 \"Administrateur\"\n";
    exit(1);
}

$username = trim($argv[1]);
$password = trim($argv[2]);
$displayName = isset($argv[3]) ? trim($argv[3]) : $username;

if (empty($username) || empty($password)) {
    die("Erreur : username et password sont requis.\n");
}

$pdo = getDb();
if (!$pdo) {
    die("Erreur : impossible de se connecter à la base de données.\n");
}

// Vérifier si l'utilisateur existe déjà
$stmt = $pdo->prepare('SELECT id FROM users WHERE username = ?');
$stmt->execute([$username]);
if ($stmt->fetch()) {
    die("Erreur : l'utilisateur '$username' existe déjà.\n");
}

// Hash du mot de passe
$passwordHash = password_hash($password, PASSWORD_DEFAULT);

// Insertion
$stmt = $pdo->prepare('INSERT INTO users (username, password_hash, display_name, created_at) VALUES (?, ?, ?, NOW())');
$stmt->execute([$username, $passwordHash, $displayName]);

if ($stmt->rowCount() > 0) {
    echo "✓ Administrateur créé avec succès :\n";
    echo "  Username : $username\n";
    echo "  Display Name : $displayName\n";
} else {
    die("Erreur : échec de la création de l'administrateur.\n");
}
