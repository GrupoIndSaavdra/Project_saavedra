@extends('layouts.appMenu')

@section('head')
    <title>{{ $pageTitle ?? 'Gestión de Documentación' }}</title>
    <link rel="icon" href="{{ url('images/lg_saavedra.png') }}?v=1">
    @vite([
        'resources/css/wo_views/manage_fundicion.css',
        'resources/js/wo_views/manage_fundicion.js'
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

                    if ($otSeleccionadaId && $claseSeleccionadaId && $otActiva) {
                        $isReady = true;
                        $otLabel = "OT " . $otActiva->id . ($otActiva->moldura ? " - " . $otActiva->moldura->nombre : "");
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


            </div> {{-- Fin Columna Izquierda --}}

            {{-- Columna Derecha (Visualización) --}}
            <div class="dibujos-dashboard-main" style="display: grid; grid-template-columns: {{ $isReady ? 'minmax(0, 1fr) minmax(0, 1fr)' : '1fr' }}; grid-template-rows: 1fr; min-height: calc(100vh - 180px); gap: 2em; align-items: stretch;">

                {{-- Panel de archivos de la carpeta seleccionada --}}
                @if($isReady)
                    <div class="dibujos-files-panel active" id="panel-archivos" style="display: flex; flex-direction: column; height: 100%;">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; border-bottom: 1px solid #e2e8f0; padding-bottom: 0.5rem;">
                            <h2 style="margin: 0; padding-bottom: 0; border: none;">Archivos en: <span>{!! $folderPathLabel !!}</span></h2>
                            <div class="dibujos-files-breadcrumb" style="margin: 0; background: none; padding: 0; border: none; flex-shrink: 0;">
                                Carpeta activa: <strong>{!! $folderPathLabel !!}</strong>
                            </div>
                        </div>

                        <div class="dibujos-files-grid" id="archivos-grid" style="flex: 1; max-height: none; overflow-y: auto; align-content: start;">
                            <p class="d-text-subtle d-text-center d-w-100">Cargando archivos...</p>
                        </div>
                    </div>

                    {{-- Panel de Ayudas Visuales Manuales (Solo para aquellas que no son automáticas) --}}
                    {{-- Sección de Ayudas Manuales Eliminada por Requerimiento --}}
                @endif

                <div style="{{ $isReady ? 'position: relative; height: 100%;' : 'display: flex; flex-direction: column; height: 100%;' }}">
                    <div class="dibujos-table-section" style="{{ $isReady ? 'position: absolute; top: 0; left: 0; right: 0; bottom: 0;' : 'flex: 1;' }} display: flex; flex-direction: column;">
                        @if($isReady)
                            <div style="display: flex; flex-direction: column; gap: 0.5rem; margin-bottom: 1rem;">
                                <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
                                    <h2 style="margin: 0; padding: 0; border: none; flex: 1; min-width: 250px;">Estructura Actual de Carpetas en el Servidor</h2>
                                    <div style="position: relative; min-width: 240px; max-width: 360px;">
                                        <select id="filtro-tabla-estructura"
                                                style="width: 100%; padding: 8px 14px; border-radius: 8px; border: 2px solid #033966; font-size: 0.9em; outline: none; background: #ffffff; color: #033966; font-weight: 700; cursor: pointer; box-shadow: 0 1px 3px rgba(0,0,0,0.08);">
                                            <option value="">— Mostrar Todos —</option>
                                        </select>
                                    </div>
                                </div>
                                <div style="display: flex; align-items: center; justify-content: flex-start; gap: 0.8rem; flex-wrap: wrap;">
                                </div>
                            </div>
                        @else
                            <div style="display: flex; justify-content: space-between; align-items: center; gap: 1rem; margin-bottom: 1rem; flex-wrap: wrap;">
                                <h2 style="margin: 0; padding: 0; border: none;">Estructura Actual de Carpetas en el Servidor</h2>

                                <div style="display: flex; align-items: center; justify-content: center; gap: 0.8rem; flex: 1; min-width: 250px;">
                                </div>

                                <div style="position: relative; min-width: 240px; max-width: 360px;">
                                    <select id="filtro-tabla-estructura"
                                            style="width: 100%; padding: 8px 14px; border-radius: 8px; border: 2px solid #033966; font-size: 0.9em; outline: none; background: #ffffff; color: #033966; font-weight: 700; cursor: pointer; box-shadow: 0 1px 3px rgba(0,0,0,0.08);">
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
                <div class="dibujos-table-container" style="flex: 1; max-height: calc(100vh - 280px); overflow-y: auto;">
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
                                                                onclick="irACarpeta({{ \Illuminate\Support\Js::from($otIdBD ?? $otName) }}, {{ \Illuminate\Support\Js::from($clTagId) }}, {{ $otIdBD ? 'true' : 'false' }})">
                                                                {{ $al }}
                                                            </span>
                                                        @endforeach
                                                    </div>
                                                @else
                                                    <div class="d-flex d-flex-wrap d-justify-center d-gap-1 tags-container">
                                                        <span class="badge-ayuda-tag alerta-sin-clases-tag" style="pointer-events: none;">Sin clases vinculadas</span>
                                                    </div>
                                                @endif
                                            </td>
                                            <td class="d-text-center"><span class="badge-count" id="{{ $badgeId }}">...</span></td>
                                            <td class="d-text-center">
                                                <div class="td-actions">
                                                    <button class="btn-action-icon btn-ver-archivos" title="Ver archivos"
                                                        onclick="irACarpeta({{ \Illuminate\Support\Js::from($otIdBD ?? $otName) }}, {{ \Illuminate\Support\Js::from($claseIdBD) }}, {{ $otIdBD ? 'true' : 'false' }})">
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
                @if($isReady)
                    </div>
                @else
                    </div>
                @endif

        {{-- NUEVA TABLA: Envío de Alertas por OT (Simplificada) --}}
        @if(count($estructura) > 0)
            <div class="dibujos-table-section d-mt-3" style="grid-column: 1 / -1; {{ $isReady ? '' : 'display: flex; flex-direction: column;' }}">
                <div style="display: flex; justify-content: space-between; align-items: center; gap: 1rem; margin-bottom: 1rem; flex-wrap: wrap;">
                    <h2 style="margin: 0; padding: 0; border: none;">Envío de Alertas a Producción</h2>

                    <div style="display: flex; align-items: center; justify-content: center; gap: 0.8rem; flex: 1; min-width: 250px;">
                    </div>

                    <div style="position: relative; min-width: 240px; max-width: 360px;">
                        <select id="filtro-tabla-alertas"
                                style="width: 100%; padding: 8px 14px; border-radius: 8px; border: 2px solid #033966; font-size: 0.9em; outline: none; background: #ffffff; color: #033966; font-weight: 700; cursor: pointer; box-shadow: 0 1px 3px rgba(0,0,0,0.08);">
                            <option value="">— Mostrar Todas las OTs —</option>
                        </select>
                    </div>
                </div>
                <div class="dibujos-table-container d-log-scroll" style="flex: 1; max-height: calc(50vh - 150px); overflow-y: auto;">
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
                                                <span class="badge-ayuda-tag alerta-sin-clases-tag" style="pointer-events: none;">Sin clases vinculadas</span>
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
        </details>
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

    <!-- Botón Flotante de Simbología -->
    <div class="floating-symbology-wrapper">
        <div class="floating-symbology-btn">
            <span style="font-weight: bold; font-size: 1.1em; text-transform: uppercase;">Código de Colores</span>
        </div>
        <div class="floating-symbology-panel">
            <span class="badge-ayuda-tag alerta-vacia-tag" style="padding: 8px 16px; font-size: 0.85em; pointer-events: none; transform: none !important; width: 100%; text-align: center; box-sizing: border-box;">Vacía</span>
            <span class="badge-ayuda-tag" style="padding: 8px 16px; font-size: 0.85em; pointer-events: none; width: 100%; text-align: center; box-sizing: border-box;">Pendiente</span>
            <span class="badge-ayuda-tag alerta-enviada-tag" style="padding: 8px 16px; font-size: 0.85em; pointer-events: none; transform: none !important; width: 100%; text-align: center; box-sizing: border-box;">Enviada</span>
            <span class="badge-ayuda-tag alerta-modificada-tag" style="padding: 8px 16px; font-size: 0.85em; pointer-events: none; transform: none !important; width: 100%; text-align: center; box-sizing: border-box;">Modificada</span>
            <span class="badge-ayuda-tag alerta-sin-clases-tag" style="padding: 8px 16px; font-size: 0.85em; pointer-events: none; transform: none !important; width: 100%; text-align: center; box-sizing: border-box;">Sin Clases</span>
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
