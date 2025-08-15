<?php

namespace App\Http\Controllers;

use App\Models\Clase;
use App\Models\Desbaste_pza;
use App\Models\DesbasteExterior;
use App\Models\Metas;
use App\Models\Moldura;
use App\Models\Orden_trabajo;
use App\Models\Pieza;
use App\Models\PrimeraOpeSoldadura;
use App\Models\PrimeraOpeSoldadura_cnominal;
use App\Models\PrimeraOpeSoldadura_pza;
use App\Models\PrimeraOpeSoldadura_tolerancia;
use App\Models\Procesos;
use App\Models\RevLaterales;
use App\Models\RevLaterales_pza;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class PrimeraOpeSoldaduraController extends Controller
{
    protected $controladorPzasLiberadas;
    public function __construct()
    {
        $this->controladorPzasLiberadas = new PzasLiberadasController();
    }
    public function show($error)
    {
        $ot = Orden_trabajo::all(); //Obtención de todas las ordenes de trabajo.
        if (count($ot) != 0) {
            $oTrabajo = array(); //Declara arreglo para guardar las ordenes de trabajo disponibles en Primera operación soldadura.
            //Recorre todas las ordenes de trabajo.
            foreach ($ot as $ot) {
                $contador = 0; //Contador para verificar que existan clases que pasaran por Primera operación soldadura
                $clases = Clase::where('id_ot', $ot->id)->get();
                //Recorre todas las clases registradas en la orden de trabajo.
                foreach ($clases as $clase) {
                    $proceso = Procesos::where('id_clase', $clase->id)->first(); //Obtención del proceso de la clase.
                    if ($proceso) {
                        if ($proceso->pOperacion) { //Si existen maquinas en Primera operación soldadura de esa clase, se almacena en el arreglo que se pasara a la vista
                            $contador++;
                        }
                    }
                }
                //Si hay clases que pasaran por Revisión laterales, se almacena la orden de trabajo en el arreglo.
                if ($contador != 0) {
                    array_push($oTrabajo, $ot);
                }
            }
            //Si hay clases que pasaran por Primera operación soldadura, se almacena la orden de trabajo en el arreglo.
            if (count($oTrabajo) != 0) {
                if ($error == 1) {
                    return view('processes.primeraOpeSoldadura', ['ot' => $oTrabajo, 'error' => $error]); //Retorno a vista de Primera operación soldadura 
                }
                return view('processes.primeraOpeSoldadura', ['ot' => $oTrabajo]); //Retorno a vista de Primera operación soldadura
            }
            if ($error == 1) {
                return view('processes.primeraOpeSoldadura', ['ot' => $oTrabajo, 'error' => $error]); //Retorno a vista de Primera operación soldadura 
            }
            //Se retorna a la vista de Primera operación soldadura con las ordenes de trabajo que tienen clases que pasaran por Primera operación soldadura 
            return view('processes.primeraOpeSoldadura', ['ot']); //Retorno a vista de Primera operación soldadura 
        }
        if ($error == 1) {
            return view('processes.primeraOpeSoldadura', ['error' => $error]); //Retorno a vista de Primera operación soldadura 
        }
        return view('processes.primeraOpeSoldadura');
    }
    public function storeheaderTable(Request $request)
    {
        //Si se obtienen los datos de la OT y la meta, se guardan en variables de sesión.
        if (session('controller')) {
            $meta = Metas::find(session('meta')); //Busco la meta de la OT.
        } else {
            $meta = Metas::find($request->metaData); //Busco la meta de la OT.
        }
        $ot = Orden_trabajo::where('id', $meta->id_ot)->first(); //Busco la OT que se quiere editar.
        $clase = Clase::find($meta->id_clase); //Busco la clase de la OT.
        $id = "Primera_Operacion_" . $clase->nombre . "_" . $ot->id; //Creación de id para tabla Primera operación soldadura.
        $cNominal = PrimeraOpeSoldadura_cnominal::where('id_proceso', $id)->first(); //Busco la meta de la OT.
        $tolerancia = PrimeraOpeSoldadura_tolerancia::where('id_proceso', $id)->first(); //Busco la meta de la OT.
        $moldura = Moldura::find($ot->id_moldura); //Busco la moldura de la OT.
        $proceso = PrimeraOpeSoldadura::where('id_proceso', $id)->first(); //Busco el proceso de la OT.
        if (!$proceso) {
            //Llenado de la tabla Primera operación soldadura.
            $desbaste = new PrimeraOpeSoldadura(); //Creación de objeto para llenar tabla Primera operación soldadura.
            $desbaste->id_proceso = $id; //Creación de id para tabla Primera operación soldadura.
            $desbaste->id_ot = $ot->id; //Llenado de id_proceso para tabla Primera operación soldadura.
            $desbaste->save(); //Guardado de datos en la tabla Primera operación soldadura.
        }
        $id_proceso = PrimeraOpeSoldadura::where('id_proceso', $id)->first();
        $pzasRestantes = count($this->piezasRestantes($clase));
        if (isset($request->n_pieza)) {  //Si se obtienen los datos de las piezas, se guardan en la tabla Primera operación soldadura_cnominal.
            $id_pieza = $request->n_pieza . $id_proceso->id; //Creación de id para tabla Primera operación soldadura_cnominal.
            $piezaExistente = PrimeraOpeSoldadura_pza::where('id_pza', $id_pieza)->first();
            if ($piezaExistente) {
                $piezaExistente->diametro1 = $request->diametro1;
                $piezaExistente->profundidad1 = $request->profundidad1;
                $piezaExistente->diametro2 = $request->diametro2;
                $piezaExistente->profundidad2 = $request->profundidad2;
                $piezaExistente->diametro3 = $request->diametro3;
                $piezaExistente->profundidad3 = $request->profundidad3;
                $piezaExistente->diametroSoldadura = $request->diametroSoldadura;
                $piezaExistente->profundidadSoldadura = $request->profundidadSoldadura;
                $piezaExistente->diametroBarreno = $request->diametroBarreno;
                $piezaExistente->simetriaLinea_partida = $request->simetriaLinea_partida;
                $piezaExistente->pernoAlineacion = $request->pernoAlineacion;
                $piezaExistente->Simetria90G = $request->Simetria90G;
                $piezaExistente->observaciones = $request->observaciones;
                $piezaExistente->estado = 2;
                $piezaExistente->save();

                if ($this->compararDatosPieza($piezaExistente, $cNominal, $tolerancia) == 0 && $request->error == 0) {
                    $piezaExistente->error = 'Maquinado';
                    $piezaExistente->correcto = 0;
                } else if (($this->compararDatosPieza($piezaExistente, $cNominal, $tolerancia) == 0 && $request->error == 'Fundicion') || ($this->compararDatosPieza($piezaExistente, $cNominal, $tolerancia) == 1 && $request->error == 'Fundicion')) {
                    $piezaExistente->error = $request->error;
                    $piezaExistente->correcto = 0;
                } else {
                    $piezaExistente->error = 'Ninguno';
                    $piezaExistente->correcto = 1;
                }
                $piezaExistente->save();

                $pieza = Pieza::where('n_pieza', $piezaExistente->n_pieza)->where('proceso', 'Primera Operacion Soldadura')->where('id_ot', $ot->id)->where('id_clase', $clase->id)->first();
                //Guardar los datos de las pieza en la tabla pieza (En donde se almacenan todas las piezas)
                if (!isset($pieza)) {
                    $pieza = new Pieza();
                }
                $pieza->id_clase = $clase->id;
                $pieza->id_ot = $ot->id;
                $pieza->n_pieza = $request->n_pieza;
                $pieza->id_operador = $meta->id_usuario;
                $pieza->maquina = $meta->maquina;
                $pieza->proceso = "Primera Operacion Soldadura";
                $pieza->error = $piezaExistente->error;
                $pieza->save();

                if ($pieza->error == 'Ninguno') {
                    //Obtener piezas de la meta
                    $piezasMeta = PrimeraOpeSoldadura_pza::where('id_meta', $meta->id)->get();
                    $this->controladorPzasLiberadas->liberarPiezasMeta($meta, $piezasMeta, $pieza->n_pieza, "Primera Operacion Soldadura");
                }

                //Actualizar resultado de la meta
                $contadorPzas = 0;
                $juegosUsados = array();
                $pzasCorrectas = PrimeraOpeSoldadura_pza::where('id_meta', $meta->id)->where('correcto', 1)->get(); //Obtención de todas las piezas correctas.
                foreach ($pzasCorrectas as $pzaCorrecta) {
                    $pzaCorrecta2 = PrimeraOpeSoldadura_pza::where('n_juego', $pzaCorrecta->n_juego)->where('id_meta', $meta->id)->get();
                    if (!in_array($pzaCorrecta->n_juego, $juegosUsados)) {
                        array_push($juegosUsados, $pzaCorrecta->n_juego);
                        $pzasMalas = 0;
                        foreach ($pzaCorrecta2 as $pza) {
                            if ($pza->correcto == 1) {
                                $contadorPzas += .5;
                            } else if ($pza->correcto === 0) {
                                $pzasMalas++;
                            }
                        }
                        if ($pzasMalas == 1) {
                            $contadorPzas -= .5;
                        }
                    }
                }
                $meta = Metas::find($meta->id); //Actualización de datos en tabla Metas.
                $meta->resultado = $contadorPzas;
                $meta->save(); //Guardado de datos en la tabla Metas.

                //  //Retornar la pieza siguiente
                $pzaUtilizar = PrimeraOpeSoldadura_pza::where('id_proceso', $id_proceso->id)->where('estado', 1)->where('id_meta', $meta->id)->first();
                if ($id_proceso) {
                    $pzasRestantes = count($this->piezasRestantes($clase));
                } else {
                    $pzasRestantes = 0;
                }
                if (isset($pzaUtilizar)) { //Si existe una pieza para utilizar, se retorna a la vista de Primera operación soldadura.
                    $pzasCreadas = PrimeraOpeSoldadura_pza::where('id_proceso', $id_proceso->id)->where('estado', 2)->where('id_meta', $meta->id)->get();
                    return view('processes.primeraOpeSoldadura', ['band' => 2, 'moldura' => $moldura->nombre, 'ot' => $ot, 'meta' => $meta, 'cNominal' => $cNominal, 'tolerancia' => $tolerancia, 'nPiezas' => $pzasCreadas, 'clase' => $clase, 'piezaElegida' => $pzaUtilizar, 'pzasRestantes' => $pzasRestantes]); //Retorno a vista de Primera operación soldadura.
                } else {
                    //Actualizar solo dos registros de las piezas que se van a ocupar en la tabla Primera operación soldadura
                    $this->piezaUtilizar($clase);
                }
            }
        } else if (isset($request->n_juegoElegido)) {
            $juegoExiste = PrimeraOpeSoldadura_pza::where('n_juego', $request->n_juegoElegido)->where('id_proceso', $id_proceso->id)->first();
            if (!$juegoExiste) {
                //Obtener el numero del juego para crear las piezas
                $numero = "";
                $juegoDividido = str_split($request->n_juegoElegido);
                for ($i = 0; $i < count($juegoDividido) - 1; $i++) {
                    $numero .= $juegoDividido[$i];
                }
                //For para crear las dos piezas del juego
                for ($i = 0; $i < 2; $i++) {
                    $newPza = new PrimeraOpeSoldadura_pza(); //Creación de objeto para llenar tabla Primera operación soldadura.
                    if ($i == 0) {
                        $newPza->id_pza = $numero . "M" . $id_proceso->id; //Creación de id para tabla Primera operación soldadura.
                        $newPza->n_pieza = $numero . "M";
                    } else {
                        $newPza->id_pza = $numero . "H" . $id_proceso->id; //Creación de id para tabla Primera operación soldadura.
                        $newPza->n_pieza = $numero . "H";
                    }
                    $newPza->id_meta = $meta->id; //Llenado de id_meta para tabla Primera operación soldadura.
                    $newPza->id_proceso = $id_proceso->id; //Llenado de id_proceso para tabla Primera operación soldadura.
                    $newPza->estado = 1; //Llenado de estado para tabla Primera operación soldadura.
                    $newPza->n_juego = $request->n_juegoElegido; //Llenado de estado para tabla Primera operación soldadura.
                    $newPza->save(); //Guardado de datos en la tabla Primera operación soldadura.
                }
            } else {
                $pieceFoundes = PrimeraOpeSoldadura_pza::where('n_juego', $request->n_juegoElegido)->where('id_proceso', $id_proceso->id)->get(); //Obtención de todas las piezas creadas.
                foreach ($pieceFoundes as $pieceFound) {
                    if ($pieceFound->estado == 0) {
                        $pieceFound->id_meta = $meta->id; //Llenado de id_meta para tabla Desbaste.
                        $pieceFound->id_proceso = $id_proceso->id; //Llenado de id_proceso para tabla Desbaste.
                        $pieceFound->estado = 1; //Llenado de estado para tabla Desbaste.
                        $pieceFound->save(); //Guardado de datos en la tabla Desbaste.
                    }
                }
            }
        }
        $id_proceso = PrimeraOpeSoldadura::where('id_proceso', $id)->first();
        if ($id_proceso !== "[]") {
            $pzasCreadas = PrimeraOpeSoldadura_pza::where('id_proceso', $id_proceso->id)->where('estado', 2)->where('id_meta', $meta->id)->get(); //Obtención de todas las piezas creadas.
            for ($i = 0; $i < count($pzasCreadas); $i++) { //Recorro las piezas creadas.
                //Acrualiza el estado correcto de la pieza.
                if ($this->compararDatosPieza($pzasCreadas[$i], $cNominal, $tolerancia) == 0 && ($pzasCreadas[$i]->error == 'Maquinado' || $pzasCreadas[$i]->error == 'Ninguno')) {
                    $pzasCreadas[$i]->error = 'Maquinado';
                    $pzasCreadas[$i]->correcto = 0;
                } else if (($this->compararDatosPieza($pzasCreadas[$i], $cNominal, $tolerancia) == 0 && $pzasCreadas[$i]->error == 'Fundicion') || ($this->compararDatosPieza($pzasCreadas[$i], $cNominal, $tolerancia) == 1 && $pzasCreadas[$i]->error == 'Fundicion')) {
                    $pzasCreadas[$i]->error = 'Fundicion';
                    $pzasCreadas[$i]->correcto = 0;
                } else {
                    $pzasCreadas[$i]->error = 'Ninguno';
                    $pzasCreadas[$i]->correcto = 1; //pieza correcto de la pieza.
                }
                $pzasCreadas[$i]->save();
            }

            //Actualizar resultado de la meta
            $contadorPzas = 0;
            $juegosUsados = array();
            $pzasCorrectas = PrimeraOpeSoldadura_pza::where('id_meta', $meta->id)->where('correcto', 1)->get(); //Obtención de todas las piezas correctas.
            foreach ($pzasCorrectas as $pzaCorrecta) {
                $pzaCorrecta2 = PrimeraOpeSoldadura_pza::where('n_juego', $pzaCorrecta->n_juego)->where('id_meta', $meta->id)->get();
                if (!in_array($pzaCorrecta->n_juego, $juegosUsados)) {
                    array_push($juegosUsados, $pzaCorrecta->n_juego);
                    $pzasMalas = 0;
                    foreach ($pzaCorrecta2 as $pza) {
                        if ($pza->correcto == 1) {
                            $contadorPzas += .5;
                        } else if ($pza->correcto === 0) {
                            $pzasMalas++;
                        }
                    }
                    if ($pzasMalas == 1) {
                        $contadorPzas -= .5;
                    }
                }
            }
            $meta = Metas::find($meta->id); //Actualización de datos en tabla Metas.
            $meta->resultado = $contadorPzas;
            $meta->save(); //Guardado de datos en la tabla Metas.

            if (isset($cNominal) && isset($tolerancia)) {
                $pzaUtilizar = PrimeraOpeSoldadura_pza::where('id_proceso', $id_proceso->id)->where('estado', 1)->where('id_meta', $meta->id)->first(); //Obtención de la pieza a utilizar.
                if ($pzaUtilizar == null) { //Si no existe una pieza para utilizar, se retorna a la vista de Primera operación soldadura 
                    $piezasVacias = PrimeraOpeSoldadura_pza::where('correcto', null)->where('estado', 1)->where('id_proceso', $id_proceso->id)->get();
                    if (isset($piezasVacias) && $piezasVacias->count() > 0) { //Si existen piezas vacias, se busca una pieza para utilizar.
                        for ($i = 0; $i < count($piezasVacias); $i++) { //Recorro las piezas creadas.
                            $metaAnterior = Metas::where('id', $piezasVacias[$i]->id_meta)->first(); //Obtención de la meta anterior.
                            if ($metaAnterior->maquina == $meta->maquina) { //Si la meta anterior es igual a la meta actual, se utiliza la pieza.
                                $piezasVacias[$i]->id_meta = $meta->id; //Llenado de id_meta para tabla Pza_Primera operación soldadura.
                                $piezasVacias[$i]->save(); //Guardado de datos en tabla Pza_Primera operación soldadura.
                                $pzaUtilizar = $piezasVacias[$i]; //Obtención de la pieza a utilizar.
                                $piezaEncontrada = true; //Se encontro una pieza para utilizar.
                                break; //Se rompe el ciclo.
                            } else {
                                $piezaEncontrada = false; //No se encontro una pieza para utilizar.
                            }
                        }
                    } else {
                        $piezaEncontrada = false; //No se encontro una pieza para utilizar.
                    }

                    if (!$piezaEncontrada) {
                        $pzasUtilizar = $this->piezaUtilizar($clase); //Llamado a función para obtener las piezas disponibles.
                    }
                }
                if (isset($pzasUtilizar)) { //Si no se encontro una pieza para utilizar, se crea una nueva pieza.
                    return view('processes.primeraOpeSoldadura', ['band' => 2, 'moldura' => $moldura->nombre, 'ot' => $ot, 'meta' => $meta, 'cNominal' => $cNominal, 'tolerancia' => $tolerancia, 'nPiezas' => $pzasCreadas, 'clase' => $clase, 'piezasUtilizar' => $pzasUtilizar, 'pzasRestantes' => $pzasRestantes]); //Retorno a vista de Primera operación soldadura.
                } else {
                    return view('processes.primeraOpeSoldadura', ['band' => 2, 'moldura' => $moldura->nombre, 'ot' => $ot, 'meta' => $meta, 'cNominal' => $cNominal, 'tolerancia' => $tolerancia, 'nPiezas' => $pzasCreadas, 'clase' => $clase, 'piezaElegida' => $pzaUtilizar, 'pzasRestantes' => $pzasRestantes])->with('success', 'Se han registrado todas las piezas correctamente'); //Retorno a vista de Primera operación soldadura.
                }
            }
        }
        return view('processes.primeraOpeSoldadura', ['band' => 2, 'moldura' => $moldura->nombre, 'ot' => $ot, 'meta' => $meta, 'cNominal' => $cNominal, 'tolerancia' => $tolerancia, 'clase' => $clase, 'pzasRestantes' => $pzasRestantes])->with('success', 'Se han registrado todas las piezas correctamente'); //Retorno a vista de Primera operación soldadura.
    }
    public function compararDatosPieza($pieza, $cNominal, $tolerancia) //Función para comparar los datos de la pieza con los datos nominales y de tolerancia.
    {
        if ($pieza->diametro1 > ($cNominal->diametro1 + $tolerancia->diametro1) || $pieza->diametro1 < ($cNominal->diametro1 - $tolerancia->diametro1) || $pieza->profundidad1 > ($cNominal->profundidad1 + $tolerancia->profundidad1) || $pieza->profundidad1 < ($cNominal->profundidad1 - $tolerancia->profundidad1) || $pieza->diametro2 > ($cNominal->diametro2 + $tolerancia->diametro2) || $pieza->diametro2 < ($cNominal->diametro2 - $tolerancia->diametro2) || $pieza->profundidad2 > ($cNominal->profundidad2 + $tolerancia->profundidad2) || $pieza->profundidad2 < ($cNominal->profundidad2 - $tolerancia->profundidad2) || $pieza->diametro3 > ($cNominal->diametro3 + $tolerancia->diametro3) || $pieza->diametro3 < ($cNominal->diametro3 - $tolerancia->diametro3) || $pieza->profundidad3  > ($cNominal->profundidad3  + $tolerancia->profundidad3) || $pieza->profundidad3 < ($cNominal->profundidad3 - $tolerancia->profundidad3) || $pieza->diametroSoldadura > ($cNominal->diametroSoldadura  + $tolerancia->diametroSoldadura) || $pieza->diametroSoldadura < ($cNominal->diametroSoldadura - $tolerancia->diametroSoldadura) || $pieza->profundidadSoldadura > ($cNominal->profundidadSoldadura + $tolerancia->profundidadSoldadura) || $pieza->profundidadSoldadura < ($cNominal->profundidadSoldadura - $tolerancia->profundidadSoldadura) || $pieza->diametroBarreno > ($cNominal->diametroBarreno + $tolerancia->diametroBarreno1) || $pieza->diametroBarreno < round(($cNominal->diametroBarreno - $tolerancia->diametroBarreno2), 3) || $pieza->simetriaLinea_partida > ($cNominal->simetriaLinea_partida + $tolerancia->simetriaLinea_partida1) || $pieza->simetriaLinea_partida < ($cNominal->simetriaLinea_partida - $tolerancia->simetriaLinea_partida2) || $pieza->pernoAlineacion > ($cNominal->pernoAlineacion + $tolerancia->pernoAlineacion) || $pieza->pernoAlineacion < ($cNominal->pernoAlineacion - $tolerancia->pernoAlineacion) || $pieza->Simetria90G > ($cNominal->Simetria90G + $tolerancia->Simetria90G) || $pieza->Simetria90G < ($cNominal->Simetria90G - $tolerancia->Simetria90G)) {
            return 0; //Si los datos de la pieza son diferentes a los nominales y de tolerancia, se retorna 0.
        } else {
            return 1; //Si los datos de la pieza son iguales a los nominales y de tolerancia, se retorna 1.
        }
    }
    public function piezasRestantes($clase)
    {
        //Obtener los juegos buenos de desbaste
        $desbastePieces =  Pieza::where('proceso', 'Desbaste Exterior')->where('id_clase', $clase->id)->where(function ($query) {
            $query->where(function ($q) {
                $q->where('error', 'Ninguno')
                    ->where('liberacion', 1);
            })->orWhere(function ($q) {
                $q->where('error', 'Maquinado')
                    ->where('liberacion', 1);
            })->orWhere(function ($q) {
                $q->where('error', 'Ninguno')
                    ->where('liberacion', 0);
            });
        })->get();

        $juegosDesbaste = array();
        foreach ($desbastePieces as $pieza) {
            $n_juego = substr($pieza->n_pieza, 0, -1);
            if (!in_array($n_juego, $juegosDesbaste)) {
                $malePiece = Pieza::where('n_pieza', $n_juego . 'M')->where('proceso', 'Desbaste Exterior')->where('id_clase', $clase->id)->where(function ($query) {
                    $query->where(function ($q) {
                        $q->where('error', 'Ninguno')
                            ->where('liberacion', 1);
                    })->orWhere(function ($q) {
                        $q->where('error', 'Maquinado')
                            ->where('liberacion', 1);
                    })->orWhere(function ($q) {
                        $q->where('error', 'Ninguno')
                            ->where('liberacion', 0);
                    });
                })->first();
                $femalePiece = Pieza::where('n_pieza', $n_juego . 'H')->where('proceso', 'Desbaste Exterior')->where('id_clase', $clase->id)->where(function ($query) {
                    $query->where(function ($q) {
                        $q->where('error', 'Ninguno')
                            ->where('liberacion', 1);
                    })->orWhere(function ($q) {
                        $q->where('error', 'Maquinado')
                            ->where('liberacion', 1);
                    })->orWhere(function ($q) {
                        $q->where('error', 'Ninguno')
                            ->where('liberacion', 0);
                    });
                })->first();

                if ($malePiece && $femalePiece) {
                    array_push($juegosDesbaste, $n_juego);
                }
            }
        }

        //Obtener las piezas de Revision Laterales
        $revisionPieces = Pieza::where("Proceso", "Revision Laterales")->where("id_clase", $clase->id)->get();

        foreach ($revisionPieces as $piece) {
            $n_juego = substr($piece->n_pieza, 0, -1);
            $malePiece = Pieza::where('n_pieza', $n_juego . 'M')->where('proceso', 'Revision Laterales')->where('id_clase', $clase->id)->where(function ($query) {
                $query->where(function ($q) {
                    $q->where('error', 'Ninguno')
                        ->where('liberacion', 1);
                })->orWhere(function ($q) {
                    $q->where('error', 'Maquinado')
                        ->where('liberacion', 1);
                })->orWhere(function ($q) {
                    $q->where('error', 'Ninguno')
                        ->where('liberacion', 0);
                });
            })->first();
            $femalePiece = Pieza::where('n_pieza', $n_juego . 'H')->where('proceso', 'Revision Laterales')->where('id_clase', $clase->id)->where(function ($query) {
                $query->where(function ($q) {
                    $q->where('error', 'Ninguno')
                        ->where('liberacion', 1);
                })->orWhere(function ($q) {
                    $q->where('error', 'Maquinado')
                        ->where('liberacion', 1);
                })->orWhere(function ($q) {
                    $q->where('error', 'Ninguno')
                        ->where('liberacion', 0);
                });
            })->first();
            if (!$malePiece || !$femalePiece) {
                if (in_array($n_juego, $juegosDesbaste)) {
                    //Eliminar elemento
                    $key = array_search($n_juego, $juegosDesbaste);
                    if ($key !== false) {
                        unset($juegosDesbaste[$key]);
                    }
                }
            } else {
                if (!in_array($n_juego, $juegosDesbaste)) {
                    array_push($juegosDesbaste, $n_juego);
                }
            }
        }


        //Obtener las piezas de Primera Operacion
        $pOperacionPieces = Pieza::where("Proceso", "Primera Operacion Soldadura")->where("id_clase", $clase->id)->get();
        foreach ($pOperacionPieces as $pieza) {
            $n_juego = substr($pieza->n_pieza, 0, -1);
            if (in_array($n_juego, $juegosDesbaste)) {
                //Eliminar elemento
                $key = array_search($n_juego, $juegosDesbaste);
                if ($key !== false) {
                    unset($juegosDesbaste[$key]);
                }
            }
        }
        return $juegosDesbaste;
    }
    public function edit(Request $request) //Función para editar los datos de la pieza.
    {
        $meta = Metas::find($request->metaData); //Busco la meta de la OT.
        $ot = Orden_trabajo::find($meta->id_ot); //Obtención de la OT.
        $moldura = Moldura::find($ot->id_moldura); //Busco la moldura de la OT.
        $clase = Clase::find($meta->id_clase); //Busco la clase de la OT.
        $id = "Primera_Operacion_" . $clase->nombre . "_" . $ot->id; //Creación de id para tabla Primera operación soldadura.
        $id_proceso = PrimeraOpeSoldadura::where('id_proceso', $id)->first();;
        $cNominal = PrimeraOpeSoldadura_cnominal::where('id_proceso', $id)->first(); //Busco la meta de la OT.
        $tolerancia = PrimeraOpeSoldadura_tolerancia::where('id_proceso', $id)->first(); //Busco la meta de la OT.
        $pzasCreadas = PrimeraOpeSoldadura_pza::where('id_proceso', $id_proceso->id)->where('estado', 2)->where('id_meta', $meta->id)->get(); //Obtención de todas las piezas creadas.
        if ($id_proceso) {
            $pzasRestantes = count($this->piezasRestantes($clase));
        } else {
            $pzasRestantes = 0;
        }
        $pzaUtilizar = PrimeraOpeSoldadura_pza::where('id_proceso', $id_proceso->id)->where('estado', 1)->where('id_meta', $meta->id)->first(); //Obtención de la pieza a utilizar.
        if (isset($request->n_pieza)) { //Si se obtienen los datos de las piezas, se guada
            for ($i = 0; $i < count($request->n_pieza); $i++) {
                $id_pieza = $request->n_pieza[$i] . $id_proceso->id; //Creación de id para tabla Primera operación soldadura_cnominal.
                $piezaExistente = PrimeraOpeSoldadura_pza::where('id_pza', $id_pieza)->first();
                if ($piezaExistente) {
                    $piezaExistente->diametro1 = $request->diametro1[$i];
                    $piezaExistente->profundidad1 = $request->profundidad1[$i];
                    $piezaExistente->diametro2 = $request->diametro2[$i];
                    $piezaExistente->profundidad2 = $request->profundidad2[$i];
                    $piezaExistente->diametro3 = $request->diametro3[$i];
                    $piezaExistente->profundidad3 = $request->profundidad3[$i];
                    $piezaExistente->diametroSoldadura = $request->diametroSoldadura[$i];
                    $piezaExistente->profundidadSoldadura = $request->profundidadSoldadura[$i];
                    $piezaExistente->diametroBarreno = $request->diametroBarreno[$i];
                    $piezaExistente->simetriaLinea_partida = $request->simetriaLinea_partida[$i];
                    $piezaExistente->pernoAlineacion = $request->pernoAlineacion[$i];
                    $piezaExistente->Simetria90G = $request->Simetria90G[$i];
                    if (isset($request->observaciones[$i])) { //Si se obtienen los datos de las piezas, se guardan en la tabla Primera operación soldadura_cnominal.
                        $piezaExistente->observaciones = $request->observaciones[$i];  //Llenado de observaciones para tabla Primera operación soldadura_cnominal.
                    }
                    $piezaExistente->save(); //Guardado de datos en la tabla Pza_Primera operación soldadura.

                    //Acrualiza el estado correcto de la pieza.
                    if ($this->compararDatosPieza($piezaExistente, $cNominal, $tolerancia) == 0 && ($request->error[$i] == "Ninguno" || $request->error[$i] == "Maquinado")) {
                        $piezaExistente->error = 'Maquinado';
                        $piezaExistente->correcto = 0;
                    } else if (($this->compararDatosPieza($piezaExistente, $cNominal, $tolerancia) == 0 && $request->error[$i] == 'Fundicion') || ($this->compararDatosPieza($piezaExistente, $cNominal, $tolerancia) == 1 && $request->error[$i] == 'Fundicion')) {
                        $piezaExistente->error = $request->error[$i];
                        $piezaExistente->correcto = 0;
                    } else {
                        $piezaExistente->error = 'Ninguno';
                        $piezaExistente->correcto = 1;
                    }
                    $piezaExistente->save();



                    $pieza = Pieza::where('n_pieza', $piezaExistente->n_pieza)->where('proceso', 'Primera Operacion Soldadura')->where('id_ot', $ot->id)->where('id_clase', $clase->id)->first();
                    //Guardar los datos de las pieza en la tabla pieza (En donde se almacenan todas las piezas)
                    if (!isset($pieza)) {
                        $pieza = new Pieza(); //Creación del obejeto para llenar la tabla pieza.
                    }
                    $pieza->id_clase = $clase->id; //Lenado de id_clase para la tabla pieza.
                    $pieza->id_ot = $ot->id;
                    $pieza->n_pieza = $piezaExistente->n_pieza;
                    $pieza->id_operador = $meta->id_usuario;
                    $pieza->maquina = $meta->maquina;
                    $pieza->proceso = "Primera Operacion Soldadura";
                    $pieza->error = $piezaExistente->error;
                    $pieza->save();
                    if ($pieza->error == 'Ninguno') {
                        //Obtener piezas de la meta
                        $piezasMeta = PrimeraOpeSoldadura_pza::where('id_meta', $meta->id)->get();
                        $this->controladorPzasLiberadas->liberarPiezasMeta($meta, $piezasMeta, $pieza->n_pieza, "Primera Operacion Soldadura");
                    }
                }
            }
            //Actualizar resultado de la meta
            $contadorPzas = 0;
            $juegosUsados = array();
            $pzasCorrectas = PrimeraOpeSoldadura_pza::where('id_meta', $meta->id)->where('correcto', 1)->get(); //Obtención de todas las piezas correctas.
            foreach ($pzasCorrectas as $pzaCorrecta) {
                $pzaCorrecta2 = PrimeraOpeSoldadura_pza::where('n_juego', $pzaCorrecta->n_juego)->where('id_meta', $meta->id)->get();
                if (!in_array($pzaCorrecta->n_juego, $juegosUsados)) {
                    array_push($juegosUsados, $pzaCorrecta->n_juego);
                    $pzasMalas = 0;
                    foreach ($pzaCorrecta2 as $pza) {
                        if ($pza->correcto == 1) {
                            $contadorPzas += .5;
                        } else if ($pza->correcto === 0) {
                            $pzasMalas++;
                        }
                    }
                    if ($pzasMalas == 1) {
                        $contadorPzas -= .5;
                    }
                }
            }
            $meta = Metas::find($meta->id); //Actualización de datos en tabla Metas.
            $meta->resultado = $contadorPzas;
            $meta->save(); //Guardado de datos en la tabla Metas.
            //Retornar la pieza siguiente
            $pzaUtilizar = PrimeraOpeSoldadura_pza::where('id_proceso', $id_proceso->id)->where('estado', 1)->where('id_meta', $meta->id)->first(); //Obtención de la pieza a utilizar.
            if ($id_proceso) {
                $pzasRestantes = count($this->piezasRestantes($clase));
            } else {
                $pzasRestantes = 0;
            }
            if ($pzaUtilizar == null) { //Si no existe una pieza para utilizar, se retorna a la vista de Primera operación soldadura 
                $piezasVacias = PrimeraOpeSoldadura_pza::where('correcto', null)->where('estado', 1)->where('id_proceso', $id_proceso->id)->get();
                if (isset($piezasVacias) && $piezasVacias->count() > 0) { //Si existen piezas vacias, se busca una pieza para utilizar.
                    for ($i = 0; $i < count($piezasVacias); $i++) { //Recorro las piezas creadas.
                        $metaAnterior = Metas::where('id', $piezasVacias[$i]->id_meta)->first(); //Obtención de la meta anterior.
                        if ($metaAnterior->maquina == $meta->maquina) { //Si la meta anterior es igual a la meta actual, se utiliza la pieza.
                            $piezasVacias[$i]->id_meta = $meta->id; //Llenado de id_meta para tabla Pza_Primera operación soldadura.
                            $piezasVacias[$i]->save(); //Guardado de datos en tabla Pza_Primera operación soldadura.
                            $pzaUtilizar = $piezasVacias[$i]; //Obtención de la pieza a utilizar.
                            $piezaEncontrada = true; //Se encontro una pieza para utilizar.
                            break; //Se rompe el ciclo.
                        } else {
                            $piezaEncontrada = false; //No se encontro una pieza para utilizar.
                        }
                    }
                } else {
                    $piezaEncontrada = false; //No se encontro una pieza para utilizar.
                }
                if (!$piezaEncontrada) { //Si no se encontro una pieza para utilizar, se crea una nueva pieza.
                    $pzasUtilizar = $this->piezaUtilizar($clase); //Llamado a función para obtener las piezas disponibles.
                }
            }
            $pzasCreadas = PrimeraOpeSoldadura_pza::where('id_proceso', $id_proceso->id)->where('estado', 2)->where('id_meta', $meta->id)->get(); //Obtención de todas las piezas creadas.
            $cNominal = PrimeraOpeSoldadura_cnominal::where('id_proceso', $id)->first(); //Busco la meta de la OT.
            $tolerancia = PrimeraOpeSoldadura_tolerancia::where('id_proceso', $id)->first(); //Busco la meta de la OT.
            if (isset($pzasUtilizar)) { //Si existe una pieza para utilizar, se retorna a la vista de Primera operación soldadura 
                return view('processes.primeraOpeSoldadura', ['band' => 2, 'moldura' => $moldura->nombre, 'ot' => $ot, 'meta' => $meta, 'cNominal' => $cNominal, 'tolerancia' => $tolerancia, 'nPiezas' => $pzasCreadas, 'clase' => $clase, 'piezasUtilizar' => $pzasUtilizar, 'pzasRestantes' => $pzasRestantes]); //Retorno a vista de Primera operación soldadura.
            } else { //Si no existe una pieza para utilizar, se retorna a la vista de Primera operación soldadura 
                $pzasUtilizar = $this->piezaUtilizar($clase); //Llamado a función para obtener las piezas disponibles.
                return view('processes.primeraOpeSoldadura', ['band' => 2, 'moldura' => $moldura->nombre, 'ot' => $ot, 'meta' => $meta, 'cNominal' => $cNominal, 'tolerancia' => $tolerancia, 'nPiezas' => $pzasCreadas, 'clase' => $clase, 'piezasUtilizar' => array(), 'piezaElegida' => $pzaUtilizar, 'pzasRestantes' => $pzasRestantes])->with('success', 'Se han registrado todas las piezas correctamente'); //Retorno a vista de Primera operación soldadura.
            }
        } else {
            if (isset($request->password)) { //Si se ingreso una contraseña y la meta existe entonces...
                $usersPasswords = User::all(); //Obtengo todas las contraseñas.
                foreach ($usersPasswords as $userPassword) { //Recorro las contraseñas.
                    if (Hash::check($request->password, $userPassword->contrasena) && $userPassword->perfil == 1) {  //Si la contraseña es correcta.
                        return view('processes.primeraOpeSoldadura', ['band' => 4, 'moldura' => $moldura->nombre, 'ot' => $ot, 'meta' => $meta, 'clase' => $clase, 'cNominal' => $cNominal, 'tolerancia' => $tolerancia, 'nPiezas' => $pzasCreadas, 'pzasRestantes' => $pzasRestantes]); //Retorno la vista de Primera operación soldadura.
                    }
                }
            }
            $pzaUtilizar = PrimeraOpeSoldadura_pza::where('id_proceso', $id_proceso->id)->where('estado', 1)->where('id_meta', $meta->id)->first(); //Obtención de la pieza a utilizar.
            if ($pzaUtilizar == null) { //Si no existe una pieza para utilizar, se retorna a la vista de Primera operación soldadura 
                $piezasVacias = PrimeraOpeSoldadura_pza::where('correcto', null)->where('estado', 1)->where('id_proceso', $id_proceso->id)->get();
                if (isset($piezasVacias) && $piezasVacias->count() > 0) { //Si existen piezas vacias, se busca una pieza para utilizar.
                    for ($i = 0; $i < count($piezasVacias); $i++) { //Recorro las piezas creadas.
                        $metaAnterior = Metas::where('id', $piezasVacias[$i]->id_meta)->first(); //Obtención de la meta anterior.
                        if ($metaAnterior->maquina == $meta->maquina) { //Si la meta anterior es igual a la meta actual, se utiliza la pieza.
                            $piezasVacias[$i]->id_meta = $meta->id; //Llenado de id_meta para tabla Pza_Primera operación soldadura.
                            $piezasVacias[$i]->save(); //Guardado de datos en tabla Pza_Primera operación soldadura.
                            $pzaUtilizar = $piezasVacias[$i]; //Obtención de la pieza a utilizar.
                            $piezaEncontrada = true; //Se encontro una pieza para utilizar.
                            break; //Se rompe el ciclo.
                        } else {
                            $piezaEncontrada = false; //No se encontro una pieza para utilizar.
                        }
                    }
                } else {
                    $piezaEncontrada = false; //No se encontro una pieza para utilizar.
                }
                if (!$piezaEncontrada) { //Si no se encontro una pieza para utilizar, se crea una nueva pieza.
                    $pzasUtilizar = $this->piezaUtilizar($clase); //Llamado a función para obtener las piezas disponibles.
                }
            }
            $pzasCreadas = PrimeraOpeSoldadura_pza::where('id_proceso', $id_proceso->id)->where('estado', 2)->where('id_meta', $meta->id)->get(); //Obtención de todas las piezas creadas.
            $cNominal = PrimeraOpeSoldadura_cnominal::where('id_proceso', $id)->first(); //Busco la meta de la OT.
            $tolerancia = PrimeraOpeSoldadura_tolerancia::where('id_proceso', $id)->first(); //Busco la meta de la OT.
            if (isset($pzasUtilizar)) { //Si existe una pieza para utilizar, se retorna a la vista de Primera operación soldadura
                return view('processes.primeraOpeSoldadura', ['band' => 2, 'moldura' => $moldura->nombre, 'ot' => $ot, 'meta' => $meta, 'cNominal' => $cNominal, 'tolerancia' => $tolerancia, 'nPiezas' => $pzasCreadas, 'clase' => $clase, 'piezasUtilizar' => $pzasUtilizar, 'pzasRestantes' => $pzasRestantes]); //Retorno a vista de Primera operación soldadura.
            } else { //Si no existe una pieza para utilizar, se retorna a la vista de primera operación soldadura.
                $pzasUtilizar = $this->piezaUtilizar($clase); //Llamado a función para obtener las piezas disponibles.
                return view('processes.primeraOpeSoldadura', ['band' => 2, 'moldura' => $moldura->nombre, 'ot' => $ot, 'meta' => $meta, 'cNominal' => $cNominal, 'tolerancia' => $tolerancia, 'nPiezas' => $pzasCreadas, 'clase' => $clase, 'piezasUtilizar' => array(), 'piezaElegida' => $pzaUtilizar, 'pzasRestantes' => $pzasRestantes])->with('success', 'Se han registrado todas las piezas correctamente'); //Retorno a vista de Primera operación soldadura.
            }
        }
    }

    public function piezaUtilizar($clase) //Función para obtener la pieza a utilizar.
    {
        $piezasUtilizar = array();
        $piezasRestantes = $this->piezasRestantes($clase); //Obtención de las piezas restantes.
        foreach ($piezasRestantes as $pieza) {
            array_push($piezasUtilizar, $pieza . "J"); //Llenado de array con las piezas a utilizar.
        }
        return $piezasUtilizar;
    }
    public function compararPiezas($pzasDesbaste, $pzasRevision)
    {
        $pzasUtilizar = array();
        foreach ($pzasDesbaste as $pza) {
            foreach ($pzasRevision as $pza2) {
                if ($pza == $pza2) {
                    array_push($pzasUtilizar, $pza);
                }
            }
        }
        return $pzasUtilizar;
    }
    public function piezasEncontradas($ot, $clase, $pzasEncontradas, &$pzasUtilizar, $pzasGuardadas, $nameProceso, $pzasUsadas, $pzasOcupadas)
    {
        $numero = "";
        for ($i = 0; $i < count($pzasEncontradas); $i++) { //Recorro las piezas encontradas de Desbaste.
            if (array_search($pzasEncontradas[$i]->n_pieza, $pzasGuardadas) == false) {
                $pzaBien = Pieza::where('id_ot', $clase->id_ot)->where('id_clase', $clase->id)->where('proceso', $nameProceso)->where('n_pieza', $pzasEncontradas[$i]->n_pieza)->where(function ($query) {
                    $query->where(function ($q) {
                        $q->where('error', 'Ninguno')
                            ->where('liberacion', 1);
                    })->orWhere(function ($q) {
                        $q->where('error', 'Maquinado')
                            ->where('liberacion', 1);
                    })->orWhere(function ($q) {
                        $q->where('error', 'Ninguno')
                            ->where('liberacion', 0);
                    });
                })->first(); //Obtención de todas las piezas creadas.
                if ($pzaBien) {
                    $numerosUsados = array();
                    if (count($pzasUsadas) > 0) {
                        $numeroUsado = "";
                        for ($x = 0; $x < count($pzasUsadas); $x++) {
                            $pzaDividida_Usada = str_split($pzasUsadas[$x]->n_pieza); //División del número de pieza usada.
                            for ($h = 0; $h < count($pzaDividida_Usada) - 1; $h++) { //Recorro el número de pieza usada.
                                $numeroUsado .= $pzaDividida_Usada[$h]; //Obtención del número de pieza usada.
                            }
                            array_push($numerosUsados, $numeroUsado); //Guardo el número de pieza usada.
                            $numeroUsado = ""; //Reinicio la variable.
                        } //Recorro las piezas ocupadas en Desbaste
                    }
                    if (count($pzasOcupadas) > 0) {
                        $numeroUsado = ""; //Reinicio la variable.
                        for ($x = 0; $x < count($pzasOcupadas); $x++) {
                            $n_piezaUsada = $pzasOcupadas[$x]->n_pieza; //Obtención del número de pieza ocupada
                            $pzaDividida_Usada = str_split($n_piezaUsada);
                            for ($h = 0; $h < count($pzaDividida_Usada) - 1; $h++) {
                                $numeroUsado .= $pzaDividida_Usada[$h];
                            }
                            array_push($numerosUsados, $numeroUsado);
                            $numeroUsado = "";
                        }
                    }
                    $n_pieza = $pzasEncontradas[$i]->n_pieza; //Obtención del número de pieza.
                    $piezaDividida = str_split($n_pieza); //División del número de pieza.
                    for ($j = 0; $j < count($piezaDividida) - 1; $j++) { //Recorro el número de pieza.
                        $numero .= $piezaDividida[$j]; //Obtención del número de pieza.
                    }
                    //Se hace la condicion para saber si el número de la pieza se encuentra ya usada.
                    if (array_search($numero, $numerosUsados) === false) {
                        if (mb_substr($n_pieza, -1, null, 'UTF-8') == "M") { //Si la pieza es macho, se busca la pieza hembra.
                            $pieza = Pieza::where('id_ot', $ot)->where('id_clase', $clase->id)->where('proceso', $nameProceso)->where('n_pieza', $numero . "H")->first(); //Busco la pieza hembra.
                        } else {
                            $pieza = Pieza::where('id_ot', $ot)->where('id_clase', $clase->id)->where('proceso', $nameProceso)->where('n_pieza', $numero . "M")->first(); //Busco la pieza macho.
                        }
                        if (isset($pieza)) {
                            array_push($pzasUtilizar, $numero . "J"); //Guardo el número de pieza.
                            array_push($pzasGuardadas, $pzasEncontradas[$i]->n_pieza); //Guardo el número de pieza.
                            array_push($pzasGuardadas, $pieza->n_pieza); //Guardo el número de pieza.
                        }
                    }
                    $numero = "";
                } else {
                    $numeroDiv = str_split($pzasEncontradas[$i]->n_pieza);
                    for ($l = 0; $l < count($numeroDiv) - 1; $l++) {
                        $numero .= $numeroDiv[$l];
                    }
                    array_push($pzasGuardadas, $numero . "H");
                    array_push($pzasGuardadas, $numero . "M");
                    $numero = "";
                }
            }
        }
    }
}
