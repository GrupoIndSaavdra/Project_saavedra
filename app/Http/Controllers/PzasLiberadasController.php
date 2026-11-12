<?php

namespace App\Http\Controllers;

use App\Models\CandadoObturador_pza;
use App\Models\Clase;
use App\Models\Metas;
use App\Models\Pieza;
use App\Models\PySOpeSoldadura_pza;
use App\Models\SoldaduraPTA_pza;
use App\Models\SystemLog;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PzasLiberadasController extends Controller
{
    /** @var \App\Http\Controllers\PzasGeneralesController */
    protected $controladorPzas;

    public function __construct()
    {
        $this->middleware('auth');
        $this->controladorPzas = new PzasGeneralesController();
    }

    public function obtenerLayout()
    {
        $perfil = auth()->user()->perfil;

        return in_array((string) $perfil, ['4', '8'], true) ? 'layouts.appQuality' : 'layouts.appAdmin';
    }

    public function show()
    {
        try {
            $retainedFilters = session('retainedFilters');

            if ($retainedFilters) {
                // Restore from flashed session after a liberation action
                $emptyDatos = array_fill_keys(array_keys($retainedFilters), 'Todos');
                $emptyDatos['action'] = null;

                $array = $this->controladorPzas->search($emptyDatos, 'quality');
                $array[4] = $retainedFilters;

                return $this->showPieces($array);
            }

            return $this->getPiecesRequest(new Request());
        } catch (\Throwable $e) {
            Log::error('Error en PzasLiberadasController@show: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);

            $pieces = [];
            $piecesData = [];
            $infoPieces = [];
            $filtersData = $this->controladorPzas->getFiltersInfo();
            $selectedItems = [];
            $pieces_Released = [];
            $info_Pieces = [];

            return view('pieces_views.releasePieces.pzasLiberar', compact('pieces', 'piecesData', 'infoPieces', 'filtersData', 'selectedItems', 'pieces_Released', 'info_Pieces'));
        }
    }

    public function getPiecesRequest(Request $request)
    {
        try {
            $datosPiezas = [
                'workOrder' => $request->input('workOrder'),
                'class' => $request->input('class'),
                'operator' => $request->input('operator'),
                'machine' => $request->input('machine'),
                'process' => $request->input('process'),
                'error' => $request->input('error'),
                'dateFrom' => $request->input('dateFrom'),
                'dateTo' => $request->input('dateTo'),
                'n_juego' => $request->input('n_juego'),
                'status' => $request->input('status'),
                'order' => $request->input('order', 'desc'),
                'action' => $request->input('action'),
            ];

            return $this->showPieces($this->controladorPzas->search($datosPiezas, 'quality'));
        } catch (\Throwable $e) {
            Log::error('Error en PzasLiberadasController@getPiecesRequest: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);

            $pieces = [];
            $piecesData = $request->all();
            $infoPieces = [];
            $filtersData = $this->controladorPzas->getFiltersInfo();
            $selectedItems = [];
            $pieces_Released = [];
            $info_Pieces = [];

            return view('pieces_views.releasePieces.pzasLiberar', compact('pieces', 'piecesData', 'infoPieces', 'filtersData', 'selectedItems', 'pieces_Released', 'info_Pieces'));
        }
    }

    /**
     * @param  mixed  $array
     */
    public function showPieces($array)
    {
        $toView = $array[0];
        $pieces = $array[1];
        $piecesData = $array[2];
        $infoPieces = $array[3];
        $selectedItems = $array[4];
        $filtersData = $array[5];
        $profile = 'quality';

        $pieces_Released = [];
        $info_Pieces = [];

        if ($toView) {
            return view('pieces_views.releasePieces.pzasLiberar', compact('pieces', 'piecesData', 'infoPieces', 'filtersData', 'selectedItems', 'pieces_Released', 'info_Pieces'));
        } else {
            // Configuración para generación de PDFs grandes
            @ini_set('max_execution_time', '300'); // 5 minutos
            @ini_set('memory_limit', '2048M'); // Aumentar memoria disponible
            @set_time_limit(300);

            $pdf = Pdf::loadView('pieces_views.piecesReport.pdf', compact('pieces', 'piecesData', 'infoPieces', 'filtersData', 'selectedItems', 'profile'));

            // Generar nombre del archivo
            $filename = $this->controladorPzas->generatePdfFilename($selectedItems, 'Liberacion');

            return $pdf->download($filename);
        }
    }

    public function liberar_rechazar(Request $request) //Función para liberar o rechazar piezas
    {
        if ($request->liberar == 'true') {
            $this->liberarPiezas($this->getPiezasLiberar($request->pieza, $request->proceso, $request->buena), $request->proceso, $request->buena, $request->observationPiece);
        } else {
            $this->rechazarPieza($this->getPiezasLiberar($request->pieza, $request->proceso, $request->buena), $request->proceso, $request->observationPiece);
        }

        if ($request->expectsJson() || $request->ajax()) {
            $actionVerb = ($request->liberar == 'true') ? 'liberada' : 'rechazada';

            return response()->json([
                'success' => true,
                'message' => "Pieza {$actionVerb} correctamente.",
                'action' => ($request->liberar == 'true') ? 'liberar' : 'rechazar',
                'pieza' => $request->pieza,
                'proceso' => $request->proceso,
            ]);
        }
        $extraRequest = explode('|', $request->extraRequest);

        // El workOrder usa "!" como reemplazo de "/"
        $workOrder = isset($extraRequest[0]) ? str_replace('!', '/', $extraRequest[0]) : 'Todos';
        $operator = $extraRequest[2] ?? 'Todos';

        if ($operator !== 'Todos') {
            $operatorObj = User::query()->where('matricula', '=', $operator, 'and')->first();
            if ($operatorObj) {
                $operator = $operatorObj;
            }
        }

        $selectedItems = [
            'workOrder' => $workOrder,
            'class' => $extraRequest[1] ?? 'Todos',
            'operator' => $operator,
            'machine' => $extraRequest[3] ?? 'Todos',
            'process' => $extraRequest[4] ?? 'Todos',
            'error' => $extraRequest[5] ?? 'Todos',
            'dateFrom' => $extraRequest[6] ?? 'Todos',
            'dateTo' => $extraRequest[7] ?? 'Todos',
            'n_juego' => $extraRequest[8] ?? 'Todos',
            'action' => null,
        ];

        // Redirigir a la vista GET utilizando variables de sesión flash,
        // para que un simple F5 no reenvíe el formulario y limpie los filtros.
        return redirect()->route('showReleasePieces_view')->with('retainedFilters', $selectedItems);
    }

    /**
     * @param  mixed  $juego
     * @param  mixed  $proceso
     * @param  mixed  $buena
     * @return array
     */
    public function getPiezasLiberar($juego, $proceso, $buena)
    {
        $pieza = [];
        $juego = explode(',', $juego);

        if ($proceso === 'Soldadura PTA') {
            foreach ($juego as $pza) {
                preg_match('/^(\\d+[a-zA-Z]*)(\\d+)$/', $pza, $matches);
                if (count($matches) == 3) {
                    $n_pieza = $matches[1];
                    $id_proceso = $matches[2];
                    $p = SoldaduraPTA_pza::query()->where('n_pieza', '=', $n_pieza, 'and')->where('id_proceso', '=', $id_proceso, 'and')->first();
                } else {
                    $p = SoldaduraPTA_pza::query()->where('id_pza', '=', $pza, 'and')->first();
                }

                if ($p) {
                    $pieza[] = $p;
                }
            }
        } elseif (str_contains($proceso, 'Operacion Equipo')) {
            $pieza = PySOpeSoldadura_pza::query()->whereIn('id_pza', $juego, 'and', false)->get()->all();
            if (count($pieza) < count($juego)) {
                $foundIds = collect($pieza)->pluck('id_pza')->toArray();
                $missingIds = array_diff($juego, $foundIds);
                if (! empty($missingIds)) {
                    $candadoPzas = CandadoObturador_pza::query()->whereIn('id_pza', $missingIds, 'and', false)->get()->all();
                    $pieza = array_merge($pieza, $candadoPzas);
                }
            }
        } else {
            $ctrl = new ProcessProductionController();
            $modelClass = $ctrl->get_ModelProcessPieces($proceso);
            if (class_exists($modelClass)) {
                $pieza = $modelClass::query()->whereIn('id_pza', $juego, 'and', false)->get()->all();
            }
        }
        $piezas = $pieza;

        // Evitar múltiples registros por una misma pieza (ej. Soldadura PTA tiene 3 sub-records por pieza M/H)
        if (! empty($piezas)) {
            $piezas = collect($piezas)->unique(function ($item) {
                return $item->n_pieza ?? $item->n_juego;
            })->values()->all();
        }

        return $piezas;
    }

    /**
     * @param  mixed  $piezas
     * @param  mixed  $proceso
     * @param  mixed  $buena
     * @param  mixed  $observacion
     */
    public function liberarPiezas($piezas, $proceso, $buena, $observacion)
    {
        if (empty($piezas) || ! $piezas[0]) {
            return;
        }
        $meta = Metas::query()->find($piezas[0]->id_meta);
        $claseLog = Clase::query()->find($meta->id_clase);
        $nowTime = date('H:i:s');
        $matricula = auth()->user()->matricula;

        // 1. Actualizar estado de liberación en la tabla Pieza para todas
        foreach ($piezas as $pza) {
            $n_pieza = $pza->n_pieza ?: $pza->n_juego;

            Pieza::query()->where('n_pieza', '=', $n_pieza, 'and')
                ->where('id_clase', '=', $meta->id_clase, 'and')
                ->where('proceso', '=', $proceso, 'and')
                ->update([
                    'liberacion' => 1,
                    'fecha_liberacion' => date('Y-m-d H:i:s'),
                    'user_liberacion' => $matricula,
                    'observacion_liberacion' => $observacion,
                ]);

            // Manejo especial para Soldadura PTA
            if ($proceso === 'Soldadura PTA') {
                $ultimaLetra = substr($n_pieza, -1);
                if ($ultimaLetra === 'H' || $ultimaLetra === 'M') {
                    $partnerLetra = $ultimaLetra === 'H' ? 'M' : 'H';
                    $partnerNPieza = substr($n_pieza, 0, -1).$partnerLetra;
                    Pieza::query()->where('n_pieza', '=', $partnerNPieza, 'and')
                        ->where('id_clase', '=', $meta->id_clase, 'and')
                        ->where('proceso', '=', $proceso, 'and')
                        ->update([
                            'liberacion' => 1,
                            'fecha_liberacion' => date('Y-m-d H:i:s'),
                            'user_liberacion' => $matricula,
                            'observacion_liberacion' => $observacion,
                        ]);
                }
            }
        }

        // 2. Lógica de Consolidación para Logs
        $grouped = [];
        foreach ($piezas as $pza) {
            $raw = $pza->n_pieza ?: $pza->n_juego;
            if (preg_match('/^(\d+)([HM])$/', $raw, $m)) {
                $num = $m[1];
                $letra = $m[2];
                if (! isset($grouped[$num])) {
                    $grouped[$num] = [];
                }
                $grouped[$num][] = $letra;
            } else {
                $grouped[$raw] = ['UNIQUE'];
            }
        }

        foreach ($grouped as $key => $parts) {
            $displayName = '';
            if (count($parts) >= 2 && in_array('M', $parts) && in_array('H', $parts)) {
                $displayName = $key.'J'; // Consolidado a Juego
            } elseif ($parts[0] === 'UNIQUE') {
                $displayName = $key;
            } else {
                $displayName = $key.$parts[0]; // Solo una mitad
            }

            SystemLog::create([
                'user_matricula' => $matricula,
                'action' => 'Liberación por Calidad',
                'details' => "Juego $displayName LIBERADO en $proceso. Obs: $observacion",
                'ot' => $claseLog ? ($claseLog->id_ot.' - '.($claseLog->tamanio ?? 'N/A')) : ($meta->id_ot ?? 'N/A'),
                'clase' => $claseLog->nombre ?? 'N/A',
                'id_ot' => $meta->id_ot,
                'id_clase' => $meta->id_clase,
                'proceso' => $proceso,
                'n_pieza' => $displayName,
                'maquina' => $meta->maquina,
                'h_inicio' => $nowTime,
                'h_termino' => $nowTime,
            ]);
        }

        //Algoritmo para liberar 5 juegos despues de que se libere uno
        // //Identificar los juegos malos
        // $meta = Metas::query()->find($piezas[0]->id_meta);
        // $juegosMalos = $this->juegosMalos($meta, $proceso);
        // if ($buena == 'true') {
        //     foreach ($piezas as $pza) {
        //         //Actualizar el estado de liberacion de la pieza
        //         if ($pza->n_pieza) {
        //             $numero = substr($pza->n_pieza, 0, -1);
        //             $piezaH = Pieza::query()->where('n_pieza', $numero . "H")->where('id_clase', $meta->id_clase)->where('proceso', $proceso)->where('error', 'Ninguno')->where('liberacion', 0)->first();
        //             $piezaM = Pieza::query()->where('n_pieza', $numero . "M")->where('id_clase', $meta->id_clase)->where('proceso', $proceso)->where('error', 'Ninguno')->where('liberacion', 0)->first();

        //             if ($piezaH && $piezaM) {
        //                 if (!in_array($numero, $juegosMalos)) {
        //                     $piezaH->liberacion = 1;
        //                     $piezaH->fecha_liberacion = date('Y-m-d H:i:s');
        //                     $piezaH->user_liberacion = auth()->user()->matricula;
        //                     $piezaH->save();

        //                     $piezaM->liberacion = 1;
        //                     $piezaM->fecha_liberacion = date('Y-m-d H:i:s');
        //                     $piezaM->user_liberacion = auth()->user()->matricula;
        //                     $piezaM->save();
        //                 }
        //             }
        //         } else {
        //             $pieza = Pieza::query()->where('n_pieza', $pza->n_juego)->where('id_clase', $meta->id_clase)->where('proceso', $proceso)->where('error', 'Ninguno')->where('liberacion', 0)->first();
        //             if ($pieza) {
        //                 if (!in_array($this->controladorPzas->getPiezaNumber($pieza->n_pieza), $juegosMalos)) {
        //                     $pieza->liberacion = 1;
        //                     $pieza->fecha_liberacion = date('Y-m-d H:i:s');
        //                     $pieza->user_liberacion = auth()->user()->matricula;
        //                     $pieza->save();
        //                 }
        //             }
        //         }
        //     }
        // } else {
        //     $meta = Metas::query()->find($piezas[0]->id_meta);
        //     //Actualizar el estado de liberacion de la pieza
        //     foreach ($piezas as $pza) {
        //         if ($pza->n_pieza) {
        //             $n_pieza = $pza->n_pieza;
        //         } else {
        //             $n_pieza = $pza->n_juego;
        //         }
        //         Pieza::query()->where('n_pieza', $n_pieza)->where('id_clase', $meta->id_clase)->where('proceso', $proceso)->update([
        //             'liberacion' => 1,
        //             'fecha_liberacion' => date('Y-m-d H:i:s'),
        //             'user_liberacion' => auth()->user()->matricula,
        //         ]);
        //     }
        // }
    }

    /**
     * @param  mixed  $piezas
     * @param  mixed  $proceso
     * @param  mixed  $observacion
     */
    public function rechazarPieza($piezas, $proceso, $observacion)
    {
        if (empty($piezas) || ! $piezas[0]) {
            return;
        }
        $meta = Metas::query()->find($piezas[0]->id_meta);
        $claseLog = Clase::query()->find($meta->id_clase);
        $nowTime = date('H:i:s');
        $matricula = auth()->user()->matricula;

        // 1. Actualizar estado de rechazo
        foreach ($piezas as $pza) {
            $n_pieza = $pza->n_pieza ?: $pza->n_juego;
            Pieza::query()->where('n_pieza', '=', $n_pieza, 'and')->where('id_clase', '=', $meta->id_clase, 'and')->where('proceso', '=', $proceso, 'and')->update([
                'liberacion' => 2,
                'fecha_liberacion' => date('Y-m-d H:i:s'),
                'user_liberacion' => $matricula,
                'observacion_liberacion' => $observacion,
            ]);

            // Manejo Soldadura PTA
            if ($proceso === 'Soldadura PTA') {
                $ultimaLetra = substr($n_pieza, -1);
                if ($ultimaLetra === 'H' || $ultimaLetra === 'M') {
                    $partnerLetra = $ultimaLetra === 'H' ? 'M' : 'H';
                    $partnerNPieza = substr($n_pieza, 0, -1).$partnerLetra;
                    Pieza::query()->where('n_pieza', '=', $partnerNPieza, 'and')
                        ->where('id_clase', '=', $meta->id_clase, 'and')
                        ->where('proceso', '=', $proceso, 'and')
                        ->update([
                            'liberacion' => 2,
                            'fecha_liberacion' => date('Y-m-d H:i:s'),
                            'user_liberacion' => $matricula,
                            'observacion_liberacion' => $observacion,
                        ]);
                }
            }
        }

        // 2. Lógica de Consolidación para Logs
        $grouped = [];
        foreach ($piezas as $pza) {
            $raw = $pza->n_pieza ?: $pza->n_juego;
            if (preg_match('/^(\d+)([HM])$/', $raw, $m)) {
                $num = $m[1];
                $letra = $m[2];
                if (! isset($grouped[$num])) {
                    $grouped[$num] = [];
                }
                $grouped[$num][] = $letra;
            } else {
                $grouped[$raw] = ['UNIQUE'];
            }
        }

        foreach ($grouped as $key => $parts) {
            $displayName = '';
            if (count($parts) >= 2 && in_array('M', $parts) && in_array('H', $parts)) {
                $displayName = $key.'J';
            } elseif ($parts[0] === 'UNIQUE') {
                $displayName = $key;
            } else {
                $displayName = $key.$parts[0];
            }

            SystemLog::create([
                'user_matricula' => $matricula,
                'action' => 'Rechazo por Calidad',
                'details' => "Juego $displayName RECHAZADO en $proceso. Obs: $observacion",
                'ot' => $claseLog ? ($claseLog->id_ot.' - '.($claseLog->tamanio ?? 'N/A')) : ($meta->id_ot ?? 'N/A'),
                'clase' => $claseLog->nombre ?? 'N/A',
                'id_ot' => $meta->id_ot,
                'id_clase' => $meta->id_clase,
                'proceso' => $proceso,
                'n_pieza' => $displayName,
                'maquina' => $meta->maquina,
                'h_inicio' => $nowTime,
                'h_termino' => $nowTime,
            ]);
        }
    }

    /**
     * @param  mixed  $meta
     * @param  mixed  $proceso
     */
    public function juegosMalos($meta, $proceso)
    {
        $juegosMalos = [];
        $piezasMalas = Pieza::query()
            ->select(['n_pieza', 'n_juego'])
            ->where('id_operador', '=', $meta->id_usuario, 'and')
            ->where('id_clase', '=', $meta->id_clase, 'and')
            ->where('proceso', '=', $proceso, 'and')
            ->where('error', '!=', 'Ninguno', 'and')
            ->where('liberacion', '=', 0, 'and')
            ->get();
        foreach ($piezasMalas as $pieza) {
            //Obtener el numero de juego
            if ($pieza->n_pieza) {
                $nPieza = $pieza->n_pieza;
            } else {
                $nPieza = $pieza->n_juego;
            }
            if (! in_array($this->controladorPzas->getPiezaNumber($nPieza), $juegosMalos)) {
                array_push($juegosMalos, $this->controladorPzas->getPiezaNumber($nPieza));
            }
        }

        return $juegosMalos;
    }

    /**
     * @param  mixed  $meta
     * @param  mixed  $piezasMeta
     * @param  mixed  $piezaLiberar
     * @param  mixed  $proceso
     */
    public function liberarPiezasMeta($meta, $piezasMeta, $piezaLiberar, $proceso)
    {

        foreach ($piezasMeta as $pieza) {
            $piezaLiberada = null;
            if ($pieza->n_pieza) {
                $numero = substr($pieza->n_pieza, 0, -1);
                $pLibH = Pieza::query()->where('id_ot', '=', $meta->id_ot, 'and')->where('id_clase', '=', $meta->id_clase, 'and')->where('id_operador', '=', $meta->id_usuario, 'and')->where('proceso', '=', $proceso, 'and')->where('n_pieza', '=', $numero.'H', 'and')->where('error', '=', 'Ninguno', 'and')->where('liberacion', '=', 1, 'and')->first();
                $pLibM = Pieza::query()->where('id_ot', '=', $meta->id_ot, 'and')->where('id_clase', '=', $meta->id_clase, 'and')->where('id_operador', '=', $meta->id_usuario, 'and')->where('proceso', '=', $proceso, 'and')->where('n_pieza', '=', $numero.'M', 'and')->where('error', '=', 'Ninguno', 'and')->where('liberacion', '=', 1, 'and')->first();
                if ($pLibH && $pLibM) {
                    $piezaLiberada = $pLibH; // Usar una de las piezas como referencia
                }
            } else {
                $piezaLiberada = Pieza::query()->where('id_ot', '=', $meta->id_ot, 'and')->where('id_clase', '=', $meta->id_clase, 'and')->where('id_operador', '=', $meta->id_usuario, 'and')->where('proceso', '=', $proceso, 'and')->where('n_pieza', '=', $pieza->n_juego, 'and')->where('error', '=', 'Ninguno', 'and')->where('liberacion', '=', 1, 'and')->first();
            }
            if ($piezaLiberada) {
                if (substr($piezaLiberar, -1) == 'H' || substr($piezaLiberar, -1) == 'M') {
                    $numero = substr($piezaLiberar, 0, -1);
                    $piezaLiberarH = Pieza::query()->where('id_ot', '=', $meta->id_ot, 'and')->where('id_clase', '=', $meta->id_clase, 'and')->where('id_operador', '=', $meta->id_usuario, 'and')->where('proceso', '=', $proceso, 'and')->where('n_pieza', '=', $numero.'H', 'and')->where('error', '=', 'Ninguno', 'and')->first();
                    $piezaLiberarM = Pieza::query()->where('id_ot', '=', $meta->id_ot, 'and')->where('id_clase', '=', $meta->id_clase, 'and')->where('id_operador', '=', $meta->id_usuario, 'and')->where('proceso', '=', $proceso, 'and')->where('n_pieza', '=', $numero.'M', 'and')->where('error', '=', 'Ninguno', 'and')->first();

                    if ($piezaLiberarH && $piezaLiberarM) {
                        $piezaLiberarH->liberacion = 1;
                        $piezaLiberarH->fecha_liberacion = date('Y-m-d H:i:s');
                        $piezaLiberarH->user_liberacion = $piezaLiberada->user_liberacion;
                        $piezaLiberarH->save();

                        $piezaLiberarM->liberacion = 1;
                        $piezaLiberarM->fecha_liberacion = date('Y-m-d H:i:s');
                        $piezaLiberarM->user_liberacion = $piezaLiberada->user_liberacion;
                        $piezaLiberarM->save();

                        return;
                    }
                } else {
                    $piezaLiberar = Pieza::query()->where('id_ot', '=', $meta->id_ot, 'and')->where('id_clase', '=', $meta->id_clase, 'and')->where('id_operador', '=', $meta->id_usuario, 'and')->where('proceso', '=', $proceso, 'and')->where('n_pieza', '=', $piezaLiberar, 'and')->where('error', '=', 'Ninguno', 'and')->first();
                    $piezaLiberar->liberacion = 1;
                    $piezaLiberar->fecha_liberacion = date('Y-m-d H:i:s');
                    $piezaLiberar->user_liberacion = $piezaLiberada->user_liberacion;
                    $piezaLiberar->save();

                    return;
                }
            }
        }
    }

    /**
     * @return array
     */
    public function piecesToBeReleased()
    {
        // ── OPTIMIZACIÓN: query directa sin pasar por saveInArray completo ──
        // Solo necesitamos piezas con error pendientes de liberar, de clases activas
        $activeClassIds = Clase::query()->where('finalizada', '=', 0, 'and')->pluck('id')->toArray();

        // 1 query: solo las piezas que realmente necesitamos, con columnas mínimas
        // CRÍTICO: sin ->select() se cargan TODOS los atributos de 76k+ piezas → memory exhausted
        $piezasRaw = Pieza::query()
            ->select(['id', 'id_ot', 'id_clase', 'id_operador', 'proceso', 'error', 'liberacion',
                'n_pieza', 'maquina', 'created_at', 'fecha_liberacion', 'user_liberacion', 'observacion_liberacion'])
            ->where('error', '!=', 'Ninguno', 'and')
            ->where('liberacion', '=', 0, 'and')
            ->whereIn('id_clase', $activeClassIds, 'and', false)
            ->get();

        if ($piezasRaw->isEmpty()) {
            return [[], []];
        }

        // Construir array procesado usando saveInArray (observaciones optimizadas con eager loading)
        $pieces = $this->controladorPzas->saveInArray($piezasRaw, true);

        // Filtrar juegos incompletos
        foreach ($pieces as $key => $piece) {
            if (isset($piece[5]) && str_contains((string) $piece[5], 'Incompleto')) {
                unset($pieces[$key]);
            }
        }

        $infoPieces = [];
        if (count($pieces) > 0) {
            $this->controladorPzas->saveInfoPzas($infoPieces, $pieces);
        }

        return [$pieces, $infoPieces];
    }
}
