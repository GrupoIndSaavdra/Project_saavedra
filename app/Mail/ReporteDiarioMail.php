<?php

namespace App\Mail;

use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ReporteDiarioMail extends Mailable
{
    use Queueable, SerializesModels;

    public array $reporte;
    public Carbon $fecha;

    /**
     * @param array  $reporte  Datos ya agrupados OT→Clase→Proceso→Operadores
     * @param Carbon $fecha    Fecha del reporte
     */
    public function __construct(array $reporte, Carbon $fecha)
    {
        $this->reporte = $reporte;
        $this->fecha = $fecha;
    }

    public function envelope(): Envelope
    {
        $fechaLegible = $this->fecha->translatedFormat('d \d\e F \d\e Y');
        return new Envelope(
            subject: "Reporte General de Producción — {$fechaLegible}",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.reporte_diario',
            with: [
                'reporte' => $this->reporte,
                'fecha' => $this->fecha,
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
