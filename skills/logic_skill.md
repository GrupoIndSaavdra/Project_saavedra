# Guía de Lógica y Modelos (Logic Skill) — Project Saavedra

> ** Directorio de Referencia:** `app/Models/ y Lógica de Negocio Central`
> *Usa los archivos en este directorio como base o inspiración al crear/modificar funcionalidades relacionadas con esta skill.*

Los modelos en `Project_saavedra` gestionan la estructura profunda de datos. Esta guía define cómo filtrar de forma masiva, cómo manipular fechas correctamente y cómo orquestar permisos de usuario complejos.

---

## 1. El Pilar de Perfiles (`auth()->user()->perfil`)

Todo el flujo de negocio cambia dependiendo de quién sea el usuario. El perfil numérico debe usarse para condicionales tempranos (Early Returns).

```php
public function procesarReporte() {
 $perfil = auth()->user()->perfil;

 // Early return para usuarios no autorizados
 if (!in_array($perfil, [1, 6, 8])) {
 return redirect()->route('home')->with('error', 'Acceso denegado. Se requiere nivel Gerencial o Calidad.');
 }

 // Lógica específica por perfil
 if ($perfil == 8) {
 $query->where('proceso', 'LIKE', '%Soldadura%');
 }
}
```

**Perfiles y sus nombres reales:**
```php
// 1 = Administrador: acceso total
// 2 = Gerente/Supervisor: ver todo, aprobar
// 4 = Calidad: revisión y liberación de OTs, SCARs
// 5 = Almacén: recepción, pre-órdenes, reprocesos
// 6 = Operador Maquinado: maquinado de piezas
// 8 = Calidad Soldadura: liberación de botes y lotes
```

---

## 2. Eloquent Queries — Optimizaciones de Búsqueda

Evita iterar sobre `->get()` para hacer sumatorias. Usa métodos de agregación directos en la BD.

```php
// PÉSIMO (Usa MBs de memoria RAM):
$piezas = Pieza::where('id_ot', 10)->get();
$totalBuenas = $piezas->where('error', 'Ninguno')->count();

// EXCELENTE (La base de datos hace el trabajo):
$totalBuenas = Pieza::where('id_ot', 10)->where('error', 'Ninguno')->count();

// Múltiples agregados en una sola consulta:
$stats = Pieza::where('id_ot', 10)
 ->selectRaw('
 COUNT(*) as total,
 SUM(CASE WHEN error = "Ninguno" THEN 1 ELSE 0 END) as buenas,
 SUM(CASE WHEN error != "Ninguno" THEN 1 ELSE 0 END) as malas
 ')
 ->first();
// Acceso: $stats->total, $stats->buenas, $stats->malas
```

---

## 3. Manipulación de Fechas con Carbon

Fechas y tiempos deben manejarse estrictamente con `\Carbon\Carbon` de Laravel.

```php
use Carbon\Carbon;

// Crear fechas formateadas para bases de datos
$fechaFin = Carbon::now()->format('Y-m-d H:i:s');

// Formatear fechas legibles para vistas o PDFs
$fechaLegible = Carbon::parse($registro->fecha_inicio)->translatedFormat('l d \d\e F \d\e Y, h:i A');
// Output: Lunes 25 de Octubre de 2026, 08:30 PM

// Diferencia entre fechas (para calcular tiempo transcurrido)
$diasTranscurridos = Carbon::parse($registro->created_at)->diffInDays(Carbon::now());

// Verificar si una fecha ya pasó
if (Carbon::parse($registro->fecha_limite)->isPast()) {
 // La OT está vencida
}

// Agregar días hábiles (solo días de semana)
$fechaEntrega = Carbon::now()->addWeekdays(5);
```

> Siempre usa `Carbon::parse()` para fechas que provienen de strings de la BD, no `new Carbon()`.

---

## 4. Sesiones y Estados Temporales (Flujos Multi-paso)

Los flujos como **PTA (Procedimiento de Trabajo Autorizado)** exigen operaciones parciales no guardadas de inmediato.

```php
// Paso 1: Iniciar el proceso y atarlo a la OT temporalmente
session()->put('pta_state', [
 'ot_id' => $ot->id,
 'step' => 'revisando_soldadura',
 'timestamp' => now()
]);

// Paso 2: El middleware o controlador verifica si hay un proceso activo
if (session()->has('pta_state')) {
 $data = session('pta_state');
 // Verificar que no haya expirado (más de 2 horas)
 if (Carbon::parse($data['timestamp'])->diffInHours(now()) > 2) {
 session()->forget('pta_state');
 return redirect()->route('pta.inicio')->with('error', 'Sesión de PTA expirada.');
 }
}

// Paso 3: Limpiar una vez que el proceso es finalizado o cancelado
session()->forget('pta_state');
```

---

## 5. Colecciones vs Consultas (Maps y KeyBy)

Para relacionar IDs y descripciones cuando no hay Foreign Keys estrictas:

```php
// Cargar catálogo a memoria (una sola query)
$maquinasDb = Maquinas::all()->keyBy('id'); // Indexado por ID

// Ahora en tu bucle, búsqueda O(1) sin queries adicionales
foreach($registros as $reg) {
 $nombreMaquina = $maquinasDb->get($reg->maquina_id)?->nombre ?? 'N/A';
}

// También útil: groupBy para agrupar registros
$piezasPorProceso = Pieza::all()->groupBy('proceso');
// Acceso: $piezasPorProceso['Cepillado'], $piezasPorProceso['Rectificado']
```

---

## 6. Relaciones Explícitas en Eloquent

La BD de Project_saavedra tiene tablas heredadas sin llaves foráneas estrictas. Declara siempre la llave local y foránea:

```php
// En el modelo Orden_trabajo.php
public function piezas() {
 return $this->hasMany(Pieza::class, 'id_ot', 'id');
}

public function fundicion() {
 return $this->hasOne(FundicionHistory::class, 'ot', 'n_ot'); // Columnas de texto, no IDs
}

// En el modelo Pieza.php
public function ordenTrabajo() {
 return $this->belongsTo(Orden_trabajo::class, 'id_ot', 'id');
}
```

---

## 7. Scopes Locales para Consultas Reutilizables

Evita duplicar `where()` en múltiples controladores. Define consultas comunes en el modelo:

```php
// En el modelo Pieza.php
public function scopeDeCalidad($query) {
 return $query->where('liberada', 1)->whereNotNull('verificado_por');
}

public function scopePorLote($query, $loteId) {
 return $query->where('lote_id', $loteId);
}

public function scopeSinScrap($query) {
 return $query->where('error', 'Ninguno');
}

// En el controlador:
$piezasCalidad = Pieza::deCalidad()->porLote($loteId)->sinScrap()->get();
```

---

## 8. Mutadores, Accesores y Attribute Casting

```php
// Casting automático en el modelo
protected $casts = [
 'metadatos' => 'array', // JSON ↔ PHP array automáticamente
 'fecha_liberacion' => 'datetime', // Carbon automático
 'aprobado' => 'boolean', // 0/1 ↔ true/false
 'peso_kg' => 'float',
];

// Accesores y Mutadores (Laravel 9+)
use Illuminate\Database\Eloquent\Casts\Attribute;

protected function nombreCompleto(): Attribute {
 return Attribute::make(
 get: fn ($value, $attributes) => "{$attributes['nombre']} {$attributes['apellido']}",
 set: fn ($value) => ['nombre' => strtolower($value)]
 );
}
```

> En `FundicionHistory`, `almacen_archivos` se guarda como JSON. Siempre verifica que sea array antes de usarlo:
> ```php
> $archivos = is_array($reg->almacen_archivos) ? $reg->almacen_archivos : [];
> ```

---

## 9. Agrupación Lógica en Consultas Eloquent (orWhere)

**ERROR GRAVE COMÚN:** Al mezclar `where()` y `orWhere()` sin closure, el `OR` anula condiciones anteriores.

```php
// PÉSIMO (Error lógico grave):
// Ejecuta: WHERE ot = 'A' OR (ot LIKE 'A_R%' AND estado = 'rechazado')
// Obtiene TODOS los registros de 'A' sin filtrar por estado
$registros = Model::where('ot', $ot)->orWhere('ot', 'LIKE', $ot . '_R%')->where('estado', 'rechazado')->get();

// CORRECTO (Agrupado con closure):
// Ejecuta: WHERE (ot = 'A' OR ot LIKE 'A_R%') AND estado = 'rechazado'
$registros = Model::where(function($q) use ($ot) {
 $q->where('ot', $ot)->orWhere('ot', 'LIKE', $ot . '_R%');
})->where('estado', 'rechazado')->get();
```

---

## 10. Patrón OT Base vs Reproceso (`_R1`, `_R2`, ...)

Las OTs de reproceso tienen sufijo `_R\d+`. Siempre extrae la OT base antes de buscar archivos:

```php
$esReproceso = (bool) preg_match('/_R\d+$/i', $reg->ot);
$baseOt = preg_replace('/_R\d+$/i', '', $reg->ot);

// Buscar registros relacionados (OT base + todos sus reprocesos)
$relatedRecords = FundicionHistory::where(function($q) use ($baseOt) {
 $q->where('ot', $baseOt)->orWhere('ot', 'LIKE', $baseOt . '_R%');
})->get();
```

---

## 11. Modelos Críticos del Proyecto (Referencia Rápida)

| Modelo | Tabla | Responsabilidad |
|---|---|---|
| `FundicionHistory` | `fundicion_history` | Estado completo de una OT de fundición |
| `LiberacionModeloFundicion` | `liberacion_modelos_fundicion` | Decisiones de calidad por clase |
| `PreOrdenFundicion` | `pre_ordenes_fundicion` | Pre-órdenes de reproceso de fundición |
| `ScarModelo` | `scar_modelos` | SCARs (no conformidades) de modelos de fundición |
| `Pieza` | `piezas` | Piezas individuales de cada proceso de maquinado |
| `FundicionFileLog` | `fundicion_file_logs` | Historial de archivos subidos/eliminados |
| `SoldaduraLote` | `soldadura_lotes` | Lotes de soldadura con botes |
| `SoldaduraBote` | `soldadura_botes` | Botes individuales dentro de un lote |
| `SystemLog` | `system_logs` | Eventos de auditoría del sistema |
| `QrGenerado` | `qrs_generados` | QRs generados para piezas y botes |

### Clasificación de Tipos de Modelo en Fundición
Los 4 tipos de modelo válidos son: `Fondo`, `Molde`, `Bombillo`, `Obturador`.
```php
$knownClasses = ['fondo', 'obturador', 'bombillo', 'molde'];
$hasKnownClass = collect($knownClasses)->contains(fn($kc) =>
 str_contains(strtolower($archivo), $kc)
);
```

---

## 12. Optimización de Queries con `chunk()` para Datos Masivos

Para procesar grandes volúmenes de registros sin agotar la memoria del servidor:

```php
// MAL: Carga todos los registros en RAM
$todas = FundicionHistory::all(); // Puede ser miles de registros

// BIEN: Procesa en lotes de 100
FundicionHistory::chunk(100, function($registros) {
 foreach ($registros as $registro) {
 // Procesar cada registro
 }
});

// También útil: lazy() para streams PHP
FundicionHistory::lazy()->each(function($registro) {
 // Procesa uno a la vez
});
```

---

## 13. Uso Correcto de `findOrFail()` vs `find()`

```php
// find() devuelve null si no existe → puede causar errores si no verificas
$pieza = Pieza::find($id);
if (!$pieza) {
 return response()->json(['error' => 'No encontrado'], 404);
}

// findOrFail() lanza ModelNotFoundException automáticamente → Laravel devuelve 404
$pieza = Pieza::findOrFail($id); // Más limpio para endpoints web

// firstOrCreate() — busca o crea si no existe
$history = FundicionHistory::firstOrCreate(
 ['ot' => $otName], // condición de búsqueda
 ['estado' => 'en_proceso'] // valores solo si se crea nuevo
);
```

---

## 14. 🔴 REGLA CRÍTICA: Anti-patrón de Memoria con Eloquent (PHP Fatal: memory exhausted)

> **Origen:** Bug real encontrado el 2026-08-18. Causó crashes en `/system-logs` y `/productionData`.
> **Causa raíz:** Eloquent hidrata modelos completos (incluyendo `GuardsAttributes`) para CADA fila — con tablas de miles de filas, esto agota los 128MB de límite de PHP.

### Tablas grandes del proyecto (referencia obligatoria)

| Tabla | Filas | Riesgo |
|---|---|---|
| `piezas` | **76,094** | 🔴 CRÍTICO |
| `cepillado_pza` / `primeraopesoldadura_pza` / `desbaste_pza` | 8,000–9,500 | 🔴 Alto |
| `metas` | 6,404 | 🔴 Alto |
| `revcalificado_pza` / `segundaopesoldadura_pza` | 7,900–8,400 | 🔴 Alto |
| `acabadobombillo_pza` | 6,290 | 🔴 Alto |
| `tiempos_produccion` | 2,669 | 🟡 Medio |
| `fechas_procesos` | 2,256 | 🟡 Medio |
| `clases` / `procesos` / `molduras` | < 250 | ✅ Seguro |
| `users` / `orden_trabajo` / `maquinas` | < 120 | ✅ Seguro |

### REGLA 1: Nunca `->get()` o `::all()` sin `->select()` en tablas grandes

```php
// ❌ PROHIBIDO en piezas, metas, tiempos_produccion, y *_pza tables:
$piezas = Pieza::query()->whereIn('id_ot', $ids)->get();
$metas  = Metas::all();
$tiempos = tiempoproduccion::all()->groupBy('id_clase');

// ✅ CORRECTO: seleccionar SOLO las columnas que el código usa
$piezas = Pieza::query()
    ->select(['id_ot', 'id_clase', 'id_operador', 'proceso', 'error', 'liberacion', 'n_pieza', 'created_at'])
    ->whereIn('id_ot', $ids)
    ->get();

$metas = Metas::query()
    ->select(['id', 'id_clase', 'proceso', 'h_inicio', 'h_termino', 't_estandar', 'meta'])
    ->get();

$tiempos = tiempoproduccion::query()
    ->select(['id_clase', 'proceso', 'tiempo', 'tamanio'])
    ->get()
    ->groupBy('id_clase');
```

### REGLA 2: Usar `DB::table()` para queries de solo lectura (filtros, dropdowns)

Cuando solo necesitas **valores escalares** para llenar un dropdown o filtro, `DB::table()` es mucho más eficiente que Eloquent — devuelve `stdClass` sin instanciar modelos ni pasar por `GuardsAttributes`:

```php
// ❌ PROHIBIDO: Eloquent hidrata modelos incluso para pluck()
$procesos = SystemLog::query()->distinct()->pluck('proceso');

// ✅ CORRECTO: DB::table() no instancia modelos Eloquent
$procesos = DB::table('system_logs')
    ->distinct()
    ->whereNotNull('proceso')
    ->orderBy('proceso')
    ->pluck('proceso');

// ✅ Para JOINs con dropdowns:
$ots = DB::table('system_logs')
    ->select(['system_logs.ot', 'molduras.nombre as moldura_nombre'])
    ->leftJoin('orden_trabajo', 'system_logs.id_ot', '=', 'orden_trabajo.id')
    ->leftJoin('molduras', 'orden_trabajo.id_moldura', '=', 'molduras.id')
    ->whereNotNull('system_logs.ot')
    ->distinct()
    ->get();
```

### REGLA 3: Para caches pre-cargados, siempre especificar columnas

```php
// ❌ Carga TODAS las columnas incluyendo campos pesados/no usados
$usersCache  = User::all()->keyBy('matricula');
$clasesCache = Clase::all()->keyBy('id');

// ✅ Solo las columnas que se usan en el loop
$usersCache  = User::query()
    ->select(['matricula', 'nombre', 'a_paterno', 'a_materno'])
    ->get()->keyBy('matricula');

$clasesCache = Clase::query()
    ->select(['id', 'id_ot', 'nombre', 'pedido', 'tamanio'])
    ->get()->keyBy('id');
```

### Checklist antes de escribir cualquier `->get()` o `::all()`

- [ ] ¿La tabla tiene más de 1,000 filas? → Agregar `->select([columnas_usadas])`
- [ ] ¿Es una query de filtro/dropdown? → Usar `DB::table()` en lugar de Eloquent
- [ ] ¿El resultado se usa solo para pluck()? → `DB::table()->pluck()` directamente
- [ ] ¿Se carga para hacer un `->keyBy()` o `->groupBy()`? → Agregar `->select()` primero

---

## 15. Patrón de Reintentos Automáticos en Operaciones Críticas del Servidor

Cuando ejecutes tareas complejas en el servidor que interactúan con APIs externas, bases de datos remotas o generación de archivos mediante librerías pesadas (ej. DomPDF generando PDFs multi-página de gran volumen), existe el riesgo de fallos temporales debido a picos en el consumo de recursos (RAM agotada momentáneamente, bloqueos de I/O en disco, bloqueos transaccionales).

Para mitigar esto, implementa el **Patrón de Reintentos (Retry Loop)**:
1. Intenta la operación dentro de un bucle `while` con un número máximo de intentos (usualmente 3).
2. Si la operación falla, captura el error, espera unos segundos con `sleep()`, libera memoria y vuelve a intentar.
3. **Regla de Oro:** Si el proceso tiene una fase destructiva (por ejemplo, depurar/eliminar los datos de origen una vez exportados), ejecuta esa acción **ÚNICAMENTE** si la operación anterior fue completamente exitosa y no lanzó excepciones.

```php
// ✅ PATRÓN CORRECTO: Reintentos en Procesamiento de Servidor
$maxAttempts = 3;
$attempt = 0;
$operationSuccess = false;
$lastExceptionMessage = '';

while ($attempt < $maxAttempts && !$operationSuccess) {
    $attempt++;
    try {
        // 1. Operación con riesgo de fallo por recursos / red
        $pdf = Pdf::loadView('reports.logs_layout', compact('data'))
            ->setOption('enable_javascript', false)
            ->setOption('enable_remote', false);

        $filePath = "exportados/reporte_semanal.pdf";
        Storage::disk('local')->put($filePath, $pdf->output());

        // 2. Liberar recursos explícitamente si tiene éxito
        unset($pdf);
        gc_collect_cycles();
        
        $operationSuccess = true; // Salir del bucle

    } catch (\Throwable $e) {
        $lastExceptionMessage = $e->getMessage();
        
        Log::warning("[GIS-Proceso] Intento {$attempt}/{$maxAttempts} fallido. Error: " . $lastExceptionMessage);
        
        // 3. Liberar recursos y esperar antes del reintento
        unset($pdf);
        gc_collect_cycles();
        
        sleep(2); // Pausa de 2 segundos para liberar carga del CPU/Disco
    }
}

// 4. Ejecutar acción destructiva SOLO tras éxito garantizado
if ($operationSuccess) {
    // Es seguro eliminar o cambiar el estado en la base de datos
    $eliminados = RegistroOriginal::whereIn('id', $ids)->delete();
    Log::info("[GIS-Proceso] Exportado correctamente. Eliminados {$eliminados} registros.");
} else {
    // Si todos los intentos fallaron, registrar el fallo técnico y no tocar los datos originales
    Log::error("[GIS-Proceso] Fallo técnico al procesar el lote tras {$maxAttempts} intentos. Error: {$lastExceptionMessage}");
    
    // Alerta opcional para la UI del administrador
    SystemLog::create([
        'action' => 'Error de Sistema',
        'details' => 'No se pudo exportar el lote. Detalles: ' . substr($lastExceptionMessage, 0, 250),
        'maquina' => 'Sistema'
    ]);
}
```

---

## 16. Blindaje de Consultas Encadenadas (Null-Safety) y Optimización de Índices Compuestos

### A. Null-Safety en Consultas Encadenadas (Evitar "Attempt to read property on null")
Cuando se realicen búsquedas de registros padre para luego consultar tablas hijas usando el ID obtenido (ej. buscar el ID de un proceso para encontrar la pieza asociada), siempre se debe comprobar explícitamente que el registro padre no sea `null` antes de acceder a sus atributos.

```php
// ❌ PÉSIMO (Si no existe el proceso, lanzará error Fatal de PHP al intentar leer ->id):
$proceso = Cepillado::where('id_proceso', $idString)->first();
$pieza = Pza_cepillado::where('id_proceso', $proceso->id)->first();

// ✅ EXCELENTE (Protección explícita mediante condicional):
$proceso = Cepillado::where('id_proceso', $idString)->first();
$pieza = null;
if ($proceso) {
    $pieza = Pza_cepillado::where('id_proceso', $proceso->id)->first();
}

// ✅ EXCELENTE (Utilizando el operador Null-safe de PHP 8 si se encadena directamente):
$idProceso = Cepillado::where('id_proceso', $idString)->first()?->id;
```

### B. Indexación Compuesta para Optimización de Consultas en Planta
En consultas con múltiples filtros en cascada (como buscar piezas por `id_ot`, `id_clase`, `id_operador` y `proceso` en la tabla `piezas` que contiene más de 76,000 registros), la base de datos requiere índices compuestos. 
Al crear migraciones para tablas de telemetría o de gran volumen, define índices que agrupen estas columnas de filtrado frecuente para evitar escaneos de tabla completa (Full Table Scan).

```php
Schema::table('piezas', function (Blueprint $table) {
    // Índice compuesto para acelerar consultas de datos de producción
    $table->index(['id_ot', 'id_clase', 'id_operador', 'proceso'], 'piezas_prod_composite_index');
});
```



