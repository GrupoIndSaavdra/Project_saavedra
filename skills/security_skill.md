# Guía de Seguridad y Protección de Datos (Security Skill)

> ** Directorio de Referencia:** `app/Http/Middleware/ y app/Providers/`
> *Usa los archivos en este directorio como base o inspiración al crear/modificar funcionalidades relacionadas con esta skill.*


La seguridad en `Project_saavedra` garantiza que sólo el personal autorizado ejecute las acciones adecuadas según su rol, y protege los servidores contra ataques externos y fugas de datos.

---

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
| **1** | Administrador | Creación de OTs, dibujos, depuración total, acceso global |
| **2** | Gerente/Supervisor | Ver todo, aprobar, auditoría completa |
| **4** | Calidad Fundición | Revisión y liberación de OTs, SCARs, liberación de modelos |
| **5** | Almacén | Recepción, pre-órdenes, reprocesos, subida de documentos |
| **6** | Operador Maquinado | Maquinado de piezas, registro de medidas en procesos |
| **8** | Calidad Soldadura | Liberación de botes y lotes de soldadura |

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

### Perfiles Reales del Sistema (Actualizado)
```php
// auth()->user()->perfil — valores reales en uso:
// 1 = Administrador: acceso total
// 2 = Gerente/Supervisor: ver todo, aprobar
// 4 = Calidad: revisión y liberación de OTs, SCARs
// 5 = Almacén: recepción, pre-órdenes, reprocesos
// 6 = Operador: maquinado de piezas, registro de medidas
// 8 = Calidad Soldadura: liberación de botes y lotes de soldadura

// Verificación estándar en controladores de Fundición:
$perfil = auth()->user()->perfil;
$puedeEliminar = in_array($perfil, [1, 2]);
$esAlmacen = ($perfil === 5);
$esCalidad = ($perfil === 4);
```
