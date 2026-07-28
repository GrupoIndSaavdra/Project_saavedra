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
    public array $pdfPaths;

    /**
     * @param array  $reporte   Datos ya agrupados OT→Clase→Proceso→Operadores
     * @param Carbon $fecha     Fecha del reporte
     * @param array  $pdfPaths  Rutas absolutas a los PDFs generados para adjuntar
     */
    public function __construct(array $reporte, Carbon $fecha, array $pdfPaths = [])
    {
        $this->reporte = $reporte;
        $this->fecha = $fecha;
        $this->pdfPaths = $pdfPaths;
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
            view: 'emails.daily_report',
            with: [
                'reporte' => $this->reporte,
                'fecha' => $this->fecha,
            ],
        );
    }

    public function attachments(): array
    {
        $attachments = [];
        foreach ($this->pdfPaths as $path) {
            // Nombre base solicitado: Reporte_Produccion_[Fecha]_[Hora]
            $hora = now()->format('H-i');
            $friendlyName = "Reporte_Produccion_{$this->fecha->toDateString()}_{$hora}.pdf";

            $attachments[] = \Illuminate\Mail\Mailables\Attachment::fromPath($path)
                ->as($friendlyName);
        }
        return $attachments;
    }
}
