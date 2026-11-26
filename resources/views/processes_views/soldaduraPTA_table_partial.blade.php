{{--
PARTIAL: soldaduraPTA_table_partial.blade.php
===============================================================
Tabla simplificada de Soldadura PTA (1 fila por pieza + fila opcional de 2da pasada):
  1. Número (M/H o Juego)
  2. Estado (En correcto estado / En mal estado)
  3. Programa Utilizado (selector modal en DOCUMENTACION_GIS/PROGRAMAS_SOLDADURA_PTA)
  4. Observaciones
--}}

@vite(['resources/css/processes_views/soldaduraPTA_table_partial.css'])

@php
    $normalizePtaEstado = static function (?string $val): string {
        $v = trim((string) ($val ?? ''));
        return match ($v) {
            'Bien', 'En correcto estado' => 'En correcto estado',
            'Mal', 'En mal estado' => 'En mal estado',
            default => $v,
        };
    };

    if (!isset($piezasGroup) || $piezasGroup === null || $piezasGroup->isEmpty()) {
        $piezasGroup = isset($piezas) && $piezas instanceof \Illuminate\Support\Collection
            ? $piezas->groupBy('n_pieza')
            : collect();
    }

    $ptaLiberacion = $ptaLiberacion ?? [];
    $modo = $modo ?? 'captura';
    $activeProgGlobal = $ptaActiveProgram ?? null;
@endphp

<div class="pta-table-wrapper"
    data-pta-process-id="{{ $ptaProcessId ?? '' }}"
    data-pta-ot-id="{{ $ptaOtId ?? '' }}"
    data-pta-clase-id="{{ $ptaClaseId ?? '' }}"
    data-pta-ot-label="{{ $ptaOtLabel ?? '' }}"
    data-pta-clase-label="{{ $ptaClaseLabel ?? '' }}"
    data-pta-active-program="{{ $activeProgGlobal ?? '' }}">
    <table class="pta-table pta-table-simple">
        <thead>
            <tr>
                <th style="width: 115px;">Número<br>({{ isset($esJuegoCompleto) && $esJuegoCompleto ? 'Juego' : 'M/H' }})</th>
                <th style="width: 220px;">Estado</th>
                <th style="width: 280px;">Programa Utilizado</th>
                <th>Observaciones</th>
            </tr>
        </thead>
        <tbody>

            @forelse ($piezasGroup as $nPieza => $subFilas)
                @php
                    $filasPorTipo = [];
                    foreach ($subFilas as $sf) {
                        $filasPorTipo[$sf->tipo_medida] = $sf;
                    }
                    $filaPrincipal = $filasPorTipo['D_Conexion_pico']
                        ?? $subFilas->firstWhere('tipo_medida', '!=', 'Segunda_Pasada')
                        ?? $subFilas->first();

                    $liberacionValor = $ptaLiberacion[$nPieza] ?? null;
                    if ($liberacionValor === 1) {
                        $claseColor = 'pta-row-liberada';
                    } elseif ($liberacionValor === 2) {
                        $claseColor = 'pta-row-rechazada';
                    } elseif ($liberacionValor === 3) {
                        $claseColor = 'pta-row-buena';
                    } elseif ($liberacionValor === 4) {
                        $claseColor = 'pta-row-mala';
                    } elseif ($liberacionValor === 5) {
                        $claseColor = 'pta-row-incompleta';
                    } elseif ($liberacionValor === 0 || $liberacionValor === null) {
                        $claseColor = 'pta-row-ok';
                    } else {
                        $claseColor = 'pta-row-sin-lib';
                    }

                    $keyH = $filaPrincipal?->id ?? ('new_' . $nPieza . '_D_Conexion_pico');
                    $estadoH = $normalizePtaEstado($filaPrincipal?->resultado);
                    $progH = $filaPrincipal?->programa_pta ?: '';
                    $progNameH = $progH !== '' ? basename($progH) : '';
                @endphp

                <tr class="fila-primera {{ $claseColor }}">
                    <td class="td-pieza sol-font-size-14px">
                        {{ $nPieza }}
                    </td>

                    <td>
                        @if ($modo === 'captura')
                            <select name="resultado[{{ $keyH }}]" class="pta-select" required>
                                <option value="">— Seleccionar —</option>
                                <option value="En correcto estado" {{ $estadoH === 'En correcto estado' ? 'selected' : '' }}>En correcto estado</option>
                                <option value="En mal estado" {{ $estadoH === 'En mal estado' ? 'selected' : '' }}>En mal estado</option>
                            </select>
                            <input type="hidden" name="piece_id[{{ $keyH }}]" value="{{ $filaPrincipal?->id ?? '' }}">
                            <input type="hidden" name="tipo_medida[{{ $keyH }}]" value="{{ $filaPrincipal?->tipo_medida ?? 'D_Conexion_pico' }}">
                            <input type="hidden" name="n_pieza_ref[{{ $keyH }}]" value="{{ $nPieza }}">
                        @else
                            @if ($estadoH !== '')
                                <span class="{{ $estadoH === 'En correcto estado' ? 'resultado-OK' : 'resultado-NOK' }}">
                                    {{ $estadoH }}
                                </span>
                            @else
                                —
                            @endif
                        @endif
                    </td>

                    <td>
                        @if ($modo === 'captura')
                            <input type="hidden" name="programa_pta[{{ $keyH }}]" class="pta-programa-input" value="{{ $progH }}">
                            <button type="button"
                                class="pta-prog-btn {{ $progH !== '' ? 'has-program' : '' }}"
                                onclick="window.openPtaProgramModal(this)"
                                title="{{ $progH !== '' ? $progH : 'Seleccionar programa de Soldadura PTA' }}">
                                <svg class="pta-prog-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                                    <polyline points="14 2 14 8 20 8"></polyline>
                                    <line x1="16" y1="13" x2="8" y2="13"></line>
                                    <line x1="16" y1="17" x2="8" y2="17"></line>
                                </svg>
                                <span class="pta-prog-btn-label">{{ $progNameH !== '' ? $progNameH : 'Seleccionar programa' }}</span>
                            </button>
                        @else
                            @if ($progH !== '')
                                <span class="pta-prog-readonly" title="{{ $progH }}">{{ $progNameH }}</span>
                            @else
                                —
                            @endif
                        @endif
                    </td>

                    <td class="sol-text-align-left sol-padding-6px">
                        @if ($modo === 'captura')
                            <textarea name="observaciones[{{ $keyH }}]"
                                class="pta-input sol-resize-vertical" rows="2"
                                placeholder="Observaciones...">{{ $filaPrincipal?->observaciones ?? '' }}</textarea>
                        @else
                            {{ $filaPrincipal?->observaciones ?: '—' }}
                        @endif
                    </td>
                </tr>

                {{-- ── 2da Pasada (Historial) ── --}}
                @php
                    $filaP2H = $filasPorTipo['Segunda_Pasada'] ?? null;
                    if (!$filaP2H && isset($filasPorTipo['D_Conexion_pico']) && $filasPorTipo['D_Conexion_pico']->p2_activa) {
                        $filaP2H = $filasPorTipo['D_Conexion_pico'];
                    }
                    $p2YaActivaH = $filaP2H?->p2_activa ?? false;
                    $keyP2H = $nPieza;
                    $p2IdUniqH = 'hist_p2_' . Str::slug((string) $nPieza, '_');
                    $p2EstadoH = $normalizePtaEstado($filaP2H?->p2_resultado);
                    $p2ProgH = $filaP2H?->p2_programa_pta ?: $progH;
                    $p2ProgNameH = $p2ProgH !== '' ? basename($p2ProgH) : '';
                @endphp

                @if ($p2YaActivaH)
                    @if ($modo === 'captura')
                        <tr class="sol-background-055a9e sol-border-top-2px-solid-034a87">
                            <td colspan="4" class="sol-padding-4rem-8rem sol-text-align-left">
                                <label class="sol-display-flex sol-align-items-center sol-gap-5rem sol-cursor-pointer sol-font-size-82rem sol-color-fff">
                                    <input type="checkbox" checked onchange="handleP2Checkbox('{{ $p2IdUniqH }}')"
                                        id="chk-p2-{{ $p2IdUniqH }}"
                                        class="sol-width-15px sol-height-15px sol-cursor-pointer sol-accent-color-fff">
                                    <strong>2da Pasada (registrada)</strong>
                                </label>
                                <input type="hidden" name="p2_activa[{{ $keyP2H }}]" id="inp-p2-activa-{{ $p2IdUniqH }}" value="1">
                            </td>
                        </tr>
                    @endif

                    <tr id="row-p2-{{ $p2IdUniqH }}-0" class="fila-p2 {{ $claseColor }} sol-border-bottom-1px-solid-90b8e0">
                        <td class="td-pieza sol-font-size-12px sol-background-color-055a9e sol-color-fff sol-font-weight-700">
                            <div class="sol-display-flex sol-flex-direction-column sol-align-items-center">
                                <span>{{ $nPieza }}</span>
                                <span class="sol-font-size-0-7rem sol-color-ffeb3b sol-margin-top-2px">(2da Pasada)</span>
                            </div>
                        </td>

                        <td>
                            @if ($modo === 'captura')
                                <select name="p2_resultado[{{ $keyP2H }}]" class="pta-select">
                                    <option value="">— Seleccionar —</option>
                                    <option value="En correcto estado" {{ $p2EstadoH === 'En correcto estado' ? 'selected' : '' }}>En correcto estado</option>
                                    <option value="En mal estado" {{ $p2EstadoH === 'En mal estado' ? 'selected' : '' }}>En mal estado</option>
                                </select>
                            @else
                                @if ($p2EstadoH !== '')
                                    <span class="{{ $p2EstadoH === 'En correcto estado' ? 'resultado-OK' : 'resultado-NOK' }}">
                                        {{ $p2EstadoH }}
                                    </span>
                                @else
                                    —
                                @endif
                            @endif
                        </td>

                        <td>
                            @if ($modo === 'captura')
                                <input type="hidden" name="p2_programa_pta[{{ $keyP2H }}]" class="pta-programa-input pta-programa-p2-input" value="{{ $p2ProgH }}">
                                <button type="button"
                                    class="pta-prog-btn {{ $p2ProgH !== '' ? 'has-program' : '' }}"
                                    data-is-p2="1"
                                    onclick="window.openPtaProgramModal(this)"
                                    title="{{ $p2ProgH !== '' ? $p2ProgH : 'Seleccionar programa para 2da pasada' }}">
                                    <svg class="pta-prog-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                                        <polyline points="14 2 14 8 20 8"></polyline>
                                        <line x1="16" y1="13" x2="8" y2="13"></line>
                                        <line x1="16" y1="17" x2="8" y2="17"></line>
                                    </svg>
                                    <span class="pta-prog-btn-label">{{ $p2ProgNameH !== '' ? $p2ProgNameH : 'Seleccionar programa' }}</span>
                                </button>
                            @else
                                @if ($p2ProgH !== '')
                                    <span class="pta-prog-readonly" title="{{ $p2ProgH }}">{{ $p2ProgNameH }}</span>
                                @else
                                    —
                                @endif
                            @endif
                        </td>

                        <td class="sol-text-align-left sol-padding-6px">
                            @if ($modo === 'captura')
                                <textarea name="p2_observaciones[{{ $keyP2H }}]" class="pta-input sol-resize-vertical"
                                    rows="2" placeholder="Obs. 2da pasada...">{{ $filaP2H?->p2_observaciones ?? '' }}</textarea>
                            @else
                                {{ $filaP2H?->p2_observaciones ?: '—' }}
                            @endif
                        </td>
                    </tr>
                @endif

            @empty
                @if (!isset($piezasGroupActivas) || $piezasGroupActivas->isEmpty())
                    <tr>
                        <td colspan="4" class="sol-text-align-center sol-padding-20px sol-color-888 sol-font-style-italic">
                            No hay registros de Soldadura PTA para esta pieza / OT.
                        </td>
                    </tr>
                @endif
            @endforelse

            {{-- ═══════════════════════════════════════════════════════
            SECCIÓN DE CAPTURA: piezas activas (estado=1)
            ═══════════════════════════════════════════════════════ --}}
            @isset($piezasGroupActivas)
                @foreach ($piezasGroupActivas as $nPiezaA => $subFilasA)
                    @php
                        $filasActivasPorTipo = [];
                        foreach ($subFilasA as $sf) {
                            $filasActivasPorTipo[$sf->tipo_medida] = $sf;
                        }
                        $filaA = $filasActivasPorTipo['D_Conexion_pico'] ?? $subFilasA->first();
                        $keyA = $filaA?->id ?? ('new_' . $nPiezaA . '_D_Conexion_pico');
                        $estadoA = $normalizePtaEstado($filaA?->resultado);
                        $progA = $filaA?->programa_pta ?: ($activeProgGlobal ?: '');
                        $progNameA = $progA !== '' ? basename($progA) : '';
                    @endphp

                    <tr style="background:#ffffff; border-top: 2px solid #e6a800;">
                        <td class="td-pieza sol-font-size-14px sol-background-color-055a9e">
                            {{ $nPiezaA }}
                        </td>

                        <td>
                            <select name="resultado[{{ $keyA }}]" class="pta-select input-pieceUsed" required>
                                <option value="">— Seleccionar —</option>
                                <option value="En correcto estado" {{ $estadoA === 'En correcto estado' ? 'selected' : '' }}>En correcto estado</option>
                                <option value="En mal estado" {{ $estadoA === 'En mal estado' ? 'selected' : '' }}>En mal estado</option>
                            </select>
                            <input type="hidden" name="piece_id[{{ $keyA }}]" value="{{ $filaA?->id ?? '' }}">
                            <input type="hidden" name="tipo_medida[{{ $keyA }}]" value="D_Conexion_pico">
                            <input type="hidden" name="n_pieza_ref[{{ $keyA }}]" value="{{ $nPiezaA }}">
                        </td>

                        <td>
                            <input type="hidden" name="programa_pta[{{ $keyA }}]" class="pta-programa-input input-pieceUsed" value="{{ $progA }}">
                            <button type="button"
                                class="pta-prog-btn {{ $progA !== '' ? 'has-program' : '' }}"
                                onclick="window.openPtaProgramModal(this)"
                                title="{{ $progA !== '' ? $progA : 'Seleccionar programa de Soldadura PTA' }}">
                                <svg class="pta-prog-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                                    <polyline points="14 2 14 8 20 8"></polyline>
                                    <line x1="16" y1="13" x2="8" y2="13"></line>
                                    <line x1="16" y1="17" x2="8" y2="17"></line>
                                </svg>
                                <span class="pta-prog-btn-label">{{ $progNameA !== '' ? $progNameA : 'Seleccionar programa' }}</span>
                            </button>
                        </td>

                        <td class="sol-text-align-left sol-padding-6px">
                            <textarea name="observaciones[{{ $keyA }}]"
                                class="pta-input input-pieceUsed sol-resize-vertical" rows="2"
                                placeholder="Observaciones...">{{ $filaA?->observaciones ?? '' }}</textarea>
                        </td>
                    </tr>

                    {{-- ── 2da Pasada (Captura) ── --}}
                    @php
                        $filaP2Existente = $filasActivasPorTipo['Segunda_Pasada'] ?? null;
                        if (!$filaP2Existente && isset($filasActivasPorTipo['D_Conexion_pico']) && $filasActivasPorTipo['D_Conexion_pico']->p2_activa) {
                            $filaP2Existente = $filasActivasPorTipo['D_Conexion_pico'];
                        }
                        $p2YaActiva = $filaP2Existente?->p2_activa ?? false;
                        $p2IdUniq = 'p2_' . Str::slug((string) $nPiezaA, '_');
                        $keyP2 = $nPiezaA;
                        $estadoP2A = $normalizePtaEstado($filaP2Existente?->p2_resultado);
                        $progP2A = $filaP2Existente?->p2_programa_pta ?: $progA;
                        $progNameP2A = $progP2A !== '' ? basename($progP2A) : '';
                    @endphp

                    <tr id="row-p2-ctrl-{{ $p2IdUniq }}" class="sol-background-055a9e sol-border-top-2px-solid-034a87">
                        <td colspan="4" class="sol-padding-4rem-8rem sol-text-align-left">
                            <label class="sol-display-flex sol-align-items-center sol-gap-1rem sol-cursor-pointer sol-font-size-1rem sol-color-fff">
                                <input type="checkbox" id="chk-p2-{{ $p2IdUniq }}" {{ $p2YaActiva ? 'checked' : '' }}
                                    onchange="handleP2Checkbox('{{ $p2IdUniq }}')" class="input-pieceUsed sol-width-20px sol-height-20px sol-cursor-pointer sol-margin-left-5rem sol-accent-color-fff">
                                <strong>Aplicar 2da pasada</strong>
                                @if($p2YaActiva)
                                    <span class="sol-color-b3d4f5 sol-font-size-78rem">(ya registrada — puedes editar los datos)</span>
                                @endif
                            </label>
                            <input type="hidden" name="p2_activa[{{ $nPiezaA }}]" id="inp-p2-activa-{{ $p2IdUniq }}"
                                value="{{ $p2YaActiva ? '1' : '0' }}">
                        </td>
                    </tr>

                    <tr id="row-p2-{{ $p2IdUniq }}-0" class="fila-p2"
                        style="{{ !$p2YaActiva ? 'display:none;' : '' }} background:#e8f1fa; border-bottom:1px solid #90b8e0;">
                        <td class="td-pieza sol-font-size-15px sol-background-color-055a9e sol-color-fff sol-font-weight-700">
                            {{ $nPiezaA }}<br><span class="sol-font-size-10px">2da P.</span>
                        </td>

                        <td>
                            <select name="p2_resultado[{{ $keyP2 }}]" class="pta-select input-pieceUsed">
                                <option value="">— Seleccionar —</option>
                                <option value="En correcto estado" {{ $estadoP2A === 'En correcto estado' ? 'selected' : '' }}>En correcto estado</option>
                                <option value="En mal estado" {{ $estadoP2A === 'En mal estado' ? 'selected' : '' }}>En mal estado</option>
                            </select>
                        </td>

                        <td>
                            <input type="hidden" name="p2_programa_pta[{{ $keyP2 }}]" class="pta-programa-input pta-programa-p2-input input-pieceUsed" value="{{ $progP2A }}">
                            <button type="button"
                                class="pta-prog-btn {{ $progP2A !== '' ? 'has-program' : '' }}"
                                data-is-p2="1"
                                onclick="window.openPtaProgramModal(this)"
                                title="{{ $progP2A !== '' ? $progP2A : 'Seleccionar programa para 2da pasada' }}">
                                <svg class="pta-prog-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                                    <polyline points="14 2 14 8 20 8"></polyline>
                                    <line x1="16" y1="13" x2="8" y2="13"></line>
                                    <line x1="16" y1="17" x2="8" y2="17"></line>
                                </svg>
                                <span class="pta-prog-btn-label">{{ $progNameP2A !== '' ? $progNameP2A : 'Seleccionar programa' }}</span>
                            </button>
                        </td>

                        <td class="sol-text-align-left sol-padding-6px">
                            <textarea name="p2_observaciones[{{ $keyP2 }}]" class="pta-input input-pieceUsed sol-resize-vertical"
                                rows="2" placeholder="Obs. 2da pasada...">{{ $filaP2Existente?->p2_observaciones ?? '' }}</textarea>
                        </td>
                    </tr>
                @endforeach
            @endisset

        </tbody>
    </table>
</div>
