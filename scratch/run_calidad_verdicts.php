<?php
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

// Login as Calidad
$user = User::where('perfil', 4)->first();
if ($user) Auth::login($user);

$controller = app(\App\Http\Controllers\CalidadFundicionController::class);

$scenarios = [
    'OT TEST 1 - RECHAZA TODAS' => [
        'Fondo' => 'rechazar',
        'Bombillo' => 'rechazar',
        'Molde' => 'rechazar',
        'Obturador' => 'rechazar'
    ],
    'OT TEST 2 - APRUEBA TODAS' => [
        'Fondo' => 'aprobar',
        'Bombillo' => 'aprobar',
        'Molde' => 'aprobar',
        'Obturador' => 'aprobar'
    ],
    'OT TEST 3 - MITAD Y MITAD' => [
        'Fondo' => 'aprobar',
        'Bombillo' => 'aprobar',
        'Molde' => 'rechazar',
        'Obturador' => 'rechazar'
    ],
    'OT TEST 4 - 1 APROBADO 3 RECHAZADOS' => [
        'Fondo' => 'aprobar',
        'Bombillo' => 'rechazar',
        'Molde' => 'rechazar',
        'Obturador' => 'rechazar'
    ],
    'OT TEST 5 - 3 APROBADOS 1 RECHAZADO' => [
        'Fondo' => 'aprobar',
        'Bombillo' => 'aprobar',
        'Molde' => 'aprobar',
        'Obturador' => 'rechazar'
    ]
];

foreach ($scenarios as $ot => $verdicts) {
    echo "Procesando $ot...\n";
    $aprobados = [];
    $rechazados = [];
    foreach ($verdicts as $tipo => $decision) {
        $req = Request::create('/submit-liberacion', 'POST', [
            'ot' => $ot,
            'accion' => $decision,
            'decision' => $decision,
            'tipo_modelo' => $tipo,
            'motivo_rechazo' => $decision === 'rechazar' ? "Motivo de prueba para $tipo" : null
        ]);
        $response = $controller->submitLiberacion($req);
        // echo "  - $tipo ($decision): " . $response->getStatusCode() . "\n";
        
        if ($decision === 'aprobar') {
            $aprobados[] = $tipo;
        } else {
            $rechazados[] = $tipo;
        }
    }
    
    // Determine global decision
    if (count($aprobados) == 4) {
        $globalDecision = 'aprobar';
    } elseif (count($rechazados) == 4) {
        $globalDecision = 'rechazar';
    } else {
        $globalDecision = 'mixto';
    }
    
    $reqAlert = Request::create('/enviar-alerta-liberacion', 'POST', [
        'ot' => $ot,
        'decision' => $globalDecision,
        'tipo_modelo' => implode(',', array_keys($verdicts)),
        'tipos_aprobados' => implode(',', $aprobados),
        'fecha' => date('Y-m-d'),
        'email_custom' => 'jaxer020406@gmail.com'
    ]);
    try {
        $respAlert = $controller->enviarAlertaLiberacion($reqAlert);
        echo "  - Alerta enviada: " . $respAlert->getStatusCode() . "\n";
    } catch (\Exception $e) {
        echo "  - Error enviando alerta: " . $e->getMessage() . "\n";
    }
}
echo "Done.\n";
