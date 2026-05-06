@extends('layouts.appMenu')

@section('head')
    <title>Almacén — Dibujos de Fundición | GIS</title>
    <meta name="description"
        content="Consulta histórica de dibujos de fundición enviados a Almacén y Calidad. Vista de solo lectura.">
    @vite(['resources/css/almacen_views/almacen_fundicion.css', 'resources/js/almacen_views/almacen_fundicion.js'])
@endsection

@section('background-body', 'background-image:url("' . asset('images/fondoLogin.jpg') . '")')

@section('content')

    <div class="alm-wrapper">

        {{-- ── HEADER ─────────────────────────────────────────────── --}}
        @php
            $perfil = Auth::user()->perfil;
            $deptName = $perfil == 4 ? 'Calidad' : 'Almacén';
            $deptIcon = $perfil == 4 ? 'Quality.png' : 'almacen.png';
        @endphp

        <div class="alm-header">
            <div class="alm-header-icon">
                <img src="{{ asset('images/' . $deptIcon) }}" alt="{{ $deptName }}" style="width: 90px;">
            </div>
            <div class="alm-header-text">
                <h1>Dibujos y Ayudas Visuales de Fundición — {{ $deptName }}</h1>
                <p>Consulta histórica de todos los dibujos y ayudas visuales enviados a {{ $deptName }}. Registro
                    permanente e inmutable.</p>
            </div>
            <span class="alm-readonly-badge">Solo lectura</span>
        </div>

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
                                    {{ $otOption }}
                                </option>
                            @endforeach
                        </select>
                        <label for="alm-search-ot">Orden de trabajo: </label>
                    </div>

                    <div class="filter">
                        <input id="alm-desde" class="input-filter" type="date" name="desde"
                            value="{{ $desde }}" onchange="this.form.submit()">
                        <label for="alm-desde">Desde: </label>
                    </div>

                    <div class="filter">
                        <input id="alm-hasta" class="input-filter" type="date" name="hasta"
                            value="{{ $hasta }}" onchange="this.form.submit()">
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

        {{-- ── TABLAS ───────────────────────────────────────────────── --}}
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
                                        style="width:42%; {{ $estado === 'inactiva' ? 'background: #6c757d; border-color: #5a6268;' : '' }}">
                                        Orden de Trabajo</th>
                                    <th style="width:14%; {{ $estado === 'inactiva' ? 'background: #6c757d; border-color: #5a6268;' : '' }}"
                                        class="d-text-center">Estado</th>
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
                                        $archivos = is_array($reg->almacen_archivos) ? $reg->almacen_archivos : [];
                                        $countDibujos = count($archivos);

                                        $otName = trim(
                                            preg_replace('/[\/\\\\]/', '', preg_replace('/\.\.+/', '', $reg->ot)),
                                        );
                                        $ayudasDir =
                                            'DOCUMENTACION_GIS/ALMACEN_FUNDICION/' . $otName . '/ayudas_visuales';
                                        $ayudasArchivos = [];
                                        if (\Illuminate\Support\Facades\Storage::disk('local')->exists($ayudasDir)) {
                                            $files = \Illuminate\Support\Facades\Storage::disk('local')->allFiles(
                                                $ayudasDir,
                                            );
                                            foreach ($files as $f) {
                                                if (strtolower(pathinfo($f, PATHINFO_EXTENSION)) === 'pdf') {
                                                    // Obtener ruta relativa a ayudas_visuales (ej: "Bombillo/archivo.pdf")
                                                    $relativePath = str_replace($ayudasDir . '/', '', $f);
                                                    $ayudasArchivos[] = $relativePath;
                                                }
                                            }
                                        }
                                        $countAyudas = count($ayudasArchivos);
                                        $count = $countDibujos + $countAyudas;
                                    @endphp

                                    {{-- Fila principal --}}
                                    <tr data-ot="{{ $reg->ot }}">
                                        <td>
                                            <div class="alm-ot-label">{{ $reg->ot }}</div>
                                            @if ($reg->status === 'inactiva')
                                                <div class="alm-inactiva-note">
                                                    La carpeta fue eliminada por el administrador. Los PDFs de Almacén se
                                                    conservan.
                                                </div>
                                            @endif
                                        </td>
                                        <td class="d-text-center">
                                            <span class="badge-status badge-{{ $reg->status }}">
                                                {{ $reg->status }}
                                            </span>
                                        </td>
                                        <td class="alm-date d-text-center">
                                            {{ $reg->alert_sent_at ? $reg->alert_sent_at->format('d/m/Y H:i') : '—' }}
                                        </td>
                                        <td class="d-text-center">
                                            <span class="badge-pdf-count">{{ $count }}</span>
                                        </td>
                                        <td class="d-text-center">
                                            @if ($count > 0)
                                                <button class="btn-toggle-files"
                                                    data-target="files-{{ $estado }}-{{ $loop->index }}"
                                                    data-ot="{{ $reg->ot }}"
                                                    id="toggle-btn-{{ $estado }}-{{ $loop->index }}"
                                                    aria-expanded="false">
                                                    Ver Archivos
                                                </button>
                                            @else
                                                <span class="d-text-subtle" style="font-size:0.85em;">Sin archivos</span>
                                            @endif
                                        </td>
                                    </tr>

                                    {{-- Fila desplegable de archivos --}}
                                    @if ($count > 0)
                                        <tr class="alm-files-row" id="files-{{ $estado }}-{{ $loop->index }}">
                                            <td colspan="5">
                                                @if ($countDibujos > 0)
                                                    <h4
                                                        style="margin-top: 15px; margin-bottom: 10px; color: #005194; border-bottom: 2px solid #005194; padding-bottom: 5px;">
                                                        Dibujos de Fundición</h4>
                                                    <div class="alm-pdf-grid">
                                                        @foreach ($archivos as $archivo)
                                                            <div class="dibujos-file-card"
                                                                style="animation-delay: {{ $loop->index * 0.05 }}s;">
                                                                <div class="file-icon-wrapper"
                                                                    onclick="almacenVerPdf('{{ $reg->ot }}', '{{ $archivo }}', 'dibujo')"
                                                                    style="cursor: pointer;" title="Abrir PDF">
                                                                    <img src="{{ asset('images/pdf-view-shadow.png') }}"
                                                                        class="file-icon icon-default">
                                                                    <img src="{{ asset('images/pdf-view.png') }}"
                                                                        class="file-icon icon-hover">
                                                                </div>
                                                                <div class="file-name" style="cursor: pointer;"
                                                                    title="Abrir PDF"
                                                                    onclick="almacenVerPdf('{{ $reg->ot }}', '{{ $archivo }}', 'dibujo')">
                                                                    {{ $archivo }}</div>
                                                                <div class="file-actions">
                                                                    <button class="btn-dibujos btn-dibujos-sm btn-ver"
                                                                        onclick="almacenVerPdf('{{ $reg->ot }}', '{{ $archivo }}', 'dibujo')">Ver</button>
                                                                </div>
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                @endif

                                                @if ($countAyudas > 0)
                                                    <h4
                                                        style="margin-top: 25px; margin-bottom: 10px; color: #9c0300; border-bottom: 2px solid #9c0300; padding-bottom: 5px;">
                                                        Ayudas Visuales</h4>
                                                    <div class="alm-pdf-grid">
                                                        @foreach ($ayudasArchivos as $ayudaArchivo)
                                                            <div class="dibujos-file-card card-ayuda"
                                                                style="animation-delay: {{ $loop->index * 0.05 }}s;">
                                                                <div class="file-icon-wrapper"
                                                                    onclick="almacenVerPdf('{{ $reg->ot }}', '{{ $ayudaArchivo }}', 'ayuda')"
                                                                    style="cursor: pointer;" title="Abrir PDF">
                                                                    <img src="{{ asset('images/pdf-view-shadow.png') }}"
                                                                        class="file-icon icon-default">
                                                                    <img src="{{ asset('images/pdf-view.png') }}"
                                                                        class="file-icon icon-hover">
                                                                </div>
                                                                <div class="file-name" style="cursor: pointer;"
                                                                    title="Abrir PDF"
                                                                    onclick="almacenVerPdf('{{ $reg->ot }}', '{{ $ayudaArchivo }}', 'ayuda')">
                                                                    {{ $ayudaArchivo }}</div>
                                                                <div class="file-actions">
                                                                    <button
                                                                        class="btn-dibujos btn-dibujos-sm btn-ver btn-ayuda-color"
                                                                        onclick="almacenVerPdf('{{ $reg->ot }}', '{{ $ayudaArchivo }}', 'ayuda')">Ver</button>
                                                                </div>
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                @elseif(!empty($reg->ayudas_config))
                                                    <div
                                                        style="margin-top: 20px; padding: 15px; background: #fff5f5; border: 1px solid #feb2b2; border-radius: 8px; color: #9c0300;">
                                                        <strong>Aviso:</strong> Se han vinculado
                                                        {{ count($reg->ayudas_config) }} clases de ayudas visuales, pero
                                                        los archivos aún no se han sincronizado con Almacén. Por favor,
                                                        <strong>Vuelve a Vincular</strong> las ayudas desde la vista de
                                                        administración.
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

    </div>{{-- /.alm-wrapper --}}

    <script>
        window.almacenRoutes = {
            archivos: "{{ route('almacen.fundicion.archivos') }}",
            serve: "{{ route('almacen.fundicion.serve') }}",
        };
    </script>

@endsection
