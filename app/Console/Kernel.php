<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Event;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;

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
        $cloudUrl = rtrim((string) config('sync.cloud_url', 'https://api.grupoindsaavedra.com'), '/');
        $appUrl = rtrim((string) config('app.url', ''), '/');
        $isCloudHost = (!empty($appUrl) && $cloudUrl === $appUrl);

        // ── Reporte Diario de Producción — 23:50 todos los días ────────
        $this->scheduleSafeCommand($schedule, 'reporte:enviar-diario', [], 'reporte_diario.log', 60)
            ->dailyAt('23:50');

        // ── Alerta Pre-órdenes de Entrega — 08:00 ──────────────────────
        $this->scheduleSafeCommand($schedule, 'alert:preorden-entrega', [], 'alert_preorden_entrega.log', 60)
            ->dailyAt('08:00');

        // ── Latido de Monitoreo y Limpieza de Sesiones Fantasma — cada 5 minutos ──
        $this->scheduleSafeCommand($schedule, 'app:heartbeat', [], 'heartbeat.log', 5)
            ->everyFiveMinutes();

        // ── Tareas exclusivas del Servidor Físico Local (Planta Tecámac) ──
        if (!$isCloudHost) {
            // Sincronización Local <-> Nube — cada 5 minutos (Solo Producción Local)
            if (config('sync.auto_enabled', true) && !config('sync.is_dev', false)) {
                $this->scheduleSafeCommand($schedule, 'saavedra:sync', [], 'sync.log', 10)
                    ->everyFiveMinutes();
            }

            // Respaldo Diario de Base de Datos Local — 23:55 todos los días (múltiplo de 5)
            $this->scheduleSafeCommand($schedule, 'saavedra:backup-local', [], 'backup_local.log', 60)
                ->dailyAt('23:55');
        }

        // ── Sincronización Calidad Maquinados — cada 5 minutos ─────────
        $this->scheduleSafeCommand($schedule, 'calidad:sync-maquinados', [], 'sync_maquinados.log', 15)
            ->everyFiveMinutes();

        // ── Sincronización Calidad Fundición — cada 5 minutos ──────────
        $this->scheduleSafeCommand($schedule, 'calidad:sync-fundicion', [], 'sync_fundicion.log', 15)
            ->everyFiveMinutes();

        // ── Depuración automática cada domingo a las 23:00 ─────────────
        $this->scheduleSafeCommand($schedule, 'app:depurar-logs', [], 'depuracion_periodica.log', 120)
            ->weekly()
            ->sundays()
            ->at('23:00');

        // ── Limpieza automática de view_performance_logs (retención rodante de 3 días) ──
        $this->scheduleSafeCommand($schedule, 'logs:prune-view-performance', ['--days' => 3], 'prune_view_performance.log', 30)
            ->dailyAt('02:30');
    }

    /**
     * Registra un comando programado de forma híbrida compatible con Windows Server y cPanel/Neubox:
     * - En cPanel/Linux compartido (donde proc_open suele estar bloqueado en disable_functions o corre bajo php-cgi),
     *   ejecuta el comando en memoria vía Artisan::call() sin abrir subprocesos del sistema operativo y escribe el log.
     * - En Windows Server CLI con proc_open activo, utiliza el subproceso estándar de Laravel.
     */
    protected function scheduleSafeCommand(
        Schedule $schedule,
        string $command,
        array $parameters,
        string $logFilename,
        int $mutexMinutes
    ): Event {
        $logPath = storage_path('logs/' . ltrim($logFilename, '/\\'));

        if ($this->shouldRunInProcess()) {
            return $schedule->call(function () use ($command, $parameters, $logPath) {
                $stamp = now()->format('Y-m-d H:i:s');
                try {
                    Artisan::call($command, $parameters);
                    $output = trim((string) Artisan::output());
                    $entry = $output !== ''
                        ? "[{$stamp}] [OK] {$command}\n{$output}\n"
                        : "[{$stamp}] [OK] {$command}\n";

                    @file_put_contents($logPath, $entry, FILE_APPEND | LOCK_EX);
                } catch (\Throwable $e) {
                    @file_put_contents(
                        $logPath,
                        "[{$stamp}] [ERROR] {$command}: {$e->getMessage()} ({$e->getFile()}:{$e->getLine()})\n",
                        FILE_APPEND | LOCK_EX
                    );
                    Log::error("Fallo en tarea programada '{$command}'", [
                        'error' => $e->getMessage(),
                        'file' => $e->getFile() . ':' . $e->getLine(),
                    ]);
                }
            })
                ->name("php artisan {$command}")
                ->withoutOverlapping($mutexMinutes);
        }

        $cliArgs = '';
        foreach ($parameters as $key => $value) {
            if (is_int($key)) {
                $cliArgs .= ' ' . $value;
            } elseif ($value === true) {
                $cliArgs .= ' ' . $key;
            } else {
                $cliArgs .= " {$key}={$value}";
            }
        }

        return $schedule->command($command . $cliArgs)
            ->withoutOverlapping($mutexMinutes)
            ->appendOutputTo($logPath);
    }

    /**
     * Determina si debemos ejecutar los comandos en el mismo proceso PHP (in-process)
     * para evitar fallos de proc_open / php-cgi / Entry Processes en cPanel compartido.
     */
    protected function shouldRunInProcess(): bool
    {
        if (PHP_OS_FAMILY !== 'Windows') {
            return true;
        }

        if (PHP_SAPI !== 'cli' || !function_exists('proc_open')) {
            return true;
        }

        $disabled = strtolower((string) ini_get('disable_functions'));
        if ($disabled !== '' && in_array('proc_open', array_map('trim', explode(',', $disabled)), true)) {
            return true;
        }

        return false;
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
