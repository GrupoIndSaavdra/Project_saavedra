<?php
require_once __DIR__ . '/../vendor/autoload.php';
use App\Models\FundicionHistory;

$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$histories = FundicionHistory::where('ot', 'LIKE', '%9999%')->get();
foreach ($histories as $h) {
    echo "OT: " . $h->ot . "\n";
    echo "  alert_sent_at: " . var_export($h->alert_sent_at?->toDateTimeString(), true) . "\n";
    echo "-------------------------\n";
}
