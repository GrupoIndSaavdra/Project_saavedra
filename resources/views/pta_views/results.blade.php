@extends('layouts.appMenu')

@section('head')
    <title>Resultados Sold. PTA — OT {{ $ot->id }}</title>
    <link rel="icon" type="image/x-icon" href="{{ asset('images/logos/favicon.ico') }}?v=3">
    @vite(['resources/css/pta_views/results.css'])
@endsection

@section('background-body', 'background-image:url("' . asset('images/fondos/fondoLogin.jpg') . '")')

@section('content')
    <div class="pta-wrapper">

        {{-- Header Principal --}}
        <div class="pta-header">
            <div class="pta-header-left">
                <div class="pta-header-icon-box" title="Análisis de Soldadura PTA">
                    <img src="{{ icon('vista_pta_analisis') }}" class="static-color-icon" alt="PTA Color" />
                </div>
                <div class="pta-header-text">
                    <h2>Resultados de Soldadura PTA</h2>
                    <div class="pta-header-badges">
                        <span class="pta-badge-chip">
                            <img src="{{ icon('ot_color') }}" class="badge-static-icon" alt="OT">
                            <span>OT {{ $ot->id }}{{ $ot->moldura ? ' — ' . $ot->moldura->nombre : '' }}</span>
                        </span>
                        <span class="pta-badge-chip pta-badge-chip-green">
                            <img src="{{ icon('clase_color') }}" class="badge-static-icon" alt="Clase">
                            <span>{{ $claseSeleccionada->nombre }}</span>
                        </span>
                        <span class="pta-badge-chip pta-badge-chip-sub">
                            Registro y control de inspección técnica
                        </span>
                    </div>
                </div>
            </div>
            <div class="pta-header-right">
                <a href="{{ route('showPiecesInProgress') }}" class="btn-pta-back" title="Regresar al tablero de progreso">
                    <div class="pta-icon-duo btn-icon-duo">
                        <img src="{{ icon('img_back_shadow') }}" class="icon-light" alt="Volver">
                        <img src="{{ icon('img_back') }}" class="icon-color" alt="Volver Color">
                    </div>
                    <span>Volver a Progreso OT</span>
                </a>
            </div>
        </div>

        {{-- Alertas del Sistema --}}
        @if (session('success'))
            <div class="pta-alert pta-alert-success">
                <img src="{{ icon('aprobado') }}" alt="Éxito">
                <span>{{ session('success') }}</span>
            </div>
        @endif
        @if (session('error'))
            <div class="pta-alert pta-alert-error">
                <img src="{{ icon('rechazado') }}" alt="Error">
                <span>{{ session('error') }}</span>
            </div>
        @endif
        @if ($errors->any())
            <div class="pta-alert pta-alert-error">
                <img src="{{ icon('critica') }}" alt="Aviso">
                <ul class="mb-0 ps-3">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- Fila de Selectores (OT, Clase, Pieza) --}}
        <div class="pta-selectors-row">
            {{-- 1. Selector de OT --}}
            <div class="pta-selector-card">
                <div class="pta-selector-header">
                    <div class="selector-icon-badge">
                        <img src="{{ icon('ot_color') }}" class="static-color-icon" alt="OT Color">
                    </div>
                    <div class="selector-title-wrap">
                        <span class="selector-kicker">Parámetro de Orden</span>
                        <label for="ot-select">Orden de Trabajo (OT)</label>
                    </div>
                </div>
                <div class="pta-select-wrap">
                    <select id="ot-select" onchange="changeOT(this.value)">
                        <option value="">— Seleccionar OT —</option>
                        @php $otsVistas = []; @endphp
                        @foreach ($otsConPTA as $otOpt)
                            @if (!in_array($otOpt->id, $otsVistas))
                                <option value="{{ $otOpt->id }}" {{ $ot->id == $otOpt->id ? 'selected' : '' }}>
                                    OT {{ $otOpt->id }}{{ $otOpt->moldura ? ' — ' . $otOpt->moldura->nombre : '' }}
                                </option>
                                @php $otsVistas[] = $otOpt->id; @endphp
                            @endif
                        @endforeach
                    </select>
                </div>
            </div>

            {{-- 2. Selector de Clase --}}
            <div class="pta-selector-card">
                <div class="pta-selector-header">
                    <div class="selector-icon-badge">
                        <img src="{{ icon('clase_color') }}" class="static-color-icon" alt="Clase Color">
                    </div>
                    <div class="selector-title-wrap">
                        <span class="selector-kicker">Modelo / Sección</span>
                        <label for="clase-select">Clase / Modelo</label>
                    </div>
                </div>
                <div class="pta-select-wrap">
                    <select id="clase-select" onchange="changeClase(this.value)" {{ !$ot ? 'disabled' : '' }}>
                        <option value="">— Seleccionar Clase —</option>
                        @if ($ot)
                            @php
                                $otActualObj = $otsConPTA->firstWhere('id', $ot->id);
                            @endphp
                            @if ($otActualObj)
                                @foreach ($otActualObj->clases as $claseOpt)
                                    <option value="{{ $claseOpt->id }}"
                                        {{ $claseSeleccionada->id == $claseOpt->id ? 'selected' : '' }}>
                                        {{ $claseOpt->nombre }}
                                    </option>
                                @endforeach
                            @endif
                        @endif
                    </select>
                </div>
            </div>

            {{-- 3. Selector de Pieza / Juego --}}
            <div class="pta-selector-card">
                <div class="pta-selector-header">
                    <div class="selector-icon-badge">
                        <img src="{{ icon('piezas_color') }}" class="static-color-icon" alt="Pieza Color">
                    </div>
                    <div class="selector-title-wrap">
                        <span class="selector-kicker">Elemento Activo</span>
                        <label
                            for="piece-select">{{ isset($esJuegoCompleto) && $esJuegoCompleto ? 'Juego de Piezas' : 'Pieza Individual' }}</label>
                    </div>
                </div>
                <div class="pta-select-wrap">
                    <select id="piece-select" onchange="changePiece(this.value)"
                        {{ !$claseSeleccionada ? 'disabled' : '' }}>
                        <option value="">— Seleccionar
                            {{ isset($esJuegoCompleto) && $esJuegoCompleto ? 'Juego' : 'Pieza' }} —</option>
                        @foreach ($piezas as $pieza)
                            @php $tieneRes = isset($todosResultados[$pieza->id]); @endphp
                            <option value="{{ $pieza->id }}"
                                {{ $pieza->id == $piezaSeleccionada->id ? 'selected' : '' }}>
                                {{ $pieza->n_pieza }} — {{ $tieneRes ? 'Guardado' : 'Pendiente' }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        {{-- Formulario de Registro de Resultados Técnicos --}}
        <form action="{{ route('pta.results.store', ['ot_id' => $ot->id, 'clase_id' => $claseSeleccionada->id]) }}"
            method="POST" enctype="multipart/form-data" id="pta-form">
            @csrf

            <input type="hidden" name="pieza_id" value="{{ $piezaSeleccionada->id }}">
            <input type="hidden" name="n_pieza" value="{{ $piezaSeleccionada->n_pieza }}">

            <div class="pta-form-card">
                <div class="pta-form-card-header">
                    <div class="pta-form-title-group">
                        <div class="form-header-icon-box">
                            <img src="{{ icon('inspeccion') }}" class="static-color-icon" alt="Inspección Color">
                        </div>
                        <div class="pta-form-header-text">
                            <h3>Resultados Técnicos de Soldadura</h3>
                            <span class="pta-form-header-subtitle">
                                Inspeccionando {{ isset($esJuegoCompleto) && $esJuegoCompleto ? 'Juego' : 'Pieza' }}:
                                <strong class="piece-highlight">{{ $piezaSeleccionada->n_pieza }}</strong>
                            </span>
                        </div>
                    </div>
                    @if (isset($todosResultados[$piezaSeleccionada->id]))
                        <span class="badge-status-edited">
                            <img src="{{ icon('guardado') }}" alt="Guardado">
                            Editando resultado registrado
                        </span>
                    @endif
                </div>

                <div class="pta-form-card-body">

                    {{-- ── ETAPA 1: PICO ── --}}
                    <div class="pta-stage-section pta-stage-pico">
                        <div class="pta-stage-header">
                            <div class="pta-stage-left">
                                <span class="pta-stage-badge badge-pico">FASE 01</span>
                                <div class="stage-icon-badge">
                                    <img src="{{ icon('pico_color') }}" class="static-color-icon" alt="Pico Color">
                                </div>
                                <div class="pta-stage-info">
                                    <h4>Inspección de Zona Pico</h4>
                                    <p>Evaluación de llenado, soldadura y evidencia visual del pico</p>
                                </div>
                            </div>
                        </div>

                        <div class="pta-stage-grid">
                            {{-- Parámetros Técnicos --}}
                            <div class="pta-stage-params">
                                {{-- 1. Resultado Pico Llenado --}}
                                <div class="pta-field-card">
                                    <div class="pta-field-header">
                                        <span class="field-step-badge">1</span>
                                        <label for="res_pico_llenado">Resultado Pico Llenado</label>
                                    </div>
                                    <div class="pta-input-group">
                                        <select name="resultado_pico_llenado" id="res_pico_llenado" required>
                                            <option value="">— Seleccionar —</option>
                                            @foreach (['Si', 'No', 'No Aplica'] as $opt)
                                                <option value="{{ $opt }}"
                                                    {{ old('resultado_pico_llenado', $resultado->resultado_pico_llenado ?? '') == $opt ? 'selected' : '' }}>
                                                    {{ $opt }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                {{-- 2. Resultado Pico Soldadura --}}
                                <div class="pta-field-card">
                                    <div class="pta-field-header">
                                        <span class="field-step-badge">2</span>
                                        <label for="res_pico_sold">Resultado Pico Soldadura</label>
                                    </div>
                                    <div class="pta-input-group">
                                        <select name="resultado_pico_soldadura" id="res_pico_sold" required>
                                            <option value="">— Seleccionar —</option>
                                            @foreach (['Si', 'No', 'No Aplica'] as $opt)
                                                <option value="{{ $opt }}"
                                                    {{ old('resultado_pico_soldadura', $resultado->resultado_pico_soldadura ?? '') == $opt ? 'selected' : '' }}>
                                                    {{ $opt }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>{{-- /pta-stage-params --}}

                            {{-- Evidencia Fotográfica --}}
                            <div class="pta-stage-evidence">
                                <div class="pta-field-card pta-field-evidence">
                                    <div class="pta-field-header pta-evidence-header">
                                        <div class="field-header-left">
                                            <span class="field-step-badge">3</span>
                                            <label>Evidencia Pico Soldadura</label>
                                        </div>
                                        @php $imgPs = $resultado->imagen_pico_soldadura ?? null; @endphp
                                        <span id="badge_status_pico" class="evidence-status-pill {{ $imgPs ? 'status-pill-filled' : 'status-pill-empty' }}">
                                            @if ($imgPs)
                                                <img src="{{ icon('aprobado') }}" alt="OK" class="pill-icon">
                                                <span>Adjunta</span>
                                            @else
                                                <img src="{{ icon('alerta') }}" alt="Pendiente" class="pill-icon">
                                                <span>Sin foto</span>
                                            @endif
                                        </span>
                                    </div>
                                    <div class="pta-evidence-body">
                                        <input type="file" id="img_pico_sold" name="imagen_pico_soldadura"
                                            accept="image/*" class="pta-file-input-hidden"
                                            onchange="handleEvidenceChange(this, 'pico', 'Inspección: Evidencia Pico Soldadura')">

                                        {{-- Estado 1: Dropzone cuando no hay foto --}}
                                        <label for="img_pico_sold" id="dropzone_pico" class="pta-evidence-dropzone" style="display: {{ $imgPs ? 'none' : 'flex' }};">
                                            <div class="dropzone-icon-box">
                                                <div class="pta-icon-duo upload-icon-duo">
                                                    <img src="{{ icon('camara_light') }}" class="icon-light" alt="Cámara Light">
                                                    <img src="{{ icon('camara_color') }}" class="icon-color" alt="Cámara Color">
                                                </div>
                                            </div>
                                            <div class="dropzone-text-group">
                                                <span class="dropzone-main-text">Subir Evidencia</span>
                                                <span class="dropzone-sub-text">Clic para seleccionar foto</span>
                                            </div>
                                        </label>

                                        {{-- Estado 2: Tarjeta visual cuando sí hay foto --}}
                                        <div id="preview_card_pico" class="pta-evidence-loaded-card" style="display: {{ $imgPs ? 'flex' : 'none' }};">
                                            <div class="loaded-thumb-wrapper" onclick="triggerPreviewClick('preview_pico_sold', 'Inspección: Evidencia Pico Soldadura')" title="Clic para ver en pantalla completa">
                                                <img id="preview_pico_sold" src="{{ $imgPs ? asset($imgPs) : '#' }}" alt="Pico" class="loaded-thumb-img">
                                                <div class="loaded-thumb-overlay">
                                                    <img src="{{ icon('lupa') }}" alt="Zoom">
                                                    <span>Ver</span>
                                                </div>
                                            </div>

                                            <div class="loaded-info-col">
                                                <div class="loaded-meta-row">
                                                    <span id="filename_pico" class="loaded-filename" title="{{ $imgPs ? basename($imgPs) : '' }}">
                                                        {{ $imgPs ? basename($imgPs) : '' }}
                                                    </span>
                                                </div>
                                                <div class="loaded-actions-row">
                                                    <button type="button" class="btn-evidence-view" onclick="openPtaModal(document.getElementById('preview_pico_sold').src, 'Inspección: Evidencia Pico Soldadura')" title="Ver en tamaño completo">
                                                        <img src="{{ icon('ojito') }}" alt="Ver">
                                                        <span>Ver</span>
                                                    </button>
                                                    <label for="img_pico_sold" class="btn-evidence-change" title="Cambiar fotografía">
                                                        <img src="{{ icon('camara_color') }}" alt="Cambiar">
                                                        <span>Cambiar</span>
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>{{-- /pta-stage-evidence --}}
                        </div>{{-- /pta-stage-grid --}}
                    </div>{{-- /pta-stage-pico --}}

                    {{-- ── ETAPA 2: CONEXIÓN ── --}}
                    <div class="pta-stage-section pta-stage-conexion">
                        <div class="pta-stage-header">
                            <div class="pta-stage-left">
                                <span class="pta-stage-badge badge-conexion">FASE 02</span>
                                <div class="stage-icon-badge">
                                    <img src="{{ icon('conexion_color') }}" class="static-color-icon"
                                        alt="Conexión Color">
                                </div>
                                <div class="pta-stage-info">
                                    <h4>Inspección de Zona Conexión</h4>
                                    <p>Control de unión, continuidad y soldadura de conexión</p>
                                </div>
                            </div>
                        </div>

                        <div class="pta-stage-grid">
                            {{-- Parámetros Técnicos --}}
                            <div class="pta-stage-params">
                                {{-- 4. Resultado Conexión Llenado --}}
                                <div class="pta-field-card">
                                    <div class="pta-field-header">
                                        <span class="field-step-badge">4</span>
                                        <label for="res_con_llenado">Resultado Conexión Llenado</label>
                                    </div>
                                    <div class="pta-input-group">
                                        <select name="resultado_conexion_llenado" id="res_con_llenado" required>
                                            <option value="">— Seleccionar —</option>
                                            @foreach (['Si', 'No', 'No Aplica'] as $opt)
                                                <option value="{{ $opt }}"
                                                    {{ old('resultado_conexion_llenado', $resultado->resultado_conexion_llenado ?? '') == $opt ? 'selected' : '' }}>
                                                    {{ $opt }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                {{-- 5. Resultado Conexión Soldadura --}}
                                <div class="pta-field-card">
                                    <div class="pta-field-header">
                                        <span class="field-step-badge">5</span>
                                        <label for="res_con_sold">Resultado Conexión Soldadura</label>
                                    </div>
                                    <div class="pta-input-group">
                                        <select name="resultado_conexion_soldadura" id="res_con_sold" required>
                                            <option value="">— Seleccionar —</option>
                                            @foreach (['Si', 'No', 'No Aplica'] as $opt)
                                                <option value="{{ $opt }}"
                                                    {{ old('resultado_conexion_soldadura', $resultado->resultado_conexion_soldadura ?? '') == $opt ? 'selected' : '' }}>
                                                    {{ $opt }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>{{-- /pta-stage-params --}}

                            {{-- Evidencia Fotográfica --}}
                            <div class="pta-stage-evidence">
                                <div class="pta-field-card pta-field-evidence">
                                    <div class="pta-field-header pta-evidence-header">
                                        <div class="field-header-left">
                                            <span class="field-step-badge">6</span>
                                            <label>Evidencia Conexión Soldadura</label>
                                        </div>
                                        @php $imgCs = $resultado->imagen_conexion_soldadura ?? null; @endphp
                                        <span id="badge_status_con" class="evidence-status-pill {{ $imgCs ? 'status-pill-filled' : 'status-pill-empty' }}">
                                            @if ($imgCs)
                                                <img src="{{ icon('aprobado') }}" alt="OK" class="pill-icon">
                                                <span>Adjunta</span>
                                            @else
                                                <img src="{{ icon('alerta') }}" alt="Pendiente" class="pill-icon">
                                                <span>Sin foto</span>
                                            @endif
                                        </span>
                                    </div>
                                    <div class="pta-evidence-body">
                                        <input type="file" id="img_con_sold" name="imagen_conexion_soldadura"
                                            accept="image/*" class="pta-file-input-hidden"
                                            onchange="handleEvidenceChange(this, 'con', 'Inspección: Evidencia Conexión Soldadura')">

                                        {{-- Estado 1: Dropzone cuando no hay foto --}}
                                        <label for="img_con_sold" id="dropzone_con" class="pta-evidence-dropzone" style="display: {{ $imgCs ? 'none' : 'flex' }};">
                                            <div class="dropzone-icon-box">
                                                <div class="pta-icon-duo upload-icon-duo">
                                                    <img src="{{ icon('camara_light') }}" class="icon-light" alt="Cámara Light">
                                                    <img src="{{ icon('camara_color') }}" class="icon-color" alt="Cámara Color">
                                                </div>
                                            </div>
                                            <div class="dropzone-text-group">
                                                <span class="dropzone-main-text">Subir Evidencia</span>
                                                <span class="dropzone-sub-text">Clic para seleccionar foto</span>
                                            </div>
                                        </label>

                                        {{-- Estado 2: Tarjeta visual cuando sí hay foto --}}
                                        <div id="preview_card_con" class="pta-evidence-loaded-card" style="display: {{ $imgCs ? 'flex' : 'none' }};">
                                            <div class="loaded-thumb-wrapper" onclick="triggerPreviewClick('preview_con_sold', 'Inspección: Evidencia Conexión Soldadura')" title="Clic para ver en pantalla completa">
                                                <img id="preview_con_sold" src="{{ $imgCs ? asset($imgCs) : '#' }}" alt="Conexión" class="loaded-thumb-img">
                                                <div class="loaded-thumb-overlay">
                                                    <img src="{{ icon('lupa') }}" alt="Zoom">
                                                    <span>Ver</span>
                                                </div>
                                            </div>

                                            <div class="loaded-info-col">
                                                <div class="loaded-meta-row">
                                                    <span id="filename_con" class="loaded-filename" title="{{ $imgCs ? basename($imgCs) : '' }}">
                                                        {{ $imgCs ? basename($imgCs) : '' }}
                                                    </span>
                                                </div>
                                                <div class="loaded-actions-row">
                                                    <button type="button" class="btn-evidence-view" onclick="openPtaModal(document.getElementById('preview_con_sold').src, 'Inspección: Evidencia Conexión Soldadura')" title="Ver en tamaño completo">
                                                        <img src="{{ icon('ojito') }}" alt="Ver">
                                                        <span>Ver</span>
                                                    </button>
                                                    <label for="img_con_sold" class="btn-evidence-change" title="Cambiar fotografía">
                                                        <img src="{{ icon('camara_color') }}" alt="Cambiar">
                                                        <span>Cambiar</span>
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>{{-- /pta-stage-evidence --}}
                        </div>{{-- /pta-stage-grid --}}
                    </div>{{-- /pta-stage-conexion --}}

                    {{-- ── ETAPA 3: PERFILADO ── --}}
                    <div class="pta-stage-section pta-stage-perfilado">
                        <div class="pta-stage-header">
                            <div class="pta-stage-left">
                                <span class="pta-stage-badge badge-perfilado">FASE 03</span>
                                <div class="stage-icon-badge">
                                    <img src="{{ icon('perfilado_color') }}" class="static-color-icon"
                                        alt="Perfilado Color">
                                </div>
                                <div class="pta-stage-info">
                                    <h4>Inspección de Zona Perfilado</h4>
                                    <p>Verificación de perfilado de llenado y acabado de soldadura</p>
                                </div>
                            </div>
                        </div>

                        <div class="pta-stage-grid">
                            {{-- Parámetros Técnicos --}}
                            <div class="pta-stage-params">
                                {{-- 7. Resultado Perfilado Llenado --}}
                                <div class="pta-field-card">
                                    <div class="pta-field-header">
                                        <span class="field-step-badge">7</span>
                                        <label for="res_perf_llenado">Resultado Perfilado Llenado</label>
                                    </div>
                                    <div class="pta-input-group">
                                        <select name="resultado_perfilado_llenado" id="res_perf_llenado" required>
                                            <option value="">— Seleccionar —</option>
                                            @foreach (['Si', 'No', 'No Aplica'] as $opt)
                                                <option value="{{ $opt }}"
                                                    {{ old('resultado_perfilado_llenado', $resultado->resultado_perfilado_llenado ?? '') == $opt ? 'selected' : '' }}>
                                                    {{ $opt }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                {{-- 8. Resultado Perfilado Soldadura --}}
                                <div class="pta-field-card">
                                    <div class="pta-field-header">
                                        <span class="field-step-badge">8</span>
                                        <label for="res_perf_sold">Resultado Perfilado Soldadura</label>
                                    </div>
                                    <div class="pta-input-group">
                                        <select name="resultado_perfilado_soldadura" id="res_perf_sold" required>
                                            <option value="">— Seleccionar —</option>
                                            @foreach (['Si', 'No', 'No Aplica'] as $opt)
                                                <option value="{{ $opt }}"
                                                    {{ old('resultado_perfilado_soldadura', $resultado->resultado_perfilado_soldadura ?? '') == $opt ? 'selected' : '' }}>
                                                    {{ $opt }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>{{-- /pta-stage-params --}}

                            {{-- Evidencia Fotográfica --}}
                            <div class="pta-stage-evidence">
                                <div class="pta-field-card pta-field-evidence">
                                    <div class="pta-field-header pta-evidence-header">
                                        <div class="field-header-left">
                                            <span class="field-step-badge">9</span>
                                            <label>Evidencia Perfilado Soldadura</label>
                                        </div>
                                        @php $imgPs2 = $resultado->imagen_perfilado_soldadura ?? null; @endphp
                                        <span id="badge_status_perf" class="evidence-status-pill {{ $imgPs2 ? 'status-pill-filled' : 'status-pill-empty' }}">
                                            @if ($imgPs2)
                                                <img src="{{ icon('aprobado') }}" alt="OK" class="pill-icon">
                                                <span>Adjunta</span>
                                            @else
                                                <img src="{{ icon('alerta') }}" alt="Pendiente" class="pill-icon">
                                                <span>Sin foto</span>
                                            @endif
                                        </span>
                                    </div>
                                    <div class="pta-evidence-body">
                                        <input type="file" id="img_perf_sold" name="imagen_perfilado_soldadura"
                                            accept="image/*" class="pta-file-input-hidden"
                                            onchange="handleEvidenceChange(this, 'perf', 'Inspección: Evidencia Perfilado Soldadura')">

                                        {{-- Estado 1: Dropzone cuando no hay foto --}}
                                        <label for="img_perf_sold" id="dropzone_perf" class="pta-evidence-dropzone" style="display: {{ $imgPs2 ? 'none' : 'flex' }};">
                                            <div class="dropzone-icon-box">
                                                <div class="pta-icon-duo upload-icon-duo">
                                                    <img src="{{ icon('camara_light') }}" class="icon-light" alt="Cámara Light">
                                                    <img src="{{ icon('camara_color') }}" class="icon-color" alt="Cámara Color">
                                                </div>
                                            </div>
                                            <div class="dropzone-text-group">
                                                <span class="dropzone-main-text">Subir Evidencia</span>
                                                <span class="dropzone-sub-text">Clic para seleccionar foto</span>
                                            </div>
                                        </label>

                                        {{-- Estado 2: Tarjeta visual cuando sí hay foto --}}
                                        <div id="preview_card_perf" class="pta-evidence-loaded-card" style="display: {{ $imgPs2 ? 'flex' : 'none' }};">
                                            <div class="loaded-thumb-wrapper" onclick="triggerPreviewClick('preview_perf_sold', 'Inspección: Evidencia Perfilado Soldadura')" title="Clic para ver en pantalla completa">
                                                <img id="preview_perf_sold" src="{{ $imgPs2 ? asset($imgPs2) : '#' }}" alt="Perfilado" class="loaded-thumb-img">
                                                <div class="loaded-thumb-overlay">
                                                    <img src="{{ icon('lupa') }}" alt="Zoom">
                                                    <span>Ver</span>
                                                </div>
                                            </div>

                                            <div class="loaded-info-col">
                                                <div class="loaded-meta-row">
                                                    <span id="filename_perf" class="loaded-filename" title="{{ $imgPs2 ? basename($imgPs2) : '' }}">
                                                        {{ $imgPs2 ? basename($imgPs2) : '' }}
                                                    </span>
                                                </div>
                                                <div class="loaded-actions-row">
                                                    <button type="button" class="btn-evidence-view" onclick="openPtaModal(document.getElementById('preview_perf_sold').src, 'Inspección: Evidencia Perfilado Soldadura')" title="Ver en tamaño completo">
                                                        <img src="{{ icon('ojito') }}" alt="Ver">
                                                        <span>Ver</span>
                                                    </button>
                                                    <label for="img_perf_sold" class="btn-evidence-change" title="Cambiar fotografía">
                                                        <img src="{{ icon('camara_color') }}" alt="Cambiar">
                                                        <span>Cambiar</span>
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>{{-- /pta-stage-evidence --}}
                        </div>{{-- /pta-stage-grid --}}
                    </div>{{-- /pta-stage-perfilado --}}

                </div>{{-- /card-body --}}

                <div class="pta-form-card-footer">
                    <button type="submit" class="btn-pta-save" id="btn-guardar">
                        <img src="{{ icon('guardar_light') }}" class="save-power-icon" alt="Guardar Resultados">
                        <span>Guardar Resultados</span>
                    </button>
                </div>
            </div>{{-- /pta-form-card --}}

        </form>

        {{-- Tabla Resumen de Piezas / Juegos --}}
        <div class="pta-overview-wrap">
            <div class="pta-overview-header">
                <div class="pta-overview-header-left">
                    <div class="table-icon-badge">
                        <img src="{{ icon('piezas_color') }}" class="static-color-icon" alt="Resumen de Piezas">
                    </div>
                    <div class="table-title-group">
                        <h4>Resumen de {{ isset($esJuegoCompleto) && $esJuegoCompleto ? 'Juegos' : 'Piezas' }}</h4>
                        <span class="table-subtitle">OT {{ $ot->id }} / {{ $claseSeleccionada->nombre }}</span>
                    </div>
                </div>
                <div class="pta-overview-header-right">
                    <span class="badge-total-pieces">{{ count($piezas) }} registros</span>
                </div>
            </div>

            <div class="pta-table-container">
                <table class="pta-table">
                    <thead>
                        <tr class="pta-th-group-row">
                            <th rowspan="2" class="th-col-piece">{{ isset($esJuegoCompleto) && $esJuegoCompleto ? 'Juego' : 'Pieza' }}</th>
                            <th colspan="2" class="th-group-phase th-group-pico">
                                <span class="th-phase-tag">Fase 01</span>
                                <span class="th-phase-title">Pico</span>
                            </th>
                            <th colspan="2" class="th-group-phase th-group-conexion">
                                <span class="th-phase-tag">Fase 02</span>
                                <span class="th-phase-title">Conexión</span>
                            </th>
                            <th colspan="2" class="th-group-phase th-group-perfilado">
                                <span class="th-phase-tag">Fase 03</span>
                                <span class="th-phase-title">Perfilado</span>
                            </th>
                            <th rowspan="2" class="th-col-status">Estado</th>
                        </tr>
                        <tr class="pta-th-sub-row">
                            <th class="th-sub-col th-sub-pico">Llenado</th>
                            <th class="th-sub-col th-sub-pico th-sub-boundary">Soldadura</th>
                            <th class="th-sub-col th-sub-conexion">Llenado</th>
                            <th class="th-sub-col th-sub-conexion th-sub-boundary">Soldadura</th>
                            <th class="th-sub-col th-sub-perfilado">Llenado</th>
                            <th class="th-sub-col th-sub-perfilado th-sub-boundary">Soldadura</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($piezas as $pieza)
                            @php $res = $todosResultados->get($pieza->id); @endphp
                            <tr class="{{ $pieza->id == $piezaSeleccionada->id ? 'row-selected' : '' }}"
                                onclick="changePiece({{ $pieza->id }})"
                                title="Clic para seleccionar y editar la pieza {{ $pieza->n_pieza }}">
                                <td class="td-col-piece">
                                    <div class="piece-cell">
                                        <span class="piece-bullet"></span>
                                        <strong>{{ $pieza->n_pieza }}</strong>
                                    </div>
                                </td>

                                @php
                                    $columnasFases = [
                                        ['val' => $res->resultado_pico_llenado ?? null, 'phase' => 'pico', 'boundary' => false],
                                        ['val' => $res->resultado_pico_soldadura ?? null, 'phase' => 'pico', 'boundary' => true],
                                        ['val' => $res->resultado_conexion_llenado ?? null, 'phase' => 'conexion', 'boundary' => false],
                                        ['val' => $res->resultado_conexion_soldadura ?? null, 'phase' => 'conexion', 'boundary' => true],
                                        ['val' => $res->resultado_perfilado_llenado ?? null, 'phase' => 'perfilado', 'boundary' => false],
                                        ['val' => $res->resultado_perfilado_soldadura ?? null, 'phase' => 'perfilado', 'boundary' => true],
                                    ];
                                @endphp
                                @foreach ($columnasFases as $col)
                                    <td class="td-phase-col td-phase-{{ $col['phase'] }}{{ $col['boundary'] ? ' td-phase-boundary' : '' }}">
                                        @if ($col['val'] === 'Si')
                                            <span class="pta-badge badge-si">✔ Sí</span>
                                        @elseif ($col['val'] === 'No')
                                            <span class="pta-badge badge-no">✖ No</span>
                                        @elseif ($col['val'] === 'No Aplica')
                                            <span class="pta-badge badge-na">N/A</span>
                                        @else
                                            <span class="pta-badge badge-empty">—</span>
                                        @endif
                                    </td>
                                @endforeach

                                <td class="td-col-status">
                                    @if ($res && $res->liberado_por_admin)
                                        <span class="pta-badge badge-liberada">✓ Liberada</span>
                                    @elseif ($res)
                                        <span class="pta-badge badge-guardada">Guardada</span>
                                    @else
                                        <span class="pta-badge badge-pendiente">Pendiente</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Modal Visor de Inspección de Evidencia (Lightbox en Tiempo Real) --}}
        <div id="pta-image-modal" class="pta-lightbox-modal" style="display: none;" onclick="closePtaModal(event)">
            <div class="pta-lightbox-card" onclick="event.stopPropagation()">
                <div class="pta-lightbox-header">
                    <div class="pta-lightbox-title">
                        <img src="{{ icon('ojito') }}" alt="Ojo">
                        <span id="pta-modal-caption">Inspección de Evidencia Fotográfica</span>
                    </div>
                    <button type="button" class="pta-lightbox-close" onclick="closePtaModal()" title="Cerrar (Esc)">
                        ✕
                    </button>
                </div>
                <div class="pta-lightbox-body">
                    <img id="pta-modal-img" src="#" alt="Evidencia en Alta Resolución">
                </div>
            </div>
        </div>

    </div>

    <script>
        function handleEvidenceChange(input, stageKey, caption) {
            const file = input.files && input.files[0];
            const dropzone = document.getElementById(`dropzone_${stageKey}`);
            const previewCard = document.getElementById(`preview_card_${stageKey}`);
            const previewImg = document.getElementById(`preview_${stageKey}_sold`);
            const fileNameEl = document.getElementById(`filename_${stageKey}`);
            const badgeStatus = document.getElementById(`badge_status_${stageKey}`);

            const iconAprobado = "{{ icon('aprobado') }}";
            const iconAlerta = "{{ icon('alerta') }}";

            if (file) {
                if (fileNameEl) {
                    fileNameEl.textContent = file.name;
                    fileNameEl.title = file.name;
                }
                if (badgeStatus) {
                    badgeStatus.innerHTML = `<img src="${iconAprobado}" alt="OK" class="pill-icon"> <span>Adjunta</span>`;
                    badgeStatus.className = 'evidence-status-pill status-pill-filled';
                }
                const reader = new FileReader();
                reader.onload = function(e) {
                    if (previewImg) {
                        previewImg.src = e.target.result;
                    }
                    if (dropzone) dropzone.style.display = 'none';
                    if (previewCard) previewCard.style.display = 'flex';
                };
                reader.readAsDataURL(file);
            } else {
                if (badgeStatus) {
                    badgeStatus.innerHTML = `<img src="${iconAlerta}" alt="Sin foto" class="pill-icon"> <span>Sin foto</span>`;
                    badgeStatus.className = 'evidence-status-pill status-pill-empty';
                }
                if (dropzone) dropzone.style.display = 'flex';
                if (previewCard) previewCard.style.display = 'none';
                if (previewImg) previewImg.src = '#';
            }
        }

        function updateFileLabel(input, statusId, previewId, thumbBoxId, btnLblId, btnViewId) {
            const el = document.getElementById(statusId);
            const previewImg = previewId ? document.getElementById(previewId) : null;
            const thumbBox = thumbBoxId ? document.getElementById(thumbBoxId) : null;
            const btnLbl = btnLblId ? document.getElementById(btnLblId) : null;
            const btnView = btnViewId ? document.getElementById(btnViewId) : null;
            const placeholder = thumbBox ? thumbBox.querySelector('.thumb-empty-placeholder') : null;

            if (input.files && input.files.length > 0) {
                const file = input.files[0];
                const checkIcon = "{{ icon('aprobado') }}";
                if (el) {
                    el.innerHTML = `<img src="${checkIcon}" alt="OK" class="status-icon"> <span class="file-name-text">${file.name}</span>`;
                    el.className = 'pta-img-status filled';
                }

                if (btnLbl) btnLbl.textContent = 'Cambiar foto';

                if (previewImg && thumbBox) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        previewImg.src = e.target.result;
                        previewImg.style.display = 'block';
                        if (placeholder) placeholder.style.display = 'none';
                        thumbBox.classList.add('is-active');
                        if (btnView) btnView.style.display = 'inline-flex';
                    };
                    reader.readAsDataURL(file);
                }
            } else {
                const alertIcon = "{{ icon('alerta') }}";
                if (el) {
                    el.innerHTML = `<img src="${alertIcon}" alt="Vacío" class="status-icon"> <span>Sin foto adjunta</span>`;
                    el.className = 'pta-img-status empty';
                }

                if (btnLbl) btnLbl.textContent = 'Subir imagen';

                if (previewImg && thumbBox) {
                    previewImg.src = '#';
                    previewImg.style.display = 'none';
                    if (placeholder) placeholder.style.display = 'flex';
                    thumbBox.classList.remove('is-active');
                    if (btnView) btnView.style.display = 'none';
                }
            }
        }

        function triggerPreviewClick(previewId, caption) {
            const img = document.getElementById(previewId);
            if (img && img.src && img.src !== '#' && !img.src.endsWith('#')) {
                openPtaModal(img.src, caption);
            }
        }

        function openPtaModal(src, caption) {
            const modal = document.getElementById('pta-image-modal');
            const modalImg = document.getElementById('pta-modal-img');
            const modalCap = document.getElementById('pta-modal-caption');
            if (modal && modalImg && src && src !== '#') {
                modalImg.src = src;
                if (modalCap && caption) modalCap.textContent = caption;
                modal.style.display = 'flex';
                document.body.style.overflow = 'hidden';
            }
        }

        function closePtaModal() {
            const modal = document.getElementById('pta-image-modal');
            if (modal) {
                modal.style.display = 'none';
                document.body.style.overflow = '';
            }
        }

        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') closePtaModal();
        });

        function changePiece(piezaId) {
            const url = new URL(window.location.href);
            if (piezaId) {
                url.searchParams.set('pieza_id', piezaId);
            } else {
                url.searchParams.delete('pieza_id');
            }
            window.location.href = url.toString();
        }

        function changeOT(otId) {
            if (otId) {
                window.location.href = `{{ url('admin/pta/results') }}/${otId}`;
            }
        }

        function changeClase(claseId) {
            const url = new URL(window.location.href);
            if (claseId) {
                url.searchParams.set('clase_id', claseId);
                url.searchParams.delete('pieza_id');
                window.location.href = url.toString();
            }
        }

        // Auto-dismiss alertas (4 s)
        document.querySelectorAll('.pta-alert').forEach((el) => {
            setTimeout(() => {
                el.classList.add('fade-out');
                el.addEventListener('transitionend', () => el.remove(), {
                    once: true
                });
            }, 4000);
        });
    </script>
@endsection
