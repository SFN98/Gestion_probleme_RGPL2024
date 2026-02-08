<?php
/**
 * Authentification contrôleurs — session, remember, déconnexion
 * Utilise MySQL si configuré, sinon JSON.
 */

class AuthService
{
    private static $sessionStarted = false;

    /**
     * Initialise la session si pas déjà démarrée
     */
    public static function initSession(): void
    {
        if (!self::$sessionStarted && session_status() === PHP_SESSION_NONE) {
            session_start();
            self::$sessionStarted = true;
        }
    }

    /**
     * Connexion utilisateur
     */
    public static function login(string $username, string $password, bool $remember = false): bool
    {
        self::initSession();
        $user = self::findUserByUsername($username);
        if (!$user || !password_verify($password, $user['password_hash'])) {
            return false;
        }
        $_SESSION['auth'] = [
            'user_id' => $user['id'],
            'username' => $user['username'],
            'display_name' => $user['display_name'] ?? $user['username'],
        ];
        if ($remember) {
            self::setRememberToken($user['id']);
        }
        return true;
    }

    /**
     * Déconnexion
     */
    public static function logout(): void
    {
        self::initSession();
        if (isset($_SESSION['auth']['user_id'])) {
            self::clearRememberToken($_SESSION['auth']['user_id']);
        }
        $_SESSION = [];
        if (isset($_COOKIE[session_name()])) {
            setcookie(session_name(), '', time() - 3600, '/');
        }
        if (isset($_COOKIE['remember_token'])) {
            setcookie('remember_token', '', time() - 3600, '/', '', false, true);
        }
        session_destroy();
        self::$sessionStarted = false;
    }

    /**
     * Vérifie si l'utilisateur est authentifié
     */
    public static function isAuthenticated(): bool
    {
        self::initSession();
        if (isset($_SESSION['auth']['user_id'])) {
            return true;
        }
        // Vérifier remember token
        if (isset($_COOKIE['remember_token'])) {
            return self::restoreSessionFromToken($_COOKIE['remember_token']);
        }
        return false;
    }

    /**
     * Retourne l'utilisateur actuel ou null
     */
    public static function getCurrentUser(): ?array
    {
        if (!self::isAuthenticated()) {
            return null;
        }
        return $_SESSION['auth'] ?? null;
    }

    /**
     * Redirige vers /login si non authentifié
     */
    public static function requireAuth(): void
    {
        if (!self::isAuthenticated()) {
            $redirect = $_SERVER['REQUEST_URI'] ?? '/dashboard';
            redirect(APP_BASE_URL . '/login?redirect=' . urlencode($redirect));
        }
    }

    /**
     * Trouve un utilisateur par username (MySQL ou JSON)
     */
    private static function findUserByUsername(string $username): ?array
    {
        $pdo = getDb();
        if ($pdo !== null) {
            $stmt = $pdo->prepare('SELECT id, username, password_hash, display_name FROM users WHERE username = ?');
            $stmt->execute([$username]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($row) {
                return [
                    'id' => (int) $row['id'],
                    'username' => $row['username'],
                    'password_hash' => $row['password_hash'],
                    'display_name' => $row['display_name'] ?? $row['username'],
                ];
            }
            return null;
        }
        $users = loadJsonFile(DATA_USERS);
        foreach ($users as $user) {
            if (isset($user['username']) && $user['username'] === $username) {
                return [
                    'id' => $user['id'] ?? 0,
                    'username' => $user['username'],
                    'password_hash' => $user['password_hash'] ?? '',
                    'display_name' => $user['display_name'] ?? $user['username'],
                ];
            }
        }
        return null;
    }

    /**
     * Crée un token remember et le stocke en BDD + cookie
     */
    private static function setRememberToken(int $userId): void
    {
        $token = bin2hex(random_bytes(32));
        $expires = date('Y-m-d H:i:s', time() + (30 * 24 * 60 * 60)); // 30 jours
        $pdo = getDb();
        if ($pdo !== null) {
            // Vérifier si colonnes existent (optionnel)
            try {
                $stmt = $pdo->prepare('UPDATE users SET remember_token = ?, remember_expires_at = ? WHERE id = ?');
                $stmt->execute([$token, $expires, $userId]);
            } catch (PDOException $e) {
                // Colonnes n'existent pas encore, ignorer
                return;
            }
        }
        setcookie('remember_token', $token, time() + (30 * 24 * 60 * 60), '/', '', false, true);
    }

    /**
     * Restaure la session depuis un remember token
     */
    private static function restoreSessionFromToken(string $token): bool
    {
        $pdo = getDb();
        if ($pdo !== null) {
            try {
                $stmt = $pdo->prepare('SELECT id, username, display_name FROM users WHERE remember_token = ? AND remember_expires_at > NOW()');
                $stmt->execute([$token]);
                $user = $stmt->fetch(PDO::FETCH_ASSOC);
                if ($user) {
                    $_SESSION['auth'] = [
                        'user_id' => (int) $user['id'],
                        'username' => $user['username'],
                        'display_name' => $user['display_name'] ?? $user['username'],
                    ];
                    return true;
                }
            } catch (PDOException $e) {
                // Colonnes n'existent pas encore
            }
        }
        // Supprimer cookie invalide
        setcookie('remember_token', '', time() - 3600, '/', '', false, true);
        return false;
    }

    /**
     * Supprime le remember token
     */
    private static function clearRememberToken(int $userId): void
    {
        $pdo = getDb();
        if ($pdo !== null) {
            try {
                $stmt = $pdo->prepare('UPDATE users SET remember_token = NULL, remember_expires_at = NULL WHERE id = ?');
                $stmt->execute([$userId]);
            } catch (PDOException $e) {
                // Colonnes n'existent pas encore
            }
        }
    }
}
