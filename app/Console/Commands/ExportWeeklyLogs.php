<?php
 
namespace App\Console\Commands;
 
use Illuminate\Console\Command;
use App\Models\SystemLog;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
 
class ExportWeeklyLogs extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:depurar-logs';
 
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Exporta los logs agrupados semanalmente en PDF y limpia la tabla SystemLog (el día 24 de cada mes)';
 
    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Desactivar el límite de tiempo de ejecución de PHP para evitar cortes en depuraciones masivas
        set_time_limit(0);
        // Elevar el límite de memoria a 1GB para asegurar suficiente espacio de procesamiento
        ini_set('memory_limit', '1024M');
 
        $this->info('Iniciando exportación periódica y depuración de logs en formato PDF...');
 
        $allLogs = SystemLog::all();
 
        if ($allLogs->isEmpty()) {
            $this->info('No hay logs para exportar.');
            return;
        }
 
        $now = Carbon::now();
        $currentDate = $now->format('d-m-Y');
 
        // Definición de acciones de Administrador
        $adminActions = [
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
 
        $monthsSpanish = [
            1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril',
            5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto',
            9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre'
        ];
 
        // Cargar todos los usuarios involucrados en una sola consulta indexada para evitar el problema N+1
        $matriculas = $allLogs->pluck('user_matricula')->filter()->unique()->toArray();
        $usersMap = \App\Models\User::query()->whereIn('matricula', $matriculas)->get()->keyBy('matricula');
 
        // Agrupación multidimensional: $grouped[Año][Mes][Semana][Tipo][] = $log
        $grouped = [];
 
        foreach ($allLogs as $log) {
            $year = $log->created_at->year;
            $month = $monthsSpanish[$log->created_at->month] ?? 'Mes';
            $weekNum = $log->created_at->weekOfMonth;
            $weekKey = "Semana_" . $weekNum;
 
            // Clasificación de tipo de log: Admin o Produccion
            $type = 'Produccion';
            $matricula = $log->user_matricula;
 
            if ($matricula && $usersMap->has($matricula)) {
                $user = $usersMap->get($matricula);
                if ($user && in_array($user->perfil, [1, 3])) {
                    $type = 'Admin';
                }
            }
 
            // Si la acción está catalogada como administrativa, forzar tipo Admin
            if (in_array($log->action, $adminActions)) {
                $type = 'Admin';
            }
 
            $grouped[$year][$month][$weekKey][$type][] = $log;
        }
 
        $totalPdfsGenerated = 0;
        $totalDeleted = 0;
 
        try {
            foreach ($grouped as $year => $months) {
                foreach ($months as $month => $weeks) {
                    foreach ($weeks as $weekKey => $types) {
                        foreach ($types as $type => $logs) {
                            $this->info("Procesando {$year}/{$month}/{$weekKey} - Tipo: {$type} (Logs: " . count($logs) . ")...");
 
                            // Chunks de 300 logs para prevenir consumo excesivo de memoria en DomPDF (tablas grandes con muchas columnas)
                            $logChunks = array_chunk($logs, 300);
                            $totalChunks = count($logChunks);
 
                            foreach ($logChunks as $chunkIndex => $chunkLogs) {
                                $partNumber = $totalChunks > 1 ? ($chunkIndex + 1) : null;
                                $pdfLogs = [];
 
                                foreach ($chunkLogs as $log) {
                                    // Calcular Tiempo Total
                                    $tiempoTotal = 'N/A';
                                    if ($log->h_inicio && $log->h_termino && $log->h_inicio !== 'N/A' && $log->h_termino !== 'N/A') {
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
 
                                    // Obtener nombre completo del operador mapeado desde la memoria caché de consulta
                                    $user = $log->user_matricula ? $usersMap->get($log->user_matricula) : null;
                                    $operatorName = $user ? "{$user->nombre} {$user->a_paterno}" : 'Sistema';
 
                                    $pdfLogs[] = [
                                        'date' => $log->created_at->format('Y-m-d'),
                                        'time' => $log->created_at->format('H:i:s'),
                                        'hora_inicio' => $log->h_inicio ?? 'N/A',
                                        'hora_termino' => $log->h_termino ?? $log->created_at->format('H:i:s'),
                                        'operador' => $log->user_matricula ?? 'N/A',
                                        'operador_nombre' => $operatorName,
                                        'action' => $log->action,
                                        'details' => $log->details,
                                        'ot' => $log->ot ?? 'N/A',
                                        'clase' => $log->clase ?? 'N/A',
                                        'proceso' => $log->proceso ?? 'N/A',
                                        'maquina' => $log->maquina ?? 'N/A',
                                        'n_juego' => ($log->n_pieza && preg_match('/[HM]$/i', $log->n_pieza)) ? preg_replace('/[HM]$/i', 'J', $log->n_pieza) : ($log->n_pieza ?? 'N/A'),
                                        'tiempo_total' => $tiempoTotal,
                                        'is_suspicious' => ($log->action === 'Captura Sospechosa' || $log->action === 'Captura Crítica'),
                                    ];
                                }
 
                                $isAdminOnly = ($type === 'Admin');
                                $selectedItems = [
                                    'ot' => 'Todos',
                                    'clase' => 'Todos',
                                    'operador' => 'Todos',
                                    'maquina' => 'Todos',
                                    'proceso' => 'Todos',
                                    'audit_status' => 'Todos',
                                    'action' => 'Todos',
                                    'dateFrom' => '',
                                    'dateTo' => '',
                                    'n_pieza' => 'Todos'
                                ];
 
                                // Cargar vista PDF con indicador de parte
                                $pdf = Pdf::loadView('reports.systemLogsPdf', [
                                    'logsRender'   => $pdfLogs,
                                    'selectedItems' => $selectedItems,
                                    'isAdminOnly'  => $isAdminOnly,
                                    'partNumber'   => $partNumber,
                                ]);
 
                                // Construir ruta: LOGS_GIS/YEAR/MONTH/Semana_X/PDF/
                                $directoryPath = "LOGS_GIS/{$year}/{$month}/{$weekKey}/PDF";
 
                                if (!Storage::disk('local')->exists($directoryPath)) {
                                    Storage::disk('local')->makeDirectory($directoryPath);
                                }
 
                                // Nombre del archivo: Log_GIS_FechaActual_(Admin/Produccion)_Parte_X.pdf
                                $partSuffix = $partNumber ? "_Parte_{$partNumber}" : "";
                                $fileName = "Log_GIS_{$currentDate}_{$type}{$partSuffix}.pdf";
                                $filePath = "{$directoryPath}/{$fileName}";
 
                                // Guardar PDF en almacenamiento
                                Storage::disk('local')->put($filePath, $pdf->output());
                                $totalPdfsGenerated++;
 
                                $this->info("Guardado: storage/app/{$filePath}");

                                // Eliminar de forma incremental los registros que ya se guardaron en el PDF
                                $logIds = collect($chunkLogs)->pluck('id')->toArray();
                                SystemLog::query()->whereIn('id', $logIds)->delete();
                                $totalDeleted += count($logIds);
                                $this->info("Depurados " . count($logIds) . " registros de la base de datos.");
 
                                // Destruir el objeto y forzar recolección de basura para liberar RAM
                                unset($pdf);
                                gc_collect_cycles();
                            }
                        }
                    }
                }
            }
        } catch (\Throwable $e) {
            $this->error("Fallo durante la exportación de PDFs: " . $e->getMessage());
            \Illuminate\Support\Facades\Log::error('[ExportWeeklyLogs] Error en la generación del respaldo de logs', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return self::FAILURE;
        }
 
        $this->info("Generación de respaldos completada. Se crearon {$totalPdfsGenerated} archivos PDF.");
 
        $this->info("Se han depurado {$totalDeleted} registros de la tabla system_logs de forma incremental.");
        \Illuminate\Support\Facades\Log::info("Depuración de logs completada con éxito. PDFs generados: {$totalPdfsGenerated}. Registros eliminados: {$totalDeleted}");
 
        return self::SUCCESS;
    }
}
