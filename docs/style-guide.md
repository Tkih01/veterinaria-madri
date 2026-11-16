# Guía de Estilos y Estándares UI/UX - Veterinaria Madri

## 1. Regla de la Grilla de 8 Puntos (8pt Grid)
Toda dimensión, margen (`margin`), relleno (`padding`) o separación (`gap`) debe utilizar las variables CSS definidas en `:root`:
- `--space-0-5`: `4px` (`0.25rem`) — Solo para etiquetas pequeñas (`badges`).
- `--space-1`: `8px` (`0.5rem`) — Separación entre label e input.
- `--space-2`: `16px` (`1rem`) — Padding lateral en móvil y separación entre campos de formulario.
- `--space-3`: `24px` (`1.5rem`) — Padding interno de tarjetas (`.card`) y gap de columnas en escritorio.
- `--space-4`: `32px` (`2rem`) — Padding lateral del contenedor en escritorio.
- `--space-6`: `48px` (`3rem`) — Espaciado vertical de secciones en móvil.
- `--space-8`: `64px` (`4rem`) — Espaciado vertical de secciones en escritorio.

## 2. Accesibilidad y Contraste (WCAG 2.1 Nivel AA)
- **Prohibido remover el indicador de foco (`outline: none`)** sin proporcionar un reemplazo de alto contraste.
- Todo botón, enlace de menú o campo interactivo debe medir como mínimo **44x44 píxeles** (`--touch-min: 2.75rem`).
- Los estados clínicos (vacunas al día, refuerzos pendientes, disponibilidad de stock) nunca deben depender únicamente del color; siempre deben ir acompañados de texto descriptivo dentro de un `.badge`.

## 3. Nomenclatura de Archivos (Compatibilidad Windows -> Debian)
- Todos los archivos en `/public/assets/` deben nombrarse en **minúsculas y separados por guiones medios** (`kebab-case`), por ejemplo: `consulta-general.webp`, `logo-vetmadri.svg`.