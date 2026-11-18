<?php
declare(strict_types=1);

namespace App\Middlewares;

use App\Config\Env;

/**
 * Control de sesión administrativa y RBAC (OWASP A01 / A07)
 */
final class Auth
{
    public static function checkAdmin(): bool
    {
        if (empty($_SESSION['user_id']) || ($_SESSION['user_role'] ?? '') !== 'admin') {
            return false;
        }

        $lifetime = (int) Env::get('SESSION_LIFETIME', 3600);
        if (isset($_SESSION['last_activity']) && (time() - (int) $_SESSION['last_activity']) > $lifetime) {
            self::logout();
            return false;
        }

        $_SESSION['last_activity'] = time();
        return true;
    }

    public static function requireAdmin(): void
    {
        if (!self::checkAdmin()) {
            header('Location: /admin/login');
            exit;
        }
    }

    public static function loginUser(array $user): void
    {
        session_regenerate_id(true);
        $_SESSION['user_id']       = (int) $user['id'];
        $_SESSION['user_name']     = (string) $user['nombre_completo'];
        $_SESSION['user_email']    = (string) $user['email'];
        $_SESSION['user_role']     = (string) $user['rol'];
        $_SESSION['last_activity'] = time();
    }

    public static function logout(): void
    {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params['path'],
                $params['domain'],
                $params['secure'],
                $params['httponly']
            );
        }
        session_destroy();
    }
}
