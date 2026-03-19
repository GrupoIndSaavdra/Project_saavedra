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
            $pieceId = $request->piece[$index] ?? null;
            if (!$pieceId)
                return;
            $piece = SegundaOpeSoldadura_pza::find($pieceId);

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
            $error = $request->error[$index] ?? 'Ninguno';
        } else {
            $pieceId = $request->piece;
            if (!$pieceId)
                return;
            $piece = SegundaOpeSoldadura_pza::find($pieceId);
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
        $camposSimples = [
            'diametro1',
            'profundidad1',
            'diametro2',
            'profundidad2',
            'diametro3',
            'profundidad3',
            'diametroSoldadura',
            'profundidadSoldadura',
            'simetriaLinea_Partida',
        ];

        $epsilon = 0.000001; // tolerancia mínima para errores de redondeo

        // 1️⃣ Comparar los campos "simples" (± tolerancia)
        foreach ($camposSimples as $campo) {
            $valorPieza = $pieza->$campo;
            $valorNominal = $cNominal->$campo;
            $valorTolerancia = $tolerancia->$campo;

            if (
                $valorPieza > ($valorNominal + $valorTolerancia + $epsilon) ||
                $valorPieza < ($valorNominal - $valorTolerancia - $epsilon)
            ) {
                return 0;
            }
        }

        // 2️⃣ Comparar campos con tolerancias diferentes arriba y abajo
        $camposEspeciales = [
            'alturaTotal' => ['min' => 'alturaTotal1', 'max' => 'alturaTotal2'],
            'simetria90G' => ['min' => 'simetria90G1', 'max' => 'simetria90G2'],
        ];

        foreach ($camposEspeciales as $campo => $rangos) {
            $valorPieza = $pieza->$campo;
            $valorNominal = $cNominal->$campo;
            $tolMin = $tolerancia->{$rangos['min']};
            $tolMax = $tolerancia->{$rangos['max']};

            if (
                $valorPieza < ($valorNominal - $tolMin - $epsilon) ||
                $valorPieza > ($valorNominal + $tolMax + $epsilon)
            ) {
                return 0;
            }
        }

        // Si todos los valores están dentro del rango permitido
        return 1;
    }
}
