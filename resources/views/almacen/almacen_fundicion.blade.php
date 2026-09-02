@extends('layouts.appMenu')

@section('head')
    @php
        $perfil = Auth::user()->perfil;
        $deptName = in_array($perfil, [1, 2, 3]) ? 'Administración' : ($perfil == 4 ? 'Calidad' : 'Almacén');
    @endphp
    <title>Almacén — Dibujos de Fundición | GIS</title>
    <meta name="description"
        content="Consulta histórica de dibujos de fundición enviados a Almacén y Calidad. Vista de solo lectura.">
    @vite(['resources/css/almacen_views/almacen_fundicion.css', 'resources/js/almacen_views/almacen_fundicion.js'])
@endsection

@section('background-body', 'background-image:url("' . asset('images/fondoLogin.jpg') . '")')

@section('content')

    <div class="alm-wrapper">
        @php
            $perfil = Auth::user()->perfil;
            $deptName = in_array($perfil, [1, 2, 3]) ? 'Administración' : ($perfil == 4 ? 'Calidad' : 'Almacén');
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

            <aside class="alm-sidebar">


                <div
                    class="alm-filters-card alm-margin-bottom-2em alm-background-rgba-255-255-255-0-95 alm-backdrop-filter-blur-10px alm-border-radius-12px alm-box-shadow-0-4px-15px-rgba-0-0-0-0-08 alm-position-relative alm-padding-1-6em">
                    <div
                        class="alm-display-flex alm-align-items-center alm-gap-12px alm-margin-bottom-16px alm-border-bottom-2px-solid-e2e8f0 alm-padding-bottom-12px">
                        <img src="{{ asset('images/Quality.png') }}" alt="Leyenda"
                            class="alm-width-30px alm-height-30px alm-object-fit-contain">
                        <h2 class="alm-margin-0 alm-font-size-1-30rem alm-color-0f172a alm-font-weight-700">Guía de Estados
                            de Modelo</h2>
                    </div>

                    <h3
                        class="alm-font-size-0-92rem alm-color-475569 alm-font-weight-700 alm-margin-0-0-10px-0 alm-border-left-4px-solid-94a3b8 alm-padding-left-8px">
                        Estados de Transición</h3>
                    <div
                        class="legend-grid-compact alm-display-flex alm-flex-wrap-wrap alm-justify-content-center alm-gap-8px alm-margin-bottom-20px">

                        <div
                            class="legend-compact-item alm-width-calc-33-33-6pxpct alm-display-flex alm-flex-direction-column alm-align-items-center alm-padding-10px-6px alm-background-f8fafc alm-border-1-5px-solid-e2e8f0 alm-border-radius-8px alm-min-height-102px alm-justify-content-center">
                            <span
                                class="alm-display-flex alm-background-f1f5f9 alm-border-2px-solid-cbd5e1 alm-align-items-center alm-justify-content-center alm-width-54px alm-height-54px alm-border-radius-50pct alm-box-shadow-0-2px-4px-rgba-0-0-0-0-04 alm-flex-shrink-0">
                                <img src="{{ asset('images/Recibido.png') }}" class="alm-legend-icon">
                            </span>
                            <span
                                class="alm-font-size-0-80rem alm-color-475569 alm-font-weight-700 alm-margin-top-7px alm-text-align-center alm-line-height-1-1">Nuevo</span>
                        </div>

                        @if (Auth::user()->perfil != 4)
                            <div
                                class="legend-compact-item alm-width-calc-33-33-6pxpct alm-display-flex alm-flex-direction-column alm-align-items-center alm-padding-10px-6px alm-background-f8fafc alm-border-1-5px-solid-e2e8f0 alm-border-radius-8px alm-min-height-102px alm-justify-content-center">
                                <span
                                    class="alm-display-flex alm-background-e0e7ff alm-border-2px-solid-818cf8 alm-align-items-center alm-justify-content-center alm-width-54px alm-height-54px alm-border-radius-50pct alm-box-shadow-0-2px-4px-rgba-0-0-0-0-04 alm-flex-shrink-0">
                                    <img src="{{ asset('images/enviando.png') }}" class="alm-legend-icon">
                                </span>
                                <span
                                    class="alm-font-size-0-80rem alm-color-4f46e5 alm-font-weight-700 alm-margin-top-7px alm-text-align-center alm-line-height-1-1">Correo
                                    Enviado</span>
                            </div>
                        @endif

                        <div
                            class="legend-compact-item alm-width-calc-33-33-6pxpct alm-display-flex alm-flex-direction-column alm-align-items-center alm-padding-10px-6px alm-background-f8fafc alm-border-1-5px-solid-e2e8f0 alm-border-radius-8px alm-min-height-102px alm-justify-content-center">
                            <span
                                class="alm-display-flex alm-background-fffbeb alm-border-2px-solid-f59e0b alm-align-items-center alm-justify-content-center alm-width-54px alm-height-54px alm-border-radius-50pct alm-box-shadow-0-2px-4px-rgba-0-0-0-0-04 alm-flex-shrink-0">
                                <img src="{{ asset('images/Revisando.png') }}" class="alm-legend-icon">
                            </span>
                            <span
                                class="alm-font-size-0-80rem alm-color-b45309 alm-font-weight-700 alm-margin-top-7px alm-text-align-center alm-line-height-1-1">En
                                Revisión</span>
                        </div>
                    </div>

                    <h3
                        class="alm-font-size-0-92rem alm-color-0f172a alm-font-weight-700 alm-margin-0-0-10px-0 alm-border-left-4px-solid-3b82f6 alm-padding-left-8px">
                        Estados Prioritarios</h3>
                    <div
                        class="legend-grid-compact alm-display-flex alm-flex-wrap-wrap alm-justify-content-center alm-gap-8px">

                        <div
                            class="legend-compact-item alm-width-calc-33-33-6pxpct alm-display-flex alm-flex-direction-column alm-align-items-center alm-padding-10px-6px alm-background-f8fafc alm-border-1-5px-solid-e2e8f0 alm-border-radius-8px alm-min-height-102px alm-justify-content-center">
                            <span
                                class="alm-display-flex alm-background-eff6ff alm-border-2px-solid-60a5fa alm-align-items-center alm-justify-content-center alm-width-54px alm-height-54px alm-border-radius-50pct alm-box-shadow-0-2px-4px-rgba-0-0-0-0-04 alm-flex-shrink-0">
                                <img src="{{ asset('images/pdf-view.png') }}" class="alm-legend-icon">
                            </span>
                            <span
                                class="alm-font-size-0-80rem alm-color-2563eb alm-font-weight-700 alm-margin-top-7px alm-text-align-center alm-line-height-1-1">Pre-Orden</span>
                        </div>

                        <div
                            class="legend-compact-item alm-width-calc-33-33-6pxpct alm-display-flex alm-flex-direction-column alm-align-items-center alm-padding-10px-6px alm-background-f8fafc alm-border-1-5px-solid-e2e8f0 alm-border-radius-8px alm-min-height-102px alm-justify-content-center">
                            <span
                                class="alm-display-flex alm-background-f0f9ff alm-border-2px-solid-0ea5e9 alm-align-items-center alm-justify-content-center alm-width-54px alm-height-54px alm-border-radius-50pct alm-box-shadow-0-2px-4px-rgba-0-0-0-0-04 alm-flex-shrink-0">
                                <img src="{{ asset('images/Espera.png') }}" class="alm-legend-icon">
                            </span>
                            <span
                                class="alm-font-size-0-80rem alm-color-0369a1 alm-font-weight-700 alm-margin-top-7px alm-text-align-center alm-line-height-1-1">Tengo
                                Modelo</span>
                        </div>

                        <div
                            class="legend-compact-item alm-width-calc-33-33-6pxpct alm-display-flex alm-flex-direction-column alm-align-items-center alm-padding-10px-6px alm-background-f8fafc alm-border-1-5px-solid-e2e8f0 alm-border-radius-8px alm-min-height-102px alm-justify-content-center">
                            <span
                                class="alm-display-flex alm-background-ecfdf5 alm-border-2px-solid-10b981 alm-align-items-center alm-justify-content-center alm-width-54px alm-height-54px alm-border-radius-50pct alm-box-shadow-0-2px-4px-rgba-0-0-0-0-04 alm-flex-shrink-0">
                                <img src="{{ asset('images/Quality.png') }}" class="alm-legend-icon">
                            </span>
                            <span
                                class="alm-font-size-0-80rem alm-color-047857 alm-font-weight-700 alm-margin-top-7px alm-text-align-center alm-line-height-1-1">Aprobado</span>
                        </div>

                        <div
                            class="legend-compact-item alm-width-calc-33-33-6pxpct alm-display-flex alm-flex-direction-column alm-align-items-center alm-padding-10px-6px alm-background-f8fafc alm-border-1-5px-solid-e2e8f0 alm-border-radius-8px alm-min-height-102px alm-justify-content-center">
                            <span
                                class="alm-display-flex alm-background-fef2f2 alm-border-2px-solid-ef4444 alm-align-items-center alm-justify-content-center alm-width-54px alm-height-54px alm-border-radius-50pct alm-box-shadow-0-2px-4px-rgba-0-0-0-0-04 alm-flex-shrink-0">
                                <img src="{{ asset('images/Quality.png') }}" class="alm-legend-icon">
                            </span>
                            <span
                                class="alm-font-size-0-80rem alm-color-b91c1c alm-font-weight-700 alm-margin-top-7px alm-text-align-center alm-line-height-1-1">Rechazado</span>
                        </div>

                        <div
                            class="legend-compact-item alm-width-calc-33-33-6pxpct alm-display-flex alm-flex-direction-column alm-align-items-center alm-padding-10px-6px alm-background-f8fafc alm-border-1-5px-solid-e2e8f0 alm-border-radius-8px alm-min-height-102px alm-justify-content-center">
                            <span
                                class="alm-display-flex alm-background-fef9c3 alm-border-2px-solid-eab308 alm-align-items-center alm-justify-content-center alm-width-54px alm-height-54px alm-border-radius-50pct alm-box-shadow-0-2px-4px-rgba-0-0-0-0-04 alm-flex-shrink-0">
                                <img src="{{ asset('images/Quality.png') }}" class="alm-legend-icon">
                            </span>
                            <span
                                class="alm-font-size-0-80rem alm-color-854d0e alm-font-weight-700 alm-margin-top-7px alm-text-align-center alm-line-height-1-1">Mixto</span>
                        </div>

                        <div
                            class="legend-compact-item alm-width-calc-33-33-6pxpct alm-display-flex alm-flex-direction-column alm-align-items-center alm-padding-10px-6px alm-background-f8fafc alm-border-1-5px-solid-e2e8f0 alm-border-radius-8px alm-min-height-102px alm-justify-content-center">
                            <span
                                class="alm-display-flex alm-background-f0fdf4 alm-border-2px-solid-059669 alm-align-items-center alm-justify-content-center alm-width-54px alm-height-54px alm-border-radius-50pct alm-box-shadow-0-2px-4px-rgba-0-0-0-0-04 alm-flex-shrink-0">
                                <img src="{{ asset('images/pdf-view.png') }}" class="alm-legend-icon">
                            </span>
                            <span
                                class="alm-font-size-0-80rem alm-color-15803d alm-font-weight-700 alm-margin-top-7px alm-text-align-center alm-line-height-1-1">Casting</span>
                        </div>

                        <div
                            class="legend-compact-item alm-width-calc-33-33-6pxpct alm-display-flex alm-flex-direction-column alm-align-items-center alm-padding-10px-6px alm-background-f8fafc alm-border-1-5px-solid-e2e8f0 alm-border-radius-8px alm-min-height-102px alm-justify-content-center">
                            <span
                                class="alm-display-flex alm-background-fdf2f8 alm-border-2px-solid-ec4899 alm-align-items-center alm-justify-content-center alm-width-54px alm-height-54px alm-border-radius-50pct alm-box-shadow-0-2px-4px-rgba-0-0-0-0-04 alm-flex-shrink-0">
                                <img src="{{ asset('images/Reproceso.png') }}" class="alm-legend-icon">
                            </span>
                            <span
                                class="alm-font-size-0-80rem alm-color-be185d alm-font-weight-700 alm-margin-top-7px alm-text-align-center alm-line-height-1-1">Reproceso</span>
                        </div>

                        <div
                            class="legend-compact-item alm-width-calc-33-33-6pxpct alm-display-flex alm-flex-direction-column alm-align-items-center alm-padding-10px-6px alm-background-f8fafc alm-border-1-5px-solid-e2e8f0 alm-border-radius-8px alm-min-height-102px alm-justify-content-center">
                            <span
                                class="alm-display-flex alm-background-f3e8ff alm-border-2px-solid-9333ea alm-align-items-center alm-justify-content-center alm-width-54px alm-height-54px alm-border-radius-50pct alm-box-shadow-0-2px-4px-rgba-0-0-0-0-04 alm-flex-shrink-0">
                                <img src="{{ asset('images/Proveedor.png') }}" class="alm-legend-icon">
                            </span>
                            <span
                                class="alm-font-size-0-80rem alm-color-9333ea alm-font-weight-700 alm-margin-top-7px alm-text-align-center alm-line-height-1-1">Enviado
                                a Proveedor</span>
                        </div>

                        <div
                            class="legend-compact-item alm-width-calc-33-33-6pxpct alm-display-flex alm-flex-direction-column alm-align-items-center alm-padding-10px-6px alm-background-f8fafc alm-border-1-5px-solid-e2e8f0 alm-border-radius-8px alm-min-height-102px alm-justify-content-center">
                            <span
                                class="alm-display-flex alm-background-ecfdf5 alm-border-2px-solid-10b981 alm-align-items-center alm-justify-content-center alm-width-54px alm-height-54px alm-border-radius-50pct alm-box-shadow-0-2px-4px-rgba-0-0-0-0-04 alm-flex-shrink-0">
                                <img src="{{ asset('images/Aprobado.png') }}" class="alm-legend-icon">
                            </span>
                            <span
                                class="alm-font-size-0-80rem alm-color-047857 alm-font-weight-700 alm-margin-top-7px alm-text-align-center alm-line-height-1-1">Aprobado
                                Final</span>
                        </div>

                        <div
                            class="legend-compact-item alm-width-calc-33-33-6pxpct alm-display-flex alm-flex-direction-column alm-align-items-center alm-padding-10px-6px alm-background-f8fafc alm-border-1-5px-solid-e2e8f0 alm-border-radius-8px alm-min-height-102px alm-justify-content-center">
                            <span
                                class="alm-display-flex alm-background-fef2f2 alm-border-2px-solid-dc2626 alm-align-items-center alm-justify-content-center alm-width-54px alm-height-54px alm-border-radius-50pct alm-box-shadow-0-2px-4px-rgba-0-0-0-0-04 alm-flex-shrink-0">
                                <img src="{{ asset('images/Rechazado.png') }}" class="alm-legend-icon">
                            </span>
                            <span
                                class="alm-font-size-0-80rem alm-color-b91c1c alm-font-weight-700 alm-margin-top-7px alm-text-align-center alm-line-height-1-1">Rechazado
                                Final</span>
                        </div>
                    </div>
                </div>
                <style>
                    .legend-compact-item {
                        transition: all 0.2s ease-in-out !important;
                        cursor: help;
                    }

                    .legend-compact-item:hover {
                        transform: translateY(-2px);
                        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.08);
                    }

                    @keyframes almFadeIn {
                        from {
                            opacity: 0;
                            transform: translateY(4px);
                        }

                        to {
                            opacity: 1;
                            transform: translateY(0);
                        }
                    }
                </style>

                <!-- Zoom Tooltip Flotante (Mercado Libre / Amazon Style) -->
                <div id="legend-zoom-tooltip"
                    class="alm-position-fixed alm-display-none alm-pointer-events-none alm-z-index-99999 alm-background-rgba-255-255-255-0-98 alm-backdrop-filter-blur-10px alm-border-radius-12px alm-box-shadow-0-10px-25px-rgba-0-0-0-0-15 alm-border-3px-solid-cbd5e1 alm-padding-16px alm-width-170px alm-height-180px alm-flex-direction-column alm-align-items-center alm-justify-content-center alm-box-sizing-border-box alm-transition-transform-0-15s-cubic-bezier-0-175-0-885-0-32-1-25-opacity-0-15s-ease alm-opacity-0 alm-transform-scale-0-9 alm-font-family-Poppins-sans-serif">
                    <span id="legend-zoom-circle"
                        class="alm-display-flex alm-align-items-center alm-justify-content-center alm-width-90px alm-height-90px alm-border-radius-50pct alm-box-shadow-0-4px-8px-rgba-0-0-0-0-06 alm-flex-shrink-0 alm-border-3px-solid-transparent">
                        <img id="legend-zoom-img" src="" class="alm-width-55px alm-height-55px alm-object-fit-contain">
                    </span>
                    <span id="legend-zoom-label"
                        class="alm-font-size-1-08rem alm-font-weight-800 alm-margin-top-10px alm-text-align-center alm-line-height-1-2"></span>
                </div>
            </aside>

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
                            <img src="{{ asset('images/pdf-view.png') }}" alt="Total" class="alm-w-60">
                        </div>
                        <div>
                            <div class="alm-stat-value">{{ $total }}</div>
                            <div class="alm-stat-label">OTs en historial</div>
                        </div>
                    </div>
                    <div class="alm-stat-card stat-activas">
                        <div class="alm-stat-icon">
                            <img src="{{ asset('images/ready.png') }}" alt="Activas" class="alm-w-60">
                        </div>
                        <div>
                            <div class="alm-stat-value">{{ $activas }}</div>
                            <div class="alm-stat-label">OTs activas</div>
                        </div>
                    </div>
                    <div class="alm-stat-card stat-inactivas">
                        <div class="alm-stat-icon">
                            <img src="{{ asset('images/Eliminar-Carpeta.png') }}" alt="Archivadas" class="alm-w-60">
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
            liberar: "{{ asset('images/Liberar.png') }}",
            descarga: "{{ asset('images/Descarga.png') }}",
            recibido: "{{ asset('images/Recibido.png') }}",
            aprobado: "{{ asset('images/Aprobado.png') }}",
            rechazado: "{{ asset('images/Rechazado.png') }}",
            guardado: "{{ asset('images/Guardado.png') }}",
            revisando: "{{ asset('images/Revisando.png') }}",
            espera: "{{ asset('images/Espera.png') }}",
        };

        // Contenedores organizados estáticamente por etapa
    </script>

@endsection