# 👑 Guía Maestra (Master Skill) - Arquitectura Project_saavedra

**ESTA ES LA INSTRUCCIÓN PRINCIPAL DEL SISTEMA.**
Cualquier agente, LLM o desarrollador que trabaje en `Project_saavedra` **DEBE** leer y aplicar las directrices enlazadas a continuación antes de escribir una sola línea de código. Este proyecto utiliza un stack basado en **Laravel (PHP), Blade, Vanilla JS y CSS Puro**. 

No asumas patrones comunes si no están definidos aquí. Sigue el flujo de trabajo orquestado para evitar romper la integridad del sistema.

## 🗂️ Índice de Habilidades (Skills) Obligatorias

1. **[⚙️ Controladores (Controllers)](controllers_skill.md):** 
   *Qué aprenderás:* Eager Loading avanzado, inyección de dependencias, transacciones DB, try-catch, y formateo de respuestas JSON vs Blade.
2. **[🧠 Lógica y Modelos (Logic)](logic_skill.md):** 
   *Qué aprenderás:* Consultas optimizadas con Eloquent, manejo de `auth()->user()->perfil` (Seguridad), fechas con Carbon, y el uso correcto del Estado de Sesión Temporal.
3. **[👁️ Vistas (Views - Blade)](views_skill.md):** 
   *Qué aprenderás:* Uso de layouts (`appMenu`), paso de variables al frontend (`@json`, `window.routes`), inyección de Vite y componentes parciales.
4. **[🎨 Estilos y UI (Styles)](styles_skill.md):** 
   *Qué aprenderás:* CSS Vanilla Premium, variables de colores institucionales (`#0a8504`, `#030041`), Glassmorphism, sombreados de profundidad y reglas de responsive design.
5. **[⚡ JavaScript (JS Puro)](javascript_skill.md):** 
   *Qué aprenderás:* Peticiones Fetch API con `X-CSRF-TOKEN`, manejo del DOM, FormData (Subida de archivos) y alertas con SweetAlert2.
6. **[📄 Generación de PDFs (PDF)](pdf_skill.md):** 
   *Qué aprenderás:* Limitaciones estrictas de DOMPDF, maquetación con tablas HTML obsoletas (obligatorio), paginación y rutas absolutas para imágenes.

---

## 🚦 Flujo de Trabajo Obligatorio (Paso a Paso)

Cuando recibas un nuevo requerimiento, debes ejecutar mentalmente este flujo:

1. **Análisis de la Base de Datos:** ¿Necesita migraciones? Ve a la guía de *Lógica*. ¿Qué modelo interviene?
2. **Definición de Seguridad:** ¿Qué *perfiles* (`1`, `4`, `6`, `8`) pueden ejecutar esto? Defínelo en el Controlador.
3. **Construcción del Endpoint:** Diseña el método en el *Controlador* aplicando `try/catch` y `DB::transaction` si se van a modificar múltiples tablas.
4. **Armado de la Vista:** Construye la estructura en Blade extendiendo de `layouts.appMenu`. Inyecta los `window.routes` necesarios.
5. **Estilizado Premium:** Ve a la carpeta CSS y aplica *Glassmorphism*, transiciones y sombras. Asegúrate de que funciona en móviles.
6. **Interactividad:** Haz que el botón principal no recargue la página si es un proceso intermedio. Ve a la guía de *JavaScript* y usa `fetch()` con un loading state.
7. **Documentación:** Deja comentarios en PHP y JS explicando **por qué** se tomó la decisión, no *qué* hace el código.

**Falla en seguir estas guías resultará en código ineficiente (N+1), inseguro (sin CSRF) o visualmente deficiente.**
