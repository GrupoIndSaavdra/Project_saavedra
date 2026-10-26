# Directivas Globales y Mapa de Arquitectura — Project Saavedra

> Este archivo es cargado automáticamente por Antigravity en cada conversación. Define el protocolo obligatorio de trabajo, el mapa de arquitectura del sistema y el catálogo de habilidades especializadas.

---

## 1. PROTOCOLO OBLIGATORIO DE CONSULTA DE SKILLS

**ANTES de escribir, modificar o eliminar código**, el agente debe:
1. **Identificar la skill aplicable** en la tabla de abajo.
2. **Consultar el archivo `.md` respectivo** en `skills/` usando `view_file` para extraer directivas y convenciones específicas.
3. **Cero improvisación:** Si la skill define un patrón (transacciones, nombrado de clases, helpers, estructura Blade, etc.), aplícalo al 100%.
4. **Documentar lo nuevo:** Si deduces una solución no documentada, regístrala en la skill correspondiente.

### Catálogo de Skills (`skills/`)
| Tipo de Tarea / Tema | Skill Obligatoria |
|---|---|
| Protocolo maestro, stack, arquitectura general | `skills/general_skill.md` |
| Controladores, endpoints, AJAX, FormRequests, transacciones | `skills/controllers_skill.md` |
| Modelos Eloquent, relaciones, consultas BD, scopes | `skills/logic_skill.md` |
| Vistas Blade, layouts, componentes, modales, `@vite` | `skills/views_skill.md` |
| CSS, colores GIS, glassmorphism, botones `.btns`, responsive | `skills/styles_skill.md` |
| JavaScript, fetch, SweetAlert2, DOM, `window.routes` | `skills/javascript_skill.md` |
| Generación de PDFs con DomPDF | `skills/pdf_skill.md` |
| Migraciones, esquema de BD, soft deletes | `skills/migrations_skill.md` |
| Validaciones, Form Requests, sanitización | `skills/validation_skill.md` |
| Códigos QR, lectores físicos, parseo | `skills/qr_codes_skill.md` |
| Logs de errores, depuración, auditoría, SystemLog | `skills/error_logging_skill.md` |
| Seguridad, perfiles (1,2,4,5,6,8), CSRF, middlewares | `skills/security_skill.md` |
| Comandos Artisan, NPM, Git, PowerShell, Python | `skills/commands_skill.md` |
| Actualización dinámica de UI sin recargar, `window.*` | `skills/dynamic_ui_skill.md` |
| Emails, Mailables, plantillas de correo | `skills/emails_skill.md` |
| Rutas, `routes/web.php`, naming conventions | `skills/routes_skill.md` |
| Almacenamiento en disco, Storage, archivos OT, `_R1` | `skills/file_storage_skill.md` |
| Optimización de rendimiento, memoria RAM, `lazyById()` | `skills/performance_opt_skill.md` |
| Ciclo de reprocesos (`_R1`), liberaciones y reinicios | `skills/rework_cycle_skill.md` |
| Catálogo y normalización de clases (`config/global_classes.php`) | `skills/classes_skill.md` |
| Estándar de creación de skills | `skills/agent_skills_standard.md` |

---

## 2. MAPA EXPRESS DE ARQUITECTURA (UBICACIÓN RÁPIDA DE ARCHIVOS)

Usa este mapa para ubicar archivos inmediatamente **sin necesidad de búsquedas recursivas**:

| Módulo del Sistema | Controlador Principal (`app/Http/Controllers/`) | Vistas Blade (`resources/views/`) | JS (`resources/js/`) | CSS (`resources/css/`) |
|---|---|---|---|---|
| **Almacén Fundición** | `AlmacenFundicionController.php`, `AlmacenWOController.php` | `almacen/` | `almacen/` | `almacen/` |
| **Calidad Fundición / Maquinados** | `CalidadFundicionController.php`, `CalidadMaquinadosController.php` | `calidad/` | `calidad/` | `calidad/` |
| **Órdenes de Trabajo (WO)** | `WOController.php` | `wo_views/` | `wo_views/` | `wo_views/` |
| **PTA (Procedimiento Trabajo Autorizado)** | `PtaResultsController.php`, `EnvioPtaController.php`, `SoldaduraPTAController.php` | `pta_views/` | `pta_views/` | `pta_views/` |
| **Procesos de Maquinado** | `ProcessesController.php`, `ProcessProductionController.php`, `ProgresoProcesosController.php` | `processes_views/` | `processes_views/` | `processes_views/` |
| **Soldadura & Tracking** | `SoldaduraController.php`, `TrackingSoldaduraController.php`, `LiberarSoldaduraController.php` | `trackingSoldadura_views/` | `trackingSoldadura_views/` | `trackingSoldadura_views/` |
| **Piezas (Generales, Ocupadas, Liberadas)** | `PzasGeneralesController.php`, `PiecesOccupiedController.php`, `PzasLiberadasController.php` | `pieces_views/` | `pieces_views/` | `pieces_views/` |
| **Máquinas y Molduras** | `MachinesController.php`, `MoldingController.php` | `machines_views/`, `moldings_views/` | — | — |
| **Herramientas Tecámac** | `HerramientasTecamacController.php` | `herramientas/` | `herramientas/` | `herramientas/` |
| **Reportes & Productividad** | `ReporteProduccionController.php`, `ProductivityController.php`, `tiemposProduccionController.php` | `reportes/`, `reports/` | — | — |
| **Códigos QR** | `GenerarQRIndividualController.php`, `GenerarQRLoteController.php`, `LiberarQRPlantaController.php`, `RegenerarQRController.php` | En vistas del módulo correspondiente | — | — |
| **PDFs (Dibujos, Ayudas Visuales)** | `DibujosPdfController.php`, `DibujosFundicionPdfController.php`, `AyudasVisualesPdfController.php` | `pdf/` | — | — |
| **Usuarios y Auth** | `UserController.php`, `LoginController.php`, `LogoutController.php` | `users_views/`, `auth/` | — | — |
| **Logs del Sistema** | `SystemLogController.php` | `almacen/` o vistas admin | — | — |
| **Layout Base** | — | `layouts/appMenu.blade.php` | — | — |
| **Rutas Web** | `routes/web.php` | — | — | — |

---

## 3. DIRECTIVAS TÉCNICAS OBLIGATORIAS (REGLAS DE ORO)

1. **Stack Fijo (PROHIBIDO AGREGAR FRAMEWORKS):**
   - Backend: Laravel (PHP 8.x) con Eloquent ORM.
   - Frontend: **Blade + Vanilla JS + Vanilla CSS**.
   - No usar TailwindCSS, Bootstrap, React, Vue ni Alpine.
   - Alertas UI: SweetAlert2 (`almacenToast` o `Swal.fire` según módulo).
   - Bundler: Vite (`@vite(['resources/css/...', 'resources/js/...'])` en `@section('head')`).

2. **Controladores y Transacciones:**
   - Toda operación multi-tabla DEBE ir dentro de `DB::beginTransaction()`, `DB::commit()` y `try/catch (\Throwable $e)`.
   - Registrar errores con `Log::error(...)` con contexto (`user`, `ot`, `error`).
   - Usar `where()` de 4 argumentos: `->where('columna', '=', $valor, 'and')`.

3. **Vistas y JavaScript:**
   - Siempre `@csrf` en formularios POST/PUT/DELETE.
   - Rutas a JS inyectadas en `window.routes` vía `@json(route(...))`.
   - JS siempre envuelto en `document.addEventListener('DOMContentLoaded', ...)`.
   - Manejo de async/await con estados visuales de carga (`setVisualLoading`).

4. **Perfiles de Usuario:**
   - `1`: Administrador (Acceso total)
   - `2`: Gerente / Supervisor
   - `4`: Calidad Fundición
   - `5`: Almacén
   - `6`: Operador Maquinado
   - `8`: Calidad Soldadura

5. **Anti-Sobreingeniería (YAGNI):**
   - Reutilizar scopes, helpers y controladores existentes antes de crear archivos nuevos.
   - No crear wrappers ni Service Classes vacías si un método o scope resuelve la tarea.
