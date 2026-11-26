<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

/*
|--------------------------------------------------------------------------
| Rutas de Sincronización Segura (Local <-> Nube)
|--------------------------------------------------------------------------
|
| Endpoints para mantener sincronizada la base de datos de la planta
| con el servidor en la nube. Protegidos por validación de X-Sync-Token.
|
*/
Route::prefix('sync')
    ->withoutMiddleware([\Illuminate\Routing\Middleware\ThrottleRequests::class . ':api'])
    ->group(function () {
    Route::get('/ping', [\App\Http\Controllers\Api\SyncController::class, 'ping']);
    Route::get('/tables', [\App\Http\Controllers\Api\SyncController::class, 'tables']);
    Route::post('/changes', [\App\Http\Controllers\Api\SyncController::class, 'changes']);
    Route::get('/pull', [\App\Http\Controllers\Api\SyncController::class, 'pull']);
    Route::match(['get', 'post'], '/push', [\App\Http\Controllers\Api\SyncController::class, 'push']);

    // Endpoints de Sincronización de Archivos Físicos (Dibujos, PDFs, Documentación GIS)
    Route::get('/files/manifest', [\App\Http\Controllers\Api\SyncController::class, 'filesManifest']);
    Route::get('/files/download', [\App\Http\Controllers\Api\SyncController::class, 'filesDownload']);
    Route::post('/files/upload', [\App\Http\Controllers\Api\SyncController::class, 'filesUpload']);
});
