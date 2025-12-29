<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\RegistroSoldadura;

class RegistrarSoldaduraController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function create()
    {
        return view('trackingSoldadura_views.registrar');
    }

    public function store(Request $request)
    {
        $request->validate([
            'fecha_ingreso' => 'required|date',
            'nombre' => 'required|string',
            'lote' => 'required|string',
            'kilos' => 'required|numeric|min:0.01',
        ]);

        RegistroSoldadura::create([
            'fecha_ingreso' => $request->fecha_ingreso,
            'nombre' => $request->nombre,
            'lote' => $request->lote,
            'kilos' => $request->kilos,
        ]);

        return redirect()
            ->route('registrarSoldadura')
            ->with('success', 'Soldadura registrada correctamente');
    }
}