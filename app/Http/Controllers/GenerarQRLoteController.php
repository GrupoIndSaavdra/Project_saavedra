<?php

namespace App\Http\Controllers;

use App\Models\SoldaduraLote;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class GenerarQRLoteController extends Controller
{
    public function index()
    {
        return view('welding_tracking_views.generate_qr_batch');
    }

        /**
     * @param \Illuminate\Http\Request Request $request
     */
    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'lote' => 'required|string|max:255',
            'peso_total' => 'required|numeric|min:5',
            'numero_factura' => 'required|string|max:255',
        ], [
            'peso_total.min' => 'El peso total debe ser al menos 5 kg',
        ]);

        $matricula = SoldaduraLote::generarMatricula($request->nombre, $request->lote);

        $lote = SoldaduraLote::create([
            'matricula' => $matricula,
            'nombre' => $request->nombre,
            'lote' => $request->lote,
            'peso_total_kg' => $request->peso_total,
            'numero_factura' => $request->numero_factura,
            'fecha_ingreso' => now()->toDateString(),
        ]);

        return $this->generarPDF($lote);
    }

    /**
     * @param \App\Models\SoldaduraLote $lote
     */
    private function generarPDF($lote)
    {
        $qrContent = json_encode([
            'tipo' => 'lote',
            'id' => $lote->id,
            'matricula' => $lote->matricula,
            'nombre' => $lote->nombre,
            'lote' => $lote->lote,
            'peso_total_kg' => $lote->peso_total_kg,
            'numero_factura' => $lote->numero_factura,
        ]);

        $pdf = Pdf::loadView('welding_tracking_views.qr_batch_pdf', compact('lote', 'qrContent'));
        
        return $pdf->download('QR_Lote_' . $lote->matricula . '.pdf');
    }
}