<?php
use App\Config\Database;
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

$folioInput        = trim((string) ($_GET['folio'] ?? $_GET['q'] ?? ''));
$mascotaInput      = trim((string) ($_GET['mascota'] ?? ''));
$verificacionInput = trim((string) ($_GET['verificacion'] ?? ''));

$resultado         = null;
$mensajeError      = null;
$busquedaRealizada = ($folioInput !== '' || $mascotaInput !== '' || $verificacionInput !== '');

if ($busquedaRealizada) {
    try {
        $db = Database::getConnection();

        if ($folioInput !== '') {
            $folioNormalizado = strtoupper($folioInput);
            $stmt = $db->prepare("
                SELECT id, codigo_unico, nombre_mascota, especie, raza, sexo, edad_aprox,
                       peso_actual, esterilizado, alergias_notas, foto_webp, nombre_dueno
                FROM mascotas
                WHERE codigo_unico = :codigo
                LIMIT 1
            ");
            $stmt->execute([':codigo' => $folioNormalizado]);
            $mascotaRow = $stmt->fetch();

            if ($mascotaRow) {
                $resultado = $mascotaRow;
            } else {
                $mensajeError = 'No encontramos un expediente con el folio "' . $folioInput . '". Si no tienes tu código VM-XXXXX a la mano, utiliza la búsqueda asistida ingresando el nombre de tu mascota y tu primer apellido o últimos 4 dígitos de teléfono.';
            }
        } elseif ($mascotaInput !== '' || $verificacionInput !== '') {
            if ($mascotaInput === '' || $verificacionInput === '') {
                $mensajeError = 'Para proteger tu privacidad, debes ingresar tanto el nombre de la mascota como el primer apellido o los últimos 4 dígitos de tu teléfono.';
            } else {
                $stmt = $db->prepare("
                    SELECT id, codigo_unico, nombre_mascota, especie, raza, sexo, edad_aprox,
                           peso_actual, esterilizado, alergias_notas, foto_webp, nombre_dueno, telefono_contacto
                    FROM mascotas
                    WHERE LOWER(nombre_mascota) = LOWER(:nombre_mascota)
                ");
                $stmt->execute([':nombre_mascota' => $mascotaInput]);
                $candidatos = $stmt->fetchAll();

                foreach ($candidatos as $cand) {
                    $coincideApellido = str_contains(
                        mb_strtolower((string) $cand['nombre_dueno'], 'UTF-8'),
                        mb_strtolower($verificacionInput, 'UTF-8')
                    );
                    $telefonoLimpio = preg_replace('/\D+/', '', (string) ($cand['telefono_contacto'] ?? ''));
                    $ultimos4 = $telefonoLimpio !== '' ? substr($telefonoLimpio, -4) : '';
                    $coincideTel = ($ultimos4 !== '' && $ultimos4 === $verificacionInput);

                    if ($coincideApellido || $coincideTel) {
                        $resultado = $cand;
                        break;
                    }
                }

                if ($resultado === null) {
                    $mensajeError = 'No se encontraron coincidencias con los datos proporcionados. Verifica que el nombre de la mascota y tu apellido o últimos 4 dígitos de teléfono coincidan con los registrados en clínica.';
                }
            }
        }

        // Si se encontró la mascota, cargar su historial clínico ordenado por fecha DESC
        if ($resultado !== null) {
            $stmtHist = $db->prepare("
                SELECT fecha_atencion, tipo_atencion, titulo, descripcion, peso_consulta, proximo_refuerzo
                FROM historial_clinico
                WHERE mascota_id = :mascota_id
                ORDER BY fecha_atencion DESC, id DESC
            ");
            $stmtHist->execute([':mascota_id' => $resultado['id']]);
            $resultado['historial'] = $stmtHist->fetchAll();
        }
    } catch (\Throwable $ex) {
        $mensajeError = 'No fue posible consultar la base de datos en este momento. Asegúrate de haber importado schema.sql y seeds.sql.';
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
        <!-- FORMULARIOS DE BÚSQUEDA -->
        <div class="search-modes-grid" style="margin-bottom: var(--space-4);">
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

        <?php if ($mensajeError !== null): ?>
            <div class="notice-box" role="alert" style="border-left-color: var(--color-status-danger); background-color: var(--color-primary-soft);">
                <strong style="color: var(--color-primary);">Sin resultados:</strong> <?= e($mensajeError) ?>
            </div>
        <?php endif; ?>

        <?php if ($resultado !== null): ?>
            <article class="card" aria-labelledby="titulo-expediente-encontrado">
                <div class="pet-header-bar">
                    <div class="pet-identity-group">
                        <!-- FOTOGRAFÍA OPCIONAL DE LA MASCOTA -->
                        <div class="pet-photo-avatar">
                            <?php if (!empty($resultado['foto_webp'])): ?>
                                <img
                                    src="/assets/img/mascotas/<?= e($resultado['foto_webp']) ?>"
                                    alt="Fotografía de <?= e($resultado['nombre_mascota']) ?>"
                                    loading="lazy"
                                >
                            <?php else: ?>
                                <span aria-hidden="true">Sin foto</span>
                            <?php endif; ?>
                        </div>

                        <div>
                            <span class="badge badge-ok" style="margin-bottom: var(--space-0-5);">Expediente Activo</span>
                            <h2 id="titulo-expediente-encontrado" style="font-size: var(--text-2xl);">
                                <?= e($resultado['nombre_mascota']) ?>
                                <span style="font-size: var(--text-base); font-weight: 500; color: var(--color-text-muted);">
                                    (<?= e($resultado['especie']) ?>)
                                </span>
                            </h2>
                            <p style="margin-bottom: 0; font-size: var(--text-sm); color: var(--color-text-muted);">
                                <strong>Propietario (Protegido):</strong> <?= e(enmascararPropietario((string) $resultado['nombre_dueno'])) ?>
                            </p>
                        </div>
                    </div>

                    <div style="text-align: right;">
                        <span style="display: block; font-size: var(--text-xs); color: var(--color-text-muted); margin-bottom: var(--space-0-5);">Folio de Expediente</span>
                        <span class="pet-code-badge" style="font-size: var(--text-base);"><?= e($resultado['codigo_unico']) ?></span>
                    </div>
                </div>

                <!-- FICHA TÉCNICA CON TOLERANCIA A CAMPOS NULOS -->
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
                        <?php if (!empty($resultado['edad_aprox'])): ?>
                            <span class="spec-value"><?= e($resultado['edad_aprox']) ?></span>
                        <?php else: ?>
                            <span class="spec-value spec-empty">Sin registro</span>
                        <?php endif; ?>
                    </div>

                    <div class="spec-item">
                        <span class="spec-label">Peso Registrado</span>
                        <?php if ($resultado['peso_actual'] !== null): ?>
                            <span class="spec-value"><?= number_format((float) $resultado['peso_actual'], 2) ?> kg</span>
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
                        <?php if (!empty($resultado['alergias_notas'])): ?>
                            <span class="spec-value"><?= e($resultado['alergias_notas']) ?></span>
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
                                <?php
                                $badgeClass = match ($evento['tipo_atencion']) {
                                    'Vacunación', 'Desparasitación' => 'badge-ok',
                                    'Cirugía / Otro'                => 'badge-warn',
                                    default                         => 'badge-danger',
                                };
                                $dotClass = match ($evento['tipo_atencion']) {
                                    'Vacunación', 'Desparasitación' => 'timeline-dot-ok',
                                    'Cirugía / Otro'                => 'timeline-dot-warn',
                                    default                         => '',
                                };
                                $fechaAtencionFmt = date('d/m/Y', strtotime((string) $evento['fecha_atencion']));
                                $fechaRefuerzoFmt = !empty($evento['proximo_refuerzo'])
                                    ? date('d/m/Y', strtotime((string) $evento['proximo_refuerzo']))
                                    : null;
                                ?>
                                <div class="timeline-entry">
                                    <span class="timeline-dot <?= e($dotClass) ?>" aria-hidden="true"></span>
                                    <div class="card" style="background-color: var(--color-bg-main);">
                                        <div class="timeline-header">
                                            <span class="badge <?= e($badgeClass) ?>"><?= e($evento['tipo_atencion']) ?></span>
                                            <span class="timeline-date">Fecha: <?= e($fechaAtencionFmt) ?></span>
                                        </div>

                                        <h4 style="font-size: var(--text-base); margin-top: var(--space-1); margin-bottom: var(--space-1);">
                                            <?= e($evento['titulo']) ?>
                                        </h4>

                                        <?php if (!empty($evento['descripcion'])): ?>
                                            <p style="font-size: var(--text-sm); margin-bottom: var(--space-1);">
                                                <?= e($evento['descripcion']) ?>
                                            </p>
                                        <?php endif; ?>

                                        <?php if ($evento['peso_consulta'] !== null): ?>
                                            <p style="font-size: var(--text-xs); color: var(--color-text-muted); margin-bottom: 0;">
                                                <strong>Peso en esa visita:</strong> <?= number_format((float) $evento['peso_consulta'], 2) ?> kg
                                            </p>
                                        <?php endif; ?>

                                        <?php if ($fechaRefuerzoFmt !== null): ?>
                                            <div class="refuerzo-alert">
                                                <span>Próximo refuerzo / cita sugerida: <strong><?= e($fechaRefuerzoFmt) ?></strong></span>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="notice-box" style="margin-top: var(--space-4); margin-bottom: 0;" role="note">
                    <strong>Nota Médica Legal:</strong> Este resumen es de carácter informativo. Conforme a los lineamientos de <strong>SENASICA / SADER</strong>, no constituye una receta médica electrónica; cualquier medicamento requiere valoración presencial y receta física expedida en <strong>Veterinaria Madri</strong>.
                </div>
            </article>
        <?php elseif (!$busquedaRealizada): ?>
            <div class="notice-box" role="note">
                <strong>Ejemplos de prueba desde Base de Datos:</strong> Puedes probar la consulta en vivo ingresando el folio <code>VM-7K4P9</code> (Max) o el folio <code>VM-3M9R2</code> (Luna).
            </div>
        <?php endif; ?>
    </div>
</section>