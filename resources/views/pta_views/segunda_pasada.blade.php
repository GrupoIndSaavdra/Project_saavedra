@extends('layouts.appMenu')

@section('head')
    <title>2da Pasada — Soldadura PTA</title>
    <link rel="icon" href="{{ url('images/lg_saavedra.png') }}?v=1">
    @vite(['resources/css/pta_views/analysis.css', 'resources/css/processes_views/soldaduraPTA_table_partial.css', 'resources/css/pta_views/segunda_pasada.css'])
@endsection

@section('background-body', 'background-image:url("' . asset("images/fondoLogin.jpg") . '")')

@section('content')
    <div class="pta-analysis-wrapper">

        {{-- Header --}}
        <div class="pta-header">
            <h2>Edición 2da Pasada — Soldadura PTA</h2>
            <p>Selecciona OT, Clase y Pieza para editar los datos de segunda pasada</p>
        </div>

        {{-- Alertas --}}
        @if (session('success'))
            <div class="pta-alert pta-alert-success">&#10003; {{ session('success') }}</div>
        @endif
        @if (session('error'))
            <div class="pta-alert pta-alert-error">&#10007; {{ session('error') }}</div>
        @endif

        {{-- ── Modal contraseña PTA2026 ── --}}
        <div id="modalPta" class="pta-modal-overlay">
            <div class="pta-modal-content">
                <h3 class="pta-modal-title">Autenticación requerida</h3>
                <p class="pta-modal-text">Ingresa la contraseña para habilitar la edición de 2da pasada.</p>
                <input type="password" id="ptaPasswordInput" placeholder="Contraseña" class="pta-modal-input">
                <div id="ptaPassError" class="pta-modal-error">Contraseña incorrecta.</div>
                <button onclick="verificarPassword()" class="pta-btn-verify">
                    Verificar
                </button>
                <button onclick="cerrarModal()" class="pta-btn-cancel">
                    Cancelar
                </button>
            </div>
        </div>

        {{-- ── FILTROS EN CASCADA ── --}}
        <div class="pta-selectors-row">

            {{-- 1. Selector de OT --}}
            <div class="pta-ot-selector">
                <label for="sel-ot">Orden de Trabajo (OT)</label>
                <select id="sel-ot" onchange="onOtChange()">
                    <option value="">— Seleccionar OT —</option>
                    @foreach ($otsConPTA as $otOpt)
                        <option value="{{ $otOpt->id }}" {{ $otSeleccionadaId == $otOpt->id ? 'selected' : '' }}>
                            OT {{ $otOpt->id }}{{ $otOpt->moldura ? ' — ' . $otOpt->moldura->nombre : '' }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- 2. Selector de Clase (se habilita al elegir OT) --}}
            <div class="pta-clase-selector">
                <label for="sel-clase">Clase</label>
                <select id="sel-clase" onchange="onClaseChange()" {{ !$otSeleccionadaId ? 'disabled' : '' }}>
                    <option value="">— Seleccionar Clase —</option>
                    @if ($otSeleccionadaId)
                        @foreach ($otsConPTA->firstWhere('id', $otSeleccionadaId)?->clases ?? [] as $claseOpt)
                            <option value="{{ $claseOpt->id }}" {{ $claseSeleccionadaId == $claseOpt->id ? 'selected' : '' }}>
                                {{ $claseOpt->nombre }}
                            </option>
                        @endforeach
                    @endif
                </select>
            </div>

            {{-- 3. Selector de Pieza (se habilita al elegir Clase) --}}
            <div class="pta-pieza-selector">
                <label for="sel-pieza">Pieza</label>
                <select id="sel-pieza" onchange="onPiezaChange()" {{ !$claseSeleccionadaId ? 'disabled' : '' }}>
                    <option value="">— Seleccionar Pieza —</option>
                    @foreach ($piezasDisponibles as $np)
                        <option value="{{ $np }}" {{ $nPiezaSel === $np ? 'selected' : '' }}>{{ $np }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        {{-- ── DATOS DE LA PIEZA SELECCIONADA ── --}}
        @if ($piezasGroup && $piezasGroup->isNotEmpty())
             @php
                $picRow = $piezasGroup['D_Conexion_pico'] ?? null;
                $obtRow = $piezasGroup['D_Conexion_obt'] ?? null;
                $perRow = $piezasGroup['Perfilado'] ?? null;
                $p2Row = $piezasGroup['Segunda_Pasada'] ?? null;
                $p2Act = $p2Row?->p2_activa ?? false;

                $optsPreparacion = ['P1', 'P2', 'P3'];
            @endphp

            {{-- ────────────────────────────────────────────────────────────────
            1RA PASADA — solo lectura
            ──────────────────────────────────────────────────────────────────── --}}
            {{-- ────────────────────────────────────────────────────────────────
            1RA PASADA Y 2DA PASADA UNIFICADAS
            ──────────────────────────────────────────────────────────────────── --}}
            <div style="margin-bottom:1.5rem;">
                <div class="pta-section-header">
                    <h3 class="pta-section-title">
                        Resultados de Soldadura PTA (Pieza {{ $nPiezaSel }})
                    </h3>
                    @if (!session('pta_temp_auth'))
                        <button type="button" onclick="abrirModal()" class="pta-btn-unlock">
                            Desbloquear Edición 2da Pasada
                        </button>
                    @else
                        <span class="pta-unlocked-badge">Edición Desbloqueada</span>
                    @endif
                </div>

                <form method="POST" action="{{ route('pta.segunda_pasada.update') }}" id="form-p2"
                    style="{{ !session('pta_temp_auth') ? 'pointer-events:none; filter:grayscale(90%) opacity(0.85);' : '' }}">
                    @csrf
                    <input type="hidden" name="ot_id" value="{{ $otSeleccionadaId }}">
                    <input type="hidden" name="clase_id" value="{{ $claseSeleccionadaId }}">
                    <input type="hidden" name="n_pieza" value="{{ $nPiezaSel }}">
                    <input type="hidden" name="id_proceso" value="{{ $procesoPTA->id }}">

                    <div class="pta-table-wrapper">
                        <table class="pta-table">
                            <thead>
                                <tr>
                                    <th rowspan="2">Número<br>(M/H)</th>
                                    <th colspan="2" style="background:#055a9e;">Concepto</th>
                                    <th rowspan="2">VL</th>
                                    <th rowspan="2">T. de P.</th>
                                    <th rowspan="2">Precal.<br>(°C)</th>
                                    <th rowspan="2">Soldadura</th>
                                    <th colspan="3" style="background:#055a9e;">Soldadura</th>
                                    <th colspan="3" style="background:#055a9e;">Corriente</th>
                                    <th rowspan="2">Gas<br>Argón<br><small>(75-80 PSI)</small></th>
                                    <th rowspan="2">Vel.<br>Calc.</th>
                                    <th rowspan="2">Resultado</th>
                                    <th rowspan="2">Defecto</th>
                                    <th rowspan="2">Observaciones</th>
                                </tr>
                                <tr>
                                    <th style="width: 60px;">Medida</th>
                                    <th style="width: 25px;">Valor</th>
                                    <th>Inicial (POLI)</th>
                                    <th>Aplicada (POLS)</th>
                                    <th>Final (POLF)</th>
                                    <th>Inicial (CORI)</th>
                                    <th>Aplicada (CORS)</th>
                                    <th>Final (CORF)</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $tiposOrden = ['D_Conexion_pico', 'D_Conexion_obt', 'Perfilado'];
                                    $labelMedida = [
                                        'D_Conexion_pico' => 'D. Conexión pico',
                                        'D_Conexion_obt' => 'D. Conexión obt',
                                        'Perfilado' => 'Perfilado',
                                    ];
                                    $filaPrecal = $piezasGroup['D_Conexion_pico'] ?? null;
                                @endphp

                                @foreach ($tiposOrden as $loopIndex => $tipo)
                                    @php
                                        $fila = $piezasGroup[$tipo] ?? null;
                                        $esPrimera = ($loopIndex === 0);
                                        $claseFila = ($loopIndex % 2 === 0) ? 'grupo-par' : 'grupo-impar';
                                        if ($esPrimera)
                                            $claseFila .= ' fila-primera';
                                    @endphp
                                    <tr class="{{ $claseFila }} pta-row-sin-lib">
                                        @if ($esPrimera)
                                            <td class="td-pieza" rowspan="3">
                                                {{ $nPiezaSel }}
                                            </td>
                                        @endif
                                        <td class="td-tipo-medida">{{ $labelMedida[$tipo] ?? $tipo }}</td>
                                        <td>
                                            @php
                                                $campo = $tipo === 'D_Conexion_pico' ? 'd_conexion_pico'
                                                    : ($tipo === 'D_Conexion_obt' ? 'd_conexion_obt' : 'perfilado');
                                            @endphp
                                            {{ $fila?->$campo ?? '—' }}
                                        </td>
                                        <td>{{ $fila?->vl ?? '—' }}</td>
                                        <td>{{ $fila?->tipo_preparacion ?? '—' }}</td>
                                        @if ($esPrimera)
                                            <td rowspan="3" class="td-precal">{{ $filaPrecal?->precalentamiento ?? '—' }}</td>
                                            <td rowspan="3">{{ $filaPrecal?->material_soldadura ?? '—' }}</td>
                                        @endif
                                        <td>{{ $fila?->sold_inicial ?? '—' }}</td>
                                        <td>{{ $fila?->sold_aplicada ?? '—' }}</td>
                                        <td>{{ $fila?->sold_final ?? '—' }}</td>
                                        <td>{{ $fila?->corr_inicial ?? '—' }}</td>
                                        <td>{{ $fila?->corr_aplicada ?? '—' }}</td>
                                        <td>{{ $fila?->corr_final ?? '—' }}</td>
                                        <td>{{ $fila?->gas_argon ?? '—' }}</td>
                                        <td>{{ $fila?->velocidad_calculada ?? '—' }}</td>
                                        @php
                                            $resClass = '';
                                            if ($fila?->resultado === 'Bien' || $fila?->resultado === 'OK')
                                                $resClass = 'resultado-OK';
                                            elseif ($fila?->resultado === 'Mal' || $fila?->resultado === 'NOK')
                                                $resClass = 'resultado-NOK';

                                            $defClass = ($fila?->defecto_pta && $fila?->defecto_pta !== 'Ninguno') ? 'defecto-fund' : 'defecto-none';
                                        @endphp
                                        <td class="{{ $resClass }}">
                                            {{ $fila?->resultado ?? '—' }}
                                        </td>
                                        <td class="{{ $defClass }}">{{ $fila?->defecto_pta ?? '—' }}</td>
                                        @if ($esPrimera)
                                            <td rowspan="3">{{ $filaPrecal?->observaciones ?? '—' }}</td>
                                        @endif
                                    </tr>
                                @endforeach

                                @php
                                    $tipoP2Guardado = null;
                                    if ($p2Row?->p2_d_conexion_pico !== null)
                                        $tipoP2Guardado = 'D_Conexion_pico';
                                    elseif ($p2Row?->p2_d_conexion_obt !== null)
                                        $tipoP2Guardado = 'D_Conexion_obt';
                                    elseif ($p2Row?->p2_perfilado !== null)
                                        $tipoP2Guardado = 'Perfilado';
                                @endphp

                                <tr class="pta-captura-header">
                                    <td colspan="18">
                                        EDICIÓN DE SEGUNDA PASADA {{ $p2Act ? '(ACTUALIZAR)' : '(NUEVA)' }}
                                    </td>
                                </tr>
                                <tr class="fila-captura pta-row-sin-lib">
                                    <td class="td-pieza">
                                        <div class="pta-p2-td-pieza">
                                            <span>{{ $nPiezaSel }}</span>
                                            <span class="pta-p2-label-small">(2da)</span>
                                        </div>
                                    </td>
                                    <td class="td-tipo-medida" style="padding:0 !important;">
                                        <select name="p2_tipo_medida" id="p2TipoMedida" required onchange="onTipoMedidaChange()"
                                            class="pta-select">
                                            <option value="">— Medida —</option>
                                            <option value="D_Conexion_pico" {{ $tipoP2Guardado === 'D_Conexion_pico' ? 'selected' : '' }}>D. Conexión Pico</option>
                                            <option value="D_Conexion_obt" {{ $tipoP2Guardado === 'D_Conexion_obt' ? 'selected' : '' }}>D. Conexión Obt.</option>
                                            <option value="Perfilado" {{ $tipoP2Guardado === 'Perfilado' ? 'selected' : '' }}>
                                                Perfilado</option>
                                        </select>
                                    </td>
                                    <td style="padding:0;"><input type="number" step="0.001" name="p2_valor_principal"
                                            id="p2ValorPrincipal"
                                            value="{{ $p2Row?->p2_d_conexion_pico ?? $p2Row?->p2_d_conexion_obt ?? $p2Row?->p2_perfilado ?? '' }}"
                                            class="pta-input" placeholder="0.000" required></td>
                                    <td style="padding:0;"><input type="number" step="0.001" name="p2_vl"
                                            value="{{ $p2Row?->p2_vl ?? '' }}" class="pta-input" placeholder="0.000"></td>
                                     <td style="padding:0;">
                                         <select name="p2_tipo_preparacion" class="pta-select">
                                             <option value="">—</option>
                                             @foreach ($optsPreparacion as $optP2)
                                                 <option value="{{ $optP2 }}" {{ ($p2Row?->p2_tipo_preparacion ?? '') == $optP2 ? 'selected' : '' }}>{{ $optP2 }}</option>
                                             @endforeach
                                         </select>
                                     </td>
                                     <td style="padding:0;" class="td-precal"><input type="number" step="0.01"
                                             name="p2_precalentamiento" value="{{ $p2Row?->p2_precalentamiento ?? '' }}"
                                             class="pta-input" style="background:transparent; font-weight:bold;"
                                             placeholder="0.00"></td>
                                     <td style="padding:0;">
                                         @php
                                             $idWidgetP2S = 'mat_sold_p2_diferida';
                                             $nameFieldP2S = 'p2_material_soldadura';
                                             $currentValP2S = $p2Row?->material_soldadura ?? '';
                                             $optionsP2S = [
                                                 "COMMERSAL 23PSP",
                                                 "LSN 250-PL2",
                                                 "UNIMETAL 200",
                                                 "COLMONOY 42SA"
                                             ];
                                             $isOtroP2S = !empty($currentValP2S) && !in_array($currentValP2S, $optionsP2S);
                                         @endphp
                                         <div class="mat-sold-wrap">
                                             <select id="select_{{ $idWidgetP2S }}" 
                                                     class="pta-select mat-sold-select" 
                                                     style="display: {{ $isOtroP2S ? 'none' : 'block' }};"
                                                     onchange="handlePTAMaterialSelectChange('{{ $idWidgetP2S }}')"
                                                     @if(!$isOtroP2S) name="{{ $nameFieldP2S }}" @endif>
                                                 <option value="">— Seleccionar —</option>
                                                 @foreach ($optionsP2S as $opt)
                                                     <option value="{{ $opt }}" {{ $currentValP2S === $opt ? 'selected' : '' }}>{{ $opt }}</option>
                                                 @endforeach
                                                 <option value="__otro__" {{ $isOtroP2S ? 'selected' : '' }}>Otro...</option>
                                             </select>
                                             <div id="otro_wrap_{{ $idWidgetP2S }}" 
                                                  class="mat-sold-otro-wrap {{ $isOtroP2S ? 'visible' : '' }}" 
                                                  style="display: {{ $isOtroP2S ? 'flex' : 'none' }}; gap: 4px; width: 100%;">
                                                 <button type="button" 
                                                         class="mat-sold-btn-back" 
                                                         style="cursor: pointer;"
                                                         onclick="handlePTAMaterialBackClick('{{ $idWidgetP2S }}', '{{ $nameFieldP2S }}')">
                                                     ←
                                                 </button>
                                                 <input type="text" 
                                                        id="input_{{ $idWidgetP2S }}" 
                                                        class="pta-input mat-sold-input" 
                                                        placeholder="Escribir material..." 
                                                        maxlength="80" 
                                                        @if($isOtroP2S) name="{{ $nameFieldP2S }}" @else disabled @endif
                                                        value="{{ $isOtroP2S ? $currentValP2S : '' }}">
                                             </div>
                                         </div>
                                     </td>
                                    <td style="padding:0;"><input type="number" step="0.001" name="p2_sold_inicial"
                                            value="{{ $p2Row?->p2_sold_inicial ?? '' }}" class="pta-input" placeholder="0.000">
                                    </td>
                                    <td style="padding:0;"><input type="number" step="0.001" name="p2_sold_aplicada"
                                            value="{{ $p2Row?->p2_sold_aplicada ?? '' }}" class="pta-input" placeholder="0.000">
                                    </td>
                                    <td style="padding:0;"><input type="number" step="0.001" name="p2_sold_final"
                                            value="{{ $p2Row?->p2_sold_final ?? '' }}" class="pta-input" placeholder="0.000">
                                    </td>
                                    <td style="padding:0;"><input type="number" step="0.001" name="p2_corr_inicial"
                                            value="{{ $p2Row?->p2_corr_inicial ?? '' }}" class="pta-input" placeholder="0.000">
                                    </td>
                                    <td style="padding:0;"><input type="number" step="0.001" name="p2_corr_aplicada"
                                            value="{{ $p2Row?->p2_corr_aplicada ?? '' }}" class="pta-input" placeholder="0.000">
                                    </td>
                                    <td style="padding:0;"><input type="number" step="0.001" name="p2_corr_final"
                                            value="{{ $p2Row?->p2_corr_final ?? '' }}" class="pta-input" placeholder="0.000">
                                    </td>
                                    <td style="padding:0;"><input type="number" step="0.001" name="p2_gas_argon"
                                            value="{{ $p2Row?->p2_gas_argon ?? '' }}" class="pta-input" placeholder="0.000">
                                    </td>
                                    <td style="padding:0;"><input type="number" step="0.001" name="p2_velocidad_calculada"
                                            value="{{ $p2Row?->p2_velocidad_calculada ?? '' }}" class="pta-input"
                                            placeholder="0.000"></td>
                                    <td style="padding:0;">
                                        <select name="p2_resultado"
                                            class="pta-select {{ $p2Row?->p2_resultado === 'Mal' ? 'resultado-NOK' : ($p2Row?->p2_resultado === 'Bien' ? 'resultado-OK' : '') }}">
                                            <option value="">—</option>
                                            <option value="Bien" {{ $p2Row?->p2_resultado === 'Bien' ? 'selected' : '' }}>Bien
                                            </option>
                                            <option value="Mal" {{ $p2Row?->p2_resultado === 'Mal' ? 'selected' : '' }}>Mal
                                            </option>
                                        </select>
                                    </td>
                                    <td style="padding:0;">
                                        <select name="p2_defecto_pta"
                                            class="pta-select {{ ($p2Row?->p2_defecto_pta && $p2Row?->p2_defecto_pta !== 'Ninguno') ? 'defecto-fund' : '' }}">
                                            <option value="Ninguno" {{ ($p2Row?->p2_defecto_pta ?? 'Ninguno') === 'Ninguno' ? 'selected' : '' }}>Ninguno</option>
                                            <option value="Fundición" {{ ($p2Row?->p2_defecto_pta) === 'Fundición' ? 'selected' : '' }}>Fundición</option>
                                            {{-- <option value="Falta de fusion" {{ ($p2Row?->p2_defecto_pta) === 'Falta de
                                                fusion' ? 'selected' : '' }}>Falta de fusión</option>
                                            <option value="Porosidad" {{ ($p2Row?->p2_defecto_pta) === 'Porosidad' ? 'selected'
                                                : '' }}>Porosidad</option>
                                            <option value="Inclusiones" {{ ($p2Row?->p2_defecto_pta) === 'Inclusiones' ?
                                                'selected' : '' }}>Inclusiones</option>
                                            <option value="Goteo" {{ ($p2Row?->p2_defecto_pta) === 'Goteo' ? 'selected' : '' }}>
                                                Goteo</option>
                                            <option value="Grietas" {{ ($p2Row?->p2_defecto_pta) === 'Grietas' ? 'selected' : ''
                                                }}>Grietas</option>
                                            <option value="Rechupe" {{ ($p2Row?->p2_defecto_pta) === 'Rechupe' ? 'selected' : ''
                                                }}>Rechupe</option> --}}
                                        </select>
                                    </td>
                                    <td style="padding:0;">
                                        <textarea name="p2_observaciones" class="pta-input"
                                            placeholder="Obs...">{{ $p2Row?->p2_observaciones ?? '' }}</textarea>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <button type="submit" class="btn-guardar-pta">
                        Guardar 2da Pasada
                    </button>
                </form>
            </div>

        @elseif ($claseSeleccionadaId && $nPiezaSel)
            <div style="background:#fff8e1; border-radius:8px; padding:1.2rem; text-align:center; color:#f9a825;">
                No se encontraron datos para la pieza <strong>{{ $nPiezaSel }}</strong> en el proceso seleccionado.
            </div>
        @elseif ($claseSeleccionadaId)
            <div style="background:#e8f5e9; border-radius:8px; padding:1.2rem; text-align:center; color:#388e3c;">
                Selecciona una pieza para ver / editar la 2da pasada.
            </div>
        @else
            <div style="background:#e3f2fd; border-radius:8px; padding:1.2rem; text-align:center; color:#1565c0;">
                Selecciona OT y Clase para continuar.
            </div>
        @endif

    </div>

    <script>
        // ── Mapa de OT → clases (para recargar el select de clase por JS) ──
        const clasesMap = {
            @foreach ($otsConPTA as $otOpt)
                                    "{{ $otOpt->id }}": [
                @foreach ($otOpt->clases as $claseOpt)
                    { id: "{{ $claseOpt->id }}", nombre: "{{ addslashes($claseOpt->nombre) }}" },
                @endforeach
                                    ],
            @endforeach
                        };

        function onOtChange() {
            const otId = document.getElementById('sel-ot').value;
            const selCl = document.getElementById('sel-clase');
            const selPz = document.getElementById('sel-pieza');

            // Limpiar y deshabilitar clase y pieza
            selCl.innerHTML = '<option value="">— Seleccionar Clase —</option>';
            selPz.innerHTML = '<option value="">— Seleccionar Pieza —</option>';
            selCl.disabled = !otId;
            selPz.disabled = true;

            if (otId && clasesMap[otId]) {
                clasesMap[otId].forEach(c => {
                    const opt = document.createElement('option');
                    opt.value = c.id;
                    opt.textContent = c.nombre;
                    selCl.appendChild(opt);
                });
            }
        }

        function onClaseChange() {
            const otId = document.getElementById('sel-ot').value;
            const claseId = document.getElementById('sel-clase').value;
            const selPz = document.getElementById('sel-pieza');

            selPz.innerHTML = '<option value="">— Seleccionar Pieza —</option>';
            selPz.disabled = !claseId;

            if (claseId) {
                // Recargar encabezado con OT y clase para obtener las piezas
                window.location.href = `{{ route('pta.segunda_pasada') }}?ot_id=${otId}&clase_id=${claseId}`;
            }
        }

        function onPiezaChange() {
            const otId = document.getElementById('sel-ot').value;
            const claseId = document.getElementById('sel-clase').value;
            const nPieza = document.getElementById('sel-pieza').value;

            if (nPieza) {
                window.location.href = `{{ route('pta.segunda_pasada') }}?ot_id=${otId}&clase_id=${claseId}&n_pieza=${nPieza}`;
            }
        }

        // ── Tipo de medida → mostrar campo valor ──
        function onTipoMedidaChange() {
            const tipo = document.getElementById('p2TipoMedida').value;
            const wrap = document.getElementById('wrap-p2-valor');
            const input = document.getElementById('p2ValorPrincipal');

            if (wrap) {
                wrap.style.display = tipo ? 'block' : 'none';
            }

            // Actualizar label placeholder
            if (input) {
                if (tipo === 'D_Conexion_pico') input.placeholder = 'D. Conexión Pico (mm)';
                else if (tipo === 'D_Conexion_obt') input.placeholder = 'D. Conexión Obt. (mm)';
                else if (tipo === 'Perfilado') input.placeholder = 'Perfilado (mm)';
                else input.placeholder = '0.000';
            }
        }

        // ── Modal contraseña ──
        function abrirModal() {
            document.getElementById('modalPta').style.display = 'flex';
            setTimeout(() => document.getElementById('ptaPasswordInput').focus(), 100);
        }

        function cerrarModal() {
            document.getElementById('modalPta').style.display = 'none';
            document.getElementById('ptaPasswordInput').value = '';
            document.getElementById('ptaPassError').style.display = 'none';
        }

        function verificarPassword() {
            const pwd = document.getElementById('ptaPasswordInput').value;
            const otId = document.getElementById('sel-ot')?.value || '{{ $otSeleccionadaId ?? "" }}';

            fetch('{{ route('pta.verify_temp_password') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content
                        || '{{ csrf_token() }}'
                },
                body: JSON.stringify({ password: pwd, ot_id: otId })
            })
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        cerrarModal();
                        // Recargar la página para que session('pta_temp_auth') esté activa
                        window.location.reload();
                    } else {
                        document.getElementById('ptaPassError').style.display = 'block';
                    }
                })
                .catch(() => { document.getElementById('ptaPassError').style.display = 'block'; });
        }

        // Enter key en el input
        document.getElementById('ptaPasswordInput')?.addEventListener('keydown', e => {
            if (e.key === 'Enter') verificarPassword();
        });

        // Inicializar: si ya hay tipo de medida seleccionado, mostrar campo valor
        document.addEventListener('DOMContentLoaded', () => {
            const tipo = document.getElementById('p2TipoMedida');
            if (tipo && tipo.value) onTipoMedidaChange();
        });

        window.handlePTAMaterialSelectChange = function(idWidget) {
            const selectEl = document.getElementById('select_' + idWidget);
            const otroWrap = document.getElementById('otro_wrap_' + idWidget);
            const inputEl = document.getElementById('input_' + idWidget);

            if (selectEl.value === '__otro__') {
                const name = selectEl.name;
                selectEl.name = '';
                selectEl.style.display = 'none';
                
                otroWrap.style.display = 'flex';
                otroWrap.classList.add('visible');
                inputEl.name = name;
                inputEl.disabled = false;
                inputEl.focus();
            }
        };

        window.handlePTAMaterialBackClick = function(idWidget, originalName) {
            const selectEl = document.getElementById('select_' + idWidget);
            const otroWrap = document.getElementById('otro_wrap_' + idWidget);
            const inputEl = document.getElementById('input_' + idWidget);

            inputEl.name = '';
            inputEl.value = '';
            inputEl.disabled = true;
            otroWrap.style.display = 'none';
            otroWrap.classList.remove('visible');

            selectEl.name = originalName;
            selectEl.value = '';
            selectEl.style.display = 'block';
        };
    </script>
@endsection
