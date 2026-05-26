# Guía de Vistas (Views Skill) - Project_saavedra

Esta guía detalla cómo crear, modificar y estructurar las interfaces de usuario (Vistas) en el proyecto utilizando **Laravel Blade**.

## Ubicación y Estructura
- **Ubicación:** `resources/views/`
- **Carpetas Modulares:** Las vistas no están sueltas en la raíz, sino agrupadas por entidad o módulo (ej. `wo_views` para órdenes de trabajo, `pieces_views` para piezas, `pta_views` para PTA, etc.).

## Uso de Layouts y Secciones Blade
- Las vistas deben extender del layout principal para mantener un diseño unificado y un encabezado (header) persistente.
- El layout principal común es `layouts.appMenu`.

### Estructura base de una vista:
```blade
@extends('layouts.appMenu')

@section('head')
    <!-- Títulos y metas adicionales -->
    <title>Título de la Vista</title>
    <!-- Incluir CSS y JS específicos para la vista usando Vite -->
    @vite(['resources/css/carpeta/estilo.css', 'resources/js/carpeta/script.js'])
@endsection

@section('background-body')
    <!-- Si la vista requiere un fondo especial en el body -->
    background-color: #f4f4f4;
@endsection

@section('content')
    <!-- Contenido HTML de la vista -->
    <main class="contenedor-principal">
        <h1>Mi Vista</h1>
    </main>
@endsection
```

## Importación de Assets
- **Vite:** Es mandatorio cargar los estilos y scripts mediante `@vite([...])`.
- **Imágenes:** Siempre utilizar el helper de Blade `asset()` para las rutas de imágenes: `<img src="{{ asset('images/logo.png') }}" />`.

## Integración con JavaScript
- Para pasar variables desde el servidor (PHP/Controlador) hacia JavaScript, se sigue la convención de utilizar el objeto global `window` mediante `@json()`.
- **Mapeo de Rutas para JS:** En el layout base (`appMenu.blade.php`), existe un objeto global `window.routes` que contiene la url pre-renderizada de cada ruta de Laravel.
  ```html
  <script>
      window.routes = {
          ...(window.routes || {}),
          miRuta: @json(route('nombre.de.la.ruta'))
      };
  </script>
  ```
  Esto permite que los scripts (`.js`) llamen internamente a `window.routes.miRuta` sin quemar las URLs ni causar errores de ruteo.

## Componentes y Partials
- Si hay bloques de código repetitivos (como alertas, o modales), extráelos a vistas parciales en carpetas como `partials/` e inclúyelos con `@include('partials.mi_componente')`.
