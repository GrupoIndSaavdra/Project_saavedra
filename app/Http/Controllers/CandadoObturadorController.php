<?php

namespace App\Http\Controllers;

use App\Models\CandadoObturador_pza;
use Illuminate\Http\Request;

class CandadoObturadorController extends Controller
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
    public function storePiece($request, $cNominal, $tolerance, $index = null)
    {
        if ($index !== null) {
            $pieceId = $request->piece[$index] ?? null;
            if (!$pieceId)
                return;
            $piece = CandadoObturador_pza::query()->find($pieceId);

            // Crear arreglo de datos por índice
            $fields = [
                'altura',
                'alturaCandado1',
                'alturaCandado2',
                'alturaAsientoObturador1',
                'alturaAsientoObturador2',
                'profundidadSoldadura1',
                'profundidadSoldadura2',
                'pushUp',
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
            $piece = CandadoObturador_pza::query()->find($pieceId);
            //Guardar los datos de la pieza
            $piece->fill($request->only([
                'altura',
                'alturaCandado1',
                'alturaCandado2',
                'alturaAsientoObturador1',
                'alturaAsientoObturador2',
                'profundidadSoldadura1',
                'profundidadSoldadura2',
                'pushUp',
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
     * @param mixed $tolerance
     */
    public function comparePieceData($pieza, $cNominal, $tolerance)
    {
        $fields = [
            'altura',
            'alturaCandado1',
            'alturaCandado2',
            'alturaAsientoObturador1',
            'alturaAsientoObturador2',
            'profundidadSoldadura1',
            'profundidadSoldadura2',
            'pushUp',
        ];

        $epsilon = 0.000001; // tolerancia mínima para errores de redondeo

        foreach ($fields as $field) {
            $valorPiece = (float) $pieza->$field;
            $nominal = (float) $cNominal->$field;
            $tol = (float) $tolerance->{$field};

            $min = $nominal - $tol;
            $max = $nominal + $tol;

            // Compara con epsilon para evitar falsos positivos por decimales
            if ($valorPiece > $max + $epsilon || $valorPiece < $min - $epsilon) {
                return 0;
            }
        }

        return 1;
    }
}
