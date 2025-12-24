<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class TrackingSoldaduraController extends Controller
{
    public function store(Request $request)
    {
        if ($request->accion === 'registrar') {
            return view('trackingSoldadura_views.registrar');
        }

        if ($request->accion === 'liberar') {
            return view('trackingSoldadura_views.liberar');
        }
    }
}
