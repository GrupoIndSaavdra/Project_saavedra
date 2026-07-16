@extends('layouts.appMenu')

@section('head')
    <title>{{ $pageTitle ?? 'Gestión de Documentación' }}</title>
    <link rel="icon" href="{{ url('images/lg_saavedra.png') }}?v=1">
    @vite([
        'resources/css/wo_views/manage_ayudas_fundicion.css',
        'resources/js/wo_views/manage_ayudas_fundicion.js'
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

                    if ($moduleType === 'ayudas_fundicion' && $claseSeleccionadaId && $claseActiva) {
                        $isReady = true;
                        $param1Name = 'Fundicion';
                        $param2Name = $claseActiva->nombre;
                        $folderPathLabel = "<span class='lvl-1'>" . $claseActiva->nombre . "</span>";
                        $carpetaExiste = isset($estructura[$param2Name]);
                        $folderProps = ['data-proceso' => $param1Name, 'data-clase' => $param2Name];
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


            </div> {{-- Fin Columna Izquierda --}}

            {{-- Columna Derecha (Visualización) --}}
            <div class="dibujos-dashboard-main" style="display: grid; grid-template-columns: {{ $isReady ? 'minmax(0, 1fr) minmax(0, 1fr)' : '1fr' }}; min-height: calc(100vh - 180px); gap: 2em; align-items: stretch; align-content: start;">

                {{-- Panel de archivos de la carpeta seleccionada --}}
                @if($isReady)
                    <div class="dibujos-files-panel active" id="panel-archivos">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; border-bottom: 1px solid #e2e8f0; padding-bottom: 0.5rem;">
                            <h2 style="margin: 0; padding-bottom: 0; border: none;">Archivos en: <span>{!! $folderPathLabel !!}</span></h2>
                            <div class="dibujos-files-breadcrumb" style="margin: 0; background: none; padding: 0; border: none; flex-shrink: 0;">
                                Carpeta activa: <strong>{!! $folderPathLabel !!}</strong>
                            </div>
                        </div>

                        <div class="dibujos-files-grid" id="archivos-grid" style="max-height: none;">
                            <p class="d-text-subtle d-text-center d-w-100">Cargando archivos...</p>
                        </div>
                    </div>

                    {{-- Panel de Ayudas Visuales Manuales (Solo para aquellas que no son automáticas) --}}
                    {{-- Sección de Ayudas Manuales Eliminada por Requerimiento --}}
                @endif

                <div style="{{ $isReady ? 'position: relative; height: 100%;' : '' }}">
                    <div class="dibujos-table-section" style="{{ $isReady ? 'position: absolute; top: 0; left: 0; right: 0; bottom: 0;' : '' }} display: flex; flex-direction: column;">
                        <h2>Estructura Actual de Carpetas en el Servidor</h2>

            @if(count($estructura) === 0)
                <div class="dibujos-empty-state">
                    <p>No hay carpetas creadas aun.</p>
                </div>
            @else
                <div class="dibujos-table-container" style="flex: 1; max-height: none; overflow-y: auto;">
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
                @if($isReady)
                    </div>
                @else
                    </div>
                @endif



                {{-- Log de auditoria --}}
                <div class="dibujos-table-section" style="border: none; padding: 0; box-shadow: none; background: transparent; grid-column: 1 / -1;">
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
                            <td colspan="5" class="d-text-center d-text-subtle" style="padding:1em;">Cargando registro...
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
        window.activeParam1 = 'Fundicion';
        window.activeParam2 = @json($claseActiva?->nombre ?? null);
    </script>
@endsection
