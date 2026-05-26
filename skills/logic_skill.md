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
