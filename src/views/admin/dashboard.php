<?php
use App\Config\Database;
use App\Config\Env;
use App\Controllers\AdminController;
use App\Middlewares\Auth;
use App\Middlewares\Csrf;
use function App\Config\e;

Auth::requireAdmin();

$db    = Database::getConnection();
$flash = AdminController::handlePost();

if ($flash === null && ($_GET['msg'] ?? '') === 'created') {
    $flash = ['type' => 'ok', 'text' => 'Mascota registrada con éxito. Ya puedes imprimir su código QR o agregar su primera consulta.'];
}

$tab         = (string) ($_GET['tab'] ?? 'pacientes');
$searchQuery = trim((string) ($_GET['q'] ?? ''));
$mascotaId   = !empty($_GET['mascota_id']) ? (int) $_GET['mascota_id'] : null;
$prodEditId  = !empty($_GET['editar_producto']) ? (int) $_GET['editar_producto'] : null;

// 1. Búsqueda Universal de Mascotas (por Nombre de mascota, Dueño, Teléfono o Folio VM-XXXXX)
if ($searchQuery !== '') {
    $like = '%' . $searchQuery . '%';
    $stmtPets = $db->prepare("
        SELECT id, codigo_unico, nombre_mascota, especie, raza, peso_actual, nombre_dueno, telefono_contacto, foto_webp
        FROM mascotas
        WHERE codigo_unico LIKE :q1
           OR nombre_mascota LIKE :q2
           OR nombre_dueno LIKE :q3
           OR telefono_contacto LIKE :q4
        ORDER BY updated_at DESC
        LIMIT 30
    ");
    $stmtPets->execute([':q1' => $like, ':q2' => $like, ':q3' => $like, ':q4' => $like]);
} else {
    $stmtPets = $db->query("
        SELECT id, codigo_unico, nombre_mascota, especie, raza, peso_actual, nombre_dueno, telefono_contacto, foto_webp
        FROM mascotas
        ORDER BY updated_at DESC
        LIMIT 20
    ");
}
$listaMascotas = $stmtPets->fetchAll();

// 2. Si hay una mascota seleccionada, cargar su expediente completo e historial
$mascotaActiva   = null;
$historialActivo = [];
if ($mascotaId !== null) {
    $stmtOne = $db->prepare("SELECT * FROM mascotas WHERE id = :id LIMIT 1");
    $stmtOne->execute([':id' => $mascotaId]);
    $mascotaActiva = $stmtOne->fetch() ?: null;

    if ($mascotaActiva) {
        $stmtHist = $db->prepare("
            SELECT * FROM historial_clinico
            WHERE mascota_id = :id
            ORDER BY fecha_atencion DESC, id DESC
        ");
        $stmtHist->execute([':id' => $mascotaId]);
        $historialActivo = $stmtHist->fetchAll();
    }
}

// 3. Cargar catálogo de productos si estamos en la pestaña de productos
$listaProductos = [];
$productoEditar = null;
if ($tab === 'productos') {
    $stmtProds = $db->query("SELECT * FROM productos ORDER BY id DESC");
    $listaProductos = $stmtProds->fetchAll();

    if ($prodEditId !== null) {
        foreach ($listaProductos as $p) {
            if ((int) $p['id'] === $prodEditId) {
                $productoEditar = $p;
                break;
            }
        }
    }
}

$appBaseUrl = rtrim((string) Env::get('APP_URL', 'http://vetmadri.test'), '/');
?>

<!-- Barra Superior del Panel Administrativo -->
<div class="admin-topbar">
    <div class="container admin-topbar-inner">
        <div>
            <span class="badge badge-ok">Panel Médico Administrativo</span>
            <strong style="margin-left: var(--space-1);"><?= e($_SESSION['user_name'] ?? 'Veterinario') ?></strong>
        </div>
        <div style="display: flex; flex-wrap: wrap; gap: var(--space-1);">
            <a href="/admin?tab=pacientes" class="btn <?= $tab !== 'productos' ? 'btn-primary' : 'btn-dark' ?>">
                Pacientes y Expedientes
            </a>
            <a href="/admin?tab=productos" class="btn <?= $tab === 'productos' ? 'btn-primary' : 'btn-dark' ?>">
                Catálogo de Productos
            </a>
            <a href="/admin/logout" class="btn btn-outline" style="border-color: #94A3B8; color: #FFFFFF;">
                Cerrar Sesión
            </a>
        </div>
    </div>
</div>

<section class="container" style="padding-bottom: var(--space-8);">
    <?php if ($flash !== null): ?>
        <div class="notice-box no-print" role="alert" style="border-left-color: <?= $flash['type'] === 'ok' ? 'var(--color-status-ok)' : 'var(--color-status-danger)' ?>;">
            <strong><?= $flash['type'] === 'ok' ? 'Operación exitosa:' : 'Atención:' ?></strong>
            <?= e($flash['text']) ?>
        </div>
    <?php endif; ?>

    <?php if ($tab !== 'productos'): ?>
        <!-- ==================================================================
             MÓDULO 1: GESTIÓN DE PACIENTES, FOTOS WEBP, QR E HISTORIAL CLÍNICO
             ================================================================== -->
        <div class="admin-layout-grid">
            <!-- COLUMNA IZQUIERDA: Buscador Universal y Listado / Detalle de Mascota -->
            <div>
                <div class="card no-print" style="margin-bottom: var(--space-3);">
                    <div style="display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: var(--space-2); margin-bottom: var(--space-2);">
                        <h1 style="font-size: var(--text-xl);">Buscador Universal de Pacientes</h1>
                        <a href="/admin?tab=pacientes" class="btn btn-outline" style="min-height: 2.25rem; padding: 0.25rem 0.75rem; font-size: var(--text-sm);">
                            + Registrar Nueva Mascota
                        </a>
                    </div>

                    <form action="/admin" method="GET" style="display: flex; gap: var(--space-1); flex-wrap: wrap;">
                        <input type="hidden" name="tab" value="pacientes">
                        <input
                            type="text"
                            name="q"
                            class="form-control"
                            style="flex: 1; min-width: 220px;"
                            placeholder="Buscar por Folio (VM-XXXXX), Mascota, Dueño o Teléfono..."
                            value="<?= e($searchQuery) ?>"
                        >
                        <button type="submit" class="btn btn-dark">Buscar</button>
                        <?php if ($searchQuery !== ''): ?>
                            <a href="/admin?tab=pacientes" class="btn btn-outline">Limpiar</a>
                        <?php endif; ?>
                    </form>
                </div>

                <!-- Tabla de Mascotas Registradas -->
                <div class="card no-print" style="margin-bottom: var(--space-3);">
                    <h2 style="font-size: var(--text-lg); margin-bottom: var(--space-2);">
                        Pacientes Registrados (<?= count($listaMascotas) ?>)
                    </h2>
                    <div class="table-responsive">
                        <table class="admin-table">
                            <thead>
                                <tr>
                                    <th>Folio</th>
                                    <th>Mascota</th>
                                    <th>Propietario / Teléfono</th>
                                    <th>Peso</th>
                                    <th>Acción</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($listaMascotas as $m): ?>
                                    <tr>
                                        <td><span class="pet-code-badge"><?= e($m['codigo_unico']) ?></span></td>
                                        <td>
                                            <strong><?= e($m['nombre_mascota']) ?></strong>
                                            <span style="display: block; font-size: var(--text-xs); color: var(--color-text-muted);">
                                                <?= e($m['especie']) ?> <?= !empty($m['raza']) ? '• ' . e($m['raza']) : '' ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?= e($m['nombre_dueno']) ?>
                                            <?php if (!empty($m['telefono_contacto'])): ?>
                                                <span style="display: block; font-size: var(--text-xs); color: var(--color-text-muted);">
                                                    Tel: <?= e($m['telefono_contacto']) ?>
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?= $m['peso_actual'] !== null ? number_format((float) $m['peso_actual'], 2) . ' kg' : '—' ?>
                                        </td>
                                        <td>
                                            <a href="/admin?tab=pacientes&mascota_id=<?= (int) $m['id'] ?>" class="btn btn-primary" style="min-height: 2.25rem; padding: 0.25rem 0.75rem; font-size: var(--text-xs);">
                                                Abrir Expediente
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Si hay una mascota seleccionada: Mostrar Carnet QR y Línea del Tiempo -->
                <?php if ($mascotaActiva !== null): ?>
                    <?php
                    $urlPublicaQr = $appBaseUrl . '/expediente?folio=' . urlencode((string) $mascotaActiva['codigo_unico']);
                    ?>
                    <div class="card" style="margin-bottom: var(--space-3);">
                        <div class="pet-header-bar">
                            <div class="pet-identity-group">
                                <div class="pet-photo-avatar">
                                    <?php if (!empty($mascotaActiva['foto_webp'])): ?>
                                        <img src="/assets/img/mascotas/<?= e($mascotaActiva['foto_webp']) ?>" alt="Foto de <?= e($mascotaActiva['nombre_mascota']) ?>">
                                    <?php else: ?>
                                        <span>Sin foto</span>
                                    <?php endif; ?>
                                </div>
                                <div>
                                    <span class="badge badge-ok">Carnet Digital Veterinaria Madri</span>
                                    <h2 style="font-size: var(--text-2xl); margin-top: var(--space-0-5);">
                                        <?= e($mascotaActiva['nombre_mascota']) ?> (<?= e($mascotaActiva['especie']) ?>)
                                    </h2>
                                    <p style="margin-bottom: 0; font-size: var(--text-sm);">
                                        <strong>Propietario:</strong> <?= e($mascotaActiva['nombre_dueno']) ?>
                                        <?= !empty($mascotaActiva['telefono_contacto']) ? ' | <strong>Tel:</strong> ' . e($mascotaActiva['telefono_contacto']) : '' ?>
                                    </p>
                                </div>
                            </div>
                            <div>
                                <span class="pet-code-badge" style="font-size: var(--text-lg);"><?= e($mascotaActiva['codigo_unico']) ?></span>
                            </div>
                        </div>

                        <!-- Bloque de Código QR y Enlace Directo para el Cliente -->
                        <div class="qr-carnet-box">
                            <div id="qr-code-target" class="qr-canvas-container" data-url="<?= e($urlPublicaQr) ?>"></div>
                            <div style="flex: 1; min-width: 200px;">
                                <h3 style="font-size: var(--text-base); margin-bottom: var(--space-0-5);">
                                    Acceso Rápido para el Propietario
                                </h3>
                                <p style="font-size: var(--text-sm); margin-bottom: var(--space-1);">
                                    Folio único: <strong><?= e($mascotaActiva['codigo_unico']) ?></strong><br>
                                    Enlace directo: <a href="<?= e($urlPublicaQr) ?>" target="_blank"><?= e($urlPublicaQr) ?></a>
                                </p>
                                <div class="no-print" style="display: flex; flex-wrap: wrap; gap: var(--space-1);">
                                    <button type="button" id="btn-imprimir-carnet" class="btn btn-dark" style="min-height: 2.25rem; font-size: var(--text-xs);">
                                        Imprimir Carnet / QR
                                    </button>
                                    <a href="/expediente?folio=<?= e($mascotaActiva['codigo_unico']) ?>" target="_blank" class="btn btn-outline" style="min-height: 2.25rem; font-size: var(--text-xs);">
                                        Ver como Cliente (Enmascarado)
                                    </a>
                                </div>
                            </div>
                        </div>

                        <!-- Historial Clínico de la Mascota Seleccionada -->
                        <div style="margin-top: var(--space-4);">
                            <h3 style="font-size: var(--text-lg); margin-bottom: var(--space-2);">
                                Historial de Vacunas y Consultas (<?= count($historialActivo) ?>)
                            </h3>
                            <?php if (empty($historialActivo)): ?>
                                <p class="spec-empty">Aún no hay consultas ni vacunas registradas para esta mascota.</p>
                            <?php else: ?>
                                <div class="clinical-timeline">
                                    <?php foreach ($historialActivo as $ev): ?>
                                        <div class="timeline-entry">
                                            <span class="timeline-dot timeline-dot-ok" aria-hidden="true"></span>
                                            <div class="card" style="background-color: var(--color-bg-main); padding: var(--space-2);">
                                                <div class="timeline-header">
                                                    <span class="badge badge-ok"><?= e($ev['tipo_atencion']) ?></span>
                                                    <span class="timeline-date"><?= date('d/m/Y', strtotime((string) $ev['fecha_atencion'])) ?></span>
                                                </div>
                                                <h4 style="font-size: var(--text-base); margin: var(--space-0-5) 0;">
                                                    <?= e($ev['titulo']) ?>
                                                </h4>
                                                <?php if (!empty($ev['descripcion'])): ?>
                                                    <p style="font-size: var(--text-sm); margin-bottom: var(--space-1);"><?= e($ev['descripcion']) ?></p>
                                                <?php endif; ?>
                                                <div style="font-size: var(--text-xs); color: var(--color-text-muted);">
                                                    <?= $ev['peso_consulta'] !== null ? '<strong>Peso:</strong> ' . number_format((float) $ev['peso_consulta'], 2) . ' kg | ' : '' ?>
                                                    <?= !empty($ev['proximo_refuerzo']) ? '<strong>Próximo refuerzo:</strong> ' . date('d/m/Y', strtotime((string) $ev['proximo_refuerzo'])) : '' ?>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <!-- COLUMNA DERECHA: Formularios de Alta/Edición de Mascota y Nueva Consulta -->
            <div class="no-print">
                <?php if ($mascotaActiva !== null): ?>
                    <!-- Formulario 1: Agregar Vacuna o Consulta al Expediente Abierto -->
                    <div class="card" style="margin-bottom: var(--space-3); border-top: 4px solid var(--color-primary);">
                        <h2 style="font-size: var(--text-lg); margin-bottom: var(--space-1);">
                            Agregar Vacuna o Consulta a <?= e($mascotaActiva['nombre_mascota']) ?>
                        </h2>
                        <p style="font-size: var(--text-xs); color: var(--color-text-muted);">
                            Solo la fecha, el tipo de atención y el título son obligatorios.
                        </p>

                        <form action="/admin?tab=pacientes&mascota_id=<?= (int) $mascotaActiva['id'] ?>" method="POST">
                            <?= Csrf::field() ?>
                            <input type="hidden" name="form_action" value="add_history">
                            <input type="hidden" name="mascota_id" value="<?= (int) $mascotaActiva['id'] ?>">

                            <div class="form-grid-2">
                                <div class="form-group">
                                    <label class="form-label" for="fecha_atencion">Fecha de Atención *</label>
                                    <input type="date" id="fecha_atencion" name="fecha_atencion" class="form-control" value="<?= date('Y-m-d') ?>" required>
                                </div>
                                <div class="form-group">
                                    <label class="form-label" for="tipo_atencion">Tipo de Atención *</label>
                                    <select id="tipo_atencion" name="tipo_atencion" class="form-control" required>
                                        <option value="Vacunación">Vacunación</option>
                                        <option value="Desparasitación">Desparasitación</option>
                                        <option value="Consulta General">Consulta General</option>
                                        <option value="Cirugía / Otro">Cirugía / Otro</option>
                                    </select>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="form-label" for="titulo">Vacuna Aplicada o Motivo de Consulta *</label>
                                <input type="text" id="titulo" name="titulo" class="form-control" placeholder="Ej. Vacuna Séxtuple / Desparasitación / Otitis" required>
                            </div>

                            <div class="form-group">
                                <label class="form-label" for="descripcion">Notas Clínicas / Tratamiento (Opcional)</label>
                                <textarea id="descripcion" name="descripcion" class="form-control" rows="2" placeholder="Observaciones médicas o indicaciones..."></textarea>
                            </div>

                            <div class="form-grid-2">
                                <div class="form-group">
                                    <label class="form-label" for="peso_consulta">Peso en Visita kg (Opcional)</label>
                                    <input type="number" step="0.01" min="0" id="peso_consulta" name="peso_consulta" class="form-control" placeholder="Ej. 12.40">
                                </div>
                                <div class="form-group">
                                    <label class="form-label" for="proximo_refuerzo">Próximo Refuerzo (Opcional)</label>
                                    <input type="date" id="proximo_refuerzo" name="proximo_refuerzo" class="form-control">
                                </div>
                            </div>

                            <button type="submit" class="btn btn-primary" style="width: 100%;">
                                Guardar en Historial Clínico
                            </button>
                        </form>
                    </div>
                <?php endif; ?>

                <!-- Formulario 2: Crear Nueva Mascota o Editar la Seleccionada (Incluye Foto WebP Opcional) -->
                <div class="card">
                    <h2 style="font-size: var(--text-lg); margin-bottom: var(--space-1);">
                        <?= $mascotaActiva ? 'Editar Ficha de ' . e($mascotaActiva['nombre_mascota']) : 'Registrar Nueva Mascota' ?>
                    </h2>
                    <p style="font-size: var(--text-xs); color: var(--color-text-muted);">
                        Los campos marcados con (*) son obligatorios; todos los demás son opcionales.
                    </p>

                    <form
                        action="/admin?tab=pacientes<?= $mascotaActiva ? '&mascota_id=' . (int) $mascotaActiva['id'] : '' ?>"
                        method="POST"
                        enctype="multipart/form-data"
                    >
                        <?= Csrf::field() ?>
                        <input type="hidden" name="form_action" value="save_pet">
                        <?php if ($mascotaActiva): ?>
                            <input type="hidden" name="mascota_id" value="<?= (int) $mascotaActiva['id'] ?>">
                        <?php endif; ?>

                        <div class="form-grid-2">
                            <div class="form-group">
                                <label class="form-label" for="nombre_mascota">Nombre de la Mascota *</label>
                                <input type="text" id="nombre_mascota" name="nombre_mascota" class="form-control" value="<?= e($mascotaActiva['nombre_mascota'] ?? '') ?>" required>
                            </div>
                            <div class="form-group">
                                <label class="form-label" for="especie">Especie *</label>
                                <select id="especie" name="especie" class="form-control" required>
                                    <?php foreach (['Perro', 'Gato', 'Otro'] as $esp): ?>
                                        <option value="<?= e($esp) ?>" <?= ($mascotaActiva['especie'] ?? 'Perro') === $esp ? 'selected' : '' ?>>
                                            <?= e($esp) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <div class="form-grid-2">
                            <div class="form-group">
                                <label class="form-label" for="nombre_dueno">Nombre Completo del Dueño *</label>
                                <input type="text" id="nombre_dueno" name="nombre_dueno" class="form-control" value="<?= e($mascotaActiva['nombre_dueno'] ?? '') ?>" required>
                            </div>
                            <div class="form-group">
                                <label class="form-label" for="telefono_contacto">Teléfono (Opcional)</label>
                                <input type="text" id="telefono_contacto" name="telefono_contacto" class="form-control" placeholder="Ej. 4491234567" value="<?= e($mascotaActiva['telefono_contacto'] ?? '') ?>">
                            </div>
                        </div>

                        <div class="form-grid-2">
                            <div class="form-group">
                                <label class="form-label" for="raza">Raza (Opcional)</label>
                                <input type="text" id="raza" name="raza" class="form-control" placeholder="Ej. Mestizo, Schnauzer..." value="<?= e($mascotaActiva['raza'] ?? '') ?>">
                            </div>
                            <div class="form-group">
                                <label class="form-label" for="sexo">Sexo (Opcional)</label>
                                <select id="sexo" name="sexo" class="form-control">
                                    <option value="">-- Sin registro --</option>
                                    <option value="Macho" <?= ($mascotaActiva['sexo'] ?? '') === 'Macho' ? 'selected' : '' ?>>Macho</option>
                                    <option value="Hembra" <?= ($mascotaActiva['sexo'] ?? '') === 'Hembra' ? 'selected' : '' ?>>Hembra</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-grid-2">
                            <div class="form-group">
                                <label class="form-label" for="edad_aprox">Edad Aproximada (Opcional)</label>
                                <input type="text" id="edad_aprox" name="edad_aprox" class="form-control" placeholder="Ej. 2 años / 6 meses" value="<?= e($mascotaActiva['edad_aprox'] ?? '') ?>">
                            </div>
                            <div class="form-group">
                                <label class="form-label" for="peso_actual">Peso Actual en kg (Opcional)</label>
                                <input type="number" step="0.01" min="0" id="peso_actual" name="peso_actual" class="form-control" value="<?= e((string) ($mascotaActiva['peso_actual'] ?? '')) ?>">
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="form-label" for="esterilizado">¿Esterilizado? (Opcional)</label>
                            <select id="esterilizado" name="esterilizado" class="form-control">
                                <option value="">-- Sin registro --</option>
                                <option value="Sí" <?= ($mascotaActiva['esterilizado'] ?? '') === 'Sí' ? 'selected' : '' ?>>Sí</option>
                                <option value="No" <?= ($mascotaActiva['esterilizado'] ?? '') === 'No' ? 'selected' : '' ?>>No</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label class="form-label" for="alergias_notas">Alergias / Notas Crónicas (Opcional)</label>
                            <textarea id="alergias_notas" name="alergias_notas" class="form-control" rows="2"><?= e($mascotaActiva['alergias_notas'] ?? '') ?></textarea>
                        </div>

                        <!-- Fotografía Opcional y Modificable de la Mascota -->
                        <div class="form-group" style="background-color: var(--color-bg-main); padding: var(--space-2); border-radius: var(--radius-md); border: 1px solid var(--color-border);">
                            <label class="form-label" for="foto_mascota">
                                <?= !empty($mascotaActiva['foto_webp']) ? 'Reemplazar Fotografía de la Mascota (Opcional)' : 'Subir Fotografía de la Mascota (Opcional)' ?>
                            </label>
                            <input type="file" id="foto_mascota" name="foto_mascota" class="form-control" accept="image/jpeg,image/png,image/webp">
                            <span style="display: block; font-size: var(--text-xs); color: var(--color-text-muted); margin-top: var(--space-0-5);">
                                Se convertirá automáticamente a formato WebP ligero eliminando metadatos GPS (máx. 2 MB).
                            </span>

                            <?php if (!empty($mascotaActiva['foto_webp'])): ?>
                                <label style="display: inline-flex; align-items: center; gap: var(--space-1); margin-top: var(--space-1); font-size: var(--text-xs); color: var(--color-primary); cursor: pointer;">
                                    <input type="checkbox" name="eliminar_foto" value="1">
                                    Eliminar la fotografía actual del expediente
                                </label>
                            <?php endif; ?>
                        </div>

                        <button type="submit" class="btn btn-dark" style="width: 100%;">
                            <?= $mascotaActiva ? 'Guardar Cambios de la Mascota' : 'Generar Folio VM-XXXXX y Registrar Mascota' ?>
                        </button>
                    </form>
                </div>
            </div>
        </div>

    <?php else: ?>
        <!-- ==================================================================
             MÓDULO 2: GESTIÓN DEL CATÁLOGO VITRINA DE PRODUCTOS
             ================================================================== -->
        <div class="admin-layout-grid">
            <div>
                <div class="card">
                    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: var(--space-2);">
                        <h1 style="font-size: var(--text-xl);">Inventario de Productos en Vitrina</h1>
                        <a href="/admin?tab=productos" class="btn btn-outline" style="min-height: 2.25rem; font-size: var(--text-xs);">
                            + Nuevo Producto
                        </a>
                    </div>

                    <div class="table-responsive">
                        <table class="admin-table">
                            <thead>
                                <tr>
                                    <th>SKU</th>
                                    <th>Producto / Categoría</th>
                                    <th>Precio MXN</th>
                                    <th>Estado</th>
                                    <th>Acción</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($listaProductos as $prod): ?>
                                    <?php
                                    $bClass = match ($prod['disponibilidad']) {
                                        'Disponible'     => 'badge-ok',
                                        'Pocas unidades' => 'badge-warn',
                                        default          => 'badge-danger',
                                    };
                                    ?>
                                    <tr>
                                        <td><code><?= e($prod['sku']) ?></code></td>
                                        <td>
                                            <strong><?= e($prod['nombre']) ?></strong>
                                            <span style="display: block; font-size: var(--text-xs); color: var(--color-text-muted);">
                                                <?= e($prod['categoria']) ?> <?= (int) $prod['activo'] === 0 ? '(Oculto)' : '' ?>
                                            </span>
                                        </td>
                                        <td><strong>$<?= number_format((float) $prod['precio_mxn'], 2) ?></strong></td>
                                        <td><span class="badge <?= e($bClass) ?>"><?= e($prod['disponibilidad']) ?></span></td>
                                        <td>
                                            <a href="/admin?tab=productos&editar_producto=<?= (int) $prod['id'] ?>" class="btn btn-dark" style="min-height: 2.25rem; padding: 0.25rem 0.75rem; font-size: var(--text-xs);">
                                                Editar
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div>
                <div class="card">
                    <h2 style="font-size: var(--text-lg); margin-bottom: var(--space-2);">
                        <?= $productoEditar ? 'Editar Producto: ' . e($productoEditar['sku']) : 'Agregar Producto al Catálogo' ?>
                    </h2>

                    <form action="/admin?tab=productos" method="POST" enctype="multipart/form-data">
                        <?= Csrf::field() ?>
                        <input type="hidden" name="form_action" value="save_product">
                        <?php if ($productoEditar): ?>
                            <input type="hidden" name="producto_id" value="<?= (int) $productoEditar['id'] ?>">
                        <?php endif; ?>

                        <div class="form-grid-2">
                            <div class="form-group">
                                <label class="form-label" for="sku">Código SKU *</label>
                                <input type="text" id="sku" name="sku" class="form-control" placeholder="Ej. PROD-007" value="<?= e($productoEditar['sku'] ?? '') ?>" required>
                            </div>
                            <div class="form-group">
                                <label class="form-label" for="precio_mxn">Precio MXN (IVA incluido) *</label>
                                <input type="number" step="0.01" min="1" id="precio_mxn" name="precio_mxn" class="form-control" value="<?= e((string) ($productoEditar['precio_mxn'] ?? '')) ?>" required>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="form-label" for="nombre_prod">Nombre del Producto *</label>
                            <input type="text" id="nombre_prod" name="nombre" class="form-control" value="<?= e($productoEditar['nombre'] ?? '') ?>" required>
                        </div>

                        <div class="form-grid-2">
                            <div class="form-group">
                                <label class="form-label" for="categoria">Categoría *</label>
                                <input type="text" id="categoria" name="categoria" class="form-control" placeholder="Ej. Farmacia y Prevención" value="<?= e($productoEditar['categoria'] ?? '') ?>" required>
                            </div>
                            <div class="form-group">
                                <label class="form-label" for="disponibilidad">Estado en Mostrador *</label>
                                <select id="disponibilidad" name="disponibilidad" class="form-control">
                                    <?php foreach (['Disponible', 'Pocas unidades', 'Agotado'] as $est): ?>
                                        <option value="<?= e($est) ?>" <?= ($productoEditar['disponibilidad'] ?? 'Disponible') === $est ? 'selected' : '' ?>>
                                            <?= e($est) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="form-label" for="descripcion_prod">Descripción Breve</label>
                            <textarea id="descripcion_prod" name="descripcion" class="form-control" rows="2"><?= e($productoEditar['descripcion'] ?? '') ?></textarea>
                        </div>

                        <div class="form-group">
                            <label class="form-label" for="imagen_producto">Fotografía del Producto (Opcional)</label>
                            <input type="file" id="imagen_producto" name="imagen_producto" class="form-control" accept="image/jpeg,image/png,image/webp">
                        </div>

                        <div class="form-group">
                            <label style="display: inline-flex; align-items: center; gap: var(--space-1); cursor: pointer;">
                                <input type="checkbox" name="activo" value="1" <?= (!isset($productoEditar) || (int) $productoEditar['activo'] === 1) ? 'checked' : '' ?>>
                                Mostrar este producto en el catálogo público
                            </label>
                        </div>

                        <button type="submit" class="btn btn-primary" style="width: 100%;">
                            <?= $productoEditar ? 'Actualizar Producto' : 'Publicar Producto en Catálogo' ?>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    <?php endif; ?>
</section>

<script src="/assets/vendor/qrcode/qrcode.min.js"></script>
<script src="/assets/js/admin.js"></script>