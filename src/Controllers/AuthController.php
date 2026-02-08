<?php
/**
 * Actions d'authentification — déconnexion
 */

AuthService::logout();
redirect(APP_BASE_URL . '/login');
