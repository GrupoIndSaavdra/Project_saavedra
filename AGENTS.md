# Directivas Globales y Protocolo Maestro — Project Saavedra

> Este documento es cargado automáticamente por Antigravity / Gemini en cada conversación. Define el protocolo obligatorio de trabajo, el mapa de arquitectura del sistema, la matriz de perfiles y el catálogo completo de directivas técnicas para garantizar ejecuciones limpias, seguras y sin fallos.

---

## 1. PROTOCOLO OBLIGATORIO DE CONSULTA DE SKILLS

**ANTES de escribir, modificar o eliminar cualquier línea de código**, el agente debe:
1. **Identificar la skill aplicable** en el catálogo de abajo.
2. **Consultar el archivo `.md` respectivo** en `skills/` usando `view_file` para extraer directivas, helpers y convenciones específicas.
3. **Cero improvisación:** Si la skill define un patrón (transacciones, nombrado de clases, helpers, estructura Blade, etc.), aplícalo al 100%.
4. **Verificación post-edición obligatoria:**
   - Archivos PHP modificados: Verificar sintaxis con `php -l ruta/al/archivo.php`.
   - Archivos JavaScript modificados: Verificar sintaxis con `node --check ruta/al/archivo.js`.
5. **Documentar lo nuevo:** Si deduces una solución no documentada, regístrala en la skill correspondiente.

---

## 2. CATÁLOGO COMPLETO DE SKILLS (`skills/`)

| Tipo de Tarea / Tema | Skill Obligatoria | Descripción Clave |
|---|---|---|
| Protocolo maestro, stack, arquitectura general | `skills/general_skill.md` | Guía madre del proyecto y directrices de desarrollo. |
| Controladores, endpoints, AJAX, transacciones | `skills/controllers_skill.md` | Estructura de controllers, `DB::beginTransaction()`, respuestas JSON. |
| Modelos Eloquent, relaciones, consultas BD, scopes | `skills/logic_skill.md` | Métodos Eloquent, relaciones `belongsTo`/`hasMany`, scopes reutilizables. |
| Vistas Blade, layouts, componentes, modales, `@vite` | `skills/views_skill.md` | Directivas Blade, layouts (`appMenu`), partials, inyección `@json`. |
| CSS, colores GIS, glassmorphism, responsive | `skills/styles_skill.md` | Variables GIS (`--gis-blue`, etc.), botones `.btns`, drawers, diseño visual. |
| JavaScript, fetch, SweetAlert2, DOM, `window.routes` | `skills/javascript_skill.md` | Vanilla JS moderno, async/await, alertas GIS, event delegation. |
| Generación de PDFs con DomPDF | `skills/pdf_skill.md` | Plantillas PDF en `resources/views/pdf/`, orientación y estilos DomPDF. |
| Migraciones, esquema de BD, soft deletes | `skills/migrations_skill.md` | Tablas, foreign keys, índices, campos nullable y timestamps. |
| Validaciones, Form Requests, sanitización | `skills/validation_skill.md` | Reglas de validación, sanitización de inputs, FormRequests. |
| Códigos QR, lectores físicos, parseo | `skills/qr_codes_skill.md` | Generación de QRs por lote/individual, escaneo y decodificación. |
| Logs de errores, depuración, auditoría, SystemLog | `skills/error_logging_skill.md` | `Log::error()` contextual y registro en modelo `SystemLog`. |
| Seguridad, perfiles (1,2,3,4,5,6,8), CSRF, middlewares | `skills/security_skill.md` | Roles, matriz de acceso, protección contra ataques CSRF/XSS. |
| Comandos Artisan, NPM, Git, PowerShell, Python | `skills/commands_skill.md` | Comandos permitidos para servidor, Vite, migraciones y utilidades. |
| Actualización dinámica de UI sin recargar, `window.*` | `skills/dynamic_ui_skill.md` | Modificación del DOM en tiempo real, renderizado dinámico sin F5. |
| Emails, Mailables, plantillas de correo | `skills/emails_skill.md` | Notificaciones por correo, plantillas Blade en `resources/views/emails/`. |
| Rutas, `routes/web.php`, naming conventions | `skills/routes_skill.md` | Nombres de rutas `route('...')`, agrupación de controladores. |
| Almacenamiento en disco, Storage, archivos OT, `_R1` | `skills/file_storage_skill.md` | Manejo de PDFs/imágenes de OTs en `storage/app/public/` o `public/`. |
| Optimización de rendimiento, memoria RAM, `lazyById()` | `skills/performance_opt_skill.md` | Prevención de fugas de memoria en tablas pesadas (`chunk`, `lazyById`). |
| Ciclo de reprocesos (`_R1`), liberaciones y reinicios | `skills/rework_cycle_skill.md` | Reglas de sufijo `_R1`, reinicio de piezas y flujo de no-conformidad. |
| Catálogo y normalización de clases (`global_classes.php`) | `skills/classes_skill.md` | Normalización de nombres de modelos y clases de fundición. |
| Ausencia de tests automatizados | `skills/no_testing_skill.md` | Protocolo para proyectos sin suite de pruebas unitarias automatizadas. |
| Estándar de creación de skills | `skills/agent_skills_standard.md` | Formato estándar para crear o extender skills en la carpeta `skills/`. |

---

## 3. MAPA EXPRESS DE ARQUITECTURA (UBICACIÓN RÁPIDA)

Usa este mapa para ubicar archivos inmediatamente **sin realizar búsquedas recursivas**:

| Módulo del Sistema | Controlador Principal (`app/Http/Controllers/`) | Vistas Blade (`resources/views/`) | JS (`resources/js/`) | CSS (`resources/css/`) |
|---|---|---|---|---|
| **Almacén Fundición** | `AlmacenFundicionController.php`, `AlmacenWOController.php` | `almacen/` | `almacen_views/` | `almacen_views/` |
| **Calidad Fundición / Maquinados** | `CalidadFundicionController.php`, `CalidadMaquinadosController.php` | `calidad/` | `calidad_views/` | `calidad_views/` |
| **Órdenes de Trabajo (WO)** | `WOController.php` | `wo_views/` | `wo_views/` | `wo_views/` |
| **PTA (Procedimiento Trabajo Autorizado)** | `PtaResultsController.php`, `EnvioPtaController.php`, `SoldaduraPTAController.php` | `pta_views/` | `pta_views/` | `pta_views/` |
| **Procesos de Maquinado** | `ProcessesController.php`, `ProcessProductionController.php`, `ProgresoProcesosController.php` | `processes_views/` | `processes_views/` | `processes_views/` |
| **Soldadura & Tracking** | `SoldaduraController.php`, `TrackingSoldaduraController.php`, `LiberarSoldaduraController.php` | `trackingSoldadura_views/` | `trackingSoldadura_views/` | `trackingSoldadura_views/` |
| **Piezas (Generales, Ocupadas, Liberadas)** | `PzasGeneralesController.php`, `PiecesOccupiedController.php`, `PzasLiberadasController.php` | `pieces_views/` | `pieces_views/` | `pieces_views/` |
| **Máquinas y Molduras** | `MachinesController.php`, `MoldingController.php` | `machines_views/`, `moldings_views/` | `machines_views/` | `machines_views/` |
| **Herramientas Tecámac** | `HerramientasTecamacController.php` | `herramientas/` | `herramientas/` | `herramientas/` |
| **Reportes & Productividad** | `ReporteProduccionController.php`, `ProductivityController.php`, `TiemposProduccionController.php` | `reportes/`, `reports/` | — | — |
| **Códigos QR** | `GenerarQRIndividualController.php`, `GenerarQRLoteController.php`, `LiberarQRPlantaController.php`, `RegenerarQRController.php` | Vistas del módulo respectivo | — | — |
| **PDFs (Dibujos, Ayudas Visuales)** | `DibujosPdfController.php`, `DibujosFundicionPdfController.php`, `AyudasVisualesPdfController.php` | `pdf/` | — | — |
| **Usuarios y Auth** | `UserController.php`, `LoginController.php`, `LogoutController.php` | `users_views/`, `auth/` | — | — |
| **Logs del Sistema** | `SystemLogController.php` | `almacen/` o admin views | — | — |
| **Layout Base y Menú Lateral** | — | `layouts/appMenu.blade.php` | `layouts/appMenu.js` | `layouts/appMenu.css` |
| **Rutas Web** | `routes/web.php` | — | — | — |

---

## 4. MATRIZ DE PERFILES Y CONTROL DE ACCESO

El sistema utiliza perfiles numéricos en la tabla de usuarios (`perfil`). Cada uno tiene alcances bien definidos:

| Perfil | Nombre / Rol | Alcance y Permisos |
|:---:|---|---|
| **`1`** | **Administrador** | Acceso total a administración, producción, usuarios, configuración y almacén. |
| **`2`** | **Operador Maquinado** | Acceso a registro de proceso de producción y herramientas. Sin menú extendido. |
| **`3`** | **Master** | Supervisión global. Menú multi-sección completo (Administración, Calidad, Almacén). |
| **`4`** | **Calidad Fundición / Maquinados** | Liberación de piezas, SCARs, inspección visual, dimensional y reportes de calidad. |
| **`5`** | **Almacén** | Modificación de O.T., control de soldadura (QRs, recepción, entrega), inventarios. |
| **`6`** | **Supervisor de Producción** | Administración de producción, prioridades, reporte de piezas y seguimiento de O.T. |
| **`8`** | **Calidad Soldadura / PTA** | Análisis de soldadura PTA, segunda pasada y liberación especializada. |
| **`pta_temp`** | **Sesión Temporal PTA** | Vista acotada exclusivamente al flujo del procedimiento PTA en curso. |

---

## 5. DIRECTIVAS TÉCNICAS OBLIGATORIAS (REGLAS DE ORO)

### A. Stack Tecnológico Fijo
1. **Frontend & Reactividad:** **Blade + Laravel Livewire v3 (con Alpine.js nativo) + Tailwind CSS v3 + Vanilla JS/CSS**.
   - **Framework CSS:** **Tailwind CSS v3** integrado (`resources/css/tailwind.css`), configurado con `preflight: false` para proteger y coexistir con los estilos Vanilla corporativos existentes.
   - **Reactividad Backend-Frontend:** **Laravel Livewire v3** (`app/Livewire/`, vistas en `resources/views/livewire/`, comandos `php artisan make:livewire`). Incluye **Alpine.js** de forma nativa para micro-interacciones en cliente.
   - **PROHIBIDO:** Añadir Bootstrap, React, Vue ni librerías externas que no formen parte del ecosistema configurado.
   - Bundler: **Vite**. Assets deben cargarse con `@vite(['resources/css/...', 'resources/js/...'])` dentro de `@section('head')`.
   - Directivas Livewire: `@livewireStyles` en `<head>` y `@livewireScripts` al pie integrados en `layouts/appMenu.blade.php`.
   - Alertas: SweetAlert2 con diseño corporativo GIS (`Swal.fire` o `almacenToast`).

### B. Backend y Base de Datos (Laravel / Eloquent)
1. **Transacciones:** Toda acción que involucre más de un registro o tabla DEBE estar envuelta en:
   ```php
   DB::beginTransaction();
   try {
       // Operaciones de BD...
       DB::commit();
   } catch (\Throwable $e) {
       DB::rollBack();
       Log::error('Fallo en operación X', [
           'user_id' => auth()->id(),
           'error'   => $e->getMessage(),
           'trace'   => $e->getTraceAsString()
       ]);
       return response()->json(['success' => false, 'message' => 'Error al procesar la solicitud'], 500);
   }
   ```
2. **Consultas Eloquent Seguras:**
   - Usar el método `where()` con 4 argumentos para evitar ambigüedades:
     `->where('columna', '=', $valor, 'and')`
   - Para consultas grandes sobre tablas con miles de registros (`piezas`, `piezas_historial`, `system_logs`), **utilizar siempre `chunk(200, ...)` o `lazyById()`** para no exceder la memoria RAM de PHP.
3. **Catálogo de Clases:**
   - Siempre normalizar clases mediante `config('global_classes.php')` o `App\Services\FundicionPaths::normalizeClass($clase)`.

### C. Vistas Blade y JavaScript
1. **Seguridad CSRF:**
   - Todo formulario tradicional `<form method="POST">` DEBE incluir `@csrf`.
   - Toda petición asíncrona `fetch()` POST/PUT/DELETE DEBE enviar el header:
     `'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')`.
2. **Rutas a JavaScript:**
   - Nunca quemar URLs hardcodeadas (`/almacen/guardar`) en JS.
   - Inyectar las rutas en `window.routes` desde Blade usando `@json(route('nombre.de.ruta'))`.
3. **Catálogo Centralizado de Iconos:**
   - Todos los iconos del sistema están mapeados en `config/icons.php`.
   - En Blade: usar `{{ icon('alias') }}` o `<x-icon name="alias" />`.
   - En JavaScript: usar `window.getIcon('alias')`.
   - **Nunca escribir rutas manuales dispersas** tipo `images/iconos/...`.
4. **Estados de Carga (UX Premium):**
   - Toda llamada async o navegación debe desplegar feedback visual (`loading.gif` o `setVisualLoading(true)`).
5. **DOM Ready:**
   - Todo código JS debe inicializarse dentro de `document.addEventListener('DOMContentLoaded', ...)`.
6. **Sistema Oficial de Toasts y Fricción Positiva en Modales:**
   - **Toasts Oficiales:** Toda notificación debe usar `window.gisToast(mensaje, tipo)`. Prohibido crear divs de toast manuales en el DOM (`.dibujos-toast`, `.alm-toast`, `.ht-toast`, `.custom-alert-saavedra`).
   - **Fricción Positiva en Modales:** Usar `await window.gisConfirm({...})` o `window.gisAlert({...})`. Prohibidos títulos genéricos ("Alerta del Sistema"), prohibidos botones con "Sí" (usar verbos de acción: Eliminar, Desocupar, Desactivar, Reactivar, Entendido), prohibidos íconos distractores (`warning`, `question`), y botones alineados siempre a la derecha (`.swal2-actions { justify-content: flex-end }`).

### D. Encodaje y Nomenclatura Oficial
1. **Codificación:** Archivos guardados obligatoriamente en **UTF-8 sin BOM**. Prohibido introducir o dejar caracteres mojibake (`Ã¡`, `â€“`, etc.).
2. **Nombres Oficiales Estrictos:**
   - "Herramientas Planta Tecámac" (con acento en *Tecámac* y especificando *Planta*).
   - "Liberación de piezas" (con acento en *Liberación*).
   - "Órdenes de Trabajo" (con acento en *Órdenes*).

---

## 6. FILOSOFÍA ANTI-SOBREINGENIERÍA (YAGNI)

1. **Reutilizar antes de crear:** Verifica scopes existentes, helpers en `app/helpers.php` y controladores previos antes de añadir clases nuevas.
2. **No wrappers innecesarios:** Si un método en el controlador o un scope en el modelo resuelve el requerimiento de forma legible, no crees Service Providers, Repositorios o clases intermedias redundantes.
3. **Cambios quirúrgicos:** Modifica únicamente las líneas pertinentes respetando el estilo y formato del archivo original.
