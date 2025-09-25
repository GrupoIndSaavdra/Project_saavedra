<?php

namespace App\Http\Controllers;

use App\Models\SegundaOpeSoldadura_pza;

class SegundaOpeSoldaduraController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }
    public function storePiece($request, $cNominal, $tolerance, $index)
    {
        if ($index !== null) {
            $piece = SegundaOpeSoldadura_pza::find($request->piece[$index]);

            // Crear arreglo de datos por índice
            $fields = [
                'diametro1',
                'profundidad1',
                'diametro2',
                'profundidad2',
                'diametro3',
                'profundidad3',
                'diametroSoldadura',
                'profundidadSoldadura',
                'alturaTotal',
                'simetria90G',
                'simetriaLinea_Partida',
                'observaciones',
            ];

            $data = array();
            foreach ($fields as $field) {
                $data[$field] = $request->$field[$index] ?? null;
            }
            $piece->fill($data);
            $error = $request->error[$index];
        } else {
            $piece = SegundaOpeSoldadura_pza::find($request->piece);
            //Guardar los datos de la pieza
            $piece->fill($request->only([
                'diametro1',
                'profundidad1',
                'diametro2',
                'profundidad2',
                'diametro3',
                'profundidad3',
                'diametroSoldadura',
                'profundidadSoldadura',
                'alturaTotal',
                'simetria90G',
                'simetriaLinea_Partida',
                'observaciones',
            ]));
            $error = $request->error;
        }
        $piece->estado = 2;

        //Verificar si las medidas de la pieza estan correctas
        $correct = $this->comparePieceData($piece, $cNominal, $tolerance);
        if ($correct == 0 && $error == "Ninguno") {
            $piece->error = 'Maquinado';
            $piece->correcto = 0;
        } else if (($correct == 0 && $error == 'Fundicion') || ($correct == 1 && $error == 'Fundicion')) {
            $piece->error = $error;
            $piece->correcto = 0;
        } else {
            $piece->error = 'Ninguno';
            $piece->correcto = 1;
        }
        $piece->save();
    }

    public function comparePieceData($pieza, $cNominal, $tolerancia) //Función para comparar los datos de la pieza con los datos nominales y de tolerancia.
    {
        if ($pieza->diametro1 > ($cNominal->diametro1 + $tolerancia->diametro1) || $pieza->diametro1 < ($cNominal->diametro1 - $tolerancia->diametro1) || $pieza->profundidad1 > ($cNominal->profundidad1 + $tolerancia->profundidad1) || $pieza->profundidad1 < ($cNominal->profundidad1 - $tolerancia->profundidad1) || $pieza->diametro2 > ($cNominal->diametro2 + $tolerancia->diametro2) || $pieza->diametro2 < ($cNominal->diametro2 - $tolerancia->diametro2) || $pieza->profundidad2 > ($cNominal->profundidad2 + $tolerancia->profundidad2) || $pieza->profundidad2 < ($cNominal->profundidad2 - $tolerancia->profundidad2) || $pieza->diametro3 > ($cNominal->diametro3 + $tolerancia->diametro3) || $pieza->diametro3 < ($cNominal->diametro3 - $tolerancia->diametro3) || $pieza->profundidad3  > ($cNominal->profundidad3  + $tolerancia->profundidad3) || $pieza->profundidad3 < ($cNominal->profundidad3 - $tolerancia->profundidad3) || $pieza->diametroSoldadura > ($cNominal->diametroSoldadura  + $tolerancia->diametroSoldadura) || $pieza->diametroSoldadura < ($cNominal->diametroSoldadura - $tolerancia->diametroSoldadura) || $pieza->profundidadSoldadura > ($cNominal->profundidadSoldadura + $tolerancia->profundidadSoldadura) || $pieza->profundidadSoldadura < ($cNominal->profundidadSoldadura - $tolerancia->profundidadSoldadura) || $pieza->alturaTotal < ($cNominal->alturaTotal - $tolerancia->alturaTotal1) || $pieza->alturaTotal > ($cNominal->alturaTotal + $tolerancia->alturaTotal2) || $pieza->simetria90G > ($cNominal->simetria90G + $tolerancia->simetria90G2) || $pieza->simetria90G < ($cNominal->simetria90G - $tolerancia->simetria90G1) || $pieza->simetriaLinea_Partida < ($cNominal->simetriaLinea_Partida - $tolerancia->simetriaLinea_Partida) || $pieza->simetriaLinea_Partida > ($cNominal->simetriaLinea_Partida + $tolerancia->simetriaLinea_Partida)) {
            return 0; //Si los datos de la pieza son diferentes a los nominales y de tolerancia, se retorna 0.
        } else {
            return 1; //Si los datos de la pieza son iguales a los nominales y de tolerancia, se retorna 1.
        }
    }
}
