<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Barryvdh\DomPDF\Facade\Pdf;

class ConsortiumReportMail extends Mailable
{
    use Queueable, SerializesModels;

    public $consortium;
    public $pdf;

    public function __construct($consortium, $pdf)
    {
        $this->consortium = $consortium;
        $this->pdf = $pdf;
    }

    public function build()
    {
        return $this->subject('Your Think Tank Consortium Evaluation Report')
            ->view('emails.consortium_report_mail')
            ->attachData(
                $this->pdf->output(),
                'Consortium_Report_' . $this->consortium->id . '.pdf',
                ['mime' => 'application/pdf']
            );
    }
}