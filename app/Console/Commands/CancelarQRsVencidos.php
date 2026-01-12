<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\QRGeneradoSoldadura;
use Carbon\Carbon;

class CancelarQRsVencidos extends Command
{
    protected $signature = 'qr:cancelar-vencidos';
    protected $description = 'Cancela QRs que han pasado más de 2 días sin ser liberados';

    public function handle()
    {
        $fechaLimite = Carbon::now()->subDays(2)->toDateString();
        
        $qrsVencidos = QRGeneradoSoldadura::where('estado', 'generado')
            ->where('fecha_generacion', '<', $fechaLimite)
            ->get();

        $contador = 0;
        foreach ($qrsVencidos as $qr) {
            $qr->update(['estado' => 'cancelado']);
            $contador++;
        }

        $this->info("Se cancelaron {$contador} QRs vencidos.");
        
        return 0;
    }
}