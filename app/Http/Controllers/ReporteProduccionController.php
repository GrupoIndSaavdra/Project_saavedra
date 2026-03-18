<?php

namespace App\Http\Controllers;

use App\Mail\ReporteDiarioMail;
use App\Models\Clase;
use App\Models\Moldura;
use App\Models\Orden_trabajo;
use App\Models\Pieza;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Barryvdh\DomPDF\Facade\Pdf as FacadePdf;

class ReporteProduccionController extends Controller
{
    public function __construct()
    {
        // Solo el perfil 1 (Administrador) puede ver y reenviar reportes
        $this->middleware(function ($request, $next) {
            if (auth()->check() && auth()->user()->perfil != 1) {
                abort(403, 'No tienes permiso para acceder a esta sección.');
            }
            return $next($request);
        });
    }

    // ════════════════════════════════════════════════════════════════════════
    //  VISTA DE RE-ENVÍO MANUAL
    // ════════════════════════════════════════════════════════════════════════

    /**
     * Muestra el formulario de re-envío manual de reporte.
     * GET /reportes/reenvio
     */
    public function showReenvio()
    {
        $backgroundImage = 'images/fondoadmin.jpg';
        return view('reportes.reenvio', compact('backgroundImage'));
    }

    // ════════════════════════════════════════════════════════════════════════
    //  RE-ENVÍO MANUAL POR CORREO (acción POST)
    // ════════════════════════════════════════════════════════════════════════

    /**
     * Re-envía el reporte de una fecha específica.
     * POST /reportes/reenviar
     */
    public function reenviarCorreo(Request $request)
    {
        $request->validate([
            'fecha' => 'required|date',
            'correos' => 'nullable|string',
        ]);

        $fecha = Carbon::parse($request->fecha);
        $correos = $request->correos ?: env('REPORT_RECIPIENTS', '');
        $destinatarios = array_filter(array_map('trim', explode(',', $correos)));

        // Reutilizar la misma lógica del Command
        $piezas = $this->buscarConFiltros(new Request([
            'fecha_desde' => $fecha->toDateString(),
            'fecha_hasta' => $fecha->toDateString(),
        ]));

        if ($piezas->isEmpty()) {
            return back()->with('error', "No hay registros de producción para {$fecha->format('d/m/Y')}.");
        }

        $reporte = $this->agruparJerarquicamente($piezas);

        $enviados = 0;
        $errores = [];
        foreach ($destinatarios as $correo) {
            try {
                Mail::to($correo)->send(new ReporteDiarioMail($reporte, $fecha));
                $enviados++;
            } catch (\Throwable $e) {
                $errores[] = "{$correo}: " . $e->getMessage();
            }
        }

        if ($enviados > 0) {
            return redirect()->route('reportes.reenvio')
                ->with('success', "Reporte enviado a {$enviados} destinatario(s).");
        }

        return redirect()->route('reportes.reenvio')
            ->with('error', 'No se pudo enviar el correo: ' . implode(' | ', $errores));
    }

    /**
     * Genera y descarga el PDF del reporte para una fecha específica.
     * GET /reportes/descargar-pdf/{fecha}
     */
    public function descargarPDF($fechaStr)
    {
        $fecha = Carbon::parse($fechaStr);

        $piezas = $this->buscarConFiltros(new Request([
            'fecha_desde' => $fecha->toDateString(),
            'fecha_hasta' => $fecha->toDateString(),
        ]));

        if ($piezas->isEmpty()) {
            abort(404, "No hay registros de producción para {$fecha->format('d/m/Y')}.");
        }

        $reporte = $this->agruparJerarquicamente($piezas);

        $pdf = FacadePdf::loadView('emails.reporte_diario_pdf', [
            'reporte' => $reporte,
            'fecha' => $fecha
        ]);

        // Configurar opciones de PDF para mejor visualización
        $pdf->setPaper('a4', 'portrait');

        return $pdf->download("Reporte_Produccion_{$fecha->toDateString()}.pdf");
    }

    // ════════════════════════════════════════════════════════════════════════
    //  MÉTODOS PRIVADOS
    // ════════════════════════════════════════════════════════════════════════

    /**
     * Construye el query con los filtros del Request.
     * Reutiliza el patrón de PzasGeneralesController::getPiecesRequest()
     */
    private function buscarConFiltros(Request $request)
    {
        $query = Pieza::with(['clase', 'operador', 'ordenTrabajo']);

        // ── Rango de fechas ───────────────────────────────────────────────
        $desde = $request->fecha_desde ?? Carbon::today()->toDateString();
        $hasta = $request->fecha_hasta ?? Carbon::today()->toDateString();
        $query->whereDate('created_at', '>=', $desde)
            ->whereDate('created_at', '<=', $hasta);

        // ── OT ────────────────────────────────────────────────────────────
        if ($request->ot && $request->ot !== 'Todos') {
            // Acepta "5 - Moldura X" o solo "5"
            $otId = explode(' - ', $request->ot)[0];
            $query->where('id_ot', trim($otId));
        }

        // ── Clase ─────────────────────────────────────────────────────────
        if ($request->clase && $request->clase !== 'Todos') {
            $clase = Clase::where('nombre', $request->clase)->first();
            if ($clase) {
                $query->where('id_clase', $clase->id);
            }
        }

        // ── Proceso ───────────────────────────────────────────────────────
        if ($request->proceso && $request->proceso !== 'Todos') {
            $query->where('proceso', $request->proceso);
        }

        return $query->orderBy('id_ot')
            ->orderBy('id_clase')
            ->orderBy('proceso')
            ->orderBy('created_at')
            ->get();
    }

    /**
     * Agrupa piezas en: OT → Clase → Proceso → [ filas de operadores ]
     * Idéntica a la lógica del Command (reutilizable desde ambos lugares).
     */
    private function agruparJerarquicamente($piezas): array
    {
        $reporte = [];
        $molduras = [];
        $usuarios = [];

        foreach ($piezas as $pieza) {
            // ── OT ────────────────────────────────────────────────────────
            $otId = $pieza->id_ot;
            if (!isset($reporte[$otId])) {
                if (!isset($molduras[$otId])) {
                    $ot = Orden_trabajo::find($otId);
                    $mn = $ot ? optional(Moldura::find($ot->id_moldura))->nombre ?? '—' : '—';
                    $molduras[$otId] = "OT #{$otId} — {$mn}";
                }
                $reporte[$otId] = ['ot_label' => $molduras[$otId], 'clases' => []];
            }

            // ── Clase ─────────────────────────────────────────────────────
            $claseId = $pieza->id_clase;
            if (!isset($reporte[$otId]['clases'][$claseId])) {
                $cls = Clase::find($claseId);
                $reporte[$otId]['clases'][$claseId] = [
                    'clase_label' => $cls ? trim($cls->nombre . ' ' . $cls->tamanio) : "Clase #{$claseId}",
                    'procesos' => [],
                ];
            }

            // ── Proceso ───────────────────────────────────────────────────
            $proceso = $pieza->proceso ?? 'Sin Proceso';
            if (!isset($reporte[$otId]['clases'][$claseId]['procesos'][$proceso])) {
                $reporte[$otId]['clases'][$claseId]['procesos'][$proceso] = [];
            }

            // ── Fila ──────────────────────────────────────────────────────
            $mat = $pieza->id_operador;
            if (!isset($usuarios[$mat])) {
                $u = User::where('matricula', $mat)->first();
                $usuarios[$mat] = $u
                    ? trim("{$u->nombre} {$u->a_paterno} {$u->a_materno}")
                    : "Op #{$mat}";
            }
            $operador = $usuarios[$mat];

            if (!isset($reporte[$otId]['clases'][$claseId]['procesos'][$proceso][$operador])) {
                $reporte[$otId]['clases'][$claseId]['procesos'][$proceso][$operador] = [];
            }

            $reporte[$otId]['clases'][$claseId]['procesos'][$proceso][$operador][] = [
                'n_piezas' => $pieza->n_pieza,
                'hora' => Carbon::parse($pieza->created_at)->format('d/m/Y H:i'),
                'observacion' => $pieza->observacion_liberacion ?? '—',
                'liberado' => ($pieza->liberacion != 2),
                'error' => $pieza->error ?? 'Ninguno',
            ];
        }

        return $reporte;
    }
}
