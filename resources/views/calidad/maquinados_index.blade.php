@extends('layouts.appMenu')

@section('head')
    <title>Calidad — Dibujos y Ayudas de Maquinados | GIS</title>
    <meta name="description"
        content="Vista de solo lectura para Calidad: Dibujos de Maquinados y Ayudas Visuales de Maquinados sincronizados automáticamente.">
    @vite([
        'resources/css/calidad_views/calidad_maquinados.css',
        'resources/js/calidad_views/calidad_maquinados.js',
    ])
@endsection

@section('background-body', 'background-image:url("' . asset('images/fondoLogin.jpg') . '")')

@section('content')

    <div class="alm-wrapper">

        {{-- ── HEADER ─────────────────────────────────────────────── --}}
        <div class="alm-header">
            <div class="alm-header-icon">
                <img src="{{ asset('images/Quality.png') }}" alt="Calidad" style="width: 90px;">
            </div>
            <div class="alm-header-text">
                <h1>Dibujos y Ayudas Visuales de Maquinados — Calidad</h1>
                <p>Consulta de documentación técnica de Maquinados. Sincronización automática cada 5 minutos. Solo lectura.</p>
            </div>
            <span class="alm-readonly-badge">Solo lectura</span>
        </div>

        {{-- ── STATS ───────────────────────────────────────────────── --}}
        <div class="alm-stats">
            <div class="alm-stat-card stat-dibujos">
                <div class="alm-stat-icon">
                    <img src="{{ asset('images/pdf-view.png') }}" alt="Dibujos" style="width: 60px;">
                </div>
                <div>
                    <div class="alm-stat-value">{{ $totalDibujos }}</div>
                    <div class="alm-stat-label">Dibujos Activos</div>
                </div>
            </div>
            <div class="alm-stat-card stat-ayudas">
                <div class="alm-stat-icon">
                    <img src="{{ asset('images/pdf-view.png') }}" alt="Ayudas" style="width: 60px;">
                </div>
                <div>
                    <div class="alm-stat-value">{{ $totalAyudas }}</div>
                    <div class="alm-stat-label">Ayudas Activas</div>
                </div>
            </div>
            <div class="alm-stat-card stat-inactivos">
                <div class="alm-stat-icon">
                    <img src="{{ asset('images/Eliminar-Carpeta.png') }}" alt="Inactivos" style="width: 60px;">
                </div>
                <div>
                    <div class="alm-stat-value">{{ $totalInactivos }}</div>
                    <div class="alm-stat-label">Docs. Inactivos</div>
                </div>
            </div>
        </div>

        {{-- ── FILTROS (reactivos, sin recarga de página) ──────────── --}}
        <div class="alm-filters-card">
            <h2>Busqueda y Filtros</h2>

            {{-- Fechas: server-side (form hidden) --}}
            <form method="GET" action="{{ route('calidad.maquinados.index') }}" id="calmaq-filter-form">
                <input type="hidden" name="ot"      id="calmaq-ot-hidden"      value="{{ $filtroOt }}">
                <input type="hidden" name="clase"   id="calmaq-clase-hidden"   value="{{ $filtroClase }}">
                <input type="hidden" name="proceso" id="calmaq-proceso-hidden" value="{{ $filtroProceso }}">

                <div class="filters">

                    {{-- Filtro OT — solo aplica a Dibujos --}}
                    <div class="filter">
                        <select id="calmaq-ot" class="select-filter">
                            <option value="">Todas las OTs</option>
                            @foreach ($listaOts as $otOpt)
                                <option value="{{ $otOpt }}" {{ $filtroOt === $otOpt ? 'selected' : '' }}>
                                    {{ $otOpt }}
                                </option>
                            @endforeach
                        </select>
                        <label for="calmaq-ot">OT <span class="filter-scope filter-scope-dibujo">Dibujos</span></label>
                    </div>

                    {{-- Filtro Clase — aplica a ambas tablas --}}
                    <div class="filter">
                        <select id="calmaq-clase" class="select-filter">
                            <option value="">Todas las Clases</option>
                            @foreach ($listaClases as $claseOpt)
                                <option value="{{ $claseOpt }}" {{ $filtroClase === $claseOpt ? 'selected' : '' }}>
                                    {{ $claseOpt }}
                                </option>
                            @endforeach
                        </select>
                        <label for="calmaq-clase">Clase <span class="filter-scope filter-scope-ambas">Dibujos + Ayudas</span></label>
                    </div>

                    {{-- Filtro Proceso — solo aplica a Ayudas --}}
                    <div class="filter">
                        <select id="calmaq-proceso" class="select-filter">
                            <option value="">Todos los Procesos</option>
                            @foreach ($listaProcesos as $procesoOpt)
                                <option value="{{ $procesoOpt }}" {{ $filtroProceso === $procesoOpt ? 'selected' : '' }}>
                                    {{ $procesoOpt }}
                                </option>
                            @endforeach
                        </select>
                        <label for="calmaq-proceso">Proceso <span class="filter-scope filter-scope-ayuda">Ayudas</span></label>
                    </div>

                    {{-- Rango de fechas (recarga el servidor para filtrar) --}}
                    <div class="filter">
                        <input id="calmaq-desde" class="input-filter" type="date" name="desde"
                               value="{{ $desde }}" onchange="this.form.submit()">
                        <label for="calmaq-desde">Desde</label>
                    </div>

                    <div class="filter">
                        <input id="calmaq-hasta" class="input-filter" type="date" name="hasta"
                               value="{{ $hasta }}" onchange="this.form.submit()">
                        <label for="calmaq-hasta">Hasta</label>
                    </div>

                    {{-- Botón limpiar (siempre visible, el JS lo activa solo si hay filtros) --}}
                    <button type="button" id="calmaq-btn-limpiar"
                            class="btns btn-clear-filters"
                            style="display:none;">
                        Limpiar Filtros
                    </button>

                </div>
            </form>
        </div>

        {{-- ══════════════════════════════════════════════════════════
             TABLA 1 — DIBUJOS DE MAQUINADOS (activos)
        ══════════════════════════════════════════════════════════ --}}
        <div class="alm-table-card">
            <div class="alm-table-header">
                <h2>Dibujos de Maquinados</h2>
                <span class="alm-results-count" id="calmaq-count-dibujos">
                    {{ $dibujos->count() }} resultado{{ $dibujos->count() !== 1 ? 's' : '' }}
                </span>
            </div>

            @if ($dibujos->isEmpty())
                <div class="alm-empty" id="calmaq-empty-dibujos">
                    <div class="alm-empty-icon">
                        <img src="{{ asset('images/noPieces.png') }}" alt="Sin resultados" style="width: 64px; opacity: 0.5;">
                    </div>
                    <p>
                        @if ($desde || $hasta)
                            No se encontraron dibujos activos en el rango de fechas indicado.
                        @else
                            Aún no se han sincronizado Dibujos de Maquinados. La tarea se ejecuta cada 5 minutos.
                        @endif
                    </p>
                </div>
            @else
                <div class="alm-table-scroll">
                    <table class="alm-table">
                        <thead>
                            <tr>
                                <th style="width: 28%">OT</th>
                                <th style="width: 22%">Clase</th>
                                <th style="width: 28%">Nombre del Archivo</th>
                                <th style="width: 12%" class="d-text-center">Fecha Archivo</th>
                                <th style="width: 10%" class="d-text-center">Accion</th>
                            </tr>
                        </thead>
                        <tbody id="calmaq-tbody-dibujos">
                            @foreach ($dibujos as $doc)
                                <tr class="calmaq-fila-doc"
                                    data-ot="{{ $doc->ot }}"
                                    data-clase="{{ $doc->clase }}">
                                    <td>
                                        <div class="alm-ot-label">{{ $doc->ot ?? '—' }}</div>
                                    </td>
                                    <td>{{ $doc->clase ?? '—' }}</td>
                                    <td title="{{ $doc->nombre_archivo }}">
                                        {{ Str::limit($doc->nombre_archivo, 42) }}
                                    </td>
                                    <td class="alm-date d-text-center">
                                        {{ $doc->fecha_archivo ? $doc->fecha_archivo->format('d/m/Y') : '—' }}
                                    </td>
                                    <td class="d-text-center">
                                        <div class="td-actions">
                                            <button class="btn-action-icon btn-ver-dibujos"
                                                    id="btn-dibujo-{{ $doc->id }}"
                                                    title="Ver archivos"
                                                    onclick="calmaqVerArchivo({{ $doc->id }})">
                                                <img src="{{ asset('images/documento.png') }}" alt="Ver">
                                                <span>Ver PDF's</span>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    <div class="alm-empty" id="calmaq-no-match-dibujos" style="display:none;">
                        <div class="alm-empty-icon">
                            <img src="{{ asset('images/noPieces.png') }}" alt="Sin resultados" style="width: 64px; opacity: 0.5;">
                        </div>
                        <p>Ningun dibujo coincide con los filtros seleccionados.</p>
                    </div>
                </div>
            @endif
        </div>

        {{-- ══════════════════════════════════════════════════════════
             TABLA 2 — AYUDAS VISUALES DE MAQUINADOS (activas)
        ══════════════════════════════════════════════════════════ --}}
        <div class="alm-table-card" style="border-color: #027a3ad4;">
            <div class="alm-table-header header-ayudas">
                <h2>Ayudas Visuales de Maquinados</h2>
                <span class="alm-results-count" id="calmaq-count-ayudas">
                    {{ $ayudas->count() }} resultado{{ $ayudas->count() !== 1 ? 's' : '' }}
                </span>
            </div>

            @if ($ayudas->isEmpty())
                <div class="alm-empty" id="calmaq-empty-ayudas">
                    <div class="alm-empty-icon">
                        <img src="{{ asset('images/noPieces.png') }}" alt="Sin resultados" style="width: 64px; opacity: 0.5;">
                    </div>
                    <p>
                        @if ($desde || $hasta)
                            No se encontraron ayudas activas en el rango de fechas indicado.
                        @else
                            Aún no se han sincronizado Ayudas Visuales de Maquinados. La tarea se ejecuta cada 5 minutos.
                        @endif
                    </p>
                </div>
            @else
                <div class="alm-table-scroll">
                    <table class="alm-table table-ayudas">
                        <thead>
                            <tr>
                                <th style="width: 25%">Clase</th>
                                <th style="width: 21%">Proceso</th>
                                <th style="width: 32%">Nombre del Archivo</th>
                                <th style="width: 12%" class="d-text-center">Fecha Archivo</th>
                                <th style="width: 12%" class="d-text-center">Accion</th>
                            </tr>
                        </thead>
                        <tbody id="calmaq-tbody-ayudas">
                            @foreach ($ayudas as $doc)
                                <tr class="calmaq-fila-doc"
                                    data-clase="{{ $doc->clase }}"
                                    data-proceso="{{ $doc->proceso }}">
                                    <td><div class="alm-clase-ayuda">{{ $doc->clase ?? '—' }}</div></td>
                                    <td>{{ $doc->proceso ?? '—' }}</td>
                                    <td title="{{ $doc->nombre_archivo }}">
                                        {{ Str::limit($doc->nombre_archivo, 42) }}
                                    </td>
                                    <td class="alm-date d-text-center">
                                        {{ $doc->fecha_archivo ? $doc->fecha_archivo->format('d/m/Y') : '—' }}
                                    </td>
                                    <td class="d-text-center">
                                        <div class="td-actions">
                                            <button class="btn-action-icon btn-ver-ayudas"
                                                    id="btn-ayuda-{{ $doc->id }}"
                                                    title="Ver archivos"
                                                    onclick="calmaqVerArchivo({{ $doc->id }})">
                                                <img src="{{ asset('images/documento.png') }}" alt="Ver">
                                                <span>Ver PDF's</span>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    <div class="alm-empty" id="calmaq-no-match-ayudas" style="display:none;">
                        <div class="alm-empty-icon">
                            <img src="{{ asset('images/noPieces.png') }}" alt="Sin resultados" style="width: 64px; opacity: 0.5;">
                        </div>
                        <p>Ninguna ayuda coincide con los filtros seleccionados.</p>
                    </div>
                </div>
            @endif
        </div>

        {{-- ══════════════════════════════════════════════════════════
             TABLA 3 — DOCUMENTOS INACTIVOS (todos los tipos)
        ══════════════════════════════════════════════════════════ --}}
        <div class="alm-table-card" style="border-color: #6c757dd4;">
            <div class="alm-table-header header-inactivos">
                <h2>Documentos Inactivos (Historico)</h2>
                <span class="alm-results-count" id="calmaq-count-inactivos">
                    {{ $inactivos->count() }} resultado{{ $inactivos->count() !== 1 ? 's' : '' }}
                </span>
            </div>

            @if ($inactivos->isEmpty())
                <div class="alm-empty">
                    <div class="alm-empty-icon">
                        <img src="{{ asset('images/ready.png') }}" alt="Sin inactivos" style="width: 64px; opacity: 0.5;">
                    </div>
                    <p>No hay documentos inactivos. Todos los registros tienen su archivo de origen activo.</p>
                </div>
            @else
                <div class="alm-table-scroll">
                    <table class="alm-table table-inactivos">
                        <thead>
                            <tr>
                                <th style="width: 14%">Tipo</th>
                                <th style="width: 14%">OT</th>
                                <th style="width: 13%">Clase</th>
                                <th style="width: 14%">Proceso</th>
                                <th style="width: 22%">Nombre del Archivo</th>
                                <th style="width: 12%" class="d-text-center">Ultima deteccion</th>
                                <th style="width: 10%" class="d-text-center">Estado</th>
                                <th style="width: 10%" class="d-text-center">Accion</th>
                            </tr>
                        </thead>
                        <tbody id="calmaq-tbody-inactivos">
                            @foreach ($inactivos as $doc)
                                <tr class="calmaq-fila-doc"
                                    data-ot="{{ $doc->ot }}"
                                    data-clase="{{ $doc->clase }}"
                                    data-proceso="{{ $doc->proceso }}">
                                    <td class="d-text-center">
                                        @if ($doc->tipo === 'dibujo')
                                            <span class="badge-tipo-dibujo">Dibujo</span>
                                        @else
                                            <span class="badge-tipo-ayuda">Ayuda</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="alm-ot-label" style="color: #6c757d;">{{ $doc->ot ?? '—' }}</div>
                                    </td>
                                    <td>{{ $doc->clase ?? '—' }}</td>
                                    <td>{{ $doc->proceso ?? '—' }}</td>
                                    <td title="{{ $doc->nombre_archivo }}">
                                        {{ Str::limit($doc->nombre_archivo, 36) }}
                                        <div class="alm-inactiva-note">
                                            Carpeta eliminada - Backup conservado
                                        </div>
                                    </td>
                                    <td class="alm-date d-text-center">
                                        {{ $doc->ultima_deteccion_at ? $doc->ultima_deteccion_at->format('d/m/Y H:i') : '—' }}
                                    </td>
                                    <td class="d-text-center">
                                        <span class="badge-status badge-inactivo">Inactivo</span>
                                    </td>
                                    <td class="d-text-center">
                                        <div class="td-actions">
                                            @if ($doc->existeEnStorage())
                                                <button class="btn-action-icon btn-ver-inactivos"
                                                        id="btn-inactivo-{{ $doc->id }}"
                                                        title="Ver archivos"
                                                        onclick="calmaqVerArchivo({{ $doc->id }})">
                                                    <img src="{{ asset('images/documento.png') }}" alt="Ver">
                                                    <span>Ver PDF's</span>
                                                </button>
                                            @else
                                                <span class="d-text-subtle" style="font-size: 0.8em;">Sin backup</span>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    <div class="alm-empty" id="calmaq-no-match-inactivos" style="display:none;">
                        <div class="alm-empty-icon">
                            <img src="{{ asset('images/noPieces.png') }}" alt="Sin resultados" style="width: 64px; opacity: 0.5;">
                        </div>
                        <p>Ningun documento inactivo coincide con los filtros seleccionados.</p>
                    </div>
                </div>
            @endif
        </div>

    </div>{{-- /.alm-wrapper --}}

    {{-- Variables de rutas para el JS --}}
    <script>
        window.calmaqRoutes = {
            serve      : "{{ route('calidad.maquinados.serve') }}",
            docs       : "{{ route('calidad.maquinados.docs') }}",
            indexBase  : "{{ route('calidad.maquinados.index') }}",
        };
    </script>

@endsection
