<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Token Secreto de Sincronización (Producción / Planta)
    |--------------------------------------------------------------------------
    |
    | Llave principal compartida entre el servidor local de Planta Tecámac
    | y el servidor en la nube (api.grupoindsaavedra.com).
    | Permite sincronización bidireccional completa (PUSH y PULL).
    |
    */
    'token' => env('SYNC_API_TOKEN', 'saavedra_sync_secret_key_change_me'),

    /*
    |--------------------------------------------------------------------------
    | Token de Desarrolladores (Devs - Solo Lectura / Manual)
    |--------------------------------------------------------------------------
    |
    | Llave secundaria para que los programadores descarguen (PULL) la base
    | de datos de producción a sus entornos locales.
    | Por seguridad, este token es 100% de solo lectura: el servidor de la nube
    | rechaza cualquier intento de subida (PUSH) con este token.
    |
    */
    'dev_token' => env('SYNC_DEV_TOKEN', null),

    /*
    |--------------------------------------------------------------------------
    | Sincronización Automática en Segundo Plano (Cron / Task Scheduler)
    |--------------------------------------------------------------------------
    |
    | En producción debe ser 'true' para sincronizar cada 2 minutos.
    | En entornos de desarrollo debe ser 'false' para que sea siempre manual.
    |
    */
    'auto_enabled' => (bool) env('SYNC_AUTO_ENABLED', true),

    /*
    |--------------------------------------------------------------------------
    | Modo Desarrollador (Dev)
    |--------------------------------------------------------------------------
    |
    | Si está activo, el comando CLI opera en modo solo lectura (PULL protegido)
    | y desactiva las sincronizaciones automáticas.
    |
    */
    'is_dev' => (bool) env('SYNC_IS_DEV', false),

    /*
    |--------------------------------------------------------------------------
    | URL del Servidor en la Nube
    |--------------------------------------------------------------------------
    |
    | Dirección base de la API oficial hacia donde el servidor local
    | enviará los paquetes de datos (push) o solicitará novedades (pull).
    |
    */
    'cloud_url' => env('SYNC_CLOUD_URL', 'https://api.grupoindsaavedra.com'),

    /*
    |--------------------------------------------------------------------------
    | Tamaño de Lote y Timeout
    |--------------------------------------------------------------------------
    |
    | batch_size: Cantidad de registros por paquete para PUSH (40 garantiza que
    |             ningún paquete supere los 15 KB del buffer de Nginx en Neubox).
    | pull_batch_size: Cantidad de registros por paquete para PULL (descarga GET).
    | timeout: Segundos máximos de espera por paquete HTTP.
    | ping_timeout: Segundos para comprobar si hay conexión a internet.
    |
    */
    'batch_size' => (int) env('SYNC_BATCH_SIZE', 40),
    'pull_batch_size' => (int) env('SYNC_PULL_BATCH_SIZE', 500),
    'timeout' => (int) env('SYNC_TIMEOUT', 90),
    'ping_timeout' => (int) env('SYNC_PING_TIMEOUT', 4),
    'ssl_verify' => (bool) env('SYNC_SSL_VERIFY', false),

    /*
    |--------------------------------------------------------------------------
    | Respaldo Local de Base de Datos
    |--------------------------------------------------------------------------
    |
    | Directorio de almacenamiento para volcados .sql.gz y días de retención.
    |
    */
    'backup_path' => env('SYNC_BACKUP_PATH', storage_path('app/backups')),
    'backup_retention_days' => (int) env('SYNC_BACKUP_RETENTION_DAYS', 15),

    /*
    |--------------------------------------------------------------------------
    | Sincronización de Archivos Físicos (Dibujos, PDFs, Ayudas Visuales)
    |--------------------------------------------------------------------------
    |
    | Habilita la réplica bidireccional de archivos en storage/app/DOCUMENTACION_GIS.
    |
    */
    'sync_files_enabled' => (bool) env('SYNC_FILES_ENABLED', true),
    'files_relative_dir' => 'DOCUMENTACION_GIS',
    'allowed_file_extensions' => ['pdf', 'png', 'jpg', 'jpeg', 'dwg', 'dxf', 'txt', 'csv'],
    'max_file_size_bytes' => 100 * 1024 * 1024, // 100 MB máximo por archivo

    /*
    |--------------------------------------------------------------------------
    | Tablas Excluidas de la Sincronización
    |--------------------------------------------------------------------------
    |
    | Exclusivamente tablas de infraestructura de Laravel, sesiones de usuario
    | y logs de telemetría de rendimiento cliente.
    | Todas las demás tablas del sistema (incluyendo catálogos, molduras,
    | soldadura, maquinados, inspecciones y piezas) se sincronizan.
    |
    */
    'ignore_tables' => [
        'cache',
        'cache_locks',
        'failed_jobs',
        'migrations',
        'password_reset_tokens',
        'personal_access_tokens',
        'sessions',
        'sync_checkpoints',
        'view_performance_logs',

        // Módulo de QR (inactivo temporalmente / comentado)
        'soldadura_lotes',
        'soldadura_botes',
        'soldadura_recepciones_planta',
        'soldadura_liberaciones',
    ],

    /*
    |--------------------------------------------------------------------------
    | Orden Prioritario de Tablas para Sincronización
    |--------------------------------------------------------------------------
    |
    | Orden estricto de dependencias padre-hijo para garantizar integridad:
    | 1. Perfiles y Usuarios
    | 2. Catálogos maestros (clases, procesos, máquinas, molduras)
    | 3. Órdenes de trabajo y metas
    | 4. Fundición y Almacén
    | 5. Soldadura y PTA
    | 6. Piezas maestras y Tiempos de producción
    | 7. Calidad y Reportes (SCAR, RVisual, RDimensional)
    | 8. Archivos y Documentación
    | 9. Procesos de Maquinado (pza, cnominal, tolerancia)
    |
    */
    'priority_tables' => [
        // 1. Roles y Usuarios
        'perfiles',
        'puestos',
        'users',

        // 2. Catálogos Maestros
        'clases',
        'procesos',
        'maquinas',
        'molduras',

        // 3. Órdenes de Trabajo y Metas
        'orden_trabajo',
        'metas',
        'parcialidades_ot',
        'remisiones_ot',

        // 4. Fundición y Almacén
        'herramientas_tecamac',
        'herramienta_imagenes',
        'pre_ordenes_fundicion',
        'liberacion_modelos_fundicion',
        'fundicion_history',
        'fundicion_file_logs',
        'fundicion_state_logs',
        'preorden_logs',

        // 5. Soldadura y PTA
        // 'soldadura_lotes', // QR inactivo temporalmente
        // 'soldadura_botes', // QR inactivo temporalmente
        // 'soldadura_recepciones_planta', // QR inactivo temporalmente
        // 'soldadura_liberaciones', // QR inactivo temporalmente
        'soldadura',
        'soldadurapta',
        'pta_resultados',
        'soldadurapta_pza',
        'soldadura_pza',
        'pta_reporte_logs',

        // 6. Piezas Maestras y Producción
        'piezas',
        'piezas_historial',
        'piezas_ocupadas',
        'pzas_generales',
        'pzas_liberadas',
        'fechas_procesos',
        'tiempos_produccion',
        'tratamiento_termicos',

        // 7. Calidad y Rechazos
        'scar_modelos',
        'scar_logs',
        'rechazo_logs',
        'r_visuales',
        'r_visual_medidas',
        'r_visuales_pdfs',
        'r_dimensionales',
        'r_dimensional_medidas',
        'r_dimensionales_pdfs',
        'calidad_maquinados_docs',

        // 8. Historial de Dibujos y Documentos
        'dibujos_file_log',
        'dibujos_ot_history',
        'ayudas_visuales_file_log',
        'ayudas_visuales_history',
        'ayudas_visuales_fundicion_file_log',
        'ayudas_visuales_fundicion_history',
        'manuales_file_log',
        'manuales_history',

        // 9. Procesos de Maquinado (Procesos base y tolerancias)
        'cepillado',
        'cepillado_cnominal',
        'cepillado_tolerancia',
        'cepillado_pza',

        'desbasteexterior',
        'desbaste_cnominal',
        'desbaste_tolerancia',
        'desbaste_pza',

        'rectificado',
        'rectificado_cnominal',
        'rectificado_tolerancia',
        'rectificado_pza',

        'asentado',
        'asentado_pza',

        'barrenomaniobra',
        'barrenomaniobra_cnominal',
        'barrenomaniobra_tolerancia',
        'barrenomaniobra_pza',

        'barrenoprofundidad',
        'barrenoprofundidad_cnominal',
        'barrenoprofundidad_tolerancia',
        'barrenoprofundidad_pza',

        'acabadobombillo',
        'acabadobombillo_cnominal',
        'acabadobombillo_tolerancia',
        'acabadobombillo_pza',

        'acabadomolde',
        'acabadomolde_cnominal',
        'acabadomolde_tolerancia',
        'acabadomolde_pza',

        'cavidades',
        'cavidades_cnominal',
        'cavidades_tolerancia',
        'cavidades_pza',

        'candadoobturador',
        'candadoobturador_cnominal',
        'candadoobturador_tolerancia',
        'candadoobturador_pza',

        'copiado',
        'copiado_cnominal',
        'copiado_tolerancia',
        'copiado_pza',

        'embudocm',
        'embudocm_cnominal',
        'embudocm_tolerancia',
        'embudocm_pza',

        'offset',
        'offset_cnominal',
        'offset_tolerancia',
        'offset_pza',

        'primeraopesoldadura',
        'primeraopesoldadura_cnominal',
        'primeraopesoldadura_tolerancia',
        'primeraopesoldadura_pza',

        'segundaopesoldadura',
        'segundaopesoldadura_cnominal',
        'segundaopesoldadura_tolerancia',
        'segundaopesoldadura_pza',

        'pysopesoldadura',
        'pysopesoldadura_cnominal',
        'pysopesoldadura_tolerancia',
        'pysopesoldadura_pza',

        'primeraoperacioncabezasoplo',
        'primeraoperacioncabezasoplo_cnominal',
        'primeraoperacioncabezasoplo_tolerancia',
        'primeraoperacioncabezasoplo_pza',

        'segundaoperacioncabezasoplo',
        'segundaoperacioncabezasoplo_cnominal',
        'segundaoperacioncabezasoplo_tolerancia',
        'segundaoperacioncabezasoplo_pza',

        'revcalificado',
        'revcalificado_cnominal',
        'revcalificado_tolerancia',
        'revcalificado_pza',

        'revlaterales',
        'revlaterales_cnominal',
        'revlaterales_tolerancia',
        'revlaterales_pza',

        'r_interno_salidas',
        'r_interno_salidas_log',
        'r_interno_salidas_pdfs',
        'r_interno_salidas_piezas',
    ],

];
