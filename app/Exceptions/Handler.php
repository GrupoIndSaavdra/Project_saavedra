<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
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
        // 1. Siempre registrar el error detallado para auditoría en storage/logs/laravel.log
        Log::error('Excepción interceptada por Handler: ' . $e->getMessage(), [
            'url' => $request->fullUrl(),
            'method' => $request->method(),
            'user_id' => auth()->id() ?? 'invitado',
            'ip' => $request->ip(),
            'file' => $e->getFile() . ':' . $e->getLine(),
            'trace' => substr($e->getTraceAsString(), 0, 1500),
        ]);

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
}
