---
name: styles_skill
description: Guía y patrones para Vanilla CSS, paleta GIS, glassmorphism, botones .btns, animación y responsividad en Project_saavedra.
version: 1.0.0
---

# Guía de Estilos y Colores (Styles Skill) — Project Saavedra

> **Directorio de Referencia:** `resources/css/`

## Cuándo usar esta habilidad
- Al crear o modificar archivos CSS en `resources/css/`.
- Al definir colores corporativos GIS, layouts con Flexbox/Grid o animaciones `@keyframes`.
- Al aplicar glassmorphism, clases de estado (`.status-ok`, `.status-error`) o responsive design.

---

## ⚠️ DIRECTIVAS DE EJECUCIÓN OBLIGATORIA PARA EL AGENTE DE IA

Cuando crees o modifiques estilos CSS, DEBES cumplir estrictamente estas 5 reglas:

1. **Tailwind CSS v3 Integrado (`preflight: false`) + Vanilla CSS:** El diseño se compone de **Vanilla CSS corporativo** (`global.css`) junto a utilidades de **Tailwind CSS v3** (`resources/css/tailwind.css`). Prohibido añadir Bootstrap u otros frameworks no configurados.
2. **Importar `global.css`:** En todo CSS de módulo nuevo, incluye `@import "../global.css";` al inicio.
3. **Usar Variables CSS Corporativas:** Usa `var(--gis-green)`, `var(--gis-blue)`, `var(--gis-red)`, `var(--gis-gray)`. NUNCA hardcodees colores hex/rgb sueltos.
4. **Prefijos `-webkit-` SIEMPRE ANTES del estándar:** Por ejemplo `-webkit-backdrop-filter: blur(12px); backdrop-filter: blur(12px);`.
5. **No dejar rulesets vacíos (`.clase {}`):** Todo selector creado debe contener propiedades válidas o comentarse para evitar warnings de IDE.

---

## Instrucciones y Patrones Estándar

## 0. Protocolo Obligatorio — ANTES de Escribir Cualquier Estilo

1. **NUNCA redefinas `*`, `box-sizing`, `font-family` o `:root`** en archivos de módulo — ya están en `resources/css/global.css`.
2. **Importa `global.css` al inicio** de cada CSS de módulo nuevo:
   ```css
   @import "../global.css"; /* o la ruta relativa correcta */
   ```
3. **NUNCA escribas colores hardcoded** (`#0a8504`, `#033966`, etc.) — usa siempre variables CSS (`var(--gis-green)`).
4. **NUNCA uses `style=` en Blade** para colores, fuentes o fondos estáticos — pon la clase en el CSS del módulo.
5. **Para colores condicionales en JS**, usa `classList.add/remove` con clases CSS, NO `element.style.color`.
6. **Excepción PDFs (DomPDF):** Las plantillas en `resources/views/pdf/` y en `piecesReport/*Pdf.blade.php` llevan CSS incrustado en `<style>` porque DomPDF no carga archivos externos.
7. **Vendor prefixes — ORDEN OBLIGATORIO:** El prefijo `-webkit-` SIEMPRE va **antes** de la propiedad estándar, nunca después. Además, cuando uses `-webkit-background-clip`, agrega también `background-clip` sin prefijo:
   ```css
   /* ✅ CORRECTO */
   -webkit-backdrop-filter: blur(12px);
   backdrop-filter: blur(12px);

   -webkit-background-clip: text;
   background-clip: text;         /* ← siempre incluir ambos */
   -webkit-text-fill-color: transparent;

   -webkit-user-select: none;     /* ← Safari requiere el prefijo */
   user-select: none;

   /* ❌ INCORRECTO */
   backdrop-filter: blur(12px);
   -webkit-backdrop-filter: blur(12px); /* webkit DESPUÉS = warning */
   user-select: none;                   /* sin -webkit- = error en Safari */
   ```

**Tabla de propiedades que SIEMPRE requieren `-webkit-` primero:**

| Propiedad estándar | Prefijo requerido | Nota |
|---|---|---|
| `backdrop-filter` | `-webkit-backdrop-filter` | Antes del estándar |
| `background-clip: text` | `-webkit-background-clip: text` | Incluir ambos + `background-clip` |
| `user-select` | `-webkit-user-select` | Safari no soporta sin prefijo |
| `appearance` | `-webkit-appearance` | Safari/iOS antiguo requiere prefijo |
| `text-fill-color` | `-webkit-text-fill-color` | Solo existe con prefijo |
| `scrollbar-width` + `scrollbar-color` | Pseudo-elementos `::-webkit-scrollbar*` | Ver patrón completo abajo |

**Propiedades obsoletas que NUNCA deben usarse:**

| Propiedad obsoleta | Razón / Alternativa |
|---|---|
| `-webkit-overflow-scrolling: touch;` | Ya no es soportado por navegadores modernos. Simplemente usa `overflow-y: auto;` o `overflow-x: auto;`. |
| `min-height: auto;` | No es soportado por Firefox 22+. Usar `min-height: 0;` o omitirlo. |

**Reglas Generales de Limpieza:**
- **Bloques vacíos (Empty rulesets):** Nunca dejes selectores sin propiedades (ej. `.clase {}`). Elimínalos o coméntalos. El IDE marcará un warning "Do not use empty rulesets".

**Patrón obligatorio para scrollbar cross-browser (con `@supports`):**

`scrollbar-width` y `scrollbar-color` **no tienen equivalente `-webkit-`** — Safari usa pseudo-elementos, que es una API completamente distinta. El IDE marcará warning si se ponen dentro del selector directamente. La solución es usar `@supports` + webkit fallback:

```css
/* ✅ PATRÓN CORRECTO — @supports elimina el warning del IDE */
.mi-elemento {
    overflow-y: auto;
    /* NO poner scrollbar-width/color aquí directamente */
}
/* 1. Webkit fallback — Safari, Chrome < 121 (siempre primero como base) */
.mi-elemento::-webkit-scrollbar        { width: 6px; height: 6px; }
.mi-elemento::-webkit-scrollbar-track  { background: #f1f1f1; }
.mi-elemento::-webkit-scrollbar-thumb  { background: var(--gis-blue); border-radius: 3px; }
/* 2. Estándar moderno — Firefox, Chrome 121+ (dentro de @supports) */
@supports (scrollbar-width: thin) {
    .mi-elemento { scrollbar-width: thin; scrollbar-color: var(--gis-blue) #f1f1f1; }
}

/* ❌ INCORRECTO — genera warning en el IDE */
.mi-elemento {
    scrollbar-width: thin;              /* warning: Safari no lo soporta */
    scrollbar-color: var(--gis-blue) #f1f1f1;
}
```

> **Por qué `@supports`:** El IDE respeta que la propiedad está dentro de una feature query y no marca warning, porque el código ya expresa que es uso intencional/progresivo. Sin `@supports`, el warning persiste aunque agregues el fallback webkit.
>
> **Nota:** El scrollbar global ya está en `global.css`. Solo necesitas este patrón cuando el scrollbar del elemento tenga colores distintos al global.




---

## 1. La Base del Diseño (global.css)

El archivo `resources/css/global.css` es la **única fuente de verdad** del sistema de diseño. Contiene:
- Reset universal con `font-family: 'Poppins', sans-serif`
- Variables CSS (`:root`) de toda la paleta GIS
- Clases utilitarias globales (`.btns`, `.overlay-bg`, `.glass-panel`, `.data-table`, etc.)

```css
/* Fuente universal — POPPINS (cargada vía Google Fonts en global.css) */
*, *::before, *::after {
    box-sizing: border-box;
    margin: 0;
    padding: 0;
    font-family: 'Poppins', sans-serif;
}

:root {
    /* Paleta Oficial GIS — NOMBRES DE VARIABLE ESTÁNDAR */
    --gis-green:  #0a8504;   /* Verde principal */
    --gis-blue:   #033966;   /* Azul marino */
    --gis-red:    #9c0303;   /* Rojo peligro */
    --gis-gray:   #404040;   /* Gris oscuro */
    --gis-white:  #ffffff;
    --gis-black:  #000000;

    /* Sombras de color */
    --shadow-green: rgba(10, 133, 4, 0.35);
    --shadow-red:   rgba(156, 3, 3, 0.35);
    --shadow-blue:  rgba(3, 57, 102, 0.35);

    /* Glassmorphism */
    --glass-bg:     rgba(3, 57, 102, 0.22);
    --glass-border: rgba(255, 255, 255, 0.25);
    --glass-shadow: 0 10px 30px rgba(0, 0, 0, 0.4);

    /* Espaciados */
    --sp-xs: 4px; --sp-sm: 8px; --sp-md: 16px; --sp-lg: 24px; --sp-xl: 32px;

    /* Transiciones */
    --trans:      0.2s ease;
    --trans-slow: 0.4s cubic-bezier(0.25, 0.8, 0.25, 1);
}
```

---

## 2. Paleta Oficial Estricta (NO CAMBIAR)

Los colores son la identidad corporativa del Grupo Industrial Saavedra (GIS):

| Color | Hex | Variable CSS | Uso |
|---|---|---|---|
| **Verde Principal** | `#0A8504` | `var(--gis-green)` | Acciones de éxito, botones primarios, confirmaciones |
| **Rojo Peligro** | `#9C0303` | `var(--gis-red)` | Cerrar, scrap, paro de línea, eliminar |
| **Azul Marino** | `#033966` | `var(--gis-blue)` | Paneles, headers, elementos corporativos |
| **Gris Oscuro** | `#404040` | `var(--gis-gray)` | Texto secundario, acentos neutros |
| **Blanco Puro** | `#FFFFFF` | `var(--gis-white)` | Fondos principales |
| **Negro Puro** | `#000000` | `var(--gis-black)` | Texto principal, bordes en PDFs |

> **NUNCA uses colores genéricos como `red`, `blue`, `green`, `#ff0000`** ni hardcodes de la paleta directamente en el CSS — usa las variables `var(--gis-*)`.

---

## 3. Arquitectura del "Glassmorphism" (Paneles Premium)

Cuando crees menús superpuestos, modales o paneles de detalles, usa la arquitectura de cristal esmerilado con gradientes direccionales en `135deg` (inician en su tonalidad base/corporativa normal y se difuminan oscureciéndose progresivamente hacia el vértice opuesto).

Todas las variables y clases utilitarias residen en `resources/css/global.css`:

```css
/* Variables de fondo glassmorphism direccional (135deg: tono normal -> oscureciéndose) */
--glass-bg-white:  /* Blanco cristal que oscurece sutilmente hacia humo/perla */
--glass-bg-blue:   /* Azul marino GIS normal (#033966) oscureciéndose hacia medianoche */
--glass-bg-dark:   /* Grafito oscuro normal oscureciéndose hacia obsidiana */
--glass-bg-yellow: /* Ámbar/dorado cálido normal oscureciéndose hacia caramelo oscuro (alias: --glass-bg-amber) */
--glass-bg-purple: /* Púrpura/violeta imperial normal oscureciéndose hacia berenjena oscuro (alias: --glass-bg-morado) */
--glass-bg-green:  /* Verde GIS normal (#0a8504) oscureciéndose hacia bosque profundo (alias: --glass-bg-verde) */
--glass-bg-red:    /* Rojo GIS normal (#9c0303) oscureciéndose hacia borgoña/vino (alias: --glass-bg-rojo) */
--glass-bg-cyan:   /* Cyan/azul océano normal oscureciéndose hacia azul abisal (alias: --glass-bg-teal) */
--glass-bg-orange: /* Naranja industrial normal oscureciéndose hacia ámbar oscuro (alias: --glass-bg-naranja) */

/* Bordes cristalinos a juego */
--glass-border-white, --glass-border-blue, --glass-border-dark,
--glass-border-yellow, --glass-border-purple, --glass-border-green,
--glass-border-red, --glass-border-cyan, --glass-border-orange
```

**Uso con clase utilitaria `.glass-panel`:**
```html
<!-- Blanco estándar -->
<div class="glass-panel">...</div>

<!-- Con variante de color corporativa o temática -->
<div class="glass-panel glass-blue">...</div>
<div class="glass-panel glass-yellow">...</div>
<div class="glass-panel glass-purple">...</div>
<div class="glass-panel glass-green">...</div>
<div class="glass-panel glass-red">...</div>
```

**Ejemplo personalizado (CSS):**
```css
.mi-contenedor-glass {
    background: var(--glass-bg-blue);
    -webkit-backdrop-filter: blur(20px) saturate(180%);
    backdrop-filter: blur(20px) saturate(180%);
    border: 1.5px solid var(--glass-border-blue);
    box-shadow: var(--glass-shadow-dark);
    border-radius: 16px;
    padding: var(--sp-lg);
}
```

---

## 4. Botones (`.btns`) e Interacciones (Hover/Keyframes)

```css
.btns {
 background-color: var(--gis-green);
 color: #fff;
 border: none;
 padding: 12px 35px;
 border-radius: 8px;
 font-size: 1.2rem;
 font-weight: bold;
 cursor: pointer;
 box-shadow: 0 4px 6px var(--shadow-green);
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
 background-color: var(--gis-red);
 box-shadow: 0 4px 6px var(--shadow-red);
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
 gap: var(--sp-md);
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
 background-color: var(--gis-blue);
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
 border-bottom: 1px solid rgba(0,0,0,0.08);
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

---

## 13. Clases de Estado (Reemplazando Estilos Inline y JS)

En lugar de `style="color: #0a8504"` en Blade o `element.style.color = '#0a8504'` en JS, usa estas clases de `global.css`:

| Clase CSS | Efecto | Variable interna |
|---|---|---|
| `.status-ok` | Color de texto verde | `var(--gis-green)` |
| `.status-error` | Color de texto rojo | `var(--gis-red)` |
| `.status-neutral` | Color de texto gris | `var(--gis-gray)` |
| `.status-info` | Color de texto azul | `var(--gis-blue)` |
| `.bg-status-ok` | Fondo verde suave | `rgba(gis-green, 0.12)` |
| `.bg-status-error` | Fondo rojo suave | `rgba(gis-red, 0.12)` |
| `.bg-status-info` | Fondo azul suave | `rgba(gis-blue, 0.08)` |

**Ejemplo en Blade:**
```blade
{{-- ANTES (incorrecto) --}}
<span style="color: #0a8504">Aprobado</span>

{{-- AHORA (correcto) --}}
<span class="status-ok">Aprobado</span>
```

**Ejemplo en JS:**
```js
// ANTES (incorrecto)
element.style.color = '#9c0303';

// AHORA (correcto)
element.classList.add('status-error');
element.classList.remove('status-ok');
```

---

## 14. Clases Utilitarias de Visibilidad (para JS)

Usa las clases de `global.css` en lugar de manipular `style.display` directamente:

| Clase | Equivalente |
|---|---|
| `.hidden` | `display: none !important` |
| `.visible` | `display: block !important` |
| `.flex-visible` | `display: flex !important` |

```js
// ANTES
element.style.display = 'none';

// AHORA
element.classList.add('hidden');
element.classList.replace('hidden', 'flex-visible');
```

> **Excepción válida:** Si el `display` depende de un valor dinámico que no puede encapsularse en clase fija (ej. grids con columnas variables), puede usarse `style=` inline en ese caso específico.

---

## 15. Creación Dinámica de Componentes DOM en JavaScript

Al construir interfaces o componentes de manera dinámica con `document.createElement()` en JavaScript (`almacen_fundicion.js`, `processProduction.js`, etc.), sigue estas reglas:

1. **Prohibido `style.cssText` o asignaciones masivas inline:** No inyectes cadenas largas de estilos ni redefinas propiedades estáticas (como bordes, sombras, fuentes o paddings) mediante código JavaScript.
2. **Usar Clases Declarativas:** Asigna siempre clases CSS claras mediante `element.className` o `element.classList.add(...)`. Si el componente no tiene clases existentes, define nuevas clases en `resources/css/global.css` o en el CSS del módulo respectivo.
3. **Excepción Válida de Runtime:** Las únicas asignaciones mediante `element.style.*` permitidas son aquellas cuyos valores se calculan en tiempo de ejecución y cambian dinámicamente según los datos:
   - Anchos de barras de progreso: `element.style.width = \`${pct}%\`;`
   - Retardos de animación calculados en bucles: `element.style.animationDelay = \`${i * 0.05}s\`;`
   - Colores o gradientes variables recibidos como parámetro de configuración o base de datos.

```js
// ❌ ANTES (incorrecto - estilo estático inyectado por JS)
const card = document.createElement('div');
card.style.cssText = 'border-radius: 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); padding: 12px; background: #fff; border: 2px solid #d97706;';

// ✅ AHORA (correcto - uso de clases atómicas o de componente)
const card = document.createElement('div');
card.classList.add('alm-card-base', 'alm-card-warning');
```

---

## 16. Estándar Visual para Íconos de Estado Vacío (Sin Carpetas/Sin Archivos)

En las 6 vistas de administración de documentos (`manage_dibujos`, `manage_ayudas`, `manage_ayudas_fundicion`, `manage_fundicion`, `manage_manuales`, `manage_programas`), los contenedores vacíos deben renderizar la imagen de estado con reglas CSS estrictas y estáticas para mantener consistencia visual perfecta:

1. **Dimensiones fijas**: `width: 120px` y `max-width: 120px`.
2. **Opacidad sobria**: `opacity: 0.75` (sin deslumbrar ni competir con otros elementos).
3. **Sin animaciones en hover**: Desactivar cualquier `transform: scale(...)` o animación mediante `transform: none !important` y `transition: none !important`.

```css
/* ✅ PATRÓN OBLIGATORIO: Estado vacío estático en vistas de administración */
.dibujos-empty-state img.empty-folder-icon,
.ayudas-empty-state img.empty-folder-icon,
.programas-empty-state img.empty-folder-icon {
    width: 120px;
    max-width: 120px;
    height: auto;
    opacity: 0.75;
    transform: none !important;
    transition: none !important;
}

.dibujos-empty-state img.empty-folder-icon:hover {
    transform: none !important;
    opacity: 0.75 !important;
}
```
---

## 21. Grid Layout para Acciones y Botones Múltiples

Cuando una tabla o modal contenga un grupo variable de botones de acción que deban verse uniformes, evita el uso de `flex-wrap` con márgenes que se rompen en pantallas pequeñas. En su lugar, usa `display: grid` con `grid-template-columns: repeat(auto-fit, minmax(120px, 1fr))`.

```css
.action-buttons-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
    gap: 12px;
    width: 100%;
}
```

---

## 22. Backdrop / Filtro Azul Corporativo en Modales y SweetAlert2

El fondo oscuro para cualquier modal (sea SweetAlert2, modal flotante `.modal-overlay` o backdrop de Bootstrap) **NUNCA** debe ser negro genérico (`rgba(0, 0, 0, 0.4)` ni `black`). Debe ser el azul marino profundo Saavedra:
```css
background: rgba(0, 10, 25, 0.85) !important;
-webkit-backdrop-filter: none !important;
backdrop-filter: none !important;
```
- **Rendimiento:** Mantener `backdrop-filter: none` en backdrops globales de pantalla completa para garantizar 60 FPS y evitar caídas de cuadros o sensación de lentitud al desplegar ventanas de confirmación.

---

## 23. Manejo de Iconos PNG Blancos en Tablas e Interfaces

Iconos que consisten en siluetas blancas sobre transparencia (ej. `Operador-Icon.png`) quedan completamente ilegibles sobre fondos claros o translúcidos. Deben colocarse dentro de un contenedor circular con fondo corporativo azul sólido:
```css
.operator-icon-box {
    width: 46px;
    height: 46px;
    border-radius: 50%;
    background: linear-gradient(135deg, #033966 0%, #0284c7 100%);
    border: 2.5px solid #0284c7;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    box-shadow: 0 3px 8px rgba(3, 57, 102, 0.22);
}
```

---

## 24. Proporciones y Geometría en Contenedores de Filtros de Fecha

- Los contenedores `.filter-dateFrom`, `.filter-dateTo`, `.filter-date` **NUNCA** deben tener flex-grow o basis mayor que los selectores vecinos:
```css
.filter-dateFrom,
.filter-dateTo,
.filter-date {
    flex: 1 1 120px;
    min-width: 100px;
}
```
- **Botón Disparador (`.btn-date-trigger`):** Debe contar con `width: 100% !important; min-width: 0 !important; max-width: 100% !important; overflow: hidden !important;` para que su geometría nunca deforme el ancho de columna.
- **Etiqueta de Fecha (`.date-trigger-label`):** Usa `flex: 1 1 auto; min-width: 0; text-overflow: ellipsis; white-space: nowrap;`.
- **Botón Embebido de Limpieza Individual (`.btn-clear-single-date`):** Dimensiones compactas fijas (`26px × 24px`, flex `0 0 26px`) con ícono `✕` de 13px.

---

## 25. Cajón Lateral Desplegable de Códigos de Colores

En las vistas con drawer de colores (`systemLogsReport`, `adminPieces`, `releasePieces`), el contenedor `.colors-content` debe tener fondo azul marino al 50% de transparencia con desenfoque:
```css
background: rgba(21, 44, 78, 0.5) !important;
-webkit-backdrop-filter: blur(8px) !important;
backdrop-filter: blur(8px) !important;
```

---

## 26. Estado Vacío de Consultas con Ilustración Superior

Cuando una consulta devuelva 0 registros (`@else` de la tabla), estructurar el mensaje centrado con ícono ilustrativo en la parte superior:
- `.letrero`: Centrado con `display: flex; flex-direction: column; align-items: center; justify-content: center; margin: auto 0;`.
- `.letrero-icon`: Tamaño responsive (`clamp(90px, 11vw, 130px)`), sombra suave `drop-shadow` y animación flotante sutil (`animation: floatEmptyIcon 3.5s ease-in-out infinite`).
- `.advertence`: Color rojo vibrante (`#dc2626 !important;`), peso bold (`700`), mayúsculas y resplandor tenue para máxima legibilidad.

---

## 27. Menú Lateral: Tipografía y Prevención de Saltos de Línea

- Asegurar un ancho suficiente (`width: 310px`) y padding proporcional para que las opciones del menú mantengan una sola línea limpia y elegante.
- Escribir siempre el nombre oficial de la vista: **"Herramientas Planta Tecámac"** (con acento en *Tecámac* y especificando *Planta*).

---

## 28. Estandarización de Controles y Acciones en Reportes de Producción

1. **Ubicación del Botón Cancelar en Cabecera (`header-left`)**:
   - En vistas con barra superior (`appMenu`), el botón Cancelar (`.btn-cancel-report`) debe residir exclusivamente en la cabecera izquierda sustituyendo a "Terminar Reporte", en lugar de flotar desfasado debajo de tarjetas o tablas centrales.
   - Debe compartir exactamente la misma geometría (`height: 40px`, `padding: 0 20px`, `border-radius: 10px`) y estilos que `header .btn-finishReport`.

2. **Botón Confirmar y Registrar en Formularios de Operador (`.btn-submit`)**:
   - En formularios de edición de datos generales del operador, la acción confirmatoria debe llamarse **"Confirmar"** (o **"Registrar"**) y llevar un único icono a la izquierda de `Resultado-Icon.png` (`.btn-submit-icon` a 26px) antes del texto.

3. **Banner de Alerta por Falta de Cotas (`.label-noCotas`)**:
   - Layout en columna centrada (`flex-direction: column; align-items: center; justify-content: center; gap: 16px;`).
   - Icono institucional superior centrado de 72px (`.no-cotas-icon` con `icono_sin_cotas.png` y animación `alert-icon-pulse`) ubicado directamente encima del texto explicativo.

4. **Tarjeta de Perfil de Operador (`.operator-profile-card`)**:
   - Contenedor de avatar `.operator-avatar-wrapper` con dimensiones de 108px × 108px y borde corporativo azul GIS.
   - Icono interior `.operator-avatar-img` a 86px × 86px con micro-interacción suave `scale(1.06)` en hover sobre la tarjeta.

---

## 29. Rendimiento de Modales y Overlays (Anti-Lag)

Los modales se sentían "trabados" porque el **fondo** seguía repintando paneles con `backdrop-filter: blur(24px) saturate(180%)` y animaciones infinitas (`alert-pulse-yellow`, `pulseGlowCotas`) aunque el overlay los tapara.

1. **Congelar el fondo mientras hay overlay** (ya en `processes_views/components/viewers_modal.css`):
   ```css
   body:has(> .prod-viewer-portal) *:not(.prod-viewer-portal, .prod-viewer-portal *),
   body.is-verifying-active *:not(.is-verifying, .is-verifying *) {
       -webkit-backdrop-filter: none !important;
       backdrop-filter: none !important;
       animation-play-state: paused !important;
   }
   ```
   - Nuevos overlays de producción deben usar `.prod-viewer-portal` como hijo directo de `body` para heredar esto.
2. **PROHIBIDO `body:has(.clase)` sin `>`**: obliga a recalcular estilos de toda la página en cada mutación del DOM. Usa una clase en `body` (ej. `is-verifying-active`) o `body:has(> .x)`.
3. **Animaciones de modal/tarjetas**: solo `opacity` + `translateY`, ≤ 0.22s, `ease-out`. Nada de `scale` con rebote (`cubic-bezier(...1.275)`).
4. **Nunca `transition: all`** en modales: lista solo las propiedades (`transform`, `border-color`, `background-color`).
5. **Nunca `will-change` en elementos repetidos** (tarjetas de grid): crea una capa GPU por elemento.
6. **Sombras**: máximo 1 `box-shadow` en el contenedor del modal; no animar `box-shadow` en hover.
7. Agregar `contain: layout paint;` al contenedor del modal para aislar su repintado.
8. **Modales de verificación inline (contraseñas):** El contenedor (pastilla blanca `.form-inline-verification` / `.form-group-password`) y las columnas deben ser 100% estáticos en hover (`transform: translateY(-50%) !important`). Solo el botón para regresar (`.img-action-btn:hover` / `.img-edit:hover`) y el botón para verificar (`.btn-submit-password:hover`) deben reaccionar al cursor.

---

## 30. Estandarización de Iconos en Etiquetas (Labels) y Botones de Acción

1. **Un solo icono a la izquierda**: En todas las etiquetas de formulario y tablas (`.form-label`, `.table-meta-th-content`, `.table-code-th-content`, `.warning-title-pill`), debe existir únicamente un icono a la izquierda del texto; el icono simétrico derecho queda eliminado.
2. **Botones de acción unificados**: Los botones primarios (`.btn-submit`, `.btn-savePiece`, `.btn-finishReport`, `.btn-cancel-report`, `.btn-cancel-pieces`) llevan únicamente un icono a la izquierda a 26px × 26px (`Resultado-Icon.png`, `Terminar-Repo-Icon.png`, `Cancelar-Icon.png`).
3. **Excepción de encabezados**: Los encabezados principales de tarjeta `Reporte de Operador` y `Datos del Operador` (`.panel-section-header`) conservan sus iconos en ambos extremos.
4. **Dimensión uniforme de 26px**: Todos los iconos de etiquetas y botones (`.form-label-icon`, `.table-meta-icon`, `.warning-pill-icon`, `.table-code-icon`, `.btn-submit-icon`, `.finish-report-icon`, `.cancel-report-icon`, `.save-piece-icon`) deben medir exactamente `26px × 26px` con `object-fit: contain`.

---

## 31. Estilización de Campos Bloqueados en Formularios de Reporte

Al editar datos generales de un reporte (`edit == 1`):
1. **Inputs bloqueados (`.input-locked`)**: Fondo grisáceo sutil (`linear-gradient(135deg, #f1f5f9 0%, #e2e8f0 100%)`), borde `#cbd5e1`, texto atenuado `#475569`, sombra interior tenue, `cursor: not-allowed !important`, y `-webkit-user-select: none; user-select: none;`.
2. **Badge indicador (`.locked-field-badge`)**: Pastilla redondeada junto al label con icono grayscale de candado (`bloqueado.png`) y texto en mayúsculas `BLOQUEADO`.
3. **Banner informativo (`.edit-meta-notice-banner`)**: Borde punteado azul corporativo con fondo translúcido que aclara al operador qué campos están protegidos.
4. **Extensión de tabla en edición de piezas (`edit == 2`)**: `.is-edit-pieces-mode` en `html` y `body` activa `overflow-y: auto !important` en la vista completa, y `.scrollable-table` pasa a `max-height: 760px !important`, permitiendo ver hasta ~12 piezas a la vez sin scroll interno si son pocas, o scroll contenido si son muchas (evitando una página kilométrica).

---

## 32. Presupuesto Visual de Altura en Tablas con Cabeceras Sticky y Aceleración GPU

1. **Rango Óptimo de Altura para Tablas Interactivas (`.scrollable-table`)**:
   - Las cabeceras fijas (`.table-title`: 56px, `C.nominal`: 50px, `Tolerancias`: 50px) consumen ~156px de altura superior.
   - Para que el operador visualice al mismo tiempo la pieza anterior maquinada (50px) y los inputs de la pieza activa en captura (50px) con su scroll horizontal sin empujar el botón de Guardar (`.btn-savePiece` / `.table-actions-container`) fuera del límite visible de pantalla (viewport fold), el rango ideal es:
     ```css
     min-height: 290px;
     max-height: 335px;
     margin-top: 0.25rem;
     margin-bottom: 0.25rem;
     ```
   - *Regla*: Si `max-height` supera 350px, el botón inferior se corta en pantallas de planta y laptops estándar. Si es menor a 275px, las filas de piezas no caben simultáneamente.

2. **Diferenciación de Cotas Nominales y Tolerancias (Campos Guía No Editables)**:
   - Para que las filas no editables de referencia se distingan claramente de las piezas capturadas sin parecer inputs "rotos", usar fondo azul tenue y texto nítido:
     ```css
     .table tr.table-row-cNominals td {
         background-color: #d6e8f8 !important;
     }
     .table tr.table-row-cNominals td:first-child {
         background-color: #eaf2fa !important;
         color: var(--gis-blue) !important;
         font-weight: 800 !important;
     }
     .table tr.table-row-cNominals input:disabled {
         background-color: transparent !important;
         color: #033966 !important;
         -webkit-text-fill-color: #033966 !important;
         opacity: 1 !important;
     }
     ```

3. **Aceleración GPU sin Repintados en Botones con Destellos (`buttonShineSweep`, `pulseGlowBlue`)**:
   - En botones con animaciones de barrido o sombras continuas, es obligatorio promoverlos a su propia capa de composición GPU para evitar temblores (*jitter*) o invalidaciones de layout:
     ```css
     -webkit-transform: translateZ(0);
     transform: translateZ(0);
     -webkit-backface-visibility: hidden;
     backface-visibility: hidden;
     contain: paint;
     isolation: isolate;
     ```
   - Dentro de los `@keyframes`, mantener `translateZ(0)` en cada etapa de `transform: translateX(...) skewX(...) translateZ(0)`.

---

## 33. Arquitectura de Botones Corporativos Ultra-Fluidos (60 FPS GPU Compositing)

Para evitar que los efectos de barrido luminoso (`buttonShineSweep`) o resplandor (`pulseGlow`) se sientan "trabados", lentos o con tirones en vistas con `backdrop-filter: blur(...)` o tablas grandes:

1. **Aislamiento absoluto en capa de GPU (Composite Layer):**
   ```css
   /* ✅ OBLIGATORIO en botones con destello (.btn-finishReport, .btn-savePiece, .btn-submit, .btn-accept-release, .btn-submit-password) */
   -webkit-transform: translateZ(0);
   transform: translateZ(0);
   -webkit-backface-visibility: hidden;
   backface-visibility: hidden;
   contain: paint;
   isolation: isolate;
   overflow: hidden;
   ```
   Esto aísla el repintado del botón; el navegador nunca recompondrá los filtros difuminados del fondo ni las tablas al animar el destello.

2. **Rayo oblicuo sobre `::after` con `translateZ(0)` y `will-change: transform`:**
   ```css
   .btn-action::after {
       content: '';
       position: absolute;
       top: 0; left: 0; width: 60%; height: 100%;
       background: linear-gradient(
           90deg,
           transparent 0%,
           rgba(255, 255, 255, 0.05) 20%,
           rgba(255, 255, 255, 0.85) 50%,
           rgba(255, 255, 255, 0.05) 80%,
           transparent 100%
       );
       -webkit-transform: translateX(-160%) skewX(-25deg) translateZ(0);
       transform: translateX(-160%) skewX(-25deg) translateZ(0);
       will-change: transform;
       -webkit-backface-visibility: hidden;
       backface-visibility: hidden;
       pointer-events: none;
       border-radius: inherit;
       z-index: 5;
   }
   ```

3. **Velocidad y Curva Estándar a 1.6s:**
   - La duración fija óptima es **`1.6s`** (nunca 2.0s ni más lenta en botones compactos).
   - El keyframe corre rápido del `0%` al `45%` y **descansa fuera de pantalla del `45%` al `100%`**, logrando un barrido orgánico y natural:
     ```css
     @-webkit-keyframes buttonShineSweep {
         0% {
             -webkit-transform: translateX(-160%) skewX(-25deg) translateZ(0);
             transform: translateX(-160%) skewX(-25deg) translateZ(0);
         }
         45%, 100% {
             -webkit-transform: translateX(280%) skewX(-25deg) translateZ(0);
             transform: translateX(280%) skewX(-25deg) translateZ(0);
         }
     }
     ```

4. **Diferenciación de interacción:**
   - **Botones rojos / críticos (`.btn-finishReport`, `.btn-cancel-report`, `.btn-cancel-pieces`, `.btn-close-session-operator`):** Barrido continuo permanente para advertir al usuario.
   - **Botones constructivos / guardar (`.btn-savePiece`, `.btn-submit`, `.btn-accept-release`, `.btn-submit-password`, `.btn-start-report`):** El barrido y el `pulseGlow` se activan exclusivamente en `:hover` para no saturar la vista durante la captura de datos.

5. **Paneles y Drawers Desplegables de Alto Rendimiento (ej. `.div-new-report`):**
   - **Prohibido animar `width`:** Modificar el ancho durante una transición fuerza reflow y layout recalculation en cada fotograma, colapsando los FPS en pantallas con `backdrop-filter`.
   - **Desplazamiento por GPU:** Definir un ancho fijo (`width: min(50vw, 760px);`) y desplazarlo con `transform: translateX(calc(100% - 64px)) translateZ(0)` al estar colapsado y `translateX(0)` al estar abierto/hover.
   - **Aislamiento absoluto:** Aplicar `contain: paint; isolation: isolate; will-change: transform;`.
   - **DOM interior estático:** No destruir ni reescribir con `innerHTML` en eventos de mouse; estructurar el HTML una sola vez y alternar visibilidad mediante `opacity` y `transform: translateX(...)`.

---

## 34. Minicontenedores y Badges de Estado Vivos (No Tenues)

En modales y tablas de producción, los badges de estado no deben usar tonos pasteles apagados:

1. **Acabado de Alto Contraste:** Gradiente sólido saturado, tipografía blanca nítida (`#ffffff !important`), borde blanco translúcido (`border: 2px solid rgba(255, 255, 255, 0.85)`), brillo interior (`box-shadow: inset 0 1px 2px rgba(255, 255, 255, 0.55)`), y resplandor perimetral acorde al estado.
2. **Micro-interacción Hover:** `-webkit-transform: translateY(-2px) scale(1.06); transform: translateY(-2px) scale(1.06);` con expansión de sombra para una respuesta táctil inmediata.
3. **Mapeo de Colores:**
   - **Liberado:** Gradiente `#0284c7 -> #0369a1` con resplandor azul.
   - **Rechazado / Error:** Gradiente `#dc2626 -> #b91c1c` con resplandor carmesí.
   - **Incompleto:** Gradiente `#d97706 -> #b45309` con resplandor ámbar.
   - **Pieza OK (Sin liberar):** Gradiente `#059669 -> #047857` con resplandor esmeralda.
   - **Pendiente:** Gradiente `#9333ea -> #7e22ce` con resplandor púrpura.

---

## 35. Estilizado Directo de Contenedores `<option>` en Selects

Al crear menús de acciones o estatus en controles `<select>`:
1. **Fondo en el contenedor de cada opción:** Aplicar `background-color` con `!important` y `color: #ffffff !important` directamente sobre cada `option` según su valor (`option[value="1"]`, etc.), en lugar de teñir solo el texto sobre fondo blanco.
2. **Prohibido agregar viñetas manuales (`●`, `•`):** No inyectar caracteres de viñeta en el string del texto (`text: "Liberar"`, nunca `"● Liberar"`), para evitar renderizados inconsistentes o ruidosos entre plataformas.
3. **Select Activo:** Al seleccionar una opción, el `<select>` hereda el fondo vibrante y sustituye el icono chevron SVG por uno de trazo blanco puro.

---

## 36. Arquitectura Híbrida de Prioridad de Captura y Foco en Tabla para Pantallas de Planta

En vistas de producción con paneles de metadatos superiores y tabla interactiva inferior (`processProduction`):

1. **Prevención de Recorte por `overflow-y: hidden`:**
   - `html, body` debe conservar `min-height: 100vh; overflow-y: auto; overflow-x: hidden;` con scrollbars delgados para garantizar que en pantallas pequeñas (1366×768 o laptops con escala Windows 125%/150%) el botón de confirmación/guardado nunca quede inaccesible fuera de pantalla.

2. **Ajuste Fluido por Media Queries de Altura (`@media (max-height: 860px)` y `@media (max-height: 740px)`):**
   - Reducir paddings de `.container-meta`, fuentes de inputs (a 30px) y comprimir banners de advertencia (eliminando iconos decorativos gigantes o reduciéndolos a 38px) para que la mitad superior baje de ~440px a ~220px, dejando espacio suficiente a la tabla sin necesidad de hacer zoom al 33%.

3. **Modo Foco en Tabla (`body.table-focus-mode`):**
   - Al capturar piezas, la mitad superior se pliega en una **Barra de Resumen Ultra-Compacta** (`.meta-summary-strip` de ~44px de alto) mostrando: `OT | Proceso | Máquina | Turno | Meta | Realizadas | Restantes | Operador` y un botón para desplegar detalles.
   - La tabla `.scrollable-table` pasa a ocupar hasta `calc(100vh - 165px)`, otorgándole el 80% de la pantalla para ver múltiples piezas a la vez con el botón GUARDAR siempre visible.
   - La preferencia se persiste en `localStorage.getItem('gis_production_table_focus')` y se alterna con un botón en cabecera (`.btn-toggle-focus`) y disparadores en el área de trabajo (`.btn-quick-focus-fold` y `.btn-expand-meta`).

---

## 37. Presupuesto Horizontal Fluido y Efectos Neón Sin Bordes para Banners de Alerta

En paneles de producción de alta densidad (`processProduction`):

1. **Presupuesto Horizontal Fluido (Evitar desbordes en monitores de 1366×768 / 1280×1024):**
   - **NUNCA usar anchos fijos masivos:** Evitar `flex: 0 0 480px` en tarjetas laterales (`.principal-data`, `.div-table-code`). En su lugar usar `clamp()` dinámico (ej. `clamp(230px, 18vw, 300px)`) y media queries (`@media (max-width: 1400px)` y `@media (max-width: 1200px)`).
   - **Tablas de datos centrales:** Usar siempre `table-layout: fixed; width: 100%;` en `.table-meta` para que las columnas métricas se repartan proporcionalmente (20% c/u) y no colapsen los paneles adyacentes.

2. **Efecto Neón Perimetral Sin Bordes (`.warning-pieces`):**
   - Cuando un banner requiera máxima visibilidad y efecto de advertencia urgente pero el diseño exija **cero bordes** (`border: none !important`), lograr el efecto neón mediante capas compuestas de `box-shadow`:
     - **Resplandor interior en las orillas (`inset`):** Iluminar el perímetro interno con `inset 0 0 8px rgba(255, 255, 255, 0.6)` e `inset 0 0 18px rgba(251, 191, 36, 0.45)`.
     - **Corona de neón exterior:** Núcleo láser (`0 0 10px rgba(255, 215, 0, 0.8)`), dispersión media (`0 0 24px rgba(245, 158, 11, 0.65)`) y aura ambiental (`0 0 38px rgba(234, 88, 12, 0.45)`).
     - **Animación de respiración neón:** Animar la expansión y contracción lumínica (`alertPulseAmberNeon`) de forma periódica (2.4s) para alertar visualmente sin alterar la geometría de la interfaz.

---

## 38. Ecosistema Tailwind CSS v3 + Vanilla CSS (Coexistencia y Reglas de Oro)

Tailwind CSS v3 está formalmente integrado en el proyecto para acelerar la construcción de componentes y reducir el código CSS manual repetitivo, manteniendo al 100% la identidad visual de Grupo Industrial Saavedra.

### 1. Configuración Base (`tailwind.config.js`)
- **`preflight: false`:** Deshabilitado estrictamente para no resetear estilos base del navegador, permitiendo que los botones `.btns`, tablas y formularios existentes se vean exactamente igual.
- **Paleta Oficial Extendida (`theme.extend.colors.gis.*`):**
  - `bg-gis-blue`, `text-gis-blue`, `border-gis-blue` (`#033966`)
  - `bg-gis-green`, `text-gis-green` (`#0a8504`)
  - `bg-gis-red`, `text-gis-red` (`#9c0303`)
  - `bg-gis-gray`, `text-gis-gray` (`#404040`)
  - `bg-gis-dark` (`#012344`)
- **Tipografía:** `font-sans` configurada con `"Poppins", sans-serif`.

### 2. Archivo de Entrada y Compilación (`resources/css/tailwind.css`)
Tailwind compila exclusivamente a través de su archivo dedicado:
```css
@tailwind base;
@tailwind components;
@tailwind utilities;
```
Este archivo se carga en los layouts principales mediante `@vite(['resources/css/tailwind.css', ...])`.

### 3. Patrón de Diseño Híbrido (Vanilla + Tailwind)
Puedes combinar libremente clases corporativas existentes con utilidades de Tailwind:
```blade
{{-- Contenedor Glassmorphism tradicional con flex y espaciado de Tailwind --}}
<div class="glass-panel flex flex-col md:flex-row items-center justify-between gap-4 p-6 shadow-xl">
    <div class="flex items-center gap-3">
        <img src="{{ icon('ot') }}" alt="OT" class="w-10 h-10 object-contain drop-shadow" />
        <h2 class="text-gis-blue font-bold text-xl uppercase tracking-wide">Órdenes de Trabajo</h2>
    </div>
    <button class="btns blue flex items-center gap-2 hover:scale-105 transition-transform">
        <span>Nueva Orden</span>
    </button>
</div>
```

---

## 39. Estilos Corporativos para Modales con Fricción Positiva (`.swal2-gis-popup`)

Para garantizar la coherencia visual y UX premium en modales de SweetAlert2 y notificaciones:

### 1. Alineación a la Derecha de Botones de Acción
Todo modal interactivo o destructivo debe alinear sus botones a la derecha para respetar el flujo de lectura y decisión:
```css
.swal2-actions {
    display: flex !important;
    justify-content: flex-end !important;
    width: 100% !important;
    margin: 1.25rem 0 0 0 !important;
    padding: 0 1.5rem !important;
    gap: 12px !important;
}
```

### 2. Contenedor de Fricción Positiva (`.swal2-gis-popup`)
- **Tipografía:** `'Poppins', sans-serif`.
- **Alineación de Texto:** `text-align: left !important;` en títulos y párrafos explicativos.
- **Bordes y Sombras:** `border-radius: 16px !important;` con elevación y sombras suaves.
- **Prohibido Íconos:** No incluir íconos circulares decorativos de advertencia que distraigan la atención del usuario.

### 3. Notificaciones Toast Oficiales (`.swal2-toast`)
- Fijadas en la esquina inferior derecha (`position: bottom-end`).
- Fondo blanco puro o dark mode según layout, con barra de progreso estilizada (`timerProgressBar: true`) y transición fluida `.swal2-toast-in` y `.swal2-toast-out`.

---

## 40. Estilos Corporativos para Selector de Fecha Universal (`.gis-datepicker-wrapper`)

Para garantizar que todos los selectores de fecha compartan la misma identidad visual GIS:

1. **Estructura del Trigger:**
   ```css
   .gis-datepicker-wrapper {
       position: relative;
       width: 100%;
       display: inline-flex;
       align-items: center;
       box-sizing: border-box;
   }

   .gis-datepicker-display {
       width: 100%;
       cursor: pointer !important;
       padding-right: 38px !important;
       -webkit-user-select: none; /* Safari / iOS */
       user-select: none;
       box-sizing: border-box;
       transition: all 0.2s ease;
   }

   .gis-datepicker-display.is-empty {
       color: #64748b;
   }
   ```

2. **Botón e Ícono de Calendario:**
   - Ubicado a la derecha con posicionamiento absoluto (`right: 8px; top: 50%; transform: translateY(-50%);`).
   - El icono es [`Fecha.png`](file:///c:/Users/Jaxer020406/Documents/GitHub/Project_saavedra/public/images/Color_Icons/Generales/Fecha.png).
   - **Inversión sobre Fondos Oscuros:** Si el input tiene clase `.input-filter` (fondo azul oscuro `--gis-blue`), el icono se invierte automáticamente a blanco mediante:
     ```css
     .input-filter ~ .gis-datepicker-btn img {
         filter: brightness(0) invert(1);
         opacity: 0.9;
     }
     ```

3. **Superposición de Capas (Z-Index):**
   - El modal `#modalCmCalendar` debe tener siempre `z-index: 999999 !important;` para garantizar que se sobreponga limpiamente incluso sobre ventanas de SweetAlert2 (`z-index: 1060+`) y drawers laterales.

