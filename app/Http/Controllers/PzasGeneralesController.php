<?php

namespace App\Http\Controllers;

use App\Models\AcabadoBombilo;
use App\Models\AcabadoBombilo_cnominal;
use App\Models\AcabadoBombilo_pza;
use App\Models\AcabadoBombilo_tolerancia;
use App\Models\AcabadoMolde;
use App\Models\AcabadoMolde_cnominal;
use App\Models\AcabadoMolde_pza;
use App\Models\AcabadoMolde_tolerancia;
use App\Models\Asentado;
use App\Models\Asentado_pza;
use App\Models\BarrenoManiobra;
use App\Models\BarrenoManiobra_cnominal;
use App\Models\BarrenoManiobra_pza;
use App\Models\BarrenoManiobra_tolerancia;
use App\Models\BarrenoProfundidad;
use App\Models\BarrenoProfundidad_cnominal;
use App\Models\BarrenoProfundidad_pza;
use App\Models\BarrenoProfundidad_tolerancia;
use App\Models\Cavidades;
use App\Models\Cavidades_cnominal;
use App\Models\Cavidades_pza;
use App\Models\Cavidades_tolerancia;
use App\Models\Cepillado;
use App\Models\Cepillado_cnominal;
use App\Models\Cepillado_tolerancia;
use App\Models\Clase;
use App\Models\Copiado;
use App\Models\Copiado_cnominal;
use App\Models\Copiado_pza;
use App\Models\Copiado_tolerancia;
use App\Models\Desbaste_cnominal;
use App\Models\Desbaste_pza;
use App\Models\Desbaste_tolerancia;
use App\Models\DesbasteExterior;
use App\Models\EmbudoCM;
use App\Models\EmbudoCM_cnominal;
use App\Models\EmbudoCM_pza;
use App\Models\EmbudoCM_tolerancias;
use App\Models\Metas;
use App\Models\Moldura;
use App\Models\OffSet;
use App\Models\OffSet_cnominal;
use App\Models\OffSet_pza;
use App\Models\OffSet_tolerancia;
use App\Models\Orden_trabajo;
use App\Models\Palomas;
use App\Models\Palomas_cnominal;
use App\Models\Palomas_pza;
use App\Models\Palomas_tolerancia;
use App\Models\Pieza;
use App\Models\PrimeraOpeSoldadura;
use App\Models\PrimeraOpeSoldadura_cnominal;
use App\Models\PrimeraOpeSoldadura_pza;
use App\Models\PrimeraOpeSoldadura_tolerancia;
use App\Models\Procesos;
use App\Models\PySOpeSoldadura;
use App\Models\PySOpeSoldadura_cnominal;
use App\Models\PySOpeSoldadura_pza;
use App\Models\PySOpeSoldadura_tolerancia;
use App\Models\Pza_cepillado;
use App\Models\Rebajes;
use App\Models\Rebajes_cnominal;
use App\Models\Rebajes_pza;
use App\Models\Rebajes_tolerancia;
use App\Models\Rectificado;
use App\Models\Rectificado_pza;
use App\Models\revCalificado;
use App\Models\revCalificado_cnominal;
use App\Models\revCalificado_pza;
use App\Models\revCalificado_tolerancia;
use App\Models\RevLaterales;
use App\Models\RevLaterales_cnominal;
use App\Models\RevLaterales_pza;
use App\Models\RevLaterales_tolerancia;
use App\Models\SegundaOpeSoldadura;
use App\Models\SegundaOpeSoldadura_cnominal;
use App\Models\SegundaOpeSoldadura_pza;
use App\Models\SegundaOpeSoldadura_tolerancia;
use App\Models\Soldadura;
use App\Models\Soldadura_pza;
use App\Models\SoldaduraPTA;
use App\Models\SoldaduraPTA_pza;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

//Clase para el control de las piezas generales
class PzasGeneralesController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }
    public function showPiecesReport_view()
    {
        return $this->getPiecesRequest(new Request());
    }
    public function getDataWO($workOrder, $index, $classes, &$array)
    {
        //Insertar la ot en el arreglo
        $array[$index] = array();
        $array[$index][0] = $workOrder->id;

        foreach ($classes as $indexClass => $class) {
            //Insertar la clase en el arreglo
            $array[$index][1][$indexClass] = array();
            $array[$index][1][$indexClass][0] = $class->id;
            $array[$index][1][$indexClass][1] = $class->nombre . " " . $class->tamanio;
        }
    }
    public function search($piecesData, $profile = null)
    {
        $selectedItems = array();
        $infoPieces = array();
        $filtersData = $this->getFiltersInfo();
        $pieces = $this->buscarPiezas($piecesData, $selectedItems);
        $pieces = $pieces == null ? array() : $pieces;
        $this->saveInfoPzas($infoPieces, $pieces);

        if ($piecesData["action"] != 'pdf' || $piecesData["action"] == null) {
            if ($profile == 'quality') {
                return [true, $pieces, $piecesData, $infoPieces, $selectedItems, $filtersData];
            }
            return view('pieces_views.piecesReport.adminPieces', compact('pieces', 'piecesData', 'infoPieces', 'filtersData', 'selectedItems'));
        } else {
            if ($profile == 'quality') {
                return [false, $pieces, $piecesData, $infoPieces, $selectedItems, $filtersData];
            }
            $pdf = Pdf::loadView('pieces_views.piecesReport.pdf', compact('pieces', 'piecesData', 'infoPieces', 'filtersData', 'selectedItems'));
            return $pdf->download('Reporte de piezas.pdf');
        }
    }
    public function getFiltersInfo()
    {
        $filtersData = array(
            "workOrder" => $this->objectToArrayFromDB(Orden_trabajo::all(), "workOrder"),
            "class" => ["Bombillo", "Molde", "Obturador", "Fondo", "Corona", "Plato", "Embudo"],
            "operator" => $this->objectToArrayFromDB(User::all(), "operator"),
            "machine" => [1, 2, 3, 4, 5, 6, 7],
            "process" => ["Cepillado", "Desbaste Exterior", "Revision Laterales", "Primera Operacion", "Barreno Maniobra", "Segunda Operacion", "Soldadura", "Soldadura PTA", "Rectificado", "Asentado", "Calificado", "Acabado Bombillo", "Acabado Molde", "Barreno Profundidad", "Cavidades", "Copiado", "Off Set", "Palomas", "Rebajes", "Operacion Equipo_1 operacion", "Operacion Equipo_2 operacion", "Embudo CM"],
            "error" => ["Ninguno", "Maquinado", "Fundicion"],
        );
        return $filtersData;
    }
    public function objectToArrayFromDB($object, $param)
    {
        $array = array();
        foreach ($object as $item) {
            if (($param == "operator" && $item->perfil == 2)) {
                array_push($array, $item);
            } else if ($param == "workOrder") {
                $molding = Moldura::where('id', $item->id_moldura)->first();
                $text = $item->id . " - " . $molding->nombre;
                array_push($array, $text);
            }
        }
        return $array;
    }
    public function getPiecesRequest(Request $request)
    {
        $datosPiezas = array(
            "workOrder" => $request->workOrder,
            "class" => $request->class,
            "operator" => $request->operator,
            "machine" => $request->machine,
            "process" => $request->process,
            "error" => $request->error,
            "dateFrom" => $request->dateFrom,
            "dateTo" => $request->dateTo,
            "action" => $request->input("action"),
        );
        return $this->search($datosPiezas, 'admin');
    }
    public function buscarPiezas($piecesData, &$itemElegidos)
    {
        //Busca las piezas que coincidan con los parametros de búsqueda
        $array = Pieza::all();
        $array = $this->saveInArray($array);
        if (count($array) > 0) {
            $positionsArray = array("workOrder" => 0, "class" => "className",  "operator" => 2, "machine" => 3, "process" => 4, "error" => 5, "dateFrom" => 6, "dateTo" => 6);
            foreach ($piecesData as $key => $value) {
                $dateField = false;
                if ($key != "action") {
                    if ($value !== "Todos" && isset($piecesData[$key])) {
                        $itemElegidos[$key] = $piecesData[$key];
                        if ($key == "operator") {
                            $itemElegidos[$key] = User::where('matricula', $piecesData[$key])->first();
                            $piecesData[$key] = User::where('matricula', $piecesData[$key])->first()->nombre . " " . User::where('matricula', $piecesData[$key])->first()->a_paterno . " " . User::where('matricula', $piecesData[$key])->first()->a_materno;
                        } else if ($key == "workOrder") {
                            $workOrderId = explode(" - ", $piecesData[$key])[0];
                            $workOrder = Orden_trabajo::find($workOrderId);
                            $molding = Moldura::where('id', $workOrder->id_moldura)->first();
                            $itemElegidos[$key] = $workOrder->id . " - " . $molding->nombre;
                            $piecesData[$key] = $workOrder->id;
                        }
                        if (($key == "dateFrom" || $key == "dateTo") && !$dateField) {
                            $dateField = true;
                            $dateFrom = $piecesData["dateFrom"] ? $piecesData["dateFrom"] . " 00:00:00" : null;
                            $dateTo = $piecesData["dateTo"] ? $piecesData["dateTo"] . " 23:59:59" : null;
                            $array = $this->buscarElemento($array, $positionsArray[$key], [$dateFrom, $dateTo]);
                        } else {
                            $array = $this->buscarElemento($array, $positionsArray[$key], $piecesData[$key]);
                        }
                    } else {
                        $itemElegidos[$key] = "Todos";
                    }
                }
            }
            return $array;
        }
    }
    //Obtener los procesos por los que pasa una clase
    public function procesosClase($clase)
    {
        $procesos = array();
        $procesosClase = Procesos::where('id_clase', $clase->id)->first();
        $procesosClase = $procesosClase->toArray();
        $campos = array_filter($procesosClase, function ($valor) {
            return $valor != 0;
        });
        $procesos = array();
        foreach (array_keys($campos) as $nombreCampo) {
            array_push($procesos, $this->nombreProceso($nombreCampo));
        }
        array_splice($procesos, 0, 2);
        return $procesos;
    }
    public function buscarElemento($arrayP, $posicion, $elemento)
    {
        //Busca un elemento en un arreglo de arreglos y regresa un arreglo con los arreglos que contienen el elemento
        $array = array();
        for ($i = 0; $i < count($arrayP); $i++) {
            $elementoArray = $arrayP[$i][$posicion];
            if (is_numeric($arrayP[$i][$posicion]) && $posicion == 5) {
                $elementoArray = $arrayP[$i][$posicion + 1];
            }

            switch (true) {
                case is_array($elemento): // Si se estan filtrando por fechas
                    $fechaDesde = $elemento[0];
                    $fechaHasta = $elemento[1];

                    $cumpleDesde = is_null($fechaDesde) || $elementoArray >= $fechaDesde;
                    $cumpleHasta = is_null($fechaHasta) || $elementoArray <= $fechaHasta;

                    if ($cumpleDesde && $cumpleHasta) {
                        if ($elemento == "Soldadura") {
                            if ($arrayP[$i][$posicion] === $elemento) {
                                array_push($array, $arrayP[$i]);
                            }
                        } else {
                            array_push($array, $arrayP[$i]);
                        }
                    }
                    break;
                case strpos($elementoArray, $elemento) !== false: // Si el campo de la pieza coincide con el elemento filtrado
                    if ($elemento == "Soldadura") {
                        if ($arrayP[$i][$posicion] === $elemento) {
                            array_push($array, $arrayP[$i]);
                        }
                    } else {
                        array_push($array, $arrayP[$i]);
                    }
                    break;
            }
        }
        return $array;
    }
    public function saveInArray($arrayP)
    {
        //Obtener las clases ya finalizadas
        $finishedClasess = Clase::where('finalizada', '!=', 0)->get();
        $arrayFClasses = array();
        foreach ($finishedClasess as $finishedClass) {
            array_push($arrayFClasses, $finishedClass->id);
        }
        
        $array = array();
        $juegosGuardados = array();
        $contador = 0;
        $mitad = false;
        foreach ($arrayP as $item) {
            if(in_array($item->id_clase, $arrayFClasses)){
                continue;
            }
            $band = false;
            //Identificar si la pieza es mitad o juego
            if (substr($item->n_pieza, -1) != "J") { //Si la pieza es mitad
                $mitad = true;
                //Si la pieza es mitad, buscar si ya se guardo el juego
                $numJuego = $this->getPiezaNumber($item->n_pieza);
                if (!in_array($numJuego . "J" . "_" . $item->proceso . "_" . $item->id_clase . "_" . $item->id_ot, $juegosGuardados)) {
                    $band = true;
                    //Guardar el numero de juego
                    $array[$contador][1] = $numJuego . "J";
                    array_push($juegosGuardados, $array[$contador][1] . "_" . $item->proceso . "_" . $item->id_clase . "_" . $item->id_ot);

                    //Buscar las mitades del juego para los demás datos
                    $pzas[0] = Pieza::where('id_clase', $item->id_clase)->where('proceso', $item->proceso)->where('n_pieza', $numJuego . 'H')->first();
                    $pzas[1] = Pieza::where('id_clase', $item->id_clase)->where('proceso', $item->proceso)->where('n_pieza', $numJuego . 'M')->first();

                    if (!$pzas[0] || !$pzas[1]) { //Si no existe la mitad M
                        if (!$pzas[0]) {
                            //Guardar operador
                            $array[$contador][2] = $this->getNameOperador($pzas[1]->id_operador);

                            //Identificar error
                            $error = "";
                            if ($pzas[1]->error != "Ninguno") {
                                $error = $pzas[1]->error . " / Incompleto";
                            } else {
                                $error = "Incompleto";
                            }
                        } else {
                            //Guardar operador
                            $array[$contador][2] = $this->getNameOperador($pzas[0]->id_operador);

                            //Identificar error
                            $error = "";
                            if ($pzas[0]->error != "Ninguno") {
                                $error = $pzas[0]->error . " / Incompleto";
                            } else {
                                $error = "Incompleto";
                            }
                        }

                        //Guardar el error
                        if ($item->proceso == "Operacion Equipo_1" || $item->proceso == "Operacion Equipo_2") {
                            $array[$contador][6] = $error;
                        } else {
                            $array[$contador][5] = $error;
                        }
                    } else {
                        //Guardar operadores u operador
                        if ($pzas[0]->id_operador == $pzas[1]->id_operador) {
                            $array[$contador][2] = $this->getNameOperador($pzas[0]->id_operador);
                        } else {
                            $array[$contador][2] = $this->getNameOperador($pzas[0]->id_operador) . " / " . $this->getNameOperador($pzas[1]->id_operador);
                        }

                        //Guardar el error o errores
                        if ($pzas[0]->error == $pzas[1]->error) {
                            if ($pzas[0]->proceso = "Desbaste Exterior") {
                                $pzas[0]->error . "_" . $pzas[0]->error;
                            }
                            if ($item->proceso == "Operacion Equipo_1" || $item->proceso == "Operacion Equipo_2") {
                                $array[$contador][6] = $pzas[0]->error;
                            } else {
                                $pzas[0]->error . $pzas[0]->n_pieza;
                                $array[$contador][5] = $pzas[0]->error;
                            }
                        } else {
                            if ($item->proceso == "Operacion Equipo_1" || $item->proceso == "Operacion Equipo_2") {
                                $array[$contador][6] = $pzas[0]->error . " / " . $pzas[1]->error;
                            } else {
                                $array[$contador][5] = $pzas[0]->error . " / " . $pzas[1]->error;
                            }
                        }
                    }
                }
            } else { //Si la pieza es juego
                $band = true;
                $mitad = false;
                $array[$contador][1] = $item->n_pieza;
                $array[$contador][2] = $this->getNameOperador($item->id_operador);
                $array[$contador][5] = $item->error;
            }

            //Almacenar los demas datos
            $user_liberacion = User::where('matricula', $item->user_liberacion)->first();
            if ($band) {
                $array[$contador][0] = $item->id_ot;
                $array[$contador][3] = $item->maquina;
                $array[$contador]["id_clase"] = $item->id_clase;
                $className = Clase::find($item->id_clase);
                $array[$contador]["className"] = $className ? $className->nombre : null;

                //Obtener las observaciones  de la pieza
                $array[$contador]["observations"] = "";
                $controller = new ProcessProductionController();
                $id_process = str_replace(" ", "_", $item->proceso) . "_" . $className->nombre . "_" . $item->id_ot;
                $processString = str_contains($item->proceso, "Operacion Equipo") ? "Operacion Equipo" : $item->proceso;
                $id_procesDB = $controller->get_ModelProcess($processString)::where('id_proceso', $id_process)->first()->id;
                $pieces = $controller->get_ModelProcessPieces($processString)::where('id_proceso', $id_procesDB)->where('n_juego', $numJuego . "J")->get();
                foreach ($pieces as $piece) {
                    if ($piece == $pieces->last() && $array[$contador]["observations"] != "" && $piece->observaciones != "") {
                        $array[$contador]["observations"] .= " / ";
                    }
                    $array[$contador]["observations"] .= $piece->observaciones;
                }

                $array[$contador][4] = $item->proceso;
                $date = new \DateTime($item->created_at);
                $array[$contador][6] = $date->format('Y-m-d H:i:s');
                if ($item->fecha_liberacion != null) {
                    $array[$contador][7] = $item->fecha_liberacion;
                } else {
                    $array[$contador][7] = "No liberado";
                }
                if ($user_liberacion) {
                    $array[$contador][8] = $user_liberacion->nombre . " " . $user_liberacion->a_paterno . " " . $user_liberacion->a_materno;
                } else {
                    $array[$contador][8] = null;
                }

                //Almacenar valor de liberacion
                array_push($array[$contador], $item->liberacion);
                //Almacenar valor si la pieza es juego o no
                if ($mitad == true) {
                    array_push($array[$contador], "mitad");
                } else {
                    array_push($array[$contador], "juego");
                }
                $contador++;
            }
        }
        return $array;
    }
    public function saveInfoPzas(&$infoPiezas, $piezas)
    {
        $contador = 0;
        if ($piezas == null || count($piezas) == 0) {
            return;
        }
        foreach ($piezas as $pieza) {
            //Buscar la clase de la pieza
            $clase = Clase::find($pieza["id_clase"]);
            $clase = $clase->nombre;

            $clase = $clase == null ?: $clase;
            switch ($pieza[4]) {
                case 'Cepillado':
                    $id_proceso = 'Cepillado_' . $clase . "_" . $pieza[0];
                    $id_proceso = Cepillado::where('id_proceso', $id_proceso)->first();
                    $infoPiezas[$contador][1] = 'Cepillado';
                    break;
                case 'Desbaste Exterior':
                    $id_proceso = 'Desbaste_Exterior_' . $clase . "_" . $pieza[0];
                    $id_proceso = DesbasteExterior::where('id_proceso', $id_proceso)->first();
                    $infoPiezas[$contador][1] = 'Desbaste Exterior';
                    break;
                case 'Revision Laterales':
                    $id_proceso = 'Revision_Laterales_' . $clase . "_" . $pieza[0];
                    $id_proceso = RevLaterales::where('id_proceso', $id_proceso)->first();
                    $infoPiezas[$contador][1] = 'Revision Laterales';
                    break;
                case 'Primera Operacion':
                    $id_proceso = 'Primera_Operacion_' . $clase . "_" . $pieza[0];
                    $id_proceso = PrimeraOpeSoldadura::where('id_proceso', $id_proceso)->first();
                    $infoPiezas[$contador][1] = 'Primera Operacion';
                    break;
                case 'Barreno Maniobra':
                    $id_proceso = 'Barreno_Maniobra_' . $clase . "_" . $pieza[0];
                    $id_proceso = BarrenoManiobra::where('id_proceso', $id_proceso)->first();
                    $infoPiezas[$contador][1] = 'Barreno Maniobra';
                    break;
                case 'Segunda Operacion':
                    $id_proceso = 'Segunda_Operacion_' . $clase . "_" . $pieza[0];
                    $id_proceso = SegundaOpeSoldadura::where('id_proceso', $id_proceso)->first();
                    $infoPiezas[$contador][1] = 'Segunda Operacion';
                    break;
                case 'Soldadura':
                    $id_proceso = 'Soldadura_' . $clase . "_" . $pieza[0];
                    $id_proceso = Soldadura::where('id_proceso', $id_proceso)->first();
                    $infoPiezas[$contador][1] = 'Soldadura';
                    break;
                case 'Soldadura PTA':
                    $id_proceso = 'Soldadura_PTA_' . $clase . "_" . $pieza[0];
                    $id_proceso = SoldaduraPTA::where('id_proceso', $id_proceso)->first();
                    $infoPiezas[$contador][1] = 'Soldadura PTA';
                    break;
                case 'Rectificado':
                    $id_proceso = 'Rectificado_' . $clase . "_" . $pieza[0];
                    $id_proceso = Rectificado::where('id_proceso', $id_proceso)->first();
                    $infoPiezas[$contador][1] = 'Rectificado';
                    break;
                case 'Asentado':
                    $id_proceso = 'Asentado_' . $clase . "_" . $pieza[0];
                    $id_proceso = Asentado::where('id_proceso', $id_proceso)->first();
                    $infoPiezas[$contador][1] = 'Asentado';
                    break;
                case 'Calificado':
                    $id_proceso = 'Calificado_' . $clase . "_" . $pieza[0];
                    $id_proceso = revCalificado::where('id_proceso', $id_proceso)->first();
                    $infoPiezas[$contador][1] = 'Calificado';
                    break;
                case 'Acabado Bombillo':
                    $id_proceso = 'Acabado_Bombillo_' . $clase . "_" . $pieza[0];
                    $id_proceso = AcabadoBombilo::where('id_proceso', $id_proceso)->first();
                    $infoPiezas[$contador][1] = 'Acabado Bombillo';
                    break;
                case 'Acabado Molde':
                    $id_proceso = 'Acabado_Molde_' . $clase . "_" . $pieza[0];
                    $id_proceso = AcabadoMolde::where('id_proceso', $id_proceso)->first();
                    $infoPiezas[$contador][1] = 'Acabado Molde';
                    break;
                case 'Barreno Profundidad':
                    $id_proceso = 'Barreno_Profundidad_' . $clase . "_" . $pieza[0];
                    $id_proceso = BarrenoProfundidad::where('id_proceso', $id_proceso)->first();
                    $infoPiezas[$contador][1] = 'Barreno Profundidad';
                    break;
                case 'Cavidades':
                    $id_proceso = 'Cavidades_' . $clase . "_" . $pieza[0];
                    $id_proceso = Cavidades::where('id_proceso', $id_proceso)->first();
                    $infoPiezas[$contador][1] = 'Cavidades';
                    break;
                case 'Copiado':
                    $id_proceso = 'Copiado_' . $clase . "_" . $pieza[0];
                    $id_proceso = Copiado::where('id_proceso', $id_proceso)->first();
                    $infoPiezas[$contador][1] = 'Copiado';
                    break;
                case 'Off Set':
                    $id_proceso = 'Off_Set_' . $clase . "_" . $pieza[0];
                    $id_proceso = OffSet::where('id_proceso', $id_proceso)->first();
                    $infoPiezas[$contador][1] = 'Off Set';
                    break;
                case 'Palomas':
                    $id_proceso = 'Palomas_' . $clase . "_" . $pieza[0];
                    $id_proceso = Palomas::where('id_proceso', $id_proceso)->first();
                    $infoPiezas[$contador][1] = 'Palomas';
                    break;
                case 'Rebajes':
                    $id_proceso = 'Rebajes_' . $clase . "_" . $pieza[0];
                    $id_proceso = Rebajes::where('id_proceso', $id_proceso)->first();
                    $infoPiezas[$contador][1] = 'Rebajes';
                    break;
                case 'Operacion Equipo_1 operacion':
                    $id_proceso = 'Operacion_Equipo_1_operacion' . "_" . $clase . "_" . $pieza[0];
                    $id_proceso = PySOpeSoldadura::where('id_proceso', $id_proceso)->first();
                    $infoPiezas[$contador][1] = "Operacion Equipo_1 operacion";
                    break;
                case 'Operacion Equipo_2 operacion':
                    $id_proceso = 'Operacion_Equipo_2_operacion' . "_" . $clase . "_" . $pieza[0];
                    $id_proceso = PySOpeSoldadura::where('id_proceso', $id_proceso)->first();
                    $infoPiezas[$contador][1] = "Operacion Equipo_2 operacion";
                    break;
                case 'Embudo CM':
                    $id_proceso = 'Embudo_CM_' . $clase . "_" . $pieza[0];
                    $id_proceso = EmbudoCM::where('id_proceso', $id_proceso)->first();
                    $infoPiezas[$contador][1] = 'Embudo CM';
                    break;
            }
            if (end($pieza) == "mitad") {
                //Guardar el numero de pieza
                $numero = $this->getPiezaNumber($pieza[1]);
                $infoPiezas[$contador][0][0] = $numero . "H" . $id_proceso->id;
                $infoPiezas[$contador][0][1] = $numero . "M" . $id_proceso->id;
            } else {
                $infoPiezas[$contador][0][0] = $pieza[1] . $id_proceso->id;
            }

            //Guardar el error
            if (count($pieza) > 11) {
                $infoPiezas[$contador][2] = $pieza[6];
            } else {
                $infoPiezas[$contador][2] = $pieza[5];
            }
            $contador++;
        }
    }

    public function showPiece($pieces, $process, $profile)
    {
        switch ($process) {
            case 'Cepillado':
                $pieceInfo = array();
                $pieces = explode(",", $pieces);
                foreach ($pieces as $piece) {
                    array_push($pieceInfo, Pza_cepillado::where('id_pza', $piece)->first());
                }
                //Obtener Cotas nominales y tolerancias
                $id_process = Cepillado::find($pieceInfo[0]->id_proceso);
                $cNominal = Cepillado_cnominal::where('id_proceso', $id_process->id_proceso)->first()->toArray();
                $tolerance = Cepillado_tolerancia::where('id_proceso', $id_process->id_proceso)->first()->toArray();
                $process = 'Cepillado';
                break;
            case 'Desbaste Exterior':
                //Obtener informacion de la pieza elegida
                $pieceInfo = array();
                $pieces = explode(",", $pieces);
                foreach ($pieces as $piece) {
                    array_push($pieceInfo, Desbaste_pza::where('id_pza', $piece)->first());
                }
                //Obtener Cotas nominales y tolerancias
                $id_process = DesbasteExterior::find($pieceInfo[0]->id_proceso);
                // $cNominal = Desbaste_cnominal::where('id_proceso', $id_process->id_proceso)->first()->toArray();
                $id_process->id_proceso;
                $cNominal = Desbaste_cnominal::where('id_proceso', $id_process->id_proceso)->first();
                $tolerance = Desbaste_tolerancia::where('id_proceso', $id_process->id_proceso)->first()->toArray();
                $process = 'Desbaste Exterior';
                break;
            case 'Revision Laterales':
                //Obtener informacion de la pieza elegida
                $pieceInfo = array();
                $pieces = explode(",", $pieces);
                foreach ($pieces as $piece) {
                    array_push($pieceInfo, RevLaterales_pza::where('id_pza', $piece)->first());
                }
                //Obtener Cotas nominales y tolerancias
                $id_process = RevLaterales::find($pieceInfo[0]->id_proceso);
                $cNominal = RevLaterales_cnominal::where('id_proceso', $id_process->id_proceso)->first()->toArray();
                $tolerance = RevLaterales_tolerancia::where('id_proceso', $id_process->id_proceso)->first()->toArray();
                $process = 'Revision Laterales';
                break;
            case 'Primera Operacion':
                //Obtener informacion de la pieza elegida
                $pieceInfo = array();
                $pieces = explode(",", $pieces);
                foreach ($pieces as $piece) {
                    array_push($pieceInfo, PrimeraOpeSoldadura_pza::where('id_pza', $piece)->first());
                }
                //Obtener Cotas nominales y tolerancias
                $id_process = PrimeraOpeSoldadura::find($pieceInfo[0]->id_proceso);
                $cNominal = PrimeraOpeSoldadura_cnominal::where('id_proceso', $id_process->id_proceso)->first()->toArray();
                $tolerance = PrimeraOpeSoldadura_tolerancia::where('id_proceso', $id_process->id_proceso)->first()->toArray();
                $process = 'Primera Operacion';
                break;
            case 'Barreno Maniobra':
                //Obtener informacion de la pieza elegida
                $pieceInfo = array();
                $pieces = explode(",", $pieces);
                foreach ($pieces as $piece) {
                    array_push($pieceInfo, BarrenoManiobra_pza::where('id_pza', $piece)->first());
                }
                //Obtener Cotas nominales y tolerancias
                $id_process = BarrenoManiobra::find($pieceInfo[0]->id_proceso);
                $cNominal = BarrenoManiobra_cnominal::where('id_proceso', $id_process->id_proceso)->first()->toArray();
                $tolerance = BarrenoManiobra_tolerancia::where('id_proceso', $id_process->id_proceso)->first()->toArray();
                $process = 'Barreno Maniobra';
                break;
            case 'Segunda Operacion':
                //Obtener informacion de la pieza elegida
                $pieceInfo = array();
                $pieces = explode(",", $pieces);
                foreach ($pieces as $piece) {
                    array_push($pieceInfo, SegundaOpeSoldadura_pza::where('id_pza', $piece)->first());
                }
                //Obtener Cotas nominales y tolerancias
                $id_process = SegundaOpeSoldadura::find($pieceInfo[0]->id_proceso);
                $cNominal = SegundaOpeSoldadura_cnominal::where('id_proceso', $id_process->id_proceso)->first()->toArray();
                $tolerance = SegundaOpeSoldadura_tolerancia::where('id_proceso', $id_process->id_proceso)->first()->toArray();
                $process = 'Segunda Operacion';
                break;
            case 'Soldadura':
                //Obtener informacion de la pieza elegida
                $pieceInfo = Soldadura_pza::where('id_pza', $pieces)->first();
                //Obtener Cotas nominales y tolerancias
                $id_process = Soldadura::find($pieceInfo->id_proceso);
                $cNominal = 0;
                $tolerance = 0;
                $process = 'Soldadura';
                break;
            case 'Soldadura PTA':
                //Obtener informacion de la pieza elegida
                $pieceInfo = SoldaduraPTA_pza::where('id_pza', $pieces)->first();
                //Obtener Cotas nominales y tolerancias
                $id_process = SoldaduraPTA::find($pieceInfo->id_proceso);
                $cNominal = 0;
                $tolerance = 0;
                $process = 'Soldadura PTA';
                break;
            case 'Rectificado':
                //Obtener informacion de la pieza elegida
                $pieceInfo = Rectificado_pza::where('id_pza', $pieces)->first();
                //Obtener Cotas nominales y tolerancias
                $id_process = Rectificado::find($pieceInfo->id_proceso);
                $cNominal = 0;
                $tolerance = 0;
                $process = 'Rectificado';
                break;
            case 'Asentado':
                //Obtener informacion de la pieza elegida
                $pieceInfo = Asentado_pza::where('id_pza', $pieces)->first();
                //Obtener Cotas nominales y tolerancias
                $id_process = Asentado::find($pieceInfo->id_proceso);
                $cNominal = 0;
                $tolerance = 0;
                $process = 'Asentado';
                break;

            case 'Calificado':
                //Obtener informacion de la pieza elegida
                $pieceInfo = array();
                $pieces = explode(",", $pieces);
                foreach ($pieces as $piece) {
                    array_push($pieceInfo, revCalificado_pza::where('id_pza', $piece)->first());
                }
                //Obtener Cotas nominales y tolerancias
                $id_process = revCalificado::find($pieceInfo[0]->id_proceso);
                $cNominal = revCalificado_cnominal::where('id_proceso', $id_process->id_proceso)->first()->toArray();
                $tolerance = revCalificado_tolerancia::where('id_proceso', $id_process->id_proceso)->first()->toArray();
                $process = 'Calificado';
                break;
            case 'Acabado Bombillo':
                //Obtener informacion de la pieza elegida
                $pieceInfo = array();
                $pieces = explode(",", $pieces);
                foreach ($pieces as $piece) {
                    array_push($pieceInfo, AcabadoBombilo_pza::where('id_pza', $piece)->first());
                }
                //Obtener Cotas nominales y tolerancias
                $id_process = AcabadoBombilo::find($pieceInfo[0]->id_proceso);
                $cNominal = AcabadoBombilo_cnominal::where('id_proceso', $id_process->id_proceso)->first()->toArray();
                $tolerance = AcabadoBombilo_tolerancia::where('id_proceso', $id_process->id_proceso)->first()->toArray();
                $process = 'Acabado Bombillo';
                break;
            case 'Acabado Molde':
                //Obtener informacion de la pieza elegida
                $pieceInfo = array();
                $pieces = explode(",", $pieces);
                foreach ($pieces as $piece) {
                    array_push($pieceInfo, AcabadoMolde_pza::where('id_pza', $piece)->first());
                }
                //Obtener Cotas nominales y tolerancias
                $id_process = AcabadoMolde::find($pieceInfo[0]->id_proceso);
                $cNominal = AcabadoMolde_cnominal::where('id_proceso', $id_process->id_proceso)->first()->toArray();
                $tolerance = AcabadoMolde_tolerancia::where('id_proceso', $id_process->id_proceso)->first()->toArray();
                $process = 'Acabado Molde';
                break;
            case 'Barreno Profundidad':
                //Obtener informacion de la pieza elegida
                $pieceInfo = BarrenoProfundidad_pza::where('id_pza', $pieces)->first();
                //Obtener Cotas nominales y tolerancias
                $id_process = BarrenoProfundidad::find($pieceInfo->id_proceso);
                $cNominal = BarrenoProfundidad_cnominal::where('id_proceso', $id_process->id_proceso)->first()->toArray();
                $tolerance = BarrenoProfundidad_tolerancia::where('id_proceso', $id_process->id_proceso)->first()->toArray();
                $process = 'Barreno Profundidad';
                break;
            case 'Cavidades':
                //Obtener informacion de la pieza elegida
                $pieceInfo = array();
                $pieces = explode(",", $pieces);
                foreach ($pieces as $piece) {
                    array_push($pieceInfo, Cavidades_pza::where('id_pza', $piece)->first());
                }
                //Obtener Cotas nominales y tolerancias
                $id_process = Cavidades::find($pieceInfo[0]->id_proceso);
                $cNominal = Cavidades_cnominal::where('id_proceso', $id_process->id_proceso)->first();
                $tolerance = Cavidades_tolerancia::where('id_proceso', $id_process->id_proceso)->first();
                $proceso = 'Cavidades';
                break;
            case 'Copiado':
                //Obtener informacion de la pieza elegida
                $pieceInfo = array();
                $pieces = explode(",", $pieces);
                foreach ($pieces as $piece) {
                    array_push($pieceInfo, Copiado_pza::where('id_pza', $piece)->first());
                }
                //Obtener Cotas nominales y tolerancias
                $id_process = Copiado::find($pieceInfo[0]->id_proceso);
                $cNominal = Copiado_cnominal::where('id_proceso', $id_process->id_proceso)->first();
                $tolerance = Copiado_tolerancia::where('id_proceso', $id_process->id_proceso)->first();
                $process = 'Copiado';
                break;
            case 'Off Set':
                //Obtener informacion de la pieza elegida
                $pieceInfo = array();
                $pieces = explode(",", $pieces);
                foreach ($pieces as $piece) {
                    array_push($pieceInfo, OffSet_pza::where('id_pza', $piece)->first());
                }
                //Obtener Cotas nominales y tolerancias
                $id_process = OffSet::find($pieceInfo[0]->id_proceso);
                $cNominal = OffSet_cnominal::where('id_proceso', $id_process->id_proceso)->first();
                $tolerance = OffSet_tolerancia::where('id_proceso', $id_process->id_proceso)->first();
                $process = 'Off Set';
                break;
            case 'Palomas':
                //Obtener informacion de la pieza elegida
                $pieceInfo = Palomas_pza::where('id_pza', $pieces)->first();
                //Obtener Cotas nominales y tolerancias
                $id_process = Palomas::find($pieceInfo->id_proceso);
                $cNominal = Palomas_cnominal::where('id_proceso', $id_process->id_proceso)->first()->toArray();
                $tolerance = Palomas_tolerancia::where('id_proceso', $id_process->id_proceso)->first()->toArray();
                $process = 'Palomas';
                break;
            case 'Rebajes':
                //Obtener informacion de la pieza elegida
                $pieceInfo = Rebajes_pza::where('id_pza', $pieces)->first();
                //Obtener Cotas nominales y tolerancias
                $id_process = Rebajes::find($pieceInfo->id_proceso);
                $cNominal = Rebajes_cnominal::where('id_proceso', $id_process->id_proceso)->first()->toArray();
                $tolerance = Rebajes_tolerancia::where('id_proceso', $id_process->id_proceso)->first()->toArray();
                $process = 'Rebajes';
                break;
            case 'Operacion Equipo_1 operacion':
                //Obtener informacion del juego elegido
                $pieceInfo = array();
                $piece = explode(",", $pieces);
                foreach ($piece as $pza) {
                    array_push($pieceInfo, PySOpeSoldadura_pza::where('id_pza', $pza)->first());
                }
                //Obtener Cotas nominales y tolerancias
                $id_process = PySOpeSoldadura::find($pieceInfo[0]->id_proceso);
                $cNominal = PySOpeSoldadura_cnominal::where('id_proceso', $id_process->id_proceso)->first()->toArray();
                $tolerance = PySOpeSoldadura_tolerancia::where('id_proceso', $id_process->id_proceso)->first()->toArray();
                $process = 'Operacion Equipo_1 operacion';
                break;
            case 'Operacion Equipo_2 operacion':
                //Obtener informacion del juego elegido
                $pieceInfo = array();
                $piece = explode(",", $pieces);
                foreach ($piece as $pza) {
                    array_push($pieceInfo, PySOpeSoldadura_pza::where('id_pza', $pza)->first());
                }
                //Obtener Cotas nominales y tolerancias
                $id_process = PySOpeSoldadura::find($pieceInfo[0]->id_proceso);
                $cNominal = PySOpeSoldadura_cnominal::where('id_proceso', $id_process->id_proceso)->first()->toArray();
                $tolerance = PySOpeSoldadura_tolerancia::where('id_proceso', $id_process->id_proceso)->first()->toArray();
                $process = 'Operacion Equipo_2 operacion';
                break;
            case 'Embudo CM':
                //Obtener informacion de la pieza elegida
                $pieceInfo = EmbudoCM_pza::where('id_pza', $pieces)->first();
                //Obtener Cotas nominales y tolerancias
                $id_process = EmbudoCM::find($pieceInfo->id_proceso);
                $cNominal = EmbudoCM_cnominal::where('id_proceso', $id_process->id_proceso)->first()->toArray();
                $tolerance = EmbudoCM_tolerancias::where('id_proceso', $id_process->id_proceso)->first()->toArray();
                $process = 'Embudo CM';
                break;
        }
        // Obtener meta para obtener la ot y la clase
        if (is_array($pieceInfo)) { //Si el juego es mitad
            $meta = Metas::find($pieceInfo[0]->id_meta);
        } else { //Si no es mitad
            $meta = Metas::find($pieceInfo->id_meta);
        }
        $ot = $meta->id_ot;
        $clase = Clase::find($meta->id_clase);
        $clase = $clase->nombre . " " . $clase->tamanio;
        if ($process != 'Asentado') {
            $piecesInfo = array();
            //Si el juego es mitad
            if (is_array($pieceInfo)) {
                $contador = 0;
                foreach ($pieceInfo as $pza) {
                    $piecesInfo[$contador] = $pza->toArray();
                    $contador++;
                }
            } else { //Si no es mitad
                $piecesInfo[0] = $pieceInfo->toArray();
            }
        } else {
            $piecesInfo = $pieceInfo;
        }
        $profile = $profile;

        //Obtener el nombre del operador
        $operadores = array();
        if (is_array($piecesInfo)) {
            $contador = 0;
            foreach ($piecesInfo as $pza) {
                //Obtener la meta para obtener el id del operador
                $meta = Metas::find($pza['id_meta']);

                //Obtener el nombre del operador
                if (!in_array($this->getNameOperador($meta->id_usuario), $operadores)) {
                    //Guardar el nombre del operador
                    $operadores[$contador] = array();
                    if (!array_key_exists("n_pieza", $pza)) {
                        array_push($operadores[$contador], $pza["n_juego"]);
                    } else {
                        array_push($operadores[$contador], $pza["n_pieza"]);
                    }
                    array_push($operadores[$contador], $this->getNameOperador($meta->id_usuario));
                    $contador++;
                }
            }
        } else {
            $meta = Metas::find($piecesInfo->id_meta);
            $operadores[0] = array();
            array_push($operadores[0], $pieceInfo->n_juego);
            array_push($operadores[0], $this->getNameOperador($meta->id_usuario));
        }
        return view('pieces_views.piecesReport.chosenPiece', compact('process', 'piecesInfo', 'cNominal', 'tolerance', 'ot', 'clase', 'profile', 'operadores'));
    }

    public function getOperadores($ot)
    {
        $operadores = Pieza::where('id_ot', $ot)->distinct('id_operador')->pluck('id_operador');
        for ($i = 0; $i < count($operadores); $i++) {
            $operadores[$i] = User::where('matricula', $operadores[$i])->first();
        }
        return $operadores;
    }
    public function getNameOperador($matricula)
    {
        $operador = User::where('matricula', $matricula)->first();
        return $operador->nombre . " " . $operador->a_paterno . " " . $operador->a_materno;
    }
    public function getNameClase($id)
    {
        $clase = Clase::find($id);
        return $clase->nombre . " " . $clase->tamanio;
    }
    public function getPiezaNumber($pieza)
    {
        switch (strlen($pieza)) {
            case 2:
                return substr($pieza, 0, 1);
            case 3:
                return substr($pieza, 0, 2);
            case 4:
                return substr($pieza, 0, 3);
        }
    }

    //Funciones para el control de la vista de piezas por maquina


    public function showVistaMaquina()
    {
        if ($this->retornarOTs() != 0) {
            $arregloOT = $this->retornarOTs();
            return view('processesAdmin.Maquinas.maquinas', compact('arregloOT'));
        } else {
            return view('processesAdmin.Maquinas.maquinas');
        }
    }
    public function showMachinesProcess(Request $request)
    {
        $ot = Orden_trabajo::find($request->ot);
        $clase = Clase::find($request->clase);
        $procesos = array();

        $proceso = Procesos::where('id_clase', $clase->id)->first();
        $proceso = $proceso->toArray();
        $camposNoCero = array_filter($proceso, function ($valor) {
            return $valor != 0;
        });
        $contador = 0;
        $indice = 0;

        foreach (array_keys($camposNoCero) as $nombreCampo) {
            if ($contador != 0 || $contador != 1) {
                $procesos[$indice] = array();
                $procesos[$indice][0] = $this->nombreProceso($nombreCampo);
                switch ($nombreCampo) {
                    case "cepillado":
                        $metas = Metas::where('id_clase', $clase->id)->get();
                        if (count($metas) > 0) {
                            $pzasNoCero = 0;
                            foreach ($metas as $meta) {
                                $piezas = Pza_cepillado::where('id_meta', $meta->id)->get();
                                if (count($piezas) > 0) {
                                    foreach ($piezas as $pieza) {
                                        if ($pieza->estado != 0) {
                                            $user = User::where('matricula', $meta->id_usuario)->first();
                                            if ($pieza->estado == 1) {
                                                $procesos[$indice][1][$pzasNoCero] = array($pieza->n_pieza, $user->nombre, "---", $meta->maquina);
                                            } else {
                                                $procesos[$indice][1][$pzasNoCero] = array($pieza->n_pieza, $user->nombre, "Terminada", $meta->maquina);
                                            }
                                            $pzasNoCero++;
                                        }
                                    }
                                }
                            }
                            if ($pzasNoCero == 0) {
                                $procesos[$indice][1][$pzasNoCero] = array("---", "---", "---", "---");
                            }
                        } else {
                            $procesos[$indice][1][0] = array("---", "---", "---", "---");
                        }
                        break;
                    case "desbaste_exterior":
                        $metas = Metas::where('id_clase', $clase->id)->get();
                        if (count($metas) > 0) {
                            $pzasNoCero = 0;
                            foreach ($metas as $meta) {
                                $piezas = Desbaste_pza::where('id_meta', $meta->id)->get();
                                if (count($piezas) > 0) {
                                    foreach ($piezas as $pieza) {
                                        if ($pieza->estado != 0) {
                                            $user = User::where('matricula', $meta->id_usuario)->first();
                                            if ($pieza->estado == 1) {
                                                $procesos[$indice][1][$pzasNoCero] = array($pieza->n_pieza, $user->nombre, "---", $meta->maquina);
                                            } else {
                                                $procesos[$indice][1][$pzasNoCero] = array($pieza->n_pieza, $user->nombre, "Terminada", $meta->maquina);
                                            }
                                            $pzasNoCero++;
                                        }
                                    }
                                }
                            }
                            if ($pzasNoCero == 0) {
                                $procesos[$indice][1][$pzasNoCero] = array("---", "---", "---", "---");
                            }
                        } else {
                            $procesos[$indice][1][0] = array("---", "---", "---", "---");
                        }
                        break;
                    case "revision_laterales":
                        $metas = Metas::where('id_clase', $clase->id)->get();
                        if (count($metas) > 0) {
                            $pzasNoCero = 0;
                            foreach ($metas as $meta) {
                                $piezas = RevLaterales_pza::where('id_meta', $meta->id)->get();
                                if (count($piezas) > 0) {
                                    foreach ($piezas as $pieza) {
                                        if ($pieza->estado != 0) {
                                            $user = User::where('matricula', $meta->id_usuario)->first();
                                            if ($pieza->estado == 1) {
                                                $procesos[$indice][1][$pzasNoCero] = array($pieza->n_pieza, $user->nombre, "---", $meta->maquina);
                                            } else {
                                                $procesos[$indice][1][$pzasNoCero] = array($pieza->n_pieza, $user->nombre, "Terminada", $meta->maquina);
                                            }
                                            $pzasNoCero++;
                                        }
                                    }
                                }
                            }
                            if ($pzasNoCero == 0) {
                                $procesos[$indice][1][$pzasNoCero] = array("---", "---", "---", "---");
                            }
                        } else {
                            $procesos[$indice][1][0] = array("---", "---", "---", "---");
                        }
                        break;
                    case "pOperacion":
                        $metas = Metas::where('id_clase', $clase->id)->get();
                        if (count($metas) > 0) {
                            $pzasNoCero = 0;
                            foreach ($metas as $meta) {
                                $piezas = PrimeraOpeSoldadura_pza::where('id_meta', $meta->id)->get();
                                if (count($piezas) > 0) {
                                    foreach ($piezas as $pieza) {
                                        if ($pieza->estado != 0) {
                                            $user = User::where('matricula', $meta->id_usuario)->first();
                                            if ($pieza->estado == 1) {
                                                $procesos[$indice][1][$pzasNoCero] = array($pieza->n_pieza, $user->nombre, "---", $meta->maquina);
                                            } else {
                                                $procesos[$indice][1][$pzasNoCero] = array($pieza->n_pieza, $user->nombre, "Terminada", $meta->maquina);
                                            }
                                            $pzasNoCero++;
                                        }
                                    }
                                }
                            }
                            if ($pzasNoCero == 0) {
                                $procesos[$indice][1][$pzasNoCero] = array("---", "---", "---", "---");
                            }
                        } else {
                            $procesos[$indice][1][0] = array("---", "---", "---", "---");
                        }
                        break;
                    case "barreno_maniobra":
                        $metas = Metas::where('id_clase', $clase->id)->get();
                        if (count($metas) > 0) {
                            $pzasNoCero = 0;
                            foreach ($metas as $meta) {
                                $piezas = BarrenoManiobra_pza::where('id_meta', $meta->id)->get();
                                if (count($piezas) > 0) {
                                    foreach ($piezas as $pieza) {
                                        if ($pieza->estado != 0) {
                                            $user = User::where('matricula', $meta->id_usuario)->first();
                                            if ($pieza->estado == 1) {
                                                $procesos[$indice][1][$pzasNoCero] = array($pieza->n_pieza, $user->nombre, "---", $meta->maquina);
                                            } else {
                                                $procesos[$indice][1][$pzasNoCero] = array($pieza->n_pieza, $user->nombre, "Terminada", $meta->maquina);
                                            }
                                            $pzasNoCero++;
                                        }
                                    }
                                }
                            }
                            if ($pzasNoCero == 0) {
                                $procesos[$indice][1][$pzasNoCero] = array("---", "---", "---", "---");
                            }
                        } else {
                            $procesos[$indice][1][0] = array("---", "---", "---", "---");
                        }
                        break;
                    case "sOperacion":
                        $metas = Metas::where('id_clase', $clase->id)->get();
                        if (count($metas) > 0) {
                            $pzasNoCero = 0;
                            foreach ($metas as $meta) {
                                $piezas = SegundaOpeSoldadura_pza::where('id_meta', $meta->id)->get();
                                if (count($piezas) > 0) {
                                    foreach ($piezas as $pieza) {
                                        if ($pieza->estado != 0) {
                                            $user = User::where('matricula', $meta->id_usuario)->first();
                                            if ($pieza->estado == 1) {
                                                $procesos[$indice][1][$pzasNoCero] = array($pieza->n_pieza, $user->nombre, "---", $meta->maquina);
                                            } else {
                                                $procesos[$indice][1][$pzasNoCero] = array($pieza->n_pieza, $user->nombre, "Terminada", $meta->maquina);
                                            }
                                            $pzasNoCero++;
                                        }
                                    }
                                }
                            }
                            if ($pzasNoCero == 0) {
                                $procesos[$indice][1][$pzasNoCero] = array("---", "---", "---", "---");
                            }
                        } else {
                            $procesos[$indice][1][0] = array("---", "---", "---", "---");
                        }
                        break;
                    case "soldadura":
                        $metas = Metas::where('id_clase', $clase->id)->get();
                        if (count($metas) > 0) {
                            $pzasNoCero = 0;
                            foreach ($metas as $meta) {
                                $piezas = Soldadura_pza::where('id_meta', $meta->id)->get();
                                if (count($piezas) > 0) {
                                    foreach ($piezas as $pieza) {
                                        if ($pieza->estado != 0) {
                                            $user = User::where('matricula', $meta->id_usuario)->first();
                                            if ($pieza->estado == 1) {
                                                $procesos[$indice][1][$pzasNoCero] = array($pieza->n_juego, $user->nombre, "---", $meta->maquina);
                                            } else {
                                                $procesos[$indice][1][$pzasNoCero] = array($pieza->n_juego, $user->nombre, "Terminada", $meta->maquina);
                                            }
                                            $pzasNoCero++;
                                        }
                                    }
                                }
                            }
                            if ($pzasNoCero == 0) {
                                $procesos[$indice][1][$pzasNoCero] = array("---", "---", "---", "---");
                            }
                        } else {
                            $procesos[$indice][1][0] = array("---", "---", "---", "---");
                        }
                        break;
                    case "soldaduraPTA":
                        $metas = Metas::where('id_clase', $clase->id)->get();
                        if (count($metas) > 0) {
                            $pzasNoCero = 0;
                            foreach ($metas as $meta) {
                                $piezas = SoldaduraPTA_pza::where('id_meta', $meta->id)->get();
                                if (count($piezas) > 0) {
                                    foreach ($piezas as $pieza) {
                                        if ($pieza->estado != 0) {
                                            $user = User::where('matricula', $meta->id_usuario)->first();
                                            if ($pieza->estado == 1) {
                                                $procesos[$indice][1][$pzasNoCero] = array($pieza->n_juego, $user->nombre, "---", $meta->maquina);
                                            } else {
                                                $procesos[$indice][1][$pzasNoCero] = array($pieza->n_juego, $user->nombre, "Terminada", $meta->maquina);
                                            }
                                            $pzasNoCero++;
                                        }
                                    }
                                }
                            }
                            if ($pzasNoCero == 0) {
                                $procesos[$indice][1][$pzasNoCero] = array("---", "---", "---", "---");
                            }
                        } else {
                            $procesos[$indice][1][0] = array("---", "---", "---", "---");
                        }
                        break;
                    case "rectificado":
                        $metas = Metas::where('id_clase', $clase->id)->get();
                        if (count($metas) > 0) {
                            $pzasNoCero = 0;
                            foreach ($metas as $meta) {
                                $piezas = Rectificado_pza::where('id_meta', $meta->id)->get();
                                if (count($piezas) > 0) {
                                    foreach ($piezas as $pieza) {
                                        if ($pieza->estado != 0) {
                                            $user = User::where('matricula', $meta->id_usuario)->first();
                                            if ($pieza->estado == 1) {
                                                $procesos[$indice][1][$pzasNoCero] = array($pieza->n_juego, $user->nombre, "---", $meta->maquina);
                                            } else {
                                                $procesos[$indice][1][$pzasNoCero] = array($pieza->n_juego, $user->nombre, "Terminada", $meta->maquina);
                                            }
                                            $pzasNoCero++;
                                        }
                                    }
                                }
                            }
                            if ($pzasNoCero == 0) {
                                $procesos[$indice][1][$pzasNoCero] = array("---", "---", "---", "---");
                            }
                        } else {
                            $procesos[$indice][1][0] = array("---", "---", "---", "---");
                        }
                        break;
                    case "asentado":
                        $metas = Metas::where('id_clase', $clase->id)->get();
                        if (count($metas) > 0) {
                            $pzasNoCero = 0;
                            foreach ($metas as $meta) {
                                $piezas = Asentado_pza::where('id_meta', $meta->id)->get();
                                if (count($piezas) > 0) {
                                    foreach ($piezas as $pieza) {
                                        if ($pieza->estado != 0) {
                                            $user = User::where('matricula', $meta->id_usuario)->first();
                                            if ($pieza->estado == 1) {
                                                $procesos[$indice][1][$pzasNoCero] = array($pieza->n_juego, $user->nombre, "---", $meta->maquina);
                                            } else {
                                                $procesos[$indice][1][$pzasNoCero] = array($pieza->n_juego, $user->nombre, "Terminada", $meta->maquina);
                                            }
                                            $pzasNoCero++;
                                        }
                                    }
                                }
                            }
                            if ($pzasNoCero == 0) {
                                $procesos[$indice][1][$pzasNoCero] = array("---", "---", "---", "---");
                            }
                        } else {
                            $procesos[$indice][1][0] = array("---", "---", "---", "---");
                        }
                        break;
                    case "calificado":
                        $metas = Metas::where('id_clase', $clase->id)->get();
                        if (count($metas) > 0) {
                            $pzasNoCero = 0;
                            foreach ($metas as $meta) {
                                $piezas = revCalificado_pza::where('id_meta', $meta->id)->get();
                                if (count($piezas) > 0) {
                                    foreach ($piezas as $pieza) {
                                        if ($pieza->estado != 0) {
                                            $user = User::where('matricula', $meta->id_usuario)->first();
                                            if ($pieza->estado == 1) {
                                                $procesos[$indice][1][$pzasNoCero] = array($pieza->n_juego, $user->nombre, "---", $meta->maquina);
                                            } else {
                                                $procesos[$indice][1][$pzasNoCero] = array($pieza->n_juego, $user->nombre, "Terminada", $meta->maquina);
                                            }
                                            $pzasNoCero++;
                                        }
                                    }
                                }
                            }
                            if ($pzasNoCero == 0) {
                                $procesos[$indice][1][$pzasNoCero] = array("---", "---", "---", "---");
                            }
                        } else {
                            $procesos[$indice][1][0] = array("---", "---", "---", "---");
                        }
                        break;
                    case "acabadoBombillo":
                        $metas = Metas::where('id_clase', $clase->id)->get();
                        if (count($metas) > 0) {
                            $pzasNoCero = 0;
                            foreach ($metas as $meta) {
                                $piezas = AcabadoBombilo_pza::where('id_meta', $meta->id)->get();
                                if (count($piezas) > 0) {
                                    foreach ($piezas as $pieza) {
                                        if ($pieza->estado != 0) {
                                            $user = User::where('matricula', $meta->id_usuario)->first();
                                            if ($pieza->estado == 1) {
                                                $procesos[$indice][1][$pzasNoCero] = array($pieza->n_juego, $user->nombre, "---", $meta->maquina);
                                            } else {
                                                $procesos[$indice][1][$pzasNoCero] = array($pieza->n_juego, $user->nombre, "Terminada", $meta->maquina);
                                            }
                                            $pzasNoCero++;
                                        }
                                    }
                                }
                            }
                            if ($pzasNoCero == 0) {
                                $procesos[$indice][1][$pzasNoCero] = array("---", "---", "---", "---");
                            }
                        } else {
                            $procesos[$indice][1][0] = array("---", "---", "---", "---");
                        }
                        break;
                    case "acabadoMolde":
                        $metas = Metas::where('id_clase', $clase->id)->get();
                        if (count($metas) > 0) {
                            $pzasNoCero = 0;
                            foreach ($metas as $meta) {
                                $piezas = AcabadoMolde_pza::where('id_meta', $meta->id)->get();
                                if (count($piezas) > 0) {
                                    foreach ($piezas as $pieza) {
                                        if ($pieza->estado != 0) {
                                            $user = User::where('matricula', $meta->id_usuario)->first();
                                            if ($pieza->estado == 1) {
                                                $procesos[$indice][1][$pzasNoCero] = array($pieza->n_juego, $user->nombre, "---", $meta->maquina);
                                            } else {
                                                $procesos[$indice][1][$pzasNoCero] = array($pieza->n_juego, $user->nombre, "Terminada", $meta->maquina);
                                            }
                                            $pzasNoCero++;
                                        }
                                    }
                                }
                            }
                            if ($pzasNoCero == 0) {
                                $procesos[$indice][1][$pzasNoCero] = array("---", "---", "---", "---");
                            }
                        } else {
                            $procesos[$indice][1][0] = array("---", "---", "---", "---");
                        }
                        break;
                    case 'barreno_profundidad':
                        $metas = Metas::where('id_clase', $clase->id)->get();
                        if (count($metas) > 0) {
                            $pzasNoCero = 0;
                            foreach ($metas as $meta) {
                                $piezas = BarrenoProfundidad_pza::where('id_meta', $meta->id)->get();
                                if (count($piezas) > 0) {
                                    foreach ($piezas as $pieza) {
                                        if ($pieza->estado != 0) {
                                            $user = User::where('matricula', $meta->id_usuario)->first();
                                            if ($pieza->estado == 1) {
                                                $procesos[$indice][1][$pzasNoCero] = array($pieza->n_juego, $user->nombre, "---", $meta->maquina);
                                            } else {
                                                $procesos[$indice][1][$pzasNoCero] = array($pieza->n_juego, $user->nombre, "Terminada", $meta->maquina);
                                            }
                                            $pzasNoCero++;
                                        }
                                    }
                                }
                            }
                            if ($pzasNoCero == 0) {
                                $procesos[$indice][1][$pzasNoCero] = array("---", "---", "---", "---");
                            }
                        } else {
                            $procesos[$indice][1][0] = array("---", "---", "---", "---");
                        }
                        break;
                    case "cavidades":
                        $metas = Metas::where('id_clase', $clase->id)->get();
                        if (count($metas) > 0) {
                            $pzasNoCero = 0;
                            foreach ($metas as $meta) {
                                $piezas = Cavidades_pza::where('id_meta', $meta->id)->get();
                                if (count($piezas) > 0) {
                                    foreach ($piezas as $pieza) {
                                        if ($pieza->estado != 0) {
                                            $user = User::where('matricula', $meta->id_usuario)->first();
                                            if ($pieza->estado == 1) {
                                                $procesos[$indice][1][$pzasNoCero] = array($pieza->n_juego, $user->nombre, "---", $meta->maquina);
                                            } else {
                                                $procesos[$indice][1][$pzasNoCero] = array($pieza->n_juego, $user->nombre, "Terminada", $meta->maquina);
                                            }
                                            $pzasNoCero++;
                                        }
                                    }
                                }
                            }
                            if ($pzasNoCero == 0) {
                                $procesos[$indice][1][$pzasNoCero] = array("---", "---", "---", "---");
                            }
                        } else {
                            $procesos[$indice][1][0] = array("---", "---", "---", "---");
                        }
                        break;
                    case "copiado":
                        $metas = Metas::where('id_clase', $clase->id)->get();
                        if (count($metas) > 0) {
                            $pzasNoCero = 0;
                            foreach ($metas as $meta) {
                                $piezas = Copiado_pza::where('id_meta', $meta->id)->get();
                                if (count($piezas) > 0) {
                                    foreach ($piezas as $pieza) {
                                        if ($pieza->estado != 0) {
                                            $user = User::where('matricula', $meta->id_usuario)->first();
                                            if ($pieza->estado == 1) {
                                                $procesos[$indice][1][$pzasNoCero] = array($pieza->n_juego, $user->nombre, "---", $meta->maquina);
                                            } else {
                                                $procesos[$indice][1][$pzasNoCero] = array($pieza->n_juego, $user->nombre, "Terminada", $meta->maquina);
                                            }
                                            $pzasNoCero++;
                                        }
                                    }
                                }
                            }
                            if ($pzasNoCero == 0) {
                                $procesos[$indice][1][$pzasNoCero] = array("---", "---", "---", "---");
                            }
                        } else {
                            $procesos[$indice][1][0] = array("---", "---", "---", "---");
                        }
                        break;
                    case "offSet":
                        $metas = Metas::where('id_clase', $clase->id)->get();
                        if (count($metas) > 0) {
                            $pzasNoCero = 0;
                            foreach ($metas as $meta) {
                                $piezas = OffSet_pza::where('id_meta', $meta->id)->get();
                                if (count($piezas) > 0) {
                                    foreach ($piezas as $pieza) {
                                        if ($pieza->estado != 0) {
                                            $user = User::where('matricula', $meta->id_usuario)->first();
                                            if ($pieza->estado == 1) {
                                                $procesos[$indice][1][$pzasNoCero] = array($pieza->n_juego, $user->nombre, "---", $meta->maquina);
                                            } else {
                                                $procesos[$indice][1][$pzasNoCero] = array($pieza->n_juego, $user->nombre, "Terminada", $meta->maquina);
                                            }
                                            $pzasNoCero++;
                                        }
                                    }
                                }
                            }
                            if ($pzasNoCero == 0) {
                                $procesos[$indice][1][$pzasNoCero] = array("---", "---", "---", "---");
                            }
                        } else {
                            $procesos[$indice][1][0] = array("---", "---", "---", "---");
                        }
                        break;
                    case "palomas":
                        $metas = Metas::where('id_clase', $clase->id)->get();
                        if (count($metas) > 0) {
                            $pzasNoCero = 0;
                            foreach ($metas as $meta) {
                                $piezas = Palomas_pza::where('id_meta', $meta->id)->get();
                                if (count($piezas) > 0) {
                                    foreach ($piezas as $pieza) {
                                        if ($pieza->estado != 0) {
                                            $user = User::where('matricula', $meta->id_usuario)->first();
                                            if ($pieza->estado == 1) {
                                                $procesos[$indice][1][$pzasNoCero] = array($pieza->n_juego, $user->nombre, "---", $meta->maquina);
                                            } else {
                                                $procesos[$indice][1][$pzasNoCero] = array($pieza->n_juego, $user->nombre, "Terminada", $meta->maquina);
                                            }
                                            $pzasNoCero++;
                                        }
                                    }
                                }
                            }
                            if ($pzasNoCero == 0) {
                                $procesos[$indice][1][$pzasNoCero] = array("---", "---", "---", "---");
                            }
                        } else {
                            $procesos[$indice][1][0] = array("---", "---", "---", "---");
                        }
                        break;
                    case "rebajes":
                        $metas = Metas::where('id_clase', $clase->id)->get();
                        if (count($metas) > 0) {
                            $pzasNoCero = 0;
                            foreach ($metas as $meta) {
                                $piezas = Rebajes_pza::where('id_meta', $meta->id)->get();
                                if (count($piezas) > 0) {
                                    foreach ($piezas as $pieza) {
                                        if ($pieza->estado != 0) {
                                            $user = User::where('matricula', $meta->id_usuario)->first();
                                            if ($pieza->estado == 1) {
                                                $procesos[$indice][1][$pzasNoCero] = array($pieza->n_juego, $user->nombre, "---", $meta->maquina);
                                            } else {
                                                $procesos[$indice][1][$pzasNoCero] = array($pieza->n_juego, $user->nombre, "Terminada", $meta->maquina);
                                            }
                                            $pzasNoCero++;
                                        }
                                    }
                                }
                            }
                            if ($pzasNoCero == 0) {
                                $procesos[$indice][1][$pzasNoCero] = array("---", "---", "---", "---");
                            }
                        } else {
                            $procesos[$indice][1][0] = array("---", "---", "---", "---");
                        }
                        break;
                    case "grabado":
                        $procesos[$indice][1][0] = array("---", "---", "---", "---");
                        break;
                    case "operacionEquipo":
                        $metas = Metas::where('id_clase', $clase->id)->get();
                        if (count($metas) > 0) {
                            $pzasNoCero = 0;
                            foreach ($metas as $meta) {
                                $piezas = PySOpeSoldadura_pza::where('id_meta', $meta->id)->get();
                                if (count($piezas) > 0) {
                                    foreach ($piezas as $pieza) {
                                        if ($pieza->estado != 0) {
                                            //Obtener usuario
                                            $user = User::where('matricula', $meta->id_usuario)->first();
                                            //Obtener operacion
                                            $operacion = PySOpeSoldadura::find($pieza->id_proceso);
                                            $operacion = $operacion->operacion;
                                            if ($pieza->estado == 1) {
                                                $procesos[$indice][1][$pzasNoCero] = array($pieza->n_pieza, $user->nombre, "---", $meta->maquina, $operacion);
                                            } else {
                                                $procesos[$indice][1][$pzasNoCero] = array($pieza->n_pieza, $user->nombre, "Terminada", $meta->maquina, $operacion);
                                            }
                                            $pzasNoCero++;
                                        }
                                    }
                                }
                            }
                            if ($pzasNoCero == 0) {
                                $procesos[$indice][1][$pzasNoCero] = array("---", "---", "---", "---");
                            }
                        } else {
                            $procesos[$indice][1][0] = array("---", "---", "---", "---");
                        }
                        break;
                    case "embudoCM":
                        $metas = Metas::where('id_clase', $clase->id)->get();
                        if (count($metas) > 0) {
                            $pzasNoCero = 0;
                            foreach ($metas as $meta) {
                                $piezas = EmbudoCM_pza::where('id_meta', $meta->id)->get();
                                if (count($piezas) > 0) {
                                    foreach ($piezas as $pieza) {
                                        if ($pieza->estado != 0) {
                                            $user = User::where('matricula', $meta->id_usuario)->first();
                                            if ($pieza->estado == 1) {
                                                $procesos[$indice][1][$pzasNoCero] = array($pieza->n_juego, $user->nombre, "---", $meta->maquina);
                                            } else {
                                                $procesos[$indice][1][$pzasNoCero] = array($pieza->n_juego, $user->nombre, "Terminada", $meta->maquina);
                                            }
                                            $pzasNoCero++;
                                        }
                                    }
                                }
                            }
                            if ($pzasNoCero == 0) {
                                $procesos[$indice][1][$pzasNoCero] = array("---", "---", "---", "---");
                            }
                        } else {
                            $procesos[$indice][1][0] = array("---", "---", "---", "---");
                        }
                        break;
                    default:
                        break;
                }
                $indice++;
            }

            $contador++;
        }
        array_splice($procesos, 0, 2);
        return view('processesAdmin.Maquinas.vistaProcesos', compact('procesos', 'ot', 'clase'));
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
                return "Barreno Maniobra";
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
                return "Operacion Equipo";
            case "embudoCM":
                return "Embudo CM";
        }
    }
}
