<?php

namespace App\Http\Controllers;

use App\Models\PrimeraOperacionCabezaSoplo_pza;

class PrimeraOperacionCabezaSoploController extends Controller
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
            $piece = PrimeraOperacionCabezaSoplo_pza::find($pieceId);
            // Crear arreglo de datos por índice
            $fields = [
                'diametro_exterior',
                'longitud',
                'diametro_candado',
                'longitud_candado',
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
            $piece = PrimeraOperacionCabezaSoplo_pza::find($pieceId);
            //Guardar los datos de la pieza
            $piece->fill($request->only([
                'diametro_exterior',
                'longitud',
                'diametro_candado',
                'longitud_candado',
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
    public function comparePieceData($pieza, $cNominal, $tolerancia)
    {
        $campos = [
            'diametro_exterior',
            'longitud',
            'diametro_candado',
            'longitud_candado'
        ];

        $epsilon = 0.000001; // tolerancia mínima para errores de redondeo

        foreach ($campos as $campo) {
            $valorPiece = (float) $pieza->$campo;
            $nominal = (float) $cNominal->$campo;
            $tolPlus = (float) $tolerancia->{$campo . '1'};
            $tolMinus = (float) $tolerancia->{$campo . '2'};

            $min = $nominal - $tolMinus;
            $max = $nominal + $tolPlus;

            // Compara con epsilon para evitar falsos positivos por decimales
            if ($valorPiece > $max + $epsilon || $valorPiece < $min - $epsilon) {
                return 0;
            }
        }

        return 1;
    }
}
