<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class TrackingSoldaduraController extends Controller
{
    // Mostrar la vista del formulario de tracking de soldadura
    public function index()
    {
        return view('trackingSoldadura_views.trackingSoldadura');
    }

    // Manejar el envío del formulario
    public function store(Request $request)
    {
        // Validar los datos del formulario
        $validatedData = $request->validate([
            'profile' => 'required|string',
            // Agrega otras reglas de validación según los campos del formulario
        ]);

        // Lógica para procesar y guardar los datos del formulario
        // ...

        // Redirigir con un mensaje de éxito
        return redirect()->route('trackingSoldadura.index')->with('success', 'Datos de soldadura registrados correctamente.');
    }
}
