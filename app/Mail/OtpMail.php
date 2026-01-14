<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OtpMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $otpCode;
    public string $type;
    public int $expirySeconds;

    /**
     * Create a new message instance.
     */
    public function __construct(string $otpCode, string $type = 'register', int $expirySeconds = 60)
    {
        $this->otpCode = $otpCode;
        $this->type = $type;
        $this->expirySeconds = $expirySeconds;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $subject = $this->type === 'register'
            ? 'Kode OTP Pendaftaran - TNLL Explore'
            : 'Kode OTP Reset Password - TNLL Explore';

        return new Envelope(
            subject: $subject,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.otp',
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
