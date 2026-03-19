<?php

namespace App\Http\Controllers;

use App\Models\Asentado_pza;

class AsentadoController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }
    public function storePiece($request, $index)
    {
        if ($index !== null) {
            $pieceId = $request->piece[$index] ?? null;
            if (!$pieceId)
                return;
            $piece = Asentado_pza::find($pieceId);

            // Crear arreglo de datos por índice
            $fields = [
                'sin_juego',
                'sin_luz',
                'observaciones',
            ];

            $data = array();
            foreach ($fields as $field) {
                $data[$field] = $request->$field[$index] ?? null;
            }
            $piece->fill($data);

            //Calcular el error
            $errInput = $request->error[$index] ?? 'Ninguno';
            $sinJuegoInput = $request->sin_juego[$index] ?? null;
            $sinLuzInput = $request->sin_luz[$index] ?? null;

            if ($errInput == "Fundicion") {
                $piece->error = $errInput;
            } else {
                if ($sinJuegoInput == "X" || $sinLuzInput == "X") {
                    $piece->error = "Maquinado";
                } else {
                    $piece->error = $errInput;
                }
            }
        } else {
            $pieceId = $request->piece;
            if (!$pieceId)
                return;
            $piece = Asentado_pza::find($pieceId);
            //Guardar los datos de la pieza
            $piece->fill($request->only([
                'sin_juego',
                'sin_luz',
                'observaciones',
            ]));

            //Calcular el error
            $errInput = $request->error ?? 'Ninguno';
            $sinJuegoInput = $request->sin_juego ?? null;
            $sinLuzInput = $request->sin_luz ?? null;

            if ($errInput == "Fundicion") {
                $piece->error = $errInput;
            } else {
                if ($sinJuegoInput == "X" || $sinLuzInput == "X") {
                    $piece->error = "Maquinado";
                } else {
                    $piece->error = $errInput;
                }
            }
        }
        $piece->estado = 2;
        $piece->save();
    }
}
