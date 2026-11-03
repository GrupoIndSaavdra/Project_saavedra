<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Clase;
use App\Models\Procesos;
use App\Models\Orden_trabajo;
use App\Http\Controllers\ClassController;
use App\Http\Controllers\ProcessesController;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;

class ClassProcessProtectionTest extends TestCase
{
    use DatabaseTransactions;

    protected User $user;
    protected string $otId;
    protected int $molduraId;

    protected function setUp(): void
    {
        parent::setUp();

        // Crear usuario autenticado para pruebas
        $this->user = User::create([
            'matricula' => 'TEST_' . rand(10000, 99999),
            'nombre' => 'TestQA',
            'a_paterno' => 'Dev',
            'a_materno' => 'Saavedra',
            'contrasena' => bcrypt('secret123'),
            'perfil' => 1,
        ]);
        $this->actingAs($this->user);

        // Crear moldura y OT de prueba
        $this->molduraId = DB::table('molduras')->insertGetId([
            'nombre' => 'MOLDURA_TEST_' . rand(1000, 9999),
        ]);

        $this->otId = 'OT_TEST_' . rand(1000, 9999);
        Orden_trabajo::create([
            'id' => $this->otId,
            'id_moldura' => $this->molduraId,
        ]);
    }

    /**
     * Helper para crear una clase y sus procesos asociados
     */
    protected function createClassWithProcesses(string $className = '3 - BOMBILLO'): array
    {
        $clase = Clase::create([
            'id_ot' => $this->otId,
            'nombre' => $className,
            'pedido' => 40,
            'piezas' => 40,
            'fecha_inicio' => now()->toDateString(),
            'hora_inicio' => '08:00:00',
            'finalizada' => 0,
            'tamanio' => 'Chico',
        ]);

        $procesos = new Procesos();
        $procesos->id_clase = $clase->id;
        $procesos->cepillado = 2; // 2 máquinas
        $procesos->desbaste_exterior = 1; // 1 máquina
        $procesos->calificado = 1; // 1 máquina
        $procesos->save();

        return [$clase, $procesos];
    }

    /**
     * 1. Verifica que la normalización de nombres de clase funcione con prefijos, sufijos y mayúsculas/minúsculas.
     */
    public function test_clase_normalize_class_name_handles_various_formats(): void
    {
        $this->assertSame('Bombillo', Clase::normalizeClassName('3 - BOMBILLO'));
        $this->assertSame('Bombillo', Clase::normalizeClassName('bombillo'));
        $this->assertSame('Molde', Clase::normalizeClassName('1 - MOLDE'));
        $this->assertSame('Corona', Clase::normalizeClassName('4 - CORONA'));
        $this->assertSame('Fondo', Clase::normalizeClassName('6 - FONDO'));
        $this->assertSame('Obturador', Clase::normalizeClassName('8 - OBTURADOR'));
        $this->assertSame('Cabeza de Soplo', Clase::normalizeClassName('7 - CABEZA SOPLO'));
        $this->assertSame('Cabeza de Soplo', Clase::normalizeClassName('CABEZA DE SOPLO'));
        $this->assertSame('Plato', Clase::normalizeClassName('PLATO'));
        $this->assertSame('Embudo', Clase::normalizeClassName('EMBUDO'));
        $this->assertSame('Candado Obturador', Clase::normalizeClassName('CANDADO'));

        // Excluidos
        $this->assertSame('', Clase::normalizeClassName('PORTA DADOS'));
        $this->assertSame('', Clase::normalizeClassName('BASE'));
        $this->assertSame('', Clase::normalizeClassName('TIP'));
    }

    /**
     * 2. Verifica que getDefaultProcessesByClass devuelva los procesos esperados incluso con prefijos de OT ("3 - BOMBILLO").
     */
    public function test_get_default_processes_by_class_returns_valid_processes_for_prefixed_classes(): void
    {
        $controller = new ProcessesController();

        // Bombillo
        $bombilloProcs = $controller->getDefaultProcessesByClass('3 - BOMBILLO');
        $this->assertIsArray($bombilloProcs);
        $this->assertContains('Cepillado', $bombilloProcs);
        $this->assertContains('Acabado Bombillo', $bombilloProcs);
        $this->assertContains('Barreno Profundidad', $bombilloProcs);

        // Molde
        $moldeProcs = $controller->getDefaultProcessesByClass('1 - MOLDE');
        $this->assertIsArray($moldeProcs);
        $this->assertContains('Acabado Molde', $moldeProcs);
        $this->assertContains('Barreno Profundidad', $moldeProcs);

        // Corona
        $coronaProcs = $controller->getDefaultProcessesByClass('4 - CORONA');
        $this->assertIsArray($coronaProcs);
        $this->assertContains('Cepillado', $coronaProcs);
        $this->assertContains('Calificado', $coronaProcs);

        // Fondo
        $fondoProcs = $controller->getDefaultProcessesByClass('6 - FONDO');
        $this->assertIsArray($fondoProcs);
        $this->assertContains('Operacion Equipo', $fondoProcs);

        // Cabeza de Soplo
        $soploProcs = $controller->getDefaultProcessesByClass('7 - CABEZA SOPLO');
        $this->assertIsArray($soploProcs);
        $this->assertContains('Primera Operacion Cabeza Soplo', $soploProcs);
        $this->assertContains('Segunda Operacion Cabeza Soplo', $soploProcs);

        // Clase inexistente retorna null sin arrojar excepciones
        $this->assertNull($controller->getDefaultProcessesByClass('INEXISTENTE_XYZ'));
    }

    /**
     * 3. Verifica que storeProcess NO resetee a 0 los procesos configurados cuando $dataProcess es null en un registro existente.
     */
    public function test_store_process_protects_existing_configuration_when_data_process_is_null(): void
    {
        [$clase, $procesos] = $this->createClassWithProcesses('3 - BOMBILLO');

        $controller = new ClassController();
        // Llamada con $dataProcess = null (como sucedía al guardar sin checkboxes)
        $controller->storeProcess($clase, null, null, $procesos);

        $procesos->refresh();

        // Los procesos DEBEN permanecer activos (valor > 0 y máquinas intactas)
        $this->assertEquals(2, $procesos->cepillado);
        $this->assertEquals(1, $procesos->desbaste_exterior);
        $this->assertEquals(1, $procesos->calificado);
    }

    /**
     * 4. Verifica que una edición parcial desde Almacén (from_almacen = 1) no elimine los procesos configurados.
     */
    public function test_almacen_partial_edit_does_not_wipe_configured_processes(): void
    {
        [$clase, $procesos] = $this->createClassWithProcesses('3 - BOMBILLO');

        $response = $this->post(route('saveClass'), [
            'idClass' => $clase->id,
            'workOrder' => $this->otId,
            'from_almacen' => 1,
            'order' => 55,
            'pieces' => 55,
            'material' => 'Hierro Dúctil',
            // Sin operations ni from_programming
        ]);

        $response->assertSessionHasNoErrors();

        $clase->refresh();
        $procesos->refresh();

        // El pedido se actualizó
        $this->assertEquals(55, $clase->pedido);
        $this->assertEquals(55, $clase->piezas);

        // Los procesos NO fueron sobreescritos a 0
        $this->assertEquals(2, $procesos->cepillado);
        $this->assertEquals(1, $procesos->desbaste_exterior);
        $this->assertEquals(1, $procesos->calificado);
    }

    /**
     * 5. Verifica que el flag partial_edit impida la sobreescritura accidental de procesos.
     */
    public function test_partial_edit_flag_preserves_processes_without_operations(): void
    {
        [$clase, $procesos] = $this->createClassWithProcesses('1 - MOLDE');

        $response = $this->post(route('saveClass'), [
            'idClass' => $clase->id,
            'workOrder' => $this->otId,
            'partial_edit' => 1,
            'order' => 80,
            'pieces' => 80,
        ]);

        $response->assertSessionHasNoErrors();

        $clase->refresh();
        $procesos->refresh();

        $this->assertEquals(80, $clase->pedido);
        $this->assertEquals(2, $procesos->cepillado);
        $this->assertEquals(1, $procesos->desbaste_exterior);
    }

    /**
     * 6. Verifica que guardar desde la vista de programación (from_programming = 1) actualice procesos y máquinas correctamente.
     */
    public function test_programming_submission_updates_processes_and_machines_correctly(): void
    {
        [$clase, $procesos] = $this->createClassWithProcesses('3 - BOMBILLO');

        $response = $this->post(route('saveClass'), [
            'idClass' => $clase->id,
            'workOrder' => $this->otId,
            'from_programming' => 1,
            'operations' => ['cepillado', 'desbaste_exterior', 'barreno_profundidad'],
            'machines' => [
                'cepillado' => 3,
                'desbaste_exterior' => 2,
                'barreno_profundidad' => 1,
            ],
        ]);

        $response->assertSessionHasNoErrors();

        $procesos->refresh();

        // Procesos seleccionados deben tener su número de máquinas
        $this->assertEquals(3, $procesos->cepillado);
        $this->assertEquals(2, $procesos->desbaste_exterior);
        $this->assertEquals(1, $procesos->barreno_profundidad);

        // Proceso no seleccionado (calificado) debe estar apagado (0)
        $this->assertEquals(0, $procesos->calificado);
    }
}
