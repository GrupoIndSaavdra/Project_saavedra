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
use App\Models\PrimeraOperacionCabezaSoplo;
use App\Models\PrimeraOperacionCabezaSoplo_cnominal;
use App\Models\PrimeraOperacionCabezaSoplo_pza;
use App\Models\PrimeraOperacionCabezaSoplo_tolerancia;
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
use App\Models\SegundaOperacionCabezaSoplo;
use App\Models\SegundaOperacionCabezaSoplo_cnominal;
use App\Models\SegundaOperacionCabezaSoplo_pza;
use App\Models\SegundaOperacionCabezaSoplo_tolerancia;
use App\Models\Soldadura;
use App\Models\Soldadura_pza;
use App\Models\SoldaduraPTA;
use App\Models\SoldaduraPTA_pza;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

//Clase para el control de las piezas generales
class PzasGeneralesController extends Controller
{
    /** Cache de usuarios indexado por matrícula para evitar N+1 queries */
    protected array $usersCache = [];

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
        $infoPieces    = array();
        $filtersData   = $this->getFiltersInfo();

        // Siempre cargar observaciones: los índices de BD hacen las queries eficientes
        $isPdf   = $piecesData["action"] === 'pdf';
        $pieces  = $this->buscarPiezas($piecesData, $selectedItems, true);

        $pieces = $pieces == null ? array() : $pieces;
        $this->saveInfoPzas($infoPieces, $pieces);

        if (!$isPdf) {
            if ($profile == 'quality') {
                return [true, $pieces, $piecesData, $infoPieces, $selectedItems, $filtersData];
            }
            return view('pieces_views.piecesReport.adminPieces', compact('pieces', 'piecesData', 'infoPieces', 'filtersData', 'selectedItems'));
        } else {
            if ($profile == 'quality') {
                return [false, $pieces, $piecesData, $infoPieces, $selectedItems, $filtersData];
            }

            // Configuración para generación de PDFs grandes
            @ini_set('max_execution_time', '300'); // 5 minutos
            @ini_set('memory_limit', '512M');
            @set_time_limit(300);

            $pdf = Pdf::loadView('pieces_views.piecesReport.pdf', compact('pieces', 'piecesData', 'infoPieces', 'filtersData', 'selectedItems'));
            $filename = $this->generatePdfFilename($selectedItems, "Piezas");
            return $pdf->download($filename);
        }
    }

    public function generatePdfFilename($selectedItems, $reportType)
    {
        $parts = [];
        $date = date('d-m-Y');

        if (isset($selectedItems['workOrder']) && $selectedItems['workOrder'] !== 'Todos') {
            $parts[] = $selectedItems['workOrder'];
        }
        if (isset($selectedItems['class']) && $selectedItems['class'] !== 'Todos') {
            $parts[] = $selectedItems['class'];
        }
        if (isset($selectedItems['process']) && $selectedItems['process'] !== 'Todos') {
            $parts[] = $selectedItems['process'];
        }

        if (count($parts) > 0) {
            return implode(' - ', $parts) . " - " . $date . ".pdf";
        } else {
            return "Reporte de " . $reportType . " General - " . $date . ".pdf";
        }
    }
    public function getFiltersInfo()
    {
        // 1 minuto de cache local: reduce carga en base de datos para la generación de filtros
        $molduras = \Illuminate\Support\Facades\Cache::remember('molduras_all', 60, function () {
            return Moldura::all()->keyBy('id');
        });
        $orders = \Illuminate\Support\Facades\Cache::remember('ot_all', 60, function () {
            return Orden_trabajo::all();
        });
        $users = \Illuminate\Support\Facades\Cache::remember('users_all', 60, function () {
            return User::all();
        });

        $filtersData = array(
            "workOrder" => $this->objectToArrayFromDB($orders, "workOrder", $molduras),
            "class" => ["Bombillo", "Molde", "Obturador", "Fondo", "Corona", "Plato", "Embudo", "Cabeza de Soplo"],
            "operator" => $this->objectToArrayFromDB($users, "operator"),
            "machine" => [1, 2, 3, 4, 5, 6, 7],
            "process" => ["Cepillado", "Desbaste Exterior", "Revision Laterales", "Primera Operacion", "Barreno Maniobra", "Segunda Operacion", "Soldadura", "Soldadura PTA", "Rectificado", "Asentado", "Calificado", "Acabado Bombillo", "Acabado Molde", "Barreno Profundidad", "Cavidades", "Copiado", "Off Set", "Palomas", "Rebajes", "Operacion Equipo_1 operacion", "Operacion Equipo_2 operacion", "Embudo CM", "Primera Operacion Cabeza Soplo", "Segunda Operacion Cabeza Soplo"],
            "error" => ["Ninguno", "Maquinado", "Fundicion"],
        );
        return $filtersData;
    }
    public function objectToArrayFromDB($object, $param, $molduras = null)
    {
        $array = array();
        foreach ($object as $item) {
            if (($param == "operator" && $item->perfil == 2)) {
                array_push($array, $item);
            } else if ($param == "workOrder") {
                // Usa cache de molduras si está disponible (0 queries), si no hace 1 query
                $molding = $molduras ? $molduras->get($item->id_moldura) : Moldura::where('id', $item->id_moldura)->first();
                $text = $item->id . " - " . ($molding ? $molding->nombre : '?');
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
            "n_juego" => $request->n_juego,
            "action" => $request->input("action"),
        );
        return $this->search($datosPiezas, 'admin');
    }
    public function buscarPiezas($piecesData, &$itemElegidos, bool $includeObservations = false)
    {
        // ── OPTIMIZACIÓN: filtrar directamente en SQL en lugar de cargar Pieza::all() ──
        $finishedClassIds = Clase::where('finalizada', '!=', 0)->pluck('id')->toArray();
        $query = Pieza::query();
        if (!empty($finishedClassIds)) {
            $query->whereNotIn('id_clase', $finishedClassIds);
        }

        foreach ($piecesData as $key => $value) {
            if ($key === 'action') continue;

            if ($value === null || $value === 'Todos' || $value === '') {
                $itemElegidos[$key] = 'Todos';
                continue;
            }

            $itemElegidos[$key] = $value;

            switch ($key) {
                case 'workOrder':
                    $workOrderId = explode(' - ', $value)[0];
                    $workOrder   = Orden_trabajo::find($workOrderId);
                    $molding     = Moldura::find($workOrder->id_moldura);
                    $itemElegidos[$key] = $workOrder->id . ' - ' . ($molding ? $molding->nombre : '?');
                    $query->where('id_ot', $workOrderId);
                    break;
                case 'class':
                    $claseIds = Clase::where('nombre', $value)->pluck('id');
                    $query->whereIn('id_clase', $claseIds);
                    break;
                case 'operator':
                    $user = User::where('matricula', $value)->first();
                    $itemElegidos[$key] = $user;
                    $query->where('id_operador', $value);
                    break;
                case 'machine':
                    $query->where('maquina', $value);
                    break;
                case 'process':
                    // Exacto para que 'Soldadura' no coincida con 'Soldadura PTA'
                    $query->where('proceso', $value);
                    break;
                case 'error':
                    $query->where('error', $value);
                    break;
                case 'dateFrom':
                    $query->where('created_at', '>=', $value . ' 00:00:00');
                    break;
                case 'dateTo':
                    $query->where('created_at', '<=', $value . ' 23:59:59');
                    break;
                case 'n_juego':
                    $numJuego = rtrim($value, 'J'); // "3J" → "3"
                    $query->where(function ($q) use ($numJuego) {
                        $q->where('n_pieza', $numJuego . 'J')
                          ->orWhere('n_pieza', $numJuego . 'H')
                          ->orWhere('n_pieza', $numJuego . 'M');
                    });
                    break;
            }
        }

        $piezas = $query->get();
        return $piezas->isEmpty() ? [] : $this->saveInArray($piezas, $includeObservations);
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
                case ($posicion == 1): // Filtrado exacto para n_juego
                    if ($elementoArray == $elemento) {
                        array_push($array, $arrayP[$i]);
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
    public function saveInArray($arrayP, bool $includeObservations = false)
    {
        // ── OPTIMIZACIÓN: pre-cargar todo en memoria para eliminar N+1 queries ──
        $finishedClassIds = Clase::where('finalizada', '!=', 0)->pluck('id')->toArray();
        $usersCache       = User::all()->keyBy('matricula');
        $clasesCache      = Clase::all()->keyBy('id');

        // Índice en memoria: '{id_clase}_{proceso}_{numJuego}' → colección de piezas
        $piezasIndex = collect($arrayP)->groupBy(function ($pza) {
            $num = $this->getPiezaNumber($pza->n_pieza);
            return $pza->id_clase . '_' . $pza->proceso . '_' . $num;
        });

        // Instanciar controller de proceso una sola vez fuera del loop
        $processController = new ProcessProductionController();

        // ── OPTIMIZACIÓN DE OBSERVACIONES ──
        $observacionesMap = [];
        $procesosDBMap = [];
        if ($includeObservations) {
            $piezasPorProceso = collect($arrayP)->groupBy('proceso');
            foreach ($piezasPorProceso as $nombreProceso => $piezasDelProceso) {
                $processString = str_contains($nombreProceso, 'Operacion Equipo') ? 'Operacion Equipo' : $nombreProceso;
                try {
                    $modelClass = $processController->get_ModelProcess($processString); 
                    $modelPiecesClass = $processController->get_ModelProcessPieces($processString);

                    $idProcesos = [];
                    foreach ($piezasDelProceso as $pz) {
                        $clsObj = $clasesCache->get($pz->id_clase);
                        if ($clsObj) {
                            $id_process = str_replace(' ', '_', $pz->proceso) . '_' . $clsObj->nombre . '_' . $pz->id_ot;
                            $idProcesos[] = $id_process;
                        }
                    }
                    $idProcesos = array_unique($idProcesos);

                    $procesosDB = $modelClass::whereIn('id_proceso', $idProcesos)->get()->keyBy('id_proceso');
                    $procesosDBMap[$nombreProceso] = $procesosDB;

                    $parentDbIds = $procesosDB->pluck('id')->toArray();
                    if (!empty($parentDbIds)) {
                        $childPieces = $modelPiecesClass::whereIn('id_proceso', $parentDbIds)->get();
                        foreach ($childPieces as $cp) {
                            $key = $cp->id_proceso . '_' . $cp->n_juego;
                            if (!isset($observacionesMap[$nombreProceso][$key])) {
                                $observacionesMap[$nombreProceso][$key] = [];
                            }
                            if ($cp->observaciones) {
                                $observacionesMap[$nombreProceso][$key][] = $cp->observaciones;
                            }
                        }
                    }
                } catch (\Throwable $e) { }
            }
        }

        $array          = array();
        $juegosGuardados = array();
        $contador       = 0;
        $mitad          = false;

        foreach ($arrayP as $item) {
            if (in_array($item->id_clase, $finishedClassIds)) {
                continue;
            }

            $band     = false;
            $numJuego = $this->getPiezaNumber($item->n_pieza);

            if (substr($item->n_pieza, -1) !== 'J') { // Es mitad (H o M)
                $mitad    = true;
                $juegoKey = $numJuego . 'J_' . $item->proceso . '_' . $item->id_clase . '_' . $item->id_ot;

                if (!in_array($juegoKey, $juegosGuardados)) {
                    $band = true;
                    $array[$contador][1] = $numJuego . 'J';
                    $juegosGuardados[]   = $juegoKey;

                    // ── Buscar mitades desde índice en memoria (0 queries) ──
                    $indexKey = $item->id_clase . '_' . $item->proceso . '_' . $numJuego;
                    $group    = $piezasIndex->get($indexKey, collect());
                    $pzaH     = $group->firstWhere('n_pieza', $numJuego . 'H');
                    $pzaM     = $group->firstWhere('n_pieza', $numJuego . 'M');

                    // ── Si no se encontró alguna mitad en el índice en memoria,
                    //    puede ser que esté filtrada (ej. filtro por operador).
                    //    Buscarla en BD antes de declarar el juego incompleto. ──
                    if (!$pzaH) {
                        $pzaH = Pieza::where('n_pieza', $numJuego . 'H')
                            ->where('id_clase', $item->id_clase)
                            ->where('proceso', $item->proceso)
                            ->where('id_ot', $item->id_ot)
                            ->first();
                    }
                    if (!$pzaM) {
                        $pzaM = Pieza::where('n_pieza', $numJuego . 'M')
                            ->where('id_clase', $item->id_clase)
                            ->where('proceso', $item->proceso)
                            ->where('id_ot', $item->id_ot)
                            ->first();
                    }

                    if (!$pzaH || !$pzaM) { // Realmente incompleto: una mitad no existe en BD
                        $existing = $pzaH ?? $pzaM;
                        $op = $usersCache->get($existing->id_operador);
                        $array[$contador][2] = $op ? "{$op->nombre} {$op->a_paterno} {$op->a_materno}" : '(desconocido)';
                        $error = $existing->error !== 'Ninguno' ? $existing->error . ' / Incompleto' : 'Incompleto';
                        // Siempre en [5] — [6] se usa para created_at y no debe pisarse
                        $array[$contador][5] = $error;
                    } else {
                        // Operadores
                        $opH = $usersCache->get($pzaH->id_operador);
                        $opM = $usersCache->get($pzaM->id_operador);
                        // Si el operador H no está en caché (es de otro operador filtrado), ir a BD
                        if (!$opH) $opH = User::where('matricula', $pzaH->id_operador)->first();
                        if (!$opM) $opM = User::where('matricula', $pzaM->id_operador)->first();
                        $nombreH = $opH ? "{$opH->nombre} {$opH->a_paterno} {$opH->a_materno}" : '(desconocido)';
                        $nombreM = $opM ? "{$opM->nombre} {$opM->a_paterno} {$opM->a_materno}" : '(desconocido)';

                        if ($pzaH->id_operador === $pzaM->id_operador) {
                            $array[$contador][2] = $nombreH;
                        } else {
                            $array[$contador][2] = $nombreH . ' / ' . $nombreM;
                        }

                        // Errores — siempre en [5], independientemente del tipo de proceso
                        if ($pzaH->error === $pzaM->error) {
                            $array[$contador][5] = $pzaH->error;
                        } else {
                            $array[$contador][5] = $pzaH->error . ' / ' . $pzaM->error;
                        }

                        // Liberación combinada: usar el peor estado entre ambas mitades
                        // La asignación final se hará en array_push más abajo,
                        // pero necesitamos que $item->liberacion refleje el estado del juego.
                        // Guardamos el estado combinado en una variable temporal.
                        $liberacionCombinada = $this->combinarLiberacion($pzaH->liberacion, $pzaM->liberacion);
                    }

                }
            } else { // Es juego completo (J)
                $band  = true;
                $mitad = false;
                $array[$contador][1] = $item->n_pieza;
                $op = $usersCache->get($item->id_operador);
                $array[$contador][2] = $op ? "{$op->nombre} {$op->a_paterno} {$op->a_materno}" : '(desconocido)';
                $array[$contador][5] = $item->error;
            }

            // Usuario de liberación desde cache (0 queries)
            $userLib = $usersCache->get($item->user_liberacion);

            if ($band) {
                $array[$contador][0]             = $item->id_ot;
                $array[$contador][3]             = $item->maquina;
                $array[$contador]['id_clase']    = $item->id_clase;
                $className                       = $clasesCache->get($item->id_clase);
                $array[$contador]['className']   = $className ? $className->nombre : null;
                $array[$contador]['observacion_liberacion'] = $item->observacion_liberacion;

                $array[$contador]['observations'] = '';
                if ($includeObservations && $className) {
                    $id_process = str_replace(' ', '_', $item->proceso) . '_' . $className->nombre . '_' . $item->id_ot;
                    
                    if (isset($procesosDBMap[$item->proceso][$id_process])) {
                        $pDbId = $procesosDBMap[$item->proceso][$id_process]->id;
                        $mapKey = $pDbId . '_' . $numJuego . 'J';
                        if (isset($observacionesMap[$item->proceso][$mapKey]) && !empty($observacionesMap[$item->proceso][$mapKey])) {
                            $array[$contador]['observations'] = implode(' / ', $observacionesMap[$item->proceso][$mapKey]);
                        }
                    }
                }

                $array[$contador][4] = $item->proceso;
                $date                = new \DateTime($item->created_at);
                $array[$contador][6] = $date->format('Y-m-d H:i:s');
                $array[$contador][7] = $item->fecha_liberacion ?? 'No liberado';
                $array[$contador][8] = $userLib ? "{$userLib->nombre} {$userLib->a_paterno} {$userLib->a_materno}" : null;

                // Si es un juego de dos mitades con estados diferentes, usar el estado combinado
                $liberacionFinal = isset($liberacionCombinada) ? $liberacionCombinada : $item->liberacion;
                array_push($array[$contador], $liberacionFinal);
                array_push($array[$contador], $mitad ? 'mitad' : 'juego');
                $liberacionCombinada = null; // Resetear para la siguiente iteración

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
        // Pre-cargar clases en memoria para evitar N+1 en el loop
        $clasesCache = Clase::all()->keyBy('id');
        foreach ($piezas as $pieza) {
            // Buscar la clase desde cache (0 queries)
            $claseObj = $clasesCache->get($pieza["id_clase"]);
            $clase = $claseObj ? $claseObj->nombre : null;

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
                case 'Primera Operacion Cabeza Soplo':
                    $id_proceso = 'Primera_Operacion_Cabeza_Soplo_' . $clase . "_" . $pieza[0];
                    $id_proceso = PrimeraOperacionCabezaSoplo::where('id_proceso', $id_proceso)->first();
                    $infoPiezas[$contador][1] = 'Primera Operacion Cabeza Soplo';
                    break;
                case 'Segunda Operacion Cabeza Soplo':
                    $id_proceso = 'Segunda_Operacion_Cabeza_Soplo_' . $clase . "_" . $pieza[0];
                    $id_proceso = SegundaOperacionCabezaSoplo::where('id_proceso', $id_proceso)->first();
                    $infoPiezas[$contador][1] = 'Segunda Operacion Cabeza Soplo';
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

            // Guardar el error — siempre en [5] ahora que se unificó el layout de índices
            $infoPiezas[$contador][2] = $pieza[5];
            $contador++;
        }
    }
    public function getGamesFromOT(Request $request)
    {
        $otStr = $request->input('ot');
        $className = $request->input('class');

        // Extract ID OT (handles format "123 - Nombre OT")
        $otId = $otStr;
        if (str_contains($otStr, ' - ')) {
            $parts = explode(' - ', $otStr);
            $otId = trim($parts[0]);
        }

        // Find Class ID - search by nombre AND id_ot to uniquely identify the class
        $clase = Clase::where('nombre', $className)->where('id_ot', $otId)->first();
        // Fallback: search by nombre only if not found with id_ot
        if (!$clase) {
            $clase = Clase::where('nombre', $className)->first();
        }
        if (!$clase) {
            return response()->json([]);
        }

        // Search pieces - n_pieza column only (n_juego does not exist in this table)
        $piezas = Pieza::where('id_ot', $otId)
            ->where('id_clase', $clase->id)
            ->select('n_pieza')
            ->get();

        $games = [];

        foreach ($piezas as $pza) {
            if ($pza->n_pieza) {
                $lastChar = substr($pza->n_pieza, -1);
                if ($lastChar === 'J') {
                    // Already a game number (e.g. "1J", "10J")
                    $games[] = $pza->n_pieza;
                } elseif ($lastChar === 'H' || $lastChar === 'M') {
                    // Half piece - convert to game number (e.g. "1H" -> "1J")
                    $numero = $this->getPiezaNumber($pza->n_pieza);
                    $games[] = $numero . 'J';
                }
            }
        }

        // Remove duplicates and sort naturally (so 2J < 10J, not lexicographic)
        $games = array_unique($games);
        natsort($games);

        return response()->json(array_values($games));
    }

    /**
     * @param string $pieces
     * @param string $process
     * @param string $profile
     * @return mixed
     */
    public function showPiece($pieces, $process, $profile)
    {
        /** @var array<int, mixed> $pieceInfo */
        $pieceInfo = array();
        /** @var string $processName */
        $processName = '';
        /** @var mixed $cNominal */
        $cNominal = null;
        /** @var mixed $tolerance */
        $tolerance = null;
        
        switch ($process) {
            case 'Cepillado':
                $pieceInfo = array();
                $pieces = explode(",", $pieces);
                foreach ($pieces as $piece) {
                    $pza = Pza_cepillado::where('id_pza', $piece)->first();
                    if ($pza) {
                        array_push($pieceInfo, $pza);
                    }
                }

                if (empty($pieceInfo)) {
                    return redirect()->back()->with('error', 'No se encontraron las piezas solicitadas.');
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
                $piecesArray = explode(",", $pieces);
                $pieceInfo = array();
                foreach ($piecesArray as $pza) {
                    $p = Soldadura_pza::where('id_pza', $pza)->first();
                    if ($p) {
                        array_push($pieceInfo, $p);
                    }
                }
                if (count($pieceInfo) == 0)
                    return redirect()->back()->with('error', 'No se encontraron las piezas solicitadas.');
                //Obtener Cotas nominales y tolerancias
                $id_process = Soldadura::find($pieceInfo[0]->id_proceso);
                $cNominal = 0;
                $tolerance = 0;
                $process = 'Soldadura';
                break;
            case 'Soldadura PTA':
                // Obtener la primera pieza para localizar el proceso padre
                $piecesArray = explode(",", $pieces);
                $unaPieza = null;
                $n_piezas_solicitadas = [];
                foreach ($piecesArray as $pza) {
                    preg_match('/^(\d+[a-zA-Z]*)(\d+)$/', $pza, $matches);
                    if (count($matches) == 3) {
                        $n_pieza = $matches[1];
                        $id_proceso = $matches[2];
                        $n_piezas_solicitadas[] = $n_pieza;
                        if (!$unaPieza) {
                            $unaPieza = SoldaduraPTA_pza::where('n_pieza', $n_pieza)->where('id_proceso', $id_proceso)->first();
                        }
                    } else {
                        $p = SoldaduraPTA_pza::where('id_pza', $pza)->first();
                        if ($p) {
                            $n_piezas_solicitadas[] = $p->n_pieza;
                            if (!$unaPieza)
                                $unaPieza = $p;
                        }
                    }
                }

                if (!$unaPieza) {
                    return redirect()->back()->with('error', 'No se encontraron las piezas solicitadas.');
                }

                // Obtener SOLO las sub-filas de las piezas solicitadas (las 3 por cada pieza M/H)
                $id_process = SoldaduraPTA::find($unaPieza->id_proceso);
                $pieceInfo = SoldaduraPTA_pza::where('id_proceso', $id_process->id)
                    ->whereIn('n_pieza', $n_piezas_solicitadas)
                    ->where('estado', 2)
                    ->orderBy('n_pieza')
                    ->orderByRaw("FIELD(tipo_medida, 'D_Conexion_pico', 'D_Conexion_obt', 'Perfilado')")
                    ->get();
                // Pasar colección agrupada para el partial Blade
                $piezasGroup = $pieceInfo->groupBy('n_pieza');
                $cNominal = 0;
                $tolerance = 0;
                $process = 'Soldadura PTA';
                break;
            case 'Rectificado':
                //Obtener informacion de la pieza elegida
                $piecesArray = explode(",", $pieces);
                $pieceInfo = array();
                foreach ($piecesArray as $pza) {
                    $p = Rectificado_pza::where('id_pza', $pza)->first();
                    if ($p) {
                        array_push($pieceInfo, $p);
                    }
                }
                if (count($pieceInfo) == 0)
                    return redirect()->back()->with('error', 'No se encontraron las piezas solicitadas.');
                //Obtener Cotas nominales y tolerancias
                $id_process = Rectificado::find($pieceInfo[0]->id_proceso);
                $cNominal = 0;
                $tolerance = 0;
                $process = 'Rectificado';
                break;
            case 'Asentado':
                //Obtener informacion de la pieza elegida
                $piecesArray = explode(",", $pieces);
                $pieceInfo = array();
                foreach ($piecesArray as $pza) {
                    $p = Asentado_pza::where('id_pza', $pza)->first();
                    if ($p) {
                        array_push($pieceInfo, $p);
                    }
                }
                if (count($pieceInfo) == 0)
                    return redirect()->back()->with('error', 'No se encontraron las piezas solicitadas.');
                //Obtener Cotas nominales y tolerancias
                $id_process = Asentado::find($pieceInfo[0]->id_proceso);
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
                $process = 'Cavidades';
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
            case 'Primera Operacion Cabeza Soplo':
                //Obtener informacion de la pieza elegida
                $piecesArray = explode(",", $pieces);
                $pieceInfo = array();
                foreach ($piecesArray as $pza) {
                    $p = PrimeraOperacionCabezaSoplo_pza::where('id_pza', $pza)->first();
                    if ($p) {
                        array_push($pieceInfo, $p);
                    }
                }
                if (count($pieceInfo) == 0)
                    return redirect()->back()->with('error', 'No se encontraron las piezas solicitadas.');

                //Obtener Cotas nominales y tolerancias
                $id_process = PrimeraOperacionCabezaSoplo::find($pieceInfo[0]->id_proceso);
                $cNominal = null;
                $tolerance = null;
                if ($id_process) {
                    $cnRecord = PrimeraOperacionCabezaSoplo_cnominal::where('id_proceso', $id_process->id_proceso)
                        ->select('id', 'diametro_exterior', 'longitud', 'diametro_candado', 'longitud_candado')
                        ->first();
                    $cNominal = $cnRecord ? $cnRecord->toArray() : null;

                    $tolRecord = PrimeraOperacionCabezaSoplo_tolerancia::where('id_proceso', $id_process->id_proceso)
                        ->select('id', 'diametro_exterior1', 'diametro_exterior2', 'longitud1', 'longitud2', 'diametro_candado1', 'diametro_candado2', 'longitud_candado1', 'longitud_candado2')
                        ->first();
                    $tolerance = $tolRecord ? $tolRecord->toArray() : null;
                }
                $process = 'Primera Operacion Cabeza Soplo';
                break;
            case 'Segunda Operacion Cabeza Soplo':
                //Obtener informacion de la pieza elegida
                $piecesArray = explode(",", $pieces);
                $pieceInfo = array();
                foreach ($piecesArray as $pza) {
                    $p = SegundaOperacionCabezaSoplo_pza::where('id_pza', $pza)->first();
                    if ($p) {
                        array_push($pieceInfo, $p);
                    }
                }
                if (count($pieceInfo) == 0)
                    return redirect()->back()->with('error', 'No se encontraron las piezas solicitadas.');

                //Obtener Cotas nominales y tolerancias
                $id_process = SegundaOperacionCabezaSoplo::find($pieceInfo[0]->id_proceso);
                $cNominal = null;
                $tolerance = null;
                if ($id_process) {
                    $cnRecord = SegundaOperacionCabezaSoplo_cnominal::where('id_proceso', $id_process->id_proceso)
                        ->select('id', 'diametro_exterior', 'longitud', 'diametro_candado', 'longitud_candado')
                        ->first();
                    $cNominal = $cnRecord ? $cnRecord->toArray() : null;

                    $tolRecord = SegundaOperacionCabezaSoplo_tolerancia::where('id_proceso', $id_process->id_proceso)
                        ->select('id', 'diametro_exterior1', 'diametro_exterior2', 'longitud1', 'longitud2', 'diametro_candado1', 'diametro_candado2', 'longitud_candado1', 'longitud_candado2')
                        ->first();
                    $tolerance = $tolRecord ? $tolRecord->toArray() : null;
                }
                $process = 'Segunda Operacion Cabeza Soplo';
                break;
        }
        // Obtener meta para obtener la ot y la clase
        if (is_array($pieceInfo) || $pieceInfo instanceof \Illuminate\Support\Collection) { //Si el juego es mitad o coleccion (Soldadura PTA)
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
            if (is_array($pieceInfo) || $pieceInfo instanceof \Illuminate\Support\Collection) {
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

        //Obtener el nombre del operador
        $operadores = array();
        $vistos = array(); // Para evitar duplicados por pieza + operador
        if (is_array($piecesInfo)) {
            $contador = 0;
            foreach ($piecesInfo as $pza) {
                //Obtener la meta para obtener el id del operador
                $meta = Metas::find($pza['id_meta']);
                $nombreOp = $this->getNameOperador($meta->id_usuario);
                $nPieza = is_array($pza) ? ($pza["n_pieza"] ?? $pza["n_juego"]) : ($pza->n_pieza ?? $pza->n_juego);

                // Identificador único para este par pieza-operador
                $hash = $nPieza . '|' . $nombreOp;

                if (!in_array($hash, $vistos)) {
                    //Guardar el nombre del operador
                    $operadores[$contador] = array($nPieza, $nombreOp);
                    $vistos[] = $hash;
                    $contador++;
                }
            }
        } else {
            $meta = Metas::find($piecesInfo->id_meta);
            $nPieza = $pieceInfo->n_juego;
            $nombreOp = $this->getNameOperador($meta->id_usuario);
            $operadores[0] = array($nPieza, $nombreOp);
        }
        $piezasGroup = $piezasGroup ?? null;
        return view('pieces_views.piecesReport.chosenPiece', compact('process', 'piecesInfo', 'cNominal', 'tolerance', 'ot', 'clase', 'profile', 'operadores', 'piezasGroup'));
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
        // Memoización: solo va a BD la primera vez que se pide esta matrícula
        if (!isset($this->usersCache[$matricula])) {
            $this->usersCache[$matricula] = User::where('matricula', $matricula)->first();
        }
        $op = $this->usersCache[$matricula];
        return $op ? "{$op->nombre} {$op->a_paterno} {$op->a_materno}" : '(desconocido)';
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

    /**
     * Combina los estados de liberación de dos mitades (H y M) de un juego,
     * devolviendo el peor estado entre ambas para que el color del juego sea correcto.
     * Prioridad (mayor = peor): 2(Rechazado) > 4(Mala) > 5(Incompleto) > 0(Sin lib) > 3(Buena) > 1(Liberado)
     */
    private function combinarLiberacion($libH, $libM): int
    {
        $priority = [
            2 => 6, // Rechazado       — peor
            4 => 5, // Mala sin lib
            5 => 4, // Incompleto
            0 => 3, // Sin liberación
            3 => 2, // Buena sin lib
            1 => 1, // Liberado        — mejor
        ];
        $pH = $priority[(int)$libH] ?? 0;
        $pM = $priority[(int)$libM] ?? 0;
        return $pH >= $pM ? (int)$libH : (int)$libM;
    }

    //Funciones para el control de la vista de piezas por maquina


    public function showVistaMaquina()
    {
        if ($this->retornarOTs() != 0) {
            $arregloOT = $this->retornarOTs();
            return view('machines_views.maquinas', compact('arregloOT'));
        } else {
            return view('machines_views.maquinas');
        }
    }
    /**
     * @param Request $request
     * @return mixed
     */
    public function showMachinesProcess(Request $request)
    {
        /** @var mixed $ot */
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
        return view('machines_views.vistaProcesos', compact('procesos', 'ot', 'clase'));
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
            case "primeraOperacionCabezaSoplo":
                return "Primera Operacion Cabeza Soplo";
            case "segundaOperacionCabezaSoplo":
                return "Segunda Operacion Cabeza Soplo";
        }
    }

    /**
     * Helper para aplicar filtros a Soldadura
     */
    private function applySoldaduraFilters($filters)
    {
        $query = Soldadura_pza::query();

        // 1. Filtro por n_juego
        if (isset($filters['n_juego']) && $filters['n_juego'] !== 'Todos' && $filters['n_juego'] !== '') {
            $query->where('n_juego', $filters['n_juego']);
        }

        // 2. Filtro por fechas (dateFrom, dateTo)
        if (isset($filters['dateFrom']) && $filters['dateFrom'] !== '' && $filters['dateFrom'] !== 'Todos') {
            $query->where('soldadura_pza.created_at', '>=', $filters['dateFrom'] . " 00:00:00");
        }
        if (isset($filters['dateTo']) && $filters['dateTo'] !== '' && $filters['dateTo'] !== 'Todos') {
            $query->where('soldadura_pza.created_at', '<=', $filters['dateTo'] . " 23:59:59");
        }

        // 3. Filtro por Operador o Maquina (requiere join con metas)
        $needsMetaJoin = false;
        if (
            (isset($filters['operator']) && $filters['operator'] !== 'Todos' && $filters['operator'] !== '') ||
            (isset($filters['machine']) && $filters['machine'] !== 'Todos' && $filters['machine'] !== '')
        ) {

            $query->join('metas', 'soldadura_pza.id_meta', '=', 'metas.id');
            $needsMetaJoin = true;

            if (isset($filters['operator']) && $filters['operator'] !== 'Todos' && $filters['operator'] !== '') {
                $matricula = is_array($filters['operator']) ? $filters['operator']['matricula'] : $filters['operator'];
                $query->where('metas.id_usuario', $matricula);
            }

            if (isset($filters['machine']) && $filters['machine'] !== 'Todos' && $filters['machine'] !== '') {
                $query->where('metas.maquina', $filters['machine']);
            }
        }

        // 4. Filtro por OT o Clase (usando join con soldadura)
        $needsSoldaduraJoin = false;
        if (
            (isset($filters['workOrder']) && $filters['workOrder'] !== 'Todos' && $filters['workOrder'] !== '') ||
            (isset($filters['class']) && $filters['class'] !== 'Todos' && $filters['class'] !== '')
        ) {

            $query->join('soldadura', 'soldadura_pza.id_proceso', '=', 'soldadura.id');
            $needsSoldaduraJoin = true;

            if (isset($filters['workOrder']) && $filters['workOrder'] !== 'Todos' && $filters['workOrder'] !== '') {
                $otId = strpos($filters['workOrder'], ' - ') !== false
                    ? explode(' - ', $filters['workOrder'])[0]
                    : $filters['workOrder'];
                $query->where('soldadura.id_proceso', 'LIKE', '%_' . $otId);
            }

            if (isset($filters['class']) && $filters['class'] !== 'Todos' && $filters['class'] !== '') {
                $query->where('soldadura.id_proceso', 'LIKE', 'Soldadura_' . $filters['class'] . '_%');
            }
        }

        // Seleccionar solo las columnas de soldadura_pza para evitar conflictos
        $query->select('soldadura_pza.*');

        return $query->get();
    }

    /**
     * Verificar contraseña de administrador (perfil == 1)
     */
    public function verifyAdminPassword(Request $request)
    {
        $password = $request->input('passwordAdmin');

        if ($password) {
            $users = User::all();
            foreach ($users as $user) {
                if ($user->perfil == 1) { // Solo verificar usuarios administradores
                    if (Hash::check($password, $user->contrasena)) {
                        return response()->json([
                            'success' => true,
                            'message' => 'Contraseña correcta. Acceso autorizado.',
                            'adminUser' => $user->nombre . ' ' . $user->a_paterno . ' ' . $user->a_materno
                        ]);
                    }
                }
            }
        }

        return response()->json([
            'success' => false,
            'message' => 'Contraseña incorrecta. Solo personal administrativo puede acceder.'
        ], 401);
    }

    /**
     * Obtener información extra de piezas en proceso de Soldadura
     */
    public function getSoldaduraExtraInfo(Request $request)
    {
        try {
            // Recibir filtros del cuerpo de la petición
            $filters = $request->all();

            // Obtener piezas filtradas
            $soldaduraPieces = $this->applySoldaduraFilters($filters);

            $piecesData = [];

            foreach ($soldaduraPieces as $piece) {
                // Obtener información del proceso
                $proceso = Soldadura::find($piece->id_proceso);

                // Obtener la meta asociada para sacar el operador
                $meta = Metas::find($piece->id_meta);
                $operatorName = 'N/A';

                if ($meta) {
                    $operator = User::where('matricula', $meta->id_usuario)->first();
                    if ($operator) {
                        $operatorName = $operator->nombre . ' ' . $operator->a_paterno . ' ' . $operator->a_materno;
                    }
                }

                if ($proceso) {
                    // Extraer información de la clase y OT del id_proceso
                    // Formato: Soldadura_NombreClase_IdOT
                    $processIdParts = explode('_', $proceso->id_proceso);
                    $className = isset($processIdParts[1]) ? $processIdParts[1] : 'N/A';
                    $workOrderId = isset($processIdParts[2]) ? $processIdParts[2] : 'N/A';

                    $piecesData[] = [
                        'n_juego' => $piece->n_juego ?? 'N/A',
                        'operador' => $operatorName,
                        'clase' => $className,
                        'orden_trabajo' => $workOrderId,
                        'peso_pieza' => $piece->pesoxpieza ?? 'N/A',
                        'tiempo_aplicacion' => $piece->tiempo_aplicacion ?? 'N/A',
                        'tipo_soldadura' => $piece->tipo_soldadura ?? 'N/A',
                        'lote' => $piece->lote ?? 'N/A',
                        'fecha' => $piece->created_at ? $piece->created_at->format('d-m-Y') : 'N/A',
                        'hora' => $piece->created_at ? $piece->created_at->format('H:i') : 'N/A',
                        'observaciones' => $piece->observaciones ?? '',
                    ];
                }
            }

            return response()->json([
                'success' => true,
                'pieces' => $piecesData,
                'total' => count($piecesData)
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener información de Soldadura: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Descargar PDF de información extra de Soldadura
     */
    public function downloadSoldaduraExtraInfoPDF(Request $request)
    {
        try {
            // Recibir filtros del query string
            $filters = $request->all();

            $soldaduraPieces = $this->applySoldaduraFilters($filters);
            $piecesData = [];

            foreach ($soldaduraPieces as $piece) {
                $proceso = Soldadura::find($piece->id_proceso);
                $meta = Metas::find($piece->id_meta);
                $operatorName = 'N/A';

                if ($meta) {
                    $operator = User::where('matricula', $meta->id_usuario)->first();
                    if ($operator) {
                        $operatorName = $operator->nombre . ' ' . $operator->a_paterno . ' ' . $operator->a_materno;
                    }
                }

                if ($proceso) {
                    $processIdParts = explode('_', $proceso->id_proceso);
                    $className = isset($processIdParts[1]) ? $processIdParts[1] : 'N/A';
                    $workOrderId = isset($processIdParts[2]) ? $processIdParts[2] : 'N/A';

                    $piecesData[] = [
                        'n_juego' => $piece->n_juego ?? 'N/A',
                        'operador' => $operatorName,
                        'clase' => $className,
                        'orden_trabajo' => $workOrderId,
                        'peso_pieza' => $piece->pesoxpieza ?? 'N/A',
                        'tipo_soldadura' => $piece->tipo_soldadura ?? 'N/A',
                        'lote' => $piece->lote ?? 'N/A',
                        'fecha' => $piece->created_at ? $piece->created_at->format('d-m-Y') : 'N/A',
                        'hora' => $piece->created_at ? $piece->created_at->format('H:i') : 'N/A',
                        'observaciones' => $piece->observaciones ?? '',
                    ];
                }
            }

            // Construir nombre de archivo dinámico basado en filtros
            $filenameParts = ['Reporte_Soldadura'];

            // Agregar OT si está filtrado
            if (isset($filters['workOrder']) && $filters['workOrder'] !== 'Todos' && $filters['workOrder'] !== '') {
                $otValue = $filters['workOrder'];
                // Si tiene formato "123 - Descripción", extraer solo el número
                if (strpos($otValue, ' - ') !== false) {
                    $otValue = explode(' - ', $otValue)[0];
                }
                $filenameParts[] = 'OT' . $otValue;
            }

            // Agregar Clase si está filtrada
            if (isset($filters['class']) && $filters['class'] !== 'Todos' && $filters['class'] !== '') {
                $filenameParts[] = 'Clase_' . $filters['class'];
            }

            // Agregar Operador si está filtrado
            if (isset($filters['operator']) && $filters['operator'] !== 'Todos' && $filters['operator'] !== '') {
                $operatorMatricula = is_array($filters['operator']) ? $filters['operator']['matricula'] : $filters['operator'];
                $operator = User::where('matricula', $operatorMatricula)->first();
                if ($operator) {
                    $operatorName = $operator->nombre . '_' . $operator->a_paterno;
                    // Limpiar caracteres especiales del nombre
                    $operatorName = preg_replace('/[^A-Za-z0-9_]/', '', $operatorName);
                    $filenameParts[] = 'Op_' . $operatorName;
                }
            }

            // Agregar Máquina si está filtrada
            if (isset($filters['machine']) && $filters['machine'] !== 'Todos' && $filters['machine'] !== '') {
                $machineValue = str_replace('_', 'y', $filters['machine']);
                $filenameParts[] = 'Maq' . $machineValue;
            }

            // Agregar N# Juego si está filtrado
            if (isset($filters['n_juego']) && $filters['n_juego'] !== 'Todos' && $filters['n_juego'] !== '') {
                $filenameParts[] = 'Juego' . $filters['n_juego'];
            }

            // Agregar rango de fechas si están filtradas
            if (isset($filters['dateFrom']) && $filters['dateFrom'] !== '' && $filters['dateFrom'] !== 'Todos') {
                $dateFrom = date('d-m-Y', strtotime($filters['dateFrom']));
                if (isset($filters['dateTo']) && $filters['dateTo'] !== '' && $filters['dateTo'] !== 'Todos') {
                    $dateTo = date('d-m-Y', strtotime($filters['dateTo']));
                    $filenameParts[] = $dateFrom . '_a_' . $dateTo;
                } else {
                    $filenameParts[] = 'Desde_' . $dateFrom;
                }
            } elseif (isset($filters['dateTo']) && $filters['dateTo'] !== '' && $filters['dateTo'] !== 'Todos') {
                $dateTo = date('d-m-Y', strtotime($filters['dateTo']));
                $filenameParts[] = 'Hasta_' . $dateTo;
            } else {
                // Si no hay filtro de fecha, agregar la fecha actual
                $filenameParts[] = date('d-m-Y');
            }

            // Unir todas las partes con guiones bajos
            $filename = implode('_', $filenameParts) . '.pdf';

            $pdf = Pdf::loadView('pieces_views.piecesReport.soldaduraExtraInfoPdf', compact('piecesData'));
            return $pdf->download($filename);

        } catch (\Exception $e) {
            return back()->with('error', 'Error al generar el PDF: ' . $e->getMessage());
        }
    }
}
