<?php

namespace App\Http\Controllers;

use App\Models\RevLaterales_pza;

class RevLateralesController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }
    public function storePiece($request, $cNominal, $tolerance)
    {
        $piece = RevLaterales_pza::find($request->piece);
        //Guardar los datos de la pieza
        $piece->fill($request->only([
            'desfasamiento_entrada',
            'desfasamiento_salida',
            'ancho_simetriaEntrada',
            'ancho_simetriaSalida',
            'angulo_corte',
            'observaciones',
        ]));
        $piece->estado = 2;

        //Verificar si las medidas de la pieza estan correctas
        $correct = $this->comparePieceData($piece, $cNominal, $tolerance);
        if ($correct == 0 && $request->error == "Ninguno") {
            $piece->error = 'Maquinado';
            $piece->correcto = 0;
        } else if (($correct == 0 && $request->error == 'Fundicion') || ($correct == 1 && $request->error == 'Fundicion')) {
            $piece->error = $request->error;
            $piece->correcto = 0;
        } else {
            $piece->error = 'Ninguno';
            $piece->correcto = 1;
        }
        $piece->save();
    }
    public function comparePieceData($pieza, $cNominal, $tolerancia) //Función para comparar los datos de la pieza con los datos nominales y de tolerancia.
    {
        if ($pieza->desfasamiento_entrada > ($cNominal->desfasamiento_entrada + $tolerancia->desfasamiento_entrada1) || $pieza->desfasamiento_entrada < ($cNominal->desfasamiento_entrada - $tolerancia->desfasamiento_entrada2) || $pieza->desfasamiento_salida > ($cNominal->desfasamiento_salida + $tolerancia->desfasamiento_salida1) || $pieza->desfasamiento_salida < ($cNominal->desfasamiento_salida - $tolerancia->desfasamiento_salida2) || $pieza->ancho_simetriaEntrada > ($cNominal->ancho_simetriaEntrada + $tolerancia->ancho_simetriaEntrada1) || $pieza->ancho_simetriaEntrada < ($cNominal->ancho_simetriaEntrada - $tolerancia->ancho_simetriaEntrada2) || $pieza->ancho_simetriaSalida > ($cNominal->ancho_simetriaSalida + $tolerancia->ancho_simetriaSalida1) || $pieza->ancho_simetriaSalida < ($cNominal->ancho_simetriaSalida - $tolerancia->ancho_simetriaSalida2) || $pieza->angulo_corte  > ($cNominal->angulo_corte  + $tolerancia->angulo_corte1) || $pieza->angulo_corte < ($cNominal->angulo_corte - $tolerancia->angulo_corte2)) {
            return 0; //Si los datos de la pieza son diferentes a los nominales y de tolerancia, se retorna 0.
        } else {
            return 1; //Si los datos de la pieza son iguales a los nominales y de tolerancia, se retorna 1.
        }
    }
}
