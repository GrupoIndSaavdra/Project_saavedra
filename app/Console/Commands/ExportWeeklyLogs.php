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
    protected $signature = 'app:depurar-logs';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Exporta los logs y limpia la tabla SystemLog (programado cada 3 días)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Iniciando exportación periódica de logs (cada 3 días) organizada por operador...');

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

            $fileName = "Reporte de log [{$dateStr}] del Operador [{$safeOperatorName}].txt";
            
            // Columnas solicitadas
            $headerArr = ["Fecha", "Hora", "Operador", "Acción", "Detalles", "Orden de Trabajo", "N# Juego", "Hora de Inicio", "Hora de Término", "Tiempo Total", "Clase", "Proceso", "Máquina"];
            
            // 1. Preparar datos de todas las filas y calcular anchos máximos
            $rowsData = [];
            $widths = array_map('mb_strlen', $headerArr);

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
                    (string)$log->created_at->format('Y-m-d'),
                    (string)$log->created_at->format('H:i:s'),
                    (string)"{$matricula} - {$operatorName}",
                    (string)($log->action ?? 'N/A'),
                    (string)str_replace([" | ", "\n", "\r"], [" ", " ", ""], $log->details ?? 'N/A'),
                    (string)($log->ot ?? 'N/A'),
                    (string)($log->n_pieza ?? 'N/A'),
                    (string)($log->h_inicio ?? 'N/A'),
                    (string)($log->h_termino ?? 'N/A'),
                    (string)$tiempoTotal,
                    (string)($log->clase ?? 'N/A'),
                    (string)($log->proceso ?? 'N/A'),
                    (string)($log->maquina ?? 'N/A')
                ];

                foreach ($row as $i => $val) {
                    $widths[$i] = max($widths[$i], mb_strlen($val));
                }
                $rowsData[] = $row;
            }

            // 2. Construir el contenido con anchos fijos
            $lines = [];
            
            // Fila de Encabezados
            $headerLine = [];
            foreach ($headerArr as $i => $title) {
                $headerLine[] = $this->pad($title, $widths[$i]);
            }
            $lines[] = implode(' | ', $headerLine);

            // Línea separadora de guiones
            $separatorLine = [];
            foreach ($widths as $w) {
                $separatorLine[] = str_repeat('-', $w);
            }
            $lines[] = implode('-|-', $separatorLine);

            // Filas de Datos
            foreach ($rowsData as $row) {
                $dataLine = [];
                foreach ($row as $i => $val) {
                    $dataLine[] = $this->pad($val, $widths[$i]);
                }
                $lines[] = implode(' | ', $dataLine);
            }

            $content = implode("\n", $lines) . "\n";

            // Guardar archivo en storage/app/System_Log Backup/{OperatorName}/
            $subDirectory = "System_Log Backup/" . trim($safeOperatorName);
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
        
        \Illuminate\Support\Facades\Log::info("Depuración de logs completada. Archivos generados: {$totalExported}. Registros eliminados: {$count}");
    }

    /**
     * Rellena con espacios un string multibyte para ancho fijo.
     */
    private function pad($str, $len)
    {
        $diff = $len - mb_strlen($str);
        return $str . str_repeat(' ', max(0, $diff));
    }
}
