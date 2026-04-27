<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePtaResultadoRequest;
use App\Models\Orden_trabajo;
use App\Models\Pieza;
use App\Models\PtaResultado;
use App\Models\SoldaduraPTA;
use App\Models\SoldaduraPTA_pza;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Auth;

class PtaResultsController extends Controller
{
    public function __construct()
    {
        // auth middleware is now applied selectively in routes/web.php via pta.access
    }

    // ═══════════════════════════════════════════════════════════════════════
    // SESIÓN TEMPORAL
    // ═══════════════════════════════════════════════════════════════════════

    public function verifyTempPassword(Request $request)
    {
        $password = $request->input('password');

        // Contraseña predefinida
        if ($password === 'PTA2026') {
            session([
                'pta_temp_auth' => true,
                'pta_temp_ot_id' => $request->input('ot_id'),
                'pta_return_url' => url()->previous()
            ]);

            return response()->json([
                'success' => true,
                'redirect_url' => route('pta.results', ['ot_id' => $request->input('ot_id')])
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Contraseña incorrecta'
        ], 403);
    }

    public function closeTempSession()
    {
        $returnUrl = session('pta_return_url', route('home')); // Fallback

        session()->forget(['pta_temp_auth', 'pta_temp_ot_id', 'pta_return_url']);

        return redirect($returnUrl);
    }

    // ═══════════════════════════════════════════════════════════════════════
    // HELPERS PRIVADOS
    // ═══════════════════════════════════════════════════════════════════════

    /**
     * Verifica si una OT pasó por el proceso de Soldadura PTA.
     */
    private function pasoPorPTA(string $otId, int $claseId): bool
    {
        return Pieza::where('id_ot', $otId)
            ->where('id_clase', $claseId)
            ->where('proceso', 'Soldadura PTA')
            ->exists();
    }

    /**
     * Obtiene las piezas de una OT que pasaron por Soldadura PTA
     */
    private function getPiezasPTA(string $otId, int $claseId, bool $soloBuenas = true, bool $incluirRechazados = false)
    {
        $query = Pieza::where('id_ot', $otId)
            ->where('id_clase', $claseId)
            ->where('proceso', 'Soldadura PTA');

        if ($soloBuenas) {
            $query->where('error', 'Ninguno');
        }

        if (!$incluirRechazados) {
            $query->where('liberacion', '!=', 2);
        }

        return $query->orderByRaw('CAST(n_pieza AS UNSIGNED) ASC')
            ->orderByRaw("RIGHT(n_pieza, 1) DESC")
            ->get();
    }

    /**
     * Sube una imagen al disco public, la redimensiona usando GD nativo y devuelve la ruta.
     * Convierte imágenes a formato JPEG (1000px max, 80% compresión) para optimización extrema.
     */
    private function subirImagen($file, ?string $rutaAnterior, string $prefijo, string $otId, string $claseNombre, string $nPieza): string
    {
        $claseLimpia = preg_replace('/[^A-Za-z0-9_\-]/', '_', $claseNombre);
        $nPiezaLimpia = preg_replace('/[^A-Za-z0-9_\-]/', '_', $nPieza);

        $relativePath = 'images/resultados_PTA/OT_' . $otId . '/' . $claseLimpia;
        $directorioDestino = public_path($relativePath);

        if (!file_exists($directorioDestino)) {
            mkdir($directorioDestino, 0755, true);
        }

        if ($rutaAnterior && file_exists(public_path($rutaAnterior))) {
            @unlink(public_path($rutaAnterior));
        }

        $fechaHora = date('Y-m-d_H-i-s');
        $rutaTemporal = $file->getRealPath();
        
        $infoImagen = @getimagesize($rutaTemporal);
        $tieneGD = extension_loaded('gd');

        if ($tieneGD && $infoImagen !== false && in_array($infoImagen[2], [IMAGETYPE_JPEG, IMAGETYPE_PNG, IMAGETYPE_WEBP, IMAGETYPE_GIF])) {
            $nombre = $otId . '_' . $claseLimpia . '_' . $nPiezaLimpia . '_' . $prefijo . '_' . $fechaHora . '.jpg';
            $destinoFinal = $directorioDestino . DIRECTORY_SEPARATOR . $nombre;

            $tipoImagen = $infoImagen[2];
            $anchoOrig = $infoImagen[0];
            $altoOrig = $infoImagen[1];

            // Crear el recurso base
            if ($tipoImagen === IMAGETYPE_JPEG) $origen = @imagecreatefromjpeg($rutaTemporal);
            elseif ($tipoImagen === IMAGETYPE_PNG) $origen = @imagecreatefrompng($rutaTemporal);
            elseif ($tipoImagen === IMAGETYPE_WEBP) $origen = @imagecreatefromwebp($rutaTemporal);
            elseif ($tipoImagen === IMAGETYPE_GIF) $origen = @imagecreatefromgif($rutaTemporal);
            else $origen = false;

            if ($origen !== false) {
                // Corregir orientación EXIF
                if ($tipoImagen === IMAGETYPE_JPEG && function_exists('exif_read_data')) {
                    $exif = @exif_read_data($rutaTemporal);
                    if (!empty($exif['Orientation'])) {
                        switch ($exif['Orientation']) {
                            case 3: $origen = imagerotate($origen, 180, 0); break;
                            case 6: $origen = imagerotate($origen, -90, 0); $tmp = $anchoOrig; $anchoOrig = $altoOrig; $altoOrig = $tmp; break;
                            case 8: $origen = imagerotate($origen, 90, 0);  $tmp = $anchoOrig; $anchoOrig = $altoOrig; $altoOrig = $tmp; break;
                        }
                    }
                }

                // Cálculo para escala (1000px ancho max)
                $maxAncho = 1000;
                if ($anchoOrig > $maxAncho) {
                    $nuevoAncho = $maxAncho;
                    $nuevoAlto = intval(($maxAncho / $anchoOrig) * $altoOrig);
                } else {
                    $nuevoAncho = $anchoOrig;
                    $nuevoAlto = $altoOrig;
                }

                $lienzo = imagecreatetruecolor($nuevoAncho, $nuevoAlto);

                if (in_array($tipoImagen, [IMAGETYPE_PNG, IMAGETYPE_WEBP, IMAGETYPE_GIF])) {
                    $fondoBlanco = imagecolorallocate($lienzo, 255, 255, 255);
                    imagefill($lienzo, 0, 0, $fondoBlanco);
                }

                imagecopyresampled($lienzo, $origen, 0, 0, 0, 0, $nuevoAncho, $nuevoAlto, $anchoOrig, $altoOrig);

                imagejpeg($lienzo, $destinoFinal, 80);
                
                return $relativePath . '/' . $nombre;
            }
        }
        
        $nombreFallback = $otId . '_' . $claseLimpia . '_' . $nPiezaLimpia . '_' . $prefijo . '_' . $fechaHora . '.' . $file->getClientOriginalExtension();
        $file->move($directorioDestino, $nombreFallback);
        
        return $relativePath . '/' . $nombreFallback;
    }

    // ═══════════════════════════════════════════════════════════════════════
    // PARTE 2: VISTA DE RESULTADOS — OPERADOR
    // ═══════════════════════════════════════════════════════════════════════

    /**
     * GET admin/pta/results/{ot_id}
     * Muestra el formulario de resultados de Soldadura PTA para una OT.
     */
    public function index(string $ot_id, Request $request)
    {
        $ot = Orden_trabajo::findOrFail($ot_id);
        $clase_id = $request->get('clase_id');

        $clasesConPTA = \App\Models\Clase::where('id_ot', $ot_id)
            ->whereHas('piezas', function ($q) {
                $q->where('proceso', 'Soldadura PTA');
            })->get();

        if ($clasesConPTA->isEmpty()) {
            return redirect()->back()->with('error', 'Esta OT no pasó por el proceso de Soldadura PTA.');
        }

        if (!$clase_id) {
            $clase_id = $clasesConPTA->first()->id;
        }

        $claseSeleccionada = $clasesConPTA->firstWhere('id', $clase_id);

        if (!$claseSeleccionada) {
            return redirect()->back()->with('error', 'La clase seleccionada no tiene Soldadura PTA.');
        }

        // Permitimos ver piezas con errores para que se puedan editar, pero NO las rechazadas
        $piezas = $this->getPiezasPTA($ot_id, $clase_id, false, false);

        if ($piezas->isEmpty()) {
            return redirect()->back()->with('error', 'No hay piezas terminadas en Soldadura PTA para esta OT.');
        }

        // IDs de piezas que ya tienen resultado guardado (o liberado)
        $resultadosGuardados = PtaResultado::where('ot_id', $ot_id)
            ->whereHas('pieza', fn($q) => $q->where('id_clase', $clase_id))
            ->pluck('pieza_id')
            ->flip();  // flip() para buscar en O(1)

        // Piezas que todavía no tienen resultado
        $piezasPendientes = $piezas->filter(fn($p) => !isset($resultadosGuardados[$p->id]));

        $piezaSeleccionadaId = $request->get('pieza_id');
        $piezaSeleccionada = $piezasPendientes->firstWhere('id', $piezaSeleccionadaId)
            ?? $piezas->firstWhere('id', $piezaSeleccionadaId)
            ?? $piezasPendientes->first()
            ?? $piezas->first();

        // Todos los resultados de la OT para la tabla resumen (keyed by pieza_id)
        $todosResultados = PtaResultado::where('ot_id', $ot_id)
            ->whereHas('pieza', fn($q) => $q->where('id_clase', $clase_id))
            ->get()->keyBy('pieza_id');

        // Lista de OTs con PTA para el selector de OT y clase
        $otsConPTA = Orden_trabajo::whereHas('clases', function ($q) {
            $q->whereHas('piezas', function ($q2) {
                $q2->where('proceso', 'Soldadura PTA');
            });
        })->with([
                    'moldura',
                    'clases' => function ($q) {
                        $q->whereHas('piezas', function ($q2) {
                            $q2->where('proceso', 'Soldadura PTA');
                        });
                    }
                ])->orderBy('id', 'desc')->get();

        // Resultado ya guardado para la pieza seleccionada (por si se edita)
        $resultado = PtaResultado::where('ot_id', $ot_id)
            ->where('pieza_id', $piezaSeleccionada->id)
            ->first();

        $esJuegoCompleto = $claseSeleccionada ? in_array(strtoupper($claseSeleccionada->nombre), ['OBTURADOR', 'FONDO']) : false;

        return view('pta_views.results', compact(
            'ot',
            'claseSeleccionada',
            'otsConPTA',
            'piezas',
            'piezasPendientes',
            'piezaSeleccionada',
            'resultado',
            'todosResultados',
            'esJuegoCompleto'
        ));
    }

    public function store(StorePtaResultadoRequest $request, string $ot_id)
    {
        $ot = Orden_trabajo::findOrFail($ot_id);
        $clase_id = $request->get('clase_id');
        $clase = \App\Models\Clase::find($clase_id);
        $clase_nombre = $clase ? $clase->nombre : 'Clase_' . $clase_id;

        $resultado = PtaResultado::firstOrNew([
            'ot_id' => $ot_id,
            'pieza_id' => $request->pieza_id,
        ]);

        $resultado->n_pieza = $request->n_pieza;
        $resultado->resultado_pico_llenado = $request->resultado_pico_llenado;
        $resultado->resultado_pico_soldadura = $request->resultado_pico_soldadura;
        $resultado->resultado_conexion_llenado = $request->resultado_conexion_llenado;
        $resultado->resultado_conexion_soldadura = $request->resultado_conexion_soldadura;
        $resultado->resultado_perfilado_llenado = $request->resultado_perfilado_llenado;
        $resultado->resultado_perfilado_soldadura = $request->resultado_perfilado_soldadura;

        // Subir imágenes si se enviaron
        if ($request->hasFile('imagen_pico_soldadura')) {
            $resultado->imagen_pico_soldadura = $this->subirImagen(
                $request->file('imagen_pico_soldadura'),
                $resultado->imagen_pico_soldadura,
                'pico_sold',
                $ot_id,
                $clase_nombre,
                $request->n_pieza
            );
        }
        if ($request->hasFile('imagen_conexion_soldadura')) {
            $resultado->imagen_conexion_soldadura = $this->subirImagen(
                $request->file('imagen_conexion_soldadura'),
                $resultado->imagen_conexion_soldadura,
                'conexion_sold',
                $ot_id,
                $clase_nombre,
                $request->n_pieza
            );
        }
        if ($request->hasFile('imagen_perfilado_soldadura')) {
            $resultado->imagen_perfilado_soldadura = $this->subirImagen(
                $request->file('imagen_perfilado_soldadura'),
                $resultado->imagen_perfilado_soldadura,
                'perfilado_sold',
                $ot_id,
                $clase_nombre,
                $request->n_pieza
            );
        }

        $resultado->save();

        // Registrar log de auditoría para PTA (Manual)
        $meta = \App\Models\Metas::where('id_ot', $request->id_ot)->where('id_usuario', Auth::user()->matricula)->orderBy('created_at', 'desc')->first();
        $clase = \App\Models\Clase::find($clase_id);
        $otFull = $clase ? ($clase->id_ot . ' - ' . $clase->tamanio) : $ot_id;

        \App\Models\SystemLog::create([
            'user_matricula' => Auth::user()->matricula,
            'action' => 'Captura Medida',
            'details' => "Registro de resultados PTA para pieza {$request->n_pieza} en OT {$ot_id}.",
            'ot' => $otFull,
            'clase' => $clase->nombre ?? 'N/A',
            'proceso' => 'Soldadura PTA',
            'maquina' => $meta->maquina ?? 'N/A',
            'n_pieza' => $request->n_pieza,
            'h_inicio' => $request->input('h_inicio_solicitud') ?? now()->subMinute()->format('H:i:s'),
            'h_termino' => now()->format('H:i:s'),
            'id_ot' => $request->id_ot,
            'id_clase' => $clase_id
        ]);

        // ── Auto-liberar la pieza actual al guardar ───────────────────────────
        $resultado->liberado_por_admin = true;
        $resultado->save();

        // Verificar si la pieza complementaria también está guardada (juego completo)
        $msg = 'Resultados guardados y pieza liberada.';
        preg_match('/^\d+/', $resultado->n_pieza, $m);
        $prefix = $m[0] ?? null;

        $esJuegoCompleto = in_array(strtoupper($clase_nombre), ['OBTURADOR', 'FONDO']);

        if ($esJuegoCompleto && $prefix) {
            $msg = 'Juego ' . $prefix . ' (Juego Completo) guardado y liberado.';
        } elseif ($prefix) {
            $compañera = PtaResultado::where('ot_id', $ot_id)
                ->where('id', '!=', $resultado->id)
                ->whereHas('pieza', fn($q) => $q->where('n_pieza', 'like', $prefix . '%'))
                ->first();

            if ($compañera && $compañera->liberado_por_admin) {
                $msg = 'Juego ' . $prefix . ' completo — ambas piezas liberadas.';
            } elseif ($compañera) {
                $msg = 'Pieza guardada y liberada. Falta completar la pieza complementaria del juego ' . $prefix . '.';
            }
        }

        $clase_id = $request->get('clase_id');

        return redirect()
            ->route('pta.results', ['ot_id' => $ot_id, 'clase_id' => $clase_id, 'pieza_id' => $request->pieza_id])
            ->with('success', $msg);
    }

    // ═══════════════════════════════════════════════════════════════════════
    // LIBERACIÓN POR ADMIN
    // ═══════════════════════════════════════════════════════════════════════

    /**
     * PUT admin/pta/results/{id}/liberar
     * Libera o revoca la liberación de un resultado por parte del administrador.
     */
    public function update(Request $request, int $id)
    {
        $resultado = PtaResultado::findOrFail($id);

        $liberar = $request->boolean('liberar', true);

        $resultado->liberado_por_admin = $liberar;
        $resultado->save();

        $msg = $liberar ? 'Pieza liberada correctamente.' : 'Liberación revocada correctamente.';
        return redirect()->back()->with('success', $msg);
    }

    /**
     * PUT admin/pta/results/{id}/rechazar
     * Rechaza o quita el rechazo de un resultado por parte del administrador.
     */
    public function rechazar(Request $request, int $id)
    {
        $resultado = PtaResultado::findOrFail($id);

        // Sin funcionalidad de rechazo separada en la BD, simplemente revocamos la liberación
        $resultado->liberado_por_admin = false;
        $resultado->save();

        return redirect()->back()->with('success', 'Liberación revocada correctamente.');
    }

    // ═══════════════════════════════════════════════════════════════════════
    // PARTE 4: ANÁLISIS ADMIN
    // ═══════════════════════════════════════════════════════════════════════

    /**
     * GET admin/pta/analysis
     * Vista de análisis para administradores: filtrar por OT y ver todos los resultados.
     */
    public function analysis(Request $request)
    {
        $otsConPTA = Orden_trabajo::whereHas('clases', function ($q) {
            $q->whereHas('piezas', function ($q2) {
                $q2->where('proceso', 'Soldadura PTA');
            });
        })->with([
                    'moldura',
                    'clases' => function ($q) {
                        $q->whereHas('piezas', function ($q2) {
                            $q2->where('proceso', 'Soldadura PTA');
                        });
                    }
                ])->orderBy('id', 'desc')->get();

        $otSeleccionadaId = $request->get('ot_id');
        $claseSeleccionadaId = $request->get('clase_id');
        $resultados = collect();
        $piezasPTA = collect();
        $piezasGroup = collect();
        $ot = null;
        $claseSeleccionada = null;

        if ($otSeleccionadaId && $claseSeleccionadaId) {
            $ot = Orden_trabajo::find($otSeleccionadaId);
            $claseSeleccionada = \App\Models\Clase::find($claseSeleccionadaId);
            // En el análisis queremos VER TODAS, incluso las que tienen errores o están rechazadas
            $piezasPTA = $this->getPiezasPTA($otSeleccionadaId, $claseSeleccionadaId, false, true);

            // Cargar resultados con relaciones para no hacer N+1
            $resultados = PtaResultado::where('ot_id', $otSeleccionadaId)
                ->whereHas('pieza', fn($q) => $q->where('id_clase', $claseSeleccionadaId))
                ->with(['pieza'])
                ->get()
                ->keyBy('pieza_id');

            // ── Datos técnicos de soldadura (soldaduraPTA_pza) ─────────────────
            $nombreClaseLimpio = str_replace(' ', '_', $claseSeleccionada->nombre);
            $procesoStringId = "Soldadura_PTA_{$nombreClaseLimpio}_{$otSeleccionadaId}";

            $procesoPTA = SoldaduraPTA::where('id_ot', $otSeleccionadaId)
                ->where('id_proceso', $procesoStringId)
                ->latest()
                ->first();

            $piezasGroup = $procesoPTA
                ? (new SoldaduraPTAController())->buildPiezasGroup($procesoPTA->id)
                : collect();
        }

        $esJuegoCompleto = $claseSeleccionada ? in_array(strtoupper($claseSeleccionada->nombre), ['OBTURADOR', 'FONDO']) : false;

        return view('pta_views.analysis', compact(
            'otsConPTA',
            'otSeleccionadaId',
            'claseSeleccionadaId',
            'ot',
            'claseSeleccionada',
            'piezasPTA',
            'resultados',
            'piezasGroup',
            'esJuegoCompleto'
        ));
    }

    /**
     * GET admin/pta/analysis/pdf
     * Genera el reporte PDF del análisis actual (filtros OT y Clase).
     */
    public function analysisPDF(Request $request)
    {
        $otId = $request->get('ot_id');
        $claseId = $request->get('clase_id');

        if (!$otId || !$claseId) {
            return redirect()->back()->with('error', 'Debe seleccionar OT y Clase para generar el PDF.');
        }

        $ot = Orden_trabajo::findOrFail($otId);
        $claseSeleccionada = \App\Models\Clase::findOrFail($claseId);

        // 1. Obtener piezas PTA (incluyendo rechazadas para el reporte completo)
        $piezasPTA = $this->getPiezasPTA($otId, $claseId, false, true);

        // 2. Cargar resultados
        $resultados = PtaResultado::where('ot_id', $otId)
            ->whereHas('pieza', fn($q) => $q->where('id_clase', $claseId))
            ->with(['pieza'])
            ->get()
            ->keyBy('pieza_id');

        // 3. Datos técnicos
        $nombreClaseLimpio = str_replace(' ', '_', $claseSeleccionada->nombre);
        $procesoStringId = "Soldadura_PTA_{$nombreClaseLimpio}_{$otId}";

        $procesoPTA = SoldaduraPTA::where('id_ot', $otId)
            ->where('id_proceso', $procesoStringId)
            ->latest()
            ->first();

        $piezasGroup = $procesoPTA
            ? (new SoldaduraPTAController())->buildPiezasGroup($procesoPTA->id)
            : collect();

        // 4. Generar PDF
        $fecha = now()->format('d-m-Y');
        $fechaHora = now()->format('d-m-Y_h-i-s-A');
        $molduraNombre = $ot->moldura ? $ot->moldura->nombre : 'SinMoldura';
        
        $filename = "OT_{$ot->id}_{$molduraNombre}_Clase_{$claseSeleccionada->nombre}_{$fechaHora}.pdf";
        
        // Limpiar nombre de archivo (evitar espacios y caracteres problemáticos)
        $filename = preg_replace('/[^A-Za-z0-9_\-\.]/', '_', $filename);

        $esJuegoCompleto = in_array(strtoupper($claseSeleccionada->nombre), ['OBTURADOR', 'FONDO']);

        $pdf = Pdf::loadView('pta_views.analysis_pdf', compact(
            'ot',
            'claseSeleccionada',
            'piezasPTA',
            'resultados',
            'piezasGroup',
            'fecha',
            'esJuegoCompleto'
        ));

        // Establecer orientación horizontal (landscape)
        $pdf->setPaper('a4', 'landscape');

        return $pdf->download($filename);
    }

    // ═══════════════════════════════════════════════════════════════════════
    // PARTE 5: 2DA PASADA — Vista de edición diferida
    // ═══════════════════════════════════════════════════════════════════════

    /**
     * GET admin/pta/segunda-pasada
     * Vista con filtros en cascada OT → Clase → Pieza (H ó M).
     * Muestra la 1ra pasada en solo-lectura y la 2da pasada editable.
     */
    public function segPasadaIndex(Request $request)
    {
        $otsConPTA = Orden_trabajo::whereHas('clases', function ($q) {
            $q->whereHas('piezas', fn($q2) => $q2->where('proceso', 'Soldadura PTA'));
        })->with([
                    'moldura',
                    'clases' => fn($q) => $q->whereHas('piezas', fn($q2) => $q2->where('proceso', 'Soldadura PTA'))
                ])->orderBy('id', 'desc')->get();

        $otSeleccionadaId = $request->get('ot_id');
        $claseSeleccionadaId = $request->get('clase_id');
        $nPiezaSel = $request->get('n_pieza');

        $ot = null;
        $claseSeleccionada = null;
        $piezasDisponibles = collect();
        $piezasGroup = null;
        $procesoPTA = null;

        if ($otSeleccionadaId && $claseSeleccionadaId) {
            $ot = Orden_trabajo::find($otSeleccionadaId);
            $claseSeleccionada = \App\Models\Clase::find($claseSeleccionadaId);

            $nombreClaseLimpio = str_replace(' ', '_', $claseSeleccionada->nombre ?? '');
            $procesoStringId = "Soldadura_PTA_{$nombreClaseLimpio}_{$otSeleccionadaId}";

            $procesoPTA = SoldaduraPTA::where('id_ot', $otSeleccionadaId)
                ->where('id_proceso', $procesoStringId)
                ->latest()
                ->first();

            if ($procesoPTA) {
                // n_piezas distintos con estado=2 para este proceso
                $piezasDisponibles = SoldaduraPTA_pza::where('id_proceso', $procesoPTA->id)
                    ->where('estado', 2)
                    ->whereNotNull('n_pieza')
                    ->distinct()
                    ->orderByRaw('CAST(n_pieza AS UNSIGNED) ASC')
                    ->orderByRaw("RIGHT(n_pieza, 1) DESC")
                    ->pluck('n_pieza');

                if ($nPiezaSel) {
                    $piezasGroup = SoldaduraPTA_pza::where('id_proceso', $procesoPTA->id)
                        ->where('n_pieza', $nPiezaSel)
                        ->where('estado', 2)
                        ->orderByRaw("FIELD(tipo_medida, 'D_Conexion_pico', 'D_Conexion_obt', 'Perfilado')")
                        ->get()
                        ->keyBy('tipo_medida');
                }
            }
        }

        return view('pta_views.segunda_pasada', compact(
            'otsConPTA',
            'otSeleccionadaId',
            'claseSeleccionadaId',
            'ot',
            'claseSeleccionada',
            'piezasDisponibles',
            'nPiezaSel',
            'piezasGroup',
            'procesoPTA'
        ));
    }

    /**
     * POST admin/pta/segunda-pasada/update
     * Guarda los datos de 2da pasada para la pieza seleccionada.
     * Requiere autenticación temporal (sesión pta_temp_auth).
     */
    public function segPasadaUpdate(Request $request)
    {
        if (!session('pta_temp_auth')) {
            return redirect()->route('pta.segunda_pasada', [
                'ot_id' => $request->input('ot_id'),
                'clase_id' => $request->input('clase_id'),
                'n_pieza' => $request->input('n_pieza'),
            ])->with('error', 'Debes autenticarte con la contraseña PTA2026 antes de guardar.');
        }

        $request->validate([
            'id_proceso' => 'required|integer',
            'n_pieza' => 'required|string',
            'p2_tipo_medida' => 'required|in:D_Conexion_pico,D_Conexion_obt,Perfilado',
            'p2_valor_principal' => 'required|numeric',
            'p2_vl' => 'nullable|numeric',
            'p2_tipo_preparacion' => 'nullable|integer|in:1,2,3',
            'p2_precalentamiento' => 'nullable|numeric',
            'p2_sold_inicial' => 'nullable|numeric',
            'p2_sold_aplicada' => 'nullable|numeric',
            'p2_sold_final' => 'nullable|numeric',
            'p2_corr_inicial' => 'nullable|numeric',
            'p2_corr_aplicada' => 'nullable|numeric',
            'p2_corr_final' => 'nullable|numeric',
            'p2_gas_argon' => 'nullable|numeric',
            'p2_velocidad_calculada' => 'nullable|numeric',
            'p2_resultado' => 'nullable|in:Bien,Mal',
            'p2_defecto_pta' => 'nullable|string',
            'p2_observaciones' => 'nullable|string|max:500',
        ]);

        $idProceso = $request->input('id_proceso');
        $nPieza = $request->input('n_pieza');
        $p2Tipo = $request->input('p2_tipo_medida'); // tipo que lleva el valor principal

        // Buscar el número de juego de cualquier fila de esta pieza para poder
        // asignarlo a la nueva fila si se va a crear.
        $piezaBase = SoldaduraPTA_pza::where('id_proceso', $idProceso)
            ->where('n_pieza', $nPieza)
            ->where('estado', 2)
            ->first();

        if ($piezaBase) {
            // Buscar si ya existe la fila dedicada a 2da pasada
            $p2Row = SoldaduraPTA_pza::where('id_proceso', $idProceso)
                ->where('n_pieza', $nPieza)
                ->where('p2_activa', 1)
                ->first();

            if (!$p2Row) {
                $p2Row = new SoldaduraPTA_pza();
                $p2Row->id_proceso = $idProceso;
                // Sufijo para evitar error UNIQUE CONSTRAINT al crear esta 4ta fila
                $p2Row->id_pza = $piezaBase->id_pza . '_P2';
                $p2Row->id_meta = $piezaBase->id_meta;
                $p2Row->n_juego = $piezaBase->n_juego;
                $p2Row->n_pieza = $nPieza;
                $p2Row->p2_activa = true;
                $p2Row->estado = 2;
            }

            // Nulificar base de 1ra pasada
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
            $p2Row->error = 'Ninguno';
            $p2Row->observaciones = null;

            // Asignar los valores p2_*
            $p2Row->p2_vl = $request->input('p2_vl');
            $p2Row->p2_tipo_preparacion = $request->input('p2_tipo_preparacion');
            $p2Row->p2_precalentamiento = $request->input('p2_precalentamiento');
            $p2Row->p2_sold_inicial = $request->input('p2_sold_inicial');
            $p2Row->p2_sold_aplicada = $request->input('p2_sold_aplicada');
            $p2Row->p2_sold_final = $request->input('p2_sold_final');
            $p2Row->p2_corr_inicial = $request->input('p2_corr_inicial');
            $p2Row->p2_corr_aplicada = $request->input('p2_corr_aplicada');
            $p2Row->p2_corr_final = $request->input('p2_corr_final');
            $p2Row->p2_gas_argon = $request->input('p2_gas_argon');
            $p2Row->p2_velocidad_calculada = $request->input('p2_velocidad_calculada');
            $p2Row->p2_resultado = $request->input('p2_resultado');
            $p2Row->p2_defecto_pta = $request->input('p2_defecto_pta', 'Ninguno');
            $p2Row->p2_observaciones = $request->input('p2_observaciones');

            // Asignar valor principal al campo correcto según el tipo seleccionado
            $p2Row->p2_d_conexion_pico = ($p2Tipo === 'D_Conexion_pico') ? $request->input('p2_valor_principal') : null;
            $p2Row->p2_d_conexion_obt = ($p2Tipo === 'D_Conexion_obt') ? $request->input('p2_valor_principal') : null;
            $p2Row->p2_perfilado = ($p2Tipo === 'Perfilado') ? $request->input('p2_valor_principal') : null;

            $p2Row->save();

            // Registrar log de auditoría para 2da pasada PTA
            \App\Models\SystemLog::create([
                'user_matricula' => Auth::user()->matricula,
                'action' => 'Segunda Pasada PTA',
                'details' => "El operador registró una Segunda Pasada para la pieza {$nPieza} (OT: {$piezaBase->id_ot}).",
                'ot' => $piezaBase->id_ot,
                'clase' => $piezaBase->id_clase,
                'n_pieza' => $nPieza,
                'h_inicio' => now()->subMinute()->format('H:i:s'),
                'h_termino' => now()->format('H:i:s')
            ]);
        }

        return redirect()->route('pta.segunda_pasada', [
            'ot_id' => $request->input('ot_id'),
            'clase_id' => $request->input('clase_id'),
            'n_pieza' => $request->input('n_pieza'),
        ])->with('success', '2da pasada guardada correctamente para la pieza ' . $request->input('n_pieza') . '.');
    }

    // ═══════════════════════════════════════════════════════════════════════
    // MÉTODO AUXILIAR PARA OTRAS VISTAS (piecesInProgress)
    // ═══════════════════════════════════════════════════════════════════════

    /**
     * Helper para contar piezas como juegos (sets) siguiendo la lógica del Dashboard.
     * Si termina en 'J', cuenta como 1 juego completo. De lo contrario, cuenta como 0.5.
     */
    private static function countAsGames($piezas): float
    {
        $count = 0.0;
        foreach ($piezas as $p) {
            if (str_ends_with(trim($p->n_pieza), 'J')) {
                $count += 1.0;
            } else {
                $count += 0.5;
            }
        }
        return (float) $count;
    }

    /**
     * Construye los datos de la card PTA para una OT dada.
     * Devuelve null si la OT no pasó por PTA o no hay actividad relacionada.
     */
    public static function buildCardData(string $otId, int $claseId): ?array
    {
        // 1. Obtener TODAS las piezas de esta clase en esta OT
        $piezasClase = Pieza::where('id_ot', $otId)
            ->where('id_clase', $claseId)
            ->get();

        if ($piezasClase->isEmpty()) {
            return null;
        }

        // 2. Identificar actividad PTA (resultados o piezas en proceso que contenga 'PTA')
        $piezaIds = $piezasClase->pluck('id');
        $resultados = PtaResultado::whereIn('pieza_id', $piezaIds)
            ->get()
            ->keyBy('pieza_id');

        $hayActividadPTA = $resultados->isNotEmpty() || $piezasClase->contains(function ($p) {
            return str_contains($p->proceso, 'PTA');
        });

        if (!$hayActividadPTA) {
            return null;
        }

        // 3. Población "viable": solo piezas que NO han sido rechazadas (liberacion != 2)
        $piezasViables = $piezasClase->filter(function ($p) {
            return (int) $p->liberacion !== 2;
        });

        $totalJuegos = self::countAsGames($piezasViables);

        // Si no quedan piezas viables (todas rechazadas), ocultamos la card
        if ($totalJuegos == 0) {
            return null;
        }

        $piezasLiberadas = $piezasViables->filter(function ($p) use ($resultados) {
            $res = $resultados->get($p->id);
            return $res && (int) $res->liberado_por_admin === 1;
        });

        $piezasSinLiberar = $piezasViables->reject(function ($p) use ($resultados) {
            $res = $resultados->get($p->id);
            return $res && (int) $res->liberado_por_admin === 1;
        });

        $piezasTerminadasPTA = $piezasViables->filter(function ($p) use ($resultados) {
            // Se considera terminada para el flujo de PTA si:
            // 1. Ya tiene un registro de resultado (pasó por la inspección)
            // 2. O si el sistema lo marca como "bueno" para Soldadura PTA (está en el proceso y no rechazado)
            return $resultados->has($p->id) || str_contains($p->proceso, 'PTA');
        });

        return [
            'totalPTA' => $totalJuegos,
            'terminadas' => self::countAsGames($piezasTerminadasPTA),
            'liberadas' => self::countAsGames($piezasLiberadas),
            'rechazadas' => 0,
            'sinLiberar' => self::countAsGames($piezasSinLiberar),
        ];
    }
}

