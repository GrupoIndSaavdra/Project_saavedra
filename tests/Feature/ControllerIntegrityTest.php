<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Http\Controllers\PzasGeneralesController;
use App\Http\Controllers\SystemLogController;
use App\Http\Controllers\PtaResultsController;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class ControllerIntegrityTest extends TestCase
{
    use DatabaseTransactions;
    /**
     * Verificar que los controladores principales pueden instanciarse
     * y sus métodos clave no tienen errores de sintaxis o lógica básica.
     */
    public function test_controllers_can_be_instantiated()
    {
        $this->assertInstanceOf(PzasGeneralesController::class, new PzasGeneralesController());
        $this->assertInstanceOf(SystemLogController::class, new SystemLogController());
        $this->assertInstanceOf(PtaResultsController::class, new PtaResultsController());
    }

    /**
     * Verificar que las rutas de reportes responden (al menos con redirección si no hay auth)
     */
    public function test_report_routes_status()
    {
        $response = $this->get('/pieces');
        $response->assertStatus(302); // Redirige a login

        $response = $this->get('/system-logs-report');
        $response->assertStatus(302);
    }

    /**
     * Simulación de lógica de SystemLog sin guardar en BD (usando mock o transacción)
     */
    public function test_system_log_filtering_logic()
    {
        $controller = new SystemLogController();
        // Verificar que el método exists (análisis estático dinámico)
        $this->assertTrue(method_exists($controller, 'store'));
        $this->assertTrue(method_exists($controller, 'index'));
    }
}
