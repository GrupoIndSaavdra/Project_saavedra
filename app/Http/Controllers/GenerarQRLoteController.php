<?php

namespace App\Http\Controllers;

use App\Models\SoldaduraLote;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class GenerarQRLoteController extends Controller
{
    public function index()
    {
        return view('trackingSoldadura_views.generarQRLote');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'lote' => 'required|string|max:255',
            'kilos_totales' => 'required|numeric|min:0.01',
            'numero_factura' => 'required|string|max:255',
        ]);

        $idUnico = SoldaduraLote::generarIdUnico($request->nombre, $request->lote);

        $lote = SoldaduraLote::create([
            'id_unico' => $idUnico,
            'fecha_ingreso' => now()->toDateString(),
            'nombre' => $request->nombre,
            'lote' => $request->lote,
            'kilos_totales' => $request->kilos_totales,
            'numero_factura' => $request->numero_factura,
        ]);

        return $this->generarPDF($lote);
    }

    private function generarPDF($lote)
    {
        $qrContent = json_encode([
            'id_unico' => $lote->id_unico,
            'nombre' => $lote->nombre,
            'lote' => $lote->lote,
            'kilos_totales' => $lote->kilos_totales,
            'numero_factura' => $lote->numero_factura,
            'tipo' => 'lote'
        ]);

        // Usar URL del QR directamente en el PDF
        $qrUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=' . urlencode($qrContent);

        $pdf = Pdf::loadView('trackingSoldadura_views.qr_lote_pdf', compact('lote', 'qrContent'));
        
        return $pdf->download('QR_Lote_' . $lote->id_unico . '.pdf');
    }
}