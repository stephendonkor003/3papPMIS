<?php

namespace App\Mail\Security;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class LoginOtpMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public User $user,
        public string $otpCode
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Your Verification Code - ' . config('app.name'),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.security.login-otp',
            with: [
                'user' => $this->user,
                'otpCode' => $this->otpCode,
            ],
        );
    }
}
