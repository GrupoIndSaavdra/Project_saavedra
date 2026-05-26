# Guía de Controladores (Controllers Skill) - Project_saavedra

Esta guía define las reglas y convenciones para crear y mantener controladores en el proyecto Laravel `Project_saavedra`.

## Ubicación y Nombres
- **Ubicación:** `app/Http/Controllers/`
- **Convención de Nombres:** `[NombreEntidad o Caracteristica]Controller.php` (e.g., `WOController.php`, `PtaResultsController.php`).

## Estructura de Métodos
Se siguen convenciones similares a recursos de Laravel, pero con métodos adicionales según la lógica de negocio:
- `manage()` o `index()`: Para mostrar la vista principal o un dashboard de una sección.
- `store(Request $request)`: Para guardar registros.
- `show($id)`: Para mostrar detalles específicos.
- `destroy($id)`: Para eliminar lógicamente o físicamente un registro.
- `generatePDF($id)`: Usado frecuentemente para exportar datos usando la fachada `Barryvdh\DomPDF\Facade\Pdf`.

## Optimización y Consultas
- **Eager Loading:** Siempre evita el problema de *N+1 queries*. Si un modelo tiene relaciones que se usarán en bucles, precárgalas en la consulta principal:
  ```php
  $workOrders = Orden_trabajo::query()->with(['clases', 'moldura'])->get();
  ```
- **Uso de Caché de Modelos:** Para consultas repetitivas de catálogos (como usuarios u operadores), trae los datos antes de iterar y mapealos en memoria:
  ```php
  $usersCache = User::all()->keyBy('matricula');
  ```

## Retornos y Respuestas
- **Vistas:** Se utiliza `return view('carpeta_views.nombre_vista', compact('variable1', 'variable2'));`. Asegúrate de usar `compact()` para mantener limpieza.
- **AJAX / APIs Internas:** Si el método responde a una petición asíncrona, retorna respuestas JSON limpias usando `response()->json(['data' => $data], 200);`.
- **Redirecciones:** Usa `redirect()->route('nombre_ruta')->with('success', 'Mensaje');` para pasar datos temporales (flash) tras operaciones de escritura (crear, editar, eliminar).

## Dependencias
- Los controladores suelen instanciar otros controladores auxiliares directamente en su constructor o métodos si es estrictamente necesario, aunque se sugiere abstraer lógica compleja a Traits o los propios Modelos cuando sea posible para mantener controladores delgados.
