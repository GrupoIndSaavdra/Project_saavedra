<?php

use App\Http\Controllers\AcabadoBombilloController;
use App\Http\Controllers\AcabadoMoldeController;
use App\Http\Controllers\AsentadoController;
use App\Http\Controllers\BarrenoManiobraController;
use App\Http\Controllers\BarrenoProfundidadController;
use App\Http\Controllers\CavidadesController;
use App\Http\Controllers\CepilladoController;
use App\Http\Controllers\ClassController;
use App\Http\Controllers\CopiadoController;
use App\Http\Controllers\DatosProduccionController;
use App\Http\Controllers\DesbasteExteriorController;
use App\Http\Controllers\EmbudoCMController;
use App\Http\Controllers\GestionOTController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\LogoutController;
use App\Http\Controllers\OffSetController;
use App\Http\Controllers\PalomasController;
use App\Http\Controllers\PrimeraOpeSoldaduraController;
use App\Http\Controllers\ProgresoProcesosController;
use App\Http\Controllers\PySOpeSoldaduraController;
use App\Http\Controllers\PzasGeneralesController;
use App\Http\Controllers\PzasLiberadasController;
use App\Http\Controllers\RebajesController;
use App\Http\Controllers\RectificadoController;
use App\Http\Controllers\revCalificadoController;
use App\Http\Controllers\RevLateralesController;
use App\Http\Controllers\SegundaOpeSoldaduraController;
use App\Http\Controllers\SoldaduraController;
use App\Http\Controllers\SoldaduraPTAController;
use App\Http\Controllers\TiemposProduccionController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\MachinesController;
use App\Http\Controllers\MoldingController;
use App\Http\Controllers\ProcessesController;
use App\Http\Controllers\ProcessProductionController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\WOController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TrackingSoldaduraController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/


Route::get('/', [HomeController::class, 'index'])->name('home');

//Ruta para el controlador LogoutController
Route::get('/logout', [LogoutController::class, 'logout'])->name('logout');

//Grupo de rutas para el controlador LoginController
Route::controller(LoginController::class)->group(function () {
    Route::get('/login', 'show')->name('login');
    Route::post('/login', 'login')->name('loginUser');
});

//Grupo de rutas para el controlador de ver usuarios en perfil de master
Route::controller(UserController::class)->group(function () {
    Route::get('/users', 'show')->name('users'); //Vista de usuarios
    Route::get('/users/create', 'create')->name('createUser'); //Vista de crear usuario
    Route::post('/users/create/store', 'store')->name('storeUser');
    Route::get('/users/recoverPassword', 'showRecoverPassword')->name('recoverPassword'); //Vista recuperar contraseña
    Route::post('/users/recoverPassword', 'recoverPassword')->name('recover'); //Recuperar contraseña

    // Route::get('/alta-usuario', [UserController::class, 'altaUsuario'])->name('alta_usuario');
    // Route::get('/baja-usuario', [UserController::class, 'bajaUsuario'])->name('baja_usuario');
    // Route::get('/eliminar-usuario', [UserController::class, 'eliminarUsuario'])->name('eliminar_usuario');
});

//Grupo de ruta para el controlador MolduraController
Route::controller(MoldingController::class)->group(function () {
    Route::get('/createMolding', 'create')->name('createMolding'); //Vista registrar moldura  
    Route::post('/createMolding/storeMolding', 'store')->name('storeMolding'); //Registrar moldura
    Route::get('/editMolding', 'edit')->name('editMolding'); //Vista editar moldura
    Route::post('/editMolding/update', 'update')->name('updateMolding'); //Actualizar moldura
    Route::get('/deleteMolding/{id}', 'destroy')->name('deleteMolding'); //Eliminar moldura
});

//Grupo de ruta para el controlador OTController
Route::controller(WOController::class)->group(function () {
    Route::get('/manageWO', 'manage')->name('manageWO');
    Route::post('/storeWO', 'store')->name('storeWO');
    Route::get('/showWO/{workOrder}', 'show')->name('showWO');
    Route::get('/destroyWO/{wo}', 'destroy')->name('destroyWO');
    Route::get('/generatePDFWO/{wo}', 'generatePDF')->name('generatePDFWO');
    Route::get('/piecesInProgress', 'showViewPiecesInProgress')->name('showPiecesInProgress');
    Route::get('/finishOrder/{wOrderName}/{className}', 'finishOrder')->name('finishOrder'); //Finalizar pedido
    Route::get('/show_panelWO', 'show_panelWO')->name('show_panelWO');
});

Route::controller(ClassController::class)->group(function () {
    Route::post('/saveClass', 'saveClass')->name('saveClass'); //Informacion sobre piezas agregadas
    Route::get('/destroyClass/{idClass}', 'destroy')->name('destroyClass'); //Eliminar clase
});

//Grupo de rutas para el controlador ProcesosController
Route::controller(ProcessesController::class)->group(function () {
    Route::get('/cNominals', 'show_cNominalsView')->name('cNominals'); //Ruta para la interfaz de los procesos para editar las cotas nominales y tolerancias
    Route::post('/cNominals/store', 'storeCNominalsData')->name('storeCNominals'); //Ruta para la interfaz de los procesos para guardar las cotas nominales y tolerancias
});

Route::controller(ProcessProductionController::class)->group(function () {
    Route::get('/processProduction', 'show')->name('processProduction'); //Ruta para ver los procesos de producción
    Route::post('/processProduction/selected', 'storeHeaderdata')->name('headerdata'); //Ruta para ver los procesos de producción
    Route::get('/processProduction/format/{meta}/{process}/{edit}', 'showReportFormat')->name('showReportFormat'); //Ruta para ver los procesos de producción
    Route::post('/processProduction/verified', 'verifiedPasswordAdmin')->name('verifiedPassword'); //Ruta para verificar la contraseña del administrador
    Route::post('/processProduction/editMeta', 'editMeta')->name('editMeta'); //Ruta para editar las metas
    Route::get('/processProduction/finishReport/{meta}', 'finishReport')->name('finishReport'); //Ruta para finalizar el reporte
    Route::post('/processProduction/storePiece', 'storePiece')->name('storePiece'); //Ruta para almacenar una pieza
    Route::post('/processProduction/selectAssembly', 'selectAssembly')->name('selectAssembly'); //Ruta para almacenar una pieza
    Route::post('/processProduction/editPieces', 'editPieces')->name('editPieces'); //Ruta para editar las piezas registradas
});

//Ruta para ver el progreso de los procesos
Route::get('/progresoOT', [ProgresoProcesosController::class, 'show'])->name('verProcesos');

//Grupo de rutas para el controlador TiemposProduccionCo    ntroller
Route::controller(TiemposProduccionController::class)->group(function () {
    // Route::get('/tiemposProduccion/update', 'update')->name('actualizarClases');
    Route::get('/tiemposProduccion/{clase?}', 'show')->name('showTimes');
    Route::post('/tiemposProduccion', 'store')->name('storeTimes');
});

//Grupo de rutas para el controlador PzasGeneralesController
Route::controller(PzasGeneralesController::class)->group(function () {
    Route::get('/pieces', 'showPiecesReport_view')->name('showPiecesReport_view'); //Ruta para la vista general de piezas
    Route::post('/pieces/search', 'getPiecesRequest')->name('searchPieces'); //Ruta para el controlador de piezas generales
    Route::get('/pieces/{pieces}/{process}/{profile}', 'showPiece')->name('chosenPiece'); //Vista de la pieza elegida

    Route::get('/piezasMaquina', 'showVistaMaquina')->name('vistaPzasMaquina'); //Ruta para la vista de piezas por maquina
    Route::post('/piezasMaquina', 'showMachinesProcess')->name('showMachinesProcess'); //Ruta para ver los procesos de las maquinas
});

//Grupo de rutas para el controlador PzasLiberadasController
Route::controller(PzasLiberadasController::class)->group(function () {
    Route::get('/releasePieces', 'show')->name('showReleasePieces_view'); //Ruta para la vista de piezas para liberar
    Route::post('/pieces', 'getPiecesRequest')->name('piecesRelease'); //Ruta para ver los procesos de las maquinas
    Route::post('/piezasLiberar', 'liberar_rechazar')->name('liberar_rechazar'); //Ruta para liberar o rechazar
});
//Rutas para el controlador de DatosProduccionController
Route::controller(DatosProduccionController::class)->group(function () {
    Route::get('/productionData', 'index')->name('productionData'); //Vista de datos de producción
    Route::post('/productionData', 'show')->name('showProduccion'); //Vista de datos de producción
});

//Rutas para el controlador de MachinesController
Route::controller(MachinesController::class)->group(function () {
    Route::get('/machinesOccupied', 'show')->name('machinesOccupied'); //Vista de máquinas ocupadas
    Route::post('/machinesOccupied/freeUp', 'freeUp')->name('freeUp'); //Vista de máquinas ocupadas
});

//Rutas para el controlador de ProgressPanelController
Route::get('/panel-progreso', function () {
    return view('progress'); // o el nombre de tu vista Blade
})->name('panelProgreso');

//Rutas para TrackingSoladura
Route::get('/trackingSoldadura', function () {
    return view('trackingsoldadura');
})->name('trackingSoldadura');


Route::get('/check-time', function () {
    return [
        'PHP timezone' => date_default_timezone_get(),
        'Laravel timezone' => config('app.timezone'),
        'Current time' => now()->toDateTimeString(),
    ];
});