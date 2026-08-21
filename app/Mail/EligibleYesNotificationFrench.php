<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class EligibleYesNotificationFrench extends Mailable
{
    use Queueable, SerializesModels;

    public $applicant;

    public function __construct($applicant)
    {
        $this->applicant = $applicant;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Félicitations pour votre avancement à la prochaine étape de l\'évaluation des propositions 3PAP PMIS',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.eligible_yes_fr',
            with: [
                'applicant' => $this->applicant,
            ],
        );
    }
}