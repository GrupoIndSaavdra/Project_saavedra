<?php

namespace App\Http\Controllers;

use App\Http\Requests\OTRequest;
use App\Models\Clase;
use App\Models\Fecha_proceso;
use App\Models\Metas;
use App\Models\Moldura;
use App\Models\Orden_trabajo;
use App\Models\Pieza;
use App\Models\Procesos;
use App\Models\SystemLog;
use App\Models\User;
use App\Http\Controllers\PtaResultsController;
use Barryvdh\DomPDF\Facade\Pdf as FacadePdf;
use DateTime;
use Illuminate\Http\Request;

class WOController extends Controller
{
    /** @var \App\Http\Controllers\ClassController */
    protected $classController;
    /** @var \App\Http\Controllers\ProcessesController */
    protected $processesController;
    /** @var \App\Http\Controllers\PzasLiberadasController */
    protected $releasedPiecesController;

    public function __construct()
    {
        $this->middleware('auth');
        $this->classController = new ClassController();
        $this->processesController = new ProcessesController();
        $this->releasedPiecesController = new PzasLiberadasController();
    }

    //Mostrar la vista para seleccionar o crear una Orden de Trabajo
    public function manage()
    {
        // ── OPTIMIZACIÓN: eager loading evita N+1 queries de moldura y clases ──
        $moldings       = Moldura::all();
        $workOrdersAll  = Orden_trabajo::query()->with(['clases', 'moldura'])->get();
        $workOrders     = null;

        if ($workOrdersAll->isNotEmpty()) {
            $workOrders = [];
            $counter    = 0;
            foreach ($workOrdersAll as $workOrder) {
                $clases = $workOrder->clases;
                if (auth()->user()->perfil == 5) {
                    if ($clases->count() == 0) continue;
                } else {
                    $clases = $clases->where('finalizada', 0);
                    if ($clases->count() == 0) continue;
                }
                // Moldura ya cargada con eager loading (0 queries)
                $workOrders[$counter]['workOrder'] = $workOrder->id;
                $workOrders[$counter]['molding']   = $workOrder->moldura ? $workOrder->moldura->nombre : '?';
                $counter++;
            }
        }
        return view('wo_views.manage_wo', compact('moldings', 'workOrders'));
    }

        /**
     * @param \Illuminate\Http\Request OTRequest $request
     */
    public function store(OTRequest $request) //Funcion para registrar una OT.
    {
        if (isset($request->workOrderAdded)) {
            //Creacion de la orden de trabajo registrada
            $newWorkOrder = new Orden_trabajo();
            $newWorkOrder->id = $request->workOrderAdded;
            $newWorkOrder->id_moldura = $request->moldingSelected;
            $newWorkOrder->save();
            
            SystemLog::create([
                'user_matricula' => auth()->user()->matricula,
                'action' => 'Cargo de OT',
                'details' => "El administrador registró la OT {$request->workOrderAdded} con id_moldura {$request->moldingSelected}.",
                'ot' => $request->workOrderAdded,
                'id_ot' => $request->workOrderAdded,
            ]);
        }
        //Busqueda de la orden de trabajo ingresada o creada
        $workOrder = Orden_trabajo::query()->find(isset($request->workOrderAdded) ? $request->workOrderAdded : $request->workOrderSelected);

        return redirect()->route('showWO', ['workOrder' => $workOrder]);
    }

    /**
     * @param mixed $workOrder
     */
    public function show($workOrder)
    {
        $workOrder = Orden_trabajo::query()->find($workOrder);
        $molding = Moldura::query()->find($workOrder->id_moldura);

        //Se obtienen las clases de la Orden de trabajo
        $classes = $this->classController->getClasses($workOrder);
        $classes = $classes->count() == 0 ? null : $classes;

        //Se obtienen las maquinas de los procesos guardados
        $processes = $this->classController->getClassProcesses($classes);
        return view('wo_views.show_wo', compact('workOrder', 'molding', 'classes', 'processes'));
    }

    /**
     * @param mixed $idWOrder
     */
    public function destroy($idWOrder)
    {
        $pieces = Pieza::query()->where('id_ot', $idWOrder)->get(); //Busco las piezas de la OT
        $goal = Metas::query()->where('id_ot', $idWOrder)->get();
        if (count($pieces) == 0 && count($goal) == 0) { //Si la OT no tiene piezas ni metas asociadas entonces
            $classes = Clase::query()->where('id_ot', $idWOrder)->get(); //Busco todas las clases que pertenecen a la OT
            foreach ($classes as $class) { //Recorro las clases de la OT
                $this->classController->destroy($class->id, $idWOrder); //Elimino las clases
            }
            $workOrder = Orden_trabajo::query()->find($idWOrder);
            if ($workOrder) {
                $workOrder->delete(); //Eliminar OT
            }
            return redirect()->route('manageWO')->with('success', '¡Orden de trabajo eliminada con éxito!'); //Redirecciono a la vista de registro de la OT
        }
        return redirect()->route('showWO', ['workOrder' => $idWOrder])->with('error', '¡La orden de trabajo no se puede eliminar porque tiene piezas o metas asociadas!');
    }
    /**
     * @param mixed $idWOrder
     */
    public function generatePDF($idWOrder)
    {
        $workOrder = Orden_trabajo::query()->find($idWOrder);
        $molding = Moldura::query()->find($workOrder->id_moldura);

        $classes = $this->classController->getClasses($workOrder);
        $classes = $classes->count() == 0 ? null : $classes;
        $processes = null;
        if ($classes) {
            $processesFounded = $this->classController->getClassProcesses($classes);
            if ($processesFounded != null) {
                $processes = [];
                //Obtener el nombre del campo del proceso
                foreach ($processesFounded as $idClass => $process) {
                    $processes[$idClass] = "";
                    foreach ($process as $processName => $value) {
                        $processes[$idClass] .= $this->nombreProceso($processName) . ", ";
                    }
                }
            }
        }
        $pdf = FacadePdf::loadView('wo_views.pdf_wo', compact('workOrder', 'molding', 'classes', 'processes'));
        return $pdf->download('Orden_de_trabajo_' . $workOrder->id . '.pdf');
    }

    public function show_panelWO()
    {
        return view('wo_views.progressPanel_wo');
    }

    /**
     * @param mixed $moldingId
     */
    public function getMolding($moldingId)
    {
        $molding = Moldura::query()->find($moldingId);
        return $molding ? $molding->nombre : null;
    }
    /**
     * @param array $array
     * @param mixed $class
     */
    public function insertClassesData(&$array, $class)
    {
        $array[$class->nombre] = array();
        $array[$class->nombre]["id"] = $class->id;
        $array[$class->nombre]["pieces"] = $class->piezas;
        $array[$class->nombre]["order"] = $class->pedido;
        $array[$class->nombre]["startDate"] = $this->getStringDate($class->fecha_inicio, $class->hora_inicio);
        $array[$class->nombre]["endDate"] = $class->fecha_termino ? $this->getStringDate($class->fecha_termino, $class->hora_termino) : "-";
        $array[$class->nombre]["processes"] = $this->insertProcessesData($class);
    }
    /**
     * @param mixed $class
     */
    public function insertProcessesData($class)
    {
        $processes = array();
        $processesFounded = Procesos::query()->where('id_clase', $class->id)->first();

        //Establecer el orden de los procesos
        $processesInOrder = array();
        switch ($class->nombre) {
            case "Bombillo":
            case "Molde":
                $processesInOrder = ["cepillado", "desbaste_exterior", "revision_laterales", "pOperacion", "barreno_maniobra", "sOperacion", "soldadura", "soldaduraPTA", "rectificado", "asentado", "calificado", "acabadoBombillo", "acabadoMolde", "barreno_profundidad", "cavidades", "copiado", "offSet", "palomas", "rebajes", "grabado"];
                break;
            case 'Corona':
                $processesInOrder = ["cepillado", "desbaste_exterior", "pOperacion", "sOperacion", "soldadura", "soldaduraPTA", "rectificado", "asentado", "calificado"];
                break;
            case "Obturador":
            case "Fondo":
                $processesInOrder = ["operacionEquipo", "soldadura", "soldaduraPTA"];
                break;
            case "Candado Obturador":
                $processesInOrder = ["operacionEquipo"];
                break;
            case "Plato":
                $processesInOrder = ["barreno_maniobra", "operacionEquipo"];
                break;
            case "Embudo":
                $processesInOrder = ["operacionEquipo", "embudoCM"];
                break;
            case "Cabeza de Soplo":
                $processesInOrder = ["primeraOperacionCabezaSoplo", "segundaOperacionCabezaSoplo"];
                break;
            default:
                $processesInOrder = [];
                break;
        }
        //Ordenar array
        $soldaduraBand = false;
        if ($processesFounded) {
            // Fallback: if all relevant processes are 0 (legacy data saved with wrong JS keys),
            // treat all processes in the expected list as active.
            $anyActive = false;
            foreach ($processesInOrder as $p) {
                if ($processesFounded[$p] != 0) {
                    $anyActive = true;
                    break;
                }
            }

            foreach ($processesInOrder as $process) {
                $isActive = $anyActive ? ($processesFounded[$process] != 0) : true;
                if ($isActive) {
                    if (str_contains($process, "soldadura") && $soldaduraBand) { // Verificar si soldadura o soldadura PTA ya fueron insertadas
                        continue;
                    }
                    $soldaduraBand = str_contains($process, "soldadura") ? true : false;
                    $field = $process == "operacionEquipo" ? ["1 operacion", "2 operacion"] : [$process];
                    foreach ($field as $processField) {
                        //Asignar el nombre del proceso
                        if (count($field) > 1) {
                            $processName = "Operacion Equipo_" . $processField;
                        } else {
                            if (str_contains($processField, "soldadura")) {
                                $processName = "Soldadura y Soldadura PTA";
                            } else {
                                $processName = $this->nombreProceso($processField);
                            }
                        }
                        $processes[$processName] = array();
                        $piecesBadData = array();
                        $processes[$processName]['pieces'] = $this->getPieces($class, $processName, $piecesBadData);
                        $processes[$processName]['piecesBadData'] = $piecesBadData; //Informacion de las piezas malas
                        $processes[$processName]['endDate'] = $this->getDateEndFromProcess($field, $class->id); //Fecha de termino del proceso
                    }
                }
            }
        }
        return $processes;

    }
    /**
     * @param mixed $process
     * @param mixed $class
     */
    public function getDateEndFromProcess($process, $class)
    {
        $dateEnd = Fecha_proceso::query()->where('clase', $class)->where('proceso', $process)->first();
        if ($dateEnd) {
            $formattedDate = new DateTime($dateEnd->fecha_fin);
            $formattedDate = $formattedDate->format('d-m-Y');

            $formattedTime = new DateTime($dateEnd->fecha_fin);
            $formattedTime = $formattedTime->format('H:i:s');
            return $this->getStringDate($formattedDate, $formattedTime);
        } else {
            return "---";
        }
    }

    public function showViewPiecesInProgress()
    {
        // ── OPTIMIZACIÓN: eager loading evita N+1 de clases y moldura ──
        $wOInProgress = array();
        $workOrders   = Orden_trabajo::query()->with(['clases', 'moldura'])->get();
        foreach ($workOrders as $workOrder) {
            $classes = $workOrder->clases->where('finalizada', 0);
            if ($classes->count() > 0) {
                foreach ($classes as $index => $class) {
                    $process = Procesos::query()->where('id_clase', $class->id)->first();
                    if ($process) {
                        if ($index === $classes->keys()->first()) {
                            $wOInProgress[$workOrder->id] = array();
                            $wOInProgress[$workOrder->id]['molding']  = $workOrder->moldura ? $workOrder->moldura->nombre : '?';
                            $wOInProgress[$workOrder->id]['classes']  = array();
                        }
                        $this->insertClassesData($wOInProgress[$workOrder->id]['classes'], $class);
                    }
                }
            }
        }

        // ── Datos de cards PTA (para las OTs actualmente en progreso) ────────
        // buildCardData devuelve null si la clase no tiene registros en PTA.
        $ptaCardsData = [];
        foreach (array_keys($wOInProgress) as $otId) {
            foreach ($wOInProgress[$otId]['classes'] as $className => $classData) {
                if (!isset($classData['id']))
                    continue;
                $claseId = $classData['id'];
                $cardData = PtaResultsController::buildCardData((string) $otId, $claseId);
                if ($cardData !== null) {
                    if (!isset($ptaCardsData[$otId])) {
                        $ptaCardsData[$otId] = [];
                    }
                    $ptaCardsData[$otId][$claseId] = $cardData;
                }
            }
        }

        [$pieces_Released, $info_Pieces] = $this->releasedPiecesController->piecesToBeReleased();
        return view('pieces_views.piecesInProgress_view', compact(
            'wOInProgress',
            'pieces_Released',
            'info_Pieces',
            'ptaCardsData'
        ));
    }

    /**
     * AJAX: devuelve JSON con los datos actualizados de la card PTA para una OT.
     * GET /piecesInProgress/ptaCard/{otId}
     */
    public function getPtaCardData(string $otId, string $claseId)
    {
        $data = PtaResultsController::buildCardData($otId, (int) $claseId);
        if ($data === null) {
            return response()->json(['error' => 'No PTA data'], 404);
        }
        return response()->json($data);
    }
    /**
     * @param string $date
     * @param string $time
     */
    public function getStringDate($date, $time)
    {
        $formattedDate = new DateTime($date);
        $formattedDate = $formattedDate->format('d-m-Y');

        //Establecer la fecha en español
        $dayName = new DateTime($date);
        $dayName = $dayName->format('l');

        switch ($dayName) {
            case "Monday":
                $dayName = "Lunes";
                break;
            case "Tuesday":
                $dayName = "Martes";
                break;
            case "Wednesday":
                $dayName = "Miercoles";
                break;
            case "Thursday":
                $dayName = "Jueves";
                break;
            case "Friday":
                $dayName = "Viernes";
                break;
            case "Saturday":
                $dayName = "Sabado";
                break;
            case "Sunday":
                $dayName = "Domingo";
                break;
        }

        $formattedTime = new DateTime($time);
        $formattedTime = $formattedTime->format('H:i:s A');

        return $dayName . " " . $formattedDate . " " . $formattedTime;
    }
    /**
     * @param string $proceso
     */
    public function nombreProceso($proceso)
    {
        switch ($proceso) {
            case "cepillado":
                return "Cepillado";
            case "desbaste_exterior":
                return "Desbaste Exterior";
            case "revision_laterales":
                return "Revision Laterales";
            case "pOperacion":
                return "Primera Operacion";
            case "barreno_maniobra":
                return "Barreno maniobra";
            case "sOperacion":
                return "Segunda Operacion";
            case "soldadura":
                return "Soldadura";
            case "soldaduraPTA":
                return "Soldadura PTA";
            case "rectificado":
                return "Rectificado";
            case "asentado":
                return "Asentado";
            case "calificado":
                return "Calificado";
            case "acabadoBombillo":
                return "Acabado Bombillo";
            case "acabadoMolde":
                return "Acabado Molde";
            case "cavidades":
                return "Cavidades";
            case "barreno_profundidad":
                return "Barreno Profundidad";
            case "copiado":
                return "Copiado";
            case "offSet":
                return "Off Set";
            case "palomas":
                return "Palomas";
            case "rebajes":
                return "Rebajes";
            case "grabado":
                return "Grabado";
            case "operacionEquipo":
                return "Operación Equipo";
            case "embudoCM":
                return "Embudo CM";
            case "primeraOperacionCabezaSoplo":
                return "Primera Operacion Cabeza Soplo";
            case "segundaOperacionCabezaSoplo":
                return "Segunda Operacion Cabeza Soplo";
        }
    }
    function finishOrder(Request $request)
    {
        // Algoritmo para finalizar el pedido de una clase
        $class = Clase::query()->where('id_ot', $request->wOrderName)->where('nombre', $request->className)->first();
        $arrayProcesses = $this->insertProcessesData($class);

        $counterRejected = 0;
        $text = "";
        $bandSold = false;
        foreach ($arrayProcesses as $key => $process) {
            $text = "No se puede finalizar el pedido porque las piezas no se han completado en " . $key;
            $total = 0;
            // Sumar las piezas rechazadas de soldadura y soldadura pta
            if (str_contains($key, "Soldadura")) {
                if (!$bandSold) {
                    $bandSold = true;
                    foreach (["Soldadura", "Soldadura PTA"] as $processSold) {
                        $counterRejected += array_key_exists($processSold, $arrayProcesses) ? $arrayProcesses[$processSold]["pieces"]["bad"] : 0;
                    }
                }
            } else { // Sumar las piezas rechazadas del proceso actual
                $counterRejected += $process["pieces"]["bad"];
            }

            //Sumar las piezas buenas de los procesos
            if (($key == "Soldadura" || $key == "Soldadura PTA")) {
                if (array_key_exists("Soldadura", $arrayProcesses) && array_key_exists("Soldadura PTA", $arrayProcesses)) {
                    foreach (["Soldadura", "Soldadura PTA"] as $processSold) {
                        $total += array_key_exists($processSold, $arrayProcesses) ? $arrayProcesses[$processSold]["pieces"]["good"] : 0;
                    }
                }
                $text = "No se puede finalizar el pedido porque las piezas no se han completado en las soldaduras";
            } else {
                $total = $process["pieces"]["good"];
            }

            $total += $counterRejected; // Sumar las piezas rechazadas de los anteriores procesos con las piezas buenas del proceso

            if ($total < $class->piezas) {
                $finishOrder = ["error", $text];
                return redirect()->back()->with('finishOrder', $finishOrder);
            }
        }
        $class->finalizada = 1;
        $class->save();
        $finishOrder = ["success", "Se ha finalizado el pedido correctamente"];
        return redirect()->route('showPiecesInProgress')->with('finishOrder', $finishOrder);
    }
    /**
     * @param mixed $class
     * @param string $processName
     * @param array $piecesBadData
     */
    function getPieces($class, $processName, &$piecesBadData)
    {
        $setStoredParts = array();
        $piecesArray    = ['good' => [], 'bad' => [], 'total' => 0];
        $processNamesArray = $processName === 'Soldadura y Soldadura PTA'
            ? ['Soldadura', 'Soldadura PTA']
            : [$processName];

        // ── OPTIMIZACIÓN: pre-cargar users en memoria para evitar N+1 en getBadPiecesData ──
        $usersCache = User::all()->keyBy('matricula');

        foreach ($processNamesArray as $pName) {
            $pieces = Pieza::query()->where('proceso', $pName)->where('id_clase', $class->id)->get();

            if ($pieces->isEmpty()) continue;

            // ── Mapa n_pieza → pieza para buscar H/M sin queries adicionales ──
            $piecesMap = $pieces->keyBy('n_pieza');

            foreach ($pieces as $piece) {
                if (substr($piece->n_pieza, -1, 1) !== 'J') {
                    $pares = true;
                    preg_match('/^\d+/', $piece->n_pieza, $noSet);
                    $noSet = $noSet[0];

                    if (!in_array($noSet, $setStoredParts)) {
                        $setStoredParts[] = $noSet;

                        // ── 0 queries: buscar H/M desde el mapa en memoria ──
                        $pFemale = $piecesMap->get($noSet . 'H');
                        $pMale   = $piecesMap->get($noSet . 'M');

                        if ($pFemale && $pMale) {
                            if ($pFemale->liberacion == 0) {
                                if ($pFemale->error === 'Ninguno' && $pMale->error === 'Ninguno') {
                                    array_push($piecesArray['good'], $pFemale, $pMale);
                                } else {
                                    if ($processName === 'Soldadura PTA') {
                                        if (in_array($pFemale->error, ['Fundicion', 'Fundición']) || in_array($pMale->error, ['Fundicion', 'Fundición'])) {
                                            array_push($piecesArray['bad'], $pFemale, $pMale);
                                            if (in_array($pFemale->error, ['Fundicion', 'Fundición'])) array_push($piecesBadData, $this->getBadPiecesData($pFemale, null, '- - - ', $usersCache));
                                            if (in_array($pMale->error, ['Fundicion', 'Fundición']))   array_push($piecesBadData, $this->getBadPiecesData($pMale, null, '- - - ', $usersCache));
                                        } else {
                                            array_push($piecesArray['good'], $pFemale, $pMale);
                                        }
                                    } else {
                                        array_push($piecesArray['bad'], $pFemale, $pMale);
                                        if ($pFemale->error !== 'Ninguno') array_push($piecesBadData, $this->getBadPiecesData($pFemale, null, '- - - ', $usersCache));
                                        if ($pMale->error !== 'Ninguno')   array_push($piecesBadData, $this->getBadPiecesData($pMale, null, '- - - ', $usersCache));
                                    }
                                }
                            } elseif ($pFemale->liberacion == 1) {
                                array_push($piecesArray['good'], $pFemale, $pMale);
                            } else {
                                array_push($piecesArray['bad'], $pFemale, $pMale);
                                $piecesBadData[] = $this->getBadPiecesData($pFemale, $pFemale->error !== 'Ninguno' ? null : 'Rechazada', '- - - ', $usersCache);
                                $piecesBadData[] = $this->getBadPiecesData($pMale,   $pMale->error !== 'Ninguno'   ? null : 'Rechazada', '- - - ', $usersCache);
                            }
                        } else {
                            $incompletePiece = $pFemale ?? $pMale;
                            if ($incompletePiece && $incompletePiece->liberacion == 2) {
                                array_push($piecesArray['bad'], $incompletePiece, $incompletePiece);
                                $piecesBadData[] = $this->getBadPiecesData($incompletePiece, 'Rechazada', '- - - ', $usersCache);
                            }
                        }
                    }
                } else {
                    $pares = false;
                    if ($piece->liberacion == 0) {
                        if ($piece->error === 'Ninguno') {
                            array_push($piecesArray['good'], $piece);
                        } else {
                            if ($processName === 'Soldadura PTA') {
                                if (in_array($piece->error, ['Fundicion', 'Fundición'])) {
                                    array_push($piecesArray['bad'], $piece);
                                    $piecesBadData[] = $this->getBadPiecesData($piece, null, '- - - ', $usersCache);
                                } else {
                                    array_push($piecesArray['good'], $piece);
                                }
                            } else {
                                array_push($piecesArray['bad'], $piece);
                                $piecesBadData[] = $this->getBadPiecesData($piece, null, '- - - ', $usersCache);
                            }
                        }
                    } elseif ($piece->liberacion == 1) {
                        array_push($piecesArray['good'], $piece);
                    } else {
                        array_push($piecesArray['bad'], $piece);
                        $piecesBadData[] = $this->getBadPiecesData($piece, $piece->error !== 'Ninguno' ? null : 'Rechazada', '- - - ', $usersCache);
                    }
                }
            }
        }

        if (isset($pares)) {
            $goodCount = 0;
            foreach ($piecesArray['good'] as $p) {
                $goodCount += (substr($p->n_pieza, -1) === 'J') ? 1 : 0.5;
            }
            $badCount = 0;
            foreach ($piecesArray['bad'] as $p) {
                $badCount += (substr($p->n_pieza, -1) === 'J') ? 1 : 0.5;
            }
            $piecesArray['good']  = $goodCount;
            $piecesArray['bad']   = $badCount;
            $piecesArray['total'] = $goodCount + $badCount;
        } else {
            $piecesArray['good']  = 0;
            $piecesArray['bad']   = 0;
            $piecesArray['total'] = 0;
        }
        return $piecesArray;
    }

    /**
     * @param \App\Models\Pieza $piece
     * @param mixed $rechazada
     * @param string $operation
     * @param mixed $usersCache
     */
    function getBadPiecesData($piece, $rechazada = null, $operation = '- - - ', $usersCache = null)
    {
        $array    = array();
        // ── Usa cache si está disponible; si no, hace la query ──
        $operador = $usersCache
            ? $usersCache->get($piece->id_operador)
            : User::query()->where('matricula', $piece->id_operador)->first();

        $array['piece']     = $piece->n_pieza;
        preg_match('/^\d+/', $piece->n_pieza, $n_juego);
        $array['setNumber'] = $n_juego[0] . 'J';
        $array['operator']  = $operador ? "{$operador->nombre} {$operador->a_paterno} {$operador->a_materno}" : '(desconocido)';
        $array['process']   = $piece->proceso;
        $array['operation'] = $operation;
        $array['error']     = $rechazada ?? $piece->error;
        return $array;
    }
}
