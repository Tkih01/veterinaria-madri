# Arquitectura de Software e Infraestructura — Veterinaria Madri

## 1. Stack Tecnológico y Patrón de Diseño
* **Arquitectura de Aplicación:** Patrón **MVC (Modelo-Vista-Controlador) puro** en PHP 8.2+ nativo bajo un único controlador frontal (`/public/index.php`). No existen scripts PHP secundarios accesibles directamente en el directorio público.
* **Base de Datos:** MariaDB / MySQL con motor **InnoDB**, codificación `utf8mb4_unicode_ci` y conexión centralizada mediante patrón **Singleton** sobre **PDO** nativo (`ATTR_EMULATE_PREPARES => false`, DSN con `charset=utf8mb4`).
* **Frontend:** HTML5 semántico, CSS3 puro modular con Custom Properties bajo **Grilla de 8 Puntos (WCAG 2.1 AA)**, JavaScript vanilla y mapas mediante **Leaflet.js + OpenStreetMap**.
* **Entorno Local vs. Producción:** Desarrollo en Windows con XAMPP (`http://vetmadri.test`) y despliegue en servidor Debian GNU/Linux con Apache2 (`VirtualHost` apuntando estrictamente a `/public`) y HTTPS mediante Certbot.

## 2. Estructura de Directorios y Aislamiento (Capa de Servidor)
```text
veterinaria-madri/
├── docs/               # Documentación técnica y legal (Fuera de DocumentRoot)
├── database/           # Esquemas SQL, seeds y scripts de permisos (Fuera de DocumentRoot)
├── src/                # Núcleo MVC de la aplicación (Fuera de DocumentRoot)
│   ├── config/         # Lectura de .env y clase Singleton Database.php
│   ├── controllers/    # Controladores públicos y administrativos
│   ├── middlewares/    # Control de sesión, RBAC (admin), CSRF y RateLimiter
│   ├── models/         # Entidades y consultas preparadas PDO
│   └── views/          # Plantillas HTML/PHP modulares (layouts, pages, admin)
└── public/             # Único directorio expuesto por Apache (DocumentRoot)
    ├── index.php       # Front Controller único
    ├── .htaccess       # Reescritura de URLs y cabeceras de seguridad
    └── assets/
        ├── css/        # Hojas de estilo modulares
        ├── js/         # Scripts del cliente
        ├── fonts/      # Tipografías .woff2 auto-alojadas
        └── img/
            └── productos/  # Imágenes del catálogo con ejecución de scripts bloqueada