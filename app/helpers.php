<?php

if (!function_exists('icon_path')) {
    /**
     * Retorna la ruta relativa en disco/public de un icono a partir de su alias.
     *
     * @param string $key Alias del catálogo (ej. 'aprobado', 'dwg', 'vista_liberacion')
     * @param string|null $default Ruta de fallback
     * @return string
     */
    function icon_path(string $key, ?string $default = null): string
    {
        $path = config("icons.{$key}");
        if ($path) {
            return $path;
        }

        // Si ya incluye el prefijo 'images/', devolver directamente
        if (str_starts_with($key, 'images/')) {
            return $key;
        }

        // Fallback por si pasan un archivo directo como 'iconos/Aprobado.png'
        if (file_exists(public_path("images/{$key}"))) {
            return "images/{$key}";
        }

        // Búsqueda automática en subcarpetas si solo pasaron el nombre del archivo
        foreach (['iconos', 'vistas', 'usuarios', 'logos', 'fondos', 'calidad', 'planta'] as $sub) {
            if (file_exists(public_path("images/{$sub}/{$key}"))) {
                return "images/{$sub}/{$key}";
            }
        }

        return $default ?? "images/iconos/{$key}";
    }
}

if (!function_exists('icon')) {
    /**
     * Retorna la URL pública generada con asset() para un icono según su alias.
     *
     * @param string $key Alias del catálogo (ej. 'aprobado', 'rechazado', 'editar')
     * @param string|null $default Fallback si no existe
     * @return string URL pública completa del recurso
     */
    function icon(string $key, ?string $default = null): string
    {
        return asset(icon_path($key, $default));
    }
}

if (!function_exists('icon_map')) {
    /**
     * Genera un mapa asociativo de todos los alias con sus URLs públicas para JS.
     *
     * @return array<string, string>
     */
    function icon_map(): array
    {
        $icons = config('icons', []);
        $resolved = [];
        foreach ($icons as $alias => $relPath) {
            $resolved[$alias] = asset($relPath);
        }
        return $resolved;
    }
}
