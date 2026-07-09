# Guía Maestra (Master Skill) - Arquitectura Project_saavedra

> **Directorio de Referencia:** `(Raíz del Proyecto y Configuración global)`
> *Usa los archivos en este directorio como base o inspiración al crear/modificar funcionalidades relacionadas con esta skill.*


**ESTA ES LA INSTRUCCIÓN PRINCIPAL DEL SISTEMA.**

Cualquier agente, LLM o desarrollador que trabaje en `Project_saavedra` **DEBE** leer y aplicar las directrices enlazadas a continuación antes de escribir una sola línea de código. Este proyecto utiliza un stack basado en **Laravel (PHP), Blade, Vanilla JS y CSS Puro**. 

No asumas patrones comunes si no están definidos aquí. Sigue el flujo de trabajo orquestado para evitar romper la integridad del sistema.

**REGLA CRÍTICA PARA EL USO DE SKILLS:**
Para poner a "chambear" (trabajar) una skill específica, el agente **DEBE** utilizar su herramienta de lectura de archivos (`view_file` o equivalente) para leer el contenido completo del archivo `.md` correspondiente (ej: `skills/controllers_skill.md`) **ANTES** de comenzar a escribir o modificar código relacionado con ese tema. Alternativamente, el usuario puede invocar la skill directamente incluyéndola en su mensaje mediante la sintaxis `@[ruta_al_archivo]`. ¡No programes de memoria! Consulta la skill correspondiente siempre.

---

## Índice de Habilidades (Skills) Obligatorias

1. **[Controladores (Controllers)](controllers_skill.md):** 
   *Qué aprenderás:* Eager Loading avanzado, inyección de dependencias, transacciones DB, try-catch, validación de Form Requests, controladores delgados y formateo de respuestas JSON vs Blade.
2. **[Lógica y Modelos (Logic)](logic_skill.md):** 
   *Qué aprenderás:* Consultas optimizadas con Eloquent, scopes locales, relaciones explícitas, castings y mutadores de atributos, manejo de `auth()->user()->perfil` y el estado de sesión temporal.
3. **[Vistas (Views - Blade)](views_skill.md):** 
   *Qué aprenderás:* Uso de layouts (`appMenu`), visualización de errores, toasts/flash messages, inyección modular con stacks, escape seguro de datos e inyección de Vite.
4. **[Estilos y UI (Styles)](styles_skill.md):** 
   *Qué aprenderás:* CSS Vanilla Premium, variables de colores institucionales, Glassmorphism, animaciones, diseño responsivo (breakpoints) y layouts modernos con Flexbox/Grid.
5. **[JavaScript (JS Puro)](javascript_skill.md):** 
   *Qué aprenderás:* Aislamiento de scopes, eventos DOMContentLoaded, peticiones Fetch API, FormData, alertas de Swal, validación frontend y delegación de eventos.
6. **[Generación de PDFs (PDF)](pdf_skill.md):** 
   *Qué aprenderás:* Limitaciones de DOMPDF, maquetación estricta con tablas, fuentes personalizadas TTF localizadas, prevención de cortes de fila y configuración de imágenes remotas.
7. **[Migraciones (Migrations)](migrations_skill.md):**
   *Qué aprenderás:* Reglas para añadir columnas en desarrollo local vs. producción, estándares de nomenclatura de base de datos, Soft Deletes e índices de búsqueda.
8. **[Validación y Sanitización (Validation)](validation_skill.md):**
   *Qué aprenderás:* Reglas de validación avanzadas, Form Requests independientes, limpieza de caracteres especiales y sanitización contra ataques XSS.
9. **[Generación y Lectura de QRs (QR Codes)](qr_codes_skill.md):**
   *Qué aprenderás:* Formateo de payloads JSON en QRs, corrección del mapeo corrupto por emulación de teclado de lectores físicos de hardware y enfoque de escaneo frontend.
10. **[Registro de Errores y Debugging (Error Logging)](error_logging_skill.md):**
    *Qué aprenderás:* Logging estructurado con contexto asociativo, excepciones silenciosas de backend vs. excepciones de negocio para la UI, y la localización física de archivos log.
11. **[Seguridad y Protección (Security)](security_skill.md):**
    *Qué aprenderás:* Prevención de inyecciones SQL, tokens CSRF para forms y fetch, autorizaciones basadas en perfil y protección de manipulación de parámetros.
12. **[No Pruebas en Navegador (No Testing Rule)](no_testing_skill.md):**
    *Qué aprenderás:* El agente no realiza pruebas visuales en navegador web ni usa subagentes de DOM; el foco técnico es la validación sintáctica (`php -l` o tests unitarios locales).
13. **[Comandos de Entorno (Commands)](commands_skill.md):**
    *Qué aprenderás:* Los comandos Python, PowerShell, Artisan, NPM y Git más frecuentes del proyecto. Incluye patrones para búsqueda y edición de archivos grandes Blade/JS, y los caracteres que rompen en PowerShell que se deben evitar.

---

## Flujo de Trabajo Obligatorio (Paso a Paso)

Cuando recibas un nuevo requerimiento, debes ejecutar mentalmente este flujo:

1. **Análisis de la Base de Datos:** ¿Necesita migraciones? Ve a la guía de *Migraciones*. Diseña los índices y decide si usará *Soft Deletes*.
2. **Definición de Seguridad:** ¿Qué *perfiles* (`1`, `2`, `5`, `6`, `8`) están autorizados a interactuar con este recurso? Ve a la guía de *Seguridad*.
3. **Validación Estricta:** Define las reglas de entrada. Si es un formulario complejo, crea un Form Request dedicado. Ve a la guía de *Validación*.
4. **Construcción del Endpoint (Controlador):** Diseña el método. Si altera datos de múltiples tablas, usa transacciones de base de datos y try/catch. Si el servidor falla, registra un log estructurado (ver *Error Logging*).
5. **Armado de la Vista:** Construye la estructura en Blade extendiendo de `layouts.appMenu`. Usa `@stack` y `@push` para inyectar scripts locales de manera limpia (ver *Views*).
6. **Estilizado Premium:** Aplica *Glassmorphism*, layouts responsivos con Flexbox/Grid y micro-animaciones en CSS Vanilla (ver *Styles*).
7. **Interactividad y Validaciones JS:** Envuelve tu script en un scope aislado de `DOMContentLoaded`. Implementa deshabilitación de botones y spinners visuales mientras se procesan las peticiones AJAX (ver *JavaScript*).
8. **Documentación:** Deja comentarios en PHP y JS explicando **por qué** se tomó la decisión técnica o la regla de negocio, no solo *qué* hace la línea de código.

**Falla en seguir estas guías resultará en código ineficiente (N+1), inseguro (sin CSRF o vulnerable a inyección) o visualmente deficiente.**

---

## Estructura de Directorios del Proyecto (Referencia Rápida)
```
Project_saavedra/
├── app/
│   ├── Http/
│   │   ├── Controllers/   ← 65 controladores (ver controllers_skill)
│   │   └── Middleware/    ← auth, CheckPtaAccess, guest (ver security_skill)
│   └── Models/            ← 132 modelos (ver logic_skill)
├── database/
│   └── migrations/        ← 31+ grupos de migraciones (ver migrations_skill)
├── resources/
│   ├── css/               ← CSS por módulo, mirrors de views/ (ver styles_skill)
│   ├── js/                ← JS por módulo, mirrors de views/ (ver javascript_skill)
│   └── views/             ← Blade por módulo (ver views_skill)
│       ├── almacen/       ← almacen_fundicion.blade.php
│       ├── calidad/       ← calidad_fundicion.blade.php
│       ├── pdf/           ← pre_orden.blade.php (ver pdf_skill)
│       └── layouts/       ← appMenu.blade.php (layout base)
├── public/
│   ├── css/               ← CSS compilado (solo emails y algunos legacy)
│   └── images/            ← Logos, íconos de PDF, íconos de reproceso
├── storage/
│   ├── app/DOCUMENTACION_GIS/  ← Archivos de OTs en disco
│   └── logs/laravel.log        ← Logs de error del sistema (ver error_logging_skill)
└── skills/                ← Este directorio de skills
```
