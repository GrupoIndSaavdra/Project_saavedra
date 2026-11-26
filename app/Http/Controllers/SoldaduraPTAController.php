<?php

namespace App\Http\Controllers;

use App\Models\Clase;
use App\Models\Metas;
use App\Models\Orden_trabajo;
use App\Models\SoldaduraPTA;
use App\Models\SoldaduraPTA_pza;
use App\Models\SystemLog;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class SoldaduraPTAController extends Controller
{
    public const PROGRAMAS_PTA_DIR = 'DOCUMENTACION_GIS/PROGRAMAS_SOLDADURA_PTA';

    private const BLOCKED_EXTENSIONS = ['php', 'phtml', 'phar'];

    /**
     * Tipos de sub-fila legacy (en orden de aparición en registros históricos).
     * Las capturas nuevas simplificadas usan un único registro por pieza ('D_Conexion_pico').
     */
    const TIPOS_MEDIDA = ['D_Conexion_pico', 'D_Conexion_obt', 'Perfilado'];

    /**
     * Mapa tipo_medida → campo de valor principal (la columna "Valor" de la tabla)
     */
    const CAMPO_VALOR = [
        'D_Conexion_pico' => 'd_conexion_pico',
        'D_Conexion_obt' => 'd_conexion_obt',
        'Perfilado' => 'perfilado',
    ];

    /**
     * Mapa tipo_medida → campo de valor principal de 2da pasada
     */
    const CAMPO_VALOR_P2 = [
        'D_Conexion_pico' => 'p2_d_conexion_pico',
        'D_Conexion_obt' => 'p2_d_conexion_obt',
        'Perfilado' => 'p2_perfilado',
    ];

    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Guarda / actualiza las 3 sub-filas de una pieza individual (M o H).
     *
     * El $request lleva arrays con clave = ID del registro SoldaduraPTA_pza:
     *   Primera pasada:
     *   - tipo_medida[id], d_conexion_pico[id], d_conexion_obt[id], perfilado[id]
     *   - vl[id], tipo_preparacion[id], precalentamiento[idPrecal], observaciones[idObs]
     *   - sold_inicial[id], sold_aplicada[id], sold_final[id]
     *   - corr_inicial[id], corr_aplicada[id], corr_final[id]
     *   - gas_argon[id], velocidad_calculada[id], resultado[id], defecto_pta[id]
     *
     *   Segunda pasada (opcionales, solo si p2_activa.[nPieza] == '1'):
     *   - p2_activa[nPieza]    — '1' indica que se activa la 2da pasada para ese grupo
     *   - p2_d_conexion_pico[id], p2_d_conexion_obt[id], p2_perfilado[id]
     *   - p2_vl[id], p2_tipo_preparacion[id]
     *   - p2_precalentamiento[id], p2_observaciones[id]
     *   - p2_sold_inicial[id] ... p2_corr_final[id]
     *   - p2_gas_argon[id], p2_velocidad_calculada[id]
     *   - p2_resultado[id], p2_defecto_pta[id]
     *
     * @param  Request  $request
     * @param  int|null  $index  — no se usa en PTA (legacy param)
     */
    public function storePiece($request, $index = null)
    {
        $pieceIds = $request->input('piece_id', []);
        if (empty($pieceIds)) {
            return;
        }

        // ── Ordenar pieceIds para procesar Hembra (H) antes que Macho (M) ──
        // Esto asegura que los logs se guarden en el orden lógico H -> M
        $sortedIds = [];
        foreach ($pieceIds as $key => $pid) {
            if (! $pid) {
                continue;
            }
            $row = SoldaduraPTA_pza::query()->find($pid, ['n_pieza']);
            if ($row) {
                $sortedIds[] = [
                    'key' => $key,
                    'pid' => $pid,
                    'n_pieza' => $row->n_pieza,
                ];
            }
        }

        usort($sortedIds, function ($a, $b) {
            return strcmp($a['n_pieza'], $b['n_pieza']); // H < M alfabéticamente si el número es igual
        });

        foreach ($sortedIds as $item) {
            $key = $item['key'];
            $pieceId = $item['pid'];

            $piece = SoldaduraPTA_pza::query()->find($pieceId, ['*']);
            if (! $piece) {
                continue;
            }

            $tipo = $request->tipo_medida[$key] ?? $piece->tipo_medida ?? 'D_Conexion_pico';
            $resultado = $request->resultado[$key] ?? null;
            $observaciones = $request->observaciones[$key] ?? null;
            $rawPrograma = $request->programa_pta[$key] ?? null;
            $programaPta = ($rawPrograma && $this->isSafeProgramPath($rawPrograma)) ? $rawPrograma : null;

            $ptaParent = SoldaduraPTA::query()->find($piece->id_proceso);
            if (! $programaPta && $ptaParent && $ptaParent->programa_pta && $this->isSafeProgramPath($ptaParent->programa_pta)) {
                $programaPta = $ptaParent->programa_pta;
            }

            $isBad = in_array($resultado, ['En mal estado', 'Mal'], true);

            $piece->tipo_medida = $tipo;
            $piece->resultado = $resultado;
            $piece->defecto_pta = $request->defecto_pta[$key] ?? 'Ninguno';
            $piece->error = $isBad ? 'Maquinado' : 'Ninguno';

            if ($tipo === 'D_Conexion_pico' || count($sortedIds) === 1) {
                $piece->observaciones = $observaciones;
                $piece->programa_pta = $programaPta;
            }

            $piece->estado = 2;
            $piece->save();

            // ── Persistencia sticky del programa utilizado para todas las piezas PTA pendientes ──
            if ($programaPta) {
                if ($ptaParent && $ptaParent->programa_pta !== $programaPta) {
                    $ptaParent->programa_pta = $programaPta;
                    $ptaParent->save();
                }
                SoldaduraPTA_pza::query()
                    ->where('id_proceso', '=', $piece->id_proceso, 'and')
                    ->where('estado', '=', 1, 'and')
                    ->update(['programa_pta' => $programaPta]);
            }

            // ── REGISTRO DE LOG OFICIAL (Consolidado por Juego: Solo en Hembra o Juego completo) ──
            $nPiezaRef = $request->n_pieza_ref[$key] ?? $piece->n_pieza;
            $meta = Metas::query()->where('id', '=', $piece->id_meta, 'and')->first();
            $clase = $meta ? Clase::query()->where('id', '=', $meta->id_clase, 'and')->first() : null;
            $otFull = $clase ? ($clase->id_ot.' - '.$clase->tamanio) : ($meta->id_ot ?? 'N/A');

            if ($tipo === 'D_Conexion_pico' && (str_ends_with(strtoupper($nPiezaRef), 'H') || str_ends_with(strtoupper($nPiezaRef), 'J'))) {
                $baseNum = preg_replace('/[HMJ]$/i', '', $nPiezaRef);
                $h_termino_log = now()->format('H:i:s');

                // ── LÓGICA DE AUDITORÍA (RESTRICTIVA) ──
                // Replicamos la lógica de SystemLogController para detectar tiempos sospechosos
                $lastLog = SystemLog::query()->where('user_matricula', '=', Auth::user()->matricula, 'and')
                    ->whereIn('action', ['Captura Medida', 'Captura Sospechosa', 'Captura Crítica'], 'and', false)
                    ->orderBy('created_at', 'desc')
                    ->first();

                $isTimeSuspicious = false;
                $diffMins = 0;
                if ($lastLog) {
                    $now = now();
                    $diffSecs = (int) abs($now->diffInSeconds($lastLog->created_at));
                    $diffMins = floor($diffSecs / 60);
                    // Menos de 5 minutos es sospechoso (regla estándar del sistema)
                    $isTimeSuspicious = ($diffSecs < 300 && $diffSecs >= 10);
                }

                $action = 'Captura Medida';
                $details = "El operador completó el maquinado del juego {$baseNum} a las ".substr($h_termino_log, 0, 5);

                if ($isTimeSuspicious) {
                    $action = 'Captura Sospechosa';
                    $details .= "\nALERTA: Tiempo insuficiente entre juegos diferentes ({$diffMins} min)";
                }

                SystemLog::create([
                    'user_matricula' => Auth::user()->matricula,
                    'action' => $action,
                    'details' => $details,
                    'ot' => $otFull,
                    'clase' => $clase->nombre ?? 'N/A',
                    'proceso' => 'Soldadura PTA',
                    'maquina' => $meta->maquina ?? 'N/A',
                    'n_pieza' => $baseNum.'J',
                    'h_inicio' => $request->input('h_inicio_solicitud') ?? now()->subMinute()->format('H:i:s'),
                    'h_termino' => $h_termino_log,
                    'id_ot' => $meta->id_ot,
                    'id_clase' => $meta->id_clase,
                ]);
            }

            // ── 2da pasada (Se crea un ROW separado para toda la pieza) ──
            $nPiezaRef = $request->n_pieza_ref[$key] ?? $piece->n_pieza;
            $p2Activa = $nPiezaRef ? ($request->input("p2_activa.{$nPiezaRef}") === '1') : false;

            if ($tipo === 'D_Conexion_pico' && $p2Activa && $nPiezaRef) {
                $p2Row = SoldaduraPTA_pza::query()->where('id_proceso', '=', $piece->id_proceso, 'and')
                    ->where('n_pieza', '=', $nPiezaRef, 'and')
                    ->where('p2_activa', '=', 1, 'and')
                    ->first();

                if (! $p2Row) {
                    $p2Row = new SoldaduraPTA_pza();
                    $p2Row->id_proceso = $piece->id_proceso;
                    $p2Row->id_pza = $piece->id_pza.'_P2';
                    $p2Row->id_meta = $piece->id_meta;
                    $p2Row->n_juego = $piece->n_juego;
                    $p2Row->n_pieza = $nPiezaRef;
                    $p2Row->p2_activa = true;
                    $p2Row->estado = 2;
                }

                $p2Row->tipo_medida = 'Segunda_Pasada';
                $p2Row->d_conexion_pico = null;
                $p2Row->d_conexion_obt = null;
                $p2Row->vl = null;
                $p2Row->tipo_preparacion = null;
                $p2Row->perfilado = null;
                $p2Row->precalentamiento = null;
                $p2Row->sold_inicial = null;
                $p2Row->sold_aplicada = null;
                $p2Row->sold_final = null;
                $p2Row->corr_inicial = null;
                $p2Row->corr_aplicada = null;
                $p2Row->corr_final = null;
                $p2Row->gas_argon = null;
                $p2Row->velocidad_calculada = null;
                $p2Row->resultado = null;
                $p2Row->defecto_pta = null;
                $p2Row->temp_calentado = null;
                $p2Row->temp_dispositivo = null;
                $p2Row->limpieza = null;
                $p2Row->observaciones = null;

                $p2Resultado = $request->input("p2_resultado.{$nPiezaRef}");
                $p2IsBad = in_array($p2Resultado, ['En mal estado', 'Mal'], true);
                $rawP2Prog = $request->input("p2_programa_pta.{$nPiezaRef}");
                $p2Programa = ($rawP2Prog && $this->isSafeProgramPath($rawP2Prog))
                    ? $rawP2Prog
                    : ($piece->programa_pta ?? null);

                $p2Row->p2_resultado = $p2Resultado;
                $p2Row->p2_defecto_pta = 'Ninguno';
                $p2Row->error = $p2IsBad ? 'Maquinado' : 'Ninguno';
                $p2Row->p2_observaciones = $request->input("p2_observaciones.{$nPiezaRef}");
                $p2Row->p2_programa_pta = $p2Programa;

                $p2Row->save();

                // ── LOG DE SEGUNDA PASADA (Audit Trail) ──
                $baseNum = preg_replace('/[HMJ]$/i', '', $nPiezaRef);
                SystemLog::create([
                    'user_matricula' => Auth::user()->matricula,
                    'action' => 'Segunda Pasada PTA',
                    'details' => "El operador registró una SEGUNDA PASADA para el juego {$baseNum}.",
                    'ot' => $otFull,
                    'clase' => $clase->nombre ?? 'N/A',
                    'proceso' => 'Soldadura PTA',
                    'maquina' => $meta->maquina ?? 'N/A',
                    'n_pieza' => $baseNum.'J',
                    'h_inicio' => $request->input('h_inicio_solicitud') ?? now()->subMinute()->format('H:i:s'),
                    'h_termino' => now()->format('H:i:s'),
                    'id_ot' => $meta->id_ot,
                    'id_clase' => $meta->id_clase,
                ]);
            } elseif ($tipo === 'D_Conexion_pico' && ! $p2Activa && $nPiezaRef) {
                SoldaduraPTA_pza::query()->where('id_proceso', '=', $piece->id_proceso, 'and')
                    ->where('n_pieza', '=', $nPiezaRef, 'and')
                    ->where('p2_activa', '=', 1, 'and')
                    ->delete();
            }
        }
    }

    /**
     * comparePieceData — Soldadura PTA no tiene cotas nominales ni tolerancias.
     * Siempre retorna 1 (correcto).
     */
    public function comparePieceData($piece, $cNominal, $tolerance): int
    {
        return 1;
    }

    /**
     * buildPiezasGroup — Construye la colección agrupada para pasar al partial Blade.
     * Solo retorna filas con estado = 2 (completadas).
     *
     * @return \Illuminate\Support\Collection agrupada por n_pieza
     */
    public function buildPiezasGroup(int $idProceso)
    {
        // El orden por defecto en SQL para strings pone '1H' antes que '1M' (H antes que M en alfabeto).
        // Usamos una extracción numérica seguida del sufijo para que ordene 1, 2, 3... y luego M, H.
        // Dado que típicamente es "Número + Letra", ordenamos numéricamente primero, y luego forzamos 'M' antes de 'H' si es necesario.
        return SoldaduraPTA_pza::query()->where('id_proceso', '=', $idProceso, 'and')
            ->where('estado', '=', 2, 'and')
            ->orderByRaw('CAST(n_pieza AS UNSIGNED) ASC') // Ordena por el número: '1M' y '1H' serán ambos 1
            ->orderByRaw('RIGHT(n_pieza, 1) DESC')        // Letra final: 'M' (Macho) antes que 'H' (Hembra) porque M > H lexicográficamente en DESC
            ->orderByRaw("FIELD(tipo_medida, 'D_Conexion_pico', 'D_Conexion_obt', 'Perfilado')")
            ->get()
            ->groupBy('n_pieza');
    }

    /**
     * store2daPasada — Guarda la 2da pasada para los 3 registros de una pieza
     * desde la vista de edición diferida (/admin/pta/segunda-pasada).
     *
     * Recibe del formulario:
     *   id_proceso     — ID del proceso soldaduraPTA
     *   n_pieza        — ej. '1M'
     *   p2_tipo_medida — qué medida lleva el valor principal ('D_Conexion_pico'|'D_Conexion_obt'|'Perfilado')
     *   p2_valor_principal — el valor numérico del campo seleccionado
     *   p2_vl, p2_tipo_preparacion, p2_precalentamiento, p2_observaciones
     *   p2_sold_inicial, p2_sold_aplicada, p2_sold_final
     *   p2_corr_inicial, p2_corr_aplicada, p2_corr_final
     *   p2_gas_argon, p2_velocidad_calculada
     *   p2_resultado, p2_defecto_pta
     */
    public function store2daPasada(Request $request): void
    {
        $idProceso = $request->input('id_proceso');
        $nPieza = $request->input('n_pieza');
        $p2Tipo = $request->input('p2_tipo_medida'); // tipo que lleva el valor principal

        $rows = SoldaduraPTA_pza::query()->where('id_proceso', '=', $idProceso, 'and')
            ->where('n_pieza', '=', $nPieza, 'and')
            ->where('estado', '=', 2, 'and')
            ->get()
            ->keyBy('tipo_medida');

        foreach (self::TIPOS_MEDIDA as $tipo) {
            $piece = $rows[$tipo] ?? null;
            if (! $piece) {
                continue;
            }

            // Campos comunes de 2da pasada en todas las sub-filas
            $piece->p2_activa = true;
            $piece->p2_vl = $request->input('p2_vl');
            $piece->p2_tipo_preparacion = $request->input('p2_tipo_preparacion');
            $piece->p2_sold_inicial = $request->input('p2_sold_inicial');
            $piece->p2_sold_aplicada = $request->input('p2_sold_aplicada');
            $piece->p2_sold_final = $request->input('p2_sold_final');
            $piece->p2_corr_inicial = $request->input('p2_corr_inicial');
            $piece->p2_corr_aplicada = $request->input('p2_corr_aplicada');
            $piece->p2_corr_final = $request->input('p2_corr_final');
            $piece->p2_gas_argon = $request->input('p2_gas_argon');
            $piece->p2_velocidad_calculada = $request->input('p2_velocidad_calculada');
            $piece->p2_resultado = $request->input('p2_resultado');
            $piece->p2_defecto_pta = $request->input('p2_defecto_pta', 'Ninguno');

            // Precalentamiento y observaciones solo van en la fila D_Conexion_pico
            if ($tipo === 'D_Conexion_pico') {
                $piece->p2_precalentamiento = $request->input('p2_precalentamiento');
                $piece->p2_observaciones = $request->input('p2_observaciones');

                // Asignar valor principal al campo correcto según el tipo seleccionado
                $piece->p2_d_conexion_pico = ($p2Tipo === 'D_Conexion_pico')
                    ? $request->input('p2_valor_principal') : null;
                $piece->p2_d_conexion_obt = ($p2Tipo === 'D_Conexion_obt')
                    ? $request->input('p2_valor_principal') : null;
                $piece->p2_perfilado = ($p2Tipo === 'Perfilado')
                    ? $request->input('p2_valor_principal') : null;
            }

            $piece->save();
        }
    }

    /**
     * Lista programas de Soldadura PTA dentro de DOCUMENTACION_GIS/PROGRAMAS_SOLDADURA_PTA,
     * creando automáticamente la carpeta OT / Clase actual si no existe.
     */
    public function listPrograms(Request $request)
    {
        try {
            $otId = (string) $request->query('ot_id', '');
            $claseId = $request->query('clase_id');
            $idProceso = $request->query('id_proceso');

            if ($otId === '') {
                return response()->json(['success' => false, 'message' => 'OT requerida.'], 422);
            }

            $otModel = Orden_trabajo::query()->with('moldura')->find($otId);
            if (! $otModel) {
                return response()->json(['success' => false, 'message' => 'Orden de trabajo no encontrada.'], 404);
            }

            $claseModel = $claseId ? Clase::query()->find($claseId) : null;
            $disk = Storage::disk('local');

            if (! $disk->exists(self::PROGRAMAS_PTA_DIR)) {
                $disk->makeDirectory(self::PROGRAMAS_PTA_DIR);
            }

            $otFolder = $this->resolvePtaOtFolder($otModel);
            $claseFolder = $this->resolvePtaClaseFolder($otFolder, $claseModel?->nombre ?? 'GENERAL');
            $currentDir = self::PROGRAMAS_PTA_DIR.'/'.$otFolder.'/'.$claseFolder;

            if (! $disk->exists($currentDir)) {
                $disk->makeDirectory($currentDir);
            }

            $activeProgram = null;
            if ($idProceso) {
                $activeProgram = SoldaduraPTA::query()->where('id', '=', $idProceso, 'and')->value('programa_pta');
            } elseif ($claseModel) {
                $procStr = 'Soldadura_PTA_'.str_replace(' ', '_', $claseModel->nombre).'_'.$otModel->id;
                $activeProgram = SoldaduraPTA::query()->where('id_proceso', '=', $procStr, 'and')->value('programa_pta');
            }

            $currentFiles = $this->readPtaDirectoryFiles($disk, $currentDir, $otFolder, $claseFolder);

            // Escanear todas las carpetas de OTs dentro de PROGRAMAS_SOLDADURA_PTA (sin salir de esa raíz)
            $allOts = [];
            foreach ($disk->directories(self::PROGRAMAS_PTA_DIR) as $otDir) {
                $otName = basename($otDir);
                $isCurrentOt = strcasecmp($otName, $otFolder) === 0;
                $classesData = [];

                foreach ($disk->directories($otDir) as $clsDir) {
                    $clsName = basename($clsDir);
                    $files = $this->readPtaDirectoryFiles($disk, $clsDir, $otName, $clsName);
                    $classesData[] = [
                        'clase' => $clsName,
                        'is_current' => $isCurrentOt && strcasecmp($clsName, $claseFolder) === 0,
                        'files' => $files,
                    ];
                }

                // Archivos sueltos directamente en la carpeta de OT (si existieran)
                $rootFiles = $this->readPtaDirectoryFiles($disk, $otDir, $otName, 'General');
                if (! empty($rootFiles)) {
                    $classesData[] = [
                        'clase' => 'General',
                        'is_current' => false,
                        'files' => $rootFiles,
                    ];
                }

                usort($classesData, function ($a, $b) {
                    if ($a['is_current'] !== $b['is_current']) {
                        return $a['is_current'] ? -1 : 1;
                    }
                    return strcasecmp($a['clase'], $b['clase']);
                });

                $allOts[] = [
                    'ot' => $otName,
                    'is_current' => $isCurrentOt,
                    'classes' => $classesData,
                ];
            }

            usort($allOts, function ($a, $b) {
                if ($a['is_current'] !== $b['is_current']) {
                    return $a['is_current'] ? -1 : 1;
                }
                return strcasecmp($a['ot'], $b['ot']);
            });

            return response()->json([
                'success' => true,
                'root_label' => 'DOCUMENTACION_GIS / PROGRAMAS_SOLDADURA_PTA',
                'current' => [
                    'ot_folder' => $otFolder,
                    'clase_folder' => $claseFolder,
                    'relative_dir' => $currentDir,
                    'files' => $currentFiles,
                ],
                'all_ots' => $allOts,
                'active_program' => ($activeProgram && $this->isSafeProgramPath($activeProgram)) ? $activeProgram : null,
            ]);
        } catch (\Throwable $e) {
            Log::error('Error al listar programas de Soldadura PTA', [
                'user_id' => auth()->id(),
                'error' => $e->getMessage(),
            ]);

            return response()->json(['success' => false, 'message' => 'No se pudieron consultar los programas de Soldadura PTA.'], 500);
        }
    }

    /**
     * Sube un programa (cualquier extensión no ejecutable) a PROGRAMAS_SOLDADURA_PTA/{OT}/{Clase}.
     */
    public function uploadProgram(Request $request)
    {
        $request->validate([
            'ot_id' => 'required|exists:orden_trabajo,id',
            'clase_id' => 'required|exists:clases,id',
            'programa' => 'required|file|max:20480',
        ]);

        try {
            $file = $request->file('programa');
            $ext = strtolower((string) $file->getClientOriginalExtension());
            if (in_array($ext, self::BLOCKED_EXTENSIONS, true)) {
                return response()->json(['success' => false, 'message' => 'Tipo de archivo no permitido por seguridad.'], 422);
            }

            $otModel = Orden_trabajo::query()->with('moldura')->findOrFail($request->input('ot_id'));
            $claseModel = Clase::query()->findOrFail($request->input('clase_id'));
            $disk = Storage::disk('local');

            $otFolder = $this->resolvePtaOtFolder($otModel);
            $claseFolder = $this->resolvePtaClaseFolder($otFolder, $claseModel->nombre);
            $targetDir = self::PROGRAMAS_PTA_DIR.'/'.$otFolder.'/'.$claseFolder;

            if (! $disk->exists($targetDir)) {
                $disk->makeDirectory($targetDir);
            }

            $originalName = $this->sanitizeFileName($file->getClientOriginalName());
            if ($disk->exists($targetDir.'/'.$originalName)) {
                $base = pathinfo($originalName, PATHINFO_FILENAME);
                $extPart = pathinfo($originalName, PATHINFO_EXTENSION);
                $suffix = now()->format('Ymd_His');
                $originalName = $extPart !== '' ? "{$base}_{$suffix}.{$extPart}" : "{$base}_{$suffix}";
            }

            $storedPath = $file->storeAs($targetDir, $originalName, 'local');

            return response()->json([
                'success' => true,
                'message' => 'Programa guardado en DOCUMENTACION_GIS/PROGRAMAS_SOLDADURA_PTA.',
                'file' => [
                    'name' => $originalName,
                    'path' => $storedPath,
                    'extension' => strtoupper($ext ?: 'ARCHIVO'),
                    'ot' => $otFolder,
                    'clase' => $claseFolder,
                ],
            ]);
        } catch (\Throwable $e) {
            Log::error('Error al subir programa de Soldadura PTA', [
                'user_id' => auth()->id(),
                'error' => $e->getMessage(),
            ]);

            return response()->json(['success' => false, 'message' => 'No se pudo subir el programa.'], 500);
        }
    }

    /**
     * Asigna un programa de Soldadura PTA al proceso padre y a todas las piezas activas pendientes.
     */
    public function assignProgram(Request $request)
    {
        $data = $request->validate([
            'programa_pta' => 'required|string|max:500',
            'id_proceso' => 'nullable|integer',
            'ot_id' => 'nullable|string',
            'clase_id' => 'nullable|integer',
        ]);

        $path = trim(str_replace('\\', '/', $data['programa_pta']));
        if (! $this->isSafeProgramPath($path) || ! Storage::disk('local')->fileExists($path)) {
            return response()->json(['success' => false, 'message' => 'El programa seleccionado no existe en PROGRAMAS_SOLDADURA_PTA.'], 404);
        }

        DB::beginTransaction();
        try {
            $proceso = null;
            if (! empty($data['id_proceso'])) {
                $proceso = SoldaduraPTA::query()->find($data['id_proceso']);
            } elseif (! empty($data['ot_id']) && ! empty($data['clase_id'])) {
                $clase = Clase::query()->find($data['clase_id']);
                if ($clase) {
                    $procStr = 'Soldadura_PTA_'.str_replace(' ', '_', $clase->nombre).'_'.$data['ot_id'];
                    $proceso = SoldaduraPTA::query()->where('id_proceso', '=', $procStr, 'and')->first();
                }
            }

            if ($proceso) {
                $proceso->programa_pta = $path;
                $proceso->save();

                SoldaduraPTA_pza::query()
                    ->where('id_proceso', '=', $proceso->id, 'and')
                    ->where('estado', '=', 1, 'and')
                    ->update(['programa_pta' => $path]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'programa_pta' => $path,
                'file_name' => basename($path),
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Error al asignar programa de Soldadura PTA', [
                'user_id' => auth()->id(),
                'error' => $e->getMessage(),
            ]);

            return response()->json(['success' => false, 'message' => 'Error al asignar el programa.'], 500);
        }
    }

    private function resolvePtaOtFolder(Orden_trabajo $ot): string
    {
        $disk = Storage::disk('local');
        if ($disk->exists(self::PROGRAMAS_PTA_DIR)) {
            foreach ($disk->directories(self::PROGRAMAS_PTA_DIR) as $dir) {
                $base = basename($dir);
                if (preg_match('/^OT[\s_-]*0*'.preg_quote((string) $ot->id, '/').'(?:\s|[_-]|$)/i', $base)) {
                    return $base;
                }
            }
        }

        $moldura = $ot->moldura ? $ot->moldura->nombre : '';
        $raw = $moldura ? "OT {$ot->id} - {$moldura}" : "OT {$ot->id}";

        return $this->sanitizeSegment($raw);
    }

    private function resolvePtaClaseFolder(string $otFolder, string $claseNombre): string
    {
        $disk = Storage::disk('local');
        $otDir = self::PROGRAMAS_PTA_DIR.'/'.$otFolder;
        $sanitized = $this->sanitizeSegment($claseNombre ?: 'GENERAL');

        if ($disk->exists($otDir)) {
            foreach ($disk->directories($otDir) as $dir) {
                $base = basename($dir);
                if (strcasecmp($base, $sanitized) === 0 || strcasecmp($base, trim($claseNombre)) === 0) {
                    return $base;
                }
            }
        }

        return $sanitized;
    }

    private function readPtaDirectoryFiles(Filesystem $disk, string $dir, string $otName, string $clsName): array
    {
        if (! $disk->exists($dir)) {
            return [];
        }

        $files = [];
        foreach ($disk->files($dir) as $path) {
            $name = basename($path);
            if ($name === '' || str_starts_with($name, '.')) {
                continue;
            }
            $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
            if (in_array($ext, self::BLOCKED_EXTENSIONS, true)) {
                continue;
            }
            $files[] = [
                'name' => $name,
                'path' => str_replace('\\', '/', $path),
                'extension' => strtoupper($ext ?: 'ARCHIVO'),
                'ot' => $otName,
                'clase' => $clsName,
            ];
        }

        usort($files, fn ($a, $b) => strcasecmp($a['name'], $b['name']));

        return $files;
    }

    private function sanitizeSegment(string $value): string
    {
        $clean = str_replace(['/', '\\', "\0"], '-', $value);
        $clean = preg_replace('/[:*?"<>|]/', '_', $clean);
        $clean = preg_replace('/\.{2,}/', '.', $clean);
        $clean = trim($clean, " .\t\n\r\0\x0B");

        return $clean !== '' ? $clean : 'GENERAL';
    }

    private function sanitizeFileName(string $name): string
    {
        $base = basename(str_replace('\\', '/', $name));
        $base = preg_replace('/[:*?"<>|\0]/', '_', $base);
        $base = trim($base);

        return ($base !== '' && $base !== '.' && $base !== '..') ? $base : 'programa_pta.txt';
    }

    private function isSafeProgramPath(?string $path): bool
    {
        if (! is_string($path) || $path === '') {
            return false;
        }
        $norm = str_replace('\\', '/', $path);
        if (str_contains($norm, "\0") || preg_match('~(?:^|/)\.\.(?:/|$)~', $norm)) {
            return false;
        }

        return str_starts_with($norm, self::PROGRAMAS_PTA_DIR.'/');
    }
}
