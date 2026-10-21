<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Moldura;
use App\Models\Orden_trabajo;
use App\Models\Clase;
use App\Models\FundicionHistory;
use App\Models\LiberacionModeloFundicion;
use App\Http\Controllers\DibujosFundicionPdfController;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Mail;

class ConfirmacionModeloFlowTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
        Mail::fake();
    }

    public function test_confirmacion_modelo_se_guarda_en_almacen_y_calidad()
    {
        $user = User::factory()->create(['perfil' => '1']); // Admin / Almacén permitido
        $this->actingAs($user);

        // Crear Moldura y OT de prueba
        $moldura = Moldura::create([
            'nombre' => 'Moldura Test CFM',
        ]);

        $otId = '99991';
        $otName = "OT {$otId}";

        $ot = Orden_trabajo::create([
            'id' => $otId,
            'id_moldura' => $moldura->id,
            'cliente' => 'Cliente Test',
        ]);

        Clase::create([
            'id_ot' => $ot->id,
            'nombre' => '1 - BOMBILLO',
            'piezas' => 1,
            'pedido' => 1,
            'finalizada' => 0,
        ]);

        FundicionHistory::create([
            'ot' => $otName,
            'status' => 'activa',
            'ayudas_config' => ['1 - BOMBILLO'],
        ]);

        $fakeFile = UploadedFile::fake()->create('remision_modelo.pdf', 100, 'application/pdf');

        $response = $this->postJson(route('almacen.fundicion.confirmarModelo'), [
            'ot' => $otName,
            'clases_seleccionadas' => ['1 - BOMBILLO'],
            'archivos' => [$fakeFile],
            'fecha' => '2026-10-21 10:00:00',
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        // 1. Verificar que el archivo se guardó físicamente en Almacén
        // 1. Verificar que el archivo se guardó físicamente en Almacén
        $archivosAlmacen = Storage::disk('local')->allFiles("DOCUMENTACION_GIS/ALMACEN_FUNDICION/{$otName}");
        $this->assertNotEmpty($archivosAlmacen, "No se guardó ningún archivo en Almacén");
        $this->assertTrue(
            str_contains(strtolower($archivosAlmacen[0]), 'f_alm_cfm'),
            "El archivo guardado en Almacén no es F_ALM_CFM: {$archivosAlmacen[0]}"
        );

        // 2. Verificar que el archivo se guardó físicamente en Calidad
        $archivosCalidad = Storage::disk('local')->allFiles("DOCUMENTACION_GIS/CALIDAD_FUNDICION/{$otName}");
        $this->assertNotEmpty($archivosCalidad, "No se guardó ningún archivo en Calidad");
        $this->assertTrue(
            str_contains(strtolower($archivosCalidad[0]), 'f_alm_cfm'),
            "El archivo guardado en Calidad no es F_ALM_CFM: {$archivosCalidad[0]}"
        );

        // 3. Verificar que se creó el registro en BD de liberación
        $this->assertDatabaseHas('liberacion_modelos_fundicion', [
            'ot' => $otName,
            'tipo_modelo' => 'Bombillo',
            'estado' => 'pendiente',
            'tipo_origen' => 'con_modelo',
        ]);
    }

    public function test_sincronizador_cron_copy_to_almacen_no_elimina_escaneados()
    {
        $otName = "OT 99992";

        FundicionHistory::create([
            'ot' => $otName,
            'status' => 'activa',
            'ayudas_config' => ['1 - BOMBILLO'],
        ]);

        $almacenPath = "DOCUMENTACION_GIS/ALMACEN_FUNDICION/{$otName}/1 - BOMBILLO/ESCANEADOS/F_ALM_CFM_1-BOMBILLO.pdf";
        $calidadPath = "DOCUMENTACION_GIS/CALIDAD_FUNDICION/{$otName}/1 - BOMBILLO/ESCANEADOS/F_ALM_CFM_1-BOMBILLO.pdf";

        Storage::disk('local')->put($almacenPath, 'contenido de prueba PDF');
        Storage::disk('local')->put($calidadPath, 'contenido de prueba PDF');

        $this->assertTrue(Storage::disk('local')->exists($almacenPath));
        $this->assertTrue(Storage::disk('local')->exists($calidadPath));

        // Ejecutar el sincronizador (lo mismo que corre el cron cada 5 minutos)
        DibujosFundicionPdfController::copyToAlmacen($otName, false);

        // Verificar que los archivos NO fueron eliminados por el sincronizador
        $this->assertTrue(
            Storage::disk('local')->exists($almacenPath),
            "copyToAlmacen eliminó el archivo CFM en Almacén"
        );
        $this->assertTrue(
            Storage::disk('local')->exists($calidadPath),
            "copyToAlmacen eliminó el archivo CFM en Calidad"
        );
    }

    public function test_calidad_get_files_detecta_archivo_cfm()
    {
        $user = User::factory()->create(['perfil' => '1']); // Calidad
        $this->actingAs($user);

        $moldura = Moldura::create(['nombre' => 'Moldura Test Calidad']);
        $otId = '99993';
        $otName = "OT {$otId}";

        $ot = Orden_trabajo::create([
            'id' => $otId,
            'id_moldura' => $moldura->id,
            'cliente' => 'Cliente Test',
        ]);

        Clase::create([
            'id_ot' => $ot->id,
            'nombre' => '1 - BOMBILLO',
            'piezas' => 1,
            'pedido' => 1,
            'finalizada' => 0,
        ]);

        FundicionHistory::create([
            'ot' => $otName,
            'status' => 'activa',
            'ayudas_config' => ['1 - BOMBILLO'],
        ]);

        // Simular archivo CFM existente en Almacén
        $almacenPath = "DOCUMENTACION_GIS/ALMACEN_FUNDICION/{$otName}/1 - BOMBILLO/ESCANEADOS/F_ALM_CFM_1-BOMBILLO.pdf";
        Storage::disk('local')->put($almacenPath, 'contenido de prueba');

        $response = $this->getJson(route('calidad.fundicion.archivos', ['ot' => $otName]));
        $response->assertStatus(200);

        $data = $response->json();
        $this->assertTrue($data['existe']);

        $nombres = collect($data['archivos'])->pluck('nombre')->toArray();
        $encontrado = false;
        foreach ($nombres as $nom) {
            if (str_contains(strtolower($nom), 'f_alm_cfm')) {
                $encontrado = true;
                break;
            }
        }

        $this->assertTrue($encontrado, "Calidad getFiles no detectó el archivo F_ALM_CFM en ESCANEADOS");
    }
}
