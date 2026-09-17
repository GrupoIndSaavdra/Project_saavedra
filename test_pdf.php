<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    $reporte = App\Models\SalidaMoldura::first();
    if (!$reporte) {
        echo "No hay reportes\n";
        exit;
    }
    
    $ordenTrabajo = App\Models\Orden_trabajo::find($reporte->ot_id);
    $inspector = App\Models\User::find($reporte->inspector_id);
    $totalPiezas = $reporte->total_piezas;
    $celdas = $reporte->piezas()->get()->keyBy('numero_pieza');
    
    $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('calidad.salida_molduras.pdf', compact('reporte', 'ordenTrabajo', 'inspector', 'totalPiezas', 'celdas'));
    $pdf->setOption('isRemoteEnabled', true);
    $pdf->setOption('isPhpEnabled', true);
    $pdf->setPaper('letter', 'landscape');
    
    $content = $pdf->output();
    echo "PDF generacion exitosa: " . strlen($content) . " bytes\n";
} catch (\Throwable $e) {
    echo "Error: " . $e->getMessage() . "\n" . $e->getTraceAsString();
}
