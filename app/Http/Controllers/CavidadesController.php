<?php

namespace App\Http\Controllers;

use App\Models\Cavidades_pza;

class CavidadesController extends Controller
{
    /** @var \App\Http\Controllers\PzasLiberadasController */
    protected $controladorPzasLiberadas;
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
            $piece = Cavidades_pza::query()->find($pieceId);

            // Crear arreglo de datos por índice
            $fields = [
                'profundidad1',
                'diametro1',
                'profundidad2',
                'diametro2',
                'profundidad3',
                'diametro3',
                'altura1',
                'altura2',
                'altura3',
                'acetatoBM',
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
            $piece = Cavidades_pza::query()->find($pieceId);
            //Guardar los datos de la pieza
            $piece->fill($request->only([
                'profundidad1',
                'diametro1',
                'profundidad2',
                'diametro2',
                'profundidad3',
                'diametro3',
                'altura1',
                'altura2',
                'altura3',
                'acetatoBM',
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
        if ($pieza->profundidad1 > $cNominal->profundidad1 + $tolerancia->profundidad1_1 || $pieza->profundidad1 < $cNominal->profundidad1 - $tolerancia->profundidad2_1 || $pieza->diametro1 > $cNominal->diametro1 + $tolerancia->diametro1_1 || $pieza->diametro1 < $cNominal->diametro1 - $tolerancia->diametro2_1 || $pieza->profundidad2 > $cNominal->profundidad2 + $tolerancia->profundidad1_2 || $pieza->profundidad2 < $cNominal->profundidad2 - $tolerancia->profundidad2_2 || $pieza->diametro2 > $cNominal->diametro2 + $tolerancia->diametro1_2 || $pieza->diametro2 < $cNominal->diametro2 - $tolerancia->diametro2_2 || $pieza->profundidad3 > $cNominal->profundidad3 + $tolerancia->profundidad1_3 || $pieza->profundidad3 < $cNominal->profundidad3 - $tolerancia->profundidad2_3 || $pieza->diametro3 > $cNominal->diametro3 + $tolerancia->diametro1_3 || $pieza->diametro3 < $cNominal->diametro3 - $tolerancia->diametro2_3 || $pieza->acetatoBM != 'Bien') {
            return 0; //Si los datos de la pieza son diferentes a los nominales y de tolerancia, se retorna 0.
        } else {
            return 1; //Si los datos de la pieza son iguales a los nominales y de tolerancia, se retorna 1.
        }
    }
}
