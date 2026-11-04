<?php

namespace App\Console\Commands;

use App\Models\ViewPerformanceLog;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class PruneViewPerformanceLogs extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'logs:prune-view-performance {--days=3 : Días de retención de logs} {--truncate : Vaciar la tabla completamente sin importar la antigüedad}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Elimina registros antiguos de la tabla view_performance_logs para mantener un histórico de rendimiento óptimo';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        if ($this->option('truncate')) {
            $total = ViewPerformanceLog::count();
            ViewPerformanceLog::truncate();
            $msg = "Tabla view_performance_logs vaciada completamente ({$total} registros eliminados).";
            $this->warn($msg);
            Log::info("Mantenimiento: {$msg}");

            return 0;
        }

        $days = (int) $this->option('days');
        if ($days < 1) {
            $days = 3;
        }

        $cutoffDate = Carbon::now()->subDays($days);
        $deleted = ViewPerformanceLog::where('created_at', '<', $cutoffDate)->delete();

        $msg = "Depuración de view_performance_logs completada: {$deleted} registros eliminados (anteriores a {$cutoffDate->toDateTimeString()}, retención de {$days} días).";
        $this->info($msg);

        if ($deleted > 0) {
            Log::info("Mantenimiento: {$msg}");
        }

        return 0;
    }
}

