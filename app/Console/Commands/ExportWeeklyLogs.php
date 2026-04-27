<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\SystemLog;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ExportWeeklyLogs extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:export-logs-weekly';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Exporta los logs de la semana a un archivo TXT y limpia la tabla SystemLog';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Iniciando exportación semanal de logs organizada por operador...');

        $allLogs = SystemLog::all();

        if ($allLogs->isEmpty()) {
            $this->info('No hay logs para exportar.');
            return;
        }

        $now = Carbon::now();
        $dateStr = $now->format('d-m-Y');
        
        // Agrupar logs por matrícula de operador
        $groupedLogs = $allLogs->groupBy('user_matricula');
        $totalExported = 0;

        foreach ($groupedLogs as $matricula => $logs) {
            // Obtener nombre del operador
            $user = \App\Models\User::where('matricula', $matricula)->first();
            $operatorName = $user ? "{$user->nombre} {$user->a_paterno} {$user->a_materno}" : "Operador_{$matricula}";
            $safeOperatorName = str_replace(['/', '\\', ':', '*', '?', '"', '<', '>', '|'], '', $operatorName); // Limpiar nombre para carpeta

            $fileName = "Reporte de log semanal [{$dateStr}] del Operador [{$safeOperatorName}].txt";
            
            // Formatear contenido con el orden de columnas solicitado por el USER (separado por espacios/pipes)
            $headerArr = ["Fecha", "Hora", "Operador", "Acción", "Detalles", "Orden de Trabajo", "N# Juego", "Hora de Inicio", "Hora de Término", "Tiempo Total", "Clase", "Proceso", "Máquina"];
            $content = implode(' | ', $headerArr) . "\n";

            foreach ($logs as $log) {
                // Calcular Tiempo Total (H_Inicio vs H_Termino)
                $tiempoTotal = 'N/A';
                if ($log->h_inicio && $log->h_termino && $log->h_inicio != 'N/A' && $log->h_termino != 'N/A') {
                    try {
                        $start = Carbon::parse($log->h_inicio);
                        $end = Carbon::parse($log->h_termino);
                        $diff = $start->diff($end);
                        $tiempoTotal = $diff->format('%H:%I:%S');
                    } catch (\Exception $e) {
                        $tiempoTotal = 'N/A';
                    }
                }

                $row = [
                    $log->created_at->format('Y-m-d'),
                    $log->created_at->format('H:i:s'),
                    "{$matricula} - {$operatorName}", // Matricula y Nombre completo del operador
                    $log->action ?? 'N/A',
                    str_replace([" | ", "\n", "\r"], [" ", " ", ""], $log->details ?? 'N/A'),
                    $log->ot ?? 'N/A',
                    $log->n_pieza ?? 'N/A', // Se usa n_pieza como N# Juego
                    $log->h_inicio ?? 'N/A',
                    $log->h_termino ?? 'N/A',
                    $tiempoTotal,
                    $log->clase ?? 'N/A',
                    $log->proceso ?? 'N/A',
                    $log->maquina ?? 'N/A'
                ];
                
                $content .= implode(' | ', $row) . "\n";
            }

            // Guardar archivo en storage/app/logs_backups/{OperatorName}/{Date}/
            $subDirectory = "logs_backups/" . trim($safeOperatorName);
            if (!Storage::exists($subDirectory)) {
                Storage::makeDirectory($subDirectory);
            }

            $filePath = "{$subDirectory}/{$fileName}";
            Storage::put($filePath, $content);
            $totalExported++;
            
            $this->info("Exportado: {$safeOperatorName}");
        }

        $this->info("Se han generado {$totalExported} archivos de reporte.");

        // Depuración de la tabla
        $count = $allLogs->count();
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        SystemLog::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $this->info("Se han depurado {$count} registros de la tabla system_logs.");
        
        \Illuminate\Support\Facades\Log::info("Exportación semanal completada. Archivos generados: {$totalExported}. Registros eliminados: {$count}");
    }
}
