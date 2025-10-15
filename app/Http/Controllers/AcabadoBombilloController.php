<?php

namespace App\Http\Controllers;

use App\Models\AcabadoBombilo_pza;
use Illuminate\Support\Facades\Log;

class AcabadoBombilloController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }
    public function storePiece($request, $cNominal, $tolerance, $index)
    {
        if ($index !== null) {
            $piece = AcabadoBombilo_pza::find($request->piece[$index]);

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
            $error = $request->error[$index];
        } else {
            $piece = AcabadoBombilo_pza::find($request->piece);
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

        foreach ($campos as $campo) {
            if (
                $pieza->$campo > ($cNominal->$campo + $tolerancia->{$campo . '1'}) ||
                $pieza->$campo < ($cNominal->$campo - $tolerancia->{$campo . '2'})
            ) {
                Log::warning("Fuera de tolerancia en $campo", [
                    'valor' => $pieza->$campo,
                    'min' => $cNominal->$campo - $tolerancia->{$campo . '2'},
                    'max' => $cNominal->$campo + $tolerancia->{$campo . '1'},
                ]);
                return 0;
            }
        }
        return 1;
    }
}
