<?php

namespace App\Http\Controllers;

use App\Models\Rectificado_pza;

class RectificadoController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }
    public function storePiece($request)
    {
        $piece = Rectificado_pza::find($request->piece);
        //Guardar los datos de la pieza
        $piece->fill($request->only([
            'cumple',
            'observaciones',
        ]));
        $piece->estado = 2;
        
        //Calcular el error
        if($request->error == "Fundicion"){
            $piece->error = $request->error;
        } else {
            $piece->error = $request->cumple == "Si" ? $request->error : "Maquinado";
        }
        $piece->save();
    }
}
