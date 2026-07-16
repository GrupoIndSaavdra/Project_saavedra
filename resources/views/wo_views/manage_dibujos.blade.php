@extends('layouts.appMenu')

@section('head')
    <title>{{ $pageTitle ?? 'Gestión de Documentación' }}</title>
    <link rel="icon" href="{{ url('images/lg_saavedra.png') }}?v=1">
    @vite([
        'resources/css/wo_views/manage_dibujos.css',
        'resources/js/wo_views/manage_dibujos.js'
    ])
@endsection

@section('background-body', 'background-image:url("' . asset("images/fondoLogin.jpg") . '")')

@section('content')
    <div class="dibujos-wrapper" data-module="{{ $moduleType }}">

        {{-- Encabezado --}}
        <div class="dibujos-header">
            <h1>{{ $pageTitle ?? 'Gestión de Documentación' }}</h1>
            <span>Directorio: {{ $directoryName ?? 'GIS' }} &nbsp;|&nbsp; Sistema de Archivos</span>
        </div>

        {{-- Panel superior: Selectores + Subir PDF --}}
        <div class="dibujos-panel">

            {{-- Tarjeta izquierda --}}
            <div class="dibujos-card">
                <h2>Seleccionar / Crear Carpeta</h2>
                <p class="d-text-small d-text-muted d-mb-2">
                    {{ $moduleMetadata['description'] ?? 'Selecciona las opciones correspondientes.' }}
                    Si la carpeta no existe en el servidor, se creará antes de subir el primer PDF.
                </p>


                    {{-- Selector de OT --}}
                    <div class="dibujos-form-group">
                        <label for="ot-select">Orden de Trabajo (OT)</label>
                        <select id="ot-select" onchange="changeDocSelector('ot_id', this.value, ['clase_id'])">
                            <option value="">— Seleccionar OT —</option>
                            @foreach($todasLasOTs as $otOpt)
                                <option value="{{ $otOpt->id }}" {{ $otSeleccionadaId == $otOpt->id ? 'selected' : '' }}>
                                    OT {{ $otOpt->id }}{{ $otOpt->moldura ? ' — ' . $otOpt->moldura->nombre : '' }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Selector de Clase --}}
                    <div class="dibujos-form-group">
                        <label for="clase-select">Clase</label>
                        <select id="clase-select" onchange="changeDocSelector('clase_id', this.value)" {{ !$otSeleccionadaId ? 'disabled' : '' }}>
                            <option value="">— Seleccionar Clase —</option>
                            @if($otSeleccionadaId && $otActiva)
                                @foreach($otActiva->clases as $claseOpt)
                                    <option value="{{ $claseOpt->id }}" {{ $claseSeleccionadaId == $claseOpt->id ? 'selected' : '' }}>
                                        {{ $claseOpt->nombre }}
                                    </option>
                                @endforeach
                                <option value="--" {{ $claseSeleccionadaId === '--' ? 'selected' : '' }}>
                                    Archivos en Raíz (Antiguos)
                                </option>
                            @endif
                        </select>
                    </div>



                {{-- Estado de la carpeta y botón crear --}}
                @php
                    $isReady = false;
                    $folderPathLabel = '';
                    $folderProps = [];
                    $alertContext = 'Selecciona las opciones necesarias para continuar.';
                    $carpetaExiste = false;

                    if ($moduleType === 'dibujos' && $otSeleccionadaId && $claseSeleccionadaId && $otActiva && $claseActiva) {
                        $isReady = true;
                        $param1Name = (string) $otActiva->id;
                        $param2Name = $claseActiva->nombre;
                        $folderPathLabel = "<span class='lvl-1'>OT " . $otActiva->id . "</span> <span class='lvl-sep'>/</span> <span class='lvl-2'>" . $claseActiva->nombre . "</span>";
                        $carpetaExiste = isset($estructura[$param1Name]) && in_array($param2Name, $estructura[$param1Name]);
                        $folderProps = ['data-ot' => $param1Name, 'data-clase' => $param2Name];
                    }
                @endphp

                <div id="admin-status-container">
                    <div id="alert-ready-exists" class="d-alert d-alert-success d-mt-2"
                        style="display: {{ $isReady && $carpetaExiste ? 'block' : 'none' }};">
                        La carpeta <strong class="folder-label">{!! $folderPathLabel ?? '...' !!}</strong> ya existe en el
                        servidor.
                    </div>
                    <div id="alert-ready-not-exists" class="d-alert d-alert-warning d-mt-2"
                        style="display: {{ $isReady && !$carpetaExiste ? 'block' : 'none' }};">
                        La carpeta <strong class="folder-label">{!! $folderPathLabel ?? '...' !!}</strong> aun <strong>no
                            existe</strong>. Creala antes de
                        subir PDFs.
                    </div>
                    <button class="btn-dibujos d-mt-2" id="btn-crear-carpeta"
                        style="display: {{ $isReady && !$carpetaExiste ? 'block' : 'none' }};" @if(isset($folderProps))
                        @foreach($folderProps as $k => $v) {{ $k }}="{{ $v }}" @endforeach @else data-ot="" data-clase=""
                        data-proceso="" @endif data-folder-param1="{{ $param1Name ?? '' }}"
                        data-folder-param2="{{ $param2Name ?? '' }}">
                        Crear Carpeta
                    </button>
                    <div id="alert-not-ready" class="d-alert d-alert-info d-mt-2"
                        style="display: {{ $isReady ? 'none' : 'block' }};">
                        {{ $alertContext }}
                    </div>
                </div>
            </div>

            {{-- Tarjeta derecha: Subir PDF --}}
            <div class="dibujos-card">
                <h2>Subir PDF</h2>

                <div id="admin-upload-container">
                    <div id="upload-ready-content" style="display:none;">
                        <p class="d-text-xs d-text-muted d-mb-2">
                            Carpeta destino: <strong class="folder-label d-text-bold" style="color:#033966;">...</strong>
                        </p>

                        <div id="alert-upload-no-folder" class="d-alert d-alert-warning d-mb-3"
                            style="display:none; font-size: 0.95em; border-left: 4px solid #f59e0b; background-color: #fffbeb; color: #b45309; padding: 12px 15px; border-radius: 6px;">
                            <strong style="color:red;">ACCIÓN REQUERIDA:</strong> La carpeta de destino aun no existe.<br>
                            Para habilitar la subida de archivos, primero <strong style="color:red;">Crea la Carpeta</strong> utilizando el
                            botón correspondiente en el panel izquierdo.
                        </div>

                        <div class="dibujos-form-group">
                            <label class="dibujos-file-label" for="d-upload-file">
                                <span id="d-upload-file-label-text">Seleccionar archivo PDF</span>
                                <input type="file" id="d-upload-file" accept=".pdf" multiple>
                            </label>
                            <span class="dibujos-file-name" id="d-upload-file-name"></span>
                        </div>

                        <button class="btn-dibujos" id="btn-subir-pdf" data-ot="" data-clase="" data-proceso="" disabled>
                            Subir PDF
                        </button>
                    </div>

                    <div id="upload-not-ready-content" class="d-card-placeholder">
                        <p class="d-text-subtle">Completa la selección en el panel izquierdo para habilitar la subida.</p>
                    </div>
                </div>
            </div>
        </div>


        {{-- Panel de archivos de la carpeta seleccionada --}}
        @if($isReady)
            <div class="dibujos-files-panel active" id="panel-archivos">
                <h2>Archivos en: <span>{!! $folderPathLabel !!}</span></h2>

                <div class="dibujos-files-breadcrumb">
                    Carpeta activa: <strong>{!! $folderPathLabel !!}</strong>
                </div>

                <div class="dibujos-files-grid" id="archivos-grid">
                    <p class="d-text-subtle d-text-center d-w-100">Cargando archivos...</p>
                </div>
            </div>

            {{-- Panel de Ayudas Visuales Manuales (Solo para aquellas que no son automáticas) --}}
            {{-- Sección de Ayudas Manuales Eliminada por Requerimiento --}}
        @endif

        {{-- Tabla global de estructura --}}
        <div class="dibujos-table-section">
            <h2>Estructura Actual de Carpetas en el Servidor</h2>

            @if(count($estructura) === 0)
                <div class="dibujos-empty-state">
                    <p>No hay carpetas creadas aun.</p>
                </div>
            @else
                <div class="dibujos-table-container">
                    <table class="dibujos-table" id="tabla-estructura">
                        <thead>
                            <tr>
                                    <th class="d-text-center">Orden de Trabajo</th>
                                    <th class="d-text-center">Clase</th>
                                <th class="d-text-center">Archivos PDF</th>
                                <th class="d-text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if($moduleType === 'dibujos')
                                @foreach($estructura as $otName => $clases)
                                    @php
                                        // $otName format: "OT 6695 - TALL BOY..."
                                        preg_match('/OT\s*(\d+)/', $otName, $matches);
                                        $otIdNumber = isset($matches[1]) ? (int) $matches[1] : 0;
                                        $otReal = $otIdNumber > 0 ? $todasLasOTs->firstWhere('id', $otIdNumber) : null;
                                        $otLabel = $otReal ? ("OT " . $otReal->id . ($otReal->moldura ? " — " . $otReal->moldura->nombre : "")) : $otName;
                                        $otIdBD = $otReal ? $otReal->id : null;
                                    @endphp
                                    @if(count($clases) === 0)
                                        <tr data-ot="{{ $otName }}" data-clase="">
                                            <td class="d-text-center d-text-primary"><strong>{{ $otLabel }}</strong></td>
                                            <td class="d-text-center"><em class="d-text-danger d-text-bold">Sin clases</em></td>
                                            <td class="d-text-center"><span class="badge-count"
                                                    id="badge-{{ Str::slug($otName) }}-raiz">...</span></td>
                                            <td class="d-text-center">
                                                <div class="td-actions">
                                                    <button class="btn-action-icon btn-eliminar-carpeta" title="Eliminar OT completa"
                                                        onclick="confirmarEliminarCarpeta('{{ $otName }}', null, '{{ $otLabel }}')">
                                                        <img src="{{ asset('images/Eliminar-Carpeta.png') }}" alt="Eliminar">
                                                        <span>Eliminar Directorio Raíz</span>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    @else
                                        @foreach($clases as $claseName)
                                            @php
                                                $isRoot = $claseName === null || $claseName === '--';
                                                $paramClase = $isRoot ? '--' : $claseName;
                                                $displayClase = $isRoot ? 'Archivos en Raíz' : $claseName;
                                                $claseReal = (!$isRoot && $otReal) ? $otReal->clases->firstWhere('nombre', $claseName) : null;
                                                $claseIdBD = $claseReal ? $claseReal->id : $paramClase;
                                                $badgeId = "badge-" . Str::slug($otName) . "-" . Str::slug($paramClase);
                                            @endphp
                                            <tr data-ot="{{ $otName }}" data-clase="{{ $paramClase }}">
                                                <td class="d-text-center d-text-primary"><strong>{{ $otLabel }}</strong></td>
                                                <td class="d-text-center {{ $isRoot ? 'd-text-warning' : 'd-text-success' }} d-text-bold">{{ $displayClase }}</td>
                                                <td class="d-text-center"><span class="badge-count" id="{{ $badgeId }}">...</span></td>
                                                <td class="d-text-center">
                                                    <div class="td-actions">
                                                        <button class="btn-action-icon btn-ver-archivos" title="Ver archivos"
                                                            onclick="irACarpeta('{{ $otIdBD ?? $otName }}', '{{ $claseIdBD }}', {{ $otIdBD ? 'true' : 'false' }})">
                                                            <img src="{{ asset('images/documento.png') }}" alt="Ver">
                                                            <span>Ver PDF's</span>
                                                        </button>
                                                        <button class="btn-action-icon btn-eliminar-carpeta" title="Eliminar carpeta"
                                                            onclick="confirmarEliminarCarpeta('{{ $otName }}', '{{ $isRoot ? null : $claseName }}', '{{ $otLabel }}{{ $isRoot ? '' : ' / ' . $claseName }}')">
                                                            <img src="{{ asset('images/Eliminar-Carpeta.png') }}" alt="Eliminar">
                                                            <span>Eliminar Clase</span>
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    @endif
                                @endforeach
                            @endif
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        {{-- Log de auditoria --}}
        <div class="dibujos-table-section">
            <h2>Registro de Auditoría (últimas acciones)</h2>
            <div class="dibujos-table-container d-log-scroll">
                <table class="dibujos-log-table">
                    <thead>
                        <tr>
                            <th>Fecha</th>
                            <th>Usuario</th>
                            <th>Acción</th>
                            <th>Ruta</th>
                            <th>Archivo</th>
                        </tr>
                    </thead>
                    <tbody id="tbody-log">
                        <tr>
                            <td colspan="5" class="d-text-center d-text-subtle" style="padding:1em;">Cargando registro...
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

    </div>


    {{-- Modal de Confirmación Estilo Premium (prod-viewer) --}}
    <div id="dibujos-confirm-modal" class="confirm-portal" style="display: none;">
        <div class="confirm-modal">
            <div class="confirm-modal-header" style="justify-content: center;">
                <h3>Confirmar Eliminación</h3>
            </div>
            <div class="confirm-modal-body">
                <div class="confirm-icon-wrapper">
                    <img id="confirm-modal-icon" src="{{ asset('images/Eliminar-Carpeta.png') }}" alt="Eliminar">
                </div>
                <p id="confirm-message-container" style="font-size: 1.1em; line-height: 1.6; text-align: center;">
                </p>
                <div class="confirm-modal-actions">
                    <button class="btn-confirm-cancel" onclick="cerrarConfirmarEliminar()">Cancelar</button>
                    <button id="btn-confirmar-borrar" class="btn-confirm-danger">Eliminar Permanentemente</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        window.baseUrl = "{{ url('/') }}";
        window.cerrarImgUrl = "{{ asset('images/cerrar.png') }}";
        window.moduleType = "{{ $moduleType }}";
        window.routesPrefix = "{{ $modulePrefix }}";

        window.routes = {
            ...(window.routes || {}),
            'doc.estructura': "{{ route($modulePrefix . '.estructura') }}",
            'doc.archivos': "{{ route($modulePrefix . '.archivos') }}",
            'doc.serve': "{{ route($modulePrefix . '.serve') }}",
            'doc.createFolder': "{{ route($modulePrefix . '.createFolder') }}",
            'doc.upload': "{{ route($modulePrefix . '.upload') }}",
            'doc.delete': "{{ route($modulePrefix . '.delete') }}",
            'doc.replace': "{{ route($modulePrefix . '.replace') }}",
            'doc.log': "{{ url('/') }}/{{ $modulePrefix }}/log",
            'doc.deleteFolder': "{{ route($modulePrefix . '.deleteFolder') }}",
            'doc.deleteParent': "{{ route($modulePrefix . '.deleteParent') }}",
        };
        window.csrfToken = "{{ csrf_token() }}";
        window.estructura = @json($estructura);

        // Exportar active selection para cargar panel inicialmente
        @if($moduleType === 'dibujos')
            window.activeParam1 = @json($otActiva?->id ?? null);
            window.activeParam2 = @json($claseActiva?->nombre ?? null);
        @elseif($moduleType === 'manuales')
            window.activeParam1 = @json($procesoActivo?->nombre ?? null);
            window.activeParam2 = null;
        @elseif($moduleType === 'fundicion')
            window.activeParam1 = @json($otActiva?->id ?? null);
            window.activeParam2 = @json($claseActiva?->nombre ?? null);
        @elseif($moduleType === 'ayudas')
            window.activeParam1 = @json($procesoActivo?->nombre ?? null);
            window.activeParam2 = @json($claseActiva?->nombre ?? null);
        @elseif($moduleType === 'ayudas_fundicion')
            window.activeParam1 = 'Fundicion';
            window.activeParam2 = @json($claseActiva?->nombre ?? null);
        @endif
    </script>
@endsection
