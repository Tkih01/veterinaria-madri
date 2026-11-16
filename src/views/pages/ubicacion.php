<?php
use function App\Config\e;
?>
<!-- Hoja de estilos local de Leaflet (BSD-2-Clause) -->
<link rel="stylesheet" href="/assets/vendor/leaflet/leaflet.css">

<section class="page-header">
    <div class="container">
        <span class="hero-eyebrow">Visítanos en Aguascalientes</span>
        <h1>Ubicación y Contacto</h1>
        <p style="margin-top: var(--space-1); margin-bottom: 0;">
            Encuéntranos fácilmente en el Fraccionamiento Villas de Nuestra Señora de la Asunción.
        </p>
    </div>
</section>

<section class="section">
    <div class="container location-grid">
        <!-- Columna de Información Física -->
        <div class="card">
            <h2 style="font-size: var(--text-xl); margin-bottom: var(--space-2);">Veterinaria Madri</h2>

            <div style="margin-bottom: var(--space-3);">
                <h3 style="font-size: var(--text-base); color: var(--color-primary); margin-bottom: var(--space-0-5);">
                    Dirección Física
                </h3>
                <p style="margin-bottom: 0;">
                    Ermita de San Sebastián 402,<br>
                    Fracc. Villas de Ntra. Sra. de la Asunción,<br>
                    C.P. 20126, Aguascalientes, Ags., México.
                </p>
            </div>

            <div style="margin-bottom: var(--space-3);">
                <h3 style="font-size: var(--text-base); color: var(--color-primary); margin-bottom: var(--space-0-5);">
                    Horarios de Atención
                </h3>
                <p style="margin-bottom: var(--space-0-5); font-size: var(--text-sm);">
                    <strong>Lunes a Viernes:</strong> Horario en mostrador
                </p>
                <p style="margin-bottom: 0; font-size: var(--text-sm);">
                    <strong>Sábados:</strong> Horario en mostrador
                </p>
            </div>

            <div style="margin-bottom: var(--space-3);">
                <h3 style="font-size: var(--text-base); color: var(--color-primary); margin-bottom: var(--space-0-5);">
                    Teléfono y Contacto
                </h3>
                <p style="margin-bottom: 0; font-size: var(--text-sm); color: var(--color-text-muted);">
                    <em>(Datos telefónicos y WhatsApp por confirmar en clínica)</em>
                </p>
            </div>

            <a
                href="https://www.google.com/maps/search/?api=1&query=Ermita+de+San+Sebastian+402+Villas+de+Nuestra+Senora+de+la+Asuncion+20126+Aguascalientes"
                target="_blank"
                rel="noopener noreferrer"
                class="btn btn-primary"
                style="width: 100%;"
            >
                Abrir Ruta en Google Maps &rarr;
            </a>
        </div>

        <!-- Columna del Mapa Interactivo (OpenStreetMap) -->
        <div>
            <div
                id="mapa-veterinaria"
                class="map-frame"
                data-lat="21.929172"
                data-lng="-102.261747"
                data-title="Veterinaria Madri"
                data-address="Ermita de San Sebastián 402, C.P. 20126, Aguascalientes, Ags."
                role="region"
                aria-label="Mapa interactivo de ubicación de Veterinaria Madri"
            ></div>
            <p style="margin-top: var(--space-1); font-size: var(--text-xs); color: var(--color-text-muted);">
                Cartografía provista por <strong>&copy; OpenStreetMap contributors</strong> (Licencia ODbL) mediante Leaflet.js alojado localmente. Al visualizar el mapa, tu navegador descarga los mosaicos cartográficos directamente desde los servidores públicos de OpenStreetMap conforme a nuestro <a href="/aviso-privacidad">Aviso de Privacidad</a>.
            </p>
        </div>
    </div>
</section>

<!-- Scripts locales de Leaflet y configuración del mapa -->
<script src="/assets/vendor/leaflet/leaflet.js"></script>
<script src="/assets/js/map.js"></script>