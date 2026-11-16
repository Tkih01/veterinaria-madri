<?php
use function App\Config\e;

/**
 * Función de enmascaramiento de datos personales (LFPDPPP)
 * Convierte "Andrés Israel Torres" -> "Andrés T. ***"
 */
function enmascararPropietario(string $nombreCompleto): string
{
    $partes = preg_split('/\s+/', trim($nombreCompleto));
    if (!$partes || count($partes) === 0) {
        return 'Propietario registrado ***';
    }
    $primerNombre = $partes[0];
    $inicialApellido = isset($partes[1]) ? mb_substr($partes[1], 0, 1, 'UTF-8') . '.' : '';
    return trim("{$primerNombre} {$inicialApellido} ***");
}

// Datos de demostración local (En la Fase 4 se reemplazarán por consultas PDO a MariaDB)
$expedientesDemo = [
    'VM-7K4P9' => [
        'codigo_unico'   => 'VM-7K4P9',
        'nombre_mascota' => 'Max',
        'especie'        => 'Perro',
        'raza'           => 'Mestizo / Labrador',
        'sexo'           => 'Macho',
        'edad'           => '3 años',
        'peso_actual'    => '22.5 kg',
        'esterilizado'   => 'Sí',
        'alergias'       => 'Ninguna conocida',
        'nombre_dueno'   => 'Andrés Israel Torres',
        'telefono_ult4'  => '4821',
        'historial'      => [
            [
                'fecha'            => '10/11/2026',
                'tipo'             => 'Vacunación',
                'badge_class'      => 'badge-ok',
                'dot_class'        => 'timeline-dot-ok',
                'titulo'           => 'Refuerzo Vacuna Séxtuple + Antirrábica',
                'descripcion'      => 'Paciente sin reacciones adversas. Mucosas rosadas, temperatura 38.4 °C.',
                'peso_consulta'    => '22.5 kg',
                'proximo_refuerzo' => '10/11/2027'
            ],
            [
                'fecha'            => '14/05/2026',
                'tipo'             => 'Desparasitación',
                'badge_class'      => 'badge-ok',
                'dot_class'        => 'timeline-dot-ok',
                'titulo'           => 'Desparasitación Interna (Tableta Oral)',
                'descripcion'      => 'Dosis administrada según peso corporal.',
                'peso_consulta'    => '21.8 kg',
                'proximo_refuerzo' => null
            ],
            [
                'fecha'            => '02/02/2026',
                'tipo'             => 'Consulta General',
                'badge_class'      => 'badge-danger',
                'dot_class'        => '',
                'titulo'           => 'Revisión por otitis externa leve',
                'descripcion'      => 'Limpieza ótica en consultorio y aplicación de gotas óticas cada 12 horas por 7 días.',
                'peso_consulta'    => '21.5 kg',
                'proximo_refuerzo' => null
            ],
        ]
    ],
    // Caso de prueba con datos escasos (campos opcionales en NULL)
    'VM-3M9R2' => [
        'codigo_unico'   => 'VM-3M9R2',
        'nombre_mascota' => 'Luna',
        'especie'        => 'Gato',
        'raza'           => null,
        'sexo'           => 'Hembra',
        'edad'           => null,
        'peso_actual'    => '3.8 kg',
        'esterilizado'   => null,
        'alergias'       => null,
        'nombre_dueno'   => 'María Fernanda López',
        'telefono_ult4'  => '9014',
        'historial'      => [
            [
                'fecha'            => '05/11/2026',
                'tipo'             => 'Consulta General',
                'badge_class'      => 'badge-danger',
                'dot_class'        => '',
                'titulo'           => 'Valoración inicial de paciente rescatada',
                'descripcion'      => null,
                'peso_consulta'    => '3.8 kg',
                'proximo_refuerzo' => '20/11/2026'
            ]
        ]
    ]
];

// Procesar parámetros de búsqueda (Folio directo, QR o búsqueda asistida)
$folioInput       = trim((string) ($_GET['folio'] ?? $_GET['q'] ?? ''));
$mascotaInput     = trim((string) ($_GET['mascota'] ?? ''));
$verificacionInput = trim((string) ($_GET['verificacion'] ?? ''));

$resultado = null;
$mensajeError = null;
$busquedaRealizada = ($folioInput !== '' || $mascotaInput !== '' || $verificacionInput !== '');

if ($folioInput !== '') {
    $folioNormalizado = strtoupper($folioInput);
    if (isset($expedientesDemo[$folioNormalizado])) {
        $resultado = $expedientesDemo[$folioNormalizado];
    } else {
        // Si escribió el nombre en el buscador rápido del inicio, orientarlo a validar apellido/teléfono
        $mensajeError = 'No encontramos un expediente exacto con el folio "' . $folioInput . '". Si no tienes tu código VM-XXXXX a la mano, utiliza la búsqueda asistida ingresando el nombre de tu mascota y tu primer apellido o últimos 4 dígitos de teléfono.';
    }
} elseif ($mascotaInput !== '' || $verificacionInput !== '') {
    if ($mascotaInput === '' || $verificacionInput === '') {
        $mensajeError = 'Para proteger tu privacidad, debes ingresar tanto el nombre de la mascota como el primer apellido o los últimos 4 dígitos de tu teléfono.';
    } else {
        foreach ($expedientesDemo as $exp) {
            $coincideMascota = mb_strtolower($exp['nombre_mascota'], 'UTF-8') === mb_strtolower($mascotaInput, 'UTF-8');
            $coincideApellido = str_contains(mb_strtolower($exp['nombre_dueno'], 'UTF-8'), mb_strtolower($verificacionInput, 'UTF-8'));
            $coincideTel = ($exp['telefono_ult4'] === $verificacionInput);

            if ($coincideMascota && ($coincideApellido || $coincideTel)) {
                $resultado = $exp;
                break;
            }
        }
        if ($resultado === null) {
            $mensajeError = 'No se encontraron coincidencias con los datos proporcionados. Verifica que el nombre de la mascota y tu apellido o últimos 4 dígitos de teléfono coincidan con los registrados en clínica.';
        }
    }
}
?>

<section class="page-header">
    <div class="container">
        <span class="hero-eyebrow">Carnet Digital</span>
        <h1>Consulta de Historial Clínico</h1>
        <p style="margin-top: var(--space-1); margin-bottom: 0;">
            Revisa las vacunas, desparasitaciones, evolución de peso y consultas de tu mascota sin necesidad de crear una cuenta.
        </p>
    </div>
</section>

<section class="section">
    <div class="container">
        <!-- FORMULARIOS DE BÚSQUEDA (FOLIO/QR O ASISTIDA) -->
        <div class="search-modes-grid" style="margin-bottom: var(--space-4);">
            <!-- Opción 1: Por Folio Único / QR -->
            <div class="card">
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: var(--space-1);">
                    <h2 style="font-size: var(--text-lg);">Opción 1: Código de Carnet</h2>
                    <span class="pet-code-badge">VM-XXXXX</span>
                </div>
                <p style="font-size: var(--text-sm); color: var(--color-text-muted);">
                    Escribe el folio único impreso en tu carnet físico o escanea tu código QR.
                </p>
                <form action="/expediente" method="GET">
                    <div class="form-group">
                        <label for="folio" class="form-label">Folio Único de Mascota</label>
                        <input
                            type="text"
                            id="folio"
                            name="folio"
                            class="form-control"
                            placeholder="Ej. VM-7K4P9"
                            value="<?= e($folioInput) ?>"
                            required
                        >
                    </div>
                    <button type="submit" class="btn btn-primary" style="width: 100%;">
                        Consultar por Folio
                    </button>
                </form>
            </div>

            <!-- Opción 2: Búsqueda Asistida -->
            <div class="card">
                <h2 style="font-size: var(--text-lg); margin-bottom: var(--space-1);">Opción 2: Búsqueda Asistida</h2>
                <p style="font-size: var(--text-sm); color: var(--color-text-muted);">
                    ¿Olvidaste tu carnet? Valida tu identidad ingresando el nombre de tu mascota y tu apellido o teléfono.
                </p>
                <form action="/expediente" method="GET">
                    <div class="form-group">
                        <label for="mascota" class="form-label">Nombre de la Mascota</label>
                        <input
                            type="text"
                            id="mascota"
                            name="mascota"
                            class="form-control"
                            placeholder="Ej. Max"
                            value="<?= e($mascotaInput) ?>"
                            required
                        >
                    </div>
                    <div class="form-group">
                        <label for="verificacion" class="form-label">Primer Apellido del Dueño o Últimos 4 Dígitos del Teléfono</label>
                        <input
                            type="text"
                            id="verificacion"
                            name="verificacion"
                            class="form-control"
                            placeholder="Ej. Torres o 4821"
                            value="<?= e($verificacionInput) ?>"
                            required
                        >
                    </div>
                    <button type="submit" class="btn btn-dark" style="width: 100%;">
                        Buscar y Validar
                    </button>
                </form>
            </div>
        </div>

        <!-- MENSAJE DE ERROR SI NO HAY COINCIDENCIAS -->
        <?php if ($mensajeError !== null): ?>
            <div class="notice-box" role="alert" style="border-left-color: var(--color-status-danger); background-color: var(--color-primary-soft);">
                <strong style="color: var(--color-primary);">Sin resultados:</strong> <?= e($mensajeError) ?>
            </div>
        <?php endif; ?>

        <!-- DESPLIEGUE DEL EXPEDIENTE CLÍNICO ENCONTRADO -->
        <?php if ($resultado !== null): ?>
            <article class="card" aria-labelledby="titulo-expediente-encontrado">
                <div class="pet-header-bar">
                    <div>
                        <span class="badge badge-ok" style="margin-bottom: var(--space-1);">Expediente Activo</span>
                        <h2 id="titulo-expediente-encontrado" style="font-size: var(--text-2xl);">
                            <?= e($resultado['nombre_mascota']) ?>
                            <span style="font-size: var(--text-base); font-weight: 500; color: var(--color-text-muted);">
                                (<?= e($resultado['especie']) ?>)
                            </span>
                        </h2>
                        <p style="margin-bottom: 0; font-size: var(--text-sm); color: var(--color-text-muted);">
                            <strong>Propietario (Protegido):</strong> <?= e(enmascararPropietario($resultado['nombre_dueno'])) ?>
                        </p>
                    </div>
                    <div style="text-align: right;">
                        <span style="display: block; font-size: var(--text-xs); color: var(--color-text-muted); margin-bottom: var(--space-0-5);">Folio de Expediente</span>
                        <span class="pet-code-badge" style="font-size: var(--text-base);"><?= e($resultado['codigo_unico']) ?></span>
                    </div>
                </div>

                <!-- FICHA TÉCNICA CON TOLERANCIA A DATOS OPCIONALES -->
                <h3 style="font-size: var(--text-lg); margin-bottom: var(--space-2);">Datos Generales del Paciente</h3>
                <div class="pet-specs-grid">
                    <div class="spec-item">
                        <span class="spec-label">Raza</span>
                        <?php if (!empty($resultado['raza'])): ?>
                            <span class="spec-value"><?= e($resultado['raza']) ?></span>
                        <?php else: ?>
                            <span class="spec-value spec-empty">Sin registro</span>
                        <?php endif; ?>
                    </div>

                    <div class="spec-item">
                        <span class="spec-label">Sexo</span>
                        <?php if (!empty($resultado['sexo'])): ?>
                            <span class="spec-value"><?= e($resultado['sexo']) ?></span>
                        <?php else: ?>
                            <span class="spec-value spec-empty">Sin registro</span>
                        <?php endif; ?>
                    </div>

                    <div class="spec-item">
                        <span class="spec-label">Edad Aproximada</span>
                        <?php if (!empty($resultado['edad'])): ?>
                            <span class="spec-value"><?= e($resultado['edad']) ?></span>
                        <?php else: ?>
                            <span class="spec-value spec-empty">Sin registro</span>
                        <?php endif; ?>
                    </div>

                    <div class="spec-item">
                        <span class="spec-label">Peso Registrado</span>
                        <?php if (!empty($resultado['peso_actual'])): ?>
                            <span class="spec-value"><?= e($resultado['peso_actual']) ?></span>
                        <?php else: ?>
                            <span class="spec-value spec-empty">Sin registro</span>
                        <?php endif; ?>
                    </div>

                    <div class="spec-item">
                        <span class="spec-label">Esterilizado</span>
                        <?php if (!empty($resultado['esterilizado'])): ?>
                            <span class="spec-value"><?= e($resultado['esterilizado']) ?></span>
                        <?php else: ?>
                            <span class="spec-value spec-empty">Sin registro</span>
                        <?php endif; ?>
                    </div>

                    <div class="spec-item" style="grid-column: span 2;">
                        <span class="spec-label">Alergias / Observaciones Crónicas</span>
                        <?php if (!empty($resultado['alergias'])): ?>
                            <span class="spec-value"><?= e($resultado['alergias']) ?></span>
                        <?php else: ?>
                            <span class="spec-value spec-empty">Sin registro</span>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- LÍNEA DEL TIEMPO MÉDICA -->
                <div style="margin-top: var(--space-4); padding-top: var(--space-3); border-top: 1px solid var(--color-border);">
                    <h3 style="font-size: var(--text-lg);">Historial de Vacunas y Consultas</h3>
                    <p style="font-size: var(--text-sm); color: var(--color-text-muted);">
                        Ordenado de la atención más reciente a la más antigua.
                    </p>

                    <?php if (empty($resultado['historial'])): ?>
                        <p class="spec-empty">Aún no se han registrado atenciones clínicas en este expediente.</p>
                    <?php else: ?>
                        <div class="clinical-timeline">
                            <?php foreach ($resultado['historial'] as $evento): ?>
                                <div class="timeline-entry">
                                    <span class="timeline-dot <?= e($evento['dot_class']) ?>" aria-hidden="true"></span>
                                    <div class="card" style="background-color: var(--color-bg-main);">
                                        <div class="timeline-header">
                                            <span class="badge <?= e($evento['badge_class']) ?>"><?= e($evento['tipo']) ?></span>
                                            <span class="timeline-date">Fecha: <?= e($evento['fecha']) ?></span>
                                        </div>

                                        <h4 style="font-size: var(--text-base); margin-top: var(--space-1); margin-bottom: var(--space-1);">
                                            <?= e($evento['titulo']) ?>
                                        </h4>

                                        <?php if (!empty($evento['descripcion'])): ?>
                                            <p style="font-size: var(--text-sm); margin-bottom: var(--space-1);">
                                                <?= e($evento['descripcion']) ?>
                                            </p>
                                        <?php endif; ?>

                                        <?php if (!empty($evento['peso_consulta'])): ?>
                                            <p style="font-size: var(--text-xs); color: var(--color-text-muted); margin-bottom: 0;">
                                                <strong>Peso en esa visita:</strong> <?= e($evento['peso_consulta']) ?>
                                            </p>
                                        <?php endif; ?>

                                        <?php if (!empty($evento['proximo_refuerzo'])): ?>
                                            <div class="refuerzo-alert">
                                                <span>Próximo refuerzo / cita sugerida: <strong><?= e($evento['proximo_refuerzo']) ?></strong></span>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- DESLINDE SANITARIO SENASICA / SADER -->
                <div class="notice-box" style="margin-top: var(--space-4); margin-bottom: 0;" role="note">
                    <strong>Nota Médica Legal:</strong> Este resumen es de carácter informativo. Conforme a los lineamientos de <strong>SENASICA / SADER</strong>, no constituye una receta médica electrónica; cualquier medicamento requiere valoración presencial y receta física expedida en <strong>Veterinaria Madri</strong>.
                </div>
            </article>
        <?php elseif (!$busquedaRealizada): ?>
            <!-- Guía de prueba rápida mientras estamos en entorno de desarrollo -->
            <div class="notice-box" role="note">
                <strong>Ejemplos de prueba disponibles en modo demostración:</strong> Puedes probar la consulta ingresando el folio <code>VM-7K4P9</code> (expediente completo de Max) o el folio <code>VM-3M9R2</code> (expediente con datos escasos de Luna).
            </div>
        <?php endif; ?>
    </div>
</section>