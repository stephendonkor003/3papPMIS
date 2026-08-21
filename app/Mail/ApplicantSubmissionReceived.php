<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ApplicantSubmissionReceived extends Mailable
{
    use Queueable, SerializesModels;

    public $applicant;
    public $loginCode;
    public $defaultPassword;

    /**
     * Create a new message instance.
     */
    public function __construct($applicant, $loginCode, $defaultPassword)
    {
        $this->applicant = $applicant;
        $this->loginCode = $loginCode;
        $this->defaultPassword = $defaultPassword;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        return $this->subject('Application received | 3PAP PMIS')
                    ->view('emails.applicant.received')
                    ->with([
                        'applicant' => $this->applicant,
                        'loginCode' => $this->loginCode,
                        'defaultPassword' => $this->defaultPassword,
                    ]);
    }
}
