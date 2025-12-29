<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\LiberacionSoldadura;
use App\Models\RegistroSoldadura;

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

        // Antes: $soldaduras = Soldadura::all();
        $soldaduras = RegistroSoldadura::all(); // Ahora sí trae los registros

        return view('trackingSoldadura_views.liberar', compact('operadores', 'soldaduras'));
    }

    /**
     * Guardar liberación
     */
    public function store(Request $request)
    {
        $request->validate([
            'operador_id' => 'required|exists:users,id',
            'fecha_entrega' => 'required|date',
            'soldadura_id' => 'required|exists:soldadura_registro,id', // ahora apunta al registro
            'cantidad' => 'required|numeric|min:0.01',
        ]);

        // Traer el registro de soldadura seleccionado
        $soldadura = RegistroSoldadura::findOrFail($request->soldadura_id);

        LiberacionSoldadura::create([
            'id_operador' => $request->operador_id,
            'fecha_entrega' => $request->fecha_entrega,
            'nombre' => $soldadura->nombre,
            'lote' => $soldadura->lote,
            'cantidad' => $request->cantidad,
        ]);

        return redirect()
            ->route('soldadura.liberar')
            ->with('success', 'Soldadura liberada correctamente');
    }
}