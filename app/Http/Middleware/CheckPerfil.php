<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware centralizado de autorización por perfil (Catálogo GIS 1–10).
 *
 * Uso en rutas: ->middleware(['auth', 'perfil:1,2,10'])
 *
 * Catálogo oficial:
 *   1  = Master
 *   2  = Administrador
 *   3  = Supervisor
 *   4  = Ingeniería
 *   5  = Diseño y programación
 *   6  = Ventas
 *   7  = Almacén
 *   8  = Calidad
 *   9  = Operador
 *   10 = Sistemas (y '0' legacy o puesto SISTEMAS)
 */
class CheckPerfil
{
    public function handle(Request $request, Closure $next, string ...$perfiles): Response
    {
        $user = $request->user();

        if (! $user) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Sesión no autenticada.',
                ], 401);
            }

            return redirect()->route('login');
        }

        $userPerfil = (string) ($user->perfil ?? '');
        $allowed = in_array($userPerfil, $perfiles, true);

        // Solo evaluar compatibilidad de Sistemas ('0' legacy o puesto SISTEMAS) si aún no pasó y '10' está permitido
        if (! $allowed && in_array('10', $perfiles, true)) {
            $isSistemas = $userPerfil === '10' || $userPerfil === '0';
            if (! $isSistemas) {
                $puestoNombre = strtoupper(trim((string) ($user->puestoInfo->nombre ?? '')));
                $isSistemas = str_contains($puestoNombre, 'SISTEMAS');
            }
            $allowed = $isSistemas;
        }

        if (! $allowed) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Acceso denegado: tu perfil no tiene permisos para acceder a esta sección.',
                ], 403);
            }

            abort(403, 'Acceso denegado: tu perfil no tiene permisos para acceder a esta sección.');
        }

        return $next($request);
    }
}
