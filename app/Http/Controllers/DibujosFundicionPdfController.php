<?php

namespace App\Http\Controllers;

use App\Models\FundicionFileLog;
use App\Models\FundicionHistory;
use App\Models\Orden_trabajo;
use App\Mail\DibujoFundicionAlertMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Mail;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class DibujosFundicionPdfController extends Controller
{
    /**
     * Disco local de Laravel donde se almacenan los PDFs.
     * Carpeta base: storage/app/FUNDICION_GIS/
     */
    private const BASE_DIR = 'FUNDICION_GIS';

    // =========================================================================
    // VISTAS
    // =========================================================================

    public function showManage(Request $request)
    {
        // Estructura del filesystem
        $estructura = $this->buildStructure();

        // OTs activas (que tienen al menos una clase NO finalizada)
        $todasLasOTs = Orden_trabajo::with('moldura')
            ->whereHas('clases', fn($q) => $q->where('finalizada', 0))
            ->orderBy('id', 'asc')
            ->get();

        $otSeleccionadaId = $request->query('ot_id');
        $otActiva = $otSeleccionadaId ? $todasLasOTs->firstWhere('id', $otSeleccionadaId) : null;

        return view('wo_views.manage_documentation', array_merge(compact(
            'estructura',
            'todasLasOTs',
            'otSeleccionadaId',
            'otActiva'
        ), [
            'moduleType' => 'fundicion',
            'modulePrefix' => 'fundicion',
            'pageTitle' => 'Gestión de Dibujos de Fundición',
            'directoryName' => 'FUNDICION_GIS',
            'moduleMetadata' => [
                'description' => 'Selecciona la OT para buscar o subir dibujos de fundición.'
            ],
            // En caso que la vista lo espere. En Fundición no ocupamos clases:
            'claseSeleccionadaId' => null,
            'claseActiva' => null,
        ]));
    }

    public function getLog()
    {
        $logs = FundicionFileLog::query()
            ->orderByDesc('created_at')
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

    // =========================================================================
    // API LECTURA
    // =========================================================================

    public function getStructure()
    {
        $estructura = $this->buildStructure();
        return response()->json($estructura);
    }

    public function getFiles(Request $request)
    {
        $ot = $this->sanitizePath($request->query('ot', ''));

        if (empty($ot)) {
            return response()->json(['error' => 'Parámetro OT es requerido.'], 422);
        }

        $dirPath = self::BASE_DIR . '/' . $ot;

        if (!Storage::disk('local')->exists($dirPath)) {
            return response()->json([
                'archivos' => [],
                'ot' => $ot,
                'existe' => false,
            ]);
        }

        $files = Storage::disk('local')->files($dirPath);

        $archivos = collect($files)
            ->filter(fn($f) => strtolower(pathinfo($f, PATHINFO_EXTENSION)) === 'pdf')
            ->map(fn($f) => [
                'nombre' => basename($f),
                'url' => route('fundicion.serve', [
                    'ot' => $ot,
                    'archivo' => basename($f),
                ]),
            ])
            ->values();

        return response()->json([
            'archivos' => $archivos,
            'ot' => $ot,
            'existe' => true,
        ]);
    }

    public function serveFile(Request $request): BinaryFileResponse
    {
        $ot = $this->sanitizePath($request->query('ot', ''));
        $archivo = $this->sanitizeFileName($request->query('archivo', ''));

        if (empty($ot) || empty($archivo)) {
            abort(422, 'Parámetros inválidos.');
        }

        $filePath = self::BASE_DIR . '/' . $ot . '/' . $archivo;

        if (!Storage::disk('local')->exists($filePath)) {
            abort(404, 'Archivo no encontrado.');
        }

        /** @var \Illuminate\Filesystem\FilesystemAdapter $disk */
        $disk = Storage::disk('local');
        $fullPath = $disk->path($filePath);

        return response()->file($fullPath, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . $archivo . '"',
        ]);
    }

    // =========================================================================
    // CRUD ADMINISTRADOR
    // =========================================================================

    public function createFolder(Request $request)
    {
        $request->validate([
            'ot_id' => 'required|exists:orden_trabajo,id',
        ]);

        $otId = $request->input('ot_id');
        $otModel = Orden_trabajo::with('moldura')->findOrFail($otId);
        $otFolderName = "OT " . $otModel->id . ($otModel->moldura ? " - " . $otModel->moldura->nombre : "");
        $otFolderName = $this->sanitizePath($otFolderName);

        $dirPath = self::BASE_DIR . '/' . $otFolderName;

        if (Storage::disk('local')->exists($dirPath)) {
            return response()->json([
                'success' => false,
                'message' => 'La carpeta ya existe.',
            ], 409);
        }

        Storage::disk('local')->makeDirectory($dirPath);

        FundicionHistory::firstOrCreate(['ot' => $otFolderName]);
        $this->logAction('crear_carpeta', $otFolderName, "Creación de Carpeta");

        return response()->json([
            'success' => true,
            'message' => "Carpeta {$otFolderName} creada correctamente.",
            'ot' => $otFolderName,
        ]);
    }

    public function uploadPdf(Request $request)
    {
        $request->validate([
            'ot_id' => 'required|exists:orden_trabajo,id',
            'pdf' => 'required|file|mimes:pdf',
        ]);

        $otId = $request->input('ot_id');
        $otModel = Orden_trabajo::with('moldura')->findOrFail($otId);
        $otFolderName = "OT " . $otModel->id . ($otModel->moldura ? " - " . $otModel->moldura->nombre : "");
        $otFolderName = $this->sanitizePath($otFolderName);

        $dirPath = self::BASE_DIR . '/' . $otFolderName;

        if (!Storage::disk('local')->exists($dirPath)) {
            Storage::disk('local')->makeDirectory($dirPath);
            FundicionHistory::firstOrCreate(['ot' => $otFolderName]);
        }

        $file = $request->file('pdf');
        $originalName = $this->sanitizeFileName($file->getClientOriginalName());

        if (Storage::disk('local')->exists($dirPath . '/' . $originalName)) {
            return response()->json([
                'success' => false,
                'message' => "Ya existe un archivo con el nombre '{$originalName}'. Use la función de Reemplazar.",
            ], 409);
        }

        $file->storeAs($dirPath, $originalName, 'local');

        $this->logAction('subir_pdf', $otFolderName, $originalName);

        // Envío de correo automático
        $this->sendAlertInternal($otFolderName, $originalName);
        $this->logAction('enviar_alerta', $otFolderName, "Envío de archivo: " . $originalName);

        return response()->json([
            'success' => true,
            'message' => "PDF '{$originalName}' subido y se envió la alerta correctamente.",
            'nombre' => $originalName,
            'url' => route('fundicion.serve', [
                'ot' => $otFolderName,
                'archivo' => $originalName,
            ]),
        ]);
    }

    public function sendEmailAlert(Request $request)
    {
        $request->validate([
            'ot' => 'required|string|max:200',
            'archivo' => 'nullable|string|max:300',
        ]);

        $otFolderName = $this->sanitizePath($request->input('ot'));
        $originalName = $request->input('archivo') ? $this->sanitizeFileName($request->input('archivo')) : null;

        try {
            $this->sendAlertInternal($otFolderName, $originalName);
            $descLog = $originalName ? "Envío de archivo: {$originalName}" : "Múltiples archivos";
            $this->logAction('enviar_alerta', $otFolderName, $descLog);
            $msg = $originalName ? "Correo de alerta enviado para {$originalName}." : "Correo de alerta enviado para la OT {$otFolderName}.";
            return response()->json([
                'success' => true,
                'message' => $msg
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => "Error al enviar el correo: " . $e->getMessage()
            ], 500);
        }
    }

    private function sendAlertInternal($otName, $fileName)
    {
        // Se usa la configuración de servicios con el fallback de almacén
        $email = config('services.almacen.email', 'almacentec@grupoindsaavedra.com');
        Mail::to($email)->send(new DibujoFundicionAlertMail($otName, $fileName));
    }

    public function deletePdf(Request $request)
    {
        $request->validate([
            'ot' => 'required|string|max:200',
            'archivo' => 'required|string|max:300',
        ]);

        $ot = $this->sanitizePath($request->input('ot'));
        $archivo = $this->sanitizeFileName($request->input('archivo'));
        $filePath = self::BASE_DIR . '/' . $ot . '/' . $archivo;

        if (!Storage::disk('local')->exists($filePath)) {
            return response()->json([
                'success' => false,
                'message' => 'El archivo no existe.',
            ], 404);
        }

        Storage::disk('local')->delete($filePath);
        $this->logAction('eliminar_pdf', $ot, $archivo);

        return response()->json([
            'success' => true,
            'message' => "Archivo '{$archivo}' eliminado correctamente.",
        ]);
    }

    public function deleteFolder(Request $request)
    {
        $request->validate([
            'ot' => 'required|string|max:200',
        ]);

        $ot = $this->sanitizePath($request->input('ot'));
        $dirPath = self::BASE_DIR . '/' . $ot;

        if (!Storage::disk('local')->exists($dirPath)) {
            return response()->json([
                'success' => false,
                'message' => 'La carpeta no existe.',
            ], 404);
        }

        Storage::disk('local')->deleteDirectory($dirPath);
        $this->logAction('eliminar_pdf', $ot, "Eliminación de Carpeta");

        return response()->json([
            'success' => true,
            'message' => "Carpeta '{$ot}' eliminada correctamente.",
        ]);
    }

    public function replacePdf(Request $request)
    {
        $request->validate([
            'ot' => 'required|string|max:200',
            'archivo_anterior' => 'required|string|max:300',
            'pdf' => 'required|file|mimes:pdf',
        ]);

        $ot = $this->sanitizePath($request->input('ot'));
        $archivoAnterior = $this->sanitizeFileName($request->input('archivo_anterior'));
        $dirPath = self::BASE_DIR . '/' . $ot;
        $oldPath = $dirPath . '/' . $archivoAnterior;

        if (Storage::disk('local')->exists($oldPath)) {
            Storage::disk('local')->delete($oldPath);
        }

        $file = $request->file('pdf');
        $originalName = $this->sanitizeFileName($file->getClientOriginalName());
        $file->storeAs($dirPath, $originalName, 'local');

        $this->logAction('reemplazar_pdf', $ot, "{$archivoAnterior} → {$originalName}");

        return response()->json([
            'success' => true,
            'message' => "Archivo reemplazado: '{$archivoAnterior}' → '{$originalName}'.",
            'nombre' => $originalName,
            'url' => route('fundicion.serve', [
                'ot' => $ot,
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

        $otDirs = Storage::disk('local')->directories($baseDir);

        // A diferencia de dibujos, fundición no tiene clases (es 1 nivel).
        // Retornaremos un array de OTs para ser listadas.
        foreach ($otDirs as $otDir) {
            $estructura[] = basename($otDir);
        }

        sort($estructura, SORT_NATURAL);
        return $estructura;
    }

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

        FundicionFileLog::create([
            'user_id' => $user?->id,
            'user_name' => $userName,
            'action' => $action,
            'ruta' => $ruta,
            'archivo' => $archivo,
        ]);
    }

    private function sanitizePath(string $path): string
    {
        $path = preg_replace('/\.\.+/', '', $path);
        $path = preg_replace('/[\/\\\\]/', '', $path);
        $path = trim($path);
        return $path;
    }

    private function sanitizeFileName(string $name): string
    {
        $name = preg_replace('/[^a-zA-Z0-9_\-\.\s]/', '_', $name);
        $name = preg_replace('/\s+/', '_', $name);
        $name = trim($name, '_.');
        return $name ?: 'archivo.pdf';
    }
}
