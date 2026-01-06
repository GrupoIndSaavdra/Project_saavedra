<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\User;
use App\Models\RegistroSoldadura;
use App\Models\QrGenerado;
use Barryvdh\DomPDF\Facade\Pdf;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;

class GenerarQRController extends Controller
{
    public function create()
    {
        $operadores = User::select('id', 'matricula', 'nombre', 'a_paterno')->where('perfil', 2)->get();
        $soldaduras = RegistroSoldadura::select('id', 'nombre', 'lote')->get();
        
        return view('GenerateQR_views.generarQR_views', compact('operadores', 'soldaduras'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'id_operador' => 'required|string',
            'id_soldadura' => 'required|integer',
            'fecha_entrega' => 'required|date',
            'cantidad_entregada' => 'required|numeric|min:0'
        ]);

        // Crear el texto del QR en formato de 4 líneas para liberación
        $textoQR = $request->id_operador . "\n" . $request->id_soldadura . "\n" . $request->fecha_entrega . "\n" . $request->cantidad_entregada;

        // Crear el código QR como SVG
        $renderer = new ImageRenderer(
            new RendererStyle(300),
            new SvgImageBackEnd()
        );
        $writer = new Writer($renderer);
        $qrString = $writer->writeString($textoQR);

        // Guardar el QR en storage
        $filename = 'qr_' . time() . '.svg';
        Storage::disk('public')->put('qr_codes/' . $filename, $qrString);

        // Guardar registro en base de datos
        QrGenerado::create([
            'id_operador' => $request->id_operador,
            'id_soldadura' => $request->id_soldadura,
            'fecha_entrega' => $request->fecha_entrega,
            'cantidad_entregada' => $request->cantidad_entregada,
            'contenido_qr' => $textoQR,
            'archivo_qr' => $filename
        ]);

        // Guardar datos en sesión para descarga
        session([
            'qr_filename' => $filename,
            'qr_data' => [
                'id_operador' => $request->id_operador,
                'id_soldadura' => $request->id_soldadura,
                'fecha_entrega' => $request->fecha_entrega,
                'cantidad_entregada' => $request->cantidad_entregada,
                'contenido_qr' => $textoQR
            ]
        ]);

        // Generar el HTML del QR para mostrar
        $qrCodeHtml = '<div style="width: 300px; height: 300px; margin: 0 auto; border: 1px solid #ddd; padding: 10px; border-radius: 8px;">' . $qrString . '</div>';

        // Obtener datos para mostrar en la vista
        $operadores = User::select('id', 'matricula', 'nombre', 'a_paterno')->where('perfil', 2)->get();
        $soldaduras = RegistroSoldadura::select('id', 'nombre', 'lote')->get();

        return view('GenerateQR_views.generarQR_views', [
            'qrCode' => $qrCodeHtml,
            'texto_qr' => $textoQR,
            'operadores' => $operadores,
            'soldaduras' => $soldaduras
        ]);
    }

    public function download()
    {
        $filename = session('qr_filename');
        $qrData = session('qr_data');
        
        if (!$filename || !$qrData || !Storage::disk('public')->exists('qr_codes/' . $filename)) {
            return redirect()->route('generarQR.create')->with('error', 'No hay código QR para descargar.');
        }

        // Obtener el contenido del SVG
        $qrSvg = Storage::disk('public')->get('qr_codes/' . $filename);
        
        // Convertir SVG a base64 para el PDF
        $qrBase64 = 'data:image/svg+xml;base64,' . base64_encode($qrSvg);
        
        // Generar PDF
        $pdf = Pdf::loadView('GenerateQR_views.qr_pdf', [
            'qrImage' => $qrBase64,
            'qrData' => $qrData
        ]);
        
        $pdfFilename = 'QR_' . $qrData['id_operador'] . '_' . $qrData['id_soldadura'] . '_' . date('Y-m-d') . '.pdf';
        
        return $pdf->download($pdfFilename);
    }
}