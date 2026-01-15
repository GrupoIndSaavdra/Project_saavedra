<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @vite(['resources/css/layouts/appMenu.css', 'resources/js/layouts/appMenu.js'])
    @yield('head')
    <script>
        window.loading = "{{ asset('images/loading.gif') }}"
        window.liberar = "{{ asset('images/Liberar.png') }}"
        window.rechazar = "{{ asset('images/Rechazar.png') }}"
        window.ojito = "{{ asset('images/ojito.png') }}"
        window.baseUrl = "{{ url('/') }}";
    </script>
</head>

<body style="@yield('background-body')">
    <header>
        @auth
            <!--Menu-->
            <button class="open-menu">
                <img class="icon" src="{{ asset('images/icono.png') }}">
            </button>
            <div class="filter-opacity">
                <nav class="nav" id="nav">
                    <ul class="nav-list"></ul>
                    <a class="btn-close-session" href="{{ route('logout') }}">Cerrar sesión</a>
                    <input type="hidden" value="{{ auth()->user()->perfil }}" id="profile">
                </nav>
            </div>

            <!--Texto central-->
            <span class="text-header">GRUPO INDUSTRIAL SAAVEDRA</span>
            <!--Logo Saavedra-->
            <img src="{!! asset('images/lg_saavedra.png') !!}" alt="logo" class="logo">
        @endauth
    </header>
    @yield('content')
</body>

<!--Creacion de rutas de laravel para pasarlas a JS-->
<script>
    window.routes = {
        home: @json(route('home')),
        createMolding: @json(route('createMolding')),
        editMolding: @json(route('editMolding')),
        manageWO: @json(route('manageWO')),
        show_panelWO: @json(route('show_panelWO')),
        users: @json(route('users')), // PENDING
        createUser: @json(route('createUser')),
        recoverPassword: @json(route('recoverPassword')),
        cNominals: @json(route('cNominals')),
        piecesInProgress: @json(route('showPiecesInProgress')),
        showPiecesReport_view: @json(route('showPiecesReport_view')),
        showReleasePieces_view: @json(route('showReleasePieces_view')),
        showTimes: @json(route('showTimes')),
        productionData: @json(route('productionData')),
        panelProgreso: @json(route('panelProgreso')),
        machinesOccupied: @json(route('machinesOccupied')),
        logout: @json(route('logout')),
        'soldadura.generarQRLote': @json(route('soldadura.generarQRLote')),
        'soldadura.generarQRIndividual': @json(route('soldadura.generarQRIndividual')),
        'soldadura.recepcionPlanta': @json(route('soldadura.recepcionPlanta')),
        'soldadura.liberarQRPlanta': @json(route('soldadura.liberarQRPlanta')),
    };
</script>
@isset($pieces_Released)
    <script>
        window.pieces_Released = @json(auth()->user()->perfil) == 4 ? @json($pieces_Released) : [];
        window.info_Pieces = @json(auth()->user()->perfil) == 4 ? @json($info_Pieces) : [];
    </script>
@endisset

</html>