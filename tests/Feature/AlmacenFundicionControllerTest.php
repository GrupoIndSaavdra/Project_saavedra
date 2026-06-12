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
        $user = User::factory()->create(['perfil' => '6']); // Profile 6 not allowed
        $this->actingAs($user);

        $response = $this->getJson(route('almacen.fundicion.archivos', ['ot' => 'OT-TEST']));
        $response->assertStatus(403);
    }

public function test_get_files_returns_no_files_if_history_does_not_exist()
    {
        $user = User::factory()->create(['perfil' => '5']); // Almacen profile
        $this->actingAs($user);

        $response = $this->getJson(route('almacen.fundicion.archivos', ['ot' => 'OT-NON-EXISTENT']));
        $response->assertJson([
            'existe' => false,
            'archivos' => []
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

public function test_send_email_pre_orden_succeeds_without_archivos_adicionales()
    {
        $user = User::factory()->create(['perfil' => '2']); // Warehouse profile
        $this->actingAs($user);

        $ot = 'OT-MODEL-789';
        $folderName = 'OT-MODEL-789';

        // Create FundicionHistory
        FundicionHistory::create([
            'ot' => $ot,
            'tiene_modelo' => false,
            'alert_sent_at' => now(),
            'calidad_revision_status' => 'espera'
        ]);

        // Create PreOrdenFundicion
        PreOrdenFundicion::create([
            'ot' => $ot,
            'proveedor' => 'Provider B',
            'folio' => 'MOD-2026-0300',
            'pdf_filename' => 'Pre-Orden_Fundicion-MOD-2026-0300_OT_MODEL_789.pdf',
            'fecha_creacion' => now(),
            'filas' => []
        ]);

        // Setup mock pre-order PDF under the new path
        $newPreOrdenPath = 'DOCUMENTACION_GIS/ALMACEN_FUNDICION/' . $folderName . '/Documentos_Aprobados/Preorden_Modelo/Pre-Orden_Fundicion-MOD-2026-0300_OT_MODEL_789.pdf';
        Storage::disk('local')->put($newPreOrdenPath, 'dummy pre-order pdf content');

        // Post request without 'archivos_adicionales'
        $response = $this->postJson(route('almacen.fundicion.sendEmailPreOrden'), [
            'ot' => $ot,
            'tipo' => 'modelo',
            'destinatario' => 'test@example.com',
            'fecha_entrega' => '2026-06-20'
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true
        ]);

        $this->assertDatabaseHas('fundicion_history', [
            'ot' => $ot,
            'pre_orden_email_sent' => true
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

public function test_get_files_returns_correct_structured_new_folders()
    {
        $user = User::factory()->create(['perfil' => '5']); // Quality profile
        $this->actingAs($user);

        $ot = 'OT-FOLDER-TEST';
        $folderName = 'OT-FOLDER-TEST';

        $almacenDir = 'DOCUMENTACION_GIS/ALMACEN_FUNDICION/' . $folderName;
        $calidadDir = 'DOCUMENTACION_GIS/CALIDAD_FUNDICION/' . $folderName;

        // Save files to new folders
        Storage::disk('local')->put($almacenDir . '/Documentos_Aprobados/Preorden_Modelo/Pre-Orden_OT-FOLDER-TEST.pdf', 'dummy content');
        Storage::disk('local')->put($almacenDir . '/Documentos_Rechazados/SCAR/molde/SCAR_Molde.pdf', 'dummy content');

        // Create FundicionHistory
        FundicionHistory::create([
            'ot' => $ot,
            'tiene_modelo' => true,
            'alert_sent_at' => now(),
            'calidad_revision_status' => 'espera'
        ]);

        $response = $this->getJson(route('almacen.fundicion.archivos', ['ot' => $ot]));
        $response->assertStatus(200);

        $response->assertJsonFragment([
            'nombre' => 'Documentos_Aprobados/Preorden_Modelo/Pre-Orden_OT-FOLDER-TEST.pdf',
            'tipo' => 'otro',
            'origin' => 'aprobado'
        ]);

        $response->assertJsonFragment([
            'nombre' => 'Documentos_Rechazados/SCAR/molde/SCAR_Molde.pdf',
            'tipo' => 'otro',
            'origin' => 'rechazado'
        ]);
    }

public function test_serve_file_allows_case_insensitive_preorden_flow_and_multiple_statuses()
    {
        $user = User::factory()->create(['perfil' => '5']); // Almacen profile
        $this->actingAs($user);

        $ot = 'OT-PERM-TEST';
        $folderName = 'OT-PERM-TEST';

        $almacenDir = 'DOCUMENTACION_GIS/ALMACEN_FUNDICION/' . $folderName;
        Storage::disk('local')->put($almacenDir . '/Documentos_Aprobados/obturador/F-CCL-LDM_OBTURADOR_OT-PERM-TEST_APROBADO.pdf', 'pdf contents');

        $history = FundicionHistory::create([
            'ot' => $ot,
            'tiene_modelo' => true,
            'alert_sent_at' => now(),
            'calidad_revision_status' => 'calidad_aprobado'
        ]);

        $response = $this->get(route('almacen.fundicion.serve', [
            'ot' => $ot,
            'archivo' => 'Documentos_Aprobados/obturador/F-CCL-LDM_OBTURADOR_OT-PERM-TEST_APROBADO.pdf',
            'tipo' => 'otro',
            'origin' => 'aprobado'
        ]));

        $response->assertStatus(200);
    }

public function test_mixed_alert_does_not_shadow_target_reg()
    {
        $user = User::factory()->create(['perfil' => '5']); // Almacen profile
        $this->actingAs($user);

        $ot = 'OT-MIXED-SHADOW-TEST';
        
        // Create base history with status 'activa' and calidad_revision_status 'calidad_mixto'
        FundicionHistory::create([
            'ot' => $ot,
            'tiene_modelo' => true,
            'alert_sent_at' => now(),
            'calidad_revision_status' => 'calidad_mixto',
            'status' => 'activa',
            'rechazos_procesados' => true
        ]);

        // Create reprocess history (OT_R1)
        FundicionHistory::create([
            'ot' => $ot . '_R1',
            'tiene_modelo' => false,
            'alert_sent_at' => now(),
            'calidad_revision_status' => null,
            'status' => 'activa'
        ]);

        // Create approved liberacion record for the base OT
        LiberacionModeloFundicion::create([
            'ot' => $ot,
            'tipo_modelo' => 'Fondo',
            'decision' => 'aprobar',
            'estado' => 'aprobado'
        ]);

        // Call the index view
        $response = $this->get(route('almacen.fundicion.index'));
        $response->assertStatus(200);

        // Assert both OTs are rendered in the response HTML.
        $response->assertSee($ot);
        $response->assertSee($ot . '_R1');
    }

public function test_mixed_alert_filters_files_by_active_class()
    {
        $user = User::factory()->create(['perfil' => '5']); // Quality profile
        $this->actingAs($user);

        $ot = 'OT-MIXED-FILES-TEST';
        $otR1 = 'OT-MIXED-FILES-TEST_R1';

        // 1. Create history records
        FundicionHistory::create([
            'ot' => $ot,
            'tiene_modelo' => true,
            'status' => 'activa'
        ]);

        FundicionHistory::create([
            'ot' => $otR1,
            'tiene_modelo' => true,
            'status' => 'activa'
        ]);

        // 2. Create model pre-order for _R1, which only has 'Obturador' active
        PreOrdenFundicion::create([
            'ot' => $otR1,
            'folio' => 'FOLIO-123',
            'proveedor' => 'PROV-123',
            'pdf_filename' => 'MOD-Pre-Orden_OT-MIXED-FILES-TEST_R1.pdf',
            'filas' => json_encode([
                ['clase' => 'Obturador', 'modelo_dibujo' => 'Obt-123']
            ]),
            'fecha_creacion' => now(),
            'fecha_entrega' => now(),
            'moldura' => 'Mold-123'
        ]);

        // 3. Setup files on disk for both OTs
        $baseAlmacenDir = 'DOCUMENTACION_GIS/ALMACEN_FUNDICION/' . $ot;
        $r1AlmacenDir = 'DOCUMENTACION_GIS/ALMACEN_FUNDICION/' . $otR1;

        // Fondo drawing
        Storage::disk('local')->put($baseAlmacenDir . '/Fondo - Dibujo 1.pdf', 'dummy content');
        // Obturador drawing
        Storage::disk('local')->put($baseAlmacenDir . '/Obturador - Dibujo 2.pdf', 'dummy content');
        // Fondo approved doc
        Storage::disk('local')->put($baseAlmacenDir . '/Documentos_Aprobados/Fondo_Aprobado.pdf', 'dummy content');
        // Obturador approved doc
        Storage::disk('local')->put($r1AlmacenDir . '/Documentos_Aprobados/Obturador_Aprobado.pdf', 'dummy content');

        // Request files for _R1
        $response = $this->getJson(route('almacen.fundicion.archivos', ['ot' => $otR1]));
        $response->assertStatus(200);

        $files = $response->json('archivos');

        // Verify that Obturador drawing and approved doc are present
        $fileNames = array_column($files, 'nombre');

        $this->assertTrue(in_array('Obturador - Dibujo 2.pdf', $fileNames));
        $this->assertTrue(in_array('Documentos_Aprobados/Obturador_Aprobado.pdf', $fileNames));

        // Verify that Fondo drawing and approved doc are NOT present
        $this->assertFalse(in_array('Fondo - Dibujo 1.pdf', $fileNames));
        $this->assertFalse(in_array('Documentos_Aprobados/Fondo_Aprobado.pdf', $fileNames));
    }

public function test_user_can_delete_own_file_before_alert()
    {
        $user = User::factory()->create(['perfil' => '5']); // Almacen
        $this->actingAs($user);

        $ot = 'OT-DEL-TEST-1';
        $folderName = 'OT-DEL-TEST-1';
        $almacenDir = 'DOCUMENTACION_GIS/ALMACEN_FUNDICION/' . $folderName;
        Storage::disk('local')->put($almacenDir . '/ayudas_visuales/preordenes/ConfirmacionModelo_Test.pdf', 'dummy content');

        FundicionHistory::create([
            'ot' => $ot,
            'tiene_modelo' => true,
            'alert_sent_at' => now(),
            'pre_orden_email_sent' => false,
            'pre_orden_sent' => false
        ]);

        $response = $this->postJson(route('almacen.fundicion.deleteFile'), [
            'ot' => $ot,
            'archivo' => 'preordenes/ConfirmacionModelo_Test.pdf',
            'tipo' => 'otro'
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
        $this->assertFalse(Storage::disk('local')->exists($almacenDir . '/ayudas_visuales/preordenes/ConfirmacionModelo_Test.pdf'));
    }

public function test_user_cannot_delete_file_after_alert_is_sent()
    {
        $user = User::factory()->create(['perfil' => '5']); // Almacen
        $this->actingAs($user);

        $ot = 'OT-DEL-TEST-2';
        $folderName = 'OT-DEL-TEST-2';
        $almacenDir = 'DOCUMENTACION_GIS/ALMACEN_FUNDICION/' . $folderName;
        Storage::disk('local')->put($almacenDir . '/ayudas_visuales/preordenes/ConfirmacionModelo_Test2.pdf', 'dummy content');

        FundicionHistory::create([
            'ot' => $ot,
            'tiene_modelo' => true,
            'alert_sent_at' => now(),
            'pre_orden_email_sent' => true,
            'pre_orden_sent' => true
        ]);

        $response = $this->postJson(route('almacen.fundicion.deleteFile'), [
            'ot' => $ot,
            'archivo' => 'preordenes/ConfirmacionModelo_Test2.pdf',
            'tipo' => 'otro'
        ]);

        $response->assertStatus(403);
        $response->assertJsonFragment(['error' => 'No se puede eliminar. La alerta de Almacén ya ha sido enviada.']);
        $this->assertTrue(Storage::disk('local')->exists($almacenDir . '/ayudas_visuales/preordenes/ConfirmacionModelo_Test2.pdf'));
    }

public function test_user_cannot_delete_file_from_another_department()
    {
        $user = User::factory()->create(['perfil' => '5']); // Almacen trying to delete Calidad file
        $this->actingAs($user);

        $ot = 'OT-DEL-TEST-3';
        $folderName = 'OT-DEL-TEST-3';
        $calidadDir = 'DOCUMENTACION_GIS/CALIDAD_FUNDICION/' . $folderName;
        Storage::disk('local')->put($calidadDir . '/ayudas_visuales/preordenes/evidencias/evidencia_adicional_0_OT-DEL-TEST-3.png', 'dummy image content');

        FundicionHistory::create([
            'ot' => $ot,
            'tiene_modelo' => true,
            'alert_sent_at' => now(),
            'calidad_revision_status' => 'pendiente'
        ]);

        $response = $this->postJson(route('almacen.fundicion.deleteFile'), [
            'ot' => $ot,
            'archivo' => 'preordenes/evidencias/evidencia_adicional_0_OT-DEL-TEST-3.png',
            'tipo' => 'otro',
            'origin' => 'calidad'
        ]);

        $response->assertStatus(403);
        $response->assertJsonFragment(['error' => 'Acceso denegado. Solo Calidad puede eliminar este documento.']);
        $this->assertTrue(Storage::disk('local')->exists($calidadDir . '/ayudas_visuales/preordenes/evidencias/evidencia_adicional_0_OT-DEL-TEST-3.png'));
    }

public function test_admin_can_delete_any_file_before_alert()
    {
        $user = User::factory()->create(['perfil' => '1']); // Admin
        $this->actingAs($user);

        $ot = 'OT-DEL-TEST-4';
        $folderName = 'OT-DEL-TEST-4';
        $calidadDir = 'DOCUMENTACION_GIS/CALIDAD_FUNDICION/' . $folderName;
        Storage::disk('local')->put($calidadDir . '/ayudas_visuales/preordenes/evidencias/evidencia_adicional_0_OT-DEL-TEST-4.png', 'dummy image content');

        FundicionHistory::create([
            'ot' => $ot,
            'tiene_modelo' => true,
            'alert_sent_at' => now(),
            'calidad_revision_status' => 'pendiente'
        ]);

        $response = $this->postJson(route('almacen.fundicion.deleteFile'), [
            'ot' => $ot,
            'archivo' => 'preordenes/evidencias/evidencia_adicional_0_OT-DEL-TEST-4.png',
            'tipo' => 'otro',
            'origin' => 'calidad'
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
        $this->assertFalse(Storage::disk('local')->exists($calidadDir . '/ayudas_visuales/preordenes/evidencias/evidencia_adicional_0_OT-DEL-TEST-4.png'));
    }

public function test_reprocess_control_card_binds_to_active_cycle_record()
    {
        $user = User::factory()->create(['perfil' => '5']); // Almacen
        $this->actingAs($user);

        $ot = 'OT-REPROCESS-CARD-TEST';

        // Base history: already completed/email sent, rechazos_procesados = true
        $baseHistory = FundicionHistory::create([
            'ot' => $ot,
            'tiene_modelo' => true,
            'pre_orden_sent' => true,
            'pre_orden_email_sent' => true,
            'alert_sent_at' => now(),
            'status' => 'activa',
            'almacen_archivos' => ['dummy_drawing.pdf']
        ]);
        $baseHistory->rechazos_procesados = true;
        $baseHistory->save();

        // Reprocess cycle _R1: currently waiting for pre-order, not yet confirmed
        FundicionHistory::create([
            'ot' => $ot . '_R1',
            'tiene_modelo' => false,
            'pre_orden_sent' => false,
            'pre_orden_email_sent' => false,
            'status' => 'activa',
            'alert_sent_at' => now()
        ]);

        $response = $this->get(route('almacen.fundicion.index'));
        $response->assertStatus(200);

        // Assert the card is not disabled and prompts for generating pre-order in reprocess
        $response->assertSee('id="control-modelo-' . md5($ot) . '"', false);
        $response->assertDontSee('id="control-modelo-' . md5($ot) . '" style="opacity: 0.5; pointer-events: none;"', false);
        $response->assertSee('OT en re-proceso por rechazo de Calidad');

        // Now update reprocess model status so it should block
        $reprocessReg = FundicionHistory::where('ot', $ot . '_R1')->first();
        $reprocessReg->tiene_modelo = true;
        $reprocessReg->save();

        $response2 = $this->get(route('almacen.fundicion.index'));
        $response2->assertStatus(200);
        $content = $response2->getContent();
        $hash = md5($ot);
        $this->assertMatchesRegularExpression(
            '/<div class="lib-calidad-card"\s+id="control-modelo-' . $hash . '"[^>]*?style="opacity:\s*0\.5;\s*pointer-events:\s*none;"/i',
            $content
        );
    }

    public function test_reproceso_copies_only_rejected_class_files_and_model_preorder()
    {
        $user = User::factory()->create(['perfil' => '5']); // Almacen
        $this->actingAs($user);

        $otBase = 'OT-REPROCESO-COPY-TEST';
        $folderNameOriginal = 'OT-REPROCESO-COPY-TEST';
        $folderNameNew = 'OT-REPROCESO-COPY-TEST_R1';

        // Base directories in fake storage
        $originalBaseDir = 'DOCUMENTACION_GIS/ALMACEN_FUNDICION/' . $folderNameOriginal;
        $newBaseDir = 'DOCUMENTACION_GIS/ALMACEN_FUNDICION/' . $folderNameNew;

        // Create base files in fake storage
        Storage::disk('local')->put($originalBaseDir . '/bombillo/Dibujos/Bombillo_dibujo.pdf', 'bombillo drawing pdf');
        Storage::disk('local')->put($originalBaseDir . '/fondo/Dibujos/Fondo_dibujo.pdf', 'fondo drawing pdf');
        Storage::disk('local')->put($originalBaseDir . '/bombillo/Ayudas_Visuales/Bombillo_ayuda.pdf', 'bombillo visual aid pdf');
        Storage::disk('local')->put($originalBaseDir . '/fondo/Ayudas_Visuales/Fondo_ayuda.pdf', 'fondo visual aid pdf');
        Storage::disk('local')->put($originalBaseDir . '/Documentos_Aprobados/preordenes/Pre-Orden_Fundicion-MOD-1234_OT_2102.pdf', 'pre-orden fundicion model pdf');
        Storage::disk('local')->put($originalBaseDir . '/Documentos_Aprobados/preordenes/Pre-Orden_Casting-MOD-1234_OT_2102.pdf', 'pre-orden casting pdf');
        Storage::disk('local')->put($originalBaseDir . '/Documentos_Aprobados/FDLDM/F-CCL-LDM_FONDO_APROBADO.pdf', 'approved ldm pdf');
        Storage::disk('local')->put($originalBaseDir . '/Documentos_Rechazados/FDRDM/Rechazo_BOMBILLO_OT-REPROCESO-COPY-TEST.pdf', 'rejected bombillo pdf');
        Storage::disk('local')->put($originalBaseDir . '/Documentos_Rechazados/FDRDM/Rechazo_FONDO_OT-REPROCESO-COPY-TEST.pdf', 'rejected fondo pdf');

        // Create base database records
        FundicionHistory::create([
            'ot' => $otBase,
            'status' => 'activa',
            'tiene_modelo' => 1,
            'pre_orden_sent' => 1,
            'pre_orden_email_sent' => 1,
            'calidad_revision_status' => 'calidad_rechazado',
            'ayudas_config' => ['bombillo', 'fondo'],
            'almacen_archivos' => []
        ]);

        PreOrdenFundicion::create([
            'ot' => $otBase,
            'folio' => 'MOD-2026-1111',
            'proveedor' => 'SS Metal Foundry',
            'fecha_creacion' => now(),
            'fecha_entrega' => now()->addDays(7),
            'moldura' => 'TEST MOLDURA',
            'observaciones' => 'Base Preorden',
            'filas' => [
                [
                    'clase' => 'bombillo',
                    'tipo_modelo' => 'Suelto',
                    'clase_nombre' => 'bombillo',
                    'cantidad' => 1,
                    'cant_fabricar' => 1,
                    'cantidad_consignacion' => 0,
                    'cant_consignacion' => 0,
                    'descripcion' => 'Bombillo Test',
                    'material' => 'Hierro',
                    'codigo_modelo' => 'B-1111',
                    'peso_juego' => 10,
                    'peso_total' => 10,
                    'fecha_entrega' => '2026-06-15'
                ],
                [
                    'clase' => 'fondo',
                    'tipo_modelo' => 'Suelto',
                    'clase_nombre' => 'fondo',
                    'cantidad' => 1,
                    'cant_fabricar' => 1,
                    'cantidad_consignacion' => 0,
                    'cant_consignacion' => 0,
                    'descripcion' => 'Fondo Test',
                    'material' => 'Hierro',
                    'codigo_modelo' => 'F-1111',
                    'peso_juego' => 10,
                    'peso_total' => 10,
                    'fecha_entrega' => '2026-06-15'
                ]
            ],
            'user_id' => $user->id,
            'user_nombre' => $user->nombre,
            'version' => 1,
            'pdf_filename' => 'Pre-Orden_Fundicion-MOD-1234_OT_2102.pdf'
        ]);

        // POST request to procesarRechazos, only rejecting 'bombillo'
        $response = $this->postJson(route('almacen.fundicion.procesarRechazos'), [
            'ot' => $otBase,
            'fecha_recepcion' => '2026-06-12',
            'clases_rechazadas' => json_encode(['bombillo'])
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        // Check file copies in the new OT (_R1) folder
        // 1. Bombillo drawings and visual aids SHOULD be copied (as they belong to the rejected class 'bombillo')
        $this->assertTrue(Storage::disk('local')->exists($newBaseDir . '/bombillo/Dibujos/Bombillo_dibujo.pdf'));
        $this->assertTrue(Storage::disk('local')->exists($newBaseDir . '/bombillo/Ayudas_Visuales/Bombillo_ayuda.pdf'));

        // 2. Fondo drawings and visual aids SHOULD NOT be copied (class 'fondo' was not rejected)
        $this->assertFalse(Storage::disk('local')->exists($newBaseDir . '/fondo/Dibujos/Fondo_dibujo.pdf'));
        $this->assertFalse(Storage::disk('local')->exists($newBaseDir . '/fondo/Ayudas_Visuales/Fondo_ayuda.pdf'));

        // 3. Rejected files matching class 'bombillo' SHOULD be copied
        $this->assertTrue(Storage::disk('local')->exists($newBaseDir . '/Documentos_Rechazados/FDRDM/Rechazo_BOMBILLO_OT-REPROCESO-COPY-TEST.pdf'));

        // 4. Rejected files matching class 'fondo' SHOULD NOT be copied
        $this->assertFalse(Storage::disk('local')->exists($newBaseDir . '/Documentos_Rechazados/FDRDM/Rechazo_FONDO_OT-REPROCESO-COPY-TEST.pdf'));

        // 5. Approved files: first pre-order (starts with Pre-Orden_Fundicion-) SHOULD be copied
        $this->assertTrue(Storage::disk('local')->exists($newBaseDir . '/Documentos_Aprobados/preordenes/Pre-Orden_Fundicion-MOD-1234_OT_2102.pdf'));

        // 6. Approved files: casting pre-order (starts with Pre-Orden_Casting-) and other files (like FDLDM approval) SHOULD NOT be copied
        $this->assertFalse(Storage::disk('local')->exists($newBaseDir . '/Documentos_Aprobados/preordenes/Pre-Orden_Casting-MOD-1234_OT_2102.pdf'));
        $this->assertFalse(Storage::disk('local')->exists($newBaseDir . '/Documentos_Aprobados/FDLDM/F-CCL-LDM_FONDO_APROBADO.pdf'));
    }
}
