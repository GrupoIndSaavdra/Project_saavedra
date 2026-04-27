<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class ProductivityController extends Controller
{
    /**
     * Sincroniza el estado de productividad del operador.
     * Es llamado por un Ping de JS cada 10-30 segundos.
     */
    public function ping(Request $request)
    {
        $user = Auth::user();
        if (!$user || $user->perfil != '2') {
            return response()->json(['locked' => null]);
        }

        // Si el usuario YA tiene un bloqueo activo, no permitimos que el ping lo limpie.
        // Solo respondemos que sigue bloqueado.
        if ($user->prod_locked_type != null) {
            return response()->json([
                'locked' => $user->prod_locked_type,
                'standard_min' => $user->prod_standard_min
            ]);
        }

        $newStatus = $request->input('status', 'none'); 
        $standardMin = $request->input('standard_min', 0);

        // LÓGICA DE PROTECCIÓN ANTI-RESETEO:
        if ($user->prod_status != $newStatus) {
            $oldStatus = $user->prod_status;

            // Restablecemos el comportamiento original para Welcome y Form (se reinician al entrar, con 00 segs)
            if ($newStatus == 'welcome' || $newStatus == 'form') {
                $user->prod_start_at = now()->setSeconds(0);
            }

            // Para Maquinado, solo reseteamos si es la transición inicial desde el formulario (con 00 segs)
            if ($newStatus == 'machining' && $oldStatus == 'form') {
                $user->prod_start_at = now()->setSeconds(0);
            }

            // LOG TÉCNICO DE TRANSICIÓN
            if ($oldStatus == 'none') {
                \Illuminate\Support\Facades\Log::channel('productivity')->info("[INGRESO] El operador {$user->matricula} ha iniciado su monitoreo de productividad.");
            } elseif ($newStatus != 'none' && $newStatus != 'welcome') {
                \Illuminate\Support\Facades\Log::channel('productivity')->info("[FASE] Operador {$user->matricula} cambió a fase: {$newStatus}.");
            }

            $user->prod_status = $newStatus;
            $user->prod_standard_min = $standardMin;
            $user->save();
        }

        // TÉCNICO: Actualizar estampa de tiempo para limpieza de sesiones fantasma
        $user->touch();

        return response()->json([
            'locked' => $user->prod_locked_type,
            'standard_min' => $user->prod_standard_min
        ]);
    }

    /**
     * Desbloquea al usuario tras aceptar la alerta.
     */
    public function unlock(Request $request)
    {
        $user = Auth::user();
        if ($user) {
            $faseAnterior = $user->prod_status;
            $lockedType = $user->prod_locked_type; // Capturar antes de limpiar
            
            $user->prod_locked_type = null;
            $user->prod_start_at = now()->setSeconds(0);
            $user->save();

            // 1. Log técnico de archivo
            \Illuminate\Support\Facades\Log::channel('productivity')->info("[RECUPERACIÓN] Operador {$user->matricula} aceptó la alerta y reanudó su actividad en fase: {$faseAnterior}.");

            // 2. Intentar recuperar metadatos activos del operador
            $activeOT = null;
            $activeClase = null;
            $activeProceso = null;
            $activeNPieza = null;

            if ($user->prod_status == 'machining') {
                // Buscamos la máquina ocupada por este usuario (esto nos vincula a la meta)
                $maquinaActiva = \App\Models\Maquinas::where('id_usuario', $user->id)->first();
                if ($maquinaActiva && $maquinaActiva->id_meta) {
                    $meta = \App\Models\Metas::find($maquinaActiva->id_meta);
                    if ($meta) {
                        $activeOT = $meta->id_ot;
                        $activeClase = $meta->id_clase;
                        $activeProceso = $meta->proceso;
                        
                        // Intentar buscar la pieza que está en estado "ocupada" (1) para este operador en esta meta
                        // Nota: El modelo de pieza varía según el proceso, pero podemos intentar buscar en la tabla general de Pieza
                        // o dejarlo como null si es muy complejo, pero al menos OT y Clase son seguros.
                        $activeNPieza = \App\Models\Pieza::where('id_clase', $activeClase)
                            ->where('proceso', $activeProceso)
                            ->where('id_operador', $user->id)
                            ->orderBy('id', 'desc')
                            ->value('n_pieza');
                    }
                }
            }

            // 3. Los logs se gestionan ahora de forma dinamica entre Heartbeat y el Frontend
        }

        return response()->json(['success' => true]);
    }
}
