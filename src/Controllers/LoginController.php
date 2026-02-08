<?php
/**
 * Connexion administrateur (5.4) — authentification contrôleur
 */

$error = null;
$redirect = $_GET['redirect'] ?? '/dashboard';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $remember = isset($_POST['remember']) && $_POST['remember'] === '1';
    
    if (empty($username) || empty($password)) {
        $error = 'Veuillez remplir tous les champs.';
    } else {
        if (AuthService::login($username, $password, $remember)) {
            $target = !empty($_POST['redirect']) ? $_POST['redirect'] : $redirect;
            redirect(APP_BASE_URL . $target);
        } else {
            $error = 'Identifiant ou mot de passe incorrect.';
        }
    }
}

// Si déjà connecté, rediriger vers dashboard
if (AuthService::isAuthenticated()) {
    redirect(APP_BASE_URL . '/dashboard');
}
