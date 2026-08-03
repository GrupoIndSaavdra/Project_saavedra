@extends('layouts.appMenu')

@section('head')
    <title>{{ $pageTitle ?? 'Gestión de Documentación' }}</title>
    <link rel="icon" href="{{ url('images/lg_saavedra.png') }}?v=1">
    @vite([
        'resources/css/wo_views/manage_casting.css',
        'resources/js/wo_views/manage_casting.js'
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

                {{-- Estado de la carpeta y botón crear --}}
                @php
                    $isReady = false;
                    $folderPathLabel = '';
                    $folderProps = [];
                    $alertContext = 'Selecciona las opciones necesarias para continuar.';
                    $carpetaExiste = false;

                    if ($otSeleccionadaId && $claseSeleccionadaId) {
                        $isReady = true;

                        if ($otActiva) {
                            $otLabel = "OT " . $otActiva->id . ($otActiva->moldura ? " - " . $otActiva->moldura->nombre : "");
                            $normalizedOt = trim(preg_replace('/\s+/', ' ', mb_strtoupper(str_replace(['—', '–', "\xc2\xa0"], '-', $otLabel))));
                            $param1Name = $normalizedOt;
                            $param2Name = $claseActiva ? $claseActiva->nombre : $claseSeleccionadaId;
                        } else {
                            $param1Name = $otSeleccionadaId; // Fallback al string crudo si no hay OT activa
                            $param2Name = $claseSeleccionadaId;
                        }

                        $folderPathLabel = "<span class='lvl-1'>" . htmlspecialchars($param1Name) . "</span> <span class='lvl-sep'>/</span> <span class='lvl-2'>" . htmlspecialchars($param2Name) . "</span>";
                        $carpetaExiste = isset($estructura[$param1Name]) && in_array($param2Name, $estructura[$param1Name]);
                        $folderProps = ['data-ot' => $param1Name, 'data-clase' => $param2Name, 'data-ot-id' => $otActiva ? $otActiva->id : ''];
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
                            Carpeta destino: <strong class="folder-label d-text-bold" class="text-primary">...</strong>
                        </p>

                        <div id="alert-upload-no-folder" class="d-alert d-alert-warning d-mb-3"
                            class="d-alert d-alert-warning custom-alert-warning hidden">
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
                        <div class="dibujos-files-panel active d-flex d-flex-column h-100" id="panel-archivos" style="margin-bottom: 0;">
                            <div class="d-flex d-justify-between d-align-center mb-1-5 border-bottom pb-0-5">
                                <h2 class="m-0 pb-0 border-none">Archivos en: <span>{!! $folderPathLabel !!}</span></h2>
                                <div class="dibujos-files-breadcrumb" class="m-0 bg-none p-0 border-none shrink-0">
                                    Carpeta activa: <strong>{!! $folderPathLabel !!}</strong>
                                </div>
                            </div>

                            <div class="dibujos-files-grid flex-1 overflow-y-auto align-content-start" id="archivos-grid" style="max-height: 50vh;">
                                <p class="d-text-subtle d-text-center d-w-100">Cargando archivos...</p>
                            </div>
                        </div>

                        {{-- Panel de Ayudas Visuales Manuales (Solo para aquellas que no son automáticas) --}}
                        {{-- Sección de Ayudas Manuales Eliminada por Requerimiento --}}
                    @endif

                    <div class="dibujos-table-section d-flex d-flex-column h-100" style="margin-bottom: 0;">
                        @if($isReady)
                            <div class="d-flex d-flex-column gap-0-5 mb-1">
                                <div class="d-flex d-justify-between d-align-center flex-wrap gap-1">
                                    <h2 class="m-0 p-0 border-none flex-1 min-w-250">Estructura Actual de Carpetas en el Servidor</h2>
                                    <div class="position-relative min-w-240 max-w-360">
                                        <select id="filtro-tabla-estructura"
                                                class="custom-select">
                                            <option value="">— Mostrar Todos —</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="d-flex d-align-center d-justify-start gap-0-8 flex-wrap">
                                </div>
                            </div>
                        @else
                            <div class="d-flex d-justify-between d-align-center gap-1 mb-1 flex-wrap">
                                <h2 class="m-0 p-0 border-none">Estructura Actual de Carpetas en el Servidor</h2>

                                <div class="d-flex d-align-center d-justify-center gap-0-8 flex-1 min-w-250">
                                </div>

                                <div class="position-relative min-w-240 max-w-360">
                                    <select id="filtro-tabla-estructura"
                                            class="custom-select">
                                        <option value="">— Mostrar Todos —</option>
                                    </select>
                                </div>
                            </div>
                        @endif

            @if(count($estructura) === 0)
                <div class="dibujos-empty-state">
                    <p>No hay carpetas creadas aun.</p>
                </div>
            @else
                <div class="dibujos-table-container" style="max-height: 50vh; overflow-y: auto;">
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

                                @foreach($estructura as $otName => $clasesFisicas)
                                    @php
                                        preg_match('/OT\s*(\d+)/', $otName, $matches);
                                        $otIdNumber = isset($matches[1]) ? (int) $matches[1] : 0;
                                        $otReal = $otIdNumber > 0 ? $todasLasOTs->firstWhere('id', $otIdNumber) : null;
                                        $otLabel = $otReal ? ("OT " . $otReal->id . ($otReal->moldura ? " — " . $otReal->moldura->nombre : "")) : $otName;
                                        $otIdBD = $otReal ? $otReal->id : null;

                                        // Ayudas vinculadas desde el historial
                                        $ayudasLinked = $historiales[$otName] ?? [];

                                        // Combinar las clases físicas con las ayudas vinculadas para asegurar que todas tengan una fila
                                        $clasesCompletas = array_unique(array_merge($clasesFisicas, $ayudasLinked));
                                        $clasesValidas = array_filter($clasesCompletas, function($c) {
                                            if (is_null($c)) return true; // Mantener archivos en raíz
                                            $val = trim(strtolower((string) $c));
                                            return !empty($val) && $val !== 'null' && $val !== 'undefined';
                                        });
                                        $displayClases = count($clasesValidas) > 0 ? $clasesValidas : [null];
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
                                                    // Ya no filtramos por existencia física, mostramos todas las vinculadas
                                                    $ayudasFiltradas = collect($ayudasLinked)->filter(function ($a) {
                                                        $val = trim(strtolower((string) $a));
                                                        return !empty($val) && $val !== 'null' && $val !== 'undefined';
                                                    });
                                                @endphp
                                                @if($ayudasFiltradas->count() > 0)
                                                    <div class="d-flex d-flex-wrap d-justify-center d-gap-1 tags-container">
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
                                                                $estadoClase = $alertasEnviadas[$otName][$al] ?? 'pendiente';
                                                                $tagClass = '';
                                                                if ($estadoClase === 'enviada') $tagClass = 'alerta-enviada-tag';
                                                                elseif ($estadoClase === 'modificada') $tagClass = 'alerta-modificada-tag';
                                                                elseif ($estadoClase === 'vacio') $tagClass = 'alerta-vacia-tag';
                                                            @endphp
                                                            <span class="badge-ayuda-tag clickable-tag {{ $isThisClass ? 'badge-tag-active' : '' }} {{ $tagClass }}"
                                                                title="Filtrar por esta clase"
                                                                onclick="irACarpeta({{ \Illuminate\Support\Js::from($otIdBD ?? $otName) }}, {{ \Illuminate\Support\Js::from($otIdBD ? $clTagId : $al) }}, {{ $otIdBD ? 'true' : 'false' }})">
                                                                {{ $al }}
                                                            </span>
                                                        @endforeach
                                                    </div>
                                                @else
                                                    <div class="d-flex d-flex-wrap d-justify-center d-gap-1 tags-container">
                                                        <span class="badge-ayuda-tag alerta-sin-clases-tag" class="pointer-events-none">Sin clases vinculadas</span>
                                                    </div>
                                                @endif
                                            </td>
                                            <td class="d-text-center"><span class="badge-count" id="{{ $badgeId }}">...</span></td>
                                            <td class="d-text-center">
                                                <div class="td-actions">
                                                    <button class="btn-action-icon btn-ver-archivos" title="Ver archivos"
                                                        onclick="irACarpeta({{ \Illuminate\Support\Js::from($otIdBD ?? $otName) }}, {{ \Illuminate\Support\Js::from($otIdBD ? $claseIdBD : ($esRaiz ? null : $claseName)) }}, {{ $otIdBD ? 'true' : 'false' }})">
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

                        </tbody>
                    </table>
                </div>
            @endif
                    </div>
                </div>

        {{-- NUEVA TABLA: Envío de Alertas por OT (Simplificada) --}}
        @if(count($estructura) > 0)
            <div class="dibujos-table-section d-mt-3" @class(['grid-col-full', 'd-flex d-flex-column' => !$isReady])>
                <div class="d-flex d-justify-between d-align-center gap-1 mb-1 flex-wrap">
                    <h2 class="m-0 p-0 border-none">Envío de Alertas a Producción</h2>

                    <div class="d-flex d-align-center d-justify-center gap-0-8 flex-1 min-w-250">
                    </div>

                    <div class="position-relative min-w-240 max-w-360">
                        <select id="filtro-tabla-alertas"
                                class="custom-select">
                            <option value="">— Mostrar Todas las OTs —</option>
                        </select>
                    </div>
                </div>
                <div class="dibujos-table-container d-log-scroll" @class(['flex-1 overflow-y-auto', 'max-h-40vh' => $isReady, 'max-h-calc' => !$isReady])>
                    <table class="dibujos-table">
                        <thead>
                            <tr>
                                <th class="d-text-center">Orden de Trabajo</th>
                                <th class="d-text-center">Ayudas Visuales Vinculadas por Clase</th>
                                <th class="d-text-center">Archivos PDF</br>(Total)</th>
                                <th class="d-text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody id="tabla-alertas-body">
                            @foreach($estructura as $otName => $clasesFisicas)
                                @php
                                    preg_match('/OT\s*(\d+)/', $otName, $matches);
                                    $otIdNumber = isset($matches[1]) ? (int) $matches[1] : 0;
                                    $otReal = $otIdNumber > 0 ? $todasLasOTs->firstWhere('id', $otIdNumber) : null;
                                    $otLabel = $otReal ? ("OT " . $otReal->id . ($otReal->moldura ? " — " . $otReal->moldura->nombre : "")) : $otName;
                                    $otIdBD = $otReal ? $otReal->id : null;
                                    $ayudasLinked = $historiales[$otName] ?? [];
                                    $clasesEnviadas = $alertasEnviadas[$otName] ?? [];

                                    $ayudasFiltradas = collect($ayudasLinked)->filter(function ($a) {
                                        $val = trim(strtolower((string) $a));
                                        return !empty($val) && $val !== 'null' && $val !== 'undefined';
                                    });
                                @endphp
                                <tr>
                                    <td class="d-text-center d-text-primary"><strong>{{ $otLabel }}</strong></td>
                                    <td class="d-text-center">
                                        @if($ayudasFiltradas->count() > 0)
                                            <div class="d-flex d-flex-wrap d-justify-center d-gap-1 tags-container">
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
                                                        $estadoClase = $alertasEnviadas[$otName][$al] ?? 'pendiente';
                                                        $tagClass = '';
                                                        if ($estadoClase === 'enviada') $tagClass = 'alerta-enviada-tag';
                                                        elseif ($estadoClase === 'modificada') $tagClass = 'alerta-modificada-tag';
                                                        elseif ($estadoClase === 'vacio') $tagClass = 'alerta-vacia-tag';
                                                    @endphp
                                                    <span class="badge-ayuda-tag clickable-tag {{ $tagClass }}" title="Ir a esta carpeta"
                                                        onclick="irACarpeta({{ \Illuminate\Support\Js::from($otIdBD ?? $otName) }}, {{ \Illuminate\Support\Js::from($clTagId) }}, {{ $otIdBD ? 'true' : 'false' }})">
                                                        {{ $al }}
                                                    </span>
                                                @endforeach
                                            </div>
                                        @else
                                            <div class="d-flex d-flex-wrap d-justify-center d-gap-1 tags-container">
                                                <span class="badge-ayuda-tag alerta-sin-clases-tag" class="pointer-events-none">Sin clases vinculadas</span>
                                            </div>
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
                <div class="dibujos-table-section" class="dibujos-table-section-clean">
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
                            <td colspan="5" class="d-text-center d-text-subtle" class="p-1">Cargando registro...
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </details>
    </div>

            </div>
        </div>

    </div>


    {{-- Modal de Confirmación Estilo Premium (prod-viewer) --}}
    <div id="dibujos-confirm-modal" class="confirm-portal hidden">
        <div class="confirm-modal">
            <div class="confirm-modal-header" class="d-justify-center">
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

    <!-- Botón Flotante de Simbología -->
    <div class="floating-symbology-wrapper">
        <div class="floating-symbology-btn">
            <span class="fw-bold fs-1-1 text-uppercase">Código de Colores</span>
        </div>
        <div class="floating-symbology-panel">
            <span class="badge-ayuda-tag alerta-vacia-tag" class="badge-legend">Vacía</span>
            <span class="badge-ayuda-tag" class="badge-legend">Pendiente</span>
            <span class="badge-ayuda-tag alerta-enviada-tag" class="badge-legend">Enviada</span>
            <span class="badge-ayuda-tag alerta-modificada-tag" class="badge-legend">Modificada</span>
            <span class="badge-ayuda-tag alerta-sin-clases-tag" class="badge-legend">Sin Clases</span>
        </div>
    </div>

    <script>
        window.baseUrl = "{{ url('/') }}";
        window.cerrarImgUrl = "{{ asset('images/cerrar.png') }}";
        window.moduleType = "{{ $moduleType }}";
        window.routesPrefix = "{{ $modulePrefix }}";
        window.csrfToken = "{{ csrf_token() }}";
        window.estructura = @json($estructura);
        window.historiales = @json($historiales);
        window.alertasEnviadas = @json($alertasEnviadas);
        window.todasLasOTs = @json($todasLasOTs);

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
            'fundicion.send_alert': "{{ route('fundicion.send_alert') }}",
            'doc.total_archivos': "{{ route('fundicion.total_archivos') }}",
                                };
        window.csrfToken = "{{ csrf_token() }}";
        window.estructura = @json($estructura);

        window.todasLasOTs = {!! json_encode($todasLasOTs->map(fn($o) => ['id' => $o->id, 'moldura_nombre' => $o->moldura?->nombre])) !!};
        window.todasLasClases = {!! json_encode($todasLasClases->map(fn($c) => ['id' => $c->id, 'nombre' => $c->nombre])) !!};
        window.historiales = {!! json_encode($historiales) !!};

        // Exportar active selection para cargar panel inicialmente
        window.activeParam1 = @json($otActiva?->id ?? null);
        window.activeParam2 = @json($claseActiva?->nombre ?? null);
    </script>
@endsection
