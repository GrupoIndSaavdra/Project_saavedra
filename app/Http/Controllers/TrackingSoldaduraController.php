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
        switch ($request->accion) {
            case 'registrar':
                return redirect()->route('registrarSoldadura');
            case 'liberar':
                return redirect()->route('soldadura.liberar');
            default:
                return back()->withErrors(['accion' => 'Acción no válida']);
        }
    }
}