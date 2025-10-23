<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreHeaderProcessRequest;
use App\Models\Clase;
use App\Models\Maquinas;
use App\Models\Metas;
use App\Models\Moldura;
use App\Models\Orden_trabajo;
use App\Models\Pieza;
use App\Models\Procesos;
use App\Models\tiempoproduccion;
use App\Models\User;
use Carbon\Carbon;
use DateTime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class ProcessProductionController extends Controller
{
    protected $classController;
    protected $processesController;
    protected $cepilladoController;

    public function __construct()
    {
        $this->middleware('auth');
        $this->classController = new ClassController();
        $this->processesController = new ProcessesController();
    }
    public function show($returnArray = null)
    {
        $wOrdersFounded = Orden_trabajo::all();
        $workOrders = array();
        if (count($wOrdersFounded) > 0) {
            foreach ($wOrdersFounded as $workOrder) {
                $classes = $this->classController->getClasses($workOrder);
                if (count($classes) > 0) {
                    $workOrders[$workOrder->id] = array();
                    $molding = Moldura::find($workOrder->id_moldura);
                    $workOrders[$workOrder->id]['moldura'] = $molding ? $molding->nombre : 'Moldura no encontrada';
                    foreach ($classes as $class) {
                        $processes = Procesos::where('id_clase', $class->id)->first();
                        if ($processes) {
                            $workOrders[$workOrder->id][$class->nombre] = array();
                            $workOrders[$workOrder->id][$class->nombre] = $this->setOrderedProcess($class); // Establecer el orden de los procesos disponibles e insertarlos en el array
                        }
                    }
                }
            }
        }
        $workOrders = count($workOrders) > 0 ? $workOrders : null;
        if ($returnArray != null) {
            return $workOrders; // Si se solicita un array, se retorna el array de órdenes de trabajo
        }
        return view('processes_views.processProduction_view', compact('workOrders'));
    }

    public function setOrderedProcess($class)
    {
        //Establecer el orden de los procesos
        $processesInOrder = ["cepillado", "desbaste_exterior", "revision_laterales", "pOperacion", "barreno_maniobra", "sOperacion", "soldadura", "soldaduraPTA", "rectificado", "asentado", "calificado", "acabadoBombillo", "acabadoMolde", "barreno_profundidad", "cavidades", "copiado", "offSet", "palomas", "rebajes", "grabado", "operacionEquipo", "embudoCM"];

        //Verificar los procesos por los que pasa la clase
        $processesNotEmpty = Procesos::where("id_clase", $class->id)->first();
        foreach ($processesInOrder as $key => $proc) {
            if ($processesNotEmpty->$proc == 0) {
                unset($processesInOrder[$key]);
            }
        }
        // Reindexar el array para mantener los índices consecutivos
        $processesInOrder = array_values($processesInOrder);

        // Convertir los procesos a su formato de nombre completo
        foreach ($processesInOrder as $key => $proc) {
            $processesInOrder[$key] = $this->processesController->convertProcessToString($proc);
        }

        return $processesInOrder;
    }

    public function showReportFormat($meta, $process, $edit)
    {
        $this->updateMeta($meta); // Actualizar la meta del operador
        $workOrders = $this->show(true); // Obtener el array de órdenes de trabajo disponibles para registrar piezas

        $meta = Metas::find($meta); // Obtener meta actualizada para encontrar los valores siguientes

        $machine = Maquinas::where('id_meta', $meta->id)->first();
        if (!$machine) {
            return redirect()->route('processProduction')->with('error', 'La máquina ha sido liberada. Por favor, crea una nueva meta para continuar registrando piezas.');
        }
        // Obtener orden de trabajo junto con la moldura asociada
        $workOrder = Orden_trabajo::find($meta->id_ot);
        $molding = Moldura::find($workOrder->id_moldura);

        $class = Clase::find($meta->id_clase); // Obtener clase de la meta
        $edit = $edit != 0 ? $edit : false; // Verificar si se está editando

        // Obtener cadena de proceso y subproceso (Si existe)
        $process = $this->getSub_Process($meta->proceso, 0);
        $subprocess = $this->getSub_Process($meta->proceso, 1);

        $piecesData = $this->get_ArrayPieces($process, $class, $meta); // Obtener datos de las piezas

        // Asignar los valores a un array que se enviara a la vista
        $arrayData = [
            'operator' => auth()->user()->matricula . ' - ' . auth()->user()->a_paterno . ' ' . auth()->user()->a_materno . ' ' . auth()->user()->nombre,
            'workOrder' => $workOrder->id . ' - ' . $molding->nombre,
            'class' => Clase::where('id_ot', $meta->id_ot)->where('id', $meta->id_clase)->first()->nombre,
            'process' => $process,
            'subprocess' => $subprocess,
            'startTime' => $meta->h_inicio,
            'endTime' => $meta->h_termino,
            'machine' => $meta->maquina,
            'date' => $meta->fecha,
            'edit' => $edit,
            'meta' => $meta,
            'numberPieces' => $this->verifyNumbersOfPieces($meta),
            'consignmentPieces' => $piecesData['consignmentPieces'],
            'remainingPieces' => $piecesData['remainingPieces'],
            'machinedPiecesInMeta' => $piecesData['machinedPiecesInMeta'],
            'availableAssemblies' => $piecesData['availableAssemblies'],
            'cNominals' => $this->saveCNominals($class, $process, $subprocess),
        ];

        $pieceToBeUsed = $this->get_pieceToBeUsed($process, $piecesData['availableAssemblies'], $meta, $class); // Obtener la pieza a utilizar en la interfaz del reporte
        return view('processes_views.processProduction_view', compact('arrayData', 'workOrders', 'pieceToBeUsed')); // Redireccionar a la vista del reporte con los datos
    }
    public function get_pieceToBeUsed($processName, $availableAssemblies, $meta, $class)
    {
        // Obtener el modelo del proceso
        $modelProcess = $this->get_ModelProcess($processName);
        $id_process = str_replace(" ", "_", $processName) . "_" . $class->nombre . "_" . $class->id_ot;
        $process = $modelProcess::where('id_proceso', $id_process)->first();

        // Obtener el modelo de las piezas del proceso
        $modelPieces = $this->get_ModelProcessPieces($processName);

        if ($process) { // Si no existe el proceso, no se puede obtener una pieza
            //Verificar si hay piezas vacias asociadas a la meta del usuario
            $pieceMeta = $modelPieces::where("id_meta", $meta->id)->whereNot('estado', 2)->first();
            if ($pieceMeta) { // Si hay una pieza vacía asociada a la meta, se puede usar
                $pieceMeta->estado = 1;
                $pieceMeta->save();
                return $pieceMeta;
            }

            //Verificar si hay alguna pieza que este en la misma maquina en la que se esta trabajando
            $unoccupiedPiece = $modelPieces::where("id_proceso", $process->id)->where('estado', 0)->first();
            if ($unoccupiedPiece) {
                $metaPiece = $unoccupiedPiece->id_meta;
                $metaPiece = Metas::find($metaPiece);
                if ($metaPiece->maquina == $meta->maquina) {
                    // Marcar la pieza como ocupada
                    $unoccupiedPiece->estado = 1;
                    $unoccupiedPiece->id_meta = $meta->id;
                    $unoccupiedPiece->save();
                    return $unoccupiedPiece;
                }
            }

            //Si no hay piezas vacias asociadas a la meta, se crea una nueva pieza solamente si el proceso es "Cepillado"
            if ($processName == "Cepillado") {
                if (count($availableAssemblies) > 0) {
                    $assembly = $availableAssemblies[0];
                    $noAssembly = substr($assembly, 0, -1); // Extraer el numero de juego
                    for ($i = 1; $i <= 2; $i++) {
                        $pieceLetter = $i > 1 ? "H" : "M"; // Asociar la letra de la mitad de la pieza

                        //Verificar que no exista la pieza que se quiere crear
                        $existingPiece = $modelPieces::where("id_proceso", $process->id)
                            ->where("n_pieza", $noAssembly . $pieceLetter)
                            ->first();

                        if (!$existingPiece) {
                            //Creación de piezas
                            $newPiece = new $modelPieces();
                            $newPiece->id_pza = $noAssembly . $pieceLetter . $process->id;
                            $newPiece->id_meta = $meta->id;
                            $newPiece->id_proceso = $process->id;
                            $newPiece->estado = 1;
                            $newPiece->n_pieza = $noAssembly . $pieceLetter;
                            $newPiece->n_juego = $assembly;
                            $newPiece->save();
                        }
                        if ($i == 1) {
                            $pieceToBeUsed = !$existingPiece ? $newPiece : $existingPiece;
                        }
                    }
                    return $pieceToBeUsed;
                } else {
                    return null;
                }
            } else {
                //Verificar que haya piezas registradas en el proceso anterior
                $previousProcess = $this->convertProcessToString($this->get_previousProcess($class, $processName));
                if ($previousProcess) {
                    $specialProcess = ["Soldadura", "Soldadura PTA", "Desbaste Exterior", "Revision Laterales"];
                    if (in_array($previousProcess, $specialProcess)) {
                        $specialProcesses = $previousProcess == "Soldadura" || $previousProcess == "Soldadura PTA" ? ["Soldadura", "Soldadura PTA"] : ["Desbaste Exterior", "Revision Laterales"];
                        foreach($specialProcesses as $specProcess) {
                            $modelPreviousProcessPieces = $this->get_ModelProcessPieces($specProcess);
                            $previousProcessId = str_replace(" ", "_", $specProcess) . "_" . $class->nombre . "_" . $class->id_ot;
                            $previousProcessDB = $this->get_ModelProcess($specProcess)::where('id_proceso', $previousProcessId)->first();
                            if ($previousProcessDB) {
                                $previousPieces = $modelPreviousProcessPieces::where('id_proceso', $previousProcessDB->id)->where('estado', 2)->get();
                                if ($previousPieces->isNotEmpty()) {
                                    break;
                                } else {
                                    return "NoPreviousPieces";
                                }
                            } else {
                                return "NoPreviousPieces";
                            }
                        }
                    } else {
                        $modelPreviousProcessPieces = $this->get_ModelProcessPieces($previousProcess);
                        $previousProcessId = str_replace(" ", "_", $previousProcess) . "_" . $class->nombre . "_" . $class->id_ot;
                        $previousProcessDB = $this->get_ModelProcess($previousProcess)::where('id_proceso', $previousProcessId)->first();
                        if ($previousProcessDB) {
                            $previousPieces = $modelPreviousProcessPieces::where('id_proceso', $previousProcessDB->id)->where('estado', 2)->get();
                            if (!$previousPieces->isNotEmpty()) {
                                return "NoPreviousPieces";
                            }
                        } else {
                            return "NoPreviousPieces";
                        }
                    }
                }
                return null;
            }
        } else {
            return "NoPreviousPieces";
        }
    }
    public function saveCNominals($class, $process, $subprocess)
    {
        if ($process == "Copiado") {
            $data = array();
            $data["Cilindrado"] = $this->processesController->searchCNominals($class, $process, "Cilindrado");
            $data["Cavidades"] = $this->processesController->searchCNominals($class, $process, "Cavidades");
            return $data;
        } else {
            return $this->processesController->searchCNominals($class, $process, $subprocess);
        }
    }

    public function getSub_Process($process, $param)
    {
        $subprocess = explode('_', $process);
        return isset($subprocess[$param]) ? $subprocess[$param] : null;
    }

    public function validatePasswordAdmin($passwordEntered)
    {
        if ($passwordEntered) {
            $users = User::all();
            foreach ($users as $user) {
                if ($user->perfil == 1 || $user->perfil == 3 || $user->perfil == 4) { // Verificar si el usuario es admin o calidad o superadmin
                    if (Hash::check($passwordEntered, $user->contrasena)) {
                        return true; // Contraseña correcta
                    }
                }
            }
        }
        return false; // No se proporcionó contraseña
    }
    public function verifiedPasswordAdmin(Request $request)
    {
        $password = $request->input('passwordAdmin');
        $this->validatePasswordAdmin($password);
        if ($this->validatePasswordAdmin($password)) {
            $meta = Metas::find($request->meta);
            $process = $meta->proceso;
            if (!$request->editPieces) {
                if ($meta) {
                    return redirect()->route('showReportFormat', ["meta" => $meta, "process" => $process, "edit" => 1])->with('success', 'Contraseña correcta. Ahora puedes editar tu meta');
                }
            }
            return redirect()->route('showReportFormat', ["meta" => $meta, "process" => $process, "edit" => 2])->with('success', 'Contraseña correcta. Ahora puedes editar las piezas que has registrado');
        }
        return redirect()->back()->with('error', 'Contraseña incorrecta, intenta de nuevo'); // Si la contraseña es incorrecta, retornar error
    }
    public function verifyNumbersOfPieces($meta)
    {
        $model = $this->get_ModelProcessPieces($meta->proceso);
        $piecesCount = $model::where('id_meta', $meta->id)->count();
        return $piecesCount;
    }
    public function storePiece(Request $request)
    {
        $meta = Metas::find($request->input('meta'));
        $machine = Maquinas::where('id_meta', $meta->id)->first();
        if ($machine) {
            $class = Clase::find($meta->id_clase);
            $this->savePiece($class, $meta->proceso, $request, $meta);

            //Retornar pieza siguiente
            return redirect()->route('showReportFormat', ["meta" => $meta, "process" => $request->process, "edit" => 0])->with('success', 'Pieza registrada correctamente.');
        } else {
            return redirect()->route('processProduction')->with('error', 'La máquina ha sido liberada. Por favor, crea una nueva meta para continuar registrando piezas.');
        }
    }
    public function savePiece($class, $processName, $request, $meta, $index = null, $arrayPieces = null)
    {
        // Obtener los datos de CNominal y Tolerancia del proceso
        if ($processName != "Soldadura" && $processName != "Asentado" && $processName != "Rectificado" && $processName != "Soldadura PTA") {
            $id_process = str_replace(" ", "_", $processName) . "_" . $class->nombre . "_" . $class->id_ot;
            [$cNominalModel, $toleranceModel] = $this->getModelProcessCNominal_Tolerance($processName);
            $cNominal = $cNominalModel::where("id_proceso", $id_process)->first();
            $tolerance = $toleranceModel::where("id_proceso", $id_process)->first();

            //Guardar los datos de la pieza en su respectiva tabla del proceso
            $controllerProcess = $this->get_ControllerProcess($processName);
            if ($processName == "Copiado") {
                $controllerProcess->storePiece($request, $cNominal, $tolerance, $index !== null ? $index : null, $arrayPieces);
            } else {
                $controllerProcess->storePiece($request, $cNominal, $tolerance, $index !== null ? $index : null);
            }
        } else {
            //Guardar los datos de la pieza en su respectiva tabla del proceso
            $controllerProcess = $this->get_ControllerProcess($processName);
            $controllerProcess->storePiece($request, $index !== null ? $index : null);
        }

        //Guardar la pieza en la tabla Piezas
        $modelProcessPiece = $this->get_ModelProcessPieces($request->process);
        $piece = $modelProcessPiece::find($index !== null ? $request->piece[$index] : $request->piece);
        $n_piece = $piece->n_pieza ? $piece->n_pieza : $piece->n_juego;
        $pieceInPiezas = Pieza::where("id_clase", $class->id)->where("proceso", $request->process)->where("n_pieza", $n_piece)->first();
        if (!$pieceInPiezas) {
            $pieceInPiezas = new Pieza();
            $pieceInPiezas->id_ot = $class->id_ot;
            $pieceInPiezas->id_clase = $class->id;
            $pieceInPiezas->n_pieza = $n_piece;
            $pieceInPiezas->id_operador = $meta->id_usuario;
            $pieceInPiezas->maquina = $meta->maquina;
            $pieceInPiezas->proceso = $request->process;
        }
        if ($request->process == "Copiado") {
            $hasMaquinado = $piece->error_cilindrado === "Maquinado" || $piece->error_cavidades === "Maquinado";
            $hasFundicion = $piece->error_cilindrado === "Fundicion" || $piece->error_cavidades === "Fundicion";

            if ($hasFundicion) {
                $error = "Fundicion";
            } elseif ($hasMaquinado) {
                $error = "Maquinado";
            } else {
                $error = "Ninguno";
            }
        } else {
            $error = $piece->error;
        }
        $pieceInPiezas->error = $error;
        $pieceInPiezas->save();
    }
    public function selectAssembly(Request $request)
    {
        // Obtener las variables principales
        $meta = Metas::find($request->input('meta'));
        $machine = Maquinas::where('id_meta', $meta->id)->first();
        if (!$machine) {
            return redirect()->route('processProduction')->with('error', 'La máquina ha sido liberada. Por favor, crea una nueva meta para continuar registrando piezas.');
        }
        $class = Clase::find($meta->id_clase);

        // Obtener los modelo de la tabla de las piezas del proceso
        $processIdString = str_replace(" ", "_", $request->process) . "_" . $class->nombre . "_" . $class->id_ot;
        $modelProcess = $this->get_ModelProcess($request->process);
        $process = $modelProcess::where("id_proceso", $processIdString)->first();
        $modelPieces = $this->get_ModelProcessPieces($request->process);

        // Crear las piezas la tabla de piezas del proceso
        if ($request->selectedAssembly) {
            $processAssembly = ["Barreno Maniobra", "Soldadura", "Soldadura PTA", "Rectificado", "Asentado", "Barreno Profundidad", "Palomas", "Rebajes", "Grabado", "Operacion Equipo", "Embudo CM"];
            $noAssembly = substr($request->selectedAssembly, 0, -1); // Extraer el numero de juego
            if (in_array($request->process, $processAssembly)) {
                //Verificar que no exista la pieza que se quiere crear
                $existingPiece = $modelPieces::where("id_proceso", $process->id)
                    ->where("n_juego", $request->selectedAssembly)
                    ->first();

                if (!$existingPiece || ($existingPiece && $existingPiece->meta == $meta->id)) {
                    if ($request->process == "Soldadura" || $request->process == "Soldadura PTA") {
                        $reverseProcess = $request->process == "Soldadura" ? "Soldadura PTA" : "Soldadura";
                        $processReverseIdString = str_replace(" ", "_", $reverseProcess) . "_" . $class->nombre . "_" . $class->id_ot;
                        $modelProcessReverse = $this->get_ModelProcess($reverseProcess);
                        $reverseProcessDB = $modelProcessReverse::where("id_proceso", $processReverseIdString)->first();
                        $modelReversePieces = $this->get_ModelProcessPieces($reverseProcess);

                        $existingPiece = $modelReversePieces::where("id_proceso", $reverseProcessDB->id)
                            ->where("n_juego", $request->selectedAssembly)
                            ->first();
                        if ($existingPiece && $existingPiece->meta != $meta->id) {
                            $param = "error";
                            $message = 'El juego ' . $noAssembly . ' ya está en uso en ' . $reverseProcess . '. Por favor, elija otro juego.';
                            return redirect()->route('showReportFormat', ["meta" => $meta, "process" => $request->process, "edit" => 0])->with($param, $message);
                        }
                    }
                    // Obtener el proceso anterior
                    $previousProcess = $this->convertProcessToString($this->get_previousProcess($class, $request->process));
                    if ($previousProcess == "Desbaste Exterior" || $previousProcess == "Revision Laterales") {
                        [$availableAssemblies, $remainingPieces] = $this->getRemainingPieces_LateralesOrDesbaste($request->process, $previousProcess, $class);
                        if (!in_array($request->selectedAssembly, $availableAssemblies)) {
                            // Si el juego seleccionado esta disponible, se crea la pieza
                            $param = "error";
                            $message = 'El juego ' . $noAssembly . ' no está disponible. Por favor, elija otro juego.';
                            return redirect()->route('showReportFormat', ["meta" => $meta, "process" => $request->process, "edit" => 0])->with($param, $message);
                        }
                    }

                    //Creación de piezas
                    $newPiece = new $modelPieces();
                    $newPiece->id_pza = $request->selectedAssembly . $process->id;
                    $newPiece->id_meta = $meta->id;
                    $newPiece->id_proceso = $process->id;
                    $newPiece->estado = 1;
                    $newPiece->n_juego = $request->selectedAssembly;
                    $newPiece->save();

                    $param = "success";
                    $message = 'Juego ' . $noAssembly . ' seleccionado correctamente';
                } else {
                    $param = "error";
                    $message = 'El juego ' . $noAssembly . ' ya está en uso. Por favor, elija otro juego.';
                }
            } else {
                for ($i = 1; $i <= 2; $i++) {
                    $pieceLetter = $i > 1 ? "H" : "M"; // Asociar la letra de la mitad de la pieza

                    //Verificar que no exista la pieza que se quiere crear en el proceso actual
                    $existingPiece = $modelPieces::where("id_proceso", $process->id)
                        ->where("n_pieza", $noAssembly . $pieceLetter)
                        ->first();

                    if (!$existingPiece) {
                        // Verificar si el proceso actual es Revision Laterales o Desbaste Exterior
                        if ($request->process == "Revision Laterales" || $request->process == "Desbaste Exterior") {
                            // Verificar si el juego ya esta maquinado o ocupado en el proceso intermedio
                            $intermediateProcess = $request->process == "Revision Laterales" ? "Desbaste Exterior" : "Revision Laterales";
                            $intermediateProcessId = str_replace(" ", "_", $intermediateProcess) . "_" . $class->nombre . "_" . $class->id_ot;
                            $intermediateProcessDB = $this->get_ModelProcess($intermediateProcess)::where('id_proceso', $intermediateProcessId)->first();
                            if ($intermediateProcessDB) {
                                $assembly = $this->get_ModelProcessPieces($intermediateProcess)::where('n_juego', $request->selectedAssembly)->where('id_proceso', $intermediateProcessDB->id)->get();
                                if ($assembly->isNotEmpty()) {
                                    $status = 0;
                                    $correct = 0;
                                    $error = false;
                                    foreach ($assembly as $piece) {
                                        if ($piece->estado == 2) { // Si la pieza esta registrada y maquinada en el proceso intermedio
                                            $releasedPiece = $this->verifyPiece(Pieza::where('n_pieza', $piece->n_pieza)->where('proceso', $intermediateProcess)->where('id_clase', $class->id)->first());
                                            if ($releasedPiece) {
                                                $status += 1;
                                                $correct += 1;
                                            } else {
                                                $error = true;
                                            }
                                        } else if ($piece->estado == 0) {
                                            // Si la pieza esta registrada pero no ocupada ni maquinada en el proceso intermedio
                                            $status += 1;
                                        }
                                    }
                                    // Verificar que esten bien las dos o que ninugna este ocupada
                                    if (!($status > 1 && ($correct == 0 || $correct > 1))) {
                                        $param = "error";
                                        if ($error) {
                                            $message = 'El juego ' . $noAssembly . ' esta incorrecto en el proceso ' . $intermediateProcess . '. Por favor, elija otro juego.';
                                        } else {
                                            $message = 'El juego ' . $noAssembly . ' ya está en uso en el proceso ' . $intermediateProcess . '. Por favor, elija otro juego.';
                                        }
                                        return redirect()->route('showReportFormat', ["meta" => $meta, "process" => $request->process, "edit" => 0])->with($param, $message);
                                    }
                                }
                            }
                        }
                        //Creación de piezas
                        $newPiece = new $modelPieces();
                        $newPiece->id_pza = $noAssembly . $pieceLetter . $process->id;
                        $newPiece->id_meta = $meta->id;
                        $newPiece->id_proceso = $process->id;
                        $newPiece->estado = 1;
                        $newPiece->n_pieza = $noAssembly . $pieceLetter;
                        $newPiece->n_juego = $request->selectedAssembly;
                        $newPiece->save();

                        $param = "success";
                        $message = 'Juego ' . $noAssembly . ' seleccionado correctamente';
                    } else {
                        $param = "error";
                        $message = 'El juego ' . $noAssembly . ' ya está en uso. Por favor, elija otro juego.';
                    }
                }
            }
        } else {
            $param = "error";
            $message = 'No fue posible seleccionar el juego. Intenta de nuevo.';
        }
        return redirect()->route('showReportFormat', ["meta" => $meta, "process" => $request->process, "edit" => 0])->with($param, $message);
    }
    public function editPieces(Request $request)
    {
        $meta = Metas::find($request->meta);
        $machine = Maquinas::where('id_meta', $meta->id)->first();
        if (!$machine) {
            return redirect()->route('processProduction')->with('error', 'La máquina ha sido liberada. Por favor, crea una nueva meta para continuar registrando piezas.');
        }
        $class = Clase::find($meta->id_clase);

        $arrayPieces = array_unique($request->piece);
        foreach ($arrayPieces as $index => $piece) {
            if ($meta->proceso == "Copiado") {
                $this->savePiece($class, $meta->proceso, $request, $meta, $index, $arrayPieces);
            } else {
                $this->savePiece($class, $meta->proceso, $request, $meta, $index);
            }
        }
        //Retornar pieza siguiente
        return redirect()->route('showReportFormat', ["meta" => $meta, "process" => $request->process, "edit" => 0])->with('success', 'Piezas editadas correctamente.');
    }
    public function editMeta(Request $request)
    {
        // Verificar que la clase ingresada exista
        $workOrder = strtok($request->workOrder, ' ');
        $class = Clase::where('id_ot', $workOrder)->where('nombre', $request->class)->first(); //Obtener el id de la clase
        if ($class) {
            $foundedMeta = Metas::find($request->meta);
            if ($foundedMeta) {
                //Cambiar el formato de las horas ingresadas 00:00 a 00:00:00
                $startTime = DateTime::createFromFormat('H:i', $request->startTime);
                $startTime = $startTime->format('H:i:s');
                $endTime = DateTime::createFromFormat('H:i', $request->endTime);
                $endTime = $endTime->format('H:i:s');
                $date = Carbon::createFromFormat('Y-m-d', $request->date)->format('Y-m-d');

                //Verificar si ya hay piezas registradas de esa meta
                if ($this->verifyNumbersOfPieces($foundedMeta) == 0) {
                    // Verificar si la maquina no esta siendo ocupada
                    $machineOccupied = Maquinas::where('maquina', $request->machine)->where('proceso', $request->process)->first();
                    if (!$machineOccupied || $machineOccupied->id_meta === $foundedMeta->id) {
                        // Si la máquina ocupada es la misma que habia creado, se elimina
                        if ($machineOccupied) {
                            $machineOccupied->delete();
                        } else {
                            $oldMachine = Maquinas::where('maquina', $foundedMeta->maquina)->where('proceso', $foundedMeta->proceso)->first();
                            if ($oldMachine) {
                                $oldMachine->delete(); // Eliminar la máquina ocupada anterior
                            }
                        }

                        //Verificar si existe una meta creada con los datos ingresados
                        $existingMeta = Metas::where('id_ot', $workOrder)
                            ->where('id_clase', $class->id)
                            ->where('fecha', $date)
                            ->where('h_inicio', $startTime)
                            ->where('h_termino', $endTime)
                            ->where('maquina', $request->machine)
                            ->where('proceso', $request->process)
                            ->where('id_usuario', auth()->user()->matricula)
                            ->first();


                        if ($existingMeta && $existingMeta->id != $foundedMeta->id) { // Si existe la meta y es diferente a la anterior
                            // Si existe, borrar la meta
                            $foundedMeta->delete();
                            $this->storeMachine($request, $existingMeta); // Si la máquina no existe, se crea una nueva máquina ocupada asociada a la meta
                            $successMessage = 'Se ha ingresado correctamente a la meta de ' . auth()->user()->a_paterno . ' ' . auth()->user()->a_materno . ' ' . auth()->user()->nombre;
                            $meta = $existingMeta;
                        } else {
                            //Si no existe se edita la meta que habia ingresado
                            $this->storeMeta($request, $class, $startTime, $endTime, $date, $foundedMeta);
                            $this->storeMachine($request, $foundedMeta); // Se crea una nueva máquina ocupada asociada a la meta
                            $successMessage = 'Tu meta se ha editado correctamente';
                            $meta = $foundedMeta;
                        }
                        // Se retorna al reporte con el mensaje de exito
                        return redirect()->route('showReportFormat', ["meta" => $meta, "process" => $request->process, "edit" => 0])->with('success', $successMessage);
                    }
                    return redirect()->route('showReportFormat', ["meta" => $foundedMeta, "process" => $foundedMeta->proceso, "edit" => 0])->with('error', 'La máquina esta ocupada. Por favor, elija otra maquina o pida a un supervisor desbloquearla'); // Si la mquina esta ocupada retornar error con la meta antes creada
                } else {
                    $foundedMeta->fecha = $date;
                    $foundedMeta->h_inicio = $startTime;
                    $foundedMeta->h_termino = $endTime;
                    $this->calculateMeta($foundedMeta, $startTime, $endTime, $class);
                    $foundedMeta->save();
                    return redirect()->route('showReportFormat', ["meta" => $foundedMeta, "process" => $request->process, "edit" => 0])->with('success', 'Tu meta se ha editado correctamente');
                }
            }
            return redirect()->route('processProduction')->with('error', 'La meta a editar no se ha encontrado.'); // Si la meta a editar no existe, retornar error
        }
        return redirect()->route('processProduction')->with('error', 'La clase ingresada no existe.'); // Si la clase no existe, retornar error
    }
    public function storeHeaderdata(StoreHeaderProcessRequest $request)
    {
        $validatedData = $request->validated(); //Validación de los datos ingresados.
        // Verificar que la clase ingresada exista
        $class = Clase::where('id_ot', $request->workOrder)->where('nombre', $request->class)->first();
        if ($class) {
            // Verificar si la maquina no esta siendo ocupada
            $machineOccupied = Maquinas::where('maquina', $request->machine)->where('proceso', $request->process)->first();
            if (!$machineOccupied) {
                //Cambiar el formato de las horas ingresadas 00:00 a 00:00:00
                $startTime = DateTime::createFromFormat('H:i', $request->startTime);
                $startTime = $startTime->format('H:i:s');
                $endTime = DateTime::createFromFormat('H:i', $request->endTime);
                $endTime = $endTime->format('H:i:s');
                $date = Carbon::createFromFormat('Y-m-d', $request->date)->format('Y-m-d');

                $foundedMeta = Metas::where('id_ot', $request->workOrder)
                    ->where('id_clase', $class->id)
                    ->where('fecha', $date)
                    ->where('h_inicio', $startTime)
                    ->where('h_termino', $endTime)
                    ->where('maquina', $request->machine)
                    ->where('proceso', $request->process)
                    ->where('id_usuario', auth()->user()->matricula)
                    ->first();
                if ($foundedMeta) { // Si la máquina no existe, pero ya existe una meta con los mismos datos
                    $this->storeMachine($request, $foundedMeta); // Si la máquina no existe, se crea una nueva máquina ocupada asociada a la meta
                    $meta = $foundedMeta;
                    $successMessage = 'Se ha ingresado correctamente a la meta de ' . auth()->user()->a_paterno . ' ' . auth()->user()->a_materno . ' ' . auth()->user()->nombre;
                } else { // Si la máquina no existe y tampoco una meta con esos datos, se crea una nueva meta y maquina
                    $meta = $this->storeMeta($request, $class, $startTime, $endTime, $date);
                    $meta = Metas::find($meta->id);
                    $this->storeMachine($request, $meta); // Se crea una nueva máquina ocupada asociada a la meta
                    $successMessage = 'Se ha creado correctamente la meta';
                }
                return redirect()->route('showReportFormat', ["meta" => $meta, "process" => $request->process, "edit" => 0])->with('success', $successMessage);
            }
            return redirect()->route('processProduction')->with('error', 'La máquina esta ocupada. Por favor, elija otra maquina o pida a un supervisor desbloquearla');
        }
        return redirect()->route('processProduction')->with('error', 'La clase ingresada no existe.'); // Si la clase no existe, retornar error
    }

    public function storeMeta($request, $class, $startTime, $endTime, $date, $meta = null)
    {
        // Si no se encontró la meta, se puede crear una nueva
        if (!$meta) {
            $meta = new Metas();
        }
        $meta->id_ot = strtok($request->workOrder, ' ');
        $meta->id_usuario = auth()->user()->matricula;
        $meta->fecha = $date;
        $meta->h_inicio = $startTime;
        $meta->h_termino = $endTime;
        $meta->maquina = $request->machine;
        $meta->id_clase = $class->id;
        $meta->proceso = $request->subprocess ? $request->process . '_' . $request->subprocess : $request->process;
        $this->calculateMeta($meta, $startTime, $endTime, $class);
        $meta->save();
        return $meta;
    }

    public function storeMachine($request, $newMeta, $machineOccupied = null)
    {
        // Crear una nueva máquina ocupada asociada a la meta
        if (!$machineOccupied) {
            $machineOccupied = new Maquinas();
            $machineOccupied->maquina = $request->machine;
            $machineOccupied->proceso = $request->subprocess ? $request->process . '_' . $request->subprocess : $request->process;
        }
        $machineOccupied->id_meta = $newMeta->id;
        $machineOccupied->save();
    }

    public function finishReport($meta)
    {
        $meta = Metas::find($meta);
        if ($meta) {
            // Desocupar la maquina
            $machineOccupied = Maquinas::where('id_meta', $meta->id)->first();
            if ($machineOccupied) {
                $machineOccupied->delete();
            }
            // Desocupar piezas en la meta si es que estaban ocupadas
            $modelProcessPieces = $this->get_ModelProcessPieces($meta->proceso);
            $occupiedPieces = $modelProcessPieces::where('id_meta', $meta->id)->where('estado', 1)->get();
            if (count($occupiedPieces) > 0) {
                $processesAssemblies = ["Barreno Maniobra", "Soldadura", "Soldadura PTA", "Rectificado", "Asentado", "Barreno Profundidad", "Palomas", "Rebajes", "Grabado", "Operacion Equipo", "Embudo CM"];
                if (in_array($meta->proceso, $processesAssemblies)) {
                    foreach ($occupiedPieces as $piece) { // Si es un juego marcar como desocupado
                        $piece->delete();
                    }
                } else {
                    if (count($occupiedPieces) < 2) { // Si es una mitad marcar como desocupada
                        foreach ($occupiedPieces as $piece) {
                            $piece->estado = 0;
                            $piece->save();
                        }
                    } else {
                        foreach ($occupiedPieces as $piece) { // Si son dos mitades eliminarlas
                            $piece->delete();
                        }
                    }
                }
            }
            return redirect()->route('home');
        }
        return redirect()->route('home')->with('error', 'Meta no encontrada.');
    }

    public function get_ArrayPieces($process, $class, $meta)
    {
        $arrayData = array();
        // Obtener pedido con piezas de consignacion
        $consignmentPieces = Clase::find($class->id)->piezas;

        //Obtener las piezas maquinadas en la meta
        $machinedPiecesInMeta = $this->get_machinedPiecesInMeta($meta);

        //Calcular piezas restantes por maquinar y devolver las que estan disponibles
        $previousProcess = $this->convertProcessToString($this->get_previousProcess($class, $process));
        if ($previousProcess == "Desbaste Exterior" || $previousProcess == "Revision Laterales") {
            [$availableAssemblies, $remainingPieces] = $this->getRemainingPieces_LateralesOrDesbaste($process, $previousProcess, $class);
        } else if ($previousProcess == "Soldadura PTA") {
            [$availableAssemblies, $remainingPieces] = $this->getRemainingPieces_Soldaduras($process, $class);
        } else {
            $process = $process == "Operacion Equipo" ? $meta->proceso : $process;
            [$availableAssemblies, $remainingPieces] = $this->get_RemainingPieces($process, $previousProcess, $class);
        }

        if ($process == "Soldadura" || $process == "Soldadura PTA") {
            $reverseProcess = $process == "Soldadura" ? "Soldadura PTA" : "Soldadura";
            $this->get_FilteredPiecesSoldadura_SoldaduraPTA($reverseProcess, $class, $availableAssemblies, $remainingPieces);
        }

        // Asignar los valores en el array
        $arrayData = [
            'consignmentPieces' => $consignmentPieces,
            'machinedPiecesInMeta' => $machinedPiecesInMeta,
            'availableAssemblies' => $availableAssemblies,
            'remainingPieces' => $remainingPieces,
        ];
        return $arrayData;
    }
    public function getRemainingPieces_Soldaduras($process, $class)
    {
        //Obtener los juegos buenos maquinados en Soldadura y Soldadura PTA
        $processes = ["Soldadura", "Soldadura PTA"];
        $availableAssemblies = [];
        foreach ($processes as $processArray) {
            [$occupiedAssemblies, $machinedPieces] = $this->get_machinedPieces($processArray, $class);
            foreach ($machinedPieces as $piece) {
                if (!in_array($piece, $availableAssemblies)) {
                    if ($this->verifyPiece(Pieza::where('n_pieza', $piece)->where('proceso', $processArray)->where('id_clase', $class->id)->first())) {
                        $availableAssemblies[] = $piece;
                    }
                }
            }
        }

        //Filtrar los juegos ocupados y maquinados
        $remainingPieces = 0;
        //Filtrar los juegos que pasaron y los que se encuentran registrados en el proceso actual
        [$occupiedAssemblies, $machinedPieces] = $this->get_machinedPieces($process, $class); //Obtener las piezas maquinadas en el proceso actual
        foreach ($availableAssemblies as $assembly) {
            if (!in_array($assembly, $occupiedAssemblies)) {
                $remainingPieces++;
            } else { // Si el juego ya esta ocupado en el proceso actual pero no ha sido maquinado
                $processAssembly = ["Barreno Maniobra", "Soldadura", "Soldadura PTA", "Rectificado", "Asentado", "Barreno Profundidad", "Palomas", "Rebajes", "Grabado", "Operacion Equipo", "Embudo CM"];
                if (in_array($process, $processAssembly)) {
                    if (!in_array($assembly, $machinedPieces)) {
                        $remainingPieces += 1;
                    } else {
                        //Si ya esta maquinado eliminar del array de disponibles
                        $key = array_search($assembly, $availableAssemblies);
                        if ($key !== false) {
                            unset($availableAssemblies[$key]);
                            // Reindexar el array para evitar huecos en las claves
                            $availableAssemblies = array_values($availableAssemblies);
                        }
                    }
                } else {
                    for ($i = 1; $i <= 2; $i++) {
                        $halfPiece = substr($assembly, 0, -1) . ($i == 1 ? "H" : "M");
                        if (!in_array($halfPiece, $machinedPieces)) {
                            $remainingPieces += 0.5;
                        } else {
                            //Si ya esta maquinado eliminar del array de disponibles
                            $key = array_search($assembly, $availableAssemblies);
                            if ($key !== false) {
                                unset($availableAssemblies[$key]);
                                // Reindexar el array para evitar huecos en las claves
                                $availableAssemblies = array_values($availableAssemblies);
                            }
                        }
                    }
                }
            }
        }
        return [$availableAssemblies, $remainingPieces];
    }

    public function get_FilteredPiecesSoldadura_SoldaduraPTA($reverseProcess, $class, &$availableAssemblies, &$remainingPieces)
    {
        // Filtrar los juegos que aun no han sido maquinados en el proceso inverso
        [$occupiedAssemblies, $machinedPieces] = $this->get_machinedPieces($reverseProcess, $class);
        foreach ($occupiedAssemblies as $assembly) {
            if (in_array($assembly, $availableAssemblies)) {
                $key = array_search($assembly, $availableAssemblies);
                if ($key !== false) {
                    unset($availableAssemblies[$key]);
                    // Reindexar el array para evitar huecos en las claves
                    $availableAssemblies = array_values($availableAssemblies);
                    $modelProcess = $this->get_ModelProcess($reverseProcess);
                    $id_processId = str_replace(" ", "_", $reverseProcess) . "_" . $class->nombre . "_" . $class->id_ot;
                    $processDB = $modelProcess::where('id_proceso', $id_processId)->first();
                    $modelProcessPieces = $this->get_ModelProcessPieces($reverseProcess);
                    $assembly = $modelProcessPieces::where('id_proceso', $processDB->id)->where('n_juego', $assembly)->first();
                    if ($assembly && $assembly->estado == 2) {
                        $remainingPieces -= 1;
                    }
                }
            }
        }
    }
    public function updateMeta($metaId)
    {
        $meta = Metas::find($metaId);
        $class = Clase::find($meta->id_clase);
        $process = $meta->proceso;

        // Obtener cadena de proceso y subproceso (Si existe)
        $process = $this->getSub_Process($meta->proceso, 0);
        $subprocess = $this->getSub_Process($meta->proceso, 1);
        //Actualizar las piezas de la meta (correctas o incorrectas) en base a las Cotas nominales y Tolerancias
        $this->updatePieces($process, $subprocess, $meta, $class);

        //Obtener array de las piezas maquinadas en la meta
        $machinedPiecesInMeta = $this->get_machinedPiecesInMeta($meta);

        //Calcular la meta
        $meta->resultado = $this->calculate_metaResult($machinedPiecesInMeta, $class, $process);
        $meta->save();
    }
    // //Se actualiza las piezas de cada proceso para verificar que este correcta
    public function updatePieces($process, $subprocess, $meta, $class)
    {
        $processString = str_replace(" ", "_", $process); // Reemplazar espacios por guiones bajos
        $subprocessString = $subprocess ? str_replace(" ", "_", $subprocess) : null; // Reemplazar espacios por guiones bajos
        $completeString = $subprocessString ? $processString . '_' . $subprocessString : $processString;

        $processId = $completeString . '_' . $class->nombre . '_' . $meta->id_ot; // Obtener la cadena "id" del proceso

        //Obtener registro del proceso
        $modelProcess = $this->get_ModelProcess($process);
        $processDB = $modelProcess::where('id_proceso', $processId)->first();

        if ($processDB && ($process != "Soldadura PTA" && $process != "Soldadura" && $process != "Asentado" && $process != "Rectificado")) { // Si ya hay piezas creadas de  esa clase y proceso
            //Obtener las piezas registradas del proceso
            $modelProcessPieces = $this->get_ModelProcessPieces($process);
            $piecesInMeta = $modelProcessPieces::where('id_proceso', $processDB->id)->where('estado', 2)->where('id_meta', $meta->id)->get();

            if ($piecesInMeta->count() > 0) { // Si hay piezas registradas
                //Actualizar las piezas del proceso
                $controllerProcess = $this->get_ControllerProcess($process); // Obtener el controlador del proceso

                [$cNominalModel, $toleranceModel] = $this->getModelProcessCNominal_Tolerance($process); // Obtener los modelos de las Cotas nominales y Tolerancias del proceso
                $cNominal = $cNominalModel::where('id_proceso', $processId)->first();
                $tolerance = $toleranceModel::where('id_proceso', $processId)->first();

                //Verificar si las medidas de la pieza estan correctas
                foreach ($piecesInMeta as $piece) {
                    if ($meta->proceso == "Copiado") {
                        $correctSubprocess = $controllerProcess->comparePieceData($piece, $cNominal, $tolerance);
                        foreach ($correctSubprocess as $key => $value) {
                            if ($value == 0 && ($piece->$key == "Ninguno" || $piece->$key == "Maquinado")) {
                                $piece->$key = 'Maquinado';
                                $piece->correcto = 0;
                            } else if (($value == 0 && $piece->$key == 'Fundicion') || ($value == 1 && $piece->$key == 'Fundicion')) {
                                $piece->$key = $piece->$key;
                                $piece->correcto = 0;
                            } else {
                                $piece->$key = 'Ninguno';
                                $piece->correcto = 1;
                            }
                        }
                    } else {
                        $correct = $controllerProcess->comparePieceData($piece, $cNominal, $tolerance);
                        if ($correct == 0 && ($piece->error == "Ninguno" || $piece->error == "Maquinado")) {
                            $piece->error = 'Maquinado';
                            $piece->correcto = 0;
                        } else if (($correct == 0 && $piece->error == 'Fundicion') || ($correct == 1 && $piece->error == 'Fundicion')) {
                            $piece->error = $piece->error;
                            $piece->correcto = 0;
                        } else {
                            $piece->error = 'Ninguno';
                            $piece->correcto = 1;
                        }
                    }
                    $piece->save(); // Actualizar la pieza en su tabla

                    //Actualizar la pieza en la tabla de Piezas (En donde se almacenan todas las piezas)
                    $n_piece = $piece->n_pieza ? $piece->n_pieza : $piece->n_juego; // Obtener el nombre de la pieza o del juego
                    $pieceDB = Pieza::where('n_pieza', $n_piece)->where('id_ot', $meta->id_ot)->where('id_clase', $class->id)->where('proceso', $meta->proceso)->first();
                    if (!isset($pieceDB)) {
                        $pieceDB = new Pieza();
                        $pieceDB->id_ot = $class->id_ot;
                        $pieceDB->id_clase = $class->id;
                        $pieceDB->n_pieza = $n_piece;
                        $pieceDB->id_operador = $meta->id_usuario;
                        $pieceDB->maquina = $meta->maquina;
                        $pieceDB->proceso = $meta->proceso;
                    }
                    if ($meta->proceso == "Copiado") {
                        $hasMaquinado = $piece->error_cilindrado === "Maquinado" || $piece->error_cavidades === "Maquinado";
                        $hasFundicion = $piece->error_cilindrado === "Fundicion" || $piece->error_cavidades === "Fundicion";

                        if ($hasFundicion) {
                            $error = "Fundicion";
                        } elseif ($hasMaquinado) {
                            $error = "Maquinado";
                        } else {
                            $error = "Ninguno";
                        }
                    } else {
                        $error = $piece->error;
                    }
                    $pieceDB->error = $error;
                    $pieceDB->save();
                }
            }
        }
    }
    public function get_ControllerProcess($process)
    {
        return match ($process) {
            'Cepillado' => new CepilladoController(),
            'Desbaste Exterior' => new DesbasteExteriorController(),
            'Revision Laterales' => new RevLateralesController(),
            'Primera Operacion' => new PrimeraOpeSoldaduraController(),
            'Barreno Maniobra' => new BarrenoManiobraController(),
            'Segunda Operacion' => new SegundaOpeSoldaduraController(),
            'Soldadura' => new SoldaduraController(),
            'Soldadura PTA' => new SoldaduraPTAController(),
            'Rectificado' => new RectificadoController(),
            'Asentado' => new AsentadoController(),
            'Calificado' => new revCalificadoController(),
            'Acabado Bombillo' => new AcabadoBombilloController(),
            'Acabado Molde' => new AcabadoMoldeController(),
            'Barreno Profundidad' => new BarrenoProfundidadController(),
            'Cavidades' => new CavidadesController(),
            'Copiado' => new CopiadoController(),
            'Off Set' => new OffSetController(),
            'Palomas' => new PalomasController(),
            'Rebajes' => new RebajesController(),
        };
    }

    public function calculate_metaResult($arrayPiecesInMeta, $class, $process)
    {
        $total = 0;
        if ($arrayPiecesInMeta) {

            $usedAssemblies = array();
            foreach ($arrayPiecesInMeta as $piece) {
                //Verificar si la pieza se registra por mitad o por juego
                $char = substr($piece["piece"]->n_pieza, -1) ? substr($piece["piece"]->n_pieza, -1) : "J";
                if ($char == "J") {
                    $total += $piece["color"] == "green" || $piece["color"] == "blue" ? 1 : 0;
                } else {
                    $badPieces = 0;
                    // Extraer el numero de pieza
                    $noAssembly = substr($piece["piece"]->n_pieza, 0, -1);
                    // Encontrar la primera mitad del juego en la tabla Piezas
                    $halfPiece = Pieza::where('id_clase', $class->id)->where('proceso', $process)->where("n_pieza", $piece["piece"]->n_pieza)->first();

                    //Verificar si ese juego aun no ha sido contado
                    if (!in_array($noAssembly, $usedAssemblies)) {
                        array_push($usedAssemblies, $noAssembly);
                        // Buscar la segunda mitad del juego
                        $halfLetter = substr($halfPiece->n_pieza, -1) == "M" ? "H" : "M";
                        $halfPiece2 = Pieza::where('id_clase', $class->id)->where('proceso', $process)->where("n_pieza", $noAssembly . $halfLetter)->first();
                        if ($halfPiece2 == null) { // Si aun no existe la otra mitad
                            $total += $this->verifyPiece($halfPiece) ? 0.5 : 0;
                        } else {
                            //Verificar si las dos piezas estan bien para contarlo en la meta                            
                            $correct = 0;
                            if ($halfPiece->id_operador == $halfPiece2->id_operador) {
                                $correct += $this->verifyPiece($halfPiece) ? 0.5 : 0;
                                $correct += $this->verifyPiece($halfPiece2) ? 0.5 : 0;
                                $total += $correct < 1 ? 0 : 1;

                                // Verificar si las mitades no pertenecen a la misma meta
                                $id_process = str_replace(" ", "_", $process) . '_' . $class->nombre . '_' . $class->id_ot;
                                $processDB = $this->get_ModelProcess($process)::where('id_proceso', $id_process)->first();

                                $modelProcessPieces = $this->get_ModelProcessPieces($process);
                                $piece2 = $modelProcessPieces::where('id_proceso', $processDB->id)
                                    ->where(function ($query) use ($halfPiece2) {
                                        $query->where('n_pieza', $halfPiece2->n_pieza)
                                            ->orWhere('n_juego', substr($halfPiece2->n_pieza, 0, -1));
                                    })
                                    ->first();
                                if ($piece2->id_meta != $piece["piece"]->id_meta) {
                                    // Si las mitades pertenecen a diferentes metas, restar la mitad correspondiente
                                    $total -= 0.5;
                                }
                            } else {
                                // Seleccionar la pieza que corresponda al operador de la meta y verificarla
                                if ($halfPiece->id_operador == auth()->user()->matricula) {
                                    $total += $this->verifyPiece($halfPiece) ? 0.5 : 0;
                                } else {
                                    $total += $this->verifyPiece($halfPiece2) ? 0.5 : 0;
                                }
                            }
                        }
                    }
                }
            }
        }
        return $total;
    }
    public function verifyPiece($halfPiece)
    {
        if ($halfPiece) {
            if (($halfPiece->error == "Ninguno" && $halfPiece->liberacion == 0) ||
                ($halfPiece->error != "Ninguno" && $halfPiece->liberacion == 1) ||
                ($halfPiece->error == "Ninguno" && $halfPiece->liberacion == 1)
            ) {
                return true;
            }
        }
        return false;
    }
    public function get_machinedPiecesInMeta($meta)
    {
        //Obtener las piezas desde la tabla del proceso y despues compararla para ver si esta liberada o no
        $modelPiecesProcess = $this->get_ModelProcessPieces($meta->proceso);
        $piecesProcess = $modelPiecesProcess::where("id_meta", $meta->id)->where('estado', 2)->get();
        if (count($piecesProcess) > 0) {
            $machinedPieces = [];
            foreach ($piecesProcess as $key => $piece) {
                if ($meta->proceso == "Copiado") {
                    $color = $piece->error_cilindrado === "Ninguno" && $piece->error_cavidades === "Ninguno" ? "green" : "red";
                } else {
                    $color = $piece->error == "Ninguno" ? "green" : "red";
                }
                $nPiece = $piece->n_pieza ? $piece->n_pieza : $piece->n_juego;
                $releasedPiece = Pieza::where("id_clase", $meta->id_clase)->where('proceso', $meta->proceso)->where("n_pieza", $nPiece)->first();
                if ($releasedPiece) { //Verificar si la pieza esta inspeccionada (sin liberación, liberada, rechazada)
                    $color = match ($releasedPiece->liberacion) {
                        0 => $color, //Sin liberación
                        1 => 'blue', // Liberada
                        2 => 'red', // Rechazada
                    };
                }
                $machinedPieces[$key] = [
                    'piece' => $piece,
                    'color' => $color
                ];
            }
            return $machinedPieces;
        }
        return null;
    }
    public function get_previousProcess($class, $process)
    {
        $process = $this->get_processNameDB($process);

        //Establecer el orden de los procesos
        $processesInOrder = ["cepillado", "desbaste_exterior", "revision_laterales", "pOperacion", "barreno_maniobra", "sOperacion", "soldadura", "soldaduraPTA", "rectificado", "asentado", "calificado", "acabadoBombillo", "acabadoMolde", "barreno_profundidad", "cavidades", "copiado", "offSet", "palomas", "rebajes", "grabado", "operacionEquipo", "embudoCM"];

        //Verificar los procesos por los que pasa la clase
        $processesNotEmpty = Procesos::where("id_clase", $class->id)->first();
        foreach ($processesInOrder as $key => $proc) {
            if ($processesNotEmpty->$proc == 0) {
                unset($processesInOrder[$key]);
            }
        }
        // Reindexar el array para mantener los índices consecutivos
        $processesInOrder = array_values($processesInOrder);

        //Obtener el proceso anterior al actual
        $positionActualProcess = array_search($process, $processesInOrder);
        $previousProcess = $positionActualProcess !== 0 ? $processesInOrder[array_search($process, $processesInOrder) - 1] : null;
        if ($process == "soldaduraPTA" && $previousProcess == "soldadura" || $process == "revision_laterales" && $previousProcess == "desbaste_exterior") {
            if (array_search($previousProcess, $processesInOrder) != 0) {
                $previousProcess = $processesInOrder[array_search($process, $processesInOrder) - 2];
            } else {
                return null;
            }
        }
        return $previousProcess;
    }

    public function get_processNameDB($processName)
    {
        $process = match ($processName) {
            'Cepillado' => 'cepillado',
            'Desbaste Exterior' => 'desbaste_exterior',
            'Revision Laterales' => 'revision_laterales',
            'Primera Operacion' => 'pOperacion',
            'Barreno Maniobra' => 'barreno_maniobra',
            'Segunda Operacion' => 'sOperacion',
            'Rectificado' => 'rectificado',
            'Asentado' => 'asentado',
            'Calificado' => 'calificado',
            'Acabado Bombillo' => 'acabadoBombillo',
            'Acabado Molde' => 'acabadoMolde',
            'Barreno Profundidad' => 'barreno_profundidad',
            'Cavidades' => 'cavidades',
            'Copiado' => 'copiado',
            'Off Set' => 'offSet',
            'Palomas' => 'palomas',
            'Rebajes' => 'rebajes',
            'Grabado' => 'grabado',
            'Operacion Equipo' => 'operacionEquipo',
            'Operacion Equipo_1 operacion' => 'operacionEquipo',
            'Operacion Equipo_2 operacion' => 'operacionEquipo',
            'Embudo CM' => 'embudoCM',
            'Soldadura' => 'soldadura',
            'Soldadura PTA' => 'soldaduraPTA',
        };
        return $process;
    }

    public function getRemainingPieces_LateralesOrDesbaste($process, $previousProcess, $class)
    {
        $remainingPieces = 0;
        $availableAssemblies = array();

        // Obtener las piezas maquinadas de Desbaste y Revision Laterales y oragizarlas en arrays como buenas y malas
        $assembliesProcesses = [
            "Desbaste Exterior" => array("good" => array(), "bad" => array(), "incomplete" => array()),
            "Revision Laterales" => array("good" => array(), "bad" => array(), "incomplete" => array())
        ];
        foreach ($assembliesProcesses as $processName => $assemblies) {
            // Obtener el id del proceso anterior
            $id_process = str_replace(' ', '_', $processName) . "_" . $class->nombre . "_" . $class->id_ot;
            $modelProcess = $this->get_ModelProcess($processName);
            $processDB = $modelProcess::where('id_proceso', $id_process)->first();

            $countedAssemblies = array(); // Array para almacenar los juegos que ya han pasado
            if ($processDB) {
                // Obtener las piezas maquinadas en el proceso
                $modelPiecesProcess = $this->get_ModelProcessPieces($processName);
                $pieces = $modelPiecesProcess::where('id_proceso', $processDB->id)->where('estado', 2)->get();
                if (count($pieces) > 0) {
                    foreach ($pieces as $piece) {
                        if (!in_array($piece->n_juego, $countedAssemblies)) { // Si el juego aun no ha sido contado
                            array_push($countedAssemblies, $piece->n_juego); // Contar el juego
                            // Obtener las mitades de ese juego
                            $halfPieces = $modelPiecesProcess::where('n_juego', $piece->n_juego)->where('id_proceso', $processDB->id)->get();
                            // Verificar si el juego esta completo
                            if ($halfPieces->count() > 1) { // Si el juego esta completo
                                $correct = false;
                                foreach ($halfPieces as $halfPiece) {
                                    // Verificar si la pieza ya esta maquinada
                                    $half = Pieza::where('n_pieza', $halfPiece->n_pieza)->where('proceso', $processName)->where('id_clase', $class->id)->first();
                                    if ($half) { // Si esta maquinada
                                        $releasedPiece = $this->verifyPiece($half);
                                        if ($releasedPiece) { // Verificar si la mitad esta correcta
                                            $correct = true;
                                        } else {
                                            $correct = false;
                                            array_push($assembliesProcesses[$processName]["bad"], $piece->n_juego);
                                            break;
                                        }
                                    } else {
                                        $correct = false;
                                        array_push($assembliesProcesses[$processName]["incomplete"], $piece->n_juego);
                                        break;
                                    }
                                }
                                if ($correct) { // Si las dos piezas estan correctas y maquinadas
                                    array_push($assembliesProcesses[$processName]["good"], $piece->n_juego);
                                }
                            } else {
                                array_push($assembliesProcesses[$processName]["incomplete"], $piece->n_juego);
                            }
                        }
                    }
                }
            }
        }


        // Filtrar los juegos que pasan al siguiente proceso, unicamente pasan los que estan buenos en ambos procesos o los que en un proceso estan bien y en el otro aun no se han registrado
        $goodAssemblies = array();
        foreach ($assembliesProcesses as $processName => $assemblies) {
            $reverseProcess = $processName == "Desbaste Exterior" ? "Revision Laterales" : "Desbaste Exterior";
            foreach ($assembliesProcesses[$processName]["good"] as $goodAssembly) {
                if (!in_array($goodAssembly, $goodAssemblies)) {
                    if (!in_array($goodAssembly, $assembliesProcesses[$reverseProcess]["bad"]) && !in_array($goodAssembly, $assembliesProcesses[$reverseProcess]["incomplete"])) {
                        array_push($goodAssemblies, $goodAssembly);
                    }
                }
            }
        }

        //Filtrar los juegos que pasaron y los que se encuentran registrados en el proceso actual
        [$occupiedAssemblies, $machinedPieces] = $this->get_machinedPieces($process, $class); //Obtener las piezas maquinadas en el proceso actual
        foreach ($goodAssemblies as $assembly) {
            if (!in_array($assembly, $occupiedAssemblies)) {
                $remainingPieces++;
                array_push($availableAssemblies, $assembly);
            } else { // Si el juego ya esta ocupado en el proceso actual pero no ha sido maquinado
                $processAssembly = ["Barreno Maniobra", "Soldadura", "Soldadura PTA", "Rectificado", "Asentado", "Barreno Profundidad", "Palomas", "Rebajes", "Grabado", "Operacion Equipo", "Embudo CM"];
                if (in_array($process, $processAssembly)) {
                    if (!in_array($assembly, $machinedPieces)) {
                        $remainingPieces += 1;
                    }
                } else {
                    for ($i = 1; $i <= 2; $i++) {
                        $halfPiece = substr($assembly, 0, -1) . ($i == 1 ? "H" : "M");
                        if (!in_array($halfPiece, $machinedPieces)) {
                            $remainingPieces += 0.5;
                        }
                    }
                }
            }
        }
        return [$availableAssemblies, $remainingPieces];
    }
    public function get_RemainingPieces($process, $previousProcess, $class)
    {

        $remainingPieces = 0;
        $availableAssemblies = array();
        if ($previousProcess != null) {
            //Obtener las piezas maquinadas que esten correctas o liberadas del proceso anterior
            //Obtener el id del proceso anterior
            $modelPreProcess = $this->get_ModelProcess($previousProcess);
            $stringPreProcess = str_replace(' ', '_', $previousProcess);
            $stringPreProcess = $stringPreProcess . "_" . $class->nombre . "_" . $class->id_ot; // Obtener el registro de la tabla del proceso anterior
            $preProcessDB = $modelPreProcess::where('id_proceso', $stringPreProcess)->first();

            if ($preProcessDB) {
                //Obtener las piezas maquinadas en el proceso anterior
                $modelPiecesPreProcess = $this->get_ModelProcessPieces($previousProcess);
                $prePieces = $modelPiecesPreProcess::where('id_proceso', $preProcessDB->id)->where('estado', 2)->get();
                if (count($prePieces) > 0) {
                    [$occupiedAssemblies, $machinedPieces] = $this->get_machinedPieces($process, $class); //Obtener las piezas maquinadas en el proceso actual
                    $countedAssemblies = array();
                    //Guardar en un array los juegos restantes de la piezas del proceso anterior
                    foreach ($prePieces as $prePiece) {
                        $assembly = $modelPiecesPreProcess::where('n_juego', $prePiece->n_juego)->where('id_proceso', $prePiece->id_proceso)->get();
                        // Verificar si el juego ya esta ocupado o maquinado en el proceso actual o ya fue contado
                        if (!in_array($prePiece->n_juego, $occupiedAssemblies) && !in_array($prePiece->n_juego, $countedAssemblies)) {
                            array_push($countedAssemblies, $prePiece->n_juego);
                            $status = 0;
                            $correct = 0;
                            if ($assembly->count() > 1) { //Si el juego tiene dos mitades
                                foreach ($assembly as $piece) {
                                    $releasedPiece = $this->verifyPiece(Pieza::where('n_pieza', $piece->n_pieza)->where('proceso', $previousProcess)->where('id_clase', $class->id)->first());
                                    if ($releasedPiece) { // Si la pieza esta correcta o liberada en el proceso anterior
                                        if ($process == "Revision Laterales" || $process == "Desbaste Exterior") {
                                            // Verificar si la pieza ha sido registrada en el proceso intermedio
                                            $intermediateProcess = $process == "Revision Laterales" ? "Desbaste Exterior" : "Revision Laterales";
                                            $id_process = str_replace(" ", "_", $intermediateProcess) . "_" . $class->nombre . "_" . $class->id_ot;
                                            $processIntermediateDB = $this->get_ModelProcess($intermediateProcess)::where('id_proceso', $id_process)->first();
                                            if ($processIntermediateDB) {
                                                $pieceIntermedio = $this->get_ModelProcessPieces($intermediateProcess)::where('n_pieza', $piece->n_pieza)->where('id_proceso', $processIntermediateDB->id)->first();
                                                if ($pieceIntermedio) {
                                                    if ($pieceIntermedio->estado == 2) { // Si la pieza esta registrada y maquinada en el proceso intermedio
                                                        $releasedPiece = $this->verifyPiece(Pieza::where('n_pieza', $pieceIntermedio->n_pieza)->where('proceso', $intermediateProcess)->where('id_clase', $class->id)->first());
                                                        if ($releasedPiece) {
                                                            $status += 1;
                                                            $correct += 1;
                                                        }
                                                    } else if ($pieceIntermedio->estado == 0) {
                                                        // Si la pieza esta registrada pero no ocupada ni maquinada en el proceso intermedio
                                                        $status += 1;
                                                    }
                                                } else {
                                                    $status += 1;
                                                }
                                            } else {
                                                $status += 1;
                                            }
                                        } else {
                                            $status += 1;
                                        }
                                    }
                                }
                                if ($status > 1 && ($correct == 0 || $correct > 1)) { //Si las dos piezas estan correctas o liberadas contar el juego
                                    $remainingPieces++;
                                    array_push($availableAssemblies, $prePiece->n_juego);
                                }
                            } else { // Si el juego es completo
                                // Verificar si la pieza esta correcta
                                $releasedPiece = $this->verifyPiece(Pieza::where('n_pieza', $assembly[0]->n_juego)->where('proceso', $previousProcess)->where('id_clase', $class->id)->first());
                                if ($releasedPiece) {
                                    $remainingPieces++;
                                    array_push($availableAssemblies, $prePiece->n_juego);
                                }
                            }
                        } else if (!in_array($prePiece->n_juego, $countedAssemblies)) {
                            // Verificar si en el proceso actual se registran por juego o por mitad
                            $processAssembly = ["Barreno Maniobra", "Soldadura", "Soldadura PTA", "Rectificado", "Asentado", "Barreno Profundidad", "Palomas", "Rebajes", "Grabado", "Operacion Equipo", "Embudo CM"];
                            if (in_array($previousProcess, $processAssembly)) { // Si el proceso anterior se registran por juego
                                $prePiece = $prePiece->n_juego;
                                if (in_array($process, $processAssembly)) {
                                    if (!in_array($prePiece, $machinedPieces)) {
                                        $remainingPieces += 1; // Verificar si en el proceso actual se registran por juego o por mitad
                                    }
                                } else {
                                    $noAssembly = substr($prePiece, 0, -1);
                                    $halfLetter = substr($prePiece, -1) == "M" ? "H" : "M";
                                    for ($i = 1; $i <= 2; $i++) {
                                        $halfPiece = substr($prePiece, 0, -1) . ($i == 1 ? "H" : "M");
                                        if (!in_array($halfPiece, $machinedPieces)) {
                                            $remainingPieces += 0.5;
                                        }
                                    }
                                }
                            } else { // Si el proceso anterior se registran por mitad
                                $prePiece = $prePiece->n_pieza;
                                if (in_array($process, $processAssembly)) {
                                    $noAssembly = substr($prePiece, 0, -1);
                                    $halfPiece = $noAssembly . "J";
                                    if (!in_array($halfPiece, $machinedPieces)) {
                                        array_push($countedAssemblies, $halfPiece);
                                        $remainingPieces += 1; // Verificar si en el proceso actual se registran por juego o por mitad
                                    }
                                } else {
                                    if (!in_array($prePiece, $machinedPieces)) {
                                        $remainingPieces += 0.5; // Verificar si en el proceso actual se registran por juego o por mitad
                                    }
                                }
                            }
                        }
                    }
                }
            }
        } else {
            $consignamentPieces = $class->piezas; //Obtener las piezas con consignación
            [$occupiedAssemblies, $machinedPieces] = $this->get_machinedPieces($process, $class); //Obtener las piezas maquinadas

            //Calcular las piezas restantes
            $remainingPieces = $consignamentPieces;
            if (count($machinedPieces) > 0) {
                $letterPiece = substr($machinedPieces[0], -1);
                foreach ($machinedPieces as $piece) {
                    if ($letterPiece != "J") {
                        $remainingPieces = $remainingPieces - 0.5; //Si las piezas se registran por mitad restar .5
                    } else {
                        $remainingPieces = $remainingPieces - 1; //Si las piezas se registran completas restar 1
                    }
                }
            }

            //Filtrar las piezas disponibles en los procesos quitando las piezas maquinadas
            for ($i = 1; $i <= $consignamentPieces; $i++) {
                $n_juego_string = $i . "J";
                if (!in_array($n_juego_string, $occupiedAssemblies)) {
                    array_push($availableAssemblies, $n_juego_string);
                }
            }
        }
        return [$availableAssemblies, $remainingPieces];
    }

    public function get_machinedPieces($processName, $class)
    {
        // Obtener el modelo del proceso
        $modelProcess = $this->get_ModelProcess($processName);
        $stringProcess = str_replace(' ', '_', $processName);
        $id_process_string = $stringProcess . "_" . $class->nombre . "_" . $class->id_ot;
        $processDB = $modelProcess::where('id_proceso', $id_process_string)->first();

        //Si el proceso no existe crearlo para retornar las piezas
        if (!$processDB) {
            $processDB = new $modelProcess();
            $processDB->id_proceso = $id_process_string;
            $processDB->id_ot = $class->id_ot;
            if ($processName == "Operacion Equipo") {
                $processDB->id_clase = $class->id;
            }
            $processDB->save();
        }

        // Obtener las piezas maquinadas en el proceso correspondiente
        $modelPiecesProcess = $this->get_ModelProcessPieces($processName);
        $machinedPiecesInProcess = $modelPiecesProcess::where('id_proceso', $processDB->id)->where('estado', 2)->get();

        //Insertar los juegos maquinados en un array
        $machinedPieces = [];
        foreach ($machinedPiecesInProcess as $piece) {
            if ($piece->n_pieza) {
                array_push($machinedPieces, $piece->n_pieza);
            } else {
                array_push($machinedPieces, $piece->n_juego);
            }
        }
        //Obtener las piezas ocupadas en el proceso correspondiente
        $occupiedPieces = $modelPiecesProcess::where('id_proceso', $processDB->id)->whereIn('estado', [1, 2])->get();

        //Insertar los juegos ocupados en un array
        $occupiedAssemblies = [];
        foreach ($occupiedPieces as $piece) {
            if (!in_array($piece->n_juego, $occupiedAssemblies)) {
                $occupiedAssemblies[] = $piece->n_juego;
            }
        }
        return [$occupiedAssemblies, $machinedPieces];
    }
    public function get_ModelProcess($process)
    {
        $modelProcess = match ($process) {
            'Cepillado' => "Cepillado",
            'Desbaste Exterior' => "DesbasteExterior",
            'Revision Laterales' => "RevLaterales",
            'Primera Operacion' => "PrimeraOpeSoldadura",
            'Barreno Maniobra' => "BarrenoManiobra",
            'Segunda Operacion' => "SegundaOpeSoldadura",
            'Rectificado' => "Rectificado",
            'Asentado' => "Asentado",
            'Calificado' => "revCalificado",
            'Acabado Bombillo' => "AcabadoBombilo",
            'Acabado Molde' => "AcabadoMolde",
            'Barreno Profundidad' => "BarrenoProfundidad",
            'Cavidades' => "Cavidades",
            'Copiado' => "Copiado",
            'Off Set' => "OffSet",
            'Palomas' => "Palomas",
            'Rebajes' => "Rebajes",
            'Grabado' => "Grabado", // No existe, crearlo
            'Operacion Equipo' => "PySOpeSoldadura",
            'Operacion Equipo_1 operacion' => "PySOpeSoldadura",
            'Operacion Equipo_2 operacion' => "PySOpeSoldadura",
            'Embudo CM' => "EmbudoCM",
            'Soldadura' => "Soldadura",
            'Soldadura PTA' => "SoldaduraPTA",
        };
        return "App\Models\\" . $modelProcess;
    }
    public function getModelProcessCNominal_Tolerance($process)
    {
        $cNominal = match ($process) {
            'Cepillado' => "Cepillado_cnominal",
            'Desbaste Exterior' => "Desbaste_cnominal",
            'Revision Laterales' => "RevLaterales_cnominal",
            'Primera Operacion' => "PrimeraOpeSoldadura_cnominal",
            'Barreno Maniobra' => "BarrenoManiobra_cnominal",
            'Segunda Operacion' => "SegundaOpeSoldadura_cnominal",
            'Calificado' => "revCalificado_cnominal",
            'Acabado Bombillo' => "AcabadoBombilo_cnominal",
            'Acabado Molde' => "AcabadoMolde_cnominal",
            'Barreno Profundidad' => "BarrenoProfundidad_cnominal",
            'Cavidades' => "Cavidades_cnominal",
            'Copiado' => "Copiado_cnominal",
            'Off Set' => "OffSet_cnominal",
            'Palomas' => "Palomas_cnominal",
            'Rebajes' => "Rebajes_cnominal",
            'Grabado' => "Grabado_cnominal", // No existe, crearlo
            'Operacion Equipo_1 operacion' => "PySOpeSoldadura_cnominal",
            'Operacion Equipo_2 operacion' => "PySOpeSoldadura_cnominal",
            'Embudo CM' => "EmbudoCM_cnominal",
        };
        $cNominal = "App\Models\\" . $cNominal;

        $tolerance = match ($process) {
            'Cepillado' => "Cepillado_tolerancia",
            'Desbaste Exterior' => "Desbaste_tolerancia",
            'Revision Laterales' => "RevLaterales_tolerancia",
            'Primera Operacion' => "PrimeraOpeSoldadura_tolerancia",
            'Barreno Maniobra' => "BarrenoManiobra_tolerancia",
            'Segunda Operacion' => "SegundaOpeSoldadura_tolerancia",
            'Calificado' => "revCalificado_tolerancia",
            'Acabado Bombillo' => "AcabadoBombilo_tolerancia",
            'Acabado Molde' => "AcabadoMolde_tolerancia",
            'Barreno Profundidad' => "BarrenoProfundidad_tolerancia",
            'Cavidades' => "Cavidades_tolerancia",
            'Copiado' => "Copiado_tolerancia",
            'Off Set' => "OffSet_tolerancia",
            'Palomas' => "Palomas_tolerancia",
            'Rebajes' => "Rebajes_tolerancia",
            'Grabado' => "Grabado_tolerancia", // No existe, crearlo
            'Operacion Equipo_1 operacion' => "PySOpeSoldadura_tolerancia",
            'Operacion Equipo_2 operacion' => "PySOpeSoldadura_tolerancia",
            'Embudo CM' => "EmbudoCM_tolerancias",
        };
        $tolerance = "App\Models\\" . $tolerance;

        return [$cNominal, $tolerance];
    }
    public function get_ModelProcessPieces($process)
    {
        $modelProcess = match ($process) {
            'Cepillado' => "Pza_cepillado",
            'Desbaste Exterior' => "Desbaste_pza",
            'Revision Laterales' => "RevLaterales_pza",
            'Primera Operacion' => "PrimeraOpeSoldadura_pza",
            'Barreno Maniobra' => "BarrenoManiobra_pza",
            'Segunda Operacion' => "SegundaOpeSoldadura_pza",
            'Rectificado' => "Rectificado_pza",
            'Asentado' => "Asentado_pza",
            'Calificado' => "revCalificado_pza",
            'Acabado Bombillo' => "AcabadoBombilo_pza",
            'Acabado Molde' => "AcabadoMolde_pza",
            'Barreno Profundidad' => "BarrenoProfundidad_pza",
            'Cavidades' => "Cavidades_pza",
            'Copiado' => "Copiado_pza",
            'Off Set' => "OffSet_pza",
            'Palomas' => "Palomas_pza",
            'Rebajes' => "Rebajes_pza",
            'Grabado' => "Grabado_pza", // No existe, crearlo
            'Operacion Equipo' => "PySOpeSoldadura_pza",
            'Operacion Equipo_1 operacion' => "PySOpeSoldadura_pza",
            'Operacion Equipo_2 operacion' => "PySOpeSoldadura_pza",
            'Embudo CM' => "EmbudoCM_pza",
            'Soldadura' => "Soldadura_pza",
            'Soldadura PTA' => "SoldaduraPTA_pza",
        };
        return "App\Models\\" . $modelProcess;
    }

    public function calculateHrs($h_inicio, $h_termino) //Función para calcular las horas trabajadas.
    {
        $carbon1 = Carbon::parse($h_inicio);
        $carbon2 = Carbon::parse($h_termino);

        // Si la hora de termino es menor o igual a la hora de inicio, se le suma un día a la hora de termino
        if ($carbon2 <= $carbon1) {
            $carbon2->addDay();
        }

        //Calcular la diferencia entre las horas en minutos
        $diferencia = $carbon1->diffInMinutes($carbon2);
        if ($diferencia > 480) {
            $diferencia = $diferencia - 90; //Si la diferencia es mayor a 8 horas, se le resta media hora de limpieza y una hora de comida
        } else {
            $diferencia = $diferencia - 60; //Si la diferencia es menor o igual a 8 horas, se le resta media hora de limpieza y media hora de comida
        }
        return $diferencia; //Retorno las horas trabajadas.
    }
    public function calculateMeta(&$meta, $h_inicio, $h_termino, $class) //Función para calcular la meta.
    {
        //Asignar tiempo estándar
        $tiempo = tiempoproduccion::where('id_clase', $class->id)->where('proceso', $this->nameProcess($meta->proceso))->first();
        $meta->t_estandar = $tiempo->tiempo ?? 0;

        //Calcular las horas de trabajo de cada operador
        if ($tiempo) {
            $workHrs = $this->calculateHrs($h_inicio, $h_termino);
            $tiempo = $tiempo->tiempo != 0 ? round(($workHrs / $tiempo->tiempo)) : 0;
            $meta->meta = $tiempo; //Asignar la meta calculada
        } else {
            $meta->meta = 0; //Si no se encuentra el tiempo, se asigna 0 a la meta
        }
        $meta->save();
    }

    public function nameProcess($process)
    {
        $nameProcess = match ($process) {
            'Cepillado' => 'cepillado',
            'Desbaste Exterior' => 'desbaste',
            'Revision Laterales' => 'revLaterales',
            'Primera Operacion' => 'primeraOpeSoldadura',
            'Barreno Maniobra' => 'barrenoManiobra',
            'Segunda Operacion' => 'segundaOpeSoldadura',
            'Rectificado' => 'rectificado',
            'Asentado' => 'asentado',
            'Calificado' => 'revCalificado',
            'Acabado Bombillo' => 'acabadoBombillo',
            'Acabado Molde' => 'acabadoMolde',
            'Barreno Profundidad' => 'barrenoProfundidad',
            'Cavidades' => 'cavidades',
            'Copiado' => 'copiado',
            'Off Set' => 'offset',
            'Palomas' => 'palomas',
            'Rebajes' => 'rebajes',
            'Grabado' => 'grabado',
            'Operacion Equipo_1 operacion' => 'operacionEquipo',
            'Operacion Equipo_2 operacion' => 'operacionEquipo',
            'Embudo CM' => 'embudoCM',
            'Soldadura' => 'soldadura',
            'Soldadura PTA' => 'soldaduraPTA',
        };
        return $nameProcess;
    }
    public function convertProcessToString($process)
    {
        switch ($process) {
            case "cepillado":
                return "Cepillado";
            case "desbaste_exterior":
                return "Desbaste Exterior";
            case "revision_laterales":
                return "Revision Laterales";
            case "pOperacion":
                return "Primera Operacion";
            case "barreno_maniobra":
                return "Barreno Maniobra";
            case "sOperacion":
                return "Segunda Operacion";
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
            case "barreno_profundidad":
                return "Barreno Profundidad";
            case "cavidades":
                return "Cavidades";
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
                return "Operacion Equipo";
            case "embudoCM":
                return "Embudo CM";
            case "soldadura":
                return "Soldadura";
            case "soldaduraPTA":
                return "Soldadura PTA";
            default:
                return null;
        }
    }
}
