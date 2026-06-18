@extends('layouts.appMenu')

@section('head')
    @php
        $perfil = Auth::user()->perfil;
        $deptName = ($perfil == 1 || $perfil == 2) ? 'Administración' : ($perfil == 4 ? 'Calidad' : 'Almacén');
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
            $deptName = ($perfil == 1 || $perfil == 2) ? 'Administración' : ($perfil == 4 ? 'Calidad' : 'Almacén');
            $deptIcon = $perfil == 4 ? 'Quality.png' : 'almacen.png';
        @endphp

        <div class="alm-header">
            <div class="alm-header-icon">
                <img src="{{ asset('images/' . $deptIcon) }}" alt="{{ $deptName }}" style="width: 90px;">
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

                
                <div class="alm-filters-card" style="margin-bottom: 2em; background: rgba(255, 255, 255, 0.95); backdrop-filter: blur(10px); border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.08); position: relative; padding: 1.6em;">
                    <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 16px; border-bottom: 2px solid #e2e8f0; padding-bottom: 12px;">
                        <img src="{{ asset('images/Quality.png') }}" alt="Leyenda" style="width: 30px; height: 30px; object-fit: contain;">
                        <h2 style="margin: 0; font-size: 1.30rem; color: #0f172a; font-weight: 700;">Guía de Estados de Modelo</h2>
                    </div>

                    <h3 style="font-size: 0.92rem; color: #475569; font-weight: 700; margin: 0 0 10px 0; border-left: 4px solid #94a3b8; padding-left: 8px;">Estados de Transición</h3>
                    <div class="legend-grid-compact" style="display: flex; flex-wrap: wrap; justify-content: center; gap: 8px; margin-bottom: 20px;">
                        
                        <div class="legend-compact-item" style="width: calc(33.33% - 6px); display: flex; flex-direction: column; align-items: center; padding: 10px 6px; background: #f8fafc; border: 1.5px solid #e2e8f0; border-radius: 8px; min-height: 102px; justify-content: center;">
                            <span style="display: flex; background: #f1f5f9; border: 2px solid #cbd5e1; align-items: center; justify-content: center; width: 54px; height: 54px; border-radius: 50%; box-shadow: 0 2px 4px rgba(0,0,0,0.04); flex-shrink: 0;">
                                <img src="{{ asset('images/Recibido.png') }}" style="width: 28px; height: 28px; object-fit: contain;">
                            </span>
                            <span style="font-size: 0.80rem; color: #475569; font-weight: 700; margin-top: 7px; text-align: center; line-height: 1.1;">Nuevo</span>
                        </div>

                        @if (Auth::user()->perfil != 4)
                        <div class="legend-compact-item" style="width: calc(33.33% - 6px); display: flex; flex-direction: column; align-items: center; padding: 10px 6px; background: #f8fafc; border: 1.5px solid #e2e8f0; border-radius: 8px; min-height: 102px; justify-content: center;">
                            <span style="display: flex; background: #e0e7ff; border: 2px solid #818cf8; align-items: center; justify-content: center; width: 54px; height: 54px; border-radius: 50%; box-shadow: 0 2px 4px rgba(0,0,0,0.04); flex-shrink: 0;">
                                <img src="{{ asset('images/enviando.png') }}" style="width: 28px; height: 28px; object-fit: contain;">
                            </span>
                            <span style="font-size: 0.80rem; color: #4f46e5; font-weight: 700; margin-top: 7px; text-align: center; line-height: 1.1;">Correo Enviado</span>
                        </div>
                        @endif

                        <div class="legend-compact-item" style="width: calc(33.33% - 6px); display: flex; flex-direction: column; align-items: center; padding: 10px 6px; background: #f8fafc; border: 1.5px solid #e2e8f0; border-radius: 8px; min-height: 102px; justify-content: center;">
                            <span style="display: flex; background: #fffbeb; border: 2px solid #f59e0b; align-items: center; justify-content: center; width: 54px; height: 54px; border-radius: 50%; box-shadow: 0 2px 4px rgba(0,0,0,0.04); flex-shrink: 0;">
                                <img src="{{ asset('images/Revisando.png') }}" style="width: 28px; height: 28px; object-fit: contain;">
                            </span>
                            <span style="font-size: 0.80rem; color: #b45309; font-weight: 700; margin-top: 7px; text-align: center; line-height: 1.1;">En Revisión</span>
                        </div>
                    </div>

                    <h3 style="font-size: 0.92rem; color: #0f172a; font-weight: 700; margin: 0 0 10px 0; border-left: 4px solid #3b82f6; padding-left: 8px;">Estados Prioritarios</h3>
                    <div class="legend-grid-compact" style="display: flex; flex-wrap: wrap; justify-content: center; gap: 8px;">
                        
                        <div class="legend-compact-item" style="width: calc(33.33% - 6px); display: flex; flex-direction: column; align-items: center; padding: 10px 6px; background: #f8fafc; border: 1.5px solid #e2e8f0; border-radius: 8px; min-height: 102px; justify-content: center;">
                            <span style="display: flex; background: #eff6ff; border: 2px solid #60a5fa; align-items: center; justify-content: center; width: 54px; height: 54px; border-radius: 50%; box-shadow: 0 2px 4px rgba(0,0,0,0.04); flex-shrink: 0;">
                                <img src="{{ asset('images/pdf-view.png') }}" style="width: 28px; height: 28px; object-fit: contain;">
                            </span>
                            <span style="font-size: 0.80rem; color: #2563eb; font-weight: 700; margin-top: 7px; text-align: center; line-height: 1.1;">Pre-Orden</span>
                        </div>

                        <div class="legend-compact-item" style="width: calc(33.33% - 6px); display: flex; flex-direction: column; align-items: center; padding: 10px 6px; background: #f8fafc; border: 1.5px solid #e2e8f0; border-radius: 8px; min-height: 102px; justify-content: center;">
                            <span style="display: flex; background: #f0f9ff; border: 2px solid #0ea5e9; align-items: center; justify-content: center; width: 54px; height: 54px; border-radius: 50%; box-shadow: 0 2px 4px rgba(0,0,0,0.04); flex-shrink: 0;">
                                <img src="{{ asset('images/Espera.png') }}" style="width: 28px; height: 28px; object-fit: contain;">
                            </span>
                            <span style="font-size: 0.80rem; color: #0369a1; font-weight: 700; margin-top: 7px; text-align: center; line-height: 1.1;">Tengo Modelo</span>
                        </div>

                        <div class="legend-compact-item" style="width: calc(33.33% - 6px); display: flex; flex-direction: column; align-items: center; padding: 10px 6px; background: #f8fafc; border: 1.5px solid #e2e8f0; border-radius: 8px; min-height: 102px; justify-content: center;">
                            <span style="display: flex; background: #ecfdf5; border: 2px solid #10b981; align-items: center; justify-content: center; width: 54px; height: 54px; border-radius: 50%; box-shadow: 0 2px 4px rgba(0,0,0,0.04); flex-shrink: 0;">
                                <img src="{{ asset('images/Quality.png') }}" style="width: 28px; height: 28px; object-fit: contain;">
                            </span>
                            <span style="font-size: 0.80rem; color: #047857; font-weight: 700; margin-top: 7px; text-align: center; line-height: 1.1;">Aprobado</span>
                        </div>

                        <div class="legend-compact-item" style="width: calc(33.33% - 6px); display: flex; flex-direction: column; align-items: center; padding: 10px 6px; background: #f8fafc; border: 1.5px solid #e2e8f0; border-radius: 8px; min-height: 102px; justify-content: center;">
                            <span style="display: flex; background: #fef2f2; border: 2px solid #ef4444; align-items: center; justify-content: center; width: 54px; height: 54px; border-radius: 50%; box-shadow: 0 2px 4px rgba(0,0,0,0.04); flex-shrink: 0;">
                                <img src="{{ asset('images/Quality.png') }}" style="width: 28px; height: 28px; object-fit: contain;">
                            </span>
                            <span style="font-size: 0.80rem; color: #b91c1c; font-weight: 700; margin-top: 7px; text-align: center; line-height: 1.1;">Rechazado</span>
                        </div>

                        <div class="legend-compact-item" style="width: calc(33.33% - 6px); display: flex; flex-direction: column; align-items: center; padding: 10px 6px; background: #f8fafc; border: 1.5px solid #e2e8f0; border-radius: 8px; min-height: 102px; justify-content: center;">
                            <span style="display: flex; background: #fef9c3; border: 2px solid #eab308; align-items: center; justify-content: center; width: 54px; height: 54px; border-radius: 50%; box-shadow: 0 2px 4px rgba(0,0,0,0.04); flex-shrink: 0;">
                                <img src="{{ asset('images/Quality.png') }}" style="width: 28px; height: 28px; object-fit: contain;">
                            </span>
                            <span style="font-size: 0.80rem; color: #854d0e; font-weight: 700; margin-top: 7px; text-align: center; line-height: 1.1;">Mixto</span>
                        </div>

                        <div class="legend-compact-item" style="width: calc(33.33% - 6px); display: flex; flex-direction: column; align-items: center; padding: 10px 6px; background: #f8fafc; border: 1.5px solid #e2e8f0; border-radius: 8px; min-height: 102px; justify-content: center;">
                            <span style="display: flex; background: #f0fdf4; border: 2px solid #059669; align-items: center; justify-content: center; width: 54px; height: 54px; border-radius: 50%; box-shadow: 0 2px 4px rgba(0,0,0,0.04); flex-shrink: 0;">
                                <img src="{{ asset('images/pdf-view.png') }}" style="width: 28px; height: 28px; object-fit: contain;">
                            </span>
                            <span style="font-size: 0.80rem; color: #15803d; font-weight: 700; margin-top: 7px; text-align: center; line-height: 1.1;">Casting</span>
                        </div>

                        <div class="legend-compact-item" style="width: calc(33.33% - 6px); display: flex; flex-direction: column; align-items: center; padding: 10px 6px; background: #f8fafc; border: 1.5px solid #e2e8f0; border-radius: 8px; min-height: 102px; justify-content: center;">
                            <span style="display: flex; background: #fdf2f8; border: 2px solid #ec4899; align-items: center; justify-content: center; width: 54px; height: 54px; border-radius: 50%; box-shadow: 0 2px 4px rgba(0,0,0,0.04); flex-shrink: 0;">
                                <img src="{{ asset('images/Reproceso.png') }}" style="width: 28px; height: 28px; object-fit: contain;">
                            </span>
                            <span style="font-size: 0.80rem; color: #be185d; font-weight: 700; margin-top: 7px; text-align: center; line-height: 1.1;">Reproceso</span>
                        </div>

                        <div class="legend-compact-item" style="width: calc(33.33% - 6px); display: flex; flex-direction: column; align-items: center; padding: 10px 6px; background: #f8fafc; border: 1.5px solid #e2e8f0; border-radius: 8px; min-height: 102px; justify-content: center;">
                            <span style="display: flex; background: #f3e8ff; border: 2px solid #9333ea; align-items: center; justify-content: center; width: 54px; height: 54px; border-radius: 50%; box-shadow: 0 2px 4px rgba(0,0,0,0.04); flex-shrink: 0;">
                                <img src="{{ asset('images/Proveedor.png') }}" style="width: 28px; height: 28px; object-fit: contain;">
                            </span>
                            <span style="font-size: 0.80rem; color: #9333ea; font-weight: 700; margin-top: 7px; text-align: center; line-height: 1.1;">Enviado a Proveedor</span>
                        </div>

                        <div class="legend-compact-item" style="width: calc(33.33% - 6px); display: flex; flex-direction: column; align-items: center; padding: 10px 6px; background: #f8fafc; border: 1.5px solid #e2e8f0; border-radius: 8px; min-height: 102px; justify-content: center;">
                            <span style="display: flex; background: #ecfdf5; border: 2px solid #10b981; align-items: center; justify-content: center; width: 54px; height: 54px; border-radius: 50%; box-shadow: 0 2px 4px rgba(0,0,0,0.04); flex-shrink: 0;">
                                <img src="{{ asset('images/Aprobado.png') }}" style="width: 28px; height: 28px; object-fit: contain;">
                            </span>
                            <span style="font-size: 0.80rem; color: #047857; font-weight: 700; margin-top: 7px; text-align: center; line-height: 1.1;">Aprobado Final</span>
                        </div>

                        <div class="legend-compact-item" style="width: calc(33.33% - 6px); display: flex; flex-direction: column; align-items: center; padding: 10px 6px; background: #f8fafc; border: 1.5px solid #e2e8f0; border-radius: 8px; min-height: 102px; justify-content: center;">
                            <span style="display: flex; background: #fef2f2; border: 2px solid #dc2626; align-items: center; justify-content: center; width: 54px; height: 54px; border-radius: 50%; box-shadow: 0 2px 4px rgba(0,0,0,0.04); flex-shrink: 0;">
                                <img src="{{ asset('images/Rechazado.png') }}" style="width: 28px; height: 28px; object-fit: contain;">
                            </span>
                            <span style="font-size: 0.80rem; color: #b91c1c; font-weight: 700; margin-top: 7px; text-align: center; line-height: 1.1;">Rechazado Final</span>
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
                <div id="legend-zoom-tooltip" style="position: fixed; display: none; pointer-events: none; z-index: 99999; background: rgba(255, 255, 255, 0.98); backdrop-filter: blur(10px); border-radius: 12px; box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15); border: 3px solid #cbd5e1; padding: 16px; width: 170px; height: 180px; flex-direction: column; align-items: center; justify-content: center; box-sizing: border-box; transition: transform 0.15s cubic-bezier(0.175, 0.885, 0.32, 1.25), opacity 0.15s ease; opacity: 0; transform: scale(0.9); font-family: 'Poppins', sans-serif;">
                    <span id="legend-zoom-circle" style="display: flex; align-items: center; justify-content: center; width: 90px; height: 90px; border-radius: 50%; box-shadow: 0 4px 8px rgba(0,0,0,0.06); flex-shrink: 0; border: 3px solid transparent;">
                        <img id="legend-zoom-img" src="" style="width: 55px; height: 55px; object-fit: contain;">
                    </span>
                    <span id="legend-zoom-label" style="font-size: 1.08rem; font-weight: 800; margin-top: 10px; text-align: center; line-height: 1.2;"></span>
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
                            <img src="{{ asset('images/pdf-view.png') }}" alt="Total" style="width: 60px;">
                        </div>
                        <div>
                            <div class="alm-stat-value">{{ $total }}</div>
                            <div class="alm-stat-label">OTs en historial</div>
                        </div>
                    </div>
                    <div class="alm-stat-card stat-activas">
                        <div class="alm-stat-icon">
                            <img src="{{ asset('images/ready.png') }}" alt="Activas" style="width: 60px;">
                        </div>
                        <div>
                            <div class="alm-stat-value">{{ $activas }}</div>
                            <div class="alm-stat-label">OTs activas</div>
                        </div>
                    </div>
                    <div class="alm-stat-card stat-inactivas">
                        <div class="alm-stat-icon">
                            <img src="{{ asset('images/Eliminar-Carpeta.png') }}" alt="Archivadas" style="width: 60px;">
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

                @foreach (['activa' => 'Dibujos Activos', 'inactiva' => 'Dibujos Inactivos (Histórico)'] as $estado => $titulo)
                    @php
                        $registrosEstado = $registros->where('status', $estado);
                    @endphp

                    <div class="alm-table-card" style="margin-bottom: 2em;">
                        <div class="alm-table-header"
                            style="{{ $estado === 'inactiva' ? 'background: #6c757d; border-bottom: 2px solid #5a6268;' : '' }}">
                            <h2>{{ $titulo }}</h2>
                            <span class="alm-results-count">{{ $registrosEstado->count() }}
                                resultado{{ $registrosEstado->count() !== 1 ? 's' : '' }}</span>
                        </div>

                        @if ($registrosEstado->isEmpty())
                            <div class="alm-empty">
                                <div class="alm-empty-icon">
                                    <img src="{{ asset('images/noPieces.png') }}" alt="Sin resultados"
                                        style="width: 64px; opacity: 0.5;">
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
                                                $liberacionesReg = \App\Models\LiberacionModeloFundicion::where('ot', $reg->ot)
                                                    ->where('estado', '!=', 'pendiente')
                                                    ->get();
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

                                                // Obtener clases activas para filtrar archivos del historial
                                                $activeClassesForOt = [];
                                                $confSource = $targetReg->ayudas_config ?? ($reg->ayudas_config ?? null);
                                                if (!empty($confSource)) {
                                                    $configs = is_string($confSource) ? json_decode($confSource, true) : $confSource;
                                                    if (is_array($configs)) {
                                                        foreach ($configs as $val) {
                                                            $val = strtolower($val);
                                                            if (str_contains($val, 'opcional')) continue;
                                                            foreach (['fondo', 'obturador', 'bombillo', 'molde'] as $kc) {
                                                                if (strpos($val, $kc) !== false) {
                                                                    $activeClassesForOt[] = $kc;
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
                                                                    foreach (['fondo', 'obturador', 'bombillo', 'molde'] as $kc) {
                                                                        if (strpos($val, $kc) !== false) {
                                                                            $activeClassesForOt[] = $kc;
                                                                        }
                                                                    }
                                                                }
                                                            }
                                                        }
                                                    }
                                                }
                                                // Filtrar clases activas basándose en las decisiones de Calidad
                                                $isReproceso = preg_match('/_R\d+$/i', $reg->ot);
                                                if ($isReproceso) {
                                                    $prevOt = preg_replace_callback('/_R(\d+)$/i', function($m) {
                                                        $num = intval($m[1]) - 1;
                                                        return $num > 0 ? '_R' . $num : '';
                                                    }, $reg->ot);

                                                    $rechazados = \App\Models\LiberacionModeloFundicion::where('ot', '=', $prevOt)
                                                        ->where('decision', '=', 'rechazar')
                                                        ->pluck('tipo_modelo')
                                                        ->toArray();

                                                    $validClasses = [];
                                                    foreach ($rechazados as $r) {
                                                        $clases = array_map('trim', explode(',', strtolower($r)));
                                                        foreach ($clases as $c) {
                                                            $validClasses[] = $c;
                                                        }
                                                    }
                                                    if (!empty($validClasses)) {
                                                        $activeClassesForOt = $validClasses;
                                                    }
                                                } else {
                                                    $hasLiberaciones = \App\Models\LiberacionModeloFundicion::where('ot', '=', $reg->ot)->exists();
                                                    if ($hasLiberaciones) {
                                                        $aprobados = \App\Models\LiberacionModeloFundicion::where('ot', '=', $reg->ot)
                                                            ->where('decision', '=', 'aprobar')
                                                            ->pluck('tipo_modelo')
                                                            ->toArray();

                                                        $validClasses = [];
                                                        foreach ($aprobados as $a) {
                                                            $clases = array_map('trim', explode(',', strtolower($a)));
                                                            foreach ($clases as $c) {
                                                                $validClasses[] = $c;
                                                            }
                                                        }
                                                        if (!empty($validClasses)) {
                                                            $activeClassesForOt = $validClasses;
                                                        } else {
                                                            $activeClassesForOt = [];
                                                        }
                                                    }
                                                }

                                                if (empty($activeClassesForOt)) {
                                                    $activeClassesForOt = ['fondo', 'bombillo', 'molde', 'obturador'];
                                                }
                                                $activeClassesForOt = array_values(array_unique($activeClassesForOt));

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
                                                        $knownClasses = ['fondo', 'obturador', 'bombillo', 'molde'];
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
                                                            if ($relRec->ot !== $reg->ot) {
                                                                continue;
                                                            }
                                                        }
                                                        if (!in_array($base, $dibujoBaseNames)) {
                                                            $archivos[] = [
                                                                'nombre' => $archivo,
                                                                'ot' => $relRec->ot,
                                                            ];
                                                            $dibujoBaseNames[] = $base;
                                                        }
                                                    }
                                                }
                                                $countDibujos = count($archivos);

                                                $ayudasArchivos = [];
                                                $otrosArchivos = [];
                                                $baseNames = [];

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
                                                    foreach (['Bombillo', 'Fondo', 'Obturador', 'Molde'] as $claseDir) {
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
                                                                $knownClasses = ['fondo', 'obturador', 'bombillo', 'molde'];
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
                                                                    if ($otName !== $reg->ot) {
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
                                                            $knownClasses = ['fondo', 'obturador', 'bombillo', 'molde'];
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
                                                                if ($otName !== $reg->ot) {
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
                                                                $knownClasses = ['fondo', 'obturador', 'bombillo', 'molde'];
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
                                                                    if ($otName !== $reg->ot) {
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
                                                            $knownClasses = ['fondo', 'obturador', 'bombillo', 'molde'];
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
                                                                if ($otName !== $reg->ot) {
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
                                                            $knownClasses = ['fondo', 'obturador', 'bombillo', 'molde'];
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
                                                                if ($otName !== $reg->ot) {
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
                                                        $fileHistory = $relatedRecords->firstWhere('ot', $archivo['ot']);
                                                        $status = $fileHistory ? $fileHistory->calidad_revision_status : null;
                                                        $calidadAlertaEnviada = (
                                                            in_array($status, ['calidad_aprobado', 'calidad_rechazado', 'calidad_mixto', 'casting_aprobado']) ||
                                                            \App\Models\ScarModelo::where('ot', '=', $archivo['ot'])->where('estatus', '=', 'alertado')->exists()
                                                        );
                                                        if (!$calidadAlertaEnviada) {
                                                            continue; // Ocultar para todos los perfiles, incluidos Admin y Supervisor
                                                        }
                                                    }

                                                    if ($userPerfil != 1 && $userPerfil != 2) {
                                                        if ($userPerfil == 4) { // Calidad
                                                            // Calidad solo ve preordenes si pre_orden_email_sent es true
                                                            if ($isPreorden) {
                                                                $fileHistory = $relatedRecords->firstWhere('ot', $archivo['ot']);
                                                                if (!$fileHistory || !$fileHistory->pre_orden_email_sent) {
                                                                    continue;
                                                                }
                                                            }
                                                        } elseif ($userPerfil == 5) { // Almacén
                                                            // Almacén ve preordenes y confirmaciones (calidad ya se filtro arriba)
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
                                                $count = $countDibujos + $countAyudas + $countOtros;

                                                $isFinalized = in_array($targetReg->calidad_revision_status, ['calidad_aprobado', 'calidad_rechazado', 'calidad_mixto', 'casting_aprobado']);
                                                $showControlCard = (Auth::user()->perfil != 4 && $estado === 'activa' && !$isFinalized);
                                                $hasFilesOrControl = ($count > 0 || $showControlCard);

                                                // ── CALCULAR APROBADOS Y RECHAZADOS DEL ÚLTIMO VEREDICTO DE CADA CLASE ──
                                                $liberacionesAll = \App\Models\LiberacionModeloFundicion::whereIn('ot', $allRelatedOtNames)->get();
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

                                                // Filtrar por clases activas en esta versión de la OT (desde la pre-orden de modelo)
                                                // (Ya calculado al inicio en activeClassesForOt)

                                                $aprobados = array_filter($aprobadosRaw, function ($clase) use ($activeClassesForOt) {
                                                    return in_array(strtolower($clase), $activeClassesForOt);
                                                });
                                                $rechazados = array_filter($rechazadosRaw, function ($clase) use ($activeClassesForOt) {
                                                    return in_array(strtolower($clase), $activeClassesForOt);
                                                });
                                            @endphp

                                            {{-- Fila principal --}}
                                            <tr data-ot="{{ $reg->ot }}">
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
                                                        @php
                                                            $libStatus = $targetReg->calidad_revision_status ?? null;

                                                            if ($libStatus === 'casting_aprobado') {
                                                                $icon = 'Proveedor.png';
                                                                $label = 'Enviado a Proveedor';
                                                                $tooltip = 'Pre-orden de casting enviada al proveedor, proceso finalizado';
                                                                $borderColor = '#9333ea';
                                                                $bgColor = '#f3e8ff';
                                                                $textColor = '#9333ea';
                                                            } elseif ($targetReg->casting_pdf_generated) {
                                                                $icon = 'pdf-view.png';
                                                                $label = 'Casting';
                                                                $tooltip = 'Pre-orden de casting generada, esperando envío';
                                                                $borderColor = '#059669';
                                                                $bgColor = '#f0fdf4';
                                                                $textColor = '#15803d';
                                                            } elseif ($libStatus === 'calidad_aprobado') {
                                                                $icon = 'Quality.png';
                                                                $label = 'Aprobado';
                                                                $tooltip = 'Modelo aprobado y liberado por Calidad';
                                                                $borderColor = '#10b981';
                                                                $bgColor = '#ecfdf5';
                                                                $textColor = '#047857';
                                                            } elseif ($libStatus === 'calidad_rechazado') {
                                                                $icon = 'Quality.png';
                                                                $label = 'Rechazado';
                                                                $tooltip = 'Modelo rechazado por Calidad debido a desviaciones';
                                                                $borderColor = '#ef4444';
                                                                $bgColor = '#fef2f2';
                                                                $textColor = '#b91c1c';
                                                            } elseif ($libStatus === 'calidad_mixto') {
                                                                $icon = 'Quality.png';
                                                                $label = 'Mixto';
                                                                $tooltip = 'Liberación mixta por Calidad (clases aprobadas y rechazadas)';
                                                                $borderColor = '#eab308';
                                                                $bgColor = '#fef9c3';
                                                                $textColor = '#854d0e';
                                                            } elseif (in_array($libStatus, ['pendiente', 'aprobado', 'rechazado', 'mixto'])) {
                                                                $icon = 'Revisando.png';
                                                                $label = 'En Revisión';
                                                                $tooltip = 'Calidad está realizando la revisión del modelo';
                                                                $borderColor = '#f59e0b';
                                                                $bgColor = '#fffbeb';
                                                                $textColor = '#b45309';
                                                            } elseif ($targetReg->pre_orden_email_sent) {
                                                                if (Auth::user()->perfil == 4) {
                                                                    $icon = 'Recibido.png';
                                                                    $label = 'Nuevo';
                                                                    $tooltip = 'Pre-orden de fabricación de modelo recibida, esperando revisión de Calidad';
                                                                    $borderColor = '#cbd5e1';
                                                                    $bgColor = '#f1f5f9';
                                                                    $textColor = '#64748b';
                                                                } else {
                                                                    $icon = 'enviando.png';
                                                                    $label = 'Correo Enviado';
                                                                    $tooltip = 'Pre-orden enviada por correo electrónico, esperando revisión de Calidad';
                                                                    $borderColor = '#818cf8';
                                                                    $bgColor = '#e0e7ff';
                                                                    $textColor = '#4f46e5';
                                                                }
                                                            } elseif ($targetReg->pre_orden_sent) {
                                                                $icon = 'pdf-view.png';
                                                                $label = 'Pre-Orden';
                                                                $tooltip = 'Pre-orden de modelo generada y guardada, pendiente de enviar';
                                                                $borderColor = '#60a5fa';
                                                                $bgColor = '#eff6ff';
                                                                $textColor = '#2563eb';
                                                            } elseif ($targetReg->tiene_modelo) {
                                                                $icon = 'Espera.png';
                                                                $label = 'Tengo Modelo';
                                                                $tooltip = 'Modelo físico disponible en Almacén, en espera de revisión por Calidad';
                                                                $borderColor = '#0ea5e9';
                                                                $bgColor = '#f0f9ff';
                                                                $textColor = '#0369a1';
                                                            } elseif ($reg->rechazos_procesados) {
                                                                if (count($aprobados) > 0) {
                                                                    $icon = 'Quality.png';
                                                                    $label = 'Aprobado';
                                                                    $tooltip = 'Clases aprobadas se conservan en este registro';
                                                                    $borderColor = '#10b981';
                                                                    $bgColor = '#ecfdf5';
                                                                    $textColor = '#047857';
                                                                } else {
                                                                    $icon = 'Rechazado.png';
                                                                    $label = 'Rechazado';
                                                                    $tooltip = 'Retornado hacia un nuevo ciclo de modelo (Reproceso)';
                                                                    $borderColor = '#dc2626';
                                                                    $bgColor = '#fef2f2';
                                                                    $textColor = '#b91c1c';
                                                                }
                                                            } elseif ($isReproceso && in_array($libStatus, [null, 'pendiente']) && !$targetReg->tiene_modelo && !$targetReg->pre_orden_sent && !$targetReg->pre_orden_email_sent) {
                                                                $icon = 'Rechazado.png';
                                                                $label = 'Rechazado';
                                                                $tooltip = 'Reproceso por rechazo de Calidad';
                                                                $borderColor = '#dc2626';
                                                                $bgColor = '#fef2f2';
                                                                $textColor = '#b91c1c';
                                                            } else {
                                                                $icon = 'Recibido.png';
                                                                $label = 'Nuevo';
                                                                $tooltip = 'Alerta inicial recibida, pendiente de procesar modelo por Almacén';
                                                                $borderColor = '#cbd5e1';
                                                                $bgColor = '#f1f5f9';
                                                                $textColor = '#64748b';
                                                            }
                                                        @endphp
                                                        <div class="status-modelo-container"
                                                            style="display: inline-flex; flex-direction: column; align-items: center; gap: 2px; padding: 6px; border-radius: 8px;">
                                                            <span class="badge-modelo-icon" title="{{ $tooltip }}"
                                                                style="display: flex; align-items: center; justify-content: center; width: 52px; height: 52px; border-radius: 50%; background: {{ $bgColor }}; box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1); border: 2px solid {{ $borderColor }}; transition: all 0.2s ease;">
                                                                <img src="{{ asset('images/' . $icon) }}" alt="{{ $label }}"
                                                                    style="width: 34px; height: 34px; object-fit: contain;">
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
                                                        <span class="d-text-subtle" style="font-size:0.85em;">Sin archivos</span>
                                                    @endif
                                                </td>
                                            </tr>

                                            {{-- Fila desplegable de archivos --}}
                                            @if ($hasFilesOrControl)
                                                <tr class="alm-files-row" id="files-{{ $estado }}-{{ $loop->index }}">
                                                    <td colspan="6">

                                                        @if ($countDibujos > 0 && $reg->alert_sent_at)
                                                            <h3
                                                                style="margin-top: 15px; margin-bottom: 10px; color: #005194; border-bottom: 2px solid #005194; padding-bottom: 5px;">
                                                                Dibujos de Fundición</h3>
                                                            <div class="alm-pdf-grid">
                                                                @foreach ($archivos as $archivoInfo)
                                                                    <div class="dibujos-file-card"
                                                                        style="animation-delay: {{ $loop->index * 0.05 }}s;">
                                                                        <div class="file-icon-wrapper"
                                                                            onclick="almacenVerPdf('{{ $archivoInfo['ot'] }}', '{{ $archivoInfo['nombre'] }}', 'dibujo')"
                                                                            style="cursor: pointer;" title="Abrir PDF">
                                                                            <img src="{{ asset('images/pdf-view-shadow.png') }}"
                                                                                class="file-icon icon-default">
                                                                            <img src="{{ asset('images/pdf-view.png') }}"
                                                                                class="file-icon icon-hover">
                                                                        </div>
                                                                        <div class="file-name" style="cursor: pointer;" title="Abrir PDF"
                                                                            onclick="almacenVerPdf('{{ $archivoInfo['ot'] }}', '{{ $archivoInfo['nombre'] }}', 'dibujo')">
                                                                            {{ basename($archivoInfo['nombre']) }}
                                                                        </div>
                                                                        <div class="file-actions">
                                                                            <button class="btn-dibujos btn-dibujos-sm btn-ver"
                                                                                onclick="almacenVerPdf('{{ $archivoInfo['ot'] }}', '{{ $archivoInfo['nombre'] }}', 'dibujo')">Ver</button>
                                                                        </div>
                                                                    </div>
                                                                @endforeach
                                                            </div>
                                                        @elseif ($countDibujos > 0 && !$reg->alert_sent_at)
                                                            {{-- Dibujos existentes pero alerta aún no enviada desde Ingeniería --}}
                                                            <div
                                                                style="margin-top: 15px; padding: 14px 18px; background: rgba(0,81,148,0.06); border: 1.5px dashed #005194; border-radius: 10px; color: #005194; font-size: 0.93em;">
                                                                <strong>Dibujos pendientes:</strong> Los dibujos estarán disponibles una vez que
                                                                Ingeniería envíe la alerta oficial desde el sistema de gestión documental.
                                                            </div>
                                                        @endif

                                                        @if ($countAyudas > 0)
                                                            <h3
                                                                style="margin-top: 25px; margin-bottom: 10px; color: #9c0300; border-bottom: 2px solid #9c0300; padding-bottom: 5px;">
                                                                Ayudas Visuales de Fundición</h3>
                                                            <div class="alm-pdf-grid">
                                                                @foreach ($ayudasArchivos as $ayudaArchivo)
                                                                    <div class="dibujos-file-card card-ayuda"
                                                                        style="animation-delay: {{ $loop->index * 0.05 }}s;">
                                                                        <div class="file-icon-wrapper"
                                                                            onclick="almacenVerPdf('{{ $ayudaArchivo['ot'] }}', '{{ $ayudaArchivo['nombre'] }}', '{{ $ayudaArchivo['tipo'] }}')"
                                                                            style="cursor: pointer;" title="Abrir PDF">
                                                                            <img src="{{ asset('images/pdf-view-shadow.png') }}"
                                                                                class="file-icon icon-default">
                                                                            <img src="{{ asset('images/pdf-view.png') }}"
                                                                                class="file-icon icon-hover">
                                                                        </div>
                                                                        <div class="file-name" style="cursor: pointer;" title="Abrir PDF"
                                                                            onclick="almacenVerPdf('{{ $ayudaArchivo['ot'] }}', '{{ $ayudaArchivo['nombre'] }}', '{{ $ayudaArchivo['tipo'] }}')">
                                                                            {{ basename($ayudaArchivo['nombre']) }}
                                                                        </div>
                                                                        <div class="file-actions">
                                                                            <button class="btn-dibujos btn-dibujos-sm btn-ver btn-ayuda-color"
                                                                                onclick="almacenVerPdf('{{ $ayudaArchivo['ot'] }}', '{{ $ayudaArchivo['nombre'] }}', '{{ $ayudaArchivo['tipo'] }}')">Ver</button>
                                                                        </div>
                                                                    </div>
                                                                @endforeach
                                                            </div>
                                                        @elseif(!empty($reg->ayudas_config))
                                                            <div
                                                                style="margin-top: 20px; padding: 15px; background: #fff5f5; border: 1px solid #feb2b2; border-radius: 8px; color: #9c0300;">
                                                                <strong>Aviso:</strong> Se han vinculado
                                                                {{ count($reg->ayudas_config) }} clases de ayudas visuales, pero
                                                                los archivos aún no se han sincronizado con {{ $deptName }}. Por favor,
                                                                <strong>Vuelve a Vincular</strong> las ayudas desde la vista de
                                                                administración.
                                                            </div>
                                                        @endif

                                                        {{-- BLOQUE 4: Renombrar sección a "Documentos Aprobados" --}}
                                                        @if ($countAprobados > 0)
                                                            <h3
                                                                style="margin-top: 25px; margin-bottom: 10px; color: #155724; border-bottom: 2px solid #155724; padding-bottom: 5px;">
                                                                Documentos Aprobados</h3>
                                                            <div class="alm-pdf-grid">
                                                                @foreach ($archivosAprobados as $otroArchivo)
                                                                    @php
                                                                        $canDelete = false;
                                                                        $fileOwner = $otroArchivo['owner'] ?? '';
                                                                        $userPerfil = Auth::user()->perfil;

                                                                        $alertSent = false;
                                                                        if ($fileOwner === 'almacen') {
                                                                            $alertSent = (bool)($targetReg->pre_orden_email_sent || $targetReg->pre_orden_sent);
                                                                        } elseif ($fileOwner === 'calidad') {
                                                                            $alertSent = in_array($targetReg->calidad_revision_status, ['calidad_aprobado', 'calidad_rechazado', 'calidad_mixto', 'casting_aprobado']);
                                                                        }

                                                                        if (!$alertSent) {
                                                                            if ($userPerfil == 1 || $userPerfil == 2) {
                                                                                $canDelete = true;
                                                                            } elseif ($userPerfil == 5 && $fileOwner === 'almacen') {
                                                                                $canDelete = true;
                                                                            } elseif ($userPerfil == 4 && $fileOwner === 'calidad') {
                                                                                $canDelete = true;
                                                                            }
                                                                        }
                                                                    @endphp
                                                                    @if ($otroArchivo['tipo'] === 'imagen')
                                                                        {{-- Tarjeta para imágenes (fotos de evidencia) --}}
                                                                        <div class="dibujos-file-card card-otro card-imagen"
                                                                            style="animation-delay: {{ $loop->index * 0.05 }}s; border-left-color: #0369a1;">
                                                                            <div class="file-icon-wrapper"
                                                                                onclick="almacenVerPdf('{{ $otroArchivo['ot'] }}', '{{ $otroArchivo['nombre'] }}', 'otro')"
                                                                                style="cursor: pointer;" title="Ver imagen">
                                                                                <img src="{{ $otroArchivo['url'] }}" class="file-icon-img-thumb"
                                                                                    alt="{{ basename($otroArchivo['nombre']) }}"
                                                                                    style="width:100%; height:80px; object-fit:cover; border-radius:6px; border:1px solid #bae6fd;">
                                                                            </div>
                                                                            <div class="file-name" style="cursor: pointer;" title="Ver imagen"
                                                                                onclick="almacenVerPdf('{{ $otroArchivo['ot'] }}', '{{ $otroArchivo['nombre'] }}', 'otro')">
                                                                                {{ basename($otroArchivo['nombre']) }}
                                                                            </div>
                                                                            <div class="file-actions" style="display: flex; gap: 5px;">
                                                                                <button class="btn-dibujos btn-dibujos-sm btn-ver"
                                                                                    style="background-color: #0369a1; color: white;"
                                                                                    onclick="almacenVerPdf('{{ $otroArchivo['ot'] }}', '{{ $otroArchivo['nombre'] }}', 'otro')">Ver</button>
                                                                                @if ($canDelete)
                                                                                <button class="btn-dibujos btn-dibujos-sm btn-eliminar"
                                                                                    style="background-color: #dc3545; color: white;"
                                                                                    onclick="almacenEliminarOtroArchivo('{{ $otroArchivo['ot'] }}', '{{ $otroArchivo['nombre'] }}', '{{ $otroArchivo['tipo'] }}', this, '{{ $otroArchivo['origin'] ?? '' }}')">Eliminar</button>
                                                                                @endif
                                                                            </div>
                                                                        </div>
                                                                    @else
                                                                        {{-- Tarjeta para PDFs y otros documentos --}}
                                                                        <div class="dibujos-file-card card-otro"
                                                                            style="animation-delay: {{ $loop->index * 0.05 }}s; border-left-color: #155724;">
                                                                            <div class="file-icon-wrapper"
                                                                                onclick="almacenVerPdf('{{ $otroArchivo['ot'] }}', '{{ $otroArchivo['nombre'] }}', '{{ $otroArchivo['tipo'] }}')"
                                                                                style="cursor: pointer;" title="Abrir PDF">
                                                                                <img src="{{ asset('images/pdf-view-shadow.png') }}"
                                                                                    class="file-icon icon-default">
                                                                                <img src="{{ asset('images/pdf-view.png') }}"
                                                                                    class="file-icon icon-hover">
                                                                            </div>
                                                                            <div class="file-name" style="cursor: pointer;" title="Abrir PDF"
                                                                                onclick="almacenVerPdf('{{ $otroArchivo['ot'] }}', '{{ $otroArchivo['nombre'] }}', '{{ $otroArchivo['tipo'] }}')">
                                                                                {{ basename($otroArchivo['nombre']) }}
                                                                            </div>
                                                                            <div class="file-actions" style="display: flex; gap: 5px;">
                                                                                <button class="btn-dibujos btn-dibujos-sm btn-ver"
                                                                                    style="background-color: #155724; color: white;"
                                                                                    onclick="almacenVerPdf('{{ $otroArchivo['ot'] }}', '{{ $otroArchivo['nombre'] }}', '{{ $otroArchivo['tipo'] }}')">Ver</button>
                                                                                @if ($canDelete)
                                                                                <button class="btn-dibujos btn-dibujos-sm btn-eliminar"
                                                                                    style="background-color: #dc3545; color: white;"
                                                                                    onclick="almacenEliminarOtroArchivo('{{ $otroArchivo['ot'] }}', '{{ $otroArchivo['nombre'] }}', '{{ $otroArchivo['tipo'] }}', this, '{{ $otroArchivo['origin'] ?? '' }}')">Eliminar</button>
                                                                                @endif
                                                                            </div>
                                                                        </div>
                                                                    @endif
                                                                @endforeach
                                                            </div>
                                                        @endif

                                                        {{-- BLOQUE 5: Sección de "Documentos Rechazados" --}}
                                                        @if ($countRechazados > 0)
                                                            <h3
                                                                style="margin-top: 25px; margin-bottom: 10px; color: #9c0300; border-bottom: 2px solid #9c0300; padding-bottom: 5px;">
                                                                Documentos Rechazados</h3>
                                                            <div class="alm-pdf-grid">
                                                                @foreach ($archivosRechazados as $otroArchivo)
                                                                    @php
                                                                        $canDelete = false;
                                                                        $fileOwner = $otroArchivo['owner'] ?? '';
                                                                        $userPerfil = Auth::user()->perfil;

                                                                        $alertSent = false;
                                                                        if ($fileOwner === 'almacen') {
                                                                            $alertSent = (bool)($targetReg->pre_orden_email_sent || $targetReg->pre_orden_sent);
                                                                        } elseif ($fileOwner === 'calidad') {
                                                                            $alertSent = in_array($targetReg->calidad_revision_status, ['calidad_aprobado', 'calidad_rechazado', 'calidad_mixto', 'casting_aprobado']);
                                                                        }

                                                                        if (!$alertSent) {
                                                                            if ($userPerfil == 1 || $userPerfil == 2) {
                                                                                $canDelete = true;
                                                                            } elseif ($userPerfil == 5 && $fileOwner === 'almacen') {
                                                                                $canDelete = true;
                                                                            } elseif ($userPerfil == 4 && $fileOwner === 'calidad') {
                                                                                $canDelete = true;
                                                                            }
                                                                        }
                                                                    @endphp
                                                                    @if ($otroArchivo['tipo'] === 'imagen')
                                                                        {{-- Tarjeta para imágenes (fotos de evidencia) --}}
                                                                        <div class="dibujos-file-card card-otro card-imagen"
                                                                            style="animation-delay: {{ $loop->index * 0.05 }}s; border-left-color: #0369a1;">
                                                                            <div class="file-icon-wrapper"
                                                                                onclick="almacenVerPdf('{{ $otroArchivo['ot'] }}', '{{ $otroArchivo['nombre'] }}', 'otro')"
                                                                                style="cursor: pointer;" title="Ver imagen">
                                                                                <img src="{{ $otroArchivo['url'] }}" class="file-icon-img-thumb"
                                                                                    alt="{{ basename($otroArchivo['nombre']) }}"
                                                                                    style="width:100%; height:80px; object-fit:cover; border-radius:6px; border:1px solid #bae6fd;">
                                                                            </div>
                                                                            <div class="file-name" style="cursor: pointer;" title="Ver imagen"
                                                                                onclick="almacenVerPdf('{{ $otroArchivo['ot'] }}', '{{ $otroArchivo['nombre'] }}', 'otro')">
                                                                                {{ basename($otroArchivo['nombre']) }}
                                                                            </div>
                                                                            <div class="file-actions" style="display: flex; gap: 5px;">
                                                                                <button class="btn-dibujos btn-dibujos-sm btn-ver"
                                                                                    style="background-color: #0369a1; color: white;"
                                                                                    onclick="almacenVerPdf('{{ $otroArchivo['ot'] }}', '{{ $otroArchivo['nombre'] }}', 'otro')">Ver</button>
                                                                                @if ($canDelete)
                                                                                <button class="btn-dibujos btn-dibujos-sm btn-eliminar"
                                                                                    style="background-color: #dc3545; color: white;"
                                                                                    onclick="almacenEliminarOtroArchivo('{{ $otroArchivo['ot'] }}', '{{ $otroArchivo['nombre'] }}', '{{ $otroArchivo['tipo'] }}', this, '{{ $otroArchivo['origin'] ?? '' }}')">Eliminar</button>
                                                                                @endif
                                                                            </div>
                                                                        </div>
                                                                    @else
                                                                        {{-- Tarjeta para PDFs y otros documentos --}}
                                                                        <div class="dibujos-file-card card-otro"
                                                                            style="animation-delay: {{ $loop->index * 0.05 }}s; border-left-color: #9c0300;">
                                                                            <div class="file-icon-wrapper"
                                                                                onclick="almacenVerPdf('{{ $otroArchivo['ot'] }}', '{{ $otroArchivo['nombre'] }}', '{{ $otroArchivo['tipo'] }}')"
                                                                                style="cursor: pointer;" title="Abrir PDF">
                                                                                <img src="{{ asset('images/pdf-view-shadow.png') }}"
                                                                                    class="file-icon icon-default">
                                                                                <img src="{{ asset('images/pdf-view.png') }}"
                                                                                    class="file-icon icon-hover">
                                                                            </div>
                                                                            <div class="file-name" style="cursor: pointer;" title="Abrir PDF"
                                                                                onclick="almacenVerPdf('{{ $otroArchivo['ot'] }}', '{{ $otroArchivo['nombre'] }}', '{{ $otroArchivo['tipo'] }}')">
                                                                                {{ basename($otroArchivo['nombre']) }}
                                                                            </div>
                                                                            <div class="file-actions" style="display: flex; gap: 5px;">
                                                                                <button class="btn-dibujos btn-dibujos-sm btn-ver"
                                                                                    style="background-color: #9c0300; color: white;"
                                                                                    onclick="almacenVerPdf('{{ $otroArchivo['ot'] }}', '{{ $otroArchivo['nombre'] }}', '{{ $otroArchivo['tipo'] }}')">Ver</button>
                                                                                @if ($canDelete)
                                                                                <button class="btn-dibujos btn-dibujos-sm btn-eliminar"
                                                                                    style="background-color: #dc3545; color: white;"
                                                                                    onclick="almacenEliminarOtroArchivo('{{ $otroArchivo['ot'] }}', '{{ $otroArchivo['nombre'] }}', '{{ $otroArchivo['tipo'] }}', this, '{{ $otroArchivo['origin'] ?? '' }}')">Eliminar</button>
                                                                                @endif
                                                                            </div>
                                                                        </div>
                                                                    @endif
                                                                @endforeach
                                                            </div>
                                                        @endif


                                                        @if ($showControlCard)
                                                                @php

                                                                    $esReproceso = (bool) preg_match('/_R\d+$/i', $targetReg->ot);
                                                                    $controlDisabled = ($targetReg->tiene_modelo || $targetReg->pre_orden_email_sent) ? 'opacity: 0.5; pointer-events: none;' : '';
                                                                    $hideSiNo = ($esReproceso || $targetReg->pre_orden_sent || $targetReg->pre_orden_email_sent) ? 'display: none;' : '';
                                                                    $hideEditMail = ($targetReg->pre_orden_sent && !$targetReg->pre_orden_email_sent) ? '' : 'display: none;';

                                                                    $hideReprocesoPreOrden = ($esReproceso && !$targetReg->pre_orden_sent && !$targetReg->pre_orden_email_sent) ? '' : 'display: none;';
                                                                @endphp
                                                                <div class="lib-calidad-card" id="control-modelo-{{ md5($reg->ot) }}"
                                                                    style="{{ $controlDisabled }}">
                                                                    <div class="lib-calidad-card-header">
                                                                        <img src="{{ asset('images/almacen.png') }}" alt="Almacén"
                                                                            style="width:38px;height:38px;object-fit:contain;flex-shrink:0;">
                                                                        <div style="overflow:hidden;">
                                                                            <span class="lib-calidad-card-title">Control de Modelos &mdash;
                                                                                Almacén</span>
                                                                            <span
                                                                                class="lib-calidad-card-ot">{{ preg_replace('/_\d{8}_\d{6}_.*/', '', $reg->ot) }}</span>
                                                                        </div>
                                                                    </div>
                                                                    <div class="lib-calidad-card-body">
                                                                        <div class="lib-calidad-action-row">
                                                                            <h4 class="lib-calidad-card-prompt">
                                                                                @if ($targetReg->tiene_modelo)
                                                                                    ¡Modelo recibido y procesado! Pendiente de que Calidad lo revise.
                                                                                @elseif ($targetReg->pre_orden_email_sent)
                                                                                    Alerta enviada a Calidad. En espera de su revisión y nuevo veredicto
                                                                                    de
                                                                                    liberación.
                                                                                @elseif ($targetReg->pre_orden_sent)
                                                                                    @if ($esReproceso)
                                                                                        Pre-orden de re-proceso lista. Puedes editar los datos o enviar la
                                                                                        alerta
                                                                                        a Calidad para iniciar la revisión.
                                                                                    @else
                                                                                        Pre-orden lista. Puedes seguir editando los datos o enviarla por
                                                                                        correo.
                                                                                    @endif
                                                                                @elseif ($esReproceso)
                                                                                    OT en re-proceso por rechazo de Calidad. Genera o edita la pre-orden
                                                                                    de
                                                                                    modelo para iniciar el nuevo ciclo de fabricación.
                                                                                    @else
                                                                                    ¿Ya cuentas con el modelo de esta OT o necesitas generar una
                                                                                    pre-orden?
                                                                                @endif
                                                                            </h4>
                                                                            <div class="lib-calidad-card-btns">
                                                                                {{-- Botones para OT normal (no reproceso) --}}
                                                                                <button class="btn-modelo btn-modelo-si"
                                                                                    onclick="abrirModalConfirmarModelo('{{ $targetReg->ot }}', '{{ md5($reg->ot) }}')"
                                                                                    title="Sí, cuento con el modelo de esta OT"
                                                                                    style="{{ $hideSiNo }}">
                                                                                    <img src="{{ asset('images/Aprobado.png') }}" alt="Si">
                                                                                    <span>Tengo el Modelo</span>
                                                                                </button>
                                                                                <button class="btn-modelo btn-modelo-no"
                                                                                    onclick="abrirModalPreOrden('{{ $targetReg->ot }}')"
                                                                                    title="No cuento con él, generar formato PDF"
                                                                                    style="{{ $hideSiNo }}">
                                                                                    <img src="{{ asset('images/pdf.png') }}" alt="PDF">
                                                                                    <span>No, generar formato</span>
                                                                                </button>

                                                                                {{-- Botón inicial para re-proceso: generar pre-orden --}}
                                                                                <button class="btn-modelo btn-modelo-no"
                                                                                    onclick="abrirModalPreOrden('{{ $targetReg->ot }}')"
                                                                                    title="Generar / editar la pre-orden de fabricación de modelo"
                                                                                    style="{{ $hideReprocesoPreOrden }}">
                                                                                    <img src="{{ asset('images/pdf.png') }}" alt="Pre-Orden">
                                                                                    <span>Pre-Orden Modelo</span>
                                                                                </button>


                                                                                <button class="btn-modelo btn-modelo-edit"
                                                                                    onclick="abrirModalPreOrden('{{ $targetReg->ot }}')"
                                                                                    title="Editar información de la preorden existente"
                                                                                    style="{{ $hideEditMail }}">
                                                                                    <img src="{{ asset('images/editar-informacion.png') }}"
                                                                                        alt="Editar">
                                                                                    <span>Editar Pre-orden</span>
                                                                                </button>
                                                                                <button class="btn-modelo btn-modelo-email"
                                                                                    onclick="abrirModalEnviarPreOrden('{{ $targetReg->ot }}', 'modelo')"
                                                                                    title="{{ $esReproceso ? 'Enviar alerta a Calidad para iniciar revisión de re-proceso' : 'Enviar pre-orden por correo electrónico' }}"
                                                                                    style="{{ $hideEditMail }}">
                                                                                    <img src="{{ asset('images/enviando.png') }}" alt="Enviar">
                                                                                    <span>{{ $esReproceso ? 'Enviar Alerta' : 'Enviar Correo' }}</span>
                                                                                </button>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            @else
                                                                @php

                                                                    $liberaciones = \App\Models\LiberacionModeloFundicion::where('ot', $reg->ot)->where('estado', '!=', 'pendiente')->get();
                                                                    $aprobados = $liberaciones->where('decision', 'aprobar')->pluck('tipo_modelo')->unique()->values()->toArray();
                                                                    $rechazados = $liberaciones->where('decision', 'rechazar')->pluck('tipo_modelo')->unique()->values()->toArray();
                                                                    $castingEmailSent = ($reg->calidad_revision_status === 'casting_aprobado');

                                                                    // Detectar si el último reproceso fue aprobado por Calidad.
                                                                    // En ese caso la card de Rechazados asume el rol de la card de Aprobados
                                                                    // para evitar mostrar dos cards en un caso que NO es mixto.
                                                                    $reprocesoAprobadoPorCalidad = $latestReproceso
                                                                        && in_array($latestReproceso->calidad_revision_status, ['aprobado', 'calidad_aprobado']);
                                                                @endphp

                                                                @if ($castingEmailSent && count($aprobados) === 0)
                                                                    <div class="lib-calidad-card" id="control-almacen-aprobados-{{ md5($reg->ot) }}"
                                                                        style="margin-top: 15px; opacity: 0.9; pointer-events: none; border: 2px solid #16a34a;">
                                                                        <div class="lib-calidad-card-header"
                                                                            style="background: linear-gradient(135deg, #16a34a, #15803d); border-bottom: 2px solid rgba(22, 163, 74, 0.5);">
                                                                            <img src="{{ asset('images/almacen.png') }}" alt="Almacén"
                                                                                style="width:38px;height:38px;object-fit:contain;flex-shrink:0;">
                                                                            <div style="overflow:hidden;">
                                                                                <span class="lib-calidad-card-title" style="color: #ffffff;">Control de
                                                                                    Modelos
                                                                                    &mdash; Almacén</span>
                                                                                <span class="lib-calidad-card-ot"
                                                                                    style="color: #d1fae5;">{{ preg_replace('/_\d{8}_\d{6}_.*/', '', $reg->ot) }}</span>
                                                                            </div>
                                                                        </div>
                                                                        <div class="lib-calidad-card-body">
                                                                            <div class="lib-calidad-action-row">
                                                                                <h4 class="lib-calidad-card-prompt">
                                                                                    <span
                                                                                        style="color: #15803d; font-weight: 700; display: inline-flex; align-items: center; gap: 8px;">
                                                                                        <img src="{{ asset('images/ready.png') }}"
                                                                                            style="width: 22px; height: 22px; vertical-align: middle;"
                                                                                            alt="Listo">
                                                                                        Proceso de pre-orden finalizado correctamente. El correo ha sido
                                                                                        enviado
                                                                                        al proveedor. Favor de esperar nuevas instrucciones.
                                                                                    </span>
                                                                                </h4>
                                                                                <div class="lib-calidad-card-btns">
                                                                                    {{-- Sin botones: el proceso está terminado --}}
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                @endif

                                                                @if (count($aprobados) > 0)
                                                                    @php
                                                                        $castingPre = \App\Models\PreOrdenFundicion::where('ot', $reg->ot)->where('pdf_filename', 'LIKE', '%Casting%')->first();
                                                                        $hasCastingPre = (bool) $castingPre;
                                                                        $aprobCardDisabled = $castingEmailSent ? 'opacity: 0.5; pointer-events: none;' : '';
                                                                    @endphp
                                                                    <div class="lib-calidad-card" id="control-almacen-aprobados-{{ md5($reg->ot) }}"
                                                                        style="margin-top: 15px; {{ $aprobCardDisabled }}">
                                                                        <div class="lib-calidad-card-header"
                                                                            style="background: linear-gradient(135deg, #16a34a, #15803d); border-bottom: 2px solid rgba(22, 163, 74, 0.5);">
                                                                            <img src="{{ asset('images/almacen.png') }}" alt="Almacén"
                                                                                style="width:38px;height:38px;object-fit:contain;flex-shrink:0;">
                                                                            <div style="overflow:hidden;">
                                                                                <span class="lib-calidad-card-title" style="color: #ffffff;">Control de
                                                                                    Modelos
                                                                                    &mdash; Almacén (Aprobados)</span>
                                                                                <span class="lib-calidad-card-ot"
                                                                                    style="color: #d1fae5;">{{ preg_replace('/_\d{8}_\d{6}_.*/', '', $reg->ot) }}</span>
                                                                            </div>
                                                                        </div>
                                                                        <div class="lib-calidad-card-body">
                                                                            <div class="lib-calidad-action-row">
                                                                                <h4 class="lib-calidad-card-prompt">
                                                                                    @if ($castingEmailSent)
                                                                                        <span
                                                                                            style="color: #15803d; font-weight: 700; display: inline-flex; align-items: center; gap: 8px;">
                                                                                            <img src="{{ asset('images/ready.png') }}"
                                                                                                style="width: 20px; height: 20px; vertical-align: middle;"
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
                                                                                            style="display: flex; background-color: #15803d; color: white;">
                                                                                            <img src="{{ asset('images/editar-informacion.png') }}"
                                                                                                alt="Editar">
                                                                                            <span>Editar Pre-orden</span>
                                                                                        </button>
                                                                                        <button class="btn-modelo btn-modelo-email"
                                                                                            onclick="abrirModalEnviarPreOrden('{{ $reg->ot }}', 'casting')"
                                                                                            style="display: flex; background-color: #033966; color: white;">
                                                                                            <img src="{{ asset('images/enviando.png') }}" alt="Enviar">
                                                                                            <span>Enviar Correo</span>
                                                                                        </button>
                                                                                    @elseif ($reg->casting_pdf_generated)
                                                                                        <button class="btn-modelo btn-modelo-si"
                                                                                            onclick="abrirModalPreOrdenCasting('{{ $reg->ot }}')"
                                                                                            style="display: flex; background-color: #15803d; color: white;">
                                                                                            <img src="{{ asset('images/almacen.png') }}" alt="Preorden"
                                                                                                style="width: 16px; height: 16px; filter: brightness(0) invert(1);">
                                                                                            <span>Preorden de Casting</span>
                                                                                        </button>
                                                                                    @else
                                                                                        <button class="btn-modelo btn-modelo-si"
                                                                                            onclick="abrirModalGestionVeredicto('{{ $reg->ot }}', {{ json_encode($aprobados) }}, [])"
                                                                                            style="display: flex; background-color: #15803d; color: white;">
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
                                                                @if (count($rechazados) > 0 && !$reg->rechazos_procesados)
                                                                    @php
                                                                        $latestReproceso = null;
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
                                                                                && in_array($latestReproceso->calidad_revision_status, ['aprobado', 'calidad_aprobado']);
                                                                            if ($castingEmailSentReproceso) {
                                                                                $rechCardDisabled = 'opacity: 0.5; pointer-events: none;';
                                                                            } elseif ($ultimoRechazadoPorCalidad || $reprocesoAprobadoPorCalidad) {

                                                                                $rechCardDisabled = '';
                                                                            } elseif (!$latestReproceso || $latestReproceso->pre_orden_email_sent || $latestReproceso->tiene_modelo) {
                                                                                $rechCardDisabled = 'opacity: 0.65; pointer-events: none;';
                                                                            }
                                                                        }
                                                                    @endphp
                                                                    <div class="lib-calidad-card" id="control-almacen-rechazados-{{ md5($reg->ot) }}"
                                                                        style="margin-top: 15px; {{ $rechCardDisabled }}">
                                                                        @if ($reprocesoAprobadoPorCalidad)
                                                                            <div class="lib-calidad-card-header"
                                                                                style="background: linear-gradient(135deg, #16a34a, #15803d); border-bottom: 2px solid rgba(22, 163, 74, 0.5);">
                                                                                <img src="{{ asset('images/almacen.png') }}" alt="Almacén"
                                                                                    style="width:38px;height:38px;object-fit:contain;flex-shrink:0;">
                                                                                <div style="overflow:hidden;">
                                                                                    <span class="lib-calidad-card-title" style="color: #ffffff;">Control de
                                                                                        Modelos
                                                                                        &mdash; Almacén (<span
                                                                                            style="color: #ff0000; font-weight: 800;">Re-Proceso</span>)</span>
                                                                                    <span class="lib-calidad-card-ot"
                                                                                        style="color: #d1fae5;">{{ preg_replace('/_\d{8}_\d{6}_.*/', '', $reg->ot) }}</span>
                                                                                </div>
                                                                            </div>
                                                                        @else
                                                                            <div class="lib-calidad-card-header"
                                                                                style="background: linear-gradient(135deg, #dc2626, #b91c1c); border-bottom: 2px solid rgba(220, 38, 38, 0.5);">
                                                                                <img src="{{ asset('images/almacen.png') }}" alt="Almacén"
                                                                                    style="width:38px;height:38px;object-fit:contain;flex-shrink:0;">
                                                                                <div style="overflow:hidden;">
                                                                                    <span class="lib-calidad-card-title" style="color: #ffffff;">Control de
                                                                                        Modelos
                                                                                        &mdash; Almacén (Rechazados)</span>
                                                                                    <span class="lib-calidad-card-ot"
                                                                                        style="color: #fee2e2;">{{ preg_replace('/_\d{8}_\d{6}_.*/', '', $reg->ot) }}</span>
                                                                                </div>
                                                                            </div>
                                                                        @endif
                                                                        <div class="lib-calidad-card-body">
                                                                            <div class="lib-calidad-action-row">
                                                                                <h4 class="lib-calidad-card-prompt">
                                                                                    @if ($reg->rechazos_procesados)
                                                                                        @if ($reprocesoAprobadoPorCalidad && $latestReproceso)
                                                                                            <span
                                                                                                style="color: #15803d; font-weight: 700; display: inline-flex; align-items: center; gap: 8px;">
                                                                                                <img src="{{ asset('images/Aprobado.png') }}"
                                                                                                    style="width: 20px; height: 20px; vertical-align: middle;"
                                                                                                    alt="Aprobado">
                                                                                                ¡Re-proceso
                                                                                                <strong>{{ preg_replace('/_\d{8}_\d{6}_.*/', '', $latestReproceso->ot) }}</strong>
                                                                                                aprobado por Calidad! Los modelos:
                                                                                                <strong>{{ implode(', ', $rechazados) }}</strong> han sido
                                                                                                liberados.
                                                                                                Procede a subir los formatos F-CCL-LDM firmados para iniciar el
                                                                                                casting.
                                                                                            </span>
                                                                                        @elseif ($ultimoRechazadoPorCalidad && $latestReproceso)
                                                                                            Modelos Rechazados por Calidad para la OT de re-proceso
                                                                                            <strong>{{ $latestReproceso->ot }}</strong>:
                                                                                            <strong>{{ implode(', ', $rechazados) }}</strong>. Procede a subir
                                                                                            el
                                                                                            Formato de Rechazo y el SCAR correspondiente.
                                                                                        @elseif ($latestReproceso)
                                                                                            @if ($latestReproceso->pre_orden_email_sent)
                                                                                                Alerta enviada a Calidad para la OT de re-proceso
                                                                                                <strong>{{ $latestReproceso->ot }}</strong>. En espera de su
                                                                                                revisión.
                                                                                            @elseif ($latestReproceso->pre_orden_sent)
                                                                                                Pre-orden de re-proceso lista para
                                                                                                <strong>{{ $latestReproceso->ot }}</strong>. Puedes editar los datos
                                                                                                o
                                                                                                enviar la alerta a Calidad para iniciar la revisión.
                                                                                            @else
                                                                                                OT en re-proceso por rechazo de Calidad
                                                                                                (<strong>{{ $latestReproceso->ot }}</strong>). Genera o edita la
                                                                                                pre-orden
                                                                                                de modelo para iniciar el nuevo ciclo.
                                                                                            @endif
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
                                                                                        @if ($reprocesoAprobadoPorCalidad && $latestReproceso)

                                                                                            @php
                                                                                                $latestAprobados = \App\Models\LiberacionModeloFundicion::where('ot', $latestReproceso->ot)
                                                                                                    ->where('estado', '!=', 'pendiente')
                                                                                                    ->where('decision', 'aprobar')
                                                                                                    ->pluck('tipo_modelo')
                                                                                                    ->toArray();
                                                                                                $castingPreReproceso = \App\Models\PreOrdenFundicion::where('ot', $latestReproceso->ot)
                                                                                                    ->where('pdf_filename', 'LIKE', '%Casting%')
                                                                                                    ->first();
                                                                                                $hasCastingPreReproceso = (bool) $castingPreReproceso;
                                                                                                $castingEmailSentReproceso = ($latestReproceso->calidad_revision_status === 'casting_aprobado');
                                                                                            @endphp
                                                                                            @if ($castingEmailSentReproceso)

                                                                                            @elseif ($hasCastingPreReproceso)
                                                                                                <button class="btn-modelo btn-modelo-si"
                                                                                                    onclick="abrirModalPreOrdenCasting('{{ $latestReproceso->ot }}')"
                                                                                                    style="display: flex; background-color: #15803d; color: white;">
                                                                                                    <img src="{{ asset('images/editar-informacion.png') }}"
                                                                                                        alt="Editar">
                                                                                                    <span>Editar Pre-orden</span>
                                                                                                </button>
                                                                                                <button class="btn-modelo btn-modelo-email"
                                                                                                    onclick="abrirModalEnviarPreOrden('{{ $latestReproceso->ot }}', 'casting')"
                                                                                                    style="display: flex; background-color: #033966; color: white;">
                                                                                                    <img src="{{ asset('images/enviando.png') }}" alt="Enviar">
                                                                                                    <span>Enviar Correo</span>
                                                                                                </button>
                                                                                            @elseif ($latestReproceso->casting_pdf_generated)
                                                                                                <button class="btn-modelo btn-modelo-si"
                                                                                                    onclick="abrirModalPreOrdenCasting('{{ $latestReproceso->ot }}')"
                                                                                                    style="display: flex; background-color: #15803d; color: white;">
                                                                                                    <img src="{{ asset('images/almacen.png') }}" alt="Preorden"
                                                                                                        style="width: 16px; height: 16px; filter: brightness(0) invert(1);">
                                                                                                    <span>Preorden de Casting</span>
                                                                                                </button>
                                                                                            @else
                                                                                                <button class="btn-modelo btn-modelo-si"
                                                                                                    onclick="abrirModalGestionVeredicto('{{ $latestReproceso->ot }}', {{ json_encode($latestAprobados ?: $rechazados) }}, [])"
                                                                                                    style="display: flex; background-color: #15803d; color: white;">
                                                                                                    <img src="{{ asset('images/Aprobado.png') }}" alt="Aprobado">
                                                                                                    <span>Procesar Aceptados</span>
                                                                                                </button>
                                                                                            @endif
                                                                                        @elseif ($ultimoRechazadoPorCalidad && $latestReproceso)
                                                                                            <button class="btn-modelo btn-modelo-no"
                                                                                                onclick="abrirModalGestionVeredicto('{{ $latestReproceso->ot }}', [], {{ json_encode($rechazados) }})"
                                                                                                style="display: flex; background-color: #b91c1c; color: white;">
                                                                                                <img src="{{ asset('images/Rechazado.png') }}" alt="No">
                                                                                                <span>Procesar Rechazados</span>
                                                                                            </button>
                                                                                        @elseif ($latestReproceso)
                                                                                            @php
                                                                                                $hideReprocesoPreOrden = (!$latestReproceso->pre_orden_sent && !$latestReproceso->pre_orden_email_sent) ? '' : 'display: none;';
                                                                                                $hideEditMail = ($latestReproceso->pre_orden_sent && !$latestReproceso->pre_orden_email_sent) ? '' : 'display: none;';
                                                                                                
                                                                                                // VALIDACIÓN ESTRICTA: SCAR Obligatorio
                                                                                                $scarExists = \App\Models\ScarModelo::where('ot', preg_replace('/_R\d+$/i', '', $latestReproceso->ot))->orWhere('ot', $latestReproceso->ot)->exists();
                                                                                                $scarDisabledAttr = (!$scarExists) ? 'disabled style="opacity: 0.5; cursor: not-allowed; ' . $hideReprocesoPreOrden . '" title="Requisito faltante: SCAR firmado y Formato de Rechazo"' : 'style="' . $hideReprocesoPreOrden . '" title="Generar / editar la pre-orden de fabricación de modelo"';
                                                                                            @endphp
                                                                                            {{-- Botón inicial para re-proceso: generar pre-orden --}}
                                                                                            <button class="btn-modelo btn-modelo-no"
                                                                                                onclick="abrirModalPreOrden('{{ $latestReproceso->ot }}')"
                                                                                                {!! $scarDisabledAttr !!}>
                                                                                                <img src="{{ asset('images/pdf.png') }}" alt="Pre-Orden">
                                                                                                <span>Pre-Orden de Modelo</span>
                                                                                            </button>

                                                                                            {{-- Editar + Enviar Alerta (cuando pre-orden ya existe) --}}
                                                                                            <button class="btn-modelo btn-modelo-edit"
                                                                                                onclick="abrirModalPreOrden('{{ $latestReproceso->ot }}')"
                                                                                                title="Editar información de la preorden existente"
                                                                                                style="{{ $hideEditMail }}">
                                                                                                <img src="{{ asset('images/editar-informacion.png') }}"
                                                                                                    alt="Editar">
                                                                                                <span>Editar Pre-orden</span>
                                                                                            </button>
                                                                                            <button class="btn-modelo btn-modelo-email"
                                                                                                onclick="abrirModalEnviarPreOrden('{{ $latestReproceso->ot }}', 'modelo')"
                                                                                                title="Enviar alerta a Calidad para iniciar revisión de re-proceso"
                                                                                                style="{{ $hideEditMail }}">
                                                                                                <img src="{{ asset('images/enviando.png') }}" alt="Enviar">
                                                                                                <span>Enviar Alerta</span>
                                                                                            </button>
                                                                                        @else
                                                                                            <button class="btn-modelo btn-modelo-no"
                                                                                                style="display: flex; background-color: #b91c1c; color: white;">
                                                                                                <img src="{{ asset('images/Rechazado.png') }}" alt="No">
                                                                                                <span>Rechazos Procesados</span>
                                                                                            </button>
                                                                                        @endif
                                                                                    @else
                                                                                        <button class="btn-modelo btn-modelo-no"
                                                                                            onclick="abrirModalGestionVeredicto('{{ $reg->ot }}', [], {{ json_encode($rechazados) }})"
                                                                                            style="display: flex; background-color: #b91c1c; color: white;">
                                                                                            <img src="{{ asset('images/Rechazado.png') }}" alt="No">
                                                                                            <span>Procesar Rechazados</span>
                                                                                        </button>
                                                                                    @endif
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                @endif
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
        <div class="alm-modal-content"
            style="max-width: 1100px; border-radius: 20px; border: 2.5px solid #0a8504; overflow: hidden;">
            <div class="alm-modal-header"
                style="background: linear-gradient(135deg, #0a8504, #064e03); border-bottom: 2px solid #064e03; padding: 2.2em 2.5em 2em; position: relative;">
                <div class="div-cerrar">
                    <button type="button" class="btn-cerrar" onclick="cerrarModalConfirmarModelo()"
                        style="position: absolute; top: 25px; right: 25px; background: rgba(255, 255, 255, 0.18); border: 1.5px solid rgba(255, 255, 255, 0.45); border-radius: 50%; width: 42px; height: 42px; display: inline-flex; align-items: center; justify-content: center; cursor: pointer; transition: all 0.2s ease;">
                        <img class="img-cerrar" src="{{ asset('images/cerrar.png') }}" alt="Cerrar"
                            style="width: 14px; height: 14px; filter: brightness(0) invert(1);">
                    </button>
                </div>
                <div style="display: flex; align-items: center; gap: 18px;">
                    <img src="{{ asset('images/Aprobado.png') }}"
                        style="width: 46px; height: 46px; object-fit: contain; filter: drop-shadow(0 4px 8px rgba(0,0,0,0.15));"
                        alt="">
                    <div>
                        <h3
                            style="color:#fff; margin:0; font-size:1.45em; font-weight: 800; font-family: 'Poppins', sans-serif;">
                            Confirmar Disponibilidad del Modelo</h3>
                        <div id="confirmar-modelo-subtitle"
                            style="color: rgba(255,255,255,0.9); font-size: 0.95em; margin-top: 2px; font-weight: 500; font-family: 'Poppins', sans-serif;">
                            OT: -</div>
                    </div>
                </div>
            </div>
            <div class="alm-modal-body"
                style="padding: 2.2em 2.5em; background: #fafafa; font-family: 'Poppins', sans-serif;">
                <form id="formConfirmarModelo" enctype="multipart/form-data"
                      data-email-modelo="{{ env('EMAIL_PROVEEDOR_MODELOS', 'produccion@ssmetalf.mx,asistenteprod@ssmetalf.mx') }}"
                      data-email-calidad="{{ env('EMAIL_CALIDAD', 'inspecciontec@grupoindsaavedra.com') }}">
                    <input type="hidden" id="cm-ot" name="ot">
                    <input type="hidden" id="cm-id-hash" name="id_hash">

                    <div style="padding: 0 0 14px; color: #334155; font-size:0.97em;">
                        <p style="margin-bottom:12px; font-weight: 500;">¿Confirmas que cuentas físicamente con el modelo
                            para esta OT?</p>
                        <p
                            style="background:#fef9c3; border:1px solid #fde047; border-radius:12px; padding:12px 18px; color:#713f12; font-size:0.9em; line-height: 1.5; margin-bottom: 20px;">
                            <strong>Documentos requeridos:</strong> Debes adjuntar los documentos que acrediten la
                            recepción del modelo (ej. remisión, hoja de entrega, fotos).
                        </p>
                    </div>

                    <div class="form-group" style="margin-bottom: 20px;" id="div-cm-destinatario">
                        <label for="cm-destinatario" style="font-weight:700; color:#334155; display:block; margin-bottom:10px; font-family:'Poppins', sans-serif; font-size:1.15em;">
                            Notificar a Proveedor (correo electrónico):
                        </label>
                        <input type="text" id="cm-destinatario" name="destinatario" class="form-control" required style="font-family:'Poppins', sans-serif; font-size: 1.1em; padding: 12px 18px; height: auto; border-radius: 10px;">
                        <span style="font-size: 0.85em; color: #64748b; margin-top: 4px; display: block;">Separa múltiples correos con comas.</span>
                    </div>

                    <div class="form-group" style="margin-bottom: 20px;" id="div-cm-destinatario-calidad">
                        <label for="cm-destinatario-calidad" style="font-weight:700; color:#334155; display:block; margin-bottom:10px; font-family:'Poppins', sans-serif; font-size:1.15em;">
                            Notificar a Calidad (correo electrónico):
                        </label>
                        <input type="text" id="cm-destinatario-calidad" name="destinatario_calidad" class="form-control" style="font-family:'Poppins', sans-serif; font-size: 1.1em; padding: 12px 18px; height: auto; border-radius: 10px;">
                        <span style="font-size: 0.85em; color: #64748b; margin-top: 4px; display: block;">Copia para Calidad (Modelos).</span>
                    </div>

                    {{-- FECHA DE CONFIRMACIÓN / ENVÍO --}}
                    <div class="form-group" style="margin-bottom: 20px;">
                        <label for="cm-fecha"
                            style="font-weight:700; color:#334155; display:block; margin-bottom:10px; font-family:'Poppins', sans-serif; font-size:1.15em;">
                            Fecha de Confirmación / Envío <span style="color:#9c0300;">*</span>
                        </label>
                        <input type="date" id="cm-fecha" name="fecha" class="form-control" required
                            style="font-family:'Poppins', sans-serif; font-size: 1.1em; padding: 12px 18px; height: auto; border-radius: 10px;">
                    </div>

                    <div class="form-group" style="margin-bottom: 22px;">
                        <label
                            style="font-weight: 700; color: #334155; display: block; margin-bottom: 8px; font-family:'Poppins', sans-serif; font-size:1.15em;">Archivos
                            de la OT disponibles para adjuntar:</label>
                        <div id="cm-server-files-container"
                            style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; padding: 15px; max-height: 420px; overflow-y: auto; display: flex; flex-direction: column; gap: 15px;">
                            <div class="alm-spinner" id="cm-server-spinner"
                                style="border-top-color: #0284c7; display: block; margin: 10px auto; grid-column:1/-1;">
                            </div>
                        </div>
                    </div>

                    <div class="form-group" style="margin-bottom: 22px;">
                        <label class="custom-file-upload-label"
                            style="font-weight:700; color:#334155; display:block; margin-bottom:10px; font-family:'Poppins', sans-serif; font-size:1.15em;">
                            Adjuntar documentos de recepción <span style="color:#9c0300;">*</span>
                        </label>
                        <div class="custom-file-dropzone"
                            style="border: 2px dashed #0a8504; background: #f0fdf4; min-height: 80px; position: relative; border-radius: 12px; display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 12px; cursor: pointer;">
                            <input type="file" id="cm-archivos" name="archivos[]" class="custom-file-input" multiple
                                accept=".pdf,image/*"
                                style="position: absolute; width:100%; height:100%; opacity:0; cursor:pointer;">
                            <div class="dropzone-content">
                                <img src="{{ asset('images/anadir.png') }}" class="dropzone-icon"
                                    style="width: 40px; height: 40px; margin-bottom: 8px; object-fit: contain;">
                                <span class="dropzone-text"
                                    style="font-weight: 700; color: #0a8504; font-size: 0.85em; text-align: center; font-family:'Poppins', sans-serif;">Arrastra
                                    archivos aquí o haz clic para buscar</span>
                                <span class="dropzone-subtext"
                                    style="font-size: 0.7em; color: #64748b; margin-top: 2px; font-family:'Poppins', sans-serif;">Soporta
                                    múltiples archivos PDF o imágenes</span>
                            </div>
                        </div>
                        <div id="cm-archivos-list"
                            style="margin-top: 15px; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; padding: 15px; max-height: 420px; overflow-y: auto; display: none; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 12px; justify-items: center;">
                        </div>
                    </div>

                    <div class="form-actions" style="text-align:center; margin-top:24px;">
                        <button type="submit" class="btn-save-preorden" id="btn-submit-confirmar-modelo"
                            style="background: linear-gradient(135deg, #0a8504, #064e03); box-shadow: 0 4px 15px rgba(10,133,4,0.35); padding:12px 32px; border:none; border-radius:10px; color:#fff; font-weight:700; cursor:pointer; font-family:'Poppins', sans-serif; font-size:1.05em; display: inline-flex; align-items: center; justify-content: center; gap: 8px;">
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
                                <label for="po-proveedor">Proveedor <span style="color:#dc2626;">*</span>:</label>
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
                                        <th style="width: 16%;">Tipo de Modelo <span style="color:#dc2626;">*</span></th>
                                        <th style="width: 12%;">Impresiones <span style="color:#dc2626;">*</span></th>
                                        <th style="width: 12%;">Cantidad <span style="color:#dc2626;">*</span></th>
                                        <th style="width: 22%;">Descripción <span style="color:#dc2626;">*</span></th>
                                        <th style="width: 22%;">Código de Modelo</th>
                                        <th style="width: 12%;">Fecha Entrega</th>
                                        <th style="width: 6%; text-align:center;">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody id="alm-tbody-preorden">

                                </tbody>
                            </table>
                            <div style="margin-top: 10px; text-align: center;">
                                <button type="button" id="btn-add-clase-po" class="btn-img-action"
                                    onclick="agregarFilaPreOrden()" title="Añadir una nueva clase a la pre-orden"
                                    style="display: inline-block;">
                                    <img src="{{ asset('images/anadir.png') }}" alt="Añadir" style="width: 40px;">
                                </button>
                            </div>
                        </div>

                        <div class="form-group" style="margin-top: 20px;">
                            <div id="po-observaciones-cycle-prefix"
                                style="display: none; padding: 8px 12px; background-color: #fee2e2; border-left: 4px solid #ef4444; color: #991b1b; font-weight: bold; margin-bottom: 8px; border-radius: 4px; font-family: 'Poppins', sans-serif;">
                            </div>
                            <label for="po-observaciones">Observaciones:</label>
                            <textarea id="po-observaciones" name="observaciones" class="form-control" rows="3"></textarea>
                        </div>

                        <div class="form-actions" style="margin-top: 30px; text-align: center;">
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
        <div class="alm-modal-content"
            style="max-width: 1800px; width: 95vw; border-radius: 20px; overflow: hidden; border: 1.5px solid #0284c7;">
            <div class="alm-modal-header" id="poc-header"
                style="background: linear-gradient(135deg, #0369a1, #0284c7); padding: 2.2em 2.5em 1.5em; position: relative;">
                <div class="div-cerrar">
                    <button type="button" class="btn-cerrar" onclick="cerrarModalPreOrdenCasting()"
                        style="position: absolute; top: 25px; right: 25px; background: rgba(255, 255, 255, 0.18); border: 1.5px solid rgba(255, 255, 255, 0.45); border-radius: 50%; width: 42px; height: 42px; display: inline-flex; align-items: center; justify-content: center; cursor: pointer; transition: all 0.2s ease;">
                        <img class="img-cerrar" src="{{ asset('images/cerrar.png') }}"
                            style="width: 14px; height: 14px; filter: brightness(0) invert(1);" alt="Cerrar">
                    </button>
                </div>
                <h3 style="font-size: 2em; margin: 0; font-family:'Poppins', sans-serif; font-weight: 700; color: #fff;">
                    Pre-Orden de Fabricación de Casting (4ALM-17)</h3>
                <p id="poc-modal-subtitle" class="lib-modal-subtitle"
                    style="color: #bae6fd; font-size: 1.15em; margin-top: 8px; margin-bottom: 0; font-family:'Poppins', sans-serif; font-weight: 500;">
                </p>

                <div
                    style="display: flex; gap: 10px; margin-top: 25px; border-bottom: 2px solid rgba(255,255,255,0.2); padding-bottom: 0; align-items: center;">
                    <button type="button" id="tab-poc-page-1" class="btn-po-tab active" onclick="switchPocPage(1)"
                        style="border: none; padding: 12px 25px; border-top-left-radius: 12px; border-top-right-radius: 12px; font-family: 'Poppins', sans-serif; font-weight: 600; font-size: 1.05em; cursor: pointer; transition: all 0.2s ease;">
                        Proveedor 1
                    </button>
                    <button type="button" id="tab-poc-page-2" class="btn-po-tab" onclick="switchPocPage(2)"
                        style="display: none; border: none; padding: 12px 25px; border-top-left-radius: 12px; border-top-right-radius: 12px; font-family: 'Poppins', sans-serif; font-weight: 600; font-size: 1.05em; cursor: pointer; transition: all 0.2s ease;">
                        Proveedor 2
                    </button>

                    <button type="button" id="btn-add-poc-page-2" class="btns btn-add-tab" onclick="agregarPocPagina2()"
                        style="display: flex; align-items: center; gap: 6px; padding: 8px 16px; background: rgba(255,255,255,0.15); border: 1.5px dashed rgba(255,255,255,0.5); border-radius: 8px; color: white; cursor: pointer; font-family: 'Poppins', sans-serif; font-size: 0.9em; font-weight: 500; transition: all 0.2s ease; margin-left: 15px; height: auto;">
                        <img src="{{ asset('images/anadir.png') }}"
                            style="width: 14px; height: 14px; filter: brightness(0) invert(1);" alt=""> Agregar Proveedor 2
                    </button>
                    <button type="button" id="btn-remove-poc-page-2" class="btns btn-remove-tab"
                        onclick="removerPocPagina2()"
                        style="display: none; align-items: center; gap: 6px; padding: 8px 16px; background: #dc2626; border: 1.5px solid #b91c1c; border-radius: 8px; color: #ffffff; cursor: pointer; font-family: 'Poppins', sans-serif; font-size: 0.9em; font-weight: 500; transition: all 0.2s ease; margin-left: 15px; height: auto;">
                        Remover Proveedor 2
                    </button>
                </div>
            </div>

            <div class="alm-modal-body" style="padding: 2.5em; background: #fafafa; font-family: 'Poppins', sans-serif;">
                <form id="formPreOrdenCasting" novalidate autocomplete="off">
                    @csrf
                    <input type="hidden" id="poc-has-page2" name="has_page2" value="0">


                    <div id="poc-page-1" class="poc-page">
                        <div class="form-grid"
                            style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 20px; margin-bottom: 25px;">
                            <div class="form-group">
                                <label for="poc-p1-proveedor"
                                    style="font-weight: 700; color: #334155; margin-bottom: 8px; display: block; font-family:'Poppins', sans-serif;">Proveedor <span style="color:#dc2626;">*</span>:</label>
                                <select id="poc-p1-proveedor" name="page1_proveedor" class="form-control" required
                                    onchange="handlePocProveedorChange(1)"
                                    style="height: auto; padding: 12px 16px; border-radius: 10px; font-family:'Poppins', sans-serif; font-size: 1.05em;">
                                    <option value="" disabled selected>-- Selecciona un proveedor --</option>
                                    <option value="SS Metal Foundry, S. de R. L. de C. V.">SS Metal Foundry, S. de R. L. de C. V.</option>
                                    <option value="Fundición Especializada, S. A. de C. V.">Fundición Especializada, S. A. de C. V.</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="poc-p1-fecha"
                                    style="font-weight: 700; color: #334155; margin-bottom: 8px; display: block; font-family:'Poppins', sans-serif;">Fecha:</label>
                                <input type="date" id="poc-p1-fecha" name="page1_fecha" class="form-control" readonly
                                    required
                                    style="height: auto; padding: 12px 16px; border-radius: 10px; font-family:'Poppins', sans-serif; font-size: 1.05em; background: #f1f5f9;">
                            </div>
                            <div class="form-group">
                                <label for="poc-p1-folio"
                                    style="font-weight: 700; color: #334155; margin-bottom: 8px; display: block; font-family:'Poppins', sans-serif;">Folio:</label>
                                <input type="text" id="poc-p1-folio" name="page1_folio" class="form-control" readonly
                                    required
                                    style="height: auto; padding: 12px 16px; border-radius: 10px; font-family:'Poppins', sans-serif; font-size: 1.05em; background: #f1f5f9; font-weight: bold; color: #0369a1;">
                            </div>
                            <div class="form-group">
                                <label for="poc-p1-moldura"
                                    style="font-weight: 700; color: #334155; margin-bottom: 8px; display: block; font-family:'Poppins', sans-serif;">Moldura:</label>
                                <input type="text" id="poc-p1-moldura" name="page1_moldura" class="form-control" readonly
                                    required
                                    style="height: auto; padding: 12px 16px; border-radius: 10px; font-family:'Poppins', sans-serif; font-size: 1.05em; background: #f1f5f9;">
                            </div>
                            <div class="form-group">
                                <label for="poc-p1-ot"
                                    style="font-weight: 700; color: #334155; margin-bottom: 8px; display: block; font-family:'Poppins', sans-serif;">Orden
                                    de Trabajo:</label>
                                <input type="text" id="poc-p1-ot" name="page1_ot" class="form-control" readonly required
                                    style="height: auto; padding: 12px 16px; border-radius: 10px; font-family:'Poppins', sans-serif; font-size: 1.05em; background: #f1f5f9;">
                            </div>
                            <div class="form-group">
                                <label for="poc-p1-fecha-entrega"
                                    style="font-weight: 700; color: #334155; margin-bottom: 8px; display: block; font-family:'Poppins', sans-serif;">Fecha
                                    Entrega <span style="color:#dc2626;">*</span>:</label>
                                <input type="date" id="poc-p1-fecha-entrega" name="page1_fecha_entrega" class="form-control"
                                    required
                                    style="height: auto; padding: 12px 16px; border-radius: 10px; font-family:'Poppins', sans-serif; font-size: 1.05em;">
                            </div>
                        </div>

                        <div class="modal-table-container"
                            style="overflow-x: auto; background: #fff; border: 1px solid #e2e8f0; border-radius: 14px; padding: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
                            <table class="modal-table" style="width: 100%; border-collapse: collapse; text-align: left;">
                                <thead>
                                    <tr
                                        style="border-bottom: 2px solid #cbd5e1; color: #475569; font-weight: 700; font-size: 0.95em;">
                                        <th style="padding: 12px 10px; width: 12%; font-family:'Poppins', sans-serif;">Tipo
                                            de Modelo <span style="color:#dc2626;">*</span></th>
                                        <th style="padding: 12px 10px; width: 8%; font-family:'Poppins', sans-serif;">Cant.
                                            Fabricar <span style="color:#dc2626;">*</span></th>
                                        <th style="padding: 12px 10px; width: 8%; font-family:'Poppins', sans-serif;">Cant.
                                            Consign. <span style="color:#dc2626;">*</span></th>
                                        <th style="padding: 12px 10px; width: 15%; font-family:'Poppins', sans-serif;">
                                            Descripción <span style="color:#dc2626;">*</span></th>
                                        <th style="padding: 12px 10px; width: 14%; font-family:'Poppins', sans-serif;">
                                            Material <span style="color:#dc2626;">*</span></th>
                                        <th style="padding: 12px 10px; width: 12%; font-family:'Poppins', sans-serif;">
                                            Código de Modelo <span style="color:#dc2626;">*</span></th>
                                        <th style="padding: 12px 10px; width: 7%; font-family:'Poppins', sans-serif;">Peso
                                            Juego (KG) </th>
                                        <th style="padding: 12px 10px; width: 7%; font-family:'Poppins', sans-serif;">Peso
                                            Total (KG) </th>
                                        <th style="padding: 12px 10px; width: 12%; font-family:'Poppins', sans-serif;">Fecha
                                            Entrega <span style="color:#dc2626;">*</span></th>
                                        <th
                                            style="padding: 12px 10px; width: 5%; text-align: center; font-family:'Poppins', sans-serif;">
                                            Acciones</th>
                                    </tr>
                                </thead>
                                <tbody id="alm-tbody-poc-p1">

                                </tbody>
                            </table>
                            <div style="margin-top: 15px; text-align: center;">
                                <button type="button" class="btn-img-action" onclick="agregarFilaPoc(1)"
                                    title="Añadir una nueva fila"
                                    style="border: none; background: none; cursor: pointer; padding: 5px; outline: none; transition: transform 0.2s ease;">
                                    <img src="{{ asset('images/anadir.png') }}" alt="Añadir"
                                        style="width: 38px; height: 38px;">
                                </button>
                            </div>
                        </div>

                        <div class="form-group" style="margin-top: 25px;">
                            <label for="poc-p1-observaciones"
                                style="font-weight: 700; color: #334155; margin-bottom: 8px; display: block; font-family:'Poppins', sans-serif;">Observaciones:</label>
                            <textarea id="poc-p1-observaciones" name="page1_observaciones" class="form-control" rows="3"
                                style="border-radius: 10px; padding: 14px; font-family:'Poppins',sans-serif; font-size: 1em; width: 100%; box-sizing: border-box; border: 1.5px solid #cbd5e1;"></textarea>
                        </div>
                    </div>


                    <div id="poc-page-2" class="poc-page" style="display: none;">
                        <div class="form-grid"
                            style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 20px; margin-bottom: 25px;">
                            <div class="form-group">
                                <label for="poc-p2-proveedor"
                                    style="font-weight: 700; color: #334155; margin-bottom: 8px; display: block; font-family:'Poppins', sans-serif;">Proveedor <span style="color:#dc2626;">*</span>:</label>
                                <select id="poc-p2-proveedor" name="page2_proveedor" class="form-control" required
                                    onchange="handlePocProveedorChange(2)"
                                    style="height: auto; padding: 12px 16px; border-radius: 10px; font-family:'Poppins', sans-serif; font-size: 1.05em;">
                                    <option value="" disabled selected>-- Selecciona un proveedor --</option>
                                    <option value="Fundición Especializada, S. A. de C. V.">Fundición Especializada, S. A. de C. V.</option>
                                    <option value="SS Metal Foundry, S. de R. L. de C. V.">SS Metal Foundry, S. de R. L. de C. V.</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="poc-p2-fecha"
                                    style="font-weight: 700; color: #334155; margin-bottom: 8px; display: block; font-family:'Poppins', sans-serif;">Fecha:</label>
                                <input type="date" id="poc-p2-fecha" name="page2_fecha" class="form-control" readonly
                                    required
                                    style="height: auto; padding: 12px 16px; border-radius: 10px; font-family:'Poppins', sans-serif; font-size: 1.05em; background: #f1f5f9;">
                            </div>
                            <div class="form-group">
                                <label for="poc-p2-folio"
                                    style="font-weight: 700; color: #334155; margin-bottom: 8px; display: block; font-family:'Poppins', sans-serif;">Folio:</label>
                                <input type="text" id="poc-p2-folio" name="page2_folio" class="form-control" readonly
                                    required
                                    style="height: auto; padding: 12px 16px; border-radius: 10px; font-family:'Poppins', sans-serif; font-size: 1.05em; background: #f1f5f9; font-weight: bold; color: #0369a1;">
                            </div>
                            <div class="form-group">
                                <label for="poc-p2-moldura"
                                    style="font-weight: 700; color: #334155; margin-bottom: 8px; display: block; font-family:'Poppins', sans-serif;">Moldura:</label>
                                <input type="text" id="poc-p2-moldura" name="page2_moldura" class="form-control" readonly
                                    required
                                    style="height: auto; padding: 12px 16px; border-radius: 10px; font-family:'Poppins', sans-serif; font-size: 1.05em; background: #f1f5f9;">
                            </div>
                            <div class="form-group">
                                <label for="poc-p2-ot"
                                    style="font-weight: 700; color: #334155; margin-bottom: 8px; display: block; font-family:'Poppins', sans-serif;">Orden
                                    de Trabajo:</label>
                                <input type="text" id="poc-p2-ot" name="page2_ot" class="form-control" readonly required
                                    style="height: auto; padding: 12px 16px; border-radius: 10px; font-family:'Poppins', sans-serif; font-size: 1.05em; background: #f1f5f9;">
                            </div>
                            <div class="form-group">
                                <label for="poc-p2-fecha-entrega"
                                    style="font-weight: 700; color: #334155; margin-bottom: 8px; display: block; font-family:'Poppins', sans-serif;">Fecha
                                    Entrega <span style="color:#dc2626;">*</span>:</label>
                                <input type="date" id="poc-p2-fecha-entrega" name="page2_fecha_entrega" class="form-control"
                                    required
                                    style="height: auto; padding: 12px 16px; border-radius: 10px; font-family:'Poppins', sans-serif; font-size: 1.05em;">
                            </div>
                        </div>

                        <div class="modal-table-container"
                            style="overflow-x: auto; background: #fff; border: 1px solid #e2e8f0; border-radius: 14px; padding: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
                            <table class="modal-table" style="width: 100%; border-collapse: collapse; text-align: left;">
                                <thead>
                                    <tr
                                        style="border-bottom: 2px solid #cbd5e1; color: #475569; font-weight: 700; font-size: 0.95em;">
                                        <th style="padding: 12px 10px; width: 12%; font-family:'Poppins', sans-serif;">Tipo
                                            de Modelo <span style="color:#dc2626;">*</span></th>
                                        <th style="padding: 12px 10px; width: 8%; font-family:'Poppins', sans-serif;">Cant.
                                            Fabricar <span style="color:#dc2626;">*</span></th>
                                        <th style="padding: 12px 10px; width: 8%; font-family:'Poppins', sans-serif;">Cant.
                                            Consign. <span style="color:#dc2626;">*</span></th>
                                        <th style="padding: 12px 10px; width: 15%; font-family:'Poppins', sans-serif;">
                                            Descripción <span style="color:#dc2626;">*</span></th>
                                        <th style="padding: 12px 10px; width: 14%; font-family:'Poppins', sans-serif;">
                                            Material <span style="color:#dc2626;">*</span></th>
                                        <th style="padding: 12px 10px; width: 12%; font-family:'Poppins', sans-serif;">
                                            Código de Modelo <span style="color:#dc2626;">*</span></th>
                                        <th style="padding: 12px 10px; width: 7%; font-family:'Poppins', sans-serif;">Peso
                                            Juego (KG) </th>
                                        <th style="padding: 12px 10px; width: 7%; font-family:'Poppins', sans-serif;">Peso
                                            Total (KG) </th>
                                        <th style="padding: 12px 10px; width: 12%; font-family:'Poppins', sans-serif;">Fecha
                                            Entrega <span style="color:#dc2626;">*</span></th>
                                        <th
                                            style="padding: 12px 10px; width: 5%; text-align: center; font-family:'Poppins', sans-serif;">
                                            Acciones</th>
                                    </tr>
                                </thead>
                                <tbody id="alm-tbody-poc-p2">

                                </tbody>
                            </table>
                            <div style="margin-top: 15px; text-align: center;">
                                <button type="button" class="btn-img-action" onclick="agregarFilaPoc(2)"
                                    title="Añadir una nueva fila"
                                    style="border: none; background: none; cursor: pointer; padding: 5px; outline: none; transition: transform 0.2s ease;">
                                    <img src="{{ asset('images/anadir.png') }}" alt="Añadir"
                                        style="width: 38px; height: 38px;">
                                </button>
                            </div>
                        </div>

                        <div class="form-group" style="margin-top: 25px;">
                            <label for="poc-p2-observaciones"
                                style="font-weight: 700; color: #334155; margin-bottom: 8px; display: block; font-family:'Poppins', sans-serif;">Observaciones:</label>
                            <textarea id="poc-p2-observaciones" name="page2_observaciones" class="form-control" rows="3"
                                style="border-radius: 10px; padding: 14px; font-family:'Poppins',sans-serif; font-size: 1em; width: 100%; box-sizing: border-box; border: 1.5px solid #cbd5e1;"></textarea>
                        </div>
                    </div>

                    <div class="form-actions" style="margin-top: 35px; text-align: center;">
                        <button type="submit" class="btn-save-preorden" id="btn-submit-poc"
                            style="font-size: 1.2em; padding: 15px 35px; border-radius: 10px; font-family: 'Poppins', sans-serif; font-weight: 700; background: linear-gradient(135deg, #0369a1, #0284c7); border: none; color: white; cursor: pointer; transition: all 0.2s ease; box-shadow: 0 4px 15px rgba(3, 105, 161, 0.3); height: auto;">
                            Guardar y Descargar Pre-Orden de Casting
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div id="modalEnviarPreOrden" class="alm-modal">
        <div class="alm-modal-content" style="max-width: 1100px;">
            <div class="alm-modal-header">
                <div class="div-cerrar">
                    <button type="button" class="btn-cerrar" onclick="cerrarModalEnviarPreOrden()">
                        <img class="img-cerrar" src="{{ asset('images/cerrar.png') }}">
                    </button>
                </div>
                <h3>Enviar Pre-Orden por Correo</h3>
                <p id="env-po-modal-subtitle" class="lib-modal-subtitle"
                    style="color: #bae6fd; font-size: 0.9em; margin-top: 4px; margin-bottom: 0;"></p>
            </div>
            <div class="alm-modal-body">
                <form id="formEnviarPreOrden" enctype="multipart/form-data"
                      data-email-modelo="{{ env('EMAIL_PROVEEDOR_MODELOS', 'produccion@ssmetalf.mx,asistenteprod@ssmetalf.mx') }}"
                      data-email-casting="{{ env('EMAIL_PRODUCCION_SS', 'produccion@ssmetalf.mx,laboratorio@ssmetalf.mx') }}"
                      data-email-calidad="{{ env('EMAIL_CALIDAD', 'inspecciontec@grupoindsaavedra.com') }}">
                    <input type="hidden" id="env-ot" name="ot">
                    <input type="hidden" id="env-tipo" name="tipo" value="modelo">

                    <div class="form-group" style="margin-bottom: 20px;" id="div-env-destinatario">
                        <label for="env-destinatario">Notificar a Proveedor (correo electrónico):</label>
                        <input type="text" id="env-destinatario" name="destinatario" class="form-control" required>
                        <span style="font-size: 0.8em; color: #64748b; margin-top: 4px;">Separa múltiples correos con comas.</span>
                    </div>

                    <div class="form-group" style="margin-bottom: 20px;" id="div-env-destinatario-calidad">
                        <label for="env-destinatario-calidad">Notificar a Calidad (correo electrónico):</label>
                        <input type="text" id="env-destinatario-calidad" name="destinatario_calidad" class="form-control">
                        <span style="font-size: 0.8em; color: #64748b; margin-top: 4px;">Copia para Calidad (Modelos).</span>
                    </div>

                    <div class="form-group" style="margin-bottom: 20px;">
                        <label for="env-fecha-entrega">Fecha de Entrega:</label>
                        <input type="date" id="env-fecha-entrega" name="fecha_entrega" class="form-control" required>
                        <span style="font-size: 0.8em; color: #64748b; margin-top: 4px;">Indica la fecha de entrega acordada
                            para imprimirla en el reporte.</span>
                    </div>



                    <div class="form-group" style="margin-bottom: 20px;">
                        <label>Archivos de la OT disponibles para adjuntar:</label>
                        <div id="env-server-files-container"
                            style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; padding: 15px; max-height: 420px; overflow-y: auto; display: flex; flex-direction: column; gap: 15px;">
                            <div class="alm-spinner"
                                style="border-top-color: #033966; display: block; margin: 10px auto; grid-column: 1 / -1;">
                            </div>
                            <span style="text-align: center; color: #64748b; grid-column: 1 / -1;">Cargando archivos de la
                                OT...</span>
                        </div>
                    </div>

                    <div class="form-group" style="margin-bottom: 30px;">
                        <label class="custom-file-upload-label"
                            style="font-weight: 700; color: #033966; display: block; margin-bottom: 8px;">Adjuntar archivos
                            adicionales desde tu equipo:</label>
                        <div class="custom-file-dropzone">
                            <input type="file" id="env-archivos-adicionales" name="archivos_adicionales[]"
                                class="custom-file-input" multiple>
                            <div class="dropzone-content">
                                <img src="{{ asset('images/anadir.png') }}" class="dropzone-icon"
                                    style="width: 40px; height: 40px; margin-bottom: 8px; object-fit: contain;">
                                <span class="dropzone-text">Arrastra archivos aquí o haz clic para buscar</span>
                                <span class="dropzone-subtext">Soporta múltiples archivos PDF o imágenes</span>
                            </div>
                        </div>
                        <div id="env-archivos-adicionales-list"
                            style="margin-top: 10px; display: flex; flex-wrap: wrap; gap: 8px;"></div>
                    </div>

                    <div class="form-actions" style="text-align: center;">
                        <button type="submit" class="btn-save-preorden" id="btn-submit-envio"
                            style="background: #005194; box-shadow: 0 4px 15px rgba(0, 81, 148, 0.3);">
                            Enviar Correo con Adjuntos
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>



    @include('almacen.partials._modal_iniciar_casting')

    <script>
        window.almacenRoutes = {
            archivos: "{{ route('almacen.fundicion.archivos') }}",
            serve: "{{ route('almacen.fundicion.serve') }}",
            confirmarModelo: "{{ route('almacen.fundicion.confirmarModelo') }}",
            getOtData: "{{ route('almacen.fundicion.getOtData') }}",
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
    </script>

@endsection


