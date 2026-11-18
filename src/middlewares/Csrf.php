<?php
declare(strict_types=1);

namespace App\Middlewares;

/**
 * Protección contra Cross-Site Request Forgery (OWASP ASVS V4)
 */
final class Csrf
{
    public static function token(): string
    {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return (string) $_SESSION['csrf_token'];
    }

    public static function field(): string
    {
        $token = htmlspecialchars(self::token(), ENT_QUOTES, 'UTF-8');
        return '<input type="hidden" name="csrf_token" value="' . $token . '">';
    }

    public static function validate(?string $submittedToken): bool
    {
        if (empty($_SESSION['csrf_token']) || empty($submittedToken)) {
            return false;
        }
        return hash_equals((string) $_SESSION['csrf_token'], $submittedToken);
    }
}