<?php

namespace App\Http\Controllers;

use App\Models\Palomas_pza;

class PalomasController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }
    public function storePiece($request, $cNominal, $tolerance, $index)
    {
        if ($index !== null) {
            $piece = Palomas_pza::find($request->piece[$index]);

            // Crear arreglo de datos por índice
            $fields = [
                'anchoPaloma',
                'gruesoPaloma',
                'profundidadPaloma',
                'rebajeLlanta',
                'observaciones',
            ];

            $data = array();
            foreach ($fields as $field) {
                $data[$field] = $request->$field[$index] ?? null;
            }
            $piece->fill($data);
            $error = $request->error[$index];
        } else {
            $piece = Palomas_pza::find($request->piece);
            //Guardar los datos de la pieza
            $piece->fill($request->only([
                'anchoPaloma',
                'gruesoPaloma',
                'profundidadPaloma',
                'rebajeLlanta',
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
        if ($pieza->anchoPaloma > ($cNominal->anchoPaloma + $tolerancia->anchoPaloma) || $pieza->anchoPaloma < ($cNominal->anchoPaloma - $tolerancia->anchoPaloma) || $pieza->gruesoPaloma > ($cNominal->gruesoPaloma + $tolerancia->gruesoPaloma) || $pieza->gruesoPaloma < ($cNominal->gruesoPaloma - $tolerancia->gruesoPaloma) || $pieza->profundidadPaloma > ($cNominal->profundidadPaloma + $tolerancia->profundidadPaloma) || $pieza->profundidadPaloma < ($cNominal->profundidadPaloma - $tolerancia->profundidadPaloma) || $pieza->rebajeLlanta > ($cNominal->rebajeLlanta + $tolerancia->rebajeLlanta) || $pieza->rebajeLlanta < ($cNominal->rebajeLlanta - $tolerancia->rebajeLlanta)) {
            return 0; //Retorno de datos. //Si los datos de la pieza son diferentes a los nominales y de tolerancia, se retorna 0.
        }
        return 1;
    }
}
