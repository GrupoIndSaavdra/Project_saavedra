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
                                <option value="--" {{ $claseSeleccionadaId === '--' ? 'selected' : '' }}>
                                    Archivos en Raíz (Antiguos)
                                </option>
                            @endif
                        </select>
                    </div>

                @elseif($moduleType === 'fundicion')
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
                                {{-- Opciones Opcionales --}}
                                <option value="Pistones" {{ $claseSeleccionadaId === 'Pistones' ? 'selected' : '' }}>
                                    Pistones (Opcional)
                                </option>
                                <option value="Guías" {{ $claseSeleccionadaId === 'Guías' ? 'selected' : '' }}>
                                    Guías (Opcional)
                                </option>
                            @endif
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
                        $folderPathLabel = "<span class='lvl-1'>OT " . $otActiva->id . "</span> <span class='lvl-sep'>/</span> <span class='lvl-2'>" . $claseActiva->nombre . "</span>";
                        $carpetaExiste = isset($estructura[$param1Name]) && in_array($param2Name, $estructura[$param1Name]);
                        $folderProps = ['data-ot' => $param1Name, 'data-clase' => $param2Name];
                    } elseif ($moduleType === 'manuales' && $procesoSeleccionadoId && $procesoActivo) {
                        $isReady = true;
                        $param1Name = $procesoActivo->nombre;
                        $folderPathLabel = "<span class='lvl-1'>" . $procesoActivo->nombre . "</span>";
                        $carpetaExiste = in_array($param1Name, $estructura);
                        $folderProps = ['data-proceso' => $param1Name];
                    } elseif ($moduleType === 'ayudas' && $procesoSeleccionadoId && $claseSeleccionadaId && $procesoActivo && $claseActiva) {
                        $isReady = true;
                        $param1Name = $procesoActivo->nombre;
                        $param2Name = $claseActiva->nombre;
                        $folderPathLabel = "<span class='lvl-1'>" . $claseActiva->nombre . "</span> <span class='lvl-sep'>/</span> <span class='lvl-2'>" . $procesoActivo->nombre . "</span>";
                        $carpetaExiste = isset($estructura[$param2Name]) && in_array($param1Name, $estructura[$param2Name]);
                        $folderProps = ['data-proceso' => $param1Name, 'data-clase' => $param2Name];
                    } elseif ($moduleType === 'ayudas_fundicion' && $claseSeleccionadaId && $claseActiva) {
                        $isReady = true;
                        $param1Name = 'Fundicion';
                        $param2Name = $claseActiva->nombre;
                        $folderPathLabel = "<span class='lvl-1'>" . $claseActiva->nombre . "</span>";
                        $carpetaExiste = isset($estructura[$param2Name]);
                        $folderProps = ['data-proceso' => $param1Name, 'data-clase' => $param2Name];
                    } elseif ($moduleType === 'fundicion' && $otSeleccionadaId && $claseSeleccionadaId && $otActiva) {
                        $isReady = true;
                        $otLabel = "OT " . $otActiva->id . ($otActiva->moldura ? " - " . $otActiva->moldura->nombre : "");
                        // Normalización básica para coincidir con el controlador
                        $normalizedOt = trim(preg_replace('/\s+/', ' ', mb_strtoupper(str_replace(['—', '–', "\xc2\xa0"], '-', $otLabel))));

                        $param1Name = $normalizedOt;
                        $param2Name = $claseActiva ? $claseActiva->nombre : $claseSeleccionadaId;
                        $folderPathLabel = "<span class='lvl-1'>" . $otLabel . "</span> <span class='lvl-sep'>/</span> <span class='lvl-2'>" . $param2Name . "</span>";
                        $carpetaExiste = isset($estructura[$param1Name]) && in_array($param2Name, $estructura[$param1Name]);
                        $folderProps = ['data-ot' => $param1Name, 'data-clase' => $param2Name, 'data-ot-id' => $otActiva->id];
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
                                @if($moduleType === 'dibujos')
                                    <th class="d-text-center">Orden de Trabajo</th>
                                    <th class="d-text-center">Clase</th>
                                @elseif($moduleType === 'fundicion')
                                    <th class="d-text-center">Orden de Trabajo</th>
                                    <th class="d-text-center">Clase</th>
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
                            @elseif($moduleType === 'manuales')
                                @foreach($estructura as $procesoName)
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
                                                    <img src="{{ asset('images/Eliminar-Carpeta.png') }}" alt="Eliminar">
                                                    <span>Eliminar Directorio Raíz</span>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            @elseif($moduleType === 'fundicion')
                                @foreach($estructura as $otName => $clasesFisicas)
                                    @php
                                        preg_match('/OT\s*(\d+)/', $otName, $matches);
                                        $otIdNumber = isset($matches[1]) ? (int) $matches[1] : 0;
                                        $otReal = $otIdNumber > 0 ? $todasLasOTs->firstWhere('id', $otIdNumber) : null;
                                        $otLabel = $otReal ? ("OT " . $otReal->id . ($otReal->moldura ? " — " . $otReal->moldura->nombre : "")) : $otName;
                                        $otIdBD = $otReal ? $otReal->id : null;

                                        // Ayudas vinculadas desde el historial
                                        $ayudasLinked = $historiales[$otName] ?? [];

                                        // Si no hay clases físicas, creamos una entrada para la raíz
                                        $displayClases = count($clasesFisicas) > 0 ? $clasesFisicas : [null];
                                    @endphp

                                    @foreach($displayClases as $claseName)
                                        @php
                                            $esRaiz = is_null($claseName);
                                            $claseLabel = $esRaiz ? 'Raíz OT' : $claseName;
                                            $claseReal = (!$esRaiz && $otReal) ? $otReal->clases->firstWhere('nombre', $claseName) : null;

                                            // Si no hay clase en BD pero es una de nuestras clases virtuales, usamos el nombre como ID
                                            if ($claseReal) {
                                                $claseIdBD = $claseReal->id;
                                            } elseif (in_array($claseName, ['Pistones', 'Guías', 'Guias'])) {
                                                $claseIdBD = $claseName;
                                            } else {
                                                $claseIdBD = 'null';
                                            }

                                            $badgeId = "badge-" . Str::slug($otName) . "-" . Str::slug($claseLabel);
                                        @endphp
                                        <tr data-ot="{{ $otName }}" data-clase="{{ $claseName }}">
                                            <td class="d-text-center d-text-primary"><strong>{{ $otLabel }}</strong></td>
                                            <td class="d-text-center">
                                                @php
                                                    // Filtrar por existencia física real en el servidor
                                                    $ayudasFiltradas = collect($ayudasLinked)->filter(function ($a) use ($clasesFisicas) {
                                                        $val = trim(strtolower((string) $a));
                                                        if (empty($val) || $val === 'null' || $val === 'undefined')
                                                            return false;
                                                        return in_array($a, $clasesFisicas);
                                                    });
                                                @endphp
                                                @if($ayudasFiltradas->count() > 0)
                                                    <div class="d-flex d-flex-wrap d-justify-center d-gap-1">
                                                        @foreach($ayudasFiltradas as $al)
                                                            @php
                                                                // Mostrar solo si coincide con la clase de la fila actual
                                                                if ($al !== $claseName && !$esRaiz)
                                                                    continue;

                                                                $clTagReal = $todasLasClases->firstWhere('nombre', $al);
                                                                if ($clTagReal) {
                                                                    $clTagId = $clTagReal->id;
                                                                } elseif (in_array($al, ['Pistones', 'Guías', 'Guias'])) {
                                                                    $clTagId = $al;
                                                                } else {
                                                                    $clTagId = 'null';
                                                                }
                                                                $isThisClass = ($al === $claseName);
                                                            @endphp
                                                            <span class="badge-ayuda-tag clickable-tag {{ $isThisClass ? 'badge-tag-active' : '' }}"
                                                                title="Filtrar por esta clase"
                                                                onclick="irACarpeta('{{ $otIdBD ?? $otName }}', '{{ $clTagId }}', {{ $otIdBD ? 'true' : 'false' }})">
                                                                {{ $al }}
                                                            </span>
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
                                                        onclick="irACarpeta('{{ $otIdBD ?? $otName }}', '{{ $claseIdBD }}', {{ $otIdBD ? 'true' : 'false' }})">
                                                        <img src="{{ asset('images/documento.png') }}" alt="Ver">
                                                        <span>Ver PDF's</span>
                                                    </button>
                                                    {{-- Botón de Correo Eliminado de aquí --}}
                                                    <button class="btn-action-icon btn-eliminar-carpeta" title="Eliminar carpeta"
                                                        onclick="confirmarEliminarCarpeta('{{ $otName }}', '{{ $claseName }}', '{{ $otLabel }} / {{ $claseLabel }}')">
                                                        <img src="{{ asset('images/Eliminar-Carpeta.png') }}" alt="Eliminar">
                                                        <span>{{ $esRaiz ? 'Vaciar Raíz' : 'Eliminar Clase' }}</span>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                @endforeach
                            @elseif($moduleType === 'ayudas')
                                @foreach($estructura as $claseName => $procesos)
                                    @php
                                        $claseReal = $clasesUnicas->firstWhere('nombre', $claseName);
                                        $claseIdBD = $claseReal ? $claseReal->id : null;

                                        // Si no tiene procesos, mostramos una fila "huérfana" para poder borrar la Clase
                                        $displayProcesos = count($procesos) > 0 ? $procesos : ['--'];
                                    @endphp

                                    @foreach($displayProcesos as $procesoName)
                                        @php
                                            $esHuerfano = ($procesoName === '--');
                                            $procesoReal = $esHuerfano ? null : $todosLosProcesos->firstWhere('nombre', $procesoName);
                                            $procesoIdBD = $procesoReal ? $procesoReal->id : null;
                                            $badgeId = "badge-" . Str::slug($claseName) . "-" . Str::slug($procesoName);
                                        @endphp
                                        <tr data-proceso="{{ $procesoName }}" data-clase="{{ $claseName }}">
                                            <td class="d-text-center d-text-primary">
                                                <strong>{{ $claseName }}</strong>
                                            </td>
                                            <td class="d-text-center">
                                                @if($esHuerfano)
                                                    <em class="d-text-danger d-text-bold">Sin procesos</em>
                                                @else
                                                    <span class="d-text-success d-text-bold">{{ $procesoName }}</span>
                                                @endif
                                            </td>
                                            <td class="d-text-center">
                                                @if($esHuerfano)
                                                    <span class="d-text-subtle">—</span>
                                                @else
                                                    <span class="badge-count" id="{{ $badgeId }}">...</span>
                                                @endif
                                            </td>
                                            <td class="d-text-center">
                                                <div class="td-actions">
                                                    @if(!$esHuerfano)
                                                        <button class="btn-action-icon btn-ver-archivos" title="Ver archivos"
                                                            onclick="irACarpeta('{{ $procesoIdBD ?? $procesoName }}', '{{ $claseIdBD ?? $claseName }}', {{ $procesoIdBD ? 'true' : 'false' }})">
                                                            <img src="{{ asset('images/documento.png') }}" alt="Ver">
                                                            <span>Ver PDF's</span>
                                                        </button>
                                                    @endif
                                                    <button class="btn-action-icon btn-eliminar-carpeta" title="Eliminar carpeta"
                                                        onclick="confirmarEliminarCarpeta('{{ $procesoName }}', '{{ $claseName }}', '{{ $claseName }}{{ $esHuerfano ? "" : " / " . $procesoName }}')">
                                                        <img src="{{ asset('images/Eliminar-Carpeta.png') }}" alt="Eliminar">
                                                        <span>Eliminar {{ $esHuerfano ? 'Directorio Raíz' : 'Proceso' }}</span>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                @endforeach
                            @elseif($moduleType === 'ayudas_fundicion')
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
                                                    onclick="irACarpeta('Fundicion', '{{ $claseIdBD ?? $claseName }}', false)">
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
                            @endif
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        {{-- NUEVA TABLA: Envío de Alertas por OT (Simplificada) --}}
        @if($moduleType === 'fundicion' && count($estructura) > 0)
            <div class="dibujos-table-section d-mt-3">
                <h2>Envío de Alertas</h2>
                <div class="dibujos-table-container">
                    <table class="dibujos-table">
                        <thead>
                            <tr>
                                <th class="d-text-center">Orden de Trabajo</th>
                                <th class="d-text-center">Ayudas Visuales Vinculadas por Clase</th>
                                <th class="d-text-center">Archivos PDF</br>(Total)</th>
                                <th class="d-text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($estructura as $otName => $clasesFisicas)
                                @php
                                    preg_match('/OT\s*(\d+)/', $otName, $matches);
                                    $otIdNumber = isset($matches[1]) ? (int) $matches[1] : 0;
                                    $otReal = $otIdNumber > 0 ? $todasLasOTs->firstWhere('id', $otIdNumber) : null;
                                    $otLabel = $otReal ? ("OT " . $otReal->id . ($otReal->moldura ? " — " . $otReal->moldura->nombre : "")) : $otName;
                                    $otIdBD = $otReal ? $otReal->id : null;
                                    $ayudasLinked = $historiales[$otName] ?? [];

                                    $ayudasFiltradas = collect($ayudasLinked)->filter(function ($a) use ($clasesFisicas) {
                                        $val = trim(strtolower((string) $a));
                                        if (empty($val) || $val === 'null' || $val === 'undefined')
                                            return false;
                                        // Solo mostrar si existe la carpeta física actualmente en la OT
                                        return in_array($a, $clasesFisicas);
                                    });
                                @endphp
                                <tr>
                                    <td class="d-text-center d-text-primary"><strong>{{ $otLabel }}</strong></td>
                                    <td class="d-text-center">
                                        @if($ayudasFiltradas->count() > 0)
                                            <div class="d-flex d-flex-wrap d-justify-center d-gap-1">
                                                @foreach($ayudasFiltradas as $al)
                                                    @php
                                                        $clTagReal = $todasLasClases->firstWhere('nombre', $al);
                                                        if ($clTagReal) {
                                                            $clTagId = $clTagReal->id;
                                                        } elseif (in_array($al, ['Pistones', 'Guías', 'Guias'])) {
                                                            $clTagId = $al;
                                                        } else {
                                                            $clTagId = 'null';
                                                        }
                                                    @endphp
                                                    <span class="badge-ayuda-tag clickable-tag" title="Ir a esta carpeta"
                                                        onclick="irACarpeta('{{ $otIdBD ?? $otName }}', '{{ $clTagId }}', {{ $otIdBD ? 'true' : 'false' }})">
                                                        {{ $al }}
                                                    </span>
                                                @endforeach
                                            </div>
                                        @else
                                            <span class="d-text-subtle">Sin ayudas vinculadas</span>
                                        @endif
                                    </td>
                                    <td class="d-text-center">
                                        <span class="badge-count" data-ot-total="{{ $otName }}">
                                            ...
                                        </span>
                                    </td>
                                    <td class="d-text-center">
                                        <div class="td-actions">
                                            <button class="btn-action-icon btn-alerta-fund" title="Enviar correo de alerta global"
                                                onclick="enviarAlertaFundicion(null, '{{ $otName }}', this)">
                                                <img src="{{ asset('images/enviando.png') }}" alt="Alerta">
                                                <span>Enviar Correo</span>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

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
            @if($moduleType === 'fundicion')
                                'fundicion.send_alert': "{{ route('fundicion.send_alert') }}",
                'doc.total_archivos': "{{ route('fundicion.total_archivos') }}",
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
