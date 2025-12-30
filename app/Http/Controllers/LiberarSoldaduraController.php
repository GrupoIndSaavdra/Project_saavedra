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

        // Solo soldaduras con inventario disponible
        $soldaduras = RegistroSoldadura::where('kilos', '>', 0)->get();

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
            'soldadura_id' => 'required|exists:soldadura_registro,id',
            'cantidad' => 'required|numeric|min:0.01',
        ]);

        // Traer el registro de soldadura seleccionado
        $soldadura = RegistroSoldadura::findOrFail($request->soldadura_id);
        
        // Verificar que hay suficiente cantidad disponible
        if ($soldadura->kilos <= 0) {
            return redirect()
                ->back()
                ->withErrors(['cantidad' => 'No hay soldadura disponible para este lote.'])
                ->withInput();
        }
        
        if ($request->cantidad > $soldadura->kilos) {
            return redirect()
                ->back()
                ->withErrors(['cantidad' => "Solo hay {$soldadura->kilos} kg disponibles. No se pueden liberar {$request->cantidad} kg."])
                ->withInput();
        }

        // Crear el registro de liberación
        LiberacionSoldadura::create([
            'id_operador' => $request->operador_id,
            'fecha_entrega' => $request->fecha_entrega,
            'nombre' => $soldadura->nombre,
            'lote' => $soldadura->lote,
            'cantidad' => $request->cantidad,
        ]);
        
        // Descontar la cantidad del inventario
        $soldadura->kilos = $soldadura->kilos - $request->cantidad;
        $soldadura->save();

        $mensaje = 'Soldadura liberada correctamente.';
        if ($soldadura->kilos <= 0) {
            $mensaje .= ' ATENCIÓN: Se agotó el inventario de este lote.';
        } elseif ($soldadura->kilos <= 5) {
            $mensaje .= " ADVERTENCIA: Solo quedan {$soldadura->kilos} kg de este lote.";
        }

        return redirect()
            ->route('soldadura.liberar')
            ->with('success', $mensaje);
    }
}