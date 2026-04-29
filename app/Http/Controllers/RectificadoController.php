<?php

namespace App\Http\Controllers;

use App\Models\Rectificado_pza;

class RectificadoController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }
        /**
     * @param mixed $request
     * @param int $index
     */
    public function storePiece($request, $index)
    {
        if ($index !== null) {
            $pieceId = $request->piece[$index] ?? null;
            if (!$pieceId)
                return;
            $piece = Rectificado_pza::query()->find($pieceId);

            // Crear arreglo de datos por índice
            $fields = [
                'cumple',
                'observaciones',
            ];

            $data = array();
            foreach ($fields as $field) {
                $data[$field] = $request->$field[$index] ?? null;
            }
            $piece->fill($data);

            //Calcular el error
            $errInput = $request->error[$index] ?? 'Ninguno';
            $cumpleInput = $request->cumple[$index] ?? null;
            if ($errInput == "Fundicion") {
                $piece->error = $errInput;
            } else {
                $piece->error = $cumpleInput == "Si" ? $errInput : "Maquinado";
            }
        } else {
            $pieceId = $request->piece;
            if (!$pieceId)
                return;
            $piece = Rectificado_pza::query()->find($pieceId);
            //Guardar los datos de la pieza
            $piece->fill($request->only([
                'cumple',
                'observaciones',
            ]));

            //Calcular el error
            $errInput = $request->error ?? 'Ninguno';
            $cumpleInput = $request->cumple ?? null;
            if ($errInput == "Fundicion") {
                $piece->error = $errInput;
            } else {
                $piece->error = $cumpleInput == "Si" ? $errInput : "Maquinado";
            }
        }
        $piece->estado = 2;
        $piece->save();
    }
}
