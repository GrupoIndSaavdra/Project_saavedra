@extends('layouts.appMenu')

@section('head')
    <title>Reporte Dimensional OT #{{ $otData->id }} — {{ $clase }} | GIS</title>
    <meta name="description" content="Reporte de inspección dimensional de piezas. Formato oficial 4CAL-02. Grupo Industrial Saavedra.">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @vite([
        'resources/css/calidad_views/inspeccion_dimensional.css',
        'resources/js/calidad_views/inspeccion_dimensional.js'
    ])
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
@endsection

@section('background-body', 'background-image:url("' . asset('images/fondoLogin.jpg') . '")')

@section('content')

<div class="alm-wrapper rd-page-wrapper">

    {{-- ═══ BARRA SUPERIOR DE ACCIONES ═══ --}}
    <div class="rd-top-bar">
        <div class="rd-top-left">
            <a href="{{ route('calidad.inspeccion_dimensional.index') }}" class="btn-regresar">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline></svg>
                Regresar al Selector
            </a>
            <span class="rd-title-tag">
                <img src="{{ asset('images/reporte dimensional.png') }}" width="18" height="18" alt="Reporte Dimensional">
                <span>Reporte Dimensional</span>
            </span>
            <span class="rd-ot-pill">OT #{{ $otData->id }} — {{ $clase }}</span>
        </div>
        <div class="rd-top-right">
            <div class="rd-autosave-indicator" id="autosave-status">
                <span class="rd-status-dot"></span>
                <span class="rd-status-text">Cambios guardados</span>
            </div>
        </div>
    </div>

    {{-- ═══ FORMATO INSTITUCIONAL DE CONTROL DIMENSIONAL (4CAL-02) ═══ --}}
    <div class="rd-sheet-container">

        {{-- ── Encabezado Oficial (No editable - Control de Calidad SGC) ── --}}
        <div class="rd-doc-header">
            <div class="rd-doc-title-row">
                <div class="rd-doc-main-title">
                    REPORTE DE INSPECCIÓN PARA {{ strtoupper($clase) }} (Reporte Dimensional)
                </div>
                <div class="rd-doc-logo">
                    <img src="{{ asset('images/lg_saavedra.png') }}" alt="Grupo Industrial Saavedra" class="rd-logo-img">
                </div>
            </div>

            <div class="rd-doc-meta-grid">
                <div class="rd-meta-col">
                    <span class="rd-meta-label">Fecha de Elaboración</span>
                    <span class="rd-meta-text-val">19-nov-25</span>
                </div>
                <div class="rd-meta-col">
                    <span class="rd-meta-label">Fecha de Revisión</span>
                    <span class="rd-meta-text-val">19-nov-25</span>
                </div>
                <div class="rd-meta-col">
                    <span class="rd-meta-label">Fecha de Aprobación</span>
                    <span class="rd-meta-text-val">20-nov-25</span>
                </div>
                <div class="rd-meta-col rd-meta-col-sm">
                    <span class="rd-meta-label">Nivel de Revisión</span>
                    <span class="rd-meta-text-val">0</span>
                </div>
                <div class="rd-meta-col rd-meta-col-code">
                    <span class="rd-meta-label">Código</span>
                    <span class="rd-meta-code-val">4CAL-02</span>
                </div>
            </div>
        </div>

        {{-- ── Información General y de Entrega ── --}}
        <div class="rd-info-section">
            <div class="rd-info-block">
                <div class="rd-block-heading">INFORMACIÓN GENERAL</div>
                <div class="rd-info-grid">
                    <div class="rd-info-cell">
                        <span class="rd-lbl">FECHA:</span>
                        <span class="rd-val">{{ $reporte->fecha_elaboracion ? $reporte->fecha_elaboracion->format('d-M-y') : now()->format('d-M-y') }}</span>
                    </div>
                    <div class="rd-info-cell">
                        <span class="rd-lbl">CLIENTE:</span>
                        <span class="rd-val">{{ $reporte->cliente ?? '—' }}</span>
                    </div>
                    <div class="rd-info-cell">
                        <span class="rd-lbl">OT:</span>
                        <span class="rd-val font-bold text-blue">{{ $reporte->ot_id }}</span>
                    </div>
                    <div class="rd-info-cell">
                        <span class="rd-lbl">MOLDURA:</span>
                        <span class="rd-val">{{ $reporte->nombre_moldura ?? '—' }}</span>
                    </div>
                </div>
            </div>

            <div class="rd-info-block">
                <div class="rd-block-heading">INFORMACIÓN DE ENTREGA Y MUESTREO</div>
                <div class="rd-info-grid">
                    <div class="rd-info-cell">
                        <span class="rd-lbl">PEDIDO:</span>
                        <span class="rd-val font-bold text-blue">{{ $pedido }} pzas</span>
                    </div>
                    <div class="rd-info-cell">
                        <span class="rd-lbl">CONSIGNACIÓN:</span>
                        <span class="rd-val font-bold text-amber">
                            @if($consignacion > 0)
                                +{{ $consignacion }} pzas
                            @else
                                0 pzas
                            @endif
                        </span>
                    </div>
                    <div class="rd-info-cell">
                        <span class="rd-lbl">TOTAL LOTE:</span>
                        <span class="rd-val font-bold">{{ $totalPiezas }} pzas</span>
                    </div>
                    <div class="rd-info-cell">
                        <span class="rd-lbl">INSPECCIONADO:</span>
                        <span class="rd-val font-bold text-emerald">
                            <span id="display-cant-inspeccionada">{{ $piezasCompletas }}</span> pzas completas
                            (<span id="display-porcentaje-inspeccion">{{ $reporte->porcentaje_inspeccion }}</span>%)
                        </span>
                    </div>
                </div>
            </div>
        </div>

        {{-- ── SECCIÓN MODALIDAD: SUBIR ARCHIVO EXCEL DE MÁQUINA ── --}}
        <div class="rd-excel-box">
            <div class="rd-excel-box-header">
                <div class="rd-excel-header-left">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#10b981" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>
                    <div>
                        <h3 class="rd-excel-title">Documentos / Reportes de Medición Adjuntos (PDF o Excel)</h3>
                        <p class="rd-excel-subtitle">Puedes adjuntar hasta 4 reportes en formato PDF o Excel de la máquina de medición o respaldo.</p>
                    </div>
                </div>
                <div class="rd-excel-actions">
                    <form id="form-upload-excel" enctype="multipart/form-data" class="rd-upload-form">
                        @csrf
                        <input type="hidden" name="reporte_id" value="{{ $reporte->id }}">
                        <input type="file" id="input-excel-file" name="archivo_excel" accept=".pdf,.xlsx,.xls,.csv,.txt" style="display:none;">
                        <button type="button" id="btn-trigger-upload" class="btn-excel-upload" {{ count($reporte->archivos_list) >= 4 ? 'disabled' : '' }}>
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="17 8 12 3 7 8"></polyline><line x1="12" y1="3" x2="12" y2="15"></line></svg>
                            <span>Subir Archivo (PDF / Excel)</span>
                            <span id="excel-count-badge" class="rd-badge-counter">({{ count($reporte->archivos_list) }}/4)</span>
                        </button>
                    </form>
                </div>
            </div>

            {{-- Lista de archivos adjuntos --}}
            <div id="excel-files-container" class="rd-excel-files-container">
                @forelse($reporte->archivos_list as $archivo)
                    @php
                        $ext = strtolower(pathinfo($archivo['nombre'] ?? '', PATHINFO_EXTENSION));
                        $isExcel = in_array($ext, ['xlsx', 'xls', 'csv']);
                        $usuario = !empty($archivo['usuario']) ? $archivo['usuario'] : ($reporte->inspector ? (explode(' ', trim($reporte->inspector->nombre))[0] . ' ' . trim($reporte->inspector->a_paterno ?? '')) : '');
                        $viewUrl = route('calidad.inspeccion_dimensional.view_excel', ['id' => $reporte->id, 'file_id' => $archivo['id']]);
                        $downloadUrl = route('calidad.inspeccion_dimensional.download_excel', ['id' => $reporte->id, 'file_id' => $archivo['id']]);
                    @endphp
                    <div class="rd-excel-file-card" data-file-id="{{ $archivo['id'] }}">
                        <div class="rd-file-info">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#22c55e" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline></svg>
                            <span class="rd-file-name" title="{{ $archivo['nombre'] }}">{{ $archivo['nombre'] }}</span>
                            @if(!empty($archivo['fecha']))
                                <span class="rd-file-date">{{ $archivo['fecha'] }}</span>
                            @endif
                            @if(!empty($usuario))
                                <span class="rd-file-user" title="Subido por: {{ $usuario }}">
                                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
                                    {{ $usuario }}
                                </span>
                            @endif
                            <span class="rd-file-badge">Archivo guardado</span>
                        </div>
                        <div class="rd-file-btn-group">
                            <button type="button" class="btn-file-view btn-preview-file {{ $isExcel ? 'btn-open-excel' : '' }}" 
                                data-file-id="{{ $archivo['id'] }}" 
                                data-file-name="{{ $archivo['nombre'] }}" 
                                data-is-excel="{{ $isExcel ? 'true' : 'false' }}" 
                                data-view-url="{{ $viewUrl }}" 
                                data-download-url="{{ $downloadUrl }}" 
                                title="{{ $isExcel ? 'Ver hoja de cálculo interactiva' : 'Ver archivo PDF' }}">
                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                                Ver
                            </button>
                            <a href="{{ $downloadUrl }}" class="btn-file-download" target="_blank" title="Descargar archivo">
                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
                                Descargar
                            </a>
                            <button type="button" class="btn-file-delete btn-delete-excel-file" data-file-id="{{ $archivo['id'] }}" data-file-name="{{ $archivo['nombre'] }}" title="Eliminar archivo">
                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
                                Eliminar
                            </button>
                        </div>
                    </div>
                @empty
                    {{-- Vacío inicialmente si no hay archivos --}}
                @endforelse
            </div>
        </div>

        {{-- ── CUERPO PRINCIPAL: TABLA DE COTAS Y DIAGRAMA LATERAL ── --}}
        <div class="rd-inspection-body">

            <div class="rd-inspection-left-col">
                {{-- ── TABLA DE CRITERIO DE CALIDAD (MUESTREO) ── --}}
                <div class="rd-criterio-table-card">
                    <div class="rd-criterio-table-header">
                        <div class="rd-criterio-title-left">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="16" x2="12" y2="12"></line><line x1="12" y1="8" x2="12.01" y2="8"></line></svg>
                            <span class="rd-criterio-table-title">CRITERIO DE CALIDAD — MUESTREO DE REVISIÓN</span>
                        </div>
                        <div class="rd-criterio-current-badge">
                            Lote actual: <strong>{{ $totalPiezas }} pzas</strong> &rarr; Muestreo requerido: <strong class="text-highlight">{{ $rangoMuestreo['min'] }} a {{ $rangoMuestreo['max'] }} pzas</strong>
                        </div>
                    </div>
                    <div class="rd-criterio-table-body">
                        <table class="rd-criterio-tbl">
                            <thead>
                                <tr>
                                    <th style="width: 22%;">PIEZAS</th>
                                    <th style="width: 26%;">% DE REVISIÓN</th>
                                    <th>MUESTREO SUGERIDO PARA ESTE LOTE</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr class="{{ $totalPiezas <= 50 ? 'tr-criterio-active' : '' }}">
                                    <td class="font-bold text-center">1 – 50 pzas</td>
                                    <td class="font-bold text-center text-blue">50% AL 60%</td>
                                    <td>
                                        @if($totalPiezas <= 50)
                                            <span class="criterio-pill-active">
                                                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg>
                                                Criterio Aplicable: <strong>{{ $rangoMuestreo['min'] }} a {{ $rangoMuestreo['max'] }} pzas</strong> (<span class="display-criterio-pct">{{ $reporte->porcentaje_inspeccion }}</span>% actual)
                                            </span>
                                        @else
                                            <span class="text-gray-muted">—</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr class="{{ $totalPiezas > 50 ? 'tr-criterio-active' : '' }}">
                                    <td class="font-bold text-center">51 – 128+ pzas</td>
                                    <td class="font-bold text-center text-blue">30% AL 35%</td>
                                    <td>
                                        @if($totalPiezas > 50)
                                            <span class="criterio-pill-active">
                                                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg>
                                                Criterio Aplicable: <strong>{{ $rangoMuestreo['min'] }} a {{ $rangoMuestreo['max'] }} pzas</strong> (<span class="display-criterio-pct">{{ $reporte->porcentaje_inspeccion }}</span>% actual)
                                            </span>
                                        @else
                                            <span class="text-gray-muted">—</span>
                                        @endif
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- ─ Tabla de Inspección Final de Moldura ─ --}}
                <div class="rd-table-wrapper">
                <div class="rd-table-bar">
                    <h3 class="rd-table-title">REPORTE DE INSPECCIÓN FINAL DE MOLDURA</h3>
                    <div class="rd-table-actions">
                        <button type="button" id="btn-add-row" class="btn-add-row">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                            Agregar Fila
                        </button>
                    </div>
                </div>

                <div class="rd-table-scroll">
                    <table class="rd-table" id="tabla-medidas">
                        <thead>
                            <tr class="tr-main-headers">
                                <th class="th-num">NUM</th>
                                <th>C1</th>
                                <th>C2</th>
                                <th>CUELLO ",500"</th>
                                <th>E</th>
                                <th>D</th>
                                <th>F1</th>
                                <th>A. TOTAL</th>
                                <th>F</th>
                                <th>ALTURA</th>
                                <th>ALTURA 2</th>
                                <th>AL CUELLO</th>
                                <th class="th-obs">OBSERVACIONES</th>
                                <th class="th-del"></th>
                            </tr>
                            {{-- Fila de Valores Nominales / Especificación --}}
                            <tr class="tr-spec tr-nominales">
                                <td class="td-num text-center">
                                    <span class="rd-spec-tag rd-tag-nom" title="Valores Nominales">NOM</span>
                                </td>
                                <td><input type="text" class="rd-cell-input spec-editable input-decimal-only font-bold text-center" inputmode="decimal" data-tipo="nominal" data-cota="c1" value="{{ $reporte->valores_nominales['c1'] ?? '' }}" placeholder="Nom"></td>
                                <td><input type="text" class="rd-cell-input spec-editable input-decimal-only font-bold text-center" inputmode="decimal" data-tipo="nominal" data-cota="c2" value="{{ $reporte->valores_nominales['c2'] ?? '' }}" placeholder="Nom"></td>
                                <td><input type="text" class="rd-cell-input spec-editable input-decimal-only font-bold text-center" inputmode="decimal" data-tipo="nominal" data-cota="cuello" value="{{ $reporte->valores_nominales['cuello'] ?? '' }}" placeholder="Nom"></td>
                                <td><input type="text" class="rd-cell-input spec-editable input-decimal-only font-bold text-center" inputmode="decimal" data-tipo="nominal" data-cota="e" value="{{ $reporte->valores_nominales['e'] ?? '' }}" placeholder="Nom"></td>
                                <td><input type="text" class="rd-cell-input spec-editable input-decimal-only font-bold text-center" inputmode="decimal" data-tipo="nominal" data-cota="d" value="{{ $reporte->valores_nominales['d'] ?? '' }}" placeholder="Nom"></td>
                                <td><input type="text" class="rd-cell-input spec-editable input-decimal-only font-bold text-center" inputmode="decimal" data-tipo="nominal" data-cota="f1" value="{{ $reporte->valores_nominales['f1'] ?? '' }}" placeholder="Nom"></td>
                                <td><input type="text" class="rd-cell-input spec-editable input-decimal-only font-bold text-center" inputmode="decimal" data-tipo="nominal" data-cota="a_total" value="{{ $reporte->valores_nominales['a_total'] ?? '' }}" placeholder="Nom"></td>
                                <td><input type="text" class="rd-cell-input spec-editable input-decimal-only font-bold text-center" inputmode="decimal" data-tipo="nominal" data-cota="f" value="{{ $reporte->valores_nominales['f'] ?? '' }}" placeholder="Nom"></td>
                                <td><input type="text" class="rd-cell-input spec-editable input-decimal-only font-bold text-center" inputmode="decimal" data-tipo="nominal" data-cota="altura" value="{{ $reporte->valores_nominales['altura'] ?? '' }}" placeholder="Nom"></td>
                                <td><input type="text" class="rd-cell-input spec-editable input-decimal-only font-bold text-center" inputmode="decimal" data-tipo="nominal" data-cota="altura_2" value="{{ $reporte->valores_nominales['altura_2'] ?? '' }}" placeholder="Nom"></td>
                                <td><input type="text" class="rd-cell-input spec-editable input-decimal-only font-bold text-center" inputmode="decimal" data-tipo="nominal" data-cota="al_cuello" value="{{ $reporte->valores_nominales['al_cuello'] ?? '' }}" placeholder="Nom"></td>
                                <td class="td-obs"><input type="text" class="rd-cell-input spec-editable text-left" data-tipo="nominal" data-cota="observaciones" value="{{ $reporte->valores_nominales['observaciones'] ?? '' }}" placeholder="Nota espec..."></td>
                                <td class="td-actions"></td>
                            </tr>
                            {{-- Fila de Tolerancias (±) --}}
                            <tr class="tr-spec tr-tolerancias">
                                <td class="td-num text-center">
                                    <span class="rd-spec-tag rd-tag-tol" title="Tolerancias">±</span>
                                </td>
                                <td><input type="text" class="rd-cell-input spec-editable input-decimal-only text-center" inputmode="decimal" data-tipo="tolerancia" data-cota="c1" value="{{ ($reporte->tolerancias['c1'] ?? '') === '±' ? '' : ($reporte->tolerancias['c1'] ?? '') }}" placeholder="±"></td>
                                <td><input type="text" class="rd-cell-input spec-editable input-decimal-only text-center" inputmode="decimal" data-tipo="tolerancia" data-cota="c2" value="{{ ($reporte->tolerancias['c2'] ?? '') === '±' ? '' : ($reporte->tolerancias['c2'] ?? '') }}" placeholder="±"></td>
                                <td><input type="text" class="rd-cell-input spec-editable input-decimal-only text-center" inputmode="decimal" data-tipo="tolerancia" data-cota="cuello" value="{{ ($reporte->tolerancias['cuello'] ?? '') === '±' ? '' : ($reporte->tolerancias['cuello'] ?? '') }}" placeholder="±"></td>
                                <td><input type="text" class="rd-cell-input spec-editable input-decimal-only text-center" inputmode="decimal" data-tipo="tolerancia" data-cota="e" value="{{ ($reporte->tolerancias['e'] ?? '') === '±' ? '' : ($reporte->tolerancias['e'] ?? '') }}" placeholder="±"></td>
                                <td><input type="text" class="rd-cell-input spec-editable input-decimal-only text-center" inputmode="decimal" data-tipo="tolerancia" data-cota="d" value="{{ ($reporte->tolerancias['d'] ?? '') === '±' ? '' : ($reporte->tolerancias['d'] ?? '') }}" placeholder="±"></td>
                                <td><input type="text" class="rd-cell-input spec-editable input-decimal-only text-center" inputmode="decimal" data-tipo="tolerancia" data-cota="f1" value="{{ ($reporte->tolerancias['f1'] ?? '') === '±' ? '' : ($reporte->tolerancias['f1'] ?? '') }}" placeholder="±"></td>
                                <td><input type="text" class="rd-cell-input spec-editable input-decimal-only text-center" inputmode="decimal" data-tipo="tolerancia" data-cota="a_total" value="{{ ($reporte->tolerancias['a_total'] ?? '') === '±' ? '' : ($reporte->tolerancias['a_total'] ?? '') }}" placeholder="±"></td>
                                <td><input type="text" class="rd-cell-input spec-editable input-decimal-only text-center" inputmode="decimal" data-tipo="tolerancia" data-cota="f" value="{{ ($reporte->tolerancias['f'] ?? '') === '±' ? '' : ($reporte->tolerancias['f'] ?? '') }}" placeholder="±"></td>
                                <td><input type="text" class="rd-cell-input spec-editable input-decimal-only text-center" inputmode="decimal" data-tipo="tolerancia" data-cota="altura" value="{{ ($reporte->tolerancias['altura'] ?? '') === '±' ? '' : ($reporte->tolerancias['altura'] ?? '') }}" placeholder="±"></td>
                                <td><input type="text" class="rd-cell-input spec-editable input-decimal-only text-center" inputmode="decimal" data-tipo="tolerancia" data-cota="altura_2" value="{{ ($reporte->tolerancias['altura_2'] ?? '') === '±' ? '' : ($reporte->tolerancias['altura_2'] ?? '') }}" placeholder="±"></td>
                                <td><input type="text" class="rd-cell-input spec-editable input-decimal-only text-center" inputmode="decimal" data-tipo="tolerancia" data-cota="al_cuello" value="{{ ($reporte->tolerancias['al_cuello'] ?? '') === '±' ? '' : ($reporte->tolerancias['al_cuello'] ?? '') }}" placeholder="±"></td>
                                <td class="td-obs"></td>
                                <td class="td-actions"></td>
                            </tr>
                        </thead>
                        <tbody id="medidas-tbody">
                            @forelse($medidas as $m)
                            <tr data-medida-id="{{ $m->id }}" class="{{ \App\Http\Controllers\InspeccionDimensionalController::isMedidaCompleta($m) ? 'tr-completa' : '' }}">
                                <td class="td-num">
                                    <select class="rd-cell-select cell-editable text-center font-bold" data-campo="numero_pieza">
                                        @for($i = 1; $i <= $totalPiezas; $i++)
                                            <option value="{{ $i }}" {{ (string)$m->numero_pieza === (string)$i ? 'selected' : '' }}>{{ $i }}</option>
                                        @endfor
                                        @if($m->numero_pieza > $totalPiezas)
                                            <option value="{{ $m->numero_pieza }}" selected>{{ $m->numero_pieza }}</option>
                                        @endif
                                    </select>
                                </td>
                                <td><input type="text" class="rd-cell-input cell-editable input-decimal-only text-center" inputmode="decimal" data-campo="c1" value="{{ $m->c1 ?? '' }}"></td>
                                <td><input type="text" class="rd-cell-input cell-editable input-decimal-only text-center" inputmode="decimal" data-campo="c2" value="{{ $m->c2 ?? '' }}"></td>
                                <td><input type="text" class="rd-cell-input cell-editable input-decimal-only text-center" inputmode="decimal" data-campo="cuello" value="{{ $m->cuello ?? '' }}"></td>
                                <td><input type="text" class="rd-cell-input cell-editable input-decimal-only text-center" inputmode="decimal" data-campo="e" value="{{ $m->e ?? '' }}"></td>
                                <td><input type="text" class="rd-cell-input cell-editable input-decimal-only text-center" inputmode="decimal" data-campo="d" value="{{ $m->d ?? '' }}"></td>
                                <td><input type="text" class="rd-cell-input cell-editable input-decimal-only text-center" inputmode="decimal" data-campo="f1" value="{{ $m->f1 ?? '' }}"></td>
                                <td><input type="text" class="rd-cell-input cell-editable input-decimal-only text-center" inputmode="decimal" data-campo="a_total" value="{{ $m->a_total ?? '' }}"></td>
                                <td><input type="text" class="rd-cell-input cell-editable input-decimal-only text-center" inputmode="decimal" data-campo="f" value="{{ $m->f ?? '' }}"></td>
                                <td><input type="text" class="rd-cell-input cell-editable input-decimal-only text-center" inputmode="decimal" data-campo="altura" value="{{ $m->altura ?? '' }}"></td>
                                <td><input type="text" class="rd-cell-input cell-editable input-decimal-only text-center" inputmode="decimal" data-campo="altura_2" value="{{ $m->altura_2 ?? '' }}"></td>
                                <td><input type="text" class="rd-cell-input cell-editable input-decimal-only text-center" inputmode="decimal" data-campo="al_cuello" value="{{ $m->al_cuello ?? '' }}"></td>
                                <td><input type="text" class="rd-cell-input cell-editable text-left" data-campo="observaciones" value="{{ $m->observaciones ?? '' }}" placeholder="Observaciones..."></td>
                                <td class="td-actions">
                                    <button type="button" class="btn-del-row" title="Eliminar fila" data-medida-id="{{ $m->id }}">
                                        &times;
                                    </button>
                                </td>
                            </tr>
                            @empty
                            <tr id="row-empty">
                                <td colspan="14" class="text-center text-muted" style="padding: 24px;">
                                    No hay piezas registradas. Da clic en "Agregar Fila" para comenzar a medir.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

            {{-- ─ Diagrama Técnico Lateral ─ --}}
            @php
                $claseUpper = strtoupper($clase);
                $esMolde = str_contains($claseUpper, 'MOLDE');
                $diagramaImg = $esMolde
                    ? asset('images/MOLDE_ REPORTEDIMNSIONAL .jpg')
                    : asset('images/BOMBILLO_REPORTEDIMENSIONAL.jpg');
                $diagramaTitulo = $esMolde ? 'MOLDE' : 'BOMBILLO';
            @endphp
            <div class="rd-diagram-panel">
                <div class="rd-diagram-header">
                    <h4>DIAGRAMA DE REFERENCIA</h4>
                    <div style="display: flex; align-items: center; gap: 6px;">
                        <span class="rd-diagram-tag">{{ $diagramaTitulo }}</span>
                        <button type="button" id="btn-expand-diagram" class="btn-diagram-expand" title="Ver en pantalla completa">
                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M15 3h6v6M9 21H3v-6M21 3l-7 7M3 21l7-7"/></svg>
                            Ampliar
                        </button>
                    </div>
                </div>
                <div class="rd-diagram-image-wrapper" id="diagram-zoom-wrapper" title="Haz clic para ver en pantalla completa o pasa el cursor para zoom">
                    <img src="{{ $diagramaImg }}" alt="Diagrama Técnico {{ $clase }}" class="rd-diagram-img" id="diagram-img">
                    <div class="rd-zoom-hint">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line><line x1="11" y1="8" x2="11" y2="14"></line><line x1="8" y1="11" x2="14" y2="11"></line></svg>
                        Clic para ampliar &bull; Pasa el cursor para zoom
                    </div>
                </div>
                <div class="rd-diagram-legend">
                    <div class="rd-legend-item"><span class="rd-legend-key">A</span> Ajuste de Cono</div>
                    <div class="rd-legend-item"><span class="rd-legend-key">B1 / B2</span> Conexión L.P. / 90°</div>
                    <div class="rd-legend-item"><span class="rd-legend-key">C1 / C2</span> Boca L.P. / 90°</div>
                    <div class="rd-legend-item"><span class="rd-legend-key">K</span> Altura de Cavidad</div>
                    <div class="rd-legend-item"><span class="rd-legend-key">L</span> Viaje</div>
                    <div class="rd-legend-item"><span class="rd-legend-key">F1 / F2</span> Grueso / Altura Ceja</div>
                    <div class="rd-legend-item"><span class="rd-legend-key">E1 / E2</span> Mordaza / Mordaza Inf.</div>
                    <div class="rd-legend-item"><span class="rd-legend-key">G1 / G2</span> Grueso / Altura Tacón</div>
                    <div class="rd-legend-item"><span class="rd-legend-key">W / O</span> Rebaje Borlioni / 90°</div>
                </div>
            </div>

        </div>

        {{-- ── Observaciones ── --}}
        <div class="rd-observations-block">
            <label class="rd-obs-label" for="txt-observaciones">Observaciones / Notas del Inspector:</label>
            <textarea id="txt-observaciones" class="rd-obs-textarea header-editable" data-campo="observaciones" rows="2" placeholder="Escribe observaciones adicionales o detalles de la inspección...">{{ $reporte->observaciones ?? '' }}</textarea>
        </div>

        {{-- ── Botones de Acción Finales ── --}}
        <div class="rd-bottom-actions-bar">
            <button type="button" class="btn-rd-bottom-action btn-rd-bottom-download" id="btn-bottom-descargar-pdf">
                @if(isset($pdfs) && $pdfs->count() > 0)
                    <span class="ri-btn-badge" id="pdf-badge-counter">{{ $pdfs->count() }}</span>
                @else
                    <span class="ri-btn-badge" id="pdf-badge-counter" style="display: none;">0</span>
                @endif
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
                Descargar Formato en PDF
            </button>
            <button type="button" class="btn-rd-bottom-action btn-rd-bottom-send" id="btn-bottom-enviar-reporte">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="22" y1="2" x2="11" y2="13"></line><polygon points="22 2 15 22 11 13 2 9 22 2"></polygon></svg>
                Enviar Reporte
            </button>
        </div>

    </div>

    {{-- ── TABLA DE HISTORIAL DE FORMATOS PDF GENERADOS ── --}}
    <div class="alm-table-card" id="pdf-versions-container"
        style="margin-top: 1.5rem; margin-bottom: 2rem; {{ (isset($pdfs) && $pdfs->count() > 0) ? '' : 'display: none;' }}">
        <div class="alm-table-header">
            <h2>
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align: middle; margin-right: 8px;"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>
                Historial de Formatos PDF Generados
            </h2>
        </div>
        <div style="padding: 15px;">
            <div class="alm-table-scroll">
                <table class="alm-table" style="width: 100%; text-align: center;">
                    <thead>
                        <tr>
                            <th style="width: 10%;">Versión</th>
                            <th style="width: 35%;">Nombre de Archivo</th>
                            <th style="width: 20%;">Generado Por</th>
                            <th style="width: 15%;">Fecha y Hora</th>
                            <th style="width: 20%;">Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="pdf-versions-tbody">
                        @if(isset($pdfs))
                            @foreach($pdfs as $pdf)
                                <tr id="pdf-row-{{ $pdf->id }}">
                                    <td style="font-weight: bold; color: #d32f2f;">V{{ $pdf->version }}</td>
                                    <td style="font-weight: 500;">{{ $pdf->nombre_archivo }}</td>
                                    <td>{{ $pdf->creador ? ($pdf->creador->nombre . ' ' . $pdf->creador->a_paterno) : 'Sistema' }}</td>
                                    <td>{{ $pdf->created_at ? $pdf->created_at->format('d/m/Y H:i A') : '—' }}</td>
                                    <td>
                                        <div style="display: inline-flex; align-items: center; justify-content: center; gap: 6px;">
                                            <a href="{{ route('calidad.inspeccion_dimensional.pdf.view', [$reporte->id, $pdf->id]) }}"
                                                target="_blank"
                                                class="btn-file-view"
                                                style="padding: 6px 12px; font-size: 0.82rem; text-decoration: none; display: inline-flex; align-items: center; gap: 4px;"
                                                title="Ver PDF">
                                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                                                Ver
                                            </a>
                                            <a href="{{ route('calidad.inspeccion_dimensional.pdf.download', [$reporte->id, $pdf->id]) }}"
                                                class="btn-file-download"
                                                style="padding: 6px 12px; font-size: 0.82rem; text-decoration: none; display: inline-flex; align-items: center; gap: 4px;"
                                                title="Descargar PDF">
                                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
                                                Descargar
                                            </a>
                                            <button type="button"
                                                class="btn-file-delete btn-delete-pdf"
                                                data-pdf-id="{{ $pdf->id }}"
                                                data-pdf-name="{{ $pdf->nombre_archivo }}"
                                                style="padding: 6px 12px; font-size: 0.82rem; display: inline-flex; align-items: center; gap: 4px;"
                                                title="Eliminar PDF del historial">
                                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
                                                Eliminar
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>

{{-- ── Modal de Pantalla Completa para Diagrama Técnico ── --}}
<div id="diagram-fullscreen-modal" class="diagram-fullscreen-modal" style="display: none;" role="dialog" aria-modal="true">
    <div class="diagram-modal-backdrop" id="diagram-modal-backdrop"></div>
    <div class="diagram-modal-container">
        <button type="button" class="diagram-modal-close-btn" id="btn-close-diagram-modal" title="Cerrar vista completa (Esc)">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
        </button>
        <div class="diagram-modal-header-bar">
            <div class="diagram-modal-title-group">
                <span class="diagram-modal-badge">{{ $diagramaTitulo }}</span>
                <span class="diagram-modal-title">DIAGRAMA TÉCNICO DE REFERENCIA</span>
            </div>
            <span class="diagram-modal-meta">OT: {{ $reporte->ot_id }} &bull; Clase: {{ $clase }}</span>
        </div>
        <div class="diagram-modal-image-box" id="modal-zoom-box">
            <div class="modal-zoom-toolbar">
                <button type="button" class="btn-modal-zoom" id="btn-modal-zoom-in" title="Acercar (+)">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line><line x1="11" y1="8" x2="11" y2="14"></line><line x1="8" y1="11" x2="14" y2="11"></line></svg>
                </button>
                <button type="button" class="btn-modal-zoom" id="btn-modal-zoom-out" title="Alejar (-)">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line><line x1="8" y1="11" x2="14" y2="11"></line></svg>
                </button>
                <button type="button" class="btn-modal-zoom" id="btn-modal-zoom-reset" title="Restablecer tamaño (100%)">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"></path><polyline points="3 3 3 8 8 8"></polyline></svg>
                </button>
                <span class="modal-zoom-level" id="modal-zoom-level">100%</span>
            </div>
            <img src="{{ $diagramaImg }}" alt="Diagrama Técnico {{ $diagramaTitulo }}" class="diagram-modal-full-img" id="modal-zoom-img">
            <div class="modal-zoom-hint" id="modal-zoom-hint">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line><line x1="11" y1="8" x2="11" y2="14"></line><line x1="8" y1="11" x2="14" y2="11"></line></svg>
                Pasa el cursor o usa la rueda del ratón para zoom
            </div>
        </div>
        <div class="diagram-modal-legend-bar">
            <div class="modal-legend-items">
                <span><b>A:</b> Ajuste Cono</span>
                <span><b>B1/B2:</b> Conexión L.P./90°</span>
                <span><b>C1/C2:</b> Boca L.P./90°</span>
                <span><b>K:</b> Altura Cavidad</span>
                <span><b>L:</b> Viaje</span>
                <span><b>F1/F2:</b> Grueso/Alt. Ceja</span>
                <span><b>E1/E2:</b> Mordaza/Inf.</span>
                <span><b>G1/G2:</b> Grueso/Alt. Tacón</span>
                <span><b>W/O:</b> Rebaje Borlioni/90°</span>
            </div>
        </div>
    </div>
</div>

{{-- ── Modal de Vista Previa Interactiva para Archivos Excel ── --}}
<div id="excel-preview-modal" class="excel-preview-modal" style="display: none;" role="dialog" aria-modal="true">
    <div class="excel-modal-backdrop" id="excel-modal-backdrop"></div>
    <div class="excel-modal-container">
        <div class="excel-modal-header">
            <div class="excel-modal-title-group">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#22c55e" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="8" y1="13" x2="16" y2="13"></line><line x1="8" y1="17" x2="16" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>
                <div>
                    <h3 class="excel-modal-title" id="excel-modal-filename">Archivo.xlsx</h3>
                    <span class="excel-modal-subtitle">Visor Integrado de Hoja de Cálculo</span>
                </div>
            </div>
            <div class="excel-modal-actions">
                <a id="excel-modal-download-btn" href="#" class="btn-modal-excel-download" download title="Descargar archivo original">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
                    Descargar Archivo
                </a>
                <button type="button" class="excel-modal-close-btn" id="btn-close-excel-modal" title="Cerrar (Esc)">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                </button>
            </div>
        </div>
        <div class="excel-modal-sheet-tabs" id="excel-modal-tabs"></div>
        <div class="excel-modal-body" id="excel-modal-table-container">
            <div class="excel-loading-spinner" id="excel-loading-spinner">
                <div class="rd-spinner"></div>
                <span>Cargando contenido de la hoja de cálculo...</span>
            </div>
        </div>
    </div>
</div>

{{-- Variables globales para JavaScript --}}
<script>
    window.rdConfig = {
        reporteId: {{ $reporte->id }},
        totalPiezas: {{ $totalPiezas }},
        routes: {
            autosaveMedida:          @json(route('calidad.inspeccion_dimensional.autosave_medida')),
            updateNominalTolerancia: @json(route('calidad.inspeccion_dimensional.update_nominal_tolerancia')),
            addRow:                  @json(route('calidad.inspeccion_dimensional.add_row')),
            deleteRow:               @json(route('calidad.inspeccion_dimensional.delete_row')),
            uploadExcel:             @json(route('calidad.inspeccion_dimensional.upload_excel')),
            viewExcel:               @json(route('calidad.inspeccion_dimensional.view_excel', ['id' => $reporte->id])),
            downloadExcel:           @json(route('calidad.inspeccion_dimensional.download_excel', ['id' => $reporte->id])),
            deleteExcel:             @json(route('calidad.inspeccion_dimensional.delete_excel')),
            updateHeader:            @json(route('calidad.inspeccion_dimensional.update_header')),
            pdf:                     @json(route('calidad.inspeccion_dimensional.pdf', ['id' => $reporte->id])),
            deletePdf:               @json(route('calidad.inspeccion_dimensional.pdf.delete')),
        }
    };
</script>

@endsection
