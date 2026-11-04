<?php

namespace App\Http\Middleware;

use App\Models\ViewPerformanceLog;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class LogViewPerformance
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        return $next($request);
    }

    /**
     * Handle tasks after the response has been sent to the browser.
     * Terminable middleware: 0% overhead on user response time.
     */
    public function terminate(Request $request, Response $response): void
    {
        $path = $request->path();
        if ($this->shouldIgnore($path)) {
            return;
        }

        // Medir peticiones GET (vistas) o AJAX principales de carga de vistas
        if (! $request->isMethod('GET') && ! $request->ajax()) {
            return;
        }

        $startTime = defined('LARAVEL_START') ? LARAVEL_START : $request->server('REQUEST_TIME_FLOAT', microtime(true));
        $durationMs = round((microtime(true) - $startTime) * 1000, 2);

        $route = $request->route();
        $routeName = $route ? $route->getName() : null;
        $actionName = $route ? $route->getActionName() : null;

        if ($actionName && is_string($actionName)) {
            $actionName = class_basename($actionName);
        }

        $user = $request->user();

        try {
            ViewPerformanceLog::create([
                'url' => '/' . ltrim($path, '/'),
                'ruta' => $routeName,
                'vista' => $actionName,
                'metodo' => $request->method(),
                'tiempo_ms' => $durationMs,
                'matricula' => $user ? $user->matricula : null,
                'perfil' => $user ? $user->perfil : null,
                'status_code' => $response->getStatusCode(),
                'ip' => $request->ip(),
            ]);

            // Auto-mantenimiento rotativo: purgar registros de más de 3 días cada 12 horas en segundo plano
            if (! \Illuminate\Support\Facades\Cache::store('file')->has('view_perf_logs_auto_pruned')) {
                \Illuminate\Support\Facades\Cache::store('file')->put('view_perf_logs_auto_pruned', true, now()->addHours(12));
                ViewPerformanceLog::where('created_at', '<', now()->subDays(3))->delete();
            }
        } catch (\Throwable $e) {
            // Silencioso para garantizar que un fallo de log jamás afecte la navegación
        }
    }

    /**
     * Prefijos o recursos que no corresponden a vistas o cargas de pantalla.
     */
    protected function shouldIgnore(string $path): bool
    {
        $ignoredPrefixes = [
            'build',
            'images',
            'css',
            'js',
            'fonts',
            'favicon.ico',
            '_debugbar',
            'vendor',
            'livewire/livewire.js',
            'livewire/livewire.min.js',
        ];

        foreach ($ignoredPrefixes as $prefix) {
            if ($path === $prefix || str_starts_with($path, $prefix . '/')) {
                return true;
            }
        }

        return false;
    }
}