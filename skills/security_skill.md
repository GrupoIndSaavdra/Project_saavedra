# 🔒 Guía de Seguridad y Protección de Datos (Security Skill)

La seguridad en `Project_saavedra` garantiza que sólo el personal autorizado ejecute las acciones adecuadas según su rol, y protege los servidores contra ataques externos y fugas de datos.

---

## 1. Prevención de Inyección SQL (SQL Injection)
Laravel protege el sistema de inyecciones SQL de manera nativa si usas el Query Builder de Eloquent o enlaces de parámetros.
- **❌ PÉSIMO (Vulnerable):**
```php
$operador = DB::select("SELECT * FROM users WHERE matricula = '" . $request->matricula . "'");
```
- **✅ EXCELENTE (Protegido por Eloquent/Bindings):**
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
| **1**     | Administrador  | Creación de OTs, dibujos, depuración total |
| **2**     | Operador       | Maquinado de piezas, registro de medidas |
| **5**     | Almacén        | Recepción y liberación de botes de soldadura |
| **6 / 8** | Calidad / Gte  | Liberación final, auditoría de logs, PTA |

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
// ❌ PÉSIMO (Cualquiera podría cambiar el ID de la URL y borrar otra pieza):
public function destroy($id) {
    Pieza::destroy($id);
    return back();
}

// ✅ EXCELENTE (Valida que el usuario tenga acceso y el estado sea coherente):
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
