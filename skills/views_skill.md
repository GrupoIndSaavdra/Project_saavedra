# 👁️ Guía de Vistas Blade (Views Skill) - Máximo Nivel

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
