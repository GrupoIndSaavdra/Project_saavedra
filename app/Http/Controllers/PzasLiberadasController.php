<?php

namespace App\Http\Controllers;

use App\Models\AcabadoBombilo_pza;
use App\Models\AcabadoMolde_pza;
use App\Models\Asentado_pza;
use App\Models\BarrenoManiobra_pza;
use App\Models\BarrenoProfundidad_pza;
use App\Models\Cavidades_pza;
use App\Models\Clase;
use App\Models\Copiado_pza;
use App\Models\Desbaste_pza;
use App\Models\EmbudoCM_pza;
use App\Models\Metas;
use App\Models\OffSet_pza;
use App\Models\Palomas_pza;
use App\Models\Pieza;
use App\Models\PrimeraOpeSoldadura_pza;
use App\Models\PrimeraOperacionCabezaSoplo_pza;
use App\Models\PySOpeSoldadura_pza;
use App\Models\Pza_cepillado;
use App\Models\Rebajes_pza;
use App\Models\Rectificado_pza;
use App\Models\revCalificado_pza;
use App\Models\RevLaterales_pza;
use App\Models\CandadoObturador_pza;
use App\Models\SegundaOpeSoldadura_pza;
use App\Models\SegundaOperacionCabezaSoplo_pza;
use App\Models\Soldadura_pza;
use App\Models\SoldaduraPTA_pza;
use App\Models\SystemLog;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

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
        return $perfil == 4 ? 'layouts.appQuality' : 'layouts.appAdmin';
    }
    public function show()
    {
        $retainedFilters = session('retainedFilters');

        if ($retainedFilters) {
            // Restore from flashed session after a liberation action
            $emptyDatos = array_fill_keys(array_keys($retainedFilters), "Todos");
            $emptyDatos['action'] = null;

            $array = $this->controladorPzas->search($emptyDatos, "quality");
            $array[4] = $retainedFilters;
            return $this->showPieces($array);
        }

        return $this->getPiecesRequest(new Request());
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
        return $this->showPieces($this->controladorPzas->search($datosPiezas, "quality"));
    }
        /**
     * @param mixed $array
     */
    public function showPieces($array)
    {
        $toView = $array[0];
        $pieces = $array[1];
        $piecesData = $array[2];
        $infoPieces = $array[3];
        $selectedItems = $array[4];
        $filtersData = $array[5];
        $profile = "quality";

        [$pieces_Released, $info_Pieces] = $this->piecesToBeReleased();

        if ($toView) {
            return view('pieces_views.releasePieces.pzasLiberar', compact('pieces', 'piecesData', 'infoPieces', 'filtersData', 'selectedItems', 'pieces_Released', 'info_Pieces'));
        } else {
            // Configuración para generación de PDFs grandes
            @ini_set('max_execution_time', '300'); // 5 minutos
            @ini_set('memory_limit', '2048M'); // Aumentar memoria disponible
            @set_time_limit(300);

            $pdf = Pdf::loadView('pieces_views.piecesReport.pdf', compact('pieces', 'piecesData', 'infoPieces', 'filtersData', 'selectedItems', 'profile'));

            // Generar nombre del archivo
            $filename = $this->controladorPzas->generatePdfFilename($selectedItems, "Liberacion");
            return $pdf->download($filename);
        }
    }
        /**
     * @param Request $request
     */
    public function liberar_rechazar(Request $request) //Función para liberar o rechazar piezas
    {
        if ($request->liberar == 'true') {
            $this->liberarPiezas($this->getPiezasLiberar($request->pieza, $request->proceso, $request->buena), $request->proceso, $request->buena, $request->observationPiece);
        } else {
            $this->rechazarPieza($this->getPiezasLiberar($request->pieza, $request->proceso, $request->buena), $request->proceso, $request->observationPiece);
        }
        // Datos de las piezas
        if (isset($request->requestLiberation)) {
            // El resto de la función se encargará de procesar los filtros y devolver la vista actualizada
        }
        $extraRequest = explode("|", $request->extraRequest);

        // El workOrder usa "!" como reemplazo de "/"
        $workOrder = isset($extraRequest[0]) ? str_replace("!", "/", $extraRequest[0]) : "Todos";
        $operator = $extraRequest[2] ?? "Todos";

        if ($operator !== "Todos") {
            $operatorObj = User::query()->where('matricula', '=', $operator, 'and')->first();
            if ($operatorObj) {
                $operator = $operatorObj;
            }
        }

        $selectedItems = array(
            "workOrder" => $workOrder,
            "class"     => $extraRequest[1] ?? "Todos",
            "operator"  => $operator,
            "machine"   => $extraRequest[3] ?? "Todos",
            "process"   => $extraRequest[4] ?? "Todos",
            "error"     => $extraRequest[5] ?? "Todos",
            "dateFrom"  => $extraRequest[6] ?? "Todos",
            "dateTo"    => $extraRequest[7] ?? "Todos",
            "n_juego"   => $extraRequest[8] ?? "Todos",
            "action"    => null,
        );

        // Redirigir a la vista GET utilizando variables de sesión flash,
        // para que un simple F5 no reenvíe el formulario y limpie los filtros.
        return redirect()->route('showReleasePieces_view')->with('retainedFilters', $selectedItems);
    }

        /**
     * @param mixed $juego
     * @param mixed $proceso
     * @param mixed $buena
     * @return array
     */
    public function getPiezasLiberar($juego, $proceso, $buena)
    {
        $pieza = array();
        $juego = explode(",", $juego);
        switch ($proceso) {
            case "Cepillado":
                $pieza = array();
                foreach ($juego as $pza) {
                    $p = Pza_cepillado::query()->where('id_pza', $pza)->first();
                    array_push($pieza, $p);
                }
                $piezas = !empty($pieza) && $pieza[0] ? Pza_cepillado::query()->where('id_meta', $pieza[0]->id_meta)->get() : array();
                break;
            case "Desbaste Exterior":
                $pieza = array();
                foreach ($juego as $pza) {
                    $p = Desbaste_pza::query()->where('id_pza', $pza)->first();
                    array_push($pieza, $p);
                }
                $piezas = !empty($pieza) && $pieza[0] ? Desbaste_pza::query()->where('id_meta', $pieza[0]->id_meta)->get() : array();
                break;
            case "Revision Laterales":
                $pieza = array();
                foreach ($juego as $pza) {
                    $p = RevLaterales_pza::query()->where('id_pza', $pza)->first();
                    array_push($pieza, $p);
                }
                $piezas = !empty($pieza) && $pieza[0] ? RevLaterales_pza::query()->where('id_meta', $pieza[0]->id_meta)->get() : array();
                break;
            case "Primera Operacion":
                $pieza = array();
                foreach ($juego as $pza) {
                    $p = PrimeraOpeSoldadura_pza::query()->where('id_pza', $pza)->first();
                    array_push($pieza, $p);
                }
                $piezas = !empty($pieza) && $pieza[0] ? PrimeraOpeSoldadura_pza::query()->where('id_meta', $pieza[0]->id_meta)->get() : array();
                break;
            case "Barreno Maniobra":
                $pieza = array();
                foreach ($juego as $pza) {
                    $p = BarrenoManiobra_pza::query()->where('id_pza', $pza)->first();
                    array_push($pieza, $p);
                }
                $piezas = !empty($pieza) && $pieza[0] ? BarrenoManiobra_pza::query()->where('id_meta', $pieza[0]->id_meta)->get() : array();
                break;
            case "Segunda Operacion":
                $pieza = array();
                foreach ($juego as $pza) {
                    $p = SegundaOpeSoldadura_pza::query()->where('id_pza', $pza)->first();
                    array_push($pieza, $p);
                }
                $piezas = !empty($pieza) && $pieza[0] ? SegundaOpeSoldadura_pza::query()->where('id_meta', $pieza[0]->id_meta)->get() : array();
                break;
            case "Soldadura":
                $pieza = array();
                foreach ($juego as $pza) {
                    $p = Soldadura_pza::query()->where('id_pza', $pza)->first();
                    array_push($pieza, $p);
                }
                $piezas = !empty($pieza) && $pieza[0] ? Soldadura_pza::query()->where('id_meta', $pieza[0]->id_meta)->get() : array();
                break;
            case "Soldadura PTA":
                $pieza = array();
                foreach ($juego as $pza) {
                    preg_match('/^(\d+[a-zA-Z]*)(\d+)$/', $pza, $matches);
                    if (count($matches) == 3) {
                        $n_pieza = $matches[1];
                        $id_proceso = $matches[2];
                        $p = SoldaduraPTA_pza::query()->where('n_pieza', $n_pieza)->where('id_proceso', $id_proceso)->first();
                    } else {
                        $p = SoldaduraPTA_pza::query()->where('id_pza', $pza)->first();
                    }

                    if ($p) {
                        array_push($pieza, $p);
                    }
                }
                if (count($pieza) > 0) {
                    $piezas = SoldaduraPTA_pza::query()->where('id_meta', $pieza[0]->id_meta)->get();
                } else {
                    $piezas = array();
                }
                break;
            case "Rectificado":
                $pieza = array();
                foreach ($juego as $pza) {
                    $p = Rectificado_pza::query()->where('id_pza', $pza)->first();
                    array_push($pieza, $p);
                }
                $piezas = !empty($pieza) && $pieza[0] ? Rectificado_pza::query()->where('id_meta', $pieza[0]->id_meta)->get() : array();
                break;
            case "Asentado":
                $pieza = array();
                foreach ($juego as $pza) {
                    $p = Asentado_pza::query()->where('id_pza', $pza)->first();
                    array_push($pieza, $p);
                }
                $piezas = !empty($pieza) && $pieza[0] ? Asentado_pza::query()->where('id_meta', $pieza[0]->id_meta)->get() : array();
                break;
            case "Calificado":
                $pieza = array();
                foreach ($juego as $pza) {
                    $p = revCalificado_pza::query()->where('id_pza', $pza)->first();
                    array_push($pieza, $p);
                }
                $piezas = !empty($pieza) && $pieza[0] ? revCalificado_pza::query()->where('id_meta', $pieza[0]->id_meta)->get() : array();
                break;
            case "Acabado Bombillo":
                $pieza = array();
                foreach ($juego as $pza) {
                    $p = AcabadoBombilo_pza::query()->where('id_pza', $pza)->first();
                    array_push($pieza, $p);
                }
                $piezas = !empty($pieza) && $pieza[0] ? AcabadoBombilo_pza::query()->where('id_meta', $pieza[0]->id_meta)->get() : array();
                break;
            case "Acabado Molde":
                $pieza = array();
                foreach ($juego as $pza) {
                    $p = AcabadoMolde_pza::query()->where('id_pza', $pza)->first();
                    array_push($pieza, $p);
                }
                $piezas = !empty($pieza) && $pieza[0] ? AcabadoMolde_pza::query()->where('id_meta', $pieza[0]->id_meta)->get() : array();
                break;
            case "Cavidades":
                $pieza = array();
                foreach ($juego as $pza) {
                    $p = Cavidades_pza::query()->where('id_pza', $pza)->first();
                    array_push($pieza, $p);
                }
                $piezas = !empty($pieza) && $pieza[0] ? Cavidades_pza::query()->where('id_meta', $pieza[0]->id_meta)->get() : array();
                break;
            case "Barreno Profundidad":
                $pieza = array();
                foreach ($juego as $pza) {
                    $p = BarrenoProfundidad_pza::query()->where('id_pza', $pza)->first();
                    array_push($pieza, $p);
                }
                $piezas = !empty($pieza) && $pieza[0] ? BarrenoProfundidad_pza::query()->where('id_meta', $pieza[0]->id_meta)->get() : array();
                break;
            case "Copiado":
                $pieza = array();
                foreach ($juego as $pza) {
                    $p = Copiado_pza::query()->where('id_pza', $pza)->first();
                    array_push($pieza, $p);
                }
                $piezas = !empty($pieza) && $pieza[0] ? Copiado_pza::query()->where('id_meta', $pieza[0]->id_meta)->get() : array();
                break;
            case "Off Set":
                $pieza = array();
                foreach ($juego as $pza) {
                    $p = OffSet_pza::query()->where('id_pza', $pza)->first();
                    array_push($pieza, $p);
                }
                $piezas = !empty($pieza) && $pieza[0] ? OffSet_pza::query()->where('id_meta', $pieza[0]->id_meta)->get() : array();
                break;
            case "Palomas":
                $pieza = array();
                foreach ($juego as $pza) {
                    $p = Palomas_pza::query()->where('id_pza', $pza)->first();
                    array_push($pieza, $p);
                }
                $piezas = !empty($pieza) && $pieza[0] ? Palomas_pza::query()->where('id_meta', $pieza[0]->id_meta)->get() : array();
                break;
            case "Rebajes":
                $pieza = array();
                foreach ($juego as $pza) {
                    $p = Rebajes_pza::query()->where('id_pza', $pza)->first();
                    array_push($pieza, $p);
                }
                $piezas = !empty($pieza) && $pieza[0] ? Rebajes_pza::query()->where('id_meta', $pieza[0]->id_meta)->get() : array();
                break;
            case "Operacion Equipo_1 operacion":
            case "Operacion Equipo_2 operacion":
                $pieza = array();
                foreach ($juego as $pza) {
                    $p = PySOpeSoldadura_pza::query()->where('id_pza', $pza)->first();
                    if (!$p) {
                        $p = CandadoObturador_pza::query()->where('id_pza', $pza)->first();
                    }
                    if ($p) {
                        array_push($pieza, $p);
                    }
                }
                if (!empty($pieza) && $pieza[0]) {
                    $piezas = get_class($pieza[0])::query()->where('id_meta', $pieza[0]->id_meta)->get();
                } else {
                    $piezas = array();
                }
                break;
            case "Embudo CM":
                $pieza = array();
                foreach ($juego as $pza) {
                    $p = EmbudoCM_pza::query()->where('id_pza', $pza)->first();
                    array_push($pieza, $p);
                }
                $piezas = !empty($pieza) && $pieza[0] ? EmbudoCM_pza::query()->where('id_meta', $pieza[0]->id_meta)->get() : array();
                break;
            case "Primera Operacion Cabeza Soplo":
                $pieza = array();
                foreach ($juego as $pza) {
                    $p = PrimeraOperacionCabezaSoplo_pza::query()->where('id_pza', $pza)->first();
                    array_push($pieza, $p);
                }
                $piezas = !empty($pieza) && $pieza[0] ? PrimeraOperacionCabezaSoplo_pza::query()->where('id_meta', $pieza[0]->id_meta)->get() : array();
                break;
            case "Segunda Operacion Cabeza Soplo":
                $pieza = array();
                foreach ($juego as $pza) {
                    $p = SegundaOperacionCabezaSoplo_pza::query()->where('id_pza', $pza)->first();
                    array_push($pieza, $p);
                }
                $piezas = !empty($pieza) && $pieza[0] ? SegundaOperacionCabezaSoplo_pza::query()->where('id_meta', $pieza[0]->id_meta)->get() : array();
                break;
        }
        //Algoritmo para liberar 5 juegos despues de que se libere uno
        // if ($buena == 'false') {
        //     $piezas = $pieza;
        // }
        $piezas = $pieza;

        // Evitar múltiples registros por una misma pieza (ej. Soldadura PTA tiene 3 sub-records por pieza M/H)
        if (!empty($piezas)) {
            $piezas = collect($piezas)->unique(function ($item) {
                return $item->n_pieza ?? $item->n_juego;
            })->values()->all();
        }

        return $piezas;
    }
        /**
     * @param mixed $piezas
     * @param mixed $proceso
     * @param mixed $buena
     * @param mixed $observacion
     */
    public function liberarPiezas($piezas, $proceso, $buena, $observacion)
    {
        if (empty($piezas) || !$piezas[0]) return;
        $meta = Metas::query()->find($piezas[0]->id_meta);
        $claseLog = Clase::query()->find($meta->id_clase);
        $nowTime = date('H:i:s');
        $matricula = auth()->user()->matricula;

        // 1. Actualizar estado de liberación en la tabla Pieza para todas
        foreach ($piezas as $pza) {
            $n_pieza = $pza->n_pieza ?: $pza->n_juego;

            Pieza::query()->where('n_pieza', $n_pieza)
                ->where('id_clase', $meta->id_clase)
                ->where('proceso', $proceso)
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
                    $partnerNPieza = substr($n_pieza, 0, -1) . $partnerLetra;
                    Pieza::query()->where('n_pieza', $partnerNPieza)
                        ->where('id_clase', $meta->id_clase)
                        ->where('proceso', $proceso)
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
                if (!isset($grouped[$num])) $grouped[$num] = [];
                $grouped[$num][] = $letra;
            } else {
                $grouped[$raw] = ['UNIQUE'];
            }
        }

        foreach ($grouped as $key => $parts) {
            $displayName = "";
            if (count($parts) >= 2 && in_array('M', $parts) && in_array('H', $parts)) {
                $displayName = $key . "J"; // Consolidado a Juego
            } elseif ($parts[0] === 'UNIQUE') {
                $displayName = $key;
            } else {
                $displayName = $key . $parts[0]; // Solo una mitad
            }

            SystemLog::create([
                'user_matricula' => $matricula,
                'action' => 'Liberación por Calidad',
                'details' => "Juego $displayName LIBERADO en $proceso. Obs: $observacion",
                'ot' => $claseLog ? ($claseLog->id_ot . ' - ' . ($claseLog->tamanio ?? 'N/A')) : ($meta->id_ot ?? 'N/A'),
                'clase' => $claseLog->nombre ?? 'N/A',
                'id_ot' => $meta->id_ot,
                'id_clase' => $meta->id_clase,
                'proceso' => $proceso,
                'n_pieza' => $displayName,
                'maquina' => $meta->maquina,
                'h_inicio' => $nowTime,
                'h_termino' => $nowTime
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
     * @param mixed $piezas
     * @param mixed $proceso
     * @param mixed $observacion
     */
    public function rechazarPieza($piezas, $proceso, $observacion)
    {
        if (empty($piezas) || !$piezas[0]) return;
        $meta = Metas::query()->find($piezas[0]->id_meta);
        $claseLog = Clase::query()->find($meta->id_clase);
        $nowTime = date('H:i:s');
        $matricula = auth()->user()->matricula;

        // 1. Actualizar estado de rechazo
        foreach ($piezas as $pza) {
            $n_pieza = $pza->n_pieza ?: $pza->n_juego;
            Pieza::query()->where('n_pieza', $n_pieza)->where('id_clase', $meta->id_clase)->where('proceso', $proceso)->update([
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
                    $partnerNPieza = substr($n_pieza, 0, -1) . $partnerLetra;
                    Pieza::query()->where('n_pieza', $partnerNPieza)
                        ->where('id_clase', $meta->id_clase)
                        ->where('proceso', $proceso)
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
                if (!isset($grouped[$num])) $grouped[$num] = [];
                $grouped[$num][] = $letra;
            } else {
                $grouped[$raw] = ['UNIQUE'];
            }
        }

        foreach ($grouped as $key => $parts) {
            $displayName = "";
            if (count($parts) >= 2 && in_array('M', $parts) && in_array('H', $parts)) {
                $displayName = $key . "J";
            } elseif ($parts[0] === 'UNIQUE') {
                $displayName = $key;
            } else {
                $displayName = $key . $parts[0];
            }

            SystemLog::create([
                'user_matricula' => $matricula,
                'action' => 'Rechazo por Calidad',
                'details' => "Juego $displayName RECHAZADO en $proceso. Obs: $observacion",
                'ot' => $claseLog ? ($claseLog->id_ot . ' - ' . ($claseLog->tamanio ?? 'N/A')) : ($meta->id_ot ?? 'N/A'),
                'clase' => $claseLog->nombre ?? 'N/A',
                'id_ot' => $meta->id_ot,
                'id_clase' => $meta->id_clase,
                'proceso' => $proceso,
                'n_pieza' => $displayName,
                'maquina' => $meta->maquina,
                'h_inicio' => $nowTime,
                'h_termino' => $nowTime
            ]);
        }
    }
        /**
     * @param mixed $meta
     * @param mixed $proceso
     */
    public function juegosMalos($meta, $proceso)
    {
        $juegosMalos = array();
        $piezasMalas = Pieza::query()->where('id_operador', $meta->id_usuario)->where('id_clase', $meta->id_clase)->where('proceso', $proceso)->where('error', '!=', 'Ninguno')->where('liberacion', 0)->get();
        foreach ($piezasMalas as $pieza) {
            //Obtener el numero de juego
            if ($pieza->n_pieza) {
                $nPieza = $pieza->n_pieza;
            } else {
                $nPieza = $pieza->n_juego;
            }
            if (!in_array($this->controladorPzas->getPiezaNumber($nPieza), $juegosMalos)) {
                array_push($juegosMalos, $this->controladorPzas->getPiezaNumber($nPieza));
            }
        }
        return $juegosMalos;
    }
        /**
     * @param mixed $meta
     * @param mixed $piezasMeta
     * @param mixed $piezaLiberar
     * @param mixed $proceso
     */
    public function liberarPiezasMeta($meta, $piezasMeta, $piezaLiberar, $proceso)
    {

        foreach ($piezasMeta as $pieza) {
            $piezaLiberada = null;
            if ($pieza->n_pieza) {
                $numero = substr($pieza->n_pieza, 0, -1);
                $pLibH = Pieza::query()->where('id_ot', $meta->id_ot)->where('id_clase', $meta->id_clase)->where('id_operador', $meta->id_usuario)->where('proceso', $proceso)->where('n_pieza', $numero . "H")->where('error', 'Ninguno')->where('liberacion', 1)->first();
                $pLibM = Pieza::query()->where('id_ot', $meta->id_ot)->where('id_clase', $meta->id_clase)->where('id_operador', $meta->id_usuario)->where('proceso', $proceso)->where('n_pieza', $numero . "M")->where('error', 'Ninguno')->where('liberacion', 1)->first();
                if ($pLibH && $pLibM) {
                    $piezaLiberada = $pLibH; // Usar una de las piezas como referencia
                }
            } else {
                $piezaLiberada = Pieza::query()->where('id_ot', $meta->id_ot)->where('id_clase', $meta->id_clase)->where('id_operador', $meta->id_usuario)->where('proceso', $proceso)->where('n_pieza', $pieza->n_juego)->where('error', 'Ninguno')->where('liberacion', 1)->first();
            }
            if ($piezaLiberada) {
                if (substr($piezaLiberar, -1) == "H" || substr($piezaLiberar, -1) == "M") {
                    $numero = substr($piezaLiberar, 0, -1);
                    $piezaLiberarH = Pieza::query()->where('id_ot', $meta->id_ot)->where('id_clase', $meta->id_clase)->where('id_operador', $meta->id_usuario)->where('proceso', $proceso)->where('n_pieza', $numero . "H")->where('error', 'Ninguno')->first();
                    $piezaLiberarM = Pieza::query()->where('id_ot', $meta->id_ot)->where('id_clase', $meta->id_clase)->where('id_operador', $meta->id_usuario)->where('proceso', $proceso)->where('n_pieza', $numero . "M")->where('error', 'Ninguno')->first();

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
                    $piezaLiberar = Pieza::query()->where('id_ot', $meta->id_ot)->where('id_clase', $meta->id_clase)->where('id_operador', $meta->id_usuario)->where('proceso', $proceso)->where('n_pieza', $piezaLiberar)->where('error', 'Ninguno')->first();
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
        // ── OPTIMIZACIÓN: query directa filtrando por clases activas (~40 IDs) ──
        $activeClassIds = Clase::query()->where('finalizada', 0)->pluck('id')->toArray();

        // 1 query: solo las piezas que realmente necesitamos de clases activas
        $piezasRaw = Pieza::query()->where('error', '!=', 'Ninguno')
            ->where('liberacion', 0)
            ->when(!empty($activeClassIds), fn($q) => $q->whereIn('id_clase', $activeClassIds, 'and', false))
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

        $infoPieces = array();
        if (count($pieces) > 0) {
            $this->controladorPzas->saveInfoPzas($infoPieces, $pieces);
        }
        return [$pieces, $infoPieces];
    }
}
