<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\PreOrdenFundicion;
use App\Models\FundicionHistory;
use App\Services\FundicionPaths;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;
use App\Mail\PreordenEntregaAlertMail;
use Illuminate\Support\Facades\Storage;

class AlertPreordenEntrega extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'alert:preorden-entrega';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Envía alertas por correo un día antes de la fecha de entrega de la preorden de modelo/casting.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $tomorrow = Carbon::tomorrow()->toDateString();

        $preOrdenes = PreOrdenFundicion::whereDate('fecha_entrega', $tomorrow)->get();

        if ($preOrdenes->isEmpty()) {
            $this->info("No hay preórdenes con fecha de entrega para mañana ($tomorrow).");
            return;
        }

        foreach ($preOrdenes as $po) {
            $isCasting = (
                strpos(strtolower($po->pdf_filename), 'casting') !== false ||
                strpos($po->pdf_filename, 'F_ALM_PFC_') !== false ||
                strpos($po->pdf_filename, 'PFC') !== false
            );
            $tipo = $isCasting ? 'Casting' : 'Modelo';

            // Buscar carpeta de la OT
            $history = FundicionHistory::where('ot', $po->ot)->first();
            $otClean = $po->ot;
            if ($history) {
                $otClean = $history->ot;
            }
            // Sanitizar nombre
            $folderName = preg_replace('/[^\w\s\-]/', '', $otClean);
            $folderName = preg_replace('/[\s]+/', '_', trim($folderName));

            // Buscar la ruta del escaneado
            $escaneadoPath = '';
            if ($isCasting) {
                $baseDir = FundicionPaths::preordenCasting($folderName, true);
            } else {
                $baseDir = FundicionPaths::preordenModelo($folderName, true);
            }

            // Buscar el archivo PDF escaneado
            // Generalmente se llama igual que el pdf_filename generado o comienza con eso.
            if (Storage::disk('local')->exists($baseDir)) {
                $files = Storage::disk('local')->files($baseDir);
                foreach ($files as $file) {
                    // Tratar de encontrar un PDF. En teoría solo debería haber uno o coincidir por nombre.
                    if (str_ends_with(strtolower($file), '.pdf')) {
                        // Asumiremos el primero que encontremos si no hay match exacto,
                        // o podemos buscar que el nombre original contenga partes del pdf.
                        $escaneadoPath = Storage::disk('local')->path($file);
                        break;
                    }
                }
            }

            if (empty($escaneadoPath) || !file_exists($escaneadoPath)) {
                $this->warn("No se encontró el archivo escaneado para la preorden OT: {$po->ot}, Folio: {$po->folio}. Se omitirá el envío adjunto.");
                // Si el usuario dijo "solamente escaneado", lo enviamos sin adjunto o buscamos el original?
                // El requerimiento decía "y se van a añadir la preorden... escaneada solamente".
                // Enviaremos sin archivo si no existe, para no bloquear la alerta.
            }

            // Determinar los destinatarios
            $proveedorMail = '';
            $provStr = strtolower($po->proveedor);
            if (strpos($provStr, 'ss') !== false) {
                $proveedorMail = env('EMAIL_PRODUCCION_SS', '');
            } elseif (strpos($provStr, 'jacarandas') !== false) {
                $proveedorMail = env('EMAIL_PRODUCCION_JACARANDAS', '');
            }

            // Si no detecta SS Metal o Jacarandas, usa un genérico o nada
            $almacenReqMail = env('EMAIL_ALMACEN', ''); // Ya incluye a requisiciones

            // Juntar todos los correos
            $allMails = array_merge(
                explode(',', $proveedorMail),
                explode(',', $almacenReqMail)
            );
            $allMails = array_unique(array_filter(array_map('trim', $allMails)));

            if (empty($allMails)) {
                $this->error("No hay destinatarios configurados para OT: {$po->ot}.");
                continue;
            }

            // Enviar correo
            try {
                Mail::to($allMails)->send(new PreordenEntregaAlertMail($po, $tipo, $escaneadoPath));
                $this->info("Alerta enviada correctamente para OT: {$po->ot}");
            } catch (\Exception $e) {
                $this->error("Error enviando alerta para OT: {$po->ot} - " . $e->getMessage());
            }
        }
    }
}
