{{-- _modal_liberacion_modelos.blade.php — F-CCL-LDM v2 --}}
@php
    $itemsModelo = [
        'A' => 'Altura de la ceja',
        'A1' => 'Altura de sufridera',
        'B' => 'Altura total',
        'C' => 'Diam. de ceja',
        'D1' => 'Diam. de mordaza',
        'D2' => 'Laterales',
        'E2' => 'Radio de mordaza',
        'E1' => 'Radio de ceja',
        'G1' => 'Distancia de Vena',
        'G2' => 'Ensamble W',
    ];

    $fondoRows = [
        'mayor_diam' => '&Oslash;MAYOR',
        'mayor_altura' => 'ALT. &Oslash;MAYOR',
        'menor_diam' => '&Oslash;MENOR',
        'menor_altura' => 'ALT. &Oslash;MENOR',
    ];

    // Columnas de la matriz (V,W sin subdivisión; X→x1,x2; Y→y1,y2,y3; Z→z1,z2)
    $matrixCols = [
        'V' => ['V'],
        'W' => ['W'],
        'X' => ['x1', 'x2'],
        'Y' => ['y1', 'y2', 'y3'],
        'Z' => ['z1', 'z2'],
    ];
    $matrixRows = ['plantilla' => 'PLANTILLA', 'templadera' => 'TEMPLADERA DE MADERA'];
@endphp

<style>
    /* Premium Large Decision Cards */
    .lib-decision-selector-wrap {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 16px;
        padding: 24px;
        margin-bottom: 24px;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
        display: flex !important;
        flex-direction: column !important;
        transition: all 0.5s cubic-bezier(0.4, 0, 0.2, 1);
    }

    /* Dynamic Container Styling based on Selection */
    .lib-decision-selector-wrap:has(.lib-decision-aprobar.active) {
        background: linear-gradient(135deg, #ffffff 40%, #f0fdf4 100%) !important;
        border-color: rgba(10, 133, 4, 0.3) !important;
        box-shadow: 0 12px 24px -6px rgba(10, 133, 4, 0.15), 0 0 0 2px rgba(10, 133, 4, 0.05) !important;
    }
    .lib-decision-selector-wrap:has(.lib-decision-aprobar.active) .decision-label-title {
        color: #0a8504;
    }

    .lib-decision-selector-wrap:has(.lib-decision-rechazar.active) {
        background: linear-gradient(135deg, #ffffff 40%, #fef2f2 100%) !important;
        border-color: rgba(156, 3, 0, 0.3) !important;
        box-shadow: 0 12px 24px -6px rgba(156, 3, 0, 0.15), 0 0 0 2px rgba(156, 3, 0, 0.05) !important;
    }
    .lib-decision-selector-wrap:has(.lib-decision-rechazar.active) .decision-label-title {
        color: #9c0300;
    }

    .decision-label-header {
        margin-bottom: 20px;
        border-bottom: 1px solid #f1f5f9;
        padding-bottom: 12px;
        width: 100%;
        transition: all 0.3s ease;
    }
    .lib-decision-selector-wrap:has(.lib-decision-aprobar.active) .decision-label-header {
        border-bottom-color: rgba(10, 133, 4, 0.1);
    }
    .lib-decision-selector-wrap:has(.lib-decision-rechazar.active) .decision-label-header {
        border-bottom-color: rgba(156, 3, 0, 0.1);
    }

    .decision-label-title {
        font-size: 1.15em;
        font-weight: 800;
        color: #0f172a;
        margin-bottom: 4px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        transition: color 0.4s ease;
    }
    .decision-label-subtitle {
        font-size: 0.9em;
        color: #64748b;
    }

    .lib-decision-selector-inner {
        display: flex;
        gap: 20px;
        width: 100%;
    }
    
    .lib-decision-card {
        flex: 1;
        background: #f8fafc !important;
        border: 2px solid #e2e8f0 !important;
        border-radius: 16px;
        padding: 24px 16px;
        cursor: pointer;
        text-align: center;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        position: relative;
        overflow: hidden;
    }

    .lib-decision-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 12px 20px -8px rgba(0,0,0,0.15);
        background: #ffffff !important;
    }

    .lib-decision-card img {
        width: 48px !important;
        margin-bottom: 12px !important;
        transition: transform 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
        filter: drop-shadow(0 4px 6px rgba(0,0,0,0.1));
    }

    .card-title {
        font-weight: 800;
        font-size: 1.2em;
        margin-bottom: 6px;
        transition: color 0.3s ease;
    }
    .card-desc {
        font-size: 0.85em;
        color: #64748b;
        line-height: 1.4;
    }

    /* Aprobar */
    .lib-decision-aprobar .card-title { color: #475569; }
    .lib-decision-aprobar:hover { border-color: rgba(10, 133, 4, 0.4) !important; }
    .lib-decision-aprobar.active {
        background: linear-gradient(145deg, #ffffff, #f0fdf4) !important;
        border-color: #0a8504 !important;
        box-shadow: 0 0 0 4px rgba(10, 133, 4, 0.15), 0 8px 16px rgba(10, 133, 4, 0.1) !important;
        transform: translateY(-2px) scale(1.02);
    }
    .lib-decision-aprobar.active .card-title { color: #0a8504; }

    /* Rechazar */
    .lib-decision-rechazar .card-title { color: #475569; }
    .lib-decision-rechazar:hover { border-color: rgba(156, 3, 0, 0.4) !important; }
    .lib-decision-rechazar.active {
        background: linear-gradient(145deg, #ffffff, #fef2f2) !important;
        border-color: #9c0300 !important;
        box-shadow: 0 0 0 4px rgba(156, 3, 0, 0.15), 0 8px 16px rgba(156, 3, 0, 0.1) !important;
        transform: translateY(-2px) scale(1.02);
    }
    .lib-decision-rechazar.active .card-title { color: #9c0300; }

    .lib-decision-card.active img {
        transform: scale(1.2) rotate(-5deg);
        filter: drop-shadow(0 8px 12px rgba(0,0,0,0.2));
    }

    /* Active Indicator Badge */
    .active-badge {
        position: absolute;
        top: 12px;
        right: 12px;
        width: 24px;
        height: 24px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        opacity: 0;
        transform: scale(0);
        transition: all 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
    }
    .lib-decision-aprobar.active .active-badge {
        background: #0a8504 !important;
        opacity: 1; transform: scale(1);
    }
    .lib-decision-rechazar.active .active-badge {
        background: #9c0300 !important;
        opacity: 1; transform: scale(1);
    }
    .active-badge::after {
        content: '';
        width: 6px; height: 10px;
        border: solid white;
        border-width: 0 2px 2px 0;
        transform: rotate(45deg);
        margin-bottom: 2px;
    }
    .lib-decision-rechazar.active .active-badge::after {
        content: '✕';
        border: none;
        color: white;
        font-size: 14px;
        font-weight: bold;
        transform: none;
        margin-bottom: 0;
        line-height: 1;
    }
</style>

{{-- Zoom result overlay (lupa hover) --}}
<div id="lib-zoom-result" class="lib-zoom-result" aria-hidden="true"></div>

{{-- ── MODAL PRINCIPAL ── --}}
<div id="modalLiberacionModelo" class="alm-modal" role="dialog" aria-modal="true" style="padding: 0;">
    <div class="alm-modal-content lib-modal-content alm-border-radius-20px alm-border-2-5px-solid-0a8504 alm-overflow-hidden"
        style="max-width: 1750px; width: 98vw; max-height: 97vh; height: 97vh; display: flex; flex-direction: column; margin: auto;">

        {{-- CABECERA --}}
        <div class="alm-modal-header lib-modal-header alm-background-linear-gradient-135deg-0a8504-064e03 alm-border-bottom-2px-solid-064e03 alm-position-relative"
            id="lib-modal-header" style="padding: 10px 28px;">
            <div class="div-cerrar">
                <button type="button" class="btn-cerrar" onclick="cerrarModalLiberacion()">
                    <img class="img-cerrar" src="{{ asset('images/cerrar.png') }}" alt="Cerrar"
                        style="width: 32px !important; height: 32px !important;">
                </button>
            </div>
            <div class="lib-header-top" style="display: flex; align-items: center; gap: 12px; margin-top: 5px;">
                <div>
                    <div class="lib-format-meta" style="margin-bottom: 2px;">
                        <span class="lib-meta-item" id="lib-meta-codigo"
                            style="color: rgba(255,255,255,0.9); font-size: 0.85em;"><strong>Codigo:</strong>
                            F-CCL-LDM</span>
                        <span class="lib-meta-sep" style="color: rgba(255,255,255,0.9);">|</span>
                        <span class="lib-meta-item"
                            style="color: rgba(255,255,255,0.9); font-size: 0.85em;"><strong>Version:</strong> B</span>
                        <span class="lib-meta-sep" style="color: rgba(255,255,255,0.9);">|</span>
                        <span class="lib-meta-item"
                            style="color: rgba(255,255,255,0.9); font-size: 0.85em;"><strong>Revision:</strong> 17 de
                            enero de 2024</span>
                    </div>
                    <h3 id="lib-modal-title-text"
                        class="lib-modal-title-text alm-color-fff alm-margin-0 alm-font-weight-800 alm-font-family-Poppins-sans-serif"
                        style="font-size: 1.15em;">Formato de Liberacion de Modelos (F-CCL-LDM)</h3>
                    <p id="lib-modal-subtitle"
                        class="lib-modal-subtitle alm-color-rgba-255-255-255-0-9 alm-margin-top-2px alm-font-weight-500 alm-font-family-Poppins-sans-serif"
                        style="font-size: 0.85em; margin: 0;"></p>
                </div>
            </div>
        </div>

        {{-- CUERPO --}}
        <div class="alm-modal-body lib-modal-body alm-padding-1em-1-6em-1-2em-1-6em alm-background-fafafa alm-font-family-Poppins-sans-serif"
            style="flex: 1; display: flex; flex-direction: column; overflow-y: auto; overflow-x: hidden; min-height: 0; padding-top: 10px;">
            <form id="formLiberacion" autocomplete="off">
                <input type="hidden" id="lib-ot" name="ot">
                <input type="hidden" id="lib-accion" name="accion" value="aprobar">



                {{-- DATOS DEL FORMATO --}}
                <div class="lib-format-datos">
                    <div class="lib-datos-grid">
                        <div class="lib-dato-group">
                            <label for="lib-tipo" class="lib-dato-label">Tipo de Modelo:</label>
                            {{-- El select se filtra por JS según las clases activas de la OT --}}
                            <select id="lib-tipo" name="tipo_modelo" class="lib-select"
                                onchange="libCambiarTipo(this.value)">
                                <option value="">-- Seleccionar tipo --</option>
                                @foreach (config('global_classes.clases', []) as $index => $className)
                                    <option value="{{ $className }}">{{ $index }} - {{ $className }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="lib-dato-group">
                            <label class="lib-dato-label">Orden de Trabajo:</label>
                            <span id="lib-ot-display" class="lib-dato-value">—</span>
                        </div>
                        <div class="lib-dato-group">
                            <label class="lib-dato-label">Fecha de Inspeccion:</label>
                            <span class="lib-dato-value">{{ now()->format('d/m/Y') }}</span>
                        </div>
                    </div>
                </div>

                {{-- IMAGENES DE REFERENCIA (click = nueva pestaña) --}}
                <div class="lib-ref-section">
                    <h4 class="lib-section-title">Diagramas Dimensionales de Referencia</h4>
                    <p class="lib-section-hint">Haz clic en cualquier imagen para verla en tamano completo en una nueva
                        pestana.</p>
                    <div class="lib-img-strip">
                        @foreach ([['url' => asset('images/Liberación Calidad/Figura 1.jpg'), 'label' => 'Vista General (A, A1, B, C, D)'], ['url' => asset('images/Liberación Calidad/Figura 2.jpg'), 'label' => 'Vista Lateral (E1, E2)'], ['url' => asset('images/Liberación Calidad/Figura 3.jpg'), 'label' => 'Vista Superior (D2, G1, G2)'], ['url' => asset('images/Liberación Calidad/Figura 4.jpg'), 'label' => 'Plantilla y Templadera'], ['url' => asset('images/Liberación Calidad/Figura 5.jpg'), 'label' => 'Referencia 3D']] as $img)
                            <div class="lib-img-card">
                                <div class="lib-img-zoom-wrapper" data-src="{{ $img['url'] }}"
                                    onclick="window.open(this.dataset.src,'_blank')" role="button" tabindex="0"
                                    onkeydown="if(event.key==='Enter')window.open(this.dataset.src,'_blank')"
                                    title="Clic para ver en tamano completo">
                                    <img src="{{ $img['url'] }}" alt="{{ $img['label'] }}" class="lib-ref-img">
                                    <div class="lib-img-overlay-hint"><span>Ver completa</span></div>
                                </div>
                                <div class="lib-img-label">{{ $img['label'] }}</div>
                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- ────────────────────────────────────────────────────────────────────
             TABLA 1 — Macho y Hembra (Bombillo + Molde)
             Columnas: MEDIDA DEL DIBUJO | ITEM | MACHO | HEMBRA
             ──────────────────────────────────────────────────────────────────── --}}
                <div id="lib-tabla-1" class="lib-tabla-section" hidden>
                    <h4 class="lib-section-title">Dimensiones del Modelo — Macho y Hembra</h4>
                    <div class="lib-table-wrapper">
                        <table class="lib-report-table">
                            <thead>
                                <tr>
                                    <th class="lib-th-measure">MEDIDA DEL DIBUJO<br><span class="lib-unit-th">(")</span>
                                    </th>
                                    <th class="lib-th-item-wide">ITEM</th>
                                    <th class="lib-th-measure">DIMENSION DEL MODELO<br>MACHO <span
                                            class="lib-unit-th">(")</span></th>
                                    <th class="lib-th-measure">DIMENSION DEL MODELO<br>HEMBRA <span
                                            class="lib-unit-th">(")</span></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($itemsModelo as $key => $desc)
                                    <tr>
                                        <td class="lib-td-input">
                                            <div class="lib-input-unit-wrap">
                                                <input type="number" id="lib-modelo-{{ $key }}-dibujo"
                                                    name="modelo[{{ $key }}][dibujo]" class="lib-num-input"
                                                    step="0.001" min="0" placeholder="0.000">
                                                <span class="lib-unit-inline">"</span>
                                            </div>
                                        </td>
                                        <td class="lib-td-item-wide">
                                            <span class="lib-item-badge">{{ $key }}</span>
                                            <span class="lib-item-desc-inline">{{ $desc }}</span>
                                        </td>
                                        <td class="lib-td-input">
                                            <div class="lib-input-unit-wrap">
                                                <input type="number" id="lib-modelo-{{ $key }}-macho"
                                                    name="modelo[{{ $key }}][macho]" class="lib-num-input"
                                                    step="0.001" min="0" placeholder="0.000">
                                                <span class="lib-unit-inline">"</span>
                                            </div>
                                        </td>
                                        <td class="lib-td-input">
                                            <div class="lib-input-unit-wrap">
                                                <input type="number" id="lib-modelo-{{ $key }}-hembra"
                                                    name="modelo[{{ $key }}][hembra]" class="lib-num-input"
                                                    step="0.001" min="0" placeholder="0.000">
                                                <span class="lib-unit-inline">"</span>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    {{-- OBSERVACIONES TABLA 1 --}}
                    <div class="lib-section-block mt-1-5em">
                        <h5 class="modal-subtitle">Observaciones de Dimensiones del Modelo</h5>
                        <textarea id="lib-obs-modelo" name="observaciones_modelo" class="form-control lib-textarea" rows="3"
                            placeholder="Observaciones de dimensiones del modelo Macho y Hembra..."></textarea>
                    </div>
                </div>

                {{-- ────────────────────────────────────────────────────────────────────
             TABLA 2 — Plantilla y Templadera (Bombillo + Molde)
             ──────────────────────────────────────────────────────────────────── --}}
                <div id="lib-tabla-2" class="lib-tabla-section" hidden>
                    <h4 class="lib-section-title">Plantilla y Templadera de Madera</h4>
                    <div class="lib-table-wrapper lib-matrix-wrapper">

                        <table class="lib-report-table lib-matrix-table" style="margin-bottom: 20px;">
                            <thead>
                                <tr>
                                    <th rowspan="2" class="lib-th-tipo" style="vertical-align: middle;">MEDIDA
                                        DE<br>PLANTILLA</th>
                                    @foreach ($matrixCols as $mainCol => $subCols)
                                        <th colspan="{{ count($subCols) }}" class="lib-th-main-col text-center">
                                            {{ $mainCol }}</th>
                                    @endforeach
                                </tr>
                                <tr>
                                    @foreach ($matrixCols as $mainCol => $subCols)
                                        @foreach ($subCols as $sub)
                                            <th class="lib-th-sub text-center">{{ $sub }}</th>
                                        @endforeach
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td class="lib-td-tipo"><strong>Dibujo (")</strong></td>
                                    @foreach ($matrixCols as $mainCol => $subCols)
                                        @foreach ($subCols as $sub)
                                            <td class="lib-td-matrix">
                                                <input type="number"
                                                    id="lib-plt-plantilla-{{ $sub }}-dibujo"
                                                    name="plantilla[plantilla_{{ $sub }}][dibujo]"
                                                    class="lib-num-input lib-num-input-sm" step="0.001"
                                                    min="0" placeholder="0.000">
                                            </td>
                                        @endforeach
                                    @endforeach
                                </tr>
                                <tr>
                                    <td class="lib-td-tipo"><strong>Fisico (")</strong></td>
                                    @foreach ($matrixCols as $mainCol => $subCols)
                                        @foreach ($subCols as $sub)
                                            <td class="lib-td-matrix">
                                                <input type="number"
                                                    id="lib-plt-plantilla-{{ $sub }}-fisico"
                                                    name="plantilla[plantilla_{{ $sub }}][fisico]"
                                                    class="lib-num-input lib-num-input-sm" step="0.001"
                                                    min="0" placeholder="0.000">
                                            </td>
                                        @endforeach
                                    @endforeach
                                </tr>
                            </tbody>
                        </table>

                        <table class="lib-report-table lib-matrix-table">
                            <thead>
                                <tr>
                                    <th rowspan="2" class="lib-th-tipo" style="vertical-align: middle;">MEDIDA
                                        DE<br>TEMPLADERA DE MADERA</th>
                                    @foreach ($matrixCols as $mainCol => $subCols)
                                        <th colspan="{{ count($subCols) }}" class="lib-th-main-col text-center">
                                            {{ $mainCol }}</th>
                                    @endforeach
                                </tr>
                                <tr>
                                    @foreach ($matrixCols as $mainCol => $subCols)
                                        @foreach ($subCols as $sub)
                                            <th class="lib-th-sub text-center">{{ $sub }}</th>
                                        @endforeach
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td class="lib-td-tipo"><strong>Dibujo (")</strong></td>
                                    @foreach ($matrixCols as $mainCol => $subCols)
                                        @foreach ($subCols as $sub)
                                            <td class="lib-td-matrix">
                                                <input type="number"
                                                    id="lib-plt-templadera-{{ $sub }}-dibujo"
                                                    name="plantilla[templadera_{{ $sub }}][dibujo]"
                                                    class="lib-num-input lib-num-input-sm" step="0.001"
                                                    min="0" placeholder="0.000">
                                            </td>
                                        @endforeach
                                    @endforeach
                                </tr>
                                <tr>
                                    <td class="lib-td-tipo"><strong>Fisico (")</strong></td>
                                    @foreach ($matrixCols as $mainCol => $subCols)
                                        @foreach ($subCols as $sub)
                                            <td class="lib-td-matrix">
                                                <input type="number"
                                                    id="lib-plt-templadera-{{ $sub }}-fisico"
                                                    name="plantilla[templadera_{{ $sub }}][fisico]"
                                                    class="lib-num-input lib-num-input-sm" step="0.001"
                                                    min="0" placeholder="0.000">
                                            </td>
                                        @endforeach
                                    @endforeach
                                </tr>
                            </tbody>
                        </table>

                    </div>
                    {{-- OBSERVACIONES TABLA 2 --}}
                    <div class="lib-section-block mt-1-5em">
                        <h5 class="modal-subtitle">Observaciones de Plantilla y Templadera</h5>
                        <textarea id="lib-obs-plantilla" name="observaciones_plantilla" class="form-control lib-textarea" rows="3"
                            placeholder="Observaciones de plantilla y templadera de madera..."></textarea>
                    </div>
                </div>

                {{-- ────────────────────────────────────────────────────────────────────
             TABLA 3 — Fondo (solo Fondo)
             ──────────────────────────────────────────────────────────────────── --}}
                <div id="lib-tabla-fondo" class="lib-tabla-section" hidden>
                    <h4 class="lib-section-title" id="lib-tabla-fondo-title">Dimensiones de Fondo</h4>
                    <div class="lib-table-wrapper">
                        <table class="lib-report-table">
                            <thead>
                                <tr>
                                    <th colspan="1" class="lib-th-main-col">ITEM</th>
                                    <th class="lib-th-measure">MEDIDA DEL DIBUJO<br><span
                                            class="lib-unit-th">(")</span></th>
                                    <th class="lib-th-measure">MEDIDA FISICA<br><span class="lib-unit-th">(")</span>
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($fondoRows as $key => $label)
                                    <tr>
                                        <td class="lib-td-desc lib-td-fondo-item">{!! $label !!}</td>
                                        <td class="lib-td-input">
                                            <div class="lib-input-unit-wrap">
                                                <input type="number" id="lib-fondo-{{ $key }}-dibujo"
                                                    name="fondo[{{ $key }}][dibujo]" class="lib-num-input"
                                                    step="0.001" min="0" placeholder="0.000">
                                                <span class="lib-unit-inline">"</span>
                                            </div>
                                        </td>
                                        <td class="lib-td-input">
                                            <div class="lib-input-unit-wrap">
                                                <input type="number" id="lib-fondo-{{ $key }}-fisico"
                                                    name="fondo[{{ $key }}][fisico]" class="lib-num-input"
                                                    step="0.001" min="0" placeholder="0.000">
                                                <span class="lib-unit-inline">"</span>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    {{-- OBSERVACIONES TABLA 3 --}}
                    <div class="lib-section-block mt-1-5em">
                        <h5 class="modal-subtitle">Observaciones de Fondo</h5>
                        <textarea id="lib-obs-fondo" name="observaciones_fondo" class="form-control lib-textarea" rows="3"
                            placeholder="Observaciones de dimensiones de fondo..."></textarea>
                    </div>
                </div>

                {{-- ────────────────────────────────────────────────────────────────────
             TABLA 4 — Obturador (solo Obturador)
             ──────────────────────────────────────────────────────────────────── --}}
                <div id="lib-tabla-obturador" class="lib-tabla-section" hidden>
                    <h4 class="lib-section-title">Dimensiones de Obturador</h4>
                    <div class="lib-table-wrapper">
                        <table class="lib-report-table">
                            <thead>
                                <tr>
                                    <th colspan="1" class="lib-th-main-col">ITEM</th>
                                    <th class="lib-th-measure">MEDIDA DEL DIBUJO<br><span
                                            class="lib-unit-th">(")</span></th>
                                    <th class="lib-th-measure">MEDIDA FISICA<br><span class="lib-unit-th">(")</span>
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($fondoRows as $key => $label)
                                    <tr>
                                        <td class="lib-td-desc lib-td-fondo-item">{!! $label !!}</td>
                                        <td class="lib-td-input">
                                            <div class="lib-input-unit-wrap">
                                                <input type="number" id="lib-obturador-{{ $key }}-dibujo"
                                                    name="obturador[{{ $key }}][dibujo]"
                                                    class="lib-num-input" step="0.001" min="0"
                                                    placeholder="0.000">
                                                <span class="lib-unit-inline">"</span>
                                            </div>
                                        </td>
                                        <td class="lib-td-input">
                                            <div class="lib-input-unit-wrap">
                                                <input type="number" id="lib-obturador-{{ $key }}-fisico"
                                                    name="obturador[{{ $key }}][fisico]"
                                                    class="lib-num-input" step="0.001" min="0"
                                                    placeholder="0.000">
                                                <span class="lib-unit-inline">"</span>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    {{-- OBSERVACIONES TABLA 4 --}}
                    <div class="lib-section-block mt-1-5em">
                        <h5 class="modal-subtitle">Observaciones de Obturador</h5>
                        <textarea id="lib-obs-obturador" name="observaciones_obturador" class="form-control lib-textarea" rows="3"
                            placeholder="Observaciones de dimensiones de obturador..."></textarea>
                    </div>
                </div>

                {{-- Aviso: sin tipo seleccionado --}}
                <div id="lib-tabla-aviso" class="lib-tabla-aviso">
                    Selecciona el Tipo de Modelo para visualizar la tabla de captura correspondiente.
                </div>

                {{-- BLOQUE 5b: SELECTOR VISUAL APROBAR / RECHAZAR --}}
                <div class="lib-decision-selector-wrap alm-display-none cal-display-none" id="lib-decision-selector">
                    <div class="decision-label-header">
                        <div class="decision-label-title">Veredicto de Calidad</div>
                        <div class="decision-label-subtitle">Selecciona la decisión final para la liberación del modelo. Esto determinará el siguiente paso.</div>
                    </div>
                    
                    <div class="lib-decision-selector-inner">
                        
                        <div class="lib-decision-card lib-decision-aprobar active" id="lib-dec-aprobar"
                            onclick="libSeleccionarDecision('aprobar')">
                            <div class="active-badge"></div>
                            <img src="{{ asset('images/Aprobado.png') }}" alt="Aprobar">
                            <div class="card-title">Aprobar Modelo</div>
                            <div class="card-desc">El modelo cumple con todas las especificaciones de calidad requeridas.</div>
                        </div>

                        <div class="lib-decision-card lib-decision-rechazar" id="lib-dec-rechazar"
                            onclick="libSeleccionarDecision('rechazar')">
                            <div class="active-badge"></div>
                            <img src="{{ asset('images/Rechazado.png') }}" alt="Rechazar">
                            <div class="card-title">Rechazar Modelo</div>
                            <div class="card-desc">El modelo presenta defectos dimensionales o físicos. Generar SCAR.</div>
                        </div>

                    </div>
                </div>

                {{-- MOTIVO DE RECHAZO (condicional) --}}
                <div id="lib-rechazo-block" class="lib-section-block lib-rechazo-block" hidden>
                    <h4 class="lib-section-title lib-section-title-danger">Motivo de Rechazo</h4>
                    <p class="lib-section-hint text-danger">
                        Describe el incumplimiento que impide la liberacion del modelo.
                    </p>
                    <textarea id="lib-motivo-rechazo" name="motivo_rechazo" class="form-control lib-textarea lib-textarea-danger"
                        rows="4"
                        placeholder="Ej: La medida B (Altura total) presenta desviacion de +0.025 pulg. sobre el limite de tolerancia..."></textarea>
                </div>

                {{-- DESTINATARIO REMOVIDO PARA USO DE .ENV --}}

                {{-- BOTONES DE ACCION --}}
                <div class="lib-actions" id="lib-actions"></div>

            </form>
        </div>
    </div>
</div>
