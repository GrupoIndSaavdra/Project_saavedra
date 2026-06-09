<?php

namespace App\Http\Controllers;

use App\Models\FundicionHistory;
use App\Models\LiberacionModeloFundicion;
use App\Models\Orden_trabajo;
use App\Models\PreOrdenFundicion;
use App\Models\ScarModelo;
use App\Mail\LiberacionModeloMailable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Mail;
use Barryvdh\DomPDF\Facade\Pdf;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class AlmacenFundicionController extends Controller
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
     * 1 = Admin | 2 = Admin | 4 = Calidad | 5 = Almacen
     */
    private const PERFILES_PERMITIDOS = ['1', '2', '4', '5'];

    // =========================================================================
    // GATE DE ACCESO
    // =========================================================================

    private function verificarAcceso(): void
    {
        /** @var \App\Models\User|null $user */
        $user = Auth::user();

        if (!$user || !in_array($user->perfil, self::PERFILES_PERMITIDOS, true)) {
            abort(403, 'Acceso restringido. Solo Almacén y Calidad pueden ver esta sección.');
        }
    }

    // =========================================================================
    // VISTA PRINCIPAL
    // =========================================================================

    /**
     * Muestra la tabla con todos los registros históricos de Almacén,
     * incluyendo su estado Activa/Inactiva.
     *
     * @param \Illuminate\Http\Request $request
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

        return view('almacen.fundicion_index', compact(
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
     * @param \Illuminate\Http\Request $request
     */
    public function getFiles(Request $request)
    {
        $this->verificarAcceso();

        $ot = $this->sanitizePath($request->query('ot', ''));

        if (empty($ot)) {
            return response()->json(['error' => 'Parámetro OT es requerido.'], 422);
        }

        $folderName = $this->sanitizePath($this->normalizeOTName($ot));

        /** @var \App\Models\FundicionHistory|null $history */
        $history = FundicionHistory::where('ot', '=', $ot, 'and')->first();
        if (!$history) {
            $history = FundicionHistory::where('ot', '=', $folderName, 'and')->first();
        }

        // Check by base OT if not found directly
        $baseOt = preg_replace('/_R\d+$/i', '', $ot);
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

        $allOtNames = FundicionHistory::where('ot', '=', $baseOt, 'or')
            ->where('ot', 'LIKE', $baseOt . '_R%', 'or')
            ->pluck('ot')
            ->toArray();
        if (!in_array($ot, $allOtNames)) {
            $allOtNames[] = $ot;
        }

        $modelPreOrden = PreOrdenFundicion::where('ot', $ot)->where('pdf_filename', 'NOT LIKE', '%Casting%')->first();
        $activeClasses = [];
        if ($modelPreOrden) {
            $filas = $modelPreOrden->filas;
            if (is_string($filas)) {
                $filas = json_decode($filas, true);
            }
            if (is_array($filas)) {
                foreach ($filas as $f) {
                    $val = null;
                    if (isset($f['clase'])) {
                        $val = strtolower($f['clase']);
                    } elseif (isset($f['clase_nombre'])) {
                        $val = strtolower($f['clase_nombre']);
                    }
                    if ($val) {
                        foreach (['fondo', 'obturador', 'bombillo', 'molde'] as $kc) {
                            if (strpos($val, $kc) !== false) {
                                $activeClasses[] = $kc;
                            }
                        }
                    }
                }
            }
        }
        if (empty($activeClasses)) {
            $activeClasses = ['fondo', 'bombillo', 'molde', 'obturador'];
        }

        $user = Auth::user();
        $isQuality = ($user->perfil == 4);
        $isAdmin = ($user->perfil == 1 || $user->perfil == 2);
        $soloPreorden = $request->query('solo_preorden', '0') === '1';

        $dibujos = collect([]);
        $ayudas = collect([]);
        $generatedFiles = collect([]);

        foreach ($allOtNames as $relatedOt) {
            $relFolder = $this->sanitizePath($this->normalizeOTName($relatedOt));
            
            // Dibujos y Ayudas Visuales (no pre-ordenes) son cargados por Admin y compartidos.
            $sharedDir = self::ALMACEN_DIR . '/' . $relFolder;
            $sharedAyudasDir = $sharedDir . '/ayudas_visuales';

            if (!$soloPreorden) {
                // 1. Obtener dibujos principales (desde sharedDir)
                if (Storage::disk('local')->exists($sharedDir)) {
                    $relatedDibujos = collect(Storage::disk('local')->allFiles($sharedDir))
                        ->filter(function ($f) use ($sharedDir, $relatedOt, $ot, $activeClasses) {
                            $rel = str_replace($sharedDir . '/', '', $f);
                            $isBase = strpos($rel, 'ayudas_visuales/') !== 0
                                && strpos($rel, 'preordenes/') !== 0
                                && stripos($rel, 'Documentos_Aprobados/') !== 0
                                && stripos($rel, 'Documentos_Rechazados/') !== 0
                                && strtolower(pathinfo($f, PATHINFO_EXTENSION)) === 'pdf';
                            if (!$isBase) return false;

                            $fileLower = strtolower($rel);
                            $knownClasses = ['fondo', 'obturador', 'bombillo', 'molde'];
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
                                    if (strpos($fileLower, $ac) !== false) {
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
                        ->map(function ($f) use ($relatedOt, $sharedDir) {
                            $fNorm = str_replace('\\', '/', $f);
                            $dirPathNorm = str_replace('\\', '/', $sharedDir);
                            $relName = ltrim(str_replace($dirPathNorm, '', $fNorm), '/');
                            $utf8RelName = $this->toUtf8($relName);

                            return [
                                'nombre' => $utf8RelName,
                                'tipo' => 'dibujo',
                                'url' => route('almacen.fundicion.serve', [
                                    'ot' => $relatedOt,
                                    'archivo' => $utf8RelName,
                                    'tipo' => 'dibujo',
                                ]),
                            ];
                        });
                    $dibujos = $dibujos->merge($relatedDibujos);
                }

                // 2a. Ayudas visuales reales: PDFs que NO están bajo preordenes/ (desde sharedAyudasDir)
                if (Storage::disk('local')->exists($sharedAyudasDir)) {
                    $relatedAyudas = collect(Storage::disk('local')->allFiles($sharedAyudasDir))
                        ->filter(function ($f) use ($sharedAyudasDir, $relatedOt, $ot, $activeClasses) {
                            $fNorm      = str_replace('\\', '/', $f);
                            $dirNorm    = str_replace('\\', '/', $sharedAyudasDir);
                            $relName    = ltrim(str_replace($dirNorm, '', $fNorm), '/');
                            $ext        = strtolower(pathinfo($f, PATHINFO_EXTENSION));
                            $isAyuda = $ext === 'pdf' && !str_starts_with($relName, 'preordenes/');
                            if (!$isAyuda) return false;

                            $fileLower = strtolower($relName);
                            $knownClasses = ['fondo', 'obturador', 'bombillo', 'molde'];
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
                                    if (strpos($fileLower, $ac) !== false) {
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
                        ->map(function ($f) use ($sharedAyudasDir, $relatedOt) {
                            $fNorm       = str_replace('\\', '/', $f);
                            $dirNorm     = str_replace('\\', '/', $sharedAyudasDir);
                            $relName     = ltrim(str_replace($dirNorm, '', $fNorm), '/');
                            $utf8RelName = $this->toUtf8($relName);
                            return [
                                'nombre' => $utf8RelName,
                                'tipo'   => 'ayuda',
                                'url'    => route('almacen.fundicion.serve', ['ot' => $relatedOt, 'archivo' => $utf8RelName, 'tipo' => 'ayuda']),
                            ];
                        });
                    $ayudas = $ayudas->merge($relatedAyudas);
                }
            }

            // 3. Documentos generados (Preordenes, Evidencias, Confirmaciones, LDM, SCAR)
            $dirsToScan = [];

            // --- NUEVO ESQUEMA DE APARTADOS ESPECÍFICOS ---
            // Todos pueden leer de Documentos_Aprobados
            $dirsToScan[] = [
                'path' => self::ALMACEN_DIR . '/' . $relFolder . '/Documentos_Aprobados',
                'origin' => 'aprobado',
                'prefix' => 'Documentos_Aprobados/'
            ];
            // Todos pueden leer de Documentos_Rechazados
            $dirsToScan[] = [
                'path' => self::ALMACEN_DIR . '/' . $relFolder . '/Documentos_Rechazados',
                'origin' => 'rechazado',
                'prefix' => 'Documentos_Rechazados/'
            ];

            if ($isAdmin) {
                $dirsToScan[] = [
                    'path' => self::CALIDAD_DIR . '/' . $relFolder . '/Documentos_Aprobados',
                    'origin' => 'aprobado',
                    'prefix' => 'Documentos_Aprobados/'
                ];
                $dirsToScan[] = [
                    'path' => self::CALIDAD_DIR . '/' . $relFolder . '/Documentos_Rechazados',
                    'origin' => 'rechazado',
                    'prefix' => 'Documentos_Rechazados/'
                ];

                // Legacy
                $dirsToScan[] = [
                    'path' => self::ALMACEN_DIR . '/' . $relFolder . '/ayudas_visuales/preordenes',
                    'origin' => 'almacen',
                    'prefix' => 'preordenes/'
                ];
                $dirsToScan[] = [
                    'path' => self::CALIDAD_DIR . '/' . $relFolder . '/ayudas_visuales/preordenes',
                    'origin' => 'calidad',
                    'prefix' => 'preordenes/'
                ];
            } elseif ($isQuality) {
                $dirsToScan[] = [
                    'path' => self::CALIDAD_DIR . '/' . $relFolder . '/Documentos_Aprobados',
                    'origin' => 'aprobado',
                    'prefix' => 'Documentos_Aprobados/'
                ];
                $dirsToScan[] = [
                    'path' => self::CALIDAD_DIR . '/' . $relFolder . '/Documentos_Rechazados',
                    'origin' => 'rechazado',
                    'prefix' => 'Documentos_Rechazados/'
                ];

                // Legacy
                $dirsToScan[] = [
                    'path' => self::CALIDAD_DIR . '/' . $relFolder . '/ayudas_visuales/preordenes',
                    'origin' => 'calidad',
                    'prefix' => 'preordenes/'
                ];
            } else {
                // Legacy
                $dirsToScan[] = [
                    'path' => self::ALMACEN_DIR . '/' . $relFolder . '/ayudas_visuales/preordenes',
                    'origin' => 'almacen',
                    'prefix' => 'preordenes/'
                ];
            }

            // Legacy/Compatibilidad
            $dirsToScan[] = [
                'path' => self::ALMACEN_DIR . '/' . $relFolder . '/preordenes/documentos_aprobados',
                'origin' => 'aprobado',
                'prefix' => 'preordenes/documentos_aprobados/'
            ];
            $dirsToScan[] = [
                'path' => self::ALMACEN_DIR . '/' . $relFolder . '/preordenes/documentos_rechazados',
                'origin' => 'rechazado',
                'prefix' => 'preordenes/documentos_rechazados/'
            ];

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

                            $knownClasses = ['fondo', 'obturador', 'bombillo', 'molde'];
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
                                    if (strpos($fileLower, $ac) !== false) {
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
                                'url' => route('almacen.fundicion.serve', [
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

        // De-duplicar documentos por nombre
        $generatedFiles = $generatedFiles->unique('nombre')->values();
        $dibujos = $dibujos->unique('nombre')->values();
        $ayudas = $ayudas->unique('nombre')->values();

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

        $historyLatest = FundicionHistory::where('ot', '=', $ot, 'and')->first() ?: FundicionHistory::where('ot', 'LIKE', $baseOt . '%', 'and')->orderBy('id', 'desc')->first();
        $preOrden = PreOrdenFundicion::where('ot', '=', $historyLatest->ot, 'and')->first();
        $fechaEntrega = $preOrden && $preOrden->fecha_entrega 
            ? ($preOrden->fecha_entrega instanceof \DateTimeInterface 
                ? $preOrden->fecha_entrega->format('Y-m-d') 
                : substr((string)$preOrden->fecha_entrega, 0, 10)) 
            : null;

        return response()->json([
            'existe' => true,
            'archivos' => $allFiles,
            'ot' => $ot,
            'status' => $historyLatest->status,
            'tiene_modelo' => (bool) $historyLatest->tiene_modelo,
            'casting_pdf_generated' => (bool) $historyLatest->casting_pdf_generated,
            'alert_sent_at' => $historyLatest->alert_sent_at?->format('d/m/Y H:i'),
            'fecha_entrega' => $fechaEntrega,
        ]);
    }

    // =========================================================================
    // SERVIR ARCHIVOS (Solo Lectura)
    // =========================================================================

    /**
     * Sirve un PDF desde el directorio aislado FUNDICION_ALMACEN/.
     *
     * @param \Illuminate\Http\Request $request
     */
    public function serveFile(Request $request): BinaryFileResponse
    {
        $this->verificarAcceso();

        $ot = $this->sanitizePath($request->query('ot', ''));
        $archivo = $this->sanitizeFileNameWithFolder($request->query('archivo', ''));
        $tipo = $request->query('tipo', 'dibujo');
        $origin = $request->query('origin', '');

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

            if ($user->perfil == 4) { // Calidad
                // Calidad solo ve preordenes si pre_orden_email_sent es true
                $isPreorden = ($tipo === 'otro' || str_starts_with(strtolower($archivo), 'preordenes/'));
                $isLdmOrScar = str_contains(strtolower($archivo), 'documentos_aprobados') || str_contains(strtolower($archivo), 'documentos_rechazados') || str_contains($archivo, 'F-CCL-LDM') || str_contains($archivo, 'SCAR');
                if ($isPreorden && !$isLdmOrScar && !$history->pre_orden_email_sent) {
                    abort(403, 'Acceso denegado. La pre-orden no ha sido alertada por Almacén.');
                }
            } elseif ($user->perfil == 5) { // Almacén
                // Almacén solo ve PDFs de Calidad si se envió la alerta (aprobado o scar alertado)
                $isCalidadDoc = ($tipo === 'liberacion' || 
                                 str_contains($archivo, 'F-CCL-LDM') || 
                                 str_contains($archivo, 'F-CCL-SCAR') || 
                                 str_contains($archivo, 'SCAR'));
                $isPreordenFlowDoc = str_contains(strtolower($archivo), 'documentos_aprobados') || str_contains(strtolower($archivo), 'documentos_rechazados');
                if ($isCalidadDoc && !$isPreordenFlowDoc) {
                    $status = $history->calidad_revision_status;
                    $calidadAlertaEnviada = (
                        in_array($status, ['aprobado', 'rechazado', 'mixto', 'calidad_aprobado', 'calidad_rechazado', 'calidad_mixto', 'casting_aprobado']) || 
                        ScarModelo::where('ot', '=', $ot, 'and')->where('estatus', '=', 'alertado', 'and')->exists() ||
                        ScarModelo::where('ot', '=', $folderName, 'and')->where('estatus', '=', 'alertado', 'and')->exists()
                    );
                    if (!$calidadAlertaEnviada) {
                        abort(403, 'Acceso denegado. Calidad no ha enviado la alerta de rechazo o aprobación.');
                    }
                }
            }
        }

        if ($tipo === 'liberacion') {
            $baseDir = 'public/liberaciones_pdf';
        } else {
            if ($tipo === 'ayuda' || $tipo === 'otro') {
                // Archivos en Documentos_Aprobados / Documentos_Rechazados viven en el root de la OT
                if ($origin === 'aprobado' || $origin === 'rechazado') {
                    $baseDir = self::ALMACEN_DIR . '/' . $folderName;
                } elseif ($origin === 'calidad' || ($user->perfil == 4 && empty($origin))) {
                    $baseDir = self::CALIDAD_DIR . '/' . $folderName . '/ayudas_visuales';
                } else {
                    $baseDir = self::ALMACEN_DIR . '/' . $folderName . '/ayudas_visuales';
                }
            } else {
                $baseDir = self::ALMACEN_DIR . '/' . $folderName;
            }
        }

        // Si el directorio principal no existe, intentar fallback cross-OT (base ↔ _R1/_R2)
        if (!Storage::disk('local')->exists($baseDir)) {
            $baseOtRaw = preg_replace('/_R\d+$/', '', $ot);
            $baseFolder = $this->sanitizePath($this->normalizeOTName($baseOtRaw));
            $altDirs = [
                self::ALMACEN_DIR . '/' . $baseFolder,
                self::ALMACEN_DIR . '/' . $baseFolder . '/ayudas_visuales',
                self::CALIDAD_DIR . '/' . $baseFolder . '/ayudas_visuales',
                self::ALMACEN_DIR . '/' . $folderName . '/ayudas_visuales',
                self::CALIDAD_DIR . '/' . $folderName . '/ayudas_visuales',
            ];
            $found = false;
            foreach ($altDirs as $altDir) {
                if (Storage::disk('local')->exists($altDir)) {
                    $baseDir = $altDir;
                    $found = true;
                    break;
                }
            }
            if (!$found) {
                abort(404, 'Directorio no encontrado.');
            }
        }

        $files = Storage::disk('local')->allFiles($baseDir);
        $foundFile = null;
        foreach ($files as $f) {
            $fNorm = str_replace('\\', '/', $f);
            $baseDirNorm = str_replace('\\', '/', $baseDir);
            $relName = ltrim(str_replace($baseDirNorm, '', $fNorm), '/');
            
            $utf8RelName = $this->toUtf8($relName);
            if ($utf8RelName === $archivo) {
                if ($tipo === 'dibujo' && strpos($relName, 'ayudas_visuales/') === 0) continue;
                
                $foundFile = $f;
                break;
            }
        }

        // FALLBACK ampliado: buscar en todas las carpetas relacionadas de la OT (base + reprocesos)
        if (!$foundFile) {
            $baseOtRaw = preg_replace('/_R\d+$/', '', $ot);
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
            ];
            foreach ($possibleDirs as $possibleDir) {
                if ($possibleDir === $baseDir) continue;
                if (!Storage::disk('local')->exists($possibleDir)) continue;
                
                $pFiles = Storage::disk('local')->allFiles($possibleDir);
                foreach ($pFiles as $f) {
                    $fNorm = str_replace('\\', '/', $f);
                    $pDirNorm = str_replace('\\', '/', $possibleDir);
                    $relName = ltrim(str_replace($pDirNorm, '', $fNorm), '/');
                    
                    $utf8RelName = $this->toUtf8($relName);
                    if ($utf8RelName === $archivo) {
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
     * @param \Illuminate\Http\Request $request
     */
    public function deleteFile(Request $request)
    {
        $this->verificarAcceso();

        $ot = $this->sanitizePath($request->input('ot', ''));
        $archivo = $this->sanitizeFileNameWithFolder($request->input('archivo', ''));
        $tipo = $request->input('tipo', 'otro');

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

            if ($user->perfil == 4) { // Calidad
                // Calidad solo ve preordenes si pre_orden_email_sent es true
                $isPreorden = ($tipo === 'otro' || str_starts_with(strtolower($archivo), 'preordenes/'));
                $isLdmOrScar = str_contains(strtolower($archivo), 'documentos_aprobados') || str_contains(strtolower($archivo), 'documentos_rechazados') || str_contains($archivo, 'F-CCL-LDM') || str_contains($archivo, 'SCAR');
                if ($isPreorden && !$isLdmOrScar && !$history->pre_orden_email_sent) {
                    return response()->json(['success' => false, 'error' => 'Acceso denegado. La pre-orden no ha sido alertada por Almacén.'], 403);
                }
            } elseif ($user->perfil == 5) { // Almacén
                // Almacén solo ve PDFs de Calidad si se envió la alerta (aprobado o scar alertado)
                $isCalidadDoc = ($tipo === 'liberacion' || 
                                 str_contains($archivo, 'F-CCL-LDM') || 
                                 str_contains($archivo, 'F-CCL-SCAR') || 
                                 str_contains($archivo, 'SCAR'));
                $isPreordenFlowDoc = str_contains(strtolower($archivo), 'documentos_aprobados') || str_contains(strtolower($archivo), 'documentos_rechazados');
                if ($isCalidadDoc && !$isPreordenFlowDoc) {
                    $status = $history->calidad_revision_status;
                    $calidadAlertaEnviada = (
                        in_array($status, ['aprobado', 'rechazado', 'mixto', 'calidad_aprobado', 'calidad_rechazado', 'calidad_mixto', 'casting_aprobado']) || 
                        ScarModelo::where('ot', '=', $ot, 'and')->where('estatus', '=', 'alertado', 'and')->exists() ||
                        ScarModelo::where('ot', '=', $folderName, 'and')->where('estatus', '=', 'alertado', 'and')->exists()
                    );
                    if (!$calidadAlertaEnviada) {
                        return response()->json(['success' => false, 'error' => 'Acceso denegado. Calidad no ha enviado la alerta de rechazo o aprobación.'], 403);
                    }
                }
            }
        }

        $origin = $request->input('origin', '');
        if ($tipo === 'liberacion') {
            $baseDir = 'public/liberaciones_pdf';
        } else {
            if ($tipo === 'ayuda' || $tipo === 'otro') {
                // Archivos en Documentos_Aprobados / Documentos_Rechazados viven en el root de la OT
                if ($origin === 'aprobado' || $origin === 'rechazado') {
                    $baseDir = self::ALMACEN_DIR . '/' . $folderName;
                } elseif ($origin === 'calidad' || ($user->perfil == 4 && empty($origin))) {
                    $baseDir = self::CALIDAD_DIR . '/' . $folderName . '/ayudas_visuales';
                } else {
                    $baseDir = self::ALMACEN_DIR . '/' . $folderName . '/ayudas_visuales';
                }
            } else {
                $baseDir = self::ALMACEN_DIR . '/' . $folderName;
            }
        }

        if (!Storage::disk('local')->exists($baseDir)) {
            // Fallback ampliado incluyendo la OT base para reprocesos
            $baseOtRaw = preg_replace('/_R\d+$/', '', $ot);
            $baseFolder = $this->sanitizePath($this->normalizeOTName($baseOtRaw));
            $altDirs = [
                self::ALMACEN_DIR . '/' . $baseFolder,
                self::ALMACEN_DIR . '/' . $baseFolder . '/ayudas_visuales',
                self::CALIDAD_DIR . '/' . $baseFolder . '/ayudas_visuales',
                self::ALMACEN_DIR . '/' . $folderName . '/ayudas_visuales',
                self::CALIDAD_DIR . '/' . $folderName . '/ayudas_visuales',
            ];
            $found = false;
            foreach ($altDirs as $altDir) {
                if (Storage::disk('local')->exists($altDir)) {
                    $baseDir = $altDir;
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
        foreach ($files as $f) {
            $fNorm = str_replace('\\', '/', $f);
            $baseDirNorm = str_replace('\\', '/', $baseDir);
            $relName = ltrim(str_replace($baseDirNorm, '', $fNorm), '/');
            
            $utf8RelName = $this->toUtf8($relName);
            if ($utf8RelName === $archivo) {
                if ($tipo === 'dibujo' && strpos($relName, 'ayudas_visuales/') === 0) continue;
                
                $foundFile = $f;
                break;
            }
        }

        // FALLBACK: Si no se encuentra en el directorio calculado, buscamos en los otros directorios válidos para esta OT
        if (!$foundFile) {
            $possibleDirs = [
                self::ALMACEN_DIR . '/' . $folderName,
                self::CALIDAD_DIR . '/' . $folderName,
                self::ALMACEN_DIR . '/' . $folderName . '/ayudas_visuales',
                self::CALIDAD_DIR . '/' . $folderName . '/ayudas_visuales',
            ];
            foreach ($possibleDirs as $possibleDir) {
                if ($possibleDir === $baseDir) continue;
                if (!Storage::disk('local')->exists($possibleDir)) continue;
                
                $pFiles = Storage::disk('local')->allFiles($possibleDir);
                foreach ($pFiles as $f) {
                    $fNorm = str_replace('\\', '/', $f);
                    $pDirNorm = str_replace('\\', '/', $possibleDir);
                    $relName = ltrim(str_replace($pDirNorm, '', $fNorm), '/');
                    
                    $utf8RelName = $this->toUtf8($relName);
                    if ($utf8RelName === $archivo) {
                        $foundFile = $f;
                        break 2;
                    }
                }
            }
        }

        if (!$foundFile) {
            return response()->json(['success' => false, 'error' => 'Archivo no encontrado.'], 404);
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
    // HELPERS PRIVADOS
    // =========================================================================

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
        $name = preg_replace('/[^a-zA-Z0-9_\-\.\s\/]/', '_', $name); // Permitir /
        return trim($name, '_.');
    }

    private function sanitizeFileName(string $name): string
    {
        $name = preg_replace('/\.\.+/', '', $name);
        $name = preg_replace('/[\/\\\\]/', '', $name);
        return trim($name) ?: 'archivo.pdf';
    }

    private function toUtf8(string $string): string
    {
        if (!mb_check_encoding($string, 'UTF-8')) {
            return mb_convert_encoding($string, 'UTF-8', 'Windows-1252');
        }
        return $string;
    }

    // =========================================================================
    // ACCIONES DE MODELOS
    // =========================================================================

    /**
     * Actualiza el estado del modelo para una OT (Boton "Si").
     * Marca calidad_revision_status como pendiente para que Calidad actue.
     */
    public function updateModelStatus(Request $request)
    {
        $this->verificarAcceso();

        $ot = $request->input('ot');
        $history = FundicionHistory::where('ot', '=', $ot, 'and')->first();

        if (!$history) {
            return response()->json(['success' => false, 'message' => 'Registro no encontrado.'], 404);
        }

        // ── Guardar archivos de recepción adjuntos (Bloque 2) ──────────────────
        if ($request->hasFile('archivos')) {
            $folderName   = $this->sanitizePath($this->normalizeOTName($ot));
            $destDir      = self::ALMACEN_DIR . '/' . $folderName . '/ayudas_visuales/preordenes/confirmacion_modelo';

            if (!Storage::disk('local')->exists($destDir)) {
                Storage::disk('local')->makeDirectory($destDir);
            }

            foreach ($request->file('archivos') as $file) {
                $ext      = $file->getClientOriginalExtension();
                $safeName = preg_replace('/[^A-Za-z0-9_\-.]/', '_', pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME));
                $stamp    = date('d_m_Y_H_i_s');
                $fileName = "ConfirmacionModelo_{$safeName}_{$stamp}.{$ext}";
                Storage::disk('local')->put($destDir . '/' . $fileName, file_get_contents($file->getRealPath()));
            }
        }

        $history->tiene_modelo = true;
        $history->save();

        // Crear o actualizar el registro de liberacion indicando el origen
        $fecha = $request->input('fecha');
        LiberacionModeloFundicion::updateOrCreate(
            ['ot' => $ot],
            [
                'estado'      => 'pendiente',
                'tipo_origen' => 'con_modelo',
                'fecha_revision' => $fecha ? date('Y-m-d H:i:s', strtotime($fecha)) : now()
            ]
        );

        return response()->json([
            'success' => true,
            'message' => "Se ha registrado que la OT {$ot} ya cuenta con modelo. Calidad debe revisar."
        ]);
    }

    /**
     * Obtiene datos de la OT para el modal de Pre-Orden.
     */
    public function getOtData(Request $request)
    {
        $this->verificarAcceso();

        $otFull = $request->query('ot', '');
        Log::info("getOtData: Consultando OT = " . $otFull);
        // Extraer el número de OT (ej: de "OT 6473 - ..." extraer 6473)
        preg_match('/OT\s*(\d+)/', $otFull, $matches);
        $otId = isset($matches[1]) ? (int) $matches[1] : 0;

        $ot = Orden_trabajo::with(['moldura', 'clases'])->find($otId);

        if (!$ot) {
            return response()->json(['success' => false, 'message' => 'OT no encontrada.'], 404);
        }

        $type = $request->query('type', '');

        $baseOt = preg_replace('/_R\d+$/i', '', $otFull);

        $modelPreOrden = PreOrdenFundicion::where('ot', $otFull)->where('pdf_filename', 'NOT LIKE', '%Casting%')->first();
        $activeClasses = [];
        if ($modelPreOrden) {
            $filas = $modelPreOrden->filas;
            if (is_string($filas)) {
                $filas = json_decode($filas, true);
            }
            if (is_array($filas)) {
                foreach ($filas as $f) {
                    $val = null;
                    if (isset($f['clase'])) {
                        $val = strtolower($f['clase']);
                    } elseif (isset($f['clase_nombre'])) {
                        $val = strtolower($f['clase_nombre']);
                    }
                    if ($val) {
                        foreach (['fondo', 'obturador', 'bombillo', 'molde'] as $kc) {
                            if (strpos($val, $kc) !== false) {
                                $activeClasses[] = $kc;
                            }
                        }
                    }
                }
            }
        }

        // Obtener TODAS las clases para esta OT y filtrarlas por aprobados en liberación
        $clases = $ot->clases->map(fn($c) => [
            'id' => $c->id,
            'nombre' => $c->nombre,
            'pedido' => $c->pedido
        ])->filter(function($c) use ($otFull, $baseOt, $type, $activeClasses) {
            if (!empty($activeClasses) && !in_array(strtolower($c['nombre']), $activeClasses)) {
                return false;
            }

            $clLow = strtolower($c['nombre']);
            $tipo = null;
            if (strpos($clLow, 'fondo') !== false) $tipo = 'Fondo';
            elseif (strpos($clLow, 'obturador') !== false) $tipo = 'Obturador';
            elseif (strpos($clLow, 'molde') !== false) $tipo = 'Molde';
            elseif (strpos($clLow, 'bombillo') !== false) $tipo = 'Bombillo';

            if ($tipo) {
                $isAprobado = LiberacionModeloFundicion::query()
                    ->where(function($q) use ($otFull, $baseOt) {
                        $q->where('ot', '=', $otFull)
                          ->orWhere('ot', '=', $baseOt)
                          ->orWhere('ot', 'LIKE', $baseOt . '_R%');
                    })
                    ->where('tipo_modelo', '=', $tipo, 'and')
                    ->where('estado', '=', 'aprobado', 'and')
                    ->exists();
                if ($type === 'casting') {
                    return $isAprobado; // Para casting, solo queremos los APROBADOS
                } else {
                    return !$isAprobado; // Para pre-orden normal, queremos los NO aprobados/pendientes
                }
            }
            return $type !== 'casting'; // Si no es casting, dejamos los otros. Si es casting, los quitamos
        })->values();

        // Obtener clases vinculadas desde FundicionHistory (Ayudas Visuales asignadas)
        $history = FundicionHistory::where('ot', '=', $otFull, 'and')->first();
        $clasesVinculadas = $history ? ($history->ayudas_config ?? []) : [];
        $clasesVinculadas = collect($clasesVinculadas)->filter(function($claseNombre) use ($otFull, $baseOt, $type, $activeClasses) {
            if (!empty($activeClasses) && !in_array(strtolower($claseNombre), $activeClasses)) {
                return false;
            }

            $clLow = strtolower($claseNombre);
            $tipo = null;
            if (strpos($clLow, 'fondo') !== false) $tipo = 'Fondo';
            elseif (strpos($clLow, 'obturador') !== false) $tipo = 'Obturador';
            elseif (strpos($clLow, 'molde') !== false) $tipo = 'Molde';
            elseif (strpos($clLow, 'bombillo') !== false) $tipo = 'Bombillo';

            if ($tipo) {
                $isAprobado = LiberacionModeloFundicion::query()
                    ->where(function($q) use ($otFull, $baseOt) {
                        $q->where('ot', '=', $otFull)
                          ->orWhere('ot', '=', $baseOt)
                          ->orWhere('ot', 'LIKE', $baseOt . '_R%');
                    })
                    ->where('tipo_modelo', '=', $tipo, 'and')
                    ->where('estado', '=', 'aprobado', 'and')
                    ->exists();
                if ($type === 'casting') {
                    return $isAprobado;
                } else {
                    return !$isAprobado;
                }
            }
            return $type !== 'casting';
        })->values()->toArray();

        $fechaEntrega = $history && $history->fecha_entrega ? $history->fecha_entrega->format('Y-m-d') : '';
        
        if ($type === 'casting') {
            $preOrdenes = PreOrdenFundicion::where('ot', '=', $otFull, 'and')->get();
            
            $folioStr = '';
            if ($preOrdenes->isEmpty()) {
                $folioPath = 'DOCUMENTACION_GIS/folio_casting_config.json';
                $currentFolio = 47;
                
                if (Storage::disk('local')->exists($folioPath)) {
                    $config = json_decode(Storage::disk('local')->get($folioPath), true);
                    $currentFolio = $config['next_folio'] ?? 47;
                } else {
                    Storage::disk('local')->put($folioPath, json_encode(['next_folio' => 47]));
                }
                
                $year = date('Y');
                $folioStr = "PFC-{$year}-" . str_pad($currentFolio, 4, '0', STR_PAD_LEFT);
            } else {
                // Si hay alguna orden, tomar su folio. Si es standard, tomar folio casting si existe, o generar
                $castingPo = $preOrdenes->first(function($po) {
                    if (!empty($po->filas)) {
                        $firstFila = $po->filas[0] ?? [];
                        return isset($firstFila['cant_fabricar']);
                    }
                    return false;
                });
                if ($castingPo) {
                    $folioStr = $castingPo->folio;
                } else {
                    $folioPath = 'DOCUMENTACION_GIS/folio_casting_config.json';
                    $currentFolio = 47;
                    if (Storage::disk('local')->exists($folioPath)) {
                        $config = json_decode(Storage::disk('local')->get($folioPath), true);
                        $currentFolio = $config['next_folio'] ?? 47;
                    }
                    $year = date('Y');
                    $folioStr = "PFC-{$year}-" . str_pad($currentFolio, 4, '0', STR_PAD_LEFT);
                }
            }
            
            return response()->json([
                'success'         => true,
                'moldura'         => $ot->moldura ? $ot->moldura->nombre : 'Sin moldura',
                'clases'          => $clases,
                'clases_vinculadas' => $clasesVinculadas,
                'folio'           => $folioStr,
                'pre_ordenes'     => $preOrdenes,
                'fecha_entrega'   => $fechaEntrega,
            ]);
        }

        // --- Recuperar pre-orden existente desde la BASE DE DATOS ---
        $preOrdenDB = PreOrdenFundicion::where('ot', '=', $otFull, 'and')->first();

        $preordenData = null;
        $folioStr = '';

        if ($preOrdenDB) {
            // Pre-orden existente: recuperar folio y datos para prellenar el formulario
            $folioStr = $preOrdenDB->folio;
            
            // Filtrar las filas ya guardadas de la pre-orden para remover clases aprobadas
            $filasFiltradas = [];
            if (!empty($preOrdenDB->filas)) {
                foreach ($preOrdenDB->filas as $fila) {
                    $claseNombre = $fila['clase'] ?? '';
                    $clLow = strtolower($claseNombre);
                    $tipo = null;
                    if (strpos($clLow, 'fondo') !== false) $tipo = 'Fondo';
                    elseif (strpos($clLow, 'obturador') !== false) $tipo = 'Obturador';
                    elseif (strpos($clLow, 'molde') !== false) $tipo = 'Molde';
                    elseif (strpos($clLow, 'bombillo') !== false) $tipo = 'Bombillo';

                    if ($tipo) {
                        $isAprobado = LiberacionModeloFundicion::query()
                            ->where(function($q) use ($otFull, $baseOt) {
                                $q->where('ot', '=', $otFull)
                                  ->orWhere('ot', '=', $baseOt)
                                  ->orWhere('ot', 'LIKE', $baseOt . '_R%');
                            })
                            ->where('tipo_modelo', '=', $tipo, 'and')
                            ->where('estado', '=', 'aprobado', 'and')
                            ->exists();
                        if ($isAprobado) {
                            continue;
                        }
                    }
                    $filasFiltradas[] = $fila;
                }
            }

            $preordenData = [
                'folio'        => $preOrdenDB->folio,
                'proveedor'    => $preOrdenDB->proveedor,
                'fecha_creacion'=> $preOrdenDB->fecha_creacion ? ($preOrdenDB->fecha_creacion instanceof \DateTimeInterface ? $preOrdenDB->fecha_creacion->format('Y-m-d') : substr((string)$preOrdenDB->fecha_creacion, 0, 10)) : null,
                'fecha_entrega'=> $preOrdenDB->fecha_entrega ? ($preOrdenDB->fecha_entrega instanceof \DateTimeInterface ? $preOrdenDB->fecha_entrega->format('Y-m-d') : substr((string)$preOrdenDB->fecha_entrega, 0, 10)) : null,
                'moldura'      => $preOrdenDB->moldura,
                'observaciones'=> $preOrdenDB->observaciones,
                'filas'        => $filasFiltradas,
                'version'      => $preOrdenDB->version,
                'pdf_filename' => $preOrdenDB->pdf_filename,
            ];
        } else {
            // Pre-orden nueva: calcular siguiente folio desde el contador persistente
            $folioPath = 'DOCUMENTACION_GIS/folio_config.json';
            $currentFolio = 47;

            if (Storage::disk('local')->exists($folioPath)) {
                $config = json_decode(Storage::disk('local')->get($folioPath), true);
                $currentFolio = $config['next_folio'] ?? 47;
                if ($currentFolio < 47) {
                    $currentFolio = 47;
                    $config['next_folio'] = 47;
                    Storage::disk('local')->put($folioPath, json_encode($config));
                }
            } else {
                Storage::disk('local')->put($folioPath, json_encode(['next_folio' => 47]));
            }

            $year = date('Y');
            $folioStr = "MOD-{$year}-" . str_pad($currentFolio, 4, '0', STR_PAD_LEFT);
        }

        return response()->json([
            'success'         => true,
            'moldura'         => $ot->moldura ? $ot->moldura->nombre : 'Sin moldura',
            'clases'          => $clases,
            'clases_vinculadas' => $clasesVinculadas,
            'folio'           => $folioStr,
            'pre_orden_data'  => $preordenData,
        ]);
    }

    /**
     * Guarda o actualiza la pre-orden en base de datos y en disco, retorna el PDF generado.
     */
    public function storePreOrden(Request $request)
    {
        $this->verificarAcceso();

        $data = $request->all();
        $user = Auth::user();

        if ($request->input('type') === 'casting') {
            $hasPage2 = (bool) $request->input('has_page2', false);
            $p1Data = $request->input('page1');
            
            if (empty($p1Data) || empty($p1Data['ot_raw']) || empty($p1Data['filas'])) {
                return response()->json(['success' => false, 'message' => 'Datos de página 1 incompletos.'], 422);
            }
            
            $otRaw = $p1Data['ot_raw'];
            $proveedor1 = $p1Data['proveedor'];
            
            // Clean up any other suppliers if not in page 2
            $keepSuppliers = [$proveedor1];
            if ($hasPage2) {
                $p2Data = $request->input('page2');
                if (!empty($p2Data) && !empty($p2Data['proveedor'])) {
                    $keepSuppliers[] = $p2Data['proveedor'];
                }
            }
            
            // Delete pre-orders for this OT that are not in the keep list
            PreOrdenFundicion::where('ot', '=', $otRaw, 'and')
                ->whereNotIn('proveedor', $keepSuppliers)
                ->delete();
                
            // Process Page 1 and Page 2 (combined PDF generation)
            $saved = $this->saveCastingPreOrdenes($p1Data, ($hasPage2 && isset($p2Data)) ? $p2Data : null, $user);
            
            $pdfs = [];
            if ($saved) {
                $pdfs[] = [
                    'url' => route('almacen.fundicion.serve', [
                        'ot' => $otRaw,
                        'archivo' => 'preordenes/documentos_aprobados/' . $saved['pdf_filename'],
                        'tipo' => 'otro'
                    ]),
                    'filename' => $saved['pdf_filename']
                ];
            }
            
            return response()->json([
                'success' => true,
                'message' => 'Pre-Orden de Casting guardada correctamente.',
                'pdfs' => $pdfs
            ]);
        }

        // 1. Validaciones básicas
        if (empty($data['ot']) || empty($data['filas'])) {
            return response()->json(['success' => false, 'message' => 'Datos incompletos.'], 422);
        }

        // OT completa para búsquedas
        $otRaw = $data['ot_raw'] ?? null;
        $otClean = preg_replace('/[^A-Za-z0-9\-]/', '_', $data['ot']);

        if (empty($otRaw)) {
            $history = FundicionHistory::where('ot', 'LIKE', '%OT ' . $otClean . '%', 'and')->first();
            $otRaw = $history ? $history->ot : ('OT ' . $otClean);
        }

        $baseOt = preg_replace('/_R\d+$/i', '', $otRaw);
        // Filtrar filas para no guardar ni imprimir las clases ya aprobadas
        $filasFiltradas = [];
        if (!empty($data['filas'])) {
            foreach ($data['filas'] as $fila) {
                $claseNombre = $fila['clase'] ?? '';
                $clLow = strtolower($claseNombre);
                $tipo = null;
                if (strpos($clLow, 'fondo') !== false) $tipo = 'Fondo';
                elseif (strpos($clLow, 'obturador') !== false) $tipo = 'Obturador';
                elseif (strpos($clLow, 'molde') !== false) $tipo = 'Molde';
                elseif (strpos($clLow, 'bombillo') !== false) $tipo = 'Bombillo';

                if ($tipo) {
                    $isAprobado = LiberacionModeloFundicion::query()
                        ->where(function($q) use ($otRaw, $baseOt) {
                            $q->where('ot', '=', $otRaw)
                              ->orWhere('ot', '=', $baseOt)
                              ->orWhere('ot', 'LIKE', $baseOt . '_R%');
                        })
                        ->where('tipo_modelo', '=', $tipo, 'and')
                        ->where('estado', '=', 'aprobado', 'and')
                        ->exists();
                    if ($isAprobado) {
                        continue;
                    }
                }
                $filasFiltradas[] = $fila;
            }
        }
        $data['filas'] = $filasFiltradas;

        // 2. Determinar si la pre-orden ya existe en BD para decidir si incrementar folio
        $preOrdenDB = PreOrdenFundicion::where('ot', '=', $otRaw, 'and')->first();
        $existeEnBD = (bool) $preOrdenDB;

        if ($preOrdenDB && $preOrdenDB->fecha_entrega) {
            $data['fecha_entrega'] = $preOrdenDB->fecha_entrega instanceof \DateTimeInterface 
                ? $preOrdenDB->fecha_entrega->format('Y-m-d') 
                : substr((string)$preOrdenDB->fecha_entrega, 0, 10);
        } else {
            $data['fecha_entrega'] = null;
        }

        // 3. Generar el PDF en orientación horizontal
        ini_set('memory_limit', '512M');
        $pdf = Pdf::loadView('pdf.pre_orden', [
            'data' => $data,
            'user' => $user
        ])->setPaper('a4', 'landscape');

        // 4. Definir nombre del archivo y ruta de guardado
        $folio    = preg_replace('/[^A-Za-z0-9\-]/', '_', $data['folio']);
        $moldura  = preg_replace('/[^A-Za-z0-9\-]/', '_', $data['moldura'] ?? '');
        $proveedor = preg_replace('/[^A-Za-z0-9\-]/', '_', $data['proveedor']);
        $fechaStamp = date('d_m_Y_H_i');
        
        // Extraer solo el número de OT para que el nombre del archivo no exceda el MAX_PATH de Windows (260 chars)
        preg_match('/OT\s*(\d+)/i', $otRaw, $matches);
        $otId = $matches[1] ?? (preg_replace('/[^0-9]/', '', $otRaw) ?: 'SN');
        
        $fileName = "Pre-Orden_Fundicion-{$folio}_OT_{$otId}_{$fechaStamp}.pdf";

        $folderName = $this->sanitizePath($this->normalizeOTName($otRaw));
        $otPath = self::ALMACEN_DIR . '/' . $folderName . '/Documentos_Aprobados/Preorden_Modelo';
        $savePath = $otPath . '/' . $fileName;

        if (!Storage::disk('local')->exists($otPath)) {
            Storage::disk('local')->makeDirectory($otPath);
        } else {
            // Delete old PDF file using database reference if exists
            if ($preOrdenDB && $preOrdenDB->pdf_filename) {
                $oldPath = $otPath . '/' . $preOrdenDB->pdf_filename;
                if (Storage::disk('local')->exists($oldPath)) {
                    Storage::disk('local')->delete($oldPath);
                }
            }
            // Also scan and clean up any other files starting with 'PreOrden_' or 'Pre-Orden_' in this folder
            $existingFiles = Storage::disk('local')->files($otPath);
            foreach ($existingFiles as $file) {
                $base = basename($file);
                if (str_starts_with($base, 'PreOrden_') || str_starts_with($base, 'Pre-Orden_')) {
                    Storage::disk('local')->delete($file);
                }
            }
        }

        // 5. Guardar PDF físicamente en servidor
        Storage::disk('local')->put($savePath, $pdf->output());

        // 6. Guardar / actualizar registro en base de datos (upsert por OT)
        PreOrdenFundicion::updateOrCreate(
            ['ot' => $otRaw],
            [
                'folio'        => $data['folio'],
                'proveedor'    => $data['proveedor'],
                'fecha_creacion' => $data['fecha_creacion'],
                'moldura'      => $data['moldura'] ?? null,
                'observaciones'=> $data['observaciones'] ?? null,
                'filas'        => $data['filas'],
                'pdf_filename' => $fileName,
                'version'      => DB::raw('version + 1'),
                'user_id'      => $user ? $user->id : null,
                'user_nombre'  => $user ? $user->name : null,
            ]
        );

        // 7. Actualizar flag de pre_orden_sent en historial de Fundicion
        //    y marcar como pendiente de revision por Calidad
        FundicionHistory::where('ot', '=', $otRaw, 'and')->update([
            'pre_orden_sent'          => true,
        ]);

        // Crear o actualizar registro de liberacion con origen pre_orden
        LiberacionModeloFundicion::updateOrCreate(
            ['ot' => $otRaw],
            ['estado' => 'pendiente', 'tipo_origen' => 'pre_orden']
        );

        // 8. Incrementar Folio global SOLO si es una pre-orden completamente nueva
        if (!$existeEnBD) {
            try {
                $folioPath = 'DOCUMENTACION_GIS/folio_config.json';
                if (Storage::disk('local')->exists($folioPath)) {
                    $config = json_decode(Storage::disk('local')->get($folioPath), true);
                    $currentVal = max($config['next_folio'] ?? 47, 47);
                    $config['next_folio'] = $currentVal + 1;
                    Storage::disk('local')->put($folioPath, json_encode($config));
                }
            } catch (\Exception $e) {
                Log::error("Error incrementando Folio: " . $e->getMessage());
            }
        }

        // 9. Retornar el PDF para descarga automática en el navegador
        return response($pdf->output(), 200)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'attachment; filename="' . $fileName . '"');
    }

    /**
     * Helper para guardar las pre-órdenes de Casting (soporta proveedor único o doble).
     *
     * @param array $p1Data
     * @param array|null $p2Data
     * @param \App\Models\User|null $user
     * @return array
     */
    private function saveCastingPreOrdenes(array $p1Data, ?array $p2Data, $user): array
    {
        $otRaw = $p1Data['ot_raw'];
        $hasPage2 = !empty($p2Data);

        // 1. Filtrar filas válidas
        $p1Data['filas'] = array_values(array_filter($p1Data['filas'], function ($fila) {
            return !empty($fila['id_clase']);
        }));
        
        if ($hasPage2) {
            $p2Data['filas'] = array_values(array_filter($p2Data['filas'], function ($fila) {
                return !empty($fila['id_clase']);
            }));
        }

        // Forzar fecha actual
        $fechaActual = date('Y-m-d');
        $p1Data['fecha_creacion'] = $fechaActual;
        if ($hasPage2) {
            $p2Data['fecha_creacion'] = $fechaActual;
        }

        // Generar PDF combinado
        ini_set('memory_limit', '512M');
        
        $pages = [$p1Data];
        if ($hasPage2) {
            $pages[] = $p2Data;
        }

        $pdf = Pdf::loadView('almacen.pdf_pre_orden_casting', [
            'pages' => $pages,
            'user' => $user
        ])->setPaper('a4', 'landscape');

        $folio = preg_replace('/[^A-Za-z0-9\-]/', '_', $p1Data['folio']);
        $fechaStamp = date('d_m_Y_H_i');
        preg_match('/OT\s*(\d+)/i', $otRaw, $matches);
        $otId = $matches[1] ?? (preg_replace('/[^0-9]/', '', $otRaw) ?: 'SN');
        
        $fileName = "Pre-Orden_Casting-{$folio}_OT_{$otId}_{$fechaStamp}.pdf";
        $folderName = $this->sanitizePath($this->normalizeOTName($otRaw));
        $otPath = self::ALMACEN_DIR . '/' . $folderName . '/ayudas_visuales/preordenes/documentos_aprobados';
        $savePath = $otPath . '/' . $fileName;

        if (!Storage::disk('local')->exists($otPath)) {
            Storage::disk('local')->makeDirectory($otPath);
        } else {
            // Eliminar PDFs viejos de la base de datos si existen para esta OT
            $oldPdfs = PreOrdenFundicion::where('ot', '=', $otRaw, 'and')->pluck('pdf_filename')->filter()->unique();
            foreach ($oldPdfs as $oldFile) {
                $oldPath = $otPath . '/' . $oldFile;
                if (Storage::disk('local')->exists($oldPath)) {
                    Storage::disk('local')->delete($oldPath);
                }
            }
        }

        // Guardar PDF en disco
        Storage::disk('local')->put($savePath, $pdf->output());

        // Guardar en BD para Proveedor 1
        $pre1DB = PreOrdenFundicion::where('ot', '=', $otRaw, 'and')
            ->where('proveedor', '=', $p1Data['proveedor'], 'and')
            ->first();
        $existe1 = (bool) $pre1DB;

        PreOrdenFundicion::updateOrCreate(
            ['ot' => $otRaw, 'proveedor' => $p1Data['proveedor']],
            [
                'folio'          => $p1Data['folio'],
                'fecha_creacion' => $fechaActual,
                'fecha_entrega'  => !empty($p1Data['fecha_entrega']) ? $p1Data['fecha_entrega'] : null,
                'moldura'        => $p1Data['moldura'] ?? null,
                'observaciones'  => $p1Data['observaciones'] ?? null,
                'filas'          => $p1Data['filas'],
                'pdf_filename'   => $fileName,
                'version'        => DB::raw('version + 1'),
                'user_id'        => $user ? $user->id : null,
                'user_nombre'    => $user ? $user->name : null,
            ]
        );

        if (!$existe1) {
            $this->incrementarFolioCasting();
        }

        // Guardar en BD para Proveedor 2 (si existe)
        if ($hasPage2) {
            $pre2DB = PreOrdenFundicion::where('ot', '=', $otRaw, 'and')
                ->where('proveedor', '=', $p2Data['proveedor'], 'and')
                ->first();
            $existe2 = (bool) $pre2DB;

            PreOrdenFundicion::updateOrCreate(
                ['ot' => $otRaw, 'proveedor' => $p2Data['proveedor']],
                [
                    'folio'          => $p2Data['folio'],
                    'fecha_creacion' => $fechaActual,
                    'fecha_entrega'  => !empty($p2Data['fecha_entrega']) ? $p2Data['fecha_entrega'] : null,
                    'moldura'        => $p2Data['moldura'] ?? null,
                    'observaciones'  => $p2Data['observaciones'] ?? null,
                    'filas'          => $p2Data['filas'],
                    'pdf_filename'   => $fileName,
                    'version'        => DB::raw('version + 1'),
                    'user_id'        => $user ? $user->id : null,
                    'user_nombre'    => $user ? $user->name : null,
                ]
            );

            if (!$existe2) {
                $this->incrementarFolioCasting();
            }
        }

        return [
            'pdf_filename' => $fileName
        ];
    }

    /**
     * Incrementa el folio global de casting en la configuración.
     */
    private function incrementarFolioCasting()
    {
        try {
            $folioPath = 'DOCUMENTACION_GIS/folio_casting_config.json';
            $currentVal = 47;
            if (Storage::disk('local')->exists($folioPath)) {
                $config = json_decode(Storage::disk('local')->get($folioPath), true);
                $currentVal = max($config['next_folio'] ?? 47, 47);
                $config['next_folio'] = $currentVal + 1;
                Storage::disk('local')->put($folioPath, json_encode($config));
            } else {
                Storage::disk('local')->put($folioPath, json_encode(['next_folio' => 48]));
            }
        } catch (\Exception $e) {
            Log::error('Error incrementando folio casting: ' . $e->getMessage());
        }
    }

    /**
     * Envía la pre-orden y los adjuntos seleccionados por correo electrónico (Fase 2).
     */
    public function sendEmailPreOrden(Request $request)
    {
        $this->verificarAcceso();

        $ot = $request->input('ot');
        $destinatario = $request->input('destinatario');

        if (empty($ot) || empty($destinatario) || empty($request->input('fecha_entrega'))) {
            return response()->json([
                'success' => false, 
                'message' => 'La OT, el Destinatario y la Fecha de Entrega son requeridos.'
            ], 422);
        }

        // Obtener la OT
        $history = FundicionHistory::where('ot', '=', $ot, 'and')->first();
        if (!$history) {
            return response()->json(['success' => false, 'message' => 'Historial de la OT no encontrado.'], 404);
        }

        $folderName = $this->sanitizePath($this->normalizeOTName($ot));
        $dirPath = self::ALMACEN_DIR . '/' . $folderName;
        $ayudasDirPath = $dirPath . '/ayudas_visuales';

        // Obtener datos guardados de la pre-orden desde la base de datos (filtrados por tipo de envío)
        $tipoEnvio = $request->input('tipo');
        if (empty($tipoEnvio)) {
            $hasCasting = PreOrdenFundicion::where('ot', '=', $ot, 'and')
                ->where('pdf_filename', 'LIKE', '%Casting%')
                ->exists();
            $hasModelo = PreOrdenFundicion::where('ot', '=', $ot, 'and')
                ->where('pdf_filename', 'NOT LIKE', '%Casting%')
                ->exists();
            if ($hasCasting && !$hasModelo) {
                $tipoEnvio = 'casting';
            } elseif ($hasModelo && !$hasCasting) {
                $tipoEnvio = 'modelo';
            } else {
                $tipoEnvio = 'modelo';
            }
        }

        if ($tipoEnvio === 'casting') {
            $preOrdenes = PreOrdenFundicion::where('ot', '=', $ot, 'and')
                ->where('pdf_filename', 'LIKE', '%Casting%')
                ->get();
        } else {
            $preOrdenes = PreOrdenFundicion::where('ot', '=', $ot, 'and')
                ->where('pdf_filename', 'NOT LIKE', '%Casting%')
                ->get();
        }

        if ($preOrdenes->isEmpty()) {
            return response()->json(['success' => false, 'message' => 'No se encontró la pre-orden en la base de datos.'], 404);
        }

        // Si se envió una fecha de entrega en Fase 2, guardarla en BD y regenerar el PDF
        if ($request->filled('fecha_entrega')) {
            foreach ($preOrdenes as $preOrden) {
                $preOrden->fecha_entrega = $request->input('fecha_entrega');
                $preOrden->save();
            }

            $isCastingPo = $preOrdenes->contains(function ($po) {
                return $po->pdf_filename && (strpos(strtolower($po->pdf_filename), 'casting') !== false);
            });

            if ($isCastingPo) {
                // Generar PDF combinado de Casting
                $pages = [];
                foreach ($preOrdenes as $preOrden) {
                    $fechaValStr = $preOrden->fecha_creacion ? ($preOrden->fecha_creacion instanceof \DateTimeInterface ? $preOrden->fecha_creacion->format('Y-m-d') : substr((string)$preOrden->fecha_creacion, 0, 10)) : null;
                    $fechaEntregaValStr = $preOrden->fecha_entrega ? ($preOrden->fecha_entrega instanceof \DateTimeInterface ? $preOrden->fecha_entrega->format('Y-m-d') : substr((string)$preOrden->fecha_entrega, 0, 10)) : null;

                    $pages[] = [
                        'proveedor' => $preOrden->proveedor,
                        'fecha_creacion' => $fechaValStr,
                        'fecha_entrega' => $fechaEntregaValStr,
                        'folio' => $preOrden->folio,
                        'moldura' => $preOrden->moldura,
                        'ot' => $ot,
                        'observaciones' => $preOrden->observaciones,
                        'filas' => $preOrden->filas
                    ];
                }

                $firstPo = $preOrdenes->first();
                $creator = \App\Models\User::where('id', '=', $firstPo->user_id, 'and')->first() ?: Auth::user();

                $pdf = Pdf::loadView('almacen.pdf_pre_orden_casting', [
                    'pages' => $pages,
                    'user' => $creator
                ])->setPaper('a4', 'landscape');

                $folderName = $this->sanitizePath($this->normalizeOTName($ot));
                $otPath = self::ALMACEN_DIR . '/' . $folderName . '/Documentos_Aprobados/Preorden_Casting';
                $savePath = $otPath . '/' . $firstPo->pdf_filename;

                if (!Storage::disk('local')->exists($otPath)) {
                    Storage::disk('local')->makeDirectory($otPath);
                }
                Storage::disk('local')->put($savePath, $pdf->output());
            } else {
                // Modelo Pre-orden flow
                foreach ($preOrdenes as $preOrden) {
                    $fechaValStr = $preOrden->fecha_creacion ? ($preOrden->fecha_creacion instanceof \DateTimeInterface ? $preOrden->fecha_creacion->format('Y-m-d') : substr((string)$preOrden->fecha_creacion, 0, 10)) : null;
                    $fechaEntregaValStr = $preOrden->fecha_entrega ? ($preOrden->fecha_entrega instanceof \DateTimeInterface ? $preOrden->fecha_entrega->format('Y-m-d') : substr((string)$preOrden->fecha_entrega, 0, 10)) : null;

                    $data = [
                        'proveedor' => $preOrden->proveedor,
                        'fecha_creacion' => $fechaValStr,
                        'fecha_entrega' => $fechaEntregaValStr,
                        'folio' => $preOrden->folio,
                        'moldura' => $preOrden->moldura,
                        'ot' => $ot,
                        'observaciones' => $preOrden->observaciones,
                        'filas' => $preOrden->filas
                    ];

                    $creator = \App\Models\User::where('id', '=', $preOrden->user_id, 'and')->first() ?: Auth::user();

                    $pdf = Pdf::loadView('pdf.pre_orden', [
                        'data' => $data,
                        'user' => $creator
                    ])->setPaper('a4', 'landscape');

                    $folderName = $this->sanitizePath($this->normalizeOTName($ot));
                    $otPath = self::ALMACEN_DIR . '/' . $folderName . '/Documentos_Aprobados/Preorden_Modelo';
                    $savePath = $otPath . '/' . $preOrden->pdf_filename;

                    if (!Storage::disk('local')->exists($otPath)) {
                        Storage::disk('local')->makeDirectory($otPath);
                    }
                    Storage::disk('local')->put($savePath, $pdf->output());
                }
            }
        }

        $firstPo = $preOrdenes->first();
        $folioVal = $firstPo->folio;
        $molduraVal = $firstPo->moldura ?: 'N/A';
        $fechaEntregaVal = $firstPo->fecha_entrega ? ($firstPo->fecha_entrega instanceof \DateTimeInterface ? $firstPo->fecha_entrega->format('d/m/Y') : $firstPo->fecha_entrega) : 'Llenado manual';
        $observacionesVal = $firstPo->observaciones ?: 'Sin observaciones adicionales.';
        $proveedorVal = $preOrdenes->pluck('proveedor')->unique()->implode(', ');

        $otCleaned = preg_replace('/^OT\s*/i', '', $ot);
        $parts = explode('-', $otCleaned, 2);
        $otOnly = trim($parts[0]);
        $molduraVal = (count($parts) > 1) ? trim($parts[1]) : ($firstPo->moldura ?: 'N/A');
        
        $isCastingPo = $preOrdenes->contains(function ($po) {
            return $po->pdf_filename && (strpos(strtolower($po->pdf_filename), 'casting') !== false);
        });
        
        $tipoOrdenStr = $isCastingPo ? 'Pre-Orden de Fabricación de Casting' : 'Pre-Orden de Fabricación de Modelos';
        $asunto = "{$tipoOrdenStr} (Folio: {$folioVal}) - OT {$otCleaned}";

        $cuerpo = "
        <div style='font-family: Arial, sans-serif; line-height: 1.6; color: #334155; max-width: 600px; margin: 0 auto; border: 1px solid #e2e8f0; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 6px rgba(0,0,0,0.05);'>
            <div style='background-color: #033966; color: white; padding: 20px; text-align: center;'>
                <h2 style='margin: 0; font-size: 1.5em;'>{$tipoOrdenStr}</h2>
                <p style='margin: 5px 0 0 0; font-size: 0.9em; opacity: 0.9;'>Orden de Trabajo: {$otCleaned}</p>
            </div>
            <div style='padding: 25px; background-color: #ffffff;'>
                <p>Estimado Proveedor (<strong>{$proveedorVal}</strong>),</p>
                <p>Se ha generado una solicitud de " . ($isCastingPo ? 'fabricación de casting' : 'fabricación de modelos') . " para la Orden de Trabajo <strong>{$otCleaned}</strong>. A continuación se presentan los detalles clave de la pre-orden:</p>
                
                <table style='width: 100%; border-collapse: collapse; margin: 20px 0; font-size: 0.95em;'>
                    <tr>
                        <td style='padding: 8px; border-bottom: 1px solid #f1f5f9; font-weight: bold; color: #64748b; width: 40%;'>Folio de Pre-Orden:</td>
                        <td style='padding: 8px; border-bottom: 1px solid #f1f5f9; color: #0f172a; font-weight: bold;'>{$folioVal}</td>
                    </tr>
                    <tr>
                        <td style='padding: 8px; border-bottom: 1px solid #f1f5f9; font-weight: bold; color: #64748b;'>Orden de Trabajo:</td>
                        <td style='padding: 8px; border-bottom: 1px solid #f1f5f9; color: #0f172a;'>{$otOnly}</td>
                    </tr>
                    <tr>
                        <td style='padding: 8px; border-bottom: 1px solid #f1f5f9; font-weight: bold; color: #64748b;'>Moldura:</td>
                        <td style='padding: 8px; border-bottom: 1px solid #f1f5f9; color: #0f172a;'>{$molduraVal}</td>
                    </tr>
                    <tr>
                        <td style='padding: 8px; border-bottom: 1px solid #f1f5f9; font-weight: bold; color: #64748b;'>Fecha de Entrega Solicitada:</td>
                        <td style='padding: 8px; border-bottom: 1px solid #f1f5f9; color: #033966; font-weight: bold;'>{$fechaEntregaVal}</td>
                    </tr>
                    <tr>
                        <td style='padding: 8px; border-bottom: 1px solid #f1f5f9; font-weight: bold; color: #64748b;'>Observaciones:</td>
                        <td style='padding: 8px; border-bottom: 1px solid #f1f5f9; color: #0f172a; font-style: italic;'>{$observacionesVal}</td>
                    </tr>
                </table>

                <p style='margin-top: 25px;'>Adjunto a este correo electrónico encontrará la Pre-Orden de Fabricación en PDF con el desglose completo de los modelos solicitados.</p>
                <p>Por favor, confirme la recepción de este correo y de indicarnos la fecha estimada de entrega.</p>
            </div>
            <div style='background-color: #f8fafc; padding: 15px; text-align: center; border-top: 1px solid #e2e8f0; font-size: 0.85em; color: #64748b;'>
                Este es un correo automático de control de pre-órdenes.<br>
                <strong>GRUPO INDUSTRIAL SAAVEDRA</strong>
            </div>
        </div>
        ";

        // Recopilar adjuntos
        $attachments = [];
        $attachedPaths = [];

        // 0. Siempre adjuntar las Pre-Órdenes de Fabricación asociadas
        foreach ($preOrdenes as $preOrden) {
            $isCasting = $preOrden->pdf_filename && (strpos(strtolower($preOrden->pdf_filename), 'casting') !== false);
            
            // Buscar pre-órdenes en rutas nuevas y legadas
            $candidates = [];
            if ($isCasting) {
                $candidates[] = self::ALMACEN_DIR . '/' . $folderName . '/Documentos_Aprobados/Preorden_Casting/' . $preOrden->pdf_filename;
                $candidates[] = $ayudasDirPath . '/preordenes/documentos_aprobados/' . $preOrden->pdf_filename;
            } else {
                $candidates[] = self::ALMACEN_DIR . '/' . $folderName . '/Documentos_Aprobados/Preorden_Modelo/' . $preOrden->pdf_filename;
                $candidates[] = $ayudasDirPath . '/preordenes/' . $preOrden->pdf_filename;
            }
            
            $foundPreOrdenPath = null;
            foreach ($candidates as $cand) {
                if (Storage::disk('local')->exists($cand)) {
                    $foundPreOrdenPath = $cand;
                    break;
                }
            }
            
            if (!$foundPreOrdenPath) {
                continue;
            }
            
            if (in_array($foundPreOrdenPath, $attachedPaths)) {
                continue;
            }
            $attachedPaths[] = $foundPreOrdenPath;

            $attachments[] = [
                'path' => storage_path('app/' . $foundPreOrdenPath),
                'name' => basename($preOrden->pdf_filename),
                'mime' => 'application/pdf'
            ];
        }

        // 1. Archivos del servidor seleccionados
        $archivosSeleccionados = $request->input('archivos_seleccionados', []);
        if (is_array($archivosSeleccionados)) {
            foreach ($archivosSeleccionados as $archivo) {
                // Si es el mismo PDF de la pre-orden, omitirlo para no duplicarlo
                $isPoPdf = $preOrdenes->contains(function ($po) use ($archivo) {
                    return basename($archivo) === $po->pdf_filename;
                });
                if ($isPoPdf) {
                    continue;
                }

                // Sanitizar para evitar path traversal
                $archivoSanitized = $this->sanitizeFileNameWithFolder($archivo);
                
                // Buscar el archivo en cualquiera de las carpetas de OTs relacionadas (base y reprocesos)
                $baseOt = preg_replace('/_R\d+$/i', '', $ot);
                $allOtNames = FundicionHistory::where('ot', '=', $baseOt, 'or')
                    ->where('ot', 'LIKE', $baseOt . '_R%', 'or')
                    ->pluck('ot')
                    ->toArray();
                if (!in_array($ot, $allOtNames)) {
                    $allOtNames[] = $ot;
                }

                foreach ($allOtNames as $relatedOt) {
                    $relFolder = $this->sanitizePath($this->normalizeOTName($relatedOt));
                    
                    // Buscar en todas las combinaciones posibles de Almacén y Calidad
                    $posPaths = [];
                    if (str_starts_with($archivoSanitized, 'Documentos_Aprobados/')) {
                        $subPath = str_replace('Documentos_Aprobados/', '', $archivoSanitized);
                        $posPaths[] = self::ALMACEN_DIR . '/' . $relFolder . '/Documentos_Aprobados/' . $subPath;
                        $posPaths[] = self::CALIDAD_DIR . '/' . $relFolder . '/Documentos_Aprobados/' . $subPath;
                    }
                    if (str_starts_with($archivoSanitized, 'Documentos_Rechazados/')) {
                        $subPath = str_replace('Documentos_Rechazados/', '', $archivoSanitized);
                        $posPaths[] = self::ALMACEN_DIR . '/' . $relFolder . '/Documentos_Rechazados/' . $subPath;
                        $posPaths[] = self::CALIDAD_DIR . '/' . $relFolder . '/Documentos_Rechazados/' . $subPath;
                    }
                    if (str_starts_with($archivoSanitized, 'preordenes/documentos_aprobados/')) {
                        $subPath = str_replace('preordenes/documentos_aprobados/', '', $archivoSanitized);
                        $posPaths[] = self::ALMACEN_DIR . '/' . $relFolder . '/preordenes/documentos_aprobados/' . $subPath;
                        $posPaths[] = self::ALMACEN_DIR . '/' . $relFolder . '/ayudas_visuales/preordenes/documentos_aprobados/' . $subPath;
                        $posPaths[] = self::CALIDAD_DIR . '/' . $relFolder . '/ayudas_visuales/preordenes/documentos_aprobados/' . $subPath;
                    }
                    if (str_starts_with($archivoSanitized, 'preordenes/documentos_rechazados/')) {
                        $subPath = str_replace('preordenes/documentos_rechazados/', '', $archivoSanitized);
                        $posPaths[] = self::ALMACEN_DIR . '/' . $relFolder . '/preordenes/documentos_rechazados/' . $subPath;
                        $posPaths[] = self::ALMACEN_DIR . '/' . $relFolder . '/ayudas_visuales/preordenes/documentos_rechazados/' . $subPath;
                        $posPaths[] = self::CALIDAD_DIR . '/' . $relFolder . '/ayudas_visuales/preordenes/documentos_rechazados/' . $subPath;
                    }
                    if (str_starts_with($archivoSanitized, 'preordenes/')) {
                        $subPath = str_replace('preordenes/', '', $archivoSanitized);
                        $posPaths[] = self::ALMACEN_DIR . '/' . $relFolder . '/ayudas_visuales/preordenes/' . $subPath;
                        $posPaths[] = self::ALMACEN_DIR . '/' . $relFolder . '/preordenes/' . $subPath;
                        $posPaths[] = self::CALIDAD_DIR . '/' . $relFolder . '/ayudas_visuales/preordenes/' . $subPath;
                    }

                    // Fallbacks generales
                    $posPaths[] = self::ALMACEN_DIR . '/' . $relFolder . '/ayudas_visuales/' . $archivoSanitized;
                    $posPaths[] = self::ALMACEN_DIR . '/' . $relFolder . '/' . $archivoSanitized;
                    $posPaths[] = self::CALIDAD_DIR . '/' . $relFolder . '/ayudas_visuales/' . $archivoSanitized;
                    $posPaths[] = self::CALIDAD_DIR . '/' . $relFolder . '/' . $archivoSanitized;

                    $found = false;
                    foreach ($posPaths as $path) {
                        if (Storage::disk('local')->exists($path)) {
                            $attachments[] = [
                                'path' => storage_path('app/' . $path),
                                'name' => basename($archivoSanitized),
                                'mime' => 'application/pdf'
                            ];
                            $found = true;
                            break;
                        }
                    }
                    if ($found) {
                        break;
                    }
                }
            }
        }

        // 2. Archivos adicionales cargados desde la computadora
        if ($request->hasFile('archivos_adicionales')) {
            $uploadedFiles = $request->file('archivos_adicionales');
            $filesArray = is_array($uploadedFiles) ? $uploadedFiles : [$uploadedFiles];
            
            $subfolder = $isCastingPo ? 'Preorden_Casting' : 'Preorden_Modelo';
            $destDir = self::ALMACEN_DIR . '/' . $folderName . '/Documentos_Aprobados/' . $subfolder;
            
            if (!Storage::disk('local')->exists($destDir)) {
                Storage::disk('local')->makeDirectory($destDir);
            }

            foreach ($filesArray as $file) {
                $name = 'Escaneado_Fundicion-' . $file->getClientOriginalName();
                $savedPath = $file->storeAs($destDir, $name, 'local');
                $attachments[] = [
                    'path' => storage_path('app/' . $savedPath),
                    'name' => $name,
                    'mime' => $file->getClientMimeType()
                ];
            }
        }

        // Enviar Email
        try {
            // Destinatarios de prueba (Temporalmente modificado a petición del usuario)
            $destinatarios = array_map('trim', explode(',', $destinatario));
            if (empty($destinatarios) || (count($destinatarios) === 1 && $destinatarios[0] === '')) {
                $destinatarios = ['jaxer020406@gmail.com'];
            }

            // Si es un re-proceso de rechazados, los destinatarios ya vienen del formulario
            // (se eliminó el lookup automático de usuarios calidad por columna 'email' inexistente)

            // ── AUTO-ADJUNTOS: Si es re-proceso (_R1, _R2...), adjuntar docs de toda la historia ──
            if (preg_match('/^(.+?)(_[rR](\d+))$/', $ot, $match)) {
                $otBase = $match[1];
                $currentIteration = (int)$match[3];
                
                $extPermitidas = ['pdf', 'jpg', 'jpeg', 'png'];

                for ($i = 0; $i <= $currentIteration; $i++) {
                    $otName = $i === 0 ? $otBase : $otBase . '_R' . $i;
                    $folder = $this->sanitizePath($this->normalizeOTName($otName));
                    $dir = self::ALMACEN_DIR . '/' . $folder;
                    $ayudas = $dir . '/ayudas_visuales';

                    $carpetas = [];
                    if ($i === 0) {
                        // En la OT base escaneamos dibujos y ayudas visuales
                        $carpetas[$dir] = 'Dibujo';
                        $carpetas[$ayudas] = 'AyudaVisual';
                    }
                    // Para la OT base y todas las iteraciones escaneamos aprobados y rechazados
                    if ($i === $currentIteration) {
                        $carpetas[$dir . '/preordenes/documentos_aprobados'] = 'DocAprobado';
                        $carpetas[$dir . '/Documentos_Aprobados'] = 'DocAprobado';
                    }
                    $carpetas[$dir . '/preordenes/documentos_rechazados'] = 'DocRechazado';
                    $carpetas[$dir . '/Documentos_Rechazados'] = 'DocRechazado';

                    foreach ($carpetas as $carpeta => $etiqueta) {
                        if (!Storage::disk('local')->exists($carpeta)) continue;

                        $archivos = Storage::disk('local')->allFiles($carpeta);
                        foreach ($archivos as $archivoPath) {
                            $ext = strtolower(pathinfo($archivoPath, PATHINFO_EXTENSION));
                            if (!in_array($ext, $extPermitidas, true)) continue;

                            $absPath = storage_path('app/' . $archivoPath);
                            // Evitar duplicados
                            if (collect($attachments)->contains(fn($a) => $a['path'] === $absPath)) continue;

                            // Ignorar dibujos que estén en subcarpetas para la ruta raíz
                            if ($etiqueta === 'Dibujo' && str_contains($archivoPath, '/ayudas_visuales/')) continue;
                            if ($etiqueta === 'Dibujo' && str_contains($archivoPath, '/preordenes/')) continue;

                            $iterationLabel = $i === 0 ? 'OT_Base' : 'R' . $i;
                            $attachments[] = [
                                'path' => $absPath,
                                'name' => '[' . $etiqueta . ' ' . $iterationLabel . '] ' . basename($archivoPath),
                                'mime' => $ext === 'pdf' ? 'application/pdf' : 'image/' . $ext,
                            ];
                        }
                    }
                }
            }

            Mail::send([], [], function ($message) use ($destinatarios, $asunto, $cuerpo, $attachments) {
                $message->to($destinatarios)
                    ->subject($asunto)
                    ->html($cuerpo);

                foreach ($attachments as $att) {
                    $message->attach($att['path'], [
                        'as' => $att['name'],
                        'mime' => $att['mime']
                    ]);
                }
            });

            // Copiar los archivos de preordenes al directorio de Calidad
            $this->copyDirectoryRecursive(
                self::ALMACEN_DIR . '/' . $folderName . '/ayudas_visuales/preordenes',
                self::CALIDAD_DIR . '/' . $folderName . '/ayudas_visuales/preordenes'
            );
            $this->copyDirectoryRecursive(
                self::ALMACEN_DIR . '/' . $folderName . '/Documentos_Aprobados',
                self::CALIDAD_DIR . '/' . $folderName . '/Documentos_Aprobados'
            );

            // Actualizar flag de correo de pre-orden enviado en historial
            $updateData = ['pre_orden_email_sent' => true];
            // Si es una pre-orden de casting, marcamos el estatus como casting_aprobado
            // independientemente del perfil del usuario (Almacén o Calidad pueden enviarla)
            if ($isCastingPo) {
                $updateData['calidad_revision_status'] = 'casting_aprobado';
            }

            FundicionHistory::where('ot', '=', $ot, 'and')->update($updateData);

            return response()->json([
                'success' => true,
                'message' => 'El correo electrónico de la pre-orden con sus adjuntos ha sido enviado con éxito.'
            ]);
        } catch (\Exception $e) {
            Log::error("Error al enviar pre-orden con adjuntos: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al enviar el correo: ' . $e->getMessage()
            ], 500);
        }
    }

    // =========================================================================
    // LIBERACION DE MODELOS (Flujo de Calidad)
    // =========================================================================

    /**
     * Devuelve los datos de liberacion existentes para una OT.
     * Usado para pre-llenar el formulario cuando Calidad vuelve a abrirlo.
     */
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
        if (!$user || !in_array($user->perfil, ['1', '2', '4'], true)) {
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
            'medidas_fondo'           => $tipo === 'Fondo' ? $sanitizarMedidas($request->input('fondo')) : null,
            'medidas_obturador'       => $tipo === 'Obturador' ? $sanitizarMedidas($request->input('obturador')) : null,
            'observaciones_modelo'    => in_array($tipo, ['Molde', 'Bombillo']) ? $request->input('observaciones_modelo') : null,
            'observaciones_plantilla' => in_array($tipo, ['Molde', 'Bombillo']) ? $request->input('observaciones_plantilla') : null,
            'observaciones_fondo'     => $tipo === 'Fondo' ? $request->input('observaciones_fondo') : null,
            'observaciones_obturador' => $tipo === 'Obturador' ? $request->input('observaciones_obturador') : null,
            'motivo_rechazo'          => $accion === 'rechazar' ? $request->input('motivo_rechazo') : null,
            'user_id_calidad'         => $user->id,
            'user_nombre_calidad'     => $user->name,
            'fecha_revision'          => in_array($accion, ['aprobar', 'rechazar']) ? now() : null,
        ];

        // Requerimiento 2: Actualizar SOLO los campos del tipo activo.
        // Si existe un registro inicial (tipo_modelo = null), lo actualizamos.
        $liberacionInicial = LiberacionModeloFundicion::where('ot', '=', $ot, 'and')->whereNull('tipo_modelo')->first();
        if ($liberacionInicial) {
            $liberacionInicial->update(['tipo_modelo' => $tipo]);
            $liberacion = $liberacionInicial;
        } else {
            $liberacion = LiberacionModeloFundicion::firstOrCreate([
                'ot'          => $ot,
                'tipo_modelo' => $tipo,
            ], [
                'estado'      => 'pendiente',
            ]);
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
        } elseif ($tipo === 'Fondo') {
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
            // Nombre estetico: F-CCL-LDM_[Tipo]_[OT-sanitizada]_[Estado].pdf
            $otSanitizada = preg_replace('/[^\w\s\-]/', '', $ot);
            $otSanitizada = preg_replace('/[\s]+/', '_', trim($otSanitizada));
            $tipoLabel    = $tipo ? strtoupper($tipo) : 'GENERAL';
            $estadoLabel  = strtoupper($decision === 'aprobar' ? 'aprobado' : 'rechazado');
            $pdfFilename  = "F-CCL-LDM_{$tipoLabel}_{$otSanitizada}_{$estadoLabel}.pdf";
            $pdfPath = storage_path("app/public/liberaciones_pdf");
            if (!file_exists($pdfPath)) {
                mkdir($pdfPath, 0755, true);
            } else {
                // Eliminar PDFs anteriores (incluyendo posibles historiales con timestamp) para este Tipo y OT
                $pattern = "{$pdfPath}/F-CCL-LDM_{$tipoLabel}_{$otSanitizada}*.pdf";
                foreach (glob($pattern) as $oldFile) {
                    @unlink($oldFile);
                }
            }
            ini_set('memory_limit', '512M');
            $hasRechazo = ($nuevoEstado === 'rechazado') || 
                           ($nuevoEstado === 'pendiente' && $decision === 'rechazar');
            $viewName = $hasRechazo ? 'almacen.pdf_rechazo' : 'almacen.pdf_liberacion';
            $pdf = Pdf::loadView($viewName, ['liberacion' => $liberacion])
                      ->setPaper('letter', 'landscape');
            $pdf->save("{$pdfPath}/{$pdfFilename}");
            $pdfUrl = asset('storage/liberaciones_pdf/' . $pdfFilename);
            $liberacion->update(['pdf_filename' => $pdfFilename]);

            // Copiar a la carpeta de la OT en ayudas_visuales/preordenes de Calidad para que se liste en Otros documentos
            $folderName = $this->sanitizePath($this->normalizeOTName($ot));
            $basePath = self::CALIDAD_DIR . '/' . $folderName . '/ayudas_visuales/preordenes';
            $subFolder = $hasRechazo ? 'documentos_rechazados' : 'documentos_aprobados';
            $classSubFolder = strtolower(preg_replace('/[^a-zA-Z0-9_\-]/', '_', trim($tipo)));
            if (empty($classSubFolder)) {
                $classSubFolder = 'general';
            }
            $otPath = $basePath . '/' . $subFolder . '/' . $classSubFolder;
            
            // Eliminar versiones previas de F-CCL-LDM para esta clase/modelo en ambas carpetas, la raíz y subcarpetas de clases
            foreach (['', 'documentos_aprobados', 'documentos_rechazados'] as $folder) {
                $checkPath = $folder === '' ? $basePath : $basePath . '/' . $folder;
                if (Storage::disk('local')->exists($checkPath)) {
                    // Limpiar de la carpeta principal
                    $files = Storage::disk('local')->files($checkPath);
                    foreach ($files as $f) {
                        if (str_contains(basename($f), "F-CCL-LDM_{$tipoLabel}_")) {
                            Storage::disk('local')->delete($f);
                        }
                    }
                    // Limpiar de la subcarpeta de la clase específica
                    $classCheckPath = $checkPath . '/' . $classSubFolder;
                    if (Storage::disk('local')->exists($classCheckPath)) {
                        $classFiles = Storage::disk('local')->files($classCheckPath);
                        foreach ($classFiles as $f) {
                            if (str_contains(basename($f), "F-CCL-LDM_{$tipoLabel}_")) {
                                Storage::disk('local')->delete($f);
                            }
                        }
                    }
                }
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
        FundicionHistory::where('ot', '=', $ot, 'and')
            ->update(['calidad_revision_status' => $nuevoEstado]);

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
        $firstClassSubFolder = strtolower(preg_replace('/[^a-zA-Z0-9_\-]/', '_', trim($clases[0] ?? 'general')));
        $firstClassSuffix = strtoupper($firstClassSubFolder);
        
        $otSanitizada = preg_replace('/[\s]+/', '_', trim(preg_replace('/[^\w\s\-]/', '', $ot)));
        $pdfDir       = storage_path('app/public/liberaciones_pdf');
        if (!file_exists($pdfDir)) {
            mkdir($pdfDir, 0755, true);
        }
        
        // Borrar PDFs viejos
        foreach (glob("{$pdfDir}/F-CCL-SCAR_{$firstClassSuffix}_{$otSanitizada}.pdf") as $old) {
            @unlink($old);
        }
        
        $folderName = $this->sanitizePath($this->normalizeOTName($ot));
        $otPath = self::CALIDAD_DIR . '/' . $folderName . '/ayudas_visuales/preordenes';
        foreach ($clases as $clase) {
            $classSubFolder = strtolower(preg_replace('/[^a-zA-Z0-9_\-]/', '_', $clase));
            if (empty($classSubFolder)) $classSubFolder = 'general';
            $classSuffix = strtoupper($classSubFolder);
            
            $oldScarPattern = $otPath . '/documentos_rechazados/' . $classSubFolder . '/F-CCL-SCAR_' . $classSuffix . '_' . $otSanitizada . '.pdf';
            if (Storage::disk('local')->exists($oldScarPattern)) {
                Storage::disk('local')->delete($oldScarPattern);
            }
        }
        
        ini_set('memory_limit', '512M');
        $pdf = Pdf::loadView('almacen.pdf_scar', ['scar' => $scar])
                  ->setPaper('letter', 'portrait');
                  
        $pdfFilename = "F-CCL-SCAR_" . $firstClassSuffix . "_{$otSanitizada}.pdf";
        $pdf->save("{$pdfDir}/{$pdfFilename}");
        
        foreach ($clases as $clase) {
            $classSubFolder = strtolower(preg_replace('/[^a-zA-Z0-9_\-]/', '_', $clase));
            if (empty($classSubFolder)) $classSubFolder = 'general';
            $classSuffix = strtoupper($classSubFolder);
            
            $destClassPath = $otPath . '/documentos_rechazados/' . $classSubFolder;
            if (!Storage::disk('local')->exists($destClassPath)) {
                Storage::disk('local')->makeDirectory($destClassPath);
            }
            
            $classPdfFilename = "F-CCL-SCAR_" . $classSuffix . "_{$otSanitizada}.pdf";
            Storage::disk('local')->put($destClassPath . '/' . $classPdfFilename, file_get_contents("{$pdfDir}/{$pdfFilename}"));
        }
    }

    public function enviarAlertaLiberacion(Request $request)
    {
        /** @var \App\Models\User|null $user */
        $user = Auth::user();
        if (!$user || !in_array($user->perfil, ['1', '2', '4', '5'], true)) {
            return response()->json(['success' => false, 'message' => 'Acceso restringido.'], 403);
        }

        $ot          = $request->input('ot', '');
        $decision    = $request->input('decision', ''); // 'aprobar' | 'rechazar'
        $tipoModelo  = $request->input('tipo_modelo', '');
        $fecha       = $request->input('fecha', '');
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
            return response()->json(['success' => false, 'message' => 'No se encontró un borrador guardado para esta liberación.'], 404);
        }

        $folderName = $this->sanitizePath($this->normalizeOTName($ot));
        $otPath = self::CALIDAD_DIR . '/' . $folderName . '/ayudas_visuales/preordenes';
        if (!Storage::disk('local')->exists($otPath)) {
            Storage::disk('local')->makeDirectory($otPath);
        }

        $attachments = [];
        $attachmentsAprobados = [];
        $attachmentsRechazados = [];
        $otSanitizada = str_replace(['/', '\\', ' ', ':'], '_', $ot);

        // Archivos Adicionales (Subidos mediante el nuevo dropzone unificado)
        if ($request->hasFile('archivos_adicionales')) {
            $evidenciasPath = self::CALIDAD_DIR . '/' . $folderName . '/ayudas_visuales/preordenes/evidencias';
            if (!Storage::disk('local')->exists($evidenciasPath)) {
                Storage::disk('local')->makeDirectory($evidenciasPath);
            }
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
                    Storage::disk('local')->put(
                        $evidenciasPath . '/' . $addName,
                        file_get_contents($addPath->getRealPath())
                    );
                }
            }
        }

        // El formato escaneado ya no se recibe aquí como input aislado
        $nameFormato = $liberacion->pdf_filename ?? null;

        // Actualizar el estado de la liberación en base de datos y destinatario
        // NOTA: Si la decisión es 'mixto', no actualizamos el estado general a 'mixto'
        // a menos que queramos mantener un string diferente. Sin embargo, actualizaremos los registros individuales.
        $esCalidadUser = ($user->perfil == '4');
        if ($esCalidadUser) {
            $decisionNorm = ($decision === 'aprobar') ? 'aprobado' : (($decision === 'rechazar') ? 'rechazado' : 'mixto');
            $nuevoEstado = 'calidad_' . $decisionNorm;
        } else {
            $nuevoEstado = ($decision === 'aprobar') ? 'aprobado' : (($decision === 'rechazar') ? 'rechazado' : 'mixto');
        }
        
        $liberacionesOT = LiberacionModeloFundicion::where('ot', '=', $ot, 'and')->get();
        $clasesAprobadas = [];
        $clasesRechazadas = [];
        $libAprobada = null;
        $libRechazada = null;

        foreach ($liberacionesOT as $libRow) {
            $libNuevoEst = $libRow->decision === 'aprobar' ? 'aprobado' : 'rechazado';
            
            $libRow->update([
                'estado'         => $libNuevoEst,
                'fecha_revision' => $fecha,
                'pdf_filename'   => $libRow->pdf_filename,
                'destinatario'   => $request->input('destinatario')
            ]);

            if ($libRow->decision === 'aprobar') {
                $clasesAprobadas[] = strtolower($libRow->tipo_modelo);
                if (!$libAprobada) $libAprobada = clone $libRow;
            } elseif ($libRow->decision === 'rechazar') {
                $clasesRechazadas[] = strtolower($libRow->tipo_modelo);
                if (!$libRechazada) $libRechazada = clone $libRow;
            }
        }

        $scarModelos = ScarModelo::where('ot', '=', $ot, 'and')->get();
        // Si hay rechazos, marcar también en el SCAR que ha sido alertado y actualizar fecha de compromiso
        if (($decision === 'rechazar' || $decision === 'mixto') && $scarModelos->isNotEmpty()) {
            foreach ($scarModelos as $scarMod) {
                $scarMod->update([
                    'estatus'          => 'alertado',
                    'fecha_compromiso' => $fecha
                ]);
                $this->updateScarPdf($scarMod);
            }
        }

        // Actualizar calidad_revision_status en fundicion_history
        FundicionHistory::where('ot', '=', $ot, 'and')->update([
            'calidad_revision_status' => $nuevoEstado
        ]);

        // Si es RECHAZO definitivo (desde Almacén): reiniciar el ciclo de Almacén para los elementos de esta OT
        if (!$esCalidadUser && $decision === 'rechazar') {
            FundicionHistory::where('ot', '=', $ot, 'and')->update([
                'pre_orden_sent'       => false,
                'pre_orden_email_sent' => false,
                'tiene_modelo'         => false
            ]);
        }

        // Dibujos y Ayudas del servidor (filtrados por los archivos seleccionados en el modal)
        // Buscamos en todas las carpetas de OTs relacionadas (base y reprocesos)
        $baseOtForSearch = preg_replace('/_R\d+$/i', '', $ot);
        $allOtNamesForSearch = FundicionHistory::where('ot', '=', $baseOtForSearch, 'or')
            ->where('ot', 'LIKE', $baseOtForSearch . '_R%', 'or')
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

                if ($isSelAprobado) {
                    $attachmentsAprobados[] = $fileItem;
                }
                if ($isSelRechazado) {
                    $attachmentsRechazados[] = $fileItem;
                }
            }
        }

        // Archivos Aprobados extras (por modelo: archivos_aprobados_extra[Tipo])
        if ($request->hasFile('archivos_aprobados_extra')) {
            $uploadedAprobados = $request->file('archivos_aprobados_extra');
            // puede llegar como array asociativo [tipo => file] o array plano
            $aprobadosDestDir = self::CALIDAD_DIR . '/' . $folderName . '/ayudas_visuales/preordenes/documentos_aprobados';
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
                    $classSubFolder = strtolower(preg_replace('/[^a-zA-Z0-9_\-]/', '_', trim($tipoName)));
                    if (empty($classSubFolder)) $classSubFolder = 'general';
                    
                    $destPath = $aprobadosDestDir . '/' . $classSubFolder;
                    if (!Storage::disk('local')->exists($destPath)) {
                        Storage::disk('local')->makeDirectory($destPath);
                    }
                    
                    $prefix    = $item['tipo'] ? strtoupper($item['tipo']) . '_Aprobado_' : 'Aprobado_';
                    $extraName = $prefix . $extraFile->getClientOriginalName();
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
            $rechazadosDestDir = self::CALIDAD_DIR . '/' . $folderName . '/ayudas_visuales/preordenes/documentos_rechazados';
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
                    $classSubFolder = strtolower(preg_replace('/[^a-zA-Z0-9_\-]/', '_', trim($tipoName)));
                    if (empty($classSubFolder)) $classSubFolder = 'general';
                    
                    $destPath = $rechazadosDestDir . '/' . $classSubFolder;
                    if (!Storage::disk('local')->exists($destPath)) {
                        Storage::disk('local')->makeDirectory($destPath);
                    }
                    
                    $prefix    = $item['tipo'] ? strtoupper($item['tipo']) . '_Rechazado_' : 'Rechazado_';
                    $extraName = $prefix . $extraFile->getClientOriginalName();
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
            $rechazadosDestDir = self::CALIDAD_DIR . '/' . $folderName . '/ayudas_visuales/preordenes/documentos_rechazados';
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
                    $classSubFolder = strtolower(preg_replace('/[^a-zA-Z0-9_\-]/', '_', trim($tipoName)));
                    if (empty($classSubFolder)) $classSubFolder = 'general';
                    
                    $destPath = $rechazadosDestDir . '/' . $classSubFolder;
                    if (!Storage::disk('local')->exists($destPath)) {
                        Storage::disk('local')->makeDirectory($destPath);
                    }
                    
                    $prefix    = $item['tipo'] ? strtoupper($item['tipo']) . '_SCAR_' : 'SCAR_';
                    $extraName = $prefix . $extraFile->getClientOriginalName();
                    $savedPath = $extraFile->storeAs($destPath, $extraName, 'local');
                    $attachmentsRechazados[] = [
                        'path' => storage_path('app/' . $savedPath),
                        'name' => $extraName,
                        'mime' => $extraFile->getClientMimeType() ?: 'application/octet-stream'
                    ];
                }
            }
        }

        // Enviar correos
        $destinatarios = array_filter(
            array_map('trim', explode(',', $request->input('destinatario', 'jaxer020406@gmail.com')))
        );
        if (empty($destinatarios)) {
            $destinatarios = ['jaxer020406@gmail.com'];
        }

        try {
            if ($decision === 'mixto') {
                if ($libAprobada) {
                    Mail::to($destinatarios)->send(new LiberacionModeloMailable($ot, 'aprobado', $libAprobada, $attachmentsAprobados));
                }
                if ($libRechazada) {
                    Mail::to($destinatarios)->send(new LiberacionModeloMailable($ot, 'rechazado', $libRechazada, $attachmentsRechazados));
                }
            } elseif ($decision === 'aprobar') {
                Mail::to($destinatarios)->send(new LiberacionModeloMailable($ot, 'aprobado', $libAprobada ?: $liberacion, $attachmentsAprobados));
            } else {
                Mail::to($destinatarios)->send(new LiberacionModeloMailable($ot, 'rechazado', $libRechazada ?: $liberacion, $attachmentsRechazados));
            }

            // Copiar los archivos de Calidad a Almacén
            $this->copyDirectoryRecursive(
                self::CALIDAD_DIR . '/' . $folderName . '/ayudas_visuales/preordenes',
                self::ALMACEN_DIR . '/' . $folderName . '/ayudas_visuales/preordenes'
            );

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

    /**
     * Confirma la recepción de la información de rechazo por parte de Almacén.
     * Restablece el ciclo de la OT para volver a empezar el proceso de pre-orden / liberación.
     */
    public function confirmarRecepcionRechazo(Request $request)
    {
        $ot = $request->input('ot');
        $fecha = $request->input('fecha');

        if (empty($ot) || empty($fecha)) {
            return response()->json(['success' => false, 'message' => 'Parámetros obligatorios faltantes.'], 422);
        }

        // Reiniciar el ciclo
        FundicionHistory::where('ot', '=', $ot, 'and')->update([
            'pre_orden_sent'          => false,
            'pre_orden_email_sent'    => false,
            'tiene_modelo'            => false,
            'calidad_revision_status' => null
        ]);

        // Cambiar el estado de las liberaciones rechazadas a historico_rechazado
        LiberacionModeloFundicion::where('ot', '=', $ot, 'and')
            ->where('estado', '=', 'rechazado', 'and')
            ->update(['estado' => 'historico_rechazado']);

        return response()->json([
            'success' => true,
            'message' => 'Recepción confirmada y ciclo reiniciado con éxito.'
        ]);
    }

    /**
     * Genera el PDF del formato SCAR (Solicitud de Accion Correctiva de Rechazo)
     * a partir de los campos del formulario modal + el motivo_rechazo ya guardado en la BD.
     *
     * Acciones:
     *   'guardar' → genera y descarga el PDF del SCAR
     *   'enviar'  → genera el PDF del SCAR y envía correo de alerta con el PDF adjunto
     *
     * Perfil restringido: solo Calidad (perfil 4) o Administrador (perfil 1).
     */
    public function generateScar(Request $request)
    {
        /** @var \App\Models\User|null $user */
        $user = Auth::user();
        if (!$user || !in_array($user->perfil, ['1', '2', '4'], true)) {
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
            $firstClassSubFolder = strtolower(preg_replace('/[^a-zA-Z0-9_\-]/', '_', trim($clases[0] ?? 'general')));
            $firstClassSuffix = strtoupper($firstClassSubFolder);
            
            // Reemplazar SCAR anterior de la misma OT en el disco
            $otSanitizada = preg_replace('/[\s]+/', '_', trim(preg_replace('/[^\w\s\-]/', '', $ot)));
            $pdfDir       = storage_path('app/public/liberaciones_pdf');
            if (!file_exists($pdfDir)) {
                mkdir($pdfDir, 0755, true);
            }
            foreach (glob("{$pdfDir}/F-CCL-SCAR_{$firstClassSuffix}_{$otSanitizada}.pdf") as $old) {
                @unlink($old);
            }

            // También borrar en la carpeta ayudas_visuales
            $folderName = $this->sanitizePath($this->normalizeOTName($ot));
            $otPath = self::CALIDAD_DIR . '/' . $folderName . '/ayudas_visuales/preordenes';
            foreach ($clases as $clase) {
                $classSubFolder = strtolower(preg_replace('/[^a-zA-Z0-9_\-]/', '_', $clase));
                if (empty($classSubFolder)) $classSubFolder = 'general';
                $classSuffix = strtoupper($classSubFolder);
                
                $oldScarPattern = $otPath . '/documentos_rechazados/' . $classSubFolder . '/F-CCL-SCAR_' . $classSuffix . '_' . $otSanitizada . '.pdf';
                if (Storage::disk('local')->exists($oldScarPattern)) {
                    Storage::disk('local')->delete($oldScarPattern);
                }
            }

            ini_set('memory_limit', '512M');
            $pdf = Pdf::loadView('almacen.pdf_scar', ['scar' => $scarData])
                      ->setPaper('letter', 'portrait');

            // Guardamos el primer PDF en el directorio temporal
            $firstClassSubFolder = strtolower(preg_replace('/[^a-zA-Z0-9_\-]/', '_', trim($clases[0] ?? 'general')));
            $firstClassSuffix = strtoupper($firstClassSubFolder);
            $pdfFilename = "F-CCL-SCAR_" . $firstClassSuffix . "_{$otSanitizada}.pdf";
            $pdf->save("{$pdfDir}/{$pdfFilename}");
            $pdfUrl = asset('storage/liberaciones_pdf/' . $pdfFilename);

            // Ahora copiamos a cada subcarpeta de clase en ayudas_visuales
            foreach ($clases as $clase) {
                $classSubFolder = strtolower(preg_replace('/[^a-zA-Z0-9_\-]/', '_', $clase));
                if (empty($classSubFolder)) $classSubFolder = 'general';
                $classSuffix = strtoupper($classSubFolder);
                
                $destClassPath = $otPath . '/documentos_rechazados/' . $classSubFolder;
                if (!Storage::disk('local')->exists($destClassPath)) {
                    Storage::disk('local')->makeDirectory($destClassPath);
                }
                
                $classPdfFilename = "F-CCL-SCAR_" . $classSuffix . "_{$otSanitizada}.pdf";
                Storage::disk('local')->put($destClassPath . '/' . $classPdfFilename, file_get_contents("{$pdfDir}/{$pdfFilename}"));
            }

            // Guardar fotografías si se adjuntaron
            if ($request->hasFile('fotos')) {
                foreach ($request->file('fotos') as $idx => $foto) {
                    foreach ($clases as $clase) {
                        $classSubFolder = strtolower(preg_replace('/[^a-zA-Z0-9_\-]/', '_', $clase));
                        if (empty($classSubFolder)) $classSubFolder = 'general';
                        $classSuffix = strtoupper($classSubFolder);
                        
                        $fotosPath = $otPath . '/documentos_rechazados/' . $classSubFolder;
                        if (!Storage::disk('local')->exists($fotosPath)) {
                            Storage::disk('local')->makeDirectory($fotosPath);
                        }
                        
                        $fname = "SCAR_FOTO_" . $classSuffix . "_" . date('Ymd_His') . "_" . $idx . "." . $foto->getClientOriginalExtension();
                        $foto->storeAs($fotosPath, $fname, 'local');
                    }
                }
            }

            // Guardar otros archivos si se adjuntaron
            if ($request->hasFile('otros_archivos')) {
                foreach ($request->file('otros_archivos') as $idx => $archivo) {
                    foreach ($clases as $clase) {
                        $classSubFolder = strtolower(preg_replace('/[^a-zA-Z0-9_\-]/', '_', $clase));
                        if (empty($classSubFolder)) $classSubFolder = 'general';
                        $classSuffix = strtoupper($classSubFolder);
                        
                        $otrosPath = $otPath . '/documentos_rechazados/' . $classSubFolder;
                        if (!Storage::disk('local')->exists($otrosPath)) {
                            Storage::disk('local')->makeDirectory($otrosPath);
                        }
                        
                        $fname = "SCAR_DOC_" . $classSuffix . "_" . date('Ymd_His') . "_" . $idx . "." . $archivo->getClientOriginalExtension();
                        $archivo->storeAs($otrosPath, $fname, 'local');
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

    /**
     * Paso 2 del Flujo SCAR:
     * Recibe la fecha compromiso, el archivo del SCAR firmado físicamente (PDF),
     * y los archivos adicionales seleccionados por el usuario para enviar la alerta de correo al proveedor.
     */
    public function sendScarAlert(Request $request)
    {
        /** @var \App\Models\User|null $user */
        $user = Auth::user();
        if (!$user || !in_array($user->perfil, ['1', '2', '4'], true)) {
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

        // Copiar a la carpeta de la OT en ayudas_visuales/preordenes de Calidad para que se liste en Otros documentos
        $folderName = $this->sanitizePath($this->normalizeOTName($ot));
        $otPath = self::CALIDAD_DIR . '/' . $folderName . '/ayudas_visuales/preordenes';
        if (!Storage::disk('local')->exists($otPath)) {
            Storage::disk('local')->makeDirectory($otPath);
        }
        Storage::disk('local')->put($otPath . '/' . $pdfFirmadoName, file_get_contents("{$pdfDir}/{$pdfFirmadoName}"));

        // 2. Actualizar el modelo
        $scar->update([
            'fecha_compromiso' => $fechaCompromiso,
            'pdf_firmado_filename' => $pdfFirmadoName,
            'estatus' => 'alertado',
        ]);

        // 2.5 Regenerar el PDF digital del SCAR para que plasme la fecha de compromiso
        try {
            ini_set('memory_limit', '512M');
            $pdf = Pdf::loadView('almacen.pdf_scar', ['scar' => $scar])
                      ->setPaper('letter', 'portrait');
            $pdf->save("{$pdfDir}/{$scar->pdf_filename}");
            
            // Copiar a la carpeta de la OT en ayudas_visuales/preordenes para que se liste en Otros documentos
            Storage::disk('local')->put($otPath . '/' . $scar->pdf_filename, file_get_contents("{$pdfDir}/{$scar->pdf_filename}"));
        } catch (\Exception $pdfEx) {
            Log::error('Error al regenerar PDF digital de SCAR en alerta: ' . $pdfEx->getMessage());
        }

        // 3. Destinatarios
        $destinatarios = array_filter(
            array_map('trim', explode(',', $request->input('destinatario', 'jaxer020406@gmail.com')))
        );
        if (empty($destinatarios)) {
            $destinatarios = ['jaxer020406@gmail.com'];
        }

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

        // Fotografías subidas en el momento (si las hay) - se guardan en el directorio de Calidad
        $evidenciasPath = self::CALIDAD_DIR . '/' . $folderName . '/ayudas_visuales/preordenes/evidencias';
        if (!Storage::disk('local')->exists($evidenciasPath)) {
            Storage::disk('local')->makeDirectory($evidenciasPath);
        }

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
                    // Copiar a la carpeta de la OT para que aparezca en Otros Documentos
                    Storage::disk('local')->put(
                        $evidenciasPath . '/' . $photoName,
                        file_get_contents($photoPath->getRealPath())
                    );
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
                    // Copiar a la carpeta de la OT para que aparezca en Otros Documentos
                    Storage::disk('local')->put(
                        $evidenciasPath . '/' . $otherName,
                        file_get_contents($otherPath->getRealPath())
                    );
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
                    // Copiar a la carpeta de la OT para que aparezca en Otros Documentos
                    Storage::disk('local')->put(
                        $evidenciasPath . '/' . $addName,
                        file_get_contents($addPath->getRealPath())
                    );
                }
            }
        }

        // 5. Enviar el correo
        try {
            Mail::to($destinatarios)->send(new LiberacionModeloMailable($ot, 'rechazado', $liberacion, $attachments));
            
            // Copiar los archivos de Calidad a Almacén
            $this->copyDirectoryRecursive(
                self::CALIDAD_DIR . '/' . $folderName . '/ayudas_visuales/preordenes',
                self::ALMACEN_DIR . '/' . $folderName . '/ayudas_visuales/preordenes'
            );
            $this->eliminarCarpetasVacias($ot);
        } catch (\Exception $mailEx) {
            Log::error('Error al enviar correo SCAR Firmado: ' . $mailEx->getMessage());
            $this->eliminarCarpetasVacias($ot);
            return response()->json([
                'success' => false,
                'message' => 'Los datos se guardaron pero la alerta por correo no pudo enviarse: ' . $mailEx->getMessage(),
            ], 500);
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
        $preOrden = PreOrdenFundicion::where('ot', '=', $ot, 'and')->first();
        if (!$preOrden) {
            $baseOt = preg_replace('/_R\d+$/', '', $ot);
            $preOrden = PreOrdenFundicion::where('ot', '=', $baseOt, 'and')->first();
        }
        if ($preOrden && !empty($tipoModelo)) {
            $filas = $preOrden->filas;
            if (is_string($filas)) {
                $filas = json_decode($filas, true);
            }
            if (is_array($filas)) {
                $targetTipo = strtolower($tipoModelo);
                foreach ($filas as $fila) {
                    $claseFila = strtolower($fila['clase_nombre'] ?? $fila['clase'] ?? '');
                    if (!empty($claseFila) && (strpos($claseFila, $targetTipo) !== false || strpos($targetTipo, $claseFila) !== false)) {
                        $preordenCodigoModelo = $fila['codigo_modelo'] ?? null;
                        break;
                    }
                }
            }
        }
        
        return response()->json([
            'success' => true,
            'scar' => $scar,
            'preorden_codigo_modelo' => $preordenCodigoModelo
        ]);
    }

    /**
     * Copia recursivamente todos los archivos de un directorio a otro en el Storage 'local'.
     */
    private function copyDirectoryRecursive(string $src, string $dst): void
    {
        if (!Storage::disk('local')->exists($src)) {
            return;
        }

        $files = Storage::disk('local')->allFiles($src);
        foreach ($files as $file) {
            // Reemplazar la ruta origen por la de destino
            $relPath = ltrim(substr($file, strlen($src)), '/');
            $targetPath = $dst . '/' . $relPath;

            // Asegurar directorio destino
            $targetDir = dirname($targetPath);
            if (!Storage::disk('local')->exists($targetDir)) {
                Storage::disk('local')->makeDirectory($targetDir);
            }

            // Copiar el archivo (reemplazar si ya existe)
            Storage::disk('local')->put($targetPath, Storage::disk('local')->get($file));
        }
    }

    /**
     * Elimina automáticamente las carpetas de documentos aprobados/rechazados (y sus subcarpetas de clases)
     * si se quedan sin archivos (mínimo de archivos es 1).
     */
    private function eliminarCarpetasVacias(string $ot): void
    {
        $folderName = $this->sanitizePath($this->normalizeOTName($ot));
        
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
                
                // Si la carpeta padre (documentos_aprobados o documentos_rechazados) también se quedó vacía, se elimina
                $parentFiles = Storage::disk('local')->files($path);
                $parentDirs = Storage::disk('local')->directories($path);
                if (empty($parentFiles) && empty($parentDirs)) {
                    Storage::disk('local')->deleteDirectory($path);
                }
            }
        }
    }

    public function iniciarCasting(Request $request)
    {
        $this->verificarAcceso();

        $otRaw = $request->input('ot');
        $fecha = $request->input('fecha_recepcion');
        
        if (empty($otRaw) || empty($fecha)) {
            return response()->json(['success' => false, 'message' => 'Faltan datos obligatorios.']);
        }

        $folderName = $this->sanitizePath($this->normalizeOTName($otRaw));
        $otPath = self::ALMACEN_DIR . '/' . $folderName . '/Documentos_Aprobados/Liberacion_Modelo';
        
        if (!Storage::disk('local')->exists($otPath)) {
            Storage::disk('local')->makeDirectory($otPath);
        }

        $filesSaved = 0;
        foreach ($request->allFiles() as $key => $file) {
            if (str_starts_with($key, 'ldm_')) {
                $clase = strtoupper(str_replace('ldm_', '', $key));
                $originalName = $file->getClientOriginalName();
                $ext = pathinfo($originalName, PATHINFO_EXTENSION);
                
                $filename = "F-CCL-LDM_{$clase}_{$folderName}_APROBADO.{$ext}";
                $file->storeAs($otPath, $filename, 'local');
                $filesSaved++;
            }
        }

        if ($request->hasFile('archivos_adicionales')) {
            $ayudasPath = self::ALMACEN_DIR . '/' . $folderName . '/Documentos_Aprobados/Preorden_Casting';
            if (!Storage::disk('local')->exists($ayudasPath)) {
                Storage::disk('local')->makeDirectory($ayudasPath);
            }
            $otSanitizada = preg_replace('/[\s]+/', '_', trim(preg_replace('/[^\w\s\-]/', '', $otRaw)));
            foreach ($request->file('archivos_adicionales') as $idx => $addFile) {
                if ($addFile->isValid()) {
                    $originalName = $addFile->getClientOriginalName();
                    $ext = $addFile->getClientOriginalExtension();
                    $addName = "evidencia_adicional_" . date('Ymd_His') . "_{$idx}_{$otSanitizada}.{$ext}";
                    $addFile->storeAs($ayudasPath, $addName, 'local');
                    $filesSaved++;
                }
            }
        }

        // Marcar en la BD (Unlock Casting button)
        $history = FundicionHistory::where('ot', '=', $otRaw, 'and')->first();
        if ($history) {
            $history->casting_pdf_generated = true;
            $history->save();
        }

        return response()->json([
            'success' => true, 
            'message' => 'Formatos LDM subidos con éxito. Ya puedes generar la Pre-Orden de Casting.'
        ]);
    }

    public function procesarRechazos(Request $request)
    {
        $this->verificarAcceso();

        $otRaw = $request->input('ot');
        $fecha = $request->input('fecha_recepcion');
        $clasesJson = $request->input('clases_rechazadas');
        $clases = json_decode($clasesJson, true) ?? [];
        
        if (empty($otRaw) || empty($fecha) || empty($clases)) {
            return response()->json(['success' => false, 'message' => 'Faltan datos obligatorios.']);
        }

        // Marcar original como procesado
        $historyOriginal = FundicionHistory::where('ot', '=', $otRaw, 'and')->first();
        if ($historyOriginal) {
            $historyOriginal->rechazos_procesados = true;
            $historyOriginal->save();
        }

        // Guardar archivos en el OT original para trazabilidad
        $folderNameOriginal = $this->sanitizePath($this->normalizeOTName($otRaw));
        $docRechazadosPathOriginal = self::ALMACEN_DIR . '/' . $folderNameOriginal . '/Documentos_Rechazados';
        if (!Storage::disk('local')->exists($docRechazadosPathOriginal)) {
            Storage::disk('local')->makeDirectory($docRechazadosPathOriginal);
        }
        foreach ($request->allFiles() as $key => $file) {
            if (!$file->isValid()) {
                continue;
            }
            $originalName = $file->getClientOriginalName();
            $ext = pathinfo($originalName, PATHINFO_EXTENSION);
            if (str_starts_with($key, 'rechazo_')) {
                $clase = strtoupper(str_replace('rechazo_', '', $key));
                $filename = "Rechazo_{$clase}_{$folderNameOriginal}.{$ext}";
                $file->storeAs($docRechazadosPathOriginal . '/Liberacion_Modelo', $filename, 'local');
            } elseif (str_starts_with($key, 'scar_')) {
                $clase = strtoupper(str_replace('scar_', '', $key));
                $filename = "SCAR_{$clase}_{$folderNameOriginal}.{$ext}";
                $file->storeAs($docRechazadosPathOriginal . '/Scar', $filename, 'local');
            }
        }

        // Duplicar OT
        $baseOt = preg_replace('/_R\d+$/', '', $otRaw);
        $existingHistories = FundicionHistory::where('ot', 'LIKE', $baseOt . '_R%', 'and')->count();
        $newSuffix = '_R' . ($existingHistories + 1);
        $newOt = $baseOt . $newSuffix;

        $newHistory = new FundicionHistory();
        $newHistory->ot = $newOt;
        $newHistory->status = 'activa';
        $newHistory->tiene_modelo = 0;
        $newHistory->pre_orden_sent = 0;
        $newHistory->pre_orden_email_sent = 0;
        $newHistory->calidad_revision_status = null;
        $newHistory->alert_sent_at = now();
        if ($historyOriginal) {
            $newHistory->ayudas_config = $historyOriginal->ayudas_config;
            $newHistory->almacen_archivos = $historyOriginal->almacen_archivos;
        }
        $newHistory->save();

        $oldPreOrdenes = PreOrdenFundicion::where('ot', '=', $otRaw, 'and')->get();
        $generatedPdfUrl = null;

        foreach ($oldPreOrdenes as $oldPo) {
            $oldFilas = is_string($oldPo->filas) ? json_decode((string)$oldPo->filas, true) : $oldPo->filas;
            $newFilas = [];
            foreach ($oldFilas as $fila) {
                if (isset($fila['clase']) && in_array(strtolower($fila['clase']), array_map('strtolower', $clases))) {
                    $newFilas[] = $fila;
                }
            }

            if (count($newFilas) > 0) {
                $newPoData = $oldPo->toArray();
                $newPoData['ot'] = $newOt;
                $newPoData['ot_raw'] = $newOt;
                $newPoData['filas'] = $newFilas;
                // Add suffix to folio to keep it unique-ish or trackable
                if (isset($newPoData['folio'])) {
                    $newPoData['folio'] .= $newSuffix;
                }

                ini_set('memory_limit', '512M');
                $pdf = Pdf::loadView('pdf.pre_orden', [
                    'data' => $newPoData,
                    'user' => Auth::user()
                ])->setPaper('a4', 'landscape');

                $folio = preg_replace('/[^A-Za-z0-9\-]/', '_', $newPoData['folio']);
                $fechaStamp = date('d_m_Y_H_i');
                preg_match('/OT\s*(\d+)/i', $newOt, $matches);
                $otId = $matches[1] ?? (preg_replace('/[^0-9]/', '', $newOt) ?: 'SN');
                $fileName = "Pre-Orden_Fundicion-{$folio}_OT_{$otId}_{$fechaStamp}.pdf";

                $folderNameNew = $this->sanitizePath($this->normalizeOTName($newOt));
                $otPathNew = self::ALMACEN_DIR . '/' . $folderNameNew . '/Documentos_Aprobados/Preorden_Modelo';
                $savePathNew = $otPathNew . '/' . $fileName;

                if (!Storage::disk('local')->exists($otPathNew)) {
                    Storage::disk('local')->makeDirectory($otPathNew);
                }

                Storage::disk('local')->put($savePathNew, $pdf->output());

                $newPo = new PreOrdenFundicion();
                $newPo->ot = $newOt;
                $newPo->folio = $newPoData['folio'] ?? '';
                $newPo->proveedor = $oldPo->proveedor;
                $newPo->fecha_creacion = now();
                $newPo->fecha_entrega = $oldPo->fecha_entrega;
                $newPo->moldura = $oldPo->moldura;
                $newPo->observaciones = 'RECHAZO: ' . ($oldPo->observaciones ?? '');
                $newPo->filas = $newFilas;
                $newPo->user_id = Auth::id();
                $newPo->user_nombre = Auth::user()->nombre ?? 'Sistema';
                $newPo->version = 1;
                $newPo->pdf_filename = $fileName;
                $newPo->save();

                $generatedPdfUrl = route('almacen.fundicion.serve', [
                    'ot' => $newOt,
                    'archivo' => 'Documentos_Aprobados/Preorden_Modelo/' . $fileName,
                    'tipo' => 'otro',
                    'origin' => 'aprobado'
                ]);
                
                // Copy original drawings and ayudas visuales to the new OT folder recursively (excluding preordenes/Documentos_Aprobados/Documentos_Rechazados)
                $originalBaseDir = self::ALMACEN_DIR . '/' . $folderNameOriginal;
                $newBaseDir = self::ALMACEN_DIR . '/' . $folderNameNew;
                if (Storage::disk('local')->exists($originalBaseDir)) {
                    $allFiles = Storage::disk('local')->allFiles($originalBaseDir);
                    foreach ($allFiles as $file) {
                        $relPath = str_replace($originalBaseDir . '/', '', $file);
                        if (strpos($relPath, 'preordenes') === false && strpos($relPath, 'Documentos_Aprobados') === false && strpos($relPath, 'Documentos_Rechazados') === false) {
                            $targetPath = $newBaseDir . '/' . $relPath;
                            $targetDir = dirname($targetPath);
                            if (!Storage::disk('local')->exists($targetDir)) {
                                Storage::disk('local')->makeDirectory($targetDir);
                            }
                            if (!Storage::disk('local')->exists($targetPath)) {
                                Storage::disk('local')->copy($file, $targetPath);
                            }
                        }
                    }
                }
            }
        }

        return response()->json([
            'success' => true, 
            'message' => "Rechazos procesados correctamente. Se generó la $newOt.",
            'pdf_url' => $generatedPdfUrl,
            'new_ot' => $newOt
        ]);
    }
}