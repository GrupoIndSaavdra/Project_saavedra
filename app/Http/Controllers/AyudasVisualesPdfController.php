<?php

namespace App\Http\Controllers;

use App\Models\AyudaVisualFileLog;
use App\Models\AyudaVisualHistory;
use App\Models\Procesos;
use App\Models\Clase;
use App\Models\Fecha_proceso;
use App\Models\SystemLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class AyudasVisualesPdfController extends Controller
{
    private const BASE_DIR = 'DOCUMENTACION_GIS/AYUDAS_MAQUINADOS';

    /**
     * Directorio legado para compatibilidad con archivos anteriores.
     */
    private const OLD_BASE_DIR = 'AYUDAS_GIS';

    // =========================================================================
    // VISTAS
    // =========================================================================

    /**
     * Vista de administración del módulo de ayudas visuales.
     */
    public function showManage(Request $request)
    {
        $estructura = $this->buildStructure();

        // 1. Catálogo de Clases Únicas
                        $procesoSeleccionadoId = $request->query('proceso_id');

        // 2. Catálogo de Procesos Estándar (Completo)
        $nombresProcesos = [
            'Cepillado', 'Desbaste Exterior', 'Revision Laterales', 'Primera Operacion',
            'Barreno Maniobra', 'Segunda Operacion', 'Soldadura', 'Soldadura PTA',
            'Rectificado', 'Asentado', 'Calificado', 'Acabado Bombillo', 'Acabado Molde',
            'Barreno Profundidad', 'Cavidades', 'Copiado', 'Off Set', 'Palomas',
            'Rebajes', 'Grabado', 'Operacion Equipo', 'Embudo CM',
            'Primera Operacion Cabeza Soplo', 'Segunda Operacion Cabeza Soplo'
        ];

        $todosLosProcesos = collect($nombresProcesos)->map(function($nombre) {
            return (object)[ 'id' => $nombre, 'nombre' => $nombre ];
        })->sortBy('nombre')->values();

                $procesoActivo = $procesoSeleccionadoId ? $todosLosProcesos->firstWhere('id', $procesoSeleccionadoId) : null;

        return view('wo_views.manage_ayudas', array_merge(compact(
            'estructura',
            'todosLosProcesos',
                        'procesoSeleccionadoId',
                        'procesoActivo',
                    ), [
            'moduleType' => 'ayudas',
            'modulePrefix' => 'ayudas',
            'pageTitle' => 'Ayudas Visuales de Maquinados',
            'directoryName' => 'DOCUMENTACION_GIS / AYUDAS_MAQUINADOS',
            'moduleMetadata' => [
                'description' => 'Selecciona el proceso.'
            ]
        ]));
    }

    public function getLog()
    {
        $logs = AyudaVisualFileLog::query()
            ->orderByDesc('id')
            ->limit(50)
            ->get(['id', 'user_name', 'action', 'ruta', 'archivo', 'created_at'])
            ->map(function ($log) {
                return [
                    'created_at' => $log->created_at->format('d/m/Y H:i:s'),
                    'user_name'  => $log->user_name,
                    'action'     => $log->action,
                    'ruta'       => $log->ruta,
                    'archivo'    => $log->archivo,
                ];
            });

        return response()->json(['logs' => $logs]);
    }

    // =========================================================================
    // API DE LECTURA
    // =========================================================================

    public function getStructure()
    {
        $estructura = $this->buildStructure();
        return response()->json($estructura);
    }

    /**
     * @param Request $request
     */
    public function getFiles(Request $request)
    {
        try {
            $proceso = $this->sanitizePath($request->query('proceso', ''));

            if (empty($proceso)) {
                return response()->json(['error' => 'Parámetro Proceso es requerido.'], 422);
            }

            $newDirPath = self::BASE_DIR . '/' . $proceso;
            $oldDirPath = self::OLD_BASE_DIR . '/' . $proceso;

            $newFiles = Storage::disk('local')->exists($newDirPath) ? Storage::disk('local')->files($newDirPath) : [];
            $oldFiles = Storage::disk('local')->exists($oldDirPath) ? Storage::disk('local')->files($oldDirPath) : [];

            $allFiles = collect(array_merge($newFiles, $oldFiles))
                ->filter(fn($f) => strtolower(pathinfo($f, PATHINFO_EXTENSION)) === 'pdf')
                ->map(function($f) use ($proceso) {
                    $rawName = basename($f);
                    $utf8Name = $this->toUtf8($rawName);
                    return [
                        'nombre' => $utf8Name,
                        'url'    => url('/ayudas/serve') . '?proceso=' . urlencode($proceso) . '&archivo=' . urlencode($utf8Name),
                    ];
                })
                ->unique('nombre')
                ->values();

            return response()->json([
                'archivos' => $allFiles,
                'proceso'  => $proceso,
                'existe'   => (count($allFiles) > 0),
            ]);
        } catch (\Throwable $e) {
            $errorMsg = $e->getMessage();
            if (function_exists('mb_convert_encoding')) {
                $errorMsg = @mb_convert_encoding($errorMsg, 'UTF-8', 'UTF-8');
            }
            return response()->json([
                'success' => false,
                'error' => $errorMsg,
                'archivos' => [],
                'existe' => false
            ], 200);
        }
    }

        /**
     * @param Request $request
     */
    public function serveFile(Request $request): BinaryFileResponse
    {
        $proceso = $this->sanitizePath($request->query('proceso', ''));
        $archivo = $this->sanitizeFileName($request->query('archivo', ''));

        if (empty($proceso) || empty($archivo)) {
            abort(422, 'Parámetros inválidos.');
        }

        $dirPath = self::BASE_DIR . '/' . $proceso;

        // Fallback
        if (!Storage::disk('local')->exists($dirPath)) {
            $dirPath = self::OLD_BASE_DIR . '/' . $proceso;
        }

        if (!Storage::disk('local')->exists($dirPath)) {
            abort(404, 'Archivo no encontrado.');
        }

        $files = Storage::disk('local')->files($dirPath);
        $foundFile = null;
        $archivoNorm = \Normalizer::normalize(mb_strtolower($archivo, 'UTF-8'), \Normalizer::FORM_C);
        foreach ($files as $f) {
            $rawName = basename($f);
            $utf8Name = $this->toUtf8($rawName);
            $utf8NameNorm = \Normalizer::normalize(mb_strtolower($utf8Name, 'UTF-8'), \Normalizer::FORM_C);
            if ($utf8NameNorm === $archivoNorm) {
                $foundFile = $f;
                break;
            }
        }

        if (!$foundFile) {
            abort(404, 'Archivo no encontrado.');
        }

        /** @var \Illuminate\Filesystem\FilesystemAdapter $disk */
        $disk     = Storage::disk('local');
        $fullPath = $disk->path($foundFile);

        return response()->file($fullPath, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . $archivo . '"',
        ]);
    }

    // =========================================================================
    // CRUD ADMINISTRADOR
    // =========================================================================

        /**
     * @param Request $request
     */
    public function createFolder(Request $request)
    {
        try {
            $request->validate([
                'proceso' => 'required|string|max:100',
                
            ]);

            $proceso = $this->sanitizePath($request->input('proceso'));
            
            $dirPath = self::BASE_DIR . '/' . $proceso;

            if (Storage::disk('local')->exists($dirPath)) {
                return response()->json([
                    'success' => false,
                    'message' => 'La carpeta ya existe.',
                ], 409);
            }

            Storage::disk('local')->makeDirectory($dirPath);

            $history = AyudaVisualHistory::query()->where('proceso', $proceso)->first();
            if (!$history) {
                $history = new AyudaVisualHistory();
                $history->proceso = $proceso;
                $history->clase = 'N/A';
                $history->save();
            }
            $this->logAction('crear_carpeta', $proceso, null);

            return response()->json([
                'success' => true,
                'message' => "Carpeta del proceso '{$proceso}' creada correctamente.",
                'proceso' => $proceso,
                
            ]);
        } catch (\Exception $e) {
            Log::error("Error en AyudasVisualesPdfController@createFolder: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error interno al crear la carpeta: ' . $e->getMessage(),
            ], 500);
        }
    }

        /**
     * @param Request $request
     */
    public function uploadPdf(Request $request)
    {
        $request->validate([
            'proceso' => 'required|string|max:100',
            
            'pdf'     => 'required|file|mimes:pdf',
        ]);

        $proceso = $this->sanitizePath($request->input('proceso'));
        
        $dirPath = self::BASE_DIR . '/' . $proceso;

        if (!Storage::disk('local')->exists($dirPath)) {
            Storage::disk('local')->makeDirectory($dirPath);
            $history = AyudaVisualHistory::query()->where('proceso', $proceso)->first();
            if (!$history) {
                $history = new AyudaVisualHistory();
                $history->proceso = $proceso;
                $history->clase = 'N/A';
                $history->save();
            }
        }

        $file         = $request->file('pdf');
        $originalName = $this->sanitizeFileName($file->getClientOriginalName());
        $finalName = $originalName;

        if (Storage::disk('local')->exists($dirPath . '/' . $finalName)) {
            return response()->json([
                'success' => false,
                'message' => "Ya existe un archivo con el nombre '{$finalName}'. Use la función de Reemplazar.",
            ], 409);
        }

        $file->storeAs($dirPath, $finalName, 'local');

        $this->logAction('subir_pdf', $proceso, $finalName);

        return response()->json([
            'success'  => true,
            'message'  => "PDF '{$finalName}' subido correctamente.",
            'nombre'   => $finalName,
            'url'      => url('/ayudas/serve') . '?proceso=' . urlencode($proceso) . '&archivo=' . urlencode($finalName),
            'proceso'  => $proceso,
        ]);
    }

        /**
     * @param Request $request
     */
    public function deletePdf(Request $request)
    {
        $request->validate([
            'proceso' => 'required|string|max:100',
            
            'archivo' => 'required|string|max:300',
        ]);

        $proceso  = $this->sanitizePath($request->input('proceso'));
        
        $archivo  = $this->sanitizeFileName($request->input('archivo'));
        
        $dirPath = self::BASE_DIR . '/' . $proceso;
        $files = Storage::disk('local')->exists($dirPath) ? Storage::disk('local')->files($dirPath) : [];
        
        $foundFile = null;
        $archivoNorm = \Normalizer::normalize(mb_strtolower($archivo, 'UTF-8'), \Normalizer::FORM_C);
        foreach ($files as $f) {
            $rawName = basename($f);
            $utf8Name = $this->toUtf8($rawName);
            $utf8NameNorm = \Normalizer::normalize(mb_strtolower($utf8Name, 'UTF-8'), \Normalizer::FORM_C);
            if ($utf8NameNorm === $archivoNorm) {
                $foundFile = $f;
                break;
            }
        }

        if (!$foundFile) {
            // Fallback for read-only error
            $oldDirPath = self::OLD_BASE_DIR . '/' . $proceso;
            $oldFiles = Storage::disk('local')->exists($oldDirPath) ? Storage::disk('local')->files($oldDirPath) : [];
            $archivoNorm = \Normalizer::normalize(mb_strtolower($archivo, 'UTF-8'), \Normalizer::FORM_C);
            foreach ($oldFiles as $f) {
                $rawName = basename($f);
                $utf8Name = $this->toUtf8($rawName);
                $utf8NameNorm = \Normalizer::normalize(mb_strtolower($utf8Name, 'UTF-8'), \Normalizer::FORM_C);
                if ($utf8NameNorm === $archivoNorm) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Las ayudas visuales antiguas son de solo lectura.',
                    ], 403);
                }
            }

            return response()->json([
                'success' => false,
                'message' => 'El archivo no existe.',
            ], 404);
        }

        Storage::disk('local')->delete($foundFile);
        $this->logAction('eliminar_pdf', $proceso, $archivo);

        return response()->json([
            'success' => true,
            'message' => "Archivo '{$archivo}' eliminado correctamente.",
            'proceso' => $proceso,
        ]);
    }

    /**
     * Elimina una carpeta completa (el Proceso dentro de una Clase).
     *
     * POST /ayudas/deleteFolder
     * Body: { proceso, clase }
     */
    public function deleteFolder(Request $request)
    {
        $request->validate([
            'proceso' => 'required|string|max:100',
            
        ]);

        $proceso = $this->sanitizePath($request->input('proceso'));
        
        $dirPath = self::BASE_DIR . '/' . $proceso;

        if (!Storage::disk('local')->exists($dirPath)) {
            return response()->json([
                'success' => false,
                'message' => 'La carpeta no existe.',
            ], 404);
        }

        $files = Storage::disk('local')->files($dirPath);
        if (count($files) > 0) {
            Storage::disk('local')->delete($files);
            $this->logAction('vaciar_carpeta', $proceso, null);
            return response()->json([
                'success' => true,
                'message' => "Se eliminaron " . count($files) . " archivos del proceso '{$proceso}'.",
                'proceso' => $proceso,
                'vaciada' => true,
            ]);
        }

        Storage::disk('local')->deleteDirectory($dirPath);
        $this->logAction('eliminar_carpeta', $proceso, null);

        return response()->json([
            'success' => true,
            'message' => "La carpeta del proceso '{$proceso}' fue eliminada correctamente.",
            'proceso' => $proceso,
        ]);
    }

    /**
     * Elimina la carpeta principal de un Proceso si está vacía.
     *
     * POST /ayudas/deleteParent
     * Body: { proceso }
     */
    public function deleteParent(Request $request)
    {
        $request->validate([
            'proceso' => 'required|string|max:200',
        ]);

        $clase   = $this->sanitizePath($request->input('proceso')); // En el swap, el padre es la Clase
        $dirPath = self::BASE_DIR . '/' . $clase;

        if (!Storage::disk('local')->exists($dirPath)) {
            return response()->json([
                'success' => false,
                'message' => 'La carpeta del proceso no existe.',
            ], 404);
        }

        // Seguridad: Verificar subcarpetas
        $subDirs = Storage::disk('local')->directories($dirPath);
        if (count($subDirs) > 0) {
            return response()->json([
                'success' => false,
                'message' => 'No se puede eliminar: el proceso todavía tiene subcarpetas (clases).',
            ], 400);
        }

        // Seguridad: Verificar archivos
        $files = Storage::disk('local')->files($dirPath);
        if (count($files) > 0) {
            return response()->json([
                'success' => false,
                'message' => 'No se puede eliminar: la carpeta todavía contiene archivos.',
            ], 400);
        }

        Storage::disk('local')->deleteDirectory($dirPath);
        $this->logAction('eliminar_carpeta', $clase, null);

        return response()->json([
            'success' => true,
            'message' => "La carpeta de la clase '{$clase}' fue eliminada correctamente.",
            'proceso' => $clase,
        ]);
    }

        /**
     * @param Request $request
     */
    public function replacePdf(Request $request)
    {
        $request->validate([
            'proceso'         => 'required|string|max:100',
            
            'archivo_anterior'=> 'required|string|max:300',
            'pdf'             => 'required|file|mimes:pdf',
        ]);

        $proceso         = $this->sanitizePath($request->input('proceso'));
        
        $archivoAnterior = $this->sanitizeFileName($request->input('archivo_anterior'));
        $dirPath         = self::BASE_DIR . '/' . $proceso;
        
        $files = Storage::disk('local')->exists($dirPath) ? Storage::disk('local')->files($dirPath) : [];
        $foundFile = null;
        $archivoAnteriorNorm = \Normalizer::normalize(mb_strtolower($archivoAnterior, 'UTF-8'), \Normalizer::FORM_C);
        foreach ($files as $f) {
            $rawName = basename($f);
            $utf8Name = $this->toUtf8($rawName);
            $utf8NameNorm = \Normalizer::normalize(mb_strtolower($utf8Name, 'UTF-8'), \Normalizer::FORM_C);
            if ($utf8NameNorm === $archivoAnteriorNorm) {
                $foundFile = $f;
                break;
            }
        }

        if ($foundFile) {
            Storage::disk('local')->delete($foundFile);
        }

        $file         = $request->file('pdf');
        $originalName = $this->sanitizeFileName($file->getClientOriginalName());
        $finalName = $originalName;

        $file->storeAs($dirPath, $finalName, 'local');

        $this->logAction('reemplazar_pdf', $proceso, "{$archivoAnterior} → {$finalName}");

        return response()->json([
            'success'  => true,
            'message'  => "Archivo reemplazado: '{$archivoAnterior}' → '{$finalName}'.",
            'nombre'   => $finalName,
            'url'      => url('/ayudas/serve') . '?proceso=' . urlencode($proceso) . '&archivo=' . urlencode($finalName),
            'proceso'  => $proceso,
        ]);
    }

    // =========================================================================
    // HELPERS PRIVADOS
    // =========================================================================

    
    private function buildStructure(): array
    {
        $estructura = [];

        // 1. Escaneo del directorio Nuevo
        if (Storage::disk('local')->exists(self::BASE_DIR)) {
            $pDirs = Storage::disk('local')->directories(self::BASE_DIR);
            foreach ($pDirs as $pDir) {
                $pName = $this->toUtf8(basename($pDir)); // Proceso
                $estructura[] = $pName;
            }
        }

        // 2. Escaneo del directorio Viejo (Fallback)
        if (Storage::disk('local')->exists(self::OLD_BASE_DIR)) {
            $pDirs = Storage::disk('local')->directories(self::OLD_BASE_DIR);
            foreach ($pDirs as $pDir) {
                $pName = $this->toUtf8(basename($pDir));
                if (!in_array($pName, $estructura)) {
                    $estructura[] = $pName;
                }
            }
        }

        sort($estructura, SORT_NATURAL);
        return $estructura;
    }



    /**
     * @param string $action
     * @param string $ruta
     * @param string|null $archivo
     */
    private function logAction(string $action, string $ruta, ?string $archivo): void
    {
        $user     = Auth::user();
        $userName = null;

        if ($user) {
            $userName = trim(
                ($user->matricula ?? '') . ' - ' .
                ($user->nombre ?? '') . ' ' .
                ($user->a_paterno ?? '') . ' ' .
                ($user->a_materno ?? '')
            );
        }

        AyudaVisualFileLog::create([
            'user_id'   => $user?->id,
            'user_name' => $userName,
            'action'    => $action,
            'ruta'      => $ruta,
            'archivo'   => $archivo,
        ]);

        // Mapeo de acciones técnicas a acciones legibles para system_logs
        $actionMap = [
            'subir_pdf'        => 'Subida de Ayuda Visual',
            'eliminar_pdf'     => 'Eliminación de Ayuda Visual',
            'reemplazar_pdf'   => 'Reemplazo de Ayuda Visual',
            'crear_carpeta'    => 'Creación de Carpeta',
            'vaciar_carpeta'   => 'Eliminación de Ayuda Visual',
            'eliminar_carpeta' => 'Eliminación de Ayuda Visual',
        ];
        $systemAction = $actionMap[$action] ?? null;
        if ($systemAction && $user) {
            $detailsMap = [
                'subir_pdf'        => "El administrador subió la ayuda visual '{$archivo}' en la ruta {$ruta}.",
                'eliminar_pdf'     => "El administrador eliminó la ayuda visual '{$archivo}' de la ruta {$ruta}.",
                'reemplazar_pdf'   => "El administrador reemplazó la ayuda visual en {$ruta}: {$archivo}.",
                'crear_carpeta'    => "El administrador creó la carpeta de ayudas visuales: {$ruta}.",
                'vaciar_carpeta'   => "El administrador vació la carpeta de ayudas visuales: {$ruta}.",
                'eliminar_carpeta' => "El administrador eliminó la carpeta de ayudas visuales: {$ruta}.",
            ];
            SystemLog::create([
                'user_matricula' => $user->matricula,
                'action'         => $systemAction,
                'details'        => $detailsMap[$action] ?? "Administrador realizó la acción '{$action}' en {$ruta}.",
            ]);
        }
    }

        /**
     * @param mixed string $path
     */
    private function sanitizePath(string $path): string
    {
        $path = preg_replace('/\.\.+/', '', $path);
        $path = preg_replace('/[\/\\\\]/', '', $path);
        $path = trim($path);
        return $path;
    }

        /**
     * @param mixed string $name
     */
    private function sanitizeFileName(string $name): string
    {
        $name = preg_replace('/[\/\\\\]/', '', $name);
        $name = preg_replace('/\.\.+/', '', $name);
        return trim($name) ?: 'archivo.pdf';
    }

    private function toUtf8(string $string): string
    {
        if (!mb_check_encoding($string, 'UTF-8')) {
            return mb_convert_encoding($string, 'UTF-8', 'Windows-1252');
        }
        return $string;
    }
}
