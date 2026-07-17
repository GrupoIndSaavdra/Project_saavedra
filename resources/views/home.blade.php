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
    @if (auth()->user()->perfil == 2)
        {{-- Diseño de pantalla dividida para Operadores --}}
        <div class="operator-home-container">
            {{-- Panel izquierdo: Prioridades --}}
            <div class="home-priorities-panel">
                <div class="home-priorities-header">
                    <img src="{{ asset('images/priorizar.png') }}" class="home-priorities-icon" alt="">
                    <h2 class="home-priorities-title">Secuencia de Prioridades de Fabricación</h2>
                </div>
                
                <div class="home-priorities-list-container">
                    @if (empty($otPriorities))
                        <div class="home-priorities-empty">
                            No hay órdenes de trabajo en progreso.
                        </div>
                    @else
                        <ul class="home-priorities-list">
                            @foreach ($otPriorities as $index => $ot)
                                @php
                                    $priorityNum = $index + 1;
                                    $isGold = $priorityNum <= 5;
                                    
                                    if ($priorityNum === 1) $priorityImgPath = asset('images/uno.png');
                                    elseif ($priorityNum === 2) $priorityImgPath = asset('images/dos.png');
                                    elseif ($priorityNum === 3) $priorityImgPath = asset('images/tres.png');
                                    elseif ($priorityNum === 4) $priorityImgPath = asset('images/cuatro.png');
                                    elseif ($priorityNum === 5) $priorityImgPath = asset('images/cinco.png');
                                    else $priorityImgPath = asset('images/plata.png');
                                    
                                    $glowClass = $isGold ? 'glow-gold' : 'glow-silver';
                                @endphp
                                <li class="home-priority-card {{ $glowClass }}">
                                    <div class="home-priority-badge">
                                        <span class="home-priority-number">{{ $priorityNum }}</span>
                                        <img src="{{ $priorityImgPath }}" class="home-priority-medal" alt="">
                                    </div>
                                    <div class="home-priority-body">
                                        <div class="home-priority-meta">
                                            <span class="home-priority-ot">OT {{ $ot['ot_id'] }}</span>
                                            <span class="home-priority-molding" title="{{ $ot['moldura'] }}">{{ $ot['moldura'] }}</span>
                                        </div>
                                        <div class="home-priority-classes">
                                            @foreach ($ot['clases'] as $clase)
                                                <span class="home-priority-class-badge">{{ $clase }}</span>
                                            @endforeach
                                        </div>
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            </div>

            {{-- Panel derecho: Mensaje de bienvenida y cerrar sesión --}}
            <div class="operator-welcome-panel">
                <div class="intro-text-operator">
                    @auth
                        <h2 class="welcome-title">{{ $welcomeT }}</h2>
                        <h1 class="operator-name">{{ auth()->user()->nombre }} {{ auth()->user()->a_paterno }} {{ auth()->user()->a_materno }}</h1>
                        <p class="welcome-text">{{ $objectiveT }}</p>
                    @endauth
                </div>
                <a class="btn-close-session-operator" href="{{ route('logout') }}">Cerrar sesión</a>
            </div>
        </div>
    @else
        {{-- Diseño original para administradores, control de calidad, etc. --}}
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
    @endif

    @if (auth()->user()->perfil == 2)
        <div class="div-new-report">
            >
        </div>
    @endif

    <script>
        window.baseUrl = @json(url('/'));
        window.reportRoute = @json(route('processProduction'));
    </script>
@endsection