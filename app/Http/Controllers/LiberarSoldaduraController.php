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

        // Agrupar soldaduras por nombre y lote, sumando los kilos disponibles
        $soldaduras = RegistroSoldadura::where('kilos', '>', 0)
            ->selectRaw('nombre, lote, SUM(kilos) as kilos_totales, MIN(id) as id')
            ->groupBy('nombre', 'lote')
            ->having('kilos_totales', '>', 0)
            ->get();

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
            'soldadura_id' => 'required',
            'cantidad' => 'required|numeric|min:0.01',
        ]);

        // Obtener nombre y lote del request (ahora viene como "nombre|lote")
        $soldaduraInfo = explode('|', $request->soldadura_id);
        $nombre = $soldaduraInfo[0];
        $lote = $soldaduraInfo[1];
        
        // Obtener todos los registros con el mismo nombre y lote que tengan kilos disponibles
        $registros = RegistroSoldadura::where('nombre', $nombre)
                                     ->where('lote', $lote)
                                     ->where('kilos', '>', 0)
                                     ->orderBy('id')
                                     ->get();
        
        // Calcular kilos totales disponibles
        $kilosTotales = $registros->sum('kilos');
        
        // Verificar que hay suficiente cantidad disponible
        if ($kilosTotales <= 0) {
            return redirect()
                ->back()
                ->withErrors(['cantidad' => 'No hay soldadura disponible para este lote.'])
                ->withInput();
        }
        
        if ($request->cantidad > $kilosTotales) {
            return redirect()
                ->back()
                ->withErrors(['cantidad' => "Solo hay {$kilosTotales} kg disponibles. No se pueden liberar {$request->cantidad} kg."])
                ->withInput();
        }

        // Crear el registro de liberación
        LiberacionSoldadura::create([
            'id_operador' => $request->operador_id,
            'fecha_entrega' => $request->fecha_entrega,
            'nombre' => $nombre,
            'lote' => $lote,
            'cantidad' => $request->cantidad,
        ]);
        
        // Descontar la cantidad proporcionalmente de cada registro
        $cantidadRestante = $request->cantidad;
        foreach ($registros as $registro) {
            if ($cantidadRestante <= 0) break;
            
            $descontar = min($cantidadRestante, $registro->kilos);
            $registro->kilos -= $descontar;
            $registro->save();
            $cantidadRestante -= $descontar;
        }

        // Calcular kilos restantes totales
        $kilosRestantes = RegistroSoldadura::where('nombre', $nombre)
                                          ->where('lote', $lote)
                                          ->sum('kilos');

        $mensaje = 'Soldadura liberada correctamente.';
        if ($kilosRestantes <= 0) {
            $mensaje .= ' ATENCIÓN: Se agotó el inventario de este lote.';
        } elseif ($kilosRestantes <= 5) {
            $mensaje .= " ADVERTENCIA: Solo quedan {$kilosRestantes} kg de este lote.";
        }

        return redirect()
            ->route('soldadura.liberar')
            ->with('success', $mensaje);
    }
}