<?php

namespace App\Http\Controllers;

use App\Models\FundicionHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class AlmacenFundicionController extends Controller
{
    /**
     * Directorio aislado donde se guardan las copias protegidas de Almacén.
     */
    private const ALMACEN_DIR = 'DOCUMENTACION_GIS/ALMACEN_FUNDICION';

    /**
     * Perfiles de usuario que tienen acceso a esta vista.
     * 4 = Calidad | 5 = Almacen
     */
    private const PERFILES_PERMITIDOS = ['4', '5'];

    // =========================================================================
    // GATE DE ACCESO
    // =========================================================================

    private function verificarAcceso(): void
    {
        /** @var \App\Models\User|null $user */
        $user = Auth::user();

        if (!$user || !in_array($user->perfil, self::PERFILES_PERMITIDOS, true)) {
            abort(403, 'Acceso restringido. Solo Almacén y Calidad pueden ver esta sección.');
        }
    }

    // =========================================================================
    // VISTA PRINCIPAL
    // =========================================================================

    /**
     * Muestra la tabla con todos los registros históricos de Almacén,
     * incluyendo su estado Activa/Inactiva.
     *
     * @param \Illuminate\Http\Request $request
     */
    public function index(Request $request)
    {
        $this->verificarAcceso();

        // Filtros desde query string
        $busquedaOt = trim($request->query('ot', ''));
        $desde      = $request->query('desde', '');
        $hasta      = $request->query('hasta', '');

        $query = FundicionHistory::query()->orderByDesc('alert_sent_at');

        // Filtro: búsqueda por nombre de OT (incluye todas, activas e inactivas)
        if ($busquedaOt !== '') {
            $query->where('ot', $busquedaOt);
        }

        // Filtro: rango de fechas por fecha de alerta enviada
        if ($desde !== '') {
            $query->whereDate('alert_sent_at', '>=', $desde);
        }
        if ($hasta !== '') {
            $query->whereDate('alert_sent_at', '<=', $hasta);
        }

        // Solo registros que al menos hayan sido enviados a Almacén (alert_sent_at no nulo)
        $query->whereNotNull('alert_sent_at');

        $registros = $query->get();

        // Obtener lista única de OTs para el dropdown (solo los que están en Almacén)
        $listaOts = FundicionHistory::query()
            ->whereNotNull('alert_sent_at', 'and')
            ->orderBy('ot')
            ->pluck('ot');

        return view('almacen.fundicion_index', compact(
            'registros',
            'listaOts',
            'busquedaOt',
            'desde',
            'hasta'
        ));
    }

    // =========================================================================
    // API — Lista de Archivos (para el panel de detalle)
    // =========================================================================

    /**
     * Devuelve los archivos del directorio aislado para una OT dada.
     * La lista proviene del snapshot en BD (almacen_archivos) y se verifica
     * físicamente para filtrar archivos que puedan haberse eliminado.
     *
     * @param \Illuminate\Http\Request $request
     */
    public function getFiles(Request $request)
    {
        $this->verificarAcceso();

        $ot = $this->sanitizePath($request->query('ot', ''));

        if (empty($ot)) {
            return response()->json(['error' => 'Parámetro OT es requerido.'], 422);
        }

        /** @var \App\Models\FundicionHistory|null $history */
        $history = FundicionHistory::query()->where('ot', $ot)->first();

        if (!$history || !$history->alert_sent_at) {
            return response()->json([
                'existe'   => false,
                'archivos' => [],
                'ot'       => $ot,
            ]);
        }

        $dirPath       = self::ALMACEN_DIR . '/' . $ot;
        $ayudasDirPath = $dirPath . '/ayudas_visuales';

        // 1. Obtener dibujos principales (directorio raíz de la OT en Almacén)
        $dibujos = collect(Storage::disk('local')->files($dirPath))
            ->filter(fn($f) => strtolower(pathinfo($f, PATHINFO_EXTENSION)) === 'pdf')
            ->map(fn($f) => [
                'nombre' => basename($f),
                'tipo'   => 'dibujo',
                'url'    => route('almacen.fundicion.serve', [
                    'ot'      => $ot,
                    'archivo' => basename($f),
                    'tipo'    => 'dibujo',
                ]),
            ]);

        // 2. Obtener ayudas visuales (subdirectorio ayudas_visuales - ESCANEO RECURSIVO)
        $ayudas = [];
        if (Storage::disk('local')->exists($ayudasDirPath)) {
            $ayudas = collect(Storage::disk('local')->allFiles($ayudasDirPath))
                ->filter(fn($f) => strtolower(pathinfo($f, PATHINFO_EXTENSION)) === 'pdf')
                ->map(fn($f) => [
                    // Importante: El nombre debe incluir la subcarpeta (ej: "Bombillo/archivo.pdf")
                    'nombre' => str_replace($ayudasDirPath . '/', '', $f),
                    'tipo'   => 'ayuda',
                    'url'    => route('almacen.fundicion.serve', [
                        'ot'      => $ot,
                        'archivo' => str_replace($ayudasDirPath . '/', '', $f),
                        'tipo'    => 'ayuda',
                    ]),
                ]);
        }

        $allFiles = $dibujos->merge($ayudas)->values();

        return response()->json([
            'existe'       => true,
            'archivos'     => $allFiles,
            'ot'           => $ot,
            'status'       => $history->status,
            'alert_sent_at' => $history->alert_sent_at?->format('d/m/Y H:i'),
        ]);
    }

    // =========================================================================
    // SERVIR ARCHIVOS (Solo Lectura)
    // =========================================================================

    /**
     * Sirve un PDF desde el directorio aislado FUNDICION_ALMACEN/.
     *
     * @param \Illuminate\Http\Request $request
     */
    public function serveFile(Request $request): BinaryFileResponse
    {
        $this->verificarAcceso();

        $ot      = $this->sanitizePath($request->query('ot', ''));
        $archivo = $this->sanitizeFileNameWithFolder($request->query('archivo', ''));
        $tipo    = $request->query('tipo', 'dibujo');

        if (empty($ot) || empty($archivo)) {
            abort(422, 'Parámetros inválidos.');
        }

        if ($tipo === 'ayuda') {
            // $archivo ya contiene "Clase/archivo.pdf" si es necesario
            $filePath = self::ALMACEN_DIR . '/' . $ot . '/ayudas_visuales/' . $archivo;
        } else {
            $filePath = self::ALMACEN_DIR . '/' . $ot . '/' . $archivo;
        }

        if (!Storage::disk('local')->exists($filePath)) {
            abort(404, 'Archivo no encontrado en el directorio de Almacén.');
        }

        /** @var \Illuminate\Filesystem\FilesystemAdapter $disk */
        $disk     = Storage::disk('local');
        $fullPath = $disk->path($filePath);

        return response()->file($fullPath, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . basename($archivo) . '"',
        ]);
    }

    // =========================================================================
    // HELPERS PRIVADOS
    // =========================================================================

    private function sanitizePath(string $path): string
    {
        $path = preg_replace('/\.\.+/', '', $path);
        $path = preg_replace('/[\/\\\\]/', '', $path);
        return trim($path);
    }

    private function sanitizeFileNameWithFolder(string $name): string
    {
        // Permitir un solo nivel de carpeta (ej: "Clase/archivo.pdf")
        // Bloquear .. y cualquier intento de subir de nivel
        $name = preg_replace('/\.\.+/', '', $name);
        $name = preg_replace('/[^a-zA-Z0-9_\-\.\s\/]/', '_', $name); // Permitir /
        return trim($name, '_.');
    }

    private function sanitizeFileName(string $name): string
    {
        $name = preg_replace('/\.\.+/', '', $name);
        $name = preg_replace('/[\/\\\\]/', '', $name);
        $name = preg_replace('/[^a-zA-Z0-9_\-\.\s]/', '_', $name);
        return trim($name, '_.') ?: 'archivo.pdf';
    }
}
