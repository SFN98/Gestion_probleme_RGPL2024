<?php
/**
 * Routage et dispatch — inclus après config/app.php
 * Utilisé par index.php (racine) et public/index.php (racine web = public)
 */
$requestUri = $_SERVER['REQUEST_URI'] ?? '/';
$path = parse_url($requestUri, PHP_URL_PATH);
if (defined('APP_BASE_URL') && APP_BASE_URL !== '' && strpos($path, APP_BASE_URL) === 0) {
    $path = substr($path, strlen(APP_BASE_URL)) ?: '/';
}
$path = rtrim($path, '/') ?: '/';

// Servir les assets (CSS, JS, images) depuis public/ — ne plus dépendre d'Apache pour /public/*
if (strpos($path, '/public/') === 0) {
    $assetPath = PUBLIC_PATH . substr($path, 7); // enlève "/public"
    $assetPath = realpath($assetPath);
    $publicReal = realpath(PUBLIC_PATH);
    if ($assetPath !== false && $publicReal !== false && strpos(str_replace('\\', '/', $assetPath), str_replace('\\', '/', $publicReal)) === 0 && is_file($assetPath)) {
        $ext = pathinfo($assetPath, PATHINFO_EXTENSION);
        $types = [
            'css' => 'text/css',
            'js' => 'application/javascript',
            'png' => 'image/png',
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'gif' => 'image/gif',
            'ico' => 'image/x-icon',
            'webp' => 'image/webp',
            'svg' => 'image/svg+xml',
            'woff' => 'font/woff',
            'woff2' => 'font/woff2',
            'pdf' => 'application/pdf',
        ];
        $mime = $types[$ext] ?? 'application/octet-stream';
        header('Content-Type: ' . $mime);
        header('Content-Length: ' . filesize($assetPath));
        readfile($assetPath);
        return;
    }
}

$routes = [
    '/' => ['HomeController', 'index'],
    '/recherche' => ['SearchController', 'index'],
    '/soumettre' => ['SubmitController', 'index'],
    '/ticket' => ['TicketCheckController', 'index'],
    '/login' => ['LoginController', 'index'],
    '/logout' => ['AuthController', 'logout'],
    '/dashboard' => ['DashboardController', 'index'],
];

$route = $routes[$path] ?? null;

if ($route === null) {
    http_response_code(404);
    $pageTitle = 'Page non trouvée — Plateforme RGPL 2024';
    $contentView = '404';
    require TEMPLATES_PATH . '/layouts/layout.php';
    return;
}

require_once BASE_PATH . '/src/Utils/helpers.php';
require_once BASE_PATH . '/src/Utils/db.php';
require_once BASE_PATH . '/src/Utils/ProvinceHelper.php';
require_once BASE_PATH . '/src/Services/AuthService.php';
require_once BASE_PATH . '/src/Services/TicketService.php';
require_once BASE_PATH . '/src/Services/FaqService.php';
require_once BASE_PATH . '/src/Services/PdfLibraryService.php';

// Initialiser la session
AuthService::initSession();

// Protection de la route /dashboard
if ($path === '/dashboard' && !AuthService::isAuthenticated()) {
    $redirect = $requestUri;
    redirect(APP_BASE_URL . '/login?redirect=' . urlencode($redirect));
}

$controllerFile = BASE_PATH . '/src/Controllers/' . $route[0] . '.php';

if (!is_file($controllerFile)) {
    http_response_code(500);
    echo 'Contrôleur introuvable';
    return;
}

$controllerName = $route[0];
require $controllerFile;

$titles = [
    'HomeController' => 'Accueil — Plateforme RGPL 2024',
    'SearchController' => 'Rechercher une solution',
    'SubmitController' => 'Soumettre un problème',
    'TicketCheckController' => 'Consulter mon ticket',
    'LoginController' => 'Connexion',
    'AuthController' => 'Déconnexion',
    'DashboardController' => 'Dashboard',
];
$views = [
    'HomeController' => 'home',
    'SearchController' => 'search',
    'SubmitController' => 'submit',
    'TicketCheckController' => 'ticket-check',
    'LoginController' => 'login',
    'AuthController' => null, // Pas de vue pour logout
    'DashboardController' => 'dashboard',
];
$pageTitle = $titles[$controllerName] ?? 'Plateforme RGPL 2024';
$contentView = $views[$controllerName] ?? 'home';
$currentUser = AuthService::getCurrentUser();

// AuthController n'a pas de vue (redirection)
if ($contentView === null) {
    return;
}

require TEMPLATES_PATH . '/layouts/layout.php';
