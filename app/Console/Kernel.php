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
     * 3. Configurar el Cron / Programador de Tareas para ejecutar 'php artisan schedule:run' cada 5 minutos.
     *    IMPORTANTE: Todos los horarios en este Kernel deben ser múltiplos de 5 minutos (:00, :05, :10 ... :55).
     */
    protected function schedule(Schedule $schedule): void
    {
        // ── Tareas existentes ──────────────────────────────────────────
        // $schedule->command('inspire')->hourly();
        // $schedule->command('qr:cancelar-vencidos')->daily(); // Inactivo temporalmente

        // ── Reporte Diario de Producción — 23:50 todos los días ────────
        $schedule->command('reporte:enviar-diario')
            ->dailyAt('23:50')
            ->withoutOverlapping(60)
            ->appendOutputTo(storage_path('logs/reporte_diario.log'));

        // ── Alerta Pre-órdenes de Entrega — 08:00 ────────
        $schedule->command('alert:preorden-entrega')
            ->dailyAt('08:00')
            ->withoutOverlapping(60)
            ->appendOutputTo(storage_path('logs/alert_preorden_entrega.log'));

        // ── Latido de Monitoreo y Limpieza de Sesiones Fantasma — cada 5 minutos ──
        $schedule->command('app:heartbeat')
            ->everyFiveMinutes()
            ->withoutOverlapping(5)
            ->appendOutputTo(storage_path('logs/heartbeat.log'));

        // ── Sincronización Local <-> Nube — cada 5 minutos (Solo Servidor de Producción) ──
        if (config('sync.auto_enabled', true) && !config('sync.is_dev', false)) {
            $schedule->command('saavedra:sync')
                ->everyFiveMinutes()
                ->withoutOverlapping(10)
                ->appendOutputTo(storage_path('logs/sync.log'));
        }

        // ── Respaldo Diario de Base de Datos Local — 23:55 todos los días (múltiplo de 5) ──
        $schedule->command('saavedra:backup-local')
            ->dailyAt('23:55')
            ->withoutOverlapping(60)
            ->appendOutputTo(storage_path('logs/backup_local.log'));

        // ── Sincronización Calidad Maquinados — cada 5 minutos ────────────────
        $schedule->command('calidad:sync-maquinados')
            ->everyFiveMinutes()
            ->withoutOverlapping(15)
            ->appendOutputTo(storage_path('logs/sync_maquinados.log'));

        // ── Sincronización Calidad Fundición — cada 5 minutos ─────────────────
        $schedule->command('calidad:sync-fundicion')
            ->everyFiveMinutes()
            ->withoutOverlapping(15)
            ->appendOutputTo(storage_path('logs/sync_fundicion.log'));

        // ── Depuración automática cada domingo a las 23:00 ────────────────────
        $schedule->command('app:depurar-logs')
            ->weekly()
            ->sundays()
            ->at('23:00')
            ->withoutOverlapping(120)
            ->appendOutputTo(storage_path('logs/depuracion_periodica.log'));

        // ── Limpieza automática de view_performance_logs (retención rodante de 3 días) ──
        $schedule->command('logs:prune-view-performance --days=3')
            ->dailyAt('02:30')
            ->withoutOverlapping(30)
            ->appendOutputTo(storage_path('logs/prune_view_performance.log'));
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
