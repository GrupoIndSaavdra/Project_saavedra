<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\FundicionHistory;
use App\Models\LiberacionModeloFundicion;
use App\Models\ScarModelo;
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
}
