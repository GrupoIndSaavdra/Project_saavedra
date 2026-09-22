<?php

namespace App\Http\Controllers;

use App\Models\Orden_trabajo;
use App\Models\SalidaMoldura;
use App\Models\SalidaMolduraPieza;
use App\Models\SalidaMolduraLog;
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
class SalidaMoldurasController extends Controller
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
                  ->orWhere('cl.nombre', 'like', '%BOMBILLO%');
            })
            ->orderByDesc('ot.created_at')
            ->get();

        // Reportes ya creados (últimos 30)
        $reportesExistentes = SalidaMoldura::with('inspector')
            ->orderByDesc('created_at')
            ->limit(30)
            ->get();

        return view('calidad.salida_molduras.index', compact(
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
        $formato = SalidaMoldura::detectarFormato($clase);

        // Obtener datos del inspector actual
        $inspector = auth()->user();

        // Buscar la información real de pedido y piezas (consignación) de la tabla clases
        $claseData = \App\Models\Clase::where('id_ot', $ot)->where('nombre', $clase)->first();
        
        $pedido       = $claseData ? (int) $claseData->pedido : (int) ($ordenTrabajo->cantidad ?? 0);
        $consignacion = $claseData ? (int) $claseData->piezas : 0;

        // Crear o cargar el reporte
        $reporte = SalidaMoldura::firstOrCreate(
            [
                'ot_id' => $ot,
                'clase' => $clase,
            ],
            [
                'nombre_moldura'       => $ordenTrabajo->moldura?->nombre ?? 'Sin moldura',
                'inspector_id'         => $inspector->id,
                'cliente'              => $ordenTrabajo->cliente ?? '',
                'cantidad_pedido'      => $pedido,
                'cantidad_consignacion'=> $consignacion,
                'fecha_inicio'         => now()->toDateString(),
                'formato'              => $formato,
                'observaciones'        => null,
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
        SalidaMolduraLog::registrar(
            reporteId: $reporte->id,
            accion:    'Cargar información',
            campo:     'seleccion_ot_clase',
            anterior:  null,
            nuevo:     "Acceso a OT: {$ot} | Clase: {$clase} | Formato: {$formato}",
            ip:        request()->ip()
        );

        // Total de piezas disponibles en este reporte
        $totalPiezas = $reporte->total_piezas;

        // Cargar piezas existentes indexadas por número
        $piezasExistentes = $reporte->piezas->keyBy('numero_pieza');

        // Generar array completo de celdas (1 hasta $totalPiezas)
        $celdas = [];
        for ($i = 1; $i <= $totalPiezas; $i++) {
            $celdas[$i] = $piezasExistentes->get($i) ?? new SalidaMolduraPieza([
                'salida_moldura_id' => $reporte->id,
                'numero_pieza'      => $i,
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
                  ->orWhere('cl.nombre', 'like', '%BOMBILLO%');
            })
            ->orderByDesc('ot.created_at')
            ->get();

        return view('calidad.salida_molduras.form', compact(
            'reporte',
            'ordenTrabajo',
            'inspector',
            'celdas',
            'totalPiezas',
            'ordenesTrabajo'
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
            'reporte_id'   => 'required|integer|exists:salida_molduras,id',
            'numero_pieza' => 'required|integer|min:1',
            'campo'        => 'required|in:valor_simple,valor_90,valor_lp,descripcion',
            'valor'        => 'nullable|string|max:100',
        ]);

        try {
            DB::beginTransaction();

            $reporte = SalidaMoldura::findOrFail($request->reporte_id);

            // Buscar o crear la pieza en BD
            $pieza = SalidaMolduraPieza::firstOrCreate(
                [
                    'salida_moldura_id' => $reporte->id,
                    'numero_pieza'      => $request->numero_pieza,
                ],
                []
            );

            // Capturar valor anterior para auditoría
            $valorAnterior = $pieza->{$request->campo};
            $valorNuevo    = $request->valor;

            // Solo guardar y loguear si hubo cambio real
            if ($valorAnterior !== $valorNuevo) {
                $pieza->{$request->campo} = $valorNuevo;
                $pieza->save();

                // Registrar en log de auditoría
                SalidaMolduraLog::registrar(
                    reporteId: $reporte->id,
                    accion:    'Editar pieza',
                    campo:     "pieza_{$request->numero_pieza}_{$request->campo}",
                    anterior:  $valorAnterior,
                    nuevo:     $valorNuevo,
                    ip:        $request->ip()
                );
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Guardado',
                'pieza_id'=> $pieza->id,
            ]);

        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('[SalidaMolduras][autosave] Error.', [
                'user'       => auth()->user()->matricula ?? 'N/A',
                'reporte_id' => $request->reporte_id,
                'pieza'      => $request->numero_pieza,
                'error'      => $e->getMessage(),
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
            'reporte_id'    => 'required|integer|exists:salida_molduras,id',
            'observaciones' => 'nullable|string|max:250',
        ]);

        try {
            DB::beginTransaction();

            $reporte = SalidaMoldura::findOrFail($request->reporte_id);
            $anterior = $reporte->observaciones;
            $nueva    = $request->observaciones;

            if ($anterior !== $nueva) {
                $reporte->observaciones = $nueva;
                $reporte->save();

                SalidaMolduraLog::registrar(
                    reporteId: $reporte->id,
                    accion:    'Editar observaciones',
                    campo:     'observaciones',
                    anterior:  $anterior,
                    nuevo:     $nueva,
                    ip:        $request->ip()
                );
            }

            DB::commit();

            return response()->json(['success' => true, 'message' => 'Observaciones guardadas']);

        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('[SalidaMolduras][observaciones] Error.', [
                'user'       => auth()->user()->matricula ?? 'N/A',
                'reporte_id' => $request->reporte_id,
                'error'      => $e->getMessage(),
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
            'reporte_id'            => 'required|integer|exists:salida_molduras,id',
            'cantidad_consignacion' => 'nullable|integer|min:0|max:9999',
            'fecha_inicio'          => 'nullable|date',
        ]);

        try {
            DB::beginTransaction();

            $reporte = SalidaMoldura::findOrFail($request->reporte_id);

            $cambios = [];

            // Actualizar consignación si llegó el dato
            if ($request->has('cantidad_consignacion')) {
                $anterior = $reporte->cantidad_consignacion;
                $nueva    = (int) $request->cantidad_consignacion;
                if ($anterior !== $nueva) {
                    $reporte->cantidad_consignacion = $nueva;
                    $cambios[] = "Consignación: {$anterior} → {$nueva}";
                    SalidaMolduraLog::registrar(
                        reporteId: $reporte->id,
                        accion:    'Editar consignación',
                        campo:     'cantidad_consignacion',
                        anterior:  $anterior,
                        nuevo:     $nueva,
                        ip:        $request->ip()
                    );
                }
            }

            // Actualizar fecha de inicio si llegó el dato
            if ($request->has('fecha_inicio') && $request->fecha_inicio) {
                $anterior = $reporte->fecha_inicio
                    ? \Carbon\Carbon::parse($reporte->fecha_inicio)->toDateString()
                    : null;
                $nueva    = $request->fecha_inicio;
                if ($anterior !== $nueva) {
                    $reporte->fecha_inicio = $nueva;
                    SalidaMolduraLog::registrar(
                        reporteId: $reporte->id,
                        accion:    'Editar fecha de inicio',
                        campo:     'fecha_inicio',
                        anterior:  $anterior,
                        nuevo:     $nueva,
                        ip:        $request->ip()
                    );
                }
            }

            $reporte->save();
            DB::commit();

            return response()->json([
                'success'      => true,
                'total_piezas' => $reporte->total_piezas,
                'message'      => 'Encabezado actualizado',
            ]);

        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('[SalidaMolduras][updateHeader] Error.', [
                'user'       => auth()->user()->matricula ?? 'N/A',
                'reporte_id' => $request->reporte_id,
                'error'      => $e->getMessage(),
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

        // Obtener todos los movimientos de auditoría de la tabla salida_molduras_log
        $logs = SalidaMolduraLog::with(['usuario', 'reporte'])
            ->orderByDesc('created_at')
            ->limit(300)
            ->get()
            ->map(fn($log) => [
                'id'             => $log->id,
                'salida_moldura_id' => $log->salida_moldura_id,
                'ot_id'          => $log->reporte?->ot_id ?? 'N/A',
                'clase'          => $log->reporte?->clase ?? 'N/A',
                'accion'         => $log->accion,
                'campo'          => $log->campo_editado,
                'valor_anterior' => $log->valor_anterior,
                'valor_nuevo'    => $log->valor_nuevo,
                'usuario'        => $log->usuario?->nombre ?? 'Desconocido',
                'ip'             => $log->ip,
                'fecha'          => $log->created_at->format('d/m/Y H:i:s'),
                'fecha_iso'      => $log->created_at->format('Y-m-d'),
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
            'reporte_id' => 'required|integer|exists:salida_molduras,id',
            'desde'      => 'required|integer|min:1',
            'hasta'      => 'required|integer|min:1',
            'valor'      => 'nullable|in:1,0',
        ]);

        try {
            DB::beginTransaction();

            $reporte = SalidaMoldura::findOrFail($request->reporte_id);
            $desde   = min((int)$request->desde, (int)$request->hasta);
            $hasta   = max((int)$request->desde, (int)$request->hasta);
            $valor   = $request->valor === '1' ? '1' : null;

            for ($num = $desde; $num <= $hasta; $num++) {
                SalidaMolduraPieza::updateOrCreate(
                    [
                        'salida_moldura_id' => $reporte->id,
                        'numero_pieza'      => $num,
                    ],
                    [
                        'valor_simple' => $valor,
                    ]
                );
            }

            $accion = $valor === '1' ? 'Marcar rango liberado' : 'Desmarcar rango';
            $texto  = $valor === '1'
                ? "Piezas N° {$desde} a {$hasta} marcadas como liberadas"
                : "Piezas N° {$desde} a {$hasta} desmarcadas";

            SalidaMolduraLog::registrar(
                reporteId: $reporte->id,
                accion:    $accion,
                campo:     'rango_piezas',
                anterior:  null,
                nuevo:     $texto,
                ip:        $request->ip()
            );

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => $texto,
                'desde'   => $desde,
                'hasta'   => $hasta,
                'valor'   => $valor,
            ]);

        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('[SalidaMolduras][bulkUpdate] Error.', [
                'user'       => auth()->user()->matricula ?? 'N/A',
                'reporte_id' => $request->reporte_id,
                'error'      => $e->getMessage(),
            ]);
            return response()->json(['error' => 'Error al actualizar rango'], 500);
        }
    }

    public function generatePdf(int $id)
    {
        if (!in_array(auth()->user()->perfil, [1, 3, 4])) {
            abort(403, 'No tienes permisos para acceder a esta sección.');
        }

        $reporte = clone SalidaMoldura::findOrFail($id);
        
        if (!$reporte->enviado) {
            $reporte->pdf_generado_count += 1;
            $reporte->save();
        }

        $ordenTrabajo = Orden_trabajo::find($reporte->ot_id);
        $inspector = \App\Models\User::find($reporte->inspector_id);
        $totalPiezas = $reporte->total_piezas;
        $celdas = $reporte->piezas()->get()->keyBy('numero_pieza');

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('calidad.salida_molduras.pdf', compact('reporte', 'ordenTrabajo', 'inspector', 'totalPiezas', 'celdas'));
        $pdf->setOption('isRemoteEnabled', true);
        $pdf->setOption('isPhpEnabled', true);
        $pdf->setPaper('letter', 'portrait');

        return $pdf->download("SalidaMolduras_OT_{$reporte->ot_id}_{$reporte->clase}.pdf");
    }

    public function sendReport(int $id)
    {
        if (!in_array(auth()->user()->perfil, [1, 3, 4])) {
            return response()->json(['error' => 'Sin permisos'], 403);
        }

        $reporte = SalidaMoldura::findOrFail($id);

        if ($reporte->enviado) {
            return response()->json(['error' => 'El reporte ya ha sido enviado.'], 400);
        }

        $reporte->enviado = true;
        if ($reporte->pdf_generado_count == 0) {
            $reporte->pdf_generado_count = 1;
        }
        $reporte->save();

        SalidaMolduraLog::registrar(
            reporteId: $reporte->id,
            accion:    'Enviar Reporte',
            campo:     'enviado',
            anterior:  'No',
            nuevo:     'Sí',
            ip:        request()->ip()
        );

        // Generar el PDF y enviar correo
        try {
            $ordenTrabajo = Orden_trabajo::find($reporte->ot_id);
            $inspector = \App\Models\User::find($reporte->inspector_id);
            $totalPiezas = $reporte->total_piezas;
            $celdas = $reporte->piezas()->get()->keyBy('numero_pieza');

            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('calidad.salida_molduras.pdf', compact('reporte', 'ordenTrabajo', 'inspector', 'totalPiezas', 'celdas'));
            $pdf->setOption('isRemoteEnabled', true);
            $pdf->setOption('isPhpEnabled', true);
            $pdf->setPaper('letter', 'portrait');

            \Illuminate\Support\Facades\Mail::send([], [], function ($message) use ($reporte, $pdf) {
                $message->to('sistemas@grupo-saavedra.com.mx')
                    ->subject("Reporte de Salida de Molduras - OT {$reporte->ot_id} ({$reporte->clase})")
                    ->html('<p>Se adjunta el reporte de salida de molduras.</p>')
                    ->attachData($pdf->output(), "SalidaMolduras_OT_{$reporte->ot_id}_{$reporte->clase}.pdf", [
                        'mime' => 'application/pdf',
                    ]);
            });

        } catch (\Exception $e) {
            Log::error('[SalidaMolduras][sendReport] Error al enviar correo.', [
                'reporte_id' => $reporte->id,
                'error'      => $e->getMessage()
            ]);
            // Aun si falla el correo, marcamos como enviado
        }

        return response()->json(['success' => true]);
    }

    public function unlockMaster(Request $request, int $id)
    {
        $request->validate([
            'password' => 'required|string',
        ]);

        $reporte = SalidaMoldura::findOrFail($id);

        $usuario = auth()->user();
        $isMaster = \App\Models\User::whereIn('perfil', [1, 3])
            ->get()
            ->first(function ($u) use ($request) {
                return \Illuminate\Support\Facades\Hash::check($request->password, $u->password);
            });

        if (!$isMaster) {
            return response()->json(['error' => 'Contraseña incorrecta o el usuario no tiene permisos Master.'], 403);
        }

        $reporte->update(['enviado' => false]);

        SalidaMolduraLog::registrar(
            reporteId: $reporte->id,
            accion:    'Desbloquear Reporte',
            campo:     'enviado',
            anterior:  'Sí',
            nuevo:     'No (Desbloqueado por Master)',
            ip:        $request->ip()
        );

        return response()->json(['success' => true]);
    }
}
