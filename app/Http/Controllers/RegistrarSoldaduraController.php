<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\RegistroSoldadura;
use Illuminate\Support\Facades\Storage;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use Barryvdh\DomPDF\Facade\Pdf;

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

        // Siempre crear un nuevo registro (QR individual)
        $soldadura = RegistroSoldadura::create([
            'fecha_ingreso' => $request->fecha_ingreso,
            'nombre' => $request->nombre,
            'lote' => $request->lote,
            'kilos' => $request->kilos,
        ]);

        // Generar QR con nombre y lote
        $textoQR = $request->nombre . "\n" . $request->lote;

        $renderer = new ImageRenderer(
            new RendererStyle(300),
            new SvgImageBackEnd()
        );
        $writer = new Writer($renderer);
        $qrString = $writer->writeString($textoQR);

        // Asegurar que los directorios existen
        if (!Storage::disk('public')->exists('qr_codes')) {
            Storage::disk('public')->makeDirectory('qr_codes');
        }
        if (!Storage::disk('public')->exists('temp')) {
            Storage::disk('public')->makeDirectory('temp');
        }

        // Guardar QR en storage
        $filename = 'qr_soldadura_' . $soldadura->id . '_' . time() . '.svg';
        Storage::disk('public')->put('qr_codes/' . $filename, $qrString);

        // Convertir SVG a base64 para el PDF
        $qrBase64 = 'data:image/svg+xml;base64,' . base64_encode($qrString);

        // Generar PDF
        $pdf = Pdf::loadView('trackingSoldadura_views.qr_soldadura_pdf', [
            'qrImage' => $qrBase64,
            'soldadura' => [
                'nombre' => $request->nombre,
                'lote' => $request->lote,
                'fecha_ingreso' => $request->fecha_ingreso,
                'kilos' => $request->kilos
            ]
        ]);

        $pdfFilename = 'QR_Soldadura_' . $request->nombre . '_' . $request->lote . '_' . date('Y-m-d') . '.pdf';

        // Guardar PDF temporalmente para descarga
        $pdfPath = 'temp/' . $pdfFilename;
        Storage::disk('public')->put($pdfPath, $pdf->output());

        // Mensaje de éxito con enlace de descarga
        return redirect()->route('registrarSoldadura')
            ->with('success', 'Soldadura registrada y QR generado correctamente.')
            ->with('download_link', Storage::url($pdfPath))
            ->with('download_filename', $pdfFilename);
    }
}