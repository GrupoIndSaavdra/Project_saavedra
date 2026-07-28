<?php

namespace App\Http\Controllers;

use App\Models\CalidadMaquinadoDoc;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * CalidadMaquinadosController
 *
 * Vista de solo lectura para el perfil de Calidad.
 * Muestra los documentos de Maquinados (Dibujos y Ayudas Visuales)
 * indexados por el comando calidad:sync-maquinados.
 *
 * Acceso restringido al perfil de Calidad (perfil = 4) y Administrador (perfil = 1).
 */
class CalidadMaquinadosController extends Controller
{
    /**
     * Perfiles de usuario con acceso.
     * 1 = Administrador | 4 = Calidad
     */
    private const PERFILES_PERMITIDOS = ['1', '3', '4'];

    // =========================================================================
    // GATE DE ACCESO
    // =========================================================================

    private function verificarAcceso(): void
    {
        /** @var \App\Models\User|null $user */
        $user = Auth::user();

        if (!$user || !in_array((string) $user->perfil, self::PERFILES_PERMITIDOS, true)) {
            abort(403, 'Acceso restringido. Solo Calidad y Administradores pueden ver esta sección.');
        }
    }

    // =========================================================================
    // VISTA PRINCIPAL
    // =========================================================================

    /**
     * Muestra la interfaz de solo lectura con las tres tablas:
     *   1. Dibujos de Maquinados (activos)
     *   2. Ayudas Visuales (activas)
     *   3. Documentos Inactivos (todos los tipos)
     */
    public function index(Request $request)
    {
        $this->verificarAcceso();

        // ── Parámetros de filtro ──────────────────────────────────────────────
        $filtroOt     = trim($request->query('ot', ''));
        $filtroClase  = trim($request->query('clase', ''));
        $filtroProceso = trim($request->query('proceso', ''));
        $desde        = $request->query('desde', '');
        $hasta        = $request->query('hasta', '');

        // ── Query base con filtros compartidos ───────────────────────────────
        $queryBase = CalidadMaquinadoDoc::query()
            ->when($filtroOt !== '', fn($q) => $q->where('ot', $filtroOt))
            ->when($filtroClase !== '', fn($q) => $q->where('clase', $filtroClase))
            ->when($filtroProceso !== '', fn($q) => $q->where('proceso', $filtroProceso))
            ->when($desde !== '', fn($q) => $q->whereDate('fecha_archivo', '>=', $desde))
            ->when($hasta !== '', fn($q) => $q->whereDate('fecha_archivo', '<=', $hasta));

        // ── Tabla 1: Dibujos activos ─────────────────────────────────────────
        $dibujos = (clone $queryBase)
            ->where('tipo', 'dibujo')
            ->where('estado', 'activo')
            ->orderBy('ot')
            ->orderBy('nombre_archivo')
            ->get();

        // ── Tabla 2: Ayudas activas ──────────────────────────────────────────
        $ayudas = (clone $queryBase)
            ->where('tipo', 'ayuda')
            ->where('estado', 'activo')
            ->orderBy('ot')
            ->orderBy('nombre_archivo')
            ->get();

        // ── Tabla 3: Inactivos (ambos tipos) ─────────────────────────────────
        $inactivos = (clone $queryBase)
            ->where('estado', 'inactivo')
            ->orderByDesc('updated_at')
            ->get();

        // ── Listas de valores únicos SOLO de documentos activos ──────────────
        // OT:     solo de Dibujos (las Ayudas no tienen OT)
        // Clase:  de ambos tipos
        // Proceso: solo de Ayudas (los Dibujos pueden no tenerlo)
        $listaOts      = CalidadMaquinadoDoc::query()->where('estado', 'activo')->where('tipo', 'dibujo')->distinct()->orderBy('ot')->pluck('ot')->filter()->values();
        $listaClases   = CalidadMaquinadoDoc::query()->where('estado', 'activo')->distinct()->orderBy('clase')->pluck('clase')->filter()->values();
        $listaProcesos = CalidadMaquinadoDoc::query()->where('estado', 'activo')->where('tipo', 'ayuda')->distinct()->orderBy('proceso')->pluck('proceso')->filter()->values();

        // ── Stats globales (sin filtros) ──────────────────────────────────────
        $totalDibujos   = CalidadMaquinadoDoc::query()->where('tipo', 'dibujo')->where('estado', 'activo')->count();
        $totalAyudas    = CalidadMaquinadoDoc::query()->where('tipo', 'ayuda')->where('estado', 'activo')->count();
        $totalInactivos = CalidadMaquinadoDoc::query()->where('estado', 'inactivo')->count();

        return view('quality.machining_index', compact(
            'dibujos',
            'ayudas',
            'inactivos',
            'listaOts',
            'listaClases',
            'listaProcesos',
            'filtroOt',
            'filtroClase',
            'filtroProceso',
            'desde',
            'hasta',
            'totalDibujos',
            'totalAyudas',
            'totalInactivos',
        ));
    }

    // =========================================================================
    // SERVIR ARCHIVOS (Solo Lectura, Protegido)
    // =========================================================================

    /**
     * Sirve un PDF / imagen desde el directorio de respaldo CALIDAD_MAQUINADOS.
     * Nunca expone la ruta de sistema; valida la ruta contra BD.
     */
    public function serveFile(Request $request): BinaryFileResponse
    {
        $this->verificarAcceso();

        $id = (int) $request->query('id', 0);

        if ($id <= 0) {
            abort(422, 'Parámetro inválido.');
        }

        /** @var CalidadMaquinadoDoc|null $doc */
        $doc = CalidadMaquinadoDoc::query()->find($id);

        if (!$doc) {
            abort(404, 'Documento no encontrado en el índice.');
        }

        if (!Storage::disk('local')->exists($doc->ruta_storage)) {
            abort(404, 'El archivo físico de respaldo no se encontró.');
        }

        /** @var \Illuminate\Filesystem\FilesystemAdapter $disk */
        $disk     = Storage::disk('local');
        $fullPath = $disk->path($doc->ruta_storage);
        $ext      = strtolower(pathinfo($doc->nombre_archivo, PATHINFO_EXTENSION));

        $mimeMap = [
            'pdf'  => 'application/pdf',
            'png'  => 'image/png',
            'jpg'  => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'dwg'  => 'application/octet-stream',
            'dxf'  => 'application/octet-stream',
        ];

        $mime = $mimeMap[$ext] ?? 'application/octet-stream';

        return response()->file($fullPath, [
            'Content-Type'        => $mime,
            'Content-Disposition' => 'inline; filename="' . $doc->nombre_archivo . '"',
        ]);
    }

    // =========================================================================
    // API — Datos para el frontend reactivo (AJAX)
    // =========================================================================

    /**
     * Devuelve los documentos filtrados en formato JSON para consumo del JS.
     * Útil si en el futuro se desea hacer el filtrado completamente en el cliente.
     */
    public function getDocs(Request $request): JsonResponse
    {
        $this->verificarAcceso();

        $filtroOt      = trim($request->query('ot', ''));
        $filtroClase   = trim($request->query('clase', ''));
        $filtroProceso = trim($request->query('proceso', ''));
        $desde         = $request->query('desde', '');
        $hasta         = $request->query('hasta', '');

        $query = CalidadMaquinadoDoc::query()
            ->when($filtroOt !== '', fn($q) => $q->where('ot', $filtroOt))
            ->when($filtroClase !== '', fn($q) => $q->where('clase', $filtroClase))
            ->when($filtroProceso !== '', fn($q) => $q->where('proceso', $filtroProceso))
            ->when($desde !== '', fn($q) => $q->whereDate('fecha_archivo', '>=', $desde))
            ->when($hasta !== '', fn($q) => $q->whereDate('fecha_archivo', '<=', $hasta));

        $dibujos   = (clone $query)->where('tipo', 'dibujo')->where('estado', 'activo')->orderBy('ot')->get();
        $ayudas    = (clone $query)->where('tipo', 'ayuda')->where('estado', 'activo')->orderBy('ot')->get();
        $inactivos = (clone $query)->where('estado', 'inactivo')->orderByDesc('updated_at')->get();

        return response()->json([
            'dibujos'   => $dibujos,
            'ayudas'    => $ayudas,
            'inactivos' => $inactivos,
        ]);
    }
}
