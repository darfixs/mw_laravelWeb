<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Attachment;

class FacturaMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $numeroFactura;
    public string $nombreReceptor;
    public string $pdfPath;          // Ruta absoluta al PDF
    public string $pdfNombre;        // Nombre del archivo adjunto

    public function __construct(string $numeroFactura, string $nombreReceptor, string $pdfPath, string $pdfNombre)
    {
        $this->numeroFactura  = $numeroFactura;
        $this->nombreReceptor = $nombreReceptor;
        $this->pdfPath        = $pdfPath;
        $this->pdfNombre      = $pdfNombre;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Tu factura de Miss Whitney · ' . $this->numeroFactura,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.factura',
            with: [
                'numeroFactura'  => $this->numeroFactura,
                'nombreReceptor' => $this->nombreReceptor,
            ],
        );
    }

    public function attachments(): array
    {
        return [
            Attachment::fromPath($this->pdfPath)
                ->as($this->pdfNombre)
                ->withMime('application/pdf'),
        ];
    }
}
