{{--
    Partial: _observaciones.blade.php
    Cuadro de observaciones generales del reporte.
    - Siempre visible (independiente del formato)
    - Máximo 250 caracteres
    - Autoguardado con debounce

    Variables requeridas:
        $reporte → SalidaMoldura
--}}
<div class="ri-obs-container">
    <div class="ri-obs-header">
        <h3 class="ri-obs-title">Observaciones:</h3>
        <span class="ri-obs-counter">
            <span id="obs-chars-used">{{ strlen($reporte->observaciones ?? '') }}</span>/250
        </span>
    </div>
    <textarea
        id="campo-observaciones"
        class="ri-obs-textarea"
        maxlength="250"
        rows="4"
        placeholder="Escribe aquí las observaciones generales del reporte..."
        data-reporte-id="{{ $reporte->id }}"

    >{{ $reporte->observaciones ?? '' }}</textarea>
    <div class="ri-obs-footer">
        <span class="ri-obs-status" id="obs-status"></span>
    </div>
</div>
