@extends('layouts.appMenu')

@section('head')
    @php
        $perfil = Auth::user()->perfil;
        $deptName = ($perfil == 1 || $perfil == 2) ? 'Administración' : ($perfil == 4 ? 'Calidad' : 'Almacén');
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
            $deptName = ($perfil == 1 || $perfil == 2) ? 'Administración' : ($perfil == 4 ? 'Calidad' : 'Almacén');
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
                                    {{ preg_replace('/_\d{8}_\d{6}_.*/', '', $otOption) }}
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
                                                    $ext = strtolower(pathinfo($f, PATHINFO_EXTENSION));
                                                    $isPdf   = $ext === 'pdf';
                                                    $isImage = in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp']);

                                                    if (!$isPdf && !$isImage) continue;

                                                    $fNorm = str_replace('\\', '/', $f);
                                                    $dirNorm = str_replace('\\', '/', $ayudasDir);
                                                    $relativePath = ltrim(str_replace($dirNorm, '', $fNorm), '/');

                                                    if (str_starts_with($relativePath, 'preordenes/')) {
                                                        $otrosArchivos[] = [
                                                            'nombre' => $relativePath,
                                                            'url'    => route('almacen.fundicion.serve', ['ot' => $reg->ot, 'archivo' => $relativePath, 'tipo' => 'otro']),
                                                            'tipo'   => $isImage ? 'imagen' : 'otro',
                                                        ];
                                                    } elseif ($isPdf) {
                                                        $ayudasArchivos[] = [
                                                            'nombre' => $relativePath,
                                                            'url'    => route('almacen.fundicion.serve', ['ot' => $reg->ot, 'archivo' => $relativePath, 'tipo' => 'ayuda']),
                                                            'tipo'   => 'ayuda',
                                                        ];
                                                    }
                                                }
                                        }
                                        
                                        // Buscar liberaciones y SCARs PDF
                                        $liberacionesPath = storage_path('app/public/liberaciones_pdf');
                                        $otSanitizada = preg_replace('/[^\w\s\-]/', '', $reg->ot);
                                        $otSanitizada = preg_replace('/[\s]+/', '_', trim($otSanitizada));
                                        
                                        $baseNames = array_map(function($item) {
                                            return basename($item['nombre']);
                                        }, $otrosArchivos);

                                        if (file_exists($liberacionesPath)) {
                                            // Buscar LDM PDFs
                                            $ldmPattern = "{$liberacionesPath}/F-CCL-LDM_*_{$otSanitizada}*.pdf";
                                            foreach (glob($ldmPattern) ?: [] as $f) {
                                                $base = basename($f);
                                                if (!in_array($base, $baseNames)) {
                                                    $otrosArchivos[] = [
                                                        'nombre' => $base,
                                                        'url' => route('almacen.fundicion.serve', ['ot' => $reg->ot, 'archivo' => $base, 'tipo' => 'liberacion']),
                                                        'tipo' => 'liberacion'
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
                                                if (!in_array($base, $baseNames)) {
                                                    $otrosArchivos[] = [
                                                        'nombre' => $base,
                                                        'url' => route('almacen.fundicion.serve', ['ot' => $reg->ot, 'archivo' => $base, 'tipo' => 'liberacion']),
                                                        'tipo' => 'liberacion'
                                                    ];
                                                    $baseNames[] = $base;
                                                }
                                            }
                                        }

                                        // Aplicar filtros de visibilidad según perfil de usuario
                                        $userPerfil = Auth::user()->perfil;
                                        if ($userPerfil != 1 && $userPerfil != 2) {
                                            $filteredOtros = [];
                                            foreach ($otrosArchivos as $archivo) {
                                                $isPreorden = (in_array($archivo['tipo'], ['otro', 'imagen']) || str_starts_with($archivo['nombre'], 'preordenes/'));
                                                
                                                if ($userPerfil == 4) { // Calidad
                                                    // Calidad solo ve preordenes si pre_orden_email_sent es true
                                                    if (!$isPreorden || $reg->pre_orden_email_sent) {
                                                        $filteredOtros[] = $archivo;
                                                    }
                                                } elseif ($userPerfil == 5) { // Almacén
                                                    // Almacén solo ve PDFs de Calidad si se envió la alerta (aprobado o scar alertado)
                                                    if ($isPreorden) {
                                                        $filteredOtros[] = $archivo;
                                                    } else {
                                                        $calidadAlertaEnviada = ($reg->calidad_revision_status === 'aprobado' || 
                                                            \App\Models\ScarModelo::where('ot', '=', $reg->ot, 'and')->where('estatus', '=', 'alertado', 'and')->exists());
                                                        if ($calidadAlertaEnviada) {
                                                            $filteredOtros[] = $archivo;
                                                        }
                                                    }
                                                }
                                            }
                                            $otrosArchivos = $filteredOtros;
                                        }

                                        $countAyudas = count($ayudasArchivos);
                                        $countOtros = count($otrosArchivos);
                                        $count = $countDibujos + $countAyudas + $countOtros;
                                    @endphp

                                    {{-- Fila principal --}}
                                    <tr data-ot="{{ $reg->ot }}">
                                        <td>
                                            <div class="alm-ot-label">{{ preg_replace('/_\d{8}_\d{6}_.*/', '', $reg->ot) }}</div>
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
                                                {{-- BLOQUE 1: Dibujos solo visibles si la alerta fue enviada desde manage_documentation.js --}}
                                                @if ($countDibujos > 0 && $reg->alert_sent_at)
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
                                                @elseif ($countDibujos > 0 && !$reg->alert_sent_at)
                                                    {{-- Dibujos existentes pero alerta aún no enviada desde Ingeniería --}}
                                                    <div style="margin-top: 15px; padding: 14px 18px; background: rgba(0,81,148,0.06); border: 1.5px dashed #005194; border-radius: 10px; color: #005194; font-size: 0.93em;">
                                                        <strong>Dibujos pendientes:</strong> Los dibujos estarán disponibles una vez que Ingeniería envíe la alerta oficial desde el sistema de gestión documental.
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

                                                {{-- BLOQUE 4: Renombrar sección a "Documentos Aprobados" --}}
                                                @if ($countOtros > 0)
                                                    <h3
                                                        style="margin-top: 25px; margin-bottom: 10px; color: #155724; border-bottom: 2px solid #155724; padding-bottom: 5px;">
                                                        Documentos Aprobados</h3>
                                                    <div class="alm-pdf-grid">
                                                        @foreach ($otrosArchivos as $otroArchivo)
                                                            @if ($otroArchivo['tipo'] === 'imagen')
                                                                {{-- Tarjeta para imágenes (fotos de evidencia) --}}
                                                                <div class="dibujos-file-card card-otro card-imagen"
                                                                    style="animation-delay: {{ $loop->index * 0.05 }}s; border-left-color: #0369a1;">
                                                                    <div class="file-icon-wrapper"
                                                                        onclick="almacenVerPdf('{{ $reg->ot }}', '{{ $otroArchivo['nombre'] }}', 'otro')"
                                                                        style="cursor: pointer;" title="Ver imagen">
                                                                        <img src="{{ $otroArchivo['url'] }}"
                                                                            class="file-icon-img-thumb"
                                                                            alt="{{ basename($otroArchivo['nombre']) }}"
                                                                            style="width:100%; height:80px; object-fit:cover; border-radius:6px; border:1px solid #bae6fd;">
                                                                    </div>
                                                                    <div class="file-name" style="cursor: pointer;"
                                                                        title="Ver imagen"
                                                                        onclick="almacenVerPdf('{{ $reg->ot }}', '{{ $otroArchivo['nombre'] }}', 'otro')">
                                                                        {{ basename($otroArchivo['nombre']) }}</div>
                                                                    <div class="file-actions" style="display: flex; gap: 5px;">
                                                                        <button class="btn-dibujos btn-dibujos-sm btn-ver" style="background-color: #0369a1; color: white;"
                                                                            onclick="almacenVerPdf('{{ $reg->ot }}', '{{ $otroArchivo['nombre'] }}', 'otro')">Ver</button>
                                                                        <button class="btn-dibujos btn-dibujos-sm btn-eliminar" style="background-color: #dc3545; color: white;"
                                                                            onclick="almacenEliminarOtroArchivo('{{ $reg->ot }}', '{{ $otroArchivo['nombre'] }}', 'otro', this)">Eliminar</button>
                                                                    </div>
                                                                </div>
                                                            @else
                                                                {{-- Tarjeta para PDFs y otros documentos --}}
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
                                                                    <div class="file-actions" style="display: flex; gap: 5px;">
                                                                        <button class="btn-dibujos btn-dibujos-sm btn-ver" style="background-color: #155724; color: white;"
                                                                            onclick="almacenVerPdf('{{ $reg->ot }}', '{{ $otroArchivo['nombre'] }}', '{{ $otroArchivo['tipo'] }}')">Ver</button>
                                                                        <button class="btn-dibujos btn-dibujos-sm btn-eliminar" style="background-color: #dc3545; color: white;"
                                                                            onclick="almacenEliminarOtroArchivo('{{ $reg->ot }}', '{{ $otroArchivo['nombre'] }}', '{{ $otroArchivo['tipo'] }}', this)">Eliminar</button>
                                                                    </div>
                                                                </div>
                                                            @endif
                                                        @endforeach
                                                    </div>
                                                @endif

                                                {{-- ── SECCIÓN CONTROL DE MODELOS (Solo Almacén y OTs Activas) ── --}}
                                                @if (Auth::user()->perfil != 4 && $estado === 'activa')
                                                    @php
                                                        $controlDisabled = ($reg->tiene_modelo || $reg->pre_orden_email_sent) ? 'opacity: 0.5; pointer-events: none;' : '';
                                                        $hideSiNo = ($reg->pre_orden_sent || $reg->pre_orden_email_sent) ? 'display: none;' : '';
                                                        $hideEditMail = ($reg->pre_orden_sent && !$reg->pre_orden_email_sent) ? '' : 'display: none;';
                                                    @endphp
                                                    <div class="lib-calidad-card" id="control-modelo-{{ md5($reg->ot) }}" style="{{ $controlDisabled }}">
                                                        <div class="lib-calidad-card-header">
                                                            <img src="{{ asset('images/almacen.png') }}" alt="Almacén" style="width:38px;height:38px;object-fit:contain;flex-shrink:0;">
                                                            <div style="overflow:hidden;">
                                                                <span class="lib-calidad-card-title">Control de Modelos &mdash; Almacén</span>
                                                                <span class="lib-calidad-card-ot">{{ preg_replace('/_\d{8}_\d{6}_.*/', '', $reg->ot) }}</span>
                                                            </div>
                                                        </div>
                                                        <div class="lib-calidad-card-body">
                                                            <div class="lib-calidad-action-row">
                                                                <h4 class="lib-calidad-card-prompt">
                                                                    @if ($reg->tiene_modelo)
                                                                        ¡Modelo recibido y procesado! Pendiente de que Calidad lo revise.
                                                                    @elseif ($reg->pre_orden_email_sent)
                                                                        Pre-orden enviada por correo. En espera de revisión por Calidad.
                                                                    @elseif ($reg->pre_orden_sent)
                                                                        Pre-orden lista. Puedes seguir editando los datos o enviarla por correo.
                                                                    @else
                                                                        ¿Ya cuentas con el modelo de esta OT o necesitas generar una pre-orden?
                                                                    @endif
                                                                </h4>
                                                                <div class="lib-calidad-card-btns">
                                                                    <button class="btn-modelo btn-modelo-si" onclick="abrirModalConfirmarModelo('{{ $reg->ot }}', '{{ md5($reg->ot) }}')" title="Sí, cuento con el modelo de esta OT" style="{{ $hideSiNo }}">
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
                                                                <span class="lib-calidad-card-ot">{{ preg_replace('/_\d{8}_\d{6}_.*/', '', $reg->ot) }}</span>
                                                            </div>
                                                        </div>
                                                        <div class="lib-calidad-card-body">
                                                        @if ($reg->calidad_revision_status === 'rechazado')
                                                            <div class="lib-estado-badge lib-estado-rechazado" style="padding: 12px 16px;">
                                                                <div style="display: flex; align-items: center; gap: 8px;">
                                                                    <img src="{{ asset('images/Rechazado.png') }}" alt="" style="width:18px;height:18px;object-fit:contain;flex-shrink:0;">
                                                                    <span>Liberacion rechazada anteriormente. Puedes revisar y volver a emitir un veredicto.</span>
                                                                </div>
                                                            </div>
                                                        @elseif (is_null($reg->calidad_revision_status) && !$reg->pre_orden_sent && !$reg->tiene_modelo)
                                                            <div class="lib-estado-badge lib-estado-info">
                                                                En espera de que Almacén envíe la información necesaria para realizar la liberación.
                                                            </div>
                                                        @elseif ($reg->calidad_revision_status === 'pendiente')
                                                            <div class="lib-estado-badge lib-estado-guardado">
                                                                <img src="{{ asset('images/Guardado.png') }}" alt="" style="width:18px;height:18px;object-fit:contain;flex-shrink:0;">
                                                                Datos capturados como borrador.
                                                            </div>
                                                        @endif
                                                        @php
                                                            $borradorPendiente = \App\Models\LiberacionModeloFundicion::where('ot', $reg->ot)
                                                                ->where('estado', 'pendiente')
                                                                ->first();
                                                            $scarModelo = \App\Models\ScarModelo::where('ot', $reg->ot)->first();
                                                            $reqFotos = $scarModelo && ($scarModelo->evidencia_fotos || $scarModelo->evidencia_otro);
                                                            $clasesActivas = collect($reg->ayudas_config ?? [])
                                                                ->filter(fn($c) => !str_contains(strtolower($c), 'opcional'))
                                                                ->values()
                                                                ->toArray();

                                                            // Determinar si todas las clases activas tienen datos guardados (como borrador pendiente)
                                                            $todosGuardados = true;
                                                            $contClasesConDatos = 0;
                                                            foreach ($clasesActivas as $clName) {
                                                                $clLow = strtolower($clName);
                                                                $tipo = null;
                                                                if (strpos($clLow, 'fondo') !== false) $tipo = 'Fondo';
                                                                elseif (strpos($clLow, 'obturador') !== false) $tipo = 'Obturador';
                                                                elseif (strpos($clLow, 'molde') !== false) $tipo = 'Molde';
                                                                elseif (strpos($clLow, 'bombillo') !== false) $tipo = 'Bombillo';

                                                                if ($tipo) {
                                                                    $hasDraft = \App\Models\LiberacionModeloFundicion::where('ot', '=', $reg->ot, 'and')
                                                                        ->where('tipo_modelo', '=', $tipo, 'and')
                                                                        ->where('estado', '=', 'pendiente', 'and')
                                                                        ->exists();
                                                                    if (!$hasDraft) {
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
                                                            $hasRechazoBorrador = \App\Models\LiberacionModeloFundicion::where('ot', '=', $reg->ot, 'and')
                                                                ->where('estado', '=', 'pendiente', 'and')
                                                                ->where('decision', '=', 'rechazar', 'and')
                                                                ->exists();

                                                            $borradorRechazado = \App\Models\LiberacionModeloFundicion::where('ot', '=', $reg->ot, 'and')
                                                                ->where('estado', '=', 'pendiente', 'and')
                                                                ->where('decision', '=', 'rechazar', 'and')
                                                                ->first();

                                                            $tiposGuardados = \App\Models\LiberacionModeloFundicion::where('ot', '=', $reg->ot, 'and')
                                                                ->where('estado', '=', 'pendiente', 'and')
                                                                ->pluck('tipo_modelo')
                                                                ->toArray();
                                                            $tiposLabel = implode(', ', $tiposGuardados);
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
                                                                     Proceso de liberación en curso (capturados: {{ $contClasesConDatos }} de {{ count($clasesActivas) }}).
                                                                 @elseif ($reg->calidad_revision_status === 'rechazado')
                                                                     El modelo fue rechazado antes. ¿Quieres revisarlo de nuevo?
                                                                 @else
                                                                     ¿Qué deseas hacer con este modelo? ¿Lo apruebas o lo rechazas?
                                                                 @endif
                                                             </h4>
                                                             <div class="lib-calidad-card-btns">
                                                                 @if ($todosGuardados)
                                                                     {{-- Todas las clases están guardadas: se puede editar o enviar alerta --}}
                                                                     <button class="btn-calidad-action btn-calidad-iniciar"
                                                                             onclick="abrirModalLiberacionUnificado('{{ $reg->ot }}', {{ json_encode($clasesActivas) }}, {{ json_encode($reg->ayudas_config ?? []) }})"
                                                                             title="Editar borrador del formato de liberación F-CCL-LDM">
                                                                         <img src="{{ asset('images/editar-informacion.png') }}" alt="">
                                                                         <span>Editar Datos</span>
                                                                     </button>

                                                                     @if ($hasRechazoBorrador)
                                                                         @if (!$scarModelo)
                                                                             <button class="btn-calidad-action btn-calidad-borrador"
                                                                                     onclick="abrirModalScar('{{ $reg->ot }}', '{{ $borradorRechazado->tipo_modelo }}', '{{ $borradorRechazado->motivo_rechazo }}')"
                                                                                     title="Generar el formato de acción correctiva SCAR">
                                                                                 <img src="{{ asset('images/pdf.png') }}" alt="">
                                                                                 <span>Generar Formato SCAR</span>
                                                                             </button>
                                                                         @else
                                                                             <button class="btn-calidad-action btn-calidad-edit"
                                                                                     onclick="abrirModalScar('{{ $reg->ot }}', '{{ $borradorRechazado->tipo_modelo }}', '{{ $borradorRechazado->motivo_rechazo }}')"
                                                                                     title="Editar el formato de acción correctiva SCAR">
                                                                                 <img src="{{ asset('images/editar-informacion.png') }}" alt="">
                                                                                 <span>Editar SCAR</span>
                                                                             </button>
                                                                             <button class="btn-calidad-action btn-calidad-email"
                                                                                     onclick="abrirModalEnviarAlertaLiberacion('{{ $reg->ot }}', 'rechazar', '{{ $tiposLabel }}', {{ $reqFotos ? 'true' : 'false' }})"
                                                                                     title="Subir documentos firmados y enviar alerta global de rechazo">
                                                                                 <img src="{{ asset('images/enviando.png') }}" alt="">
                                                                                 <span>Enviar Alerta</span>
                                                                             </button>
                                                                         @endif
                                                                     @else
                                                                         {{-- Borrador Aprobado: no necesita SCAR --}}
                                                                         <button class="btn-calidad-action btn-calidad-email"
                                                                                 onclick="abrirModalEnviarAlertaLiberacion('{{ $reg->ot }}', 'aprobar', '{{ $tiposLabel }}', false)"
                                                                                 title="Subir formato escaneado y enviar alerta global de aprobación">
                                                                             <img src="{{ asset('images/enviando.png') }}" alt="">
                                                                             <span>Enviar Alerta</span>
                                                                         </button>
                                                                     @endif
                                                                 @else
                                                                     {{-- No todas las clases están capturadas --}}
                                                                     <button class="btn-calidad-action btn-calidad-iniciar"
                                                                             @if (!$reg->pre_orden_email_sent && !$reg->tiene_modelo)
                                                                                 disabled
                                                                                 style="opacity: 0.55; cursor: not-allowed;"
                                                                                 title="En espera de que Almacén envíe la información necesaria para realizar la liberación"
                                                                             @else
                                                                                 title="{{ $contClasesConDatos > 0 ? 'Continuar con el proceso de liberación' : 'Iniciar el proceso de liberación' }}"
                                                                             @endif
                                                                             onclick="abrirModalLiberacionUnificado('{{ $reg->ot }}', {{ json_encode($clasesActivas) }}, {{ json_encode($reg->ayudas_config ?? []) }})">
                                                                         <img src="{{ asset('images/Liberar.png') }}" alt="">
                                                                         <span>{{ $contClasesConDatos > 0 ? 'Continuar con el proceso de liberación' : 'Empezar con el proceso de liberación' }}</span>
                                                                     </button>
                                                                 @endif
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

    {{-- ── MINI-MODAL: CONFIRMAR MODELO CON DOCUMENTOS OBLIGATORIOS ── --}}
    <div id="modalConfirmarModelo" class="alm-modal" role="dialog" aria-modal="true">
        <div class="alm-modal-content" style="max-width: 560px;">
            <div class="alm-modal-header" style="background: linear-gradient(135deg, #0a8504, #064e03); border-bottom: 2px solid #064e03;">
                <div class="div-cerrar">
                    <button type="button" class="btn-cerrar" onclick="cerrarModalConfirmarModelo()">
                        <img class="img-cerrar" src="{{ asset('images/cerrar.png') }}" alt="Cerrar">
                    </button>
                </div>
                <h3 style="color:#fff; margin:0; font-size:1.15em;">Confirmar disponibilidad del Modelo Físico</h3>
            </div>
            <div class="alm-modal-body">
                <form id="formConfirmarModelo" enctype="multipart/form-data">
                    <input type="hidden" id="cm-ot" name="ot">
                    <input type="hidden" id="cm-id-hash" name="id_hash">

                    <div style="padding: 6px 0 14px; color: #334155; font-size:0.97em;">
                        <p style="margin-bottom:10px;">¿Confirmas que cuentas físicamente con el modelo para esta OT?</p>
                        <p style="background:#fef9c3; border:1px solid #fde047; border-radius:8px; padding:10px 14px; color:#713f12; font-size:0.9em;">
                            <strong>⚠ Documentos requeridos:</strong> Debes adjuntar los documentos que acrediten la recepción del modelo (ej. remisión, hoja de entrega, fotos).
                        </p>
                    </div>

                    <div class="form-group" style="margin-bottom: 18px;">
                        <label for="cm-archivos" style="font-weight:700; color:#334155; display:block; margin-bottom:6px;">
                            Adjuntar documentos de recepción <span style="color:#9c0300;">*</span>
                        </label>
                        <input type="file" id="cm-archivos" name="archivos[]" class="form-control"
                            multiple accept=".pdf,image/*" required>
                        <span style="font-size:0.82em; color:#64748b; margin-top:4px; display:block;">PDFs o imágenes (fotos, hojas de entrega, remisiones).</span>
                    </div>

                    <div class="form-actions" style="text-align:center; margin-top:20px;">
                        <button type="button" onclick="cerrarModalConfirmarModelo()" style="margin-right:10px; padding:10px 20px; background:#e2e8f0; border:none; border-radius:8px; font-weight:600; cursor:pointer;">Cancelar</button>
                        <button type="submit" class="btn-save-preorden" id="btn-submit-confirmar-modelo"
                            style="background: linear-gradient(135deg, #0a8504, #064e03); box-shadow: 0 4px 15px rgba(10,133,4,0.35);">
                            <img src="{{ asset('images/Aprobado.png') }}" alt="" style="width:16px;height:16px;vertical-align:middle;margin-right:6px;">
                            Confirmar y Registrar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- ── MINI-MODAL: ENVIAR ALERTA DE LIBERACION (APROBADO/RECHAZADO) ── --}}
    <div id="modalEnviarAlertaLiberacion" class="alm-modal" role="dialog" aria-modal="true">
        <div class="alm-modal-content" style="max-width: 560px;">
            <div class="alm-modal-header" id="alerta-lib-header" style="background: linear-gradient(135deg, #0284c7, #0369a1); border-bottom: 2px solid #0369a1;">
                <div class="div-cerrar">
                    <button type="button" class="btn-cerrar" onclick="cerrarModalEnviarAlertaLiberacion()">
                        <img class="img-cerrar" src="{{ asset('images/cerrar.png') }}" alt="Cerrar">
                    </button>
                </div>
                <h3 style="color:#fff; margin:0; font-size:1.15em;" id="alerta-lib-title">Enviar Alerta de Liberación</h3>
            </div>
            <div class="alm-modal-body">
                <form id="formEnviarAlertaLiberacion" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" id="al-ot" name="ot">
                    <input type="hidden" id="al-decision" name="decision">
                    <input type="hidden" id="al-tipo-modelo" name="tipo_modelo">

                    <div style="padding: 6px 0 14px; color: #334155; font-size:0.97em;">
                        <p style="margin-bottom:10px;" id="al-prompt-text"></p>
                        <p style="background:#fef9c3; border:1px solid #fde047; border-radius:8px; padding:10px 14px; color:#713f12; font-size:0.9em;">
                            <strong>⚠ Campos obligatorios:</strong> Todos los campos marcados con (*) son estrictamente requeridos para finalizar el proceso.
                        </p>
                    </div>

                    {{-- FECHA --}}
                    <div class="form-group" style="margin-bottom: 18px;">
                        <label for="al-fecha" style="font-weight:700; color:#334155; display:block; margin-bottom:6px;">
                            Fecha de Emisión / Entrega <span style="color:#9c0300;">*</span>
                        </label>
                        <input type="date" id="al-fecha" name="fecha" class="form-control" required>
                    </div>

                    {{-- FORMATO ESCANEADO --}}
                    <div class="form-group" style="margin-bottom: 18px;">
                        <label for="al-formato-escaneado" style="font-weight:700; color:#334155; display:block; margin-bottom:6px;">
                            Formato de Liberación Escaneado (PDF) <span style="color:#9c0300;">*</span>
                        </label>
                        <input type="file" id="al-formato-escaneado" name="formato_escaneado" class="form-control" accept=".pdf" required>
                    </div>

                    {{-- SCAR ESCANEADO (SÓLO SI ES RECHAZO) --}}
                    <div class="form-group" id="al-scar-container" style="margin-bottom: 18px; display: none;">
                        <label for="al-scar-escaneado" style="font-weight:700; color:#334155; display:block; margin-bottom:6px;">
                            Formato SCAR Escaneado (PDF) <span style="color:#9c0300;">*</span>
                        </label>
                        <input type="file" id="al-scar-escaneado" name="scar_escaneado" class="form-control" accept=".pdf">
                    </div>

                    {{-- FOTOS Y OTROS ARCHIVOS (SÓLO SI ES RECHAZO Y TIENE CONFIG) --}}
                    <div class="form-group" id="al-fotos-container" style="margin-bottom: 18px; display: none;">
                        <label for="al-fotos" style="font-weight:700; color:#334155; display:block; margin-bottom:6px;">
                            Fotografías y otros archivos adicionales <span style="color:#9c0300;">*</span>
                        </label>
                        <input type="file" id="al-fotos" name="fotos[]" class="form-control" multiple accept="image/*,.pdf,.zip">
                    </div>

                    <div style="display:flex; justify-content:flex-end; margin-top:24px; padding-top:16px; border-top:1px solid #e2e8f0;">
                        <button type="button" onclick="cerrarModalEnviarAlertaLiberacion()" style="margin-right:10px; padding:10px 20px; background:#e2e8f0; border:none; border-radius:8px; font-weight:600; cursor:pointer;">Cancelar</button>
                        <button type="submit" class="btn-save-preorden" id="btn-submit-alerta-liberacion" style="background:#0284c7; color:#fff; border:none; padding:10px 20px; border-radius:8px; font-weight:600; cursor:pointer;">
                            Enviar Alerta
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

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
                <p id="env-po-modal-subtitle" class="lib-modal-subtitle" style="color: #bae6fd; font-size: 0.9em; margin-top: 4px; margin-bottom: 0;"></p>
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
                        <div id="env-server-files-container" style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; padding: 15px; max-height: 420px; overflow-y: auto; display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 8px; justify-items: center;">
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


    {{-- ── MODAL: ENVIAR ALERTA SCAR (Paso 2) ── --}}
    <div id="modalEnviarScar" class="alm-modal" role="dialog" aria-modal="true">
        <div class="alm-modal-content lib-modal-content" style="max-width: 800px;">
            <div class="alm-modal-header lib-modal-header lib-modal-header-rechazo">
                <div class="div-cerrar">
                    <button type="button" class="btn-cerrar" onclick="cerrarModalEnviarScar()">
                        <img class="img-cerrar" src="{{ asset('images/cerrar.png') }}" alt="Cerrar">
                    </button>
                </div>
                <div class="lib-header-top">
                    <h3 class="lib-modal-title-text" style="color: #ffffff;">Enviar Alerta SCAR (Paso 2)</h3>
                    <p id="env-scar-modal-subtitle" class="lib-modal-subtitle" style="color: #ffd1d1;"></p>
                </div>
            </div>
            <div class="alm-modal-body lib-modal-body">
                <form id="formEnviarScar" enctype="multipart/form-data" autocomplete="off">
                    <input type="hidden" id="env-scar-ot" name="ot">

                    {{-- Destinatario --}}
                    <div class="form-group" style="margin-bottom: 16px;">
                        <label for="env-scar-destinatario" style="font-weight: 700; color: #334155; display: block; margin-bottom: 4px;">
                            Destinatario(s) (separados por coma):
                        </label>
                        <input type="text" id="env-scar-destinatario" name="destinatario" class="form-control" required value="jaxer020406@gmail.com">
                    </div>

                    {{-- Fecha Compromiso (Paso 2 Mandatorio) --}}
                    <div class="form-group" style="margin-bottom: 16px;">
                        <label for="env-scar-fecha-compromiso" style="font-weight: 700; color: #334155; display: block; margin-bottom: 4px;">
                            Fecha Compromiso de Devolución (Obligatoria):
                        </label>
                        <input type="date" id="env-scar-fecha-compromiso" name="fecha_compromiso" class="form-control" required>
                    </div>

                    {{-- SCAR Firmado (PDF, Mandatorio) --}}
                    <div class="form-group" style="margin-bottom: 20px;">
                        <label for="env-scar-pdf-firmado" style="font-weight: 700; color: #9c0300; display: block; margin-bottom: 4px;">
                            Subir SCAR Firmado Físicamente (PDF Obligatorio):
                        </label>
                        <input type="file" id="env-scar-pdf-firmado" name="pdf_firmado" class="form-control" accept=".pdf" required>
                    </div>

                    {{-- Evidencia adjunta checklists --}}
                    <div class="lib-section-block" style="margin-bottom: 20px; border-top: 1px solid #e2e8f0; padding-top: 16px;">
                        <h5 style="font-weight: 700; color: #334155; margin-bottom: 8px;">Documentos a Adjuntar:</h5>

                        {{-- Dibujos del Servidor --}}
                        <div style="margin-bottom: 12px;">
                            <label style="font-weight: 700; color: #475569; display: block; margin-bottom: 4px;">Dibujos Autorizados:</label>
                            <div id="env-scar-dibujos-container" style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 10px; max-height: 120px; overflow-y: auto;">
                                <span style="font-size:0.9em; color:#64748b;">No hay dibujos disponibles</span>
                            </div>
                        </div>

                        {{-- Ayudas del Servidor --}}
                        <div style="margin-bottom: 12px;">
                            <label style="font-weight: 700; color: #475569; display: block; margin-bottom: 4px;">Ayudas Visuales:</label>
                            <div id="env-scar-ayudas-container" style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 10px; max-height: 120px; overflow-y: auto;">
                                <span style="font-size:0.9em; color:#64748b;">No hay ayudas visuales disponibles</span>
                            </div>
                        </div>

                        {{-- Otros Documentos del Servidor (LDM, SCAR, Pre-Orden, etc.) --}}
                        <div style="margin-bottom: 12px;">
                            <label style="font-weight: 700; color: #475569; display: block; margin-bottom: 4px;">Otros Documentos (LDM, SCAR, Pre-Orden…):</label>
                            <div id="env-scar-otros-container" style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 10px; max-height: 120px; overflow-y: auto;">
                                <span style="font-size:0.9em; color:#64748b;">No hay otros documentos disponibles</span>
                            </div>
                        </div>
                    </div>

                    {{-- Fotografias y otros archivos a adjuntar en este envío --}}
                    <div class="lib-section-block" style="margin-bottom: 20px; background-color: #fff8f8; border: 1px solid #fecaca; border-radius: 8px; padding: 14px;">
                        <h5 style="font-weight: 700; color: #9c0300; margin-bottom: 12px;">Subir Evidencia Adicional al Envío:</h5>

                        <div class="form-group" style="margin-bottom: 12px;">
                            <label for="env-scar-evidencia-fotos" style="font-weight: 600; color: #475569; display: block; margin-bottom: 4px;">
                                Fotografías (Imágenes):
                            </label>
                            <input type="file" id="env-scar-evidencia-fotos" name="evidencia_fotos_files[]"
                                multiple accept="image/*" class="form-control">
                        </div>

                        <div class="form-group">
                            <label for="env-scar-evidencia-otro" style="font-weight: 600; color: #475569; display: block; margin-bottom: 4px;">
                                Otros Archivos (PDFs adicionales):
                            </label>
                            <input type="file" id="env-scar-evidencia-otro" name="evidencia_otro_files[]"
                                multiple accept=".pdf" class="form-control">
                        </div>
                    </div>

                    {{-- Boton de Envio --}}
                    <div class="lib-actions" style="display: flex; justify-content: center; margin-top: 8px;">
                        <button type="submit" class="btn-lib-send">
                            <img src="{{ asset('images/enviando.png') }}" alt="" style="width: 18px; height: 18px;">
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
            generateScar: "{{ route('almacen.fundicion.generateScar') }}",
            getScar: "{{ route('almacen.fundicion.getScar') }}",
            sendScarAlert: "{{ route('almacen.fundicion.sendScarAlert') }}",
            enviarAlertaLiberacion: "{{ route('almacen.fundicion.enviarAlertaLiberacion') }}",
            deleteFile: "{{ route('almacen.fundicion.deleteFile') }}",
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
