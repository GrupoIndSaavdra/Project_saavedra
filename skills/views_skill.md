# 👁️ Guía de Vistas Blade (Views Skill) - Máximo Nivel

> **📁 Directorio de Referencia:** `resources/views/`
> *Usa los archivos en este directorio como base o inspiración al crear/modificar funcionalidades relacionadas con esta skill.*


`Project_saavedra` usa Blade como motor de renderizado. Las vistas deben ser ligeras, modulares y seguras. 

## 1. Directivas de Seguridad (CSRF y Method Spoofing)
Todo formulario POST/PUT/DELETE tradicional **DEBE** incluir las directivas protectoras.

```blade
<form action="{{ route('wo.guardar') }}" method="POST">
    {{-- Token de seguridad obligatorio --}}
    @csrf 
    {{-- Falsificación de método si necesitas actualizar --}}
    @method('PUT') 
    
    <input type="text" name="n_pieza" required>
    <button type="submit" class="btns">Actualizar</button>
</form>
```

## 2. Estructura Exacta y Uso de `@vite`
La inclusión de scripts es estricta. Nunca incluyas scripts en el medio del documento. Usa `@section('head')` para assets estáticos Vite o en su defecto layouts extendidos.

```blade
@extends('layouts.appMenu')

@section('head')
    <title>Gestión Avanzada</title>
    {{-- Vite inyectará los hashes y el hot-reload en desarrollo --}}
    @vite(['resources/css/wo_views/avanzada.css', 'resources/js/wo_views/avanzada.js'])
@endsection
```

## 3. Comunicación a JavaScript (`window.routes` y Variables)
Nunca quemes URLs ni tokens dentro del JS externo. Usa este patrón exacto antes de que cierre el `@section('content')` o dentro del mismo.

```blade
<script>
    // Se recomienda poner esto al final del content o en un push de scripts
    window.routes = {
        ...(window.routes || {}),
        apiActualizar: @json(route('api.actualizar.pieza')),
        apiEliminar: @json(route('api.eliminar.pieza', ['id' => ':id'])) // Truco para reemplazar luego
    };
    
    window.usuarioConfig = {
        perfil: @json(auth()->user()->perfil),
        nombre: @json(auth()->user()->nombre),
        modoOscuro: false // Configuración extra
    };
</script>
```

## 4. Uso de Componentes / Partials (Blade Components)
Para evitar repetir HTML (como Modales o tarjetas), usa la etiqueta `@include`. Puedes pasarle variables directamente:

```blade
{{-- En views/wo_views/index.blade.php --}}
<div class="lista">
    @foreach($ordenes as $orden)
        {{-- Llamamos a la sub-vista pasándole la variable $orden y renombrándola --}}
        @include('partials.tarjeta_orden', ['data' => $orden, 'modo' => 'reducido'])
    @endforeach
</div>
```

## 5. Renderizado Condicional Limpio
Evita usar PHP crudo (`<?php ?>`). Usa directivas Blade siempre.

```blade
@auth
    <span>Usuario Logueado</span>
@endauth

@if($clase->piezas_buenas >= $clase->total)
    <span class="badge bg-green">Completado</span>
@elseif($clase->piezas_malas > 0)
    <span class="badge bg-red">Scrap Detectado</span>
@else
    <span class="badge bg-blue">En Proceso</span>
@endif
```

## 6. Mostrar Errores de Validación de Entrada
Cuando las validaciones de Laravel fallan, debes reportarlo de inmediato en la UI de forma legible.

- **Para listar todos los errores al inicio del formulario:**
```blade
@if ($errors->any())
    <div class="alert alert-danger">
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif
```

- **Para resaltar un campo específico con error:**
```blade
<div class="form-group">
    <label for="n_pieza">Número de Pieza</label>
    <input type="text" id="n_pieza" name="n_pieza" class="@error('n_pieza') is-invalid @enderror">
    @error('n_pieza')
        <span class="error-message">{{ $message }}</span>
    @enderror
</div>
```

## 7. Mensajes de Sesión (Toasts / Flash Messages)
Para dar retroalimentación al usuario al redirigir pantallas:

```blade
@if (session('success'))
    <div class="toast toast-success fade-in">
        {{ session('success') }}
    </div>
@endif

@if (session('error'))
    <div class="toast toast-danger fade-in">
        {{ session('error') }}
    </div>
@endif
```

## 8. Inyección Limpia de Scripts/Estilos con `@stack` y `@push`
En lugar de forzar scripts globales, si un componente o vista parcial requiere CSS o JS específico, inyéctalos de forma modular en los "stacks" definidos en el layout general (`layouts.appMenu`).

- **En el Layout Padre (`layouts.appMenu.blade.php`):**
```blade
<head>
    ...
    @stack('styles')
</head>
<body>
    ...
    @stack('scripts')
</body>
```

- **En la vista de Blade hija:**
```blade
@push('styles')
    <link rel="stylesheet" href="{{ asset('css/custom-modal.css') }}">
@endpush

@push('scripts')
    <script src="{{ asset('js/custom-modal.js') }}"></script>
@endpush
```

## 9. Seguridad XSS: Escapar `{{ }}` vs Renderizar `{!! !!}`
- **Siempre usa double curly braces `{{ $variable }}`:** Esto escapa automáticamente etiquetas HTML/JS previniendo inyección maliciosa (XSS).
- **Evita a toda costa `{!! $variable !!}`:** Esto renderiza HTML crudo y es una brecha de seguridad grave a menos que el contenido haya sido sanitizado previamente y sea 100% confiable.

## 10. Separación y Renderizado Condicional de Archivos (Categorización)
Cuando se listan archivos (ej. en Calidad o Almacén) que deben separarse visualmente en contenedores diferentes basados en un estado (ej. Aprobados vs Rechazados), NUNCA lo dejes a la suerte dentro del render HTML iterativo ni asumas estados de arreglos combinados.

1. **Pre-procesamiento en arrays estrictos:** Al leer y agrupar la información, define y llena arreglos específicos separados ANTES del `@foreach` en el HTML.
```php
@php
    $rechazadosDibujos = [];
    $rechazadosAyudas = [];
    $rechazadosOtros = [];
    
    foreach ($archivos as $archivo) {
        // ... Lógica para verificar rechazo de LA CLASE ACTUAL
        if ($matchesRejected) {
            if ($tipo === 'dibujo') $rechazadosDibujos[] = $archivo;
            elseif ($tipo === 'ayuda') $rechazadosAyudas[] = $archivo;
            continue; // CRÍTICO: Prevenir que el archivo caiga en arreglos aprobados
        }
        // ... Lógica de aprobados
    }
@endphp
```

2. **Renderizado en Grillas Modulares:** Evalúa el contenido usando `count()` de manera independiente. Si usas estilos de fondo para resaltar estatus (ej. contenedor rojo para rechazados `#fef2f2`), aplica el estilo de fondo sobre el contenedor principal (`div.alm-pdf-grid`), NO en cada tarjeta individual.

```blade
@if (count($rechazadosDibujos) > 0)
    <h3 style="color: #9c0300;">Dibujos Rechazados</h3>
    {{-- Fondo de estatus en el contenedor padre --}}
    <div class="alm-pdf-grid" style="background-color: #fef2f2; padding: 15px; border-radius: 8px; border: 1px solid #fecaca;">
        @foreach ($rechazadosDibujos as $dibujo)
            @include('partials.file_card', ['file' => $dibujo, 'status' => 'rechazado'])
        @endforeach
    </div>
@endif
```

---

## 11. Estructura de Vistas del Proyecto (Referencia Rápida)
Las vistas están organizadas por módulo. Nunca mezcles CSS de diferentes módulos:

| Carpeta en `resources/views/` | Descripción |
|---|---|
| `almacen/` | Vistas de Almacén Fundición (recepción, liberación, reprocesos) |
| `calidad/` | Vistas de Calidad Fundición (revisión, SCARs, liberación de modelos) |
| `wo_views/` | Vistas de Órdenes de Trabajo (listado, detalle, maquinado) |
| `pdf/` | Plantillas exclusivas para generación PDF con DomPDF |
| `layouts/` | Layout base `appMenu.blade.php` del que extienden todas las vistas |
| `emails/` | Plantillas de correo enviadas vía Mailables de Laravel |
| `pta_views/` | Vistas del flujo PTA (Procedimiento de Trabajo Autorizado) |
| `processes_views/` | Vistas por proceso de maquinado (cepillado, rectificado, etc.) |

### CSS y JS por Módulo
Cada módulo tiene su propio archivo CSS y JS en `resources/css/` y `resources/js/`:
- **Almacén Fundición:** `almacen_views/almacen_fundicion.css` + `almacen_views/almacen_fundicion.js`
- **Calidad Fundición:** `calidad_views/calidad_fundicion.css` + `calidad_views/calidad_fundicion.js`
- **Liberación:** `almacen_views/lib_liberacion.css`

Siempre cárgalos con `@vite` en el `@section('head')` de la vista:
```blade
@section('head')
    <title>Almacén Fundición</title>
    @vite(['resources/css/almacen_views/almacen_fundicion.css',
           'resources/js/almacen_views/almacen_fundicion.js'])
@endsection
```

## 12. Sincronización entre Controladores y Vistas (Evitar Variables Indefinidas y Fallos en Blade)

1. **Limpieza de variables al refactorizar**: Cuando modifiques un controlador para remover filtros o lógica (por ejemplo, remover una columna de catálogo como `Clase` y pasar a un flujo puramente de `Proceso`), es imperativo buscar y eliminar **todas** las referencias a esas variables en las vistas Blade correspondientes.
2. **Cuelgues por compilación**: Laravel Blade compila la plantilla completa. Si una variable que eliminaste en el controlador (ej. `$clasesUnicas`) se mantiene dentro de una directiva `@if` o `@foreach`, la vista lanzará una excepción fatal `ErrorException: Undefined variable`, incluso si esa rama del condicional supuestamente está inactiva.
3. **Escapado de HTML**:
   - Por defecto, `{{ $variable }}` escapa de manera segura los caracteres HTML para prevenir ataques XSS.
   - Si tienes variables generadas por el controlador que contienen etiquetas HTML con estilos (ej. `<span class="lvl-1">Proceso</span>`), **debes** usar `{!! $variable !!}` en Blade para que el navegador renderice los colores y estilos en vez de mostrar las etiquetas de texto plano crudas.
