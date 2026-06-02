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
                            <img src="{{ asset('images/noPieces.png') }}" alt="Sin resultados" style="width: 64px; opacity: 0.5;">
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
                                                $isPdf = $ext === 'pdf';
                                                $isImage = in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp']);

                                                if (!$isPdf && !$isImage)
                                                    continue;

                                                $fNorm = str_replace('\\', '/', $f);
                                                $dirNorm = str_replace('\\', '/', $ayudasDir);
                                                $relativePath = ltrim(str_replace($dirNorm, '', $fNorm), '/');

                                                if (str_starts_with($relativePath, 'preordenes/')) {
                                                    $otrosArchivos[] = [
                                                        'nombre' => $relativePath,
                                                        'url' => route('almacen.fundicion.serve', ['ot' => $reg->ot, 'archivo' => $relativePath, 'tipo' => 'otro']),
                                                        'tipo' => $isImage ? 'imagen' : 'otro',
                                                    ];
                                                } elseif ($isPdf) {
                                                    $ayudasArchivos[] = [
                                                        'nombre' => $relativePath,
                                                        'url' => route('almacen.fundicion.serve', ['ot' => $reg->ot, 'archivo' => $relativePath, 'tipo' => 'ayuda']),
                                                        'tipo' => 'ayuda',
                                                    ];
                                                }
                                            }
                                        }

                                        // Buscar liberaciones y SCARs PDF
                                        $liberacionesPath = storage_path('app/public/liberaciones_pdf');
                                        $otSanitizada = preg_replace('/[^\w\s\-]/', '', $reg->ot);
                                        $otSanitizada = preg_replace('/[\s]+/', '_', trim($otSanitizada));

                                        $baseNames = array_map(function ($item) {
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

                                        $archivosAprobados = [];
                                        $archivosRechazados = [];
                                        foreach ($otrosArchivos as $archivo) {
                                            $nameLow = strtolower($archivo['nombre']);
                                            if (strpos($nameLow, 'documentos_rechazados') !== false || strpos($nameLow, 'rechazado') !== false || strpos($nameLow, 'scar') !== false) {
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
                                                            <img src="{{ asset('images/Aprobado.png') }}" alt="Aprobado"
                                                                style="width: 38px; height: 38px;">
                                                        </span>
                                                    @elseif ($libStatus === 'rechazado')
                                                        <span class="badge-modelo-rechazado" title="Modelo rechazado por Calidad">
                                                            <img src="{{ asset('images/Rechazado.png') }}" alt="Rechazado"
                                                                style="width: 38px; height: 38px;">
                                                        </span>
                                                    @elseif ($libStatus === 'pendiente')
                                                        <span class="badge-modelo-guardado" title="Datos capturados por Calidad (borrador)">
                                                            <img src="{{ asset('images/Guardado.png') }}" alt="Guardado"
                                                                style="width: 38px; height: 38px;">
                                                        </span>
                                                    @elseif ($reg->tiene_modelo || $reg->pre_orden_sent)
                                                        <span class="badge-modelo-recibido"
                                                            title="Almacén ha procesado el modelo, pendiente de revisión por Calidad">
                                                            <img src="{{ asset('images/Recibido.png') }}" alt="Recibido"
                                                                style="width: 38px; height: 38px;">
                                                        </span>
                                                    @else
                                                        <span class="badge-modelo-missing" title="En espera de que Almacén procese el modelo">
                                                            <img src="{{ asset('images/Espera.png') }}" alt="En Espera"
                                                                style="width: 38px; height: 38px;">
                                                        </span>
                                                    @endif
                                                @else
                                                    {{-- VISTA ALMACÉN --}}
                                                    @if ($libStatus === 'aprobado')
                                                        <span class="badge-modelo-ok" title="Modelo liberado y aprobado por Calidad">
                                                            <img src="{{ asset('images/Aprobado.png') }}" alt="Aprobado"
                                                                style="width: 38px; height: 38px;">
                                                        </span>
                                                    @elseif ($libStatus === 'rechazado')
                                                        <span class="badge-modelo-rechazado" title="Modelo rechazado por Calidad">
                                                            <img src="{{ asset('images/Rechazado.png') }}" alt="Rechazado"
                                                                style="width: 38px; height: 38px;">
                                                        </span>

                                                    @elseif ($reg->tiene_modelo || $reg->pre_orden_sent)
                                                        <span class="badge-modelo-espera"
                                                            title="Procesado por Almacén, en espera de respuesta de Calidad">
                                                            <img src="{{ asset('images/Espera.png') }}" alt="En Espera"
                                                                style="width: 38px; height: 38px;">
                                                        </span>
                                                    @else
                                                        <span class="badge-modelo-recibido"
                                                            title="Alerta inicial recibida, pendiente de procesar modelo">
                                                            <img src="{{ asset('images/Recibido.png') }}" alt="Recibido"
                                                                style="width: 38px; height: 38px;">
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
                                                <button class="btn-toggle-files" data-target="files-{{ $estado }}-{{ $loop->index }}"
                                                    data-ot="{{ $reg->ot }}" id="toggle-btn-{{ $estado }}-{{ $loop->index }}"
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
                                                {{-- BLOQUE 1: Dibujos solo visibles si la alerta fue enviada desde manage_documentation.js
                                                --}}
                                                @if ($countDibujos > 0 && $reg->alert_sent_at)
                                                    <h3
                                                        style="margin-top: 15px; margin-bottom: 10px; color: #005194; border-bottom: 2px solid #005194; padding-bottom: 5px;">
                                                        Dibujos de Fundición</h3>
                                                    <div class="alm-pdf-grid">
                                                        @foreach ($archivos as $archivo)
                                                            <div class="dibujos-file-card" style="animation-delay: {{ $loop->index * 0.05 }}s;">
                                                                <div class="file-icon-wrapper"
                                                                    onclick="almacenVerPdf('{{ $reg->ot }}', '{{ $archivo }}', 'dibujo')"
                                                                    style="cursor: pointer;" title="Abrir PDF">
                                                                    <img src="{{ asset('images/pdf-view-shadow.png') }}"
                                                                        class="file-icon icon-default">
                                                                    <img src="{{ asset('images/pdf-view.png') }}" class="file-icon icon-hover">
                                                                </div>
                                                                <div class="file-name" style="cursor: pointer;" title="Abrir PDF"
                                                                    onclick="almacenVerPdf('{{ $reg->ot }}', '{{ $archivo }}', 'dibujo')">
                                                                    {{ basename($archivo) }}
                                                                </div>
                                                                <div class="file-actions">
                                                                    <button class="btn-dibujos btn-dibujos-sm btn-ver"
                                                                        onclick="almacenVerPdf('{{ $reg->ot }}', '{{ $archivo }}', 'dibujo')">Ver</button>
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
                                                                    onclick="almacenVerPdf('{{ $reg->ot }}', '{{ $ayudaArchivo['nombre'] }}', '{{ $ayudaArchivo['tipo'] }}')"
                                                                    style="cursor: pointer;" title="Abrir PDF">
                                                                    <img src="{{ asset('images/pdf-view-shadow.png') }}"
                                                                        class="file-icon icon-default">
                                                                    <img src="{{ asset('images/pdf-view.png') }}" class="file-icon icon-hover">
                                                                </div>
                                                                <div class="file-name" style="cursor: pointer;" title="Abrir PDF"
                                                                    onclick="almacenVerPdf('{{ $reg->ot }}', '{{ $ayudaArchivo['nombre'] }}', '{{ $ayudaArchivo['tipo'] }}')">
                                                                    {{ basename($ayudaArchivo['nombre']) }}
                                                                </div>
                                                                <div class="file-actions">
                                                                    <button class="btn-dibujos btn-dibujos-sm btn-ver btn-ayuda-color"
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
                                                @if ($countAprobados > 0)
                                                    <h3
                                                        style="margin-top: 25px; margin-bottom: 10px; color: #155724; border-bottom: 2px solid #155724; padding-bottom: 5px;">
                                                        Documentos Aprobados</h3>
                                                    <div class="alm-pdf-grid">
                                                        @foreach ($archivosAprobados as $otroArchivo)
                                                            @if ($otroArchivo['tipo'] === 'imagen')
                                                                {{-- Tarjeta para imágenes (fotos de evidencia) --}}
                                                                <div class="dibujos-file-card card-otro card-imagen"
                                                                    style="animation-delay: {{ $loop->index * 0.05 }}s; border-left-color: #0369a1;">
                                                                    <div class="file-icon-wrapper"
                                                                        onclick="almacenVerPdf('{{ $reg->ot }}', '{{ $otroArchivo['nombre'] }}', 'otro')"
                                                                        style="cursor: pointer;" title="Ver imagen">
                                                                        <img src="{{ $otroArchivo['url'] }}" class="file-icon-img-thumb"
                                                                            alt="{{ basename($otroArchivo['nombre']) }}"
                                                                            style="width:100%; height:80px; object-fit:cover; border-radius:6px; border:1px solid #bae6fd;">
                                                                    </div>
                                                                    <div class="file-name" style="cursor: pointer;" title="Ver imagen"
                                                                        onclick="almacenVerPdf('{{ $reg->ot }}', '{{ $otroArchivo['nombre'] }}', 'otro')">
                                                                        {{ basename($otroArchivo['nombre']) }}
                                                                    </div>
                                                                    <div class="file-actions" style="display: flex; gap: 5px;">
                                                                        <button class="btn-dibujos btn-dibujos-sm btn-ver"
                                                                            style="background-color: #0369a1; color: white;"
                                                                            onclick="almacenVerPdf('{{ $reg->ot }}', '{{ $otroArchivo['nombre'] }}', 'otro')">Ver</button>
                                                                        <button class="btn-dibujos btn-dibujos-sm btn-eliminar"
                                                                            style="background-color: #dc3545; color: white;"
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
                                                                        <img src="{{ asset('images/pdf-view.png') }}" class="file-icon icon-hover">
                                                                    </div>
                                                                    <div class="file-name" style="cursor: pointer;" title="Abrir PDF"
                                                                        onclick="almacenVerPdf('{{ $reg->ot }}', '{{ $otroArchivo['nombre'] }}', '{{ $otroArchivo['tipo'] }}')">
                                                                        {{ basename($otroArchivo['nombre']) }}
                                                                    </div>
                                                                    <div class="file-actions" style="display: flex; gap: 5px;">
                                                                        <button class="btn-dibujos btn-dibujos-sm btn-ver"
                                                                            style="background-color: #155724; color: white;"
                                                                            onclick="almacenVerPdf('{{ $reg->ot }}', '{{ $otroArchivo['nombre'] }}', '{{ $otroArchivo['tipo'] }}')">Ver</button>
                                                                        <button class="btn-dibujos btn-dibujos-sm btn-eliminar"
                                                                            style="background-color: #dc3545; color: white;"
                                                                            onclick="almacenEliminarOtroArchivo('{{ $reg->ot }}', '{{ $otroArchivo['nombre'] }}', '{{ $otroArchivo['tipo'] }}', this)">Eliminar</button>
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
                                                            @if ($otroArchivo['tipo'] === 'imagen')
                                                                {{-- Tarjeta para imágenes (fotos de evidencia) --}}
                                                                <div class="dibujos-file-card card-otro card-imagen"
                                                                    style="animation-delay: {{ $loop->index * 0.05 }}s; border-left-color: #0369a1;">
                                                                    <div class="file-icon-wrapper"
                                                                        onclick="almacenVerPdf('{{ $reg->ot }}', '{{ $otroArchivo['nombre'] }}', 'otro')"
                                                                        style="cursor: pointer;" title="Ver imagen">
                                                                        <img src="{{ $otroArchivo['url'] }}" class="file-icon-img-thumb"
                                                                            alt="{{ basename($otroArchivo['nombre']) }}"
                                                                            style="width:100%; height:80px; object-fit:cover; border-radius:6px; border:1px solid #bae6fd;">
                                                                    </div>
                                                                    <div class="file-name" style="cursor: pointer;" title="Ver imagen"
                                                                        onclick="almacenVerPdf('{{ $reg->ot }}', '{{ $otroArchivo['nombre'] }}', 'otro')">
                                                                        {{ basename($otroArchivo['nombre']) }}
                                                                    </div>
                                                                    <div class="file-actions" style="display: flex; gap: 5px;">
                                                                        <button class="btn-dibujos btn-dibujos-sm btn-ver"
                                                                            style="background-color: #0369a1; color: white;"
                                                                            onclick="almacenVerPdf('{{ $reg->ot }}', '{{ $otroArchivo['nombre'] }}', 'otro')">Ver</button>
                                                                        <button class="btn-dibujos btn-dibujos-sm btn-eliminar"
                                                                            style="background-color: #dc3545; color: white;"
                                                                            onclick="almacenEliminarOtroArchivo('{{ $reg->ot }}', '{{ $otroArchivo['nombre'] }}', 'otro', this)">Eliminar</button>
                                                                    </div>
                                                                </div>
                                                            @else
                                                                {{-- Tarjeta para PDFs y otros documentos --}}
                                                                <div class="dibujos-file-card card-otro"
                                                                    style="animation-delay: {{ $loop->index * 0.05 }}s; border-left-color: #9c0300;">
                                                                    <div class="file-icon-wrapper"
                                                                        onclick="almacenVerPdf('{{ $reg->ot }}', '{{ $otroArchivo['nombre'] }}', '{{ $otroArchivo['tipo'] }}')"
                                                                        style="cursor: pointer;" title="Abrir PDF">
                                                                        <img src="{{ asset('images/pdf-view-shadow.png') }}"
                                                                            class="file-icon icon-default">
                                                                        <img src="{{ asset('images/pdf-view.png') }}" class="file-icon icon-hover">
                                                                    </div>
                                                                    <div class="file-name" style="cursor: pointer;" title="Abrir PDF"
                                                                        onclick="almacenVerPdf('{{ $reg->ot }}', '{{ $otroArchivo['nombre'] }}', '{{ $otroArchivo['tipo'] }}')">
                                                                        {{ basename($otroArchivo['nombre']) }}
                                                                    </div>
                                                                    <div class="file-actions" style="display: flex; gap: 5px;">
                                                                        <button class="btn-dibujos btn-dibujos-sm btn-ver"
                                                                            style="background-color: #9c0300; color: white;"
                                                                            onclick="almacenVerPdf('{{ $reg->ot }}', '{{ $otroArchivo['nombre'] }}', '{{ $otroArchivo['tipo'] }}')">Ver</button>
                                                                        <button class="btn-dibujos btn-dibujos-sm btn-eliminar"
                                                                            style="background-color: #dc3545; color: white;"
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
                                                    <div class="lib-calidad-card" id="control-modelo-{{ md5($reg->ot) }}"
                                                        style="{{ $controlDisabled }}">
                                                        <div class="lib-calidad-card-header">
                                                            <img src="{{ asset('images/almacen.png') }}" alt="Almacén"
                                                                style="width:38px;height:38px;object-fit:contain;flex-shrink:0;">
                                                            <div style="overflow:hidden;">
                                                                <span class="lib-calidad-card-title">Control de Modelos &mdash; Almacén</span>
                                                                <span
                                                                    class="lib-calidad-card-ot">{{ preg_replace('/_\d{8}_\d{6}_.*/', '', $reg->ot) }}</span>
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
                                                                    <button class="btn-modelo btn-modelo-si"
                                                                        onclick="abrirModalConfirmarModelo('{{ $reg->ot }}', '{{ md5($reg->ot) }}')"
                                                                        title="Sí, cuento con el modelo de esta OT" style="{{ $hideSiNo }}">
                                                                        <img src="{{ asset('images/Aprobado.png') }}" alt="Si">
                                                                        <span>Tengo el Modelo</span>
                                                                    </button>
                                                                    <button class="btn-modelo btn-modelo-no"
                                                                        onclick="abrirModalPreOrden('{{ $reg->ot }}')"
                                                                        title="No cuento con él, generar formato PDF" style="{{ $hideSiNo }}">
                                                                        <img src="{{ asset('images/pdf.png') }}" alt="PDF">
                                                                        <span>No, generar formato</span>
                                                                    </button>
                                                                    <button class="btn-modelo btn-modelo-edit"
                                                                        onclick="abrirModalPreOrden('{{ $reg->ot }}')"
                                                                        title="Editar información de la preorden existente"
                                                                        style="{{ $hideEditMail }}">
                                                                        <img src="{{ asset('images/editar-informacion.png') }}" alt="Editar">
                                                                        <span>Editar Datos</span>
                                                                    </button>
                                                                    <button class="btn-modelo btn-modelo-email"
                                                                        onclick="abrirModalEnviarPreOrden('{{ $reg->ot }}', '{{ md5($reg->ot) }}')"
                                                                        title="Enviar pre-orden por correo electrónico"
                                                                        style="{{ $hideEditMail }}">
                                                                        <img src="{{ asset('images/enviando.png') }}" alt="Enviar">
                                                                        <span>Enviar Correo</span>
                                                                    </button>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endif

                                                {{-- ── ACCIONES DE CALIDAD / ESTADOS DE LIBERACION ── --}}
                                                @if (Auth::user()->perfil == 4 && $estado === 'activa' && (in_array($reg->calidad_revision_status, [null, 'pendiente']) || ($reg->calidad_revision_status === 'rechazado' && ($reg->tiene_modelo || $reg->pre_orden_sent || $reg->pre_orden_email_sent))))
                                                    <div class="lib-calidad-card">
                                                        <div class="lib-calidad-card-header">
                                                            <img src="{{ asset('images/Quality.png') }}" alt="Calidad"
                                                                style="width:38px;height:38px;object-fit:contain;flex-shrink:0;">
                                                            <div style="overflow:hidden;">
                                                                <span class="lib-calidad-card-title">Acciones de Liberacion &mdash;
                                                                    Calidad</span>
                                                                <span
                                                                    class="lib-calidad-card-ot">{{ preg_replace('/_\d{8}_\d{6}_.*/', '', $reg->ot) }}</span>
                                                            </div>
                                                        </div>
                                                        <div class="lib-calidad-card-body">
                                                            @if ($reg->calidad_revision_status === 'rechazado')
                                                                <div class="lib-estado-badge lib-estado-rechazado" style="padding: 12px 16px; width: 100%; box-sizing: border-box; display: flex; align-items: center; gap: 8px;">
                                                                    <img src="{{ asset('images/Rechazado.png') }}" alt=""
                                                                        style="width:18px;height:18px;object-fit:contain;flex-shrink:0;">
                                                                    <span>Liberacion rechazada anteriormente. Puedes revisar y volver a emitir
                                                                        un veredicto.</span>
                                                                </div>
                                                            @elseif (is_null($reg->calidad_revision_status) && !$reg->pre_orden_sent && !$reg->tiene_modelo)
                                                                <div class="lib-estado-badge lib-estado-info" style="width: 100%; box-sizing: border-box; display: flex; align-items: center; gap: 8px;">
                                                                    En espera de que Almacén envíe la información necesaria para realizar la
                                                                    liberación.
                                                                </div>
                                                            @elseif ($reg->calidad_revision_status === 'pendiente')
                                                                <div class="lib-estado-badge lib-estado-guardado" style="width: 100%; box-sizing: border-box; display: flex; align-items: center; gap: 8px;">
                                                                    <img src="{{ asset('images/Guardado.png') }}" alt=""
                                                                        style="width:18px;height:18px;object-fit:contain;flex-shrink:0;">
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
                                                                    if (strpos($clLow, 'fondo') !== false)
                                                                        $tipo = 'Fondo';
                                                                    elseif (strpos($clLow, 'obturador') !== false)
                                                                        $tipo = 'Obturador';
                                                                    elseif (strpos($clLow, 'molde') !== false)
                                                                        $tipo = 'Molde';
                                                                    elseif (strpos($clLow, 'bombillo') !== false)
                                                                        $tipo = 'Bombillo';

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

                                                                $hasAprobadoBorrador = \App\Models\LiberacionModeloFundicion::where('ot', '=', $reg->ot, 'and')
                                                                    ->where('estado', '=', 'pendiente', 'and')
                                                                    ->where('decision', '=', 'aprobar', 'and')
                                                                    ->exists();
                                                                    
                                                                $decisionGlobal = 'aprobar';
                                                                if ($hasRechazoBorrador && $hasAprobadoBorrador) {
                                                                    $decisionGlobal = 'mixto';
                                                                } elseif ($hasRechazoBorrador) {
                                                                    $decisionGlobal = 'rechazar';
                                                                }

                                                                $borradorRechazado = \App\Models\LiberacionModeloFundicion::where('ot', '=', $reg->ot, 'and')
                                                                    ->where('estado', '=', 'pendiente', 'and')
                                                                    ->where('decision', '=', 'rechazar', 'and')
                                                                    ->first();

                                                                $tiposGuardados = \App\Models\LiberacionModeloFundicion::where('ot', '=', $reg->ot, 'and')
                                                                    ->where('estado', '=', 'pendiente', 'and')
                                                                    ->get(['tipo_modelo','decision']);
                                                                $tiposLabel = implode(', ', $tiposGuardados->pluck('tipo_modelo')->toArray());
                                                                $tiposAprobadosArr  = $tiposGuardados->where('decision','aprobar')->pluck('tipo_modelo')->values()->toArray();
                                                                $tiposRechazadosArr = $tiposGuardados->where('decision','rechazar')->pluck('tipo_modelo')->values()->toArray();
                                                                $tiposAprobadosJson  = json_encode($tiposAprobadosArr);
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
                                                                        Proceso de liberación en curso (capturados: {{ $contClasesConDatos }} de
                                                                        {{ count($clasesActivas) }}).
                                                                    @elseif ($reg->calidad_revision_status === 'rechazado')
                                                                        El modelo fue rechazado antes. ¿Quieres revisarlo de nuevo?
                                                                    @else
                                                                        ¿Qué deseas hacer con este modelo? ¿Lo apruebas o lo rechazas?
                                                                    @endif
                                                                </h4>
                                                                <div class="lib-calidad-card-btns">
                                                                    @if ($todosGuardados)
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
                                                                                <button class="btn-calidad-action btn-calidad-email"
                                                                                    onclick="abrirModalEnviarAlertaLiberacion('{{ $reg->ot }}', '{{ $decisionGlobal }}', {{ $tiposAprobadosJson }}, {{ $tiposRechazadosJson }})"
                                                                                    title="Subir documentos firmados y enviar alerta de rechazo">
                                                                                    <img src="{{ asset('images/enviando.png') }}" alt="">
                                                                                    <span>Enviar Alerta</span>
                                                                                </button>
                                                                            @endif
                                                                        @else
                                                                            <button class="btn-calidad-action btn-calidad-email"
                                                                                onclick="abrirModalEnviarAlertaLiberacion('{{ $reg->ot }}', '{{ $decisionGlobal }}', {{ $tiposAprobadosJson }}, {{ $tiposRechazadosJson }})"
                                                                                title="Subir formato escaneado y enviar alerta de aprobación">
                                                                                <img src="{{ asset('images/enviando.png') }}" alt="">
                                                                                <span>Enviar Alerta</span>
                                                                            </button>
                                                                        @endif
                                                                    @else
                                                                        <button class="btn-calidad-action btn-calidad-iniciar" @if (!$reg->pre_orden_email_sent && !$reg->tiene_modelo) disabled
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
                                                        </div>
                                                    </div>
                                                @else
                                                    @if ($reg->calidad_revision_status === 'aprobado')
                                                        <div class="lib-estado-badge lib-estado-aprobado" style="margin-top: 20px; display: flex; width: 100%; justify-content: center; box-sizing: border-box; padding: 12px 16px; font-size: 1.1em;">
                                                            <img src="{{ asset('images/Aprobado.png') }}" alt="Aprobado" style="width:22px; height:22px; object-fit:contain;">
                                                            Modelo liberado y aprobado por Calidad.
                                                        </div>
                                                    @elseif ($reg->calidad_revision_status === 'rechazado')
                                                        <div class="lib-estado-badge lib-estado-rechazado" style="margin-top: 20px; display: flex; width: 100%; justify-content: center; box-sizing: border-box; padding: 12px 16px; font-size: 1.1em;">
                                                            <img src="{{ asset('images/Rechazado.png') }}" alt="Rechazado" style="width:22px; height:22px; object-fit:contain;">
                                                            Modelo rechazado por Calidad.
                                                        </div>
                                                    @elseif ($reg->calidad_revision_status === 'mixto')
                                                        <div class="lib-estado-badge lib-estado-info" style="margin-top: 20px; display: flex; width: 100%; justify-content: center; box-sizing: border-box; padding: 12px 16px; font-size: 1.1em;">
                                                            <img src="{{ asset('images/Quality.png') }}" alt="Mixto" style="width:22px; height:22px; object-fit:contain;">
                                                            Modelo con liberación mixta (algunos tipos aprobados y otros rechazados) por Calidad.
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
        <div class="alm-modal-content" style="max-width: 1100px; border-radius: 20px; border: 2.5px solid #0a8504; overflow: hidden;">
            <div class="alm-modal-header"
                style="background: linear-gradient(135deg, #0a8504, #064e03); border-bottom: 2px solid #064e03; padding: 2.2em 2.5em 2em; position: relative;">
                <div class="div-cerrar">
                    <button type="button" class="btn-cerrar" onclick="cerrarModalConfirmarModelo()"
                        style="position: absolute; top: 25px; right: 25px; background: rgba(255, 255, 255, 0.18); border: 1.5px solid rgba(255, 255, 255, 0.45); border-radius: 50%; width: 42px; height: 42px; display: inline-flex; align-items: center; justify-content: center; cursor: pointer; transition: all 0.2s ease;">
                        <img class="img-cerrar" src="{{ asset('images/cerrar.png') }}" alt="Cerrar" style="width: 14px; height: 14px; filter: brightness(0) invert(1);">
                    </button>
                </div>
                <div style="display: flex; align-items: center; gap: 18px;">
                    <img src="{{ asset('images/Aprobado.png') }}" style="width: 46px; height: 46px; object-fit: contain; filter: drop-shadow(0 4px 8px rgba(0,0,0,0.15));" alt="">
                    <div>
                        <h3 style="color:#fff; margin:0; font-size:1.45em; font-weight: 800; font-family: 'Poppins', sans-serif;">Confirmar Disponibilidad del Modelo</h3>
                        <div id="confirmar-modelo-subtitle" style="color: rgba(255,255,255,0.9); font-size: 0.95em; margin-top: 2px; font-weight: 500; font-family: 'Poppins', sans-serif;">OT: -</div>
                    </div>
                </div>
            </div>
            <div class="alm-modal-body" style="padding: 2.2em 2.5em; background: #fafafa; font-family: 'Poppins', sans-serif;">
                <form id="formConfirmarModelo" enctype="multipart/form-data">
                    <input type="hidden" id="cm-ot" name="ot">
                    <input type="hidden" id="cm-id-hash" name="id_hash">

                    <div style="padding: 0 0 14px; color: #334155; font-size:0.97em;">
                        <p style="margin-bottom:12px; font-weight: 500;">¿Confirmas que cuentas físicamente con el modelo para esta OT?</p>
                        <p style="background:#fef9c3; border:1px solid #fde047; border-radius:12px; padding:12px 18px; color:#713f12; font-size:0.9em; line-height: 1.5; margin-bottom: 20px;">
                            <strong>⚠ Documentos requeridos:</strong> Debes adjuntar los documentos que acrediten la
                            recepción del modelo (ej. remisión, hoja de entrega, fotos).
                        </p>
                    </div>

                    {{-- FECHA DE CONFIRMACIÓN / ENVÍO --}}
                    <div class="form-group" style="margin-bottom: 20px;">
                        <label for="cm-fecha" style="font-weight:700; color:#334155; display:block; margin-bottom:10px; font-family:'Poppins', sans-serif; font-size:1.15em;">
                            Fecha de Confirmación / Envío <span style="color:#9c0300;">*</span>
                        </label>
                        <input type="date" id="cm-fecha" name="fecha" class="form-control" required style="font-family:'Poppins', sans-serif; font-size: 1.1em; padding: 12px 18px; height: auto; border-radius: 10px;">
                    </div>

                    <div class="form-group" style="margin-bottom: 22px;">
                        <label class="custom-file-upload-label" style="font-weight:700; color:#334155; display:block; margin-bottom:10px; font-family:'Poppins', sans-serif; font-size:1.15em;">
                            Adjuntar documentos de recepción <span style="color:#9c0300;">*</span>
                        </label>
                        <div class="custom-file-dropzone" style="border: 2px dashed #0a8504; background: #f0fdf4; min-height: 80px; position: relative; border-radius: 12px; display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 12px; cursor: pointer;">
                            <input type="file" id="cm-archivos" name="archivos[]" class="custom-file-input" multiple accept=".pdf,image/*" style="position: absolute; width:100%; height:100%; opacity:0; cursor:pointer;">
                            <div class="dropzone-content">
                                <img src="{{ asset('images/anadir.png') }}" class="dropzone-icon" style="width: 40px; height: 40px; margin-bottom: 8px; object-fit: contain;">
                                <span class="dropzone-text" style="font-weight: 700; color: #0a8504; font-size: 0.85em; text-align: center; font-family:'Poppins', sans-serif;">Arrastra archivos aquí o haz clic para buscar</span>
                                <span class="dropzone-subtext" style="font-size: 0.7em; color: #64748b; margin-top: 2px; font-family:'Poppins', sans-serif;">Soporta múltiples archivos PDF o imágenes</span>
                            </div>
                        </div>
                        <div id="cm-archivos-list" style="margin-top: 15px; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; padding: 15px; max-height: 420px; overflow-y: auto; display: none; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 12px; justify-items: center;"></div>
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

    {{-- ── MODAL: ENVIAR ALERTA DE LIBERACION (APROBADO/RECHAZADO) ── --}}
    <div id="modalEnviarAlertaLiberacion" class="alm-modal" role="dialog" aria-modal="true">
        <div class="alm-modal-content" id="alerta-lib-modal-content" style="max-width: 1500px; width: 96vw; border-radius: 20px;">
            <div class="alm-modal-header" id="alerta-lib-header" style="padding: 2.5em 3em 2.2em; border-top-left-radius: 18px; border-top-right-radius: 18px;">
                <div class="div-cerrar">
                    @include('almacen.partials._btn_cerrar', ['onclick' => 'cerrarModalEnviarAlertaLiberacion()'])
                </div>
                <h3 id="alerta-lib-title" style="font-size: 2.2em; margin: 0; font-family:'Poppins', sans-serif; font-weight: 700; color: #fff;">Enviar Alerta de Liberación</h3>
                <p id="alerta-lib-subtitle" class="lib-modal-subtitle" style="color: #bae6fd; font-size: 1.15em; margin-top: 8px; margin-bottom: 0; font-family:'Poppins', sans-serif; font-weight: 500;"></p>
            </div>
            <div class="alm-modal-body" style="padding: 3em 3.5em;">
                <form id="formEnviarAlertaLiberacion" enctype="multipart/form-data" novalidate>
                    @csrf
                    <input type="hidden" id="al-ot" name="ot">
                    <input type="hidden" id="al-decision" name="decision">
                    <input type="hidden" id="al-tipo-modelo" name="tipo_modelo">

                    <p style="margin-bottom:28px; font-family:'Poppins', sans-serif; font-weight:500; line-height:1.6; color:#334155; font-size: 1.3em;" id="al-prompt-text"></p>

                    {{-- Destinatario(s) --}}
                    <div class="form-group" style="margin-bottom: 24px;">
                        <label for="al-destinatario" style="font-size: 1.2em; font-weight: 700; color: #334155; display: block; margin-bottom: 10px; font-family:'Poppins', sans-serif;">Destinatario(s):</label>
                        <input type="text" id="al-destinatario" name="destinatario" class="form-control" required value="jaxer020406@gmail.com" style="font-size: 1.15em; padding: 14px 20px; height: auto; border-radius: 10px; font-family:'Poppins', sans-serif;">
                        <span style="font-size: 0.9em; color: #64748b; margin-top: 8px; display: block;">Separa múltiples correos usando comas (,).</span>
                    </div>

                    {{-- FECHA --}}
                    <div class="form-group" style="margin-bottom: 28px;">
                        <label id="al-fecha-label" for="al-fecha" style="font-weight:700; color:#334155; display:block; margin-bottom:10px; font-family:'Poppins', sans-serif; font-size:1.2em;">
                            Fecha de Emisión / Entrega <span style="color:#9c0300;">*</span>
                        </label>
                        <input type="date" id="al-fecha" name="fecha" class="form-control" required style="font-family:'Poppins', sans-serif; font-size: 1.15em; padding: 14px 20px; height: auto; border-radius: 10px;">
                    </div>

                    {{-- ═══ LAYOUT DUAL: Aprobados (izq) + Rechazados (der) si hay ambos, o uno solo al 100% ═══ --}}
                    <div id="al-dual-layout" style="display: flex; gap: 32px; align-items: stretch; margin-top: 32px;">

                        {{-- ── COLUMNA APROBADOS ── --}}
                        <div id="al-col-aprobados" style="flex: 1; width: 100%; display: none;">
                            <div style="border: 2.5px solid #059669; border-radius: 18px; overflow: hidden; box-shadow: 0 8px 25px rgba(5,150,105,0.12);">
                                {{-- Header Aprobados --}}
                                <div style="background: linear-gradient(135deg, #059669, #047857); padding: 20px 24px; display: flex; align-items: center; gap: 14px;">
                                    <img src="{{ asset('images/Aprobado.png') }}" style="width:36px;height:36px;object-fit:contain;" alt="">
                                    <div>
                                        <div style="font-weight:800; font-size:1.35em; color:#fff; font-family:'Poppins',sans-serif;">Documentos Aprobados</div>
                                        <div id="al-aprobados-tipos-label" style="font-size:0.95em; color:#a7f3d0; font-family:'Poppins',sans-serif;">—</div>
                                    </div>
                                </div>
                                <div style="padding: 24px;">
                                    {{-- Archivos del servidor — Aprobados --}}
                                    <label style="font-weight:700; color:#059669; font-size:1.15em; margin-bottom:12px; display:block; font-family:'Poppins',sans-serif;">Archivos en servidor (selecciona los que deseas adjuntar):</label>
                                    <div id="al-server-files-aprobados" style="background:#f0fdf4; border:1.8px solid #bbf7d0; border-radius:14px; padding:20px; max-height:280px; overflow-y:auto; display:grid; grid-template-columns:repeat(auto-fill,minmax(180px,1fr)); gap:12px; justify-items:center;">
                                        <div style="text-align:center; color:#64748b; grid-column:1/-1; padding:12px; font-style:italic; font-size:0.95em; font-family:'Poppins',sans-serif;">Cargando archivos...</div>
                                    </div>

                                    {{-- Upload Firmados — por Modelo (Aprobados) --}}
                                    <div style="margin-top:24px;">
                                        <label style="font-weight:700; color:#059669; font-size:1.15em; display:block; margin-bottom:10px; font-family:'Poppins',sans-serif;">
                                            Subir Formato F-CCL-LDM Firmado (por modelo):
                                        </label>
                                        <p style="font-size:0.9em; color:#64748b; margin-bottom:14px; font-family:'Poppins',sans-serif; line-height: 1.5;">Selecciona el tipo de modelo y luego sube el formato de liberación <strong>aprobado y firmado</strong> correspondiente.</p>
                                        <div id="al-upload-aprobados-rows" style="display:flex; flex-direction:column; gap:14px;"></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- ── COLUMNA RECHAZADOS ── --}}
                        <div id="al-col-rechazados" style="flex: 1; width: 100%; display: none;">
                            <div style="border: 2.5px solid #dc2626; border-radius: 18px; overflow: hidden; box-shadow: 0 8px 25px rgba(220,38,38,0.12);">
                                {{-- Header Rechazados --}}
                                <div style="background: linear-gradient(135deg, #dc2626, #b91c1c); padding: 20px 24px; display: flex; align-items: center; gap: 14px;">
                                    <img src="{{ asset('images/Rechazado.png') }}" style="width:36px;height:36px;object-fit:contain;" alt="">
                                    <div>
                                        <div style="font-weight:800; font-size:1.35em; color:#fff; font-family:'Poppins',sans-serif;">Documentos Rechazados</div>
                                        <div id="al-rechazados-tipos-label" style="font-size:0.95em; color:#fecaca; font-family:'Poppins',sans-serif;">—</div>
                                    </div>
                                </div>
                                <div style="padding: 24px;">
                                    {{-- Archivos del servidor — Rechazados --}}
                                    <label style="font-weight:700; color:#dc2626; font-size:1.15em; margin-bottom:12px; display:block; font-family:'Poppins',sans-serif;">Archivos en servidor (selecciona los que deseas adjuntar):</label>
                                    <div id="al-server-files-rechazados" style="background:#fef2f2; border:1.8px solid #fecaca; border-radius:14px; padding:20px; max-height:280px; overflow-y:auto; display:grid; grid-template-columns:repeat(auto-fill,minmax(180px,1fr)); gap:12px; justify-items:center;">
                                        <div style="text-align:center; color:#64748b; grid-column:1/-1; padding:12px; font-style:italic; font-size:0.95em; font-family:'Poppins',sans-serif;">Cargando archivos...</div>
                                    </div>

                                    {{-- Upload Firmados — por Modelo (Rechazados) --}}
                                    <div style="margin-top:24px;">
                                        <label style="font-weight:700; color:#dc2626; font-size:1.15em; display:block; margin-bottom:10px; font-family:'Poppins',sans-serif;">
                                            Subir Formato F-CCL-LDM de Rechazo + SCAR Firmado (por modelo):
                                        </label>
                                        <p style="font-size:0.9em; color:#64748b; margin-bottom:14px; font-family:'Poppins',sans-serif; line-height: 1.5;">Selecciona el tipo de modelo y luego sube el <strong>formato de liberación rechazado</strong> y el <strong>SCAR firmado</strong> correspondiente.</p>
                                        <div id="al-upload-rechazados-rows" style="display:flex; flex-direction:column; gap:14px;"></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>{{-- fin dual-layout --}}

                    <div class="form-actions" style="text-align: center; margin-top: 40px; margin-bottom: 12px;">
                        <button type="submit" class="btn-save-preorden" id="btn-submit-alerta-liberacion" style="font-size:1.2em; padding:15px 32px; border-radius:10px; font-family:'Poppins',sans-serif; font-weight: 700; height: auto;">
                            Enviar Alerta de Liberación
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
                                <button type="button" id="btn-add-clase-po" class="btn-img-action"
                                    onclick="agregarFilaPreOrden()" title="Añadir una nueva clase a la pre-orden"
                                    style="display: inline-block;">
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
                <p id="env-po-modal-subtitle" class="lib-modal-subtitle"
                    style="color: #bae6fd; font-size: 0.9em; margin-top: 4px; margin-bottom: 0;"></p>
            </div>
            <div class="alm-modal-body">
                <form id="formEnviarPreOrden" enctype="multipart/form-data">
                    <input type="hidden" id="env-ot" name="ot">

                    <div class="form-group" style="margin-bottom: 15px;">
                        <label for="env-destinatario">Destinatario(s):</label>
                        <input type="text" id="env-destinatario" name="destinatario" class="form-control" required
                            value="jaxer020406@gmail.com">
                        <span style="font-size: 0.8em; color: #64748b; margin-top: 4px;">Separa múltiples correos usando
                            comas (,).</span>
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
                            style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; padding: 15px; max-height: 420px; overflow-y: auto; display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 8px; justify-items: center;">
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
                <p id="env-scar-modal-subtitle" class="lib-modal-subtitle" style="color: #ffd1d1; font-size: 0.9em; margin-top: 4px; margin-bottom: 0;"></p>
            </div>
            <div class="alm-modal-body lib-modal-body">
                <form id="formEnviarScar" enctype="multipart/form-data" autocomplete="off">
                    <input type="hidden" id="env-scar-ot" name="ot">

                    {{-- Destinatario --}}
                    <div class="form-group" style="margin-bottom: 16px;">
                        <label for="env-scar-destinatario" style="font-weight: 700; color: #334155; display: block; margin-bottom: 4px;">
                            Destinatario(s) (separados por coma):
                        </label>
                        <input type="text" id="env-scar-destinatario" name="destinatario" class="form-control" required
                            value="jaxer020406@gmail.com">
                    </div>

                    {{-- Fecha Compromiso --}}
                    <div class="form-group" style="margin-bottom: 16px;">
                        <label for="env-scar-fecha-compromiso" style="font-weight: 700; color: #334155; display: block; margin-bottom: 4px;">
                            Fecha Compromiso de Devolución (Obligatoria):
                        </label>
                        <input type="date" id="env-scar-fecha-compromiso" name="fecha_compromiso" class="form-control" required>
                    </div>

                    {{-- SCAR Firmado --}}
                    <div class="form-group" style="margin-bottom: 20px;">
                        <label for="env-scar-pdf-firmado" style="font-weight: 700; color: #9c0300; display: block; margin-bottom: 8px;">Subir SCAR Firmado Físicamente (PDF Obligatorio): <span style="color:#9c0300;">*</span></label>
                        <div class="custom-file-dropzone" style="border: 2px dashed #dc2626; background: #fef2f2; min-height: 80px; position: relative; border-radius: 12px; display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 12px; cursor: pointer;">
                            <input type="file" id="env-scar-pdf-firmado" name="pdf_firmado" class="custom-file-input" accept=".pdf" required style="position: absolute; width:100%; height:100%; opacity:0; cursor:pointer;" onchange="handleAlertaFileChange(this, 'env-scar-pdf-text', 'pdf')">
                            <div class="dropzone-content" style="display: flex; flex-direction: column; align-items: center; pointer-events: none;">
                                <img src="{{ asset('images/pdf.png') }}" style="width: 24px; height: 24px; margin-bottom: 4px;" alt="PDF">
                                <span id="env-scar-pdf-text" style="font-weight: 700; color: #dc2626; font-size: 0.85em; text-align: center; font-family:'Poppins', sans-serif;">Seleccionar o arrastrar PDF *</span>
                                <span style="font-size: 0.7em; color: #64748b; margin-top: 2px; font-family:'Poppins', sans-serif;">Solo archivos PDF</span>
                            </div>
                        </div>
                    </div>

                    {{-- Archivos de la OT disponibles --}}
                    <div class="form-group" style="margin-bottom: 20px;">
                        <label>Archivos de la OT disponibles para adjuntar:</label>
                        <div id="env-scar-server-files-container"
                            style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; padding: 15px; max-height: 420px; overflow-y: auto; display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 8px; justify-items: center;">
                            <div class="alm-spinner"
                                style="border-top-color: #9c0300; display: block; margin: 10px auto; grid-column: 1 / -1;">
                            </div>
                            <span style="text-align: center; color: #64748b; grid-column: 1 / -1;">Cargando archivos de la OT...</span>
                        </div>
                    </div>

                    {{-- Evidencia adicional --}}
                    <div class="form-group" style="margin-bottom: 30px;">
                        <label class="custom-file-upload-label" style="font-weight: 700; color: #9c0300; display: block; margin-bottom: 8px;">Subir Evidencia Adicional al Envío (Imágenes o PDFs adicionales):</label>
                        <div class="custom-file-dropzone" style="border: 2px dashed #9c0300; background: #fff8f8; min-height: 80px; position: relative; border-radius: 12px; display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 12px; cursor: pointer;">
                            <input type="file" id="env-scar-archivos-adicionales" name="archivos_adicionales[]" class="custom-file-input" multiple style="position: absolute; width:100%; height:100%; opacity:0; cursor:pointer;">
                            <div class="dropzone-content">
                                <img src="{{ asset('images/anadir.png') }}" class="dropzone-icon" style="width: 24px; height: 24px; margin-bottom: 4px; object-fit: contain;">
                                <span class="dropzone-text" style="font-weight: 700; color: #9c0300; font-size: 0.85em; text-align: center; font-family:'Poppins', sans-serif;">Arrastra archivos aquí o haz clic para buscar</span>
                                <span class="dropzone-subtext" style="font-size: 0.7em; color: #64748b; margin-top: 2px; font-family:'Poppins', sans-serif;">Imágenes, PDF, ZIP</span>
                            </div>
                        </div>
                        <div id="env-scar-archivos-adicionales-list" style="margin-top: 10px; display: flex; flex-wrap: wrap; gap: 8px;"></div>
                    </div>

                    {{-- Boton de Envio --}}
                    <div class="form-actions" style="text-align: center; margin-top: 20px;">
                        <button type="submit" class="btn-lib-send" style="background: linear-gradient(135deg, #9c0300, #7a0200); box-shadow: 0 4px 15px rgba(156, 3, 0, 0.3);">
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
