<?php
declare(strict_types=1);

namespace App\Middlewares;

use App\Config\Database;
use App\Config\Env;

/**
 * Control contra ataques de fuerza bruta y enumeración de folios (OWASP A01 / A07)
 */
final class RateLimiter
{
    private static function getIpHash(): string
    {
        $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
        $secret = (string) Env::get('CSRF_TOKEN_SECRET', 'vetmadri_salt_2026');
        return hash('sha256', $ip . '|' . $secret);
    }

    public static function isBlocked(string $action): bool
    {
        $db = Database::getConnection();
        $stmt = $db->prepare("
            SELECT bloqueado_hasta
            FROM auditoria_accesos
            WHERE ip_hash = :ip_hash AND accion = :accion
            LIMIT 1
        ");
        $stmt->execute([
            ':ip_hash' => self::getIpHash(),
            ':accion'  => $action,
        ]);
        $row = $stmt->fetch();

        if (!$row || empty($row['bloqueado_hasta'])) {
            return false;
        }

        if (strtotime((string) $row['bloqueado_hasta']) > time()) {
            return true;
        }

        // Si el tiempo de bloqueo ya expiró, limpiar el registro
        self::clear($action);
        return false;
    }

    public static function registerFailure(string $action, int $maxAttempts = 5, int $lockMinutes = 15): void
    {
        $db = Database::getConnection();
        $ipHash = self::getIpHash();

        $stmt = $db->prepare("
            SELECT id, intentos
            FROM auditoria_accesos
            WHERE ip_hash = :ip_hash AND accion = :accion
            LIMIT 1
        ");
        $stmt->execute([
            ':ip_hash' => $ipHash,
            ':accion'  => $action,
        ]);
        $row = $stmt->fetch();

        if (!$row) {
            $ins = $db->prepare("
                INSERT INTO auditoria_accesos (ip_hash, accion, intentos, bloqueado_hasta)
                VALUES (:ip_hash, :accion, 1, NULL)
            ");
            $ins->execute([
                ':ip_hash' => $ipHash,
                ':accion'  => $action,
            ]);
            return;
        }

        $nuevosIntentos = ((int) $row['intentos']) + 1;
        $bloqueadoHasta = ($nuevosIntentos >= $maxAttempts)
            ? date('Y-m-d H:i:s', time() + ($lockMinutes * 60))
            : null;

        $upd = $db->prepare("
            UPDATE auditoria_accesos
            SET intentos = :intentos, bloqueado_hasta = :bloqueado
            WHERE id = :id
        ");
        $upd->execute([
            ':intentos'  => $nuevosIntentos,
            ':bloqueado' => $bloqueadoHasta,
            ':id'        => $row['id'],
        ]);
    }

    public static function clear(string $action): void
    {
        $db = Database::getConnection();
        $stmt = $db->prepare("
            DELETE FROM auditoria_accesos
            WHERE ip_hash = :ip_hash AND accion = :accion
        ");
        $stmt->execute([
            ':ip_hash' => self::getIpHash(),
            ':accion'  => $action,
        ]);
    }
}