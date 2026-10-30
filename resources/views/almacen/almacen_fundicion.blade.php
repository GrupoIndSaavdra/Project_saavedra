@extends('layouts.appMenu')

@section('head')
    @php
        $perfil = Auth::user()->perfil;
        $deptName = in_array($perfil, [1, 2, 3, 6]) ? 'Administración' : ($perfil == 4 ? 'Calidad' : 'Almacén');
    @endphp
    <title>Almacén — Dibujos de Fundición | GIS</title>
    <meta name="description"
        content="Consulta histórica de dibujos de fundición enviados a Almacén y Calidad. Vista de solo lectura.">
    @vite(['resources/css/almacen_views/almacen_fundicion.css', 'resources/js/almacen_views/almacen_fundicion.js'])
@endsection

@section('background-body', 'background-image:url("' . asset('images/fondos/fondoLogin.jpg') . '")')

@section('content')
    <div class="alm-wrapper">
        @php
            $perfil = Auth::user()->perfil;
            $deptName = in_array($perfil, [1, 2, 3, 6]) ? 'Administración' : ($perfil == 4 ? 'Calidad' : 'Almacén');
            $deptIcon = $perfil == 4 ? 'Color_Icons/Generales/Quality.png' : 'Color_Icons/Generales/almacen.png';
        @endphp

        <div class="alm-header">
            <div class="alm-header-icon">
                <img src="{{ asset('images/' . $deptIcon) }}" alt="{{ $deptName }}" class="alm-width-90px">
            </div>
            <div class="alm-header-text">
                <h1>Almacén — Dibujos y Ayudas Visuales de Fundición</h1>
                <p>Consulta histórica de todos los dibujos y ayudas visuales enviados a Almacén. Registro permanente e inmutable.</p>
            </div>
            <span class="alm-readonly-badge">Solo lectura</span>
        </div>

        <div class="alm-main-layout">
            @include('almacen.partials.sidebar_legend')

            {{-- ── COLUMNA DERECHA (CONTENIDO PRINCIPAL) ────────────────── --}}
            <main class="alm-content">
                {{-- ── STATS ───────────────────────────────────────────────── --}}
                @php
                    $total = $totalCount ?? $registros->count();
                    $activas = $activasCount ?? $registros->where('status', 'activa')->count();
                    $inactivas = $inactivasCount ?? $registros->where('status', 'inactiva')->count();
                @endphp

                <div class="alm-stats">
                    <div class="alm-stat-card stat-total">
                        <div class="alm-stat-icon">
                            <img src="{{ asset('images/Color_Icons/Generales/pdf-view.png') }}" alt="Total"
                                class="alm-w-60">
                        </div>
                        <div>
                            <div class="alm-stat-value">{{ $total }}</div>
                            <div class="alm-stat-label">OTs en historial</div>
                        </div>
                    </div>

                    <div class="alm-stat-card stat-activas">
                        <div class="alm-stat-icon">
                            <img src="{{ asset('images/Color_Icons/Generales/ready.png') }}" alt="Activas"
                                class="alm-w-60">
                        </div>
                        <div>
                            <div class="alm-stat-value">{{ $activas }}</div>
                            <div class="alm-stat-label">OTs activas</div>
                        </div>
                    </div>

                    <div class="alm-stat-card stat-inactivas">
                        <div class="alm-stat-icon">
                            <img src="{{ asset('images/Color_Icons/Generales/Eliminar-Carpeta.png') }}" alt="Archivadas"
                                class="alm-w-60">
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
                    <form method="GET" action="{{ route('almacen.fundicion.index') }}" id="alm-filter-form">
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
                                <label for="alm-search-ot">Orden de trabajo: </label>
                            </div>

                            <div class="filter">
                                <select id="alm-filter-status" class="select-filter" name="estado"
                                    onchange="this.form.submit()">
                                    <option value="activa" {{ ($estado ?? 'activa') === 'activa' ? 'selected' : '' }}>
                                        Activas (En proceso)</option>
                                    <option value="inactiva" {{ ($estado ?? '') === 'inactiva' ? 'selected' : '' }}>
                                        Archivadas (Inactivas)</option>
                                    <option value="todas" {{ ($estado ?? '') === 'todas' ? 'selected' : '' }}>Todas
                                    </option>
                                </select>
                                <label for="alm-filter-status">Estado: </label>
                            </div>

                            <div class="filter">
                                <input id="alm-desde" class="input-filter" type="date" name="desde"
                                    value="{{ $desde }}" onchange="this.form.submit()" data-title="Fecha Desde">
                                <label for="alm-desde">Desde: </label>
                            </div>

                            <div class="filter">
                                <input id="alm-hasta" class="input-filter" type="date" name="hasta"
                                    value="{{ $hasta }}" onchange="this.form.submit()" data-title="Fecha Hasta">
                                <label for="alm-hasta">Hasta: </label>
                            </div>

                            @if ($busquedaOt || $desde || $hasta || ($estado ?? 'activa') !== 'activa')
                                <button type="button" class="btns btn-clear-filters"
                                    onclick="window.location.href='{{ route('almacen.fundicion.index') }}'">
                                    Limpiar Filtros
                                </button>
                            @endif
                        </div>
                    </form>
                </div>

                @include('almacen.partials.tables.main_table')
            </main>
        </div>
    </div>
    {{-- /.alm-wrapper --}}

    {{-- ── MODALES ─────────────────────────────────────────────────── --}}
    @include('almacen.partials.modals.confirm_modal')
    @include('almacen.partials.modals.preorder_modal')
    @include('almacen.partials.modals.casting_preorder_modal')
    @include('almacen.partials.modals.send_preorder_modal')
    @include('almacen.partials.modals.start_casting_modal')
    @include('almacen.partials.modals.review_changes_modal')
    @include('almacen.partials.modals.new_confirm_modals')

    <script>
        window.almacenRoutes = {
            archivos: "{{ route('almacen.fundicion.archivos') }}",
            serve: "{{ route('almacen.fundicion.serve') }}",
            confirmarModelo: "{{ route('almacen.fundicion.confirmarModelo') }}",
            getOtData: "{{ route('almacen.fundicion.getOtData') }}",
            pendingPreOrdenes: "{{ route('almacen.fundicion.getPendingPreOrdenes') }}",
            storePreOrden: "{{ route('almacen.fundicion.storePreOrden') }}",
            generarPreOrden: "{{ route('almacen.fundicion.storePreOrden') }}",
            sendEmailPreOrden: "{{ route('almacen.fundicion.sendEmailPreOrden') }}",
            getLiberacion: "{{ route('calidad.fundicion.getLiberacion') }}",
            submitLiberacion: "{{ route('calidad.fundicion.submitLiberacion') }}",
            generateScar: "{{ route('calidad.fundicion.generateScar') }}",
            getScar: "{{ route('calidad.fundicion.getScar') }}",
            sendScarAlert: "{{ route('calidad.fundicion.sendScarAlert') }}",
            enviarAlertaLiberacion: "{{ route('calidad.fundicion.enviarAlertaLiberacion') }}",
            deleteFile: "{{ route('almacen.fundicion.deleteFile') }}",
            iniciarCasting: "{{ route('almacen.fundicion.iniciarCasting') }}",
            procesarRechazos: "{{ route('almacen.fundicion.procesarRechazos') }}",
            confirmarRecepcionRechazo: "{{ route('almacen.fundicion.confirmarRecepcionRechazo') }}",
            pendingComparison: "{{ route('almacen.fundicion.pending_comparison') }}",
            resolveChanges: "{{ route('almacen.fundicion.resolve_changes') }}"
        };

        window.almacenAppAssets = {
            liberar: "{{ asset('images/Color_Icons/Generales/Liberar.png') }}",
            descarga: "{{ asset('images/Color_Icons/Generales/Descarga.png') }}",
            recibido: "{{ asset('images/Color_Icons/Generales/Recibido.png') }}",
            aprobado: "{{ asset('images/Color_Icons/Generales/Aprobado.png') }}",
            rechazado: "{{ asset('images/Color_Icons/Generales/Rechazado.png') }}",
            guardado: "{{ asset('images/Color_Icons/Generales/Guardado.png') }}",
            revisando: "{{ asset('images/Color_Icons/Generales/Revisando.png') }}",
            espera: "{{ asset('images/Color_Icons/Generales/Espera.png') }}"
        };
    </script>
@endsection
