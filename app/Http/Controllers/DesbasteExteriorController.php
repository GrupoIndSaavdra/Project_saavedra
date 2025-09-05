<?php

namespace App\Http\Controllers;

use App\Models\Desbaste_pza;

class DesbasteExteriorController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }
    public function storePiece($request, $cNominal, $tolerance)
    {
        $piece = Desbaste_pza::find($request->piece);
        //Guardar los datos de la pieza
        $piece->fill($request->only([
            'diametro_mordaza',
            'diametro_ceja',
            'diametro_sufrideraExtra',
            'simetria_ceja',
            'simetria_mordaza',
            'altura_ceja',
            'altura_sufridera',
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
        if ($pieza->diametro_mordaza > ($cNominal->diametro_mordaza + $tolerancia->diametro_mordaza1) || $pieza->diametro_mordaza < ($cNominal->diametro_mordaza - $tolerancia->diametro_mordaza2) || $pieza->diametro_ceja > ($cNominal->diametro_ceja + $tolerancia->diametro_ceja1) || $pieza->diametro_ceja < ($cNominal->diametro_ceja - $tolerancia->diametro_ceja2) || $pieza->diametro_sufrideraExtra > ($cNominal->diametro_sufrideraExtra + $tolerancia->diametro_sufrideraExtra1) || $pieza->diametro_sufrideraExtra < ($cNominal->diametro_sufrideraExtra - $tolerancia->diametro_sufrideraExtra2) || $pieza->simetria_ceja > ($cNominal->simetria_ceja + $tolerancia->simetria_ceja1) || $pieza->simetria_ceja < ($cNominal->simetria_ceja - $tolerancia->simetria_ceja2) || $pieza->simetria_mordaza  > ($cNominal->simetria_mordaza  + $tolerancia->simetria_mordaza1) || $pieza->simetria_mordaza < ($cNominal->simetria_mordaza - $tolerancia->simetria_mordaza2) || $pieza->altura_ceja  > ($cNominal->altura_ceja  + $tolerancia->altura_ceja1) || $pieza->altura_ceja < ($cNominal->altura_ceja - $tolerancia->altura_ceja2) || $pieza->altura_sufridera > ($cNominal->altura_sufridera + $tolerancia->altura_sufridera1) || $pieza->altura_sufridera < ($cNominal->altura_sufridera - $tolerancia->altura_sufridera2)) {
            return 0; //Si los datos de la pieza son diferentes a los nominales y de tolerancia, se retorna 0.
        } else {
            return 1; //Si los datos de la pieza son iguales a los nominales y de tolerancia, se retorna 1.
        }
    }
}