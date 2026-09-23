<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$ot = 'OT 1134 - JIMA 1000 ML_MOLDE_R1';

// Simular usuario almacén (perfil 5)
$user = App\Models\User::where('perfil', 5)->first() ?? App\Models\User::first();
auth()->login($user);
echo "Usuario perfil: {$user->perfil}\n\n";

// 1. Estado del registro en BD
$history = App\Models\FundicionHistory::where('ot', $ot)->first();
if ($history) {
    echo "=== FundicionHistory ===\n";
    echo "ot: {$history->ot}\n";
    echo "estado_flujo: " . ($history->estado_flujo instanceof BackedEnum ? $history->estado_flujo->value : $history->estado_flujo) . "\n";
    echo "calidad_revision_status: {$history->calidad_revision_status}\n";
    echo "pre_orden_sent: " . ($history->pre_orden_sent ? '1' : '0') . "\n";
    echo "pre_orden_email_sent: " . ($history->pre_orden_email_sent ? '1' : '0') . "\n";
    echo "alert_sent_at: {$history->alert_sent_at}\n";
    echo "rechazos_procesados: " . ($history->rechazos_procesados ? '1' : '0') . "\n\n";
}

// 2. Liberaciones
$libs = App\Models\LiberacionModeloFundicion::where('ot', $ot)->get();
echo "=== Liberaciones ({$libs->count()}) ===\n";
foreach ($libs as $l) {
    echo "  [{$l->decision}] {$l->tipo_modelo} | alerta_enviada: {$l->alerta_enviada}\n";
}
echo "\n";

// 3. ViewModel — FSM state
$vm = new App\ViewModels\AlmacenTableRowViewModel($history, 'activa', 'Almacén');
echo "=== ViewModel ===\n";
echo "fsmState: {$vm->fsmState}\n";
echo "label: {$vm->label}\n";
echo "tieneFabricacion: " . ($vm->tieneFabricacion ? 'true' : 'false') . "\n";
echo "tieneAprobados: " . ($vm->tieneAprobados ? 'true' : 'false') . "\n";
echo "tieneRechazados: " . ($vm->tieneRechazados ? 'true' : 'false') . "\n";
echo "isCalidadAlerted: " . ($vm->isCalidadAlerted ? 'true' : 'false') . "\n";
echo "\nalmacenPreordenesFab count: " . count($vm->almacenPreordenesFab) . "\n";
foreach ($vm->almacenPreordenesFab as $f) { echo "  [FAB] " . $f['nombre'] . "\n"; }
echo "\ndibujosModelo count: " . count($vm->dibujosModelo) . "\n";
foreach ($vm->dibujosModelo as $f) { echo "  [DWG] " . $f['nombre'] . "\n"; }
echo "\nayudasModelo count: " . count($vm->ayudasModelo ?? []) . "\n";
echo "\ncalidadAprobadosLdm count: " . count($vm->calidadAprobadosLdm ?? []) . "\n";
foreach (($vm->calidadAprobadosLdm ?? []) as $f) { echo "  [LDM] " . $f['nombre'] . "\n"; }
echo "\naprobados: " . implode(', ', $vm->aprobados ?? []) . "\n";
echo "\ndibujosCasting count: " . count($vm->dibujosCasting ?? []) . "\n";
foreach (($vm->dibujosCasting ?? []) as $f) { echo "  [CAST_DWG] " . $f['nombre'] . "\n"; }
echo "\nayudasCasting count: " . count($vm->ayudasCasting ?? []) . "\n";
foreach (($vm->ayudasCasting ?? []) as $f) { echo "  [CAST_AY] " . $f['nombre'] . "\n"; }
echo "\nalmacenPreordenesCasting count: " . count($vm->almacenPreordenesCasting ?? []) . "\n";
echo "\ncountVisibleAprobados: " . ($vm->countVisibleAprobados ?? 'N/A') . "\n";
echo "countVisibleFabricacion: " . ($vm->countVisibleFabricacion ?? 'N/A') . "\n";
echo "count (total): " . ($vm->count ?? 'N/A') . "\n";

