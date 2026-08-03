<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Queue\SerializesModels;

class PreOrdenMailable extends Mailable
{
    use Queueable, SerializesModels;

    public array $data;
    public string $pdfPath;
    public ?string $userName;

    /**
     * Create a new message instance.
     * 
     * @param array $data
     * @param string $pdfPath
     * @param string|null $userName
     */
    public function __construct(array $data, string $pdfPath, ?string $userName = null)
    {
        $this->data = $data;
        $this->pdfPath = $pdfPath;
        $this->userName = $userName;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Pre-Orden de Modelos: {$this->data['folio']} - {$this->data['ot']}",
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.pre_order',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, Attachment>
     */
    public function attachments(): array
    {
        return [
            Attachment::fromPath($this->pdfPath)
                ->as("PreOrden_{$this->data['folio']}.pdf")
                ->withMime('application/pdf'),
        ];
    }
}
