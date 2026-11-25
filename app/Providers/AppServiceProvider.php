<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        if (file_exists(app_path('helpers.php'))) {
            require_once app_path('helpers.php');
        }
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Manejo dinámico de esquemas HTTP / HTTPS (Soporte dual: Local Planta vs API Cloud)
        if (!$this->app->runningInConsole()) {
            $host = request()->getHost();
            $isLocalNetwork = in_array($host, ['localhost', '127.0.0.1', '::1'])
                || preg_match('/^(192\.168\.|10\.|172\.(1[6-9]|2[0-9]|3[01])\.)/', $host);

            if ($isLocalNetwork) {
                // En red local / planta, si el navegador entra por HTTPS (sin certificado SSL válido),
                // redirigir limpiamente a HTTP para que no se bloqueen los estilos ni scripts
                if (request()->isSecure() && !request()->expectsJson()) {
                    $httpUrl = 'http://' . request()->getHttpHost() . request()->getRequestUri();
                    header("Location: $httpUrl", true, 301);
                    exit();
                }
                \Illuminate\Support\Facades\URL::forceScheme('http');
            } else {
                // En producción / API en la nube (api.grupoindsaavedra.com), forzar siempre HTTPS
                if ($this->app->environment('production') || str_contains((string) config('app.url'), 'https://') || request()->isSecure() || request()->header('X-Forwarded-Proto') === 'https') {
                    \Illuminate\Support\Facades\URL::forceScheme('https');
                }
            }
        }

        // Asegurar memoria suficiente para procesamiento de vistas pesadas y modelos
        if ((int) ini_get('memory_limit') < 512) {
            @ini_set('memory_limit', '512M');
        }

        // Cargar todas las migraciones que estén dentro de subcarpetas en database/migrations/ (solo en consola)
        if ($this->app->runningInConsole()) {
            $mainPath = database_path('migrations');
            $directories = glob($mainPath . '/*' , GLOB_ONLYDIR);
            $paths = array_merge([$mainPath], $directories ?: []);
            $this->loadMigrationsFrom($paths);
        }
    }
}
