<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
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
        User::query()->whereIn('perfil', [2, 9, '2', '9'], 'and', false)
            ->where('prod_status', '!=', 'none', 'and')
            ->where('updated_at', '<', $now->copy()->subMinutes(2), 'and')
            ->update([
                'prod_status' => 'none',
                'prod_locked_type' => null,
                'prod_start_at' => null
            ]);

        // Buscar operadores (Perfil 9 y 2) que tengan un monitoreo activo
        $users = User::query()->whereIn('perfil', [2, 9, '2', '9'], 'and', false)
            ->where('prod_status', '!=', 'none', 'and')
            ->whereNull('prod_locked_type') // Solo procesar los que no están bloqueados aún
            ->get();

        foreach ($users as $user) {
            self::evaluateUser($user, $now);
        }

        // La salida hacia heartbeat.log es manejada automáticamente por appendOutputTo en Kernel.php
        $this->info($message);
    }

    /**
     * Evalúa si un operador activo excedió su tiempo permitido de inactividad o maquinado.
     * Se invoca tanto en tiempo real desde ProductivityController::ping() como por respaldo desde el Cron cada 5 min.
     */
    public static function evaluateUser(User $user, ?Carbon $now = null): void
    {
        if ($user->prod_status === 'none' || $user->prod_locked_type !== null || empty($user->prod_start_at)) {
            return;
        }

        $now = $now ?? Carbon::now();
        $startTime = Carbon::parse($user->prod_start_at);
        $elapsedMinutes = $startTime->diffInMinutes($now);

        switch ($user->prod_status) {
            case 'welcome':
            case 'form':
                // Usar tiempo definido en config/productivity.php (.env)
                $idleLimit = (int) config('productivity.idle_mins', 3);
                if ($elapsedMinutes >= $idleLimit) {
                    $lockedType = ($user->prod_status == 'welcome' ? 'inicio' : 'formulario');
                    $faseNombre = ($user->prod_status == 'welcome' ? 'Menú Principal' : 'Configuración de Formulario');

                    DB::beginTransaction();
                    try {
                        $user->update(['prod_locked_type' => $lockedType]);

                        // REGISTRO EN BITÁCORA OFICIAL (SystemLog) para reporte en pantalla
                        SystemLog::create([
                            'user_matricula' => $user->matricula,
                            'action' => 'Exceso de Tiempo',
                            'details' => "Inactividad en {$faseNombre}.",
                            'maquina' => $user->maquina ?? 'N/A',
                            'h_inicio' => $startTime->format('H:i:s'),
                            'h_termino' => $now->format('H:i:s'),
                        ]);

                        DB::commit();

                        $logMsg = "[BLOQUEO] Operador {$user->matricula} excedió el límite de inactividad ({$idleLimit}m) en {$faseNombre}.";
                        Log::channel('productivity')->info($logMsg);
                    } catch (\Throwable $e) {
                        DB::rollBack();
                        Log::error('Fallo al registrar bloqueo de inactividad en Heartbeat', [
                            'user_id' => $user->id,
                            'error'   => $e->getMessage(),
                            'trace'   => $e->getTraceAsString(),
                        ]);
                    }
                }
                break;

            case 'machining':
                // Usar el umbral definido (ej: 1.10 para 110%)
                $standard = $user->prod_standard_min > 0 ? $user->prod_standard_min : 60;
                $thresholdFactor = config('productivity.machining_threshold', 1.10);
                $threshold = $standard * $thresholdFactor;

                if ($elapsedMinutes >= $threshold) {
                    // Intentar recuperar los metadatos de la meta actual para el log oficial
                    $maquinaActiva = Maquinas::query()->where('id_meta', '=', function ($q) use ($user) {
                        $q->select('id')->from('metas')->where('id_usuario', '=', $user->matricula, 'and')->orderBy('id', 'desc')->limit(1);
                    }, 'and')->first();

                    $meta = null;
                    if ($maquinaActiva) {
                        $meta = Metas::query()->find($maquinaActiva->id_meta, ['*']);
                    }

                    DB::beginTransaction();
                    try {
                        $user->update(['prod_locked_type' => 'produccion']);

                        // REGISTRO EN BITÁCORA OFICIAL (SystemLog) para reporte en pantalla
                        SystemLog::create([
                            'user_matricula' => $user->matricula,
                            'action' => 'Exceso de Tiempo',
                            'details' => "Exceso de Tiempo de Maquinado.",
                            'ot' => $meta->id_ot ?? 'N/A',
                            'clase' => $meta->clase ?? 'N/A',
                            'proceso' => $meta->proceso ?? 'N/A',
                            'id_ot' => $meta->id_ot ?? null,
                            'maquina' => $meta->maquina ?? 'N/A',
                            'h_inicio' => $startTime->format('H:i:s'),
                            'h_termino' => $now->format('H:i:s'),
                        ]);

                        DB::commit();

                        $logMsg = "[BLOQUEO] Operador {$user->matricula} excedió el tiempo estándar de producción ({$standard}m + margen de tolerancia).";
                        Log::channel('productivity')->info($logMsg);
                    } catch (\Throwable $e) {
                        DB::rollBack();
                        Log::error('Fallo al registrar bloqueo de maquinado en Heartbeat', [
                            'user_id' => $user->id,
                            'error'   => $e->getMessage(),
                            'trace'   => $e->getTraceAsString(),
                        ]);
                    }
                }
                break;
        }
    }
}
