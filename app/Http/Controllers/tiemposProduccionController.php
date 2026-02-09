<?php

namespace App\Http\Controllers;

use App\Models\Clase;
use App\Models\Metas;
use App\Models\Moldura;
use App\Models\Orden_trabajo;
use App\Models\Procesos;
use App\Models\tiempoproduccion;
use Carbon\Carbon;
use Illuminate\Http\Request;
use PhpParser\Node\Stmt\Break_;

class tiemposProduccionController extends Controller
{
    protected $controladorPzas;
    protected $classController;
    public function __construct()
    {
        $this->controladorPzas = new PzasLiberadasController();
        $this->classController = new ClassController();
        $this->middleware('auth');
    }
    public function show($clase = false)
    {

        $wOrdersFounded = Orden_trabajo::all();
        $workOrders = array();
        if (count($wOrdersFounded) > 0) {
            foreach ($wOrdersFounded as $workOrder) {
                $classes = $this->classController->getClasses($workOrder);
                if (count($classes) > 0) {
                    $workOrders[$workOrder->id] = array();
                    foreach ($classes as $class) {
                        $workOrders[$workOrder->id][$class->nombre] = array();
                        $tiemposProduccion = tiempoproduccion::where('id_clase', $class->id)->get();
                        if ($tiemposProduccion->count() > 0) {
                            foreach ($tiemposProduccion as $tiempo) {
                                // Inicializar el array si no existe
                                $workOrders[$workOrder->id][$class->nombre][$tiempo->proceso] = $workOrders[$workOrder->id][$class->nombre][$tiempo->proceso] ?? [];
                                // Inicializar el array de tiempos si no existe
                                foreach ($tiempo->toArray() as $columna => $valor) {
                                    if ($columna == 'id_clase' || $columna == 'clase' || $columna == 'proceso' || $columna == 'tamanio' || $columna == 'created_at' || $columna == 'updated_at') {
                                        continue;
                                    }
                                    $workOrders[$workOrder->id][$class->nombre][$tiempo->proceso][$columna] = $valor;
                                }
                            }
                        } else {
                            $workOrders[$workOrder->id][$class->nombre] = null;
                        }
                    }
                }
            }
        }

        if ($clase) {
            return view('processes_views.productionTimes', compact('workOrders', 'clase'));
        }
        return view('processes_views.productionTimes', compact('workOrders'));
    }
    public function getProductionTimes($class)
    {
        switch ($class->nombre) {
            case "Bombillo":
                return match ($class->tamanio) {
                    'Chico' => ['Cepillado' => 35, 'Desbaste Exterior' => 26, 'Revision Laterales' => 20, 'Primera Operacion' => 24, 'Barreno Maniobra' => 15, 'Segunda Operacion' => 24, 'Soldadura' => 24, 'Soldadura PTA' => 24, 'Rectificado' => 12, 'Asentado' => 20, 'Calificado' => 22, 'Acabado Bombillo' => 25, 'Barreno Profundidad' => 27, 'Cavidades' => 42, 'Copiado' => 27, 'Off Set' => 16, 'Palomas' => 12, 'Rebajes' => 20, 'Grabado' => 12,],

                    'Mediano' => ['Cepillado' => 60, 'Desbaste Exterior' => 30, 'Revision Laterales' => 24, 'Primera Operacion' => 28, 'Barreno Maniobra' => 15, 'Segunda Operacion' => 28, 'Soldadura' => 30, 'Soldadura PTA' => 30, 'Rectificado' => 13, 'Asentado' => 24, 'Calificado' => 24, 'Acabado Bombillo' => 27, 'Barreno Profundidad' => 40, 'Cavidades' => 34, 'Copiado' => 29, 'Off Set' => 16, 'Palomas' => 12, 'Rebajes' => 20, 'Grabado' => 12,],

                    'Grande' => ['Cepillado' => 90, 'Desbaste Exterior' => 35, 'Revision Laterales' => 26, 'Primera Operacion' => 30, 'Barreno Maniobra' => 15, 'Segunda Operacion' => 28, 'Soldadura' => 34, 'Soldadura PTA' => 34, 'Rectificado' => 14, 'Asentado' => 30, 'Calificado' => 26, 'Acabado Bombillo' => 28, 'Barreno Profundidad' => 60, 'Cavidades' => 26, 'Copiado' => 0, 'Off Set' => 0, 'Palomas' => 0, 'Rebajes' => 0, 'Grabado' => 0,],
                    default => null,
                };

            case "Molde":
                return match ($class->tamanio) {
                    'Chico' => ['Cepillado' => 53, 'Desbaste Exterior' => 26, 'Revision Laterales' => 20, 'Primera Operacion' => 20, 'Barreno Maniobra' => 15, 'Segunda Operacion' => 24, 'Soldadura' => 24, 'Soldadura PTA' => 24, 'Rectificado' => 12, 'Asentado' => 20, 'Calificado' => 22, 'Acabado Molde' => 24, 'Barreno Profundidad' => 28, 'Cavidades' => 21, 'Copiado' => 0, 'Off Set' => 0, 'Palomas' => 0, 'Rebajes' => 0, 'Grabado' => 0,],

                    'Mediano' => ['Cepillado' => 64, 'Desbaste Exterior' => 30, 'Revision Laterales' => 24, 'Primera Operacion' => 24, 'Barreno Maniobra' => 15, 'Segunda Operacion' => 28, 'Soldadura' => 30, 'Soldadura PTA' => 30, 'Rectificado' => 13, 'Asentado' => 24, 'Calificado' => 24, 'Acabado Molde' => 26, 'Barreno Profundidad' => 40, 'Cavidades' => 17, 'Copiado' => 0, 'Off Set' => 0, 'Palomas' => 0, 'Rebajes' => 0, 'Grabado' => 0,],

                    'Grande' => ['Cepillado' => 120, 'Desbaste Exterior' => 35, 'Revision Laterales' => 26, 'Primera Operacion' => 26, 'Barreno Maniobra' => 15, 'Segunda Operacion' => 30, 'Soldadura' => 70, 'Soldadura PTA' => 70, 'Rectificado' => 20, 'Asentado' => 30, 'Calificado' => 26, 'Acabado Molde' => 30, 'Barreno Profundidad' => 90, 'Cavidades' => 13, 'Copiado' => 0, 'Off Set' => 0, 'Palomas' => 0, 'Rebajes' => 0, 'Grabado' => 0,],
                    default => null,
                };
            case "Obturador":
            // return match ($class->tamanio) {
            //      'Chico' => [],
            //      'Mediano' => [],
            //      'Grande' => [],
            //      default => null,
            // };
            case "Fondo":
            // return match ($class->tamanio) {
            //     'Chico' => [],
            //     'Mediano' => [],
            //     'Grande' => [],
            //     default => null,
            // };
            case "Plato":
            // return match ($class->tamanio) {
            //     'Chico' => [],
            //     'Mediano' => [],
            //     'Grande' => [],
            //     default => null,
            // };
            case "Embudo":
            // return match ($class->tamanio) {
            //     'Chico' => [],
            //     'Mediano' => [],
            //     'Grande' => [],
            //     default => null,
            // };
            case "Corona":
                return match ($class->tamanio) {
                    'Chico', 'Mediano', 'Grande' => ['Operacion Equipo' => 24, 'Soldadura' => 30, 'Soldadura PTA' => 15],
                    default => null,
                };
            default:
                return null;
        }
    }


    //Funcion para visualizar los tiempos productivos
    public function setProductionTimes($class)
    {
        $productionTimes = $this->getProductionTimes($class);
        if ($productionTimes != null) {
            foreach ($productionTimes as $process => $time) {
                $processName = $this->get_processName($process);
                $tiempo = tiempoproduccion::where('id_clase', $class->id)->where('proceso', $processName)->first();
                if ($tiempo) {
                    if ($tiempo->tamanio == $class->tamanio) {
                        $tiempo->tiempo = $tiempo->tiempo != 0 ? $tiempo->tiempo : $time;
                    } else {
                        $tiempo->tiempo = $time;
                    }
                    $tiempo->tamanio = $class->tamanio;
                } else {
                    $tiempo = new tiempoproduccion();
                    $tiempo->id_clase = $class->id;
                    $tiempo->clase = $class->nombre;
                    $tiempo->tamanio = $class->tamanio;
                    $tiempo->proceso = $processName;
                    $tiempo->tiempo = $time;
                }
                $tiempo->save();
            }
        }
    }
    public function store(Request $request)
    {
        $productionTimes = $this->getProductionTimes(Clase::where('nombre', $request->input('class'))->where("id_ot", $request->input('workOrder'))->first());
        foreach ($request->all() as $key => $value) {
            if ($key == '_token' || $key == "class" || $key == "workOrder") {
                continue;
            }
            $class = Clase::where('nombre', $request->input('class'))->where("id_ot", $request->input('workOrder'))->first();
            $tiempo = tiempoproduccion::where('id_clase', $class->id)->where('proceso', $key)->first();

            $processName = $this->get_processNormalName($key);
            if ($tiempo) {
                $tiempo->tamanio = $class->tamanio;
                $tiempo->tiempo = $value != 0 ? $value : ($productionTimes[$processName]) ?? 0;
            } else {
                $tiempo = new tiempoproduccion();
                $tiempo->id_clase = $class->id;
                $tiempo->clase = $request->input('class');
                $tiempo->tamanio = $class->tamanio;
                $tiempo->proceso = $key;
                $tiempo->tiempo = $value != 0 ? $value : ($productionTimes[$processName]) ?? 0;
            }
            $tiempo->save();
        }
        $clase = $request->input('class');

        //Actualizar todas las Clases
        $this->update();
        return redirect()->route("showTimes", compact('clase'))->with('success', 'Tiempos de producción actualizados correctamente.');
    }
    public function update()
    {
        $clases = $this->guardarClasesInArray();
        if ($clases != null) {
            //Se hace el algoritmo
            foreach ($clases as $clase) {
                //Se obtienen los procesos de la clase
                $procesos = $this->asignarProcesos($clase[0]->nombre);
                if ($procesos != null) {
                    $this->calcularFechas($procesos, $clase);
                    $this->updateMetas();
                }
            }
        }
    }
    public function guardarClasesInArray()
    {
        //Se obtienen todas las clases de la tabla fechas_procesos
        $idClase = Procesos::select('id_clase')->distinct()->get();

        if ($idClase->count() == 0) {
            return null;
        }
        //Se guardan los procesos de cada clase en una array bidimensional
        $contadorClases = 0;
        $clases = array();
        foreach ($idClase as $id) {
            //Obtener la clase por el id
            $clase = Clase::find($id->id_clase);
            $clases[$contadorClases] = array();
            $clases[$contadorClases][0] = $clase;
            $clases[$contadorClases][1] = array();
            // $clases[$contadorClases][0] = $clase->id; //Distinguir como se conforma el array

            //Obtener todos los procesos creados (tiempos) de esa clase
            $procesos = $this->getProcesos($clase);
            $clases[$contadorClases][1] = $procesos;
            $contadorClases++;
        }
        return $clases;
    }
    public function asignarProcesos($clase)
    {
        switch ($clase) {
            case "Bombillo":
                return array("cepillado", "desbaste_exterior", "revision_laterales", "pOperacion", "barreno_maniobra", "sOperacion", "soldadura", "soldaduraPTA", "rectificado", "asentado", "calificado", "acabadoBombillo", "barreno_profundidad", "cavidades", "copiado", "offSet", "palomas", "rebajes");
            case "Molde":
                return array("cepillado", "desbaste_exterior", "revision_laterales", "pOperacion", "barreno_maniobra", "sOperacion", "soldadura", "soldaduraPTA", "rectificado", "asentado", "calificado", "acabadoMolde", "barreno_profundidad", "cavidades", "copiado", "offSet", "palomas", "rebajes");
            case "Obturador":
            case "Fondo":
                return array("operacionEquipo", "soldadura", "soldaduraPTA");
                break;
            case "Corona":
                return array("cepillado", "desbaste_exterior");
            case "Plato":
                return array("operacionEquipo", "barreno_profundidad", "soldaduraPTA");
            case "Embudo":
                return array("operacionEquipo", "embudoCM");
            default:
                return;
        }
    }
    public function getProcesos($clase)
    {
        $registroProcesos = Procesos::where('id_clase', $clase->id)->first();
        if ($registroProcesos) {
            $columnas = $registroProcesos->getAttributes();

            $procesos = array_keys(array_filter($columnas, function ($value) {
                return $value != 0;
            }));

            //Eliminar los campos que no son procesos
            $procesos = array_slice($procesos, 2);
            return $procesos;
        }
    }
    public function calcularFechas($procesos, $clase)
    {
        $noProceso = 0;
        for ($i = 0; $i < count($procesos); $i++) {
            $pos = array_search($procesos[$i], $clase[1]);
            if ($pos !== false) {
                $maquinas = $this->obtenerMaquinasClase($clase[0]->id, $clase[1][$pos]);
                $procesoFechas = $this->classController->registerProcessDates($clase[0], $procesos, $i, $noProceso, $maquinas);
                $noProceso++;
            }
        }

        //Guardar unicamente la fecha de termino
        $clase = Clase::find($clase[0]->id);
        $clase->fecha_termino = Carbon::parse($procesoFechas->fecha_fin)->format('Y-m-d');
        $clase->hora_termino = Carbon::parse($procesoFechas->fecha_fin)->format('H:i:s');
        // echo $clase->nombre;
        // echo $clase->fecha_termino;
        // echo $clase->hora_termino;
        // echo "<br>";
        $clase->save();
    }
    public function obtenerMaquinasClase($claseID, $proceso)
    {
        $maquinas = Procesos::where('id_clase', $claseID)->distinct()->value($proceso);
        return $maquinas;
    }
    public function updateMetas()
    {
        $metas = Metas::all();
        if ($metas->count() > 0) {
            foreach ($metas as $meta) {
                //Asignar tiempo estándar
                $processName = $this->get_processName($meta->proceso);
                $tiempo = tiempoproduccion::where('id_clase', $meta->id_clase)->where('proceso', $processName)->first();
                $meta->t_estandar = $tiempo->tiempo ?? 0;

                //Calcular las horas de trabajo de cada operador
                if ($tiempo) {
                    $workHrs = $this->calculateHrs($meta->h_inicio, $meta->h_termino);
                    $tiempo = $tiempo->tiempo != 0 ? round(($workHrs / $tiempo->tiempo)) : 0;
                    $meta->meta = $tiempo; //Asignar la meta calculada
                } else {
                    $meta->meta = 0; //Si no se encuentra el tiempo, se asigna 0 a la meta
                }
                $meta->save(); //Guardar los cambios en la base de datos
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

    public function get_processName($processName)
    {
        $process = match ($processName) {
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
            'Operacion Equipo' => 'operacionEquipo',
            'Operacion Equipo_1 operacion' => 'operacionEquipo',
            'Operacion Equipo_2 operacion' => 'operacionEquipo',
            'Embudo CM' => 'embudoCM',
            'Soldadura' => 'soldadura',
            'Soldadura PTA' => 'soldaduraPTA',
        };
        return $process;
    }

    public function get_processNormalName($processName)
    {
        $process = match ($processName) {
            'cepillado' => 'Cepillado',
            'desbaste' => 'Desbaste Exterior',
            'revLaterales' => 'Revision Laterales',
            'primeraOpeSoldadura' => 'Primera Operacion',
            'barrenoManiobra' => 'Barreno Maniobra',
            'segundaOpeSoldadura' => 'Segunda Operacion',
            'rectificado' => 'Rectificado',
            'asentado' => 'Asentado',
            'revCalificado' => 'Calificado',
            'acabadoBombillo' => 'Acabado Bombillo',
            'acabadoMolde' => 'Acabado Molde',
            'barrenoProfundidad' => 'Barreno Profundidad',
            'cavidades' => 'Cavidades',
            'copiado' => 'Copiado',
            'offset' => 'Off Set',
            'palomas' => 'Palomas',
            'rebajes' => 'Rebajes',
            'grabado' => 'Grabado',
            'operacionEquipo' => 'Operacion Equipo',
            'operacionEquipo_1' => 'Operacion Equipo_1',
            'operacionEquipo_2' => 'Operacion Equipo_2',
            'embudoCM' => 'Embudo CM',
            'soldadura' => 'Soldadura',
            'soldaduraPTA' => 'Soldadura PTA',
        };
        return $process;
    }

    /**
     * ========================================================================
     * SISTEMA DE ESTIMACIÓN DE TIEMPOS DE PRODUCCIÓN - FLUJO CONTINUO
     * ========================================================================
     *
     * Modelo basado en TASAS DE PRODUCCIÓN (piezas/minuto), NO en lotes.
     * Refleja el comportamiento real de una línea de manufactura:
     *
     * - Flujo continuo pieza a pieza
     * - Dependencias estrictas entre procesos
     * - Arranque por primera pieza
     * - Cuellos de botella que limitan el sistema completo
     * - Tiempos muertos reales (NO ajustes mágicos)
     * - Cálculo correcto del tiempo total del sistema
     *
     * FÓRMULA CLAVE:
     * tiempo_total = tiempo_primera_pieza_total + (cantidad - 1) / tasa_cuello_botella
     */

    /**
     * Calcula el tiempo total estimado de producción usando modelo de flujo continuo
     *
     * @param int $cantidadPiezas Número de piezas a producir
     * @param array $procesosConfig Configuración de procesos
     * @param Carbon $fechaInicio Fecha y hora de inicio de producción
     * @return array Resultado completo con tiempos, fechas, detalles y datos para UI
     */
    public function calcularTiempoProduccionRealista($cantidadPiezas, $procesosConfig, $fechaInicio = null)
    {
        if ($fechaInicio === null) {
            $fechaInicio = Carbon::now();
        }

        // Validar que estamos en horario laboral
        $fechaInicio = $this->ajustarAInicioTurno($fechaInicio);

        // PASO 1: Calcular tasas de producción para cada proceso
        $procesosConTasas = [];
        foreach ($procesosConfig as $proceso) {
            $tasa = $this->calcularTasaProduccion($proceso['tiempo_por_pieza'], $proceso['maquinas']);
            $procesosConTasas[] = array_merge($proceso, ['tasa_produccion' => $tasa]);
        }

        // PASO 2: Calcular tasas efectivas acumuladas
        // La tasa efectiva de cada proceso es el mínimo entre su tasa propia y la tasa efectiva del proceso anterior
        $tasasEfectivas = [];
        foreach ($procesosConTasas as $i => $proceso) {
            if ($i === 0) {
                $tasasEfectivas[$i] = $proceso['tasa_produccion'];
            } else {
                $tasasEfectivas[$i] = min($proceso['tasa_produccion'], $tasasEfectivas[$i - 1]);
            }
            $procesosConTasas[$i]['tasa_efectiva'] = $tasasEfectivas[$i];
        }

        // PASO 3: Identificar el cuello de botella (menor tasa EFECTIVA)
        $cuelloBotella = $this->identificarCuelloBotellaReal($procesosConTasas);
        $tasaCuelloBotella = $cuelloBotella['tasa_efectiva'];

        // PASO 4: Calcular tiempo hasta que sale la primera pieza del último proceso
        // NOTA: Este cálculo NO se divide entre máquinas porque representa el tiempo secuencial
        // hasta que la primera pieza completa TODOS los procesos, no producción por lote
        $tiemposPrimeraPieza = [];

        foreach ($procesosConTasas as $i => $proceso) {
            if ($i === 0) {
                // Primer proceso: la primera pieza tarda el tiempo completo
                $tiemposPrimeraPieza[$i] = $proceso['tiempo_por_pieza'];
            } else {
                // Procesos siguientes: deben esperar la primera pieza del anterior + procesar su primera pieza
                $tiemposPrimeraPieza[$i] = $tiemposPrimeraPieza[$i - 1] + $proceso['tiempo_por_pieza'];
            }
        }
        $tiempoPrimeraPiezaTotal = end($tiemposPrimeraPieza);

        // PASO 5: Calcular tiempo total del sistema (FÓRMULA CORRECTA)
        // tiempo_total = tiempo_primera_pieza + (piezas_restantes / tasa_cuello_botella)
        $tiempoProduccionRestante = ($cantidadPiezas - 1) / $tasaCuelloBotella;
        $tiempoTotalMinutos = $tiempoPrimeraPiezaTotal + $tiempoProduccionRestante;

        // PASO 6: Simular el flujo proceso por proceso para obtener detalles y tiempos muertos
        $detalleProcesos = [];

        foreach ($procesosConTasas as $i => $proceso) {
            // Determinar cuándo inicia este proceso
            if ($i === 0) {
                $inicioReal = clone $fechaInicio;
            } else {
                // Inicia cuando sale la primera pieza del proceso anterior
                $inicioReal = $this->sumarMinutosLaborales(
                    $fechaInicio,
                    $tiemposPrimeraPieza[$i - 1]
                );
            }

            // Calcular tiempos usando tasa efectiva acumulada
            $tasaEfectiva = $proceso['tasa_efectiva'];
            $tasaProduccion = $proceso['tasa_produccion'];

            // Tiempo teórico: lo que tardaría trabajando a su propia tasa
            $tiempoTeorico = $cantidadPiezas / $tasaProduccion;

            // Tiempo real: lo que realmente tarda limitado por la tasa efectiva del sistema
            $tiempoTrabajoEfectivo = $cantidadPiezas / $tasaEfectiva;

            // Tiempo muerto: diferencia entre tiempo real y teórico
            $tiempoMuerto = max(0, $tiempoTrabajoEfectivo - $tiempoTeorico);

            // Es cuello de botella si su tasa efectiva es la menor del sistema
            $esCuelloBotella = ($tasaEfectiva == $tasaCuelloBotella);

            // Calcular fin del proceso
            $finReal = $this->sumarMinutosLaborales($inicioReal, $tiempoTrabajoEfectivo);

            // Calcular cuántas piezas están en cada estado (para UI)
            // NOTA: Estado estimado inicial, NO seguimiento en tiempo real
            $estadoPiezas = $this->calcularEstadoPiezasProceso(
                $cantidadPiezas,
                $proceso['maquinas'],
                0,
                $tiempoTrabajoEfectivo
            );

            // Calcular utilización correcta: (tiempo_teorico / tiempo_real) * 100
            // Cuello de botella = 100%, procesos más rápidos < 100%
            $porcentajeUtilizacion = $tiempoTrabajoEfectivo > 0
                ? round(($tiempoTeorico / $tiempoTrabajoEfectivo) * 100, 2)
                : 100;

            $detalleProcesos[] = [
                'nombre' => $proceso['nombre'],
                'inicio' => $inicioReal,
                'fin' => $finReal,
                'tiempo_por_pieza' => $proceso['tiempo_por_pieza'],
                'maquinas' => $proceso['maquinas'],
                'tasa_produccion' => $tasaProduccion,
                'tasa_efectiva' => $tasaEfectiva,
                'tasa_produccion_por_hora' => round($tasaProduccion * 60, 2),
                'tasa_efectiva_por_hora' => round($tasaEfectiva * 60, 2),
                'tiempo_trabajo_efectivo_minutos' => round($tiempoTrabajoEfectivo, 2),
                'tiempo_teorico_minutos' => round($tiempoTeorico, 2),
                'tiempo_muerto_minutos' => round($tiempoMuerto, 2),
                'es_cuello_botella' => $esCuelloBotella,
                'tiempo_primera_pieza' => $tiemposPrimeraPieza[$i],
                'estado_piezas' => $estadoPiezas,
                'porcentaje_utilizacion' => $porcentajeUtilizacion
            ];
        }

        // PASO 7: Calcular fecha final considerando turnos
        $fechaFin = $this->sumarMinutosLaborales($fechaInicio, $tiempoTotalMinutos);

        // PASO 8: Calcular tiempo muerto total del sistema
        $tiempoMuertoTotal = array_sum(array_column($detalleProcesos, 'tiempo_muerto_minutos'));

        // PASO 9: Preparar datos para UI
        $datosUI = $this->prepararDatosParaUI(
            $cantidadPiezas,
            $detalleProcesos,
            $cuelloBotella,
            $fechaInicio,
            $fechaFin,
            $tiempoTotalMinutos
        );

        return [
            // Tiempos
            'tiempo_total_minutos' => round($tiempoTotalMinutos, 2),
            'tiempo_total_horas' => round($tiempoTotalMinutos / 60, 2),
            'tiempo_total_dias_laborales' => round($tiempoTotalMinutos / 480, 2), // 8 horas/día
            'tiempo_primera_pieza_minutos' => round($tiempoPrimeraPiezaTotal, 2),
            'tiempo_muerto_total_minutos' => round($tiempoMuertoTotal, 2),

            // Fechas
            'fecha_inicio' => $fechaInicio,
            'fecha_fin' => $fechaFin,

            // Análisis
            'cuello_botella' => $cuelloBotella['nombre'],
            'tasa_cuello_botella' => round($tasaCuelloBotella, 4),
            'tasa_sistema_piezas_por_hora' => round($tasaCuelloBotella * 60, 2),
            'eficiencia_sistema' => $tiempoTotalMinutos > 0
                ? round((1 - ($tiempoMuertoTotal / $tiempoTotalMinutos)) * 100, 2)
                : 100,

            // Detalles por proceso
            'detalle_procesos' => $detalleProcesos,

            // Datos para UI
            'datos_ui' => $datosUI
        ];
    }

    /**
     * Identifica el cuello de botella real (proceso con MENOR tasa EFECTIVA)
     * La tasa efectiva considera la limitación acumulada del sistema
     */
    private function identificarCuelloBotellaReal($procesosConTasas)
    {
        $cuelloBotella = null;
        $tasaMinima = PHP_FLOAT_MAX;

        foreach ($procesosConTasas as $proceso) {
            // Usar tasa_efectiva en lugar de tasa_produccion
            $tasaComparar = isset($proceso['tasa_efectiva']) ? $proceso['tasa_efectiva'] : $proceso['tasa_produccion'];

            if ($tasaComparar < $tasaMinima) {
                $tasaMinima = $tasaComparar;
                $cuelloBotella = $proceso;
            }
        }

        return $cuelloBotella;
    }

    /**
     * Calcula la tasa de producción (piezas por minuto)
     */
    private function calcularTasaProduccion($tiempoPorPieza, $numeroMaquinas)
    {
        if ($tiempoPorPieza == 0)
            return 0;
        return $numeroMaquinas / $tiempoPorPieza;
    }

    /**
     * Calcula el estado de las piezas en un proceso (para visualización UI)
     * NOTA: Estado estimado inicial, NO seguimiento en tiempo real
     */
    private function calcularEstadoPiezasProceso($totalPiezas, $maquinas, $progreso, $tiempoTotal)
    {
        return [
            'total' => $totalPiezas,
            'procesadas' => 0,
            'en_maquina' => min($maquinas, $totalPiezas),
            'en_cola' => max(0, $totalPiezas - $maquinas),
            'tiempo_estimado_restante_minutos' => round($tiempoTotal, 2)
        ];
    }

    /**
     * Prepara datos estructurados para la UI
     */
    private function prepararDatosParaUI($cantidadPiezas, $detalleProcesos, $cuelloBotella, $fechaInicio, $fechaFin, $tiempoTotal)
    {
        return [
            'estado_pedido' => [
                'status' => 'en_espera',
                'cantidad_total' => $cantidadPiezas,
                'piezas_completadas' => 0,
                'porcentaje_completado' => 0
            ],
            'indicadores_clave' => [
                'tiempo_total_estimado' => round($tiempoTotal / 60, 2) . ' horas',
                'fecha_entrega_estimada' => $fechaFin->format('Y-m-d H:i'),
                'proceso_cuello_botella' => $cuelloBotella['nombre'],
                'ritmo_sistema' => round($cuelloBotella['tasa_produccion'] * 60, 2) . ' piezas/hora'
            ],
            'linea_tiempo_procesos' => array_map(function ($proceso) {
                return [
                    'nombre' => $proceso['nombre'],
                    'hora_inicio' => $proceso['inicio']->format('Y-m-d H:i'),
                    'hora_fin' => $proceso['fin']->format('Y-m-d H:i'),
                    'duracion_horas' => round($proceso['tiempo_trabajo_efectivo_minutos'] / 60, 2),
                    'es_cuello_botella' => $proceso['es_cuello_botella'],
                    'tiempo_muerto_horas' => round($proceso['tiempo_muerto_minutos'] / 60, 2),
                    'utilizacion' => $proceso['porcentaje_utilizacion'] . '%'
                ];
            }, $detalleProcesos),
            'estado_por_proceso' => array_map(function ($proceso) {
                return [
                    'nombre' => $proceso['nombre'],
                    'piezas_procesadas' => $proceso['estado_piezas']['procesadas'],
                    'piezas_en_maquina' => $proceso['estado_piezas']['en_maquina'],
                    'piezas_en_cola' => $proceso['estado_piezas']['en_cola'],
                    'tiempo_restante' => round($proceso['estado_piezas']['tiempo_estimado_restante_minutos'] / 60, 2) . ' horas',
                    'tasa_produccion' => $proceso['tasa_produccion_por_hora'] . ' piezas/hora'
                ];
            }, $detalleProcesos)
        ];
    }

    /**
     * Ajusta una fecha al inicio del siguiente turno disponible si está fuera de horario
     */
    private function ajustarAInicioTurno($fecha)
    {
        $hora = $fecha->format('H:i:s');
        $horaEnMinutos = $fecha->hour * 60 + $fecha->minute;

        // Turno matutino: 6:00 am – 2:00 pm (360 - 840 minutos)
        // Turno vespertino: 1:45 pm – 9:45 pm (825 - 1305 minutos)

        if ($horaEnMinutos < 360) {
            // Antes de las 6:00 am, ajustar a las 6:00 am del mismo día
            return Carbon::parse($fecha->format('Y-m-d') . ' 06:00:00');
        } elseif ($horaEnMinutos >= 1305) {
            // Después de las 9:45 pm, ajustar a las 6:00 am del día siguiente
            return Carbon::parse($fecha->format('Y-m-d') . ' 06:00:00')->addDay();
        }

        // Si está dentro de horario laboral, retornar la misma fecha
        return $fecha;
    }

    /**
     * Suma minutos laborales a una fecha, considerando turnos y tiempos de comida
     */
    private function sumarMinutosLaborales($fechaInicio, $minutosASumar)
    {
        $fechaActual = clone $fechaInicio;
        $minutosRestantes = $minutosASumar;

        while ($minutosRestantes > 0) {
            // Obtener información del turno actual
            $turnoInfo = $this->obtenerInfoTurno($fechaActual);

            if ($turnoInfo === null) {
                // Fuera de horario laboral, saltar al siguiente turno
                $fechaActual = $this->ajustarAInicioTurno($fechaActual->addMinutes(1));
                continue;
            }

            // Calcular minutos disponibles hasta el fin del turno
            $minutosHastaFinTurno = $turnoInfo['minutos_hasta_fin'];

            // Verificar si hay tiempo de comida en el camino
            $tiempoComida = $this->calcularTiempoComidaEnRango(
                $fechaActual,
                min($minutosRestantes, $minutosHastaFinTurno),
                $turnoInfo
            );

            $minutosDisponibles = min($minutosRestantes, $minutosHastaFinTurno);

            if ($minutosDisponibles <= $minutosHastaFinTurno) {
                // Podemos completar en este turno
                $fechaActual->addMinutes($minutosDisponibles + $tiempoComida);
                $minutosRestantes -= $minutosDisponibles;
            } else {
                // Necesitamos continuar en el siguiente turno
                $fechaActual->addMinutes($minutosHastaFinTurno + $tiempoComida);
                $minutosRestantes -= $minutosHastaFinTurno;

                // Ajustar al siguiente turno
                $fechaActual = $this->ajustarAInicioTurno($fechaActual->addMinutes(1));
            }
        }

        return $fechaActual;
    }

    /**
     * Calcula los minutos laborales entre dos fechas
     */
    private function calcularMinutosLaboralesEntre($fechaInicio, $fechaFin)
    {
        $minutosLaborales = 0;
        $fechaActual = clone $fechaInicio;

        while ($fechaActual < $fechaFin) {
            $turnoInfo = $this->obtenerInfoTurno($fechaActual);

            if ($turnoInfo !== null) {
                // Estamos en horario laboral
                $minutosHastaFin = $fechaActual->diffInMinutes($fechaFin);
                $minutosHastaFinTurno = $turnoInfo['minutos_hasta_fin'];

                $minutosEnEsteTurno = min($minutosHastaFin, $minutosHastaFinTurno);
                $minutosLaborales += $minutosEnEsteTurno;

                $fechaActual->addMinutes($minutosEnEsteTurno);
            } else {
                // Fuera de horario, saltar al siguiente turno
                $fechaActual = $this->ajustarAInicioTurno($fechaActual->addMinutes(1));
            }
        }

        return $minutosLaborales;
    }

    /**
     * Obtiene información del turno actual
     * Retorna null si está fuera de horario laboral
     */
    private function obtenerInfoTurno($fecha)
    {
        $horaEnMinutos = $fecha->hour * 60 + $fecha->minute;

        // Turno matutino: 6:00 am – 2:00 pm (360 - 840 minutos)
        // Comida matutina: 10:00 am - 10:30 am (600 - 630 minutos)
        if ($horaEnMinutos >= 360 && $horaEnMinutos < 840) {
            return [
                'nombre' => 'matutino',
                'inicio' => 360,
                'fin' => 840,
                'comida_inicio' => 600,
                'comida_fin' => 630,
                'minutos_hasta_fin' => 840 - $horaEnMinutos
            ];
        }

        // Turno vespertino: 1:45 pm – 9:45 pm (825 - 1305 minutos)
        // Comida vespertina: 5:30 pm - 6:00 pm (1050 - 1080 minutos)
        if ($horaEnMinutos >= 825 && $horaEnMinutos < 1305) {
            return [
                'nombre' => 'vespertino',
                'inicio' => 825,
                'fin' => 1305,
                'comida_inicio' => 1050,
                'comida_fin' => 1080,
                'minutos_hasta_fin' => 1305 - $horaEnMinutos
            ];
        }

        return null; // Fuera de horario laboral
    }

    /**
     * Calcula el tiempo de comida que se encuentra en un rango de minutos
     */
    private function calcularTiempoComidaEnRango($fechaInicio, $minutosRango, $turnoInfo)
    {
        $horaInicioMinutos = $fechaInicio->hour * 60 + $fechaInicio->minute;
        $horaFinMinutos = $horaInicioMinutos + $minutosRango;

        $comidaInicio = $turnoInfo['comida_inicio'];
        $comidaFin = $turnoInfo['comida_fin'];

        // Verificar si el rango intersecta con el tiempo de comida
        if ($horaFinMinutos > $comidaInicio && $horaInicioMinutos < $comidaFin) {
            // Hay intersección, calcular cuántos minutos de comida están en el rango
            $inicioInterseccion = max($horaInicioMinutos, $comidaInicio);
            $finInterseccion = min($horaFinMinutos, $comidaFin);

            return max(0, $finInterseccion - $inicioInterseccion);
        }

        return 0; // No hay tiempo de comida en este rango
    }

    /**
     * Método de ejemplo para calcular un pedido específico
     * Este método puede ser llamado desde una ruta o controlador
     */
    public function calcularPedidoEjemplo()
    {
        // Ejemplo con los datos proporcionados
        $cantidadPiezas = 100;

        $procesosConfig = [
            [
                'nombre' => 'Cepillado',
                'tiempo_por_pieza' => 35, // minutos
                'maquinas' => 3
            ],
            [
                'nombre' => 'Desbaste Exterior',
                'tiempo_por_pieza' => 40, // minutos
                'maquinas' => 3
            ],
            [
                'nombre' => 'Primera Operación',
                'tiempo_por_pieza' => 38, // minutos
                'maquinas' => 3
            ]
        ];

        $fechaInicio = Carbon::parse('2026-02-10 06:00:00');

        $resultado = $this->calcularTiempoProduccionRealista($cantidadPiezas, $procesosConfig, $fechaInicio);

        return response()->json($resultado);
    }

    /**
     * Calcula el tiempo de producción para una clase específica
     *
     * @param Clase $clase
     * @param int $cantidadPiezas
     * @param Carbon $fechaInicio
     * @return array
     */
    public function calcularTiempoProduccionPorClase($clase, $cantidadPiezas, $fechaInicio = null)
    {
        // Obtener los tiempos de producción de la clase
        $tiemposProduccion = tiempoproduccion::where('id_clase', $clase->id)->get();

        if ($tiemposProduccion->count() == 0) {
            return [
                'error' => 'No se encontraron tiempos de producción para esta clase',
                'clase' => $clase->nombre
            ];
        }

        // Obtener los procesos activos de la clase
        $procesosActivos = $this->getProcesos($clase);

        if (!$procesosActivos) {
            return [
                'error' => 'No se encontraron procesos activos para esta clase',
                'clase' => $clase->nombre
            ];
        }

        // Construir la configuración de procesos
        $procesosConfig = [];

        foreach ($procesosActivos as $procesoKey) {
            $processName = $this->get_processName($this->get_processNormalName($procesoKey));
            $tiempo = $tiemposProduccion->where('proceso', $processName)->first();

            if ($tiempo && $tiempo->tiempo > 0) {
                // Obtener número de máquinas
                $maquinas = $this->obtenerMaquinasClase($clase->id, $procesoKey);

                $procesosConfig[] = [
                    'nombre' => $this->get_processNormalName($procesoKey),
                    'tiempo_por_pieza' => $tiempo->tiempo,
                    'maquinas' => $maquinas > 0 ? $maquinas : 1
                ];
            }
        }

        if (count($procesosConfig) == 0) {
            return [
                'error' => 'No se encontraron procesos configurados con tiempos válidos',
                'clase' => $clase->nombre
            ];
        }

        // Calcular el tiempo de producción
        $resultado = $this->calcularTiempoProduccionRealista($cantidadPiezas, $procesosConfig, $fechaInicio);
        $resultado['clase'] = $clase->nombre;
        $resultado['tamanio'] = $clase->tamanio;
        $resultado['cantidad_piezas'] = $cantidadPiezas;

        return $resultado;
    }
}
