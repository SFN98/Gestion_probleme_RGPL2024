<?php
/**
 * Ajout de l'administrateur Thierry Moussavou
 * Usage : php scripts/add-admin-thierry.php
 */

if (php_sapi_name() !== 'cli') {
    die('Ce script doit être exécuté en ligne de commande.' . PHP_EOL);
}

$basePath = dirname(__DIR__);
require $basePath . '/config/app.php';
require $basePath . '/src/Utils/db.php';

// Informations de l'administrateur
$username = 'thierrymarvyn@gmail.com';
$password = 'TxB$G@686766';
$displayName = 'MOUSSAVOU THIERRY MARVYN';

$pdo = getDb();
if (!$pdo) {
    die("Erreur : impossible de se connecter à la base de données.\n");
}

// Vérifier si l'utilisateur existe déjà
$stmt = $pdo->prepare('SELECT id FROM users WHERE username = ?');
$stmt->execute([$username]);
if ($stmt->fetch()) {
    echo "⚠ L'utilisateur '$username' existe déjà.\n";
    echo "Voulez-vous mettre à jour le mot de passe ? (o/n) : ";
    $handle = fopen("php://stdin", "r");
    $line = trim(fgets($handle));
    fclose($handle);
    
    if (strtolower($line) !== 'o' && strtolower($line) !== 'oui' && strtolower($line) !== 'y' && strtolower($line) !== 'yes') {
        echo "Opération annulée.\n";
        exit(0);
    }
    
    // Mise à jour du mot de passe
    $passwordHash = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $pdo->prepare('UPDATE users SET password_hash = ?, display_name = ?, updated_at = NOW() WHERE username = ?');
    $stmt->execute([$passwordHash, $displayName, $username]);
    
    if ($stmt->rowCount() > 0) {
        echo "✓ Mot de passe et nom d'affichage mis à jour avec succès pour :\n";
        echo "  Username : $username\n";
        echo "  Display Name : $displayName\n";
    } else {
        die("Erreur : échec de la mise à jour.\n");
    }
    exit(0);
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
    echo "\nVous pouvez maintenant vous connecter avec ces identifiants.\n";
} else {
    die("Erreur : échec de la création de l'administrateur.\n");
}
