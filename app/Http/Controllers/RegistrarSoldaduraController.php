<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Soldadura;
use App\Models\RegistroSoldadura; // Modelo para registrar la soldadura

class RegistrarSoldaduraController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Mostrar formulario de registro
     */
    public function create()
    {
        // Traer todas las soldaduras disponibles (si es necesario para referencia)
        $soldaduras = Soldadura::all();

        // Retornar vista de registro
        return view('trackingSoldadura_views.registrar', compact('soldaduras'));
    }

    /**
     * Guardar nueva soldadura registrada vía AJAX
     */
    public function store(Request $request)
    {
        // Validación de los datos
        $request->validate([
            'fecha_ingreso' => 'required|date',
            'nombre' => 'required|string',
            'lote' => 'required|string',
            'kilos' => 'required|numeric|min:0.01',
        ]);

        // Crear el registro
        $soldadura = RegistroSoldadura::create([
            'fecha_ingreso' => $request->fecha_ingreso,
            'nombre' => $request->nombre,
            'lote' => $request->lote,
            'kilos' => $request->kilos,
        ]);

        // Retornar respuesta JSON para AJAX
        return response()->json([
            'success' => true,
            'message' => 'Soldadura registrada correctamente',
            'data' => $soldadura,
        ]);
    }
}