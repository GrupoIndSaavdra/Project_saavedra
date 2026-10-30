---
name: general_skill
description: Guía maestra y protocolo de inicio para arquitectura, stack tecnológico y selección de habilidades en Project_saavedra.
version: 1.0.0
---

# Guía Maestra (Master Skill) — Arquitectura Project_saavedra

> **Directorio de Referencia:** `(Raíz del Proyecto y Configuración global)`
> *Esta es la skill de arranque. Léela primero antes que cualquier otra.*

---

## PROTOCOLO OBLIGATORIO DE INICIO — LEE ESTO PRIMERO

**REGLA MÁXIMA DEL SISTEMA — ANTES DE ESCRIBIR UNA SOLA LÍNEA DE CÓDIGO:**

Cuando recibas cualquier petición que involucre el proyecto (errores, nuevas funciones, modificaciones, búsquedas, refactorizaciones), debes ejecutar el siguiente protocolo:

### Paso 0 — Sincronizar Aprendizajes Continuos (Autocorrección)
Antes de iniciar, comprueba si existe el archivo [aprendizajes_temp.md](file:///c:/Users/Jaxer020406/Documents/GitHub/Project_saavedra/skills/aprendizajes_temp.md).
- Si **existe**: Léelo en detalle para asimilar qué errores ocurrieron en logs o qué commits de corrección se aplicaron recientemente. Actualiza de inmediato las skills afectadas (ej. `logic_skill.md`, `javascript_skill.md`) con las nuevas lecciones aprendidas y **elimina** el archivo `aprendizajes_temp.md` para marcar que el conocimiento ya está consolidado.
- Si **no existe**: Continúa al Paso 1.

### Paso 1 — Identificar la Skill aplicable
Analiza mentalmente cuál de las skills del Índice de abajo cubre el contexto de la tarea:

| Si la tarea involucra... | Lee primero esta skill |
|---|---|
| Controladores, endpoints, AJAX, FormRequests | `controllers_skill.md` |
| Modelos, Eloquent, relaciones, consultas BD | `logic_skill.md` |
| Vistas Blade, layouts, componentes, modales | `views_skill.md` |
| CSS, colores, glassmorphism, animaciones | `styles_skill.md` |
| JavaScript, fetch, SweetAlert, DOM | `javascript_skill.md` |
| Generación de PDFs con DomPDF | `pdf_skill.md` |
| Migraciones, esquema de base de datos | `migrations_skill.md` |
| Validaciones, Form Requests, sanitización | `validation_skill.md` |
| Códigos QR, lectores físicos, parseo | `qr_codes_skill.md` |
| Logs de errores, depuración, auditoría | `error_logging_skill.md` |
| Seguridad, perfiles, CSRF, middlewares | `security_skill.md` |
| Comandos Artisan, NPM, Git, Python, PowerShell | `commands_skill.md` |
| Actualización dinámica de UI sin recargar | `dynamic_ui_skill.md` |
| Emails, Mailables, plantillas de correo | `emails_skill.md` |
| Rutas, `routes/web.php`, API routes | `routes_skill.md` |
| Adquisición de skills externos, npx skills, SKILL.md | `agent_skills_standard.md` |
| Gestión de archivos en disco, carpetas, Storage | `file_storage_skill.md` |
| Optimización extrema de rendimiento y RAM | `performance_opt_skill.md` |
| Ciclo de reprocesos (_R1), liberaciones y reinicios | `rework_cycle_skill.md` |
| Catálogo global de clases, normalización y reglas | `classes_skill.md` |
| Límites de pruebas del agente, verificaciones backend (`php -l`) | `no_testing_skill.md` |
| Navegación de código, AST, grafo de dependencias, ahorro de tokens | `code_intelligence_skill.md` |
| Scripts .bat, setup.bat, tools\agent.bat y actualización diaria | `bat_cli_skill.md` |

### Paso 2 — Leer el archivo `.md` completo con `view_file`
Usa la herramienta `view_file` para leer el contenido de la skill identificada **antes** de proponer o escribir cualquier solución.

### Paso 3 — Aplicación Estricta del Patrón (PROHIBIDO IMPROVISAR)
- **Cero improvisación:** Si la skill define una estructura (ej. `almacenToast` en vez de `Swal.fire` en Almacén, `where()` con 4 argumentos en Eloquent, `@vite` en `@section('head')`, `async/await` con `try/catch` y `setVisualLoading`, `DB::transaction()` en escrituras multi-tabla), debes aplicar ese patrón **al 100% sin excepciones ni atajos**.
- **Autoverificación pre-entrega:** Antes de aplicar o presentar cambios en código, audita tu solución contra las directivas obligatorias de la skill aplicable. Si tu código viola alguna regla documentada, corrígelo antes de entregarlo.

### Paso 4 — Documentar lo nuevo y cerrar sesión
- Si la tarea te obligó a deducir algo que no estaba en ninguna skill, agrégalo a la skill más cercana o crea una nueva con `agent_skills_standard.md` para que futuras sesiones sean más rápidas.
- **Antes de dar por finalizada la sesión:** Ejecuta el comando `php artisan app:analizar-aprendizajes` para escanear tus propios commits de la sesión y los logs recientes. Así, si cometiste algún error que corregiste más tarde, el buffer quedará listo para la siguiente IA.

> ⚠️ **DIRECTIVA MÁXIMA DE EJECUCIÓN:** Cualquier propuesta o cambio de código que ignore o contradiga las directivas marcadas en la skill correspondiente es considerado una **falla grave de arquitectura**. El agente debe respetar y ejecutar las reglas de cada skill de forma literal.

---

## Directivas Anti-Sobreingeniería (Ponytail & YAGNI)

Antes de proponer o implementar cualquier solución, evalúa mentalmente esta escalera de decisión:

1. **YAGNI (You Ain't Gonna Need It):** Si el usuario no lo pidió explícitamente o no agrega valor inmediato, NO lo construyas.
2. **Reutilizar existente:** Usa `grep_search` en `app/Models`, `app/Http/Controllers`, `resources/js`, etc., para reutilizar scopes, traits y helpers existentes.
3. **Plataforma web nativa:** Usa elementos HTML5/JS nativos antes de inventar componentes JS o wrappers complejos.
4. **Respetar el stack nativo:** El stack oficial es **Blade + Laravel Livewire v3 (con Alpine.js) + Tailwind CSS v3** (`preflight: false`) + **Vanilla JS/CSS**. No agregues frameworks externos como React, Vue ni Bootstrap.
5. **Cero clases vacías o wrappers de una línea:** Si una función del controlador, componente Livewire o scope del modelo lo resuelve, no crees una Service Class ni Repository innecesario.

---

## Stack Tecnológico (Oficial)

- **Backend:** Laravel 10 (PHP 8.2+) con Eloquent ORM
- **Reactividad & UI:** Laravel Livewire v3 + Alpine.js (para interactividad dinámica server-driven y micro-interacciones sin escribir JS boilerplate)
- **Frontend & Estilos:** Blade + Vanilla JS + Vanilla CSS + Tailwind CSS v3 (con `preflight: false`)
- **Assets:** Vite para compilar CSS y JS
- **PDF:** DomPDF (Barryvdh)
- **Alertas UI:** SweetAlert2
- **Base de datos:** MySQL
- **Entorno:** Windows (PowerShell) en desarrollo, Linux en producción
- **Encoding:** Todos los archivos deben ser UTF-8 sin BOM (ver `fix_encoding.py` si hay problemas de mojibake)
- **Instalación Todo en Uno:** `setup.bat` o `npm run setup` / `npm run install:all` (instala Composer + Livewire v3 + NPM + Tailwind CSS v3 y compila assets en un solo paso)

---

## Índice Completo de Skills

1. **[Controladores](controllers_skill.md)** — Transacciones, eager loading, JSON vs Blade, Form Requests, thin controllers
2. **[Lógica y Modelos](logic_skill.md)** — Eloquent optimizado, scopes, perfiles, Carbon, orWhere agrupado, anti-patrón de memoria
3. **[Vistas Blade](views_skill.md)** — Layouts, CSRF, Vite, partials, stacks, escapado XSS
4. **[Estilos CSS](styles_skill.md)** — Paleta GIS, glassmorphism, botones `.btns`, responsivo, dark mode
5. **[JavaScript](javascript_skill.md)** — Async/await, FormData, delegación de eventos, window.routes
6. **[PDFs DomPDF](pdf_skill.md)** — Tablas HTML, imágenes locales, fuentes TTF, saltos de página
7. **[Migraciones](migrations_skill.md)** — Tablas nuevas vs producción, índices, soft deletes, naming
8. **[Validación](validation_skill.md)** — Inline vs Form Request, sanitización, reglas reales del proyecto, after hooks
9. **[QR Codes](qr_codes_skill.md)** — Payload JSON, parseo de lectores físicos, interfaz de escaneo
10. **[Error Logging](error_logging_skill.md)** — Log estructurado, SystemLog, niveles de severidad, anti-patrón de memoria
11. **[Seguridad](security_skill.md)** — SQL Injection, XSS, CSRF, perfiles de acceso correctos (1,2,4,5,6,8), middlewares
12. **[Comandos](commands_skill.md)** — Python, PowerShell, Artisan, NPM, Git, patrones de búsqueda
13. **[Dynamic UI](dynamic_ui_skill.md)** — State local con `window.*`, render sin recargar, pattern CRUD
14. **[Emails](emails_skill.md)** — Mailables Laravel, plantillas Blade, configuración SMTP
15. **[Rutas](routes_skill.md)** — Convenciones, grupos de rutas, naming, middlewares en rutas
16. **[No Testing](no_testing_skill.md)** — Límites del agente: no browser_subagent, verificaciones de backend permitidas
17. **[Agent Skills Standard](agent_skills_standard.md)** — Integración con npx skills, creación y adquisición dinámica de habilidades SKILL.md
18. **[Gestión de Archivos en Disco](file_storage_skill.md)** — Storage, normalización cross-platform, sufijos _R1 y auditoría de archivos
19. **[Optimización de Rendimiento](performance_opt_skill.md)** — Prevención de memoria exhausta, DB::table(), lazyById() y desfragmentación
20. **[Ciclo de Reprocesos y Liberaciones](rework_cycle_skill.md)** — Reinicios destructivos vs reemplazos seguros, aislamiento de clases y SCARs
21. **[Catálogo y Normalización de Clases](classes_skill.md)** — config/global_classes.php, normalización de nombres, categorizaciones y matching singular/plural
22. **[Inteligencia de Código y Optimización de Tokens](code_intelligence_skill.md)** — Tree-sitter, Repomap, NetworkX DAG, AST-grep, Universal Ctags, Ripgrep, Clean Diff, Tiktoken, Compresión LLMLingua e Introspección Prisma
23. **[Guía de Scripts BAT y CLI del Agente](bat_cli_skill.md)** — Regla de los 2 archivos .bat únicos (setup.bat y tools\agent.bat), comandos de actualización rápida y paridad Linux

---

## Flujo de Trabajo Obligatorio (Paso a Paso)

Cuando recibas un nuevo requerimiento completo, ejecuta este flujo mental:

1. **Base de Datos primero:** ¿Necesita migraciones? → `migrations_skill.md`
2. **Seguridad:** ¿Qué perfiles tienen acceso? → `security_skill.md`
3. **Rutas:** ¿Cómo se llama la ruta y qué middleware lleva? → `routes_skill.md`
4. **Validación:** Define reglas de entrada. Forms complejos → Form Request dedicado → `validation_skill.md`
5. **Controlador:** Diseña el método con transacciones y try/catch → `controllers_skill.md`
6. **Lógica de Negocio:** Scopes, relaciones, optimizaciones Eloquent → `logic_skill.md`
7. **Vista Blade:** Extiende `layouts.appMenu`, usa `@vite`, inyecta `window.routes` → `views_skill.md`
8. **CSS:** Aplica glassmorphism, paleta GIS, Flexbox/Grid, responsive → `styles_skill.md`
9. **JavaScript:** Async/await aislado en `DOMContentLoaded`, estados de carga → `javascript_skill.md`
10. **Documentación:** Comenta el *por qué*, no solo el *qué*.

---

## Estructura de Directorios del Proyecto (Referencia Rápida)

```
Project_saavedra/
 app/
 Http/
  Controllers/ ← 72+ controladores (ver controllers_skill)
  Requests/    ← Form Requests (ver validation_skill)
  Middleware/  ← auth, CheckPtaAccess, guest (ver security_skill)
 Models/      ← 145+ modelos (ver logic_skill)
 Mail/ ← Mailables de correo (ver emails_skill)
 database/
 migrations/ ← 31+ grupos de migraciones (ver migrations_skill)
 resources/
 css/ ← CSS por módulo, mirrors de views/ (ver styles_skill)
 js/ ← JS por módulo, mirrors de views/ (ver javascript_skill)
 views/ ← Blade por módulo (ver views_skill)
 almacen/ ← Almacén Fundición
 calidad/ ← Calidad Fundición
 wo_views/ ← Órdenes de Trabajo
 pta_views/ ← PTA (Procedimiento de Trabajo Autorizado)
 processes_views/ ← Procesos de maquinado
 pdf/ ← Plantillas PDF (ver pdf_skill)
 emails/ ← Plantillas de correo (ver emails_skill)
 layouts/ ← appMenu.blade.php (layout base)
 routes/
 web.php ← Rutas web (ver routes_skill)
 api.php ← Rutas API
 public/
 css/ ← CSS compilado/legacy
 images/ ← Logos, íconos de PDF, íconos de reproceso
 storage/
 app/DOCUMENTACION_GIS/ ← Archivos de OTs en disco
 logs/laravel.log ← Logs técnicos (ver error_logging_skill)
 skills/ ← Este directorio de skills (¡SIEMPRE CONSULTAR!)
```

---

## Perfiles de Usuario (Referencia Rápida)

| Perfil | Nombre | Acceso Principal |
|--------|--------|-----------------|
| `1` | Administrador | Acceso total, creación de OTs, depuración, usuarios y acceso global |
| `2` | Operador Maquinado | Registro de proceso de producción y herramientas (sin menú extendido) |
| `3` | Master / Maestro | Supervisión global. Menú multi-sección (Administración, Calidad, Almacén) |
| `4` | Calidad Fundición / Maquinados | Liberación de piezas, SCARs, inspección visual, dimensional y reportes |
| `5` | Almacén | Modificación de O.T., control de soldadura (QRs, recepción, entrega), inventarios |
| `6` | Supervisor de Producción | Administración de producción, prioridades, reporte de piezas y seguimiento |
| `8` | Calidad Soldadura / PTA | Análisis de soldadura PTA, segunda pasada y liberación especializada |
| `pta_temp` | Sesión Temporal PTA | Vista acotada exclusivamente al flujo del procedimiento PTA en curso |
