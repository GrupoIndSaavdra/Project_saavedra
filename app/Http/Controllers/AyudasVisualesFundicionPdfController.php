<?php

namespace App\Http\Controllers;

use App\Models\AyudaVisualFundicionFileLog;
use App\Models\AyudaVisualFundicionHistory;
use App\Models\Procesos;
use App\Models\Clase;
use App\Models\Fecha_proceso;
use App\Models\SystemLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class AyudasVisualesFundicionPdfController extends Controller
{
    private const BASE_DIR = 'DOCUMENTACION_GIS/AYUDAS_FUNDICION';

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

        // 1. Catálogo de Clases Únicas + Pistones y Guías
        $clasesBd = Clase::query()->select('nombre')->distinct()->pluck('nombre')->toArray();
        $clasesCompletas = array_unique(array_merge($clasesBd, ['Pistones', 'Guías']));
        sort($clasesCompletas);

        $clasesUnicas = collect($clasesCompletas)->map(function($clase) {
            return (object)[ 'id' => $clase, 'nombre' => $clase ];
        });

        $claseSeleccionadaId   = $request->query('clase_id');
        // Forzamos que el proceso sea Fundición
        $procesoSeleccionadoId = 'Fundicion';

        // 2. Catálogo de Procesos (Solo Fundición)
        $todosLosProcesos = collect([
            (object)[ 'id' => 'Fundicion', 'nombre' => 'Fundicion' ]
        ]);

        $claseActiva = $claseSeleccionadaId ? $clasesUnicas->firstWhere('id', $claseSeleccionadaId) : null;
        $procesoActivo = $todosLosProcesos->firstWhere('id', 'Fundicion');

        return view('wo_views.manage_documentation', array_merge(compact(
            'estructura',
            'todosLosProcesos',
            'clasesUnicas',
            'procesoSeleccionadoId',
            'claseSeleccionadaId',
            'procesoActivo',
            'claseActiva'
        ), [
            'moduleType' => 'ayudas_fundicion',
            'modulePrefix' => 'ayudas_fundicion',
            'pageTitle' => 'Ayudas Visuales de Fundición',
            'directoryName' => 'DOCUMENTACION_GIS / AYUDAS_FUNDICION',
            'moduleMetadata' => [
                'description' => 'Selecciona la clase para administrar sus ayudas maestras.'
            ]
        ]));
    }

    public function getLog()
    {
        $logs = AyudaVisualFundicionFileLog::query()
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
     * @param \Illuminate\Http\Request Request $request
     */
    public function getFiles(Request $request)
    {
        $clase = $this->sanitizePath($request->query('clase', ''));

        if (empty($clase)) {
            return response()->json(['error' => 'Parámetro Clase es requerido.'], 422);
        }

        // Buscar en las tres posibles ubicaciones (nuevo, legacy intermedio, legacy antiguo)
        $paths = [
            self::BASE_DIR     . '/' . $clase,                        // Nuevo: DOCUMENTACION_GIS/AYUDAS_FUNDICION/{Clase}
            self::BASE_DIR     . '/' . $clase . '/Fundicion',          // Legacy intermedio: {Clase}/Fundicion
            self::OLD_BASE_DIR . '/' . $clase . '/Fundicion',          // Legacy antiguo: AYUDAS_GIS/{Clase}/Fundicion
        ];

        $allRawFiles = [];
        foreach ($paths as $path) {
            if (Storage::disk('local')->exists($path)) {
                $allRawFiles = array_merge($allRawFiles, Storage::disk('local')->files($path));
            }
        }

        $allFiles = collect($allRawFiles)
            ->filter(fn($f) => strtolower(pathinfo($f, PATHINFO_EXTENSION)) === 'pdf')
            ->map(function($f) use ($clase) {
                $rawName = basename($f);
                $utf8Name = $this->toUtf8($rawName);
                return [
                    'nombre' => $utf8Name,
                    'url'    => route('ayudas_fundicion.serve', [
                        'clase'   => $clase,
                        'archivo' => $utf8Name,
                    ]),
                ];
            })
            ->unique('nombre')
            ->values();

        return response()->json([
            'archivos' => $allFiles,
            'clase'    => $clase,
            'existe'   => ($allFiles->count() > 0),
        ]);
    }

        /**
     * @param \Illuminate\Http\Request Request $request
     */
    public function serveFile(Request $request): BinaryFileResponse
    {
        $clase   = $this->sanitizePath($request->query('clase', ''));
        $archivo = $this->sanitizeFileName($request->query('archivo', ''));

        if (empty($clase) || empty($archivo)) {
            abort(422, 'Parámetros inválidos.');
        }

        // Buscar en los tres posibles directorios
        $candidateDirs = [
            self::BASE_DIR     . '/' . $clase,
            self::BASE_DIR     . '/' . $clase . '/Fundicion',
            self::OLD_BASE_DIR . '/' . $clase . '/Fundicion',
        ];

        $foundFile = null;
        foreach ($candidateDirs as $dir) {
            if (Storage::disk('local')->exists($dir)) {
                $files = Storage::disk('local')->files($dir);
                foreach ($files as $f) {
                    $rawName = basename($f);
                    $utf8Name = $this->toUtf8($rawName);
                    if ($utf8Name === $archivo) {
                        $foundFile = $f;
                        break 2;
                    }
                }
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
     * @param \Illuminate\Http\Request Request $request
     */
    public function createFolder(Request $request)
    {
        try {
            $request->validate([
                'clase'   => 'required|string|max:100',
            ]);

            $clase   = $this->sanitizePath($request->input('clase'));
            $dirPath = self::BASE_DIR . '/' . $clase;

            if (Storage::disk('local')->exists($dirPath)) {
                return response()->json([
                    'success' => false,
                    'message' => 'La carpeta ya existe.',
                ], 409);
            }

            Storage::disk('local')->makeDirectory($dirPath);

            AyudaVisualFundicionHistory::firstOrCreate(['proceso' => 'Fundicion', 'clase' => $clase]);
            $this->logAction('crear_carpeta', $clase, null);

            return response()->json([
                'success' => true,
                'message' => "Carpeta para la clase {$clase} creada correctamente.",
                'clase'   => $clase,
            ]);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("Error en AyudasVisualesFundicionPdfController@createFolder: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error interno al crear la carpeta: ' . $e->getMessage(),
            ], 500);
        }
    }

        /**
     * @param \Illuminate\Http\Request Request $request
     */
    public function uploadPdf(Request $request)
    {
        $request->validate([
            'clase'   => 'required|string|max:100',
            'pdf'     => 'required|file|mimes:pdf',
        ]);

        $clase   = $this->sanitizePath($request->input('clase'));
        $dirPath = self::BASE_DIR . '/' . $clase;

        if (!Storage::disk('local')->exists($dirPath)) {
            Storage::disk('local')->makeDirectory($dirPath);
            AyudaVisualFundicionHistory::firstOrCreate(['proceso' => 'Fundicion', 'clase' => $clase]);
        }

        $file         = $request->file('pdf');
        $originalName = $this->sanitizeFileName($file->getClientOriginalName());
        $finalName    = $clase . ' - ' . $originalName;

        if (Storage::disk('local')->exists($dirPath . '/' . $finalName)) {
            return response()->json([
                'success' => false,
                'message' => "Ya existe un archivo con el nombre '{$finalName}'. Use la función de Reemplazar.",
            ], 409);
        }

        $file->storeAs($dirPath, $finalName, 'local');

        $this->logAction('subir_pdf', $clase, $finalName);

        return response()->json([
            'success'  => true,
            'message'  => "PDF '{$finalName}' subido correctamente.",
            'nombre'   => $finalName,
            'url'      => route('ayudas_fundicion.serve', [
                'clase'   => $clase,
                'archivo' => $finalName,
            ]),
        ]);
    }

        /**
     * @param \Illuminate\Http\Request Request $request
     */
    public function deletePdf(Request $request)
    {
        $request->validate([
            'clase'   => 'required|string|max:100',
            'archivo' => 'required|string|max:300',
        ]);

        $clase   = $this->sanitizePath($request->input('clase'));
        $archivo = $this->sanitizeFileName($request->input('archivo'));

        // Buscar en las tres posibles ubicaciones (directorios)
        $candidateDirs = [
            self::BASE_DIR     . '/' . $clase,
            self::BASE_DIR     . '/' . $clase . '/Fundicion',
            self::OLD_BASE_DIR . '/' . $clase . '/Fundicion',
        ];

        $found = null;
        foreach ($candidateDirs as $dir) {
            if (Storage::disk('local')->exists($dir)) {
                $files = Storage::disk('local')->files($dir);
                foreach ($files as $f) {
                    $rawName = basename($f);
                    $utf8Name = $this->toUtf8($rawName);
                    if ($utf8Name === $archivo) {
                        $found = $f;
                        break 2;
                    }
                }
            }
        }

        if (!$found) {
            return response()->json([
                'success' => false,
                'message' => 'El archivo no existe.',
            ], 404);
        }

        // No permitir eliminar desde el OLD_BASE_DIR (solo lectura)
        if (str_starts_with($found, self::OLD_BASE_DIR)) {
            return response()->json([
                'success' => false,
                'message' => 'Los archivos en la ruta legada (AYUDAS_GIS) son de solo lectura.',
            ], 403);
        }

        Storage::disk('local')->delete($found);
        $this->logAction('eliminar_pdf', $clase, $archivo);

        return response()->json([
            'success' => true,
            'message' => "Archivo '{$archivo}' eliminado correctamente.",
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
            'clase'   => 'required|string|max:100',
        ]);

        $clase     = $this->sanitizePath($request->input('clase'));
        $dirPath   = self::BASE_DIR . '/' . $clase;
        $legacyDir = self::BASE_DIR . '/' . $clase . '/Fundicion'; // Ruta intermedia legacy

        if (!Storage::disk('local')->exists($dirPath)) {
            return response()->json([
                'success' => false,
                'message' => 'La carpeta no existe.',
            ], 404);
        }

        // Recopilar archivos directos + archivos en subcarpeta /Fundicion (legacy)
        $filesDirectos = Storage::disk('local')->files($dirPath);
        $filesLegacy   = Storage::disk('local')->exists($legacyDir)
            ? Storage::disk('local')->files($legacyDir)
            : [];
        $allFiles = array_merge($filesDirectos, $filesLegacy);

        if (count($allFiles) > 0) {
            Storage::disk('local')->delete($allFiles);
            // Si la subcarpeta /Fundicion quedó vacía, eliminarla también
            if (Storage::disk('local')->exists($legacyDir) && count(Storage::disk('local')->files($legacyDir)) === 0) {
                Storage::disk('local')->deleteDirectory($legacyDir);
            }
            $this->logAction('vaciar_carpeta', $clase, null);
            return response()->json([
                'success' => true,
                'message' => "Se eliminaron " . count($allFiles) . " archivos de la clase '{$clase}'.",
            ]);
        }

        Storage::disk('local')->deleteDirectory($dirPath);
        $this->logAction('eliminar_carpeta', $clase, null);

        return response()->json([
            'success' => true,
            'message' => "La carpeta de la clase '{$clase}' fue eliminada correctamente.",
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

        $clase = $this->sanitizePath($request->input('proceso')); // Padre es la Clase
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
        ]);
    }

        /**
     * @param \Illuminate\Http\Request Request $request
     */
    public function replacePdf(Request $request)
    {
        $request->validate([
            'clase'           => 'required|string|max:100',
            'archivo_anterior'=> 'required|string|max:300',
            'pdf'             => 'required|file|mimes:pdf',
        ]);

        $clase           = $this->sanitizePath($request->input('clase'));
        $archivoAnterior = $this->sanitizeFileName($request->input('archivo_anterior'));
        $dirPath         = self::BASE_DIR . '/' . $clase;
        
        $files = Storage::disk('local')->exists($dirPath) ? Storage::disk('local')->files($dirPath) : [];
        $foundFile = null;
        foreach ($files as $f) {
            $rawName = basename($f);
            $utf8Name = $this->toUtf8($rawName);
            if ($utf8Name === $archivoAnterior) {
                $foundFile = $f;
                break;
            }
        }

        if ($foundFile) {
            Storage::disk('local')->delete($foundFile);
        }

        $file         = $request->file('pdf');
        $originalName = $this->sanitizeFileName($file->getClientOriginalName());
        $finalName    = $clase . ' - ' . $originalName;

        $file->storeAs($dirPath, $finalName, 'local');

        $this->logAction('reemplazar_pdf', $clase, "{$archivoAnterior} → {$finalName}");

        return response()->json([
            'success'  => true,
            'message'  => "Archivo reemplazado: '{$archivoAnterior}' → '{$finalName}'.",
            'nombre'   => $finalName,
            'url'      => route('ayudas_fundicion.serve', [
                'clase'   => $clase,
                'archivo' => $finalName,
            ]),
        ]);
    }

    // =========================================================================
    // HELPERS PRIVADOS
    // =========================================================================

    private function buildStructure(): array
    {
        $estructura = [];

        if (Storage::disk('local')->exists(self::BASE_DIR)) {
            $claseDirs = Storage::disk('local')->directories(self::BASE_DIR);
            foreach ($claseDirs as $claseDir) {
                $claseName = $this->toUtf8(basename($claseDir));
                // Detectar como existente si tiene archivos directos O tiene la subcarpeta /Fundicion (legacy)
                $hasDirectFiles  = count(Storage::disk('local')->files($claseDir)) > 0;
                $hasLegacySubDir = Storage::disk('local')->exists($claseDir . '/Fundicion');
                if ($hasDirectFiles || $hasLegacySubDir) {
                    $estructura[$claseName] = true;
                } else {
                    // Carpeta vacía pero existe — la incluimos igualmente para poder eliminarla
                    $estructura[$claseName] = true;
                }
            }
        }

        // También detectar clases que solo existen en AYUDAS_GIS (legado puro)
        if (Storage::disk('local')->exists(self::OLD_BASE_DIR)) {
            $oldClassDirs = Storage::disk('local')->directories(self::OLD_BASE_DIR);
            foreach ($oldClassDirs as $oldClaseDir) {
                $claseName = $this->toUtf8(basename($oldClaseDir));
                if (!isset($estructura[$claseName])) {
                    $estructura[$claseName] = true;
                }
            }
        }

        ksort($estructura, SORT_NATURAL);
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

        AyudaVisualFundicionFileLog::create([
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
                'subir_pdf'        => "El administrador subió la ayuda visual de fundición '{$archivo}' (Clase: {$ruta}).",
                'eliminar_pdf'     => "El administrador eliminó la ayuda visual de fundición '{$archivo}' (Clase: {$ruta}).",
                'reemplazar_pdf'   => "El administrador reemplazó la ayuda visual de fundición (Clase: {$ruta}): {$archivo}.",
                'crear_carpeta'    => "El administrador creó la carpeta de ayudas de fundición: {$ruta}.",
                'vaciar_carpeta'   => "El administrador vació la carpeta de ayudas de fundición: {$ruta}.",
                'eliminar_carpeta' => "El administrador eliminó la carpeta de ayudas de fundición: {$ruta}.",
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
