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
        if ($pieza->diametro_mordaza > ($cNominal->diametro_mordaza + $tolerancia->diametro_mordaza1) || $pieza->diametro_mordaza < ($cNominal->diametro_mordaza - $tolerancia->diametro_mordaza2) || $pieza->diametro_ceja > ($cNominal->diametro_ceja + $tolerancia->diametro_ceja1) || $pieza->diametro_ceja < ($cNominal->diametro_ceja - $tolerancia->diametro_ceja2) || $pieza->diametro_sufridera > ($cNominal->diametro_sufridera + $tolerancia->diametro_sufridera1) || $pieza->diametro_sufridera < ($cNominal->diametro_sufridera - $tolerancia->diametro_sufridera2) || $pieza->altura_mordaza > ($cNominal->altura_mordaza + $tolerancia->altura_mordaza1) || $pieza->altura_mordaza < ($cNominal->altura_mordaza - $tolerancia->altura_mordaza2) || $pieza->altura_ceja > ($cNominal->altura_ceja + $tolerancia->altura_ceja1) || $pieza->altura_ceja < ($cNominal->altura_ceja - $tolerancia->altura_ceja2) || $pieza->altura_sufridera > ($cNominal->altura_sufridera + $tolerancia->altura_sufridera1) || $pieza->altura_sufridera < ($cNominal->altura_sufridera - $tolerancia->altura_sufridera2) || $pieza->diametro_boca > ($cNominal->diametro_boca + $tolerancia->diametro_boca1) || $pieza->diametro_boca < ($cNominal->diametro_boca - $tolerancia->diametro_boca2) || $pieza->diametro_asiento_corona > ($cNominal->diametro_asiento_corona + $tolerancia->diametro_asiento_corona1) || $pieza->diametro_asiento_corona < ($cNominal->diametro_asiento_corona - $tolerancia->diametro_asiento_corona2) || $pieza->diametro_llanta > ($cNominal->diametro_llanta + $tolerancia->diametro_llanta1) || $pieza->diametro_llanta < ($cNominal->diametro_llanta - $tolerancia->diametro_llanta2) || $pieza->diametro_caja_corona > ($cNominal->diametro_caja_corona + $tolerancia->diametro_caja_corona1) || $pieza->diametro_caja_corona < ($cNominal->diametro_caja_corona - $tolerancia->diametro_caja_corona2) || $pieza->profundidad_corona > ($cNominal->profundidad_corona + $tolerancia->profundidad_corona1) || $pieza->profundidad_corona < ($cNominal->profundidad_corona - $tolerancia->profundidad_corona2) || $pieza->angulo_30 > ($cNominal->angulo_30 + $tolerancia->angulo_301) || $pieza->angulo_30 < ($cNominal->angulo_30 - $tolerancia->angulo_302) || $pieza->profundidad_caja_corona > ($cNominal->profundidad_caja_corona + $tolerancia->profundidad_caja_corona1) || $pieza->profundidad_caja_corona < ($cNominal->profundidad_caja_corona - $tolerancia->profundidad_caja_corona2) || $pieza->simetria > ($cNominal->simetria + $tolerancia->simetria1) || $pieza->simetria < ($cNominal->simetria - $tolerancia->simetria2)) {
            return 0; //Si los datos de la pieza son diferentes a los nominales y de tolerancia, se retorna 0.
        } else {
            return 1; //Si los datos de la pieza son iguales a los nominales y de tolerancia, se retorna 1.
        }
    }
}
