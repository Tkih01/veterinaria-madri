<?php
use function App\Config\e;

// Datos iniciales del catálogo vitrina (en la Fase 3/4 se alimentarán directamente desde MariaDB)
$productos = [
    [
        'sku'            => 'PROD-001',
        'nombre'         => 'Antipulgas y Garrapatas Masticable (Perro Mediano 10-20 kg)',
        'categoria'      => 'Farmacia y Prevención',
        'descripcion'    => 'Tableta masticable de acción mensual contra pulgas, garrapatas y ácaros.',
        'precio'         => 380.00,
        'disponibilidad' => 'Disponible',
        'badge_class'    => 'badge-ok',
        'imagen'         => null
    ],
    [
        'sku'            => 'PROD-002',
        'nombre'         => 'Pipeta Desparasitante Felina Multi-Espectro',
        'categoria'      => 'Farmacia y Prevención',
        'descripcion'    => 'Aplicación tópica mensual para gatos de 2.5 a 7.5 kg.',
        'precio'         => 310.00,
        'disponibilidad' => 'Disponible',
        'badge_class'    => 'badge-ok',
        'imagen'         => null
    ],
    [
        'sku'            => 'PROD-003',
        'nombre'         => 'Alimento Seco Mantenimiento Adulto Raza Pequeña (Bulto 4 kg)',
        'categoria'      => 'Nutrición Clínica',
        'descripcion'    => 'Fórmula balanceada de alta digestibilidad con proteínas seleccionadas y ácidos grasos Omega 3 y 6.',
        'precio'         => 540.00,
        'disponibilidad' => 'Pocas unidades',
        'badge_class'    => 'badge-warn',
        'imagen'         => null
    ],
    [
        'sku'            => 'PROD-004',
        'nombre'         => 'Shampoo Dermatológico Calmante con Avena (250 ml)',
        'categoria'      => 'Higiene y Dermatología',
        'descripcion'    => 'Auxiliar en el cuidado de piel sensible o con prurito en perros y gatos.',
        'precio'         => 225.00,
        'disponibilidad' => 'Disponible',
        'badge_class'    => 'badge-ok',
        'imagen'         => null
    ],
    [
        'sku'            => 'PROD-005',
        'nombre'         => 'Collar Isabelino Transparente Ajustable (Talla M)',
        'categoria'      => 'Recuperación y Accesorios',
        'descripcion'    => 'Protección postoperatoria cómoda con cierre seguro para evitar el lamido de heridas o suturas.',
        'precio'         => 145.00,
        'disponibilidad' => 'Disponible',
        'badge_class'    => 'badge-ok',
        'imagen'         => null
    ],
    [
        'sku'            => 'PROD-006',
        'nombre'         => 'Suplemento Articular con Condroitina y Glucosamina (60 tabs)',
        'categoria'      => 'Suplementos',
        'descripcion'    => 'Apoyo nutricional para mascotas adultas mayores o en recuperación ortopédica.',
        'precio'         => 460.00,
        'disponibilidad' => 'Agotado',
        'badge_class'    => 'badge-danger',
        'imagen'         => null
    ],
];
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