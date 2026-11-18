-- ============================================================================
-- HARDENING DE BASE DE DATOS Y PRINCIPIO DE MÍNIMO PRIVILEGIO (OWASP A05)
-- ============================================================================

-- 1. Usuario exclusivo para la aplicación web (Sin permisos DROP, ALTER ni GRANT)
CREATE USER IF NOT EXISTS 'vetmadri_app'@'localhost' IDENTIFIED BY 'CambiaEstaClaveSegura_App2026!';
GRANT SELECT, INSERT, UPDATE, DELETE ON `vetmadri_db`.* TO 'vetmadri_app'@'localhost';

-- 2. Usuario administrativo para migraciones desde consola SSH
CREATE USER IF NOT EXISTS 'vetmadri_admin'@'localhost' IDENTIFIED BY 'CambiaEstaClaveSegura_Admin2026!';
GRANT ALL PRIVILEGES ON `vetmadri_db`.* TO 'vetmadri_admin'@'localhost' WITH GRANT OPTION;

FLUSH PRIVILEGES;