<?php

namespace App\Http\Controllers;

use App\Models\Orden_trabajo;
use App\Models\RInternoSalida;
use App\Models\RInternoSalidaPieza;
use App\Models\RInternoSalidaLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Controlador para la vista "Salida de Molduras" (Calidad Fundición).
 * Gestiona el registro de números de trazabilidad enviados a la RAZA.
 *
 * Perfiles con acceso:
 *   1 = Administrador  (lectura + escritura)
 *   3 = Master         (lectura + escritura)
 *   4 = Calidad        (lectura + escritura)
 *
 * Código de formato: F PRO CPT | Versión 6 | Fecha Rev: 22/04/2026
 */
class RInternoSalidasController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    // ─────────────────────────────────────────────────────────────────
    // 1. Vista de índice: buscador de OT + tabla de reportes existentes
    // ─────────────────────────────────────────────────────────────────

    public function index(Request $request)
    {
        // Solo perfiles autorizados
        if (!in_array(auth()->user()->perfil, [1, 3, 4])) {
            abort(403, 'No tienes permisos para acceder a esta sección.');
        }

        // --- AUTO-REPARACIÓN DE BUG DE URLENCODE ---
        // Elimina los '+' que se hayan guardado en la columna clase, dejándolos como espacios
        DB::table('r_interno_salidas')
            ->where('clase', 'like', '%+%')
            ->update(['clase' => DB::raw("REPLACE(clase, '+', ' ')")]);
        // -------------------------------------------

        // Buscar OTs que tengan clases de tipo MOLDES o BOMBILLOS para el selector
        $ordenesTrabajo = DB::table('orden_trabajo as ot')
            ->join('clases as cl', 'cl.id_ot', '=', 'ot.id')
            ->join('molduras as m', 'm.id', '=', 'ot.id_moldura')
            ->select([
                'ot.id as ot_id',
                'm.nombre as nombre_moldura',
                'ot.cliente',
                'ot.cantidad',
                'cl.id as clase_id',
                'cl.nombre as clase_nombre',
            ])
            ->where(function ($q) {
                $q->where('cl.nombre', 'like', '%MOLDES%')
                    ->orWhere('cl.nombre', 'like', '%MOLDE%')
                    ->orWhere('cl.nombre', 'like', '%BOMBILLOS%')
                    ->orWhere('cl.nombre', 'like', '%BOMBILLO%')
                    ->orWhere('cl.nombre', 'like', '%FONDO%')
                    ->orWhere('cl.nombre', 'like', '%OBTURADOR%')
                    ->orWhere('cl.nombre', 'like', '%EMBUDO%');
            })
            ->orderByDesc('ot.created_at')
            ->get();

        // Reportes ya creados (agrupados por OT para evitar repetir renglones)
        $reportesRaw = RInternoSalida::with('inspector')
            ->orderByDesc('created_at')
            ->limit(50)
            ->get();

        $reportesExistentes = $reportesRaw->groupBy('ot_id')->map(function ($grupo) {
            $primero = $grupo->first();

            // Se asegura de que solo haya una clase de molde y una de bombillo
            $clasesPorFormato = $grupo->unique('formato')->pluck('clase')->map(fn($c) => str_replace('+', ' ', $c))->toArray();

            return (object) [
                'ot_id' => $primero->ot_id,
                'nombre_moldura' => $primero->nombre_moldura,
                'clases' => $clasesPorFormato,
                'formatos' => $grupo->pluck('formato')->unique()->toArray(),
                'inspector' => $primero->inspector,
                'fecha_inicio' => $primero->fecha_inicio,
                'total_piezas' => $primero->total_piezas,
                'primer_clase' => str_replace('+', ' ', $primero->clase),
            ];
        })->values();

        return view('calidad.r_interno_salidas.index', compact(
            'ordenesTrabajo',
            'reportesExistentes'
        ));
    }

    // ─────────────────────────────────────────────────────────────────
    // 2. Vista del reporte: carga o crea el reporte para OT + clase
    // ─────────────────────────────────────────────────────────────────

    public function show(string $ot, string $clase)
    {
        if (!in_array(auth()->user()->perfil, [1, 3, 4])) {
            abort(403, 'No tienes permisos para acceder a esta sección.');
        }

        // Buscar la OT con su moldura
        $ordenTrabajo = Orden_trabajo::with('moldura')
            ->where('id', '=', $ot, 'and')
            ->firstOrFail();

        // Detectar formato automáticamente por la clase
        $formato = RInternoSalida::detectarFormato($clase);

        // Obtener datos del inspector actual
        $inspector = auth()->user();

        // Buscar la información real de pedido y piezas (consignación) de la tabla clases
        $claseData = \App\Models\Clase::where('id_ot', $ot)->where('nombre', $clase)->first();

        $pedido = $claseData ? (int) $claseData->pedido : (int) ($ordenTrabajo->cantidad ?? 0);
        $consignacion = $claseData ? (int) $claseData->piezas : 0;

        // Crear o cargar el reporte
        $reporte = RInternoSalida::firstOrCreate(
            [
                'ot_id' => $ot,
                'clase' => $clase,
            ],
            [
                'nombre_moldura' => $ordenTrabajo->moldura?->nombre ?? 'Sin moldura',
                'inspector_id' => $inspector->id,
                'cliente' => $ordenTrabajo->cliente ?? '',
                'cantidad_pedido' => $pedido,
                'cantidad_consignacion' => $consignacion,
                'fecha_inicio' => now()->toDateString(),
                'formato' => $formato,
                'observaciones' => null,
            ]
        );

        // Sincronizar en caso de que la Clase en Programación de O.T. se haya modificado después
        $updated = false;
        if ($reporte->cantidad_pedido !== $pedido || $reporte->cantidad_consignacion !== $consignacion) {
            $reporte->cantidad_pedido = $pedido;
            $reporte->cantidad_consignacion = $consignacion;
            $updated = true;
        }
        if (empty($reporte->fecha_inicio)) {
            $reporte->fecha_inicio = now()->toDateString();
            $updated = true;
        }
        if ($updated) {
            $reporte->save();
        }

        // Registrar acceso / carga de información en el log de auditoría
        RInternoSalidaLog::registrar(
            reporteId: $reporte->id,
            accion: 'Cargar información',
            campo: 'seleccion_ot_clase',
            anterior: null,
            nuevo: "Acceso a OT: {$ot} | Clase: {$clase} | Formato: {$formato}",
            ip: request()->ip()
        );

        // Total de piezas disponibles en este reporte
        $totalPiezas = $reporte->total_piezas;

        // Cargar piezas existentes indexadas por número
        $piezasExistentes = $reporte->piezas->keyBy('numero_pieza');

        // Generar array completo de celdas (1 hasta $totalPiezas)
        $celdas = [];
        for ($i = 1; $i <= $totalPiezas; $i++) {
            $celdas[$i] = $piezasExistentes->get($i) ?? new RInternoSalidaPieza([
                'r_interno_salida_id' => $reporte->id,
                'numero_pieza' => $i,
            ]);
        }

        // Obtener listado de OTs disponibles para el selector rápido del encabezado
        $ordenesTrabajo = DB::table('orden_trabajo as ot')
            ->join('clases as cl', 'cl.id_ot', '=', 'ot.id')
            ->join('molduras as m', 'm.id', '=', 'ot.id_moldura')
            ->select([
                'ot.id as ot_id',
                'm.nombre as nombre_moldura',
                'ot.cliente',
                'cl.nombre as clase_nombre',
            ])
            ->where(function ($q) {
                $q->where('cl.nombre', 'like', '%MOLDES%')
                    ->orWhere('cl.nombre', 'like', '%MOLDE%')
                    ->orWhere('cl.nombre', 'like', '%BOMBILLOS%')
                    ->orWhere('cl.nombre', 'like', '%BOMBILLO%')
                    ->orWhere('cl.nombre', 'like', '%FONDO%')
                    ->orWhere('cl.nombre', 'like', '%OBTURADOR%')
                    ->orWhere('cl.nombre', 'like', '%EMBUDO%');
            })
            ->orderByDesc('ot.created_at')
            ->get();

        $pdfs = $reporte->pdfs;

        $hasChanges = true;
        $latestPdf = $pdfs->sortByDesc('created_at')->first();
        if ($latestPdf) {
            $latestLog = RInternoSalidaLog::where('r_interno_salida_id', $reporte->id)
                ->where('campo_editado', '!=', 'seleccion_ot_clase')
                ->latest('created_at')
                ->first();
            if (!$latestLog || $latestLog->created_at <= $latestPdf->created_at) {
                $hasChanges = false;
            }
        }

        $siguienteVersion = \App\Models\RInternoSalidaPdf::where('r_interno_salida_id', $reporte->id)->count() + 1;

        return view('calidad.r_interno_salidas.form', compact(
            'reporte',
            'ordenTrabajo',
            'inspector',
            'celdas',
            'totalPiezas',
            'ordenesTrabajo',
            'pdfs',
            'hasChanges',
            'siguienteVersion'
        ));
    }

    // ─────────────────────────────────────────────────────────────────
    // 3. Autoguardado de celda individual (AJAX)
    // ─────────────────────────────────────────────────────────────────

    public function autosave(Request $request)
    {
        if (!in_array(auth()->user()->perfil, [1, 3, 4])) {
            return response()->json(['error' => 'Sin permisos'], 403);
        }

        $request->validate([
            'reporte_id' => 'required|integer|exists:r_interno_salidas,id',
            'numero_pieza' => 'required|integer|min:1',
            'campo' => 'required|in:valor_simple,valor_90,valor_lp,descripcion',
            'valor' => 'nullable|string|max:100',
        ]);

        try {
            DB::beginTransaction();

            $reporte = RInternoSalida::findOrFail($request->reporte_id);

            // Buscar o crear la pieza en BD
            $pieza = RInternoSalidaPieza::firstOrCreate(
                [
                    'r_interno_salida_id' => $reporte->id,
                    'numero_pieza' => $request->numero_pieza,
                ],
                []
            );

            // Capturar valor anterior para auditoría
            $valorAnterior = $pieza->{$request->campo};
            $valorNuevo = $request->valor;

            // Solo guardar y loguear si hubo cambio real
            if ($valorAnterior !== $valorNuevo) {
                $pieza->{$request->campo} = $valorNuevo;
                $pieza->save();

                // Registrar en log de auditoría
                RInternoSalidaLog::registrar(
                    reporteId: $reporte->id,
                    accion: 'Editar pieza',
                    campo: "pieza_{$request->numero_pieza}_{$request->campo}",
                    anterior: $valorAnterior,
                    nuevo: $valorNuevo,
                    ip: $request->ip()
                );
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Guardado',
                'pieza_id' => $pieza->id,
            ]);

        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('[RInternoSalidas][autosave] Error.', [
                'user' => auth()->user()->matricula ?? 'N/A',
                'reporte_id' => $request->reporte_id,
                'pieza' => $request->numero_pieza,
                'error' => $e->getMessage(),
            ]);
            return response()->json(['error' => 'Error al guardar'], 500);
        }
    }

    // ─────────────────────────────────────────────────────────────────
    // 4. Guardar observaciones (AJAX)
    // ─────────────────────────────────────────────────────────────────

    public function updateObservaciones(Request $request)
    {
        if (!in_array(auth()->user()->perfil, [1, 3, 4])) {
            return response()->json(['error' => 'Sin permisos'], 403);
        }

        $request->validate([
            'reporte_id' => 'required|integer|exists:r_interno_salidas,id',
            'observaciones' => 'nullable|string|max:250',
        ]);

        try {
            DB::beginTransaction();

            $reporte = RInternoSalida::findOrFail($request->reporte_id);
            $anterior = $reporte->observaciones;
            $nueva = $request->observaciones;

            if ($anterior !== $nueva) {
                $reporte->observaciones = $nueva;
                $reporte->save();

                RInternoSalidaLog::registrar(
                    reporteId: $reporte->id,
                    accion: 'Editar observaciones',
                    campo: 'observaciones',
                    anterior: $anterior,
                    nuevo: $nueva,
                    ip: $request->ip()
                );
            }

            DB::commit();

            return response()->json(['success' => true, 'message' => 'Observaciones guardadas']);

        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('[RInternoSalidas][observaciones] Error.', [
                'user' => auth()->user()->matricula ?? 'N/A',
                'reporte_id' => $request->reporte_id,
                'error' => $e->getMessage(),
            ]);
            return response()->json(['error' => 'Error al guardar observaciones'], 500);
        }
    }

    // ─────────────────────────────────────────────────────────────────
    // 5. Actualizar datos del encabezado (AJAX): consignación y fecha
    // ─────────────────────────────────────────────────────────────────

    public function updateHeader(Request $request)
    {
        if (!in_array(auth()->user()->perfil, [1, 3, 4])) {
            return response()->json(['error' => 'Sin permisos'], 403);
        }

        $request->validate([
            'reporte_id' => 'required|integer|exists:r_interno_salidas,id',
            'cantidad_consignacion' => 'nullable|integer|min:0|max:9999',
            'fecha_inicio' => 'nullable|date',
        ]);

        try {
            DB::beginTransaction();

            $reporte = RInternoSalida::findOrFail($request->reporte_id);

            $cambios = [];

            // Actualizar consignación si llegó el dato
            if ($request->has('cantidad_consignacion')) {
                $anterior = $reporte->cantidad_consignacion;
                $nueva = (int) $request->cantidad_consignacion;
                if ($anterior !== $nueva) {
                    $reporte->cantidad_consignacion = $nueva;
                    $cambios[] = "Consignación: {$anterior} → {$nueva}";
                    RInternoSalidaLog::registrar(
                        reporteId: $reporte->id,
                        accion: 'Editar consignación',
                        campo: 'cantidad_consignacion',
                        anterior: $anterior,
                        nuevo: $nueva,
                        ip: $request->ip()
                    );
                }
            }

            // Actualizar fecha de inicio si llegó el dato
            if ($request->has('fecha_inicio') && $request->fecha_inicio) {
                $anterior = $reporte->fecha_inicio
                    ? \Carbon\Carbon::parse($reporte->fecha_inicio)->toDateString()
                    : null;
                $nueva = $request->fecha_inicio;
                if ($anterior !== $nueva) {
                    $reporte->fecha_inicio = $nueva;
                    RInternoSalidaLog::registrar(
                        reporteId: $reporte->id,
                        accion: 'Editar fecha de inicio',
                        campo: 'fecha_inicio',
                        anterior: $anterior,
                        nuevo: $nueva,
                        ip: $request->ip()
                    );
                }
            }

            $reporte->save();
            DB::commit();

            return response()->json([
                'success' => true,
                'total_piezas' => $reporte->total_piezas,
                'message' => 'Encabezado actualizado',
            ]);

        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('[RInternoSalidas][updateHeader] Error.', [
                'user' => auth()->user()->matricula ?? 'N/A',
                'reporte_id' => $request->reporte_id,
                'error' => $e->getMessage(),
            ]);
            return response()->json(['error' => 'Error al actualizar encabezado'], 500);
        }
    }

    // ─────────────────────────────────────────────────────────────────
    // 6. Obtener log de auditoría del reporte (AJAX)
    // ─────────────────────────────────────────────────────────────────

    public function getLog(int $id)
    {
        if (!in_array(auth()->user()->perfil, [1, 3, 4])) {
            return response()->json(['error' => 'Sin permisos'], 403);
        }

        // Obtener todos los movimientos de auditoría de la tabla r_interno_salidas_log
        $logs = RInternoSalidaLog::with(['usuario', 'reporte'])
            ->orderByDesc('created_at')
            ->limit(300)
            ->get()
            ->map(fn($log) => [
                'id' => $log->id,
                'r_interno_salida_id' => $log->r_interno_salida_id,
                'ot_id' => $log->reporte?->ot_id ?? 'N/A',
                'clase' => $log->reporte?->clase ?? 'N/A',
                'accion' => $log->accion,
                'campo' => $log->campo_editado,
                'valor_anterior' => $log->valor_anterior,
                'valor_nuevo' => $log->valor_nuevo,
                'usuario' => $log->usuario?->nombre ?? 'Desconocido',
                'ip' => $log->ip,
                'fecha' => $log->created_at->format('d/m/Y H:i:s'),
                'fecha_iso' => $log->created_at->format('Y-m-d'),
            ]);

        return response()->json(['success' => true, 'logs' => $logs]);
    }

    // ─────────────────────────────────────────────────────────────────
    // 7. Actualización masiva por rango de piezas (AJAX)
    // ─────────────────────────────────────────────────────────────────

    public function bulkUpdate(Request $request)
    {
        if (!in_array(auth()->user()->perfil, [1, 3, 4])) {
            return response()->json(['error' => 'Sin permisos'], 403);
        }

        $request->validate([
            'reporte_id' => 'required|integer|exists:r_interno_salidas,id',
            'desde' => 'required|integer|min:1',
            'hasta' => 'required|integer|min:1',
            'valor' => 'nullable|in:1,0',
        ]);

        try {
            DB::beginTransaction();

            $reporte = RInternoSalida::findOrFail($request->reporte_id);
            $desde = min((int) $request->desde, (int) $request->hasta);
            $hasta = max((int) $request->desde, (int) $request->hasta);
            $valor = $request->valor;

            for ($num = $desde; $num <= $hasta; $num++) {
                RInternoSalidaPieza::updateOrCreate(
                    [
                        'r_interno_salida_id' => $reporte->id,
                        'numero_pieza' => $num,
                    ],
                    [
                        'valor_simple' => $valor,
                    ]
                );
            }

            if ($valor === '1') {
                $accion = 'Marcar rango liberado';
                $texto = "Piezas N° {$desde} a {$hasta} marcadas como liberadas";
            } elseif ($valor === '0') {
                $accion = 'Marcar rango rechazado';
                $texto = "Piezas N° {$desde} a {$hasta} marcadas como rechazadas";
            } else {
                $accion = 'Marcar rango ninguno';
                $texto = "Piezas N° {$desde} a {$hasta} marcadas como estado ninguno";
            }

            RInternoSalidaLog::registrar(
                reporteId: $reporte->id,
                accion: $accion,
                campo: 'rango_piezas',
                anterior: null,
                nuevo: $texto,
                ip: $request->ip()
            );

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => $texto,
                'desde' => $desde,
                'hasta' => $hasta,
                'valor' => $valor,
            ]);

        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('[RInternoSalidas][bulkUpdate] Error.', [
                'user' => auth()->user()->matricula ?? 'N/A',
                'reporte_id' => $request->reporte_id,
                'error' => $e->getMessage(),
            ]);
            return response()->json(['error' => 'Error al actualizar rango'], 500);
        }
    }

    public function generatePdf(int $id)
    {
        if (!in_array(auth()->user()->perfil, [1, 3, 4])) {
            abort(403, 'No tienes permisos para acceder a esta sección.');
        }

        $reporte = clone RInternoSalida::findOrFail($id);
        if (!$reporte->enviado) {
            $reporte->pdf_generado_count += 1;
            $reporte->save();
        }

        $ordenTrabajo = Orden_trabajo::find($reporte->ot_id);
        $inspector = \App\Models\User::find($reporte->inspector_id);
        $totalPiezas = $reporte->total_piezas;
        $celdas = $reporte->piezas()->get()->keyBy('numero_pieza');

        // Calcular version
        $siguienteVersion = \App\Models\RInternoSalidaPdf::where('r_interno_salida_id', $id)->count() + 1;

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('calidad.r_interno_salidas.pdf', compact('reporte', 'ordenTrabajo', 'inspector', 'totalPiezas', 'celdas', 'siguienteVersion'));
        $pdf->setOption('isRemoteEnabled', true);
        $pdf->setOption('isPhpEnabled', true);
        $pdf->setPaper('letter', 'portrait');

        // Nombrar el archivo
        $fechaStr = \Carbon\Carbon::now()->format('Y-m-d');
        $otNum = trim($ordenTrabajo->id);

        // Sanitizar nombres para Windows (eliminar caracteres inválidos y manejar vacíos)
        $otNombreOriginal = trim((string) $ordenTrabajo->nombre_pieza);
        $otNombreSeguro = preg_replace('/[\\\\\\/\\:\\*\\?\\"\\<\\>\\|]/', '_', $otNombreOriginal);
        $claseSeguro = preg_replace('/[\\\\\\/\\:\\*\\?\\"\\<\\>\\|]/', '_', trim($reporte->clase));

        if (empty($otNombreSeguro)) {
            $folderName = "OT {$otNum}";
            $filePrefix = "OT_{$otNum}";
        } else {
            $folderName = trim("OT {$otNum} - {$otNombreSeguro}");
            $filePrefix = "OT_{$otNum}-{$otNombreSeguro}";
        }

        $nombreArchivo = "Reporte_Interno_Salida-{$filePrefix}-{$claseSeguro}-{$fechaStr}-V{$siguienteVersion}.pdf";

        // Ruta en disco local
        $baseFolder = "DOCUMENTACION_GIS/REPORTES_INTERNOS_SALIDAS/{$folderName}/{$claseSeguro}";
        $rutaCompleta = "{$baseFolder}/{$nombreArchivo}";

        \Illuminate\Support\Facades\Storage::disk('local')->put($rutaCompleta, $pdf->output());

        $pdfRecord = \App\Models\RInternoSalidaPdf::create([
            'r_interno_salida_id' => $id,
            'version' => $siguienteVersion,
            'nombre_archivo' => $nombreArchivo,
            'ruta' => $rutaCompleta,
            'creado_por' => auth()->id(),
        ]);

        return response()->json([
            'success' => true,
            'pdf_id' => $pdfRecord->id,
            'message' => 'PDF generado y guardado correctamente.'
        ]);
    }

    public function downloadPdf(int $id, int $pdf_id)
    {
        $pdfRecord = \App\Models\RInternoSalidaPdf::where('r_interno_salida_id', $id)->findOrFail($pdf_id);

        if (!\Illuminate\Support\Facades\Storage::disk('local')->exists($pdfRecord->ruta)) {
            abort(404, 'El archivo PDF no existe en el servidor.');
        }

        return \Illuminate\Support\Facades\Storage::disk('local')->download($pdfRecord->ruta, $pdfRecord->nombre_archivo);
    }


}
