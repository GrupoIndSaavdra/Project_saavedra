@extends('layouts.appMenu')

@section('head')
    <title>Inicio</title>
    <link rel="icon" href="{{ url('images/lg_saavedra.png') }}?v=1">
    <!--Styles-->
    @vite(['resources/css/home.css', 'resources/js/home.js'])

@endsection
@section('background-body', 'background-image:url("' . asset($backgroundImage) . '")')
@section('content')
    <!-- Main content -->
    <div class="filter-blur">
    </div>
    <div class="intro">
        <div class="intro-text">
            @auth
                <h2 class="welcome-title">{{ $welcomeT }} {{ auth()->user()->nombre }}
                    {{ auth()->user()->a_paterno }} {{ auth()->user()->a_materno }}
                </h2>
                <p class="welcome-text">{{ $objectiveT }}</p>
            @endauth
        </div>
        <img class="intro-img" src="{{ asset('images/img-index.png') }}" alt="..." />
    </div>
    @if (auth()->user()->perfil == 2)
        <a class="btn-close-session-home" href="{{ route('logout') }}">Cerrar sesión</a>
        <div class="div-new-report">
            >
        </div>
    @endif

    <script>
        window.baseUrl = @json(url('/'));
        window.reportRoute = @json(route('processProduction'));
    </script>
@endsection