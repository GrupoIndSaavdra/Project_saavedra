<div class="sm-table-wrapper-unique">
    <table class="sm-table-modern">
        <thead>
            <tr>
                @for($i = 0; $i < 4; $i++)
                    <th class="sm-th-num" style="width: 5%; text-align: center;">N°</th>
                    <th class="sm-th-check" style="width: 5%; text-align: center;">✔</th>
                    <th class="sm-th-desc {{ $i < 3 ? 'sm-group-divider-head' : '' }}" style="width: 15%; text-align: left;">Descripción</th>
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
                            echo "<td class='sm-td-blank'></td><td class='sm-td-blank'></td><td class='sm-td-blank {$dividerClass}'></td>";
                            continue;
                        }
                        
                        $celda = $celdas[$numPieza] ?? null;
                        $isChecked = ($celda?->valor_simple === '1');
                        $desc = $celda?->descripcion ?? '';
                        $badgeClass = $isChecked ? 'sm-badge-num--green' : 'sm-badge-num--red';
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
                    <td class="sm-td-input {{ $dividerClass }}">
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
