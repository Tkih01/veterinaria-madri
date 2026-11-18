<?php
declare(strict_types=1);

/**
 * ============================================================================
 * FRONT CONTROLLER ÚNICO - VETERINARIA MADRI
 * Todas las peticiones HTTP pasan por este punto de entrada.
 * ============================================================================
 */

define('BASE_PATH', dirname(__DIR__));

require_once BASE_PATH . '/src/config/Env.php';
require_once BASE_PATH . '/src/config/Database.php';

use App\Config\Env;

// 1. Cargar variables de entorno desde /.env
Env::load(BASE_PATH . '/.env');

// 2. Configurar zona horaria y manejo de errores según entorno
date_default_timezone_set((string) Env::get('APP_TIMEZONE', 'America/Mexico_City'));
$isDebug = filter_var(Env::get('APP_DEBUG', false), FILTER_VALIDATE_BOOLEAN);

if ($isDebug) {
    ini_set('display_errors', '1');
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', '0');
    error_reporting(0);
}

// 3. Endurecimiento de Cookies de Sesión (OWASP A07)
$isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
    || (isset($_SERVER['SERVER_PORT']) && (int) $_SERVER['SERVER_PORT'] === 443);

session_set_cookie_params([
    'lifetime' => (int) Env::get('SESSION_LIFETIME', 3600),
    'path'     => '/',
    'domain'   => '',
    'secure'   => $isHttps,
    'httponly' => true,
    'samesite' => 'Strict',
]);

if (session_status() === PHP_SESSION_NONE) {
    session_name('VETMADRI_SESSID');
    session_start();
}

// 4. Cabeceras HTTP de Seguridad (OWASP A05)
header("Content-Type: text/html; charset=UTF-8");
header("X-Frame-Options: SAMEORIGIN");
header("X-Content-Type-Options: nosniff");
header("Referrer-Policy: strict-origin-when-cross-origin");
// Content-Security-Policy permitiendo recursos propios y mosaicos de OpenStreetMap
header("Content-Security-Policy: default-src 'self'; img-src 'self' data: https://*.tile.openstreetmap.org; style-src 'self' 'unsafe-inline'; script-src 'self'; font-src 'self'; frame-ancestors 'self';");

if ($isHttps) {
    header("Strict-Transport-Security: max-age=31536000; includeSubDomains");
}

// 5. Obtener ruta limpia solicitada
$rawUrl = $_GET['url'] ?? '';
$route  = trim( filter_var($rawUrl, FILTER_SANITIZE_URL), '/' );
// 6. Mapa de rutas públicas (Fase 2)

$routes = [
    ''                     => ['view' => 'pages/home.php',       'title' => 'Inicio | Atención Veterinaria en Aguascalientes'],
    'servicios'            => ['view' => 'pages/servicios.php',  'title' => 'Servicios Clínicos y Preventivos'],
    'productos'            => ['view' => 'pages/productos.php',  'title' => 'Catálogo de Productos en Clínica'],
    'ubicacion'            => ['view' => 'pages/ubicacion.php',  'title' => 'Ubicación y Horarios de Atención'],
    'expediente'           => ['view' => 'pages/expediente.php', 'title' => 'Consulta de Historial Clínico de Mascotas'],
    'aviso-privacidad'     => ['view' => 'pages/privacidad.php', 'title' => 'Aviso de Privacidad Integral'],
    'terminos-condiciones' => ['view' => 'pages/terminos.php',   'title' => 'Términos y Condiciones de Uso'],
];

if (array_key_exists($route, $routes)) {
    $pageTitle   = $routes[$route]['title'];
    $currentPage = $route === '' ? 'inicio' : $route;
    $viewFile    = BASE_PATH . '/src/views/' . $routes[$route]['view'];

    require_once BASE_PATH . '/src/views/layouts/header.php';
    if (is_readable($viewFile)) {
        require_once $viewFile;
    } else {
        echo '<section class="section container"><div class="card"><h1>Sección en construcción</h1><p>Esta vista se habilitará en el siguiente bloque de la Fase 2.</p></div></section>';
    }
    require_once BASE_PATH . '/src/views/layouts/footer.php';
} else {
    http_response_code(404);
    $pageTitle   = 'Página no encontrada (404)';
    $currentPage = '404';
    require_once BASE_PATH . '/src/views/layouts/header.php';
    echo '<section class="section container"><div class="card"><h1>Error 404 — Página no encontrada</h1><p>La dirección solicitada no existe en el portal de Veterinaria Madri.</p><a href="/" class="btn btn-primary">Volver al Inicio</a></div></section>';
    require_once BASE_PATH . '/src/views/layouts/footer.php';
}