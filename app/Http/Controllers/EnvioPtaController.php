<?php

namespace App\Http\Controllers;

use App\Mail\PtaReporteMail;
use App\Models\Clase;
use App\Models\Orden_trabajo;
use App\Models\PtaReporteLog;
use App\Models\PtaResultado;
use App\Models\SoldaduraPTA;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

class EnvioPtaController extends Controller
{
    /**
     * Dirección de correo fija a la que se enviarán los reportes PTA.
     * Modifica este valor para cambiar el destinatario predeterminado.
     */
    const DESTINATARIO = 'alemanpereznatali@gmail.com';

    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            if (auth()->check() && auth()->user()->perfil != 1) {
                abort(403, 'No tienes permiso para acceder a esta sección.');
            }
            return $next($request);
        });
    }

    // ════════════════════════════════════════════════════════════════════════
    //  VISTA PRINCIPAL
    // ════════════════════════════════════════════════════════════════════════

    /**
     * GET /reportes/pta
     * Muestra el formulario de envío de reportes PTA y el historial de envíos.
     */
    public function index()
    {
        // OTs que tienen al menos una clase con Soldadura PTA
        $otsConPTA = Orden_trabajo::whereHas('clases', function ($q) {
            $q->whereHas('piezas', function ($q2) {
                $q2->where('proceso', 'Soldadura PTA');
            });
        })->with([
            'moldura',
            'clases' => function ($q) {
                $q->whereHas('piezas', function ($q2) {
                    $q2->where('proceso', 'Soldadura PTA');
                });
            }
        ])->orderBy('id', 'desc')->get();

        // Historial de envíos (más recientes primero, máximo 150 registros)
        $logs = PtaReporteLog::with('usuario')->orderBy('created_at', 'desc')->limit(150)->get();

        return view('reportes.envio_pta', compact('otsConPTA', 'logs'));
    }

    // ════════════════════════════════════════════════════════════════════════
    //  ENVIAR CORREO
    // ════════════════════════════════════════════════════════════════════════

    /**
     * POST /reportes/pta/enviar
     * Genera el PDF del análisis PTA y lo envía por correo.
     */
    public function enviar(Request $request)
    {
        ini_set('memory_limit', '512M');
        set_time_limit(120);

        $request->validate([
            'ot_id'    => 'required|integer|exists:orden_trabajo,id',
            'clase_id' => 'required|integer|exists:clases,id',
        ]);

        $otId    = $request->input('ot_id');
        $claseId = $request->input('clase_id');

        $ot    = Orden_trabajo::findOrFail($otId);
        $clase = Clase::findOrFail($claseId);

        $otNombre    = "OT #{$ot->id}" . ($ot->moldura ? " — {$ot->moldura->nombre}" : '');
        $claseNombre = $clase->nombre . ($clase->tamanio ? " ({$clase->tamanio})" : '');

        // ── 1. Generar PDF (reutilizar lógica de PtaResultsController::analysisPDF) ──
        $piezasPTA = \App\Models\Pieza::query()
            ->where('id_ot', $otId)
            ->where('id_clase', $claseId)
            ->where('proceso', 'Soldadura PTA')
            ->orderByRaw('CAST(n_pieza AS UNSIGNED) ASC')
            ->orderByRaw("RIGHT(n_pieza, 1) DESC")
            ->get();

        if ($piezasPTA->isEmpty()) {
            return redirect()->route('reportes.pta')
                ->with('error', "No hay piezas con Soldadura PTA para {$otNombre} / Clase {$claseNombre}.");
        }

        $resultados = PtaResultado::query()
            ->where('ot_id', $otId)
            ->whereHas('pieza', fn($q) => $q->where('id_clase', $claseId))
            ->with(['pieza'])
            ->get()
            ->keyBy('pieza_id');

        // Datos técnicos de soldadura
        $nombreClaseLimpio = str_replace(' ', '_', $clase->nombre);
        $procesoStringId   = "Soldadura_PTA_{$nombreClaseLimpio}_{$otId}";

        $procesoPTA = SoldaduraPTA::query()
            ->where('id_ot', $otId)
            ->where('id_proceso', $procesoStringId)
            ->latest()
            ->first();

        $piezasGroup = $procesoPTA
            ? (new SoldaduraPTAController())->buildPiezasGroup($procesoPTA->id)
            : collect();

        $esJuegoCompleto = in_array(strtoupper($clase->nombre), ['OBTURADOR', 'FONDO']);
        $fecha           = now()->format('d-m-Y');
        $fechaHora       = now()->format('d-m-Y_H-i-s');

        $pdf = Pdf::loadView('pta_views.analysis_pdf', compact(
            'ot', 'clase', 'piezasPTA', 'resultados', 'piezasGroup', 'fecha', 'esJuegoCompleto'
        ) + ['claseSeleccionada' => $clase]);

        $pdf->setPaper('a4', 'landscape');

        // Guardar PDF temporal
        $baseDir    = storage_path('app/public/reportes/PTA');
        if (!file_exists($baseDir)) {
            mkdir($baseDir, 0755, true);
        }
        $filename = preg_replace('/[^A-Za-z0-9_\-\.]/', '_', "OT_{$ot->id}_{$claseNombre}_{$fechaHora}.pdf");
        $pdfPath  = "{$baseDir}/{$filename}";
        $pdf->save($pdfPath);

        // ── 2. Enviar correo ─────────────────────────────────────────────────
        $destinatario = self::DESTINATARIO;
        $estado       = 'enviado';
        $mensajeError = null;

        try {
            Mail::to($destinatario)->send(new PtaReporteMail($otNombre, $claseNombre, $pdfPath));
        } catch (\Throwable $e) {
            $estado       = 'error';
            $mensajeError = $e->getMessage();
            \Illuminate\Support\Facades\Log::error("Error enviando reporte PTA a {$destinatario}: " . $e->getMessage(), [
                'exception' => $e,
            ]);
        }

        // ── 3. Registrar en log ──────────────────────────────────────────────
        PtaReporteLog::create([
            'ot_id'        => $otId,
            'clase_id'     => $claseId,
            'ot_nombre'    => $otNombre,
            'clase_nombre' => $claseNombre,
            'destinatario' => $destinatario,
            'estado'       => $estado,
            'mensaje_error' => $mensajeError,
            'enviado_por'  => Auth::user()?->matricula,
        ]);

        if ($estado === 'enviado') {
            return redirect()->route('reportes.pta')
                ->with('success', "✅ Reporte PTA enviado correctamente a {$destinatario}.");
        }

        return redirect()->route('reportes.pta')
            ->with('error', "❌ No se pudo enviar el reporte: {$mensajeError}");
    }
}