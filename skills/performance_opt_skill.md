---
name: performance_opt_skill
description: Guía y patrones para la optimización extrema de rendimiento, prevención de PHP Fatal memory exhausted, paginación masiva y desfragmentación de base de datos en Project_saavedra.
version: 1.0.0
---

# Habilidad de Optimización de Rendimiento (Performance Skill) — Project Saavedra

> **Directorio de Referencia:** `app/Services/ y Optimización Global`

---

## ⚠️ DIRECTIVAS DE EJECUCIÓN OBLIGATORIA PARA EL AGENTE DE IA

Cuando escribas o refactorices consultas SQL, comandos Artisan o bucles masivos, DEBES cumplir estrictamente estas 5 reglas:

1. **PROHIBIDO `Model::all()` en tablas grandes:** En `piezas`, `metas`, `tiempos_produccion`, `system_logs` y `*_pza`, NUNCA hagas `Model::all()` ni `->get()` sin `->select(['col1', 'col2'])`.
2. **Uso de `lazyById()` en bucles destructivos:** Si eliminas o modificas registros dentro de una iteración, usa `lazyById($chunkSize)` en lugar de `chunk($chunkSize)` para evitar desfasamiento del OFFSET SQL.
3. **`DB::table()` para sólo-lectura:** Para llenar selects, dropdowns o conteos simples, usa `DB::table(...)` para evitar instanciar modelos Eloquent en RAM.
4. **Liberación de memoria en bucles (`gc_collect_cycles()`):** En procesos masivos, ejecuta `unset($variable)` e invoca `gc_collect_cycles()` tras procesar cada lote.
5. **Desfragmentación tras borrado masivo:** Tras borrar masivamente en tablas pesadas InnoDB, ejecuta `DB::statement('OPTIMIZE TABLE nombre_tabla');`.

---

## Instrucciones y Patrones Estándar

## 1. Tabla de Riesgo de Memoria por Modelo

| Modelo / Tabla | Filas Aprox. | Riesgo de RAM | Regla de Consulta |
|---|---|---|---|
| `Pieza` / `piezas` | **76,000+** | 🔴 CRÍTICO | `->select([...])` obligatorio siempre |
| `Metas` / `metas` | **6,400+** | 🔴 Alto | `DB::table()` o `->select([...])` |
| `SystemLog` / `system_logs` | **10,000+** | 🔴 Alto | `->paginate(50)` + `->select([...])` |
| `*_pza` (Maquinado) | **8,000–9,500** | 🔴 Alto | `->select([...])` obligatorio siempre |
| `FundicionHistory` | < 1,000 | 🟡 Medio | Eager loading con `with(...)` |
| `Clase` / `User` / `Maquinas` | < 300 | ✅ Seguro | `get()` seguro |

---

## 2. Patrón de Búsqueda O(1) con Pre-carga en Memoria (Evitar N+1)

```php
// ❌ INEFICIENTE (N+1 queries en bucle de 10,000 elementos)
foreach ($logs as $log) {
    $user = User::where('matricula', '=', $log->user_matricula, 'and')->first();
}

// ✅ EFICIENTE (1 sola query inicial con mapa en memoria O(1))
$matriculas = $logs->pluck('user_matricula')->filter()->unique()->toArray();

$usersMap = User::query()
    ->select(['matricula', 'nombre', 'a_paterno'])
    ->whereIn('matricula', $matriculas)
    ->get()
    ->keyBy('matricula');

foreach ($logs as $log) {
    $user = $usersMap->get($log->user_matricula);
}
```

---

## 3. Patrón de Iteración Masiva Segura (`lazyById`)

```php
// ✅ PATRÓN CORRECTO: Procesamiento masivo sin memory leak ni salto de registros
$query = FundicionHistory::query()
    ->select(['id', 'ot', 'estado'])
    ->orderBy('id')
    ->lazyById(300);

foreach ($query as $registro) {
    // Procesamiento...
}
```
