<?php

namespace App\Http\Controllers;

use App\Models\FundicionHistory;
use App\Models\LiberacionModeloFundicion;
use App\Models\Orden_trabajo;
use App\Models\Clase;
use App\Models\PreOrdenFundicion;
use App\Models\PreOrdenLog;
use App\Models\RechazoLog;
use App\Models\ScarModelo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Normalizer;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Mail;
use App\Services\FundicionPaths;
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
     * 1 = Admin | 2 = Admin | 3 = Jefe | 5 = Almacen
     */
    private const PERFILES_PERMITIDOS = ['1', '2', '3', '5'];

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

        return view('almacen.almacen_fundicion', compact(
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
     * @return \Illuminate\Http\JsonResponse
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

        $modelPreOrden = PreOrdenFundicion::where('ot', '=', $ot, 'and')->where('pdf_filename', 'NOT LIKE', '%Casting%', 'and')->first();
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
                        $parts = explode(',', $val);
                        foreach (['candado obturador', 'cabeza de soplo', 'obturador', 'bombillo', 'embudo', 'corona', 'plato', 'molde', 'fondo'] as $kc) {
                            foreach ($parts as $p) {
                                if (trim($p) === $kc) {
                                    $activeClasses[] = $kc;
                                }
                            }
                        }
                    }
                }
            }
        }

        $todo = $request->query('todo', '0') === '1';
        $tipoPeticion = $request->query('tipo', '');

        if ($todo || $tipoPeticion === 'modelo') {
            $activeClasses = ['candado obturador', 'cabeza de soplo', 'obturador', 'bombillo', 'embudo', 'corona', 'plato', 'molde', 'fondo'];
        } else {
            // Filtrar clases activas basándose en las decisiones de Calidad
            $isReproceso = preg_match('/_R\d+$/i', $ot);
            if ($isReproceso) {
                // Para un reproceso, solo mostramos las clases que fueron RECHAZADAS en el ciclo anterior
                $prevOt = preg_replace_callback('/_R(\d+)$/i', function ($m) {
                    $num = intval($m[1]) - 1;
                    return $num > 0 ? '_R' . $num : '';
                }, $ot);

                $rechazados = LiberacionModeloFundicion::where('ot', '=', $prevOt, 'and')
                    ->where('decision', '=', 'rechazar', 'and')
                    ->pluck('tipo_modelo')
                    ->toArray();

                $validClasses = [];
                foreach ($rechazados as $r) {
                    $clases = array_map('trim', explode(',', strtolower($r)));
                    foreach ($clases as $c) {
                        $validClasses[] = $c;
                    }
                }
                if (!empty($validClasses)) {
                    $activeClasses = $validClasses;
                }
            } else {
                // Para la OT base, si ya pasó por Calidad, solo mostramos las APROBADAS (para Casting)
                $hasLiberaciones = LiberacionModeloFundicion::where('ot', '=', $ot, 'and')->exists();
                if ($hasLiberaciones) {
                    $aprobados = LiberacionModeloFundicion::where('ot', '=', $ot, 'and')
                        ->where('decision', '=', 'aprobar', 'and')
                        ->pluck('tipo_modelo')
                        ->toArray();

                    $validClasses = [];
                    foreach ($aprobados as $a) {
                        $clases = array_map('trim', explode(',', strtolower($a)));
                        foreach ($clases as $c) {
                            $validClasses[] = $c;
                        }
                    }
                    if (!empty($validClasses)) {
                        $activeClasses = $validClasses;
                    } else {
                        $activeClasses = [];
                    }
                }
            }

            if (empty($activeClasses)) {
                $activeClasses = ['candado obturador', 'cabeza de soplo', 'obturador', 'bombillo', 'embudo', 'corona', 'plato', 'molde', 'fondo'];
            }
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
            $sharedDir = $this->resolveCaseInsensitivePath(self::ALMACEN_DIR . '/' . $relFolder);
            $sharedAyudasDir = $this->resolveCaseInsensitivePath($sharedDir . '/ayudas_visuales');

            if (!$soloPreorden) {
                // 1a. Dibujos — nueva ruta: {Clase}/Dibujos/ (con fallback a raíz de clase)
                foreach (['Candado obturador', 'Cabeza de soplo', 'Obturador', 'Bombillo', 'Embudo', 'Corona', 'Plato', 'Molde', 'Fondo'] as $claseDir) {
                    $claseNorm = strtolower($claseDir);
                    if (!in_array($claseNorm, $activeClasses))
                        continue;

                    // Nueva ruta primero
                    $newDibjPath = $this->resolveCaseInsensitivePath(self::ALMACEN_DIR . '/' . $relFolder . '/' . $claseDir . '/' . FundicionPaths::DIBUJOS);
                    // Legacy: dibujos en raíz de carpeta de clase
                    $legacyDibjPath = $this->resolveCaseInsensitivePath(self::ALMACEN_DIR . '/' . $relFolder . '/' . $claseDir);

                    foreach ([$newDibjPath, $legacyDibjPath] as $scanDibjDir) {
                        if (!Storage::disk('local')->exists($scanDibjDir))
                            continue;
                        $relatedDibujos = collect(Storage::disk('local')->files($scanDibjDir))
                            ->filter(fn($f) => strtolower(pathinfo($f, PATHINFO_EXTENSION)) === 'pdf')
                            ->map(function ($f) use ($relatedOt, $relFolder, $claseDir) {
                                $relName = $claseDir . '/Dibujos/' . basename($f);
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
                }

                // 1b. Fallback genérico: archivos en la raíz de sharedDir que no sean carpetas conocidas
                if (Storage::disk('local')->exists($sharedDir)) {
                    $relatedDibujos = collect(Storage::disk('local')->allFiles($sharedDir))
                        ->filter(function ($f) use ($sharedDir) {
                            $rel = str_replace(str_replace('\\', '/', $sharedDir) . '/', '', str_replace('\\', '/', $f));
                            // Excluir archivos en subcarpetas de nueva estructura (ya cubiertos por 1a y 2a)
                            return !str_contains($rel, 'Ayudas_Visuales')
                                && !str_contains($rel, 'ayudas_visuales')
                                && !str_contains($rel, '/Dibujos/')
                                && !str_starts_with($rel, 'Dibujos/')
                                && !str_starts_with($rel, 'Documentos_Aprobados/')
                                && !str_starts_with($rel, 'Documentos_Rechazados/')
                                && !str_starts_with($rel, 'preordenes/')
                                && strtolower(pathinfo($f, PATHINFO_EXTENSION)) === 'pdf';
                        })
                        ->filter(function ($f) use ($sharedDir, $activeClasses) {
                            $rel = str_replace(str_replace('\\', '/', $sharedDir) . '/', '', str_replace('\\', '/', $f));
                            $lower = strtolower($rel);
                            $known = ['candado obturador', 'cabeza de soplo', 'obturador', 'bombillo', 'embudo', 'corona', 'plato', 'molde', 'fondo'];
                            foreach ($known as $k) {
                                if (strpos($lower, $k) !== false) {
                                    return in_array($k, $activeClasses);
                                }
                            }
                            return true;
                        })
                        ->map(function ($f) use ($relatedOt, $sharedDir) {
                            $relName = ltrim(str_replace(str_replace('\\', '/', $sharedDir), '', str_replace('\\', '/', $f)), '/');
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

                // 2a. Ayudas Visuales — nueva ruta: {Clase}/Ayudas_Visuales/ (con fallback legacy)
                foreach (['Candado obturador', 'Cabeza de soplo', 'Obturador', 'Bombillo', 'Embudo', 'Corona', 'Plato', 'Molde', 'Fondo'] as $claseDir) {
                    $claseNorm = strtolower($claseDir);
                    if (!in_array($claseNorm, $activeClasses))
                        continue;

                    $newAyPath = $this->resolveCaseInsensitivePath(self::ALMACEN_DIR . '/' . $relFolder . '/' . $claseDir . '/' . FundicionPaths::AYUDAS_VISUALES);
                    $legacyAyPath = $this->resolveCaseInsensitivePath(self::ALMACEN_DIR . '/' . $relFolder . '/ayudas_visuales/' . $claseDir);

                    foreach ([$newAyPath, $legacyAyPath] as $scanAyDir) {
                        if (!Storage::disk('local')->exists($scanAyDir))
                            continue;
                        $relatedAyudas = collect(Storage::disk('local')->files($scanAyDir))
                            ->filter(fn($f) => strtolower(pathinfo($f, PATHINFO_EXTENSION)) === 'pdf')
                            ->map(function ($f) use ($relatedOt, $claseDir) {
                                $relName = $claseDir . '/Ayudas_Visuales/' . basename($f);
                                $utf8RelName = $this->toUtf8($relName);
                                return [
                                    'nombre' => $utf8RelName,
                                    'tipo' => 'ayuda',
                                    'url' => route('almacen.fundicion.serve', [
                                        'ot' => $relatedOt,
                                        'archivo' => $utf8RelName,
                                        'tipo' => 'ayuda',
                                    ]),
                                ];
                            });
                        $ayudas = $ayudas->merge($relatedAyudas);
                    }
                }
            }

            // 3. Documentos generados (Preordenes, Evidencias, Confirmaciones, LDM, SCAR)
            $dirsToScan = [];

            // --- NUEVO ESQUEMA DE APARTADOS ESPECÍFICOS ---
            // Todos pueden leer de Documentos_Aprobados
            $dirsToScan[] = [
                'path' => $this->resolveCaseInsensitivePath(self::ALMACEN_DIR . '/' . $relFolder . '/Documentos_Aprobados'),
                'origin' => 'aprobado',
                'prefix' => 'Documentos_Aprobados/'
            ];
            // Todos pueden leer de Documentos_Rechazados
            $dirsToScan[] = [
                'path' => $this->resolveCaseInsensitivePath(self::ALMACEN_DIR . '/' . $relFolder . '/Documentos_Rechazados'),
                'origin' => 'rechazado',
                'prefix' => 'Documentos_Rechazados/'
            ];

            // --- PREORDENES (NUEVA RUTA RAÍZ Y RUTAS LEGACY) -> ORIGIN = 'aprobado' PARA APARECER EN CONTENEDOR DE DOCUMENTOS APROBADOS ---
            $preOrdenesCandidates = [
                self::ALMACEN_DIR . '/' . $relFolder . '/preordenes',
                self::CALIDAD_DIR . '/' . $relFolder . '/preordenes',
                self::ALMACEN_DIR . '/' . $relFolder . '/Documentos_Aprobados/preordenes',
                self::CALIDAD_DIR . '/' . $relFolder . '/Documentos_Aprobados/preordenes',
                self::ALMACEN_DIR . '/' . $relFolder . '/ayudas_visuales/preordenes',
                self::CALIDAD_DIR . '/' . $relFolder . '/ayudas_visuales/preordenes',
                self::ALMACEN_DIR . '/' . $relFolder . '/ayudas_visuales/preordenes/documentos_aprobados',
                self::CALIDAD_DIR . '/' . $relFolder . '/ayudas_visuales/preordenes/documentos_aprobados',
                self::ALMACEN_DIR . '/' . $relFolder . '/preordenes/documentos_aprobados',
                self::CALIDAD_DIR . '/' . $relFolder . '/preordenes/documentos_aprobados',
            ];

            foreach ($preOrdenesCandidates as $poCandPath) {
                $resolvedPoPath = $this->resolveCaseInsensitivePath($poCandPath);
                if (!empty($resolvedPoPath)) {
                    $dirsToScan[] = [
                        'path' => $resolvedPoPath,
                        'origin' => 'aprobado',
                        'prefix' => 'preordenes/'
                    ];
                }
            }

            if ($isAdmin) {
                $dirsToScan[] = [
                    'path' => $this->resolveCaseInsensitivePath(self::CALIDAD_DIR . '/' . $relFolder . '/Documentos_Aprobados'),
                    'origin' => 'aprobado',
                    'prefix' => 'Documentos_Aprobados/'
                ];
                $dirsToScan[] = [
                    'path' => $this->resolveCaseInsensitivePath(self::CALIDAD_DIR . '/' . $relFolder . '/Documentos_Rechazados'),
                    'origin' => 'rechazado',
                    'prefix' => 'Documentos_Rechazados/'
                ];
            } elseif ($isQuality) {
                $dirsToScan[] = [
                    'path' => $this->resolveCaseInsensitivePath(self::CALIDAD_DIR . '/' . $relFolder . '/Documentos_Aprobados'),
                    'origin' => 'aprobado',
                    'prefix' => 'Documentos_Aprobados/'
                ];
                $dirsToScan[] = [
                    'path' => $this->resolveCaseInsensitivePath(self::CALIDAD_DIR . '/' . $relFolder . '/Documentos_Rechazados'),
                    'origin' => 'rechazado',
                    'prefix' => 'Documentos_Rechazados/'
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
                            if (!$isDoc)
                                return false;

                            $fNorm = str_replace('\\', '/', $f);
                            $dirNorm = str_replace('\\', '/', $scanPath);
                            $relName = ltrim(str_replace($dirNorm, '', $fNorm), '/');
                            $fileLower = strtolower($relName);

                            if (str_contains($fileLower, 'pre-orden') || str_contains($fileLower, 'preorden')) {
                                return true;
                            }

                            $knownClasses = ['candado obturador', 'cabeza de soplo', 'obturador', 'bombillo', 'embudo', 'corona', 'plato', 'molde', 'fondo'];
                            $hasKnownClass = false;
                            $foundClass = null;
                            foreach ($knownClasses as $kc) {
                                if (strpos($fileLower, $kc) !== false) {
                                    $hasKnownClass = true;
                                    $foundClass = $kc;
                                    break;
                                }
                            }
                            if ($hasKnownClass) {
                                $matchesActive = in_array($foundClass, $activeClasses);
                                if (!$matchesActive)
                                    return false;
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
        $preOrden = ($historyLatest && $historyLatest->ot) ? PreOrdenFundicion::where('ot', '=', $historyLatest->ot, 'and')->first() : null;
        $fechaEntrega = $preOrden && $preOrden->fecha_entrega
            ? ($preOrden->fecha_entrega instanceof \DateTimeInterface
                ? $preOrden->fecha_entrega->format('Y-m-d')
                : substr((string) $preOrden->fecha_entrega, 0, 10))
            : null;
        $proveedor = $preOrden ? $preOrden->proveedor : null;

        return response()->json([
            'existe' => true,
            'archivos' => $allFiles,
            'ot' => $ot,
            'status' => $historyLatest ? $historyLatest->status : null,
            'tiene_modelo' => $historyLatest ? (bool) $historyLatest->tiene_modelo : false,
            'casting_pdf_generated' => $historyLatest ? (bool) $historyLatest->casting_pdf_generated : false,
            'alert_sent_at' => $historyLatest ? $historyLatest->alert_sent_at?->format('d/m/Y H:i') : null,
            'fecha_entrega' => $fechaEntrega,
            'proveedor' => $proveedor,
        ]);
    }

    // =========================================================================
    // SERVIR ARCHIVOS (Solo Lectura)
    // =========================================================================

    /**
     * Sirve un PDF desde el directorio aislado FUNDICION_ALMACEN/.
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
                // Calidad/Master solo ve preordenes si pre_orden_email_sent es true
                $isPreorden = ($tipo === 'otro' || str_starts_with(strtolower($archivo), 'preordenes/'));
                $isAllowedBeforeAlert = str_contains(strtolower($archivo), 'documentos_aprobados') || str_contains(strtolower($archivo), 'documentos_rechazados') || str_contains(strtolower($archivo), 'confirmacion') || str_contains($archivo, 'F-CCL-LDM') || str_contains($archivo, 'SCAR');
                if ($isPreorden && !$isAllowedBeforeAlert && !$history->pre_orden_email_sent) {
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
                        in_array($status, ['calidad_aprobado', 'calidad_rechazado', 'calidad_mixto', 'calidad_parcial', 'casting_aprobado']) ||
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
                } elseif (
                    str_contains(strtolower($archivo), 'ayudas_visuales') && (
                        str_starts_with(strtolower($archivo), 'bombillo/') ||
                        str_starts_with(strtolower($archivo), 'fondo/') ||
                        str_starts_with(strtolower($archivo), 'obturador/') ||
                        str_starts_with(strtolower($archivo), 'molde/')
                    )
                ) {
                    // Nueva estructura: ayudas_visuales vive en el root de la OT bajo la carpeta de la clase
                    $baseDir = ($origin === 'calidad' || (($user->perfil == 4 || $user->perfil == 3) && empty($origin)))
                        ? self::CALIDAD_DIR . '/' . $folderName
                        : self::ALMACEN_DIR . '/' . $folderName;
                } elseif ($origin === 'calidad' || (($user->perfil == 4 || $user->perfil == 3) && empty($origin))) {
                    $baseDir = self::CALIDAD_DIR . '/' . $folderName . '/ayudas_visuales';
                } else {
                    $baseDir = self::ALMACEN_DIR . '/' . $folderName . '/ayudas_visuales';
                }
            } else {
                $baseDir = self::ALMACEN_DIR . '/' . $folderName;
            }
        }

        $baseDir = $this->resolveCaseInsensitivePath($baseDir);

        // Si el directorio principal no existe, intentar fallback cross-OT (base ↔ _R1/_R2)
        if (!Storage::disk('local')->exists($baseDir)) {
            $baseOtRaw = preg_replace('/_R\d+$/', '', $ot);
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
                Log::warning("Directorio no encontrado en Almacén (serveFile). OT: {$ot}, Archivo buscado: {$archivo}. Buscado en múltiples alternativas cross-OT.");
                abort(404, 'Directorio no encontrado.');
            }
        }

        $files = Storage::disk('local')->allFiles($baseDir);
        $foundFile = null;
        $archivoNorm = Normalizer::normalize(mb_strtolower($archivo, 'UTF-8'), Normalizer::FORM_C);

        foreach ($files as $f) {
            $fNorm = str_replace('\\', '/', $f);
            $baseDirNorm = str_replace('\\', '/', $baseDir);
            $relName = ltrim(str_replace($baseDirNorm, '', $fNorm), '/');

            $utf8RelName = $this->toUtf8($relName);
            $utf8RelNameNorm = Normalizer::normalize(mb_strtolower($utf8RelName, 'UTF-8'), Normalizer::FORM_C);

            if ($utf8RelNameNorm === $archivoNorm) {
                if ($tipo === 'dibujo' && strpos($relName, 'ayudas_visuales/') === 0)
                    continue;

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

                    if ($utf8RelNameNorm === $archivoNorm) {
                        $foundFile = $f;
                        break 2;
                    }
                }
            }
        }

        if (!$foundFile) {
            Log::warning("Archivo no encontrado en Almacén (serveFile). OT: {$ot}, Archivo buscado: {$archivo}, Directorio Final: {$baseDir}");
            abort(404, 'Archivo no encontrado.');
        }

        /** @var \Illuminate\Filesystem\FilesystemAdapter $disk */
        $disk = Storage::disk('local');
        $fullPath = $disk->path($foundFile);

        $ext = strtolower(pathinfo($fullPath, PATHINFO_EXTENSION));
        $mimeMap = [
            'pdf' => 'application/pdf',
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'gif' => 'image/gif',
            'webp' => 'image/webp',
        ];
        $mimeType = $mimeMap[$ext] ?? (mime_content_type($fullPath) ?: 'application/octet-stream');

        return response()->file($fullPath, [
            'Content-Type' => $mimeType,
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
                $isAllowedBeforeAlert = str_contains(strtolower($archivo), 'documentos_aprobados') || str_contains(strtolower($archivo), 'documentos_rechazados') || str_contains(strtolower($archivo), 'confirmacion') || str_contains($archivo, 'F-CCL-LDM') || str_contains($archivo, 'SCAR');
                if ($isPreorden && !$isAllowedBeforeAlert && !$history->pre_orden_email_sent) {
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
                        in_array($status, ['calidad_aprobado', 'calidad_rechazado', 'calidad_mixto', 'calidad_parcial', 'casting_aprobado']) ||
                        ScarModelo::where('ot', '=', $ot, 'and')->where('estatus', '=', 'alertado', 'and')->exists() ||
                        ScarModelo::where('ot', '=', $folderName, 'and')->where('estatus', '=', 'alertado', 'and')->exists()
                    );
                    if (!$calidadAlertaEnviada) {
                        return response()->json(['success' => false, 'error' => 'Acceso denegado. Calidad no ha enviado la alerta de rechazo o aprobación.'], 403);
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
                } elseif ($origin === 'calidad' || (($user->perfil == 4 || $user->perfil == 3) && empty($origin))) {
                    $baseDir = self::CALIDAD_DIR . '/' . $folderName . '/ayudas_visuales';
                } else {
                    $baseDir = self::ALMACEN_DIR . '/' . $folderName . '/ayudas_visuales';
                }
            } else {
                $baseDir = self::ALMACEN_DIR . '/' . $folderName;
            }
        }

        $baseDir = $this->resolveCaseInsensitivePath($baseDir);

        if (!Storage::disk('local')->exists($baseDir)) {
            // Fallback ampliado incluyendo la OT base para reprocesos
            $baseOtRaw = preg_replace('/_R\d+$/', '', $ot);
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
        foreach ($files as $f) {
            $fNorm = str_replace('\\', '/', $f);
            $baseDirNorm = str_replace('\\', '/', $baseDir);
            $relName = ltrim(str_replace($baseDirNorm, '', $fNorm), '/');

            $utf8RelName = $this->toUtf8($relName);
            if (mb_strtolower($utf8RelName, 'UTF-8') === mb_strtolower($archivo, 'UTF-8')) {
                if ($tipo === 'dibujo' && strpos($relName, 'ayudas_visuales/') === 0)
                    continue;

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
                    if (mb_strtolower($utf8RelName, 'UTF-8') === mb_strtolower($archivo, 'UTF-8')) {
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
        if ($user->perfil != 1 && $user->perfil != 2) {
            if ($fileOwner === 'almacen' && $user->perfil != 5 && $user->perfil != 3) {
                return response()->json(['success' => false, 'error' => 'Acceso denegado. Solo Almacén puede eliminar este documento.'], 403);
            }
            if ($fileOwner === 'calidad' && $user->perfil != 4) {
                return response()->json(['success' => false, 'error' => 'Acceso denegado. Solo Calidad puede eliminar este documento.'], 403);
            }
        }

        // Verificar el estado de la alerta
        $history = FundicionHistory::where('ot', '=', $ot, 'and')->first();
        if (!$history) {
            $history = FundicionHistory::where('ot', '=', $folderName, 'and')->first();
        }

        if ($history && $user->perfil != 1 && $user->perfil != 2) {
            if ($fileOwner === 'almacen') {
                $alertSent = (bool) ($history->pre_orden_email_sent || $history->pre_orden_sent);
                if ($alertSent) {
                    return response()->json(['success' => false, 'error' => 'No se puede eliminar. La alerta de Almacén ya ha sido enviada.'], 403);
                }
            } elseif ($fileOwner === 'calidad') {
                $alertSent = in_array($history->calidad_revision_status, ['aprobado', 'rechazado', 'mixto', 'calidad_aprobado', 'calidad_rechazado', 'calidad_mixto', 'calidad_parcial', 'casting_aprobado']);
                if ($alertSent) {
                    return response()->json(['success' => false, 'error' => 'No se puede eliminar. La alerta de Calidad ya ha sido enviada.'], 403);
                }
            }
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

        // Si es una pre-orden, eliminar también el registro de la base de datos
        if (str_contains(strtolower($fileNameOnly), 'pre-orden')) {
            $preOrden = PreOrdenFundicion::query()
                ->where('ot', '=', $ot, 'and')
                ->where('pdf_filename', '=', $fileNameOnly, 'and')
                ->first();
            if ($preOrden && isset($preOrden->id)) {
                PreOrdenFundicion::destroy($preOrden->id);
            }
        }

        return response()->json(['success' => true, 'message' => 'Archivo eliminado correctamente y sincronizado en todos los directorios.']);
    }

    // =========================================================================
    // HELPERS PRIVADOS
    // =========================================================================

    private function resolveCaseInsensitivePath(string $path): string
    {
        // Optimización masiva: si la ruta exacta ya existe, devolverla inmediatamente
        if (Storage::disk('local')->exists($path)) {
            return $path;
        }

        $parts = explode('/', str_replace('\\', '/', $path));
        $resolved = '';

        foreach ($parts as $part) {
            if ($part === '')
                continue;

            $currentSearch = $resolved ? $resolved : '.';

            $exactPath = $resolved ? $resolved . '/' . $part : $part;
            if (Storage::disk('local')->exists($exactPath)) {
                $resolved = $exactPath;
                continue;
            }

            if (!Storage::disk('local')->exists($currentSearch)) {
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
        $destinatario = $request->input('destinatario');
        $destinatarioCalidad = $request->input('destinatario_calidad');
        $history = FundicionHistory::where('ot', '=', $ot, 'and')->first();

        if (!$history) {
            return response()->json(['success' => false, 'message' => 'Registro no encontrado.'], 404);
        }

        $clasesSeleccionadas = $request->input('clases_seleccionadas', []);

        // Determinar si se seleccionaron todas las clases activas de la OT para el nombre del archivo
        $clasesSuffix = "";
        if (is_array($clasesSeleccionadas) && count($clasesSeleccionadas) > 0) {
            $baseOt = preg_replace('/_R\d+$/i', '', $ot);
            preg_match('/OT\s*(\d+)/', $baseOt, $matches);
            $otId = isset($matches[1]) ? (int) $matches[1] : 0;
            $otFullRaw = Orden_trabajo::query()->find($otId);

            $allActiveClassesCount = 4; // default
            if ($otFullRaw) {
                $clasesActivasCount = Clase::query()
                    ->where('id_ot', '=', $otFullRaw->id)
                    ->count();
                if ($clasesActivasCount > 0) {
                    $allActiveClassesCount = $clasesActivasCount;
                }
            }

            if (count($clasesSeleccionadas) < $allActiveClassesCount) {
                $nombres = [];
                foreach ($clasesSeleccionadas as $c) {
                    $l = strtolower($c);
                    if (strpos($l, 'candado obturador') !== false)
                        $nombres[] = 'Candado obturador';
                    elseif (strpos($l, 'cabeza de soplo') !== false)
                        $nombres[] = 'Cabeza de soplo';
                    elseif (strpos($l, 'embudo') !== false)
                        $nombres[] = 'Embudo';
                    elseif (strpos($l, 'corona') !== false)
                        $nombres[] = 'Corona';
                    elseif (strpos($l, 'plato') !== false)
                        $nombres[] = 'Plato';
                    elseif (strpos($l, 'fondo') !== false)
                        $nombres[] = 'Fondo';
                    elseif (strpos($l, 'obturador') !== false)
                        $nombres[] = 'Obturador';
                    elseif (strpos($l, 'molde') !== false)
                        $nombres[] = 'Molde';
                    elseif (strpos($l, 'bombillo') !== false)
                        $nombres[] = 'Bombillo';
                }
                if (count($nombres) > 0) {
                    $clasesSuffix = "_" . implode("_", $nombres);
                }
            }
        }

        $attachments = [];

        // ── LIMPIEZA DE DOCUMENTOS PREVIOS AL REINICIAR CLASES ──────────────────
        $baseOt = preg_replace('/_R\d+$/i', '', $ot);
        preg_match('/OT\s*(\d+)/', $baseOt, $matches);
        $otId = isset($matches[1]) ? (int) $matches[1] : 0;
        $otFullRaw = Orden_trabajo::query()->find($otId);

        $clasesActivasNorm = [];
        if ($otFullRaw) {
            $clRawList = Clase::query()->where('id_ot', '=', $otFullRaw->id)->pluck('nombre')->toArray();
            foreach ($clRawList as $cr) {
                $crLow = strtolower($cr);
                foreach (['candado obturador', 'cabeza de soplo', 'obturador', 'bombillo', 'embudo', 'corona', 'plato', 'molde', 'fondo'] as $kc) {
                    if (strpos($crLow, $kc) !== false) {
                        $clasesActivasNorm[] = $kc;
                    }
                }
            }
        }
        $clasesActivasNorm = array_values(array_unique($clasesActivasNorm));

        $clasesSeleccionadasNorm = [];
        if (is_array($clasesSeleccionadas)) {
            foreach ($clasesSeleccionadas as $cs) {
                $csLow = strtolower($cs);
                foreach (['candado obturador', 'cabeza de soplo', 'obturador', 'bombillo', 'embudo', 'corona', 'plato', 'molde', 'fondo'] as $kc) {
                    if (strpos($csLow, $kc) !== false) {
                        $clasesSeleccionadasNorm[] = $kc;
                    }
                }
            }
        }
        $clasesSeleccionadasNorm = array_values(array_unique($clasesSeleccionadasNorm));

        $esReinicioTotal = (!empty($clasesActivasNorm) && count(array_diff($clasesActivasNorm, $clasesSeleccionadasNorm)) === 0);

        if ($esReinicioTotal) {
            // Si es reinicio total (todas las clases activas se confirman de nuevo), reseteamos todo en Storage y BD
            \App\Http\Controllers\DibujosFundicionPdfController::copyToAlmacen($ot, true);
        } else {
            // Si es parcial, borramos los documentos aprobados/rechazados de las clases seleccionadas
            foreach ($clasesSeleccionadas as $claseNombre) {
                $tipo = null;
                $clLow = strtolower($claseNombre);
                foreach (['candado obturador', 'cabeza de soplo', 'obturador', 'bombillo', 'embudo', 'corona', 'plato', 'molde', 'fondo'] as $kc) {
                    if (strpos($clLow, $kc) !== false) {
                        $tipo = $kc;
                        break;
                    }
                }

                if ($tipo) {
                    // 1. Limpiar veredicto de Calidad y SCAR
                    LiberacionModeloFundicion::where('ot', '=', $ot, 'and')
                        ->where('tipo_modelo', '=', $tipo, 'and')
                        ->delete();

                    ScarModelo::where('ot', '=', $ot, 'and')
                        ->where('tipo_modelo', '=', $tipo, 'and')
                        ->delete();

                    // 2. Eliminar PDFs de LDM y SCAR en public/liberaciones_pdf
                    $liberacionesPath = storage_path('app/public/liberaciones_pdf');
                    $claseNorm = strtolower(trim($tipo));
                    $otSanitizada = preg_replace('/[^\w\s\-]/', '', $ot);
                    $otSanitizada = preg_replace('/[\s]+/', '_', trim($otSanitizada));

                    if (file_exists($liberacionesPath)) {
                        $patterns = [
                            "{$liberacionesPath}/*{$claseNorm}*{$otSanitizada}*.pdf",
                            "{$liberacionesPath}/*{$otSanitizada}*{$claseNorm}*.pdf",
                            "{$liberacionesPath}/F-CCL-SCAR_*{$claseNorm}*.pdf",
                            "{$liberacionesPath}/F-CCL-LDM_*{$claseNorm}*.pdf",
                        ];
                        foreach ($patterns as $p) {
                            foreach (glob($p) ?: [] as $f) {
                                if (file_exists($f)) {
                                    @unlink($f);
                                }
                            }
                        }
                    }

                    // 3. Eliminar carpetas físicas de Documentos_Aprobados / Documentos_Rechazados de la clase
                    $classSubFolder = strtolower(preg_replace('/[^a-zA-Z0-9_\-]/', '_', trim($tipo)));
                    $baseRoots = [
                        self::ALMACEN_DIR,
                        'DOCUMENTACION_GIS/ALMACEN_FUNDICION',
                        'DOCUMENTACION_GIS/CALIDAD_FUNDICION',
                        'DOCUMENTACION_GIS/Fundicion_Calidad',
                    ];
                    $roots = [];
                    $baseOtStr = preg_replace('/_R\d+$/i', '', $ot);
                    foreach ($baseRoots as $br) {
                        if (Storage::disk('local')->exists($br)) {
                            $dirs = Storage::disk('local')->directories($br);
                            foreach ($dirs as $d) {
                                if (str_starts_with(basename($d), $baseOtStr)) {
                                    $roots[] = $d;
                                }
                            }
                        }
                    }
                    foreach ($roots as $r) {
                        if (Storage::disk('local')->exists($r)) {
                            $subFoldersToClean = [
                                $r . '/Documentos_Aprobados/' . $classSubFolder,
                                $r . '/Documentos_Rechazados/' . $classSubFolder,
                                $r . '/Documentos_Rechazados/SCAR/' . $classSubFolder,
                            ];
                            foreach ($subFoldersToClean as $sfc) {
                                if (Storage::disk('local')->exists($sfc)) {
                                    Storage::disk('local')->deleteDirectory($sfc);
                                }
                            }
                            foreach (['/Documentos_Rechazados', '/Documentos_Aprobados', '/ayudas_visuales/preordenes/documentos_aprobados'] as $sub) {
                                $targetSub = $r . $sub;
                                if (Storage::disk('local')->exists($targetSub)) {
                                    foreach (Storage::disk('local')->files($targetSub) as $f) {
                                        if (str_contains(strtolower(basename($f)), $claseNorm)) {
                                            Storage::disk('local')->delete($f);
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }

            // Sincronizar snapshot en Almacén
            \App\Http\Controllers\DibujosFundicionPdfController::copyToAlmacen($ot, false);
        }

        // ── Guardar archivos de recepción adjuntos (Bloque 2) ──────────────────
        // NOTA: Se ejecuta DESPUÉS de la limpieza previa para evitar que copyToAlmacen los elimine
        if ($request->hasFile('archivos')) {
            $folderName = $this->sanitizePath($this->normalizeOTName($ot));
            $destDir = self::ALMACEN_DIR . '/' . $folderName . '/Documentos_Aprobados/confirmacion_modelo';

            if (!Storage::disk('local')->exists($destDir)) {
                Storage::disk('local')->makeDirectory($destDir);
            }

            foreach ($request->file('archivos') as $file) {
                $ext = $file->getClientOriginalExtension();
                $safeName = preg_replace('/[^A-Za-z0-9_\-.]/', '_', pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME));
                $stamp = date('d_m_Y_H_i_s');
                $fileName = "ConfirmacionModelo{$clasesSuffix}_{$safeName}_{$stamp}.{$ext}";
                Storage::disk('local')->put($destDir . '/' . $fileName, file_get_contents($file->getRealPath()));

                $attachments[] = [
                    'path' => storage_path('app/' . $destDir . '/' . $fileName),
                    'name' => $fileName,
                    'mime' => strtolower($ext) === 'pdf' ? 'application/pdf' : 'image/' . strtolower($ext)
                ];
            }
        }

        // Crear o actualizar el registro de liberacion indicando el origen
        $fecha = $request->input('fecha');

        if (is_array($clasesSeleccionadas) && count($clasesSeleccionadas) > 0) {
            foreach ($clasesSeleccionadas as $claseNombre) {
                $tipo = null;
                $clLow = strtolower($claseNombre);
                if (strpos($clLow, 'candado obturador') !== false) {
                    $tipo = 'Candado obturador';
                } elseif (strpos($clLow, 'cabeza de soplo') !== false) {
                    $tipo = 'Cabeza de soplo';
                } elseif (strpos($clLow, 'embudo') !== false) {
                    $tipo = 'Embudo';
                } elseif (strpos($clLow, 'corona') !== false) {
                    $tipo = 'Corona';
                } elseif (strpos($clLow, 'plato') !== false) {
                    $tipo = 'Plato';
                } elseif (strpos($clLow, 'fondo') !== false) {
                    $tipo = 'Fondo';
                } elseif (strpos($clLow, 'obturador') !== false) {
                    $tipo = 'Obturador';
                } elseif (strpos($clLow, 'molde') !== false) {
                    $tipo = 'Molde';
                } elseif (strpos($clLow, 'bombillo') !== false) {
                    $tipo = 'Bombillo';
                }

                if ($tipo) {
                    LiberacionModeloFundicion::updateOrCreate(
                        [
                            'ot' => $ot,
                            'tipo_modelo' => $tipo
                        ],
                        [
                            'estado' => 'pendiente',
                            'tipo_origen' => 'con_modelo',
                            'fecha_revision' => $fecha ? date('Y-m-d H:i:s', strtotime($fecha)) : now()
                        ]
                    );
                }
            }
        } else {
            // Fallback en caso de que no se manden clases (comportamiento legacy)
            LiberacionModeloFundicion::updateOrCreate(
                ['ot' => $ot],
                [
                    'estado' => 'pendiente',
                    'tipo_origen' => 'con_modelo',
                    'fecha_revision' => $fecha ? date('Y-m-d H:i:s', strtotime($fecha)) : now()
                ]
            );
        }

        // Revisar si ya están todas las clases procesadas para marcar tiene_modelo = true
        // Primero obtenemos todas las clases activas de esta OT
        $baseOt = preg_replace('/_R\d+$/i', '', $ot);
        preg_match('/OT\s*(\d+)/', $baseOt, $matches);
        $otId = isset($matches[1]) ? (int) $matches[1] : 0;

        $otFullRaw = Orden_trabajo::query()->find($otId);
        $clasesFaltantes = 0; // Inicializar antes del bloque condicional para evitar undefined variable
        if ($otFullRaw) {
            $clasesActivas = Clase::query()->where('id_ot', '=', $otFullRaw->id)
                ->pluck('nombre')
                ->toArray();

            $clasesFaltantes = 0;
            foreach ($clasesActivas as $clName) {
                $tipo = null;
                $clLow = strtolower($clName);
                if (strpos($clLow, 'candado obturador') !== false)
                    $tipo = 'Candado obturador';
                elseif (strpos($clLow, 'cabeza de soplo') !== false)
                    $tipo = 'Cabeza de soplo';
                elseif (strpos($clLow, 'embudo') !== false)
                    $tipo = 'Embudo';
                elseif (strpos($clLow, 'corona') !== false)
                    $tipo = 'Corona';
                elseif (strpos($clLow, 'plato') !== false)
                    $tipo = 'Plato';
                elseif (strpos($clLow, 'fondo') !== false)
                    $tipo = 'Fondo';
                elseif (strpos($clLow, 'obturador') !== false)
                    $tipo = 'Obturador';
                elseif (strpos($clLow, 'molde') !== false)
                    $tipo = 'Molde';
                elseif (strpos($clLow, 'bombillo') !== false)
                    $tipo = 'Bombillo';

                if ($tipo) {
                    $hasData = LiberacionModeloFundicion::query()->where('ot', '=', $ot)->where('tipo_modelo', '=', $tipo)->exists();
                    if (!$hasData) {
                        $clasesFaltantes++;
                    }
                }
            }

            if ($clasesFaltantes === 0 && count($clasesActivas) > 0) {
                $history->tiene_modelo = true;
                $history->save();
            }
        } else {
            // Legacy behaviour
            $history->tiene_modelo = true;
            $history->save();
        }

        // Sincronizar confirmación de modelo a Calidad
        $folderName = $this->sanitizePath($this->normalizeOTName($ot));
        $this->syncAlmacenToCalidad($folderName);

        // ── ENVIAR CORREOS ───────────────────────────────────────────────────────
        if ($clasesFaltantes === 0) {
            $otCleaned = preg_replace('/^OT\s*/i', '', $ot);
            $asunto = "Disponibilidad de Modelo Confirmada - OT {$otCleaned}";
            $cuerpo = "
        <div style='font-family: \"Segoe UI\", Helvetica, Arial, sans-serif; line-height: 1.6; color: #334155; max-width: 650px; margin: 0 auto; border: 1px solid #e2e8f0; border-radius: 12px; overflow: hidden; box-shadow: 0 10px 25px rgba(0,0,0,0.05);'>
            <!-- Header con gradiente premium -->
            <div style='background: linear-gradient(135deg, #0a8504, #064e03); color: white; padding: 30px 20px; text-align: center; border-bottom: 4px solid #043d02;'>
                <div style='background: rgba(255,255,255,0.15); display: inline-block; padding: 10px 20px; border-radius: 30px; margin-bottom: 15px;'>
                    <span style='font-size: 0.9em; font-weight: 600; letter-spacing: 1px;'>ALERTA DE ALMACÉN</span>
                </div>
                <h2 style='margin: 0; font-size: 1.8em; font-weight: 800; letter-spacing: -0.5px;'>Confirmación de Modelo Físico</h2>
                <p style='margin: 8px 0 0 0; font-size: 1.1em; opacity: 0.95; font-weight: 500;'>Orden de Trabajo: <strong>{$otCleaned}</strong></p>
            </div>

            <!-- Cuerpo Principal -->
            <div style='padding: 35px 30px; background-color: #ffffff;'>
                <p style='font-size: 1.1em; margin-top: 0;'>Estimado Equipo,</p>

                <p style='font-size: 1.05em;'>El departamento de Almacén ha confirmado que actualmente se <strong>cuenta con el modelo físico requerido</strong> en las instalaciones para dar seguimiento a la Orden de Trabajo <strong>{$otCleaned}</strong>.</p>

                <div style='background-color: #f0fdf4; border: 1px solid #bbf7d0; border-left: 5px solid #22c55e; padding: 18px 20px; margin: 25px 0; border-radius: 6px;'>
                    <h3 style='margin: 0 0 10px 0; color: #166534; font-size: 1.15em; display: flex; align-items: center;'>
                        📄 Documentación de Respaldo
                    </h3>
                    <p style='margin: 0; color: #15803d; font-size: 0.95em;'>Se ha anexado a este correo la documentación correspondiente (como hojas de entrega, remisiones o fotografías) que avalan la recepción y disponibilidad de los herramentales.</p>
                </div>

                <div style='background-color: #f8fafc; border: 1px solid #e2e8f0; padding: 20px; margin: 25px 0; border-radius: 8px;'>
                    <h3 style='margin: 0 0 15px 0; color: #0f172a; font-size: 1.1em; text-transform: uppercase; letter-spacing: 0.5px;'>Siguientes Pasos Requeridos</h3>
                    <ul style='margin: 0; padding-left: 20px; color: #475569; line-height: 1.7;'>
                        <li style='margin-bottom: 8px;'><strong>Para el Departamento de Calidad:</strong> Favor de proceder a realizar la liberación dimensional del modelo ahora que está disponible en planta.</li>
                        <li><strong>Para Proveedores/Producción:</strong> Favor de mantenerse a la espera de la liberación de Calidad para continuar con el flujo correspondiente.</li>
                    </ul>
                </div>

                <p style='margin-bottom: 0; font-size: 0.95em; color: #64748b;'>Agradecemos su pronta atención al seguimiento de esta OT.</p>
            </div>

            <!-- Footer -->
            <div style='background-color: #f1f5f9; padding: 20px; text-align: center; font-size: 0.85em; color: #94a3b8; border-top: 1px solid #e2e8f0;'>
                <p style='margin: 0; font-weight: 600; color: #64748b;'>Sistema Automatizado GIS Saavedra</p>
                <p style='margin: 5px 0 0 0;'>Este es un mensaje generado automáticamente. Por favor, no responda a esta dirección de correo.</p>
            </div>
        </div>";

            $destCalidadStr = !empty($destinatarioCalidad) ? $destinatarioCalidad : env('EMAIL_CALIDAD', 'inspecciontec@grupoindsaavedra.com');
            $destCalidad = array_filter(array_map('trim', explode(',', $destCalidadStr)));

            $destProveedorStr = !empty($destinatario) ? $destinatario : env('EMAIL_PROVEEDOR_MODELOS', 'produccion@ssmetalf.mx,asistenteprod@ssmetalf.mx');
            $destProveedor = array_filter(array_map('trim', explode(',', $destProveedorStr)));

            // Buscar dibujos y ayudas visuales para adjuntar a Calidad
            $calidadAttachments = $attachments;
            // Archivos del servidor seleccionados
            $archivosSeleccionados = $request->input('archivos_seleccionados', []);
            if (is_array($archivosSeleccionados)) {
                $baseOt = preg_replace('/_R\d+$/i', '', $ot);
                $allOtNames = FundicionHistory::where('ot', '=', $baseOt, 'or')
                    ->where('ot', 'LIKE', $baseOt . '_R%', 'or')
                    ->pluck('ot')
                    ->toArray();
                if (!in_array($ot, $allOtNames)) {
                    $allOtNames[] = $ot;
                }

                foreach ($archivosSeleccionados as $archivo) {
                    // Sanitizar para evitar path traversal
                    $archivoSanitized = $this->sanitizeFileNameWithFolder($archivo);

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
                                // Evitar duplicados
                                $yaExiste = false;
                                foreach ($calidadAttachments as $extAtt) {
                                    if ($extAtt['name'] === basename($path)) {
                                        $yaExiste = true;
                                        break;
                                    }
                                }
                                if (!$yaExiste) {
                                    $calidadAttachments[] = [
                                        'path' => storage_path('app/' . $path),
                                        'name' => basename($archivoSanitized),
                                        'mime' => 'application/pdf',
                                        'tipo' => 'dibujo_ayuda'
                                    ];
                                }
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

            if (!empty($destCalidad)) {
                Mail::send([], [], function ($message) use ($destCalidad, $asunto, $cuerpo, $calidadAttachments) {
                    $message->to($destCalidad)->subject($asunto)->html($cuerpo);
                    foreach ($calidadAttachments as $att) {
                        if (!empty($att['path']) && file_exists($att['path'])) {
                            $message->attach($att['path'], ['as' => $att['name'], 'mime' => $att['mime']]);
                        }
                    }
                });
            }

            if (!empty($destProveedor)) {
                Mail::send([], [], function ($message) use ($destProveedor, $asunto, $cuerpo, $attachments) {
                    $message->to($destProveedor)->subject($asunto)->html($cuerpo);
                    foreach ($attachments as $att) {
                        if (!empty($att['path']) && file_exists($att['path'])) {
                            $message->attach($att['path'], ['as' => $att['name'], 'mime' => $att['mime']]);
                        }
                    }
                });
            }
        }

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

        $modelPreOrden = PreOrdenFundicion::where('ot', '=', $otFull, 'and')->where('pdf_filename', 'NOT LIKE', '%Casting%', 'and')->first();
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
                        foreach (['candado obturador', 'cabeza de soplo', 'obturador', 'bombillo', 'embudo', 'corona', 'plato', 'molde', 'fondo'] as $kc) {
                            if (strpos($val, $kc) !== false) {
                                $activeClasses[] = $kc;
                                break;
                            }
                        }
                    }
                }
            }
        }

        $clasesOrig = $ot->clases->map(fn($c) => [
            'id' => $c->id,
            'nombre' => $c->nombre,
            'pedido' => $c->pedido
        ])->values();

        // Obtener TODAS las clases para esta OT y filtrarlas por aprobados en liberación
        $clases = collect($clasesOrig)->filter(function ($c) use ($otFull, $baseOt, $type) {

            $clLow = strtolower($c['nombre']);
            $tipo = null;
            if (strpos($clLow, 'candado obturador') !== false)
                $tipo = 'Candado obturador';
            elseif (strpos($clLow, 'cabeza de soplo') !== false)
                $tipo = 'Cabeza de soplo';
            elseif (strpos($clLow, 'embudo') !== false)
                $tipo = 'Embudo';
            elseif (strpos($clLow, 'corona') !== false)
                $tipo = 'Corona';
            elseif (strpos($clLow, 'plato') !== false)
                $tipo = 'Plato';
            elseif (strpos($clLow, 'fondo') !== false)
                $tipo = 'Fondo';
            elseif (strpos($clLow, 'obturador') !== false)
                $tipo = 'Obturador';
            elseif (strpos($clLow, 'molde') !== false)
                $tipo = 'Molde';
            elseif (strpos($clLow, 'bombillo') !== false)
                $tipo = 'Bombillo';

            if ($tipo) {
                if ($type === 'casting') {
                    // Para casting, solo queremos los APROBADOS en ESTA iteración específica ($otFull)
                    // Así evitamos incluir clases que ya fueron aprobadas en la OT base o iteraciones previas.
                    return LiberacionModeloFundicion::query()
                        ->where('ot', '=', $otFull)
                        ->where('tipo_modelo', '=', $tipo)
                        ->where('estado', '=', 'aprobado')
                        ->exists();
                } else {
                    // Para pre-orden de modelo normal, queremos los que NUNCA han sido aprobados
                    // en ninguna iteración de esta OT.
                    $isAprobado = LiberacionModeloFundicion::query()
                        ->where(function ($q) use ($otFull, $baseOt) {
                            $q->where('ot', 'LIKE', $baseOt . '%');
                        })
                        ->where('tipo_modelo', '=', $tipo)
                        ->where('estado', '=', 'aprobado')
                        ->exists();
                    return !$isAprobado;
                }
            }
            return $type !== 'casting'; // Si no es casting, dejamos los otros. Si es casting, los quitamos
        })->values();

        // Obtener clases vinculadas desde FundicionHistory (Ayudas Visuales asignadas)
        $history = FundicionHistory::where('ot', '=', $otFull, 'and')->first();
        $clasesVinculadas = $history ? ($history->ayudas_config ?? []) : [];
        $clasesVinculadas = collect($clasesVinculadas)->filter(function ($claseNombre) use ($otFull, $baseOt, $type) {

            $clLow = strtolower($claseNombre);
            $tipo = null;
            if (strpos($clLow, 'candado obturador') !== false)
                $tipo = 'Candado obturador';
            elseif (strpos($clLow, 'cabeza de soplo') !== false)
                $tipo = 'Cabeza de soplo';
            elseif (strpos($clLow, 'embudo') !== false)
                $tipo = 'Embudo';
            elseif (strpos($clLow, 'corona') !== false)
                $tipo = 'Corona';
            elseif (strpos($clLow, 'plato') !== false)
                $tipo = 'Plato';
            elseif (strpos($clLow, 'fondo') !== false)
                $tipo = 'Fondo';
            elseif (strpos($clLow, 'obturador') !== false)
                $tipo = 'Obturador';
            elseif (strpos($clLow, 'molde') !== false)
                $tipo = 'Molde';
            elseif (strpos($clLow, 'bombillo') !== false)
                $tipo = 'Bombillo';

            if ($tipo) {
                $isAprobado = LiberacionModeloFundicion::query()
                    ->where(function ($q) use ($baseOt) {
                        $q->where('ot', 'LIKE', $baseOt . '%');
                    })
                    ->where('tipo_modelo', '=', $tipo)
                    ->where('estado', '=', 'aprobado')
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
                $castingPo = $preOrdenes->first(function ($po) {
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
                'success' => true,
                'moldura' => $ot->moldura ? $ot->moldura->nombre : 'Sin moldura',
                'clases' => $clases,
                'clases_vinculadas' => $clasesVinculadas,
                'folio' => $folioStr,
                'pre_ordenes' => $preOrdenes,
                'fecha_entrega' => $fechaEntrega,
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
                    $claseNombre = $fila['clase_nombre'] ?? $fila['clase'] ?? '';
                    if (is_numeric($claseNombre)) {
                        $cObj = collect($clasesOrig)->firstWhere('id', (int) $claseNombre);
                        if ($cObj) {
                            $claseNombre = $cObj['nombre'];
                        }
                    }
                    $clLow = strtolower($claseNombre);
                    $tipo = null;
                    if (strpos($clLow, 'candado obturador') !== false)
                        $tipo = 'Candado obturador';
                    elseif (strpos($clLow, 'cabeza de soplo') !== false)
                        $tipo = 'Cabeza de soplo';
                    elseif (strpos($clLow, 'embudo') !== false)
                        $tipo = 'Embudo';
                    elseif (strpos($clLow, 'corona') !== false)
                        $tipo = 'Corona';
                    elseif (strpos($clLow, 'plato') !== false)
                        $tipo = 'Plato';
                    elseif (strpos($clLow, 'fondo') !== false)
                        $tipo = 'Fondo';
                    elseif (strpos($clLow, 'obturador') !== false)
                        $tipo = 'Obturador';
                    elseif (strpos($clLow, 'molde') !== false)
                        $tipo = 'Molde';
                    elseif (strpos($clLow, 'bombillo') !== false)
                        $tipo = 'Bombillo';

                    if ($tipo) {
                        $isAprobado = LiberacionModeloFundicion::query()
                            ->where(function ($q) use ($baseOt) {
                                $q->where('ot', 'LIKE', $baseOt . '%');
                            })
                            ->where('tipo_modelo', '=', $tipo)
                            ->where('estado', '=', 'aprobado')
                            ->exists();
                        if ($isAprobado) {
                            continue;
                        }
                    }
                    $filasFiltradas[] = $fila;
                }
            }

            $preordenData = [
                'folio' => $preOrdenDB->folio,
                'proveedor' => $preOrdenDB->proveedor,
                'fecha_creacion' => $preOrdenDB->fecha_creacion ? ($preOrdenDB->fecha_creacion instanceof \DateTimeInterface ? $preOrdenDB->fecha_creacion->format('Y-m-d') : substr((string) $preOrdenDB->fecha_creacion, 0, 10)) : null,
                'fecha_entrega' => $preOrdenDB->fecha_entrega ? ($preOrdenDB->fecha_entrega instanceof \DateTimeInterface ? $preOrdenDB->fecha_entrega->format('Y-m-d') : substr((string) $preOrdenDB->fecha_entrega, 0, 10)) : null,
                'moldura' => $preOrdenDB->moldura,
                'observaciones' => $preOrdenDB->observaciones,
                'filas' => $filasFiltradas,
                'version' => $preOrdenDB->version,
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
            'success' => true,
            'moldura' => $ot->moldura ? $ot->moldura->nombre : 'Sin moldura',
            'clases' => $clases,
            'clases_vinculadas' => $clasesVinculadas,
            'folio' => $folioStr,
            'pre_orden_data' => $preordenData,
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

            // VALIDACIÓN ESTRICTA: No generar pre-orden de casting si faltan formatos LDM
            $history = FundicionHistory::where('ot', '=', $otRaw, 'and')->first();
            if (!$history || !$history->casting_pdf_generated) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se puede generar la Pre-orden de Casting. Debe subir los Formatos LDM firmados obligatoriamente.'
                ], 422);
            }

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

            // Si la OT tenía calidad_revision_status = casting_aprobado (correo ya enviado antes),
            // revertirlo a calidad_aprobado para que el usuario pueda enviar el nuevo correo.
            FundicionHistory::where('ot', '=', $otRaw, 'and')
                ->where('calidad_revision_status', '=', 'casting_aprobado', 'and')
                ->update(['calidad_revision_status' => 'calidad_aprobado']);

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
        $esReprocesoRegistro = (bool) preg_match('/_R\d+$/i', $otRaw);

        // VALIDACIÓN ESTRICTA: Para generar una pre-orden de modelo de reproceso, el SCAR debe estar emitido.
        if ($esReprocesoRegistro) {
            $scarExists = ScarModelo::where('ot', '=', $baseOt, 'and')
                ->orWhere('ot', '=', $otRaw, 'or')
                ->exists();
            if (!$scarExists) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se puede generar la Pre-orden de Modelo de Reproceso. Calidad debe emitir y firmar el formato de Rechazo y el SCAR primero.'
                ], 422);
            }
        }
        // Filtrar filas para no guardar ni imprimir las clases ya aprobadas
        $filasFiltradas = [];
        if (!empty($data['filas'])) {
            foreach ($data['filas'] as $fila) {
                $claseNombre = $fila['clase_nombre'] ?? $fila['clase'] ?? '';
                $clLow = strtolower($claseNombre);
                $tipo = null;
                if (strpos($clLow, 'candado obturador') !== false)
                    $tipo = 'Candado obturador';
                elseif (strpos($clLow, 'cabeza de soplo') !== false)
                    $tipo = 'Cabeza de soplo';
                elseif (strpos($clLow, 'embudo') !== false)
                    $tipo = 'Embudo';
                elseif (strpos($clLow, 'corona') !== false)
                    $tipo = 'Corona';
                elseif (strpos($clLow, 'plato') !== false)
                    $tipo = 'Plato';
                elseif (strpos($clLow, 'fondo') !== false)
                    $tipo = 'Fondo';
                elseif (strpos($clLow, 'obturador') !== false)
                    $tipo = 'Obturador';
                elseif (strpos($clLow, 'molde') !== false)
                    $tipo = 'Molde';
                elseif (strpos($clLow, 'bombillo') !== false)
                    $tipo = 'Bombillo';

                if ($tipo) {
                    $isAprobado = LiberacionModeloFundicion::query()
                        ->where(function ($q) use ($otRaw, $baseOt) {
                            $q->where('ot', '=', $otRaw)
                                ->orWhere('ot', '=', $baseOt)
                                ->orWhere('ot', 'LIKE', $baseOt . '_R%');
                        })
                        ->where('tipo_modelo', '=', $tipo)
                        ->where('estado', '=', 'aprobado')
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
                : substr((string) $preOrdenDB->fecha_entrega, 0, 10);
        } else {
            $data['fecha_entrega'] = null;
        }

        // 3. Generar el PDF en orientación horizontal
        ini_set('memory_limit', '2048M');
        $pdf = Pdf::loadView('pdf.pre_orden', [
            'data' => $data,
            'user' => $user
        ])->setPaper('a4', 'landscape');

        // 4. Definir nombre del archivo y ruta de guardado
        $folio = preg_replace('/[^A-Za-z0-9\-]/', '_', $data['folio']);
        $moldura = preg_replace('/[^A-Za-z0-9\-]/', '_', $data['moldura'] ?? '');
        $proveedor = preg_replace('/[^A-Za-z0-9\-]/', '_', $data['proveedor']);
        $fechaStamp = date('d_m_Y_H_i');

        // Extraer solo el número de OT para que el nombre del archivo no exceda el MAX_PATH de Windows (260 chars)
        preg_match('/OT\s*(\d+)/i', $otRaw, $matches);
        $otId = $matches[1] ?? (preg_replace('/[^0-9]/', '', $otRaw) ?: 'SN');

        $fileName = "Pre-Orden_Fundicion-{$folio}_OT_{$otId}_{$fechaStamp}.pdf";

        $folderName = $this->sanitizePath($this->normalizeOTName($otRaw));
        $otPath = self::ALMACEN_DIR . '/' . $folderName . '/preordenes';
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
                'folio' => $data['folio'],
                'proveedor' => $data['proveedor'],
                'fecha_creacion' => $data['fecha_creacion'],
                'moldura' => $data['moldura'] ?? null,
                'observaciones' => $data['observaciones'] ?? null,
                'filas' => $data['filas'],
                'pdf_filename' => $fileName,
                'version' => DB::raw('version + 1'),
                'user_id' => $user ? $user->id : null,
                'user_nombre' => $user ? $user->name : null,
            ]
        );

        // 7. Actualizar flag de pre_orden_sent en historial de Fundicion
        //    y marcar como pendiente de revision por Calidad
        FundicionHistory::where('ot', '=', $otRaw, 'and')->update([
            'pre_orden_sent' => true,
        ]);

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

        // 9. Registrar auditoría de generación de Pre-Orden
        try {
            PreOrdenLog::create([
                'ot' => $otRaw,
                'proveedor' => $data['proveedor'] ?? null,
                'accion' => $existeEnBD ? 'generar' : 'generar',
                'pdf_filename' => $fileName,
                'user_id' => $user ? $user->id : null,
                'user_nombre' => $user ? $user->name : null,
            ]);
        } catch (\Exception $e) {
            Log::warning('Error al registrar log de pre-orden: ' . $e->getMessage());
        }

        // 10. Retornar el PDF para descarga automática en el navegador
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
        ini_set('memory_limit', '2048M');

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
        $otPath = self::ALMACEN_DIR . '/' . $folderName . '/preordenes';
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
                'folio' => $p1Data['folio'],
                'fecha_creacion' => $fechaActual,
                'fecha_entrega' => !empty($p1Data['fecha_entrega']) ? $p1Data['fecha_entrega'] : null,
                'moldura' => $p1Data['moldura'] ?? null,
                'observaciones' => $p1Data['observaciones'] ?? null,
                'filas' => $p1Data['filas'],
                'pdf_filename' => $fileName,
                'version' => DB::raw('version + 1'),
                'is_sent' => false, // Reset al regenerar para desbloquear la tarjeta
                'user_id' => $user ? $user->id : null,
                'user_nombre' => $user ? $user->name : null,
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
                    'folio' => $p2Data['folio'],
                    'fecha_creacion' => $fechaActual,
                    'fecha_entrega' => !empty($p2Data['fecha_entrega']) ? $p2Data['fecha_entrega'] : null,
                    'moldura' => $p2Data['moldura'] ?? null,
                    'observaciones' => $p2Data['observaciones'] ?? null,
                    'filas' => $p2Data['filas'],
                    'pdf_filename' => $fileName,
                    'version' => DB::raw('version + 1'),
                    'is_sent' => false, // Reset al regenerar para desbloquear la tarjeta
                    'user_id' => $user ? $user->id : null,
                    'user_nombre' => $user ? $user->name : null,
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
     * @return \Illuminate\Http\JsonResponse
     */
    public function sendEmailPreOrden(Request $request)
    {
        $this->verificarAcceso();

        $ot = $request->input('ot');
        $destinatario = $request->input('destinatario');
        $destinatarioCalidad = $request->input('destinatario_calidad', '');

        if (empty($ot) || empty($destinatario) || empty($request->input('fecha_entrega'))) {
            return response()->json([
                'success' => false,
                'message' => 'La OT, el Destinatario y la Fecha de Entrega son requeridos.'
            ], 422);
        }

        $destinatariosArray = array_map('trim', explode(',', $destinatario));
        foreach ($destinatariosArray as $email) {
            if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                return response()->json([
                    'success' => false,
                    'message' => "El correo electrónico proporcionado no es válido: $email"
                ], 422);
            }
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
        $query = PreOrdenFundicion::query()->where('ot', $ot);

        if ($request->input('tipo') === 'casting') {
            $query->where('pdf_filename', 'LIKE', '%Casting%');
        } else {
            $query->where('pdf_filename', 'NOT LIKE', '%Casting%');
        }

        $preOrdenIdsRaw = $request->input('pre_orden_ids');
        if (empty($preOrdenIdsRaw) || !is_array($preOrdenIdsRaw)) {
            return response()->json(['success' => false, 'message' => 'Debe seleccionar al menos una pre-orden para enviar.'], 422);
        }

        $preOrdenIds = [];
        foreach ($preOrdenIdsRaw as $idGroup) {
            $parts = explode(',', $idGroup);
            foreach ($parts as $p) {
                if (trim($p) !== '') {
                    $preOrdenIds[] = trim($p);
                }
            }
        }

        $query->whereIn('id', $preOrdenIds);

        $preOrdenes = $query->get();

        if ($preOrdenes->isEmpty()) {
            return response()->json(['success' => false, 'message' => 'No se encontraron pre-órdenes válidas para enviar.'], 404);
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
                    $fechaValStr = $preOrden->fecha_creacion ? ($preOrden->fecha_creacion instanceof \DateTimeInterface ? $preOrden->fecha_creacion->format('Y-m-d') : substr((string) $preOrden->fecha_creacion, 0, 10)) : null;
                    $fechaEntregaValStr = $preOrden->fecha_entrega ? ($preOrden->fecha_entrega instanceof \DateTimeInterface ? $preOrden->fecha_entrega->format('Y-m-d') : substr((string) $preOrden->fecha_entrega, 0, 10)) : null;

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
                    $fechaValStr = $preOrden->fecha_creacion ? ($preOrden->fecha_creacion instanceof \DateTimeInterface ? $preOrden->fecha_creacion->format('Y-m-d') : substr((string) $preOrden->fecha_creacion, 0, 10)) : null;
                    $fechaEntregaValStr = $preOrden->fecha_entrega ? ($preOrden->fecha_entrega instanceof \DateTimeInterface ? $preOrden->fecha_entrega->format('Y-m-d') : substr((string) $preOrden->fecha_entrega, 0, 10)) : null;

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
                    $otPath = self::ALMACEN_DIR . '/' . $folderName . '/Documentos_Aprobados/preordenes';
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
                $candidates[] = self::ALMACEN_DIR . '/' . $folderName . '/Documentos_Aprobados/preordenes/' . $preOrden->pdf_filename;
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
                'mime' => 'application/pdf',
                'tipo' => 'preorden'
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
                                'mime' => 'application/pdf',
                                'tipo' => 'dibujo_ayuda' // Se considera Dibujo/Ayuda por venir del panel superior
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

            $clasesNombres = [];
            foreach ($preOrdenes as $po) {
                $filas = is_array($po->filas) ? $po->filas : json_decode($po->filas, true);
                if (is_array($filas)) {
                    foreach ($filas as $fila) {
                        $claseNombre = $fila['clase_nombre'] ?? $fila['clase'] ?? $fila['descripcion'] ?? '';
                        $clLow = strtolower($claseNombre);
                        if (strpos($clLow, 'candado obturador') !== false)
                            $clasesNombres[] = 'Candado obturador';
                        elseif (strpos($clLow, 'cabeza de soplo') !== false)
                            $clasesNombres[] = 'Cabeza de soplo';
                        elseif (strpos($clLow, 'embudo') !== false)
                            $clasesNombres[] = 'Embudo';
                        elseif (strpos($clLow, 'corona') !== false)
                            $clasesNombres[] = 'Corona';
                        elseif (strpos($clLow, 'plato') !== false)
                            $clasesNombres[] = 'Plato';
                        elseif (strpos($clLow, 'candado obturador') !== false)
                            $clasesNombres[] = 'Candado obturador';
                        elseif (strpos($clLow, 'cabeza de soplo') !== false)
                            $clasesNombres[] = 'Cabeza de soplo';
                        elseif (strpos($clLow, 'embudo') !== false)
                            $clasesNombres[] = 'Embudo';
                        elseif (strpos($clLow, 'corona') !== false)
                            $clasesNombres[] = 'Corona';
                        elseif (strpos($clLow, 'plato') !== false)
                            $clasesNombres[] = 'Plato';
                        elseif (strpos($clLow, 'fondo') !== false)
                            $clasesNombres[] = 'Fondo';
                        elseif (strpos($clLow, 'obturador') !== false)
                            $clasesNombres[] = 'Obturador';
                        elseif (strpos($clLow, 'molde') !== false)
                            $clasesNombres[] = 'Molde';
                        elseif (strpos($clLow, 'bombillo') !== false)
                            $clasesNombres[] = 'Bombillo';
                    }
                }
            }
            $clasesNombres = array_unique($clasesNombres);
            $clasesSuffixStr = count($clasesNombres) > 0 ? '_' . implode('_', $clasesNombres) : '';

            if (!Storage::disk('local')->exists($destDir)) {
                Storage::disk('local')->makeDirectory($destDir);
            }

            foreach ($filesArray as $file) {
                $ext = $file->getClientOriginalExtension();
                $safeName = preg_replace('/[^a-zA-Z0-9_\-\.\s]/', '_', pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME));
                $name = 'Escaneado_Fundicion' . $clasesSuffixStr . '-' . trim($safeName, '_.') . ($ext ? '.' . $ext : '');
                $savedPath = $file->storeAs($destDir, $name, 'local');
                $attachments[] = [
                    'path' => storage_path('app/' . $savedPath),
                    'name' => $name,
                    'mime' => $file->getClientMimeType(),
                    'tipo' => 'escaneado'
                ];
            }
        }

        // Enviar Email
        try {
            // ── AUTO-ADJUNTOS: Si es re-proceso (_R1, _R2...), adjuntar docs de toda la historia (Omitir si es pre-orden de casting)
            if (!$isCastingPo && preg_match('/^(.+?)(_[rR](\d+))$/', $ot, $match)) {
                $otBase = $match[1];
                $currentIteration = (int) $match[3];

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
                        if (!Storage::disk('local')->exists($carpeta))
                            continue;

                        $archivos = Storage::disk('local')->allFiles($carpeta);
                        foreach ($archivos as $archivoPath) {
                            $ext = strtolower(pathinfo($archivoPath, PATHINFO_EXTENSION));
                            if (!in_array($ext, $extPermitidas, true))
                                continue;

                            $absPath = storage_path('app/' . $archivoPath);
                            // Evitar duplicados
                            if (collect($attachments)->contains(fn($a) => $a['path'] === $absPath))
                                continue;

                            // Ignorar dibujos que estén en subcarpetas para la ruta raíz
                            if ($etiqueta === 'Dibujo' && str_contains($archivoPath, '/ayudas_visuales/'))
                                continue;
                            if ($etiqueta === 'Dibujo' && str_contains($archivoPath, '/preordenes/'))
                                continue;

                            $iterationLabel = $i === 0 ? 'OT_Base' : 'R' . $i;
                            $tipoCat = ($etiqueta === 'Dibujo' || $etiqueta === 'AyudaVisual') ? 'dibujo_ayuda' : 'otro';

                            $attachments[] = [
                                'path' => $absPath,
                                'name' => '[' . $etiqueta . ' ' . $iterationLabel . '] ' . basename($archivoPath),
                                'mime' => $ext === 'pdf' ? 'application/pdf' : 'image/' . $ext,
                                'tipo' => $tipoCat
                            ];
                        }
                    }
                }
            }

            if ($isCastingPo) {
                // Determine if Jacarandas is the provider
                $proveedores = $preOrdenes->pluck('proveedor')->map(function ($p) {
                    return strtolower($p); })->toArray();
                $isJacarandas = false;
                foreach ($proveedores as $p) {
                    if (strpos($p, 'jacarandas') !== false) {
                        $isJacarandas = true;
                        break;
                    }
                }
                $defaultEmail = $isJacarandas ? env('EMAIL_PRODUCCION_JACARANDAS', 'ventas_jacarandas@prodigy.net.mx,requisicionestec@grupoindsaavedra.com') : env('EMAIL_PRODUCCION_SS', 'produccion@ssmetalf.mx,laboratorio@ssmetalf.mx');

                // Para Casting: enviar a Proveedores de Casting y CC General
                $destinosStr = !empty($destinatario) ? $destinatario : $defaultEmail . ',' .
                    env('EMAIL_CC_GENERAL', 'alejandross@grupoindsaavedra.com,analilia@grupoindsaavedra.com,blanca@grupoindsaavedra.com,juanss@grupoindsaavedra.com,abraham@grupoindsaavedra.com,inspecciontec@grupoindsaavedra.com,requisicionestec@grupoindsaavedra.com,auxadmtec@grupoindsaavedra.com,producciontec@grupoindsaavedra.com');
                $destinatarios = array_filter(array_map('trim', explode(',', $destinosStr)));

                Mail::send([], [], function ($message) use ($destinatarios, $asunto, $cuerpo, $attachments) {
                    $message->to($destinatarios)
                        ->subject($asunto)
                        ->html($cuerpo);

                    foreach ($attachments as $att) {
                        if (!empty($att['path']) && file_exists($att['path'])) {
                            $message->attach($att['path'], [
                                'as' => $att['name'],
                                'mime' => $att['mime']
                            ]);
                        }
                    }
                });
            } else {
                // Para Modelo: Enviar correo completo a Calidad, y correo filtrado a Proveedores
                $destCalidadStr = !empty($destinatarioCalidad) ? $destinatarioCalidad : env('EMAIL_CALIDAD', 'inspecciontec@grupoindsaavedra.com');
                $destCalidad = array_filter(array_map('trim', explode(',', $destCalidadStr)));

                $destProveedorStr = !empty($destinatario) ? $destinatario : env('EMAIL_PROVEEDOR_MODELOS', 'produccion@ssmetalf.mx,asistenteprod@ssmetalf.mx');
                $destProveedor = array_filter(array_map('trim', explode(',', $destProveedorStr)));

                // Enviar a Calidad con TODOS los adjuntos
                if (!empty($destCalidad)) {
                    Mail::send([], [], function ($message) use ($destCalidad, $asunto, $cuerpo, $attachments) {
                        $message->to($destCalidad)
                            ->subject($asunto)
                            ->html($cuerpo);

                        foreach ($attachments as $att) {
                            if (!empty($att['path']) && file_exists($att['path'])) {
                                $message->attach($att['path'], [
                                    'as' => $att['name'],
                                    'mime' => $att['mime']
                                ]);
                            }
                        }
                    });
                }

                // Filtrar adjuntos para Proveedor: Omitir 'dibujo_ayuda'
                $attachmentsFiltrados = array_filter($attachments, function ($att) {
                    return $att['tipo'] !== 'dibujo_ayuda';
                });

                // Enviar a Proveedor
                if (!empty($destProveedor)) {
                    Mail::send([], [], function ($message) use ($destProveedor, $asunto, $cuerpo, $attachmentsFiltrados) {
                        $message->to($destProveedor)
                            ->subject($asunto)
                            ->html($cuerpo);

                        foreach ($attachmentsFiltrados as $att) {
                            if (!empty($att['path']) && file_exists($att['path'])) {
                                $message->attach($att['path'], [
                                    'as' => $att['name'],
                                    'mime' => $att['mime']
                                ]);
                            }
                        }
                    });
                }
            }

            // Sincronizar carpeta completa de Almacén a Calidad
            $this->syncAlmacenToCalidad($folderName);

            // Actualizar flag de correo de pre-orden enviado en historial
            $updateData = ['pre_orden_email_sent' => true];
            // Si es una pre-orden de casting, marcamos el estatus como casting_aprobado
            // independientemente del perfil del usuario (Almacén o Calidad pueden enviarla)
            if ($isCastingPo) {
                $updateData['calidad_revision_status'] = 'casting_aprobado';
            }

            FundicionHistory::where('ot', '=', $ot, 'and')->update($updateData);

            // Registrar auditoría de envío de alerta de Pre-Orden
            try {
                $sendUser = Auth::user();
                PreOrdenLog::create([
                    'ot' => $ot,
                    'proveedor' => $preOrdenes->pluck('proveedor')->unique()->implode(', '),
                    'accion' => 'enviar_alerta',
                    'pdf_filename' => $firstPo->pdf_filename ?? null,
                    'user_id' => $sendUser ? $sendUser->id : null,
                    'user_nombre' => $sendUser ? $sendUser->name : null,
                ]);
            } catch (\Exception $logEx) {
                Log::warning('Error al registrar log de envío pre-orden: ' . $logEx->getMessage());
            }

            foreach ($preOrdenes as $po) {
                $po->is_sent = true;
                $po->save();

                // Crear o actualizar registro de liberacion con origen pre_orden para cada clase confirmada enviada
                if (!empty($po->filas)) {
                    foreach ($po->filas as $fila) {
                        $claseNombre = $fila['clase'] ?? $fila['clase_nombre'] ?? '';
                        $clLow = strtolower($claseNombre);
                        $tipo = null;
                        if (strpos($clLow, 'candado obturador') !== false)
                            $tipo = 'Candado obturador';
                        elseif (strpos($clLow, 'cabeza de soplo') !== false)
                            $tipo = 'Cabeza de soplo';
                        elseif (strpos($clLow, 'embudo') !== false)
                            $tipo = 'Embudo';
                        elseif (strpos($clLow, 'corona') !== false)
                            $tipo = 'Corona';
                        elseif (strpos($clLow, 'plato') !== false)
                            $tipo = 'Plato';
                        elseif (strpos($clLow, 'fondo') !== false)
                            $tipo = 'Fondo';
                        elseif (strpos($clLow, 'obturador') !== false)
                            $tipo = 'Obturador';
                        elseif (strpos($clLow, 'molde') !== false)
                            $tipo = 'Molde';
                        elseif (strpos($clLow, 'bombillo') !== false)
                            $tipo = 'Bombillo';

                        if ($tipo) {
                            LiberacionModeloFundicion::updateOrCreate(
                                [
                                    'ot' => $ot,
                                    'tipo_modelo' => $tipo
                                ],
                                [
                                    'estado' => 'pendiente',
                                    'tipo_origen' => 'pre_orden',
                                    'fecha_revision' => now()
                                ]
                            );
                        }
                    }
                } else {
                    LiberacionModeloFundicion::updateOrCreate(
                        ['ot' => $ot],
                        ['estado' => 'pendiente', 'tipo_origen' => 'pre_orden', 'fecha_revision' => now()]
                    );
                }
            }

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
            'pre_orden_sent' => false,
            'pre_orden_email_sent' => false,
            'tiene_modelo' => false,
            'calidad_revision_status' => null
        ]);

        // Cambiar el estado de las liberaciones rechazadas a historico_rechazado
        $liberacionesRechazadas = LiberacionModeloFundicion::where('ot', '=', $ot, 'and')
            ->where('estado', '=', 'rechazado', 'and')
            ->get();
        foreach ($liberacionesRechazadas as $libRechazada) {
            $libRechazada->update(['estado' => 'historico_rechazado']);
        }

        // Registrar auditoría de confirmación de rechazo
        try {
            $rechazoUser = Auth::user();
            foreach ($liberacionesRechazadas as $libRechazada) {
                RechazoLog::create([
                    'ot' => $ot,
                    'tipo_modelo' => $libRechazada->tipo_modelo,
                    'accion' => 'confirmar_recepcion',
                    'pdf_filename' => $libRechazada->pdf_filename,
                    'motivo_rechazo' => $libRechazada->motivo_rechazo,
                    'user_id' => $rechazoUser ? $rechazoUser->id : null,
                    'user_nombre' => $rechazoUser ? $rechazoUser->name : null,
                ]);
            }
        } catch (\Exception $logEx) {
            Log::warning('Error al registrar log de rechazo: ' . $logEx->getMessage());
        }

        return response()->json([
            'success' => true,
            'message' => 'Recepción confirmada y ciclo reiniciado con éxito.'
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
     * Sincroniza la carpeta completa de la OT desde ALMACEN_FUNDICION hacia CALIDAD_FUNDICION.
     */
    private function syncAlmacenToCalidad(string $folderName): void
    {
        $almacenDir = self::ALMACEN_DIR . '/' . $folderName;
        $calidadDir = self::CALIDAD_DIR . '/' . $folderName;

        if (!Storage::disk('local')->exists($almacenDir)) {
            return;
        }

        $allAlmacenFiles = Storage::disk('local')->allFiles($almacenDir);

        foreach ($allAlmacenFiles as $srcFile) {
            $srcNorm = str_replace('\\', '/', $srcFile);
            $almacenDirNorm = str_replace('\\', '/', $almacenDir);
            $relPath = ltrim(substr($srcNorm, strlen($almacenDirNorm)), '/');

            $targetPath = $calidadDir . '/' . $relPath;
            $targetDir = dirname($targetPath);

            if (!Storage::disk('local')->exists($targetDir)) {
                Storage::disk('local')->makeDirectory($targetDir);
            }

            Storage::disk('local')->put($targetPath, Storage::disk('local')->get($srcFile));
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
            self::CALIDAD_DIR . '/' . $folderName . '/' . FundicionPaths::FDLDM . '/' . FundicionPaths::ESCANEADOS,
            self::CALIDAD_DIR . '/' . $folderName . '/' . FundicionPaths::FDRDM . '/' . FundicionPaths::ESCANEADOS,
            self::CALIDAD_DIR . '/' . $folderName . '/' . FundicionPaths::SCAR . '/' . FundicionPaths::ESCANEADOS,
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
        $otPath = self::ALMACEN_DIR . '/' . $folderName . '/' . FundicionPaths::FDLDM;

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
                $file->storeAs($docRechazadosPathOriginal . '/' . FundicionPaths::FDRDM_SUBFOLDER, $filename, 'local');
            } elseif (str_starts_with($key, 'scar_')) {
                $clase = strtoupper(str_replace('scar_', '', $key));
                $filename = "SCAR_{$clase}_{$folderNameOriginal}.{$ext}";
                $file->storeAs($docRechazadosPathOriginal . '/' . FundicionPaths::SCAR_SUBFOLDER, $filename, 'local');
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
            $newHistory->ayudas_config = $clases; // Sólo incluir las clases explícitamente rechazadas para esta iteración
            $newHistory->almacen_archivos = $historyOriginal->almacen_archivos;
        }
        $newHistory->save();

        $oldPreOrdenes = PreOrdenFundicion::where('ot', '=', $otRaw, 'and')->get();
        $generatedPdfUrl = null;

        foreach ($oldPreOrdenes as $oldPo) {
            $oldFilas = is_string($oldPo->filas) ? json_decode((string) $oldPo->filas, true) : $oldPo->filas;
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

                ini_set('memory_limit', '2048M');
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
                $otPathNew = self::ALMACEN_DIR . '/' . $folderNameNew . '/Documentos_Aprobados/preordenes';
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
                    'archivo' => 'Documentos_Aprobados/preordenes/' . $fileName,
                    'tipo' => 'otro',
                    'origin' => 'aprobado'
                ]);

                // Copy original drawings and ayudas visuales to the new OT folder recursively
                // Under the following rules for reprocesos:
                // 1. Drawings and visual aids: only copy for the rejected classes.
                // 2. Rejected files: only copy for the rejected classes.
                // 3. Approved files: only copy the first pre-order (filename starts with Pre-Orden_Fundicion-).
                $originalBaseDir = self::ALMACEN_DIR . '/' . $folderNameOriginal;
                $newBaseDir = self::ALMACEN_DIR . '/' . $folderNameNew;
                $originalBaseDirNorm = str_replace('\\', '/', $originalBaseDir);
                if (Storage::disk('local')->exists($originalBaseDir)) {
                    $allFiles = Storage::disk('local')->allFiles($originalBaseDir);
                    foreach ($allFiles as $file) {
                        $fileNorm = str_replace('\\', '/', $file);
                        $relPath = str_replace($originalBaseDirNorm . '/', '', $fileNorm);
                        $pathLower = strtolower($relPath);
                        $filename = basename($pathLower);

                        $shouldCopy = false;

                        if (str_contains($pathLower, 'documentos_aprobados') || str_contains($pathLower, 'preordenes')) {
                            // Only copy the model pre-order (Pre-Orden_Fundicion-)
                            if (str_starts_with($filename, 'pre-orden_fundicion-')) {
                                $shouldCopy = true;
                            }
                        } else {
                            // For drawings, visual aids, and rejected files: check if they belong to a rejected class
                            $belongsToRejectedClass = false;
                            foreach ($clases as $clase) {
                                $claseLower = strtolower($clase);

                                // Check if the class is a folder segment in the path
                                $segments = explode('/', $pathLower);
                                if (in_array($claseLower, $segments)) {
                                    $belongsToRejectedClass = true;
                                    break;
                                }

                                // Check if class name is a segment in the filename (e.g. "Rechazo_BOMBILLO_...")
                                $normFilename = preg_replace('/[^a-z0-9]/', '_', $filename);
                                $fnSegments = explode('_', $normFilename);
                                if (in_array($claseLower, $fnSegments)) {
                                    $belongsToRejectedClass = true;
                                    break;
                                }
                            }

                            if ($belongsToRejectedClass) {
                                $shouldCopy = true;
                            }
                        }

                        if ($shouldCopy) {
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

    public function getPendingPreOrdenes(Request $request)
    {
        try {
            $ot = $request->input('ot');
            $tipo = $request->input('tipo', 'modelo');

            if (!$ot) {
                return response()->json(['success' => false, 'message' => 'Falta OT']);
            }

            // Buscar las pre-órdenes que NO han sido enviadas (is_sent = 0)
            $pending = PreOrdenFundicion::where('ot', '=', $ot, 'and')
                ->where('is_sent', '=', 0, 'and')
                ->get();

            $pendingData = [];
            $grouped = $pending->groupBy('pdf_filename');
            foreach ($grouped as $pdf => $group) {
                $first = $group->first();
                $allClases = [];
                $ids = [];
                foreach ($group as $po) {
                    $ids[] = $po->id;
                    $filas = is_string($po->filas) ? json_decode($po->filas, true) : $po->filas;
                    if (is_array($filas)) {
                        foreach ($filas as $f) {
                            $c = $f['clase_nombre'] ?? $f['clase'] ?? 'Desconocida';
                            $allClases[] = trim($c);
                        }
                    }
                }
                $clasesStr = empty($allClases) ? 'Sin clases' : implode(', ', array_unique(array_filter($allClases)));

                $pendingData[] = [
                    'id' => implode(',', $ids),
                    'clases_str' => $clasesStr,
                    'pdf_filename' => $first->pdf_filename,
                    'proveedor' => $first->proveedor,
                    'fecha_creacion' => \Carbon\Carbon::parse($first->created_at)->format('d/m/Y H:i'),
                ];
            }

            return response()->json([
                'success' => true,
                'pending' => $pendingData
            ]);
        } catch (\Exception $e) {
            \Log::error('Error en getPendingPreOrdenes: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error interno: ' . $e->getMessage()]);
        }
    }

    public function getPendingChangesComparison(Request $request)
    {
        $this->verificarAcceso();
        $ot = $request->input('ot');
        if (!$ot)
            return response()->json(['success' => false, 'message' => 'Falta OT']);

        $history = FundicionHistory::where('ot', '=', $ot, 'and')->first();
        if (!$history)
            return response()->json(['success' => false, 'message' => 'No se encontró historial para la OT']);

        $pending = is_array($history->pending_almacen_changes) ? $history->pending_almacen_changes : [];
        if (empty($pending)) {
            return response()->json(['success' => true, 'has_pending' => false]);
        }

        $folderName = $this->sanitizePath($this->normalizeOTName($ot));
        $comparison = [];
        $isOverallAddition = true;

        $isPreorder = function (string $filePath) {
            $lower = strtolower(basename($filePath));
            $lowerPath = strtolower(str_replace('\\', '/', $filePath));
            return str_contains($lower, 'pre-orden') ||
                   str_contains($lower, 'preorden') ||
                   str_contains($lowerPath, '/preordenes/');
        };

        $isAffectedDoc = function (string $filePath) {
            $lower = strtolower(basename($filePath));
            $lowerPath = strtolower(str_replace('\\', '/', $filePath));
            return str_contains($lower, 'confirmacion') ||
                   str_contains($lower, 'escaneado') ||
                   str_contains($lower, 'f-ccl-ldm') ||
                   str_contains($lower, 'scar') ||
                   str_contains($lowerPath, '/ayudas_visuales/') ||
                   str_contains($lowerPath, '/documentos_aprobados/') ||
                   str_contains($lowerPath, '/documentos_rechazados/');
        };

        // Para cada clase con cambios, listamos sus dibujos actuales en Almacén (Viejos) y en Ingeniería (Nuevos)
        foreach ($pending as $clase) {
            $claseComparison = [
                'clase' => $clase,
                'viejos' => [],
                'nuevos' => [],
                'agregados' => [],
                'afectados' => [],
                'es_adicion' => false
            ];

            $viejosList = [];
            $afectadosList = [];

            // Escanear todos los archivos en la carpeta de la clase en Almacén
            $claseAlmacenDir = self::ALMACEN_DIR . '/' . $folderName . '/' . $clase;
            $resolvedClaseAlmacenDir = $this->resolveCaseInsensitivePath($claseAlmacenDir);
            
            if ($resolvedClaseAlmacenDir && Storage::disk('local')->exists($resolvedClaseAlmacenDir)) {
                $allAlmacenFiles = Storage::disk('local')->allFiles($resolvedClaseAlmacenDir);
                foreach ($allAlmacenFiles as $f) {
                    if (strtolower(pathinfo($f, PATHINFO_EXTENSION)) === 'pdf') {
                        // Excluir pre-órdenes de fundición de cualquier listado ya que no se eliminan al reiniciar
                        if ($isPreorder($f)) {
                            continue;
                        }

                        $filename = basename($f);
                        
                        // Determinar ruta relativa limpia
                        $fNorm = str_replace('\\', '/', $f);
                        $dirNorm = str_replace('\\', '/', $resolvedClaseAlmacenDir);
                        $relSubPath = ltrim(substr($fNorm, strlen($dirNorm)), '/');
                        
                        $item = [
                            'nombre' => $filename,
                            'url' => route('almacen.fundicion.serve', [
                                'ot' => $ot,
                                'archivo' => $clase . '/' . $relSubPath,
                                'tipo' => str_contains(strtolower($f), '/ayudas_visuales/') ? 'ayuda' : 'dibujo'
                            ])
                        ];

                        if ($isAffectedDoc($f)) {
                            // Documento de proceso afectado (Ayudas visuales, LDM, SCAR, confirmaciones)
                            $afectadosList[] = $item;
                        } else {
                            // Dibujo real actualmente en Almacén
                            $viejosList[] = $item;
                        }
                    }
                }
            }

            $nuevosList = [];
            // Nuevos (Ingeniería)
            $ingenieriaDir = \App\Http\Controllers\DibujosFundicionPdfController::BASE_DIR . '/' . $folderName . '/' . $clase;
            $resolvedIng = $this->resolveCaseInsensitivePath($ingenieriaDir);
            if ($resolvedIng && Storage::disk('local')->exists($resolvedIng)) {
                $files = Storage::disk('local')->allFiles($resolvedIng);
                foreach ($files as $f) {
                    if (strtolower(pathinfo($f, PATHINFO_EXTENSION)) === 'pdf') {
                        if ($isPreorder($f) || $isAffectedDoc($f)) {
                            continue;
                        }
                        $filename = basename($f);
                        $fNorm = str_replace('\\', '/', $f);
                        $dirNorm = str_replace('\\', '/', $resolvedIng);
                        $relSubPath = ltrim(substr($fNorm, strlen($dirNorm)), '/');

                        $item = [
                            'nombre' => $filename,
                            'url' => route('fundicion.serve', ['ot' => $ot, 'clase' => $clase, 'archivo' => $relSubPath])
                        ];
                        $nuevosList[] = $item;
                    }
                }
            }

            // Deduplicar listas por nombre de archivo
            $viejos = collect($viejosList)->unique('nombre')->values()->all();
            $nuevos = collect($nuevosList)->unique('nombre')->values()->all();
            $afectados = collect($afectadosList)->unique('nombre')->values()->all();

            $viejosNames = array_map(fn($v) => mb_strtolower($v['nombre'], 'UTF-8'), $viejos);
            $nuevosNames = array_map(fn($n) => mb_strtolower($n['nombre'], 'UTF-8'), $nuevos);

            $agregados = [];
            foreach ($nuevos as $nItem) {
                if (!in_array(mb_strtolower($nItem['nombre'], 'UTF-8'), $viejosNames, true)) {
                    $agregados[] = $nItem;
                }
            }

            $isAdicion = false;
            if (!empty($nuevos)) {
                if (empty($viejos)) {
                    $isAdicion = true;
                } else {
                    $missingInNuevos = array_diff($viejosNames, $nuevosNames);
                    if (empty($missingInNuevos) && !empty($agregados)) {
                        $isAdicion = true;
                    }
                }
            }

            if (!$isAdicion) {
                $isOverallAddition = false;
            }

            $claseComparison['viejos'] = $viejos;
            $claseComparison['nuevos'] = $nuevos;
            $claseComparison['agregados'] = $agregados;
            $claseComparison['afectados'] = $afectados;
            $claseComparison['es_adicion'] = $isAdicion;

            $comparison[] = $claseComparison;
        }

        $allClassesInOt = $history ? ($history->ayudas_config ?? []) : [];
        $totalClasesOt = count($allClassesInOt);
        $affectedCount = count($comparison);
        $esTotal = ($affectedCount >= $totalClasesOt && $totalClasesOt > 0);

        return response()->json([
            'success' => true,
            'has_pending' => true,
            'tipo_cambio' => ($isOverallAddition && count($comparison) > 0) ? 'adicion' : 'reemplazo',
            'es_total' => $esTotal,
            'total_clases_ot' => $totalClasesOt,
            'affected_clases_count' => $affectedCount,
            'comparison' => $comparison
        ]);
    }

    public function resolvePendingChanges(Request $request)
    {
        $this->verificarAcceso();
        $ot = $this->sanitizePath($this->normalizeOTName($request->input('ot')));
        $action = $request->input('action'); // 'reiniciar_completo', 'reiniciar_parcial', 'reiniciar', 'mantener'

        if (!$ot || !in_array($action, ['reiniciar_completo', 'reiniciar_parcial', 'reiniciar', 'mantener'])) {
            return response()->json(['success' => false, 'message' => 'Parámetros inválidos']);
        }

        $history = FundicionHistory::where('ot', '=', $ot, 'and')->first();
        if (!$history)
            return response()->json(['success' => false, 'message' => 'No se encontró historial para la OT']);

        $pending = is_array($history->pending_almacen_changes) ? $history->pending_almacen_changes : [];
        if (empty($pending)) {
            return response()->json(['success' => true, 'message' => 'No hay cambios pendientes']);
        }

        $allClassesInOt = $history->ayudas_config ?? [];
        $totalClasesOt = count($allClassesInOt);
        $affectedCount = count($pending);
        $esTotal = ($affectedCount >= $totalClasesOt && $totalClasesOt > 0);

        if ($action === 'reiniciar_completo' || ($action === 'reiniciar' && $esTotal)) {
            // Reiniciar proceso completo de TODA la OT
            $history->pending_almacen_changes = null;
            $history->save();

            \App\Http\Controllers\DibujosFundicionPdfController::copyToAlmacen($ot, true);
        } else if ($action === 'reiniciar_parcial' || $action === 'reiniciar') {
            // Reiniciar proceso únicamente para las clases afectadas
            $activeClasses = array_diff($allClassesInOt, $pending);

            foreach ($pending as $clase) {
                // 1. Limpiar veredictos de Calidad y SCAR de la clase afectada
                LiberacionModeloFundicion::where('ot', '=', $ot, 'and')
                    ->where('tipo_modelo', '=', $clase, 'and')
                    ->delete();

                ScarModelo::where('ot', '=', $ot, 'and')
                    ->where('tipo_modelo', '=', $clase, 'and')
                    ->delete();

                // 2. Eliminar PDFs de LDM y SCAR en public/liberaciones_pdf para la clase afectada
                $liberacionesPath = storage_path('app/public/liberaciones_pdf');
                $claseNorm = strtolower(trim($clase));
                $otSanitizada = preg_replace('/[^\w\s\-]/', '', $ot);
                $otSanitizada = preg_replace('/[\s]+/', '_', trim($otSanitizada));

                if (file_exists($liberacionesPath)) {
                    $patterns = [
                        "{$liberacionesPath}/*{$claseNorm}*{$otSanitizada}*.pdf",
                        "{$liberacionesPath}/*{$otSanitizada}*{$claseNorm}*.pdf",
                        "{$liberacionesPath}/F-CCL-SCAR_*{$claseNorm}*.pdf",
                        "{$liberacionesPath}/F-CCL-LDM_*{$claseNorm}*.pdf",
                    ];
                    foreach ($patterns as $p) {
                        foreach (glob($p) ?: [] as $f) {
                            if (file_exists($f)) {
                                @unlink($f);
                            }
                        }
                    }
                }

                // 3. Eliminar documentos aprobados y rechazados de la clase afectada en Storage
                $classSubFolder = strtolower(preg_replace('/[^a-zA-Z0-9_\-]/', '_', trim($clase)));
                $baseRoots = [
                    self::ALMACEN_DIR,
                    'DOCUMENTACION_GIS/ALMACEN_FUNDICION',
                    'DOCUMENTACION_GIS/CALIDAD_FUNDICION',
                    'DOCUMENTACION_GIS/Fundicion_Calidad',
                ];
                $roots = [];
                $baseOtStr = preg_replace('/_R\d+$/i', '', $ot); // Obtener OT base por si acaso
                foreach ($baseRoots as $br) {
                    if (Storage::disk('local')->exists($br)) {
                        $dirs = Storage::disk('local')->directories($br);
                        foreach ($dirs as $d) {
                            if (str_starts_with(basename($d), $baseOtStr)) {
                                $roots[] = $d;
                            }
                        }
                    }
                }
                foreach ($roots as $r) {
                    if (Storage::disk('local')->exists($r)) {
                        $subFoldersToClean = [
                            $r . '/Documentos_Aprobados/' . $classSubFolder,
                            $r . '/Documentos_Rechazados/' . $classSubFolder,
                            $r . '/Documentos_Rechazados/SCAR/' . $classSubFolder,
                        ];
                        foreach ($subFoldersToClean as $sfc) {
                            if (Storage::disk('local')->exists($sfc)) {
                                Storage::disk('local')->deleteDirectory($sfc);
                            }
                        }
                        // Borrar archivos directos que contengan el nombre de la clase en carpetas de rechazados/aprobados
                        foreach (['/Documentos_Rechazados', '/Documentos_Aprobados', '/ayudas_visuales/preordenes/documentos_aprobados'] as $sub) {
                            $targetSub = $r . $sub;
                            if (Storage::disk('local')->exists($targetSub)) {
                                foreach (Storage::disk('local')->files($targetSub) as $f) {
                                    if (str_contains(strtolower(basename($f)), $claseNorm)) {
                                        Storage::disk('local')->delete($f);
                                    }
                                }
                            }
                        }
                    }
                }
            }

            // 4. Actualizar hashes de clases enviadas y limpiar cambios pendientes
            $history->pending_almacen_changes = null;
            $enviadas = is_array($history->clases_enviadas) ? $history->clases_enviadas : [];
            foreach ($pending as $clase) {
                $newHash = \App\Http\Controllers\DibujosFundicionPdfController::computeClassHash($ot, $clase);
                if ($newHash !== "") {
                    $enviadas[$clase] = $newHash;
                }
            }
            $history->clases_enviadas = $enviadas;
            $history->save();

            \App\Http\Controllers\DibujosFundicionPdfController::copyToAlmacen($ot, false);
        } else {
            // Solo reemplazar archivos manteniendo el avance del proceso
            $history->pending_almacen_changes = null;

            $enviadas = is_array($history->clases_enviadas) ? $history->clases_enviadas : [];
            foreach ($pending as $clase) {
                $newHash = \App\Http\Controllers\DibujosFundicionPdfController::computeClassHash($ot, $clase);
                if ($newHash !== "") {
                    $enviadas[$clase] = $newHash;
                }
            }
            $history->clases_enviadas = $enviadas;
            $history->save();

            \App\Http\Controllers\DibujosFundicionPdfController::copyToAlmacen($ot, false);
        }

        return response()->json([
            'success' => true,
            'message' => 'Cambios aplicados exitosamente'
        ]);
    }
}

