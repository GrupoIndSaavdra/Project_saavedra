<div class="ri-table-wrapper-unique">
    <table class="ri-table-modern">
        <thead>
            <tr>
                @for($i = 0; $i < 4; $i++)
                    <th style="width: 4%; text-align: center;" class="ri-th-num">N°</th>
                    <th style="width: 3.5%; text-align: center; font-size: 0.85rem;" class="ri-th-check">✔</th>
                    <th style="width: 3.5%; text-align: center; font-size: 0.85rem;" class="ri-th-check">✘</th>
                    <th style="width: 4%; text-align: center;" class="ri-th-sub">90°</th>
                    <th style="width: 4%; text-align: center;" class="ri-th-sub">LP</th>
                    <th style="width: 9.5%; text-align: left;" class="ri-th-desc {{ $i < 3 ? 'ri-group-divider-head' : '' }}">Descripción</th>
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
                        $dividerClass = ($col < 3) ? 'ri-group-divider' : '';

                        if($numPieza > $totalPiezas) {
                            echo "<td class='ri-td-blank'></td><td class='ri-td-blank'></td><td class='ri-td-blank'></td><td class='ri-td-blank'></td><td class='ri-td-blank'></td><td class='ri-td-blank {$dividerClass}'></td>";
                            continue;
                        }
                        $celda = $celdas[$numPieza] ?? null;
                        $isAprobado = ($celda?->valor_simple === '1');
                        $isRechazado = ($celda?->valor_simple === '0');
                        $isNinguno = ($celda?->valor_simple === null);

                        if ($isAprobado) {
                            $badgeClass = 'ri-badge-num--green';
                            $disabledInput = '';
                        } elseif ($isRechazado) {
                            $badgeClass = 'ri-badge-num--red';
                            $disabledInput = '';
                        } else {
                            $badgeClass = 'ri-badge-num--gray';
                            $disabledInput = 'disabled';
                        }
                        $val90 = $celda?->valor_90 ?? '';
                        $valLp = $celda?->valor_lp ?? '';
                        $desc  = $celda?->descripcion ?? '';
                    @endphp
                    <td class="ri-td-center">
                        <span class="ri-badge-num {{ $badgeClass }}" id="molde-num-{{ $numPieza }}">
                            {{ $numPieza }}
                        </span>
                    </td>
                    <td class="ri-td-center" style="text-align: center; padding-top: 10px; padding-bottom: 10px;">
                        <input type="checkbox"
                               class="ri-checkbox-molde ri-checkbox-aprobado ri-input-autosave custom-checkbox-styled"
                               data-reporte-id="{{ $reporte->id }}"
                               data-num="{{ $numPieza }}"
                               data-campo="valor_simple"
                               value="1"
                               {{ $isAprobado ? 'checked' : '' }}
                               title="Aprobar pieza {{ $numPieza }}">
                    </td>
                    <td class="ri-td-center" style="text-align: center; padding-top: 10px; padding-bottom: 10px;">
                        <input type="checkbox"
                               class="ri-checkbox-molde ri-checkbox-rechazado ri-input-autosave custom-checkbox-styled custom-checkbox-red"
                               data-reporte-id="{{ $reporte->id }}"
                               data-num="{{ $numPieza }}"
                               data-campo="valor_simple"
                               value="0"
                               {{ $isRechazado ? 'checked' : '' }}
                               title="Rechazar pieza {{ $numPieza }}">
                    </td>
                    <td class="ri-td-input" style="padding: 4px;">
                        <input type="number" step="any"
                               class="ri-b-input ri-input-autosave ri-styled-input ri-styled-input--num"
                               id="pieza-{{ $numPieza }}-90"
                               data-reporte-id="{{ $reporte->id }}"
                               data-num="{{ $numPieza }}"
                               data-campo="valor_90"
                               value="{{ $val90 }}"
                               placeholder="90°"
                               autocomplete="off"
                               {{ $disabledInput }}>
                    </td>
                    <td class="ri-td-input" style="padding: 4px;">
                        <input type="number" step="any"
                               class="ri-b-input ri-input-autosave ri-styled-input ri-styled-input--num"
                               id="pieza-{{ $numPieza }}-lp"
                               data-reporte-id="{{ $reporte->id }}"
                               data-num="{{ $numPieza }}"
                               data-campo="valor_lp"
                               value="{{ $valLp }}"
                               placeholder="LP"
                               autocomplete="off"
                               {{ $disabledInput }}>
                    </td>
                    <td class="ri-td-input {{ $dividerClass }}" style="padding: 4px;">
                        <input type="text"
                               class="ri-desc-molde ri-input-autosave ri-styled-input"
                               id="pieza-{{ $numPieza }}-desc"
                               data-reporte-id="{{ $reporte->id }}"
                               data-num="{{ $numPieza }}"
                               data-campo="descripcion"
                               value="{{ $desc }}"
                               placeholder="Motivo / Descripción..."
                               autocomplete="off"
                               maxlength="250"
                               {{ $disabledInput }}
                               >
                    </td>
                @endfor
            </tr>
        @endfor
        </tbody>
    </table>
</div>

@if($totalPiezas === 0)
    <div class="ri-grid-empty">
        <p>La cantidad de pedido + consignación es <strong>0</strong>. Verifica los datos de la O.T.</p>
    </div>
@endif
