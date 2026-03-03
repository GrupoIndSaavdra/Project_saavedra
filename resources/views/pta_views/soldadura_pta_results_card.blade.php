{{--
Partial: soldadura_pta_results_card.blade.php
Variables esperadas:
- $ot_id (string) – ID de la OT
- $totalPTA (int) – Piezas terminadas correctamente en PTA
- $liberadas (int) – Piezas liberadas por administrador
--}}

@php
    $pct_terminadas = $totalPTA > 0 ? 100 : 0;          // Ya terminadas sobre el total deseado
    $pct_liberadas = $totalPTA > 0 ? round(($liberadas / $totalPTA) * 100) : 0;
@endphp

<a href="{{ route('pta.results', ['ot_id' => $ot_id]) }}" class="pta-card-link text-decoration-none"
    title="Ver / registrar resultados de Soldadura PTA">
    <div class="card pta-results-card border-0 shadow-sm h-100">
        <div class="card-header pta-card-header d-flex align-items-center gap-2">
            <span class="pta-header-icon">🔬</span>
            <h6 class="mb-0 fw-semibold text-white">Resultados Sold. PTA</h6>
        </div>

        <div class="card-body pta-card-body">
            {{-- OT badge --}}
            <p class="pta-ot-badge mb-3">OT: <strong>{{ $ot_id }}</strong></p>

            {{-- Barra 1: Piezas terminadas --}}
            <div class="mb-3">
                <div class="d-flex justify-content-between mb-1">
                    <small class="text-muted fw-medium">Piezas Terminadas</small>
                    <small class="fw-bold text-primary">{{ $pct_terminadas }}% — {{ $totalPTA }} pza(s)</small>
                </div>
                <div class="progress pta-progress">
                    <div class="progress-bar bg-primary" role="progressbar" style="width: {{ $pct_terminadas }}%"
                        aria-valuenow="{{ $pct_terminadas }}" aria-valuemin="0" aria-valuemax="100">
                    </div>
                </div>
            </div>

            {{-- Barra 2: Piezas liberadas por admin --}}
            <div class="mb-2">
                <div class="d-flex justify-content-between mb-1">
                    <small class="text-muted fw-medium">Liberadas por Admin.</small>
                    <small class="fw-bold {{ $liberadas > 0 ? 'text-primary' : 'text-secondary' }}">
                        {{ $pct_liberadas }}% — {{ $liberadas }} pza(s)
                    </small>
                </div>
                <div class="progress pta-progress">
                    <div class="progress-bar {{ $liberadas > 0 ? 'bg-primary' : 'bg-secondary' }}" role="progressbar"
                        style="width: {{ $pct_liberadas }}%" aria-valuenow="{{ $pct_liberadas }}" aria-valuemin="0"
                        aria-valuemax="100">
                    </div>
                </div>
            </div>
        </div>

        <div class="card-footer pta-card-footer text-end">
            <small class="text-primary fw-semibold">Ver resultados →</small>
        </div>
    </div>
</a>
