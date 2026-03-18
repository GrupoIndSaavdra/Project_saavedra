<?php

namespace App\Console\Commands;

use App\Mail\ReporteDiarioMail;
use App\Models\Clase;
use App\Models\Moldura;
use App\Models\Orden_trabajo;
use App\Models\Pieza;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class EnviarReporteDiario extends Command
{
    /**
     * Firma del comando.
     * Uso normal:  php artisan reporte:enviar-diario
     * Modo prueba: php artisan reporte:enviar-diario --test
     * Fecha esp.:  php artisan reporte:enviar-diario --fecha=2025-03-15
     */
    protected $signature = 'reporte:enviar-diario
                            {--test : Envía el correo solo al remitente (modo prueba)}
                            {--fecha= : Fecha específica YYYY-MM-DD (por defecto hoy)}';

    protected $description = 'Genera y envía el Reporte General de Producción diario por correo electrónico';

    public function handle(): int
    {
        // ── 1. Determinar fecha ───────────────────────────────────────────
        $fechaStr = $this->option('fecha');
        $fecha = $fechaStr ? Carbon::parse($fechaStr) : Carbon::today();

        $this->info("Generando reporte para: {$fecha->toDateString()}");

        // ── 2. Consultar piezas del día ───────────────────────────────────
        $piezasDelDia = Pieza::with(['clase', 'operador', 'ordenTrabajo'])
            ->whereDate('created_at', $fecha)
            ->orderBy('id_ot')
            ->orderBy('id_clase')
            ->orderBy('proceso')
            ->get();

        if ($piezasDelDia->isEmpty()) {
            $this->warn("No se encontraron registros para {$fecha->toDateString()}. No se enviará correo.");
            return self::SUCCESS;
        }

        $this->info("Se encontraron {$piezasDelDia->count()} registros. Agrupando...");

        // ── 3. Agrupar: OT → Clase → Proceso → Operadores ────────────────
        $reporte = $this->agruparJerarquicamente($piezasDelDia);

        // ── 4. Determinar destinatarios ───────────────────────────────────
        $destinatarios = $this->obtenerDestinatarios();

        if ($this->option('test')) {
            $destinatarios = [config('mail.from.address')];
            $this->warn("MODO PRUEBA: correo enviado solo a " . implode(', ', $destinatarios));
        }

        // ── 5. Enviar ─────────────────────────────────────────────────────
        $enviados = 0;
        foreach ($destinatarios as $correo) {
            try {
                Mail::to(trim($correo))->send(new ReporteDiarioMail($reporte, $fecha));
                $this->info("✓ Enviado a: {$correo}");
                $enviados++;
            } catch (\Throwable $e) {
                $this->error("✗ Error enviando a {$correo}: " . $e->getMessage());
            }
        }

        $this->info("Reporte diario completado. Enviados: {$enviados}/" . count($destinatarios));
        return self::SUCCESS;
    }

    /**
     * Agrupa los registros en la jerarquía:
     * OT → Clase → Proceso → [ filas de operadores ]
     */
    private function agruparJerarquicamente($piezas): array
    {
        $reporte = [];
        $molduras = [];
        $usuarios = [];

        foreach ($piezas as $pieza) {
            // ── Nivel 1: OT ───────────────────────────────────────────────
            $otId = $pieza->id_ot;
            if (!isset($reporte[$otId])) {
                if (!isset($molduras[$otId])) {
                    $ot = Orden_trabajo::find($otId);
                    $mn = $ot
                        ? optional(Moldura::find($ot->id_moldura))->nombre ?? 'Sin Moldura'
                        : 'Sin Moldura';
                    $molduras[$otId] = "OT #{$otId} — {$mn}";
                }
                $reporte[$otId] = [
                    'ot_label' => $molduras[$otId],
                    'clases' => [],
                ];
            }

            // ── Nivel 2: Clase ────────────────────────────────────────────
            $claseId = $pieza->id_clase;
            if (!isset($reporte[$otId]['clases'][$claseId])) {
                $cls = Clase::find($claseId);
                $reporte[$otId]['clases'][$claseId] = [
                    'clase_label' => $cls
                        ? trim($cls->nombre . ' ' . $cls->tamanio)
                        : "Clase #{$claseId}",
                    'procesos' => [],
                ];
            }

            // ── Nivel 3: Proceso ──────────────────────────────────────────
            $proceso = $pieza->proceso ?? 'Sin Proceso';
            if (!isset($reporte[$otId]['clases'][$claseId]['procesos'][$proceso])) {
                $reporte[$otId]['clases'][$claseId]['procesos'][$proceso] = [];
            }

            // ── Nivel 4: Operador ─────────────────────────────────────────
            $mat = $pieza->id_operador;
            if (!isset($usuarios[$mat])) {
                $u = User::where('matricula', $mat)->first();
                $usuarios[$mat] = $u
                    ? trim("{$u->nombre} {$u->a_paterno} {$u->a_materno}")
                    : "Operador #{$mat}";
            }
            $operador = $usuarios[$mat];

            if (!isset($reporte[$otId]['clases'][$claseId]['procesos'][$proceso][$operador])) {
                $reporte[$otId]['clases'][$claseId]['procesos'][$proceso][$operador] = [];
            }

            // liberacion != 2 → pieza buena/liberada (igual que DatosProduccionController)
            $estaLiberada = ($pieza->liberacion != 2);

            $reporte[$otId]['clases'][$claseId]['procesos'][$proceso][$operador][] = [
                'n_piezas' => $pieza->n_pieza,
                'hora' => Carbon::parse($pieza->created_at)->format('d/m/Y H:i'),
                'observacion' => $pieza->observacion_liberacion ?? '—',
                'liberado' => $estaLiberada,
                'error' => $pieza->error ?? 'Ninguno',
            ];
        }

        return $reporte;
    }

    /**
     * Lee destinatarios de .env (REPORT_RECIPIENTS=a@b.com,c@d.com)
     */
    private function obtenerDestinatarios(): array
    {
        $raw = env('REPORT_RECIPIENTS', config('mail.from.address'));
        return array_filter(array_map('trim', explode(',', $raw)));
    }
}
