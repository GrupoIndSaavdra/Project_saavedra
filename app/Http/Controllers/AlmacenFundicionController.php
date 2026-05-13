<?php

namespace App\Http\Controllers;

use App\Models\FundicionHistory;
use App\Models\Orden_trabajo;
use App\Models\Clase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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
        $ayudas = [];
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

        $allFiles = $dibujos->merge($ayudas)->values();

        return response()->json([
            'existe' => true,
            'archivos' => $allFiles,
            'ot' => $ot,
            'status' => $history->status,
            'tiene_modelo' => (bool) $history->tiene_modelo,
            'alert_sent_at' => $history->alert_sent_at?->format('d/m/Y H:i'),
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

        // Obtener TODAS las clases para esta OT (incluyendo las ya finalizadas para historial)
        $clases = $ot->clases->map(fn($c) => [
            'id' => $c->id,
            'nombre' => $c->nombre
        ])->values();

        // Obtener clases vinculadas desde FundicionHistory (Ayudas Visuales asignadas)
        $history = FundicionHistory::where('ot', '=', $otFull, 'and')->first();
        $clasesVinculadas = $history ? ($history->ayudas_config ?? []) : [];

        // --- Lógica de Folio Autoincremental ---
        $folioPath = 'DOCUMENTACION_GIS/folio_config.json';
        $currentFolio = 46; // Valor inicial solicitado
        
        if (Storage::disk('local')->exists($folioPath)) {
            $config = json_decode(Storage::disk('local')->get($folioPath), true);
            $currentFolio = $config['next_folio'] ?? 46;
        } else {
            // Crear el archivo si no existe
            Storage::disk('local')->put($folioPath, json_encode(['next_folio' => 46]));
        }

        $year = date('Y');
        $folioStr = "MOD-{$year}-" . str_pad($currentFolio, 4, '0', STR_PAD_LEFT);

        return response()->json([
            'success' => true,
            'moldura' => $ot->moldura ? $ot->moldura->nombre : 'Sin moldura',
            'clases' => $clases,
            'clases_vinculadas' => $clasesVinculadas,
            'folio' => $folioStr
        ]);
    }

    /**
     * Guarda la pre-orden (Placeholder hasta tener el modelo/tabla).
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

        // 2. Generar el PDF en orientación horizontal
        $pdf = Pdf::loadView('pdf.pre_orden', [
            'data' => $data,
            'user' => $user
        ])->setPaper('a4', 'landscape');

        // 3. Definir rutas y nombres descriptivos
        $otName = $this->sanitizePath($data['ot']);
        $folio = preg_replace('/[^A-Za-z0-9\-]/', '_', $data['folio']);
        $otClean = preg_replace('/[^A-Za-z0-9\-]/', '_', $data['ot']);
        $moldura = preg_replace('/[^A-Za-z0-9\-]/', '_', $data['moldura']);
        $proveedor = preg_replace('/[^A-Za-z0-9\-]/', '_', $data['proveedor']);
        $fecha = date('d_m_Y_H_i');

        $fileName = "PreOrden_{$folio}_OT_{$otClean}_{$moldura}_{$proveedor}_{$fecha}.pdf";
        $otPath = self::ALMACEN_DIR . '/' . $otName;
        $savePath = $otPath . '/pre_ordenes/' . $fileName;

        // Asegurar que el directorio de pre-órdenes existe
        if (!Storage::disk('local')->exists($otPath . '/pre_ordenes')) {
            Storage::disk('local')->makeDirectory($otPath . '/pre_ordenes');
        }

        // 4. Guardar copia en el servidor
        Storage::disk('local')->put($savePath, $pdf->output());

        // Actualizar estado en historial (Pendiente de modelo)
        $otToUpdate = $data['ot_raw'] ?? $data['ot'];
        FundicionHistory::where('ot', '=', $otToUpdate, 'and')->update(['pre_orden_sent' => true]);

        // 5. Enviar por Email
        try {
            // Destinatarios confirmados para SS Metal Foundry
            $destinatarios = [
                'produccion@ssmetalf.mx',
                'laboratorio@ssmetalf.mx',
                'abraham@grupoindsaavedra.com',
                'inspecciontec@grupoindsaavedra.com'
            ];

            Mail::to($destinatarios)->send(new PreOrdenMailable($data, storage_path('app/' . $savePath), Auth::user()->name));

            // 6. Incrementar el Folio tras el envío exitoso
            $folioPath = 'DOCUMENTACION_GIS/folio_config.json';
            if (Storage::disk('local')->exists($folioPath)) {
                $config = json_decode(Storage::disk('local')->get($folioPath), true);
                $config['next_folio'] = ($config['next_folio'] ?? 46) + 1;
                Storage::disk('local')->put($folioPath, json_encode($config));
            }
        } catch (\Exception $e) {
            // Log error but continue for the download
            \Illuminate\Support\Facades\Log::error("Error enviando email de Pre-Orden: " . $e->getMessage());
        }

        // 6. Retornar el PDF para descarga automática en el navegador
        return response($pdf->output(), 200)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'attachment; filename="' . $fileName . '"');
    }
}