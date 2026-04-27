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
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class PzasLiberadasController extends Controller
{
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
        return $this->getPiecesRequest(new Request());
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
        return $this->showPieces($this->controladorPzas->search($datosPiezas, "quality"));
    }
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
            @ini_set('memory_limit', '512M'); // Aumentar memoria disponible
            @set_time_limit(300);

            $pdf = Pdf::loadView('pieces_views.piecesReport.pdf', compact('pieces', 'piecesData', 'infoPieces', 'filtersData', 'selectedItems', 'profile'));

            // Generar nombre del archivo
            $filename = $this->controladorPzas->generatePdfFilename($selectedItems, "Liberacion");
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
        // Datos de las piezas
        if (isset($request->requestLiberation)) {
            // El resto de la función se encargará de procesar los filtros y devolver la vista actualizada
        }
        $extraRequest = explode("|", $request->extraRequest);
        
        // El workOrder usa "!" como reemplazo de "/"
        $workOrder = isset($extraRequest[0]) ? str_replace("!", "/", $extraRequest[0]) : "Todos";

        $datosPiezas = array(
            "workOrder" => $workOrder,
            "class"     => $extraRequest[1] ?? "Todos",
            "operator"  => $extraRequest[2] ?? "Todos",
            "machine"   => $extraRequest[3] ?? "Todos",
            "process"   => $extraRequest[4] ?? "Todos",
            "error"     => $extraRequest[5] ?? "Todos",
            "dateFrom"  => $extraRequest[6] ?? "Todos",
            "dateTo"    => $extraRequest[7] ?? "Todos",
            "n_juego"   => $extraRequest[8] ?? "Todos",
            "action"    => null,
        );
        // Regresar a la vista con TODOS los registros para que el frontend pueda seguir filtrando globalmente
        return $this->show();
    }

    public function getPiezasLiberar($juego, $proceso, $buena)
    {
        $juego = explode(",", $juego);
        switch ($proceso) {
            case "Cepillado":
                $pieza = array();
                foreach ($juego as $pza) {
                    $p = Pza_cepillado::where('id_pza', $pza)->first();
                    array_push($pieza, $p);
                }
                $piezas = !empty($pieza) && $pieza[0] ? Pza_cepillado::where('id_meta', $pieza[0]->id_meta)->get() : array();
                break;
            case "Desbaste Exterior":
                $pieza = array();
                foreach ($juego as $pza) {
                    $p = Desbaste_pza::where('id_pza', $pza)->first();
                    array_push($pieza, $p);
                }
                $piezas = !empty($pieza) && $pieza[0] ? Desbaste_pza::where('id_meta', $pieza[0]->id_meta)->get() : array();
                break;
            case "Revision Laterales":
                $pieza = array();
                foreach ($juego as $pza) {
                    $p = RevLaterales_pza::where('id_pza', $pza)->first();
                    array_push($pieza, $p);
                }
                $piezas = !empty($pieza) && $pieza[0] ? RevLaterales_pza::where('id_meta', $pieza[0]->id_meta)->get() : array();
                break;
            case "Primera Operacion":
                $pieza = array();
                foreach ($juego as $pza) {
                    $p = PrimeraOpeSoldadura_pza::where('id_pza', $pza)->first();
                    array_push($pieza, $p);
                }
                $piezas = !empty($pieza) && $pieza[0] ? PrimeraOpeSoldadura_pza::where('id_meta', $pieza[0]->id_meta)->get() : array();
                break;
            case "Barreno Maniobra":
                $pieza = array();
                foreach ($juego as $pza) {
                    $p = BarrenoManiobra_pza::where('id_pza', $pza)->first();
                    array_push($pieza, $p);
                }
                $piezas = !empty($pieza) && $pieza[0] ? BarrenoManiobra_pza::where('id_meta', $pieza[0]->id_meta)->get() : array();
                break;
            case "Segunda Operacion":
                $pieza = array();
                foreach ($juego as $pza) {
                    $p = SegundaOpeSoldadura_pza::where('id_pza', $pza)->first();
                    array_push($pieza, $p);
                }
                $piezas = !empty($pieza) && $pieza[0] ? SegundaOpeSoldadura_pza::where('id_meta', $pieza[0]->id_meta)->get() : array();
                break;
            case "Soldadura":
                $pieza = array();
                foreach ($juego as $pza) {
                    $p = Soldadura_pza::where('id_pza', $pza)->first();
                    array_push($pieza, $p);
                }
                $piezas = !empty($pieza) && $pieza[0] ? Soldadura_pza::where('id_meta', $pieza[0]->id_meta)->get() : array();
                break;
            case "Soldadura PTA":
                $pieza = array();
                foreach ($juego as $pza) {
                    preg_match('/^(\d+[a-zA-Z]*)(\d+)$/', $pza, $matches);
                    if (count($matches) == 3) {
                        $n_pieza = $matches[1];
                        $id_proceso = $matches[2];
                        $p = SoldaduraPTA_pza::where('n_pieza', $n_pieza)->where('id_proceso', $id_proceso)->first();
                    } else {
                        $p = SoldaduraPTA_pza::where('id_pza', $pza)->first();
                    }

                    if ($p) {
                        array_push($pieza, $p);
                    }
                }
                if (count($pieza) > 0) {
                    $piezas = SoldaduraPTA_pza::where('id_meta', $pieza[0]->id_meta)->get();
                } else {
                    $piezas = array();
                }
                break;
            case "Rectificado":
                $pieza = array();
                foreach ($juego as $pza) {
                    $p = Rectificado_pza::where('id_pza', $pza)->first();
                    array_push($pieza, $p);
                }
                $piezas = !empty($pieza) && $pieza[0] ? Rectificado_pza::where('id_meta', $pieza[0]->id_meta)->get() : array();
                break;
            case "Asentado":
                $pieza = array();
                foreach ($juego as $pza) {
                    $p = Asentado_pza::where('id_pza', $pza)->first();
                    array_push($pieza, $p);
                }
                $piezas = !empty($pieza) && $pieza[0] ? Asentado_pza::where('id_meta', $pieza[0]->id_meta)->get() : array();
                break;
            case "Calificado":
                $pieza = array();
                foreach ($juego as $pza) {
                    $p = revCalificado_pza::where('id_pza', $pza)->first();
                    array_push($pieza, $p);
                }
                $piezas = !empty($pieza) && $pieza[0] ? revCalificado_pza::where('id_meta', $pieza[0]->id_meta)->get() : array();
                break;
            case "Acabado Bombillo":
                $pieza = array();
                foreach ($juego as $pza) {
                    $p = AcabadoBombilo_pza::where('id_pza', $pza)->first();
                    array_push($pieza, $p);
                }
                $piezas = !empty($pieza) && $pieza[0] ? AcabadoBombilo_pza::where('id_meta', $pieza[0]->id_meta)->get() : array();
                break;
            case "Acabado Molde":
                $pieza = array();
                foreach ($juego as $pza) {
                    $p = AcabadoMolde_pza::where('id_pza', $pza)->first();
                    array_push($pieza, $p);
                }
                $piezas = !empty($pieza) && $pieza[0] ? AcabadoMolde_pza::where('id_meta', $pieza[0]->id_meta)->get() : array();
                break;
            case "Cavidades":
                $pieza = array();
                foreach ($juego as $pza) {
                    $p = Cavidades_pza::where('id_pza', $pza)->first();
                    array_push($pieza, $p);
                }
                $piezas = !empty($pieza) && $pieza[0] ? Cavidades_pza::where('id_meta', $pieza[0]->id_meta)->get() : array();
                break;
            case "Barreno Profundidad":
                $pieza = array();
                foreach ($juego as $pza) {
                    $p = BarrenoProfundidad_pza::where('id_pza', $pza)->first();
                    array_push($pieza, $p);
                }
                $piezas = !empty($pieza) && $pieza[0] ? BarrenoProfundidad_pza::where('id_meta', $pieza[0]->id_meta)->get() : array();
                break;
            case "Copiado":
                $pieza = array();
                foreach ($juego as $pza) {
                    $p = Copiado_pza::where('id_pza', $pza)->first();
                    array_push($pieza, $p);
                }
                $piezas = !empty($pieza) && $pieza[0] ? Copiado_pza::where('id_meta', $pieza[0]->id_meta)->get() : array();
                break;
            case "Off Set":
                $pieza = array();
                foreach ($juego as $pza) {
                    $p = OffSet_pza::where('id_pza', $pza)->first();
                    array_push($pieza, $p);
                }
                $piezas = !empty($pieza) && $pieza[0] ? OffSet_pza::where('id_meta', $pieza[0]->id_meta)->get() : array();
                break;
            case "Palomas":
                $pieza = array();
                foreach ($juego as $pza) {
                    $p = Palomas_pza::where('id_pza', $pza)->first();
                    array_push($pieza, $p);
                }
                $piezas = !empty($pieza) && $pieza[0] ? Palomas_pza::where('id_meta', $pieza[0]->id_meta)->get() : array();
                break;
            case "Rebajes":
                $pieza = array();
                foreach ($juego as $pza) {
                    $p = Rebajes_pza::where('id_pza', $pza)->first();
                    array_push($pieza, $p);
                }
                $piezas = !empty($pieza) && $pieza[0] ? Rebajes_pza::where('id_meta', $pieza[0]->id_meta)->get() : array();
                break;
            case "Operacion Equipo_1 operacion":
            case "Operacion Equipo_2 operacion":
                $pieza = array();
                foreach ($juego as $pza) {
                    $p = PySOpeSoldadura_pza::where('id_pza', $pza)->first();
                    if (!$p) {
                        $p = CandadoObturador_pza::where('id_pza', $pza)->first();
                    }
                    if ($p) {
                        array_push($pieza, $p);
                    }
                }
                if (!empty($pieza) && $pieza[0]) {
                    $piezas = get_class($pieza[0])::where('id_meta', $pieza[0]->id_meta)->get();
                } else {
                    $piezas = array();
                }
                break;
            case "Embudo CM":
                $pieza = array();
                foreach ($juego as $pza) {
                    $p = EmbudoCM_pza::where('id_pza', $pza)->first();
                    array_push($pieza, $p);
                }
                $piezas = !empty($pieza) && $pieza[0] ? EmbudoCM_pza::where('id_meta', $pieza[0]->id_meta)->get() : array();
                break;
            case "Primera Operacion Cabeza Soplo":
                $pieza = array();
                foreach ($juego as $pza) {
                    $p = PrimeraOperacionCabezaSoplo_pza::where('id_pza', $pza)->first();
                    array_push($pieza, $p);
                }
                $piezas = !empty($pieza) && $pieza[0] ? PrimeraOperacionCabezaSoplo_pza::where('id_meta', $pieza[0]->id_meta)->get() : array();
                break;
            case "Segunda Operacion Cabeza Soplo":
                $pieza = array();
                foreach ($juego as $pza) {
                    $p = SegundaOperacionCabezaSoplo_pza::where('id_pza', $pza)->first();
                    array_push($pieza, $p);
                }
                $piezas = !empty($pieza) && $pieza[0] ? SegundaOperacionCabezaSoplo_pza::where('id_meta', $pieza[0]->id_meta)->get() : array();
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
    public function liberarPiezas($piezas, $proceso, $buena, $observacion)
    {
        if (empty($piezas) || !$piezas[0]) return;
        //Algoritmo para liberar solamente 1 juego
        $meta = Metas::find($piezas[0]->id_meta);
        //Actualizar el estado de liberacion de la pieza
        foreach ($piezas as $pza) {
            if ($pza->n_pieza) {
                $n_pieza = $pza->n_pieza;
            } else {
                $n_pieza = $pza->n_juego;
            }
            Pieza::where('n_pieza', $n_pieza)->where('id_clase', $meta->id_clase)->where('proceso', $proceso)->update([
                'liberacion' => 1,
                'fecha_liberacion' => date('Y-m-d H:i:s'),
                'user_liberacion' => auth()->user()->matricula,
                'observacion_liberacion' => $observacion,
            ]);

            // Logger de Auditoría
            $claseLog = Clase::find($meta->id_clase);
            SystemLog::create([
                'user_matricula' => auth()->user()->matricula,
                'action' => 'Liberación por Calidad',
                'details' => "Pieza/Juego $n_pieza LIBERADA en $proceso. Obs: $observacion",
                'ot' => $claseLog ? ($claseLog->id_ot . ' - ' . ($claseLog->tamanio ?? 'N/A')) : ($meta->id_ot ?? 'N/A'),
                'clase' => $claseLog->nombre ?? 'N/A',
                'id_ot' => $meta->id_ot,
                'id_clase' => $meta->id_clase,
                'proceso' => $proceso,
                'n_pieza' => $n_pieza,
                'maquina' => $meta->maquina
            ]);

            // ── Para Soldadura PTA: liberar también la mitad contraria del par M/H ──
            if ($proceso === 'Soldadura PTA') {
                $ultimaLetra = substr($n_pieza, -1);
                if ($ultimaLetra === 'H' || $ultimaLetra === 'M') {
                    $partnerLetra = $ultimaLetra === 'H' ? 'M' : 'H';
                    $partnerNPieza = substr($n_pieza, 0, -1) . $partnerLetra;
                    Pieza::where('n_pieza', $partnerNPieza)
                        ->where('id_clase', $meta->id_clase)
                        ->where('proceso', $proceso)
                        ->update([
                            'liberacion' => 1,
                            'fecha_liberacion' => date('Y-m-d H:i:s'),
                            'user_liberacion' => auth()->user()->matricula,
                            'observacion_liberacion' => $observacion,
                        ]);
                }
            }
        }

        //Algoritmo para liberar 5 juegos despues de que se libere uno
        // //Identificar los juegos malos
        // $meta = Metas::find($piezas[0]->id_meta);
        // $juegosMalos = $this->juegosMalos($meta, $proceso);
        // if ($buena == 'true') {
        //     foreach ($piezas as $pza) {
        //         //Actualizar el estado de liberacion de la pieza
        //         if ($pza->n_pieza) {
        //             $numero = substr($pza->n_pieza, 0, -1);
        //             $piezaH = Pieza::where('n_pieza', $numero . "H")->where('id_clase', $meta->id_clase)->where('proceso', $proceso)->where('error', 'Ninguno')->where('liberacion', 0)->first();
        //             $piezaM = Pieza::where('n_pieza', $numero . "M")->where('id_clase', $meta->id_clase)->where('proceso', $proceso)->where('error', 'Ninguno')->where('liberacion', 0)->first();

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
        //             $pieza = Pieza::where('n_pieza', $pza->n_juego)->where('id_clase', $meta->id_clase)->where('proceso', $proceso)->where('error', 'Ninguno')->where('liberacion', 0)->first();
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
        //     $meta = Metas::find($piezas[0]->id_meta);
        //     //Actualizar el estado de liberacion de la pieza
        //     foreach ($piezas as $pza) {
        //         if ($pza->n_pieza) {
        //             $n_pieza = $pza->n_pieza;
        //         } else {
        //             $n_pieza = $pza->n_juego;
        //         }
        //         Pieza::where('n_pieza', $n_pieza)->where('id_clase', $meta->id_clase)->where('proceso', $proceso)->update([
        //             'liberacion' => 1,
        //             'fecha_liberacion' => date('Y-m-d H:i:s'),
        //             'user_liberacion' => auth()->user()->matricula,
        //         ]);
        //     }
        // }
    }
    public function rechazarPieza($piezas, $proceso, $observacion)
    {
        if (empty($piezas) || !$piezas[0]) return;
        $meta = Metas::find($piezas[0]->id_meta);
        //Actualizar el estado de liberacion de la pieza
        foreach ($piezas as $pza) {
            if ($pza->n_pieza) {
                $n_pieza = $pza->n_pieza;
            } else {
                $n_pieza = $pza->n_juego;
            }
            Pieza::where('n_pieza', $n_pieza)->where('id_clase', $meta->id_clase)->where('proceso', $proceso)->update([
                'liberacion' => 2,
                'fecha_liberacion' => date('Y-m-d H:i:s'),
                'user_liberacion' => auth()->user()->matricula,
                'observacion_liberacion' => $observacion,
            ]);

            // Logger de Auditoría
            $claseLog = Clase::find($meta->id_clase);
            SystemLog::create([
                'user_matricula' => auth()->user()->matricula,
                'action' => 'Rechazo por Calidad',
                'details' => "Pieza/Juego $n_pieza RECHAZADA en $proceso. Obs: $observacion",
                'ot' => $claseLog ? ($claseLog->id_ot . ' - ' . ($claseLog->tamanio ?? 'N/A')) : ($meta->id_ot ?? 'N/A'),
                'clase' => $claseLog->nombre ?? 'N/A',
                'id_ot' => $meta->id_ot,
                'id_clase' => $meta->id_clase,
                'proceso' => $proceso,
                'n_pieza' => $n_pieza,
                'maquina' => $meta->maquina
            ]);

            // ── Para Soldadura PTA: rechazar también la mitad contraria del par M/H ──
            if ($proceso === 'Soldadura PTA') {
                $ultimaLetra = substr($n_pieza, -1);
                if ($ultimaLetra === 'H' || $ultimaLetra === 'M') {
                    $partnerLetra = $ultimaLetra === 'H' ? 'M' : 'H';
                    $partnerNPieza = substr($n_pieza, 0, -1) . $partnerLetra;
                    Pieza::where('n_pieza', $partnerNPieza)
                        ->where('id_clase', $meta->id_clase)
                        ->where('proceso', $proceso)
                        ->update([
                            'liberacion' => 2,
                            'fecha_liberacion' => date('Y-m-d H:i:s'),
                            'user_liberacion' => auth()->user()->matricula,
                            'observacion_liberacion' => $observacion,
                        ]);
                }
            }
        }
    }
    public function juegosMalos($meta, $proceso)
    {
        $juegosMalos = array();
        $piezasMalas = Pieza::where('id_operador', $meta->id_usuario)->where('id_clase', $meta->id_clase)->where('proceso', $proceso)->where('error', '!=', 'Ninguno')->where('liberacion', 0)->get();
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
    public function liberarPiezasMeta($meta, $piezasMeta, $piezaLiberar, $proceso)
    {

        foreach ($piezasMeta as $pieza) {
            if ($pieza->n_pieza) {
                $numero = substr($pieza->n_pieza, 0, -1);
                $piezaLiberadaH = Pieza::where('id_ot', $meta->id_ot)->where('id_clase', $meta->id_clase)->where('id_operador', $meta->id_usuario)->where('proceso', $proceso)->where('n_pieza', $numero . "H")->where('error', 'Ninguno')->where('liberacion', 1)->first();
                $piezaLiberadaM = Pieza::where('id_ot', $meta->id_ot)->where('id_clase', $meta->id_clase)->where('id_operador', $meta->id_usuario)->where('proceso', $proceso)->where('n_pieza', $numero . "M")->where('error', 'Ninguno')->where('liberacion', 1)->first();
                if ($piezaLiberadaH && $piezaLiberadaM) {
                    $piezaLiberada = true;
                } else {
                    $piezaLiberada = false;
                }
            } else {
                $piezaLiberada = Pieza::where('id_ot', $meta->id_ot)->where('id_clase', $meta->id_clase)->where('id_operador', $meta->id_usuario)->where('proceso', $proceso)->where('n_pieza', $pieza->n_juego)->where('error', 'Ninguno')->where('liberacion', 1)->first();
            }
            if ($piezaLiberada) {
                if (substr($piezaLiberar, -1) == "H" || substr($piezaLiberar, -1) == "M") {
                    $numero = substr($piezaLiberar, 0, -1);
                    $piezaLiberarH = Pieza::where('id_ot', $meta->id_ot)->where('id_clase', $meta->id_clase)->where('id_operador', $meta->id_usuario)->where('proceso', $proceso)->where('n_pieza', $numero . "H")->where('error', 'Ninguno')->first();
                    $piezaLiberarM = Pieza::where('id_ot', $meta->id_ot)->where('id_clase', $meta->id_clase)->where('id_operador', $meta->id_usuario)->where('proceso', $proceso)->where('n_pieza', $numero . "M")->where('error', 'Ninguno')->first();

                    if ($piezaLiberarH && $piezaLiberarM) {
                        $piezaLiberarH->liberacion = 1;
                        $piezaLiberarH->fecha_liberacion = date('Y-m-d H:i:s');
                        $piezaLiberarH->user_liberacion = $piezaLiberadaH->user_liberacion;
                        $piezaLiberarH->save();

                        $piezaLiberarM->liberacion = 1;
                        $piezaLiberarM->fecha_liberacion = date('Y-m-d H:i:s');
                        $piezaLiberarM->user_liberacion = $piezaLiberadaH->user_liberacion;
                        $piezaLiberarM->save();
                        return;
                    }
                } else {
                    $piezaLiberar = Pieza::where('id_ot', $meta->id_ot)->where('id_clase', $meta->id_clase)->where('id_operador', $meta->id_usuario)->where('proceso', $proceso)->where('n_pieza', $piezaLiberar)->where('error', 'Ninguno')->first();
                    $piezaLiberar->liberacion = 1;
                    $piezaLiberar->fecha_liberacion = date('Y-m-d H:i:s');
                    $piezaLiberar->user_liberacion = $piezaLiberada->user_liberacion;
                    $piezaLiberar->save();
                    return;
                }
            }
        }
    }
    public function piecesToBeReleased()
    {
        // ── OPTIMIZACIÓN: query directa sin pasar por saveInArray completo ──
        // Solo necesitamos piezas con error pendientes de liberar, de clases activas
        $finishedClassIds = Clase::where('finalizada', '!=', 0)->pluck('id')->toArray();

        // 1 query: solo las piezas que realmente necesitamos
        $piezasRaw = Pieza::where('error', '!=', 'Ninguno')
            ->where('liberacion', 0)
            ->when(!empty($finishedClassIds), fn($q) => $q->whereNotIn('id_clase', $finishedClassIds))
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
