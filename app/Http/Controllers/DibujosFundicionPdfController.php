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
use Illuminate\Support\Facades\Log;
use Normalizer;
use App\Services\FundicionPaths;
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
        $todasLasOTs = Orden_trabajo::with('moldura')
            ->whereHas('clases', fn($q) => $q->where('finalizada', '=', 0, 'and'))
            ->orderBy('id', 'asc')
            ->get();

        $otSeleccionadaId = $request->query('ot_id');
        $claseSeleccionadaId = $request->query('clase_id');

        $otActiva = $otSeleccionadaId ? $todasLasOTs->first(fn($ot) => (int) $ot->id === (int) $otSeleccionadaId) : null;
        $claseActiva = null;
        if ($claseSeleccionadaId) {
            if (is_numeric($claseSeleccionadaId)) {
                $claseActiva = optional($otActiva?->clases)->first(fn($c) => (int) $c->id === (int) $claseSeleccionadaId);
            } elseif (in_array($claseSeleccionadaId, ['Pistones', 'Guías', 'Guias'])) {
                // Clase virtual para ayudas manuales
                $claseActiva = (object) [
                    'id' => $claseSeleccionadaId,
                    'nombre' => $claseSeleccionadaId
                ];
            }
        }

        // --- Lógica de Ayudas Visuales ---
        $clasesBD = Clase::all(['id', 'nombre'])->mapWithKeys(fn($c) => [$c->id => $c->nombre])->toArray();
        $clasesCompletas = array_unique(array_merge($clasesBD, ['Pistones', 'Guías']));
        sort($clasesCompletas);

        $ayudasDisponibles = [];

        // Buscamos procesos en AMBOS directorios
        $newBase = 'DOCUMENTACION_GIS/AYUDAS_FUNDICION';
        $oldBase = 'AYUDAS_GIS';


        foreach ($clasesCompletas as $clase) {
            $bases = [$newBase, $oldBase];
            foreach ($bases as $base) {
                // Intentar nueva estructura (1 nivel) y legacy (2 niveles)
                $candidates = [
                    $base . '/' . $clase,
                    $base . '/' . $clase . '/Fundicion'
                ];

                foreach ($candidates as $dir) {
                    if (Storage::disk('local')->exists($dir)) {
                        $files = Storage::disk('local')->files($dir);
                        $hasPdf = collect($files)->contains(fn($f) => strtolower(pathinfo($f, PATHINFO_EXTENSION)) === 'pdf');
                        if ($hasPdf) {
                            $ayudasDisponibles[] = $clase;
                            break 2; // Encontrado en esta clase, pasar a la siguiente base o clase
                        }
                    }
                }
            }
        }
        $ayudasDisponibles = array_unique(array_merge($ayudasDisponibles, ['Pistones', 'Guías']));
        sort($ayudasDisponibles);
        $ayudasSeleccionadas = [];
        $hasDibujos = false;
        $ayudasConEstado = [];

        if ($otActiva) {
            $otFolderNameRaw = "OT " . $otActiva->id . ($otActiva->moldura ? " - " . $otActiva->moldura->nombre : "");
            $otFolderName = $this->normalizeOTName($this->sanitizePath($otFolderNameRaw));

            $history = FundicionHistory::where('ot', '=', $otFolderName, 'and')->first();
            $ayudasSeleccionadas = $history ? ($history->ayudas_config ?? []) : [];

            // 1. Verificar dibujos principales
            $newPath = self::BASE_DIR . '/' . $otFolderName;
            $oldPath = self::OLD_BASE_DIR . '/' . $otFolderName;
            $newFiles = Storage::disk('local')->exists($newPath) ? Storage::disk('local')->files($newPath) : [];
            $oldFiles = Storage::disk('local')->exists($oldPath) ? Storage::disk('local')->files($oldPath) : [];
            $hasDibujos = collect(array_merge($newFiles, $oldFiles))->contains(fn($f) => strtolower(pathinfo($f, PATHINFO_EXTENSION)) === 'pdf');

            foreach ($ayudasDisponibles as $clase) {
                // REQUERIMIENTO: Solo Pistones y Guias aparecen en el panel manual
                $esManualHardcoded = in_array($clase, ['Pistones', 'Guias', 'Guías']);
                if (!$esManualHardcoded)
                    continue;

                $isNew = true;
                $masterFiles = [];
                $bases = [$newBase, $oldBase];
                foreach ($bases as $base) {
                    $claseDir = $base . '/' . $clase;
                    if (Storage::disk('local')->exists($claseDir)) {
                        $fList = Storage::disk('local')->files($claseDir);
                        foreach ($fList as $f) {
                            if (strtolower(pathinfo($f, PATHINFO_EXTENSION)) === 'pdf')
                                $masterFiles[] = basename($f);
                        }
                    }
                }

                $almacenAyudasBase = self::ALMACEN_DIR . '/' . $otFolderName . '/ayudas_visuales';
                $almacenClaseDir = $almacenAyudasBase . '/' . $clase;
                $almacenFiles = [];
                if (Storage::disk('local')->exists($almacenClaseDir)) {
                    $fList = Storage::disk('local')->files($almacenClaseDir);
                    foreach ($fList as $f) {
                        if (strtolower(pathinfo($f, PATHINFO_EXTENSION)) === 'pdf')
                            $almacenFiles[] = basename($f);
                    }
                }

                if (!empty($masterFiles) && !empty($almacenFiles)) {
                    sort($masterFiles);
                    sort($almacenFiles);
                    if ($masterFiles === $almacenFiles)
                        $isNew = false;
                }

                $ayudasConEstado[] = [
                    'nombre' => $clase,
                    'is_new' => $isNew,
                    'is_selected' => in_array($clase, $ayudasSeleccionadas)
                ];
            }
        }

        $todasLasClases = Clase::all();

        // Consolidar historiales (agrupar por nombre normalizado para evitar duplicidades por guiones)
        $historialesRaw = FundicionHistory::all();
        $historiales = [];
        foreach ($historialesRaw as $h) {
            $normName = $this->normalizeOTName($h->ot);
            if (!isset($historiales[$normName])) {
                $historiales[$normName] = $h->ayudas_config ?? [];
            } else {
                // Si ya existe, combinamos para no perder datos
                $historiales[$normName] = array_unique(array_merge($historiales[$normName], ($h->ayudas_config ?? [])));
            }
        }

        return view('wo_views.manage_documentation', array_merge(compact(
            'estructura',
            'todasLasOTs',
            'todasLasClases',
            'otSeleccionadaId',
            'otActiva',
            'ayudasConEstado',
            'historiales',
            'hasDibujos'
        ), [
            'moduleType' => 'fundicion',
            'modulePrefix' => 'fundicion',
            'pageTitle' => 'Dibujos de Fundición',
            'directoryName' => 'DOCUMENTACION_GIS / DIBUJOS_FUNDICION',
            'moduleMetadata' => [
                'description' => 'Selecciona la OT para buscar o subir dibujos de fundición.'
            ],
            'claseSeleccionadaId' => $claseSeleccionadaId,
            'claseActiva' => $claseActiva,
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

    /**
     * Devuelve el conteo total de archivos para una OT (Dibujos + Ayudas Visuales Vinculadas).
     * @param \Illuminate\Http\Request Request $request
     */
    public function getTotalFiles(Request $request)
    {
        $ot = $this->sanitizePath($request->query('ot', ''));
        if (empty($ot))
            return response()->json(['total' => 0]);

        $otNorm = $this->normalizeOTName($ot);
        $total = 0;

        // 1. Dibujos en directorio base
        $baseDir = self::BASE_DIR . '/' . $otNorm;
        if (Storage::disk('local')->exists($baseDir)) {
            $total += collect(Storage::disk('local')->allFiles($baseDir))
                ->filter(fn($f) => strtolower(pathinfo($f, PATHINFO_EXTENSION)) === 'pdf')->count();
        }

        // 2. Dibujos en directorio legado
        $oldBaseDir = self::OLD_BASE_DIR . '/' . $otNorm;
        if (Storage::disk('local')->exists($oldBaseDir)) {
            $total += collect(Storage::disk('local')->allFiles($oldBaseDir))
                ->filter(fn($f) => strtolower(pathinfo($f, PATHINFO_EXTENSION)) === 'pdf')->count();
        }

        // 3. Ayudas Visuales vinculadas
        $history = FundicionHistory::where('ot', '=', $otNorm, 'and')->first();
        $ayudas = $history ? ($history->ayudas_config ?? []) : [];
        foreach ($ayudas as $aName) {
            $ayudaBases = [
                'DOCUMENTACION_GIS/AYUDAS_FUNDICION/' . $aName,           // Nuevo
                'DOCUMENTACION_GIS/AYUDAS_FUNDICION/' . $aName . '/Fundicion', // Legacy intermedio
                'AYUDAS_GIS/' . $aName . '/Fundicion'                     // Legacy antiguo
            ];
            foreach ($ayudaBases as $b) {
                if (Storage::disk('local')->exists($b)) {
                    $total += collect(Storage::disk('local')->files($b))
                        ->filter(fn($f) => strtolower(pathinfo($f, PATHINFO_EXTENSION)) === 'pdf')->count();
                }
            }
        }

        return response()->json(['total' => $total]);
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
        $clase = $this->sanitizePath($request->query('clase', ''));

        if (empty($ot)) {
            return response()->json(['error' => 'Parámetro OT es requerido.'], 422);
        }

        if ($clase === 'null' || $clase === '--')
            $clase = '';

        // Resolver nombre de carpeta si se pasó un ID
        if (is_numeric($ot)) {
            $otModel = Orden_trabajo::query()->with('moldura')->find($ot);
            if ($otModel) {
                $otLabel = "OT " . $otModel->id . ($otModel->moldura ? " - " . $otModel->moldura->nombre : "");
                $ot = $this->normalizeOTName($this->sanitizePath($otLabel));
            }
        } else {
            $ot = $this->normalizeOTName($ot);
        }

        // 1. Directorios de Clase (Nuevo esquema)
        $newClasePath = self::BASE_DIR . '/' . $ot . '/' . $clase;
        $oldClasePath = self::OLD_BASE_DIR . '/' . $ot . '/' . $clase;

        // 2. Directorios Raíz (Esquema anterior/legado)
        $newRootPath = self::BASE_DIR . '/' . $ot;
        $oldRootPath = self::OLD_BASE_DIR . '/' . $ot;

        $files = [];

        // --- Buscar en Clase seleccionada ---
        if (!empty($clase)) {
            $files = array_merge($files, Storage::disk('local')->exists($newClasePath) ? Storage::disk('local')->files($newClasePath) : []);
            $files = array_merge($files, Storage::disk('local')->exists($oldClasePath) ? Storage::disk('local')->files($oldClasePath) : []);
        }

        // --- Buscar en Raíz de la OT (Archivos que no tienen clase aún) ---
        $rootFilesNew = Storage::disk('local')->exists($newRootPath) ? Storage::disk('local')->files($newRootPath) : [];
        $rootFilesOld = Storage::disk('local')->exists($oldRootPath) ? Storage::disk('local')->files($oldRootPath) : [];

        $files = array_merge($files, $rootFilesNew, $rootFilesOld);

        $allFiles = collect($files)
            ->filter(fn($f) => strtolower(pathinfo($f, PATHINFO_EXTENSION)) === 'pdf')
            ->map(function ($f) use ($ot, $clase, $newClasePath, $oldClasePath, $newRootPath, $oldRootPath) {
                $rawName = basename($f);
                $utf8Name = $this->toUtf8($rawName);
                $fullPath = $f;

                // Determinar si el archivo está en la raíz o en una clase para generar la URL de servicio correcta
                $esRaiz = (strpos($fullPath, $newRootPath . '/' . $rawName) !== false || strpos($fullPath, $oldRootPath . '/' . $rawName) !== false);

                return [
                    'nombre' => $utf8Name,
                    'url' => route('fundicion.serve', [
                        'ot' => $ot,
                        'clase' => $esRaiz ? '--' : $clase,
                        'archivo' => $utf8Name,
                    ]),
                    'es_raiz' => $esRaiz
                ];
            })
            ->unique('nombre')
            ->values();

        return response()->json([
            'archivos' => $allFiles,
            'ot' => $ot,
            'clase' => $clase,
            'existe' => (count($allFiles) > 0),
        ]);
    }

    /**
     * @param \Illuminate\Http\Request Request $request
     */
    public function serveFile(Request $request): BinaryFileResponse
    {
        $ot = $this->sanitizePath($request->query('ot', ''));
        $clase = $this->sanitizePath($request->query('clase', ''));
        $archivo = $this->sanitizeFileName($request->query('archivo', ''));

        if (empty($ot) || empty($archivo)) {
            abort(422, 'Parámetros inválidos.');
        }

        if (is_numeric($ot)) {
            $otModel = Orden_trabajo::query()->with('moldura')->find($ot);
            if ($otModel) {
                $otLabel = "OT " . $otModel->id . ($otModel->moldura ? " - " . $otModel->moldura->nombre : "");
                $ot = $this->normalizeOTName($this->sanitizePath($otLabel));
            }
        } else {
            $ot = $this->normalizeOTName($ot);
        }

        // Si la clase es '--', buscamos en la raíz de la OT
        if ($clase === '--' || empty($clase)) {
            $dirPath = self::BASE_DIR . '/' . $ot;
            if (!Storage::disk('local')->exists($dirPath)) {
                $dirPath = self::OLD_BASE_DIR . '/' . $ot;
            }
        } else {
            $dirPath = self::BASE_DIR . '/' . $ot . '/' . $clase;
            if (!Storage::disk('local')->exists($dirPath)) {
                $dirPath = self::OLD_BASE_DIR . '/' . $ot . '/' . $clase;
            }
        }

        $dirPath = $this->resolveCaseInsensitivePath($dirPath);

        if (!Storage::disk('local')->exists($dirPath)) {
            Log::warning("Directorio no encontrado en Fundicion (Dibujos). OT solicitada: {$ot}, Clase: {$clase}. Directorio esperado: {$dirPath}");
            abort(404, 'Archivo no encontrado.');
        }

        $files = Storage::disk('local')->files($dirPath);
        $foundFile = null;

        $archivoNorm = Normalizer::normalize(mb_strtolower($archivo, 'UTF-8'), Normalizer::FORM_C);

        foreach ($files as $f) {
            $rawName = basename($f);
            $utf8Name = $this->toUtf8($rawName);
            $utf8NameNorm = Normalizer::normalize(mb_strtolower($utf8Name, 'UTF-8'), Normalizer::FORM_C);

            if ($utf8NameNorm === $archivoNorm) {
                $foundFile = $f;
                break;
            }
        }

        if (!$foundFile) {
            Log::warning("Archivo no encontrado en Fundicion (Dibujos). OT: {$ot}, Archivo buscado: {$archivo}, dentro del directorio: {$dirPath}");
            abort(404, 'Archivo no encontrado.');
        }

        /** @var \Illuminate\Filesystem\FilesystemAdapter $disk */
        $disk = Storage::disk('local');
        $fullPath = $disk->path($foundFile);

        return response()->file($fullPath, [
            'Content-Type' => 'application/pdf',
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
                'ot_id' => 'required|exists:orden_trabajo,id',
                'clase' => 'required|string|max:100',
            ]);

            $otId = $request->input('ot_id');
            $clase = $this->sanitizePath($request->input('clase'));

            $otModel = Orden_trabajo::query()->with('moldura')->findOrFail($otId);
            $otFolderName = "OT " . $otModel->id . ($otModel->moldura ? " - " . $otModel->moldura->nombre : "");
            $otFolderName = $this->normalizeOTName($this->sanitizePath($otFolderName));

            $dirPath = self::BASE_DIR . '/' . $otFolderName . '/' . $clase;

            if (Storage::disk('local')->exists($dirPath)) {
                return response()->json([
                    'success' => false,
                    'message' => 'La carpeta ya existe.',
                ], 409);
            }

            Storage::disk('local')->makeDirectory($dirPath);

            $history = FundicionHistory::firstOrCreate(['ot' => $otFolderName]);

            // VINCULACIÓN AUTOMÁTICA: Agregar clase a ayudas_config si no existe
            $ayudas = $history->ayudas_config ?? [];
            if (!in_array($clase, $ayudas)) {
                $ayudas[] = $clase;
                $history->ayudas_config = $ayudas;
                $history->save();
                $this->copyToAlmacen($otFolderName); // Sincronizar inmediatamente
            }

            $this->logAction('crear_carpeta', $otFolderName . '/' . $clase, "Creación de Clase con Vinculación Automática");

            return response()->json([
                'success' => true,
                'message' => "Carpeta {$otFolderName}/{$clase} creada correctamente.",
                'ot' => $otFolderName,
                'clase' => $clase,
            ]);
        } catch (\Exception $e) {
            Log::error("Error en DibujosFundicionPdfController@createFolder: " . $e->getMessage());
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
            'ot_id' => 'required|exists:orden_trabajo,id',
            'clase' => 'nullable|string|max:100',
            'pdf' => 'required|file|mimes:pdf',
        ]);

        $otId = $request->input('ot_id');
        $clase = $this->sanitizePath($request->input('clase'));

        $otModel = Orden_trabajo::query()->with('moldura')->findOrFail($otId);
        $otFolderName = "OT " . $otModel->id . ($otModel->moldura ? " - " . $otModel->moldura->nombre : "");
        $otFolderName = $this->normalizeOTName($this->sanitizePath($otFolderName));

        $dirPath = self::BASE_DIR . '/' . $otFolderName . '/' . $clase;

        if (!Storage::disk('local')->exists($dirPath)) {
            Storage::disk('local')->makeDirectory($dirPath);
            $history = FundicionHistory::firstOrCreate(['ot' => $otFolderName]);

            // VINCULACIÓN AUTOMÁTICA: Si se sube a una clase, vincularla
            $ayudas = $history->ayudas_config ?? [];
            if (!empty($clase) && !in_array($clase, $ayudas)) {
                $ayudas[] = $clase;
                $history->ayudas_config = $ayudas;
                $history->save();
                $this->copyToAlmacen($otFolderName);
            }
        }

        $file = $request->file('pdf');
        $cleanName = $this->sanitizeFileName($file->getClientOriginalName());

        // Prefijar con el nombre de la clase si no lo tiene ya y si no es nula
        $newName = $cleanName;
        if (!empty($clase)) {
            $prefix = $clase . " - ";
            $newName = (strpos($cleanName, $prefix) === 0) ? $cleanName : $prefix . $cleanName;
        }

        if (Storage::disk('local')->exists($dirPath . '/' . $newName)) {
            return response()->json([
                'success' => false,
                'message' => "Ya existe un archivo con el nombre '{$newName}'. Use la función de Reemplazar.",
            ], 409);
        }

        $file->storeAs($dirPath, $newName, 'local');

        $this->logAction('subir_pdf', $otFolderName . '/' . $clase, $newName);

        return response()->json([
            'success' => true,
            'message' => "PDF '{$newName}' subido correctamente.",
            'nombre' => $newName,
            'url' => route('fundicion.serve', [
                'ot' => $otFolderName,
                'clase' => $clase,
                'archivo' => $newName,
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

        $otFolderName = $this->normalizeOTName($this->sanitizePath($request->input('ot')));
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
        $history = FundicionHistory::where('ot', '=', $otName, 'and')->first();
        $ayudas = $history ? ($history->ayudas_config ?? []) : [];

        $emailsStr = config('services.almacen.email', 'almacentec@grupoindsaavedra.com');
        $emails = array_filter(array_map('trim', explode(',', $emailsStr)));

        Mail::to($emails)->send(new DibujoFundicionAlertMail($otName, $fileName, $ayudas));
    }

    /**
     * Copia los archivos del directorio de trabajo al directorio protegido de Almacén.
     * También sincroniza las ayudas visuales vinculadas.
     *
     * @param string $otName
     */
    public static function copyToAlmacen(string $otName): void
    {
        $srcDir = self::BASE_DIR . '/' . $otName;
        $dstDir = self::ALMACEN_DIR . '/' . $otName;

        // 1. Sincronizar dibujos por clase → nueva ruta: {Clase}/Dibujos/
        if (Storage::disk('local')->exists($srcDir)) {
            if (!Storage::disk('local')->exists($dstDir)) {
                Storage::disk('local')->makeDirectory($dstDir);
            }

            // Obtener todos los PDFs organizados por clase (subcarpetas directas de la OT)
            $srcFilesFull = collect(Storage::disk('local')->allFiles($srcDir))
                ->filter(fn($f) => strtolower(pathinfo($f, PATHINFO_EXTENSION)) === 'pdf');

            // Mapear rutas relativas al directorio fuente de la OT
            $srcFilesRel = $srcFilesFull->map(fn($f) => str_replace(str_replace('\\', '/', $srcDir) . '/', '', str_replace('\\', '/', $f)))->toArray();

            // Obtener archivos actuales en Almacén en la carpeta Dibujos/ (nueva estructura)
            $dstFilesFull = collect(Storage::disk('local')->allFiles($dstDir))
                ->filter(function ($f) use ($dstDir) {
                    $rel = str_replace(str_replace('\\', '/', $dstDir) . '/', '', str_replace('\\', '/', $f));
                    // Solo archivos dentro de {Clase}/Dibujos/ (nueva) o {Clase}/ (legacy)
                    return !str_starts_with($rel, 'Ayudas_Visuales/')
                        && !str_starts_with($rel, 'ayudas_visuales/')
                        && !str_starts_with($rel, 'Documentos_Aprobados/')
                        && !str_starts_with($rel, 'Documentos_Rechazados/')
                        && strtolower(pathinfo($f, PATHINFO_EXTENSION)) === 'pdf';
                });

            $dstFilesRel = $dstFilesFull->map(fn($f) => str_replace(str_replace('\\', '/', $dstDir) . '/', '', str_replace('\\', '/', $f)))->toArray();

            // Sincronizar: eliminar dibujos en Almacén que ya no existen en Ingeniería
            foreach ($dstFilesRel as $dfRel) {
                // Normalizar: si el destino tiene /Dibujos/ en la ruta, comparar sin él
                $dfRelNorm = preg_replace('#/Dibujos/#', '/', $dfRel);
                $srcFilesRelNorm = array_map(fn($r) => preg_replace('#/Dibujos/#', '/', $r), $srcFilesRel);
                if (!in_array($dfRelNorm, $srcFilesRelNorm)) {
                    Storage::disk('local')->delete($dstDir . '/' . $dfRel);
                }
            }

            // Copiar dibujos a nueva ruta: {Clase}/Dibujos/{archivo}
            foreach ($srcFilesRel as $sfRel) {
                // sfRel viene como: {Clase}/{archivo.pdf}  (estructura de DIBUJOS_FUNDICION)
                $parts = explode('/', $sfRel, 2);
                if (count($parts) === 2) {
                    [$clase, $archivo] = $parts;
                    // Nueva ruta: {Clase}/Dibujos/{archivo}
                    $dstPath = $dstDir . '/' . $clase . '/' . FundicionPaths::DIBUJOS . '/' . $archivo;
                } else {
                    // Archivo en raíz de la OT (sin clase) → lo dejamos tal cual
                    $dstPath = $dstDir . '/' . $sfRel;
                }

                $parentDir = dirname($dstPath);
                if (!Storage::disk('local')->exists($parentDir)) {
                    Storage::disk('local')->makeDirectory($parentDir);
                }
                // Copia completa e independiente: sobreescribir siempre
                if (Storage::disk('local')->exists($dstPath)) {
                    Storage::disk('local')->delete($dstPath);
                }
                Storage::disk('local')->copy($srcDir . '/' . $sfRel, $dstPath);
            }
        }

        // 2. Copiar Ayudas Visuales → nueva ruta: {Clase}/Ayudas_Visuales/
        $history = FundicionHistory::where('ot', '=', $otName, 'and')->first();
        $ayudasVinculadas = $history ? ($history->ayudas_config ?? []) : [];

        if (!empty($ayudasVinculadas)) {
            $newBase = 'DOCUMENTACION_GIS/AYUDAS_FUNDICION';
            $oldBase = 'AYUDAS_GIS';

            foreach ($ayudasVinculadas as $clase) {
                // Nueva ruta de destino: {OT}/{Clase}/Ayudas_Visuales/
                $claseDstDir = $dstDir . '/' . $clase . '/' . FundicionPaths::AYUDAS_VISUALES;
                if (!Storage::disk('local')->exists($claseDstDir)) {
                    Storage::disk('local')->makeDirectory($claseDstDir);
                }

                // Obtener archivos maestros (nuevo esquema y legacy)
                $masterFiles = [];
                $bases = [$newBase, $oldBase];
                foreach ($bases as $base) {
                    $candidates = [
                        $base . '/' . $clase,
                        $base . '/' . $clase . '/Fundicion'
                    ];
                    foreach ($candidates as $srcClaseDir) {
                        if (Storage::disk('local')->exists($srcClaseDir)) {
                            $fList = Storage::disk('local')->files($srcClaseDir);
                            foreach ($fList as $f) {
                                if (strtolower(pathinfo($f, PATHINFO_EXTENSION)) === 'pdf') {
                                    $masterFiles[basename($f)] = $f;
                                }
                            }
                        }
                    }
                }

                // Eliminar huérfanos
                $filesInDst = collect(Storage::disk('local')->files($claseDstDir))
                    ->map(fn($f) => basename($f))->toArray();
                foreach ($filesInDst as $fa) {
                    if (!isset($masterFiles[$fa])) {
                        Storage::disk('local')->delete($claseDstDir . '/' . $fa);
                    }
                }

                // Copiar todas (sobreescribir siempre)
                foreach ($masterFiles as $name => $srcPath) {
                    $targetAyudaPath = $claseDstDir . '/' . $name;
                    if (Storage::disk('local')->exists($targetAyudaPath)) {
                        Storage::disk('local')->delete($targetAyudaPath);
                    }
                    Storage::disk('local')->copy($srcPath, $targetAyudaPath);
                }
            }
        }

        // 3. Actualizar snapshot de archivos en el historial
        $almacenFiles = [];
        if (Storage::disk('local')->exists($dstDir)) {
            $almacenFiles = collect(Storage::disk('local')->allFiles($dstDir))
                ->filter(function ($f) use ($dstDir) {
                    $rel = str_replace(str_replace('\\', '/', $dstDir) . '/', '', str_replace('\\', '/', $f));
                    // Incluir solo dibujos (nuevos en Dibujos/ o legacy en raíz de clase)
                    return !str_starts_with($rel, 'Ayudas_Visuales/')
                        && !str_starts_with($rel, 'ayudas_visuales/')
                        && !str_starts_with($rel, 'Documentos_Aprobados/')
                        && !str_starts_with($rel, 'Documentos_Rechazados/')
                        && strtolower(pathinfo($f, PATHINFO_EXTENSION)) === 'pdf';
                })
                ->map(fn($f) => str_replace(str_replace('\\', '/', $dstDir) . '/', '', str_replace('\\', '/', $f)))
                ->values()
                ->toArray();
        }

        // 4. Limpiar los registros antiguos de Almacén y Calidad
        // Esto garantiza que cada vez que se envía un correo, el flujo se reinicia (borrón y cuenta nueva)
        \App\Models\PreOrdenFundicion::where('ot', '=', $otName, 'and')->delete();
        \App\Models\LiberacionModeloFundicion::where('ot', '=', $otName, 'and')->delete();

        FundicionHistory::updateOrCreate(
            ['ot' => $otName],
            [
                'status' => 'activa',
                'alert_sent_at' => now(), // Asegurar que aparezca en Almacén inmediatamente
                'almacen_archivos' => $almacenFiles,
                'tiene_modelo' => false,
                'pre_orden_sent' => false,
                'calidad_revision_status' => null,
            ]
        );

        // 5. Espejo Almacén → Calidad: Copiar dibujos + ayudas_visuales al directorio de Calidad
        //    para que el equipo de Calidad vea los mismos archivos que subió Almacén.
        //    Se excluye la subcarpeta preordenes/ (es territorio exclusivo de Calidad).
        $calidadDir = 'DOCUMENTACION_GIS/CALIDAD_FUNDICION/' . $otName;
        if (Storage::disk('local')->exists($dstDir)) {
            $allAlmacenFiles = Storage::disk('local')->allFiles($dstDir);
            foreach ($allAlmacenFiles as $srcFile) {
                $relPath = ltrim(substr(str_replace('\\', '/', $srcFile), strlen(str_replace('\\', '/', $dstDir))), '/');

                // Excluir carpetas exclusivas de Calidad (documentos generados y escaneados)
                if (
                    str_starts_with($relPath, 'Documentos_Aprobados/') ||
                    str_starts_with($relPath, 'Documentos_Rechazados/') ||
                    str_starts_with($relPath, 'ayudas_visuales/preordenes/') ||
                    str_starts_with($relPath, 'preordenes/')
                ) {
                    continue;
                }

                $targetPath = $calidadDir . '/' . $relPath;
                $targetDirPath = dirname($targetPath);

                if (!Storage::disk('local')->exists($targetDirPath)) {
                    Storage::disk('local')->makeDirectory($targetDirPath);
                }

                // Copia completa e independiente hacia Calidad: sobreescribir siempre
                if (Storage::disk('local')->exists($targetPath)) {
                    Storage::disk('local')->delete($targetPath);
                }
                Storage::disk('local')->copy($srcFile, $targetPath);
            }
        }
    }

    /**
     * @param \Illuminate\Http\Request Request $request
     */
    public function deletePdf(Request $request)
    {
        $request->validate([
            'ot' => 'required|string|max:200',
            'clase' => 'nullable|string|max:100',
            'archivo' => 'required|string|max:300',
        ]);

        $ot = $this->sanitizePath($request->input('ot'));
        $otNorm = $this->normalizeOTName($ot);
        $clase = $this->sanitizePath($request->input('clase'));
        $archivo = $this->sanitizeFileName($request->input('archivo'));
        $dirPath = ($clase === '--')
            ? self::BASE_DIR . '/' . $otNorm
            : self::BASE_DIR . '/' . $otNorm . '/' . $clase;

        $files = Storage::disk('local')->exists($dirPath) ? Storage::disk('local')->files($dirPath) : [];
        $foundFile = null;
        foreach ($files as $f) {
            $rawName = basename($f);
            $utf8Name = $this->toUtf8($rawName);
            if ($utf8Name === $archivo) {
                $foundFile = $f;
                break;
            }
        }

        if (!$foundFile) {
            $oldDirPath = ($clase === '--')
                ? self::OLD_BASE_DIR . '/' . $otNorm
                : self::OLD_BASE_DIR . '/' . $otNorm . '/' . $clase;
            $oldFiles = Storage::disk('local')->exists($oldDirPath) ? Storage::disk('local')->files($oldDirPath) : [];
            foreach ($oldFiles as $f) {
                $rawName = basename($f);
                $utf8Name = $this->toUtf8($rawName);
                if ($utf8Name === $archivo) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Los archivos antiguos son de solo lectura.',
                    ], 403);
                }
            }

            return response()->json([
                'success' => false,
                'message' => 'El archivo no existe.',
            ], 404);
        }

        Storage::disk('local')->delete($foundFile);
        $this->logAction('eliminar_pdf', $otNorm . '/' . $clase, $archivo);

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
            'clase' => 'required|string|max:100',
        ]);

        $ot = $this->sanitizePath($request->input('ot'));
        $otNorm = $this->normalizeOTName($ot);
        $clase = $this->sanitizePath($request->input('clase'));

        $dirPath = self::BASE_DIR . '/' . $otNorm . '/' . $clase;
        $oldDirPath = self::OLD_BASE_DIR . '/' . $otNorm . '/' . $clase;

        $existsBase = Storage::disk('local')->exists($dirPath);
        $existsOld = Storage::disk('local')->exists($oldDirPath);

        if (!$existsBase && !$existsOld) {
            return response()->json(['success' => false, 'message' => 'La carpeta no existe.'], 404);
        }

        if ($existsBase) {
            Storage::disk('local')->deleteDirectory($dirPath);
        }
        if ($existsOld) {
            Storage::disk('local')->deleteDirectory($oldDirPath);
        }

        $this->logAction('eliminar_carpeta', $otNorm . '/' . $clase, 'Eliminación de Clase');

        // Sincronizar con histórico (eliminar vinculación si existe)
        $history = FundicionHistory::where('ot', '=', $otNorm, 'and')->first();
        if ($history) {
            $ayudas = $history->ayudas_config ?? [];
            if (in_array($clase, $ayudas)) {
                $ayudas = array_values(array_filter($ayudas, fn($a) => $a !== $clase));
                $history->update(['ayudas_config' => $ayudas]);
                $this->copyToAlmacen($otNorm);
            }
        }

        return response()->json([
            'success' => true,
            'message' => "Carpeta de la clase '{$clase}' eliminada correctamente.",
        ]);
    }

    /**
     * Elimina la carpeta raíz de la OT si está vacía.
     */
    public function deleteParent(Request $request)
    {
        $request->validate(['ot' => 'required|string|max:200']);
        $ot = $this->sanitizePath($request->input('ot'));
        $otNorm = $this->normalizeOTName($ot);

        $dirPath = self::BASE_DIR . '/' . $otNorm;
        $oldDirPath = self::OLD_BASE_DIR . '/' . $otNorm;

        $existsBase = Storage::disk('local')->exists($dirPath);
        $existsOld = Storage::disk('local')->exists($oldDirPath);

        if (!$existsBase && !$existsOld) {
            return response()->json(['success' => false, 'message' => 'La carpeta no existe.'], 404);
        }

        // 1. Eliminar carpetas de trabajo físicas en Ingeniería
        if ($existsBase) {
            Storage::disk('local')->deleteDirectory($dirPath);
        }
        if ($existsOld) {
            Storage::disk('local')->deleteDirectory($oldDirPath);
        }

        // 2. Renombrar la carpeta en Almacén en vez de eliminarla para guardar precedentes
        $timestamp = date('_Ymd_His_del');
        $almacenPath = self::ALMACEN_DIR . '/' . $otNorm;
        if (Storage::disk('local')->exists($almacenPath)) {
            Storage::disk('local')->move($almacenPath, $almacenPath . $timestamp);
        }

        $this->logAction('eliminar_carpeta', $otNorm, 'Eliminación de Directorio Raíz OT (Conservando Almacén como precedente)');

        // 3. Respaldar registros de historial, pre-orden y liberación renombrando el OT para liberar el original
        FundicionHistory::where('ot', '=', $otNorm, 'and')
            ->update([
                'ot' => \Illuminate\Support\Facades\DB::raw("CONCAT(ot, '{$timestamp}')"),
                'status' => 'inactiva'
                // Se conserva almacen_archivos intacto para el registro de archivos
            ]);
        \App\Models\PreOrdenFundicion::where('ot', '=', $otNorm, 'and')
            ->update(['ot' => \Illuminate\Support\Facades\DB::raw("CONCAT(ot, '{$timestamp}')")]);
        \App\Models\LiberacionModeloFundicion::where('ot', '=', $otNorm, 'and')
            ->update(['ot' => \Illuminate\Support\Facades\DB::raw("CONCAT(ot, '{$timestamp}')")]);

        // Si es la OT Original (sin R1, R2, etc.), también desactivamos y renombrarmos todos sus reprocesos
        $isOriginal = !preg_match('/_R\d+$/i', $otNorm);
        if ($isOriginal) {
            $reprocessHistories = FundicionHistory::where('ot', 'LIKE', $otNorm . '_R%', 'and')->get();
            foreach ($reprocessHistories as $rh) {
                // Eliminar físicamente los directorios de los reprocesos en Ingeniería
                $rDirPath = self::BASE_DIR . '/' . $rh->ot;
                $rOldDirPath = self::OLD_BASE_DIR . '/' . $rh->ot;

                if (Storage::disk('local')->exists($rDirPath)) {
                    Storage::disk('local')->deleteDirectory($rDirPath);
                }
                if (Storage::disk('local')->exists($rOldDirPath)) {
                    Storage::disk('local')->deleteDirectory($rOldDirPath);
                }

                // Renombrar copia en Almacén del reproceso en vez de eliminarla
                $rAlmacenPath = self::ALMACEN_DIR . '/' . $rh->ot;
                if (Storage::disk('local')->exists($rAlmacenPath)) {
                    Storage::disk('local')->move($rAlmacenPath, $rAlmacenPath . $timestamp);
                }

                FundicionHistory::where('id', '=', $rh->id, 'and')->update([
                    'ot' => $rh->ot . $timestamp,
                    'status' => 'inactiva'
                    // Se conserva almacen_archivos intacto
                ]);
                \App\Models\PreOrdenFundicion::where('ot', '=', $rh->ot, 'and')
                    ->update(['ot' => $rh->ot . $timestamp]);
                \App\Models\LiberacionModeloFundicion::where('ot', '=', $rh->ot, 'and')
                    ->update(['ot' => $rh->ot . $timestamp]);
            }
        }

        return response()->json([
            'success' => true,
            'message' => "Directorio raíz '{$ot}' eliminado correctamente.",
        ]);
    }

    /**
     * @param \Illuminate\Http\Request Request $request
     */
    public function replacePdf(Request $request)
    {
        $request->validate([
            'ot' => 'required|string|max:200',
            'clase' => 'nullable|string|max:100',
            'archivo_anterior' => 'required|string|max:300',
            'pdf' => 'required|file|mimes:pdf',
        ]);

        $ot = $this->sanitizePath($request->input('ot'));
        $otNorm = $this->normalizeOTName($ot);
        $clase = $this->sanitizePath($request->input('clase'));
        $archivoAnterior = $this->sanitizeFileName($request->input('archivo_anterior'));
        $dirPath = ($clase === '--') ? self::BASE_DIR . '/' . $otNorm : self::BASE_DIR . '/' . $otNorm . '/' . $clase;

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

        $file = $request->file('pdf');
        $cleanName = $this->sanitizeFileName($file->getClientOriginalName());

        // Prefijar con el nombre de la clase si no es raíz
        $newName = ($clase !== '--') ?
            ((strpos($cleanName, $clase . " - ") === 0) ? $cleanName : $clase . " - " . $cleanName) :
            $cleanName;

        $file->storeAs($dirPath, $newName, 'local');

        $this->logAction('reemplazar_pdf', $otNorm . '/' . $clase, "{$archivoAnterior} → {$newName}");

        return response()->json([
            'success' => true,
            'message' => "PDF reemplazado correctamente por '{$newName}'.",
            'nombre' => $newName,
            'url' => route('fundicion.serve', [
                'ot' => $otNorm,
                'clase' => $clase,
                'archivo' => $newName,
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

        $ot = $this->normalizeOTName($this->sanitizePath($request->input('ot')));
        $nuevasAyudasManuales = $request->input('ayudas', []);
        $ayudasManualesPosibles = ['Pistones', 'Guias', 'Guías'];

        $history = FundicionHistory::where('ot', '=', $ot, 'and')->first();
        $ayudasPrevias = $history ? ($history->ayudas_config ?? []) : [];

        // Mantener las que no son manuales (automáticas) + las nuevas manuales seleccionadas
        $ayudasFiltradas = array_filter($ayudasPrevias, function ($a) use ($ayudasManualesPosibles, $nuevasAyudasManuales) {
            if (in_array($a, $ayudasManualesPosibles)) {
                return in_array($a, $nuevasAyudasManuales);
            }
            return !empty($a);
        });

        // Limpiar nulos o basura antes de guardar
        $ayudasFinales = array_values(array_filter(array_unique(array_merge($ayudasFiltradas, $nuevasAyudasManuales)), function ($v) {
            $val = trim(strtolower((string) $v));
            return !empty($val) && $val !== 'null' && $val !== 'undefined';
        }));

        // Guardar con nombre normalizado
        FundicionHistory::updateOrCreate(
            ['ot' => $ot],
            ['ayudas_config' => $ayudasFinales]
        );

        $this->logAction('guardar_ayudas', $ot, 'Se vincularon: ' . implode(', ', $ayudasFinales));

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

        FundicionHistory::where('ot', '=', $ot, 'and')->update(['ayudas_config' => []]);

        // Limpiar carpeta física en Almacén (Se deshabilita para preservar histórico)
        $ayudasDstDir = self::ALMACEN_DIR . '/' . $ot . '/ayudas_visuales';
        // if (Storage::disk('local')->exists($ayudasDstDir)) {
        //     Storage::disk('local')->deleteDirectory($ayudasDstDir);
        // }

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
        $bases = [self::BASE_DIR, self::OLD_BASE_DIR];

        foreach ($bases as $base) {
            if (Storage::disk('local')->exists($base)) {
                $otDirs = Storage::disk('local')->directories($base);
                foreach ($otDirs as $dir) {
                    $otNameRaw = basename($dir);
                    $otName = $this->toUtf8($this->normalizeOTName($otNameRaw));
                    $clases = array_map(fn($d) => $this->toUtf8(basename($d)), Storage::disk('local')->directories($dir));

                    // Verificar si hay archivos en la raíz
                    $hasFilesAtRoot = collect(Storage::disk('local')->files($dir))
                        ->contains(fn($f) => strtolower(pathinfo($f, PATHINFO_EXTENSION)) === 'pdf');

                    if ($hasFilesAtRoot) {
                        $clases[] = null; // Indicador de archivos en raíz
                    }

                    if (isset($estructura[$otName])) {
                        $estructura[$otName] = array_unique(array_merge($estructura[$otName], $clases));
                    } else {
                        $estructura[$otName] = $clases;
                    }
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

        // Mapeo de acciones técnicas a acciones legibles para system_logs
        $actionMap = [
            'subir_pdf' => 'Subida de Dibujo Fundición',
            'eliminar_pdf' => 'Eliminación de Dibujo Fundición',
            'reemplazar_pdf' => 'Reemplazo de Dibujo Fundición',
            'crear_carpeta' => 'Creación de Carpeta',
            'vaciar_carpeta' => 'Eliminación de Dibujo Fundición',
            'eliminar_carpeta' => 'Eliminación de Dibujo Fundición',
        ];
        $systemAction = $actionMap[$action] ?? null;
        if ($systemAction && $user) {
            $detailsMap = [
                'subir_pdf' => "El administrador subió el dibujo de fundición '{$archivo}' en la ruta {$ruta}.",
                'eliminar_pdf' => "El administrador eliminó el dibujo de fundición '{$archivo}' de la ruta {$ruta}.",
                'reemplazar_pdf' => "El administrador reemplazó el dibujo de fundición en {$ruta}: {$archivo}.",
                'crear_carpeta' => "El administrador creó la carpeta de fundición: {$ruta}.",
                'vaciar_carpeta' => "El administrador vació la carpeta de fundición: {$ruta}.",
                'eliminar_carpeta' => "El administrador eliminó la carpeta de fundición: {$ruta}.",
            ];
            \App\Models\SystemLog::create([
                'user_matricula' => $user->matricula,
                'action' => $systemAction,
                'details' => $detailsMap[$action] ?? "Administrador realizó la acción '{$action}' en {$ruta}.",
            ]);
        }
    }

    private function resolveCaseInsensitivePath(string $path): string
    {
        $parts = explode('/', str_replace('\\', '/', $path));
        $resolved = '';

        foreach ($parts as $part) {
            if ($part === '')
                continue;

            $currentSearch = $resolved ? $resolved : '.';
            if (!Storage::disk('local')->exists($currentSearch)) {
                $resolved = $resolved ? $resolved . '/' . $part : $part;
                continue;
            }

            $subdirs = Storage::disk('local')->directories($currentSearch);
            $found = false;

            $partNorm = mb_strtolower($part, 'UTF-8');
            $partNorm = str_replace(['—', '–'], '-', $partNorm);
            $partNorm = preg_replace('/\s+/', ' ', $partNorm);
            $partNorm = trim($partNorm);

            foreach ($subdirs as $subdir) {
                $base = basename($subdir);
                $baseNorm = mb_strtolower($base, 'UTF-8');
                $baseNorm = str_replace(['—', '–'], '-', $baseNorm);
                $baseNorm = preg_replace('/\s+/', ' ', $baseNorm);
                $baseNorm = trim($baseNorm);

                if ($baseNorm === $partNorm) {
                    $resolved = $resolved ? $resolved . '/' . $base : $base;
                    $found = true;
                    break;
                }
            }

            if (!$found) {
                // Si no se encuentra en las subcarpetas, intentar buscar en archivos (si es el segmento final)
                $files = Storage::disk('local')->files($currentSearch);
                foreach ($files as $file) {
                    $base = basename($file);
                    $baseNorm = mb_strtolower($base, 'UTF-8');
                    $baseNorm = str_replace(['—', '–'], '-', $baseNorm);
                    $baseNorm = preg_replace('/\s+/', ' ', $baseNorm);
                    $baseNorm = trim($baseNorm);

                    if ($baseNorm === $partNorm) {
                        $resolved = $resolved ? $resolved . '/' . $base : $base;
                        $found = true;
                        break;
                    }
                }
            }

            if (!$found) {
                $resolved = $resolved ? $resolved . '/' . $part : $part;
            }
        }

        return $resolved;
    }

    /**
     * @param mixed string $path
     */
    public static function sanitizePath(string $path): string
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

    /**
     * Normaliza el nombre de la OT para tratar consistentemente guiones largos, cortos y espacios.
     */
    public static function normalizeOTName(?string $name): string
    {
        if (!$name)
            return '';
        // Reemplazar guiones especiales y espacios de no ruptura
        $name = str_replace(['—', '–', "\xc2\xa0"], '-', $name);
        // Todo a mayúsculas para evitar problemas de case-sensitivity
        $name = mb_strtoupper($name, 'UTF-8');
        // Eliminar espacios múltiples
        $name = preg_replace('/\s+/', ' ', $name);
        return trim($name);
    }
}