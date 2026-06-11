# 🛡️ Guía de Validación y Sanitización de Datos (Validation Skill)

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
