<?php

namespace App\Mail;

use App\Models\BulletinPaie;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class BulletinPaye extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public BulletinPaie $bulletin) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Bulletin de paie {$this->bulletin->mois} {$this->bulletin->annee} — EdenCorporate"
        );
    }

    public function content(): Content
    {
        return new Content(markdown: 'emails.bulletin.paye', with: [
            'employe'   => $this->bulletin->user,
            'bulletin'  => $this->bulletin,
            'netAPayer' => number_format((float) $this->bulletin->net_a_payer, 0, ',', ' '),
        ]);
    }
}
