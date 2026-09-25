@extends('layouts.appMenu')

@section('head')
    <title>Desplazamiento de Molduras OT #{{ $otData->id }} — {{ $clase }} | GIS</title>
    <meta name="description" content="Reporte de desplazamiento de molduras. Formato oficial 4CAL-01. Grupo Industrial Saavedra.">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @vite([
        'resources/css/calidad_views/inspeccion_visual.css',
        'resources/js/calidad_views/inspeccion_visual.js'
    ])
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
@endsection

@section('background-body', 'background-image:url("' . asset('images/fondoLogin.jpg') . '")')

@section('content')

<div class="alm-wrapper rd-page-wrapper">

    {{-- ═══ BARRA SUPERIOR DE ACCIONES ═══ --}}
    <div class="rd-top-bar">
        <div class="rd-top-left">
            <a href="{{ route('calidad.inspeccion_visual.index') }}" class="btn-regresar">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline></svg>
                Regresar al Selector
            </a>
            <span class="rd-title-tag">
                <img src="{{ asset('images/reporte Volumetrico.png') }}" width="18" height="18" alt="Reporte Volumétrico">
                <span>Reporte Volumétrico</span>
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

    {{-- ═══ FORMATO INSTITUCIONAL DE DESPLAZAMIENTO DE MOLDURAS (4CAL-01) ═══ --}}
    <div class="rd-sheet-container">

        {{-- ── Encabezado Oficial (No editable - Control de Calidad SGC) ── --}}
        <div class="rd-doc-header">
            <div class="rd-doc-title-row">
                <div class="rd-doc-main-title">
                    DESPLAZAMIENTO DE MOLDURAS (Reporte Volumétrico)
                </div>
                <div class="rd-doc-logo">
                    <img src="{{ asset('images/lg_saavedra.png') }}" alt="Grupo Industrial Saavedra" class="rd-logo-img">
                </div>
            </div>

            <div class="rd-doc-meta-grid">
                <div class="rd-meta-col">
                    <span class="rd-meta-label">Fecha de Elaboración</span>
                    <span class="rd-meta-text-val">07-nov-25</span>
                </div>
                <div class="rd-meta-col">
                    <span class="rd-meta-label">Fecha de Revisión</span>
                    <span class="rd-meta-text-val">10-nov-25</span>
                </div>
                <div class="rd-meta-col">
                    <span class="rd-meta-label">Fecha de Aprobación</span>
                    <span class="rd-meta-text-val">10-nov-25</span>
                </div>
                <div class="rd-meta-col rd-meta-col-sm">
                    <span class="rd-meta-label">Nivel de Revisión</span>
                    <span class="rd-meta-text-val">0</span>
                </div>
                <div class="rd-meta-col rd-meta-col-code">
                    <span class="rd-meta-label">Código</span>
                    <span class="rd-meta-code-val">4CAL-01</span>
                </div>
            </div>
        </div>

        {{-- ── Información General y Especificaciones ── --}}
        <div class="rd-info-section">
            {{-- Bloque de Identificación --}}
            <div class="rd-info-block">
                <div class="rd-block-heading">IDENTIFICACIÓN DE ORDEN Y MOLDURA</div>
                <div class="rd-info-grid">
                    <div class="rd-info-cell">
                        <span class="rd-lbl">CODIGO:</span>
                        <input type="text" class="rd-inline-input header-editable" data-campo="codigo" value="{{ $reporte->codigo ?? '' }}" placeholder="Código...">
                    </div>
                    <div class="rd-info-cell">
                        <span class="rd-lbl">NOMBRE:</span>
                        <span class="rd-val font-bold">{{ $reporte->nombre_moldura ?? $otData->nombre_moldura }}</span>
                    </div>
                    <div class="rd-info-cell">
                        <span class="rd-lbl">SISTEMA:</span>
                        <input type="text" class="rd-inline-input header-editable font-bold" data-campo="sistema" value="{{ $reporte->sistema ?? 'P.S.B.A.' }}" placeholder="P.S.B.A.">
                    </div>
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
                </div>
            </div>

            {{-- Bloque de Especificaciones de Volumen S/D y Físico --}}
            <div class="rd-info-block">
                <div class="rd-block-heading">ESPECIFICACIONES DE VOLUMEN</div>
                <div class="rd-info-grid">
                    <div class="rd-info-cell">
                        <span class="rd-lbl">VOL. MOLDE S/D:</span>
                        <input type="text" class="rd-inline-input header-editable" data-campo="vol_molde_sd" value="{{ $reporte->vol_molde_sd ?? 'NA' }}" placeholder="NA">
                    </div>
                    <div class="rd-info-cell">
                        <span class="rd-lbl">VOL. BOMBILLO S/D:</span>
                        <input type="text" class="rd-inline-input header-editable" data-campo="vol_bombillo_sd" value="{{ $reporte->vol_bombillo_sd ?? '' }}" placeholder="Opcional...">
                    </div>
                    <div class="rd-info-cell">
                        <span class="rd-lbl">VOL. CONJUNTO FISICO:</span>
                        <input type="text" class="rd-inline-input header-editable" data-campo="vol_conjunto_fisico" value="{{ $reporte->vol_conjunto_fisico ?? 'NA ml' }}" placeholder="NA ml">
                    </div>
                    <div class="rd-info-cell">
                        <span class="rd-lbl">VOL. CONJUNTO S/D:</span>
                        <input type="text" class="rd-inline-input header-editable" data-campo="vol_conjunto_sd" value="{{ $reporte->vol_conjunto_sd ?? 'NA' }}" placeholder="NA">
                    </div>
                    <div class="rd-info-cell rd-info-cell--full">
                        <span class="rd-lbl">DATOS DE LOTE:</span>
                        <span class="rd-val font-bold">
                            Pedido: <span class="text-blue">{{ $pedido }} pzas</span> | 
                            Consignación: <span class="text-amber">{{ $consignacion > 0 ? "+{$consignacion} pzas" : "0 pzas" }}</span> | 
                            Total Lote: <span class="text-emerald">{{ $totalPiezas }} pzas</span>
                        </span>
                    </div>
                </div>
            </div>
        </div>

        {{-- ── SECCIÓN: SUBIR ARCHIVO ADJUNTO (PDF / EXCEL) ── --}}
        {{-- ── SECCIÓN MODALIDAD: SUBIR ARCHIVO ADJUNTO (PDF / EXCEL) ── --}}
        <div class="rd-excel-box">
            <div class="rd-excel-box-header">
                <div class="rd-excel-header-left">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#10b981" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>
                    <div>
                        <h3 class="rd-excel-title">Documentos / Reportes Adjuntos (PDF o Excel)</h3>
                        <p class="rd-excel-subtitle">Puedes adjuntar hasta 4 reportes (PDF o Excel) de inspección volumétrica o archivos de respaldo.</p>
                    </div>
                </div>
                <div class="rd-excel-actions">
                    <form id="form-upload-archivo" enctype="multipart/form-data" class="rd-upload-form">
                        @csrf
                        <input type="hidden" name="reporte_id" value="{{ $reporte->id }}">
                        <input type="file" id="input-archivo-file" name="archivo" accept=".pdf,.xlsx,.xls,.csv,.txt" style="display:none;">
                        <button type="button" id="btn-trigger-upload-visual" class="btn-excel-upload" {{ count($reporte->archivos_list) >= 4 ? 'disabled' : '' }}>
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="17 8 12 3 7 8"></polyline><line x1="12" y1="3" x2="12" y2="15"></line></svg>
                            <span>Subir Archivo (PDF / Excel)</span>
                            <span id="visual-count-badge" class="rd-badge-counter">({{ count($reporte->archivos_list) }}/4)</span>
                        </button>
                    </form>
                </div>
            </div>

            {{-- Lista de archivos adjuntos --}}
            <div id="visual-files-container" class="rd-excel-files-container">
                @forelse($reporte->archivos_list as $archivo)
                    @php
                        $ext = strtolower(pathinfo($archivo['nombre'] ?? '', PATHINFO_EXTENSION));
                        $isExcel = in_array($ext, ['xlsx', 'xls', 'csv']);
                        $usuario = !empty($archivo['usuario']) ? $archivo['usuario'] : ($reporte->inspector ? (explode(' ', trim($reporte->inspector->nombre))[0] . ' ' . trim($reporte->inspector->a_paterno ?? '')) : '');
                        $viewUrl = route('calidad.inspeccion_visual.view_archivo', ['id' => $reporte->id, 'file_id' => $archivo['id']]);
                        $downloadUrl = route('calidad.inspeccion_visual.download_archivo', ['id' => $reporte->id, 'file_id' => $archivo['id']]);
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
                            <button type="button" class="btn-file-delete btn-delete-visual-file" data-file-id="{{ $archivo['id'] }}" data-file-name="{{ $archivo['nombre'] }}" title="Eliminar archivo">
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

        {{-- ── CUERPO PRINCIPAL: TABLA DE MEDICIÓN DE DESPLAZAMIENTO ── --}}
        <div class="rd-table-wrapper">
            <div class="rd-table-bar">
                <h3 class="rd-table-title">REGISTRO DE MEDICIONES — DESPLAZAMIENTO DE MOLDURAS</h3>
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
                            <th class="th-num"># Pz</th>
                            <th style="width: 110px;">T °C H2O</th>
                            <th style="width: 150px;">Vol. real (ml)</th>
                            <th style="width: 170px;">Diferencia Vs Vol. I</th>
                            <th class="th-obs">Observaciones</th>
                            <th class="th-del"></th>
                        </tr>
                    </thead>
                    <tbody id="medidas-tbody">
                        @forelse($medidas as $m)
                        <tr data-medida-id="{{ $m->id }}">
                            <td class="td-num">
                                <select class="rd-cell-select cell-editable text-center font-bold" data-campo="numero_pieza">
                                    @for($i = 1; $i <= $totalPiezas; $i++)
                                        <option value="{{ $i }}" {{ (string)$m->numero_pieza === (string)$i ? 'selected' : '' }}>{{ $i }}</option>
                                    @endfor
                                    @if((int)$m->numero_pieza > $totalPiezas)
                                        <option value="{{ $m->numero_pieza }}" selected>{{ $m->numero_pieza }}</option>
                                    @endif
                                </select>
                            </td>
                            <td>
                                <input type="text" class="rd-cell-input cell-editable text-center font-bold" data-campo="temp_agua" value="{{ $m->temp_agua ?? '20°' }}" placeholder="20°">
                            </td>
                            <td>
                                <input type="text" class="rd-cell-input cell-editable input-decimal-only input-vol-real text-center font-bold" inputmode="decimal" data-campo="vol_real" value="{{ $m->vol_real ?? '' }}" placeholder="0.0">
                            </td>
                            <td>
                                <input type="text" class="rd-cell-input cell-editable input-decimal-only input-dif text-center" inputmode="decimal" data-campo="dif_vs_vol_ideal" value="{{ $m->dif_vs_vol_ideal ?? '' }}" placeholder="0.0">
                            </td>
                            <td>
                                <input type="text" class="rd-cell-input cell-editable text-left" data-campo="observaciones" value="{{ $m->observaciones ?? '' }}" placeholder="Observaciones de la pieza...">
                            </td>
                            <td class="td-actions">
                                <button type="button" class="btn-del-row" title="Eliminar fila" data-medida-id="{{ $m->id }}">
                                    &times;
                                </button>
                            </td>
                        </tr>
                        @empty
                        <tr id="row-empty">
                            <td colspan="6" class="text-center text-muted" style="padding: 24px;">
                                No hay piezas registradas. Da clic en "Agregar Fila" para comenzar la captura.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- ── SECCIÓN INFERIOR: VOLUMEN DESPLAZADO Y OBSERVACIONES GENERALES ── --}}
        <div class="rd-info-section" style="margin-top: 1.2rem; margin-bottom: 0;">
            {{-- Tarjeta 1: Parámetros de Volumen Desplazado (MAX, IDEAL, MIN) --}}
            <div class="rd-info-block">
                <div class="rd-block-heading">VOLUMEN DESPLAZADO</div>
                <div class="rd-volumen-grid">
                    <div class="rd-vol-cell">
                        <span class="rd-vol-label">MAX:</span>
                        <div class="rd-vol-input-wrap">
                            <input type="text" class="rd-vol-input header-editable input-decimal-only font-bold" inputmode="decimal" data-campo="vol_desplazado_max" value="{{ $reporte->vol_desplazado_max ?? '' }}" placeholder="0">
                            <span class="rd-vol-unit">ml</span>
                        </div>
                    </div>
                    <div class="rd-vol-cell rd-vol-cell-ideal">
                        <span class="rd-vol-label rd-vol-label-ideal">IDEAL:</span>
                        <div class="rd-vol-input-wrap">
                            <input type="text" id="input-vol-ideal" class="rd-vol-input rd-vol-input-ideal header-editable input-decimal-only font-bold" inputmode="decimal" data-campo="vol_desplazado_ideal" value="{{ $reporte->vol_desplazado_ideal ?? '' }}" placeholder="0">
                            <span class="rd-vol-unit rd-vol-unit-ideal">ml</span>
                        </div>
                    </div>
                    <div class="rd-vol-cell">
                        <span class="rd-vol-label">MIN:</span>
                        <div class="rd-vol-input-wrap">
                            <input type="text" class="rd-vol-input header-editable input-decimal-only font-bold" inputmode="decimal" data-campo="vol_desplazado_min" value="{{ $reporte->vol_desplazado_min ?? '' }}" placeholder="0">
                            <span class="rd-vol-unit">ml</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Tarjeta 2: Observaciones Generales --}}
            <div class="rd-info-block">
                <div class="rd-block-heading">OBSERVACIONES GENERALES</div>
                <div class="rd-obs-container">
                    <textarea class="rd-obs-textarea header-editable" data-campo="observaciones_generales" placeholder="Escribe aquí las observaciones generales del reporte de desplazamiento...">{{ $reporte->observaciones_generales ?? '' }}</textarea>
                </div>
            </div>
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
                                            <a href="{{ route('calidad.inspeccion_visual.pdf.view', [$reporte->id, $pdf->id]) }}"
                                                target="_blank"
                                                class="btn-file-view"
                                                style="padding: 6px 12px; font-size: 0.82rem; text-decoration: none; display: inline-flex; align-items: center; gap: 4px;"
                                                title="Ver PDF">
                                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                                                Ver
                                            </a>
                                            <a href="{{ route('calidad.inspeccion_visual.pdf.download', [$reporte->id, $pdf->id]) }}"
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

{{-- Configuración de JavaScript --}}
<script>
    window.rvConfig = {
        reporteId: {{ $reporte->id }},
        totalPiezas: {{ $totalPiezas }},
        routes: {
            autosaveMedida:  @json(route('calidad.inspeccion_visual.autosave_medida')),
            addRow:          @json(route('calidad.inspeccion_visual.add_row')),
            deleteRow:       @json(route('calidad.inspeccion_visual.delete_row')),
            updateHeader:    @json(route('calidad.inspeccion_visual.update_header')),
            uploadArchivo:   @json(route('calidad.inspeccion_visual.upload_archivo')),
            viewArchivo:     @json(route('calidad.inspeccion_visual.view_archivo', ['id' => $reporte->id])),
            downloadArchivo: @json(route('calidad.inspeccion_visual.download_archivo', ['id' => $reporte->id])),
            deleteArchivo:   @json(route('calidad.inspeccion_visual.delete_archivo')),
            pdf:             @json(route('calidad.inspeccion_visual.pdf', ['id' => $reporte->id])),
            deletePdf:       @json(route('calidad.inspeccion_visual.pdf.delete')),
        }
    };
</script>

@endsection
