<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Models\SystemLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

class LoginController extends Controller
{
    public function show()
    {
        $tecamacImages = [];
        $tecamacPath = public_path('images/planta');
        if (is_dir($tecamacPath)) {
            $files = scandir($tecamacPath);
            foreach ($files as $file) {
                if ($file !== '.' && $file !== '..') {
                    $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
                    if (in_array($ext, ['jpg', 'jpeg', 'png', 'webp'])) {
                        $tecamacImages[] = asset('images/planta/'.$file);
                    }
                }
            }
        }

        if (empty($tecamacImages)) {
            $tecamacImages[] = asset('images/fondos/img-login.png');
        }

        $randomInitialIndex = array_rand($tecamacImages);
        $initialImage = $tecamacImages[$randomInitialIndex];

        return view('auth.login', compact('tecamacImages', 'randomInitialIndex', 'initialImage'));
    }

    public function login(LoginRequest $request)
    {
        $matricula = (string) $request->input('matricula');
        $contrasena = (string) $request->input('contrasena');
        $clientIp = (string) $request->ip();

        // 1. Protección contra Fuerza Bruta (Rate Limiting por matrícula + IP)
        $throttleKey = Str::transliterate(Str::lower($matricula).'|'.$clientIp);

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);

            Log::warning('Login bloqueado temporalmente por exceso de intentos fallidos', [
                'matricula' => $matricula,
                'ip' => $clientIp,
                'segundos_espera' => $seconds,
            ]);

            return redirect()->to('/login')->withErrors(
                "Demasiados intentos fallidos. Por seguridad, espera {$seconds} segundos antes de volver a intentar."
            );
        }

        $user = User::query()->where('matricula', $matricula)->first();

        // 2. Bloquear acceso si la cuenta está inactiva
        if ($user && ! $user->estatus) {
            RateLimiter::hit($throttleKey, 60);

            SystemLog::create([
                'user_matricula' => $user->matricula,
                'action' => 'Intento de Login Bloqueado',
                'details' => "El usuario intentó iniciar sesión pero su cuenta está inactiva desde la IP: {$clientIp}.",
            ]);

            return redirect()->to('/login')->withErrors('Tu cuenta está inactiva. Contacta al administrador del sistema.');
        }

        // 3. Restricción de red para operadores (Perfil 2) ANTES de calcular el Hash en producción
        if ($user && (string) $user->perfil === '2' && app()->environment('production')) {
            $rawTecamac = config('app.planta_tecamac_allowed_ips', '');
            $rawLaRaza = config('app.planta_la_raza_allowed_ips', '');

            $tecamacIps = array_filter(array_map('trim', explode(',', (string) $rawTecamac)));
            $laRazaIps = array_filter(array_map('trim', explode(',', (string) $rawLaRaza)));
            $allowedIps = array_unique(array_merge($tecamacIps, $laRazaIps));

            if (! empty($allowedIps)) {
                $isAllowed = false;
                foreach ($allowedIps as $allowed) {
                    if ($clientIp === $allowed || (! empty($allowed) && str_starts_with($clientIp, $allowed))) {
                        $isAllowed = true;
                        break;
                    }
                }

                if (! $isAllowed) {
                    RateLimiter::hit($throttleKey, 60);

                    $nombreCompleto = trim("{$user->nombre} {$user->a_paterno} {$user->a_materno}");
                    $nombreDisplay = $nombreCompleto !== '' ? $nombreCompleto : 'Sin Nombre';

                    SystemLog::create([
                        'user_matricula' => $user->matricula,
                        'action' => 'Intento de Login Fuera de Planta',
                        'details' => "El operador {$nombreDisplay} (Puesto: {$user->puesto}, Planta: {$user->planta}) intentó iniciar sesión fuera de la planta desde la IP: {$clientIp}.",
                    ]);

                    Log::warning('Intento de Login de Operador Fuera de Planta', [
                        'matricula' => $user->matricula,
                        'nombre' => $nombreDisplay,
                        'puesto' => $user->puesto,
                        'planta' => $user->planta,
                        'ip' => $clientIp,
                    ]);

                    return redirect()->to('/login')->withErrors(
                        'Acceso denegado: No puedes conectarte fuera de las instalaciones de la planta.'
                    );
                }
            }
        }

        // 4. Verificación de credenciales con Hash seguro
        if (! $user || ! Hash::check($contrasena, $user->contrasena)) {
            RateLimiter::hit($throttleKey, 60);

            SystemLog::create([
                'user_matricula' => $matricula !== '' ? $matricula : 'DESCONOCIDO',
                'action' => 'Intento de Login Fallido',
                'details' => "Intento de inicio de sesión fallido por matrícula o contraseña incorrecta desde la IP: {$clientIp}.",
            ]);

            return redirect()->to('/login')->withErrors('Matrícula y/o contraseña incorrecta.');
        }

        // 5. Login exitoso: Limpiar intentos fallidos y regenerar sesión (OWASP Anti-Session Fixation)
        RateLimiter::clear($throttleKey);

        Auth::login($user);

        $request->session()->regenerate();

        return $this->authenticated($request, $user);
    }

    /**
     * @param  mixed  $user
     */
    public function authenticated(Request $request, $user)
    {
        // Si es operador (Perfil 2), inicializamos su cronómetro de productividad desde YA (con 00 segs)
        if ($user->perfil == '2') {
            $user->prod_status = 'welcome';
            $user->prod_start_at = now()->setSeconds(0);
            $user->prod_locked_type = null;
            $user->save();

            Log::channel('productivity')->info("[SESIÓN] Operador {$user->matricula} inició sesión. Cronómetro de productividad activo.");
        }

        SystemLog::create([
            'user_matricula' => $user->matricula,
            'action' => 'Inicio de Sesión',
            'details' => "El usuario ha iniciado sesión exitosamente en el sistema desde la IP: {$request->ip()}.",
        ]);

        return redirect()->route('home');
    }
}
