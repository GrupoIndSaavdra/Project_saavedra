{{--
    Partial: _header.blade.php
    Encabezado del reporte físico "Registro de Números de Trazabilidad por Moldura".
    Estilo institucional unificado con Reporte Dimensional y Volumétrico (GIS).
--}}

{{-- ── Encabezado Oficial Institucional ── --}}
<div class="rd-doc-header">
    <div class="rd-doc-title-row">
        <div class="rd-doc-main-title">
            REGISTRO DE NÚMEROS DE TRAZABILIDAD POR MOLDURA
        </div>
        <div class="rd-doc-logo">
            <img src="{{ asset('images/lg_saavedra.png') }}" alt="Grupo Industrial Saavedra" class="rd-logo-img">
        </div>
    </div>

    <div class="rd-doc-meta-grid">
        <div class="rd-meta-col">
            <span class="rd-meta-label">Folio</span>
            <span class="rd-meta-text-val font-bold text-red" style="color: #d32f2f;">F_CCL_RIS_{{ str_pad($reporte->id, 4, '0', STR_PAD_LEFT) }}-V{{ $siguienteVersion }}</span>
        </div>
        <div class="rd-meta-col">
            <span class="rd-meta-label">Fecha de Revisión</span>
            <span class="rd-meta-text-val">22/04/2026</span>
        </div>
        <div class="rd-meta-col rd-meta-col-sm">
            <span class="rd-meta-label">Versión</span>
            <span class="rd-meta-text-val">6</span>
        </div>
        <div class="rd-meta-col rd-meta-col-code">
            <span class="rd-meta-label">Código</span>
            <span class="rd-meta-code-val">F PRO CPT</span>
        </div>
    </div>
</div>

{{-- ── Información General y de Pedido ── --}}
<div class="rd-info-section">
    <div class="rd-info-block">
        <div class="rd-block-heading">INFORMACIÓN GENERAL DE LA MOLDURA</div>
        <div class="rd-info-grid">
            <div class="rd-info-cell">
                <span class="rd-lbl">FECHA:</span>
                <span class="rd-val">{{ $reporte->fecha_inicio ? \Carbon\Carbon::parse($reporte->fecha_inicio)->format('d/m/Y') : now()->format('d/m/Y') }}</span>
            </div>
            <div class="rd-info-cell">
                <span class="rd-lbl">MOLDURA:</span>
                <span class="rd-val">{{ $reporte->nombre_moldura }}</span>
            </div>
            <div class="rd-info-cell">
                <span class="rd-lbl">OT:</span>
                <span class="rd-val font-bold text-blue">{{ $reporte->ot_id }}</span>
            </div>
            <div class="rd-info-cell">
                <span class="rd-lbl">CLASE:</span>
                <span class="rd-val font-bold">{{ $reporte->clase }}</span>
            </div>
        </div>
    </div>

    <div class="rd-info-block">
        <div class="rd-block-heading">INFORMACIÓN DE PEDIDO Y TRAZABILIDAD</div>
        <div class="rd-info-grid">
            <div class="rd-info-cell">
                <span class="rd-lbl">PEDIDO:</span>
                <span class="rd-val font-bold text-blue">{{ $reporte->cantidad_pedido }} pzas</span>
            </div>
            <div class="rd-info-cell">
                <span class="rd-lbl">CONSIGNACIÓN:</span>
                <span class="rd-val font-bold text-amber">
                    @if($reporte->cantidad_consignacion > 0)
                        +{{ $reporte->cantidad_consignacion }} pzas
                    @else
                        0 pzas
                    @endif
                </span>
            </div>
            <div class="rd-info-cell">
                <span class="rd-lbl">CLIENTE:</span>
                <span class="rd-val">{{ $reporte->cliente ?? '—' }}</span>
            </div>
            <div class="rd-info-cell">
                <span class="rd-lbl">INSPECTOR:</span>
                <span class="rd-val font-bold">{{ $inspector->nombre ?? 'N/A' }}</span>
            </div>
        </div>
    </div>
</div>

{{-- Barra de Total Calculado --}}
<div class="ri-total-bar">
    <div class="ri-total-left">
        <span class="ri-total-lbl">TOTAL DE PIEZAS EN REPORTE:</span>
        <span class="ri-total-num" id="display-total-piezas">{{ $reporte->total_piezas }}</span>
        <span class="ri-total-unit">piezas</span>
    </div>
    <div class="ri-total-right">
        @if($reporte->cantidad_consignacion > 0)
            <span class="ri-header-total__formula">
                (Consignación: <strong id="display-consignacion">{{ $reporte->cantidad_consignacion }}</strong> | Pedido original: <strong>{{ $reporte->cantidad_pedido }}</strong>)
            </span>
        @else
            <span class="ri-header-total__formula">
                (Pedido original: <strong>{{ $reporte->cantidad_pedido }}</strong>)
            </span>
        @endif
    </div>
</div>
