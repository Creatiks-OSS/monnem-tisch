<?php

namespace App\Mail;

use App\Models\Quote;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class QuoteProposalMail extends Mailable
{
    use SerializesModels;

    public function __construct(
        public Quote $quote,
        public string $pdfPath
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Ihr Angebot '.$this->quote->quote_number,
        );
    }

    public function content(): Content
    {
        $name = trim($this->quote->first_name.' '.$this->quote->last_name);
        $body = "Sehr geehrte/r {$name},\n\nanbei erhalten Sie unser Angebot {$this->quote->quote_number}.\n\nMit freundlichen Grüßen\nMonnemtisch Catering";

        return new Content(
            htmlString: nl2br(e($body), false),
        );
    }

    public function attachments(): array
    {
        return [
            Attachment::fromPath($this->pdfPath)
                ->as($this->quote->quote_number.'.pdf')
                ->withMime('application/pdf'),
        ];
    }
}
