
<?php
use App\Config\Database;
use function App\Config\e;

$productos = [];
try {
    $db = Database::getConnection();
    $stmt = $db->query("
        SELECT sku, nombre, categoria, descripcion, precio_mxn AS precio, disponibilidad, imagen_webp AS imagen
        FROM productos
        WHERE activo = 1
        ORDER BY id ASC
    ");
    $rows = $stmt->fetchAll();

    foreach ($rows as $row) {
        $row['badge_class'] = match ($row['disponibilidad']) {
            'Disponible'     => 'badge-ok',
            'Pocas unidades' => 'badge-warn',
            default          => 'badge-danger',
        };
        $productos[] = $row;
    }
} catch (\Throwable $ex) {
    $productos = [];
}
?>

<section class="page-header">
    <div class="container">
        <span class="hero-eyebrow">Catálogo Vitrina</span>
        <h1>Productos Disponibles en Clínica</h1>
        <p style="margin-top: var(--space-1); margin-bottom: 0;">
            Consulta la existencia actual de alimentos, medicamentos preventivos y accesorios en nuestro mostrador.
        </p>
    </div>
</section>

<section class="section">
    <div class="container">
        <!-- Aviso Legal PROFECO / LFPC -->
        <div class="notice-box" role="note" aria-label="Información comercial y de precios">
            <strong>Información Comercial (LFPC / PROFECO):</strong> Este catálogo es exclusivamente de exhibición informativa (no se realizan ventas ni cobros en línea). Todos los precios están expresados en <strong>Pesos Mexicanos (MXN) e incluyen IVA</strong>. La disponibilidad mostrada está sujeta a existencia física al momento de acudir a la clínica.
        </div>

        <div class="cards-grid-3">
            <?php foreach ($productos as $prod): ?>
                <article class="card product-card">
                    <div>
                        <div class="product-img-wrapper">
                            <?php if (!empty($prod['imagen'])): ?>
                                <img src="/assets/img/productos/<?= e($prod['imagen']) ?>" alt="<?= e($prod['nombre']) ?>" loading="lazy">
                            <?php else: ?>
                                <span aria-hidden="true">Imagen referencial en clínica</span>
                            <?php endif; ?>
                        </div>

                        <div class="product-meta">
                            <span style="font-size: var(--text-xs); color: var(--color-text-muted); font-weight: 600;">
                                <?= e($prod['categoria']) ?>
                            </span>
                            <span class="badge <?= e($prod['badge_class']) ?>">
                                <?= e($prod['disponibilidad']) ?>
                            </span>
                        </div>

                        <h2 style="font-size: var(--text-lg); margin-bottom: var(--space-1);">
                            <?= e($prod['nombre']) ?>
                        </h2>
                        <p style="font-size: var(--text-sm); color: var(--color-text-body);">
                            <?= e($prod['descripcion']) ?>
                        </p>
                    </div>

                    <div style="border-top: 1px solid var(--color-border); padding-top: var(--space-2); margin-top: var(--space-2); display: flex; align-items: baseline; justify-content: space-between;">
                        <div>
                            <span style="font-size: var(--text-xs); color: var(--color-text-muted); display: block;">Precio en mostrador</span>
                            <span class="product-price">$<?= number_format((float) $prod['precio'], 2) ?> <small style="font-size: var(--text-xs); font-weight: 500;">MXN</small></span>
                        </div>
                        <span style="font-family: var(--font-mono); font-size: var(--text-xs); color: var(--color-text-muted);">
                            <?= e($prod['sku']) ?>
                        </span>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    </div>
</section>