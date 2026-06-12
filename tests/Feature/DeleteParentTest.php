<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\FundicionHistory;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;

class DeleteParentTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
    }

    public function test_delete_parent_fundicion()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $otNorm = 'OT 2102 - TEST';
        Storage::disk('local')->makeDirectory('DOCUMENTACION_GIS/DIBUJOS_FUNDICION/' . $otNorm);
        Storage::disk('local')->makeDirectory('DOCUMENTACION_GIS/ALMACEN_FUNDICION/' . $otNorm);

        // Create a history record
        $history = FundicionHistory::create([
            'ot' => $otNorm,
            'status' => 'activa',
            'tiene_modelo' => false,
            'pre_orden_sent' => false,
            'pre_orden_email_sent' => false,
        ]);

        $response = $this->postJson(route('fundicion.deleteParent'), [
            'ot' => $otNorm
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        // Verify physical directories
        $this->assertFalse(Storage::disk('local')->exists('DOCUMENTACION_GIS/DIBUJOS_FUNDICION/' . $otNorm));
        
        // The Almacen folder should be renamed with a timestamp extension (preceded by '_')
        $dirs = Storage::disk('local')->directories('DOCUMENTACION_GIS/ALMACEN_FUNDICION');
        $foundRename = false;
        foreach ($dirs as $dir) {
            if (str_contains($dir, $otNorm . '_')) {
                $foundRename = true;
                break;
            }
        }
        $this->assertTrue($foundRename, "Almacen folder should be renamed with timestamp");

        // Database record should be renamed and status set to 'inactiva'
        $updatedHistory = FundicionHistory::where('id', $history->id)->first();
        $this->assertEquals('inactiva', $updatedHistory->status);
        $this->assertNotEquals($otNorm, $updatedHistory->ot);
        $this->assertStringContainsString($otNorm, $updatedHistory->ot);
    }

    public function test_delete_parent_dibujos()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $ot = 'OT-DIBUJO-TEST';
        Storage::disk('local')->makeDirectory('DOCUMENTACION_GIS/DIBUJOS_MAQUINADOS/' . $ot);

        $response = $this->postJson(route('dibujos.deleteParent'), [
            'ot' => $ot
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $this->assertFalse(Storage::disk('local')->exists('DOCUMENTACION_GIS/DIBUJOS_MAQUINADOS/' . $ot));
    }

    public function test_delete_parent_manuales()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $proceso = 'Cepillado';
        Storage::disk('local')->makeDirectory('DOCUMENTACION_GIS/MANUALES_PROCESOS/' . $proceso);

        // Note: manuales routes deleteParent to deleteFolder
        $response = $this->postJson(route('manuales.deleteParent'), [
            'proceso' => $proceso
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $this->assertFalse(Storage::disk('local')->exists('DOCUMENTACION_GIS/MANUALES_PROCESOS/' . $proceso));
    }

    public function test_delete_parent_ayudas()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $clase = 'Molde';
        Storage::disk('local')->makeDirectory('DOCUMENTACION_GIS/AYUDAS_MAQUINADOS/' . $clase);

        $response = $this->postJson(route('ayudas.deleteParent'), [
            'proceso' => $clase // Note: helps routes expect 'proceso' parameter representing the class (parent)
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $this->assertFalse(Storage::disk('local')->exists('DOCUMENTACION_GIS/AYUDAS_MAQUINADOS/' . $clase));
    }

    public function test_delete_parent_ayudas_fundicion()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $clase = 'Obturador';
        Storage::disk('local')->makeDirectory('DOCUMENTACION_GIS/AYUDAS_FUNDICION/' . $clase);

        $response = $this->postJson(route('ayudas_fundicion.deleteParent'), [
            'proceso' => $clase
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $this->assertFalse(Storage::disk('local')->exists('DOCUMENTACION_GIS/AYUDAS_FUNDICION/' . $clase));
    }
}
