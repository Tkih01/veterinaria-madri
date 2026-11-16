# Sistema Web y Expediente Clínico — Veterinaria Madri

Plataforma web institucional, catálogo vitrina de servicios/productos y sistema de consulta de historial clínico veterinario desarrollado bajo estándares empresariales de diseño (Grilla de 8 puntos, WCAG 2.1 AA), seguridad (OWASP) y privacidad (LFPDPPP).

- **Establecimiento:** Veterinaria Madri
- **Ubicación:** Ermita de San Sebastián 402, Villas de Ntra. Sra. de la Asunción, 20126 Aguascalientes, Ags.

## Estructura del Proyecto
- `/public` — Único directorio público expuesto por Apache (`index.php`, `.htaccess`, `assets/`).
- `/src` — Lógica de aplicación MVC (`config/`, `controllers/`, `middlewares/`, `models/`, `views/`).
- `/database` — Scripts SQL versionados (`schema.sql`, `seeds.sql`, `hardening.sql`).
- `/docs` — Documentación de arquitectura, seguridad, guía de estilos y marco legal (`/docs/legal`).

## Instalación Local (Windows + XAMPP)
1. Clonar el repositorio dentro de `C:\xampp\htdocs\veterinaria-madri`.
2. Copiar `.env.example` a `.env` y ajustar las credenciales locales de MariaDB/MySQL.
3. Configurar el `VirtualHost` en `httpd-vhosts.conf` apuntando a `C:/xampp/htdocs/veterinaria-madri/public` bajo el dominio local `http://vetmadri.test`.
4. Importar los scripts de `/database/` en phpMyAdmin o consola MySQL.