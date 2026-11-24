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
        // En producción o detrás de proxy HTTPS (api.grupoindsaavedra.com), forzar esquema HTTPS
        if ($this->app->environment('production') || str_contains((string) config('app.url'), 'https://') || request()->isSecure() || request()->header('X-Forwarded-Proto') === 'https') {
            \Illuminate\Support\Facades\URL::forceScheme('https');
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
