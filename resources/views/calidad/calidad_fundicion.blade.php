@extends('layouts.appMenu')

@section('head')
    @php
        $perfil = Auth::user()->perfil;
        $deptName = ($perfil == 1 || $perfil == 2) ? 'Administración' : ($perfil == 4 ? 'Calidad' : 'Almacén');
    @endphp
    <title>Calidad — Dibujos de Fundición | GIS</title>
    <meta name="description"
        content="Consulta histórica de dibujos de fundición enviados a Almacén y Calidad. Vista de solo lectura.">
    @vite(['resources/css/almacen_views/calidad_fundicion.css', 'resources/js/almacen_views/calidad_fundicion.js'])
@endsection

@section('background-body', 'background-image:url("' . asset('images/fondoLogin.jpg') . '")')

@section('content')

    <div class="alm-wrapper">

        {{-- ── HEADER ─────────────────────────────────────────────── --}}
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
                <h1>Calidad — Dibujos y Ayudas Visuales de Fundición</h1>
                <p>Consulta histórica de todos los dibujos y ayudas visuales enviados a Calidad. Registro
                    permanente e inmutable.</p>
            </div>
            <span class="alm-readonly-badge">Solo lectura</span>
        </div>

        <div class="alm-main-layout">
            {{-- ── COLUMNA IZQUIERDA (SIDEBAR) ───────────────────────── --}}
            <aside class="alm-sidebar">
                {{-- ── LEYENDA DE ESTADOS DE MODELO ───────────────────────── --}}
                <div class="alm-filters-card"
                    style="margin-bottom: 2em; background: rgba(255, 255, 255, 0.95); backdrop-filter: blur(10px); border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.08); position: relative; padding: 1.8em;">
                    <div
                        style="display: flex; align-items: center; gap: 12px; margin-bottom: 16px; border-bottom: 2px solid #e2e8f0; padding-bottom: 12px;">
                        <img src="{{ asset('images/Quality.png') }}" alt="Leyenda"
                            style="width: 32px; height: 32px; object-fit: contain;">
                        <h2 style="margin: 0; font-size: 1.35rem; color: #0f172a; font-weight: 700;">Guía de Estados</h2>
                    </div>
                    <p style="font-size: 0.88rem; color: #64748b; margin-top: 0; margin-bottom: 16px;">
                        Haz clic en cada icono para ver detalles del estado:
                    </p>

                    <div class="legend-grid-compact"
                        style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 12px;">
                        {{-- Nuevo --}}
                        <div class="legend-compact-item"
                            onclick="showLegendDetail(this, 'Nuevo', '{{ Auth::user()->perfil == 4 ? 'Pre-orden recibida, espera Calidad.' : 'Alerta recibida. Pendiente Almacén.' }}')"
                            style="display: flex; flex-direction: column; align-items: center; padding: 12px 8px; background: #f8fafc; border: 1.5px solid #e2e8f0; border-radius: 8px; cursor: pointer; transition: all 0.2s; min-height: 115px; justify-content: center;">
                            <span
                                style="display: flex; align-items: center; justify-content: center; width: 60px; height: 60px; border-radius: 50%; background: #f1f5f9; border: 2px solid #cbd5e1; box-shadow: 0 2px 4px rgba(0,0,0,0.04); flex-shrink: 0;">
                                <img src="{{ asset('images/Recibido.png') }}"
                                    style="width: 32px; height: 32px; object-fit: contain;">
                            </span>
                            <span
                                style="font-size: 0.82rem; font-weight: 700; color: #475569; margin-top: 10px; text-align: center; line-height: 1.1;">Nuevo</span>
                        </div>

                        {{-- Pre-Orden --}}
                        <div class="legend-compact-item"
                            onclick="showLegendDetail(this, 'Pre-Orden', 'Pre-orden generada, pendiente enviar.')"
                            style="display: flex; flex-direction: column; align-items: center; padding: 12px 8px; background: #f8fafc; border: 1.5px solid #e2e8f0; border-radius: 8px; cursor: pointer; transition: all 0.2s; min-height: 115px; justify-content: center;">
                            <span
                                style="display: flex; align-items: center; justify-content: center; width: 60px; height: 60px; border-radius: 50%; background: #eff6ff; border: 2px solid #60a5fa; box-shadow: 0 2px 4px rgba(0,0,0,0.04); flex-shrink: 0;">
                                <img src="{{ asset('images/pdf-view.png') }}"
                                    style="width: 32px; height: 32px; object-fit: contain;">
                            </span>
                            <span
                                style="font-size: 0.82rem; font-weight: 700; color: #2563eb; margin-top: 10px; text-align: center; line-height: 1.1;">Pre-Orden</span>
                        </div>

                        {{-- Correo Enviado --}}
                        @if (Auth::user()->perfil != 4)
                            <div class="legend-compact-item"
                                onclick="showLegendDetail(this, 'Correo Enviado', 'Enviada por correo. Esperando Calidad.')"
                                style="display: flex; flex-direction: column; align-items: center; padding: 12px 8px; background: #f8fafc; border: 1.5px solid #e2e8f0; border-radius: 8px; cursor: pointer; transition: all 0.2s; min-height: 115px; justify-content: center;">
                                <span
                                    style="display: flex; align-items: center; justify-content: center; width: 60px; height: 60px; border-radius: 50%; background: #e0e7ff; border: 2px solid #818cf8; box-shadow: 0 2px 4px rgba(0,0,0,0.04); flex-shrink: 0;">
                                    <img src="{{ asset('images/enviando.png') }}"
                                        style="width: 32px; height: 32px; object-fit: contain;">
                                </span>
                                <span
                                    style="font-size: 0.82rem; font-weight: 700; color: #4f46e5; margin-top: 10px; text-align: center; line-height: 1.1;">Correo
                                    Enviado</span>
                            </div>
                        @endif

                        {{-- Tengo Modelo --}}
                        <div class="legend-compact-item"
                            onclick="showLegendDetail(this, 'Tengo Modelo', 'Modelo en Almacén. Esperando Calidad.')"
                            style="display: flex; flex-direction: column; align-items: center; padding: 12px 8px; background: #f8fafc; border: 1.5px solid #e2e8f0; border-radius: 8px; cursor: pointer; transition: all 0.2s; min-height: 115px; justify-content: center;">
                            <span
                                style="display: flex; align-items: center; justify-content: center; width: 60px; height: 60px; border-radius: 50%; background: #f0f9ff; border: 2px solid #0ea5e9; box-shadow: 0 2px 4px rgba(0,0,0,0.04); flex-shrink: 0;">
                                <img src="{{ asset('images/Espera.png') }}"
                                    style="width: 32px; height: 32px; object-fit: contain;">
                            </span>
                            <span
                                style="font-size: 0.82rem; font-weight: 700; color: #0369a1; margin-top: 10px; text-align: center; line-height: 1.1;">Tengo
                                Modelo</span>
                        </div>

                        {{-- En Revisión --}}
                        <div class="legend-compact-item"
                            onclick="showLegendDetail(this, 'En Revisión', 'Calidad capturando veredicto.')"
                            style="display: flex; flex-direction: column; align-items: center; padding: 12px 8px; background: #f8fafc; border: 1.5px solid #e2e8f0; border-radius: 8px; cursor: pointer; transition: all 0.2s; min-height: 115px; justify-content: center;">
                            <span
                                style="display: flex; align-items: center; justify-content: center; width: 60px; height: 60px; border-radius: 50%; background: #fffbeb; border: 2px solid #f59e0b; box-shadow: 0 2px 4px rgba(0,0,0,0.04); flex-shrink: 0;">
                                <img src="{{ asset('images/Revisando.png') }}"
                                    style="width: 32px; height: 32px; object-fit: contain;">
                            </span>
                            <span
                                style="font-size: 0.82rem; font-weight: 700; color: #b45309; margin-top: 10px; text-align: center; line-height: 1.1;">En
                                Revisión</span>
                        </div>

                        {{-- Aprobado --}}
                        <div class="legend-compact-item"
                            onclick="showLegendDetail(this, 'Aprobado (Calidad)', 'Aprobado y liberado por Calidad.')"
                            style="display: flex; flex-direction: column; align-items: center; padding: 12px 8px; background: #f8fafc; border: 1.5px solid #e2e8f0; border-radius: 8px; cursor: pointer; transition: all 0.2s; min-height: 115px; justify-content: center;">
                            <span
                                style="display: flex; align-items: center; justify-content: center; width: 60px; height: 60px; border-radius: 50%; background: #ecfdf5; border: 2px solid #10b981; box-shadow: 0 2px 4px rgba(0,0,0,0.04); flex-shrink: 0;">
                                <img src="{{ asset('images/Quality.png') }}"
                                    style="width: 32px; height: 32px; object-fit: contain;">
                            </span>
                            <span
                                style="font-size: 0.82rem; font-weight: 700; color: #047857; margin-top: 10px; text-align: center; line-height: 1.1;">Aprobado
                                (Calidad)</span>
                        </div>

                        {{-- Rechazado --}}
                        <div class="legend-compact-item"
                            onclick="showLegendDetail(this, 'Rechazado (Calidad)', 'Rechazado por Calidad (desviaciones).')"
                            style="display: flex; flex-direction: column; align-items: center; padding: 12px 8px; background: #f8fafc; border: 1.5px solid #e2e8f0; border-radius: 8px; cursor: pointer; transition: all 0.2s; min-height: 115px; justify-content: center;">
                            <span
                                style="display: flex; align-items: center; justify-content: center; width: 60px; height: 60px; border-radius: 50%; background: #fef2f2; border: 2px solid #ef4444; box-shadow: 0 2px 4px rgba(0,0,0,0.04); flex-shrink: 0;">
                                <img src="{{ asset('images/Quality.png') }}"
                                    style="width: 32px; height: 32px; object-fit: contain;">
                            </span>
                            <span
                                style="font-size: 0.82rem; font-weight: 700; color: #b91c1c; margin-top: 10px; text-align: center; line-height: 1.1;">Rechazado
                                (Calidad)</span>
                        </div>

                        {{-- Mixto --}}
                        <div class="legend-compact-item"
                            onclick="showLegendDetail(this, 'Mixto (Calidad)', 'Liberación mixta (aprobado/rechazado).')"
                            style="display: flex; flex-direction: column; align-items: center; padding: 12px 8px; background: #f8fafc; border: 1.5px solid #e2e8f0; border-radius: 8px; cursor: pointer; transition: all 0.2s; min-height: 115px; justify-content: center;">
                            <span
                                style="display: flex; align-items: center; justify-content: center; width: 60px; height: 60px; border-radius: 50%; background: #fef9c3; border: 2px solid #eab308; box-shadow: 0 2px 4px rgba(0,0,0,0.04); flex-shrink: 0;">
                                <img src="{{ asset('images/Quality.png') }}"
                                    style="width: 32px; height: 32px; object-fit: contain;">
                            </span>
                            <span
                                style="font-size: 0.82rem; font-weight: 700; color: #854d0e; margin-top: 10px; text-align: center; line-height: 1.1;">Mixto
                                (Calidad)</span>
                        </div>

                        {{-- Casting --}}
                        <div class="legend-compact-item"
                            onclick="showLegendDetail(this, 'Casting', 'Pre-orden de casting generada.')"
                            style="display: flex; flex-direction: column; align-items: center; padding: 12px 8px; background: #f8fafc; border: 1.5px solid #e2e8f0; border-radius: 8px; cursor: pointer; transition: all 0.2s; min-height: 115px; justify-content: center;">
                            <span
                                style="display: flex; align-items: center; justify-content: center; width: 60px; height: 60px; border-radius: 50%; background: #f0fdf4; border: 2px solid #059669; box-shadow: 0 2px 4px rgba(0,0,0,0.04); flex-shrink: 0;">
                                <img src="{{ asset('images/pdf-view.png') }}"
                                    style="width: 32px; height: 32px; object-fit: contain;">
                            </span>
                            <span
                                style="font-size: 0.82rem; font-weight: 700; color: #15803d; margin-top: 10px; text-align: center; line-height: 1.1;">Casting</span>
                        </div>

                        {{-- Reproceso --}}
                        <div class="legend-compact-item"
                            onclick="showLegendDetail(this, 'Reproceso', 'Retornado para fabricar nuevo modelo.')"
                            style="display: flex; flex-direction: column; align-items: center; padding: 12px 8px; background: #f8fafc; border: 1.5px solid #e2e8f0; border-radius: 8px; cursor: pointer; transition: all 0.2s; min-height: 115px; justify-content: center;">
                            <span
                                style="display: flex; align-items: center; justify-content: center; width: 60px; height: 60px; border-radius: 50%; background: #fdf2f8; border: 2px solid #ec4899; box-shadow: 0 2px 4px rgba(0,0,0,0.04); flex-shrink: 0;">
                                <img src="{{ asset('images/Reproceso.png') }}"
                                    style="width: 32px; height: 32px; object-fit: contain;">
                            </span>
                            <span
                                style="font-size: 0.82rem; font-weight: 700; color: #be185d; margin-top: 10px; text-align: center; line-height: 1.1;">Reproceso</span>
                        </div>

                        {{-- Enviado a Proveedor --}}
                        <div class="legend-compact-item"
                            onclick="showLegendDetail(this, 'Enviado a Proveedor', 'Pre-orden de casting enviada al proveedor, proceso finalizado.')"
                            style="display: flex; flex-direction: column; align-items: center; padding: 12px 8px; background: #f8fafc; border: 1.5px solid #e2e8f0; border-radius: 8px; cursor: pointer; transition: all 0.2s; min-height: 115px; justify-content: center;">
                            <span
                                style="display: flex; align-items: center; justify-content: center; width: 60px; height: 60px; border-radius: 50%; background: #f3e8ff; border: 2px solid #9333ea; box-shadow: 0 2px 4px rgba(0,0,0,0.04); flex-shrink: 0;">
                                <img src="{{ asset('images/Proveedor.png') }}"
                                    style="width: 32px; height: 32px; object-fit: contain;">
                            </span>
                            <span
                                style="font-size: 0.82rem; font-weight: 700; color: #9333ea; margin-top: 10px; text-align: center; line-height: 1.1;">Enviado a
                                Proveedor</span>
                        </div>

                        {{-- Rechazado (Final) --}}
                        <div class="legend-compact-item"
                            onclick="showLegendDetail(this, 'Rechazado Final', 'Modelo rechazado y reproceso iniciado.')"
                            style="display: flex; flex-direction: column; align-items: center; padding: 12px 8px; background: #f8fafc; border: 1.5px solid #e2e8f0; border-radius: 8px; cursor: pointer; transition: all 0.2s; min-height: 115px; justify-content: center;">
                            <span
                                style="display: flex; align-items: center; justify-content: center; width: 60px; height: 60px; border-radius: 50%; background: #fef2f2; border: 2px solid #dc2626; box-shadow: 0 2px 4px rgba(0,0,0,0.04); flex-shrink: 0;">
                                <img src="{{ asset('images/Rechazado.png') }}"
                                    style="width: 32px; height: 32px; object-fit: contain;">
                            </span>
                            <span
                                style="font-size: 0.82rem; font-weight: 700; color: #b91c1c; margin-top: 10px; text-align: center; line-height: 1.1;">Rechazado
                                (Final)</span>
                        </div>
                    </div>

                    {{-- Mini panel de detalles --}}
                    <div id="legend-detail-card"
                        style="display: none; margin-top: 14px; padding: 12px 16px; background: #f0f9ff; border: 1.5px solid #0ea5e9; border-radius: 10px; animation: almFadeIn 0.25s ease;">
                        <div
                            style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px;">
                            <span id="legend-detail-title"
                                style="font-size: 0.95rem; font-weight: 800; color: #0369a1; text-transform: uppercase; letter-spacing: 0.5px;"></span>
                            <span onclick="closeLegendDetail()"
                                style="cursor: pointer; font-size: 1.4rem; font-weight: 700; color: #0ea5e9; line-height: 1; padding: 2px 6px;">&times;</span>
                        </div>
                        <p id="legend-detail-desc"
                            style="margin: 0; font-size: 0.9rem; color: #0369a1; line-height: 1.4; font-weight: 600;"></p>
                    </div>
                </div>

                <style>
                    .legend-compact-item {
                        transition: all 0.2s ease-in-out !important;
                    }

                    .legend-compact-item:hover {
                        transform: translateY(-2px);
                        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.08);
                        border-color: #cbd5e1 !important;
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

                <script>
                    function showLegendDetail(element, title, desc) {
                        document.querySelectorAll('.legend-compact-item').forEach(item => {
                            item.style.borderColor = '#e2e8f0';
                            item.style.background = '#f8fafc';
                        });

                        // Extract style tokens from inner circle and label text
                        const circleSpan = element.querySelector('span');
                        const labelSpan = element.querySelectorAll('span')[1];

                        const bgColor = circleSpan.style.background || window.getComputedStyle(circleSpan).backgroundColor;
                        const borderColor = circleSpan.style.borderColor || window.getComputedStyle(circleSpan).borderColor;
                        const textColor = labelSpan.style.color || window.getComputedStyle(labelSpan).color;

                        element.style.borderColor = borderColor;
                        element.style.background = bgColor;

                        const card = document.getElementById('legend-detail-card');
                        const titleSpan = document.getElementById('legend-detail-title');
                        const descP = document.getElementById('legend-detail-desc');
                        const closeBtn = card.querySelector('span[onclick="closeLegendDetail()"]');

                        titleSpan.textContent = title;
                        descP.textContent = desc;

                        // Dynamic adaptation of the detail card's colors
                        card.style.background = bgColor;
                        card.style.borderColor = borderColor;
                        titleSpan.style.color = textColor;
                        descP.style.color = textColor;
                        if (closeBtn) {
                            closeBtn.style.color = textColor;
                        }

                        card.style.display = 'block';
                    }

                    function closeLegendDetail() {
                        document.querySelectorAll('.legend-compact-item').forEach(item => {
                            item.style.borderColor = '#e2e8f0';
                            item.style.background = '#f8fafc';
                        });
                        document.getElementById('legend-detail-card').style.display = 'none';
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
                                                $liberacionesReg = \App\Models\LiberacionModeloFundicion::where('ot', $reg->ot)->get();
                                                $hasAprobados = $liberacionesReg->where('decision', 'aprobar')->isNotEmpty();

                                                $latestReproceso = null;
                                                if ($reg->rechazos_procesados) {
                                                    $latestReproceso = \App\Models\FundicionHistory::where('ot', 'LIKE', $reg->ot . '_R%')
                                                        ->orderBy('id', 'desc')
                                                        ->first();
                                                }
                                                $targetReg = ($reg->rechazos_procesados && $latestReproceso) ? $latestReproceso : $reg;

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
                                                                            'url' => route('calidad.fundicion.serve', ['ot' => $otName, 'archivo' => $relativePath, 'tipo' => 'otro']),
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
                                                                            'url' => route('calidad.fundicion.serve', ['ot' => $otName, 'archivo' => $relativePath, 'tipo' => 'ayuda']),
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
                                                                    'url' => route('calidad.fundicion.serve', ['ot' => $otName, 'archivo' => $relativePathWithPrefix, 'tipo' => 'otro', 'origin' => $origin]),
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
                                                                        'url' => route('calidad.fundicion.serve', ['ot' => $otName, 'archivo' => $relativePathWithPrefix, 'tipo' => 'otro', 'origin' => $origin]),
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
                                                                    'url' => route('calidad.fundicion.serve', ['ot' => $otName, 'archivo' => $base, 'tipo' => 'liberacion']),
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
                                                                    'url' => route('calidad.fundicion.serve', ['ot' => $otName, 'archivo' => $base, 'tipo' => 'liberacion']),
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

                                                // Aplicar filtros de visibilidad según perfil de usuario
                                                $userPerfil = Auth::user()->perfil;
                                                if ($userPerfil != 1 && $userPerfil != 2) {
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

                                                        if ($userPerfil == 4) { // Calidad
                                                            // Calidad solo ve preordenes si pre_orden_email_sent es true
                                                            if (!$isPreorden) {
                                                                $filteredOtros[] = $archivo;
                                                            } else {
                                                                $fileHistory = $relatedRecords->firstWhere('ot', $archivo['ot']);
                                                                if ($fileHistory && $fileHistory->pre_orden_email_sent) {
                                                                    $filteredOtros[] = $archivo;
                                                                }
                                                            }
                                                        } elseif ($userPerfil == 5) { // Almacén
                                                            // Almacén solo ve PDFs de Calidad si se envió la alerta (aprobado o scar alertado)
                                                            if ($isPreorden || strpos($nameLow, 'confirmacion') !== false) {
                                                                $filteredOtros[] = $archivo;
                                                            } else {
                                                                $fileHistory = $relatedRecords->firstWhere('ot', $archivo['ot']);
                                                                $status = $fileHistory ? $fileHistory->calidad_revision_status : null;
                                                                $calidadAlertaEnviada = (
                                                                    in_array($status, ['calidad_aprobado', 'calidad_rechazado', 'calidad_mixto', 'casting_aprobado']) ||
                                                                    \App\Models\ScarModelo::where('ot', '=', $archivo['ot'])->where('estatus', '=', 'alertado')->exists()
                                                                );
                                                                if ($calidadAlertaEnviada) {
                                                                    $filteredOtros[] = $archivo;
                                                                }
                                                            }
                                                        }
                                                    }
                                                    $otrosArchivos = $filteredOtros;
                                                }

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

                                                $isQualityFinalized = in_array($targetReg->calidad_revision_status, ['calidad_aprobado', 'calidad_rechazado', 'calidad_mixto', 'casting_aprobado']);
                                                $showQualityCard = (Auth::user()->perfil == 4 && $estado === 'activa' && !$isQualityFinalized);
                                                $hasFilesOrControl = ($count > 0 || $showQualityCard);

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
                                                    if ($lib->decision === 'aprobar') {
                                                        $aprobadosRaw[] = $tipo;
                                                    } elseif ($lib->decision === 'rechazar') {
                                                        $rechazadosRaw[] = $tipo;
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
                                                            } elseif (in_array($libStatus, ['aprobado', 'calidad_aprobado'])) {
                                                                $icon = 'Quality.png';
                                                                $label = 'Aprobado';
                                                                $tooltip = 'Modelo aprobado y liberado por Calidad';
                                                                $borderColor = '#10b981';
                                                                $bgColor = '#ecfdf5';
                                                                $textColor = '#047857';
                                                            } elseif (in_array($libStatus, ['rechazado', 'calidad_rechazado'])) {
                                                                $icon = 'Quality.png';
                                                                $label = 'Rechazado';
                                                                $tooltip = 'Modelo rechazado por Calidad debido a desviaciones';
                                                                $borderColor = '#ef4444';
                                                                $bgColor = '#fef2f2';
                                                                $textColor = '#b91c1c';
                                                            } elseif (in_array($libStatus, ['mixto', 'calidad_mixto'])) {
                                                                $icon = 'Quality.png';
                                                                $label = 'Mixto';
                                                                $tooltip = 'Liberación mixta por Calidad (clases aprobadas y rechazadas)';
                                                                $borderColor = '#eab308';
                                                                $bgColor = '#fef9c3';
                                                                $textColor = '#854d0e';
                                                            } elseif ($libStatus === 'pendiente') {
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
                                                                $icon = 'Rechazado.png';
                                                                $label = 'Rechazado';
                                                                $tooltip = 'Retornado hacia un nuevo ciclo de modelo (Reproceso)';
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
                                                        {{-- BLOQUE 1: Dibujos solo visibles si la alerta fue enviada desde
                                                        manage_documentation.js
                                                        --}}
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


                                                        {{-- ── ACCIONES DE CALIDAD / ESTADOS DE LIBERACION ── --}}
                                                        @if (Auth::user()->perfil == 4 && $estado === 'activa' && (in_array($targetReg->calidad_revision_status, [null, 'pendiente']) || ($targetReg->calidad_revision_status === 'rechazado' && ($targetReg->tiene_modelo || $targetReg->pre_orden_sent || $targetReg->pre_orden_email_sent))))
                                                            <div class="lib-calidad-card">
                                                                <div class="lib-calidad-card-header">
                                                                    <img src="{{ asset('images/Quality.png') }}" alt="Calidad"
                                                                        style="width:38px;height:38px;object-fit:contain;flex-shrink:0;">
                                                                    <div style="overflow:hidden;">
                                                                        <span class="lib-calidad-card-title">Acciones de Liberacion &mdash;
                                                                            Calidad</span>
                                                                        <span
                                                                            class="lib-calidad-card-ot">{{ preg_replace('/_\d{8}_\d{6}_.*/', '', $targetReg->ot) }}</span>
                                                                    </div>
                                                                </div>
                                                                <div class="lib-calidad-card-body">
                                                                    @if (in_array($targetReg->calidad_revision_status, ['rechazado', 'calidad_rechazado']))
                                                                        <div class="lib-estado-badge lib-estado-rechazado"
                                                                            style="padding: 12px 16px; width: 100%; box-sizing: border-box; display: flex; align-items: center; gap: 8px;">
                                                                            <img src="{{ asset('images/Rechazado.png') }}" alt=""
                                                                                style="width:18px;height:18px;object-fit:contain;flex-shrink:0;">
                                                                            <span>Liberacion rechazada anteriormente. Puedes revisar y volver a
                                                                                emitir
                                                                                un veredicto.</span>
                                                                        </div>
                                                                    @elseif (is_null($targetReg->calidad_revision_status) && !$targetReg->pre_orden_sent && !$targetReg->tiene_modelo)
                                                                        <div class="lib-estado-badge lib-estado-info"
                                                                            style="width: 100%; box-sizing: border-box; display: flex; align-items: center; gap: 8px;">
                                                                            En espera de que Almacén envíe la información necesaria para realizar la
                                                                            liberación.
                                                                        </div>
                                                                    @elseif (in_array($targetReg->calidad_revision_status, ['pendiente', 'calidad_pendiente']))
                                                                        <div class="lib-estado-badge lib-estado-guardado"
                                                                            style="width: 100%; box-sizing: border-box; display: flex; align-items: center; gap: 8px;">
                                                                            <img src="{{ asset('images/Guardado.png') }}" alt=""
                                                                                style="width:18px;height:18px;object-fit:contain;flex-shrink:0;">
                                                                            Datos capturados como borrador.
                                                                        </div>
                                                                    @endif
                                                                    @php
                                                                        $borradorPendiente = \App\Models\LiberacionModeloFundicion::where('ot', $targetReg->ot)
                                                                            ->where('estado', 'pendiente')
                                                                            ->first();
                                                                        $scarModelo = \App\Models\ScarModelo::where('ot', $targetReg->ot)->first();
                                                                        $reqFotos = $scarModelo && ($scarModelo->evidencia_fotos || $scarModelo->evidencia_otro);
 
                                                                        $clasesActivas = collect($targetReg->ayudas_config ?? [])
                                                                            ->filter(fn($c) => !str_contains(strtolower($c), 'opcional'))
                                                                            ->filter(function ($claseNombre) use ($targetReg) {
                                                                                $clLow = strtolower($claseNombre);
                                                                                $tipo = null;
                                                                                if (strpos($clLow, 'fondo') !== false)
                                                                                    $tipo = 'Fondo';
                                                                                elseif (strpos($clLow, 'obturador') !== false)
                                                                                    $tipo = 'Obturador';
                                                                                elseif (strpos($clLow, 'molde') !== false)
                                                                                    $tipo = 'Molde';
                                                                                elseif (strpos($clLow, 'bombillo') !== false)
                                                                                    $tipo = 'Bombillo';
 
                                                                                if ($tipo) {
                                                                                    $baseOt = preg_replace('/_R\d+$/i', '', $targetReg->ot);
                                                                                    $isAprobado = \App\Models\LiberacionModeloFundicion::where(fn($q) => $q->where('ot', '=', $baseOt)
                                                                                        ->orWhere('ot', 'LIKE', $baseOt . '_R%'))
                                                                                        ->where('ot', '!=', $targetReg->ot)
                                                                                        ->where('tipo_modelo', '=', $tipo)
                                                                                        ->where('estado', '=', 'aprobado')
                                                                                        ->exists();
                                                                                    return !$isAprobado;
                                                                                }
                                                                                return true;
                                                                            })
                                                                            ->values()
                                                                            ->toArray();

                                                                        // Determinar si todas las clases activas tienen datos guardados (como borrador pendiente)
                                                                        $todosGuardados = true;
                                                                        $contClasesConDatos = 0;
                                                                        foreach ($clasesActivas as $clName) {
                                                                            $clLow = strtolower($clName);
                                                                            $tipo = null;
                                                                            if (strpos($clLow, 'fondo') !== false)
                                                                                $tipo = 'Fondo';
                                                                            elseif (strpos($clLow, 'obturador') !== false)
                                                                                $tipo = 'Obturador';
                                                                            elseif (strpos($clLow, 'molde') !== false)
                                                                                $tipo = 'Molde';
                                                                            elseif (strpos($clLow, 'bombillo') !== false)
                                                                                $tipo = 'Bombillo';

                                                                            if ($tipo) {
                                                                                $hasData = \App\Models\LiberacionModeloFundicion::where('ot', '=', $targetReg->ot)
                                                                                    ->where('tipo_modelo', '=', $tipo)
                                                                                    ->exists();
                                                                                if (!$hasData) {
                                                                                    $todosGuardados = false;
                                                                                } else {
                                                                                    $contClasesConDatos++;
                                                                                }
                                                                            }
                                                                        }
                                                                        if (empty($clasesActivas)) {
                                                                            $todosGuardados = false;
                                                                        }

                                                                        // Determinar si hay al menos una clase con decisión de rechazo
                                                                        $hasRechazoBorrador = \App\Models\LiberacionModeloFundicion::where('ot', '=', $targetReg->ot)
                                                                            ->where('decision', '=', 'rechazar')
                                                                            ->exists();

                                                                        $hasAprobadoBorrador = \App\Models\LiberacionModeloFundicion::where('ot', '=', $targetReg->ot)
                                                                            ->where('decision', '=', 'aprobar')
                                                                            ->exists();

                                                                        $decisionGlobal = 'aprobar';
                                                                        if ($hasRechazoBorrador && $hasAprobadoBorrador) {
                                                                            $decisionGlobal = 'mixto';
                                                                        } elseif ($hasRechazoBorrador) {
                                                                            $decisionGlobal = 'rechazar';
                                                                        }

                                                                        $borradorRechazado = \App\Models\LiberacionModeloFundicion::where('ot', '=', $targetReg->ot)
                                                                            ->where('decision', '=', 'rechazar')
                                                                            ->first();

                                                                        $tiposGuardados = \App\Models\LiberacionModeloFundicion::where('ot', '=', $targetReg->ot)
                                                                            ->get(['tipo_modelo', 'decision']);
                                                                        $tiposLabel = implode(', ', $tiposGuardados->pluck('tipo_modelo')->toArray());
                                                                        $tiposAprobadosArr = $tiposGuardados->where('decision', 'aprobar')->pluck('tipo_modelo')->values()->toArray();
                                                                        $tiposRechazadosArr = $tiposGuardados->where('decision', 'rechazar')->pluck('tipo_modelo')->values()->toArray();
                                                                        $tiposAprobadosJson = json_encode($tiposAprobadosArr);
                                                                        $tiposRechazadosJson = json_encode($tiposRechazadosArr);
                                                                     @endphp
                                                                    <div class="lib-calidad-action-row">
                                                                        <h4 class="lib-calidad-card-prompt">
                                                                            @if ($todosGuardados)
                                                                                @if ($hasRechazoBorrador)
                                                                                    Borrador de rechazo guardado para esta OT. ¿Qué deseas hacer?
                                                                                @else
                                                                                    Borrador de aprobación guardado para esta OT. ¿Qué deseas hacer?
                                                                                @endif
                                                                            @elseif ($contClasesConDatos > 0)
                                                                                Proceso de liberación en curso (capturados:
                                                                                {{ $contClasesConDatos }} de
                                                                                {{ count($clasesActivas) }}).
                                                                            @elseif (in_array($targetReg->calidad_revision_status, ['rechazado', 'calidad_rechazado']))
                                                                                El modelo fue rechazado antes. ¿Quieres revisarlo de nuevo?
                                                                            @else
                                                                                ¿Qué deseas hacer con este modelo? ¿Lo apruebas o lo rechazas?
                                                                            @endif
                                                                        </h4>
                                                                        <div class="lib-calidad-card-btns">
                                                                            @if ($todosGuardados)
                                                                                <button class="btn-calidad-action btn-calidad-iniciar"
                                                                                    onclick="abrirModalLiberacionUnificado('{{ $targetReg->ot }}', {{ json_encode($clasesActivas) }}, {{ json_encode($targetReg->ayudas_config ?? []) }})"
                                                                                    title="Editar borrador del formato de liberación F-CCL-LDM">
                                                                                    <img src="{{ asset('images/editar-informacion.png') }}" alt="">
                                                                                    <span>Editar Información</span>
                                                                                </button>
                                                                            @else
                                                                                @php
                                                                                    $btnDisabled = (!$targetReg->pre_orden_email_sent && !$targetReg->tiene_modelo);
                                                                                    $btnTitle    = $btnDisabled
                                                                                        ? 'En espera de que Almacén envíe la información necesaria para realizar la liberación'
                                                                                        : ($contClasesConDatos > 0 ? 'Continuar con el proceso de liberación' : 'Iniciar el proceso de liberación');
                                                                                @endphp
                                                                                <button class="btn-calidad-action btn-calidad-iniciar"
                                                                                    @disabled($btnDisabled)
                                                                                    @if($btnDisabled) style="opacity: 0.55; cursor: not-allowed;" @endif
                                                                                    title="{{ $btnTitle }}"
                                                                                    onclick="abrirModalLiberacionUnificado('{{ $targetReg->ot }}', {{ json_encode($clasesActivas) }}, {{ json_encode($targetReg->ayudas_config ?? []) }})">
                                                                                    <img src="{{ asset('images/Liberar.png') }}" alt="">
                                                                                    <span>{{ $contClasesConDatos > 0 ? 'Continuar con el proceso de liberación' : 'Empezar con el proceso de liberación' }}</span>
                                                                                </button>
                                                                            @endif

                                                                            @if ($contClasesConDatos > 0)
                                                                                @if ($hasRechazoBorrador)
                                                                                    @if (!$scarModelo)
                                                                                        <button class="btn-calidad-action btn-calidad-borrador"
                                                                                            onclick="abrirModalScar('{{ $targetReg->ot }}', '{{ $borradorRechazado->tipo_modelo }}', '{{ $borradorRechazado->motivo_rechazo }}')"
                                                                                            title="Generar el formato de acción correctiva SCAR">
                                                                                            <img src="{{ asset('images/pdf.png') }}" alt="">
                                                                                            <span>Generar Formato SCAR</span>
                                                                                        </button>
                                                                                    @else
                                                                                        <button class="btn-calidad-action btn-calidad-email"
                                                                                            onclick="abrirModalFinalizarCalidad('{{ $targetReg->ot }}', '{{ $decisionGlobal }}', {{ $tiposAprobadosJson }}, {{ $tiposRechazadosJson }})"
                                                                                            title="Enviar alerta de calidad y notificar por correo"
                                                                                            style="background-color: #dc2626; color: white;">
                                                                                            <img src="{{ asset('images/enviando.png') }}" alt="">
                                                                                            <span>Enviar Alerta</span>
                                                                                        </button>
                                                                                    @endif
                                                                                @else
                                                                                    <button class="btn-calidad-action btn-calidad-email"
                                                                                        onclick="abrirModalFinalizarCalidad('{{ $targetReg->ot }}', '{{ $decisionGlobal }}', {{ $tiposAprobadosJson }}, {{ $tiposRechazadosJson }})"
                                                                                        title="Enviar alerta de calidad y notificar por correo"
                                                                                        style="background-color: #059669; color: white;">
                                                                                        <img src="{{ asset('images/enviando.png') }}" alt="">
                                                                                        <span>Enviar Alerta</span>
                                                                                    </button>
                                                                                @endif
                                                                            @endif
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        @elseif (Auth::user()->perfil == 4 && in_array($targetReg->calidad_revision_status, ['aprobado', 'calidad_aprobado', 'rechazado', 'calidad_rechazado', 'mixto', 'calidad_mixto', 'casting_aprobado']))
                                                                @php
                                                                    $libStatusClean = str_replace('calidad_', '', $targetReg->calidad_revision_status);
                                                                @endphp
                                                                @if (in_array($targetReg->calidad_revision_status, ['aprobado', 'calidad_aprobado', 'rechazado', 'calidad_rechazado', 'mixto', 'calidad_mixto']))
                                                                    @php
                                                                        $liberaciones = \App\Models\LiberacionModeloFundicion::where('ot', $targetReg->ot)->get();
                                                                        $aprobados = $liberaciones->where('decision', 'aprobar')->pluck('tipo_modelo')->toArray();
                                                                        $rechazados = $liberaciones->where('decision', 'rechazar')->pluck('tipo_modelo')->toArray();
                                                                        $decisionFinal = ($libStatusClean === 'aprobado') ? 'aprobar' : (($libStatusClean === 'rechazado') ? 'rechazar' : 'mixto');
                                                                        $tiposAprobadosJson = json_encode(array_values($aprobados));
                                                                        $tiposRechazadosJson = json_encode(array_values($rechazados));
                                                                    @endphp
                                                                    <div class="lib-calidad-card"
                                                                        id="control-calidad-finalizado-{{ md5($targetReg->ot) }}"
                                                                        style="margin-top: 20px;">
                                                                        <div class="lib-calidad-card-header"
                                                                            style="background: linear-gradient(135deg, #475569, #334155); border-bottom: 2px solid rgba(71, 85, 105, 0.5);">
                                                                            <img src="{{ asset('images/Quality.png') }}" alt="Calidad"
                                                                                style="width:38px;height:38px;object-fit:contain;flex-shrink:0;">
                                                                            <div style="overflow:hidden;">
                                                                                <span class="lib-calidad-card-title" style="color: #ffffff;">Control de
                                                                                    Modelos
                                                                                    &mdash; Calidad</span>
                                                                                <span class="lib-calidad-card-ot"
                                                                                    style="color: #cbd5e1;">{{ preg_replace('/_\d{8}_\d{6}_.*/', '', $targetReg->ot) }}</span>
                                                                            </div>
                                                                        </div>
                                                                        <div class="lib-calidad-card-body">
                                                                            <div class="lib-calidad-action-row">
                                                                                <h4 class="lib-calidad-card-prompt">
                                                                                    @php
                                                                                        $isDraft = in_array($targetReg->calidad_revision_status, ['aprobado', 'rechazado', 'mixto']);
                                                                                    @endphp
                                                                                    @if ($isDraft)
                                                                                        @if ($libStatusClean === 'aprobado')
                                                                                            Borrador de Liberación (Aprobado) guardado. Se aprobarán: <strong style="color: #16a34a;">{{ implode(', ', $aprobados) }}</strong>. Por favor, procede a enviar la alerta oficial para iniciar el casting.
                                                                                        @elseif ($libStatusClean === 'rechazado')
                                                                                            Borrador de Liberación (Rechazado) guardado. Se rechazarán: <strong style="color: #dc2626;">{{ implode(', ', $rechazados) }}</strong>. Por favor, procede a enviar la alerta oficial y el SCAR correspondiente.
                                                                                        @elseif ($libStatusClean === 'mixto')
                                                                                            Borrador de Liberación (Mixto) guardado. Se aprobarán: <strong style="color: #16a34a;">{{ implode(', ', $aprobados) }}</strong> y se rechazarán: <strong style="color: #dc2626;">{{ implode(', ', $rechazados) }}</strong>. Por favor, procede a enviar las alertas oficiales.
                                                                                        @endif
                                                                                    @else
                                                                                        @if ($targetReg->calidad_revision_status === 'casting_aprobado')
                                                                                            Proceso Finalizado: La pre-orden de casting ha sido enviada al proveedor.
                                                                                        @elseif ($libStatusClean === 'aprobado')
                                                                                            Proceso Finalizado (Aprobado): La alerta ya fue enviada. Se aprobaron: <strong style="color: #16a34a;">{{ implode(', ', $aprobados) }}</strong>.
                                                                                        @elseif ($libStatusClean === 'rechazado')
                                                                                            Proceso Finalizado (Rechazado): La alerta ya fue enviada. Se rechazaron: <strong style="color: #dc2626;">{{ implode(', ', $rechazados) }}</strong>.
                                                                                        @elseif ($libStatusClean === 'mixto')
                                                                                            Proceso Finalizado (Mixto): La alerta ya fue enviada. Se aprobaron: <strong style="color: #16a34a;">{{ implode(', ', $aprobados) }}</strong> | Se rechazaron: <strong style="color: #dc2626;">{{ implode(', ', $rechazados) }}</strong>.
                                                                                        @endif
                                                                                    @endif
                                                                                </h4>
                                                                                <div class="lib-calidad-card-btns" style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
                                                                                    @if ($isDraft)
                                                                                        @php
                                                                                            $clasesConfig = collect($targetReg->ayudas_config ?? [])
                                                                                                ->filter(fn($c) => !str_contains(strtolower($c), 'opcional'))
                                                                                                ->values()
                                                                                                ->toArray();
                                                                                        @endphp
                                                                                        <button class="btn-calidad-action btn-calidad-iniciar"
                                                                                            onclick="abrirModalLiberacionUnificado('{{ $targetReg->ot }}', {{ json_encode($clasesConfig) }}, {{ json_encode($targetReg->ayudas_config ?? []) }})"
                                                                                            title="Editar datos de liberación del modelo"
                                                                                            style="background-color: #4f46e5; color: white;">
                                                                                            <img src="{{ asset('images/editar-informacion.png') }}" alt="">
                                                                                            <span>Editar Información</span>
                                                                                        </button>

                                                                                        <button class="btn-calidad-action btn-calidad-email"
                                                                                            onclick="abrirModalFinalizarCalidad('{{ $targetReg->ot }}', '{{ $decisionFinal }}', {{ $tiposAprobadosJson }}, {{ $tiposRechazadosJson }})"
                                                                                            title="Enviar alerta de calidad de forma manual"
                                                                                            style="background-color: #0ea5e9; color: white;">
                                                                                            <img src="{{ asset('images/enviando.png') }}" alt="">
                                                                                            <span>Enviar Alerta</span>
                                                                                        </button>
                                                                                    @else
                                                                                        <span style="color: #475569; font-weight: 600; display: inline-flex; align-items: center; gap: 6px;">
                                                                                            <img src="{{ asset('images/ready.png') }}" style="width: 18px; height: 18px;" alt="">
                                                                                            Acciones finalizadas y alerta enviada.
                                                                                        </span>
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
    </div>{{-- /.alm-wrapper --}}

    {{-- ── MINI-MODAL: CONFIRMAR MODELO CON DOCUMENTOS OBLIGATORIOS ── --}}

    {{-- ── MODAL: ENVIAR ALERTA DE LIBERACION (APROBADO/RECHAZADO) ── --}}
    <div id="modalEnviarAlertaLiberacion" class="alm-modal" role="dialog" aria-modal="true">
        <div class="alm-modal-content" id="alerta-lib-modal-content"
            style="max-width: 1500px; width: 96vw; border-radius: 20px;">
            <div class="alm-modal-header" id="alerta-lib-header"
                style="padding: 2.5em 3em 2.2em; border-top-left-radius: 18px; border-top-right-radius: 18px;">
                <div class="div-cerrar">
                    @include('almacen.partials._btn_cerrar', ['onclick' => 'cerrarModalEnviarAlertaLiberacion()'])
                </div>
                <h3 id="alerta-lib-title"
                    style="font-size: 2.2em; margin: 0; font-family:'Poppins', sans-serif; font-weight: 700; color: #fff;">
                    Enviar Alerta de Liberación</h3>
                <p id="alerta-lib-subtitle" class="lib-modal-subtitle"
                    style="color: #bae6fd; font-size: 1.15em; margin-top: 8px; margin-bottom: 0; font-family:'Poppins', sans-serif; font-weight: 500;">
                </p>
            </div>
            <div class="alm-modal-body" style="padding: 3em 3.5em;">
                <form id="formEnviarAlertaLiberacion" enctype="multipart/form-data" novalidate>
                    @csrf
                    <input type="hidden" id="al-ot" name="ot">
                    <input type="hidden" id="al-decision" name="decision">
                    <input type="hidden" id="al-tipo-modelo" name="tipo_modelo">

                    <p style="margin-bottom:28px; font-family:'Poppins', sans-serif; font-weight:500; line-height:1.6; color:#334155; font-size: 1.3em;"
                        id="al-prompt-text"></p>

                    {{-- Destinatario(s) --}}
                    <div class="form-group" style="margin-bottom: 24px;">
                        <label for="al-destinatario"
                            style="font-size: 1.2em; font-weight: 700; color: #334155; display: block; margin-bottom: 10px; font-family:'Poppins', sans-serif;">Destinatario(s):</label>
                        <input type="text" id="al-destinatario" name="destinatario" class="form-control" required
                            value="inspecciontec@grupoindsaavedra.com"
                            style="font-size: 1.15em; padding: 14px 20px; height: auto; border-radius: 10px; font-family:'Poppins', sans-serif;">
                        <span style="font-size: 0.9em; color: #64748b; margin-top: 8px; display: block;">Separa múltiples
                            correos usando comas (,).</span>
                    </div>

                    {{-- FECHA --}}
                    <div class="form-group" style="margin-bottom: 28px;">
                        <label id="al-fecha-label" for="al-fecha"
                            style="font-weight:700; color:#334155; display:block; margin-bottom:10px; font-family:'Poppins', sans-serif; font-size:1.2em;">
                            Fecha de Emisión / Entrega <span style="color:#9c0300;">*</span>
                        </label>
                        <input type="date" id="al-fecha" name="fecha" class="form-control" required
                            style="font-family:'Poppins', sans-serif; font-size: 1.15em; padding: 14px 20px; height: auto; border-radius: 10px;">
                    </div>

                    {{-- ═══ LAYOUT DUAL: Aprobados (izq) + Rechazados (der) si hay ambos, o uno solo al 100% ═══ --}}
                    <div id="al-dual-layout" style="display: flex; gap: 32px; align-items: stretch; margin-top: 32px;">

                        {{-- ── COLUMNA APROBADOS ── --}}
                        <div id="al-col-aprobados" style="flex: 1; width: 100%; display: none;">
                            <div
                                style="border: 2.5px solid #059669; border-radius: 18px; overflow: hidden; box-shadow: 0 8px 25px rgba(5,150,105,0.12);">
                                {{-- Header Aprobados --}}
                                <div
                                    style="background: linear-gradient(135deg, #059669, #047857); padding: 20px 24px; display: flex; align-items: center; gap: 14px;">
                                    <img src="{{ asset('images/Aprobado.png') }}"
                                        style="width:36px;height:36px;object-fit:contain;" alt="">
                                    <div>
                                        <div
                                            style="font-weight:800; font-size:1.35em; color:#fff; font-family:'Poppins',sans-serif;">
                                            Documentos Aprobados</div>
                                        <div id="al-aprobados-tipos-label"
                                            style="font-size:0.95em; color:#a7f3d0; font-family:'Poppins',sans-serif;">—
                                        </div>
                                    </div>
                                </div>
                                <div style="padding: 24px;">
                                    {{-- Archivos del servidor — Aprobados --}}
                                    <label
                                        style="font-weight:700; color:#059669; font-size:1.15em; margin-bottom:12px; display:block; font-family:'Poppins',sans-serif;">Archivos
                                        en servidor (selecciona los que deseas adjuntar):</label>
                                    <div id="al-server-files-aprobados"
                                        style="background:#f0fdf4; border:1.8px solid #bbf7d0; border-radius:14px; padding:20px; max-height:280px; overflow-y:auto; display:grid; grid-template-columns:repeat(auto-fill,minmax(180px,1fr)); gap:12px; justify-items:center;">
                                        <div
                                            style="text-align:center; color:#64748b; grid-column:1/-1; padding:12px; font-style:italic; font-size:0.95em; font-family:'Poppins',sans-serif;">
                                            Cargando archivos...</div>
                                    </div>

                                    {{-- Upload Firmados — por Modelo (Aprobados) --}}
                                    <div style="margin-top:24px;">
                                        <label
                                            style="font-weight:700; color:#059669; font-size:1.15em; display:block; margin-bottom:10px; font-family:'Poppins',sans-serif;">
                                            Subir Formato F-CCL-LDM Firmado (por modelo):
                                        </label>
                                        <p
                                            style="font-size:0.9em; color:#64748b; margin-bottom:14px; font-family:'Poppins',sans-serif; line-height: 1.5;">
                                            Selecciona el tipo de modelo y luego sube el formato de liberación
                                            <strong>aprobado y firmado</strong> correspondiente.
                                        </p>
                                        <div id="al-upload-aprobados-rows"
                                            style="display:flex; flex-direction:column; gap:14px;"></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- ── COLUMNA RECHAZADOS ── --}}
                        <div id="al-col-rechazados" style="flex: 1; width: 100%; display: none;">
                            <div
                                style="border: 2.5px solid #dc2626; border-radius: 18px; overflow: hidden; box-shadow: 0 8px 25px rgba(220,38,38,0.12);">
                                {{-- Header Rechazados --}}
                                <div
                                    style="background: linear-gradient(135deg, #dc2626, #b91c1c); padding: 20px 24px; display: flex; align-items: center; gap: 14px;">
                                    <img src="{{ asset('images/Rechazado.png') }}"
                                        style="width:36px;height:36px;object-fit:contain;" alt="">
                                    <div>
                                        <div
                                            style="font-weight:800; font-size:1.35em; color:#fff; font-family:'Poppins',sans-serif;">
                                            Documentos Rechazados</div>
                                        <div id="al-rechazados-tipos-label"
                                            style="font-size:0.95em; color:#fecaca; font-family:'Poppins',sans-serif;">—
                                        </div>
                                    </div>
                                </div>
                                <div style="padding: 24px;">
                                    {{-- Archivos del servidor — Rechazados --}}
                                    <label
                                        style="font-weight:700; color:#dc2626; font-size:1.15em; margin-bottom:12px; display:block; font-family:'Poppins',sans-serif;">Archivos
                                        en servidor (selecciona los que deseas adjuntar):</label>
                                    <div id="al-server-files-rechazados"
                                        style="background:#fef2f2; border:1.8px solid #fecaca; border-radius:14px; padding:20px; max-height:280px; overflow-y:auto; display:grid; grid-template-columns:repeat(auto-fill,minmax(180px,1fr)); gap:12px; justify-items:center;">
                                        <div
                                            style="text-align:center; color:#64748b; grid-column:1/-1; padding:12px; font-style:italic; font-size:0.95em; font-family:'Poppins',sans-serif;">
                                            Cargando archivos...</div>
                                    </div>

                                    {{-- Upload Firmados — por Modelo (Rechazados) --}}
                                    <div style="margin-top:24px;">
                                        <label
                                            style="font-weight:700; color:#dc2626; font-size:1.15em; display:block; margin-bottom:10px; font-family:'Poppins',sans-serif;">
                                            Subir Formato F-CCL-LDM de Rechazo + SCAR Firmado (por modelo):
                                        </label>
                                        <p
                                            style="font-size:0.9em; color:#64748b; margin-bottom:14px; font-family:'Poppins',sans-serif; line-height: 1.5;">
                                            Selecciona el tipo de modelo y luego sube el <strong>formato de liberación
                                                rechazado</strong> y el <strong>SCAR firmado</strong> correspondiente.</p>
                                        <div id="al-upload-rechazados-rows"
                                            style="display:flex; flex-direction:column; gap:14px;"></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>{{-- fin dual-layout --}}

                    <div class="form-actions" style="text-align: center; margin-top: 40px; margin-bottom: 12px;">
                        <button type="submit" class="btn-save-preorden" id="btn-submit-alerta-liberacion"
                            style="font-size:1.2em; padding:15px 32px; border-radius:10px; font-family:'Poppins',sans-serif; font-weight: 700; height: auto;">
                            Enviar Alerta de Liberación
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- ── MODAL: PRE-ORDEN PARA FABRICAR MODELOS ──────────────────── --}}

    {{-- ── MODAL: PRE-ORDEN PARA FABRICAR CASTING (DOUBLE MODAL TABS) ── --}}

    {{-- ── MODAL: FINALIZAR PROCESO DE CALIDAD (CORREO Y FECHA) ── --}}
    <div id="modalFinalizarCalidad" class="alm-modal" role="dialog" aria-modal="true">
        <div class="alm-modal-content" id="finalizar-calidad-modal-content"
            style="max-width: 1500px; width: 95vw; border-radius: 20px; overflow: hidden;">
            <div class="alm-modal-header" id="finalizar-calidad-header"
                style="padding: 1.5em 5.5em 1.2em 2.2em; border-top-left-radius: 18px; border-top-right-radius: 18px; position: relative;">
                <div class="div-cerrar">
                    <button type="button" class="btn-cerrar" onclick="cerrarModalFinalizarCalidad()"
                        style="position: absolute; top: 18px; right: 22px; background: rgba(255, 255, 255, 0.18); border: 1.5px solid rgba(255, 255, 255, 0.45); border-radius: 50%; width: 36px; height: 36px; display: inline-flex; align-items: center; justify-content: center; cursor: pointer; transition: all 0.2s ease;">
                        <img class="img-cerrar" src="{{ asset('images/cerrar.png') }}"
                            style="width: 12px; height: 12px; filter: brightness(0) invert(1);">
                    </button>
                </div>
                <h3 id="finalizar-calidad-title"
                    style="font-size: 1.5em; margin: 0; font-family:'Poppins', sans-serif; font-weight: 700; color: #fff; line-height: 1.3;">
                    Finalizar Proceso de Calidad</h3>
                <p id="finalizar-calidad-subtitle" class="lib-modal-subtitle"
                    style="color: #ffffff; font-size: 0.95em; margin-top: 5px; margin-bottom: 0; font-family:'Poppins', sans-serif; font-weight: 500; opacity: 0.9;">
                </p>
            </div>
            <div class="alm-modal-body"
                style="padding: 2.2em 2.5em; background: #fafafa; font-family: 'Poppins', sans-serif;">
                <form id="formFinalizarCalidad" enctype="multipart/form-data" novalidate>
                    @csrf
                    <input type="hidden" id="fc-ot" name="ot">
                    <input type="hidden" id="fc-decision" name="decision">
                    <input type="hidden" id="fc-tipo-modelo" name="tipo_modelo">
                    <input type="hidden" id="fc-tipos-aprobados" name="tipos_aprobados">
                    <input type="hidden" id="fc-tipos-rechazados" name="tipos_rechazados">

                    <div id="fc-prompt-text" style="margin-bottom: 24px;"></div>

                    {{-- Destinatario(s) --}}
                    <div class="form-group" style="margin-bottom: 20px;">
                        <label for="fc-destinatario"
                            style="font-size: 1.1em; font-weight: 700; color: #334155; display: block; margin-bottom: 8px; font-family:'Poppins', sans-serif;">Destinatario(s)
                            <span style="color:#dc2626;">*</span></label>
                        <input type="text" id="fc-destinatario" name="destinatario" class="form-control" required
                            value="inspecciontec@grupoindsaavedra.com"
                            style="font-size: 1.1em; padding: 12px 18px; height: auto; border-radius: 10px; font-family:'Poppins', sans-serif;">
                        <span style="font-size: 0.85em; color: #64748b; margin-top: 6px; display: block;">Separa múltiples
                            correos usando comas (,).</span>
                    </div>

                    {{-- FECHA (OBLIGATORIA) --}}
                    <div class="form-group" style="margin-bottom: 24px;">
                        <label id="fc-fecha-label" for="fc-fecha"
                            style="font-weight:700; color:#334155; display:block; margin-bottom:8px; font-family:'Poppins', sans-serif; font-size:1.1em;">
                            Fecha de Finalización <span style="color:#dc2626;">*</span>
                        </label>
                        <input type="date" id="fc-fecha" name="fecha" class="form-control" required
                            style="font-family:'Poppins', sans-serif; font-size: 1.1em; padding: 12px 18px; height: auto; border-radius: 10px;">
                    </div>

                    {{-- Listado de Documentos del Servidor --}}
                    <div class="form-group" style="margin-bottom: 24px;">
                        <label
                            style="font-weight: 700; color: #334155; display: block; margin-bottom: 8px; font-family:'Poppins', sans-serif;">Archivos
                            de liberación en servidor a adjuntar:</label>
                        <div id="fc-server-files-container"
                            style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; padding: 15px; max-height: 420px; overflow-y: auto; display: flex; flex-direction: column; gap: 15px;">
                            <div class="alm-spinner" id="fc-server-spinner"
                                style="border-top-color: #0284c7; display: block; margin: 10px auto; grid-column:1/-1;">
                            </div>
                            <span style="text-align:center; color:#64748b; grid-column:1/-1;">Cargando archivos de la
                                OT...</span>
                        </div>
                    </div>

                    <div class="form-actions" style="text-align: center; margin-top: 30px; margin-bottom: 10px;">
                        <button type="submit" class="btn-save-preorden" id="btn-submit-finalizar-calidad"
                            style="font-size:1.15em; padding:14px 30px; border-radius:10px; font-family:'Poppins',sans-serif; font-weight: 700; height: auto;">
                            Finalizar y Enviar Correo
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- ── MODAL: ENVIAR PRE-ORDEN POR CORREO CON ADJUNTOS (FASE 2) ── --}}


    {{-- ── MODAL: ENVIAR ALERTA SCAR (Paso 2) ── --}}
    <div id="modalEnviarScar" class="alm-modal" role="dialog" aria-modal="true">
        <div class="alm-modal-content lib-modal-content" style="max-width: 1100px;">
            <div class="alm-modal-header lib-modal-header lib-modal-header-rechazo" id="env-scar-header">
                <div class="div-cerrar">
                    <button type="button" class="btn-cerrar" onclick="cerrarModalEnviarScar()">
                        <img class="img-cerrar" src="{{ asset('images/cerrar.png') }}" alt="Cerrar">
                    </button>
                </div>
                <h3 style="color: #ffffff;">Enviar Alerta SCAR al Proveedor</h3>
                <p id="env-scar-modal-subtitle" class="lib-modal-subtitle"
                    style="color: #ffd1d1; font-size: 0.9em; margin-top: 4px; margin-bottom: 0;"></p>
            </div>
            <div class="alm-modal-body lib-modal-body">
                <form id="formEnviarScar" enctype="multipart/form-data" autocomplete="off">
                    <input type="hidden" id="env-scar-ot" name="ot">

                    {{-- Destinatario --}}
                    <div class="form-group" style="margin-bottom: 16px;">
                        <label for="env-scar-destinatario"
                            style="font-weight: 700; color: #334155; display: block; margin-bottom: 4px;">
                            Destinatario(s) (separados por coma):
                        </label>
                        <input type="text" id="env-scar-destinatario" name="destinatario" class="form-control" required
                            value="produccion@ssmetalf.mx, laboratorio@ssmetalf.mx, alejandross@grupoindsaavedra.com, analilia@grupoindsaavedra.com, blanca@grupoindsaavedra.com, juanss@grupoindsaavedra.com, abraham@grupoindsaavedra.com, inspecciontec@grupoindsaavedra.com, requisicionestec@grupoindsaavedra.com, auxadmtec@grupoindsaavedra.com, producciontec@grupoindsaavedra.com">
                    </div>

                    {{-- Fecha Compromiso --}}
                    <div class="form-group" style="margin-bottom: 16px;">
                        <label for="env-scar-fecha-compromiso"
                            style="font-weight: 700; color: #334155; display: block; margin-bottom: 4px;">
                            Fecha Compromiso de Devolución (Obligatoria):
                        </label>
                        <input type="date" id="env-scar-fecha-compromiso" name="fecha_compromiso" class="form-control"
                            required>
                    </div>

                    {{-- SCAR Firmado --}}
                    <div class="form-group" style="margin-bottom: 20px;">
                        <label for="env-scar-pdf-firmado"
                            style="font-weight: 700; color: #9c0300; display: block; margin-bottom: 8px;">Subir SCAR Firmado
                            Físicamente (PDF Obligatorio): <span style="color:#9c0300;">*</span></label>
                        <div class="custom-file-dropzone"
                            style="border: 2px dashed #dc2626; background: #fef2f2; min-height: 80px; position: relative; border-radius: 12px; display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 12px; cursor: pointer;">
                            <input type="file" id="env-scar-pdf-firmado" name="pdf_firmado" class="custom-file-input"
                                accept=".pdf" required
                                style="position: absolute; width:100%; height:100%; opacity:0; cursor:pointer;"
                                onchange="handleAlertaFileChange(this, 'env-scar-pdf-text', 'pdf')">
                            <div class="dropzone-content"
                                style="display: flex; flex-direction: column; align-items: center; pointer-events: none;">
                                <img src="{{ asset('images/pdf.png') }}"
                                    style="width: 24px; height: 24px; margin-bottom: 4px;" alt="PDF">
                                <span id="env-scar-pdf-text"
                                    style="font-weight: 700; color: #dc2626; font-size: 0.85em; text-align: center; font-family:'Poppins', sans-serif;">Seleccionar
                                    o arrastrar PDF *</span>
                                <span
                                    style="font-size: 0.7em; color: #64748b; margin-top: 2px; font-family:'Poppins', sans-serif;">Solo
                                    archivos PDF</span>
                            </div>
                        </div>
                    </div>

                    {{-- Archivos de la OT disponibles --}}
                    <div class="form-group" style="margin-bottom: 20px;">
                        <label>Archivos de la OT disponibles para adjuntar:</label>
                        <div id="env-scar-server-files-container"
                            style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; padding: 15px; max-height: 420px; overflow-y: auto; display: flex; flex-direction: column; gap: 15px;">
                            <div class="alm-spinner"
                                style="border-top-color: #9c0300; display: block; margin: 10px auto; grid-column: 1 / -1;">
                            </div>
                            <span style="text-align: center; color: #64748b; grid-column: 1 / -1;">Cargando archivos de la
                                OT...</span>
                        </div>
                    </div>

                    {{-- Evidencia adicional --}}
                    <div class="form-group" style="margin-bottom: 30px;">
                        <label class="custom-file-upload-label"
                            style="font-weight: 700; color: #9c0300; display: block; margin-bottom: 8px;">Subir Evidencia
                            Adicional al Envío (Imágenes o PDFs adicionales):</label>
                        <div class="custom-file-dropzone"
                            style="border: 2px dashed #9c0300; background: #fff8f8; min-height: 80px; position: relative; border-radius: 12px; display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 12px; cursor: pointer;">
                            <input type="file" id="env-scar-archivos-adicionales" name="archivos_adicionales[]"
                                class="custom-file-input" multiple
                                style="position: absolute; width:100%; height:100%; opacity:0; cursor:pointer;">
                            <div class="dropzone-content">
                                <img src="{{ asset('images/anadir.png') }}" class="dropzone-icon"
                                    style="width: 24px; height: 24px; margin-bottom: 4px; object-fit: contain;">
                                <span class="dropzone-text"
                                    style="font-weight: 700; color: #9c0300; font-size: 0.85em; text-align: center; font-family:'Poppins', sans-serif;">Arrastra
                                    archivos aquí o haz clic para buscar</span>
                                <span class="dropzone-subtext"
                                    style="font-size: 0.7em; color: #64748b; margin-top: 2px; font-family:'Poppins', sans-serif;">Imágenes,
                                    PDF, ZIP</span>
                            </div>
                        </div>
                        <div id="env-scar-archivos-adicionales-list"
                            style="margin-top: 10px; display: flex; flex-wrap: wrap; gap: 8px;"></div>
                    </div>

                    {{-- Boton de Envio --}}
                    <div class="form-actions" style="text-align: center; margin-top: 20px;">
                        <button type="submit" class="btn-lib-send"
                            style="background: linear-gradient(135deg, #9c0300, #7a0200); box-shadow: 0 4px 15px rgba(156, 3, 0, 0.3);">
                            Enviar Alerta SCAR al Proveedor
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- ── MODAL: LIBERACIÓN DE MODELOS (Calidad) ──────────────────── --}}
    @include('almacen.partials._modal_liberacion_modelos')

    {{-- ── MODAL: SCAR (Solicitud de Acción Correctiva de Rechazo) ─── --}}
    @include('almacen.partials._modal_scar')

    {{-- ── MODAL: INICIAR CASTING / GESTION VEREDICTO (Almacén) ────── --}}

    <script>
        window.almacenRoutes = {
            archivos: "{{ route('calidad.fundicion.archivos') }}",
            serve: "{{ route('calidad.fundicion.serve') }}",
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
            deleteFile: "{{ route('calidad.fundicion.deleteFile') }}",
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
