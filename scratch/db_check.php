<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$ot = 'OT 2103 - HACIENDA TEPA 1 LTO COMPLEMENTO';

echo "=== FundicionHistory ===\n";
$history = \App\Models\FundicionHistory::where('ot', $ot)->first();
if ($history) {
    echo "OT: " . $history->ot . "\n";
    echo "calidad_revision_status: " . $history->calidad_revision_status . "\n";
    echo "ayudas_config: " . json_encode($history->ayudas_config) . "\n";
    echo "tiene_modelo: " . $history->tiene_modelo . "\n";
} else {
    echo "No history found\n";
}

echo "\n=== LiberacionModeloFundicion ===\n";
$liberaciones = \App\Models\LiberacionModeloFundicion::where('ot', $ot)->get();
foreach ($liberaciones as $lib) {
    echo "ID: " . $lib->id . ", tipo: " . $lib->tipo_modelo . ", decision: " . $lib->decision . ", estado: " . $lib->estado . "\n";
}
