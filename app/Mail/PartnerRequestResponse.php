<?php

namespace App\Mail;

use App\Models\PartnerInformationRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class PartnerRequestResponse extends Mailable
{
    use Queueable, SerializesModels;

    public $infoRequest;

    /**
     * Create a new message instance.
     */
    public function __construct(PartnerInformationRequest $infoRequest)
    {
        $this->infoRequest = $infoRequest;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        $subject = 'Response to Your Information Request: ' . $this->infoRequest->subject;

        return $this->subject($subject)
            ->view('emails.partners.request-response')
            ->with([
                'request' => $this->infoRequest,
            ]);
    }
}
