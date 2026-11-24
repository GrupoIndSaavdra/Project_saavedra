<?php

namespace Tests\Feature;

use Tests\TestCase;

class SyncTest extends TestCase
{
    /**
     * Prueba que el ping sin token sea rechazado con 401.
     */
    public function test_ping_without_token_returns_401(): void
    {
        $response = $this->getJson('/api/sync/ping');
        $response->assertStatus(401);
        $response->assertJson(['success' => false]);
    }

    /**
     * Prueba que el ping con token válido responda con éxito 200.
     */
    public function test_ping_with_valid_token_returns_200(): void
    {
        $token = config('sync.token');
        $response = $this->withHeaders(['X-Sync-Token' => $token])->getJson('/api/sync/ping');

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'version' => '2.0.0',
        ]);
    }

    /**
     * Prueba que el listado de tablas devuelva un arreglo válido.
     */
    public function test_tables_endpoint_returns_syncable_tables(): void
    {
        $token = config('sync.token');
        $response = $this->withHeaders(['X-Sync-Token' => $token])->getJson('/api/sync/tables');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'tables',
            'count',
        ]);
    }

    /**
     * Prueba el endpoint PULL para consultar registros de una tabla.
     */
    public function test_pull_endpoint_returns_records_structure(): void
    {
        $token = config('sync.token');
        $response = $this->withHeaders(['X-Sync-Token' => $token])->getJson('/api/sync/pull?table=users&limit=5');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'table',
            'records',
            'count',
            'has_more',
        ]);
    }

    /**
     * Prueba el endpoint PUSH con un lote vacío o válido.
     */
    public function test_push_endpoint_handles_batch_gracefully(): void
    {
        $token = config('sync.token');
        $response = $this->withHeaders(['X-Sync-Token' => $token])->postJson('/api/sync/push', [
            'table' => 'users',
            'records' => [],
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'processed' => 0,
        ]);
    }

    /**
     * Prueba el endpoint del manifiesto de archivos físicos.
     */
    public function test_files_manifest_returns_valid_structure(): void
    {
        $token = config('sync.token');
        $response = $this->withHeaders(['X-Sync-Token' => $token])->getJson('/api/sync/files/manifest');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'base_dir',
            'total_files',
            'files',
        ]);
    }

    /**
     * Prueba que el download rechace rutas inválidas o con directory traversal.
     */
    public function test_files_download_blocks_directory_traversal(): void
    {
        $token = config('sync.token');
        $response = $this->withHeaders(['X-Sync-Token' => $token])->getJson('/api/sync/files/download?path=../../test.pdf');

        $response->assertStatus(400);
        $response->assertJson(['success' => false]);
    }

    /**
     * Prueba que el upload rechace peticiones sin archivo adjunto.
     */
    public function test_files_upload_validates_file_presence(): void
    {
        $token = config('sync.token');
        $response = $this->withHeaders(['X-Sync-Token' => $token])->postJson('/api/sync/files/upload', [
            'path' => 'DIBUJOS_MAQUINADOS/test.pdf',
        ]);

        $response->assertStatus(400);
        $response->assertJson(['success' => false]);
    }
}
