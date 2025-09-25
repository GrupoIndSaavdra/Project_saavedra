<?php

namespace App\Http\Controllers;

use App\Models\Rebajes_pza;

class RebajesController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }
    public function storePiece($request, $cNominal, $tolerance, $index)
    {
        if ($index !== null) {
            $piece = Rebajes_pza::find($request->piece[$index]);

            // Crear arreglo de datos por índice
            $fields = [
                'rebaje1',
                'rebaje2',
                'rebaje3',
                'profundidad_bordonio',
                'vena1',
                'vena2',
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
            $piece = Rebajes_pza::find($request->piece);
            //Guardar los datos de la pieza
            $piece->fill($request->only([
                'rebaje1',
                'rebaje2',
                'rebaje3',
                'profundidad_bordonio',
                'vena1',
                'vena2',
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
        if ($pieza->rebaje1 > ($cNominal->rebaje1 + $tolerancia->rebaje1) || $pieza->rebaje1 < ($cNominal->rebaje1 - $tolerancia->rebaje1) || $pieza->rebaje2 > ($cNominal->rebaje2 + $tolerancia->rebaje2) || $pieza->rebaje2 < ($cNominal->rebaje2 - $tolerancia->rebaje2) || $pieza->rebaje3 > ($cNominal->rebaje3 + $tolerancia->rebaje3) || $pieza->rebaje3 < ($cNominal->rebaje3 - $tolerancia->rebaje3) || $pieza->profundidad_bordonio > ($cNominal->profundidad_bordonio + $tolerancia->profundidad_bordonio) || $pieza->profundidad_bordonio < ($cNominal->profundidad_bordonio - $tolerancia->profundidad_bordonio) || $pieza->vena1 > ($cNominal->vena1 + $tolerancia->vena1) || $pieza->vena1 < ($cNominal->vena1 - $tolerancia->vena1) || $pieza->vena2 > ($cNominal->vena2 + $tolerancia->vena2) || $pieza->vena2 < ($cNominal->vena2 - $tolerancia->vena2) || $pieza->simetria > ($cNominal->simetria + $tolerancia->simetria) || $pieza->simetria < ($cNominal->simetria - $tolerancia->simetria)) {
            return 0; //Retorno de datos. //Si los datos de la pieza son diferentes a los nominales y de tolerancia, se retorna 0.
        }
        return 1;
    }
}