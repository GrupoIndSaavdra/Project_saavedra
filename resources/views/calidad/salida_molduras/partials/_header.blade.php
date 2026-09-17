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
<div class="sm-reporte-header glass-panel">

    {{-- Franja superior: Logo + Título + Código --}}
    <div class="sm-header-top">
        <div class="sm-header-logo">
            <img src="{{ asset('images/lg_saavedra.png') }}" alt="Grupo Industrial Saavedra" class="sm-logo-img">
        </div>

        <div class="sm-header-title">
            <h2>Registro de Números de Trazabilidad por Moldura</h2>
        </div>

        <div class="sm-header-codigo">
            <table class="sm-codigo-table">
                <tr>
                    <td class="sm-codigo-label">Código:</td>
                    <td class="sm-codigo-val">F PRO CPT</td>
                </tr>
                <tr>
                    <td class="sm-codigo-label">Versión:</td>
                    <td class="sm-codigo-val">6</td>
                </tr>
                <tr>
                    <td class="sm-codigo-label">Fecha Rev:</td>
                    <td class="sm-codigo-val">22/04/2026</td>
                </tr>
            </table>
        </div>
    </div>

    {{-- Fila 1: Fecha | Moldura | OT | Clase --}}
    <div class="sm-header-row">
        <div class="sm-header-cell sm-header-cell--md">
            <span class="sm-header-label">Fecha Inicio:</span>
            <span class="sm-header-value">
                {{ $reporte->fecha_inicio ? \Carbon\Carbon::parse($reporte->fecha_inicio)->format('d/m/Y') : now()->format('d/m/Y') }}
            </span>
        </div>
        <div class="sm-header-cell sm-header-cell--lg">
            <span class="sm-header-label">Nombre de la moldura:</span>
            <span class="sm-header-value">{{ $reporte->nombre_moldura }}</span>
        </div>
        <div class="sm-header-cell sm-header-cell--md">
            <span class="sm-header-label">OT:</span>
            <span class="sm-header-value">{{ $reporte->ot_id }}</span>
        </div>
        <div class="sm-header-cell sm-header-cell--sm">
            <span class="sm-header-label">Clase:</span>
            <span class="sm-header-value">{{ $reporte->clase }}</span>
        </div>
    </div>

    {{-- Fila 2: Cantidad Pedido | Consignación | Cliente | Inspector --}}
    <div class="sm-header-row">
        <div class="sm-header-cell sm-header-cell--sm">
            <span class="sm-header-label">Cantidad de Pedido:</span>
            <span class="sm-header-value">{{ $reporte->cantidad_pedido }}</span>
        </div>
        <div class="sm-header-cell sm-header-cell--sm">
            <span class="sm-header-label">Consignación:</span>
            <span class="sm-header-value">{{ $reporte->cantidad_consignacion }}</span>
        </div>
        <div class="sm-header-cell sm-header-cell--lg">
            <span class="sm-header-label">Cliente:</span>
            <span class="sm-header-value">{{ $reporte->cliente ?? '—' }}</span>
        </div>
        <div class="sm-header-cell sm-header-cell--lg">
            <span class="sm-header-label">Nombre de Inspector:</span>
            <span class="sm-header-value">{{ $inspector->nombre ?? 'N/A' }}</span>
        </div>
    </div>

    {{-- Info total de piezas calculado --}}
    <div class="sm-header-total">
        <span>Total de piezas en reporte:&nbsp;</span>
        <strong id="display-total-piezas">{{ $reporte->total_piezas }}</strong>
        <span>&nbsp;pzas.</span>
        @if($reporte->cantidad_consignacion > 0)
            <span class="sm-header-total__formula">
                (Consignación: <span id="display-consignacion">{{ $reporte->cantidad_consignacion }}</span> | Pedido original: {{ $reporte->cantidad_pedido }})
            </span>
        @else
            <span class="sm-header-total__formula">
                (Pedido original: {{ $reporte->cantidad_pedido }})
            </span>
        @endif
    </div>
</div>
