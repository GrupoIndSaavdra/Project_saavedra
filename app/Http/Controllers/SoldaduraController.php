<?php

namespace App\Http\Controllers;

use App\Models\Soldadura_pza;

class SoldaduraController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }
    public function storePiece($request, $index)
    {
        if ($index !== null) {
            $piece = Soldadura_pza::find($request->piece[$index]);

            // Crear arreglo de datos por índice
            $fields = [
                'pesoxpieza',
                'temperatura_precalentado',
                'tiempo_aplicacion',
                'tipo_soldadura',
                'lote',
                'error',
                'observaciones',
            ];

            $data = array();
            foreach ($fields as $field) {
                $data[$field] = $request->$field[$index] ?? null;
            }
            $piece->fill($data);
        } else {
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
        }
        $piece->estado = 2;
        $piece->save();
    }


}
