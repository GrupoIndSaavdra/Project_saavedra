<?php

namespace App\Http\Controllers;

use App\Models\revCalificado_pza as RevCalificadoPza;

class revCalificadoController extends Controller
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
            $piece = RevCalificadoPza::find($pieceId);

            // Crear arreglo de datos por índice
            $fields = [
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
            $piece = RevCalificadoPza::find($pieceId);
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
    public function comparePieceData($pieza, $cNominal, $tolerancia) //Función para comparar los datos de la pieza con los datos nominales y de tolerancia.
    {
        if ($pieza->diametro_ceja > ($cNominal->diametro_ceja + $tolerancia->diametro_ceja1) || $pieza->diametro_ceja < ($cNominal->diametro_ceja - $tolerancia->diametro_ceja2) || $pieza->diametro_sufridera > ($cNominal->diametro_sufridera + $tolerancia->diametro_sufridera1) || $pieza->diametro_sufridera < ($cNominal->diametro_sufridera - $tolerancia->diametro_sufridera2) || $pieza->altura_sufridera > ($cNominal->altura_sufridera + $tolerancia->altura_sufridera1) || $pieza->altura_sufridera < ($cNominal->altura_sufridera - $tolerancia->altura_sufridera2) || $pieza->diametro_conexion > ($cNominal->diametro_conexion + $tolerancia->diametro_conexion1) || $pieza->diametro_conexion < ($cNominal->diametro_conexion - $tolerancia->diametro_conexion2) || $pieza->altura_conexion > ($cNominal->altura_conexion + $tolerancia->altura_conexion1) || $pieza->altura_conexion < ($cNominal->altura_conexion - $tolerancia->altura_conexion2) || $pieza->diametro_caja > ($cNominal->diametro_caja + $tolerancia->diametro_caja1) || $pieza->diametro_caja < ($cNominal->diametro_caja - $tolerancia->diametro_caja2) || $pieza->altura_caja > ($cNominal->altura_caja + $tolerancia->altura_caja1) || $pieza->altura_caja < ($cNominal->altura_caja - $tolerancia->altura_caja2) || $pieza->altura_total > ($cNominal->altura_total + $tolerancia->altura_total1) || $pieza->altura_total < ($cNominal->altura_total - $tolerancia->altura_total2) || $pieza->simetria > ($cNominal->simetria + $tolerancia->simetria1) || $pieza->simetria < ($cNominal->simetria - $tolerancia->simetria2)) {
            return 0; //Si los datos de la pieza son diferentes a los nominales y de tolerancia, se retorna 0.
        } else {
            return 1; //Si los datos de la pieza son iguales a los nominales y de tolerancia, se retorna 1.
        }
    }
}
