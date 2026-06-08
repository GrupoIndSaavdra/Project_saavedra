<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\FundicionHistory;
use App\Models\LiberacionModeloFundicion;
use App\Models\ScarModelo;
use App\Models\PreOrdenFundicion;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Mail;
use App\Mail\LiberacionModeloMailable;

class AlmacenFundicionControllerTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
        Mail::fake();
    }

    public function test_unauthenticated_user_cannot_access_get_files()
    {
        $response = $this->getJson(route('almacen.fundicion.archivos', ['ot' => 'OT-TEST']));
        $response->assertStatus(401);
    }

    public function test_non_permitted_profile_cannot_access_get_files()
    {
        $user = User::factory()->create(['perfil' => '3']); // Profile 3 not allowed
        $this->actingAs($user);

        $response = $this->getJson(route('almacen.fundicion.archivos', ['ot' => 'OT-TEST']));
        $response->assertStatus(403);
    }

    public function test_get_files_returns_no_files_if_history_does_not_exist()
    {
        $user = User::factory()->create(['perfil' => '4']); // Quality profile
        $this->actingAs($user);

        $response = $this->getJson(route('almacen.fundicion.archivos', ['ot' => 'OT-NON-EXISTENT']));
        $response->assertJson([
            'existe' => false,
            'archivos' => []
        ]);
    }

    public function test_attachment_parsing_and_routing_in_enviar_alerta_liberacion()
    {
        // 1. Setup mock files on fake local storage
        $ot = 'OT-TEST-123';
        $folderName = 'OT-TEST-123';
        
        $almacenDir = 'DOCUMENTACION_GIS/ALMACEN_FUNDICION/' . $folderName;
        $calidadDir = 'DOCUMENTACION_GIS/CALIDAD_FUNDICION/' . $folderName;

        // Create drawing file in Almacen
        Storage::disk('local')->put($almacenDir . '/Dibujo_Bombillo.pdf', 'dummy pdf content');
        // Create visual aid file in Almacen
        Storage::disk('local')->put($almacenDir . '/ayudas_visuales/Ayuda_Fondo.pdf', 'dummy pdf content');
        // Create preorden file in Calidad
        Storage::disk('local')->put($calidadDir . '/ayudas_visuales/preordenes/Pre-Orden_OT-TEST-123.pdf', 'dummy pdf content');
        // Create rejection file in Calidad
        Storage::disk('local')->put($calidadDir . '/ayudas_visuales/preordenes/documentos_rechazados/molde/SCAR_FOTO_Rechazado.png', 'dummy image content');

        // 2. Setup database records using Transactions (will be rolled back)
        $user = User::factory()->create(['perfil' => '4']); // Quality profile
        $this->actingAs($user);

        // Create FundicionHistory
        FundicionHistory::create([
            'ot' => $ot,
            'tiene_modelo' => true,
            'alert_sent_at' => now(),
            'calidad_revision_status' => 'espera'
        ]);

        // Create LiberacionModeloFundicion
        LiberacionModeloFundicion::create([
            'ot' => $ot,
            'tipo_modelo' => 'Bombillo',
            'decision' => 'aprobar',
            'estado' => 'espera',
            'pdf_filename' => 'F-CCL-LDM_BOMBILLO_APROBADO.pdf'
        ]);

        LiberacionModeloFundicion::create([
            'ot' => $ot,
            'tipo_modelo' => 'Molde',
            'decision' => 'rechazar',
            'estado' => 'espera',
            'pdf_filename' => 'F-CCL-LDM_MOLDE_RECHAZADO.pdf'
        ]);

        // 3. Perform enviarAlertaLiberacion POST request
        $response = $this->postJson(route('almacen.fundicion.enviarAlertaLiberacion'), [
            'ot' => $ot,
            'decision' => 'mixto',
            'tipo_modelo' => 'Bombillo, Molde',
            'fecha' => '2026-06-02',
            'destinatario' => 'test@example.com',
            // Selected files
            'dibujos_aprobados' => ['Dibujo_Bombillo.pdf'],
            'dibujos_rechazados' => ['preordenes/documentos_rechazados/molde/SCAR_FOTO_Rechazado.png']
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        // 4. Assert emails were sent with correct attachments
        Mail::assertSent(LiberacionModeloMailable::class, function ($mail) {
            if ($mail->estado === 'aprobado') {
                $attachments = $mail->emailAttachments;
                $hasDibujo = false;
                foreach ($attachments as $att) {
                    if (str_ends_with($att['name'], 'Dibujo_Bombillo.pdf')) {
                        $hasDibujo = true;
                    }
                }
                return $hasDibujo;
            }
            return true;
        });

        Mail::assertSent(LiberacionModeloMailable::class, function ($mail) {
            if ($mail->estado === 'rechazado') {
                $attachments = $mail->emailAttachments;
                $hasRechazo = false;
                foreach ($attachments as $att) {
                    if (str_ends_with($att['name'], 'SCAR_FOTO_Rechazado.png')) {
                        $hasRechazo = true;
                    }
                }
                return $hasRechazo;
            }
            return true;
        });
    }

    public function test_enviar_alerta_liberacion_fails_without_fecha()
    {
        $user = User::factory()->create(['perfil' => '4']); // Quality profile
        $this->actingAs($user);

        $response = $this->postJson(route('almacen.fundicion.enviarAlertaLiberacion'), [
            'ot' => 'OT-TEST-123',
            'decision' => 'aprobar',
            'tipo_modelo' => 'Bombillo',
            // 'fecha' is missing
            'destinatario' => 'test@example.com'
        ]);

        $response->assertStatus(422);
        $response->assertJsonFragment([
            'success' => false,
            'message' => 'Campos obligatorios incompletos: fecha'
        ]);
    }

    public function test_store_pre_orden_casting_generates_and_saves_correctly()
    {
        $user = User::factory()->create(['perfil' => '2']); // Warehouse profile
        $this->actingAs($user);

        $ot = 'OT-CAST-123';
        
        $pData = [
            'type' => 'casting',
            'has_page2' => false,
            'page1' => [
                'ot_raw' => $ot,
                'proveedor' => 'SS Metal Foundry, S. de R. L. de C. V.',
                'fecha_creacion' => '2026-06-08',
                'folio' => 'MOD-2026-0110',
                'moldura' => 'ESPOLON 75 CL IS6180',
                'ot' => $ot,
                'observaciones' => 'Test observations',
                'filas' => [
                    [
                        'id_clase' => '1',
                        'tipo_modelo' => 'Molde',
                        'cantidad_fabricar' => 5,
                        'cant_fabricar' => 5,
                        'cantidad_consignacion' => 0,
                        'cant_consignacion' => 0,
                        'descripcion' => 'Molde de prueba',
                        'material' => 'Hierro Gris',
                        'codigo_modelo' => 'M-1234',
                        'peso_juego' => 10.5,
                        'peso_total' => 52.5,
                        'fecha_entrega' => '2026-06-15'
                    ]
                ]
            ]
        ];

        $response = $this->postJson(route('almacen.fundicion.storePreOrden'), $pData);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'message' => 'Pre-Orden de Casting guardada correctamente.'
        ]);

        $this->assertDatabaseHas('pre_ordenes_fundicion', [
            'ot' => $ot,
            'proveedor' => 'SS Metal Foundry, S. de R. L. de C. V.',
            'folio' => 'MOD-2026-0110'
        ]);
    }

    public function test_send_email_pre_orden_casting_updates_status_and_sends_email()
    {
        $user = User::factory()->create(['perfil' => '2']); // Warehouse profile
        $this->actingAs($user);

        $ot = 'OT-CAST-123';

        // Create FundicionHistory
        FundicionHistory::create([
            'ot' => $ot,
            'tiene_modelo' => true,
            'alert_sent_at' => now(),
            'calidad_revision_status' => 'espera'
        ]);

        // Create PreOrdenFundicion
        PreOrdenFundicion::create([
            'ot' => $ot,
            'proveedor' => 'SS Metal Foundry, S. de R. L. de C. V.',
            'folio' => 'MOD-2026-0110',
            'pdf_filename' => 'Pre-Orden_Casting-MOD-2026-0110_OT_CAST_123.pdf',
            'fecha_creacion' => now(),
            'filas' => []
        ]);


        // Setup mock file on fake local storage
        $folderName = 'OT-CAST-123';
        $preOrdenPdfPath = 'DOCUMENTACION_GIS/ALMACEN_FUNDICION/' . $folderName . '/ayudas_visuales/preordenes/documentos_aprobados/Pre-Orden_Casting-MOD-2026-0110_OT_CAST_123.pdf';
        Storage::disk('local')->put($preOrdenPdfPath, 'dummy pdf content');

        // Prepare request
        $file = \Illuminate\Http\UploadedFile::fake()->create('scanned_po.pdf', 100);

        $response = $this->postJson(route('almacen.fundicion.sendEmailPreOrden'), [
            'ot' => $ot,
            'destinatario' => 'test@example.com',
            'fecha_entrega' => '2026-06-20',
            'archivos_adicionales' => [$file]
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true
        ]);

        $this->assertDatabaseHas('fundicion_history', [
            'ot' => $ot,
            'pre_orden_email_sent' => true,
            'calidad_revision_status' => 'casting_aprobado'
        ]);
    }

    public function test_send_email_pre_orden_modelo_only_updates_pre_orden_email_sent()
    {
        $user = User::factory()->create(['perfil' => '2']); // Warehouse profile
        $this->actingAs($user);

        $ot = 'OT-MODEL-456';

        // Create FundicionHistory
        FundicionHistory::create([
            'ot' => $ot,
            'tiene_modelo' => false,
            'alert_sent_at' => now(),
            'calidad_revision_status' => 'espera'
        ]);

        // Create PreOrdenFundicion (no casting in filename)
        PreOrdenFundicion::create([
            'ot' => $ot,
            'proveedor' => 'Provider A',
            'folio' => 'MOD-2026-0200',
            'pdf_filename' => 'Pre-Orden_Fundicion-MOD-2026-0200_OT_MODEL_456.pdf',
            'fecha_creacion' => now(),
            'filas' => []
        ]);

        // Setup mock file on fake local storage
        $folderName = 'OT-MODEL-456';
        $preOrdenPdfPath = 'DOCUMENTACION_GIS/ALMACEN_FUNDICION/' . $folderName . '/ayudas_visuales/preordenes/Pre-Orden_Fundicion-MOD-2026-0200_OT_MODEL_456.pdf';
        Storage::disk('local')->put($preOrdenPdfPath, 'dummy pdf content');

        // Prepare request
        $file = \Illuminate\Http\UploadedFile::fake()->create('scanned_po.pdf', 100);

        $response = $this->postJson(route('almacen.fundicion.sendEmailPreOrden'), [
            'ot' => $ot,
            'tipo' => 'modelo',
            'destinatario' => 'test@example.com',
            'fecha_entrega' => '2026-06-20',
            'archivos_adicionales' => [$file]
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true
        ]);

        $this->assertDatabaseHas('fundicion_history', [
            'ot' => $ot,
            'pre_orden_email_sent' => true,
            'calidad_revision_status' => 'espera' // should NOT change to casting_aprobado
        ]);
    }

    public function test_store_pre_orden_casting_with_two_suppliers_generates_single_combined_pdf()
    {
        $user = User::factory()->create(['perfil' => '2']);
        $this->actingAs($user);

        $ot = 'OT-CAST-456';

        $pData = [
            'type' => 'casting',
            'has_page2' => true,
            'page1' => [
                'ot_raw' => $ot,
                'proveedor' => 'SS Metal Foundry, S. de R. L. de C. V.',
                'fecha' => '2026-06-08',
                'fecha_entrega' => '2026-06-15',
                'folio' => 'MOD-2026-0111',
                'moldura' => 'ESPOLON 75 CL IS6180',
                'ot' => $ot,
                'observaciones' => 'Page 1 Obs',
                'filas' => [
                    [
                        'id_clase' => '1',
                        'tipo_modelo' => 'Molde',
                        'cantidad_fabricar' => 5,
                        'cant_fabricar' => 5,
                        'cantidad_consignacion' => 0,
                        'cant_consignacion' => 0,
                        'descripcion' => 'Molde Page 1',
                        'material' => 'Hierro Gris',
                        'codigo_modelo' => 'M-1234',
                        'peso_juego' => 10.5,
                        'peso_total' => 52.5,
                        'fecha_entrega' => '2026-06-15'
                    ]
                ]
            ],
            'page2' => [
                'ot_raw' => $ot,
                'proveedor' => 'Fundición Especializada, S. A. de C. V.',
                'fecha' => '2026-06-08',
                'fecha_entrega' => '2026-06-18',
                'folio' => 'MOD-2026-0111',
                'moldura' => 'ESPOLON 75 CL IS6180',
                'ot' => $ot,
                'observaciones' => 'Page 2 Obs',
                'filas' => [
                    [
                        'id_clase' => '2',
                        'tipo_modelo' => 'Obturador',
                        'cantidad_fabricar' => 2,
                        'cant_fabricar' => 2,
                        'cantidad_consignacion' => 1,
                        'cant_consignacion' => 1,
                        'descripcion' => 'Obturador Page 2',
                        'material' => 'Bronce',
                        'codigo_modelo' => 'O-5678',
                        'peso_juego' => 5.2,
                        'peso_total' => 10.4,
                        'fecha_entrega' => '2026-06-18'
                    ]
                ]
            ]
        ];

        $response = $this->postJson(route('almacen.fundicion.storePreOrden'), $pData);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'message' => 'Pre-Orden de Casting guardada correctamente.'
        ]);

        $resData = $response->json();
        $this->assertCount(1, $resData['pdfs']);

        $this->assertDatabaseHas('pre_ordenes_fundicion', [
            'ot' => $ot,
            'proveedor' => 'SS Metal Foundry, S. de R. L. de C. V.',
            'folio' => 'MOD-2026-0111'
        ]);

        $this->assertDatabaseHas('pre_ordenes_fundicion', [
            'ot' => $ot,
            'proveedor' => 'Fundición Especializada, S. A. de C. V.',
            'folio' => 'MOD-2026-0111'
        ]);
    }

    public function test_reproceso_cycle_reaches_multiple_iterations()
    {
        $user = User::factory()->create(['perfil' => '5']); // Almacen profile
        $this->actingAs($user);

        $otBase = 'OT-REPROCESO-123';

        // Create base history
        FundicionHistory::create([
            'ot' => $otBase,
            'status' => 'activa',
            'tiene_modelo' => 1,
            'pre_orden_sent' => 1,
            'pre_orden_email_sent' => 1,
            'calidad_revision_status' => 'rechazado',
            'ayudas_config' => ['Molde'],
            'almacen_archivos' => []
        ]);

        // Create base PreOrden
        PreOrdenFundicion::create([
            'ot' => $otBase,
            'folio' => 'MOD-2026-9999',
            'proveedor' => 'SS Metal Foundry',
            'fecha_creacion' => now(),
            'fecha_entrega' => now()->addDays(7),
            'moldura' => 'TEST MOLDURA',
            'observaciones' => 'Base Preorden',
            'filas' => [[
                'clase' => 'Molde',
                'tipo_modelo' => 'Molde',
                'clase_nombre' => 'Molde',
                'cantidad' => 1,
                'cant_fabricar' => 1,
                'cantidad_fabricar' => 1,
                'cantidad_consignacion' => 0,
                'cant_consignacion' => 0,
                'descripcion' => 'Molde Test',
                'material' => 'Hierro',
                'codigo_modelo' => 'M-9999',
                'peso_juego' => 10,
                'peso_total' => 10,
                'fecha_entrega' => '2026-06-15'
            ]],
            'user_id' => $user->id,
            'user_nombre' => $user->nombre,
            'version' => 1,
            'pdf_filename' => 'Pre-Orden_OT-REPROCESO-123.pdf'
        ]);

        // Simulate processing rejection on base OT to generate _R1
        $fileRechazo = \Illuminate\Http\UploadedFile::fake()->create('rechazo_molde.pdf', 100);
        $fileScar = \Illuminate\Http\UploadedFile::fake()->create('scar_molde.pdf', 100);

        $response = $this->postJson(route('almacen.fundicion.procesarRechazos'), [
            'ot' => $otBase,
            'fecha_recepcion' => '2026-06-08',
            'clases_rechazadas' => json_encode(['Molde']),
            'rechazo_molde' => $fileRechazo,
            'scar_molde' => $fileScar
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        // Assert base OT is marked as rechazos_procesados = true
        $this->assertDatabaseHas('fundicion_history', [
            'ot' => $otBase,
            'rechazos_procesados' => true
        ]);

        // Assert _R1 history is created
        $this->assertDatabaseHas('fundicion_history', [
            'ot' => $otBase . '_R1',
            'status' => 'activa',
            'tiene_modelo' => 0,
            'pre_orden_sent' => 0,
            'pre_orden_email_sent' => 0,
            'calidad_revision_status' => null
        ]);

        // Assert PreOrden for _R1 exists
        $this->assertDatabaseHas('pre_ordenes_fundicion', [
            'ot' => $otBase . '_R1',
            'folio' => 'MOD-2026-9999_R1'
        ]);

        // Simulate processing rejection on _R1 OT to generate _R2
        $response2 = $this->postJson(route('almacen.fundicion.procesarRechazos'), [
            'ot' => $otBase . '_R1',
            'fecha_recepcion' => '2026-06-09',
            'clases_rechazadas' => json_encode(['Molde']),
            'rechazo_molde' => $fileRechazo,
            'scar_molde' => $fileScar
        ]);

        $response2->assertStatus(200);
        $response2->assertJson(['success' => true]);

        // Assert _R1 is marked as rechazos_procesados = true
        $this->assertDatabaseHas('fundicion_history', [
            'ot' => $otBase . '_R1',
            'rechazos_procesados' => true
        ]);

        // Assert _R2 history is created
        $this->assertDatabaseHas('fundicion_history', [
            'ot' => $otBase . '_R2',
            'status' => 'activa'
        ]);

        // Assert PreOrden for _R2 exists
        $this->assertDatabaseHas('pre_ordenes_fundicion', [
            'ot' => $otBase . '_R2',
            'folio' => 'MOD-2026-9999_R1_R2'
        ]);
    }

    public function test_enviar_alerta_liberacion_approved_attaches_rejected_files_if_selected()
    {
        $ot = 'OT-TEST-ATT-123';
        $folderName = 'OT-TEST-ATT-123';
        
        $calidadDir = 'DOCUMENTACION_GIS/CALIDAD_FUNDICION/' . $folderName;

        // Create rejection file in Calidad
        Storage::disk('local')->put($calidadDir . '/ayudas_visuales/preordenes/documentos_rechazados/molde/SCAR_FOTO_Rechazado.png', 'dummy image content');

        $user = User::factory()->create(['perfil' => '4']); // Quality profile
        $this->actingAs($user);

        // Create FundicionHistory
        FundicionHistory::create([
            'ot' => $ot,
            'tiene_modelo' => true,
            'alert_sent_at' => now(),
            'calidad_revision_status' => 'espera'
        ]);

        // Create LiberacionModeloFundicion
        LiberacionModeloFundicion::create([
            'ot' => $ot,
            'tipo_modelo' => 'Molde',
            'decision' => 'aprobar',
            'estado' => 'espera',
            'pdf_filename' => 'F-CCL-LDM_MOLDE_APROBADO.pdf'
        ]);

        // Perform enviarAlertaLiberacion POST request
        $response = $this->postJson(route('almacen.fundicion.enviarAlertaLiberacion'), [
            'ot' => $ot,
            'decision' => 'aprobar',
            'tipo_modelo' => 'Molde',
            'fecha' => '2026-06-02',
            'destinatario' => 'test@example.com',
            // Selected files
            'dibujos_rechazados' => ['preordenes/documentos_rechazados/molde/SCAR_FOTO_Rechazado.png']
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        // Assert email was sent with correct attachments
        Mail::assertSent(LiberacionModeloMailable::class, function ($mail) {
            $attachments = $mail->emailAttachments;
            $hasRechazo = false;
            foreach ($attachments as $att) {
                if (str_ends_with($att['name'], 'SCAR_FOTO_Rechazado.png')) {
                    $hasRechazo = true;
                }
            }
            return $hasRechazo;
        });
    }
}

