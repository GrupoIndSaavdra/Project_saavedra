<!doctype html>
<html lang="es">

<head>
    <title>Login - Grupo Industrial Saavedra</title>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="icon" href="{{ url('images/lg_saavedra.png') }}?v=1">
    <link rel="icon" type="image/png" href="{{ asset('images/lg_saavedra.png') }}">

    <!-- Bootstrap CSS v5.2.1 -->
    <link rel="stylesheet" href="{{ asset('bootstrap/css/bootstrap.min.css') }}">

    <!-- Estilos y Scripts Modularizados -->
    @vite(['resources/css/auth/login.css', 'resources/js/auth/login.js', 'resources/js/layouts/partials/messages.js'])

    <script>
        window.baseUrl = "{{ url('/') }}";
        window.loginCarouselImages = @json($tecamacImages ?? []);
        window.loginInitialIndex = {{ $randomInitialIndex ?? 0 }};
    </script>
</head>

<body background="{{ asset('images/fondoLogin.jpg') }}">
    <!-- Section: Design Block -->
    <section class="text-center text-lg-start">
        <!-- Container Principal -->
        <div class="container py-4">
            <div class="row g-0 align-items-center">
                {{-- Componente Carrusel Fotográfico Planta Tecámac --}}
                @include('auth.partials.carousel')

                {{-- Componente Formulario de Inicio de Sesión --}}
                @include('auth.partials.loginForm')
            </div>
        </div>
    </section>

    <!-- Bootstrap JavaScript Libraries -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"
        integrity="sha384-oBqDVmMz9ATKxIep9tiCxS/Z9fNfEXiDAYTujMAeBAsjFuCZSmKbSSUnQlmh/jp3" crossorigin="anonymous">
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.1/dist/js/bootstrap.min.js"
        integrity="sha384-7VPbUDkoPSGFnVtYi0QogXtr74QeVeeIs99Qfg5YCF+TidwNdjvaKZX19NZ/e6oz" crossorigin="anonymous">
    </script>
</body>

</html>
