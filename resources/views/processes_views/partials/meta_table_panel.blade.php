{{-- Tabla de Metas y Parámetros del Reporte --}}
<form action="{{ route('verifiedPassword') }}" method="post" class="form-verified-password">
    @csrf
    <table class="table-meta">
        <thead>
            <tr>
                <th>
                    <div class="table-meta-th-content">
                        <img src="{{ asset('images/iconos/Turno-Icon.png') }}" class="table-meta-icon" alt="Tiempo">
                        <span>Tiempo estándar</span>
                    </div>
                </th>
                <th>
                    <div class="table-meta-th-content">
                        <img src="{{ asset('images/iconos/Meta-Icon.png') }}" class="table-meta-icon" alt="Meta">
                        <span>Meta piezas/juegos</span>
                    </div>
                </th>
                <th>
                    <div class="table-meta-th-content">
                        <img src="{{ asset('images/iconos/Resultado-Icon.png') }}" class="table-meta-icon" alt="Resultado">
                        <span>Resultado</span>
                    </div>
                </th>
                <th>
                    <div class="table-meta-th-content">
                        <img src="{{ asset('images/iconos/OT-Icon.png') }}" class="table-meta-icon" alt="Pedido">
                        <span>Pedido Consignación</span>
                    </div>
                </th>
                <th>
                    <div class="table-meta-th-content">
                        <img src="{{ asset('images/iconos/Tornilleria-Icon.png') }}" class="table-meta-icon" alt="Restantes">
                        <span>Piezas Restantes</span>
                    </div>
                </th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>
                    <input type="text" class="meta-readonly-input" value="{{ $arrayData['meta']->t_estandar ?? 0 }} minutos" readonly tabindex="-1" title="Tiempo estándar calculado (No editable)">
                    {{-- Campo oculto para el monitoreo de productividad --}}
                    <input type="hidden" id="standard_time_hidden" value="{{ $arrayData['meta']->t_estandar ?? 0 }}">
                </td>
                <td>
                    <input type="text" class="meta-readonly-input" value="{{ $arrayData['meta']->meta ?? 0 }} piezas" readonly tabindex="-1" title="Meta calculada (No editable)">
                </td>
                <td>
                    <input type="text" class="meta-readonly-input" value="{{ $arrayData['meta']->resultado ?? 0 }} piezas" readonly tabindex="-1" title="Resultado de producción (No editable)">
                </td>
                <td>
                    <input type="text" id="meta_consignment_pieces" class="meta-readonly-input" value="{{ $arrayData['consignmentPieces'] ?? 0 }} piezas" readonly tabindex="-1" title="Pedido consignación (No editable)">
                </td>
                <td>
                    <input type="text" id="meta_remaining_pieces" class="meta-readonly-input" value="{{ $arrayData['remainingPieces'] ?? 0 }} piezas" readonly tabindex="-1" title="Piezas restantes (No editable)">
                </td>
            </tr>
        </tbody>
    </table>
</form>
