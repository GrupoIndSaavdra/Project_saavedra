<?php

namespace App\Http\Controllers;

use App\Models\SoldaduraPTA_pza;

class SoldaduraPTAController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }
    public function storePiece($request)
    {
        $piece = SoldaduraPTA_pza::find($request->piece);
        //Guardar los datos de la pieza
        $piece->fill($request->only([
            'temp_calentado',
            'temp_dispositivo',
            'limpieza',
            'error',
            'observaciones',
        ]));
        $piece->estado = 2;
        $piece->save();
    }
}
