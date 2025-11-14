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
use App\Models\PySOpeSoldadura;
use App\Models\PySOpeSoldadura_pza;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf as FacadePdf;
use DateTime;
use Illuminate\Http\Request;

class WOController extends Controller
{
    protected $classController;
    protected $processesController;
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
        $moldings = Moldura::all();
        $workOrdersAll = Orden_trabajo::all();
        //Si existen ordenes de trabajo registradas.
        $workOrders = null;
        if ($workOrdersAll != "[]") {
            $workOrders = []; //Arreglo para guardar las molduras de cada OT
            $counter = 0; // Contador para las molduras y OT
            foreach ($workOrdersAll as $workOrder) { //Recorro las ordenes de trabajo
                $clases = Clase::where("id_ot", $workOrder->id)->get();
                if (auth()->user()->perfil == 5) {
                    if ($clases->count() == 0) {
                        continue;
                    }
                } else {
                    $clases = Clase::where("id_ot", $workOrder->id)->where('finalizada', 0)->get();
                    if ($clases->count() == 0) {
                        continue;
                    }
                }
                $moldura = Moldura::find($workOrder->id_moldura);
                $workOrders[$counter]['workOrder'] = $workOrder->id;
                $workOrders[$counter]['molding'] = $moldura->nombre;
                $counter++;
            }
        }
        return view('wo_views.manage_wo', compact('moldings', 'workOrders'));
    }

    public function store(OTRequest $request) //Funcion para registrar una OT.
    {
        if (isset($request->workOrderAdded)) {
            //Creacion de la orden de trabajo registrada
            $newWorkOrder = new Orden_trabajo();
            $newWorkOrder->id = $request->workOrderAdded;
            $newWorkOrder->id_moldura = $request->moldingSelected;
            $newWorkOrder->save();
        }
        //Busqueda de la orden de trabajo ingresada o creada
        $workOrder = Orden_trabajo::find(isset($request->workOrderAdded) ? $request->workOrderAdded : $request->workOrderSelected);

        return redirect()->route('showWO', ['workOrder' => $workOrder]);
    }

    public function show($workOrder)
    {
        $workOrder = Orden_trabajo::find($workOrder);
        $molding = Moldura::find($workOrder->id_moldura);

        //Se obtienen las clases de la Orden de trabajo
        $classes = $this->classController->getClasses($workOrder);
        $classes = $classes->count() == 0 ? null : $classes;

        //Se obtienen las maquinas de los procesos guardados
        $processes = $this->classController->getClassProcesses($classes);
        return view('wo_views.show_wo', compact('workOrder', 'molding', 'classes', 'processes'));
    }

    public function destroy($idWOrder)
    {
        $pieces = Pieza::where('id_ot', $idWOrder)->get(); //Busco las piezas de la OT
        $goal = Metas::where('id_ot', $idWOrder)->get();
        if (count($pieces) == 0 && count($goal) == 0) { //Si la OT no tiene piezas ni metas asociadas entonces
            $classes = Clase::where('id_ot', $idWOrder)->get(); //Busco todas las clases que pertenecen a la OT
            foreach ($classes as $class) { //Recorro las clases de la OT
                $this->classController->destroy($class->id, $idWOrder); //Elimino las clases
            }
            $workOrder = Orden_trabajo::find($idWOrder);
            if ($workOrder) {
                $workOrder->delete(); //Eliminar OT
            }
            return redirect()->route('manageWO')->with('success', '¡Orden de trabajo eliminada con éxito!'); //Redirecciono a la vista de registro de la OT
        }
        return redirect()->route('showWO', ['workOrder' => $idWOrder])->with('error', '¡La orden de trabajo no se puede eliminar porque tiene piezas o metas asociadas!');
    }
    public function generatePDF($idWOrder)
    {
        $workOrder = Orden_trabajo::find($idWOrder);
        $molding = Moldura::find($workOrder->id_moldura);

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

    public function getMolding($moldingId)
    {
        $molding = Moldura::find($moldingId);
        return $molding ? $molding->nombre : null;
    }
    public function insertClassesData(&$array, $class)
    {
        $array[$class->nombre] = array();
        $array[$class->nombre]["pieces"] = $class->piezas;
        $array[$class->nombre]["order"] = $class->pedido;
        $array[$class->nombre]["startDate"] = $this->getStringDate($class->fecha_inicio, $class->hora_inicio);
        $array[$class->nombre]["endDate"] = $class->fecha_termino ? $this->getStringDate($class->fecha_termino, $class->hora_termino) : "-";
        $array[$class->nombre]["processes"] = $this->insertProcessesData($class);
    }
    public function insertProcessesData($class)
    {
        $processes = array();
        $processesFounded = Procesos::where('id_clase', $class->id)->first();

        //Establecer el orden de los procesos
        $processesInOrder = array();
        switch ($class->nombre) {
            case "Bombillo":
            case "Molde":
                $processesInOrder = ["cepillado", "desbaste_exterior", "revision_laterales", "pOperacion", "barreno_maniobra", "sOperacion", "soldadura", "soldaduraPTA", "rectificado", "asentado", "calificado", "acabadoBombillo", "acabadoMolde", "barreno_profundidad", "cavidades", "copiado", "offSet", "palomas", "rebajes", "grabado"];
                break;
            case "Obturador":
            case "Fondo":
                $processesInOrder = ["operacionEquipo", "soldadura", "soldaduraPTA"];
                break;
            default:
                $processesInOrder = [];
                break;
        }
        //Ordenar array
        $soldaduraBand = false;
        if ($processesFounded) {
            foreach ($processesInOrder as $process) {
                if ($processesFounded[$process] != 0) {
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
    public function getDateEndFromProcess($process, $class)
    {
        $dateEnd = Fecha_proceso::where('clase', $class)->where('proceso', $process)->first();
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
        //Obtener las ordenes de trabajo que aun siguen en progreso, es decir, que tienen clases que no han sido finalizadas.
        $wOInProgress = array();
        $workOrders = Orden_trabajo::all();
        foreach ($workOrders as $workOrder) {
            //Obtener las clases que pertenecen a la orden de trabajo y que no han sido finalizadas.
            $classes = Clase::where('id_ot', $workOrder->id)->where('finalizada', 0)->get();
            if (count($classes) > 0) {
                foreach ($classes as $index => $class) {
                    //Verificar si ya se asignaron procesos a la clase
                    $process = Procesos::where('id_clase', $class->id)->first();
                    if ($process) {
                        if ($index == 0) {
                            $wOInProgress[$workOrder->id] = array();
                            $wOInProgress[$workOrder->id]['molding'] = $this->getMolding($workOrder->id_moldura); //Insertar el nombre de la moldura
                            //Insertar las clases de la orden de trabajo
                            $wOInProgress[$workOrder->id]['classes'] = array();
                        }
                        $this->insertClassesData($wOInProgress[$workOrder->id]['classes'], $class);
                    }
                }
            }
        }
        [$pieces_Released, $info_Pieces] = $this->releasedPiecesController->piecesToBeReleased();
        return view('pieces_views.piecesInProgress_view', compact('wOInProgress', 'pieces_Released', 'info_Pieces'));
    }
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
        }
    }
    function finishOrder(Request $request)
    {
        // Algoritmo para finalizar el pedido de una clase
        $class = Clase::where('id_ot', $request->wOrderName)->where('nombre', $request->className)->first();
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
    function getPieces($class, $processName, &$piecesBadData)
    {
        $setStoredParts = array();
        $piecesArray = array();
        $piecesArray["good"] = array();
        $piecesArray["bad"] = array();
        $piecesArray["total"] = 0;
        $processNamesArray = $processName == "Soldadura y Soldadura PTA" ? ["Soldadura", "Soldadura PTA"] : [$processName];

        foreach ($processNamesArray as $processName) {
            $pieces = Pieza::where("proceso", $processName)->where('id_clase', $class->id)->get();
            if (count($pieces) > 0) {
                //Recorrer cada una de las piezas
                foreach ($pieces as $piece) {
                    //Verificar si es juego o pieza
                    if (substr($piece->n_pieza, -1, 1) != "J") { // Si no es un juego y se divide en hembra y macho
                        $pares = true;
                        preg_match('/^\d+/', $piece->n_pieza, $noSet); //Obtener el numero de juego de la pieza
                        $noSet = $noSet[0];
                        //Comprobar si el juego ya fue almacenado en el array
                        if (!in_array($noSet, $setStoredParts)) {
                            array_push($setStoredParts, $noSet); //Almacenar el juego en el array

                            //Obtener las piezas del juego
                            $pFemale = Pieza::where("n_pieza", $noSet . "H")->where('id_clase', $class->id)->where('proceso', $processName)->first();
                            $pMale = Pieza::where("n_pieza", $noSet . "M")->where('id_clase', $class->id)->where('proceso', $processName)->first();

                            //Verificar si ambas piezas existen
                            if ($pFemale && $pMale) {
                                //Verificar si el juego esta rechazado o liberado
                                if ($pFemale->liberacion == 0) {
                                    //Verificar si las pieza son correctas o no
                                    if ($pFemale->error == "Ninguno" && $pMale->error == "Ninguno") {
                                        array_push($piecesArray["good"], $pFemale, $pMale);
                                    } else {
                                        //Guardar el juego completo como malo
                                        array_push($piecesArray["bad"], $pFemale, $pMale);

                                        if ($pFemale->error != "Ninguno") {
                                            array_push($piecesBadData, $this->getBadPiecesData($pFemale));
                                        }
                                        if ($pMale->error != "Ninguno") {
                                            array_push($piecesBadData, $this->getBadPiecesData($pMale));
                                        }
                                    }
                                } else if ($pFemale->liberacion == 1) {
                                    array_push($piecesArray["good"], $pFemale, $pMale);
                                } else {
                                    array_push($piecesArray["bad"], $pFemale, $pMale);

                                    if ($pFemale->error != "Ninguno") {
                                        array_push($piecesBadData, $this->getBadPiecesData($pFemale));
                                    } else {
                                        array_push($piecesBadData, $this->getBadPiecesData($pFemale, "Rechazada"));
                                    }
                                    if ($pMale->error != "Ninguno") {
                                        array_push($piecesBadData, $this->getBadPiecesData($pMale));
                                    } else {
                                        array_push($piecesBadData, $this->getBadPiecesData($pMale, "Rechazada"));
                                    }
                                }
                            } else {
                                //Si no existe una de las piezas, se guarda la pieza incompleta como mala
                                $imcompletePiece = $pFemale ? $pFemale : $pMale;

                                if ($imcompletePiece->liberacion == 2) {
                                    array_push($piecesArray["bad"], $imcompletePiece, $imcompletePiece);
                                    array_push($piecesBadData, $this->getBadPiecesData($imcompletePiece, "Rechazada"));
                                }
                            }
                        }
                    } else {
                        $pares = false;
                        //Verificar si el juego esta rechazado o liberado
                        if ($piece->liberacion == 0) {
                            //Verificar si las pieza son correctas o no
                            if ($piece->error == "Ninguno") {
                                array_push($piecesArray["good"], $piece);
                            } else {
                                //Guardar el juego completo como malo
                                array_push($piecesArray["bad"], $piece);
                                array_push($piecesBadData, $this->getBadPiecesData($piece));
                            }
                        } else if ($piece->liberacion == 1) {
                            array_push($piecesArray["good"], $piece);
                        } else {
                            array_push($piecesArray["bad"], $piece);
                            if ($piece->error != "Ninguno") {
                                array_push($piecesBadData, $this->getBadPiecesData($piece));
                            } else {
                                array_push($piecesBadData, $this->getBadPiecesData($piece, "Rechazada"));
                            }
                        }
                    }
                }
            }
        }
        if (isset($pares)) {
            if ($pares) {
                $piecesArray["good"] = count($piecesArray["good"]) != 0 ? count($piecesArray["good"]) / 2 : 0;
                $piecesArray["bad"] = count($piecesArray["bad"]) != 0 ? count($piecesArray["bad"]) / 2 : 0;
            } else {
                $piecesArray["good"] = count($piecesArray["good"]);
                $piecesArray["bad"] = count($piecesArray["bad"]);
            }
            $piecesArray["total"] = $piecesArray["good"] + $piecesArray["bad"];
        } else {
            $piecesArray["good"] = 0;
            $piecesArray["bad"] = 0;
            $piecesArray["total"] = 0;
        }
        return $piecesArray;
    }
    function getBadPiecesData($piece, $rechazada = null, $operation = "- - - ")
    {
        $array = array();
        $operador = User::where('matricula', $piece->id_operador)->first();
        $array["piece"] = $piece->n_pieza;
        //Obtener el numero de juego
        preg_match('/^\d+/', $piece->n_pieza, $n_juego);
        $array["setNumber"] = $n_juego[0] . "J";
        $array["operator"] = $operador->nombre . " " . $operador->a_paterno . " "  . $operador->a_materno;
        $array["process"] = $piece->proceso;
        $array["operation"] = $operation;
        $array["error"] = $rechazada ? $rechazada : $piece->error; //Si la pieza no tiene ningun error pero esta rechazada
        return $array;
    }
}
