<?php

namespace App\Http\Controllers;

use App\Models\AyudaVisualFileLog;
use App\Models\AyudaVisualHistory;
use App\Models\Procesos;
use App\Models\Clase;
use App\Models\Fecha_proceso;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class AyudasVisualesPdfController extends Controller
{
    /**
     * Disco local de Laravel donde se almacenan los PDFs.
     * Carpeta base: storage/app/AYUDAS_GIS/
     */
    private const BASE_DIR = 'AYUDAS_GIS';

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
        $clasesUnicas = Clase::query()->select('nombre')
            ->distinct()
            ->orderBy('nombre')
            ->get()
            ->map(function($clase) {
                return (object)[ 'id' => $clase->nombre, 'nombre' => $clase->nombre ];
            });

        $claseSeleccionadaId   = $request->query('clase_id');
        $procesoSeleccionadoId = $request->query('proceso_id');

        // 2. Catálogo de Procesos Estándar (Completo)
        $nombresProcesos = [
            'General', 'Cepillado', 'Desbaste Exterior', 'Revision Laterales', 'Primera Operacion',
            'Barreno Maniobra', 'Segunda Operacion', 'Soldadura', 'Soldadura PTA',
            'Rectificado', 'Asentado', 'Calificado', 'Acabado Bombillo', 'Acabado Molde',
            'Barreno Profundidad', 'Cavidades', 'Copiado', 'Off Set', 'Palomas',
            'Rebajes', 'Grabado', 'Operacion Equipo', 'Embudo CM',
            'Primera Operacion Cabeza Soplo', 'Segunda Operacion Cabeza Soplo'
        ];

        $todosLosProcesos = collect($nombresProcesos)->map(function($nombre) {
            return (object)[ 'id' => $nombre, 'nombre' => $nombre ];
        })->sortBy(function($p) {
            return $p->nombre === 'General' ? '' : $p->nombre; // General siempre primero
        })->values();

        $claseActiva = $claseSeleccionadaId ? $clasesUnicas->firstWhere('id', $claseSeleccionadaId) : null;
        $procesoActivo = $procesoSeleccionadoId ? $todosLosProcesos->firstWhere('id', $procesoSeleccionadoId) : null;

        return view('wo_views.manage_documentation', array_merge(compact(
            'estructura',
            'todosLosProcesos',
            'clasesUnicas',
            'procesoSeleccionadoId',
            'claseSeleccionadaId',
            'procesoActivo',
            'claseActiva'
        ), [
            'moduleType' => 'ayudas',
            'modulePrefix' => 'ayudas',
            'pageTitle' => 'Gestión de Ayudas Visuales',
            'directoryName' => 'AYUDAS_GIS',
            'moduleMetadata' => [
                'description' => 'Selecciona primero la clase y luego el proceso.'
            ]
        ]));
    }

    public function getLog()
    {
        $logs = AyudaVisualFileLog::query()
            ->orderByDesc('created_at')
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
        $proceso = $this->sanitizePath($request->query('proceso', ''));
        $clase   = $this->sanitizePath($request->query('clase', ''));

        if (empty($proceso) || empty($clase)) {
            return response()->json(['error' => 'Parámetros proceso y clase son requeridos.'], 422);
        }

        $dirPath = self::BASE_DIR . '/' . $proceso . '/' . $clase;

        if (!Storage::disk('local')->exists($dirPath)) {
            return response()->json([
                'archivos' => [],
                'proceso'  => $proceso,
                'clase'    => $clase,
                'existe'   => false,
            ]);
        }

        $files = Storage::disk('local')->files($dirPath);

        $archivos = collect($files)
            ->filter(fn($f) => strtolower(pathinfo($f, PATHINFO_EXTENSION)) === 'pdf')
            ->map(fn($f) => [
                'nombre' => basename($f),
                'url'    => route('ayudas.serve', [
                    'proceso' => $proceso,
                    'clase'   => $clase,
                    'archivo' => basename($f),
                ]),
            ])
            ->values();

        return response()->json([
            'archivos' => $archivos,
            'proceso'  => $proceso,
            'clase'    => $clase,
            'existe'   => true,
        ]);
    }

        /**
     * @param \Illuminate\Http\Request Request $request
     */
    public function serveFile(Request $request): BinaryFileResponse
    {
        $proceso = $this->sanitizePath($request->query('proceso', ''));
        $clase   = $this->sanitizePath($request->query('clase', ''));
        $archivo = $this->sanitizeFileName($request->query('archivo', ''));

        if (empty($proceso) || empty($clase) || empty($archivo)) {
            abort(422, 'Parámetros inválidos.');
        }

        $filePath = self::BASE_DIR . '/' . $proceso . '/' . $clase . '/' . $archivo;

        if (!Storage::disk('local')->exists($filePath)) {
            abort(404, 'Archivo no encontrado.');
        }

        /** @var \Illuminate\Filesystem\FilesystemAdapter $disk */
        $disk     = Storage::disk('local');
        $fullPath = $disk->path($filePath);

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
        $request->validate([
            'proceso' => 'required|string|max:100',
            'clase'   => 'required|string|max:100',
        ]);

        $proceso = $this->sanitizePath($request->input('proceso'));
        $clase   = $this->sanitizePath($request->input('clase'));
        $dirPath = self::BASE_DIR . '/' . $proceso . '/' . $clase;

        if (Storage::disk('local')->exists($dirPath)) {
            return response()->json([
                'success' => false,
                'message' => 'La carpeta ya existe.',
            ], 409);
        }

        Storage::disk('local')->makeDirectory($dirPath);

        AyudaVisualHistory::firstOrCreate(['proceso' => $proceso, 'clase' => $clase]);
        $this->logAction('crear_carpeta', $proceso . '/' . $clase, null);

        return response()->json([
            'success' => true,
            'message' => "Carpeta {$proceso}/{$clase} creada correctamente.",
            'proceso' => $proceso,
            'clase'   => $clase,
        ]);
    }

        /**
     * @param \Illuminate\Http\Request Request $request
     */
    public function uploadPdf(Request $request)
    {
        $request->validate([
            'proceso' => 'required|string|max:100',
            'clase'   => 'required|string|max:100',
            'pdf'     => 'required|file|mimes:pdf',
        ]);

        $proceso = $this->sanitizePath($request->input('proceso'));
        $clase   = $this->sanitizePath($request->input('clase'));
        $dirPath = self::BASE_DIR . '/' . $proceso . '/' . $clase;

        if (!Storage::disk('local')->exists($dirPath)) {
            Storage::disk('local')->makeDirectory($dirPath);
            AyudaVisualHistory::firstOrCreate(['proceso' => $proceso, 'clase' => $clase]);
        }

        $file         = $request->file('pdf');
        $originalName = $this->sanitizeFileName($file->getClientOriginalName());

        if (Storage::disk('local')->exists($dirPath . '/' . $originalName)) {
            return response()->json([
                'success' => false,
                'message' => "Ya existe un archivo con el nombre '{$originalName}'. Use la función de Reemplazar.",
            ], 409);
        }

        $file->storeAs($dirPath, $originalName, 'local');

        $this->logAction('subir_pdf', $proceso . '/' . $clase, $originalName);

        return response()->json([
            'success'  => true,
            'message'  => "PDF '{$originalName}' subido correctamente.",
            'nombre'   => $originalName,
            'url'      => route('ayudas.serve', [
                'proceso' => $proceso,
                'clase'   => $clase,
                'archivo' => $originalName,
            ]),
        ]);
    }

        /**
     * @param \Illuminate\Http\Request Request $request
     */
    public function deletePdf(Request $request)
    {
        $request->validate([
            'proceso' => 'required|string|max:100',
            'clase'   => 'required|string|max:100',
            'archivo' => 'required|string|max:300',
        ]);

        $proceso  = $this->sanitizePath($request->input('proceso'));
        $clase    = $this->sanitizePath($request->input('clase'));
        $archivo  = $this->sanitizeFileName($request->input('archivo'));
        $filePath = self::BASE_DIR . '/' . $proceso . '/' . $clase . '/' . $archivo;

        if (!Storage::disk('local')->exists($filePath)) {
            return response()->json([
                'success' => false,
                'message' => 'El archivo no existe.',
            ], 404);
        }

        Storage::disk('local')->delete($filePath);
        $this->logAction('eliminar_pdf', $proceso . '/' . $clase, $archivo);

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
            'proceso' => 'required|string|max:100',
            'clase'   => 'required|string|max:100',
        ]);

        $proceso = $this->sanitizePath($request->input('proceso'));
        $clase   = $this->sanitizePath($request->input('clase'));
        $dirPath = self::BASE_DIR . '/' . $proceso . '/' . $clase;

        if (!Storage::disk('local')->exists($dirPath)) {
            return response()->json([
                'success' => false,
                'message' => 'La carpeta no existe.',
            ], 404);
        }

        Storage::disk('local')->deleteDirectory($dirPath);
        $this->logAction('eliminar_pdf', $proceso . '/' . $clase, null);

        return response()->json([
            'success' => true,
            'message' => "Carpeta '{$proceso}' (Clase: {$clase}) eliminada correctamente.",
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

        $proceso = $this->sanitizePath($request->input('proceso'));
        $dirPath = self::BASE_DIR . '/' . $proceso;

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
        $this->logAction('eliminar_pdf', $proceso, null);

        return response()->json([
            'success' => true,
            'message' => "Carpeta principal del proceso '{$proceso}' eliminada correctamente.",
        ]);
    }

        /**
     * @param \Illuminate\Http\Request Request $request
     */
    public function replacePdf(Request $request)
    {
        $request->validate([
            'proceso'         => 'required|string|max:100',
            'clase'           => 'required|string|max:100',
            'archivo_anterior'=> 'required|string|max:300',
            'pdf'             => 'required|file|mimes:pdf',
        ]);

        $proceso         = $this->sanitizePath($request->input('proceso'));
        $clase           = $this->sanitizePath($request->input('clase'));
        $archivoAnterior = $this->sanitizeFileName($request->input('archivo_anterior'));
        $dirPath         = self::BASE_DIR . '/' . $proceso . '/' . $clase;
        $oldPath         = $dirPath . '/' . $archivoAnterior;

        if (Storage::disk('local')->exists($oldPath)) {
            Storage::disk('local')->delete($oldPath);
        }

        $file         = $request->file('pdf');
        $originalName = $this->sanitizeFileName($file->getClientOriginalName());
        $file->storeAs($dirPath, $originalName, 'local');

        $this->logAction('reemplazar_pdf', $proceso . '/' . $clase, "{$archivoAnterior} → {$originalName}");

        return response()->json([
            'success'  => true,
            'message'  => "Archivo reemplazado: '{$archivoAnterior}' → '{$originalName}'.",
            'nombre'   => $originalName,
            'url'      => route('ayudas.serve', [
                'proceso' => $proceso,
                'clase'   => $clase,
                'archivo' => $originalName,
            ]),
        ]);
    }

    // =========================================================================
    // HELPERS PRIVADOS
    // =========================================================================

    private function buildStructure(): array
    {
        $baseDir = self::BASE_DIR;
        $estructura = [];

        if (!Storage::disk('local')->exists($baseDir)) {
            return $estructura;
        }

        $procesoDirs = Storage::disk('local')->directories($baseDir);

        foreach ($procesoDirs as $procesoDir) {
            $procesoName = basename($procesoDir);
            $claseDirs   = Storage::disk('local')->directories($procesoDir);
            
            if (empty($claseDirs)) {
                // Si el proceso no tiene carpetas de clase, lo marcamos como huérfano
                $estructura['-- SIN CLASE --'][] = $procesoName;
            } else {
                foreach ($claseDirs as $claseDir) {
                    $claseName = basename($claseDir);
                    $estructura[$claseName][] = $procesoName;
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

        AyudaVisualFileLog::create([
            'user_id'   => $user?->id,
            'user_name' => $userName,
            'action'    => $action,
            'ruta'      => $ruta,
            'archivo'   => $archivo,
        ]);
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
        $name = preg_replace('/[^a-zA-Z0-9_\-\.\s]/', '_', $name);
        $name = preg_replace('/\s+/', '_', $name);
        $name = trim($name, '_.');
        return $name ?: 'archivo.pdf';
    }
}
