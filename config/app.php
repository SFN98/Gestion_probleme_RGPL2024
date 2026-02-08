<?php
/**
 * Configuration de l'application — constantes, chemins, options
 *
 * Deux modes :
 * - Racine web = public/ (recommandé) : définir WEB_ROOT_IS_PUBLIC dans public/index.php
 *   → APP_BASE_URL = '', PUBLIC_URL = '' (assets en /css/, /js/, etc.)
 * - Racine web = dossier projet (sous-dossier type .../Gestion_probleme_RGPL2024/)
 *   → APP_BASE_URL = '/Gestion_probleme_RGPL2024', PUBLIC_URL = .../public
 */

define('BASE_PATH', dirname(__DIR__));
define('PUBLIC_PATH', BASE_PATH . '/public');

// Connexion base de données (optionnel — si config/database.php existe)
if (is_file(__DIR__ . '/database.php')) {
    require __DIR__ . '/database.php';
}
define('DATA_PATH', BASE_PATH . '/data');
define('TEMPLATES_PATH', BASE_PATH . '/templates');
define('STORAGE_PATH', BASE_PATH . '/storage');

if (defined('WEB_ROOT_IS_PUBLIC') && WEB_ROOT_IS_PUBLIC) {
    /** Racine web = public/ : pas de préfixe dans les URLs */
    define('APP_BASE_URL', '');
    define('PUBLIC_URL', '');
} else {
    /** Racine web = dossier projet (sous-dossier) */
    define('APP_BASE_URL', '/Gestion_probleme_RGPL2024');
    define('PUBLIC_URL', rtrim(APP_BASE_URL, '/') . '/public');
}
define('UPLOAD_PATH_TICKETS', PUBLIC_PATH . '/uploads/tickets');
define('UPLOAD_PATH_PDF_LIBRARY', PUBLIC_PATH . '/uploads/pdf-library');

define('UPLOAD_MAX_SIZE_TICKET_MB', 5);
define('UPLOAD_MAX_SIZE_PDF_MB', 20);

define('DATA_TICKETS', DATA_PATH . '/tickets.json');
define('DATA_FAQ', DATA_PATH . '/faq.json');
define('DATA_PDF_LIBRARY', DATA_PATH . '/pdf-library.json');
define('DATA_USERS', DATA_PATH . '/users.json');
