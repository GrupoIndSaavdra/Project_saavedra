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

        // ── Generar PDFs por OT/Clase ─────────────────────────────────────
        $pdfPaths = [];
        $baseDir = storage_path('app/public/reportes');

        foreach ($reporte as $otId => $otData) {
            $nOT = str_replace(['#', 'OT ', ' ', '—'], ['', '', '_', ''], $otData['ot_label']);
            $nOT = preg_replace('/[^A-Za-z0-9_\-]/', '', $nOT); // Sanitizar

            foreach ($otData['clases'] as $claseId => $claseData) {
                $nClase = str_replace(['#', 'Clase ', ' '], ['', '', '_'], $claseData['clase_label']);
                $nClase = preg_replace('/[^A-Za-z0-9_\-]/', '', $nClase); // Sanitizar

                $folderPath = "{$baseDir}/{$nOT}/{$nClase}";
                if (!file_exists($folderPath)) {
                    mkdir($folderPath, 0755, true);
                }

                $fileName = "{$fecha->toDateString()}.pdf";
                $fullPath = "{$folderPath}/{$fileName}";

                $miniReporte = [
                    $otId => [
                        'ot_label' => $otData['ot_label'],
                        'clases' => [
                            $claseId => $claseData
                        ]
                    ]
                ];

                $pdf = FacadePdf::loadView('emails.reporte_diario_pdf', [
                    'reporte' => $miniReporte,
                    'fecha' => $fecha
                ]);
                $pdf->setPaper('a4', 'portrait');
                $pdf->save($fullPath);

                $pdfPaths[] = $fullPath;
            }
        }

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

            // ── Lógica de Agrupación (Juegos) ─────────────────────────────
            $nPiezaRaw = $pieza->n_pieza;
            $nPiezaBase = $nPiezaRaw;
            $sufijo = '';

            if (preg_match('/^(\d+)([HM])$/i', $nPiezaRaw, $matches)) {
                $nPiezaBase = $matches[1];
                $sufijo = strtoupper($matches[2]);
            }

            $keyDict = "juego_{$nPiezaBase}";
            $coleccion = &$reporte[$otId]['clases'][$claseId]['procesos'][$proceso][$operador];

            $liberado = $this->verifyPiece($pieza);
            $obsCalidad = $pieza->observacion_liberacion ?: '—';

            // Recuperar observaciones del operador desde tablas de proceso
            $nPiezaRaw = $pieza->n_pieza;
            $esJuego = str_ends_with($nPiezaRaw, 'H') || str_ends_with($nPiezaRaw, 'M');
            $numJuego = $esJuego ? substr($nPiezaRaw, 0, -1) : $nPiezaRaw;
            $identificador = $esJuego ? $numJuego . "J" : $nPiezaRaw;

            $obsOperador = $this->getObservacionesOperador($pieza);

            if ($esJuego) { // Es parte de un juego (termina en H o M)
                if (!isset($coleccion[$keyDict])) {
                    // Inicializamos el juego
                    $coleccion[$keyDict] = [
                        'n_piezas' => "{$nPiezaBase}J",
                        'hora' => Carbon::parse($pieza->created_at)->format('d/m/Y H:i'),
                        'obs_operador' => $obsOperador,
                        'obs_calidad' => $obsCalidad, // Tomamos la primera obs de calidad que llegue
                        'liberado' => $liberado,
                        'is_juego' => true,
                        'piezas_incluidas' => [$sufijo],
                    ];
                } else {
                    // Ya existe el juego, agregamos la otra mitad
                    if (!in_array($sufijo, $coleccion[$keyDict]['piezas_incluidas'])) {
                        // Concatenar observaciones del operador si hay nuevas y no son "—"
                        if ($obsOperador !== '—' && !str_contains($coleccion[$keyDict]['obs_operador'], $obsOperador)) {
                            if ($coleccion[$keyDict]['obs_operador'] === '—') {
                                $coleccion[$keyDict]['obs_operador'] = $obsOperador;
                            } else {
                                $coleccion[$keyDict]['obs_operador'] .= ' | ' . $obsOperador;
                            }
                        }

                        // Concatenar observaciones de calidad si hay nuevas y no son "—"
                        if ($obsCalidad !== '—' && !str_contains($coleccion[$keyDict]['obs_calidad'], $obsCalidad)) {
                            if ($coleccion[$keyDict]['obs_calidad'] === '—') {
                                $coleccion[$keyDict]['obs_calidad'] = $obsCalidad;
                            } else {
                                $coleccion[$keyDict]['obs_calidad'] .= ' | ' . $obsCalidad;
                            }
                        }

                        // La liberación es estricta (si uno fue rechazado, todo el juego figura rechazado)
                        $coleccion[$keyDict]['liberado'] = $coleccion[$keyDict]['liberado'] && $liberado;
                        $coleccion[$keyDict]['piezas_incluidas'][] = $sufijo;
                    }
                }
            } else {
                // Es una pieza individual (no termina en H o M)
                $keyInd = "pieza_{$nPiezaRaw}_" . $pieza->id; // Usar ID para que no colisionen piezas con el mismo num en distintos momentos
                $coleccion[$keyInd] = [
                    'n_piezas' => "{$nPiezaRaw}",
                    'hora' => Carbon::parse($pieza->created_at)->format('d/m/Y H:i'),
                    'obs_operador' => $obsOperador,
                    'obs_calidad' => $obsCalidad,
                    'liberado' => $liberado,
                    'is_juego' => false,
                ];
            }
        }

        // Limpiar las llaves de diccionario (para que Blade itere normalmente sin ver 'juego_XX')
        foreach ($reporte as $otId => &$otData) {
            foreach ($otData['clases'] as $claseId => &$claseData) {
                foreach ($claseData['procesos'] as $proceso => &$operadores) {
                    ksort($operadores); // Ordenar operadores alfabéticamente
                    foreach ($operadores as $nombreOperador => &$filas) {
                        // Si un juego quedó huérfano (solo H o solo M), lo renombramos a "Pieza XXH"
                        foreach ($filas as $key => &$fila) {
                            if (isset($fila['is_juego']) && $fila['is_juego']) {
                                if (count($fila['piezas_incluidas']) == 1) {
                                    $suf = $fila['piezas_incluidas'][0];
                                    $numBase = str_replace('J', '', $fila['n_piezas']);
                                    $fila['n_piezas'] = "{$numBase}{$suf}";
                                    // Limpiamos la 'H: ' de la observación si quedó suelta para que no se vea feo, o la dejamos.
                                }
                                unset($fila['is_juego']);
                                unset($fila['piezas_incluidas']);
                            }
                        }
                        $filas = array_values($filas); // Quitar llaves de texto
                    }
                }
            }
        }

        return $reporte;
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
}