<?php

namespace App\Http\Controllers;

use App\Models\Clase;
use App\Models\Orden_trabajo;
use App\Models\RVisual;
use App\Models\RVisualMedida;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

/**
 * Controlador para la vista "Inspección Visual — Desplazamiento de Molduras" (Calidad).
 * Formato Oficial: 4CAL-01
 *
 * Perfiles con acceso:
 *   1 = Administrador (lectura + escritura)
 *   3 = Master        (lectura + escritura)
 *   4 = Calidad       (lectura + escritura)
 */
class InspeccionVisualController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Vista principal: selector de OT y listado de reportes visuales existentes
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

        // Obtener reportes visuales registrados
        $reportesExistentes = RVisual::with('inspector')
            ->orderByDesc('updated_at')
            ->limit(100)
            ->get();

        return view('calidad.inspeccion_visual.index', compact('ordenesTrabajo', 'reportesExistentes'));
    }

    /**
     * Vista de detalle / formulario de inspección visual para una OT y Clase específicas (4CAL-01)
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
            return redirect()->route('calidad.inspeccion_visual.index')
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

        // Cargar o crear el reporte visual (4CAL-01)
        $reporte = RVisual::firstOrCreate(
            [
                'ot_id' => $ot,
                'clase' => $clase,
            ],
            [
                'nombre_moldura' => $otData->nombre_moldura ?? 'Sin moldura',
                'cliente' => $cliente,
                'codigo' => null,
                'sistema' => 'P.S.B.A.',
                'codigo_formato' => '4CAL-01',
                'nivel_revision' => 0,
                'fecha_elaboracion' => now()->toDateString(),
                'fecha_revision' => '2025-11-10',
                'fecha_aprobacion' => '2025-11-10',
                'vol_molde_sd' => 'NA',
                'vol_conjunto_fisico' => 'NA ml',
                'vol_bombillo_sd' => null,
                'vol_conjunto_sd' => 'NA',
                'vol_desplazado_max' => null,
                'vol_desplazado_ideal' => null,
                'vol_desplazado_min' => null,
                'observaciones_generales' => null,
                'inspector_id' => auth()->id(),
                'estado' => 'en_proceso',
            ]
        );

        // Si el reporte no tiene medidas registradas, crear 1 fila inicial por defecto
        if ($reporte->medidas()->count() === 0) {
            RVisualMedida::create([
                'r_visual_id' => $reporte->id,
                'numero_pieza' => '1',
                'temp_agua' => '20°',
            ]);
        }

        $medidas = $reporte->medidas()->get();

        return view('calidad.inspeccion_visual.show', compact(
            'reporte',
            'medidas',
            'otData',
            'clase',
            'pedido',
            'consignacion',
            'totalPiezas'
        ));
    }

    /**
     * AJAX: Autoguardar celda individual de la tabla de medidas
     */
    public function autosaveMedida(Request $request)
    {
        $request->validate([
            'reporte_id' => 'required|integer',
            'medida_id' => 'required|integer',
            'campo' => 'required|string',
            'valor' => 'nullable|string',
        ]);

        $medida = RVisualMedida::where('id', $request->medida_id)
            ->where('r_visual_id', $request->reporte_id)
            ->first();

        if (!$medida) {
            return response()->json(['success' => false, 'error' => 'Medida no encontrada.'], 404);
        }

        $campo = $request->campo;
        $valor = $request->valor;

        $camposPermitidos = [
            'numero_pieza',
            'temp_agua',
            'vol_real',
            'dif_vs_vol_ideal',
            'observaciones',
        ];

        if (!in_array($campo, $camposPermitidos)) {
            return response()->json(['success' => false, 'error' => 'Campo no permitido.'], 422);
        }

        $medida->{$campo} = $valor !== '' ? $valor : null;
        $medida->save();

        return response()->json([
            'success' => true,
            'medida_id' => $medida->id,
        ]);
    }

    /**
     * AJAX: Agregar una nueva fila de medición al reporte
     */
    public function addRow(Request $request)
    {
        $request->validate([
            'reporte_id' => 'required|integer',
            'numero_pieza' => 'nullable|string',
        ]);

        $reporte = RVisual::findOrFail($request->reporte_id);
        $claseData = \App\Models\Clase::where('id_ot', $reporte->ot_id)->where('nombre', $reporte->clase)->first();
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

        $medida = RVisualMedida::create([
            'r_visual_id' => $reporte->id,
            'numero_pieza' => $numeroPieza,
            'temp_agua' => '20°',
        ]);

        return response()->json([
            'success' => true,
            'medida' => $medida,
        ]);
    }

    /**
     * AJAX: Eliminar una fila de medición del reporte
     */
    public function deleteRow(Request $request)
    {
        $request->validate([
            'reporte_id' => 'required|integer',
            'medida_id' => 'required|integer',
        ]);

        $medida = RVisualMedida::where('id', $request->medida_id)
            ->where('r_visual_id', $request->reporte_id)
            ->first();

        if ($medida) {
            $medida->delete();
        }

        return response()->json([
            'success' => true,
        ]);
    }

    /**
     * AJAX: Actualizar campos del encabezado, especificaciones de volumen y observaciones
     */
    public function updateHeader(Request $request)
    {
        $request->validate([
            'reporte_id' => 'required|integer',
            'campo' => 'required|string',
            'valor' => 'nullable|string',
        ]);

        $reporte = RVisual::findOrFail($request->reporte_id);

        $camposPermitidos = [
            'codigo',
            'sistema',
            'vol_molde_sd',
            'vol_conjunto_fisico',
            'vol_bombillo_sd',
            'vol_conjunto_sd',
            'vol_desplazado_max',
            'vol_desplazado_ideal',
            'vol_desplazado_min',
            'observaciones_generales',
        ];

        if (!in_array($request->campo, $camposPermitidos)) {
            return response()->json(['success' => false, 'error' => 'Campo no permitido.'], 422);
        }

        $reporte->{$request->campo} = $request->valor !== '' ? $request->valor : null;
        $reporte->save();

        return response()->json([
            'success' => true,
        ]);
    }

    /**
     * AJAX: Subir archivo adjunto (PDF / Excel) al reporte volumétrico - Hasta 4 archivos
     */
    public function uploadArchivo(Request $request)
    {
        if (!in_array(auth()->user()->perfil, [1, 3, 4])) {
            return response()->json(['error' => 'Sin permisos'], 403);
        }

        $request->validate([
            'reporte_id' => 'required|integer|exists:r_visuales,id',
            'archivo' => 'required|file|mimes:pdf,xlsx,xls,csv,txt|max:30720',
        ]);

        try {
            $reporte = RVisual::findOrFail($request->reporte_id);
            $archivos = $reporte->archivos_list;

            if (count($archivos) >= 4) {
                return response()->json(['error' => 'Has alcanzado el límite máximo de 4 archivos adjuntos para este reporte.'], 422);
            }

            $file = $request->file('archivo');
            $nombreOriginal = $file->getClientOriginalName();
            $safeOt = preg_replace('/[^A-Za-z0-9_\-]/', '_', $reporte->ot_id);
            $safeClase = preg_replace('/[^A-Za-z0-9_\-]/', '_', $reporte->clase);
            $fecha = now()->format('Ymd_His');
            $extension = $file->getClientOriginalExtension();

            $nextId = count($archivos) > 0 ? (max(array_column($archivos, 'id')) + 1) : 1;

            $folder = "DOCUMENTACION_GIS/REPORTES_VOLUMETRICOS/OT_{$safeOt}/{$safeClase}";
            $filename = "Reporte_Volumetrico_OT_{$safeOt}_{$safeClase}_{$fecha}_{$nextId}.{$extension}";
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
            $reporte->archivo_ruta = json_encode(array_values($archivos), JSON_UNESCAPED_UNICODE);
            $reporte->archivo_nombre = json_encode(array_column($archivos, 'nombre'), JSON_UNESCAPED_UNICODE);
            $reporte->save();

            $token = md5($reporte->id . '_' . config('app.key'));
            $archivosFormat = array_map(function ($a) use ($reporte, $token) {
                $ext = strtolower(pathinfo($a['nombre'] ?? '', PATHINFO_EXTENSION));
                $isExcel = in_array($ext, ['xlsx', 'xls', 'csv']);
                $openUrl = url("/calidad/inspeccion-visual/open-file/{$reporte->id}?file_id={$a['id']}&token={$token}");
                $viewUrl = route('calidad.inspeccion_visual.view_archivo', ['id' => $reporte->id, 'file_id' => $a['id']]);
                $downloadUrl = route('calidad.inspeccion_visual.download_archivo', ['id' => $reporte->id, 'file_id' => $a['id']]);

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
            Log::error('[RVisual][uploadArchivo] Error.', ['error' => $e->getMessage()]);
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

        $reporte = RVisual::findOrFail($id);
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
    public function viewArchivo(Request $request, int $id)
    {
        if (!in_array(auth()->user()->perfil, [1, 3, 4])) {
            abort(403, 'No tienes permisos para acceder a esta sección.');
        }

        $reporte = RVisual::findOrFail($id);
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
    public function downloadArchivo(Request $request, int $id)
    {
        if (!in_array(auth()->user()->perfil, [1, 3, 4])) {
            abort(403, 'No tienes permisos para acceder a esta sección.');
        }

        $reporte = RVisual::findOrFail($id);
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

        return Storage::disk('local')->download($archivoTarget['ruta'], $archivoTarget['nombre'] ?? 'Reporte_Volumetrico_Adjunto');
    }

    /**
     * AJAX: Eliminar archivo adjunto con clave maestra CALIDAD2026
     */
    public function deleteArchivo(Request $request)
    {
        if (!in_array(auth()->user()->perfil, [1, 3, 4])) {
            return response()->json(['error' => 'Sin permisos'], 403);
        }

        $request->validate([
            'reporte_id' => 'required|integer|exists:r_visuales,id',
            'clave_maestra' => 'required|string',
            'file_id' => 'nullable',
        ]);

        if (trim($request->clave_maestra) !== 'CALIDAD2026') {
            return response()->json(['error' => 'Clave maestra incorrecta. No tienes autorización para eliminar este archivo.'], 403);
        }

        try {
            $reporte = RVisual::findOrFail($request->reporte_id);
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
                $reporte->archivo_ruta = null;
                $reporte->archivo_nombre = null;
            } else {
                $reporte->archivo_ruta = json_encode(array_values($archivos), JSON_UNESCAPED_UNICODE);
                $reporte->archivo_nombre = json_encode(array_column($archivos, 'nombre'), JSON_UNESCAPED_UNICODE);
            }
            $reporte->save();

            return response()->json([
                'success' => true,
                'message' => 'Archivo eliminado correctamente.',
                'archivos' => $reporte->archivos_list,
                'total' => count($reporte->archivos_list),
            ]);

        } catch (\Throwable $e) {
            Log::error('[RVisual][deleteArchivo] Error.', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'Error al eliminar archivo'], 500);
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

        $reporte = \App\Models\RVisual::with(['medidas', 'inspector'])->findOrFail($id);
        $otData = DB::table('orden_trabajo as ot')
            ->join('molduras as m', 'm.id', '=', 'ot.id_moldura')
            ->where('ot.id', $reporte->ot_id)
            ->select('ot.*', 'm.nombre as nombre_moldura')
            ->first();

        $claseData = \App\Models\Clase::where('id_ot', $reporte->ot_id)->where('nombre', $reporte->clase)->first();
        $pedido = $claseData ? (int) $claseData->pedido : (int) ($otData->cantidad ?? 0);
        $totalPiezas = $claseData ? (int) $claseData->piezas : $pedido;
        if ($totalPiezas <= 0) $totalPiezas = $pedido;
        $consignacion = ($totalPiezas > $pedido) ? ($totalPiezas - $pedido) : 0;

        $medidas = $reporte->medidas()->orderBy('numero_pieza')->get();

        // Calcular piezas completas (que tengan volumen real registrado)
        $piezasCompletas = $medidas->filter(function ($m) {
            return !is_null($m->vol_real) && trim((string) $m->vol_real) !== '';
        })->count();

        $porcentajeActual = $totalPiezas > 0 ? round(($piezasCompletas / $totalPiezas) * 100, 1) : 0;

        $logoPath = public_path('images/lg_saavedra.png');
        $logoBase64 = file_exists($logoPath) ? 'data:image/png;base64,' . base64_encode(file_get_contents($logoPath)) : '';

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('calidad.inspeccion_visual.pdf', compact(
            'reporte', 'otData', 'claseData', 'pedido', 'totalPiezas', 'consignacion', 'medidas', 'logoBase64', 'piezasCompletas', 'porcentajeActual'
        ));

        $pdf->setPaper('letter', 'landscape');
        $pdf->setOptions([
            'isHtml5ParserEnabled' => true,
            'isRemoteEnabled' => true,
            'defaultFont' => 'DejaVu Sans'
        ]);

        $fileName = 'Reporte_Volumetrico_OT_' . $reporte->ot_id . '_' . str_replace(' ', '_', $reporte->clase) . '.pdf';
        return $pdf->download($fileName);
    }
}
