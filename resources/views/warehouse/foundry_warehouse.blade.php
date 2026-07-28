@extends('layouts.appMenu')

@section('head')
    @php
        $perfil = Auth::user()->perfil;
        $deptName = ($perfil == 1 || $perfil == 2) ? 'Administración' : ($perfil == 4 ? 'Calidad' : 'Almacén');
    @endphp
    <title>Almacén — Dibujos de Fundición | GIS</title>
    <meta name="description"
        content="Consulta histórica de dibujos de fundición enviados a Almacén y Calidad. Vista de solo lectura.">
    @vite(['resources/css/warehouse_views/foundry_warehouse.css', 'resources/js/warehouse_views/foundry_warehouse.js'])
@endsection

@section('background-body', 'background-image:url("' . asset('images/fondoLogin.jpg') . '")')

@section('content')

    <div class="alm-wrapper">
        @php
            $perfil = Auth::user()->perfil;
            $deptName = ($perfil == 1 || $perfil == 2) ? 'Administración' : ($perfil == 4 ? 'Calidad' : 'Almacén');
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


                <div class="alm-filters-card alm-margin-bottom-2em alm-background-rgba-255-255-255-0-95 alm-backdrop-filter-blur-10px alm-border-radius-12px alm-box-shadow-0-4px-15px-rgba-0-0-0-0-08 alm-position-relative alm-padding-1-6em">
                    <div class="alm-display-flex alm-align-items-center alm-gap-12px alm-margin-bottom-16px alm-border-bottom-2px-solid-e2e8f0 alm-padding-bottom-12px">
                        <img src="{{ asset('images/Quality.png') }}" alt="Leyenda" class="alm-width-30px alm-height-30px alm-object-fit-contain">
                        <h2 class="alm-margin-0 alm-font-size-1-30rem alm-color-0f172a alm-font-weight-700">Guía de Estados de Modelo</h2>
                    </div>

                    <h3 class="alm-font-size-0-92rem alm-color-475569 alm-font-weight-700 alm-margin-0-0-10px-0 alm-border-left-4px-solid-94a3b8 alm-padding-left-8px">Estados de Transición</h3>
                    <div class="legend-grid-compact alm-display-flex alm-flex-wrap-wrap alm-justify-content-center alm-gap-8px alm-margin-bottom-20px">

                        <div class="legend-compact-item alm-width-calc-33-33-6pxpct alm-display-flex alm-flex-direction-column alm-align-items-center alm-padding-10px-6px alm-background-f8fafc alm-border-1-5px-solid-e2e8f0 alm-border-radius-8px alm-min-height-102px alm-justify-content-center">
                            <span class="alm-display-flex alm-background-f1f5f9 alm-border-2px-solid-cbd5e1 alm-align-items-center alm-justify-content-center alm-width-54px alm-height-54px alm-border-radius-50pct alm-box-shadow-0-2px-4px-rgba-0-0-0-0-04 alm-flex-shrink-0">
                                <img src="{{ asset('images/Recibido.png') }}" class="alm-legend-icon">
                            </span>
                            <span class="alm-font-size-0-80rem alm-color-475569 alm-font-weight-700 alm-margin-top-7px alm-text-align-center alm-line-height-1-1">Nuevo</span>
                        </div>

                        @if (Auth::user()->perfil != 4)
                        <div class="legend-compact-item alm-width-calc-33-33-6pxpct alm-display-flex alm-flex-direction-column alm-align-items-center alm-padding-10px-6px alm-background-f8fafc alm-border-1-5px-solid-e2e8f0 alm-border-radius-8px alm-min-height-102px alm-justify-content-center">
                            <span class="alm-display-flex alm-background-e0e7ff alm-border-2px-solid-818cf8 alm-align-items-center alm-justify-content-center alm-width-54px alm-height-54px alm-border-radius-50pct alm-box-shadow-0-2px-4px-rgba-0-0-0-0-04 alm-flex-shrink-0">
                                <img src="{{ asset('images/enviando.png') }}" class="alm-legend-icon">
                            </span>
                            <span class="alm-font-size-0-80rem alm-color-4f46e5 alm-font-weight-700 alm-margin-top-7px alm-text-align-center alm-line-height-1-1">Correo Enviado</span>
                        </div>
                        @endif

                        <div class="legend-compact-item alm-width-calc-33-33-6pxpct alm-display-flex alm-flex-direction-column alm-align-items-center alm-padding-10px-6px alm-background-f8fafc alm-border-1-5px-solid-e2e8f0 alm-border-radius-8px alm-min-height-102px alm-justify-content-center">
                            <span class="alm-display-flex alm-background-fffbeb alm-border-2px-solid-f59e0b alm-align-items-center alm-justify-content-center alm-width-54px alm-height-54px alm-border-radius-50pct alm-box-shadow-0-2px-4px-rgba-0-0-0-0-04 alm-flex-shrink-0">
                                <img src="{{ asset('images/Revisando.png') }}" class="alm-legend-icon">
                            </span>
                            <span class="alm-font-size-0-80rem alm-color-b45309 alm-font-weight-700 alm-margin-top-7px alm-text-align-center alm-line-height-1-1">En Revisión</span>
                        </div>
                    </div>

                    <h3 class="alm-font-size-0-92rem alm-color-0f172a alm-font-weight-700 alm-margin-0-0-10px-0 alm-border-left-4px-solid-3b82f6 alm-padding-left-8px">Estados Prioritarios</h3>
                    <div class="legend-grid-compact alm-display-flex alm-flex-wrap-wrap alm-justify-content-center alm-gap-8px">

                        <div class="legend-compact-item alm-width-calc-33-33-6pxpct alm-display-flex alm-flex-direction-column alm-align-items-center alm-padding-10px-6px alm-background-f8fafc alm-border-1-5px-solid-e2e8f0 alm-border-radius-8px alm-min-height-102px alm-justify-content-center">
                            <span class="alm-display-flex alm-background-eff6ff alm-border-2px-solid-60a5fa alm-align-items-center alm-justify-content-center alm-width-54px alm-height-54px alm-border-radius-50pct alm-box-shadow-0-2px-4px-rgba-0-0-0-0-04 alm-flex-shrink-0">
                                <img src="{{ asset('images/pdf-view.png') }}" class="alm-legend-icon">
                            </span>
                            <span class="alm-font-size-0-80rem alm-color-2563eb alm-font-weight-700 alm-margin-top-7px alm-text-align-center alm-line-height-1-1">Pre-Orden</span>
                        </div>

                        <div class="legend-compact-item alm-width-calc-33-33-6pxpct alm-display-flex alm-flex-direction-column alm-align-items-center alm-padding-10px-6px alm-background-f8fafc alm-border-1-5px-solid-e2e8f0 alm-border-radius-8px alm-min-height-102px alm-justify-content-center">
                            <span class="alm-display-flex alm-background-f0f9ff alm-border-2px-solid-0ea5e9 alm-align-items-center alm-justify-content-center alm-width-54px alm-height-54px alm-border-radius-50pct alm-box-shadow-0-2px-4px-rgba-0-0-0-0-04 alm-flex-shrink-0">
                                <img src="{{ asset('images/Espera.png') }}" class="alm-legend-icon">
                            </span>
                            <span class="alm-font-size-0-80rem alm-color-0369a1 alm-font-weight-700 alm-margin-top-7px alm-text-align-center alm-line-height-1-1">Tengo Modelo</span>
                        </div>

                        <div class="legend-compact-item alm-width-calc-33-33-6pxpct alm-display-flex alm-flex-direction-column alm-align-items-center alm-padding-10px-6px alm-background-f8fafc alm-border-1-5px-solid-e2e8f0 alm-border-radius-8px alm-min-height-102px alm-justify-content-center">
                            <span class="alm-display-flex alm-background-ecfdf5 alm-border-2px-solid-10b981 alm-align-items-center alm-justify-content-center alm-width-54px alm-height-54px alm-border-radius-50pct alm-box-shadow-0-2px-4px-rgba-0-0-0-0-04 alm-flex-shrink-0">
                                <img src="{{ asset('images/Quality.png') }}" class="alm-legend-icon">
                            </span>
                            <span class="alm-font-size-0-80rem alm-color-047857 alm-font-weight-700 alm-margin-top-7px alm-text-align-center alm-line-height-1-1">Aprobado</span>
                        </div>

                        <div class="legend-compact-item alm-width-calc-33-33-6pxpct alm-display-flex alm-flex-direction-column alm-align-items-center alm-padding-10px-6px alm-background-f8fafc alm-border-1-5px-solid-e2e8f0 alm-border-radius-8px alm-min-height-102px alm-justify-content-center">
                            <span class="alm-display-flex alm-background-fef2f2 alm-border-2px-solid-ef4444 alm-align-items-center alm-justify-content-center alm-width-54px alm-height-54px alm-border-radius-50pct alm-box-shadow-0-2px-4px-rgba-0-0-0-0-04 alm-flex-shrink-0">
                                <img src="{{ asset('images/Quality.png') }}" class="alm-legend-icon">
                            </span>
                            <span class="alm-font-size-0-80rem alm-color-b91c1c alm-font-weight-700 alm-margin-top-7px alm-text-align-center alm-line-height-1-1">Rechazado</span>
                        </div>

                        <div class="legend-compact-item alm-width-calc-33-33-6pxpct alm-display-flex alm-flex-direction-column alm-align-items-center alm-padding-10px-6px alm-background-f8fafc alm-border-1-5px-solid-e2e8f0 alm-border-radius-8px alm-min-height-102px alm-justify-content-center">
                            <span class="alm-display-flex alm-background-fef9c3 alm-border-2px-solid-eab308 alm-align-items-center alm-justify-content-center alm-width-54px alm-height-54px alm-border-radius-50pct alm-box-shadow-0-2px-4px-rgba-0-0-0-0-04 alm-flex-shrink-0">
                                <img src="{{ asset('images/Quality.png') }}" class="alm-legend-icon">
                            </span>
                            <span class="alm-font-size-0-80rem alm-color-854d0e alm-font-weight-700 alm-margin-top-7px alm-text-align-center alm-line-height-1-1">Mixto</span>
                        </div>

                        <div class="legend-compact-item alm-width-calc-33-33-6pxpct alm-display-flex alm-flex-direction-column alm-align-items-center alm-padding-10px-6px alm-background-f8fafc alm-border-1-5px-solid-e2e8f0 alm-border-radius-8px alm-min-height-102px alm-justify-content-center">
                            <span class="alm-display-flex alm-background-f0fdf4 alm-border-2px-solid-059669 alm-align-items-center alm-justify-content-center alm-width-54px alm-height-54px alm-border-radius-50pct alm-box-shadow-0-2px-4px-rgba-0-0-0-0-04 alm-flex-shrink-0">
                                <img src="{{ asset('images/pdf-view.png') }}" class="alm-legend-icon">
                            </span>
                            <span class="alm-font-size-0-80rem alm-color-15803d alm-font-weight-700 alm-margin-top-7px alm-text-align-center alm-line-height-1-1">Casting</span>
                        </div>

                        <div class="legend-compact-item alm-width-calc-33-33-6pxpct alm-display-flex alm-flex-direction-column alm-align-items-center alm-padding-10px-6px alm-background-f8fafc alm-border-1-5px-solid-e2e8f0 alm-border-radius-8px alm-min-height-102px alm-justify-content-center">
                            <span class="alm-display-flex alm-background-fdf2f8 alm-border-2px-solid-ec4899 alm-align-items-center alm-justify-content-center alm-width-54px alm-height-54px alm-border-radius-50pct alm-box-shadow-0-2px-4px-rgba-0-0-0-0-04 alm-flex-shrink-0">
                                <img src="{{ asset('images/Reproceso.png') }}" class="alm-legend-icon">
                            </span>
                            <span class="alm-font-size-0-80rem alm-color-be185d alm-font-weight-700 alm-margin-top-7px alm-text-align-center alm-line-height-1-1">Reproceso</span>
                        </div>

                        <div class="legend-compact-item alm-width-calc-33-33-6pxpct alm-display-flex alm-flex-direction-column alm-align-items-center alm-padding-10px-6px alm-background-f8fafc alm-border-1-5px-solid-e2e8f0 alm-border-radius-8px alm-min-height-102px alm-justify-content-center">
                            <span class="alm-display-flex alm-background-f3e8ff alm-border-2px-solid-9333ea alm-align-items-center alm-justify-content-center alm-width-54px alm-height-54px alm-border-radius-50pct alm-box-shadow-0-2px-4px-rgba-0-0-0-0-04 alm-flex-shrink-0">
                                <img src="{{ asset('images/Proveedor.png') }}" class="alm-legend-icon">
                            </span>
                            <span class="alm-font-size-0-80rem alm-color-9333ea alm-font-weight-700 alm-margin-top-7px alm-text-align-center alm-line-height-1-1">Enviado a Proveedor</span>
                        </div>

                        <div class="legend-compact-item alm-width-calc-33-33-6pxpct alm-display-flex alm-flex-direction-column alm-align-items-center alm-padding-10px-6px alm-background-f8fafc alm-border-1-5px-solid-e2e8f0 alm-border-radius-8px alm-min-height-102px alm-justify-content-center">
                            <span class="alm-display-flex alm-background-ecfdf5 alm-border-2px-solid-10b981 alm-align-items-center alm-justify-content-center alm-width-54px alm-height-54px alm-border-radius-50pct alm-box-shadow-0-2px-4px-rgba-0-0-0-0-04 alm-flex-shrink-0">
                                <img src="{{ asset('images/Aprobado.png') }}" class="alm-legend-icon">
                            </span>
                            <span class="alm-font-size-0-80rem alm-color-047857 alm-font-weight-700 alm-margin-top-7px alm-text-align-center alm-line-height-1-1">Aprobado Final</span>
                        </div>

                        <div class="legend-compact-item alm-width-calc-33-33-6pxpct alm-display-flex alm-flex-direction-column alm-align-items-center alm-padding-10px-6px alm-background-f8fafc alm-border-1-5px-solid-e2e8f0 alm-border-radius-8px alm-min-height-102px alm-justify-content-center">
                            <span class="alm-display-flex alm-background-fef2f2 alm-border-2px-solid-dc2626 alm-align-items-center alm-justify-content-center alm-width-54px alm-height-54px alm-border-radius-50pct alm-box-shadow-0-2px-4px-rgba-0-0-0-0-04 alm-flex-shrink-0">
                                <img src="{{ asset('images/Rechazado.png') }}" class="alm-legend-icon">
                            </span>
                            <span class="alm-font-size-0-80rem alm-color-b91c1c alm-font-weight-700 alm-margin-top-7px alm-text-align-center alm-line-height-1-1">Rechazado Final</span>
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
                <div id="legend-zoom-tooltip" class="alm-position-fixed alm-display-none alm-pointer-events-none alm-z-index-99999 alm-background-rgba-255-255-255-0-98 alm-backdrop-filter-blur-10px alm-border-radius-12px alm-box-shadow-0-10px-25px-rgba-0-0-0-0-15 alm-border-3px-solid-cbd5e1 alm-padding-16px alm-width-170px alm-height-180px alm-flex-direction-column alm-align-items-center alm-justify-content-center alm-box-sizing-border-box alm-transition-transform-0-15s-cubic-bezier-0-175-0-885-0-32-1-25-opacity-0-15s-ease alm-opacity-0 alm-transform-scale-0-9 alm-font-family-Poppins-sans-serif">
                    <span id="legend-zoom-circle" class="alm-display-flex alm-align-items-center alm-justify-content-center alm-width-90px alm-height-90px alm-border-radius-50pct alm-box-shadow-0-4px-8px-rgba-0-0-0-0-06 alm-flex-shrink-0 alm-border-3px-solid-transparent">
                        <img id="legend-zoom-img" src="" class="alm-width-55px alm-height-55px alm-object-fit-contain">
                    </span>
                    <span id="legend-zoom-label" class="alm-font-size-1-08rem alm-font-weight-800 alm-margin-top-10px alm-text-align-center alm-line-height-1-2"></span>
                </div>

                <script>
                    function initLegendZoom() {
                        const tooltip = document.getElementById('legend-zoom-tooltip');
                        const zoomCircle = document.getElementById('legend-zoom-circle');
                        const zoomImg = document.getElementById('legend-zoom-img');
                        const zoomLabel = document.getElementById('legend-zoom-label');

                        if (!tooltip) return;

                        document.querySelectorAll('.legend-compact-item').forEach(item => {
                            item.addEventListener('mouseenter', (e) => {
                                const circle = item.querySelector('span');
                                const img = circle ? circle.querySelector('img') : null;
                                const label = item.querySelectorAll('span')[1];

                                if (!circle || !img || !label) return;

                                // Extract styles
                                const bgColor = circle.style.backgroundColor || window.getComputedStyle(circle).backgroundColor;
                                const borderColor = circle.style.borderColor || window.getComputedStyle(circle).borderColor;
                                const textColor = label.style.color || window.getComputedStyle(label).color;
                                const imgSrc = img.src;
                                const textContent = label.textContent;

                                // Apply to tooltip
                                tooltip.style.borderColor = borderColor;

                                zoomCircle.style.backgroundColor = bgColor;
                                zoomCircle.style.borderColor = borderColor;
                                zoomCircle.style.borderStyle = 'solid';
                                zoomCircle.style.borderWidth = '3px';

                                zoomImg.src = imgSrc;

                                zoomLabel.textContent = textContent;
                                zoomLabel.style.color = textColor;

                                tooltip.style.display = 'flex';
                                // Trigger animation frame for fade-in transition
                                requestAnimationFrame(() => {
                                    tooltip.style.opacity = '1';
                                    tooltip.style.transform = 'scale(1.05)';
                                });
                            });

                            item.addEventListener('mousemove', (e) => {
                                const offsetX = 20;
                                const offsetY = 20;

                                let posX = e.clientX + offsetX;
                                let posY = e.clientY + offsetY;

                                // Boundary checks
                                const tooltipWidth = 170;
                                const tooltipHeight = 180;

                                if (posX + tooltipWidth > window.innerWidth - 10) {
                                    posX = e.clientX - tooltipWidth - offsetX;
                                }
                                if (posY + tooltipHeight > window.innerHeight - 10) {
                                    posY = e.clientY - tooltipHeight - offsetY;
                                }

                                tooltip.style.left = `${posX}px`;
                                tooltip.style.top = `${posY}px`;
                            });

                            item.addEventListener('mouseleave', () => {
                                tooltip.style.opacity = '0';
                                tooltip.style.transform = 'scale(0.95)';
                                // Hide after transition
                                setTimeout(() => {
                                    if (tooltip.style.opacity === '0') {
                                        tooltip.style.display = 'none';
                                    }
                                }, 100);
                            });
                        });
                    }

                    if (document.readyState !== 'loading') {
                        initLegendZoom();
                    } else {
                        document.addEventListener('DOMContentLoaded', initLegendZoom);
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

                @foreach (['activa' => 'Documentos Activos (Dibujos y Ayudas)', 'inactiva' => 'Documentos Inactivos (Histórico)'] as $estado => $titulo)
                    @php
                        $registrosEstado = $registros->where('status', $estado);
                    @endphp

                    <div class="alm-table-card alm-margin-bottom-2em">
                        <div class="alm-table-header"
                            style="{{ $estado === 'inactiva' ? 'background: #6c757d; border-bottom: 2px solid #5a6268;' : '' }}">
                            <h2>{{ $titulo }}</h2>
                            <span class="alm-results-count">{{ $registrosEstado->count() }}
                                resultado{{ $registrosEstado->count() !== 1 ? 's' : '' }}</span>
                        </div>

                        @if ($estado === 'activa')
                        {{-- ── BARRA DE SINCRONIZACIÓN MANUAL (solo tabla Activa) ── --}}
                        <div id="sync-bar-activa" class="alm-display-flex alm-align-items-center alm-justify-content-space-between alm-flex-wrap-wrap alm-gap-10px alm-padding-10px-20px alm-background-linear-gradient-135deg-f0f9ff-0-e0f2fe-100pct alm-border-bottom-1px-solid-bae6fd alm-font-size-0-85rem alm-color-0369a1 alm-font-family-Poppins-sans-serif">
                            <span id="sync-status-almacen" class="alm-display-flex alm-align-items-center alm-gap-6px alm-font-weight-600">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#0369a1" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="alm-flex-shrink-0">
                                    <polyline points="23 4 23 10 17 10"></polyline><polyline points="1 20 1 14 7 14"></polyline>
                                    <path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"></path>
                                </svg>
                                <span id="sync-last-time-almacen">Sincronización automática activa</span>
                            </span>
                            <button
                                id="btn-sync-manual-almacen"
                                onclick="sincronizarDibujos(true)"
                                title="Sincronizar archivos ahora"
                                class="alm-display-inline-flex alm-align-items-center alm-gap-7px alm-padding-7px-18px alm-background-linear-gradient-135deg-0369a1-0-0284c7-100pct alm-color-fff alm-border-none alm-border-radius-8px alm-font-weight-700 alm-font-size-0-82rem alm-font-family-Poppins-sans-serif alm-cursor-pointer alm-box-shadow-0-3px-10px-rgba-3-105-161-0-25 alm-transition-all-0-2s-ease alm-white-space-nowrap"
                                onmouseover="this.style.transform='translateY(-1px)'; this.style.boxShadow='0 5px 15px rgba(3,105,161,0.35)';"
                                onmouseout="this.style.transform=''; this.style.boxShadow='0 3px 10px rgba(3,105,161,0.25)';"
                            >
                                <svg id="sync-icon-almacen" xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                    <polyline points="23 4 23 10 17 10"></polyline><polyline points="1 20 1 14 7 14"></polyline>
                                    <path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"></path>
                                </svg>
                                Sincronizar ahora
                            </button>
                        </div>
                        @endif

                        @if ($registrosEstado->isEmpty())
                            <div class="alm-empty">
                                <div class="alm-empty-icon">
                                    <img src="{{ asset('images/noPieces.png') }}" alt="Sin resultados"
                                        class="alm-width-64px alm-opacity-0-5">
                                </div>
                                <p>
                                    @if ($busquedaOt || $desde || $hasta)
                                        No se encontraron registros de {{ strtolower($titulo) }} con los filtros aplicados.
                                    @else
                                        Aún no hay registros en la bandeja de {{ strtolower($titulo) }}.
                                    @endif
                                </p>
                            </div>
                        @else
                            <div class="alm-table-scroll">
                                <table class="alm-table">
                                    <thead>
                                        <tr>
                                            <th
                                                style="width:30%; {{ $estado === 'inactiva' ? 'background: #6c757d; border-color: #5a6268;' : '' }}">
                                                Orden de Trabajo</th>
                                            <th style="width:12%; {{ $estado === 'inactiva' ? 'background: #6c757d; border-color: #5a6268;' : '' }}"
                                                class="d-text-center">Estado</th>
                                            <th style="width:12%; {{ $estado === 'inactiva' ? 'background: #6c757d; border-color: #5a6268;' : '' }}"
                                                class="d-text-center">Modelo</th>
                                            <th style="width:18%; {{ $estado === 'inactiva' ? 'background: #6c757d; border-color: #5a6268;' : '' }}"
                                                class="d-text-center">Último envío</th>
                                            <th style="width:10%; {{ $estado === 'inactiva' ? 'background: #6c757d; border-color: #5a6268;' : '' }}"
                                                class="d-text-center">Archivos</th>
                                            <th style="width:16%; {{ $estado === 'inactiva' ? 'background: #6c757d; border-color: #5a6268;' : '' }}"
                                                class="d-text-center">Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody id="alm-tbody-{{ $estado }}">
                                        @foreach ($registrosEstado as $reg)
                                            @php
                                                /** @var \App\Models\FundicionHistory $reg */
                                                $liberacionesReg = \App\Models\LiberacionModeloFundicion::where('ot', $reg->ot)
                                                    ->where('estado', '!=', 'pendiente')
                                                    ->get();
                                                /** @var \Illuminate\Database\Eloquent\Collection<\App\Models\LiberacionModeloFundicion> $liberacionesReg */
                                                $hasAprobados = $liberacionesReg->where('decision', 'aprobar')->isNotEmpty();

                                                $latestReproceso = null;
                                                if ($reg->rechazos_procesados) {
                                                    $latestReproceso = \App\Models\FundicionHistory::where('ot', 'LIKE', $reg->ot . '_R%')
                                                        ->orderBy('id', 'desc')
                                                        ->first();
                                                }
                                                // Corregimos la asignación para que la fila original NO actúe como si fuera el reproceso.
                                                // Así mantenemos las clases independientes (Ej: original para Bombillo, R1 para Fondo).
                                                $targetReg = $reg;

                                                // ── RESOLVER TODOS LOS REGISTROS RELACIONADOS ──
                                                $baseOtName = preg_replace('/_R\d+$/', '', $reg->ot);
                                                $relatedRecords = \App\Models\FundicionHistory::where('ot', '=', $baseOtName)
                                                    ->orWhere('ot', 'LIKE', $baseOtName . '_R%')
                                                    ->get();
                                                $allRelatedOtNames = $relatedRecords->pluck('ot')->toArray();
                                                $allOtNames = $allRelatedOtNames;

                                                $isReprocesoOT = preg_match('/_R\d+$/i', $reg->ot);
                                                $baseOtOfReg = preg_replace('/_R\d+$/i', '', $reg->ot);
                                                preg_match('/_R(\d+)$/i', $reg->ot, $mReg);
                                                $sReg = isset($mReg[1]) ? (int)$mReg[1] : 0;

                                                $allowFileCrossOt = function($fileOt) use ($reg, $isReprocesoOT, $baseOtOfReg, $sReg) {
                                                    if ($fileOt === $reg->ot) return true;
                                                    if (!$isReprocesoOT) return false;
                                                    $baseOtOfFile = preg_replace('/_R\d+$/i', '', $fileOt);
                                                    if ($baseOtOfFile !== $baseOtOfReg) return false;
                                                    preg_match('/_R(\d+)$/i', $fileOt, $mFile);
                                                    $sFile = isset($mFile[1]) ? (int)$mFile[1] : 0;
                                                    return $sFile < $sReg;
                                                };

                                                // Obtener clases activas para filtrar archivos del historial
                                                $activeClassesForOt = [];
                                                $confSource = $targetReg->ayudas_config ?? ($reg->ayudas_config ?? null);
                                                if (!empty($confSource)) {
                                                    $configs = is_string($confSource) ? json_decode($confSource, true) : $confSource;
                                                    if (is_array($configs)) {
                                                        foreach ($configs as $val) {
                                                            $val = strtolower($val);
                                                            if (str_contains($val, 'opcional')) continue;
                                                            $parts = explode(',', $val);
                                                            foreach (['candado obturador', 'cabeza de soplo', 'obturador', 'bombillo', 'embudo', 'corona', 'plato', 'molde', 'fondo'] as $kc) {
                                                                foreach ($parts as $p) {
                                                                    if (trim($p) === $kc) {
                                                                        $activeClassesForOt[] = $kc;
                                                                    }
                                                                }
                                                            }
                                                        }
                                                    }
                                                }
                                                if (empty($activeClassesForOt)) {
                                                    $po = \App\Models\PreOrdenFundicion::where('ot', $reg->ot)->first();
                                                    if ($po) {
                                                        $filas = $po->filas;
                                                        if (is_string($filas)) {
                                                            $filas = json_decode($filas, true);
                                                        }
                                                        if (is_array($filas)) {
                                                            foreach ($filas as $f) {
                                                                $val = null;
                                                                if (isset($f['clase'])) {
                                                                    $val = strtolower($f['clase']);
                                                                } elseif (isset($f['clase_nombre'])) {
                                                                    $val = strtolower($f['clase_nombre']);
                                                                } elseif (isset($f['tipo_modelo'])) {
                                                                    $val = strtolower($f['tipo_modelo']);
                                                                }
                                                                if ($val) {
                                                                    $parts = explode(',', $val);
                                                                    foreach (['candado obturador', 'cabeza de soplo', 'obturador', 'bombillo', 'embudo', 'corona', 'plato', 'molde', 'fondo'] as $kc) {
                                                                        foreach ($parts as $p) {
                                                                            if (trim($p) === $kc) {
                                                                                $activeClassesForOt[] = $kc;
                                                                            }
                                                                        }
                                                                    }
                                                                }
                                                            }
                                                        }
                                                    }
                                                }
                                                // Filtrar clases activas basándose en las decisiones de Calidad
                                                /** @var \App\Models\FundicionHistory $reg */
                                                $isReproceso = preg_match('/_R\d+$/i', $reg->ot);
                                                if ($isReproceso) {
                                                    // Para reprocesos: usar TODAS las clases con decisión en la OT ACTUAL
                                                    // (tanto aprobadas como rechazadas), para que los archivos de todas
                                                    // las clases evaluadas aparezcan en el scan del filesystem.
                                                    $classesInCurrentOtRaw = \App\Models\LiberacionModeloFundicion::where('ot', '=', $reg->ot)
                                                        ->where('decision', '!=', 'pendiente')
                                                        ->pluck('tipo_modelo')
                                                        ->toArray();

                                                    $parsedCurrent = [];
                                                    foreach ($classesInCurrentOtRaw as $dc) {
                                                        $parts = explode(',', strtolower($dc));
                                                        foreach ($parts as $p) {
                                                            $p = trim($p);
                                                            if ($p !== '') $parsedCurrent[] = $p;
                                                        }
                                                    }

                                                    if (!empty($parsedCurrent)) {
                                                        // Usar las clases de la OT actual (aprobadas + rechazadas)
                                                        $activeClassesForOt = array_unique($parsedCurrent);
                                                    } else {
                                                        // Fallback: si la OT actual aún no tiene decisiones, mostrar las
                                                        // rechazadas de la OT anterior (las que se están re-procesando)
                                                        $prevOt = preg_replace_callback('/_R(\d+)$/i', function($m) {
                                                            $num = intval($m[1]) - 1;
                                                            return $num > 0 ? '_R' . $num : '';
                                                        }, $reg->ot);
                                                        $rejectedPrevRaw = \App\Models\LiberacionModeloFundicion::where('ot', '=', $prevOt)
                                                            ->where('decision', '=', 'rechazar')
                                                            ->pluck('tipo_modelo')
                                                            ->toArray();

                                                        $parsedPrev = [];
                                                        foreach ($rejectedPrevRaw as $dc) {
                                                            $parts = explode(',', strtolower($dc));
                                                            foreach ($parts as $p) {
                                                                $p = trim($p);
                                                                if ($p !== '') $parsedPrev[] = $p;
                                                            }
                                                        }
                                                        if (!empty($parsedPrev)) {
                                                            $activeClassesForOt = array_unique($parsedPrev);
                                                        }
                                                    }
                                                } else {
                                                    /** @var \App\Models\FundicionHistory $reg */
                                                    $hasLiberaciones = \App\Models\LiberacionModeloFundicion::where('ot', '=', $reg->ot)->exists();
                                                    if ($hasLiberaciones) {
                                                        $decidedClassesRaw = \App\Models\LiberacionModeloFundicion::where('ot', '=', $reg->ot)
                                                            ->where('decision', '!=', 'pendiente')
                                                            ->pluck('tipo_modelo')
                                                            ->toArray();

                                                        $parsedDecided = [];
                                                        foreach ($decidedClassesRaw as $dc) {
                                                            $parts = explode(',', strtolower($dc));
                                                            foreach ($parts as $p) {
                                                                $p = trim($p);
                                                                if ($p !== '') $parsedDecided[] = $p;
                                                            }
                                                        }

                                                        if (!empty($parsedDecided)) {
                                                            $activeClassesForOt = array_unique($parsedDecided);
                                                        }
                                                        // Si está vacío (solo pendientes), conservamos el activeClassesForOt poblado previamente (fallback normal)
                                                    }
                                                }

                                                if (empty($activeClassesForOt)) {
                                                    $activeClassesForOt = ['candado obturador', 'cabeza de soplo', 'obturador', 'bombillo', 'embudo', 'corona', 'plato', 'molde', 'fondo'];
                                                }
                                                $activeClassesForOt = array_values(array_unique($activeClassesForOt));

                                                // Para reprocesos, las decisiones de rechazo están en la OT anterior (_R0, _R1, ...)
                                                // Para OTs base, están en la misma OT.
                                                $otParaRechazados = $reg->ot;
                                                if (preg_match('/_R\d+$/i', $reg->ot)) {
                                                    $otParaRechazados = preg_replace_callback('/_R(\d+)$/i', function($m) {
                                                        $num = intval($m[1]) - 1;
                                                        return $num > 0 ? '_R' . $num : '';
                                                    }, $reg->ot);
                                                }
                                                $clasesRechazadas = \App\Models\LiberacionModeloFundicion::where('ot', $otParaRechazados)
                                                    ->where('decision', 'rechazar')
                                                    ->pluck('tipo_modelo')
                                                    ->map(function ($modelo) {
                                                        return strtolower(trim($modelo));
                                                    })
                                                    ->toArray();


                                                                // Cuando esta OT ES un reproceso (_R1, _R2...) y tiene
                                                                // pre-orden generada, los dibujos/ayudas de las clases
                                                                // rechazadas ya estan siendo trabajadas nuevamente:
                                                                // mostrarlas como aprobadas (limpiar clasesRechazadas).
                                                                $reprocesoTienePreOrden = false;
                                                                if (preg_match('/_R\d+$/i', $reg->ot) && !empty($clasesRechazadas)) {
                                                                    $reprocesoTienePreOrden = (
                                                                        $reg->pre_orden_sent
                                                                        || $reg->pre_orden_email_sent
                                                                        || \App\Models\PreOrdenFundicion::where('ot', $reg->ot)->exists()
                                                                    );
                                                                    if ($reprocesoTienePreOrden) {
                                                                        $clasesRechazadas = [];
                                                                    }
                                                                }
                                                $rechazadosDibujos = [];
                                                $rechazadosAyudas = [];
                                                $rechazadosOtros = [];
                                                $archivos = [];
                                                $dibujoBaseNames = [];
                                                foreach ($relatedRecords as $relRec) {
                                                    $relArchivos = is_array($relRec->almacen_archivos) ? $relRec->almacen_archivos : [];
                                                    foreach ($relArchivos as $archivo) {
                                                        $base = basename($archivo);
                                                        $fileLower = strtolower($archivo);
                                                        if (strpos($fileLower, 'ayudas_visuales') !== false || strpos($fileLower, 'ayudas-visuales') !== false) {
                                                            continue;
                                                        }
                                                        $knownClasses = ['candado obturador', 'cabeza de soplo', 'obturador', 'bombillo', 'embudo', 'corona', 'plato', 'molde', 'fondo'];
                                                        $hasKnownClass = false;
                                                        $foundClass = null;
                                                        foreach ($knownClasses as $kc) {
                                                            if (strpos($fileLower, $kc) !== false) {
                                                                $hasKnownClass = true;
                                                                $foundClass = $kc;
                                                                break;
                                                            }
                                                        }
                                                        if ($hasKnownClass) {
                                                            $matchesActive = in_array($foundClass, $activeClassesForOt);
                                                            $matchesRejected = in_array($foundClass, $clasesRechazadas);
                                                            if ($matchesRejected) {
                                                                if (!in_array($base, $dibujoBaseNames)) {
                                                                    $rechazadosDibujos[] = [
                                                                        'nombre' => $archivo,
                                                                        'ot' => $relRec->ot,
                                                                        'tipo' => 'dibujo',
                                                                        'origin' => 'dibujo',
                                                                    ];
                                                                    $dibujoBaseNames[] = $base;
                                                                    break;
                                                                }
                                                                continue;
                                                            }
                                                            if (!$matchesActive)
                                                                continue;
                                                        } else {
                                                            if (!$allowFileCrossOt($relRec->ot)) {
                                                                continue;
                                                            }
                                                        }
                                                        if (!in_array($base, $dibujoBaseNames)) {
                                                            $archivos[] = [
                                                                'nombre' => $archivo,
                                                                'ot' => $relRec->ot,
                                                                'tipo' => 'dibujo',
                                                                'origin' => 'dibujo',
                                                                'owner' => 'almacen',
                                                            ];
                                                            $dibujoBaseNames[] = $base;
                                                        }
                                                    }
                                                }
                                                $countDibujos = count($archivos);

                                                $ayudasArchivos = [];
                                                $otrosArchivos = [];
                                                $baseNames = [];
                                                $dibujoBaseNames = [];

                                                // --- NUEVO: Escanear ayudas visuales globales desde AYUDAS_FUNDICION ---
                                                $ayudasGlobalesBase = 'DOCUMENTACION_GIS/AYUDAS_FUNDICION';
                                                foreach ($activeClassesForOt as $activeClass) {
                                                    $classNameProper = ucfirst(strtolower($activeClass));
                                                    
                                                    $candidateDirs = [
                                                        $ayudasGlobalesBase . '/' . $classNameProper,
                                                        $ayudasGlobalesBase . '/' . $classNameProper . '/Fundicion'
                                                    ];
                                                    
                                                    foreach ($candidateDirs as $globalClassDir) {
                                                        if (\Illuminate\Support\Facades\Storage::disk('local')->exists($globalClassDir)) {
                                                            $files = \Illuminate\Support\Facades\Storage::disk('local')->files($globalClassDir);
                                                            foreach ($files as $f) {
                                                                $ext = strtolower(pathinfo($f, PATHINFO_EXTENSION));
                                                                if ($ext === 'pdf') {
                                                                    $base = basename($f);
                                                                    if (!in_array($base, $baseNames)) {
                                                                        $ayudasArchivos[] = [
                                                                            'nombre' => $classNameProper . '/' . $base,
                                                                            'url' => route('ayudas_fundicion.serve', ['clase' => $classNameProper, 'archivo' => $base]),
                                                                            'tipo' => 'ayuda',
                                                                            'ot' => $reg->ot,
                                                                        ];
                                                                        $baseNames[] = $base;
                                                                    }
                                                                }
                                                            }
                                                        }
                                                    }
                                                }

                                                $liberacionesPath = storage_path('app/public/liberaciones_pdf');

                                                foreach ($allOtNames as $otName) {
                                                    $otNameSanitized = trim(
                                                        preg_replace('/[\/\\\\]/', '', preg_replace('/\.\.+/', '', $otName)),
                                                    );

                                                    // 1. Escanear ayudas visuales de Almacen (Legacy y Nueva Estructura)
                                                    $ayudasDir = 'DOCUMENTACION_GIS/ALMACEN_FUNDICION/' . $otNameSanitized . '/ayudas_visuales';
                                                    $almacenRootScan = 'DOCUMENTACION_GIS/ALMACEN_FUNDICION/' . $otNameSanitized;

                                                    $scanDirs = [];
                                                    if (\Illuminate\Support\Facades\Storage::disk('local')->exists($ayudasDir)) {
                                                        $scanDirs[] = [
                                                            'path' => $ayudasDir,
                                                            'base_dir' => $ayudasDir,
                                                        ];
                                                    }
                                                    foreach (['Candado obturador', 'Cabeza de soplo', 'Obturador', 'Bombillo', 'Embudo', 'Corona', 'Plato', 'Molde', 'Fondo'] as $claseDir) {
                                                        $newAyDir = $almacenRootScan . '/' . $claseDir . '/Ayudas_Visuales';
                                                        if (\Illuminate\Support\Facades\Storage::disk('local')->exists($newAyDir)) {
                                                            $scanDirs[] = [
                                                                'path' => $newAyDir,
                                                                'base_dir' => $almacenRootScan,
                                                            ];
                                                        }
                                                        $legacyClaseAyDir = $ayudasDir . '/' . $claseDir;
                                                        if (\Illuminate\Support\Facades\Storage::disk('local')->exists($legacyClaseAyDir)) {
                                                            $scanDirs[] = [
                                                                'path' => $legacyClaseAyDir,
                                                                'base_dir' => $ayudasDir,
                                                            ];
                                                        }
                                                    }

                                                    foreach ($scanDirs as $sInfo) {
                                                        if (\Illuminate\Support\Facades\Storage::disk('local')->exists($sInfo['path'])) {
                                                            $files = \Illuminate\Support\Facades\Storage::disk('local')->allFiles($sInfo['path']);
                                                            foreach ($files as $f) {
                                                                $ext = strtolower(pathinfo($f, PATHINFO_EXTENSION));
                                                                $isPdf = $ext === 'pdf';
                                                                $isImage = in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp']);

                                                                if (!$isPdf && !$isImage)
                                                                    continue;

                                                                $fNorm = str_replace('\\', '/', $f);
                                                                $dirNorm = str_replace('\\', '/', $sInfo['base_dir']);
                                                                $relativePath = ltrim(str_replace($dirNorm, '', $fNorm), '/');
                                                                $base = basename($relativePath);

                                                                $fileLower = strtolower($relativePath);
                                                                $knownClasses = ['candado obturador', 'cabeza de soplo', 'obturador', 'bombillo', 'embudo', 'corona', 'plato', 'molde', 'fondo'];
                                                                $hasKnownClass = false;
                                                                foreach ($knownClasses as $kc) {
                                                                    if (strpos($fileLower, $kc) !== false) {
                                                                        $hasKnownClass = true;
                                                                        break;
                                                                    }
                                                                }
                                                                if ($hasKnownClass) {
                                                                    $matchesActive = false;
                                                                    $matchesRejected = false;
                                                                    foreach ($activeClassesForOt as $ac) {
                                                                        if (strpos($fileLower, $ac) !== false) {
                                                                            $matchesActive = true;
                                                                            break;
                                                                        }
                                                                    }
                                                                    foreach ($clasesRechazadas as $rc) {
                                                                        if (strpos($fileLower, $rc) !== false) {
                                                                            $matchesRejected = true;
                                                                            break;
                                                                        }
                                                                    }
                                                                    if ($matchesRejected) {
                                                                        if (!in_array($base, $baseNames)) {
                                                                            $rechazadosAyudas[] = [
                                                                                'nombre' => $relativePath,
                                                                                'url' => route('almacen.fundicion.serve', ['ot' => $otName, 'archivo' => $relativePath, 'tipo' => 'ayuda']),
                                                                                'tipo' => 'ayuda',
                                                                                'ot' => $otName,
                                                                            ];
                                                                            $baseNames[] = $base;
                                                                            break;
                                                                        }
                                                                        continue;
                                                                    }
                                                                    if (!$matchesActive)
                                                                        continue;
                                                                } else {
                                                                    if (!$allowFileCrossOt($otName)) {
                                                                        continue;
                                                                    }
                                                                }

                                                                if (str_starts_with($relativePath, 'preordenes/')) {
                                                                    if (!in_array($base, $baseNames)) {
                                                                        $otrosArchivos[] = [
                                                                            'nombre' => $relativePath,
                                                                            'url' => route('almacen.fundicion.serve', ['ot' => $otName, 'archivo' => $relativePath, 'tipo' => 'otro']),
                                                                            'tipo' => $isImage ? 'imagen' : 'otro',
                                                                            'ot' => $otName,
                                                                            'origin' => 'otro',
                                                                            'owner' => 'almacen',
                                                                        ];
                                                                        $baseNames[] = $base;
                                                                    }
                                                                } elseif ($isPdf) {
                                                                    if (!in_array($base, $baseNames)) {
                                                                        $ayudasArchivos[] = [
                                                                            'nombre' => $relativePath,
                                                                            'url' => route('almacen.fundicion.serve', ['ot' => $otName, 'archivo' => $relativePath, 'tipo' => 'ayuda']),
                                                                            'tipo' => 'ayuda',
                                                                            'ot' => $otName,
                                                                        ];
                                                                        $baseNames[] = $base;
                                                                    }
                                                                }
                                                            }
                                                        }
                                                    }

                                                    // 2. Escanear ayudas visuales de Calidad
                                                    $calidadDir = 'DOCUMENTACION_GIS/CALIDAD_FUNDICION/' . $otNameSanitized . '/ayudas_visuales/preordenes';
                                                    if (\Illuminate\Support\Facades\Storage::disk('local')->exists($calidadDir)) {
                                                        $files = \Illuminate\Support\Facades\Storage::disk('local')->allFiles($calidadDir);
                                                        foreach ($files as $f) {
                                                            $ext = strtolower(pathinfo($f, PATHINFO_EXTENSION));
                                                            $isPdf = $ext === 'pdf';
                                                            $isImage = in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp']);

                                                            if (!$isPdf && !$isImage)
                                                                continue;

                                                            $fNorm = str_replace('\\', '/', $f);
                                                            $dirNorm = str_replace('\\', '/', $calidadDir);
                                                            $relativePath = ltrim(str_replace($dirNorm, '', $fNorm), '/');
                                                            $base = basename($relativePath);

                                                            $fileLower = strtolower($relativePath);
                                                            $knownClasses = ['candado obturador', 'cabeza de soplo', 'obturador', 'bombillo', 'embudo', 'corona', 'plato', 'molde', 'fondo'];
                                                            $hasKnownClass = false;
                                                            foreach ($knownClasses as $kc) {
                                                                if (strpos($fileLower, $kc) !== false) {
                                                                    $hasKnownClass = true;
                                                                    break;
                                                                }
                                                            }
                                                            if ($hasKnownClass) {
                                                                    $matchesActive = false;
                                                                    $matchesRejected = false;
                                                                    foreach ($activeClassesForOt as $ac) {
                                                                        if (strpos($fileLower, $ac) !== false) {
                                                                            $matchesActive = true;
                                                                            break;
                                                                        }
                                                                    }
                                                                    foreach ($clasesRechazadas as $rc) {
                                                                        if (strpos($fileLower, $rc) !== false) {
                                                                            $matchesRejected = true;
                                                                            break;
                                                                        }
                                                                    }
                                                                    if ($matchesRejected) {
                                                                        if (!in_array($base, $baseNames)) {
                                                                            $rechazadosOtros[] = [
                                                                                'nombre' => $relativePath,
                                                                                'url' => route('almacen.fundicion.serve', ['ot' => $otName, 'archivo' => $relativePath, 'tipo' => 'otro']),
                                                                                'tipo' => 'otro',
                                                                                'ot' => $otName,
                                                                            ];
                                                                            $baseNames[] = $base;
                                                                            break;
                                                                        }
                                                                        continue;
                                                                    }
                                                                    if (!$matchesActive)
                                                                        continue;
                                                                } else {
                                                                if (!$allowFileCrossOt($otName)) {
                                                                    continue;
                                                                }
                                                            }

                                                            if (!in_array($base, $baseNames)) {
                                                                $origin = 'otro';
                                                                if (strpos($relativePath, 'documentos_aprobados') !== false) {
                                                                    $origin = 'aprobado';
                                                                } elseif (strpos($relativePath, 'documentos_rechazados') !== false) {
                                                                    $origin = 'rechazado';
                                                                }
                                                                $relativePathWithPrefix = 'preordenes/' . $relativePath;

                                                                $otrosArchivos[] = [
                                                                    'nombre' => $relativePathWithPrefix,
                                                                    'url' => route('almacen.fundicion.serve', ['ot' => $otName, 'archivo' => $relativePathWithPrefix, 'tipo' => 'otro', 'origin' => $origin]),
                                                                    'tipo' => $isImage ? 'imagen' : 'otro',
                                                                    'ot' => $otName,
                                                                    'origin' => $origin,
                                                                    'owner' => 'calidad',
                                                                ];
                                                                $baseNames[] = $base;
                                                            }
                                                        }
                                                    }

                                                    // 2b. Escanear Documentos_Aprobados y Documentos_Rechazados de Almacen y Calidad
                                                    $newDirs = [
                                                        ['dir' => 'DOCUMENTACION_GIS/ALMACEN_FUNDICION/' . $otNameSanitized . '/Documentos_Aprobados', 'origin' => 'aprobado', 'prefix' => 'Documentos_Aprobados/', 'owner' => 'almacen'],
                                                        ['dir' => 'DOCUMENTACION_GIS/ALMACEN_FUNDICION/' . $otNameSanitized . '/Documentos_Rechazados', 'origin' => 'rechazado', 'prefix' => 'Documentos_Rechazados/', 'owner' => 'almacen'],
                                                        ['dir' => 'DOCUMENTACION_GIS/CALIDAD_FUNDICION/' . $otNameSanitized . '/Documentos_Aprobados', 'origin' => 'aprobado', 'prefix' => 'Documentos_Aprobados/', 'owner' => 'calidad'],
                                                        ['dir' => 'DOCUMENTACION_GIS/CALIDAD_FUNDICION/' . $otNameSanitized . '/Documentos_Rechazados', 'origin' => 'rechazado', 'prefix' => 'Documentos_Rechazados/', 'owner' => 'calidad'],
                                                    ];

                                                    foreach ($newDirs as $dirInfo) {
                                                        $targetDir = $dirInfo['dir'];
                                                        $origin = $dirInfo['origin'];
                                                        $prefix = $dirInfo['prefix'];

                                                        if (\Illuminate\Support\Facades\Storage::disk('local')->exists($targetDir)) {
                                                            $files = \Illuminate\Support\Facades\Storage::disk('local')->allFiles($targetDir);
                                                            foreach ($files as $f) {
                                                                $ext = strtolower(pathinfo($f, PATHINFO_EXTENSION));
                                                                $isPdf = $ext === 'pdf';
                                                                $isImage = in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp']);

                                                                if (!$isPdf && !$isImage)
                                                                    continue;

                                                                $fNorm = str_replace('\\', '/', $f);
                                                                $dirNorm = str_replace('\\', '/', $targetDir);
                                                                $relativePath = ltrim(str_replace($dirNorm, '', $fNorm), '/');
                                                                $base = basename($relativePath);

                                                                $fileLower = strtolower($relativePath);
                                                                $knownClasses = ['candado obturador', 'cabeza de soplo', 'obturador', 'bombillo', 'embudo', 'corona', 'plato', 'molde', 'fondo'];
                                                                $hasKnownClass = false;
                                                                foreach ($knownClasses as $kc) {
                                                                    if (strpos($fileLower, $kc) !== false) {
                                                                        $hasKnownClass = true;
                                                                        break;
                                                                    }
                                                                }
                                                                if ($hasKnownClass) {
                                                                    $matchesActive = false;
                                                                    $matchesRejected = false;
                                                                    foreach ($activeClassesForOt as $ac) {
                                                                        if (strpos($fileLower, $ac) !== false) {
                                                                            $matchesActive = true;
                                                                            break;
                                                                        }
                                                                    }
                                                                    foreach ($clasesRechazadas as $rc) {
                                                                        if (strpos($fileLower, $rc) !== false) {
                                                                            $matchesRejected = true;
                                                                            break;
                                                                        }
                                                                    }
                                                                    if ($matchesRejected) {
                                                                        if (!in_array($base, $baseNames)) {
                                                                            $rechazadosOtros[] = [
                                                                                'nombre' => $relativePath,
                                                                                'url' => route('almacen.fundicion.serve', ['ot' => $otName, 'archivo' => $relativePath, 'tipo' => 'otro']),
                                                                                'tipo' => 'otro',
                                                                                'ot' => $otName,
                                                                            ];
                                                                            $baseNames[] = $base;
                                                                            break;
                                                                        }
                                                                        continue;
                                                                    }
                                                                    if (!$matchesActive)
                                                                        continue;
                                                                } else {
                                                                    if (!$allowFileCrossOt($otName)) {
                                                                        continue;
                                                                    }
                                                                }

                                                                if (!in_array($base, $baseNames)) {
                                                                    $relativePathWithPrefix = $prefix . $relativePath;
                                                                    $otrosArchivos[] = [
                                                                        'nombre' => $relativePathWithPrefix,
                                                                        'url' => route('almacen.fundicion.serve', ['ot' => $otName, 'archivo' => $relativePathWithPrefix, 'tipo' => 'otro', 'origin' => $origin]),
                                                                        'tipo' => $isImage ? 'imagen' : 'otro',
                                                                        'ot' => $otName,
                                                                        'origin' => $origin,
                                                                        'owner' => $dirInfo['owner'],
                                                                    ];
                                                                    $baseNames[] = $base;
                                                                }
                                                            }
                                                        }
                                                    }

                                                    // 3. Buscar PDFs generados en public/liberaciones_pdf (LDM y SCAR)
                                                    $otSanitizada = preg_replace('/[^\w\s\-]/', '', $otName);
                                                    $otSanitizada = preg_replace('/[\s]+/', '_', trim($otSanitizada));

                                                    if (file_exists($liberacionesPath)) {
                                                        // Buscar LDM PDFs
                                                        $ldmPattern = "{$liberacionesPath}/F-CCL-LDM_*_{$otSanitizada}*.pdf";
                                                        foreach (glob($ldmPattern) ?: [] as $f) {
                                                            $base = basename($f);
                                                            $fileLower = strtolower($base);
                                                            $knownClasses = ['candado obturador', 'cabeza de soplo', 'obturador', 'bombillo', 'embudo', 'corona', 'plato', 'molde', 'fondo'];
                                                            $hasKnownClass = false;
                                                            foreach ($knownClasses as $kc) {
                                                                if (strpos($fileLower, $kc) !== false) {
                                                                    $hasKnownClass = true;
                                                                    break;
                                                                }
                                                            }
                                                            if ($hasKnownClass) {
                                                                    $matchesActive = false;
                                                                    $matchesRejected = false;
                                                                    foreach ($activeClassesForOt as $ac) {
                                                                        if (strpos($fileLower, $ac) !== false) {
                                                                            $matchesActive = true;
                                                                            break;
                                                                        }
                                                                    }
                                                                    foreach ($clasesRechazadas as $rc) {
                                                                        if (strpos($fileLower, $rc) !== false) {
                                                                            $matchesRejected = true;
                                                                            break;
                                                                        }
                                                                    }
                                                                    if ($matchesRejected) {
                                                                        if (!in_array($base, $baseNames)) {
                                                                            $rechazadosOtros[] = [
                                                                                'nombre' => $relativePath,
                                                                                'url' => route('almacen.fundicion.serve', ['ot' => $otName, 'archivo' => $relativePath, 'tipo' => 'otro']),
                                                                                'tipo' => 'otro',
                                                                                'ot' => $otName,
                                                                            ];
                                                                            $baseNames[] = $base;
                                                                            break;
                                                                        }
                                                                        continue;
                                                                    }
                                                                    if (!$matchesActive)
                                                                        continue;
                                                                } else {
                                                                if (!$allowFileCrossOt($otName)) {
                                                                    continue;
                                                                }
                                                            }
                                                            if (!in_array($base, $baseNames)) {
                                                                $otrosArchivos[] = [
                                                                    'nombre' => $base,
                                                                    'url' => route('almacen.fundicion.serve', ['ot' => $otName, 'archivo' => $base, 'tipo' => 'liberacion']),
                                                                    'tipo' => 'liberacion',
                                                                    'ot' => $otName,
                                                                    'origin' => '',
                                                                    'owner' => 'calidad',
                                                                ];
                                                                $baseNames[] = $base;
                                                            }
                                                        }

                                                        // Buscar SCAR PDFs (digital y firmado)
                                                        $scarPattern = "{$liberacionesPath}/F-CCL-SCAR_*_{$otSanitizada}*.pdf";
                                                        $scarPattern2 = "{$liberacionesPath}/F-CCL-SCAR_{$otSanitizada}.pdf";
                                                        $scarFiles = array_merge(glob($scarPattern) ?: [], glob($scarPattern2) ?: []);
                                                        foreach (array_unique($scarFiles) as $f) {
                                                            $base = basename($f);
                                                            $fileLower = strtolower($base);
                                                            $knownClasses = ['candado obturador', 'cabeza de soplo', 'obturador', 'bombillo', 'embudo', 'corona', 'plato', 'molde', 'fondo'];
                                                            $hasKnownClass = false;
                                                            foreach ($knownClasses as $kc) {
                                                                if (strpos($fileLower, $kc) !== false) {
                                                                    $hasKnownClass = true;
                                                                    break;
                                                                }
                                                            }
                                                            if ($hasKnownClass) {
                                                                    $matchesActive = false;
                                                                    $matchesRejected = false;
                                                                    foreach ($activeClassesForOt as $ac) {
                                                                        if (strpos($fileLower, $ac) !== false) {
                                                                            $matchesActive = true;
                                                                            break;
                                                                        }
                                                                    }
                                                                    foreach ($clasesRechazadas as $rc) {
                                                                        if (strpos($fileLower, $rc) !== false) {
                                                                            $matchesRejected = true;
                                                                            break;
                                                                        }
                                                                    }
                                                                    if ($matchesRejected) {
                                                                        if (!in_array($base, $baseNames)) {
                                                                            $rechazadosOtros[] = [
                                                                                'nombre' => $relativePath,
                                                                                'url' => route('almacen.fundicion.serve', ['ot' => $otName, 'archivo' => $relativePath, 'tipo' => 'otro']),
                                                                                'tipo' => 'otro',
                                                                                'ot' => $otName,
                                                                            ];
                                                                            $baseNames[] = $base;
                                                                            break;
                                                                        }
                                                                        continue;
                                                                    }
                                                                    if (!$matchesActive)
                                                                        continue;
                                                                } else {
                                                                if (!$allowFileCrossOt($otName)) {
                                                                    continue;
                                                                }
                                                            }
                                                            if (!in_array($base, $baseNames)) {
                                                                $otrosArchivos[] = [
                                                                    'nombre' => $base,
                                                                    'url' => route('almacen.fundicion.serve', ['ot' => $otName, 'archivo' => $base, 'tipo' => 'liberacion']),
                                                                    'tipo' => 'liberacion',
                                                                    'ot' => $otName,
                                                                    'origin' => '',
                                                                    'owner' => 'calidad',
                                                                ];
                                                                $baseNames[] = $base;
                                                            }
                                                        }
                                                    }
                                                }

                                                // Aplicar filtros de visibilidad
                                                $userPerfil = Auth::user()->perfil;
                                                $filteredOtros = [];
                                                foreach ($otrosArchivos as $archivo) {
                                                    $nameLow = strtolower($archivo['nombre']);
                                                    $isPreorden = (
                                                        ((in_array($archivo['tipo'], ['otro', 'imagen']) || str_starts_with($archivo['nombre'], 'preordenes/')) &&
                                                            strpos($nameLow, 'ldm') === false &&
                                                            strpos($nameLow, 'scar') === false &&
                                                            strpos($nameLow, 'confirmacion') === false &&
                                                            strpos($nameLow, 'liberacion') === false) ||
                                                        strpos($nameLow, 'escaneado') !== false
                                                    );

                                                    // Si el archivo es de Calidad y no es preorden ni confirmacion, ocultar hasta que se envie la alerta
                                                    if ($archivo['owner'] === 'calidad' && !$isPreorden && strpos($nameLow, 'confirmacion') === false) {
                                                        /** @var \App\Models\FundicionHistory|null $fileHistory */
                                                        $fileHistory = $relatedRecords->firstWhere('ot', $archivo['ot']);
                                                        $status = $fileHistory ? $fileHistory->calidad_revision_status : null;
                                                        $calidadAlertaEnviada = (
                                                            in_array($status, ['calidad_aprobado', 'calidad_rechazado', 'calidad_mixto', 'calidad_parcial', 'casting_aprobado']) ||
                                                            \App\Models\ScarModelo::where('ot', '=', $archivo['ot'])->where('estatus', '=', 'alertado')->exists()
                                                        );
                                                        if (!$calidadAlertaEnviada) {
                                                            continue; // Ocultar para todos los perfiles, incluidos Admin y Supervisor
                                                        }
                                                    }

                                                    if ($userPerfil != 1 && $userPerfil != 2) {
                                                        if ($userPerfil == 4 || $userPerfil == 3) { // Calidad o Master
                                                            // Calidad/Master solo ve preordenes si pre_orden_email_sent es true
                                                            if ($isPreorden) {
                                                                /** @var \App\Models\FundicionHistory|null $fileHistory */
                                                                $fileHistory = $relatedRecords->firstWhere('ot', $archivo['ot']);
                                                                if (!$fileHistory || !$fileHistory->pre_orden_email_sent) {
                                                                    continue;
                                                                }
                                                            }
                                                        } elseif ($userPerfil == 5) { // Almacén
                                                            // Almacén ve preordenes y confirmaciones (calidad ya se filtró arriba)
                                                        }
                                                    }
                                                    $filteredOtros[] = $archivo;
                                                }
                                                $otrosArchivos = $filteredOtros;

                                                $archivosAprobados = [];
                                                $archivosRechazados = [];
                                                foreach ($otrosArchivos as $archivo) {
                                                    $nameLow = strtolower($archivo['nombre']);
                                                    $baseLow = strtolower(basename($archivo['nombre']));
                                                    if (strpos($nameLow, 'documentos_rechazados') !== false) {
                                                        $archivosRechazados[] = $archivo;
                                                    } elseif (strpos($nameLow, 'documentos_aprobados') !== false) {
                                                        $archivosAprobados[] = $archivo;
                                                    } elseif (
                                                        strpos($baseLow, 'pre-orden') !== false ||
                                                        strpos($baseLow, 'preorden') !== false ||
                                                        strpos($baseLow, 'confirmacion') !== false ||
                                                        strpos($baseLow, 'escaneado_fundicion') !== false
                                                    ) {
                                                        $archivosAprobados[] = $archivo;
                                                    } elseif (
                                                        strpos($baseLow, 'rechazado') !== false ||
                                                        strpos($baseLow, 'scar') !== false
                                                    ) {
                                                        $archivosRechazados[] = $archivo;
                                                    } else {
                                                        $archivosAprobados[] = $archivo;
                                                    }
                                                }
                                                $countAprobados = count($archivosAprobados);
                                                $countRechazados = count($archivosRechazados);

                                                $countAyudas = count($ayudasArchivos);
                                                $countOtros = count($otrosArchivos);

                                                // ── CALCULAR APROBADOS Y RECHAZADOS DEL ÚLTIMO VEREDICTO DE CADA CLASE ──
                                                // (Calculado ANTES de showControlCard para poder usarlos en la lógica de visibilidad)
                                                $liberacionesAll = \App\Models\LiberacionModeloFundicion::where('ot', $targetReg->ot)->get();
                                                $latestLiberacionesByClass = [];
                                                foreach ($liberacionesAll as $lib) {
                                                    $tipo = $lib->tipo_modelo;
                                                    $libOt = $lib->ot;

                                                    preg_match('/_R(\d+)$/', $libOt, $matches);
                                                    $suffixNum = isset($matches[1]) ? (int) $matches[1] : 0;

                                                    if (!isset($latestLiberacionesByClass[$tipo]) || $suffixNum > $latestLiberacionesByClass[$tipo]['suffix']) {
                                                        $latestLiberacionesByClass[$tipo] = [
                                                            'lib' => $lib,
                                                            'suffix' => $suffixNum
                                                        ];
                                                    }
                                                }

                                                $aprobadosRaw = [];
                                                $rechazadosRaw = [];
                                                foreach ($latestLiberacionesByClass as $tipo => $data) {
                                                    $lib = $data['lib'];
                                                    if ($lib->estado !== 'pendiente') {
                                                        if ($lib->decision === 'aprobar') {
                                                            $aprobadosRaw[] = $tipo;
                                                        } elseif ($lib->decision === 'rechazar') {
                                                            $rechazadosRaw[] = $tipo;
                                                        }
                                                    }
                                                }

                                                // Filtrar por clases activas en esta versión de la OT
                                                $aprobados = array_values(array_filter($aprobadosRaw, function ($clase) use ($activeClassesForOt) {
                                                    return in_array(strtolower($clase), $activeClassesForOt);
                                                }));
                                                $rechazados = array_values(array_filter($rechazadosRaw, function ($clase) use ($activeClassesForOt) {
                                                    return in_array(strtolower($clase), $activeClassesForOt);
                                                }));

                                                $dibujosAprobados = [];
                                                $dibujosRechazados = [];
                                                $dibujosPendientes = [];

                                                foreach ($archivos as $dibujo) {
                                                    $found = false;
                                                    $nameLower = strtolower($dibujo['nombre']);
                                                    $knownClasses = ['candado obturador', 'cabeza de soplo', 'obturador', 'bombillo', 'embudo', 'corona', 'plato', 'molde', 'fondo'];
                                                    $foundClass = null;
                                                    foreach ($knownClasses as $kc) {
                                                        if (strpos($nameLower, $kc) !== false) {
                                                            $foundClass = $kc;
                                                            break;
                                                        }
                                                    }
                                                    
                                                    if ($foundClass) {
                                                        if (in_array($foundClass, $aprobados)) {
                                                            $dibujosAprobados[] = $dibujo;
                                                            $found = true;
                                                            break;
                                                        } elseif (in_array($foundClass, $rechazados)) {
                                                            $dibujosRechazados[] = $dibujo;
                                                            $found = true;
                                                        }
                                                    }
                                                    if (!$found) {
                                                        $dibujosPendientes[] = $dibujo;
                                                    }
                                                }

                                                $ayudasAprobados = [];
                                                $ayudasRechazados = [];
                                                $ayudasPendientes = [];

                                                foreach ($ayudasArchivos as $ayuda) {
                                                    $found = false;
                                                    $nameLower = strtolower($ayuda['nombre']);
                                                    $knownClasses = ['candado obturador', 'cabeza de soplo', 'obturador', 'bombillo', 'embudo', 'corona', 'plato', 'molde', 'fondo'];
                                                    $foundClass = null;
                                                    foreach ($knownClasses as $kc) {
                                                        if (strpos($nameLower, $kc) !== false) {
                                                            $foundClass = $kc;
                                                            break;
                                                        }
                                                    }
                                                    
                                                    if ($foundClass) {
                                                        if (in_array($foundClass, $aprobados)) {
                                                            $ayudasAprobados[] = $ayuda;
                                                            $found = true;
                                                            break;
                                                        } elseif (in_array($foundClass, $rechazados)) {
                                                            $ayudasRechazados[] = $ayuda;
                                                            $found = true;
                                                        }
                                                    }
                                                    if (!$found) {
                                                        $ayudasPendientes[] = $ayuda;
                                                    }
                                                }

                                                $otrosAprobados = $archivosAprobados;
                                                $otrosRechazados = $archivosRechazados;

                                                $countAprobados = count($otrosAprobados) + count($dibujosAprobados) + count($ayudasAprobados);
                                                
                                                // Combinar los rechazos que vienen del escaneo de clases con los de Documentos_Rechazados
                                                $rechazadosDibujos = array_merge($rechazadosDibujos, $dibujosRechazados);
                                                $rechazadosAyudas = array_merge($rechazadosAyudas, $ayudasRechazados);
                                                
                                                $countRechazados = count($archivosRechazados) + count($rechazadosDibujos) + count($rechazadosAyudas) + count($rechazadosOtros);
                                                $countPendientes = count($dibujosPendientes) + count($ayudasPendientes);

                                                $isReprocesoBadge = (bool) preg_match('/_R\d+$/i', $reg->ot);
                                                $count = $countAprobados + $countPendientes + $countRechazados;

                                                // ── CONTROL DE VISIBILIDAD DE LA CARD DE ALMACÉN ──
                                                // La card se muestra siempre que:
                                                //  (a) Calidad haya enviado al menos una alerta (hay aprobados o rechazados en BD), O
                                                //  (b) El status aún no ha sido procesado completamente
                                                // Importante: NO ocultar aunque el status sea 'calidad_aprobado' o similar,
                                                // porque pueden llegar nuevas alertas parciales o re-envíos.
                                                $hasVerdictosPendientes = count($aprobados) > 0 || count($rechazados) > 0;
                                                $isFinalized = ($targetReg->calidad_revision_status === 'casting_aprobado') && !$hasVerdictosPendientes;
                                                // Si hay clases que calidad marcó pero no han sido gestionadas por almacén, desbloquear la card
                                                if ($hasVerdictosPendientes) {
                                                    $isFinalized = false;
                                                }
                                                $showControlCard = ($estado === 'activa' && !$isFinalized);
                                                $hasFilesOrControl = ($count > 0 || $showControlCard);

                                                // DEBUG MARKER
                                                echo "<!-- DEBUG OT: {$reg->ot}, estado: {$estado}, isFinalized: " . ($isFinalized ? 'true' : 'false') . ", showControlCard: " . ($showControlCard ? 'true' : 'false') . " -->";


                                                $libStatus = $targetReg->calidad_revision_status ?? null;
                                                $fsmState = 'recibido';

                                                if ($libStatus === 'casting_aprobado') {
                                                    $icon = 'Proveedor.png';
                                                    $label = 'Enviado a Proveedor';
                                                    $fsmState = 'casting_aprobado';
                                                    $tooltip = 'Pre-orden de casting enviada al proveedor, proceso finalizado';
                                                    $borderColor = '#9333ea';
                                                    $bgColor = '#f3e8ff';
                                                    $textColor = '#9333ea';
                                                } elseif ($targetReg->casting_pdf_generated) {
                                                    $icon = 'pdf-view.png';
                                                    $label = 'Casting';
                                                    $fsmState = 'casting';
                                                    $tooltip = 'Pre-orden de casting generada, esperando envío';
                                                    $borderColor = '#059669';
                                                    $bgColor = '#f0fdf4';
                                                    $textColor = '#15803d';
                                                } elseif (in_array($libStatus, ['calidad_aprobado', 'calidad_parcial'])) {
                                                    $icon = 'Quality.png';
                                                    $label = 'Aprobado';
                                                    $fsmState = 'aprobado';
                                                    $tooltip = 'Modelo aprobado y liberado por Calidad';
                                                    $borderColor = '#10b981';
                                                    $bgColor = '#ecfdf5';
                                                    $textColor = '#047857';
                                                } elseif ($libStatus === 'calidad_rechazado') {
                                                    $icon = 'Quality.png';
                                                    $label = 'Rechazado';
                                                    $fsmState = 'rechazado';
                                                    $tooltip = 'Modelo rechazado por Calidad debido a desviaciones';
                                                    $borderColor = '#ef4444';
                                                    $bgColor = '#fef2f2';
                                                    $textColor = '#b91c1c';
                                                } elseif ($libStatus === 'calidad_mixto') {
                                                    $icon = 'Quality.png';
                                                    $label = 'Mixto';
                                                    $fsmState = 'mixto';
                                                    $tooltip = 'Liberación mixta por Calidad (clases aprobadas y rechazadas)';
                                                    $borderColor = '#eab308';
                                                    $bgColor = '#fef9c3';
                                                    $textColor = '#854d0e';
                                                } elseif (in_array($libStatus, ['pendiente', 'aprobado', 'rechazado', 'mixto'])) {
                                                    $icon = 'Revisando.png';
                                                    $label = 'En Revisión';
                                                    $fsmState = 'revisando';
                                                    $tooltip = 'Calidad está realizando la revisión del modelo';
                                                    $borderColor = '#f59e0b';
                                                    $bgColor = '#fffbeb';
                                                    $textColor = '#b45309';
                                                } elseif ($targetReg->pre_orden_email_sent) {
                                                    if (Auth::user()->perfil == 4) {
                                                        $icon = 'Recibido.png';
                                                        $label = 'Nuevo';
                                                        $fsmState = 'recibido';
                                                        $tooltip = 'Pre-orden de fabricación de modelo recibida, esperando revisión de Calidad';
                                                        $borderColor = '#cbd5e1';
                                                        $bgColor = '#f1f5f9';
                                                        $textColor = '#64748b';
                                                    } else {
                                                        if (!$targetReg->isAlmacenFullyProcessed()) {
                                                            $icon = 'Revisando.png';
                                                            $label = 'Proceso Parcial';
                                                            $fsmState = 'revisando';
                                                            $tooltip = 'Pre-orden parcial enviada, esperando clases restantes o revisión';
                                                            $borderColor = '#f59e0b';
                                                            $bgColor = '#fffbeb';
                                                            $textColor = '#b45309';
                                                        } else {
                                                            $icon = 'enviando.png';
                                                            $label = 'Correo Enviado';
                                                            $fsmState = 'correo_enviado';
                                                            $tooltip = 'Pre-orden enviada por correo electrónico, esperando revisión de Calidad';
                                                            $borderColor = '#818cf8';
                                                            $bgColor = '#e0e7ff';
                                                            $textColor = '#4f46e5';
                                                        }
                                                    }
                                                } elseif ($targetReg->pre_orden_sent) {
                                                    if (!$targetReg->isAlmacenFullyProcessed()) {
                                                        $icon = 'Revisando.png';
                                                        $label = 'Proceso Parcial';
                                                        $fsmState = 'revisando';
                                                        $tooltip = 'Pre-orden parcial generada, esperando procesar el resto de las clases';
                                                        $borderColor = '#f59e0b';
                                                        $bgColor = '#fffbeb';
                                                        $textColor = '#b45309';
                                                    } else {
                                                        $icon = 'pdf-view.png';
                                                        $label = 'Pre-Orden';
                                                        $fsmState = 'pre_orden';
                                                        $tooltip = 'Pre-orden de modelo generada y guardada, pendiente de enviar';
                                                        $borderColor = '#60a5fa';
                                                        $bgColor = '#eff6ff';
                                                        $textColor = '#2563eb';
                                                    }
                                                } elseif ($targetReg->tiene_modelo) {
                                                    if (!$targetReg->isAlmacenFullyProcessed()) {
                                                        $icon = 'Revisando.png';
                                                        $label = 'Proceso Parcial';
                                                        $fsmState = 'revisando';
                                                        $tooltip = 'Clases parciales indicadas con modelo físico, esperando las demás';
                                                        $borderColor = '#f59e0b';
                                                        $bgColor = '#fffbeb';
                                                        $textColor = '#b45309';
                                                    } else {
                                                        $icon = 'Espera.png';
                                                        $label = 'Tengo Modelo';
                                                        $fsmState = 'tiene_modelo';
                                                        $tooltip = 'Modelo físico disponible en Almacén, en espera de revisión por Calidad';
                                                        $borderColor = '#0ea5e9';
                                                        $bgColor = '#f0f9ff';
                                                        $textColor = '#0369a1';
                                                    }
                                                } elseif ($reg->rechazos_procesados) {
                                                    if (count($aprobados) > 0) {
                                                        $icon = 'Quality.png';
                                                        $label = 'Aprobado';
                                                        $fsmState = 'aprobado';
                                                        $tooltip = 'Clases aprobadas se conservan en este registro';
                                                        $borderColor = '#10b981';
                                                        $bgColor = '#ecfdf5';
                                                        $textColor = '#047857';
                                                    } else {
                                                        $icon = 'Rechazado.png';
                                                        $label = 'Rechazado';
                                                        $fsmState = 'rechazado';
                                                        $tooltip = 'Retornado hacia un nuevo ciclo de modelo (Reproceso)';
                                                        $borderColor = '#dc2626';
                                                        $bgColor = '#fef2f2';
                                                        $textColor = '#b91c1c';
                                                    }
                                                } elseif ($isReproceso && in_array($libStatus, [null, 'pendiente']) && !$targetReg->tiene_modelo && !$targetReg->pre_orden_sent && !$targetReg->pre_orden_email_sent) {
                                                    $icon = 'Rechazado.png';
                                                    $label = 'Rechazado';
                                                    $fsmState = 'rechazado';
                                                    $tooltip = 'Reproceso por rechazo de Calidad';
                                                    $borderColor = '#dc2626';
                                                    $bgColor = '#fef2f2';
                                                    $textColor = '#b91c1c';
                                                } else {
                                                    $icon = 'Recibido.png';
                                                    $label = 'Nuevo';
                                                    $fsmState = 'recibido';
                                                    $tooltip = 'Alerta inicial recibida, pendiente de procesar modelo por Almacén';
                                                    $borderColor = '#cbd5e1';
                                                    $bgColor = '#f1f5f9';
                                                    $textColor = '#64748b';
                                                }
                                            @endphp

                                            {{-- Fila principal --}}
                                            <tr data-ot="{{ $reg->ot }}" data-estado-real="{{ $fsmState }}" data-is-fully-processed="{{ $targetReg->isAlmacenFullyProcessed() ? 'true' : 'false' }}">
                                                <td>
                                                    <div class="alm-ot-label">{{ preg_replace('/_\d{8}_\d{6}_.*/', '', $reg->ot) }}
                                                    </div>
                                                    @if ($reg->status === 'inactiva')
                                                        <div class="alm-inactiva-note">
                                                            La carpeta fue eliminada por el administrador. Los PDFs de {{ $deptName }} se
                                                            conservan.
                                                        </div>
                                                    @endif
                                                </td>
                                                <td class="d-text-center">
                                                    <span class="badge-status badge-{{ $reg->status }}">
                                                        {{ $reg->status }}
                                                    </span>
                                                </td>
                                                <td class="d-text-center">
                                                    <div id="status-modelo-{{ $reg->ot }}">
                                                        <div class="status-modelo-container alm-display-inline-flex alm-flex-direction-column alm-align-items-center alm-gap-2px alm-padding-6px alm-border-radius-8px">
                                                            <span class="badge-modelo-icon" title="{{ $tooltip }}"
                                                                style="display: flex; align-items: center; justify-content: center; width: 52px; height: 52px; border-radius: 50%; background: {{ $bgColor }}; box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1); border: 2px solid {{ $borderColor }}; transition: all 0.2s ease;">
                                                                <img src="{{ asset('images/' . $icon) }}" alt="{{ $label }}"
                                                                    class="alm-width-34px alm-height-34px alm-object-fit-contain">
                                                            </span>
                                                            <span class="status-modelo-label"
                                                                style="font-size: 11px; font-weight: 700; color: {{ $textColor }}; margin-top: 4px; text-transform: uppercase; white-space: nowrap;">
                                                                {{ $label }}
                                                            </span>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="alm-date d-text-center">
                                                    {{ $reg->alert_sent_at ? $reg->alert_sent_at->format('d/m/Y H:i') : '—' }}
                                                </td>
                                                <td class="d-text-center">
                                                    <span class="badge-pdf-count">{{ $count }}</span>
                                                </td>
                                                <td class="d-text-center">
                                                    @if ($hasFilesOrControl)
                                                        <button class="btn-toggle-files"
                                                            data-target="files-{{ $estado }}-{{ $loop->index }}" data-ot="{{ $reg->ot }}"
                                                            id="toggle-btn-{{ $estado }}-{{ $loop->index }}" aria-expanded="false">
                                                            Ver Archivos
                                                        </button>
                                                    @else
                                                        <span class="d-text-subtle alm-font-size-0-85em">Sin archivos</span>
                                                    @endif
                                                </td>
                                            </tr>

                                            {{-- Fila desplegable de archivos --}}
                                            @if ($hasFilesOrControl)
                                                <tr class="alm-files-row" id="files-{{ $estado }}-{{ $loop->index }}">
                                                    <td colspan="6">

                                                        @if ($countPendientes > 0)
                                                            @if ($reg->alert_sent_at)
                                                                @php
                                                                    $pendientesGroups = [
                                                                        ['titulo' => 'Dibujos de Fundición', 'archivos' => $dibujosPendientes, 'color' => '#005194'],
                                                                        ['titulo' => 'Ayudas Visuales de Fundición', 'archivos' => $ayudasPendientes, 'color' => '#9c0300'],
                                                                    ];
                                                                @endphp
                                                                @foreach ($pendientesGroups as $group)
                                                                    @if (count($group['archivos']) > 0)
                                                                        <h3 style="margin-top: 15px; margin-bottom: 10px; color: {{ $group['color'] }}; border-bottom: 2px solid {{ $group['color'] }}; padding-bottom: 5px;">
                                                                            {{ $group['titulo'] }}
                                                                        </h3>
                                                                        <div class="alm-pdf-grid alm-success-box">
                                                                            @foreach ($group['archivos'] as $archivoInfo)
                                                                                @php
                                                                                    $tipoCls = $archivoInfo['tipo'] === 'ayuda' ? 'card-ayuda' : '';
                                                                                    $btnCls = $archivoInfo['tipo'] === 'ayuda' ? 'btn-ayuda-color' : '';
                                                                                @endphp
                                                                                <div class="dibujos-file-card {{ $tipoCls }}" style="animation-delay: {{ $loop->index * 0.05 }}s;">
                                                                                    <div class="file-icon-wrapper alm-cursor-pointer" title="Abrir PDF">
                                                                                        <img src="{{ asset('images/pdf-view-shadow.png') }}" class="file-icon icon-default">
                                                                                        <img src="{{ asset('images/pdf-view.png') }}" class="file-icon icon-hover">
                                                                                    </div>
                                                                                    <div class="file-name alm-cursor-pointer" title="Abrir PDF" onclick="almacenVerPdf('{{ $archivoInfo['ot'] }}', '{{ $archivoInfo['nombre'] }}', '{{ $archivoInfo['tipo'] }}')">
                                                                                        {{ basename($archivoInfo['nombre']) }}
                                                                                    </div>
                                                                                    <div class="file-actions">
                                                                                        <button class="btn-dibujos btn-dibujos-sm btn-ver {{ $btnCls }}" onclick="almacenVerPdf('{{ $archivoInfo['ot'] }}', '{{ $archivoInfo['nombre'] }}', '{{ $archivoInfo['tipo'] }}')">Ver</button>
                                                                                    </div>
                                                                                </div>
                                                                            @endforeach
                                                                        </div>
                                                                    @endif
                                                                @endforeach
                                                            @else
                                                                <div class="alm-margin-top-15px alm-padding-14px-18px alm-background-rgba-0-81-148-0-06 alm-border-1-5px-dashed-005194 alm-border-radius-10px alm-color-005194 alm-font-size-0-93em">
                                                                    <strong>Documentos pendientes:</strong> Los dibujos y ayudas estarán disponibles una vez que Ingeniería envíe la alerta oficial desde el sistema de gestión documental.
                                                                </div>
                                                            @endif
                                                        @endif

                                                        @php
                                                            // $rechazadosDibujos y $rechazadosAyudas ya fueron poblados durante el
                                                            // scan del filesystem (clases rechazadas detectadas por nombre de archivo).
                                                            // Aquí solo AGREGAMOS los archivos de la carpeta Documentos_Rechazados
                                                            // clasificándolos por nombre — igual que el patrón de Calidad.
                                                            // NO sobreescribimos: usamos los arrays del scan como base.

                                                            foreach ($archivosRechazados as $rArchivo) {
                                                                $nameLow = strtolower($rArchivo['nombre']);
                                                                $ext     = pathinfo($nameLow, PATHINFO_EXTENSION);
                                                                $isImg   = in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp']);

                                                                $rArchivo['ot']   = $rArchivo['ot'] ?? $reg->ot;
                                                                $rArchivo['tipo'] = $rArchivo['tipo'] ?? ($isImg ? 'imagen' : 'otro');

                                                                if (strpos($nameLow, 'ayudas_visuales') !== false || strpos($nameLow, 'ayudas-visuales') !== false || $isImg) {
                                                                    if ($rArchivo['tipo'] === 'otro') $rArchivo['tipo'] = 'ayuda';
                                                                    $rechazadosAyudas[] = $rArchivo;
                                                                } elseif (strpos($nameLow, 'dibujos') !== false || strpos($nameLow, 'dibujo') !== false) {
                                                                    $rechazadosDibujos[] = $rArchivo;
                                                                } else {
                                                                    $rechazadosOtros[] = $rArchivo;
                                                                }
                                                            }
                                                        @endphp

                                                        @if (count($rechazadosAyudas) > 0)
                                                            <h3 class="alm-margin-top-25px alm-margin-bottom-10px alm-color-155724 alm-border-bottom-2px-solid-155724 alm-padding-bottom-5px">
                                                                Ayudas Visuales de Fundición</h3>
                                                            <div class="alm-pdf-grid alm-success-box">
                                                                @foreach ($rechazadosAyudas as $otroArchivo)
                                                                    @php
                                                                        $canDelete = false;
                                                                    @endphp
                                                                    <div class="dibujos-file-card card-otro" style="animation-delay: {{ $loop->index * 0.05 }}s; border-left-color: #155724;">
                                                                        <div class="file-icon-wrapper alm-cursor-pointer" title="Abrir PDF">
                                                                            <img src="{{ asset('images/pdf-view-shadow.png') }}" class="file-icon icon-default">
                                                                            <img src="{{ asset('images/pdf-view.png') }}" class="file-icon icon-hover">
                                                                        </div>
                                                                        <div class="file-name alm-cursor-pointer" title="Abrir PDF" onclick="almacenVerPdf('{{ $otroArchivo['ot'] }}', '{{ $otroArchivo['nombre'] }}', 'ayuda')">
                                                                            {{ basename($otroArchivo['nombre']) }}
                                                                        </div>
                                                                        <div class="file-actions alm-flex-gap-5">
                                                                        <button class="btn-dibujos btn-dibujos-sm btn-ver alm-background-color-155724 alm-color-white" onclick="almacenVerPdf('{{ $otroArchivo['ot'] }}', '{{ $otroArchivo['nombre'] }}', 'ayuda')">Ver</button>
                                                                        </div>
                                                                    </div>
                                                                @endforeach
                                                            </div>
                                                        @endif

                                                        {{-- BLOQUE 4: Renombrar sección a "Documentos Aprobados" --}}
                                                        @if ($countAprobados > 0)
                                                            <h3 class="alm-margin-top-25px alm-margin-bottom-10px alm-color-155724 alm-border-bottom-2px-solid-155724 alm-padding-bottom-5px">
                                                                Documentos Aprobados</h3>
                                                            <div class="alm-pdf-grid alm-success-box">
                                                                @php
                                                                    $archivosAprobados = array_merge($dibujosAprobados, $ayudasAprobados, $otrosAprobados);
                                                                @endphp
                                                                @foreach ($archivosAprobados as $otroArchivo)
                                                                    @php
                                                                        $canDelete = false;
                                                                        $fileOwner = $otroArchivo['owner'] ?? '';
                                                                        $fileNameLower = strtolower($otroArchivo['nombre']);
                                                                        if (strpos($fileNameLower, 'f-ccl-ldm') !== false || strpos($fileNameLower, 'scar') !== false) {
                                                                            $fileOwner = 'calidad';
                                                                        }
                                                                        $userPerfil = Auth::user()->perfil;
                                                                        
                                                                        $alertSent = false;
                                                                        if ($fileOwner === 'almacen') {
                                                                            $alertSent = (bool)($targetReg->pre_orden_email_sent || $targetReg->pre_orden_sent);
                                                                        } elseif ($fileOwner === 'calidad') {
                                                                            $alertSent = in_array($targetReg->calidad_revision_status, ['calidad_aprobado', 'calidad_rechazado', 'calidad_mixto', 'calidad_parcial', 'casting_aprobado']);
                                                                        }
                                                                        
                                                                        if (!$alertSent) {
                                                                            if ($userPerfil == 1 || $userPerfil == 2 || $userPerfil == 3) {
                                                                                $canDelete = true;
                                                                            } elseif ($userPerfil == 5 && $fileOwner === 'almacen') {
                                                                                $canDelete = true;
                                                                            } elseif (($userPerfil == 4 || $userPerfil == 3) && $fileOwner === 'calidad') {
                                                                                $canDelete = true;
                                                                            }
                                                                        }
                                                                    @endphp
                                                                    @if ($otroArchivo['tipo'] === 'imagen')
                                                                        <div class="dibujos-file-card card-otro card-imagen" style="animation-delay: {{ $loop->index * 0.05 }}s; border-left-color: #0369a1;">
                                                                            <div class="file-icon-wrapper alm-cursor-pointer" title="Ver imagen">
                                                                                <img src="{{ $otroArchivo['url'] }}" class="file-icon-img-thumb alm-width-100pct alm-height-80px alm-object-fit-cover alm-border-radius-6px alm-border-1px-solid-bae6fd">
                                                                            </div>
                                                                            <div class="file-name alm-cursor-pointer" title="Ver imagen" onclick="almacenVerPdf('{{ $otroArchivo['ot'] }}', '{{ $otroArchivo['nombre'] }}', 'otro')">
                                                                                {{ basename($otroArchivo['nombre']) }}
                                                                            </div>
                                                                            <div class="file-actions alm-flex-gap-5">
                                                                                <button class="btn-dibujos btn-dibujos-sm btn-ver alm-background-color-0369a1 alm-color-white" onclick="almacenVerPdf('{{ $otroArchivo['ot'] }}', '{{ $otroArchivo['nombre'] }}', 'otro')">Ver</button>
                                                                                @if ($canDelete)
                                                                                <button class="btn-dibujos btn-dibujos-sm btn-eliminar alm-bg-danger-white" onclick="almacenEliminarOtroArchivo('{{ $otroArchivo['ot'] }}', '{{ $otroArchivo['nombre'] }}', '{{ $otroArchivo['tipo'] }}', this, '{{ $otroArchivo['origin'] ?? '' }}')">Eliminar</button>
                                                                                @endif
                                                                            </div>
                                                                        </div>
                                                                    @else
                                                                        <div class="dibujos-file-card card-otro" style="animation-delay: {{ $loop->index * 0.05 }}s; border-left-color: #155724;">
                                                                            <div class="file-icon-wrapper alm-cursor-pointer" title="Abrir PDF">
                                                                                <img src="{{ asset('images/pdf-view-shadow.png') }}" class="file-icon icon-default">
                                                                                <img src="{{ asset('images/pdf-view.png') }}" class="file-icon icon-hover">
                                                                            </div>
                                                                            <div class="file-name alm-cursor-pointer" title="Abrir PDF" onclick="almacenVerPdf('{{ $otroArchivo['ot'] }}', '{{ $otroArchivo['nombre'] }}', '{{ $otroArchivo['tipo'] }}')">
                                                                                {{ basename($otroArchivo['nombre']) }}
                                                                            </div>
                                                                            <div class="file-actions alm-flex-gap-5">
                                                                                <button class="btn-dibujos btn-dibujos-sm btn-ver alm-background-color-155724 alm-color-white" onclick="almacenVerPdf('{{ $otroArchivo['ot'] }}', '{{ $otroArchivo['nombre'] }}', '{{ $otroArchivo['tipo'] }}')">Ver</button>
                                                                                @if ($canDelete)
                                                                                <button class="btn-dibujos btn-dibujos-sm btn-eliminar alm-bg-danger-white" onclick="almacenEliminarOtroArchivo('{{ $otroArchivo['ot'] }}', '{{ $otroArchivo['nombre'] }}', '{{ $otroArchivo['tipo'] }}', this, '{{ $otroArchivo['origin'] ?? '' }}')">Eliminar</button>
                                                                                @endif
                                                                            </div>
                                                                        </div>
                                                                    @endif
                                                                @endforeach
                                                            </div>
                                                        @endif








                                                        @if (count($rechazadosDibujos) > 0 || count($rechazadosAyudas) > 0 || count($rechazadosOtros) > 0)
                                                            @if (count($rechazadosDibujos) > 0)
                                                                <h3 class="alm-margin-top-25px alm-margin-bottom-10px alm-color-9c0300 alm-border-bottom-2px-solid-9c0300 alm-padding-bottom-5px">
                                                                    Dibujos Rechazados</h3>
                                                                <div class="alm-pdf-grid alm-background-color-fef2f2 alm-padding-15px alm-border-radius-8px alm-border-1px-solid-fecaca">
                                                                    @foreach ($rechazadosDibujos as $otroArchivo)
                                                                        @php
                                                                            $canDelete = false;
                                                                        @endphp
                                                                        <div class="dibujos-file-card card-otro" style="animation-delay: {{ $loop->index * 0.05 }}s; border-left-color: #9c0300;">
                                                                            <div class="file-icon-wrapper alm-cursor-pointer" title="Abrir PDF">
                                                                                <img src="{{ asset('images/pdf-view-shadow.png') }}" class="file-icon icon-default">
                                                                                <img src="{{ asset('images/pdf-view.png') }}" class="file-icon icon-hover">
                                                                            </div>
                                                                            <div class="file-name alm-cursor-pointer" title="Abrir PDF" onclick="almacenVerPdf('{{ $otroArchivo['ot'] }}', '{{ $otroArchivo['nombre'] }}', 'dibujo')">
                                                                                {{ basename($otroArchivo['nombre']) }}
                                                                            </div>
                                                                            <div class="file-actions alm-flex-gap-5">
                                                                            <button class="btn-dibujos btn-dibujos-sm btn-ver alm-background-color-9c0300 alm-color-white" onclick="almacenVerPdf('{{ $otroArchivo['ot'] }}', '{{ $otroArchivo['nombre'] }}', 'dibujo')">Ver</button>
                                                                            </div>
                                                                        </div>
                                                                    @endforeach
                                                                </div>
                                                            @endif



                                                            @if (count($rechazadosAyudas) > 0)
                                                                <h3 class="alm-margin-top-25px alm-margin-bottom-10px alm-color-15803d alm-border-bottom-2px-solid-15803d alm-padding-bottom-5px">
                                                                    Ayudas Visuales de Fundición</h3>
                                                                <div class="alm-pdf-grid alm-success-box">
                                                                    @foreach ($rechazadosAyudas as $otroArchivo)
                                                                        @php
                                                                            $canDelete = false;
                                                                        @endphp
                                                                        <div class="dibujos-file-card card-ayuda" style="animation-delay: {{ $loop->index * 0.05 }}s;">
                                                                            <div class="file-icon-wrapper alm-cursor-pointer" title="Abrir PDF">
                                                                                <img src="{{ asset('images/pdf-view-shadow.png') }}" class="file-icon icon-default">
                                                                                <img src="{{ asset('images/pdf-view.png') }}" class="file-icon icon-hover">
                                                                            </div>
                                                                            <div class="file-name alm-cursor-pointer" title="Abrir PDF" onclick="almacenVerPdf('{{ $otroArchivo['ot'] }}', '{{ $otroArchivo['nombre'] }}', 'ayuda')">
                                                                                {{ basename($otroArchivo['nombre']) }}
                                                                            </div>
                                                                            <div class="file-actions alm-flex-gap-5">
                                                                            <button class="btn-dibujos btn-dibujos-sm btn-ver alm-background-color-15803d alm-color-white" onclick="almacenVerPdf('{{ $otroArchivo['ot'] }}', '{{ $otroArchivo['nombre'] }}', 'ayuda')">Ver</button>
                                                                            </div>
                                                                        </div>
                                                                    @endforeach
                                                                </div>
                                                            @endif



                                                            @if (count($rechazadosOtros) > 0)
                                                                <h3 class="alm-margin-top-25px alm-margin-bottom-10px alm-color-9c0300 alm-border-bottom-2px-solid-9c0300 alm-padding-bottom-5px">
                                                                    Documentos Rechazados</h3>
                                                                <div class="alm-pdf-grid alm-background-color-fef2f2 alm-padding-15px alm-border-radius-8px alm-border-1px-solid-fecaca">
                                                                    @foreach ($rechazadosOtros as $otroArchivo)
                                                                        @php
                                                                            $canDelete = false;
                                                                            $fileOwner = $otroArchivo['owner'] ?? '';
                                                                            $userPerfil = Auth::user()->perfil;
                                                                            $alertSent = false;
                                                                            if ($fileOwner === 'almacen') {
                                                                                $alertSent = (bool)($targetReg->pre_orden_email_sent || $targetReg->pre_orden_sent);
                                                                            } elseif ($fileOwner === 'calidad') {
                                                                                $alertSent = in_array($targetReg->calidad_revision_status, ['calidad_aprobado', 'calidad_rechazado', 'calidad_mixto', 'calidad_parcial', 'casting_aprobado']);
                                                                            }
                                                                            if ($userPerfil == 1 || $userPerfil == 2) $canDelete = true;
                                                                            elseif ($userPerfil == 5 && $fileOwner === 'almacen') $canDelete = true;
                                                                            elseif ($userPerfil == 4 && $fileOwner === 'calidad') $canDelete = true;
                                                                        @endphp
                                                                        <div class="dibujos-file-card card-otro" style="animation-delay: {{ $loop->index * 0.05 }}s; border-left-color: #9c0300;">
                                                                            <div class="file-icon-wrapper alm-cursor-pointer" title="Abrir PDF">
                                                                                <img src="{{ asset('images/pdf-view-shadow.png') }}" class="file-icon icon-default">
                                                                                <img src="{{ asset('images/pdf-view.png') }}" class="file-icon icon-hover">
                                                                            </div>
                                                                            <div class="file-name alm-cursor-pointer" title="Abrir PDF" onclick="almacenVerPdf('{{ $otroArchivo['ot'] }}', '{{ $otroArchivo['nombre'] }}', '{{ $otroArchivo['tipo'] }}')">
                                                                                {{ basename($otroArchivo['nombre']) }}
                                                                            </div>
                                                                            <div class="file-actions alm-flex-gap-5">
                                                                            <button class="btn-dibujos btn-dibujos-sm btn-ver alm-background-color-9c0300 alm-color-white" onclick="almacenVerPdf('{{ $otroArchivo['ot'] }}', '{{ $otroArchivo['nombre'] }}', '{{ $otroArchivo['tipo'] }}')">Ver</button>
                                                                            @if($canDelete && !$alertSent)
                                                                                <button class="btn-dibujos btn-dibujos-sm btn-eliminar alm-bg-danger-white" onclick="almacenEliminarOtroArchivo('{{ $otroArchivo['ot'] }}', '{{ $otroArchivo['nombre'] }}', '{{ $otroArchivo['tipo'] }}', this, '{{ $otroArchivo['origin'] ?? '' }}')" title="Eliminar archivo">
                                                                                    Eliminar
                                                                                </button>
                                                                            @endif
                                                                            </div>
                                                                        </div>
                                                                    @endforeach
                                                                </div>
                                                            @endif
                                                        @endif
                                                        @if ($showControlCard)
                                                                @php
                                                                    $esReproceso = (bool) preg_match('/_R\d+$/i', $targetReg->ot);
                                                                    // Para buscar liberaciones del reproceso, usar la OT anterior
                                                                    $previousOtForRechazo = $targetReg->ot;
                                                                    if ($esReproceso) {
                                                                        preg_match('/_R(\d+)$/i', $targetReg->ot, $matches);
                                                                        $rNum = (int)($matches[1] ?? 1);
                                                                        if ($rNum > 1) {
                                                                            $previousOtForRechazo = preg_replace('/_R\d+$/i', '_R' . ($rNum - 1), $targetReg->ot);
                                                                        } else {
                                                                            $previousOtForRechazo = preg_replace('/_R\d+$/i', '', $targetReg->ot);
                                                                        }
                                                                    }
                                                                    $rechazadosClases = \App\Models\LiberacionModeloFundicion::where('ot', $previousOtForRechazo)
                                                                        ->where('decision', 'rechazar')
                                                                        ->pluck('tipo_modelo')
                                                                        ->unique()
                                                                        ->filter(fn($v) => !empty($v))
                                                                        ->values()
                                                                        ->toArray();

                                                                    $otClasesActivas = $esReproceso
                                                                        ? array_map('strtolower', $rechazadosClases)
                                                                        : (is_array($reg->ayudas_config) ? array_map('strtolower', $reg->ayudas_config) : []);
                                                                    $clasesProcesadas = [];

                                                                    /** @var \App\Models\FundicionHistory $targetReg */
                                                                    $preOrdenesEnviadas = \App\Models\PreOrdenFundicion::where('ot', $targetReg->ot)->where('is_sent', 1)->get();
                                                                    foreach ($preOrdenesEnviadas as $po) {
                                                                        $filas = is_string($po->filas) ? json_decode($po->filas, true) : $po->filas;
                                                                        if (is_array($filas)) {
                                                                            foreach ($filas as $f) {
                                                                                if (!empty($f['clase'] ?? $f['clase_nombre'])) {
                                                                                    $clasesProcesadas[] = strtolower($f['clase'] ?? $f['clase_nombre']);
                                                                                }
                                                                            }
                                                                        }
                                                                    }

                                                                    $liberacionesFisicas = \App\Models\LiberacionModeloFundicion::where('ot', $targetReg->ot)
                                                                        ->where('tipo_origen', 'con_modelo')
                                                                        ->whereNotNull('tipo_modelo')
                                                                        ->where('tipo_modelo', '!=', '')
                                                                        ->pluck('tipo_modelo')->toArray();
                                                                    foreach ($liberacionesFisicas as $lf) {
                                                                        if (!empty($lf)) {
                                                                            $clasesProcesadas[] = strtolower($lf);
                                                                        }
                                                                    }
                                                                    $clasesProcesadas = array_filter(array_unique($clasesProcesadas), fn($v) => $v !== '');
                                                                    $clasesProcesadas = array_values($clasesProcesadas);

                                                                    $clasesActivasCubiertas = [];
                                                                    $clasesActivasFaltantes = [];
                                                                    foreach ($otClasesActivas as $clActiva) {
                                                                        $cubierta = false;
                                                                        foreach ($clasesProcesadas as $cp) {
                                                                            if ($cp === '' || $clActiva === '') continue;
                                                                            if (strpos($cp, strtolower($clActiva)) !== false || strpos(strtolower($clActiva), $cp) !== false) {
                                                                                $cubierta = true;
                                                                                break;
                                                                            }
                                                                        }
                                                                        if ($cubierta) {
                                                                            $clasesActivasCubiertas[] = $clActiva;
                                                                        } else {
                                                                            $clasesActivasFaltantes[] = $clActiva;
                                                                        }
                                                                    }

                                                                    $todasClasesProcesadas = count($otClasesActivas) > 0 && count($clasesActivasFaltantes) === 0;
                                                                    $algunaClaseProcesada  = count($clasesActivasCubiertas) > 0;
                                                                    $tienePreOrden = (bool)($targetReg->pre_orden_sent || $targetReg->pre_orden_email_sent);

                                                                    // Nunca deshabilitamos la tarjeta entera para que el usuario siempre pueda interactuar
                                                                    $controlDisabled = '';
                                                                    $hideControlCard = $hasVerdictosPendientes ? 'display: none;' : ''; // Siempre mostrar la tarjeta principal de controles
                                                                    $hideTengoModelo = $esReproceso ? 'display: none;' : '';
                                                                    $hideGenerarFormato = ($esReproceso || $tienePreOrden) ? 'display: none;' : '';
                                                                    $hideReprocesoPreOrden = ($esReproceso && !$tienePreOrden) ? '' : 'display: none;';
                                                                    $hideEditPreOrden = $tienePreOrden ? '' : 'display: none;';

                                                                    $clasesFisicamenteConfirmadas = [];
                                                                    $liberacionesFisicas = \App\Models\LiberacionModeloFundicion::where('ot', $targetReg->ot)
                                                                        ->where('tipo_origen', 'con_modelo')
                                                                        ->whereNotNull('tipo_modelo')
                                                                        ->where('tipo_modelo', '!=', '')
                                                                        ->get();
                                                                    foreach ($liberacionesFisicas as $lib) {
                                                                        $clasesArray = explode(',', $lib->tipo_modelo);
                                                                        foreach ($clasesArray as $c) {
                                                                            $clasesFisicamenteConfirmadas[] = strtolower(trim($c));
                                                                        }
                                                                    }
                                                                    $clasesFisicamenteConfirmadas = array_unique($clasesFisicamenteConfirmadas);

                                                                    $clasesFaltantesFisico = [];
                                                                    foreach ($otClasesActivas as $clActiva) {
                                                                        $cubierta = false;
                                                                        foreach ($clasesFisicamenteConfirmadas as $cp) {
                                                                            if ($cp === '' || $clActiva === '') continue;
                                                                            if (strpos($cp, strtolower($clActiva)) !== false || strpos(strtolower($clActiva), $cp) !== false) {
                                                                                $cubierta = true;
                                                                                break;
                                                                            }
                                                                        }
                                                                        if (!$cubierta) {
                                                                            $clasesFaltantesFisico[] = $clActiva;
                                                                        }
                                                                    }

                                                                    $poPendienteEnvio = \App\Models\PreOrdenFundicion::where('ot', $targetReg->ot)->where('is_sent', 0)->orderBy('id', 'desc')->first();
                                                                    $clasesParaEnvio = [];
                                                                    if ($poPendienteEnvio) {
                                                                        $filas = is_string($poPendienteEnvio->filas) ? json_decode($poPendienteEnvio->filas, true) : $poPendienteEnvio->filas;
                                                                        if (is_array($filas)) {
                                                                            foreach ($filas as $f) {
                                                                                if (!empty($f['clase'] ?? $f['clase_nombre'])) {
                                                                                    $clasesParaEnvio[] = strtolower($f['clase'] ?? $f['clase_nombre']);
                                                                                }
                                                                            }
                                                                        }
                                                                    }

                                                                    $clasesYaProcesadasJson     = json_encode(array_values($clasesActivasCubiertas));
                                                                    $clasesActivasFaltantesJson = json_encode(array_values($clasesActivasFaltantes));
                                                                    $todasClasesActivasJson     = json_encode(array_values($otClasesActivas));
                                                                    $clasesActivasNoEnviadasJson = json_encode(array_values($clasesActivasFaltantes));
                                                                    $clasesFaltantesFisicoJson = json_encode(array_values($clasesFaltantesFisico));
                                                                    $clasesParaEnvioJson = json_encode(array_values(array_unique($clasesParaEnvio)));

                                                                    $todasClasesEnviadas = $todasClasesProcesadas && !$poPendienteEnvio;
                                                                    $isFullySubmitted = $targetReg->tiene_modelo || $todasClasesEnviadas;
                                                                    $hideAllBtns = $isFullySubmitted ? 'display: none;' : '';
                                                                    $hideSendEmail = ($tienePreOrden && $poPendienteEnvio) ? '' : 'display: none;';
                                                                @endphp
                                                                <div class="lib-calidad-card" id="control-modelo-{{ md5($reg->ot) }}"
                                                                    style="{{ $controlDisabled }} {{ $hideControlCard }}">
                                                                    <div class="lib-calidad-card-header">
                                                                        <img src="{{ $esReproceso ? asset('images/Reproceso.png') : asset('images/almacen.png') }}" alt="Almacén"
                                                                            class="alm-icon-lg">
                                                                        <div class="alm-overflow-hidden alm-flex-1">
                                                                            <span class="lib-calidad-card-title">Control de Modelos &mdash;
                                                                                Almacén</span>
                                                                            <span
                                                                                class="lib-calidad-card-ot">{{ preg_replace('/_\d{8}_\d{6}_.*/', '', $reg->ot) }}</span>
                                                                        </div>
                                                                        @if (count($otClasesActivas) > 0)
                                                                            <div class="alm-flex-shrink-0 alm-display-flex alm-flex-direction-column alm-align-items-center alm-gap-2px">
                                                                                <span style="font-size:1.1em; font-weight:800; color:{{ $todasClasesProcesadas ? '#15803d' : ($algunaClaseProcesada ? '#0369a1' : '#ffffff') }};">
                                                                                    {{ count($clasesActivasCubiertas) }}/{{ count($otClasesActivas) }}
                                                                                </span>
                                                                                <span class="alm-font-size-0-65em alm-font-weight-600 alm-color-rgba-255-255-255-0-75 alm-letter-spacing-0-5px alm-text-transform-uppercase">clases</span>
                                                                                @if ($todasClasesProcesadas)
                                                                                    <img src="{{ asset('images/ready.png') }}" class="alm-width-18px alm-height-18px alm-margin-top-2px" alt="Listo">
                                                                                @endif
                                                                            </div>
                                                                        @endif
                                                                    </div>
                                                                    <div class="lib-calidad-card-body">
                                                                        <div class="lib-calidad-action-row">
                                                                            <h4 class="lib-calidad-card-prompt">
                                                                                @if ($isFullySubmitted)
                                                                                    <span class="alm-color-15803d alm-font-weight-700 alm-display-inline-flex alm-align-items-center alm-gap-8px">
                                                                                        <img src="{{ asset('images/ready.png') }}" class="alm-icon-md" alt="Listo">
                                                                                        El proceso ahora le pertenece a Calidad. Por favor, espera instrucciones para las clases enviadas.
                                                                                    </span>
                                                                                @elseif ($todasClasesProcesadas)
                                                                                    <span class="alm-color-0369a1 alm-font-weight-700 alm-display-inline-flex alm-align-items-center alm-gap-8px">
                                                                                        <img src="{{ asset('images/ready.png') }}" class="alm-icon-md" alt="Listo">
                                                                                        ¡Todas las clases procesadas! Falta enviar la alerta a Calidad.
                                                                                    </span>
                                                                                @elseif ($algunaClaseProcesada)
                                                                                    <span class="alm-color-0369a1 alm-font-weight-600">
                                                                                        Proceso parcial ({{ count($clasesActivasCubiertas) }}/{{ count($otClasesActivas) }} clases enviadas).
                                                                                        Puedes generar o enviar las pre-órdenes restantes.
                                                                                    </span>
                                                                                @elseif ($targetReg->tiene_modelo)
                                                                                    ¡Modelo recibido y procesado! Pendiente de que Calidad lo revise.
                                                                                @elseif ($targetReg->pre_orden_email_sent)
                                                                                    Alerta enviada a Calidad. En espera de su revisión y nuevo veredicto
                                                                                    de liberación.
                                                                                @elseif ($targetReg->pre_orden_sent)
                                                                                    Pre-orden lista. Puedes seguir editando los datos o enviarla por correo.
                                                                                @elseif ($esReproceso)
                                                                                    OT en re-proceso por rechazo de Calidad. Genera o edita la pre-orden
                                                                                    de modelo para iniciar el nuevo ciclo de fabricación.
                                                                                @else
                                                                                    ¿Ya cuentas con el modelo de esta OT o necesitas generar una
                                                                                    pre-orden?
                                                                                @endif
                                                                            </h4>
                                                                            <div class="lib-calidad-card-btns" style="{{ $hideAllBtns }}">
                                                                                <button class="btn-modelo btn-modelo-si"
                                                                                    onclick="abrirModalConfirmarModelo('{{ $targetReg->ot }}', '{{ md5($reg->ot) }}', {{ $clasesFaltantesFisicoJson }}, {{ $todasClasesActivasJson }})"
                                                                                    title="Sí, cuento con el modelo de esta OT"
                                                                                    style="{{ $hideTengoModelo }}">
                                                                                    <img src="{{ asset('images/Aprobado.png') }}" alt="Si">
                                                                                    <span>Tengo el Modelo</span>
                                                                                </button>
                                                                                <button class="btn-modelo btn-modelo-no"
                                                                                    onclick="abrirModalPreOrden('{{ $targetReg->ot }}', {{ $clasesYaProcesadasJson }})"
                                                                                    title="No cuento con él, generar formato PDF"
                                                                                    style="{{ $hideGenerarFormato }}">
                                                                                    <img src="{{ asset('images/pdf.png') }}" alt="PDF">
                                                                                    <span>No, generar formato</span>
                                                                                </button>
                                                                                <button class="btn-modelo btn-modelo-no"
                                                                                    onclick="abrirModalPreOrden('{{ $targetReg->ot }}', {{ $clasesYaProcesadasJson }})"
                                                                                    title="Generar / editar la pre-orden de fabricación de modelo"
                                                                                    style="{{ $hideReprocesoPreOrden }}">
                                                                                    <img src="{{ asset('images/pdf.png') }}" alt="Pre-Orden">
                                                                                    <span>Pre-Orden Modelo</span>
                                                                                </button>
                                                                                <button class="btn-modelo btn-modelo-edit"
                                                                                    onclick="abrirModalPreOrden('{{ $targetReg->ot }}', {{ $clasesYaProcesadasJson }})"
                                                                                    title="Editar información de la preorden existente"
                                                                                    style="{{ $hideEditPreOrden }}">
                                                                                    <img src="{{ asset('images/editar-informacion.png') }}"
                                                                                        alt="Editar">
                                                                                    <span>Editar Pre-orden</span>
                                                                                </button>
                                                                                <button class="btn-modelo btn-modelo-email"
                                                                                    onclick="abrirModalEnviarPreOrden('{{ $targetReg->ot }}', 'modelo', {{ $clasesParaEnvioJson }})"
                                                                                    title="{{ $esReproceso ? 'Enviar alerta a Calidad para iniciar revisión de re-proceso' : 'Enviar pre-orden por correo electrónico' }}"
                                                                                    style="{{ $hideSendEmail }}">
                                                                                    <img src="{{ asset('images/enviando.png') }}" alt="Enviar">
                                                                                    <span>{{ $esReproceso ? 'Enviar Alerta' : 'Enviar Correo' }}</span>
                                                                                </button>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                @endif

                                                                @php                                                                    /** @var \App\Models\FundicionHistory $reg */
                                                                    // Incluir todas las liberaciones donde Calidad ya emitió un veredicto (decisión != null),
                                                                    // sin importar el estado interno. Así Almacén puede ver y procesar los aprobados/rechazados
                                                                    // aunque su estado aún sea 'pendiente' de procesamiento por parte de Almacén.
                                                                    $liberaciones = \App\Models\LiberacionModeloFundicion::where('ot', $reg->ot)
                                                                        ->whereNotNull('decision')
                                                                        ->where('decision', '!=', '')
                                                                        ->get();
                                                                    /** @var \Illuminate\Database\Eloquent\Collection<\App\Models\LiberacionModeloFundicion> $liberaciones */
                                                                    $aprobados = $liberaciones->where('decision', 'aprobar')->pluck('tipo_modelo')->unique()->values()->toArray();
                                                                    $rechazados = $liberaciones->where('decision', 'rechazar')->pluck('tipo_modelo')->unique()->values()->toArray();
                                                                    
                                                                    $isCalidadAlerted = in_array($reg->calidad_revision_status, ['calidad_aprobado', 'calidad_rechazado', 'calidad_mixto', 'calidad_parcial', 'aprobado', 'rechazado', 'mixto', 'parcial', 'casting_aprobado']);
                                                                    $castingEmailSent = ($reg->calidad_revision_status === 'casting_aprobado');

                                                                    // Detectar si el último reproceso fue aprobado por Calidad.
                                                                    // En ese caso la card de Rechazados asume el rol de la card de Aprobados
                                                                    // para evitar mostrar dos cards en un caso que NO es mixto.
                                                                    $reprocesoAprobadoPorCalidad = $latestReproceso
                                                                        && in_array($latestReproceso->calidad_revision_status, ['aprobado', 'calidad_aprobado', 'calidad_parcial']);
                                                                @endphp

                                                                @if (count($aprobados) > 0 && $isCalidadAlerted)
                                                                    @php
                                                                    /** @var \App\Models\FundicionHistory $reg */
                                                                    $castingPre = \App\Models\PreOrdenFundicion::where('ot', $reg->ot)->where('pdf_filename', 'LIKE', '%Casting%')->first();
                                                                    $hasCastingPre = (bool) $castingPre;

                                                                    // Validar si todas las clases aprobadas tienen una pre-orden de casting enviada
                                                                    $todosCastingProcesados = count($aprobados) > 0;
                                                                    /** @var \Illuminate\Database\Eloquent\Collection<\App\Models\PreOrdenFundicion> $castingSent */
                                                                    $castingSent = \App\Models\PreOrdenFundicion::where('ot', $reg->ot)->where('pdf_filename', 'LIKE', '%Casting%')->where('is_sent', true)->get();
                                                                    $clasesCastingProcesadas = [];
                                                                    foreach ($castingSent as $po) {
                                                                        $filas = is_string($po->filas) ? json_decode($po->filas, true) : $po->filas;
                                                                        if (is_array($filas)) {
                                                                            foreach ($filas as $f) {
                                                                                if (!empty($f['clase'] ?? $f['clase_nombre'])) {
                                                                                    $clasesCastingProcesadas[] = strtolower($f['clase'] ?? $f['clase_nombre']);
                                                                                }
                                                                            }
                                                                        }
                                                                    }
                                                                    foreach ($aprobados as $clActiva) {
                                                                        $procesada = false;
                                                                        foreach ($clasesCastingProcesadas as $cp) {
                                                                            if (strpos($cp, strtolower($clActiva)) !== false || strpos(strtolower($clActiva), $cp) !== false) {
                                                                                $procesada = true;
                                                                                break;
                                                                            }
                                                                        }
                                                                        if (!$procesada) {
                                                                            $todosCastingProcesados = false;
                                                                            break;
                                                                        }
                                                                    }

                                                                    // $isCalidadAlerted se define arriba
                                                                    $castingEmailSent = ($reg->calidad_revision_status === 'casting_aprobado');
                                                                    $aprobCardDisabled = '';
                                                                    @endphp
                                                                    <div class="lib-calidad-card" id="control-almacen-aprobados-{{ md5($reg->ot) }}"
                                                                        style="margin-top: 15px; {{ $aprobCardDisabled }}">
                                                                        <div class="lib-calidad-card-header alm-background-linear-gradient-135deg-16a34a-15803d alm-border-bottom-2px-solid-rgba-22-163-74-0-5">
                                                                            <img src="{{ asset('images/almacen.png') }}" alt="Almacén"
                                                                                class="alm-icon-lg">
                                                                            <div class="alm-overflow-hidden">
                                                                                <span class="lib-calidad-card-title alm-color-ffffff">Control de
                                                                                    Modelos
                                                                                    &mdash; Almacén (Aprobados)</span>
                                                                                <span class="lib-calidad-card-ot alm-color-d1fae5">{{ preg_replace('/_\d{8}_\d{6}_.*/', '', $reg->ot) }}</span>
                                                                            </div>
                                                                        </div>
                                                                        <div class="lib-calidad-card-body">
                                                                            <div class="lib-calidad-action-row">
                                                                                <h4 class="lib-calidad-card-prompt">
                                                                                    @if ($castingEmailSent)
                                                                                        <span
                                                                                            class="alm-color-15803d alm-font-weight-700 alm-display-inline-flex alm-align-items-center alm-gap-8px">
                                                                                            <img src="{{ asset('images/ready.png') }}"
                                                                                                class="alm-icon-md"
                                                                                                alt="Listo">
                                                                                            Proceso de pre-orden finalizado correctamente. El correo ha sido
                                                                                            enviado
                                                                                            al proveedor. Favor de esperar nuevas instrucciones.
                                                                                        </span>
                                                                                    @elseif ($hasCastingPre)
                                                                                        Pre-orden de casting generada para los modelos:
                                                                                        <strong>{{ implode(', ', $aprobados) }}</strong>. Puedes editar los
                                                                                        datos o
                                                                                        enviar la pre-orden por correo.
                                                                                    @elseif ($reg->casting_pdf_generated)
                                                                                        Formatos LDM subidos. Procede a generar la Pre-Orden de Fabricación
                                                                                        de
                                                                                        Casting para los modelos:
                                                                                        <strong>{{ implode(', ', $aprobados) }}</strong>.
                                                                                    @else
                                                                                        Modelos Aprobados por Calidad:
                                                                                        <strong>{{ implode(', ', $aprobados) }}</strong>. Procede a subir
                                                                                        los
                                                                                        formatos F-CCL-LDM firmados para iniciar el casting.
                                                                                    @endif
                                                                                </h4>
                                                                                <div class="lib-calidad-card-btns">
                                                                                    @if ($castingEmailSent)
                                                                                        {{-- Controles ocultos tras finalizar el proceso --}}
                                                                                    @elseif ($hasCastingPre)
                                                                                        <button class="btn-modelo btn-modelo-si"
                                                                                            onclick="abrirModalPreOrdenCasting('{{ $reg->ot }}')"
                                                                                            class="alm-bg-success-white">
                                                                                            <img src="{{ asset('images/editar-informacion.png') }}"
                                                                                                alt="Editar">
                                                                                            <span>Editar Pre-orden</span>
                                                                                        </button>
                                                                                        <button class="btn-modelo btn-modelo-email"
                                                                                            onclick="abrirModalEnviarPreOrden('{{ $reg->ot }}', 'casting')"
                                                                                            class="alm-display-flex alm-background-color-033966 alm-color-white">
                                                                                            <img src="{{ asset('images/enviando.png') }}" alt="Enviar">
                                                                                            <span>Enviar Correo</span>
                                                                                        </button>
                                                                                    @elseif ($reg->casting_pdf_generated)
                                                                                        <button class="btn-modelo btn-modelo-si"
                                                                                            onclick="abrirModalPreOrdenCasting('{{ $reg->ot }}')"
                                                                                            class="alm-bg-success-white">
                                                                                            <img src="{{ asset('images/almacen.png') }}" alt="Preorden"
                                                                                                class="alm-width-16px alm-height-16px alm-filter-brightness-0-invert-1">
                                                                                            <span>Preorden de Casting</span>
                                                                                        </button>
                                                                                    @else
                                                                                        <button class="btn-modelo btn-modelo-si"
                                                                                            onclick="abrirModalGestionVeredicto('{{ $reg->ot }}', {{ json_encode($aprobados) }}, [])"
                                                                                            class="alm-bg-success-white">
                                                                                            <img src="{{ asset('images/Aprobado.png') }}" alt="Si">
                                                                                            <span>Procesar Aceptados</span>
                                                                                        </button>
                                                                                    @endif
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                @endif

                                                                {{-- Card 2: Rejected models --}}
                                                                @if (count($rechazados) > 0 && $isCalidadAlerted)
                                                                    @php
                                                                        $latestReproceso = null;
                                                                        /** @var \App\Models\FundicionHistory $reg */
                                                                        if ($reg->rechazos_procesados) {
                                                                            $latestReproceso = \App\Models\FundicionHistory::where('ot', 'LIKE', $reg->ot . '_R%')
                                                                                ->orderBy('id', 'desc')
                                                                                ->first();
                                                                        }

                                                                        $rechCardDisabled = '';
                                                                        $ultimoRechazadoPorCalidad = false;
                                                                        $reprocesoAprobadoPorCalidad = false;
                                                                        $castingEmailSentReproceso = false;
                                                                        if ($reg->rechazos_procesados) {
                                                                            $castingEmailSentReproceso = $latestReproceso && ($latestReproceso->calidad_revision_status === 'casting_aprobado');
                                                                            $ultimoRechazadoPorCalidad = $latestReproceso
                                                                                && in_array($latestReproceso->calidad_revision_status, ['rechazado', 'calidad_rechazado', 'mixto', 'calidad_mixto'])
                                                                                && !$latestReproceso->rechazos_procesados;
                                                                            $reprocesoAprobadoPorCalidad = $latestReproceso
                                                                                && in_array($latestReproceso->calidad_revision_status, ['aprobado', 'calidad_aprobado', 'calidad_parcial']);
                                                                            if ($castingEmailSentReproceso) {
                                                                                $rechCardDisabled = '';
                                                                            } elseif ($ultimoRechazadoPorCalidad || $reprocesoAprobadoPorCalidad) {

                                                                                $rechCardDisabled = '';
                                                                            } elseif (!$latestReproceso || $latestReproceso->pre_orden_email_sent || $latestReproceso->tiene_modelo) {
                                                                                $rechCardDisabled = '';
                                                                            }
                                                                        }
                                                                    @endphp

                                                                    <div class="lib-calidad-card" id="control-almacen-rechazados-{{ md5($reg->ot) }}"
                                                                        style="margin-top: 15px; {{ $rechCardDisabled }}">
                                                                        <div class="lib-calidad-card-header alm-background-linear-gradient-135deg-dc2626-b91c1c alm-border-bottom-2px-solid-rgba-220-38-38-0-5">
                                                                            <img src="{{ asset('images/Reproceso.png') }}" alt="Reproceso"
                                                                                class="alm-icon-lg">
                                                                            <div class="alm-overflow-hidden">
                                                                                <span class="lib-calidad-card-title alm-color-ffffff">Control de
                                                                                    Modelos
                                                                                    &mdash; Almacén (Rechazados)</span>
                                                                                <span class="lib-calidad-card-ot alm-color-fee2e2">{{ preg_replace('/_\d{8}_\d{6}_.*/', '', $reg->ot) }}</span>
                                                                            </div>
                                                                        </div>
                                                                        <div class="lib-calidad-card-body">
                                                                            <div class="lib-calidad-action-row">
                                                                                <h4 class="lib-calidad-card-prompt">
                                                                                    @if ($reg->rechazos_procesados)
                                                                                        @if ($latestReproceso)
                                                                                            <div class="alm-background-linear-gradient-to-right-f8fafc-f1f5f9 alm-border-left-4px-solid-0284c7 alm-padding-14px-18px alm-border-radius-8px alm-box-shadow-0-2px-6px-rgba-0-0-0-0-06 alm-margin-bottom-5px alm-display-inline-block">
                                                                                                <span class="alm-color-1e293b alm-font-weight-600 alm-display-inline-flex alm-align-items-center alm-gap-12px alm-font-size-1-05rem">
                                                                                                    <span class="alm-display-flex alm-align-items-center alm-justify-content-center alm-background-e0f2fe alm-width-38px alm-height-38px alm-border-radius-50pct alm-flex-shrink-0">
                                                                                                        <img src="{{ asset('images/redireccionar.png') }}" class="alm-width-22px alm-height-22px alm-filter-invert-36-sepia-87-saturate-1514-hue-rotate-176deg-brightness-94-contrast-101pct" alt="Info">
                                                                                                    </span>
                                                                                                    <span class="alm-line-height-1-45">
                                                                                                        El reproceso de la <strong class="alm-color-dc2626 alm-background-fee2e2 alm-padding-2px-6px alm-border-radius-4px alm-font-weight-800">{{ $reg->ot }}</strong> 
                                                                                                        se está trabajando en la nueva OT <strong class="alm-color-15803d alm-background-dcfce7 alm-padding-2px-6px alm-border-radius-4px alm-font-weight-800">{{ preg_replace('/_\d{8}_\d{6}_.*/', '', $latestReproceso->ot) }}</strong>.<br>
                                                                                                        <span class="alm-font-size-0-9rem alm-color-64748b alm-font-weight-500">Presiona el botón para redirigirte a la nueva Orden de Trabajo.</span>
                                                                                                    </span>
                                                                                                </span>
                                                                                            </div>
                                                                                        @else
                                                                                            Formatos de rechazo y SCAR subidos para los modelos:
                                                                                            <strong>{{ implode(', ', $rechazados) }}</strong>. Nueva pre-orden
                                                                                            de modelo
                                                                                            generada.
                                                                                        @endif
                                                                                    @else
                                                                                        Modelos Rechazados por Calidad:
                                                                                        <strong>{{ implode(', ', $rechazados) }}</strong>. Procede a subir
                                                                                        el
                                                                                        Formato de Rechazo y el SCAR correspondiente.
                                                                                    @endif
                                                                                </h4>
                                                                                <div class="lib-calidad-card-btns">
                                                                                    @if ($reg->rechazos_procesados)
                                                                                        @if ($latestReproceso)
                                                                                            <button class="btn-modelo btn-modelo-si"
                                                                                                onclick="const row = document.querySelector('tr[data-ot=\'{{ $latestReproceso->ot }}\']'); if(row) { row.scrollIntoView({behavior: 'smooth', block: 'center'}); row.animate([{ backgroundColor: '#86efac' }, { backgroundColor: 'transparent' }], { duration: 800, iterations: 3 }); } else { alert('La OT de reproceso se encuentra en otra página o filtro.'); }"
                                                                                                class="alm-display-flex alm-background-linear-gradient-135deg-0284c7-0369a1 alm-color-white alm-padding-12px-35px alm-font-size-1-15em alm-align-items-center alm-justify-content-center alm-min-height-52px alm-border-radius-10px alm-gap-10px alm-box-shadow-0-4px-12px-rgba-2-132-199-0-35 alm-border-none alm-cursor-pointer alm-transition-transform-0-2s-ease-box-shadow-0-2s-ease"
                                                                                                onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 6px 16px rgba(2, 132, 199, 0.45)';"
                                                                                                onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 12px rgba(2, 132, 199, 0.35)';"
                                                                                                title="Ir a la OT de re-proceso">
                                                                                                <img src="{{ asset('images/redireccionar.png') }}" alt="Ir" class="alm-width-24px alm-height-24px alm-filter-brightness-0-invert-1">
                                                                                                <span class="alm-font-weight-700 alm-letter-spacing-0-5px">Ir a la Nueva OT</span>
                                                                                            </button>
                                                                                        @else
                                                                                            <button class="btn-modelo btn-modelo-no alm-display-flex alm-background-color-b91c1c alm-color-white">
                                                                                                <img src="{{ asset('images/Rechazado.png') }}" alt="No">
                                                                                                <span>Rechazos Procesados</span>
                                                                                            </button>
                                                                                        @endif
                                                                                    @else
                                                                                        <button class="btn-modelo btn-modelo-no"
                                                                                            onclick="abrirModalGestionVeredicto('{{ $reg->ot }}', [], {{ json_encode($rechazados) }})"
                                                                                            class="alm-display-flex alm-background-color-b91c1c alm-color-white">
                                                                                            <img src="{{ asset('images/Rechazado.png') }}" alt="No">
                                                                                            <span>Procesar Rechazados</span>
                                                                                        </button>
                                                                                    @endif
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                @endif

                                                    </td>
                                                </tr>
                                            @endif
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>
                @endforeach
            </main>
        </div>
    </div>

    <div id="modalConfirmarModelo" class="alm-modal" role="dialog" aria-modal="true">
        <div class="alm-modal-content alm-max-width-1100px alm-border-radius-20px alm-border-2-5px-solid-0a8504 alm-overflow-hidden">
            <div class="alm-modal-header alm-background-linear-gradient-135deg-0a8504-064e03 alm-border-bottom-2px-solid-064e03 alm-padding-2-2em-2-5em-2em alm-position-relative">
                <div class="div-cerrar">
                    <button type="button" class="btn-cerrar" onclick="cerrarModalConfirmarModelo()">
                        <img class="img-cerrar" src="{{ asset('images/cerrar.png') }}" alt="Cerrar">
                    </button>
                </div>
                <div class="alm-display-flex alm-align-items-center alm-gap-18px">
                    <img src="{{ asset('images/Aprobado.png') }}"
                        class="alm-width-46px alm-height-46px alm-object-fit-contain alm-filter-drop-shadow-0-4px-8px-rgba-0-0-0-0-15"
                        alt="">
                    <div>
                        <h3
                            class="alm-color-fff alm-margin-0 alm-font-size-1-45em alm-font-weight-800 alm-font-family-Poppins-sans-serif">
                            Confirmar Disponibilidad del Modelo</h3>
                        <div id="confirmar-modelo-subtitle"
                            class="alm-color-rgba-255-255-255-0-9 alm-font-size-0-95em alm-margin-top-2px alm-font-weight-500 alm-font-family-Poppins-sans-serif">
                            OT: -</div>
                    </div>
                </div>
            </div>
            <div class="alm-modal-body alm-padding-2-2em-2-5em alm-background-fafafa alm-font-family-Poppins-sans-serif">
                <form id="formConfirmarModelo" enctype="multipart/form-data"
                      data-email-modelo="{{ env('EMAIL_PROVEEDOR_MODELOS', 'produccion@ssmetalf.mx,asistenteprod@ssmetalf.mx') }}"
                      data-email-calidad="{{ env('EMAIL_CALIDAD', 'inspecciontec@grupoindsaavedra.com') }}">
                    <input type="hidden" id="cm-ot" name="ot">
                    <input type="hidden" id="cm-id-hash" name="id_hash">

                    <div class="alm-padding-0-0-14px alm-color-334155 alm-font-size-0-97em">
                        <p class="alm-margin-bottom-12px alm-font-weight-500">¿Confirmas que cuentas físicamente con el modelo
                            para esta OT?</p>
                        <p
                            class="alm-background-fef9c3 alm-border-1px-solid-fde047 alm-border-radius-12px alm-padding-12px-18px alm-color-713f12 alm-font-size-0-9em alm-line-height-1-5 alm-margin-bottom-20px">
                            <strong>Documentos requeridos:</strong> Debes adjuntar los documentos que acrediten la
                            recepción del modelo (ej. remisión, hoja de entrega, fotos).
                        </p>
                    </div>

                    <div class="form-group alm-mb-20" id="div-cm-destinatario">
                        <label for="cm-destinatario" class="alm-font-weight-700 alm-color-334155 alm-display-block alm-margin-bottom-10px alm-font-family-Poppins-sans-serif alm-font-size-1-15em">
                            Notificar a Proveedor (correo electrónico):
                        </label>
                        <input type="text" id="cm-destinatario" name="destinatario" class="form-control alm-font-family-Poppins-sans-serif alm-font-size-1-1em alm-padding-12px-18px alm-height-auto alm-border-radius-10px">
                        <span class="alm-font-size-0-85em alm-color-64748b alm-margin-top-4px alm-display-block">Separa múltiples correos con comas.</span>
                    </div>

                    <div class="form-group alm-mb-20" id="div-cm-destinatario-calidad">
                        <label for="cm-destinatario-calidad" class="alm-font-weight-700 alm-color-334155 alm-display-block alm-margin-bottom-10px alm-font-family-Poppins-sans-serif alm-font-size-1-15em">
                            Notificar a Calidad (correo electrónico):
                        </label>
                        <input type="text" id="cm-destinatario-calidad" name="destinatario_calidad" class="form-control alm-font-family-Poppins-sans-serif alm-font-size-1-1em alm-padding-12px-18px alm-height-auto alm-border-radius-10px">
                        <span class="alm-font-size-0-85em alm-color-64748b alm-margin-top-4px alm-display-block">Copia para Calidad (Modelos).</span>
                    </div>

                    {{-- FECHA DE CONFIRMACIÓN / ENVÍO --}}
                    <div class="form-group alm-mb-20">
                        <label for="cm-fecha"
                            class="alm-font-weight-700 alm-color-334155 alm-display-block alm-margin-bottom-10px alm-font-family-Poppins-sans-serif alm-font-size-1-15em">
                            Fecha de Confirmación / Envío <span class="alm-text-dark-red">*</span>
                        </label>
                        <input type="date" id="cm-fecha" name="fecha" class="form-control alm-font-family-Poppins-sans-serif alm-font-size-1-1em alm-padding-12px-18px alm-height-auto alm-border-radius-10px">
                    </div>

                    <div class="form-group alm-mb-22">
                        <label class="alm-font-weight-700 alm-color-334155 alm-display-block alm-margin-bottom-8px alm-font-family-Poppins-sans-serif alm-font-size-1-15em">
                            Selecciona las clases (modelos) disponibles físicamente <span class="alm-text-dark-red">*</span>
                        </label>
                        <div id="cm-clases-container" class="alm-background-f8fafc alm-border-1px-solid-e2e8f0 alm-border-radius-12px alm-padding-15px alm-display-flex alm-flex-wrap-wrap alm-gap-15px">
                            <div class="alm-spinner alm-border-top-color-0284c7 alm-display-block alm-margin-5px-auto"></div>
                        </div>
                    </div>

                    <div class="form-group alm-mb-22">
                        <label
                            class="alm-font-weight-700 alm-color-334155 alm-display-block alm-margin-bottom-8px alm-font-family-Poppins-sans-serif alm-font-size-1-15em">Archivos
                            de la OT disponibles para adjuntar:</label>
                        <div id="cm-server-files-container"
                            class="alm-background-f8fafc alm-border-1px-solid-e2e8f0 alm-border-radius-12px alm-padding-15px alm-max-height-420px alm-overflow-y-auto alm-display-flex alm-flex-direction-column alm-gap-15px">
                            <div class="alm-spinner alm-border-top-color-0284c7 alm-display-block alm-margin-10px-auto alm-grid-column-1-1">
                            </div>
                        </div>
                    </div>

                    <div class="form-group alm-mb-22">
                        <label class="custom-file-upload-label alm-font-weight-700 alm-color-334155 alm-display-block alm-margin-bottom-10px alm-font-family-Poppins-sans-serif alm-font-size-1-15em">
                            Adjuntar documentos de recepción <span class="alm-text-dark-red">*</span>
                        </label>
                        <div class="custom-file-dropzone alm-border-2px-dashed-0a8504 alm-background-f0fdf4 alm-min-height-80px alm-position-relative alm-border-radius-12px alm-display-flex alm-flex-direction-column alm-align-items-center alm-justify-content-center alm-padding-12px alm-cursor-pointer">
                            <input type="file" id="cm-archivos" name="archivos[]" class="custom-file-input alm-position-absolute alm-width-100pct alm-height-100pct alm-opacity-0 alm-cursor-pointer">
                            <div class="dropzone-content">
                                <img src="{{ asset('images/anadir.png') }}" class="dropzone-icon alm-width-40px alm-height-40px alm-margin-bottom-8px alm-object-fit-contain">
                                <span class="dropzone-text alm-font-weight-700 alm-color-0a8504 alm-font-size-0-85em alm-text-align-center alm-font-family-Poppins-sans-serif">Arrastra
                                    archivos aquí o haz clic para buscar</span>
                                <span class="dropzone-subtext alm-font-size-0-7em alm-color-64748b alm-margin-top-2px alm-font-family-Poppins-sans-serif">Soporta
                                    múltiples archivos PDF o imágenes</span>
                            </div>
                        </div>
                        <div id="cm-archivos-list"
                            class="alm-margin-top-15px alm-background-f8fafc alm-border-1px-solid-e2e8f0 alm-border-radius-12px alm-padding-15px alm-max-height-420px alm-overflow-y-auto alm-display-none alm-grid-template-columns-repeat-auto-fill-minmax-200px-1fr alm-gap-12px alm-justify-items-center">
                        </div>
                    </div>

                    <div class="form-actions alm-text-align-center alm-margin-top-24px">
                        <button type="submit" class="btn-save-preorden alm-background-linear-gradient-135deg-0a8504-064e03 alm-box-shadow-0-4px-15px-rgba-10-133-4-0-35 alm-padding-12px-32px alm-border-none alm-border-radius-10px alm-color-fff alm-font-weight-700 alm-cursor-pointer alm-font-family-Poppins-sans-serif alm-font-size-1-05em alm-display-inline-flex alm-align-items-center alm-justify-content-center alm-gap-8px">
                            Confirmar y Registrar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div id="modalPreOrden" class="alm-modal">
        <div class="alm-modal-content">
            <div class="alm-modal-header">
                <div class="div-cerrar">
                    <button type="button" class="btn-cerrar" onclick="cerrarModalPreOrden()">
                        <img class="img-cerrar" src="{{ asset('images/cerrar.png') }}">
                    </button>
                </div>
                <h3>Pre-Orden para Fabricar Modelos (4ALM-17)</h3>

            </div>
            <div class="alm-modal-body">

                <div id="po-page-1" class="po-page">
                    <form id="formPreOrden">
                        <div class="form-grid">
                            <div class="form-group po-proveedor-group">
                                <label for="po-proveedor">Proveedor <span class="alm-text-danger">*</span>:</label>
                                <select id="po-proveedor" name="proveedor" class="form-control" required>
                                    <option value="SS Metal Foundry, S. de R. L. de C. V." selected>SS Metal Foundry, S. de R. L. de C. V.</option>
                                    <option value="Sociedad Cooperativa de Producción Jacarandas">Sociedad Cooperativa de Producción Jacarandas</option>
                                </select>
                            </div>
                            <div class="form-group po-fecha-group">
                                <label for="po-fecha">Fecha:</label>
                                <input type="date" id="po-fecha" name="fecha" class="form-control" required
                                    value="{{ date('Y-m-d') }}">
                            </div>
                            <div class="form-group po-folio-group">
                                <label for="po-folio">Folio:</label>
                                <input type="text" id="po-folio" name="folio" class="form-control" readonly
                                    value="MOD-{{ date('Y') }}-0000">
                            </div>
                            <div class="form-group po-moldura-group">
                                <label for="po-moldura">Moldura:</label>
                                <input type="text" id="po-moldura" name="moldura" class="form-control" readonly required>
                            </div>
                            <div class="form-group po-ot-group">
                                <label for="po-ot">Orden de Trabajo:</label>
                                <input type="text" id="po-ot" name="ot" class="form-control" readonly required>
                                <input type="hidden" id="po-ot-raw" name="ot_raw">
                            </div>
                        </div>

                        <div class="modal-table-container">
                            <table class="modal-table">
                                <thead>
                                    <tr>
                                        <th class="alm-width-16pct">Tipo de Modelo <span class="alm-text-danger">*</span></th>
                                        <th class="alm-w-12">Impresiones <span class="alm-text-danger">*</span></th>
                                        <th class="alm-w-12">Cantidad <span class="alm-text-danger">*</span></th>
                                        <th class="alm-width-22pct">Descripción <span class="alm-text-danger">*</span></th>
                                        <th class="alm-width-22pct">Código de Modelo</th>
                                        <th class="alm-w-12">Fecha Entrega</th>
                                        <th class="alm-width-6pct alm-text-align-center">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody id="alm-tbody-preorden">

                                </tbody>
                            </table>
                            <div class="alm-margin-top-10px alm-text-align-center">
                                <button type="button" id="btn-add-clase-po" class="btn-img-action alm-display-inline-block">
                                    <img src="{{ asset('images/anadir.png') }}" alt="Añadir" class="alm-width-40px">
                                </button>
                            </div>
                        </div>

                        <div class="form-group alm-margin-top-20px">
                            <div id="po-observaciones-cycle-prefix"
                                class="alm-display-none alm-padding-8px-12px alm-background-color-fee2e2 alm-border-left-4px-solid-ef4444 alm-color-991b1b alm-font-weight-bold alm-margin-bottom-8px alm-border-radius-4px alm-font-family-Poppins-sans-serif">
                            </div>
                            <label for="po-observaciones">Observaciones:</label>
                            <textarea id="po-observaciones" name="observaciones" class="form-control" rows="3"></textarea>
                        </div>

                        <div class="form-actions alm-margin-top-30px alm-text-align-center">
                            <button type="submit" class="btn-save-preorden" id="btn-submit-preorden">
                                Guardar y Descargar Pre-Orden (Fase 1)
                            </button>
                        </div>
                    </form>
                </div>


            </div>
        </div>
    </div>

    <div id="modalPreOrdenCasting" class="alm-modal" role="dialog" aria-modal="true">
        <div class="alm-modal-content alm-max-width-1800px alm-width-95vw alm-border-radius-20px alm-overflow-hidden alm-border-1-5px-solid-0284c7">
            <div class="alm-modal-header alm-background-linear-gradient-135deg-0369a1-0284c7 alm-padding-2-2em-2-5em-1-5em alm-position-relative">
                <div class="div-cerrar">
                    <button type="button" class="btn-cerrar" onclick="cerrarModalPreOrdenCasting()">
                        <img class="img-cerrar" src="{{ asset('images/cerrar.png') }}" alt="Cerrar">
                    </button>
                </div>
                <h3 class="alm-font-size-2em alm-margin-0 alm-font-family-Poppins-sans-serif alm-font-weight-700 alm-color-fff">
                    Pre-Orden de Fabricación de Casting (4ALM-17)</h3>
                <p id="poc-modal-subtitle" class="lib-modal-subtitle alm-color-bae6fd alm-font-size-1-15em alm-margin-top-8px alm-margin-bottom-0 alm-font-family-Poppins-sans-serif alm-font-weight-500">
                </p>

                <div
                    class="alm-display-flex alm-gap-10px alm-margin-top-25px alm-border-bottom-2px-solid-rgba-255-255-255-0-2 alm-padding-bottom-0 alm-align-items-center">
                    <button type="button" id="tab-poc-page-1" class="btn-po-tab active alm-border-none alm-padding-12px-25px alm-border-top-left-radius-12px alm-border-top-right-radius-12px alm-font-family-Poppins-sans-serif alm-font-weight-600 alm-font-size-1-05em alm-cursor-pointer alm-transition-all-0-2s-ease">
                        Proveedor 1
                    </button>
                    <button type="button" id="tab-poc-page-2" class="btn-po-tab alm-display-none alm-border-none alm-padding-12px-25px alm-border-top-left-radius-12px alm-border-top-right-radius-12px alm-font-family-Poppins-sans-serif alm-font-weight-600 alm-font-size-1-05em alm-cursor-pointer alm-transition-all-0-2s-ease">
                        Proveedor 2
                    </button>

                    <button type="button" id="btn-add-poc-page-2" class="btns btn-add-tab alm-display-flex alm-align-items-center alm-gap-6px alm-padding-8px-16px alm-background-rgba-255-255-255-0-15 alm-border-1-5px-dashed-rgba-255-255-255-0-5 alm-border-radius-8px alm-color-white alm-cursor-pointer alm-font-family-Poppins-sans-serif alm-font-size-0-9em alm-font-weight-500 alm-transition-all-0-2s-ease alm-margin-left-15px alm-height-auto">
                        <img src="{{ asset('images/anadir.png') }}"
                            class="alm-width-14px alm-height-14px alm-filter-brightness-0-invert-1" alt=""> Agregar Proveedor 2
                    </button>
                    <button type="button" id="btn-remove-poc-page-2" class="btns btn-remove-tab alm-display-none alm-align-items-center alm-gap-6px alm-padding-8px-16px alm-background-dc2626 alm-border-1-5px-solid-b91c1c alm-border-radius-8px alm-color-ffffff alm-cursor-pointer alm-font-family-Poppins-sans-serif alm-font-size-0-9em alm-font-weight-500 alm-transition-all-0-2s-ease alm-margin-left-15px alm-height-auto">
                        Remover Proveedor 2
                    </button>
                </div>
            </div>

            <div class="alm-modal-body alm-padding-2-5em alm-background-fafafa alm-font-family-Poppins-sans-serif">
                <form id="formPreOrdenCasting" novalidate autocomplete="off">
                    @csrf
                    <input type="hidden" id="poc-has-page2" name="has_page2" value="0">


                    <div id="poc-page-1" class="poc-page">
                        <div class="form-grid alm-display-grid alm-grid-template-columns-repeat-auto-fit-minmax-220px-1fr alm-gap-20px alm-margin-bottom-25px">
                            <div class="form-group">
                                <label for="poc-p1-proveedor"
                                    class="alm-stat-title">Proveedor <span class="alm-text-danger">*</span>:</label>
                                <select id="poc-p1-proveedor" name="page1_proveedor" class="form-control alm-height-auto alm-padding-12px-16px alm-border-radius-10px alm-font-family-Poppins-sans-serif alm-font-size-1-05em">
                                    <option value="" disabled selected>-- Selecciona un proveedor --</option>
                                    <option value="SS Metal Foundry, S. de R. L. de C. V.">SS Metal Foundry, S. de R. L. de C. V.</option>
                                    <option value="SOCIEDAD COOPERATIVA DE PRODUCCIÓN JACARANDAS">SOCIEDAD COOPERATIVA DE PRODUCCIÓN JACARANDAS</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="poc-p1-fecha"
                                    class="alm-stat-title">Fecha:</label>
                                <input type="date" id="poc-p1-fecha" name="page1_fecha" class="form-control alm-height-auto alm-padding-12px-16px alm-border-radius-10px alm-font-family-Poppins-sans-serif alm-font-size-1-05em alm-background-f1f5f9">
                            </div>
                            <div class="form-group">
                                <label for="poc-p1-folio"
                                    class="alm-stat-title">Folio:</label>
                                <input type="text" id="poc-p1-folio" name="page1_folio" class="form-control alm-height-auto alm-padding-12px-16px alm-border-radius-10px alm-font-family-Poppins-sans-serif alm-font-size-1-05em alm-background-f1f5f9 alm-font-weight-bold alm-color-0369a1">
                            </div>
                            <div class="form-group">
                                <label for="poc-p1-moldura"
                                    class="alm-stat-title">Moldura:</label>
                                <input type="text" id="poc-p1-moldura" name="page1_moldura" class="form-control alm-height-auto alm-padding-12px-16px alm-border-radius-10px alm-font-family-Poppins-sans-serif alm-font-size-1-05em alm-background-f1f5f9">
                            </div>
                            <div class="form-group">
                                <label for="poc-p1-ot"
                                    class="alm-stat-title">Orden
                                    de Trabajo:</label>
                                <input type="text" id="poc-p1-ot" name="page1_ot" class="form-control alm-height-auto alm-padding-12px-16px alm-border-radius-10px alm-font-family-Poppins-sans-serif alm-font-size-1-05em alm-background-f1f5f9">
                            </div>
                            <div class="form-group">
                                <label for="poc-p1-fecha-entrega"
                                    class="alm-stat-title">Fecha
                                    Entrega <span class="alm-text-danger">*</span>:</label>
                                <input type="date" id="poc-p1-fecha-entrega" name="page1_fecha_entrega" class="form-control alm-height-auto alm-padding-12px-16px alm-border-radius-10px alm-font-family-Poppins-sans-serif alm-font-size-1-05em">
                            </div>
                        </div>

                        <div class="modal-table-container alm-overflow-x-auto alm-background-fff alm-border-1px-solid-e2e8f0 alm-border-radius-14px alm-padding-20px alm-box-shadow-0-1px-3px-rgba-0-0-0-0-05">
                            <table class="modal-table alm-width-100pct alm-border-collapse-collapse alm-text-align-left">
                                <thead>
                                    <tr
                                        class="alm-border-bottom-2px-solid-cbd5e1 alm-color-475569 alm-font-weight-700 alm-font-size-0-95em">
                                        <th class="alm-th-12">Tipo
                                            de Modelo <span class="alm-text-danger">*</span></th>
                                        <th class="alm-th-8">Cant.
                                            Fabricar <span class="alm-text-danger">*</span></th>
                                        <th class="alm-th-8">Cant.
                                            Consign. <span class="alm-text-danger">*</span></th>
                                        <th class="alm-padding-12px-10px alm-width-15pct alm-font-family-Poppins-sans-serif">
                                            Descripción <span class="alm-text-danger">*</span></th>
                                        <th class="alm-padding-12px-10px alm-width-14pct alm-font-family-Poppins-sans-serif">
                                            Material <span class="alm-text-danger">*</span></th>
                                        <th class="alm-th-12">
                                            Código de Modelo <span class="alm-text-danger">*</span></th>
                                        <th class="alm-th-7">Peso
                                            Juego (KG) </th>
                                        <th class="alm-th-7">Peso
                                            Total (KG) </th>
                                        <th class="alm-th-12">Fecha
                                            Entrega <span class="alm-text-danger">*</span></th>
                                        <th
                                            class="alm-padding-12px-10px alm-width-5pct alm-text-align-center alm-font-family-Poppins-sans-serif">
                                            Acciones</th>
                                    </tr>
                                </thead>
                                <tbody id="alm-tbody-poc-p1">

                                </tbody>
                            </table>
                            <div class="alm-margin-top-15px alm-text-align-center">
                                <button type="button" class="btn-img-action alm-border-none alm-background-none alm-cursor-pointer alm-padding-5px alm-outline-none alm-transition-transform-0-2s-ease">
                                    <img src="{{ asset('images/anadir.png') }}" alt="Añadir"
                                        class="alm-width-38px alm-height-38px">
                                </button>
                            </div>
                        </div>

                        <div class="form-group alm-margin-top-25px">
                            <label for="poc-p1-observaciones"
                                class="alm-stat-title">Observaciones:</label>
                            <textarea id="poc-p1-observaciones" name="page1_observaciones" class="form-control alm-border-radius-10px alm-padding-14px alm-font-family-Poppins-sans-serif alm-font-size-1em alm-width-100pct alm-box-sizing-border-box alm-border-1-5px-solid-cbd5e1"></textarea>
                        </div>
                    </div>


                    <div id="poc-page-2" class="poc-page alm-display-none">
                        <div class="form-grid alm-display-grid alm-grid-template-columns-repeat-auto-fit-minmax-220px-1fr alm-gap-20px alm-margin-bottom-25px">
                            <div class="form-group">
                                <label for="poc-p2-proveedor"
                                    class="alm-stat-title">Proveedor <span class="alm-text-danger">*</span>:</label>
                                <select id="poc-p2-proveedor" name="page2_proveedor" class="form-control alm-height-auto alm-padding-12px-16px alm-border-radius-10px alm-font-family-Poppins-sans-serif alm-font-size-1-05em">
                                    <option value="" disabled selected>-- Selecciona un proveedor --</option>
                                    <option value="SOCIEDAD COOPERATIVA DE PRODUCCIÓN JACARANDAS">SOCIEDAD COOPERATIVA DE PRODUCCIÓN JACARANDAS</option>
                                    <option value="SS Metal Foundry, S. de R. L. de C. V.">SS Metal Foundry, S. de R. L. de C. V.</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="poc-p2-fecha"
                                    class="alm-stat-title">Fecha:</label>
                                <input type="date" id="poc-p2-fecha" name="page2_fecha" class="form-control alm-height-auto alm-padding-12px-16px alm-border-radius-10px alm-font-family-Poppins-sans-serif alm-font-size-1-05em alm-background-f1f5f9">
                            </div>
                            <div class="form-group">
                                <label for="poc-p2-folio"
                                    class="alm-stat-title">Folio:</label>
                                <input type="text" id="poc-p2-folio" name="page2_folio" class="form-control alm-height-auto alm-padding-12px-16px alm-border-radius-10px alm-font-family-Poppins-sans-serif alm-font-size-1-05em alm-background-f1f5f9 alm-font-weight-bold alm-color-0369a1">
                            </div>
                            <div class="form-group">
                                <label for="poc-p2-moldura"
                                    class="alm-stat-title">Moldura:</label>
                                <input type="text" id="poc-p2-moldura" name="page2_moldura" class="form-control alm-height-auto alm-padding-12px-16px alm-border-radius-10px alm-font-family-Poppins-sans-serif alm-font-size-1-05em alm-background-f1f5f9">
                            </div>
                            <div class="form-group">
                                <label for="poc-p2-ot"
                                    class="alm-stat-title">Orden
                                    de Trabajo:</label>
                                <input type="text" id="poc-p2-ot" name="page2_ot" class="form-control alm-height-auto alm-padding-12px-16px alm-border-radius-10px alm-font-family-Poppins-sans-serif alm-font-size-1-05em alm-background-f1f5f9">
                            </div>
                            <div class="form-group">
                                <label for="poc-p2-fecha-entrega"
                                    class="alm-stat-title">Fecha
                                    Entrega <span class="alm-text-danger">*</span>:</label>
                                <input type="date" id="poc-p2-fecha-entrega" name="page2_fecha_entrega" class="form-control alm-height-auto alm-padding-12px-16px alm-border-radius-10px alm-font-family-Poppins-sans-serif alm-font-size-1-05em">
                            </div>
                        </div>

                        <div class="modal-table-container alm-overflow-x-auto alm-background-fff alm-border-1px-solid-e2e8f0 alm-border-radius-14px alm-padding-20px alm-box-shadow-0-1px-3px-rgba-0-0-0-0-05">
                            <table class="modal-table alm-width-100pct alm-border-collapse-collapse alm-text-align-left">
                                <thead>
                                    <tr
                                        class="alm-border-bottom-2px-solid-cbd5e1 alm-color-475569 alm-font-weight-700 alm-font-size-0-95em">
                                        <th class="alm-th-12">Tipo
                                            de Modelo <span class="alm-text-danger">*</span></th>
                                        <th class="alm-th-8">Cant.
                                            Fabricar <span class="alm-text-danger">*</span></th>
                                        <th class="alm-th-8">Cant.
                                            Consign. <span class="alm-text-danger">*</span></th>
                                        <th class="alm-padding-12px-10px alm-width-15pct alm-font-family-Poppins-sans-serif">
                                            Descripción <span class="alm-text-danger">*</span></th>
                                        <th class="alm-padding-12px-10px alm-width-14pct alm-font-family-Poppins-sans-serif">
                                            Material <span class="alm-text-danger">*</span></th>
                                        <th class="alm-th-12">
                                            Código de Modelo <span class="alm-text-danger">*</span></th>
                                        <th class="alm-th-7">Peso
                                            Juego (KG) </th>
                                        <th class="alm-th-7">Peso
                                            Total (KG) </th>
                                        <th class="alm-th-12">Fecha
                                            Entrega <span class="alm-text-danger">*</span></th>
                                        <th
                                            class="alm-padding-12px-10px alm-width-5pct alm-text-align-center alm-font-family-Poppins-sans-serif">
                                            Acciones</th>
                                    </tr>
                                </thead>
                                <tbody id="alm-tbody-poc-p2">

                                </tbody>
                            </table>
                            <div class="alm-margin-top-15px alm-text-align-center">
                                <button type="button" class="btn-img-action alm-border-none alm-background-none alm-cursor-pointer alm-padding-5px alm-outline-none alm-transition-transform-0-2s-ease">
                                    <img src="{{ asset('images/anadir.png') }}" alt="Añadir"
                                        class="alm-width-38px alm-height-38px">
                                </button>
                            </div>
                        </div>

                        <div class="form-group alm-margin-top-25px">
                            <label for="poc-p2-observaciones"
                                class="alm-stat-title">Observaciones:</label>
                            <textarea id="poc-p2-observaciones" name="page2_observaciones" class="form-control alm-border-radius-10px alm-padding-14px alm-font-family-Poppins-sans-serif alm-font-size-1em alm-width-100pct alm-box-sizing-border-box alm-border-1-5px-solid-cbd5e1"></textarea>
                        </div>
                    </div>

                    <div class="form-actions alm-margin-top-35px alm-text-align-center">
                        <button type="submit" class="btn-save-preorden alm-font-size-1-2em alm-padding-15px-35px alm-border-radius-10px alm-font-family-Poppins-sans-serif alm-font-weight-700 alm-background-linear-gradient-135deg-0369a1-0284c7 alm-border-none alm-color-white alm-cursor-pointer alm-transition-all-0-2s-ease alm-box-shadow-0-4px-15px-rgba-3-105-161-0-3 alm-height-auto">
                            Guardar y Descargar Pre-Orden de Casting
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div id="modalEnviarPreOrden" class="alm-modal">
        <div class="alm-modal-content alm-max-width-1100px">
            <div class="alm-modal-header">
                <div class="div-cerrar">
                    <button type="button" class="btn-cerrar" onclick="cerrarModalEnviarPreOrden()">
                        <img class="img-cerrar" src="{{ asset('images/cerrar.png') }}">
                    </button>
                </div>
                <h3>Enviar Pre-Orden por Correo</h3>
                <p id="env-po-modal-subtitle" class="lib-modal-subtitle alm-color-bae6fd alm-font-size-0-9em alm-margin-top-4px alm-margin-bottom-0"></p>
            </div>
            <div class="alm-modal-body">
                <form id="formEnviarPreOrden" enctype="multipart/form-data"
                      data-email-modelo="{{ env('EMAIL_PROVEEDOR_MODELOS', 'produccion@ssmetalf.mx,asistenteprod@ssmetalf.mx') }}"
                      data-email-casting="{{ env('EMAIL_PRODUCCION_SS', 'produccion@ssmetalf.mx,laboratorio@ssmetalf.mx') }}"
                      data-email-calidad="{{ env('EMAIL_CALIDAD', 'inspecciontec@grupoindsaavedra.com') }}"
                      data-email-jacarandas="{{ env('EMAIL_PRODUCCION_JACARANDAS', 'ventas_jacarandas@prodigy.net.mx,requisicionestec@grupoindsaavedra.com') }}">
                    <input type="hidden" id="env-ot" name="ot">
                    <input type="hidden" id="env-tipo" name="tipo" value="modelo">

                    <div class="form-group alm-mb-20" id="div-env-destinatario">
                        <label for="env-destinatario">Notificar a Proveedor (correo electrónico):</label>
                        <input type="text" id="env-destinatario" name="destinatario" class="form-control" required>
                        <span class="alm-text-sm-gray">Separa múltiples correos con comas.</span>
                    </div>

                    <div class="form-group alm-mb-20" id="div-env-destinatario-calidad">
                        <label for="env-destinatario-calidad">Notificar a Calidad (correo electrónico):</label>
                        <input type="text" id="env-destinatario-calidad" name="destinatario_calidad" class="form-control">
                        <span class="alm-text-sm-gray">Copia para Calidad (Modelos).</span>
                    </div>

                    <div class="form-group alm-mb-20">
                        <label for="env-fecha-entrega">Fecha de Entrega:</label>
                        <input type="date" id="env-fecha-entrega" name="fecha_entrega" class="form-control" required>
                        <span class="alm-text-sm-gray">Indica la fecha de entrega acordada
                            para imprimirla en el reporte.</span>
                    </div>



                    <div class="form-group alm-mb-20">
                        <label>Pre-órdenes pendientes de enviar:</label>
                        <div id="env-pending-preordenes-container"
                            class="alm-background-f8fafc alm-border-1px-solid-e2e8f0 alm-border-radius-12px alm-padding-15px alm-max-height-200px alm-overflow-y-auto alm-display-flex alm-flex-direction-column alm-gap-10px">
                            <!-- Checkboxes se cargarán aquí dinámicamente -->
                        </div>
                    </div>

                    <div class="form-group alm-mb-20">
                        <label>Archivos de la OT disponibles para adjuntar:</label>
                        <div id="env-server-files-container"
                            class="alm-background-f8fafc alm-border-1px-solid-e2e8f0 alm-border-radius-12px alm-padding-15px alm-max-height-420px alm-overflow-y-auto alm-display-flex alm-flex-direction-column alm-gap-15px">
                            <div class="alm-spinner alm-border-top-color-033966 alm-display-block alm-margin-10px-auto alm-grid-column-1-1">
                            </div>
                            <span class="alm-text-align-center alm-color-64748b alm-grid-column-1-1">Cargando archivos de la
                                OT...</span>
                        </div>
                    </div>

                    <div class="form-group alm-margin-bottom-30px">
                        <label class="custom-file-upload-label alm-font-weight-700 alm-color-033966 alm-display-block alm-margin-bottom-8px">Adjuntar archivos
                            adicionales desde tu equipo:</label>
                        <div class="custom-file-dropzone">
                            <input type="file" id="env-archivos-adicionales" name="archivos_adicionales[]"
                                class="custom-file-input" multiple>
                            <div class="dropzone-content">
                                <img src="{{ asset('images/anadir.png') }}" class="dropzone-icon alm-width-40px alm-height-40px alm-margin-bottom-8px alm-object-fit-contain">
                                <span class="dropzone-text">Arrastra archivos aquí o haz clic para buscar</span>
                                <span class="dropzone-subtext">Soporta múltiples archivos PDF o imágenes</span>
                            </div>
                        </div>
                        <div id="env-archivos-adicionales-list"
                            class="alm-margin-top-10px alm-display-flex alm-flex-wrap-wrap alm-gap-8px"></div>
                    </div>

                    <div class="form-actions alm-text-align-center">
                        <button type="submit" class="btn-save-preorden alm-background-005194 alm-box-shadow-0-4px-15px-rgba-0-81-148-0-3">
                            Enviar Correo con Adjuntos
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>



    @include('warehouse.partials._modal_start_casting')

    <script>
        window.almacenRoutes = {
            archivos: "{{ route('almacen.fundicion.archivos') }}",
            serve: "{{ route('almacen.fundicion.serve') }}",
            confirmarModelo: "{{ route('almacen.fundicion.confirmarModelo') }}",
            getOtData: "{{ route('almacen.fundicion.getOtData') }}",
            pendingPreOrdenes: "{{ route('almacen.fundicion.getPendingPreOrdenes') }}",
            storePreOrden: "{{ route('almacen.fundicion.storePreOrden') }}",
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

        // DOM Rearrangement for Control Cards to match corresponding document sections
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.alm-files-row').forEach(row => {
                const tbody = row.querySelector('td');
                if (!tbody) return;
                
                const aprobadosCard = tbody.querySelector('[id^="control-almacen-aprobados-"]');
                const mainCard = tbody.querySelector('[id^="control-modelo-"]');
                
                if (aprobadosCard) {
                    // Find the Rechazados section title
                    const h3Rechazados = Array.from(tbody.querySelectorAll('h3')).find(el => el.textContent.includes('Rechazados') || el.textContent.includes('Rechazadas'));
                    
                    if (h3Rechazados) {
                        // Insert immediately before the Rechazados section
                        tbody.insertBefore(aprobadosCard, h3Rechazados);
                    } else if (mainCard) {
                        // If no Rechazados section exists, insert before the main card (so it stays immediately below Aprobados grids)
                        tbody.insertBefore(aprobadosCard, mainCard);
                    }
                }
            });
        });
    </script>

@endsection


