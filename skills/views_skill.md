# 👁️ Guía de Vistas Blade (Views Skill) - Máximo Nivel

`Project_saavedra` usa Blade como motor de renderizado. Las vistas deben ser ligeras, modulares y seguras. 

## 1. Directivas de Seguridad (CSRF y Method Spofing)
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
La inclusión de scripts es estricta. Nunca incluyas scripts en el medio del documento. Usa `@section('head')` para assets estáticos Vite.

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
    // Se recomienda poner esto al final del content
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
