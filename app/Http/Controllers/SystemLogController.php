<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\SystemLog;
use App\Models\Clase;
use App\Models\Metas;
use App\Models\User;
use App\Models\Orden_trabajo;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Barryvdh\DomPDF\Facade\Pdf;

class SystemLogController extends Controller
{
        /**
     * @param Request $request
     */
    public function store(Request $request)
    {
        $action = $request->action;

        // 1. FILTRAR SPAM TRANSACCIONAL (TELEMETRÍA DE UI)
        $ignoredActions = [
            'Carga de Formulario de Producción', 'Selección de OT', 'Selección de Clase', 
            'Selección de Proceso', 'Selección de Pieza',
            'Consulta Dibujos Técnicos', 'Consulta Documentación Técnica'
        ];

        if (in_array($action, $ignoredActions)) {
            return response()->json(['success' => true, 'message' => 'Evento de telemetría omitido']);
        }

        $now = now();
        $details = str_replace([' (Parte H + M)', 'ALERTA: Tiempo insuficiente entre piezas diferentes (0 min)'], '', $request->details);
        $h_inicio = $request->h_inicio;
        $h_termino = $request->h_termino ?: $now->format('H:i:s');
        $n_pieza = $request->n_pieza;

        // 3. ESTANDARIZACIÓN DE NOMENCLATURA (Catálogo Maestro)
        $otString = $request->ot;
        $idOtSanitized = is_numeric($request->id_ot) ? (int)$request->id_ot : null;
        if ($idOtSanitized) {
            $otModel = Orden_trabajo::query()->with('moldura')->where('id', $idOtSanitized)->first();
            if ($otModel && $otModel->moldura) {
                $otString = "{$otModel->id} - {$otModel->moldura->nombre}";
            }
        }

        $claseString = $request->clase;
        $idClaseSanitized = is_numeric($request->id_clase) ? (int)$request->id_clase : null;
        if ($idClaseSanitized) {
            $claseModel = Clase::query()->where('id', $idClaseSanitized)->first();
            if ($claseModel) {
                $claseString = $claseModel->nombre;
            }
        }
        if (preg_match('/^\d+$/', $claseString)) $claseString = 'N/A';

        // 4. LIMPIEZA DE MÁQUINAS (IDs Compuestos/Sucios)
        $maquina = $request->maquina;
        if (strpos($maquina, '_') !== false) {
            $maquina = explode('_', $maquina)[0];
        }

        // 5. TRATAMIENTO DE EVENTOS DE RANGO Y AUDITORÍA
        if (($action === 'Captura Medida' || $action === 'Captura Sospechosa' || $action === 'Captura Crítica') && (!$h_inicio || $h_inicio === 'N/A')) {
            $syncLog = SystemLog::query()->where('user_matricula', Auth::check() ? Auth::user()->matricula : null)
                ->whereDate('created_at', now()->toDateString())
                ->where(function($q) {
                    $q->where('details', 'LIKE', '%sincronizó%')->orWhere('action', 'Proceso Correcto');
                })
                ->orderBy('created_at', 'desc')
                ->first();

            if ($syncLog) {
                $h_inicio = $syncLog->created_at->format('H:i:s');
            } else {
                $h_inicio = $now->copy()->subMinute()->format('H:i:s');
            }
        }

        if ($action === 'Autorización de Edición' || $request->has('h_inicio_solicitud')) {
            $h_inicio = $request->h_inicio_solicitud ?: $h_inicio;
        }

        // LÓGICA DE CONSOLIDACIÓN (H+M) Y SOSPECHA
        if (($action === 'Captura Medida' || $action === 'Captura Sospechosa' || $action === 'Captura Crítica') && Auth::check()) {
            $n_pieza = strtoupper($request->n_pieza);
            $isMacho = str_ends_with($n_pieza, 'M');
            $isHembra = str_ends_with($n_pieza, 'H');

            // 1. SI ES MACHO: Pausamos el log hasta que llegue la Hembra
            if ($isMacho) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'Log pausado para consolidación (Macho)',
                    'action' => $action
                ]);
            }

            // 2. SI ES HEMBRA: Buscamos el inicio del Macho para consolidar el ciclo
            if ($isHembra) {
                $baseNum = preg_replace('/[H]$/', '', $n_pieza);
                $machoName = $baseNum . 'M';
                $modelName = $this->getModelForProcess($request->proceso, $request->id_clase);
                
                if ($modelName) {
                    $machoPiece = $modelName::query()->where('n_pieza', $machoName)
                        ->where('id_meta', $request->meta) // El JS envía el ID en el campo 'meta'
                        ->orWhere(function($q) use ($machoName, $request) {
                             $q->where('n_pieza', $machoName)
                               ->where('id_proceso', 'LIKE', '%' . $request->id_ot . '%');
                        })
                        ->orderBy('created_at', 'desc')
                        ->first();
                    
                    if ($machoPiece && $machoPiece->h_inicio) {
                        $h_inicio = $machoPiece->h_inicio;
                        $n_pieza = $baseNum . 'J';
                        $details = "El operador completó el maquinado del juego {$baseNum} a las " . substr($h_termino, 0, 5);
                    }
                }
            }

            // 3. AUDITORÍA DE REINCIDENCIA CON REDENCIÓN
            // Se recorre cronológicamente el historial de la sesión.
            // Al alcanzar 3 capturas buenas consecutivas (Captura Medida), el contador se reinicia
            // por completo: si el operador vuelve a fallar, comienza desde Sospechosa otra vez.
            $user = Auth::user();
            if (!$user) return response()->json(['success' => false, 'message' => 'Sesión expirada']);

            $lastResetLog = SystemLog::query()->where('user_matricula', $user->matricula)
                ->whereIn('action', ['Inicio de Reporte', 'Inicio de Sesión'], 'and', false)
                ->orderBy('created_at', 'desc')
                ->first();

            $allCaptures = SystemLog::query()
                ->where('user_matricula', $user->matricula)
                ->whereIn('action', ['Captura Medida', 'Captura Sospechosa', 'Captura Crítica'], 'and', false)
                ->when($lastResetLog, fn($q) => $q->where('created_at', '>', $lastResetLog->created_at))
                ->when(!$lastResetLog, fn($q) => $q->whereDate('created_at', now()->toDateString()))
                ->orderBy('created_at', 'asc')
                ->get(['action', 'created_at']);

            // Recorrer historial y calcular el estado efectivo actual (consecutivos)
            $consecutiveBadCount = 0;
            $hasCriticalInStreak  = false;

            foreach ($allCaptures as $cap) {
                if (in_array($cap->action, ['Captura Sospechosa', 'Captura Crítica'])) {
                    $consecutiveBadCount++;
                    if ($cap->action === 'Captura Crítica') $hasCriticalInStreak = true;
                } else {
                    // Una sola pieza buena (Captura Medida) reinicia el contador totalmente
                    $consecutiveBadCount = 0;
                    $hasCriticalInStreak = false;
                }
            }

            // Detección temporal: ¿la pieza actual fue completada en tiempo sospechosamente corto?
            $lastLog = SystemLog::query()->where('user_matricula', Auth::user()->matricula)
                ->whereIn('action', ['Captura Medida', 'Captura Sospechosa', 'Captura Crítica'], 'and', false)
                ->orderBy('created_at', 'desc')
                ->first();

            $isTimeSuspicious = false;
            $diffMins = 0;
            if ($lastLog) {
                $diffSecs = (int) abs($now->diffInSeconds($lastLog->created_at));
                $diffMins = floor($diffSecs / 60);
                $isTimeSuspicious = ($diffSecs < 300);
            }

            // LÓGICA DE ASIGNACIÓN DE ACCIÓN
            if ($isTimeSuspicious) {
                // Si falla, evaluamos reincidencia consecutiva
                if ($consecutiveBadCount >= 2 || $hasCriticalInStreak) {
                    $action = 'Captura Crítica';
                    $alertMsg = "\nALERTA CRÍTICA: Problema recurrente de llenado. Reincidencia detectada. ({$diffMins} min)";
                } else {
                    $action = 'Captura Sospechosa';
                    $alertMsg = "\nALERTA: Tiempo insuficiente entre juegos diferentes ({$diffMins} min)";
                }
                
                // Evitar duplicar el mensaje si ya viene del frontend
                if (!str_contains($details, trim($alertMsg))) {
                    $details .= $alertMsg;
                }
            } else {
                // Si el tiempo es bueno, siempre es Captura Medida
                $action = 'Captura Medida';
            }
        }

        $pointEvents = ['Inicio de Sesión', 'Cierre de Sesión', 'Inicio de Reporte', 'Terminar Reporte', 'Nueva Meta Creada', 'Autorización de Edición'];
        if (in_array($action, $pointEvents)) {
            $h_inicio = $h_termino;
        }

        // ESCRITURA BLINDADA: DB::transaction + try-catch + Log::error fallback
        try {
            DB::transaction(function () use (
                $action, $details, $otString, $idOtSanitized, $claseString, $idClaseSanitized,
                $maquina, $h_inicio, $h_termino, $request, $n_pieza
            ) {
                SystemLog::create([
                    'user_matricula' => Auth::check() ? Auth::user()->matricula : null,
                    'action' => $action,
                    'details' => $details,
                    'ot' => $otString,
                    'id_ot' => $idOtSanitized,
                    'clase' => $claseString,
                    'id_clase' => $idClaseSanitized,
                    'proceso' => $request->proceso,
                    'maquina' => $maquina,
                    'n_pieza' => $n_pieza ?? $request->n_pieza,
                    'h_inicio' => $h_inicio,
                    'h_termino' => $h_termino,
                ]);
            });
        } catch (\Throwable $e) {
            // Nunca fallamos en silencio: registrar en el log del servidor
            Log::error('[SystemLog] Fallo al insertar registro de auditoría.', [
                'action'        => $action,
                'user'          => Auth::check() ? Auth::user()->matricula : 'guest',
                'error'         => $e->getMessage(),
                'trace'         => $e->getTraceAsString(),
            ]);
            // No lanzamos el error al usuario para no romper el flujo de producción
        }

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

        /**
     * @param Request $request
     */
    public function index(Request $request)
    {
        // 1. Obtener valores ÚNICOS para los filtros usando DB::table() en lugar de Eloquent.
        // DB::table() devuelve stdClass simples — NUNCA instancia modelos Eloquent ni su
        // GuardsAttributes, evitando el agotamiento de memoria con tablas grandes.
        $filtrosDisponibles = [
            'ot' => DB::table('system_logs')
                ->select(['system_logs.id_ot', 'system_logs.ot', 'molduras.nombre as moldura_nombre'])
                ->leftJoin('orden_trabajo', 'system_logs.id_ot', '=', 'orden_trabajo.id')
                ->leftJoin('molduras', 'orden_trabajo.id_moldura', '=', 'molduras.id')
                ->whereNotNull('system_logs.ot')
                ->distinct()
                ->get()
                ->map(function($val) {
                    $idBase = preg_match('/^(\d+)/', $val->ot, $m) ? $m[1] : $val->ot;
                    if ($val->moldura_nombre) {
                        return "{$idBase} - {$val->moldura_nombre}";
                    }
                    return $val->ot;
                })
                ->filter()
                ->unique()
                ->sort()
                ->values(),

            'clase' => DB::table('system_logs')
                ->select(['system_logs.clase', 'clases.nombre as clase_nombre'])
                ->leftJoin('clases', 'system_logs.id_clase', '=', 'clases.id')
                ->whereNotNull('system_logs.clase')
                ->distinct()
                ->get()
                ->map(function($val) {
                    if ($val->clase_nombre) return $val->clase_nombre;
                    return preg_match('/^\d+$/', $val->clase) ? null : $val->clase;
                })
                ->filter()
                ->unique()
                ->sort()
                ->values(),

            'proceso' => DB::table('system_logs')
                ->distinct()
                ->whereNotNull('proceso')
                ->orderBy('proceso')
                ->pluck('proceso'),

            'maquina' => DB::table('system_logs')
                ->distinct()
                ->whereNotNull('maquina')
                ->orderBy('maquina')
                ->pluck('maquina'),

            'action' => $request->filled('admin_only') && $request->admin_only == 1
                ? collect([
                    'Inicio de Sesión',
                    'Cierre de Sesión',
                    'Cargo de OT',
                    'Cargo de Clase de OT',
                    'Modificación de OT',
                    'Cargo/Modificación Cotas Nominales',
                    'Desocupación de Máquina',
                    'Subida de Dibujo',
                    'Eliminación de Dibujo',
                    'Reemplazo de Dibujo',
                    'Creación de Carpeta',
                    'Subida de Manual',
                    'Eliminación de Manual',
                    'Reemplazo de Manual',
                    'Subida de Ayuda Visual',
                    'Eliminación de Ayuda Visual',
                    'Reemplazo de Ayuda Visual',
                    'Subida de Dibujo Fundición',
                    'Eliminación de Dibujo Fundición',
                    'Reemplazo de Dibujo Fundición',
                    'Visualización de Dibujo',
                    'Autorización de Edición',
                ])->sort()->values()
                : DB::table('system_logs')
                    ->distinct()
                    ->whereNotNull('action')
                    ->orderBy('action')
                    ->pluck('action'),
        ];

        // Obtener operadores únicos de forma eficiente
        // En modo admin_only, solo mostrar administradores (perfil == 1)
        $filtrosDisponibles['operador'] = DB::table('system_logs')
            ->select(['system_logs.user_matricula', 'users.nombre', 'users.a_paterno', 'users.perfil'])
            ->leftJoin('users', 'system_logs.user_matricula', '=', 'users.matricula')
            ->whereNotNull('system_logs.user_matricula')
            ->when($request->filled('admin_only') && $request->admin_only == 1, fn($q) => $q->whereIn('users.perfil', [1, 3]))
            ->distinct()
            ->get()
            ->map(fn($o) => (object)[
                'matricula' => $o->user_matricula,
                'nombre' => $o->nombre,
                'a_paterno' => $o->a_paterno
            ]);

        // Obtener N# Pieza (Juegos) simplificado — sin Eloquent para evitar hidratación de modelos
        $filtrosDisponibles['n_pieza'] = DB::table('system_logs')
            ->distinct()
            ->whereNotNull('n_pieza')
            ->where('n_pieza', 'NOT LIKE', '%/%')
            ->orderBy('n_pieza')
            ->pluck('n_pieza')
            ->map(function($p) {
                $num = preg_replace('/[a-zA-Z]/', '', (string)$p);
                return $num ? $num . "J" : null;
            })
            ->filter()->unique()->sort()->values();

        // 2. Preparar la consulta principal con paginación
        $query = SystemLog::query()->select([
                'system_logs.*', 
                'users.nombre as user_nombre', 'users.a_paterno', 'users.a_materno',
                'molduras.nombre as moldura_nombre',
                'clases.nombre as clase_real_nombre'
            ])
            ->leftJoin('users', 'system_logs.user_matricula', '=', 'users.matricula')
            ->leftJoin('orden_trabajo', 'system_logs.id_ot', '=', 'orden_trabajo.id')
            ->leftJoin('molduras', 'orden_trabajo.id_moldura', '=', 'molduras.id')
            ->leftJoin('clases', 'system_logs.id_clase', '=', 'clases.id');

        // --- APLICAR FILTROS ---
        if ($request->filled('ot') && $request->ot !== 'Todos') {
            $baseOt = preg_match('/^(\d+)/', $request->ot, $matches) ? $matches[1] : $request->ot;
            $query->where('system_logs.ot', 'LIKE', $baseOt . '%');
        }
        if ($request->filled('clase') && $request->clase !== 'Todos') {
            $query->where(function($q) use ($request) {
                $q->where('system_logs.clase', 'LIKE', $request->clase . '%')
                  ->orWhere('clases.nombre', 'LIKE', $request->clase . '%');
            });
        }
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

        // --- FILTRO LOGS DE ADMINISTRADORES ---
        if ($request->filled('admin_only') && $request->admin_only == 1) {
            // Filtrar SOLO usuarios con perfil de administrador (perfil == 1)
            $query->whereExists(function($subQ) {
                $subQ->select(DB::raw(1))
                    ->from('users')
                    ->whereColumn('users.matricula', 'system_logs.user_matricula')
                    ->whereIn('users.perfil', [1, 3]);
            });

            // Filtrar solo acciones propias de administradores
            $query->whereIn('action', [
                'Inicio de Sesión',
                'Cierre de Sesión',
                'Cargo de OT',
                'Cargo de Clase de OT',
                'Modificación de OT',
                'Cargo/Modificación Cotas Nominales',
                'Desocupación de Máquina',
                'Subida de Dibujo',
                'Eliminación de Dibujo',
                'Reemplazo de Dibujo',
                'Creación de Carpeta',
                'Subida de Manual',
                'Eliminación de Manual',
                'Reemplazo de Manual',
                'Subida de Ayuda Visual',
                'Eliminación de Ayuda Visual',
                'Reemplazo de Ayuda Visual',
                'Subida de Dibujo Fundición',
                'Eliminación de Dibujo Fundición',
                'Reemplazo de Dibujo Fundición',
                'Visualización de Dibujo',
                'Autorización de Edición',
            ]);
        } else {
            // Modo normal: excluir acciones exclusivas de gestión documental de administradores
            // para que el log de producción se mantenga limpio (solo operadores/producción)
            $query->whereNotIn('action', [
                'Subida de Dibujo',
                'Eliminación de Dibujo',
                'Reemplazo de Dibujo',
                'Subida de Dibujo Fundición',
                'Eliminación de Dibujo Fundición',
                'Reemplazo de Dibujo Fundición',
                'Subida de Manual',
                'Eliminación de Manual',
                'Reemplazo de Manual',
                'Subida de Ayuda Visual',
                'Eliminación de Ayuda Visual',
                'Reemplazo de Ayuda Visual',
                'Creación de Carpeta',
            ]);
        }

        // --- FILTRO DE AUDITORÍA (NIVELES DE SOSPECHA) ---
        if ($request->filled('audit_status') && $request->audit_status !== 'Todos') {
            if ($request->audit_status === 'Críticos') {
                $query->where('action', 'Captura Crítica');
            } elseif ($request->audit_status === 'Sospechosos') {
                $query->where('action', 'Captura Sospechosa');
            } elseif ($request->audit_status === 'Válidos') {
                $query->whereNotIn('action', ['Captura Sospechosa', 'Captura Crítica'], 'and');
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
                'Abandono de Liberación', 'Exceso de Tiempo de Maquinado', 'Inicio de Reporte Pendiente', 'Inactividad en Bienvenida',
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

            // Extraer diff_mins de los detalles si existen para las alertas
            $diffMins = 0;
            if ($isSuspicious && preg_match('/\((\d+)\s*min\)/i', $log->details, $m)) {
                $diffMins = $m[1];
            }

            $logsRender[] = [
                'id' => $log->id,
                'date' => $log->created_at->format('Y-m-d'),
                'time' => $log->created_at->format('H:i:s'),
                'hora_inicio' => $log->h_inicio ?? 'N/A',
                'hora_termino' => $log->h_termino ?? ($showTimes ? $log->created_at->format('H:i:s') : 'N/A'),
                'tiempo_total' => $tiempoTotal,
                'operador' => $log->user_matricula,
                'operador_nombre' => ($log->user_nombre . ' ' . $log->a_paterno) ?: 'Sistema',
                'action' => $log->action,
                'details' => $log->details,
                'ot' => $log->moldura_nombre ? (preg_match('/^(\d+)/', $log->ot, $m) ? "{$m[1]} - {$log->moldura_nombre}" : "{$log->ot} - {$log->moldura_nombre}") : ($log->ot ?? 'N/A'),
                'clase' => $log->clase_real_nombre ?: (preg_match('/^\d+$/', $log->clase) ? 'N/A' : ($log->clase ?? 'N/A')),
                'proceso' => $log->proceso ?? 'N/A',
                'maquina' => $log->maquina ?? 'N/A',
                'n_juego' => ($log->n_pieza && preg_match('/[HM]$/i', $log->n_pieza)) ? preg_replace('/[HM]$/i', 'J', $log->n_pieza) : ($log->n_pieza ?: 'N/A'),
                'is_suspicious' => $isSuspicious,
                'is_critical' => ($log->action === 'Captura Crítica'),
                'diff_mins' => $diffMins,
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
                    'tiempo_total' => 'N/A',
                    'is_suspicious' => false,
                ];
            });

            $isAdminOnly = $request->filled('admin_only') && $request->admin_only == 1;
            $reportType  = $isAdminOnly ? 'Logs de Administradores' : 'Logs de Sistema';

            $pdf = Pdf::loadView('reports.systemLogsPdf', [
                'logsRender'   => $pdfLogs,
                'selectedItems' => $selectedItems,
                'isAdminOnly'  => $isAdminOnly,
            ]);
            return $pdf->download($this->generatePdfFilename($selectedItems, $reportType));
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


        /**
     * @param array $selectedItems
     * @param string $reportType
     */
    public function generatePdfFilename(array $selectedItems, string $reportType): string
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
        set_time_limit(0);
        try {
            // Solo administradores (perfil 1) o Master (perfil 3)
            if (!in_array(Auth::user()->perfil, [1, 3])) {
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

    /**
     * Obtiene el nombre del modelo de piezas para un proceso dado.
     * 
     * @param string|null $process
     * @param int|null $id_clase
     * @return string|null
     */
    private function getModelForProcess($process, $id_clase)
    {
        if (!$process || !$id_clase) return null;
        $clase = Clase::query()->find($id_clase);
        
        try {
            $modelName = match ($process) {
                'Cepillado' => "Pza_cepillado",
                'Desbaste Exterior' => "Desbaste_pza",
                'Revision Laterales' => "RevLaterales_pza",
                'Primera Operacion' => "PrimeraOpeSoldadura_pza",
                'Barreno Maniobra' => "BarrenoManiobra_pza",
                'Segunda Operacion' => "SegundaOpeSoldadura_pza",
                'Rectificado' => "Rectificado_pza",
                'Asentado' => "Asentado_pza",
                'Calificado' => "revCalificado_pza",
                'Acabado Bombillo' => "AcabadoBombilo_pza",
                'Acabado Molde' => "AcabadoMolde_pza",
                'Barreno Profundidad' => "BarrenoProfundidad_pza",
                'Cavidades' => "Cavidades_pza",
                'Copiado' => "Copiado_pza",
                'Off Set' => "OffSet_pza",
                'Palomas' => "Palomas_pza",
                'Rebajes' => "Rebajes_pza",
                'Operacion Equipo' => ($clase && $clase->nombre == 'Candado Obturador') ? "CandadoObturador_pza" : "PySOpeSoldadura_pza",
                'Embudo CM' => "EmbudoCM_pza",
                'Soldadura' => "Soldadura_pza",
                'Soldadura PTA' => "SoldaduraPTA_pza",
                'Primera Operacion Cabeza Soplo' => "PrimeraOperacionCabezaSoplo_pza",
                'Segunda Operacion Cabeza Soplo' => "SegundaOperacionCabezaSoplo_pza",
                'Candado Obturador' => "CandadoObturador_pza",
                default => null,
            };

            if ($modelName) {
                return "App\Models\\" . $modelName;
            }
        } catch (\Exception $e) {
            return null;
        }

        return null;
    }
}