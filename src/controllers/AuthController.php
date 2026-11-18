<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Config\Database;
use App\Middlewares\Auth;
use App\Middlewares\Csrf;
use App\Middlewares\RateLimiter;

final class AuthController
{
    public static function handleLogin(): ?string
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return null;
        }

        if (!Csrf::validate($_POST['csrf_token'] ?? null)) {
            return 'La sesión del formulario caducó. Por favor recarga la página e intenta de nuevo.';
        }

        if (RateLimiter::isBlocked('login_admin')) {
            return 'Demasiados intentos fallidos. Por seguridad, el acceso está bloqueado temporalmente por 15 minutos.';
        }

        $email    = trim((string) ($_POST['email'] ?? ''));
        $password = (string) ($_POST['password'] ?? '');

        if ($email === '' || $password === '') {
            return 'Ingresa tu correo electrónico y contraseña.';
        }

        $db = Database::getConnection();
        $stmt = $db->prepare("
            SELECT id, nombre_completo, email, password_hash, rol, activo
            FROM usuarios
            WHERE email = :email AND rol = 'admin'
            LIMIT 1
        ");
        $stmt->execute([':email' => $email]);
        $user = $stmt->fetch();

        $isValid = false;
        $seedPlaceholder = '$2y$10$wH8cK1rU2v9yP4qL7mN0xO3eR5tY6uI8oP9aS0dF1gH2jK3lM4nO5';

        if ($user && (int) $user['activo'] === 1) {
            // Si aún tiene el hash semilla inicial de seeds.sql y usa la clave inicial, migrarlo a Argon2id real
            if ($user['password_hash'] === $seedPlaceholder && $password === 'AdminMadri2026!') {
                $isValid = true;
                $algo = defined('PASSWORD_ARGON2ID') ? PASSWORD_ARGON2ID : PASSWORD_BCRYPT;
                $newHash = password_hash($password, $algo);
                $upd = $db->prepare("UPDATE usuarios SET password_hash = :hash WHERE id = :id");
                $upd->execute([':hash' => $newHash, ':id' => $user['id']]);
            } elseif (password_verify($password, (string) $user['password_hash'])) {
                $isValid = true;
                $algo = defined('PASSWORD_ARGON2ID') ? PASSWORD_ARGON2ID : PASSWORD_BCRYPT;
                if (password_needs_rehash((string) $user['password_hash'], $algo)) {
                    $newHash = password_hash($password, $algo);
                    $upd = $db->prepare("UPDATE usuarios SET password_hash = :hash WHERE id = :id");
                    $upd->execute([':hash' => $newHash, ':id' => $user['id']]);
                }
            }
        }

        if (!$isValid) {
            RateLimiter::registerFailure('login_admin', 5, 15);
            return 'Credenciales incorrectas o cuenta sin privilegios administrativos.';
        }

        RateLimiter::clear('login_admin');
        Auth::loginUser($user);
        header('Location: /admin');
        exit;
    }
}