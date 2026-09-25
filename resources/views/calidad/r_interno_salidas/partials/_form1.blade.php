@php
    $numCols = min(4, max(1, (int) $totalPiezas));
    $colWidthN = round(14 / $numCols, 2);
    $colWidthCheck = round(11 / $numCols, 2);
    $colWidthDesc = round((100 / $numCols) - $colWidthN - ($colWidthCheck * 2), 2);
    $filas = (int) ceil($totalPiezas / $numCols);
@endphp

<div class="ri-table-wrapper-unique">
    <table class="ri-table-modern" style="min-width: {{ min(1200, $numCols * 300) }}px;">
        <thead>
            <tr>
                @for($i = 0; $i < $numCols; $i++)
                    <th class="ri-th-num" style="width: {{ $colWidthN }}%; text-align: center;">N°</th>
                    <th class="ri-th-check" style="width: {{ $colWidthCheck }}%; text-align: center; font-size: 0.85rem;">✔</th>
                    <th class="ri-th-check" style="width: {{ $colWidthCheck }}%; text-align: center; font-size: 0.85rem;">✘</th>
                    <th class="ri-th-desc {{ $i < ($numCols - 1) ? 'ri-group-divider-head' : '' }}" style="width: {{ $colWidthDesc }}%; text-align: left;">Descripción</th>
                @endfor
            </tr>
        </thead>
        <tbody>
        @for($fila = 0; $fila < $filas; $fila++)
            <tr>
                @for($col = 0; $col < $numCols; $col++)
                    @php
                        $numPieza = ($fila * $numCols) + $col + 1;
                        $dividerClass = ($col < ($numCols - 1)) ? 'ri-group-divider' : '';

                        if($numPieza > $totalPiezas) {
                            echo "<td class='ri-td-blank'></td><td class='ri-td-blank'></td><td class='ri-td-blank'></td><td class='ri-td-blank {$dividerClass}'></td>";
                            continue;
                        }
                        
                        $celda = $celdas[$numPieza] ?? null;
                        $isAprobado = ($celda?->valor_simple === '1');
                        $isRechazado = ($celda?->valor_simple === '0');
                        $isNinguno = ($celda?->valor_simple === null);
                        $desc = $celda?->descripcion ?? '';

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
                    <td class="ri-td-input {{ $dividerClass }}">
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
