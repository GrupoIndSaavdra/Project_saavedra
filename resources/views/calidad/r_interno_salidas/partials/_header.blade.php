{{--
    Partial: _header.blade.php
    Encabezado del reporte físico "Registro de Números de Trazabilidad por Moldura".
    Replica el encabezado de las fotos con:
    - Logo + Título + Código/Versión
    - Fila: Fecha Inicio | Nombre de la moldura | OT | Clase
    - Fila: Cantidad de Pedido | Consignación | Cliente | Inspector

    Variables requeridas:
        $reporte      → SalidaMoldura
        $ordenTrabajo → Orden_trabajo
        $inspector    → User
--}}
<div class="ri-reporte-header glass-panel">

    {{-- Franja superior: Logo + Título + Código --}}
    <div class="ri-header-top">
        <div class="ri-header-logo">
            <img src="{{ asset('images/lg_saavedra.png') }}" alt="Grupo Industrial Saavedra" class="ri-logo-img">
        </div>

        <div class="ri-header-title" style="text-align: center;">
            <h2>Registro de Números de Trazabilidad por Moldura</h2>
            <h3 style="margin-top: 3px; font-weight: bold; letter-spacing: 1px; font-size: 1.05rem;">
                <span style="color: #6c757d;">Folio:</span> 
                <span style="color: #d32f2f;">F_CCL_RIS_{{ str_pad($reporte->id, 4, '0', STR_PAD_LEFT) }}-V{{ $siguienteVersion }}</span>
            </h3>
        </div>

        <div class="ri-header-codigo">
            <table class="ri-codigo-table">
                <tr>
                    <td class="ri-codigo-label">Código:</td>
                    <td class="ri-codigo-val">F PRO CPT</td>
                </tr>
                <tr>
                    <td class="ri-codigo-label">Versión:</td>
                    <td class="ri-codigo-val">6</td>
                </tr>
                <tr>
                    <td class="ri-codigo-label">Fecha Rev:</td>
                    <td class="ri-codigo-val">22/04/2026</td>
                </tr>
            </table>
        </div>
    </div>

    {{-- Fila 1: Fecha | Moldura | OT | Clase --}}
    <div class="ri-header-row">
        <div class="ri-header-cell ri-header-cell--md">
            <span class="ri-header-label">Fecha Inicio:</span>
            <span class="ri-header-value">
                {{ $reporte->fecha_inicio ? \Carbon\Carbon::parse($reporte->fecha_inicio)->format('d/m/Y') : now()->format('d/m/Y') }}
            </span>
        </div>
        <div class="ri-header-cell ri-header-cell--lg">
            <span class="ri-header-label">Nombre de la moldura:</span>
            <span class="ri-header-value">{{ $reporte->nombre_moldura }}</span>
        </div>
        <div class="ri-header-cell ri-header-cell--md">
            <span class="ri-header-label">OT:</span>
            <span class="ri-header-value">{{ $reporte->ot_id }}</span>
        </div>
        <div class="ri-header-cell ri-header-cell--sm">
            <span class="ri-header-label">Clase:</span>
            <span class="ri-header-value">{{ $reporte->clase }}</span>
        </div>
    </div>

    {{-- Fila 2: Cantidad Pedido | Consignación | Cliente | Inspector --}}
    <div class="ri-header-row">
        <div class="ri-header-cell ri-header-cell--sm">
            <span class="ri-header-label">Cantidad de Pedido:</span>
            <span class="ri-header-value">{{ $reporte->cantidad_pedido }}</span>
        </div>
        <div class="ri-header-cell ri-header-cell--sm">
            <span class="ri-header-label">Consignación:</span>
            <span class="ri-header-value">{{ $reporte->cantidad_consignacion }}</span>
        </div>
        <div class="ri-header-cell ri-header-cell--lg">
            <span class="ri-header-label">Cliente:</span>
            <span class="ri-header-value">{{ $reporte->cliente ?? '—' }}</span>
        </div>
        <div class="ri-header-cell ri-header-cell--lg">
            <span class="ri-header-label">Nombre de Inspector:</span>
            <span class="ri-header-value">{{ $inspector->nombre ?? 'N/A' }}</span>
        </div>
    </div>

    {{-- Info total de piezas calculado --}}
    <div class="ri-header-total">
        <span>Total de piezas en reporte:&nbsp;</span>
        <strong id="display-total-piezas">{{ $reporte->total_piezas }}</strong>
        <span>&nbsp;pzas.</span>
        @if($reporte->cantidad_consignacion > 0)
            <span class="ri-header-total__formula">
                (Consignación: <span id="display-consignacion">{{ $reporte->cantidad_consignacion }}</span> | Pedido original: {{ $reporte->cantidad_pedido }})
            </span>
        @else
            <span class="ri-header-total__formula">
                (Pedido original: {{ $reporte->cantidad_pedido }})
            </span>
        @endif
    </div>
</div>
