<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Queue\SerializesModels;
use App\Models\PreOrdenFundicion;

class PreordenEntregaAlertMail extends Mailable
{
    use Queueable, SerializesModels;

    public PreOrdenFundicion $po;
    public string $tipo;
    public string $escaneadoPath;

    /**
     * Create a new message instance.
     */
    public function __construct(PreOrdenFundicion $po, string $tipo, string $escaneadoPath)
    {
        $this->po = $po;
        $this->tipo = $tipo;
        $this->escaneadoPath = $escaneadoPath;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "ALERTA: Falta 1 día para entrega de {$this->tipo} - OT: {$this->po->ot}",
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.preorden_entrega_alert',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        $attachments = [];
        
        if (!empty($this->escaneadoPath) && file_exists($this->escaneadoPath)) {
            $attachments[] = Attachment::fromPath($this->escaneadoPath)
                ->as("PreOrden_{$this->tipo}_Escaneada_{$this->po->folio}.pdf")
                ->withMime('application/pdf');
        }

        return $attachments;
    }
}
