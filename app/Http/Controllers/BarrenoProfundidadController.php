<?php

namespace App\Http\Controllers;

use App\Models\BarrenoProfundidad_pza;

class BarrenoProfundidadController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }
    public function storePiece($request, $cNominal, $tolerance, $index)
    {
        if ($index !== null) {
            $pieceId = $request->piece[$index] ?? null;
            if (!$pieceId)
                return;
            $piece = BarrenoProfundidad_pza::find($pieceId);

            // Crear arreglo de datos por índice
            $fields = [
                'broca1',
                'tiempo1',
                'broca2',
                'tiempo2',
                'broca3',
                'tiempo3',
                'entrada',
                'salida',
                'diametro_arrastre1',
                'diametro_arrastre2',
                'diametro_arrastre3',
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
            $piece = BarrenoProfundidad_pza::find($pieceId);
            //Guardar los datos de la pieza
            $piece->fill($request->only([
                'broca1',
                'tiempo1',
                'broca2',
                'tiempo2',
                'broca3',
                'tiempo3',
                'entrada',
                'salida',
                'diametro_arrastre1',
                'diametro_arrastre2',
                'diametro_arrastre3',
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
    public function comparePieceData($pieza, $cNominal, $tolerancia) //Función para comparar los datos de la pieza con los datos nominales y de tolerancia.
    {
        if ($pieza->broca1 > ($cNominal->broca1 + $tolerancia->broca1) || $pieza->broca1 < ($cNominal->broca1 - $tolerancia->broca1) || $pieza->tiempo1 > ($cNominal->tiempo1 + $tolerancia->tiempo1) || $pieza->tiempo1 < ($cNominal->tiempo1 - $tolerancia->tiempo1) || $pieza->broca2 > ($cNominal->broca2 + $tolerancia->broca2) || $pieza->broca2 < ($cNominal->broca2 - $tolerancia->broca2) || $pieza->tiempo2 > ($cNominal->tiempo2 + $tolerancia->tiempo2) || $pieza->tiempo2 < ($cNominal->tiempo2 - $tolerancia->tiempo2) || $pieza->broca3 > ($cNominal->broca3 + $tolerancia->broca3) || $pieza->broca3 < ($cNominal->broca3 - $tolerancia->broca3) || $pieza->tiempo3 > ($cNominal->tiempo3 + $tolerancia->tiempo3) || $pieza->tiempo3 < ($cNominal->tiempo3 - $tolerancia->tiempo3) || $pieza->entrada > ($cNominal->entradaSalida + $tolerancia->entrada) || $pieza->entrada < ($cNominal->entradaSalida - $tolerancia->salida) || $pieza->salida > ($cNominal->entradaSalida + $tolerancia->entrada) || $pieza->salida < ($cNominal->entradaSalida - $tolerancia->salida) || $pieza->diametro_arrastre1 > ($cNominal->diametro_arrastre1 + $tolerancia->diametro_arrastre1) || $pieza->diametro_arrastre1 < ($cNominal->diametro_arrastre1 - $tolerancia->diametro_arrastre1) || $pieza->diametro_arrastre2 > ($cNominal->diametro_arrastre2 + $tolerancia->diametro_arrastre2) || $pieza->diametro_arrastre2 < ($cNominal->diametro_arrastre2 - $tolerancia->diametro_arrastre2) || $pieza->diametro_arrastre3 > ($cNominal->diametro_arrastre3 + $tolerancia->diametro_arrastre3) || $pieza->diametro_arrastre3 < ($cNominal->diametro_arrastre3 - $tolerancia->diametro_arrastre3)) {
            return 0; //Si los datos de la pieza son diferentes a los nominales y de tolerancia, se retorna 0.
        } else {
            return 1; //Si los datos de la pieza son iguales a los nominales y de tolerancia, se retorna 1.
        }
    }
}
