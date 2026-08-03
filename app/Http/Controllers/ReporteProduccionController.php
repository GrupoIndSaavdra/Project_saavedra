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
        // Solo el perfil 1 (Administrador) y perfil 3 (Master) pueden ver y reenviar reportes
        $this->middleware(function ($request, $next) {
            if (auth()->check() && !in_array(auth()->user()->perfil, [1, 3])) {
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
        return view('reports.resend', compact('backgroundImage'));
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
        ini_set('memory_limit', '2048M');
        $request->validate([
            'fecha' => 'required|date',
            'correos' => 'nullable|string',
        ]);

        $fecha = Carbon::parse($request->input('fecha'));
        $raw = $request->input('correos') ?: config('mail.report_recipients');
        
        // 1. Limpiar comillas y espacios externos
        $raw = trim($raw, '"\' ');
        
        // 2. Separar y limpiar cada correo de caracteres invisibles
        $destinatarios = array_filter(array_map(function($correo) {
            return preg_replace('/[^a-zA-Z0-9@._+-]/', '', trim($correo));
        }, explode(',', $raw)));
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

        $pdf = FacadePdf::loadView('emails.daily_report_pdf', [
            'reporte' => $reporte,
            'fecha' => $fecha
        ]);
        $pdf->setPaper('a4', 'portrait');
        $pdf->save($fullPath);

        $pdfPaths[] = $fullPath;


        \Illuminate\Support\Facades\Log::info("PDFs generados (" . count($pdfPaths) . "): " . implode(', ', $pdfPaths));

        $enviados = 0;
        $errores = [];
        set_time_limit(0); // Evitar timeout en reportes grandes
        
        \Illuminate\Support\Facades\Log::info("Iniciando envío a " . count($destinatarios) . " destinatarios.");

        foreach ($destinatarios as $correo) {
            try {
                Mail::to($correo)->send(new ReporteDiarioMail($reporte, $fecha, $pdfPaths));
                \Illuminate\Support\Facades\Log::info("✓ Mail enviado exitosamente a: {$correo}");
                $enviados++;
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::error("✗ Error enviando mail a {$correo}: " . $e->getMessage(), [
                    'exception' => $e
                ]);
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
     * @param string $fechaStr
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

        $pdf = FacadePdf::loadView('emails.daily_report_pdf', [
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
        $query = Pieza::query()->with(['clase', 'operador', 'ordenTrabajo']);

        // ── Rango de fechas ───────────────────────────────────────────────
        $desde = $request->input('fecha_desde') ?? Carbon::today()->toDateString();
        $hasta = $request->input('fecha_hasta') ?? Carbon::today()->toDateString();
        $query->whereDate('created_at', '>=', $desde)
            ->whereDate('created_at', '<=', $hasta);

        // ── OT ────────────────────────────────────────────────────────────
        if ($request->input('ot') && $request->input('ot') !== 'Todos') {
            // Acepta "5 - Moldura X" o solo "5"
            $otId = explode(' - ', $request->input('ot'))[0];
            $query->where('id_ot', trim($otId));
        }

        // ── Clase ─────────────────────────────────────────────────────────
        if ($request->input('clase') && $request->input('clase') !== 'Todos') {
            $clase = Clase::query()->where('nombre', $request->input('clase'))->first();
            if ($clase) {
                $query->where('id_clase', $clase->id);
            }
        }

        // ── Proceso ───────────────────────────────────────────────────────
        if ($request->input('proceso') && $request->input('proceso') !== 'Todos') {
            $query->where('proceso', $request->input('proceso'));
        }

        return $query->orderBy('id_ot')
            ->orderBy('id_clase')
            ->orderBy('created_at')
            ->get();
    }

    /**
     * Agrupa piezas en: OT → Clase → Proceso → [ filas de operadores ]
     * Idéntica a la lógica del Command (reutilizable desde ambos lugares).
     * @param \Illuminate\Support\Collection $piezas
     * @return array<string, mixed>
     */
    private function agruparJerarquicamente(\Illuminate\Support\Collection $piezas): array
    {
        /** @var array<string, mixed> $reporteFinal */
        $reporteFinal = [];
        $agrupacion = [];
        $totales = [];
        $moldurasMap = [];
        $clasesMap = [];
        $usuariosMap = [];

        $dt = new \App\Http\Controllers\DatosProduccionController();
        $processesAssembly = ["Barreno Maniobra", "Soldadura", "Soldadura PTA", "Rectificado", "Asentado", "Barreno Profundidad", "Palomas", "Rebajes", "Grabado", "Operacion Equipo", "Operacion Equipo_1 operacion", "Operacion Equipo_2 operacion"];

        // Índice global de mitades: para detectar H y M de operadores distintos
        $globalJuegoIndex = []; // [hash][numBase]['H'|'M'] => $pieza
        foreach ($piezas as $pz) {
            if (preg_match('/^(\d+)([HM])$/i', $pz->n_pieza, $gm)) {
                $gh = $pz->id_ot . '_' . $pz->id_clase . '_' . ($pz->proceso ?? 'Sin Proceso');
                $globalJuegoIndex[$gh][$gm[1]][strtoupper($gm[2])] = $pz;
            }
        }

        // --- Optimización: Bulk Eager Load ---
        $matIds = $piezas->pluck('id_operador')->unique();
        $otIds = $piezas->pluck('id_ot')->unique();
        $claseIds = $piezas->pluck('id_clase')->unique();

        $usuariosPrecargados = User::query()->whereIn('matricula', $matIds, 'and', false)->get()->keyBy('matricula');
        $otsPrecargadas = Orden_trabajo::query()->with('moldura')->whereIn('id', $otIds, 'and', false)->get()->keyBy('id');
        $clasesPrecargadas = Clase::query()->whereIn('id', $claseIds, 'and', false)->get()->keyBy('id');

        foreach ($piezas as $pieza) {
            $mat = $pieza->id_operador;
            if (!isset($usuariosMap[$mat])) {
                $u = $usuariosPrecargados->get($mat);
                $usuariosMap[$mat] = $u ? trim("{$u->nombre} {$u->a_paterno} {$u->a_materno}") : "Op #{$mat}";
            }
            $operador = $usuariosMap[$mat];

            $otId = $pieza->id_ot;
            if (!isset($moldurasMap[$otId])) {
                $ot = $otsPrecargadas->get($otId);
                $mn = $ot && $ot->moldura ? $ot->moldura->nombre : '—';
                $moldurasMap[$otId] = "OT #{$otId} — {$mn}";
            }

            $claseId = $pieza->id_clase;
            if (!isset($clasesMap[$claseId])) {
                $cls = $clasesPrecargadas->get($claseId);
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

            // Cantidad: las mitades (H/M) siempre valen 0.5 sin importar el proceso,
            // porque H + M = 1 juego. Solo las piezas completas (J) o piezas únicas valen 1.
            $nPiezaForCount = $pieza->n_pieza;
            $esMitad = str_ends_with($nPiezaForCount, 'H') || str_ends_with($nPiezaForCount, 'M');
            $cantidad = $esMitad ? 0.5 : 1;

            $isValid = false;
            if ($pieza->proceso === 'Soldadura PTA') {
                // PTA: only Fundicion blocks
                if ($pieza->liberacion == 2) {
                    $isValid = false;
                } elseif (in_array($pieza->error, ['Fundicion', 'Fundición']) && !in_array($pieza->liberacion, [1, 3])) {
                    $isValid = false;
                } else {
                    $isValid = true;
                }
            } elseif ($pieza->error != "Ninguno" && !empty($pieza->error)) {
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
            $colorFila = $this->asignColorTr($pieza->liberacion, $pieza->error, $pieza->proceso ?? '');

            $nPiezaRaw = $pieza->n_pieza;
            $esJuego = str_ends_with($nPiezaRaw, 'H') || str_ends_with($nPiezaRaw, 'M');

            if (preg_match('/^(\d+)([HM])$/i', $nPiezaRaw, $matches)) {
                $nPiezaBase = $matches[1];
                $sufijo = strtoupper($matches[2]);
            } else {
                $nPiezaBase = $nPiezaRaw;
                $sufijo = '';
            }

            if (!isset($agrupacion[$proceso][$operador][$hashProceso])) {
                $agrupacion[$proceso][$operador][$hashProceso] = [];
            }
            $coleccion = &$agrupacion[$proceso][$operador][$hashProceso];

            $keyDict = $esJuego ? "juego_{$nPiezaBase}" : "pieza_{$nPiezaRaw}_" . $pieza->id;

            if ($esJuego) {
                // ── Detectar juego compartido (distinto operador en H y M) ──
                $partnerSuf = $sufijo === 'H' ? 'M' : 'H';
                $partnerPza = $globalJuegoIndex[$hashProceso][$nPiezaBase][$partnerSuf] ?? null;

                if (!$partnerPza) {
                    $partnerPza = Pieza::query()->where('id_ot', $pieza->id_ot)
                        ->where('id_clase', $pieza->id_clase)
                        ->where('n_pieza', "{$nPiezaBase}{$partnerSuf}")
                        ->where(function($q) use ($pieza) {
                            if ($pieza->proceso) { $q->where('proceso', $pieza->proceso); }
                            else { $q->whereNull('proceso'); }
                        })->orderBy('id', 'desc')->first();
                }

                $esCompartido = $partnerPza && $partnerPza->id_operador !== $pieza->id_operador;

                if ($esCompartido) {
                    $mOpPartner = $partnerPza->id_operador;
                    if (!isset($usuariosMap[$mOpPartner])) {
                        $uOp = User::query()->where('matricula', $mOpPartner)->first();
                        $usuariosMap[$mOpPartner] = $uOp ? trim("{$uOp->nombre} {$uOp->a_paterno} {$uOp->a_materno}") : "Operador #{$mOpPartner}";
                    }
                    $opPartner = $usuariosMap[$mOpPartner];

                    if (!isset($coleccion[$keyDict])) {
                        $nota = '"Se realizó mitad de pieza junto con ' . $opPartner . '"';
                        $obsCompleta = ($obsOperador !== '' && $obsOperador !== '—') ? $nota . ', ' . $obsOperador : $nota;
                        $coleccion[$keyDict] = [
                            'n_piezas'        => "{$nPiezaBase}J",
                            'hora_inicio'     => Carbon::parse($pieza->created_at)->format('d/m/Y H:i'),
                            'hora_fin'        => Carbon::parse($pieza->updated_at)->format('d/m/Y H:i'),
                            'obs_operador'    => $obsCompleta,
                            'obs_calidad'     => $obsCalidad,
                            'bg_color'        => $colorFila,
                            'is_juego'        => true,
                            'es_compartido'   => true,
                            'piezas_incluidas'=> ['H', 'M'],
                            'maquina'         => $pieza->maquina ?? '—',
                        ];
                    }
                } else {
                    if (!isset($coleccion[$keyDict])) {
                        $coleccion[$keyDict] = [
                            'n_piezas'        => "{$nPiezaBase}J",
                            'hora_inicio'     => Carbon::parse($pieza->created_at)->format('d/m/Y H:i'),
                            'hora_fin'        => Carbon::parse($pieza->updated_at)->format('d/m/Y H:i'),
                            'obs_operador'    => $obsOperador,
                            'obs_calidad'     => $obsCalidad,
                            'bg_color'        => $colorFila,
                            'is_juego'        => true,
                            'piezas_incluidas'=> [$sufijo],
                            'maquina'         => $pieza->maquina ?? '—',
                        ];
                    } else {
                        if (!in_array($sufijo, $coleccion[$keyDict]['piezas_incluidas'])) {
                            if ($obsOperador !== '—' && !str_contains($coleccion[$keyDict]['obs_operador'], $obsOperador)) {
                                $coleccion[$keyDict]['obs_operador'] = $coleccion[$keyDict]['obs_operador'] === '—' ? $obsOperador : $coleccion[$keyDict]['obs_operador'] . ' | ' . $obsOperador;
                            }
                            if ($obsCalidad !== '—' && !str_contains($coleccion[$keyDict]['obs_calidad'], $obsCalidad)) {
                                $coleccion[$keyDict]['obs_calidad'] = $coleccion[$keyDict]['obs_calidad'] === '—' ? $obsCalidad : $coleccion[$keyDict]['obs_calidad'] . ' | ' . $obsCalidad;
                            }
                            $priority = ['#FF6B6B' => 5, '#DDA0DD' => 4, '#FFD700' => 3, '#90EE90' => 2, '#79BFED' => 1];
                            if (($priority[$colorFila] ?? 0) > ($priority[$coleccion[$keyDict]['bg_color']] ?? 0)) {
                                $coleccion[$keyDict]['bg_color'] = $colorFila;
                            }
                            $coleccion[$keyDict]['piezas_incluidas'][] = $sufijo;
                        }
                    }
                }
            } else {
                $coleccion[$keyDict] = [
                    'n_piezas'    => "{$nPiezaRaw}",
                    'hora_inicio' => Carbon::parse($pieza->created_at)->format('d/m/Y H:i'),
                    'hora_fin'    => Carbon::parse($pieza->updated_at)->format('d/m/Y H:i'),
                    'obs_operador'=> $obsOperador,
                    'obs_calidad' => $obsCalidad,
                    'bg_color'    => $colorFila,
                    'is_juego'    => false,
                    'maquina'     => $pieza->maquina ?? '—',
                ];
            }
        }

        foreach ($agrupacion as $procesoKey => $operadoresData) {
            if (!isset($reporteFinal[$procesoKey])) {
                $reporteFinal[$procesoKey] = [];
            }
            foreach ($operadoresData as $operador => $procesosData) {
                if (!isset($reporteFinal[$procesoKey][$operador])) {
                    $reporteFinal[$procesoKey][$operador] = [];
                }
                foreach ($procesosData as $hashProceso => $filas) {
                    $t = $totales[$operador][$hashProceso];
                    foreach ($filas as $fila) {
                        if (isset($fila['is_juego']) && $fila['is_juego']) {
                            if (count($fila['piezas_incluidas']) == 1) {
                                $suf = $fila['piezas_incluidas'][0];
                                $numBase = str_replace('J', '', $fila['n_piezas']);
                                $fila['n_piezas'] = "{$numBase}{$suf} (.5)"; // Mitad solitaria (aporta 0.5)
                            } elseif (isset($fila['es_compartido']) && $fila['es_compartido']) {
                                $fila['n_piezas'] .= " (.5)"; // Juego compartido (aporta 0.5 a este op)
                            }
                            unset($fila['is_juego']);
                            unset($fila['piezas_incluidas']);
                            unset($fila['es_compartido']);
                        }
                        $fila['meta'] = $t['meta'];
                        $fila['juegos_realizados'] = $t['buenas'];
                        $fila['ot_label'] = $t['ot_label'];
                        $fila['clase_label'] = $t['clase_label'];
                        $fila['proceso'] = $t['proceso'];
                        $reporteFinal[$procesoKey][$operador][] = $fila;
                    }
                }
            }
            ksort($reporteFinal[$procesoKey]);
        }

        // ── Ordenar los procesos según el flujo de producción ──
        $prioridadProcesos = [
            'Cepillado' => 1,
            'Desbaste Exterior' => 2,
            'Revision Laterales' => 3,
            'Primera Operacion' => 4,
            'Barreno Maniobra' => 5,
            'Segunda Operacion' => 6,
            'Soldadura' => 7,
            'Soldadura PTA' => 8,
            'Rectificado' => 9,
            'Asentado' => 10,
            'Calificado' => 11,
            'Acabado Bombillo' => 12,
            'Acabado Molde' => 13,
            'Barreno Profundidad' => 14,
            'Cavidades' => 15,
            'Copiado' => 16,
            'Off Set' => 17,
            'Palomas' => 18,
            'Rebajes' => 19,
            'Grabado' => 20,
            'Operacion Equipo' => 21,
            'Embudo CM' => 22,
            'Primera Operacion Cabeza Soplo' => 23,
            'Segunda Operacion Cabeza Soplo' => 24,
        ];

        uksort($reporteFinal, function ($a, $b) use ($prioridadProcesos) {
            $pA = $prioridadProcesos[$a] ?? 99;
            $pB = $prioridadProcesos[$b] ?? 99;
            return $pA <=> $pB;
        });

        return $reporteFinal;
    }

    /**
     * Recupera las observaciones del operador desde las tablas específicas de cada proceso.
     * Lógica adaptada de PzasGeneralesController/ProcessProductionController.
     * @param Pieza $pieza
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

        /**
     * @param mixed $process
     */
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

        /**
     * @param mixed $process
     */
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

        /**
     * @param mixed $piece
     */
    private function verifyPiece($piece): bool
    {
        if (!$piece)
            return false;

        // PTA-specific: only Fundicion blocks
        if ($piece->proceso === 'Soldadura PTA') {
            if (in_array($piece->liberacion, [2, 5]))
                return false;
            if (in_array($piece->error, ['Fundicion', 'Fundición']) && !in_array($piece->liberacion, [1, 3]))
                return false;
            return true;
        }

        if ($piece->liberacion == 1 || $piece->liberacion == 3)
            return true;
        if ($piece->liberacion == 0 && ($piece->error == 'Ninguno' || empty($piece->error)))
            return true;
        return false;
    }

    /**
     * Mapeamos el color en Hex de acuerdo con admin_pieces.js
     * @param int|string $status
     * @param string|null $error
     * @param string $process
     */
    private function asignColorTr($status, $error, $process = '')
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
                // PTA: only Fundicion stays purple
                if ($process === 'Soldadura PTA' && !str_contains((string) $error, 'Fundicion') && !str_contains((string) $error, 'Fundición')) {
                    return "#90EE90";
                }
                return "#DDA0DD";
            case 5:
                return "#FFD700";
            default:
                if (str_contains((string) $error, "Incompleto"))
                    return "#FFD700";
                elseif ($error === "Ninguno" || empty($error))
                    return "#90EE90";
                else {
                    if ($process === 'Soldadura PTA' && !str_contains((string) $error, 'Fundicion') && !str_contains((string) $error, 'Fundición')) {
                        return "#90EE90";
                    }
                    return "#DDA0DD";
                }
        }
    }
}
