<?php

namespace App\Http\Controllers;

use App\Models\Maquinas;
use App\Models\Metas;
use App\Models\Pza_cepillado;
use App\Models\SystemLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class LogoutController extends Controller
{
    public function logout(){
        $user = Auth::user();
        
        if ($user) {
            // Eliminar las máquinas ocupadas por el usuario al cerrar sesión
            $userMetasIds = Metas::query()
                ->where('id_usuario', $user->matricula)
                ->pluck('id');

            Maquinas::query()
                ->whereIn('id_meta', $userMetasIds, 'and', false)
                ->delete();

            // Limpiar estado de productividad al salir
            $user->update([
                'prod_status' => 'none',
                'prod_locked_type' => null,
                'prod_start_at' => null
            ]);

            SystemLog::create([
                'user_matricula' => $user->matricula,
                'action' => 'Cierre de Sesión',
                'details' => 'El usuario ha cerrado sesión en el sistema.',
            ]);

            // Registro en log técnico de productividad
            \Illuminate\Support\Facades\Log::channel('productivity')->info("[SESIÓN] El operador {$user->matricula} ha cerrado su sesión de forma segura. Monitoreo de productividad terminado.");
        }

        Session::flush();
        Auth::logout();
        return redirect()->route('home')->with('success', 'Cerraste sesión correctamente.');
    }
} 
