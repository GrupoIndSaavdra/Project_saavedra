<?php

namespace App\Http\Controllers;

use App\Models\Clase;
use App\Models\Fecha_proceso;
use App\Models\Metas;
use App\Models\Orden_trabajo;
use App\Models\Procesos;
use App\Models\tiempoproduccion;
use Carbon\Carbon;
use DateTime;
use App\Models\SystemLog;
use Illuminate\Http\Request;

class ClassController extends Controller
{
    /** @var \App\Http\Controllers\UserController */
    public $userController;

    public function __construct()
    {
        $this->userController = new UserController();
    }
        /**
     * @param mixed $workOrder
     */
    public function getClasses($workOrder)
    {
        $classes = Clase::query()->where('id_ot', '=', $workOrder->id, 'and')->get();
        return $classes;
    }
        /**
     * @param mixed $classes
     */
    public function getClassProcesses($classes)
    {
        if ($classes != null && count($classes) > 0) {
            $processes = [];
            $classIds = $classes->pluck('id')->toArray();
            $procesosCache = Procesos::query()->whereIn('id_clase', $classIds, 'and', false)->get()->keyBy('id_clase');

            foreach ($classes as $class) {
                $process = $procesosCache->get($class->id);
                if ($process) {
                    // Obtener los campos donde el valor es igual a 1
                    foreach ($process->getAttributes() as $campo => $valor) { //Se recorren los campos del registro.
                        if ($campo != "id" && $campo != "id_clase") {
                            if ($valor != 0) {
                                $processes[$class->id][$campo] = $valor;
                            }
                        }
                    }
                }
            }
            return $processes;
        }
        return null;
    }
        /**
     * @param Request $request
     */
    public function saveClass(Request $request)
    {
        if ($request->input('idClass') == null) {
            return $this->store($request);
        } else {
            return $this->edit($request->input('idClass'), $request);
        }
    }
        /**
     * @param mixed $request
     */
    public function store($request)
    {
        $composiciones = $request->input('composicion_quimica');
        if (!is_array($composiciones)) {
            $composiciones = $composiciones ? [$composiciones] : [];
        }
        $otro = $request->input('composicion_quimica_otro');
        if (!empty($otro)) {
            $otros_array = array_filter(array_map('trim', explode(',', $otro)));
            $composiciones = array_merge($composiciones, $otros_array);
        }

        if (empty($composiciones)) {
            return redirect()->back()->with('error', '¡Debe seleccionar al menos una Composición Química o escribir otra!');
        }

        $composicion = implode('/', $composiciones);

        // Verificar si la clase ya existe en esta OT
        $foundClass = Clase::query()
            ->where('id_ot', '=', $request->input('workOrder'))
            ->where('nombre', '=', $request->input('class'))
            ->first();

        if ($foundClass) {
            return redirect()->back()->with('error', '¡La clase ingresada ya existe en la orden de trabajo!');
        }

        // Almacenar los datos ingresados de la clase.
        $class = new Clase();
        $class->id_ot = $request->input('workOrder');
        $class->nombre = $request->input('class');
        $class->pedido = $request->input('order');
        $class->piezas = $request->input('pieces');
        $class->fecha_inicio = $request->input('start_date');
        $class->hora_inicio = $request->input('start_time');
        $class->tamanio = $request->input('size');
        $class->composicion_quimica = $composicion;
        $class->tipo_soldadura = $request->input('tipo_soldadura');
        $class->seccion = null;

        if ($class->nombre === null) {
            return redirect()->back()->with('error', '¡El nombre de la clase no puede estar vacío!');
        }

        $class->save();

        // Establecer los tiempos de producción
        $controllerProductionTime = new tiemposProduccionController();
        $controllerProductionTime->setProductionTimes($class);

        // Asignar los procesos a la clase
        if ($request->input('operations') != null) {
            $process = new Procesos();
            $this->storeProcess($class, $request->input('operations'), $request->input('machines'), $process);
        }

        SystemLog::create([
            'user_matricula' => auth()->user()->matricula,
            'action' => 'Cargo de Clase de OT',
            'details' => "Se registró la clase {$class->nombre} (Composición: {$composicion}) en la OT {$request->input('workOrder')} con {$class->piezas} piezas.",
            'ot' => $request->input('workOrder'),
            'clase' => $class->nombre,
            'id_ot' => $request->input('workOrder'),
        ]);

        return redirect()->route('showWO', ['workOrder' => $request->input('workOrder')])->with('success', "¡La clase se ha registrado con éxito!");
    }

        /**
     * @param int|string $idClass
     * @param mixed $request
     */
    public function edit($idClass, $request)
    {
        $class = Clase::query()->find($idClass, ['*']);
        $workOrder = Orden_trabajo::query()->find($class->id_ot, ['*']);

        if (!in_array(auth()->user()->perfil, [1, 3, 5]) && $request->input('from_almacen') != 1) {
            $class->pedido = $request->input('order');
            $class->piezas = $request->input('pieces');
            $class->fecha_inicio = $request->input('start_date');
            $class->hora_inicio = $request->input('start_time');
            $class->tamanio = $request->input('size');
            $comp = $request->input('composicion_quimica');
            if (!is_array($comp)) {
                $comp = $comp ? [$comp] : [];
            }
            $otro = $request->input('composicion_quimica_otro');
            if (!empty($otro)) {
                $otros_array = array_filter(array_map('trim', explode(',', $otro)));
                $comp = array_merge($comp, $otros_array);
            }

            if (empty($comp)) {
                return redirect()->back()->with('error', '¡Debe seleccionar al menos una Composición Química o escribir otra!');
            }

            $class->composicion_quimica = implode('/', $comp);
            $class->tipo_soldadura = $request->input('tipo_soldadura');
            $class->seccion = null;
        } else {
            $class->piezas = $request->input('pieces');
            $class->pedido = $request->input('order');
        }
        $class->save(); //Guardo los cambios.

        //Establecer los tiempos de producción
        $controllerProductionTime = new tiemposProduccionController();
        $controllerProductionTime->setProductionTimes($class);

        //Actualizar las metas que tengan relacion con la clase
        $goals = Metas::query()->where('id_clase', $class->id)->get();
        if (count($goals) > 0) {
            foreach ($goals as $goal) {
                $hrsWorked = $this->calculateHrs($goal->h_inicio, $goal->h_termino);
                $clase = $this->AsignMetaData($goal, $hrsWorked, $workOrder, $class->nombre, $goal->proceso); //Asigno los datos de la meta.
            }
        }

        //Actualizar los procesos de la clase
        $process = Procesos::query()->where('id_clase', '=', $class->id, 'and')->first();
        if ($request->input('operations') != null) { //Si se seleccionaron procesos
            if (!$process) {
                $process = new Procesos();
            }
        }
        $this->storeProcess($class, $request->input('operations'), $request->input('machines'), $process); //Verifico las casillas.
        
        SystemLog::create([
            'user_matricula' => auth()->user()->matricula,
            'action' => 'Modificación de OT',
            'details' => "Se modificó la clase {$class->nombre} en la OT {$workOrder->id}. Piezas: {$class->piezas}, Pedido: {$class->pedido}.",
            'ot' => $workOrder->id,
            'clase' => $class->nombre,
            'id_ot' => $workOrder->id,
            'id_clase' => $class->id,
        ]);

        if ($request->input('from_almacen') == 1) {
            return redirect()->back()->with("success", "¡La clase {$class->nombre} se ha editado con éxito!");
        }

        return redirect()->route('showWO', ['workOrder' => $request->input('workOrder')])->with("success", "¡La clase {$class->nombre} se ha editado con éxito!");
    }


        /**
     * @param int|string $idClass
     * @param mixed $workOrderParam
     */
    public function destroy($idClass, $workOrderParam = null)
    {
        $class = Clase::query()->find($idClass, ['*']);
        if (!$class) {
            return redirect()->back()->with("error", "La clase que intentas eliminar no existe");
        }
        $workOrder = Orden_trabajo::query()->find($class->id_ot, ['*']); //Busco la OT ingresada

        //Si existen metas asociadas a la clase no se elimina
        $text = "La clase {$class->nombre} no se puede eliminar porque ya tiene metas asociadas";
        $param = "error";
        $goals = Metas::query()->where('id_clase', $class->id)->get();
        if (count($goals) == 0) {
            $process = Procesos::query()->where('id_clase', $class->id)->first();
            //Si el proceso existe.
            if ($process) {
                $process->delete(); //Elimino el proceso de la clase

                //Eliminar las fechas de los procesos
                $process_dates = Fecha_proceso::query()->where('clase', '=', $class->id, 'and')->get();
                if (count($process_dates) > 0) {
                    foreach ($process_dates as $process_date) {
                        $process_date->delete();
                    }
                }
            }
            Clase::destroy($class->id); //Elimino la clase
            $text = "La clase {$class->nombre} se elimino exitosamente";
            $param = "success";
        }
        if ($workOrderParam == null) {
            return redirect()->route('showWO', ['workOrder' => $workOrder->id])->with($param, $text); //Redirecciono a la vista de registro de la OT
        }
    }
        /**
     * @param mixed $class
     * @param mixed $dataProcess
     * @param mixed $machines
     * @param mixed $process
     */
    public function storeProcess($class, $dataProcess, $machines, $process)
    {
        //Obtener la clase que sera registrada por su id único
        $class = Clase::query()->find($class->id);

        $processNames = [];
        //Asignar los procesos por los que pasara la clase
        switch ($class->nombre) {
            case "Bombillo":
            case "Molde":
                $processNames = array("cepillado", "desbaste_exterior", "revision_laterales", "pOperacion", "barreno_maniobra", "sOperacion", "soldadura", "soldaduraPTA", "rectificado", "asentado", "calificado", "acabadoBombillo", "acabadoMolde", "barreno_profundidad", "cavidades", "copiado", "offSet", "palomas", "rebajes", "grabado");
                break;
            case "Fondo":
            case "Obturador":
                $processNames = array("soldadura", "soldaduraPTA", "operacionEquipo"); //Asigno los procesos.
                break;
            case "Corona":
                $processNames = array("cepillado", "desbaste_exterior", "pOperacion", "sOperacion", "soldadura", "soldaduraPTA", "rectificado", "asentado", "calificado");
                break;
            case "Plato":
                $processNames = array("barreno_maniobra", "operacionEquipo");
                break;
            case "Embudo":
                $processNames = array("operacionEquipo", "embudoCM");
                break;
            case "Cabeza de Soplo":
                $processNames = array("primeraOperacionCabezaSoplo", "segundaOperacionCabezaSoplo");
                break;
            case "Candado Obturador":
                $processNames = array("operacionEquipo");
                break;
        }

        $process->id_clase = $class->id;
        if (!in_array(auth()->user()->perfil, [1, 3, 5])) {
            //Inicializar los campos de los procesos en 0
            $fields = [
                'cepillado',
                'desbaste_exterior',
                'revision_laterales',
                'pOperacion',
                'barreno_maniobra',
                'sOperacion',
                'soldadura',
                'soldaduraPTA',
                'rectificado',
                'asentado',
                'calificado',
                'acabadoBombillo',
                'acabadoMolde',
                'barreno_profundidad',
                'cavidades',
                'copiado',
                'offSet',
                'palomas',
                'rebajes',
                'grabado',
                'operacionEquipo',
                'embudoCM',
                'primeraOperacionCabezaSoplo',
                'segundaOperacionCabezaSoplo'
            ];
            foreach ($fields as $field) {
                $process->$field = 0;
            }
        }
        if ($dataProcess !== null || in_array(auth()->user()->perfil, [1, 3, 5])) {
            if (in_array(auth()->user()->perfil, [1, 3, 5])) {
                //Asignar los procesos a la clase
                $noProcess = 0;
                $processFounded = Procesos::query()->where('id_clase', '=', $class->id, 'and')->first();
                for ($i = 0; $i < count($processNames); $i++) {
                    if ($processFounded) {
                        //Crear el registro de la fecha de inicio del proceso solo si está activo
                        $string = $processNames[$i]; //Asigno el nombre del proceso.
                        if (isset($processFounded->$string) && $processFounded->$string > 0) {
                            $processDates = $this->registerProcessDates($class, $processNames, $i, $noProcess, $processFounded->$string);
                            $noProcess++;
                        }
                    }
                }
            } else {
                //Asignar los procesos a la clase
                $counterMachines = 0;
                $noProcess = 0;
                for ($i = 0; $i < count($processNames); $i++) {
                    if (in_array($processNames[$i], $dataProcess)) {
                        $string = $processNames[$i]; //Asigno el nombre del proceso.
                        //Asigno el valor de la máquina al campo correspondiente del proceso
                        $process->$string = $machines[$counterMachines];
                        $counterMachines++;

                        //Crear el registro de la fecha de inicio del proceso
                        $processDates = $this->registerProcessDates($class, $processNames, $i, $noProcess, $machines[$counterMachines - 1]);
                        $noProcess++;
                    } else {
                        $dateProcess = Fecha_proceso::query()->where('clase', '=', $class->id, 'and')->where('proceso', '=', $processNames[$i], 'and')->first();
                        if ($dateProcess) {
                            $dateProcess->delete(); //Eliminar el registro de la fecha del proceso si no se selecciono.
                        }
                    }
                }
            }
        }
        $process->save(); //Guardo los cambios.

        if (isset($processDates)) {
            //Guardar unicamente la fecha de termino
            $class->fecha_termino = Carbon::parse($processDates->fecha_fin)->format('Y-m-d');
            $class->hora_termino = Carbon::parse($processDates->fecha_fin)->format('H:i:s');
        } else {
            $class->fecha_termino = null;
            $class->hora_termino = null;
        }
        $class->save();
    }

        /**
     * @param mixed $class
     * @param mixed $processes
     * @param mixed $i
     * @param mixed $noProcess
     * @param mixed $machines
     */
    public function registerProcessDates($class, $processes, $i, $noProcess, $machines)
    {
        //Si exister un registro de la fecha de un proceso se elimina para posteriormente crear uno nuevo
        $existingProcess = Fecha_proceso::query()->where('clase', '=', $class->id, 'and')->where('proceso', '=', $processes[$i], 'and')->first();
        if ($existingProcess) {
            $existingProcess->delete();
        }

        //Crear el registro de la fecha de inicio y termino del proceso
        $newProcess = new Fecha_proceso();
        $newProcess->clase = $class->id;
        $newProcess->proceso = $processes[$i];
        $fechaInicio = $this->calculateStartDate($class, $processes, $i, $noProcess);
        $newProcess->fecha_inicio = $fechaInicio;
        $newProcess->fecha_fin = $this->calculateEndDate($class, $processes, $i, $machines, $fechaInicio, $noProcess);
        $newProcess->save();
        // echo $newProcess . "<br>";
        return $newProcess;
    }

        /**
     * @param mixed $class
     * @param mixed $processes
     * @param mixed $i
     * @param mixed $noProceso
     */
    public function calculateStartDate($class, $processes, $i, $noProceso)
    {
        $startDate = "";
        $startDate = $class->fecha_inicio . " " . $class->hora_inicio;
        $startDate = new DateTime($startDate);
        if ($noProceso != 0) {
            //Obtener el anterior proceso
            $startDate = $this->delayTime_start_end($processes, $i, $class, "start");
        }
        return $startDate;
    }

        /**
     * @param mixed $processes
     * @param mixed $i
     * @param mixed $class
     * @param mixed $phase
     */
    public function delayTime_start_end($processes, $i, $class, $phase)
    {
        //Obtener el anterior proceso
        // echo "OT" . $clase->id_ot . $clase->nombre . "<br>";
        // echo $procesos[$i] . "<br>";
        $process_counter = $this->calculatePreviousProcess($processes, $i, $class);
        $previousProcess = $process_counter[0];
        $counter = $process_counter[1];
        //Calcular los juegos por maquina y por turno
        $juegosMaqTurn = $this->pieces_machShift($i - $counter, $class);
        if ($juegosMaqTurn != 0) {
            //Si se desea calcular la fecha de inicio
            if ($phase == "start") {
                $date = new DateTime($previousProcess->fecha_inicio);
            } else { //Si se desea calcular la fecha de termmino
                $date = new DateTime($previousProcess->fecha_fin);
            }
            $dateAux = new DateTime($date->format('Y-m-d H:i:s'));
            //Se calcula cuanto tiempo se tarda en generar una pieza para calcular el tiempo de retraso entre el procesos
            $piecesProcesses = [];
            switch ($class->nombre) {
                case "Bombillo":
                    $piecesProcesses = ["cepillado", "desbaste", "revLaterales", "primeraOpeSoldadura", "barrenoManiobra", "segundaOpeSoldadura", "soldadura", "soldaduraPTA", "rectificado", "asentado", "revCalificado", "acabadoBombillo", "barrenoProfundidad", "cavidades", "copiado", "offset", "palomas", "rebajes", "grabado"];
                    break;
                case "Molde":
                    $piecesProcesses = ["cepillado", "desbaste", "revLaterales", "primeraOpeSoldadura", "barrenoManiobra", "segundaOpeSoldadura", "soldadura", "soldaduraPTA", "rectificado", "asentado", "revCalificado", "acabadoMolde", "barrenoProfundidad", "cavidades", "copiado", "offset", "palomas", "rebajes", "grabado"];
                    break;
                case "Fondo":
                case "Obturador":
                    $piecesProcesses = ["operacionEquipo", "soldadura", "soldaduraPTA"];
                    break;
                case "Corona":
                    $piecesProcesses = ["cepillado", "desbaste", "primeraOpeSoldadura", "segundaOpeSoldadura", "soldadura", "soldaduraPTA", "rectificado", "asentado", "revCalificado"];
                    break;
                case "Plato":
                    $piecesProcesses = ["barrenoManiobra", "operacionEquipo"];
                    break;
                case "Embudo":
                    $piecesProcesses = ["operacionEquipo", "embudoCM"];
                    break;
                case "Cabeza de Soplo":
                    $piecesProcesses = ["primeraOperacionCabezaSoplo", "segundaOperacionCabezaSoplo"];
                    break;
                case "Candado Obturador":
                    $piecesProcesses = ["operacionEquipo"];
                    break;
            }

            $delayTime = tiempoproduccion::query()->where('id_clase', '=', $class->id, 'and')->where('proceso', '=', $piecesProcesses[$i - $counter], 'and')->first();
            if ($delayTime) {
                //Agregar el factor de seguridad
                $safetyFactor = $delayTime->tiempo * .08;
                $safetyFactor = round($safetyFactor);
                $delayTime = $delayTime->tiempo + $safetyFactor;
            } else {
                $delayTime = 0;
            }

            $dateAux->modify("+{$delayTime} minutes");

            if ($dateAux->format('H') >= 22) {
                $date->modify("+1 days");
                $date->setTime(6, 0, 0);
                $date->modify("+{$delayTime} minutes");
            } else if ($dateAux->format('H') >= 19 && $dateAux->format('l') == "Saturday") {
                $date->modify("+2 days");
                $date->setTime(6, 0, 0);
                $date->modify("+{$delayTime} minutes");
            } else {
                $date->modify("+{$delayTime} minutes");
            }
        } else {
            //Obtener el anterior proceso
            $process_counter = $this->calculatePreviousProcess($processes, $i, $class);
            $previousProcess = $process_counter[0];
            $counter = $process_counter[1];

            $date = new DateTime($previousProcess->fecha_fin);
        }
        return $date;
    }

        /**
     * @param mixed $class
     * @param mixed $process
     * @param mixed $i
     * @param mixed $machines
     * @param mixed $date
     * @param mixed $noProcess
     */
    public function calculateEndDate($class, $process, $i, $machines, $date, $noProcess)
    {
        if ($noProcess == 0) {
            $startDate = new DateTime($date->format('Y-m-d H:i:s'));

            // echo "Proceso: " . $proceso . "<br>";
            // echo "Pedido: " . $clase->pedido . "<br>";

            //Calcular los dias que tarda en maquinar el proceso
            $diasMaq = $this->calculateMachiningDays($class, $i, $machines);

            // echo "Dias maquinar: " . $diasMaq . "<br>";

            //Convertir los dias a horas y minutos
            $time = $this->convertMachiningDaysToHours($diasMaq);
            $hours = $time[0];
            $minutes = $time[1];
            $endDate = $this->addHrsMnts($startDate, $hours, $minutes); //Sumar horas y minutos
        } else {
            //Obtener la fecha de termino con el tiempo de retraso
            $endDate = $this->delayTime_start_end($process, $i, $class, "end");
        }

        // echo "Horas: " . $horas . " Minutos: " . $minutos . "<br>";
        // echo "Fecha inicio: " . $fecha->format('l') . " " . $fecha->format('Y-m-d H:i:s') . "<br>";
        // echo "Fecha termino: " . $fecha_termino->format('l') . " " . $fecha_termino->format('Y-m-d H:i:s') . "<br>";

        return $endDate;
    }


        /**
     * @param mixed $processes
     * @param mixed $i
     * @param mixed $class
     */
    public function calculatePreviousProcess($processes, $i, $class)
    {
        $counter = 1;
        do {
            $previousProcess = Fecha_proceso::query()->where('proceso', '=', $processes[$i - $counter], 'and')->where('clase', '=', $class->id, 'and')->first();
            if ($previousProcess == null) {
                $counter++;
            }
        } while ($previousProcess == null);
        return [$previousProcess, $counter];
    }
        /**
     * @param mixed $class
     * @param mixed $i
     * @param mixed $machines
     */
    public function calculateMachiningDays($class, $i, $machines)
    {
        $piecesShift = $machines * $this->pieces_machShift($i, $class);

        $piecesDay = $piecesShift * 2;
        if ($piecesDay != 0) {
            $diasMaq = $class->pedido / $piecesDay;
            $diasMaq = floor($diasMaq * 100) / 100; //Tomar solo dos numeros despues del punto y redondearlo
        } else {
            $diasMaq = 0;
        }
        return $diasMaq;
    }
        /**
     * @param mixed $diasMaq
     */
    public function convertMachiningDaysToHours($diasMaq)
    {
        $MachiningTime = $diasMaq * 16;
        $hrsMach = (int) $MachiningTime;
        $mntsMach = round($MachiningTime - $hrsMach, 2) * 100;
        if ($mntsMach >= 60) {
            $hrsMach++;
            $mntsMach -= 60;
        }
        return [$hrsMach, $mntsMach];
    }
        /**
     * @param mixed $date
     * @param mixed $hours
     * @param mixed $minutes
     */
    public function addHrsMnts($date, $hours, $minutes)
    {
        while ($hours != 0) {
            if ($minutes >= 60) {
                $hours++;
                $minutes -= 60;
            }
            if ($date->format('H') == 21) {
                if ($date->format('i') > 0) {
                    $mntesLeft = 60 - $date->format('i');
                    $minutes += $mntesLeft;
                }
                $date->modify("+1 days");
                $hours--;
                $date->setTime(6, 0, 0);
            } else if ($date->format('H') == 18 && $date->format('l') == "Saturday") {
                if ($date->format('i') > 0) {
                    $mntesLeft = 60 - $date->format('i');
                    $minutes += $mntesLeft;
                }
                $hours--;
                $date->modify("+2 days");
                $date->setTime(6, 0, 0);
            } else {
                $hours--;
                $date->modify("+1 hours");
            }
        }
        if ($minutes > 0) {
            $date->modify("+{$minutes} minutes");
        }
        return $date;
    }
        /**
     * @param mixed $i
     * @param mixed $clase
     */
    public function pieces_machShift($i, $clase)
    {

        switch ($clase->nombre) {
            case "Bombillo":
                $procesos = ["cepillado", "desbaste", "revLaterales", "primeraOpeSoldadura", "barrenoManiobra", "segundaOpeSoldadura", "soldadura", "soldaduraPTA", "rectificado", "asentado", "revCalificado", "acabadoBombillo", "barrenoProfundidad", "cavidades", "copiado", "offSet", "palomas", "rebajes", "grabado"];
                break;
            case "Molde":
                $procesos = ["cepillado", "desbaste", "revLaterales", "primeraOpeSoldadura", "barrenoManiobra", "segundaOpeSoldadura", "soldadura", "soldaduraPTA", "rectificado", "asentado", "revCalificado", "acabadoMolde", "barrenoProfundidad", "cavidades", "copiado", "offSet", "palomas", "rebajes", "grabado"];
                break;
            case "Fondo":
            case "Obturador":
                $procesos = ["operacionEquipo", "soldadura", "soldaduraPTA"];
                break;
            case "Corona":
                $procesos = ["cepillado", "desbaste", "primeraOpeSoldadura", "segundaOpeSoldadura", "soldadura", "soldaduraPTA", "rectificado", "asentado", "revCalificado"];
                break;
            case "Plato":
                $procesos = ["barrenoManiobra", "operacionEquipo"];
                break;
            case "Embudo":
                $procesos = ["operacionEquipo", "embudoCM"];
                break;
            case "Cabeza de Soplo":
                $procesos = ["primeraOperacionCabezaSoplo", "segundaOperacionCabezaSoplo"];
                break;
            case "Candado Obturador":
                $procesos = ["operacionEquipo"];
                break;
            default:
                $procesos = [];
                break;
        }

        $juegos = 0;
        $t_estandar = tiempoproduccion::query()->where('id_clase', '=', $clase->id, 'and')->where('proceso', '=', $procesos[$i], 'and')->first();
        if ($t_estandar && $t_estandar->tiempo != 0) {
            $juegos = 420 / $t_estandar->tiempo;
            $juegos = floor($juegos * 10) / 10;
        }
        return $juegos;
    }

        /**
     * @param mixed $h_start
     * @param mixed $h_end
     */
    public function calculateHrs($h_start, $h_end) //Función para calcular las horas trabajadas.
    {
        // $carbon1 = Carbon::createFromFormat('H:i', $h_inicio);
        $carbon1 = Carbon::parse($h_start);
        $carbon2 = Carbon::parse($h_end);
        // $carbon2 = Carbon::createFromFormat('H:i', $h_termino);

        //Calcular la diferencia entre las horas en minutos
        $diference = $carbon1->diffInMinutes($carbon2) - 60; //Calculo de las horas trabajadas.
        return $diference; //Retorno las horas trabajadas.
    }

        /**
     * @param mixed $goal
     * @param mixed $hrsWorked
     * @param mixed $workOrder
     * @param mixed $className
     * @param mixed $process
     */
    public function AsignMetaData($goal, $hrsWorked, $workOrder, $className, $process) //Función para asignar los datos de la meta.
    {
        $class = null;
        if ($goal->id_clase) {
            $class = Clase::query()->find($goal->id_clase);
        }
        if (!$class) {
            $class = Clase::query()->where('id_ot', '=', $workOrder->id, 'and')->where('nombre', '=', $className, 'and')->first(); //Busco la clase.
        }
        $goal->id_clase = $class->id;

        $time = tiempoproduccion::query()->where('id_clase', '=', $class->id, 'and')->where('proceso', '=', $process, 'and')->first();
        $goal->t_estandar = $time->tiempo ?? 0;
        $goal->meta = $this->calculateGoal($goal->t_estandar, $hrsWorked) ?? 0; //Se calcula la meta.

        $goal->save();
        return $class; //Se retorna la clase.
    }
        /**
     * @param mixed $t_standard
     * @param mixed $hrsWorked
     */
    public function calculateGoal($t_standard, $hrsWorked) //Función para calcular la meta.
    {
        //Calculo de la meta.
        $time = $t_standard != 0 ? round(($hrsWorked / $t_standard)) : 0;
        return $time;
    }
}
