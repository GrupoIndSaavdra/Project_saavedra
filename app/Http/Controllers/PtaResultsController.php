<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePtaResultadoRequest;
use App\Models\Orden_trabajo;
use App\Models\Pieza;
use App\Models\PtaResultado;
use App\Models\SoldaduraPTA;
use Illuminate\Http\Request;

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
     * Se comprueba existencia de piezas con proceso de soldadura PTA para la clase.
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
     * y están terminadas correctamente (error = 'Ninguno', no rechazadas).
     */
    private function getPiezasPTA(string $otId, int $claseId)
    {
        return Pieza::where('id_ot', $otId)
            ->where('id_clase', $claseId)
            ->where('proceso', 'Soldadura PTA')
            ->where('error', 'Ninguno')
            ->where('liberacion', '!=', 2)
            ->orderBy('n_pieza')
            ->get();
    }

    /**
     * Sube una imagen al disco public y devuelve la ruta relativa.
     * Si ya existía una imagen anterior, la elimina.
     */
    private function subirImagen($file, ?string $rutaAnterior, string $prefijo): string
    {
        $directorioDestino = public_path('pta_resultados');

        if (!file_exists($directorioDestino)) {
            mkdir($directorioDestino, 0755, true);
        }

        if ($rutaAnterior && file_exists(public_path($rutaAnterior))) {
            unlink(public_path($rutaAnterior));
        }

        $nombre = $prefijo . '_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
        $file->move($directorioDestino, $nombre);

        return 'pta_resultados/' . $nombre;
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

        $piezas = $this->getPiezasPTA($ot_id, $clase_id);

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

        return view('pta_views.results', compact(
            'ot',
            'claseSeleccionada',
            'otsConPTA',
            'piezas',
            'piezasPendientes',
            'piezaSeleccionada',
            'resultado',
            'todosResultados'
        ));
    }

    /**
     * POST admin/pta/results/{ot_id}
     * Guarda o actualiza el resultado de Soldadura PTA de una pieza.
     */
    public function store(StorePtaResultadoRequest $request, string $ot_id)
    {
        $ot = Orden_trabajo::findOrFail($ot_id);

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
                'pico_sold'
            );
        }
        if ($request->hasFile('imagen_conexion_soldadura')) {
            $resultado->imagen_conexion_soldadura = $this->subirImagen(
                $request->file('imagen_conexion_soldadura'),
                $resultado->imagen_conexion_soldadura,
                'conexion_sold'
            );
        }
        if ($request->hasFile('imagen_perfilado_soldadura')) {
            $resultado->imagen_perfilado_soldadura = $this->subirImagen(
                $request->file('imagen_perfilado_soldadura'),
                $resultado->imagen_perfilado_soldadura,
                'perfilado_sold'
            );
        }

        $resultado->save();

        // ── Auto-liberar la pieza actual al guardar ───────────────────────────
        $resultado->liberado_por_admin = true;
        $resultado->save();

        // Verificar si la pieza complementaria también está guardada (juego completo)
        $msg = '💾 Resultados guardados y pieza liberada.';
        preg_match('/^\d+/', $resultado->n_pieza, $m);
        $prefix = $m[0] ?? null;

        if ($prefix) {
            $compañera = PtaResultado::where('ot_id', $ot_id)
                ->where('id', '!=', $resultado->id)
                ->whereHas('pieza', fn($q) => $q->where('n_pieza', 'like', $prefix . '%'))
                ->first();

            if ($compañera && $compañera->liberado_por_admin) {
                $msg = '✅ Juego ' . $prefix . ' completo — ambas piezas liberadas.';
            } elseif ($compañera) {
                $msg = '💾 Pieza guardada y liberada. Falta completar la pieza complementaria del juego ' . $prefix . '.';
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
            $piezasPTA = $this->getPiezasPTA($otSeleccionadaId, $claseSeleccionadaId);

            // Cargar resultados con relaciones para no hacer N+1
            $resultados = PtaResultado::where('ot_id', $otSeleccionadaId)
                ->whereHas('pieza', fn($q) => $q->where('id_clase', $claseSeleccionadaId))
                ->with(['pieza'])
                ->get()
                ->keyBy('pieza_id');

            // ── Datos técnicos de soldadura (soldaduraPTA_pza) ─────────────────
            // En esta BD, el proceso maestro se guarda en SoldaduraPTA con la nomenclatura:
            // "Soldadura_PTA_{NombreClase}_{OT}"
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

        return view('pta_views.analysis', compact(
            'otsConPTA',
            'otSeleccionadaId',
            'claseSeleccionadaId',
            'ot',
            'claseSeleccionada',
            'piezasPTA',
            'resultados',
            'piezasGroup'
        ));
    }

    // ═══════════════════════════════════════════════════════════════════════
    // MÉTODO AUXILIAR PARA OTRAS VISTAS (piecesInProgress)
    // ═══════════════════════════════════════════════════════════════════════

    /**
     * Construye los datos de la card PTA para una OT dada.
     * Devuelve null si la OT no pasó por PTA.
     *
     * @return array|null [pasoPorPTA, totalPTA, liberadas] o null
     */
    public static function buildCardData(string $otId, int $claseId): ?array
    {
        // Total de piezas que pasaron por PTA (buenas + malas, todas) en ESTA clase específica
        $totalPTA = Pieza::where('id_ot', $otId)
            ->where('id_clase', $claseId)
            ->where('proceso', 'Soldadura PTA')
            ->count();

        if ($totalPTA === 0) {
            return null;
        }

        // Piezas TERMINADAS correctamente: liberadas por Calidad (liberacion = 1)
        $terminadas = Pieza::where('id_ot', $otId)
            ->where('id_clase', $claseId)
            ->where('proceso', 'Soldadura PTA')
            ->where('error', 'Ninguno')
            ->where('liberacion', 1)
            ->count();


        // ── Clasificación de juegos completos ─────────────────────────────────
        // Un juego se considera COMPLETO cuando AMBAS piezas (H y M) tienen registro
        // en pta_resultados. Solo los juegos completos influyen en las barras.
        // Estado mixto (ej: 5H=liberada, 5M=rechazada) → va a "sin liberar".

        $allResultados = PtaResultado::where('pta_resultados.ot_id', $otId)
            ->join('piezas', 'piezas.id', '=', 'pta_resultados.pieza_id')
            ->where('piezas.id_clase', $claseId)
            ->where('piezas.error', 'Ninguno')
            ->select(
                'pta_resultados.liberado_por_admin',
                'piezas.n_pieza'
            )
            ->get();

        // Agrupar por prefijo numérico (ej. "5" para 5H y 5M)
        $byJuego = [];
        foreach ($allResultados as $r) {
            preg_match('/^\d+/', $r->n_pieza, $m);
            $key = $m[0] ?? $r->n_pieza;
            $byJuego[$key][] = $r;
        }

        $liberadas = 0;
        $rechazadas = 0;
        $sinLiberar = 0;

        foreach ($byJuego as $piezasJuego) {
            // Solo juegos COMPLETOS (ambas piezas H y M presentes)
            if (count($piezasJuego) < 2)
                continue;

            $todoLiberado = collect($piezasJuego)->every(fn($p) => $p->liberado_por_admin);

            if ($todoLiberado) {
                $liberadas++;
            } else {
                // Estado mixto o ambas pendientes → sin liberar
                $sinLiberar++;
            }
        }

        if ($totalPTA === 0)
            return null;

        return [
            'totalPTA' => $totalPTA,    // total que pasó por el proceso
            'terminadas' => $terminadas,  // terminadas sin error
            'liberadas' => $liberadas,   // juegos completos con AMBAS piezas liberadas
            'rechazadas' => $rechazadas,  // juegos completos con AMBAS piezas rechazadas
            'sinLiberar' => $sinLiberar,  // juegos completos en estado mixto/pendiente
        ];
    }
}
