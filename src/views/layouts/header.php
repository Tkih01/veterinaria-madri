<?php
use function App\Config\e;
?>
<!DOCTYPE html>
<html lang="es-MX">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Veterinaria Madri en Aguascalientes. Consulta médica, vacunación, desparasitación, catálogo de productos y consulta de expediente clínico digital.">
    <title><?= e($pageTitle ?? 'Veterinaria Madri') ?> — Veterinaria Madri</title>
    <link rel="stylesheet" href="/assets/css/main.css">
</head>
<body>
    <a href="#contenido-principal" class="skip-link">Saltar al contenido principal</a>

    <header class="site-header">
        <div class="container navbar">
            <a href="/" class="brand-logo" aria-label="Veterinaria Madri - Ir a Inicio">
                <span class="brand-mark" aria-hidden="true">VM</span>
                <span>Veterinaria Madri</span>
            </a>

            <nav aria-label="Navegación principal">
                <ul class="nav-links">
                    <li>
                        <a href="/" class="nav-link" <?= ($currentPage === 'inicio') ? 'aria-current="page"' : '' ?>>Inicio</a>
                    </li>
                    <li>
                        <a href="/servicios" class="nav-link" <?= ($currentPage === 'servicios') ? 'aria-current="page"' : '' ?>>Servicios</a>
                    </li>
                    <li>
                        <a href="/productos" class="nav-link" <?= ($currentPage === 'productos') ? 'aria-current="page"' : '' ?>>Productos</a>
                    </li>
                    <li>
                        <a href="/ubicacion" class="nav-link" <?= ($currentPage === 'ubicacion') ? 'aria-current="page"' : '' ?>>Ubicación</a>
                    </li>
                    <li>
                        <a href="/expediente" class="btn btn-primary">
                            Consultar Mascota
                        </a>
                    </li>
                </ul>
            </nav>
        </div>
    </header>

    <main id="contenido-principal">