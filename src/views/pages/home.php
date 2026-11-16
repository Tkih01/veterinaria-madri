<?php
use function App\Config\e;
?>
<!-- SECCIÓN HERO PRINCIPAL -->
<section class="hero-section">
    <div class="container hero-layout">
        <div>
            <span class="hero-eyebrow">Clínica Veterinaria en Villas de Ntra. Sra. de la Asunción</span>
            <h1>Cuidado médico confiable y expediente digital para tu mascota</h1>
            <p style="margin-top: var(--space-2); font-size: var(--text-lg);">
                En <strong>Veterinaria Madri</strong> combinamos atención clínica cálida y accesible con un sistema moderno de carnet digital para que consultes las vacunas, peso e historial de tu mascota en cualquier momento.
            </p>
            <div class="hero-actions">
                <a href="/servicios" class="btn btn-primary">Ver Servicios Médicos</a>
                <a href="/ubicacion" class="btn btn-outline">Cómo llegar a la clínica</a>
            </div>
        </div>

        <!-- TARJETA DE ACCESO RÁPIDO AL EXPEDIENTE CLÍNICO -->
        <aside class="card" aria-labelledby="titulo-busqueda-rapida">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: var(--space-2);">
                <h2 id="titulo-busqueda-rapida" style="font-size: var(--text-xl);">Expediente de Mascota</h2>
                <span class="pet-code-badge">VM-XXXXX</span>
            </div>
            <p style="font-size: var(--text-sm); color: var(--color-text-muted);">
                Ingresa el código único anotado en tu carnet físico o busca por el nombre de tu mascota para revisar sus vacunas y próximas citas sin necesidad de crear una cuenta.
            </p>

            <form action="/expediente" method="GET" style="margin-top: var(--space-2);">
                <div class="form-group">
                    <label for="codigo_home" class="form-label">Código de Mascota o Nombre</label>
                    <input
                        type="text"
                        id="codigo_home"
                        name="q"
                        class="form-control"
                        placeholder="Ej. VM-7K4P9 o nombre de tu mascota"
                        required
                    >
                </div>
                <button type="submit" class="btn btn-dark" style="width: 100%;">
                    Buscar Historial Clínico
                </button>
            </form>
            <p style="margin-top: var(--space-2); margin-bottom: 0; font-size: var(--text-xs); color: var(--color-text-muted);">
                Tus datos personales están protegidos mediante enmascaramiento conforme a nuestro <a href="/aviso-privacidad">Aviso de Privacidad</a>.
            </p>
        </aside>
    </div>
</section>

<!-- RESUMEN DE SERVICIOS PRINCIPALES -->
<section class="section">
    <div class="container">
        <div class="section-header">
            <h2>Nuestros Servicios Principales</h2>
            <p>Atención preventiva, diagnóstica y productos seleccionados disponibles directamente en clínica.</p>
        </div>

        <div class="cards-grid-3">
            <article class="card card-interactive">
                <span class="badge badge-ok" style="margin-bottom: var(--space-2);">Prevención</span>
                <h3>Vacunación y Desparasitación</h3>
                <p style="margin-top: var(--space-1);">
                    Esquemas completos para cachorros y adultos con registro inmediato en su historial digital y recordatorio de próximo refuerzo.
                </p>
                <a href="/servicios" style="font-weight: 600;">Conocer más detalles &rarr;</a>
            </article>

            <article class="card card-interactive">
                <span class="badge badge-danger" style="margin-bottom: var(--space-2);">Clínica</span>
                <h3>Consulta Médica General</h3>
                <p style="margin-top: var(--space-1);">
                    Revisión física integral, control de peso, diagnóstico oportuno y seguimiento clínico personalizado para perros y gatos.
                </p>
                <a href="/servicios" style="font-weight: 600;">Conocer más detalles &rarr;</a>
            </article>

            <article class="card card-interactive">
                <span class="badge badge-warn" style="margin-bottom: var(--space-2);">Tienda Física</span>
                <h3>Alimentos, Farmacia y Accesorios</h3>
                <p style="margin-top: var(--space-1);">
                    Consulta en línea la disponibilidad actual de medicamentos veterinarios, alimento y artículos de cuidado en nuestro mostrador.
                </p>
                <a href="/productos" style="font-weight: 600;">Ver catálogo disponible &rarr;</a>
            </article>
        </div>
    </div>
</section>

<!-- BANNER DE UBICACIÓN RÁPIDA -->
<section class="section" style="padding-top: 0;">
    <div class="container">
        <div class="card" style="background-color: var(--color-primary-soft); border-color: var(--color-primary-border); display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: var(--space-3);">
            <div>
                <h2 style="font-size: var(--text-xl); color: var(--color-primary);">Visítanos en Villas de Ntra. Sra. de la Asunción</h2>
                <p style="margin-bottom: 0; margin-top: var(--space-1);">
                    <strong>Dirección:</strong> Ermita de San Sebastián 402, C.P. 20126, Aguascalientes, Ags.
                </p>
            </div>
            <a href="/ubicacion" class="btn btn-primary">Ver Mapa Interactivo</a>
        </div>
    </div>
</section>