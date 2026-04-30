<?php

namespace App\Http\Controllers;

use App\Models\FundicionFileLog;
use App\Models\FundicionHistory;
use App\Models\Orden_trabajo;
use App\Models\Clase;

use App\Mail\DibujoFundicionAlertMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Mail;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class DibujosFundicionPdfController extends Controller
{
    private const BASE_DIR = 'DOCUMENTACION_GIS/DIBUJOS_FUNDICION';

    /**
     * Directorio legado para compatibilidad con archivos anteriores.
     */
    private const OLD_BASE_DIR = 'FUNDICION_GIS';

    /**
     * NUNCA se escanea desde la vista admin y NUNCA se borra con deleteFolder.
     */
    private const ALMACEN_DIR = 'DOCUMENTACION_GIS/ALMACEN_FUNDICION';

    // =========================================================================
    // VISTAS
    // =========================================================================

        /**
     * @param \Illuminate\Http\Request Request $request
     */
    public function showManage(Request $request)
    {
        // Estructura del filesystem
        $estructura = $this->buildStructure();

        // OTs activas (que tienen al menos una clase NO finalizada)
        $todasLasOTs = Orden_trabajo::query()->with('moldura')
            ->whereHas('clases', fn($q) => $q->where('finalizada', 0))
            ->orderBy('id', 'asc')
            ->get();

        $otSeleccionadaId = $request->query('ot_id');
        $otActiva = $otSeleccionadaId ? $todasLasOTs->firstWhere('id', $otSeleccionadaId) : null;

        // --- Lógica de Ayudas Visuales ---
        $clasesBD = Clase::query()->pluck('nombre')->unique()->toArray();
        $clasesCompletas = array_unique(array_merge($clasesBD, ['Pistones', 'Guías']));
        sort($clasesCompletas);

        $ayudasDisponibles = [];
        
        // Buscamos procesos en AMBOS directorios
        $newBase = 'DOCUMENTACION_GIS/AYUDAS_FUNDICION';
        $oldBase = 'AYUDAS_GIS';
        
        $newDirs = Storage::disk('local')->exists($newBase) ? Storage::disk('local')->directories($newBase) : [];
        $oldDirs = Storage::disk('local')->exists($oldBase) ? Storage::disk('local')->directories($oldBase) : [];
        $ayudasBaseDirs = array_unique(array_merge($newDirs, $oldDirs));
        
        foreach ($clasesCompletas as $clase) {
            foreach ($ayudasBaseDirs as $procesoDir) {
                $claseDir = $procesoDir . '/' . $clase;
                if (Storage::disk('local')->exists($claseDir)) {
                    $files = Storage::disk('local')->files($claseDir);
                    $hasPdf = collect($files)->contains(fn($f) => strtolower(pathinfo($f, PATHINFO_EXTENSION)) === 'pdf');
                    if ($hasPdf) {
                        $ayudasDisponibles[] = $clase;
                        break; 
                    }
                }
            }
        }
        $ayudasDisponibles = array_unique($ayudasDisponibles);
        sort($ayudasDisponibles);

        $ayudasSeleccionadas = [];
        $hasDibujos = false;
        $ayudasConEstado = []; // Nuevo array con info de sincronización

        if ($otActiva) {
            $otFolderName = "OT " . $otActiva->id . ($otActiva->moldura ? " - " . $otActiva->moldura->nombre : "");
            $otFolderName = trim(preg_replace('/[\/\\\\]/', '', preg_replace('/\.\.+/', '', $otFolderName)));
            
            $history = FundicionHistory::query()->where('ot', $otFolderName)->first();
            $ayudasSeleccionadas = $history ? ($history->ayudas_config ?? []) : [];

            // 1. Verificar dibujos principales
            $newPath = self::BASE_DIR . '/' . $otFolderName;
            $oldPath = self::OLD_BASE_DIR . '/' . $otFolderName;
            $newFiles = Storage::disk('local')->exists($newPath) ? Storage::disk('local')->files($newPath) : [];
            $oldFiles = Storage::disk('local')->exists($oldPath) ? Storage::disk('local')->files($oldPath) : [];
            $hasDibujos = collect(array_merge($newFiles, $oldFiles))->contains(fn($f) => strtolower(pathinfo($f, PATHINFO_EXTENSION)) === 'pdf');

            // 2. Comparar Ayudas Visuales (Maestras vs Almacén)
            $almacenAyudasBase = self::ALMACEN_DIR . '/' . $otFolderName . '/ayudas_visuales';

            foreach ($ayudasDisponibles as $clase) {
                $isNew = true;
                
                // Buscar archivos maestros de esta clase
                $masterFiles = [];
                foreach ($ayudasBaseDirs as $procesoDir) {
                    $claseDir = $procesoDir . '/' . $clase;
                    if (Storage::disk('local')->exists($claseDir)) {
                        $fList = Storage::disk('local')->files($claseDir);
                        foreach($fList as $f) {
                            if(strtolower(pathinfo($f, PATHINFO_EXTENSION)) === 'pdf') $masterFiles[] = basename($f);
                        }
                    }
                }
                
                // Buscar archivos ya existentes en Almacén para esta OT
                $almacenClaseDir = $almacenAyudasBase . '/' . $clase;
                $almacenFiles = [];
                if (Storage::disk('local')->exists($almacenClaseDir)) {
                    $fList = Storage::disk('local')->files($almacenClaseDir);
                    foreach($fList as $f) {
                        if(strtolower(pathinfo($f, PATHINFO_EXTENSION)) === 'pdf') $almacenFiles[] = basename($f);
                    }
                }

                // Comparación: Si el contenido es idéntico (mismos nombres), no es "nueva"
                if (!empty($masterFiles) && !empty($almacenFiles)) {
                    sort($masterFiles);
                    sort($almacenFiles);
                    if ($masterFiles === $almacenFiles) {
                        $isNew = false;
                    }
                }

                $ayudasConEstado[] = [
                    'nombre' => $clase,
                    'is_new' => $isNew,
                    'is_selected' => in_array($clase, $ayudasSeleccionadas)
                ];
            }
        }
        // ---------------------------------

        return view('wo_views.manage_documentation', array_merge(compact(
            'estructura',
            'todasLasOTs',
            'otSeleccionadaId',
            'otActiva',
            'ayudasConEstado',
            'hasDibujos'
        ), [
            'moduleType' => 'fundicion',
            'modulePrefix' => 'fundicion',
            'pageTitle' => 'Gestión de Dibujos de Fundición',
            'directoryName' => 'DOCUMENTACION_GIS / DIBUJOS_FUNDICION',
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

        /**
     * @param \Illuminate\Http\Request Request $request
     */
    public function getFiles(Request $request)
    {
        $ot = $this->sanitizePath($request->query('ot', ''));

        if (empty($ot)) {
            return response()->json(['error' => 'Parámetro OT es requerido.'], 422);
        }

        $newDirPath = self::BASE_DIR . '/' . $ot;
        $oldDirPath = self::OLD_BASE_DIR . '/' . $ot;

        $newFiles = Storage::disk('local')->exists($newDirPath) ? Storage::disk('local')->files($newDirPath) : [];
        $oldFiles = Storage::disk('local')->exists($oldDirPath) ? Storage::disk('local')->files($oldDirPath) : [];

        $allFiles = collect(array_merge($newFiles, $oldFiles))
            ->filter(fn($f) => strtolower(pathinfo($f, PATHINFO_EXTENSION)) === 'pdf')
            ->map(function($f) use ($ot) {
                return [
                    'nombre' => basename($f),
                    'url'    => route('fundicion.serve', [
                        'ot'      => $ot,
                        'archivo' => basename($f),
                    ]),
                ];
            })
            ->unique('nombre')
            ->values();

        return response()->json([
            'archivos' => $allFiles,
            'ot'       => $ot,
            'existe'   => (count($allFiles) > 0),
        ]);
    }

        /**
     * @param \Illuminate\Http\Request Request $request
     */
    public function serveFile(Request $request): BinaryFileResponse
    {
        $ot = $this->sanitizePath($request->query('ot', ''));
        $archivo = $this->sanitizeFileName($request->query('archivo', ''));

        if (empty($ot) || empty($archivo)) {
            abort(422, 'Parámetros inválidos.');
        }

        $filePath = self::BASE_DIR . '/' . $ot . '/' . $archivo;

        // Fallback al viejo
        if (!Storage::disk('local')->exists($filePath)) {
            $filePath = self::OLD_BASE_DIR . '/' . $ot . '/' . $archivo;
        }

        if (!Storage::disk('local')->exists($filePath)) {
            abort(404, 'Archivo no encontrado.');
        }

        /** @var \Illuminate\Filesystem\FilesystemAdapter $disk */
        $disk = Storage::disk('local');
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
            'ot_id' => 'required|exists:orden_trabajo,id',
        ]);

        $otId = $request->input('ot_id');
        $otModel = Orden_trabajo::query()->with('moldura')->findOrFail($otId);
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

        /**
     * @param \Illuminate\Http\Request Request $request
     */
    public function uploadPdf(Request $request)
    {
        $request->validate([
            'ot_id' => 'required|exists:orden_trabajo,id',
            'pdf' => 'required|file|mimes:pdf',
        ]);

        $otId = $request->input('ot_id');
        $otModel = Orden_trabajo::query()->with('moldura')->findOrFail($otId);
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
        // $this->sendAlertInternal($otFolderName, $originalName);
        // $this->logAction('enviar_alerta', $otFolderName, "Envío de archivo: " . $originalName);

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

        /**
     * @param \Illuminate\Http\Request Request $request
     */
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

        /**
     * @param mixed $otName
     * @param mixed $fileName
     */
    private function sendAlertInternal($otName, $fileName): void
    {
        // ─── 1. Copiar y Sincronizar ─────────────────────────────────────────────
        $this->copyToAlmacen($otName);

        // ─── 3. Enviar correo (incluyendo info de ayudas visuales) ───────────────
        $history = FundicionHistory::query()->where('ot', $otName)->first();
        $ayudas  = $history ? ($history->ayudas_config ?? []) : [];

        $emailsStr = config('services.almacen.email', 'almacentec@grupoindsaavedra.com');
        $emails    = array_filter(array_map('trim', explode(',', $emailsStr)));

        Mail::to($emails)->send(new DibujoFundicionAlertMail($otName, $fileName, $ayudas));
    }

    /**
     * Copia los archivos del directorio de trabajo al directorio protegido de Almacén.
     * También sincroniza las ayudas visuales vinculadas.
     * 
     * @param string $otName
     */
    private function copyToAlmacen(string $otName): void
    {
        $srcDir  = self::BASE_DIR    . '/' . $otName;
        $dstDir  = self::ALMACEN_DIR . '/' . $otName;

        // 1. Sincronizar dibujos principales (Mirroring)
        if (Storage::disk('local')->exists($srcDir)) {
            if (!Storage::disk('local')->exists($dstDir)) {
                Storage::disk('local')->makeDirectory($dstDir);
            }

            $srcFiles = collect(Storage::disk('local')->files($srcDir))
                ->filter(fn($f) => strtolower(pathinfo($f, PATHINFO_EXTENSION)) === 'pdf')
                ->map(fn($f) => basename($f))
                ->toArray();
            
            $dstFiles = collect(Storage::disk('local')->files($dstDir))
                ->filter(fn($f) => strtolower(pathinfo($f, PATHINFO_EXTENSION)) === 'pdf')
                ->map(fn($f) => basename($f))
                ->toArray();

            // Eliminar los que ya no están en origen (Huérfanos)
            foreach ($dstFiles as $df) {
                if (!in_array($df, $srcFiles)) {
                    Storage::disk('local')->delete($dstDir . '/' . $df);
                }
            }

            // Copiar nuevos o actualizados
            foreach ($srcFiles as $sf) {
                $srcPath = $srcDir . '/' . $sf;
                $dstPath = $dstDir . '/' . $sf;
                if (!Storage::disk('local')->exists($dstPath)) {
                    Storage::disk('local')->copy($srcPath, $dstPath);
                }
            }
        }

        // 2. Copiar y Sincronizar Ayudas Visuales (Mirroring)
        $history = FundicionHistory::query()->where('ot', $otName)->first();
        $ayudasVinculadas = $history ? ($history->ayudas_config ?? []) : [];
        $ayudasDstDir = $dstDir . '/ayudas_visuales';
        
        // Sincronizar carpetas de clases en Almacén
        if (Storage::disk('local')->exists($ayudasDstDir)) {
            $clasesEnAlmacen = array_map('basename', Storage::disk('local')->directories($ayudasDstDir));
            foreach ($clasesEnAlmacen as $claseAlmacen) {
                if (!in_array($claseAlmacen, $ayudasVinculadas)) {
                    Storage::disk('local')->deleteDirectory($ayudasDstDir . '/' . $claseAlmacen);
                }
            }
        }

        if (!empty($ayudasVinculadas)) {
            if (!Storage::disk('local')->exists($ayudasDstDir)) {
                Storage::disk('local')->makeDirectory($ayudasDstDir);
            }
            
            $newBase = 'DOCUMENTACION_GIS/AYUDAS_FUNDICION';
            $oldBase = 'AYUDAS_GIS';
            
            foreach ($ayudasVinculadas as $clase) {
                $claseDstDir = $ayudasDstDir . '/' . $clase;
                if (!Storage::disk('local')->exists($claseDstDir)) {
                    Storage::disk('local')->makeDirectory($claseDstDir);
                }

                // Obtener archivos maestros actuales para esta clase
                $masterFiles = [];
                $bases = [$newBase . '/Fundicion', $oldBase . '/Fundicion'];
                foreach ($bases as $base) {
                    $srcClaseDir = $base . '/' . $clase;
                    if (Storage::disk('local')->exists($srcClaseDir)) {
                        $fList = Storage::disk('local')->files($srcClaseDir);
                        foreach($fList as $f) {
                            if (strtolower(pathinfo($f, PATHINFO_EXTENSION)) === 'pdf') {
                                $masterFiles[basename($f)] = $f;
                            }
                        }
                    }
                }

                // Sincronizar archivos dentro de la clase
                $filesInAlmacen = collect(Storage::disk('local')->files($claseDstDir))
                    ->map(fn($f) => basename($f))
                    ->toArray();
                
                // Eliminar huérfanos dentro de la clase
                foreach ($filesInAlmacen as $fa) {
                    if (!isset($masterFiles[$fa])) {
                        Storage::disk('local')->delete($claseDstDir . '/' . $fa);
                    }
                }

                // Copiar nuevos
                foreach ($masterFiles as $name => $srcPath) {
                    $dstPath = $claseDstDir . '/' . $name;
                    if (!Storage::disk('local')->exists($dstPath)) {
                        Storage::disk('local')->copy($srcPath, $dstPath);
                    }
                }
            }
        }

        // 3. Actualizar snapshot de archivos en el historial
        $almacenFiles = [];
        if (Storage::disk('local')->exists($dstDir)) {
            $almacenFiles = collect(Storage::disk('local')->files($dstDir))
                ->filter(fn($f) => strtolower(pathinfo($f, PATHINFO_EXTENSION)) === 'pdf')
                ->map(fn($f) => basename($f))
                ->values()
                ->toArray();
        }

        FundicionHistory::updateOrCreate(
            ['ot' => $otName],
            [
                'status'           => 'activa',
                'alert_sent_at'    => now(), // Asegurar que aparezca en Almacén inmediatamente
                'almacen_archivos' => $almacenFiles,
            ]
        );
    }

        /**
     * @param \Illuminate\Http\Request Request $request
     */
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
            // Check fallback for read-only error
            $oldPath = self::OLD_BASE_DIR . '/' . $ot . '/' . $archivo;
            if (Storage::disk('local')->exists($oldPath)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Los archivos antiguos son de solo lectura.',
                ], 403);
            }

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

        /**
     * @param \Illuminate\Http\Request Request $request
     */
    public function deleteFolder(Request $request)
    {
        $request->validate([
            'ot' => 'required|string|max:200',
        ]);

        $ot      = $this->sanitizePath($request->input('ot'));
        $dirPath = self::BASE_DIR . '/' . $ot;

        if (!Storage::disk('local')->exists($dirPath)) {
            return response()->json([
                'success' => false,
                'message' => 'La carpeta no existe.',
            ], 404);
        }

        // ─── Borrar SOLO del directorio ADMIN (FUNDICION_GIS) ────────────────────
        Storage::disk('local')->deleteDirectory($dirPath);
        $this->logAction('eliminar_carpeta', $ot, 'Eliminación de Carpeta OT (Admin)');

        // ─── Soft-delete en histórico de Almacén ─────────────────────────────────
        // FUNDICION_ALMACEN/{ot}/ NO se toca. Solo cambiamos el estado visual.
        FundicionHistory::query()->where('ot', $ot)->update(['status' => 'inactiva']);

        return response()->json([
            'success' => true,
            'message' => "Carpeta '{$ot}' eliminada. Los PDFs de Almacén permanecen como registros históricos (estado: Inactiva).",
        ]);
    }

        /**
     * @param \Illuminate\Http\Request Request $request
     */
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
    // AYUDAS VISUALES PARA FUNDICIÓN
    // =========================================================================

    public function saveAyudas(Request $request)
    {
        $request->validate([
            'ot' => 'required|string|max:200',
            'ayudas' => 'nullable|array',
            'ayudas.*' => 'string'
        ]);

        $ot = $this->sanitizePath($request->input('ot'));
        $nuevasAyudas = $request->input('ayudas', []);

        // Obtener historial actual para no desvincular las anteriores (Merge)
        $history = FundicionHistory::query()->where('ot', $ot)->first();
        $ayudasPrevias = $history ? ($history->ayudas_config ?? []) : [];
        $ayudasFinales = array_unique(array_merge($ayudasPrevias, $nuevasAyudas));

        // Guardar la selección en el historial (configuración)
        FundicionHistory::updateOrCreate(
            ['ot' => $ot],
            ['ayudas_config' => $ayudasFinales]
        );

        $this->logAction('guardar_ayudas', $ot, 'Se seleccionaron ' . count($ayudasFinales) . ' ayudas visuales.');

        // Sincronizar copias en el directorio de Almacén inmediatamente
        $this->copyToAlmacen($ot);

        $msg = 'Ayudas visuales guardadas correctamente para ' . $ot;

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => $msg
            ]);
        }

        return redirect()->back()->with('success', $msg);
    }

    /**
     * Desvincula todas las ayudas visuales de una OT y limpia su carpeta en Almacén.
     */
    public function unlinkAyudas(Request $request)
    {
        $request->validate(['ot' => 'required|string|max:200']);
        $ot = $this->sanitizePath($request->input('ot'));

        FundicionHistory::query()->where('ot', $ot)->update(['ayudas_config' => []]);

        // Limpiar carpeta física en Almacén
        $ayudasDstDir = self::ALMACEN_DIR . '/' . $ot . '/ayudas_visuales';
        if (Storage::disk('local')->exists($ayudasDstDir)) {
            Storage::disk('local')->deleteDirectory($ayudasDstDir);
        }

        $this->logAction('desvincular_ayudas', $ot, 'Se desvincularon todas las ayudas visuales.');

        return response()->json([
            'success' => true,
            'message' => 'Ayudas visuales desvinculadas correctamente.'
        ]);
    }

    // =========================================================================
    // HELPERS PRIVADOS
    // =========================================================================

    private function buildStructure(): array
    {
        $estructura = [];

        // 1. Nuevo
        if (Storage::disk('local')->exists(self::BASE_DIR)) {
            $otDirs = Storage::disk('local')->directories(self::BASE_DIR);
            foreach ($otDirs as $dir) {
                $otName = basename($dir);
                $estructura[$otName] = [];
            }
        }

        // 2. Viejo
        if (Storage::disk('local')->exists(self::OLD_BASE_DIR)) {
            $oldDirs = Storage::disk('local')->directories(self::OLD_BASE_DIR);
            foreach ($oldDirs as $dir) {
                $otName = basename($dir);
                $estructura[$otName] = [];
            }
        }

        // 3. Vincular con Ayudas (Solo si el módulo lo requiere, pero aquí lo hacemos general para Fundición)
        $histories = FundicionHistory::all();
        foreach ($histories as $h) {
            if (isset($estructura[$h->ot])) {
                $estructura[$h->ot] = $h->ayudas_config ?? [];
            }
        }

        ksort($estructura, SORT_NATURAL);

        // Si no estamos en modo fundición, quizás el blade espera una lista simple.
        // Pero el blade de manage_documentation maneja varios módulos.
        // Vamos a retornar el array asociativo y el blade decidirá.
        return $estructura;
    }

    /**
     * @param string $action
     * @param string $ruta
     * @param string|null $archivo
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

        FundicionFileLog::create([
            'user_id' => $user?->id,
            'user_name' => $userName,
            'action' => $action,
            'ruta' => $ruta,
            'archivo' => $archivo,
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
