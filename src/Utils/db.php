<?php
/**
 * Connexion PDO à la base de données (MySQL/MariaDB)
 *
 * Utilisation : $pdo = getDb(); si ($pdo) { ... }
 * Si config/database.php n'est pas chargé ou que la connexion échoue, getDb() retourne null.
 */

/**
 * Retourne une instance PDO ou null si la base n'est pas configurée / indisponible.
 *
 * @return PDO|null
 */
function getDb(): ?PDO
{
    if (!defined('DB_HOST') || !defined('DB_NAME')) {
        return null;
    }

    static $pdo = null;
    if ($pdo !== null) {
        return $pdo;
    }

    try {
        $dsn = sprintf(
            'mysql:host=%s;port=%s;dbname=%s;charset=%s',
            DB_HOST,
            defined('DB_PORT') ? DB_PORT : '3306',
            DB_NAME,
            defined('DB_CHARSET') ? DB_CHARSET : 'utf8mb4'
        );
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ];
        $pdo = new PDO(
            $dsn,
            defined('DB_USER') ? DB_USER : 'root',
            defined('DB_PASSWORD') ? DB_PASSWORD : '',
            $options
        );
        return $pdo;
    } catch (PDOException $e) {
        if (defined('BASE_PATH') && (getenv('APP_DEBUG') === '1' || (defined('APP_DEBUG') && constant('APP_DEBUG')))) {
            throw $e;
        }
        return null;
    }
}
