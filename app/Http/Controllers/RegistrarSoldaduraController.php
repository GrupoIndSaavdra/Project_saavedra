<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\RegistroSoldadura;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;

class RegistrarSoldaduraController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function create()
    {
        return view('welding_tracking_views.registrar');
    }

        /**
     * @param Request $request
     */
    public function store(Request $request)
    {
        $request->validate([
            'fecha_ingreso' => 'required|date',
            'nombre' => 'required|string',
            'lote' => 'required|string',
            'kilos' => 'required|numeric|min:0.01',
        ]);

        // Siempre crear un nuevo registro (QR individual)
        $soldadura = RegistroSoldadura::create([
            'fecha_ingreso' => $request->fecha_ingreso,
            'nombre' => $request->nombre,
            'lote' => $request->lote,
            'kilos' => $request->kilos,
        ]);

        // Generar QR con nombre y lote usando la API externa (consistente con el resto del proyecto)
        $textoQR = $request->nombre . "\n" . $request->lote;
        $qrBase64 = "https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=" . urlencode($textoQR);

        // Generar PDF con la plantilla original
        $pdf = Pdf::loadView('welding_tracking_views.qr_soldadura_pdf', [
            'qrImage' => $qrBase64,
            'soldadura' => [
                'nombre' => $request->nombre,
                'lote' => $request->lote,
                'fecha_ingreso' => $request->fecha_ingreso,
                'kilos' => $request->kilos
            ]
        ]);

        $pdfFilename = 'QR_Lote_Soldadura_' . $request->nombre . '_' . $request->lote . '_' . date('Y-m-d') . '.pdf';

        // Guardar PDF temporalmente para descarga
        $pdfPath = 'temp/' . $pdfFilename;
        Storage::disk('public')->put($pdfPath, $pdf->output());

        // Mensaje de éxito con enlace de descarga
        // Nota: El nombre de la ruta 'registrarSoldadura' y la vista 'welding_tracking_views.registrar'
        // parecen estar pendientes de definición o fueron removidos en la migración al nuevo sistema.
        return redirect()->back()
            ->with('success', 'Soldadura registrada y QR generado correctamente.')
            ->with('download_link', Storage::url($pdfPath))
            ->with('download_filename', $pdfFilename);
    }
}