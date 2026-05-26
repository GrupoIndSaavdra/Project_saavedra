# ⚙️ Guía de Controladores (Controllers Skill) - Máximo Nivel

Los controladores en `Project_saavedra` son el corazón del sistema. Deben ser robustos, a prueba de fallos y estrictamente optimizados. No se permite código espagueti.

## 1. Nomenclatura, Rutas y Middlewares
- **Convención:** `[Entidad]Controller.php` (Ej. `PtaResultsController.php`).
- **Middlewares en el Constructor:** Todo controlador que maneje datos confidenciales debe bloquear el acceso a invitados.
```php
public function __construct() {
    $this->middleware('auth');
    // Instanciar servicios o helpers compartidos
    $this->classController = new ClassController();
}
```

## 2. Protección de Integridad (Transacciones y Excepciones)
Cualquier método que afecte múltiples modelos (`insert`, `update`, `delete`) **DEBE** usar `DB::transaction()` y un bloque `try/catch` para evitar bases de datos corruptas si el script falla a la mitad.

```php
use Illuminate\Support\Facades\DB;

public function store(Request $request) {
    // 1. Validaciones previas
    $request->validate(['n_pieza' => 'required', 'estado' => 'required']);

    try {
        DB::beginTransaction();

        // 2. Operaciones Críticas
        $orden = Orden_trabajo::find($request->ot_id);
        $orden->estado = 'Completado';
        $orden->save();

        Pieza::where('id_ot', $orden->id)->update(['liberacion' => 1]);

        DB::commit(); // Todo se guarda si llega aquí
        return response()->json(['success' => 'Operación completada']);

    } catch (\Exception $e) {
        DB::rollBack(); // Revertir todo si hay un error
        // Guardar log si es necesario
        \Log::error("Error al guardar OT: " . $e->getMessage());
        return response()->json(['error' => 'Ocurrió un error interno'], 500);
    }
}
```

## 3. Resolución de Rendimiento (Eager Loading Avanzado)
El problema N+1 es el enemigo número uno. No basta con usar `with()`, a veces necesitas filtrar relaciones precargadas.

```php
// ✅ EXCELENTE: Precarga la moldura y SOLO las clases que NO están finalizadas
$workOrders = Orden_trabajo::query()
    ->with(['moldura', 'clases' => function($query) {
        $query->where('finalizada', 0);
    }])
    ->get();
```

## 4. Tipos de Respuestas (Blade vs JSON vs Redirect)

El desarrollador debe saber exactamente cómo debe responder el controlador basándose en quién hizo la solicitud (El Navegador o JavaScript).

- **Renderizado Completo (Blade):** Para navegación de menús. 
  ```php
  return view('wo_views.show', compact('orden', 'piezas'));
  ```
- **Petición Fetch/AJAX:** Responder JSON siempre, controlando los códigos HTTP (200, 400, 404, 500).
  ```php
  return response()->json([
      'success' => true,
      'data' => $resultadosHtml // Puedes mandar HTML pre-renderizado aquí
  ], 200);
  ```
- **Formularios Síncronos (`<form action="...">`):** Redirige a la vista anterior con mensajes de estado.
  ```php
  return redirect()->back()->with('success', 'La clase ha sido cerrada.');
  // O con errores:
  return redirect()->route('home')->withErrors(['error' => 'No tienes permiso']);
  ```
