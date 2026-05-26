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
```

## 2. Paleta Oficial Estricta

Los colores no son sugerencias. Son la identidad del corporativo.

- **Verde Principal (Éxito/Acción):** `#0a8504`
- **Rojo Peligro (Cerrar/Scrap):** `#b30404`
- **Azul Marino Saavedra (Paneles/Headers):** `#030041`
- **Fondo General/Gris Neutro:** `#f4f7f6`
- **Textos Oscuros (Lectura):** `#333333` o `#1a1a1a`

## 3. Arquitectura del "Glassmorphism" (Paneles Premium)

Cuando crees menús superpuestos (como el menú hamburguesa o paneles de detalles), usa esta arquitectura de cristal esmerilado que deja ver el fondo desenfocado.

```css
.glass-container {
    /* 1. Fondo translúcido (25% opacidad) */
    background: rgba(3, 57, 102, 0.25); 
    
    /* 2. El filtro que hace la magia */
    backdrop-filter: blur(12px);
    -webkit-backdrop-filter: blur(12px); 
    
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
    background-color: #0a8504;
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
                box-shadow 0.2s cubic-bezier(0.25, 0.8, 0.25, 1);
}

.btns:hover {
    transform: translateY(-2px) scale(1.03); /* Elevar y zoom */
    box-shadow: 0 8px 15px rgba(10, 133, 4, 0.5); /* Sombra más grande */
}

.btns:active {
    transform: translateY(1px) scale(0.98); /* Sensación de presionar */
}
```

## 5. Z-Index Management
Evita los `z-index: 999999`. Sigue esta escala lógica:
- `z-index: 10`: Elementos flotantes relativos.
- `z-index: 50`: Headers y Navbars.
- `z-index: 100`: Filtros de oscurecimiento (`.filter-blur`).
- `z-index: 200`: Modales y Popups.

## 6. Animaciones Complejas (Keyframes)
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
