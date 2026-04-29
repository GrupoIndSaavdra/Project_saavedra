<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\RegistroSoldadura;
use App\Models\QRGeneradoSoldadura;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use Barryvdh\DomPDF\Facade\Pdf;

class GenerarQRSoldaduraController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Mostrar formulario
     */
    public function create()
    {
        $operadores = User::query()->where('perfil', 2)->get();

        // Agrupar soldaduras por nombre y lote, sumando los kilos disponibles
        $soldaduras = RegistroSoldadura::query()->where('kilos', '>', 0)
            ->selectRaw('nombre, lote, SUM(kilos) as kilos_totales, MIN(id) as id')
            ->groupBy('nombre', 'lote')
            ->having('kilos_totales', '>', 0)
            ->get();

        return view('trackingSoldadura_views.generarQRSoldadura', compact('operadores', 'soldaduras'));
    }

    /**
     * Generar y descargar QRs
     */
    public function store(Request $request)
    {
        $request->validate([
            'operador_id' => 'required|exists:users,id',
            'fecha_generacion' => 'required|date',
            'soldadura_id' => 'required',
            'cantidad' => 'required|numeric|min:0.01',
        ]);

        // Obtener nombre y lote del request
        $soldaduraInfo = explode('|', $request->soldadura_id);
        $nombre = $soldaduraInfo[0];
        $lote = $soldaduraInfo[1];

        // Verificar disponibilidad
        $kilosTotales = RegistroSoldadura::query()->where('nombre', $nombre)
            ->where('lote', $lote)
            ->where('kilos', '>', 0)
            ->sum('kilos');

        if ($request->cantidad > $kilosTotales) {
            return redirect()
                ->back()
                ->withErrors(['cantidad' => "Solo hay {$kilosTotales} kg disponibles."])
                ->withInput();
        }

        // Generar un solo QR con el ID
        $qrGenerado = QRGeneradoSoldadura::create([
            'id_operador' => $request->operador_id,
            'fecha_generacion' => $request->fecha_generacion,
            'nombre' => $nombre,
            'lote' => $lote,
            'kilos' => $request->cantidad,
            'qr_content' => '', // Se llenará después
            'estado' => 'generado'
        ]);

        // El QR solo contiene el ID
        $qrContent = $qrGenerado->id;

        // Actualizar el QR con su propio ID
        $qrGenerado->update(['qr_content' => $qrContent]);
        $qrs = [
            [
                'content' => $qrContent,
                'kilos' => $request->cantidad,
                'numero' => 1,
                'id' => $qrGenerado->id
            ]
        ];

        // Generar QRs con imágenes
        $qrsConImagenes = [];
        foreach ($qrs as $qr) {
            $renderer = new ImageRenderer(
                new RendererStyle(300),
                new SvgImageBackEnd()
            );
            $writer = new Writer($renderer);
            $qrString = $writer->writeString($qr['content']);

            // Convertir SVG a base64
            $qrBase64 = 'data:image/svg+xml;base64,' . base64_encode($qrString);

            $qrsConImagenes[] = [
                'content' => $qr['content'],
                'kilos' => $qr['kilos'],
                'numero' => $qr['numero'],
                'id' => $qr['id'],
                'qr_image' => $qrBase64
            ];
        }

        // Obtener datos del operador
        $operador = User::query()->find($request->operador_id);

        if (!$operador) {
            return redirect()
                ->back()
                ->withErrors(['operador_id' => 'Operador no encontrado.'])
                ->withInput();
        }

        // Generar PDF
        $pdf = Pdf::loadView('trackingSoldadura_views.qr_individual_pdf', [
            'qrs' => $qrsConImagenes,
            'operador' => $operador,
            'nombre' => $nombre,
            'lote' => $lote,
            'fecha' => $request->fecha_generacion,
            'total_kilos' => $request->cantidad
        ]);

        $pdfFilename = "QR_Individual_Soldadura_{$nombre}_{$lote}_" . date('Y-m-d') . ".pdf";

        return redirect()
            ->route('soldadura.generarQRSoldadura')
            ->with('success', 'QR individual generado y descargado correctamente.')
            ->with('pdf_content', $pdf->output())
            ->with('pdf_filename', $pdfFilename);
    }
}