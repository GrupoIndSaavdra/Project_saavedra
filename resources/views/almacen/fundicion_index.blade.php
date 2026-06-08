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
                                        $latestReproceso = null;
                                        if ($reg->rechazos_procesados) {
                                            $latestReproceso = \App\Models\FundicionHistory::where('ot', 'LIKE', $reg->ot . '_R%')
                                                ->orderBy('id', 'desc')
                                                ->first();
                                        }
                                        $targetReg = ($reg->rechazos_procesados && $latestReproceso) ? $latestReproceso : $reg;

                                        $archivos = is_array($reg->almacen_archivos) ? $reg->almacen_archivos : [];
                                        $countDibujos = count($archivos);

                                        // ── RESOLVER TODOS LOS REGISTROS RELACIONADOS ──
                                        $allOtNames = \App\Models\FundicionHistory::where('ot', '=', $reg->ot)
                                            ->orWhere('ot', 'LIKE', $reg->ot . '_R%')
                                            ->pluck('ot')
                                            ->toArray();

                                        $ayudasArchivos = [];
                                        $otrosArchivos = [];
                                        $baseNames = [];

                                        $liberacionesPath = storage_path('app/public/liberaciones_pdf');

                                        foreach ($allOtNames as $otName) {
                                            $otNameSanitized = trim(
                                                preg_replace('/[\/\\\\]/', '', preg_replace('/\.\.+/', '', $otName)),
                                            );
                                            
                                            // 1. Escanear ayudas visuales de Almacen
                                            $ayudasDir = 'DOCUMENTACION_GIS/ALMACEN_FUNDICION/' . $otNameSanitized . '/ayudas_visuales';
                                            if (\Illuminate\Support\Facades\Storage::disk('local')->exists($ayudasDir)) {
                                                $files = \Illuminate\Support\Facades\Storage::disk('local')->allFiles($ayudasDir);
                                                foreach ($files as $f) {
                                                    $ext = strtolower(pathinfo($f, PATHINFO_EXTENSION));
                                                    $isPdf = $ext === 'pdf';
                                                    $isImage = in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp']);

                                                    if (!$isPdf && !$isImage)
                                                        continue;

                                                    $fNorm = str_replace('\\', '/', $f);
                                                    $dirNorm = str_replace('\\', '/', $ayudasDir);
                                                    $relativePath = ltrim(str_replace($dirNorm, '', $fNorm), '/');
                                                    $base = basename($relativePath);

                                                    if (str_starts_with($relativePath, 'preordenes/')) {
                                                        if (!in_array($base, $baseNames)) {
                                                            $otrosArchivos[] = [
                                                                'nombre' => $relativePath,
                                                                'url' => route('almacen.fundicion.serve', ['ot' => $otName, 'archivo' => $relativePath, 'tipo' => 'otro']),
                                                                'tipo' => $isImage ? 'imagen' : 'otro',
                                                                'ot' => $otName,
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

                                                    if (!in_array($base, $baseNames)) {
                                                        $origin = 'otro';
                                                        $tipo = 'otro';
                                                        if (strpos($relativePath, 'documentos_aprobados') !== false) {
                                                            $origin = 'aprobado';
                                                            $relativePathWithPrefix = 'preordenes/documentos_aprobados/' . $relativePath;
                                                        } elseif (strpos($relativePath, 'documentos_rechazados') !== false) {
                                                            $origin = 'rechazado';
                                                            $relativePathWithPrefix = 'preordenes/documentos_rechazados/' . $relativePath;
                                                        } else {
                                                            $relativePathWithPrefix = 'preordenes/' . $relativePath;
                                                        }

                                                        $otrosArchivos[] = [
                                                            'nombre' => $relativePathWithPrefix,
                                                            'url' => route('almacen.fundicion.serve', ['ot' => $otName, 'archivo' => $relativePathWithPrefix, 'tipo' => 'otro', 'origin' => $origin]),
                                                            'tipo' => $isImage ? 'imagen' : 'otro',
                                                            'ot' => $otName,
                                                        ];
                                                        $baseNames[] = $base;
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
                                                    if (!in_array($base, $baseNames)) {
                                                        $otrosArchivos[] = [
                                                            'nombre' => $base,
                                                            'url' => route('almacen.fundicion.serve', ['ot' => $otName, 'archivo' => $base, 'tipo' => 'liberacion']),
                                                            'tipo' => 'liberacion',
                                                            'ot' => $otName,
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
                                                            'url' => route('almacen.fundicion.serve', ['ot' => $otName, 'archivo' => $base, 'tipo' => 'liberacion']),
                                                            'tipo' => 'liberacion',
                                                            'ot' => $otName,
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
                                                $isPreorden = (in_array($archivo['tipo'], ['otro', 'imagen']) || str_starts_with($archivo['nombre'], 'preordenes/'));

                                                if ($userPerfil == 4) { // Calidad
                                                    // Calidad solo ve preordenes si pre_orden_email_sent es true
                                                    if (!$isPreorden || $targetReg->pre_orden_email_sent) {
                                                        $filteredOtros[] = $archivo;
                                                    }
                                                } elseif ($userPerfil == 5) { // Almacén
                                                    // Almacén solo ve PDFs de Calidad si se envió la alerta (aprobado o scar alertado)
                                                    if ($isPreorden) {
                                                        $filteredOtros[] = $archivo;
                                                    } else {
                                                        $calidadAlertaEnviada = ($targetReg->calidad_revision_status === 'aprobado' ||
                                                            \App\Models\ScarModelo::where(function($q) use ($reg) {
                                                                $q->where('ot', '=', $reg->ot)
                                                                  ->orWhere('ot', 'LIKE', $reg->ot . '_R%');
                                                            })->where('estatus', '=', 'alertado')->exists());
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

                                        // ── CALCULAR APROBADOS Y RECHAZADOS DEL ÚLTIMO VEREDICTO DE CADA CLASE ──
                                        $liberacionesAll = \App\Models\LiberacionModeloFundicion::whereIn('ot', $allOtNames)->get();
                                        $latestLiberacionesByClass = [];
                                        foreach ($liberacionesAll as $lib) {
                                            $tipo = $lib->tipo_modelo;
                                            $libOt = $lib->ot;

                                            preg_match('/_R(\d+)$/', $libOt, $matches);
                                            $suffixNum = isset($matches[1]) ? (int)$matches[1] : 0;

                                            if (!isset($latestLiberacionesByClass[$tipo]) || $suffixNum > $latestLiberacionesByClass[$tipo]['suffix']) {
                                                $latestLiberacionesByClass[$tipo] = [
                                                    'lib' => $lib,
                                                    'suffix' => $suffixNum
                                                ];
                                            }
                                        }

                                        $aprobados = [];
                                        $rechazados = [];
                                        foreach ($latestLiberacionesByClass as $tipo => $data) {
                                            $lib = $data['lib'];
                                            if ($lib->decision === 'aprobar') {
                                                $aprobados[] = $tipo;
                                            } elseif ($lib->decision === 'rechazar') {
                                                $rechazados[] = $tipo;
                                            }
                                        }
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
                                                    $libStatus = $targetReg->calidad_revision_status ?? null;
                                                    $perfil = Auth::user()->perfil;
                                                @endphp
                                                @if ($perfil == 4)
                                                    {{-- VISTA CALIDAD --}}
                                                    @if ($libStatus === 'casting_aprobado')
                                                        <span class="badge-modelo-ok" title="Pre-orden de casting enviada y aprobada">
                                                            <img src="{{ asset('images/Aprobado.png') }}" alt="Aprobado"
                                                                style="width: 38px; height: 38px;">
                                                        </span>
                                                    @elseif (in_array($libStatus, ['aprobado', 'calidad_aprobado']))
                                                        <span class="badge-modelo-ok" title="Modelo liberado y aprobado por Calidad">
                                                            <img src="{{ asset('images/Aprobado.png') }}" alt="Aprobado"
                                                                style="width: 38px; height: 38px;">
                                                        </span>
                                                    @elseif (in_array($libStatus, ['rechazado', 'calidad_rechazado']))
                                                        <span class="badge-modelo-rechazado" title="Modelo rechazado por Calidad">
                                                            <img src="{{ asset('images/Rechazado.png') }}" alt="Rechazado"
                                                                style="width: 38px; height: 38px;">
                                                        </span>
                                                    @elseif (in_array($libStatus, ['mixto', 'calidad_mixto']))
                                                        <span class="badge-modelo-ok" title="Modelo con liberación mixta por Calidad" style="background-color: #e0f2fe; border-color: #bae6fd;">
                                                            <img src="{{ asset('images/Quality.png') }}" alt="Mixto"
                                                                style="width: 38px; height: 38px; object-fit: contain;">
                                                        </span>
                                                    @elseif ($libStatus === 'pendiente')
                                                        <span class="badge-modelo-guardado" title="Datos capturados por Calidad (borrador)">
                                                            <img src="{{ asset('images/Guardado.png') }}" alt="Guardado"
                                                                style="width: 38px; height: 38px;">
                                                        </span>
                                                    @elseif ($targetReg->tiene_modelo || $targetReg->pre_orden_sent)
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
                                                    @if ($libStatus === 'casting_aprobado')
                                                        <span class="badge-modelo-ok" title="Pre-orden de casting enviada y aprobada">
                                                            <img src="{{ asset('images/Aprobado.png') }}" alt="Aprobado"
                                                                style="width: 38px; height: 38px;">
                                                        </span>
                                                    @elseif (in_array($libStatus, ['aprobado', 'calidad_aprobado']))
                                                        <span class="badge-modelo-ok" title="Modelo liberado y aprobado por Calidad">
                                                            <img src="{{ asset('images/Aprobado.png') }}" alt="Aprobado"
                                                                style="width: 38px; height: 38px;">
                                                        </span>
                                                    @elseif (in_array($libStatus, ['rechazado', 'calidad_rechazado']))
                                                        <span class="badge-modelo-rechazado" title="Modelo rechazado por Calidad">
                                                            <img src="{{ asset('images/Rechazado.png') }}" alt="Rechazado"
                                                                style="width: 38px; height: 38px;">
                                                        </span>
                                                    @elseif (in_array($libStatus, ['mixto', 'calidad_mixto']))
                                                        <span class="badge-modelo-espera" title="Modelo con liberación mixta por Calidad" style="background-color: #e0f2fe; border-color: #bae6fd;">
                                                            <img src="{{ asset('images/Quality.png') }}" alt="Mixto"
                                                                style="width: 38px; height: 38px; object-fit: contain;">
                                                        </span>
                                                    @elseif ($targetReg->tiene_modelo || $targetReg->pre_orden_sent)
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
                                                                    onclick="almacenVerPdf('{{ $ayudaArchivo['ot'] }}', '{{ $ayudaArchivo['nombre'] }}', '{{ $ayudaArchivo['tipo'] }}')"
                                                                    style="cursor: pointer;" title="Abrir PDF">
                                                                    <img src="{{ asset('images/pdf-view-shadow.png') }}"
                                                                        class="file-icon icon-default">
                                                                    <img src="{{ asset('images/pdf-view.png') }}" class="file-icon icon-hover">
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
                                                                        <button class="btn-dibujos btn-dibujos-sm btn-eliminar"
                                                                            style="background-color: #dc3545; color: white;"
                                                                            onclick="almacenEliminarOtroArchivo('{{ $otroArchivo['ot'] }}', '{{ $otroArchivo['nombre'] }}', 'otro', this)">Eliminar</button>
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
                                                                        <img src="{{ asset('images/pdf-view.png') }}" class="file-icon icon-hover">
                                                                    </div>
                                                                    <div class="file-name" style="cursor: pointer;" title="Abrir PDF"
                                                                        onclick="almacenVerPdf('{{ $otroArchivo['ot'] }}', '{{ $otroArchivo['nombre'] }}', '{{ $otroArchivo['tipo'] }}')">
                                                                        {{ basename($otroArchivo['nombre']) }}
                                                                    </div>
                                                                    <div class="file-actions" style="display: flex; gap: 5px;">
                                                                        <button class="btn-dibujos btn-dibujos-sm btn-ver"
                                                                            style="background-color: #155724; color: white;"
                                                                            onclick="almacenVerPdf('{{ $otroArchivo['ot'] }}', '{{ $otroArchivo['nombre'] }}', '{{ $otroArchivo['tipo'] }}')">Ver</button>
                                                                        <button class="btn-dibujos btn-dibujos-sm btn-eliminar"
                                                                            style="background-color: #dc3545; color: white;"
                                                                            onclick="almacenEliminarOtroArchivo('{{ $otroArchivo['ot'] }}', '{{ $otroArchivo['nombre'] }}', '{{ $otroArchivo['tipo'] }}', this)">Eliminar</button>
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
                                                                        <button class="btn-dibujos btn-dibujos-sm btn-eliminar"
                                                                            style="background-color: #dc3545; color: white;"
                                                                            onclick="almacenEliminarOtroArchivo('{{ $otroArchivo['ot'] }}', '{{ $otroArchivo['nombre'] }}', 'otro', this)">Eliminar</button>
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
                                                                        <img src="{{ asset('images/pdf-view.png') }}" class="file-icon icon-hover">
                                                                    </div>
                                                                    <div class="file-name" style="cursor: pointer;" title="Abrir PDF"
                                                                        onclick="almacenVerPdf('{{ $otroArchivo['ot'] }}', '{{ $otroArchivo['nombre'] }}', '{{ $otroArchivo['tipo'] }}')">
                                                                        {{ basename($otroArchivo['nombre']) }}
                                                                    </div>
                                                                    <div class="file-actions" style="display: flex; gap: 5px;">
                                                                        <button class="btn-dibujos btn-dibujos-sm btn-ver"
                                                                            style="background-color: #9c0300; color: white;"
                                                                            onclick="almacenVerPdf('{{ $otroArchivo['ot'] }}', '{{ $otroArchivo['nombre'] }}', '{{ $otroArchivo['tipo'] }}')">Ver</button>
                                                                        <button class="btn-dibujos btn-dibujos-sm btn-eliminar"
                                                                            style="background-color: #dc3545; color: white;"
                                                                            onclick="almacenEliminarOtroArchivo('{{ $otroArchivo['ot'] }}', '{{ $otroArchivo['nombre'] }}', '{{ $otroArchivo['tipo'] }}', this)">Eliminar</button>
                                                                    </div>
                                                                </div>
                                                            @endif
                                                        @endforeach
                                                    </div>
                                                @endif

                                                {{-- ── SECCIÓN CONTROL DE MODELOS (Solo Almacén y OTs Activas) ── --}}
                                                @if (Auth::user()->perfil != 4 && $estado === 'activa')
                                                    @php
                                                        $isFinalized = in_array($reg->calidad_revision_status, ['aprobado', 'calidad_aprobado', 'rechazado', 'calidad_rechazado', 'mixto', 'calidad_mixto', 'casting_aprobado']);
                                                    @endphp
                                                    @if (!$isFinalized)
                                                        @php
                                                            // Detectar si es una OT de re-proceso (_R1, _R2, etc.)
                                                            $esReproceso = (bool) preg_match('/_R\d+$/i', $reg->ot);
                                                            $controlDisabled = ($reg->tiene_modelo || $reg->pre_orden_email_sent) ? 'opacity: 0.5; pointer-events: none;' : '';
                                                            $hideSiNo    = ($esReproceso || $reg->pre_orden_sent || $reg->pre_orden_email_sent) ? 'display: none;' : '';
                                                            $hideEditMail = ($reg->pre_orden_sent && !$reg->pre_orden_email_sent) ? '' : 'display: none;';
                                                            // Para reproceso: mostrar Pre-Orden si aún no se ha generado
                                                            $hideReprocesoPreOrden = ($esReproceso && !$reg->pre_orden_sent && !$reg->pre_orden_email_sent) ? '' : 'display: none;';
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
                                                                            ✅ Alerta enviada a Calidad. En espera de su revisión y nuevo veredicto de liberación.
                                                                        @elseif ($reg->pre_orden_sent)
                                                                            @if ($esReproceso)
                                                                                🔄 Pre-orden de re-proceso lista. Puedes editar los datos o enviar la alerta a Calidad para iniciar la revisión.
                                                                            @else
                                                                                Pre-orden lista. Puedes seguir editando los datos o enviarla por correo.
                                                                            @endif
                                                                        @elseif ($esReproceso)
                                                                            🔄 OT en re-proceso por rechazo de Calidad. Genera o edita la pre-orden de modelo para iniciar el nuevo ciclo de fabricación.
                                                                        @else
                                                                            ¿Ya cuentas con el modelo de esta OT o necesitas generar una pre-orden?
                                                                        @endif
                                                                    </h4>
                                                                    <div class="lib-calidad-card-btns">
                                                                        {{-- Botones para OT normal (no reproceso) --}}
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

                                                                        {{-- Botón inicial para re-proceso: generar pre-orden --}}
                                                                        <button class="btn-modelo btn-modelo-no"
                                                                            onclick="abrirModalPreOrden('{{ $reg->ot }}')"
                                                                            title="Generar / editar la pre-orden de fabricación de modelo" style="{{ $hideReprocesoPreOrden }}">
                                                                            <img src="{{ asset('images/pdf.png') }}" alt="Pre-Orden">
                                                                            <span>Pre-Orden Modelo</span>
                                                                        </button>

                                                                        {{-- Editar + Enviar Alerta (cuando pre-orden ya existe, normal o reproceso) --}}
                                                                        <button class="btn-modelo btn-modelo-edit"
                                                                            onclick="abrirModalPreOrden('{{ $reg->ot }}')"
                                                                            title="Editar información de la preorden existente"
                                                                            style="{{ $hideEditMail }}">
                                                                            <img src="{{ asset('images/editar-informacion.png') }}" alt="Editar">
                                                                            <span>Editar Pre-orden</span>
                                                                        </button>
                                                                        <button class="btn-modelo btn-modelo-email"
                                                                            onclick="abrirModalEnviarPreOrden('{{ $reg->ot }}', 'modelo')"
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
                                                            $liberaciones = \App\Models\LiberacionModeloFundicion::where('ot', $reg->ot)->get();
                                                            $aprobados = $liberaciones->where('decision', 'aprobar')->pluck('tipo_modelo')->toArray();
                                                            $rechazados = $liberaciones->where('decision', 'rechazar')->pluck('tipo_modelo')->toArray();
                                                            $castingEmailSent = ($targetReg->calidad_revision_status === 'casting_aprobado');
                                                        @endphp

                                                        {{-- Card Final: Pre-orden enviada por correo (flujo directo sin paso por Calidad/liberaciones) --}}
                                                        @if ($castingEmailSent && count($aprobados) === 0)
                                                            <div class="lib-calidad-card" id="control-almacen-aprobados-{{ md5($reg->ot) }}" style="margin-top: 15px; opacity: 0.9; pointer-events: none; border: 2px solid #16a34a;">
                                                                <div class="lib-calidad-card-header" style="background: linear-gradient(135deg, #16a34a, #15803d); border-bottom: 2px solid rgba(22, 163, 74, 0.5);">
                                                                    <img src="{{ asset('images/almacen.png') }}" alt="Almacén" style="width:38px;height:38px;object-fit:contain;flex-shrink:0;">
                                                                    <div style="overflow:hidden;">
                                                                        <span class="lib-calidad-card-title" style="color: #ffffff;">Control de Modelos &mdash; Almacén</span>
                                                                        <span class="lib-calidad-card-ot" style="color: #d1fae5;">{{ preg_replace('/_\d{8}_\d{6}_.*/', '', $reg->ot) }}</span>
                                                                    </div>
                                                                </div>
                                                                <div class="lib-calidad-card-body">
                                                                    <div class="lib-calidad-action-row">
                                                                        <h4 class="lib-calidad-card-prompt">
                                                                            <span style="color: #15803d; font-weight: 700; display: inline-flex; align-items: center; gap: 8px;">
                                                                                <img src="{{ asset('images/ready.png') }}" style="width: 22px; height: 22px; vertical-align: middle;" alt="Listo">
                                                                                Proceso de pre-orden finalizado correctamente. El correo ha sido enviado al proveedor. Favor de esperar nuevas instrucciones.
                                                                            </span>
                                                                        </h4>
                                                                        <div class="lib-calidad-card-btns">
                                                                            {{-- Sin botones: el proceso está terminado --}}
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        @endif

                                                        {{-- Card 1: Approved models --}}
                                                        @if (count($aprobados) > 0)
                                                            @php
                                                                $castingPre = \App\Models\PreOrdenFundicion::where('ot', $reg->ot)->where('pdf_filename', 'LIKE', '%Casting%')->first();
                                                                $hasCastingPre = (bool) $castingPre;
                                                                $aprobCardDisabled = $castingEmailSent ? 'opacity: 0.85; pointer-events: none;' : '';
                                                            @endphp
                                                            <div class="lib-calidad-card" id="control-almacen-aprobados-{{ md5($reg->ot) }}" style="margin-top: 15px; {{ $aprobCardDisabled }}">
                                                                <div class="lib-calidad-card-header" style="background: linear-gradient(135deg, #16a34a, #15803d); border-bottom: 2px solid rgba(22, 163, 74, 0.5);">
                                                                    <img src="{{ asset('images/almacen.png') }}" alt="Almacén" style="width:38px;height:38px;object-fit:contain;flex-shrink:0;">
                                                                    <div style="overflow:hidden;">
                                                                        <span class="lib-calidad-card-title" style="color: #ffffff;">Control de Modelos &mdash; Almacén (Aprobados)</span>
                                                                        <span class="lib-calidad-card-ot" style="color: #d1fae5;">{{ preg_replace('/_\d{8}_\d{6}_.*/', '', $reg->ot) }}</span>
                                                                    </div>
                                                                </div>
                                                                <div class="lib-calidad-card-body">
                                                                    <div class="lib-calidad-action-row">
                                                                        <h4 class="lib-calidad-card-prompt">
                                                                            @if ($castingEmailSent)
                                                                                <span style="color: #15803d; font-weight: 700; display: inline-flex; align-items: center; gap: 8px;">
                                                                                    <img src="{{ asset('images/ready.png') }}" style="width: 20px; height: 20px; vertical-align: middle;" alt="Listo">
                                                                                    Proceso de pre-orden finalizado correctamente. El correo ha sido enviado al proveedor. Favor de esperar nuevas instrucciones.
                                                                                </span>
                                                                            @elseif ($hasCastingPre)
                                                                                Pre-orden de casting generada para los modelos: <strong>{{ implode(', ', $aprobados) }}</strong>. Puedes editar los datos o enviar la pre-orden por correo.
                                                                            @elseif ($reg->casting_pdf_generated)
                                                                                Formatos LDM subidos. Procede a generar la Pre-Orden de Fabricación de Casting para los modelos: <strong>{{ implode(', ', $aprobados) }}</strong>.
                                                                            @else
                                                                                Modelos Aprobados por Calidad: <strong>{{ implode(', ', $aprobados) }}</strong>. Procede a subir los formatos F-CCL-LDM firmados para iniciar el casting.
                                                                            @endif
                                                                        </h4>
                                                                        <div class="lib-calidad-card-btns">
                                                                            @if ($castingEmailSent)
                                                                                {{-- Controles ocultos tras finalizar el proceso --}}
                                                                            @elseif ($hasCastingPre)
                                                                                <button class="btn-modelo btn-modelo-si" onclick="abrirModalPreOrdenCasting('{{ $reg->ot }}')" style="display: flex; background-color: #15803d; color: white;">
                                                                                    <img src="{{ asset('images/editar-informacion.png') }}" alt="Editar">
                                                                                    <span>Editar Pre-orden</span>
                                                                                </button>
                                                                                <button class="btn-modelo btn-modelo-email" onclick="abrirModalEnviarPreOrden('{{ $reg->ot }}', 'casting')" style="display: flex; background-color: #033966; color: white;">
                                                                                    <img src="{{ asset('images/enviando.png') }}" alt="Enviar">
                                                                                    <span>Enviar Correo</span>
                                                                                </button>
                                                                            @elseif ($reg->casting_pdf_generated)
                                                                                <button class="btn-modelo btn-modelo-si" onclick="abrirModalPreOrdenCasting('{{ $reg->ot }}')" style="display: flex; background-color: #15803d; color: white;">
                                                                                    <img src="{{ asset('images/almacen.png') }}" alt="Preorden" style="width: 16px; height: 16px; filter: brightness(0) invert(1);">
                                                                                    <span>Preorden de Casting</span>
                                                                                </button>
                                                                            @else
                                                                                <button class="btn-modelo btn-modelo-si" onclick="abrirModalGestionVeredicto('{{ $reg->ot }}', {{ json_encode($aprobados) }}, [])" style="display: flex; background-color: #15803d; color: white;">
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
                                                        @if (count($rechazados) > 0)
                                                            @php
                                                                $latestReproceso = null;
                                                                if ($reg->rechazos_procesados) {
                                                                    $latestReproceso = \App\Models\FundicionHistory::where('ot', 'LIKE', $reg->ot . '_R%')
                                                                        ->orderBy('id', 'desc')
                                                                        ->first();
                                                                }

                                                                $rechCardDisabled = '';
                                                                $ultimoRechazadoPorCalidad = false;
                                                                if ($reg->rechazos_procesados) {
                                                                    $ultimoRechazadoPorCalidad = $latestReproceso && in_array($latestReproceso->calidad_revision_status, ['rechazado', 'calidad_rechazado', 'mixto', 'calidad_mixto']) && !$latestReproceso->rechazos_procesados;
                                                                    if ($ultimoRechazadoPorCalidad) {
                                                                        $rechCardDisabled = '';
                                                                    } elseif (!$latestReproceso || $latestReproceso->pre_orden_email_sent || $latestReproceso->tiene_modelo) {
                                                                        $rechCardDisabled = 'opacity: 0.65; pointer-events: none;';
                                                                    }
                                                                }
                                                            @endphp
                                                            <div class="lib-calidad-card" id="control-almacen-rechazados-{{ md5($reg->ot) }}" style="margin-top: 15px; {{ $rechCardDisabled }}">
                                                                <div class="lib-calidad-card-header" style="background: linear-gradient(135deg, #dc2626, #b91c1c); border-bottom: 2px solid rgba(220, 38, 38, 0.5);">
                                                                    <img src="{{ asset('images/almacen.png') }}" alt="Almacén" style="width:38px;height:38px;object-fit:contain;flex-shrink:0;">
                                                                    <div style="overflow:hidden;">
                                                                        <span class="lib-calidad-card-title" style="color: #ffffff;">Control de Modelos &mdash; Almacén (Rechazados)</span>
                                                                        <span class="lib-calidad-card-ot" style="color: #fee2e2;">{{ preg_replace('/_\d{8}_\d{6}_.*/', '', $reg->ot) }}</span>
                                                                    </div>
                                                                </div>
                                                                <div class="lib-calidad-card-body">
                                                                    <div class="lib-calidad-action-row">
                                                                        <h4 class="lib-calidad-card-prompt">
                                                                            @if ($reg->rechazos_procesados)
                                                                                @if ($ultimoRechazadoPorCalidad && $latestReproceso)
                                                                                    Modelos Rechazados por Calidad para la OT de re-proceso <strong>{{ $latestReproceso->ot }}</strong>: <strong>{{ implode(', ', $rechazados) }}</strong>. Procede a subir el Formato de Rechazo y el SCAR correspondiente.
                                                                                @elseif ($latestReproceso)
                                                                                    @if ($latestReproceso->pre_orden_email_sent)
                                                                                        Alerta enviada a Calidad para la OT de re-proceso <strong>{{ $latestReproceso->ot }}</strong>. En espera de su revisión y nuevo veredicto.
                                                                                    @elseif ($latestReproceso->pre_orden_sent)
                                                                                        Pre-orden de re-proceso lista para <strong>{{ $latestReproceso->ot }}</strong>. Puedes editar los datos o enviar la alerta a Calidad para iniciar la revisión.
                                                                                    @else
                                                                                        OT en re-proceso por rechazo de Calidad (<strong>{{ $latestReproceso->ot }}</strong>). Genera o edita la pre-orden de modelo para iniciar el nuevo ciclo.
                                                                                    @endif
                                                                                @else
                                                                                    Formatos de rechazo y SCAR subidos para los modelos: <strong>{{ implode(', ', $rechazados) }}</strong>. Nueva pre-orden de modelo generada.
                                                                                @endif
                                                                            @else
                                                                                Modelos Rechazados por Calidad: <strong>{{ implode(', ', $rechazados) }}</strong>. Procede a subir el Formato de Rechazo y el SCAR correspondiente.
                                                                            @endif
                                                                        </h4>
                                                                        <div class="lib-calidad-card-btns">
                                                                            @if ($reg->rechazos_procesados)
                                                                                @if ($ultimoRechazadoPorCalidad && $latestReproceso)
                                                                                    <button class="btn-modelo btn-modelo-no" onclick="abrirModalGestionVeredicto('{{ $latestReproceso->ot }}', [], {{ json_encode($rechazados) }})" style="display: flex; background-color: #b91c1c; color: white;">
                                                                                        <img src="{{ asset('images/Rechazado.png') }}" alt="No">
                                                                                        <span>Procesar Rechazados</span>
                                                                                    </button>
                                                                                @elseif ($latestReproceso)
                                                                                    @php
                                                                                        $hideReprocesoPreOrden = (!$latestReproceso->pre_orden_sent && !$latestReproceso->pre_orden_email_sent) ? '' : 'display: none;';
                                                                                        $hideEditMail = ($latestReproceso->pre_orden_sent && !$latestReproceso->pre_orden_email_sent) ? '' : 'display: none;';
                                                                                    @endphp
                                                                                    {{-- Botón inicial para re-proceso: generar pre-orden --}}
                                                                                    <button class="btn-modelo btn-modelo-no"
                                                                                        onclick="abrirModalPreOrden('{{ $latestReproceso->ot }}')"
                                                                                        title="Generar / editar la pre-orden de fabricación de modelo" style="{{ $hideReprocesoPreOrden }}">
                                                                                        <img src="{{ asset('images/pdf.png') }}" alt="Pre-Orden">
                                                                                        <span>Pre-Orden Modelo</span>
                                                                                    </button>

                                                                                    {{-- Editar + Enviar Alerta (cuando pre-orden ya existe) --}}
                                                                                    <button class="btn-modelo btn-modelo-edit"
                                                                                        onclick="abrirModalPreOrden('{{ $latestReproceso->ot }}')"
                                                                                        title="Editar información de la preorden existente"
                                                                                        style="{{ $hideEditMail }}">
                                                                                        <img src="{{ asset('images/editar-informacion.png') }}" alt="Editar">
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
                                                                                    <button class="btn-modelo btn-modelo-no" style="display: flex; background-color: #b91c1c; color: white;">
                                                                                        <img src="{{ asset('images/Rechazado.png') }}" alt="No">
                                                                                        <span>Rechazos Procesados</span>
                                                                                    </button>
                                                                                @endif
                                                                            @else
                                                                                <button class="btn-modelo btn-modelo-no" onclick="abrirModalGestionVeredicto('{{ $reg->ot }}', [], {{ json_encode($rechazados) }})" style="display: flex; background-color: #b91c1c; color: white;">
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
                                                            @if ($targetReg->calidad_revision_status === 'rechazado')
                                                                <div class="lib-estado-badge lib-estado-rechazado" style="padding: 12px 16px; width: 100%; box-sizing: border-box; display: flex; align-items: center; gap: 8px;">
                                                                    <img src="{{ asset('images/Rechazado.png') }}" alt=""
                                                                        style="width:18px;height:18px;object-fit:contain;flex-shrink:0;">
                                                                    <span>Liberacion rechazada anteriormente. Puedes revisar y volver a emitir
                                                                        un veredicto.</span>
                                                                </div>
                                                            @elseif (is_null($targetReg->calidad_revision_status) && !$targetReg->pre_orden_sent && !$targetReg->tiene_modelo)
                                                                <div class="lib-estado-badge lib-estado-info" style="width: 100%; box-sizing: border-box; display: flex; align-items: center; gap: 8px;">
                                                                    En espera de que Almacén envíe la información necesaria para realizar la
                                                                    liberación.
                                                                </div>
                                                            @elseif ($targetReg->calidad_revision_status === 'pendiente')
                                                                <div class="lib-estado-badge lib-estado-guardado" style="width: 100%; box-sizing: border-box; display: flex; align-items: center; gap: 8px;">
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
                                                                    ->filter(function($claseNombre) use ($reg) {
                                                                        $clLow = strtolower($claseNombre);
                                                                        $tipo = null;
                                                                        if (strpos($clLow, 'fondo') !== false) $tipo = 'Fondo';
                                                                        elseif (strpos($clLow, 'obturador') !== false) $tipo = 'Obturador';
                                                                        elseif (strpos($clLow, 'molde') !== false) $tipo = 'Molde';
                                                                        elseif (strpos($clLow, 'bombillo') !== false) $tipo = 'Bombillo';

                                                                        if ($tipo) {
                                                                            $baseOt = preg_replace('/_R\d+$/i', '', $reg->ot);
                                                                            $isAprobado = \App\Models\LiberacionModeloFundicion::where(function($q) use ($reg, $baseOt) {
                                                                                    $q->where('ot', '=', $reg->ot)
                                                                                      ->orWhere('ot', '=', $baseOt)
                                                                                      ->orWhere('ot', 'LIKE', $baseOt . '_R%');
                                                                                })
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
                                                                        $hasDraft = \App\Models\LiberacionModeloFundicion::where('ot', '=', $targetReg->ot)
                                                                            ->where('tipo_modelo', '=', $tipo)
                                                                            ->where('estado', '=', 'pendiente')
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
                                                                $hasRechazoBorrador = \App\Models\LiberacionModeloFundicion::where('ot', '=', $targetReg->ot)
                                                                    ->where('estado', '=', 'pendiente')
                                                                    ->where('decision', '=', 'rechazar')
                                                                    ->exists();

                                                                $hasAprobadoBorrador = \App\Models\LiberacionModeloFundicion::where('ot', '=', $targetReg->ot)
                                                                    ->where('estado', '=', 'pendiente')
                                                                    ->where('decision', '=', 'aprobar')
                                                                    ->exists();

                                                                $decisionGlobal = 'aprobar';
                                                                if ($hasRechazoBorrador && $hasAprobadoBorrador) {
                                                                    $decisionGlobal = 'mixto';
                                                                } elseif ($hasRechazoBorrador) {
                                                                    $decisionGlobal = 'rechazar';
                                                                }

                                                                $borradorRechazado = \App\Models\LiberacionModeloFundicion::where('ot', '=', $targetReg->ot)
                                                                    ->where('estado', '=', 'pendiente')
                                                                    ->where('decision', '=', 'rechazar')
                                                                    ->first();

                                                                $tiposGuardados = \App\Models\LiberacionModeloFundicion::where('ot', '=', $targetReg->ot)
                                                                    ->where('estado', '=', 'pendiente')
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
                                                                     @elseif ($targetReg->calidad_revision_status === 'rechazado')
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
                                                                             <span>Editar Datos</span>
                                                                         </button>

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
                                                                                     title="Finalizar proceso de Calidad y notificar por correo"
                                                                                     style="background-color: #dc2626; color: white;">
                                                                                     <img src="{{ asset('images/enviando.png') }}" alt="">
                                                                                     <span>Finalizar Proceso</span>
                                                                                 </button>
                                                                             @endif
                                                                         @else
                                                                             <button class="btn-calidad-action btn-calidad-email"
                                                                                 onclick="abrirModalFinalizarCalidad('{{ $targetReg->ot }}', '{{ $decisionGlobal }}', {{ $tiposAprobadosJson }}, {{ $tiposRechazadosJson }})"
                                                                                 title="Finalizar proceso de Calidad y notificar por correo"
                                                                                 style="background-color: #059669; color: white;">
                                                                                 <img src="{{ asset('images/enviando.png') }}" alt="">
                                                                                 <span>Finalizar Proceso</span>
                                                                             </button>
                                                                         @endif
                                                                     @else
                                                                         <button class="btn-calidad-action btn-calidad-iniciar" @if (!$targetReg->pre_orden_email_sent && !$targetReg->tiene_modelo) disabled
                                                                             style="opacity: 0.55; cursor: not-allowed;"
                                                                             title="En espera de que Almacén envíe la información necesaria para realizar la liberación"
                                                                         @else
                                                                                 title="{{ $contClasesConDatos > 0 ? 'Continuar con el proceso de liberación' : 'Iniciar el proceso de liberación' }}"
                                                                             @endif
                                                                             onclick="abrirModalLiberacionUnificado('{{ $targetReg->ot }}', {{ json_encode($clasesActivas) }}, {{ json_encode($targetReg->ayudas_config ?? []) }})">
                                                                             <img src="{{ asset('images/Liberar.png') }}" alt="">
                                                                             <span>{{ $contClasesConDatos > 0 ? 'Continuar con el proceso de liberación' : 'Empezar con el proceso de liberación' }}</span>
                                                                         </button>
                                                                     @endif
                                                                 </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @else
                                                    @if (Auth::user()->perfil == 4)
                                                        @php
                                                            $libStatusClean = str_replace('calidad_', '', $targetReg->calidad_revision_status);
                                                        @endphp
                                                        @if (in_array($targetReg->calidad_revision_status, ['aprobado', 'calidad_aprobado', 'rechazado', 'calidad_rechazado', 'mixto', 'calidad_mixto']))
                                                            @php
                                                                $liberaciones = \App\Models\LiberacionModeloFundicion::where('ot', $targetReg->ot)->get();
                                                                $aprobados = $liberaciones->where('decision', 'aprobar')->pluck('tipo_modelo')->toArray();
                                                                $rechazados = $liberaciones->where('decision', 'rechazar')->pluck('tipo_modelo')->toArray();
                                                            @endphp
                                                            <div class="lib-calidad-card" id="control-calidad-finalizado-{{ md5($targetReg->ot) }}" style="opacity: 0.65; pointer-events: none; margin-top: 20px;">
                                                                <div class="lib-calidad-card-header" style="background: linear-gradient(135deg, #475569, #334155); border-bottom: 2px solid rgba(71, 85, 105, 0.5);">
                                                                    <img src="{{ asset('images/Quality.png') }}" alt="Calidad" style="width:38px;height:38px;object-fit:contain;flex-shrink:0;">
                                                                    <div style="overflow:hidden;">
                                                                        <span class="lib-calidad-card-title" style="color: #ffffff;">Control de Modelos &mdash; Calidad</span>
                                                                        <span class="lib-calidad-card-ot" style="color: #cbd5e1;">{{ preg_replace('/_\d{8}_\d{6}_.*/', '', $targetReg->ot) }}</span>
                                                                    </div>
                                                                </div>
                                                                <div class="lib-calidad-card-body">
                                                                    <div class="lib-calidad-action-row">
                                                                        <h4 class="lib-calidad-card-prompt">
                                                                            @if ($libStatusClean === 'aprobado')
                                                                                Proceso Finalizado (Aprobado): Se envió la alerta de liberación aprobada para los modelos: <strong>{{ implode(', ', $aprobados) }}</strong>.
                                                                            @elseif ($libStatusClean === 'rechazado')
                                                                                Proceso Finalizado (Rechazado): Se envió la alerta de rechazo para los modelos: <strong>{{ implode(', ', $rechazados) }}</strong>.
                                                                            @elseif ($libStatusClean === 'mixto')
                                                                                Proceso Finalizado (Mixto): Se enviaron las alertas correspondientes. Aprobados: <strong>{{ implode(', ', $aprobados) }}</strong> | Rechazados: <strong>{{ implode(', ', $rechazados) }}</strong>.
                                                                            @endif
                                                                        </h4>
                                                                        <div class="lib-calidad-card-btns">
                                                                            @if ($libStatusClean === 'aprobado')
                                                                                <button class="btn-modelo btn-modelo-si" style="display: flex; background-color: #059669; color: white;">
                                                                                    <img src="{{ asset('images/Aprobado.png') }}" alt="Si">
                                                                                    <span>Aprobado</span>
                                                                                </button>
                                                                            @elseif ($libStatusClean === 'rechazado')
                                                                                <button class="btn-modelo btn-modelo-no" style="display: flex; background-color: #dc2626; color: white;">
                                                                                    <img src="{{ asset('images/Rechazado.png') }}" alt="No">
                                                                                    <span>Rechazado</span>
                                                                                </button>
                                                                            @elseif ($libStatusClean === 'mixto')
                                                                                <button class="btn-modelo btn-modelo-edit" style="display: flex; background-color: #0284c7; color: white;">
                                                                                    <img src="{{ asset('images/Quality.png') }}" alt="Mixto">
                                                                                    <span>Mixto</span>
                                                                                </button>
                                                                            @endif
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        @endif
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
                            <div id="po-observaciones-cycle-prefix" style="display: none; padding: 8px 12px; background-color: #fee2e2; border-left: 4px solid #ef4444; color: #991b1b; font-weight: bold; margin-bottom: 8px; border-radius: 4px; font-family: 'Poppins', sans-serif;"></div>
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

    {{-- ── MODAL: PRE-ORDEN PARA FABRICAR CASTING (DOUBLE MODAL TABS) ── --}}
    <div id="modalPreOrdenCasting" class="alm-modal" role="dialog" aria-modal="true">
        <div class="alm-modal-content" style="max-width: 1800px; width: 95vw; border-radius: 20px; overflow: hidden; border: 1.5px solid #0284c7;">
            <div class="alm-modal-header" id="poc-header" style="background: linear-gradient(135deg, #0369a1, #0284c7); padding: 2.2em 2.5em 1.5em; position: relative;">
                <div class="div-cerrar">
                    <button type="button" class="btn-cerrar" onclick="cerrarModalPreOrdenCasting()"
                        style="position: absolute; top: 25px; right: 25px; background: rgba(255, 255, 255, 0.18); border: 1.5px solid rgba(255, 255, 255, 0.45); border-radius: 50%; width: 42px; height: 42px; display: inline-flex; align-items: center; justify-content: center; cursor: pointer; transition: all 0.2s ease;">
                        <img class="img-cerrar" src="{{ asset('images/cerrar.png') }}" style="width: 14px; height: 14px; filter: brightness(0) invert(1);" alt="Cerrar">
                    </button>
                </div>
                <h3 style="font-size: 2em; margin: 0; font-family:'Poppins', sans-serif; font-weight: 700; color: #fff;">Pre-Orden de Fabricación de Casting (4ALM-17)</h3>
                <p id="poc-modal-subtitle" class="lib-modal-subtitle" style="color: #bae6fd; font-size: 1.15em; margin-top: 8px; margin-bottom: 0; font-family:'Poppins', sans-serif; font-weight: 500;"></p>

                {{-- Tabs/Pestañas para los dos proveedores --}}
                <div style="display: flex; gap: 10px; margin-top: 25px; border-bottom: 2px solid rgba(255,255,255,0.2); padding-bottom: 0; align-items: center;">
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
                        <img src="{{ asset('images/anadir.png') }}" style="width: 14px; height: 14px; filter: brightness(0) invert(1);" alt=""> Agregar Proveedor 2
                    </button>
                    <button type="button" id="btn-remove-poc-page-2" class="btns btn-remove-tab" onclick="removerPocPagina2()"
                        style="display: none; align-items: center; gap: 6px; padding: 8px 16px; background: #dc2626; border: 1.5px solid #b91c1c; border-radius: 8px; color: #ffffff; cursor: pointer; font-family: 'Poppins', sans-serif; font-size: 0.9em; font-weight: 500; transition: all 0.2s ease; margin-left: 15px; height: auto;">
                        Remover Proveedor 2
                    </button>
                </div>
            </div>

            <div class="alm-modal-body" style="padding: 2.5em; background: #fafafa; font-family: 'Poppins', sans-serif;">
                <form id="formPreOrdenCasting" novalidate autocomplete="off">
                    @csrf
                    <input type="hidden" id="poc-has-page2" name="has_page2" value="0">

                    {{-- ══════════════ PAGINA 1 ══════════════ --}}
                    <div id="poc-page-1" class="poc-page">
                        <div class="form-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 20px; margin-bottom: 25px;">
                            <div class="form-group">
                                <label for="poc-p1-proveedor" style="font-weight: 700; color: #334155; margin-bottom: 8px; display: block; font-family:'Poppins', sans-serif;">Proveedor:</label>
                                <select id="poc-p1-proveedor" name="page1_proveedor" class="form-control" required onchange="handlePocProveedorChange(1)" style="height: auto; padding: 12px 16px; border-radius: 10px; font-family:'Poppins', sans-serif; font-size: 1.05em;">
                                    <option value="SS Metal Foundry, S. de R. L. de C. V.">SS Metal Foundry, S. de R. L. de C. V.</option>
                                    <option value="Fundición Especializada, S. A. de C. V.">Fundición Especializada, S. A. de C. V.</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="poc-p1-fecha" style="font-weight: 700; color: #334155; margin-bottom: 8px; display: block; font-family:'Poppins', sans-serif;">Fecha:</label>
                                <input type="date" id="poc-p1-fecha" name="page1_fecha" class="form-control" readonly required style="height: auto; padding: 12px 16px; border-radius: 10px; font-family:'Poppins', sans-serif; font-size: 1.05em; background: #f1f5f9;">
                            </div>
                            <div class="form-group">
                                <label for="poc-p1-folio" style="font-weight: 700; color: #334155; margin-bottom: 8px; display: block; font-family:'Poppins', sans-serif;">Folio:</label>
                                <input type="text" id="poc-p1-folio" name="page1_folio" class="form-control" readonly required style="height: auto; padding: 12px 16px; border-radius: 10px; font-family:'Poppins', sans-serif; font-size: 1.05em; background: #f1f5f9; font-weight: bold; color: #0369a1;">
                            </div>
                            <div class="form-group">
                                <label for="poc-p1-moldura" style="font-weight: 700; color: #334155; margin-bottom: 8px; display: block; font-family:'Poppins', sans-serif;">Moldura:</label>
                                <input type="text" id="poc-p1-moldura" name="page1_moldura" class="form-control" readonly required style="height: auto; padding: 12px 16px; border-radius: 10px; font-family:'Poppins', sans-serif; font-size: 1.05em; background: #f1f5f9;">
                            </div>
                            <div class="form-group">
                                <label for="poc-p1-ot" style="font-weight: 700; color: #334155; margin-bottom: 8px; display: block; font-family:'Poppins', sans-serif;">Orden de Trabajo:</label>
                                <input type="text" id="poc-p1-ot" name="page1_ot" class="form-control" readonly required style="height: auto; padding: 12px 16px; border-radius: 10px; font-family:'Poppins', sans-serif; font-size: 1.05em; background: #f1f5f9;">
                            </div>
                            <div class="form-group">
                                <label for="poc-p1-fecha-entrega" style="font-weight: 700; color: #334155; margin-bottom: 8px; display: block; font-family:'Poppins', sans-serif;">Fecha Entrega <span style="color:#dc2626;">*</span>:</label>
                                <input type="date" id="poc-p1-fecha-entrega" name="page1_fecha_entrega" class="form-control" required style="height: auto; padding: 12px 16px; border-radius: 10px; font-family:'Poppins', sans-serif; font-size: 1.05em;">
                            </div>
                        </div>

                        <div class="modal-table-container" style="overflow-x: auto; background: #fff; border: 1px solid #e2e8f0; border-radius: 14px; padding: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
                            <table class="modal-table" style="width: 100%; border-collapse: collapse; text-align: left;">
                                <thead>
                                    <tr style="border-bottom: 2px solid #cbd5e1; color: #475569; font-weight: 700; font-size: 0.95em;">
                                        <th style="padding: 12px 10px; width: 12%; font-family:'Poppins', sans-serif;">Tipo de Modelo</th>
                                        <th style="padding: 12px 10px; width: 8%; font-family:'Poppins', sans-serif;">Cant. Fabricar</th>
                                        <th style="padding: 12px 10px; width: 8%; font-family:'Poppins', sans-serif;">Cant. Consign.</th>
                                        <th style="padding: 12px 10px; width: 15%; font-family:'Poppins', sans-serif;">Descripción</th>
                                        <th style="padding: 12px 10px; width: 14%; font-family:'Poppins', sans-serif;">Material</th>
                                        <th style="padding: 12px 10px; width: 12%; font-family:'Poppins', sans-serif;">Código de Modelo</th>
                                        <th style="padding: 12px 10px; width: 7%; font-family:'Poppins', sans-serif;">Peso Juego (KG)</th>
                                        <th style="padding: 12px 10px; width: 7%; font-family:'Poppins', sans-serif;">Peso Total (KG)</th>
                                        <th style="padding: 12px 10px; width: 12%; font-family:'Poppins', sans-serif;">Fecha Entrega</th>
                                        <th style="padding: 12px 10px; width: 5%; text-align: center; font-family:'Poppins', sans-serif;">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody id="alm-tbody-poc-p1">
                                    {{-- Se llenará por JS --}}
                                </tbody>
                            </table>
                            <div style="margin-top: 15px; text-align: center;">
                                <button type="button" class="btn-img-action" onclick="agregarFilaPoc(1)" title="Añadir una nueva fila" style="border: none; background: none; cursor: pointer; padding: 5px; outline: none; transition: transform 0.2s ease;">
                                    <img src="{{ asset('images/anadir.png') }}" alt="Añadir" style="width: 38px; height: 38px;">
                                </button>
                            </div>
                        </div>

                        <div class="form-group" style="margin-top: 25px;">
                            <label for="poc-p1-observaciones" style="font-weight: 700; color: #334155; margin-bottom: 8px; display: block; font-family:'Poppins', sans-serif;">Observaciones:</label>
                            <textarea id="poc-p1-observaciones" name="page1_observaciones" class="form-control" rows="3" style="border-radius: 10px; padding: 14px; font-family:'Poppins',sans-serif; font-size: 1em; width: 100%; box-sizing: border-box; border: 1.5px solid #cbd5e1;"></textarea>
                        </div>
                    </div>

                    {{-- ══════════════ PAGINA 2 ══════════════ --}}
                    <div id="poc-page-2" class="poc-page" style="display: none;">
                        <div class="form-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 20px; margin-bottom: 25px;">
                            <div class="form-group">
                                <label for="poc-p2-proveedor" style="font-weight: 700; color: #334155; margin-bottom: 8px; display: block; font-family:'Poppins', sans-serif;">Proveedor:</label>
                                <select id="poc-p2-proveedor" name="page2_proveedor" class="form-control" required onchange="handlePocProveedorChange(2)" style="height: auto; padding: 12px 16px; border-radius: 10px; font-family:'Poppins', sans-serif; font-size: 1.05em;">
                                    <option value="Fundición Especializada, S. A. de C. V.">Fundición Especializada, S. A. de C. V.</option>
                                    <option value="SS Metal Foundry, S. de R. L. de C. V.">SS Metal Foundry, S. de R. L. de C. V.</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="poc-p2-fecha" style="font-weight: 700; color: #334155; margin-bottom: 8px; display: block; font-family:'Poppins', sans-serif;">Fecha:</label>
                                <input type="date" id="poc-p2-fecha" name="page2_fecha" class="form-control" readonly required style="height: auto; padding: 12px 16px; border-radius: 10px; font-family:'Poppins', sans-serif; font-size: 1.05em; background: #f1f5f9;">
                            </div>
                            <div class="form-group">
                                <label for="poc-p2-folio" style="font-weight: 700; color: #334155; margin-bottom: 8px; display: block; font-family:'Poppins', sans-serif;">Folio:</label>
                                <input type="text" id="poc-p2-folio" name="page2_folio" class="form-control" readonly required style="height: auto; padding: 12px 16px; border-radius: 10px; font-family:'Poppins', sans-serif; font-size: 1.05em; background: #f1f5f9; font-weight: bold; color: #0369a1;">
                            </div>
                            <div class="form-group">
                                <label for="poc-p2-moldura" style="font-weight: 700; color: #334155; margin-bottom: 8px; display: block; font-family:'Poppins', sans-serif;">Moldura:</label>
                                <input type="text" id="poc-p2-moldura" name="page2_moldura" class="form-control" readonly required style="height: auto; padding: 12px 16px; border-radius: 10px; font-family:'Poppins', sans-serif; font-size: 1.05em; background: #f1f5f9;">
                            </div>
                            <div class="form-group">
                                <label for="poc-p2-ot" style="font-weight: 700; color: #334155; margin-bottom: 8px; display: block; font-family:'Poppins', sans-serif;">Orden de Trabajo:</label>
                                <input type="text" id="poc-p2-ot" name="page2_ot" class="form-control" readonly required style="height: auto; padding: 12px 16px; border-radius: 10px; font-family:'Poppins', sans-serif; font-size: 1.05em; background: #f1f5f9;">
                            </div>
                            <div class="form-group">
                                <label for="poc-p2-fecha-entrega" style="font-weight: 700; color: #334155; margin-bottom: 8px; display: block; font-family:'Poppins', sans-serif;">Fecha Entrega <span style="color:#dc2626;">*</span>:</label>
                                <input type="date" id="poc-p2-fecha-entrega" name="page2_fecha_entrega" class="form-control" required style="height: auto; padding: 12px 16px; border-radius: 10px; font-family:'Poppins', sans-serif; font-size: 1.05em;">
                            </div>
                        </div>

                        <div class="modal-table-container" style="overflow-x: auto; background: #fff; border: 1px solid #e2e8f0; border-radius: 14px; padding: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
                            <table class="modal-table" style="width: 100%; border-collapse: collapse; text-align: left;">
                                <thead>
                                    <tr style="border-bottom: 2px solid #cbd5e1; color: #475569; font-weight: 700; font-size: 0.95em;">
                                        <th style="padding: 12px 10px; width: 12%; font-family:'Poppins', sans-serif;">Tipo de Modelo</th>
                                        <th style="padding: 12px 10px; width: 8%; font-family:'Poppins', sans-serif;">Cant. Fabricar</th>
                                        <th style="padding: 12px 10px; width: 8%; font-family:'Poppins', sans-serif;">Cant. Consign.</th>
                                        <th style="padding: 12px 10px; width: 15%; font-family:'Poppins', sans-serif;">Descripción</th>
                                        <th style="padding: 12px 10px; width: 14%; font-family:'Poppins', sans-serif;">Material</th>
                                        <th style="padding: 12px 10px; width: 12%; font-family:'Poppins', sans-serif;">Código de Modelo</th>
                                        <th style="padding: 12px 10px; width: 7%; font-family:'Poppins', sans-serif;">Peso Juego (KG)</th>
                                        <th style="padding: 12px 10px; width: 7%; font-family:'Poppins', sans-serif;">Peso Total (KG)</th>
                                        <th style="padding: 12px 10px; width: 12%; font-family:'Poppins', sans-serif;">Fecha Entrega</th>
                                        <th style="padding: 12px 10px; width: 5%; text-align: center; font-family:'Poppins', sans-serif;">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody id="alm-tbody-poc-p2">
                                    {{-- Se llenará por JS --}}
                                </tbody>
                            </table>
                            <div style="margin-top: 15px; text-align: center;">
                                <button type="button" class="btn-img-action" onclick="agregarFilaPoc(2)" title="Añadir una nueva fila" style="border: none; background: none; cursor: pointer; padding: 5px; outline: none; transition: transform 0.2s ease;">
                                    <img src="{{ asset('images/anadir.png') }}" alt="Añadir" style="width: 38px; height: 38px;">
                                </button>
                            </div>
                        </div>

                        <div class="form-group" style="margin-top: 25px;">
                            <label for="poc-p2-observaciones" style="font-weight: 700; color: #334155; margin-bottom: 8px; display: block; font-family:'Poppins', sans-serif;">Observaciones:</label>
                            <textarea id="poc-p2-observaciones" name="page2_observaciones" class="form-control" rows="3" style="border-radius: 10px; padding: 14px; font-family:'Poppins',sans-serif; font-size: 1em; width: 100%; box-sizing: border-box; border: 1.5px solid #cbd5e1;"></textarea>
                        </div>
                    </div>

                    <div class="form-actions" style="margin-top: 35px; text-align: center;">
                        <button type="submit" class="btn-save-preorden" id="btn-submit-poc" style="font-size: 1.2em; padding: 15px 35px; border-radius: 10px; font-family: 'Poppins', sans-serif; font-weight: 700; background: linear-gradient(135deg, #0369a1, #0284c7); border: none; color: white; cursor: pointer; transition: all 0.2s ease; box-shadow: 0 4px 15px rgba(3, 105, 161, 0.3); height: auto;">
                            Guardar y Descargar Pre-Orden de Casting
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- ── MODAL: FINALIZAR PROCESO DE CALIDAD (CORREO Y FECHA) ── --}}
    <div id="modalFinalizarCalidad" class="alm-modal" role="dialog" aria-modal="true">
        <div class="alm-modal-content" id="finalizar-calidad-modal-content" style="max-width: 1500px; width: 95vw; border-radius: 20px; overflow: hidden;">
            <div class="alm-modal-header" id="finalizar-calidad-header" style="padding: 1.5em 5.5em 1.2em 2.2em; border-top-left-radius: 18px; border-top-right-radius: 18px; position: relative;">
                <div class="div-cerrar">
                    <button type="button" class="btn-cerrar" onclick="cerrarModalFinalizarCalidad()"
                        style="position: absolute; top: 18px; right: 22px; background: rgba(255, 255, 255, 0.18); border: 1.5px solid rgba(255, 255, 255, 0.45); border-radius: 50%; width: 36px; height: 36px; display: inline-flex; align-items: center; justify-content: center; cursor: pointer; transition: all 0.2s ease;">
                        <img class="img-cerrar" src="{{ asset('images/cerrar.png') }}" style="width: 12px; height: 12px; filter: brightness(0) invert(1);">
                    </button>
                </div>
                <h3 id="finalizar-calidad-title" style="font-size: 1.5em; margin: 0; font-family:'Poppins', sans-serif; font-weight: 700; color: #fff; line-height: 1.3;">Finalizar Proceso de Calidad</h3>
                <p id="finalizar-calidad-subtitle" class="lib-modal-subtitle" style="color: #ffffff; font-size: 0.95em; margin-top: 5px; margin-bottom: 0; font-family:'Poppins', sans-serif; font-weight: 500; opacity: 0.9;"></p>
            </div>
            <div class="alm-modal-body" style="padding: 2.2em 2.5em; background: #fafafa; font-family: 'Poppins', sans-serif;">
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
                        <label for="fc-destinatario" style="font-size: 1.1em; font-weight: 700; color: #334155; display: block; margin-bottom: 8px; font-family:'Poppins', sans-serif;">Destinatario(s) <span style="color:#dc2626;">*</span></label>
                        <input type="text" id="fc-destinatario" name="destinatario" class="form-control" required value="jaxer020406@gmail.com" style="font-size: 1.1em; padding: 12px 18px; height: auto; border-radius: 10px; font-family:'Poppins', sans-serif;">
                        <span style="font-size: 0.85em; color: #64748b; margin-top: 6px; display: block;">Separa múltiples correos usando comas (,).</span>
                    </div>

                    {{-- FECHA (OBLIGATORIA) --}}
                    <div class="form-group" style="margin-bottom: 24px;">
                        <label id="fc-fecha-label" for="fc-fecha" style="font-weight:700; color:#334155; display:block; margin-bottom:8px; font-family:'Poppins', sans-serif; font-size:1.1em;">
                            Fecha de Finalización <span style="color:#dc2626;">*</span>
                        </label>
                        <input type="date" id="fc-fecha" name="fecha" class="form-control" required style="font-family:'Poppins', sans-serif; font-size: 1.1em; padding: 12px 18px; height: auto; border-radius: 10px;">
                    </div>

                    {{-- Listado de Documentos del Servidor --}}
                    <div class="form-group" style="margin-bottom: 24px;">
                        <label style="font-weight: 700; color: #334155; display: block; margin-bottom: 8px; font-family:'Poppins', sans-serif;">Archivos de liberación en servidor a adjuntar:</label>
                        <div id="fc-server-files-container" style="background:#f8fafc; border:1px solid #e2e8f0; border-radius:12px; padding:15px; max-height:420px; overflow-y:auto; display:grid; grid-template-columns:repeat(auto-fill,minmax(200px, 1fr)); gap:8px; justify-items:center;">
                            <div class="alm-spinner" id="fc-server-spinner" style="border-top-color: #0284c7; display: block; margin: 10px auto; grid-column:1/-1;"></div>
                            <span style="text-align:center; color:#64748b; grid-column:1/-1;">Cargando archivos de la OT...</span>
                        </div>
                    </div>

                    <div class="form-actions" style="text-align: center; margin-top: 30px; margin-bottom: 10px;">
                        <button type="submit" class="btn-save-preorden" id="btn-submit-finalizar-calidad" style="font-size:1.15em; padding:14px 30px; border-radius:10px; font-family:'Poppins',sans-serif; font-weight: 700; height: auto;">
                            Finalizar y Enviar Correo
                        </button>
                    </div>
                </form>
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
                    <input type="hidden" id="env-tipo" name="tipo" value="modelo">

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

    {{-- ── MODAL: INICIAR CASTING / GESTION VEREDICTO (Almacén) ────── --}}
    @include('almacen.partials._modal_iniciar_casting')

    <script>
        window.almacenRoutes = {
            archivos: "{{ parse_url(route('almacen.fundicion.archivos'), PHP_URL_PATH) }}",
            serve: "{{ parse_url(route('almacen.fundicion.serve'), PHP_URL_PATH) }}",
            confirmarModelo: "{{ parse_url(route('almacen.fundicion.confirmarModelo'), PHP_URL_PATH) }}",
            getOtData: "{{ parse_url(route('almacen.fundicion.getOtData'), PHP_URL_PATH) }}",
            storePreOrden: "{{ parse_url(route('almacen.fundicion.storePreOrden'), PHP_URL_PATH) }}",
            sendEmailPreOrden: "{{ parse_url(route('almacen.fundicion.sendEmailPreOrden'), PHP_URL_PATH) }}",
            getLiberacion: "{{ parse_url(route('almacen.fundicion.getLiberacion'), PHP_URL_PATH) }}",
            submitLiberacion: "{{ parse_url(route('almacen.fundicion.submitLiberacion'), PHP_URL_PATH) }}",
            generateScar: "{{ parse_url(route('almacen.fundicion.generateScar'), PHP_URL_PATH) }}",
            getScar: "{{ parse_url(route('almacen.fundicion.getScar'), PHP_URL_PATH) }}",
            sendScarAlert: "{{ parse_url(route('almacen.fundicion.sendScarAlert'), PHP_URL_PATH) }}",
            enviarAlertaLiberacion: "{{ parse_url(route('almacen.fundicion.enviarAlertaLiberacion'), PHP_URL_PATH) }}",
            deleteFile: "{{ parse_url(route('almacen.fundicion.deleteFile'), PHP_URL_PATH) }}",
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
