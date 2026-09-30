<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ResetPasswordMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public string $code, public string $name)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Your password reset code — U Nyi Lay Silver Shop');
    }

    public function content(): Content
    {
        // Plain text, not HTML — a styled HTML template with a reset-link
        // button was landing nowhere (not even spam) on the production mail
        // server, while plain text delivered fine. A short numeric code
        // doesn't need HTML anyway.
        return new Content(text: 'emails.reset-password-text');
    }
}
