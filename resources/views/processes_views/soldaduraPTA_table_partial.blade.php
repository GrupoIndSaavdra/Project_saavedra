{{--
PARTIAL: soldaduraPTA_table_partial.blade.php
===============================================================
Tabla compleja de Soldadura PTA con estructura de 3 sub-filas por pieza:
Fila 1: D. Conexión pico ─┐
Fila 2: D. Conexión obt ├─ Comparten columna Precalentamiento (rowspan=3)
Fila 3: Perfilado ─┘

Variables requeridas:
$piezas ─ Collection de SoldaduraPTA_pza (agrupadas por n_pieza)
$modo ─ 'captura' | 'reporte' (controla si se muestran inputs o solo texto)
$metaId ─ (solo en modo captura) ID de la meta activa

Estructura de datos esperada de $piezas:
Colección agrupada por n_pieza. Cada grupo tiene 3 registros:
tipo_medida = 'D_Conexion_pico' | 'D_Conexion_obt' | 'Perfilado'
--}}

@vite(['resources/css/processes_views/soldaduraPTA_table_partial.css'])

<div class="pta-table-wrapper">
    <table class="pta-table">
        <thead>
            {{-- FILA 1: Cabeceras principales --}}
            <tr>
                <th rowspan="2">Número<br>({{ isset($esJuegoCompleto) && $esJuegoCompleto ? 'Juego' : 'M/H' }})</th>

                {{-- Bloque Concepto --}}
                <th colspan="2" style="background:#055a9e;">Concepto</th>

                <th rowspan="2">VL</th>
                <th rowspan="2">T. de P.</th>
                <th rowspan="2">Precal.<br>(°C)</th>
                <th rowspan="2">Soldadura</th>

                {{-- Bloque Soldadura --}}
                <th colspan="3" style="background:#055a9e;">Soldadura</th>

                {{-- Bloque Corriente --}}
                <th colspan="3" style="background:#055a9e;">Corriente</th>

                <th rowspan="2">Gas<br>Argón<br><small>(75-80 PSI)</small></th>
                <th rowspan="2">Vel.<br>Calc.</th>
                <th rowspan="2">Resultado</th>
                <th rowspan="2">Defecto</th>
                <th rowspan="2">Observaciones</th>
            </tr>
            <tr>
                {{-- Sub-cabeceras Concepto --}}
                <th style="width: 60px;">Medida</th>
                <th style="width: 25px;">Valor</th>

                {{-- Sub-cabeceras Soldadura --}}
                <th>Inicial (POLI)</th>
                <th>Aplicada (POLS)</th>
                <th>Final (POLF)</th>
                {{-- Sub-cabeceras Corriente --}}
                <th>Inicial (CORI)</th>
                <th>Aplicada (CORS)</th>
                <th>Final (CORF)</th>
            </tr>
        </thead>
        <tbody>

            @php
                /**
                 * $piezasGroup puede venir:
                 *   A) Pre-agrupado por el controlador (Collection agrupada por n_pieza)
                 *   B) Sin agrupar (Collection plana de SoldaduraPTA_pza) → agrupamos aquí
                 *
                 * Las variables $tiposOrden y $labelMedida son siempre iguales.
                 */
                $tiposOrden = ['D_Conexion_pico', 'D_Conexion_obt', 'Perfilado'];
                $labelMedida = [
                    'D_Conexion_pico' => 'D. Conexión pico',
                    'D_Conexion_obt' => 'D. Conexión obt',
                    'Perfilado' => 'Perfilado',
                ];
                $grupoIndex = 0;

                // Usar $piezasGroup si ya viene agrupado; si no, agrupar $piezas
                if (!isset($piezasGroup) || $piezasGroup === null || $piezasGroup->isEmpty()) {
                    $piezasGroup = isset($piezas) && $piezas instanceof \Illuminate\Support\Collection
                        ? $piezas->groupBy('n_pieza')
                        : collect();
                }

                // Fallback para mapa de liberación (cuando el partial se llama sin él)
                if (!isset($ptaLiberacion)) {
                    $ptaLiberacion = [];
                }

                if (!isset($modo)) {
                    $modo = 'captura';
                }

                // Obtener nombre de la clase para cargar dinámicamente opciones de preparación
                $claseNombreVal = $claseNombre ?? null;
                if (!$claseNombreVal) {
                    if (isset($claseSeleccionada)) {
                        $claseNombreVal = is_string($claseSeleccionada) ? $claseSeleccionada : ($claseSeleccionada->nombre ?? null);
                    } elseif (isset($clase)) {
                        $claseNombreVal = is_string($clase) ? explode(' ', $clase)[0] : ($clase->nombre ?? null);
                    }
                }
                if (!$claseNombreVal) {
                    $anyFila = null;
                    if (isset($piezasGroupActivas) && $piezasGroupActivas->isNotEmpty()) {
                        $anyFila = $piezasGroupActivas->first()->first();
                    } elseif (isset($piezasGroup) && $piezasGroup->isNotEmpty()) {
                        $anyFila = $piezasGroup->first()->first();
                    }
                    if ($anyFila && isset($anyFila->id_meta)) {
                        $metaTmp = \App\Models\Metas::find($anyFila->id_meta);
                        if ($metaTmp && $metaTmp->id_clase) {
                            $claseTmp = \App\Models\Clase::find($metaTmp->id_clase);
                            if ($claseTmp) {
                                $claseNombreVal = $claseTmp->nombre;
                            }
                        }
                    }
                }

                $optsPreparacion = ['P1', 'P2', 'P3'];
            @endphp

            @forelse ($piezasGroup as $nPieza => $subFilas)
                @php
                    $esPar = ($grupoIndex % 2 === 0);
                    $claseGrupo = $esPar ? 'grupo-par' : 'grupo-impar';
                    $grupoIndex++;

                    /*
                     * Indexar sub-filas por tipo_medida para acceso rápido.
                     * Si algún tipo no existe, usamos null (puede pasar con datos viejos).
                     */
                    $filasPorTipo = [];
                    foreach ($subFilas as $sf) {
                        $filasPorTipo[$sf->tipo_medida] = $sf;
                    }
                    // La fila portadora del precalentamiento (siempre D_Conexion_pico)
                    $filaPrecal = $filasPorTipo['D_Conexion_pico'] ?? null;

                    // ── Determinar clase de color por liberación (igual que en piezas normales) ──
                    $liberacionValor = $ptaLiberacion[$nPieza] ?? null;
                    $defectoVal = $filaPrecal?->defecto_pta ?? 'Ninguno';

                    if ($liberacionValor === 1) {
                        $claseColor = 'pta-row-liberada';  // Azul  — liberada por calidad
                    } elseif ($liberacionValor === 2) {
                        $claseColor = 'pta-row-rechazada'; // Rojo  — rechazada por calidad
                    } elseif ($liberacionValor === 3) {
                        $claseColor = 'pta-row-buena';     // Verde claro — buena sin liberación
                    } elseif ($liberacionValor === 4) {
                        $claseColor = 'pta-row-mala';      // Morado — mala sin liberación
                    } elseif ($liberacionValor === 5) {
                        $claseColor = 'pta-row-incompleta';// Amarillo — incompleta
                    } elseif ($liberacionValor === 0 || $liberacionValor === null) {
                        // Sin liberación formal: color neutro aunque haya defecto
                        $claseColor = 'pta-row-ok';
                    } else {
                        $claseColor = 'pta-row-sin-lib';
                    }
                @endphp

                @foreach ($tiposOrden as $loopIndex => $tipo)
                    @php
                        $fila = $filasPorTipo[$tipo] ?? null;
                        $esPrimera = ($loopIndex === 0);
                        $claseFila = $esPar ? 'grupo-par' : 'grupo-impar';
                        if ($esPrimera)
                            $claseFila .= ' fila-primera';
                    @endphp

                    <tr class="{{ $claseFila }} {{ $claseColor }}">

                        {{-- ── Columna NÚMERO: solo en la primera sub-fila, rowspan=3 ── --}}
                        @if ($esPrimera)
                            <td class="td-pieza" rowspan="3" style="font-size:14px;">
                                {{ $nPieza }}
                            </td>
                        @endif

                        {{-- ── Tipo de medida (label de sub-fila) ── --}}
                        <td class="td-tipo-medida">
                            {{ $labelMedida[$tipo] ?? $tipo }}
                        </td>

                        {{-- ── Valor principal de esa medida ── --}}
                        <td>
                            @if ($modo === 'captura')
                                <input type="number" step="0.001"
                                    name="{{ $tipo === 'D_Conexion_pico' ? 'd_conexion_pico' : ($tipo === 'D_Conexion_obt' ? 'd_conexion_obt' : 'perfilado') }}[{{ $fila?->id ?? 'new_' . $nPieza . '_' . $tipo }}]"
                                    value="{{ old('valor', $fila?->{$tipo === 'D_Conexion_pico' ? 'd_conexion_pico' : ($tipo === 'D_Conexion_obt' ? 'd_conexion_obt' : 'perfilado')} ?? '') }}"
                                    class="pta-input" placeholder="0.000" required>
                                <input type="hidden" name="piece_id[{{ $fila?->id ?? 'new_' . $nPieza . '_' . $tipo }}]"
                                    value="{{ $fila?->id ?? '' }}">
                                <input type="hidden" name="tipo_medida[{{ $fila?->id ?? 'new_' . $nPieza . '_' . $tipo }}]"
                                    value="{{ $tipo }}">
                                <input type="hidden" name="n_pieza_ref[{{ $fila?->id ?? 'new_' . $nPieza . '_' . $tipo }}]"
                                    value="{{ $nPieza }}">
                            @else
                                @php
                                    $campo = $tipo === 'D_Conexion_pico' ? 'd_conexion_pico'
                                        : ($tipo === 'D_Conexion_obt' ? 'd_conexion_obt'
                                            : 'perfilado');
                                @endphp
                                {{ $fila?->$campo ?? '—' }}
                            @endif
                        </td>

                        {{-- ── VL ── --}}
                        <td>
                            @if ($modo === 'captura')
                                <input type="number" step="0.001" name="vl[{{ $fila?->id ?? 'new_' . $nPieza . '_' . $tipo }}]"
                                    value="{{ $fila?->vl ?? '' }}" class="pta-input" placeholder="0.000" required>
                            @else
                                {{ $fila?->vl ?? '—' }}
                            @endif
                        </td>

                        {{-- ── Tipo de preparación (select: dinámico) ── --}}
                        <td>
                            @if ($modo === 'captura')
                                <select name="tipo_preparacion[{{ $fila?->id ?? 'new_' . $nPieza . '_' . $tipo }}]"
                                    class="pta-select" required>
                                    <option value="">—</option>
                                    @foreach ($optsPreparacion as $opt)
                                        <option value="{{ $opt }}" {{ ($fila?->tipo_preparacion ?? '') == $opt ? 'selected' : '' }}>
                                            {{ $opt }}
                                        </option>
                                    @endforeach
                                </select>
                            @else
                                {{ $fila?->tipo_preparacion ?? '—' }}
                            @endif
                        </td>

                        {{-- ── PRECALENTAMIENTO — rowspan=3, solo en la primera sub-fila ── --}}
                        @if ($esPrimera)
                            <td class="td-precal" rowspan="3">
                                @if ($modo === 'captura')
                                    <input type="number" step="0.01"
                                        name="precalentamiento[{{ $filaPrecal?->id ?? 'new_' . $nPieza . '_precal' }}]"
                                        value="{{ $filaPrecal?->precalentamiento ?? '' }}" class="pta-input" style="min-width:60px;"
                                        placeholder="°C" required>
                                @else
                                    <strong>{{ $filaPrecal?->precalentamiento ?? '—' }}</strong>
                                    <br><small style="color:#888;">°C</small>
                                @endif
                            </td>
                            <td class="td-precal" rowspan="3" style="min-width: 140px;">
                                @if ($modo === 'captura')
                                    @php
                                        $idWidget = 'mat_sold_' . ($filaPrecal?->id ?? 'new_' . $nPieza . '_D_Conexion_pico');
                                        $nameField = 'material_soldadura[' . ($filaPrecal?->id ?? 'new_' . $nPieza . '_D_Conexion_pico') . ']';
                                        $currentVal = $filaPrecal?->material_soldadura ?? '';
                                        $options = [
                                            "COMMERSAL 23PSP",
                                            "LSN 250-PL2",
                                            "UNIMETAL 200",
                                            "COLMONOY 42SA"
                                        ];
                                        $isOtro = !empty($currentVal) && !in_array($currentVal, $options);
                                    @endphp
                                    <div class="mat-sold-wrap">
                                        <select id="select_{{ $idWidget }}" 
                                                class="pta-select mat-sold-select" 
                                                style="display: {{ $isOtro ? 'none' : 'block' }};"
                                                onchange="handlePTAMaterialSelectChange('{{ $idWidget }}')"
                                                @if(!$isOtro) name="{{ $nameField }}" @endif>
                                            <option value="">— Seleccionar —</option>
                                            @foreach ($options as $opt)
                                                <option value="{{ $opt }}" {{ $currentVal === $opt ? 'selected' : '' }}>{{ $opt }}</option>
                                            @endforeach
                                            <option value="__otro__" {{ $isOtro ? 'selected' : '' }}>Otro...</option>
                                        </select>
                                        <div id="otro_wrap_{{ $idWidget }}" 
                                             class="mat-sold-otro-wrap {{ $isOtro ? 'visible' : '' }}" 
                                             style="display: {{ $isOtro ? 'flex' : 'none' }}; gap: 4px; width: 100%;">
                                            <button type="button" 
                                                    class="mat-sold-btn-back" 
                                                    style="cursor: pointer;"
                                                    onclick="handlePTAMaterialBackClick('{{ $idWidget }}', '{{ $nameField }}')">
                                                ←
                                            </button>
                                            <input type="text" 
                                                   id="input_{{ $idWidget }}" 
                                                   class="pta-input mat-sold-input" 
                                                   placeholder="Escribir material..." 
                                                   maxlength="80" 
                                                   @if($isOtro) name="{{ $nameField }}" @else disabled @endif
                                                   value="{{ $isOtro ? $currentVal : '' }}">
                                        </div>
                                    </div>
                                @else
                                    {{ $filaPrecal?->material_soldadura ?? '—' }}
                                @endif
                            </td>
                        @endif

                        {{-- ── Soldadura Inicial ── --}}
                        <td>
                            @if ($modo === 'captura')
                                <input type="number" step="0.001"
                                    name="sold_inicial[{{ $fila?->id ?? 'new_' . $nPieza . '_' . $tipo }}]"
                                    value="{{ $fila?->sold_inicial ?? '' }}" class="pta-input" placeholder="0.000" required>
                            @else
                                {{ $fila?->sold_inicial ?? '—' }}
                            @endif
                        </td>

                        {{-- ── Soldadura Aplicada ── --}}
                        <td>
                            @if ($modo === 'captura')
                                <input type="number" step="0.001"
                                    name="sold_aplicada[{{ $fila?->id ?? 'new_' . $nPieza . '_' . $tipo }}]"
                                    value="{{ $fila?->sold_aplicada ?? '' }}" class="pta-input" placeholder="0.000" required>
                            @else
                                {{ $fila?->sold_aplicada ?? '—' }}
                            @endif
                        </td>

                        {{-- ── Soldadura Final ── --}}
                        <td>
                            @if ($modo === 'captura')
                                <input type="number" step="0.001"
                                    name="sold_final[{{ $fila?->id ?? 'new_' . $nPieza . '_' . $tipo }}]"
                                    value="{{ $fila?->sold_final ?? '' }}" class="pta-input" placeholder="0.000" required>
                            @else
                                {{ $fila?->sold_final ?? '—' }}
                            @endif
                        </td>

                        {{-- ── Corriente Inicial ── --}}
                        <td>
                            @if ($modo === 'captura')
                                <input type="number" step="0.001"
                                    name="corr_inicial[{{ $fila?->id ?? 'new_' . $nPieza . '_' . $tipo }}]"
                                    value="{{ $fila?->corr_inicial ?? '' }}" class="pta-input" placeholder="0.000" required>
                            @else
                                {{ $fila?->corr_inicial ?? '—' }}
                            @endif
                        </td>

                        {{-- ── Corriente Aplicada ── --}}
                        <td>
                            @if ($modo === 'captura')
                                <input type="number" step="0.001"
                                    name="corr_aplicada[{{ $fila?->id ?? 'new_' . $nPieza . '_' . $tipo }}]"
                                    value="{{ $fila?->corr_aplicada ?? '' }}" class="pta-input" placeholder="0.000" required>
                            @else
                                {{ $fila?->corr_aplicada ?? '—' }}
                            @endif
                        </td>

                        {{-- ── Corriente Final ── --}}
                        <td>
                            @if ($modo === 'captura')
                                <input type="number" step="0.001"
                                    name="corr_final[{{ $fila?->id ?? 'new_' . $nPieza . '_' . $tipo }}]"
                                    value="{{ $fila?->corr_final ?? '' }}" class="pta-input" placeholder="0.000" required>
                            @else
                                {{ $fila?->corr_final ?? '—' }}
                            @endif
                        </td>

                        {{-- ── Gas Argón ── --}}
                        <td>
                            @if ($modo === 'captura')
                                <input type="number" step="0.001"
                                    name="gas_argon[{{ $fila?->id ?? 'new_' . $nPieza . '_' . $tipo }}]"
                                    value="{{ $fila?->gas_argon ?? '' }}" class="pta-input" placeholder="0.000" required>
                            @else
                                {{ $fila?->gas_argon ?? '—' }}
                            @endif
                        </td>

                        {{-- ── Velocidad Calculada ── --}}
                        <td>
                            @if ($modo === 'captura')
                                <input type="number" step="0.001"
                                    name="velocidad_calculada[{{ $fila?->id ?? 'new_' . $nPieza . '_' . $tipo }}]"
                                    value="{{ $fila?->velocidad_calculada ?? '' }}" class="pta-input" placeholder="0.000" required>
                            @else
                                {{ $fila?->velocidad_calculada ?? '—' }}
                            @endif
                        </td>

                        {{-- ── Resultado ── --}}
                        <td>
                            @if ($modo === 'captura')
                                <select name="resultado[{{ $fila?->id ?? 'new_' . $nPieza . '_' . $tipo }}]" class="pta-select"
                                    required>
                                    <option value="">—</option>
                                    <option value="Bien" {{ ($fila?->resultado ?? '') === 'Bien' ? 'selected' : '' }}>Bien</option>
                                    <option value="Mal" {{ ($fila?->resultado ?? '') === 'Mal' ? 'selected' : '' }}>Mal</option>
                                </select>
                            @else
                                @php $res = $fila?->resultado ?? '—'; @endphp
                                <span class="{{ $res === 'Bien' ? 'resultado-OK' : ($res !== '—' ? 'resultado-NOK' : '') }}">
                                    {{ $res }}
                                </span>
                            @endif
                        </td>

                        {{-- ── Defecto (select: Ninguno / Fundición) ── --}}
                        <td>
                            @if ($modo === 'captura')
                                <select name="defecto_pta[{{ $fila?->id ?? 'new_' . $nPieza . '_' . $tipo }}]" class="pta-select"
                                    required>
                                    <option value="Ninguno" {{ ($fila?->defecto_pta ?? 'Ninguno') === 'Ninguno' ? 'selected' : '' }}>
                                        Ninguno
                                    </option>
                                    <option value="Fundición" {{ ($fila?->defecto_pta ?? '') === 'Fundición' ? 'selected' : '' }}>
                                        Fundición
                                    </option>
                                </select>
                            @else
                                @php $def = $fila?->defecto_pta ?? 'Ninguno'; @endphp
                                <span class="{{ $def === 'Ninguno' ? 'defecto-none' : 'defecto-fund' }}">
                                    {{ $def }}
                                </span>
                            @endif
                        </td>

                        {{-- ── Observaciones — solo en primera sub-fila, rowspan=3 ── --}}
                        @if ($esPrimera)
                            <td rowspan="3" style="min-width:120px; text-align:left; padding: 6px;">
                                @if ($modo === 'captura')
                                    <textarea name="observaciones[{{ $filaPrecal?->id ?? 'new_' . $nPieza . '_obs' }}]"
                                        class="pta-input" rows="3" style="resize:vertical; min-width:110px;"
                                        placeholder="Observaciones...">{{ $filaPrecal?->observaciones ?? '' }}</textarea>
                                @else
                                    {{ $filaPrecal?->observaciones ?? '—' }}
                                @endif
                            </td>
                        @endif

                    </tr>
                @endforeach

                {{-- ─────────────────────────────────────────────────────────────────
                2DA PASADA (Historial)
                ────────────────────────────────────────────────────────────────── --}}
                @php
                    $filaP2H = $filasPorTipo['Segunda_Pasada'] ?? null;
                    if (!$filaP2H && isset($filasPorTipo['D_Conexion_pico']) && $filasPorTipo['D_Conexion_pico']->p2_activa) {
                        $filaP2H = $filasPorTipo['D_Conexion_pico'];
                    }
                    $p2YaActivaH = $filaP2H?->p2_activa ?? false;
                    $keyP2H = $nPieza;
                @endphp

                @if ($p2YaActivaH)
                    @php
                        $tipoP2GuardadoH = null;
                        if ($filaP2H?->p2_d_conexion_pico !== null)
                            $tipoP2GuardadoH = 'D_Conexion_pico';
                        elseif ($filaP2H?->p2_d_conexion_obt !== null)
                            $tipoP2GuardadoH = 'D_Conexion_obt';
                        elseif ($filaP2H?->p2_perfilado !== null)
                            $tipoP2GuardadoH = 'Perfilado';

                        $valorP2GuardadoH = match ($tipoP2GuardadoH) {
                            'D_Conexion_pico' => $filaP2H?->p2_d_conexion_pico,
                            'D_Conexion_obt' => $filaP2H?->p2_d_conexion_obt,
                            'Perfilado' => $filaP2H?->p2_perfilado,
                            default => null,
                        };
                        $p2IdUniqH = 'hist_p2_' . Str::slug((string) $nPieza, '_');
                    @endphp
                    {{-- Opcional fila de control checkbox para habilitar edición extra en historial --}}
                    @if ($modo === 'captura')
                        <tr style="background:#055a9e; border-top:2px solid #034a87;">
                            <td colspan="18" style="padding:.4rem .8rem;">
                                <label
                                    style="display:flex; align-items:center; gap:.5rem; cursor:pointer; font-size:.82rem; color:#fff;">
                                    <input type="checkbox" checked onchange="handleP2Checkbox('{{ $p2IdUniqH }}')"
                                        id="chk-p2-{{ $p2IdUniqH }}"
                                        style="width:15px; height:15px; cursor:pointer; accent-color:#fff;">
                                    <strong>2da Pasada (registrada)</strong>
                                </label>
                                <input type="hidden" name="p2_activa[{{ $keyP2H }}]" id="inp-p2-activa-{{ $p2IdUniqH }}" value="1">
                            </td>
                        </tr>
                    @endif

                    <tr id="row-p2-{{ $p2IdUniqH }}-0" class="fila-p2 {{ $claseColor }}"
                        style="border-bottom:1px solid #90b8e0;">
                        <td class="td-pieza" style="font-size:12px; background-color:#055a9e; color:#fff; font-weight:700;">
                            <div style="display:flex; flex-direction:column; align-items:center;">
                                <span>{{ $nPieza }}</span>
                                <span style="font-size:0.7rem; color:#ffeb3b; margin-top:2px;">(2da Pasada)</span>
                            </div>
                        </td>
                        <td class="td-tipo-medida" style="min-width:130px;">
                            @if ($modo === 'captura')
                                <select name="p2_tipo_medida[{{ $keyP2H }}]" class="pta-select"
                                    style="font-size:.78rem; color:#034a87; font-weight:600; width:100%;">
                                    <option value="">— Medida —</option>
                                    <option value="D_Conexion_pico" {{ $tipoP2GuardadoH === 'D_Conexion_pico' ? 'selected' : '' }}>D.
                                        Conexión Pico</option>
                                    <option value="D_Conexion_obt" {{ $tipoP2GuardadoH === 'D_Conexion_obt' ? 'selected' : '' }}>D.
                                        Conexión Obt.</option>
                                    <option value="Perfilado" {{ $tipoP2GuardadoH === 'Perfilado' ? 'selected' : '' }}>Perfilado
                                    </option>
                                </select>
                            @else
                                {{ $tipoP2GuardadoH ? ($tipoP2GuardadoH === 'D_Conexion_pico' ? 'D. Conexión Pico' : ($tipoP2GuardadoH === 'D_Conexion_obt' ? 'D. Conexión Obt.' : 'Perfilado')) : '—' }}
                            @endif
                        </td>
                        <td>
                            @if ($modo === 'captura')
                                <input type="number" step="0.001" name="p2_valor[{{ $keyP2H }}]"
                                    value="{{ $valorP2GuardadoH ?? '' }}" class="pta-input" placeholder="0.000">
                            @else
                                {{ $valorP2GuardadoH ?? '—' }}
                            @endif
                        </td>
                        <td>
                            @if ($modo === 'captura')
                                <input type="number" step="0.001" name="p2_vl[{{ $keyP2H }}]" value="{{ $filaP2H?->p2_vl ?? '' }}"
                                    class="pta-input" placeholder="0.000">
                            @else
                                {{ $filaP2H?->p2_vl ?? '—' }}
                            @endif
                        </td>
                        <td>
                            @if ($modo === 'captura')
                                <select name="p2_tipo_preparacion[{{ $keyP2H }}]" class="pta-select">
                                    <option value="">—</option>
                                    @foreach ($optsPreparacion as $optP2)
                                        <option value="{{ $optP2 }}" {{ ($filaP2H?->p2_tipo_preparacion ?? '') == $optP2 ? 'selected' : '' }}>{{ $optP2 }}</option>
                                    @endforeach
                                </select>
                            @else
                                {{ $filaP2H?->p2_tipo_preparacion ?? '—' }}
                            @endif
                        </td>
                        <td class="td-precal">
                            @if ($modo === 'captura')
                                <input type="number" step="0.01" name="p2_precalentamiento[{{ $keyP2H }}]"
                                    value="{{ $filaP2H?->p2_precalentamiento ?? '' }}" class="pta-input" placeholder="°C">
                            @else
                                <strong>{{ $filaP2H?->p2_precalentamiento ?? '—' }}</strong><br><small
                                    style="color:#888;">°C</small>
                            @endif
                        </td>
                        <td class="td-precal">
                            @if ($modo === 'captura')
                                @php
                                    $idWidgetP2H = 'mat_sold_p2_' . $keyP2H;
                                    $nameFieldP2H = 'p2_material_soldadura[' . $keyP2H . ']';
                                    $currentValP2H = $filaP2H?->material_soldadura ?? '';
                                    $optionsP2 = [
                                        "COMMERSAL 23PSP",
                                        "LSN 250-PL2",
                                        "UNIMETAL 200",
                                        "COLMONOY 42SA"
                                    ];
                                    $isOtroP2H = !empty($currentValP2H) && !in_array($currentValP2H, $optionsP2);
                                @endphp
                                <div class="mat-sold-wrap">
                                    <select id="select_{{ $idWidgetP2H }}" 
                                            class="pta-select mat-sold-select" 
                                            style="display: {{ $isOtroP2H ? 'none' : 'block' }};"
                                            onchange="handlePTAMaterialSelectChange('{{ $idWidgetP2H }}')"
                                            @if(!$isOtroP2H) name="{{ $nameFieldP2H }}" @endif>
                                        <option value="">— Seleccionar —</option>
                                        @foreach ($optionsP2 as $opt)
                                            <option value="{{ $opt }}" {{ $currentValP2H === $opt ? 'selected' : '' }}>{{ $opt }}</option>
                                        @endforeach
                                        <option value="__otro__" {{ $isOtroP2H ? 'selected' : '' }}>Otro...</option>
                                    </select>
                                    <div id="otro_wrap_{{ $idWidgetP2H }}" 
                                         class="mat-sold-otro-wrap {{ $isOtroP2H ? 'visible' : '' }}" 
                                         style="display: {{ $isOtroP2H ? 'flex' : 'none' }}; gap: 4px; width: 100%;">
                                        <button type="button" 
                                                class="mat-sold-btn-back" 
                                                style="cursor: pointer;"
                                                onclick="handlePTAMaterialBackClick('{{ $idWidgetP2H }}', '{{ $nameFieldP2H }}')">
                                            ←
                                        </button>
                                        <input type="text" 
                                               id="input_{{ $idWidgetP2H }}" 
                                               class="pta-input mat-sold-input" 
                                               placeholder="Escribir material..." 
                                               maxlength="80" 
                                               @if($isOtroP2H) name="{{ $nameFieldP2H }}" @else disabled @endif
                                               value="{{ $isOtroP2H ? $currentValP2H : '' }}">
                                    </div>
                                </div>
                            @else
                                {{ $filaP2H?->material_soldadura ?? '—' }}
                            @endif
                        </td>
                        <td>
                            @if ($modo === 'captura')
                                <input type="number" step="0.001" name="p2_sold_inicial[{{ $keyP2H }}]"
                                    value="{{ $filaP2H?->p2_sold_inicial ?? '' }}" class="pta-input" placeholder="0.000">
                            @else
                                {{ $filaP2H?->p2_sold_inicial ?? '—' }}
                            @endif
                        </td>
                        <td>
                            @if ($modo === 'captura')
                                <input type="number" step="0.001" name="p2_sold_aplicada[{{ $keyP2H }}]"
                                    value="{{ $filaP2H?->p2_sold_aplicada ?? '' }}" class="pta-input" placeholder="0.000">
                            @else
                                {{ $filaP2H?->p2_sold_aplicada ?? '—' }}
                            @endif
                        </td>
                        <td>
                            @if ($modo === 'captura')
                                <input type="number" step="0.001" name="p2_sold_final[{{ $keyP2H }}]"
                                    value="{{ $filaP2H?->p2_sold_final ?? '' }}" class="pta-input" placeholder="0.000">
                            @else
                                {{ $filaP2H?->p2_sold_final ?? '—' }}
                            @endif
                        </td>
                        <td>
                            @if ($modo === 'captura')
                                <input type="number" step="0.001" name="p2_corr_inicial[{{ $keyP2H }}]"
                                    value="{{ $filaP2H?->p2_corr_inicial ?? '' }}" class="pta-input" placeholder="0.000">
                            @else
                                {{ $filaP2H?->p2_corr_inicial ?? '—' }}
                            @endif
                        </td>
                        <td>
                            @if ($modo === 'captura')
                                <input type="number" step="0.001" name="p2_corr_aplicada[{{ $keyP2H }}]"
                                    value="{{ $filaP2H?->p2_corr_aplicada ?? '' }}" class="pta-input" placeholder="0.000">
                            @else
                                {{ $filaP2H?->p2_corr_aplicada ?? '—' }}
                            @endif
                        </td>
                        <td>
                            @if ($modo === 'captura')
                                <input type="number" step="0.001" name="p2_corr_final[{{ $keyP2H }}]"
                                    value="{{ $filaP2H?->p2_corr_final ?? '' }}" class="pta-input" placeholder="0.000">
                            @else
                                {{ $filaP2H?->p2_corr_final ?? '—' }}
                            @endif
                        </td>
                        <td>
                            @if ($modo === 'captura')
                                <input type="number" step="0.001" name="p2_gas_argon[{{ $keyP2H }}]"
                                    value="{{ $filaP2H?->p2_gas_argon ?? '' }}" class="pta-input" placeholder="0.000">
                            @else
                                {{ $filaP2H?->p2_gas_argon ?? '—' }}
                            @endif
                        </td>
                        <td>
                            @if ($modo === 'captura')
                                <input type="number" step="0.001" name="p2_velocidad_calculada[{{ $keyP2H }}]"
                                    value="{{ $filaP2H?->p2_velocidad_calculada ?? '' }}" class="pta-input" placeholder="0.000">
                            @else
                                {{ $filaP2H?->p2_velocidad_calculada ?? '—' }}
                            @endif
                        </td>
                        <td>
                            @if ($modo === 'captura')
                                <select name="p2_resultado[{{ $keyP2H }}]" class="pta-select">
                                    <option value="">—</option>
                                    <option value="Bien" {{ ($filaP2H?->p2_resultado ?? '') === 'Bien' ? 'selected' : '' }}>Bien
                                    </option>
                                    <option value="Mal" {{ ($filaP2H?->p2_resultado ?? '') === 'Mal' ? 'selected' : '' }}>Mal</option>
                                </select>
                            @else
                                @php $resP2 = $filaP2H?->p2_resultado ?? '—'; @endphp
                                <span class="{{ $resP2 === 'Bien' ? 'resultado-OK' : ($resP2 !== '—' ? 'resultado-NOK' : '') }}">
                                    {{ $resP2 }}
                                </span>
                            @endif
                        </td>
                        <td>
                            @if ($modo === 'captura')
                                <select name="p2_defecto_pta[{{ $keyP2H }}]" class="pta-select">
                                    <option value="Ninguno" {{ ($filaP2H?->p2_defecto_pta ?? 'Ninguno') === 'Ninguno' ? 'selected' : '' }}>Ninguno</option>
                                    <option value="Fundición" {{ ($filaP2H?->p2_defecto_pta ?? '') === 'Fundición' ? 'selected' : '' }}>Fundición</option>
                                </select>
                            @else
                                @php $defP2 = $filaP2H?->p2_defecto_pta ?? 'Ninguno'; @endphp
                                <span class="{{ $defP2 === 'Ninguno' ? 'defecto-none' : 'defecto-fund' }}">
                                    {{ $defP2 }}
                                </span>
                            @endif
                        </td>
                        <td style="min-width:120px; text-align:left; padding:6px;">
                            @if ($modo === 'captura')
                                <textarea name="p2_observaciones[{{ $keyP2H }}]" class="pta-input" rows="2"
                                    style="resize:vertical; min-width:110px;"
                                    placeholder="Obs. 2da pasada...">{{ $filaP2H?->p2_observaciones ?? '' }}</textarea>
                            @else
                                {{ $filaP2H?->p2_observaciones ?? '—' }}
                            @endif
                        </td>
                    </tr>
                @endif

            @empty
                @if (!isset($piezasGroupActivas) || $piezasGroupActivas->isEmpty())
                    <tr>
                        <td colspan="17" style="text-align:center; padding:20px; color:#888; font-style:italic;">
                            No hay registros de Soldadura PTA para esta pieza / OT.
                        </td>
                    </tr>
                @endif
            @endforelse

            {{-- ═══════════════════════════════════════════════════════
            SECCIÓN DE CAPTURA: piezas activas (estado=1)
            Se muestran siempre en modo captura con inputs editables.
            ═══════════════════════════════════════════════════════ --}}
            @isset($piezasGroupActivas)
                @foreach ($piezasGroupActivas as $nPiezaA => $subFilasA)
                    @php
                        $filasActivasPorTipo = [];
                        foreach ($subFilasA as $sf) {
                            $filasActivasPorTipo[$sf->tipo_medida] = $sf;
                        }
                        $filaPrecalA = $filasActivasPorTipo['D_Conexion_pico'] ?? null;
                    @endphp

                    {{-- Separador visual entre historial y captura --}}


                    @foreach ($tiposOrden as $loopIdxA => $tipoA)
                        @php
                            $filaA = $filasActivasPorTipo[$tipoA] ?? null;
                            $esPrimeraA = ($loopIdxA === 0);
                            $keyA = $filaA?->id ?? ('new_' . $nPiezaA . '_' . $tipoA);
                        @endphp

                        <tr style="background:#ffffff; border-top: {{ $esPrimeraA ? '2px solid #e6a800' : '1px solid #ccc' }};">

                            {{-- Número pieza — rowspan=3, solo primera sub-fila --}}
                            @if ($esPrimeraA)
                                <td class="td-pieza" rowspan="3" style="font-size:14px; background-color:#055a9e;">
                                    {{ $nPiezaA }}
                                </td>
                            @endif

                            {{-- Tipo medida --}}
                            <td class="td-tipo-medida">{{ $labelMedida[$tipoA] ?? $tipoA }}</td>

                            {{-- Valor principal --}}
                            <td>
                                @php
                                    $campoA = $tipoA === 'D_Conexion_pico' ? 'd_conexion_pico'
                                        : ($tipoA === 'D_Conexion_obt' ? 'd_conexion_obt' : 'perfilado');
                                @endphp
                                <input type="number" step="0.001" name="{{ $campoA }}[{{ $keyA }}]"
                                    value="{{ $filaA?->$campoA ?? '' }}" class="pta-input input-pieceUsed" placeholder="0.000" required>
                                <input type="hidden" name="piece_id[{{ $keyA }}]" value="{{ $filaA?->id ?? '' }}">
                                <input type="hidden" name="tipo_medida[{{ $keyA }}]" value="{{ $tipoA }}">
                                <input type="hidden" name="n_pieza_ref[{{ $keyA }}]" value="{{ $nPiezaA }}">
                            </td>

                            {{-- VL --}}
                            <td><input type="number" step="0.001" name="vl[{{ $keyA }}]" value="{{ $filaA?->vl ?? '' }}"
                                    class="pta-input input-pieceUsed" placeholder="0.000" required></td>

                            {{-- Tipo preparación --}}
                            <td>
                                <select name="tipo_preparacion[{{ $keyA }}]" class="pta-select input-pieceUsed" required>
                                    <option value="">—</option>
                                    @foreach ($optsPreparacion as $opt)
                                        <option value="{{ $opt }}" {{ ($filaA?->tipo_preparacion ?? '') == $opt ? 'selected' : '' }}>
                                            {{ $opt }}
                                        </option>
                                    @endforeach
                                </select>
                            </td>

                            {{-- Precalentamiento — rowspan=3 --}}
                            @if ($esPrimeraA)
                                <td class="td-precal" rowspan="3">
                                    <input type="number" step="0.01"
                                        name="precalentamiento[{{ $filaPrecalA?->id ?? 'new_' . $nPiezaA . '_precal' }}]"
                                        value="{{ $filaPrecalA?->precalentamiento ?? '' }}" class="pta-input input-pieceUsed" placeholder="°C" required>
                                </td>
                                <td class="td-precal" rowspan="3" style="min-width: 140px;">
                                    @php
                                        $idWidgetA = 'mat_sold_' . $keyA;
                                        $nameFieldA = 'material_soldadura[' . $keyA . ']';
                                        $currentValA = $filaPrecalA?->material_soldadura ?? '';
                                        $optionsA = [
                                            "COMMERSAL 23PSP",
                                            "LSN 250-PL2",
                                            "UNIMETAL 200",
                                            "COLMONOY 42SA"
                                        ];
                                        $isOtroA = !empty($currentValA) && !in_array($currentValA, $optionsA);
                                    @endphp
                                    <div class="mat-sold-wrap">
                                        <select id="select_{{ $idWidgetA }}" 
                                                class="pta-select mat-sold-select" 
                                                style="display: {{ $isOtroA ? 'none' : 'block' }};"
                                                onchange="handlePTAMaterialSelectChange('{{ $idWidgetA }}')"
                                                @if(!$isOtroA) name="{{ $nameFieldA }}" @endif>
                                            <option value="">— Seleccionar —</option>
                                            @foreach ($optionsA as $opt)
                                                <option value="{{ $opt }}" {{ $currentValA === $opt ? 'selected' : '' }}>{{ $opt }}</option>
                                            @endforeach
                                            <option value="__otro__" {{ $isOtroA ? 'selected' : '' }}>Otro...</option>
                                        </select>
                                        <div id="otro_wrap_{{ $idWidgetA }}" 
                                             class="mat-sold-otro-wrap {{ $isOtroA ? 'visible' : '' }}" 
                                             style="display: {{ $isOtroA ? 'flex' : 'none' }}; gap: 4px; width: 100%;">
                                            <button type="button" 
                                                    class="mat-sold-btn-back" 
                                                    style="cursor: pointer;"
                                                    onclick="handlePTAMaterialBackClick('{{ $idWidgetA }}', '{{ $nameFieldA }}')">
                                                ←
                                            </button>
                                            <input type="text" 
                                                   id="input_{{ $idWidgetA }}" 
                                                   class="pta-input mat-sold-input" 
                                                   placeholder="Escribir material..." 
                                                   maxlength="80" 
                                                   @if($isOtroA) name="{{ $nameFieldA }}" @else disabled @endif
                                                   value="{{ $isOtroA ? $currentValA : '' }}">
                                        </div>
                                    </div>
                                </td>
                            @endif

                            {{-- Soldadura --}}
                            <td><input type="number" step="0.001" name="sold_inicial[{{ $keyA }}]"
                                    value="{{ $filaA?->sold_inicial ?? '' }}" class="pta-input input-pieceUsed" placeholder="0.000" required></td>
                            <td><input type="number" step="0.001" name="sold_aplicada[{{ $keyA }}]"
                                    value="{{ $filaA?->sold_aplicada ?? '' }}" class="pta-input input-pieceUsed" placeholder="0.000" required></td>
                            <td><input type="number" step="0.001" name="sold_final[{{ $keyA }}]"
                                    value="{{ $filaA?->sold_final ?? '' }}" class="pta-input input-pieceUsed" placeholder="0.000" required></td>

                            {{-- Corriente --}}
                            <td><input type="number" step="0.001" name="corr_inicial[{{ $keyA }}]"
                                    value="{{ $filaA?->corr_inicial ?? '' }}" class="pta-input input-pieceUsed" placeholder="0.000" required></td>
                            <td><input type="number" step="0.001" name="corr_aplicada[{{ $keyA }}]"
                                    value="{{ $filaA?->corr_aplicada ?? '' }}" class="pta-input input-pieceUsed" placeholder="0.000" required></td>
                            <td><input type="number" step="0.001" name="corr_final[{{ $keyA }}]"
                                    value="{{ $filaA?->corr_final ?? '' }}" class="pta-input input-pieceUsed" placeholder="0.000" required></td>

                            {{-- Gas argón --}}
                            <td><input type="number" step="0.001" name="gas_argon[{{ $keyA }}]"
                                    value="{{ $filaA?->gas_argon ?? '' }}" class="pta-input input-pieceUsed" placeholder="0.000" required></td>

                            {{-- Velocidad calculada --}}
                            <td><input type="number" step="0.001" name="velocidad_calculada[{{ $keyA }}]"
                                    value="{{ $filaA?->velocidad_calculada ?? '' }}" class="pta-input input-pieceUsed" placeholder="0.000" required>
                            </td>

                            {{-- Resultado --}}
                            <td>
                                <select name="resultado[{{ $keyA }}]" class="pta-select input-pieceUsed" required>
                                    <option value="">—</option>
                                    <option value="Bien" {{ ($filaA?->resultado ?? '') === 'Bien' ? 'selected' : '' }}>Bien</option>
                                    <option value="Mal" {{ ($filaA?->resultado ?? '') === 'Mal' ? 'selected' : '' }}>Mal</option>
                                </select>
                            </td>

                            {{-- Defecto --}}
                            <td>
                                <select name="defecto_pta[{{ $keyA }}]" class="pta-select input-pieceUsed" required>
                                    <option value="Ninguno" {{ ($filaA?->defecto_pta ?? 'Ninguno') === 'Ninguno' ? 'selected' : '' }}>
                                        Ninguno</option>
                                    <option value="Fundición" {{ ($filaA?->defecto_pta ?? '') === 'Fundición' ? 'selected' : '' }}>
                                        Fundición</option>
                                </select>
                            </td>

                            {{-- Observaciones — rowspan=3 --}}
                            @if ($esPrimeraA)
                                <td rowspan="3" style="min-width:120px; text-align:left; padding:6px;">
                                    <textarea name="observaciones[{{ $filaPrecalA?->id ?? 'new_' . $nPiezaA . '_obs' }}]"
                                        class="pta-input input-pieceUsed" rows="3" style="resize:vertical; min-width:110px;"
                                        placeholder="Observaciones...">{{ $filaPrecalA?->observaciones ?? '' }}</textarea>
                                </td>
                            @endif

                        </tr>
                    @endforeach

                    {{-- ─────────────────────────────────────────────────────────────────
                    2DA PASADA (solo modo captura)
                    Aparece oculta; se muestra solo si el operador desbloquea con PTA2026.
                    ────────────────────────────────────────────────────────────────── --}}
                    @php
                        // Verificamos si existe la 4ta fila y si está activa
                        $filaP2Existente = $filasActivasPorTipo['Segunda_Pasada'] ?? null;

                        // Fallback temporal a D_Conexion_pico por si hay registros viejos sin migrar
                        if (!$filaP2Existente && isset($filasActivasPorTipo['D_Conexion_pico']) && $filasActivasPorTipo['D_Conexion_pico']->p2_activa) {
                            $filaP2Existente = $filasActivasPorTipo['D_Conexion_pico'];
                        }

                        $p2YaActiva = $filaP2Existente?->p2_activa ?? false;
                        $p2IdUniq = 'p2_' . Str::slug((string) $nPiezaA, '_');
                    @endphp

                    {{-- Fila de control: checkbox --}}
                    <tr id="row-p2-ctrl-{{ $p2IdUniq }}" style="background:#055a9e; border-top:2px solid #034a87;">
                        <td colspan="18" style="padding:.4rem .8rem;">
                            <label
                                style="display:flex; align-items:center; gap:1rem; cursor:pointer; font-size:1rem; color:#fff;">
                                <input type="checkbox" id="chk-p2-{{ $p2IdUniq }}" {{ $p2YaActiva ? 'checked' : '' }}
                                    onchange="handleP2Checkbox('{{ $p2IdUniq }}')"
                                    class="input-pieceUsed"
                                    style="width:20px; height:20px; cursor:pointer; margin-left: 5rem; accent-color:#fff;">
                                <strong>Aplicar 2da pasada</strong>
                                @if($p2YaActiva)
                                    <span style="color:#b3d4f5; font-size:.78rem;">(ya registrada — puedes editar los datos)</span>
                                @endif
                            </label>
                            {{-- Input oculto que envía '1' cuando se activa (se habilita por JS) --}}
                            <input type="hidden" name="p2_activa[{{ $nPiezaA }}]" id="inp-p2-activa-{{ $p2IdUniq }}"
                                value="{{ $p2YaActiva ? '1' : '0' }}">
                            {{-- Mensaje de error de contraseña (oculto por defecto) --}}
                            <span id="p2-err-{{ $p2IdUniq }}"
                                style="color:#ffcdd2; font-size:.78rem; display:none; margin-left:.5rem;">
                                Contraseña incorrecta.
                            </span>
                        </td>
                    </tr>

                    {{-- Fila única de 2da pasada --}}
                    @php
                        // En la nueva arquitectura, la 2da pasada se guarda como un 4to registro independiente
                        // con tipo_medida = 'Segunda_Pasada'
                        $filaP2 = $filasActivasPorTipo['Segunda_Pasada'] ?? null;

                        // Por seguridad si es viejo y estaba en D_Conexion_pico
                        if (!$filaP2 && isset($filasActivasPorTipo['D_Conexion_pico']) && $filasActivasPorTipo['D_Conexion_pico']->p2_activa) {
                            $filaP2 = $filasActivasPorTipo['D_Conexion_pico'];
                        }

                        // Usamos $nPiezaA (ej: '1M') como llave para los inputs de 2da pasada
                        // Esto hace muy fácil leerlos en el backend para todas las filas de esa pieza.
                        $keyP2 = $nPiezaA;

                        // Detectar qué tipo de medida ya viene guardado
                        $tipoP2Guardado = null;
                        if ($filaP2?->p2_d_conexion_pico !== null)
                            $tipoP2Guardado = 'D_Conexion_pico';
                        elseif ($filaP2?->p2_d_conexion_obt !== null)
                            $tipoP2Guardado = 'D_Conexion_obt';
                        elseif ($filaP2?->p2_perfilado !== null)
                            $tipoP2Guardado = 'Perfilado';

                        $valorP2Guardado = match ($tipoP2Guardado) {
                            'D_Conexion_pico' => $filaP2?->p2_d_conexion_pico,
                            'D_Conexion_obt' => $filaP2?->p2_d_conexion_obt,
                            'Perfilado' => $filaP2?->p2_perfilado,
                            default => null,
                        };
                    @endphp
                    <tr id="row-p2-{{ $p2IdUniq }}-0" class="fila-p2"
                        style="{{ !$p2YaActiva ? 'display:none;' : '' }} background:#e8f1fa; border-bottom:1px solid #90b8e0;">

                        {{-- Columna pieza --}}
                        <td class="td-pieza" style="font-size:15px; background-color:#055a9e; color:#fff; font-weight:700;">
                            {{ $nPiezaA }}<br><span style="font-size:10px;">2da P.</span>
                        </td>

                        {{-- SELECT: tipo de medida --}}
                        <td class="td-tipo-medida" style="min-width:130px;">
                            <select name="p2_tipo_medida[{{ $keyP2 }}]" class="pta-select input-pieceUsed"
                                style="font-size:1.2rem; color:#034a87; font-weight:600; width:100%;">
                                <option value="">— Medida —</option>
                                <option value="D_Conexion_pico" {{ $tipoP2Guardado === 'D_Conexion_pico' ? 'selected' : '' }}>D.
                                    Conexión Pico</option>
                                <option value="D_Conexion_obt" {{ $tipoP2Guardado === 'D_Conexion_obt' ? 'selected' : '' }}>D.
                                    Conexión Obt.</option>
                                <option value="Perfilado" {{ $tipoP2Guardado === 'Perfilado' ? 'selected' : '' }}>Perfilado
                                </option>
                            </select>
                        </td>

                        {{-- Valor de la medida seleccionada --}}
                        <td>
                            <input type="number" step="0.001" name="p2_valor[{{ $keyP2 }}]" value="{{ $valorP2Guardado ?? '' }}"
                                class="pta-input input-pieceUsed" placeholder="0.000">
                        </td>

                        {{-- VL --}}
                        <td><input type="number" step="0.001" name="p2_vl[{{ $keyP2 }}]" value="{{ $filaP2?->p2_vl ?? '' }}"
                                class="pta-input input-pieceUsed" placeholder="0.000"></td>

                        {{-- Tipo Preparación --}}
                        <td>
                            <select name="p2_tipo_preparacion[{{ $keyP2 }}]" class="pta-select input-pieceUsed">
                                <option value="">—</option>
                                @foreach ([1, 2, 3] as $optP2)
                                    <option value="{{ $optP2 }}" {{ ($filaP2?->p2_tipo_preparacion ?? '') == $optP2 ? 'selected' : '' }}>{{ $optP2 }}</option>
                                @endforeach
                            </select>
                        </td>

                        {{-- Precalentamiento --}}
                        <td class="td-precal">
                            <input type="number" step="0.01" name="p2_precalentamiento[{{ $keyP2 }}]"
                                value="{{ $filaP2?->p2_precalentamiento ?? '' }}" class="pta-input input-pieceUsed" placeholder="°C">
                        </td>
                        <td class="td-precal">
                            @php
                                $idWidgetP2A = 'mat_sold_p2_' . $keyP2;
                                $nameFieldP2A = 'p2_material_soldadura[' . $keyP2 . ']';
                                $currentValP2A = $filaP2?->material_soldadura ?? '';
                                $optionsP2A = [
                                    "COMMERSAL 23PSP",
                                    "LSN 250-PL2",
                                    "UNIMETAL 200",
                                    "COLMONOY 42SA"
                                ];
                                $isOtroP2A = !empty($currentValP2A) && !in_array($currentValP2A, $optionsP2A);
                            @endphp
                            <div class="mat-sold-wrap">
                                <select id="select_{{ $idWidgetP2A }}" 
                                        class="pta-select mat-sold-select input-pieceUsed" 
                                        style="display: {{ $isOtroP2A ? 'none' : 'block' }};"
                                        onchange="handlePTAMaterialSelectChange('{{ $idWidgetP2A }}')"
                                        @if(!$isOtroP2A) name="{{ $nameFieldP2A }}" @endif>
                                    <option value="">— Seleccionar —</option>
                                    @foreach ($optionsP2A as $opt)
                                        <option value="{{ $opt }}" {{ $currentValP2A === $opt ? 'selected' : '' }}>{{ $opt }}</option>
                                    @endforeach
                                    <option value="__otro__" {{ $isOtroP2A ? 'selected' : '' }}>Otro...</option>
                                </select>
                                <div id="otro_wrap_{{ $idWidgetP2A }}" 
                                     class="mat-sold-otro-wrap {{ $isOtroP2A ? 'visible' : '' }}" 
                                     style="display: {{ $isOtroP2A ? 'flex' : 'none' }}; gap: 4px; width: 100%;">
                                    <button type="button" 
                                            class="mat-sold-btn-back" 
                                            style="cursor: pointer;"
                                            onclick="handlePTAMaterialBackClick('{{ $idWidgetP2A }}', '{{ $nameFieldP2A }}')">
                                        ←
                                    </button>
                                    <input type="text" 
                                           id="input_{{ $idWidgetP2A }}" 
                                           class="pta-input mat-sold-input input-pieceUsed" 
                                           placeholder="Escribir material..." 
                                           maxlength="80" 
                                           @if($isOtroP2A) name="{{ $nameFieldP2A }}" @else disabled @endif
                                           value="{{ $isOtroP2A ? $currentValP2A : '' }}">
                                </div>
                            </div>
                        </td>

                        {{-- Soldadura --}}
                        <td><input type="number" step="0.001" name="p2_sold_inicial[{{ $keyP2 }}]"
                                value="{{ $filaP2?->p2_sold_inicial ?? '' }}" class="pta-input input-pieceUsed" placeholder="0.000"></td>
                        <td><input type="number" step="0.001" name="p2_sold_aplicada[{{ $keyP2 }}]"
                                value="{{ $filaP2?->p2_sold_aplicada ?? '' }}" class="pta-input input-pieceUsed" placeholder="0.000"></td>
                        <td><input type="number" step="0.001" name="p2_sold_final[{{ $keyP2 }}]"
                                value="{{ $filaP2?->p2_sold_final ?? '' }}" class="pta-input input-pieceUsed" placeholder="0.000"></td>

                        {{-- Corriente --}}
                        <td><input type="number" step="0.001" name="p2_corr_inicial[{{ $keyP2 }}]"
                                value="{{ $filaP2?->p2_corr_inicial ?? '' }}" class="pta-input input-pieceUsed" placeholder="0.000"></td>
                        <td><input type="number" step="0.001" name="p2_corr_aplicada[{{ $keyP2 }}]"
                                value="{{ $filaP2?->p2_corr_aplicada ?? '' }}" class="pta-input input-pieceUsed" placeholder="0.000"></td>
                        <td><input type="number" step="0.001" name="p2_corr_final[{{ $keyP2 }}]"
                                value="{{ $filaP2?->p2_corr_final ?? '' }}" class="pta-input input-pieceUsed" placeholder="0.000"></td>

                        {{-- Gas Argón --}}
                        <td><input type="number" step="0.001" name="p2_gas_argon[{{ $keyP2 }}]"
                                value="{{ $filaP2?->p2_gas_argon ?? '' }}" class="pta-input input-pieceUsed" placeholder="0.000"></td>

                        {{-- Velocidad Calculada --}}
                        <td><input type="number" step="0.001" name="p2_velocidad_calculada[{{ $keyP2 }}]"
                                value="{{ $filaP2?->p2_velocidad_calculada ?? '' }}" class="pta-input input-pieceUsed" placeholder="0.000"></td>

                        {{-- Resultado --}}
                        <td>
                            <select name="p2_resultado[{{ $keyP2 }}]" class="pta-select input-pieceUsed">
                                <option value="">—</option>
                                <option value="Bien" {{ ($filaP2?->p2_resultado ?? '') === 'Bien' ? 'selected' : '' }}>Bien
                                </option>
                                <option value="Mal" {{ ($filaP2?->p2_resultado ?? '') === 'Mal' ? 'selected' : '' }}>Mal</option>
                            </select>
                        </td>

                        {{-- Defecto --}}
                        <td>
                            <select name="p2_defecto_pta[{{ $keyP2 }}]" class="pta-select input-pieceUsed">
                                <option value="Ninguno" {{ ($filaP2?->p2_defecto_pta ?? 'Ninguno') === 'Ninguno' ? 'selected' : '' }}>Ninguno</option>
                                <option value="Fundición" {{ ($filaP2?->p2_defecto_pta ?? '') === 'Fundición' ? 'selected' : '' }}>Fundición</option>
                            </select>
                        </td>

                        {{-- Observaciones --}}
                        <td style="min-width:120px; text-align:left; padding:6px;">
                            <textarea name="p2_observaciones[{{ $keyP2 }}]" class="pta-input input-pieceUsed" rows="2"
                                style="resize:vertical; min-width:110px;"
                                placeholder="Obs. 2da pasada...">{{ $filaP2?->p2_observaciones ?? '' }}</textarea>
                        </td>
                    </tr>


                @endforeach
            @endisset

        </tbody>
    </table>
</div>


<script>
    /**
     * Maneja el checkbox de "Aplicar 2da pasada".
     * Toggle simple sin restriccion de contrasena: muestra u oculta las filas al instante.
     */
    window.handleP2Checkbox = function (p2Id) {
        const chk = document.getElementById('chk-p2-' + p2Id);
        const hdnAct = document.getElementById('inp-p2-activa-' + p2Id);

        if (!chk) return;

        const activate = chk.checked;
        window._setP2Rows(p2Id, activate);
        if (hdnAct) hdnAct.value = activate ? '1' : '0';
    };

    window._setP2Rows = function (p2Id, show) {
        [0, 1, 2].forEach(function (i) {
            const row = document.getElementById('row-p2-' + p2Id + '-' + i);
            if (row) row.style.display = show ? '' : 'none';
        });
    };

    /**
     * Lógica de coloreado en vivo para filas (Especialmente Historial P2 editable y Captura P2)
     */
    document.addEventListener('change', function (e) {
        if (e.target.name && (e.target.name.startsWith('p2_resultado') || e.target.name.startsWith('p2_defecto_pta') || e.target.name.startsWith('resultado') || e.target.name.startsWith('defecto_pta'))) {
            const tr = e.target.closest('tr');
            if (!tr) return;

            tr.classList.remove('pta-row-ok', 'pta-row-error', 'pta-row-liberada', 'pta-row-rechazada', 'pta-row-buena', 'pta-row-mala', 'pta-row-incompleta');

            // Determinar qué conjunto estamos evaluando
            let isP2 = e.target.name.startsWith('p2_');
            let resSelect = tr.querySelector(isP2 ? 'select[name^="p2_resultado"]' : 'select[name^="resultado"]');
            let defSelect = tr.querySelector(isP2 ? 'select[name^="p2_defecto_pta"]' : 'select[name^="defecto_pta"]');

            let resVal = resSelect ? resSelect.value : '';
            let defVal = defSelect ? defSelect.value : 'Ninguno';

            if (resVal === 'Mal' || (defVal !== 'Ninguno' && defVal !== '')) {
                tr.classList.add('pta-row-error');

                // Si es parte del bloque tripartito (primera sub-fila activa), pintar las dos sub-filas de abajo
                if (!isP2 && tr.classList.contains('fila-primera')) {
                    let current = tr.nextElementSibling;
                    for (let i = 0; i < 2; i++) {
                        if (current && !current.classList.contains('fila-p2')) {
                            current.classList.remove('pta-row-ok', 'pta-row-incompleta', 'pta-row-sin-lib');
                            current.classList.add('pta-row-error');
                            current = current.nextElementSibling;
                        }
                    }
                }
            } else if (resVal === 'Bien' && (defVal === 'Ninguno' || defVal === '')) {
                let allFilled = true;
                tr.querySelectorAll('input:not([type="hidden"]):not([type="checkbox"]), select').forEach(input => {
                    if (input.name && !input.name.includes('observaciones') && input.value.trim() === '') {
                        allFilled = false;
                    }
                });

                if (allFilled) {
                    tr.classList.add('pta-row-ok');
                } else {
                    tr.classList.add('pta-row-incompleta');
                }
            }
        }
    });

    /**
     * Revisar también cuando se teclean inputs normales para pasarlos a verde si ya se completó.
     */
    document.addEventListener('input', function (e) {
        if (e.target.tagName === 'INPUT' && !e.target.name.includes('observaciones')) {
            const tr = e.target.closest('tr');
            if (!tr) return;

            let isP2 = e.target.name.startsWith('p2_');
            let resSelect = tr.querySelector(isP2 ? 'select[name^="p2_resultado"]' : 'select[name^="resultado"]');
            let defSelect = tr.querySelector(isP2 ? 'select[name^="p2_defecto_pta"]' : 'select[name^="defecto_pta"]');

            let resVal = resSelect ? resSelect.value : '';
            let defVal = defSelect ? defSelect.value : 'Ninguno';

            if (resVal !== 'Mal' && (defVal === 'Ninguno' || defVal === '')) {
                let allFilled = true;
                tr.querySelectorAll('input:not([type="hidden"]):not([type="checkbox"]), select').forEach(input => {
                    if (input.name && !input.name.includes('observaciones') && input.value.trim() === '') {
                        allFilled = false;
                    }
                });

                tr.classList.remove('pta-row-ok', 'pta-row-error', 'pta-row-incompleta', 'pta-row-sin-lib');
                if (allFilled && resVal === 'Bien') {
                    tr.classList.add('pta-row-ok');
                } else if (allFilled) {
                    tr.classList.add('pta-row-incompleta');
                }
            }
        }
        }
    });
</script>