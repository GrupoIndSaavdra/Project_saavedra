<?php

namespace App\Http\Controllers;

use App\Models\Copiado_pza;

class CopiadoController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }
    public function storePiece($request, $cNominal, $tolerance)
    {
        $piece = Copiado_pza::find($request->piece);
        //Guardar los datos de la pieza
        $piece->fill($request->only([
            'diametro1_cilindrado',
            'profundidad1_cilindrado',
            'diametro2_cilindrado',
            'profundidad2_cilindrado',
            'diametro_sufridera',
            'diametro_ranura',
            'profundidad_ranura',
            'profundidad_sufridera',
            'altura_total',
            'diametro1_cavidades',
            'profundidad1_cavidades',
            'diametro2_cavidades',
            'profundidad2_cavidades',
            'diametro3',
            'profundidad3',
            'diametro4',
            'profundidad4',
            'volumen',
            'error_cilindrado',
            'observaciones_cilindrado',
            'error_cavidades',
            'observaciones_cavidades'
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
        $subprocesos = array();
        if ($pieza->diametro1_cilindrado > ($cNominal->diametro1_cilindrado + $tolerancia->diametro1_cilindrado) || $pieza->diametro1_cilindrado < ($cNominal->diametro1_cilindrado - $tolerancia->diametro1_cilindrado) || $pieza->profundidad1_cilindrado > ($cNominal->profundidad1_cilindrado + $tolerancia->profundidad1_cilindrado) || $pieza->profundidad1_cilindrado < ($cNominal->profundidad1_cilindrado - $tolerancia->profundidad1_cilindrado) || $pieza->diametro2_cilindrado > ($cNominal->diametro2_cilindrado + $tolerancia->diametro2_cilindrado) || $pieza->diametro2_cilindrado < ($cNominal->diametro2_cilindrado - $tolerancia->diametro2_cilindrado) || $pieza->profundidad2_cilindrado > ($cNominal->profundidad2_cilindrado + $tolerancia->profundidad2_cilindrado) || $pieza->profundidad2_cilindrado < ($cNominal->profundidad2_cilindrado - $tolerancia->profundidad2_cilindrado) || $pieza->diametro_sufridera > ($cNominal->diametro_sufridera + $tolerancia->diametro_sufridera) || $pieza->diametro_sufridera < ($cNominal->diametro_sufridera - $tolerancia->diametro_sufridera) || $pieza->diametro_ranura > ($cNominal->diametro_ranura + $tolerancia->diametro_ranura) || $pieza->diametro_ranura < ($cNominal->diametro_ranura - $tolerancia->diametro_ranura) || $pieza->profundidad_ranura > ($cNominal->profundidad_ranura + $tolerancia->profundidad_ranura) || $pieza->profundidad_ranura < ($cNominal->profundidad_ranura - $tolerancia->profundidad_ranura) || $pieza->profundidad_sufridera > ($cNominal->profundidad_sufridera + $tolerancia->profundidad_sufridera) || $pieza->profundidad_sufridera < ($cNominal->profundidad_sufridera - $tolerancia->profundidad_sufridera) || $pieza->altura_total > ($cNominal->altura_total + $tolerancia->altura_total) || $pieza->altura_total < ($cNominal->altura_total - $tolerancia->altura_total)) {
            $subprocesos[0] = 0; //Si los datos de la pieza son diferentes a los nominales y de tolerancia, se retorna 0.
        } else {
            $subprocesos[0] = 1; //Si los datos de la pieza son iguales a los nominales y de tolerancia, se retorna 1.
        }

        if ($pieza->diametro1_cavidades > ($cNominal->diametro1_cavidades + $tolerancia->diametro1_cavidades) || $pieza->diametro1_cavidades < ($cNominal->diametro1_cavidades - $tolerancia->diametro1_cavidades) || $pieza->profundidad1_cavidades > ($cNominal->profundidad1_cavidades + $tolerancia->profundidad1_cavidades) || $pieza->profundidad1_cavidades < ($cNominal->profundidad1_cavidades - $tolerancia->profundidad1_cavidades) || $pieza->diametro2_cavidades > ($cNominal->diametro2_cavidades + $tolerancia->diametro2_cavidades) || $pieza->diametro2_cavidades < ($cNominal->diametro2_cavidades - $tolerancia->diametro2_cavidades) || $pieza->profundidad2_cavidades > ($cNominal->profundidad2_cavidades + $tolerancia->profundidad2_cavidades) || $pieza->profundidad2_cavidades < ($cNominal->profundidad2_cavidades - $tolerancia->profundidad2_cavidades) || $pieza->diametro3 > ($cNominal->diametro3 + $tolerancia->diametro3) || $pieza->diametro3 < ($cNominal->diametro3 - $tolerancia->diametro3) || $pieza->profundidad3 > ($cNominal->profundidad3 + $tolerancia->profundidad3) || $pieza->profundidad3 < ($cNominal->profundidad3 - $tolerancia->profundidad3) || $pieza->diametro4 > ($cNominal->diametro4 + $tolerancia->diametro4) || $pieza->diametro4 < ($cNominal->diametro4 - $tolerancia->diametro4) || $pieza->profundidad4 > ($cNominal->profundidad4 + $tolerancia->profundidad4) || $pieza->profundidad4 < ($cNominal->profundidad4 - $tolerancia->profundidad4) || $pieza->volumen > ($cNominal->volumen + $tolerancia->volumen) || $pieza->volumen < ($cNominal->volumen - $tolerancia->volumen)) {
            $subprocesos[1] = 0; //Si los datos de la pieza son diferentes a los nominales y de tolerancia, se retorna 0.
        } else {
            $subprocesos[1] = 1; //Si los datos de la pieza son iguales a los nominales y de tolerancia, se retorna 1.
        }
        return $subprocesos; //Retorno de datos.
    }
}
