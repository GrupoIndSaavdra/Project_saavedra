<?php

namespace App\Http\Controllers;

use App\Models\Cavidades_pza;

class CavidadesController extends Controller
{
    protected $controladorPzasLiberadas;
    public function __construct()
    {
        $this->middleware('auth');
    }
    public function storePiece($request, $cNominal, $tolerance)
    {
        $piece = Cavidades_pza::find($request->piece);
        //Guardar los datos de la pieza
        $piece->fill($request->only([
            'profundidad1',
            'diametro1',
            'profundidad2',
            'diametro2',
            'profundidad3',
            'diametro3',
            'acetatoBM',
            'observaciones',
        ]));
        $piece->estado = 2;

        //Verificar si las medidas de la pieza estan correctas
        $correct = $this->comparePieceData($piece, $cNominal, $tolerance);
        if ($correct == 0 && $request->error == "Ninguno") {
            $piece->error = 'Maquinado';
            $piece->correcto = 0;
        } else if (($correct == 0 && $request->error == 'Fundicion') || ($correct == 1 && $request->error == 'Fundicion')) {
            $piece->error = $request->error;
            $piece->correcto = 0;
        } else {
            $piece->error = 'Ninguno';
            $piece->correcto = 1;
        }
        $piece->save();
    }
    public function comparePieceData($pieza, $cNominal, $tolerancia) //Función para comparar los datos de la pieza con los datos nominales y de tolerancia.
    {
        if ($pieza->profundidad1 > $cNominal->profundidad1 + $tolerancia->profundidad1_1 || $pieza->profundidad1 < $cNominal->profundidad1 - $tolerancia->profundidad2_1 || $pieza->diametro1 > $cNominal->diametro1 + $tolerancia->diametro1_1 || $pieza->diametro1 < $cNominal->diametro1 - $tolerancia->diametro2_1 || $pieza->profundidad2 > $cNominal->profundidad2 + $tolerancia->profundidad1_2 || $pieza->profundidad2 < $cNominal->profundidad2 - $tolerancia->profundidad2_2 || $pieza->diametro2 > $cNominal->diametro2 + $tolerancia->diametro1_2 || $pieza->diametro2 < $cNominal->diametro2 - $tolerancia->diametro2_2 || $pieza->profundidad3 > $cNominal->profundidad3 + $tolerancia->profundidad1_3 || $pieza->profundidad3 < $cNominal->profundidad3 - $tolerancia->profundidad2_3 || $pieza->diametro3 > $cNominal->diametro3 + $tolerancia->diametro1_3 || $pieza->diametro3 < $cNominal->diametro3 - $tolerancia->diametro2_3 || $pieza->acetatoBM != 'Bien') {
            return 0; //Si los datos de la pieza son diferentes a los nominales y de tolerancia, se retorna 0.
        } else {
            return 1; //Si los datos de la pieza son iguales a los nominales y de tolerancia, se retorna 1.
        }
    }
}
