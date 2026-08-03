@extends('layouts.appMenu')

@section('head')
    <title>{{ $pageTitle ?? 'Gestión de Documentación' }}</title>
    <link rel="icon" href="{{ url('images/lg_saavedra.png') }}?v=1">
    @vite([
        'resources/css/wo_views/manage_casting_visual_aids.css',
        'resources/js/wo_views/manage_casting_visual_aids.js'
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

        {{-- Layout en columnas (Dashboard) --}}
        <div class="dibujos-dashboard-layout">

            {{-- Columna Izquierda (Controles) --}}
            <div class="dibujos-dashboard-sidebar">
                {{-- Panel superior: Selectores + Subir PDF --}}
                <div class="dibujos-panel">

            {{-- Tarjeta izquierda --}}
            <div class="dibujos-card">
                <h2>Seleccionar / Crear Carpeta</h2>
                <p class="d-text-small d-text-muted d-mb-2">
                    {{ $moduleMetadata['description'] ?? 'Selecciona las opciones correspondientes.' }}
                    Si la carpeta no existe en el servidor, se creará antes de subir el primer PDF.
                    {{-- Selector de Clase --}}
                    <div class="dibujos-form-group">
                        <label for="clase-select">Clase</label>
                        <select id="clase-select" onchange="changeDocSelector('clase_id', this.value, ['proceso_id'])">
                            <option value="">— Seleccionar Clase —</option>
                            @foreach($clasesUnicas as $claseOpt)
                                <option value="{{ $claseOpt->id }}" {{ $claseSeleccionadaId == $claseOpt->id ? 'selected' : '' }}>
                                    {{ $claseOpt->nombre }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Proceso Oculto, ya sabemos que es Fundición --}}
                    <input type="hidden" id="proceso-select" value="Fundicion">

                {{-- Estado de la carpeta y botón crear --}}
                @php
                    $isReady = false;
                    $folderPathLabel = '';
                    $folderProps = [];
                    $alertContext = 'Selecciona las opciones necesarias para continuar.';
                    $carpetaExiste = false;

                    if ($moduleType === 'ayudas_fundicion' && $claseSeleccionadaId) {
                        $isReady = true;
                        $param1Name = 'Fundicion';

                        if ($claseActiva) {
                            $param2Name = $claseActiva->nombre;
                        } else {
                            $param2Name = $claseSeleccionadaId;
                        }

                        $folderPathLabel = "<span class='lvl-1'>" . htmlspecialchars($param2Name) . "</span>";
                        $carpetaExiste = isset($estructura[$param2Name]);
                        $folderProps = ['data-proceso' => $param1Name, 'data-clase' => $param2Name];
                    }
                @endphp

                <div id="admin-status-container">
                    <div id="alert-ready-exists" @class(['d-alert', 'd-alert-success', 'd-mt-2', 'hidden' => !($isReady && $carpetaExiste)])>
                        La carpeta <strong class="folder-label">{!! $folderPathLabel ?? '...' !!}</strong> ya existe en el
                        servidor.
                    </div>
                    <div id="alert-ready-not-exists" @class(['d-alert', 'd-alert-warning', 'd-mt-2', 'hidden' => !($isReady && !$carpetaExiste)])>
                        La carpeta <strong class="folder-label">{!! $folderPathLabel ?? '...' !!}</strong> aun <strong>no
                            existe</strong>. Creala antes de
                        subir PDFs.
                    </div>
                    <button @class(['btn-dibujos', 'd-mt-2', 'hidden' => !($isReady && !$carpetaExiste)]) id="btn-crear-carpeta" @if(isset($folderProps))
                        @foreach($folderProps as $k => $v) {{ $k }}="{{ $v }}" @endforeach @else data-ot="" data-clase=""
                        data-proceso="" @endif data-folder-param1="{{ $param1Name ?? '' }}"
                        data-folder-param2="{{ $param2Name ?? '' }}">
                        Crear Carpeta
                    </button>
                    <div id="alert-not-ready" @class(['d-alert', 'd-alert-info', 'd-mt-2', 'hidden' => $isReady])>
                        {{ $alertContext }}
                    </div>
                </div>
            </div>

            {{-- Tarjeta derecha: Subir PDF --}}
            <div class="dibujos-card">
                <h2>Subir PDF</h2>

                <div id="admin-upload-container">
                    <div id="upload-ready-content" class="hidden">
                        <p class="d-text-xs d-text-muted d-mb-2">
                            Carpeta destino: <strong class="folder-label d-text-bold text-primary">...</strong>
                        </p>

                        <div id="alert-upload-no-folder" class="d-alert d-alert-warning d-mb-3 d-alert d-alert-warning custom-alert-warning hidden">
                            <strong class="text-danger">ACCIÓN REQUERIDA:</strong> La carpeta de destino aun no existe.<br>
                            Para habilitar la subida de archivos, primero <strong class="text-danger">Crea la Carpeta</strong> utilizando el
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


            </div> {{-- Fin Columna Izquierda --}}

            {{-- Columna Derecha (Visualización) --}}
            <div class="dibujos-dashboard-main d-flex d-flex-column" style="width: 100%; gap: 2em;">

                <div class="panels-wrapper" style="display: {{ $isReady ? 'grid' : 'flex' }}; grid-template-columns: {{ $isReady ? '1fr 1fr' : '1fr' }}; gap: 2em; align-items: start; width: 100%;">
                    {{-- Panel de archivos de la carpeta seleccionada --}}
                    @if($isReady)
                        <div class="dibujos-files-panel active d-flex d-flex-column h-100" style="margin-bottom: 0;">
                            <div class="d-flex d-justify-between d-align-center mb-1-5 border-bottom pb-0-5">
                                <h2 class="m-0 pb-0 border-none">Archivos en: <span>{!! $folderPathLabel !!}</span></h2>
                                <div class="dibujos-files-breadcrumb m-0 bg-none p-0 border-none shrink-0">
                                    Carpeta activa: <strong>{!! $folderPathLabel !!}</strong>
                                </div>
                            </div>

                            <div id="archivos-grid" class="dibujos-files-grid flex-1 overflow-y-auto align-content-start" style="max-height: 50vh;">
                                <p class="d-text-subtle d-text-center d-w-100">Cargando archivos...</p>
                            </div>
                        </div>

                        {{-- Panel de Ayudas Visuales Manuales (Solo para aquellas que no son automáticas) --}}
                        {{-- Sección de Ayudas Manuales Eliminada por Requerimiento --}}
                    @endif

                    <div class="dibujos-table-section d-flex d-flex-column h-100" style="margin-bottom: 0;">
                        <div class="d-flex d-justify-between d-align-center gap-1 mb-1 flex-wrap">
                            <h2 class="m-0 p-0 border-none">Estructura Actual de Carpetas en el Servidor</h2>
                            <div class="position-relative min-w-240 max-w-360 flex-1">
                                <select id="filtro-tabla-estructura"
                                        class="custom-select">
                                    <option value="">— Mostrar Todos —</option>
                                </select>
                            </div>
                        </div>

            @if(count($estructura) === 0)
                <div class="dibujos-empty-state">
                    <p>No hay carpetas creadas aun.</p>
                </div>
            @else
                <div class="dibujos-table-container" style="max-height: 50vh; overflow-y: auto;">
                    <table class="dibujos-table" id="tabla-estructura">
                        <thead>
                            <tr>
                                <th class="d-text-center">Clase</th>
                                <th class="d-text-center">Archivos PDF</th>
                                <th class="d-text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                                @foreach($estructura as $claseName => $exists)
                                    @php
                                        $claseReal = $clasesUnicas->firstWhere('nombre', $claseName);
                                        $claseIdBD = $claseReal ? $claseReal->id : null;
                                        $badgeId = "badge-" . Str::slug($claseName);
                                    @endphp
                                    <tr data-proceso="Fundicion" data-clase="{{ $claseName }}">
                                        <td class="d-text-center d-text-primary"><strong>{{ $claseName }}</strong></td>
                                        <td class="d-text-center"><span class="badge-count" id="{{ $badgeId }}">...</span></td>
                                        <td class="d-text-center">
                                            <div class="td-actions">
                                                <button class="btn-action-icon btn-ver-archivos" title="Ver archivos"
                                                    onclick="irACarpeta('Fundicion', {{ \Illuminate\Support\Js::from($claseIdBD ?? $claseName) }}, false)">
                                                    <img src="{{ asset('images/documento.png') }}" alt="Ver">
                                                    <span>Ver PDF's</span>
                                                </button>
                                                <button class="btn-action-icon btn-eliminar-carpeta" title="Eliminar clase completa"
                                                    onclick="confirmarEliminarCarpeta('{{ $claseName }}', null, '{{ $claseName }}')">
                                                    <img src="{{ asset('images/Eliminar-Carpeta.png') }}" alt="Eliminar">
                                                    <span>Eliminar Carpeta</span>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
                    </div>
                </div>



                {{-- Log de auditoria --}}
                <div class="dibujos-table-section dibujos-table-section-clean">
                    <details class="dibujos-log-details">
                        <summary>Registro de Auditoría (últimas acciones)</summary>
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
                            <td colspan="5" class="d-text-center d-text-subtle p-1">Cargando registro...
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

            </div>
        </div>

    </div>


    {{-- Modal de Confirmación Estilo Premium (prod-viewer) --}}
    <div id="dibujos-confirm-modal" class="confirm-portal hidden">
        <div class="confirm-modal">
            <div class="confirm-modal-header d-justify-center">
                <h3>Confirmar Eliminación</h3>
            </div>
            <div class="confirm-modal-body">
                <div class="confirm-icon-wrapper">
                    <img id="confirm-modal-icon" src="{{ asset('images/Eliminar-Carpeta.png') }}" alt="Eliminar">
                </div>
                <p id="confirm-message-container" class="fs-1-1 lh-1-6 text-center">
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
        window.activeParam1 = 'Fundicion';
        window.activeParam2 = @json($claseActiva?->nombre ?? null);
    </script>
@endsection
