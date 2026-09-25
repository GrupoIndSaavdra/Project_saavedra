{{--
    Partial: _observaciones.blade.php
    Cuadro de observaciones generales del reporte.
    - Máximo 250 caracteres
    - Autoguardado con debounce
--}}
<div class="rd-info-block" style="margin-top: 1.2rem; margin-bottom: 1.2rem;">
    <div class="rd-block-heading" style="display: flex; justify-content: space-between; align-items: center;">
        <span>OBSERVACIONES GENERALES</span>
        <span class="ri-obs-counter">
            <span id="obs-chars-used">{{ strlen($reporte->observaciones ?? '') }}</span> / 250
        </span>
    </div>
    <div class="ri-obs-body">
        <textarea
            id="campo-observaciones"
            class="ri-obs-textarea"
            maxlength="250"
            rows="3"
            placeholder="Escribe aquí las observaciones generales del reporte de salida..."
            data-reporte-id="{{ $reporte->id }}"
        >{{ $reporte->observaciones ?? '' }}</textarea>
        <div class="ri-obs-footer">
            <span class="ri-obs-status" id="obs-status"></span>
        </div>
    </div>
</div>
