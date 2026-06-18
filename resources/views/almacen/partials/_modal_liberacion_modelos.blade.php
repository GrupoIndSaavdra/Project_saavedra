{{-- _modal_liberacion_modelos.blade.php — F-CCL-LDM v2 --}}
@php
    $itemsModelo = ['A'=>'Altura de la ceja','A1'=>'Altura de sufridera','B'=>'Altura total',
                    'C'=>'Diam. de ceja','D1'=>'Diam. de mordaza','D2'=>'Laterales',
                    'E2'=>'Radio de mordaza','E1'=>'Radio de ceja','G1'=>'Distancia de Vena','G2'=>'Ensamble W'];

    $fondoRows   = ['mayor_diam'=>'&Oslash;MAYOR','mayor_altura'=>'ALT. &Oslash;MAYOR',
                    'menor_diam'=>'&Oslash;MENOR','menor_altura'=>'ALT. &Oslash;MENOR'];

    // Columnas de la matriz (V,W sin subdivisión; X→x1,x2; Y→y1,y2,y3; Z→z1,z2)
    $matrixCols = [
        'V' => ['V'],
        'W' => ['W'],
        'X' => ['x1','x2'],
        'Y' => ['y1','y2','y3'],
        'Z' => ['z1','z2'],
    ];
    $matrixRows = ['plantilla'=>'PLANTILLA','templadera'=>'TEMPLADERA DE MADERA'];
@endphp

{{-- Zoom result overlay (lupa hover) --}}
<div id="lib-zoom-result" class="lib-zoom-result" aria-hidden="true"></div>

{{-- ── MODAL PRINCIPAL ── --}}
<div id="modalLiberacionModelo" class="alm-modal" role="dialog" aria-modal="true">
  <div class="alm-modal-content lib-modal-content">

    {{-- CABECERA --}}
    <div class="alm-modal-header lib-modal-header" id="lib-modal-header">
      <div class="div-cerrar">
        <button type="button" class="btn-cerrar" onclick="cerrarModalLiberacion()">
          <img class="img-cerrar" src="{{ asset('images/cerrar.png') }}" alt="Cerrar">
        </button>
      </div>
      <div class="lib-header-top">
        <div class="lib-format-meta">
          <span class="lib-meta-item"><strong>Codigo:</strong> F-CCL-LDM</span>
          <span class="lib-meta-sep">|</span>
          <span class="lib-meta-item"><strong>Version:</strong> B</span>
          <span class="lib-meta-sep">|</span>
          <span class="lib-meta-item"><strong>Revision:</strong> 17 de enero de 2024</span>
        </div>
        <h3 id="lib-modal-title-text" class="lib-modal-title-text">Formato de Liberacion de Modelos</h3>
        <p id="lib-modal-subtitle" class="lib-modal-subtitle"></p>
      </div>
    </div>

    {{-- CUERPO --}}
    <div class="alm-modal-body lib-modal-body">
      <form id="formLiberacion" autocomplete="off">
        <input type="hidden" id="lib-ot"     name="ot">
        <input type="hidden" id="lib-accion" name="accion" value="aprobar">



        {{-- DATOS DEL FORMATO --}}
        <div class="lib-format-datos">
          <div class="lib-datos-grid">
            <div class="lib-dato-group">
              <label for="lib-tipo" class="lib-dato-label">Tipo de Modelo:</label>
              {{-- El select se filtra por JS según las clases activas de la OT --}}
              <select id="lib-tipo" name="tipo_modelo" class="lib-select" onchange="libCambiarTipo(this.value)">
                <option value="">-- Seleccionar tipo --</option>
                <option value="Fondo">Fondo</option>
                <option value="Obturador">Obturador</option>
                <option value="Molde">Molde</option>
                <option value="Bombillo">Bombillo</option>
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
          <p class="lib-section-hint">Haz clic en cualquier imagen para verla en tamano completo en una nueva pestana.</p>
          <div class="lib-img-strip">
            @foreach ([
              ['url'=>asset('images/Liberación Calidad/Figura 1.jpg'), 'label'=>'Vista General (A, A1, B, C, D)'],
              ['url'=>asset('images/Liberación Calidad/Figura 2.jpg'), 'label'=>'Vista Lateral (E1, E2)'],
              ['url'=>asset('images/Liberación Calidad/Figura 3.jpg'), 'label'=>'Vista Superior (D2, G1, G2)'],
              ['url'=>asset('images/Liberación Calidad/Figura 4.jpg'), 'label'=>'Plantilla y Templadera'],
              ['url'=>asset('images/Liberación Calidad/Figura 5.jpg'), 'label'=>'Referencia 3D'],
            ] as $img)
              <div class="lib-img-card">
                <div class="lib-img-zoom-wrapper"
                     data-src="{{ $img['url'] }}"
                     onclick="window.open(this.dataset.src,'_blank')"
                     role="button" tabindex="0"
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
        <div class="lib-tabla-section" id="lib-tabla-1" style="display:none;">
          <h4 class="lib-section-title">Dimensiones del Modelo — Macho y Hembra</h4>
          <div class="lib-table-wrapper">
            <table class="lib-report-table">
              <thead>
                <tr>
                  <th class="lib-th-measure">MEDIDA DEL DIBUJO<br><span class="lib-unit-th">(")</span></th>
                  <th class="lib-th-item-wide">ITEM</th>
                  <th class="lib-th-measure">DIMENSION DEL MODELO<br>MACHO <span class="lib-unit-th">(")</span></th>
                  <th class="lib-th-measure">DIMENSION DEL MODELO<br>HEMBRA <span class="lib-unit-th">(")</span></th>
                </tr>
              </thead>
              <tbody>
                @foreach ($itemsModelo as $key => $desc)
                  <tr>
                    <td class="lib-td-input">
                      <div class="lib-input-unit-wrap">
                        <input type="number" id="lib-modelo-{{ $key }}-dibujo"
                               name="modelo[{{ $key }}][dibujo]"
                               class="lib-num-input" step="0.001" min="0" placeholder="0.000">
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
                               name="modelo[{{ $key }}][macho]"
                               class="lib-num-input" step="0.001" min="0" placeholder="0.000">
                        <span class="lib-unit-inline">"</span>
                      </div>
                    </td>
                    <td class="lib-td-input">
                      <div class="lib-input-unit-wrap">
                        <input type="number" id="lib-modelo-{{ $key }}-hembra"
                               name="modelo[{{ $key }}][hembra]"
                               class="lib-num-input" step="0.001" min="0" placeholder="0.000">
                        <span class="lib-unit-inline">"</span>
                      </div>
                    </td>
                  </tr>
                @endforeach
              </tbody>
            </table>
          </div>
          {{-- OBSERVACIONES TABLA 1 --}}
          <div class="lib-section-block" style="margin-top: 1.5em;">
            <h5 style="font-weight: 700; color: #033966; font-size: 1.1em; margin-bottom: 8px;">Observaciones de Dimensiones del Modelo</h5>
            <textarea id="lib-obs-modelo" name="observaciones_modelo"
                      class="form-control lib-textarea" rows="3"
                      placeholder="Observaciones de dimensiones del modelo Macho y Hembra..."></textarea>
          </div>
        </div>

        {{-- ────────────────────────────────────────────────────────────────────
             TABLA 2 — Plantilla y Templadera (Bombillo + Molde)
             ──────────────────────────────────────────────────────────────────── --}}
        <div class="lib-tabla-section" id="lib-tabla-2" style="display:none;">
          <h4 class="lib-section-title">Plantilla y Templadera de Madera</h4>
          <div class="lib-table-wrapper lib-matrix-wrapper">

            <table class="lib-report-table lib-matrix-table" style="margin-bottom: 20px;">
              <thead>
                <tr>
                  <th rowspan="2" class="lib-th-tipo" style="vertical-align: middle;">MEDIDA DE<br>PLANTILLA</th>
                  @foreach ($matrixCols as $mainCol => $subCols)
                    <th colspan="{{ count($subCols) }}" class="lib-th-main-col" style="text-align: center;">{{ $mainCol }}</th>
                  @endforeach
                </tr>
                <tr>
                  @foreach ($matrixCols as $mainCol => $subCols)
                    @foreach ($subCols as $sub)
                      <th class="lib-th-sub" style="text-align: center;">{{ $sub }}</th>
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
                      <input type="number" id="lib-plt-plantilla-{{ $sub }}-dibujo"
                             name="plantilla[plantilla_{{ $sub }}][dibujo]"
                             class="lib-num-input lib-num-input-sm" step="0.001" min="0" placeholder="0.000">
                    </td>
                    @endforeach
                  @endforeach
                </tr>
                <tr>
                  <td class="lib-td-tipo"><strong>Fisico (")</strong></td>
                  @foreach ($matrixCols as $mainCol => $subCols)
                    @foreach ($subCols as $sub)
                    <td class="lib-td-matrix">
                      <input type="number" id="lib-plt-plantilla-{{ $sub }}-fisico"
                             name="plantilla[plantilla_{{ $sub }}][fisico]"
                             class="lib-num-input lib-num-input-sm" step="0.001" min="0" placeholder="0.000">
                    </td>
                    @endforeach
                  @endforeach
                </tr>
              </tbody>
            </table>

            <table class="lib-report-table lib-matrix-table">
              <thead>
                <tr>
                  <th rowspan="2" class="lib-th-tipo" style="vertical-align: middle;">MEDIDA DE<br>TEMPLADERA DE MADERA</th>
                  @foreach ($matrixCols as $mainCol => $subCols)
                    <th colspan="{{ count($subCols) }}" class="lib-th-main-col" style="text-align: center;">{{ $mainCol }}</th>
                  @endforeach
                </tr>
                <tr>
                  @foreach ($matrixCols as $mainCol => $subCols)
                    @foreach ($subCols as $sub)
                      <th class="lib-th-sub" style="text-align: center;">{{ $sub }}</th>
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
                      <input type="number" id="lib-plt-templadera-{{ $sub }}-dibujo"
                             name="plantilla[templadera_{{ $sub }}][dibujo]"
                             class="lib-num-input lib-num-input-sm" step="0.001" min="0" placeholder="0.000">
                    </td>
                    @endforeach
                  @endforeach
                </tr>
                <tr>
                  <td class="lib-td-tipo"><strong>Fisico (")</strong></td>
                  @foreach ($matrixCols as $mainCol => $subCols)
                    @foreach ($subCols as $sub)
                    <td class="lib-td-matrix">
                      <input type="number" id="lib-plt-templadera-{{ $sub }}-fisico"
                             name="plantilla[templadera_{{ $sub }}][fisico]"
                             class="lib-num-input lib-num-input-sm" step="0.001" min="0" placeholder="0.000">
                    </td>
                    @endforeach
                  @endforeach
                </tr>
              </tbody>
            </table>

          </div>
          {{-- OBSERVACIONES TABLA 2 --}}
          <div class="lib-section-block" style="margin-top: 1.5em;">
            <h5 style="font-weight: 700; color: #033966; font-size: 1.1em; margin-bottom: 8px;">Observaciones de Plantilla y Templadera</h5>
            <textarea id="lib-obs-plantilla" name="observaciones_plantilla"
                      class="form-control lib-textarea" rows="3"
                      placeholder="Observaciones de plantilla y templadera de madera..."></textarea>
          </div>
        </div>

        {{-- ────────────────────────────────────────────────────────────────────
             TABLA 3 — Fondo (solo Fondo)
             ──────────────────────────────────────────────────────────────────── --}}
        <div class="lib-tabla-section" id="lib-tabla-fondo" style="display:none;">
          <h4 class="lib-section-title">Dimensiones de Fondo</h4>
          <div class="lib-table-wrapper">
            <table class="lib-report-table">
              <thead>
                <tr>
                  <th colspan="1" class="lib-th-main-col">ITEM</th>
                  <th class="lib-th-measure">MEDIDA DEL DIBUJO<br><span class="lib-unit-th">(")</span></th>
                  <th class="lib-th-measure">MEDIDA FISICA<br><span class="lib-unit-th">(")</span></th>
                </tr>
              </thead>
              <tbody>
                @foreach ($fondoRows as $key => $label)
                  <tr>
                    <td class="lib-td-desc lib-td-fondo-item">{!! $label !!}</td>
                    <td class="lib-td-input">
                      <div class="lib-input-unit-wrap">
                        <input type="number" id="lib-fondo-{{ $key }}-dibujo"
                               name="fondo[{{ $key }}][dibujo]"
                               class="lib-num-input" step="0.001" min="0" placeholder="0.000">
                        <span class="lib-unit-inline">"</span>
                      </div>
                    </td>
                    <td class="lib-td-input">
                      <div class="lib-input-unit-wrap">
                        <input type="number" id="lib-fondo-{{ $key }}-fisico"
                               name="fondo[{{ $key }}][fisico]"
                               class="lib-num-input" step="0.001" min="0" placeholder="0.000">
                        <span class="lib-unit-inline">"</span>
                      </div>
                    </td>
                  </tr>
                @endforeach
              </tbody>
            </table>
          </div>
          {{-- OBSERVACIONES TABLA 3 --}}
          <div class="lib-section-block" style="margin-top: 1.5em;">
            <h5 style="font-weight: 700; color: #033966; font-size: 1.1em; margin-bottom: 8px;">Observaciones de Fondo</h5>
            <textarea id="lib-obs-fondo" name="observaciones_fondo"
                      class="form-control lib-textarea" rows="3"
                      placeholder="Observaciones de dimensiones de fondo..."></textarea>
          </div>
        </div>

        {{-- ────────────────────────────────────────────────────────────────────
             TABLA 4 — Obturador (solo Obturador)
             ──────────────────────────────────────────────────────────────────── --}}
        <div class="lib-tabla-section" id="lib-tabla-obturador" style="display:none;">
          <h4 class="lib-section-title">Dimensiones de Obturador</h4>
          <div class="lib-table-wrapper">
            <table class="lib-report-table">
              <thead>
                <tr>
                  <th colspan="1" class="lib-th-main-col">ITEM</th>
                  <th class="lib-th-measure">MEDIDA DEL DIBUJO<br><span class="lib-unit-th">(")</span></th>
                  <th class="lib-th-measure">MEDIDA FISICA<br><span class="lib-unit-th">(")</span></th>
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
                               class="lib-num-input" step="0.001" min="0" placeholder="0.000">
                        <span class="lib-unit-inline">"</span>
                      </div>
                    </td>
                    <td class="lib-td-input">
                      <div class="lib-input-unit-wrap">
                        <input type="number" id="lib-obturador-{{ $key }}-fisico"
                               name="obturador[{{ $key }}][fisico]"
                               class="lib-num-input" step="0.001" min="0" placeholder="0.000">
                        <span class="lib-unit-inline">"</span>
                      </div>
                    </td>
                  </tr>
                @endforeach
              </tbody>
            </table>
          </div>
          {{-- OBSERVACIONES TABLA 4 --}}
          <div class="lib-section-block" style="margin-top: 1.5em;">
            <h5 style="font-weight: 700; color: #033966; font-size: 1.1em; margin-bottom: 8px;">Observaciones de Obturador</h5>
            <textarea id="lib-obs-obturador" name="observaciones_obturador"
                      class="form-control lib-textarea" rows="3"
                      placeholder="Observaciones de dimensiones de obturador..."></textarea>
          </div>
        </div>

        {{-- Aviso: sin tipo seleccionado --}}
        <div id="lib-tabla-aviso" class="lib-tabla-aviso">
          Selecciona el Tipo de Modelo para visualizar la tabla de captura correspondiente.
        </div>

        {{-- BLOQUE 5b: SELECTOR VISUAL APROBAR / RECHAZAR --}}
        <div class="lib-decision-selector" id="lib-decision-selector" style="display:none; gap:14px; margin-bottom:22px; padding:16px; background:linear-gradient(135deg,rgba(3,0,65,0.04),rgba(10,133,4,0.04)); border-radius:12px; border:1.5px solid #e2e8f0;">
          <div class="lib-decision-card lib-decision-aprobar active" id="lib-dec-aprobar"
               onclick="libSeleccionarDecision('aprobar')"
               style="flex:1; padding:14px 10px; border-radius:10px; cursor:pointer; text-align:center; border:2px solid #0a8504; background:rgba(10,133,4,0.08); transition:all 0.25s;">
             <img src="{{ asset('images/Aprobado.png') }}" alt="" style="width:32px; margin-bottom:6px;">
             <div style="font-weight:700; color:#0a8504; font-size:0.95em;">Aprobar</div>
             <div style="font-size:0.8em; color:#64748b; margin-top:2px;">El modelo cumple con las especificaciones</div>
           </div>
          <div class="lib-decision-card lib-decision-rechazar" id="lib-dec-rechazar"
               onclick="libSeleccionarDecision('rechazar')"
               style="flex:1; padding:14px 10px; border-radius:10px; cursor:pointer; text-align:center; border:2px solid #e2e8f0; background:#fff; transition:all 0.25s;">
             <img src="{{ asset('images/Rechazado.png') }}" alt="" style="width:32px; margin-bottom:6px;">
             <div style="font-weight:700; color:#9c0300; font-size:0.95em;">Rechazar</div>
             <div style="font-size:0.8em; color:#64748b; margin-top:2px;">El modelo no cumple, generar SCAR</div>
           </div>
        </div>

        {{-- MOTIVO DE RECHAZO (condicional) --}}
        <div class="lib-section-block lib-rechazo-block" id="lib-rechazo-block" style="display:none;">
          <h4 class="lib-section-title lib-section-title-danger">Motivo de Rechazo</h4>
          <p class="lib-section-hint" style="color:#9c0300;">
            Describe el incumplimiento que impide la liberacion del modelo.
          </p>
          <textarea id="lib-motivo-rechazo" name="motivo_rechazo"
                    class="form-control lib-textarea lib-textarea-danger" rows="4"
                    placeholder="Ej: La medida B (Altura total) presenta desviacion de +0.025 pulg. sobre el limite de tolerancia..."></textarea>
        </div>

        {{-- DESTINATARIO REMOVIDO PARA USO DE .ENV --}}

        {{-- BOTONES DE ACCION --}}
        <div class="lib-actions" id="lib-actions"></div>

      </form>
    </div>
  </div>
</div>
