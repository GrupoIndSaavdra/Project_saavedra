<?php

namespace App\Http\Controllers;

use App\Models\HerramientaImagen;
use App\Models\HerramientaTecamac;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

/**
 * HerramientasTecamacController
 *
 * Permisos:
 *   Perfil 1 (Admin)   → puede ver, editar, inactivar y reactivar (NO dar de alta)
 *   Perfil 5 (Almacén) → CRUD completo: alta, editar, inactivar, reactivar
 */
class HerramientasTecamacController extends Controller
{
    private const PERFILES_PERMITIDOS = ['1', '5'];
    private const PERFILES_CRUD       = ['1', '5'];   // editar / inactivar / reactivar
    private const PERFILES_ALTA       = ['5'];         // SOLO Almacén puede dar de alta
    private const DIR_PUBLIC          = 'herramientas_tecamac';

    private const TIPOS_IMAGEN = [
        'herramienta',
        'accesorio',
        'tornilleria',
        'tornilleria_accesorio',
        'imagen_fisica',
    ];

    /** Lista oficial de procesos del sistema */
    public const PROCESOS = [
        'Cepillado',
        'Desbaste Exterior',
        'Revisión Laterales',
        '1ª Operación',
        'Barreno Maniobra',
        '2ª Operación',
        'Soldadura',
        'Soldadura PTA',
        'Rectificado',
        'Asentado',
        'Calificado',
        'Acabado Bombillo',
        'Acabado Molde',
        'Barreno Profundidad',
        'Cavidades',
        'Copiado',
        'OffSet',
        'Palomas',
        'Rebajes',
        'Grabado',
        'Operación Equipo',
        'Embudo CM',
        '1ª Op. Cabeza Soplo',
        '2ª Op. Cabeza Soplo',
    ];

    // ── ACCESO ────────────────────────────────────────────────────────────────

    private function verificarAcceso(): void
    {
        $user = Auth::user();
        if (!$user || !in_array((string) $user->perfil, self::PERFILES_PERMITIDOS, true)) {
            abort(403, 'Acceso restringido.');
        }
    }

    private function verificarCrud(): void
    {
        $user = Auth::user();
        if (!$user || !in_array((string) $user->perfil, self::PERFILES_CRUD, true)) {
            abort(403, 'Sin permiso para modificar el catálogo.');
        }
    }

    private function verificarAlta(): void
    {
        $user = Auth::user();
        if (!$user || !in_array((string) $user->perfil, self::PERFILES_ALTA, true)) {
            abort(403, 'Solo Almacén puede registrar nuevas herramientas.');
        }
    }

    // ── VISTA PRINCIPAL ───────────────────────────────────────────────────────

    public function index(Request $request)
    {
        $this->verificarAcceso();

        $perfil   = (string) Auth::user()->perfil;
        $modo     = $request->query('modo', 'activas');
        $busqueda = trim($request->query('q', ''));
        $filtroProceso = trim($request->query('proceso', ''));

        // ── Herramientas a mostrar según modo ─────────────────────────────────
        if ($modo === 'inactivas') {
            $query = HerramientaTecamac::query()->where('activo', false);
        } elseif ($modo === 'stock_bajo') {
            $query = HerramientaTecamac::query()
                ->where('activo', true)
                ->whereNotNull('minimo')
                ->whereColumn('cantidad_portaherramientas', '<', 'minimo');
        } else {
            $modo  = 'activas';
            $query = HerramientaTecamac::query()->where('activo', true);
        }

        // Filtro por proceso (JSON array: busca si contiene el proceso)
        if ($filtroProceso !== '') {
            $query->whereJsonContains('proceso', $filtroProceso);
        }

        // Filtro de búsqueda
        if ($busqueda !== '') {
            $query->where(function ($q) use ($busqueda) {
                $q->where('descripcion_herramienta', 'like', "%{$busqueda}%")
                  ->orWhere('nombre_herramienta', 'like', "%{$busqueda}%")
                  ->orWhere('descripcion_inserto', 'like', "%{$busqueda}%")
                  ->orWhere('nombre_accesorio', 'like', "%{$busqueda}%")
                  ->orWhere('proceso', 'like', "%{$busqueda}%");
            });
        }

        $herramientas = $query->with('imagenes')->orderBy('nombre_herramienta')->get();

        // ── Contadores globales ────────────────────────────────────────────────
        $totalActivas   = HerramientaTecamac::where('activo', true)->count();
        $totalInactivas = HerramientaTecamac::where('activo', false)->count();
        $totalStockBajo = HerramientaTecamac::where('activo', true)
            ->whereNotNull('minimo')
            ->whereColumn('cantidad_portaherramientas', '<', 'minimo')
            ->count();

        $esCrud = in_array($perfil, self::PERFILES_CRUD, true);
        $esAlta = in_array($perfil, self::PERFILES_ALTA, true);

        $procesos = self::PROCESOS;

        return view('herramientas.index', compact(
            'herramientas', 'busqueda', 'modo', 'filtroProceso',
            'totalActivas', 'totalInactivas', 'totalStockBajo',
            'esCrud', 'esAlta', 'procesos'
        ));
    }

    // ── STORE ─────────────────────────────────────────────────────────────────

    public function store(Request $request): JsonResponse
    {
        $this->verificarAlta();

        $validated = $request->validate([
            'proceso'                    => 'nullable|array',
            'proceso.*'                  => 'nullable|string|max:100',
            'nombre_herramienta'         => 'nullable|string|max:255',
            'descripcion_herramienta'    => 'nullable|string|max:255',
            'descripcion_inserto'        => 'nullable|string|max:255',
            'nombre_accesorio'           => 'nullable|string|max:255',
            'accesorios'                 => 'nullable|string|max:500',
            'cantidad_portaherramientas' => 'required|integer|min:0',
            'profundidad_corte'          => 'nullable|numeric|min:0',
            'rpm'                        => 'nullable|integer|min:0',
            'avances'                    => 'nullable|string|max:100',
            'minimo'                     => 'nullable|integer|min:0',
            'maximo'                     => 'nullable|integer|min:0',
            'img_herramienta.*'            => 'nullable|file|mimes:jpg,jpeg,png,webp|max:8192',
            'nom_herramienta.*'            => 'nullable|string|max:150',
            'img_accesorio.*'              => 'nullable|file|mimes:jpg,jpeg,png,webp|max:8192',
            'nom_accesorio.*'              => 'nullable|string|max:150',
            'img_tornilleria.*'            => 'nullable|file|mimes:jpg,jpeg,png,webp|max:8192',
            'nom_tornilleria.*'            => 'nullable|string|max:150',
            'img_tornilleria_accesorio.*'  => 'nullable|file|mimes:jpg,jpeg,png,webp|max:8192',
            'nom_tornilleria_accesorio.*'  => 'nullable|string|max:150',
            'img_imagen_fisica.*'          => 'nullable|file|mimes:jpg,jpeg,png,webp|max:8192',
            'nom_imagen_fisica.*'          => 'nullable|string|max:150',
        ]);

        $camposBase = collect($validated)->only([
            'nombre_herramienta', 'descripcion_herramienta', 'descripcion_inserto',
            'nombre_accesorio', 'accesorios', 'cantidad_portaherramientas',
            'profundidad_corte', 'rpm', 'avances', 'minimo', 'maximo',
        ])->toArray();
        // proceso viene como array desde los checkboxes
        $camposBase['proceso'] = array_values(array_filter($request->input('proceso', [])));

        $herramienta = HerramientaTecamac::create($camposBase);
        $this->guardarImagenesMultiples($request, $herramienta);

        return response()->json([
            'ok'          => true,
            'herramienta' => $herramienta->load('imagenes'),
            'message'     => 'Herramienta creada correctamente.',
        ], 201);
    }

    // ── UPDATE ────────────────────────────────────────────────────────────────

    public function update(Request $request, int $id): JsonResponse
    {
        $this->verificarCrud();

        /** @var HerramientaTecamac $h */
        $h = HerramientaTecamac::findOrFail($id);

        $validated = $request->validate([
            'proceso'                    => 'nullable|array',
            'proceso.*'                  => 'nullable|string|max:100',
            'nombre_herramienta'         => 'nullable|string|max:255',
            'descripcion_herramienta'    => 'nullable|string|max:255',
            'descripcion_inserto'        => 'nullable|string|max:255',
            'nombre_accesorio'           => 'nullable|string|max:255',
            'accesorios'                 => 'nullable|string|max:500',
            'cantidad_portaherramientas' => 'required|integer|min:0',
            'profundidad_corte'          => 'nullable|numeric|min:0',
            'rpm'                        => 'nullable|integer|min:0',
            'avances'                    => 'nullable|string|max:100',
            'minimo'                     => 'nullable|integer|min:0',
            'maximo'                     => 'nullable|integer|min:0',
            'img_herramienta.*'            => 'nullable|file|mimes:jpg,jpeg,png,webp|max:8192',
            'nom_herramienta.*'            => 'nullable|string|max:150',
            'img_accesorio.*'              => 'nullable|file|mimes:jpg,jpeg,png,webp|max:8192',
            'nom_accesorio.*'              => 'nullable|string|max:150',
            'img_tornilleria.*'            => 'nullable|file|mimes:jpg,jpeg,png,webp|max:8192',
            'nom_tornilleria.*'            => 'nullable|string|max:150',
            'img_tornilleria_accesorio.*'  => 'nullable|file|mimes:jpg,jpeg,png,webp|max:8192',
            'nom_tornilleria_accesorio.*'  => 'nullable|string|max:150',
            'img_imagen_fisica.*'          => 'nullable|file|mimes:jpg,jpeg,png,webp|max:8192',
            'nom_imagen_fisica.*'          => 'nullable|string|max:150',
            'delete_img_ids'               => 'nullable|array',
            'delete_img_ids.*'             => 'integer',
        ]);

        $camposBase = collect($validated)->only([
            'nombre_herramienta', 'descripcion_herramienta', 'descripcion_inserto',
            'nombre_accesorio', 'accesorios', 'cantidad_portaherramientas',
            'profundidad_corte', 'rpm', 'avances', 'minimo', 'maximo',
        ])->toArray();
        $camposBase['proceso'] = array_values(array_filter($request->input('proceso', [])));

        $h->update($camposBase);

        // Eliminar imágenes marcadas
        $deleteIds = $request->input('delete_img_ids', []);
        if (!empty($deleteIds)) {
            $toDelete = HerramientaImagen::whereIn('id', $deleteIds)
                ->where('herramienta_id', $h->id)
                ->get();
            foreach ($toDelete as $img) {
                $path = public_path($img->ruta);
                if (file_exists($path)) @unlink($path);
                $img->delete();
            }
        }

        $this->guardarImagenesMultiples($request, $h);

        return response()->json([
            'ok'          => true,
            'herramienta' => $h->load('imagenes')->fresh(),
            'message'     => 'Herramienta actualizada correctamente.',
        ]);
    }

    // ── DESTROY ───────────────────────────────────────────────────────────────

    public function destroy(int $id): JsonResponse
    {
        $this->verificarCrud();
        HerramientaTecamac::findOrFail($id)->update(['activo' => false]);
        return response()->json(['ok' => true, 'message' => 'Herramienta desactivada.']);
    }

    // ── REACTIVAR ─────────────────────────────────────────────────────────────

    public function reactivar(int $id): JsonResponse
    {
        $this->verificarCrud();
        HerramientaTecamac::findOrFail($id)->update(['activo' => true]);
        return response()->json(['ok' => true, 'message' => 'Herramienta reactivada correctamente.']);
    }

    // ── HELPERS ───────────────────────────────────────────────────────────────

    private function guardarImagenesMultiples(Request $request, HerramientaTecamac $h): void
    {
        $ordenBase = [];
        foreach (self::TIPOS_IMAGEN as $tipo) {
            $ordenBase[$tipo] = HerramientaImagen::where('herramienta_id', $h->id)
                ->where('tipo', $tipo)->max('orden') ?? -1;
        }

        foreach (self::TIPOS_IMAGEN as $tipo) {
            $fileKey   = 'img_' . $tipo;
            $nombreKey = 'nom_' . $tipo;

            if (!$request->hasFile($fileKey)) continue;

            $files   = $request->file($fileKey);
            $nombres = $request->input($nombreKey, []);

            foreach ($files as $idx => $file) {
                if (!$file || !$file->isValid()) continue;
                $ordenBase[$tipo]++;
                HerramientaImagen::create([
                    'herramienta_id' => $h->id,
                    'tipo'           => $tipo,
                    'nombre'         => $nombres[$idx] ?? null,
                    'ruta'           => $this->guardarImagen($file),
                    'orden'          => $ordenBase[$tipo],
                ]);
            }
        }
    }

    private function guardarImagen($file): string
    {
        $nombre  = time() . '_' . Str::random(6) . '.' . $file->getClientOriginalExtension();
        $destino = public_path(self::DIR_PUBLIC);
        if (!is_dir($destino)) mkdir($destino, 0755, true);
        $file->move($destino, $nombre);
        return self::DIR_PUBLIC . '/' . $nombre;
    }
}
