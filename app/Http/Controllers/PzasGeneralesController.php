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
use App\Models\CandadoObturador;
use App\Models\CandadoObturador_cnominal;
use App\Models\CandadoObturador_pza;
use App\Models\CandadoObturador_tolerancia;
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
    /**
     * @param mixed $workOrder
     * @param int $index
     * @param mixed $classes
     * @param mixed &$array
     */
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
    /**
     * @param mixed $piecesData
     * @param mixed $profile
     */
    public function search($piecesData, $profile = null)
    {
        $selectedItems = array();
        $infoPieces = array();
        $filtersData = $this->getFiltersInfo();

        // Siempre cargar observaciones: los índices de BD hacen las queries eficientes
        $isPdf = $piecesData["action"] === 'pdf';
        $pieces = $this->buscarPiezas($piecesData, $selectedItems, true);

        $pieces = $pieces == null ? array() : $pieces;

        if (isset($piecesData['status']) && $piecesData['status'] !== 'Todos' && $piecesData['status'] !== null) {
            $selectedItems['status'] = $piecesData['status'];
            $statusFilter = strtoupper($piecesData['status']);
            $filteredPieces = [];
            foreach ($pieces as $p) {
                $libVal = $p[9] ?? null;
                $errVal = $p[5] ?? 'Ninguno';
                $procName = $p[4] ?? '';
                $colorColumn = '#FFFFFF';
                switch (true) {
                    case $libVal == 1:
                        $colorColumn = '#79BFED';
                        break;
                    case $libVal == 2:
                        $colorColumn = '#FF6B6B';
                        break;
                    default:
                        if (str_contains($errVal, 'Incompleto')) {
                            $colorColumn = '#FFD700';
                        } elseif ($errVal === 'Ninguno') {
                            $colorColumn = '#90EE90';
                        } else {
                            if ($procName === 'Soldadura PTA' && !str_contains(strtolower($errVal), 'fundicion') && !str_contains(strtolower($errVal), 'fundición')) {
                                $colorColumn = '#90EE90';
                            } else {
                                $colorColumn = '#DDA0DD';
                            }
                        }
                        break;
                }
                if ($colorColumn === $statusFilter) {
                    $filteredPieces[] = $p;
                }
            }
            $pieces = $filteredPieces;
        } else {
            $selectedItems['status'] = 'Todos';
        }

        $this->saveInfoPzas($infoPieces, $pieces);

        if ($profile == 'admin' && ($piecesData['action'] ?? null) !== null) {
            \App\Models\SystemLog::create([
                'user_matricula' => auth()->user()->matricula,
                'action' => 'Consulta Reporte de OT',
                'details' => 'El administrador consultó el reporte de piezas generales con filtros: OT (' . ($selectedItems['workOrder'] ?? 'Todos') . '), Clase (' . ($selectedItems['class'] ?? 'Todos') . '), Proceso (' . ($selectedItems['process'] ?? 'Todos') . ').',
            ]);
        }

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
            @ini_set('memory_limit', '2048M');
            @set_time_limit(300);

            $pdf = Pdf::loadView('pieces_views.piecesReport.pdf', compact('pieces', 'piecesData', 'infoPieces', 'filtersData', 'selectedItems'));
            $filename = $this->generatePdfFilename($selectedItems, "Piezas");
            return $pdf->download($filename);
        }
    }

    /**
     * @param mixed $selectedItems
     * @param mixed $reportType
     */
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
            "class" => ["Bombillo", "Molde", "Obturador", "Fondo", "Corona", "Plato", "Embudo", "Cabeza de Soplo", "Candado Obturador"],
            "operator" => $this->objectToArrayFromDB($users, "operator"),
            "machine" => [1, 2, 3, 4, 5, 6, 7],
            "process" => ["Cepillado", "Desbaste Exterior", "Revision Laterales", "Primera Operacion", "Barreno Maniobra", "Segunda Operacion", "Soldadura", "Soldadura PTA", "Rectificado", "Asentado", "Calificado", "Acabado Bombillo", "Acabado Molde", "Barreno Profundidad", "Cavidades", "Copiado", "Off Set", "Palomas", "Rebajes", "Operacion Equipo_1 operacion", "Operacion Equipo_2 operacion", "Embudo CM", "Primera Operacion Cabeza Soplo", "Segunda Operacion Cabeza Soplo"],
            "error" => ["Ninguno", "Maquinado", "Fundicion"],
        );
        return $filtersData;
    }
    /**
     * @param mixed $object
     * @param mixed $param
     * @param mixed $molduras
     */
    public function objectToArrayFromDB($object, $param, $molduras = null)
    {
        $array = array();
        foreach ($object as $item) {
            if (($param == "operator" && $item->perfil == 2)) {
                array_push($array, $item);
            } else if ($param == "workOrder") {
                // Usa cache de molduras si está disponible (0 queries), si no hace 1 query
                $molding = $molduras ? $molduras->get($item->id_moldura) : Moldura::query()->where('id', $item->id_moldura)->first();
                $text = $item->id . " - " . ($molding ? $molding->nombre : '?');
                array_push($array, $text);
            }
        }
        return $array;
    }
    /**
     * @param Request $request
     */
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
            "status" => $request->status,
            "action" => $request->input("action"),
        );
        return $this->search($datosPiezas, 'admin');
    }
    /**
     * @param mixed $piecesData
     * @param mixed &$itemElegidos
     * @param mixed bool $includeObservations
     */
    public function buscarPiezas($piecesData, &$itemElegidos, bool $includeObservations = false)
    {
        // ── OPTIMIZACIÓN: filtrar directamente en SQL en lugar de cargar Pieza::all() ──
        $finishedClassIds = Clase::query()->where('finalizada', '!=', 0)->pluck('id')->toArray();
        $query = Pieza::query();
        if (!empty($finishedClassIds)) {
            $query->whereNotIn('id_clase', $finishedClassIds, 'and');
        }

        foreach ($piecesData as $key => $value) {
            if ($key === 'action')
                continue;

            if ($value === null || $value === 'Todos' || $value === '') {
                $itemElegidos[$key] = 'Todos';
                continue;
            }

            $itemElegidos[$key] = $value;

            switch ($key) {
                case 'workOrder':
                    $workOrderId = explode(' - ', $value)[0];
                    $workOrder = Orden_trabajo::query()->find($workOrderId);
                    $molding = Moldura::query()->find($workOrder->id_moldura);
                    $itemElegidos[$key] = $workOrder->id . ' - ' . ($molding ? $molding->nombre : '?');
                    $query->where('id_ot', $workOrderId);
                    break;
                case 'class':
                    $claseIds = Clase::query()->where('nombre', $value)->pluck('id');
                    $query->whereIn('id_clase', $claseIds, 'and', false);
                    break;
                case 'operator':
                    $user = User::query()->where('matricula', $value)->first();
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
    /**
     * @param mixed $clase
     */
    public function procesosClase($clase)
    {
        $procesos = array();
        $procesosClase = Procesos::query()->where('id_clase', $clase->id)->first();
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
    /**
     * @param mixed $arrayP
     * @param mixed $posicion
     * @param mixed $elemento
     */
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
    /**
     * @param mixed $arrayP
     * @param mixed bool $includeObservations
     */
    public function saveInArray($arrayP, bool $includeObservations = false)
    {
        // ── OPTIMIZACIÓN: pre-cargar todo en memoria para eliminar N+1 queries ──
        $finishedClassIds = Clase::query()->where('finalizada', '!=', 0)->pluck('id')->toArray();
        $usersCache = User::all()->keyBy('matricula');
        $clasesCache = Clase::all()->keyBy('id');

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

                    $procesosDB = $modelClass::query()->whereIn('id_proceso', $idProcesos)->get()->keyBy('id_proceso');
                    $procesosDBMap[$nombreProceso] = $procesosDB;

                    $parentDbIds = $procesosDB->pluck('id')->toArray();
                    if (!empty($parentDbIds)) {
                        $childPieces = $modelPiecesClass::query()->whereIn('id_proceso', $parentDbIds)->get();
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
                } catch (\Throwable $e) {
                }
            }
        }

        $array = array();
        $juegosGuardados = array();
        $contador = 0;
        $mitad = false;

        // ── OPTIMIZACIÓN DE METAS (TIEMPOS) ──
        $otsArray = collect($arrayP)->pluck('id_ot')->unique();
        $clasesArray = collect($arrayP)->pluck('id_clase')->unique();

        $metasDB = Metas::query()->whereIn('id_ot', $otsArray, 'and', false)
            ->whereIn('id_clase', $clasesArray, 'and', false)
            ->get();

        $metasCruzadas = collect($metasDB)->groupBy(function ($m) {
            return $m->id_ot . '_' . $m->id_clase . '_' . $m->proceso . '_' . $m->fecha;
        });

        // ── FALLBACK: Metas por Operador si las fechas no coinciden ──
        $metasPorOperador = collect($metasDB)->sortByDesc('fecha')->groupBy(function ($m) {
            return $m->id_ot . '_' . $m->id_clase . '_' . $m->proceso . '_' . $m->id_usuario;
        });

        foreach ($arrayP as $item) {
            if (in_array($item->id_clase, $finishedClassIds)) {
                continue;
            }

            $band = false;
            $numJuego = $this->getPiezaNumber($item->n_pieza);

            if (substr($item->n_pieza, -1) !== 'J') { // Es mitad (H o M)
                $mitad = true;
                $juegoKey = $numJuego . 'J_' . $item->proceso . '_' . $item->id_clase . '_' . $item->id_ot;

                if (!in_array($juegoKey, $juegosGuardados)) {
                    $band = true;
                    $array[$contador][1] = $numJuego . 'J';
                    $juegosGuardados[] = $juegoKey;

                    // ── Buscar mitades desde índice en memoria (0 queries) ──
                    $indexKey = $item->id_clase . '_' . $item->proceso . '_' . $numJuego;
                    $group = $piezasIndex->get($indexKey, collect());
                    $pzaH = $group->firstWhere('n_pieza', $numJuego . 'H');
                    $pzaM = $group->firstWhere('n_pieza', $numJuego . 'M');

                    // ── Si no se encontró alguna mitad en el índice en memoria,
                    //    puede ser que esté filtrada (ej. filtro por operador).
                    //    Buscarla en BD antes de declarar el juego incompleto. ──
                    if (!$pzaH) {
                        $pzaH = Pieza::query()->where('n_pieza', $numJuego . 'H')
                            ->where('id_clase', $item->id_clase)
                            ->where('proceso', $item->proceso)
                            ->where('id_ot', $item->id_ot)
                            ->first();
                    }
                    if (!$pzaM) {
                        $pzaM = Pieza::query()->where('n_pieza', $numJuego . 'M')
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
                        if (!$opH)
                            $opH = User::query()->where('matricula', $pzaH->id_operador)->first();
                        if (!$opM)
                            $opM = User::query()->where('matricula', $pzaM->id_operador)->first();
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
                $band = true;
                $mitad = false;
                $array[$contador][1] = $item->n_pieza;
                $op = $usersCache->get($item->id_operador);
                $array[$contador][2] = $op ? "{$op->nombre} {$op->a_paterno} {$op->a_materno}" : '(desconocido)';
                $array[$contador][5] = $item->error;
            }

            // Usuario de liberación desde cache (0 queries)
            $userLib = $usersCache->get($item->user_liberacion);

            if ($band) {
                $array[$contador][0] = $item->id_ot;
                $array[$contador][3] = $item->maquina;
                $array[$contador]['id_clase'] = $item->id_clase;
                $className = $clasesCache->get($item->id_clase);
                $array[$contador]['className'] = $className ? $className->nombre : null;
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
                $date = new \DateTime($item->created_at);
                $array[$contador][6] = $date->format('Y-m-d H:i:s');

                $metaKey = $item->id_ot . '_' . $item->id_clase . '_' . $item->proceso . '_' . $date->format('Y-m-d');
                $mMatchGroup = $metasCruzadas->get($metaKey, collect());
                $mMatch = $mMatchGroup->first(); // Tomar primera coincidencia de la meta por turno/proceso

                // Si no coincide por fecha exacta (ej. pieza creada días después de la meta)
                if (!$mMatch) {
                    $metaOpKey = $item->id_ot . '_' . $item->id_clase . '_' . $item->proceso . '_' . $item->id_operador;
                    $mMatchOpGroup = $metasPorOperador->get($metaOpKey, collect());
                    $mMatch = $mMatchOpGroup->first(); // Tomar la más reciente de ese operador
                }

                if ($mMatch && $mMatch->h_inicio && $mMatch->h_termino) {
                    $array[$contador]['hora_inicio'] = $mMatch->h_inicio;
                    $array[$contador]['hora_termino'] = $mMatch->h_termino;

                    $inicioStr = new \DateTime($mMatch->h_inicio);
                    $terminoStr = new \DateTime($mMatch->h_termino);
                    $array[$contador]['tiempo_total'] = $inicioStr->diff($terminoStr)->format('%H:%I:%S');
                } else {
                    $array[$contador]['hora_inicio'] = 'N/A';
                    $array[$contador]['hora_termino'] = 'N/A';
                    $array[$contador]['tiempo_total'] = 'N/A';
                }

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
    /**
     * @param mixed &$infoPiezas
     * @param mixed $piezas
     */
    public function saveInfoPzas(&$infoPiezas, $piezas)
    {
        if ($piezas == null || count($piezas) == 0) {
            return;
        }

        // Cache classes
        $clasesCache = Clase::all()->keyBy('id');

        // Pre-gather all process strings to fetch in bulk
        $processLookups = [];
        foreach ($piezas as $p) {
            $claseObj = $clasesCache->get($p["id_clase"]);
            $claseName = $claseObj ? $claseObj->nombre : null;
            $procName = $p[4];
            $otId = $p[0];

            $model = null;
            $lookupKey = null;
            switch ($procName) {
                case 'Operacion Equipo_1 operacion':
                    $lookupKey = 'Operacion_Equipo_1_operacion_' . $claseName . "_" . $otId;
                    $model = ($claseName == 'Candado Obturador') ? CandadoObturador::class : PySOpeSoldadura::class;
                    break;
                case 'Operacion Equipo_2 operacion':
                    $lookupKey = 'Operacion_Equipo_2_operacion_' . $claseName . "_" . $otId;
                    $model = ($claseName == 'Candado Obturador') ? CandadoObturador::class : PySOpeSoldadura::class;
                    break;
                case 'Cepillado':
                    $lookupKey = 'Cepillado_' . $claseName . '_' . $otId;
                    $model = Cepillado::class;
                    break;
                case 'Desbaste Exterior':
                    $lookupKey = 'Desbaste_Exterior_' . $claseName . '_' . $otId;
                    $model = DesbasteExterior::class;
                    break;
                case 'Revision Laterales':
                    $lookupKey = 'Revision_Laterales_' . $claseName . '_' . $otId;
                    $model = RevLaterales::class;
                    break;
                case 'Primera Operacion':
                    $lookupKey = 'Primera_Operacion_' . $claseName . '_' . $otId;
                    $model = PrimeraOpeSoldadura::class;
                    break;
                case 'Barreno Maniobra':
                    $lookupKey = 'Barreno_Maniobra_' . $claseName . '_' . $otId;
                    $model = BarrenoManiobra::class;
                    break;
                case 'Segunda Operacion':
                    $lookupKey = 'Segunda_Operacion_' . $claseName . '_' . $otId;
                    $model = SegundaOpeSoldadura::class;
                    break;
                case 'Soldadura':
                    $lookupKey = 'Soldadura_' . $claseName . '_' . $otId;
                    $model = Soldadura::class;
                    break;
                case 'Soldadura PTA':
                    $lookupKey = 'Soldadura_PTA_' . $claseName . '_' . $otId;
                    $model = SoldaduraPTA::class;
                    break;
                case 'Rectificado':
                    $lookupKey = 'Rectificado_' . $claseName . '_' . $otId;
                    $model = Rectificado::class;
                    break;
                case 'Asentado':
                    $lookupKey = 'Asentado_' . $claseName . '_' . $otId;
                    $model = Asentado::class;
                    break;
                case 'Calificado':
                    $lookupKey = 'Calificado_' . $claseName . '_' . $otId;
                    $model = revCalificado::class;
                    break;
                case 'Acabado Bombillo':
                    $lookupKey = 'Acabado_Bombillo_' . $claseName . '_' . $otId;
                    $model = AcabadoBombilo::class;
                    break;
                case 'Acabado Molde':
                    $lookupKey = 'Acabado_Molde_' . $claseName . '_' . $otId;
                    $model = AcabadoMolde::class;
                    break;
                case 'Barreno Profundidad':
                    $lookupKey = 'Barreno_Profundidad_' . $claseName . '_' . $otId;
                    $model = BarrenoProfundidad::class;
                    break;
                case 'Cavidades':
                    $lookupKey = 'Cavidades_' . $claseName . '_' . $otId;
                    $model = Cavidades::class;
                    break;
                case 'Copiado':
                    $lookupKey = 'Copiado_' . $claseName . '_' . $otId;
                    $model = Copiado::class;
                    break;
                case 'Off Set':
                    $lookupKey = 'Off_Set_' . $claseName . '_' . $otId;
                    $model = OffSet::class;
                    break;
                case 'Palomas':
                    $lookupKey = 'Palomas_' . $claseName . '_' . $otId;
                    $model = Palomas::class;
                    break;
                case 'Rebajes':
                    $lookupKey = 'Rebajes_' . $claseName . '_' . $otId;
                    $model = Rebajes::class;
                    break;
                case 'Embudo CM':
                    $lookupKey = 'Embudo_CM_' . $claseName . '_' . $otId;
                    $model = EmbudoCM::class;
                    break;
                case 'Primera Operacion Cabeza Soplo':
                    $lookupKey = 'Primera_Operacion_Cabeza_Soplo_' . $claseName . '_' . $otId;
                    $model = PrimeraOperacionCabezaSoplo::class;
                    break;
                case 'Segunda Operacion Cabeza Soplo':
                    $lookupKey = 'Segunda_Operacion_Cabeza_Soplo_' . $claseName . '_' . $otId;
                    $model = SegundaOperacionCabezaSoplo::class;
                    break;
            }

            if ($lookupKey) {
                $processLookups[$model][$lookupKey] = null;
            }
        }

        // Bulk fetch all needed process records
        foreach ($processLookups as $model => $keys) {
            $records = $model::query()->whereIn('id_proceso', array_keys($keys))->get();
            foreach ($records as $r) {
                $processLookups[$model][$r->id_proceso] = $r;
            }
        }

        $contador = 0;
        foreach ($piezas as $pieza) {
            $claseObj = $clasesCache->get($pieza["id_clase"]);
            $claseName = $claseObj ? $claseObj->nombre : null;
            $procName = $pieza[4];
            $otId = $pieza[0];

            $model = null;
            $lookupKey = null;
            switch ($procName) {
                case 'Operacion Equipo_1 operacion':
                    $lookupKey = 'Operacion_Equipo_1_operacion_' . $claseName . '_' . $otId;
                    $model = ($claseName == 'Candado Obturador') ? CandadoObturador::class : PySOpeSoldadura::class;
                    break;
                case 'Operacion Equipo_2 operacion':
                    $lookupKey = 'Operacion_Equipo_2_operacion_' . $claseName . '_' . $otId;
                    $model = ($claseName == 'Candado Obturador') ? CandadoObturador::class : PySOpeSoldadura::class;
                    break;
                case 'Cepillado':
                    $lookupKey = 'Cepillado_' . $claseName . '_' . $otId;
                    $model = Cepillado::class;
                    break;
                case 'Desbaste Exterior':
                    $lookupKey = 'Desbaste_Exterior_' . $claseName . '_' . $otId;
                    $model = DesbasteExterior::class;
                    break;
                case 'Revision Laterales':
                    $lookupKey = 'Revision_Laterales_' . $claseName . '_' . $otId;
                    $model = RevLaterales::class;
                    break;
                case 'Primera Operacion':
                    $lookupKey = 'Primera_Operacion_' . $claseName . '_' . $otId;
                    $model = PrimeraOpeSoldadura::class;
                    break;
                case 'Barreno Maniobra':
                    $lookupKey = 'Barreno_Maniobra_' . $claseName . '_' . $otId;
                    $model = BarrenoManiobra::class;
                    break;
                case 'Segunda Operacion':
                    $lookupKey = 'Segunda_Operacion_' . $claseName . '_' . $otId;
                    $model = SegundaOpeSoldadura::class;
                    break;
                case 'Soldadura':
                    $lookupKey = 'Soldadura_' . $claseName . '_' . $otId;
                    $model = Soldadura::class;
                    break;
                case 'Soldadura PTA':
                    $lookupKey = 'Soldadura_PTA_' . $claseName . '_' . $otId;
                    $model = SoldaduraPTA::class;
                    break;
                case 'Rectificado':
                    $lookupKey = 'Rectificado_' . $claseName . '_' . $otId;
                    $model = Rectificado::class;
                    break;
                case 'Asentado':
                    $lookupKey = 'Asentado_' . $claseName . '_' . $otId;
                    $model = Asentado::class;
                    break;
                case 'Calificado':
                    $lookupKey = 'Calificado_' . $claseName . '_' . $otId;
                    $model = revCalificado::class;
                    break;
                case 'Acabado Bombillo':
                    $lookupKey = 'Acabado_Bombillo_' . $claseName . '_' . $otId;
                    $model = AcabadoBombilo::class;
                    break;
                case 'Acabado Molde':
                    $lookupKey = 'Acabado_Molde_' . $claseName . '_' . $otId;
                    $model = AcabadoMolde::class;
                    break;
                case 'Barreno Profundidad':
                    $lookupKey = 'Barreno_Profundidad_' . $claseName . '_' . $otId;
                    $model = BarrenoProfundidad::class;
                    break;
                case 'Cavidades':
                    $lookupKey = 'Cavidades_' . $claseName . '_' . $otId;
                    $model = Cavidades::class;
                    break;
                case 'Copiado':
                    $lookupKey = 'Copiado_' . $claseName . '_' . $otId;
                    $model = Copiado::class;
                    break;
                case 'Off Set':
                    $lookupKey = 'Off_Set_' . $claseName . '_' . $otId;
                    $model = OffSet::class;
                    break;
                case 'Palomas':
                    $lookupKey = 'Palomas_' . $claseName . '_' . $otId;
                    $model = Palomas::class;
                    break;
                case 'Rebajes':
                    $lookupKey = 'Rebajes_' . $claseName . '_' . $otId;
                    $model = Rebajes::class;
                    break;
                case 'Embudo CM':
                    $lookupKey = 'Embudo_CM_' . $claseName . '_' . $otId;
                    $model = EmbudoCM::class;
                    break;
                case 'Primera Operacion Cabeza Soplo':
                    $lookupKey = 'Primera_Operacion_Cabeza_Soplo_' . $claseName . '_' . $otId;
                    $model = PrimeraOperacionCabezaSoplo::class;
                    break;
                case 'Segunda Operacion Cabeza Soplo':
                    $lookupKey = 'Segunda_Operacion_Cabeza_Soplo_' . $claseName . '_' . $otId;
                    $model = SegundaOperacionCabezaSoplo::class;
                    break;
            }

            $id_proceso = ($model && $lookupKey) ? ($processLookups[$model][$lookupKey] ?? null) : null;
            $infoPiezas[$contador][1] = $procName;

            if ($id_proceso) {
                if (end($pieza) == "mitad") {
                    $numero = $this->getPiezaNumber($pieza[1]);
                    $infoPiezas[$contador][0][0] = $numero . "H" . $id_proceso->id;
                    $infoPiezas[$contador][0][1] = $numero . "M" . $id_proceso->id;
                } else {
                    $infoPiezas[$contador][0][0] = $pieza[1] . $id_proceso->id;
                }
            } else {
                $infoPiezas[$contador][0][0] = $pieza[1] . "??";
            }

            $infoPiezas[$contador][2] = $pieza[5];
            $contador++;
        }
    }
    /**
     * @param Request $request
     */
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
        $clase = Clase::query()->where('nombre', $className)->where('id_ot', $otId)->first();
        // Fallback: search by nombre only if not found with id_ot
        if (!$clase) {
            $clase = Clase::query()->where('nombre', $className)->first();
        }
        if (!$clase) {
            return response()->json([]);
        }

        // Search pieces - n_pieza column only (n_juego does not exist in this table)
        $piezas = Pieza::query()->where('id_ot', $otId)
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
    public function showPiece(string $pieces, string $process, string $profile)
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
                    $pza = Pza_cepillado::query()->where('id_pza', $piece)->first();
                    if ($pza) {
                        array_push($pieceInfo, $pza);
                    }
                }

                if (empty($pieceInfo)) {
                    return redirect()->back()->with('error', 'No se encontraron las piezas solicitadas.');
                }
                //Obtener Cotas nominales y tolerancias
                $id_process = Cepillado::query()->find($pieceInfo[0]->id_proceso);
                $cnRecord = $id_process ? Cepillado_cnominal::query()->where('id_proceso', $id_process->id_proceso)->first() : null;
                $tolRecord = $id_process ? Cepillado_tolerancia::query()->where('id_proceso', $id_process->id_proceso)->first() : null;
                $cNominal = $cnRecord ? $cnRecord->toArray() : null;
                $tolerance = $tolRecord ? $tolRecord->toArray() : null;
                $process = 'Cepillado';
                break;
            case 'Desbaste Exterior':
                //Obtener informacion de la pieza elegida
                $pieceInfo = array();
                $pieces = explode(",", $pieces);
                foreach ($pieces as $piece) {
                    array_push($pieceInfo, Desbaste_pza::query()->where('id_pza', $piece)->first());
                }
                //Obtener Cotas nominales y tolerancias
                $id_process = DesbasteExterior::query()->find($pieceInfo[0]->id_proceso);
                // $cNominal = Desbaste_cnominal::query()->where('id_proceso', $id_process->id_proceso)->first()->toArray();
                $cnRecord = $id_process ? Desbaste_cnominal::query()->where('id_proceso', $id_process->id_proceso)->first() : null;
                $tolRecord = $id_process ? Desbaste_tolerancia::query()->where('id_proceso', $id_process->id_proceso)->first() : null;
                $cNominal = $cnRecord ? $cnRecord->toArray() : null;
                $tolerance = $tolRecord ? $tolRecord->toArray() : null;
                $process = 'Desbaste Exterior';
                break;
            case 'Revision Laterales':
                //Obtener informacion de la pieza elegida
                $pieceInfo = array();
                $pieces = explode(",", $pieces);
                foreach ($pieces as $piece) {
                    array_push($pieceInfo, RevLaterales_pza::query()->where('id_pza', $piece)->first());
                }
                //Obtener Cotas nominales y tolerancias
                $id_process = RevLaterales::query()->find($pieceInfo[0]->id_proceso);
                $cnRecord = $id_process ? RevLaterales_cnominal::query()->where('id_proceso', $id_process->id_proceso)->first() : null;
                $tolRecord = $id_process ? RevLaterales_tolerancia::query()->where('id_proceso', $id_process->id_proceso)->first() : null;
                $cNominal = $cnRecord ? $cnRecord->toArray() : null;
                $tolerance = $tolRecord ? $tolRecord->toArray() : null;
                $process = 'Revision Laterales';
                break;
            case 'Primera Operacion':
                //Obtener informacion de la pieza elegida
                $pieceInfo = array();
                $pieces = explode(",", $pieces);
                foreach ($pieces as $piece) {
                    array_push($pieceInfo, PrimeraOpeSoldadura_pza::query()->where('id_pza', $piece)->first());
                }
                //Obtener Cotas nominales y tolerancias
                $id_process = PrimeraOpeSoldadura::query()->find($pieceInfo[0]->id_proceso);
                $cNominal = PrimeraOpeSoldadura_cnominal::query()->where('id_proceso', $id_process->id_proceso)->first()->toArray();
                $tolerance = PrimeraOpeSoldadura_tolerancia::query()->where('id_proceso', $id_process->id_proceso)->first()->toArray();
                $process = 'Primera Operacion';
                break;
            case 'Barreno Maniobra':
                //Obtener informacion de la pieza elegida
                $pieceInfo = array();
                $pieces = explode(",", $pieces);
                foreach ($pieces as $piece) {
                    array_push($pieceInfo, BarrenoManiobra_pza::query()->where('id_pza', $piece)->first());
                }
                //Obtener Cotas nominales y tolerancias
                $id_process = BarrenoManiobra::query()->find($pieceInfo[0]->id_proceso);
                $cNominal = BarrenoManiobra_cnominal::query()->where('id_proceso', $id_process->id_proceso)->first()->toArray();
                $tolerance = BarrenoManiobra_tolerancia::query()->where('id_proceso', $id_process->id_proceso)->first()->toArray();
                $process = 'Barreno Maniobra';
                break;
            case 'Segunda Operacion':
                //Obtener informacion de la pieza elegida
                $pieceInfo = array();
                $pieces = explode(",", $pieces);
                foreach ($pieces as $piece) {
                    array_push($pieceInfo, SegundaOpeSoldadura_pza::query()->where('id_pza', $piece)->first());
                }
                //Obtener Cotas nominales y tolerancias
                $id_process = SegundaOpeSoldadura::query()->find($pieceInfo[0]->id_proceso);
                $cNominal = SegundaOpeSoldadura_cnominal::query()->where('id_proceso', $id_process->id_proceso)->first()->toArray();
                $tolerance = SegundaOpeSoldadura_tolerancia::query()->where('id_proceso', $id_process->id_proceso)->first()->toArray();
                $process = 'Segunda Operacion';
                break;
            case 'Soldadura':
                //Obtener informacion de la pieza elegida
                $piecesArray = explode(",", $pieces);
                $pieceInfo = array();
                foreach ($piecesArray as $pza) {
                    $p = Soldadura_pza::query()->where('id_pza', $pza)->first();
                    if ($p) {
                        array_push($pieceInfo, $p);
                    }
                }
                if (count($pieceInfo) == 0)
                    return redirect()->back()->with('error', 'No se encontraron las piezas solicitadas.');
                //Obtener Cotas nominales y tolerancias
                $id_process = Soldadura::query()->find($pieceInfo[0]->id_proceso);
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
                            $unaPieza = SoldaduraPTA_pza::query()->where('n_pieza', $n_pieza)->where('id_proceso', $id_proceso)->first();
                        }
                    } else {
                        $p = SoldaduraPTA_pza::query()->where('id_pza', $pza)->first();
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
                $id_process = SoldaduraPTA::query()->find($unaPieza->id_proceso);
                $pieceInfo = SoldaduraPTA_pza::query()->where('id_proceso', $id_process->id)
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
                    $p = Rectificado_pza::query()->where('id_pza', $pza)->first();
                    if ($p) {
                        array_push($pieceInfo, $p);
                    }
                }
                if (count($pieceInfo) == 0)
                    return redirect()->back()->with('error', 'No se encontraron las piezas solicitadas.');
                //Obtener Cotas nominales y tolerancias
                $id_process = Rectificado::query()->find($pieceInfo[0]->id_proceso);
                $cNominal = 0;
                $tolerance = 0;
                $process = 'Rectificado';
                break;
            case 'Asentado':
                //Obtener informacion de la pieza elegida
                $piecesArray = explode(",", $pieces);
                $pieceInfo = array();
                foreach ($piecesArray as $pza) {
                    $p = Asentado_pza::query()->where('id_pza', $pza)->first();
                    if ($p) {
                        array_push($pieceInfo, $p);
                    }
                }
                if (count($pieceInfo) == 0)
                    return redirect()->back()->with('error', 'No se encontraron las piezas solicitadas.');
                //Obtener Cotas nominales y tolerancias
                $id_process = Asentado::query()->find($pieceInfo[0]->id_proceso);
                $cNominal = 0;
                $tolerance = 0;
                $process = 'Asentado';
                break;

            case 'Calificado':
                //Obtener informacion de la pieza elegida
                $pieceInfo = array();
                $pieces = explode(",", $pieces);
                foreach ($pieces as $piece) {
                    array_push($pieceInfo, revCalificado_pza::query()->where('id_pza', $piece)->first());
                }
                //Obtener Cotas nominales y tolerancias
                $id_process = revCalificado::query()->find($pieceInfo[0]->id_proceso);
                $cNominal = revCalificado_cnominal::query()->where('id_proceso', $id_process->id_proceso)->first()->toArray();
                $tolerance = revCalificado_tolerancia::query()->where('id_proceso', $id_process->id_proceso)->first()->toArray();
                $process = 'Calificado';
                break;
            case 'Acabado Bombillo':
                //Obtener informacion de la pieza elegida
                $pieceInfo = array();
                $pieces = explode(",", $pieces);
                foreach ($pieces as $piece) {
                    array_push($pieceInfo, AcabadoBombilo_pza::query()->where('id_pza', $piece)->first());
                }
                //Obtener Cotas nominales y tolerancias
                $id_process = AcabadoBombilo::query()->find($pieceInfo[0]->id_proceso);
                $cNominal = AcabadoBombilo_cnominal::query()->where('id_proceso', $id_process->id_proceso)->first()->toArray();
                $tolerance = AcabadoBombilo_tolerancia::query()->where('id_proceso', $id_process->id_proceso)->first()->toArray();
                $process = 'Acabado Bombillo';
                break;
            case 'Acabado Molde':
                //Obtener informacion de la pieza elegida
                $pieceInfo = array();
                $pieces = explode(",", $pieces);
                foreach ($pieces as $piece) {
                    array_push($pieceInfo, AcabadoMolde_pza::query()->where('id_pza', $piece)->first());
                }
                //Obtener Cotas nominales y tolerancias
                $id_process = AcabadoMolde::query()->find($pieceInfo[0]->id_proceso);
                $cNominal = AcabadoMolde_cnominal::query()->where('id_proceso', $id_process->id_proceso)->first()->toArray();
                $tolerance = AcabadoMolde_tolerancia::query()->where('id_proceso', $id_process->id_proceso)->first()->toArray();
                $process = 'Acabado Molde';
                break;
            case 'Barreno Profundidad':
                //Obtener informacion de la pieza elegida
                $pieceInfo = BarrenoProfundidad_pza::query()->where('id_pza', $pieces)->first();
                //Obtener Cotas nominales y tolerancias
                $id_process = BarrenoProfundidad::query()->find($pieceInfo->id_proceso);
                $cNominal = BarrenoProfundidad_cnominal::query()->where('id_proceso', $id_process->id_proceso)->first()->toArray();
                $tolerance = BarrenoProfundidad_tolerancia::query()->where('id_proceso', $id_process->id_proceso)->first()->toArray();
                $process = 'Barreno Profundidad';
                break;
            case 'Cavidades':
                //Obtener informacion de la pieza elegida
                $pieceInfo = array();
                $pieces = explode(",", $pieces);
                foreach ($pieces as $piece) {
                    array_push($pieceInfo, Cavidades_pza::query()->where('id_pza', $piece)->first());
                }
                //Obtener Cotas nominales y tolerancias
                $id_process = Cavidades::query()->find($pieceInfo[0]->id_proceso);
                $cNominal = Cavidades_cnominal::query()->where('id_proceso', $id_process->id_proceso)->first();
                $tolerance = Cavidades_tolerancia::query()->where('id_proceso', $id_process->id_proceso)->first();
                $process = 'Cavidades';
                break;
            case 'Copiado':
                //Obtener informacion de la pieza elegida
                $pieceInfo = array();
                $pieces = explode(",", $pieces);
                foreach ($pieces as $piece) {
                    array_push($pieceInfo, Copiado_pza::query()->where('id_pza', $piece)->first());
                }
                //Obtener Cotas nominales y tolerancias
                $id_process = Copiado::query()->find($pieceInfo[0]->id_proceso);
                $cNominal = Copiado_cnominal::query()->where('id_proceso', $id_process->id_proceso)->first();
                $tolerance = Copiado_tolerancia::query()->where('id_proceso', $id_process->id_proceso)->first();
                $process = 'Copiado';
                break;
            case 'Off Set':
                //Obtener informacion de la pieza elegida
                $pieceInfo = array();
                $pieces = explode(",", $pieces);
                foreach ($pieces as $piece) {
                    array_push($pieceInfo, OffSet_pza::query()->where('id_pza', $piece)->first());
                }
                //Obtener Cotas nominales y tolerancias
                $id_process = OffSet::query()->find($pieceInfo[0]->id_proceso);
                $cNominal = OffSet_cnominal::query()->where('id_proceso', $id_process->id_proceso)->first();
                $tolerance = OffSet_tolerancia::query()->where('id_proceso', $id_process->id_proceso)->first();
                $process = 'Off Set';
                break;
            case 'Palomas':
                //Obtener informacion de la pieza elegida
                $pieceInfo = Palomas_pza::query()->where('id_pza', $pieces)->first();
                //Obtener Cotas nominales y tolerancias
                $id_process = Palomas::query()->find($pieceInfo->id_proceso);
                $cNominal = Palomas_cnominal::query()->where('id_proceso', $id_process->id_proceso)->first()->toArray();
                $tolerance = Palomas_tolerancia::query()->where('id_proceso', $id_process->id_proceso)->first()->toArray();
                $process = 'Palomas';
                break;
            case 'Rebajes':
                //Obtener informacion de la pieza elegida
                $pieceInfo = Rebajes_pza::query()->where('id_pza', $pieces)->first();
                //Obtener Cotas nominales y tolerancias
                $id_process = Rebajes::query()->find($pieceInfo->id_proceso);
                $cNominal = Rebajes_cnominal::query()->where('id_proceso', $id_process->id_proceso)->first()->toArray();
                $tolerance = Rebajes_tolerancia::query()->where('id_proceso', $id_process->id_proceso)->first()->toArray();
                $process = 'Rebajes';
                break;
            case 'Operacion Equipo_1 operacion':
            case 'Operacion Equipo_2 operacion':
                // Auto-detect class: try CandadoObturador_pza first, fall back to PySOpeSoldadura_pza
                $pieceInfo = array();
                $piece = explode(",", $pieces);
                foreach ($piece as $pza) {
                    $p = CandadoObturador_pza::query()->where('id_pza', $pza)->first();
                    if ($p) {
                        array_push($pieceInfo, $p);
                    } else {
                        $p = PySOpeSoldadura_pza::query()->where('id_pza', $pza)->first();
                        if ($p)
                            array_push($pieceInfo, $p);
                    }
                }
                if (empty($pieceInfo)) {
                    return redirect()->back()->with('error', 'No se encontraron las piezas solicitadas.');
                }
                // Detect model based on first piece class
                if ($pieceInfo[0] instanceof CandadoObturador_pza) {
                    $id_process = CandadoObturador::query()->find($pieceInfo[0]->id_proceso);
                    $cnRecord = $id_process ? CandadoObturador_cnominal::query()->where('id_proceso', $id_process->id_proceso)->first() : null;
                    $tolRecord = $id_process ? CandadoObturador_tolerancia::query()->where('id_proceso', $id_process->id_proceso)->first() : null;
                } else {
                    $id_process = PySOpeSoldadura::query()->find($pieceInfo[0]->id_proceso);
                    $cnRecord = $id_process ? PySOpeSoldadura_cnominal::query()->where('id_proceso', $id_process->id_proceso)->first() : null;
                    $tolRecord = $id_process ? PySOpeSoldadura_tolerancia::query()->where('id_proceso', $id_process->id_proceso)->first() : null;
                }
                $cNominal = $cnRecord ? $cnRecord->toArray() : null;
                $tolerance = $tolRecord ? $tolRecord->toArray() : null;
                // $process is already preserved as the function argument
                break;
            case 'Embudo CM':
                //Obtener informacion de la pieza elegida
                $pieceInfo = EmbudoCM_pza::query()->where('id_pza', $pieces)->first();
                //Obtener Cotas nominales y tolerancias
                $id_process = EmbudoCM::query()->find($pieceInfo->id_proceso);
                $cNominal = EmbudoCM_cnominal::query()->where('id_proceso', $id_process->id_proceso)->first()->toArray();
                $tolerance = EmbudoCM_tolerancias::query()->where('id_proceso', $id_process->id_proceso)->first()->toArray();
                $process = 'Embudo CM';
                break;
            case 'Primera Operacion Cabeza Soplo':
                //Obtener informacion de la pieza elegida
                $piecesArray = explode(",", $pieces);
                $pieceInfo = array();
                foreach ($piecesArray as $pza) {
                    $p = PrimeraOperacionCabezaSoplo_pza::query()->where('id_pza', $pza)->first();
                    if ($p) {
                        array_push($pieceInfo, $p);
                    }
                }
                if (count($pieceInfo) == 0)
                    return redirect()->back()->with('error', 'No se encontraron las piezas solicitadas.');

                //Obtener Cotas nominales y tolerancias
                $id_process = PrimeraOperacionCabezaSoplo::query()->find($pieceInfo[0]->id_proceso);
                $cNominal = null;
                $tolerance = null;
                if ($id_process) {
                    $cnRecord = PrimeraOperacionCabezaSoplo_cnominal::query()->where('id_proceso', $id_process->id_proceso)
                        ->select('id', 'diametro_exterior', 'longitud', 'diametro_candado', 'longitud_candado')
                        ->first();
                    $cNominal = $cnRecord ? $cnRecord->toArray() : null;

                    $tolRecord = PrimeraOperacionCabezaSoplo_tolerancia::query()->where('id_proceso', $id_process->id_proceso)
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
                    $p = SegundaOperacionCabezaSoplo_pza::query()->where('id_pza', $pza)->first();
                    if ($p) {
                        array_push($pieceInfo, $p);
                    }
                }
                if (count($pieceInfo) == 0)
                    return redirect()->back()->with('error', 'No se encontraron las piezas solicitadas.');

                //Obtener Cotas nominales y tolerancias
                $id_process = SegundaOperacionCabezaSoplo::query()->find($pieceInfo[0]->id_proceso);
                $cNominal = null;
                $tolerance = null;
                if ($id_process) {
                    $cnRecord = SegundaOperacionCabezaSoplo_cnominal::query()->where('id_proceso', $id_process->id_proceso)
                        ->select('id', 'diametro_exterior', 'longitud', 'diametro_candado', 'longitud_candado')
                        ->first();
                    $cNominal = $cnRecord ? $cnRecord->toArray() : null;

                    $tolRecord = SegundaOperacionCabezaSoplo_tolerancia::query()->where('id_proceso', $id_process->id_proceso)
                        ->select('id', 'diametro_exterior1', 'diametro_exterior2', 'longitud1', 'longitud2', 'diametro_candado1', 'diametro_candado2', 'longitud_candado1', 'longitud_candado2')
                        ->first();
                    $tolerance = $tolRecord ? $tolRecord->toArray() : null;
                }
                $process = 'Segunda Operacion Cabeza Soplo';
                break;
        }
        // Obtener meta para obtener la ot y la clase
        if (is_array($pieceInfo) || $pieceInfo instanceof \Illuminate\Support\Collection) { //Si el juego es mitad o coleccion (Soldadura PTA)
            $meta = Metas::query()->find($pieceInfo[0]->id_meta);
        } else { //Si no es mitad
            $meta = Metas::query()->find($pieceInfo->id_meta);
        }
        $ot = $meta->id_ot;
        $clase = Clase::query()->find($meta->id_clase);
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
                $meta = Metas::query()->find($pza['id_meta']);
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
            $meta = Metas::query()->find($piecesInfo->id_meta);
            $nPieza = $pieceInfo->n_juego;
            $nombreOp = $this->getNameOperador($meta->id_usuario);
            $operadores[0] = array($nPieza, $nombreOp);
        }
        $piezasGroup = $piezasGroup ?? null;
        return view('pieces_views.piecesReport.chosenPiece', compact('process', 'piecesInfo', 'cNominal', 'tolerance', 'ot', 'clase', 'profile', 'operadores', 'piezasGroup'));
    }

    /**
     * @param mixed $ot
     */
    public function getOperadores($ot)
    {
        $operadores = Pieza::query()->where('id_ot', $ot)->distinct('id_operador')->pluck('id_operador');
        for ($i = 0; $i < count($operadores); $i++) {
            $operadores[$i] = User::query()->where('matricula', $operadores[$i])->first();
        }
        return $operadores;
    }
    /**
     * @param mixed $matricula
     */
    public function getNameOperador($matricula)
    {
        // Memoización: solo va a BD la primera vez que se pide esta matrícula
        if (!isset($this->usersCache[$matricula])) {
            $this->usersCache[$matricula] = User::query()->where('matricula', $matricula)->first();
        }
        $op = $this->usersCache[$matricula];
        return $op ? "{$op->nombre} {$op->a_paterno} {$op->a_materno}" : '(desconocido)';
    }
    /**
     * @param int|string $id
     */
    public function getNameClase($id)
    {
        $clase = Clase::query()->find($id);
        return $clase->nombre . " " . $clase->tamanio;
    }
    /**
     * @param mixed $pieza
     */
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
     * @param int|string $libH
     * @param int|string $libM
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
        $pH = $priority[(int) $libH] ?? 0;
        $pM = $priority[(int) $libM] ?? 0;
        return $pH >= $pM ? (int) $libH : (int) $libM;
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
        $ot = Orden_trabajo::query()->find($request->ot);
        $clase = Clase::query()->find($request->clase);
        $procesos = array();

        $proceso = Procesos::query()->where('id_clase', $clase->id)->first();
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
                        $metas = Metas::query()->where('id_clase', $clase->id)->get();
                        if (count($metas) > 0) {
                            $pzasNoCero = 0;
                            foreach ($metas as $meta) {
                                $piezas = Pza_cepillado::query()->where('id_meta', $meta->id)->get();
                                if (count($piezas) > 0) {
                                    foreach ($piezas as $pieza) {
                                        if ($pieza->estado != 0) {
                                            $user = User::query()->where('matricula', $meta->id_usuario)->first();
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
                        $metas = Metas::query()->where('id_clase', $clase->id)->get();
                        if (count($metas) > 0) {
                            $pzasNoCero = 0;
                            foreach ($metas as $meta) {
                                $piezas = Desbaste_pza::query()->where('id_meta', $meta->id)->get();
                                if (count($piezas) > 0) {
                                    foreach ($piezas as $pieza) {
                                        if ($pieza->estado != 0) {
                                            $user = User::query()->where('matricula', $meta->id_usuario)->first();
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
                        $metas = Metas::query()->where('id_clase', $clase->id)->get();
                        if (count($metas) > 0) {
                            $pzasNoCero = 0;
                            foreach ($metas as $meta) {
                                $piezas = RevLaterales_pza::query()->where('id_meta', $meta->id)->get();
                                if (count($piezas) > 0) {
                                    foreach ($piezas as $pieza) {
                                        if ($pieza->estado != 0) {
                                            $user = User::query()->where('matricula', $meta->id_usuario)->first();
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
                        $metas = Metas::query()->where('id_clase', $clase->id)->get();
                        if (count($metas) > 0) {
                            $pzasNoCero = 0;
                            foreach ($metas as $meta) {
                                $piezas = PrimeraOpeSoldadura_pza::query()->where('id_meta', $meta->id)->get();
                                if (count($piezas) > 0) {
                                    foreach ($piezas as $pieza) {
                                        if ($pieza->estado != 0) {
                                            $user = User::query()->where('matricula', $meta->id_usuario)->first();
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
                        $metas = Metas::query()->where('id_clase', $clase->id)->get();
                        if (count($metas) > 0) {
                            $pzasNoCero = 0;
                            foreach ($metas as $meta) {
                                $piezas = BarrenoManiobra_pza::query()->where('id_meta', $meta->id)->get();
                                if (count($piezas) > 0) {
                                    foreach ($piezas as $pieza) {
                                        if ($pieza->estado != 0) {
                                            $user = User::query()->where('matricula', $meta->id_usuario)->first();
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
                        $metas = Metas::query()->where('id_clase', $clase->id)->get();
                        if (count($metas) > 0) {
                            $pzasNoCero = 0;
                            foreach ($metas as $meta) {
                                $piezas = SegundaOpeSoldadura_pza::query()->where('id_meta', $meta->id)->get();
                                if (count($piezas) > 0) {
                                    foreach ($piezas as $pieza) {
                                        if ($pieza->estado != 0) {
                                            $user = User::query()->where('matricula', $meta->id_usuario)->first();
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
                        $metas = Metas::query()->where('id_clase', $clase->id)->get();
                        if (count($metas) > 0) {
                            $pzasNoCero = 0;
                            foreach ($metas as $meta) {
                                $piezas = Soldadura_pza::query()->where('id_meta', $meta->id)->get();
                                if (count($piezas) > 0) {
                                    foreach ($piezas as $pieza) {
                                        if ($pieza->estado != 0) {
                                            $user = User::query()->where('matricula', $meta->id_usuario)->first();
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
                        $metas = Metas::query()->where('id_clase', $clase->id)->get();
                        if (count($metas) > 0) {
                            $pzasNoCero = 0;
                            foreach ($metas as $meta) {
                                $piezas = SoldaduraPTA_pza::query()->where('id_meta', $meta->id)->get();
                                if (count($piezas) > 0) {
                                    foreach ($piezas as $pieza) {
                                        if ($pieza->estado != 0) {
                                            $user = User::query()->where('matricula', $meta->id_usuario)->first();
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
                        $metas = Metas::query()->where('id_clase', $clase->id)->get();
                        if (count($metas) > 0) {
                            $pzasNoCero = 0;
                            foreach ($metas as $meta) {
                                $piezas = Rectificado_pza::query()->where('id_meta', $meta->id)->get();
                                if (count($piezas) > 0) {
                                    foreach ($piezas as $pieza) {
                                        if ($pieza->estado != 0) {
                                            $user = User::query()->where('matricula', $meta->id_usuario)->first();
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
                        $metas = Metas::query()->where('id_clase', $clase->id)->get();
                        if (count($metas) > 0) {
                            $pzasNoCero = 0;
                            foreach ($metas as $meta) {
                                $piezas = Asentado_pza::query()->where('id_meta', $meta->id)->get();
                                if (count($piezas) > 0) {
                                    foreach ($piezas as $pieza) {
                                        if ($pieza->estado != 0) {
                                            $user = User::query()->where('matricula', $meta->id_usuario)->first();
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
                        $metas = Metas::query()->where('id_clase', $clase->id)->get();
                        if (count($metas) > 0) {
                            $pzasNoCero = 0;
                            foreach ($metas as $meta) {
                                $piezas = revCalificado_pza::query()->where('id_meta', $meta->id)->get();
                                if (count($piezas) > 0) {
                                    foreach ($piezas as $pieza) {
                                        if ($pieza->estado != 0) {
                                            $user = User::query()->where('matricula', $meta->id_usuario)->first();
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
                        $metas = Metas::query()->where('id_clase', $clase->id)->get();
                        if (count($metas) > 0) {
                            $pzasNoCero = 0;
                            foreach ($metas as $meta) {
                                $piezas = AcabadoBombilo_pza::query()->where('id_meta', $meta->id)->get();
                                if (count($piezas) > 0) {
                                    foreach ($piezas as $pieza) {
                                        if ($pieza->estado != 0) {
                                            $user = User::query()->where('matricula', $meta->id_usuario)->first();
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
                        $metas = Metas::query()->where('id_clase', $clase->id)->get();
                        if (count($metas) > 0) {
                            $pzasNoCero = 0;
                            foreach ($metas as $meta) {
                                $piezas = AcabadoMolde_pza::query()->where('id_meta', $meta->id)->get();
                                if (count($piezas) > 0) {
                                    foreach ($piezas as $pieza) {
                                        if ($pieza->estado != 0) {
                                            $user = User::query()->where('matricula', $meta->id_usuario)->first();
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
                        $metas = Metas::query()->where('id_clase', $clase->id)->get();
                        if (count($metas) > 0) {
                            $pzasNoCero = 0;
                            foreach ($metas as $meta) {
                                $piezas = BarrenoProfundidad_pza::query()->where('id_meta', $meta->id)->get();
                                if (count($piezas) > 0) {
                                    foreach ($piezas as $pieza) {
                                        if ($pieza->estado != 0) {
                                            $user = User::query()->where('matricula', $meta->id_usuario)->first();
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
                        $metas = Metas::query()->where('id_clase', $clase->id)->get();
                        if (count($metas) > 0) {
                            $pzasNoCero = 0;
                            foreach ($metas as $meta) {
                                $piezas = Cavidades_pza::query()->where('id_meta', $meta->id)->get();
                                if (count($piezas) > 0) {
                                    foreach ($piezas as $pieza) {
                                        if ($pieza->estado != 0) {
                                            $user = User::query()->where('matricula', $meta->id_usuario)->first();
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
                        $metas = Metas::query()->where('id_clase', $clase->id)->get();
                        if (count($metas) > 0) {
                            $pzasNoCero = 0;
                            foreach ($metas as $meta) {
                                $piezas = Copiado_pza::query()->where('id_meta', $meta->id)->get();
                                if (count($piezas) > 0) {
                                    foreach ($piezas as $pieza) {
                                        if ($pieza->estado != 0) {
                                            $user = User::query()->where('matricula', $meta->id_usuario)->first();
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
                        $metas = Metas::query()->where('id_clase', $clase->id)->get();
                        if (count($metas) > 0) {
                            $pzasNoCero = 0;
                            foreach ($metas as $meta) {
                                $piezas = OffSet_pza::query()->where('id_meta', $meta->id)->get();
                                if (count($piezas) > 0) {
                                    foreach ($piezas as $pieza) {
                                        if ($pieza->estado != 0) {
                                            $user = User::query()->where('matricula', $meta->id_usuario)->first();
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
                        $metas = Metas::query()->where('id_clase', $clase->id)->get();
                        if (count($metas) > 0) {
                            $pzasNoCero = 0;
                            foreach ($metas as $meta) {
                                $piezas = Palomas_pza::query()->where('id_meta', $meta->id)->get();
                                if (count($piezas) > 0) {
                                    foreach ($piezas as $pieza) {
                                        if ($pieza->estado != 0) {
                                            $user = User::query()->where('matricula', $meta->id_usuario)->first();
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
                        $metas = Metas::query()->where('id_clase', $clase->id)->get();
                        if (count($metas) > 0) {
                            $pzasNoCero = 0;
                            foreach ($metas as $meta) {
                                $piezas = Rebajes_pza::query()->where('id_meta', $meta->id)->get();
                                if (count($piezas) > 0) {
                                    foreach ($piezas as $pieza) {
                                        if ($pieza->estado != 0) {
                                            $user = User::query()->where('matricula', $meta->id_usuario)->first();
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
                        $metas = Metas::query()->where('id_clase', $clase->id)->get();
                        if ($metas->isNotEmpty()) {
                            $metaIds = $metas->pluck('id');
                            $userIds = $metas->unique('id_usuario')->pluck('id_usuario');
                            $usersMap = User::query()->whereIn('matricula', $userIds, 'and', false)->get()->keyBy('matricula');

                            if ($clase->nombre == 'Candado Obturador') {
                                $allPieces = CandadoObturador_pza::query()->whereIn('id_meta', $metaIds, 'and', false)->where('estado', '!=', 0)->get();
                                $opIds = $allPieces->unique('id_proceso')->pluck('id_proceso');
                                $opsMap = CandadoObturador::query()->whereIn('id', $opIds, 'and', false)->get()->keyBy('id');
                            } else {
                                $allPieces = PySOpeSoldadura_pza::query()->whereIn('id_meta', $metaIds, 'and', false)->where('estado', '!=', 0)->get();
                                $opIds = $allPieces->unique('id_proceso')->pluck('id_proceso');
                                $opsMap = PySOpeSoldadura::query()->whereIn('id', $opIds, 'and', false)->get()->keyBy('id');
                            }

                            // Group pieces by meta to easily access them in the loop
                            $piecesByMeta = $allPieces->groupBy('id_meta');

                            $pzasNoCero = 0;
                            foreach ($metas as $meta) {
                                $piezas = $piecesByMeta->get($meta->id, collect());
                                $user = $usersMap->get($meta->id_usuario);

                                foreach ($piezas as $pieza) {
                                    $operacionRecord = $opsMap->get($pieza->id_proceso);
                                    $operacionName = $operacionRecord ? $operacionRecord->operacion : "---";
                                    $userName = $user ? "{$user->nombre} {$user->a_paterno} {$user->a_materno}" : "---";

                                    $estadoStr = ($pieza->estado == 1) ? "---" : "Terminada";
                                    $procesos[$indice][1][$pzasNoCero] = [$pieza->n_pieza, $userName, $estadoStr, $meta->maquina, $operacionName];
                                    $pzasNoCero++;
                                }
                            }

                            if ($pzasNoCero == 0) {
                                $procesos[$indice][1][0] = array("---", "---", "---", "---");
                            }
                        } else {
                            $procesos[$indice][1][0] = array("---", "---", "---", "---");
                        }
                        break;
                    case "embudoCM":
                        $metas = Metas::query()->where('id_clase', $clase->id)->get();
                        if (count($metas) > 0) {
                            $pzasNoCero = 0;
                            foreach ($metas as $meta) {
                                $piezas = EmbudoCM_pza::query()->where('id_meta', $meta->id)->get();
                                if (count($piezas) > 0) {
                                    foreach ($piezas as $pieza) {
                                        if ($pieza->estado != 0) {
                                            $user = User::query()->where('matricula', $meta->id_usuario)->first();
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
    /**
     * @param mixed $proceso
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
     * @param array $filters
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

            $query->join('metas', 'soldadura_pza.id_meta', '=', 'metas.id', 'inner', false);
            $needsMetaJoin = true;

            if (isset($filters['operator']) && $filters['operator'] !== 'Todos' && $filters['operator'] !== '') {
                $matricula = is_array($filters['operator']) ? $filters['operator']['matricula'] : $filters['operator'];
                $query->where('metas.id_usuario', $matricula);
            }

            if (isset($filters['machine']) && $filters['machine'] !== 'Todos' && $filters['machine'] !== '') {
                $query->where('metas.maquina', $filters['machine']);
            }
        }

        // 4. Filtro por OT o Clase (pre-resolviendo IDs de proceso para acelerar la consulta con índices SQL)
        $hasWorkOrderFilter = isset($filters['workOrder']) && $filters['workOrder'] !== 'Todos' && $filters['workOrder'] !== '';
        $hasClassFilter = isset($filters['class']) && $filters['class'] !== 'Todos' && $filters['class'] !== '';
        $activeOts = (isset($filters['activeOts']) && is_array($filters['activeOts'])) ? array_values(array_filter($filters['activeOts'])) : [];

        if ($hasWorkOrderFilter || $hasClassFilter || (!empty($activeOts) && !$hasWorkOrderFilter)) {
            $soldaduraQuery = Soldadura::query();

            if ($hasWorkOrderFilter) {
                $otId = strpos($filters['workOrder'], ' - ') !== false
                    ? explode(' - ', $filters['workOrder'])[0]
                    : $filters['workOrder'];
                $soldaduraQuery->where('id_proceso', 'LIKE', '%_' . trim($otId));
            } else if (!empty($activeOts)) {
                $soldaduraQuery->where(function ($q) use ($activeOts) {
                    foreach ($activeOts as $otId) {
                        $q->orWhere('id_proceso', 'LIKE', '%_' . trim($otId));
                    }
                });
            }

            if ($hasClassFilter) {
                $soldaduraQuery->where('id_proceso', 'LIKE', 'Soldadura_' . $filters['class'] . '_%');
            }

            $procesoIds = $soldaduraQuery->pluck('id')->toArray();

            if (empty($procesoIds)) {
                return collect([]);
            }

            $query->whereIn('soldadura_pza.id_proceso', $procesoIds);
        }

        if (isset($filters['tipo_soldadura']) && $filters['tipo_soldadura'] !== 'Todos' && $filters['tipo_soldadura'] !== '') {
            $tipoVal = $filters['tipo_soldadura'];
            $map = [
                'P1 - 3' => '1',
                'P2 - 2.5' => '2',
                'P3 - 2' => '3',
                'P4 - 1.5' => '4'
            ];
            if (isset($map[$tipoVal])) {
                $tipoVal = $map[$tipoVal];
            }
            $query->where('soldadura_pza.tipo_soldadura', $tipoVal);
        }

        if (isset($filters['material_soldadura']) && $filters['material_soldadura'] !== 'Todos' && $filters['material_soldadura'] !== '') {
            $query->where('soldadura_pza.material_soldadura', $filters['material_soldadura']);
        }

        // Seleccionar solo el ID máximo agrupado por pieza única
        $subQuery = $query->clone()
            ->selectRaw('MAX(soldadura_pza.id)')
            ->groupBy('soldadura_pza.id_proceso', 'soldadura_pza.n_juego');

        return Soldadura_pza::query()
            ->whereIn('id', $subQuery)
            ->get();
    }

    /**
     * Helper para aplicar filtros a Soldadura PTA
     * @param array $filters
     */
    private function applySoldaduraPTAFilters($filters)
    {
        $query = SoldaduraPTA_pza::query();

        // 1. Filtro por n_juego
        if (isset($filters['n_juego']) && $filters['n_juego'] !== 'Todos' && $filters['n_juego'] !== '') {
            $query->where('n_juego', $filters['n_juego']);
        }

        // 2. Filtro por fechas (dateFrom, dateTo)
        if (isset($filters['dateFrom']) && $filters['dateFrom'] !== '' && $filters['dateFrom'] !== 'Todos') {
            $query->where('soldaduraPTA_pza.created_at', '>=', $filters['dateFrom'] . " 00:00:00");
        }
        if (isset($filters['dateTo']) && $filters['dateTo'] !== '' && $filters['dateTo'] !== 'Todos') {
            $query->where('soldaduraPTA_pza.created_at', '<=', $filters['dateTo'] . " 23:59:59");
        }

        // 3. Filtro por Operador o Maquina (requiere join con metas)
        if (
            (isset($filters['operator']) && $filters['operator'] !== 'Todos' && $filters['operator'] !== '') ||
            (isset($filters['machine']) && $filters['machine'] !== 'Todos' && $filters['machine'] !== '')
        ) {
            $query->join('metas', 'soldaduraPTA_pza.id_meta', '=', 'metas.id', 'inner', false);

            if (isset($filters['operator']) && $filters['operator'] !== 'Todos' && $filters['operator'] !== '') {
                $matricula = is_array($filters['operator']) ? $filters['operator']['matricula'] : $filters['operator'];
                $query->where('metas.id_usuario', $matricula);
            }

            if (isset($filters['machine']) && $filters['machine'] !== 'Todos' && $filters['machine'] !== '') {
                $query->where('metas.maquina', $filters['machine']);
            }
        }

        // 4. Filtro por OT o Clase (pre-resolviendo IDs de proceso PTA)
        $hasWorkOrderFilterPTA = isset($filters['workOrder']) && $filters['workOrder'] !== 'Todos' && $filters['workOrder'] !== '';
        $hasClassFilterPTA = isset($filters['class']) && $filters['class'] !== 'Todos' && $filters['class'] !== '';
        $activeOtsPTA = (isset($filters['activeOts']) && is_array($filters['activeOts'])) ? array_values(array_filter($filters['activeOts'])) : [];

        if ($hasWorkOrderFilterPTA || $hasClassFilterPTA || (!empty($activeOtsPTA) && !$hasWorkOrderFilterPTA)) {
            $soldaduraPTAQuery = SoldaduraPTA::query();

            if ($hasWorkOrderFilterPTA) {
                $otId = strpos($filters['workOrder'], ' - ') !== false
                    ? explode(' - ', $filters['workOrder'])[0]
                    : $filters['workOrder'];
                $soldaduraPTAQuery->where('id_proceso', 'LIKE', '%_' . trim($otId));
            } else if (!empty($activeOtsPTA)) {
                $soldaduraPTAQuery->where(function ($q) use ($activeOtsPTA) {
                    foreach ($activeOtsPTA as $otId) {
                        $q->orWhere('id_proceso', 'LIKE', '%_' . trim($otId));
                    }
                });
            }

            if ($hasClassFilterPTA) {
                $soldaduraPTAQuery->where('id_proceso', 'LIKE', '%_' . $filters['class'] . '_%');
            }

            $procesoIdsPTA = $soldaduraPTAQuery->pluck('id')->toArray();

            if (empty($procesoIdsPTA)) {
                return collect([]);
            }

            $query->whereIn('soldaduraPTA_pza.id_proceso', $procesoIdsPTA);
        }

        if (isset($filters['resultado']) && $filters['resultado'] !== 'Todos' && $filters['resultado'] !== '') {
            $query->where('soldaduraPTA_pza.resultado', $filters['resultado']);
        }

        if (isset($filters['defecto']) && $filters['defecto'] !== 'Todos' && $filters['defecto'] !== '') {
            $query->where('soldaduraPTA_pza.defecto_pta', $filters['defecto']);
        }

        if (isset($filters['material_soldadura']) && $filters['material_soldadura'] !== 'Todos' && $filters['material_soldadura'] !== '') {
            $query->where('soldaduraPTA_pza.material_soldadura', $filters['material_soldadura']);
        }

        // Seleccionar solo el ID máximo agrupado por pieza única
        $subQuery = $query->clone()
            ->selectRaw('MAX(soldaduraPTA_pza.id)')
            ->groupBy('soldaduraPTA_pza.id_proceso', 'soldaduraPTA_pza.n_juego');

        return SoldaduraPTA_pza::query()
            ->whereIn('id', $subQuery)
            ->get();
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
                if (in_array($user->perfil, [1, 3])) { // Verificar usuarios administradores (Admin 1 y Master 3)
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

            // Determinar si es PTA
            $isPTA = false;
            if (isset($filters['process'])) {
                $processVal = strtolower($filters['process']);
                if (str_contains($processVal, 'pta')) {
                    $isPTA = true;
                }
            }

            // Obtener piezas filtradas
            if ($isPTA) {
                $soldaduraPieces = $this->applySoldaduraPTAFilters($filters);
            } else {
                $soldaduraPieces = $this->applySoldaduraFilters($filters);
            }

            $piecesData = [];

            // ── OPTIMIZACIÓN N+1 ──
            $procesosIds = $soldaduraPieces->pluck('id_proceso')->unique()->toArray();
            $metasIds = $soldaduraPieces->pluck('id_meta')->unique()->toArray();

            if ($isPTA) {
                $procesosMap = SoldaduraPTA::query()->whereIn('id', $procesosIds)->get()->keyBy('id');
            } else {
                $procesosMap = Soldadura::query()->whereIn('id', $procesosIds)->get()->keyBy('id');
            }

            $metasMap = Metas::query()->whereIn('id', $metasIds)->get()->keyBy('id');
            $userIds = $metasMap->pluck('id_usuario')->unique()->toArray();
            $usersMap = User::query()->whereIn('matricula', $userIds)->get()->keyBy('matricula');

            foreach ($soldaduraPieces as $piece) {
                $proceso = $procesosMap->get($piece->id_proceso);
                $meta = $metasMap->get($piece->id_meta);
                $operatorName = 'N/A';

                if ($meta) {
                    $operator = $usersMap->get($meta->id_usuario);
                    if ($operator) {
                        $operatorName = $operator->nombre . ' ' . $operator->a_paterno . ' ' . $operator->a_materno;
                    }
                }

                if ($proceso) {
                    $procStr = $proceso->id_proceso;
                    $lastUnderscore = strrpos($procStr, '_');
                    $workOrderId = ($lastUnderscore !== false) ? substr($procStr, $lastUnderscore + 1) : 'N/A';

                    if ($isPTA) {
                        $prefix = 'Soldadura_PTA_';
                        $start = (strpos($procStr, $prefix) === 0) ? strlen($prefix) : 0;
                        $className = ($lastUnderscore !== false && $lastUnderscore > $start)
                            ? substr($procStr, $start, $lastUnderscore - $start)
                            : 'N/A';
                    } else {
                        $prefix = 'Soldadura_';
                        $start = (strpos($procStr, $prefix) === 0) ? strlen($prefix) : 0;
                        $className = ($lastUnderscore !== false && $lastUnderscore > $start)
                            ? substr($procStr, $start, $lastUnderscore - $start)
                            : 'N/A';
                    }

                    $piecesData[] = [
                        'n_juego' => $piece->n_juego ?? 'N/A',
                        'operador' => $operatorName,
                        'operator_matricula' => $meta ? $meta->id_usuario : null,
                        'clase' => $className,
                        'orden_trabajo' => $workOrderId,
                        'precalentamiento' => $piece->precalentamiento ?? 'N/A',
                        'resultado' => $piece->resultado ?? 'N/A',
                        'defecto' => $piece->defecto_pta ?? 'N/A',
                        'peso_pieza' => $piece->pesoxpieza ?? 'N/A',
                        'tiempo_aplicacion' => $piece->tiempo_aplicacion ?? 'N/A',
                        'tipo_soldadura' => [
                            '1' => 'P1 - 3',
                            '2' => 'P2 - 2.5',
                            '3' => 'P3 - 2',
                            '4' => 'P4 - 1.5'
                        ][strval($piece->tipo_soldadura)] ?? 'N/A',
                        'material_soldadura' => $piece->material_soldadura ?? 'N/A',
                        'lote' => $piece->lote ?? 'N/A',
                        'fecha' => $piece->created_at ? $piece->created_at->format('d-m-Y') : 'N/A',
                        'hora' => $piece->created_at ? $piece->created_at->format('H:i') : 'N/A',
                        'observaciones' => $piece->observaciones ?? '',
                    ];
                }
            }

            // Deduplicar piezas por clase + orden_trabajo + n_juego
            $uniquePieces = [];
            foreach ($piecesData as $p) {
                $key = $p['clase'] . '_' . $p['orden_trabajo'] . '_' . $p['n_juego'];
                if (!isset($uniquePieces[$key])) {
                    $uniquePieces[$key] = $p;
                } else {
                    $existingTime = strtotime($uniquePieces[$key]['fecha'] . ' ' . $uniquePieces[$key]['hora']);
                    $newTime = strtotime($p['fecha'] . ' ' . $p['hora']);
                    if ($newTime > $existingTime) {
                        $uniquePieces[$key] = $p;
                    }
                }
            }
            $piecesData = array_values($uniquePieces);

            // Filtrar por activePieces para que coincida exactamente con las piezas visibles en el reporte principal
            $activePieces = $filters['activePieces'] ?? [];
            if (is_string($activePieces)) {
                $activePieces = json_decode($activePieces, true) ?? [];
            }
            if (is_array($activePieces) && !empty($activePieces)) {
                $activeKeys = [];
                foreach ($activePieces as $ap) {
                    $key = trim($ap['class'] ?? '') . '_' . trim($ap['workOrder'] ?? '') . '_' . trim($ap['noAssembly'] ?? '');
                    $activeKeys[$key] = true;
                }

                $filteredPieces = [];
                foreach ($piecesData as $p) {
                    $key = trim($p['clase'] ?? '') . '_' . trim($p['orden_trabajo'] ?? '') . '_' . trim($p['n_juego'] ?? '');
                    if (isset($activeKeys[$key])) {
                        $filteredPieces[] = $p;
                    }
                }
                $piecesData = $filteredPieces;
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

            $isPTA = isset($filters['process']) && stripos($filters['process'], 'pta') !== false;

            if ($isPTA) {
                $soldaduraPieces = $this->applySoldaduraPTAFilters($filters);
            } else {
                $soldaduraPieces = $this->applySoldaduraFilters($filters);
            }
            $piecesData = [];

            // ── OPTIMIZACIÓN N+1 PARA PDF ──
            $procesosIds = $soldaduraPieces->pluck('id_proceso')->unique()->toArray();
            $metasIds = $soldaduraPieces->pluck('id_meta')->unique()->toArray();

            if ($isPTA) {
                $procesosMap = SoldaduraPTA::query()->whereIn('id', $procesosIds)->get()->keyBy('id');
            } else {
                $procesosMap = Soldadura::query()->whereIn('id', $procesosIds)->get()->keyBy('id');
            }

            $metasMap = Metas::query()->whereIn('id', $metasIds)->get()->keyBy('id');
            $userIds = $metasMap->pluck('id_usuario')->unique()->toArray();
            $usersMap = User::query()->whereIn('matricula', $userIds)->get()->keyBy('matricula');
            $claseIds = $metasMap->pluck('id_clase')->unique()->toArray();
            $clasesMap = Clase::query()->whereIn('id', $claseIds)->get()->keyBy('id');

            foreach ($soldaduraPieces as $piece) {
                $proceso = $procesosMap->get($piece->id_proceso);
                $meta = $metasMap->get($piece->id_meta);
                $clase = $meta ? $clasesMap->get($meta->id_clase) : null;
                $operatorName = 'N/A';

                if ($meta) {
                    $operator = $usersMap->get($meta->id_usuario);
                    if ($operator) {
                        $operatorName = $operator->nombre . ' ' . $operator->a_paterno . ' ' . $operator->a_materno;
                    }
                }

                if ($proceso) {
                    $procStr = $proceso->id_proceso;
                    $lastUnderscore = strrpos($procStr, '_');
                    $workOrderId = ($lastUnderscore !== false) ? substr($procStr, $lastUnderscore + 1) : 'N/A';

                    if ($isPTA) {
                        $prefix = 'Soldadura_PTA_';
                        $start = (strpos($procStr, $prefix) === 0) ? strlen($prefix) : 0;
                        $className = ($lastUnderscore !== false && $lastUnderscore > $start)
                            ? substr($procStr, $start, $lastUnderscore - $start)
                            : 'N/A';
                    } else {
                        $prefix = 'Soldadura_';
                        $start = (strpos($procStr, $prefix) === 0) ? strlen($prefix) : 0;
                        $className = ($lastUnderscore !== false && $lastUnderscore > $start)
                            ? substr($procStr, $start, $lastUnderscore - $start)
                            : 'N/A';
                    }

                    if ($isPTA) {
                        $piecesData[] = [
                            'n_juego' => $piece->n_juego ?? 'N/A',
                            'operador' => $operatorName,
                            'clase' => $className,
                            'orden_trabajo' => $workOrderId,
                            'fecha' => $piece->created_at ? $piece->created_at->format('d-m-Y') : 'N/A',
                            'hora' => $piece->created_at ? $piece->created_at->format('H:i') : 'N/A',
                            'precalentamiento' => $piece->precalentamiento ?? 'N/A',
                            'material_soldadura' => $piece->material_soldadura ?? 'N/A',
                            'resultado' => $piece->resultado ?? 'N/A',
                            'defecto' => $piece->defecto_pta ?? 'N/A',
                            'observaciones' => $piece->observaciones ?? '',
                        ];
                    } else {
                        $tipoRaw = $piece->tipo_soldadura;
                        if (empty($tipoRaw) || in_array(strval($tipoRaw), ['0', '.0', '00', '000', '0000'])) {
                            if ($clase) {
                                $tipoRaw = $clase->tipo_soldadura;
                            }
                        }

                        $piecesData[] = [
                            'n_juego' => $piece->n_juego ?? 'N/A',
                            'operador' => $operatorName,
                            'clase' => $className,
                            'orden_trabajo' => $workOrderId,
                            'peso_pieza' => $piece->pesoxpieza ?? 'N/A',
                            'tipo_soldadura' => [
                                '1' => 'P1 - 3',
                                '2' => 'P2 - 2.5',
                                '3' => 'P3 - 2',
                                '4' => 'P4 - 1.5'
                            ][strval($tipoRaw)] ?? $tipoRaw ?? 'N/A',
                            'material_soldadura' => $piece->material_soldadura ?? 'N/A',
                            'lote' => $piece->lote ?? 'N/A',
                            'fecha' => $piece->created_at ? $piece->created_at->format('d-m-Y') : 'N/A',
                            'hora' => $piece->created_at ? $piece->created_at->format('H:i') : 'N/A',
                            'observaciones' => $piece->observaciones ?? '',
                        ];
                    }
                }
            }

            // Deduplicar piezas por clase + orden_trabajo + n_juego para el PDF
            $uniquePieces = [];
            foreach ($piecesData as $p) {
                $key = $p['clase'] . '_' . $p['orden_trabajo'] . '_' . $p['n_juego'];
                if (!isset($uniquePieces[$key])) {
                    $uniquePieces[$key] = $p;
                } else {
                    $existingTime = strtotime($uniquePieces[$key]['fecha'] . ' ' . $uniquePieces[$key]['hora']);
                    $newTime = strtotime($p['fecha'] . ' ' . $p['hora']);
                    if ($newTime > $existingTime) {
                        $uniquePieces[$key] = $p;
                    }
                }
            }
            $piecesData = array_values($uniquePieces);

            // Filtrar por activePieces para que coincida exactamente con las piezas visibles en el reporte principal
            $activePieces = $filters['activePieces'] ?? [];
            if (is_string($activePieces)) {
                $activePieces = json_decode($activePieces, true) ?? [];
            }
            if (is_array($activePieces) && !empty($activePieces)) {
                $activeKeys = [];
                foreach ($activePieces as $ap) {
                    $key = trim($ap['class'] ?? '') . '_' . trim($ap['workOrder'] ?? '') . '_' . trim($ap['noAssembly'] ?? '');
                    $activeKeys[$key] = true;
                }

                $filteredPieces = [];
                foreach ($piecesData as $p) {
                    $key = trim($p['clase'] ?? '') . '_' . trim($p['orden_trabajo'] ?? '') . '_' . trim($p['n_juego'] ?? '');
                    if (isset($activeKeys[$key])) {
                        $filteredPieces[] = $p;
                    }
                }
                $piecesData = $filteredPieces;
            }

            // Construir nombre de archivo dinámico basado en filtros
            $filenameParts = $isPTA ? ['Reporte_PTA'] : ['Reporte_Soldadura'];

            $ordenTrabajo = 'Todas';

            // Agregar OT si está filtrado
            if (isset($filters['workOrder']) && $filters['workOrder'] !== 'Todos' && $filters['workOrder'] !== '') {
                $otValue = $filters['workOrder'];
                $ordenTrabajo = $otValue; // Para mostrar en la vista

                // Si tiene formato "123 - Descripción", extraer solo el número para el nombre del archivo
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
                $operator = User::query()->where('matricula', $operatorMatricula)->first();
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

            if ($isPTA) {
                $pdf = Pdf::loadView('pieces_views.piecesReport.soldaduraPTAExtraInfoPdf', compact('piecesData', 'ordenTrabajo'))
                    ->setPaper('a4', 'landscape');
            } else {
                $pdf = Pdf::loadView('pieces_views.piecesReport.soldaduraExtraInfoPdf', compact('piecesData'))
                    ->setPaper('a4', 'landscape');
            }

            return $pdf->download($filename);

        } catch (\Exception $e) {
            return back()->with('error', 'Error al generar el PDF: ' . $e->getMessage());
        }
    }
}
