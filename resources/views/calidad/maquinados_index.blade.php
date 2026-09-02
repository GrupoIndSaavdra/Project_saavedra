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
                <img src="{{ asset('images/Quality.png') }}" alt="Calidad" class="cal-maq-width-90px">
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
                    <img src="{{ asset('images/pdf-view.png') }}" alt="Dibujos" class="cal-maq-width-60px">
                </div>
                <div>
                    <div class="alm-stat-value">{{ $totalDibujos }}</div>
                    <div class="alm-stat-label">Dibujos Activos</div>
                </div>
            </div>
            <div class="alm-stat-card stat-ayudas">
                <div class="alm-stat-icon">
                    <img src="{{ asset('images/pdf-view.png') }}" alt="Ayudas" class="cal-maq-width-60px">
                </div>
                <div>
                    <div class="alm-stat-value">{{ $totalAyudas }}</div>
                    <div class="alm-stat-label">Ayudas Activas</div>
                </div>
            </div>
            <div class="alm-stat-card stat-inactivos">
                <div class="alm-stat-icon">
                    <img src="{{ asset('images/Eliminar-Carpeta.png') }}" alt="Inactivos" class="cal-maq-width-60px">
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

                    <div class="filters-row">
                        {{-- Filtro OT — solo aplica a Dibujos --}}
                        <div class="filter">
                            <select id="calmaq-ot" class="select-filter">
                                <option value="">Todas las OTs</option>
                                @foreach ($listaOts as $otOpt)
                                    <option value="{{ $otOpt }}" {{ $filtroOt === $otOpt ? 'selected' : '' }}>
                                        {{ preg_replace('/_\d{8}_\d{6}_.*/', '', $otOpt) }}
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
                    </div>

                    <div class="filters-actions">
                        {{-- Botón limpiar (siempre visible, el JS lo activa solo si hay filtros) --}}
                        <button type="button" id="calmaq-btn-limpiar" class="btns btn-clear-filters cal-maq-display-none">
                            Limpiar Filtros
                        </button>
                    </div>

                </div>
            </form>
        </div>

        {{-- ══════════════════════════════════════════════════════════
             TABLA 1 — DIBUJOS DE MAQUINADOS (activos)
        ══════════════════════════════════════════════════════════ --}}
        @php
            $dibujosAgrupados = $dibujos->groupBy(fn($d) => ($d->ot ?? '—') . '|' . ($d->clase ?? '—'));
        @endphp

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
                        <img src="{{ asset('images/noPieces.png') }}" alt="Sin resultados" class="cal-maq-width-64px cal-maq-opacity-0-5">
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
                                <th class="cal-maq-width-35pct">OT</th>
                                <th class="cal-maq-width-30pct">Clase</th>
                                <th class="cal-maq-width-15pct d-text-center">Archivos</th>
                                <th class="cal-maq-width-20pct d-text-center">Accion</th>
                            </tr>
                        </thead>
                        <tbody id="calmaq-tbody-dibujos">
                            @foreach ($dibujosAgrupados as $key => $docs)
                                @php
                                    [$ot, $clase] = explode('|', $key);
                                @endphp
                                <tr class="calmaq-fila-doc row-dibujo"
                                    data-ot="{{ $ot }}"
                                    data-clase="{{ $clase }}">
                                    <td>
                                        <div class="alm-ot-label">{{ preg_replace('/_\d{8}_\d{6}_.*/', '', $ot) }}</div>
                                    </td>
                                    <td><div class="alm-clase-label">{{ $clase }}</div></td>
                                    <td class="d-text-center">
                                        <span class="badge-pdf-count">{{ $docs->count() }}</span>
                                    </td>
                                    <td class="d-text-center">
                                        <button class="btn-toggle-files"
                                                data-target="files-dibujo-{{ $loop->index }}"
                                                aria-expanded="false">
                                            Ver Archivos
                                        </button>
                                    </td>
                                </tr>
                                {{-- Fila desplegable de archivos --}}
                                <tr class="alm-files-row" id="files-dibujo-{{ $loop->index }}">
                                    <td colspan="4">
                                        <div class="alm-files-inner">
                                            <h4 class="cal-maq-margin-top-15px cal-maq-margin-bottom-10px cal-maq-color-005194 cal-maq-border-bottom-2px-solid-005194 cal-maq-padding-bottom-5px">
                                                Dibujos de Maquinados</h4>
                                            <div class="alm-pdf-grid">
                                                @foreach ($docs as $doc)
                                                    @php $isDwg = strtolower(pathinfo($doc->nombre_archivo, PATHINFO_EXTENSION)) === 'dwg'; @endphp
                                                    <div class="dibujos-file-card" style="animation-delay: {{ $loop->index * 0.05 }}s;">
                                                        <div class="file-icon-wrapper" onclick="calmaqVerArchivo({{ $doc->id }})" class="cal-maq-cursor-pointer" title="{{ $isDwg ? 'Descargar DWG' : 'Abrir PDF' }}">
                                                            <img src="{{ asset('images/' . ($isDwg ? 'dwg-shadow.png' : 'pdf-view-shadow.png')) }}" class="file-icon icon-default">
                                                            <img src="{{ asset('images/' . ($isDwg ? 'dwg.png' : 'pdf-view.png')) }}" class="file-icon icon-hover">
                                                        </div>
                                                        <div class="file-name cal-maq-cursor-pointer" title="{{ $isDwg ? 'Descargar DWG' : 'Abrir PDF' }}" onclick="calmaqVerArchivo({{ $doc->id }})">
                                                            {{ $doc->nombre_archivo }}
                                                        </div>
                                                        <div class="file-actions">
                                                            <button class="btn-dibujos btn-dibujos-sm btn-ver" onclick="calmaqVerArchivo({{ $doc->id }})">{{ $isDwg ? 'Descargar' : 'Ver' }}</button>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    <div class="alm-empty cal-maq-display-none">
                        <div class="alm-empty-icon">
                            <img src="{{ asset('images/noPieces.png') }}" alt="Sin resultados" class="cal-maq-width-64px cal-maq-opacity-0-5">
                        </div>
                        <p>Ningun dibujo coincide con los filtros seleccionados.</p>
                    </div>
                </div>
            @endif
        </div>

        {{-- ══════════════════════════════════════════════════════════
             TABLA 2 — AYUDAS VISUALES DE MAQUINADOS (activas)
        ══════════════════════════════════════════════════════════ --}}
        @php
            $ayudasAgrupadas = $ayudas->groupBy(fn($a) => ($a->clase ?? '—') . '|' . ($a->proceso ?? '—'));
        @endphp

        <div class="alm-table-card cal-maq-border-color-027a3ad4">
            <div class="alm-table-header header-ayudas">
                <h2>Ayudas Visuales de Maquinados</h2>
                <span class="alm-results-count" id="calmaq-count-ayudas">
                    {{ $ayudas->count() }} resultado{{ $ayudas->count() !== 1 ? 's' : '' }}
                </span>
            </div>

            @if ($ayudas->isEmpty())
                <div class="alm-empty" id="calmaq-empty-ayudas">
                    <div class="alm-empty-icon">
                        <img src="{{ asset('images/noPieces.png') }}" alt="Sin resultados" class="cal-maq-width-64px cal-maq-opacity-0-5">
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
                                <th class="cal-maq-width-35pct">Clase</th>
                                <th class="cal-maq-width-30pct">Proceso</th>
                                <th class="cal-maq-width-15pct d-text-center">Archivos</th>
                                <th class="cal-maq-width-20pct d-text-center">Accion</th>
                            </tr>
                        </thead>
                        <tbody id="calmaq-tbody-ayudas">
                            @foreach ($ayudasAgrupadas as $key => $docs)
                                @php
                                    [$clase, $proceso] = explode('|', $key);
                                @endphp
                                <tr class="calmaq-fila-doc row-ayuda"
                                    data-clase="{{ $clase }}"
                                    data-proceso="{{ $proceso }}">
                                    <td><div class="alm-clase-label">{{ $clase }}</div></td>
                                    <td><div class="alm-proceso-label">{{ $proceso }}</div></td>
                                    <td class="d-text-center">
                                        <span class="badge-pdf-count">{{ $docs->count() }}</span>
                                    </td>
                                    <td class="d-text-center">
                                        <button class="btn-toggle-files"
                                                data-target="files-ayuda-{{ $loop->index }}"
                                                aria-expanded="false">
                                            Ver Archivos
                                        </button>
                                    </td>
                                </tr>
                                {{-- Fila desplegable de archivos --}}
                                <tr class="alm-files-row" id="files-ayuda-{{ $loop->index }}">
                                    <td colspan="4">
                                        <div class="alm-files-inner">
                                            <h4 class="cal-maq-margin-top-15px cal-maq-margin-bottom-10px cal-maq-color-027a3a cal-maq-border-bottom-2px-solid-027a3a cal-maq-padding-bottom-5px">
                                                Ayudas Visuales de Maquinados</h4>
                                            <div class="alm-pdf-grid">
                                                @foreach ($docs as $doc)
                                                    @php $isDwg = strtolower(pathinfo($doc->nombre_archivo, PATHINFO_EXTENSION)) === 'dwg'; @endphp
                                                    <div class="dibujos-file-card card-ayuda" style="animation-delay: {{ $loop->index * 0.05 }}s;">
                                                        <div class="file-icon-wrapper" onclick="calmaqVerArchivo({{ $doc->id }})" class="cal-maq-cursor-pointer" title="{{ $isDwg ? 'Descargar DWG' : 'Abrir PDF' }}">
                                                            <img src="{{ asset('images/' . ($isDwg ? 'dwg-shadow.png' : 'pdf-view-shadow.png')) }}" class="file-icon icon-default">
                                                            <img src="{{ asset('images/' . ($isDwg ? 'dwg.png' : 'pdf-view.png')) }}" class="file-icon icon-hover">
                                                        </div>
                                                        <div class="file-name cal-maq-cursor-pointer" title="{{ $isDwg ? 'Descargar DWG' : 'Abrir PDF' }}" onclick="calmaqVerArchivo({{ $doc->id }})">
                                                            {{ $doc->nombre_archivo }}
                                                        </div>
                                                        <div class="file-actions">
                                                            <button class="btn-dibujos btn-dibujos-sm btn-ver btn-ayuda-color" onclick="calmaqVerArchivo({{ $doc->id }})">{{ $isDwg ? 'Descargar' : 'Ver' }}</button>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    <div class="alm-empty cal-maq-display-none">
                        <div class="alm-empty-icon">
                            <img src="{{ asset('images/noPieces.png') }}" alt="Sin resultados" class="cal-maq-width-64px cal-maq-opacity-0-5">
                        </div>
                        <p>Ninguna ayuda coincide con los filtros seleccionados.</p>
                    </div>
                </div>
            @endif
        </div>

        {{-- ══════════════════════════════════════════════════════════
             TABLA 3 — DOCUMENTOS INACTIVOS (todos los tipos)
        ══════════════════════════════════════════════════════════ --}}
        @php
            $inactivosAgrupados = $inactivos->groupBy(fn($i) => ($i->tipo ?? '—') . '|' . ($i->ot ?? '—') . '|' . ($i->clase ?? '—') . '|' . ($i->proceso ?? '—'));
        @endphp

        <div class="alm-table-card cal-maq-border-color-6c757dd4">
            <div class="alm-table-header header-inactivos">
                <h2>Documentos Inactivos (Historico)</h2>
                <span class="alm-results-count" id="calmaq-count-inactivos">
                    {{ $inactivos->count() }} resultado{{ $inactivos->count() !== 1 ? 's' : '' }}
                </span>
            </div>

            @if ($inactivos->isEmpty())
                <div class="alm-empty">
                    <div class="alm-empty-icon">
                        <img src="{{ asset('images/ready.png') }}" alt="Sin inactivos" class="cal-maq-width-64px cal-maq-opacity-0-5">
                    </div>
                    <p>No hay documentos inactivos. Todos los registros tienen su archivo de origen activo.</p>
                </div>
            @else
                <div class="alm-table-scroll">
                    <table class="alm-table table-inactivos">
                        <thead>
                            <tr>
                                <th class="cal-maq-width-12pct d-text-center">Tipo</th>
                                <th class="cal-maq-width-22pct">OT</th>
                                <th class="cal-maq-width-22pct">Clase</th>
                                <th class="cal-maq-width-18pct">Proceso</th>
                                <th class="cal-maq-width-8pct d-text-center">Archivos</th>
                                <th class="cal-maq-width-18pct d-text-center">Accion</th>
                            </tr>
                        </thead>
                        <tbody id="calmaq-tbody-inactivos">
                                @foreach ($inactivosAgrupados as $key => $docs)
                                @php
                                    [$tipo, $ot, $clase, $proceso] = explode('|', $key);
                                    $isAyuda = ($tipo === 'ayuda');

                                    // Limpiar guiones y convertirlos en N/A si es necesario
                                    $otText      = (trim($ot)      === '—' || !$ot)      ? 'N/A' : $ot;
                                    $claseText   = (trim($clase)   === '—' || !$clase)   ? 'N/A' : $clase;
                                    $procesoText = (trim($proceso) === '—' || !$proceso) ? 'N/A' : $proceso;
                                @endphp
                                <tr class="calmaq-fila-doc {{ $isAyuda ? 'row-ayuda' : 'row-dibujo' }}"
                                    data-ot="{{ $ot }}"
                                    data-clase="{{ $clase }}"
                                    data-proceso="{{ $proceso }}">
                                    <td class="d-text-center">
                                        @if (!$isAyuda)
                                            <span class="badge-tipo-dibujo">Dibujo</span>
                                        @else
                                            <span class="badge-tipo-ayuda">Ayuda</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="alm-ot-label">{{ preg_replace('/_\d{8}_\d{6}_.*/', '', $otText) }}</div>
                                    </td>
                                    <td><div class="alm-clase-label">{{ $claseText }}</div></td>
                                    <td><div class="alm-proceso-label">{{ $procesoText }}</div></td>
                                    <td class="d-text-center">
                                        <span class="badge-pdf-count">{{ $docs->count() }}</span>
                                    </td>
                                    <td class="d-text-center">
                                        <button class="btn-toggle-files"
                                                data-target="files-inactivo-{{ $loop->index }}"
                                                aria-expanded="false">
                                            Ver Archivos
                                        </button>
                                    </td>
                                </tr>
                                {{-- Fila desplegable de archivos --}}
                                <tr class="alm-files-row" id="files-inactivo-{{ $loop->index }}">
                                    <td colspan="6">
                                        <div class="alm-files-inner" style="border-top-color: {{ $isAyuda ? '#027a3a' : '#033966' }};">
                                            <h4 style="margin-top: 15px; margin-bottom: 10px; color: {{ $isAyuda ? '#027a3a' : '#033966' }}; border-bottom: 2px solid {{ $isAyuda ? '#027a3a' : '#033966' }}; padding-bottom: 5px; display: flex; align-items: center; justify-content: space-between;">
                                                <span>Documentos Inactivos — {{ $tipo === 'dibujo' ? 'Dibujos' : 'Ayudas' }} de Maquinados</span>
                                                <div class="alm-inactiva-note cal-maq-color-6c757d cal-maq-font-weight-normal">
                                                    Carpeta eliminada - Backup conservado
                                                </div>
                                            </h4>
                                            <div class="alm-pdf-grid">
                                                @foreach ($docs as $doc)
                                                    @php $isDwg = strtolower(pathinfo($doc->nombre_archivo, PATHINFO_EXTENSION)) === 'dwg'; @endphp
                                                    <div class="dibujos-file-card {{ $isAyuda ? 'card-ayuda' : '' }}" style="animation-delay: {{ $loop->index * 0.05 }}s;">
                                                        <div class="file-icon-wrapper" onclick="calmaqVerArchivo({{ $doc->id }})" class="cal-maq-cursor-pointer" title="{{ $isDwg ? 'Descargar DWG' : 'Abrir PDF' }}">
                                                            <img src="{{ asset('images/' . ($isDwg ? 'dwg-shadow.png' : 'pdf-view-shadow.png')) }}" class="file-icon icon-default">
                                                            <img src="{{ asset('images/' . ($isDwg ? 'dwg.png' : 'pdf-view.png')) }}" class="file-icon icon-hover">
                                                        </div>
                                                        <div class="file-name cal-maq-cursor-pointer" title="{{ $isDwg ? 'Descargar DWG' : 'Abrir PDF' }}" onclick="calmaqVerArchivo({{ $doc->id }})">
                                                            {{ $doc->nombre_archivo }}
                                                        </div>
                                                        <div class="file-actions">
                                                            @if($isAyuda)
                                                                <button class="btn-dibujos btn-dibujos-sm btn-ver btn-ayuda-color" onclick="calmaqVerArchivo({{ $doc->id }})">{{ $isDwg ? 'Descargar' : 'Ver' }}</button>
                                                            @else
                                                                <button class="btn-dibujos btn-dibujos-sm btn-ver" onclick="calmaqVerArchivo({{ $doc->id }})">{{ $isDwg ? 'Descargar' : 'Ver' }}</button>
                                                            @endif
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    <div class="alm-empty cal-maq-display-none">
                        <div class="alm-empty-icon">
                            <img src="{{ asset('images/noPieces.png') }}" alt="Sin resultados" class="cal-maq-width-64px cal-maq-opacity-0-5">
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
