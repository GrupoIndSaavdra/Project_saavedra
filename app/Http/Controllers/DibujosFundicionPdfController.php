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
        $todasLasOTs = Orden_trabajo::with('moldura')
            ->whereHas('clases', fn($q) => $q->where('finalizada', '=', 0, 'and'))
            ->orderBy('id', 'asc')
            ->get();

        $otSeleccionadaId = $request->query('ot_id');
        $claseSeleccionadaId = $request->query('clase_id');

        $otActiva = $otSeleccionadaId ? $todasLasOTs->first(fn($ot) => (int)$ot->id === (int)$otSeleccionadaId) : null;
        $claseActiva = null;
        if ($claseSeleccionadaId) {
            if (is_numeric($claseSeleccionadaId)) {
                $claseActiva = optional($otActiva?->clases)->first(fn($c) => (int)$c->id === (int)$claseSeleccionadaId);
            } elseif (in_array($claseSeleccionadaId, ['Pistones', 'Guías', 'Guias'])) {
                // Clase virtual para ayudas manuales
                $claseActiva = (object)[
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
                // La estructura correcta es {Base}/{Clase}/Fundicion
                $fundicionDir = $base . '/' . $clase . '/Fundicion';

                if (Storage::disk('local')->exists($fundicionDir)) {
                    $files = Storage::disk('local')->files($fundicionDir);
                    $hasPdf = collect($files)->contains(fn($f) => strtolower(pathinfo($f, PATHINFO_EXTENSION)) === 'pdf');
                    if ($hasPdf) {
                        $ayudasDisponibles[] = $clase;
                        break;
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
                    $claseDir = $base . '/' . $clase . '/Fundicion';
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
        if (empty($ot)) return response()->json(['total' => 0]);

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
                'DOCUMENTACION_GIS/AYUDAS_FUNDICION/' . $aName . '/Fundicion',
                'AYUDAS_GIS/' . $aName . '/Fundicion'
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
                $nombre = basename($f);
                $fullPath = $f;

                // Determinar si el archivo está en la raíz o en una clase para generar la URL de servicio correcta
                $esRaiz = (strpos($fullPath, $newRootPath . '/' . $nombre) !== false || strpos($fullPath, $oldRootPath . '/' . $nombre) !== false);

                return [
                    'nombre' => $nombre,
                    'url' => route('fundicion.serve', [
                        'ot' => $ot,
                        'clase' => $esRaiz ? '--' : $clase,
                        'archivo' => $nombre,
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

        // Si la clase es '--', buscamos en la raíz de la OT
        if ($clase === '--' || empty($clase)) {
            $filePath = self::BASE_DIR . '/' . $ot . '/' . $archivo;
            if (!Storage::disk('local')->exists($filePath)) {
                $filePath = self::OLD_BASE_DIR . '/' . $ot . '/' . $archivo;
            }
        } else {
            $filePath = self::BASE_DIR . '/' . $ot . '/' . $clase . '/' . $archivo;
            if (!Storage::disk('local')->exists($filePath)) {
                $filePath = self::OLD_BASE_DIR . '/' . $ot . '/' . $clase . '/' . $archivo;
            }
        }

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

    /**
     * @param \Illuminate\Http\Request Request $request
     */
    public function createFolder(Request $request)
    {
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
    private function copyToAlmacen(string $otName): void
    {
        $srcDir = self::BASE_DIR . '/' . $otName;
        $dstDir = self::ALMACEN_DIR . '/' . $otName;

        // 1. Sincronizar dibujos por clase (Recursivo)
        if (Storage::disk('local')->exists($srcDir)) {
            if (!Storage::disk('local')->exists($dstDir)) {
                Storage::disk('local')->makeDirectory($dstDir);
            }

            // Obtener todos los PDFs en subcarpetas (clases)
            $srcFilesFull = collect(Storage::disk('local')->allFiles($srcDir))
                ->filter(fn($f) => strtolower(pathinfo($f, PATHINFO_EXTENSION)) === 'pdf');

            // Mapear rutas relativas al directorio de la OT
            $srcFilesRel = $srcFilesFull->map(fn($f) => str_replace($srcDir . '/', '', $f))->toArray();

            // Obtener archivos actuales en Almacén (excluyendo ayudas_visuales)
            $dstFilesFull = collect(Storage::disk('local')->allFiles($dstDir))
                ->filter(function ($f) use ($dstDir) {
                    $rel = str_replace($dstDir . '/', '', $f);
                    return strpos($rel, 'ayudas_visuales/') !== 0 && strtolower(pathinfo($f, PATHINFO_EXTENSION)) === 'pdf';
                });

            $dstFilesRel = $dstFilesFull->map(fn($f) => str_replace($dstDir . '/', '', $f))->toArray();

            // Eliminar huérfanos
            foreach ($dstFilesRel as $dfRel) {
                if (!in_array($dfRel, $srcFilesRel)) {
                    Storage::disk('local')->delete($dstDir . '/' . $dfRel);
                }
            }

            // Copiar nuevos o actualizados (con sus subcarpetas)
            foreach ($srcFilesRel as $sfRel) {
                $srcPath = $srcDir . '/' . $sfRel;
                $dstPath = $dstDir . '/' . $sfRel;

                // Asegurar que existe el directorio destino de la clase
                $parentDir = dirname($dstPath);
                if (!Storage::disk('local')->exists($parentDir)) {
                    Storage::disk('local')->makeDirectory($parentDir);
                }

                if (!Storage::disk('local')->exists($dstPath)) {
                    Storage::disk('local')->copy($srcPath, $dstPath);
                }
            }
        }

        // 2. Copiar y Sincronizar Ayudas Visuales (Mirroring)
        $history = FundicionHistory::where('ot', '=', $otName, 'and')->first();
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
                $bases = [$newBase, $oldBase];
                foreach ($bases as $base) {
                    $srcClaseDir = $base . '/' . $clase . '/Fundicion';
                    if (Storage::disk('local')->exists($srcClaseDir)) {
                        $fList = Storage::disk('local')->files($srcClaseDir);
                        foreach ($fList as $f) {
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
            $almacenFiles = collect(Storage::disk('local')->allFiles($dstDir))
                ->filter(function ($f) use ($dstDir) {
                    $rel = str_replace($dstDir . '/', '', $f);
                    return strpos($rel, 'ayudas_visuales/') !== 0 && strtolower(pathinfo($f, PATHINFO_EXTENSION)) === 'pdf';
                })
                ->map(fn($f) => str_replace($dstDir . '/', '', $f))
                ->values()
                ->toArray();
        }

        FundicionHistory::updateOrCreate(
            ['ot' => $otName],
            [
                'status' => 'activa',
                'alert_sent_at' => now(), // Asegurar que aparezca en Almacén inmediatamente
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
            'clase' => 'nullable|string|max:100',
            'archivo' => 'required|string|max:300',
        ]);

        $ot = $this->sanitizePath($request->input('ot'));
        $clase = $this->sanitizePath($request->input('clase'));
        $archivo = $this->sanitizeFileName($request->input('archivo'));
        $filePath = ($clase === '--')
            ? self::BASE_DIR . '/' . $ot . '/' . $archivo
            : self::BASE_DIR . '/' . $ot . '/' . $clase . '/' . $archivo;

        if (!Storage::disk('local')->exists($filePath)) {
            $oldPath = ($clase === '--')
                ? self::OLD_BASE_DIR . '/' . $ot . '/' . $archivo
                : self::OLD_BASE_DIR . '/' . $ot . '/' . $clase . '/' . $archivo;
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
        $this->logAction('eliminar_pdf', $ot . '/' . $clase, $archivo);

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
        $clase = $this->sanitizePath($request->input('clase'));
        $dirPath = self::BASE_DIR . '/' . $ot . '/' . $clase;

        if (!Storage::disk('local')->exists($dirPath)) {
            return response()->json(['success' => false, 'message' => 'La carpeta no existe.'], 404);
        }

        $files = Storage::disk('local')->files($dirPath);
        if (count($files) > 0) {
            Storage::disk('local')->delete($files);
            $this->logAction('vaciar_carpeta', $ot . '/' . $clase, null);
            return response()->json([
                'success' => true,
                'message' => "Se eliminaron " . count($files) . " archivos de la clase '{$clase}'.",
            ]);
        }

        Storage::disk('local')->deleteDirectory($dirPath);
        $this->logAction('eliminar_carpeta', $ot . '/' . $clase, 'Eliminación de Clase');

        // Sincronizar con histórico (eliminar vinculación si existe)
        $otNorm = $this->normalizeOTName($ot);
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
        $dirPath = self::BASE_DIR . '/' . $ot;

        if (!Storage::disk('local')->exists($dirPath)) {
            return response()->json(['success' => false, 'message' => 'La carpeta no existe.'], 404);
        }

        $subDirs = Storage::disk('local')->directories($dirPath);
        $files = Storage::disk('local')->files($dirPath);

        if (count($subDirs) > 0 || count($files) > 0) {
            return response()->json([
                'success' => false,
                'message' => 'No se puede eliminar: la carpeta todavía contiene clases o archivos.',
            ], 400);
        }

        Storage::disk('local')->deleteDirectory($dirPath);
        $this->logAction('eliminar_carpeta', $ot, 'Eliminación de Directorio Raíz OT');

        // Desactivar en histórico si ya no existe carpeta física
        FundicionHistory::where('ot', '=', $ot, 'and')->update(['status' => 'inactiva']);

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
        $clase = $this->sanitizePath($request->input('clase'));
        $archivoAnterior = $this->sanitizeFileName($request->input('archivo_anterior'));
        $dirPath = ($clase === '--') ? self::BASE_DIR . '/' . $ot : self::BASE_DIR . '/' . $ot . '/' . $clase;
        $oldPath = $dirPath . '/' . $archivoAnterior;

        if (Storage::disk('local')->exists($oldPath)) {
            Storage::disk('local')->delete($oldPath);
        }

        $file = $request->file('pdf');
        $cleanName = $this->sanitizeFileName($file->getClientOriginalName());

        // Prefijar con el nombre de la clase si no es raíz
        $newName = ($clase !== '--') ?
            ((strpos($cleanName, $clase . " - ") === 0) ? $cleanName : $clase . " - " . $cleanName) :
            $cleanName;

        $file->storeAs($dirPath, $newName, 'local');

        $this->logAction('reemplazar_pdf', $ot . '/' . $clase, "{$archivoAnterior} → {$newName}");

        return response()->json([
            'success' => true,
            'message' => "PDF reemplazado correctamente por '{$newName}'.",
            'nombre' => $newName,
            'url' => route('fundicion.serve', [
                'ot' => $ot,
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
        $ayudasFiltradas = array_filter($ayudasPrevias, function($a) use ($ayudasManualesPosibles, $nuevasAyudasManuales) {
            if (in_array($a, $ayudasManualesPosibles)) {
                return in_array($a, $nuevasAyudasManuales);
            }
            return !empty($a);
        });
        
        // Limpiar nulos o basura antes de guardar
        $ayudasFinales = array_values(array_filter(array_unique(array_merge($ayudasFiltradas, $nuevasAyudasManuales)), function($v) {
            $val = trim(strtolower((string)$v));
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
        $bases = [self::BASE_DIR, self::OLD_BASE_DIR];

        foreach ($bases as $base) {
            if (Storage::disk('local')->exists($base)) {
                $otDirs = Storage::disk('local')->directories($base);
                foreach ($otDirs as $dir) {
                    $otNameRaw = basename($dir);
                    $otName = $this->normalizeOTName($otNameRaw);
                    $clases = array_map('basename', Storage::disk('local')->directories($dir));

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

    /**
     * Normaliza el nombre de la OT para tratar consistentemente guiones largos, cortos y espacios.
     */
    private function normalizeOTName(?string $name): string
    {
        if (!$name) return '';
        // Reemplazar guiones especiales y espacios de no ruptura
        $name = str_replace(['—', '–', "\xc2\xa0"], '-', $name);
        // Todo a mayúsculas para evitar problemas de case-sensitivity
        $name = mb_strtoupper($name, 'UTF-8');
        // Estandarizar guiones (asegurar espacio alrededor si parece ser el separador principal)
        $name = preg_replace('/\s*-\s*/', ' - ', $name);
        // Eliminar espacios múltiples
        $name = preg_replace('/\s+/', ' ', $name);
        return trim($name);
    }
}