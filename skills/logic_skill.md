# 🧠 Guía de Lógica y Modelos (Logic Skill) - Máximo Nivel

Los modelos en `Project_saavedra` gestionan la estructura profunda de datos. Esta guía define cómo filtrar de forma masiva, cómo manipular fechas correctamente y cómo orquestar permisos de usuario complejos.

## 1. El Pilar de Perfiles (`auth()->user()->perfil`)
Todo el flujo de negocio cambia dependiendo de quién sea el usuario. El perfil numérico debe usarse para condicionales tempranos (Early Returns).

```php
public function procesarReporte() {
    $perfil = auth()->user()->perfil;

    // Early return para usuarios no autorizados
    if (!in_array($perfil, [1, 6, 8])) {
        return redirect()->route('home')->with('error', 'Acceso denegado. Se requiere nivel Gerencial o Calidad.');
    }

    // Lógica para el perfil 8 (Calidad específica)
    if ($perfil == 8) {
        $query->where('proceso', 'LIKE', '%Soldadura%');
    }
}
```

## 2. Eloquent Queries - Optimizaciones de Búsqueda
Evita iterar sobre `->get()` para hacer sumatorias. Usa métodos de agregación directos en la BD para ahorrar memoria del servidor.

```php
// ❌ PÉSIMO (Usa MBs de memoria RAM):
$piezas = Pieza::where('id_ot', 10)->get();
$totalBuenas = $piezas->where('error', 'Ninguno')->count();

// ✅ EXCELENTE (La base de datos hace el trabajo):
$totalBuenas = Pieza::where('id_ot', 10)->where('error', 'Ninguno')->count();
```

## 3. Manipulación de Fechas con Carbon
Fechas y tiempos deben manejarse estrictamente con `\Carbon\Carbon` de Laravel para evitar inconsistencias de zonas horarias o formatos.

```php
use Carbon\Carbon;

// Crear fechas formateadas para bases de datos
$fechaFin = Carbon::now()->format('Y-m-d H:i:s');

// Formatear fechas legibles para el humano (Vistas o PDFs)
$fechaLegible = Carbon::parse($registro->fecha_inicio)->translatedFormat('l d \d\e F \d\e Y, h:i A'); 
// Output: Lunes 25 de Octubre de 2026, 08:30 PM
```

## 4. Sesiones y Estados Temporales (Flujos Multi-paso)
Los flujos como **PTA (Procedimiento de Trabajo Autorizado)** exigen que el usuario haga operaciones parciales que no se guardan permanentemente de inmediato.

- **Variables de Estado en Sesión:**
```php
// Paso 1: Iniciar el proceso de PTA y atarlo a la OT temporalmente
session()->put('pta_state', [
    'ot_id' => $ot->id,
    'step' => 'revisando_soldadura',
    'timestamp' => now()
]);

// Paso 2: El middleware o el controlador verifica si hay un proceso activo
if (session()->has('pta_state')) {
    $data = session('pta_state');
}

// Paso 3: Limpiar una vez que el proceso es finalizado o cancelado
session()->forget('pta_state');
```

## 5. Colecciones vs Consultas (Maps y KeyBy)
Para relacionar IDs y descripciones cuando no hay Foreign Keys estrictas o cuando quieres cruzar información masiva:

```php
// Cargar catálogo a memoria
$maquinasDb = Maquinas::all()->keyBy('id'); // Indexado por ID

// Ahora en tu bucle, en vez de hacer 100 queries:
foreach($registros as $reg) {
    // 0 costo, búsqueda en hash map O(1)
    $nombreMaquina = $maquinasDb->get($reg->maquina_id)?->nombre ?? 'N/A';
}
```

## 6. Relaciones Explícitas en Eloquent
Dado que la base de datos de `Project_saavedra` tiene muchas tablas heredadas u orientadas a procesos específicos sin llaves foráneas estrictas de base de datos, debes declarar explícitamente la llave local y la foránea en los modelos para evitar fallos.

```php
// En el modelo Orden_trabajo.php
public function piezas() {
    // belongsTo / hasMany (ClaseRelacionada, llave_foranea, llave_local)
    return $this->hasMany(Pieza::class, 'id_ot', 'id');
}
```

## 7. Scopes Locales para Consultas Reutilizables
Evita duplicar consultas de base de datos (`where`) en múltiples controladores. Define consultas comunes en el modelo usando `scopes`.

```php
// En el modelo Pieza.php
public function scopeDeCalidad($query) {
    return $query->where('liberada', 1)->whereNotNull('verificado_por');
}

public function scopePorLote($query, $loteId) {
    return $query->where('lote_id', $loteId);
}

// En el controlador se usa de manera fluida:
$piezasCalidad = Pieza::deCalidad()->porLote($loteId)->get();
```

## 8. Mutadores, Accesores y Attribute Casting
Formatea la información automáticamente al leerla o guardarla de la base de datos.
- **Casting de Atributos:** Convierte tipos de datos automáticamente (ej. JSON a array, enteros, booleanos).
```php
// En el modelo
protected $casts = [
    'metadatos' => 'array',
    'fecha_liberacion' => 'datetime',
    'aprobado' => 'boolean'
];
```
- **Accesores (Getters) y Mutadores (Setters):**
```php
// Laravel 9+: Accesor para nombre completo formateado
use Illuminate\Database\Eloquent\Casts\Attribute;

protected function nombreCompleto(): Attribute {
    return Attribute::make(
        get: fn ($value, $attributes) => "{$attributes['nombre']} {$attributes['apellido']}",
        set: fn ($value) => [
            'nombre' => strtolower($value) // Guarda en minúscula
        ]
    );
}
```
