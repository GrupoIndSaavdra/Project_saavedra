<?php

namespace App\Http\Controllers;

use App\Models\SoldaduraPTA_pza;
use Illuminate\Http\Request;

class SoldaduraPTAController extends Controller
{
    /**
     * Tipos de sub-fila, en orden de aparición en la tabla.
     * Cada pieza (M o H) genera exactamente 3 registros en soldaduraPTA_pza.
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
     * @param  \Illuminate\Http\Request  $request
     * @param  int|null  $index  — no se usa en PTA (legacy param)
     */
    public function storePiece($request, $index = null)
    {
        $pieceIds = $request->input('piece_id', []);
        if (empty($pieceIds)) {
            return;
        }

        foreach ($pieceIds as $key => $pieceId) {
            if (!$pieceId) {
                continue;   // fila nueva sin ID — ignorar
            }

            $piece = SoldaduraPTA_pza::find($pieceId, ['*']);
            if (!$piece) {
                continue;
            }

            $tipo = $request->tipo_medida[$key] ?? null;

            // ── Campos comunes a las 3 sub-filas (1ra pasada) ──
            $piece->tipo_medida = $tipo;
            $piece->vl = $request->vl[$key] ?? null;
            $piece->tipo_preparacion = $request->tipo_preparacion[$key] ?? null;
            $piece->sold_inicial = $request->sold_inicial[$key] ?? null;
            $piece->sold_aplicada = $request->sold_aplicada[$key] ?? null;
            $piece->sold_final = $request->sold_final[$key] ?? null;
            $piece->corr_inicial = $request->corr_inicial[$key] ?? null;
            $piece->corr_aplicada = $request->corr_aplicada[$key] ?? null;
            $piece->corr_final = $request->corr_final[$key] ?? null;
            $piece->gas_argon = $request->gas_argon[$key] ?? null;
            $piece->velocidad_calculada = $request->velocidad_calculada[$key] ?? null;
            $piece->resultado = $request->resultado[$key] ?? null;
            $piece->defecto_pta = $request->defecto_pta[$key] ?? 'Ninguno';

            // Mantener campo 'error' (legacy) sincronizado con defecto_pta
            $piece->error = $piece->defecto_pta;

            // ── Campo de valor principal según tipo_medida (1ra pasada) ──
            if ($tipo === 'D_Conexion_pico') {
                $piece->d_conexion_pico = $request->d_conexion_pico[$key] ?? null;
                $piece->precalentamiento = $request->precalentamiento[$key] ?? null;
                $piece->observaciones = $request->observaciones[$key] ?? null;
            } elseif ($tipo === 'D_Conexion_obt') {
                $piece->d_conexion_obt = $request->d_conexion_obt[$key] ?? null;
            } elseif ($tipo === 'Perfilado') {
                $piece->perfilado = $request->perfilado[$key] ?? null;
            }

            $piece->estado = 2;
            $piece->save();

            // ── 2da pasada (Se crea un ROW separado para toda la pieza) ──
            // Solo lo hacemos UNA VEZ por pieza ($nPiezaRef), usando la iteración de la primera fila
            // (D_Conexion_pico) como ancla para no crear 3 filas de 2da pasada.
            $nPiezaRef = $request->n_pieza_ref[$key] ?? $piece->n_pieza;
            $p2Activa = $nPiezaRef ? ($request->input("p2_activa.{$nPiezaRef}") === '1') : false;

            if ($tipo === 'D_Conexion_pico' && $p2Activa && $nPiezaRef) {
                // Buscar si ya existe la fila de 2da pasada para esta pieza
                $p2Row = SoldaduraPTA_pza::where('id_proceso', $piece->id_proceso)
                    ->where('n_pieza', $nPiezaRef)
                    ->where('p2_activa', '=', 1)
                    ->first();

                if (!$p2Row) {
                    $p2Row = new SoldaduraPTA_pza();
                    $p2Row->id_proceso = $piece->id_proceso;
                    // Sufijo para evitar error UNIQUE CONSTRAINT al crear esta 4ta fila
                    $p2Row->id_pza = $piece->id_pza . '_P2';
                    $p2Row->id_meta = $piece->id_meta;
                    $p2Row->n_juego = $piece->n_juego;
                    $p2Row->n_pieza = $nPiezaRef;
                    $p2Row->p2_activa = true;
                    $p2Row->estado = 2;
                }

                // Todas las columnas principales de la 1ra pasada en Null (para que en el reporte salgan vacías)
                $p2Row->tipo_medida = 'Segunda_Pasada'; // Marca para diferenciarlo de las 3 medidas normales
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

                // Estos ya eran nullable en la base original
                $p2Row->temp_calentado = null;
                $p2Row->temp_dispositivo = null;
                $p2Row->limpieza = null;
                $p2Row->error = 'Ninguno';
                $p2Row->observaciones = null;

                // ── Llenar las columnas p2_* ──
                $p2Row->p2_vl = $request->input("p2_vl.{$nPiezaRef}");
                $p2Row->p2_tipo_preparacion = $request->input("p2_tipo_preparacion.{$nPiezaRef}");
                $p2Row->p2_precalentamiento = $request->input("p2_precalentamiento.{$nPiezaRef}");
                $p2Row->p2_sold_inicial = $request->input("p2_sold_inicial.{$nPiezaRef}");
                $p2Row->p2_sold_aplicada = $request->input("p2_sold_aplicada.{$nPiezaRef}");
                $p2Row->p2_sold_final = $request->input("p2_sold_final.{$nPiezaRef}");
                $p2Row->p2_corr_inicial = $request->input("p2_corr_inicial.{$nPiezaRef}");
                $p2Row->p2_corr_aplicada = $request->input("p2_corr_aplicada.{$nPiezaRef}");
                $p2Row->p2_corr_final = $request->input("p2_corr_final.{$nPiezaRef}");
                $p2Row->p2_gas_argon = $request->input("p2_gas_argon.{$nPiezaRef}");
                $p2Row->p2_velocidad_calculada = $request->input("p2_velocidad_calculada.{$nPiezaRef}");
                $p2Row->p2_resultado = $request->input("p2_resultado.{$nPiezaRef}");
                $p2Row->p2_defecto_pta = $request->input("p2_defecto_pta.{$nPiezaRef}", 'Ninguno');
                $p2Row->p2_observaciones = $request->input("p2_observaciones.{$nPiezaRef}");

                // Mapear el valor principal según el select 'p2_tipo_medida'
                $p2Tipo = $request->input("p2_tipo_medida.{$nPiezaRef}");
                $p2Val = $request->input("p2_valor.{$nPiezaRef}");

                $p2Row->p2_d_conexion_pico = ($p2Tipo === 'D_Conexion_pico') ? $p2Val : null;
                $p2Row->p2_d_conexion_obt = ($p2Tipo === 'D_Conexion_obt') ? $p2Val : null;
                $p2Row->p2_perfilado = ($p2Tipo === 'Perfilado') ? $p2Val : null;

                $p2Row->save();
            } elseif ($tipo === 'D_Conexion_pico' && !$p2Activa && $nPiezaRef) {
                // Si el checkbox se desmarca, se puede borrar la fila de 2da pasada si existía
                SoldaduraPTA_pza::where('id_proceso', $piece->id_proceso)
                    ->where('n_pieza', $nPiezaRef)
                    ->where('p2_activa', 1)
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
     * @param  int  $idProceso
     * @return \Illuminate\Support\Collection  agrupada por n_pieza
     */
    public function buildPiezasGroup(int $idProceso)
    {
        // El orden por defecto en SQL para strings pone '1H' antes que '1M' (H antes que M en alfabeto).
        // Usamos una extracción numérica seguida del sufijo para que ordene 1, 2, 3... y luego M, H.
        // Dado que típicamente es "Número + Letra", ordenamos numéricamente primero, y luego forzamos 'M' antes de 'H' si es necesario.
        return SoldaduraPTA_pza::where('id_proceso', $idProceso)
            ->where('estado', 2)
            ->orderByRaw('CAST(n_pieza AS UNSIGNED) ASC') // Ordena por el número: '1M' y '1H' serán ambos 1
            ->orderByRaw("RIGHT(n_pieza, 1) DESC")        // Letra final: 'M' (Macho) antes que 'H' (Hembra) porque M > H lexicográficamente en DESC
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

        $rows = SoldaduraPTA_pza::where('id_proceso', $idProceso)
            ->where('n_pieza', $nPieza)
            ->where('estado', 2)
            ->get()
            ->keyBy('tipo_medida');

        foreach (self::TIPOS_MEDIDA as $tipo) {
            $piece = $rows[$tipo] ?? null;
            if (!$piece) {
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
}
