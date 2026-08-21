<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class EligibleYesNotification extends Mailable
{
    use Queueable, SerializesModels;

    public $applicant;

    /**
     * Create a new message instance.
     */
    public function __construct($applicant)
    {
        $this->applicant = $applicant;
    }

    /**
     * Get the message envelope.
     */
     public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Congratulations on Advancing to the Next Stage of the 3PAP PMIS Proposal Evaluation',
        );
    }


    /**
     * Get the message content definition.
     */
     public function content(): Content
    {
        return new Content(
            view: 'emails.eligible_yes',
            with: [
                'applicant' => $this->applicant,
            ],
        );
    }


    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}