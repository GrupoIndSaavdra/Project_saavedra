<?php

namespace App\Http\Controllers;

use App\Models\SoldaduraPTA_pza;

class SoldaduraPTAController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }
    public function storePiece($request, $index)
    {
        if ($index !== null) {
            $piece = SoldaduraPTA_pza::find($request->piece[$index]);

            // Crear arreglo de datos por índice
            $fields = [
                'temp_calentado',
                'temp_dispositivo',
                'limpieza',
                'error',
                'observaciones',
            ];

            $data = array();
            foreach ($fields as $field) {
                $data[$field] = $request->$field[$index] ?? null;
            }
            $piece->fill($data);
        } else {
            $piece = SoldaduraPTA_pza::find($request->piece);
            //Guardar los datos de la pieza
            $piece->fill($request->only([
                'temp_calentado',
                'temp_dispositivo',
                'limpieza',
                'error',
                'observaciones',
            ]));
        }
        $piece->estado = 2;
        $piece->save();
    }
}
