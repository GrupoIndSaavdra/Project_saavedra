# Guía Maestra (Master Skill) — Arquitectura Project_saavedra

> ** Directorio de Referencia:** `(Raíz del Proyecto y Configuración global)`
> *Esta es la skill de arranque. Léela primero antes que cualquier otra.*

---

## PROTOCOLO OBLIGATORIO DE INICIO — LEE ESTO PRIMERO

**REGLA MÁXIMA DEL SISTEMA — ANTES DE ESCRIBIR UNA SOLA LÍNEA DE CÓDIGO:**

Cuando recibas cualquier petición que involucre el proyecto (errores, nuevas funciones, modificaciones, búsquedas, refactorizaciones), debes ejecutar el siguiente protocolo:

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

### Paso 2 — Leer el archivo `.md` completo con `view_file`
Usa la herramienta `view_file` para leer el contenido de la skill identificada **antes** de proponer o escribir cualquier solución.

### Paso 3 — Aplicar el patrón definido en la skill
Si el patrón existe en la skill, aplícalo directamente. No improvises ni reescribas desde cero lo que ya está documentado.

### Paso 4 — Documentar lo nuevo
Si la tarea te obligó a deducir algo que no estaba en ninguna skill, agrégalo a la skill más cercana (o crea una nueva) para que futuras sesiones sean más rápidas.

> **FALLA EN SEGUIR ESTE PROTOCOLO** resultará en código inconsistente, inseguro o incompatible con la arquitectura establecida.

---

## Stack Tecnológico (No Cambiar)

- **Backend:** Laravel (PHP 8.x) con Eloquent ORM
- **Frontend:** Blade + Vanilla JS + Vanilla CSS (sin Tailwind, sin Bootstrap, sin Alpine)
- **Assets:** Vite para compilar CSS y JS
- **PDF:** DomPDF (Barryvdh)
- **Alertas UI:** SweetAlert2
- **Base de datos:** MySQL
- **Entorno:** Windows (PowerShell) en desarrollo, Linux en producción

---

## Índice Completo de Skills

1. **[Controladores](controllers_skill.md)** — Transacciones, eager loading, JSON vs Blade, Form Requests, thin controllers
2. **[Lógica y Modelos](logic_skill.md)** — Eloquent optimizado, scopes, perfiles, Carbon, orWhere agrupado
3. **[Vistas Blade](views_skill.md)** — Layouts, CSRF, Vite, partials, stacks, escapado XSS
4. **[Estilos CSS](styles_skill.md)** — Paleta GIS, glassmorphism, botones `.btns`, responsivo, dark mode
5. **[JavaScript](javascript_skill.md)** — Async/await, FormData, delegación de eventos, window.routes
6. **[PDFs DomPDF](pdf_skill.md)** — Tablas HTML, imágenes locales, fuentes TTF, saltos de página
7. **[Migraciones](migrations_skill.md)** — Tablas nuevas vs producción, índices, soft deletes, naming
8. **[Validación](validation_skill.md)** — Inline vs Form Request, sanitización, reglas reales del proyecto
9. **[QR Codes](qr_codes_skill.md)** — Payload JSON, parseo de lectores físicos, interfaz de escaneo
10. **[Error Logging](error_logging_skill.md)** — Log estructurado, SystemLog, niveles de severidad
11. **[Seguridad](security_skill.md)** — SQL Injection, XSS, CSRF, perfiles de acceso, middlewares
12. **[Comandos](commands_skill.md)** — Python, PowerShell, Artisan, NPM, Git, patrones de búsqueda
13. **[Dynamic UI](dynamic_ui_skill.md)** — State local con `window.*`, render sin recargar, pattern CRUD
14. **[Emails](emails_skill.md)** — Mailables Laravel, plantillas Blade, configuración SMTP
15. **[Rutas](routes_skill.md)** — Convenciones, grupos de rutas, naming, middlewares en rutas

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
 Controllers/ ← 65+ controladores (ver controllers_skill)
 Requests/ ← Form Requests (ver validation_skill)
 Middleware/ ← auth, CheckPtaAccess, guest (ver security_skill)
 Models/ ← 132+ modelos (ver logic_skill)
 Mail/ ← Mailables de correo (ver emails_skill)
 database/
 migrations/ ← 31+ grupos de migraciones (ver migrations_skill)
 resources/
 css/ ← CSS por módulo, mirrors de views/ (ver styles_skill)
 js/ ← JS por módulo, mirrors de views/ (ver javascript_skill)
 views/ ← Blade por módulo (ver views_skill)
    warehouse/ ← Almacén Fundición (Antes almacen/)
    quality/ ← Calidad Fundición (Antes calidad/)
    tools/ ← Herramientas Tecamac (Antes herramientas/)
    reports/ ← Reportes (Antes reportes/)
    welding_tracking_views/ ← Tracking Soldadura (Antes trackingSoldadura_views/)
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

### Regla Estricta de Nomenclatura (Resources)
A partir de la migración a la nueva arquitectura, **absolutamente todos** los nuevos archivos y carpetas dentro de `resources/views`, `resources/css` y `resources/js` deben usar **`snake_case` en INGLÉS** (ej. `warehouse_casting.blade.php`, `generate_individual_qr.css`). Queda prohibido el uso de Spanglish (ej. `trackingSoldadura_views`) o camelCase (ej. `viewUsers.blade.php`).

---

## Perfiles de Usuario (Referencia Rápida)

| Perfil | Nombre | Acceso Principal |
|--------|--------|-----------------|
| `1` | Administrador | Acceso total, creación de OTs, depuración |
| `2` | Gerente/Supervisor | Ver todo, aprobar, auditoría |
| `4` | Calidad | Revisión y liberación de OTs, SCARs |
| `5` | Almacén | Recepción, pre-órdenes, reprocesos |
| `6` | Operador Maquinado | Maquinado de piezas, registro de medidas |
| `8` | Calidad Soldadura | Liberación de botes y lotes de soldadura |
