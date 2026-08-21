<?php

namespace App\Mail;

use App\Models\Evaluation;
use App\Models\FormSubmission;
use App\Models\Procurement;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class EvaluationAssigned extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public User $evaluator,
        public Evaluation $evaluation,
        public Procurement $procurement,
        public ?FormSubmission $submission = null
    ) {}

    public function build()
    {
        $subject = $this->submission
            ? 'Evaluation Assignment (Submission): ' . $this->submission->procurement_submission_code
            : 'Evaluation Assignment (Procurement): ' . $this->procurement->title;

        return $this->subject($subject)
            ->view('emails.evaluations.assigned');
    }
}
