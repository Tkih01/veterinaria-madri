# Historial de Cambios (Changelog) — Veterinaria Madri

Todos los cambios relevantes de este proyecto se documentan en este archivo siguiendo el estándar [Keep a Changelog](https://keepachangelog.com/es-ES/1.0.0/) y [Semantic Versioning](https://semver.org/lang/es/).

## [0.1.0] - 2026-11-17

### Agregado (Added)
- **Fase 1 (Arquitectura, Diseño y Marco Legal):**
  - Estructura MVC con aislamiento de directorio público (`/public`).
  - Sistema de diseño CSS bajo grilla de 8 puntos, contraste WCAG 2.1 AA y fuentes locales (`Montserrat` e `Inter` en `.woff2` bajo licencia SIL OFL 1.1).
  - Documentación de arquitectura (`docs/architecture.md`), política OWASP (`docs/security-policy.md`), Aviso de Privacidad (LFPDPPP) y Términos y Condiciones (LFPC / PROFECO / SENASICA).
- **Fase 2 (Portal Web Público):**
  - Controlador frontal único (`public/index.php`) con cabeceras HTTP de seguridad y `Content-Security-Policy`.
  - Vistas públicas de Inicio, Servicios, Catálogo Vitrina de Productos, Ubicación interactiva con Leaflet.js + OpenStreetMap y visor de expediente clínico con enmascaramiento de datos.
- **Fase 3 (Base de Datos MariaDB):**
  - Esquema relacional 3FN en `utf8mb4` (`database/schema.sql`), datos semilla (`database/seeds.sql`), script de privilegios mínimos (`database/hardening.sql`) y conexión Singleton PDO.
- **Fase 4 (Panel Clínico Administrativo y Seguridad Backend):**
  - Autenticación con migración automática a `Argon2id`, protección anti-CSRF (`Csrf.php`) y bloqueo contra fuerza bruta (`RateLimiter.php`).
  - CRUD de mascotas con generación de folios aleatorios (`VM-XXXXX`), conversión de fotografías a `.webp` sin metadatos EXIF/GPS (`ImageUploader.php`), código QR local imprimible y gestión del catálogo de productos.