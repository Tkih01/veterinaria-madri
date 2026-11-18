<?php
declare(strict_types=1);

namespace App\Config;

use PDO;
use PDOException;
use RuntimeException;

/**
 * Conexión Singleton a MariaDB/MySQL mediante PDO nativo (OWASP A03)
 */
final class Database
{
    private static ?PDO $instance = null;

    private function __construct() {}
    private function __clone() {}

    public static function getConnection(): PDO
    {
        if (self::$instance === null) {
            $host    = (string) Env::get('DB_HOST', '127.0.0.1');
            $port    = (string) Env::get('DB_PORT', '3306');
            $dbName  = (string) Env::get('DB_DATABASE', 'vetmadri_db');
            $charset = (string) Env::get('DB_CHARSET', 'utf8mb4');
            $user    = (string) Env::get('DB_USERNAME', 'root');
            $pass    = (string) Env::get('DB_PASSWORD', '');

            $dsn = "mysql:host={$host};port={$port};dbname={$dbName};charset={$charset}";

            $options = [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES   => false,
                    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci",
                ];

            try {
                self::$instance = new PDO($dsn, $user, $pass, $options);
            } catch (PDOException $e) {
                $isDebug = filter_var(Env::get('APP_DEBUG', false), FILTER_VALIDATE_BOOLEAN);
                if ($isDebug) {
                    throw new RuntimeException('Error de conexión a Base de Datos: ' . $e->getMessage());
                }
                throw new RuntimeException('El servicio de consulta no está disponible temporalmente.');
            }
        }

        return self::$instance;
    }
}