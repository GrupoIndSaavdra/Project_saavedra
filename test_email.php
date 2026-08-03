<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\DibujosFundicionPdfController;
use App\Http\Controllers\AlmacenFundicionController;
use App\Http\Controllers\CalidadFundicionController;

Auth::loginUsingId(94); // NATALI JOSELIN ALEMAN PEREZ

echo "\n--- 1. Administracion a Almacen (Dibujos) ---\n";
$req1 = new Request(['ot' => 'OT 1099 - PRUEBADDF_R1']);
$ctrl1 = app(DibujosFundicionPdfController::class);
echo $ctrl1->sendEmailAlert($req1)->getContent() . "\n";

echo "\n--- 2. Almacen a Calidad y Proveedores (Pre-orden Modelo) ---\n";
$req2 = new Request([
    'ot' => 'OT 1099 - PRUEBADDF_R1',
    'fecha_entrega' => '2026-06-18',
    'tipo' => 'modelo'
]);
$ctrl2 = app(AlmacenFundicionController::class);
echo $ctrl2->sendEmailPreOrden($req2)->getContent() . "\n";

echo "\n--- 3. Calidad a Almacen (Liberacion Modelo) ---\n";
// For this we must use the exact data from the DB to not fail the controller checks
$req3 = new Request([
    'ot' => 'OT 1099 - PRUEBADDF_R1',
    'decision' => 'rechazar',
    'tipo_modelo' => 'Fondo',
    'fecha' => '2026-06-17'
]);
$ctrl3 = app(CalidadFundicionController::class);
echo $ctrl3->enviarAlertaLiberacion($req3)->getContent() . "\n";

echo "\n--- 4. Almacen a Proveedores (Pre-orden Casting) ---\n";
// The Casting Pre-order is for R2
$req4 = new Request([
    'ot' => 'OT 1099 - PRUEBADDF_R2',
    'fecha_entrega' => '2026-06-19',
    'tipo' => 'casting'
]);
$ctrl4 = app(AlmacenFundicionController::class);
echo $ctrl4->sendEmailPreOrden($req4)->getContent() . "\n";
