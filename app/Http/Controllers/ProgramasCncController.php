<?php

namespace App\Http\Controllers;

use App\Models\ProgramaCncFileLog;
use App\Models\Orden_trabajo;
use App\Models\Clase;
use App\Models\Procesos;
use App\Models\SystemLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ProgramasCncController extends Controller
{
    private const BASE_DIR = 'DOCUMENTACION_GIS/PROGRAMAS_MAQUINADOS';

    /** Extensiones permitidas para programas CNC */
    private const ALLOWED_EXTENSIONS = ['nc', 'cnc'];

    /** Mapa de columnas en la tabla `procesos` → nombre legible */
    private const PROCESS_COLUMN_MAP = [
        'cepillado' => 'Cepillado',
        'desbaste_exterior' => 'Desbaste Exterior',
        'revision_laterales' => 'Revision Laterales',
        'pOperacion' => 'Primera Operacion',
        'barreno_maniobra' => 'Barreno Maniobra',
        'sOperacion' => 'Segunda Operacion',
        'soldadura' => 'Soldadura',
        'soldaduraPTA' => 'Soldadura PTA',
        'rectificado' => 'Rectificado',
        'asentado' => 'Asentado',
        'calificado' => 'Calificado',
        'acabadoBombillo' => 'Acabado Bombillo',
        'acabadoMolde' => 'Acabado Molde',
        'barreno_profundidad' => 'Barreno Profundidad',
        'cavidades' => 'Cavidades',
        'copiado' => 'Copiado',
        'offSet' => 'Off Set',
        'palomas' => 'Palomas',
        'rebajes' => 'Rebajes',
        'grabado' => 'Grabado',
        'operacionEquipo' => 'Operacion Equipo',
        'embudoCM' => 'Embudo CM',
        'primeraOperacionCabezaSoplo' => 'Primera Operacion Cabeza Soplo',
        'segundaOperacionCabezaSoplo' => 'Segunda Operacion Cabeza Soplo',
        'candadoObturador' => 'Candado Obturador',
    ];

    // =========================================================================
    // VISTAS
    // =========================================================================

    /**
     * Vista de administración del módulo de programas CNC.
     * Solo accesible para administradores (perfil 1) y masters (perfil 3).
     */
    public function showManage(Request $request)
    {
        $estructura = $this->buildStructure();

        try {
            $todasLasOTs = Orden_trabajo::query()->with([
                'moldura',
                'clases' => fn($q) => $q->where('finalizada', 0)->orderBy('nombre'),
            ])
                ->whereHas('clases', fn($q) => $q->where('finalizada', 0))
                ->orderBy('id', 'asc')
                ->get();
        } catch (\Throwable $e) {
            Log::warning('Error DB en ProgramasCncController@showManage: ' . $e->getMessage());
            $todasLasOTs = collect();
        }

        $otSeleccionadaId = $request->query('ot_id');
        $claseSeleccionadaId = $request->query('clase_id');
        $procesoSeleccionado = $request->query('proceso') ?? $request->query('proceso_id');

        $otActiva = $otSeleccionadaId ? $todasLasOTs->firstWhere('id', $otSeleccionadaId) : null;
        $claseActiva = $claseSeleccionadaId ? optional($otActiva?->clases)->firstWhere('id', $claseSeleccionadaId) : null;

        // Procesos disponibles para la clase activa (desde la BD)
        $procesosActivos = $claseActiva ? $this->getProcesosForClaseModel($claseActiva) : [];

        return view('wo_views.manage_programas', compact(
            'estructura',
            'todasLasOTs',
            'otSeleccionadaId',
            'claseSeleccionadaId',
            'procesoSeleccionado',
            'otActiva',
            'claseActiva',
            'procesosActivos',
        ));
    }

    // =========================================================================
    // API — Procesos por clase (para selectores en cascada)
    // =========================================================================

    /**
     * Devuelve los procesos activos para una clase dada (desde la tabla procesos).
     * GET /programas/procesos-clase?clase_id=X
     */
    public function getProcesosForClase(Request $request)
    {
        $claseId = $request->query('clase_id');
        if (!$claseId) {
            return response()->json(['procesos' => []]);
        }

        try {
            $clase = Clase::query()->find($claseId);
            if (!$clase) {
                return response()->json(['procesos' => []]);
            }
            return response()->json(['procesos' => $this->getProcesosForClaseModel($clase)]);
        } catch (\Throwable $e) {
            Log::warning('Error en ProgramasCncController@getProcesosForClase: ' . $e->getMessage());
            return response()->json(['procesos' => []]);
        }
    }

    /**
     * Obtiene la lista de procesos activos para un modelo Clase.
     * Lee la fila de `procesos` y filtra las columnas que tienen valor != 0.
     *
     * @return string[]  Lista de nombres de proceso legibles (ej. "Cepillado", "Copiado")
     */
    private function getProcesosForClaseModel(Clase $clase): array
    {
        $procesos = Procesos::query()->where('id_clase', $clase->id)->first();
        if (!$procesos) {
            // Si no hay fila, devolver los procesos esperados del tipo de clase
            return $this->getFallbackProcesses($clase);
        }

        $result = [];
        foreach (self::PROCESS_COLUMN_MAP as $column => $displayName) {
            try {
                $val = $procesos->$column ?? null;
                if ($val !== null && $val != 0) {
                    $result[] = $displayName;
                }
            } catch (\Throwable $e) {
                // Columna no existe en la tabla — ignorar
            }
        }

        if (empty($result)) {
            return $this->getFallbackProcesses($clase);
        }

        return $result;
    }

    /**
     * Retorna los procesos esperados según el tipo base de clase,
     * como fallback cuando la tabla procesos no tiene datos.
     *
     * @return string[]
     */
    private function getFallbackProcesses(Clase $clase): array
    {
        $baseType = $clase->getBaseType();
        $map = [
            'Molde' => ['Cepillado', 'Desbaste Exterior', 'Revision Laterales', 'Primera Operacion', 'Barreno Maniobra', 'Segunda Operacion', 'Soldadura', 'Soldadura PTA', 'Rectificado', 'Asentado', 'Calificado', 'Acabado Molde', 'Barreno Profundidad', 'Cavidades', 'Copiado', 'Off Set', 'Palomas', 'Rebajes', 'Grabado'],
            'Bombillo' => ['Cepillado', 'Desbaste Exterior', 'Revision Laterales', 'Primera Operacion', 'Barreno Maniobra', 'Segunda Operacion', 'Soldadura', 'Soldadura PTA', 'Rectificado', 'Asentado', 'Calificado', 'Acabado Bombillo', 'Barreno Profundidad', 'Cavidades', 'Copiado', 'Off Set', 'Palomas', 'Rebajes', 'Grabado'],
            'Obturador' => ['Operacion Equipo', 'Soldadura', 'Soldadura PTA'],
            'Fondo' => ['Operacion Equipo', 'Soldadura', 'Soldadura PTA'],
            'Corona' => ['Cepillado', 'Desbaste Exterior', 'Primera Operacion', 'Segunda Operacion', 'Soldadura', 'Soldadura PTA', 'Rectificado', 'Asentado', 'Calificado'],
            'Plato' => ['Barreno Maniobra', 'Operacion Equipo'],
            'Embudo' => ['Operacion Equipo', 'Embudo CM'],
            'Cabeza de Soplo' => ['Primera Operacion Cabeza Soplo', 'Segunda Operacion Cabeza Soplo'],
            'Candado Obturador' => ['Operacion Equipo'],
        ];

        return $map[$baseType] ?? [];
    }

    // =========================================================================
    // API DE LECTURA
    // =========================================================================

    /**
     * Devuelve las últimas 50 entradas del log de auditoría como JSON.
     */
    public function getLog()
    {
        $logs = ProgramaCncFileLog::query()
            ->orderByDesc('id')
            ->limit(50)
            ->get(['id', 'user_name', 'action', 'ruta', 'archivo', 'created_at'])
            ->map(function ($log) {
                return [
                    'created_at' => $log->created_at->format('d/m/Y H:i:s'),
                    'user_name' => $log->user_name,
                    'action' => $log->action,
                    'ruta' => $log->ruta,
                    'archivo' => $log->archivo,
                ];
            });

        return response()->json(['logs' => $logs]);
    }

    /**
     * Devuelve la estructura completa OT → Clase → [Proceso, ...] leyendo el filesystem.
     * GET /programas/estructura
     */
    public function getStructure()
    {
        return response()->json($this->buildStructure());
    }

    /**
     * Devuelve los archivos .NC/.CNC dentro de una carpeta OT/Clase/Proceso.
     * GET /programas/archivos?ot=OT001&clase=ClaseA&proceso=Cepillado
     */
    public function getFiles(Request $request)
    {
        try {
            $rawOt = $request->query('ot', '');
            $clase = $this->sanitizePath($request->query('clase', ''));
            $proceso = $this->sanitizePath($request->query('proceso', ''));

            if (empty($rawOt)) {
                return response()->json(['error' => 'Parámetro OT es requerido.'], 422);
            }

            $ot = $this->normalizeOTName($this->sanitizePath($rawOt));
            if (preg_match('/(?:OT\s*)?(\d+)/i', $rawOt, $otMatch)) {
                try {
                    $otIdNumber = $otMatch[1];
                    $otModel = Orden_trabajo::query()->with('moldura')->find($otIdNumber);
                    if ($otModel) {
                        $otLabel = 'OT ' . $otModel->id . ($otModel->moldura ? ' - ' . $otModel->moldura->nombre : '');
                        $ot = $this->normalizeOTName($this->sanitizePath($otLabel));
                    }
                } catch (\Throwable $e) {
                    Log::warning('Error DB en ProgramasCncController@getFiles: ' . $e->getMessage());
                }
            }

            // Construir ruta: OT / Clase / Proceso
            $parts = array_filter([$ot, $clase, $proceso]);
            $dirPath = self::BASE_DIR . '/' . implode('/', $parts);
            $resolvedDir = $this->resolveCaseInsensitivePath($dirPath);

            $files = [];
            if (Storage::disk('local')->exists($resolvedDir)) {
                $rawFiles = Storage::disk('local')->files($resolvedDir);
                foreach ($rawFiles as $f) {
                    $rawName = basename($f);
                    $utf8Name = $this->toUtf8($rawName);
                    $ext = strtolower(pathinfo($utf8Name, PATHINFO_EXTENSION));
                    if (in_array($ext, self::ALLOWED_EXTENSIONS)) {
                        $files[] = [
                            'nombre' => $utf8Name,
                            'url' => url('/programas/serve')
                                . '?ot=' . urlencode($ot)
                                . '&clase=' . urlencode($clase)
                                . '&proceso=' . urlencode($proceso)
                                . '&archivo=' . urlencode($utf8Name),
                            'extension' => strtoupper($ext),
                        ];
                    }
                }
            }

            return response()->json([
                'archivos' => $files,
                'ot' => $ot,
                'clase' => $clase,
                'proceso' => $proceso,
                'existe' => count($files) > 0,
            ]);
        } catch (\Throwable $e) {
            Log::error('Error en ProgramasCncController@getFiles: ' . $e->getMessage());
            return response()->json(['archivos' => [], 'existe' => false, 'error' => $e->getMessage()], 200);
        }
    }

    /**
     * Descarga directa de un archivo .NC/.CNC.
     * GET /programas/serve?ot=...&clase=...&proceso=...&archivo=...
     */
    public function serveFile(Request $request): BinaryFileResponse
    {
        $rawOt = $request->query('ot', '');
        $clase = $this->sanitizePath($request->query('clase', ''));
        $proceso = $this->sanitizePath($request->query('proceso', ''));
        $archivo = $this->sanitizeFileName($request->query('archivo', ''));

        $ot = $this->normalizeOTName($this->sanitizePath($rawOt));
        if (is_numeric($rawOt)) {
            try {
                $otModel = Orden_trabajo::query()->with('moldura')->find($rawOt);
                if ($otModel) {
                    $otLabel = 'OT ' . $otModel->id . ($otModel->moldura ? ' - ' . $otModel->moldura->nombre : '');
                    $ot = $this->normalizeOTName($this->sanitizePath($otLabel));
                }
            } catch (\Throwable $e) {
                Log::warning('Error DB en ProgramasCncController@serveFile: ' . $e->getMessage());
            }
        }

        if (empty($ot) || empty($archivo)) {
            abort(422, 'Parámetros inválidos.');
        }

        $parts = array_filter([$ot, $clase, $proceso]);
        $dirPath = self::BASE_DIR . '/' . implode('/', $parts);
        $resolvedDir = $this->resolveCaseInsensitivePath($dirPath);

        $foundFile = null;
        $archivoNorm = \Normalizer::normalize(mb_strtolower($archivo, 'UTF-8'), \Normalizer::FORM_C);

        if (Storage::disk('local')->exists($resolvedDir)) {
            $files = Storage::disk('local')->files($resolvedDir);
            foreach ($files as $f) {
                $rawName = basename($f);
                $utf8Name = $this->toUtf8($rawName);
                $utf8Norm = \Normalizer::normalize(mb_strtolower($utf8Name, 'UTF-8'), \Normalizer::FORM_C);
                if ($utf8Norm === $archivoNorm) {
                    $foundFile = $f;
                    break;
                }
            }
        }

        if (!$foundFile) {
            abort(404, 'Archivo no encontrado.');
        }

        /** @var \Illuminate\Filesystem\FilesystemAdapter $disk */
        $disk = Storage::disk('local');
        $fullPath = $disk->path($foundFile);

        // Forzar descarga en lugar de visualización inline
        return response()->file($fullPath, [
            'Content-Type' => 'application/octet-stream',
            'Content-Disposition' => 'attachment; filename="' . $archivo . '"',
        ]);
    }

    // =========================================================================
    // CRUD ADMINISTRADOR
    // =========================================================================

    /**
     * Crea la carpeta OT/Clase/Proceso en el sistema de archivos.
     * POST /programas/createFolder
     * Body: { ot_id, clase_id, proceso }
     */
    public function createFolder(Request $request)
    {
        try {
            $request->validate([
                'ot_id' => 'required|exists:orden_trabajo,id',
                'clase_id' => 'required|exists:clases,id',
                'proceso' => 'required|string|max:100',
            ]);

            $otId = $request->input('ot_id');
            $claseId = $request->input('clase_id');
            $proceso = $this->sanitizePath($request->input('proceso'));

            $otModel = Orden_trabajo::query()->with('moldura')->findOrFail($otId);
            $otFolderName = 'OT ' . $otModel->id . ($otModel->moldura ? ' - ' . $otModel->moldura->nombre : '');
            $otFolderName = $this->normalizeOTName($this->sanitizePath($otFolderName));

            $claseModel = Clase::query()->findOrFail($claseId);
            $claseFolder = $this->sanitizePath($claseModel->nombre);

            $dirPath = self::BASE_DIR . '/' . $otFolderName . '/' . $claseFolder . '/' . $proceso;

            if (Storage::disk('local')->exists($dirPath)) {
                return response()->json(['success' => false, 'message' => 'La carpeta ya existe.'], 409);
            }

            Storage::disk('local')->makeDirectory($dirPath);
            $this->logAction('crear_carpeta', $otFolderName . '/' . $claseFolder . '/' . $proceso, null);

            return response()->json([
                'success' => true,
                'message' => "Carpeta {$otFolderName}/{$claseFolder}/{$proceso} creada correctamente.",
                'ot' => $otFolderName,
                'clase' => $claseFolder,
                'proceso' => $proceso,
            ]);
        } catch (\Exception $e) {
            Log::error('Error en ProgramasCncController@createFolder: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Ocurrió un error al crear la carpeta.', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Sube un archivo .NC/.CNC a la carpeta OT/Clase/Proceso.
     * POST /programas/upload
     * Body (multipart): { ot_id, clase_id, proceso, programa (file) }
     */
    public function uploadProgram(Request $request)
    {
        $request->validate([
            'ot_id' => 'required|exists:orden_trabajo,id',
            'clase_id' => 'required|exists:clases,id',
            'proceso' => 'required|string|max:100',
            'programa' => 'required|file',
        ]);

        $file = $request->file('programa');
        $ext = strtolower($file->getClientOriginalExtension());

        if (!in_array($ext, self::ALLOWED_EXTENSIONS)) {
            return response()->json([
                'success' => false,
                'message' => 'Solo se permiten archivos con extensión .NC o .CNC.',
            ], 422);
        }

        $otModel = Orden_trabajo::query()->with('moldura')->findOrFail($request->input('ot_id'));
        $claseModel = Clase::query()->findOrFail($request->input('clase_id'));
        $proceso = $this->sanitizePath($request->input('proceso'));

        $otFolderName = 'OT ' . $otModel->id . ($otModel->moldura ? ' - ' . $otModel->moldura->nombre : '');
        $otFolderName = $this->normalizeOTName($this->sanitizePath($otFolderName));
        $claseFolder = $this->sanitizePath($claseModel->nombre);

        $dirPath = self::BASE_DIR . '/' . $otFolderName . '/' . $claseFolder . '/' . $proceso;

        if (!Storage::disk('local')->exists($dirPath)) {
            Storage::disk('local')->makeDirectory($dirPath);
        }

        $originalName = $this->sanitizeFileName($file->getClientOriginalName());

        if (Storage::disk('local')->exists($dirPath . '/' . $originalName)) {
            return response()->json([
                'success' => false,
                'message' => "Ya existe un archivo con el nombre '{$originalName}'. Use la función de Reemplazar.",
            ], 409);
        }

        $file->storeAs($dirPath, $originalName, 'local');
        $this->logAction('subir_programa', $otFolderName . '/' . $claseFolder . '/' . $proceso, $originalName);

        return response()->json([
            'success' => true,
            'message' => "Programa '{$originalName}' subido correctamente.",
            'nombre' => $originalName,
            'url' => url('/programas/serve')
                . '?ot=' . urlencode($otFolderName)
                . '&clase=' . urlencode($claseFolder)
                . '&proceso=' . urlencode($proceso)
                . '&archivo=' . urlencode($originalName),
        ]);
    }

    /**
     * Elimina un archivo .NC/.CNC.
     * POST /programas/delete
     * Body: { ot, clase, proceso, archivo }
     */
    public function deleteProgram(Request $request)
    {
        $request->validate([
            'ot' => 'required|string|max:200',
            'clase' => 'required|string|max:100',
            'proceso' => 'required|string|max:100',
            'archivo' => 'required|string|max:300',
        ]);

        $ot = $this->sanitizePath($request->input('ot'));
        $clase = $this->sanitizePath($request->input('clase'));
        $proceso = $this->sanitizePath($request->input('proceso'));
        $archivo = $this->sanitizeFileName($request->input('archivo'));

        $dirPath = self::BASE_DIR . '/' . $ot . '/' . $clase . '/' . $proceso;
        $files = Storage::disk('local')->exists($dirPath) ? Storage::disk('local')->files($dirPath) : [];

        $foundFile = null;
        $archivoNorm = \Normalizer::normalize(mb_strtolower($archivo, 'UTF-8'), \Normalizer::FORM_C);
        foreach ($files as $f) {
            $utf8Norm = \Normalizer::normalize(mb_strtolower($this->toUtf8(basename($f)), 'UTF-8'), \Normalizer::FORM_C);
            if ($utf8Norm === $archivoNorm) {
                $foundFile = $f;
                break;
            }
        }

        if (!$foundFile) {
            return response()->json(['success' => false, 'message' => 'El archivo no existe.'], 404);
        }

        Storage::disk('local')->delete($foundFile);
        $this->logAction('eliminar_programa', $ot . '/' . $clase . '/' . $proceso, $archivo);

        return response()->json(['success' => true, 'message' => "Archivo '{$archivo}' eliminado correctamente."]);
    }

    /**
     * Elimina la carpeta de un proceso (si está vacía o la vacía primero).
     * POST /programas/deleteFolder
     * Body: { ot, clase, proceso }
     */
    public function deleteFolder(Request $request)
    {
        $request->validate([
            'ot' => 'required|string|max:200',
            'clase' => 'required|string|max:100',
            'proceso' => 'required|string|max:100',
        ]);

        $ot = $this->sanitizePath($request->input('ot'));
        $clase = $this->sanitizePath($request->input('clase'));
        $proceso = $this->sanitizePath($request->input('proceso'));
        $dirPath = self::BASE_DIR . '/' . $ot . '/' . $clase . '/' . $proceso;

        if (!Storage::disk('local')->exists($dirPath)) {
            return response()->json(['success' => false, 'message' => 'La carpeta no existe.'], 404);
        }

        $files = Storage::disk('local')->files($dirPath);
        if (count($files) > 0) {
            Storage::disk('local')->delete($files);
        }
        Storage::disk('local')->deleteDirectory($dirPath);
        $this->logAction('eliminar_programa', $ot . '/' . $clase . '/' . $proceso, null);

        return response()->json([
            'success' => true,
            'message' => "Carpeta de proceso '{$proceso}' (OT: {$ot} / Clase: {$clase}) eliminada.",
        ]);
    }

    /**
     * Elimina la carpeta de una clase (si está vacía de subcarpetas de proceso).
     * POST /programas/deleteParent
     * Body: { ot, clase }
     */
    public function deleteParent(Request $request)
    {
        $request->validate([
            'ot' => 'required|string|max:200',
            'clase' => 'required|string|max:100',
        ]);

        $ot = $this->sanitizePath($request->input('ot'));
        $clase = $this->sanitizePath($request->input('clase'));
        $dirPath = self::BASE_DIR . '/' . $ot . '/' . $clase;

        if (!Storage::disk('local')->exists($dirPath)) {
            return response()->json(['success' => false, 'message' => 'La carpeta no existe.'], 404);
        }

        $subDirs = Storage::disk('local')->directories($dirPath);
        if (count($subDirs) > 0) {
            return response()->json(['success' => false, 'message' => 'No se puede eliminar: la clase todavía tiene subcarpetas de proceso.'], 400);
        }

        $files = Storage::disk('local')->files($dirPath);
        if (count($files) > 0) {
            return response()->json(['success' => false, 'message' => 'No se puede eliminar: la carpeta todavía contiene archivos.'], 400);
        }

        Storage::disk('local')->deleteDirectory($dirPath);
        $this->logAction('eliminar_programa', $ot . '/' . $clase, null);

        return response()->json(['success' => true, 'message' => "Carpeta de clase '{$clase}' (OT: {$ot}) eliminada correctamente."]);
    }

    // =========================================================================
    // HELPERS PRIVADOS
    // =========================================================================

    /**
     * Construye la estructura OT → Clase → [Proceso, ...] leyendo el filesystem.
     *
     * @return array<string, array<string, string[]>>
     */
    private function buildStructure(): array
    {
        $estructura = [];
        $basePath = Storage::disk('local')->path(self::BASE_DIR);

        if (!is_dir($basePath)) {
            return $estructura;
        }

        $otDirs = glob($basePath . '/*', GLOB_ONLYDIR) ?: [];
        foreach ($otDirs as $otDir) {
            $otName = $this->toUtf8($this->normalizeOTName(basename($otDir)));
            $clases = [];

            $claseDirs = glob($otDir . '/*', GLOB_ONLYDIR) ?: [];
            foreach ($claseDirs as $claseDir) {
                $claseName = $this->toUtf8(basename($claseDir));
                $procesos = [];

                $procesoDirs = glob($claseDir . '/*', GLOB_ONLYDIR) ?: [];
                foreach ($procesoDirs as $procesoDir) {
                    $procesos[] = $this->toUtf8(basename($procesoDir));
                }

                $clases[$claseName] = $procesos;
            }

            $estructura[$otName] = $clases;
        }

        ksort($estructura, SORT_NATURAL);
        return $estructura;
    }

    /**
     * Registra una acción en la tabla de auditoría y en system_logs.
     */
    private function logAction(string $action, string $ruta, ?string $archivo): void
    {
        $user = Auth::user();
        $userName = null;

        if ($user) {
            $userName = trim(
                ($user->matricula ?? '') . ' - ' .
                ($user->nombre ?? '') . ' ' .
                ($user->a_paterno ?? '') . ' ' .
                ($user->a_materno ?? '')
            );
        }

        ProgramaCncFileLog::create([
            'user_id' => $user?->id,
            'user_name' => $userName,
            'action' => $action,
            'ruta' => $ruta,
            'archivo' => $archivo,
        ]);

        $actionMap = [
            'subir_programa' => 'Subida de Programa CNC',
            'eliminar_programa' => 'Eliminación de Programa CNC',
            'reemplazar_programa' => 'Reemplazo de Programa CNC',
            'crear_carpeta' => 'Creación de Carpeta CNC',
        ];
        $systemAction = $actionMap[$action] ?? null;
        if ($systemAction && $user) {
            SystemLog::create([
                'user_matricula' => $user->matricula,
                'action' => $systemAction,
                'details' => "El administrador realizó la acción '{$action}' en {$ruta}" . ($archivo ? " (archivo: {$archivo})" : '') . '.',
            ]);
        }
    }

    private function sanitizePath(string $path): string
    {
        $path = preg_replace('/\.\.+/', '', $path);
        $path = preg_replace('/[\/\\\\]/', '', $path);
        return trim($path);
    }

    private function sanitizeFileName(string $name): string
    {
        $name = preg_replace('/\.\.+/', '', $name);
        $name = str_replace('\\', '/', $name);
        return trim($name) ?: 'programa.nc';
    }

    private function toUtf8(string $string): string
    {
        if (!mb_check_encoding($string, 'UTF-8')) {
            return mb_convert_encoding($string, 'UTF-8', 'Windows-1252');
        }
        return $string;
    }

    private function normalizeOTName(?string $name): string
    {
        if (!$name)
            return '';
        $name = str_replace(['—', '–', "\xc2\xa0"], '-', $name);
        $name = mb_strtoupper($name, 'UTF-8');
        $name = preg_replace('/\s+/', ' ', $name);
        return trim($name);
    }

    private function resolveCaseInsensitivePath(string $path): string
    {
        if (Storage::disk('local')->exists($path)) {
            return $path;
        }

        $parts = explode('/', str_replace('\\', '/', $path));
        $resolved = '';

        foreach ($parts as $part) {
            if ($part === '')
                continue;
            $candidate = $resolved === '' ? $part : $resolved . '/' . $part;
            if (Storage::disk('local')->exists($candidate)) {
                $resolved = $candidate;
                continue;
            }

            $sanitize = fn($str) => mb_strtolower(trim(preg_replace('/[\/\\\\]/', '', preg_replace('/\.\.+/', '', (string) $str))), 'UTF-8');
            $partClean = $sanitize($part);
            $partNoDigits = trim(preg_replace('/^\d+\s*-\s*/', '', $partClean));

            preg_match('/(\d+)/', $partClean, $partNumMatch);
            $partNum = $partNumMatch ? $partNumMatch[1] : null;

            $found = false;
            $parent = $resolved === '' ? '.' : $resolved;
            $allEntries = Storage::disk('local')->directories($parent);

            // 1. Coincidencia exacta insensible a mayúsculas/minúsculas
            foreach ($allEntries as $entry) {
                $bName = basename($entry);
                if ($sanitize($bName) === $partClean) {
                    $resolved = $entry;
                    $found = true;
                    break;
                }
            }

            // 2. Coincidencia por número de OT
            if (!$found && $partNum) {
                foreach ($allEntries as $entry) {
                    $bName = basename($entry);
                    preg_match('/(\d+)/', $bName, $entryNumMatch);
                    if ($entryNumMatch && $entryNumMatch[1] === $partNum) {
                        $resolved = $entry;
                        $found = true;
                        break;
                    }
                }
            }

            // 3. Coincidencia por Clase (ej. "Molde" vs "1 - MOLDES")
            if (!$found && !empty($partNoDigits)) {
                $normPart = Clase::normalizeClassName($part);

                foreach ($allEntries as $entry) {
                    $bName = basename($entry);
                    $bClean = $sanitize($bName);
                    $bNoDigits = trim(preg_replace('/^\d+\s*-\s*/', '', $bClean));
                    $normEntry = Clase::normalizeClassName($bName);

                    if (
                        ($normPart !== '' && $normPart === $normEntry) ||
                        $bNoDigits === $partNoDigits ||
                        str_contains($bNoDigits, $partNoDigits) ||
                        str_contains($partNoDigits, $bNoDigits) ||
                        rtrim($bNoDigits, 's') === rtrim($partNoDigits, 's')
                    ) {
                        $resolved = $entry;
                        $found = true;
                        break;
                    }
                }
            }

            if (!$found) {
                $resolved = $candidate;
            }
        }

        return $resolved;
    }
}