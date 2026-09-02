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
        Storage::disk('local')->put('DOCUMENTACION_GIS/DIBUJOS_FUNDICION/' . $otNorm . '/dibujo1.pdf', 'content');
        
        Storage::disk('local')->makeDirectory('DOCUMENTACION_GIS/ALMACEN_FUNDICION/' . $otNorm);
        Storage::disk('local')->put('DOCUMENTACION_GIS/ALMACEN_FUNDICION/' . $otNorm . '/archivo1.pdf', 'content');

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

        // Verify physical directories are removed from active locations
        $this->assertFalse(Storage::disk('local')->exists('DOCUMENTACION_GIS/DIBUJOS_FUNDICION/' . $otNorm));
        $this->assertFalse(Storage::disk('local')->exists('DOCUMENTACION_GIS/ALMACEN_FUNDICION/' . $otNorm));
        
        // The folders should be moved to the respective INACTIVAS folder
        $inactiveDibujosDirs = Storage::disk('local')->directories('DOCUMENTACION_GIS/DIBUJOS_FUNDICION/INACTIVAS');
        $foundDibujosArchive = false;
        $dibujosArchivePath = '';
        foreach ($inactiveDibujosDirs as $dir) {
            if (str_contains($dir, $otNorm)) {
                $foundDibujosArchive = true;
                if (!str_starts_with($dir, 'DOCUMENTACION_GIS/DIBUJOS_FUNDICION/INACTIVAS')) {
                    $dibujosArchivePath = 'DOCUMENTACION_GIS/DIBUJOS_FUNDICION/INACTIVAS/' . $dir;
                } else {
                    $dibujosArchivePath = $dir;
                }
                break;
            }
        }
        $this->assertTrue($foundDibujosArchive, "DIBUJOS folder should be moved to DIBUJOS_FUNDICION/INACTIVAS folder");

        $inactiveAlmacenDirs = Storage::disk('local')->directories('DOCUMENTACION_GIS/ALMACEN_FUNDICION/INACTIVAS');
        $foundAlmacenArchive = false;
        $almacenArchivePath = '';
        foreach ($inactiveAlmacenDirs as $dir) {
            if (str_contains($dir, $otNorm)) {
                $foundAlmacenArchive = true;
                if (!str_starts_with($dir, 'DOCUMENTACION_GIS/ALMACEN_FUNDICION/INACTIVAS')) {
                    $almacenArchivePath = 'DOCUMENTACION_GIS/ALMACEN_FUNDICION/INACTIVAS/' . $dir;
                } else {
                    $almacenArchivePath = $dir;
                }
                break;
            }
        }
        $this->assertTrue($foundAlmacenArchive, "ALMACEN folder should be moved to ALMACEN_FUNDICION/INACTIVAS folder");
        
        // Check that the archived directory contains the backed up files
        $this->assertTrue(Storage::disk('local')->exists($dibujosArchivePath . '/dibujo1.pdf'));
        $this->assertTrue(Storage::disk('local')->exists($almacenArchivePath . '/archivo1.pdf'));
 
        // Database record should be renamed and status set to 'inactiva'
        $updatedHistory = FundicionHistory::where('id', $history->id)->first();
        $this->assertEquals('inactiva', $updatedHistory->status);
        $this->assertNotEquals($otNorm, $updatedHistory->ot);
        $this->assertStringContainsString($otNorm, $updatedHistory->ot);
    }

    public function test_delete_empty_parent_fundicion()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $otNorm = 'OT 2102 - TEST';
        // Create only empty folders
        Storage::disk('local')->makeDirectory('DOCUMENTACION_GIS/DIBUJOS_FUNDICION/' . $otNorm);
        Storage::disk('local')->makeDirectory('DOCUMENTACION_GIS/ALMACEN_FUNDICION/' . $otNorm);

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

        // Verify physical directories are completely removed from active locations
        $this->assertFalse(Storage::disk('local')->exists('DOCUMENTACION_GIS/DIBUJOS_FUNDICION/' . $otNorm));
        $this->assertFalse(Storage::disk('local')->exists('DOCUMENTACION_GIS/ALMACEN_FUNDICION/' . $otNorm));

        $updatedHistory = FundicionHistory::where('id', $history->id)->first();
        $this->assertEquals('inactiva', $updatedHistory->status);
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
