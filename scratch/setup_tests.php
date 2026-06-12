<?php
use App\Models\FundicionHistory;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

$ots = [
    'OT TEST 1 - RECHAZA TODAS',
    'OT TEST 2 - APRUEBA TODAS',
    'OT TEST 3 - MITAD Y MITAD',
    'OT TEST 4 - 1 APROBADO 3 RECHAZADOS',
    'OT TEST 5 - 3 APROBADOS 1 RECHAZADO'
];

foreach ($ots as $ot) {
    // 1. Create directory structure and dummy files
    $basePath = public_path("DOCUMENTACION_GIS/ALMACEN_FUNDICION/" . $ot);
    $clases = ['Fondo', 'Bombillo', 'Molde', 'Obturador'];
    
    foreach ($clases as $clase) {
        $dibujosPath = $basePath . '/' . $clase . '/Dibujos';
        $ayudasPath = $basePath . '/' . $clase . '/Ayudas_Visuales';
        
        if (!file_exists($dibujosPath)) mkdir($dibujosPath, 0777, true);
        if (!file_exists($ayudasPath)) mkdir($ayudasPath, 0777, true);
        
        file_put_contents($dibujosPath . "/{$clase} - Dibujo.pdf", "Dummy content for $clase dibujo");
        file_put_contents($ayudasPath . "/{$clase} - Ayuda.pdf", "Dummy content for $clase ayuda");
    }
    
    // Create preorden
    $preordenPath = $basePath . '/Preordenes';
    if (!file_exists($preordenPath)) mkdir($preordenPath, 0777, true);
    file_put_contents($preordenPath . "/Pre-Orden_Modelo_Test.pdf", "Dummy preorden");
    
    // 2. Create database record
    FundicionHistory::updateOrCreate(
        ['ot' => $ot],
        [
            'status' => 'activa',
            'tiene_modelo' => true,
            'pre_orden_sent' => true,
            'pre_orden_email_sent' => true,
            'calidad_revision_status' => 'pendiente',
            'alert_sent_at' => now(),
            'ayudas_config' => ['Fondo', 'Bombillo', 'Molde', 'Obturador']
        ]
    );
    echo "Setup completado para $ot\n";
}
