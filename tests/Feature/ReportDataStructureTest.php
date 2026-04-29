<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Http\Controllers\PzasGeneralesController;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use App\Models\User;
use App\Models\Clase;
use App\Models\Pieza;
use Illuminate\Support\Collection;

class ReportDataStructureTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * Verificar que saveInArray produzca la estructura de índices correcta usando la BD.
     */
    public function test_save_in_array_structure_with_db()
    {
        $controller = new PzasGeneralesController();

        // 1. Crear datos necesarios en la BD
        $user = User::factory()->create(['matricula' => '100', 'nombre' => 'Test', 'a_paterno' => 'User', 'a_materno' => '']);
        $clase = Clase::factory()->create(['id' => 1, 'nombre' => 'Clase A', 'id_ot' => '12345', 'tamanio' => 'Standard']);

        // 2. Crear una pieza (usando el modelo real)
        $pieza = new Pieza();
        $pieza->id_ot = '12345';
        $pieza->n_pieza = '10H';
        $pieza->id_operador = '100';
        $pieza->proceso = 'Cepillado';
        $pieza->error = 'Ninguno';
        $pieza->created_at = '2026-01-01 10:00:00';
        $pieza->fecha_liberacion = null;
        $pieza->user_liberacion = null;
        $pieza->liberacion = 0;
        $pieza->id_clase = $clase->id;
        $pieza->maquina = '1';
        $pieza->save();

        $collection = Pieza::all();

        // 3. Ejecutar el método
        $result = $controller->saveInArray($collection);

        $this->assertNotEmpty($result, "El resultado de saveInArray no debe estar vacío");
        $firstRow = $result[0];

        // 4. Verificamos índices críticos para PDF y JS
        $this->assertEquals('12345', $firstRow[0], "Índice 0 debe ser OT");
        $this->assertEquals('10H', $firstRow[1], "Índice 1 debe ser N Pieza");
        $this->assertEquals('Test User ', $firstRow[2], "Índice 2 debe ser Operador");
        $this->assertEquals('1', $firstRow[3], "Índice 3 debe ser Maquina");
        $this->assertEquals('Cepillado', $firstRow[4], "Índice 4 debe ser Proceso");
        $this->assertEquals('Ninguno', $firstRow[5], "Índice 5 debe ser Error");
        $this->assertEquals(0, $firstRow[9], "Índice 9 debe ser Liberación");
        $this->assertEquals('mitad', $firstRow[10], "Índice 10 debe ser Tipo (mitad/juego)");
    }
}
