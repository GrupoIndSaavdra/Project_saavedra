<?php

namespace App\Http\Controllers;

use App\Models\Cepillado;
use App\Models\Clase;
use App\Models\Desbaste_pza;
use App\Models\DesbasteExterior;
use App\Models\Moldura;
use App\Models\Orden_trabajo;
use App\Models\PrimeraOpeSoldadura;
use App\Models\PrimeraOpeSoldadura_pza;
use App\Models\Pza_cepillado;
use App\Models\RevLaterales;
use App\Models\RevLaterales_pza;
use App\Models\SegundaOpeSoldadura;
use App\Models\SegundaOpeSoldadura_pza;
use Illuminate\Http\Request;

class ProgresoProcesosController extends Controller
{
    public function show()
    {
        $ot = $this->almacenarDatos();
        if ($ot != 0) {
            return view('processesAdmin.verProcesos', ['ot' => $ot]);
        }
        return view('processesAdmin.verProcesos');
    }
    public function almacenarDatos()
    {
        $clases = Clase::all();
        if (count($clases) > 0) {
            // ── OPTIMIZACIÓN: pre-cargar OTs y molduras en memoria ──
            // Sin esto, el switch de 26 casos hacía Orden_trabajo::query()->where() en cada iteración
            $ordenesMap = Orden_trabajo::all()->keyBy('id');
            $moldurasMap = Moldura::all()->keyBy('id');

            $ot = array();
            $contador = 0;

            // Renombrado de $clases a $clase para evitar shadowing con la colección externa
            foreach ($clases as $clase) {
                // Pre-calcular OT y moldura UNA SOLA VEZ por clase (no 26 veces)
                $ordenT  = $ordenesMap->get($clase->id_ot);
                $moldura = $ordenT ? $moldurasMap->get($ordenT->id_moldura) : null;

                for ($i = 0; $i < 26; $i++) {
                    switch ($i) {
                        case 0:
                            $ot[$contador][$i] = $ordenT ? $ordenT->id : null;
                            break;
                        case 1:
                            $ot[$contador][$i] = $moldura ? $moldura->nombre : '?';
                            break;
                        case 2:
                            $ot[$contador][$i] = $clase->nombre . " " . $clase->tamanio;
                            break;
                        case 3:
                            $proceso = 'Cepillado_' . $clase->nombre . "_" . ($ordenT ? $ordenT->id : '');
                            $cepillado = Cepillado::query()->where('id_proceso', $proceso)->first();

                            if ($cepillado != null) {
                                $ot[$contador][$i] = count(Pza_cepillado::query()->where('estado', 2)->where('correcto', 1)->where('id_proceso', $cepillado->id)->get()) / 2;
                            } else {
                                $ot[$contador][$i] = 0;
                            }
                            break;
                        case 4:
                            $proceso = 'desbaste_' . $clase->nombre . "_" . ($ordenT ? $ordenT->id : '');
                            $desbaste = DesbasteExterior::query()->where('id_proceso', $proceso)->first();

                            if ($desbaste != null) {
                                $pzasCorrectas = Desbaste_pza::query()->where('estado', 2)->where('correcto', 1)->where('id_proceso', $desbaste->id)->get();
                                if (isset($pzasCorrectas)) {
                                    $correctas = 0;
                                    $juegosUtilizados = array();
                                    for ($x = 0; $x < count($pzasCorrectas); $x++) {
                                        for ($y = 0; $y < count($pzasCorrectas); $y++) {
                                            if ($pzasCorrectas[$x]->n_juego === $pzasCorrectas[$y]->n_juego && $x != $y) {
                                                if ($pzasCorrectas[$x]->correcto == 1 && $pzasCorrectas[$y]->correcto == 1) {
                                                    if (array_search($pzasCorrectas[$x]->n_juego, $juegosUtilizados) === false) {
                                                        array_push($juegosUtilizados, $pzasCorrectas[$x]->n_juego);
                                                        $correctas++;
                                                    }
                                                }
                                            }
                                        }
                                    }
                                    $ot[$contador][$i] = $correctas;
                                } else {
                                    $ot[$contador][$i] = 0;
                                }
                            } else {
                                $ot[$contador][$i] = 0;
                            }
                            break;
                        case 5:
                            $proceso = 'revLaterales_' . $clase->nombre . "_" . ($ordenT ? $ordenT->id : '');
                            $revLaterales = RevLaterales::query()->where('id_proceso', $proceso)->first();

                            if ($revLaterales != null) {
                                $pzasCorrectas = RevLaterales_pza::query()->where('estado', 2)->where('correcto', 1)->where('id_proceso', $revLaterales->id)->get();
                                if (isset($pzasCorrectas)) {
                                    $correctas = 0;
                                    $juegosUtilizados = array();
                                    for ($x = 0; $x < count($pzasCorrectas); $x++) {
                                        for ($y = 0; $y < count($pzasCorrectas); $y++) {
                                            if ($pzasCorrectas[$x]->n_juego === $pzasCorrectas[$y]->n_juego && $x != $y) {
                                                if ($pzasCorrectas[$x]->correcto == 1 && $pzasCorrectas[$y]->correcto == 1) {
                                                    if (array_search($pzasCorrectas[$x]->n_juego, $juegosUtilizados) === false) {
                                                        array_push($juegosUtilizados, $pzasCorrectas[$x]->n_juego);
                                                        $correctas++;
                                                    }
                                                }
                                            }
                                        }
                                    }
                                    $ot[$contador][$i] = $correctas;
                                } else {
                                    $ot[$contador][$i] = 0;
                                }
                            } else {
                                $ot[$contador][$i] = 0;
                            }
                            break;
                        case 6:
                            $proceso = '1opeSoldadura_' . $clase->nombre . "_" . ($ordenT ? $ordenT->id : '');
                            $primeraOpeSoldadura = PrimeraOpeSoldadura::query()->where('id_proceso', $proceso)->first();

                            if ($primeraOpeSoldadura != null) {
                                $pzasCorrectas = PrimeraOpeSoldadura_pza::query()->where('estado', 2)->where('correcto', 1)->where('id_proceso', $primeraOpeSoldadura->id)->get();
                                if (isset($pzasCorrectas)) {
                                    $correctas = 0;
                                    $juegosUtilizados = array();
                                    for ($x = 0; $x < count($pzasCorrectas); $x++) {
                                        for ($y = 0; $y < count($pzasCorrectas); $y++) {
                                            if ($pzasCorrectas[$x]->n_juego === $pzasCorrectas[$y]->n_juego && $x != $y) {
                                                if ($pzasCorrectas[$x]->correcto == 1 && $pzasCorrectas[$y]->correcto == 1) {
                                                    if (array_search($pzasCorrectas[$x]->n_juego, $juegosUtilizados) === false) {
                                                        array_push($juegosUtilizados, $pzasCorrectas[$x]->n_juego);
                                                        $correctas++;
                                                    }
                                                }
                                            }
                                        }
                                    }
                                    $ot[$contador][$i] = $correctas;
                                } else {
                                    $ot[$contador][$i] = 0;
                                }
                            } else {
                                $ot[$contador][$i] = 0;
                            }
                            break;
                        case 7:
                            $proceso = '2opeSoldadura_' . $clase->nombre . "_" . ($ordenT ? $ordenT->id : '');
                            $segundaOpeSoldadura = SegundaOpeSoldadura::query()->where('id_proceso', $proceso)->first();

                            if ($segundaOpeSoldadura != null) {
                                $pzasCorrectas = SegundaOpeSoldadura_pza::query()->where('estado', 2)->where('correcto', 1)->where('id_proceso', $segundaOpeSoldadura->id)->get();
                                if (isset($pzasCorrectas)) {
                                    $correctas = 0;
                                    $juegosUtilizados = array();
                                    for ($x = 0; $x < count($pzasCorrectas); $x++) {
                                        for ($y = 0; $y < count($pzasCorrectas); $y++) {
                                            if ($pzasCorrectas[$x]->n_juego === $pzasCorrectas[$y]->n_juego && $x != $y) {
                                                if ($pzasCorrectas[$x]->correcto == 1 && $pzasCorrectas[$y]->correcto == 1) {
                                                    if (array_search($pzasCorrectas[$x]->n_juego, $juegosUtilizados) === false) {
                                                        array_push($juegosUtilizados, $pzasCorrectas[$x]->n_juego);
                                                        $correctas++;
                                                    }
                                                }
                                            }
                                        }
                                    }
                                    $ot[$contador][$i] = $correctas;
                                } else {
                                    $ot[$contador][$i] = 0;
                                }
                            } else {
                                $ot[$contador][$i] = 0;
                            }
                            break;
                        default:
                            if ($i === 25) {
                                $ot[$contador][$i] = $clase->pedido;
                            } else {
                                $ot[$contador][$i] = 0;
                            }
                            break;
                    }
                }
                $contador++;
            }
            return $ot;
        }
        return 0;
    }
}
