<?php

namespace App\Http\Controllers;

use App\Models\BarrenoManiobra_pza;

class BarrenoManiobraController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }
    public function storePiece($request, $cNominal, $tolerance, $index)
    {
        if ($index !== null) {
            $piece = BarrenoManiobra_pza::find($request->piece[$index]);

            // Crear arreglo de datos por índice
            $fields = [
            'profundidad_barreno',
            'diametro_machuelo',
            'acetatoBM',
            'observaciones',
            ];

            $data = array();
            foreach ($fields as $field) {
                $data[$field] = $request->$field[$index] ?? null;
            }
            $piece->fill($data);
            $error = $request->error[$index];
        } else {
            $piece = BarrenoManiobra_pza::find($request->piece);
            //Guardar los datos de la pieza
            $piece->fill($request->only([
                'profundidad_barreno',
                'diametro_machuelo',
                'acetatoBM',
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
        if ($pieza->profundidad_barreno > ($cNominal->profundidad_barreno + $tolerancia->profundidad_barreno1) || $pieza->profundidad_barreno < ($cNominal->profundidad_barreno - $tolerancia->profundidad_barreno2) || $pieza->diametro_machuelo > ($cNominal->diametro_machuelo + $tolerancia->diametro_machuelo1) || $pieza->diametro_machuelo < ($cNominal->diametro_machuelo - $tolerancia->diametro_machuelo1) || $pieza->diametrodiametro_machuelo2 > ($cNominal->diametro_machuelo + $tolerancia->diametro_machuelo2) || $pieza->acetatoBM == "Mal") {
            return 0; //Si los datos de la pieza son diferentes a los nominales y de tolerancia, se retorna 0.
        } else {
            return 1; //Si los datos de la pieza son iguales a los nominales y de tolerancia, se retorna 1.
        }
    }
}
