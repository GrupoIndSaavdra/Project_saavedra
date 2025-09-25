<?php

namespace App\Http\Controllers;

use App\Models\Copiado_pza;

class CopiadoController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }
    public function storePiece($request, $cNominal, $tolerance, $index, $arrayPieces)
    {
        if ($index !== null) {
            $piece = Copiado_pza::find($arrayPieces ? $arrayPieces[$index] : $request->piece[$index]);

            // Crear arreglo de datos por índice
            $fields = [
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
                'observaciones_cilindrado',
                'observaciones_cavidades'
            ];

            $data = array();
            foreach ($fields as $field) {
                $data[$field] = $request->$field[$index] ?? null;
            }
            $piece->fill($data);
            //Verificar si las medidas de la pieza estan correctas
            $correctSubprocess = $this->comparePieceData($piece, $cNominal, $tolerance);
            foreach ($correctSubprocess as $key => $value) {
                if ($value == 0 && $request->error == "Ninguno") {
                    $piece->$key = 'Maquinado';
                    $piece->correcto = 0;
                } else if (($value == 0 && $request->$key[$index] == 'Fundicion') || ($value == 1 && $request->$key[$index] == 'Fundicion')) {
                    $piece->$key = $request->$key[$index];
                    $piece->correcto = 0;
                } else {
                    $piece->$key = 'Ninguno';
                    $piece->correcto = 1;
                }
            }
        } else {
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
                'observaciones_cilindrado',
                'observaciones_cavidades'
            ]));
            //Verificar si las medidas de la pieza estan correctas
            $correctSubprocess = $this->comparePieceData($piece, $cNominal, $tolerance);
            foreach ($correctSubprocess as $key => $value) {
                if ($value == 0 && $request->error == "Ninguno") {
                    $piece->$key = 'Maquinado';
                    $piece->correcto = 0;
                } else if (($value == 0 && $request->$key == 'Fundicion') || ($value == 1 && $request->$key == 'Fundicion')) {
                    $piece->$key = $request->$key;
                    $piece->correcto = 0;
                } else {
                    $piece->$key = 'Ninguno';
                    $piece->correcto = 1;
                }
            }
        }
        $piece->estado = 2;
        $piece->save();
    }
    public function comparePieceData($pieza, $cNominal, $tolerancia) //Función para comparar los datos de la pieza con los datos nominales y de tolerancia.
    {
        $subprocesos = [
            "error_cilindrado" => 1,
            "error_cavidades" => 1
        ];
        if ($pieza->diametro1_cilindrado > ($cNominal->diametro1_cilindrado + $tolerancia->diametro1_cilindrado) || $pieza->diametro1_cilindrado < ($cNominal->diametro1_cilindrado - $tolerancia->diametro1_cilindrado) || $pieza->profundidad1_cilindrado > ($cNominal->profundidad1_cilindrado + $tolerancia->profundidad1_cilindrado) || $pieza->profundidad1_cilindrado < ($cNominal->profundidad1_cilindrado - $tolerancia->profundidad1_cilindrado) || $pieza->diametro2_cilindrado > ($cNominal->diametro2_cilindrado + $tolerancia->diametro2_cilindrado) || $pieza->diametro2_cilindrado < ($cNominal->diametro2_cilindrado - $tolerancia->diametro2_cilindrado) || $pieza->profundidad2_cilindrado > ($cNominal->profundidad2_cilindrado + $tolerancia->profundidad2_cilindrado) || $pieza->profundidad2_cilindrado < ($cNominal->profundidad2_cilindrado - $tolerancia->profundidad2_cilindrado) || $pieza->diametro_sufridera > ($cNominal->diametro_sufridera + $tolerancia->diametro_sufridera) || $pieza->diametro_sufridera < ($cNominal->diametro_sufridera - $tolerancia->diametro_sufridera) || $pieza->diametro_ranura > ($cNominal->diametro_ranura + $tolerancia->diametro_ranura) || $pieza->diametro_ranura < ($cNominal->diametro_ranura - $tolerancia->diametro_ranura) || $pieza->profundidad_ranura > ($cNominal->profundidad_ranura + $tolerancia->profundidad_ranura) || $pieza->profundidad_ranura < ($cNominal->profundidad_ranura - $tolerancia->profundidad_ranura) || $pieza->profundidad_sufridera > ($cNominal->profundidad_sufridera + $tolerancia->profundidad_sufridera) || $pieza->profundidad_sufridera < ($cNominal->profundidad_sufridera - $tolerancia->profundidad_sufridera) || $pieza->altura_total > ($cNominal->altura_total + $tolerancia->altura_total) || $pieza->altura_total < ($cNominal->altura_total - $tolerancia->altura_total)) {
            $subprocesos["error_cilindrado"] = 0; //Si los datos de la pieza son diferentes a los nominales y de tolerancia, se retorna 0.
        }

        if ($pieza->diametro1_cavidades > ($cNominal->diametro1_cavidades + $tolerancia->diametro1_cavidades) || $pieza->diametro1_cavidades < ($cNominal->diametro1_cavidades - $tolerancia->diametro1_cavidades) || $pieza->profundidad1_cavidades > ($cNominal->profundidad1_cavidades + $tolerancia->profundidad1_cavidades) || $pieza->profundidad1_cavidades < ($cNominal->profundidad1_cavidades - $tolerancia->profundidad1_cavidades) || $pieza->diametro2_cavidades > ($cNominal->diametro2_cavidades + $tolerancia->diametro2_cavidades) || $pieza->diametro2_cavidades < ($cNominal->diametro2_cavidades - $tolerancia->diametro2_cavidades) || $pieza->profundidad2_cavidades > ($cNominal->profundidad2_cavidades + $tolerancia->profundidad2_cavidades) || $pieza->profundidad2_cavidades < ($cNominal->profundidad2_cavidades - $tolerancia->profundidad2_cavidades) || $pieza->diametro3 > ($cNominal->diametro3 + $tolerancia->diametro3) || $pieza->diametro3 < ($cNominal->diametro3 - $tolerancia->diametro3) || $pieza->profundidad3 > ($cNominal->profundidad3 + $tolerancia->profundidad3) || $pieza->profundidad3 < ($cNominal->profundidad3 - $tolerancia->profundidad3) || $pieza->diametro4 > ($cNominal->diametro4 + $tolerancia->diametro4) || $pieza->diametro4 < ($cNominal->diametro4 - $tolerancia->diametro4) || $pieza->profundidad4 > ($cNominal->profundidad4 + $tolerancia->profundidad4) || $pieza->profundidad4 < ($cNominal->profundidad4 - $tolerancia->profundidad4) || $pieza->volumen > ($cNominal->volumen + $tolerancia->volumen) || $pieza->volumen < ($cNominal->volumen - $tolerancia->volumen)) {
            $subprocesos["error_cavidades"] = 0; //Si los datos de la pieza son diferentes a los nominales y de tolerancia, se retorna 0.
        }
        return $subprocesos; //Retorno de datos.
    }
}
