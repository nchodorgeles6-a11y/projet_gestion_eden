<?php

namespace App\Mail;

use App\Models\Conge;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CongeRefuse extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Conge $conge) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Votre congé a été refusé — EdenCorporate');
    }

    public function content(): Content
    {
        return new Content(markdown: 'emails.conge.refuse', with: [
            'employe'   => $this->conge->user,
            'dateDebut' => $this->conge->date_debut?->format('d/m/Y'),
            'dateFin'   => $this->conge->date_fin?->format('d/m/Y'),
            'motif'     => $this->conge->motif?->nom ?? '—',
        ]);
    }
}
