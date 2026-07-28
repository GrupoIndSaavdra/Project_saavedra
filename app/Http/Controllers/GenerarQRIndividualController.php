<?php

namespace App\Http\Controllers;

use App\Models\SoldaduraLote;
use App\Models\SoldaduraBote;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;

class GenerarQRIndividualController extends Controller
{
    public function index()
    {
        // Obtener lotes que no han generado todos sus botes
        $lotes = SoldaduraLote::query()->whereRaw('botes_generados < FLOOR(peso_total_kg / 5)', [], 'and')
                    ->orWhere('botes_generados', 0)
                    ->orderBy('fecha_ingreso', 'desc')
                    ->get();
        
        return view('welding_tracking_views.generate_qr_individual', compact('lotes'));
    }

        /**
     * @param \Illuminate\Http\Request Request $request
     */
    public function store(Request $request)
    {
        $request->validate([
            'lote_id' => 'required|exists:soldadura_lotes,id',
        ]);

        $lote = SoldaduraLote::findOrFail($request->lote_id);
        
        // Verificar si ya se generaron los botes
        if ($lote->botesGeneradosCompletados()) {
            return back()->withErrors(['lote_id' => 'Este lote ya tiene todos sus botes generados.']);
        }
        
        $cantidadBotes = $lote->cantidadBotesEsperados();
        
        $botes = [];
        for ($i = 1; $i <= $cantidadBotes; $i++) {
            $matricula = SoldaduraBote::generarMatricula($lote->matricula, $i);
            
            $bote = SoldaduraBote::create([
                'lote_id' => $lote->id,
                'matricula' => $matricula,
                'numero_bote' => $i,
                'peso_kg' => 5.00,
                'estado' => 'en_transito',
            ]);
            
            $botes[] = $bote;
        }

        $lote->update(['botes_generados' => $cantidadBotes]);

        return $this->generarPDF($botes, $lote);
    }

    /**
     * @param array $botes
     * @param \App\Models\SoldaduraLote $lote
     */
    private function generarPDF($botes, $lote)
    {
        $qrCodes = [];
        
        foreach ($botes as $bote) {
            $qrContent = json_encode([
                'tipo' => 'bote',
                'id' => $bote->id,
                'matricula' => $bote->matricula,
                'lote_id' => $bote->lote_id,
                'numero_bote' => $bote->numero_bote,
            ]);

            $qrCodes[] = [
                'bote' => $bote,
                'qrContent' => $qrContent
            ];
        }

        $pdf = Pdf::loadView('welding_tracking_views.qr_individual_pdf', compact('qrCodes', 'lote'));
        
        return $pdf->download('QR_Botes_' . $lote->matricula . '.pdf');
    }
}