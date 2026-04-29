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
        ini_set('memory_limit', '512M');
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

        // ── 3.5. Generar PDF global ────────────────────────────────
        $pdfPaths = [];
        $baseDir = storage_path('app/public/reportes');
        $folderPath = "{$baseDir}/General";
        if (!file_exists($folderPath)) {
            mkdir($folderPath, 0755, true);
        }
        $fullPath = "{$folderPath}/{$fecha->toDateString()}.pdf";
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('emails.reporte_diario_pdf', [
            'reporte' => $reporte,
            'fecha' => $fecha
        ]);
        $pdf->setPaper('a4', 'portrait');
        $pdf->save($fullPath);
        $pdfPaths[] = $fullPath;
        $this->info("PDF generado: {$fullPath}");


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
     *
     * @param \Illuminate\Support\Collection|\App\Models\Pieza[] $piezas
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

        // Índice global de mitades por juego: mismo patrón que $piezasIndex en saveInArray.
        // Permite detectar al procesar 87H si su partner 87M es de distinto operador.
        $globalJuegoIndex = []; // [hash][numBase]['H'|'M'] => $pieza
        foreach ($piezas as $pz) {
            if (preg_match('/^(\d+)([HM])$/i', $pz->n_pieza, $gm)) {
                $gh = $pz->id_ot . '_' . $pz->id_clase . '_' . ($pz->proceso ?? 'Sin Proceso');
                $globalJuegoIndex[$gh][$gm[1]][strtoupper($gm[2])] = $pz;
            }
        }

        foreach ($piezas as $pieza) {
            $mat = $pieza->id_operador;
            if (!isset($usuariosMap[$mat])) {
                $u = User::query()->where('matricula', '=', $mat, 'and')->first();
                $usuariosMap[$mat] = $u ? trim("{$u->nombre} {$u->a_paterno} {$u->a_materno}") : "Operador #{$mat}";
            }
            $operador = $usuariosMap[$mat];

            $otId = $pieza->id_ot;
            if (!isset($moldurasMap[$otId])) {
                $ot = Orden_trabajo::query()->find($otId, ['*']);
                $mn = $ot ? optional(Moldura::query()->find($ot->id_moldura, ['*']))->nombre ?? 'Sin Moldura' : 'Sin Moldura';
                $moldurasMap[$otId] = "OT #{$otId} — {$mn}";
            }

            $claseId = $pieza->id_clase;
            if (!isset($clasesMap[$claseId])) {
                $cls = Clase::query()->find($claseId, ['*']);
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
            $colorFila = $this->asignColorTr($pieza->liberacion, $pieza->error, $pieza->proceso ?? '');

            $nPiezaRaw = $pieza->n_pieza;
            $esJuego = str_ends_with($nPiezaRaw, 'H') || str_ends_with($nPiezaRaw, 'M');
            $numJuego = $esJuego ? substr($nPiezaRaw, 0, -1) : $nPiezaRaw;
            $identificador = $esJuego ? $numJuego . "J" : $nPiezaRaw;

            $obsOperador = $this->getObservacionesOperador($pieza->proceso, $pieza->id_clase, $pieza->id_ot, $identificador);

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
                
                // Fallback a DB si la otra mitad se hizo otro día y no está en el reporte actual
                if (!$partnerPza) {
                    $partnerPza = Pieza::query()->where('id_ot', '=', $pieza->id_ot, 'and')
                        ->where('id_clase', '=', $pieza->id_clase, 'and')
                        ->where('n_pieza', '=', "{$nPiezaBase}{$partnerSuf}", 'and')
                        ->where(function($q) use ($pieza) {
                            if ($pieza->proceso) { $q->where('proceso', '=', $pieza->proceso, 'and'); }
                            else { $q->whereNull('proceso'); }
                        })->orderBy('id', 'desc')->first();
                }

                $esCompartido = $partnerPza && $partnerPza->id_operador !== $pieza->id_operador;

                if ($esCompartido) {
                    $mOpPartner = $partnerPza->id_operador;
                    if (!isset($usuariosMap[$mOpPartner])) {
                        $uOp = User::query()->where('matricula', '=', $mOpPartner, 'and')->first();
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
                            // Forzamos ambas para que no se regrese a 'H' o 'M' al final
                            'piezas_incluidas'=> ['H', 'M'],
                            'maquina'         => $pieza->maquina ?? '—',
                        ];
                    }
                } else {
                    // Juego normal (mismo operador o incompleto) — lógica original
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
     * Lee destinatarios de .env (REPORT_RECIPIENTS=a@b.com,c@d.com)
     */
    private function obtenerDestinatarios(): array
    {
        $raw = env('REPORT_RECIPIENTS', config('mail.from.address'));
        return array_filter(array_map('trim', explode(',', $raw)));
    }

    /**
     * Recupera las observaciones del operador desde las tablas específicas de cada proceso.
     * 
     * @param string $procesoRaw
     * @param int|string $claseId
     * @param int|string $otId
     * @param string|null $nJuego
     */
    private function getObservacionesOperador($procesoRaw, $claseId, $otId, $nJuego): string
    {
        try {
            $clase = Clase::query()->find($claseId, ['*']);
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

            $header = $modelHeader::query()->where('id_proceso', '=', $idProcessStr, 'and')->first();
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

            $pieces = $modelPieces::query()->where('id_proceso', '=', $header->id, 'and')
                ->where(function ($query) use ($searchNJuego) {
                    $query->where('n_juego', '=', $searchNJuego, 'and')
                        ->orWhere('n_pieza', '=', $searchNJuego, 'and')
                        ->orWhere('id_pza', '=', $searchNJuego, 'and');
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

    /**
     * @param string $process
     * @return string|null
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
     * @param string $process
     * @return string|null
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
     * @param \App\Models\Pieza|null $piece
     * @return bool
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
     * Mapeamos el color en Hex de acuerdo con adminPieces.js
     * 
     * @param int|string $status
     * @param string $error
     * @param string $process
     * @return string
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
