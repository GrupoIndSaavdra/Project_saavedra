<?php

namespace App\Console\Commands;

use App\Models\FundicionHistory;
use App\Http\Controllers\DibujosFundicionPdfController;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Throwable;

class SyncFundicionDocs extends Command
{
    protected $signature = 'calidad:sync-fundicion';
    protected $description = 'Sincroniza Dibujos y Ayudas Visuales de Fundición para todas las OTs activas.';

    public function handle(): int
    {
        $now = Carbon::now();
        $this->info('══════════════════════════════════════════════════');
        $this->info('  SYNC CALIDAD — FUNDICIÓN  ' . $now->toDateTimeString());
        $this->info('══════════════════════════════════════════════════');

        try {
            // Obtener todas las OTs con estado 'activa'
            $activeHistories = FundicionHistory::where('status', 'activa')->get();
            $total = $activeHistories->count();

            $this->info("Encontradas {$total} OTs activas para sincronizar.");

            foreach ($activeHistories as $history) {
                $this->line("  Sincronizando: {$history->ot}...");
                try {
                    DibujosFundicionPdfController::copyToAlmacen($history->ot, false);
                    $this->info("    -> Completado.");
                } catch (Throwable $e) {
                    $this->error("    -> Error al sincronizar {$history->ot}: " . $e->getMessage());
                }
            }

            $this->info('Sincronización de Fundición finalizada con éxito.');
            return self::SUCCESS;
        } catch (Throwable $e) {
            $this->error("Error general en la sincronización de Fundición: " . $e->getMessage());
            return self::FAILURE;
        }
    }
}
