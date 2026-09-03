<?php

namespace App\Http\Controllers;

use App\Models\FundicionHistory;
use App\Models\LiberacionLog;
use App\Models\LiberacionModeloFundicion;
use App\Models\Orden_trabajo;
use App\Models\PreOrdenFundicion;
use App\Models\RechazoLog;
use App\Models\ScarLog;
use App\Models\ScarModelo;
use App\Mail\LiberacionModeloMailable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use Illuminate\Support\Facades\Log;
use Normalizer;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Mail;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Services\FundicionPaths;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class CalidadFundicionController extends Controller
{
    /**
     * Directorio aislado donde se guardan las copias protegidas de Almacén.
     */
    private const ALMACEN_DIR = 'DOCUMENTACION_GIS/ALMACEN_FUNDICION';

    /**
     * Directorio aislado donde se guardan las copias protegidas de Calidad.
     */
    private const CALIDAD_DIR = 'DOCUMENTACION_GIS/CALIDAD_FUNDICION';

    /**
     * Perfiles de usuario que tienen acceso a esta vista.
     * 1 = Admin | 2 = Admin | 3 = Jefe | 4 = Calidad
     */
    private const PERFILES_PERMITIDOS = ['1', '2', '3', '4'];

    // =========================================================================
    // GATE DE ACCESO
    // =========================================================================

    private function verificarAcceso(): void
    {
        /** @var \App\Models\User|null $user */
        $user = Auth::user();

        if (!$user || !in_array($user->perfil, self::PERFILES_PERMITIDOS, true)) {
            abort(403, 'Acceso restringido. Solo Calidad puede ver esta sección.');
        }
    }

    // =========================================================================
    // VISTA PRINCIPAL
    // =========================================================================

    /**
     * Muestra la tabla con todos los registros históricos de Almacén/Calidad,
     * incluyendo su estado Activa/Inactiva.
     *
     * @param Request $request
     */
    public function index(Request $request)
    {
        $this->verificarAcceso();

        // Filtros desde query string
        $busquedaOt = trim($request->query('ot', ''));
        $desde = $request->query('desde', '');
        $hasta = $request->query('hasta', '');

        $query = FundicionHistory::query()->orderByDesc('alert_sent_at');

        // Filtro: búsqueda por nombre de OT (incluye todas, activas e inactivas)
        if ($busquedaOt !== '') {
            $query->where('ot', '=', $busquedaOt, 'and');
        }

        // Filtro: rango de fechas por fecha de alerta enviada
        if ($desde !== '') {
            $query->whereDate('alert_sent_at', '>=', $desde);
        }
        if ($hasta !== '') {
            $query->whereDate('alert_sent_at', '<=', $hasta);
        }

        // Solo registros que al menos hayan sido enviados a Almacén (alert_sent_at no nulo)
        $query->whereNotNull('alert_sent_at', 'and');

        $registros = $query->get();

        // Obtener lista única de OTs para el dropdown (solo los que están en Almacén)
        $listaOts = FundicionHistory::query()
            ->whereNotNull('alert_sent_at', 'and')
            ->orderBy('ot', 'asc')
            ->pluck('ot');

        return view('calidad.calidad_fundicion', compact(
            'registros',
            'listaOts',
            'busquedaOt',
            'desde',
            'hasta'
        ));
    }

    // =========================================================================
    // API — Lista de Archivos (para el panel de detalle)
    // =========================================================================

    /**
     * Devuelve los archivos del directorio aislado para una OT dada.
     * La lista proviene del snapshot en BD (almacen_archivos) y se verifica
     * físicamente para filtrar archivos que puedan haberse eliminado.
     *
     * @param Request $request
     */
    public function getFiles(Request $request)
    {
        $this->verificarAcceso();

        $ot = $this->sanitizePath($request->query('ot', ''));

        if (empty($ot)) {
            return response()->json(['error' => 'Parámetro OT es requerido.'], 422);
        }

        $folderName = $this->sanitizePath($this->normalizeOTName($ot));

        /** @var FundicionHistory|null $history */
        $history = FundicionHistory::where('ot', '=', $ot, 'and')->first();
        if (!$history) {
            $history = FundicionHistory::where('ot', '=', $folderName, 'and')->first();
        }

        // Check by base OT if not found directly
        $baseOt = preg_replace('/_(?:(?:candado\s+obturador|cabeza\s+de\s+soplo|obturador|bombillo|embudo|corona|plato|molde|fondo|pistones|guías|guias)(?:_(?:candado\s+obturador|cabeza\s+de\s+soplo|obturador|bombillo|embudo|corona|plato|molde|fondo|pistones|guías|guias))*_)?R\d+$/iu', '', $ot);
        if (!$history) {
            $history = FundicionHistory::where('ot', '=', $baseOt, 'and')->first();
        }

        if (!$history) {
            return response()->json([
                'existe' => false,
                'archivos' => [],
                'ot' => $ot,
            ]);
        }

        $isReproceso = (bool) preg_match('/_R\d+$/i', $ot);

        if ($isReproceso) {
            // Para reprocesos, buscar EXCLUSIVAMENTE dentro de la carpeta del reproceso ($ot), sin buscar en carpetas externas
            $allOtNames = [$ot];
        } else {
            $allOtNames = FundicionHistory::where('ot', '=', $baseOt, 'or')
                ->where('ot', 'LIKE', $baseOt . '_R%', 'or')
                ->where('ot', 'LIKE', $baseOt . '_%_R%', 'or')
                ->pluck('ot')
                ->toArray();
            if (!in_array($ot, $allOtNames)) {
                $allOtNames[] = $ot;
            }
        }

        $activeClasses = [];
        if ($history && !empty($history->ayudas_config)) {
            $config = is_string($history->ayudas_config) ? json_decode((string)$history->ayudas_config, true) : $history->ayudas_config;
            if (is_array($config)) {
                foreach ($config as $c) {
                    $clLow = strtolower($c);
                    foreach (['candado obturador', 'cabeza de soplo', 'obturador', 'bombillo', 'embudo', 'corona', 'plato', 'molde', 'fondo', 'pistones', 'guías', 'guias'] as $kc) {
                        if (strpos($clLow, $kc) !== false) {
                            $activeClasses[] = $kc;
                            break;
                        }
                    }
                }
            }
        }
        
        // Fallback a buscar en las liberaciones registradas si ayudas_config falla
        if (empty($activeClasses)) {
            $libs = LiberacionModeloFundicion::where('ot', '=', $ot, 'and')->pluck('tipo_modelo')->filter()->toArray();
            foreach ($libs as $l) {
                $activeClasses[] = strtolower($l);
            }
        }

        if (empty($activeClasses)) {
            $activeClasses = ['candado obturador', 'cabeza de soplo', 'obturador', 'bombillo', 'embudo', 'corona', 'plato', 'molde', 'fondo', 'pistones', 'guías', 'guias'];
        }
        $activeClasses = array_unique($activeClasses);

        $user = Auth::user();
        $isQuality = ($user->perfil == 4 || $user->perfil == 3); // 4 = Calidad, 3 = Master
        $isAdmin = ($user->perfil == 1 || $user->perfil == 2);
        $soloPreorden = $request->query('solo_preorden', '0') === '1';

        $dibujos = collect([]);
        $ayudas = collect([]);
        $generatedFiles = collect([]);

        foreach ($allOtNames as $relatedOt) {
            $relFolder = $this->sanitizePath($this->normalizeOTName($relatedOt));
            
            // Dibujos y Ayudas Visuales (no pre-ordenes) son cargados por Admin/Almacen y copiados a Calidad al alertar.
            $sharedDir = $this->resolveCaseInsensitivePath(self::CALIDAD_DIR . '/' . $relFolder);
            $sharedAyudasDir = $this->resolveCaseInsensitivePath($sharedDir . '/ayudas_visuales');

            if (!$soloPreorden) {
                // 1a. Dibujos — nueva ruta: {Clase}/Dibujos/ (con fallback a raíz de clase en CALIDAD)
                foreach (['Candado obturador', 'Cabeza de soplo', 'Obturador', 'Bombillo', 'Embudo', 'Corona', 'Plato', 'Molde', 'Fondo', 'Pistones', 'Guías', 'Guias'] as $claseDir) {
                    $claseNorm = strtolower($claseDir);
                    if (!in_array($claseNorm, $activeClasses)) continue;

                    $newDibjPath    = $this->resolveCaseInsensitivePath(self::CALIDAD_DIR . '/' . $relFolder . '/' . $claseDir . '/' . FundicionPaths::DIBUJOS);
                    $legacyDibjPath = $this->resolveCaseInsensitivePath(self::CALIDAD_DIR . '/' . $relFolder . '/' . $claseDir);

                    $scanFiles = [];
                    if ($newDibjPath && Storage::disk('local')->exists($newDibjPath)) {
                        $scanFiles = Storage::disk('local')->allFiles($newDibjPath);
                    } elseif ($legacyDibjPath && Storage::disk('local')->exists($legacyDibjPath)) {
                        $scanFiles = Storage::disk('local')->files($legacyDibjPath);
                    }

                    if (!empty($scanFiles)) {
                        $allowedExts = ['pdf', 'png', 'jpg', 'jpeg', 'gif', 'webp', 'bmp', 'dwg'];
                        $relatedDibujos = collect($scanFiles)
                            ->filter(fn($f) => in_array(strtolower(pathinfo($f, PATHINFO_EXTENSION)), $allowedExts))
                            ->map(function ($f) use ($relatedOt, $relFolder) {
                                $otBaseDirNorm = str_replace('\\', '/', self::CALIDAD_DIR . '/' . $relFolder);
                                $relName = ltrim(str_replace($otBaseDirNorm . '/', '', str_replace('\\', '/', $f)), '/');
                                $utf8RelName = $this->toUtf8($relName);
                                return [
                                    'nombre' => $utf8RelName,
                                    'tipo'   => 'dibujo',
                                    'url'    => route('calidad.fundicion.serve', [
                                        'ot'      => $relatedOt,
                                        'archivo' => $utf8RelName,
                                        'tipo'    => 'dibujo',
                                    ]),
                                ];
                            });
                        $dibujos = $dibujos->merge($relatedDibujos);
                    }
                }

                // 1b. Fallback genérico: raíz de sharedDir (archivos que no tienen clase nueva)
                if (Storage::disk('local')->exists($sharedDir)) {
                    $relatedDibujos = collect(Storage::disk('local')->allFiles($sharedDir))
                        ->filter(function ($f) use ($sharedDir) {
                            $rel = str_replace(str_replace('\\', '/', $sharedDir) . '/', '', str_replace('\\', '/', $f));
                            $relLower = strtolower($rel);
                            // Excluir archivos en subcarpetas de nueva estructura o carpetas reservadas
                            $reservedKeywords = [
                                'ayudas_visuales',
                                'ayudas_visuales_fundicion',
                                'dibujos_fundicion',
                                'dibujos',
                                'documentos_aprobados',
                                'documentos_rechazados',
                                'preordenes',
                                'formatos_liberacion'
                            ];
                            foreach ($reservedKeywords as $keyword) {
                                if (str_contains($relLower, $keyword)) {
                                    return false;
                                }
                            }
                            $allowedExts = ['pdf', 'png', 'jpg', 'jpeg', 'gif', 'webp', 'bmp', 'dwg'];
                            return in_array(strtolower(pathinfo($f, PATHINFO_EXTENSION)), $allowedExts);
                        })
                        ->filter(function ($f) use ($sharedDir, $activeClasses) {
                            $rel = str_replace(str_replace('\\', '/', $sharedDir) . '/', '', str_replace('\\', '/', $f));
                            $lower = strtolower($rel);
                            $known = ['candado obturador', 'cabeza de soplo', 'obturador', 'bombillo', 'embudo', 'corona', 'plato', 'molde', 'fondo', 'pistones', 'guías', 'guias'];
                            foreach ($known as $k) {
                                if (str_contains($lower, $k)) return in_array($k, $activeClasses);
                            }
                            return true;
                        })
                        ->map(function ($f) use ($relatedOt, $sharedDir) {
                            $relName = ltrim(str_replace(str_replace('\\', '/', $sharedDir), '', str_replace('\\', '/', $f)), '/');
                            $utf8RelName = $this->toUtf8($relName);
                            return [
                                'nombre' => $utf8RelName,
                                'tipo'   => 'dibujo',
                                'url'    => route('calidad.fundicion.serve', [
                                    'ot'      => $relatedOt,
                                    'archivo' => $utf8RelName,
                                    'tipo'    => 'dibujo',
                                ]),
                            ];
                        });
                    $dibujos = $dibujos->merge($relatedDibujos);
                }

                // 2a. Ayudas Visuales — nueva ruta: {Clase}/Ayudas_Visuales/ (con fallback legacy CALIDAD)
                foreach (['Candado obturador', 'Cabeza de soplo', 'Obturador', 'Bombillo', 'Embudo', 'Corona', 'Plato', 'Molde', 'Fondo', 'Pistones', 'Guías', 'Guias'] as $claseDir) {
                    $claseNorm = strtolower($claseDir);
                    if (!in_array($claseNorm, $activeClasses)) continue;

                    $newAyPath    = $this->resolveCaseInsensitivePath(self::CALIDAD_DIR . '/' . $relFolder . '/' . $claseDir . '/' . FundicionPaths::AYUDAS_VISUALES);
                    $legacyAyPath = $this->resolveCaseInsensitivePath(self::CALIDAD_DIR . '/' . $relFolder . '/ayudas_visuales/' . $claseDir);

                    $scanFiles = [];
                    if ($newAyPath && Storage::disk('local')->exists($newAyPath)) {
                        $scanFiles = Storage::disk('local')->allFiles($newAyPath);
                    } elseif ($legacyAyPath && Storage::disk('local')->exists($legacyAyPath)) {
                        $scanFiles = Storage::disk('local')->files($legacyAyPath);
                    }

                    if (!empty($scanFiles)) {
                        $allowedExts = ['pdf', 'png', 'jpg', 'jpeg', 'gif', 'webp', 'bmp', 'dwg'];
                        $relatedAyudas = collect($scanFiles)
                            ->filter(fn($f) => in_array(strtolower(pathinfo($f, PATHINFO_EXTENSION)), $allowedExts))
                            ->map(function ($f) use ($relatedOt, $relFolder) {
                                $otBaseDirNorm = str_replace('\\', '/', self::CALIDAD_DIR . '/' . $relFolder);
                                $relName = ltrim(str_replace($otBaseDirNorm . '/', '', str_replace('\\', '/', $f)), '/');
                                $utf8RelName = $this->toUtf8($relName);
                                return [
                                    'nombre' => $utf8RelName,
                                    'tipo'   => 'ayuda',
                                    'url'    => route('calidad.fundicion.serve', [
                                        'ot'      => $relatedOt,
                                        'archivo' => $utf8RelName,
                                        'tipo'    => 'ayuda',
                                    ]),
                                ];
                            });
                        $ayudas = $ayudas->merge($relatedAyudas);
                    }
                }
            }

            // 3. Documentos generados (Preordenes, Evidencias, Confirmaciones, LDM, SCAR)
            // Únicamente escanear documentos de proceso de la OT actual (no importar preórdenes/aprobados viejos de ciclos anteriores)
            if ($relatedOt === $ot) {
                $dirsToScan = [];

                // --- RUTAS EXCLUSIVAMENTE ESPECÍFICAS DE CADA CLASE ---
                foreach ($activeClasses as $clase) {
                    $cLow = trim(preg_replace('/^modelo\s+/i', '', strtolower($clase)));
                    $claseClean = strtoupper(preg_replace('/[^a-zA-Z0-9_\-]/', '_', $cLow));
                    if (empty($claseClean)) $claseClean = 'GENERAL';

                    // Almacen dirs
                    $classAlmacenBase = self::ALMACEN_DIR . '/' . $relFolder . '/' . $claseClean;
                    $dirsToScan[] = [
                        'path' => $this->resolveCaseInsensitivePath($classAlmacenBase . '/' . FundicionPaths::PREORDENES),
                        'origin' => 'aprobado',
                        'prefix' => $claseClean . '/' . FundicionPaths::PREORDENES . '/'
                    ];
                    $dirsToScan[] = [
                        'path' => $this->resolveCaseInsensitivePath($classAlmacenBase . '/' . FundicionPaths::FORMATOS_LIBERACION),
                        'origin' => 'aprobado',
                        'prefix' => $claseClean . '/' . FundicionPaths::FORMATOS_LIBERACION . '/'
                    ];
                    $dirsToScan[] = [
                        'path' => $this->resolveCaseInsensitivePath($classAlmacenBase . '/' . FundicionPaths::DOCUMENTOS_APROBADOS),
                        'origin' => 'aprobado',
                        'prefix' => $claseClean . '/' . FundicionPaths::DOCUMENTOS_APROBADOS . '/'
                    ];
                    $dirsToScan[] = [
                        'path' => $this->resolveCaseInsensitivePath($classAlmacenBase . '/ESCANEADOS'),
                        'origin' => 'aprobado',
                        'prefix' => $claseClean . '/ESCANEADOS/'
                    ];
                    $dirsToScan[] = [
                        'path' => $this->resolveCaseInsensitivePath($classAlmacenBase . '/' . FundicionPaths::DOCUMENTOS_RECHAZADOS),
                        'origin' => 'rechazado',
                        'prefix' => $claseClean . '/' . FundicionPaths::DOCUMENTOS_RECHAZADOS . '/'
                    ];

                    // Calidad dirs
                    $classCalidadBase = self::CALIDAD_DIR . '/' . $relFolder . '/' . $claseClean;
                    $dirsToScan[] = [
                        'path' => $this->resolveCaseInsensitivePath($classCalidadBase . '/' . FundicionPaths::PREORDENES),
                        'origin' => 'aprobado',
                        'prefix' => $claseClean . '/' . FundicionPaths::PREORDENES . '/'
                    ];
                    $dirsToScan[] = [
                        'path' => $this->resolveCaseInsensitivePath($classCalidadBase . '/' . FundicionPaths::FORMATOS_LIBERACION),
                        'origin' => 'aprobado',
                        'prefix' => $claseClean . '/' . FundicionPaths::FORMATOS_LIBERACION . '/'
                    ];
                    $dirsToScan[] = [
                        'path' => $this->resolveCaseInsensitivePath($classCalidadBase . '/' . FundicionPaths::DOCUMENTOS_APROBADOS),
                        'origin' => 'aprobado',
                        'prefix' => $claseClean . '/' . FundicionPaths::DOCUMENTOS_APROBADOS . '/'
                    ];
                    $dirsToScan[] = [
                        'path' => $this->resolveCaseInsensitivePath($classCalidadBase . '/ESCANEADOS'),
                        'origin' => 'aprobado',
                        'prefix' => $claseClean . '/ESCANEADOS/'
                    ];
                    $dirsToScan[] = [
                        'path' => $this->resolveCaseInsensitivePath($classCalidadBase . '/' . FundicionPaths::DOCUMENTOS_RECHAZADOS),
                        'origin' => 'rechazado',
                        'prefix' => $claseClean . '/' . FundicionPaths::DOCUMENTOS_RECHAZADOS . '/'
                    ];
                }

                foreach ($dirsToScan as $scanInfo) {
                $scanPath = $scanInfo['path'];
                $origin = $scanInfo['origin'];
                $prefix = $scanInfo['prefix'];
                if (Storage::disk('local')->exists($scanPath)) {
                    $files = collect(Storage::disk('local')->allFiles($scanPath))
                        ->filter(function ($f) use ($relatedOt, $ot, $activeClasses, $scanPath) {
                            $ext = strtolower(pathinfo($f, PATHINFO_EXTENSION));
                            $isDoc = in_array($ext, ['pdf', 'jpg', 'jpeg', 'png', 'gif', 'webp']);
                            if (!$isDoc) return false;

                            $fNorm = str_replace('\\', '/', $f);
                            $dirNorm = str_replace('\\', '/', $scanPath);
                            $relName = ltrim(str_replace($dirNorm, '', $fNorm), '/');
                            $fileLower = strtolower($relName);

                            $scanPathNorm = str_replace('\\', '/', $scanPath);
                            $isPreOrdenFile = str_contains($fileLower, 'pre-orden') || str_contains($fileLower, 'preorden') || str_contains($fileLower, 'f_alm_pfm') || str_contains($fileLower, 'f_alm_pfc');
                            $isPreOrdenDir = (bool) preg_match('/\/PREORDENES$/i', $scanPathNorm);
                            $isDocAprobDir = (bool) preg_match('/\/DOCUMENTOS_APROBADOS$/i', $scanPathNorm) || (bool) preg_match('/\/FORMATOS_LIBERACION$/i', $scanPathNorm);
                            $isEscaneadoFile = str_contains($fileLower, 'f_alm_efm') || str_contains($fileLower, 'f_alm_efc') || str_contains($fileLower, 'f_alm_cfm') || str_contains($fileLower, 'escaneado');

                            if ($isDocAprobDir && ($isPreOrdenFile || $isEscaneadoFile)) {
                                return false;
                            }

                            if ($isPreOrdenFile && !$isPreOrdenDir) {
                                return false;
                            }

                            if ($isPreOrdenFile && $isPreOrdenDir) {
                                return true;
                            }

                            $knownClasses = ['candado obturador', 'cabeza de soplo', 'obturador', 'bombillo', 'embudo', 'corona', 'plato', 'molde', 'fondo', 'pistones', 'guías', 'guias'];
                            $hasKnownClass = false;
                            foreach ($knownClasses as $kc) {
                                if (strpos($fileLower, $kc) !== false) {
                                    $hasKnownClass = true;
                                    break;
                                }
                            }
                            if ($hasKnownClass) {
                                $matchesActive = false;
                                foreach ($activeClasses as $ac) {
                                    $acTrimmed = trim($ac);
                                    if (!empty($acTrimmed) && strpos($fileLower, $acTrimmed) !== false) {
                                        $matchesActive = true;
                                        break;
                                    }
                                }
                                if (!$matchesActive) return false;
                            } else {
                                if ($relatedOt !== $ot) {
                                    return false;
                                }
                            }
                            return true;
                        })
                        ->map(function ($f) use ($scanPath, $relatedOt, $origin, $prefix) {
                            $fNorm = str_replace('\\', '/', $f);
                            $dirNorm = str_replace('\\', '/', $scanPath);
                            $relName = ltrim(str_replace($dirNorm, '', $fNorm), '/');
                            $utf8RelName = $this->toUtf8($relName);

                            $fullName = $prefix . $utf8RelName;

                            return [
                                'nombre' => $fullName,
                                'tipo' => 'otro',
                                'origin' => $origin,
                                'url' => route('calidad.fundicion.serve', [
                                    'ot' => $relatedOt,
                                    'archivo' => $fullName,
                                    'tipo' => 'otro',
                                    'origin' => $origin,
                                ]),
                            ];
                        });
                    $generatedFiles = $generatedFiles->merge($files);
                }
            }
            }
        }

        // De-duplicar documentos por nombre
        $generatedFiles = $generatedFiles->unique('nombre')->values();
        $dibujos = $dibujos->unique('nombre')->values();
        $ayudas = $ayudas->unique('nombre')->values();

        $historyLatest = FundicionHistory::where('ot', '=', $ot, 'and')->first() ?: FundicionHistory::where('ot', 'LIKE', $baseOt . '%', 'and')->orderBy('id', 'desc')->first();

        $almacenSent = $historyLatest ? (bool) (
            !empty($historyLatest->pre_orden_email_sent) ||
            !empty($historyLatest->tiene_modelo) ||
            PreOrdenFundicion::where('ot', '=', $ot, 'and')->where('is_sent', '=', true, 'and')->exists()
        ) : false;

        if ($isQuality && !$almacenSent) {
            $dibujos = collect([]);
            $ayudas = collect([]);
        }

        if ($soloPreorden) {
            // Filtrar solo las pre-órdenes (PDFs que empiezan con 'Pre-Orden' o 'PreOrden')
            $ayudas = $generatedFiles->filter(function ($f) {
                $base = basename($f['nombre']);
                return str_starts_with($base, 'Pre-Orden') || str_starts_with($base, 'PreOrden');
            });
            $allFiles = $ayudas->values();
        } else {
            $allFiles = $dibujos->merge($ayudas)->merge($generatedFiles)->values();
        }
        $preOrden = ($historyLatest && $historyLatest->ot) ? PreOrdenFundicion::where('ot', '=', $historyLatest->ot, 'and')->first() : null;
        $fechaEntrega = $preOrden && $preOrden->fecha_entrega 
            ? ($preOrden->fecha_entrega instanceof \DateTimeInterface 
                ? $preOrden->fecha_entrega->format('Y-m-d') 
                : substr((string)$preOrden->fecha_entrega, 0, 10)) 
            : null;

        return response()->json([
            'existe' => true,
            'archivos' => $allFiles,
            'ot' => $ot,
            'status' => $historyLatest ? $historyLatest->status : null,
            'tiene_modelo' => $historyLatest ? (bool) $historyLatest->tiene_modelo : false,
            'casting_pdf_generated' => $historyLatest ? (bool) $historyLatest->casting_pdf_generated : false,
            'alert_sent_at' => $historyLatest ? $historyLatest->alert_sent_at?->format('d/m/Y H:i') : null,
            'fecha_entrega' => $fechaEntrega,
        ]);
    }

    // =========================================================================
    // SERVIR ARCHIVOS (Solo Lectura)
    // =========================================================================

    /**
     * Sirve un PDF desde el directorio aislado.
     *
     * @param Request $request
     */
    public function serveFile(Request $request): BinaryFileResponse
    {
        $this->verificarAcceso();

        $ot = $this->sanitizePath($request->query('ot', ''));
        $archivo = $this->sanitizeFileNameWithFolder($request->query('archivo', ''));
        $tipo = $request->query('tipo', 'dibujo');
        $origin = $request->query('origin', '');

        // Normalización y auto-detección de tipo y origin para mayor robustez
        if ($tipo === 'aprobado' || $tipo === 'rechazado') {
            $origin = $tipo;
            $tipo = 'otro';
        }

        $archivoLower = strtolower($archivo);
        if (empty($origin)) {
            if (
                str_contains($archivoLower, 'documentos_aprobados') ||
                str_contains($archivoLower, 'confirmacion') ||
                str_contains($archivoLower, 'preorden_modelo')
            ) {
                $origin = 'aprobado';
            } elseif (
                str_contains($archivoLower, 'documentos_rechazados') ||
                str_contains($archivoLower, 'rechazo') ||
                str_contains($archivoLower, 'scar')
            ) {
                $origin = 'rechazado';
            }
        }

        if (empty($ot) || empty($archivo)) {
            abort(422, 'Parámetros inválidos.');
        }

        $folderName = $this->sanitizePath($this->normalizeOTName($ot));

        // Aplicar filtros de visibilidad según perfil de usuario
        $user = Auth::user();
        if ($user->perfil != 1 && $user->perfil != 2) { // 1 o 2 = Admin (ve todo)
            $history = FundicionHistory::where('ot', '=', $ot, 'and')->first();
            if (!$history) {
                $history = FundicionHistory::where('ot', '=', $folderName, 'and')->first();
            }
            if (!$history) {
                abort(404, 'Historial de OT no encontrado.');
            }

            if ($user->perfil == 4 || $user->perfil == 3) { // 4 = Calidad, 3 = Master
                // Calidad/Master solo ve preordenes no aprobadas si pre_orden_email_sent es true
                $isPreorden = ($tipo === 'otro' || str_starts_with(strtolower($archivo), 'preordenes/'));
                $isAllowedBeforeAlert = str_contains(strtolower($archivo), 'documentos_aprobados') || str_contains(strtolower($archivo), 'documentos_rechazados') || str_contains(strtolower($archivo), 'confirmacion') || str_contains($archivo, 'F-CCL-LDM') || str_contains($archivo, 'SCAR');
                if ($isPreorden && !$isAllowedBeforeAlert && !$history->pre_orden_email_sent) {
                    abort(403, 'Acceso denegado. La pre-orden no ha sido alertada por Almacén.');
                }
            }
        }

        if ($tipo === 'liberacion') {
            $baseDir = 'public/liberaciones_pdf';
        } else {
            if ($tipo === 'ayuda' || $tipo === 'otro') {
                // Archivos en Documentos_Aprobados / Documentos_Rechazados viven en el root de la OT
                if ($origin === 'aprobado' || $origin === 'rechazado') {
                    $baseDir = self::CALIDAD_DIR . '/' . $folderName;
                } elseif (str_contains(strtolower($archivo), 'ayudas_visuales') && (
                    str_starts_with(strtolower($archivo), 'bombillo/') ||
                    str_starts_with(strtolower($archivo), 'fondo/') ||
                    str_starts_with(strtolower($archivo), 'obturador/') ||
                    str_starts_with(strtolower($archivo), 'molde/')
                )) {
                    // Nueva estructura: ayudas_visuales vive en el root de la OT bajo la carpeta de la clase
                    $baseDir = self::CALIDAD_DIR . '/' . $folderName;
                } else {
                    $baseDir = self::CALIDAD_DIR . '/' . $folderName . '/ayudas_visuales';
                }
            } else {
                $baseDir = self::CALIDAD_DIR . '/' . $folderName;
            }
        }

        $baseDir = $this->resolveCaseInsensitivePath($baseDir);

        // Si el directorio principal no existe, intentar fallback cross-OT (base ↔ _R1/_R2)
        if (!Storage::disk('local')->exists($baseDir)) {
            $baseOtRaw = preg_replace('/_(?:(?:candado\s+obturador|cabeza\s+de\s+soplo|obturador|bombillo|embudo|corona|plato|molde|fondo|pistones|guías|guias)(?:_(?:candado\s+obturador|cabeza\s+de\s+soplo|obturador|bombillo|embudo|corona|plato|molde|fondo|pistones|guías|guias))*_)?R\d+$/iu', '', $ot);
            $baseFolder = $this->sanitizePath($this->normalizeOTName($baseOtRaw));
            $altDirs = [
                self::ALMACEN_DIR . '/' . $baseFolder,
                self::CALIDAD_DIR . '/' . $baseFolder,
                self::ALMACEN_DIR . '/' . $folderName,
                self::CALIDAD_DIR . '/' . $folderName,
                self::ALMACEN_DIR . '/' . $baseFolder . '/ayudas_visuales',
                self::CALIDAD_DIR . '/' . $baseFolder . '/ayudas_visuales',
                self::ALMACEN_DIR . '/' . $folderName . '/ayudas_visuales',
                self::CALIDAD_DIR . '/' . $folderName . '/ayudas_visuales',
            ];
            $found = false;
            foreach ($altDirs as $altDir) {
                $resolvedAltDir = $this->resolveCaseInsensitivePath($altDir);
                if (Storage::disk('local')->exists($resolvedAltDir)) {
                    $baseDir = $resolvedAltDir;
                    $found = true;
                    break;
                }
            }
            if (!$found) {
                Log::warning("Directorio no encontrado en Calidad (serveFile). OT: {$ot}, Archivo buscado: {$archivo}. Buscado en múltiples alternativas cross-OT.");
                abort(404, 'Directorio no encontrado.');
            }
        }

        $files = Storage::disk('local')->allFiles($baseDir);
        $foundFile = null;
        $archivoNorm = Normalizer::normalize(mb_strtolower($archivo, 'UTF-8'), Normalizer::FORM_C);
        $archivoBaseLower = mb_strtolower(basename($archivo), 'UTF-8');

        foreach ($files as $f) {
            $fNorm = str_replace('\\', '/', $f);
            $baseDirNorm = str_replace('\\', '/', $baseDir);
            $relName = ltrim(str_replace($baseDirNorm, '', $fNorm), '/');
            
            $utf8RelName = $this->toUtf8($relName);
            $utf8RelNameNorm = Normalizer::normalize(mb_strtolower($utf8RelName, 'UTF-8'), Normalizer::FORM_C);

            if ($utf8RelNameNorm === $archivoNorm || mb_strtolower(basename($f), 'UTF-8') === $archivoBaseLower) {
                if ($tipo === 'dibujo' && strpos($relName, 'ayudas_visuales/') === 0) continue;
                
                $foundFile = $f;
                break;
            }
        }

        // FALLBACK ampliado: buscar en todas las carpetas relacionadas de la OT (base + reprocesos + public/liberaciones_pdf)
        if (!$foundFile) {
            $baseOtRaw = preg_replace('/_(?:(?:candado\s+obturador|cabeza\s+de\s+soplo|obturador|bombillo|embudo|corona|plato|molde|fondo|pistones|guías|guias)(?:_(?:candado\s+obturador|cabeza\s+de\s+soplo|obturador|bombillo|embudo|corona|plato|molde|fondo|pistones|guías|guias))*_)?R\d+$/iu', '', $ot);
            $baseFolder = $this->sanitizePath($this->normalizeOTName($baseOtRaw));
            $possibleDirs = [
                self::ALMACEN_DIR . '/' . $baseFolder,
                self::ALMACEN_DIR . '/' . $folderName,
                self::CALIDAD_DIR . '/' . $baseFolder,
                self::CALIDAD_DIR . '/' . $folderName,
                self::ALMACEN_DIR . '/' . $baseFolder . '/ayudas_visuales',
                self::ALMACEN_DIR . '/' . $folderName . '/ayudas_visuales',
                self::CALIDAD_DIR . '/' . $baseFolder . '/ayudas_visuales',
                self::CALIDAD_DIR . '/' . $folderName . '/ayudas_visuales',
                'public/liberaciones_pdf',
            ];
            foreach ($possibleDirs as $possibleDir) {
                $resolvedPossibleDir = $this->resolveCaseInsensitivePath($possibleDir);
                if ($resolvedPossibleDir === $baseDir) continue;
                if (!Storage::disk('local')->exists($resolvedPossibleDir)) continue;
                
                $pFiles = Storage::disk('local')->allFiles($resolvedPossibleDir);
                foreach ($pFiles as $f) {
                    $fNorm = str_replace('\\', '/', $f);
                    $pDirNorm = str_replace('\\', '/', $resolvedPossibleDir);
                    $relName = ltrim(str_replace($pDirNorm, '', $fNorm), '/');
                    
                    $utf8RelName = $this->toUtf8($relName);
                    $utf8RelNameNorm = Normalizer::normalize(mb_strtolower($utf8RelName, 'UTF-8'), Normalizer::FORM_C);

                    if ($utf8RelNameNorm === $archivoNorm || mb_strtolower(basename($f), 'UTF-8') === $archivoBaseLower) {
                        $foundFile = $f;
                        break 2;
                    }
                }
            }
        }

        if (!$foundFile) {
            Log::warning("Archivo no encontrado en Calidad (serveFile). OT: {$ot}, Archivo buscado: {$archivo}, Directorio Final: {$baseDir}");
            abort(404, 'Archivo no encontrado.');
        }

        /** @var \Illuminate\Filesystem\FilesystemAdapter $disk */
        $disk = Storage::disk('local');
        $fullPath = $disk->path($foundFile);

        $ext = strtolower(pathinfo($fullPath, PATHINFO_EXTENSION));
        $mimeMap = [
            'pdf'  => 'application/pdf',
            'jpg'  => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png'  => 'image/png',
            'gif'  => 'image/gif',
            'webp' => 'image/webp',
        ];
        $mimeType = $mimeMap[$ext] ?? (mime_content_type($fullPath) ?: 'application/octet-stream');

        return response()->file($fullPath, [
            'Content-Type'        => $mimeType,
            'Content-Disposition' => 'inline; filename="' . basename($archivo) . '"',
        ]);
    }

    /**
     * Elimina un archivo PDF de Otros documentos o preordenes.
     *
     * @param Request $request
     */
    public function deleteFile(Request $request)
    {
        $this->verificarAcceso();

        $ot = $this->sanitizePath($request->input('ot', ''));
        $archivo = $this->sanitizeFileNameWithFolder($request->input('archivo', ''));
        $tipo = $request->input('tipo', 'otro');
        $origin = $request->input('origin', '');

        // Normalización y auto-detección de tipo y origin para mayor robustez
        if ($tipo === 'aprobado' || $tipo === 'rechazado') {
            $origin = $tipo;
            $tipo = 'otro';
        }

        $archivoLower = strtolower($archivo);
        if (empty($origin)) {
            if (
                str_contains($archivoLower, 'documentos_aprobados') ||
                str_contains($archivoLower, 'confirmacion') ||
                str_contains($archivoLower, 'preorden_modelo')
            ) {
                $origin = 'aprobado';
            } elseif (
                str_contains($archivoLower, 'documentos_rechazados') ||
                str_contains($archivoLower, 'rechazo') ||
                str_contains($archivoLower, 'scar')
            ) {
                $origin = 'rechazado';
            }
        }

        if (empty($ot) || empty($archivo)) {
            return response()->json(['success' => false, 'error' => 'Parámetros inválidos.'], 422);
        }

        $folderName = $this->sanitizePath($this->normalizeOTName($ot));

        // Aplicar filtros de visibilidad según perfil de usuario
        $user = Auth::user();
        if ($user->perfil != 1 && $user->perfil != 2) { // 1 o 2 = Admin (ve todo)
            $history = FundicionHistory::where('ot', '=', $ot, 'and')->first();
            if (!$history) {
                $history = FundicionHistory::where('ot', '=', $folderName, 'and')->first();
            }
            if (!$history) {
                return response()->json(['success' => false, 'error' => 'Historial de OT no encontrado.'], 404);
            }

            if ($user->perfil == 4 || $user->perfil == 3) { // 4 = Calidad, 3 = Master
                // Calidad/Master solo ve preordenes si pre_orden_email_sent es true
                $isPreorden = ($tipo === 'otro' || str_starts_with(strtolower($archivo), 'preordenes/'));
                $isLdmOrScar = str_contains(strtolower($archivo), 'documentos_aprobados') || str_contains(strtolower($archivo), 'documentos_rechazados') || str_contains($archivo, 'F-CCL-LDM') || str_contains($archivo, 'SCAR');
                if ($isPreorden && !$isLdmOrScar && !$history->pre_orden_email_sent) {
                    return response()->json(['success' => false, 'error' => 'Acceso denegado. La pre-orden no ha sido alertada por Almacén.'], 403);
                }
            }
        }

        if ($tipo === 'liberacion') {
            $baseDir = 'public/liberaciones_pdf';
        } else {
            if ($tipo === 'ayuda' || $tipo === 'otro') {
                // Archivos en Documentos_Aprobados / Documentos_Rechazados viven en el root de la OT
                if ($origin === 'aprobado' || $origin === 'rechazado') {
                    $baseDir = self::CALIDAD_DIR . '/' . $folderName;
                } else {
                    $baseDir = self::CALIDAD_DIR . '/' . $folderName . '/ayudas_visuales';
                }
            } else {
                $baseDir = self::CALIDAD_DIR . '/' . $folderName;
            }
        }

        $baseDir = $this->resolveCaseInsensitivePath($baseDir);

        if (!Storage::disk('local')->exists($baseDir)) {
            // Fallback ampliado incluyendo la OT base para reprocesos
            $baseOtRaw = preg_replace('/_(?:(?:candado\s+obturador|cabeza\s+de\s+soplo|obturador|bombillo|embudo|corona|plato|molde|fondo|pistones|guías|guias)(?:_(?:candado\s+obturador|cabeza\s+de\s+soplo|obturador|bombillo|embudo|corona|plato|molde|fondo|pistones|guías|guias))*_)?R\d+$/iu', '', $ot);
            $baseFolder = $this->sanitizePath($this->normalizeOTName($baseOtRaw));
            $altDirs = [
                self::ALMACEN_DIR . '/' . $baseFolder,
                self::CALIDAD_DIR . '/' . $baseFolder,
                self::ALMACEN_DIR . '/' . $folderName,
                self::CALIDAD_DIR . '/' . $folderName,
                self::ALMACEN_DIR . '/' . $baseFolder . '/ayudas_visuales',
                self::CALIDAD_DIR . '/' . $baseFolder . '/ayudas_visuales',
                self::ALMACEN_DIR . '/' . $folderName . '/ayudas_visuales',
                self::CALIDAD_DIR . '/' . $folderName . '/ayudas_visuales',
            ];
            $found = false;
            foreach ($altDirs as $altDir) {
                $resolvedAltDir = $this->resolveCaseInsensitivePath($altDir);
                if (Storage::disk('local')->exists($resolvedAltDir)) {
                    $baseDir = $resolvedAltDir;
                    $found = true;
                    break;
                }
            }
            if (!$found) {
                return response()->json(['success' => false, 'error' => 'Directorio no encontrado.'], 404);
            }
        }

        $files = Storage::disk('local')->allFiles($baseDir);
        $foundFile = null;
        $archivoNorm = Normalizer::normalize(mb_strtolower($archivo, 'UTF-8'), Normalizer::FORM_C);
        $archivoBaseLower = mb_strtolower(basename($archivo), 'UTF-8');
        foreach ($files as $f) {
            $fNorm = str_replace('\\', '/', $f);
            $baseDirNorm = str_replace('\\', '/', $baseDir);
            $relName = ltrim(str_replace($baseDirNorm, '', $fNorm), '/');
            
            $utf8RelName = $this->toUtf8($relName);
            $utf8RelNameNorm = Normalizer::normalize(mb_strtolower($utf8RelName, 'UTF-8'), Normalizer::FORM_C);
            if ($utf8RelNameNorm === $archivoNorm || mb_strtolower(basename($f), 'UTF-8') === $archivoBaseLower) {
                if ($tipo === 'dibujo' && strpos($relName, 'ayudas_visuales/') === 0) continue;
                
                $foundFile = $f;
                break;
            }
        }

        // FALLBACK ampliado: buscar en todas las carpetas relacionadas de la OT (base + reprocesos + public/liberaciones_pdf)
        if (!$foundFile) {
            $baseOtRaw = preg_replace('/_(?:(?:candado\s+obturador|cabeza\s+de\s+soplo|obturador|bombillo|embudo|corona|plato|molde|fondo|pistones|guías|guias)(?:_(?:candado\s+obturador|cabeza\s+de\s+soplo|obturador|bombillo|embudo|corona|plato|molde|fondo|pistones|guías|guias))*_)?R\d+$/iu', '', $ot);
            $baseFolder = $this->sanitizePath($this->normalizeOTName($baseOtRaw));
            $possibleDirs = [
                self::ALMACEN_DIR . '/' . $baseFolder,
                self::ALMACEN_DIR . '/' . $folderName,
                self::CALIDAD_DIR . '/' . $baseFolder,
                self::CALIDAD_DIR . '/' . $folderName,
                self::ALMACEN_DIR . '/' . $baseFolder . '/ayudas_visuales',
                self::ALMACEN_DIR . '/' . $folderName . '/ayudas_visuales',
                self::CALIDAD_DIR . '/' . $baseFolder . '/ayudas_visuales',
                self::CALIDAD_DIR . '/' . $folderName . '/ayudas_visuales',
                'public/liberaciones_pdf',
            ];
            foreach ($possibleDirs as $possibleDir) {
                $resolvedPossibleDir = $this->resolveCaseInsensitivePath($possibleDir);
                if ($resolvedPossibleDir === $baseDir)
                    continue;
                if (!Storage::disk('local')->exists($resolvedPossibleDir))
                    continue;

                $pFiles = Storage::disk('local')->allFiles($resolvedPossibleDir);
                foreach ($pFiles as $f) {
                    $fNorm = str_replace('\\', '/', $f);
                    $pDirNorm = str_replace('\\', '/', $resolvedPossibleDir);
                    $relName = ltrim(str_replace($pDirNorm, '', $fNorm), '/');

                    $utf8RelName = $this->toUtf8($relName);
                    $utf8RelNameNorm = Normalizer::normalize(mb_strtolower($utf8RelName, 'UTF-8'), Normalizer::FORM_C);

                    if ($utf8RelNameNorm === $archivoNorm || mb_strtolower(basename($f), 'UTF-8') === $archivoBaseLower) {
                        $foundFile = $f;
                        break 2;
                    }
                }
            }
        }

        if (!$foundFile) {
            return response()->json(['success' => false, 'error' => 'Archivo no encontrado.'], 404);
        }

        // Determinar el dueño del archivo y verificar restricciones
        $fNormTemp = str_replace('\\', '/', $foundFile);
        $fileNameOnlyTemp = basename($foundFile);
        
        $fileOwner = 'almacen'; // default
        if (
            str_contains($fNormTemp, 'DOCUMENTACION_GIS/CALIDAD_FUNDICION/') ||
            str_contains($fileNameOnlyTemp, 'F-CCL-LDM') ||
            str_contains($fileNameOnlyTemp, 'F-CCL-SCAR') ||
            str_contains($fileNameOnlyTemp, 'SCAR')
        ) {
            $fileOwner = 'calidad';
        }

        // Validar que el rol del usuario que realiza la petición coincida con el dueño del documento (o sea Admin)
        if ($user->perfil != 1 && $user->perfil != 2 && $user->perfil != 3) { // Admin y Master pueden eliminar cualquier doc
            if ($fileOwner === 'calidad' && $user->perfil != 4) {
                return response()->json(['success' => false, 'error' => 'Acceso denegado. Solo Calidad puede eliminar este documento.'], 403);
            }
            if ($fileOwner === 'almacen' && $user->perfil == 4) {
                return response()->json(['success' => false, 'error' => 'Acceso denegado. Solo Almacén puede eliminar este documento.'], 403);
            }
        }

        // Verificar el estado de la alerta
        $history = FundicionHistory::where('ot', '=', $ot, 'and')->first();
        if (!$history) {
            $history = FundicionHistory::where('ot', '=', $folderName, 'and')->first();
        }

        if ($history && $user->perfil != 1 && $user->perfil != 2) {
            // Se elimina la restricción de 'alerta_enviada' para Calidad
            // a petición expresa de que siempre puedan eliminar sus F-CCL-LDM y SCAR.
        }

        Storage::disk('local')->delete($foundFile);

        // Sincronizar eliminación entre ALMACEN_FUNDICION and CALIDAD_FUNDICION
        $fNorm = str_replace('\\', '/', $foundFile);
        $fileNameOnly = basename($foundFile);

        // 1. Sincronizar carpetas de veredicto calidad (documentos_aprobados / documentos_rechazados / Documentos_Aprobados / Documentos_Rechazados)
        if (
            str_contains($fNorm, '/documentos_aprobados/') || 
            str_contains($fNorm, '/documentos_rechazados/') ||
            str_contains($fNorm, '/Documentos_Aprobados/') || 
            str_contains($fNorm, '/Documentos_Rechazados/')
        ) {
            $otherPath = null;
            if (str_contains($fNorm, 'DOCUMENTACION_GIS/ALMACEN_FUNDICION/')) {
                $otherPath = str_replace('DOCUMENTACION_GIS/ALMACEN_FUNDICION/', 'DOCUMENTACION_GIS/CALIDAD_FUNDICION/', $foundFile);
            } elseif (str_contains($fNorm, 'DOCUMENTACION_GIS/CALIDAD_FUNDICION/')) {
                $otherPath = str_replace('DOCUMENTACION_GIS/CALIDAD_FUNDICION/', 'DOCUMENTACION_GIS/ALMACEN_FUNDICION/', $foundFile);
            }

            if ($otherPath && Storage::disk('local')->exists($otherPath)) {
                Storage::disk('local')->delete($otherPath);
            }
        }

        // 2. Sincronizar archivos generales LDM / SCAR con public/liberaciones_pdf y viceversa
        if (str_contains($fileNameOnly, 'F-CCL-LDM') || str_contains($fileNameOnly, 'F-CCL-SCAR') || str_contains($fileNameOnly, 'SCAR')) {
            if (str_contains($fNorm, 'public/liberaciones_pdf/')) {
                $otClean = $folderName;
                $almacenPath = self::ALMACEN_DIR . '/' . $otClean . '/ayudas_visuales';
                $calidadPath = self::CALIDAD_DIR . '/' . $otClean . '/ayudas_visuales';
                
                if (Storage::disk('local')->exists($almacenPath)) {
                    foreach (Storage::disk('local')->allFiles($almacenPath) as $f) {
                        if (basename($f) === $fileNameOnly) {
                            Storage::disk('local')->delete($f);
                        }
                    }
                }
                if (Storage::disk('local')->exists($calidadPath)) {
                    foreach (Storage::disk('local')->allFiles($calidadPath) as $f) {
                        if (basename($f) === $fileNameOnly) {
                            Storage::disk('local')->delete($f);
                        }
                    }
                }
            } else {
                $publicFile = 'public/liberaciones_pdf/' . $fileNameOnly;
                if (Storage::disk('local')->exists($publicFile)) {
                    Storage::disk('local')->delete($publicFile);
                }
                
                $otherDir = str_contains($fNorm, 'DOCUMENTACION_GIS/ALMACEN_FUNDICION/') ? self::CALIDAD_DIR : self::ALMACEN_DIR;
                $otherPathSearch = $otherDir . '/' . $folderName . '/ayudas_visuales';
                if (Storage::disk('local')->exists($otherPathSearch)) {
                    foreach (Storage::disk('local')->allFiles($otherPathSearch) as $f) {
                        if (basename($f) === $fileNameOnly) {
                            Storage::disk('local')->delete($f);
                        }
                    }
                }
            }
        }

        $this->eliminarCarpetasVacias($folderName);

        return response()->json(['success' => true, 'message' => 'Archivo eliminado correctamente y sincronizado en todos los directorios.']);
    }

    // =========================================================================
    // RUTAS ESPECÍFICAS DE CALIDAD
    // =========================================================================

    public function getLiberacion(Request $request)
    {
        $this->verificarAcceso();

        $ot = $request->query('ot', '');
        if (empty($ot)) {
            return response()->json(['success' => false, 'message' => 'OT requerida.'], 422);
        }

        $liberaciones = LiberacionModeloFundicion::where('ot', '=', $ot, 'and')->get();

        if ($liberaciones->isEmpty()) {
            return response()->json([
                'success'    => true,
                'liberacion' => null,
            ]);
        }

        // Devolver los registros de forma independiente por cada tipo_modelo
        $registrosPorTipo = [];
        $ultimoRegistro = null;
        
        foreach ($liberaciones as $lib) {
            $registrosPorTipo[$lib->tipo_modelo] = $lib;
            $ultimoRegistro = $lib;
        }

        return response()->json([
            'success'            => true,
            'liberacion'         => $ultimoRegistro, // Para retro-compatibilidad inicial o default
            'registros_por_tipo' => $registrosPorTipo,
        ]);
    }

    /**
     * Guarda o actualiza el formulario de liberacion de modelos.
     * El parametro `accion` determina el flujo:
     *   - 'guardar'  => Solo persiste datos, sin cambiar estado ni enviar correo.
     *   - 'aprobar'  => Persiste datos, estado = aprobado, envia correo de aprobacion.
     *   - 'rechazar' => Persiste datos, estado = rechazado, envia correo de alerta.
     */
    public function submitLiberacion(Request $request)
    {
        /** @var \App\Models\User|null $user */
        $user = Auth::user();
        if (!$user || !in_array($user->perfil, ['1', '2', '3', '4'], true)) {
            return response()->json(['success' => false, 'message' => 'Acceso restringido a Calidad.'], 403);
        }

        $ot        = $request->input('ot', '');
        $accion    = $request->input('accion', 'guardar');
        $decision  = $request->input('decision', 'aprobar');

        if (empty($ot)) {
            return response()->json(['success' => false, 'message' => 'OT requerida.'], 422);
        }

        if ($decision === 'rechazar' && empty(trim($request->input('motivo_rechazo', '')))) {
            return response()->json([
                'success' => false,
                'message' => 'El motivo de rechazo es obligatorio al rechazar la liberacion.',
            ], 422);
        }

        $nuevoEstado = match ($accion) {
            'aprobar'  => 'aprobado',
            'rechazar' => 'rechazado',
            default    => 'pendiente',
        };

        /**
         * Sanitizar y normalizar los arrays de medidas enviados desde el formulario.
         * Cada valor numerico se convierte a float. Los campos vacios se guardan como 0.000.
         */
        $sanitizarMedidas = function (?array $grupo): ?array {
            if (empty($grupo)) return null;
            $resultado = [];
            foreach ($grupo as $item => $cols) {
                if (!is_array($cols)) continue;
                foreach ($cols as $col => $val) {
                    if ($val !== null && $val !== '') {
                        $valStr = (string)$val;
                        if (strpos($valStr, '.') !== false) {
                            $parts = explode('.', $valStr);
                            $integerPart = $parts[0];
                            $decimalPart = substr($parts[1], 0, 3);
                            $valStr = $integerPart . '.' . $decimalPart;
                        }
                        $resultado[$item][$col] = (float)$valStr;
                    } else {
                        $resultado[$item][$col] = 0.000;
                    }
                }
            }
            return $resultado;
        };

        $tipo = $request->input('tipo_modelo');
        $campos = [
            'estado'                  => $nuevoEstado,
            'tipo_modelo'             => $tipo,
            'medidas_modelo'          => in_array($tipo, ['Molde', 'Bombillo']) ? $sanitizarMedidas($request->input('modelo')) : null,
            'medidas_plantilla'       => in_array($tipo, ['Molde', 'Bombillo']) ? $sanitizarMedidas($request->input('plantilla')) : null,
            'medidas_fondo'           => in_array($tipo, ['Fondo', 'Corona', 'Plato', 'Embudo', 'Cabeza de Soplo', 'Candado Obturador', 'Pistones', 'Guías', 'Guias']) ? $sanitizarMedidas($request->input('fondo')) : null,
            'medidas_obturador'       => $tipo === 'Obturador' ? $sanitizarMedidas($request->input('obturador')) : null,
            'observaciones_modelo'    => in_array($tipo, ['Molde', 'Bombillo']) ? $request->input('observaciones_modelo') : null,
            'observaciones_plantilla' => in_array($tipo, ['Molde', 'Bombillo']) ? $request->input('observaciones_plantilla') : null,
            'observaciones_fondo'     => in_array($tipo, ['Fondo', 'Corona', 'Plato', 'Embudo', 'Cabeza de Soplo', 'Candado Obturador', 'Pistones', 'Guías', 'Guias']) ? $request->input('observaciones_fondo') : null,
            'observaciones_obturador' => $tipo === 'Obturador' ? $request->input('observaciones_obturador') : null,
            'motivo_rechazo'          => $accion === 'rechazar' ? $request->input('motivo_rechazo') : null,
            'user_id_calidad'         => $user->id,
            'user_nombre_calidad'     => $user->name,
            'fecha_revision'          => in_array($accion, ['aprobar', 'rechazar']) ? now() : null,
        ];

        // Requerimiento 2: Actualizar SOLO los campos del tipo activo.
        // Intentar obtener el registro existente para este tipo y OT
        $liberacion = LiberacionModeloFundicion::where('ot', '=', $ot, 'and')->where('tipo_modelo', '=', $tipo, 'and')->first();

        if (!$liberacion) {
            // Si existe un registro inicial (tipo_modelo = null), lo actualizamos.
            $liberacionInicial = LiberacionModeloFundicion::where('ot', '=', $ot, 'and')->whereNull('tipo_modelo', 'and')->first();
            if ($liberacionInicial) {
                $liberacionInicial->update(['tipo_modelo' => $tipo]);
                $liberacion = $liberacionInicial;
            } else {
                $liberacion = LiberacionModeloFundicion::create([
                    'ot'          => $ot,
                    'tipo_modelo' => $tipo,
                    'estado'      => 'pendiente',
                ]);
            }
        }

        // Construir el arreglo de actualizacion con solo los campos pertinentes al tipo
        $actualizacion = [
            'estado'      => $nuevoEstado,
            'decision'    => $decision,
            'user_id_calidad'     => $user->id,
            'user_nombre_calidad' => $user->name,
        ];
        if (in_array($accion, ['aprobar', 'rechazar'])) {
            $actualizacion['fecha_revision'] = now();
        }
        // Solo toca los campos del tipo seleccionado
        if (in_array($tipo, ['Molde', 'Bombillo'])) {
            $actualizacion['medidas_modelo']       = $sanitizarMedidas($request->input('modelo'));
            $actualizacion['observaciones_modelo'] = $request->input('observaciones_modelo');
            $actualizacion['medidas_plantilla']       = $sanitizarMedidas($request->input('plantilla'));
            $actualizacion['observaciones_plantilla'] = $request->input('observaciones_plantilla');
        } elseif (in_array($tipo, ['Fondo', 'Corona', 'Plato', 'Embudo', 'Cabeza de Soplo', 'Candado Obturador', 'Pistones', 'Guías', 'Guias'])) {
            $actualizacion['medidas_fondo']       = $sanitizarMedidas($request->input('fondo'));
            $actualizacion['observaciones_fondo'] = $request->input('observaciones_fondo');
        } elseif ($tipo === 'Obturador') {
            $actualizacion['medidas_obturador']       = $sanitizarMedidas($request->input('obturador'));
            $actualizacion['observaciones_obturador'] = $request->input('observaciones_obturador');
        }
        if ($nuevoEstado === 'aprobado' || ($accion === 'guardar' && $decision === 'aprobar')) {
            $actualizacion['motivo_rechazo'] = null;
        } elseif ($request->has('motivo_rechazo')) {
            $actualizacion['motivo_rechazo'] = $request->input('motivo_rechazo');
        }

        $liberacion->update($actualizacion);
        $liberacion->refresh();

        $pdfUrl      = null;
        $pdfFilename = null;
        // Generar y guardar PDF en orientacion horizontal
        try {
            // Nombre estetico: Formato_LDM_[APROBADO/RECHAZADO]_[Clase]_[OT]_[Fecha].pdf
            $otSanitizada = preg_replace('/[^\w\s\-]/', '', $ot);
            $otSanitizada = preg_replace('/[\s]+/', '_', trim($otSanitizada));
            $tipoLabel    = $tipo ? mb_convert_case(trim($tipo), MB_CASE_TITLE, 'UTF-8') : 'Modelo';
            $fmtCode     = ($decision === 'aprobar') ? 'LDM' : 'RDM';
            $pdfFilename = "F_CCL_{$fmtCode}_{$tipoLabel}_{$otSanitizada}.pdf";
            $pdfPath = storage_path("app/public/liberaciones_pdf");
            $isAprobar = ($decision === 'aprobar');
            $tipoNorm = mb_strtolower(trim($tipo), 'UTF-8');
            $tipoNormAlt = str_replace(['í', 'gú'], ['i', 'gu'], $tipoNorm);
            $tipoNormClean = str_replace([' ', '_', '-'], '', $tipoNorm);
            $tipoNormAltClean = str_replace([' ', '_', '-'], '', $tipoNormAlt);

            if (!file_exists($pdfPath)) {
                mkdir($pdfPath, 0755, true);
            } else {
                // Eliminar PDFs anteriores para esta clase en liberaciones_pdf
                foreach (glob("{$pdfPath}/*.pdf") ?: [] as $oldFile) {
                    $fBase = mb_strtolower(basename($oldFile), 'UTF-8');
                    $fBaseClean = str_replace([' ', '_', '-'], '', $fBase);
                    
                    // Si se aprueba la clase, eliminar LDM, RDM y SCAR anterior. Si se rechaza, eliminar LDM y RDM anterior.
                    $matchesDocType = $isAprobar
                        ? (str_contains($fBase, 'ldm') || str_contains($fBase, 'rdm') || str_contains($fBase, 'scar'))
                        : (str_contains($fBase, 'ldm') || str_contains($fBase, 'rdm'));
                        
                    $matchesClass = str_contains($fBase, $tipoNorm) || str_contains($fBase, $tipoNormAlt) ||
                                    str_contains($fBaseClean, $tipoNormClean) || str_contains($fBaseClean, $tipoNormAltClean);

                    if ($matchesDocType && $matchesClass) {
                        $otSan = mb_strtolower($otSanitizada, 'UTF-8');
                        if (str_contains($fBase, $otSan) || !str_contains($fBase, 'ot_')) {
                            @unlink($oldFile);
                        }
                    }
                }
            }
            ini_set('memory_limit', '2048M');
            $hasRechazo = ($nuevoEstado === 'rechazado') || 
                           ($nuevoEstado === 'pendiente' && $decision === 'rechazar');
            $viewName = $hasRechazo ? 'almacen.pdf.rejection_pdf' : 'almacen.pdf.release_pdf';
            $pdf = Pdf::loadView($viewName, ['liberacion' => $liberacion])
                      ->setPaper('letter', 'landscape');
            $pdf->save("{$pdfPath}/{$pdfFilename}");
            $pdfUrl = asset('storage/liberaciones_pdf/' . $pdfFilename);
            $liberacion->update(['pdf_filename' => $pdfFilename]);

            // Copiar a la carpeta de la OT en Calidad
            $folderName = $this->sanitizePath($this->normalizeOTName($ot));
            $basePath = self::CALIDAD_DIR . '/' . $folderName;
            
            $claseClean = strtoupper(trim(preg_replace('/^modelo\s+/i', '', strtolower($tipo))));
            if (empty($claseClean)) {
                $claseClean = 'GENERAL';
            }
            
            FundicionPaths::crearEstructuraClase($folderName, $claseClean, self::CALIDAD_DIR);
            if ($decision === 'aprobar') {
                $otPath = self::CALIDAD_DIR . '/' . $folderName . '/' . $claseClean . '/' . FundicionPaths::FORMATOS_LIBERACION;
            } else {
                $otPath = self::CALIDAD_DIR . '/' . $folderName . '/' . $claseClean . '/' . FundicionPaths::DOCUMENTOS_RECHAZADOS . '/' . FundicionPaths::CALIDAD;
            }
            
            // Eliminar versiones previas de esta clase/modelo en toda la OT (LDM, RDM y si fue aprobada también SCAR)
            if (Storage::disk('local')->exists($basePath)) {
                $allOtFiles = Storage::disk('local')->allFiles($basePath);
                foreach ($allOtFiles as $f) {
                    $fBase = mb_strtolower(basename($f), 'UTF-8');
                    $fBaseClean = str_replace([' ', '_', '-'], '', $fBase);
                    
                    $matchesDocType = $isAprobar
                        ? (str_contains($fBase, 'ldm') || str_contains($fBase, 'rdm') || str_contains($fBase, 'scar'))
                        : (str_contains($fBase, 'ldm') || str_contains($fBase, 'rdm'));
                        
                    $matchesClass = str_contains($fBase, $tipoNorm) || str_contains($fBase, $tipoNormAlt) ||
                                    str_contains($fBaseClean, $tipoNormClean) || str_contains($fBaseClean, $tipoNormAltClean);

                    if ($matchesDocType && $matchesClass) {
                        Storage::disk('local')->delete($f);
                    }
                }
            }

            // Si se aprueba la clase, eliminar también registro de SCAR en la BD si existía para esta OT y clase
            if ($isAprobar) {
                ScarModelo::where('ot', '=', $ot)
                    ->where(function($q) use ($tipo, $tipoNorm, $tipoNormAlt) {
                        $q->where('tipo_modelo', '=', $tipo)
                          ->orWhereRaw("LOWER(tipo_modelo) = ?", [$tipoNorm])
                          ->orWhereRaw("LOWER(tipo_modelo) = ?", [$tipoNormAlt]);
                    })
                    ->delete();
            }

            if (!Storage::disk('local')->exists($otPath)) {
                Storage::disk('local')->makeDirectory($otPath);
            }
            Storage::disk('local')->put($otPath . '/' . $pdfFilename, file_get_contents("{$pdfPath}/{$pdfFilename}"));
            $this->eliminarCarpetasVacias($ot);
        } catch (\Exception $e) {
            Log::error('Error al generar PDF de liberacion: ' . $e->getMessage());
        }

        // Actualizar calidad_revision_status en fundicion_history para todos los flujos
        
        // Calcular estado global consolidado para la OT
        $historial = FundicionHistory::where('ot', '=', $ot, 'and')->first();
        $estadoGlobal = $nuevoEstado;
        if ($historial) {
            $clasesRequeridas = [];
            
            // 1. Intentar obtener de ayudas_config (Ayudas Visuales configuradas en la OT)
            if (!empty($historial->ayudas_config)) {
                $configs = is_string($historial->ayudas_config) ? json_decode($historial->ayudas_config, true) : $historial->ayudas_config;
                if (is_array($configs)) {
                    foreach ($configs as $val) {
                        $val = strtolower($val);
                        if (str_contains($val, 'opcional') && !str_contains($val, 'pistones') && !str_contains($val, 'guías') && !str_contains($val, 'guias')) continue;
                        foreach (['candado obturador', 'cabeza de soplo', 'obturador', 'bombillo', 'embudo', 'corona', 'plato', 'molde', 'fondo', 'pistones', 'guías', 'guias'] as $kc) {
                            if (strpos($val, $kc) !== false) {
                                $clasesRequeridas[] = ucfirst($kc);
                            }
                        }
                    }
                }
            }
            
            // 2. Si no hay ayudas_config, intentar obtener de las filas de la pre-orden
            if (empty($clasesRequeridas)) {
                $preOrden = PreOrdenFundicion::where('ot', '=', $ot, 'and')->first();
                if ($preOrden && !empty($preOrden->filas)) {
                    $filas = $preOrden->filas;
                    if (is_string($filas)) {
                        $filas = json_decode($filas, true);
                    }
                    if (is_array($filas)) {
                        foreach ($filas as $fila) {
                            $val = strtolower($fila['clase'] ?? ($fila['clase_nombre'] ?? ''));
                            foreach (['candado obturador', 'cabeza de soplo', 'obturador', 'bombillo', 'embudo', 'corona', 'plato', 'molde', 'fondo', 'pistones', 'guías', 'guias'] as $kc) {
                                if (strpos($val, $kc) !== false) {
                                    $clasesRequeridas[] = ucfirst($kc);
                                    break;
                                }
                            }
                        }
                    }
                }
            }
            
            // 3. Fallback a todas si no se pudo determinar
            if (empty($clasesRequeridas)) {
                $clasesRequeridas = ['Candado obturador', 'Cabeza de soplo', 'Obturador', 'Bombillo', 'Embudo', 'Corona', 'Plato', 'Molde', 'Fondo', 'Pistones', 'Guías', 'Guias'];
            }
            $clasesRequeridas = array_unique($clasesRequeridas);
            
            $liberaciones = LiberacionModeloFundicion::where('ot', '=', $ot, 'and')
                ->whereIn('tipo_modelo', $clasesRequeridas)
                ->get();
                
            $todasAprobadas = true;
            $algunaRechazada = false;
            $faltantes = count($clasesRequeridas) - $liberaciones->count();

            foreach ($liberaciones as $lib) {
                if ($lib->decision !== 'aprobar') {
                    $todasAprobadas = false;
                }
                if ($lib->decision === 'rechazar') {
                    $algunaRechazada = true;
                }
            }

            if ($faltantes > 0) {
                // Aún faltan clases por evaluar, el estado general de borrador sigue pendiente
                $estadoGlobal = 'pendiente';
            } else {
                if ($algunaRechazada) {
                    // Si hay rechazos y faltan por revisar (cubierto arriba), o unas aprobadas y otras rechazadas
                    $estadoGlobal = 'mixto';
                    // Si TODAS están rechazadas y no faltan
                    if (count($liberaciones->where('decision', 'rechazar')) == count($clasesRequeridas)) {
                        $estadoGlobal = 'rechazado';
                    }
                } else {
                    if ($todasAprobadas && count($clasesRequeridas) > 0) {
                        $estadoGlobal = 'aprobado';
                    } else {
                        $estadoGlobal = 'pendiente';
                    }
                }
            }
        }

        FundicionHistory::where('ot', '=', $ot, 'and')
            ->update(['calidad_revision_status' => $estadoGlobal]);

        // Registrar auditoría de Liberación / Rechazo
        try {
            LiberacionLog::create([
                'ot'           => $ot,
                'tipo_modelo'  => $tipo,
                'accion'       => $accion,          // 'guardar' | 'aprobar' | 'rechazar'
                'pdf_filename' => $pdfFilename,
                'estado_global' => $estadoGlobal,
                'user_id'      => $user->id,
                'user_nombre'  => $user->name,
            ]);

            // Si es un rechazo, también registrar en rechazo_logs
            if ($accion === 'rechazar') {
                RechazoLog::create([
                    'ot'             => $ot,
                    'tipo_modelo'    => $tipo,
                    'accion'         => 'generar',
                    'pdf_filename'   => $pdfFilename,
                    'motivo_rechazo' => $request->input('motivo_rechazo'),
                    'user_id'        => $user->id,
                    'user_nombre'    => $user->name,
                ]);
            }
        } catch (\Exception $logEx) {
            Log::warning('Error al registrar log de liberación: ' . $logEx->getMessage());
        }

        return response()->json([
            'success'      => true,
            'message'      => $decision === 'rechazar' ? 'Rechazo registrado con éxito.' : 'Informacion guardada correctamente.',
            'pdf_url'      => $pdfUrl,
            'pdf_filename' => $pdfFilename,
            'nuevo_estado' => $nuevoEstado,
            'ot'           => $ot,
        ]);
    }

    private function updateScarPdf(ScarModelo $scar)
    {
        $ot = $scar->ot;
        $tipoModelo = $scar->tipo_modelo;
        
        $clases = array_map('trim', explode(',', $tipoModelo));
        $clasesCleaned = array_values(array_filter(array_map(fn($c) => ucfirst(trim($c)), $clases)));
        $clasesTag = count($clasesCleaned) > 0 ? implode('-', $clasesCleaned) : 'Modelo';
        
        $otSanitizada = preg_replace('/[^\w\s\-]/', '', $ot);
        $otSanitizada = preg_replace('/[\s]+/', '_', trim($otSanitizada));
        $pdfDir       = storage_path('app/public/liberaciones_pdf');
        if (!file_exists($pdfDir)) {
            mkdir($pdfDir, 0755, true);
        }
        
        // Borrar PDFs viejos de SCAR
        foreach (glob("{$pdfDir}/*SCAR_*{$otSanitizada}*.pdf") as $old) {
            @unlink($old);
        }
        
        $folderName = $this->sanitizePath($this->normalizeOTName($ot));
        
        // Borrar viejos SCARs en la nueva estructura
        foreach ($clases as $clase) {
            $claseClean = strtoupper(trim(preg_replace('/^modelo\s+/i', '', strtolower($clase))));
            if (empty($claseClean)) $claseClean = 'GENERAL';
            
            $destClassPath = self::CALIDAD_DIR . '/' . $folderName . '/' . $claseClean . '/' . FundicionPaths::DOCUMENTOS_RECHAZADOS . '/' . FundicionPaths::CALIDAD;
            if (Storage::disk('local')->exists($destClassPath)) {
                foreach (Storage::disk('local')->files($destClassPath) as $sf) {
                    if (str_contains(basename($sf), 'SCAR')) {
                        Storage::disk('local')->delete($sf);
                    }
                }
            }
        }
        
        ini_set('memory_limit', '2048M');
        $pdf = Pdf::loadView('almacen.pdf.scar_pdf', ['scar' => $scar])
                  ->setPaper('letter', 'portrait');
                  
        $pdfFilename = "F_CCL_SCAR_{$clasesTag}_{$otSanitizada}.pdf";
        $pdf->save("{$pdfDir}/{$pdfFilename}");
        
        foreach ($clases as $clase) {
            $claseClean = strtoupper(trim(preg_replace('/^modelo\s+/i', '', strtolower($clase))));
            if (empty($claseClean)) $claseClean = 'GENERAL';
            $cTitle = ucfirst(trim($clase));
            
            FundicionPaths::crearEstructuraClase($folderName, $claseClean, self::CALIDAD_DIR);
            $destClassPath = self::CALIDAD_DIR . '/' . $folderName . '/' . $claseClean . '/' . FundicionPaths::DOCUMENTOS_RECHAZADOS . '/' . FundicionPaths::CALIDAD;
            if (!Storage::disk('local')->exists($destClassPath)) {
                Storage::disk('local')->makeDirectory($destClassPath);
            }
            
            $classPdfFilename = "F_CCL_SCAR_{$cTitle}_{$otSanitizada}.pdf";
            Storage::disk('local')->put($destClassPath . '/' . $classPdfFilename, file_get_contents("{$pdfDir}/{$pdfFilename}"));
        }
    }

    public function generateScar(Request $request)
    {
        /** @var \App\Models\User|null $user */
        $user = Auth::user();
        if (!$user || !in_array($user->perfil, ['1', '2', '3', '4'], true)) {
            return response()->json(['success' => false, 'message' => 'Acceso restringido a Calidad.'], 403);
        }

        $ot     = $request->input('ot', '');
        $accion = $request->input('accion', 'guardar'); // 'guardar' | 'enviar'

        if (empty($ot)) {
            return response()->json(['success' => false, 'message' => 'OT requerida.'], 422);
        }

        $tipoModelo = $request->input('tipo_modelo') ?: '';

        // Recuperar la liberacion para obtener el motivo de rechazo guardado en BD
        $liberacion = LiberacionModeloFundicion::where('ot', '=', $ot, 'and')
            ->where('tipo_modelo', '=', $tipoModelo)
            ->first();

        if (!$liberacion) {
            $liberacion = LiberacionModeloFundicion::where('ot', '=', $ot, 'and')
                ->whereIn('estado', ['rechazado', 'pendiente'])
                ->latest()
                ->first();
        }

        $existingScar = ScarModelo::where('ot', '=', $ot, 'and')
            ->where('tipo_modelo', '=', $tipoModelo, 'and')
            ->first();
        if ($existingScar) {
            $noScar = $existingScar->no_scar;
        } else {
            $scarFolioPath = 'DOCUMENTACION_GIS/scar_folio_config.json';
            $nextScarFolio = 1;

            if (Storage::disk('local')->exists($scarFolioPath)) {
                try {
                    $config = json_decode(Storage::disk('local')->get($scarFolioPath), true);
                    $nextScarFolio = $config['next_folio'] ?? 1;
                } catch (\Exception $e) {
                    $config = ['next_folio' => 1];
                }
            } else {
                $config = ['next_folio' => 1];
            }

            $noScar = 'F-SDM-' . date('Ymd') . '-' . str_pad($nextScarFolio, 4, '0', STR_PAD_LEFT);

            $config['next_folio'] = $nextScarFolio + 1;
            Storage::disk('local')->put($scarFolioPath, json_encode($config));
        }

        // Construir objeto de datos para la vista del SCAR
        $scarData = (object) [
            'ot'                         => $ot,
            'no_scar'                    => $noScar,
            'proveedor'                  => $request->input('proveedor', 'SS Metal Foundry, S. de R.L. de C.V.'),
            'descripcion_no_conformidad' => $request->input('descripcion_no_conformidad')
                                           ?: ($liberacion?->motivo_rechazo ?? ''),
            'causa_raiz'                 => $request->input('causa_raiz', ''),
            'acciones_correctivas'       => $request->input('acciones_correctivas', ''),
            'fecha_emision'              => now(),
            'fecha_compromiso'           => null,
            'codigo_modelo'              => $request->input('codigo_modelo', ''),
            'user_nombre_calidad'        => $user->name,
            'inspector'                  => $user->name,
            'created_at'                 => now(),
            
            // Nuevos campos
            'cliente_empresa'            => $request->input('cliente_empresa', 'Industrial Saavedra'),
            'area_solicitante'           => $request->input('area_solicitante', 'Calidad'),
            'nombre_solicitante'         => $request->input('nombre_solicitante', $user->name),
            'nombre_moldura'             => $request->input('nombre_moldura', ''),
            
            'evidencia_reporte'          => $request->boolean('evidencia_reporte', true),
            'evidencia_dibujos'          => $request->boolean('evidencia_dibujos', false),
            'evidencia_ayudas'           => $request->boolean('evidencia_ayudas', false),
            'evidencia_fotos'            => $request->boolean('evidencia_fotos', false),
            'evidencia_otro'             => $request->boolean('evidencia_otro', false),
            
            'accion_regreso'             => $request->boolean('accion_regreso', false),
            'accion_fabricacion'         => $request->boolean('accion_fabricacion', false),
            'accion_otro'                => $request->boolean('accion_otro', false),
            'accion_otro_texto'          => $request->input('accion_otro_texto', ''),
        ];

        try {
            // ── Generar el PDF ──────────────────────────────────────────
            $clasesStr = $request->input('tipo_modelo') ?: ($liberacion?->tipo_modelo ?? 'general');
            $clases = array_map('trim', explode(',', $clasesStr));
            $clasesCleaned = array_values(array_filter(array_map(fn($c) => ucfirst(trim($c)), $clases)));
            $clasesTag = count($clasesCleaned) > 0 ? implode('-', $clasesCleaned) : 'Modelo';
            
            // Reemplazar SCAR anterior de la misma OT en el disco
            $otSanitizada = preg_replace('/[^\w\s\-]/', '', $ot);
            $otSanitizada = preg_replace('/[\s]+/', '_', trim($otSanitizada));
            $fechaStamp   = date('d_m_Y_H_i');
            $pdfDir       = storage_path('app/public/liberaciones_pdf');
            if (!file_exists($pdfDir)) {
                mkdir($pdfDir, 0755, true);
            }
            foreach (glob("{$pdfDir}/*SCAR_*{$otSanitizada}*.pdf") as $old) {
                @unlink($old);
            }

            // También borrar en la carpeta de la nueva estructura
            $folderName = $this->sanitizePath($this->normalizeOTName($ot));
            foreach ($clases as $clase) {
                $claseClean = strtoupper(trim(preg_replace('/^modelo\s+/i', '', strtolower($clase))));
                if (empty($claseClean)) $claseClean = 'GENERAL';
                
                $destClassPath = self::CALIDAD_DIR . '/' . $folderName . '/' . $claseClean . '/' . FundicionPaths::DOCUMENTOS_RECHAZADOS . '/' . FundicionPaths::CALIDAD;
                if (Storage::disk('local')->exists($destClassPath)) {
                    foreach (Storage::disk('local')->files($destClassPath) as $sf) {
                        if (str_contains(basename($sf), 'SCAR')) {
                            Storage::disk('local')->delete($sf);
                        }
                    }
                }
            }

            ini_set('memory_limit', '2048M');
            $pdf = Pdf::loadView('almacen.pdf.scar_pdf', ['scar' => $scarData])
                      ->setPaper('letter', 'portrait');

            $pdfFilename = "F_CCL_SCAR_{$clasesTag}_{$otSanitizada}.pdf";
            $pdf->save("{$pdfDir}/{$pdfFilename}");
            $pdfUrl = asset('storage/liberaciones_pdf/' . $pdfFilename);

            // Ahora copiamos a cada subcarpeta de clase en ayudas_visuales
            foreach ($clases as $clase) {
                $claseClean = strtoupper(trim(preg_replace('/^modelo\s+/i', '', strtolower($clase))));
                if (empty($claseClean)) $claseClean = 'GENERAL';
                $cTitle = ucfirst(trim($clase));
                
                FundicionPaths::crearEstructuraClase($folderName, $claseClean, self::CALIDAD_DIR);
                $destClassPath = self::CALIDAD_DIR . '/' . $folderName . '/' . $claseClean . '/' . FundicionPaths::DOCUMENTOS_RECHAZADOS . '/' . FundicionPaths::CALIDAD;
                if (!Storage::disk('local')->exists($destClassPath)) {
                    Storage::disk('local')->makeDirectory($destClassPath);
                }
                
                $classPdfFilename = "F_CCL_SCAR_{$cTitle}_{$otSanitizada}.pdf";
                Storage::disk('local')->put($destClassPath . '/' . $classPdfFilename, file_get_contents("{$pdfDir}/{$pdfFilename}"));
            }

            // Guardar fotografías si se adjuntaron
            if ($request->hasFile('fotos')) {
                foreach ($request->file('fotos') as $idx => $foto) {
                    $num = $idx + 1;
                    $ext = $foto->getClientOriginalExtension() ?: 'jpg';
                    $fname = "F_CCL_SCAR_FOTO-{$num}_{$clasesTag}_{$otSanitizada}.{$ext}";
                    $fotoContent = file_get_contents($foto->getRealPath());

                    foreach ($clases as $clase) {
                        $claseClean = strtoupper(trim(preg_replace('/^modelo\s+/i', '', strtolower($clase))));
                        if (empty($claseClean)) $claseClean = 'GENERAL';
                        
                        // Guardar en Calidad
                        FundicionPaths::crearEstructuraClase($folderName, $claseClean, self::CALIDAD_DIR);
                        $fotosPathCalidad = self::CALIDAD_DIR . '/' . $folderName . '/' . $claseClean . '/' . FundicionPaths::DOCUMENTOS_RECHAZADOS . '/' . FundicionPaths::EXTRAS;
                        if (!Storage::disk('local')->exists($fotosPathCalidad)) {
                            Storage::disk('local')->makeDirectory($fotosPathCalidad);
                        }
                        Storage::disk('local')->put($fotosPathCalidad . '/' . $fname, $fotoContent);

                        // Guardar en Almacen
                        FundicionPaths::crearEstructuraClase($folderName, $claseClean, FundicionPaths::ALMACEN_ROOT);
                        $fotosPathAlmacen = FundicionPaths::ALMACEN_ROOT . '/' . $folderName . '/' . $claseClean . '/' . FundicionPaths::DOCUMENTOS_RECHAZADOS . '/' . FundicionPaths::EXTRAS;
                        if (!Storage::disk('local')->exists($fotosPathAlmacen)) {
                            Storage::disk('local')->makeDirectory($fotosPathAlmacen);
                        }
                        Storage::disk('local')->put($fotosPathAlmacen . '/' . $fname, $fotoContent);
                    }
                }
            }

            // Guardar otros archivos si se adjuntaron
            if ($request->hasFile('otros_archivos')) {
                foreach ($request->file('otros_archivos') as $idx => $archivo) {
                    $num = $idx + 1;
                    $ext = $archivo->getClientOriginalExtension() ?: 'pdf';
                    $fname = "F_CCL_SCAR_PDF-{$num}_{$clasesTag}_{$otSanitizada}.{$ext}";
                    $archivoContent = file_get_contents($archivo->getRealPath());

                    foreach ($clases as $clase) {
                        $claseClean = strtoupper(trim(preg_replace('/^modelo\s+/i', '', strtolower($clase))));
                        if (empty($claseClean)) $claseClean = 'GENERAL';
                        
                        // Guardar en Calidad
                        FundicionPaths::crearEstructuraClase($folderName, $claseClean, self::CALIDAD_DIR);
                        $otrosPathCalidad = self::CALIDAD_DIR . '/' . $folderName . '/' . $claseClean . '/' . FundicionPaths::DOCUMENTOS_RECHAZADOS . '/' . FundicionPaths::EXTRAS;
                        if (!Storage::disk('local')->exists($otrosPathCalidad)) {
                            Storage::disk('local')->makeDirectory($otrosPathCalidad);
                        }
                        Storage::disk('local')->put($otrosPathCalidad . '/' . $fname, $archivoContent);

                        // Guardar en Almacen
                        FundicionPaths::crearEstructuraClase($folderName, $claseClean, FundicionPaths::ALMACEN_ROOT);
                        $otrosPathAlmacen = FundicionPaths::ALMACEN_ROOT . '/' . $folderName . '/' . $claseClean . '/' . FundicionPaths::DOCUMENTOS_RECHAZADOS . '/' . FundicionPaths::EXTRAS;
                        if (!Storage::disk('local')->exists($otrosPathAlmacen)) {
                            Storage::disk('local')->makeDirectory($otrosPathAlmacen);
                        }
                        Storage::disk('local')->put($otrosPathAlmacen . '/' . $fname, $archivoContent);
                    }
                }
            }

            // ── Guardar datos en BD (Tabla: scar_modelos) ───────────────────
            ScarModelo::updateOrCreate(
                [
                    'ot'          => $ot,
                    'tipo_modelo' => $tipoModelo ?: ($request->input('tipo_modelo') ?: ($liberacion?->tipo_modelo ?? ''))
                ],
                [
                    'no_scar'                    => $scarData->no_scar,
                    'codigo_modelo'              => $scarData->codigo_modelo,
                    'proveedor'                  => $scarData->proveedor,
                    'descripcion_no_conformidad' => $scarData->descripcion_no_conformidad,
                    'causa_raiz'                 => $scarData->causa_raiz,
                    'acciones_correctivas'       => $scarData->acciones_correctivas,
                    'fecha_emision'              => $scarData->fecha_emision ?: null,
                    'fecha_compromiso'           => null,
                    'estatus'                    => 'abierto',
                    'user_id'                    => $user->id,
                    'user_nombre'                => $user->name,
                    'pdf_filename'               => $pdfFilename,
                    'cliente_empresa'            => $scarData->cliente_empresa,
                    'area_solicitante'           => $scarData->area_solicitante,
                    'nombre_solicitante'         => $scarData->nombre_solicitante,
                    'nombre_moldura'             => $scarData->nombre_moldura,
                    'evidencia_reporte'          => $scarData->evidencia_reporte,
                    'evidencia_dibujos'          => $scarData->evidencia_dibujos,
                    'evidencia_ayudas'           => $scarData->evidencia_ayudas,
                    'evidencia_fotos'            => $scarData->evidencia_fotos,
                    'evidencia_otro'             => $scarData->evidencia_otro,
                    'accion_regreso'             => $scarData->accion_regreso,
                    'accion_fabricacion'         => $scarData->accion_fabricacion,
                    'accion_otro'                => $scarData->accion_otro,
                    'accion_otro_texto'          => $scarData->accion_otro_texto,
                ]
            );

            // Guardar y generar: solo devolver URL del PDF
            $this->eliminarCarpetasVacias($ot);

            // Registrar auditoría de generación de SCAR
            try {
                ScarLog::create([
                    'ot'           => $ot,
                    'tipo_modelo'  => $tipoModelo,
                    'no_scar'      => $scarData->no_scar ?? null,
                    'accion'       => 'generar',
                    'pdf_filename' => $pdfFilename,
                    'proveedor'    => $scarData->proveedor ?? null,
                    'user_id'      => $user->id,
                    'user_nombre'  => $user->name,
                ]);
            } catch (\Exception $logEx) {
                Log::warning('Error al registrar log de SCAR: ' . $logEx->getMessage());
            }

            return response()->json([
                'success'      => true,
                'message'      => 'SCAR generado exitosamente.',
                'pdf_url'      => $pdfUrl,
                'pdf_filename' => $pdfFilename,
            ]);

         } catch (\Exception $e) {
            Log::error('Error al generar SCAR: ' . $e->getMessage());
            $this->eliminarCarpetasVacias($ot);
            return response()->json([
                'success' => false,
                'message' => 'Error al generar el SCAR: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function sendScarAlert(Request $request)
    {
        /** @var \App\Models\User|null $user */
        $user = Auth::user();
        if (!$user || !in_array($user->perfil, ['1', '2', '3', '4'], true)) {
            return response()->json(['success' => false, 'message' => 'Acceso restringido a Calidad.'], 403);
        }

        $ot              = $request->input('ot', '');
        $fechaCompromiso = $request->input('fecha_compromiso', '');

        if (empty($ot)) {
            return response()->json(['success' => false, 'message' => 'OT requerida.'], 422);
        }

        if (empty($fechaCompromiso)) {
            return response()->json(['success' => false, 'message' => 'La fecha compromiso es obligatoria para enviar la alerta.'], 422);
        }

        if (!$request->hasFile('pdf_firmado')) {
            return response()->json(['success' => false, 'message' => 'El PDF del SCAR firmado es obligatorio para enviar la alerta.'], 422);
        }

        $scar = ScarModelo::where('ot', '=', $ot, 'and')->first();
        if (!$scar) {
            return response()->json(['success' => false, 'message' => 'No se encontró un registro SCAR para esta OT. Debe generarlo primero.'], 404);
        }

        $liberacion = LiberacionModeloFundicion::where('ot', '=', $ot, 'and')
            ->whereIn('estado', ['rechazado', 'pendiente'])
            ->latest()
            ->first();

        // 1. Guardar el PDF firmado
        $file = $request->file('pdf_firmado');
        $otSanitizada = preg_replace('/[\s]+/', '_', trim(preg_replace('/[^\w\s\-]/', '', $ot)));
        $pdfFirmadoName = "Escaner_F-CCL-SCAR_{$otSanitizada}." . $file->getClientOriginalExtension();
        $pdfDir = storage_path('app/public/liberaciones_pdf');

        if (!file_exists($pdfDir)) {
            mkdir($pdfDir, 0755, true);
        }

        // Eliminar PDF firmado anterior si existe
        if ($scar->pdf_firmado_filename && file_exists("{$pdfDir}/{$scar->pdf_firmado_filename}")) {
            @unlink("{$pdfDir}/{$scar->pdf_firmado_filename}");
        }

        $file->move($pdfDir, $pdfFirmadoName);

        // Copiar a la carpeta de cada clase en la OT (bajo la nueva estructura)
        $clases = array_map('trim', explode(',', $scar->tipo_modelo));
        $folderName = $this->sanitizePath($this->normalizeOTName($ot));
        
        foreach ($clases as $clase) {
            $claseClean = strtoupper(trim(preg_replace('/^modelo\s+/i', '', strtolower($clase))));
            if (empty($claseClean)) $claseClean = 'GENERAL';
            
            FundicionPaths::crearEstructuraClase($folderName, $claseClean, self::CALIDAD_DIR);
            $destClassPath = self::CALIDAD_DIR . '/' . $folderName . '/' . $claseClean . '/' . FundicionPaths::DOCUMENTOS_RECHAZADOS . '/' . FundicionPaths::CALIDAD;
            if (!Storage::disk('local')->exists($destClassPath)) {
                Storage::disk('local')->makeDirectory($destClassPath);
            }
            Storage::disk('local')->put($destClassPath . '/' . $pdfFirmadoName, file_get_contents("{$pdfDir}/{$pdfFirmadoName}"));
        }

        // 2. Actualizar el modelo
        $scar->update([
            'fecha_compromiso' => $fechaCompromiso,
            'pdf_firmado_filename' => $pdfFirmadoName,
            'estatus' => 'alertado',
        ]);

        // 2.5 Regenerar el PDF digital del SCAR para que plasme la fecha de compromiso
        try {
            ini_set('memory_limit', '2048M');
            $pdf = Pdf::loadView('almacen.pdf.scar_pdf', ['scar' => $scar])
                      ->setPaper('letter', 'portrait');
            $pdf->save("{$pdfDir}/{$scar->pdf_filename}");
            
            // Copiar a la carpeta de cada clase en la OT
            foreach ($clases as $clase) {
                $claseClean = strtoupper(trim(preg_replace('/^modelo\s+/i', '', strtolower($clase))));
                if (empty($claseClean)) $claseClean = 'GENERAL';
                
                FundicionPaths::crearEstructuraClase($folderName, $claseClean, self::CALIDAD_DIR);
                $destClassPath = self::CALIDAD_DIR . '/' . $folderName . '/' . $claseClean . '/' . FundicionPaths::DOCUMENTOS_RECHAZADOS . '/' . FundicionPaths::CALIDAD;
                if (!Storage::disk('local')->exists($destClassPath)) {
                    Storage::disk('local')->makeDirectory($destClassPath);
                }
                Storage::disk('local')->put($destClassPath . '/' . $scar->pdf_filename, file_get_contents("{$pdfDir}/{$scar->pdf_filename}"));
            }
        } catch (\Exception $pdfEx) {
            Log::error('Error al regenerar PDF digital de SCAR en alerta: ' . $pdfEx->getMessage());
        }

        // Copiar carpeta DOCUMENTOS_RECHAZADOS de Calidad a Almacén (Solo nuevos)
        foreach ($clases as $clase) {
            $claseClean = strtoupper(trim(preg_replace('/^modelo\s+/i', '', strtolower($clase))));
            if (empty($claseClean)) $claseClean = 'GENERAL';
            
            $calRechSub = self::CALIDAD_DIR . '/' . $folderName . '/' . $claseClean . '/' . FundicionPaths::DOCUMENTOS_RECHAZADOS . '/' . FundicionPaths::CALIDAD;
            $almRechSub = self::ALMACEN_DIR . '/' . $folderName . '/' . $claseClean . '/' . FundicionPaths::DOCUMENTOS_RECHAZADOS . '/' . FundicionPaths::CALIDAD;
            
            if (Storage::disk('local')->exists($calRechSub)) {
                FundicionPaths::crearEstructuraClase($folderName, $claseClean, self::ALMACEN_DIR);
                if (!Storage::disk('local')->exists($almRechSub)) {
                    Storage::disk('local')->makeDirectory($almRechSub);
                }
                $archivosCalidad = Storage::disk('local')->files($calRechSub);
                foreach ($archivosCalidad as $archivo) {
                    $relativePath = basename($archivo);
                    $destino = $almRechSub . '/' . $relativePath;
                    if (!Storage::disk('local')->exists($destino)) {
                        Storage::disk('local')->copy($archivo, $destino);
                    }
                }
            }
        }

        // 3. Destinatarios
        $isJacarandas = $scar && stripos($scar->proveedor, 'jacarandas') !== false;
        $defaultEmail = $isJacarandas
            ? config('services.fundicion.produccion_jacarandas', 'ventas_jacarandas@prodigy.net.mx')
            : config('services.fundicion.produccion_ss', 'produccion@ssmetalf.mx');
        
        $destinosStr = $defaultEmail . ',' . config('services.fundicion.cc_general', 'alejandross@grupoindsaavedra.com');
        $destinatarios = array_filter(array_map('trim', explode(',', $destinosStr)));

        // 4. Compilar adjuntos
        $attachments = [];

        // SCAR Firmado (Escaneado)
        $attachments[] = [
            'path' => "{$pdfDir}/{$pdfFirmadoName}",
            'name' => $pdfFirmadoName,
            'mime' => 'application/pdf',
        ];

        // SCAR Digital Regenerado (con las fechas actualizadas)
        if ($scar->pdf_filename && file_exists("{$pdfDir}/{$scar->pdf_filename}")) {
            $attachments[] = [
                'path' => "{$pdfDir}/{$scar->pdf_filename}",
                'name' => $scar->pdf_filename,
                'mime' => 'application/pdf',
            ];
        }

        // LDM / Reporte de liberación (se marca en automático)
        if ($liberacion && $liberacion->pdf_filename) {
            $libPath = "{$pdfDir}/{$liberacion->pdf_filename}";
            if (file_exists($libPath)) {
                $attachments[] = [
                    'path' => $libPath,
                    'name' => $liberacion->pdf_filename,
                    'mime' => 'application/pdf',
                ];
            }
        }

        // Dibujos y Ayudas del servidor (filtrados por los archivos seleccionados en el modal)
        $folderName = $this->sanitizePath($this->normalizeOTName($ot));
        
        $files = [];
        $dirPathAlmacen = self::ALMACEN_DIR . '/' . $folderName;
        $dirPathCalidad = self::CALIDAD_DIR . '/' . $folderName;

        if (Storage::disk('local')->exists($dirPathAlmacen)) {
            $files = array_merge($files, Storage::disk('local')->allFiles($dirPathAlmacen));
        }
        if (Storage::disk('local')->exists($dirPathCalidad)) {
            $files = array_merge($files, Storage::disk('local')->allFiles($dirPathCalidad));
        }
        $files = array_unique($files);

        $dibujosSelected = $request->input('dibujos', []);
        $ayudasSelected  = $request->input('ayudas', []);
        $otrosSelected   = $request->input('otros_documentos', []);

        foreach ($files as $file) {
            $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
            if (in_array($ext, ['pdf', 'png', 'jpg', 'jpeg'])) {
                
                // Normalizar ruta para evitar problemas con contrabarras en Windows
                $fNorm = str_replace('\\', '/', $file);
                
                // Determinar el directorio base
                if (strpos($fNorm, self::CALIDAD_DIR . '/' . $folderName) !== false) {
                    $currentDir = self::CALIDAD_DIR . '/' . $folderName;
                } else {
                    $currentDir = self::ALMACEN_DIR . '/' . $folderName;
                }
                
                $dirPathNorm = str_replace('\\', '/', $currentDir);
                $ayudasPathNorm = $dirPathNorm . '/ayudas_visuales';

                $isSelected = false;

                // Comprobar si el archivo está en la subcarpeta ayudas_visuales
                if (str_starts_with($fNorm, $ayudasPathNorm . '/')) {
                    $relName = ltrim(str_replace($ayudasPathNorm, '', $fNorm), '/');
                    $utf8RelName = $this->toUtf8($relName);

                    if (str_starts_with($relName, 'preordenes/')) {
                        $isSelected = in_array($utf8RelName, $otrosSelected);
                    } else {
                        $isSelected = in_array($utf8RelName, $ayudasSelected);
                    }
                } else {
                    // Está en la raíz de la OT (tipo dibujo)
                    $relName = ltrim(str_replace($dirPathNorm, '', $fNorm), '/');
                    $utf8RelName = $this->toUtf8($relName);

                    if (!str_contains($relName, '/')) {
                        $isSelected = in_array($utf8RelName, $dibujosSelected);
                    }
                }

                if (!$isSelected) {
                    continue;
                }

                $bName = basename($file);
                // Evitar duplicar archivos
                $alreadyAttached = false;
                foreach ($attachments as $att) {
                    if ($att['name'] === $bName) {
                        $alreadyAttached = true;
                        break;
                    }
                }
                if (!$alreadyAttached) {
                    $attachments[] = [
                        'path' => storage_path('app/' . $file),
                        'name' => $bName,
                        'mime' => mime_content_type(storage_path('app/' . $file)) ?: 'application/octet-stream'
                    ];
                }
            }
        }

        $clases = array_map('trim', explode(',', $scar->tipo_modelo));

        if ($request->hasFile('evidencia_fotos_files')) {
            foreach ($request->file('evidencia_fotos_files') as $idx => $photoFile) {
                if ($photoFile->isValid()) {
                    $photoName = "evidencia_foto_{$idx}_{$otSanitizada}." . $photoFile->getClientOriginalExtension();
                    $photoPath = $photoFile->move(storage_path('app/public/liberaciones_pdf/evidencia'), $photoName);
                    $attachments[] = [
                        'path' => $photoPath->getRealPath(),
                        'name' => $photoName,
                        'mime' => $photoFile->getClientMimeType(),
                    ];
                    // Copiar a la carpeta de cada clase en la OT
                    foreach ($clases as $clase) {
                        $claseClean = strtoupper(trim(preg_replace('/^modelo\s+/i', '', strtolower($clase))));
                        if (empty($claseClean)) $claseClean = 'GENERAL';
                        
                        FundicionPaths::crearEstructuraClase($folderName, $claseClean, self::CALIDAD_DIR);
                        $destPath = self::CALIDAD_DIR . '/' . $folderName . '/' . $claseClean . '/' . FundicionPaths::DOCUMENTOS_RECHAZADOS . '/' . FundicionPaths::CALIDAD;
                        if (!Storage::disk('local')->exists($destPath)) {
                            Storage::disk('local')->makeDirectory($destPath);
                        }
                        Storage::disk('local')->put($destPath . '/' . $photoName, file_get_contents($photoPath->getRealPath()));
                    }
                }
            }
        }

        // Otros archivos PDF subidos en el momento (si los hay)
        if ($request->hasFile('evidencia_otro_files')) {
            foreach ($request->file('evidencia_otro_files') as $idx => $otherFile) {
                if ($otherFile->isValid()) {
                    $otherName = "evidencia_otro_{$idx}_{$otSanitizada}." . $otherFile->getClientOriginalExtension();
                    $otherPath = $otherFile->move(storage_path('app/public/liberaciones_pdf/evidencia'), $otherName);
                    $attachments[] = [
                        'path' => $otherPath->getRealPath(),
                        'name' => $otherName,
                        'mime' => $otherFile->getClientMimeType(),
                    ];
                    // Copiar a la carpeta de cada clase en la OT
                    foreach ($clases as $clase) {
                        $claseClean = strtoupper(trim(preg_replace('/^modelo\s+/i', '', strtolower($clase))));
                        if (empty($claseClean)) $claseClean = 'GENERAL';
                        
                        FundicionPaths::crearEstructuraClase($folderName, $claseClean, self::CALIDAD_DIR);
                        $destPath = self::CALIDAD_DIR . '/' . $folderName . '/' . $claseClean . '/' . FundicionPaths::DOCUMENTOS_RECHAZADOS . '/' . FundicionPaths::CALIDAD;
                        if (!Storage::disk('local')->exists($destPath)) {
                            Storage::disk('local')->makeDirectory($destPath);
                        }
                        Storage::disk('local')->put($destPath . '/' . $otherName, file_get_contents($otherPath->getRealPath()));
                    }
                }
            }
        }

        // Archivos Adicionales (Subidos mediante el nuevo dropzone unificado)
        if ($request->hasFile('archivos_adicionales')) {
            foreach ($request->file('archivos_adicionales') as $idx => $addFile) {
                if ($addFile->isValid()) {
                    $addName = "evidencia_adicional_{$idx}_{$otSanitizada}." . $addFile->getClientOriginalExtension();
                    $addPath = $addFile->move(storage_path('app/public/liberaciones_pdf/evidencia'), $addName);
                    $attachments[] = [
                        'path' => $addPath->getRealPath(),
                        'name' => $addName,
                        'mime' => $addFile->getClientMimeType(),
                    ];
                    // Copiar a la carpeta de cada clase en la OT
                    foreach ($clases as $clase) {
                        $claseClean = strtoupper(trim(preg_replace('/^modelo\s+/i', '', strtolower($clase))));
                        if (empty($claseClean)) $claseClean = 'GENERAL';
                        
                        FundicionPaths::crearEstructuraClase($folderName, $claseClean, self::CALIDAD_DIR);
                        $destPath = self::CALIDAD_DIR . '/' . $folderName . '/' . $claseClean . '/' . FundicionPaths::DOCUMENTOS_RECHAZADOS . '/' . FundicionPaths::CALIDAD;
                        if (!Storage::disk('local')->exists($destPath)) {
                            Storage::disk('local')->makeDirectory($destPath);
                        }
                        Storage::disk('local')->put($destPath . '/' . $addName, file_get_contents($addPath->getRealPath()));
                    }
                }
            }
        }

        // 5. Enviar el correo
        try {
            Mail::to($destinatarios)->send(new LiberacionModeloMailable($ot, 'rechazado', $liberacion, $attachments));
            
            // Sincronizar carpeta completa de Calidad a Almacén
            $this->syncCalidadToAlmacen($folderName);
            $this->eliminarCarpetasVacias($ot);
        } catch (\Exception $mailEx) {
            Log::error('Error al enviar correo SCAR Firmado: ' . $mailEx->getMessage());
            $this->eliminarCarpetasVacias($ot);
            return response()->json([
                'success' => false,
                'message' => 'Los datos se guardaron pero la alerta por correo no pudo enviarse: ' . $mailEx->getMessage(),
            ], 500);
        }

        // Registrar auditoría de envío de alerta SCAR
        try {
            ScarLog::create([
                'ot'           => $ot,
                'tipo_modelo'  => $scar->tipo_modelo ?? null,
                'no_scar'      => $scar->no_scar ?? null,
                'accion'       => 'enviar_alerta',
                'pdf_filename' => $scar->pdf_filename ?? null,
                'proveedor'    => $scar->proveedor ?? null,
                'user_id'      => $user->id,
                'user_nombre'  => $user->name,
            ]);
        } catch (\Exception $logEx) {
            Log::warning('Error al registrar log de alerta SCAR: ' . $logEx->getMessage());
        }

        return response()->json([
            'success' => true,
            'message' => 'Alerta SCAR firmada enviada exitosamente al proveedor.',
        ]);
    }

    public function getScar(Request $request)
    {
        $ot = $request->input('ot');
        $tipoModelo = $request->input('tipo_modelo');
        if (empty($ot)) {
            return response()->json(['success' => false, 'message' => 'OT requerida.'], 422);
        }
        
        $query = ScarModelo::where('ot', '=', $ot, 'and');
        if (!empty($tipoModelo)) {
            $query->where('tipo_modelo', '=', $tipoModelo);
        }
        $scar = $query->first();

        // Obtener código de modelo de la preorden
        $preordenCodigoModelo = null;
        $preOrdenes = PreOrdenFundicion::where('ot', '=', $ot, 'and')->get();
        if ($preOrdenes->isEmpty()) {
            $baseOt = preg_replace('/_(?:(?:candado\s+obturador|cabeza\s+de\s+soplo|obturador|bombillo|embudo|corona|plato|molde|fondo|pistones|guías|guias)(?:_(?:candado\s+obturador|cabeza\s+de\s+soplo|obturador|bombillo|embudo|corona|plato|molde|fondo|pistones|guías|guias))*_)?R\d+$/iu', '', $ot);
            $preOrdenes = PreOrdenFundicion::where('ot', '=', $baseOt, 'and')->get();
        }
        
        if ($preOrdenes->isNotEmpty() && !empty($tipoModelo)) {
            $targetTipo = strtolower($tipoModelo);
            foreach ($preOrdenes as $preOrden) {
                $filas = $preOrden->filas;
                if (is_string($filas)) {
                    $filas = json_decode($filas, true);
                }
                if (is_array($filas)) {
                    foreach ($filas as $fila) {
                        $tipoFila = strtolower($fila['tipo_modelo'] ?? '');
                        $claseFila = strtolower($fila['clase_nombre'] ?? $fila['clase'] ?? '');
                        
                        if ($tipoFila === $targetTipo || (!empty($claseFila) && (strpos($claseFila, $targetTipo) !== false || strpos($targetTipo, $claseFila) !== false))) {
                            $preordenCodigoModelo = $fila['codigo_modelo'] ?? $fila['codigo'] ?? null;
                            break 2; // Break both loops
                        }
                    }
                }
            }
        }
        
        // Buscar en Orden_trabajo para ver si la clase es una templadera o tiene prefijo especial
        $esTempladera = false;
        $claseNombre = null;
        preg_match('/OT\s*(\d+)/', $ot, $otMatches);
        $otId = isset($otMatches[1]) ? (int)$otMatches[1] : 0;
        $otModel = Orden_trabajo::with('clases')->find($otId);
        if ($otModel && !empty($tipoModelo)) {
            $tLow = strtolower($tipoModelo);
            foreach ($otModel->clases as $clase) {
                $cLow = strtolower($clase->nombre);
                if (strpos($cLow, $tLow) !== false) {
                    $claseNombre = $clase->nombre;
                    if (strpos($cLow, 'templadera') !== false) {
                        $esTempladera = true;
                    }
                    break;
                }
            }
        }
        
        return response()->json([
            'success' => true,
            'scar' => $scar,
            'preorden_codigo_modelo' => $preordenCodigoModelo,
            'es_templadera' => $esTempladera,
            'clase_nombre' => $claseNombre
        ]);
    }

    public function enviarAlertaLiberacion(Request $request)
    {
        /** @var \App\Models\User|null $user */
        $user = Auth::user();
        if (!$user || !in_array($user->perfil, ['1', '2', '3', '4', '5'], true)) {
            return response()->json(['success' => false, 'message' => 'Acceso restringido.'], 403);
        }

        $ot          = $request->input('ot', '');
        $decision    = $request->input('decision', ''); // 'aprobar' | 'rechazar'
        $tipoModelo  = $request->input('tipo_modelo', '');
        $fecha       = $request->input('fecha', '');
        $destinatario = $request->input('destinatario', '');
        $nameScar    = null;

        if (empty($ot) || empty($decision) || empty($tipoModelo) || empty($fecha)) {
            $emptyFields = [];
            if (empty($ot)) $emptyFields[] = 'ot';
            if (empty($decision)) $emptyFields[] = 'decision';
            if (empty($tipoModelo)) $emptyFields[] = 'tipo_modelo';
            if (empty($fecha)) $emptyFields[] = 'fecha';
            return response()->json([
                'success' => false,
                'message' => 'Campos obligatorios incompletos: ' . implode(', ', $emptyFields)
            ], 422);
        }

        // Obtener el registro de liberación correspondiente
        $tipos = array_map('trim', explode(',', $tipoModelo));
        $liberacion = LiberacionModeloFundicion::where('ot', '=', $ot, 'and')
            ->whereIn('tipo_modelo', $tipos)
            ->first();

        if (!$liberacion) {
            // Intenta buscar uno con tipo_modelo NULL o vacio
            $liberacion = LiberacionModeloFundicion::where('ot', '=', $ot, 'and')
                ->where(function($q) {
                    $q->whereNull('tipo_modelo')
                      ->orWhere('tipo_modelo', '=', '');
                })
                ->first();
        }

        if (!$liberacion) {
            // Fallback de fallback: busca cualquier registro para esta OT
            $liberacion = LiberacionModeloFundicion::where('ot', '=', $ot, 'and')->first();
        }

        if (!$liberacion) {
            return response()->json(['success' => false, 'message' => 'No se encontró un borrador guardado para esta liberación.'], 404);
        }

        $clases = array_map('trim', explode(',', $tipoModelo));
        $folderName = $this->sanitizePath($this->normalizeOTName($ot));
        
        $attachments = [];
        $attachmentsAprobados = [];
        $attachmentsRechazados = [];
        $otSanitizada = str_replace(['/', '\\', ' ', ':'], '_', $ot);

        // Archivos Adicionales (Subidos mediante el nuevo dropzone unificado)
        if ($request->hasFile('archivos_adicionales')) {
            foreach ($request->file('archivos_adicionales') as $idx => $addFile) {
                if ($addFile->isValid()) {
                    $addName = "evidencia_adicional_{$idx}_{$otSanitizada}." . $addFile->getClientOriginalExtension();
                    $addPath = $addFile->move(storage_path('app/public/liberaciones_pdf/evidencia'), $addName);
                    $fileItem = [
                        'path' => $addPath->getRealPath(),
                        'name' => $addName,
                        'mime' => $addFile->getClientMimeType() ?: 'application/octet-stream',
                    ];
                    if ($decision === 'mixto') {
                        $attachmentsAprobados[] = $fileItem;
                        $attachmentsRechazados[] = $fileItem;
                    } elseif ($decision === 'aprobar') {
                        $attachmentsAprobados[] = $fileItem;
                    } else {
                        $attachmentsRechazados[] = $fileItem;
                    }
                    
                    // Copiar a la carpeta de cada clase en la OT para que se listen
                    foreach ($clases as $clase) {
                        $claseClean = strtoupper(trim(preg_replace('/^modelo\s+/i', '', strtolower($clase))));
                        if (empty($claseClean)) $claseClean = 'GENERAL';
                        
                        FundicionPaths::crearEstructuraClase($folderName, $claseClean, self::CALIDAD_DIR);
                        $destPath = self::CALIDAD_DIR . '/' . $folderName . '/' . $claseClean . '/ESCANEADOS';
                        if (!Storage::disk('local')->exists($destPath)) {
                            Storage::disk('local')->makeDirectory($destPath);
                        }
                        Storage::disk('local')->put($destPath . '/' . $addName, file_get_contents($addPath->getRealPath()));
                    }
                }
            }
        }

        // El formato escaneado ya no se recibe aquí como input aislado
        $nameFormato = $liberacion->pdf_filename ?? null;

        // Actualizar el estado de la liberación en base de datos y destinatario
        // NOTA: Si la decisión es 'mixto', no actualizamos el estado general a 'mixto'
        // a menos que queramos mantener un string diferente. Sin embargo, actualizaremos los registros individuales.
        $decisionNorm = ($decision === 'aprobar') ? 'aprobado' : (($decision === 'rechazar') ? 'rechazado' : 'mixto');
        $nuevoEstado = 'calidad_' . $decisionNorm;
        
        $liberacionesOT = LiberacionModeloFundicion::where('ot', '=', $ot, 'and')->get();
        $clasesAprobadas = [];
        $clasesRechazadas = [];
        $libAprobada = null;
        $libRechazada = null;

        $allDecisions = [];

        // Normalizar $tipos con trim+lowercase para comparaciones case-insensitive
        $tiposNorm = array_map(fn($t) => strtolower(trim($t)), $tipos);

        // Parsear tipos_aprobados desde JSON (viene como JSON.stringify del frontend)
        $tiposAproRaw = $request->input('tipos_aprobados', '[]');
        $tiposApro = is_array($tiposAproRaw) ? $tiposAproRaw : json_decode($tiposAproRaw, true) ?? [];
        $tiposAproNorm = array_map(fn($t) => strtolower(trim($t)), $tiposApro);

        /** @var LiberacionModeloFundicion $libRow */
        foreach ($liberacionesOT as $libRow) {
            // Si el tipo de modelo o decision es null en BD (ej: registro inicial), los inicializamos con los valores de la alerta
            if (is_null($libRow->tipo_modelo) && !empty($tipos)) {
                $exists = LiberacionModeloFundicion::where('ot', '=', $ot, 'and')->where('tipo_modelo', '=', $tipos[0], 'and')->exists();
                if (!$exists) {
                    $libRow->tipo_modelo = $tipos[0];
                } else {
                    LiberacionModeloFundicion::destroy($libRow->id);
                    continue;
                }
            }

            // Procesar solo las clases que se están enviando en esta alerta (comparación case-insensitive)
            $tipoNorm = strtolower(trim($libRow->tipo_modelo ?? ''));
            if (in_array($tipoNorm, $tiposNorm)) {
                if (is_null($libRow->decision)) {
                    if ($decision === 'mixto') {
                        if (in_array($tipoNorm, $tiposAproNorm)) {
                            $libRow->decision = 'aprobar';
                        } else {
                            $libRow->decision = 'rechazar';
                        }
                    } else {
                        $libRow->decision = $decision;
                    }
                }

                $libNuevoEst = $libRow->decision === 'aprobar' ? 'aprobado' : 'rechazado';
                
                $libRow->update([
                    'tipo_modelo'    => $libRow->tipo_modelo,
                    'decision'       => $libRow->decision,
                    'estado'         => $libNuevoEst,
                    'fecha_revision' => $fecha,
                    'pdf_filename'   => $libRow->pdf_filename,
                    'alerta_enviada' => true
                ]);

                if ($libRow->decision === 'aprobar') {
                    $clasesAprobadas[] = strtolower($libRow->tipo_modelo);
                    if (!$libAprobada) $libAprobada = clone $libRow;
                } elseif ($libRow->decision === 'rechazar') {
                    $clasesRechazadas[] = strtolower($libRow->tipo_modelo);
                    if (!$libRechazada) $libRechazada = clone $libRow;
                }
            }

            // Registrar todas las decisiones de la OT para el estado global
            if ($libRow->decision) {
                $allDecisions[] = $libRow->decision;
            }
        }

        // Calcular estado global de la OT combinando TODAS sus liberaciones
        $allDecisions = array_unique($allDecisions);
        if (count($allDecisions) > 1) {
            $nuevoEstado = 'calidad_mixto';
        } elseif (count($allDecisions) == 1) {
            $nuevoEstado = $allDecisions[0] === 'aprobar' ? 'calidad_aprobado' : 'calidad_rechazado';
        } else {
            $decisionNorm = ($decision === 'aprobar') ? 'aprobado' : (($decision === 'rechazar') ? 'rechazado' : 'mixto');
            $nuevoEstado = 'calidad_' . $decisionNorm;
        }

        // Verificar si hay clases activas pendientes para asignar un estado parcial
        $history = FundicionHistory::where('ot', '=', $ot, 'and')->first();
        if ($history && $history->ayudas_config) {
            $clasesActivas = [];
            foreach ($history->ayudas_config as $c) {
                if (!str_contains(strtolower($c), 'opcional') || str_contains(strtolower($c), 'pistones') || str_contains(strtolower($c), 'guías') || str_contains(strtolower($c), 'guias')) {
                    $clLow = strtolower($c);
                    if (strpos($clLow, 'candado obturador') !== false) $clasesActivas[] = 'candado obturador';
                    elseif (strpos($clLow, 'cabeza de soplo') !== false) $clasesActivas[] = 'cabeza de soplo';
                    elseif (strpos($clLow, 'embudo') !== false) $clasesActivas[] = 'embudo';
                    elseif (strpos($clLow, 'corona') !== false) $clasesActivas[] = 'corona';
                    elseif (strpos($clLow, 'plato') !== false) $clasesActivas[] = 'plato';
                    elseif (strpos($clLow, 'fondo') !== false) $clasesActivas[] = 'fondo';
                    elseif (strpos($clLow, 'obturador') !== false) $clasesActivas[] = 'obturador';
                    elseif (strpos($clLow, 'molde') !== false) $clasesActivas[] = 'molde';
                    elseif (strpos($clLow, 'bombillo') !== false) $clasesActivas[] = 'bombillo';
                    elseif (strpos($clLow, 'pistones') !== false) $clasesActivas[] = 'pistones';
                    elseif (strpos($clLow, 'guías') !== false || strpos($clLow, 'guias') !== false) $clasesActivas[] = 'guías';
                }
            }
            
            $clasesConDecision = array_map('strtolower', $liberacionesOT->where('alerta_enviada', true)->whereNotNull('decision')->pluck('tipo_modelo')->toArray());
            
            $todasActivasConDecision = true;
            foreach ($clasesActivas as $ca) {
                if (!in_array($ca, $clasesConDecision)) {
                    $todasActivasConDecision = false;
                    break;
                }
            }
            
            if (!$todasActivasConDecision && count($clasesActivas) > 0) {
                $nuevoEstado = 'calidad_parcial';
            }
        }

        $scarModelos = ScarModelo::where('ot', '=', $ot, 'and')->get();
        // Si hay rechazos en ESTE lote procesado, marcar también en el SCAR que ha sido alertado
        if (count($clasesRechazadas) > 0 && $scarModelos->isNotEmpty()) {
            foreach ($scarModelos as $scarMod) {
                // Solo si el SCAR pertenece a las clases rechazadas o si no tiene tipo
                if (empty($scarMod->tipo_modelo) || in_array(strtolower($scarMod->tipo_modelo), $clasesRechazadas)) {
                    $scarMod->update([
                        'estatus'          => 'alertado',
                        'fecha_compromiso' => $fecha
                    ]);
                    $this->updateScarPdf($scarMod);
                }
            }
        }

        // Actualizar calidad_revision_status en fundicion_history
        FundicionHistory::where('ot', '=', $ot, 'and')->update([
            'calidad_revision_status' => $nuevoEstado
        ]);

        // Dibujos y Ayudas del servidor (filtrados por los archivos seleccionados en el modal)
        // Buscamos en todas las carpetas de OTs relacionadas (base y reprocesos)
        $baseOtForSearch = preg_replace('/_(?:(?:candado\s+obturador|cabeza\s+de\s+soplo|obturador|bombillo|embudo|corona|plato|molde|fondo|pistones|guías|guias)(?:_(?:candado\s+obturador|cabeza\s+de\s+soplo|obturador|bombillo|embudo|corona|plato|molde|fondo|pistones|guías|guias))*_)?R\d+$/iu', '', $ot);
        $allOtNamesForSearch = FundicionHistory::where('ot', '=', $baseOtForSearch, 'or')
            ->where('ot', 'LIKE', $baseOtForSearch . '_R%', 'or')
            ->where('ot', 'LIKE', $baseOtForSearch . '_%_R%', 'or')
            ->pluck('ot')
            ->toArray();
        if (!in_array($ot, $allOtNamesForSearch)) {
            $allOtNamesForSearch[] = $ot;
        }

        $files = [];
        foreach ($allOtNamesForSearch as $relatedOt) {
            $relFolder = $this->sanitizePath($this->normalizeOTName($relatedOt));
            $dirPathAlmacen = self::ALMACEN_DIR . '/' . $relFolder;
            $dirPathCalidad = self::CALIDAD_DIR . '/' . $relFolder;

            if (Storage::disk('local')->exists($dirPathAlmacen)) {
                $files = array_merge($files, Storage::disk('local')->allFiles($dirPathAlmacen));
            }
            if (Storage::disk('local')->exists($dirPathCalidad)) {
                $files = array_merge($files, Storage::disk('local')->allFiles($dirPathCalidad));
            }
        }
        $files = array_unique($files);

        $dibujosSelected = $request->input('dibujos', []);
        $ayudasSelected  = $request->input('ayudas', []);
        $otrosSelected   = $request->input('otros_documentos', []);
        $dibujosAprobadosSelected = $request->input('dibujos_aprobados', []);
        $dibujosRechazadosSelected = $request->input('dibujos_rechazados', []);

        foreach ($files as $file) {
            $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
            if (in_array($ext, ['pdf', 'png', 'jpg', 'jpeg'])) {
                // Normalizar ruta para evitar problemas con contrabarras en Windows
                $fNorm = str_replace('\\', '/', $file);
                
                // Determinar la ruta relativa exacta que coincide con la devuelta por getFiles
                $relName = '';
                $dirAlmacenNorm = str_replace('\\', '/', self::ALMACEN_DIR . '/' . $folderName);
                $dirCalidadNorm = str_replace('\\', '/', self::CALIDAD_DIR . '/' . $folderName);
                
                if (str_starts_with($fNorm, $dirAlmacenNorm)) {
                    $subPath = ltrim(substr($fNorm, strlen($dirAlmacenNorm)), '/');
                    if (str_starts_with($subPath, 'ayudas_visuales/preordenes/')) {
                        $relName = 'preordenes/' . ltrim(substr($subPath, strlen('ayudas_visuales/preordenes/')), '/');
                    } elseif (str_starts_with($subPath, 'ayudas_visuales/')) {
                        $relName = ltrim(substr($subPath, strlen('ayudas_visuales/')), '/');
                    } else {
                        $relName = $subPath;
                    }
                } elseif (str_starts_with($fNorm, $dirCalidadNorm)) {
                    $subPath = ltrim(substr($fNorm, strlen($dirCalidadNorm)), '/');
                    if (str_starts_with($subPath, 'ayudas_visuales/preordenes/')) {
                        $relName = 'preordenes/' . ltrim(substr($subPath, strlen('ayudas_visuales/preordenes/')), '/');
                    } elseif (str_starts_with($subPath, 'ayudas_visuales/')) {
                        $relName = ltrim(substr($subPath, strlen('ayudas_visuales/')), '/');
                    } else {
                        $relName = $subPath;
                    }
                }
                
                if (empty($relName)) {
                    continue;
                }
                
                $utf8RelName = $this->toUtf8($relName);

                $pathAbs = storage_path('app/' . $file);
                $nameBase = basename($file);
                try {
                    /** @var \Illuminate\Filesystem\FilesystemAdapter $disk */
                    $disk = Storage::disk('local');
                    $mimeType = $disk->mimeType($file) ?: 'application/octet-stream';
                } catch (\Exception $e) {
                    $mimeType = 'application/octet-stream';
                }
                
                $fileItem = [
                    'path' => $pathAbs,
                    'name' => $nameBase,
                    'mime' => $mimeType
                ];

                // Agregar a aprobados si está seleccionado en aprobados o dibujos/ayudas/otros generales de aprobación
                $isSelAprobado = in_array($utf8RelName, $dibujosAprobadosSelected) ||
                                 in_array($utf8RelName, $dibujosSelected) ||
                                 in_array($utf8RelName, $ayudasSelected) ||
                                 in_array($utf8RelName, $otrosSelected) ||
                                 ($decision === 'aprobar' && in_array($utf8RelName, $dibujosRechazadosSelected));

                // Agregar a rechazados si está seleccionado en rechazados
                $isSelRechazado = in_array($utf8RelName, $dibujosRechazadosSelected);

                if (!$isSelAprobado && !$isSelRechazado) {
                    continue;
                }

                // Helper para verificar si un archivo pertenece a una clase
                $perteneceAClase = function (string $filePath, string $clase): bool {
                    $fileNorm = strtolower(str_replace('\\', '/', $filePath));
                    $claseNorm = strtolower(trim($clase));
                    
                    if (str_contains($fileNorm, $claseNorm)) {
                        return true;
                    }
                    
                    return false;
                };


                if ($decision === 'aprobar') {
                    // Si es aprobado, solo mandar archivos que pertenezcan a la clase que estamos aprobando
                    $clasesAprobadas = array_map('trim', explode(',', $tipoModelo));
                    $pertenece = false;
                    foreach ($clasesAprobadas as $clase) {
                        if ($perteneceAClase($utf8RelName, $clase)) {
                            $pertenece = true;
                            break;
                        }
                    }
                    if ($isSelAprobado && $pertenece) {
                        $attachmentsAprobados[] = $fileItem;
                    }
                } elseif ($decision === 'rechazar') {
                    // Si es rechazado:
                    // 1. Mandar solo la preorden de modelo de los documentos aprobados
                    $isPreOrdenModelo = str_contains($nameBase, 'Pre-Orden_Fundicion-') && !str_contains(strtolower($nameBase), 'casting');
                    if ($isSelAprobado && $isPreOrdenModelo) {
                        $attachmentsRechazados[] = $fileItem;
                    }
                    
                    // 2. Mandar todos los documentos rechazados de la clase que estamos rechazando
                    $clasesRechazadas = array_map('trim', explode(',', $tipoModelo));
                    $pertenece = false;
                    foreach ($clasesRechazadas as $clase) {
                        if ($perteneceAClase($utf8RelName, $clase)) {
                            $pertenece = true;
                            break;
                        }
                    }
                    if ($isSelRechazado && $pertenece) {
                        $attachmentsRechazados[] = $fileItem;
                    }
                } else {
                    // Caso mixto (u otros): enviar todo sin filtrar por clase
                    if ($isSelAprobado) {
                        $attachmentsAprobados[] = $fileItem;
                    }
                    if ($isSelRechazado) {
                        $attachmentsRechazados[] = $fileItem;
                    }
                }
            }
        }

        // Archivos Aprobados extras (por modelo: archivos_aprobados_extra[Tipo])
        if ($request->hasFile('archivos_aprobados_extra')) {
            $uploadedAprobados = $request->file('archivos_aprobados_extra');
            $filesFlat = [];
            if (is_array($uploadedAprobados)) {
                foreach ($uploadedAprobados as $tipoKey => $f) {
                    if (is_array($f)) { foreach ($f as $ff) $filesFlat[] = ['file' => $ff, 'tipo' => $tipoKey]; }
                    else $filesFlat[] = ['file' => $f, 'tipo' => $tipoKey];
                }
            } else {
                $filesFlat[] = ['file' => $uploadedAprobados, 'tipo' => ''];
            }
            foreach ($filesFlat as $item) {
                $extraFile = $item['file'];
                if ($extraFile && $extraFile->isValid()) {
                    $tipoName = $item['tipo'] ?: (explode(',', $tipoModelo)[0] ?? '');
                    $claseClean = strtoupper(trim(preg_replace('/^modelo\s+/i', '', strtolower($tipoName))));
                    if (empty($claseClean)) $claseClean = 'GENERAL';
                    
                    FundicionPaths::crearEstructuraClase($folderName, $claseClean, self::CALIDAD_DIR);
                    $destPath = self::CALIDAD_DIR . '/' . $folderName . '/' . $claseClean . '/ESCANEADOS';
                    if (!Storage::disk('local')->exists($destPath)) {
                        Storage::disk('local')->makeDirectory($destPath);
                    }
                    
                    $prefix    = $item['tipo'] ? strtoupper($item['tipo']) . '_Aprobado_' : 'Aprobado_';
                    $ext       = $extraFile->getClientOriginalExtension();
                    $safeName  = preg_replace('/[^a-zA-Z0-9_\-\.\s]/', '_', pathinfo($extraFile->getClientOriginalName(), PATHINFO_FILENAME));
                    $extraName = $prefix . trim($safeName, '_.') . ($ext ? '.' . $ext : '');
                    $savedPath = $extraFile->storeAs($destPath, $extraName, 'local');
                    $attachmentsAprobados[] = [
                        'path' => storage_path('app/' . $savedPath),
                        'name' => $extraName,
                        'mime' => $extraFile->getClientMimeType() ?: 'application/octet-stream'
                    ];
                }
            }
        }

        // Archivos Rechazados extras (por modelo: archivos_rechazados_extra[Tipo])
        if ($request->hasFile('archivos_rechazados_extra')) {
            $uploadedRechazados = $request->file('archivos_rechazados_extra');
            $filesFlat = [];
            if (is_array($uploadedRechazados)) {
                foreach ($uploadedRechazados as $tipoKey => $f) {
                    if (is_array($f)) { foreach ($f as $ff) $filesFlat[] = ['file' => $ff, 'tipo' => $tipoKey]; }
                    else $filesFlat[] = ['file' => $f, 'tipo' => $tipoKey];
                }
            } else {
                $filesFlat[] = ['file' => $uploadedRechazados, 'tipo' => ''];
            }
            foreach ($filesFlat as $item) {
                $extraFile = $item['file'];
                if ($extraFile && $extraFile->isValid()) {
                    $tipoName = $item['tipo'] ?: (explode(',', $tipoModelo)[0] ?? '');
                    $claseClean = strtoupper(trim(preg_replace('/^modelo\s+/i', '', strtolower($tipoName))));
                    if (empty($claseClean)) $claseClean = 'GENERAL';
                    
                    FundicionPaths::crearEstructuraClase($folderName, $claseClean, self::CALIDAD_DIR);
                    $destPath = self::CALIDAD_DIR . '/' . $folderName . '/' . $claseClean . '/' . FundicionPaths::DOCUMENTOS_RECHAZADOS . '/' . FundicionPaths::CALIDAD;
                    if (!Storage::disk('local')->exists($destPath)) {
                        Storage::disk('local')->makeDirectory($destPath);
                    }
                    
                    $prefix    = $item['tipo'] ? strtoupper($item['tipo']) . '_Rechazado_' : 'Rechazado_';
                    $ext       = $extraFile->getClientOriginalExtension();
                    $safeName  = preg_replace('/[^a-zA-Z0-9_\-\.\s]/', '_', pathinfo($extraFile->getClientOriginalName(), PATHINFO_FILENAME));
                    $extraName = $prefix . trim($safeName, '_.') . ($ext ? '.' . $ext : '');
                    $savedPath = $extraFile->storeAs($destPath, $extraName, 'local');
                    $attachmentsRechazados[] = [
                        'path' => storage_path('app/' . $savedPath),
                        'name' => $extraName,
                        'mime' => $extraFile->getClientMimeType() ?: 'application/octet-stream'
                    ];
                }
            }
        }

        // Archivos SCAR extras por modelo (archivos_scar_extra[Tipo]) → van a rechazados
        if ($request->hasFile('archivos_scar_extra')) {
            $uploadedScar = $request->file('archivos_scar_extra');
            $filesFlat = [];
            if (is_array($uploadedScar)) {
                foreach ($uploadedScar as $tipoKey => $f) {
                    if (is_array($f)) { foreach ($f as $ff) $filesFlat[] = ['file' => $ff, 'tipo' => $tipoKey]; }
                    else $filesFlat[] = ['file' => $f, 'tipo' => $tipoKey];
                }
            } else {
                $filesFlat[] = ['file' => $uploadedScar, 'tipo' => ''];
            }
            foreach ($filesFlat as $item) {
                $extraFile = $item['file'];
                if ($extraFile && $extraFile->isValid()) {
                    $tipoName = $item['tipo'] ?: (explode(',', $tipoModelo)[0] ?? '');
                    $claseClean = strtoupper(trim(preg_replace('/^modelo\s+/i', '', strtolower($tipoName))));
                    if (empty($claseClean)) $claseClean = 'GENERAL';
                    
                    FundicionPaths::crearEstructuraClase($folderName, $claseClean, self::CALIDAD_DIR);
                    $destPath = self::CALIDAD_DIR . '/' . $folderName . '/' . $claseClean . '/' . FundicionPaths::DOCUMENTOS_RECHAZADOS . '/' . FundicionPaths::CALIDAD;
                    if (!Storage::disk('local')->exists($destPath)) {
                        Storage::disk('local')->makeDirectory($destPath);
                    }
                    
                    $cClean    = $item['tipo'] ? ucfirst(trim(preg_replace('/^modelo\s+/i', '', $item['tipo']))) : 'General';
                    $ext       = $extraFile->getClientOriginalExtension();
                    $mime      = $extraFile->getClientMimeType() ?: '';
                    $isImg     = str_starts_with($mime, 'image/') || in_array(strtolower($ext), ['jpg', 'jpeg', 'png', 'gif', 'webp']);
                    $prefix    = $isImg ? 'F_CCL_SCAR_FOTO-1' : 'F_CCL_SCAR_PDF-1';
                    $extraName = "{$prefix}_{$cClean}_{$otSanitizada}." . ($ext ?: ($isImg ? 'jpg' : 'pdf'));
                    $savedPath = $extraFile->storeAs($destPath, $extraName, 'local');
                    $attachmentsRechazados[] = [
                        'path' => storage_path('app/' . $savedPath),
                        'name' => $extraName,
                        'mime' => $extraFile->getClientMimeType() ?: 'application/octet-stream'
                    ];
                }
            }
        }

        // --- ADJUNTAR AUTOMÁTICAMENTE FORMATOS DE LIBERACIÓN Y RECHAZO ---
        $pdfDir = storage_path('app/public/liberaciones_pdf');
        foreach ($liberacionesOT as $libRow) {
            if ($libRow->pdf_filename) {
                $libPdfPath = $pdfDir . '/' . $libRow->pdf_filename;
                if (file_exists($libPdfPath)) {
                    $item = [
                        'path' => $libPdfPath,
                        'name' => $libRow->pdf_filename,
                        'mime' => 'application/pdf',
                    ];
                    if ($libRow->decision === 'aprobar') {
                        if (!collect($attachmentsAprobados)->contains(fn($a) => $a['name'] === $libRow->pdf_filename)) {
                            $attachmentsAprobados[] = $item;
                        }
                    } elseif ($libRow->decision === 'rechazar') {
                        if (!collect($attachmentsRechazados)->contains(fn($a) => $a['name'] === $libRow->pdf_filename)) {
                            $attachmentsRechazados[] = $item;
                        }
                    }
                }
            }
        }

        if ($decision === 'rechazar' || $decision === 'mixto') {
            foreach ($scarModelos as $scarMod) {
                if ($scarMod->pdf_filename) {
                    $scarPdfPath = $pdfDir . '/' . $scarMod->pdf_filename;
                    if (file_exists($scarPdfPath)) {
                        $item = [
                            'path' => $scarPdfPath,
                            'name' => $scarMod->pdf_filename,
                            'mime' => 'application/pdf',
                        ];
                        if (!collect($attachmentsRechazados)->contains(fn($a) => $a['name'] === $scarMod->pdf_filename)) {
                            $attachmentsRechazados[] = $item;
                        }
                    }
                }
            }
        }

        // Enviar correos
        $destinosStr = !empty($destinatario) ? $destinatario : config('services.fundicion.almacen', 'almacentec@grupoindsaavedra.com');
        $destinatarios = array_filter(array_map('trim', explode(',', $destinosStr)));

        $destCalidadStr = $request->input('destinatario_calidad', '');
        $destCalidadStr = !empty($destCalidadStr) ? $destCalidadStr : config('services.fundicion.calidad', 'inspecciontec@grupoindsaavedra.com');
        $destCalidad = array_filter(array_map('trim', explode(',', $destCalidadStr)));

        try {
            if ($decision === 'mixto') {
                if ($libAprobada) {
                    $mail = Mail::to($destinatarios);
                    if (!empty($destCalidad)) $mail->cc($destCalidad);
                    $mail->send(new LiberacionModeloMailable($ot, 'aprobado', $libAprobada, $attachmentsAprobados));
                }
                if ($libRechazada) {
                    $mail = Mail::to($destinatarios);
                    if (!empty($destCalidad)) $mail->cc($destCalidad);
                    $mail->send(new LiberacionModeloMailable($ot, 'rechazado', $libRechazada, $attachmentsRechazados));
                }
            } elseif ($decision === 'aprobar') {
                $mail = Mail::to($destinatarios);
                if (!empty($destCalidad)) $mail->cc($destCalidad);
                $mail->send(new LiberacionModeloMailable($ot, 'aprobado', $libAprobada ?: $liberacion, $attachmentsAprobados));
            } else {
                $mail = Mail::to($destinatarios);
                if (!empty($destCalidad)) $mail->cc($destCalidad);
                $mail->send(new LiberacionModeloMailable($ot, 'rechazado', $libRechazada ?: $liberacion, $attachmentsRechazados));
            }

            // Sincronizar carpeta completa de Calidad → Almacén:
            // - Archivos nuevos de Calidad se copian a Almacén.
            // - Archivos con el mismo nombre en ambos se sobreescriben con la versión de Calidad.
            // - Archivos que solo existen en Almacén se conservan.
            $this->syncCalidadToAlmacen($folderName);

            $this->eliminarCarpetasVacias($ot);

            return response()->json([
                'success' => true,
                'message' => 'Alerta enviada y estado finalizado con éxito.'
            ]);
        } catch (\Exception $e) {
            Log::error('Error al enviar correo de alerta de liberacion: ' . $e->getMessage());
            $this->eliminarCarpetasVacias($ot);
            return response()->json([
                'success' => true,
                'message' => 'El estado se actualizó correctamente, pero hubo un detalle al enviar la notificación por correo: ' . $e->getMessage()
            ]);
        }
    }

    // =========================================================================
    // HELPERS PRIVADOS
    // =========================================================================

    private function resolveCaseInsensitivePath(string $path): string
    {
        // Optimización masiva: si la ruta exacta ya existe, devolverla inmediatamente
        if (Storage::disk('local')->exists($path) || Storage::disk('local')->directoryExists($path)) {
            return $path;
        }

        $parts = explode('/', str_replace('\\', '/', $path));
        $resolved = '';

        foreach ($parts as $part) {
            if ($part === '') continue;

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

    private function sanitizePath(string $path): string
    {
        $path = preg_replace('/\.\.+/', '', $path);
        $path = preg_replace('/[\/\\\\]/', '', $path);
        return trim($path);
    }

    private function normalizeOTName(?string $name): string
    {
        if (!$name) return '';
        // Reemplazar guiones especiales y espacios de no ruptura
        $name = str_replace(['—', '–', "\xc2\xa0"], '-', $name);
        // Todo a mayúsculas para evitar problemas de case-sensitivity
        $name = mb_strtoupper($name, 'UTF-8');
        // Eliminar espacios múltiples
        $name = preg_replace('/\s+/', ' ', $name);
        return trim($name);
    }

    private function sanitizeFileNameWithFolder(string $name): string
    {
        // Permitir un solo nivel de carpeta (ej: "Clase/archivo.pdf")
        // Bloquear .. y cualquier intento de subir de nivel
        $name = preg_replace('/\.\.+/', '', $name);
        // Quitamos el preg_replace agresivo para permitir símbolos como () - _ etc.
        $name = str_replace('\\', '/', $name); 
        return trim($name);
    }

    private function sanitizeFileName(string $name): string
    {
        // Evitar path traversal
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
     * Sincroniza la carpeta completa de la OT desde CALIDAD_FUNDICION hacia ALMACEN_FUNDICION.
     *
     * Reglas:
     *  - Archivos nuevos en Calidad  → se copian a Almacén.
     *  - Archivos con el mismo nombre en ambos  → la versión de Calidad sobreescribe la de Almacén.
     *  - Archivos que solo existen en Almacén  → se conservan intactos.
     *
     * @param string $folderName  Nombre de carpeta de la OT (ya sanitizado y normalizado)
     */
    private function syncCalidadToAlmacen(string $folderName): void
    {
        $calidadDir = self::CALIDAD_DIR . '/' . $folderName;
        $almacenDir = self::ALMACEN_DIR . '/' . $folderName;

        if (!Storage::disk('local')->exists($calidadDir)) {
            return;
        }

        $allCalidadFiles = Storage::disk('local')->allFiles($calidadDir);

        foreach ($allCalidadFiles as $srcFile) {
            $srcNorm = str_replace('\\', '/', $srcFile);
            $calidadDirNorm = str_replace('\\', '/', $calidadDir);
            $relPath = ltrim(substr($srcNorm, strlen($calidadDirNorm)), '/');

            $targetPath = $almacenDir . '/' . $relPath;
            $targetDir  = dirname($targetPath);

            if (!Storage::disk('local')->exists($targetDir)) {
                Storage::disk('local')->makeDirectory($targetDir);
            }

            // Sobreescribir si ya existe (Calidad tiene prioridad) o copiar si es nuevo
            Storage::disk('local')->put($targetPath, Storage::disk('local')->get($srcFile));
        }
    }

    private function copyDirectoryRecursive(string $src, string $dst): void
    {
        if (!Storage::disk('local')->exists($src)) {
            return;
        }

        $files = Storage::disk('local')->allFiles($src);
        foreach ($files as $file) {
            $relPath = ltrim(substr($file, strlen($src)), '/');
            $targetPath = $dst . '/' . $relPath;

            $targetDir = dirname($targetPath);
            if (!Storage::disk('local')->exists($targetDir)) {
                Storage::disk('local')->makeDirectory($targetDir);
            }

            Storage::disk('local')->put($targetPath, Storage::disk('local')->get($file));
        }
    }

    private function eliminarCarpetasVacias(string $ot): void
    {
        $folderName = $this->sanitizePath($this->normalizeOTName($ot));
        
        $baseDirs = [
            self::ALMACEN_DIR . '/' . $folderName,
            self::CALIDAD_DIR . '/' . $folderName,
        ];

        foreach ($baseDirs as $bd) {
            if (Storage::disk('local')->exists($bd)) {
                $classesDirs = Storage::disk('local')->directories($bd);
                foreach ($classesDirs as $cd) {
                    $subdirsToCheck = [
                        $cd . '/' . FundicionPaths::DOCUMENTOS_APROBADOS . '/' . FundicionPaths::CALIDAD,
                        $cd . '/' . FundicionPaths::DOCUMENTOS_APROBADOS . '/' . FundicionPaths::ALMACEN,
                        $cd . '/' . FundicionPaths::DOCUMENTOS_RECHAZADOS . '/' . FundicionPaths::CALIDAD,
                        $cd . '/' . FundicionPaths::DOCUMENTOS_RECHAZADOS . '/' . FundicionPaths::ALMACEN,
                        $cd . '/' . FundicionPaths::FORMATOS_LIBERACION,
                    ];
                    foreach ($subdirsToCheck as $sdtc) {
                        if (Storage::disk('local')->exists($sdtc)) {
                            $files = Storage::disk('local')->files($sdtc);
                            $dirs = Storage::disk('local')->directories($sdtc);
                            if (empty($files) && empty($dirs)) {
                                Storage::disk('local')->deleteDirectory($sdtc);
                            }
                        }
                    }
                    // Eliminar DOCUMENTOS_APROBADOS y DOCUMENTOS_RECHAZADOS si quedan vacíos
                    foreach ([FundicionPaths::DOCUMENTOS_APROBADOS, FundicionPaths::DOCUMENTOS_RECHAZADOS] as $pSub) {
                        $pPath = $cd . '/' . $pSub;
                        if (Storage::disk('local')->exists($pPath)) {
                            $files = Storage::disk('local')->files($pPath);
                            $dirs = Storage::disk('local')->directories($pPath);
                            if (empty($files) && empty($dirs)) {
                                Storage::disk('local')->deleteDirectory($pPath);
                            }
                        }
                    }
                }
            }
        }
        
        // Mantener soporte de legacy
        $paths = [
            self::ALMACEN_DIR . '/' . $folderName . '/ayudas_visuales/preordenes/documentos_aprobados',
            self::ALMACEN_DIR . '/' . $folderName . '/ayudas_visuales/preordenes/documentos_rechazados',
            self::CALIDAD_DIR . '/' . $folderName . '/ayudas_visuales/preordenes/documentos_aprobados',
            self::CALIDAD_DIR . '/' . $folderName . '/ayudas_visuales/preordenes/documentos_rechazados',
        ];

        foreach ($paths as $path) {
            if (Storage::disk('local')->exists($path)) {
                $subdirs = Storage::disk('local')->directories($path);
                foreach ($subdirs as $subdir) {
                    $files = Storage::disk('local')->files($subdir);
                    $dirs = Storage::disk('local')->directories($subdir);
                    if (empty($files) && empty($dirs)) {
                        Storage::disk('local')->deleteDirectory($subdir);
                    }
                }
                
                $parentFiles = Storage::disk('local')->files($path);
                $parentDirs = Storage::disk('local')->directories($path);
                if (empty($parentFiles) && empty($parentDirs)) {
                    Storage::disk('local')->deleteDirectory($path);
                }
            }
        }
    }
}
