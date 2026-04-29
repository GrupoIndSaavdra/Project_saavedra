<?php

namespace App\Http\Controllers;

use App\Models\OffSet_pza;

class OffSetController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth');
    }
        /**
     * @param mixed $request
     * @param mixed $cNominal
     * @param mixed $tolerance
     * @param int $index
     */
    public function storePiece($request, $cNominal, $tolerance, $index)
    {
        if ($index !== null) {
            $pieceId = $request->piece[$index] ?? null;
            if (!$pieceId)
                return;
            $piece = OffSet_pza::query()->find($pieceId);

            // Crear arreglo de datos por índice
            $fields = [
                'anchoRanura',
                'profuTaconHembra',
                'profuTaconMacho',
                'simetriaHembra',
                'simetriaMacho',
                'anchoTacon',
                'barrenoLateralHembra',
                'barrenoLateralMacho',
                'alturaTaconInicial',
                'alturaTaconIntermedia',
                'observaciones',
            ];

            $data = array();
            foreach ($fields as $field) {
                $data[$field] = $request->$field[$index] ?? null;
            }
            $piece->fill($data);
            $error = $request->error[$index] ?? 'Ninguno';
        } else {
            $pieceId = $request->piece;
            if (!$pieceId)
                return;
            $piece = OffSet_pza::query()->find($pieceId);
            //Guardar los datos de la pieza
            $piece->fill($request->only([
                'anchoRanura',
                'profuTaconHembra',
                'profuTaconMacho',
                'simetriaHembra',
                'simetriaMacho',
                'anchoTacon',
                'barrenoLateralHembra',
                'barrenoLateralMacho',
                'alturaTaconInicial',
                'alturaTaconIntermedia',
                'observaciones',
            ]));
            $error = $request->error ?? 'Ninguno';
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
        /**
     * @param mixed $pieza
     * @param mixed $cNominal
     * @param mixed $tolerancia
     */
    public function comparePieceData($pieza, $cNominal, $tolerancia) //Función para comparar los datos de la pieza con los datos nominales y de tolerancia.
    {
        if ($pieza->anchoRanura > ($cNominal->anchoRanura + $tolerancia->anchoRanura) || $pieza->anchoRanura < ($cNominal->anchoRanura - $tolerancia->anchoRanura) || $pieza->profuTaconHembra > ($cNominal->profuTaconHembra + $tolerancia->profuTaconHembra) || $pieza->profuTaconHembra < ($cNominal->profuTaconHembra - $tolerancia->profuTaconHembra) || $pieza->profuTaconMacho > ($cNominal->profuTaconMacho + $tolerancia->profuTaconMacho) || $pieza->profuTaconMacho < ($cNominal->profuTaconMacho - $tolerancia->profuTaconMacho) || $pieza->simetriaHembra > ($cNominal->simetriaHembra + $tolerancia->simetriaHembra) || $pieza->simetriaHembra < ($cNominal->simetriaHembra - $tolerancia->simetriaHembra) || $pieza->simetriaMacho > ($cNominal->simetriaMacho + $tolerancia->simetriaMacho) || $pieza->simetriaMacho < ($cNominal->simetriaMacho - $tolerancia->simetriaMacho) || $pieza->anchoTacon > ($cNominal->anchoTacon + $tolerancia->anchoTacon) || $pieza->anchoTacon < ($cNominal->anchoTacon - $tolerancia->anchoTacon) || $pieza->barrenoLateralHembra > ($cNominal->barrenoLateralHembra + $tolerancia->barrenoLateralHembra) || $pieza->barrenoLateralHembra < ($cNominal->barrenoLateralHembra - $tolerancia->barrenoLateralHembra) || $pieza->barrenoLateralMacho > ($cNominal->barrenoLateralMacho + $tolerancia->barrenoLateralMacho) || $pieza->barrenoLateralMacho < ($cNominal->barrenoLateralMacho - $tolerancia->barrenoLateralMacho) || $pieza->alturaTaconInicial > ($cNominal->alturaTaconInicial + $tolerancia->alturaTaconInicial) || $pieza->alturaTaconInicial < ($cNominal->alturaTaconInicial - $tolerancia->alturaTaconInicial) || $pieza->alturaTaconIntermedia > ($cNominal->alturaTaconIntermedia + $tolerancia->alturaTaconIntermedia) || $pieza->alturaTaconIntermedia < ($cNominal->alturaTaconIntermedia - $tolerancia->alturaTaconIntermedia)) {
            return 0; //Retorno de datos. //Si los datos de la pieza son diferentes a los nominales y de tolerancia, se retorna 0.
        }
        return 1;
    }
}
