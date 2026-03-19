<?php

namespace App\Console\Commands;

use App\Mail\ReporteDiarioMail;
use App\Models\Clase;
use App\Models\Moldura;
use App\Models\Orden_trabajo;
use App\Models\Pieza;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class EnviarReporteDiario extends Command
{
    /**
     * Firma del comando.
     * Uso normal:  php artisan reporte:enviar-diario
     * Modo prueba: php artisan reporte:enviar-diario --test
     * Fecha esp.:  php artisan reporte:enviar-diario --fecha=2025-03-15
     */
    protected $signature = 'reporte:enviar-diario
                            {--test : Envía el correo solo al remitente (modo prueba)}
                            {--fecha= : Fecha específica YYYY-MM-DD (por defecto hoy)}';

    protected $description = 'Genera y envía el Reporte General de Producción diario por correo electrónico';

    public function handle(): int
    {
        // ── 1. Determinar fecha ───────────────────────────────────────────
        $fechaStr = $this->option('fecha');
        $fecha = $fechaStr ? Carbon::parse($fechaStr) : Carbon::today();

        $this->info("Generando reporte para: {$fecha->toDateString()}");

        // ── 2. Consultar piezas del día ───────────────────────────────────
        $piezasDelDia = Pieza::with(['clase', 'operador', 'ordenTrabajo'])
            ->whereDate('created_at', $fecha)
            ->orderBy('id_ot')
            ->orderBy('id_clase')
            ->orderBy('created_at')
            ->get();

        if ($piezasDelDia->isEmpty()) {
            $this->warn("No se encontraron registros para {$fecha->toDateString()}. No se enviará correo.");
            return self::SUCCESS;
        }

        $this->info("Se encontraron {$piezasDelDia->count()} registros. Agrupando...");

        // ── 3. Agrupar: OT → Clase → Proceso → Operadores ────────────────
        $reporte = $this->agruparJerarquicamente($piezasDelDia);

        // ── 3.5. Generar PDFs por OT/Clase ────────────────────────────────
        $pdfPaths = [];
        $baseDir = storage_path('app/public/reportes');

        foreach ($reporte as $otId => $otData) {
            $nOT = str_replace(['#', 'OT ', ' ', '—'], ['', '', '_', ''], $otData['ot_label']);
            $nOT = preg_replace('/[^A-Za-z0-9_\-]/', '', $nOT); // Sanitizar nombre OT

            foreach ($otData['clases'] as $claseId => $claseData) {
                $nClase = str_replace(['#', 'Clase ', ' '], ['', '', '_'], $claseData['clase_label']);
                $nClase = preg_replace('/[^A-Za-z0-9_\-]/', '', $nClase); // Sanitizar nombre Clase

                $folderPath = "{$baseDir}/{$nOT}/{$nClase}";
                if (!file_exists($folderPath)) {
                    mkdir($folderPath, 0755, true);
                }

                $fileName = "{$fecha->toDateString()}.pdf";
                $fullPath = "{$folderPath}/{$fileName}";

                // Construimos un "mini-reporte" solo con esta OT y Clase para el PDF
                $miniReporte = [
                    $otId => [
                        'ot_label' => $otData['ot_label'],
                        'clases' => [
                            $claseId => $claseData
                        ]
                    ]
                ];

                $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('emails.reporte_diario_pdf', [
                    'reporte' => $miniReporte,
                    'fecha' => $fecha
                ]);
                $pdf->setPaper('a4', 'portrait');
                $pdf->save($fullPath);

                $pdfPaths[] = $fullPath;
                $this->info("PDF generado: {$fullPath}");
            }
        }

        // ── 4. Determinar destinatarios ───────────────────────────────────
        $destinatarios = $this->obtenerDestinatarios();

        if ($this->option('test')) {
            $destinatarios = [config('mail.from.address')];
            $this->warn("MODO PRUEBA: correo enviado solo a " . implode(', ', $destinatarios));
        }

        // ── 5. Enviar ─────────────────────────────────────────────────────
        $enviados = 0;
        foreach ($destinatarios as $correo) {
            try {
                Mail::to(trim($correo))->send(new ReporteDiarioMail($reporte, $fecha, $pdfPaths));
                $this->info("✓ Enviado a: {$correo}");
                $enviados++;
            } catch (\Throwable $e) {
                $this->error("✗ Error enviando a {$correo}: " . $e->getMessage());
            }
        }

        $this->info("Reporte diario completado. Enviados: {$enviados}/" . count($destinatarios));
        return self::SUCCESS;
    }

    /**
     * Agrupa los registros en la jerarquía:
     * OT → Clase → Proceso → [ filas de operadores ]
     */
    private function agruparJerarquicamente($piezas): array
    {
        $reporte = [];
        $molduras = [];
        $usuarios = [];

        foreach ($piezas as $pieza) {
            // ── Nivel 1: OT ───────────────────────────────────────────────
            $otId = $pieza->id_ot;
            if (!isset($reporte[$otId])) {
                if (!isset($molduras[$otId])) {
                    $ot = Orden_trabajo::find($otId);
                    $mn = $ot
                        ? optional(Moldura::find($ot->id_moldura))->nombre ?? 'Sin Moldura'
                        : 'Sin Moldura';
                    $molduras[$otId] = "OT #{$otId} — {$mn}";
                }
                $reporte[$otId] = [
                    'ot_label' => $molduras[$otId],
                    'clases' => [],
                ];
            }

            // ── Nivel 2: Clase ────────────────────────────────────────────
            $claseId = $pieza->id_clase;
            if (!isset($reporte[$otId]['clases'][$claseId])) {
                $cls = Clase::find($claseId);
                $reporte[$otId]['clases'][$claseId] = [
                    'clase_label' => $cls
                        ? trim($cls->nombre . ' ' . $cls->tamanio)
                        : "Clase #{$claseId}",
                    'procesos' => [],
                ];
            }

            // ── Nivel 3: Proceso ──────────────────────────────────────────
            $proceso = $pieza->proceso ?? 'Sin Proceso';
            if (!isset($reporte[$otId]['clases'][$claseId]['procesos'][$proceso])) {
                $reporte[$otId]['clases'][$claseId]['procesos'][$proceso] = [];
            }

            // ── Nivel 4: Operador ─────────────────────────────────────────
            $mat = $pieza->id_operador;
            if (!isset($usuarios[$mat])) {
                $u = User::where('matricula', $mat)->first();
                $usuarios[$mat] = $u
                    ? trim("{$u->nombre} {$u->a_paterno} {$u->a_materno}")
                    : "Operador #{$mat}";
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

            $obsOperador = $this->getObservacionesOperador($pieza->proceso, $pieza->id_clase, $pieza->id_ot, $identificador);

            if ($esJuego) {
                // Es parte de un juego (termina en H o M)
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
     * Lee destinatarios de .env (REPORT_RECIPIENTS=a@b.com,c@d.com)
     */
    private function obtenerDestinatarios(): array
    {
        $raw = env('REPORT_RECIPIENTS', config('mail.from.address'));
        return array_filter(array_map('trim', explode(',', $raw)));
    }

    /**
     * Recupera las observaciones del operador desde las tablas específicas de cada proceso.
     */
    private function getObservacionesOperador($procesoRaw, $claseId, $otId, $nJuego): string
    {
        try {
            $clase = Clase::find($claseId);
            if (!$clase)
                return '—';

            // Normalizar nombre del proceso (ej. "Operacion Equipo_1 operacion" -> "Operacion Equipo")
            $processString = str_contains($procesoRaw, "Operacion Equipo") ? "Operacion Equipo" : $procesoRaw;

            // Construir ID de proceso (ej. "Cepillado_Clase_OT")
            // Usamos el nombre del proceso original con guiones bajos para que coincida con la tabla de encabezados (headers)
            $processPrefix = str_replace(" ", "_", $procesoRaw);
            $idProcessStr = $processPrefix . "_" . $clase->nombre . "_" . $otId;

            $modelPieces = $this->getModelProcessPieces($processString);
            $modelHeader = $this->getModelProcess($processString);

            if (!$modelPieces || !$modelHeader)
                return '—';

            $header = $modelHeader::where('id_proceso', $idProcessStr)->first();
            if (!$header)
                return '—';

            // Normalización de número de pieza para procesos de ensamble
            // (Barreno Maniobra/Profundidad, Soldadura, Rectificado, Asentado, etc. guardan obs por Juego)
            $assemblyProcesses = [
                'Barreno Maniobra',
                'Soldadura',
                'Soldadura PTA',
                'Rectificado',
                'Asentado',
                'Barreno Profundidad',
                'Palomas',
                'Rebajes',
                'Grabado',
                'Operacion Equipo',
                'Operacion Equipo_1 operacion',
                'Operacion Equipo_2 operacion'
            ];

            $searchNJuego = $nJuego;
            if (in_array($procesoRaw, $assemblyProcesses) || in_array($processString, $assemblyProcesses)) {
                // Si la pieza es H o M, convertimos a J (ej: 101H -> 101J)
                $cleanNum = preg_replace('/[HMJ]$/', '', $nJuego);
                $searchNJuego = $cleanNum . "J";
            }

            $pieces = $modelPieces::where('id_proceso', $header->id)
                ->where(function ($query) use ($searchNJuego) {
                    $query->where('n_juego', $searchNJuego)
                        ->orWhere('n_pieza', $searchNJuego)
                        ->orWhere('id_pza', $searchNJuego);
                })
                ->get();

            $obs = [];
            foreach ($pieces as $p) {
                if (!empty($p->observaciones) && $p->observaciones !== '—') {
                    $obs[] = $p->observaciones;
                }
            }

            $obs = array_unique($obs);
            return empty($obs) ? '—' : implode(' | ', $obs);
        } catch (\Exception $e) {
            return '—';
        }
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
