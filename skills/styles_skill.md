# Guía de Estilos y Colores (Styles Skill) — Project Saavedra

> ** Directorio de Referencia:** `public/css/ y resources/css/`
> *`Project_saavedra` NO usa TailwindCSS ni Bootstrap. Todo es Vanilla CSS. El diseño debe ser absolutamente premium.*

---

## 1. La Base del Diseño (CSS Reset y Variables)

```css
*, *::before, *::after {
 box-sizing: border-box;
 margin: 0;
 padding: 0;
 font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
}

:root {
 /* Paleta Oficial GIS */
 --color-primary-green: #0a8504;
 --color-primary-blue: #033966;
 --color-danger-red: #9c0303;
 --color-bg-light: #ffffff;
 --color-text-dark: #000000;
 --color-text-muted: #404040;
 --color-border: rgba(0, 0, 0, 0.1);
 --transition-speed: 0.25s;
 /* Espaciados estándar */
 --space-xs: 4px;
 --space-sm: 8px;
 --space-md: 16px;
 --space-lg: 24px;
 --space-xl: 32px;
}
```

---

## 2. Paleta Oficial Estricta (NO CAMBIAR)

Los colores son la identidad corporativa del Grupo Industrial Saavedra (GIS):

| Color | Hex | Uso |
|---|---|---|
| **Verde Principal** | `#0A8504` | Acciones de éxito, botones primarios, confirmaciones |
| **Rojo Peligro** | `#9C0303` | Cerrar, scrap, paro de línea, eliminar |
| **Azul Marino** | `#033966` | Paneles, headers, elementos corporativos |
| **Gris Oscuro** | `#404040` | Texto secundario, acentos neutros |
| **Blanco Puro** | `#FFFFFF` | Fondos principales |
| **Negro Puro** | `#000000` | Texto principal, bordes en PDFs |

> **NUNCA uses colores genéricos como `red`, `blue`, `green`, `#ff0000`** en vistas del proyecto.

---

## 3. Arquitectura del "Glassmorphism" (Paneles Premium)

Cuando crees menús superpuestos, modales o paneles de detalles, usa esta arquitectura de cristal esmerilado.

```css
.glass-container {
 /* Fondo translúcido (25% opacidad del azul marino) */
 background: rgba(3, 57, 102, 0.25);

 /* El filtro que hace la magia — Siempre webkit ANTES */
 -webkit-backdrop-filter: blur(12px);
 backdrop-filter: blur(12px);

 /* Borde fino para delimitar el "cristal" */
 border: 1px solid rgba(255, 255, 255, 0.3);

 /* Sombra para despegue del fondo */
 box-shadow: 0 10px 30px rgba(0, 0, 0, 0.4);

 border-radius: 12px;
 padding: var(--space-lg);
}
```

---

## 4. Botones (`.btns`) e Interacciones (Hover/Keyframes)

```css
.btns {
 background-color: var(--color-primary-green);
 color: #fff;
 border: none;
 padding: 12px 35px;
 border-radius: 8px;
 font-size: 1.2rem;
 font-weight: bold;
 cursor: pointer;
 box-shadow: 0 4px 6px rgba(10, 133, 4, 0.3);
 transition: transform 0.2s cubic-bezier(0.25, 0.8, 0.25, 1),
 box-shadow 0.2s cubic-bezier(0.25, 0.8, 0.25, 1),
 background-color 0.2s ease;
}

.btns:hover {
 transform: translateY(-2px) scale(1.03);
 box-shadow: 0 8px 15px rgba(10, 133, 4, 0.5);
}

.btns:active {
 transform: translateY(1px) scale(0.98);
}

/* Variante de peligro (rojo) */
.btns.danger {
 background-color: var(--color-danger-red);
 box-shadow: 0 4px 6px rgba(156, 3, 3, 0.3);
}

.btns.danger:hover {
 box-shadow: 0 8px 15px rgba(156, 3, 3, 0.5);
}

/* Botón deshabilitado */
.btns:disabled,
.btns[disabled] {
 opacity: 0.6;
 cursor: not-allowed;
 transform: none !important;
 box-shadow: none !important;
}
```

---

## 5. Diseño Responsivo (Breakpoints Estándar)

La aplicación se visualiza en tabletas industriales y teléfonos de operadores.

- **Móviles:** `max-width: 480px`
- **Tablets:** `max-width: 768px`
- **Escritorio/Pantallas grandes:** `min-width: 769px`

```css
/* Grid responsiva base */
.grid-layout {
 display: grid;
 grid-template-columns: repeat(3, 1fr);
 gap: 20px;
}

@media (max-width: 768px) {
 .grid-layout {
 grid-template-columns: repeat(2, 1fr);
 }
}

@media (max-width: 480px) {
 .grid-layout {
 grid-template-columns: 1fr;
 }
 .btns {
 width: 100%;
 }
}
```

---

## 6. Maquetación Moderna (Flexbox & Grid)

> En PDFs con DomPDF está **prohibido** usar Flexbox/Grid. En vistas web **es obligatorio** usarlos.

```css
/* Alineación perfecta con Flexbox */
.card-header {
 display: flex;
 justify-content: space-between;
 align-items: center;
 gap: var(--space-md);
}

/* Distribución de tarjetas auto-responsiva con Grid */
.card-container {
 display: grid;
 grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
 gap: 1.5rem;
}
```

---

## 7. Temas e Integración de Modo Oscuro (Dark Mode)

```css
body.dark-theme {
 --color-bg-light: #121212;
 --color-text-dark: #ffffff;
 --color-text-muted: #aaaaaa;
 --color-border: rgba(255, 255, 255, 0.1);
 --color-primary-blue: #090099;
}
```

*Toda la aplicación cambiará de colores automáticamente si usaste `var(...)` en tus selectores CSS.*

---

## 8. Z-Index Management

Evita los `z-index: 999999`. Sigue esta escala lógica:

| z-index | Uso |
|---|---|
| `10` | Elementos flotantes relativos (tooltips) |
| `50` | Headers y Navbars |
| `100` | Filtros de oscurecimiento (`.overlay-bg`) |
| `200` | Modales y Popups |
| `300` | Alertas de SweetAlert2 (maneja su propio z-index) |

---

## 9. Animaciones Complejas (Keyframes)

```css
/* Fade-in estándar del proyecto */
.fade-in {
 animation: fadeInAnimation 0.5s ease-out forwards;
}

@keyframes fadeInAnimation {
 0% { opacity: 0; transform: translateY(20px); }
 100% { opacity: 1; transform: translateY(0); }
}

/* Slide-in lateral para paneles */
.slide-in-right {
 animation: slideInRight 0.3s ease-out forwards;
}

@keyframes slideInRight {
 0% { opacity: 0; transform: translateX(30px); }
 100% { opacity: 1; transform: translateX(0); }
}

/* Spinner de carga simple */
.spinner {
 width: 20px;
 height: 20px;
 border: 3px solid rgba(255,255,255,0.3);
 border-top-color: #fff;
 border-radius: 50%;
 animation: spin 0.8s linear infinite;
 display: inline-block;
 vertical-align: middle;
}

@keyframes spin {
 to { transform: rotate(360deg); }
}
```

---

## 10. Fondos en Grids (Aprobados vs Rechazados)

Para diferenciar documentos aprobados de rechazados, aplica el `background-color` al **contenedor** `.alm-pdf-grid`, NO a las tarjetas individuales.

```css
/* Aprobado — verde suave */
.alm-pdf-grid.aprobado {
 background-color: #f0fdf4;
 border: 1px solid #bbf7d0;
 padding: 15px;
 border-radius: 8px;
}

/* Rechazado — rojo suave */
.alm-pdf-grid.rechazado {
 background-color: #fef2f2;
 border: 1px solid #fecaca;
 padding: 15px;
 border-radius: 8px;
}
```

O en línea cuando no hay clase disponible:
```html
<div class="alm-pdf-grid" style="background-color: #fef2f2; padding: 15px; border-radius: 8px; border: 1px solid #fecaca;">
```

---

## 11. Clases CSS Clave del Proyecto (No Redefinir)

| Clase CSS | Uso |
|---|---|
| `.alm-table-card` | Tarjeta contenedora de tablas en Almacén/Calidad |
| `.alm-table-header` | Encabezado azul de tarjeta con contador de resultados |
| `.alm-results-count` | Contador de resultados en la cabecera de tabla |
| `.alm-pdf-grid` | Grid de tarjetas de documentos PDF |
| `.dibujos-file-card` | Tarjeta individual de archivo (dibujo, ayuda, otro) |
| `.card-otro` | Variante de tarjeta para documentos administrativos |
| `.btn-dibujos` | Botón de acción en tarjetas de archivos |
| `.btn-dibujos-sm` | Variante compacta del botón |
| `.btn-ver` | Botón "Ver" (fondo verde o rojo según contexto) |
| `.btn-eliminar` | Botón "X" de eliminación de archivo |
| `.file-icon` | Ícono del archivo (normal) |
| `.icon-hover` | Ícono alternativo que aparece al hacer hover |
| `.btns` | Botón de acción primaria del proyecto |
| `.fade-in` | Animación de aparición suave |

---

## 12. Tablas de Datos — Estilo Premium

```css
/* Tabla de datos estándar del proyecto */
.data-table {
 width: 100%;
 border-collapse: collapse;
 font-size: 0.9rem;
 background-color: #fff;
 border-radius: 8px;
 overflow: hidden;
 box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.data-table thead {
 background-color: var(--color-primary-blue);
 color: #fff;
}

.data-table thead th {
 padding: 12px 16px;
 text-align: left;
 font-weight: 600;
 font-size: 0.85rem;
 letter-spacing: 0.05em;
 text-transform: uppercase;
}

.data-table tbody tr {
 border-bottom: 1px solid var(--color-border);
 transition: background-color 0.15s ease;
}

.data-table tbody tr:hover {
 background-color: rgba(3, 57, 102, 0.04);
}

.data-table tbody td {
 padding: 10px 16px;
 vertical-align: middle;
}
```
