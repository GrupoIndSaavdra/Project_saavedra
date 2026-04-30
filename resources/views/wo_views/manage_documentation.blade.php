@extends('layouts.appMenu')

@section('head')
    <title>{{ $pageTitle ?? 'Gestión de Documentación' }}</title>
    <link rel="icon" href="{{ url('images/lg_saavedra.png') }}?v=1">
    @vite([
        'resources/css/wo_views/manage_documentation.css',
        'resources/js/wo_views/manage_documentation.js'
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

                @if($moduleType === 'dibujos')
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
                            @endif
                        </select>
                    </div>

                @elseif($moduleType === 'fundicion')
                    {{-- Selector de OT (sin clase) --}}
                    <div class="dibujos-form-group">
                        <label for="ot-select">Orden de Trabajo (OT)</label>
                        <select id="ot-select" onchange="changeDocSelector('ot_id', this.value)">
                            <option value="">— Seleccionar OT —</option>
                            @foreach($todasLasOTs as $otOpt)
                                <option value="{{ $otOpt->id }}" {{ $otSeleccionadaId == $otOpt->id ? 'selected' : '' }}>
                                    OT {{ $otOpt->id }}{{ $otOpt->moldura ? ' — ' . $otOpt->moldura->nombre : '' }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                @elseif($moduleType === 'manuales')
                    {{-- Selector de Proceso --}}
                    <div class="dibujos-form-group">
                        <label for="proceso-select">Proceso</label>
                        <select id="proceso-select" onchange="changeDocSelector('proceso_id', this.value)">
                            <option value="">— Seleccionar Proceso —</option>
                            @foreach($todosLosProcesos as $procesoOpt)
                                <option value="{{ $procesoOpt->id }}" {{ $procesoSeleccionadoId == $procesoOpt->id ? 'selected' : '' }}>
                                    {{ $procesoOpt->nombre }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                @elseif($moduleType === 'ayudas')
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

                    {{-- Selector de Proceso --}}
                    <div class="dibujos-form-group">
                        <label for="proceso-select">Proceso</label>
                        <select id="proceso-select" onchange="changeDocSelector('proceso_id', this.value)" {{ !$claseSeleccionadaId ? 'disabled' : '' }}>
                            <option value="">— Seleccionar Proceso —</option>
                            @if($claseSeleccionadaId)
                                @foreach($todosLosProcesos as $procesoOpt)
                                    <option value="{{ $procesoOpt->id }}" {{ $procesoSeleccionadoId == $procesoOpt->id ? 'selected' : '' }}>
                                        {{ $procesoOpt->nombre }}
                                    </option>
                                @endforeach
                            @endif
                        </select>
                    </div>
                @elseif($moduleType === 'ayudas_fundicion')
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
                @endif

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
                        $folderPathLabel = "OT " . $otActiva->id . ($otActiva->moldura ? " — " . $otActiva->moldura->nombre : "") . " / " . $claseActiva->nombre;
                        $carpetaExiste = isset($estructura[$param1Name]) && in_array($param2Name, $estructura[$param1Name]);
                        $folderProps = ['data-ot' => $param1Name, 'data-clase' => $param2Name];
                    } elseif ($moduleType === 'manuales' && $procesoSeleccionadoId && $procesoActivo) {
                        $isReady = true;
                        $param1Name = $procesoActivo->nombre;
                        $folderPathLabel = $procesoActivo->nombre;
                        $carpetaExiste = in_array($param1Name, $estructura);
                        $folderProps = ['data-proceso' => $param1Name];
                    } elseif ($moduleType === 'ayudas' && $procesoSeleccionadoId && $claseSeleccionadaId && $procesoActivo && $claseActiva) {
                        $isReady = true;
                        $param1Name = $procesoActivo->nombre;
                        $param2Name = $claseActiva->nombre;
                        $folderPathLabel = $procesoActivo->nombre . " / " . $claseActiva->nombre;
                        $carpetaExiste = isset($estructura[$param2Name]) && in_array($param1Name, $estructura[$param2Name]);
                        $folderProps = ['data-proceso' => $param1Name, 'data-clase' => $param2Name];
                    } elseif ($moduleType === 'ayudas_fundicion' && $claseSeleccionadaId && $claseActiva) {
                        $isReady = true;
                        $param1Name = 'Fundicion';
                        $param2Name = $claseActiva->nombre;
                        $folderPathLabel = "Fundicion / " . $claseActiva->nombre;
                        $carpetaExiste = isset($estructura[$param2Name]) && in_array($param1Name, $estructura[$param2Name]);
                        $folderProps = ['data-proceso' => $param1Name, 'data-clase' => $param2Name];
                    } elseif ($moduleType === 'fundicion' && $otSeleccionadaId && $otActiva) {
                        $isReady = true;
                        $param1Name = (string) $otActiva->id;
                        $folderPathLabel = "OT " . $otActiva->id . ($otActiva->moldura ? " — " . $otActiva->moldura->nombre : "");

                        // En fundicion, la estructura es lineal (solo OTs en raiz)
                        // Para armar el nombre correcto como devuelve buildStructure
                        $expectedFolderName = "OT " . $otActiva->id . ($otActiva->moldura ? " - " . $otActiva->moldura->nombre : "");
                        // sanear
                        $expectedFolderName = trim(preg_replace('/[\/\\\\]/', '', preg_replace('/\.\.+/', '', $expectedFolderName)));
                        $carpetaExiste = in_array($expectedFolderName, $estructura);

                        $folderProps = ['data-ot' => $param1Name];
                    }
                @endphp

                <div id="admin-status-container">
                    <div id="alert-ready-exists" class="d-alert d-alert-success d-mt-2" style="display:none;">
                        La carpeta <strong class="folder-label">...</strong> ya existe en el servidor.
                    </div>
                    <div id="alert-ready-not-exists" class="d-alert d-alert-warning d-mt-2" style="display:none;">
                        La carpeta <strong class="folder-label">...</strong> aun <strong>no existe</strong>. Creala antes de
                        subir PDFs.
                    </div>
                    <button class="btn-dibujos d-mt-2" id="btn-crear-carpeta" style="display:none;" data-ot="" data-clase=""
                        data-proceso="">
                        Crear Carpeta
                    </button>
                    <div id="alert-not-ready" class="d-alert d-alert-info d-mt-2">
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

                        <div id="alert-upload-no-folder" class="d-text-xs d-text-danger d-mb-2" style="display:none;">
                            La carpeta no existe. Primero creala en el panel de la izquierda.
                        </div>

                        <div class="dibujos-form-group">
                            <label class="dibujos-file-label" for="d-upload-file">
                                <span id="d-upload-file-label-text">Seleccionar archivo PDF</span>
                                <input type="file" id="d-upload-file" accept=".pdf">
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
                <h2>Archivos en: <span>{{ $folderPathLabel }}</span></h2>

                <div class="dibujos-files-breadcrumb">
                    Carpeta activa: <strong>{{ $folderPathLabel }}</strong>
                </div>

                <div class="dibujos-files-grid" id="archivos-grid">
                    <p class="d-text-subtle d-text-center d-w-100">Cargando archivos...</p>
                </div>
            </div>

            {{-- Panel de Ayudas Visuales (Solo para Fundición) --}}
            @if($moduleType === 'fundicion')
                @php
                    $showAyudas = ($hasDibujos ?? false);
                @endphp
                <div class="dibujos-table-section d-mt-3" style="margin-bottom: 2em; display: {{ $showAyudas ? 'block' : 'none' }};"
                    id="fundicion-ayudas-section">
                    <h2>Ayudas Visuales Vinculadas a la OT</h2>
                    <div class="dibujos-card" style="margin-top: 10px;">
                        <p class="d-text-muted d-mb-2">¿Deseas agregar alguna ayuda visual para estos dibujos? Selecciona las clases
                            correspondientes:</p>
                        <form action="{{ route('fundicion.save_ayudas') }}" method="POST">
                            @csrf
                            <input type="hidden" name="ot" value="{{ $expectedFolderName ?? $folderPathLabel }}">

                            @if(count($ayudasConEstado) > 0)
                                <div class="ayudas-grid d-justify-center">
                                    @foreach($ayudasConEstado as $ayuda)
                                        <label class="ayuda-chip {{ !$ayuda['is_new'] ? 'ayuda-up-to-date' : '' }}">
                                            <input type="checkbox" name="ayudas[]" value="{{ $ayuda['nombre'] }}" {{ $ayuda['is_selected'] ? 'checked' : '' }} {{ !$ayuda['is_new'] ? 'disabled' : '' }}>

                                            <div class="ayuda-chip-content">
                                                <div class="ayuda-chip-icon">
                                                    @if(!$ayuda['is_new'])
                                                        <span style="color: #4ade80;">✔</span>
                                                    @else
                                                        ✓
                                                    @endif
                                                </div>
                                                <span class="ayuda-chip-label">
                                                    {{ $ayuda['nombre'] }}
                                                    @if($ayuda['is_new'])
                                                        <span class="new-ayuda-badge">NUEVO</span>
                                                    @endif
                                                </span>
                                            </div>
                                        </label>
                                    @endforeach
                                </div>
                                <div class="d-flex d-justify-center d-mt-2 d-gap-2">
                                    <button type="submit" class="btn-dibujos"
                                        style="width: auto; padding: 0.8em 2.5em; border-radius: 50px;">
                                        Vincular Ayudas Visuales a esta OT
                                    </button>
                                    <button type="button" id="btn-desvincular-ayudas" class="btn-dibujos btn-dibujos-danger"
                                        style="width: auto; padding: 0.8em 2.5em; border-radius: 50px;"
                                        data-ot="{{ $expectedFolderName ?? $folderPathLabel }}">
                                        Desvincular Ayudas Actuales
                                    </button>
                                </div>
                            @else
                                <div class="d-alert d-alert-warning">
                                    No hay ayudas visuales maestras subidas en el servidor. (Directorio DOCUMENTACION_GIS /
                                    AYUDAS_FUNDICION vacío)
                                </div>
                            @endif
                        </form>
                    </div>
                </div>
            @endif
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
                                @if($moduleType === 'dibujos')
                                    <th class="d-text-center">Orden de Trabajo</th>
                                    <th class="d-text-center">Clase</th>
                                @elseif($moduleType === 'fundicion')
                                    <th class="d-text-center">Orden de Trabajo</th>
                                    <th class="d-text-center">Ayudas Visuales Vinculadas</th>
                                @elseif($moduleType === 'manuales')
                                    <th class="d-text-center">Proceso</th>
                                @elseif($moduleType === 'ayudas')
                                    <th class="d-text-center">Clase</th>
                                    <th class="d-text-center">Proceso</th>
                                @else
                                    <th class="d-text-center">Clase</th>
                                @endif
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
                                        <tr>
                                            <td class="d-text-center d-text-primary"><strong>{{ $otLabel }}</strong></td>
                                            <td class="d-text-center"><em class="d-text-danger d-text-bold">Sin clases</em></td>
                                            <td><span class="badge-count badge-count-empty">0</span></td>
                                            <td class="d-text-center">
                                                <div class="td-actions">
                                                    <button class="btn-action-icon btn-eliminar-carpeta" title="Eliminar OT completa"
                                                        onclick="confirmarEliminarCarpeta('{{ $otName }}', null, '{{ $otLabel }}')">
                                                        <img src="{{ asset('images/papelera-de-reciclaje.png') }}" alt="Eliminar">
                                                        <span>Eliminar Carpeta</span>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    @else
                                        @foreach($clases as $claseName)
                                            @php
                                                $claseReal = $otReal ? $otReal->clases->firstWhere('nombre', $claseName) : null;
                                                $claseIdBD = $claseReal ? $claseReal->id : null;
                                                $badgeId = "badge-" . Str::slug($otName) . "-" . Str::slug($claseName);
                                            @endphp
                                            <tr data-ot="{{ $otName }}" data-clase="{{ $claseName }}">
                                                <td class="d-text-center d-text-primary"><strong>{{ $otLabel }}</strong></td>
                                                <td class="d-text-center d-text-success d-text-bold">{{ $claseName }}</td>
                                                <td class="d-text-center"><span class="badge-count" id="{{ $badgeId }}">...</span></td>
                                                <td class="d-text-center">
                                                    <div class="td-actions">
                                                        <button class="btn-action-icon btn-ver-archivos" title="Ver archivos"
                                                            onclick="irACarpeta('{{ $otIdBD ?? $otName }}', '{{ $claseIdBD ?? $claseName }}', {{ $otIdBD ? 'true' : 'false' }})">
                                                            <img src="{{ asset('images/documento.png') }}" alt="Ver">
                                                            <span>Ver PDF's</span>
                                                        </button>
                                                        <button class="btn-action-icon btn-eliminar-carpeta" title="Eliminar carpeta"
                                                            onclick="confirmarEliminarCarpeta('{{ $otName }}', '{{ $claseName }}', '{{ $otLabel }} / {{ $claseName }}')">
                                                            <img src="{{ asset('images/papelera-de-reciclaje.png') }}" alt="Eliminar">
                                                            <span>Eliminar Carpeta</span>
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    @endif
                                @endforeach
                            @elseif($moduleType === 'manuales')
                                @foreach($estructura as $procesoName => $extra)
                                    @php
                                        $procesoReal = $todosLosProcesos->firstWhere('nombre', $procesoName);
                                        $procesoIdBD = $procesoReal ? $procesoReal->id : null;
                                        $badgeId = "badge-" . Str::slug($procesoName);
                                    @endphp
                                    <tr data-proceso="{{ $procesoName }}">
                                        <td class="d-text-center d-text-primary"><strong>{{ $procesoName }}</strong></td>
                                        <td class="d-text-center"><span class="badge-count" id="{{ $badgeId }}">...</span></td>
                                        <td class="d-text-center">
                                            <div class="td-actions">
                                                <button class="btn-action-icon btn-ver-archivos" title="Ver archivos"
                                                    onclick="irACarpeta('{{ $procesoIdBD ?? $procesoName }}', null, {{ $procesoIdBD ? 'true' : 'false' }})">
                                                    <img src="{{ asset('images/documento.png') }}" alt="Ver">
                                                    <span>Ver PDF's</span>
                                                </button>
                                                <button class="btn-action-icon btn-eliminar-carpeta" title="Eliminar carpeta"
                                                    onclick="confirmarEliminarCarpeta('{{ $procesoName }}', null, '{{ $procesoName }}')">
                                                    <img src="{{ asset('images/papelera-de-reciclaje.png') }}" alt="Eliminar">
                                                    <span>Eliminar Carpeta</span>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            @elseif($moduleType === 'fundicion')
                                @foreach($estructura as $otName => $ayudasLinked)
                                    @php
                                        preg_match('/OT\s*(\d+)/', $otName, $matches);
                                        $otIdNumber = isset($matches[1]) ? (int) $matches[1] : 0;
                                        $otReal = $otIdNumber > 0 ? $todasLasOTs->firstWhere('id', $otIdNumber) : null;
                                        $otLabel = $otReal ? ("OT " . $otReal->id . ($otReal->moldura ? " — " . $otReal->moldura->nombre : "")) : $otName;
                                        $otIdBD = $otReal ? $otReal->id : null;
                                        $badgeId = "badge-" . Str::slug($otName);
                                    @endphp
                                    <tr data-ot="{{ $otName }}">
                                        <td class="d-text-center d-text-primary"><strong>{{ $otLabel }}</strong></td>
                                        <td class="d-text-center">
                                            @if(!empty($ayudasLinked))
                                                <div class="d-flex d-flex-wrap d-justify-center d-gap-1">
                                                    @foreach($ayudasLinked as $al)
                                                        <span class="badge-ayuda-tag">{{ $al }}</span>
                                                    @endforeach
                                                </div>
                                            @else
                                                <span class="d-text-subtle" style="font-size: 0.85em;">Sin ayudas</span>
                                            @endif
                                        </td>
                                        <td class="d-text-center"><span class="badge-count" id="{{ $badgeId }}">...</span></td>
                                        <td class="d-text-center">
                                            <div class="td-actions">
                                                <button class="btn-action-icon btn-ver-archivos" title="Ver archivos"
                                                    onclick="irACarpeta('{{ $otIdBD ?? $otName }}', null, {{ $otIdBD ? 'true' : 'false' }})">
                                                    <img src="{{ asset('images/documento.png') }}" alt="Ver">
                                                    <span>Ver PDF's</span>
                                                </button>
                                                <button class="btn-action-icon btn-alerta-fund" title="Enviar correo de alerta"
                                                    onclick="enviarAlertaFundicion(null, '{{ $otName }}', this)">
                                                    <img src="{{ asset('images/enviando.png') }}" alt="Alerta">
                                                    <span>Enviar Correo</span>
                                                </button>
                                                <button class="btn-action-icon btn-eliminar-carpeta" title="Eliminar carpeta"
                                                    onclick="confirmarEliminarCarpeta('{{ $otName }}', null, '{{ $otLabel }}')">
                                                    <img src="{{ asset('images/papelera-de-reciclaje.png') }}" alt="Eliminar">
                                                    <span>Eliminar Carpeta</span>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            @elseif($moduleType === 'ayudas')
                                @foreach($estructura as $claseName => $procesos)
                                    @php
                                        $claseReal = $clasesUnicas->firstWhere('nombre', $claseName);
                                        $claseIdBD = $claseReal ? $claseReal->id : null;
                                    @endphp
                                    @foreach($procesos as $procesoName)
                                        @php
                                            $procesoReal = $todosLosProcesos->firstWhere('nombre', $procesoName);
                                            $procesoIdBD = $procesoReal ? $procesoReal->id : null;
                                            $badgeId = "badge-" . Str::slug($procesoName) . "-" . Str::slug($claseName);
                                            $esHuerfano = ($claseName === '-- SIN CLASE --');
                                        @endphp
                                        <tr data-proceso="{{ $procesoName }}" data-clase="{{ $claseName }}">
                                            <td class="d-text-center d-text-primary">
                                                <strong>{{ $esHuerfano ? $procesoName : $claseName }}</strong>
                                            </td>
                                            <td class="d-text-center">
                                                @if($esHuerfano)
                                                    <em class="d-text-danger d-text-bold">Sin clases</em>
                                                @else
                                                    <span class="d-text-success d-text-bold">{{ $procesoName }}</span>
                                                @endif
                                            </td>
                                            <td class="d-text-center"><span class="badge-count" id="{{ $badgeId }}">...</span></td>
                                            <td class="d-text-center">
                                                <div class="td-actions">
                                                    @if($esHuerfano)
                                                        <button class="btn-action-icon btn-eliminar-carpeta" title="Eliminar proceso completo"
                                                            onclick="confirmarEliminarCarpeta('{{ $procesoName }}', null, '{{ $procesoName }}')">
                                                            <img src="{{ asset('images/papelera-de-reciclaje.png') }}" alt="Eliminar">
                                                            <span>Eliminar Carpeta</span>
                                                        </button>
                                                    @else
                                                        <button class="btn-action-icon btn-ver-archivos" title="Ver archivos"
                                                            onclick="irACarpeta('{{ $procesoIdBD ?? $procesoName }}', '{{ $claseIdBD ?? $claseName }}', {{ $procesoIdBD && $claseIdBD ? 'true' : 'false' }})">
                                                            <img src="{{ asset('images/documento.png') }}" alt="Ver">
                                                            <span>Ver PDF's</span>
                                                        </button>
                                                        <button class="btn-action-icon btn-eliminar-carpeta" title="Eliminar carpeta"
                                                            onclick="confirmarEliminarCarpeta('{{ $procesoName }}', '{{ $claseName }}', '{{ $claseName }} / {{ $procesoName }}')">
                                                            <img src="{{ asset('images/papelera-de-reciclaje.png') }}" alt="Eliminar">
                                                            <span>Eliminar Carpeta</span>
                                                        </button>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                @endforeach
                            @elseif($moduleType === 'ayudas_fundicion')
                                @foreach($estructura as $claseName => $procesos)
                                    @php
                                        $claseReal = $clasesUnicas->firstWhere('nombre', $claseName);
                                        $claseIdBD = $claseReal ? $claseReal->id : null;
                                    @endphp
                                    @foreach($procesos as $procesoName)
                                        @php
                                            $badgeId = "badge-" . Str::slug($procesoName) . "-" . Str::slug($claseName);
                                        @endphp
                                        <tr data-proceso="{{ $procesoName }}" data-clase="{{ $claseName }}">
                                            <td class="d-text-center d-text-primary"><strong>{{ $claseName }}</strong></td>
                                            <td class="d-text-center"><span class="badge-count" id="{{ $badgeId }}">...</span></td>
                                            <td class="d-text-center">
                                                <div class="td-actions">
                                                    <button class="btn-action-icon btn-ver-archivos" title="Ver archivos"
                                                        onclick="irACarpeta('{{ $procesoName }}', '{{ $claseIdBD ?? $claseName }}', {{ $claseIdBD ? 'true' : 'false' }})">
                                                        <img src="{{ asset('images/documento.png') }}" alt="Ver">
                                                        <span>Ver PDF's</span>
                                                    </button>
                                                    <button class="btn-action-icon btn-eliminar-carpeta" title="Eliminar carpeta"
                                                        onclick="confirmarEliminarCarpeta('{{ $procesoName }}', '{{ $claseName }}', '{{ $claseName }} / {{ $procesoName }}')">
                                                        <img src="{{ asset('images/papelera-de-reciclaje.png') }}" alt="Eliminar">
                                                        <span>Eliminar Carpeta</span>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
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
                    <img src="{{ asset('images/papelera-de-reciclaje.png') }}" alt="Eliminar">
                </div>
                <p style="font-size: 1.1em; line-height: 1.6;">
                    Se va a eliminar de la carpeta principal <br>
                    <strong id="confirm-parent-label" style="color: #033966; font-size: 1.2em;">—</strong> <br>
                    la subcarpeta del <span id="confirm-type-label">...</span>: <br>
                    <span id="confirm-folder-name" class="confirm-label-highlight"
                        style="display: inline-block; margin-top: 0.3em;">—</span>
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
            @if($moduleType === 'fundicion')
                'fundicion.send_alert': "{{ route('fundicion.send_alert') }}",
            @endif
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
            window.activeParam2 = null;
        @elseif($moduleType === 'ayudas')
            window.activeParam1 = @json($procesoActivo?->nombre ?? null);
            window.activeParam2 = @json($claseActiva?->nombre ?? null);
        @endif
    </script>
@endsection
