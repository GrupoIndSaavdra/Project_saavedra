<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use App\Models\User;
use App\Models\SystemLog;
use App\Models\Maquinas;
use App\Models\Metas;
use Carbon\Carbon;

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
    protected $description = 'Procesa el monitoreo de productividad y latidos de los operadores.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $now = Carbon::now();
        $message = "[" . $now->toDateTimeString() . "] — Latido (Heartbeat): Monitoreando productividad activa.";

        // LIMPIEZA DE SESIONES FANTASMA (Abandono de pestaña)
        // Si el usuario no ha mandado ping en más de 2 minutos, lo pasamos a none.
        User::where('perfil', '2')
            ->where('prod_status', '!=', 'none')
            ->where('updated_at', '<', $now->copy()->subMinutes(2))
            ->update([
                'prod_status' => 'none',
                'prod_locked_type' => null,
                'prod_start_at' => null
            ]);

        // Buscar operadores (Perfil 2) que tengan un monitoreo activo
        $users = User::where('perfil', '2')
            ->where('prod_status', '!=', 'none')
            ->whereNull('prod_locked_type') // Solo procesar los que no están bloqueados aún
            ->get();

        foreach ($users as $user) {
            $startTime = Carbon::parse($user->prod_start_at);
            $elapsedMinutes = $startTime->diffInMinutes($now);

            switch ($user->prod_status) {
                case 'welcome':
                case 'form':
                    // Usar tiempo definido en config/productivity.php (.env)
                    $idleLimit = config('productivity.idle_mins', 3);
                    if ($elapsedMinutes >= $idleLimit) {
                        $user->update(['prod_locked_type' => ($user->prod_status == 'welcome' ? 'inicio' : 'formulario')]);
                        $faseNombre = ($user->prod_status == 'welcome' ? 'Menú Principal' : 'Configuración de Formulario');
                        $logMsg = "[BLOQUEO] Operador {$user->matricula} excedió el límite de inactividad ({$idleLimit}m) en {$faseNombre}.";
                        Log::channel('productivity')->info($logMsg);

                        // REGISTRO EN BITÁCORA OFICIAL (SystemLog) para reporte en pantalla
                        SystemLog::create([
                            'user_matricula' => $user->matricula,
                            'action' => 'Exceso de Tiempo',
                            'details' => "Inactividad en {$faseNombre}.",
                            'maquina' => $user->maquina ?? 'N/A',
                            'h_inicio' => $startTime->format('H:i:s'),
                            'h_termino' => $now->format('H:i:s'),
                        ]);
                    }
                    break;

                case 'machining':
                    // Usar el umbral definido (ej: 1.10 para 110%)
                    $standard = $user->prod_standard_min > 0 ? $user->prod_standard_min : 60;
                    $thresholdFactor = config('productivity.machining_threshold', 1.10);
                    $threshold = $standard * $thresholdFactor;
                    
                    if ($elapsedMinutes >= $threshold) {
                        $user->update(['prod_locked_type' => 'produccion']);
                        $logMsg = "[BLOQUEO] Operador {$user->matricula} excedió el tiempo estándar de producción ({$standard}m + margen de tolerancia).";
                        Log::channel('productivity')->info($logMsg);

                        // Intentar recuperar los metadatos de la meta actual para el log oficial
                        $maquinaActiva = Maquinas::where('id_meta', function($q) use ($user) {
                            $q->select('id')->from('metas')->where('id_usuario', $user->matricula)->orderBy('id', 'desc')->limit(1);
                        })->first();

                        $meta = null;
                        if ($maquinaActiva) {
                            $meta = Metas::find($maquinaActiva->id_meta);
                        }

                        // REGISTRO EN BITÁCORA OFICIAL (SystemLog) para reporte en pantalla
                        SystemLog::create([
                            'user_matricula' => $user->matricula,
                            'action' => 'Exceso de Tiempo',
                            'details' => "Exceso de Tiempo de Maquinado.",
                            'ot' => $meta->id_ot ?? 'N/A',
                            'clase' => $meta->id_clase ?? 'N/A',
                            'proceso' => $meta->proceso ?? 'N/A',
                            'id_ot' => $meta->id_ot ?? null,
                            'id_clase' => $meta->id_clase ?? null,
                            'maquina' => $meta->maquina ?? 'N/A',
                            'h_inicio' => $startTime->format('H:i:s'),
                            'h_termino' => $now->format('H:i:s'),
                        ]);
                    }
                    break;
            }
        }

        // Log to a custom channel/file
        Log::build([
            'driver' => 'single',
            'path' => storage_path('logs/heartbeat.log'),
        ])->info($message);

        $this->info($message);
    }
}
