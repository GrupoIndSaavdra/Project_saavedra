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
            $deptIcon = $perfil == 4 ? 'Quality.png' : 'almacen.png';
        @endphp

        <div class="alm-header">
            <div class="alm-header-icon">
                <img src="{{ asset('images/' . $deptIcon) }}" alt="{{ $deptName }}" class="alm-width-90px">
            </div>
            <div class="alm-header-text">
                <h1>Almacén — Dibujos y Ayudas Visuales de Fundición</h1>

                <p>Consulta histórica de todos los dibujos y ayudas visuales enviados a Almacén. Registro
                    permanente e inmutable.</p>
            </div>
            <span class="alm-readonly-badge">Solo lectura</span>
        </div>

        <div class="alm-main-layout">

            @include('almacen.partials.sidebar_legend')

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
                            <img src="{{ asset('images/iconos/pdf-view.png') }}" alt="Total" class="alm-w-60">
                        </div>
                        <div>
                            <div class="alm-stat-value">{{ $total }}</div>
                            <div class="alm-stat-label">OTs en historial</div>
                        </div>
                    </div>
                    <div class="alm-stat-card stat-activas">
                        <div class="alm-stat-icon">
                            <img src="{{ asset('images/iconos/ready.png') }}" alt="Activas" class="alm-w-60">
                        </div>
                        <div>
                            <div class="alm-stat-value">{{ $activas }}</div>
                            <div class="alm-stat-label">OTs activas</div>
                        </div>
                    </div>
                    <div class="alm-stat-card stat-inactivas">
                        <div class="alm-stat-icon">
                            <img src="{{ asset('images/iconos/Eliminar-Carpeta.png') }}" alt="Archivadas" class="alm-w-60">
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
                                <select id="alm-search-ot" class="select-filter" name="ot" onchange="this.form.submit()">
                                    <option value="">Todas las OTs</option>
                                    @foreach ($listaOts as $otOption)
                                        <option value="{{ $otOption }}" {{ $busquedaOt === $otOption ? 'selected' : '' }}>
                                            {{ preg_replace('/_\d{8}_\d{6}_.*/', '', $otOption) }}
                                        </option>
                                    @endforeach
                                </select>
                                <label for="alm-search-ot">Orden de trabajo: </label>
                            </div>

                            <div class="filter">
                                <input id="alm-desde" class="input-filter" type="date" name="desde" value="{{ $desde }}"
                                    onchange="this.form.submit()">
                                <label for="alm-desde">Desde: </label>
                            </div>

                            <div class="filter">
                                <input id="alm-hasta" class="input-filter" type="date" name="hasta" value="{{ $hasta }}"
                                    onchange="this.form.submit()">
                                <label for="alm-hasta">Hasta: </label>
                            </div>

                            @if ($busquedaOt || $desde || $hasta)
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
            liberar: "{{ asset('images/iconos/Liberar.png') }}",
            descarga: "{{ asset('images/iconos/Descarga.png') }}",
            recibido: "{{ asset('images/iconos/Recibido.png') }}",
            aprobado: "{{ asset('images/iconos/Aprobado.png') }}",
            rechazado: "{{ asset('images/iconos/Rechazado.png') }}",
            guardado: "{{ asset('images/iconos/Guardado.png') }}",
            revisando: "{{ asset('images/iconos/Revisando.png') }}",
            espera: "{{ asset('images/iconos/Espera.png') }}",
        };

        // Contenedores organizados estáticamente por etapa
    </script>

@endsection