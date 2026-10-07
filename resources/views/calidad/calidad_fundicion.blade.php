@extends ("layouts.appMenu")
@section('head')
    @php
        $perfil = Auth::user()->perfil;
        $deptName =
            $perfil == 1 || $perfil == 2
                ? 'Administración'
                : ($perfil == 3
                    ? 'Master'
                    : ($perfil == 4
                        ? 'Calidad'
                        : 'Almacén'));
    @endphp
    <title>Calidad — Dibujos de Fundición | GIS</title>
    <meta name="description"
        content="Consulta histórica de dibujos de fundición enviados a Almacén y Calidad. Vista de solo lectura." />
    @vite ([
    "resources/css/almacen_views/calidad_fundicion.css",
    "resources/js/almacen_views/calidad_fundicion.js"
    ])
@endsection
@section('background-body', 'background-image:url("' . asset('images/fondoLogin.jpg') . '")')
@section('content')
    <div class="alm-wrapper">
        {{-- ── HEADER ─────────────────────────────────────────────── --}}
        @php
            $perfil = Auth::user()->perfil;
            $deptName =
                $perfil == 1 || $perfil == 2
                    ? 'Administración'
                    : ($perfil == 3
                        ? 'Master'
                        : ($perfil == 4
                            ? 'Calidad'
                            : 'Almacén'));
            $deptIcon = $perfil == 4 || $perfil == 3 ? 'Quality.png' : 'almacen.png';
        @endphp
        <div class="alm-header">
            <div class="alm-header-icon">
                <img src="{{ asset('images/' . $deptIcon) }}" alt="{{ $deptName }}" class="cal-width-90px" />
            </div>
            <div class="alm-header-text">
                <h1>Calidad — Dibujos y Ayudas Visuales de Fundición</h1>
                <p>Consulta histórica de todos los dibujos y ayudas visuales enviados a Calidad. Registro permanente e
                    inmutable.</p>
            </div>
            <span class="alm-readonly-badge">Solo lectura</span>
        </div>
        <div class="alm-main-layout">
            {{-- ── COLUMNA IZQUIERDA (SIDEBAR) ───────────────────────── --}}
            @include('calidad.partials.sidebar_legend')
            {{-- ── COLUMNA DERECHA (CONTENIDO PRINCIPAL) ────────────────── --}}
            <main class="alm-content">
                {{-- ── STATS ───────────────────────────────────────────────── --}}
                @php
                    $total = $registros->count();
                    $activas = $registros->where('status', 'activa')->count();
                    $inactivas = $registros->where('status', 'inactiva')->count();
                @endphp
                <div class="alm-stats">
                    <div class="alm-stat-card stat-total">
                        <div class="alm-stat-icon">
                            <img src="{{ asset('images/pdf-view.png') }}" alt="Total" class="cal-width-60px" />
                        </div>
                        <div>
                            <div class="alm-stat-value">{{ $total }}</div>
                            <div class="alm-stat-label">OTs en historial</div>
                        </div>
                    </div>
                    <div class="alm-stat-card stat-activas">
                        <div class="alm-stat-icon">
                            <img src="{{ asset('images/ready.png') }}" alt="Activas" class="cal-width-60px" />
                        </div>
                        <div>
                            <div class="alm-stat-value">{{ $activas }}</div>
                            <div class="alm-stat-label">OTs activas</div>
                        </div>
                    </div>
                    <div class="alm-stat-card stat-inactivas">
                        <div class="alm-stat-icon">
                            <img src="{{ asset('images/Eliminar-Carpeta.png') }}" alt="Archivadas" class="cal-width-60px" />
                        </div>
                        <div>
                            <div class="alm-stat-value">{{ $inactivas }}</div>
                            <div class="alm-stat-label">OTs archivadas</div>
                        </div>
                    </div>
                </div>
                {{-- ── FILTROS ─────────────────────────────────────────────── --}}
                <div class="alm-filters-card">
                    <h2>Búsqueda y Filtros</h2>
                    <form method="GET" action="{{ route('calidad.fundicion.index') }}" id="alm-filter-form">
                        <div class="filters">
                            <div class="filter">
                                <select id="alm-search-ot" class="select-filter" name="ot"
                                    onchange="this.form.submit()">
                                    <option value="">Todas las OTs</option>
                                    @foreach ($listaOts as $otOption)
                                        <option value="{{ $otOption }}"
                                            {{ $busquedaOt === $otOption ? 'selected' : '' }}>
                                            {{ preg_replace('/_\d{8}_\d{6}_.*/', '', $otOption) }}
                                        </option>
                                    @endforeach
                                </select>
                                <label for="alm-search-ot">Orden de trabajo:
                                </label>
                            </div>
                            <div class="filter">
                                <input id="alm-desde" class="input-filter" type="date" name="desde"
                                    value="{{ $desde }}" onchange="this.form.submit()" />
                                <label for="alm-desde">Desde: </label>
                            </div>
                            <div class="filter">
                                <input id="alm-hasta" class="input-filter" type="date" name="hasta"
                                    value="{{ $hasta }}" onchange="this.form.submit()" />
                                <label for="alm-hasta">Hasta: </label>
                            </div>
                            @if ($busquedaOt || $desde || $hasta)
                                <button type="button" class="btns btn-clear-filters"
                                    onclick="window.location.href='{{ route('calidad.fundicion.index') }}'">
                                    Limpiar Filtros
                                </button>
                            @endif
                        </div>
                    </form>
                </div>
                @include('calidad.partials.tables.main_table')
            </main>
        </div>
    </div>
    {{-- /.alm-wrapper --}}
    {{-- ── MINI-MODAL: CONFIRMAR MODELO CON DOCUMENTOS OBLIGATORIOS ── --}}
    {{-- ── MODAL: ENVIAR ALERTA DE LIBERACION (APROBADO/RECHAZADO) ── --}}
    @include('calidad.partials.modals.send_release_alert_modal')
    {{-- ── MODAL: PRE-ORDEN PARA FABRICAR MODELOS ──────────────────── --}}
    {{-- ── MODAL: PRE-ORDEN PARA FABRICAR CASTING (DOUBLE MODAL TABS) ── --}}
    {{-- ── MODAL: FINALIZAR PROCESO DE CALIDAD (CORREO Y FECHA) ── --}}
    @include('calidad.partials.modals.finish_quality_modal')
    {{-- ── MODAL: ENVIAR PRE-ORDEN POR CORREO CON ADJUNTOS (FASE 2) ── --}}
    {{-- ── MODAL: ENVIAR ALERTA SCAR (Paso 2) ── --}}
    @include('calidad.partials.modals.send_scar_modal')
    {{-- ── MODAL: LIBERACIÓN DE MODELOS (Calidad) ──────────────────── --}}
    @include('almacen.partials.modals.model_release_modal')
    {{-- ── MODAL: SCAR (Solicitud de Acción Correctiva de Rechazo) ─── --}}
    @include('almacen.partials.modals.scar_modal')
    {{-- ── MODAL: INICIAR CASTING / GESTION VEREDICTO (Almacén) ────── --}}
    @include('almacen.partials.modals.start_casting_modal')
    {{-- ── MODAL: PRE-ORDEN DE CASTING (Almacén) ────────────────────── --}}
    @include('almacen.partials.modals.casting_preorder_modal')
    <script>
        window.almacenRoutes = {
            archivos: "{{ route('calidad.fundicion.archivos') }}",
            serve: "{{ route('calidad.fundicion.serve') }}",
            confirmarModelo: "{{ route('almacen.fundicion.confirmarModelo') }}",
            getOtData: "{{ route('almacen.fundicion.getOtData') }}",
            storePreOrden: "{{ route('almacen.fundicion.storePreOrden') }}",
            generarPreOrden: "{{ route('almacen.fundicion.storePreOrden') }}",
            sendEmailPreOrden: "{{ route('almacen.fundicion.sendEmailPreOrden') }}",
            getLiberacion: "{{ route('calidad.fundicion.getLiberacion') }}",
            submitLiberacion: "{{ route('calidad.fundicion.submitLiberacion') }}",
            generateScar: "{{ route('calidad.fundicion.generateScar') }}",
            getScar: "{{ route('calidad.fundicion.getScar') }}",
            sendScarAlert: "{{ route('calidad.fundicion.sendScarAlert') }}",
            enviarAlertaLiberacion: "{{ route('calidad.fundicion.enviarAlertaLiberacion') }}",
            deleteFile: "{{ route('calidad.fundicion.deleteFile') }}",
            iniciarCasting: "{{ route('almacen.fundicion.iniciarCasting') }}",
            procesarRechazos: "{{ route('almacen.fundicion.procesarRechazos') }}",
            confirmarRecepcionRechazo: "{{ route('almacen.fundicion.confirmarRecepcionRechazo') }}"
        };
        window.almacenAppAssets = {
            liberar: "{{ asset('images/Liberar.png') }}",
            descarga: "{{ asset('images/Descarga.png') }}",
            aprobado: "{{ asset('images/Aprobado.png') }}",
            rechazado: "{{ asset('images/Rechazado.png') }}",
            recibido: "{{ asset('images/Recibido.png') }}",
            guardado: "{{ asset('images/Guardado.png') }}",
            revisando: "{{ asset('images/Revisando.png') }}",
            espera: "{{ asset('images/Espera.png') }}"
        };
    </script>
@endsection
