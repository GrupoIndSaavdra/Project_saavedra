<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Models\User;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\SystemLog;

class LoginController extends Controller
{
    public function show()
    {
        return view('auth.login');
    }
    public function login(LoginRequest $request)
    {
        $user = User::where('matricula', $request->matricula)->first();

        if (!$user || !Hash::check($request->contrasena, $user->contrasena)) {
            SystemLog::create([
                'user_matricula' => $request->matricula ?? 'DESCONOCIDO',
                'action' => 'Intento de Login Fallido',
                'details' => 'Intento de inicio de sesión fallido por matrícula o contraseña incorrecta.',
            ]);
            return redirect()->to('/login')->withErrors('Matricula y/o contraseña incorrecta');
        }
        Auth::login($user);
        return $this->authenticated($request, $user);
    }
    public function authenticated(Request $request, $user)
    {
        // Si es operador (Perfil 2), inicializamos su cronómetro de productividad desde YA (con 00 segs)
        if ($user->perfil == '2') {
            $user->prod_status = 'welcome';
            $user->prod_start_at = now()->setSeconds(0);
            $user->prod_locked_type = null;
            $user->save();

            \Illuminate\Support\Facades\Log::channel('productivity')->info("[SESIÓN] Operador {$user->matricula} inició sesión. Cronómetro de productividad activo.");
        }

        SystemLog::create([
            'user_matricula' => $user->matricula,
            'action' => 'Inicio de Sesión',
            'details' => 'El usuario ha iniciado sesión en el sistema.',
        ]);

        return redirect()->route('home');
    }
}
