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
use App\Http\Controllers\MoldingController;
use App\Http\Controllers\ProcessesController;
use App\Http\Controllers\ProcessProductionController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\WOController;
use Illuminate\Support\Facades\Route;

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
    Route::get('/users/create', 'create')->name('createUser');//Vista de crear usuario
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
    // Route::post('/processProduction', 'store')->name('storeProcessProduction'); //Ruta para guardar los procesos de producción
    // Route::get('/processProduction/{id}', 'edit')->name('editProcessProduction'); //Ruta para editar los procesos de producción
    // Route::post('/processProduction/update', 'update')->name('updateProcessProduction'); //Ruta para actualizar los procesos de producción
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
    Route::get('/piezasLiberar/{pieza}/{proceso}/{liberar}/{buena}/{request}', 'liberar_rechazar')->name('liberar_rechazar'); //Ruta para liberar o rechazar
});
//Rutas para el controlador de DatosProduccionController
Route::controller(DatosProduccionController::class)->group(function () {
    Route::get('/productionData', 'index')->name('productionData'); //Vista de datos de producción
    Route::post('/productionData', 'show')->name('showProduccion'); //Vista de datos de producción
});

//Rutas para el controlador de ProgressPanelController
Route::get('/panel-progreso', function () {
    return view('progress'); // o el nombre de tu vista Blade
})->name('panelProgreso');



//PROCESOS*************************************************************************************
//Grupo de rutas de cepillado
Route::controller(CepilladoController::class)->group(function () {
    Route::get('/cepillado/{error}', 'show')->name('cepillado'); //Vista de cepillado
    Route::get('/cepilladoHeader', 'storeheaderTable')->name('cepilladoHeaderGet'); //Guardar encabezado de la tabla de cepillado
    Route::post('/cepilladoHeader', 'storeheaderTable')->name('cepilladoHeader'); //Guardar encabezado de la tabla de cepillado
    Route::post('/editCepillado', 'edit')->name('editCepillado'); //Ruta para editar datos de la tabla de cepillado
});

//Grupo de rutas de Desbaste Exterior
Route::controller(DesbasteExteriorController::class)->group(function () {
    Route::get('/desbasteExterior/{error}', 'show')->name('desbasteExterior'); //Vista de Desbaste exterior
    Route::get('/desbasteHeader', 'storeheaderTable')->name('desbasteHeaderGet'); //Guardar encabezado de la tabla Desbaste Exterior
    Route::post('/desbasteHeader', 'storeheaderTable')->name('desbasteHeader'); //Guardar encabezado de la tabla Desbaste Exterior
    Route::post('/editDesbaste', 'edit')->name('editDesbaste'); //Ruta para editar datos de la tabla Desbaste Exterior
});

//Grupo de rutas para Revisión Laterales
Route::controller(RevLateralesController::class)->group(function () {
    Route::get('/revisionLaterales/{error}', 'show')->name('revisionLaterales'); //Vista de Revision laterales
    Route::get('/revLateralesHeader', 'storeheaderTable')->name('revLateralesHeaderGet'); //Guardar encabezado de la tabla Revision laterales
    Route::post('/revLateralesHeader', 'storeheaderTable')->name('revLateralesHeader'); //Guardar encabezado de la tabla Revision laterales
    Route::post('/editRevLaterales', 'edit')->name('editRevLaterales'); //Ruta para editar datos de la tabla Revision laterales
});

//Grupo de rutas de Primera Operación Soldadura
Route::controller(PrimeraOpeSoldaduraController::class)->group(function () {
    Route::get('/primeraOpeSoldadura/{error}', 'show')->name('primeraOpeSoldadura'); //Vista de Primera Operacion Soldadura
    Route::get('/primeraOpeSoldaduraHeader', 'storeheaderTable')->name('primeraOpeSoldaduraHeaderGet'); //Guardar encabezado de la tabla Primera Operacion Soldadura
    Route::post('/primeraOpeSoldaduraHeader', 'storeheaderTable')->name('primeraOpeSoldaduraHeader'); //Guardar encabezado de la tabla Primera Operacion Soldadura
    Route::post('/editPrimeraOpeSoldadura', 'edit')->name('editPrimeraOpeSoldadura'); //Ruta para editar datos de la tabla Primera Operacion Soldadura
});

//Grupo de rutas de Barreno Maniobra
Route::controller(BarrenoManiobraController::class)->group(function () {
    Route::get('/barrenoManiobra/{error}', 'show')->name('barrenoManiobra'); //Vista de Barreno Maniobra
    Route::get('/barrenoManiobraHeader', 'storeheaderTable')->name('barrenoManiobraHeaderGet'); //Guardar encabezado de la tabla Barreno Maniobra
    Route::post('/barrenoManiobraHeader', 'storeheaderTable')->name('barrenoManiobraHeader'); //Guardar encabezado de la tabla Barreno Maniobra
    Route::post('/editBarrenoManiobra', 'edit')->name('editBarrenoManiobra'); //Ruta para editar datos de la tabla Barreno Maniobra
});

//Grupo de rutas para Segunda Operación Soldadura
Route::controller(SegundaOpeSoldaduraController::class)->group(function () {
    Route::get('/segundaOpeSoldadura/{error}', 'show')->name('segundaOpeSoldadura'); //Vista de Segunda Operacion Soldadura
    Route::get('/segundaOpeSoldaduraHeader', 'storeheaderTable')->name('segundaOpeSoldaduraHeaderGet'); //Guardar encabezado de la tabla Segunda Operacion Soldadura
    Route::post('/segundaOpeSoldaduraHeader', 'storeheaderTable')->name('segundaOpeSoldaduraHeader'); //Guardar encabezado de la tabla Segunda Operacion Soldadura
    Route::post('/editSegundaOpeSoldadura', 'edit')->name('editSegundaOpeSoldadura'); //Ruta para editar datos de la tabla Segunda Operacion Soldadura
});

//Grupo de rutas para el controlador SoldaduraPTAController
Route::controller(SoldaduraController::class)->group(function () {
    Route::get('/soldadura/{error}', 'show')->name('soldadura'); //Vista de Soldadura
    Route::get('/soldaduraHeaderGet', 'storeheaderTable')->name('soldaduraHeaderGet'); //Guardar encabezado de la tabla Soldadura
    Route::post('/soldaduraHeader', 'storeheaderTable')->name('soldaduraHeader'); //Guardar encabezado de la tabla Soldadura
    Route::post('/editSoldadura', 'edit')->name('editSoldadura'); //Ruta para editar datos de la tabla Soldadura

});

//Grupo de rutas para el controlador SoldaduraPTAController
Route::controller(SoldaduraPTAController::class)->group(function () {
    Route::get('/soldaduraPTA/{error}', 'show')->name('soldaduraPTA'); //Vista de Soldadura PTA
    Route::get('/soldaduraPTAHeaderGet', 'storeheaderTable')->name('soldaduraPTAHeaderGet'); //Guardar encabezado de la tabla Soldadura PTA
    Route::post('/soldaduraPTAHeader', 'storeheaderTable')->name('soldaduraPTAHeader'); //Guardar encabezado de la tabla Soldadura PTA
    Route::post('/editSoldaduraPTA', 'edit')->name('editSoldaduraPTA'); //Ruta para editar datos de la tabla Soldadura PTA
});

//Grupo de rutas para el controlador RectificadoController
Route::controller(RectificadoController::class)->group(function () {
    Route::get('/rectificado/{error}', 'show')->name('rectificado'); //Vista Rectificado
    Route::get('/rectificadoHeader', 'storeheaderTable')->name('rectificadoHeaderGet'); //Guardar encabezado de la tabla Rectificado
    Route::post('/rectificadoHeader', 'storeheaderTable')->name('rectificadoHeader'); //Guardar encabezado de la tabla Rectificado
    Route::post('/editRectificado', 'edit')->name('editRectificado'); //Ruta para editar datos de la tabla Rectificado
});

//Grupo de rutas para el controlador AsentadoController
Route::controller(AsentadoController::class)->group(function () {
    Route::get('/asentado/{error}', 'show')->name('asentado'); //Vista de Asentado
    Route::get('/asentadoHeader', 'storeheaderTable')->name('asentadoHeaderGet'); //Guardar encabezado de la tabla Asentado
    Route::post('/asentadoHeader', 'storeheaderTable')->name('asentadoHeader'); //Guardar encabezado de la tabla Asentado
    Route::post('/editAsentado', 'edit')->name('editAsentado'); //Ruta para editar datos de la tabla Asentado
});

//Grupo de rutas para el controlador revCalificadoController
Route::controller(revCalificadoController::class)->group(function () {
    Route::get('/calificado/{error}', 'show')->name('calificado'); //Vista de Calificado
    Route::get('/calificadoHeader', 'storeheaderTable')->name('calificadoHeaderGet'); //Guardar encabezado de la tabla Calificado
    Route::post('/calificadoHeader', 'storeheaderTable')->name('calificadoHeader'); //Guardar encabezado de la tabla Calificado
    Route::post('/editCalificado', 'edit')->name('editCalificado'); //Ruta para editar datos de la tabla Calificado
});

//Grupo de rutas para el controlador AcabadoBombilloController
Route::controller(AcabadoBombilloController::class)->group(function () {
    Route::get('/acabadoBombillo/{error}', 'show')->name('acabadoBombillo'); //Vista de acabado bombillo
    Route::get('/acabadoBombilloHeader', 'storeheaderTable')->name('acabadoBombilloHeaderGet'); //Guardar encabezado de la tabla acabado bombillo
    Route::post('/acabadoBombilloHeader', 'storeheaderTable')->name('acabadoBombilloHeader'); //Guardar encabezado de la tabla acabado bombillo
    Route::post('/editAcabadoBombillo', 'edit')->name('editAcabadoBombillo'); //Ruta para editar datos de la tabla acabado bombillo
});

//Grupo de rutas para el controlador AcabadoMoldeController
Route::controller(AcabadoMoldeController::class)->group(function () {
    Route::get('/acabadoMolde/{error}', 'show')->name('acabadoMolde'); //Vista de acabado molde
    Route::get('/acabadoMoldeHeader', 'storeheaderTable')->name('acabadoMoldeHeaderGet'); //Guardar encabezado de la tabla acabado molde
    Route::post('/acabadoMoldeHeader', 'storeheaderTable')->name('acabadoMoldeHeader'); //Guardar encabezado de la tabla acabado molde
    Route::post('/editAcabadoMolde', 'edit')->name('editAcabadoMolde'); //Ruta para editar datos de la tabla acabado molde
});

//Grupo de rutas para el vomtrolador BarrenoProfundidadController
Route::controller(BarrenoProfundidadController::class)->group(function () {
    Route::get('/barrenoProfundidad/{error}', 'show')->name('barrenoProfundidad'); //Vista de Barreno de profundidad
    Route::get('/barrenoProfundidadHeader', 'storeheaderTable')->name('barrenoProfundidadHeaderGet'); //Guardar encabezado de la tabla Barreno de profundidad
    Route::post('/barrenoProfundidadHeader', 'storeheaderTable')->name('barrenoProfundidadHeader'); //Guardar encabezado de la tabla Barreno de profundidad
    Route::post('/editBarrenoProfundidad', 'edit')->name('editBarrenoProfundidad'); //Ruta para editar datos de la tabla Barreno de profundidad
});

//Grupo de rutas para el controlador CavidadesController
Route::controller(CavidadesController::class)->group(function () {
    Route::get('/cavidades/{error}', 'show')->name('cavidades'); //Vista de Cavidades
    Route::get('/cavidadesHeader', 'storeheaderTable')->name('cavidadesHeaderGet'); //Guardar encabezado de la tabla Cavidades
    Route::post('/cavidadesHeader', 'storeheaderTable')->name('cavidadesHeader'); //Guardar encabezado de la tabla Cavidades
    Route::post('/editCavidades', 'edit')->name('editCavidades'); //Ruta para editar datos de la tabla Cavidades
});

//Grupo  de rutas para el controlador CopiadoController
Route::controller(CopiadoController::class)->group(function () {
    Route::get('/copiado/{error}', 'show')->name('copiado'); //Vista de Copiado
    Route::get('/copiadoHeader', 'storeheaderTable')->name('copiadoHeaderGet'); //Guardar encabezado de la tabla Copiado
    Route::post('/copiadoHeader', 'storeheaderTable')->name('copiadoHeader'); //Guardar encabezado de la tabla Copiado
    Route::post('/editCopiado', 'edit')->name('editCopiado'); //Ruta para editar datos de la tabla Copiado
});

//Grupo de rutas para el controlador OffSetController
Route::controller(OffSetController::class)->group(function () {
    Route::get('/offSet/{error}', 'show')->name('offSet'); //Vista de OffSet
    Route::get('/offSetHeader', 'storeheaderTable')->name('offSetHeaderGet'); //Guardar encabezado de la tabla OffSet
    Route::post('/offSetHeader', 'storeheaderTable')->name('offSetHeader'); //Guardar encabezado de la tabla OffSet
    Route::post('/editOffSet', 'edit')->name('editOffSet'); //Ruta para editar datos de la tabla OffSet
});

//Grupo de rutas para el controlador PalomasController
Route::controller(PalomasController::class)->group(function () {
    Route::get('/palomas/{error}', 'show')->name('palomas'); //Vista de palomas
    Route::get('/palomasHeader', 'storeheaderTable')->name('palomasHeaderGet'); //Guardar encabezado de la tabla Palomas
    Route::post('/palomasHeader', 'storeheaderTable')->name('palomasHeader'); //Guardar encabezado de la tabla Palomas
    Route::post('/editPalomas', 'edit')->name('editPalomas'); //Ruta para editar datos de la tabla Palomas
});

//Grupo de rutas para el controlador RebajesControllera
Route::controller(RebajesController::class)->group(function () {
    Route::get('/rebajes/{error}', 'show')->name('rebajes'); //Vista de proceso de rebajes
    Route::get('/rebajesHeader', 'storeheaderTable')->name('rebajesHeaderGet'); //Guardar encabezado de la tabla Rebajes
    Route::post('/rebajesHeader', 'storeheaderTable')->name('rebajesHeader'); //Guardar encabezado de la tabla Rebajes
    Route::post('/editRebajes', 'edit')->name('editRebajes'); //Ruta para editar datos de la tabla Rebajes
});

//Grupo de rutas para el controlador PySOpeController
Route::controller(PySOpeSoldaduraController::class)->group(function () {
    Route::get('/1y2OpeSoldadura/{error}', 'show')->name('1y2OpeSoldadura'); //Vista de Primera y segunda operación
    Route::get('/1y2OpeSoldaduraHeader', 'storeheaderTable')->name('1y2OpeSoldaduraHeaderGet'); //Guardar encabezado de la tabla Primera y segunda operación
    Route::post('/1y2OpeSoldaduraHeader', 'storeheaderTable')->name('1y2OpeSoldaduraHeader'); //Guardar encabezado de la tabla Primera y segunda operación
    Route::post('/edit1y2OpeSoldadura', 'edit')->name('edit1y2OpeSoldadura'); //Ruta para editar datos de la tabla Primera y segunda operación
});

//Grupo de rutas para el controlador EmbudoCMController
Route::controller(EmbudoCMController::class)->group(function () {
    Route::get('/embudoCM/{error}', 'show')->name('embudoCM'); //Vista de Embudo CM
    Route::get('/embudoCMHeader', 'storeheaderTable')->name('embudoCMHeaderGet'); //Guardar encabezado de la tabla Embudo CM
    Route::post('/embudoCMHeader', 'storeheaderTable')->name('embudoCMHeader'); //Guardar encabezado de la tabla Embudo CM
    Route::post('/editEmbudoCM', 'edit')->name('editEmbudoCM'); //Ruta para editar datos de la tabla Embudo CM
});