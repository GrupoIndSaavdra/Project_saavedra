<?php

namespace App\Http\Controllers;

use App\Models\revCalificado_pza;

class revCalificadoController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }
    public function storePiece($request, $cNominal, $tolerance)
    {
        $piece = revCalificado_pza::find($request->piece);
        //Guardar los datos de la pieza
        $piece->fill($request->only([
            'diametro_ceja',
            'diametro_sufridera',
            'altura_sufridera',
            'diametro_conexion',
            'altura_conexion',
            'diametro_caja',
            'altura_caja',
            'altura_total',
            'simetria',
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
        if ($pieza->diametro_ceja > ($cNominal->diametro_ceja + $tolerancia->diametro_ceja1) || $pieza->diametro_ceja < ($cNominal->diametro_ceja - $tolerancia->diametro_ceja2) || $pieza->diametro_sufridera > ($cNominal->diametro_sufridera + $tolerancia->diametro_sufridera1) || $pieza->diametro_sufridera < ($cNominal->diametro_sufridera - $tolerancia->diametro_sufridera2) || $pieza->altura_sufridera > ($cNominal->altura_sufridera + $tolerancia->altura_sufridera1) || $pieza->altura_sufridera < ($cNominal->altura_sufridera - $tolerancia->altura_sufridera2) || $pieza->diametro_conexion > ($cNominal->diametro_conexion + $tolerancia->diametro_conexion1) || $pieza->diametro_conexion < ($cNominal->diametro_conexion - $tolerancia->diametro_conexion2) || $pieza->altura_conexion > ($cNominal->altura_conexion + $tolerancia->altura_conexion1) || $pieza->altura_conexion < ($cNominal->altura_conexion - $tolerancia->altura_conexion2) || $pieza->diametro_caja  > ($cNominal->diametro_caja  + $tolerancia->diametro_caja1) || $pieza->diametro_caja < ($cNominal->diametro_caja - $tolerancia->diametro_caja2) || $pieza->altura_caja > ($cNominal->altura_caja  + $tolerancia->altura_caja1) || $pieza->altura_caja < ($cNominal->altura_caja - $tolerancia->altura_caja2) || $pieza->altura_total > ($cNominal->altura_total + $tolerancia->altura_total1) || $pieza->altura_total < ($cNominal->altura_total - $tolerancia->altura_total2) || $pieza->simetria < ($cNominal->simetria - $tolerancia->simetria1) || $pieza->simetria > ($cNominal->simetria + $tolerancia->simetria2)) {
            return 0; //Si los datos de la pieza son diferentes a los nominales y de tolerancia, se retorna 0.
        } else {
            return 1; //Si los datos de la pieza son iguales a los nominales y de tolerancia, se retorna 1.
        }
    }
}
