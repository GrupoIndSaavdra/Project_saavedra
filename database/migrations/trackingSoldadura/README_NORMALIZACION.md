# Sistema de Tracking de Soldadura - Estructura Normalizada

## 📋 Resumen de la Normalización

Este sistema ha sido reestructurado siguiendo el principio de **ENTIDADES vs EVENTOS** para eliminar la redundancia y mejorar la integridad de los datos.

## 🎯 Problema Original

Las tablas mezclaban responsabilidades y duplicaban datos:
- Campos como `nombre`, `lote`, `numero_factura`, `peso`, `fecha_*` se repetían en múltiples tablas
- Cada tabla guardaba más información de la que le correspondía
- No había una entidad central clara

## ✅ Solución Implementada

### 1. **ENTIDADES** (cosas que existen)

#### `soldadura_lotes` - Entidad Principal
Información estable del lote completo que ingresa:
```
- id
- matricula (único, generado automáticamente)
- nombre (tipo de soldadura)
- lote (número de lote del proveedor)
- numero_factura
- peso_total_kg
- fecha_ingreso
- botes_generados (contador)
```

#### `soldadura_botes` - Subdivisiones del Lote
Cada bote de 5kg generado a partir del lote:
```
- id
- lote_id (FK → soldadura_lotes)
- matricula (único, formato: matricula_lote-001)
- numero_bote (secuencial dentro del lote)
- peso_kg (normalmente 5kg)
- estado (pendiente|en_transito|en_planta|liberado)
```

### 2. **EVENTOS** (cosas que pasan)

#### `soldadura_recepciones_planta` - Registro de Recepción
Cuando un bote llega a la planta:
```
- id
- bote_id (FK → soldadura_botes) [UNIQUE]
- recibido_por (FK → users)
- fecha_hora_recepcion
- observaciones
```

#### `soldadura_liberaciones` - Registro de Liberación
Cuando un bote se entrega a un operador:
```
- id
- bote_id (FK → soldadura_botes) [UNIQUE]
- operador_id (FK → users)
- liberado_por (FK → users)
- matricula_liberacion (único)
- fecha_hora_liberacion
- observaciones
```

## 🔗 Relaciones

```
soldadura_lotes (1) ──────┬ (N) soldadura_botes
                           │
                           ├──── (1) soldadura_recepciones_planta
                           │
                           └──── (1) soldadura_liberaciones
```

## 💡 Beneficios

### ❌ Antes
- Datos duplicados en múltiples tablas
- Cambios inconsistentes
- Dificultad para mantener
- Desperdicio de espacio

### ✅ Ahora
- **Un solo lugar para cada dato**
- Cambios consistentes (si cambia el peso del lote, cambia en un solo lugar)
- Relaciones claras con claves foráneas
- Más fácil de escalar (agregar nuevos procesos)
- Integridad referencial garantizada

## 📊 Flujo de Datos

1. **Generación de Lote**
   - Se crea registro en `soldadura_lotes`
   - Se genera matrícula única

2. **Generación de Botes**
   - Se crean N botes en `soldadura_botes` (N = peso_total / 5)
   - Cada bote referencia su `lote_id`
   - Estado inicial: `en_transito`

3. **Recepción en Planta**
   - Al escanear QR del bote, se crea registro en `soldadura_recepciones_planta`
   - Se actualiza estado del bote a `en_planta`

4. **Liberación a Operador**
   - Se crea registro en `soldadura_liberaciones`
   - Se actualiza estado del bote a `liberado`

## 🔍 Acceso a Datos

### Obtener información completa de un bote:

```php
$bote = SoldaduraBote::with(['lote', 'recepcion', 'liberacion'])->find($id);

// Acceso a datos del lote (sin duplicar)
$bote->lote->nombre;          // Nombre de la soldadura
$bote->lote->numero_factura;  // Número de factura
$bote->lote->fecha_ingreso;   // Fecha de ingreso

// Acceso a eventos
$bote->recepcion;              // Datos de recepción (si existe)
$bote->liberacion;             // Datos de liberación (si existe)

// Accesores para conveniencia
$bote->nombre_soldadura;       // Accessor que devuelve $bote->lote->nombre
$bote->numero_factura;         // Accessor que devuelve $bote->lote->numero_factura
```

## 🎨 Matrícula/ID Único

### Formato de Matrículas:

**Lote:**
```
DDMMYYHHMMXXXYYY
Ejemplo: 14012614301234SOL123
```
- DD: Día
- MM: Mes
- YY: Año (2 dígitos)
- HH: Hora
- MM: Minutos
- XXX: Primeras 3 letras del nombre
- YYY: Primeros 3 caracteres del lote

**Bote:**
```
[MATRICULA_LOTE]-[NUMERO]
Ejemplo: 14012614301234SOL123-001
```

**Liberación:**
```
[MATRICULA_BOTE]-OP[MATRICULA_OPERADOR]
Ejemplo: 14012614301234SOL123-001-OP12345
```

## 📝 Regla de Oro

> **Antes de agregar un campo a una tabla, pregúntate:**
> ¿Este dato describe QUÉ ES el objeto o QUÉ PASÓ con el objeto?
> 
> - **Qué ES** → Tabla de ENTIDAD (soldadura_lotes, soldadura_botes)
> - **Qué PASÓ** → Tabla de EVENTO (recepciones, liberaciones)

## 🚀 Migraciones

Para aplicar esta nueva estructura:

```bash
# 1. Respaldar la base de datos actual
php artisan db:backup

# 2. Eliminar tablas antiguas (si existen)
php artisan migrate:rollback --path=database/migrations/trackingSoldadura

# 3. Ejecutar nuevas migraciones
php artisan migrate --path=database/migrations/trackingSoldadura
```

## ⚠️ Notas Importantes

1. **Unique Constraints**: Un bote solo puede tener UNA recepción y UNA liberación
2. **Cascadas**: Si se elimina un lote, se eliminan todos sus botes y eventos relacionados
3. **Estados**: Los cambios de estado se manejan en conjunto con la creación de eventos
4. **Transacciones**: Las operaciones críticas usan transacciones DB para garantizar consistencia

---

**Autor:** Sistema de Normalización
**Fecha:** 2026-01-14
**Versión:** 1.0