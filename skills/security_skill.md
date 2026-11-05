---
name: security_skill
description: Guía y patrones para prevención de SQL Injection, XSS, CSRF, control de acceso por perfiles (1,2,4,5,6,8) y middlewares en Project_saavedra.
version: 1.0.0
---

# Guía de Seguridad y Protección de Datos (Security Skill)

> **Directorio de Referencia:** `app/Http/Middleware/ y app/Providers/`

## Cuándo usar esta habilidad
- Al validar permisos de acceso por perfil de usuario (`auth()->user()->perfil`).
- Al proteger endpoints contra inyecciones SQL, ataques XSS o vulnerabilidades CSRF.
- Al crear o editar middlewares en `app/Http/Middleware/`.

---

## Instrucciones y Patrones Estándar

## 1. Prevención de Inyección SQL (SQL Injection)
Laravel protege el sistema de inyecciones SQL de manera nativa si usas el Query Builder de Eloquent o enlaces de parámetros.
- ** PÉSIMO (Vulnerable):**
```php
$operador = DB::select("SELECT * FROM users WHERE matricula = '" . $request->matricula . "'");
```
- ** EXCELENTE (Protegido por Eloquent/Bindings):**
```php
$operador = User::where('matricula', $request->matricula)->first();
// O con Query Builder crudo:
$operador = DB::select("SELECT * FROM users WHERE matricula = ?", [$request->matricula]);
```
*Regla de Oro:* Nunca concatene variables directamente en consultas SQL o en sentencias del tipo `whereRaw` o `selectRaw`.

---

## 2. Protección contra Cross-Site Scripting (XSS)
El ataque XSS ocurre cuando un usuario malintencionado inserta scripts HTML/JS en un campo de texto y este se renderiza en el navegador de otro usuario sin escapar.

- **Directiva de Escapado:** Usa siempre `{{ $variable }}` en tus archivos de Blade. Laravel convertirá automáticamente caracteres especiales a entidades HTML inocuas.
- **Evita `{!! $variable !!}`:** Úsalo **únicamente** para contenidos estáticos confiables o cuando el string haya sido explícitamente sanitizado con purificadores HTML.
- **Entradas de Texto:** Si una columna almacena texto libre y se muestra en paneles, limpia las etiquetas HTML antes de persistirlo:
 ```php
 $model->observaciones = strip_tags($request->observaciones);
 ```

---

## 3. Seguridad en Formularios y Peticiones (CSRF)
Cross-Site Request Forgery obliga a un usuario autenticado a enviar solicitudes no deseadas a un servidor web.

1. **Formularios Blade:** Todo formulario de tipo `POST`, `PUT`, `PATCH` o `DELETE` debe incluir la directiva de token CSRF:
 ```blade
 <form action="{{ route('guardar') }}" method="POST">
 @csrf
 ...
 </form>
 ```
2. **Peticiones Fetch / Axios:** Incluye el token extraído del meta-tag de la página en los headers de la solicitud:
 ```javascript
 const token = document.querySelector('meta[name="csrf-token"]').content;
 const response = await fetch('/api/guardar', {
 method: 'POST',
 headers: {
 'Content-Type': 'application/json',
 'X-CSRF-TOKEN': token
 },
 body: JSON.stringify(data)
 });
 ```

---

## 4. Autorización Basada en Perfiles (`auth()->user()->perfil`)
El sistema restringe las funcionalidades según la matriz de perfiles del personal:

| ID Perfil | Nombre del Rol | Ejemplo de Permiso |
|-----------|----------------|--------------------| 
| **1** | Administrador | Creación de OTs, dibujos, depuración total, gestión de usuarios, acceso global |
| **2** | Operador Maquinado | Registro de producción en máquinas y herramientas (sin menú extendido) |
| **3** | Master | Supervisión global multi-módulo (Administración, Calidad, Almacén) |
| **4** | Calidad Fundición | Revisión y liberación de OTs, SCARs, liberación de modelos |
| **5** | Almacén | Recepción, pre-órdenes, reprocesos, subida de documentos, inventarios |
| **6** | Supervisor de Producción | Administración de producción, prioridades, seguimiento de OTs |
| **8** | Calidad Soldadura / PTA | Liberación de botes/lotes, análisis PTA, segunda pasada |
| **pta_temp**| Sesión Temporal PTA | Vista acotada exclusivamente al flujo del procedimiento PTA en curso |

### Middleware de Ruta (Seguridad Temprana)
Protege las rutas en el archivo `routes/web.php` usando middlewares existentes o validación en el constructor de controladores:

- **En Controlador:**
```php
public function __construct() {
    $this->middleware('auth');
}

public function store(Request $request) {
    // Validar perfil en el método
    if (auth()->user()->perfil != 1) {
        abort(403, 'No tienes permisos de Administrador.');
    }
}
```

---

## 5. Prevención de Manipulación de Parámetros en URLs
Cuando crees endpoints de borrado o edición, nunca confíes únicamente en la información de la URL para autorizar la acción. Asegúrate de verificar que el recurso pertenece a un contexto válido.

```php
// PÉSIMO (Cualquiera podría cambiar el ID de la URL y borrar otra pieza):
public function destroy($id) {
 Pieza::destroy($id);
 return back();
}

// EXCELENTE (Valida que el usuario tenga acceso y el estado sea coherente):
public function destroy($id) {
 $pieza = Pieza::findOrFail($id);

 // Validar perfil autorizado para eliminar scrap
 if (!in_array(auth()->user()->perfil, [1, 8])) {
 abort(403, 'Acceso Denegado.');
 }

 $pieza->delete();
 return back()->with('success', 'Pieza eliminada correctamente.');
}
```

---

## 6. Middlewares Activos del Proyecto (Referencia Real)

| Middleware | Archivo | Descripción |
|---|---|---|
| `auth` | `Authenticate.php` | Bloquea el acceso a usuarios no autenticados |
| `CheckPtaAccess` | `CheckPtaAccess.php` | Verifica permisos específicos del flujo PTA |
| `guest` | `RedirectIfAuthenticated.php` | Redirige usuarios autenticados que intentan ir al login |

### Perfiles Reales del Sistema (Actualizado Oficial)
```php
// auth()->user()->perfil — valores reales en producción:
// 1 = Administrador: acceso total a todo el sistema
// 2 = Operador Maquinado: registro de medidas y producción en máquinas
// 3 = Master: supervisión global con menú extendido
// 4 = Calidad Fundición / Maquinados: liberación de piezas y SCARs
// 5 = Almacén: recepción, entrega, pre-órdenes, reprocesos, inventarios
// 6 = Supervisor de Producción: administración de prioridades y reportes
// 8 = Calidad Soldadura / PTA: análisis de laboratorio y liberación
// pta_temp = Sesión temporal restringida a PTA

// Verificación estándar en controladores:
$perfil = auth()->user()->perfil;
$esAdmin = ($perfil == 1);
$esSupervisor = ($perfil == 6);
$puedeEliminar = in_array($perfil, [1, 6]);
$esAlmacen = ($perfil == 5);
$esCalidad = in_array($perfil, [4, 8]);
```

---

## 7. Blindaje del Login y Control de Acceso por Red (IP Whitelist por Planta)

El acceso al sistema a través de `LoginController` implementa 4 capas estrictas de seguridad:

### A. Whitelist de IPs por Planta para Operadores (Perfil 2)
En entorno de producción (`app()->environment('production')`), los operadores únicamente pueden iniciar sesión desde las redes físicas autorizadas de la empresa:
* **Variables de entorno en `.env`:**
  * `PLANTA_TECAMAC_ALLOWED_IPS`: IPs públicas o prefijos IPv6 de Planta Tecámac (ej. `189.191.191.86,2806:105e:1c:4008`).
  * `PLANTA_LA_RAZA_ALLOWED_IPS`: IPs autorizadas de Planta La Raza (se deja vacía si aún no se cuenta con ella).
* **Regla de Ejecución:** La validación de IP se evalúa **ANTES de verificar el hash de contraseña**, evitando consumo de CPU y protegiendo contra filtraciones.
* **Excepción de Roles:** Los usuarios Master (`perfil == 3`) y Administrador (`perfil == 1`) cuentan con acceso global irrestricto desde cualquier conexión.
* **Auditoría Forense:** Al detectar un intento fuera de planta, se registran los metadatos completos en `SystemLog` y `Log::warning` (nombre del operador, matrícula, puesto, planta e IP) y se devuelve:
  > *"Acceso denegado: No puedes conectarte fuera de las instalaciones de la planta."*

### B. Prevención de Ataques de Fuerza Bruta (`RateLimiter`)
Protege contra ataques de diccionario o scripts automatizados:
* Clave de rastreo: `Str::transliterate(Str::lower($matricula).'|'.$clientIp)`.
* Umbral: Máximo **5 intentos fallidos**.
* Penalización: Bloqueo temporal por **60 segundos** (`RateLimiter::hit(..., 60)`).
* En login exitoso: Se resetea el contador con `RateLimiter::clear()`.

### C. Protección contra Fijación de Sesión (Session Fixation)
Inmediatamente después de autenticar al usuario (`Auth::login($user)`), es obligatorio regenerar el identificador de sesión para invalidar cookies previas no autenticadas:
```php
Auth::login($user);
$request->session()->regenerate();
```

### D. Protección contra DoS a Bcrypt en `LoginRequest`
Se aplican límites máximos de longitud en los campos de entrada para prevenir ataques de denegación de servicio por cálculo excesivo en Bcrypt:
```php
public function rules(): array
{
    return [
        'matricula' => 'required|string|max:50',
        'contrasena' => 'required|string|max:100',
    ];
}
```
