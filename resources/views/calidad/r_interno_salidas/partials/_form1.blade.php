<div class="ri-table-wrapper-unique">
    <table class="ri-table-modern">
        <thead>
            <tr>
                @for($i = 0; $i < 4; $i++)
                    <th class="ri-th-num" style="width: 5%; text-align: center;">N°</th>
                    <th class="ri-th-check" style="width: 3.5%; text-align: center; font-size: 0.85rem;">✔</th>
                    <th class="ri-th-check" style="width: 3.5%; text-align: center; font-size: 0.85rem;">✘</th>
                    <th class="ri-th-desc {{ $i < 3 ? 'ri-group-divider-head' : '' }}" style="width: 15%; text-align: left;">Descripción</th>
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
