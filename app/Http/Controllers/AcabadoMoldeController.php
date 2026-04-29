<?php

namespace App\Http\Controllers;

use App\Models\AcabadoMolde_pza;

class AcabadoMoldeController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }
        /**
     * @param mixed $request
     * @param mixed $cNominal
     * @param mixed $tolerance
     * @param int $index
     */
    public function storePiece($request, $cNominal, $tolerance, $index)
    {
        if ($index !== null) {
            $pieceId = $request->piece[$index] ?? null;
            if (!$pieceId)
                return;
            $piece = AcabadoMolde_pza::query()->find($pieceId);

            // Crear arreglo de datos por índice
            $fields = [
                'diametro_mordaza',
                'diametro_ceja',
                'diametro_sufridera',
                'altura_mordaza',
                'altura_ceja',
                'altura_sufridera',
                'gauge_ceja',
                'altura_total',
                'diametro_conexion_fondo',
                'diametro_llanta',
                'diametro_caja_fondo',
                'altura_conexion_fondo',
                'profundidad_llanta',
                'profundidad_caja_fondo',
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
            $piece = AcabadoMolde_pza::query()->find($pieceId);
            //Guardar los datos de la pieza
            $piece->fill($request->only([
                'diametro_mordaza',
                'diametro_ceja',
                'diametro_sufridera',
                'altura_mordaza',
                'altura_ceja',
                'altura_sufridera',
                'gauge_ceja',
                'altura_total',
                'diametro_conexion_fondo',
                'diametro_llanta',
                'diametro_caja_fondo',
                'altura_conexion_fondo',
                'profundidad_llanta',
                'profundidad_caja_fondo',
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
        /**
     * @param mixed $pieza
     * @param mixed $cNominal
     * @param mixed $tolerancia
     */
    public function comparePieceData($pieza, $cNominal, $tolerancia) //Función para comparar los datos de la pieza con los datos nominales y de tolerancia.
    {
        if ($pieza->diametro_mordaza > ($cNominal->diametro_mordaza + $tolerancia->diametro_mordaza1) || $pieza->diametro_mordaza < ($cNominal->diametro_mordaza - $tolerancia->diametro_mordaza2) || $pieza->diametro_ceja > ($cNominal->diametro_ceja + $tolerancia->diametro_ceja1) || $pieza->diametro_ceja < ($cNominal->diametro_ceja - $tolerancia->diametro_ceja2) || $pieza->diametro_sufridera > ($cNominal->diametro_sufridera + $tolerancia->diametro_sufridera1) || $pieza->diametro_sufridera < ($cNominal->diametro_sufridera - $tolerancia->diametro_sufridera2) || $pieza->altura_mordaza > ($cNominal->altura_mordaza + $tolerancia->altura_mordaza1) || $pieza->altura_mordaza < ($cNominal->altura_mordaza - $tolerancia->altura_mordaza2) || $pieza->altura_ceja > ($cNominal->altura_ceja + $tolerancia->altura_ceja1) || $pieza->altura_ceja < ($cNominal->altura_ceja - $tolerancia->altura_ceja2) || $pieza->altura_sufridera > ($cNominal->altura_sufridera + $tolerancia->altura_sufridera1) || $pieza->altura_sufridera < ($cNominal->altura_sufridera - $tolerancia->altura_sufridera2) || $pieza->diametro_conexion_fondo > ($cNominal->diametro_conexion_fondo + $tolerancia->diametro_conexion_fondo1) || $pieza->diametro_conexion_fondo < ($cNominal->diametro_conexion_fondo - $tolerancia->diametro_conexion_fondo2) || $pieza->diametro_llanta > ($cNominal->diametro_llanta + $tolerancia->diametro_llanta1) || $pieza->diametro_llanta < ($cNominal->diametro_llanta - $tolerancia->diametro_llanta2) || $pieza->diametro_caja_fondo > ($cNominal->diametro_caja_fondo + $tolerancia->diametro_caja_fondo1) || $pieza->diametro_caja_fondo < ($cNominal->diametro_caja_fondo - $tolerancia->diametro_caja_fondo2) || $pieza->altura_conexion_fondo > ($cNominal->altura_conexion_fondo + $tolerancia->altura_conexion_fondo1) || $pieza->altura_conexion_fondo < ($cNominal->altura_conexion_fondo - $tolerancia->altura_conexion_fondo2) || $pieza->profundidad_llanta > ($cNominal->profundidad_llanta + $tolerancia->profundidad_llanta1) || $pieza->profundidad_llanta < ($cNominal->profundidad_llanta - $tolerancia->profundidad_llanta2) || $pieza->profundidad_caja_fondo > ($cNominal->profundidad_caja_fondo + $tolerancia->profundidad_caja_fondo1) || $pieza->profundidad_caja_fondo < ($cNominal->profundidad_caja_fondo - $tolerancia->profundidad_caja_fondo2) || $pieza->simetria > ($cNominal->simetria + $tolerancia->simetria1) || $pieza->simetria < ($cNominal->simetria - $tolerancia->simetria2)) {
            return 0; //Si los datos de la pieza son diferentes a los nominales y de tolerancia, se retorna 0.
        } else {
            return 1; //Si los datos de la pieza son iguales a los nominales y de tolerancia, se retorna 1.
        }
    }
}
