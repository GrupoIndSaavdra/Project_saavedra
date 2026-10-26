<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- SweetAlert2 Global GIS -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    @vite(['resources/css/layouts/appMenu.css', 'resources/js/layouts/appMenu.js', 'resources/js/layouts/productivity.js', 'resources/css/layouts/partials/messages.css', 'resources/js/layouts/partials/messages.js'])

    <link rel="icon" type="image/x-icon" href="{{ asset('images/logos/favicon.ico') }}?v=3">

    @yield('head')
    <style>
        :root {
            --triangulo-abajo: url('{{ asset('images/iconos/triangulo_abajo.png') }}');
        }
    </style>
    <script>
        window.baseUrl = "{{ url('/') }}";
        window.UI_ICONS = @json(icon_map());
        window.getIcon = function(key) {
            if (!key) return '';
            if (window.UI_ICONS && window.UI_ICONS[key]) {
                return window.UI_ICONS[key];
            }
            if (key.startsWith('http://') || key.startsWith('https://') || key.startsWith('/')) {
                return key;
            }
            const slash = window.baseUrl.endsWith('/') ? '' : '/';
            return `${window.baseUrl}${slash}${key.startsWith('images/') ? key : 'images/' + key}`;
        };
        window.loading = window.getIcon('loading');
        window.liberar = window.getIcon('liberar');
        window.rechazar = window.getIcon('rechazar');
        window.ojito = window.getIcon('ojito');
    </script>
</head>

<body
    style="@yield('background-body'); min-height: 100vh; background-size: cover; background-position: center; background-repeat: no-repeat; background-attachment: fixed;">
    <header>
        @auth
            @hasSection('header-left')
                @yield('header-left')
            @else
                <!--Menu-->
                <button class="open-menu" aria-label="Menú principal" aria-expanded="false">
                    <img class="icon" src="{{ asset('images/iconos/icono.png') }}" alt="Icono Menú">
                </button>
            @endif
            <div class="filter-opacity">
                <nav class="nav" id="nav">
                    <!-- Cabecera del Menú Lateral -->
                    <div class="nav-drawer-header">
                        <div class="nav-drawer-brand">
                            <img src="{{ asset('images/logos/lg_saavedra.png') }}" alt="GIS Logo" class="nav-drawer-logo">
                            <div class="nav-drawer-titles">
                                <span class="nav-drawer-title">PORTAL SAAVEDRA</span>
                                <span class="nav-drawer-subtitle">Navegación del Sistema</span>
                            </div>
                        </div>
                    </div>

                    <ul class="nav-list"></ul>

                    <!-- Pie del Menú con Cerrar Sesión -->
                    <div class="nav-drawer-footer">
                        @php
                            // Si tiene sesión temporal Y NO ES perfil 1 (Admin/Sistemas) ni 6 (Gerencia) ni 8 (Calidad/Ingeniería), se recorta su menú
                            $isPtaTemp =
                                session('pta_temp_auth') && !in_array(auth()->user()->perfil, ['1', '3', '6', '8']);
                        @endphp

                        @if ($isPtaTemp)
                            <a class="btn-close-session" href="{{ route('pta.close_temp_session') }}">
                                <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                    stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4" />
                                    <polyline points="16 17 21 12 16 7" />
                                    <line x1="21" y1="12" x2="9" y2="12" />
                                </svg>
                                <span>Regresar al Reporte</span>
                            </a>
                        @else
                            <a class="btn-close-session" href="{{ route('logout') }}">
                                <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                    stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4" />
                                    <polyline points="16 17 21 12 16 7" />
                                    <line x1="21" y1="12" x2="9" y2="12" />
                                </svg>
                                <span>Cerrar sesión</span>
                            </a>
                        @endif
                    </div>
                    <input type="hidden" value="{{ $isPtaTemp ? 'pta_temp' : auth()->user()->perfil }}" id="profile">
                </nav>
            </div>

            <!--Texto central-->
            <span class="text-header">GRUPO INDUSTRIAL SAAVEDRA</span>
            <!--Logo Saavedra-->
            <img src="{!! asset('images/logos/lg_saavedra.png') !!}" alt="logo" class="logo">
        @endauth
    </header>
    @yield('content')
</body>

<!--Creacion de rutas de laravel para pasarlas a JS-->
<script>
    window.routes = {
        ...(window.routes || {}),
        home: @json(route('home')),
        createMolding: @json(route('createMolding')),
        editMolding: @json(route('editMolding')),
        manageWO: @json(route('manageWO')),
        createMasterWO: @json(route('createMasterWO')),
        masterPriorities: @json(route('master.priorities')),
        show_panelWO: @json(route('show_panelWO')),
        users: @json(route('users')), // PENDING
        usersOrganigrama: @json(route('users.organigrama')),
        createUser: @json(route('createUser')),
        recoverPassword: @json(route('recoverPassword')),
        cNominals: @json(route('cNominals')),
        piecesInProgress: @json(route('showPiecesInProgress')),
        priorityManager: @json(route('showPriorityManager')),
        showPiecesReport_view: @json(route('showPiecesReport_view')),
        showReleasePieces_view: @json(route('showReleasePieces_view')),
        showTimes: @json(route('showTimes')),
        productionData: @json(route('productionData')),
        panelProgreso: @json(route('panelProgreso')),
        machinesOccupied: @json(route('machinesOccupied')),
        piecesOccupied: @json(route('piecesOccupied')),
        processProduction: @json(route('processProduction')),
        logout: @json(route('logout')),
        'soldadura.generarQRLote': @json(route('soldadura.generarQRLote')),
        'soldadura.generarQRIndividual': @json(route('soldadura.generarQRIndividual')),
        'soldadura.recepcionPlanta': @json(route('soldadura.recepcionPlanta')),
        'soldadura.liberarQRPlanta': @json(route('soldadura.liberarQRPlanta')),
        'soldadura.regenerarQR': @json(route('soldadura.regenerarQR')),
        'pta.analysis': @json(route('pta.analysis')),
        'pta.segunda_pasada': @json(route('pta.segunda_pasada')),
        'pta.results.current': @json(session('pta_temp_ot_id') ? route('pta.results', ['ot_id' => session('pta_temp_ot_id')]) : '#'),
        'reportes.reenvio': @json(route('reportes.reenvio')),
        'reportes.pta': @json(route('reportes.pta')),
        'dibujos.manage': @json(route('dibujos.manage')),
        'programas.manage': @json(route('programas.manage')),
        'fundicion.manage': @json(route('fundicion.manage')),
        'manuales.manage': @json(route('manuales.manage')),
        'ayudas.manage': @json(route('ayudas.manage')),
        'ayudas_fundicion.manage': @json(route('ayudas_fundicion.manage')),
        'almacen.fundicion.index': @json(route('almacen.fundicion.index')),
        'calidad.fundicion.index': @json(route('calidad.fundicion.index')),
        'calidad.maquinados.index': @json(route('calidad.maquinados.index')),
        'herramientas.tecamac.index': @json(route('herramientas.tecamac.index')),
        'calidad.r_interno_salidas.index': @json(route('calidad.r_interno_salidas.index')),
        'calidad.inspeccion_visual.index': @json(route('calidad.inspeccion_visual.index')),
        'calidad.inspeccion_dimensional.index': @json(route('calidad.inspeccion_dimensional.index')),
        systemLogsReport: @json(route('systemLogsReport')),
        adminLogsReport: @json(route('systemLogsReport', ['admin_only' => 1]))
    };
    window.FundicionCatalog = @json(array_values(array_unique(\App\Services\FundicionPaths::getStandardMap())));
    window.FundicionWelding = @json(config('global_classes.welding_classes', []));
    window.FundicionProcess = @json(config('global_classes.process_classes', []));
    window.FundicionProcessExcluded = @json(config('global_classes.process_excluded_classes', []));
    window.FundicionSearchableAliases = @json(config('global_classes.searchable_aliases', []));
    window.FundicionClassPrefixes = @json(config('global_classes.class_prefixes', []));
    window.GlobalClasses = @json(config('global_classes.clases', []));
    window.getSearchableClassesJS = function() {
        var globalClasses = window.GlobalClasses || {};
        var processClasses = window.FundicionProcess || [];
        var searchableAliases = window.FundicionSearchableAliases || [];
        var keywords = [];

        searchableAliases.forEach(function(sa) {
            if (sa) keywords.push(String(sa).toLowerCase().trim());
        });
        processClasses.forEach(function(pc) {
            if (pc) keywords.push(String(pc).toLowerCase().trim());
        });
        Object.values(globalClasses).forEach(function(c) {
            if (c) keywords.push(String(c).toLowerCase().trim());
        });

        var unique = Array.from(new Set(keywords.filter(Boolean)));
        return unique.sort(function(a, b) {
            return b.length - a.length;
        });
    };
    window.FundicionStandardMap = @json(\App\Services\FundicionPaths::getStandardMap());
    window.normalizeClassJS = function(clase) {
        if (!clase) return "GENERAL";
        var s = String(clase).toUpperCase().trim().replace(/^MODELO\s+/i, '');
        var sinNum = s.replace(/^\d+\s*-\s*/, '').trim();
        var map = window.FundicionStandardMap || {};

        if (map[s]) return map[s];
        if (map[sinNum]) return map[sinNum];

        var sinTilde = sinNum.normalize("NFD").replace(/[\u0300-\u036f]/g, "");
        if (map[sinTilde]) return map[sinTilde];

        return s;
    };
    window.getClassPrefixJS = function(tipoModelo, claseNombre, esTempladera) {
        var prefixes = window.FundicionClassPrefixes || {};
        var tLow = (tipoModelo || '').toLowerCase().trim();
        var cLow = (claseNombre || '').toLowerCase().trim();
        var isTempladera = Boolean(esTempladera || tLow.includes('templadera') || cLow.includes('templadera'));

        if (isTempladera) {
            if (tLow.includes('obturador') || cLow.includes('obturador')) return prefixes['templadera obturador'] ||
                'TO';
            if (tLow.includes('molde') || cLow.includes('molde')) return prefixes['templadera molde'] || 'TM';
            if (tLow.includes('fondo') || cLow.includes('fondo')) return prefixes['templadera fondo'] || 'TF';
            if (tLow.includes('bombillo') || cLow.includes('bombillo')) return prefixes['templadera bombillo'] ||
                'TB';
            return prefixes['templadera'] || 'T';
        }

        var keys = Object.keys(prefixes).sort(function(a, b) {
            return b.length - a.length;
        });
        for (var i = 0; i < keys.length; i++) {
            var k = keys[i];
            if (k.startsWith('templadera')) continue;
            if (tLow.includes(k) || cLow.includes(k)) {
                return prefixes[k];
            }
        }

        var fallbackStr = claseNombre || tipoModelo || 'F';
        return fallbackStr.charAt(0).toUpperCase();
    };
</script>



@isset($pieces_Released)
    <script>
        window.pieces_Released = ({{ auth()->user()->perfil }} == 4 || ({{ auth()->user()->perfil }} == 3 &&
            "{{ request('sec') }}" === "calidad")) ? @json($pieces_Released) : [];
        window.info_Pieces = ({{ auth()->user()->perfil }} == 4 || ({{ auth()->user()->perfil }} == 3 &&
            "{{ request('sec') }}" === "calidad")) ? @json($info_Pieces) : [];
    </script>
@endisset

</html>
