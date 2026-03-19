<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     * 
     * NOTA: Cada vez que se descargue el proyecto en un nuevo entorno, 
     * asegúrate de ejecutar los siguientes comandos para que los logs funcionen:
     * 1. php artisan key:generate
     * 2. php artisan config:clear
     * 3. (Windows) Configurar el Programador de Tareas para ejecutar 'php artisan schedule:run' cada minuto.
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

        // ── Latido de Monitoreo — cada minuto ──────────────────────────
        $schedule->command('app:heartbeat')
            ->everyMinute()
            ->withoutOverlapping();
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
