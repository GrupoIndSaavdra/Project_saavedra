<div class="sm-table-wrapper-unique">
    <table class="sm-table-modern">
        <thead>
            <tr>
                @for($i = 0; $i < 4; $i++)
                    <th style="width: 4%; text-align: center;" class="sm-th-num">N°</th>
                    <th style="width: 3.5%; text-align: center;" class="sm-th-check">✔</th>
                    <th style="width: 4%; text-align: center;" class="sm-th-sub">90°</th>
                    <th style="width: 4%; text-align: center;" class="sm-th-sub">LP</th>
                    <th style="width: 9.5%; text-align: left;" class="sm-th-desc {{ $i < 3 ? 'sm-group-divider-head' : '' }}">Descripción</th>
                @endfor
            </tr>
        </thead>
        <tbody>
        @php
            $filas = (int) ceil($totalPiezas / 4);
        @endphp
        @for($fila = 0; $fila < $filas; $fila++)
            <tr>
                @for($col = 0; $col < 4; $col++)
                    @php
                        $numPieza = ($fila * 4) + $col + 1;
                        $dividerClass = ($col < 3) ? 'sm-group-divider' : '';

                        if($numPieza > $totalPiezas) {
                            echo "<td class='sm-td-blank'></td><td class='sm-td-blank'></td><td class='sm-td-blank'></td><td class='sm-td-blank'></td><td class='sm-td-blank {$dividerClass}'></td>";
                            continue;
                        }
                        $celda = $celdas[$numPieza] ?? null;
                        $isChecked = ($celda?->valor_simple === '1');
                        $badgeClass = $isChecked ? 'sm-badge-num--green' : 'sm-badge-num--red';
                        $val90 = $celda?->valor_90 ?? '';
                        $valLp = $celda?->valor_lp ?? '';
                        $desc  = $celda?->descripcion ?? '';
                    @endphp
                    <td class="sm-td-center">
                        <span class="sm-badge-num {{ $badgeClass }}" id="molde-num-{{ $numPieza }}">
                            {{ $numPieza }}
                        </span>
                    </td>
                    <td class="sm-td-center">
                        <input type="checkbox"
                               class="sm-checkbox-molde sm-input-autosave custom-checkbox-styled"
                               data-reporte-id="{{ $reporte->id }}"
                               data-num="{{ $numPieza }}"
                               data-campo="valor_simple"
                               value="1"
                               {{ $isChecked ? 'checked' : '' }}
                               {{ $reporte->enviado ? 'disabled' : '' }}
                               title="Marcar pieza {{ $numPieza }}">
                    </td>
                    <td class="sm-td-input" style="padding: 4px;">
                        <input type="number" step="any"
                               class="sm-b-input sm-input-autosave sm-styled-input sm-styled-input--num"
                               id="pieza-{{ $numPieza }}-90"
                               data-reporte-id="{{ $reporte->id }}"
                               data-num="{{ $numPieza }}"
                               data-campo="valor_90"
                               value="{{ $val90 }}"
                               placeholder="90°"
                               autocomplete="off"
                               {{ (!$isChecked || $reporte->enviado) ? 'disabled' : '' }}>
                    </td>
                    <td class="sm-td-input" style="padding: 4px;">
                        <input type="number" step="any"
                               class="sm-b-input sm-input-autosave sm-styled-input sm-styled-input--num"
                               id="pieza-{{ $numPieza }}-lp"
                               data-reporte-id="{{ $reporte->id }}"
                               data-num="{{ $numPieza }}"
                               data-campo="valor_lp"
                               value="{{ $valLp }}"
                               placeholder="LP"
                               autocomplete="off"
                               {{ (!$isChecked || $reporte->enviado) ? 'disabled' : '' }}>
                    </td>
                    <td class="sm-td-input {{ $dividerClass }}" style="padding: 4px;">
                        <input type="text"
                               class="sm-desc-molde sm-input-autosave sm-styled-input"
                               id="pieza-{{ $numPieza }}-desc"
                               data-reporte-id="{{ $reporte->id }}"
                               data-num="{{ $numPieza }}"
                               data-campo="descripcion"
                               value="{{ $desc }}"
                               placeholder="Motivo / Descripción..."
                               autocomplete="off"
                               maxlength="250"
                               {{ $reporte->enviado ? 'disabled' : '' }}>
                    </td>
                @endfor
            </tr>
        @endfor
        </tbody>
    </table>
</div>

@if($totalPiezas === 0)
    <div class="sm-grid-empty">
        <p>La cantidad de pedido + consignación es <strong>0</strong>. Verifica los datos de la O.T.</p>
    </div>
@endif
