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
                <th rowspan="2">Número<br>(M/H)</th>

                {{-- Bloque Concepto --}}
                <th colspan="2" style="background:#055a9e;">Concepto</th>

                <th rowspan="2">VL</th>
                <th rowspan="2">T. de P.</th>
                <th rowspan="2">Precal.<br>(°C)</th>

                {{-- Bloque Soldadura --}}
                <th colspan="3" style="background:#055a9e;">Soldadura</th>

                {{-- Bloque Corriente --}}
                <th colspan="3" style="background:#055a9e;">Corriente</th>

                <th rowspan="2">Gas<br>Argón</th>
                <th rowspan="2">Vel.<br>Calc.</th>
                <th rowspan="2">Resultado</th>
                <th rowspan="2">Defecto</th>
                <th rowspan="2">Observaciones</th>
            </tr>
            <tr>
                {{-- Sub-cabeceras Concepto --}}
                <th>Medida</th>
                <th>Valor</th>

                {{-- Sub-cabeceras Soldadura --}}
                <th>Inicial</th>
                <th>Aplicada</th>
                <th>Final</th>
                {{-- Sub-cabeceras Corriente --}}
                <th>Inicial</th>
                <th>Aplicada</th>
                <th>Final</th>
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
                    $defectoVal      = $filaPrecal?->defecto_pta ?? 'Ninguno';

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
                    } elseif ($liberacionValor === -1) {
                        $claseColor = 'pta-row-error';     // Rojo — propagado por defecto de la mitad contraria
                    } elseif ($liberacionValor === 0 || $liberacionValor === null) {
                        // Sin liberación formal: color basado en defecto
                        $claseColor = ($defectoVal === 'Ninguno' || !$defectoVal)
                            ? 'pta-row-ok'    // Verde — OK sin liberar
                            : 'pta-row-error';// Rojo  — con defecto sin liberar
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
                                    class="pta-input" placeholder="0.000">
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
                                    value="{{ $fila?->vl ?? '' }}" class="pta-input" placeholder="0.000">
                            @else
                                {{ $fila?->vl ?? '—' }}
                            @endif
                        </td>

                        {{-- ── Tipo de preparación (select: 1,2,3) ── --}}
                        <td>
                            @if ($modo === 'captura')
                                <select name="tipo_preparacion[{{ $fila?->id ?? 'new_' . $nPieza . '_' . $tipo }}]"
                                    class="pta-select">
                                    <option value="">—</option>
                                    @foreach ([1, 2, 3] as $opt)
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
                                        placeholder="°C">
                                @else
                                    <strong>{{ $filaPrecal?->precalentamiento ?? '—' }}</strong>
                                    <br><small style="color:#888;">°C</small>
                                @endif
                            </td>
                        @endif

                        {{-- ── Soldadura Inicial ── --}}
                        <td>
                            @if ($modo === 'captura')
                                <input type="number" step="0.001"
                                    name="sold_inicial[{{ $fila?->id ?? 'new_' . $nPieza . '_' . $tipo }}]"
                                    value="{{ $fila?->sold_inicial ?? '' }}" class="pta-input" placeholder="0.000">
                            @else
                                {{ $fila?->sold_inicial ?? '—' }}
                            @endif
                        </td>

                        {{-- ── Soldadura Aplicada ── --}}
                        <td>
                            @if ($modo === 'captura')
                                <input type="number" step="0.001"
                                    name="sold_aplicada[{{ $fila?->id ?? 'new_' . $nPieza . '_' . $tipo }}]"
                                    value="{{ $fila?->sold_aplicada ?? '' }}" class="pta-input" placeholder="0.000">
                            @else
                                {{ $fila?->sold_aplicada ?? '—' }}
                            @endif
                        </td>

                        {{-- ── Soldadura Final ── --}}
                        <td>
                            @if ($modo === 'captura')
                                <input type="number" step="0.001"
                                    name="sold_final[{{ $fila?->id ?? 'new_' . $nPieza . '_' . $tipo }}]"
                                    value="{{ $fila?->sold_final ?? '' }}" class="pta-input" placeholder="0.000">
                            @else
                                {{ $fila?->sold_final ?? '—' }}
                            @endif
                        </td>

                        {{-- ── Corriente Inicial ── --}}
                        <td>
                            @if ($modo === 'captura')
                                <input type="number" step="0.001"
                                    name="corr_inicial[{{ $fila?->id ?? 'new_' . $nPieza . '_' . $tipo }}]"
                                    value="{{ $fila?->corr_inicial ?? '' }}" class="pta-input" placeholder="0.000">
                            @else
                                {{ $fila?->corr_inicial ?? '—' }}
                            @endif
                        </td>

                        {{-- ── Corriente Aplicada ── --}}
                        <td>
                            @if ($modo === 'captura')
                                <input type="number" step="0.001"
                                    name="corr_aplicada[{{ $fila?->id ?? 'new_' . $nPieza . '_' . $tipo }}]"
                                    value="{{ $fila?->corr_aplicada ?? '' }}" class="pta-input" placeholder="0.000">
                            @else
                                {{ $fila?->corr_aplicada ?? '—' }}
                            @endif
                        </td>

                        {{-- ── Corriente Final ── --}}
                        <td>
                            @if ($modo === 'captura')
                                <input type="number" step="0.001"
                                    name="corr_final[{{ $fila?->id ?? 'new_' . $nPieza . '_' . $tipo }}]"
                                    value="{{ $fila?->corr_final ?? '' }}" class="pta-input" placeholder="0.000">
                            @else
                                {{ $fila?->corr_final ?? '—' }}
                            @endif
                        </td>

                        {{-- ── Gas Argón ── --}}
                        <td>
                            @if ($modo === 'captura')
                                <input type="number" step="0.001"
                                    name="gas_argon[{{ $fila?->id ?? 'new_' . $nPieza . '_' . $tipo }}]"
                                    value="{{ $fila?->gas_argon ?? '' }}" class="pta-input" placeholder="0.000">
                            @else
                                {{ $fila?->gas_argon ?? '—' }}
                            @endif
                        </td>

                        {{-- ── Velocidad Calculada ── --}}
                        <td>
                            @if ($modo === 'captura')
                                <input type="number" step="0.001"
                                    name="velocidad_calculada[{{ $fila?->id ?? 'new_' . $nPieza . '_' . $tipo }}]"
                                    value="{{ $fila?->velocidad_calculada ?? '' }}" class="pta-input" placeholder="0.000">
                            @else
                                {{ $fila?->velocidad_calculada ?? '—' }}
                            @endif
                        </td>

                        {{-- ── Resultado ── --}}
                        <td>
                            @if ($modo === 'captura')
                                <select name="resultado[{{ $fila?->id ?? 'new_' . $nPieza . '_' . $tipo }}]" class="pta-select">
                                    <option value="">—</option>
                                    <option value="Bien" {{ ($fila?->resultado ?? '') === 'Bien' ? 'selected' : '' }}>Bien</option>
                                    <option value="Mal"  {{ ($fila?->resultado ?? '') === 'Mal'  ? 'selected' : '' }}>Mal</option>
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
                                <select name="defecto_pta[{{ $fila?->id ?? 'new_' . $nPieza . '_' . $tipo }}]" class="pta-select">
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
                                <td class="td-pieza" rowspan="3" style="font-size:14px; background:#055a9e !important;">
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
                                    value="{{ $filaA?->$campoA ?? '' }}" class="pta-input" placeholder="0.000">
                                <input type="hidden" name="piece_id[{{ $keyA }}]" value="{{ $filaA?->id ?? '' }}">
                                <input type="hidden" name="tipo_medida[{{ $keyA }}]" value="{{ $tipoA }}">
                                <input type="hidden" name="n_pieza_ref[{{ $keyA }}]" value="{{ $nPiezaA }}">
                            </td>

                            {{-- VL --}}
                            <td><input type="number" step="0.001" name="vl[{{ $keyA }}]" value="{{ $filaA?->vl ?? '' }}"
                                    class="pta-input" placeholder="0.000"></td>

                            {{-- Tipo preparación --}}
                            <td>
                                <select name="tipo_preparacion[{{ $keyA }}]" class="pta-select">
                                    <option value="">—</option>
                                    @foreach ([1, 2, 3] as $opt)
                                        <option value="{{ $opt }}" {{ ($filaA?->tipo_preparacion ?? '') == $opt ? 'selected' : '' }}>
                                            {{ $opt }}</option>
                                    @endforeach
                                </select>
                            </td>

                            {{-- Precalentamiento — rowspan=3 --}}
                            @if ($esPrimeraA)
                                <td class="td-precal" rowspan="3">
                                    <input type="number" step="0.01"
                                        name="precalentamiento[{{ $filaPrecalA?->id ?? 'new_' . $nPiezaA . '_precal' }}]"
                                        value="{{ $filaPrecalA?->precalentamiento ?? '' }}" class="pta-input" placeholder="°C">
                                </td>
                            @endif

                            {{-- Soldadura --}}
                            <td><input type="number" step="0.001" name="sold_inicial[{{ $keyA }}]"
                                    value="{{ $filaA?->sold_inicial ?? '' }}" class="pta-input" placeholder="0.000"></td>
                            <td><input type="number" step="0.001" name="sold_aplicada[{{ $keyA }}]"
                                    value="{{ $filaA?->sold_aplicada ?? '' }}" class="pta-input" placeholder="0.000"></td>
                            <td><input type="number" step="0.001" name="sold_final[{{ $keyA }}]"
                                    value="{{ $filaA?->sold_final ?? '' }}" class="pta-input" placeholder="0.000"></td>

                            {{-- Corriente --}}
                            <td><input type="number" step="0.001" name="corr_inicial[{{ $keyA }}]"
                                    value="{{ $filaA?->corr_inicial ?? '' }}" class="pta-input" placeholder="0.000"></td>
                            <td><input type="number" step="0.001" name="corr_aplicada[{{ $keyA }}]"
                                    value="{{ $filaA?->corr_aplicada ?? '' }}" class="pta-input" placeholder="0.000"></td>
                            <td><input type="number" step="0.001" name="corr_final[{{ $keyA }}]"
                                    value="{{ $filaA?->corr_final ?? '' }}" class="pta-input" placeholder="0.000"></td>

                            {{-- Gas argón --}}
                            <td><input type="number" step="0.001" name="gas_argon[{{ $keyA }}]"
                                    value="{{ $filaA?->gas_argon ?? '' }}" class="pta-input" placeholder="0.000"></td>

                            {{-- Velocidad calculada --}}
                            <td><input type="number" step="0.001" name="velocidad_calculada[{{ $keyA }}]"
                                    value="{{ $filaA?->velocidad_calculada ?? '' }}" class="pta-input" placeholder="0.000"></td>

                            {{-- Resultado --}}
                            <td>
                                <select name="resultado[{{ $keyA }}]" class="pta-select">
                                    <option value="">—</option>
                                    <option value="Bien" {{ ($filaA?->resultado ?? '') === 'Bien' ? 'selected' : '' }}>Bien</option>
                                    <option value="Mal"  {{ ($filaA?->resultado ?? '') === 'Mal'  ? 'selected' : '' }}>Mal</option>
                                </select>
                            </td>

                            {{-- Defecto --}}
                            <td>
                                <select name="defecto_pta[{{ $keyA }}]" class="pta-select">
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
                                        class="pta-input" rows="3" style="resize:vertical; min-width:110px;"
                                        placeholder="Observaciones...">{{ $filaPrecalA?->observaciones ?? '' }}</textarea>
                                </td>
                            @endif

                        </tr>
                    @endforeach
                @endforeach
            @endisset

        </tbody>
    </table>
</div>


