<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Metas;
use App\Models\Clase;
use App\Models\Pieza;
use App\Models\SystemLog;
use App\Models\Pza_cepillado;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class RobustProductionTest extends TestCase
{
    // DatabaseTransactions asegura que nada de lo que hagamos en la prueba
    // se guarde permanentemente en tu base de datos real.
    use DatabaseTransactions;

    /** @var User */
    protected $user;
    /** @var User */
    protected $admin;

    protected function setUp(): void
    {
        parent::setUp();
        // Crear usuarios de prueba con diferentes niveles
        $this->user = User::factory()->create(['perfil' => 2, 'matricula' => 'OP-999']);
        $this->admin = User::factory()->create(['perfil' => 1, 'matricula' => 'ADM-999']);
    }

    /**
     * ESCENARIO 1: Seguridad y Acceso.
     * Un operador no debe poder purgar logs ni ver rutas de administración.
     */
    public function test_operator_cannot_access_admin_routes()
    {
        $this->actingAs($this->user);
        
        // Intentar purgar logs (ruta protegida)
        $response = $this->post('/system-logs/purge');
        $response->assertStatus(403); // O 302 si redirige por middleware
    }

    /**
     * ESCENARIO 2: Integridad del Flujo de Registro (Cepillado).
     * Verifica validación, guardado y auditoría en un solo paso.
     */
    public function test_complete_piece_registration_workflow()
    {
        $this->actingAs($this->user);
        
        // Crear dependencias para FK
        $moldura = \Illuminate\Support\Facades\DB::table('molduras')->insertGetId(['nombre' => 'MOLDURA-TEST']);
        \Illuminate\Support\Facades\DB::table('orden_trabajo')->insert(['id' => 'OT-TEST-999', 'id_moldura' => $moldura]);
        \Illuminate\Support\Facades\DB::table('cepillado')->insert(['id' => 999998, 'id_ot' => 'OT-TEST-999', 'id_proceso' => 'PROC-TEST-999']);

        $clase = Clase::factory()->create(['nombre' => 'BOMBILLO', 'id_ot' => 'OT-TEST-999']);
        $meta = Metas::factory()->create([
            'id_usuario' => $this->user->matricula,
            'id_clase' => $clase->id,
            'proceso' => 'Cepillado',
            'maquina' => '5'
        ]);

        $pza_cep = Pza_cepillado::create([
            'id_pza' => 'TEST-123',
            'id_meta' => $meta->id,
            'id_proceso' => 999998,
            'n_pieza' => '10H',
            'n_juego' => '10J',
            'estado' => 1
        ]);

        // Simular el guardado de la pieza
        $response = $this->post('/process_production/storePiece', [
            'meta' => $meta->id,
            'process' => 'Cepillado',
            'piece' => $pza_cep->id,
            'error' => 'Ninguno'
        ]);

        $response->assertRedirect();
        $this->assertEquals(2, $pza_cep->fresh()->estado);
        
        // Verificar registro en tabla maestra 'Pieza'
        $this->assertDatabaseHas('piezas', [
            'n_pieza' => '10H',
            'proceso' => 'Cepillado',
            'id_clase' => $clase->id
        ]);
    }

    /**
     * ESCENARIO 3: Robustez de Auditoría (Detección de Spam).
     * El sistema debe detectar si se registran logs demasiado rápido.
     */
    public function test_system_log_spam_detection()
    {
        $this->actingAs($this->user);
        
        // Dependencias FK
        $moldura = \Illuminate\Support\Facades\DB::table('molduras')->insertGetId(['nombre' => 'MOLDURA-TEST-LOG']);
        \Illuminate\Support\Facades\DB::table('orden_trabajo')->insert(['id' => 'OT-LOG-TEST', 'id_moldura' => $moldura]);
        \Illuminate\Support\Facades\DB::table('cepillado')->insert(['id' => 999999, 'id_ot' => 'OT-LOG-TEST', 'id_proceso' => 'PROC-LOG-TEST']);

        $clase = Clase::factory()->create(['id_ot' => 'OT-LOG-TEST']);

        // Crear una pieza Macho previa para que la consolidación funcione
        Pza_cepillado::create([
            'id_pza' => 'M-TEST-LOG',
            'id_meta' => $clase->id,
            'id_proceso' => 999999,
            'n_pieza' => '1M',
            'n_juego' => '1J',
            'estado' => 2,
            'h_inicio' => '08:00:00'
        ]);

        // 1. Primer registro (Hembra 1H -> Debería consolidar a 1J)
        $this->postJson('/system-logs', [
            'action' => 'Captura Medida',
            'details' => 'completó maquinado de 1H',
            'id_ot' => 'OT-LOG-TEST',
            'id_clase' => $clase->id,
            'meta' => 1,
            'proceso' => 'Cepillado',
            'n_pieza' => '1H',
            'h_inicio' => '08:00:00',
            'h_termino' => '08:10:00'
        ]);

        // Crear Macho 2M para que 2H pueda consolidar
        Pza_cepillado::create([
            'id_pza' => 'M-TEST-LOG-2',
            'id_meta' => $clase->id,
            'id_proceso' => 999999,
            'n_pieza' => '2M',
            'n_juego' => '2J',
            'estado' => 2,
            'h_inicio' => '08:10:00'
        ]);

        // 2. Segundo registro inmediato (2H -> Debería ser Sospechoso y consolidar a 2J)
        $this->postJson('/system-logs', [
            'action' => 'Captura Medida',
            'details' => 'completó maquinado de 2H',
            'id_ot' => 'OT-LOG-TEST',
            'id_clase' => $clase->id,
            'meta' => 1,
            'proceso' => 'Cepillado',
            'n_pieza' => '2H',
            'h_inicio' => '08:10:00',
            'h_termino' => '08:11:00'
        ]);

        $this->assertDatabaseHas('system_logs', [
            'n_pieza' => '2J',
            'action' => 'Captura Sospechosa'
        ]);
    }

    /**
     * ESCENARIO 4: Estabilidad de Reportes PDF.
     * Verificar que las rutas de PDF no exploten.
     */
    public function test_pdf_report_generation_is_stable()
    {
        $this->actingAs($this->admin);
        
        $moldura = \Illuminate\Support\Facades\DB::table('molduras')->insertGetId(['nombre' => 'MOLDURA-PDF']);
        \Illuminate\Support\Facades\DB::table('orden_trabajo')->insert(['id' => 'OT-PDF', 'id_moldura' => $moldura]);
        $clase = Clase::factory()->create(['id_ot' => 'OT-PDF', 'nombre' => 'BOMBILLO']);

        $response = $this->post('/pieces/search', [
            'action' => 'pdf',
            'profile' => 'admin',
            'workOrder' => 'OT-PDF',
            'class' => $clase->nombre
        ]);

        $response->assertStatus(200);
        $this->assertEquals('application/pdf', $response->headers->get('Content-Type'));
    }
}
