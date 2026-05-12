<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PtaReporteMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $otNombre;
    public string $claseNombre;
    public string $pdfPath;

    /**
     * @param string $otNombre   Etiqueta legible de la OT (ej. "OT #5 — Bombillo")
     * @param string $claseNombre Nombre de la clase (ej. "Obturador")
     * @param string $pdfPath    Ruta absoluta del PDF a adjuntar
     */
    public function __construct(string $otNombre, string $claseNombre, string $pdfPath)
    {
        $this->otNombre    = $otNombre;
        $this->claseNombre = $claseNombre;
        $this->pdfPath     = $pdfPath;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Reporte Soldadura PTA — {$this->otNombre} / Clase: {$this->claseNombre}",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.pta_reporte',
            with: [
                'otNombre'    => $this->otNombre,
                'claseNombre' => $this->claseNombre,
            ],
        );
    }

    public function attachments(): array
    {
        if (!file_exists($this->pdfPath)) {
            return [];
        }

        $fechaHora    = now()->format('d-m-Y_H-i');
        $friendlyName = "Reporte_PTA_{$fechaHora}.pdf";

        return [
            \Illuminate\Mail\Mailables\Attachment::fromPath($this->pdfPath)
                ->as($friendlyName)
                ->withMime('application/pdf'),
        ];
    }
}
