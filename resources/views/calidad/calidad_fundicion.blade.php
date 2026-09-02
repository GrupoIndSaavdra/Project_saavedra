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

            <aside class="alm-sidebar">

                {{-- ── LEYENDA DE ESTADOS DE MODELO ───────────────────────── --}}

                <div
                    class="alm-filters-card cal-margin-bottom-2em cal-background-rgba-255-255-255-0-95 cal-backdrop-filter-blur-10px cal-border-radius-12px cal-box-shadow-0-4px-15px-rgba-0-0-0-0-08 cal-position-relative cal-padding-1-6em">
                    <div
                        class="cal-display-flex cal-align-items-center cal-gap-12px cal-margin-bottom-16px cal-border-bottom-2px-solid-e2e8f0 cal-padding-bottom-12px">
                        <img src="{{ asset('images/Quality.png') }}" alt="Leyenda"
                            class="cal-width-30px cal-height-30px cal-object-fit-contain" />
                        <h2 class="cal-margin-0 cal-font-size-1-3rem cal-color-0f172a cal-font-weight-700">
                            Guía de Estados de Modelo
                        </h2>
                    </div>
                    <h3
                        class="cal-font-size-0-92rem cal-color-475569 cal-font-weight-700 cal-margin-0-0-10px-0 cal-border-left-4px-solid-94a3b8 cal-padding-left-8px">
                        Estados de Transición
                    </h3>
                    <div
                        class="legend-grid-compact cal-display-flex cal-flex-wrap-wrap cal-justify-content-center cal-gap-8px cal-margin-bottom-20px">
                        <div
                            class="legend-compact-item cal-width-calc-33-33-6pxpct cal-display-flex cal-flex-direction-column cal-align-items-center cal-padding-10px-6px cal-background-f8fafc cal-border-1-5px-solid-e2e8f0 cal-border-radius-8px cal-min-height-102px cal-justify-content-center">
                            <span
                                class="cal-display-flex cal-background-f1f5f9 cal-border-2px-solid-cbd5e1 cal-align-items-center cal-justify-content-center cal-width-54px cal-height-54px cal-border-radius-50pct cal-box-shadow-0-2px-4px-rgba-0-0-0-0-04 cal-flex-shrink-0">
                                <img src="{{ asset('images/Recibido.png') }}"
                                    class="cal-width-28px cal-height-28px cal-object-fit-contain" />
                            </span>
                            <span
                                class="cal-font-size-0-8rem cal-color-475569 cal-font-weight-700 cal-margin-top-7px cal-text-align-center cal-line-height-1-1">Nuevo</span>
                        </div>
                        <div
                            class="legend-compact-item cal-width-calc-33-33-6pxpct cal-display-flex cal-flex-direction-column cal-align-items-center cal-padding-10px-6px cal-background-f8fafc cal-border-1-5px-solid-e2e8f0 cal-border-radius-8px cal-min-height-102px cal-justify-content-center">
                            <span
                                class="cal-display-flex cal-background-fffbeb cal-border-2px-solid-f59e0b cal-align-items-center cal-justify-content-center cal-width-54px cal-height-54px cal-border-radius-50pct cal-box-shadow-0-2px-4px-rgba-0-0-0-0-04 cal-flex-shrink-0">
                                <img src="{{ asset('images/Revisando.png') }}"
                                    class="cal-width-28px cal-height-28px cal-object-fit-contain" />
                            </span>
                            <span
                                class="cal-font-size-0-8rem cal-color-b45309 cal-font-weight-700 cal-margin-top-7px cal-text-align-center cal-line-height-1-1">En
                                Revisión</span>
                        </div>
                    </div>
                    <h3
                        class="cal-font-size-0-92rem cal-color-0f172a cal-font-weight-700 cal-margin-0-0-10px-0 cal-border-left-4px-solid-3b82f6 cal-padding-left-8px">
                        Estados Prioritarios
                    </h3>
                    <div
                        class="legend-grid-compact cal-display-flex cal-flex-wrap-wrap cal-justify-content-center cal-gap-8px">
                        <div
                            class="legend-compact-item cal-width-calc-33-33-6pxpct cal-display-flex cal-flex-direction-column cal-align-items-center cal-padding-10px-6px cal-background-f8fafc cal-border-1-5px-solid-e2e8f0 cal-border-radius-8px cal-min-height-102px cal-justify-content-center">
                            <span
                                class="cal-display-flex cal-background-eff6ff cal-border-2px-solid-60a5fa cal-align-items-center cal-justify-content-center cal-width-54px cal-height-54px cal-border-radius-50pct cal-box-shadow-0-2px-4px-rgba-0-0-0-0-04 cal-flex-shrink-0">
                                <img src="{{ asset('images/pdf-view.png') }}"
                                    class="cal-width-28px cal-height-28px cal-object-fit-contain" />
                            </span>
                            <span
                                class="cal-font-size-0-8rem cal-color-2563eb cal-font-weight-700 cal-margin-top-7px cal-text-align-center cal-line-height-1-1">Pre-Orden</span>
                        </div>
                        <div
                            class="legend-compact-item cal-width-calc-33-33-6pxpct cal-display-flex cal-flex-direction-column cal-align-items-center cal-padding-10px-6px cal-background-f8fafc cal-border-1-5px-solid-e2e8f0 cal-border-radius-8px cal-min-height-102px cal-justify-content-center">
                            <span
                                class="cal-display-flex cal-background-f0f9ff cal-border-2px-solid-0ea5e9 cal-align-items-center cal-justify-content-center cal-width-54px cal-height-54px cal-border-radius-50pct cal-box-shadow-0-2px-4px-rgba-0-0-0-0-04 cal-flex-shrink-0">
                                <img src="{{ asset('images/Espera.png') }}"
                                    class="cal-width-28px cal-height-28px cal-object-fit-contain" />
                            </span>
                            <span
                                class="cal-font-size-0-8rem cal-color-0369a1 cal-font-weight-700 cal-margin-top-7px cal-text-align-center cal-line-height-1-1">Tengo
                                Modelo</span>
                        </div>
                        <div
                            class="legend-compact-item cal-width-calc-33-33-6pxpct cal-display-flex cal-flex-direction-column cal-align-items-center cal-padding-10px-6px cal-background-f8fafc cal-border-1-5px-solid-e2e8f0 cal-border-radius-8px cal-min-height-102px cal-justify-content-center">
                            <span
                                class="cal-display-flex cal-background-ecfdf5 cal-border-2px-solid-10b981 cal-align-items-center cal-justify-content-center cal-width-54px cal-height-54px cal-border-radius-50pct cal-box-shadow-0-2px-4px-rgba-0-0-0-0-04 cal-flex-shrink-0">
                                <img src="{{ asset('images/Quality.png') }}"
                                    class="cal-width-28px cal-height-28px cal-object-fit-contain" />
                            </span>
                            <span
                                class="cal-font-size-0-8rem cal-color-047857 cal-font-weight-700 cal-margin-top-7px cal-text-align-center cal-line-height-1-1">Aprobado</span>
                        </div>
                        <div
                            class="legend-compact-item cal-width-calc-33-33-6pxpct cal-display-flex cal-flex-direction-column cal-align-items-center cal-padding-10px-6px cal-background-f8fafc cal-border-1-5px-solid-e2e8f0 cal-border-radius-8px cal-min-height-102px cal-justify-content-center">
                            <span
                                class="cal-display-flex cal-background-fef2f2 cal-border-2px-solid-ef4444 cal-align-items-center cal-justify-content-center cal-width-54px cal-height-54px cal-border-radius-50pct cal-box-shadow-0-2px-4px-rgba-0-0-0-0-04 cal-flex-shrink-0">
                                <img src="{{ asset('images/Quality.png') }}"
                                    class="cal-width-28px cal-height-28px cal-object-fit-contain" />
                            </span>
                            <span
                                class="cal-font-size-0-8rem cal-color-b91c1c cal-font-weight-700 cal-margin-top-7px cal-text-align-center cal-line-height-1-1">Rechazado</span>
                        </div>
                        <div
                            class="legend-compact-item cal-width-calc-33-33-6pxpct cal-display-flex cal-flex-direction-column cal-align-items-center cal-padding-10px-6px cal-background-f8fafc cal-border-1-5px-solid-e2e8f0 cal-border-radius-8px cal-min-height-102px cal-justify-content-center">
                            <span
                                class="cal-display-flex cal-background-fef9c3 cal-border-2px-solid-eab308 cal-align-items-center cal-justify-content-center cal-width-54px cal-height-54px cal-border-radius-50pct cal-box-shadow-0-2px-4px-rgba-0-0-0-0-04 cal-flex-shrink-0">
                                <img src="{{ asset('images/Quality.png') }}"
                                    class="cal-width-28px cal-height-28px cal-object-fit-contain" />
                            </span>
                            <span
                                class="cal-font-size-0-8rem cal-color-854d0e cal-font-weight-700 cal-margin-top-7px cal-text-align-center cal-line-height-1-1">Mixto</span>
                        </div>
                        <div
                            class="legend-compact-item cal-width-calc-33-33-6pxpct cal-display-flex cal-flex-direction-column cal-align-items-center cal-padding-10px-6px cal-background-f8fafc cal-border-1-5px-solid-e2e8f0 cal-border-radius-8px cal-min-height-102px cal-justify-content-center">
                            <span
                                class="cal-display-flex cal-background-f0fdf4 cal-border-2px-solid-059669 cal-align-items-center cal-justify-content-center cal-width-54px cal-height-54px cal-border-radius-50pct cal-box-shadow-0-2px-4px-rgba-0-0-0-0-04 cal-flex-shrink-0">
                                <img src="{{ asset('images/pdf-view.png') }}"
                                    class="cal-width-28px cal-height-28px cal-object-fit-contain" />
                            </span>
                            <span
                                class="cal-font-size-0-8rem cal-color-15803d cal-font-weight-700 cal-margin-top-7px cal-text-align-center cal-line-height-1-1">Casting</span>
                        </div>
                        <div
                            class="legend-compact-item cal-width-calc-33-33-6pxpct cal-display-flex cal-flex-direction-column cal-align-items-center cal-padding-10px-6px cal-background-f8fafc cal-border-1-5px-solid-e2e8f0 cal-border-radius-8px cal-min-height-102px cal-justify-content-center">
                            <span
                                class="cal-display-flex cal-background-fdf2f8 cal-border-2px-solid-ec4899 cal-align-items-center cal-justify-content-center cal-width-54px cal-height-54px cal-border-radius-50pct cal-box-shadow-0-2px-4px-rgba-0-0-0-0-04 cal-flex-shrink-0">
                                <img src="{{ asset('images/Reproceso.png') }}"
                                    class="cal-width-28px cal-height-28px cal-object-fit-contain" />
                            </span>
                            <span
                                class="cal-font-size-0-8rem cal-color-be185d cal-font-weight-700 cal-margin-top-7px cal-text-align-center cal-line-height-1-1">Reproceso</span>
                        </div>
                        <div
                            class="legend-compact-item cal-width-calc-33-33-6pxpct cal-display-flex cal-flex-direction-column cal-align-items-center cal-padding-10px-6px cal-background-f8fafc cal-border-1-5px-solid-e2e8f0 cal-border-radius-8px cal-min-height-102px cal-justify-content-center">
                            <span
                                class="cal-display-flex cal-background-f3e8ff cal-border-2px-solid-9333ea cal-align-items-center cal-justify-content-center cal-width-54px cal-height-54px cal-border-radius-50pct cal-box-shadow-0-2px-4px-rgba-0-0-0-0-04 cal-flex-shrink-0">
                                <img src="{{ asset('images/Proveedor.png') }}"
                                    class="cal-width-28px cal-height-28px cal-object-fit-contain" />
                            </span>
                            <span
                                class="cal-font-size-0-8rem cal-color-9333ea cal-font-weight-700 cal-margin-top-7px cal-text-align-center cal-line-height-1-1">Enviado
                                a Proveedor</span>
                        </div>
                        <div
                            class="legend-compact-item cal-width-calc-33-33-6pxpct cal-display-flex cal-flex-direction-column cal-align-items-center cal-padding-10px-6px cal-background-f8fafc cal-border-1-5px-solid-e2e8f0 cal-border-radius-8px cal-min-height-102px cal-justify-content-center">
                            <span
                                class="cal-display-flex cal-background-ecfdf5 cal-border-2px-solid-10b981 cal-align-items-center cal-justify-content-center cal-width-54px cal-height-54px cal-border-radius-50pct cal-box-shadow-0-2px-4px-rgba-0-0-0-0-04 cal-flex-shrink-0">
                                <img src="{{ asset('images/Aprobado.png') }}"
                                    class="cal-width-28px cal-height-28px cal-object-fit-contain" />
                            </span>
                            <span
                                class="cal-font-size-0-8rem cal-color-047857 cal-font-weight-700 cal-margin-top-7px cal-text-align-center cal-line-height-1-1">Aprobado
                                Final</span>
                        </div>
                        <div
                            class="legend-compact-item cal-width-calc-33-33-6pxpct cal-display-flex cal-flex-direction-column cal-align-items-center cal-padding-10px-6px cal-background-f8fafc cal-border-1-5px-solid-e2e8f0 cal-border-radius-8px cal-min-height-102px cal-justify-content-center">
                            <span
                                class="cal-display-flex cal-background-fef2f2 cal-border-2px-solid-dc2626 cal-align-items-center cal-justify-content-center cal-width-54px cal-height-54px cal-border-radius-50pct cal-box-shadow-0-2px-4px-rgba-0-0-0-0-04 cal-flex-shrink-0">
                                <img src="{{ asset('images/Rechazado.png') }}"
                                    class="cal-width-28px cal-height-28px cal-object-fit-contain" />
                            </span>
                            <span
                                class="cal-font-size-0-8rem cal-color-b91c1c cal-font-weight-700 cal-margin-top-7px cal-text-align-center cal-line-height-1-1">Rechazado
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
                    class="cal-position-fixed cal-display-none cal-pointer-events-none cal-z-index-99999 cal-background-rgba-255-255-255-0-98 cal-backdrop-filter-blur-10px cal-border-radius-12px cal-box-shadow-0-10px-25px-rgba-0-0-0-0-15 cal-border-3px-solid-cbd5e1 cal-padding-16px cal-width-170px cal-height-180px cal-flex-direction-column cal-align-items-center cal-justify-content-center cal-box-sizing-border-box cal-transition-transform-0-15s-cubic-bezier-0-175-0-885-0-32-1-25-opacity-0-15s-ease cal-opacity-0 cal-transform-scale-0-9 cal-font-family-quot">
                    <span id="legend-zoom-circle"
                        class="cal-display-flex cal-align-items-center cal-justify-content-center cal-width-90px cal-height-90px cal-border-radius-50pct cal-box-shadow-0-4px-8px-rgba-0-0-0-0-06 cal-flex-shrink-0 cal-border-3px-solid-transparent">
                        <img id="legend-zoom-img" src="" class="cal-width-55px cal-height-55px cal-object-fit-contain" />
                    </span>
                    <span id="legend-zoom-label"
                        class="cal-font-size-1-08rem cal-font-weight-800 cal-margin-top-10px cal-text-align-center cal-line-height-1-2"></span>
                </div>
                <script>
                    function initLegendZoom() {
                        const tooltip = document.getElementById(
                            "legend-zoom-tooltip",
                        );
                        const zoomCircle =
                            document.getElementById("legend-zoom-circle");
                        const zoomImg =
                            document.getElementById("legend-zoom-img");
                        const zoomLabel =
                            document.getElementById("legend-zoom-label");
                        if (!tooltip) return;
                        document
                            .querySelectorAll(".legend-compact-item")
                            .forEach((item) => {
                                item.addEventListener("mouseenter", (e) => {
                                    const circle = item.querySelector("span");
                                    const img = circle ?
                                        circle.querySelector("img") :
                                        null;
                                    const label =
                                        item.querySelectorAll("span")[1];
                                    if (!circle || !img || !label) return;
                                    // Extract styles
                                    const bgColor =
                                        circle.style.backgroundColor ||
                                        window.getComputedStyle(circle)
                                            .backgroundColor;
                                    const borderColor =
                                        circle.style.borderColor ||
                                        window.getComputedStyle(circle)
                                            .borderColor;
                                    const textColor =
                                        label.style.color ||
                                        window.getComputedStyle(label).color;
                                    const imgSrc = img.src;
                                    const textContent = label.textContent;
                                    // Apply to tooltip
                                    tooltip.style.borderColor = borderColor;
                                    zoomCircle.style.backgroundColor = bgColor;
                                    zoomCircle.style.borderColor = borderColor;
                                    zoomCircle.style.borderStyle = "solid";
                                    zoomCircle.style.borderWidth = "3px";
                                    zoomImg.src = imgSrc;
                                    zoomImg.src = imgSrc;
                                    zoomLabel.textContent = textContent;
                                    zoomLabel.style.color = textColor;
                                    tooltip.style.display = "flex";
                                    // Trigger animation frame for fade-in transition
                                    requestAnimationFrame(() => {
                                        tooltip.style.opacity = "1";
                                        tooltip.style.transform = "scale(1.05)";
                                    });
                                });
                                item.addEventListener("mousemove", (e) => {
                                    const offsetX = 20;
                                    const offsetY = 20;
                                    let posX = e.clientX + offsetX;
                                    let posY = e.clientY + offsetY;
                                    // Boundary checks
                                    const tooltipWidth = 170;
                                    const tooltipHeight = 180;
                                    if (
                                        posX + tooltipWidth >
                                        window.innerWidth - 10
                                    ) {
                                        posX =
                                            e.clientX - tooltipWidth - offsetX;
                                    }
                                    if (
                                        posY + tooltipHeight >
                                        window.innerHeight - 10
                                    ) {
                                        posY =
                                            e.clientY - tooltipHeight - offsetY;
                                    }
                                    tooltip.style.left = `${posX}px`;
                                    tooltip.style.top = `${posY}px`;
                                });
                                item.addEventListener("mouseleave", () => {
                                    tooltip.style.opacity = "0";
                                    tooltip.style.transform = "scale(0.95)";
                                    // Hide after transition
                                    setTimeout(() => {
                                        if (tooltip.style.opacity === "0") {
                                            tooltip.style.display = "none";
                                        }
                                    }, 100);
                                });
                            });
                    }
                    if (document.readyState !== "loading") {
                        initLegendZoom();
                    } else {
                        document.addEventListener(
                            "DOMContentLoaded",
                            initLegendZoom,
                        );
                    }
                </script>
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
                                <select id="alm-search-ot" class="select-filter" name="ot" onchange="this.form.submit()">
                                    <option value="">Todas las OTs</option>
                                    @foreach ($listaOts as $otOption)
                                        <option value="{{ $otOption }}" {{ $busquedaOt === $otOption ? 'selected' : '' }}>
                                            {{ preg_replace('/_\d{8}_\d{6}_.*/', '', $otOption) }}
                                        </option>
                                    @endforeach
                                </select>
                                <label for="alm-search-ot">Orden de trabajo:
                                </label>
                            </div>
                            <div class="filter">
                                <input id="alm-desde" class="input-filter" type="date" name="desde" value="{{ $desde }}"
                                    onchange="this.form.submit()" />
                                <label for="alm-desde">Desde: </label>
                            </div>
                            <div class="filter">
                                <input id="alm-hasta" class="input-filter" type="date" name="hasta" value="{{ $hasta }}"
                                    onchange="this.form.submit()" />
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

    @include('calidad.partials.modals.review_changes_modal')

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
            confirmarRecepcionRechazo: "{{ route('almacen.fundicion.confirmarRecepcionRechazo') }}",
            pendingComparison: "{{ route('almacen.fundicion.pending_comparison') }}",
            resolveChanges: "{{ route('almacen.fundicion.resolve_changes') }}"
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

        // ── REVISAR CAMBIOS PENDIENTES (Calidad) ──
        let currentPendingOt = null;

        window.almacenRevisarCambios = function (ot) {
            currentPendingOt = ot;
            const url = window.almacenRoutes.pendingComparison + "?ot=" + encodeURIComponent(ot);

            fetch(url)
                .then(res => res.json())
                .then(data => {
                    if (data.success && data.has_pending) {
                        renderizarModalRevisarCambios(data.comparison, data.tipo_cambio, data.es_total, data.affected_clases_count);
                        const modal = document.getElementById("modalRevisarCambios");
                        modal.classList.add("open");
                        document.body.classList.add("modal-open");
                    } else {
                        if (typeof almacenToast === 'function') {
                            almacenToast(data.message || "No hay cambios pendientes.", data.success ? "success" : "error");
                        } else {
                            alert(data.message || "No hay cambios pendientes.");
                        }
                    }
                })
                .catch(err => {
                    console.error(err);
                    if (typeof almacenToast === 'function') {
                        almacenToast("Error al obtener los cambios pendientes.", "error");
                    } else {
                        alert("Error al obtener los cambios pendientes.");
                    }
                });
        };

        window.cerrarModalRevisarCambios = function () {
            const modal = document.getElementById("modalRevisarCambios");
            modal.classList.remove("open");
            document.body.classList.remove("modal-open");
            currentPendingOt = null;
        };

        window.almacenResolverCambios = function (action) {
            if (!currentPendingOt) return;

            let msg = "";
            if (action === 'reiniciar_completo') {
                msg = "¿Estás seguro de reiniciar el proceso completo de toda la OT? Se borrarán los avances y documentos de todas las clases.";
            } else if (action === 'reiniciar_parcial' || action === 'reiniciar') {
                msg = "¿Estás seguro de reiniciar el proceso para la(s) clase(s) afectada(s)? Se borrarán únicamente los avances y documentos de esta(s) clase(s).";
            } else {
                msg = "¿Estás seguro de mantener el proceso? Se actualizarán los dibujos conservando el avance actual.";
            }

            if (!confirm(msg)) return;

            fetch(window.almacenRoutes.resolveChanges, {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute("content"),
                },
                body: JSON.stringify({ ot: currentPendingOt, action: action })
            })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        if (typeof almacenToast === 'function') {
                            almacenToast(data.message, "success");
                        } else {
                            alert(data.message);
                        }
                        cerrarModalRevisarCambios();
                        setTimeout(() => window.location.reload(), 1500);
                    } else {
                        if (typeof almacenToast === 'function') {
                            almacenToast(data.message || "Error al resolver los cambios.", "error");
                        } else {
                            alert(data.message || "Error al resolver los cambios.");
                        }
                    }
                })
                .catch(err => {
                    console.error(err);
                    if (typeof almacenToast === 'function') {
                        almacenToast("Error de conexión.", "error");
                    } else {
                        alert("Error de conexión.");
                    }
                });
        };

        function renderizarModalRevisarCambios(comparisonData, tipoCambio, esTotal, affectedCount) {
            const container = document.getElementById('revisar-cambios-container');
            const btnReiniciar = document.getElementById('btn-resolver-reiniciar');
            const btnMantener = document.getElementById('btn-resolver-mantener');

            const affectedClasses = comparisonData.map(item => item.clase).join(', ');
            const baseUrl = window.baseUrl || window.location.origin + "/";
            const pdfViewShadow = baseUrl.endsWith('/') ? baseUrl + 'images/pdf-view-shadow.png' : baseUrl + '/images/pdf-view-shadow.png';
            const pdfView = baseUrl.endsWith('/') ? baseUrl + 'images/pdf-view.png' : baseUrl + '/images/pdf-view.png';

            const isAdicion = tipoCambio === 'adicion';

            if (btnReiniciar) {
                if (esTotal) {
                    btnReiniciar.textContent = "Reiniciar Proceso Completo de la OT";
                    btnReiniciar.setAttribute("onclick", "almacenResolverCambios('reiniciar_completo')");
                } else {
                    const numClases = affectedCount || comparisonData.length;
                    btnReiniciar.textContent = numClases === 1
                        ? "Reiniciar Proceso Completo para esta Clase"
                        : "Reiniciar Proceso Completo para Clases Afectadas";
                    btnReiniciar.setAttribute("onclick", "almacenResolverCambios('reiniciar_parcial')");
                }
            }

            if (btnMantener) {
                btnMantener.textContent = isAdicion ? "Solo Agregar Dibujos" : "Solo Reemplazar Archivos";
            }

            let alertHtml = isAdicion
                ? `<strong>¡Atención!</strong> Se agregaron nuevos Dibujos de Fundición para <strong>${affectedClasses}</strong>. <br><br>¿Deseas regresar el proceso desde el inicio o solo agregamos los dibujos nuevos al proceso?`
                : `<strong>¡Atención!</strong> Se registraron cambios en Dibujos de Fundición y estos afectan al proceso de <strong>${affectedClasses}</strong>. <br><br>¿Deseas regresar el proceso desde el inicio o solo cambiamos los dibujos viejos por los nuevos?`;

            let html = `
                                                                <div class="alm-alert alm-alert-warning alm-margin-bottom-20px alm-padding-15px alm-border-radius-8px" style="background-color: #fffbeb; border-left: 4px solid #f59e0b; color: #b45309;">
                                                                    ${alertHtml}
                                                                </div>
                                                            `;

            comparisonData.forEach(item => {
                const itemIsAdicion = item.es_adicion || isAdicion;
                const viejos = item.viejos || [];
                const nuevos = item.nuevos || [];

                if (itemIsAdicion) {
                    const agregadosList = (item.agregados && item.agregados.length > 0) ? item.agregados : nuevos;
                    html += `
                                                                    <div class="alm-background-ffffff alm-border-radius-14px alm-padding-20px alm-margin-bottom-20px" style="border: 2px solid #cbd5e1; box-shadow: 0 4px 15px rgba(0,0,0,0.06);">
                                                                        <h4 class="alm-margin-top-0 alm-margin-bottom-15px alm-color-0f172a alm-font-size-1-15rem alm-border-bottom-2px-solid-0369a1 alm-padding-bottom-8px">
                                                                            Clase: <strong style="text-transform: capitalize; color: #0369a1;">${item.clase}</strong> <span style="font-size: 0.8em; color: #059669; font-weight: 700;">(Nuevo Dibujo Agregado)</span>
                                                                        </h4>
                                                                        <div class="alm-display-flex alm-flex-direction-column alm-gap-10px">
                                                                            <h5 class="alm-color-059669 alm-margin-0-0-10px-0" style="font-weight: 700;">Nuevos Dibujos Agregados</h5>
                                                                            ${agregadosList.map((n, index) => {
                        const isDwg = n.nombre.toLowerCase().endsWith('.dwg');
                        const imgShadow = isDwg ? (window.baseUrl.endsWith('/') ? window.baseUrl + 'images/dwg-shadow.png' : window.baseUrl + '/images/dwg-shadow.png') : pdfViewShadow;
                        const imgHover = isDwg ? (window.baseUrl.endsWith('/') ? window.baseUrl + 'images/dwg.png' : window.baseUrl + '/images/dwg.png') : pdfView;
                        const titleAttr = isDwg ? 'Descargar DWG' : 'Abrir PDF';
                        const btnText = isDwg ? 'Descargar' : 'Ver';
                        return `
                                                                                <div class="dibujos-file-card card-dibujo" style="animation-delay: ${index * 0.05}s; border: 2px solid #10b981; background-color: #ecfdf5; border-left: 5px solid #10b981;">
                                                                                    <div class="file-icon-wrapper alm-cursor-pointer" title="${titleAttr}">
                                                                                        <img src="${imgShadow}" class="file-icon icon-default">
                                                                                        <img src="${imgHover}" class="file-icon icon-hover">
                                                                                    </div>
                                                                                    <div class="file-name alm-cursor-pointer" title="${titleAttr}" onclick="window.open('${n.url}', '_blank')">
                                                                                        <div style="margin-bottom: 3px;">
                                                                                            <span style="font-size: 0.72em; font-weight: 700; background: #d1fae5; color: #047857; padding: 2px 8px; border-radius: 4px; border: 1px solid #a7f3d0;">NUEVO DIBUJO AGREGADO</span>
                                                                                        </div>
                                                                                        <strong>${n.nombre}</strong>
                                                                                    </div>
                                                                                    <div class="file-actions alm-flex-gap-5">
                                                                                        <button class="btn-dibujos btn-dibujos-sm btn-ver" style="background-color: #059669; color: white;" onclick="window.open('${n.url}', '_blank')">${btnText}</button>
                                                                                    </div>
                                                                                </div>
                                                                            `;
                    }).join('')}
                                                                        </div>
                                                                    </div>
                                                                    `;
                } else {
                    const nuevosProcesados = nuevos.map((n, index) => {
                        const exactMatch = viejos.some(v => v.nombre.toLowerCase() === n.nombre.toLowerCase());
                        const posMatch = index < viejos.length;
                        const isReemplazo = exactMatch || posMatch;
                        return {
                            ...n,
                            isReemplazo: isReemplazo,
                            theme: isReemplazo ? 'blue' : 'green'
                        };
                    });

                    html += `
                                                                    <div class="alm-background-ffffff alm-border-radius-14px alm-padding-20px alm-margin-bottom-20px" style="border: 2px solid #cbd5e1; box-shadow: 0 4px 15px rgba(0,0,0,0.06);">
                                                                        <h4 class="alm-margin-top-0 alm-margin-bottom-15px alm-color-0f172a alm-font-size-1-15rem alm-border-bottom-2px-solid-0369a1 alm-padding-bottom-8px">
                                                                            Clase: <strong style="text-transform: capitalize; color: #0369a1;">${item.clase}</strong>
                                                                        </h4>
                                                                        <div class="alm-display-flex alm-gap-20px">
                                                                            <!-- Viejos (En Almacén) -->
                                                                            <div class="alm-flex-1">
                                                                                <h5 class="alm-color-64748b alm-margin-0-0-10px-0" style="font-weight: 700;">Actuales (En Almacén)</h5>
                                                                                <div class="alm-display-flex alm-flex-direction-column alm-gap-10px">
                                                                                    ${viejos.length > 0 ? viejos.map((v, index) => {
                        const isDwg = v.nombre.toLowerCase().endsWith('.dwg');
                        const imgShadow = isDwg ? (window.baseUrl.endsWith('/') ? window.baseUrl + 'images/dwg-shadow.png' : window.baseUrl + '/images/dwg-shadow.png') : pdfViewShadow;
                        const imgHover = isDwg ? (window.baseUrl.endsWith('/') ? window.baseUrl + 'images/dwg.png' : window.baseUrl + '/images/dwg.png') : pdfView;
                        const titleAttr = isDwg ? 'Descargar DWG' : 'Abrir PDF';
                        const btnText = isDwg ? 'Descargar' : 'Ver';
                        return `
                                                                                        <div class="dibujos-file-card card-dibujo" style="animation-delay: ${index * 0.05}s; border: 2px solid #0284c7; background-color: #f0f9ff; border-left: 5px solid #0284c7;">
                                                                                            <div class="file-icon-wrapper alm-cursor-pointer" title="${titleAttr}">
                                                                                                <img src="${imgShadow}" class="file-icon icon-default">
                                                                                                <img src="${imgHover}" class="file-icon icon-hover">
                                                                                            </div>
                                                                                            <div class="file-name alm-cursor-pointer" title="${titleAttr}" onclick="window.open('${v.url}', '_blank')">
                                                                                                <div style="margin-bottom: 3px;">
                                                                                                    <span style="font-size: 0.72em; font-weight: 700; background: #e0f2fe; color: #0369a1; padding: 2px 8px; border-radius: 4px; border: 1px solid #bae6fd;">DIBUJO EN ALMACÉN</span>
                                                                                                </div>
                                                                                                <strong>${v.nombre}</strong>
                                                                                            </div>
                                                                                            <div class="file-actions alm-flex-gap-5">
                                                                                                <button class="btn-dibujos btn-dibujos-sm btn-ver" style="background-color: #0369a1; color: white;" onclick="window.open('${v.url}', '_blank')">${btnText}</button>
                                                                                            </div>
                                                                                        </div>
                                                                                    `;
                    }).join('') : '<span class="alm-text-sm-gray">Sin archivos</span>'}
                                                                                </div>
                                                                            </div>
                                                                            <!-- Nuevos (De Dibujos de Fundición) -->
                                                                            <div class="alm-flex-1">
                                                                                <h5 class="alm-color-0369a1 alm-margin-0-0-10px-0" style="font-weight: 700;">Nuevos (De Programación)</h5>
                                                                                <div class="alm-display-flex alm-flex-direction-column alm-gap-10px">
                                                                                    ${nuevosProcesados.length > 0 ? nuevosProcesados.map((n, index) => {
                        const isDwg = n.nombre.toLowerCase().endsWith('.dwg');
                        const imgShadow = isDwg ? (window.baseUrl.endsWith('/') ? window.baseUrl + 'images/dwg-shadow.png' : window.baseUrl + '/images/dwg-shadow.png') : pdfViewShadow;
                        const imgHover = isDwg ? (window.baseUrl.endsWith('/') ? window.baseUrl + 'images/dwg.png' : window.baseUrl + '/images/dwg.png') : pdfView;
                        const titleAttr = isDwg ? 'Descargar DWG' : 'Abrir PDF';
                        const btnText = isDwg ? 'Descargar' : 'Ver';
                        const isBlue = n.theme === 'blue';
                        const borderColor = isBlue ? '#0284c7' : '#10b981';
                        const bgColor = isBlue ? '#f0f9ff' : '#ecfdf5';
                        const badgeBg = isBlue ? '#e0f2fe' : '#d1fae5';
                        const badgeColor = isBlue ? '#0369a1' : '#047857';
                        const badgeBorder = isBlue ? '#bae6fd' : '#a7f3d0';
                        const badgeText = isBlue ? 'DIBUJO REEMPLAZADO' : 'NUEVO DIBUJO AGREGADO';
                        const btnColor = isBlue ? '#0284c7' : '#059669';
                        const textColor = isBlue ? '#0369a1' : '#047857';

                        return `
                                                                                        <div class="dibujos-file-card card-dibujo" style="animation-delay: ${index * 0.05}s; border: 2px solid ${borderColor}; background-color: ${bgColor}; border-left: 5px solid ${borderColor};">
                                                                                            <div class="file-icon-wrapper alm-cursor-pointer" title="${titleAttr}">
                                                                                                <img src="${imgShadow}" class="file-icon icon-default">
                                                                                                <img src="${imgHover}" class="file-icon icon-hover">
                                                                                            </div>
                                                                                            <div class="file-name alm-cursor-pointer" title="${titleAttr}" onclick="window.open('${n.url}', '_blank')">
                                                                                                <div style="margin-bottom: 3px;">
                                                                                                    <span style="font-size: 0.72em; font-weight: 700; background: ${badgeBg}; color: ${badgeColor}; padding: 2px 8px; border-radius: 4px; border: 1px solid ${badgeBorder};">${badgeText}</span>
                                                                                                </div>
                                                                                                <strong style="color: ${textColor};">${n.nombre}</strong>
                                                                                            </div>
                                                                                            <div class="file-actions alm-flex-gap-5">
                                                                                                <button class="btn-dibujos btn-dibujos-sm btn-ver" style="background-color: ${btnColor}; color: white;" onclick="window.open('${n.url}', '_blank')">${btnText}</button>
                                                                                            </div>
                                                                                        </div>
                                                                                        `;
                    }).join('') : '<span class="alm-text-sm-gray">Sin archivos</span>'}
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    `;
                }
            });

            container.innerHTML = html;
        }
    </script>
@endsection