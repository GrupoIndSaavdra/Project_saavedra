<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Soldadura;
use App\Models\LiberacionSoldadura;

class LiberarSoldaduraController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Mostrar formulario
     */
    public function create()
    {
        $operadores = User::where('perfil', 2)->get();

        $soldaduras = Soldadura::all();

        return view('trackingSoldadura_views.liberar', compact(
            'operadores',
            'soldaduras'
        ));
    }

    /**
     * Guardar liberación
     */
    public function store(Request $request)
    {
        $request->validate([
            'operador_id'   => 'required|exists:users,id',
            'fecha_entrega' => 'required|date',
            'soldadura_id'  => 'required|exists:soldaduras,id',
            'cantidad'      => 'required|numeric|min:0.01',
        ]);

        LiberacionSoldadura::create([
            'operador_id'   => $request->operador_id,
            'fecha_entrega' => $request->fecha_entrega,
            'soldadura_id'  => $request->soldadura_id,
            'cantidad'      => $request->cantidad,
        ]);

        return redirect()
            ->route('soldadura.liberar')
            ->with('success', 'Soldadura liberada correctamente');
    }
}