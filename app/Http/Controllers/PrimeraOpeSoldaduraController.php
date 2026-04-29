<?php

namespace App\Http\Controllers;

use App\Models\PrimeraOpeSoldadura_pza;

class PrimeraOpeSoldaduraController extends Controller
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
            $piece = PrimeraOpeSoldadura_pza::query()->find($pieceId);

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
                'diametroBarreno',
                'simetriaLinea_partida',
                'pernoAlineacion',
                'Simetria90G',
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
            $piece = PrimeraOpeSoldadura_pza::query()->find($pieceId);
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
                'diametroBarreno',
                'simetriaLinea_partida',
                'pernoAlineacion',
                'Simetria90G',
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
        if ($pieza->diametro1 > ($cNominal->diametro1 + $tolerancia->diametro1) || $pieza->diametro1 < ($cNominal->diametro1 - $tolerancia->diametro1) || $pieza->profundidad1 > ($cNominal->profundidad1 + $tolerancia->profundidad1) || $pieza->profundidad1 < ($cNominal->profundidad1 - $tolerancia->profundidad1) || $pieza->diametro2 > ($cNominal->diametro2 + $tolerancia->diametro2) || $pieza->diametro2 < ($cNominal->diametro2 - $tolerancia->diametro2) || $pieza->profundidad2 > ($cNominal->profundidad2 + $tolerancia->profundidad2) || $pieza->profundidad2 < ($cNominal->profundidad2 - $tolerancia->profundidad2) || $pieza->diametro3 > ($cNominal->diametro3 + $tolerancia->diametro3) || $pieza->diametro3 < ($cNominal->diametro3 - $tolerancia->diametro3) || $pieza->profundidad3 > ($cNominal->profundidad3 + $tolerancia->profundidad3) || $pieza->profundidad3 < ($cNominal->profundidad3 - $tolerancia->profundidad3) || $pieza->diametroSoldadura > ($cNominal->diametroSoldadura + $tolerancia->diametroSoldadura) || $pieza->diametroSoldadura < ($cNominal->diametroSoldadura - $tolerancia->diametroSoldadura) || $pieza->profundidadSoldadura > ($cNominal->profundidadSoldadura + $tolerancia->profundidadSoldadura) || $pieza->profundidadSoldadura < ($cNominal->profundidadSoldadura - $tolerancia->profundidadSoldadura) || $pieza->diametroBarreno > ($cNominal->diametroBarreno + $tolerancia->diametroBarreno1) || $pieza->diametroBarreno < round(($cNominal->diametroBarreno - $tolerancia->diametroBarreno2), 3) || $pieza->simetriaLinea_partida > ($cNominal->simetriaLinea_partida + $tolerancia->simetriaLinea_partida1) || $pieza->simetriaLinea_partida < ($cNominal->simetriaLinea_partida - $tolerancia->simetriaLinea_partida2) || $pieza->pernoAlineacion > ($cNominal->pernoAlineacion + $tolerancia->pernoAlineacion) || $pieza->pernoAlineacion < ($cNominal->pernoAlineacion - $tolerancia->pernoAlineacion) || $pieza->Simetria90G > ($cNominal->Simetria90G + $tolerancia->Simetria90G) || $pieza->Simetria90G < ($cNominal->Simetria90G - $tolerancia->Simetria90G)) {
            return 0; //Si los datos de la pieza son diferentes a los nominales y de tolerancia, se retorna 0.
        } else {
            return 1; //Si los datos de la pieza son iguales a los nominales y de tolerancia, se retorna 1.
        }
    }
}
