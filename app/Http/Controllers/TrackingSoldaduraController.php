<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class TrackingSoldaduraController extends Controller
{
    // Dashboard principal
    public function index()
    {
        return view('trackingSoldadura_views.trackingSoldadura');
    }

    // Manejo de formulario POST
    public function store(Request $request)
    {
        return match ($request->accion) {
            'registrar' => redirect()->route('registrarSoldadura'),
            'liberar' => redirect()->route('soldadura.liberar'),
            default => back()->withErrors(['accion' => 'Acción no válida'])
        };
    }
}