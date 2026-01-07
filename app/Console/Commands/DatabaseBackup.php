<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class DatabaseBackup extends Command
{
    protected $signature = 'db:backup {--filename=backup}';
    protected $description = 'Crear backup de la base de datos';

    public function handle()
    {
        $filename = $this->option('filename');
        $database = config('database.connections.mysql.database');
        $username = config('database.connections.mysql.username');
        $password = config('database.connections.mysql.password');
        $host = config('database.connections.mysql.host');

        $backupPath = storage_path("app/backups/{$filename}.sql");
        
        // Crear directorio si no existe
        if (!file_exists(dirname($backupPath))) {
            mkdir(dirname($backupPath), 0755, true);
        }

        // Comando mysqldump
        $command = sprintf(
            'mysqldump -h%s -u%s -p%s %s > %s',
            $host,
            $username,
            $password,
            $database,
            $backupPath
        );

        exec($command, $output, $returnCode);

        if ($returnCode === 0) {
            $this->info("Backup creado: {$backupPath}");
        } else {
            $this->error("Error creando backup");
        }
    }
}