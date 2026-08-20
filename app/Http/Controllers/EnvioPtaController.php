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
     * Destinatarios fijos a los que SIEMPRE se enviarán los reportes PTA:
     *   - Acabados Mex  (acabadosmex@grupoindsaavedra.com)
     *   - Ing. Alejandro (alejandross@grupoindsaavedra.com)
     *
     * Para agregar más destinatarios fijos, añade el correo a este array.
     */
    const DESTINATARIO = ['acabadosmex@grupoindsaavedra.com', 'alejandross@grupoindsaavedra.com'];

    public function __construct()
    {
        // Acceso permitido para perfil 1 (Administrador) y perfil 3 (Master)
        $this->middleware(function ($request, $next) {
            if (auth()->check() && !in_array(auth()->user()->perfil, [1, 3])) {
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
                $q2->where('proceso', '=', 'Soldadura PTA', 'and');
            }, '>=', 1);
        }, '>=', 1)->with([
            'moldura',
            'clases' => function ($q) {
                $q->whereHas('piezas', function ($q2) {
                    $q2->where('proceso', '=', 'Soldadura PTA', 'and');
                }, '>=', 1);
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
     * Siempre incluye DESTINATARIO; acepta correos adicionales por coma.
     */
    public function enviar(Request $request)
    {
        ini_set('memory_limit', '2048M');
        set_time_limit(120);

        $request->validate([
            'ot_id'               => 'required|integer|exists:orden_trabajo,id',
            'clase_id'            => 'required|integer|exists:clases,id',
            'destinatarios_extra' => 'nullable|string|max:1000',
        ]);

        $otId    = $request->input('ot_id');
        $claseId = $request->input('clase_id');

        $ot    = Orden_trabajo::findOrFail($otId);
        $clase = Clase::findOrFail($claseId);

        $otNombre    = "OT #{$ot->id}" . ($ot->moldura ? " — {$ot->moldura->nombre}" : '');
        $claseNombre = $clase->nombre . ($clase->tamanio ? " ({$clase->tamanio})" : '');

        // ── 1. Construir lista de destinatarios ──────────────────────────────
        // Los correos fijos siempre van primero
        $configPtaEmails = config('services.pta.email', 'acabadosmex@grupoindsaavedra.com,alejandross@grupoindsaavedra.com');
        $destinatarios = array_filter(array_map('trim', explode(',', $configPtaEmails)));

        $extraRaw = $request->input('destinatarios_extra', '');
        if ($extraRaw) {
            $extras = array_filter(array_map(function ($email) {
                return filter_var(trim($email), FILTER_VALIDATE_EMAIL) ? trim($email) : null;
            }, explode(',', $extraRaw)));

            foreach ($extras as $extra) {
                if (!in_array($extra, $destinatarios)) {
                    $destinatarios[] = $extra;
                }
            }
        }

        // ── 2. Generar PDF ───────────────────────────────────────────────────
        $piezasPTA = \App\Models\Pieza::query()
            ->where('id_ot', '=', $otId, 'and')
            ->where('id_clase', '=', $claseId, 'and')
            ->where('proceso', '=', 'Soldadura PTA', 'and')
            ->orderByRaw('CAST(n_pieza AS UNSIGNED) ASC')
            ->orderByRaw("RIGHT(n_pieza, 1) DESC")
            ->get();

        if ($piezasPTA->isEmpty()) {
            return redirect()->route('reportes.pta')
                ->with('error', "No hay piezas con Soldadura PTA para {$otNombre} / Clase {$claseNombre}.");
        }

        $resultados = PtaResultado::query()
            ->where('ot_id', '=', $otId, 'and')
            ->whereHas('pieza', fn($q) => $q->where('id_clase', '=', $claseId, 'and'), '>=', 1)
            ->with(['pieza'])
            ->get()
            ->keyBy('pieza_id');

        $nombreClaseLimpio = str_replace(' ', '_', $clase->nombre);
        $procesoStringId   = "Soldadura_PTA_{$nombreClaseLimpio}_{$otId}";

        $procesoPTA = SoldaduraPTA::query()
            ->where('id_ot', '=', $otId, 'and')
            ->where('id_proceso', '=', $procesoStringId, 'and')
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

        $baseDir = storage_path('app/public/reportes/PTA');
        if (!file_exists($baseDir)) {
            mkdir($baseDir, 0755, true);
        }
        $filename = preg_replace('/[^A-Za-z0-9_\-\.]/', '_', "OT_{$ot->id}_{$claseNombre}_{$fechaHora}.pdf");
        $pdfPath  = "{$baseDir}/{$filename}";
        $pdf->save($pdfPath);

        // ── 3. Enviar a todos los destinatarios ──────────────────────────────
        $errores = [];
        $enviados = [];

        foreach ($destinatarios as $correo) {
            try {
                Mail::to($correo)->send(new PtaReporteMail($otNombre, $claseNombre, $pdfPath));
                $enviados[] = $correo;
            } catch (\Throwable $e) {
                $errores[] = "{$correo}: " . $e->getMessage();
                \Illuminate\Support\Facades\Log::error("Error enviando PTA a {$correo}: " . $e->getMessage());
            }
        }

        $estado       = empty($errores) ? 'enviado' : 'error';
        $mensajeError = !empty($errores) ? implode(' | ', $errores) : null;

        // ── 4. Registrar en log ──────────────────────────────────────────────
        PtaReporteLog::create([
            'ot_id'         => $otId,
            'clase_id'      => $claseId,
            'ot_nombre'     => $otNombre,
            'clase_nombre'  => $claseNombre,
            'destinatario'  => implode(', ', $destinatarios),  // todos separados por coma
            'estado'        => $estado,
            'mensaje_error' => $mensajeError,
            'enviado_por'   => Auth::user()?->matricula,
        ]);

        if (empty($errores)) {
            return redirect()->route('reportes.pta')
                ->with('success', "Reporte PTA enviado correctamente a: " . implode(', ', $enviados) . ".");
        }

        if (!empty($enviados)) {
            return redirect()->route('reportes.pta')
                ->with('error', "Enviado parcialmente a: " . implode(', ', $enviados) . ". Errores: " . implode(' | ', $errores));
        }

        return redirect()->route('reportes.pta')
            ->with('error', "No se pudo enviar el reporte: " . implode(' | ', $errores));
    }
}
