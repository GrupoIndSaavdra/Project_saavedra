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
            $piece = Asentado_pza::find($request->piece[$index]);

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
            if ($request->error[$index] == "Fundicion") {
                $piece->error = $request->error[$index];
            } else {
                if ($request->sin_juego[$index] == "X" || $request->sin_luz[$index] == "X") {
                    $piece->error = "Maquinado";
                } else {
                    $piece->error = $request->error[$index];
                }
            }
        } else {
            $piece = Asentado_pza::find($request->piece);
            //Guardar los datos de la pieza
            $piece->fill($request->only([
                'sin_juego',
                'sin_luz',
                'observaciones',
            ]));

            //Calcular el error
            if ($request->error == "Fundicion") {
                $piece->error = $request->error;
            } else {
                if ($request->sin_juego == "X" || $request->sin_luz == "X") {
                    $piece->error = "Maquinado";
                } else {
                    $piece->error = $request->error;
                }
            }
        }
        $piece->estado = 2;
        $piece->save();
    }
}
