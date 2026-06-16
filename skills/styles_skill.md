# 🎨 Guía de Estilos y Colores (Styles Skill) - Máximo Nivel

`Project_saavedra` no usa TailwindCSS ni Bootstrap. Todo es Vanilla CSS. El diseño debe ser absolutamente premium, intuitivo y responsivo.

## 1. La Base del Diseño (CSS Reset y Variables)
El proyecto asume que todo elemento tiene caja por bordes y no tiene márgenes base.

```css
*, *::before, *::after {
    box-sizing: border-box;
    margin: 0;
    padding: 0;
    /* Fuente moderna por defecto (Inter o Segoe UI) */
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; 
}

/* Colores institucionales definidos en root */
:root {
    --color-primary-green: #0a8504;
    --color-primary-blue: #030041;
    --color-danger-red: #b30404;
    --color-bg-light: #f4f7f6;
    --color-text-dark: #1a1a1a;
    --color-text-muted: #666666;
    --color-border: rgba(0, 0, 0, 0.1);
    --transition-speed: 0.25s;
}
```

## 2. Paleta Oficial Estricta
Los colores no son sugerencias. Son la identidad del corporativo.

- **Verde Principal (Éxito/Acción):** `#0C8201` (o `--color-primary-green`)
- **Rojo Peligro (Cerrar/Scrap/Paro):** `#9D0402` (o `--color-danger-red`)
- **Azul Marino Saavedra (Paneles/Headers):** `#033861` (o `--color-primary-blue`)
- **Gris Oscuro (Acentos):** `#424141` (o `--color-dark-gray`)
- **Fondo General/Gris Neutro:** `#f4f7f6`
- **Textos Oscuros (Lectura):** `#151114` o `#070707`

## 3. Arquitectura del "Glassmorphism" (Paneles Premium)
Cuando crees menús superpuestos (como el menú hamburguesa o paneles de detalles), usa esta arquitectura de cristal esmerilado que deja ver el fondo desenfocado.

```css
.glass-container {
    /* 1. Fondo translúcido (25% opacidad) */
    background: rgba(3, 57, 102, 0.25); 
    
    /* 2. El filtro que hace la magia (Prefijos primero para evitar warnings del IDE) */
    -webkit-backdrop-filter: blur(12px); 
    backdrop-filter: blur(12px);
    
    /* 3. Borde fino para delimitar el "cristal" */
    border: 1px solid rgba(255, 255, 255, 0.3);
    
    /* 4. Sombra para despegue del fondo */
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.4);
    
    border-radius: 12px;
}
```

## 4. Botones (`.btns`) e Interacciones (Hover/Keyframes)
El usuario debe sentir que la aplicación está viva. Todo botón debe reaccionar.

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
    /* Sombras base */
    box-shadow: 0 4px 6px rgba(10, 133, 4, 0.3);
    /* Transición crítica para que no sea un cambio brusco */
    transition: transform 0.2s cubic-bezier(0.25, 0.8, 0.25, 1), 
                box-shadow 0.2s cubic-bezier(0.25, 0.8, 0.25, 1),
                background-color 0.2s ease;
}

.btns:hover {
    transform: translateY(-2px) scale(1.03); /* Elevar y zoom */
    box-shadow: 0 8px 15px rgba(10, 133, 4, 0.5); /* Sombra más grande */
}

.btns:active {
    transform: translateY(1px) scale(0.98); /* Sensación de presionar */
}
```

## 5. Diseño Responsivo (Breakpoints Estándar)
La aplicación se visualiza en tabletas industriales y teléfonos de operadores. Debes diseñar responsivo:

- **Breakpoints:**
  - Móviles: `max-width: 480px`
  - Tablets: `max-width: 768px`
  - Escritorio/Pantallas: `min-width: 769px`

```css
/* Ejemplo de grid responsiva */
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
        width: 100%; /* Botones de ancho completo en móvil */
    }
}
```

## 6. Maquetación Moderna (Flexbox & Grid)
Aunque en la generación de PDFs por `domPDF` está prohibido el uso de Flexbox/Grid, en las vistas web **es obligatorio** usarlos para crear layouts alineados y limpios.

- **Alineación perfecta con Flexbox:**
```css
.card-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
}
```

- **Distribución de tarjetas con Grid:**
```css
.card-container {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: 1.5rem;
}
```

## 7. Temas e Integración de Modo Oscuro (Dark Mode)
Si la página soporta el cambio de tema para pantallas nocturnas de operadores:
Define variables de colores invertidas bajo la clase `.dark-theme` en el body.

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

## 8. Z-Index Management
Evita los `z-index: 999999`. Sigue esta escala lógica:
- `z-index: 10`: Elementos flotantes relativos.
- `z-index: 50`: Headers y Navbars.
- `z-index: 100`: Filtros de oscurecimiento (`.filter-blur`).
- `z-index: 200`: Modales y Popups.

## 9. Animaciones Complejas (Keyframes)
Si quieres que algo aparezca suavemente (Fade In) al cargar la página:

```css
.fade-in {
    animation: fadeInAnimation 0.5s ease-out forwards;
}

@keyframes fadeInAnimation {
    0% {
        opacity: 0;
        transform: translateY(20px);
    }
    100% {
        opacity: 1;
        transform: translateY(0);
    }
}
```
