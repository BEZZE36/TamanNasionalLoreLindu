<?php

namespace App\Mail;

use App\Models\NewsletterCampaign;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class NewsletterMail extends Mailable
{
    use Queueable, SerializesModels;

    public NewsletterCampaign $campaign;
    public string $recipientEmail;
    public ?string $recipientName;
    public string $unsubscribeUrl;

    /**
     * Create a new message instance.
     */
    public function __construct(
        NewsletterCampaign $campaign,
        string $recipientEmail,
        ?string $recipientName = null
    ) {
        $this->campaign = $campaign;
        $this->recipientEmail = $recipientEmail;
        $this->recipientName = $recipientName;
        $this->unsubscribeUrl = url('/user/newsletter/unsubscribe');
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->campaign->subject,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.newsletter',
            with: [
                'subject' => $this->campaign->subject,
                'content' => $this->campaign->content,
                'recipientName' => $this->recipientName,
                'recipientEmail' => $this->recipientEmail,
                'unsubscribeUrl' => $this->unsubscribeUrl,
            ],
        );
    }

    /**
     * Get the attachments for the message.
     */
    public function attachments(): array
    {
        return [];
    }
}
