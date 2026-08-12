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
                            <div id="sync-bar-activa"
                                class="alm-display-flex alm-align-items-center alm-justify-content-space-between alm-flex-wrap-wrap alm-gap-10px alm-padding-10px-20px alm-background-linear-gradient-135deg-f0f9ff-0-e0f2fe-100pct alm-border-bottom-1px-solid-bae6fd alm-font-size-0-85rem alm-color-0369a1 alm-font-family-Poppins-sans-serif">
                                <span id="sync-status-almacen"
                                    class="alm-display-flex alm-align-items-center alm-gap-6px alm-font-weight-600">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none"
                                        stroke="#0369a1" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"
                                        class="alm-flex-shrink-0">
                                        <polyline points="23 4 23 10 17 10"></polyline>
                                        <polyline points="1 20 1 14 7 14"></polyline>
                                        <path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"></path>
                                    </svg>
                                    <span id="sync-last-time-almacen">Sincronización automática activa</span>
                                </span>
                                <button id="btn-sync-manual-almacen" onclick="sincronizarDibujos(true)"
                                    title="Sincronizar archivos ahora"
                                    class="alm-display-inline-flex alm-align-items-center alm-gap-7px alm-padding-7px-18px alm-background-linear-gradient-135deg-0369a1-0-0284c7-100pct alm-color-fff alm-border-none alm-border-radius-8px alm-font-weight-700 alm-font-size-0-82rem alm-font-family-Poppins-sans-serif alm-cursor-pointer alm-box-shadow-0-3px-10px-rgba-3-105-161-0-25 alm-transition-all-0-2s-ease alm-white-space-nowrap"
                                    onmouseover="this.style.transform='translateY(-1px)'; this.style.boxShadow='0 5px 15px rgba(3,105,161,0.35)';"
                                    onmouseout="this.style.transform=''; this.style.boxShadow='0 3px 10px rgba(3,105,161,0.25)';">
                                    <svg id="sync-icon-almacen" xmlns="http://www.w3.org/2000/svg" width="14" height="14"
                                        viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"
                                        stroke-linecap="round" stroke-linejoin="round">
                                        <polyline points="23 4 23 10 17 10"></polyline>
                                        <polyline points="1 20 1 14 7 14"></polyline>
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
                                                $sReg = isset($mReg[1]) ? (int) $mReg[1] : 0;

                                                $allowFileCrossOt = function ($fileOt) use ($reg, $isReprocesoOT, $baseOtOfReg, $sReg) {
                                                    if ($fileOt === $reg->ot)
                                                        return true;
                                                    if (!$isReprocesoOT)
                                                        return false;
                                                    $baseOtOfFile = preg_replace('/_R\d+$/i', '', $fileOt);
                                                    if ($baseOtOfFile !== $baseOtOfReg)
                                                        return false;
                                                    preg_match('/_R(\d+)$/i', $fileOt, $mFile);
                                                    $sFile = isset($mFile[1]) ? (int) $mFile[1] : 0;
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
                                                            if (str_contains($val, 'opcional'))
                                                                continue;
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
                                                if (empty($activeClassesForOt)) {
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
                                                                if ($p !== '')
                                                                    $parsedCurrent[] = $p;
                                                            }
                                                        }

                                                        if (!empty($parsedCurrent)) {
                                                            // Usar las clases de la OT actual (aprobadas + rechazadas)
                                                            $activeClassesForOt = array_unique($parsedCurrent);
                                                        } else {
                                                            // Fallback: si la OT actual aún no tiene decisiones, mostrar las
                                                            // rechazadas de la OT anterior (las que se están re-procesando)
                                                            $prevOt = preg_replace_callback('/_R(\d+)$/i', function ($m) {
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
                                                                    if ($p !== '')
                                                                        $parsedPrev[] = $p;
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
                                                                    if ($p !== '')
                                                                        $parsedDecided[] = $p;
                                                                }
                                                            }

                                                            if (!empty($parsedDecided)) {
                                                                $activeClassesForOt = array_unique($parsedDecided);
                                                            }
                                                            // Si está vacío (solo pendientes), conservamos el activeClassesForOt poblado previamente (fallback normal)
                                                        }
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
                                                    $otParaRechazados = preg_replace_callback('/_R(\d+)$/i', function ($m) {
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
                                                        if (strpos($fileLower, 'ayudas_visuales') !== false || strpos($fileLower, 'ayudas-visuales') !== false || strpos($fileLower, 'preordenes') !== false) {
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
                                                            // Los dibujos SIEMPRE se muestran, aunque la clase esté rechazada.
                                                            // Son documentos de referencia permanentes.
                                                            $matchesActive = in_array($foundClass, $activeClassesForOt);
                                                            $matchesRejected = in_array($foundClass, $clasesRechazadas);
                                                            if (!$matchesActive && !$matchesRejected)
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
                                                $baseNames = $dibujoBaseNames;
                                                $normBaseNames = array_map(function ($b) {
                                                    return strtolower(preg_replace('/[\s_]+/', '', $b)); }, $baseNames);

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
                                                                    $normBase = strtolower(preg_replace('/[\s_]+/', '', $base));
                                                                    if (!in_array($normBase, $normBaseNames)) {
                                                                        $ayudaData = [
                                                                            'nombre' => $classNameProper . '/' . $base,
                                                                            'url' => route('ayudas_fundicion.serve', ['clase' => $classNameProper, 'archivo' => $base]),
                                                                            'tipo' => 'ayuda',
                                                                            'ot' => $reg->ot,
                                                                        ];

                                                                        // Las ayudas globales SIEMPRE se muestran, aunque la clase esté rechazada.
                                                                        // Son documentos de referencia permanentes.
                                                                        $ayudasArchivos[] = $ayudaData;

                                                                        $baseNames[] = $base;
                                                                        $normBaseNames[] = $normBase;
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
                                                                    // Las ayudas SIEMPRE se muestran aunque la clase esté rechazada.
                                                                    $matchesActive = false;
                                                                    foreach ($activeClassesForOt as $ac) {
                                                                        if (strpos($fileLower, $ac) !== false) {
                                                                            $matchesActive = true;
                                                                            break;
                                                                        }
                                                                    }
                                                                    // Incluir también archivos de clases rechazadas (siguen siendo referencia)
                                                                    if (!$matchesActive) {
                                                                        foreach ($clasesRechazadas as $rc) {
                                                                            if (strpos($fileLower, $rc) !== false) {
                                                                                $matchesActive = true;
                                                                                break;
                                                                            }
                                                                        }
                                                                    }
                                                                    if (!$matchesActive)
                                                                        continue;
                                                                } else {
                                                                    if (!$allowFileCrossOt($otName)) {
                                                                        continue;
                                                                    }
                                                                }

                                                                $normBase = strtolower(preg_replace('/[\s_]+/', '', $base));
                                                                if (str_starts_with($relativePath, 'preordenes/')) {
                                                                    if (!in_array($normBase, $normBaseNames)) {
                                                                        $otrosArchivos[] = [
                                                                            'nombre' => $relativePath,
                                                                            'url' => route('almacen.fundicion.serve', ['ot' => $otName, 'archivo' => $relativePath, 'tipo' => 'otro']),
                                                                            'tipo' => $isImage ? 'imagen' : 'otro',
                                                                            'ot' => $otName,
                                                                            'origin' => 'otro',
                                                                            'owner' => 'almacen',
                                                                        ];
                                                                        $baseNames[] = $base;
                                                                        $normBaseNames[] = $normBase;
                                                                    }
                                                                } elseif ($isPdf) {
                                                                    if (!in_array($normBase, $normBaseNames)) {
                                                                        $ayudasArchivos[] = [
                                                                            'nombre' => $relativePath,
                                                                            'url' => route('almacen.fundicion.serve', ['ot' => $otName, 'archivo' => $relativePath, 'tipo' => 'ayuda']),
                                                                            'tipo' => 'ayuda',
                                                                            'ot' => $otName,
                                                                        ];
                                                                        $baseNames[] = $base;
                                                                        $normBaseNames[] = $normBase;
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
                                                                // Ayudas de preordenes de Calidad SIEMPRE visibles (documentos de referencia).
                                                                $matchesActive = false;
                                                                foreach ($activeClassesForOt as $ac) {
                                                                    if (strpos($fileLower, $ac) !== false) {
                                                                        $matchesActive = true;
                                                                        break;
                                                                    }
                                                                }
                                                                // Incluir clases rechazadas también
                                                                if (!$matchesActive) {
                                                                    foreach ($clasesRechazadas as $rc) {
                                                                        if (strpos($fileLower, $rc) !== false) {
                                                                            $matchesActive = true;
                                                                            break;
                                                                        }
                                                                    }
                                                                }
                                                                if (!$matchesActive)
                                                                    continue;
                                                            } else {
                                                                if (!$allowFileCrossOt($otName)) {
                                                                    continue;
                                                                }
                                                            }

                                                            $normBase = strtolower(preg_replace('/[\s_]+/', '', $base));
                                                            if (!in_array($normBase, $normBaseNames)) {
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
                                                                $normBaseNames[] = $normBase;
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

                                                    // --- NUEVO: ESCANEAR RUTAS DE PREORDENES FALTANTES ---
                                                    $preOrdenesCandidates = [
                                                        'DOCUMENTACION_GIS/ALMACEN_FUNDICION/' . $otNameSanitized . '/preordenes',
                                                        'DOCUMENTACION_GIS/CALIDAD_FUNDICION/' . $otNameSanitized . '/preordenes',
                                                        'DOCUMENTACION_GIS/ALMACEN_FUNDICION/' . $otNameSanitized . '/Documentos_Aprobados/preordenes',
                                                        'DOCUMENTACION_GIS/CALIDAD_FUNDICION/' . $otNameSanitized . '/Documentos_Aprobados/preordenes',
                                                        'DOCUMENTACION_GIS/ALMACEN_FUNDICION/' . $otNameSanitized . '/ayudas_visuales/preordenes',
                                                        'DOCUMENTACION_GIS/ALMACEN_FUNDICION/' . $otNameSanitized . '/ayudas_visuales/preordenes/documentos_aprobados',
                                                        'DOCUMENTACION_GIS/CALIDAD_FUNDICION/' . $otNameSanitized . '/ayudas_visuales/preordenes/documentos_aprobados',
                                                        'DOCUMENTACION_GIS/ALMACEN_FUNDICION/' . $otNameSanitized . '/preordenes/documentos_aprobados',
                                                        'DOCUMENTACION_GIS/CALIDAD_FUNDICION/' . $otNameSanitized . '/preordenes/documentos_aprobados',
                                                    ];

                                                    foreach ($preOrdenesCandidates as $poDir) {
                                                        $owner = strpos($poDir, 'ALMACEN_FUNDICION') !== false ? 'almacen' : 'calidad';
                                                        $newDirs[] = ['dir' => $poDir, 'origin' => 'aprobado', 'prefix' => 'preordenes/', 'owner' => $owner];
                                                    }

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
                                                                $fileClasses = [];
                                                                foreach ($knownClasses as $kc) {
                                                                    if (strpos($fileLower, $kc) !== false) {
                                                                        $fileClasses[] = $kc;
                                                                    }
                                                                }
                                                                if (!empty($fileClasses)) {
                                                                    // Verificar que la clase del archivo pertenece a esta OT (activas o rechazadas)
                                                                    $hasInactiveClass = false;
                                                                    foreach ($fileClasses as $fc) {
                                                                        if (!in_array($fc, $activeClassesForOt) && !in_array($fc, $clasesRechazadas)) {
                                                                            $hasInactiveClass = true;
                                                                            break;
                                                                        }
                                                                    }
                                                                    if ($hasInactiveClass) {
                                                                        continue;
                                                                    }
                                                                    // El origin viene del directorio ($dirInfo['origin']), NO de la clase del archivo.
                                                                    // Un ConfirmacionModelo en Documentos_Aprobados/ es siempre aprobado.
                                                                } else {
                                                                    if ($otName !== $reg->ot) {
                                                                        continue;
                                                                    }
                                                                }

                                                                $normBase = strtolower(preg_replace('/[\s_]+/', '', $base));
                                                                if (!in_array($normBase, $normBaseNames)) {
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
                                                                    $normBaseNames[] = $normBase;
                                                                }
                                                            }
                                                        }
                                                    }

                                                    // 3. Buscar PDFs generados en public/liberaciones_pdf (LDM y SCAR)
                                                    $otSanitizada = preg_replace('/[^\w\s\-]/', '', $otName);
                                                    $otSanitizada = preg_replace('/[\s]+/', '_', trim($otSanitizada));

                                                    if (file_exists($liberacionesPath)) {
                                                        // Buscar LDM y RDM PDFs generados para ESTA OT en public/liberaciones_pdf
                                                         $otLow = mb_strtolower($otSanitizada, 'UTF-8');
                                                         $otNameLow = mb_strtolower($otName, 'UTF-8');
                                                         $ldmFiles = array_merge(
                                                             glob("{$liberacionesPath}/*{$otSanitizada}*.pdf") ?: [],
                                                             glob("{$liberacionesPath}/F*CCL*{$otSanitizada}*.pdf") ?: []
                                                         );
                                                         foreach (array_unique($ldmFiles) as $f) {
                                                             $base = basename($f);
                                                             $fileLower = mb_strtolower($base, 'UTF-8');
                                                             if (!str_contains($fileLower, $otLow) && !str_contains($fileLower, $otNameLow)) {
                                                                 continue;
                                                             }
                                                             $knownClasses = ['candado obturador', 'cabeza de soplo', 'obturador', 'bombillo', 'embudo', 'corona', 'plato', 'molde', 'fondo', 'pistones', 'guías', 'guias'];
                                                             $hasKnownClass = false;
                                                             foreach ($knownClasses as $kc) {
                                                                 if (strpos($fileLower, $kc) !== false) {
                                                                     $hasKnownClass = true;
                                                                     break;
                                                                 }
                                                             }
                                                             if ($hasKnownClass) {
                                                                 $matchesActive = false;
                                                                 foreach ($activeClassesForOt as $ac) {
                                                                     if (strpos($fileLower, $ac) !== false) {
                                                                         $matchesActive = true;
                                                                         break;
                                                                     }
                                                                 }
                                                                 if (!$matchesActive)
                                                                     continue;
                                                             } else {
                                                                 if (!$allowFileCrossOt($otName)) {
                                                                     continue;
                                                                 }
                                                             }
                                                             $normBase = strtolower(preg_replace('/[\s_]+/', '', $base));
                                                             if (!in_array($normBase, $normBaseNames)) {
                                                                 $isRechazado = strpos($fileLower, 'rdm') !== false || strpos($fileLower, 'rechazado') !== false;
                                                                 $origin = $isRechazado ? 'rechazado' : 'aprobado';
                                                                 $otrosArchivos[] = [
                                                                     'nombre' => $base,
                                                                     'url' => route('almacen.fundicion.serve', ['ot' => $otName, 'archivo' => $base, 'tipo' => 'liberacion', 'origin' => $origin]),
                                                                     'tipo' => 'liberacion',
                                                                     'ot' => $otName,
                                                                     'origin' => $origin,
                                                                     'owner' => 'calidad',
                                                                 ];
                                                                 $baseNames[] = $base;
                                                                 $normBaseNames[] = $normBase;
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
                                                                foreach ($activeClassesForOt as $ac) {
                                                                    if (strpos($fileLower, $ac) !== false) {
                                                                        $matchesActive = true;
                                                                        break;
                                                                    }
                                                                }
                                                                if (!$matchesActive)
                                                                    continue;
                                                            } else {
                                                                if (!$allowFileCrossOt($otName)) {
                                                                    continue;
                                                                }
                                                            }
                                                            $normBase = strtolower(preg_replace('/[\s_]+/', '', $base));
                                                            if (!in_array($normBase, $normBaseNames)) {
                                                                $rechazadosOtros[] = [
                                                                    'nombre' => $base,
                                                                    'url' => route('almacen.fundicion.serve', ['ot' => $otName, 'archivo' => $base, 'tipo' => 'liberacion', 'origin' => 'rechazado']),
                                                                    'tipo' => 'liberacion',
                                                                    'ot' => $otName,
                                                                    'origin' => 'rechazado',
                                                                    'owner' => 'calidad',
                                                                ];
                                                                $baseNames[] = $base;
                                                                $normBaseNames[] = $normBase;
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
                                                            strpos($nameLow, 'rdm') === false &&
                                                            strpos($nameLow, 'scar') === false &&
                                                            strpos($nameLow, 'confirmacion') === false &&
                                                            strpos($nameLow, 'liberacion') === false) ||
                                                        strpos($nameLow, 'escaneado') !== false
                                                    );

                                                    // Si el archivo es de Calidad y no es preorden ni confirmación, verificar que Calidad haya enviado efectivamente la alerta por correo
                                                    if ($archivo['owner'] === 'calidad' && !$isPreorden && strpos($nameLow, 'confirmacion') === false) {
                                                        /** @var \App\Models\FundicionHistory|null $fileHistory */
                                                        $fileHistory = $relatedRecords->firstWhere('ot', $archivo['ot']);
                                                        $status = $targetReg->calidad_revision_status ?? ($fileHistory ? $fileHistory->calidad_revision_status : null);
                                                        $calidadAlertaEnviada = (
                                                            in_array($status, ['calidad_aprobado', 'calidad_rechazado', 'calidad_mixto', 'calidad_parcial', 'casting_aprobado']) ||
                                                            \App\Models\LiberacionModeloFundicion::where(function ($q) use ($archivo, $targetReg) {
                                                                $q->where('ot', '=', $archivo['ot'])->orWhere('ot', '=', $targetReg->ot);
                                                            })->where('alerta_enviada', true)->exists() ||
                                                            \App\Models\ScarModelo::where(function ($q) use ($archivo, $targetReg) {
                                                                $q->where('ot', '=', $archivo['ot'])->orWhere('ot', '=', $targetReg->ot);
                                                            })->whereIn('estatus', ['alertado', 'cerrado'])->exists()
                                                        );
                                                        if (!$calidadAlertaEnviada) {
                                                            continue; // Ocultar formatos F-CCL-LDM / SCAR en Almacén hasta que Calidad envíe la alerta
                                                        }
                                                    }

                                                    if ($userPerfil != 1 && $userPerfil != 2) {
                                                        if ($userPerfil == 4 || $userPerfil == 3) { // Calidad o Master
                                                            // Calidad/Master solo ve preordenes si pre_orden_email_sent es true
                                                            if ($isPreorden) {
                                                                /** @var \App\Models\FundicionHistory|null $fileHistory */
                                                                $fileHistory = $relatedRecords->firstWhere('ot', $archivo['ot']);
                                                                $hasPreorden = ($fileHistory && $fileHistory->pre_orden_email_sent)
                                                                    || !empty($targetReg->pre_orden_sent)
                                                                    || !empty($targetReg->pre_orden_email_sent)
                                                                    || \App\Models\PreOrdenFundicion::where('ot', $archivo['ot'])->orWhere('ot', $targetReg->ot)->exists();
                                                                if (!$hasPreorden && strpos($nameLow, 'documentos_aprobados') === false) {
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

                                                $almacenPreordenes = [];
                                                $calidadAprobadosLdm = [];
                                                $archivosRechazados = [];
                                                foreach ($otrosArchivos as $archivo) {
                                                    $nameLow = strtolower($archivo['nombre']);
                                                    $baseLow = strtolower(basename($archivo['nombre']));
                                                    if (
                                                        strpos($nameLow, 'documentos_rechazados') !== false ||
                                                        strpos($baseLow, 'rechazado') !== false ||
                                                        strpos($baseLow, 'scar') !== false ||
                                                        strpos($baseLow, 'rdm') !== false ||
                                                        strpos($baseLow, 'fdrdm') !== false ||
                                                        strpos($nameLow, 'f_ccl_rdm') !== false ||
                                                        strpos($nameLow, 'f_ccl_scar') !== false
                                                    ) {
                                                        $archivosRechazados[] = $archivo;
                                                    } elseif (
                                                        strpos($nameLow, 'fdldm') !== false ||
                                                        strpos($nameLow, 'f_ccl_ldm') !== false
                                                    ) {
                                                        $calidadAprobadosLdm[] = $archivo;
                                                    } elseif (
                                                        strpos($nameLow, 'preorden_casting') !== false ||
                                                        strpos($nameLow, 'preorden_modelo') !== false ||
                                                        strpos($nameLow, 'confirmacion_modelo') !== false ||
                                                        strpos($baseLow, 'pre-orden') !== false ||
                                                        strpos($baseLow, 'preorden') !== false ||
                                                        strpos($baseLow, 'confirmacion') !== false ||
                                                        strpos($baseLow, 'escaneado') !== false ||
                                                        strpos($baseLow, 'pfm') !== false ||
                                                        strpos($baseLow, 'cfm') !== false ||
                                                        strpos($baseLow, 'efm') !== false ||
                                                        strpos($baseLow, 'pfc') !== false ||
                                                        strpos($baseLow, 'efc') !== false ||
                                                        strpos($nameLow, 'f_alm_efc') !== false ||
                                                        strpos($nameLow, 'preordenes/') !== false
                                                    ) {
                                                        $almacenPreordenes[] = $archivo;
                                                    } else {
                                                        $calidadAprobadosLdm[] = $archivo;
                                                    }
                                                }
                                                $archivosAprobados = $almacenPreordenes;
                                                $countAprobados = count($calidadAprobadosLdm);
                                                $countRechazados = count($archivosRechazados);

                                                $countAyudas = count($ayudasArchivos);
                                                $countOtros = count($otrosArchivos);

                                                // ── CALCULAR APROBADOS Y RECHAZADOS DE CADA CLASE ──
                                                // (Calculado ANTES de showControlCard para poder usarlos en la lógica de visibilidad)
                                                $liberacionesAll = \App\Models\LiberacionModeloFundicion::where('ot', $targetReg->ot)->get();
                                                $latestLiberacionesByClass = [];
                                                foreach ($liberacionesAll as $lib) {
                                                    $tipo = $lib->tipo_modelo;
                                                    $libOt = $lib->ot;

                                                    preg_match('/_R(\d+)$/', $libOt, $matches);
                                                    $suffixNum = isset($matches[1]) ? (int) $matches[1] : 0;

                                                    $shouldReplace = !isset($latestLiberacionesByClass[$tipo]);
                                                    if (!$shouldReplace) {
                                                        $existSuffix = $latestLiberacionesByClass[$tipo]['suffix'];
                                                        $existId = $latestLiberacionesByClass[$tipo]['lib']->id;
                                                        if ($suffixNum > $existSuffix || ($suffixNum === $existSuffix && $lib->id > $existId)) {
                                                            $shouldReplace = true;
                                                        }
                                                    }
                                                    if ($shouldReplace) {
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
                                                    if ($lib->decision === 'aprobar' || $lib->estado === 'aprobado' || $lib->estado === 'aprobada') {
                                                        $aprobadosRaw[] = $tipo;
                                                    } elseif ($lib->decision === 'rechazar' || $lib->estado === 'rechazado' || $lib->estado === 'rechazada') {
                                                        if ($lib->alerta_enviada || $lib->estado !== 'pendiente') {
                                                            $rechazadosRaw[] = $tipo;
                                                        }
                                                    }
                                                }

                                                // Extraer clases de archivos aprobados en $calidadAprobadosLdm
                                                foreach ($calidadAprobadosLdm as $docAprob) {
                                                    $baseName = basename($docAprob['nombre']);
                                                    if (preg_match('/F_CCL_LDM_([^\.]+)/i', $baseName, $mMatches)) {
                                                        $extractedClass = trim(str_replace(['_', '-'], ' ', $mMatches[1]));
                                                        if (!empty($extractedClass)) {
                                                            $aprobadosRaw[] = $extractedClass;
                                                        }
                                                    }
                                                    foreach ($activeClassesForOt as $ac) {
                                                        if (!empty($ac) && strpos(strtolower($baseName), strtolower($ac)) !== false) {
                                                            $aprobadosRaw[] = $ac;
                                                        }
                                                    }
                                                }

                                                // Normalizar y deduplicar aprobados y rechazados con respecto a activeClassesForOt
                                                $aprobados = [];
                                                foreach ($aprobadosRaw as $cRaw) {
                                                    $cLow = strtolower(trim($cRaw));
                                                    if (empty($cLow)) continue;
                                                    $matched = false;
                                                    foreach ($activeClassesForOt as $ac) {
                                                        $acLow = strtolower(trim($ac));
                                                        if ($cLow === $acLow || strpos($cLow, $acLow) !== false || strpos($acLow, $cLow) !== false) {
                                                            $aprobados[] = $ac;
                                                            $matched = true;
                                                            break;
                                                        }
                                                    }
                                                    if (!$matched) {
                                                        $aprobados[] = trim($cRaw);
                                                    }
                                                }
                                                $aprobados = array_values(array_unique($aprobados));

                                                $rechazados = array_values(array_unique(array_filter($rechazadosRaw, function ($clase) use ($activeClassesForOt) {
                                                    return in_array(strtolower($clase), array_map('strtolower', $activeClassesForOt));
                                                })));

                                                // Fallback: si $tieneAprobados o count($calidadAprobadosLdm) > 0 y $aprobados está vacío
                                                $isCalidadAlertedLocal = in_array($reg->calidad_revision_status, ['calidad_aprobado', 'calidad_rechazado', 'calidad_mixto', 'calidad_parcial', 'casting_aprobado'])
                                                    || \App\Models\LiberacionModeloFundicion::where('ot', $reg->ot)->where('alerta_enviada', true)->exists();
                                                if (empty($aprobados) && ($isCalidadAlertedLocal || count($calidadAprobadosLdm) > 0 || in_array($targetReg->calidad_revision_status, ['calidad_aprobado', 'casting_aprobado']))) {
                                                    $rechazadosNormLocal = array_map('strtolower', $rechazados);
                                                    $aprobados = array_values(array_filter($activeClassesForOt, function ($ac) use ($rechazadosNormLocal) {
                                                        return !in_array(strtolower($ac), $rechazadosNormLocal);
                                                    }));
                                                    if (empty($aprobados) && !empty($activeClassesForOt)) {
                                                        $aprobados = $activeClassesForOt;
                                                    }
                                                }
                                                // Clasificar dibujos y ayudas por etapa (Fabricación de Modelo vs Casting)
                                                $aprobadosNorm = array_map('strtolower', $aprobados);
                                                $rechazadosNorm = array_map('strtolower', $rechazados);
                                                $clasesFabricacion = array_values(array_diff($activeClassesForOt, array_merge($aprobadosNorm, $rechazadosNorm)));
                                                $isMixedProcess = (count($aprobadosNorm) > 0 && count($clasesFabricacion) > 0);

                                                $dibujosCasting = array_values(array_filter($archivos, function ($d) use ($aprobadosNorm) {
                                                    $nameLow = strtolower($d['nombre']);
                                                    foreach ($aprobadosNorm as $ap) {
                                                        if ($ap !== '' && strpos($nameLow, $ap) !== false)
                                                            return true;
                                                    }
                                                    return false;
                                                }));

                                                $dibujosModelo = array_values(array_filter($archivos, function ($d) use ($clasesFabricacion) {
                                                    $nameLow = strtolower($d['nombre']);
                                                    foreach ($clasesFabricacion as $cf) {
                                                        if ($cf !== '' && strpos($nameLow, $cf) !== false)
                                                            return true;
                                                    }
                                                    return false;
                                                }));

                                                $ayudasCasting = array_values(array_filter($ayudasArchivos, function ($a) use ($aprobadosNorm) {
                                                    $nameLow = strtolower($a['nombre']);
                                                    foreach ($aprobadosNorm as $ap) {
                                                        if ($ap !== '' && strpos($nameLow, $ap) !== false)
                                                            return true;
                                                    }
                                                    return false;
                                                }));

                                                $ayudasModelo = array_values(array_filter($ayudasArchivos, function ($a) use ($clasesFabricacion) {
                                                    $nameLow = strtolower($a['nombre']);
                                                    foreach ($clasesFabricacion as $cf) {
                                                        if ($cf !== '' && strpos($nameLow, $cf) !== false)
                                                            return true;
                                                    }
                                                    return false;
                                                }));

                                                $dibujosRechazadosOrig = array_values(array_filter($archivos, function ($d) use ($rechazadosNorm) {
                                                    $nameLow = strtolower($d['nombre']);
                                                    foreach ($rechazadosNorm as $r) {
                                                        if ($r !== '' && strpos($nameLow, $r) !== false)
                                                            return true;
                                                    }
                                                    return false;
                                                }));

                                                $ayudasRechazadosOrig = array_values(array_filter($ayudasArchivos, function ($a) use ($rechazadosNorm) {
                                                    $nameLow = strtolower($a['nombre']);
                                                    foreach ($rechazadosNorm as $r) {
                                                        if ($r !== '' && strpos($nameLow, $r) !== false)
                                                            return true;
                                                    }
                                                    return false;
                                                }));

                                                // Clasificar rechazados:
                                                // Guardar primero los que vienen del escaneo de filesystem (acumulados arriba)
                                                // y limpiar los arrays para que la reclasificación empiece de cero.
                                                $rechazadosOtrosFilesystem = $rechazadosOtros ?? [];
                                                $rechazadosAyudasFilesystem = $rechazadosAyudas ?? [];
                                                $rechazadosDibujos = $rechazadosDibujos ?? [];
                                                // Reclasificar $archivosRechazados (los de la lógica de otrosArchivos)
                                                $rechazadosAyudas = [];
                                                $rechazadosOtros = [];
                                                foreach ($archivosRechazados as $rArchivo) {
                                                    $nameLow = strtolower($rArchivo['nombre']);
                                                    $ext = pathinfo($nameLow, PATHINFO_EXTENSION);
                                                    $isImg = in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp']);

                                                    $rArchivo['ot'] = $rArchivo['ot'] ?? $reg->ot;
                                                    $rArchivo['tipo'] = $rArchivo['tipo'] ?? ($isImg ? 'imagen' : 'otro');

                                                    if (strpos($nameLow, 'scar') !== false || strpos($nameLow, 'f_ccl_scar') !== false || strpos($nameLow, 'f_ccl_rdm') !== false || strpos($nameLow, 'foto') !== false) {
                                                        $rechazadosOtros[] = $rArchivo;
                                                    } elseif (strpos($nameLow, 'ayudas_visuales') !== false || strpos($nameLow, 'ayudas-visuales') !== false || $isImg) {
                                                        if ($rArchivo['tipo'] === 'otro')
                                                            $rArchivo['tipo'] = 'ayuda';
                                                        $rechazadosAyudas[] = $rArchivo;
                                                    } elseif (strpos($nameLow, 'dibujos') !== false || strpos($nameLow, 'dibujo') !== false) {
                                                        $rechazadosDibujos[] = $rArchivo;
                                                    } else {
                                                        $rechazadosOtros[] = $rArchivo;
                                                    }
                                                }
                                                // Combinar los del filesystem con los reclasificados, deduplicando por nombre base
                                                $baseNamesRechOtros = array_map(fn($a) => basename($a['nombre']), $rechazadosOtros);
                                                foreach ($rechazadosOtrosFilesystem as $rFs) {
                                                    if (!in_array(basename($rFs['nombre']), $baseNamesRechOtros)) {
                                                        $rechazadosOtros[] = $rFs;
                                                        $baseNamesRechOtros[] = basename($rFs['nombre']);
                                                    }
                                                }
                                                $baseNamesRechAyudas = array_map(fn($a) => basename($a['nombre']), $rechazadosAyudas);
                                                foreach ($rechazadosAyudasFilesystem as $rFs) {
                                                    if (!in_array(basename($rFs['nombre']), $baseNamesRechAyudas)) {
                                                        $rechazadosAyudas[] = $rFs;
                                                        $baseNamesRechAyudas[] = basename($rFs['nombre']);
                                                    }
                                                }
                                                // ── CONTROL DE VISIBILIDAD DE LA CARD DE ALMACÉN Y CONTEO EXACTO DE TARJETAS ──
                                                $hasVerdictosPendientes = count($aprobados) > 0 || count($rechazados) > 0;
                                                $isFinalized = ($targetReg->calidad_revision_status === 'casting_aprobado') && !$hasVerdictosPendientes;
                                                if ($hasVerdictosPendientes) {
                                                    $isFinalized = false;
                                                }
                                                $isCalidadAlerted = in_array($reg->calidad_revision_status, ['calidad_aprobado', 'calidad_rechazado', 'calidad_mixto', 'calidad_parcial', 'casting_aprobado'])
                                                    || \App\Models\LiberacionModeloFundicion::where('ot', $reg->ot)->where('alerta_enviada', true)->exists();

                                                $castingEmailSent = ($reg->calidad_revision_status === 'casting_aprobado');

                                                $rechazadosSinPreorden = [];
                                                if ($isCalidadAlerted && count($rechazados) > 0 && !$reg->rechazos_procesados) {
                                                    $rechazadosNormFab = array_map('strtolower', $rechazados);
                                                    $preordenesSentClassesFab = [];
                                                    $preOrdenesEnviadasFab = \App\Models\PreOrdenFundicion::where('ot', $targetReg->ot)->where('is_sent', 1)->get();
                                                    foreach ($preOrdenesEnviadasFab as $poFab) {
                                                        $filasFab = is_string($poFab->filas) ? json_decode($poFab->filas, true) : $poFab->filas;
                                                        if (is_array($filasFab)) {
                                                            foreach ($filasFab as $fFab) {
                                                                if (!empty($fFab['clase'] ?? $fFab['clase_nombre'])) {
                                                                    $preordenesSentClassesFab[] = strtolower($fFab['clase'] ?? $fFab['clase_nombre']);
                                                                }
                                                            }
                                                        }
                                                    }
                                                    foreach ($rechazadosNormFab as $rClase) {
                                                        $cubiertaFab = false;
                                                        foreach ($preordenesSentClassesFab as $psc) {
                                                            if (strpos($psc, $rClase) !== false || strpos($rClase, $psc) !== false) {
                                                                $cubiertaFab = true;
                                                                break;
                                                            }
                                                        }
                                                        if (!$cubiertaFab) {
                                                            $rechazadosSinPreorden[] = $rClase;
                                                        }
                                                    }
                                                }
                                                $hayRechazadosSinPreorden = count($rechazadosSinPreorden) > 0;

                                                $aprobadosNorm = array_map('strtolower', $aprobados);
                                                $rechazadosNorm = array_map('strtolower', $rechazados);

                                                $almacenPreordenesFab = array_values(array_filter($almacenPreordenes, function ($doc) use ($clasesFabricacion) {
                                                    $pathLow = strtolower($doc['nombre']);
                                                    $nameLow = strtolower(basename($doc['nombre']));
                                                    if (
                                                        str_contains($pathLow, 'preorden_casting') ||
                                                        str_contains($pathLow, 'casting') ||
                                                        str_contains($pathLow, 'fdldm') ||
                                                        str_contains($nameLow, 'pfc') ||
                                                        str_contains($nameLow, 'f_alm_pfc') ||
                                                        str_contains($nameLow, 'efc') ||
                                                        str_contains($nameLow, 'f_alm_efc') ||
                                                        str_contains($nameLow, 'f_ccl_ldm') ||
                                                        str_contains($nameLow, 'fdldm')
                                                    ) {
                                                        return false;
                                                    }
                                                    if (empty($clasesFabricacion))
                                                        return true;
                                                    if (
                                                        str_contains($nameLow, 'preorden') ||
                                                        str_contains($nameLow, 'pre-orden') ||
                                                        str_contains($nameLow, 'escaneado') ||
                                                        str_contains($nameLow, 'cfm') ||
                                                        str_contains($nameLow, 'pfm') ||
                                                        str_contains($nameLow, 'efm') ||
                                                        str_contains($pathLow, 'preorden_modelo') ||
                                                        str_contains($pathLow, 'confirmacion_modelo')
                                                    )
                                                        return true;
                                                    foreach ($clasesFabricacion as $cf) {
                                                        if ($cf !== '' && strpos($nameLow, $cf) !== false)
                                                            return true;
                                                    }
                                                    return false;
                                                }));

                                                $tieneArchivosFabricacion = count($dibujosModelo) > 0 || count($ayudasModelo) > 0 || count($almacenPreordenesFab) > 0;
                                                $tieneFabricacion = (!$isCalidadAlerted && !$castingEmailSent) || $hayRechazadosSinPreorden || $tieneArchivosFabricacion;

                                                $clasesFabricacionHeader = $clasesFabricacion;
                                                if (empty($clasesFabricacionHeader)) {
                                                    $archivosFabAll = array_merge($almacenPreordenesFab, $dibujosModelo, $ayudasModelo);
                                                    $extractedFab = [];
                                                    foreach ($archivosFabAll as $af) {
                                                        $bName = basename($af['nombre']);
                                                        foreach ($activeClassesForOt as $ac) {
                                                            if (!empty($ac) && strpos(strtolower($bName), strtolower($ac)) !== false) {
                                                                $extractedFab[] = $ac;
                                                            }
                                                        }
                                                    }
                                                    $clasesFabricacionHeader = array_values(array_unique($extractedFab));
                                                    if (empty($clasesFabricacionHeader)) {
                                                        $clasesFabricacionHeader = $activeClassesForOt;
                                                    }
                                                }
                                                $aprobadosNorm = array_map('strtolower', $aprobados);
                                                $rechazadosNorm = array_map('strtolower', $rechazados);
                                                $tieneAprobados = count($aprobados) > 0 || count($calidadAprobadosLdm) > 0 || count($dibujosCasting) > 0 || count($ayudasCasting) > 0 || count($almacenPreordenesCasting) > 0;
                                                $tieneRechazados = count($rechazados) > 0 || count($rechazadosOtros) > 0 || count($rechazadosDibujos) > 0 || count($rechazadosAyudas) > 0 || count($dibujosRechazadosOrig) > 0 || count($ayudasRechazadosOrig) > 0;

                                                $calidadAprobadosLdmCasting = array_values(array_filter($calidadAprobadosLdm, function ($doc) use ($aprobadosNorm, $rechazadosNorm) {
                                                    $nameLow = strtolower(basename($doc['nombre']));
                                                    if (!empty($rechazadosNorm)) {
                                                        $mencionaRechazada = false;
                                                        foreach ($rechazadosNorm as $rCl) {
                                                            if ($rCl !== '' && strpos($nameLow, $rCl) !== false) {
                                                                $mencionaRechazada = true;
                                                                break;
                                                            }
                                                        }
                                                        if ($mencionaRechazada) {
                                                            $mencionaAprobada = false;
                                                            foreach ($aprobadosNorm as $ap) {
                                                                if ($ap !== '' && strpos($nameLow, $ap) !== false) {
                                                                    $mencionaAprobada = true;
                                                                    break;
                                                                }
                                                            }
                                                            if (!$mencionaAprobada) {
                                                                return false;
                                                            }
                                                        }
                                                    }
                                                    return true;
                                                }));
                                                $almacenPreordenesCasting = array_values(array_filter($almacenPreordenes, function ($doc) use ($aprobadosNorm) {
                                                    $pathLow = strtolower($doc['nombre']);
                                                    $nameLow = strtolower(basename($doc['nombre']));
                                                    $isCastingDoc = (
                                                        str_contains($pathLow, 'preorden_casting') ||
                                                        str_contains($pathLow, 'casting') ||
                                                        str_contains($nameLow, 'pfc') ||
                                                        str_contains($nameLow, 'f_alm_pfc') ||
                                                        str_contains($nameLow, 'efc') ||
                                                        str_contains($nameLow, 'f_alm_efc')
                                                    );
                                                    if (!$isCastingDoc) {
                                                        return false;
                                                    }
                                                    if (empty($aprobadosNorm)) {
                                                        return true;
                                                    }
                                                    foreach ($aprobadosNorm as $ap) {
                                                        if ($ap !== '' && strpos($nameLow, $ap) !== false) {
                                                            return true;
                                                        }
                                                    }
                                                    return true;
                                                }));

                                                $countVisibleFabricacion = $tieneFabricacion ? (count($dibujosModelo) + count($ayudasModelo) + count($almacenPreordenesFab)) : 0;
                                                $countVisibleAprobados   = $tieneAprobados   ? (count($dibujosCasting) + count($ayudasCasting) + count($calidadAprobadosLdmCasting) + count($almacenPreordenesCasting)) : 0;
                                                $countVisibleRechazados  = $tieneRechazados  ? (count($dibujosRechazadosOrig) + count($ayudasRechazadosOrig) + count($rechazadosDibujos) + count($rechazadosAyudas) + count($rechazadosOtros)) : 0;

                                                $count = $countVisibleFabricacion + $countVisibleAprobados + $countVisibleRechazados;

                                                $hasRechazosRealLocal = (count($rechazados) > 0 || $tieneRechazados);
                                                $esReproceso = (bool) preg_match('/_R\d+$/i', $targetReg->ot);
                                                $showControlCard = ($estado === 'activa' && !$isFinalized && (!$isCalidadAlerted || $hasRechazosRealLocal || ($esReproceso && count($clasesFabricacion) > 0)));
                                                $hasFilesOrControl = ($count > 0 || $showControlCard);

                                                // DEBUG MARKER
                                                echo "<!-- DEBUG OT: {$reg->ot}, estado: {$estado}, isFinalized: " . ($isFinalized ? 'true' : 'false') . ", isCalidadAlerted: " . ($isCalidadAlerted ? 'true' : 'false') . ", showControlCard: " . ($showControlCard ? 'true' : 'false') . " -->";



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
                                                    if (in_array(Auth::user()->perfil, [1, 3, 4])) {
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
                                            @php
                                                $pendingChanges = is_string($reg->pending_almacen_changes) ? json_decode($reg->pending_almacen_changes, true) : ($reg->pending_almacen_changes ?? []);
                                                $hasPendingChanges = !empty($pendingChanges);
                                            @endphp

                                            {{-- Fila principal --}}
                                            <tr data-ot="{{ $reg->ot }}" data-estado-real="{{ $fsmState }}"
                                                data-is-fully-processed="{{ $targetReg->isAlmacenFullyProcessed() ? 'true' : 'false' }}">
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
                                                        <div
                                                            class="status-modelo-container alm-display-inline-flex alm-flex-direction-column alm-align-items-center alm-gap-2px alm-padding-6px alm-border-radius-8px">
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
                                                    @if($hasPendingChanges)
                                                        <button class="btn-toggle-files"
                                                            style="background: linear-gradient(135deg, #f97316, #ea580c); color: white; border: 1px solid #c2410c;"
                                                            onclick="almacenRevisarCambios('{{ $reg->ot }}')">
                                                            Revisar Cambios
                                                        </button>
                                                    @elseif ($hasFilesOrControl)
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
                                                        {{-- CONTENEDOR PRINCIPAL PROCESOS (CONTENEDOR 0) --}}
                                                        <div class="alm-contenedor-principal-procesos"
                                                            style="display: flex; flex-direction: column; gap: 25px; width: 100%; margin-top: 15px;">

                                                            {{-- CONTENEDOR 1: FABRICACIÓN / RE-PROCESO DE MODELO --}}
                                                            @if ($tieneFabricacion)
                                                                <div class="alm-process-block"
                                                                    style="margin-bottom: 10px; padding: 20px; border-radius: 14px; background-color: #f0f9ff; border: 2px solid #0284c7; box-shadow: 0 4px 14px rgba(2, 132, 199, 0.08);">
                                                                    <div
                                                                        style="display: flex; align-items: center; justify-content: space-between; border-bottom: 2px solid #0284c7; padding-bottom: 10px; margin-bottom: 15px;">
                                                                        <h3
                                                                            style="margin: 0; color: #0284c7; font-size: 1.15rem; font-weight: 700; display: flex; align-items: center; gap: 10px;">
                                                                            <img src="{{ asset('images/almacen.png') }}"
                                                                                style="width: 30px; height: 30px; object-fit: contain;">
                                                                            @if ($hayRechazadosSinPreorden)
                                                                                Documentos de Almacén
                                                                                {{ count($rechazadosSinPreorden) > 0 ? '(' . implode(', ', array_map('ucfirst', $rechazadosSinPreorden)) . ')' : '' }}
                                                                            @else
                                                                                Etapa: Fabricación / Re-Proceso de Modelo
                                                                                {{ count($clasesFabricacionHeader) > 0 ? '(' . implode(', ', array_map('ucfirst', $clasesFabricacionHeader)) . ')' : '' }}
                                                                            @endif
                                                                        </h3>
                                                                        <span
                                                                            style="font-size: 0.8rem; font-weight: 700; background: #e0f2fe; color: #0369a1; padding: 4px 12px; border-radius: 6px; border: 1px solid #bae6fd;">
                                                                            {{ $hayRechazadosSinPreorden ? 'DOCUMENTOS ALMACÉN' : 'FABRICACIÓN / MODELO' }}
                                                                        </span>
                                                                    </div>

                                                                    {{-- Dibujos de Modelo --}}
                                                                    @if (count($dibujosModelo) > 0)
                                                                        <h4
                                                                            style="margin-top: 10px; margin-bottom: 10px; color: #005194; font-weight: 700;">
                                                                            Dibujos de Fundición (Modelo)</h4>
                                                                        <div class="alm-pdf-grid"
                                                                            style="background-color: #f0f9ff; border: 1px solid #bae6fd; padding: 15px; border-radius: 8px; margin-bottom: 15px;">
                                                                            @foreach ($dibujosModelo as $archivoInfo)
                                                                                <div class="dibujos-file-card"
                                                                                    style="animation-delay: {{ $loop->index * 0.05 }}s; border-left-color: #0284c7;">
                                                                                    <div class="file-icon-wrapper alm-cursor-pointer" title="Abrir PDF">
                                                                                        <img src="{{ asset('images/pdf-view-shadow.png') }}"
                                                                                            class="file-icon icon-default">
                                                                                        <img src="{{ asset('images/pdf-view.png') }}"
                                                                                            class="file-icon icon-hover">
                                                                                    </div>
                                                                                    <div class="file-name alm-cursor-pointer" title="Abrir PDF"
                                                                                        onclick="almacenVerPdf('{{ $archivoInfo['ot'] }}', '{{ $archivoInfo['nombre'] }}', '{{ $archivoInfo['tipo'] }}')">
                                                                                        {{ basename($archivoInfo['nombre']) }}
                                                                                    </div>
                                                                                    <div class="file-actions">
                                                                                        <button class="btn-dibujos btn-dibujos-sm btn-ver"
                                                                                            onclick="almacenVerPdf('{{ $archivoInfo['ot'] }}', '{{ $archivoInfo['nombre'] }}', '{{ $archivoInfo['tipo'] }}')">Ver</button>
                                                                                    </div>
                                                                                </div>
                                                                            @endforeach
                                                                        </div>
                                                                    @endif

                                                                    {{-- Ayudas Visuales de Modelo --}}
                                                                    @if (count($ayudasModelo) > 0)
                                                                        <h4
                                                                            style="margin-top: 15px; margin-bottom: 10px; color: #9c0300; font-weight: 700;">
                                                                            Ayudas Visuales de Fundición (Modelo)</h4>
                                                                        <div class="alm-pdf-grid"
                                                                            style="background-color: #f0f9ff; border: 1px solid #bae6fd; padding: 15px; border-radius: 8px; margin-bottom: 15px;">
                                                                            @foreach ($ayudasModelo as $archivoInfo)
                                                                                @php
                                                                                    $ayudaUrl = route('ayudas_fundicion.serve', [
                                                                                        'clase' => $archivoInfo['clase'] ?? '',
                                                                                        'archivo' => basename($archivoInfo['nombre'])
                                                                                    ]);
                                                                                @endphp
                                                                                <div class="dibujos-file-card card-ayuda"
                                                                                    style="animation-delay: {{ $loop->index * 0.05 }}s; border-left-color: #0284c7;">
                                                                                    <div class="file-icon-wrapper alm-cursor-pointer" title="Abrir PDF">
                                                                                        <img src="{{ asset('images/pdf-view-shadow.png') }}"
                                                                                            class="file-icon icon-default">
                                                                                        <img src="{{ asset('images/pdf-view.png') }}"
                                                                                            class="file-icon icon-hover">
                                                                                    </div>
                                                                                    <div class="file-name alm-cursor-pointer" title="Abrir PDF"
                                                                                        onclick="almacenAbrirArchivo('{{ $ayudaUrl }}', '{{ $archivoInfo['ot'] }}', '{{ $archivoInfo['nombre'] }}', 'ayuda')">
                                                                                        {{ basename($archivoInfo['nombre']) }}
                                                                                    </div>
                                                                                    <div class="file-actions">
                                                                                        <button
                                                                                            class="btn-dibujos btn-dibujos-sm btn-ver alm-background-color-0284c7 alm-color-white"
                                                                                            onclick="almacenAbrirArchivo('{{ $ayudaUrl }}', '{{ $archivoInfo['ot'] }}', '{{ $archivoInfo['nombre'] }}', 'ayuda')">Ver</button>
                                                                                    </div>
                                                                                </div>
                                                                            @endforeach
                                                                        </div>
                                                                    @endif

                                                                    {{-- Documentos / Pre-órdenes de Fabricación --}}

                                                                    @if (count($almacenPreordenesFab) > 0)
                                                                        <h4
                                                                            style="margin-top: 15px; margin-bottom: 10px; color: #0284c7; font-weight: 700;">
                                                                            Documentos / Pre-órdenes de Fabricación</h4>
                                                                        <div class="alm-pdf-grid"
                                                                            style="background-color: #f0f9ff; border: 1px solid #bae6fd; padding: 15px; border-radius: 8px; margin-bottom: 15px;">
                                                                            @foreach ($almacenPreordenesFab as $archivoInfo)
                                                                                <div class="dibujos-file-card"
                                                                                    style="animation-delay: {{ $loop->index * 0.05 }}s; border-left-color: #0284c7;">
                                                                                    <div class="file-icon-wrapper alm-cursor-pointer" title="Abrir PDF">
                                                                                        <img src="{{ asset('images/pdf-view-shadow.png') }}"
                                                                                            class="file-icon icon-default">
                                                                                        <img src="{{ asset('images/pdf-view.png') }}"
                                                                                            class="file-icon icon-hover">
                                                                                    </div>
                                                                                    <div class="file-name alm-cursor-pointer" title="Abrir PDF"
                                                                                        onclick="almacenVerPdf('{{ $archivoInfo['ot'] }}', '{{ $archivoInfo['nombre'] }}', 'preorden')">
                                                                                        {{ basename($archivoInfo['nombre']) }}
                                                                                    </div>
                                                                                    <div class="file-actions">
                                                                                        <button
                                                                                            class="btn-dibujos btn-dibujos-sm btn-ver alm-background-color-0284c7 alm-color-white"
                                                                                            onclick="almacenVerPdf('{{ $archivoInfo['ot'] }}', '{{ $archivoInfo['nombre'] }}', 'preorden')">Ver</button>
                                                                                    </div>
                                                                                </div>
                                                                            @endforeach
                                                                        </div>
                                                                    @endif


                                                                    {{-- Tarjeta de Control Almacén Modelo --}}
                                                                    @if ($showControlCard)
                                                                        @php
                                                                            $esReproceso = (bool) preg_match('/_R\d+$/i', $targetReg->ot);
                                                                            $previousOtForRechazo = $targetReg->ot;
                                                                            if ($esReproceso) {
                                                                                preg_match('/_R(\d+)$/i', $targetReg->ot, $matches);
                                                                                $rNum = (int) ($matches[1] ?? 1);
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

                                                                            $hasRechazosReal = (count($rechazados) > 0 || count($rechazadosClases) > 0 || $tieneRechazados);
                                                                            $esReinicioParcial = $isCalidadAlerted && count($clasesFabricacion) > 0 && !$esReproceso && $hasRechazosReal;

                                                                            if ($esReinicioParcial) {
                                                                                $otClasesActivas = array_map('strtolower', $clasesFabricacion);
                                                                            } elseif ($esReproceso) {
                                                                                $otClasesActivas = array_map('strtolower', $rechazadosClases);
                                                                            } else {
                                                                                $otClasesActivas = !empty($clasesFabricacion)
                                                                                    ? array_map('strtolower', $clasesFabricacion)
                                                                                    : (is_array($reg->ayudas_config) ? array_map('strtolower', $reg->ayudas_config) : []);
                                                                            }

                                                                            $preOrdenesFabExistentes = \App\Models\PreOrdenFundicion::where('ot', $targetReg->ot)->get()->filter(function ($po) use ($clasesFabricacion) {
                                                                                if ($po->pdf_filename && (str_contains($po->pdf_filename, '_Anterior_N') || str_contains($po->pdf_filename, 'Casting') || str_contains($po->pdf_filename, 'F_ALM_PFC_') || str_contains($po->pdf_filename, 'PFC')))
                                                                                    return false;
                                                                                $filas = is_string($po->filas) ? json_decode($po->filas, true) : $po->filas;
                                                                                if (!is_array($filas))
                                                                                    return false;
                                                                                foreach ($filas as $f) {
                                                                                    $c = strtolower($f['clase'] ?? $f['clase_nombre'] ?? $f['tipo_modelo'] ?? $f['nombre'] ?? '');
                                                                                    foreach ($clasesFabricacion as $cf) {
                                                                                        if ($c !== '' && ($c === strtolower($cf) || strpos($c, strtolower($cf)) !== false || strpos(strtolower($cf), $c) !== false))
                                                                                            return true;
                                                                                    }
                                                                                }
                                                                                return false;
                                                                            });

                                                                            $tieneFisicoFab = \App\Models\LiberacionModeloFundicion::where('ot', $targetReg->ot)
                                                                                ->where('tipo_origen', 'con_modelo')
                                                                                ->get()
                                                                                ->filter(function ($lib) use ($clasesFabricacion) {
                                                                                    $tm = strtolower($lib->tipo_modelo ?? '');
                                                                                    foreach ($clasesFabricacion as $cf) {
                                                                                        if ($tm !== '' && ($tm === strtolower($cf) || strpos($tm, strtolower($cf)) !== false || strpos(strtolower($cf), $tm) !== false))
                                                                                            return true;
                                                                                    }
                                                                                })->count() > 0;

                                                                            $tienePreOrdenFab = $preOrdenesFabExistentes->count() > 0;
                                                                            $poPendienteEnvioFab = $preOrdenesFabExistentes->where('is_sent', 0)->first();

                                                                            $tienePreOrden = $tienePreOrdenFab || $tieneFisicoFab;

                                                                            $clasesProcesadas = [];
                                                                             $preOrdenesEnviadas = \App\Models\PreOrdenFundicion::where('ot', $targetReg->ot)
                                                                                 ->where('is_sent', 1)
                                                                                 ->where('pdf_filename', 'NOT LIKE', '%_Anterior_N%')
                                                                                 ->get();
                                                                             foreach ($preOrdenesEnviadas as $po) {
                                                                                 $filas = is_string($po->filas) ? json_decode($po->filas, true) : $po->filas;
                                                                                 if (is_array($filas)) {
                                                                                     foreach ($filas as $f) {
                                                                                         $cVal = strtolower($f['clase'] ?? $f['clase_nombre'] ?? $f['tipo_modelo'] ?? $f['nombre'] ?? '');
                                                                                         if (!empty($cVal)) {
                                                                                             $inFab = false;
                                                                                             foreach ($clasesFabricacion as $cf) {
                                                                                                 if (!empty($cf) && (strtolower($cf) === $cVal || strpos($cVal, strtolower($cf)) !== false || strpos(strtolower($cf), $cVal) !== false)) {
                                                                                                     $inFab = true;
                                                                                                     break;
                                                                                                 }
                                                                                             }
                                                                                             if (!$inFab) {
                                                                                                 $clasesProcesadas[] = $cVal;
                                                                                             }
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
                                                                                     foreach (explode(',', $lf) as $c) {
                                                                                         $cTrim = strtolower(trim($c));
                                                                                         $inFab = false;
                                                                                         foreach ($clasesFabricacion as $cf) {
                                                                                             if (!empty($cf) && (strtolower($cf) === $cTrim || strpos($cTrim, strtolower($cf)) !== false || strpos(strtolower($cf), $cTrim) !== false)) {
                                                                                                 $inFab = true;
                                                                                                 break;
                                                                                             }
                                                                                         }
                                                                                         if (!$inFab) {
                                                                                             $clasesProcesadas[] = $cTrim;
                                                                                         }
                                                                                     }
                                                                                 }
                                                                             }
                                                                             $clasesProcesadas = array_values(array_unique(array_filter($clasesProcesadas, fn($v) => $v !== '')));

                                                                             $clasesActivasCubiertas = [];
                                                                             $clasesActivasFaltantes = [];
                                                                             foreach ($otClasesActivas as $clActiva) {
                                                                                 $cubierta = false;
                                                                                 foreach ($clasesProcesadas as $cp) {
                                                                                     if ($cp === '' || $clActiva === '')
                                                                                         continue;
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
                                                                             $algunaClaseProcesada = count($clasesActivasCubiertas) > 0;

                                                                             $controlDisabled = ((count($clasesFabricacion) > 0 || $esReinicioParcial)) ? '' : (($targetReg->tiene_modelo || $targetReg->pre_orden_sent || $targetReg->pre_orden_email_sent) ? 'opacity: 0.5; pointer-events: none;' : '');
                                                                             $hideControlCard = (count($clasesFabricacion) > 0 || $esReinicioParcial) ? '' : ((($tieneAprobados || $tieneRechazados) && !$esReproceso) ? 'display: none;' : ((count($clasesFabricacion) === 0 && !$esReproceso && !$targetReg->tiene_modelo && !$targetReg->pre_orden_sent && !$targetReg->pre_orden_email_sent) ? 'display: none;' : ''));
                                                                            $hideTengoModelo = (($esReproceso && !$esReinicioParcial) || $todasClasesProcesadas || $poPendienteEnvioFab !== null) ? 'display: none;' : '';
                                                                            $hideGenerarFormato = (($esReproceso && !$esReinicioParcial) || $todasClasesProcesadas || $poPendienteEnvioFab !== null) ? 'display: none;' : '';
                                                                            $hideReprocesoPreOrden = ($esReproceso && !$todasClasesProcesadas && !$esReinicioParcial && $poPendienteEnvioFab === null) ? '' : 'display: none;';
                                                                            $hideEditPreOrden = ($tienePreOrdenFab && $poPendienteEnvioFab !== null) ? '' : 'display: none;';

                                                                            $clasesFisicamenteConfirmadas = [];
                                                                            $liberacionesFisicasObj = \App\Models\LiberacionModeloFundicion::where('ot', $targetReg->ot)
                                                                                ->where('tipo_origen', 'con_modelo')
                                                                                ->whereNotNull('tipo_modelo')
                                                                                ->where('tipo_modelo', '!=', '')
                                                                                ->get();
                                                                            foreach ($liberacionesFisicasObj as $lib) {
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
                                                                                    if ($cp === '' || $clActiva === '')
                                                                                        continue;
                                                                                    if (strpos($cp, strtolower($clActiva)) !== false || strpos(strtolower($clActiva), $cp) !== false) {
                                                                                        $cubierta = true;
                                                                                        break;
                                                                                    }
                                                                                }
                                                                                if (!$cubierta) {
                                                                                    $clasesFaltantesFisico[] = $clActiva;
                                                                                }
                                                                            }

                                                                            $poPendienteEnvio = \App\Models\PreOrdenFundicion::where('ot', $targetReg->ot)
                                                                                ->where('is_sent', 0)
                                                                                ->where(function ($q) {
                                                                                    $q->where('pdf_filename', 'NOT LIKE', '%Casting%')
                                                                                      ->where('pdf_filename', 'NOT LIKE', '%F_ALM_PFC_%')
                                                                                      ->where('pdf_filename', 'NOT LIKE', '%PFC%');
                                                                                })
                                                                                ->orderBy('id', 'desc')
                                                                                ->first();
                                                                            $clasesParaEnvio = [];
                                                                            if ($poPendienteEnvio) {
                                                                                $filas = is_string($poPendienteEnvio->filas) ? json_decode($poPendienteEnvio->filas, true) : $poPendienteEnvio->filas;
                                                                                if (is_array($filas)) {
                                                                                    foreach ($filas as $f) {
                                                                                        $cVal = strtolower($f['clase'] ?? $f['clase_nombre'] ?? $f['tipo_modelo'] ?? $f['nombre'] ?? '');
                                                                                        if (!empty($cVal)) {
                                                                                            $clasesParaEnvio[] = $cVal;
                                                                                        }
                                                                                    }
                                                                                }
                                                                            }

                                                                            if ($tieneFisicoFab && empty($clasesParaEnvio)) {
                                                                                $clasesParaEnvio = array_values(array_map('strtolower', $clasesFisicamenteConfirmadas));
                                                                            }

                                                                            $clasesYaProcesadasJson = json_encode(array_values($clasesActivasCubiertas));
                                                                            $clasesActivasFaltantesJson = json_encode(array_values($clasesActivasFaltantes));
                                                                            $todasClasesActivasJson = json_encode(array_values($otClasesActivas));
                                                                            $clasesActivasNoEnviadasJson = json_encode(array_values($clasesActivasFaltantes));
                                                                            $clasesFaltantesFisicoJson = json_encode(array_values($clasesFaltantesFisico));
                                                                            $clasesParaEnvioJson = json_encode(array_values(array_unique($clasesParaEnvio)));

                                                                            $todasClasesEnviadas = $todasClasesProcesadas && ($poPendienteEnvioFab === null);
                                                                            $isFullySubmitted = $todasClasesEnviadas;
                                                                            $hideAllBtns = $isFullySubmitted ? 'display: none;' : '';
                                                                            $hideSendEmail = ($poPendienteEnvioFab !== null) ? '' : 'display: none;';
                                                                            $calidadYaRespondio = ($reg->casting_pdf_generated || in_array($reg->calidad_revision_status, ['casting_aprobado']) || (($tieneAprobados || $tieneRechazados) && !$esReproceso && !$esReinicioParcial));
                                                                            $ocultarCardEnModelo = count($clasesFabricacion) > 0 ? false : ($calidadYaRespondio || (($tieneAprobados || $tieneRechazados) && (!$esReproceso || count($clasesFabricacion) === 0 || !$hasRechazosReal)));
                                                                        @endphp

                                                                        @if (!$ocultarCardEnModelo)
                                                                            <div class="lib-calidad-card" id="control-modelo-{{ md5($reg->ot) }}"
                                                                                style="{{ trim($controlDisabled . ' ' . $hideControlCard) }}">
                                                                                <div class="lib-calidad-card-header">
                                                                                    <img src="{{ ($esReproceso || $esReinicioParcial) ? asset('images/Reproceso.png') : asset('images/almacen.png') }}"
                                                                                        alt="Almacén" class="alm-icon-lg">
                                                                                    <div class="alm-overflow-hidden alm-flex-1">
                                                                                        <span class="lib-calidad-card-title">Control de Modelos &mdash;
                                                                                            Almacén</span>
                                                                                        <span
                                                                                            class="lib-calidad-card-ot">{{ preg_replace('/_\d{8}_\d{6}_.*/', '', $reg->ot) }}</span>
                                                                                    </div>
                                                                                    @if (count($otClasesActivas) > 0)
                                                                                        <div
                                                                                            class="alm-flex-shrink-0 alm-display-flex alm-flex-direction-column alm-align-items-center alm-gap-2px">
                                                                                            <span
                                                                                                style="font-size:1.1em; font-weight:800; color:{{ $todasClasesProcesadas ? '#15803d' : ($algunaClaseProcesada ? '#0369a1' : '#ffffff') }};">
                                                                                                {{ count($clasesActivasCubiertas) }}/{{ count($otClasesActivas) }}
                                                                                            </span>
                                                                                            <span
                                                                                                class="alm-font-size-0-65em alm-font-weight-600 alm-color-rgba-255-255-255-0-75 alm-letter-spacing-0-5px alm-text-transform-uppercase">clases</span>
                                                                                            @if ($todasClasesProcesadas)
                                                                                                <img src="{{ asset('images/ready.png') }}"
                                                                                                    class="alm-width-18px alm-height-18px alm-margin-top-2px"
                                                                                                    alt="Listo">
                                                                                            @endif
                                                                                        </div>
                                                                                    @endif
                                                                                </div>
                                                                                <div class="lib-calidad-card-body">
                                                                                    <div class="lib-calidad-action-row">
                                                                                        <h4 class="lib-calidad-card-prompt">
                                                                                            @if ($isFullySubmitted)
                                                                                                <span
                                                                                                    class="alm-color-15803d alm-font-weight-700 alm-display-inline-flex alm-align-items-center alm-gap-8px">
                                                                                                    <img src="{{ asset('images/ready.png') }}"
                                                                                                        class="alm-icon-md" alt="Listo">
                                                                                                    El proceso ahora le pertenece a Calidad. Por favor,
                                                                                                    espera instrucciones para las clases enviadas.
                                                                                                </span>
                                                                                            @elseif ($todasClasesProcesadas)
                                                                                                <span
                                                                                                    class="alm-color-0369a1 alm-font-weight-700 alm-display-inline-flex alm-align-items-center alm-gap-8px">
                                                                                                    <img src="{{ asset('images/ready.png') }}"
                                                                                                        class="alm-icon-md" alt="Listo">
                                                                                                    ¡Todas las clases procesadas! Falta enviar la alerta a
                                                                                                    Calidad.
                                                                                                </span>
                                                                                            @elseif ($algunaClaseProcesada)
                                                                                                <span class="alm-color-0369a1 alm-font-weight-600">
                                                                                                    Proceso parcial
                                                                                                    ({{ count($clasesActivasCubiertas) }}/{{ count($otClasesActivas) }}
                                                                                                    clases enviadas). Puedes generar o enviar las
                                                                                                    pre-órdenes restantes.
                                                                                                </span>
                                                                                            @elseif ($targetReg->tiene_modelo)
                                                                                                ¡Modelo recibido y procesado! Pendiente de que Calidad lo
                                                                                                revise.
                                                                                            @elseif ($targetReg->pre_orden_email_sent)
                                                                                                Alerta enviada a Calidad. En espera de su revisión y
                                                                                                liberación.
                                                                                            @elseif ($targetReg->pre_orden_sent)
                                                                                                Pre-orden lista. Puedes seguir editando los datos o enviarla
                                                                                                por correo.
                                                                                            @elseif ($esReinicioParcial)
                                                                                                <span
                                                                                                    class="alm-color-0284c7 alm-font-weight-700 alm-display-inline-flex alm-align-items-center alm-gap-8px">
                                                                                                    <img src="{{ asset('images/Reproceso.png') }}"
                                                                                                        class="alm-icon-md" alt="Reinicio">
                                                                                                    Clase(s) reiniciadas:
                                                                                                    <strong>{{ implode(', ', array_map('ucfirst', $clasesFabricacion)) }}</strong>.
                                                                                                    Genera una nueva pre-orden o confirma que cuentas con el
                                                                                                    modelo para continuar.
                                                                                                </span>
                                                                                            @elseif ($esReproceso)
                                                                                                OT en re-proceso por rechazo de Calidad. Genera o edita la
                                                                                                pre-orden de modelo para iniciar el nuevo ciclo de
                                                                                                fabricación.
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
                                                                                                <img src="{{ asset('images/pdf.png') }}"
                                                                                                    alt="Pre-Orden">
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
                                                                                                <img src="{{ asset('images/enviando.png') }}"
                                                                                                    alt="Enviar">
                                                                                                <span>{{ $esReproceso ? 'Enviar Alerta' : 'Enviar Correo' }}</span>
                                                                                            </button>
                                                                                        </div>
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                        @endif
                                                                    @endif
                                                                </div>
                                                            @endif

                                                            {{-- CONTENEDOR 2: PROCESO DE CASTING / MODELOS APROBADOS --}}
                                                            @if ($tieneAprobados)
                                                                @php
                                                                    $castingPre = \App\Models\PreOrdenFundicion::where(function ($q) use ($reg, $targetReg) {
                                                                        $q->where('ot', '=', $reg->ot)->orWhere('ot', '=', $targetReg->ot);
                                                                    })
                                                                    ->where('pdf_filename', 'NOT LIKE', '%_Anterior_N%')
                                                                    ->where(function ($q) {
                                                                        $q->where('pdf_filename', 'LIKE', '%Casting%')
                                                                          ->orWhere('pdf_filename', 'LIKE', '%F_ALM_PFC_%')
                                                                          ->orWhere('pdf_filename', 'LIKE', '%PFC%');
                                                                    })->orderBy('id', 'desc')->first();

                                                                    $hasCastingPre = (bool) $castingPre || (count($almacenPreordenesCasting) > 0);

                                                                    $aprobadosPendientesCasting = [];
                                                                    $clasesAprobadasCubiertas = [];
                                                                    if (!empty($aprobados)) {
                                                                        $allCastingPres = \App\Models\PreOrdenFundicion::where(function ($q) use ($reg, $targetReg) {
                                                                            $q->where('ot', '=', $reg->ot)->orWhere('ot', '=', $targetReg->ot);
                                                                        })
                                                                        ->where(function ($q) {
                                                                            $q->where('pdf_filename', 'LIKE', '%Casting%')
                                                                              ->orWhere('pdf_filename', 'LIKE', '%F_ALM_PFC_%')
                                                                              ->orWhere('pdf_filename', 'LIKE', '%PFC%');
                                                                        })->get();

                                                                        $clasesPreordenCastingValidas = [];
                                                                        foreach ($allCastingPres as $cPo) {
                                                                            if ($cPo->is_sent != 1) continue;
                                                                            $filasC = is_string($cPo->filas) ? json_decode($cPo->filas, true) : $cPo->filas;
                                                                            if (is_array($filasC)) {
                                                                                foreach ($filasC as $fc) {
                                                                                    $cVal = strtolower($fc['clase'] ?? $fc['clase_nombre'] ?? $fc['tipo_modelo'] ?? $fc['nombre'] ?? '');
                                                                                    if (!empty($cVal)) {
                                                                                        $clasesPreordenCastingValidas[$cVal] = $cPo;
                                                                                    }
                                                                                }
                                                                            }
                                                                            $fnLow = strtolower($cPo->pdf_filename ?? '');
                                                                            foreach ($aprobadosNorm as $apN) {
                                                                                if ($apN !== '' && str_contains($fnLow, $apN)) {
                                                                                    $clasesPreordenCastingValidas[$apN] = $cPo;
                                                                                }
                                                                            }
                                                                        }

                                                                        foreach ($almacenPreordenesCasting as $docCasting) {
                                                                            $docNameLow = strtolower(basename($docCasting['nombre']));
                                                                            if (str_contains($docNameLow, '_anterior_n')) continue;
                                                                            foreach ($aprobadosNorm as $apN) {
                                                                                if ($apN !== '' && str_contains($docNameLow, $apN)) {
                                                                                    if (!isset($clasesPreordenCastingValidas[$apN])) {
                                                                                        $clasesPreordenCastingValidas[$apN] = true;
                                                                                    }
                                                                                }
                                                                            }
                                                                        }

                                                                        foreach ($aprobados as $apClase) {
                                                                            $apLow = strtolower($apClase);
                                                                            $cubiertaC = false;
                                                                            $poAssoc = null;

                                                                            foreach ($clasesPreordenCastingValidas as $cKey => $poObj) {
                                                                                if ($cKey === $apLow || str_contains($cKey, $apLow) || str_contains($apLow, $cKey)) {
                                                                                    $cubiertaC = true;
                                                                                    if (is_object($poObj)) {
                                                                                        $poAssoc = $poObj;
                                                                                    }
                                                                                    break;
                                                                                }
                                                                            }

                                                                            if ($cubiertaC && $poAssoc) {
                                                                                $latestLdmClase = \App\Models\LiberacionModeloFundicion::where('ot', $targetReg->ot)
                                                                                    ->where(function ($q) use ($apLow) {
                                                                                        $q->whereRaw("LOWER(tipo_modelo) = ?", [$apLow])
                                                                                          ->orWhereRaw("LOWER(tipo_modelo) LIKE ?", ['%' . $apLow . '%']);
                                                                                    })
                                                                                    ->orderBy('id', 'desc')
                                                                                    ->first();
                                                                                if ($latestLdmClase && strtotime($latestLdmClase->created_at) > strtotime($poAssoc->created_at)) {
                                                                                    $cubiertaC = false;
                                                                                }
                                                                            }

                                                                            if (!$cubiertaC) {
                                                                                $aprobadosPendientesCasting[] = $apClase;
                                                                            } else {
                                                                                $clasesAprobadasCubiertas[] = $apClase;
                                                                            }
                                                                        }
                                                                    }

                                                                    $clasesAccionCasting = !empty($aprobadosPendientesCasting) ? $aprobadosPendientesCasting : $aprobados;
                                                                    $castingEmailSent = ($castingPre && $castingPre->is_sent == 1 && count($aprobadosPendientesCasting) === 0);
                                                                    
                                                                    $tituloCasting = "Etapa: Proceso de Casting / Modelos Aprobados";
                                                                    if (count($aprobadosPendientesCasting) > 0 && count($clasesAprobadasCubiertas) > 0) {
                                                                        $tituloCasting .= " (Pendientes: " . implode(', ', array_map('ucfirst', $aprobadosPendientesCasting)) . " | Procesadas: " . implode(', ', array_map('ucfirst', $clasesAprobadasCubiertas)) . ")";
                                                                    } elseif (count($aprobadosPendientesCasting) > 0) {
                                                                        $tituloCasting .= " (Pendientes: " . implode(', ', array_map('ucfirst', $aprobadosPendientesCasting)) . ")";
                                                                    } elseif (count($clasesAprobadasCubiertas) > 0) {
                                                                        $tituloCasting .= " (" . implode(', ', array_map('ucfirst', $clasesAprobadasCubiertas)) . ")";
                                                                    }
                                                                @endphp
                                                                <div class="alm-process-block"
                                                                    style="margin-bottom: 10px; padding: 20px; border-radius: 14px; background-color: #f0fdf4; border: 2px solid #16a34a; box-shadow: 0 4px 14px rgba(22, 163, 74, 0.08);">
                                                                    <div
                                                                        style="display: flex; align-items: center; justify-content: space-between; border-bottom: 2px solid #16a34a; padding-bottom: 10px; margin-bottom: 15px;">
                                                                        <h3
                                                                            style="margin: 0; color: #16a34a; font-size: 1.15rem; font-weight: 700; display: flex; align-items: center; gap: 10px;">
                                                                            <img src="{{ asset('images/Aprobado.png') }}"
                                                                                style="width: 30px; height: 30px; object-fit: contain;">
                                                                            {{ $tituloCasting }}
                                                                        </h3>
                                                                        <span
                                                                            style="font-size: 0.8rem; font-weight: 700; background: #dcfce7; color: #15803d; padding: 4px 12px; border-radius: 6px; border: 1px solid #bbf7d0;">
                                                                            CASTING / APROBADOS
                                                                        </span>
                                                                    </div>

                                                                    <div class="cal-subcontainer-almacen"
                                                                        style="margin-bottom: 25px; padding: 18px; border-radius: 12px; background-color: #f0fdf4; border: 2px solid #16a34a; box-shadow: 0 3px 10px rgba(22, 163, 74, 0.08);">
                                                                        <div
                                                                            style="display: flex; align-items: center; justify-content: space-between; border-bottom: 1.5px solid #bbf7d0; padding-bottom: 8px; margin-bottom: 15px;">
                                                                            <h4
                                                                                style="margin: 0; color: #15803d; font-size: 1.05rem; font-weight: 700; display: flex; align-items: center; gap: 8px;">
                                                                                <img src="{{ asset('images/almacen.png') }}"
                                                                                    style="width: 22px; height: 22px; object-fit: contain;">
                                                                                Documentos, Dibujos y Ayudas Visuales Aprobados por Almacén
                                                                            </h4>
                                                                            <span
                                                                                style="font-size: 0.75rem; font-weight: 700; background: #dcfce7; color: #15803d; padding: 3px 10px; border-radius: 6px; border: 1px solid #86efac;">
                                                                                DOCUMENTOS ALMACÉN
                                                                            </span>
                                                                        </div>

                                                                        {{-- Dibujos de Casting --}}
                                                                        @if (count($dibujosCasting) > 0)
                                                                            <h4
                                                                                style="margin-top: 10px; margin-bottom: 10px; color: #15803d; font-weight: 700;">
                                                                                Dibujos de Fundición (Casting)</h4>
                                                                            <div class="alm-pdf-grid"
                                                                                style="background-color: #f0fdf4; border: 1px solid #bbf7d0; padding: 15px; border-radius: 8px; margin-bottom: 15px;">
                                                                                @foreach ($dibujosCasting as $archivoInfo)
                                                                                    <div class="dibujos-file-card"
                                                                                        style="animation-delay: {{ $loop->index * 0.05 }}s; border-left-color: #16a34a;">
                                                                                        <div class="file-icon-wrapper alm-cursor-pointer"
                                                                                            title="Abrir PDF">
                                                                                            <img src="{{ asset('images/pdf-view-shadow.png') }}"
                                                                                                class="file-icon icon-default">
                                                                                            <img src="{{ asset('images/pdf-view.png') }}"
                                                                                                class="file-icon icon-hover">
                                                                                        </div>
                                                                                        <div class="file-name alm-cursor-pointer" title="Abrir PDF"
                                                                                            onclick="almacenVerPdf('{{ $archivoInfo['ot'] }}', '{{ $archivoInfo['nombre'] }}', '{{ $archivoInfo['tipo'] }}')">
                                                                                            {{ basename($archivoInfo['nombre']) }}
                                                                                        </div>
                                                                                        <div class="file-actions">
                                                                                            <button
                                                                                                class="btn-dibujos btn-dibujos-sm btn-ver alm-background-color-15803d alm-color-white"
                                                                                                onclick="almacenVerPdf('{{ $archivoInfo['ot'] }}', '{{ $archivoInfo['nombre'] }}', '{{ $archivoInfo['tipo'] }}')">Ver</button>
                                                                                        </div>
                                                                                    </div>
                                                                                @endforeach
                                                                            </div>
                                                                        @endif

                                                                        {{-- Ayudas Visuales de Casting --}}
                                                                        @if (count($ayudasCasting) > 0)
                                                                            <h4
                                                                                style="margin-top: 15px; margin-bottom: 10px; color: #15803d; font-weight: 700;">
                                                                                Ayudas Visuales (Casting)</h4>
                                                                            <div class="alm-pdf-grid"
                                                                                style="background-color: #f0fdf4; border: 1px solid #bbf7d0; padding: 15px; border-radius: 8px; margin-bottom: 15px;">
                                                                                @foreach ($ayudasCasting as $archivoInfo)
                                                                                    @php $ayudaUrl = $archivoInfo['url'] ?? ''; @endphp
                                                                                    <div class="dibujos-file-card card-ayuda"
                                                                                        style="animation-delay: {{ $loop->index * 0.05 }}s;">
                                                                                        <div class="file-icon-wrapper alm-cursor-pointer"
                                                                                            title="Abrir PDF">
                                                                                            <img src="{{ asset('images/pdf-view-shadow.png') }}"
                                                                                                class="file-icon icon-default">
                                                                                            <img src="{{ asset('images/pdf-view.png') }}"
                                                                                                class="file-icon icon-hover">
                                                                                        </div>
                                                                                        <div class="file-name alm-cursor-pointer" title="Abrir PDF"
                                                                                            onclick="almacenAbrirArchivo('{{ $ayudaUrl }}', '{{ $archivoInfo['ot'] }}', '{{ $archivoInfo['nombre'] }}', 'ayuda')">
                                                                                            {{ basename($archivoInfo['nombre']) }}
                                                                                        </div>
                                                                                        <div class="file-actions">
                                                                                            <button
                                                                                                class="btn-dibujos btn-dibujos-sm btn-ver alm-background-color-15803d alm-color-white"
                                                                                                onclick="almacenAbrirArchivo('{{ $ayudaUrl }}', '{{ $archivoInfo['ot'] }}', '{{ $archivoInfo['nombre'] }}', 'ayuda')">Ver</button>
                                                                                        </div>
                                                                                    </div>
                                                                                @endforeach
                                                                            </div>
                                                                        @endif

                                                                        {{-- Documentos Aprobados (solo de clases aprobadas) --}}
                                                                        @php
                                                                            $calidadAprobadosLdmCasting = array_values(array_filter($calidadAprobadosLdm, function ($doc) use ($aprobadosNorm, $rechazadosNorm) {
                                                                                $nameLow = strtolower(basename($doc['nombre']));
                                                                                if (!empty($rechazadosNorm)) {
                                                                                    $mencionaRechazada = false;
                                                                                    foreach ($rechazadosNorm as $rCl) {
                                                                                        if ($rCl !== '' && strpos($nameLow, $rCl) !== false) {
                                                                                            $mencionaRechazada = true;
                                                                                            break;
                                                                                        }
                                                                                    }
                                                                                    if ($mencionaRechazada) {
                                                                                        $mencionaAprobada = false;
                                                                                        foreach ($aprobadosNorm as $ap) {
                                                                                            if ($ap !== '' && strpos($nameLow, $ap) !== false) {
                                                                                                $mencionaAprobada = true;
                                                                                                break;
                                                                                            }
                                                                                        }
                                                                                        if (!$mencionaAprobada) {
                                                                                            return false;
                                                                                        }
                                                                                    }
                                                                                }
                                                                                return true;
                                                                            }));
                                                                            $almacenPreordenesCasting = array_values(array_filter($almacenPreordenes, function ($doc) use ($aprobadosNorm) {
                                                                                $pathLow = strtolower($doc['nombre']);
                                                                                $nameLow = strtolower(basename($doc['nombre']));
                                                                                $isCastingDoc = (
                                                                                    str_contains($pathLow, 'preorden_casting') ||
                                                                                    str_contains($pathLow, 'casting') ||
                                                                                    str_contains($nameLow, 'pfc') ||
                                                                                    str_contains($nameLow, 'f_alm_pfc') ||
                                                                                    str_contains($nameLow, 'efc') ||
                                                                                    str_contains($nameLow, 'f_alm_efc')
                                                                                );
                                                                                if (!$isCastingDoc) {
                                                                                    return false;
                                                                                }
                                                                                if (empty($aprobadosNorm)) {
                                                                                    return true;
                                                                                }
                                                                                foreach ($aprobadosNorm as $ap) {
                                                                                    if ($ap !== '' && strpos($nameLow, $ap) !== false) {
                                                                                        return true;
                                                                                    }
                                                                                }
                                                                                return true;
                                                                            }));
                                                                        @endphp
                                                                        @if (count($calidadAprobadosLdmCasting) > 0)
                                                                            <h4
                                                                                style="margin-top: 15px; margin-bottom: 10px; color: #155724; font-weight: 700;">
                                                                                Documentos Aprobados</h4>
                                                                            <div class="alm-pdf-grid"
                                                                                style="background-color: #f0fdf4; border: 1px solid #bbf7d0; padding: 15px; border-radius: 8px; margin-bottom: 15px;">
                                                                                @foreach ($calidadAprobadosLdmCasting as $otroArchivo)
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
                                                                                            $alertSent = (bool) ($targetReg->pre_orden_email_sent || $targetReg->pre_orden_sent);
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
                                                                                    <div class="dibujos-file-card card-otro"
                                                                                        style="animation-delay: {{ $loop->index * 0.05 }}s; border-left-color: #155724;">
                                                                                        <div class="file-icon-wrapper alm-cursor-pointer"
                                                                                            title="Abrir PDF">
                                                                                            <img src="{{ asset('images/pdf-view-shadow.png') }}"
                                                                                                class="file-icon icon-default">
                                                                                            <img src="{{ asset('images/pdf-view.png') }}"
                                                                                                class="file-icon icon-hover">
                                                                                        </div>
                                                                                        <div class="file-name alm-cursor-pointer" title="Abrir PDF"
                                                                                            onclick="almacenVerPdf('{{ $otroArchivo['ot'] }}', '{{ $otroArchivo['nombre'] }}', '{{ $otroArchivo['tipo'] }}')">
                                                                                            {{ basename($otroArchivo['nombre']) }}
                                                                                        </div>
                                                                                        <div class="file-actions alm-flex-gap-5">
                                                                                            <button
                                                                                                class="btn-dibujos btn-dibujos-sm btn-ver alm-background-color-155724 alm-color-white"
                                                                                                onclick="almacenVerPdf('{{ $otroArchivo['ot'] }}', '{{ $otroArchivo['nombre'] }}', '{{ $otroArchivo['tipo'] }}')">Ver</button>
                                                                                            @if ($canDelete)
                                                                                                <button
                                                                                                    class="btn-dibujos btn-dibujos-sm btn-eliminar alm-bg-danger-white"
                                                                                                    onclick="almacenEliminarOtroArchivo('{{ $otroArchivo['ot'] }}', '{{ $otroArchivo['nombre'] }}', '{{ $otroArchivo['tipo'] }}', this, '{{ $otroArchivo['origin'] ?? '' }}')">Eliminar</button>
                                                                                            @endif
                                                                                        </div>
                                                                                    </div>
                                                                                @endforeach
                                                                            </div>
                                                                        @endif

                                                                        {{-- Pre-órdenes de Modelo (Casting) - solo las de clases aprobadas --}}
                                                                        @if (count($almacenPreordenesCasting) > 0)
                                                                            <h4
                                                                                style="margin-top: 15px; margin-bottom: 10px; color: #15803d; font-weight: 700;">
                                                                                Pre-órdenes de Casting</h4>
                                                                            <div class="alm-pdf-grid"
                                                                                style="background-color: #f0fdf4; border: 1px solid #bbf7d0; padding: 15px; border-radius: 8px; margin-bottom: 15px;">
                                                                                @foreach ($almacenPreordenesCasting as $archivoInfo)
                                                                                    <div class="dibujos-file-card"
                                                                                        style="animation-delay: {{ $loop->index * 0.05 }}s; border-left-color: #16a34a;">
                                                                                        <div class="file-icon-wrapper alm-cursor-pointer"
                                                                                            title="Abrir PDF">
                                                                                            <img src="{{ asset('images/pdf-view-shadow.png') }}"
                                                                                                class="file-icon icon-default">
                                                                                            <img src="{{ asset('images/pdf-view.png') }}"
                                                                                                class="file-icon icon-hover">
                                                                                        </div>
                                                                                        <div class="file-name alm-cursor-pointer" title="Abrir PDF"
                                                                                            onclick="almacenVerPdf('{{ $archivoInfo['ot'] }}', '{{ $archivoInfo['nombre'] }}', 'preorden')">
                                                                                            {{ basename($archivoInfo['nombre']) }}
                                                                                        </div>
                                                                                        <div class="file-actions">
                                                                                            <button
                                                                                                class="btn-dibujos btn-dibujos-sm btn-ver alm-background-color-15803d alm-color-white"
                                                                                                onclick="almacenVerPdf('{{ $archivoInfo['ot'] }}', '{{ $archivoInfo['nombre'] }}', 'preorden')">Ver</button>
                                                                                        </div>
                                                                                    </div>
                                                                                @endforeach
                                                                            </div>
                                                                        @endif
                                                                    </div>

                                                                    {{-- Control de Modelos — Almacén (Aprobados) --}}

                                                                    <div class="lib-calidad-card"
                                                                        id="control-almacen-aprobados-{{ md5($reg->ot) }}"
                                                                        style="margin-top: 15px;">
                                                                        <div
                                                                            class="lib-calidad-card-header alm-background-linear-gradient-135deg-16a34a-15803d alm-border-bottom-2px-solid-rgba-22-163-74-0-5">
                                                                            <img src="{{ asset('images/almacen.png') }}" alt="Almacén"
                                                                                class="alm-icon-lg">
                                                                            <div class="alm-overflow-hidden">
                                                                                <span class="lib-calidad-card-title alm-color-ffffff">Control de
                                                                                    Modelos &mdash; Almacén (Aprobados)</span>
                                                                                <span
                                                                                    class="lib-calidad-card-ot alm-color-d1fae5">{{ preg_replace('/_\d{8}_\d{6}_.*/', '', $reg->ot) }}</span>
                                                                            </div>
                                                                        </div>
                                                                        <div class="lib-calidad-card-body">
                                                                            <div class="lib-calidad-action-row">
                                                                                <h4 class="lib-calidad-card-prompt">
                                                                                    @if ($castingEmailSent)
                                                                                        <span
                                                                                            class="alm-color-15803d alm-font-weight-700 alm-display-inline-flex alm-align-items-center alm-gap-8px">
                                                                                            <img src="{{ asset('images/ready.png') }}"
                                                                                                class="alm-icon-md" alt="Listo">
                                                                                            @if (count($aprobados) == 1)
                                                                                                El proceso de pre-orden ha finalizado para la clase <strong>{{ implode(', ', array_map('ucfirst', $aprobados)) }}</strong>. El correo ha sido enviado al proveedor. Favor de esperar instrucciones.
                                                                                            @elseif (count($aprobados) > 1)
                                                                                                El proceso de pre-orden ha finalizado para las clases <strong>{{ implode(', ', array_map('ucfirst', $aprobados)) }}</strong>. El correo ha sido enviado al proveedor. Favor de esperar instrucciones.
                                                                                            @else
                                                                                                El proceso de pre-orden ha finalizado. El correo ha sido enviado al proveedor. Favor de esperar instrucciones.
                                                                                            @endif
                                                                                        </span>
                                                                                    @elseif (!empty($aprobadosPendientesCasting))
                                                                                        <span class="alm-color-0284c7 alm-font-weight-700">
                                                                                            Clase(s) aprobada(s) pendiente(s) de Pre-Orden de Casting: <strong>{{ implode(', ', array_map('ucfirst', $aprobadosPendientesCasting)) }}</strong>. Genera o envía la pre-orden correspondiente.
                                                                                        </span>
                                                                                    @elseif ($hasCastingPre)
                                                                                        Pre-orden de casting generada {!! count($aprobados) > 0 ? 'para los modelos: <strong>' . e(implode(', ', array_map('ucfirst', $aprobados))) . '</strong>' : '' !!}. Puedes
                                                                                        editar los datos o enviar la pre-orden por correo.
                                                                                    @elseif ($reg->casting_pdf_generated)
                                                                                        Formatos LDM subidos. Procede a generar la Pre-Orden de
                                                                                        Fabricación de Casting {!! count($aprobados) > 0 ? 'para los modelos: <strong>' . e(implode(', ', array_map('ucfirst', $aprobados))) . '</strong>' : '' !!}.
                                                                                    @else
                                                                                        Modelos Aprobados por Calidad{!! count($aprobados) > 0 ? ': <strong>' . e(implode(', ', array_map('ucfirst', $aprobados))) . '</strong>' : '' !!}. Procede a
                                                                                        subir los formatos F-CCL-LDM firmados para iniciar el
                                                                                        casting.
                                                                                    @endif
                                                                                </h4>
                                                                                <div class="lib-calidad-card-btns">
                                                                                    @if ($castingEmailSent)
                                                                                    @elseif ($hasCastingPre)
                                                                                        <button
                                                                                            class="btn-modelo btn-modelo-si alm-bg-success-white"
                                                                                            onclick="abrirModalPreOrdenCasting('{{ $reg->ot }}')">
                                                                                            <img src="{{ asset('images/editar-informacion.png') }}"
                                                                                                alt="Editar">
                                                                                            <span>Editar Pre-orden</span>
                                                                                        </button>
                                                                                        <button
                                                                                            class="btn-modelo btn-modelo-email alm-display-flex alm-background-color-033966 alm-color-white"
                                                                                            onclick="abrirModalEnviarPreOrden('{{ $reg->ot }}', 'casting', {{ json_encode($clasesAccionCasting) }})">
                                                                                            <img src="{{ asset('images/enviando.png') }}"
                                                                                                alt="Enviar">
                                                                                            <span>Enviar Correo</span>
                                                                                        </button>
                                                                                    @elseif ($reg->casting_pdf_generated || count($calidadAprobadosLdm) > 0 || !empty($aprobadosPendientesCasting))
                                                                                        <button
                                                                                            class="btn-modelo btn-modelo-si alm-bg-success-white"
                                                                                            onclick="abrirModalPreOrdenCasting('{{ $reg->ot }}')">
                                                                                            <img src="{{ asset('images/almacen.png') }}"
                                                                                                alt="Preorden"
                                                                                                class="alm-width-16px alm-height-16px alm-filter-brightness-0-invert-1">
                                                                                            <span>Preorden de Casting</span>
                                                                                        </button>
                                                                                        <button
                                                                                            class="btn-modelo btn-modelo-si alm-bg-success-white"
                                                                                            onclick="abrirModalGestionVeredicto('{{ $reg->ot }}', {{ json_encode($clasesAccionCasting) }}, [])">
                                                                                            <img src="{{ asset('images/Aprobado.png') }}" alt="Si">
                                                                                            <span>Procesar Aceptados</span>
                                                                                        </button>
                                                                                    @else
                                                                                        <button
                                                                                            class="btn-modelo btn-modelo-si alm-bg-success-white"
                                                                                            onclick="abrirModalGestionVeredicto('{{ $reg->ot }}', {{ json_encode($clasesAccionCasting) }}, [])">
                                                                                            <img src="{{ asset('images/Aprobado.png') }}" alt="Si">
                                                                                            <span>Procesar Aceptados</span>
                                                                                        </button>
                                                                                        <button
                                                                                            class="btn-modelo btn-modelo-si alm-bg-success-white"
                                                                                            onclick="abrirModalPreOrdenCasting('{{ $reg->ot }}')">
                                                                                            <img src="{{ asset('images/almacen.png') }}"
                                                                                                alt="Preorden"
                                                                                                class="alm-width-16px alm-height-16px alm-filter-brightness-0-invert-1">
                                                                                            <span>Preorden de Casting</span>
                                                                                        </button>
                                                                                    @endif
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            @endif

                                                            {{-- CONTENEDOR 3: MODELOS RECHAZADOS --}}
                                                            @if ($tieneRechazados)
                                                                <div class="alm-process-block"
                                                                    style="margin-bottom: 25px; padding: 20px; border-radius: 14px; background-color: #fef2f2; border: 2px solid #dc2626; box-shadow: 0 4px 14px rgba(220, 38, 38, 0.08);">
                                                                    <div
                                                                        style="display: flex; align-items: center; justify-content: space-between; border-bottom: 2px solid #dc2626; padding-bottom: 10px; margin-bottom: 15px;">
                                                                        <h3
                                                                            style="margin: 0; color: #dc2626; font-size: 1.15rem; font-weight: 700; display: flex; align-items: center; gap: 10px;">
                                                                            <img src="{{ asset('images/Rechazado.png') }}"
                                                                                style="width: 30px; height: 30px; object-fit: contain;">
                                                                            Etapa: Modelos Rechazados
                                                                            ({{ implode(', ', array_map('ucfirst', $rechazados)) }})
                                                                        </h3>
                                                                        <span
                                                                            style="font-size: 0.8rem; font-weight: 700; background: #fee2e2; color: #b91c1c; padding: 4px 12px; border-radius: 6px; border: 1px solid #fecaca;">
                                                                            RECHAZADOS
                                                                        </span>
                                                                    </div>

                                                                    <div class="cal-subcontainer-almacen"
                                                                        style="margin-bottom: 25px; padding: 18px; border-radius: 12px; background-color: #fef2f2; border: 2px solid #dc2626; box-shadow: 0 3px 10px rgba(220, 38, 38, 0.08);">
                                                                        <div
                                                                            style="display: flex; align-items: center; justify-content: space-between; border-bottom: 1.5px solid #fecaca; padding-bottom: 8px; margin-bottom: 15px;">
                                                                            <h4
                                                                                style="margin: 0; color: #b91c1c; font-size: 1.05rem; font-weight: 700; display: flex; align-items: center; gap: 8px;">
                                                                                <img src="{{ asset('images/almacen.png') }}"
                                                                                    style="width: 22px; height: 22px; object-fit: contain;">
                                                                                Documentos, Dibujos y Ayudas Visuales de Rechazo
                                                                            </h4>
                                                                            <span
                                                                                style="font-size: 0.75rem; font-weight: 700; background: #fee2e2; color: #b91c1c; padding: 3px 10px; border-radius: 6px; border: 1px solid #fca5a5;">
                                                                                DOCUMENTOS ALMACÉN
                                                                            </span>
                                                                        </div>

                                                                        {{-- Dibujos Originales Rechazados --}}
                                                                        @if (count($dibujosRechazadosOrig) > 0)
                                                                            <h4
                                                                                style="margin-top: 10px; margin-bottom: 10px; color: #b91c1c; font-weight: 700;">
                                                                                Dibujos de Fundición (Rechazados)</h4>
                                                                            <div class="alm-pdf-grid"
                                                                                style="background-color: #fef2f2; border: 1px solid #fecaca; padding: 15px; border-radius: 8px; margin-bottom: 15px;">
                                                                                @foreach ($dibujosRechazadosOrig as $archivoInfo)
                                                                                    <div class="dibujos-file-card card-otro"
                                                                                        style="animation-delay: {{ $loop->index * 0.05 }}s; border-left-color: #dc2626;">
                                                                                        <div class="file-icon-wrapper alm-cursor-pointer"
                                                                                            title="Abrir PDF">
                                                                                            <img src="{{ asset('images/pdf-view-shadow.png') }}"
                                                                                                class="file-icon icon-default">
                                                                                            <img src="{{ asset('images/pdf-view.png') }}"
                                                                                                class="file-icon icon-hover">
                                                                                        </div>
                                                                                        <div class="file-name alm-cursor-pointer" title="Abrir PDF"
                                                                                            onclick="almacenVerPdf('{{ $archivoInfo['ot'] }}', '{{ $archivoInfo['nombre'] }}', '{{ $archivoInfo['tipo'] }}')">
                                                                                            {{ basename($archivoInfo['nombre']) }}
                                                                                        </div>
                                                                                        <div class="file-actions alm-flex-gap-5">
                                                                                            <button
                                                                                                class="btn-dibujos btn-dibujos-sm btn-ver alm-background-color-b91c1c alm-color-white"
                                                                                                onclick="almacenVerPdf('{{ $archivoInfo['ot'] }}', '{{ $archivoInfo['nombre'] }}', '{{ $archivoInfo['tipo'] }}')">Ver</button>
                                                                                        </div>
                                                                                    </div>
                                                                                @endforeach
                                                                            </div>
                                                                        @endif

                                                                        {{-- Ayudas Visuales Originales Rechazadas --}}
                                                                        @if (count($ayudasRechazadosOrig) > 0)
                                                                            <h4
                                                                                style="margin-top: 15px; margin-bottom: 10px; color: #b91c1c; font-weight: 700;">
                                                                                Ayudas Visuales (Rechazadas)</h4>
                                                                            <div class="alm-pdf-grid"
                                                                                style="background-color: #fef2f2; border: 1px solid #fecaca; padding: 15px; border-radius: 8px; margin-bottom: 15px;">
                                                                                @foreach ($ayudasRechazadosOrig as $archivoInfo)
                                                                                    @php $ayudaUrl = $archivoInfo['url'] ?? ''; @endphp
                                                                                    <div class="dibujos-file-card card-ayuda"
                                                                                        style="animation-delay: {{ $loop->index * 0.05 }}s; border-left-color: #dc2626;">
                                                                                        <div class="file-icon-wrapper alm-cursor-pointer"
                                                                                            title="Abrir PDF">
                                                                                            <img src="{{ asset('images/pdf-view-shadow.png') }}"
                                                                                                class="file-icon icon-default">
                                                                                            <img src="{{ asset('images/pdf-view.png') }}"
                                                                                                class="file-icon icon-hover">
                                                                                        </div>
                                                                                        <div class="file-name alm-cursor-pointer" title="Abrir PDF"
                                                                                            onclick="almacenAbrirArchivo('{{ $ayudaUrl }}', '{{ $archivoInfo['ot'] }}', '{{ $archivoInfo['nombre'] }}', 'ayuda')">
                                                                                            {{ basename($archivoInfo['nombre']) }}
                                                                                        </div>
                                                                                        <div class="file-actions alm-flex-gap-5">
                                                                                            <button
                                                                                                class="btn-dibujos btn-dibujos-sm btn-ver alm-background-color-b91c1c alm-color-white"
                                                                                                onclick="almacenAbrirArchivo('{{ $ayudaUrl }}', '{{ $archivoInfo['ot'] }}', '{{ $archivoInfo['nombre'] }}', 'ayuda')">Ver</button>
                                                                                        </div>
                                                                                    </div>
                                                                                @endforeach
                                                                            </div>
                                                                        @endif

                                                                        {{-- Dibujos Rechazados (Calidad) --}}
                                                                        @if (count($rechazadosDibujos) > 0)
                                                                            <h4
                                                                                style="margin-top: 10px; margin-bottom: 10px; color: #b91c1c; font-weight: 700;">
                                                                                Documentos Adjuntos de Calidad (Dibujos)</h4>
                                                                            <div class="alm-pdf-grid"
                                                                                style="background-color: #fef2f2; border: 1px solid #fecaca; padding: 15px; border-radius: 8px; margin-bottom: 15px;">
                                                                                @foreach ($rechazadosDibujos as $otroArchivo)
                                                                                    <div class="dibujos-file-card card-otro"
                                                                                        style="animation-delay: {{ $loop->index * 0.05 }}s; border-left-color: #dc2626;">
                                                                                        <div class="file-icon-wrapper alm-cursor-pointer"
                                                                                            title="Abrir PDF">
                                                                                            <img src="{{ asset('images/pdf-view-shadow.png') }}"
                                                                                                class="file-icon icon-default">
                                                                                            <img src="{{ asset('images/pdf-view.png') }}"
                                                                                                class="file-icon icon-hover">
                                                                                        </div>
                                                                                        <div class="file-name alm-cursor-pointer" title="Abrir PDF"
                                                                                            onclick="almacenVerPdf('{{ $otroArchivo['ot'] }}', '{{ $otroArchivo['nombre'] }}', '{{ $otroArchivo['tipo'] }}')">
                                                                                            {{ basename($otroArchivo['nombre']) }}
                                                                                        </div>
                                                                                        <div class="file-actions alm-flex-gap-5">
                                                                                            <button
                                                                                                class="btn-dibujos btn-dibujos-sm btn-ver alm-background-color-b91c1c alm-color-white"
                                                                                                onclick="almacenVerPdf('{{ $otroArchivo['ot'] }}', '{{ $otroArchivo['nombre'] }}', '{{ $otroArchivo['tipo'] }}')">Ver</button>
                                                                                        </div>
                                                                                    </div>
                                                                                @endforeach
                                                                            </div>
                                                                        @endif

                                                                        {{-- Ayudas Visuales Rechazadas (Calidad) --}}
                                                                        @if (count($rechazadosAyudas) > 0)
                                                                            <h4
                                                                                style="margin-top: 15px; margin-bottom: 10px; color: #b91c1c; font-weight: 700;">
                                                                                Documentos Adjuntos de Calidad (Ayudas Visuales)</h4>
                                                                            <div class="alm-pdf-grid"
                                                                                style="background-color: #fef2f2; border: 1px solid #fecaca; padding: 15px; border-radius: 8px; margin-bottom: 15px;">
                                                                                @foreach ($rechazadosAyudas as $otroArchivo)
                                                                                    <div class="dibujos-file-card card-ayuda"
                                                                                        style="animation-delay: {{ $loop->index * 0.05 }}s; border-left-color: #dc2626;">
                                                                                        <div class="file-icon-wrapper alm-cursor-pointer"
                                                                                            title="Abrir PDF">
                                                                                            <img src="{{ asset('images/pdf-view-shadow.png') }}"
                                                                                                class="file-icon icon-default">
                                                                                            <img src="{{ asset('images/pdf-view.png') }}"
                                                                                                class="file-icon icon-hover">
                                                                                        </div>
                                                                                        <div class="file-name alm-cursor-pointer" title="Abrir PDF"
                                                                                            onclick="almacenVerPdf('{{ $otroArchivo['ot'] }}', '{{ $otroArchivo['nombre'] }}', '{{ $otroArchivo['tipo'] }}')">
                                                                                            {{ basename($otroArchivo['nombre']) }}
                                                                                        </div>
                                                                                        <div class="file-actions alm-flex-gap-5">
                                                                                            <button
                                                                                                class="btn-dibujos btn-dibujos-sm btn-ver alm-background-color-b91c1c alm-color-white"
                                                                                                onclick="almacenVerPdf('{{ $otroArchivo['ot'] }}', '{{ $otroArchivo['nombre'] }}', '{{ $otroArchivo['tipo'] }}')">Ver</button>
                                                                                        </div>
                                                                                    </div>
                                                                                @endforeach
                                                                            </div>
                                                                        @endif

                                                                        {{-- Documentos de Rechazo / SCAR --}}
                                                                        @if (count($rechazadosOtros) > 0)
                                                                            <h4
                                                                                style="margin-top: 15px; margin-bottom: 10px; color: #721c24; font-weight: 700;">
                                                                                Documentos de Rechazo</h4>
                                                                            <div class="alm-pdf-grid"
                                                                                style="background-color: #fef2f2; border: 1px solid #fecaca; padding: 15px; border-radius: 8px; margin-bottom: 15px;">
                                                                                @foreach ($rechazadosOtros as $otroArchivo)
                                                                                    <div class="dibujos-file-card card-otro"
                                                                                        style="animation-delay: {{ $loop->index * 0.05 }}s; border-left-color: #721c24;">
                                                                                        <div class="file-icon-wrapper alm-cursor-pointer"
                                                                                            title="Abrir PDF">
                                                                                            <img src="{{ asset('images/pdf-view-shadow.png') }}"
                                                                                                class="file-icon icon-default">
                                                                                            <img src="{{ asset('images/pdf-view.png') }}"
                                                                                                class="file-icon icon-hover">
                                                                                        </div>
                                                                                        <div class="file-name alm-cursor-pointer" title="Abrir PDF"
                                                                                            onclick="almacenVerPdf('{{ $otroArchivo['ot'] }}', '{{ $otroArchivo['nombre'] }}', '{{ $otroArchivo['tipo'] }}')">
                                                                                            {{ basename($otroArchivo['nombre']) }}
                                                                                        </div>
                                                                                        <div class="file-actions alm-flex-gap-5">
                                                                                            <button
                                                                                                class="btn-dibujos btn-dibujos-sm btn-ver alm-background-color-b91c1c alm-color-white"
                                                                                                onclick="almacenVerPdf('{{ $otroArchivo['ot'] }}', '{{ $otroArchivo['nombre'] }}', '{{ $otroArchivo['tipo'] }}')">Ver</button>
                                                                                            @php
                                                                                                $canDeleteRechazado = false;
                                                                                                $rUserPerfil = Auth::user()->perfil;
                                                                                                $rAlertSent = in_array($targetReg->calidad_revision_status, ['calidad_aprobado', 'calidad_rechazado', 'calidad_mixto', 'calidad_parcial', 'casting_aprobado']);
                                                                                                if (!$rAlertSent && ($rUserPerfil == 1 || $rUserPerfil == 2 || $rUserPerfil == 3 || $rUserPerfil == 4)) {
                                                                                                    $canDeleteRechazado = true;
                                                                                                }
                                                                                            @endphp
                                                                                            @if ($canDeleteRechazado)
                                                                                                <button
                                                                                                    class="btn-dibujos btn-dibujos-sm btn-eliminar alm-bg-danger-white"
                                                                                                    onclick="almacenEliminarOtroArchivo('{{ $otroArchivo['ot'] }}', '{{ $otroArchivo['nombre'] }}', '{{ $otroArchivo['tipo'] }}', this, '{{ $otroArchivo['origin'] ?? '' }}')">Eliminar</button>
                                                                                            @endif
                                                                                        </div>
                                                                                    </div>
                                                                                @endforeach
                                                                            </div>
                                                                        @endif
                                                                    </div>



                                                                    {{-- Control de Modelos — Almacén (Rechazados) --}}
                                                                    @php
                                                                        $latestReproceso = null;
                                                                        if ($reg->rechazos_procesados) {
                                                                            $latestReproceso = \App\Models\FundicionHistory::where('ot', 'LIKE', $reg->ot . '_R%')
                                                                                ->orderBy('id', 'desc')
                                                                                ->first();
                                                                        }
                                                                    @endphp
                                                                    <div class="lib-calidad-card"
                                                                        id="control-almacen-rechazados-{{ md5($reg->ot) }}"
                                                                        style="margin-top: 15px;">
                                                                        <div
                                                                            class="lib-calidad-card-header alm-background-linear-gradient-135deg-dc2626-b91c1c alm-border-bottom-2px-solid-rgba-220-38-38-0-5">
                                                                            <img src="{{ asset('images/Reproceso.png') }}" alt="Reproceso"
                                                                                class="alm-icon-lg">
                                                                            <div class="alm-overflow-hidden">
                                                                                <span class="lib-calidad-card-title alm-color-ffffff">Control de
                                                                                    Modelos &mdash; Almacén (Rechazados)</span>
                                                                                <span
                                                                                    class="lib-calidad-card-ot alm-color-fee2e2">{{ preg_replace('/_\d{8}_\d{6}_.*/', '', $reg->ot) }}</span>
                                                                            </div>
                                                                        </div>
                                                                        <div class="lib-calidad-card-body">
                                                                            <div class="lib-calidad-action-row">
                                                                                <h4 class="lib-calidad-card-prompt">
                                                                                    @if ($reg->rechazos_procesados)
                                                                                        @if ($latestReproceso)
                                                                                            <div
                                                                                                class="alm-background-linear-gradient-to-right-f8fafc-f1f5f9 alm-border-left-4px-solid-0284c7 alm-padding-14px-18px alm-border-radius-8px alm-box-shadow-0-2px-6px-rgba-0-0-0-0-06 alm-margin-bottom-5px alm-display-inline-block">
                                                                                                <span
                                                                                                    class="alm-color-1e293b alm-font-weight-600 alm-display-inline-flex alm-align-items-center alm-gap-12px alm-font-size-1-05rem">
                                                                                                    <span
                                                                                                        class="alm-display-flex alm-align-items-center alm-justify-content-center alm-background-e0f2fe alm-width-38px alm-height-38px alm-border-radius-50pct alm-flex-shrink-0">
                                                                                                        <img src="{{ asset('images/redireccionar.png') }}"
                                                                                                            class="alm-width-22px alm-height-22px alm-filter-invert-36-sepia-87-saturate-1514-hue-rotate-176deg-brightness-94-contrast-101pct"
                                                                                                            alt="Info">
                                                                                                    </span>
                                                                                                    <span class="alm-line-height-1-45">
                                                                                                        El reproceso de la <strong
                                                                                                            class="alm-color-dc2626 alm-background-fee2e2 alm-padding-2px-6px alm-border-radius-4px alm-font-weight-800">{{ $reg->ot }}</strong>
                                                                                                        se está trabajando en la nueva OT <strong
                                                                                                            class="alm-color-15803d alm-background-dcfce7 alm-padding-2px-6px alm-border-radius-4px alm-font-weight-800">{{ preg_replace('/_\d{8}_\d{6}_.*/', '', $latestReproceso->ot) }}</strong>.<br>
                                                                                                        <span
                                                                                                            class="alm-font-size-0-9rem alm-color-64748b alm-font-weight-500">Presiona
                                                                                                            el botón para redirigirte a la nueva Orden
                                                                                                            de Trabajo.</span>
                                                                                                    </span>
                                                                                                </span>
                                                                                            </div>
                                                                                        @else
                                                                                            Formatos de rechazo y SCAR subidos para los modelos:
                                                                                            <strong>{{ implode(', ', $rechazados) }}</strong>. Nueva
                                                                                            pre-orden de modelo generada.
                                                                                        @endif
                                                                                    @else
                                                                                        Modelos Rechazados por Calidad:
                                                                                        <strong>{{ implode(', ', $rechazados) }}</strong>. Procede a
                                                                                        subir el Formato de Rechazo y el SCAR correspondiente.
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
                                                                                                <img src="{{ asset('images/redireccionar.png') }}"
                                                                                                    alt="Ir"
                                                                                                    class="alm-width-24px alm-height-24px alm-filter-brightness-0-invert-1">
                                                                                                <span
                                                                                                    class="alm-font-weight-700 alm-letter-spacing-0-5px">Ir
                                                                                                    a la Nueva OT</span>
                                                                                            </button>
                                                                                        @else
                                                                                            <button
                                                                                                class="btn-modelo btn-modelo-no alm-display-flex alm-background-color-b91c1c alm-color-white">
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
                                                                </div>
                                                            @endif
                                                        </div>
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
        <div class="alm-modal-content alm-border-radius-20px alm-border-2-5px-solid-0a8504 alm-overflow-hidden"
            style="max-width: 1720px; width: 97vw; max-height: 96vh; height: 95vh; display: flex; flex-direction: column; margin: auto;">
            <div
                class="alm-modal-header alm-background-linear-gradient-135deg-0a8504-064e03 alm-border-bottom-2px-solid-064e03 alm-padding-0-9em-2-2em alm-position-relative">
                <div class="div-cerrar">
                    <button type="button" class="btn-cerrar" onclick="cerrarModalConfirmarModelo()">
                        <img class="img-cerrar" src="{{ asset('images/cerrar.png') }}" alt="Cerrar" style="width: 36px !important; height: 36px !important;">
                    </button>
                </div>
                <div class="alm-display-flex alm-align-items-center alm-gap-16px">
                    <img src="{{ asset('images/Aprobado.png') }}"
                        style="width: 34px !important; height: 34px !important; max-width: 34px !important; max-height: 34px !important; object-fit: contain; flex-shrink: 0;"
                        alt="">
                    <div>
                        <h3
                            class="alm-color-fff alm-margin-0 alm-font-size-1-3em alm-font-weight-800 alm-font-family-Poppins-sans-serif">
                            Confirmar Disponibilidad del Modelo</h3>
                        <div id="confirmar-modelo-subtitle"
                            class="alm-color-rgba-255-255-255-0-9 alm-font-size-0-88em alm-margin-top-2px alm-font-weight-500 alm-font-family-Poppins-sans-serif">
                            OT: -</div>
                    </div>
                </div>
            </div>
            <div class="alm-modal-body alm-padding-1em-1-6em-1-2em-1-6em alm-background-fafafa alm-font-family-Poppins-sans-serif"
                style="flex: 1; display: flex; flex-direction: column; overflow: hidden; min-height: 0;">
                <form id="formConfirmarModelo" enctype="multipart/form-data"
                    style="display: flex; flex-direction: column; flex: 1; min-height: 0;"
                    data-email-modelo="{{ env('EMAIL_PROVEEDOR_MODELOS', 'produccion@ssmetalf.mx,asistenteprod@ssmetalf.mx') }}"
                    data-email-calidad="{{ env('EMAIL_CALIDAD', 'inspecciontec@grupoindsaavedra.com') }}">
                    <input type="hidden" id="cm-ot" name="ot">
                    <input type="hidden" id="cm-id-hash" name="id_hash">

                    <div
                        class="alm-background-fef9c3 alm-border-1px-solid-fde047 alm-border-radius-12px alm-padding-7px-14px alm-color-713f12 alm-font-size-0-86em alm-line-height-1-3 alm-margin-bottom-12px">
                        <strong>Documentos requeridos:</strong> Adjunta las evidencias o remisión que acrediten la recepción
                        del modelo para esta OT.
                    </div>

                    <div
                        style="display: grid; grid-template-columns: minmax(360px, 1fr) minmax(600px, 1.55fr); gap: 18px; align-items: stretch; flex: 1; min-height: 0;">

                        <!-- Columna Izquierda: Datos del Formulario + Botón Verde de Selección -->
                        <div style="display: flex; flex-direction: column; gap: 12px; min-height: 0;">

                            <!-- Bloque 1: Formulario Principal -->
                            <div
                                style="background: #fff; padding: 14px 16px; border-radius: 14px; border: 1px solid #e2e8f0; box-shadow: 0 4px 10px rgba(0,0,0,0.03);">
                                <h4
                                    style="margin-top: 0; margin-bottom: 8px; color: #0a8504; font-size: 1em; border-bottom: 2px solid #0a8504; padding-bottom: 4px; font-weight: 700; font-family: 'Poppins', sans-serif; display: flex; align-items: center; gap: 8px;">
                                    <img src="{{ asset('images/copia-de-datos.png') }}"
                                        style="width: 18px; height: 18px; object-fit: contain;"> Datos de Confirmación
                                </h4>

                                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
                                    <div class="form-group" id="div-cm-destinatario">
                                        <label for="cm-destinatario"
                                            style="font-weight: 700; color: #334155; display: block; margin-bottom: 2px; font-size: 0.84em;">Notificar
                                            a Proveedor:</label>
                                        <input type="text" id="cm-destinatario" name="destinatario" class="form-control"
                                            style="font-size: 0.84em; padding: 6px 10px; height: auto;">
                                    </div>

                                    <div class="form-group" id="div-cm-destinatario-calidad">
                                        <label for="cm-destinatario-calidad"
                                            style="font-weight: 700; color: #334155; display: block; margin-bottom: 2px; font-size: 0.84em;">Notificar
                                            a Calidad:</label>
                                        <input type="text" id="cm-destinatario-calidad" name="destinatario_calidad"
                                            class="form-control"
                                            style="font-size: 0.84em; padding: 6px 10px; height: auto;">
                                    </div>
                                </div>

                                <div class="form-group" style="margin-top: 6px;">
                                    <label for="cm-fecha"
                                        style="font-weight: 700; color: #334155; display: block; margin-bottom: 2px; font-size: 0.84em;">Fecha
                                        de Envío <span class="alm-text-dark-red">*</span>:</label>
                                    <input type="date" id="cm-fecha" name="fecha" class="form-control"
                                        style="font-size: 0.84em; padding: 6px 10px; height: auto;">
                                </div>

                                <div class="form-group" style="margin-top: 6px; margin-bottom: 0;">
                                    <label
                                        style="font-weight: 700; color: #334155; display: block; margin-bottom: 2px; font-size: 0.84em;">Clases
                                        Disponibles <span class="alm-text-dark-red">*</span>:</label>
                                    <div id="cm-clases-container"
                                        style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 6px 10px; display: flex; flex-wrap: wrap; gap: 6px; max-height: 100px; overflow-y: auto;">
                                        <div
                                            class="alm-spinner alm-border-top-color-0284c7 alm-display-block alm-margin-5px-auto">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Bloque 2 (VERDE): SOLO el Botón Dropzone para Seleccionar/Cargar -->
                            <div
                                style="background: #f0fdf4; border: 2px solid #16a34a; padding: 14px 16px; border-radius: 14px; box-shadow: 0 4px 10px rgba(22, 163, 74, 0.08);">
                                <h4
                                    style="margin-top: 0; margin-bottom: 6px; color: #15803d; font-size: 0.96em; border-bottom: 1.5px solid #16a34a; padding-bottom: 4px; font-weight: 700; font-family: 'Poppins', sans-serif; display: flex; align-items: center; gap: 6px;">
                                    <img src="{{ asset('images/anadir.png') }}"
                                        style="width: 18px; height: 18px; object-fit: contain;"> Subir Nuevos Archivos <span
                                        class="alm-text-dark-red">*</span>
                                </h4>

                                <div class="custom-file-dropzone"
                                    style="border: 2px dashed #16a34a; background: #ffffff; padding: 12px 14px; border-radius: 10px; text-align: center; cursor: pointer; position: relative;">
                                    <input type="file" id="cm-archivos" name="archivos[]" class="custom-file-input"
                                        style="position: absolute; top:0; left:0; width:100%; height:100%; opacity:0; cursor:pointer;"
                                        multiple>
                                    <div class="dropzone-content"
                                        style="display: flex; align-items: center; justify-content: center; gap: 8px;">
                                        <img src="{{ asset('images/anadir.png') }}"
                                            style="width: 22px; height: 22px; object-fit: contain;">
                                        <span style="font-weight: 700; color: #15803d; font-size: 0.86em;">Haz clic o
                                            arrastra PDFs e imágenes aquí</span>
                                    </div>
                                </div>
                            </div>

                        </div>

                        <!-- Columna Derecha: 2 Sub-contenedores bien diferenciados -->
                        <div style="display: flex; flex-direction: column; gap: 14px; height: 100%; min-height: 0; box-sizing: border-box;">
                            
                            <!-- Sub-contenedor 1 (AZUL ICE): Archivos y Dibujos de la OT Disponibles -->
                            <div style="background: #f0f7ff; border: 2px solid #0284c7; padding: 14px 16px; border-radius: 14px; box-shadow: 0 4px 10px rgba(2, 132, 199, 0.08); flex: 1.45; display: flex; flex-direction: column; min-height: 0;">
                                <h4 style="margin-top: 0; margin-bottom: 6px; color: #0369a1; font-size: 1.02em; border-bottom: 2px solid #0284c7; padding-bottom: 4px; font-weight: 700; font-family: 'Poppins', sans-serif; display: flex; align-items: center; gap: 8px; flex-shrink: 0;">
                                    <img src="{{ asset('images/galeria.png') }}" style="width: 18px; height: 18px; object-fit: contain;"> Archivos y Dibujos de la OT Disponibles
                                </h4>

                                <div id="cm-server-files-container" style="background: #f0f7ff; border: 1px solid #bae6fd; border-radius: 10px; padding: 12px; flex: 1; max-height: 380px; overflow-y: auto; display: flex; flex-direction: column; gap: 10px;">
                                    <div class="alm-spinner alm-border-top-color-0284c7 alm-display-block alm-margin-10px-auto"></div>
                                </div>
                            </div>

                            <!-- Sub-contenedor 2 (VERDE ESMERALDA): Nuevos Archivos Adjuntados (Coincide en color con el botón de la izquierda) -->
                            <div style="background: #f0fdf4; border: 2px solid #16a34a; padding: 14px 16px; border-radius: 14px; box-shadow: 0 4px 10px rgba(22, 163, 74, 0.08); flex: 1; display: flex; flex-direction: column; min-height: 0;">
                                <h4 style="margin-top: 0; margin-bottom: 6px; color: #15803d; font-size: 0.98em; border-bottom: 1.5px solid #16a34a; padding-bottom: 4px; font-weight: 700; font-family: 'Poppins', sans-serif; display: flex; align-items: center; gap: 6px; flex-shrink: 0;">
                                    <img src="{{ asset('images/anadir.png') }}" style="width: 16px; height: 16px; object-fit: contain;"> Nuevos Archivos Adjuntados
                                </h4>

                                <div id="cm-archivos-list" style="background: #f0fdf4; border: 1px solid #a7f3d0; border-radius: 10px; padding: 12px; flex: 1; max-height: 250px; min-height: 140px; overflow-y: auto; display: flex; flex-wrap: wrap; gap: 10px; align-items: flex-start;"></div>
                            </div>

                        </div>

                    </div>

                    <div class="form-actions"
                        style="text-align: center; margin-top: 10px; padding-top: 8px; flex-shrink: 0;">
                        <button type="submit" class="btn-save-preorden"
                            style="background: linear-gradient(135deg, #0a8504, #064e03); box-shadow: 0 4px 15px rgba(10, 133, 4, 0.35); padding: 11px 44px; border: none; border-radius: 10px; color: #fff; font-weight: 700; cursor: pointer; font-size: 1.05em; display: inline-flex; align-items: center; justify-content: center; gap: 8px;">
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
                                    <option value="SS Metal Foundry, S. de R. L. de C. V." selected>SS Metal Foundry, S. de
                                        R. L. de C. V.</option>
                                    <option value="Sociedad Cooperativa de Producción Jacarandas">Sociedad Cooperativa de
                                        Producción Jacarandas</option>
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
                                        <th class="alm-width-16pct">Tipo de Modelo <span class="alm-text-danger">*</span>
                                        </th>
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

    @include('almacen.partials._modal_preorden_casting')

    <div id="modalEnviarPreOrden" class="alm-modal">
        <div class="alm-modal-content"
            style="max-width: 1720px; width: 97vw; max-height: 96vh; height: 95vh; display: flex; flex-direction: column; margin: auto;">
            <div class="alm-modal-header" style="padding: 0.9em 2.2em;">
                <div class="div-cerrar">
                    <button type="button" class="btn-cerrar" onclick="cerrarModalEnviarPreOrden()">
                        <img class="img-cerrar" src="{{ asset('images/cerrar.png') }}" style="width: 36px !important; height: 36px !important;">
                    </button>
                </div>
                <h3>Enviar Pre-Orden por Correo</h3>
                <p id="env-po-modal-subtitle"
                    class="lib-modal-subtitle alm-color-bae6fd alm-font-size-0-88em alm-margin-top-2px alm-margin-bottom-0">
                </p>
            </div>
            <div class="alm-modal-body alm-padding-1em-1-6em-1-2em-1-6em"
                style="flex: 1; display: flex; flex-direction: column; overflow: hidden; min-height: 0;">
                <form id="formEnviarPreOrden" enctype="multipart/form-data"
                    style="display: flex; flex-direction: column; flex: 1; min-height: 0;"
                    data-email-modelo="{{ env('EMAIL_PROVEEDOR_MODELOS', 'produccion@ssmetalf.mx,asistenteprod@ssmetalf.mx') }}"
                    data-email-casting="{{ env('EMAIL_PRODUCCION_SS', 'produccion@ssmetalf.mx,laboratorio@ssmetalf.mx') }}"
                    data-email-calidad="{{ env('EMAIL_CALIDAD', 'inspecciontec@grupoindsaavedra.com') }}"
                    data-email-jacarandas="{{ env('EMAIL_PRODUCCION_JACARANDAS', 'ventas_jacarandas@prodigy.net.mx,requisicionestec@grupoindsaavedra.com') }}">
                    <input type="hidden" id="env-ot" name="ot">
                    <input type="hidden" id="env-tipo" name="tipo" value="modelo">

                    <div
                        style="display: grid; grid-template-columns: minmax(360px, 1fr) minmax(600px, 1.55fr); gap: 18px; align-items: stretch; flex: 1; min-height: 0;">

                        <!-- Columna Izquierda: Información de Envío + Botón Verde de Selección -->
                        <div style="display: flex; flex-direction: column; gap: 12px; min-height: 0;">

                            <!-- Bloque 1: Formulario de Envío -->
                            <div
                                style="background: #fff; padding: 14px 16px; border-radius: 14px; border: 1px solid #e2e8f0; box-shadow: 0 4px 10px rgba(0,0,0,0.03);">
                                <h4
                                    style="margin-top: 0; margin-bottom: 8px; color: #033966; font-size: 1em; border-bottom: 2px solid #033966; padding-bottom: 4px; font-weight: 700; font-family: 'Poppins', sans-serif; display: flex; align-items: center; gap: 8px;">
                                    <img src="{{ asset('images/enviando.png') }}"
                                        style="width: 18px; height: 18px; object-fit: contain;"> Información del Envío
                                </h4>

                                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
                                    <div class="form-group" id="div-env-destinatario">
                                        <label for="env-destinatario"
                                            style="font-weight: 700; color: #334155; display: block; margin-bottom: 2px; font-size: 0.84em;">Notificar
                                            a Proveedor:</label>
                                        <input type="text" id="env-destinatario" name="destinatario" class="form-control"
                                            style="font-size: 0.84em; padding: 6px 10px; height: auto;" required>
                                    </div>

                                    <div class="form-group" id="div-env-destinatario-calidad">
                                        <label for="env-destinatario-calidad"
                                            style="font-weight: 700; color: #334155; display: block; margin-bottom: 2px; font-size: 0.84em;">Notificar
                                            a Calidad:</label>
                                        <input type="text" id="env-destinatario-calidad" name="destinatario_calidad"
                                            class="form-control"
                                            style="font-size: 0.84em; padding: 6px 10px; height: auto;">
                                    </div>
                                </div>

                                <div class="form-group" style="margin-top: 6px;">
                                    <label for="env-fecha-entrega"
                                        style="font-weight: 700; color: #334155; display: block; margin-bottom: 2px; font-size: 0.84em;">Fecha
                                        de Entrega acordada:</label>
                                    <input type="date" id="env-fecha-entrega" name="fecha_entrega" class="form-control"
                                        style="font-size: 0.84em; padding: 6px 10px; height: auto;" required>
                                </div>

                                <div class="form-group" style="margin-top: 6px; margin-bottom: 0;">
                                    <label
                                        style="font-weight: 700; color: #334155; display: block; margin-bottom: 2px; font-size: 0.84em;">Pre-órdenes
                                        pendientes por enviar:</label>
                                    <div id="env-pending-preordenes-container"
                                        style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 6px 10px; max-height: 100px; overflow-y: auto; display: flex; flex-direction: column; gap: 6px;">
                                    </div>
                                </div>
                            </div>

                            <!-- Bloque 2 (VERDE): SOLO el Botón Dropzone de Selección -->
                            <div
                                style="background: #f0fdf4; border: 2px solid #16a34a; padding: 14px 16px; border-radius: 14px; box-shadow: 0 4px 10px rgba(22, 163, 74, 0.08);">
                                <h4
                                    style="margin-top: 0; margin-bottom: 6px; color: #15803d; font-size: 0.96em; border-bottom: 1.5px solid #16a34a; padding-bottom: 4px; font-weight: 700; font-family: 'Poppins', sans-serif; display: flex; align-items: center; gap: 6px;">
                                    <img src="{{ asset('images/anadir.png') }}"
                                        style="width: 18px; height: 18px; object-fit: contain;"> Subir Nuevos Archivos
                                </h4>

                                <div class="custom-file-dropzone"
                                    style="border: 2px dashed #16a34a; background: #ffffff; padding: 12px 14px; border-radius: 10px; text-align: center; cursor: pointer; position: relative;">
                                    <input type="file" id="env-archivos-adicionales" name="archivos_adicionales[]"
                                        class="custom-file-input"
                                        style="position: absolute; top:0; left:0; width:100%; height:100%; opacity:0; cursor:pointer;"
                                        multiple>
                                    <div class="dropzone-content"
                                        style="display: flex; align-items: center; justify-content: center; gap: 8px;">
                                        <img src="{{ asset('images/anadir.png') }}"
                                            style="width: 22px; height: 22px; object-fit: contain;">
                                        <span style="font-weight: 700; color: #15803d; font-size: 0.86em;">Arrastrar
                                            adicionales aquí (PDFs o imágenes)</span>
                                    </div>
                                </div>
                            </div>

                        </div>

                        <!-- Columna Derecha: 2 Sub-contenedores bien diferenciados -->
                        <div style="display: flex; flex-direction: column; gap: 14px; height: 100%; min-height: 0; box-sizing: border-box;">
                            
                            <!-- Sub-contenedor 1 (AZUL ICE): Archivos y Dibujos de la OT Disponibles -->
                            <div style="background: #f0f7ff; border: 2px solid #0284c7; padding: 14px 16px; border-radius: 14px; box-shadow: 0 4px 10px rgba(2, 132, 199, 0.08); flex: 1.45; display: flex; flex-direction: column; min-height: 0;">
                                <h4 style="margin-top: 0; margin-bottom: 6px; color: #0369a1; font-size: 1.02em; border-bottom: 2px solid #0284c7; padding-bottom: 4px; font-weight: 700; font-family: 'Poppins', sans-serif; display: flex; align-items: center; gap: 8px; flex-shrink: 0;">
                                    <img src="{{ asset('images/galeria.png') }}" style="width: 18px; height: 18px; object-fit: contain;"> Archivos y Dibujos de la OT Disponibles
                                </h4>

                                <div id="env-server-files-container" style="background: #f0f7ff; border: 1px solid #bae6fd; border-radius: 10px; padding: 12px; flex: 1; max-height: 380px; overflow-y: auto; display: flex; flex-direction: column; gap: 10px;">
                                    <div class="alm-spinner alm-border-top-color-033966 alm-display-block alm-margin-10px-auto"></div>
                                </div>
                            </div>

                            <!-- Sub-contenedor 2 (VERDE ESMERALDA): Nuevos Archivos Adjuntados (Coincide en color with left button) -->
                            <div style="background: #f0fdf4; border: 2px solid #16a34a; padding: 14px 16px; border-radius: 14px; box-shadow: 0 4px 10px rgba(22, 163, 74, 0.08); flex: 1; display: flex; flex-direction: column; min-height: 0;">
                                <h4 style="margin-top: 0; margin-bottom: 6px; color: #15803d; font-size: 0.98em; border-bottom: 1.5px solid #16a34a; padding-bottom: 4px; font-weight: 700; font-family: 'Poppins', sans-serif; display: flex; align-items: center; gap: 6px; flex-shrink: 0;">
                                    <img src="{{ asset('images/anadir.png') }}" style="width: 16px; height: 16px; object-fit: contain;"> Nuevos Archivos Adjuntados
                                </h4>

                                <div id="env-archivos-adicionales-list" style="background: #f0fdf4; border: 1px solid #a7f3d0; border-radius: 10px; padding: 12px; flex: 1; max-height: 250px; min-height: 140px; overflow-y: auto; display: flex; flex-wrap: wrap; gap: 10px; align-items: flex-start;"></div>
                            </div>

                        </div>

                    </div>

                    <div class="form-actions"
                        style="text-align: center; margin-top: 10px; padding-top: 8px; flex-shrink: 0;">
                        <button type="submit" id="btn-submit-envio" class="btn-save-preorden"
                            style="background: linear-gradient(135deg, #033966, #022340); box-shadow: 0 4px 15px rgba(3, 57, 102, 0.35); padding: 11px 44px; border: none; border-radius: 10px; color: #fff; font-weight: 700; cursor: pointer; font-size: 1.05em; display: inline-flex; align-items: center; justify-content: center; gap: 8px;">
                            Enviar Correo con Adjuntos
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>



    @include('almacen.partials._modal_iniciar_casting')

    <div id="modalRevisarCambios" class="alm-modal">
        <div class="alm-modal-content alm-max-width-800px">
            <div class="alm-modal-header">
                <div class="div-cerrar">
                    <button type="button" class="btn-cerrar" onclick="cerrarModalRevisarCambios()">
                        <img class="img-cerrar" src="{{ asset('images/cerrar.png') }}">
                    </button>
                </div>
                <h3>Cambios Pendientes en Dibujos de Fundición</h3>
                <p class="lib-modal-subtitle alm-color-bae6fd alm-font-size-0-9em alm-margin-top-4px alm-margin-bottom-0">
                    Se registraron cambios en Dibujos de Fundición. ¿Deseas reiniciar el proceso desde el inicio (borrando
                    estados de Calidad actuales) o solo cambiar los dibujos viejos por los nuevos manteniendo el progreso de
                    la OT?
                </p>
            </div>
            <div class="alm-modal-body">
                <div id="revisar-cambios-container" class="alm-display-flex alm-flex-direction-column alm-gap-15px">
                    <!-- Contenido dinámico -->
                </div>
                <div class="alm-margin-top-20px alm-display-flex alm-gap-15px alm-justify-content-center">
                    <button type="button" id="btn-resolver-reiniciar"
                        class="btn-save-preorden alm-background-color-b91c1c alm-box-shadow-0-4px-15px-rgba-220-38-38-0-3"
                        onclick="almacenResolverCambios('reiniciar')">
                        Reiniciar Proceso Completo
                    </button>
                    <button type="button" id="btn-resolver-mantener"
                        class="btn-save-preorden alm-background-linear-gradient-135deg-0a8504-064e03 alm-box-shadow-0-4px-15px-rgba-10-133-4-0-35"
                        onclick="almacenResolverCambios('mantener')">
                        Solo Reemplazar Archivos
                    </button>
                </div>
            </div>
        </div>
    </div>

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