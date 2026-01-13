<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class TrackingSoldaduraController extends Controller
{
    public function index()
    {
        return view('trackingSoldadura_views.trackingSoldadura');
    }

    public function store(Request $request)
    {
        return match ($request->accion) {
            'generar_lote' => redirect()->route('soldadura.generarQRLote'),
            'generar_individual' => redirect()->route('soldadura.generarQRIndividual'),
            'liberar_planta' => redirect()->route('soldadura.liberarQRPlanta'),
            default => back()->withErrors(['accion' => 'Acción no válida'])
        };
    }
}