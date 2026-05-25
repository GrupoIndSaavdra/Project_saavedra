@extends('layouts.appMenu')

@section('head')
    @php
        $perfil = Auth::user()->perfil;
        $deptName = $perfil == 4 ? 'Calidad' : 'Almacén';
    @endphp
    <title>{{ $deptName }} — Dibujos de Fundición | GIS</title>
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
                                        $otrosArchivos = [];
                                        if (\Illuminate\Support\Facades\Storage::disk('local')->exists($ayudasDir)) {
                                            $files = \Illuminate\Support\Facades\Storage::disk('local')->allFiles(
                                                $ayudasDir,
                                            );
                                            foreach ($files as $f) {
                                                if (strtolower(pathinfo($f, PATHINFO_EXTENSION)) === 'pdf') {
                                                    // Normalizar separadores para reemplazo robusto
                                                    $fNorm = str_replace('\\', '/', $f);
                                                    $dirNorm = str_replace('\\', '/', $ayudasDir);
                                                    $relativePath = ltrim(str_replace($dirNorm, '', $fNorm), '/');
                                                    
                                                    if (str_starts_with($relativePath, 'preordenes/')) {
                                                        $otrosArchivos[] = [
                                                            'nombre' => $relativePath,
                                                            'url' => route('almacen.fundicion.serve', ['ot' => $reg->ot, 'archivo' => $relativePath, 'tipo' => 'otro']),
                                                            'tipo' => 'otro'
                                                        ];
                                                    } else {
                                                        $ayudasArchivos[] = [
                                                            'nombre' => $relativePath,
                                                            'url' => route('almacen.fundicion.serve', ['ot' => $reg->ot, 'archivo' => $relativePath, 'tipo' => 'ayuda']),
                                                            'tipo' => 'ayuda'
                                                        ];
                                                    }
                                                }
                                            }
                                        }
                                        
                                        // Buscar liberaciones PDF
                                        $liberacionesPath = storage_path('app/public/liberaciones_pdf');
                                        $otSanitizada = preg_replace('/[^\w\s\-]/', '', $reg->ot);
                                        $otSanitizada = preg_replace('/[\s]+/', '_', trim($otSanitizada));
                                        if (file_exists($liberacionesPath)) {
                                            $pattern = "{$liberacionesPath}/F-CCL-LDM_*_{$otSanitizada}_*.pdf";
                                            foreach (glob($pattern) as $f) {
                                                $otrosArchivos[] = [
                                                    'nombre' => basename($f),
                                                    'url' => route('almacen.fundicion.serve', ['ot' => $reg->ot, 'archivo' => basename($f), 'tipo' => 'liberacion']),
                                                    'tipo' => 'liberacion'
                                                ];
                                            }
                                        }

                                        $countAyudas = count($ayudasArchivos);
                                        $countOtros = count($otrosArchivos);
                                        $count = $countDibujos + $countAyudas + $countOtros;
                                    @endphp

                                    {{-- Fila principal --}}
                                    <tr data-ot="{{ $reg->ot }}">
                                        <td>
                                            <div class="alm-ot-label">{{ $reg->ot }}</div>
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
                                                    $libStatus = $reg->calidad_revision_status ?? null;
                                                    $perfil = Auth::user()->perfil;
                                                @endphp
                                                @if ($perfil == 4)
                                                    {{-- VISTA CALIDAD --}}
                                                    @if ($libStatus === 'aprobado')
                                                        <span class="badge-modelo-ok" title="Modelo liberado y aprobado por Calidad">
                                                            <img src="{{ asset('images/Aprobado.png') }}" alt="Aprobado" style="width: 38px; height: 38px;">
                                                        </span>
                                                    @elseif ($libStatus === 'rechazado')
                                                        <span class="badge-modelo-rechazado" title="Modelo rechazado por Calidad">
                                                            <img src="{{ asset('images/Rechazado.png') }}" alt="Rechazado" style="width: 38px; height: 38px;">
                                                        </span>
                                                    @elseif ($libStatus === 'pendiente')
                                                        <span class="badge-modelo-guardado" title="Datos capturados por Calidad (borrador)">
                                                            <img src="{{ asset('images/Guardado.png') }}" alt="Guardado" style="width: 38px; height: 38px;">
                                                        </span>
                                                    @elseif ($reg->tiene_modelo || $reg->pre_orden_sent)
                                                        <span class="badge-modelo-recibido" title="Almacén ha procesado el modelo, pendiente de revisión por Calidad">
                                                            <img src="{{ asset('images/Recibido.png') }}" alt="Recibido" style="width: 38px; height: 38px;">
                                                        </span>
                                                    @else
                                                        <span class="badge-modelo-missing" title="En espera de que Almacén procese el modelo">
                                                            <img src="{{ asset('images/Espera.png') }}" alt="En Espera" style="width: 38px; height: 38px;">
                                                        </span>
                                                    @endif
                                                @else
                                                    {{-- VISTA ALMACÉN --}}
                                                    @if ($libStatus === 'aprobado')
                                                        <span class="badge-modelo-ok" title="Modelo liberado y aprobado por Calidad">
                                                            <img src="{{ asset('images/Aprobado.png') }}" alt="Aprobado" style="width: 38px; height: 38px;">
                                                        </span>
                                                    @elseif ($libStatus === 'rechazado')
                                                        <span class="badge-modelo-rechazado" title="Modelo rechazado por Calidad">
                                                            <img src="{{ asset('images/Rechazado.png') }}" alt="Rechazado" style="width: 38px; height: 38px;">
                                                        </span>

                                                    @elseif ($reg->tiene_modelo || $reg->pre_orden_sent)
                                                        <span class="badge-modelo-espera" title="Procesado por Almacén, en espera de respuesta de Calidad">
                                                            <img src="{{ asset('images/Espera.png') }}" alt="En Espera" style="width: 38px; height: 38px;">
                                                        </span>
                                                    @else
                                                        <span class="badge-modelo-recibido" title="Alerta inicial recibida, pendiente de procesar modelo">
                                                            <img src="{{ asset('images/Recibido.png') }}" alt="Recibido" style="width: 38px; height: 38px;">
                                                        </span>
                                                    @endif
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
                                                                    onclick="almacenVerPdf('{{ $reg->ot }}', '{{ $ayudaArchivo['nombre'] }}', '{{ $ayudaArchivo['tipo'] }}')"
                                                                    style="cursor: pointer;" title="Abrir PDF">
                                                                    <img src="{{ asset('images/pdf-view-shadow.png') }}"
                                                                        class="file-icon icon-default">
                                                                    <img src="{{ asset('images/pdf-view.png') }}"
                                                                        class="file-icon icon-hover">
                                                                </div>
                                                                <div class="file-name" style="cursor: pointer;"
                                                                    title="Abrir PDF"
                                                                    onclick="almacenVerPdf('{{ $reg->ot }}', '{{ $ayudaArchivo['nombre'] }}', '{{ $ayudaArchivo['tipo'] }}')">
                                                                    {{ basename($ayudaArchivo['nombre']) }}</div>
                                                                <div class="file-actions">
                                                                    <button
                                                                        class="btn-dibujos btn-dibujos-sm btn-ver btn-ayuda-color"
                                                                        onclick="almacenVerPdf('{{ $reg->ot }}', '{{ $ayudaArchivo['nombre'] }}', '{{ $ayudaArchivo['tipo'] }}')">Ver</button>
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

                                                @if ($countOtros > 0)
                                                    <h3
                                                        style="margin-top: 25px; margin-bottom: 10px; color: #155724; border-bottom: 2px solid #155724; padding-bottom: 5px;">
                                                        Otros documentos</h3>
                                                    <div class="alm-pdf-grid">
                                                        @foreach ($otrosArchivos as $otroArchivo)
                                                            <div class="dibujos-file-card card-otro"
                                                                style="animation-delay: {{ $loop->index * 0.05 }}s; border-left-color: #155724;">
                                                                <div class="file-icon-wrapper"
                                                                    onclick="almacenVerPdf('{{ $reg->ot }}', '{{ $otroArchivo['nombre'] }}', '{{ $otroArchivo['tipo'] }}')"
                                                                    style="cursor: pointer;" title="Abrir PDF">
                                                                    <img src="{{ asset('images/pdf-view-shadow.png') }}"
                                                                        class="file-icon icon-default">
                                                                    <img src="{{ asset('images/pdf-view.png') }}"
                                                                        class="file-icon icon-hover">
                                                                </div>
                                                                <div class="file-name" style="cursor: pointer;"
                                                                    title="Abrir PDF"
                                                                    onclick="almacenVerPdf('{{ $reg->ot }}', '{{ $otroArchivo['nombre'] }}', '{{ $otroArchivo['tipo'] }}')">
                                                                    {{ basename($otroArchivo['nombre']) }}</div>
                                                                <div class="file-actions">
                                                                    <button class="btn-dibujos btn-dibujos-sm btn-ver" style="background-color: #155724; color: white;"
                                                                        onclick="almacenVerPdf('{{ $reg->ot }}', '{{ $otroArchivo['nombre'] }}', '{{ $otroArchivo['tipo'] }}')">Ver</button>
                                                                </div>
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                @endif

                                                {{-- ── SECCIÓN CONTROL DE MODELOS (Solo Almacén y OTs Activas) ── --}}
                                                @if (Auth::user()->perfil != 4 && $estado === 'activa')
                                                    @php
                                                        $controlDisabled = $reg->tiene_modelo ? 'opacity: 0.5; pointer-events: none;' : '';
                                                        $hideSiNo = $reg->pre_orden_sent ? 'display: none;' : '';
                                                        $hideEditMail = !$reg->pre_orden_sent ? 'display: none;' : '';
                                                    @endphp
                                                    <div class="lib-calidad-card" id="control-modelo-{{ md5($reg->ot) }}" style="{{ $controlDisabled }}">
                                                        <div class="lib-calidad-card-header">
                                                            <img src="{{ asset('images/almacen.png') }}" alt="Almacén" style="width:38px;height:38px;object-fit:contain;flex-shrink:0;">
                                                            <div style="overflow:hidden;">
                                                                <span class="lib-calidad-card-title">Control de Modelos &mdash; Almacén</span>
                                                                <span class="lib-calidad-card-ot">{{ $reg->ot }}</span>
                                                            </div>
                                                        </div>
                                                        <div class="lib-calidad-card-body">
                                                            <div class="lib-calidad-action-row">
                                                                <h4 class="lib-calidad-card-prompt">
                                                                    @if ($reg->tiene_modelo)
                                                                        ¡Modelo recibido y procesado! Pendiente de que Calidad lo revise.
                                                                    @elseif ($reg->pre_orden_sent)
                                                                        Pre-orden lista. Puedes seguir editando los datos o enviarla por correo.
                                                                    @else
                                                                        ¿Ya cuentas con el modelo de esta OT o necesitas generar una pre-orden?
                                                                    @endif
                                                                </h4>
                                                                <div class="lib-calidad-card-btns">
                                                                    <button class="btn-modelo btn-modelo-si" onclick="confirmarModelo('{{ $reg->ot }}', '{{ md5($reg->ot) }}')" title="Sí, cuento con el modelo de esta OT" style="{{ $hideSiNo }}">
                                                                        <img src="{{ asset('images/Aprobado.png') }}" alt="Si">
                                                                        <span>Tengo el Modelo</span>
                                                                    </button>
                                                                    <button class="btn-modelo btn-modelo-no" onclick="abrirModalPreOrden('{{ $reg->ot }}')" title="No cuento con él, generar formato PDF" style="{{ $hideSiNo }}">
                                                                        <img src="{{ asset('images/pdf.png') }}" alt="PDF">
                                                                        <span>No, generar formato</span>
                                                                    </button>
                                                                    <button class="btn-modelo btn-modelo-edit" onclick="abrirModalPreOrden('{{ $reg->ot }}')" title="Editar información de la preorden existente" style="{{ $hideEditMail }}">
                                                                        <img src="{{ asset('images/editar-informacion.png') }}" alt="Editar">
                                                                        <span>Editar Datos</span>
                                                                    </button>
                                                                    <button class="btn-modelo btn-modelo-email" onclick="abrirModalEnviarPreOrden('{{ $reg->ot }}', '{{ md5($reg->ot) }}')" title="Enviar pre-orden por correo electrónico" style="{{ $hideEditMail }}">
                                                                        <img src="{{ asset('images/enviando.png') }}" alt="Enviar">
                                                                        <span>Enviar Correo</span>
                                                                    </button>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endif

                                                {{-- ── ACCIONES DE CALIDAD (Solo perfil 4 y OTs activas) ── --}}
                                                @if (Auth::user()->perfil == 4 && $estado === 'activa')
                                                    @if (in_array($reg->calidad_revision_status, [null, 'pendiente', 'rechazado']))
                                                    <div class="lib-calidad-card">
                                                        <div class="lib-calidad-card-header">
                                                            <img src="{{ asset('images/Quality.png') }}" alt="Calidad" style="width:38px;height:38px;object-fit:contain;flex-shrink:0;">
                                                            <div style="overflow:hidden;">
                                                                <span class="lib-calidad-card-title">Acciones de Liberacion &mdash; Calidad</span>
                                                                <span class="lib-calidad-card-ot">{{ $reg->ot }}</span>
                                                            </div>
                                                        </div>
                                                        <div class="lib-calidad-card-body">
                                                        @if ($reg->calidad_revision_status === 'rechazado')
                                                            <div class="lib-estado-badge lib-estado-rechazado">
                                                                <img src="{{ asset('images/Rechazado.png') }}" alt="" style="width:18px;height:18px;object-fit:contain;flex-shrink:0;">
                                                                Liberacion rechazada anteriormente. Puedes revisar y volver a emitir un veredicto.
                                                            </div>
                                                        @elseif (is_null($reg->calidad_revision_status) && !$reg->pre_orden_sent && !$reg->tiene_modelo)
                                                            <div class="lib-estado-badge lib-estado-info">
                                                                Sin accion de Almacen registrada aun para esta OT.
                                                            </div>
                                                        @elseif ($reg->calidad_revision_status === 'pendiente')
                                                            <div class="lib-estado-badge lib-estado-guardado">
                                                                <img src="{{ asset('images/Guardado.png') }}" alt="" style="width:18px;height:18px;object-fit:contain;flex-shrink:0;">
                                                                Datos capturados como borrador.
                                                            </div>
                                                        @endif
                                                        <div class="lib-calidad-action-row">
                                                            <h4 class="lib-calidad-card-prompt">
                                                                @if ($reg->calidad_revision_status === 'rechazado')
                                                                    El modelo fue rechazado antes. ¿Quieres revisarlo de nuevo?
                                                                @elseif ($reg->calidad_revision_status === 'pendiente')
                                                                    Tienes un borrador guardado. ¿Deseas terminar de revisarlo ahora?
                                                                @else
                                                                    ¿Qué deseas hacer con este modelo? ¿Lo apruebas o lo rechazas?
                                                                @endif
                                                            </h4>
                                                            <div class="lib-calidad-card-btns">
                                                                <button class="btn-calidad-action btn-calidad-aprobar"
                                                                        onclick="abrirModalLiberacion('{{ $reg->ot }}', 'aprobar')"
                                                                        title="Abrir formato y aprobar la liberacion de este modelo">
                                                                    <img src="{{ asset('images/Aprobado.png') }}" alt="">
                                                                    <span>Aprobar Liberacion</span>
                                                                </button>
                                                                <button class="btn-calidad-action btn-calidad-rechazar"
                                                                        onclick="abrirModalLiberacion('{{ $reg->ot }}', 'rechazar')"
                                                                        title="Abrir formato y rechazar la liberacion de este modelo">
                                                                    <img src="{{ asset('images/Rechazado.png') }}" alt="">
                                                                    <span>Rechazar Liberacion</span>
                                                                </button>
                                                            </div>
                                                        </div>
                                                        </div>{{-- /.lib-calidad-card-body --}}
                                                    </div>
                                                    @elseif ($reg->calidad_revision_status === 'aprobado')
                                                    <div class="lib-estado-badge lib-estado-aprobado" style="margin-top: 20px;">
                                                        <img src="{{ asset('images/Aprobado.png') }}" alt="Aprobado" style="width:22px;">
                                                        Modelo liberado y aprobado por Calidad.
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

                {{-- Pestañas eliminadas para flujo simplificado --}}
            </div>
            <div class="alm-modal-body">

                {{-- ══════════════ PESTAÑA 1 ══════════════ --}}
                <div id="po-page-1" class="po-page">
                    <form id="formPreOrden">
                        <div class="form-grid">
                            <div class="form-group po-proveedor-group">
                                <label for="po-proveedor">Proveedor:</label>
                                <input type="text" id="po-proveedor" name="proveedor" class="form-control" readonly required
                                    value="SS Metal Foundry, S. de R. L. de C. V.">
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
                                        <th style="width: 16%;">Tipo de Modelo</th>
                                        <th style="width: 12%;">Impresiones</th>
                                        <th style="width: 12%;">Cantidad</th>
                                        <th style="width: 22%;">Descripción</th>
                                        <th style="width: 22%;">Código de Modelo</th>
                                        <th style="width: 12%;">Fecha Entrega</th>
                                        <th style="width: 6%; text-align:center;">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody id="alm-tbody-preorden">
                                    {{-- Se llenará por JS --}}
                                </tbody>
                            </table>
                            <div style="margin-top: 10px; text-align: center;">
                                <button type="button" id="btn-add-clase-po" class="btn-img-action" onclick="agregarFilaPreOrden()" title="Añadir una nueva clase a la pre-orden" style="display: inline-block;">
                                    <img src="{{ asset('images/anadir.png') }}" alt="Añadir" style="width: 40px;">
                                </button>
                            </div>
                        </div>

                        <div class="form-group" style="margin-top: 20px;">
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

                {{-- Pestaña 2 eliminada --}}

            </div>
        </div>
    </div>

    {{-- ── MODAL: ENVIAR PRE-ORDEN POR CORREO CON ADJUNTOS (FASE 2) ── --}}
    <div id="modalEnviarPreOrden" class="alm-modal">
        <div class="alm-modal-content" style="max-width: 1100px;">
            <div class="alm-modal-header">
                <div class="div-cerrar">
                    <button type="button" class="btn-cerrar" onclick="cerrarModalEnviarPreOrden()">
                        <img class="img-cerrar" src="{{ asset('images/cerrar.png') }}">
                    </button>
                </div>
                <h3>Enviar Pre-Orden por Correo (4ALM-17 - Fase 2)</h3>
            </div>
            <div class="alm-modal-body">
                <form id="formEnviarPreOrden" enctype="multipart/form-data">
                    <input type="hidden" id="env-ot" name="ot">

                    <div class="form-group" style="margin-bottom: 15px;">
                        <label for="env-destinatario">Destinatario(s):</label>
                        <input type="text" id="env-destinatario" name="destinatario" class="form-control" required value="jaxer020406@gmail.com">
                        <span style="font-size: 0.8em; color: #64748b; margin-top: 4px;">Separa múltiples correos usando comas (,).</span>
                    </div>

                    <div class="form-group" style="margin-bottom: 20px;">
                        <label for="env-fecha-entrega">Fecha de Entrega:</label>
                        <input type="date" id="env-fecha-entrega" name="fecha_entrega" class="form-control" required>
                        <span style="font-size: 0.8em; color: #64748b; margin-top: 4px;">Indica la fecha de entrega acordada para imprimirla en el reporte.</span>
                    </div>



                    <div class="form-group" style="margin-bottom: 20px;">
                        <label>Archivos de la OT disponibles para adjuntar:</label>
                        <div id="env-server-files-container" style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; padding: 15px; max-height: 220px; overflow-y: auto; display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 8px; justify-items: center;">
                            <div class="alm-spinner" style="border-top-color: #033966; display: block; margin: 10px auto; grid-column: 1 / -1;"></div>
                            <span style="text-align: center; color: #64748b; grid-column: 1 / -1;">Cargando archivos de la OT...</span>
                        </div>
                    </div>

                    <div class="form-group" style="margin-bottom: 30px;">
                        <label class="custom-file-upload-label" style="font-weight: 700; color: #033966; display: block; margin-bottom: 8px;">Adjuntar archivos adicionales desde tu equipo:</label>
                        <div class="custom-file-dropzone">
                            <input type="file" id="env-archivos-adicionales" name="archivos_adicionales[]" class="custom-file-input" multiple>
                            <div class="dropzone-content">
                                <img src="{{ asset('images/anadir.png') }}" class="dropzone-icon" style="width: 40px; height: 40px; margin-bottom: 8px; object-fit: contain;">
                                <span class="dropzone-text">Arrastra archivos aquí o haz clic para buscar</span>
                                <span class="dropzone-subtext">Soporta múltiples archivos PDF o imágenes</span>
                            </div>
                        </div>
                        <div id="env-archivos-adicionales-list" style="margin-top: 10px; display: flex; flex-wrap: wrap; gap: 8px;"></div>
                    </div>

                    <div class="form-actions" style="text-align: center;">
                        <button type="submit" class="btn-save-preorden" id="btn-submit-envio" style="background: #005194; box-shadow: 0 4px 15px rgba(0, 81, 148, 0.3);">
                            Enviar Correo con Adjuntos
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>


    {{-- ── MODAL: LIBERACIÓN DE MODELOS (Calidad) ──────────────────── --}}
    @include('almacen.partials._modal_liberacion_modelos')

    <script>
        window.almacenRoutes = {
            archivos: "{{ route('almacen.fundicion.archivos') }}",
            serve: "{{ route('almacen.fundicion.serve') }}",
            confirmarModelo: "{{ route('almacen.fundicion.confirmarModelo') }}",
            getOtData: "{{ route('almacen.fundicion.getOtData') }}",
            storePreOrden: "{{ route('almacen.fundicion.storePreOrden') }}",
            sendEmailPreOrden: "{{ route('almacen.fundicion.sendEmailPreOrden') }}",
            getLiberacion: "{{ route('almacen.fundicion.getLiberacion') }}",
            submitLiberacion: "{{ route('almacen.fundicion.submitLiberacion') }}",
        };
        window.almacenAppAssets = {
            liberar    : "{{ asset('images/Liberar.png') }}",
            descarga   : "{{ asset('images/Descarga.png') }}",
            recibido   : "{{ asset('images/Recibido.png') }}",
            aprobado   : "{{ asset('images/Aprobado.png') }}",
            rechazado  : "{{ asset('images/Rechazado.png') }}",
            guardado   : "{{ asset('images/Guardado.png') }}",
            revisando  : "{{ asset('images/Revisando.png') }}",
            espera     : "{{ asset('images/Espera.png') }}",
        };
    </script>

@endsection
