<?php

namespace App\Http\Controllers;

use App\Models\Orden_trabajo;
use App\Models\Clase;
use App\Models\Pieza;
use App\Models\RDimensional;
use App\Models\RVisual;
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

    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request, Orden_trabajo $ot)
    {
        abort_unless(in_array((string) $request->user()->perfil, ['1', '3', '4', '6'], true), 403);
        $sections = [];
        foreach ([
            'maquinados' => 'Dibujos de Maquinados',
            'fundicion' => 'Dibujos de Fundición',
            'volumetricos' => 'Reportes volumétricos',
            'dimensionales' => 'Reportes dimensionales',
            'piezas' => 'Reporte de piezas',
            'pta' => 'Reportes de PTA',
        ] as $type => $title) {
            try {
                $files = match ($type) {
                    'maquinados', 'fundicion' => $this->drawings($ot, $type),
                    'piezas', 'pta' => $this->productionReports($ot, $type),
                    default => $this->reports($ot, $type),
                };
                $sections[] = ['title' => $title, 'files' => $files, 'error' => null];
            } catch (\Throwable $error) {
                Log::error('No se pudieron consultar los documentos de una OT en Prioridades.', [
                    'user_id' => $request->user()->id,
                    'ot' => $ot->id,
                    'section' => $type,
                    'error' => $error->getMessage(),
                ]);
                $sections[] = ['title' => $title, 'files' => [], 'error' => 'No se pudo consultar esta sección. Vuelve a intentarlo.'];
            }
        }

        return response()->json(['success' => true, 'ot' => $ot->id, 'sections' => $sections]);
    }

    public function availability(Request $request)
    {
        abort_unless(in_array((string) $request->user()->perfil, ['1', '3', '4', '6'], true), 403);
        $data = $request->validate([
            'ots' => 'required|array|max:100',
            'ots.*' => 'required|string|max:255',
        ]);

        try {
            $ids = Orden_trabajo::query()->whereIn('id', $data['ots'])->pluck('id')->all();
            $available = array_fill_keys($ids, false);
            $disk = Storage::disk('local');

            // Los reportes generados al abrir también son documentos disponibles.
            $production = Pieza::query()->whereIn('id_ot', $ids)
                ->whereHas('clase', fn ($query) => $query->whereColumn('clases.id_ot', 'piezas.id_ot')
                    ->where(fn ($state) => $state->where('finalizada', '=', 0, 'and')
                        ->orWhere('piezas.proceso', '=', 'Soldadura PTA')))
                ->distinct()->pluck('id_ot');
            foreach ($production as $id) {
                $available[$id] = true;
            }

            foreach ([RVisual::class, RDimensional::class] as $model) {
                $pending = array_keys(array_filter($available, fn ($value) => ! $value));
                if (! $pending) {
                    break;
                }
                foreach ($model::query()->whereIn('ot_id', $pending)->with('pdfs')->get() as $report) {
                    $paths = $report->pdfs->pluck('ruta')->merge(array_column($report->archivos_list, 'ruta'));
                    foreach ($paths as $path) {
                        if ($path && $disk->fileExists($path)) {
                            $available[$report->ot_id] = true;
                            break;
                        }
                    }
                }
            }

            foreach (self::DRAWINGS as $type => $roots) {
                foreach ($roots as $root) {
                    $pending = array_keys(array_filter($available, fn ($value) => ! $value));
                    if (! $pending) {
                        break 2;
                    }
                    foreach ($disk->directories($root) as $directory) {
                        foreach ($pending as $id) {
                            if ($available[$id] || ! $this->isOrderFolder(basename($directory), (string) $id)) {
                                continue;
                            }
                            foreach ($disk->allFiles($directory) as $path) {
                                if (in_array(strtolower(pathinfo($path, PATHINFO_EXTENSION)), $type === 'fundicion' ? ['pdf', 'dwg'] : ['pdf'], true)) {
                                    $available[$id] = true;
                                    break;
                                }
                            }
                        }
                    }
                }
            }

            return response()->json(['success' => true, 'available' => (object) $available]);
        } catch (\Throwable $error) {
            Log::error('No se pudo comprobar la disponibilidad de archivos en Prioridades.', [
                'user_id' => $request->user()->id, 'error' => $error->getMessage(),
            ]);

            return response()->json(['success' => false, 'message' => 'No se pudo comprobar la disponibilidad de archivos.'], 500);
        }
    }

    public function openDrawing(Request $request, Orden_trabajo $ot)
    {
        abort_unless(in_array((string) $request->user()->perfil, ['1', '3', '4', '6'], true), 403);
        $type = $request->query('section');
        $path = $request->query('file');
        abort_unless(is_string($type) && isset(self::DRAWINGS[$type]) && is_string($path), 404);
        abort_if(str_contains($path, chr(92)) || preg_match('~(?:^|/)\.\.?(/|$)~', $path), 404);
        $valid = false;
        foreach (self::DRAWINGS[$type] as $root) {
            $relative = str_starts_with($path, $root.'/') ? substr($path, strlen($root) + 1) : '';
            $folder = explode('/', $relative)[0];
            if ($this->isOrderFolder($folder, $ot->id)) {
                $valid = true;
                break;
            }
        }

        $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        abort_unless($valid && in_array($extension, $type === 'fundicion' ? ['pdf', 'dwg'] : ['pdf'], true), 404);
        $disk = Storage::disk('local');
        abort_unless($disk->fileExists($path), 404, 'El archivo ya no está disponible.');

        return $extension === 'pdf'
            ? response()->file($disk->path($path), ['Content-Type' => 'application/pdf'])
            : $disk->download($path);
    }

    public function openProductionReport(Request $request, Orden_trabajo $ot, string $type)
    {
        abort_unless(in_array((string) $request->user()->perfil, ['1', '3', '4', '6'], true), 403);
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

    private function productionReports(Orden_trabajo $ot, string $type): array
    {
        if ($type === 'piezas') {
            $hasPieces = Pieza::query()->where('id_ot', '=', $ot->id, 'and')
                ->whereHas('clase', fn ($query) => $query->where('finalizada', '=', 0, 'and'))->exists();

            return $hasPieces ? [[
                'name' => 'Reporte de piezas - OT '.$ot->id,
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
                'name' => 'Reporte de PTA - OT '.$ot->id,
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
            foreach ($disk->directories($root) as $directory) {
                if (! $this->isOrderFolder(basename($directory), $ot->id)) {
                    continue;
                }

                foreach ($disk->allFiles($directory) as $path) {
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

    private function isOrderFolder(string $folder, string $id): bool
    {
        return (bool) preg_match('/^OT\s+'.preg_quote($id, '/').'(?:\s+-\s+.+)?$/iu', $folder)
            && ! preg_match('/(?:_R\d+|_\d{8}_\d{6}_del)$/i', $folder);
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
