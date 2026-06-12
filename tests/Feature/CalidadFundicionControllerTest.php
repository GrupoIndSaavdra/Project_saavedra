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

class CalidadFundicionControllerTest extends TestCase
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
        $response = $this->getJson(route('calidad.fundicion.archivos', ['ot' => 'OT-TEST']));
        $response->assertStatus(401);
    }

public function test_non_permitted_profile_cannot_access_get_files()
    {
        $user = User::factory()->create(['perfil' => '6']); // Profile 6 not allowed
        $this->actingAs($user);

        $response = $this->getJson(route('calidad.fundicion.archivos', ['ot' => 'OT-TEST']));
        $response->assertStatus(403);
    }

public function test_get_files_returns_no_files_if_history_does_not_exist()
    {
        $user = User::factory()->create(['perfil' => '4']); // Quality profile
        $this->actingAs($user);

        $response = $this->getJson(route('calidad.fundicion.archivos', ['ot' => 'OT-NON-EXISTENT']));
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
        $response = $this->postJson(route('calidad.fundicion.enviarAlertaLiberacion'), [
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

        $response = $this->postJson(route('calidad.fundicion.enviarAlertaLiberacion'), [
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
        $response = $this->postJson(route('calidad.fundicion.enviarAlertaLiberacion'), [
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

public function test_serve_file_allows_case_insensitive_preorden_flow_and_multiple_statuses()
    {
        $user = User::factory()->create(['perfil' => '4']); // Calidad profile
        $this->actingAs($user);

        $ot = 'OT-PERM-TEST';
        $folderName = 'OT-PERM-TEST';

        $calidadDir = 'DOCUMENTACION_GIS/CALIDAD_FUNDICION/' . $folderName;
        Storage::disk('local')->put($calidadDir . '/Documentos_Aprobados/obturador/F-CCL-LDM_OBTURADOR_OT-PERM-TEST_APROBADO.pdf', 'pdf contents');

        $history = FundicionHistory::create([
            'ot' => $ot,
            'tiene_modelo' => true,
            'alert_sent_at' => now(),
            'calidad_revision_status' => 'calidad_aprobado'
        ]);

        $response = $this->get(route('calidad.fundicion.serve', [
            'ot' => $ot,
            'archivo' => 'Documentos_Aprobados/obturador/F-CCL-LDM_OBTURADOR_OT-PERM-TEST_APROBADO.pdf',
            'tipo' => 'otro',
            'origin' => 'aprobado'
        ]));

        $response->assertStatus(200);
    }

public function test_user_can_delete_own_file_before_alert()
    {
        $user = User::factory()->create(['perfil' => '4']); // Calidad
        $this->actingAs($user);

        $ot = 'OT-DEL-TEST-1';
        $folderName = 'OT-DEL-TEST-1';
        $calidadDir = 'DOCUMENTACION_GIS/CALIDAD_FUNDICION/' . $folderName;
        Storage::disk('local')->put($calidadDir . '/ayudas_visuales/preordenes/evidencias/SCAR_TEST.pdf', 'dummy content');

        FundicionHistory::create([
            'ot' => $ot,
            'tiene_modelo' => true,
            'alert_sent_at' => now(),
            'calidad_revision_status' => 'pendiente'
        ]);

        $response = $this->postJson(route('calidad.fundicion.deleteFile'), [
            'ot' => $ot,
            'archivo' => 'preordenes/evidencias/SCAR_TEST.pdf',
            'tipo' => 'otro'
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
        $this->assertFalse(Storage::disk('local')->exists($calidadDir . '/ayudas_visuales/preordenes/evidencias/SCAR_TEST.pdf'));
    }

public function test_user_cannot_delete_file_after_alert_is_sent()
    {
        $user = User::factory()->create(['perfil' => '4']); // Calidad
        $this->actingAs($user);

        $ot = 'OT-DEL-TEST-2';
        $folderName = 'OT-DEL-TEST-2';
        $calidadDir = 'DOCUMENTACION_GIS/CALIDAD_FUNDICION/' . $folderName;
        Storage::disk('local')->put($calidadDir . '/ayudas_visuales/preordenes/evidencias/SCAR_TEST2.pdf', 'dummy content');

        FundicionHistory::create([
            'ot' => $ot,
            'tiene_modelo' => true,
            'alert_sent_at' => now(),
            'calidad_revision_status' => 'calidad_aprobado'
        ]);

        $response = $this->postJson(route('calidad.fundicion.deleteFile'), [
            'ot' => $ot,
            'archivo' => 'preordenes/evidencias/SCAR_TEST2.pdf',
            'tipo' => 'otro'
        ]);

        $response->assertStatus(403);
        $response->assertJsonFragment(['error' => 'No se puede eliminar. La alerta de Calidad ya ha sido enviada.']);
        $this->assertTrue(Storage::disk('local')->exists($calidadDir . '/ayudas_visuales/preordenes/evidencias/SCAR_TEST2.pdf'));
    }

public function test_user_cannot_delete_file_from_another_department()
    {
        $user = User::factory()->create(['perfil' => '4']); // Calidad trying to delete Almacen file
        $this->actingAs($user);

        $ot = 'OT-DEL-TEST-3';
        $folderName = 'OT-DEL-TEST-3';
        $almacenDir = 'DOCUMENTACION_GIS/ALMACEN_FUNDICION/' . $folderName;
        Storage::disk('local')->put($almacenDir . '/ayudas_visuales/preordenes/ConfirmacionModelo.pdf', 'dummy content');

        FundicionHistory::create([
            'ot' => $ot,
            'tiene_modelo' => true,
            'alert_sent_at' => now(),
            'pre_orden_sent' => true,
            'pre_orden_email_sent' => true,
            'calidad_revision_status' => 'pendiente'
        ]);

        $response = $this->postJson(route('calidad.fundicion.deleteFile'), [
            'ot' => $ot,
            'archivo' => 'preordenes/ConfirmacionModelo.pdf',
            'tipo' => 'otro',
            'origin' => 'almacen'
        ]);

        $response->assertStatus(403);
        $response->assertJsonFragment(['error' => 'Acceso denegado. Solo Almacén puede eliminar este documento.']);
        $this->assertTrue(Storage::disk('local')->exists($almacenDir . '/ayudas_visuales/preordenes/ConfirmacionModelo.pdf'));
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

        $response = $this->postJson(route('calidad.fundicion.deleteFile'), [
            'ot' => $ot,
            'archivo' => 'preordenes/evidencias/evidencia_adicional_0_OT-DEL-TEST-4.png',
            'tipo' => 'otro',
            'origin' => 'calidad'
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
        $this->assertFalse(Storage::disk('local')->exists($calidadDir . '/ayudas_visuales/preordenes/evidencias/evidencia_adicional_0_OT-DEL-TEST-4.png'));
    }

    public function test_submit_liberacion_keeps_status_pendiente_when_classes_are_missing()
    {
        $user = User::factory()->create(['perfil' => '4']); // Calidad
        $this->actingAs($user);

        $ot = 'OT-DRAFT-TEST-1';
        
        // Setup history with 2 required classes in ayudas_config
        $history = FundicionHistory::create([
            'ot' => $ot,
            'tiene_modelo' => true,
            'ayudas_config' => ['Fondo', 'Molde'],
            'calidad_revision_status' => 'pendiente'
        ]);

        // Submit first class (Fondo)
        $response1 = $this->postJson(route('calidad.fundicion.submitLiberacion'), [
            'ot' => $ot,
            'accion' => 'guardar',
            'decision' => 'aprobar',
            'tipo_modelo' => 'Fondo',
            'fondo' => [
                'item1' => ['col1' => 12.345]
            ]
        ]);

        $response1->assertStatus(200);
        $response1->assertJson(['success' => true]);
        
        $history->refresh();
        // Since Molde is still missing, status must still be 'pendiente'
        $this->assertEquals('pendiente', $history->calidad_revision_status);

        // Submit second class (Molde)
        $response2 = $this->postJson(route('calidad.fundicion.submitLiberacion'), [
            'ot' => $ot,
            'accion' => 'guardar',
            'decision' => 'aprobar',
            'tipo_modelo' => 'Molde',
            'modelo' => [
                'item1' => ['col1' => 54.321]
            ]
        ]);

        $response2->assertStatus(200);
        $response2->assertJson(['success' => true]);

        $history->refresh();
        // Now that both are filled, status must become 'aprobado'
        $this->assertEquals('aprobado', $history->calidad_revision_status);
    }

    public function test_enviar_alerta_liberacion_filters_attachments_by_class_decision()
    {
        $user = User::factory()->create(['perfil' => '4']); // Calidad
        $this->actingAs($user);

        $ot = 'OT-FILTER-TEST';
        $folderName = 'OT-FILTER-TEST';
        $almacenDir = 'DOCUMENTACION_GIS/ALMACEN_FUNDICION/' . $folderName;
        $calidadDir = 'DOCUMENTACION_GIS/CALIDAD_FUNDICION/' . $folderName;

        // Create mock files
        // 1. Approved pre-order of model (starts with Pre-Orden_Fundicion- and no Casting)
        Storage::disk('local')->put($almacenDir . '/ayudas_visuales/preordenes/Pre-Orden_Fundicion-Model.pdf', 'preorder content');
        // 2. Fondo drawing (matches Fondo class)
        Storage::disk('local')->put($almacenDir . '/fondo/Dibujos/fondo_drawing.pdf', 'fondo drawing content');
        // 3. Molde drawing (matches Molde class)
        Storage::disk('local')->put($almacenDir . '/molde/Dibujos/molde_drawing.pdf', 'molde drawing content');
        // 4. Rejected Molde file
        Storage::disk('local')->put($calidadDir . '/ayudas_visuales/preordenes/documentos_rechazados/molde/molde_rechazo.png', 'molde rejection content');

        FundicionHistory::create([
            'ot' => $ot,
            'tiene_modelo' => true,
            'alert_sent_at' => now(),
            'calidad_revision_status' => 'aprobado'
        ]);

        LiberacionModeloFundicion::create([
            'ot' => $ot,
            'tipo_modelo' => 'Fondo',
            'decision' => 'aprobar',
            'estado' => 'aprobado'
        ]);

        LiberacionModeloFundicion::create([
            'ot' => $ot,
            'tipo_modelo' => 'Molde',
            'decision' => 'rechazar',
            'estado' => 'rechazado'
        ]);

        // TEST 1: Decision = 'aprobar', class = 'Fondo'.
        // Should only attach drawings of Fondo class. Should not attach Molde drawings/rejections or pre-order.
        $response1 = $this->postJson(route('calidad.fundicion.enviarAlertaLiberacion'), [
            'ot' => $ot,
            'decision' => 'aprobar',
            'tipo_modelo' => 'Fondo',
            'fecha' => '2026-06-02',
            'destinatario' => 'test@example.com',
            'dibujos' => ['fondo/Dibujos/fondo_drawing.pdf', 'molde/Dibujos/molde_drawing.pdf'],
            'dibujos_aprobados' => ['preordenes/Pre-Orden_Fundicion-Model.pdf'],
            'dibujos_rechazados' => ['preordenes/documentos_rechazados/molde/molde_rechazo.png']
        ]);
        $response1->assertStatus(200);

        Mail::assertSent(LiberacionModeloMailable::class, function ($mail) {
            if ($mail->estado !== 'aprobado') return false;
            $attachments = $mail->emailAttachments;
            $hasFondo = collect($attachments)->contains(fn($a) => str_contains($a['name'], 'fondo_drawing.pdf'));
            $hasMolde = collect($attachments)->contains(fn($a) => str_contains($a['name'], 'molde_drawing.pdf'));
            $hasPreorder = collect($attachments)->contains(fn($a) => str_contains($a['name'], 'Pre-Orden_Fundicion-Model.pdf'));
            return $hasFondo && !$hasMolde && !$hasPreorder;
        });

        // TEST 2: Decision = 'rechazar', class = 'Molde'.
        // Should only attach pre-order of model and rejected files of Molde class.
        $response2 = $this->postJson(route('calidad.fundicion.enviarAlertaLiberacion'), [
            'ot' => $ot,
            'decision' => 'rechazar',
            'tipo_modelo' => 'Molde',
            'fecha' => '2026-06-02',
            'destinatario' => 'test@example.com',
            'dibujos' => ['fondo/Dibujos/fondo_drawing.pdf'],
            'dibujos_aprobados' => ['preordenes/Pre-Orden_Fundicion-Model.pdf'],
            'dibujos_rechazados' => ['preordenes/documentos_rechazados/molde/molde_rechazo.png']
        ]);
        $response2->assertStatus(200);

        Mail::assertSent(LiberacionModeloMailable::class, function ($mail) {
            if ($mail->estado !== 'rechazado') return false;
            $attachments = $mail->emailAttachments;
            $hasFondo = collect($attachments)->contains(fn($a) => str_contains($a['name'], 'fondo_drawing.pdf'));
            $hasRechazo = collect($attachments)->contains(fn($a) => str_contains($a['name'], 'molde_rechazo.png'));
            $hasPreorder = collect($attachments)->contains(fn($a) => str_contains($a['name'], 'Pre-Orden_Fundicion-Model.pdf'));
            return !$hasFondo && $hasRechazo && $hasPreorder;
        });
    }
}
