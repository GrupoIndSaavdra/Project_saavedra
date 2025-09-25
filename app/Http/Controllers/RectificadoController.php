<?php

namespace App\Http\Controllers;

use App\Models\Rectificado_pza;

class RectificadoController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }
    public function storePiece($request, $index)
    {
        if ($index !== null) {
            $piece = Rectificado_pza::find($request->piece[$index]);

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
            if ($request->error[$index] == "Fundicion") {
                $piece->error = $request->error[$index];
            } else {
                $piece->error = $request->cumple[$index] == "Si" ? $request->error[$index] : "Maquinado";
            }
        } else {
            $piece = Rectificado_pza::find($request->piece);
            //Guardar los datos de la pieza
            $piece->fill($request->only([
                'cumple',
                'observaciones',
            ]));

            //Calcular el error
            if ($request->error == "Fundicion") {
                $piece->error = $request->error;
            } else {
                $piece->error = $request->cumple == "Si" ? $request->error : "Maquinado";
            }
        }
        $piece->estado = 2;
        $piece->save();
    }
}
