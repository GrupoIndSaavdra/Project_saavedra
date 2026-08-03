<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Metas;
use App\Models\Clase;
use App\Models\Pieza;
use App\Models\SystemLog;
use App\Models\Pza_cepillado;
use App\Models\SoldaduraPTA_pza;
use App\Http\Controllers\ProcessProductionController;
use App\Http\Controllers\SystemLogController;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class ProcessRegistrationTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        // Crear usuario y autenticarlo
        $user = User::factory()->create(['matricula' => '12345', 'perfil' => 2]);
        Auth::login($user);
    }

    /**
     * Prueba: Registro de una pieza en un proceso estándar (Cepillado).
     */
    public function test_standard_process_registration_and_logging()
    {
        $user = User::factory()->create(['matricula' => '12345', 'perfil' => 2]);
        $this->actingAs($user);

        // 1. Setup de datos con todas las dependencias requeridas
        $moldura = DB::table('molduras')->insertGetId(['nombre' => 'MOLDURA-REG']);
        DB::table('orden_trabajo')->insert(['id' => 'OT-REG', 'id_moldura' => $moldura]);
        $clase = Clase::factory()->create(['nombre' => 'BOMBILLO', 'id_ot' => 'OT-REG']);
        
        $meta = Metas::factory()->create([
            'id_usuario' => '12345',
            'id_clase' => $clase->id,
            'proceso' => 'Cepillado',
            'maquina' => 'M-REG-01'
        ]);

        // REGLA: Registrar la máquina para la meta
        \App\Models\Maquinas::create([
            'maquina' => 'M-REG-01',
            'proceso' => 'Cepillado',
            'id_meta' => $meta->id
        ]);

        // REGLA: Registrar el proceso técnico
        DB::table('cepillado')->insert(['id' => 999991, 'id_ot' => 'OT-REG', 'id_proceso' => 'PROC-REG']);

        // REGLA: Registrar medidas nominales y tolerancias
        $idProcess = "Cepillado_" . $clase->nombre . "_OT-REG";
        DB::table('cepillado_cnominal')->insert(['id_proceso' => $idProcess, 'radiof_mordaza' => 10]);
        DB::table('cepillado_tolerancia')->insert(['id_proceso' => $idProcess, 'radiof_mordaza1' => 1, 'radiof_mordaza2' => 1]);

        // Crear la pieza inicial
        $pza_cep = Pza_cepillado::create([
            'id_pza' => '10H_REG',
            'id_meta' => $meta->id,
            'id_proceso' => 1, // ID técnico
            'n_pieza' => '10H',
            'estado' => 1
        ]);

        // 2. Ejecutar registro vía HTTP para probar todo el middleware
        $response = $this->postJson('/process_production/storePiece', [
            'meta' => $meta->id,
            'process' => 'Cepillado',
            'piece' => $pza_cep->id,
            'error' => 'Ninguno'
        ]);

        $response->assertStatus(302); // Redirect back con success

        // 3. Verificaciones
        $this->assertEquals(2, $pza_cep->fresh()->estado);
        $this->assertDatabaseHas('piezas', ['n_pieza' => '10H', 'proceso' => 'Cepillado']);
    }

    /**
     * Prueba: Consolidación de logs en Soldadura PTA.
     */
    public function test_pta_consolidated_logging()
    {
        $user = User::factory()->create(['matricula' => '54321', 'perfil' => 2]);
        $this->actingAs($user);

        $clase = Clase::factory()->create(['nombre' => 'OBTURADOR', 'id_ot' => 'OT-PTA']);
        
        // Simular llamada al SystemLogController
        $response = $this->postJson('/system-logs', [
            'action' => 'Captura Medida',
            'details' => 'completó el maquinado del juego 10J',
            'id_ot' => 'OT-PTA',
            'id_clase' => $clase->id,
            'proceso' => 'Soldadura PTA',
            'n_pieza' => '10J',
            'maquina' => '1',
            'h_inicio' => '10:00:00',
            'h_termino' => '10:15:00'
        ]);

        $response->assertStatus(200);

        // Verificar que el log existe
        $this->assertDatabaseHas('system_logs', [
            'n_pieza' => '10J',
            'proceso' => 'Soldadura PTA'
        ]);
    }
}
