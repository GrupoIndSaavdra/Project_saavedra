<?php

namespace App\Http\Controllers;

use App\Models\DibujoFileLog;
use App\Models\DibujoOtHistory;
use App\Models\Orden_trabajo;
use App\Models\Clase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class DibujosPdfController extends Controller
{
    /**
     * Disco local de Laravel donde se almacenan los PDFs.
     * Carpeta base: storage/app/DIBUJOS_GIS/
     */
    private const BASE_DIR = 'DIBUJOS_GIS';

    // =========================================================================
    // VISTAS
    // =========================================================================

    /**
     * Vista de administración del módulo de dibujos.
     * Solo accesible para administradores (perfil 1).
     */
    public function showManage(Request $request)
    {
        // ── Estructura del filesystem (para la tabla de carpetas existentes) ──
        $estructura = $this->buildStructure();

        // Cargamos SOLO las OTs que tienen al menos una Clase NO finalizada (activas)
        $todasLasOTs = Orden_trabajo::with([
            'moldura',
            'clases' => fn($q) => $q->where('finalizada', 0)->orderBy('nombre'),
        ])
        ->whereHas('clases', fn($q) => $q->where('finalizada', 0))
        ->orderBy('id', 'asc')
        ->get();

        // ── Selección activa (via query string: ?ot_id=X&clase_id=Y) ──
        $otSeleccionadaId    = $request->query('ot_id');
        $claseSeleccionadaId = $request->query('clase_id');

        // Si hay OT seleccionada, obtener la OT y Clase activa
        $otActiva    = $otSeleccionadaId    ? $todasLasOTs->firstWhere('id', $otSeleccionadaId)    : null;
        $claseActiva = $claseSeleccionadaId ? optional($otActiva?->clases)->firstWhere('id', $claseSeleccionadaId) : null;

        return view('wo_views.manage_dibujos', compact(
            'estructura',
            'todasLasOTs',
            'otSeleccionadaId',
            'claseSeleccionadaId',
            'otActiva',
            'claseActiva'
        ));
    }

    /**
     * Devuelve las últimas 50 entradas del log de auditoría como JSON.
     *
     * GET /dibujos/log
     */
    public function getLog()
    {
        $logs = DibujoFileLog::query()
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
    // API DE LECTURA (para operadores y administradores)
    // =========================================================================

    /**
     * Devuelve la estructura completa de carpetas OT → [Clase, ...] leyendo
     * directamente el sistema de archivos. La BD NO es la fuente de verdad.
     *
     * GET /dibujos/estructura
     * Response: { "OT001": ["ClaseA", "ClaseB"], "OT002": ["ClaseC"] }
     */
    public function getStructure()
    {
        $estructura = $this->buildStructure();
        return response()->json($estructura);
    }

    /**
     * Devuelve los archivos PDF dentro de una carpeta OT/Clase específica.
     *
     * GET /dibujos/archivos?ot=OT001&clase=ClaseA
     * Response: { "archivos": [{ "nombre": "plano.pdf", "url": "..." }], "ot": "OT001", "clase": "ClaseA" }
     */
    public function getFiles(Request $request)
    {
        $ot    = $this->sanitizePath($request->query('ot', ''));
        $clase = $this->sanitizePath($request->query('clase', ''));

        if (empty($ot) || empty($clase)) {
            return response()->json(['error' => 'Parámetros OT y Clase son requeridos.'], 422);
        }

        $dirPath = self::BASE_DIR . '/' . $ot . '/' . $clase;

        if (!Storage::disk('local')->exists($dirPath)) {
            return response()->json([
                'archivos' => [],
                'ot'       => $ot,
                'clase'    => $clase,
                'existe'   => false,
            ]);
        }

        $files = Storage::disk('local')->files($dirPath);

        $archivos = collect($files)
            ->filter(fn($f) => strtolower(pathinfo($f, PATHINFO_EXTENSION)) === 'pdf')
            ->map(fn($f) => [
                'nombre' => basename($f),
                'url'    => route('dibujos.serve', [
                    'ot'     => $ot,
                    'clase'  => $clase,
                    'archivo'=> basename($f),
                ]),
            ])
            ->values();

        return response()->json([
            'archivos' => $archivos,
            'ot'       => $ot,
            'clase'    => $clase,
            'existe'   => true,
        ]);
    }

    /**
     * Sirve el contenido binario de un PDF al navegador (nueva pestaña).
     * El navegador mostrará el PDF inline sin descargarlo.
     *
     * GET /dibujos/serve?ot=OT001&clase=ClaseA&archivo=plano.pdf
     */
    public function serveFile(Request $request): BinaryFileResponse
    {
        $ot      = $this->sanitizePath($request->query('ot', ''));
        $clase   = $this->sanitizePath($request->query('clase', ''));
        $archivo = $this->sanitizeFileName($request->query('archivo', ''));

        if (empty($ot) || empty($clase) || empty($archivo)) {
            abort(422, 'Parámetros inválidos.');
        }

        $filePath = self::BASE_DIR . '/' . $ot . '/' . $clase . '/' . $archivo;

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
     * Crea la carpeta OT/Clase en el sistema de archivos.
     * Si ya existe, retorna un mensaje informativo (no error).
     *
     * POST /dibujos/createFolder
     * Body: { ot, clase }
     */
    public function createFolder(Request $request)
    {
        $request->validate([
            'ot'   => 'required|string|max:100',
            'clase'=> 'required|string|max:100',
        ]);

        $ot    = $this->sanitizePath($request->input('ot'));
        $clase = $this->sanitizePath($request->input('clase'));
        $dirPath = self::BASE_DIR . '/' . $ot . '/' . $clase;

        if (Storage::disk('local')->exists($dirPath)) {
            return response()->json([
                'success' => false,
                'message' => 'La carpeta ya existe.',
            ], 409);
        }

        // Crear carpeta (Storage requiere crear un archivo .gitkeep para que la carpeta exista)
        Storage::disk('local')->makeDirectory($dirPath);

        // Registrar en historial y log
        DibujoOtHistory::firstOrCreate(['ot' => $ot, 'clase' => $clase]);
        $this->logAction('crear_carpeta', $ot . '/' . $clase, null);

        return response()->json([
            'success' => true,
            'message' => "Carpeta {$ot}/{$clase} creada correctamente.",
            'ot'      => $ot,
            'clase'   => $clase,
        ]);
    }

    /**
     * Sube un PDF a la carpeta OT/Clase. Si la carpeta no existe, la crea automáticamente.
     *
     * POST /dibujos/upload
     * Body (multipart): { ot, clase, pdf (file) }
     */
    public function uploadPdf(Request $request)
    {
        $request->validate([
            'ot'   => 'required|string|max:100',
            'clase'=> 'required|string|max:100',
            'pdf'  => 'required|file|mimes:pdf',
        ]);

        $ot      = $this->sanitizePath($request->input('ot'));
        $clase   = $this->sanitizePath($request->input('clase'));
        $dirPath = self::BASE_DIR . '/' . $ot . '/' . $clase;

        // Asegurar que la carpeta existe
        if (!Storage::disk('local')->exists($dirPath)) {
            Storage::disk('local')->makeDirectory($dirPath);
            DibujoOtHistory::firstOrCreate(['ot' => $ot, 'clase' => $clase]);
        }

        $file         = $request->file('pdf');
        $originalName = $this->sanitizeFileName($file->getClientOriginalName());

        // Verificar si ya existe (no reemplazar — usar replacePdf para eso)
        if (Storage::disk('local')->exists($dirPath . '/' . $originalName)) {
            return response()->json([
                'success' => false,
                'message' => "Ya existe un archivo con el nombre '{$originalName}'. Use la función de Reemplazar.",
            ], 409);
        }

        $file->storeAs($dirPath, $originalName, 'local');

        $this->logAction('subir_pdf', $ot . '/' . $clase, $originalName);

        return response()->json([
            'success'  => true,
            'message'  => "PDF '{$originalName}' subido correctamente.",
            'nombre'   => $originalName,
            'url'      => route('dibujos.serve', [
                'ot'     => $ot,
                'clase'  => $clase,
                'archivo'=> $originalName,
            ]),
        ]);
    }

    /**
     * Elimina un PDF del sistema de archivos.
     *
     * POST /dibujos/delete
     * Body: { ot, clase, archivo }
     */
    public function deletePdf(Request $request)
    {
        $request->validate([
            'ot'     => 'required|string|max:100',
            'clase'  => 'required|string|max:100',
            'archivo'=> 'required|string|max:300',
        ]);

        $ot      = $this->sanitizePath($request->input('ot'));
        $clase   = $this->sanitizePath($request->input('clase'));
        $archivo = $this->sanitizeFileName($request->input('archivo'));
        $filePath = self::BASE_DIR . '/' . $ot . '/' . $clase . '/' . $archivo;

        if (!Storage::disk('local')->exists($filePath)) {
            return response()->json([
                'success' => false,
                'message' => 'El archivo no existe.',
            ], 404);
        }

        Storage::disk('local')->delete($filePath);
        $this->logAction('eliminar_pdf', $ot . '/' . $clase, $archivo);

        return response()->json([
            'success' => true,
            'message' => "Archivo '{$archivo}' eliminado correctamente.",
        ]);
    }

    /**
     * Reemplaza un PDF existente por el nuevo (mismo nombre o nuevo nombre).
     *
     * POST /dibujos/replace
     * Body (multipart): { ot, clase, archivo_anterior, pdf (file) }
     */
    public function replacePdf(Request $request)
    {
        $request->validate([
            'ot'              => 'required|string|max:100',
            'clase'           => 'required|string|max:100',
            'archivo_anterior'=> 'required|string|max:300',
            'pdf'             => 'required|file|mimes:pdf',
        ]);

        $ot              = $this->sanitizePath($request->input('ot'));
        $clase           = $this->sanitizePath($request->input('clase'));
        $archivoAnterior = $this->sanitizeFileName($request->input('archivo_anterior'));
        $dirPath         = self::BASE_DIR . '/' . $ot . '/' . $clase;
        $oldPath         = $dirPath . '/' . $archivoAnterior;

        // Eliminar el archivo anterior si existe
        if (Storage::disk('local')->exists($oldPath)) {
            Storage::disk('local')->delete($oldPath);
        }

        $file         = $request->file('pdf');
        $originalName = $this->sanitizeFileName($file->getClientOriginalName());
        $file->storeAs($dirPath, $originalName, 'local');

        $this->logAction('reemplazar_pdf', $ot . '/' . $clase, "{$archivoAnterior} → {$originalName}");

        return response()->json([
            'success'  => true,
            'message'  => "Archivo reemplazado: '{$archivoAnterior}' → '{$originalName}'.",
            'nombre'   => $originalName,
            'url'      => route('dibujos.serve', [
                'ot'     => $ot,
                'clase'  => $clase,
                'archivo'=> $originalName,
            ]),
        ]);
    }

    // =========================================================================
    // HELPERS PRIVADOS
    // =========================================================================

    /**
     * Construye la estructura completa de OTs y Clases leyendo el filesystem.
     * Retorna: [ "OT001" => ["ClaseA", "ClaseB"], ... ]
     */
    private function buildStructure(): array
    {
        $baseDir = self::BASE_DIR;
        $estructura = [];

        // Si la carpeta base no existe aún, retornar vacío
        if (!Storage::disk('local')->exists($baseDir)) {
            return $estructura;
        }

        $otDirs = Storage::disk('local')->directories($baseDir);

        foreach ($otDirs as $otDir) {
            $otName   = basename($otDir);
            $claseDirs = Storage::disk('local')->directories($otDir);
            $clases   = array_map('basename', $claseDirs);
            $estructura[$otName] = $clases;
        }

        ksort($estructura, SORT_NATURAL);
        return $estructura;
    }

    /**
     * Registra una acción en la tabla de auditoría dibujos_file_log.
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

        DibujoFileLog::create([
            'user_id'   => $user?->id,
            'user_name' => $userName,
            'action'    => $action,
            'ruta'      => $ruta,
            'archivo'   => $archivo,
        ]);
    }

    /**
     * Sanitiza un segmento de ruta para evitar path traversal.
     * Elimina '..' y caracteres peligrosos.
     */
    private function sanitizePath(string $path): string
    {
        // Eliminar cualquier intento de traversal
        $path = preg_replace('/\.\.+/', '', $path);
        $path = preg_replace('/[\/\\\\]/', '', $path);
        $path = trim($path);
        return $path;
    }

    /**
     * Sanitiza el nombre de un archivo PDF.
     */
    private function sanitizeFileName(string $name): string
    {
        // Solo permitir caracteres seguros en nombres de archivo
        $name = preg_replace('/[^a-zA-Z0-9_\-\.\s]/', '_', $name);
        $name = preg_replace('/\s+/', '_', $name);
        $name = trim($name, '_.');
        return $name ?: 'archivo.pdf';
    }
}
