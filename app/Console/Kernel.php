<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // ── Tareas existentes ──────────────────────────────────────────
        // $schedule->command('inspire')->hourly();
        $schedule->command('qr:cancelar-vencidos')->daily();

        // ── Reporte Diario de Producción — 23:59 todos los días ────────
        $schedule->command('reporte:enviar-diario')
            ->dailyAt('23:59')
            ->withoutOverlapping()       // evita ejecuciones duplicadas
            ->appendOutputTo(storage_path('logs/reporte_diario.log'));
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
