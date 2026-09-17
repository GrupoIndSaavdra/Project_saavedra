@extends('layouts.appMenu')

@section('head')
    <title>Programas CNC — Gestión</title>
    <link rel="icon" href="{{ url('images/lg_saavedra.png') }}?v=1">
    @vite([
        'resources/css/wo_views/manage_programas.css',
        'resources/js/wo_views/manage_programas.js'
    ])
@endsection

@section('background-body', 'background-image:url("' . asset("images/fondoLogin.jpg") . '")')

@section('content')
    <div class="dibujos-wrapper" data-module="programas">

        {{-- Encabezado --}}
        <div class="dibujos-header">
            <h1>Programas CNC — Gestión de Archivos</h1>
            <span>Directorio: DOCUMENTACION_GIS / PROGRAMAS_MAQUINADOS &nbsp;|&nbsp; Sistema de Archivos</span>
        </div>

        {{-- Layout en columnas (Dashboard) --}}
        <div class="dibujos-dashboard-layout">

            {{-- Columna Izquierda (Controles) --}}
            <div class="dibujos-dashboard-sidebar">
                <div class="dibujos-panel">

                    {{-- Tarjeta de Selectores --}}
                    <div class="dibujos-card">
                        <h2>Seleccionar / Crear Carpeta</h2>
                        <p class="d-text-small d-text-muted d-mb-2">
                            Selecciona la OT, Clase y Proceso. Si la carpeta no existe se creará antes de subir el primer programa.
                        </p>

                        {{-- Selector de OT --}}
                        {{-- Selector de OT --}}
                        <div class="dibujos-form-group">
                            <label for="ot-select">Orden de Trabajo (OT)</label>
                            <select id="ot-select" onchange="changeProgSelector('ot_id', this.value, ['clase_id', 'proceso', 'proceso_id'])">
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
                            <select id="clase-select" onchange="changeProgSelector('clase_id', this.value, ['proceso', 'proceso_id'])" {{ !$otSeleccionadaId ? 'disabled' : '' }}>
                                <option value="">— Seleccionar Clase —</option>
                                @if($otSeleccionadaId && $otActiva)
                                    @foreach($otActiva->clases as $claseOpt)
                                        <option value="{{ $claseOpt->id }}" {{ $claseSeleccionadaId == $claseOpt->id ? 'selected' : '' }}>
                                            {{ $claseOpt->nombre }}
                                        </option>
                                    @endforeach
                                @endif
                            </select>
                        </div>

                        {{-- Selector de Proceso (dinámico desde la BD) --}}
                        <div class="dibujos-form-group">
                            <label for="proceso-select">Proceso</label>
                            <select id="proceso-select" onchange="changeProgSelector('proceso', this.value)" {{ !$claseSeleccionadaId ? 'disabled' : '' }}>
                                <option value="">— Seleccionar Proceso —</option>
                                @foreach($procesosActivos as $proc)
                                    <option value="{{ $proc }}" {{ $procesoSeleccionado === $proc ? 'selected' : '' }}>
                                        {{ $proc }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        @php
                            $isReady      = !empty($otSeleccionadaId) && !empty($claseSeleccionadaId) && !empty($procesoSeleccionado);
                            $param1Name   = $otActiva    ? (string) $otActiva->id : (string)($otSeleccionadaId ?? '');
                            $param2Name   = $claseActiva ? $claseActiva->nombre   : (string)($claseSeleccionadaId ?? '');
                            $param3Name   = $procesoSeleccionado ?? '';
                            $folderLabel  = '';
                            $carpetaExiste = false;

                            if ($isReady) {
                                $folderLabel = "<span class='lvl-1'>OT {$param1Name}</span> <span class='lvl-sep'>/</span> <span class='lvl-2'>{$param2Name}</span> <span class='lvl-sep'>/</span> <span class='lvl-3'>{$param3Name}</span>";
                                $otNorm = mb_strtoupper("OT {$param1Name}" . ($otActiva?->moldura ? " - {$otActiva->moldura->nombre}" : ''), 'UTF-8');
                                
                                if (!empty($estructura)) {
                                    $sanitizeBlade = fn($str) => $str ? trim(preg_replace('/[\/\\\\]/', '', preg_replace('/\.\.+/', '', (string)$str))) : '';
                                    foreach ($estructura as $otK => $clasesK) {
                                        if (mb_strtolower($sanitizeBlade($otK), 'UTF-8') === mb_strtolower($sanitizeBlade($otNorm), 'UTF-8')) {
                                            foreach ($clasesK as $claseK => $procesosK) {
                                                if (mb_strtolower($sanitizeBlade($claseK), 'UTF-8') === mb_strtolower($sanitizeBlade($param2Name), 'UTF-8')) {
                                                    foreach ($procesosK as $procK) {
                                                        if (mb_strtolower($sanitizeBlade($procK), 'UTF-8') === mb_strtolower($sanitizeBlade($param3Name), 'UTF-8')) {
                                                            $carpetaExiste = true;
                                                            break 3;
                                                        }
                                                    }
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        @endphp

                        <div id="admin-status-container">
                            <div id="alert-ready-exists" @class(['d-alert', 'd-alert-success', 'd-mt-2', 'hidden' => !($isReady && $carpetaExiste)])>
                                La carpeta <strong class="folder-label">{!! $folderLabel !!}</strong> ya existe en el servidor.
                            </div>
                            <div id="alert-ready-not-exists" @class(['d-alert', 'd-alert-warning', 'd-mt-2', 'hidden' => !($isReady && !$carpetaExiste)])>
                                La carpeta <strong class="folder-label">{!! $folderLabel !!}</strong> aun <strong>no existe</strong>. Créala antes de subir programas.
                            </div>
                            <button @class(['btn-dibujos', 'd-mt-2', 'hidden' => !($isReady && !$carpetaExiste)])
                                id="btn-crear-carpeta"
                                data-ot="{{ $otSeleccionadaId ?? '' }}"
                                data-clase="{{ $claseSeleccionadaId ?? '' }}"
                                data-proceso="{{ $procesoSeleccionado ?? '' }}">
                                Crear Carpeta
                            </button>
                            <div id="alert-not-ready" @class(['d-alert', 'd-alert-info', 'd-mt-2', 'hidden' => $isReady])>
                                Selecciona OT, Clase y Proceso para continuar.
                            </div>
                        </div>
                    </div>

                    {{-- Tarjeta Subir Programa --}}
                    <div class="dibujos-card">
                        <h2>Subir Programa (.NC / .CNC)</h2>
                        <div id="admin-upload-container">
                            <div id="upload-ready-content" {{ $isReady ? '' : 'hidden' }}>
                                <p class="d-text-xs d-text-muted d-mb-2">
                                    Carpeta destino: <strong class="folder-label d-text-bold text-primary">{!! $folderLabel !!}</strong>
                                </p>

                                <div id="alert-upload-no-folder" class="d-alert d-alert-warning d-mb-3" {{ $carpetaExiste ? 'hidden' : '' }}>
                                    <strong class="text-danger">ACCIÓN REQUERIDA:</strong> La carpeta de destino aún no existe.<br>
                                    Primero <strong class="text-danger">Crea la Carpeta</strong> en el panel superior.
                                </div>

                                <div class="dibujos-form-group">
                                    <label class="dibujos-file-label" for="d-upload-file">
                                        <span id="d-upload-file-label-text">Seleccionar archivo .NC / .CNC</span>
                                        <input type="file" id="d-upload-file" accept=".nc,.cnc,.NC,.CNC" multiple>
                                    </label>
                                    <span class="dibujos-file-name" id="d-upload-file-name"></span>
                                </div>

                                <button class="btn-dibujos" id="btn-subir-programa"
                                    data-ot="{{ $otSeleccionadaId ?? '' }}"
                                    data-clase="{{ $claseSeleccionadaId ?? '' }}"
                                    data-proceso="{{ $procesoSeleccionado ?? '' }}"
                                    disabled>
                                    Subir Programa
                                </button>
                            </div>

                            <div id="upload-not-ready-content" class="d-card-placeholder" {{ $isReady ? 'hidden' : '' }}>
                                <p class="d-text-subtle">Completa la selección para habilitar la subida.</p>
                            </div>
                        </div>
                    </div>

                </div>
            </div>

            {{-- Columna Derecha (Visualización) --}}
            <div class="dibujos-dashboard-main d-flex d-flex-column" style="width: 100%; gap: 2em;">

                <div class="panels-wrapper" style="display: {{ $isReady ? 'grid' : 'flex' }}; grid-template-columns: {{ $isReady ? '1fr 1fr' : '1fr' }}; gap: 2em; align-items: start; width: 100%;">

                    {{-- Panel de archivos de la carpeta seleccionada --}}
                    @if($isReady)
                        <div class="dibujos-files-panel active d-flex d-flex-column h-100" style="margin-bottom: 0;">
                            <div class="d-flex d-justify-between d-align-center mb-1-5 border-bottom pb-0-5">
                                <h2 class="m-0 pb-0 border-none">Archivos en: <span>{!! $folderLabel !!}</span></h2>
                                <div class="dibujos-files-breadcrumb m-0 bg-none p-0 border-none shrink-0">
                                    Carpeta activa: <strong>{!! $folderLabel !!}</strong>
                                </div>
                            </div>
                            <div id="archivos-grid" class="dibujos-files-grid flex-1 overflow-y-auto align-content-start" style="max-height: 50vh;">
                                <p class="d-text-subtle d-text-center d-w-100">Cargando archivos...</p>
                            </div>
                        </div>
                    @endif

                    {{-- Tabla de estructura --}}
                    <div class="dibujos-table-section d-flex d-flex-column h-100" style="margin-bottom: 0;">
                        <div class="d-flex d-justify-between d-align-center gap-1 mb-1 flex-wrap">
                            <h2 class="m-0 p-0 border-none">Estructura Actual de Carpetas en el Servidor</h2>
                            <div class="position-relative min-w-240 max-w-360 flex-1">
                                <select id="filtro-tabla-estructura" class="custom-select">
                                    <option value="">— Mostrar Todos —</option>
                                </select>
                            </div>
                        </div>

                        @if(count($estructura) === 0)
                            <div class="dibujos-empty-state">
                                <img src="{{ asset('images/Sin_Carpetas.png') }}" alt="Sin carpetas" class="empty-folder-icon">
                                <p>No hay carpetas creadas aún.</p>
                            </div>
                        @else
                            <div class="dibujos-table-container" style="max-height: 50vh; overflow-y: auto;">
                                <table class="dibujos-table" id="tabla-estructura">
                                    <thead>
                                        <tr>
                                            <th class="d-text-center">Orden de Trabajo</th>
                                            <th class="d-text-center">Clase</th>
                                            <th class="d-text-center">Proceso</th>
                                            <th class="d-text-center">Archivos CNC</th>
                                            <th class="d-text-center">Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($estructura as $otName => $clases)
                                            @php
                                                preg_match('/OT\s*(\d+)/', $otName, $matches);
                                                $otIdNumber = isset($matches[1]) ? (int) $matches[1] : 0;
                                                $otReal = $otIdNumber > 0 ? $todasLasOTs->firstWhere('id', $otIdNumber) : null;
                                                $otLabel = $otReal ? ('OT ' . $otReal->id . ($otReal->moldura ? ' — ' . $otReal->moldura->nombre : '')) : $otName;
                                                $otIdBD = $otReal ? $otReal->id : null;
                                            @endphp
                                            @foreach($clases as $claseName => $procesos)
                                                @if(count($procesos) === 0)
                                                    <tr data-ot="{{ $otName }}" data-clase="{{ $claseName }}" data-proceso="">
                                                        <td class="d-text-center d-text-primary"><strong>{{ $otLabel }}</strong></td>
                                                        <td class="d-text-center"><span class="badge-ayuda-tag alerta-enviada-tag transform-none-important">{{ $claseName }}</span></td>
                                                        <td class="d-text-center"><span class="badge-ayuda-tag alerta-sin-clases-tag pointer-events-none">Sin procesos</span></td>
                                                        <td class="d-text-center"><span class="badge-count" id="badge-{{ Str::slug($otName) }}-{{ Str::slug($claseName) }}-raiz">...</span></td>
                                                        <td class="d-text-center">
                                                            <div class="td-actions">
                                                                <button class="btn-action-icon btn-eliminar-carpeta" title="Eliminar carpeta de clase"
                                                                    onclick="confirmarEliminarCarpeta('{{ $otName }}', '{{ $claseName }}', null, '{{ $otLabel }} / {{ $claseName }}')">
                                                                    <img src="{{ asset('images/Eliminar-Carpeta.png') }}" alt="Eliminar">
                                                                    <span>Eliminar Clase</span>
                                                                </button>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                @else
                                                    @foreach($procesos as $procesoName)
                                                        @php
                                                            $badgeId = 'badge-' . Str::slug($otName) . '-' . Str::slug($claseName) . '-' . Str::slug($procesoName);
                                                            $claseReal = $otReal ? $otReal->clases->firstWhere('nombre', $claseName) : null;
                                                            $claseIdBD = $claseReal ? $claseReal->id : null;
                                                        @endphp
                                                        <tr data-ot="{{ $otName }}" data-clase="{{ $claseName }}" data-proceso="{{ $procesoName }}">
                                                            <td class="d-text-center d-text-primary"><strong>{{ $otLabel }}</strong></td>
                                                            <td class="d-text-center"><span class="badge-ayuda-tag alerta-enviada-tag transform-none-important">{{ $claseName }}</span></td>
                                                            <td class="d-text-center"><span class="badge-proceso-tag">{{ $procesoName }}</span></td>
                                                            <td class="d-text-center"><span class="badge-count" id="{{ $badgeId }}">...</span></td>
                                                            <td class="d-text-center">
                                                                <div class="td-actions">
                                                                    <button class="btn-action-icon btn-ver-archivos" title="Ver archivos"
                                                                        onclick="irACarpeta({{ \Illuminate\Support\Js::from($otIdBD ?? $otName) }}, {{ \Illuminate\Support\Js::from($claseIdBD ?? $claseName) }}, {{ \Illuminate\Support\Js::from($procesoName) }}, {{ $otIdBD ? 'true' : 'false' }})">
                                                                        <img src="{{ asset('images/documento.png') }}" alt="Ver">
                                                                        <span>Ver Programas</span>
                                                                    </button>
                                                                    <button class="btn-action-icon btn-eliminar-carpeta" title="Eliminar carpeta de proceso"
                                                                        onclick="confirmarEliminarCarpeta('{{ $otName }}', '{{ $claseName }}', '{{ $procesoName }}', '{{ $otLabel }} / {{ $claseName }} / {{ $procesoName }}')">
                                                                        <img src="{{ asset('images/Eliminar-Carpeta.png') }}" alt="Eliminar">
                                                                        <span>Eliminar Proceso</span>
                                                                    </button>
                                                                </div>
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                @endif
                                            @endforeach
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
                                        <td colspan="5" class="d-text-center d-text-subtle p-1">Cargando registro...</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </details>
                </div>

            </div>
        </div>

    </div>

    {{-- Modal de Confirmación --}}
    <div id="dibujos-confirm-modal" class="confirm-portal" hidden>
        <div class="confirm-modal">
            <div class="confirm-modal-header d-justify-center">
                <h3>Confirmar Eliminación</h3>
            </div>
            <div class="confirm-modal-body">
                <div class="confirm-icon-wrapper">
                    <img id="confirm-modal-icon" src="{{ asset('images/Eliminar-Carpeta.png') }}" alt="Eliminar">
                </div>
                <p id="confirm-message-container" class="fs-1-1 lh-1-6 text-center"></p>
                <div class="confirm-modal-actions">
                    <button class="btn-confirm-cancel" onclick="cerrarConfirmarEliminar()">Cancelar</button>
                    <button id="btn-confirmar-borrar" class="btn-confirm-danger">Eliminar Permanentemente</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        window.baseUrl        = "{{ url('/') }}";
        window.cerrarImgUrl   = "{{ asset('images/cerrar.png') }}";
        window.imgProgramsCNC       = "{{ asset('images/ProgramsCNC.png') }}";
        window.imgProgramsCNCShadow = "{{ asset('images/ProgramsCNC-Shadow.png') }}";
        window.imgProgramsNC        = "{{ asset('images/ProgramsNC.png') }}";
        window.imgProgramsNCShadow  = "{{ asset('images/ProgramsNC-Shadow.png') }}";
        window.moduleType     = "programas";
        window.routesPrefix   = "programas";

        window.routes = {
            ...(window.routes || {}),
            'doc.estructura':    "{{ route('programas.estructura') }}",
            'doc.archivos':      "{{ route('programas.archivos') }}",
            'doc.serve':         "{{ route('programas.serve') }}",
            'doc.createFolder':  "{{ route('programas.createFolder') }}",
            'doc.upload':        "{{ route('programas.upload') }}",
            'doc.delete':        "{{ route('programas.delete') }}",
            'doc.deleteFolder':  "{{ route('programas.deleteFolder') }}",
            'doc.deleteParent':  "{{ route('programas.deleteParent') }}",
            'doc.log':           "{{ url('/') }}/programas/log",
            'programas.procesos_clase': "{{ route('programas.procesos_clase') }}",
        };
        window.csrfToken  = "{{ csrf_token() }}";
        window.estructura = @json($estructura);
        window.todasLasOTs = {!! json_encode($todasLasOTs->map(fn($o) => [
            'id'            => $o->id,
            'moldura_nombre'=> $o->moldura?->nombre,
            'clases'        => $o->clases->map(fn($c) => ['id' => $c->id, 'nombre' => $c->nombre])->values()
        ])) !!};

        window.activeParam1 = @json($otActiva?->id ?? null);
        window.activeParam2 = @json($claseActiva?->nombre ?? null);
        window.activeParam3 = @json($procesoSeleccionado ?? null);
    </script>
@endsection
