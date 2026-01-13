<?php

namespace App\Http\Controllers;

use App\Models\SoldaduraLote;
use App\Models\SoldaduraBoteIndividual;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class GenerarQRIndividualController extends Controller
{
    public function index()
    {
        $lotes = SoldaduraLote::where('botes_generados', 0)->get();
        return view('trackingSoldadura_views.generarQRIndividual', compact('lotes'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'lote_id' => 'required|exists:soldadura_lotes,id',
        ]);

        $lote = SoldaduraLote::findOrFail($request->lote_id);
        
        $numeroBotes = floor($lote->kilos_totales / 5);
        
        $botes = [];
        for ($i = 1; $i <= $numeroBotes; $i++) {
            $idUnicoBote = SoldaduraBoteIndividual::generarIdUnico($lote->id_unico, $i);
            
            $bote = SoldaduraBoteIndividual::create([
                'id_unico' => $idUnicoBote,
                'lote_id' => $lote->id,
                'nombre' => $lote->nombre,
                'lote' => $lote->lote,
                'peso' => 5.00,
                'numero_factura' => $lote->numero_factura,
                'numero_bote' => $i,
                'estado' => 'en_camino'
            ]);
            
            $botes[] = $bote;
        }

        $lote->update(['botes_generados' => $numeroBotes]);

        return $this->generarPDF($botes, $lote);
    }

    private function generarPDF($botes, $lote)
    {
        $qrCodes = [];
        
        foreach ($botes as $bote) {
            $qrContent = json_encode([
                'id_unico' => $bote->id_unico,
                'nombre' => $bote->nombre,
                'lote' => $bote->lote,
                'peso' => $bote->peso,
                'numero_bote' => $bote->numero_bote,
                'estado' => $bote->estado,
                'tipo' => 'bote_individual'
            ]);

            $qrUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=' . urlencode($qrContent);

            $qrCodes[] = [
                'bote' => $bote,
                'qrUrl' => $qrUrl,
                'qrContent' => $qrContent
            ];
        }

        $pdf = Pdf::loadView('trackingSoldadura_views.qr_individuales_pdf', compact('qrCodes', 'lote'));
        
        return $pdf->download('QR_Individuales_' . $lote->id_unico . '.pdf');
    }
}