# Guía de Validación y Sanitización de Datos (Validation Skill)

> ** Directorio de Referencia:** `app/Http/Requests/ y Validaciones en Controladores`
> *Usa los archivos en este directorio como base o inspiración al crear/modificar funcionalidades relacionadas con esta skill.*


La integridad de los datos en `Project_saavedra` es prioritaria. Ningún dato provisto por el usuario debe insertarse en la base de datos sin ser debidamente validado y sanitizado.

---

## 1. Regla Básica: Validar en el Punto de Entrada
La validación debe realizarse inmediatamente al entrar al controlador. Nunca proceses lógica de negocio antes de validar que los parámetros requeridos estén presentes y en el formato correcto.

---

## 2. Tipos de Validación en Laravel

### A. Validación en Controlador (Inline)
Para formularios pequeños o endpoints rápidos con menos de 3 campos.

```php
public function store(Request $request) {
 $validated = $request->validate([
 'lote' => 'required|string|max:50',
 'peso_total_kg' => 'required|numeric|min:0.01|max:9999.99',
 'fecha_ingreso' => 'required|date',
 ]);

 // $validated contiene únicamente los campos validados
}
```

### B. Form Requests Dedicados (Recomendado)
Para formularios grandes, flujos con archivos o lógica con reglas de negocio condicionales.
- Crea el Request: `php artisan make:request GuardarLoteRequest`

```php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GuardarLoteRequest extends FormRequest
{
 public function authorize()
 {
 // Solo operarios autorizados y administradores
 return in_array(auth()->user()->perfil, [1, 4, 8]);
 }

 public function rules()
 {
 return [
 'nombre' => 'required|string|min:3|max:100',
 'lote' => 'required|string|unique:soldadura_lotes,lote',
 'peso_total_kg' => 'required|numeric|decimal:2|min:0.5',
 'botes' => 'nullable|integer|min:1',
 ];
 }

 public function messages()
 {
 return [
 'lote.unique' => 'Este número de lote ya está registrado en el sistema.',
 'peso_total_kg.decimal' => 'El peso debe tener exactamente 2 decimales.',
 ];
 }
}
```

---

## 3. Reglas de Validación Clave y Formatos

- **`exists:table,column`**: Asegura integridad referencial.
 ```php
 'lote_id' => 'required|exists:soldadura_lotes,id'
 ```
- **`unique:table,column,except,idColumn`**: Evita duplicidad de llaves de negocio como matrículas o códigos de barras.
 ```php
 'matricula' => 'required|string|unique:soldadura_botes,matricula,' . $this->id
 ```
- **`in:val1,val2,...`**: Restringe valores de estados a enums lógicos.
 ```php
 'estado' => 'required|in:en_transito,almacen,liberado,rechazado'
 ```
- **`numeric|decimal:0,2`**: Para medidas y pesos con precisión flotante.
 ```php
 'peso_kg' => 'required|numeric|between:0.01,10.00'
 ```

---

## 4. Sanitización de Datos
Laravel y Eloquent nos protegen automáticamente contra inyección SQL si usamos el Query Builder estándar. Sin embargo, debes sanitizar entradas para prevenir Cross-Site Scripting (XSS) y datos corruptos.

1. **Eliminar Espacios en Blanco Innecesarios:**
 Laravel incluye por defecto el middleware `TrimStrings`. Aún así, si manejas códigos o matrículas con espacios al final, realiza un `trim()` manual antes de procesarlos.
 ```php
 $matricula = trim($request->matricula);
 ```

2. **Sanitizar HTML (XSS Prevention):**
 Si permites campos de texto libre o comentarios en los reportes de calidad, asegúrate de despojar etiquetas HTML usando `strip_tags()` antes de guardarlos.
 ```php
 $comentarioSanitizado = strip_tags($request->comentarios);
 ```

3. **Uso de Castings:**
 Fuerza los tipos de datos en el guardado de registros numéricos o booleanos para evitar almacenar cadenas vacías en columnas de enteros.
 ```php
 $bote->peso_kg = (float) $request->peso_kg;
 ```

---

## 5. Validaciones Frecuentes en el Proyecto (Referencia Real)
Estas son validaciones reales que se hacen en el sistema:

### Validación de OT y Sanitización del Nombre
En Almacén y Calidad, el nombre de la OT se sanitiza para usarse como nombre de directorio en el servidor:
```php
// Patrón real en AlmacenFundicionController y CalidadFundicionController
$otNameSanitized = preg_replace('/[^a-zA-Z0-9_\-\. ]/', '_', $otName);
// Jamás almacenes la OT sin sanitizar en el path del disco
$storagePath = 'DOCUMENTACION_GIS/ALMACEN_FUNDICION/' . $otNameSanitized . '/';
```

### Validación de Estado de OT en Blade (antes de renderizar controles)
```php
@php
 // Validar si el usuario puede eliminar archivos basándose en su perfil y el estado de la OT
 $alertSent = (bool)($targetReg->pre_orden_email_sent || $targetReg->pre_orden_sent);
 $canDelete = false;
 if (in_array(auth()->user()->perfil, [1, 2])) $canDelete = true;
 elseif (auth()->user()->perfil == 5 && $fileOwner === 'almacen' && !$alertSent) $canDelete = true;
 elseif (auth()->user()->perfil == 4 && $fileOwner === 'calidad') $canDelete = true;
@endphp
```

### Validación de Extensión de Archivo en Subida
```php
$request->validate([
 'archivo' => 'required|file|mimes:pdf,jpg,jpeg,png,gif,webp|max:20480',
]);
```

### Validación con `after` (Reglas Post-Validación Cruzada)
Cuando una regla depende de otra columna o condición de negocio:
```php
public function rules(): array
{
    return [
        'fecha_fin' => 'required|date',
        'fecha_inicio' => 'required|date',
    ];
}

public function withValidator($validator): void
{
    $validator->after(function ($validator) {
        if ($this->fecha_fin < $this->fecha_inicio) {
            $validator->errors()->add('fecha_fin', 'La fecha de fin debe ser posterior a la fecha de inicio.');
        }
    });
}
```

### Validación de Parámetros de Ruta (Request de Detalle)
Para endpoints que reciben IDs en la URL, valida la existencia y el permiso:
```php
public function show(string $ot): \Illuminate\Http\Response
{
    // Validar existencia antes de procesar
    $registro = FundicionHistory::where('ot', '=', $ot, 'and')->firstOrFail();

    // Validar permiso de acceso al recurso
    if (!in_array(auth()->user()->perfil, [1, 2, 4, 5])) {
        abort(403, 'No tienes permiso para ver esta OT.');
    }

    return view('calidad.detalle', compact('registro'));
}
```

