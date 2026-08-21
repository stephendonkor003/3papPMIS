<?php

namespace App\Mail;

use App\Models\Procurement;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class PrescreeningAssigned extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public User $assignee,
        public Procurement $procurement
    ) {}

    public function build()
    {
        return $this->subject('New Prescreening Assignment: ' . $this->procurement->title)
            ->view('emails.prescreening.assigned');
    }
}
