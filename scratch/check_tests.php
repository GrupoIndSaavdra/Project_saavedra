<?php
use App\Models\FundicionHistory;
$ots = FundicionHistory::where('ot', 'LIKE', 'OT TEST%')->orderBy('ot')->get();
foreach ($ots as $ot) {
    echo "[" . $ot->ot . "] Status: " . $ot->status . " | Calidad: " . $ot->calidad_revision_status . " | Rechazos Procesados: " . ($ot->rechazos_procesados ? 'Si' : 'No') . "\n";
}
