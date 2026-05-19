<?php

namespace App\Http\Controllers;

use App\Models\FundicionHistory;
use App\Models\Orden_trabajo;
use App\Models\Clase;
use App\Models\PreOrdenFundicion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Mail;
use App\Mail\PreOrdenMailable;
use Barryvdh\DomPDF\Facade\Pdf;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class AlmacenFundicionController extends Controller
{
    /**
     * Directorio aislado donde se guardan las copias protegidas de Almacén.
     */
    private const ALMACEN_DIR = 'DOCUMENTACION_GIS/ALMACEN_FUNDICION';

    /**
     * Perfiles de usuario que tienen acceso a esta vista.
     * 4 = Calidad | 5 = Almacen
     */
    private const PERFILES_PERMITIDOS = ['4', '5'];

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

        /** @var \App\Models\FundicionHistory|null $history */
        $history = FundicionHistory::where('ot', '=', $ot, 'and')->first();

        if (!$history || !$history->alert_sent_at) {
            return response()->json([
                'existe' => false,
                'archivos' => [],
                'ot' => $ot,
            ]);
        }

        $dirPath = self::ALMACEN_DIR . '/' . $ot;
        $ayudasDirPath = $dirPath . '/ayudas_visuales';
        $soloPreorden = $request->query('solo_preorden', '0') === '1';

        $dibujos = collect([]);
        $ayudas = collect([]);

        if (!$soloPreorden) {
            // 1. Obtener dibujos principales (Recursivo, excluyendo ayudas_visuales)
            $dibujos = collect(Storage::disk('local')->allFiles($dirPath))
                ->filter(function ($f) use ($dirPath) {
                    $rel = str_replace($dirPath . '/', '', $f);
                    return strpos($rel, 'ayudas_visuales/') !== 0 && strtolower(pathinfo($f, PATHINFO_EXTENSION)) === 'pdf';
                })
                ->map(function ($f) use ($ot, $dirPath) {
                    // Normalizar separadores para reemplazo robusto
                    $fNorm = str_replace('\\', '/', $f);
                    $dirPathNorm = str_replace('\\', '/', $dirPath);
                    $relName = ltrim(str_replace($dirPathNorm, '', $fNorm), '/');
                    $utf8RelName = $this->toUtf8($relName);

                    return [
                        'nombre' => $utf8RelName,
                        'tipo' => 'dibujo',
                        'url' => route('almacen.fundicion.serve', [
                            'ot' => $ot,
                            'archivo' => $utf8RelName,
                            'tipo' => 'dibujo',
                        ]),
                    ];
                });

            // 2. Obtener ayudas visuales (subdirectorio ayudas_visuales - ESCANEO RECURSIVO)
            if (Storage::disk('local')->exists($ayudasDirPath)) {
                $ayudas = collect(Storage::disk('local')->allFiles($ayudasDirPath))
                    ->filter(fn($f) => strtolower(pathinfo($f, PATHINFO_EXTENSION)) === 'pdf')
                    ->map(function ($f) use ($ayudasDirPath, $ot) {
                        $fNorm = str_replace('\\', '/', $f);
                        $dirPathNorm = str_replace('\\', '/', $ayudasDirPath);
                        $relName = ltrim(str_replace($dirPathNorm, '', $fNorm), '/');
                        $utf8RelName = $this->toUtf8($relName);

                        return [
                            'nombre' => $utf8RelName,
                            'tipo' => 'ayuda',
                            'url' => route('almacen.fundicion.serve', [
                                'ot' => $ot,
                                'archivo' => $utf8RelName,
                                'tipo' => 'ayuda',
                            ]),
                        ];
                    });
            }
        } else {
            // Solo obtener las pre-órdenes generadas
            $preordenesPath = $ayudasDirPath . '/preordenes';
            if (Storage::disk('local')->exists($preordenesPath)) {
                $ayudas = collect(Storage::disk('local')->allFiles($preordenesPath))
                    ->filter(fn($f) => strtolower(pathinfo($f, PATHINFO_EXTENSION)) === 'pdf')
                    ->map(function ($f) use ($preordenesPath, $ot) {
                        $fNorm = str_replace('\\', '/', $f);
                        $dirPathNorm = str_replace('\\', '/', $preordenesPath);
                        $relName = ltrim(str_replace($dirPathNorm, '', $fNorm), '/');
                        $utf8RelName = $this->toUtf8($relName);

                        // Retornar con el prefijo de la carpeta para que serveFile lo encuentre
                        $fullName = 'preordenes/' . $utf8RelName;

                        return [
                            'nombre' => $fullName,
                            'tipo' => 'ayuda',
                            'url' => route('almacen.fundicion.serve', [
                                'ot' => $ot,
                                'archivo' => $fullName,
                                'tipo' => 'ayuda',
                            ]),
                        ];
                    });
            }
        }

        $allFiles = $dibujos->merge($ayudas)->values();

        $preOrden = PreOrdenFundicion::where('ot', '=', $history->ot, 'and')->first();
        $fechaEntrega = $preOrden && $preOrden->fecha_entrega 
            ? ($preOrden->fecha_entrega instanceof \DateTimeInterface 
                ? $preOrden->fecha_entrega->format('Y-m-d') 
                : substr((string)$preOrden->fecha_entrega, 0, 10)) 
            : null;

        return response()->json([
            'existe' => true,
            'archivos' => $allFiles,
            'ot' => $ot,
            'status' => $history->status,
            'tiene_modelo' => (bool) $history->tiene_modelo,
            'alert_sent_at' => $history->alert_sent_at?->format('d/m/Y H:i'),
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

        if (empty($ot) || empty($archivo)) {
            abort(422, 'Parámetros inválidos.');
        }

        $baseDir = ($tipo === 'ayuda') 
            ? self::ALMACEN_DIR . '/' . $ot . '/ayudas_visuales'
            : self::ALMACEN_DIR . '/' . $ot;

        if (!Storage::disk('local')->exists($baseDir)) {
            abort(404, 'Directorio no encontrado.');
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

        if (!$foundFile) {
            abort(404, 'Archivo no encontrado en el directorio de Almacén.');
        }

        /** @var \Illuminate\Filesystem\FilesystemAdapter $disk */
        $disk = Storage::disk('local');
        $fullPath = $disk->path($foundFile);

        return response()->file($fullPath, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . basename($archivo) . '"',
        ]);
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
     * Actualiza el estado del modelo para una OT (Botón "Sí").
     */
    public function updateModelStatus(Request $request)
    {
        $this->verificarAcceso();

        $ot = $request->input('ot');
        $history = FundicionHistory::where('ot', '=', $ot, 'and')->first();

        if (!$history) {
            return response()->json(['success' => false, 'message' => 'Registro no encontrado.'], 404);
        }

        $history->tiene_modelo = true;
        $history->save();

        return response()->json([
            'success' => true,
            'message' => "Se ha registrado que la OT {$ot} ya cuenta con modelo."
        ]);
    }

    /**
     * Obtiene datos de la OT para el modal de Pre-Orden.
     */
    public function getOtData(Request $request)
    {
        $this->verificarAcceso();

        $otFull = $request->query('ot', '');
        \Illuminate\Support\Facades\Log::info("getOtData: Consultando OT = " . $otFull);
        // Extraer el número de OT (ej: de "OT 6473 - ..." extraer 6473)
        preg_match('/OT\s*(\d+)/', $otFull, $matches);
        $otId = isset($matches[1]) ? (int) $matches[1] : 0;

        $ot = Orden_trabajo::with(['moldura', 'clases'])->find($otId);

        if (!$ot) {
            return response()->json(['success' => false, 'message' => 'OT no encontrada.'], 404);
        }

        // Obtener TODAS las clases para esta OT
        $clases = $ot->clases->map(fn($c) => [
            'id' => $c->id,
            'nombre' => $c->nombre
        ])->values();

        // Obtener clases vinculadas desde FundicionHistory (Ayudas Visuales asignadas)
        $history = FundicionHistory::where('ot', '=', $otFull, 'and')->first();
        $clasesVinculadas = $history ? ($history->ayudas_config ?? []) : [];

        // --- Recuperar pre-orden existente desde la BASE DE DATOS ---
        $preOrdenDB = PreOrdenFundicion::where('ot', '=', $otFull, 'and')->first();

        $preordenData = null;
        $folioStr = '';

        if ($preOrdenDB) {
            // Pre-orden existente: recuperar folio y datos para prellenar el formulario
            $folioStr = $preOrdenDB->folio;
            $preordenData = [
                'folio'        => $preOrdenDB->folio,
                'proveedor'    => $preOrdenDB->proveedor,
                'fecha_creacion'=> $preOrdenDB->fecha_creacion ? ($preOrdenDB->fecha_creacion instanceof \DateTimeInterface ? $preOrdenDB->fecha_creacion->format('Y-m-d') : substr((string)$preOrdenDB->fecha_creacion, 0, 10)) : null,
                'fecha_entrega'=> $preOrdenDB->fecha_entrega ? ($preOrdenDB->fecha_entrega instanceof \DateTimeInterface ? $preOrdenDB->fecha_entrega->format('Y-m-d') : substr((string)$preOrdenDB->fecha_entrega, 0, 10)) : null,
                'moldura'      => $preOrdenDB->moldura,
                'observaciones'=> $preOrdenDB->observaciones,
                'filas'        => $preOrdenDB->filas,
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
        $pdf = Pdf::loadView('pdf.pre_orden', [
            'data' => $data,
            'user' => $user
        ])->setPaper('a4', 'landscape');

        // 4. Definir nombre del archivo y ruta de guardado
        $folio    = preg_replace('/[^A-Za-z0-9\-]/', '_', $data['folio']);
        $moldura  = preg_replace('/[^A-Za-z0-9\-]/', '_', $data['moldura'] ?? '');
        $proveedor = preg_replace('/[^A-Za-z0-9\-]/', '_', $data['proveedor']);
        $fechaStamp = date('d_m_Y_H_i');
        $fileName = "Pre-Orden_Fundicion-{$folio}_OT_{$otClean}_{$moldura}_{$proveedor}_{$fechaStamp}.pdf";

        $folderName = $this->sanitizePath($this->normalizeOTName($otRaw));
        $otPath = self::ALMACEN_DIR . '/' . $folderName . '/ayudas_visuales/preordenes';
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

        // 7. Actualizar flag de pre_orden_sent en historial de Fundición
        FundicionHistory::where('ot', '=', $otRaw, 'and')->update(['pre_orden_sent' => true]);

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
                \Illuminate\Support\Facades\Log::error("Error incrementando Folio: " . $e->getMessage());
            }
        }

        // 9. Retornar el PDF para descarga automática en el navegador
        return response($pdf->output(), 200)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'attachment; filename="' . $fileName . '"');
    }

    /**
     * Envía la pre-orden y los adjuntos seleccionados por correo electrónico (Fase 2).
     */
    public function sendEmailPreOrden(Request $request)
    {
        $this->verificarAcceso();

        $ot = $request->input('ot');
        $destinatario = $request->input('destinatario');

        if (empty($ot) || empty($destinatario) || empty($request->input('fecha_entrega')) || !$request->hasFile('archivos_adicionales')) {
            return response()->json([
                'success' => false, 
                'message' => 'La OT, el Destinatario, la Fecha de Entrega y al menos un archivo escaneado son requeridos.'
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

        // Obtener datos guardados de la pre-orden desde la base de datos
        $preOrden = PreOrdenFundicion::where('ot', '=', $ot, 'and')->first();
        if (!$preOrden) {
            return response()->json(['success' => false, 'message' => 'No se encontró la pre-orden en la base de datos.'], 404);
        }

        // Si se envió una fecha de entrega en Fase 2, guardarla en BD y regenerar el PDF
        if ($request->filled('fecha_entrega')) {
            $preOrden->fecha_entrega = $request->input('fecha_entrega');
            $preOrden->save();

            // Regenerar PDF para incorporar la fecha de entrega
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

            // Obtener el usuario creador de la pre-orden o el actual
            $creator = \App\Models\User::where('id', '=', $preOrden->user_id, 'and')->first() ?: Auth::user();

            $pdf = Pdf::loadView('pdf.pre_orden', [
                'data' => $data,
                'user' => $creator
            ])->setPaper('a4', 'landscape');

            // Guardar PDF en servidor (sobrescribir el anterior)
            $folderName = $this->sanitizePath($this->normalizeOTName($ot));
            $otPath = self::ALMACEN_DIR . '/' . $folderName . '/ayudas_visuales/preordenes';
            $savePath = $otPath . '/' . $preOrden->pdf_filename;

            if (!Storage::disk('local')->exists($otPath)) {
                Storage::disk('local')->makeDirectory($otPath);
            }
            Storage::disk('local')->put($savePath, $pdf->output());
        }

        $folioVal = $preOrden->folio;
        $molduraVal = $preOrden->moldura ?: 'N/A';
        $fechaEntregaVal = $preOrden->fecha_entrega ? ($preOrden->fecha_entrega instanceof \DateTimeInterface ? $preOrden->fecha_entrega->format('d/m/Y') : $preOrden->fecha_entrega) : 'Llenado manual';
        $observacionesVal = $preOrden->observaciones ?: 'Sin observaciones adicionales.';
        $proveedorVal = $preOrden->proveedor;

        $otCleaned = preg_replace('/^OT\s*/i', '', $ot);
        $parts = explode('-', $otCleaned, 2);
        $otOnly = trim($parts[0]);
        $molduraVal = (count($parts) > 1) ? trim($parts[1]) : ($preOrden->moldura ?: 'N/A');
        
        $asunto = "Pre-Orden de Fabricación de Modelos (Folio: {$folioVal}) - OT {$otCleaned}";

        $cuerpo = "
        <div style='font-family: Arial, sans-serif; line-height: 1.6; color: #334155; max-width: 600px; margin: 0 auto; border: 1px solid #e2e8f0; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 6px rgba(0,0,0,0.05);'>
            <div style='background-color: #033966; color: white; padding: 20px; text-align: center;'>
                <h2 style='margin: 0; font-size: 1.5em;'>Pre-Orden de Fabricación de Modelos</h2>
                <p style='margin: 5px 0 0 0; font-size: 0.9em; opacity: 0.9;'>Orden de Trabajo: {$otCleaned}</p>
            </div>
            <div style='padding: 25px; background-color: #ffffff;'>
                <p>Estimado Proveedor (<strong>{$proveedorVal}</strong>),</p>
                <p>Se ha generado una solicitud de fabricación de modelos para la Orden de Trabajo <strong>{$otCleaned}</strong>. A continuación se presentan los detalles clave de la pre-orden:</p>
                
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

        // 0. Siempre adjuntar la Pre-Orden de Fabricación
        $preOrdenPdfPath = $ayudasDirPath . '/preordenes/' . $preOrden->pdf_filename;
        if (Storage::disk('local')->exists($preOrdenPdfPath)) {
            $attachments[] = [
                'path' => storage_path('app/' . $preOrdenPdfPath),
                'name' => basename($preOrden->pdf_filename),
                'mime' => 'application/pdf'
            ];
        }

        // 1. Archivos del servidor seleccionados
        $archivosSeleccionados = $request->input('archivos_seleccionados', []);
        if (is_array($archivosSeleccionados)) {
            foreach ($archivosSeleccionados as $archivo) {
                // Si es el mismo PDF de la pre-orden, omitirlo para no duplicarlo
                if (basename($archivo) === $preOrden->pdf_filename) {
                    continue;
                }

                // Sanitizar para evitar path traversal
                $archivoSanitized = $this->sanitizeFileNameWithFolder($archivo);
                
                // Buscar si es un dibujo principal o una ayuda visual
                $posPaths = [
                    $ayudasDirPath . '/' . $archivoSanitized,
                    $dirPath . '/' . $archivoSanitized
                ];

                foreach ($posPaths as $path) {
                    if (Storage::disk('local')->exists($path)) {
                        $attachments[] = [
                            'path' => storage_path('app/' . $path),
                            'name' => basename($archivoSanitized),
                            'mime' => 'application/pdf'
                        ];
                        break;
                    }
                }
            }
        }

        // 2. Archivos adicionales cargados desde la computadora
        if ($request->hasFile('archivos_adicionales')) {
            $uploadedFiles = $request->file('archivos_adicionales');
            if (is_array($uploadedFiles)) {
                foreach ($uploadedFiles as $file) {
                    $attachments[] = [
                        'path' => $file->getRealPath(),
                        'name' => 'Escaneado_Fundicion-' . $file->getClientOriginalName(),
                        'mime' => $file->getClientMimeType()
                    ];
                }
            } else {
                $attachments[] = [
                    'path' => $uploadedFiles->getRealPath(),
                    'name' => 'Escaneado_Fundicion-' . $uploadedFiles->getClientOriginalName(),
                    'mime' => $uploadedFiles->getClientMimeType()
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

            return response()->json([
                'success' => true,
                'message' => 'El correo electrónico de la pre-orden con sus adjuntos ha sido enviado con éxito.'
            ]);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("Error al enviar pre-orden con adjuntos: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al enviar el correo: ' . $e->getMessage()
            ], 500);
        }
    }
}