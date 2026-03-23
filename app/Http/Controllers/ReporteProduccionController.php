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
        \Illuminate\Support\Facades\Log::info("Reenviando reporte para {$fecha->toDateString()} a: " . implode(', ', $destinatarios));

        // Reutilizar la misma lógica del Command
        $piezas = $this->buscarConFiltros(new Request([
            'fecha_desde' => $fecha->toDateString(),
            'fecha_hasta' => $fecha->toDateString(),
        ]));

        if ($piezas->isEmpty()) {
            return back()->with('error', "No hay registros de producción para {$fecha->format('d/m/Y')}.");
        }

        $reporte = $this->agruparJerarquicamente($piezas);

        // ── Generar PDF global ─────────────────────────────────────
        $pdfPaths = [];
        $baseDir = storage_path('app/public/reportes');
        $folderPath = "{$baseDir}/General";
        if (!file_exists($folderPath)) {
            mkdir($folderPath, 0755, true);
        }
        $fullPath = "{$folderPath}/{$fecha->toDateString()}.pdf";

        $pdf = FacadePdf::loadView('emails.reporte_diario_pdf', [
            'reporte' => $reporte,
            'fecha' => $fecha
        ]);
        $pdf->setPaper('a4', 'portrait');
        $pdf->save($fullPath);

        $pdfPaths[] = $fullPath;


        \Illuminate\Support\Facades\Log::info("PDFs generados (" . count($pdfPaths) . "): " . implode(', ', $pdfPaths));

        $enviados = 0;
        $errores = [];
        foreach ($destinatarios as $correo) {
            try {
                Mail::to($correo)->send(new ReporteDiarioMail($reporte, $fecha, $pdfPaths));
                \Illuminate\Support\Facades\Log::info("Mail enviado a {$correo}");
                $enviados++;
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::error("Error enviando mail a {$correo}: " . $e->getMessage());
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

        $hora = now()->format('H-i');
        return $pdf->download("Reporte_Produccion_{$fecha->toDateString()}_{$hora}.pdf");
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
            ->orderBy('created_at')
            ->get();
    }

    /**
     * Agrupa piezas en: OT → Clase → Proceso → [ filas de operadores ]
     * Idéntica a la lógica del Command (reutilizable desde ambos lugares).
     */
    private function agruparJerarquicamente($piezas): array
    {
        $reporteFinal = [];
        $agrupacion = [];
        $totales = [];
        $moldurasMap = [];
        $clasesMap = [];
        $usuariosMap = [];

        $dt = new \App\Http\Controllers\DatosProduccionController();
        $processesAssembly = ["Barreno Maniobra", "Soldadura", "Soldadura PTA", "Rectificado", "Asentado", "Barreno Profundidad", "Palomas", "Rebajes", "Grabado", "Operacion Equipo", "Operacion Equipo_1 operacion", "Operacion Equipo_2 operacion"];

        foreach ($piezas as $pieza) {
            $mat = $pieza->id_operador;
            if (!isset($usuariosMap[$mat])) {
                $u = User::where('matricula', $mat)->first();
                $usuariosMap[$mat] = $u ? trim("{$u->nombre} {$u->a_paterno} {$u->a_materno}") : "Op #{$mat}";
            }
            $operador = $usuariosMap[$mat];

            $otId = $pieza->id_ot;
            if (!isset($moldurasMap[$otId])) {
                $ot = Orden_trabajo::find($otId);
                $mn = $ot ? optional(Moldura::find($ot->id_moldura))->nombre ?? '—' : '—';
                $moldurasMap[$otId] = "OT #{$otId} — {$mn}";
            }

            $claseId = $pieza->id_clase;
            if (!isset($clasesMap[$claseId])) {
                $cls = Clase::find($claseId);
                $clasesMap[$claseId] = ['label' => $cls ? trim($cls->nombre . ' ' . $cls->tamanio) : "Clase #{$claseId}", 'nombre' => $cls->nombre ?? ''];
            }

            $proceso = $pieza->proceso ?? 'Sin Proceso';
            $hashProceso = "{$otId}_{$claseId}_{$proceso}";

            if (!isset($totales[$operador][$hashProceso])) {
                try {
                    $meta = $dt->obtenerMeta($pieza, $clasesMap[$claseId]['nombre']);
                } catch (\Exception $e) {
                    $meta = 0;
                }
                $totales[$operador][$hashProceso] = [
                    'meta' => $meta,
                    'buenas' => 0,
                    'ot_label' => $moldurasMap[$otId],
                    'clase_label' => $clasesMap[$claseId]['label'],
                    'proceso' => $proceso
                ];
            }

            $cantidad = in_array($pieza->proceso, $processesAssembly) || in_array($proceso, $processesAssembly) ? 1 : 0.5;
            $isValid = false;
            if ($pieza->error != "Ninguno" && !empty($pieza->error)) {
                if ($pieza->liberacion == 1 || $pieza->liberacion == 3)
                    $isValid = true;
            } else {
                if ($pieza->liberacion != 2)
                    $isValid = true;
            }
            if ($isValid) {
                $totales[$operador][$hashProceso]['buenas'] += $cantidad;
            }

            $liberado = $this->verifyPiece($pieza);
            $obsCalidad = $pieza->observacion_liberacion ?: '—';
            $obsOperador = $this->getObservacionesOperador($pieza);
            $colorFila = $this->asignColorTr($pieza->liberacion, $pieza->error);

            $nPiezaRaw = $pieza->n_pieza;
            $esJuego = str_ends_with($nPiezaRaw, 'H') || str_ends_with($nPiezaRaw, 'M');

            if (preg_match('/^(\d+)([HM])$/i', $nPiezaRaw, $matches)) {
                $nPiezaBase = $matches[1];
                $sufijo = strtoupper($matches[2]);
            } else {
                $nPiezaBase = $nPiezaRaw;
                $sufijo = '';
            }

            if (!isset($agrupacion[$operador][$hashProceso])) {
                $agrupacion[$operador][$hashProceso] = [];
            }
            $coleccion = &$agrupacion[$operador][$hashProceso];

            $keyDict = $esJuego ? "juego_{$nPiezaBase}" : "pieza_{$nPiezaRaw}_" . $pieza->id;

            if ($esJuego) {
                if (!isset($coleccion[$keyDict])) {
                    $coleccion[$keyDict] = [
                        'n_piezas' => "{$nPiezaBase}J",
                        'hora' => Carbon::parse($pieza->created_at)->format('d/m/Y H:i'),
                        'obs_operador' => $obsOperador,
                        'obs_calidad' => $obsCalidad,
                        'bg_color' => $colorFila,
                        'is_juego' => true,
                        'piezas_incluidas' => [$sufijo],
                    ];
                } else {
                    if (!in_array($sufijo, $coleccion[$keyDict]['piezas_incluidas'])) {
                        if ($obsOperador !== '—' && !str_contains($coleccion[$keyDict]['obs_operador'], $obsOperador)) {
                            $coleccion[$keyDict]['obs_operador'] = $coleccion[$keyDict]['obs_operador'] === '—' ? $obsOperador : $coleccion[$keyDict]['obs_operador'] . ' | ' . $obsOperador;
                        }
                        if ($obsCalidad !== '—' && !str_contains($coleccion[$keyDict]['obs_calidad'], $obsCalidad)) {
                            $coleccion[$keyDict]['obs_calidad'] = $coleccion[$keyDict]['obs_calidad'] === '—' ? $obsCalidad : $coleccion[$keyDict]['obs_calidad'] . ' | ' . $obsCalidad;
                        }

                        $priority = [
                            '#FF6B6B' => 5, // Rechazado
                            '#DDA0DD' => 4, // Mala sin liberación
                            '#FFD700' => 3, // Incompleto
                            '#90EE90' => 2, // Buena sin lib
                            '#79BFED' => 1  // Liberado
                        ];
                        $currentColor = $coleccion[$keyDict]['bg_color'];
                        if (($priority[$colorFila] ?? 0) > ($priority[$currentColor] ?? 0)) {
                            $coleccion[$keyDict]['bg_color'] = $colorFila;
                        }

                        $coleccion[$keyDict]['piezas_incluidas'][] = $sufijo;
                    }
                }
            } else {
                $coleccion[$keyDict] = [
                    'n_piezas' => "{$nPiezaRaw}",
                    'hora' => Carbon::parse($pieza->created_at)->format('d/m/Y H:i'),
                    'obs_operador' => $obsOperador,
                    'obs_calidad' => $obsCalidad,
                    'bg_color' => $colorFila,
                    'is_juego' => false,
                ];
            }
        }

        foreach ($agrupacion as $operador => $procesosData) {
            $reporteFinal[$operador] = [];
            foreach ($procesosData as $hashProceso => $filas) {
                $t = $totales[$operador][$hashProceso];
                foreach ($filas as $fila) {
                    if (isset($fila['is_juego']) && $fila['is_juego']) {
                        if (count($fila['piezas_incluidas']) == 1) {
                            $suf = $fila['piezas_incluidas'][0];
                            $numBase = str_replace('J', '', $fila['n_piezas']);
                            $fila['n_piezas'] = "{$numBase}{$suf}";
                        }
                        unset($fila['is_juego']);
                        unset($fila['piezas_incluidas']);
                    }
                    $fila['meta'] = $t['meta'];
                    $fila['juegos_realizados'] = $t['buenas'];
                    $fila['ot_label'] = $t['ot_label'];
                    $fila['clase_label'] = $t['clase_label'];
                    $fila['proceso'] = $t['proceso'];
                    $reporteFinal[$operador][] = $fila;
                }
            }
        }

        ksort($reporteFinal);

        return $reporteFinal;
    }

    /**
     * Recupera las observaciones del operador desde las tablas específicas de cada proceso.
     * Lógica adaptada de PzasGeneralesController/ProcessProductionController.
     */
    private function getObservacionesOperador($pieza): string
    {
        // Si el modelo ya tiene el campo observacion_operador, lo usamos directamente
        if (isset($pieza->observacion_operador) && !empty($pieza->observacion_operador) && $pieza->observacion_operador !== '—') {
            return $pieza->observacion_operador;
        }

        // Fallback para datos históricos
        return '—';
    }

    private function getModelProcess($process): ?string
    {
        $map = [
            'Cepillado' => "Cepillado",
            'Desbaste Exterior' => "DesbasteExterior",
            'Revision Laterales' => "RevLaterales",
            'Primera Operacion' => "PrimeraOpeSoldadura",
            'Barreno Maniobra' => "BarrenoManiobra",
            'Segunda Operacion' => "SegundaOpeSoldadura",
            'Rectificado' => "Rectificado",
            'Asentado' => "Asentado",
            'Calificado' => "revCalificado",
            'Acabado Bombillo' => "AcabadoBombilo",
            'Acabado Molde' => "AcabadoMolde",
            'Barreno Profundidad' => "BarrenoProfundidad",
            'Cavidades' => "Cavidades",
            'Copiado' => "Copiado",
            'Off Set' => "OffSet",
            'Palomas' => "Palomas",
            'Rebajes' => "Rebajes",
            'Operacion Equipo' => "PySOpeSoldadura",
            'Embudo CM' => "EmbudoCM",
            'Soldadura' => "Soldadura",
            'Soldadura PTA' => "SoldaduraPTA",
            'Primera Operacion Cabeza Soplo' => "PrimeraOperacionCabezaSoplo",
            'Segunda Operacion Cabeza Soplo' => "SegundaOperacionCabezaSoplo",
        ];
        return isset($map[$process]) ? "App\\Models\\" . $map[$process] : null;
    }

    private function getModelProcessPieces($process): ?string
    {
        $map = [
            'Cepillado' => "Pza_cepillado",
            'Desbaste Exterior' => "Desbaste_pza",
            'Revision Laterales' => "RevLaterales_pza",
            'Primera Operacion' => "PrimeraOpeSoldadura_pza",
            'Barreno Maniobra' => "BarrenoManiobra_pza",
            'Segunda Operacion' => "SegundaOpeSoldadura_pza",
            'Rectificado' => "Rectificado_pza",
            'Asentado' => "Asentado_pza",
            'Calificado' => "revCalificado_pza",
            'Acabado Bombillo' => "AcabadoBombilo_pza",
            'Acabado Molde' => "AcabadoMolde_pza",
            'Barreno Profundidad' => "BarrenoProfundidad_pza",
            'Cavidades' => "Cavidades_pza",
            'Copiado' => "Copiado_pza",
            'Off Set' => "OffSet_pza",
            'Palomas' => "Palomas_pza",
            'Rebajes' => "Rebajes_pza",
            'Operacion Equipo' => "PySOpeSoldadura_pza",
            'Embudo CM' => "EmbudoCM_pza",
            'Soldadura' => "Soldadura_pza",
            'Soldadura PTA' => "SoldaduraPTA_pza",
            'Primera Operacion Cabeza Soplo' => "PrimeraOperacionCabezaSoplo_pza",
            'Segunda Operacion Cabeza Soplo' => "SegundaOperacionCabezaSoplo_pza",
        ];
        return isset($map[$process]) ? "App\\Models\\" . $map[$process] : null;
    }

    private function verifyPiece($piece): bool
    {
        if (!$piece)
            return false;
        if ($piece->liberacion == 1 || $piece->liberacion == 3)
            return true;
        if ($piece->liberacion == 0 && ($piece->error == 'Ninguno' || empty($piece->error)))
            return true;
        return false;
    }

    /**
     * Mapeamos el color en Hex de acuerdo con adminPieces.js
     */
    private function asignColorTr($status, $error)
    {
        $status = (int) $status;
        switch ($status) {
            case 1:
                return "#79BFED";
            case 2:
                return "#FF6B6B";
            case 3:
                return "#90EE90";
            case 4:
                return "#DDA0DD";
            case 5:
                return "#FFD700";
            default:
                if (str_contains((string) $error, "Incompleto"))
                    return "#FFD700";
                elseif ($error === "Ninguno" || empty($error))
                    return "#90EE90";
                else
                    return "#DDA0DD";
        }
    }
}
