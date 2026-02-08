<?php
/**
 * Point d'entrée unique — racine web = public/ (recommandé)
 * DocumentRoot doit pointer vers ce dossier (ex. .../Gestion_probleme_RGPL2024/public)
 * URLs : http://gestion.dgs.capi/ (sans /public, sans sous-dossier)
 */
define('WEB_ROOT_IS_PUBLIC', true);
require __DIR__ . '/../config/app.php';
require __DIR__ . '/../bootstrap.php';
