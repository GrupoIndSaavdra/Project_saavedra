<?php

namespace App\Http\Controllers;

use App\Models\Clase;
use App\Models\Orden_trabajo;
use App\Models\RDimensional;
use App\Models\RDimensionalMedida;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

/**
 * Controlador para la vista "Reporte Dimensional" (Calidad).
 * Gestiona la selección de OT, el registro de cotas dimensionales
 * y la carga de archivos Excel de máquina de medición.
 *
 * Perfiles con acceso:
 *   1 = Administrador (lectura + escritura)
 *   3 = Master        (lectura + escritura)
 *   4 = Calidad       (lectura + escritura)
 */
class InspeccionDimensionalController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Vista principal: selector de OT y listado de reportes dimensionales registrados
     */
    public function index(Request $request)
    {
        if (!in_array(auth()->user()->perfil, [1, 3, 4])) {
            abort(403, 'No tienes permisos para acceder a esta sección.');
        }

        // Obtener OTs activas con sus clases para el selector
        $ordenesTrabajo = DB::table('orden_trabajo as ot')
            ->join('clases as cl', 'cl.id_ot', '=', 'ot.id')
            ->join('molduras as m', 'm.id', '=', 'ot.id_moldura')
            ->select([
                'ot.id as ot_id',
                'm.nombre as nombre_moldura',
                DB::raw("COALESCE(NULLIF(ot.cliente, ''), (SELECT ot2.cliente FROM orden_trabajo ot2 WHERE ot2.id_moldura = ot.id_moldura AND ot2.cliente IS NOT NULL AND ot2.cliente != '' LIMIT 1), 'Sin cliente') as cliente"),
                DB::raw("COALESCE(NULLIF(ot.cantidad, 0), (SELECT CAST(SUM(cl2.piezas) AS UNSIGNED) FROM clases cl2 WHERE cl2.id_ot = ot.id), 0) as cantidad"),
                'cl.id as clase_id',
                'cl.nombre as clase_nombre',
                'cl.piezas as clase_piezas',
                'cl.pedido as clase_pedido',
            ])
            ->orderByDesc('ot.created_at')
            ->get();

        // Obtener reportes dimensionales registrados
        $reportesExistentes = RDimensional::with('inspector')
            ->orderByDesc('updated_at')
            ->limit(100)
            ->get();

        return view('calidad.inspeccion_dimensional.index', compact('ordenesTrabajo', 'reportesExistentes'));
    }

    /**
     * Vista de detalle / formulario de reporte dimensional para una OT y Clase específicas
     */
    public function show(Request $request, $ot, $clase)
    {
        if (!in_array(auth()->user()->perfil, [1, 3, 4])) {
            abort(403, 'No tienes permisos para acceder a esta sección.');
        }

        $clase = urldecode($clase);

        $otData = DB::table('orden_trabajo as ot')
            ->join('molduras as m', 'm.id', '=', 'ot.id_moldura')
            ->where('ot.id', $ot)
            ->select('ot.*', 'm.nombre as nombre_moldura')
            ->first();

        if (!$otData) {
            return redirect()->route('calidad.inspeccion_dimensional.index')
                ->with('error', "La Orden de Trabajo #{$ot} no existe.");
        }

        // Buscar información de la clase en tabla clases
        $claseData = Clase::where('id_ot', $ot)->where('nombre', $clase)->first();

        $pedido = $claseData ? (int) $claseData->pedido : (int) ($otData->cantidad ?? 0);
        $totalPiezas = $claseData ? (int) $claseData->piezas : $pedido;
        if ($totalPiezas <= 0) $totalPiezas = $pedido;
        $consignacion = ($totalPiezas > $pedido) ? ($totalPiezas - $pedido) : 0;

        // Cliente fallback
        $cliente = $otData->cliente;
        if (empty($cliente) || $cliente === 'Sin cliente') {
            $clienteFallback = DB::table('orden_trabajo')
                ->where('id_moldura', $otData->id_moldura)
                ->whereNotNull('cliente')
                ->where('cliente', '!=', '')
                ->value('cliente');
            $cliente = $clienteFallback ?: '—';
        }

        // Cargar o crear el reporte dimensional
        $reporte = RDimensional::firstOrCreate(
            [
                'ot_id' => $ot,
                'clase' => $clase,
            ],
            [
                'nombre_moldura' => $otData->nombre_moldura ?? 'Sin moldura',
                'cliente' => $cliente,
                'moldura_numero' => null,
                'codigo_formato' => '4CAL-02',
                'nivel_revision' => 0,
                'fecha_elaboracion' => now()->toDateString(),
                'fecha_revision' => now()->toDateString(),
                'fecha_aprobacion' => now()->toDateString(),
                'cantidad_pedido' => $pedido,
                'cantidad_consignacion' => $consignacion,
                'cantidad_inspeccionada' => 0,
                'porcentaje_inspeccion' => 0,
                'inspector_id' => auth()->id(),
                'observaciones' => null,
                'estado' => 'en_proceso',
            ]
        );

        // Sincronizar cantidades si cambiaron en la OT / Clases
        if ($reporte->cantidad_pedido !== $pedido || $reporte->cantidad_consignacion !== $consignacion) {
            $reporte->cantidad_pedido = $pedido;
            $reporte->cantidad_consignacion = $consignacion;
            $reporte->save();
        }

        // Calcular regla de muestreo con base en el total con consignación (informativo)
        $rangoMuestreo = RDimensional::calcularRangoMuestreo($totalPiezas);
        $totalFilasRequeridas = $rangoMuestreo['max'] ?: 1;

        // Si el reporte no tiene medidas registradas, crear 1 fila inicial por defecto
        if ($reporte->medidas()->count() === 0) {
            RDimensionalMedida::create([
                'r_dimensional_id' => $reporte->id,
                'numero_pieza' => '1',
            ]);
        }

        $medidas = $reporte->medidas()->get();

        // Calcular cuántas piezas están completamente llenas (todos los 12 campos con valor)
        $piezasCompletas = $medidas->filter(function ($m) {
            return self::isMedidaCompleta($m);
        })->count();

        $porcentajeActual = $totalPiezas > 0 ? round(($piezasCompletas / $totalPiezas) * 100, 1) : 0;
        if ($reporte->cantidad_inspeccionada !== $piezasCompletas || $reporte->porcentaje_inspeccion !== (float) $porcentajeActual) {
            $reporte->cantidad_inspeccionada = $piezasCompletas;
            $reporte->porcentaje_inspeccion = $porcentajeActual;
            $reporte->save();
        }

        return view('calidad.inspeccion_dimensional.show', compact(
            'otData',
            'clase',
            'reporte',
            'medidas',
            'rangoMuestreo',
            'pedido',
            'consignacion',
            'totalPiezas',
            'totalFilasRequeridas',
            'piezasCompletas'
        ));
    }

    /**
     * Verificar si una fila de medidas tiene todos los campos obligatorios llenos
     * Cotas exactas: C1, C2, CUELLO ",500", E, D, F1, A. TOTAL, F, ALTURA, ALTURA 2, AL CUELLO
     */
    public static function isMedidaCompleta(RDimensionalMedida $m): bool
    {
        $campos = ['c1', 'c2', 'cuello', 'e', 'd', 'f1', 'a_total', 'f', 'altura', 'altura_2', 'al_cuello'];
        foreach ($campos as $campo) {
            $val = $m->{$campo};
            if (is_null($val) || trim((string) $val) === '') {
                return false;
            }
        }
        return true;
    }

    /**
     * Autoguardado de celdas en la tabla de medidas (AJAX)
     */
    public function autosaveMedida(Request $request)
    {
        if (!in_array(auth()->user()->perfil, [1, 3, 4])) {
            return response()->json(['error' => 'Sin permisos'], 403);
        }

        $request->validate([
            'reporte_id' => 'required|integer|exists:r_dimensionales,id',
            'medida_id' => 'required|integer|exists:r_dimensional_medidas,id',
            'campo' => 'required|string|max:50',
            'valor' => 'nullable|string|max:100',
        ]);

        try {
            $medida = RDimensionalMedida::where('r_dimensional_id', $request->reporte_id)
                ->findOrFail($request->medida_id);

            $campo = $request->campo;
            $valor = $request->valor;

            $camposValidos = [
                'numero_pieza',
                'c1', 'c2', 'cuello', 'e', 'd', 'f1', 'a_total', 'f', 'altura', 'altura_2', 'al_cuello',
                'observaciones',
                'c', 'altu_1', 'talon', 'b', 'k', 'l', 'f2', 'e2', 'a', 'volumen'
            ];
            if (in_array($campo, $camposValidos)) {
                $medida->{$campo} = $valor;
                $medida->save();
            } else {
                $extra = $medida->datos_extra ?: [];
                $extra[$campo] = $valor;
                $medida->datos_extra = $extra;
                $medida->save();
            }

            // Recalcular cuántas piezas están completamente llenas
            $reporte = RDimensional::findOrFail($request->reporte_id);
            $medidas = $reporte->medidas()->get();
            $totalPiezas = max(1, (int)$reporte->cantidad_pedido + (int)$reporte->cantidad_consignacion);

            $piezasCompletas = $medidas->filter(function ($m) {
                return self::isMedidaCompleta($m);
            })->count();

            $porcentajeActual = round(($piezasCompletas / $totalPiezas) * 100, 1);
            $reporte->cantidad_inspeccionada = $piezasCompletas;
            $reporte->porcentaje_inspeccion = $porcentajeActual;
            $reporte->save();

            $filaCompleta = self::isMedidaCompleta($medida);

            return response()->json([
                'success' => true,
                'message' => 'Guardado',
                'medida_id' => $medida->id,
                'fila_completa' => $filaCompleta,
                'piezas_completas' => $piezasCompletas,
                'total_requeridas' => $medidas->count(),
                'porcentaje_inspeccion' => $porcentajeActual,
            ]);

        } catch (\Throwable $e) {
            Log::error('[RDimensional][autosaveMedida] Error.', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'Error al guardar la cota'], 500);
        }
    }

    /**
     * Guardar especificaciones nominales o tolerancias de la cabecera de la tabla (AJAX)
     */
    public function updateNominalTolerancia(Request $request)
    {
        if (!in_array(auth()->user()->perfil, [1, 3, 4])) {
            return response()->json(['error' => 'Sin permisos'], 403);
        }

        $request->validate([
            'reporte_id' => 'required|integer|exists:r_dimensionales,id',
            'tipo' => 'required|in:nominal,tolerancia',
            'cota' => 'required|string|max:50',
            'valor' => 'nullable|string|max:100',
        ]);

        try {
            $reporte = RDimensional::findOrFail($request->reporte_id);
            $attr = ($request->tipo === 'nominal') ? 'valores_nominales' : 'tolerancias';
            $current = $reporte->{$attr} ?: [];
            $current[$request->cota] = $request->valor;
            $reporte->{$attr} = $current;
            $reporte->save();

            return response()->json(['success' => true, 'message' => 'Especificación guardada']);
        } catch (\Throwable $e) {
            Log::error('[RDimensional][updateNominalTolerancia] Error.', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'Error al guardar especificación'], 500);
        }
    }

    /**
     * Agregar una nueva fila de medición a solicitud del usuario (AJAX)
     */
    public function addRow(Request $request)
    {
        if (!in_array(auth()->user()->perfil, [1, 3, 4])) {
            return response()->json(['error' => 'Sin permisos'], 403);
        }

        $request->validate([
            'reporte_id' => 'required|integer|exists:r_dimensionales,id',
            'numero_pieza' => 'nullable|string|max:50',
        ]);

        try {
            $reporte = RDimensional::findOrFail($request->reporte_id);
            $claseData = Clase::where('id_ot', $reporte->ot_id)->where('nombre', $reporte->clase)->first();
            $otData = DB::table('orden_trabajo')->where('id', $reporte->ot_id)->first();
            $pedido = $claseData ? (int) $claseData->pedido : (int) ($otData->cantidad ?? 0);
            $totalPiezas = $claseData ? (int) $claseData->piezas : $pedido;
            if ($totalPiezas <= 0) $totalPiezas = $pedido;

            $currentCount = $reporte->medidas()->count();
            if ($totalPiezas > 0 && $currentCount >= $totalPiezas) {
                return response()->json([
                    'success' => false,
                    'error' => "Se ha alcanzado el límite máximo de {$totalPiezas} piezas para este lote (incluyendo consignación)."
                ], 422);
            }

            $existingNumbers = $reporte->medidas()->pluck('numero_pieza')->map(fn($v) => (int)$v)->filter()->values()->toArray();
            $requestedNum = $request->numero_pieza ? (int)$request->numero_pieza : null;
            if ($requestedNum && !in_array($requestedNum, $existingNumbers)) {
                $numeroPieza = (string)$requestedNum;
            } else {
                $next = 1;
                while (in_array($next, $existingNumbers)) {
                    $next++;
                }
                $numeroPieza = (string)$next;
            }

            $medida = RDimensionalMedida::create([
                'r_dimensional_id' => $reporte->id,
                'numero_pieza' => $numeroPieza,
            ]);

            $medidas = $reporte->medidas()->get();
            $totalPiezas = max(1, (int)$reporte->cantidad_pedido + (int)$reporte->cantidad_consignacion);
            $piezasCompletas = $medidas->filter(function ($m) {
                return self::isMedidaCompleta($m);
            })->count();

            $porcentajeActual = round(($piezasCompletas / $totalPiezas) * 100, 1);
            $reporte->cantidad_inspeccionada = $piezasCompletas;
            $reporte->porcentaje_inspeccion = $porcentajeActual;
            $reporte->save();

            return response()->json([
                'success' => true,
                'message' => 'Fila agregada',
                'medida' => $medida,
                'cantidad_inspeccionada' => $piezasCompletas,
                'porcentaje_inspeccion' => $porcentajeActual,
            ]);

        } catch (\Throwable $e) {
            Log::error('[RDimensional][addRow] Error.', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'Error al agregar fila'], 500);
        }
    }

    /**
     * Eliminar una fila de medición (AJAX)
     */
    public function deleteRow(Request $request)
    {
        if (!in_array(auth()->user()->perfil, [1, 3, 4])) {
            return response()->json(['error' => 'Sin permisos'], 403);
        }

        $request->validate([
            'reporte_id' => 'required|integer|exists:r_dimensionales,id',
            'medida_id' => 'required|integer|exists:r_dimensional_medidas,id',
        ]);

        try {
            $reporte = RDimensional::findOrFail($request->reporte_id);
            RDimensionalMedida::where('r_dimensional_id', $reporte->id)
                ->where('id', $request->medida_id)
                ->delete();

            $medidas = $reporte->medidas()->get();
            $totalPiezas = max(1, (int)$reporte->cantidad_pedido + (int)$reporte->cantidad_consignacion);
            $piezasCompletas = $medidas->filter(function ($m) {
                return self::isMedidaCompleta($m);
            })->count();

            $porcentajeActual = round(($piezasCompletas / $totalPiezas) * 100, 1);
            $reporte->cantidad_inspeccionada = $piezasCompletas;
            $reporte->porcentaje_inspeccion = $porcentajeActual;
            $reporte->save();

            return response()->json([
                'success' => true,
                'message' => 'Fila eliminada',
                'cantidad_inspeccionada' => $piezasCompletas,
                'porcentaje_inspeccion' => $porcentajeActual,
            ]);

        } catch (\Throwable $e) {
            Log::error('[RDimensional][deleteRow] Error.', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'Error al eliminar fila'], 500);
        }
    }

    /**
     * Subir archivo adjunto (PDF / Excel de máquina de medición) (AJAX) - Hasta 4 archivos
     */
    public function uploadExcel(Request $request)
    {
        if (!in_array(auth()->user()->perfil, [1, 3, 4])) {
            return response()->json(['error' => 'Sin permisos'], 403);
        }

        $request->validate([
            'reporte_id' => 'required|integer|exists:r_dimensionales,id',
            'archivo_excel' => 'required|file|mimes:pdf,xlsx,xls,csv,txt|max:30720',
        ]);

        try {
            $reporte = RDimensional::findOrFail($request->reporte_id);
            $archivos = $reporte->archivos_list;

            if (count($archivos) >= 4) {
                return response()->json(['error' => 'Has alcanzado el límite máximo de 4 archivos adjuntos para este reporte.'], 422);
            }

            $file = $request->file('archivo_excel');
            $nombreOriginal = $file->getClientOriginalName();
            $safeOt = preg_replace('/[^A-Za-z0-9_\-]/', '_', $reporte->ot_id);
            $safeClase = preg_replace('/[^A-Za-z0-9_\-]/', '_', $reporte->clase);
            $fecha = now()->format('Ymd_His');
            $extension = $file->getClientOriginalExtension();

            $nextId = count($archivos) > 0 ? (max(array_column($archivos, 'id')) + 1) : 1;

            $folder = "DOCUMENTACION_GIS/REPORTES_DIMENSIONALES/OT_{$safeOt}/{$safeClase}";
            $filename = "Reporte_Dimensional_OT_{$safeOt}_{$safeClase}_{$fecha}_{$nextId}.{$extension}";
            $rutaCompleta = "{$folder}/{$filename}";

            // Guardar en Storage local
            Storage::disk('local')->put($rutaCompleta, file_get_contents($file->getRealPath()));

            $user = auth()->user();
            $primerNombre = $user ? explode(' ', trim($user->nombre))[0] : 'Usuario';
            $primerApellido = $user ? trim($user->a_paterno ?? '') : '';
            $subidoPor = trim("{$primerNombre} {$primerApellido}") ?: ($user->nombre ?? 'Usuario');

            $nuevoArchivo = [
                'id' => $nextId,
                'nombre' => $nombreOriginal,
                'ruta' => $rutaCompleta,
                'fecha' => now()->format('d/m/Y H:i'),
                'usuario' => $subidoPor,
            ];

            $archivos[] = $nuevoArchivo;

            // Actualizar registro en BD
            $reporte->archivo_excel_ruta = json_encode(array_values($archivos), JSON_UNESCAPED_UNICODE);
            $reporte->archivo_excel_nombre = json_encode(array_column($archivos, 'nombre'), JSON_UNESCAPED_UNICODE);
            $reporte->save();

            $token = md5($reporte->id . '_' . config('app.key'));
            $archivosFormat = array_map(function ($a) use ($reporte, $token) {
                $ext = strtolower(pathinfo($a['nombre'] ?? '', PATHINFO_EXTENSION));
                $isExcel = in_array($ext, ['xlsx', 'xls', 'csv']);
                $openUrl = url("/calidad/inspeccion-dimensional/open-file/{$reporte->id}?file_id={$a['id']}&token={$token}");
                $viewUrl = route('calidad.inspeccion_dimensional.view_excel', ['id' => $reporte->id, 'file_id' => $a['id']]);
                $downloadUrl = route('calidad.inspeccion_dimensional.download_excel', ['id' => $reporte->id, 'file_id' => $a['id']]);

                return array_merge($a, [
                    'usuario' => $a['usuario'] ?? '',
                    'is_excel' => $isExcel,
                    'open_url' => $openUrl,
                    'view_url' => $viewUrl,
                    'download_url' => $downloadUrl,
                ]);
            }, $reporte->archivos_list);

            return response()->json([
                'success' => true,
                'message' => 'Archivo adjunto guardado correctamente.',
                'archivos' => $archivosFormat,
                'total' => count($archivosFormat),
            ]);

        } catch (\Throwable $e) {
            Log::error('[RDimensional][uploadExcel] Error.', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'Error al subir el archivo adjunto'], 500);
        }
    }

    /**
     * Abrir directamente en la aplicación Microsoft Excel de escritorio o visor
     */
    public function openFile(Request $request, int $id)
    {
        $token = $request->query('token');
        $expectedToken = md5($id . '_' . config('app.key'));

        if (!$request->hasValidSignature() && !auth()->check() && $token !== $expectedToken) {
            abort(403, 'Enlace no válido o expirado.');
        }

        $reporte = RDimensional::findOrFail($id);
        $archivos = $reporte->archivos_list;

        if (empty($archivos)) {
            abort(404, 'No hay archivos adjuntos en este reporte.');
        }

        $fileId = $request->query('file_id');
        $archivoTarget = null;

        if ($fileId !== null && $fileId !== '') {
            foreach ($archivos as $a) {
                if ((string)$a['id'] === (string)$fileId) {
                    $archivoTarget = $a;
                    break;
                }
            }
        } else {
            $archivoTarget = $archivos[0];
        }

        if (!$archivoTarget || empty($archivoTarget['ruta']) || !Storage::disk('local')->exists($archivoTarget['ruta'])) {
            abort(404, 'El archivo adjunto no existe en el servidor.');
        }

        $fullPath = Storage::disk('local')->path($archivoTarget['ruta']);
        $nombre = $archivoTarget['nombre'] ?? basename($fullPath);
        $extension = strtolower(pathinfo($nombre, PATHINFO_EXTENSION));

        $mimeTypes = [
            'pdf'  => 'application/pdf',
            'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'xls'  => 'application/vnd.ms-excel',
            'csv'  => 'text/csv',
            'txt'  => 'text/plain',
        ];
        $contentType = $mimeTypes[$extension] ?? (Storage::disk('local')->mimeType($archivoTarget['ruta']) ?: 'application/octet-stream');

        return response()->file($fullPath, [
            'Content-Type' => $contentType,
            'Content-Disposition' => 'inline; filename="' . $nombre . '"',
            'Accept-Ranges' => 'bytes',
            'Cache-Control' => 'private, max-age=0, must-revalidate',
        ]);
    }

    /**
     * Ver/Abrir archivo adjunto (PDF / Excel) en el navegador o aplicación
     */
    public function viewExcel(Request $request, int $id)
    {
        if (!in_array(auth()->user()->perfil, [1, 3, 4])) {
            abort(403, 'No tienes permisos para acceder a esta sección.');
        }

        $reporte = RDimensional::findOrFail($id);
        $archivos = $reporte->archivos_list;

        if (empty($archivos)) {
            abort(404, 'No hay archivos adjuntos en este reporte.');
        }

        $fileId = $request->query('file_id');
        $archivoTarget = null;

        if ($fileId !== null && $fileId !== '') {
            foreach ($archivos as $a) {
                if ((string)$a['id'] === (string)$fileId) {
                    $archivoTarget = $a;
                    break;
                }
            }
        } else {
            $archivoTarget = $archivos[0];
        }

        if (!$archivoTarget || empty($archivoTarget['ruta']) || !Storage::disk('local')->exists($archivoTarget['ruta'])) {
            abort(404, 'El archivo adjunto no existe en el servidor.');
        }

        $fullPath = Storage::disk('local')->path($archivoTarget['ruta']);
        $nombre = $archivoTarget['nombre'] ?? basename($fullPath);
        $extension = strtolower(pathinfo($nombre, PATHINFO_EXTENSION));

        $mimeTypes = [
            'pdf'  => 'application/pdf',
            'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'xls'  => 'application/vnd.ms-excel',
            'csv'  => 'text/csv',
            'txt'  => 'text/plain',
        ];
        $contentType = $mimeTypes[$extension] ?? (Storage::disk('local')->mimeType($archivoTarget['ruta']) ?: 'application/octet-stream');

        return response()->file($fullPath, [
            'Content-Type' => $contentType,
            'Content-Disposition' => 'inline; filename="' . $nombre . '"',
        ]);
    }

    /**
     * Descargar el archivo adjunto (PDF / Excel)
     */
    public function downloadExcel(Request $request, int $id)
    {
        if (!in_array(auth()->user()->perfil, [1, 3, 4])) {
            abort(403, 'No tienes permisos para acceder a esta sección.');
        }

        $reporte = RDimensional::findOrFail($id);
        $archivos = $reporte->archivos_list;

        if (empty($archivos)) {
            abort(404, 'No hay archivos adjuntos en este reporte.');
        }

        $fileId = $request->query('file_id');
        $archivoTarget = null;

        if ($fileId !== null && $fileId !== '') {
            foreach ($archivos as $a) {
                if ((string)$a['id'] === (string)$fileId) {
                    $archivoTarget = $a;
                    break;
                }
            }
        } else {
            $archivoTarget = $archivos[0];
        }

        if (!$archivoTarget || empty($archivoTarget['ruta']) || !Storage::disk('local')->exists($archivoTarget['ruta'])) {
            abort(404, 'El archivo adjunto no existe en el servidor.');
        }

        return Storage::disk('local')->download($archivoTarget['ruta'], $archivoTarget['nombre'] ?? 'Reporte_Dimensional_Adjunto');
    }

    /**
     * Eliminar archivo adjunto con clave maestra CALIDAD2026 (AJAX)
     */
    public function deleteExcel(Request $request)
    {
        if (!in_array(auth()->user()->perfil, [1, 3, 4])) {
            return response()->json(['error' => 'Sin permisos'], 403);
        }

        $request->validate([
            'reporte_id' => 'required|integer|exists:r_dimensionales,id',
            'clave_maestra' => 'required|string',
            'file_id' => 'nullable',
        ]);

        if (trim($request->clave_maestra) !== 'CALIDAD2026') {
            return response()->json(['error' => 'Clave maestra incorrecta. No tienes autorización para eliminar este archivo.'], 403);
        }

        try {
            $reporte = RDimensional::findOrFail($request->reporte_id);
            $archivos = $reporte->archivos_list;
            $fileId = $request->file_id;

            if (empty($archivos)) {
                return response()->json(['error' => 'No hay archivos para eliminar'], 404);
            }

            if ($fileId !== null && $fileId !== '') {
                $nuevosArchivos = [];
                $encontrado = false;
                foreach ($archivos as $a) {
                    if ((string)$a['id'] === (string)$fileId) {
                        $encontrado = true;
                        if (!empty($a['ruta']) && Storage::disk('local')->exists($a['ruta'])) {
                            Storage::disk('local')->delete($a['ruta']);
                        }
                    } else {
                        $nuevosArchivos[] = $a;
                    }
                }
                if (!$encontrado) {
                    return response()->json(['error' => 'Archivo no encontrado'], 404);
                }
                $archivos = $nuevosArchivos;
            } else {
                foreach ($archivos as $a) {
                    if (!empty($a['ruta']) && Storage::disk('local')->exists($a['ruta'])) {
                        Storage::disk('local')->delete($a['ruta']);
                    }
                }
                $archivos = [];
            }

            if (empty($archivos)) {
                $reporte->archivo_excel_ruta = null;
                $reporte->archivo_excel_nombre = null;
            } else {
                $reporte->archivo_excel_ruta = json_encode(array_values($archivos), JSON_UNESCAPED_UNICODE);
                $reporte->archivo_excel_nombre = json_encode(array_column($archivos, 'nombre'), JSON_UNESCAPED_UNICODE);
            }
            $reporte->save();

            return response()->json([
                'success' => true,
                'message' => 'Archivo eliminado correctamente.',
                'archivos' => $reporte->archivos_list,
                'total' => count($reporte->archivos_list),
            ]);

        } catch (\Throwable $e) {
            Log::error('[RDimensional][deleteExcel] Error.', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'Error al eliminar archivo'], 500);
        }
    }

    /**
     * Actualizar datos del encabezado (AJAX)
     */
    public function updateHeader(Request $request)
    {
        if (!in_array(auth()->user()->perfil, [1, 3, 4])) {
            return response()->json(['error' => 'Sin permisos'], 403);
        }

        $request->validate([
            'reporte_id' => 'required|integer|exists:r_dimensionales,id',
            'campo' => 'required|in:observaciones,estado',
            'valor' => 'nullable|string|max:500',
        ]);

        try {
            $reporte = RDimensional::findOrFail($request->reporte_id);
            $reporte->{$request->campo} = $request->valor;
            $reporte->save();

            return response()->json(['success' => true, 'message' => 'Encabezado actualizado']);

        } catch (\Throwable $e) {
            Log::error('[RDimensional][updateHeader] Error.', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'Error al actualizar encabezado'], 500);
        }
    }

    /**
     * Generar y descargar el PDF oficial (DomPDF)
     */
    public function generatePdf($id)
    {
        if (!in_array(auth()->user()->perfil, [1, 3, 4])) {
            abort(403, 'No tienes permisos para acceder a esta sección.');
        }

        $reporte = RDimensional::with(['medidas', 'inspector'])->findOrFail($id);
        $otData = DB::table('orden_trabajo as ot')
            ->join('molduras as m', 'm.id', '=', 'ot.id_moldura')
            ->where('ot.id', $reporte->ot_id)
            ->select('ot.*', 'm.nombre as nombre_moldura')
            ->first();

        $claseData = Clase::where('id_ot', $reporte->ot_id)->where('nombre', $reporte->clase)->first();
        $pedido = $claseData ? (int) $claseData->pedido : (int) ($otData->cantidad ?? 0);
        $totalPiezas = $claseData ? (int) $claseData->piezas : $pedido;
        if ($totalPiezas <= 0) $totalPiezas = $pedido;
        $consignacion = ($totalPiezas > $pedido) ? ($totalPiezas - $pedido) : 0;

        $medidas = $reporte->medidas()->orderBy('numero_pieza')->get();

        // Calcular cuántas piezas están completamente llenas (todos los campos con valor)
        $piezasCompletas = $medidas->filter(function ($m) {
            return self::isMedidaCompleta($m);
        })->count();

        $porcentajeActual = $totalPiezas > 0 ? round(($piezasCompletas / $totalPiezas) * 100, 1) : 0;

        $logoPath = public_path('images/lg_saavedra.png');
        $logoBase64 = file_exists($logoPath) ? 'data:image/png;base64,' . base64_encode(file_get_contents($logoPath)) : '';

        $claseUpper = strtoupper($reporte->clase);
        $esMolde = str_contains($claseUpper, 'MOLDE');
        $diagramaPath = $esMolde
            ? public_path('images/MOLDE_ REPORTEDIMNSIONAL .jpg')
            : public_path('images/BOMBILLO_REPORTEDIMENSIONAL.jpg');
        $diagramaBase64 = file_exists($diagramaPath) ? 'data:image/jpeg;base64,' . base64_encode(file_get_contents($diagramaPath)) : '';

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('calidad.inspeccion_dimensional.pdf', compact(
            'reporte', 'otData', 'claseData', 'pedido', 'totalPiezas', 'consignacion', 'medidas', 'logoBase64', 'diagramaBase64', 'esMolde', 'piezasCompletas', 'porcentajeActual'
        ));

        $pdf->setPaper('letter', 'landscape');
        $pdf->setOptions([
            'isHtml5ParserEnabled' => true,
            'isRemoteEnabled' => true,
            'defaultFont' => 'DejaVu Sans'
        ]);

        $fileName = 'Reporte_Dimensional_OT_' . $reporte->ot_id . '_' . str_replace(' ', '_', $reporte->clase) . '.pdf';
        return $pdf->download($fileName);
    }
}
