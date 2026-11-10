<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
        'contrasena',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  Request  $request
     * @param  Throwable  $e
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @throws Throwable
     */
    public function render($request, Throwable $e)
    {
        $creatingUser = $request->routeIs('storeUser');
        if ($creatingUser && $e instanceof ValidationException) {
            return parent::render($request, $e);
        }

        $reference = $creatingUser ? (string) Str::uuid() : null;

        // 1. Siempre registrar el error detallado para auditoría en storage/logs/laravel.log
        Log::error('Excepción interceptada por Handler: ' . $e->getMessage(), [
            'url' => $request->fullUrl(),
            'method' => $request->method(),
            'user_id' => auth()->id() ?? 'invitado',
            'ip' => $request->ip(),
            'file' => $e->getFile() . ':' . $e->getLine(),
            'trace' => substr($e->getTraceAsString(), 0, 1500),
            'reference' => $reference,
        ]);

        if ($creatingUser && ! ($e instanceof HttpExceptionInterface) && ! ($e instanceof AuthenticationException)) {
            return $this->renderCreateUserError($request, $e, $reference);
        }

        // 2. Si la petición es AJAX / Fetch o espera JSON:
        // NUNCA devolver HTML 500 para evitar romper el frontend y SweetAlert
        if ($request->expectsJson() || $request->wantsJson() || $request->ajax() || $request->is('api/*') || $request->header('X-Requested-With') === 'XMLHttpRequest') {
            $statusCode = $e instanceof HttpExceptionInterface ? $e->getStatusCode() : 500;
            $msg = config('app.debug')
                ? $e->getMessage()
                : 'Ocurrió un error en el servidor al procesar la solicitud. Por favor intenta de nuevo.';

            return response()->json([
                'success' => false,
                'status' => 'error',
                'message' => $msg,
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], $statusCode);
        }

        // 3. Si es una petición mutante (POST, PUT, DELETE, PATCH) en producción,
        // no dejar al usuario atrapado en una pantalla 500: redirigir hacia atrás con alerta
        if (!config('app.debug') && in_array($request->method(), ['POST', 'PUT', 'DELETE', 'PATCH'])) {
            return back()->withInput()->with('error', 'Ocurrió un contratiempo inesperado al procesar la solicitud. Por favor intenta de nuevo.');
        }

        return parent::render($request, $e);
    }

    private function renderCreateUserError(Request $request, Throwable $error, string $reference)
    {
        $status = 500;
        $field = 'registro';
        $message = 'No se pudo registrar al usuario. Comparte la referencia con el responsable del sistema para revisar el registro de errores.';

        if ($error instanceof QueryException) {
            $driverCode = (int) ($error->errorInfo[1] ?? 0);
            $sqlState = (string) ($error->errorInfo[0] ?? '');
            if ($driverCode === 1062) {
                $status = 422;
                $field = 'matricula';
                $message = 'La matrícula ya está registrada. Revisa el usuario existente o captura otra matrícula.';
            } elseif ($driverCode === 1045) {
                $message = 'La base de datos rechazó el acceso. Pide al responsable del sistema revisar las credenciales de conexión.';
            } elseif (in_array($sqlState, ['42S02', '42S22'], true) || $driverCode === 1049) {
                $message = 'La base de datos está incompleta o no corresponde a esta versión de GIS. Pide al responsable del sistema revisar la base configurada y sus migraciones.';
            } elseif ($sqlState === '22001' || $driverCode === 1406) {
                $status = 422;
                $message = 'Uno de los campos supera el tamaño admitido por la base de datos. Revisa la longitud de los datos capturados.';
            } elseif (str_starts_with($sqlState, '08') || in_array($driverCode, [1040, 2002, 2003, 2006, 2013], true)) {
                $status = 503;
                $message = 'No se pudo conectar con la base de datos. Pide al responsable del sistema verificar que el servicio esté disponible antes de reintentar.';
            }
        }

        $message .= ' Referencia '.$reference.'.';
        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => false,
                'message' => $message,
                'errors' => [$field => [$message]],
                'reference' => $reference,
            ], $status);
        }

        return redirect()->route('createUser')
            ->withErrors([$field => $message])
            ->withInput($request->except($this->dontFlash));
    }
}
