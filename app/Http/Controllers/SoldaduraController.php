<?php

namespace App\Http\Controllers;

use App\Models\Soldadura_pza;

class SoldaduraController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }
    public function storePiece($request)
    {
        echo $request->error;
        $piece = Soldadura_pza::find($request->piece);
        //Guardar los datos de la pieza
        $piece->fill($request->only([
            'pesoxpieza',
            'temperatura_precalentado',
            'tiempo_aplicacion',
            'tipo_soldadura',
            'lote',
            'error',
            'observaciones',
        ]));
        $piece->estado = 2;
        $piece->save();
    }
}
