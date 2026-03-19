<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class Heartbeat extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:heartbeat';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Logs a pulse to storage/logs/heartbeat.log every minute to verify scheduler is alive';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $message = "[" . now()->toDateTimeString() . "] — Latido (Heartbeat): El programador de tareas está funcionando correctamente.";

        // Log to a custom channel/file
        Log::build([
            'driver' => 'single',
            'path' => storage_path('logs/heartbeat.log'),
        ])->info($message);

        $this->info($message);
    }
}
