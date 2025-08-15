<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreHeaderProcessRequest;
use App\Models\Clase;
use App\Models\Maquinas;
use App\Models\Metas;
use App\Models\Moldura;
use App\Models\Orden_trabajo;
use App\Models\Pieza;
use App\Models\Pza_cepillado;
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
                            foreach ($processes->getAttributes() as $process => $valor) {
                                if (($process != "id" && $process != "id_clase" && $process != "soldadura" && $process != "soldaduraPTA" && $process != "rectificado" && $process != "asentado") && $valor != 0) {
                                    $process = $this->processesController->convertProcessToString($process);
                                    array_push($workOrders[$workOrder->id][$class->nombre], $process);
                                }
                            }
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

    public function showReportFormat($meta, $process, $edit)
    {
        $meta = Metas::find($meta);
        $workOrder = Orden_trabajo::find($meta->id_ot);
        $molding = Moldura::find($workOrder->id_moldura);

        $edit = $edit == 1 ? true : false; // Verificar si se está editando

        $arrayData = [
            'operator' => auth()->user()->matricula . ' - ' . auth()->user()->a_paterno . ' ' . auth()->user()->a_materno . ' ' . auth()->user()->nombre,
            'workOrder' => $workOrder->id . ' - ' . $molding->nombre,
            'class' => Clase::where('id_ot', $meta->id_ot)->where('id', $meta->id_clase)->first()->nombre,
            'process' => $this->getSub_Process($meta->proceso, 0),
            'subprocess' => $this->getSub_Process($meta->proceso, 1),
            'startTime' => $meta->h_inicio,
            'endTime' => $meta->h_termino,
            'machine' => $meta->maquina,
            'date' => $meta->fecha,
            'meta' => $meta,
            'edit' => $edit,
            'numberPieces' => $this->verifyNumbersOfPieces($meta)
        ];
        $adminPasswords = [];
        $workOrders = $this->show(true); // Obtener el array de órdenes de trabajo
        return view('processes_views.processProduction_view', compact('arrayData', 'workOrders'));
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
                if ($user->perfil == 1) {
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
            if ($meta) {
                $process = $meta->proceso;
                return redirect()->route('showReportFormat', ["meta" => $meta, "process" => $process, "edit" => 1])->with('success', 'Contraseña correcta. Ahora puedes editar tu meta');
            }
        }
        return redirect()->back()->with('error', 'Contraseña incorrecta, intenta de nuevo'); // Si la contraseña es incorrecta, retornar error
    }
    public function verifyNumbersOfPieces($meta)
    {
        $modelProcess = match ($meta->proceso) {
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
            'Operacion Equipo_1ra Operacion' => "PySOpeSoldadura_pza",
            'Operacion Equipo_2da Operacion' => "PySOpeSoldadura_pza",
            'Embudo CM' => "EmbudoCM_pza",
            'Soldadura' => "Soldadura_pza",
            'Soldadura PTA' => "SoldaduraPTA_pza",
        };
        $model = "App\Models\\" . $modelProcess;
        $piecesCount = $model::where('id_meta', $meta->id)->count();
        return $piecesCount;
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
                        echo $existingMeta = Metas::where('id_ot', $workOrder)
                            ->where('id_clase', $class->id)
                            ->where('fecha', $request->date)
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
                            $this->storeMeta($request, $class, $startTime, $endTime, $foundedMeta);
                            $this->storeMachine($request, $foundedMeta); // Se crea una nueva máquina ocupada asociada a la meta
                            $successMessage = 'Tu meta se ha editado correctamente';
                            $meta = $foundedMeta;
                        }
                        // Se retorna al reporte con el mensaje de exito
                        return redirect()->route('showReportFormat', ["meta" => $meta, "process" => $request->process, "edit" => 0])->with('success', $successMessage);
                    }
                    return redirect()->route('showReportFormat', ["meta" => $foundedMeta, "process" => $foundedMeta->proceso, "edit" => 0])->with('error', 'La máquina esta ocupada. Por favor, elija otra maquina o pida a un supervisor desbloquearla'); // Si la mquina esta ocupada retornar error con la meta antes creada
                } else {
                    $foundedMeta->fecha = $request->startDate;
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

                $foundedMeta = Metas::where('id_ot', $request->workOrder)
                    ->where('id_clase', $class->id)
                    ->where('fecha', $request->date)
                    ->where('h_inicio', $startTime)
                    ->where('h_termino', $endTime)
                    ->where('maquina', $request->machine)
                    ->first();
                if ($foundedMeta) { // Si la máquina no existe, pero ya existe una meta con los mismos datos
                    $this->storeMachine($request, $foundedMeta); // Si la máquina no existe, se crea una nueva máquina ocupada asociada a la meta
                    $meta = $foundedMeta;
                    $successMessage = 'Se ha ingresado correctamente a la meta de ' . auth()->user()->a_paterno . ' ' . auth()->user()->a_materno . ' ' . auth()->user()->nombre;

                    //VERIFICAR SI EXISTEN PIEZAS OCUPADAS ASOCIADAS A LA META**********************************


                } else { // Si la máquina no existe y tampoco una meta con esos datos, se crea una nueva meta y maquina
                    $meta = $this->storeMeta($request, $class, $startTime, $endTime);
                    $this->storeMachine($request, $meta); // Se crea una nueva máquina ocupada asociada a la meta
                    $successMessage = 'Se ha creado correctamente la meta';
                }
                return redirect()->route('showReportFormat', ["meta" => $meta, "process" => $request->process, "edit" => 0])->with('success', $successMessage);
            }
            return redirect()->route('processProduction')->with('error', 'La máquina esta ocupada. Por favor, elija otra maquina o pida a un supervisor desbloquearla');
        }
        return redirect()->route('processProduction')->with('error', 'La clase ingresada no existe.'); // Si la clase no existe, retornar error
    }

    public function storeMeta($request, $class, $startTime, $endTime, $meta = null)
    {
        // Si no se encontró la meta, se puede crear una nueva
        if (!$meta) {
            $meta = new Metas();
        }
        $meta->id_ot = strtok($request->workOrder, ' ');
        $meta->id_usuario = auth()->user()->matricula;
        $meta->fecha = $request->date;
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

    public function verifiedMachineYet($machineOccupied, $request, $class, $startTime, $endTime, $foundedMeta = null)
    {
        // Obtener la meta que está asociada a la máquina ocupada y actualizar la meta
        $machineMeta = Metas::find($machineOccupied->id_meta);

        // Obtener la fecha y hora actual
        date_default_timezone_set('America/Mexico_City'); // Establecer la zona horaria
        $currentDateTime = new DateTime();

        // Crear DateTime para la hora de inicio y término de la meta
        $startDateTime = DateTime::createFromFormat('Y-m-d H:i:s', $machineMeta->fecha . ' ' . $machineMeta->h_inicio);
        $endDateTime = DateTime::createFromFormat('Y-m-d H:i:s', $machineMeta->fecha . ' ' . $machineMeta->h_termino);

        // Verificar que se hayan parseado correctamente
        if ($startDateTime && $endDateTime) {
            // Comparar como objetos DateTime (más seguro que strings)
            if ($currentDateTime >= $startDateTime && $currentDateTime <= $endDateTime) {
                // Está dentro del rango y se retorna mensaje de maquina ocupada
                return redirect()->route('processProduction')->with('error', 'La máquina esta siendo ocupada por otro operador. Por favor, elija otra maquina.');
            } else {
                // Si la máquina está ocupada pero no está dentro del rango de horas
                if (!$foundedMeta) { // Si no se encontro la meta, se crea una nueva
                    $meta = $this->storeMeta($request, $class, $startTime, $endTime);
                    $message = 'Tu meta se ha creado correctamente.';
                } else { //Si se encontro la meta, se usa la meta encontrada
                    $meta = $foundedMeta;
                    $operator = User::where('matricula', $foundedMeta->id_usuario)->first();
                    $operator = $operator ? $operator->a_paterno . ' ' . $operator->a_materno . ' ' . $operator->nombre : 'Operador no encontrado';
                    $message = 'Se ha ingresado correctamente a la meta de ' . $operator;
                }
                $this->storeMachine($request, $meta, $machineOccupied); //Se modifica el id_meta de la maquina ocupada y se asocia a la nueva meta
                return redirect()->route('showReportFormat', ["meta" => $meta, "process" => $request->process, "edit" => 0])->with('success', $message);
            }
        }
    }

    public function calculateHrs($h_inicio, $h_termino) //Función para calcular las horas trabajadas.
    {
        // $carbon1 = Carbon::createFromFormat('H:i', $h_inicio);
        $carbon1 = Carbon::parse($h_inicio);
        $carbon2 = Carbon::parse($h_termino);
        // $carbon2 = Carbon::createFromFormat('H:i', $h_termino);

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
    }
    public function AsignarDatos_Meta($meta, $hrsTrabajadas, $ot, $reqClase, $proceso) //Función para asignar los datos de la meta.
    {
        $clase = Clase::where('id_ot', $ot->id)->where('nombre', $reqClase)->first(); //Busco la clase.
        $meta->id_clase = $clase->id;

        $tiempo = tiempoproduccion::where('id_clase', $clase->id)->where('proceso', $proceso)->first();
        $meta->t_estandar = $tiempo->tiempo ?? 0;
        $meta->meta = $this->calcularMeta($meta->t_estandar, $hrsTrabajadas) ?? 0; //Se calcula la meta.

        $meta->save();
        return $clase; //Se retorna la clase.
    }
    public function nameProcess($process)
    {
        $nameProcess = match ($process) {
            'Cepillado' => 'cepillado',
            'Desbaste Exterior' => 'desbaste',
            'Revision Laterales' => 'revLaterales',
            'Primera Operacion' => 'primeraOpeSoldadura',
            'Barreno Maniobra' => 'barrenoManiobra',
            'Segunda Operacion Soldadura' => 'segundaOpeSoldadura',
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
            'Operacion Equipo_1ra Operacion' => 'operacionEquipo',
            'Operacion Equipo_2da Operacion' => 'operacionEquipo',
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
                return "Segunda Operacion Soldadura";
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
        }
    }
}
