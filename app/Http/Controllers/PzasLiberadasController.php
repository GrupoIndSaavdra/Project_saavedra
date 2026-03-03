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
use App\Models\SegundaOpeSoldadura_pza;
use App\Models\SegundaOperacionCabezaSoplo_pza;
use App\Models\Soldadura_pza;
use App\Models\SoldaduraPTA_pza;
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
        //Datos de las piezas
        if (isset($request->requestLiberation)) {
            return redirect()->route('home');
        }
        $extraRequest = explode(",", $request->extraRequest);
        $extraRequest[0] = str_replace("_", "/", $extraRequest[0]);

        $datosPiezas = array(
            "workOrder" => $extraRequest[0],
            "class" => $extraRequest[1],
            "operator" => $extraRequest[2],
            "machine" => $extraRequest[3],
            "process" => $extraRequest[4],
            "error" => $extraRequest[5],
            "dateFrom" => $extraRequest[6],
            "dateTo" => $extraRequest[7],
            "n_juego" => $extraRequest[8],
            "action" => null,
        );
        return $this->showPieces($this->controladorPzas->search($datosPiezas, 'quality'));
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
                $piezas = Pza_cepillado::where('id_meta', $pieza[0]->id_meta)->get();
                break;
            case "Desbaste Exterior":
                $pieza = array();
                foreach ($juego as $pza) {
                    $p = Desbaste_pza::where('id_pza', $pza)->first();
                    array_push($pieza, $p);
                }
                $piezas = Desbaste_pza::where('id_meta', $pieza[0]->id_meta)->get();
                break;
            case "Revision Laterales":
                $pieza = array();
                foreach ($juego as $pza) {
                    $p = RevLaterales_pza::where('id_pza', $pza)->first();
                    array_push($pieza, $p);
                }
                $piezas = RevLaterales_pza::where('id_meta', $pieza[0]->id_meta)->get();
                break;
            case "Primera Operacion":
                $pieza = array();
                foreach ($juego as $pza) {
                    $p = PrimeraOpeSoldadura_pza::where('id_pza', $pza)->first();
                    array_push($pieza, $p);
                }
                $piezas = PrimeraOpeSoldadura_pza::where('id_meta', $pieza[0]->id_meta)->get();
                break;
            case "Barreno Maniobra":
                $pieza = array();
                foreach ($juego as $pza) {
                    $p = BarrenoManiobra_pza::where('id_pza', $pza)->first();
                    array_push($pieza, $p);
                }
                $piezas = BarrenoManiobra_pza::where('id_meta', $pieza[0]->id_meta)->get();
                break;
            case "Segunda Operacion":
                $pieza = array();
                foreach ($juego as $pza) {
                    $p = SegundaOpeSoldadura_pza::where('id_pza', $pza)->first();
                    array_push($pieza, $p);
                }
                $piezas = SegundaOpeSoldadura_pza::where('id_meta', $pieza[0]->id_meta)->get();
                break;
            case "Soldadura":
                $pieza = array();
                foreach ($juego as $pza) {
                    $p = Soldadura_pza::where('id_pza', $pza)->first();
                    array_push($pieza, $p);
                }
                $piezas = Soldadura_pza::where('id_meta', $pieza[0]->id_meta)->get();
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
                $piezas = Rectificado_pza::where('id_meta', $pieza[0]->id_meta)->get();
                break;
            case "Asentado":
                $pieza = array();
                foreach ($juego as $pza) {
                    $p = Asentado_pza::where('id_pza', $pza)->first();
                    array_push($pieza, $p);
                }
                $piezas = Asentado_pza::where('id_meta', $pieza[0]->id_meta)->get();
                break;
            case "Calificado":
                $pieza = array();
                foreach ($juego as $pza) {
                    $p = revCalificado_pza::where('id_pza', $pza)->first();
                    array_push($pieza, $p);
                }
                $piezas = revCalificado_pza::where('id_meta', $pieza[0]->id_meta)->get();
                break;
            case "Acabado Bombillo":
                $pieza = array();
                foreach ($juego as $pza) {
                    $p = AcabadoBombilo_pza::where('id_pza', $pza)->first();
                    array_push($pieza, $p);
                }
                $piezas = AcabadoBombilo_pza::where('id_meta', $pieza[0]->id_meta)->get();
                break;
            case "Acabado Molde":
                $pieza = array();
                foreach ($juego as $pza) {
                    $p = AcabadoMolde_pza::where('id_pza', $pza)->first();
                    array_push($pieza, $p);
                }
                $piezas = AcabadoMolde_pza::where('id_meta', $pieza[0]->id_meta)->get();
                break;
            case "Cavidades":
                $pieza = array();
                foreach ($juego as $pza) {
                    $p = Cavidades_pza::where('id_pza', $pza)->first();
                    array_push($pieza, $p);
                }
                $piezas = Cavidades_pza::where('id_meta', $pieza[0]->id_meta)->get();
                break;
            case "Barreno Profundidad":
                $pieza = array();
                foreach ($juego as $pza) {
                    $p = BarrenoProfundidad_pza::where('id_pza', $pza)->first();
                    array_push($pieza, $p);
                }
                $piezas = BarrenoProfundidad_pza::where('id_meta', $pieza[0]->id_meta)->get();
                break;
            case "Copiado":
                $pieza = array();
                foreach ($juego as $pza) {
                    $p = Copiado_pza::where('id_pza', $pza)->first();
                    array_push($pieza, $p);
                }
                $piezas = Copiado_pza::where('id_meta', $pieza[0]->id_meta)->get();
                break;
            case "Off Set":
                $pieza = array();
                foreach ($juego as $pza) {
                    $p = OffSet_pza::where('id_pza', $pza)->first();
                    array_push($pieza, $p);
                }
                $piezas = OffSet_pza::where('id_meta', $pieza[0]->id_meta)->get();
                break;
            case "Palomas":
                $pieza = array();
                foreach ($juego as $pza) {
                    $p = Palomas_pza::where('id_pza', $pza)->first();
                    array_push($pieza, $p);
                }
                $piezas = Palomas_pza::where('id_meta', $pieza[0]->id_meta)->get();
                break;
            case "Rebajes":
                $pieza = array();
                foreach ($juego as $pza) {
                    $p = Rebajes_pza::where('id_pza', $pza)->first();
                    array_push($pieza, $p);
                }
                $piezas = Rebajes_pza::where('id_meta', $pieza[0]->id_meta)->get();
                break;
            case "Operacion Equipo_1 operacion":
                $pieza = array();
                foreach ($juego as $pza) {
                    $p = PySOpeSoldadura_pza::where('id_pza', $pza)->first();
                    array_push($pieza, $p);
                }
                $piezas = PySOpeSoldadura_pza::where('id_meta', $pieza[0]->id_meta)->get();
                break;
            case "Operacion Equipo_2 operacion":
                $pieza = array();
                foreach ($juego as $pza) {
                    $p = PySOpeSoldadura_pza::where('id_pza', $pza)->first();
                    array_push($pieza, $p);
                }
                $piezas = PySOpeSoldadura_pza::where('id_meta', $pieza[0]->id_meta)->get();
                break;
            case "Embudo CM":
                $pieza = array();
                foreach ($juego as $pza) {
                    $p = EmbudoCM_pza::where('id_pza', $pza)->first();
                    array_push($pieza, $p);
                }
                $piezas = EmbudoCM_pza::where('id_meta', $pieza[0]->id_meta)->get();
                break;
            case "Primera Operacion Cabeza Soplo":
                $pieza = array();
                foreach ($juego as $pza) {
                    $p = PrimeraOperacionCabezaSoplo_pza::where('id_pza', $pza)->first();
                    array_push($pieza, $p);
                }
                $piezas = PrimeraOperacionCabezaSoplo_pza::where('id_meta', $pieza[0]->id_meta)->get();
                break;
            case "Segunda Operacion Cabeza Soplo":
                $pieza = array();
                foreach ($juego as $pza) {
                    $p = SegundaOperacionCabezaSoplo_pza::where('id_pza', $pza)->first();
                    array_push($pieza, $p);
                }
                $piezas = SegundaOperacionCabezaSoplo_pza::where('id_meta', $pieza[0]->id_meta)->get();
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
        //Obtener las clases ya finalizadas
        $finishedClasess = Clase::where('finalizada', '!=', 0)->get();
        $arrayFClasses = array();
        foreach ($finishedClasess as $finishedClass) {
            array_push($arrayFClasses, $finishedClass->id);
        }

        $infoPieces = array();
        $pieces = Pieza::where('error', '!=', "Ninguno")->where('liberacion', 0)->get();
        $pieces = $this->controladorPzas->saveInArray($pieces);
        foreach ($pieces as $key => $piece) {
            if (str_contains($piece[5], "Incompleto") || in_array($piece["id_clase"], $arrayFClasses)) {
                //borrar pieza del array
                unset($pieces[$key]);
            }
        }
        if (count($pieces) > 0) {
            $this->controladorPzas->saveInfoPzas($infoPieces, $pieces);
        }
        return [$pieces, $infoPieces];
    }
}
