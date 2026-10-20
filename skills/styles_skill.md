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

1. **NO USAR TailwindCSS ni Bootstrap:** Todo el diseño es **Vanilla CSS** con la fuente **Poppins**.
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


