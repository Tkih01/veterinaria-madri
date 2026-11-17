-- ============================================================================
-- DATOS SEMILLA INICIALES (SEEDS) - VETERINARIA MADRI
-- ============================================================================

USE `vetmadri_db`;

-- 1. Usuario Administrador Inicial
-- Correo: admin@veterinariamadri.mx | Contraseña temporal: AdminMadri2026!
INSERT INTO `usuarios` (`id`, `nombre_completo`, `email`, `password_hash`, `rol`, `activo`) VALUES
(1, 'Médico Veterinario Titular', 'admin@veterinariamadri.mx', '$2y$10$wH8cK1rU2v9yP4qL7mN0xO3eR5tY6uI8oP9aS0dF1gH2jK3lM4nO5', 'admin', 1)
ON DUPLICATE KEY UPDATE `nombre_completo` = VALUES(`nombre_completo`);

-- 2. Mascotas de Prueba (1 completa y 1 con datos escasos)
INSERT INTO `mascotas` (
    `id`, `codigo_unico`, `usuario_id`, `nombre_mascota`, `especie`, `raza`, `sexo`,
    `edad_aprox`, `peso_actual`, `esterilizado`, `alergias_notas`, `foto_webp`,
    `nombre_dueno`, `telefono_contacto`
) VALUES
(1, 'VM-7K4P9', NULL, 'Max', 'Perro', 'Mestizo / Labrador', 'Macho', '3 años', 22.50, 'Sí', 'Ninguna conocida', NULL, 'Andrés Israel Torres', '4491234821'),
(2, 'VM-3M9R2', NULL, 'Luna', 'Gato', NULL, 'Hembra', NULL, 3.80, NULL, NULL, NULL, 'María Fernanda López', '4499879014')
ON DUPLICATE KEY UPDATE `peso_actual` = VALUES(`peso_actual`);

-- 3. Historial Clínico de Prueba
INSERT INTO `historial_clinico` (
    `mascota_id`, `veterinario_id`, `fecha_atencion`, `tipo_atencion`,
    `titulo`, `descripcion`, `peso_consulta`, `proximo_refuerzo`
) VALUES
(1, 1, '2026-11-10', 'Vacunación', 'Refuerzo Vacuna Séxtuple + Antirrábica', 'Paciente sin reacciones adversas. Mucosas rosadas, temperatura 38.4 °C.', 22.50, '2027-11-10'),
(1, 1, '2026-05-14', 'Desparasitación', 'Desparasitación Interna (Tableta Oral)', 'Dosis administrada según peso corporal.', 21.80, NULL),
(1, 1, '2026-02-02', 'Consulta General', 'Revisión por otitis externa leve', 'Limpieza ótica en consultorio y aplicación de gotas óticas cada 12 horas por 7 días.', 21.50, NULL),
(2, 1, '2026-11-05', 'Consulta General', 'Valoración inicial de paciente rescatada', NULL, 3.80, '2026-11-20');

-- 4. Catálogo Inicial de Productos en Clínica
INSERT INTO `productos` (`sku`, `nombre`, `categoria`, `descripcion`, `precio_mxn`, `disponibilidad`, `imagen_webp`, `activo`) VALUES
('PROD-001', 'Antipulgas y Garrapatas Masticable (Perro Mediano 10-20 kg)', 'Farmacia y Prevención', 'Tableta masticable de acción mensual contra pulgas, garrapatas y ácaros.', 380.00, 'Disponible', NULL, 1),
('PROD-002', 'Pipeta Desparasitante Felina Multi-Espectro', 'Farmacia y Prevención', 'Aplicación tópica mensual para gatos de 2.5 a 7.5 kg.', 310.00, 'Disponible', NULL, 1),
('PROD-003', 'Alimento Seco Mantenimiento Adulto Raza Pequeña (Bulto 4 kg)', 'Nutrición Clínica', 'Fórmula balanceada de alta digestibilidad con proteínas seleccionadas y ácidos grasos Omega 3 y 6.', 540.00, 'Pocas unidades', NULL, 1),
('PROD-004', 'Shampoo Dermatológico Calmante con Avena (250 ml)', 'Higiene y Dermatología', 'Auxiliar en el cuidado de piel sensible o con prurito en perros y gatos.', 225.00, 'Disponible', NULL, 1),
('PROD-005', 'Collar Isabelino Transparente Ajustable (Talla M)', 'Recuperación y Accesorios', 'Protección postoperatoria cómoda con cierre seguro para evitar el lamido de heridas o suturas.', 145.00, 'Disponible', NULL, 1),
('PROD-006', 'Suplemento Articular con Condroitina y Glucosamina (60 tabs)', 'Suplementos', 'Apoyo nutricional para mascotas adultas mayores o en recuperación ortopédica.', 460.00, 'Agotado', NULL, 1)
ON DUPLICATE KEY UPDATE `precio_mxn` = VALUES(`precio_mxn`), `disponibilidad` = VALUES(`disponibilidad`);