<?php

namespace App\Http\Controllers;

use App\Models\Clase;
use App\Models\LiberacionModeloFundicion;
use App\Models\Orden_trabajo;
use App\Models\Pieza;
use App\Models\PreOrdenFundicion;
use App\Models\Procesos;
use App\Models\RDimensional;
use App\Models\RemisionOt;
use App\Models\RInternoSalida;
use App\Models\RVisual;
use App\Models\ScarModelo;
use App\Models\TratamientoTermico;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;

class PriorityDocumentsController extends Controller
{
    private const DRAWINGS = [
        'maquinados' => ['DOCUMENTACION_GIS/DIBUJOS_MAQUINADOS', 'DIBUJOS_GIS'],
        'fundicion' => ['DOCUMENTACION_GIS/DIBUJOS_FUNDICION', 'FUNDICION_GIS'],
    ];

    private const ALLOWED_SIGNED_ROOTS = [
        'DOCUMENTACION_GIS/DIBUJOS_MAQUINADOS',
        'DOCUMENTACION_GIS/DIBUJOS_FUNDICION',
        'DIBUJOS_GIS',
        'FUNDICION_GIS',
        'DOCUMENTACION_GIS/ALMACEN_FUNDICION',
        'DOCUMENTACION_GIS/CALIDAD_FUNDICION',
        'DOCUMENTACION_GIS/AYUDAS_MAQUINADOS',
        'DOCUMENTACION_GIS/AYUDAS_FUNDICION',
        'DOCUMENTACION_GIS/REMISIONES',
        'DOCUMENTACION_GIS/REPORTES_INTERNOS_SALIDAS',
        'DOCUMENTACION_GIS/PROGRAMAS_MAQUINADOS',
        'DOCUMENTACION_GIS/PROGRAMAS_SOLDADURA_PTA',
    ];

    private const INLINE_EXTENSIONS = ['pdf', 'jpg', 'jpeg', 'png', 'gif', 'webp'];

    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request, Orden_trabajo $ot)
    {
        abort_unless(in_array((string) $request->user()->perfil, ['1', '2', '3', '4', '5', '6', '7', '8', '10', '0'], true), 403);

        $ot->loadMissing(['moldura', 'clases']);
        $claseIds = $ot->clases->pluck('id')->all();

        $hasPtaProcess = Pieza::query()
            ->where('id_ot', '=', $ot->id, 'and')
            ->where('proceso', '=', 'Soldadura PTA', 'and')
            ->exists();

        if (! $hasPtaProcess && ! empty($claseIds)) {
            $hasPtaProcess = Procesos::query()
                ->whereIn('id_clase', $claseIds, 'and', false)
                ->where('soldaduraPTA', '!=', 0, 'and')
                ->exists();
        }

        // Clasificar archivos de ALMACEN_FUNDICION y CALIDAD_FUNDICION una sola vez
        $fundicionBuckets = $this->collectFundicionBuckets($ot);

        $catalog = [
            ['key' => 'orden_trabajo', 'title' => 'Orden de Trabajo (PDF)', 'required' => true],
            ['key' => 'maquinados', 'title' => 'Dibujos de Maquinados', 'required' => true],
            ['key' => 'fundicion', 'title' => 'Dibujos de Fundición', 'required' => true],
            ['key' => 'preordenes', 'title' => 'Preórdenes de Fundición (Modelo / Casting)', 'required' => true],
            ['key' => 'liberacion_modelo', 'title' => 'Formatos de Liberación de Modelo', 'required' => true],
            ['key' => 'rechazo_modelo', 'title' => 'Formatos de Rechazo de Modelo', 'required' => false],
            ['key' => 'scar', 'title' => 'Reportes SCAR', 'required' => false],
            ['key' => 'extras_fundicion', 'title' => 'Documentos Extras y Escaneados de Fundición', 'required' => false],
            ['key' => 'ayudas_fundicion', 'title' => 'Ayudas Visuales de Fundición', 'required' => false],
            ['key' => 'tratamientos_termicos', 'title' => 'Tratamientos Térmicos', 'required' => true],
            ['key' => 'remisiones', 'title' => 'Remisiones', 'required' => true],
            ['key' => 'r_interno_salidas', 'title' => 'Reportes Internos de Salidas', 'required' => true],
            ['key' => 'volumetricos', 'title' => 'Reportes Volumétricos', 'required' => true],
            ['key' => 'dimensionales', 'title' => 'Reportes Dimensionales', 'required' => true],
            ['key' => 'piezas', 'title' => 'Reporte de Piezas', 'required' => true],
            ['key' => 'pta', 'title' => 'Reportes de PTA', 'required' => $hasPtaProcess],
            ['key' => 'programas_cnc', 'title' => 'Programas CNC (Maquinados)', 'required' => false],
            ['key' => 'programas_pta', 'title' => 'Programas Soldadura PTA', 'required' => $hasPtaProcess],
        ];

        $sections = [];
        foreach ($catalog as $item) {
            $type = $item['key'];
            $title = $item['title'];
            $required = $item['required'];

            try {
                $files = match ($type) {
                    'orden_trabajo' => $this->workOrderPdf($ot),
                    'maquinados' => $this->drawings($ot, 'maquinados'),
                    'fundicion' => $this->mergeUniqueFiles($this->drawings($ot, 'fundicion'), $fundicionBuckets['fundicion']),
                    'preordenes' => $this->preordenesFiles($ot, $fundicionBuckets['preordenes']),
                    'liberacion_modelo' => $this->liberacionFiles($ot, $fundicionBuckets['liberacion_modelo']),
                    'rechazo_modelo' => $this->rechazoFiles($ot, $fundicionBuckets['rechazo_modelo']),
                    'scar' => $this->scarFiles($ot, $fundicionBuckets['scar']),
                    'extras_fundicion' => $fundicionBuckets['extras_fundicion'],
                    'ayudas_fundicion' => $fundicionBuckets['ayudas_fundicion'],
                    'tratamientos_termicos' => $this->tratamientosFiles($ot, $claseIds),
                    'remisiones' => $this->remisionesFiles($ot),
                    'r_interno_salidas' => $this->rInternoSalidasFiles($ot),
                    'volumetricos', 'dimensionales' => $this->reports($ot, $type),
                    'piezas', 'pta' => $this->productionReports($ot, $type),
                    'programas_cnc' => $this->folderTreeFiles($ot, 'DOCUMENTACION_GIS/PROGRAMAS_MAQUINADOS', 'programa_cnc', 'Programa CNC'),
                    'programas_pta' => $this->folderTreeFiles($ot, 'DOCUMENTACION_GIS/PROGRAMAS_SOLDADURA_PTA', 'programa_pta', 'Programa PTA'),
                    default => [],
                };

                $sections[] = [
                    'key' => $type,
                    'title' => $title,
                    'required' => $required,
                    'files' => $files,
                    'error' => null,
                ];
            } catch (\Throwable $error) {
                Log::error('No se pudieron consultar los documentos de una OT en Prioridades.', [
                    'user_id' => $request->user()->id,
                    'ot' => $ot->id,
                    'section' => $type,
                    'error' => $error->getMessage(),
                ]);
                $sections[] = [
                    'key' => $type,
                    'title' => $title,
                    'required' => $required,
                    'files' => [],
                    'error' => 'No se pudo consultar esta sección. Vuelve a intentarlo.',
                ];
            }
        }

        return response()->json(['success' => true, 'ot' => $ot->id, 'sections' => $sections]);
    }

    public function availability(Request $request)
    {
        abort_unless(in_array((string) $request->user()->perfil, ['1', '2', '3', '4', '5', '6', '7', '8', '10', '0'], true), 403);
        $data = $request->validate([
            'ots' => 'required|array|max:100',
            'ots.*' => 'required|string|max:255',
        ]);

        try {
            $ids = Orden_trabajo::query()->whereIn('id', $data['ots'])->pluck('id')->all();
            // Todas las OTs existentes cuentan al menos con su Orden de Trabajo (PDF) y la vista de faltantes.
            $available = array_fill_keys($ids, true);

            return response()->json(['success' => true, 'available' => (object) $available]);
        } catch (\Throwable $error) {
            Log::error('No se pudo comprobar la disponibilidad de archivos en Prioridades.', [
                'user_id' => $request->user()->id,
                'error' => $error->getMessage(),
            ]);

            return response()->json(['success' => false, 'message' => 'No se pudo comprobar la disponibilidad de archivos.'], 500);
        }
    }

    public function openDrawing(Request $request, Orden_trabajo $ot)
    {
        abort_unless(in_array((string) $request->user()->perfil, ['1', '2', '3', '4', '5', '6', '7', '8', '10', '0'], true), 403);
        $type = $request->query('section');
        $path = $request->query('file');
        abort_unless(is_string($type) && is_string($path) && $path !== '', 404);

        $path = str_replace('\\', '/', $path);
        abort_if(str_contains($path, "\0") || preg_match('~(?:^|/)\.\.(?:/|$)~', $path), 404);

        $rootsToCheck = isset(self::DRAWINGS[$type])
            ? self::DRAWINGS[$type]
            : self::ALLOWED_SIGNED_ROOTS;

        $valid = false;
        $allowReproceso = ! isset(self::DRAWINGS[$type]);
        foreach ($rootsToCheck as $root) {
            if (! str_starts_with($path, $root.'/')) {
                continue;
            }
            $relative = substr($path, strlen($root) + 1);
            $folder = explode('/', $relative)[0] ?? '';
            if ($this->isOrderFolder($folder, (string) $ot->id, $allowReproceso)) {
                $valid = true;
                break;
            }
        }

        $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        if (isset(self::DRAWINGS[$type])) {
            abort_unless($valid && in_array($extension, $type === 'fundicion' ? ['pdf', 'dwg'] : ['pdf'], true), 404);
        } else {
            abort_unless($valid && ! in_array($extension, ['php', 'phtml', 'phar'], true), 404);
        }

        $disk = Storage::disk('local');
        abort_unless($disk->fileExists($path), 404, 'El archivo ya no está disponible.');

        if (in_array($extension, self::INLINE_EXTENSIONS, true)) {
            $mime = $extension === 'pdf'
                ? 'application/pdf'
                : ($disk->mimeType($path) ?: 'application/octet-stream');

            return response()->file($disk->path($path), [
                'Content-Type' => $mime,
                'Content-Disposition' => 'inline; filename="'.basename($path).'"',
            ]);
        }

        return $disk->download($path);
    }

    public function openProductionReport(Request $request, Orden_trabajo $ot, string $type)
    {
        abort_unless(in_array((string) $request->user()->perfil, ['1', '2', '3', '4', '5', '6', '7', '8', '10', '0'], true), 403);
        abort_unless(in_array($type, ['piezas', 'pta'], true), 404);

        if ($type === 'piezas') {
            $hasPieces = Pieza::query()->where('id_ot', '=', $ot->id, 'and')
                ->whereHas('clase', fn ($query) => $query->where('finalizada', '=', 0, 'and'))->exists();
            abort_unless($hasPieces, 404, 'No hay piezas activas para esta OT.');

            return app(PzasGeneralesController::class)->search([
                'workOrder' => $ot->id,
                'action' => 'pdf',
            ], 'admin', true);
        }

        $clase = Clase::query()->where('id_ot', '=', $ot->id, 'and')
            ->where('id', '=', $request->query('clase_id'), 'and')->firstOrFail();
        abort_unless($clase->piezas()->where('id_ot', '=', $ot->id, 'and')
            ->where('proceso', '=', 'Soldadura PTA', 'and')->exists(), 404, 'No hay piezas PTA para esta clase.');

        $pdfRequest = $request->duplicate(['ot_id' => $ot->id, 'clase_id' => $clase->id]);

        return app(PtaResultsController::class)->analysisPDF($pdfRequest, true);
    }

    private function workOrderPdf(Orden_trabajo $ot): array
    {
        $moldura = $ot->moldura ? $ot->moldura->nombre : 'General';

        return [[
            'name' => 'Orden de Trabajo #'.$ot->id.'.pdf',
            'class' => $moldura,
            'detail' => 'PDF generado al abrir con los datos actuales',
            'available' => true,
            'url' => route('generatePDFWO', ['wo' => $ot->id]),
        ]];
    }

    private function productionReports(Orden_trabajo $ot, string $type): array
    {
        if ($type === 'piezas') {
            $hasPieces = Pieza::query()->where('id_ot', '=', $ot->id, 'and')
                ->whereHas('clase', fn ($query) => $query->where('finalizada', '=', 0, 'and'))->exists();

            return $hasPieces ? [[
                'name' => 'Reporte de piezas - OT '.$ot->id.'.pdf',
                'class' => 'Todas las clases activas',
                'detail' => 'PDF generado al abrir con los datos actuales',
                'available' => true,
                'url' => route('master.priorities.documents.production', ['ot' => $ot->id, 'type' => $type]),
            ]] : [];
        }

        return Clase::query()->where('id_ot', '=', $ot->id, 'and')
            ->whereHas('piezas', fn ($query) => $query->where('id_ot', '=', $ot->id, 'and')
                ->where('proceso', '=', 'Soldadura PTA', 'and'))
            ->orderBy('nombre')->get()->map(fn ($clase) => [
                'name' => 'Reporte de PTA - OT '.$ot->id.'.pdf',
                'class' => trim($clase->nombre.' '.$clase->tamanio),
                'detail' => 'PDF generado al abrir con los datos actuales',
                'available' => true,
                'url' => route('master.priorities.documents.production', [
                    'ot' => $ot->id, 'type' => $type, 'clase_id' => $clase->id,
                ]),
            ])->all();
    }

    private function drawings(Orden_trabajo $ot, string $type): array
    {
        $disk = Storage::disk('local');
        $files = [];
        foreach (self::DRAWINGS[$type] as $root) {
            if (! $disk->exists($root)) {
                continue;
            }
            foreach ($disk->directories($root) as $directory) {
                if (! $this->isOrderFolder(basename($directory), (string) $ot->id)) {
                    continue;
                }

                foreach ($disk->allFiles($directory) as $path) {
                    $path = str_replace('\\', '/', $path);
                    $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));
                    if (! in_array($extension, $type === 'fundicion' ? ['pdf', 'dwg'] : ['pdf'], true)) {
                        continue;
                    }

                    $relative = substr($path, strlen($directory) + 1);
                    $parts = explode('/', $relative);
                    $files[] = [
                        'name' => basename($path),
                        'class' => count($parts) > 1 ? $parts[0] : 'General',
                        'detail' => basename($directory),
                        'available' => true,
                        'url' => URL::signedRoute('master.priorities.documents.open', [
                            'ot' => $ot->id, 'section' => $type, 'file' => $path,
                        ]),
                    ];
                }
            }
        }

        return collect($files)->sortBy(['class', 'name', 'detail'])->values()->all();
    }

    private function collectFundicionBuckets(Orden_trabajo $ot): array
    {
        $buckets = [
            'fundicion' => [],
            'preordenes' => [],
            'liberacion_modelo' => [],
            'rechazo_modelo' => [],
            'scar' => [],
            'ayudas_fundicion' => [],
            'extras_fundicion' => [],
        ];

        $disk = Storage::disk('local');
        $seenKeys = [];

        foreach ([
            'Almacén Fundición' => 'DOCUMENTACION_GIS/ALMACEN_FUNDICION',
            'Calidad Fundición' => 'DOCUMENTACION_GIS/CALIDAD_FUNDICION',
        ] as $ownerLabel => $root) {
            if (! $disk->exists($root)) {
                continue;
            }

            foreach ($disk->directories($root) as $directory) {
                $folderName = basename($directory);
                if (! $this->isOrderFolder($folderName, (string) $ot->id, true)) {
                    continue;
                }

                foreach ($disk->allFiles($directory) as $path) {
                    $path = str_replace('\\', '/', $path);
                    $name = basename($path);
                    if ($name === '' || str_starts_with($name, '.')) {
                        continue;
                    }
                    $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
                    if (in_array($ext, ['php', 'phtml', 'phar'], true)) {
                        continue;
                    }

                    $relative = ltrim(substr($path, strlen($directory)), '/');
                    $parts = explode('/', $relative);
                    $firstSeg = $parts[0] ?? '';
                    $reservedTop = [
                        'ayudas_visuales', 'ayudas_visuales_fundicion', 'dibujos_fundicion',
                        'documentos_aprobados', 'documentos_rechazados', 'escaneados',
                        'preordenes', 'formatos_liberacion', 'scar', 'fdrdm', 'fdldm',
                        'dwg_fundicion', 'extras', 'preorden_casting', 'preorden_modelo',
                        'confirmacion_modelo',
                    ];
                    $claseLabel = (count($parts) > 1 && ! in_array(strtolower($firstSeg), $reservedTop, true))
                        ? $firstSeg
                        : 'General';

                    $relLow = strtolower($relative);
                    $nameLow = strtolower($name);

                    // Clasificación por tipo de documento
                    if (str_contains($relLow, 'extras') || str_contains($relLow, 'escaneados') || str_starts_with($nameLow, 'escaneado_')) {
                        $bucket = 'extras_fundicion';
                        $detail = str_contains($relLow, 'extras') ? 'Extra · '.$ownerLabel : 'Escaneado · '.$ownerLabel;
                    } elseif (str_contains($relLow, 'scar') || str_contains($nameLow, 'scar')) {
                        $bucket = 'scar';
                        $detail = 'SCAR · '.$ownerLabel;
                    } elseif (str_contains($relLow, 'fdrdm') || str_contains($nameLow, 'rdm') || str_contains($nameLow, 'rechaz')) {
                        $bucket = 'rechazo_modelo';
                        $detail = 'Rechazo de modelo · '.$ownerLabel;
                    } elseif (
                        str_contains($relLow, 'formatos_liberacion')
                        || str_contains($relLow, 'fdldm')
                        || str_contains($relLow, 'liberacion_modelo')
                        || str_contains($relLow, 'confirmacion_modelo')
                        || str_contains($nameLow, 'ldm')
                        || str_contains($nameLow, 'aprobado')
                    ) {
                        $bucket = 'liberacion_modelo';
                        $detail = str_contains($relLow, 'confirmacion_modelo')
                            ? 'Confirmación de modelo · '.$ownerLabel
                            : 'Liberación de modelo · '.$ownerLabel;
                    } elseif (
                        str_contains($relLow, 'preorden')
                        || str_starts_with($nameLow, 'pre-orden')
                        || str_starts_with($nameLow, 'preorden')
                    ) {
                        $bucket = 'preordenes';
                        $detail = (str_contains($nameLow, 'casting') || str_contains($nameLow, 'pfc') || str_contains($relLow, 'casting'))
                            ? 'Preorden de Casting · '.$ownerLabel
                            : ((str_contains($nameLow, 'modelo') || str_contains($nameLow, 'mod-') || str_contains($relLow, 'modelo'))
                                ? 'Preorden de Fabricación de Modelo · '.$ownerLabel
                                : 'Preorden de Fundición · '.$ownerLabel);
                    } elseif (str_contains($relLow, 'ayudas_visuales')) {
                        $bucket = 'ayudas_fundicion';
                        $detail = 'Ayuda visual · '.$ownerLabel;
                    } elseif (str_contains($relLow, 'dibujo') || str_contains($relLow, 'dwg_fundicion') || $ext === 'dwg') {
                        $bucket = 'fundicion';
                        $detail = 'Dibujo · '.$folderName;
                    } else {
                        $bucket = 'extras_fundicion';
                        $detail = 'Documento · '.$ownerLabel;
                    }

                    // Deduplicar archivos espejados entre ALMACEN_FUNDICION y CALIDAD_FUNDICION
                    $dedupeKey = $bucket.'|'.strtolower($name);
                    if (isset($seenKeys[$dedupeKey])) {
                        continue;
                    }
                    $seenKeys[$dedupeKey] = true;

                    $buckets[$bucket][] = [
                        'name' => $name,
                        'class' => $claseLabel,
                        'detail' => $detail,
                        'available' => true,
                        'url' => URL::signedRoute('master.priorities.documents.open', [
                            'ot' => $ot->id, 'section' => $bucket, 'file' => $path,
                        ]),
                    ];
                }
            }
        }

        foreach ($buckets as $k => $list) {
            $buckets[$k] = collect($list)->sortBy(['class', 'name'])->values()->all();
        }

        return $buckets;
    }

    private function preordenesFiles(Orden_trabajo $ot, array $diskFiles): array
    {
        $existingNames = array_flip(array_map(fn ($f) => strtolower($f['name']), $diskFiles));
        $records = PreOrdenFundicion::query()->where('ot', 'like', '%'.$ot->id.'%')->get();

        foreach ($records as $rec) {
            if (! $this->isOrderFolder((string) $rec->ot, (string) $ot->id, true)) {
                continue;
            }
            $fname = (string) ($rec->pdf_filename ?? '');
            if ($fname === '' || isset($existingNames[strtolower($fname)])) {
                continue;
            }
            $existingNames[strtolower($fname)] = true;
            $diskFiles[] = [
                'name' => $fname,
                'class' => 'General',
                'detail' => 'Folio '.($rec->folio ?: 'N/A').' · PDF no localizado en disco',
                'available' => false,
                'url' => null,
            ];
        }

        return collect($diskFiles)->sortBy(['class', 'name'])->values()->all();
    }

    private function liberacionFiles(Orden_trabajo $ot, array $diskFiles): array
    {
        $existingNames = array_flip(array_map(fn ($f) => strtolower($f['name']), $diskFiles));
        $records = LiberacionModeloFundicion::query()
            ->where('ot', 'like', '%'.$ot->id.'%')
            ->where('decision', '!=', 'rechazar')
            ->get();

        foreach ($records as $rec) {
            if (! $this->isOrderFolder((string) $rec->ot, (string) $ot->id, true)) {
                continue;
            }
            $fname = (string) ($rec->pdf_filename ?? '');
            if ($fname === '' || isset($existingNames[strtolower($fname)])) {
                continue;
            }
            $existingNames[strtolower($fname)] = true;
            $diskFiles[] = [
                'name' => $fname,
                'class' => $rec->tipo_modelo ?: 'General',
                'detail' => 'Formato de liberación · Archivo no localizado en disco',
                'available' => false,
                'url' => null,
            ];
        }

        return collect($diskFiles)->sortBy(['class', 'name'])->values()->all();
    }

    private function rechazoFiles(Orden_trabajo $ot, array $diskFiles): array
    {
        $existingNames = array_flip(array_map(fn ($f) => strtolower($f['name']), $diskFiles));
        $records = LiberacionModeloFundicion::query()
            ->where('ot', 'like', '%'.$ot->id.'%')
            ->where('decision', '=', 'rechazar', 'and')
            ->get();

        foreach ($records as $rec) {
            if (! $this->isOrderFolder((string) $rec->ot, (string) $ot->id, true)) {
                continue;
            }
            $fname = (string) ($rec->pdf_filename ?? '');
            if ($fname === '' || isset($existingNames[strtolower($fname)])) {
                continue;
            }
            $existingNames[strtolower($fname)] = true;
            $diskFiles[] = [
                'name' => $fname,
                'class' => $rec->tipo_modelo ?: 'General',
                'detail' => 'Formato de rechazo · Archivo no localizado en disco',
                'available' => false,
                'url' => null,
            ];
        }

        return collect($diskFiles)->sortBy(['class', 'name'])->values()->all();
    }

    private function scarFiles(Orden_trabajo $ot, array $diskFiles): array
    {
        $existingNames = array_flip(array_map(fn ($f) => strtolower($f['name']), $diskFiles));
        $records = ScarModelo::query()->where('ot', 'like', '%'.$ot->id.'%')->get();

        foreach ($records as $rec) {
            if (! $this->isOrderFolder((string) $rec->ot, (string) $ot->id, true)) {
                continue;
            }
            foreach ([$rec->pdf_filename, $rec->pdf_firmado_filename] as $fname) {
                $fname = (string) ($fname ?? '');
                if ($fname === '' || isset($existingNames[strtolower($fname)])) {
                    continue;
                }
                $existingNames[strtolower($fname)] = true;
                $diskFiles[] = [
                    'name' => $fname,
                    'class' => $rec->tipo_modelo ?: 'General',
                    'detail' => 'SCAR '.($rec->no_scar ?: '').' · Archivo no localizado en disco',
                    'available' => false,
                    'url' => null,
                ];
            }
        }

        return collect($diskFiles)->sortBy(['class', 'name'])->values()->all();
    }

    private function tratamientosFiles(Orden_trabajo $ot, array $claseIds): array
    {
        if (empty($claseIds)) {
            return [];
        }

        $disk = Storage::disk('public');
        $files = [];
        $records = TratamientoTermico::query()
            ->with('clase')
            ->whereIn('id_clase', $claseIds, 'and', false)
            ->orderByDesc('id')
            ->get();

        foreach ($records as $rec) {
            $path = (string) ($rec->archivo ?? '');
            $available = $path !== '' && $disk->fileExists($path);
            $claseNombre = $rec->clase ? trim($rec->clase->nombre.' '.$rec->clase->tamanio) : 'General';
            $files[] = [
                'name' => $path !== '' ? basename($path) : 'Tratamiento_Termico_'.$rec->id.'.pdf',
                'class' => $claseNombre,
                'detail' => 'Cantidad: '.$rec->cantidad.($rec->descripcion ? ' · '.$rec->descripcion : ''),
                'available' => $available,
                'url' => $available ? route('wo.tratamiento.download', ['id' => $rec->id]) : null,
            ];
        }

        return $files;
    }

    private function remisionesFiles(Orden_trabajo $ot): array
    {
        $disk = Storage::disk('local');
        $files = [];
        $seen = [];

        $records = RemisionOt::query()
            ->with('clase')
            ->where('id_ot', '=', $ot->id, 'and')
            ->where('visible', '=', 1, 'and')
            ->orderByDesc('id')
            ->get();

        foreach ($records as $rec) {
            $path = str_replace('\\', '/', (string) ($rec->path ?? ''));
            $available = $path !== '' && $disk->fileExists($path);
            if ($path !== '') {
                $seen[strtolower($path)] = true;
            }
            $claseNombre = $rec->clase ? trim($rec->clase->nombre.' '.$rec->clase->tamanio) : 'General';
            $files[] = [
                'name' => $rec->filename ?: ($path !== '' ? basename($path) : 'Remision_'.$rec->id.'.pdf'),
                'class' => $claseNombre,
                'detail' => $rec->descripcion ?: 'Remisión registrada',
                'available' => $available,
                'url' => $available ? route('wo.remision.serve', ['id' => $rec->id]) : null,
            ];
        }

        // Complementar con archivos sueltos en DOCUMENTACION_GIS/REMISIONES
        foreach ($this->folderTreeFiles($ot, 'DOCUMENTACION_GIS/REMISIONES', 'remisiones', 'Remisión en disco') as $extra) {
            $files[] = $extra;
        }

        // Deduplicar por nombre si ya vino por BD
        $unique = [];
        foreach ($files as $f) {
            $k = strtolower($f['name']);
            if (! isset($unique[$k])) {
                $unique[$k] = $f;
            }
        }

        return array_values($unique);
    }

    private function rInternoSalidasFiles(Orden_trabajo $ot): array
    {
        $disk = Storage::disk('local');
        $files = [];
        $seenNames = [];

        $reports = RInternoSalida::query()
            ->with('pdfs')
            ->where('ot_id', '=', $ot->id, 'and')
            ->get();

        foreach ($reports as $report) {
            foreach ($report->pdfs as $pdf) {
                $path = str_replace('\\', '/', (string) ($pdf->ruta ?? ''));
                $available = $path !== '' && $disk->fileExists($path);
                $name = $pdf->nombre_archivo ?: basename($path);
                $seenNames[strtolower($name)] = true;
                $files[] = [
                    'name' => $name,
                    'class' => $report->clase ?: 'General',
                    'detail' => 'PDF generado · Versión '.($pdf->version ?? 1),
                    'available' => $available,
                    'url' => $available ? route('calidad.r_interno_salidas.pdf.download', ['id' => $report->id, 'pdf_id' => $pdf->id]) : null,
                ];
            }
        }

        foreach ($this->folderTreeFiles($ot, 'DOCUMENTACION_GIS/REPORTES_INTERNOS_SALIDAS', 'r_interno_salidas', 'Reporte Interno de Salidas') as $extra) {
            if (! isset($seenNames[strtolower($extra['name'])])) {
                $files[] = $extra;
            }
        }

        return collect($files)->sortBy(['class', 'name'])->values()->all();
    }

    private function folderTreeFiles(Orden_trabajo $ot, string $root, string $section, string $detailPrefix): array
    {
        $disk = Storage::disk('local');
        if (! $disk->exists($root)) {
            return [];
        }

        $files = [];
        foreach ($disk->directories($root) as $directory) {
            $folderName = basename($directory);
            if (! $this->isOrderFolder($folderName, (string) $ot->id)) {
                continue;
            }

            foreach ($disk->allFiles($directory) as $path) {
                $path = str_replace('\\', '/', $path);
                $name = basename($path);
                if ($name === '' || str_starts_with($name, '.')) {
                    continue;
                }
                $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
                if (in_array($ext, ['php', 'phtml', 'phar'], true)) {
                    continue;
                }

                $relative = ltrim(substr($path, strlen($directory)), '/');
                $parts = explode('/', $relative);
                $claseLabel = count($parts) > 1 ? $parts[0] : 'General';
                $subLabel = count($parts) > 2 ? implode(' / ', array_slice($parts, 1, -1)) : '';

                $files[] = [
                    'name' => $name,
                    'class' => $claseLabel,
                    'detail' => $subLabel !== '' ? $detailPrefix.' · '.$subLabel : $detailPrefix,
                    'available' => true,
                    'url' => URL::signedRoute('master.priorities.documents.open', [
                        'ot' => $ot->id, 'section' => $section, 'file' => $path,
                    ]),
                ];
            }
        }

        return collect($files)->sortBy(['class', 'name'])->values()->all();
    }

    private function mergeUniqueFiles(array $primary, array $secondary): array
    {
        $merged = [];
        foreach (array_merge($primary, $secondary) as $file) {
            $k = strtolower($file['name'].'|'.($file['class'] ?? ''));
            if (! isset($merged[$k])) {
                $merged[$k] = $file;
            }
        }

        return collect($merged)->sortBy(['class', 'name'])->values()->all();
    }

    private function isOrderFolder(string $folder, string $id, bool $allowReproceso = false): bool
    {
        $trimmed = trim($folder);
        if ($trimmed === '') {
            return false;
        }
        if (preg_match('/(?:_\d{8}_\d{6}|_del)$/i', $trimmed)) {
            return false;
        }
        if (! $allowReproceso && preg_match('/_R\d+$/i', $trimmed)) {
            return false;
        }

        return (bool) preg_match('/^OT[\s_-]*0*'.preg_quote($id, '/').'(?:\s|[_-]|$)/i', $trimmed);
    }

    private function reports(Orden_trabajo $ot, string $type): array
    {
        $model = $type === 'volumetricos' ? RVisual::class : RDimensional::class;
        $prefix = $type === 'volumetricos' ? 'calidad.inspeccion_visual.' : 'calidad.inspeccion_dimensional.';
        $attachmentRoute = $type === 'volumetricos' ? 'view_archivo' : 'view_excel';
        $disk = Storage::disk('local');
        $files = [];
        $reports = $model::query()->where('ot_id', '=', $ot->id, 'and')->with('pdfs')->get();
        foreach ($reports as $report) {
            foreach ($report->pdfs as $pdf) {
                $available = $disk->fileExists($pdf->ruta);
                $files[$pdf->ruta] = [
                    'name' => $pdf->nombre_archivo,
                    'class' => $report->clase,
                    'detail' => 'PDF generado · Versión '.$pdf->version,
                    'available' => $available,
                    'url' => $available ? route($prefix.'pdf.view', ['id' => $report->id, 'pdf_id' => $pdf->id]) : null,
                ];
            }

            foreach ($report->archivos_list as $attachment) {
                if (empty($attachment['ruta']) || ! isset($attachment['id'])) {
                    continue;
                }

                $available = $disk->fileExists($attachment['ruta']);
                $files[$attachment['ruta']] = [
                    'name' => $attachment['nombre'] ?? basename($attachment['ruta']),
                    'class' => $report->clase,
                    'detail' => 'Archivo adjunto',
                    'available' => $available,
                    'url' => $available ? route($prefix.$attachmentRoute, ['id' => $report->id, 'file_id' => $attachment['id']]) : null,
                ];
            }
        }

        return collect($files)->sortBy(['class', 'name'])->values()->all();
    }
}
