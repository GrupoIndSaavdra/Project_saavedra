<?php

namespace App\Http\Controllers;

use App\Models\AcabadoBombilo_cnominal;
use App\Models\AcabadoBombilo_tolerancia;
use App\Models\AcabadoMolde_cnominal;
use App\Models\AcabadoMolde_tolerancia;
use App\Models\BarrenoManiobra_cnominal;
use App\Models\BarrenoManiobra_tolerancia;
use App\Models\BarrenoProfundidad_cnominal;
use App\Models\BarrenoProfundidad_tolerancia;
use App\Models\Cavidades_cnominal;
use App\Models\Cavidades_tolerancia;
use App\Models\Cepillado;
use App\Models\Cepillado_cnominal;
use App\Models\Cepillado_tolerancia;
use App\Models\Clase;
use App\Models\Copiado_cnominal;
use App\Models\Copiado_tolerancia;
use App\Models\Desbaste_cnominal;
use App\Models\Desbaste_pza;
use App\Models\Desbaste_tolerancia;
use App\Models\DesbasteExterior;
use App\Models\EmbudoCM_cnominal;
use App\Models\EmbudoCM_tolerancias;
use App\Models\Metas;
use App\Models\Moldura;
use App\Models\OffSet_cnominal;
use App\Models\OffSet_tolerancia;
use App\Models\Orden_trabajo;
use App\Models\Palomas_cnominal;
use App\Models\Palomas_tolerancia;
use App\Models\Pieza;
use App\Models\PrimeraOpeSoldadura_cnominal;
use App\Models\PrimeraOpeSoldadura_tolerancia;
use App\Models\PrimeraOpeSoldadura;
use App\Models\PrimeraOpeSoldadura_pza;
use App\Models\Procesos;
use App\Models\PySOpeSoldadura_cnominal;
use App\Models\PySOpeSoldadura_tolerancia;
use App\Models\Pza_cepillado;
use App\Models\Rebajes_cnominal;
use App\Models\Rebajes_tolerancia;
use App\Models\revCalificado_cnominal;
use App\Models\revCalificado_tolerancia;
use App\Models\RevLaterales_cnominal;
use App\Models\RevLaterales_tolerancia;
use App\Models\SegundaOpeSoldadura_cnominal;
use App\Models\SegundaOpeSoldadura_tolerancia;
use Illuminate\Http\Request;

class ProcessesController extends Controller
{
    protected $classController;
    protected $releasedPiecesController;
    public function __construct()
    {
        $this->middleware('auth');
        $this->classController = new ClassController();
        $this->releasedPiecesController = new PzasLiberadasController();
    }
    public function show_cNominalsView()
    {
        $wOrdersFounded = Orden_trabajo::all();
        $workOrders = array();
        if (count($wOrdersFounded) > 0) {
            foreach ($wOrdersFounded as $workOrder) {
                $classes = $this->classController->getClasses($workOrder);
                //Filtrar clases no finalizadas
                foreach ($classes as $key => $item) {
                    if ($item->finalizada != 0) {
                        unset($classes[$key]);
                    }
                }

                if (count($classes) > 0) {
                    $molding = Moldura::where('id', $workOrder->id_moldura)->first();
                    $workOrderTxt = $workOrder->id . " - " . $molding->nombre;
                    $workOrders[$workOrderTxt] = array();
                    foreach ($classes as $class) {
                        $processes = Procesos::where('id_clase', $class->id)->first();
                        if ($processes) {
                            $workOrders[$workOrderTxt][$class->nombre] = array();
                            foreach ($processes->getAttributes() as $process => $valor) {
                                if (($process != "id" && $process != "id_clase" && $process != "soldadura" && $process != "soldaduraPTA" && $process != "rectificado" && $process != "asentado") && $valor != 0) {
                                    $process = $this->convertProcessToString($process);
                                    $workOrders[$workOrderTxt][$class->nombre][$process] = array();
                                    if ($process == "Operacion Equipo") {
                                        for ($i = 1; $i <= 2; $i++) {
                                            $workOrders[$workOrderTxt][$class->nombre][$process][$i . ' operacion'] = array();
                                            $data = $this->searchCNominals($class, $process, $i . ' operacion');
                                            if ($data) {
                                                foreach ($data as $key => $value) {
                                                    $workOrders[$workOrderTxt][$class->nombre][$process][$i . ' operacion'][$key] = $value;
                                                }
                                            }
                                        }
                                    } elseif ($process == "Copiado") {
                                        $subProcesses = ["Cilindrado", "Cavidades"];
                                        foreach ($subProcesses as $subprocess) {
                                            $workOrders[$workOrderTxt][$class->nombre][$process][$subprocess] = array();
                                            $data = $this->searchCNominals($class, $process, $subprocess);
                                            if ($data) {
                                                foreach ($data as $key => $value) {
                                                    $workOrders[$workOrderTxt][$class->nombre][$process][$subprocess][$key] = $value;
                                                }
                                            }
                                        }
                                    } else {
                                        //Insertar cotas y tolerancias de cada proceso que no sea Copiado o 1 y 2 Operacion Equipo
                                        $data = $this->searchCNominals($class, $process);
                                        if ($data) {
                                            foreach ($data as $key => $value) {
                                                $workOrders[$workOrderTxt][$class->nombre][$process][$key] = $value;
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
        $workOrders = count($workOrders) > 0 ? $workOrders : null;

        [$pieces_Released, $info_Pieces] = $this->releasedPiecesController->piecesToBeReleased();
        return view('processes_views.cNominals_view', compact('workOrders', 'pieces_Released', 'info_Pieces'));
    }
    public function searchCNominals($class, $process, $subprocess = null)
    {
        switch ($process) {
            case 'Cepillado':
                $id_operation = 'Cepillado_' . $class->nombre . "_" . $class->id_ot;
                $cNominal = Cepillado_cnominal::where('id_proceso', $id_operation)->first();
                $tolerance = Cepillado_tolerancia::where('id_proceso', $id_operation)->first();
                break;
            case 'Desbaste Exterior':
                $id_operation = 'Desbaste_Exterior_' . $class->nombre . "_" . $class->id_ot;
                $cNominal = Desbaste_cnominal::where('id_proceso', $id_operation)->first();
                $tolerance = Desbaste_tolerancia::where('id_proceso', $id_operation)->first();
                break;
            case 'Revision Laterales':
                $id_operation = 'Revision_Laterales_' . $class->nombre . "_" . $class->id_ot;
                $cNominal = RevLaterales_cnominal::where('id_proceso', $id_operation)->first();
                $tolerance = RevLaterales_tolerancia::where('id_proceso', $id_operation)->first();
                break;
            case 'Primera Operacion':
                $id_operation = 'Primera_Operacion_' . $class->nombre . "_" . $class->id_ot;
                $cNominal = PrimeraOpeSoldadura_cnominal::where('id_proceso', $id_operation)->first();
                $tolerance = PrimeraOpeSoldadura_tolerancia::where('id_proceso', $id_operation)->first();
                break;
            case 'Barreno Maniobra':
                $id_operation = 'Barreno_Maniobra_' . $class->nombre . "_" . $class->id_ot;
                $cNominal = BarrenoManiobra_cnominal::where('id_proceso', $id_operation)->first();
                $tolerance = BarrenoManiobra_tolerancia::where('id_proceso', $id_operation)->first();
                break;
            case 'Segunda Operacion':
                $id_operation = 'Segunda_Operacion_' . $class->nombre . "_" . $class->id_ot;
                $cNominal = SegundaOpeSoldadura_cnominal::where('id_proceso', $id_operation)->first();
                $tolerance = SegundaOpeSoldadura_tolerancia::where('id_proceso', $id_operation)->first();
                break;
            case 'Calificado':
                $id_operation = 'Calificado_' . $class->nombre . "_" . $class->id_ot;
                $cNominal = revCalificado_cnominal::where('id_proceso', $id_operation)->first();
                $tolerance = revCalificado_tolerancia::where('id_proceso', $id_operation)->first();
                break;
            case 'Acabado Bombillo':
                $id_operation = 'Acabado_Bombillo_' . $class->nombre . "_" . $class->id_ot;
                $cNominal = AcabadoBombilo_cnominal::where('id_proceso', $id_operation)->first();
                $tolerance = AcabadoBombilo_tolerancia::where('id_proceso', $id_operation)->first();
                break;
            case 'Acabado Molde':
                $id_operation = 'Acabado_Molde_' . $class->nombre . "_" . $class->id_ot;
                $cNominal = AcabadoMolde_cnominal::where('id_proceso', $id_operation)->first();
                $tolerance = AcabadoMolde_tolerancia::where('id_proceso', $id_operation)->first();
                break;
            case 'Barreno Profundidad':
                $id_operation = 'Barreno_Profundidad_' . $class->nombre . "_" . $class->id_ot;
                $cNominal = BarrenoProfundidad_cnominal::where('id_proceso', $id_operation)->first();
                $tolerance = BarrenoProfundidad_tolerancia::where('id_proceso', $id_operation)->first();
                break;
            case 'Cavidades':
                $id_operation = 'Cavidades_' . $class->nombre . "_" . $class->id_ot;
                $cNominal = Cavidades_cnominal::where('id_proceso', $id_operation)->first();
                $tolerance = Cavidades_tolerancia::where('id_proceso', $id_operation)->first();
                break;
            case 'Copiado':
                $id_operation = 'Copiado_' . $class->nombre . "_" . $class->id_ot;
                $cNominal = Copiado_cnominal::where('id_proceso', $id_operation)->first();
                $tolerance = Copiado_tolerancia::where('id_proceso', $id_operation)->first();
                break;
            case 'Off Set':
                $id_operation = 'Off_Set_' . $class->nombre . "_" . $class->id_ot;
                $cNominal = OffSet_cnominal::where('id_proceso', $id_operation)->first();
                $tolerance = OffSet_tolerancia::where('id_proceso', $id_operation)->first();
                break;
            case 'Palomas':
                $id_operation = 'Palomas_' . $class->nombre . "_" . $class->id_ot;
                $cNominal = Palomas_cnominal::where('id_proceso', $id_operation)->first();
                $tolerance = Palomas_tolerancia::where('id_proceso', $id_operation)->first();
                break;
            case 'Rebajes':
                $id_operation = 'Rebajes_' . $class->nombre . "_" . $class->id_ot;
                $cNominal = Rebajes_cnominal::where('id_proceso', $id_operation)->first();
                $tolerance = Rebajes_tolerancia::where('id_proceso', $id_operation)->first();
                break;
            case 'Operacion Equipo':
                $subprocessModified = str_replace(' ', '_', $subprocess);
                $id_operation = 'Operacion_Equipo_' . $subprocessModified . "_" . $class->nombre . "_" . $class->id_ot;
                $cNominal = PySOpeSoldadura_cnominal::where('id_proceso', $id_operation)->first();
                $tolerance = PySOpeSoldadura_tolerancia::where('id_proceso', $id_operation)->first();
                break;
            case 'Embudo CM':
                $id_operation = 'Embudo_CM_' . $class->nombre . "_" . $class->id_ot;
                $cNominal = EmbudoCM_cnominal::where('id_proceso', $id_operation)->first();
                $tolerance = EmbudoCM_tolerancias::where('id_proceso', $id_operation)->first();
                break;
            default:
                return null;
        }
        if ($this->verifycNominalsExisting($cNominal, $tolerance)) {
            return [$cNominal, $tolerance];
        }
        return null;
    }
    public function verifycNominalsExisting($cNominal, $tolerance)
    {
        if ($cNominal && $tolerance) {
            return true;
        }
        return false;
    }
    public function updatePieces($workOrder, $class, $process, $subprocess = null, $operation = null)
    {
        $workOrder = explode(" - ", $workOrder)[0];
        $class = Clase::where('nombre', $class)->where('id_ot', $workOrder)->first(); // Obtener clase

        if (!$class) {
            return;
        }

        // Obtener cadena de proceso y subproceso (Si existe)
        $processModified = $process . ($operation ? "_$operation" : '');

        // Obtener todas las piezas relacionadas con la orden de trabajo, clase y proceso
        $metas = Metas::where('id_ot', $workOrder)->where('id_clase', $class->id)->where('proceso', $processModified)->get();

        // Actualizar cada pieza de las meta
        foreach ($metas as $meta) {
            $controller = app(\App\Http\Controllers\ProcessProductionController::class);
            $controller->updatePieces($process, $operation, $meta, $class);
        }
    }

    public function storeCNominalsData(Request $request)
    {
        $processModified = str_replace(' ', '_', $request->process);
        $workOrderId = explode(" - ", $request->workOrder)[0];
        if ($request->subProcess) {
            if ($request->process == "Copiado") {
                $id_process = $processModified . '_' . $request->class . "_" . $workOrderId;
            } else {
                $id_process = $processModified . '_' . $request->subProcess . '_' . $request->class . "_" . $workOrderId;
            }
        } else if ($request->operation) {
            $operationModified = str_replace(' ', '_', $request->operation);
            $id_process = $processModified . '_' . $operationModified . '_' . $request->class . "_" . $workOrderId;
        } else {
            $id_process = $processModified . '_' . $request->class . "_" . $workOrderId;
        }
        $array = match ($request->process) {
            'Cepillado' => $this->cepillado($id_process, $request),
            'Desbaste Exterior' => $this->desbasteExterior($id_process, $request),
            'Revision Laterales' => $this->revisionLaterales($id_process, $request),
            'Primera Operacion' => $this->primeraOpeSoldadura($id_process, $request),
            'Barreno Maniobra' => $this->barrenoManiobra($id_process, $request),
            'Segunda Operacion' => $this->segundaOpeSoldadura($id_process, $request),
            'Calificado' => $this->calificado($id_process, $request),
            'Acabado Bombillo' => $this->acabadoBombillo($id_process, $request),
            'Acabado Molde' => $this->acabadoMolde($id_process, $request),
            'Barreno Profundidad' => $this->barrenoProfundidad($id_process, $request),
            'Cavidades' => $this->cavidades($id_process, $request),
            'Copiado' => $this->copiado($id_process, $request),
            'Off Set' => $this->offSet($id_process, $request),
            'Palomas' => $this->palomas($id_process, $request),
            'Rebajes' => $this->rebajes($id_process, $request),
            'Operacion Equipo' => $this->pySOpeSoldadura($id_process, $request),
            'Embudo CM' => $this->embudoCM($id_process, $request),
        };
        $this->updatePieces($workOrderId, $request->class, $request->process, $request->subProcess ?? null, $request->operation ?? null);
        return redirect()->to('cNominals')->with('success', 'Datos de ' . $request->process . ' guardados correctamente.');
    }

    public function convertirString($procesos)
    {
        $stringProcesos = array();
        foreach ($procesos as $proceso) {
            switch ($proceso) {
                case "cepillado":
                    array_push($stringProcesos, "Cepillado");
                    break;
                case "desbaste_exterior":
                    array_push($stringProcesos, "Desbaste Exterior");
                    break;
                case "revision_laterales":
                    array_push($stringProcesos, "Revision Laterales");
                    break;
                case "pOperacion":
                    array_push($stringProcesos, "Primera Operacion");
                    break;
                case "barreno_maniobra":
                    array_push($stringProcesos, "Barreno Maniobra");
                    break;
                case "sOperacion":
                    array_push($stringProcesos, "Segunda Operacion");
                    break;
                case "calificado":
                    array_push($stringProcesos, "Calificado");
                    break;
                case "acabadoBombillo":
                    array_push($stringProcesos, "Acabado Bombillo");
                    break;
                case "acabadoMolde":
                    array_push($stringProcesos, "Acabado Molde");
                    break;
                case "barreno_profundidad":
                    array_push($stringProcesos, "Barreno Profundidad");
                    break;
                case "cavidades":
                    array_push($stringProcesos, "Cavidades");
                    break;
                case "copiado":
                    array_push($stringProcesos, "Copiado");
                    break;
                case "offSet":
                    array_push($stringProcesos, "Off Set");
                    break;
                case "palomas":
                    array_push($stringProcesos, "Palomas");
                    break;
                case "rebajes":
                    array_push($stringProcesos, "Rebajes");
                    break;
                case "grabado":
                    array_push($stringProcesos, "Grabado");
                    break;
                case "operacionEquipo":
                    array_push($stringProcesos, "1 y 2 Operacion Equipo");
                    break;
                case "embudoCM":
                    array_push($stringProcesos, "Embudo CM");
                    break;
            }
        }
        return $stringProcesos;
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
        }
    }
    public function cepillado($id_proceso, $request)
    {
        $cNominal = Cepillado_cnominal::where('id_proceso', $id_proceso)->first();
        $tolerance = Cepillado_tolerancia::where('id_proceso', $id_proceso)->first();
        if (!$cNominal) {
            $cNominal = new Cepillado_cnominal(); //Creación de objeto Cepillado_cnominal.
            $tolerance = new Cepillado_tolerancia(); //Creación de objeto Cepillado_tolerancia.
        }

        //Llenado de tabla Cepillado_cnominal
        $cNominal->id_proceso = $id_proceso;
        $cNominal->radiof_mordaza = $request->cNomi_radiof_mordaza;
        $cNominal->radiof_mayor = $request->cNomi_radiof_mayor;
        $cNominal->radiof_sufridera = $request->cNomi_radiof_sufridera;
        $cNominal->profuFinal_CFC = $request->cNomi_profuFinal_CFC;
        $cNominal->profuFinal_mitadMB = $request->cNomi_profuFinal_mitadMB;
        $cNominal->profuFinal_PCO = $request->cNomi_profuFinal_PCO;
        $cNominal->ensamble = $request->cNomi_ensamble;
        $cNominal->distancia_barrenoAli = $request->cNomi_distancia_barrenoAli;
        $cNominal->profu_barrenoAliHembra = $request->cNomi_profu_barrenoAliHembra;
        $cNominal->profu_barrenoAliMacho = $request->cNomi_profu_barrenoAliMacho;
        $cNominal->altura_venaHembra = $request->cNomi_altura_venaHembra;
        $cNominal->altura_venaMacho = $request->cNomi_altura_venaMacho;
        $cNominal->ancho_vena = $request->cNomi_ancho_vena;
        $cNominal->laterales = $request->cNomi_laterales;
        $cNominal->pin1 = $request->cNomi_pin1;
        $cNominal->pin2 = $request->cNomi_pin2;

        //Llenado de tabla Cepillado_tolerancia
        $tolerance->id_proceso = $id_proceso;
        $tolerance->radiof_mordaza1 = $request->tole_radiof_mordaza1;
        $tolerance->radiof_mordaza2 = $request->tole_radiof_mordaza2;
        $tolerance->radiof_mayor1 = $request->tole_radiof_mayor1;
        $tolerance->radiof_mayor2 = $request->tole_radiof_mayor2;
        $tolerance->radiof_sufridera1 = $request->tole_radiof_sufridera1;
        $tolerance->radiof_sufridera2 = $request->tole_radiof_sufridera2;
        $tolerance->profuFinal_CFC1 = $request->tole_profuFinal_CFC1;
        $tolerance->profuFinal_CFC2 = $request->tole_profuFinal_CFC2;
        $tolerance->profuFinal_mitadMB1 = $request->tole_profuFinal_mitadMB1;
        $tolerance->profuFinal_mitadMB2 = $request->tole_profuFinal_mitadMB2;
        $tolerance->profuFinal_PCO1 = $request->tole_profuFinal_PCO1;
        $tolerance->profuFinal_PCO2 = $request->tole_profuFinal_PCO2;
        $tolerance->ensamble1 = $request->tole_ensamble1;
        $tolerance->ensamble2 = $request->tole_ensamble2;
        $tolerance->distancia_barrenoAli1 = $request->tole_distancia_barrenoAli1;
        $tolerance->distancia_barrenoAli2 = $request->tole_distancia_barrenoAli2;
        $tolerance->profu_barrenoAliHembra1 = $request->tole_profu_barrenoAliHembra1;
        $tolerance->profu_barrenoAliHembra2 = $request->tole_profu_barrenoAliHembra2;
        $tolerance->profu_barrenoAliMacho1 = $request->tole_profu_barrenoAliMacho1;
        $tolerance->profu_barrenoAliMacho2 = $request->tole_profu_barrenoAliMacho2;
        $tolerance->altura_venaHembra1 = $request->tole_altura_venaHembra1;
        $tolerance->altura_venaHembra2 = $request->tole_altura_venaHembra2;
        $tolerance->altura_venaMacho1 = $request->tole_altura_venaMacho1;
        $tolerance->altura_venaMacho2 = $request->tole_altura_venaMacho2;
        $tolerance->ancho_vena1 = $request->tole_ancho_vena1;
        $tolerance->ancho_vena2 = $request->tole_ancho_vena2;
        $tolerance->laterales1 = $request->tole_laterales1;
        $tolerance->laterales2 = $request->tole_laterales2;
        $tolerance->pin1 = $request->tole_pin1;
        $tolerance->pin2 = $request->tole_pin2;

        $cNominal->save();
        $tolerance->save();
    }

    public function desbasteExterior($id_proceso, $request)
    {
        $cNominal = Desbaste_cnominal::where('id_proceso', $id_proceso)->first();
        $tolerance = Desbaste_tolerancia::where('id_proceso', $id_proceso)->first();
        if (!$cNominal) {
            $cNominal = new Desbaste_cnominal();
            $tolerance = new Desbaste_tolerancia();
        }

        //Llenado de tabla desbaste_cnominal
        $cNominal->id_proceso = $id_proceso;
        $cNominal->diametro_mordaza = $request->cNomi_diametro_mordaza;
        $cNominal->diametro_ceja = $request->cNomi_diametro_ceja;
        $cNominal->diametro_sufrideraExtra = $request->cNomi_diametro_sufrideraExtra;
        $cNominal->simetria_ceja = $request->cNomi_simetria_ceja;
        $cNominal->simetria_mordaza = $request->cNomi_simetria_mordaza;
        $cNominal->altura_ceja = $request->cNomi_altura_ceja;
        $cNominal->altura_sufridera = $request->cNomi_altura_sufridera;

        //Llenado de tabla desbaste_tolerancia
        $tolerance->id_proceso = $id_proceso;
        $tolerance->diametro_mordaza1 = $request->tole_diametro_mordaza1;
        $tolerance->diametro_mordaza2 = $request->tole_diametro_mordaza2;
        $tolerance->diametro_ceja1 = $request->tole_diametro_ceja1;
        $tolerance->diametro_ceja2 = $request->tole_diametro_ceja2;
        $tolerance->diametro_sufrideraExtra1 = $request->tole_diametro_sufrideraExtra1;
        $tolerance->diametro_sufrideraExtra2 = $request->tole_diametro_sufrideraExtra2;
        $tolerance->simetria_ceja1 = $request->tole_simetria_ceja1;
        $tolerance->simetria_ceja2 = $request->tole_simetria_ceja2;
        $tolerance->simetria_mordaza1 = $request->tole_simetria_mordaza1;
        $tolerance->simetria_mordaza2 = $request->tole_simetria_mordaza2;
        $tolerance->altura_ceja1 = $request->tole_altura_ceja1;
        $tolerance->altura_ceja2 = $request->tole_altura_ceja2;
        $tolerance->altura_sufridera1 = $request->tole_altura_sufridera1;
        $tolerance->altura_sufridera2 = $request->tole_altura_sufridera2;

        $cNominal->save();
        $tolerance->save();
    }
    public function revisionLaterales($id_proceso, $request)
    {
        $cNominal = RevLaterales_cnominal::where('id_proceso', $id_proceso)->first();
        $tolerance = RevLaterales_tolerancia::where('id_proceso', $id_proceso)->first();
        if (!$cNominal && !$tolerance) {
            $cNominal = new RevLaterales_cnominal();
            $tolerance = new RevLaterales_tolerancia();
        }
        //Llenado de tabla revLaterales_cnominal
        $cNominal->id_proceso = $id_proceso;
        $cNominal->desfasamiento_entrada = $request->cNomi_desfasamiento_entrada;
        $cNominal->desfasamiento_salida = $request->cNomi_desfasamiento_salida;
        $cNominal->ancho_simetriaEntrada = $request->cNomi_ancho_simetriaEntrada;
        $cNominal->ancho_simetriaSalida = $request->cNomi_ancho_simetriaSalida;
        $cNominal->angulo_corte = $request->cNomi_angulo_corte;

        //Llenado de tabla revLaterales_tolerancia
        $tolerance->id_proceso = $id_proceso;
        $tolerance->desfasamiento_entrada1 = $request->tole_desfasamiento_entrada1;
        $tolerance->desfasamiento_entrada2 = $request->tole_desfasamiento_entrada2;
        $tolerance->desfasamiento_salida1 = $request->tole_desfasamiento_salida1;
        $tolerance->desfasamiento_salida2 = $request->tole_desfasamiento_salida2;
        $tolerance->ancho_simetriaEntrada1 = $request->tole_ancho_simetriaEntrada1;
        $tolerance->ancho_simetriaEntrada2 = $request->tole_ancho_simetriaEntrada2;
        $tolerance->ancho_simetriaSalida1 = $request->tole_ancho_simetriaSalida1;
        $tolerance->ancho_simetriaSalida2 = $request->tole_ancho_simetriaSalida2;
        $tolerance->angulo_corte1 = $request->tole_angulo_corte1;
        $tolerance->angulo_corte2 = $request->tole_angulo_corte2;

        $cNominal->save();
        $tolerance->save();
    }
    public function primeraOpeSoldadura($id_proceso, $request)
    {
        $cNominal = PrimeraOpeSoldadura_cnominal::where('id_proceso', $id_proceso)->first();
        $tolerance = PrimeraOpeSoldadura_tolerancia::where('id_proceso', $id_proceso)->first();
        if (!$cNominal && !$tolerance) {
            $cNominal = new PrimeraOpeSoldadura_cnominal();
            $tolerance = new PrimeraOpeSoldadura_tolerancia();
        }

        //Llenado de tabla primeraOpeSoldadura_cnominal
        $cNominal->id_proceso = $id_proceso;
        $cNominal->diametro1 = $request->cNomi_diametro1;
        $cNominal->profundidad1 = $request->cNomi_profundidad1;
        $cNominal->diametro2 = $request->cNomi_diametro2;
        $cNominal->profundidad2 = $request->cNomi_profundidad2;
        $cNominal->diametro3 = $request->cNomi_diametro3;
        $cNominal->profundidad3 = $request->cNomi_profundidad3;
        $cNominal->diametroSoldadura = $request->cNomi_diametroSoldadura;
        $cNominal->diametroBarreno = $request->cNomi_diametroBarreno;
        $cNominal->profundidadSoldadura = $request->cNomi_profundidadSoldadura;
        $cNominal->simetriaLinea_partida = $request->cNomi_simetriaLinea_partida;
        $cNominal->pernoAlineacion = $request->cNomi_pernoAlineacion;
        $cNominal->Simetria90G = $request->cNomi_Simetria90G;

        //Llenado de tabla primeraOpeSoldadura_tolerancia
        $tolerance->id_proceso = $id_proceso;
        $tolerance->diametro1 = $request->tole_diametro1;
        $tolerance->profundidad1 = $request->tole_profundidad1;
        $tolerance->diametro2 = $request->tole_diametro2;
        $tolerance->profundidad2 = $request->tole_profundidad2;
        $tolerance->diametro3 = $request->tole_diametro3;
        $tolerance->profundidad3 = $request->tole_profundidad3;
        $tolerance->diametroSoldadura = $request->tole_diametroSoldadura;
        $tolerance->profundidadSoldadura = $request->tole_profundidadSoldadura;
        $tolerance->diametroBarreno1 = $request->tole_diametroBarreno1;
        $tolerance->diametroBarreno2 = $request->tole_diametroBarreno2;
        $tolerance->simetriaLinea_partida1 = $request->tole_simetriaLinea_partida1;
        $tolerance->simetriaLinea_partida2 = $request->tole_simetriaLinea_partida2;
        $tolerance->pernoAlineacion = $request->tole_pernoAlineacion;
        $tolerance->Simetria90G = $request->tole_Simetria90G;

        $cNominal->save();
        $tolerance->save();
    }
    public function barrenoManiobra($id_proceso, $request)
    {
        $cNominal = BarrenoManiobra_cnominal::where('id_proceso', $id_proceso)->first();
        $tolerance = BarrenoManiobra_tolerancia::where('id_proceso', $id_proceso)->first();
        if (!$cNominal && !$tolerance) {
            $cNominal = new BarrenoManiobra_cnominal();
            $tolerance = new BarrenoManiobra_tolerancia();
        }

        //Llenado de tabla BarrenoManiobra_cnominal
        $cNominal->id_proceso = $id_proceso;
        $cNominal->profundidad_barreno = $request->cNomi_profundidad_barreno;
        $cNominal->diametro_machuelo = $request->cNomi_diametro_machuelo;

        //Llenado de tabla BarrenoManiobra_tolerancia
        $tolerance->id_proceso = $id_proceso;
        $tolerance->profundidad_barreno1 = $request->tole_profundidad_barreno1;
        $tolerance->profundidad_barreno2 = $request->tole_profundidad_barreno2;
        $tolerance->diametro_machuelo1 = $request->tole_diametro_machuelo1;
        $tolerance->diametro_machuelo2 = $request->tole_diametro_machuelo2;

        $cNominal->save();
        $tolerance->save();
    }
    public function segundaOpeSoldadura($id_proceso, $request)
    {
        $cNominal = SegundaOpeSoldadura_cnominal::where('id_proceso', $id_proceso)->first();
        $tolerance = SegundaOpeSoldadura_tolerancia::where('id_proceso', $id_proceso)->first();
        if (!$cNominal && !$tolerance) {
            $cNominal = new SegundaOpeSoldadura_cnominal(); //Creación de objeto segundaOpeSoldadura_cnominal.
            $tolerance = new SegundaOpeSoldadura_tolerancia(); //Creación de objeto segundaOpeSoldadura_tolerancia.
        }
        //Llenado de tabla segundaOpeSoldadura_cnominal.
        $cNominal->id_proceso = $id_proceso; //Llenado de id_proceso para tabla segundaOpeSoldadura_cnominal.
        $cNominal->diametro1 = $request->cNomi_diametro1;
        $cNominal->profundidad1 = $request->cNomi_profundidad1;
        $cNominal->diametro2 = $request->cNomi_diametro2;
        $cNominal->profundidad2 = $request->cNomi_profundidad2;
        $cNominal->diametro3 = $request->cNomi_diametro3;
        $cNominal->profundidad3 = $request->cNomi_profundidad3;
        $cNominal->diametroSoldadura = $request->cNomi_diametroSoldadura;
        $cNominal->profundidadSoldadura = $request->cNomi_profundidadSoldadura;
        $cNominal->alturaTotal = $request->cNomi_alturaTotal;
        $cNominal->Simetria90G = $request->cNomi_simetria90G;
        $cNominal->simetriaLinea_Partida = $request->cNomi_simetriaLinea_Partida;

        //Llenado de tabla segundaOpeSoldadura_tolerancia
        $tolerance->id_proceso = $id_proceso;
        $tolerance->diametro1 = $request->tole_diametro1;
        $tolerance->profundidad1 = $request->tole_profundidad1;
        $tolerance->diametro2 = $request->tole_diametro2;
        $tolerance->profundidad2 = $request->tole_profundidad2;
        $tolerance->diametro3 = $request->tole_diametro3;
        $tolerance->profundidad3 = $request->tole_profundidad3;
        $tolerance->diametroSoldadura = $request->tole_diametroSoldadura;
        $tolerance->profundidadSoldadura = $request->tole_profundidadSoldadura;
        $tolerance->alturaTotal1 = $request->tole_alturaTotal1;
        $tolerance->alturaTotal2 = $request->tole_alturaTotal2;
        $tolerance->Simetria90G1 = $request->tole_simetria90G1;
        $tolerance->Simetria90G2 = $request->tole_simetria90G2;
        $tolerance->simetriaLinea_partida = $request->tole_simetriaLinea_Partida;

        $cNominal->save();
        $tolerance->save();
    }
    public function calificado($id_proceso, $request)
    {
        $cNominal = RevCalificado_cnominal::where('id_proceso', $id_proceso)->first();
        $tolerance = RevCalificado_tolerancia::where('id_proceso', $id_proceso)->first();
        if (!$cNominal && !$tolerance) {
            $cNominal = new RevCalificado_cnominal();
            $tolerance = new RevCalificado_tolerancia();
        }

        //Llenado de tabla calificado_cnominal
        $cNominal->id_proceso = $id_proceso;
        $cNominal->diametro_ceja = $request->cNomi_diametro_ceja;
        $cNominal->diametro_sufridera = $request->cNomi_diametro_sufridera;
        $cNominal->altura_sufridera = $request->cNomi_altura_sufridera;
        $cNominal->diametro_conexion = $request->cNomi_diametro_conexion;
        $cNominal->altura_conexion = $request->cNomi_altura_conexion;
        $cNominal->diametro_caja = $request->cNomi_diametro_caja;
        $cNominal->altura_caja = $request->cNomi_altura_caja;
        $cNominal->altura_total = $request->cNomi_altura_total;
        $cNominal->simetria = $request->cNomi_simetria;

        //Llenado de tabla calificado_tolerancia
        $tolerance->id_proceso = $id_proceso;
        $tolerance->diametro_ceja1 = $request->tole_diametro_ceja1;
        $tolerance->diametro_ceja2 = $request->tole_diametro_ceja2;
        $tolerance->diametro_sufridera1 = $request->tole_diametro_sufridera1;
        $tolerance->diametro_sufridera2 = $request->tole_diametro_sufridera2;
        $tolerance->altura_sufridera1 = $request->tole_altura_sufridera1;
        $tolerance->altura_sufridera2 = $request->tole_altura_sufridera2;
        $tolerance->diametro_conexion1 = $request->tole_diametro_conexion1;
        $tolerance->diametro_conexion2 = $request->tole_diametro_conexion2;
        $tolerance->altura_conexion1 = $request->tole_altura_conexion1;
        $tolerance->altura_conexion2 = $request->tole_altura_conexion2;
        $tolerance->diametro_caja1 = $request->tole_diametro_caja1;
        $tolerance->diametro_caja2 = $request->tole_diametro_caja2;
        $tolerance->altura_caja1 = $request->tole_altura_caja1;
        $tolerance->altura_caja2 = $request->tole_altura_caja2;
        $tolerance->altura_total1 = $request->tole_altura_total1;
        $tolerance->altura_total2 = $request->tole_altura_total2;
        $tolerance->simetria1 = $request->tole_simetria1;
        $tolerance->simetria2 = $request->tole_simetria2;

        $cNominal->save();
        $tolerance->save();
    }
    public function acabadoBombillo($id_proceso, $request)
    {
        $cNominal = AcabadoBombilo_cnominal::where('id_proceso', $id_proceso)->first();
        $tolerance = AcabadoBombilo_tolerancia::where('id_proceso', $id_proceso)->first();
        if (!$cNominal && !$tolerance) {
            $cNominal = new AcabadoBombilo_cnominal();
            $tolerance = new AcabadoBombilo_tolerancia();
        }

        //Llenado de tabla acabadoBombillo_cnominal
        $cNominal->id_proceso = $id_proceso;
        $cNominal->diametro_mordaza = $request->cNomi_diametro_mordaza;
        $cNominal->diametro_ceja = $request->cNomi_diametro_ceja;
        $cNominal->diametro_sufridera = $request->cNomi_diametro_sufridera;
        $cNominal->altura_mordaza = $request->cNomi_altura_mordaza;
        $cNominal->altura_ceja = $request->cNomi_altura_ceja;
        $cNominal->altura_sufridera = $request->cNomi_altura_sufridera;
        $cNominal->diametro_boca = $request->cNomi_diametro_boca;
        $cNominal->diametro_asiento_corona = $request->cNomi_diametro_asiento_corona;
        $cNominal->diametro_llanta = $request->cNomi_diametro_llanta;
        $cNominal->diametro_caja_corona = $request->cNomi_diametro_caja_corona;
        $cNominal->profundidad_corona = $request->cNomi_profundidad_corona;
        $cNominal->angulo_30 = $request->cNomi_angulo_30;
        $cNominal->profundidad_caja_corona = $request->cNomi_profundidad_caja_corona;
        $cNominal->simetria = $request->cNomi_simetria;

        //Llenado de tabla acabadoBombillo_tolerancia
        $tolerance->id_proceso = $id_proceso;
        $tolerance->diametro_mordaza1 = $request->tole_diametro_mordaza1;
        $tolerance->diametro_mordaza2 = $request->tole_diametro_mordaza2;
        $tolerance->diametro_ceja1 = $request->tole_diametro_ceja1;
        $tolerance->diametro_ceja2 = $request->tole_diametro_ceja2;
        $tolerance->diametro_sufridera1 = $request->tole_diametro_sufridera1;
        $tolerance->diametro_sufridera2 = $request->tole_diametro_sufridera2;
        $tolerance->altura_mordaza1 = $request->tole_altura_mordaza1;
        $tolerance->altura_mordaza2 = $request->tole_altura_mordaza2;
        $tolerance->altura_ceja1 = $request->tole_altura_ceja1;
        $tolerance->altura_ceja2 = $request->tole_altura_ceja2;
        $tolerance->altura_sufridera1 = $request->tole_altura_sufridera1;
        $tolerance->altura_sufridera2 = $request->tole_altura_sufridera2;
        $tolerance->diametro_boca1 = $request->tole_diametro_boca1;
        $tolerance->diametro_boca2 = $request->tole_diametro_boca2;
        $tolerance->diametro_asiento_corona1 = $request->tole_diametro_asiento_corona1;
        $tolerance->diametro_asiento_corona2 = $request->tole_diametro_asiento_corona2;
        $tolerance->diametro_llanta1 = $request->tole_diametro_llanta1;
        $tolerance->diametro_llanta2 = $request->tole_diametro_llanta2;
        $tolerance->diametro_caja_corona1 = $request->tole_diametro_caja_corona1;
        $tolerance->diametro_caja_corona2 = $request->tole_diametro_caja_corona2;
        $tolerance->profundidad_corona1 = $request->tole_profundidad_corona1;
        $tolerance->profundidad_corona2 = $request->tole_profundidad_corona2;
        $tolerance->angulo_301 = $request->tole_angulo_301;
        $tolerance->angulo_302 = $request->tole_angulo_302;
        $tolerance->profundidad_caja_corona1 = $request->tole_profundidad_caja_corona1;
        $tolerance->profundidad_caja_corona2 = $request->tole_profundidad_caja_corona2;
        $tolerance->simetria1 = $request->tole_simetria1;
        $tolerance->simetria2 = $request->tole_simetria2;

        $cNominal->save();
        $tolerance->save();
    }
    public function acabadoMolde($id_proceso, $request)
    {
        $cNominal = AcabadoMolde_cnominal::where('id_proceso', $id_proceso)->first();
        $tolerance = AcabadoMolde_tolerancia::where('id_proceso', $id_proceso)->first();
        if (!$cNominal && !$tolerance) {
            $cNominal = new AcabadoMolde_cnominal();
            $tolerance = new AcabadoMolde_tolerancia();
        }
        //Llenado de tabla acabadoMolde_cnominal
        $cNominal->id_proceso = $id_proceso;
        $cNominal->diametro_mordaza = $request->cNomi_diametro_mordaza;
        $cNominal->diametro_ceja = $request->cNomi_diametro_ceja;
        $cNominal->diametro_sufridera = $request->cNomi_diametro_sufridera;
        $cNominal->altura_mordaza = $request->cNomi_altura_mordaza;
        $cNominal->altura_ceja = $request->cNomi_altura_ceja;
        $cNominal->altura_sufridera = $request->cNomi_altura_sufridera;
        $cNominal->diametro_conexion_fondo = $request->cNomi_diametro_conexion_fondo;
        $cNominal->diametro_llanta = $request->cNomi_diametro_llanta;
        $cNominal->diametro_caja_fondo = $request->cNomi_diametro_caja_fondo;
        $cNominal->altura_conexion_fondo = $request->cNomi_altura_conexion_fondo;
        $cNominal->profundidad_llanta = $request->cNomi_profundidad_llanta;
        $cNominal->profundidad_caja_fondo = $request->cNomi_profundidad_caja_fondo;
        $cNominal->simetria = $request->cNomi_simetria;

        //Llenado de tabla acabadoMolde_tolerancia
        $tolerance->id_proceso = $id_proceso;
        $tolerance->diametro_mordaza1 = $request->tole_diametro_mordaza1;
        $tolerance->diametro_mordaza2 = $request->tole_diametro_mordaza2;
        $tolerance->diametro_ceja1 = $request->tole_diametro_ceja1;
        $tolerance->diametro_ceja2 = $request->tole_diametro_ceja2;
        $tolerance->diametro_sufridera1 = $request->tole_diametro_sufridera1;
        $tolerance->diametro_sufridera2 = $request->tole_diametro_sufridera2;
        $tolerance->altura_mordaza1 = $request->tole_altura_mordaza1;
        $tolerance->altura_mordaza2 = $request->tole_altura_mordaza2;
        $tolerance->altura_ceja1 = $request->tole_altura_ceja1;
        $tolerance->altura_ceja2 = $request->tole_altura_ceja2;
        $tolerance->altura_sufridera1 = $request->tole_altura_sufridera1;
        $tolerance->altura_sufridera2 = $request->tole_altura_sufridera2;
        $tolerance->diametro_conexion_fondo1 = $request->tole_diametro_conexion_fondo1;
        $tolerance->diametro_conexion_fondo2 = $request->tole_diametro_conexion_fondo2;
        $tolerance->diametro_llanta1 = $request->tole_diametro_llanta1;
        $tolerance->diametro_llanta2 = $request->tole_diametro_llanta2;
        $tolerance->diametro_caja_fondo1 = $request->tole_diametro_caja_fondo1;
        $tolerance->diametro_caja_fondo2 = $request->tole_diametro_caja_fondo2;
        $tolerance->altura_conexion_fondo1 = $request->tole_altura_conexion_fondo1;
        $tolerance->altura_conexion_fondo2 = $request->tole_altura_conexion_fondo2;
        $tolerance->profundidad_llanta1 = $request->tole_profundidad_llanta1;
        $tolerance->profundidad_llanta2 = $request->tole_profundidad_llanta2;
        $tolerance->profundidad_caja_fondo1 = $request->tole_profundidad_caja_fondo1;
        $tolerance->profundidad_caja_fondo2 = $request->tole_profundidad_caja_fondo2;
        $tolerance->simetria1 = $request->tole_simetria1;
        $tolerance->simetria2 = $request->tole_simetria2;

        $cNominal->save();
        $tolerance->save();
    }
    public function barrenoProfundidad($id_proceso, $request)
    {
        $cNominal = BarrenoProfundidad_cnominal::where('id_proceso', $id_proceso)->first();
        $tolerance = BarrenoProfundidad_tolerancia::where('id_proceso', $id_proceso)->first();
        if (!$cNominal && !$tolerance) {
            $cNominal = new BarrenoProfundidad_cnominal();
            $tolerance = new BarrenoProfundidad_tolerancia();
        }

        //Llenado de tabla barrenoProfundidad cNominal
        $cNominal->id_proceso = $id_proceso;
        $cNominal->broca1 = $request->cNomi_broca1;
        $cNominal->tiempo1 = $request->cNomi_tiempo1;
        $cNominal->broca2 = $request->cNomi_broca2;
        $cNominal->tiempo2 = $request->cNomi_tiempo2;
        $cNominal->broca3 = $request->cNomi_broca3;
        $cNominal->tiempo3 = $request->cNomi_tiempo3;
        $cNominal->entradaSalida = $request->cNomi_entradaSalida;
        $cNominal->diametro_arrastre1 = $request->cNomi_diametro_arrastre1;
        $cNominal->diametro_arrastre2 = $request->cNomi_diametro_arrastre2;
        $cNominal->diametro_arrastre3 = $request->cNomi_diametro_arrastre3;

        //Llenado de tabla barrenoProfundidad tolerancia
        $tolerance->id_proceso = $id_proceso;
        $tolerance->broca1 = $request->tole_broca1;
        $tolerance->tiempo1 = $request->tole_tiempo1;
        $tolerance->broca2 = $request->tole_broca2;
        $tolerance->tiempo2 = $request->tole_tiempo2;
        $tolerance->broca3 = $request->tole_broca3;
        $tolerance->tiempo3 = $request->tole_tiempo3;
        $tolerance->entrada = $request->tole_entrada;
        $tolerance->salida = $request->tole_salida;
        $tolerance->diametro_arrastre1 = $request->tole_diametro_arrastre1;
        $tolerance->diametro_arrastre2 = $request->tole_diametro_arrastre2;
        $tolerance->diametro_arrastre3 = $request->tole_diametro_arrastre3;

        $cNominal->save();
        $tolerance->save();
    }
    public function pysOpeSoldadura($id_proceso, $request)
    {
        $cNominal = PySOpeSoldadura_cnominal::where('id_proceso', $id_proceso)->first();
        $tolerance = PySOpeSoldadura_tolerancia::where('id_proceso', $id_proceso)->first();
        if (!$cNominal && !$tolerance) {
            $cNominal = new PySOpeSoldadura_cnominal();
            $tolerance = new PySOpeSoldadura_tolerancia();
        }

        //Llenado de tabla pysOpeSoldadura_cnominal
        $cNominal->id_proceso = $id_proceso;
        $cNominal->altura = $request->cNomi_altura;
        $cNominal->alturaCandado1 = $request->cNomi_alturaCandado1;
        $cNominal->alturaCandado2 = $request->cNomi_alturaCandado2;
        $cNominal->alturaAsientoObturador1 = $request->cNomi_alturaAsientoObturador1;
        $cNominal->alturaAsientoObturador2 = $request->cNomi_alturaAsientoObturador2;
        $cNominal->profundidadSoldadura1 = $request->cNomi_profundidadSoldadura1;
        $cNominal->profundidadSoldadura2 = $request->cNomi_profundidadSoldadura2;
        $cNominal->pushUp = $request->cNomi_pushUp;

        //Llenado de tabla pysOpeSoldadura_tolerancia
        $tolerance->id_proceso = $id_proceso;
        $tolerance->altura = $request->tole_altura;
        $tolerance->alturaCandado1 = $request->tole_alturaCandado1;
        $tolerance->alturaCandado2 = $request->tole_alturaCandado2;
        $tolerance->alturaAsientoObturador1 = $request->tole_alturaAsientoObturador1;
        $tolerance->alturaAsientoObturador2 = $request->tole_alturaAsientoObturador2;
        $tolerance->profundidadSoldadura1 = $request->tole_profundidadSoldadura1;
        $tolerance->profundidadSoldadura2 = $request->tole_profundidadSoldadura2;
        $tolerance->pushUp = $request->tole_pushUp;

        $cNominal->save();
        $tolerance->save();
    }
    public function cavidades($id_proceso, $request)
    {
        $cNominal = Cavidades_cnominal::where('id_proceso', $id_proceso)->first();
        $tolerance = Cavidades_tolerancia::where('id_proceso', $id_proceso)->first();
        if (!$cNominal && !$tolerance) {
            $cNominal = new Cavidades_cnominal();
            $tolerance = new Cavidades_tolerancia();
        }

        //Llenado de tabla cavidades_cnominal
        $cNominal->id_proceso = $id_proceso; //Llenado de id_proceso para tabla cavidades_cnominal.
        $cNominal->profundidad1 = $request->cNomi_profundidad1;
        $cNominal->diametro1 = $request->cNomi_diametro1;
        $cNominal->profundidad2 = $request->cNomi_profundidad2;
        $cNominal->diametro2 = $request->cNomi_diametro2;
        $cNominal->profundidad3 = $request->cNomi_profundidad3;
        $cNominal->diametro3 = $request->cNomi_diametro3;
        $cNominal->altura1 = $request->cNomi_altura1;
        $cNominal->altura2 = $request->cNomi_altura2;
        $cNominal->altura3 = $request->cNomi_altura3;


        //Llenado de tabla cavidades_tolerancia
        $tolerance->id_proceso = $id_proceso;
        $tolerance->profundidad1_1 = $request->tole_profundidad1_1;
        $tolerance->profundidad2_1 = $request->tole_profundidad2_1;
        $tolerance->diametro1_1 = $request->tole_diametro1_1;
        $tolerance->diametro2_1 = $request->tole_diametro2_1;
        $tolerance->profundidad1_2 = $request->tole_profundidad1_2;
        $tolerance->profundidad2_2 = $request->tole_profundidad2_2;
        $tolerance->diametro1_2 = $request->tole_diametro1_2;
        $tolerance->diametro2_2 = $request->tole_diametro2_2;
        $tolerance->profundidad1_3 = $request->tole_profundidad1_3;
        $tolerance->profundidad2_3 = $request->tole_profundidad2_3;
        $tolerance->diametro1_3 = $request->tole_diametro1_3;
        $tolerance->diametro2_3 = $request->tole_diametro2_3;
        $tolerance->altura1 = $request->tole_altura1;
        $tolerance->altura2 = $request->tole_altura2;
        $tolerance->altura3 = $request->tole_altura3;

        $cNominal->save();
        $tolerance->save();
    }
    public function copiado($id_proceso, $request)
    {
        $cNominal = Copiado_cnominal::where('id_proceso', $id_proceso)->first();
        $tolerance = Copiado_tolerancia::where('id_proceso', $id_proceso)->first();
        if (!$cNominal && !$tolerance) {
            $cNominal = new Copiado_cnominal();
            $tolerance = new Copiado_tolerancia();
        }

        if ($request->subProcess == 'Cilindrado') {
            //Llenado de tabla copiado_cnominal en el subproceso Cilindrado.
            $cNominal->id_proceso = $id_proceso;
            $cNominal->diametro1_cilindrado = $request->cNomi_diametro1_cilindrado;
            $cNominal->profundidad1_cilindrado = $request->cNomi_profundidad1_cilindrado;
            $cNominal->diametro2_cilindrado = $request->cNomi_diametro2_cilindrado;
            $cNominal->profundidad2_cilindrado = $request->cNomi_profundidad2_cilindrado;
            $cNominal->diametro_sufridera = $request->cNomi_diametro_sufridera;
            $cNominal->diametro_ranura = $request->cNomi_diametro_ranura;
            $cNominal->profundidad_ranura = $request->cNomi_profundidad_ranura;
            $cNominal->profundidad_sufridera = $request->cNomi_profundidad_sufridera;
            $cNominal->altura_Total = $request->cNomi_altura_total;

            //Llenado de tabla copiado_tolerancia en el subproceso Cilindrado.
            $tolerance->id_proceso = $id_proceso;
            $tolerance->diametro1_cilindrado = $request->tole_diametro1_cilindrado;
            $tolerance->profundidad1_cilindrado = $request->tole_profundidad1_cilindrado;
            $tolerance->diametro2_cilindrado = $request->tole_diametro2_cilindrado;
            $tolerance->profundidad2_cilindrado = $request->tole_profundidad2_cilindrado;
            $tolerance->diametro_sufridera = $request->tole_diametro_sufridera;
            $tolerance->diametro_ranura = $request->tole_diametro_ranura;
            $tolerance->profundidad_ranura = $request->tole_profundidad_ranura;
            $tolerance->profundidad_sufridera = $request->tole_profundidad_sufridera;
            $tolerance->altura_total = $request->tole_altura_total;
        } else {
            $cNominal->id_proceso = $id_proceso; //Llenado de id_proceso para tabla copiado_cnominal en el subproceso Cavidades.
            $cNominal->diametro1_cavidades = $request->cNomi_diametro1_cavidades;
            $cNominal->profundidad1_cavidades = $request->cNomi_profundidad1_cavidades;
            $cNominal->diametro2_cavidades = $request->cNomi_diametro2_cavidades;
            $cNominal->profundidad2_cavidades = $request->cNomi_profundidad2_cavidades;
            $cNominal->diametro3 = $request->cNomi_diametro3;
            $cNominal->profundidad3 = $request->cNomi_profundidad3;
            $cNominal->diametro4 = $request->cNomi_diametro4;
            $cNominal->profundidad4 = $request->cNomi_profundidad4;
            $cNominal->volumen = $request->cNomi_volumen;

            //Llenado de tabla copiado_tolerancia en el subproceso Cavidades.
            $tolerance->id_proceso = $id_proceso;
            $tolerance->diametro1_cavidades = $request->tole_diametro1_cavidades;
            $tolerance->profundidad1_cavidades = $request->tole_profundidad1_cavidades;
            $tolerance->diametro2_cavidades = $request->tole_diametro2_cavidades;
            $tolerance->profundidad2_cavidades = $request->tole_profundidad2_cavidades;
            $tolerance->diametro3 = $request->tole_diametro3;
            $tolerance->profundidad3 = $request->tole_profundidad3;
            $tolerance->diametro4 = $request->tole_diametro4;
            $tolerance->profundidad4 = $request->tole_profundidad4;
            $tolerance->volumen = $request->tole_volumen;
        }
        $cNominal->save();
        $tolerance->save();
    }
    public function offSet($id_proceso, $request)
    {
        $cNominal = OffSet_cnominal::where('id_proceso', $id_proceso)->first();
        $tolerance = OffSet_tolerancia::where('id_proceso', $id_proceso)->first();
        if (!$cNominal && !$tolerance) {
            $cNominal = new OffSet_cnominal();
            $tolerance = new OffSet_tolerancia();
        }

        //Llenado de tabla OffSet_cnominal
        $cNominal->id_proceso = $id_proceso; //Llenado de id_proceso para tabla OffSet_cnominal.
        $cNominal->anchoRanura = $request->cNomi_anchoRanura;
        $cNominal->profuTaconHembra = $request->cNomi_profuTaconHembra;
        $cNominal->profuTaconMacho = $request->cNomi_profuTaconMacho;
        $cNominal->simetriaHembra = $request->cNomi_simetriaHembra;
        $cNominal->simetriaMacho = $request->cNomi_simetriaMacho;
        $cNominal->anchoTacon = $request->cNomi_anchoTacon;
        $cNominal->barrenoLateralHembra = $request->cNomi_barrenoLateralHembra;
        $cNominal->barrenoLateralMacho = $request->cNomi_barrenoLateralMacho;
        $cNominal->alturaTaconInicial = $request->cNomi_alturaTaconInicial;
        $cNominal->alturaTaconIntermedia = $request->cNomi_alturaTaconIntermedia;

        //Llenado de tabla OffSet_tolerancia
        $tolerance->id_proceso = $id_proceso;
        $tolerance->anchoRanura = $request->tole_anchoRanura;
        $tolerance->profuTaconHembra = $request->tole_profuTaconHembra;
        $tolerance->profuTaconMacho = $request->tole_profuTaconMacho;
        $tolerance->simetriaHembra = $request->tole_simetriaHembra;
        $tolerance->simetriaMacho = $request->tole_simetriaMacho;
        $tolerance->anchoTacon = $request->tole_anchoTacon;
        $tolerance->barrenoLateralHembra = $request->tole_barrenoLateralHembra;
        $tolerance->barrenoLateralMacho = $request->tole_barrenoLateralMacho;
        $tolerance->alturaTaconInicial = $request->tole_alturaTaconInicial;
        $tolerance->alturaTaconIntermedia = $request->tole_alturaTaconIntermedia;

        $cNominal->save();
        $tolerance->save();
    }
    public function palomas($id_proceso, $request)
    {
        $cNominal = Palomas_cnominal::where('id_proceso', $id_proceso)->first();
        $tolerance = Palomas_tolerancia::where('id_proceso', $id_proceso)->first();
        if (!$cNominal && !$tolerance) {
            $cNominal = new Palomas_cnominal();
            $tolerance = new Palomas_tolerancia();
        }

        //Llenado de tabla Palomas_cnominal
        $cNominal->id_proceso = $id_proceso;
        $cNominal->anchoPaloma = $request->cNomi_anchoPaloma;
        $cNominal->gruesoPaloma = $request->cNomi_gruesoPaloma;
        $cNominal->profundidadPaloma = $request->cNomi_profundidadPaloma;
        $cNominal->rebajeLlanta = $request->cNomi_rebajeLlanta;

        //Llenado de tabla Palomas_tolerancia
        $tolerance->id_proceso = $id_proceso;
        $tolerance->anchoPaloma = $request->tole_anchoPaloma;
        $tolerance->gruesoPaloma = $request->tole_gruesoPaloma;
        $tolerance->profundidadPaloma = $request->tole_profundidadPaloma;
        $tolerance->rebajeLlanta = $request->tole_rebajeLlanta;

        $cNominal->save();
        $tolerance->save();
    }
    public function rebajes($id_proceso, $request)
    {
        $cNominal = Rebajes_cnominal::where('id_proceso', $id_proceso)->first();
        $tolerance = Rebajes_tolerancia::where('id_proceso', $id_proceso)->first();
        if (!$cNominal && !$tolerance) {
            $cNominal = new Rebajes_cnominal();
            $tolerance = new Rebajes_tolerancia();
        }

        //Llenado de tabla Rebajes_cnominal
        $cNominal->id_proceso = $id_proceso;
        $cNominal->rebaje1 = $request->cNomi_rebaje1;
        $cNominal->rebaje2 = $request->cNomi_rebaje2;
        $cNominal->rebaje3 = $request->cNomi_rebaje3;
        $cNominal->profundidad_bordonio = $request->cNomi_profundidad_bordonio;
        $cNominal->vena1 = $request->cNomi_vena1;
        $cNominal->vena2 = $request->cNomi_vena2;
        $cNominal->simetria = $request->cNomi_simetria;

        //Llenado de tabla Rebajes_tolerancia
        $tolerance->id_proceso = $id_proceso;
        $tolerance->rebaje1 = $request->tole_rebaje1;
        $tolerance->rebaje2 = $request->tole_rebaje2;
        $tolerance->rebaje3 = $request->tole_rebaje3;
        $tolerance->profundidad_bordonio = $request->tole_profundidad_bordonio;
        ;
        $tolerance->vena1 = $request->tole_vena1;
        $tolerance->vena2 = $request->tole_vena2;
        $tolerance->simetria = $request->tole_simetria;

        $cNominal->save();
        $tolerance->save();
    }
    public function embudoCM($id_proceso, $request)
    {
        $cNominal = EmbudoCM_cnominal::where('id_proceso', $id_proceso)->first();
        $tolerance = EmbudoCM_tolerancias::where('id_proceso', $id_proceso)->first();
        if (!$cNominal && !$tolerance) {
            $cNominal = new EmbudoCM_cnominal();
            $tolerance = new EmbudoCM_tolerancias();
        }

        //Llenado de tabla Palomas_cnominal
        $cNominal->id_proceso = $id_proceso; //Llenado de id_proceso para tabla Palomas_cnominal.
        $cNominal->conexion_lineaPartida = $request->cNomi_conexion_lineaPartida;
        $cNominal->conexion_90G = $request->cNomi_conexion_90G;
        $cNominal->altura_conexion = $request->cNomi_altura_conexion;
        $cNominal->diametro_embudo = $request->cNomi_diametro_embudo;

        //Llenado de tabla Palomas_tolerancia
        $tolerance->id_proceso = $id_proceso;
        $tolerance->conexion_lineaPartida = $request->tole_conexion_lineaPartida;
        $tolerance->conexion_90G = $request->tole_conexion_90G;
        $tolerance->altura_conexion = $request->tole_altura_conexion;
        $tolerance->diametro_embudo = $request->tole_diametro_embudo;

        $cNominal->save(); //Guardado de datos en tabla Palomas_cnominal.
        $tolerance->save(); //Guardado de datos en tabla Palomas_tolerancia.
    }
}
