<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Metas;
use App\Models\Clase;
use App\Models\Pieza;
use App\Models\Pza_cepillado;
use App\Models\Cavidades_pza;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class ProductionFlowTest extends TestCase
{
    use DatabaseTransactions;

    /** @var User */
    protected $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create(['perfil' => 2, 'matricula' => 'OP-FLOW']);
    }

    /**
     * Prueba el flujo de una pieza a través de múltiples procesos.
     */
    public function test_piece_moves_through_multiple_processes()
    {
        $this->actingAs($this->user);

        // 1. CREACIÓN DE ESTRUCTURA COMPLETA (OT -> CLASE -> METAS)
        $molduraId = DB::table('molduras')->insertGetId([
            'nombre' => 'MOLDURA-PREMIUM-01',
            'created_at' => now(),
            'updated_at' => now()
        ]);

        $otId = 'OT-PROD-2026';
        DB::table('orden_trabajo')->insert([
            'id' => $otId,
            'id_moldura' => $molduraId,
            'created_at' => now()
        ]);

        $clase = Clase::factory()->create([
            'id_ot' => $otId,
            'nombre' => 'OBTURADOR',
            'tamanio' => '12.5 INCH',
            'seccion' => 1,
            'piezas' => 10,
            'pedido' => 10,
            'fecha_inicio' => now()->format('Y-m-d'),
            'hora_inicio' => '08:00:00'
        ]);

        // 2. PASO 1: REGISTRO EN CEPILLADO
        // REGLA: Registrar el proceso técnico
        DB::table('cepillado')->insert(['id' => 999990, 'id_ot' => $otId, 'id_proceso' => 'PROC-REG']);

        // REGLA: Registrar medidas nominales y tolerancias
        $idProcessCep = "Cepillado_" . $clase->nombre . "_" . $otId;
        DB::table('cepillado_cnominal')->insert([
            'id_proceso' => $idProcessCep,
            'radiof_mordaza' => 10.0, 'radiof_mayor' => 10.0, 'radiof_sufridera' => 10.0,
            'profuFinal_CFC' => 10.0, 'profuFinal_mitadMB' => 10.0, 'profuFinal_PCO' => 10.0,
            'ensamble' => 10.0, 'distancia_barrenoAli' => 10.0, 'profu_barrenoAliHembra' => 10.0,
            'profu_barrenoAliMacho' => 10.0, 'altura_venaHembra' => 10.0, 'altura_venaMacho' => 10.0,
            'ancho_vena' => 10.0, 'pin1' => 10.0, 'pin2' => 10.0, 'laterales' => 10.0
        ]);
        DB::table('cepillado_tolerancia')->insert([
            'id_proceso' => $idProcessCep,
            'radiof_mordaza1' => 0.1, 'radiof_mordaza2' => 0.1, 'radiof_mayor1' => 0.1, 'radiof_mayor2' => 0.1,
            'radiof_sufridera1' => 0.1, 'radiof_sufridera2' => 0.1, 'profuFinal_CFC1' => 0.1, 'profuFinal_CFC2' => 0.1,
            'profuFinal_mitadMB1' => 0.1, 'profuFinal_mitadMB2' => 0.1, 'profuFinal_PCO1' => 0.1, 'profuFinal_PCO2' => 0.1,
            'ensamble1' => 0.1, 'ensamble2' => 0.1, 'distancia_barrenoAli1' => 0.1, 'distancia_barrenoAli2' => 0.1,
            'profu_barrenoAliHembra1' => 0.1, 'profu_barrenoAliHembra2' => 0.1, 'profu_barrenoAliMacho1' => 0.1, 'profu_barrenoAliMacho2' => 0.1,
            'altura_venaHembra1' => 0.1, 'altura_venaHembra2' => 0.1, 'altura_venaMacho1' => 0.1, 'altura_venaMacho2' => 0.1,
            'ancho_vena1' => 0.1, 'ancho_vena2' => 0.1, 'pin1' => 0.1, 'pin2' => 0.1, 'laterales1' => 0.1, 'laterales2' => 0.1
        ]);

        $metaCep = Metas::factory()->create([
            'id_ot' => $otId,
            'id_usuario' => $this->user->matricula,
            'id_clase' => $clase->id,
            'proceso' => 'Cepillado',
            'maquina' => 'M-CEP-01'
        ]);

        \App\Models\Maquinas::create([
            'maquina' => 'M-CEP-01',
            'proceso' => 'Cepillado',
            'id_meta' => $metaCep->id
        ]);

        // Simulamos que la pieza existe físicamente
        $idProcesoCep = DB::table('cepillado')->insertGetId(['id_proceso' => 'PROC-CEP-1', 'id_ot' => $otId]);
        
        $pza_cep = Pza_cepillado::create([
            'id_pza' => 'PZA-FL-01-CEP',
            'id_meta' => $metaCep->id,
            'id_proceso' => $idProcesoCep,
            'n_pieza' => '1M',
            'n_juego' => '1J',
            'estado' => 1
        ]);

        // El operador guarda la pieza en Cepillado
        $this->postJson('/processProduction/storePiece', [
            'meta' => $metaCep->id,
            'process' => 'Cepillado',
            'piece' => $pza_cep->id,
            'error' => 'Ninguno'
        ])->assertStatus(302);

        // Simular el log que enviaría el JS del frontend
        $res1 = $this->postJson('/system-logs', [
            'action' => 'Captura Medida',
            'details' => 'completó maquinado de 1M',
            'id_ot' => $otId,
            'id_clase' => $clase->id,
            'proceso' => 'Cepillado',
            'n_pieza' => '1M',
            'h_inicio' => '08:00:00',
            'h_termino' => '08:10:00'
        ]);
        $res1->assertSuccessful();

        // Verificar que Cepillado terminó
        $this->assertEquals(2, $pza_cep->fresh()->estado, "La pieza debería estar terminada en Cepillado");

        // 3. PASO 2: TRÁNSITO A CAVIDADES
        // Creamos la meta para el siguiente proceso
        // Dependencias Técnicas (CNominal y Tolerancia) para Cavidades
        $idProcessCav = "Cavidades_" . $clase->nombre . "_" . $otId;
        DB::table('cavidades_cnominal')->insert([
            'id_proceso' => $idProcessCav,
            'profundidad1' => 10.0, 'diametro1' => 10.0, 'profundidad2' => 10.0, 'diametro2' => 10.0, 'profundidad3' => 10.0, 'diametro3' => 10.0
        ]);
        DB::table('cavidades_tolerancia')->insert([
            'id_proceso' => $idProcessCav,
            'profundidad1_1' => 0.1, 'profundidad2_1' => 0.1, 'diametro1_1' => 0.1, 'diametro2_1' => 0.1,
            'profundidad1_2' => 0.1, 'profundidad2_2' => 0.1, 'diametro1_2' => 0.1, 'diametro2_2' => 0.1,
            'profundidad1_3' => 0.1, 'profundidad2_3' => 0.1, 'diametro1_3' => 0.1, 'diametro2_3' => 0.1
        ]);

        $metaCav = Metas::factory()->create([
            'id_ot' => $otId,
            'id_usuario' => $this->user->matricula,
            'id_clase' => $clase->id,
            'proceso' => 'Cavidades',
            'maquina' => 'M-CAV-05'
        ]);

        \App\Models\Maquinas::create([
            'maquina' => 'M-CAV-05',
            'proceso' => 'Cavidades',
            'id_meta' => $metaCav->id
        ]);

        $idProcesoCav = DB::table('cavidades')->insertGetId(['id_proceso' => 'PROC-CAV-1', 'id_ot' => $otId]);

        // La pieza debe "nacer" en el siguiente proceso
        $pza_cav = Cavidades_pza::create([
            'id_pza' => 'PZA-FL-01-CAV',
            'id_meta' => $metaCav->id,
            'id_proceso' => $idProcesoCav,
            'n_pieza' => '1M',
            'n_juego' => '1J',
            'estado' => 1
        ]);

        // Guardamos en Cavidades
        $this->postJson('/processProduction/storePiece', [
            'meta' => $metaCav->id,
            'process' => 'Cavidades',
            'piece' => $pza_cav->id,
            'error' => 'Ninguno'
        ])->assertStatus(302);

        // Simular el log de Cavidades
        $res2 = $this->postJson('/system-logs', [
            'action' => 'Captura Medida',
            'details' => 'completó maquinado de 1M',
            'id_ot' => $otId,
            'id_clase' => $clase->id,
            'proceso' => 'Cavidades',
            'n_pieza' => '1M',
            'h_inicio' => '10:10:00',
            'h_termino' => '10:20:00'
        ]);
        $res2->assertSuccessful();

        // 4. VERIFICACIÓN FINAL: TABLA MAESTRA
        // La tabla 'piezas' debe tener ambos registros, trazando el camino
        $this->assertDatabaseHas('piezas', [
            'id_ot' => $otId,
            'n_pieza' => '1M',
            'proceso' => 'Cepillado'
        ]);

        $this->assertDatabaseHas('piezas', [
            'id_ot' => $otId,
            'n_pieza' => '1M',
            'proceso' => 'Cavidades'
        ]);
        
        // Verificar que los logs de auditoría existan para ambos pasos
        $this->assertDatabaseHas('system_logs', [
            'ot' => 'OT-PROD-2026 - MOLDURA-PREMIUM-01',
            'proceso' => 'Cepillado',
            'n_pieza' => '1M'
        ]);

        $this->assertDatabaseHas('system_logs', [
            'ot' => 'OT-PROD-2026 - MOLDURA-PREMIUM-01',
            'proceso' => 'Cavidades',
            'n_pieza' => '1M'
        ]);
    }
}
