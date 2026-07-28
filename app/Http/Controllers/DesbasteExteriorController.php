<?php

namespace App\Http\Controllers;

use App\Models\Desbaste_pza;

class DesbasteExteriorController extends Controller
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
            $piece = Desbaste_pza::query()->find($pieceId);

            // Crear arreglo de datos por índice
            $fields = ['diametro_mordaza', 'diametro_ceja', 'diametro_sufrideraExtra', 'simetria_ceja', 'simetria_mordaza', 'altura_ceja', 'altura_sufridera', 'observaciones',];

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
            $piece = Desbaste_pza::query()->find($pieceId);
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
     * @param float $value
     * @param float $nominal
     * @param float $tolerancePlus
     * @param float $toleranceMinus
     * @return bool
     */
    private function outOfTolerance($value, $nominal, $tolerancePlus, $toleranceMinus)
    {
        return round($value, 4) > round($nominal + $tolerancePlus, 4) || round($value, 4) < round($nominal - $toleranceMinus, 4);
    }
    /**
     * @param mixed $pieza
     * @param mixed $cNominal
     * @param mixed $tolerancia
     * @return int
     */
    public function comparePieceData($pieza, $cNominal, $tolerancia) //Función para comparar los datos de la pieza con los datos nominales y de tolerancia.
    {
        if (
            $this->outOfTolerance($pieza->diametro_mordaza, $cNominal->diametro_mordaza, $tolerancia->diametro_mordaza1, $tolerancia->diametro_mordaza2) ||
            $this->outOfTolerance($pieza->diametro_ceja, $cNominal->diametro_ceja, $tolerancia->diametro_ceja1, $tolerancia->diametro_ceja2) ||
            $this->outOfTolerance($pieza->diametro_sufrideraExtra, $cNominal->diametro_sufrideraExtra, $tolerancia->diametro_sufrideraExtra1, $tolerancia->diametro_sufrideraExtra2) ||
            $this->outOfTolerance($pieza->simetria_ceja, $cNominal->simetria_ceja, $tolerancia->simetria_ceja1, $tolerancia->simetria_ceja2) ||
            $this->outOfTolerance($pieza->simetria_mordaza, $cNominal->simetria_mordaza, $tolerancia->simetria_mordaza1, $tolerancia->simetria_mordaza2) ||
            $this->outOfTolerance($pieza->altura_ceja, $cNominal->altura_ceja, $tolerancia->altura_ceja1, $tolerancia->altura_ceja2) ||
            $this->outOfTolerance($pieza->altura_sufridera, $cNominal->altura_sufridera, $tolerancia->altura_sufridera1, $tolerancia->altura_sufridera2)
        ) {
            return 0; //Si los datos de la pieza son diferentes a los nominales y de tolerancia, se retorna 0.
        } else {
            return 1; //Si los datos de la pieza son iguales a los nominales y de tolerancia, se retorna 1.
        }
    }
}
