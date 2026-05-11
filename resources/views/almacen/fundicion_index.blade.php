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
                                        <td class="d-text-center">
                                            <div id="status-modelo-{{ $reg->ot }}">
                                                @if ($reg->tiene_modelo)
                                                    <span class="badge-modelo-ok" title="Modelo disponible">
                                                        <img src="{{ asset('images/aprobado.png') }}" alt="OK" style="width: 35px; height: 35px;">
                                                    </span>
                                                @elseif($reg->pre_orden_sent)
                                                    <span class="badge-modelo-pending" title="Pre-orden enviada (Pendiente)">
                                                        <img src="{{ asset('images/caducado.png') }}" alt="Pendiente" style="width: 35px; height: 35px;">
                                                    </span>
                                                @else
                                                    <span class="badge-modelo-missing" title="Sin modelo">
                                                        <img src="{{ asset('images/advertencia.png') }}" alt="X" style="width: 35px; height: 35px;">
                                                    </span>
                                                @endif
                                            </div>
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
                                            <td colspan="6">
                                                @if ($countDibujos > 0)
                                                    <h3
                                                        style="margin-top: 15px; margin-bottom: 10px; color: #005194; border-bottom: 2px solid #005194; padding-bottom: 5px;">
                                                        Dibujos de Fundición</h3>
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
                                                                    {{ basename($archivo) }}</div>
                                                                <div class="file-actions">
                                                                    <button class="btn-dibujos btn-dibujos-sm btn-ver"
                                                                        onclick="almacenVerPdf('{{ $reg->ot }}', '{{ $archivo }}', 'dibujo')">Ver</button>
                                                                </div>
                                                            </div>
                                                        @endforeach
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
                                                                    {{ basename($ayudaArchivo) }}</div>
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

                                                {{-- ── SECCIÓN CONTROL DE MODELOS (Solo Almacén y OTs Activas) ── --}}
                                                @if (Auth::user()->perfil != 4 && $estado === 'activa')
                                                    <div class="alm-modelo-control" style="margin-top: 30px; padding: 20px; background: #f8fafc; border: 1px border-radius: 12px; border: 1px solid #e2e8f0;">
                                                        <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 15px;">
                                                            <div>
                                                                <h4 style="margin: 0; color: #334155; font-size: 1.1em;">Control de Modelos</h4>
                                                                <p style="margin: 5px 0 0 0; color: #64748b; font-size: 0.9em;">Actualmente, ¿Cuentas con el modelo de esta OT?</p>
                                                            </div>
                                                            <div style="display: flex; gap: 10px;">
                                                                <button class="btn-modelo btn-modelo-si" onclick="confirmarModelo('{{ $reg->ot }}')">
                                                                    Sí, cuento con él
                                                                </button>
                                                                <button class="btn-modelo btn-modelo-no" onclick="abrirModalPreOrden('{{ $reg->ot }}')">
                                                                    No, solicitar fabricación
                                                                </button>
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


    </div>{{-- /.alm-wrapper --}}

    {{-- ── MODAL: PRE-ORDEN PARA FABRICAR MODELOS ──────────────────── --}}
    <div id="modalPreOrden" class="alm-modal">
        <div class="alm-modal-content">
            <div class="alm-modal-header">
                <div class="div-cerrar">
                    <button type="button" class="btn-cerrar" onclick="cerrarModalPreOrden()">
                        <img class="img-cerrar" src="{{ asset('images/cerrar.png') }}">
                    </button>
                </div>
                <h3>Pre-Orden para Fabricar Modelos (4ALM-17)</h3>

                {{-- Pestañas (ocultas hasta que se active el flujo multi-orden) --}}
                <div id="po-tabs-nav" class="po-tabs-nav" style="display: none;">
                    <button type="button" class="po-tab-btn active" onclick="switchPoTab(1)" id="po-tab-btn-1">
                        Pre-Orden 1
                    </button>
                    <button type="button" class="po-tab-btn" onclick="switchPoTab(2)" id="po-tab-btn-2">
                        Pre-Orden 2
                    </button>
                </div>
            </div>
            <div class="alm-modal-body">

                {{-- ══════════════ PESTAÑA 1 ══════════════ --}}
                <div id="po-page-1" class="po-page">
                    <form id="formPreOrden">
                        <div class="form-grid">
                            <div class="form-group">
                                <label for="po-proveedor">Proveedor:</label>
                                <select id="po-proveedor" name="proveedor" class="form-control" required>
                                    <option value="">Selecciona uno</option>
                                    <option value="Jose">Jose</option>
                                    <option value="Jaxer">Jaxer</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="po-fecha">Fecha:</label>
                                <input type="date" id="po-fecha" name="fecha" class="form-control" required
                                    value="{{ date('Y-m-d') }}">
                            </div>
                            <div class="form-group">
                                <label for="po-folio">Folio:</label>
                                <input type="text" id="po-folio" name="folio" class="form-control" readonly
                                    value="MOD-{{ date('Y') }}-0000">
                            </div>
                            <div class="form-group">
                                <label for="po-moldura">Moldura:</label>
                                <input type="text" id="po-moldura" name="moldura" class="form-control" readonly required>
                            </div>
                            <div class="form-group">
                                <label for="po-ot">Orden de Trabajo (OT):</label>
                                <input type="text" id="po-ot" name="ot" class="form-control" readonly required>
                                <input type="hidden" id="po-ot-raw" name="ot_raw">
                            </div>
                        </div>

                        <div class="modal-table-container">
                            <table class="modal-table">
                                <thead>
                                    <tr>
                                        <th style="width: 16%;">Tipo de Modelo</th>
                                        <th style="width: 12%;">Impresiones</th>
                                        <th style="width: 12%;">Cantidad</th>
                                        <th style="width: 22%;">Descripción</th>
                                        <th style="width: 22%;">Código de Modelo</th>
                                        <th style="width: 10%;">
                                            Fecha Entrega
                                            <input type="date" id="po-fecha-entrega" name="fecha_entrega" class="form-control" style="font-size: 0.78em; padding: 2px 4px; height: 28px; margin-top: 4px;" required>
                                        </th>
                                        <th style="width: 6%; text-align:center;">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody id="alm-tbody-preorden">
                                    {{-- Se llenará por JS --}}
                                </tbody>
                            </table>
                            <div style="margin-top: 10px; text-align: center;">
                                <button type="button" id="btn-add-clase-po" class="btn-img-action" onclick="agregarFilaPreOrden()" title="Añadir una nueva clase a la pre-orden" style="display: none;">
                                    <img src="/images/anadir.png" alt="Añadir" style="width: 40px;">
                                </button>
                            </div>
                        </div>

                        <div class="form-group" style="margin-top: 20px;">
                            <label for="po-observaciones">Observaciones:</label>
                            <textarea id="po-observaciones" name="observaciones" class="form-control" rows="3"></textarea>
                        </div>

                        <div class="form-actions" style="margin-top: 30px; text-align: center;">
                            <button type="submit" class="btn-save-preorden" id="btn-submit-preorden">
                                Generar Pre-Orden y Enviar Email
                            </button>
                        </div>
                    </form>
                </div>

                {{-- ══════════════ PESTAÑA 2 ══════════════ --}}
                <div id="po-page-2" class="po-page" style="display: none;">
                    <form id="formPreOrden2">
                        <div class="po-second-order-notice">
                            <img src="/images/Aviso.png" class="po-notice-icon" alt="Aviso">
                            <div class="po-notice-text">
                                Estás creando una <strong>segunda pre-orden</strong> para las clases que no se incluyeron en la primera.
                                Selecciona el proveedor correspondiente y genera el documento.
                            </div>
                        </div>

                        <div class="form-grid">
                            <div class="form-group">
                                <label for="po2-proveedor">Proveedor:</label>
                                <select id="po2-proveedor" name="proveedor" class="form-control" required>
                                    <option value="">Selecciona uno</option>
                                    <option value="Jose">Jose</option>
                                    <option value="Jaxer">Jaxer</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="po2-fecha">Fecha:</label>
                                <input type="date" id="po2-fecha" name="fecha" class="form-control" required
                                    value="{{ date('Y-m-d') }}">
                            </div>
                            <div class="form-group">
                                <label for="po2-folio">Folio:</label>
                                <input type="text" id="po2-folio" name="folio" class="form-control" readonly
                                    value="MOD-{{ date('Y') }}-0000">
                            </div>
                            <div class="form-group">
                                <label for="po2-moldura">Moldura:</label>
                                <input type="text" id="po2-moldura" name="moldura" class="form-control" readonly required>
                            </div>
                            <div class="form-group">
                                <label for="po2-ot">Orden de Trabajo (OT):</label>
                                <input type="text" id="po2-ot" name="ot" class="form-control" readonly required>
                            </div>
                        </div>

                        <div class="modal-table-container">
                            <table class="modal-table">
                                <thead>
                                    <tr>
                                        <th style="width: 16%;">Tipo de Modelo</th>
                                        <th style="width: 12%;">Impresiones</th>
                                        <th style="width: 12%;">Cantidad</th>
                                        <th style="width: 22%;">Descripción</th>
                                        <th style="width: 22%;">Código de Modelo</th>
                                        <th style="width: 10%;">
                                            Fecha Entrega
                                            <input type="date" id="po2-fecha-entrega" name="fecha_entrega" class="form-control" style="font-size: 0.78em; padding: 2px 4px; height: 28px; margin-top: 4px;" required>
                                        </th>
                                        <th style="width: 6%; text-align:center;">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody id="alm-tbody-preorden2">
                                    {{-- Se llenará automáticamente con las clases eliminadas --}}
                                </tbody>
                            </table>
                            <div style="margin-top: 10px; text-align: center;">
                                <button type="button" class="btn-img-action" onclick="agregarFilaPreOrden2()" title="Añadir una nueva clase a la pre-orden">
                                    <img src="/images/anadir.png" alt="Añadir" style="width: 40px;">
                                </button>
                            </div>
                        </div>

                        <div class="form-group" style="margin-top: 20px;">
                            <label for="po2-observaciones">Observaciones:</label>
                            <textarea id="po2-observaciones" name="observaciones" class="form-control" rows="3"></textarea>
                        </div>

                        <div class="form-actions" style="margin-top: 30px; text-align: center;">
                            <button type="submit" class="btn-save-preorden" id="btn-submit-preorden2">
                                Generar Pre-Orden 2 y Enviar Email
                            </button>
                        </div>
                    </form>
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
            storePreOrden: "{{ route('almacen.fundicion.storePreOrden') }}",
        };
    </script>

@endsection
