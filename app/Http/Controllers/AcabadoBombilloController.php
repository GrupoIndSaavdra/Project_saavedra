<?php

namespace App\Http\Controllers;

use App\Models\AcabadoBombilo_pza;

class AcabadoBombilloController extends Controller
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
            $piece = AcabadoBombilo_pza::find($pieceId);

            // Crear arreglo de datos por índice
            $fields = [
                'diametro_mordaza',
                'diametro_ceja',
                'diametro_sufridera',
                'altura_mordaza',
                'altura_ceja',
                'altura_sufridera',
                'gauge_ceja',
                'gauge_corona',
                'gauge_llanta',
                'altura_total',
                'diametro_boca',
                'diametro_asiento_corona',
                'diametro_llanta',
                'diametro_caja_corona',
                'profundidad_corona',
                'angulo_30',
                'profundidad_caja_corona',
                'simetria',
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
            $piece = AcabadoBombilo_pza::find($pieceId);
            //Guardar los datos de la pieza
            $piece->fill($request->only([
                'diametro_mordaza',
                'diametro_ceja',
                'diametro_sufridera',
                'altura_mordaza',
                'altura_ceja',
                'altura_sufridera',
                'gauge_ceja',
                'gauge_corona',
                'gauge_llanta',
                'altura_total',
                'diametro_boca',
                'diametro_asiento_corona',
                'diametro_llanta',
                'diametro_caja_corona',
                'profundidad_corona',
                'angulo_30',
                'profundidad_caja_corona',
                'simetria',
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
            'diametro_mordaza',
            'diametro_ceja',
            'diametro_sufridera',
            'altura_mordaza',
            'altura_ceja',
            'altura_sufridera',
            'diametro_boca',
            'diametro_asiento_corona',
            'diametro_llanta',
            'diametro_caja_corona',
            'profundidad_corona',
            'angulo_30',
            'profundidad_caja_corona',
            'simetria'
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
                // dd("Fuera de tolerancia en $campo", [
                //     'valor' => $valorPiece,
                //     'min' => $min,
                //     'max' => $max,
                // ]);
                return 0;
            }
        }

        return 1;
    }
}
