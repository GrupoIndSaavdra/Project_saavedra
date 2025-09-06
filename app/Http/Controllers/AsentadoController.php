<?php

namespace App\Http\Controllers;

use App\Models\Asentado_pza;

class AsentadoController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }
    public function storePiece($request)
    {
        $piece = Asentado_pza::find($request->piece);
        //Guardar los datos de la pieza
        $piece->fill($request->only([
            'sin_juego',
            'sin_luz',
            'observaciones',
        ]));
        $piece->estado = 2;

        //Calcular el error
        if ($request->error == "Fundicion") {
            $piece->error = $request->error;
        } else {
            if($request->sin_juego == "X" || $request->sin_luz == "X"){
                $piece->error = "Maquinado";
            }else {
                $piece->error = $request->error;
            }
        }
        $piece->save();
    }
}
