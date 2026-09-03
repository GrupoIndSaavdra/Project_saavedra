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
    public const BASE_DIR = 'DOCUMENTACION_GIS/DIBUJOS_FUNDICION';

    /**
     * Directorio legado para compatibilidad con archivos anteriores.
     */
    public const OLD_BASE_DIR = 'FUNDICION_GIS';

    /**
     * NUNCA se escanea desde la vista admin y NUNCA se borra con deleteFolder.
     */
    private const ALMACEN_DIR = 'DOCUMENTACION_GIS/ALMACEN_FUNDICION';
    private const CALIDAD_DIR = 'DOCUMENTACION_GIS/CALIDAD_FUNDICION';

    // =========================================================================
    // VISTAS
    // =========================================================================

    /**
     * @param Request $request
     */
    public function showManage(Request $request)
    {
        // Estructura del filesystem
        $estructura = $this->buildStructure();

        // OTs activas (que tienen al menos una clase NO finalizada)
        try {
            $todasLasOTs = Orden_trabajo::with(['moldura', 'clases.procesos'])
                ->whereHas('clases', fn($q) => $q->where('finalizada', '=', 0))
                ->orderBy('id', 'asc')
                ->get();
        } catch (\Throwable $dbe) {
            Log::warning('Error DB en showManage (Orden_trabajo): ' . $dbe->getMessage());
            $todasLasOTs = collect();
        }

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
                    $absPath = Storage::disk('local')->path($dir);
                    $pdfs = glob($absPath . '/*.{pdf,PDF}', GLOB_BRACE);
                    if ($pdfs && count($pdfs) > 0) {
                        $ayudasDisponibles[] = $clase;
                        break 2;
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

            try {
                $history = FundicionHistory::where('ot', '=', $otFolderName, 'and')->first();
                $ayudasSeleccionadas = $history ? ($history->ayudas_config ?? []) : [];
            } catch (\Throwable $dbe) {
                Log::warning('Error DB en showManage (history): ' . $dbe->getMessage());
            }

            // 1. Verificar dibujos principales
            $newPath = self::BASE_DIR . '/' . $otFolderName;
            $oldPath = self::OLD_BASE_DIR . '/' . $otFolderName;
            $newAbsPath = Storage::disk('local')->path($newPath);
            $oldAbsPath = Storage::disk('local')->path($oldPath);
            $newPdfs = glob($newAbsPath . '/*.{pdf,PDF}', GLOB_BRACE) ?: [];
            $oldPdfs = glob($oldAbsPath . '/*.{pdf,PDF}', GLOB_BRACE) ?: [];
            $hasDibujos = count($newPdfs) > 0 || count($oldPdfs) > 0;

            foreach ($ayudasDisponibles as $clase) {
                // REQUERIMIENTO: Solo Pistones y Guias aparecen en el panel manual
                $esManualHardcoded = in_array($clase, ['Pistones', 'Guias', 'Guías']);
                if (!$esManualHardcoded)
                    continue;

                $isNew = true;
                $masterFiles = [];
                $bases = [$newBase, $oldBase];
                foreach ($bases as $base) {
                    $claseAbsPath = Storage::disk('local')->path($base . '/' . $clase);
                    $pdfs = glob($claseAbsPath . '/*.{pdf,PDF}', GLOB_BRACE);
                    if ($pdfs) {
                        foreach ($pdfs as $pdf) {
                            $masterFiles[] = basename($pdf);
                        }
                    }
                }

                $almacenAyudasBase = self::ALMACEN_DIR . '/' . $otFolderName . '/ayudas_visuales';
                $almacenAbsPath = Storage::disk('local')->path($almacenAyudasBase . '/' . $clase);
                $almacenFiles = [];
                $pdfs = glob($almacenAbsPath . '/*.{pdf,PDF}', GLOB_BRACE);
                if ($pdfs) {
                    foreach ($pdfs as $pdf) {
                        $almacenFiles[] = basename($pdf);
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

        try {
            $todasLasClases = Clase::all();
            $historialesRaw = FundicionHistory::all();
            // Cargar PreOrdenes enviadas agrupadas por OT para verificación multi-nivel
            $preOrdenesEnviadasPorOT = \App\Models\PreOrdenFundicion::where('is_sent', 1)
                ->get(['ot', 'filas'])
                ->groupBy('ot');
            // Cargar liberaciones de modelo agrupadas por OT
            $liberacionesPorOT = \App\Models\LiberacionModeloFundicion::whereNotNull('tipo_modelo')
                ->where('tipo_modelo', '!=', '')
                ->get(['ot', 'tipo_modelo', 'tipo_origen', 'created_at'])
                ->groupBy('ot');
        } catch (\Throwable $dbe) {
            Log::warning('Error DB en showManage (Clase/FundicionHistory): ' . $dbe->getMessage());
            $todasLasClases = collect();
            $historialesRaw = collect();
            $preOrdenesEnviadasPorOT = collect();
            $liberacionesPorOT = collect();
        }
        $historiales = [];
        $alertasEnviadas = [];

        /**
         * Determina si una clase de una OT ya tiene actividad en producción (preorden/casting/liberación).
         * Esto se usa para bloquear el botón de reenvío si no hay cambios reales en archivos.
         */
        $claseYaEnProduccion = function(string $otName, string $claseNombre) use ($preOrdenesEnviadasPorOT, $liberacionesPorOT): bool {
            // 1. Verificar si hay preorden enviada que incluya esta clase
            $preOrdenesPorEstaOT = $preOrdenesEnviadasPorOT->get($otName, collect());
            foreach ($preOrdenesPorEstaOT as $po) {
                $filas = $po->filas;
                if (is_array($filas)) {
                    foreach ($filas as $f) {
                        $claseEnFila = $f['clase'] ?? $f['clase_nombre'] ?? null;
                        if ($claseEnFila && strtolower(trim($claseEnFila)) === strtolower(trim($claseNombre))) {
                            return true;
                        }
                    }
                }
            }
            // 2. Verificar si hay liberación de modelo registrada para esta clase
            $liberacionesPorEstaOT = $liberacionesPorOT->get($otName, collect());
            foreach ($liberacionesPorEstaOT as $lib) {
                if (strtolower(trim($lib->tipo_modelo)) === strtolower(trim($claseNombre))) {
                    return true;
                }
            }
            return false;
        };

        /**
         * Determina el estado final de una clase basado en:
         * 1. Hash de archivos físicos vs hash guardado en FundicionHistory::clases_enviadas
         * 2. Si tiene actividad en preorden/liberación (producción activa) y no hay archivos nuevos
         * 3. Si tiene procesos en la tabla `clases.procesos` con fecha_inicio
         */
        $calcularEstado = function(
            string $otRaw,        // Nombre real de la OT (para buscar en disco)
            string $clase,        // Nombre en MAYÚSCULAS (del historial, para buscar carpeta física)
            string $claseKey,     // Nombre Title Case (de BD, para mostrar en UI)
            ?string $storedHash,  // Hash guardado en clases_enviadas (null = nunca enviado)
            ?object $claseFisica, // Objeto Clase de BD (con procesos, fecha_inicio)
            ?object $historial,   // Objeto FundicionHistory completo
        ) use ($claseYaEnProduccion): string {

            // Calcular hash actual de los archivos en disco
            $currentHash = self::computeClassHash($otRaw, $clase);

            // Sin archivos → vacío
            if ($currentHash === "") {
                return 'vacio';
            }

            // CASO 1: Ya tiene historial de envío (clases_enviadas tiene esta clase)
            if ($storedHash !== null) {
                if ($storedHash === $currentHash || $storedHash === "") {
                    return 'enviada'; // Sin cambios desde el último envío
                }
                return 'modificada'; // Los archivos cambiaron → permitir reenvío
            }

            // CASO 2: Sin historial de envío (clases_enviadas es null para esta clase)
            // Verificar si ya entró en producción por alguna de las 3 fuentes:

            // 2a. FundicionHistory flags: pre_orden_sent o casting_pdf_generated
            $enProduccionPorFlags = $historial && ($historial->pre_orden_sent || $historial->casting_pdf_generated);

            // 2b. Preórdenes enviadas o liberaciones en la BD
            $enProduccionPorTablas = $claseYaEnProduccion($otRaw, $clase);

            // 2c. Tabla procesos (registro directo de maquinado)
            $enProduccionPorProcesos = $claseFisica && $claseFisica->procesos;

            $enProduccion = $enProduccionPorFlags || $enProduccionPorTablas || $enProduccionPorProcesos;

            if ($enProduccion) {
                // Calcular límite de tiempo para isFileModifiedAfter
                // Usar la fecha más temprana disponible: fecha_inicio de procesos o created_at del historial
                $limitTime = null;
                if ($claseFisica && $claseFisica->fecha_inicio) {
                    $limitTime = strtotime($claseFisica->fecha_inicio . ' ' . ($claseFisica->hora_inicio ?: '00:00:00'));
                } elseif ($historial && $historial->created_at) {
                    $limitTime = $historial->created_at->timestamp;
                }

                if ($limitTime) {
                    $hasNewFiles = self::isFileModifiedAfter($otRaw, $clase, $limitTime);
                    if ($hasNewFiles) {
                        return 'modificada'; // Archivos subidos después de que empezó la producción → permitir envío
                    }
                }
                return 'enviada'; // En producción sin archivos nuevos → bloquear
            }

            return 'pendiente'; // Sin producción y sin envío → permitir envío
        };

        // Pre-procesar estructura keys para búsqueda rápida
        $estructuraOTs = array_map(function ($ot) {
            return self::normalizeOTName($ot); }, array_keys($estructura));

        foreach ($historialesRaw as $h) {
            $normName = $this->normalizeOTName($h->ot);

            // Ignorar historiales de OTs que ya no están activas en la estructura física
            if (!in_array($normName, $estructuraOTs)) {
                continue;
            }

            if (!isset($historiales[$normName])) {
                $historiales[$normName] = $h->ayudas_config ?? [];
            } else {
                $historiales[$normName] = array_unique(array_merge($historiales[$normName], ($h->ayudas_config ?? [])));
            }

            $enviadas = is_array($h->clases_enviadas) ? $h->clases_enviadas : [];
            $enviadasDict = [];
            foreach ($enviadas as $key => $val) {
                if (is_numeric($key)) {
                    $enviadasDict[$val] = "";
                } else {
                    $enviadasDict[$key] = $val;
                }
            }
            if (!isset($alertasEnviadas[$normName])) {
                $alertasEnviadas[$normName] = [];
            }

            // Calcular estado para cada clase vinculada al historial
            $vinculadas = $h->ayudas_config ?? [];
            foreach ($vinculadas as $clase) {
                // Buscar el nombre canónico de la BD (Title Case) para display
                $otMatch = $todasLasOTs->first(function($otM) use ($normName) {
                    $label1 = self::normalizeOTName("OT " . $otM->id . ($otM->moldura ? " - " . $otM->moldura->nombre : ""));
                    $label2 = self::normalizeOTName("OT " . $otM->id);
                    return $label1 === $normName || $label2 === $normName;
                });
                $claseFisica = $otMatch ? $otMatch->clases->first(fn($c) => strtolower(trim($c->nombre)) === strtolower(trim($clase))) : null;
                $claseKey = $claseFisica ? $claseFisica->nombre : $clase;

                // Buscar storedHash de forma case-insensitive
                $storedHash = null;
                foreach ($enviadasDict as $k => $v) {
                    if (strtolower(trim($k)) === strtolower(trim($clase))) {
                        $storedHash = $v;
                        break;
                    }
                }

                $st = $calcularEstado(
                    $h->ot, $clase, $claseKey, $storedHash, $claseFisica, $h
                );
                $alertasEnviadas[$normName][$claseKey] = $st;
                $alertasEnviadas[$normName][strtoupper($claseKey)] = $st;
                $alertasEnviadas[$normName][$clase] = $st;
            }
        }

        // Procesar TODAS las OTs de la BD para detectar clases que no tienen historial aún
        foreach ($todasLasOTs as $ot) {
            $fullOtName = "OT " . $ot->id . ($ot->moldura ? " - " . $ot->moldura->nombre : "");
            $normName = self::normalizeOTName($fullOtName);
            // Buscar el historial para esta OT (para pasarle los flags)
            $historialDeEstaOT = $historialesRaw->first(fn($hh) => self::normalizeOTName($hh->ot) === $normName);

            if (!isset($alertasEnviadas[$normName])) {
                $alertasEnviadas[$normName] = [];
            }
            if ($ot->clases) {
                foreach ($ot->clases as $claseObj) {
                    $claseKey = $claseObj->nombre;
                    // Saltar si ya fue calculada (case-insensitive)
                    $existingKey = null;
                    foreach (array_keys($alertasEnviadas[$normName]) as $k) {
                        if (strtolower(trim($k)) === strtolower(trim($claseKey))) {
                            $existingKey = $k;
                            break;
                        }
                    }
                    if ($existingKey) {
                        continue;
                    }

                    // Clase no procesada aún → calcular estado
                    // Usar nombre en mayúsculas para buscar carpeta en disco
                    $claseUppercase = strtoupper($claseKey);
                    $st = $calcularEstado(
                        $fullOtName, $claseUppercase, $claseKey, null, $claseObj, $historialDeEstaOT
                    );
                    $alertasEnviadas[$normName][$claseKey] = $st;
                    $alertasEnviadas[$normName][strtoupper($claseKey)] = $st;
                    $alertasEnviadas[$normName][$claseUppercase] = $st;
                }
            }
        }

        return view('wo_views.manage_fundicion', array_merge(compact(
            'estructura',
            'todasLasOTs',
            'todasLasClases',
            'otSeleccionadaId',
            'otActiva',
            'ayudasConEstado',
            'historiales',
            'alertasEnviadas',
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
     * @param Request $request
     */
    public function getTotalFiles(Request $request)
    {
        try {
            $ot = $this->sanitizePath($request->query('ot', ''));
            if (empty($ot))
                return response()->json(['total' => 0]);

            $otNorm = $this->normalizeOTName($ot);
            $total = 0;

            // 1. Dibujos en directorio base
            $baseDir = self::BASE_DIR . '/' . $otNorm;
            if (Storage::disk('local')->exists($baseDir)) {
                $total += collect(Storage::disk('local')->allFiles($baseDir))
                    ->filter(fn($f) => in_array(strtolower(pathinfo($f, PATHINFO_EXTENSION)), ['pdf', 'dwg']))->count();
            }

            // 2. Dibujos en directorio legado
            $oldBaseDir = self::OLD_BASE_DIR . '/' . $otNorm;
            if (Storage::disk('local')->exists($oldBaseDir)) {
                $total += collect(Storage::disk('local')->allFiles($oldBaseDir))
                    ->filter(fn($f) => in_array(strtolower(pathinfo($f, PATHINFO_EXTENSION)), ['pdf', 'dwg']))->count();
            }

            // 3. Ayudas Visuales vinculadas
            $ayudas = [];
            try {
                $history = FundicionHistory::where('ot', '=', $otNorm, 'and')->first();
                $ayudas = $history ? ($history->ayudas_config ?? []) : [];
            } catch (\Throwable $dbe) {
                Log::warning('Error DB en getTotalFiles (ayudas): ' . $dbe->getMessage());
            }

            if (is_array($ayudas)) {
                $newBase = 'DOCUMENTACION_GIS/AYUDAS_FUNDICION';
                $oldBase = 'AYUDAS_GIS';

                foreach ($ayudas as $aName) {
                    $candidates = [
                        $newBase . '/' . $aName,
                        $oldBase . '/' . $aName,
                        $oldBase . '/' . $aName . '/Fundicion'
                    ];

                    foreach ($candidates as $ayudaDir) {
                        if (Storage::disk('local')->exists($ayudaDir)) {
                            $total += collect(Storage::disk('local')->files($ayudaDir))
                                ->filter(fn($f) => in_array(strtolower(pathinfo($f, PATHINFO_EXTENSION)), ['pdf', 'dwg']))->count();
                        }
                    }
                }
            }

            return response()->json(['total' => $total]);
        } catch (\Throwable $e) {
            Log::error('Error en DibujosFundicionPdfController@getTotalFiles: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            $errorMsg = $e->getMessage();
            if (function_exists('mb_convert_encoding')) {
                $errorMsg = @mb_convert_encoding($errorMsg, 'UTF-8', 'UTF-8');
            }
            return response()->json([
                'success' => false,
                'total' => 0,
                'error' => $errorMsg
            ], 200);
        }
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
     * @param Request $request
     */
    public function getFiles(Request $request)
    {
        try {
            $rawOt = $request->query('ot', '');
            $clase = $this->sanitizePath($request->query('clase', ''));

            if (empty($rawOt)) {
                return response()->json(['error' => 'Parámetro OT es requerido.'], 422);
            }

            if ($clase === 'null' || $clase === '--')
                $clase = '';

            $ot = $this->normalizeOTName($this->sanitizePath($rawOt));
            if (is_numeric($rawOt)) {
                try {
                    $otModel = Orden_trabajo::query()->with('moldura')->find($rawOt);
                    if ($otModel) {
                        $otLabel = "OT " . $otModel->id . ($otModel->moldura ? " - " . $otModel->moldura->nombre : "");
                        $ot = $this->normalizeOTName($this->sanitizePath($otLabel));
                    }
                } catch (\Throwable $dbe) {
                    Log::warning("Error DB en DibujosFundicionPdfController@getFiles: " . $dbe->getMessage());
                }
            }

            // 1. Directorios de Clase (Nuevo esquema insensible a mayúsculas/minúsculas)
            $newClasePathRel = $this->resolveCaseInsensitivePath(self::BASE_DIR . '/' . $ot . '/' . $clase);
            $oldClasePathRel = $this->resolveCaseInsensitivePath(self::OLD_BASE_DIR . '/' . $ot . '/' . $clase);

            // 2. Directorios Raíz (Esquema anterior/legado)
            $newRootPathRel = $this->resolveCaseInsensitivePath(self::BASE_DIR . '/' . $ot);
            $oldRootPathRel = $this->resolveCaseInsensitivePath(self::OLD_BASE_DIR . '/' . $ot);

            $newClasePath = Storage::disk('local')->path($newClasePathRel);
            $oldClasePath = Storage::disk('local')->path($oldClasePathRel);
            $newRootPath = Storage::disk('local')->path($newRootPathRel);
            $oldRootPath = Storage::disk('local')->path($oldRootPathRel);

            $files = [];

            // --- Buscar en Clase seleccionada ---
            if (!empty($clase)) {
                $files = array_merge($files, glob($newClasePath . '/*.{pdf,PDF,dwg,DWG}', GLOB_BRACE) ?: []);
                $files = array_merge($files, glob($oldClasePath . '/*.{pdf,PDF,dwg,DWG}', GLOB_BRACE) ?: []);
            }

            // --- Buscar en Raíz de la OT (Archivos que no tienen clase aún) ---
            $rootFilesNew = glob($newRootPath . '/*.{pdf,PDF,dwg,DWG}', GLOB_BRACE) ?: [];
            $rootFilesOld = glob($oldRootPath . '/*.{pdf,PDF,dwg,DWG}', GLOB_BRACE) ?: [];

            $files = array_merge($files, $rootFilesNew, $rootFilesOld);

            $allFiles = collect($files)
                ->map(function ($f) use ($ot, $clase, $newRootPath, $oldRootPath) {
                    $rawName = basename($f);
                    $utf8Name = $this->toUtf8($rawName);
                    $fullPath = $f;

                    $dir = dirname($fullPath);
                    $isRoot = ($dir === $newRootPath || $dir === $oldRootPath);

                    $serveClase = $isRoot ? '' : $clase;

                    return [
                        'nombre' => $utf8Name,
                        'url' => url('/fundicion/serve') . '?ot=' . urlencode($ot) . '&clase=' . urlencode($serveClase) . '&archivo=' . urlencode($utf8Name),
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
        } catch (\Throwable $e) {
            Log::error('Error en DibujosFundicionPdfController@getFiles: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
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
        $rawOt = $request->query('ot', '');
        $clase = $this->sanitizePath($request->query('clase', ''));
        $rawArchivo = $request->query('archivo', '');

        if (empty($clase) && str_contains($rawArchivo, '/')) {
            $parts = explode('/', $rawArchivo, 2);
            $clase = $this->sanitizePath($parts[0]);
            $archivo = $this->sanitizeFileName($parts[1]);
        } else {
            $archivo = $this->sanitizeFileName($rawArchivo);
        }

        if (empty($rawOt) || empty($archivo)) {
            abort(422, 'Parámetros inválidos.');
        }

        $ot = $this->normalizeOTName($this->sanitizePath($rawOt));
        if (is_numeric($rawOt)) {
            try {
                $otModel = Orden_trabajo::query()->with('moldura')->find($rawOt);
                if ($otModel) {
                    $otLabel = "OT " . $otModel->id . ($otModel->moldura ? " - " . $otModel->moldura->nombre : "");
                    $ot = $this->normalizeOTName($this->sanitizePath($otLabel));
                }
            } catch (\Throwable $dbe) {
                Log::warning("Error DB en DibujosFundicionPdfController@serveFile: " . $dbe->getMessage());
            }
        }

        if ($clase === '--' || empty($clase)) {
            $candidateDirs = [
                self::BASE_DIR . '/' . $ot,
                self::OLD_BASE_DIR . '/' . $ot
            ];
        } else {
            $candidateDirs = [
                self::BASE_DIR . '/' . $ot . '/' . $clase,
                self::OLD_BASE_DIR . '/' . $ot . '/' . $clase,
                self::BASE_DIR . '/' . $ot,
                self::OLD_BASE_DIR . '/' . $ot
            ];
        }

        $foundFile = null;
        $archivoNorm = Normalizer::normalize(mb_strtolower($archivo, 'UTF-8'), Normalizer::FORM_C);

        foreach ($candidateDirs as $cand) {
            $resolvedDir = $this->resolveCaseInsensitivePath($cand);
            if (Storage::disk('local')->exists($resolvedDir)) {
                $files = Storage::disk('local')->files($resolvedDir);
                foreach ($files as $f) {
                    $rawName = basename($f);
                    $utf8Name = $this->toUtf8($rawName);
                    $utf8NameNorm = Normalizer::normalize(mb_strtolower($utf8Name, 'UTF-8'), Normalizer::FORM_C);

                    if ($utf8NameNorm === $archivoNorm) {
                        $foundFile = $f;
                        break 2;
                    }
                }
            }
        }

        if (!$foundFile) {
            Log::warning("Archivo no encontrado en Fundicion (Dibujos). OT: {$ot}, Archivo buscado: {$archivo}, Clase: {$clase}");
            abort(404, 'Archivo no encontrado.');
        }

        /** @var \Illuminate\Filesystem\FilesystemAdapter $disk */
        $disk = Storage::disk('local');
        $fullPath = $disk->path($foundFile);

        $ext = strtolower(pathinfo($fullPath, PATHINFO_EXTENSION));
        $mimeType = $ext === 'pdf' ? 'application/pdf' : 'application/octet-stream';
        $disposition = $ext === 'pdf' ? 'inline' : 'attachment';

        return response()->file($fullPath, [
            'Content-Type' => $mimeType,
            'Content-Disposition' => $disposition . '; filename="' . basename($archivo) . '"',
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
                'ot_id' => 'required|exists:orden_trabajo,id',
                'clase' => 'required|string|max:100',
            ]);

            $otId = $request->input('ot_id');
            $clase = $this->sanitizePath($request->input('clase'));

            $otModel = Orden_trabajo::query()->with('moldura')->findOrFail($otId);
            $otFolderName = "OT " . $otModel->id . ($otModel->moldura ? " - " . $otModel->moldura->nombre : "");
            $otFolderName = $this->normalizeOTName($this->sanitizePath($otFolderName));

            $claseClean = strtoupper(trim(preg_replace('/^modelo\s+/i', '', strtolower($clase))));
            if (empty($claseClean)) $claseClean = 'GENERAL';

            if ($clase === '--') {
                $dirPath = self::BASE_DIR . '/' . $otFolderName;
            } else {
                $dirPath = self::BASE_DIR . '/' . $otFolderName . '/' . $claseClean;
            }

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
            if ($clase !== '--' && !in_array($claseClean, $ayudas)) {
                $ayudas[] = $claseClean;
                $history->ayudas_config = $ayudas;
                $history->save();
                $this->copyToAlmacen($otFolderName); // Sincronizar inmediatamente
            }

            $this->logAction('crear_carpeta', $otFolderName . '/' . $claseClean, "Creación de Clase con Vinculación Automática");

            return response()->json([
                'success' => true,
                'message' => "Carpeta {$otFolderName}/{$claseClean} creada correctamente.",
                'ot' => $otFolderName,
                'clase' => $claseClean,
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
     * @param Request $request
     */
    public function uploadPdf(Request $request)
    {
        $request->validate([
            'ot_id' => 'required|exists:orden_trabajo,id',
            'clase' => 'nullable|string|max:100',
            'pdf' => 'required|file|mimes:pdf,dwg',
        ]);

        $otId = $request->input('ot_id');
        $clase = $this->sanitizePath($request->input('clase'));

        $otModel = Orden_trabajo::query()->with('moldura')->findOrFail($otId);
        $otFolderName = "OT " . $otModel->id . ($otModel->moldura ? " - " . $otModel->moldura->nombre : "");
        $otFolderName = $this->normalizeOTName($this->sanitizePath($otFolderName));

        $claseClean = strtoupper(trim(preg_replace('/^modelo\s+/i', '', strtolower($clase))));
        if (empty($claseClean)) $claseClean = 'GENERAL';

        if ($clase === '--') {
            $dirPath = self::BASE_DIR . '/' . $otFolderName;
        } else {
            $dirPath = self::BASE_DIR . '/' . $otFolderName . '/' . $claseClean;
        }

        if (!Storage::disk('local')->exists($dirPath)) {
            Storage::disk('local')->makeDirectory($dirPath);
        }

        $history = FundicionHistory::firstOrCreate(['ot' => $otFolderName]);

        // VINCULACIÓN AUTOMÁTICA: Si se sube a una clase, vincularla
        $ayudas = $history->ayudas_config ?? [];
        if (!empty($clase) && $clase !== '--' && !in_array($claseClean, $ayudas)) {
            $ayudas[] = $claseClean;
            $history->ayudas_config = $ayudas;
            $history->save();
            $this->copyToAlmacen($otFolderName);
        }

        $file = $request->file('pdf');
        $cleanName = $this->sanitizeFileName($file->getClientOriginalName());

        // Prefijar con el nombre de la clase si no lo tiene ya y si no es nula
        $newName = $cleanName;
        if (!empty($clase) && $clase !== '--') {
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
            'url' => url('/fundicion/serve') . '?ot=' . urlencode($otFolderName) . '&clase=' . urlencode($clase) . '&archivo=' . urlencode($newName),
            'ot' => $otFolderName,
            'clase' => $clase,
        ]);
    }

    /**
     * @param Request $request
     */
    public function sendEmailAlert(Request $request)
    {
        $request->validate([
            'ot' => 'required|string|max:200',
            'archivo' => 'nullable|string|max:300',
            'mode' => 'nullable|string|in:reemplazar,reiniciar',
        ]);

        $rawOt = $request->input('ot');
        $mode = $request->input('mode', 'reemplazar');
        $resetFlags = ($mode === 'reiniciar');

        $otModel = Orden_trabajo::query()->with('moldura')->find($rawOt);
        if ($otModel) {
            $otLabel = "OT " . $otModel->id . ($otModel->moldura ? " - " . $otModel->moldura->nombre : "");
            $otFolderName = $this->normalizeOTName($this->sanitizePath($otLabel));
        } else {
            $otFolderName = $this->normalizeOTName($this->sanitizePath($rawOt));
        }
        $originalName = $request->input('archivo') ? $this->sanitizeFileName($request->input('archivo')) : null;

        try {
            $this->sendAlertInternal($otFolderName, $originalName, $resetFlags);
            $descLog = $originalName ? "Envío de archivo ({$mode}): {$originalName}" : "Múltiples archivos ({$mode})";
            $this->logAction('enviar_alerta', $otFolderName, $descLog);
            $msg = $resetFlags ? "OT {$otFolderName} reiniciada por completo y alerta enviada a Almacén." : ($originalName ? "Correo de alerta enviado para {$originalName}." : "Correo de alerta enviado para la OT {$otFolderName}.");
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
     * @param bool $resetFlags
     */
    private function sendAlertInternal($otName, $fileName, bool $resetFlags = false): void
    {
        // ─── 3. Enviar correo (incluyendo info de ayudas visuales) ───────────────
        $history = FundicionHistory::firstOrCreate(['ot' => $otName]);
        $ayudas = is_array($history->ayudas_config) ? $history->ayudas_config : [];

        // Descubrir clases físicas en disco para asegurar que no se omita ninguna clase de la OT
        $srcDir = $this->resolveCaseInsensitivePath(self::BASE_DIR . '/' . $otName);
        if (Storage::disk('local')->exists($srcDir)) {
            $subdirs = Storage::disk('local')->directories($srcDir);
            foreach ($subdirs as $sd) {
                $bName = strtoupper(trim(basename($sd)));
                if (!in_array(strtolower($bName), ['ayudas_visuales', 'documentos_aprobados', 'documentos_rechazados', 'preordenes', 'dwg_fundicion'])) {
                    if (!in_array($bName, $ayudas)) {
                        $ayudas[] = $bName;
                    }
                }
            }
        }

        $clasesEnviadasNuevas = [];
        $clasesAEnviar = [];

        $isFirstTime = true;

        if ($history) {
            $history->alert_sent_at = now();
            $enviadasPrevias = is_array($history->clases_enviadas) ? $history->clases_enviadas : [];
            $isFirstTime = empty($enviadasPrevias);

            // Normalizar a formato diccionario
            $enviadasDict = [];
            foreach ($enviadasPrevias as $key => $val) {
                if (is_numeric($key)) {
                    $enviadasDict[$val] = ""; // Legacy
                } else {
                    $enviadasDict[$key] = $val;
                }
            }

            // Cambios pendientes anteriores (por si enviaron algo más mientras estaba pendiente)
            $pendingChanges = is_array($history->pending_almacen_changes) ? $history->pending_almacen_changes : [];

            foreach ($ayudas as $clase) {
                $currentHash = self::computeClassHash($otName, $clase);

                // Si la clase no tiene archivos (hash vacío), la ignoramos completamente (sigue pendiente)
                if ($currentHash === "") {
                    continue;
                }

                $storedHash = $enviadasDict[$clase] ?? null;

                // Solo incluir en el correo si es nueva o cambió
                if ($storedHash === null) {
                    // Clase nueva
                    $clasesAEnviar[] = $clase;
                    $clasesEnviadasNuevas[$clase] = $currentHash; // Actualizamos el hash ya que es nueva
                } elseif ($storedHash !== $currentHash) {
                    // Clase modificada
                    $clasesAEnviar[] = $clase;
                    $clasesEnviadasNuevas[$clase] = $currentHash; // Actualizamos el hash al nuevo enviado
                    if (!in_array($clase, $pendingChanges)) {
                        $pendingChanges[] = $clase;
                    }
                } else {
                    // Sin cambios: conservar el hash previo tal cual
                    $clasesEnviadasNuevas[$clase] = $storedHash;
                }
            }

            // Merge: conservar clases previas que ya no están en ayudas (no borrar historial)
            foreach ($enviadasDict as $clasePrevia => $hashPrevio) {
                if (!isset($clasesEnviadasNuevas[$clasePrevia])) {
                    $clasesEnviadasNuevas[$clasePrevia] = $hashPrevio;
                }
            }

            // Guardar el estado combinado ANTES de llamar a copyToAlmacen
            $history->clases_enviadas = $clasesEnviadasNuevas;
            $history->pending_almacen_changes = !empty($pendingChanges) ? $pendingChanges : null;
            $history->save();
        }

        // Llamamos a copyToAlmacen con el resetFlags indicado (reemplazar = false, reiniciar = true)
        $this->copyToAlmacen($otName, $resetFlags);

        // Si no hay clases nuevas/modificadas, podríamos abortar, pero tal vez quieran reenviar si $fileName no está vacío.
        // Si no hay cambios pero se forzó el envío, enviamos todas por defecto para no romper UX.
        if (empty($clasesAEnviar)) {
            // Pero NUNCA enviamos clases que tienen 0 archivos (hash vacío)
            foreach ($ayudas as $claseFallback) {
                if (self::computeClassHash($otName, $claseFallback) !== "") {
                    $clasesAEnviar[] = $claseFallback;
                }
            }
        }

        // Si después de todo esto sigue vacío (ej. la OT solo tiene clases en 0), abortamos para no enviar spam inútil.
        if (empty($clasesAEnviar) && !$fileName) {
            return; // No hay nada que enviar
        }

        $emailsStr = config('services.almacen.email', 'almacentec@grupoindsaavedra.com');
        $emails = array_filter(array_map('trim', explode(',', $emailsStr)));

        Mail::to($emails)->send(new DibujoFundicionAlertMail($otName, $fileName, $clasesAEnviar, !$isFirstTime));
    }

    /**
     * Copia los archivos del directorio de trabajo al directorio protegido de Almacén.
     * También sincroniza las ayudas visuales vinculadas.
     *
     * @param string $otName
     */
    public static function copyToAlmacen(string $otName, bool $resetFlags = false, ?array $onlyClasses = null): void
    {
        $otName = self::normalizeOTName($otName);
        $instance = new self();
        $srcDir = $instance->resolveCaseInsensitivePath(self::BASE_DIR . '/' . $otName);
        $dstDir = self::ALMACEN_DIR . '/' . $otName;

        $history = FundicionHistory::where('ot', '=', $otName, 'and')->first();
        $pendingChanges = $history && is_array($history->pending_almacen_changes) ? $history->pending_almacen_changes : [];

        // 1. Sincronizar dibujos por clase → nueva ruta: {Clase}/Dibujos/
        if (Storage::disk('local')->exists($srcDir)) {
            if (!Storage::disk('local')->exists($dstDir)) {
                Storage::disk('local')->makeDirectory($dstDir);
            }

            // Obtener todos los PDFs organizados por clase (subcarpetas directas de la OT)
            $srcFilesFull = collect(Storage::disk('local')->allFiles($srcDir))
                ->filter(fn($f) => in_array(strtolower(pathinfo($f, PATHINFO_EXTENSION)), ['pdf', 'dwg']));

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
                        && in_array(strtolower(pathinfo($f, PATHINFO_EXTENSION)), ['pdf', 'dwg']);
                });

            $dstFilesRel = $dstFilesFull->map(fn($f) => str_replace(str_replace('\\', '/', $dstDir) . '/', '', str_replace('\\', '/', $f)))->toArray();

            // Sincronizar: eliminar dibujos en Almacén y Calidad que ya no existen en Ingeniería
            foreach ($dstFilesRel as $dfRel) {
                // Normalizar: si el destino tiene /Dibujos/ en la ruta, comparar sin él
                $dfRelNorm = preg_replace('#/Dibujos/#', '/', $dfRel);

                // Si la clase tiene cambios pendientes, NO ELIMINAR sus archivos viejos en Almacen
                $parts = explode('/', $dfRelNorm, 2);
                $claseDel = count($parts) === 2 ? $parts[0] : '';
                
                if (is_array($onlyClasses) && count($onlyClasses) > 0) {
                    $claseMatch = false;
                    foreach ($onlyClasses as $oc) {
                        if (strtolower(trim($claseDel)) === strtolower(trim($oc))) {
                            $claseMatch = true;
                            break;
                        }
                    }
                    if (!$claseMatch) continue;
                }

                if (in_array($claseDel, $pendingChanges)) {
                    continue;
                }

                $srcFilesRelNorm = array_map(fn($r) => preg_replace('#/Dibujos/#', '/', $r), $srcFilesRel);
                if (!in_array($dfRelNorm, $srcFilesRelNorm)) {
                    Storage::disk('local')->delete($dstDir . '/' . $dfRel);
                    $calidadDir = self::CALIDAD_DIR . '/' . $otName;
                    Storage::disk('local')->delete($calidadDir . '/' . $dfRel);
                }
            }

            // Copiar dibujos a nueva ruta: {Clase}/Dibujos/{archivo}
            foreach ($srcFilesRel as $sfRel) {
                // sfRel viene como: {Clase}/{archivo.pdf}  (estructura de DIBUJOS_FUNDICION)
                $parts = explode('/', $sfRel, 2);
                if (count($parts) === 2) {
                    [$clase, $archivo] = $parts;
                    
                    if (is_array($onlyClasses) && count($onlyClasses) > 0) {
                        $claseMatch = false;
                        foreach ($onlyClasses as $oc) {
                            if (strtolower(trim($clase)) === strtolower(trim($oc))) {
                                $claseMatch = true;
                                break;
                            }
                        }
                        if (!$claseMatch) continue;
                    }

                    $claseClean = strtoupper(trim(preg_replace('/^modelo\s+/i', '', strtolower($clase))));
                    if (empty($claseClean)) $claseClean = 'GENERAL';

                    if (in_array($claseClean, $pendingChanges))
                        continue; // IGNORAR SI LA CLASE TIENE CAMBIOS PENDIENTES
                    
                    if (str_ends_with(strtolower($archivo), '.dwg')) {
                        $dstPath = $dstDir . '/' . $claseClean . '/' . FundicionPaths::DIBUJOS . '/' . FundicionPaths::DWG_FUNDICION . '/' . $archivo;
                    } else {
                        $dstPath = $dstDir . '/' . $claseClean . '/' . FundicionPaths::DIBUJOS . '/' . $archivo;
                    }
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
                if (is_array($onlyClasses) && count($onlyClasses) > 0) {
                    $claseMatch = false;
                    foreach ($onlyClasses as $oc) {
                        if (strtolower(trim($clase)) === strtolower(trim($oc))) {
                            $claseMatch = true;
                            break;
                        }
                    }
                    if (!$claseMatch) continue;
                }

                $claseClean = strtoupper(trim(preg_replace('/^modelo\s+/i', '', strtolower($clase))));
                if (empty($claseClean)) $claseClean = 'GENERAL';

                if (in_array($claseClean, $pendingChanges))
                    continue; // IGNORAR SI LA CLASE TIENE CAMBIOS PENDIENTES

                // Nueva ruta de destino: {OT}/{Clase}/Ayudas_Visuales/
                $claseDstDir = $dstDir . '/' . $claseClean . '/' . FundicionPaths::AYUDAS_VISUALES;
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
                                if (in_array(strtolower(pathinfo($f, PATHINFO_EXTENSION)), ['pdf', 'dwg'])) {
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
                        $calidadDir = self::CALIDAD_DIR . '/' . $otName;
                        $calidadAyudaDst = $calidadDir . '/' . $claseClean . '/' . FundicionPaths::AYUDAS_VISUALES . '/' . $fa;
                        Storage::disk('local')->delete($calidadAyudaDst);
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
                        && in_array(strtolower(pathinfo($f, PATHINFO_EXTENSION)), ['pdf', 'dwg']);
                })
                ->map(fn($f) => str_replace(str_replace('\\', '/', $dstDir) . '/', '', str_replace('\\', '/', $f)))
                ->values()
                ->toArray();
        }

        // 4. Limpiar los registros antiguos de Almacén y Calidad si se indica
        // Esto garantiza que al reiniciar el proceso se empiece desde 0 (borrón y cuenta nueva)
        if ($resetFlags) {
            $baseOtName = preg_replace('/_(?:(?:candado\s+obturador|cabeza\s+de\s+soplo|obturador|bombillo|embudo|corona|plato|molde|fondo|pistones|guías|guias)(?:_(?:candado\s+obturador|cabeza\s+de\s+soplo|obturador|bombillo|embudo|corona|plato|molde|fondo|pistones|guías|guias))*_)?R\d+$/iu', '', $otName);

            \App\Models\PreOrdenFundicion::query()
                ->where('ot', '=', $otName)
                ->orWhere('ot', 'LIKE', $baseOtName . '%')
                ->delete();

            \App\Models\LiberacionModeloFundicion::query()
                ->where('ot', '=', $otName)
                ->orWhere('ot', 'LIKE', $baseOtName . '%')
                ->delete();

            \App\Models\ScarModelo::query()
                ->where('ot', '=', $otName)
                ->orWhere('ot', 'LIKE', $baseOtName . '%')
                ->delete();

            // Eliminar físicamente los documentos aprobados, rechazados, SCAR y preórdenes en Almacén y Calidad
            $targetOtNames = array_unique([$otName, $baseOtName]);
            $baseRoots = [
                self::ALMACEN_DIR,
                'DOCUMENTACION_GIS/ALMACEN_FUNDICION',
                'DOCUMENTACION_GIS/CALIDAD_FUNDICION',
                'DOCUMENTACION_GIS/Fundicion_Calidad/Dibujos_y_Fichas_Tecnicas',
                'DOCUMENTACION_GIS/Fundicion_Calidad',
            ];

            foreach ($targetOtNames as $tOt) {
                $tOtSan = preg_replace('/[^\w\s\-]/', '', $tOt);
                $tOtSan = preg_replace('/[\s]+/', '_', trim($tOtSan));
                preg_match('/(\d+)/', $tOt, $mNum);
                $rawId = $mNum[1] ?? '';

                foreach ($baseRoots as $root) {
                    $otDir = $root . '/' . $tOt;
                    if (Storage::disk('local')->exists($otDir)) {
                        $allSubDirs = Storage::disk('local')->allDirectories($otDir);
                        $allSubDirs[] = $otDir;
                        foreach ($allSubDirs as $subDir) {
                            $baseNameLow = strtolower(basename($subDir));
                            if (in_array($baseNameLow, [
                                'documentos_aprobados', 'documentos_rechazados', 
                                'preordenes', 'fdldm', 'fdrdm', 'scar', 'evidencias'
                            ])) {
                                Storage::disk('local')->deleteDirectory($subDir);
                            }
                        }
                    }
                }

                // Borrar también los archivos PDF sueltos en public/liberaciones_pdf (storage/app/public/liberaciones_pdf)
                $liberacionesPath = storage_path('app/public/liberaciones_pdf');
                if (file_exists($liberacionesPath)) {
                    $libFiles = glob($liberacionesPath . '/*.pdf') ?: [];
                    foreach ($libFiles as $lf) {
                        $lfBase = basename($lf);
                        if (
                            str_contains($lfBase, $tOt) ||
                            (!empty($tOtSan) && str_contains($lfBase, $tOtSan)) ||
                            (!empty($rawId) && str_contains($lfBase, "OT_{$rawId}")) ||
                            (!empty($rawId) && str_contains($lfBase, "OT {$rawId}"))
                        ) {
                            @unlink($lf);
                        }
                    }

                    $evidenciasPath = $liberacionesPath . '/evidencia';
                    if (file_exists($evidenciasPath)) {
                        $evFiles = glob($evidenciasPath . '/*') ?: [];
                        foreach ($evFiles as $ef) {
                            $efBase = basename($ef);
                            if (
                                str_contains($efBase, $tOt) ||
                                (!empty($tOtSan) && str_contains($efBase, $tOtSan)) ||
                                (!empty($rawId) && str_contains($efBase, "OT_{$rawId}")) ||
                                (!empty($rawId) && str_contains($efBase, "OT {$rawId}"))
                            ) {
                                @unlink($ef);
                            }
                        }
                    }
                }
            }

            FundicionHistory::updateOrCreate(
                ['ot' => $otName],
                [
                    'status' => 'activa',
                    'alert_sent_at' => now(), // Asegurar que aparezca en Almacén inmediatamente
                    'almacen_archivos' => $almacenFiles,
                    'tiene_modelo' => false,
                    'pre_orden_sent' => false,
                    'pre_orden_email_sent' => false,
                    'calidad_revision_status' => null,
                    'casting_pdf_generated' => false,
                    'rechazos_procesados' => false,
                    'pending_almacen_changes' => null,
                ]
            );
        } else {
            // Si no se resetean los flags, solo actualizamos los archivos
            FundicionHistory::updateOrCreate(
                ['ot' => $otName],
                [
                    'status' => 'activa',
                    'almacen_archivos' => $almacenFiles,
                ]
            );
        }

        // 5. Espejo Almacén → Calidad: Copiar dibujos + ayudas_visuales al directorio de Calidad
        //    para que el equipo de Calidad vea los mismos archivos que subió Almacén.
        //    Se excluye la subcarpeta preordenes/ (es territorio exclusivo de Calidad).
        $calidadDir = 'DOCUMENTACION_GIS/CALIDAD_FUNDICION/' . $otName;
        if (Storage::disk('local')->exists($dstDir)) {
            $allAlmacenFiles = Storage::disk('local')->allFiles($dstDir);
            $almacenRelPaths = [];
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

                $almacenRelPaths[] = $relPath;

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

            // Eliminar archivos huérfanos en Calidad (dibujos y ayudas visuales) que ya no están en Almacén
            if (Storage::disk('local')->exists($calidadDir)) {
                $allCalidadFiles = Storage::disk('local')->allFiles($calidadDir);
                foreach ($allCalidadFiles as $cFile) {
                    $cRel = ltrim(substr(str_replace('\\', '/', $cFile), strlen(str_replace('\\', '/', $calidadDir))), '/');
                    if (
                        str_starts_with($cRel, 'Documentos_Aprobados/') ||
                        str_starts_with($cRel, 'Documentos_Rechazados/') ||
                        str_starts_with($cRel, 'ayudas_visuales/preordenes/') ||
                        str_starts_with($cRel, 'preordenes/')
                    ) {
                        continue;
                    }

                    if (!in_array($cRel, $almacenRelPaths)) {
                        Storage::disk('local')->delete($cFile);
                    }
                }
            }
        }
    }

    /**
     * @param Request $request
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
     * @param Request $request
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

        $claseClean = strtoupper(trim(preg_replace('/^modelo\s+/i', '', strtolower($clase))));
        if (empty($claseClean)) $claseClean = 'GENERAL';

        $dirPath = self::BASE_DIR . '/' . $otNorm . '/' . $claseClean;
        $oldDirPath = self::OLD_BASE_DIR . '/' . $otNorm . '/' . $claseClean;

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

        $this->logAction('eliminar_carpeta', $otNorm . '/' . $claseClean, 'Eliminación de Clase');

        // Sincronizar con histórico (eliminar vinculación si existe)
        $history = FundicionHistory::where('ot', '=', $otNorm, 'and')->first();
        if ($history) {
            $ayudas = $history->ayudas_config ?? [];
            if (in_array($claseClean, $ayudas)) {
                $ayudas = array_values(array_filter($ayudas, fn($a) => $a !== $claseClean));
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
        
        $otNorm = self::normalizeOTName($ot);
        $dirPath = self::BASE_DIR . '/' . $otNorm;
        $oldDirPath = self::OLD_BASE_DIR . '/' . $otNorm;

        if (!Storage::disk('local')->exists($dirPath) && !Storage::disk('local')->exists($oldDirPath)) {
            return response()->json(['success' => false, 'message' => 'La carpeta no existe.'], 404);
        }

        self::deactivateOtAndArchive($ot);

        return response()->json([
            'success' => true,
            'message' => "Directorio raíz '{$ot}' eliminado correctamente.",
        ]);
    }

    /**
     * Inactiva la OT y archiva físicamente sus directorios.
     *
     * @param string $ot
     * @return bool
     */
    public static function deactivateOtAndArchive(string $ot): bool
    {
        $otNorm = self::normalizeOTName($ot);

        $dirPath    = self::BASE_DIR . '/' . $otNorm;
        $oldDirPath = self::OLD_BASE_DIR . '/' . $otNorm;

        // ─────────────────────────────────────────────────────────────────────
        // 1. Archivar EN UNA SOLA CARPETA INACTIVAS al nivel de DOCUMENTACION_GIS
        //    Estructura: DOCUMENTACION_GIS/INACTIVAS/{OT}_{timestamp}/
        //                    INGENIERIA/        <- viene de DIBUJOS_FUNDICION
        //                    INGENIERIA_LEGACY/ <- viene de FUNDICION_GIS (legacy)
        //                    ALMACEN/           <- viene de ALMACEN_FUNDICION
        //                    CALIDAD/           <- viene de CALIDAD_FUNDICION
        // ─────────────────────────────────────────────────────────────────────
        $timestamp     = date('_Ymd_His') . '_del';
        $inactivasRoot = 'DOCUMENTACION_GIS/INACTIVAS';

        // Helper: copia todos los archivos de $source a $dest/{subdir}/ y luego
        //         elimina el directorio origen.
        $archiveToInactivas = function (string $source, string $otArchiveDir, string $subdir): void {
            if (!Storage::disk('local')->exists($source)) {
                return;
            }
            $dest = $otArchiveDir . '/' . $subdir;
            if (!Storage::disk('local')->exists($dest)) {
                Storage::disk('local')->makeDirectory($dest);
            }
            foreach (Storage::disk('local')->allFiles($source) as $file) {
                $relPath    = ltrim(str_replace(str_replace('\\', '/', $source), '', str_replace('\\', '/', $file)), '/');
                $targetPath = $dest . '/' . $relPath;
                $targetDir  = dirname($targetPath);
                if (!Storage::disk('local')->exists($targetDir)) {
                    Storage::disk('local')->makeDirectory($targetDir);
                }
                Storage::disk('local')->copy($file, $targetPath);
            }
            Storage::disk('local')->deleteDirectory($source);
        };

        // Crear carpeta raíz de inactivas si no existe
        if (!Storage::disk('local')->exists($inactivasRoot)) {
            Storage::disk('local')->makeDirectory($inactivasRoot);
        }

        $otArchiveDir = $inactivasRoot . '/' . $otNorm . $timestamp;

        $archiveToInactivas($dirPath,                                          $otArchiveDir, 'INGENIERIA');
        $archiveToInactivas($oldDirPath,                                       $otArchiveDir, 'INGENIERIA_LEGACY');
        $archiveToInactivas(self::ALMACEN_DIR . '/' . $otNorm,                $otArchiveDir, 'ALMACEN');
        $archiveToInactivas('DOCUMENTACION_GIS/CALIDAD_FUNDICION/' . $otNorm, $otArchiveDir, 'CALIDAD');
        $archiveToInactivas('DOCUMENTACION_GIS/Fundicion_Calidad/' . $otNorm, $otArchiveDir, 'CALIDAD_LEGACY');

        // Limpiar subcarpetas INACTIVAS en Almacén y Dibujos si existían de ejecuciones pasadas
        $legacySubDirs = [
            'DOCUMENTACION_GIS/DIBUJOS_FUNDICION/INACTIVAS',
            'DOCUMENTACION_GIS/ALMACEN_FUNDICION/INACTIVAS',
            'DOCUMENTACION_GIS/CALIDAD_FUNDICION/INACTIVAS',
        ];
        foreach ($legacySubDirs as $lsd) {
            if (Storage::disk('local')->exists($lsd)) {
                Storage::disk('local')->deleteDirectory($lsd);
            }
        }

        self::logActionStatic('eliminar_carpeta', $otNorm, 'OT archivada en INACTIVAS: ' . $otArchiveDir);

        // ─────────────────────────────────────────────────────────────────────
        // 2. Eliminar completamente registros en BD (Almacén y Calidad)
        // ─────────────────────────────────────────────────────────────────────
        $baseOtClean = preg_replace('/_.*_R\d+$|_R\d+$/i', '', $otNorm);

        $otsToDelete = [$otNorm, $baseOtClean];

        // ─────────────────────────────────────────────────────────────────────
        // 3. Eliminar físicamente y en BD la OT original y todos sus reprocesos
        // ─────────────────────────────────────────────────────────────────────
        $reprocessHistories = FundicionHistory::where(function ($q) use ($otNorm, $baseOtClean) {
            $q->where('ot', '=', $otNorm)
                ->orWhere('ot', 'LIKE', $baseOtClean . '_%_R%')
                ->orWhere('ot', 'LIKE', $baseOtClean . '_R%')
                ->orWhere('ot', 'LIKE', $otNorm . '%_del')
                ->orWhere('ot', 'LIKE', $baseOtClean . '%_del');
        })->get();

        foreach ($reprocessHistories as $rh) {
            $otsToDelete[] = $rh->ot;
            $rTimestamp   = date('_Ymd_His') . '_del';
            $rArchiveDir  = $inactivasRoot . '/' . $rh->ot . $rTimestamp;

            $archiveToInactivas(self::BASE_DIR    . '/' . $rh->ot,                          $rArchiveDir, 'INGENIERIA');
            $archiveToInactivas(self::OLD_BASE_DIR . '/' . $rh->ot,                         $rArchiveDir, 'INGENIERIA_LEGACY');
            $archiveToInactivas(self::ALMACEN_DIR  . '/' . $rh->ot,                         $rArchiveDir, 'ALMACEN');
            $archiveToInactivas('DOCUMENTACION_GIS/CALIDAD_FUNDICION/' . $rh->ot,           $rArchiveDir, 'CALIDAD');
            $archiveToInactivas('DOCUMENTACION_GIS/Fundicion_Calidad/' . $rh->ot,           $rArchiveDir, 'CALIDAD_LEGACY');
        }

        $otsToDelete = array_values(array_unique(array_filter($otsToDelete)));

        foreach ($otsToDelete as $otTarget) {
            FundicionHistory::where('ot', '=', $otTarget)->orWhere('ot', 'LIKE', $otTarget . '%_del')->delete();
            \App\Models\PreOrdenFundicion::where('ot', '=', $otTarget)->orWhere('ot', 'LIKE', $otTarget . '%_del')->delete();
            \App\Models\LiberacionModeloFundicion::where('ot', '=', $otTarget)->orWhere('ot', 'LIKE', $otTarget . '%_del')->delete();
            \App\Models\ScarModelo::where('ot', '=', $otTarget)->orWhere('ot', 'LIKE', $otTarget . '%_del')->delete();
            \App\Models\PreOrdenLog::where('ot', '=', $otTarget)->orWhere('ot', 'LIKE', $otTarget . '%_del')->delete();
            \App\Models\RechazoLog::where('ot', '=', $otTarget)->orWhere('ot', 'LIKE', $otTarget . '%_del')->delete();
        }

        // Limpiar también cualquier registro huérfano con status 'inactiva' o '_del' en BD
        FundicionHistory::where('status', '=', 'inactiva')->orWhere('ot', 'LIKE', '%_del%')->delete();
        \App\Models\PreOrdenFundicion::where('ot', 'LIKE', '%_del%')->delete();
        \App\Models\LiberacionModeloFundicion::where('ot', 'LIKE', '%_del%')->delete();
        \App\Models\ScarModelo::where('ot', 'LIKE', '%_del%')->delete();

        return true;

        return true;
    }

    /**
     * @param Request $request
     */
    public function replacePdf(Request $request)
    {
        $request->validate([
            'ot' => 'required|string|max:200',
            'clase' => 'nullable|string|max:100',
            'archivo_anterior' => 'required|string|max:300',
            'pdf' => 'required|file|mimes:pdf,dwg',
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
            'url' => url('/fundicion/serve') . '?ot=' . urlencode($otNorm) . '&clase=' . urlencode($clase) . '&archivo=' . urlencode($newName),
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

        $rawOt = $request->input('ot');
        $otModel = Orden_trabajo::query()->with('moldura')->find($rawOt);
        if ($otModel) {
            $otLabel = "OT " . $otModel->id . ($otModel->moldura ? " - " . $otModel->moldura->nombre : "");
            $ot = $this->normalizeOTName($this->sanitizePath($otLabel));
        } else {
            $ot = $this->normalizeOTName($this->sanitizePath($rawOt));
        }
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
                'message' => $msg,
                'ayudasLinked' => $ayudasFinales,
                'ot' => $ot
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
            'message' => 'Ayudas visuales desvinculadas correctamente.',
            'ayudasLinked' => [],
            'ot' => $ot
        ]);
    }

    // =========================================================================
    // HELPERS PRIVADOS
    // =========================================================================

    private function buildStructure(): array
    {
        $estructura = [];
        $bases = [self::BASE_DIR, self::OLD_BASE_DIR];

        foreach ($bases as $baseDir) {
            $basePath = Storage::disk('local')->path($baseDir);
            if (is_dir($basePath)) {
                $otDirs = glob($basePath . '/*', GLOB_ONLYDIR);
                if ($otDirs) {
                    foreach ($otDirs as $otDir) {
                        $folderBaseName = basename($otDir);
                        if (strtoupper(trim($folderBaseName)) === 'INACTIVAS') {
                            continue;
                        }

                        $otName = $this->toUtf8($this->normalizeOTName($folderBaseName));

                        $clases = [];

                        $claseDirs = glob($otDir . '/*', GLOB_ONLYDIR);
                        if ($claseDirs) {
                            foreach ($claseDirs as $claseDir) {
                                $clases[] = $this->toUtf8(basename($claseDir));
                            }
                        }

                        $pdfs = glob($otDir . '/*.{pdf,PDF}', GLOB_BRACE);
                        if ($pdfs && count($pdfs) > 0) {
                            $clases[] = '--';
                        }

                        if (isset($estructura[$otName])) {
                            $estructura[$otName] = array_unique(array_merge($estructura[$otName], $clases));
                        } else {
                            $estructura[$otName] = $clases;
                        }
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
        self::logActionStatic($action, $ruta, $archivo);
    }

    public static function logActionStatic(string $action, string $ruta, ?string $archivo): void
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
        // Optimización masiva: si la ruta exacta ya existe, devolverla inmediatamente
        if (Storage::disk('local')->exists($path) || Storage::disk('local')->directoryExists($path)) {
            return $path;
        }

        $parts = explode('/', str_replace('\\', '/', $path));
        $resolved = '';

        foreach ($parts as $part) {
            if ($part === '')
                continue;

            $currentSearch = $resolved ? $resolved : '.';

            $exactPath = $resolved ? $resolved . '/' . $part : $part;
            if (Storage::disk('local')->exists($exactPath) || Storage::disk('local')->directoryExists($exactPath)) {
                $resolved = $exactPath;
                continue;
            }

            if (!Storage::disk('local')->exists($currentSearch) && !Storage::disk('local')->directoryExists($currentSearch)) {
                $resolved = $exactPath;
                continue;
            }

            $subdirs = Storage::disk('local')->directories($currentSearch);
            $found = false;

            $partNorm = mb_strtolower($part, 'UTF-8');
            $partNorm = str_replace(['—', '–'], '-', $partNorm);
            $partNorm = preg_replace('/[.,_]/', ' ', $partNorm);
            $partNorm = preg_replace('/\s+/', ' ', $partNorm);
            $partNorm = trim($partNorm);

            foreach ($subdirs as $subdir) {
                $base = basename($subdir);
                $baseNorm = mb_strtolower($base, 'UTF-8');
                $baseNorm = str_replace(['—', '–'], '-', $baseNorm);
                $baseNorm = preg_replace('/[.,_]/', ' ', $baseNorm);
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
                    $baseNorm = preg_replace('/[.,_]/', ' ', $baseNorm);
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
        // Solo prevenir directory traversal hacia arriba
        $path = preg_replace('/\.\.+/', '', $path);
        // Normalizar slashes
        $path = str_replace('\\', '/', $path);
        $path = trim($path);
        return $path;
    }

    private function sanitizeFileName(string $name): string
    {
        // Solo prevenir directory traversal hacia arriba
        $name = preg_replace('/\.\.+/', '', $name);
        // Normalizar slashes
        $name = str_replace('\\', '/', $name);
        return trim($name) ?: 'archivo.pdf';
    }

    private function toUtf8(string $string): string
    {
        if (!mb_check_encoding($string, 'UTF-8')) {
            return mb_convert_encoding($string, 'UTF-8', 'Windows-1252');
        }
        return $string;
    }

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

    /**
     * Calcula un hash único basado en los archivos y sus fechas de modificación para una clase.
     */
    public static function computeClassHash(string $otName, string $claseName): string
    {
        $otNorm = self::normalizeOTName($otName);
        $instance = new self();

        // SOLO ESCANEAMOS LAS CARPETAS ESPECÍFICAS DE LA OT (DIBUJOS), NO LAS AYUDAS GLOBALES
        // Si no hay dibujos específicos, el hash será vacío y la clase se considerará en 0.
        $dirsToScan = [
            $instance->resolveCaseInsensitivePath(self::BASE_DIR . '/' . $otNorm . '/' . $claseName),
            $instance->resolveCaseInsensitivePath(self::OLD_BASE_DIR . '/' . $otNorm . '/' . $claseName)
        ];

        $filesData = [];
        foreach ($dirsToScan as $dir) {
            if ($dir && Storage::disk('local')->exists($dir)) {
                $absDir = Storage::disk('local')->path($dir);
                // Escanear archivos PDF y DWG en el directorio de la clase y subdirectorios (ej: DWG_FUNDICION)
                $files1 = glob($absDir . '/*.{pdf,PDF,dwg,DWG}', GLOB_BRACE) ?: [];
                $files2 = glob($absDir . '/*/*.{pdf,PDF,dwg,DWG}', GLOB_BRACE) ?: [];
                $files3 = glob($absDir . '/*/*/*.{pdf,PDF,dwg,DWG}', GLOB_BRACE) ?: [];
                $allFiles = array_merge($files1, $files2, $files3);

                foreach ($allFiles as $f) {
                    if (!is_file($f))
                        continue;
                    $size = filesize($f);
                    $mtime = filemtime($f);
                    $name = strtolower(basename($f));

                    // Combinamos Nombre + Tamaño en Bytes + Fecha de Modificación
                    $filesData[] = "{$name}_{$size}_{$mtime}";
                }
            }
        }

        sort($filesData);
        return empty($filesData) ? "" : md5(implode('|', array_unique($filesData)));
    }

    public static function isFileModifiedAfter(string $otName, string $claseName, int $limitTime): bool
    {
        $otNorm = self::normalizeOTName($otName);
        $instance = new self();
        $dirsToScan = [
            $instance->resolveCaseInsensitivePath(self::BASE_DIR . '/' . $otNorm . '/' . $claseName),
            $instance->resolveCaseInsensitivePath(self::OLD_BASE_DIR . '/' . $otNorm . '/' . $claseName)
        ];

        foreach ($dirsToScan as $dir) {
            if ($dir && Storage::disk('local')->exists($dir)) {
                $absDir = Storage::disk('local')->path($dir);
                $allFiles = array_merge(
                    glob($absDir . '/*.{pdf,PDF,dwg,DWG}', GLOB_BRACE) ?: [],
                    glob($absDir . '/*/*.{pdf,PDF,dwg,DWG}', GLOB_BRACE) ?: [],
                    glob($absDir . '/*/*/*.{pdf,PDF,dwg,DWG}', GLOB_BRACE) ?: []
                );
                foreach ($allFiles as $f) {
                    if (is_file($f)) {
                        if (filemtime($f) > $limitTime) {
                            return true;
                        }
                    }
                }
            }
        }
        return false;
    }
}
