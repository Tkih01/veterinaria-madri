<?php
use function App\Config\e;

// Catálogo base de servicios clínicos (preparado para administración dinámica)
$servicios = [
    [
        'categoria'   => 'Medicina Preventiva',
        'badge_class' => 'badge-ok',
        'titulo'      => 'Vacunación Canina y Felina',
        'descripcion' => 'Aplicación de esquemas de vacunación adaptados a la edad y estilo de vida de tu mascota (Puppy, Múltiple, Antirrábica, Triple Felina). Incluye registro en carnet físico y actualización inmediata en su expediente digital.',
        'incluye'     => 'Revisión física previa de temperatura y ganglios antes de inmunizar.'
    ],
    [
        'categoria'   => 'Medicina Preventiva',
        'badge_class' => 'badge-ok',
        'titulo'      => 'Desparasitación Interna y Externa',
        'descripcion' => 'Control y prevención de parásitos gastrointestinales, pulgas, garrapatas y ácaros con dosis calculadas estrictamente según el peso actual registrado en báscula clínica.',
        'incluye'     => 'Programación de fecha de próximo refuerzo en el sistema.'
    ],
    [
        'categoria'   => 'Atención Clínica',
        'badge_class' => 'badge-danger',
        'titulo'      => 'Consulta Médica General',
        'descripcion' => 'Valoración diagnóstica integral ante síntomas digestivos, respiratorios, dermatológicos, oculares o decaimiento. Seguimiento puntual de la evolución del paciente en su línea del tiempo médica.',
        'incluye'     => 'Toma de signos vitales, control de peso y prescripción médica impresa.'
    ],
    [
        'categoria'   => 'Cirugía y Control',
        'badge_class' => 'badge-warn',
        'titulo'      => 'Esterilización Quirúrgica',
        'descripcion' => 'Procedimientos de ovariohisterectomía y orquiectomía bajo protocolos seguros de anestesia, analgesia y monitoreo postoperatorio para perros y gatos.',
        'incluye'     => 'Indicaciones de cuidado en casa y retiro de puntos programado.'
    ],
    [
        'categoria'   => 'Atención Clínica',
        'badge_class' => 'badge-danger',
        'titulo'      => 'Curaciones y Manejo de Heridas',
        'descripcion' => 'Limpieza antiséptica, debridación, suturas menores, control de abscesos y vendajes clínicos con seguimiento periódico hasta el alta médica.',
        'incluye'     => 'Registro de medicación aplicada en consultorio.'
    ],
    [
        'categoria'   => 'Higiene y Bienestar',
        'badge_class' => 'badge-ok',
        'titulo'      => 'Valoración Geriátrica y Nutricional',
        'descripcion' => 'Orientación sobre dietas específicas por etapa de vida, control de sobrepeso y manejo de pacientes senior para preservar su calidad de vida.',
        'incluye'     => 'Historial comparativo de evolución de peso por visita.'
    ],
];
?>

<section class="page-header">
    <div class="container">
        <span class="hero-eyebrow">Atención Profesional</span>
        <h1>Servicios Médicos Veterinarios</h1>
        <p style="margin-top: var(--space-1); margin-bottom: 0;">
            Conoce las áreas de atención clínica y preventiva disponibles en <strong>Veterinaria Madri</strong>.
        </p>
    </div>
</section>

<section class="section">
    <div class="container">
        <!-- Aviso Normativo SENASICA / SADER -->
        <div class="notice-box" role="note" aria-label="Aviso sanitario sobre consulta y medicamentos">
            <strong>Aviso Sanitario (SENASICA / SADER):</strong> Todo diagnóstico y prescripción de medicamentos veterinarios requiere la valoración presencial del paciente en consultorio. El historial digital tiene carácter informativo y no sustituye la receta médica física.
        </div>

        <div class="cards-grid-3">
            <?php foreach ($servicios as $servicio): ?>
                <article class="card product-card">
                    <div>
                        <span class="badge <?= e($servicio['badge_class']) ?>" style="margin-bottom: var(--space-2);">
                            <?= e($servicio['categoria']) ?>
                        </span>
                        <h2 style="font-size: var(--text-xl); margin-bottom: var(--space-1);">
                            <?= e($servicio['titulo']) ?>
                        </h2>
                        <p style="font-size: var(--text-sm);">
                            <?= e($servicio['descripcion']) ?>
                        </p>
                    </div>
                    <div style="border-top: 1px solid var(--color-border); padding-top: var(--space-2); margin-top: var(--space-2); font-size: var(--text-xs); color: var(--color-text-muted);">
                        <strong>Incluye:</strong> <?= e($servicio['incluye']) ?>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>

        <div class="card" style="margin-top: var(--space-6); display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: var(--space-3);">
            <div>
                <h2 style="font-size: var(--text-lg);">¿Tu mascota ya fue atendida con nosotros?</h2>
                <p style="margin-bottom: 0; font-size: var(--text-sm); color: var(--color-text-muted);">
                    Revisa sus vacunas aplicadas y la fecha de su próximo refuerzo con tu código de carnet.
                </p>
            </div>
            <a href="/expediente" class="btn btn-primary">Consultar Historial Clínico</a>
        </div>
    </div>
</section>