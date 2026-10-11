---
name: rework_cycle_skill
description: Guía y patrones para la gestión del ciclo de vida de reprocesos (_R1, _R2), liberaciones de modelos, SCARs y aislamiento de clases en Project_saavedra.
version: 1.0.0
---

# Habilidad de Ciclo de Reproceso y Liberaciones (Rework Cycle Skill) — Project Saavedra

> **Directorio de Referencia:** `app/Services/FundicionStateService.php y app/ViewModels/CalidadTableRowViewModel.php`

---

## ⚠️ DIRECTIVAS DE EJECUCIÓN OBLIGATORIA PARA EL AGENTE DE IA

Cuando implementes o modifiques la lógica de reinicio, reemplazo, liberación o reprocesos en Fundición o Calidad, DEBES cumplir estrictamente estas 5 reglas:

1. **Distinguir Reinicio vs Reemplazo:** 
   - **Reinicio (Destructivo):** Borra liberaciones, SCARs y estados en base de datos. Requiere modal rojo, timer de advertencia de 5s y confirmación.
   - **Reemplazo (Seguro):** Mantiene progresos y liberaciones; únicamente actualiza los archivos PDF/DWG en disco e incrementa los Hashes de clase.
2. **Evaluación de Afectación Parcial vs Total (`$esTotal`):** Compara siempre la cantidad de clases afectadas contra el conteo real de clases activas en la OT (`$clasesActivasCount`). Nunca ejecutes borrado de la OT completa si es un reinicio parcial de clases específicas.
3. **Aislamiento de Clases Pendientes:** En OTs de reproceso (`_R1`, `_R2`), procesa y filtra ÚNICAMENTE las clases que sufrieron el rechazo en el ciclo anterior. No arrastres clases aprobadas de ciclos pasados.
4. **Conservación de Historial:** Al reiniciar parcialmente una clase, desactiva o marca como desactualizados sus archivos anteriores agregando el sufijo `_anterior_` o `_anterior_n` en lugar de borrar irreversiblemente los planos de ingeniería.
5. **Reseteo de Banderas en `FundicionHistory`:** Al aplicar un reinicio parcial o total, resetea `pre_orden_sent = false`, `calidad_revision_status = null` y ajusta `pending_almacen_changes`.

---

## Instrucciones y Patrones Estándar

## 1. Lógica de Evaluación Parcial vs Total

```php
// ✅ PATRÓN CORRECTO: Evaluación estricta de afectación de clases
$clasesActivasCount = Clase::where('id_ot', '=', $otModel->id, 'and')->count();
$affectedCount = count($clasesPendientes);
$esTotal = ($affectedCount >= $clasesActivasCount && $clasesActivasCount > 0);

if ($action === 'reiniciar_completo' && !$esTotal) {
    $action = 'reiniciar_parcial';
}
```

---

## 2. Aislamiento de Clases en Reprocesos (`_R1`, `_R2`)

```php
// ✅ PATRÓN CORRECTO: Limpiar OTs de reproceso asociadas a una clase reiniciada
$baseOtCleanRep = preg_replace('/_.*_R\d+$|_R\d+$/i', '', $ot);

$reprocesos = FundicionHistory::where(function ($q) use ($baseOtCleanRep) {
    $q->where('ot', 'LIKE', $baseOtCleanRep . '_%_R%')
      ->orWhere('ot', 'LIKE', $baseOtCleanRep . '_R%');
})->get();

foreach ($reprocesos as $reproceso) {
    // Filtrar únicamente las clases pendientes del ciclo actual
}
```

---

## 3. Reseteo de Estados por Clase en Calidad

```php
// ✅ PATRÓN CORRECTO: Limpieza quirúrgica de tablas de calidad por clase
foreach ($pendingClasses as $clase) {
    $cNorm = strtolower(trim(Clase::normalizeClassName($clase) ?: $clase));
    
    LiberacionModeloFundicion::where('ot', '=', $ot, 'and')
        ->where(function ($q) use ($cNorm, $clase) {
            $q->whereRaw('LOWER(tipo_modelo) LIKE ?', ['%' . $cNorm . '%'])
              ->orWhereRaw('LOWER(tipo_modelo) LIKE ?', ['%' . strtolower(trim($clase)) . '%']);
        })->delete();

    ScarModelo::where('ot', '=', $ot, 'and')
        ->where(function ($q) use ($cNorm, $clase) {
            $q->whereRaw('LOWER(tipo_modelo) LIKE ?', ['%' . $cNorm . '%'])
              ->orWhereRaw('LOWER(tipo_modelo) LIKE ?', ['%' . strtolower(trim($clase)) . '%']);
        })->delete();
}
```
