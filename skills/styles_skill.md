# Guía de Estilos y Colores (Styles Skill) - Project_saavedra

Esta guía describe el diseño visual, uso de colores, y estructura de CSS del proyecto `Project_saavedra`. Se priorizan interfaces modernas, ricas visualmente y dinámicas.

## Filosofía de Diseño
- **Estética Premium:** La interfaz debe causar una impresión moderna, utilizando paletas cuidadas, animaciones suaves (micro-interacciones) y efectos visuales de profundidad y desenfoque (Glassmorphism).
- **CSS Puro (Vanilla CSS):** No se utiliza TailwindCSS por defecto. Todos los estilos se escriben de manera directa en archivos CSS dentro de `resources/css/`.

## Estructura de Archivos
- Los archivos de estilos se ubican en `resources/css/`.
- Dependiendo de la vista o sección, crea archivos modulares (ej. `home.css`, `viewUsers.css`) y asegúrate de importarlos utilizando la directiva `@vite` de Laravel en el archivo `.blade.php`.

## Paleta de Colores Oficial
Se deben utilizar estos colores base para mantener la coherencia corporativa e interactiva:
- **Verde Principal (Botones de acción primaria / Aceptar):** `#0a8504`
- **Rojo Peligro (Botones destructivos / Cerrar / Rechazar):** `#b30404`
- **Azul Oscuro / Marino (Paneles, Reportes laterales, Fondos activos):** `#030041` y variaciones translúcidas `rgba(3, 57, 102, x)`.
- **Texto Principal:** Blanco `#ffffff` sobre fondos oscuros o colores sólidos.

## Convenciones de Estilos (Clases Globales)
Al crear nuevos elementos, utiliza o básate en estos patrones existentes:
- **Botones (`.btns`):** Tienen padding (`10px 30px`), color de fondo base (ej. verde), borde redondeado (`border-radius: 10px`), sin bordes nativos, texto en negrita y un ligero sombreado `box-shadow`.
- **Efectos Hover:** Siempre incluye un estado interactivo suave (`transition: 0.2s;`). Para botones usa animaciones de zoom como `transform: scale(1.1);`.
- **Desenfoques (Glassmorphism):** Para sobreposiciones, menús modales o filtros, usa propiedades de desenfoque como:
  ```css
  background: rgba(3, 57, 102, 0.1); 
  backdrop-filter: blur(8px);
  -webkit-backdrop-filter: blur(8px);
  ```
- **Sombras:** Se hace uso extensivo de `box-shadow` para generar profundidad en paneles e imágenes.

## Responsive Design
- Utiliza `@media screen and (max-width: 600px)` (y otros breakpoints estándar) para garantizar que los contenedores absolutos, textos grandes y menús se ajusten perfectamente a pantallas pequeñas.
