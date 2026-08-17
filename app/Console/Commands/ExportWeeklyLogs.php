<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\SystemLog;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Barryvdh\DomPDF\Facade\Pdf;

class ExportWeeklyLogs extends Command
{
    /**
     * Firma y nombre del comando Artisan.
     * Acepta dos opciones opcionales:
     *   --dry-run : Simula el proceso SIN generar PDFs ni borrar registros.
     *   --chunk=N : Tamaño de lote por PDF (default: 300).
     *
     * @var string
     */
    protected $signature = 'app:depurar-logs
                            {--dry-run : Simula el proceso sin generar PDFs ni borrar registros de la BD}
                            {--chunk=300 : Número de logs por PDF generado}';

    /**
     * Descripción del comando.
     * @var string
     */
    protected $description = 'Exporta los logs del sistema agrupados por semana/tipo en PDF y los elimina de la BD de forma incremental. Soporta volúmenes masivos (>50k) sin agotar la RAM. Usa --dry-run para simular sin borrar.';

    /**
     * Acciones que se clasifican como del tipo "Admin".
     */
    const ADMIN_ACTIONS = [
        'Inicio de Sesión',
        'Cierre de Sesión',
        'Cargo de OT',
        'Cargo de Clase de OT',
        'Modificación de OT',
        'Cargo/Modificación Cotas Nominales',
        'Desocupación de Máquina',
        'Subida de Dibujo',
        'Eliminación de Dibujo',
        'Reemplazo de Dibujo',
        'Creación de Carpeta',
        'Subida de Manual',
        'Eliminación de Manual',
        'Reemplazo de Manual',
        'Subida de Ayuda Visual',
        'Eliminación de Ayuda Visual',
        'Reemplazo de Ayuda Visual',
        'Subida de Dibujo Fundición',
        'Eliminación de Dibujo Fundición',
        'Reemplazo de Dibujo Fundición',
        'Visualización de Dibujo',
        'Autorización de Edición',
    ];

    /**
     * Mapa de número de mes → nombre en español.
     */
    const MONTHS_ES = [
        1 => 'Enero',
        2 => 'Febrero',
        3 => 'Marzo',
        4 => 'Abril',
        5 => 'Mayo',
        6 => 'Junio',
        7 => 'Julio',
        8 => 'Agosto',
        9 => 'Septiembre',
        10 => 'Octubre',
        11 => 'Noviembre',
        12 => 'Diciembre'
    ];

    /**
     * Punto de entrada principal del comando.
     */
    public function handle()
    {
        // Sin límite de tiempo ni de memoria para procesos masivos de background
        set_time_limit(0);
        ini_set('memory_limit', '1024M');

        $startTime = microtime(true);
        $now = Carbon::now();
        $currentDate = $now->format('d-m-Y');
        $isDryRun = (bool) $this->option('dry-run');
        $chunkSize = max(50, (int) $this->option('chunk')); // mínimo 50 para no generar demasiados PDFs

        $this->line('');
        $this->info('╔══════════════════════════════════════════════════════════════╗');
        $this->info('║     SISTEMA GIS — Depuración y Exportación de Logs          ║');
        $this->info('╚══════════════════════════════════════════════════════════════╝');
        $this->line('  Inicio  : ' . $now->format('d/m/Y H:i:s'));
        $this->line("  Modo    : " . ($isDryRun ? '⚠  SIMULACIÓN (--dry-run) — Nada se borrará ni guardará' : '🔴 REAL — Los logs serán exportados y eliminados de la BD'));
        $this->line("  Chunk   : {$chunkSize} registros por PDF");
        $this->line('');

        if ($isDryRun) {
            $this->warn('  ► Modo --dry-run activo. Al finalizar verás el resumen de lo que SE HARÍA sin ejecutar nada real.');
            $this->line('');
        }

        // ── 1. VERIFICACIÓN PREVIA ─────────────────────────────────────────────
        $totalLogs = SystemLog::count();

        if ($totalLogs === 0) {
            $this->info('✓ No hay logs pendientes de exportar. La tabla está limpia.');
            Log::info('[GIS-Logs] Depuración ejecutada: tabla system_logs ya vacía.');
            return self::SUCCESS;
        }

        $estimatedPdfs = (int) ceil($totalLogs / $chunkSize);
        $this->info("📊 Total de registros en BD   : {$totalLogs} logs");
        $this->info("   PDFs estimados a generar   : ~{$estimatedPdfs}");
        $this->line('');

        // ── 2. PRE-CARGAR MAPA DE USUARIOS ────────────────────────────────────
        // Una sola consulta indexada para todos los operadores — usada durante todo el proceso
        $this->comment('Cargando catálogo de usuarios desde la base de datos...');
        $matriculas = SystemLog::query()
            ->select('user_matricula')
            ->whereNotNull('user_matricula')
            ->distinct()
            ->pluck('user_matricula')
            ->toArray();

        $usersMap = \App\Models\User::query()
            ->whereIn('matricula', $matriculas)
            ->select(['matricula', 'nombre', 'a_paterno', 'a_materno', 'perfil'])
            ->get()
            ->keyBy('matricula');

        $this->info("✓ Usuarios en caché: " . $usersMap->count() . " operadores.");

        // ── PRE-CALCULAR matriculas de Admin UNA SOLA VEZ ────────────────────
        // ¡IMPORTANTE! Esto evita hacer una subquery SQL en cada iteración de grupo/tipo.
        // Sin este pre-cálculo, se lanzan N consultas redundantes (N = grupos × 2 tipos).
        $adminMatriculas = \App\Models\User::query()
            ->whereIn('perfil', [1, 3])
            ->pluck('matricula')
            ->toArray();

        $this->info("✓ Matriculas Admin en caché: " . count($adminMatriculas) . " administradores.");
        $this->line('');

        // ── 3. IDENTIFICAR GRUPOS ÚNICOS (Sin cargar los logs en memoria) ─────
        $this->comment('Identificando grupos semana/tipo...');

        $groups = SystemLog::query()
            ->whereNotNull('created_at') // Excluir logs con fecha nula
            ->selectRaw("
                YEAR(created_at)               AS year,
                MONTH(created_at)              AS month,
                WEEK(created_at, 2)            AS week_in_month_approx,
                MIN(DATE(created_at))          AS day_sample,
                COUNT(*)                       AS group_total
            ")
            ->groupByRaw("YEAR(created_at), MONTH(created_at), WEEK(created_at, 2)")
            ->orderByRaw("YEAR(created_at), MONTH(created_at), WEEK(created_at, 2)")
            ->get();

        // Contar logs con created_at nulo para informar al usuario
        $nullDateCount = SystemLog::whereNull('created_at')->count();
        if ($nullDateCount > 0) {
            $this->warn("⚠  Se encontraron {$nullDateCount} log(s) con fecha nula. Serán procesados en un grupo separado al final.");
        }

        if ($groups->isEmpty() && $nullDateCount === 0) {
            $this->warn('⚠  No se encontraron grupos de logs con fechas válidas.');
            return self::SUCCESS;
        }

        $this->info("✓ Semanas identificadas: " . $groups->count() . " grupo(s) a procesar.");
        $this->line('');

        // ── 4. PROCESAMIENTO POR GRUPO Y CHUNK ────────────────────────────────
        $totalPdfsGenerated = 0;
        $totalDeleted = 0;
        $totalErrors = 0;
        $skippedNullIds = [];

        foreach ($groups as $group) {
            $year = $group->year;
            $month = $group->month;
            $monthName = self::MONTHS_ES[$month] ?? "Mes_{$month}";

            // Calcular número de semana dentro del mes (1-5) a partir de un día de muestra
            $sampleDate = Carbon::parse($group->day_sample);
            $weekInMonth = (int) ceil($sampleDate->day / 7);
            $weekKey = "Semana_{$weekInMonth}";

            foreach (['Admin', 'Produccion'] as $type) {

                // ── Query base: filtrar por semana/mes/año ─────────────────────
                $baseQuery = SystemLog::query()
                    ->whereNotNull('created_at')
                    ->whereRaw("YEAR(created_at) = ?", [$year])
                    ->whereRaw("MONTH(created_at) = ?", [$month])
                    ->whereRaw("WEEK(created_at, 2) = ?", [$group->week_in_month_approx]);

                // ── Filtrar por tipo usando las matriculas pre-calculadas ──────
                if ($type === 'Admin') {
                    $baseQuery->where(function ($q) use ($adminMatriculas) {
                        $q->whereIn('action', self::ADMIN_ACTIONS)
                            ->orWhereIn('user_matricula', $adminMatriculas);
                    });
                } else {
                    $baseQuery->whereNotIn('action', self::ADMIN_ACTIONS);
                    if (!empty($adminMatriculas)) {
                        $baseQuery->whereNotIn('user_matricula', $adminMatriculas);
                    }
                }

                $groupCount = (clone $baseQuery)->count();

                if ($groupCount === 0) {
                    continue;
                }

                $this->line("─────────────────────────────────────────────────────────────");
                $this->info("📁 {$year}/{$monthName}/{$weekKey} — Tipo: {$type} ({$groupCount} registros)");

                $totalChunksInGroup = (int) ceil($groupCount / $chunkSize);
                $chunkIndex = 0;

                // lazyById() es más seguro que chunk() cuando se borran registros en cada iteración:
                // chunk() recalcula OFFSET y puede saltar filas al paginar sobre una tabla que se está achicando.
                // lazyById() usa WHERE id > $lastSeenId, por lo que nunca pierde registros.
                $lazyQuery = (clone $baseQuery)->orderBy('id')->lazyById($chunkSize);
                $chunkBuffer = collect();

                $processChunkBuffer = function () use (
                    &$chunkBuffer, &$totalPdfsGenerated, &$totalDeleted, &$totalErrors,
                    $year, $monthName, $weekKey, $type, $currentDate,
                    $usersMap, $totalChunksInGroup, &$chunkIndex, $isDryRun
                ) {
                    if ($chunkBuffer->isEmpty()) return;

                    $chunkLogs = $chunkBuffer;
                    $chunkBuffer = collect();

                    $chunkIndex++;
                    $partNumber = $totalChunksInGroup > 1 ? $chunkIndex : null;
                    $partLabel = $partNumber ? " (Parte {$partNumber}/{$totalChunksInGroup})" : '';

                    $this->comment("  ↳ Lote{$partLabel}: {$chunkLogs->count()} logs" . ($isDryRun ? ' [SIMULADO]' : ''));

                    if ($isDryRun) {
                        $totalPdfsGenerated++;
                        $totalDeleted += $chunkLogs->count();
                        return;
                    }

                    // ── Formatear logs + pre-calcular activeFamilies en PHP ────────────
                    // activeFamilies se calcula AQUÍ para evitar la doble iteración dentro del template
                    // Blade del PDF (antes el template hacía foreach $logsRender dos veces: una para
                    // la tabla y otra para decidir qué familias de color mostrar en la leyenda).
                    $pdfLogs = [];
                    $activeFamilies = [
                        'azul' => false, 'verde' => false,
                        'amarillo' => false, 'morado' => false, 'rojo' => false
                    ];

                    foreach ($chunkLogs as $log) {
                        $tiempoTotal = 'N/A';
                        if (
                            $log->h_inicio && $log->h_termino
                            && $log->h_inicio !== 'N/A' && $log->h_termino !== 'N/A'
                        ) {
                            try {
                                $start = Carbon::parse($log->h_inicio);
                                $end = Carbon::parse($log->h_termino);
                                $tiempoTotal = $start->diff($end)->format('%H:%I:%S');
                            } catch (\Exception $e) {
                                $tiempoTotal = '00:00:00';
                            }
                        } elseif ($log->h_inicio === $log->h_termino) {
                            $tiempoTotal = '00:00:00';
                        }

                        $user = $log->user_matricula ? $usersMap->get($log->user_matricula) : null;
                        $operatorName = $user ? trim("{$user->nombre} {$user->a_paterno}") : 'Sistema';
                        $isSuspicious = in_array($log->action, ['Captura Sospechosa', 'Captura Crítica']);

                        // Clasificar familia de color — una sola pasada, sin re-iterar en Blade
                        $action = $log->action;
                        if (in_array($action, ['Inicio de Sesión', 'Nuevo reporte', 'Inicio de Reporte', 'Cierre de Sesión', 'Login Inspector Calidad', 'Carga de Formulario de Producción', 'Selección de Pieza', 'Selección de OT', 'Selección de Clase', 'Selección de Proceso', 'Nueva Meta Creada', 'Ingreso a Meta Existente'])) {
                            $activeFamilies['azul'] = true;
                        }
                        if (in_array($action, ['Proceso Correcto', 'Captura Medida', 'Liberación por Calidad', 'Terminar Reporte']) && !$isSuspicious) {
                            $activeFamilies['verde'] = true;
                        }
                        if (in_array($action, ['Consulta Dibujos Técnicos', 'Solicitud Edición de Piezas', 'Intento de Liberación'])) {
                            $activeFamilies['morado'] = true;
                        }
                        if (in_array($action, ['Consulta Documentación Técnica', 'Captura Sospechosa', 'Solicitud Edición de Reporte']) || ($action === 'Captura Medida' && $isSuspicious)) {
                            $activeFamilies['amarillo'] = true;
                        }
                        if (in_array($action, ['Exceso de Tiempo', 'Mensaje de Error', 'Rechazo por Calidad', 'Alerta de Error en Sistema', 'Avisos de Sistema', 'Intento de Login Fallido'])) {
                            $activeFamilies['rojo'] = true;
                        }

                        $pdfLogs[] = [
                            'date'            => $log->created_at->format('Y-m-d'),
                            'time'            => $log->created_at->format('H:i:s'),
                            'hora_inicio'     => $log->h_inicio ?? 'N/A',
                            'hora_termino'    => $log->h_termino ?? $log->created_at->format('H:i:s'),
                            'operador'        => $log->user_matricula ?? 'N/A',
                            'operador_nombre' => $operatorName,
                            'action'          => $action,
                            'details'         => $log->details,
                            'ot'              => $log->ot ?? 'N/A',
                            'clase'           => $log->clase ?? 'N/A',
                            'proceso'         => $log->proceso ?? 'N/A',
                            'maquina'         => $log->maquina ?? 'N/A',
                            'n_juego'         => ($log->n_pieza && preg_match('/[HM]$/i', $log->n_pieza))
                                                    ? preg_replace('/[HM]$/i', 'J', $log->n_pieza)
                                                    : ($log->n_pieza ?? 'N/A'),
                            'tiempo_total'    => $tiempoTotal,
                            'is_suspicious'   => $isSuspicious,
                        ];
                    }

                    // Generar PDF con reintentos automáticos (máx. 3 intentos)
                    $pdfSaved = false;
                    $attempt = 0;
                    $lastError = null;

                    while ($attempt < 3 && !$pdfSaved) {
                        $attempt++;
                        try {
                            // Opciones de velocidad para DomPDF:
                            // - enable_javascript: false  → elimina un paso de procesamiento innecesario en PDFs de tabla
                            // - enable_remote: false       → evita intentos de red que bloquean la ejecución
                            // - default_media_type: print  → carga solo estilos @print, ignorando @screen y @media screen
                            // - default_font: helvetica    → fuente base de PDF nativa, sin carga de archivo TTF
                            $pdf = Pdf::loadView('reports.systemLogsPdf', [
                                'logsRender'     => $pdfLogs,
                                'activeFamilies' => $activeFamilies, // pre-calculado en PHP, no en Blade
                                'selectedItems'  => [
                                    'ot' => 'Todos', 'clase' => 'Todos', 'operador' => 'Todos',
                                    'maquina' => 'Todos', 'proceso' => 'Todos',
                                    'audit_status' => 'Todos', 'action' => 'Todos',
                                    'dateFrom' => '', 'dateTo' => '', 'n_pieza' => 'Todos'
                                ],
                                'isAdminOnly'    => ($type === 'Admin'),
                                'partNumber'     => $partNumber,
                            ])->setOption('enable_javascript', false)
                              ->setOption('enable_remote', false)
                              ->setOption('default_media_type', 'print')
                              ->setOption('default_font', 'helvetica')
                              ->setOption('dpi', 96);

                            // Subcarpeta según tipo: ADMINS para administradores, PRODUCCION para operadores
                            $subFolder = ($type === 'Admin') ? 'ADMINS' : 'PRODUCCION';
                            $filePrefix = ($type === 'Admin') ? 'AD' : 'PR';
                            $directoryPath = "LOGS_GIS/{$year}/{$monthName}/{$weekKey}/{$subFolder}";
                            if (!Storage::disk('local')->exists($directoryPath)) {
                                Storage::disk('local')->makeDirectory($directoryPath);
                            }

                            $partSuffix = $partNumber ? "_Parte_{$partNumber}" : "";
                            $fileName = "Log_GIS_{$currentDate}_{$filePrefix}{$partSuffix}.pdf";
                            $filePath = "{$directoryPath}/{$fileName}";

                            Storage::disk('local')->put($filePath, $pdf->output());

                            unset($pdf);
                            gc_collect_cycles();

                            $pdfSaved = true;
                            $totalPdfsGenerated++;
                            $this->info("  ✓ PDF guardado: storage/app/{$filePath}");

                        } catch (\Throwable $e) {
                            $lastError = $e->getMessage();
                            $this->warn("  ⚠  Intento {$attempt}/3 fallido: " . substr($lastError, 0, 120));
                            unset($pdf);
                            gc_collect_cycles();
                            sleep(2);
                        }
                    }

                    // Si todos los intentos fallaron → NO borrar estos logs, registrar en log
                    if (!$pdfSaved) {
                        $totalErrors++;
                        $logIds = $chunkLogs->pluck('id')->toArray();
                        $this->error("  ✗ No se pudo generar el PDF tras 3 intentos. Los {$chunkLogs->count()} registros NO fueron eliminados.");
                        Log::error('[GIS-Logs] Fallo al generar PDF tras 3 intentos.', [
                            'grupo' => "{$year}/{$monthName}/{$weekKey}/{$type}{$partLabel}",
                            'error' => $lastError,
                            'ids_muestra' => array_slice($logIds, 0, 10),
                        ]);

                        // Registrar el fallo en system_logs para trazabilidad dentro de la app
                        try {
                            SystemLog::create([
                                'action' => 'Error de Depuración',
                                'details' => "Fallo al generar PDF para {$year}/{$monthName}/{$weekKey}/{$type}{$partLabel}. Error: " . substr($lastError, 0, 250),
                                'maquina' => 'Sistema',
                            ]);
                        } catch (\Throwable $innerE) {
                            // Si hasta esto falla, solo loguear en Laravel
                            Log::warning('[GIS-Logs] No se pudo registrar el error en system_logs: ' . $innerE->getMessage());
                        }

                        return true; // Continuar con el siguiente chunk sin abortar
                    }

                    // Eliminación incremental SOLO si el PDF fue guardado exitosamente
                    $logIds = $chunkLogs->pluck('id')->toArray();
                    $deleted = SystemLog::query()->whereIn('id', $logIds)->delete();
                    $totalDeleted += $deleted;
                    $this->line("  🗑  Eliminados {$deleted} registros de la BD ({$totalDeleted} en total).");

                    // Micro-pausa para no saturar el I/O del servidor en depuraciones masivas
                    usleep(100000); // 100ms
                }; // fin de $processChunkBuffer

                // Iterar con lazyById() y agrupar manualmente en buffers del tamaño del chunk
                foreach ($lazyQuery as $log) {
                    $chunkBuffer->push($log);
                    if ($chunkBuffer->count() >= $chunkSize) {
                        $processChunkBuffer();
                    }
                }
                // Procesar el último buffer parcial (si quedaron registros sobrantes)
                $processChunkBuffer();

                $this->line('');
            }
        }

        // ── 5. PROCESAR LOGS CON created_at NULO ─────────────────────────────
        if ($nullDateCount > 0 && !$isDryRun) {
            $this->line("─────────────────────────────────────────────────────────────");
            $this->warn("🗂  Procesando {$nullDateCount} logs con fecha nula...");

            SystemLog::whereNull('created_at')->orderBy('id')->chunk($chunkSize, function ($chunkLogs) use (&$totalDeleted, &$totalErrors, $currentDate, $isDryRun) {
                $logIds = $chunkLogs->pluck('id')->toArray();
                $deleted = SystemLog::whereIn('id', $logIds)->delete();
                $totalDeleted += $deleted;
                $this->line("  🗑  Eliminados {$deleted} registros sin fecha válida.");
            });
        }

        // ── 6. OPTIMIZE TABLE — Desfragmentar la BD después de borrar masivamente ──
        // InnoDB deja "huecos" en las páginas de datos tras DELETE masivos.
        // OPTIMIZE TABLE reorganiza los datos y libera el espacio físico en disco.
        // Esto no solo ahorra espacio sino que acelera futuras consultas de lectura/escritura.
        if (!$isDryRun && $totalDeleted > 0) {
            $this->comment('Optimizando tabla system_logs (desfragmentando espacio en disco)...');
            try {
                \Illuminate\Support\Facades\DB::statement('OPTIMIZE TABLE system_logs');
                $this->info('✓ Tabla system_logs optimizada exitosamente.');
            } catch (\Throwable $e) {
                $this->warn('⚠  No se pudo optimizar la tabla: ' . $e->getMessage());
            }
        }

        // ── 7. RESUMEN FINAL ───────────────────────────────────────────────────
        $elapsed = round(microtime(true) - $startTime, 1);
        $minutes = (int) floor($elapsed / 60);
        $seconds = (int) ($elapsed % 60);
        $remaining = SystemLog::count();

        $this->line('');
        $this->info('╔══════════════════════════════════════════════════════════════╗');
        $this->info($isDryRun
            ? '║             SIMULACIÓN COMPLETADA (nada fue modificado)      ║'
            : '║                  DEPURACIÓN COMPLETADA                       ║');
        $this->info('╚══════════════════════════════════════════════════════════════╝');
        $this->info("  ✅ PDFs " . ($isDryRun ? 'simulados' : 'generados') . "  : {$totalPdfsGenerated}");
        $this->info("  🗑  Registros " . ($isDryRun ? 'que se eliminarían' : 'eliminados') . ": {$totalDeleted}");
        if (!$isDryRun) {
            $this->info("  📋 Registros restantes en BD: {$remaining}");
        }
        if ($totalErrors > 0) {
            $this->warn("  ⚠  Chunks con error (no eliminados): {$totalErrors}");
        }
        $this->info("  ⏱  Tiempo total              : {$minutes}m {$seconds}s");
        $this->line('');

        Log::info('[GIS-Logs] Depuración ' . ($isDryRun ? 'simulada' : 'real') . ' completada.', [
            'modo' => $isDryRun ? 'dry-run' : 'real',
            'pdfs_generados' => $totalPdfsGenerated,
            'registros_eliminados' => $totalDeleted,
            'registros_restantes_bd' => $remaining,
            'errores' => $totalErrors,
            'duracion_seg' => $elapsed,
        ]);

        return $totalErrors > 0 ? self::FAILURE : self::SUCCESS;
    }
}