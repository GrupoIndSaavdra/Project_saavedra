<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\SystemLog;
use App\Models\Clase;
use App\Models\Metas;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;

class SystemLogController extends Controller
{
    public function store(Request $request)
    {
        $now = now();
        $action = $request->action;
        $details = $request->details;
        $h_inicio = $request->h_inicio;
        $h_termino = $request->h_termino ?: $now->format('H:i:s'); // Usar término manual si viene del front (RANGOS)

        // --- REGLA DE ORO: TRATAMIENTO DE EVENTOS DE RANGO ESPECIALES ---

        // A. Sincronización de Piezas (Log de Maquinado)
        // Solo buscamos un inicio histórico si el frontend NO envió un h_inicio válido
        if (($action === 'Captura Medida' || $action === 'Captura Sospechosa') && (!$h_inicio || $h_inicio === 'N/A')) {
            $syncLog = SystemLog::where('user_matricula', Auth::check() ? Auth::user()->matricula : null)
                ->where('action', 'Proceso Correcto')
                ->where('details', 'LIKE', '%sincronizó los datos técnicos%')
                ->whereDate('created_at', now()->toDateString())
                ->orderBy('created_at', 'desc')
                ->first();

            if ($syncLog) {
                $h_inicio = $syncLog->created_at->format('H:i:s');
            } else {
                $h_inicio = $now->copy()->subMinute()->format('H:i:s');
            }
        }

        // B. Opción 3: Resumen de la Jornada (Terminar Reporte)
        // Buscamos el inicio de reporte para calcular el tiempo total acumulado
        if ($action === 'Terminar Reporte' || $action === 'Terminar jornada') {
            $startReportLog = SystemLog::where('user_matricula', Auth::check() ? Auth::user()->matricula : null)
                ->where('action', 'Inicio de Reporte')
                ->whereDate('created_at', now()->toDateString())
                ->orderBy('created_at', 'desc')
                ->first();

            if ($startReportLog) {
                $h_inicio = $startReportLog->created_at->format('H:i:s');
                $details = "El operador finalizó oficialmente el reporte de producción. Resumen de tiempo acumulado en el turno.";
            }
        }

        // C. Opción 2: Tiempos de Espera (Supervisor)
        // Se recibe h_inicio_solicitud desde el formulario de verificación
        if ($action === 'Autorización de Edición' || $request->has('h_inicio_solicitud')) {
            if ($request->has('h_inicio_solicitud')) {
                $h_inicio = $request->h_inicio_solicitud;
                $action = 'Autorización de Edición'; // Estandarizar nombre de acción
            }
        }

        // 1. Lógica de Alerta (Regla de los 5 minutos)
        // Solo para piezas (Captura Medida)
        if ($action === 'Captura Medida') {
            $lastLog = SystemLog::where('user_matricula', Auth::user()->matricula)
                ->whereIn('action', ['Captura Medida', 'Captura Sospechosa', 'Captura Crítica'])
                ->orderBy('created_at', 'desc')
                ->first();

            if ($lastLog) {
                $isSameSet = false;

                // Extraer base del número de pieza (ej: '101' de '101H')
                if ($request->n_pieza && $lastLog->n_pieza) {
                    $currentBase = preg_replace('/[HMJhmj]$/', '', $request->n_pieza);
                    $lastBase = preg_replace('/[HMJhmj]$/', '', $lastLog->n_pieza);
                    
                    // Si es la misma base (ej: 101H vs 101M o 101H vs 101H (2da pasada)), se considera el mismo juego
                    if ($currentBase === $lastBase) {
                        $isSameSet = true;
                    }
                }

                if (!$isSameSet) {
                    $diffMins = $now->diffInMinutes($lastLog->created_at);
                    if ($diffMins < 5) {
                        // REGLA DE ORO DE AUDITORÍA:
                        // Si ya tiene 2 sospechas previas y esta es la 3ra, se vuelve CRÍTICA
                        $recentSuspiciousCount = SystemLog::where('user_matricula', Auth::user()->matricula)
                            ->whereDate('created_at', now()->toDateString())
                            ->whereIn('action', ['Captura Sospechosa', 'Captura Crítica'])
                            ->where('created_at', '>', now()->subHours(8)) // Solo turno actual
                            ->count();

                        if ($recentSuspiciousCount >= 2) {
                            $action = 'Captura Crítica';
                        } else {
                            $action = 'Captura Sospechosa';
                        }
                    }
                }
            }
        }

        // 2. Eventos Puntuales (Inicio = Término = created_at)
        $pointEvents = [
            'Inicio de Sesión', 'Cierre de Sesión', 'Inicio de Reporte', 'Terminar Reporte', 
            'Carga de Formulario de Producción', 'Login Inspector Calidad', 'Consulta Documentación Técnica',
            'Nuevo reporte', 'Nueva Meta Creada', 'Ingreso a Meta Existente', 
            'Selección de OT', 'Selección de Clase', 'Selección de Proceso', 'Selección de Pieza',
            'Autorización de Edición'
        ];
        if (in_array($action, $pointEvents)) {
            $h_inicio = $h_termino;
        }

        // 3. Limpieza de Formato (Asegurar que los detalles tengan separadores claros)
        // Si el detalle viene saturado, podemos formatearlo aquí si fuera necesario.

        SystemLog::create([
            'user_matricula' => Auth::check() ? Auth::user()->matricula : null,
            'action' => $action,
            'details' => $details,
            'ot' => $request->ot,
            'clase' => $request->clase,
            'proceso' => $request->proceso,
            'maquina' => $request->maquina,
            'n_pieza' => $request->n_pieza,
            'h_inicio' => $h_inicio,
            'h_termino' => $h_termino,
            'id_ot' => $request->id_ot,
            'id_clase' => $request->id_clase,
        ]);

        // 4. REINICIAR CRONÓMETRO DE PRODUCTIVIDAD POR ACTIVIDAD TÉCNICA
        // Solo reinicia en fases de espera (Menú/Formulario). 
        // En MAQUINADO, el tiempo estándar debe respetarse y solo se reinicia al guardar la pieza.
        if ($action === 'Consulta Documentación Técnica') {
            $user = Auth::user();
            if ($user && $user->perfil == 2 && $user->prod_status !== 'machining') {
                $user->update(['prod_start_at' => now()->setSeconds(0), 'prod_locked_type' => null]);
            }
        }

        return response()->json(['success' => true]);
    }

    public function index(Request $request)
    {
        // 1. Obtener valores ÚNICOS para los filtros usando consultas eficientes y el índice de la DB
        // Esto evita cargar miles de registros en memoria solo para llenar un dropdown
        $filtrosDisponibles = [
            'ot' => SystemLog::distinct()->whereNotNull('ot')->pluck('ot')->sort()->values(),
            'clase' => SystemLog::distinct()->whereNotNull('clase')->pluck('clase')->sort()->values(),
            'proceso' => SystemLog::distinct()->whereNotNull('proceso')->pluck('proceso')->sort()->values(),
            'maquina' => SystemLog::distinct()->whereNotNull('maquina')->pluck('maquina')->sort()->values(),
            'action' => SystemLog::distinct()->whereNotNull('action')->pluck('action')->sort()->values(),
        ];

        // Obtener operadores únicos de forma eficiente
        $filtrosDisponibles['operador'] = SystemLog::select('user_matricula', 'users.nombre', 'users.a_paterno')
            ->leftJoin('users', 'system_logs.user_matricula', '=', 'users.matricula')
            ->whereNotNull('user_matricula')
            ->distinct()
            ->get()
            ->map(fn($o) => (object)[
                'matricula' => $o->user_matricula,
                'nombre' => $o->nombre,
                'a_paterno' => $o->a_paterno
            ]);

        // Obtener N# Pieza (Juegos) simplificado
        $filtrosDisponibles['n_pieza'] = SystemLog::distinct()
            ->whereNotNull('n_pieza')
            ->where('n_pieza', 'NOT LIKE', '%/%')
            ->pluck('n_pieza')
            ->map(function($p) {
                $num = preg_replace('/[a-zA-Z]/', '', (string)$p);
                return $num ? $num . "J" : null;
            })
            ->filter()->unique()->sort()->values();

        // 2. Preparar la consulta principal con paginación
        $query = SystemLog::select('system_logs.*', 'users.nombre', 'users.a_paterno', 'users.a_materno')
            ->leftJoin('users', 'system_logs.user_matricula', '=', 'users.matricula');

        // --- APLICAR FILTROS ---
        if ($request->filled('ot') && $request->ot !== 'Todos') {
            if (preg_match('/^(\d+)/', $request->ot, $matches)) {
                $query->where('ot', 'LIKE', $matches[1] . '%');
            } else {
                $query->where('ot', $request->ot);
            }
        }
        if ($request->filled('clase') && $request->clase !== 'Todos') $query->where('clase', $request->clase);
        if ($request->filled('proceso') && $request->proceso !== 'Todos') $query->where('proceso', $request->proceso);
        if ($request->filled('maquina') && $request->maquina !== 'Todos') $query->where('maquina', $request->maquina);
        if ($request->filled('action') && $request->action !== 'Todos') $query->where('action', $request->action);
        if ($request->filled('operador') && $request->operador !== 'Todos') $query->where('user_matricula', $request->operador);
        if ($request->filled('dateFrom')) $query->whereDate('system_logs.created_at', '>=', $request->dateFrom);
        if ($request->filled('dateTo')) $query->whereDate('system_logs.created_at', '<=', $request->dateTo);
        
        if ($request->filled('n_pieza') && $request->n_pieza !== 'Todos') {
            $baseSearch = preg_replace('/[a-zA-Z]/', '', $request->n_pieza);
            $query->where('n_pieza', 'REGEXP', '^' . $baseSearch . '[a-zA-Z]?$');
        }

        // --- FILTRO DE AUDITORÍA (NIVELES DE SOSPECHA) ---
        if ($request->filled('audit_status') && $request->audit_status !== 'Todos') {
            if ($request->audit_status === 'Críticos') {
                $query->where('action', 'Captura Crítica');
            } elseif ($request->audit_status === 'Sospechosos') {
                $query->where('action', 'Captura Sospechosa');
            } elseif ($request->audit_status === 'Válidos') {
                $query->whereNotIn('action', ['Captura Sospechosa', 'Captura Crítica']);
            }
        }

        // Ordenar por lo más reciente
        $query->orderBy('system_logs.created_at', 'desc');

        // 3. PAGINACIÓN: Solo traemos 500 registros por vez para evitar saturación
        // IMPORTANTE: withQueryString() mantiene los filtros activos en los enlaces de paginación
        $limit = 500;
        $paginator = $query->paginate($limit)->appends(request()->query());
        $logsRaw = $paginator->items();

        // 4. Mapear para el renderizado (Procesamiento ligero)
        $logsRender = [];
        foreach ($logsRaw as $log) {
            // Lógica simplificada de sospecha (basada en el flag guardado o en cálculo de proximidad rápida)
            $isSuspicious = ($log->action === 'Captura Sospechosa' || $log->action === 'Captura Crítica');
            
            // Definición de acciones con tiempo
            $rangeActions = [
                'Captura Medida', 'Captura Sospechosa', 'Captura Crítica', 'Consulta Dibujos Técnicos', 
                'Consulta Documentación Técnica', 'Autorización de Edición', 'Terminar Reporte', 
                'Terminar jornada', 'Proceso Correcto', 'Exceso de Tiempo', 
                'Abandono de Liberación', 'Exceso de Tiempo de Maquinado', 'Inicio de Reporte Pendiente',
                'Liberación por Calidad', 'Rechazo por Calidad'
            ];
            
            $showTimes = in_array($log->action, $rangeActions);
            $tiempoTotal = 'N/A';

            if ($showTimes) {
                $h_inicio = $log->h_inicio;
                $h_termino = $log->h_termino ?? $log->created_at->format('H:i:s');
                if ($h_inicio && $h_termino && $h_inicio !== $h_termino) {
                    try {
                        $start = \Carbon\Carbon::parse($h_inicio);
                        $end = \Carbon\Carbon::parse($h_termino);
                        $tiempoTotal = $start->diff($end)->format('%H:%I:%S');
                    } catch (\Exception $e) { $tiempoTotal = '00:00:00'; }
                } else if ($h_inicio === $h_termino) {
                    $tiempoTotal = '00:00:00';
                }
            }

            $logsRender[] = [
                'id' => $log->id,
                'date' => $log->created_at->format('Y-m-d'),
                'time' => $log->created_at->format('H:i:s'),
                'hora_inicio' => $log->h_inicio ?? 'N/A',
                'hora_termino' => $log->h_termino ?? ($showTimes ? $log->created_at->format('H:i:s') : 'N/A'),
                'tiempo_total' => $tiempoTotal,
                'operador' => $log->user_matricula,
                'operador_nombre' => ($log->nombre . ' ' . $log->a_paterno) ?: 'Sistema',
                'action' => $log->action,
                'details' => $log->details,
                'ot' => $log->ot ?? 'N/A',
                'clase' => $log->clase ?? 'N/A',
                'proceso' => $log->proceso ?? 'N/A',
                'maquina' => $log->maquina ?? 'N/A',
                'n_juego' => $log->n_pieza ?: 'N/A',
                'is_suspicious' => $isSuspicious,
                'is_critical' => ($log->action === 'Captura Crítica'),
            ];
        }

        // 5. Preparar respuesta
        $totalFound = $paginator->total();
        $selectedItems = $request->only(['ot', 'clase', 'proceso', 'maquina', 'action', 'operador', 'dateFrom', 'dateTo', 'n_pieza', 'audit_status']);

        if ($request->ajax()) {
            return response()->json([
                'logsData' => $logsRender,
                'next_page' => $paginator->nextPageUrl(),
                'has_more' => $paginator->hasMorePages(),
                'total_found' => $totalFound
            ]);
        }

        if ($request->filled('generate_pdf')) {
            // Generar PDF con los mismos resultados filtrados (pero sin paginar para el PDF completo)
            $pdfLogs = $query->get()->map(function($log) {
                // Mismo mapeo que arriba para consistencia en el PDF
                return [
                    'date' => $log->created_at->format('Y-m-d'),
                    'time' => $log->created_at->format('H:i:s'),
                    'hora_inicio' => $log->h_inicio ?? 'N/A',
                    'hora_termino' => $log->h_termino ?? $log->created_at->format('H:i:s'),
                    'operador' => $log->user_matricula,
                    'operador_nombre' => ($log->nombre . ' ' . $log->a_paterno) ?: 'Sistema',
                    'action' => $log->action,
                    'details' => $log->details,
                    'ot' => $log->ot ?? 'N/A',
                    'clase' => $log->clase ?? 'N/A',
                    'proceso' => $log->proceso ?? 'N/A',
                    'maquina' => $log->maquina ?? 'N/A',
                    'n_juego' => ($log->n_pieza && preg_match('/[HM]$/i', $log->n_pieza)) ? preg_replace('/[HM]$/i', 'J', $log->n_pieza) : ($log->n_pieza ?? 'N/A'),
                    'tiempo_total' => 'N/A' // Opcional calcularlo aquí también
                ];
            });

            $pdf = Pdf::loadView('reports.systemLogsPdf', ['logsRender' => $pdfLogs, 'selectedItems' => $selectedItems]);
            return $pdf->download($this->generatePdfFilename($selectedItems, "Logs de Sistema"));
        }

        return view('reports.systemLogs', [
            'logsRender' => $logsRender,
            'filtrosDisponibles' => $filtrosDisponibles,
            'selectedItems' => $selectedItems,
            'hasMore' => $paginator->hasMorePages(),
            'nextPage' => $paginator->nextPageUrl(),
            'totalFound' => $totalFound
        ]);
    }


    public function generatePdfFilename($selectedItems, $reportType)
    {
        $parts = [];
        $date = date('d-m-Y');

        if (isset($selectedItems['ot']) && $selectedItems['ot'] !== 'Todos') {
            $parts[] = "OT " . $selectedItems['ot'];
        }
        if (isset($selectedItems['clase']) && $selectedItems['clase'] !== 'Todos') {
            $parts[] = $selectedItems['clase'];
        }
        if (isset($selectedItems['operador']) && $selectedItems['operador'] !== 'Todos') {
            $parts[] = "Op " . $selectedItems['operador'];
        }
        if (isset($selectedItems['action']) && $selectedItems['action'] !== 'Todos') {
            $parts[] = $selectedItems['action'];
        }

        if (count($parts) > 0) {
            return implode(' - ', $parts) . " - " . $date . ".pdf";
        } else {
            return "Reporte de " . $reportType . " General - " . $date . ".pdf";
        }
    }

    /**
     * Activa manualmente el comando de depuración y exportación de logs.
     */
    public function purge(Request $request)
    {
        try {
            // Solo administradores (perfil 1) o sistemas
            if (Auth::user()->perfil != 1) {
                return response()->json([
                    'success' => false,
                    'message' => 'No tiene permisos para realizar esta acción.'
                ], 403);
            }

            // Ejecutar el comando de consola
            \Illuminate\Support\Facades\Artisan::call('app:depurar-logs');
            $output = \Illuminate\Support\Facades\Artisan::output();

            return response()->json([
                'success' => true,
                'message' => 'Depuración manual completada con éxito. Los logs han sido respaldados y la tabla se ha limpiado.',
                'output' => $output
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al ejecutar la depuración: ' . $e->getMessage()
            ], 500);
        }
    }
}